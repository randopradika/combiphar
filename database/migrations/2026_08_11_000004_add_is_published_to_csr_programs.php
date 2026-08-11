<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Status terbit untuk program & halaman CSR.
 *
 * Artikel sudah lama punya published_at, jadi berita bisa disiapkan lebih dulu.
 * CSR tidak punya padanannya: menyimpan berarti langsung tayang.
 *
 * DEFAULT-NYA true, dan itu penting -- seluruh baris yang sudah ada harus tetap
 * tayang begitu migrasi jalan. Kolom dengan default false akan mengosongkan
 * halaman CSR di setiap environment saat deploy.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('csr_programs', function (Blueprint $table) {
            $table->boolean('is_published')->default(true)->after('sort');
        });
    }

    public function down(): void
    {
        Schema::table('csr_programs', function (Blueprint $table) {
            $table->dropColumn('is_published');
        });
    }
};
