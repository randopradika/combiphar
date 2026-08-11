<?php

namespace App\Filament\Pages;

use App\Models\Activity;
use Filament\Pages\Page as BasePage;
use Filament\Tables;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;

/**
 * Jejak perubahan konten: siapa mengubah apa, kapan.
 *
 * Sepenuhnya baca-saja, dan memang tidak boleh lebih: catatan yang bisa
 * disunting atau dihapus dari panel tidak bisa dipercaya sebagai jejak.
 */
class ActivityLog extends BasePage implements HasTable
{
    use InteractsWithTable;

    protected static string $view = 'filament.pages.activity-log';

    protected static ?string $navigationGroup = 'Pengguna & Akses';

    protected static ?string $navigationLabel = 'Riwayat Perubahan';

    protected static ?int $navigationSort = 2;

    protected static ?string $navigationIcon = 'heroicon-o-clock';

    protected static ?string $title = 'Riwayat Perubahan';

    public function getSubheading(): string
    {
        return 'Perubahan yang dilakukan lewat panel. Pesan dari formulir kontak dan pemindaian Pustaka Media sengaja tidak dicatat di sini.';
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(Activity::query())
            ->defaultSort('created_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Waktu')
                    ->dateTime('d M Y H:i')
                    ->sortable(),
                Tables\Columns\TextColumn::make('event')
                    ->label('Aksi')
                    ->badge()
                    ->state(fn (Activity $record) => $record->eventLabel())
                    ->color(fn (Activity $record) => match ($record->event) {
                        'created' => 'success',
                        'deleted' => 'danger',
                        default => 'warning',
                    }),
                Tables\Columns\TextColumn::make('subject_type')
                    ->label('Jenis')
                    ->badge()
                    ->color('gray')
                    ->state(fn (Activity $record) => $record->subjectName())
                    ->sortable(),
                Tables\Columns\TextColumn::make('subject_label')
                    ->label('Record')
                    ->wrap()
                    ->searchable()
                    ->placeholder('(tanpa nama)'),
                Tables\Columns\TextColumn::make('user_name')
                    ->label('Oleh')
                    ->searchable()
                    ->placeholder('(sistem)'),
                Tables\Columns\TextColumn::make('changed_fields')
                    ->label('Perubahan')
                    ->state(function (Activity $record) {
                        $fields = $record->changedFields();

                        if ($fields === []) {
                            return '-';
                        }

                        // Nama field lengkap membuat kolom melar; tiga pertama
                        // sudah cukup untuk mengenali perubahannya, selebihnya
                        // ada di tooltip.
                        return count($fields) > 3
                            ? implode(', ', array_slice($fields, 0, 3)).' +'.(count($fields) - 3)
                            : implode(', ', $fields);
                    })
                    ->tooltip(fn (Activity $record) => $record->changeSummary())
                    ->wrap(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('event')
                    ->label('Aksi')
                    ->options([
                        'created' => 'Dibuat',
                        'updated' => 'Diubah',
                        'deleted' => 'Dihapus',
                    ]),
                Tables\Filters\SelectFilter::make('subject_type')
                    ->label('Jenis konten')
                    ->options(fn () => Activity::query()
                        ->distinct()
                        ->orderBy('subject_type')
                        ->pluck('subject_type', 'subject_type')
                        ->map(fn ($t) => class_basename($t))
                        ->all()),
                Tables\Filters\SelectFilter::make('user_name')
                    ->label('Oleh')
                    ->options(fn () => Activity::query()
                        ->whereNotNull('user_name')
                        ->distinct()
                        ->orderBy('user_name')
                        ->pluck('user_name', 'user_name')
                        ->all()),
            ]);
    }
}
