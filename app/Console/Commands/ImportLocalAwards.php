<?php

namespace App\Console\Commands;

use App\Models\Award;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ImportLocalAwards extends Command
{
    protected $signature = 'awards:import-local
        {--path= : Folder holding the {year}-*.zip archives (default: base_path/award)}
        {--from-dir= : Import from a plain folder of {year}/ subfolders instead of zips}
        {--export= : Also write the imported rows to this JSON file (e.g. database/data/awards.json)}
        {--dry-run : List what would be imported without writing files or rows}';

    protected $description = 'Import award images — from per-year zip archives (award/{year}-*.zip) or a {year}/ folder tree (--from-dir) — into the Award CMS records for the "Pencapaian & Penghargaan" popup.';

    /** Image extensions we import (the popup renders <img>); everything else (PDF, …) is skipped. */
    private const IMAGE_EXT = ['jpg', 'jpeg', 'png', 'webp'];

    private int $sort = 0;

    private int $imported = 0;

    private int $skipped = 0;

    /** @var list<array{title_id:string,title_en:string,year:string,image:string}> */
    private array $rows = [];

    public function handle(): int
    {
        $this->sort = (int) (Award::max('sort') ?? 0);
        $dry = (bool) $this->option('dry-run');

        $status = $this->option('from-dir')
            ? $this->importFromDir((string) $this->option('from-dir'), $dry)
            : $this->importFromZips($this->option('path') ?: base_path('award'), $dry);

        if ($status !== self::SUCCESS) {
            return $status;
        }

        $verb = $dry ? 'Would import' : 'Imported/updated';
        $this->info("{$verb} {$this->imported} award images; skipped {$this->skipped} non-image files (PDFs, etc.).");

        if ($export = $this->option('export')) {
            $this->writeExport((string) $export, $dry);
        }

        return self::SUCCESS;
    }

    /**
     * Import a plain folder tree: {dir}/{year}/{Descriptive-Name}.jpg — the shape
     * of the hand-curated "Edited" award folder. Deterministic destinations, so a
     * re-run updates the same rows instead of duplicating them.
     */
    private function importFromDir(string $dir, bool $dry): int
    {
        if (! is_dir($dir)) {
            $this->error("Award folder not found: {$dir}");

            return self::FAILURE;
        }

        $years = glob(rtrim($dir, '/\\').'/*', GLOB_ONLYDIR) ?: [];
        sort($years);

        if (! $years) {
            $this->error("No {year}/ subfolders found in {$dir}");

            return self::FAILURE;
        }

        foreach ($years as $yearDir) {
            $year = basename($yearDir);
            if (! preg_match('/^\d{4}$/', $year)) {
                $this->warn("Skipping non-year folder: {$year}");

                continue;
            }

            $files = glob($yearDir.'/*') ?: [];
            sort($files);

            foreach ($files as $file) {
                if (! is_file($file)) {
                    continue;
                }

                $base = basename($file);
                if (str_starts_with($base, '.')) {
                    continue;
                }

                $this->importOne(
                    entryKey: "{$year}/{$base}",
                    base: $base,
                    year: $year,
                    dry: $dry,
                    read: fn () => file_get_contents($file),
                );
            }
        }

        return self::SUCCESS;
    }

    private function importFromZips(string $dir, bool $dry): int
    {
        if (! is_dir($dir)) {
            $this->error("Award folder not found: {$dir}");

            return self::FAILURE;
        }

        $zips = glob(rtrim($dir, '/\\').'/*.zip') ?: [];
        if (! $zips) {
            $this->error("No .zip archives found in {$dir}");

            return self::FAILURE;
        }

        foreach ($zips as $zipPath) {
            $year = $this->yearFromZipName(basename($zipPath));

            $zip = new \ZipArchive;
            if ($zip->open($zipPath) !== true) {
                $this->warn('Could not open '.basename($zipPath).' — skipped.');

                continue;
            }

            for ($i = 0; $i < $zip->numFiles; $i++) {
                $stat = $zip->statIndex($i);
                $name = $stat['name'] ?? '';

                // Skip directory entries and macOS resource-fork junk.
                if ($name === '' || str_ends_with($name, '/') || str_starts_with($name, '__MACOSX/')) {
                    continue;
                }

                $base = basename($name);
                if ($base === '' || str_starts_with($base, '.')) {
                    continue;
                }

                // Prefer the year from the entry's top folder (e.g. "2013/IMG.jpg"); fall back to the zip name.
                $this->importOne(
                    entryKey: $name,
                    base: $base,
                    year: $this->yearFromEntry($name) ?? $year,
                    dry: $dry,
                    read: fn () => $zip->getFromName($name),
                );
            }

            $zip->close();
        }

        return self::SUCCESS;
    }

    /**
     * Store one image and upsert its Award row. $entryKey is the stable source
     * path — it seeds the filename hash, so the destination (and therefore the
     * row this updates) is the same on every run.
     */
    private function importOne(string $entryKey, string $base, string $year, bool $dry, callable $read): void
    {
        $ext = strtolower(pathinfo($base, PATHINFO_EXTENSION));
        if (! in_array($ext, self::IMAGE_EXT, true)) {
            $this->skipped++;        // PDFs / certificates / other non-images

            return;
        }

        $slug = Str::slug(pathinfo($base, PATHINFO_FILENAME)) ?: 'award';
        $dest = "awards/{$year}/{$slug}-".substr(md5($entryKey), 0, 6).'.'.$ext;

        [$titleId, $titleEn] = $this->titlesFor($base, $year);

        $this->rows[] = [
            'title_id' => $titleId,
            'title_en' => $titleEn,
            'year' => $year,
            'image' => $dest,
        ];

        if ($dry) {
            $this->line("[{$year}] {$titleId}  ←  {$entryKey}");
            $this->imported++;

            return;
        }

        $bytes = $read();
        if ($bytes === false) {
            $this->warn("Could not read {$entryKey} — skipped.");
            $this->skipped++;
            array_pop($this->rows);

            return;
        }

        Storage::disk('public')->put($dest, $bytes);

        Award::updateOrCreate(
            ['image' => $dest],
            [
                'title_id' => $titleId,
                'title_en' => $titleEn,
                'year' => $year,
                'is_hero' => false,
                'sort' => ++$this->sort,
            ]
        );
        $this->imported++;
    }

    /**
     * Write the imported rows as JSON. This is the file the deploy-time
     * migration reads to recreate the same rows on every environment, so it must
     * be regenerated (and committed) whenever the image set changes.
     */
    private function writeExport(string $path, bool $dry): void
    {
        if (! str_starts_with($path, '/') && ! preg_match('/^[A-Za-z]:/', $path)) {
            $path = base_path($path);
        }

        if ($dry) {
            $this->line('Would write '.count($this->rows)." rows to {$path}");

            return;
        }

        file_put_contents(
            $path,
            json_encode($this->rows, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)."\n"
        );

        $this->info('Wrote '.count($this->rows)." rows to {$path}");
    }

    /** "2013-20260722T....zip" → "2013". */
    private function yearFromZipName(string $file): string
    {
        return preg_match('/^(\d{4})/', $file, $m) ? $m[1] : 'unknown';
    }

    /** "2013/IMG_8119.JPG" → "2013" when the top segment is a 4-digit year. */
    private function yearFromEntry(string $entry): ?string
    {
        $top = explode('/', $entry)[0];

        return preg_match('/^\d{4}$/', $top) ? $top : null;
    }

    /**
     * Build a bilingual caption. Descriptive filenames become the title; generic
     * camera names (IMG_8119, CML_7910, DSC0001) get a neutral "Penghargaan {year}".
     *
     * @return array{0:string,1:string}
     */
    private function titlesFor(string $base, string $year): array
    {
        $name = pathinfo($base, PATHINFO_FILENAME);

        // Common double extension in the source (e.g. "CML_7910.jpg.jpeg").
        $name = preg_replace('/\.(jpg|jpeg|png)$/i', '', $name);

        // Generic camera/export names carry no real title.
        if (preg_match('/^(IMG|CML|DSC|DSCF|PICT|photo|image)[\s_-]*\d+/i', trim($name)) || preg_match('/^\d+$/', trim($name))) {
            return ["Penghargaan {$year}", "Award {$year}"];
        }

        // Clean a descriptive filename into a readable caption. Hyphens are word
        // separators ("Top-Brand-Award" → "Top Brand Award") except between
        // digits, so a year range like "2010-2011" survives intact.
        $t = str_replace('_', ' ', $name);
        $t = preg_replace('/(?<!\d)-|-(?!\d)/', ' ', $t);
        $t = preg_replace('/^[^\p{L}\p{N}]+/u', '', $t);   // drop leading "· ", bullets, stray punctuation
        $t = preg_replace('/\s*\(\d+\)\s*$/', '', $t);     // drop trailing " (1)" duplicate markers
        $t = trim(preg_replace('/\s+/', ' ', $t));

        if ($t === '') {
            return ["Penghargaan {$year}", "Award {$year}"];
        }

        return [$t, $t];
    }
}
