<?php

namespace App\Filament\Resources\InvestorDocumentResource\Pages;

use App\Filament\Resources\InvestorDocumentResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListInvestorDocuments extends ListRecords
{
    protected static string $resource = InvestorDocumentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
