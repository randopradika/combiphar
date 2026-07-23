<?php

namespace App\Filament\Resources;

use App\Filament\Resources\HeroAwardResource\Pages;
use App\Models\Award;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

/**
 * Logo hero "Pencapaian & Penghargaan" di halaman Tentang Kami (maks. 7 tampil).
 * Terpisah dari menu "Penghargaan" (daftar popup "Daftar Penghargaan" per tahun).
 */
class HeroAwardResource extends Resource
{
    protected static ?string $model = Award::class;

    protected static ?string $navigationGroup = 'Tentang Kami';

    protected static ?string $navigationLabel = 'Penghargaan Hero';

    protected static ?int $navigationSort = 4;

    protected static ?string $navigationIcon = 'heroicon-o-star';

    protected static ?string $modelLabel = 'Penghargaan Hero';

    protected static ?string $pluralModelLabel = 'Penghargaan Hero';

    protected static ?string $slug = 'hero-awards';

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('is_hero', true);
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('title_id')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('title_en')
                    ->maxLength(255),
                Forms\Components\FileUpload::make('image')
                    ->image()
                    ->helperText('Logo tampil di bawah deskripsi Pencapaian & Penghargaan (maksimal 7 pertama).'),
                Forms\Components\Hidden::make('is_hero')->default(true),
                Forms\Components\Hidden::make('sort')->default(fn () => (Award::max('sort') ?? 0) + 1),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title_id')
                    ->searchable(),
                Tables\Columns\TextColumn::make('title_en')
                    ->searchable(),
                Tables\Columns\ImageColumn::make('image'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
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
            'index' => Pages\ManageHeroAwards::route('/'),
        ];
    }
}
