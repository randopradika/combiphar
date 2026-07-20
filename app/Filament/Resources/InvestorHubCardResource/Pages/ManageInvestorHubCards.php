<?php

namespace App\Filament\Resources\InvestorHubCardResource\Pages;

use App\Filament\Resources\InvestorHubCardResource;
use Filament\Resources\Pages\ManageRecords;

class ManageInvestorHubCards extends ManageRecords
{
    protected static string $resource = InvestorHubCardResource::class;

    /** Fixed set seeded by migration — no create button. */
    protected function getHeaderActions(): array
    {
        return [];
    }
}
