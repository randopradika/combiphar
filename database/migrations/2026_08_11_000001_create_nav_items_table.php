<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Menu navigasi situs, dipindahkan dari lang/{id,en}/site.php ke basis data.
 *
 * Selama ini menambah satu tautan menu berarti menyunting berkas bahasa lalu
 * melakukan deploy — permintaan "kecil" yang paling sering muncul di situs
 * pemasaran, tetapi selalu membutuhkan developer.
 *
 * Baris di sini di-seed dari isi berkas bahasa saat ini, jadi tampilan menu
 * tidak berubah sedikit pun setelah migrasi dijalankan. Bila tabel kosong,
 * HandleInertiaRequests tetap memakai berkas bahasa sebagai cadangan.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('nav_items', function (Blueprint $table) {
            $table->id();
            $table->string('section');
            $table->foreignId('parent_id')->nullable()->constrained('nav_items')->cascadeOnDelete();
            $table->string('label_id');
            $table->string('label_en')->nullable();
            $table->string('suffix')->nullable();
            // Menunjuk URL bagian LAIN: dokumen governance milik Investor
            // sebenarnya berada di bawah halaman CSR.
            $table->string('base')->nullable();
            $table->boolean('is_head')->default(false);
            $table->integer('sort')->default(0);
            $table->timestamps();

            $table->index(['section', 'parent_id', 'sort']);
        });

        $this->seedFromLangFiles();
    }

    public function down(): void
    {
        Schema::dropIfExists('nav_items');
    }

    /**
     * Membaca berkas bahasa secara langsung, bukan lewat trans(): locale saat
     * migrasi berjalan tidak dapat diandalkan, dan kedua bahasa dibutuhkan
     * sekaligus.
     */
    private function seedFromLangFiles(): void
    {
        $id = $this->menus('id');
        $en = $this->menus('en');

        if (! $id) {
            return;
        }

        foreach ($id as $section => $items) {
            $this->insertItems($section, $items, $en[$section] ?? [], null);
        }
    }

    private function menus(string $locale): array
    {
        $path = base_path("lang/{$locale}/site.php");

        if (! is_file($path)) {
            return [];
        }

        $lang = include $path;

        return $lang['nav']['menus'] ?? [];
    }

    /** @param  array<int, array<string, mixed>>  $items */
    private function insertItems(string $section, array $items, array $enItems, ?int $parentId): void
    {
        foreach (array_values($items) as $i => $item) {
            // Kedua berkas bahasa punya urutan yang sama, jadi pasangannya
            // dicocokkan berdasarkan posisi. Label EN dibiarkan null bila tidak
            // ada, sehingga tr() jatuh ke bahasa cadangan alih-alih menyimpan
            // teks Indonesia sebagai "terjemahan".
            $en = $enItems[$i] ?? [];

            $navId = DB::table('nav_items')->insertGetId([
                'section' => $section,
                'parent_id' => $parentId,
                'label_id' => $item['label'] ?? '',
                'label_en' => $en['label'] ?? null,
                'suffix' => $item['suffix'] ?? null,
                'base' => $item['base'] ?? null,
                'is_head' => (bool) ($item['head'] ?? false),
                'sort' => $i + 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            if (! empty($item['children'])) {
                $this->insertItems($section, $item['children'], $en['children'] ?? [], $navId);
            }
        }
    }
};
