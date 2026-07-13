<?php

namespace App\Filament\Resources\InvestorAnnualResource\Pages;

use App\Filament\Resources\InvestorAnnualResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageInvestorAnnual extends ManageRecords
{
    protected static string $resource = InvestorAnnualResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\CreateAction::make()];
    }
}
