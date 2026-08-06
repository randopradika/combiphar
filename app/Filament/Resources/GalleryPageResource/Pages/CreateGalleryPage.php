<?php

namespace App\Filament\Resources\GalleryPageResource\Pages;

use App\Filament\Resources\GalleryPageResource;
use App\Models\CsrProgram;
use Filament\Resources\Pages\CreateRecord;

class CreateGalleryPage extends CreateRecord
{
    protected static string $resource = GalleryPageResource::class;

    /**
     * An event inherits its parent's category and carries no layout of its own.
     *
     * Both matter for where the row shows up: the CSR page builds its cards from
     * `whereNull('parent_id')`, so an event never becomes a card whatever its
     * category — but this resource scopes on `category != 'sports'`, and a null
     * category would drop the event out of the menu the moment it was saved.
     * `layout` belongs to the main page; leaving it set on a child would make
     * the child look like a main page to the scope query.
     */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        if (! blank($data['parent_id'] ?? null)) {
            $data['category'] = CsrProgram::whereKey($data['parent_id'])->value('category');
            $data['layout'] = null;
            $data['slug'] = null;
        }

        return $data;
    }
}
