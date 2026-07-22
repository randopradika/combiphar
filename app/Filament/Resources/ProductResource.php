<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductResource\Pages;
use App\Filament\Resources\ProductResource\RelationManagers;
use App\Models\Product;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static ?string $navigationGroup = 'Produk';

    protected static ?string $navigationLabel = 'Daftar Produk';

    protected static ?int $navigationSort = 3;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('top_category_id')
                    ->label('Kategori')
                    ->options(fn () => \App\Models\ProductCategory::whereNull('parent_id')->orderBy('sort')->pluck('name_id', 'id'))
                    ->required()
                    ->live()
                    ->afterStateHydrated(function (Forms\Set $set, ?\App\Models\Product $record) {
                        if ($record && $record->category) {
                            $set('top_category_id', $record->category->parent_id ?? $record->category->id);
                        }
                    })
                    ->afterStateUpdated(function (Forms\Set $set, $state) {
                        $hasChildren = \App\Models\ProductCategory::where('parent_id', $state)->exists();
                        $set('product_category_id', $hasChildren ? null : $state);
                    }),
                Forms\Components\Select::make('product_category_id')
                    ->label('Sub-Kategori')
                    ->helperText('Pilih sub-kategori (hanya untuk kategori yang memiliki sub-kategori).')
                    ->options(fn (Forms\Get $get) => \App\Models\ProductCategory::where('parent_id', $get('top_category_id'))->orderBy('sort')->pluck('name_id', 'id'))
                    ->visible(fn (Forms\Get $get) => $get('top_category_id') && \App\Models\ProductCategory::where('parent_id', $get('top_category_id'))->exists())
                    ->required(fn (Forms\Get $get) => $get('top_category_id') && \App\Models\ProductCategory::where('parent_id', $get('top_category_id'))->exists())
                    ->dehydrated(true),
                Forms\Components\TextInput::make('slug')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('name_id')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('name_en')
                    ->maxLength(255),
                Forms\Components\Textarea::make('summary_id')
                    ->label('Deskripsi Singkat (Kartu) — ID')
                    ->helperText('Teks pendek yang tampil di kartu produk (desktop).')
                    ->rows(2)
                    ->columnSpanFull(),
                Forms\Components\Textarea::make('summary_en')
                    ->label('Deskripsi Singkat (Kartu) — EN')
                    ->rows(2)
                    ->columnSpanFull(),
                Forms\Components\Textarea::make('description_id')
                    ->label('Deskripsi (Popup Detail) — ID')
                    ->columnSpanFull(),
                Forms\Components\Textarea::make('description_en')
                    ->label('Deskripsi (Popup Detail) — EN')
                    ->columnSpanFull(),
                Forms\Components\FileUpload::make('image')
                    ->image(),
                Forms\Components\CheckboxList::make('shop_ids')
                    ->label('Toko Online')
                    ->helperText('Pilih toko tempat produk ini tersedia. Default: Tokopedia, Shopee, Blibli.')
                    ->options(fn () => \App\Models\OnlineShop::orderBy('sort')->pluck('name', 'id'))
                    ->formatStateUsing(fn ($state) => $state ?? \App\Models\OnlineShop::defaultIds())
                    ->bulkToggleable()
                    ->columns(2)
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('website_url')
                    ->label('Website Resmi (URL)')
                    ->helperText('Tampil sebagai tombol "Kunjungi Website" di popup produk. Kosongkan bila tidak ada.')
                    ->url()
                    ->maxLength(255)
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('instagram_url')
                    ->label('Instagram (URL)')
                    ->helperText('Ikon Instagram pada bagian "Informasi Lebih Lanjut". Kosongkan bila tidak ada.')
                    ->url()
                    ->maxLength(255),
                Forms\Components\TextInput::make('facebook_url')
                    ->label('Facebook (URL)')
                    ->helperText('Ikon Facebook pada bagian "Informasi Lebih Lanjut". Kosongkan bila tidak ada.')
                    ->url()
                    ->maxLength(255),
                Forms\Components\Hidden::make('sort')->default(fn () => (static::getModel()::max('sort') ?? 0) + 1),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('category.name_id')
                    ->label('Kategori')
                    ->sortable(),
                Tables\Columns\TextColumn::make('slug')
                    ->searchable(),
                Tables\Columns\TextColumn::make('name_id')
                    ->searchable(),
                Tables\Columns\TextColumn::make('name_en')
                    ->searchable(),
                Tables\Columns\ImageColumn::make('image'),
                Tables\Columns\TextColumn::make('sort')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('category')
                    ->label('Kategori')
                    ->relationship('category', 'name_id')
                    ->searchable()
                    ->preload(),
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

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProducts::route('/'),
            'create' => Pages\CreateProduct::route('/create'),
            'edit' => Pages\EditProduct::route('/{record}/edit'),
        ];
    }
}
