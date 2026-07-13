<?php

namespace App\Filament\Resources\ProductResource\Pages;

use App\Filament\Resources\ProductResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateProduct extends CreateRecord
{
    protected static string $resource = ProductResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        if (empty($data['product_category_id'] ?? null)) {
            $data['product_category_id'] = $data['top_category_id'] ?? null;
        }
        unset($data['top_category_id']);

        return $data;
    }
}
