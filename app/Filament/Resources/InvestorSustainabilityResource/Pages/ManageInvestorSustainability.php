<?php

namespace App\Filament\Resources\InvestorSustainabilityResource\Pages;

use App\Filament\Resources\InvestorSustainabilityResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageInvestorSustainability extends ManageRecords
{
    protected static string $resource = InvestorSustainabilityResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\CreateAction::make()];
    }
}
