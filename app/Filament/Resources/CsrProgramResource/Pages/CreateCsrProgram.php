<?php

namespace App\Filament\Resources\CsrProgramResource\Pages;

use App\Filament\Resources\CsrProgramResource;
use Filament\Resources\Pages\CreateRecord;

class CreateCsrProgram extends CreateRecord
{
    protected static string $resource = CsrProgramResource::class;

    /** Lihat EditCsrProgram: `gallery_files` dipetakan ke kolom `gallery`. */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        if (CsrProgramResource::usesPlainGallery($data['category'] ?? null, $data['parent_id'] ?? null)) {
            $data['gallery'] = array_values(array_filter((array) ($data['gallery_files'] ?? [])));
        }

        unset($data['gallery_files']);

        return $data;
    }
}
