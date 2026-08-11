<?php

namespace App\Filament\Resources\InvestorHubCardResource\Pages;

use App\Filament\Concerns\HasGalleryToggle;
use App\Filament\Resources\InvestorHubCardResource;
use Filament\Resources\Pages\ManageRecords;

class ManageInvestorHubCards extends ManageRecords
{
    use HasGalleryToggle;

    protected static string $resource = InvestorHubCardResource::class;

    /** Fixed set seeded by migration — no create button. */
    protected function getHeaderActions(): array
    {
        return [
            $this->getGalleryToggleAction(),
        ];
    }
}
