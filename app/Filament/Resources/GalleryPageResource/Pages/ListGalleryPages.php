<?php

namespace App\Filament\Resources\GalleryPageResource\Pages;

use App\Filament\Resources\GalleryPageResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListGalleryPages extends ListRecords
{
    protected static string $resource = GalleryPageResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Adds an EVENT under Environmental or Education. Main pages are
            // created in "Program CSR"; CreateGalleryPage fills in the category
            // an event inherits from its parent.
            Actions\CreateAction::make()->label('Tambah Event'),
        ];
    }
}
