<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\ContactMessageResource;
use App\Models\ContactMessage;
use Filament\Support\Enums\FontWeight;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;

/**
 * The newest contact-form messages, right on the dashboard. Rows link to the
 * message in ContactMessageResource. Auto-registered via discoverWidgets().
 */
class LatestContactMessages extends TableWidget
{
    protected static ?int $sort = 5;

    protected int|string|array $columnSpan = 'full';

    protected static ?string $heading = 'Pesan Terbaru';

    public function table(Table $table): Table
    {
        return $table
            ->query(ContactMessage::query()->latest())
            ->paginated([5, 10, 25])
            ->defaultPaginationPageOption(5)
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nama')
                    ->weight(FontWeight::SemiBold),
                Tables\Columns\TextColumn::make('email')
                    ->label('Email')
                    ->color('gray'),
                Tables\Columns\TextColumn::make('subject')
                    ->label('Subjek')
                    ->limit(40)
                    ->placeholder('—'),
                Tables\Columns\TextColumn::make('message')
                    ->label('Pesan')
                    ->limit(70)
                    ->color('gray'),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Diterima')
                    ->since()
                    ->dateTimeTooltip(),
            ])
            ->recordUrl(fn (ContactMessage $record): string => ContactMessageResource::getUrl('edit', ['record' => $record]))
            ->emptyStateHeading('Belum ada pesan masuk')
            ->emptyStateIcon('heroicon-o-envelope-open');
    }
}
