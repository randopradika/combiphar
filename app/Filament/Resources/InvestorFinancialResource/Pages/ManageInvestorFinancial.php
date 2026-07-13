<?php

namespace App\Filament\Resources\InvestorFinancialResource\Pages;

use App\Filament\Resources\InvestorFinancialResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageInvestorFinancial extends ManageRecords
{
    protected static string $resource = InvestorFinancialResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\CreateAction::make()];
    }
}
