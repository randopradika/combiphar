<?php

namespace App\Filament\Resources\GlobalSiteResource\Pages;

use App\Filament\Resources\GlobalSiteResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditGlobalSite extends EditRecord
{
    protected static string $resource = GlobalSiteResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
