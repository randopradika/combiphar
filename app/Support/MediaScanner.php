<?php

namespace App\Support;

use App\Models\MediaFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

/**
 * Membangun ulang indeks media: apa saja yang ada di disk, dan dipakai di mana.
 *
 * Situs ini menyimpan unggahan sebagai STRING PATH di kolom biasa (30 field
 * FileUpload di 18 resource), bukan lewat relasi media library. Jadi satu-satunya
 * cara mengetahui sebuah berkas masih dipakai adalah membaca kolom-kolom teks
 * itu lalu mencocokkannya dengan daftar berkas di disk. Itulah yang dikerjakan
 * kelas ini -- tanpa mengubah satu pun field unggahan yang sudah ada.
 */
class MediaScanner
{
    /** Tabel kerangka kerja: tidak pernah memuat path konten. */
    private const SKIP_TABLES = [
        'migrations', 'sessions', 'cache', 'cache_locks', 'jobs', 'job_batches',
        'failed_jobs', 'password_reset_tokens', 'personal_access_tokens',
        'media_files', 'users',
    ];

    /** Hanya kolom bertipe teks yang mungkin memuat path. */
    private const TEXT_TYPES = [
        'char', 'varchar', 'string', 'text', 'tinytext', 'mediumtext', 'longtext', 'json', 'jsonb',
    ];

    /** Ekstensi yang dianggap aset pustaka. */
    private const EXTENSIONS = [
        'jpg', 'jpeg', 'png', 'webp', 'gif', 'svg', 'avif', 'ico', 'bmp',
        'pdf', 'mp4', 'webm', 'mov', 'doc', 'docx', 'xls', 'xlsx',
    ];

    /** Batas rujukan yang disimpan per berkas; usage_count tetap total sebenarnya. */
    private const MAX_REFERENCES = 50;

    /**
     * Direktori kode yang ikut diperiksa.
     *
     * Tidak semua rujukan ada di basis data: seeder menyebut path "seed/..."
     * secara harfiah, dan berkas itu akan tampak "tidak terpakai" bila hanya
     * kolom basis data yang dibaca -- padahal menghapusnya membuat `db:seed`
     * pada environment baru menghasilkan gambar kosong. Halaman Pustaka Media
     * mengizinkan penghapusan berkas tak terpakai, jadi ketelitian di sini
     * adalah soal keamanan, bukan kelengkapan.
     */
    private const SOURCE_DIRS = ['app', 'config', 'database', 'lang', 'resources', 'routes'];

    private const SOURCE_EXTENSIONS = ['php', 'js', 'jsx', 'ts', 'tsx', 'json', 'css', 'blade'];

    /** Berkas sumber di atas ukuran ini dilewati (bundel, sourcemap). */
    private const MAX_SOURCE_BYTES = 2097152;

    /**
     * @return array{indexed:int, unused:int, removed:int}
     */
    public function scan(): array
    {
        $disk = Storage::disk('public');

        // 1. Apa yang benar-benar ada di disk.
        $paths = collect($disk->allFiles())
            ->filter(fn ($p) => $this->isLibraryAsset($p))
            ->unique()
            ->values();

        // 2. Dua peta pencarian. byBasename menangani kolom yang menyimpan URL
        //    penuh atau awalan /storage/, yang tidak cocok persis dengan path relatif.
        $byPath = $paths->flip()->all();
        $byBasename = [];

        foreach ($paths as $p) {
            $byBasename[basename($p)][] = $p;
        }

        // 3. Kumpulkan rujukan dari seluruh kolom teks, lalu dari kode sumber.
        $usage = $this->collectUsage($byPath, $byBasename);

        foreach ($this->collectSourceUsage($byPath, $byBasename) as $path => $refs) {
            $usage[$path] = array_merge($usage[$path] ?? [], $refs);
        }

        // 4. Tulis indeksnya.
        $now = now();
        $seen = [];

        foreach ($paths as $path) {
            $refs = $usage[$path] ?? [];

            MediaFile::updateOrCreate(
                ['path' => $path],
                [
                    'folder' => str_contains($path, '/') ? dirname($path) : '',
                    'filename' => basename($path),
                    'extension' => strtolower(pathinfo($path, PATHINFO_EXTENSION)) ?: null,
                    'size' => $this->sizeOf($disk, $path),
                    'usage_count' => count($refs),
                    'used_by' => array_slice($refs, 0, self::MAX_REFERENCES),
                    'file_modified_at' => $this->modifiedAt($disk, $path),
                    'scanned_at' => $now,
                ],
            );

            $seen[] = $path;
        }

        // 5. Berkas yang sudah tidak ada di disk tidak boleh tertinggal di indeks.
        $stale = $seen === []
            ? MediaFile::query()
            : MediaFile::query()->whereNotIn('path', $seen);

        $removed = (clone $stale)->count();
        $stale->delete();

        return [
            'indexed' => $paths->count(),
            'unused' => MediaFile::query()->where('usage_count', 0)->count(),
            'removed' => $removed,
        ];
    }

    /**
     * Satu lintasan atas setiap kolom teks di setiap tabel aplikasi.
     *
     * @param  array<string, int>  $byPath
     * @param  array<string, array<int, string>>  $byBasename
     * @return array<string, array<int, array{table:string, column:string, id:mixed}>>
     */
    private function collectUsage(array $byPath, array $byBasename): array
    {
        $usage = [];

        foreach ($this->contentTables() as $table) {
            $columns = $this->textColumns($table);

            if ($columns === []) {
                continue;
            }

            $hasId = in_array('id', Schema::getColumnListing($table), true);
            $select = $hasId ? array_merge(['id'], $columns) : $columns;

            DB::table($table)
                ->select($select)
                ->orderBy($hasId ? 'id' : $columns[0])
                ->chunk(200, function ($rows) use (&$usage, $table, $columns, $hasId, $byPath, $byBasename) {
                    foreach ($rows as $row) {
                        foreach ($columns as $column) {
                            $value = $row->{$column} ?? null;

                            if (! is_string($value) || $value === '') {
                                continue;
                            }

                            foreach ($this->pathsIn($value, $byPath, $byBasename) as $path) {
                                $usage[$path][] = [
                                    'table' => $table,
                                    'column' => $column,
                                    'id' => $hasId ? ($row->id ?? null) : null,
                                ];
                            }
                        }
                    }
                });
        }

        return $usage;
    }

