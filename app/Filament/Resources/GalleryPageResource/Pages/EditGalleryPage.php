<?php

namespace App\Filament\Resources\GalleryPageResource\Pages;

use App\Filament\Resources\GalleryPageResource;
use App\Models\CsrProgram;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditGalleryPage extends EditRecord
{
    protected static string $resource = GalleryPageResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Events can be deleted here. A MAIN page cannot: it is also a card
            // on the CSR page, so removing it has consequences well beyond this
            // screen — that stays in "Program CSR", where they are visible.
            Actions\DeleteAction::make()
                ->visible(fn (CsrProgram $record) => filled($record->parent_id)),
        ];
    }
}
