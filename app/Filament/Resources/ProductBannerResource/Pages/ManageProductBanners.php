<?php

namespace App\Filament\Resources\ProductBannerResource\Pages;

use App\Filament\Resources\ProductBannerResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageProductBanners extends ManageRecords
{
    protected static string $resource = ProductBannerResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\CreateAction::make()];
    }
}
