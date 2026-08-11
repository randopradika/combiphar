<?php

namespace App\Filament\Resources\PersonResource\Pages;

use App\Filament\Concerns\HasGalleryToggle;
use App\Filament\Resources\PersonResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPeople extends ListRecords
{
    use HasGalleryToggle;

    protected static string $resource = PersonResource::class;

    protected function getHeaderActions(): array
    {
        return [
            $this->getGalleryToggleAction(),
            Actions\CreateAction::make(),
        ];
    }
}
