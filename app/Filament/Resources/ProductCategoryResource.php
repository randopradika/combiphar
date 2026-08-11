<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductCategoryResource\Pages;
use App\Models\ProductCategory;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Support\Enums\FontWeight;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ProductCategoryResource extends Resource
{
    protected static ?string $model = ProductCategory::class;

    protected static ?string $navigationGroup = 'Konten';

    protected static ?string $navigationLabel = 'Kategori Produk';

    protected static ?int $navigationSort = 3;

    protected static ?string $navigationIcon = 'heroicon-o-tag';

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
            ->columns(3)
            ->schema([
                Forms\Components\Group::make()
                    ->columnSpan(['lg' => 2])
                    ->schema([
                        Forms\Components\Section::make('Isi kategori')
                            ->schema([
                                Forms\Components\Tabs::make('Bahasa')
                                    ->tabs([
                                        Forms\Components\Tabs\Tab::make('Bahasa Indonesia')
                                            ->schema(static::contentFields('id')),
                                        Forms\Components\Tabs\Tab::make('English')
                                            ->schema(static::contentFields('en')),
                                    ])
                                    ->columnSpanFull(),
                            ]),
                    ]),

                Forms\Components\Group::make()
                    ->columnSpan(['lg' => 1])
                    ->schema([
                        Forms\Components\Section::make('Penempatan')
                            ->schema([
                                Forms\Components\Select::make('parent_id')
                                    ->label('Induk Kategori')
                                    ->helperText('Kosongkan untuk kategori utama; pilih induk untuk menjadikannya sub-kategori.')
                                    ->relationship('parent', 'name_id', fn (Builder $query) => $query->whereNull('parent_id'))
                                    ->searchable()
                                    ->preload()
                                    ->nullable(),
                                Forms\Components\TextInput::make('slug')
                                    ->label('Slug')
                                    ->helperText('Dipakai pada tautan menu (?cat= dan ?sub=). Mengubahnya membuat tautan lama tidak lagi membuka kategori ini.')
                                    ->required()
                                    ->maxLength(255),
                            ]),

                        Forms\Components\Section::make('Gambar')
                            ->schema([
                                Forms\Components\FileUpload::make('image')
                                    ->label('Gambar kategori')
                                    ->image(),
                            ]),
                    ]),

                Forms\Components\Hidden::make('sort')->default(fn () => (static::getModel()::max('sort') ?? 0) + 1),
            ]);
    }

    /** Nama dan deskripsi kategori untuk satu bahasa. */
    private static function contentFields(string $locale): array
    {
        $isId = $locale === 'id';

        return [
            Forms\Components\TextInput::make("name_{$locale}")
                ->label($isId ? 'Nama' : 'Name')
                ->required($isId)
                ->maxLength(255)
                ->columnSpanFull(),
            Forms\Components\Textarea::make("description_{$locale}")
                ->label($isId ? 'Deskripsi' : 'Description')
                ->rows(4)
                ->columnSpanFull(),
        ];
    }

    public static function table(Table $table): Table
    {
        $livewire = $table->getLivewire();
        $gallery = method_exists($livewire, 'isGallery') && $livewire->isGallery();

        $table = $gallery
            ? $table->columns(static::galleryColumns())->contentGrid(['sm' => 2, 'lg' => 3, '2xl' => 4])
            : $table->columns(static::listColumns());

        return $table
            ->filters([
                //
            ])
            ->emptyStateHeading('Belum ada kategori produk')
            ->emptyStateDescription('Kategori mengelompokkan produk di halaman Produk. Isi Induk untuk membuatnya menjadi sub-kategori.')
            ->emptyStateIcon('heroicon-o-tag')
            ->actions([
                Tables\Actions\ViewAction::make()->slideOver(),
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
                ->weight(FontWeight::SemiBold)
                ->searchable(),
            Tables\Columns\TextColumn::make('name_en')
                ->label('Name (EN)')
                ->searchable()
                ->toggleable(isToggledHiddenByDefault: true),
            Tables\Columns\TextColumn::make('parent.name_id')
                ->label('Induk')
                ->badge()
                ->color('gray')
                ->placeholder('— utama —')
                ->sortable(),
            Tables\Columns\TextColumn::make('slug')
                ->label('Slug')
                ->color('gray')
                ->searchable(),
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
                    ->height(170)
                    ->extraImgAttributes(['class' => 'w-full h-44 object-cover rounded-lg']),
                Tables\Columns\Layout\Stack::make([
                    Tables\Columns\TextColumn::make('name_id')
                        ->weight(FontWeight::SemiBold)
                        ->searchable()
                        ->wrap(),
                    // Hanya sub-kategori yang menampilkan induknya; kategori
                    // utama tidak perlu baris kosong di bawah namanya.
                    Tables\Columns\TextColumn::make('parent.name_id')
                        ->badge()
                        ->color('gray')
                        ->placeholder(''),
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
            'index' => Pages\ListProductCategories::route('/'),
            'create' => Pages\CreateProductCategory::route('/create'),
            'edit' => Pages\EditProductCategory::route('/{record}/edit'),
        ];
    }
}
