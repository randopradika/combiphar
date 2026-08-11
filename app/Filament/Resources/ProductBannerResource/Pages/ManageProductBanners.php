<?php

namespace App\Filament\Resources\ProductBannerResource\Pages;

use App\Filament\Concerns\HasGalleryToggle;
use App\Filament\Resources\ProductBannerResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageProductBanners extends ManageRecords
{
    use HasGalleryToggle;

    protected static string $resource = ProductBannerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            $this->getGalleryToggleAction(),
            Actions\CreateAction::make(),
        ];
    }
}
