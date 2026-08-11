<?php

namespace App\Filament\Resources\AboutHistoryResource\Pages;

use App\Filament\Concerns\HasGalleryToggle;
use App\Filament\Resources\AboutHistoryResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListAboutHistories extends ListRecords
{
    use HasGalleryToggle;

    protected static string $resource = AboutHistoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            $this->getGalleryToggleAction(),
            Actions\CreateAction::make(),
        ];
    }
}
