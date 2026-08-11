<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Catatan perubahan konten: siapa mengubah apa, kapan.
 *
 * Sampai sekarang panel tidak menyimpan jejak sama sekali -- bila sebuah teks
 * berubah atau sebuah record hilang, tidak ada cara mengetahui siapa yang
 * melakukannya, apalagi isi sebelumnya.
 *
 * Sengaja ditulis sendiri, tanpa paket tambahan: kebutuhannya sempit dan repo
 * ini sudah punya catatan soal kebersihan dependensi (satu plugin Filament
 * berstatus abandoned, `composer audit` belum ada di CI).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('activity_log', function (Blueprint $table) {
            $table->id();
            $table->string('event', 16);
            $table->string('subject_type');
            $table->unsignedBigInteger('subject_id')->nullable();
            // Nama record disalin saat penulisan: setelah record dihapus,
            // subject_id tidak bisa lagi dipakai untuk mengambil judulnya.
            $table->string('subject_label')->nullable();
            $table->foreignId('user_id')->nullable();
            // Sama alasannya: akun bisa dihapus, catatannya harus tetap terbaca.
            $table->string('user_name')->nullable();
            // Hanya field yang benar-benar berubah, dalam bentuk {old, new}.
            //
            // JANGAN menamainya "changes": Eloquent\Model sudah punya properti
            // protected $changes. Penulisan tetap berhasil (dari luar kelas
            // __set yang jalan), tetapi pembacaan DI DALAM model mengembalikan
            // properti bawaan itu -- selalu kosong -- bukan isi kolomnya.
            $table->json('changed_fields')->nullable();
            $table->timestamp('created_at')->nullable();

            $table->index(['subject_type', 'subject_id']);
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('activity_log');
    }
};
