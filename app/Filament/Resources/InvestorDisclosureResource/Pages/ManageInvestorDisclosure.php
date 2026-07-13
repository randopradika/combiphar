<?php

namespace App\Filament\Resources\InvestorDisclosureResource\Pages;

use App\Filament\Resources\InvestorDisclosureResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageInvestorDisclosure extends ManageRecords
{
    protected static string $resource = InvestorDisclosureResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\CreateAction::make()];
    }
}
