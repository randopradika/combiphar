<?php

namespace App\Filament\Resources\CsrProgramResource\Pages;

use App\Filament\Resources\CsrProgramResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditCsrProgram extends EditRecord
{
    protected static string $resource = CsrProgramResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    /**
     * Blok tim/event memakai bidang khusus formulir `gallery_files` (lihat
     * catatan di CsrProgramResource): satu kolom tidak boleh diikat oleh dua
     * komponen. Petakan kembali ke kolom `gallery` di sini.
     *
     * Untuk baris lain bidang ini tersembunyi dan tidak ikut dikirim, sehingga
     * galeri berketerangan milik Repeater tidak tersentuh.
     */
    protected function mutateFormDataBeforeSave(array $data): array
    {
        // Diputuskan dari isi baris, bukan dari ada/tidaknya kunci: bila suatu
        // saat bidang tersembunyi ikut terkirim, galeri berketerangan milik
        // program CSR tidak boleh tertimpa daftar kosong.
        if (CsrProgramResource::usesPlainGallery($data['category'] ?? null, $data['parent_id'] ?? null)) {
            $data['gallery'] = array_values(array_filter((array) ($data['gallery_files'] ?? [])));
        }

        unset($data['gallery_files']);

        return $data;
    }
}
