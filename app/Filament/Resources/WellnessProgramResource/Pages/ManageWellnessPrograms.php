<?php

namespace App\Filament\Resources\WellnessProgramResource\Pages;

use App\Filament\Resources\WellnessProgramResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageWellnessPrograms extends ManageRecords
{
    protected static string $resource = WellnessProgramResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()->label('Tambah Program'),
        ];
    }
}
