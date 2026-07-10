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
}
