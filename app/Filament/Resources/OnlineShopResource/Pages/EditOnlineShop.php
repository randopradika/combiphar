<?php

namespace App\Filament\Resources\OnlineShopResource\Pages;

use App\Filament\Resources\OnlineShopResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditOnlineShop extends EditRecord
{
    protected static string $resource = OnlineShopResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
