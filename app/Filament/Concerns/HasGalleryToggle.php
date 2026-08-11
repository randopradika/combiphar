<?php

namespace App\Filament\Concerns;

use Filament\Actions;

/**
 * Tombol ganti tampilan Galeri / Tabel untuk halaman daftar.
 *
 * Sebagian isi CMS ini dikenali dari GAMBARNYA, bukan dari judulnya --
 * penghargaan, foto dewan, kemasan produk, sertifikat akreditasi. Menampilkannya
 * sebagai tabel teks dengan gambar kecil di kolom keempat memaksa MEMBACA di
 * tempat yang seharusnya cukup MELIHAT, dan pada daftar penghargaan yang berisi
 * puluhan baris itu berarti menelusuri dua kolom judul (ID dan EN) yang isinya
 * nyaris sama.
 *
 * Tampilan tabel tetap dipertahankan, bukan diganti: memilih banyak baris,
 * mengurutkan, dan membandingkan banyak kolom sekaligus masih lebih mudah di
 * tabel. Pilihan tampilan disimpan di session per halaman, jadi tidak perlu
 * diatur ulang setiap kali editor kembali ke daftar yang sama.
 */
trait HasGalleryToggle
{
    /**
     * Tampilan awal halaman ini sebelum editor memilih sendiri.
     * Halaman yang isinya bergambar memakai true; sisanya false.
     *
     * ⚠️ Halaman yang ingin berbeda harus menimpa galleryByDefault(), BUKAN
     * properti ini: mendeklarasikan ulang properti trait dengan nilai bawaan
     * yang berbeda adalah komposisi yang tidak sah di PHP dan langsung membuat
     * halamannya fatal error.
     */
    protected static bool $galleryByDefault = true;

    public function isGallery(): bool
    {
        return (bool) session()->get($this->gallerySessionKey(), $this->galleryByDefault());
    }

    protected function galleryByDefault(): bool
    {
        return static::$galleryByDefault;
    }

    public function toggleGallery(): void
    {
        session()->put($this->gallerySessionKey(), ! $this->isGallery());
    }

    protected function gallerySessionKey(): string
    {
        return 'cms.gallery.'.static::class;
    }

    /**
     * Tombolnya menyebut tampilan yang akan DITUJU, bukan yang sedang aktif --
     * label tombol yang menyebut keadaan sekarang selalu terbaca ambigu.
     */
    protected function getGalleryToggleAction(): Actions\Action
    {
        $gallery = $this->isGallery();

        return Actions\Action::make('ubahTampilan')
            ->label($gallery ? 'Tampilan tabel' : 'Tampilan galeri')
            ->icon($gallery ? 'heroicon-m-bars-3' : 'heroicon-m-squares-2x2')
            ->color('gray')
            ->action(fn () => $this->toggleGallery());
    }
}
