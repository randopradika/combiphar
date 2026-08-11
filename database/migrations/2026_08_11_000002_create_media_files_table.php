<?php

use App\Support\MediaScanner;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

/**
 * Indeks berkas yang sudah diunggah, supaya ada satu tempat untuk melihat
 * seluruh aset situs.
 *
 * Sampai sekarang gambar hanya diunggah per record: logo yang sama dipakai di
 * lima tempat berarti lima berkas, tanpa cara mengetahui sebuah berkas dipakai
 * di mana — atau apakah masih dipakai sama sekali.
 *
 * Tabel ini TIDAK menggantikan kolom path yang sudah ada. Ia sekadar indeks
 * yang dibangun ulang dari isi disk + basis data oleh App\Support\MediaScanner,
 * sehingga tidak ada satu pun field unggahan yang perlu diubah.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('media_files', function (Blueprint $table) {
            $table->id();
            // Relatif terhadap disk 'public', persis seperti yang tersimpan di
            // kolom-kolom unggahan (mis. "awards/2019-contoh.jpg").
            // 512 x 4 byte masih di bawah batas indeks InnoDB (3072 byte).
            $table->string('path', 512)->unique();
            $table->string('folder')->default('');
            $table->string('filename');
            $table->string('extension', 16)->nullable();
            $table->unsignedBigInteger('size')->default(0);
            $table->unsignedInteger('usage_count')->default(0);
            // Daftar {table, column, id} tempat berkas ini dirujuk. Dibatasi
            // jumlahnya; usage_count tetap menyimpan total sebenarnya.
            $table->json('used_by')->nullable();
            $table->timestamp('file_modified_at')->nullable();
            $table->timestamp('scanned_at')->nullable();
            $table->timestamps();

            $table->index('folder');
            $table->index('usage_count');
        });

        // Mengisi sendiri supaya halaman langsung berguna begitu migrasi jalan --
        // baris media bersifat per-environment, jadi ini satu-satunya cara isinya
        // sampai ke dev. Kegagalan pemindaian tidak boleh menjatuhkan deploy:
        // tabelnya sudah terbentuk dan tombol "Pindai ulang" bisa dipakai kapan saja.
        try {
            app(MediaScanner::class)->scan();
        } catch (\Throwable $e) {
            Log::warning('Pemindaian media awal gagal: ' . $e->getMessage());
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('media_files');
    }
};
