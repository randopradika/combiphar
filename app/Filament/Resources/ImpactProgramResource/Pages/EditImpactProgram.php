<?php

namespace App\Filament\Resources\ImpactProgramResource\Pages;

use App\Filament\Resources\ImpactProgramResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditImpactProgram extends EditRecord
{
    protected static string $resource = ImpactProgramResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
