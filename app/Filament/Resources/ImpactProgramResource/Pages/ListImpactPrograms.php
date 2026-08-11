<?php

namespace App\Filament\Resources\ImpactProgramResource\Pages;

use App\Filament\Concerns\HasGalleryToggle;
use App\Filament\Resources\ImpactProgramResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListImpactPrograms extends ListRecords
{
    use HasGalleryToggle;

    protected static string $resource = ImpactProgramResource::class;

    protected function getHeaderActions(): array
    {
        return [
            $this->getGalleryToggleAction(),
            Actions\CreateAction::make(),
        ];
    }
}
