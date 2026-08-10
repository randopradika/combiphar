<?php

namespace App\Filament\Resources\NavItemResource\Pages;

use App\Filament\Resources\NavItemResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditNavItem extends EditRecord
{
    protected static string $resource = NavItemResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Menghapus item induk ikut menghapus sub-itemnya (cascade pada
            // foreign key), jadi konfirmasinya menyebutkan hal itu.
            Actions\DeleteAction::make()
                ->modalDescription(fn () => $this->getRecord()->children()->count() > 0
                    ? 'Sub-item di bawahnya akan ikut terhapus.'
                    : null),
        ];
    }
}
