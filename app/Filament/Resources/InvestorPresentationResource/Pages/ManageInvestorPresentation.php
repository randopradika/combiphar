<?php

namespace App\Filament\Resources\InvestorPresentationResource\Pages;

use App\Filament\Resources\InvestorPresentationResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageInvestorPresentation extends ManageRecords
{
    protected static string $resource = InvestorPresentationResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\CreateAction::make()];
    }
}
