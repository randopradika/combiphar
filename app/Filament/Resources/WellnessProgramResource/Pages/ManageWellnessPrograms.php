<?php

namespace App\Filament\Resources\WellnessProgramResource\Pages;

use App\Filament\Concerns\HasGalleryToggle;
use App\Filament\Resources\WellnessProgramResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageWellnessPrograms extends ManageRecords
{
    use HasGalleryToggle;

    protected static string $resource = WellnessProgramResource::class;

    protected function getHeaderActions(): array
    {
        return [
            $this->getGalleryToggleAction(),
            Actions\CreateAction::make()->label('Tambah Program'),
        ];
    }
}
