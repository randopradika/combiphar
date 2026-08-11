<?php

namespace App\Filament\Resources\ProductResource\Pages;

use App\Filament\Concerns\HasGalleryToggle;
use App\Filament\Resources\ProductResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListProducts extends ListRecords
{
    use HasGalleryToggle;

    protected static string $resource = ProductResource::class;

    protected function getHeaderActions(): array
    {
        return [
            $this->getGalleryToggleAction(),
            Actions\CreateAction::make(),
        ];
    }
}
