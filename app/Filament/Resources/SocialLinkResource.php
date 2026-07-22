<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SocialLinkResource\Pages;
use App\Models\SocialLink;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class SocialLinkResource extends Resource
{
    protected static ?string $model = SocialLink::class;

    protected static ?string $navigationGroup = 'Beranda';

    protected static ?string $navigationLabel = 'Media Sosial (Footer)';

    protected static ?int $navigationSort = 9;

    protected static ?string $navigationIcon = 'heroicon-o-share';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('name')
                ->label('Nama (mis. Instagram)')
                ->required()
                ->maxLength(255),
            Forms\Components\TextInput::make('url')
                ->label('URL')
                ->url()
                ->maxLength(255),
            Forms\Components\FileUpload::make('icon')
                ->label('Ikon Footer')
                ->helperText('Tampil di footer gelap — gunakan ikon putih / transparan (PNG/SVG).')
                ->image()
                ->imageEditor(),
            Forms\Components\FileUpload::make('product_icon')
                ->label('Ikon Produk (Informasi Lebih Lanjut)')
                ->helperText('Tampil di popup produk (latar terang) — gunakan logo berwarna. Berbeda dari ikon footer. Kosongkan bila tidak dipakai.')
                ->image()
                ->imageEditor(),
            Forms\Components\Hidden::make('sort')->default(fn () => (static::getModel()::max('sort') ?? 0) + 1),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('sort')
            ->reorderable('sort')
            ->columns([
                Tables\Columns\TextColumn::make('name')->searchable(),
                Tables\Columns\ImageColumn::make('icon'),
                Tables\Columns\TextColumn::make('url')->searchable(),
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
            'index' => Pages\ListSocialLinks::route('/'),
            'create' => Pages\CreateSocialLink::route('/create'),
            'edit' => Pages\EditSocialLink::route('/{record}/edit'),
        ];
    }
}
