<?php

namespace App\Filament\Resources\HeroAwardResource\Pages;

use App\Filament\Resources\HeroAwardResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageHeroAwards extends ManageRecords
{
    protected static string $resource = HeroAwardResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
