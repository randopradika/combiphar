<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductBannerResource\Pages;
use App\Models\ProductBanner;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Support\Enums\FontWeight;
use Filament\Tables;
use Filament\Tables\Table;

class ProductBannerResource extends Resource
{
    protected static ?string $model = ProductBanner::class;

    protected static ?string $navigationGroup = 'Halaman';

    protected static ?string $navigationLabel = 'Beranda: Sorotan Produk';

    protected static ?int $navigationSort = 3;

    protected static ?string $navigationIcon = 'heroicon-o-photo';

    /**
     * Formulir ini tampil di dalam modal (halaman ini memakai ManageRecords),
     * jadi tidak ada kolom samping seperti pada formulir halaman penuh --
     * bagiannya ditumpuk.
     */
    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Judul')
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

            Forms\Components\Section::make('Banner & tautan')
                ->schema([
                    Forms\Components\FileUpload::make('image')
                        ->label('Banner')
                        ->helperText('Gambar kartu pada bento grid Sorotan Produk di halaman Beranda.')
                        ->image()
                        ->imageEditor(),
                    Forms\Components\TextInput::make('link')
                        ->label('Tautan (opsional)')
                        ->helperText('Tujuan saat kartu diklik. Kosongkan bila kartu tidak dapat diklik.')
                        ->maxLength(255),
                ]),

            Forms\Components\Hidden::make('sort')->default(fn () => (static::getModel()::max('sort') ?? 0) + 1),
        ]);
    }

    /** Judul kartu untuk satu bahasa. */
    private static function contentFields(string $locale): array
    {
        $isId = $locale === 'id';

        return [
            Forms\Components\TextInput::make("title_{$locale}")
                ->label($isId ? 'Judul' : 'Title')
                ->required($isId)
                ->maxLength(255)
                ->columnSpanFull(),
        ];
    }

    public static function table(Table $table): Table
    {
        // Kartu bento dikenali dari gambarnya; judulnya hanya label kecil di
        // atasnya, jadi halaman ini terbuka sebagai galeri.
        $livewire = $table->getLivewire();
        $gallery = method_exists($livewire, 'isGallery') && $livewire->isGallery();

        $table = $gallery
            ? $table->columns(static::galleryColumns())->contentGrid(['sm' => 2, 'lg' => 3, '2xl' => 4])
            : $table->columns(static::listColumns());

        return $table->defaultSort('sort')
            ->emptyStateHeading('Belum ada sorotan produk')
            ->emptyStateDescription('Bento grid sorotan produk di halaman Beranda.')
            ->emptyStateIcon('heroicon-o-squares-2x2')
            ->actions([
                Tables\Actions\ViewAction::make()->slideOver(),
                Tables\Actions\EditAction::make(),
            ])->bulkActions([
                Tables\Actions\BulkActionGroup::make([Tables\Actions\DeleteBulkAction::make()]),
            ]);
    }

    private static function listColumns(): array
    {
        return [
            Tables\Columns\ImageColumn::make('image')
                ->label(''),
            Tables\Columns\TextColumn::make('title_id')
                ->label('Judul')
                ->weight(FontWeight::SemiBold)
                ->searchable(),
            Tables\Columns\TextColumn::make('title_en')
                ->label('Title (EN)')
                ->toggleable(isToggledHiddenByDefault: true),
            Tables\Columns\TextColumn::make('link')
                ->label('Tautan')
                ->color('gray')
                ->limit(40)
                ->placeholder('—'),
            Tables\Columns\TextColumn::make('sort')
                ->label('Urutan')
                ->numeric()
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
                Tables\Columns\TextColumn::make('title_id')
                    ->weight(FontWeight::SemiBold)
                    ->searchable()
                    ->wrap(),
            ])->space(2),
        ];
    }

    public static function getPages(): array
    {
        return ['index' => Pages\ManageProductBanners::route('/')];
    }
}
