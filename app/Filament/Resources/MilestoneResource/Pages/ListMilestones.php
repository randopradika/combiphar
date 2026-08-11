<?php

namespace App\Filament\Resources\MilestoneResource\Pages;

use App\Filament\Concerns\HasGalleryToggle;
use App\Filament\Resources\MilestoneResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListMilestones extends ListRecords
{
    use HasGalleryToggle;

    protected static string $resource = MilestoneResource::class;

    protected function getHeaderActions(): array
    {
        return [
            $this->getGalleryToggleAction(),
            Actions\CreateAction::make(),
        ];
    }
}
