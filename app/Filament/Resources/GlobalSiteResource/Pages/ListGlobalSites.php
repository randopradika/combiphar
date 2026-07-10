<?php

namespace App\Filament\Resources\GlobalSiteResource\Pages;

use App\Filament\Resources\GlobalSiteResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListGlobalSites extends ListRecords
{
    protected static string $resource = GlobalSiteResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