    /**
     * Rujukan yang tertulis di kode, bukan di basis data.
     *
     * Seeder adalah kasus utamanya. Perhatikan bahwa MockupContentSeeder menyusun
     * sebagian path lewat penggabungan string ('seed/board/' . $img), sehingga
     * yang tertangkap hanyalah nama berkasnya -- itu cukup, karena pencocokan
     * lewat nama berkas tetap dijalankan selama namanya unik.
     *
     * @param  array<string, int>  $byPath
     * @param  array<string, array<int, string>>  $byBasename
     * @return array<string, array<int, array{table:string, column:string, id:null}>>
     */
    private function collectSourceUsage(array $byPath, array $byBasename): array
    {
        $usage = [];

        foreach (self::SOURCE_DIRS as $dir) {
            $root = base_path($dir);

            if (! is_dir($root)) {
                continue;
            }

            $files = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($root, \FilesystemIterator::SKIP_DOTS),
            );

            foreach ($files as $file) {
                if (! $file->isFile() || $file->getSize() > self::MAX_SOURCE_BYTES) {
                    continue;
                }

                if (! in_array(strtolower($file->getExtension()), self::SOURCE_EXTENSIONS, true)) {
                    continue;
                }

                $contents = @file_get_contents($file->getPathname());

                if ($contents === false || $contents === '') {
                    continue;
                }

                $relative = str_replace('\\', '/', substr($file->getPathname(), strlen(base_path()) + 1));

                foreach ($this->pathsIn($contents, $byPath, $byBasename) as $path) {
                    $usage[$path][] = [
                        'table' => 'kode',
                        'column' => $relative,
                        'id' => null,
                    ];
                }
            }
        }

        return $usage;
    }

    /**
     * Menarik token mirip-path dari satu nilai, lalu mencocokkannya dengan berkas
     * yang benar-benar ada. Regex-nya sengaja longgar: token yang tidak cocok
     * dengan disk langsung gugur, jadi false positive tidak berbahaya.
     *
     * Menangani ketiga bentuk yang dipakai basis data ini: path relatif
     * ("awards/x.jpg"), awalan /storage/, dan URL penuh. Nilai JSON ikut tertangani
     * karena JSON hanyalah teks yang memuat path yang sama.
     *
     * @param  array<string, int>  $byPath
     * @param  array<string, array<int, string>>  $byBasename
     * @return array<int, string>
     */
    private function pathsIn(string $value, array $byPath, array $byBasename): array
    {
        $extensions = implode('|', self::EXTENSIONS);

        if (! preg_match_all('#[\w][\w\-/.%()+ ]*\.(?:' . $extensions . ')#i', $value, $matches)) {
            return [];
        }

        $found = [];

        foreach ($matches[0] as $token) {
            $path = $this->normalise($token);

            if (isset($byPath[$path])) {
                $found[$path] = true;

                continue;
            }

            // Tidak cocok persis: coba lewat nama berkas, TAPI hanya bila namanya
            // unik. Bila dua folder punya nama berkas yang sama, menebak salah
            // satunya membuat berkas yang lain tampak "tidak terpakai" -- dan
            // berkas tidak terpakai boleh dihapus dari halaman ini. Lebih aman
            // tidak menghitungnya sama sekali.
            $base = basename($path);

            if (isset($byBasename[$base]) && count($byBasename[$base]) === 1) {
                $found[$byBasename[$base][0]] = true;
            }
        }

        return array_keys($found);
    }

    /** Membuang awalan URL / /storage/ sehingga tersisa path relatif disk. */
    private function normalise(string $token): string
    {
        $token = trim($token);

        if (($pos = stripos($token, '/storage/')) !== false) {
            $token = substr($token, $pos + strlen('/storage/'));
        }

        return ltrim($token, '/');
    }

    /** @return array<int, string> */
    private function contentTables(): array
    {
        $tables = array_map(
            fn ($t) => is_array($t) ? ($t['name'] ?? '') : (string) $t,
            Schema::getTables(),
        );

        return array_values(array_filter(
            $tables,
            fn ($t) => $t !== '' && ! in_array($t, self::SKIP_TABLES, true),
        ));
    }

    /** @return array<int, string> */
    private function textColumns(string $table): array
    {
        $columns = [];

        foreach (Schema::getColumns($table) as $column) {
            $type = strtolower((string) ($column['type_name'] ?? $column['type'] ?? ''));

            if (in_array($type, self::TEXT_TYPES, true)) {
                $columns[] = $column['name'];
            }
        }

        return $columns;
    }

    private function sizeOf($disk, string $path): int
    {
        try {
            return (int) $disk->size($path);
        } catch (\Throwable) {
            return 0;
        }
    }

    private function modifiedAt($disk, string $path): ?string
    {
        try {
            return date('Y-m-d H:i:s', $disk->lastModified($path));
        } catch (\Throwable) {
            return null;
        }
    }

    private function isLibraryAsset(string $path): bool
    {
        if (str_starts_with(basename($path), '.')) {
            return false;
        }

        return in_array(strtolower(pathinfo($path, PATHINFO_EXTENSION)), self::EXTENSIONS, true);
    }
}
