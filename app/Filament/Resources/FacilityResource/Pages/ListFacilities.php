<?php

namespace App\Filament\Resources\FacilityResource\Pages;

use App\Filament\Concerns\HasGalleryToggle;
use App\Filament\Resources\FacilityResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListFacilities extends ListRecords
{
    use HasGalleryToggle;

    protected static string $resource = FacilityResource::class;

    protected function getHeaderActions(): array
    {
        return [
            $this->getGalleryToggleAction(),
            Actions\CreateAction::make(),
        ];
    }
}
