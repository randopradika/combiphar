<?php

namespace App\Filament\Resources\AwardResource\Pages;

use App\Filament\Resources\AwardResource;
use Filament\Actions;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListAwards extends ListRecords
{
    protected static string $resource = AwardResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    /**
     * Menggantikan menu "Penghargaan Unggulan" yang dulu terpisah: pemisahannya
     * kini berupa tab di dalam satu daftar, sehingga sebuah penghargaan tidak
     * perlu berpindah menu untuk diunggulkan.
     */
    public function getTabs(): array
    {
        return [
            'semua' => Tab::make('Semua'),
            'unggulan' => Tab::make('Unggulan')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('is_hero', true)),
            'popup' => Tab::make('Daftar Penghargaan')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('is_hero', false)),
        ];
    }
}
