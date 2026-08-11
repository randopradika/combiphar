<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductResource\Pages;
use App\Models\OnlineShop;
use App\Models\Product;
use App\Models\ProductCategory;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Support\Enums\FontWeight;
use Filament\Tables;
use Filament\Tables\Table;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static ?string $navigationGroup = 'Konten';

    protected static ?string $navigationLabel = 'Produk';

    protected static ?int $navigationSort = 2;

    protected static ?string $navigationIcon = 'heroicon-o-cube';

    protected static ?string $recordTitleAttribute = 'name_id';

    /**
     * Fields the panel-wide search box looks at. Both language columns are
     * listed so a search works whichever language the editor thinks in.
     */
    public static function getGloballySearchableAttributes(): array
    {
        return ['name_id', 'name_en'];
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('top_category_id')
                    ->label('Kategori')
                    ->options(fn () => ProductCategory::whereNull('parent_id')->orderBy('sort')->pluck('name_id', 'id'))
                    ->required()
                    ->live()
                    ->afterStateHydrated(function (Forms\Set $set, ?Product $record) {
                        if ($record && $record->category) {
                            $set('top_category_id', $record->category->parent_id ?? $record->category->id);
                        }
                    })
                    ->afterStateUpdated(function (Forms\Set $set, $state) {
                        $hasChildren = ProductCategory::where('parent_id', $state)->exists();
                        $set('product_category_id', $hasChildren ? null : $state);
                    }),
                Forms\Components\Select::make('product_category_id')
                    ->label('Sub-Kategori')
                    ->helperText('Pilih sub-kategori (hanya untuk kategori yang memiliki sub-kategori).')
                    ->options(fn (Forms\Get $get) => ProductCategory::where('parent_id', $get('top_category_id'))->orderBy('sort')->pluck('name_id', 'id'))
                    ->visible(fn (Forms\Get $get) => $get('top_category_id') && ProductCategory::where('parent_id', $get('top_category_id'))->exists())
                    ->required(fn (Forms\Get $get) => $get('top_category_id') && ProductCategory::where('parent_id', $get('top_category_id'))->exists())
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
                    ->options(fn () => OnlineShop::orderBy('sort')->pluck('name', 'id'))
                    ->formatStateUsing(fn ($state) => $state ?? OnlineShop::defaultIds())
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
        // Katalog produk dikenali dari kemasannya. Galeri jadi tampilan awal;
        // tabel tetap tersedia lewat tombol di header untuk kerja massal.
        $livewire = $table->getLivewire();
        $gallery = method_exists($livewire, 'isGallery') && $livewire->isGallery();

        $table = $gallery
            ? $table->columns(static::galleryColumns())->contentGrid(['sm' => 2, 'lg' => 3, '2xl' => 4])
            : $table->columns(static::listColumns());

        return $table
            ->filters([
                Tables\Filters\SelectFilter::make('category')
                    ->label('Kategori')
                    ->relationship('category', 'name_id')
                    ->searchable()
                    ->preload(),
            ])
            ->emptyStateHeading('Belum ada produk')
            ->emptyStateDescription('Produk tampil di halaman Produk, dikelompokkan menurut kategori dan sub-kategorinya.')
            ->emptyStateIcon('heroicon-o-cube')
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    private static function listColumns(): array
    {
        return [
            Tables\Columns\ImageColumn::make('image')
                ->label(''),
            Tables\Columns\TextColumn::make('name_id')
                ->label('Nama')
                ->searchable()
                ->wrap(),
            Tables\Columns\TextColumn::make('name_en')
                ->label('Name (EN)')
                ->searchable()
                ->toggleable(isToggledHiddenByDefault: true),
            Tables\Columns\TextColumn::make('category.name_id')
                ->label('Kategori')
                ->badge()
                ->color('gray')
                ->sortable(),
            Tables\Columns\TextColumn::make('slug')
                ->searchable()
                ->toggleable(isToggledHiddenByDefault: true),
            Tables\Columns\TextColumn::make('updated_at')
                ->label('Diperbarui')
                ->since()
                ->dateTimeTooltip()
                ->sortable(),
        ];
    }

    private static function galleryColumns(): array
    {
        return [
            Tables\Columns\Layout\Stack::make([
                Tables\Columns\ImageColumn::make('image')
                    ->height(160)
                    ->extraImgAttributes(['class' => 'w-full h-40 object-contain bg-white rounded-lg p-3']),
                Tables\Columns\Layout\Stack::make([
                    Tables\Columns\TextColumn::make('name_id')
                        ->weight(FontWeight::SemiBold)
                        ->searchable()
                        ->wrap()
                        ->limit(70),
                    Tables\Columns\TextColumn::make('category.name_id')
                        ->badge()
                        ->color('gray'),
                ])->space(1),
            ])->space(2),
        ];
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
