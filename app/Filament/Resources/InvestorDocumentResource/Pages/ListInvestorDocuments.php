<?php

namespace App\Filament\Resources\InvestorDocumentResource\Pages;

use App\Filament\Resources\InvestorDocumentResource;
use Filament\Actions;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListInvestorDocuments extends ListRecords
{
    protected static string $resource = InvestorDocumentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    /**
     * Satu tab per kategori, dibangun dari InvestorDocumentResource::CATEGORIES —
     * menggantikan lima menu terpisah yang dulu hanya berbeda satu kolom.
     * Kategori baru otomatis mendapat tabnya sendiri.
     */
    public function getTabs(): array
    {
        $tabs = ['semua' => Tab::make('Semua')];

        foreach (InvestorDocumentResource::CATEGORIES as $value => $label) {
            $tabs[$value] = Tab::make($label)
                ->modifyQueryUsing(fn (Builder $query) => $query->where('category', $value));
        }

        return $tabs;
    }
}
