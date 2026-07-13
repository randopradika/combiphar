<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AboutHistoryResource\Pages;
use App\Models\AboutHistory;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class AboutHistoryResource extends Resource
{
    protected static ?string $model = AboutHistory::class;

    protected static ?string $navigationGroup = 'Tentang Kami';

    protected static ?string $navigationLabel = 'Sejarah';

    protected static ?int $navigationSort = 2;

    protected static ?string $navigationIcon = 'heroicon-o-clock';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('year')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Textarea::make('caption_id')
                    ->label('Teks (ID)')
                    ->rows(4)
                    ->columnSpanFull(),
                Forms\Components\Textarea::make('caption_en')
                    ->label('Text (EN)')
                    ->rows(4)
                    ->columnSpanFull(),
                Forms\Components\FileUpload::make('photo')->image()->imageEditor(),
                Forms\Components\Hidden::make('sort')->default(fn () => (static::getModel()::max('sort') ?? 0) + 1),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('sort')
            ->columns([
                Tables\Columns\TextColumn::make('year')->searchable(),
                Tables\Columns\ImageColumn::make('photo'),
                Tables\Columns\TextColumn::make('sort')->numeric()->sortable(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAboutHistories::route('/'),
            'create' => Pages\CreateAboutHistory::route('/create'),
            'edit' => Pages\EditAboutHistory::route('/{record}/edit'),
        ];
    }
}
