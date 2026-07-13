<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductBannerResource\Pages;
use App\Models\ProductBanner;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ProductBannerResource extends Resource
{
    protected static ?string $model = ProductBanner::class;

    protected static ?string $navigationGroup = 'Beranda';

    protected static ?string $navigationLabel = 'Banner Produk';

    protected static ?int $navigationSort = 2;

    protected static ?string $navigationIcon = 'heroicon-o-photo';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('title_id')->label('Judul (ID)')->required()->maxLength(255),
            Forms\Components\TextInput::make('title_en')->label('Title (EN)')->maxLength(255),
            Forms\Components\FileUpload::make('image')->label('Banner')->image()->imageEditor(),
            Forms\Components\TextInput::make('link')->label('Link (opsional)')->maxLength(255),
            Forms\Components\Hidden::make('sort')->default(fn () => (static::getModel()::max('sort') ?? 0) + 1),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->defaultSort('sort')->columns([
            Tables\Columns\TextColumn::make('title_id')->label('Judul')->searchable(),
            Tables\Columns\ImageColumn::make('image'),
            Tables\Columns\TextColumn::make('sort')->numeric()->sortable(),
        ])->actions([
            Tables\Actions\EditAction::make(),
        ])->bulkActions([
            Tables\Actions\BulkActionGroup::make([Tables\Actions\DeleteBulkAction::make()]),
        ]);
    }

    public static function getPages(): array
    {
        return ['index' => Pages\ManageProductBanners::route('/')];
    }
}
