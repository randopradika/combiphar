<?php

namespace App\Filament\Resources\CsrProgramResource\Pages;

use App\Filament\Resources\CsrProgramResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListCsrPrograms extends ListRecords
{
    protected static string $resource = CsrProgramResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
