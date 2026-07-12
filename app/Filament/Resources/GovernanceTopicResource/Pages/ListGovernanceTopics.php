<?php

namespace App\Filament\Resources\GovernanceTopicResource\Pages;

use App\Filament\Resources\GovernanceTopicResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListGovernanceTopics extends ListRecords
{
    protected static string $resource = GovernanceTopicResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
