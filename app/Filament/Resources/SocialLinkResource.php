<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SocialLinkResource\Pages;
use App\Models\SocialLink;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Support\Enums\FontWeight;
use Filament\Tables;
use Filament\Tables\Table;

class SocialLinkResource extends Resource
{
    protected static ?string $model = SocialLink::class;

    protected static ?string $navigationGroup = 'Pengaturan';

    protected static ?string $navigationLabel = 'Media Sosial';

    protected static ?int $navigationSort = 1;

    protected static ?string $navigationIcon = 'heroicon-o-share';

    protected static ?string $recordTitleAttribute = 'name';

    /**
     * Fields the panel-wide search box looks at. Both language columns are
     * listed so a search works whichever language the editor thinks in.
     */
    public static function getGloballySearchableAttributes(): array
    {
        return ['name'];
    }

    /**
     * Tidak ada pasangan kolom ID/EN di sini, jadi tidak ada tab bahasa — yang
     * membingungkan pada layar ini adalah DUA kolom ikon yang tampak sama,
     * sehingga keduanya dikelompokkan dalam satu bagian yang menjelaskan
     * bedanya.
     */
    public static function form(Form $form): Form
    {
        return $form
            ->columns(3)
            ->schema([
                Forms\Components\Group::make()
                    ->columnSpan(['lg' => 2])
                    ->schema([
                        Forms\Components\Section::make('Akun')
                            ->schema([
                                Forms\Components\TextInput::make('name')
                                    ->label('Nama (mis. Instagram)')
                                    ->required()
                                    ->maxLength(255),
                                Forms\Components\TextInput::make('url')
                                    ->label('URL')
                                    ->url()
                                    ->maxLength(255),
                            ]),
                    ]),

                Forms\Components\Group::make()
                    ->columnSpan(['lg' => 1])
                    ->schema([
                        Forms\Components\Section::make('Ikon')
                            ->description('Dua ikon berbeda untuk dua latar yang berbeda.')
                            ->schema([
                                Forms\Components\FileUpload::make('icon')
                                    ->label('Ikon Footer')
                                    ->helperText('Tampil di footer gelap — gunakan ikon putih / transparan (PNG/SVG).')
                                    ->image()
                                    ->imageEditor(),
                                Forms\Components\FileUpload::make('product_icon')
                                    ->label('Ikon Produk (Informasi Lebih Lanjut)')
                                    ->helperText('Tampil di popup produk (latar terang) — gunakan logo berwarna. Kosongkan bila tidak dipakai.')
                                    ->image()
                                    ->imageEditor(),
                            ]),
                    ]),

                Forms\Components\Hidden::make('sort')->default(fn () => (static::getModel()::max('sort') ?? 0) + 1),
            ]);
    }

    public static function table(Table $table): Table
    {
        // ⚠️ Pengurutan tarik-lepas hanya tersedia pada tampilan tabel.
        $livewire = $table->getLivewire();
        $gallery = method_exists($livewire, 'isGallery') && $livewire->isGallery();

        $table = $gallery
            ? $table->columns(static::galleryColumns())->contentGrid(['sm' => 2, 'lg' => 3, '2xl' => 4])
            : $table->columns(static::listColumns());

        return $table
            ->defaultSort('sort')
            ->reorderable('sort')
            ->emptyStateHeading('Belum ada media sosial')
            ->emptyStateDescription('Ikon media sosial di footer dan di menu mobile.')
            ->emptyStateIcon('heroicon-o-share')
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
            Tables\Columns\ImageColumn::make('icon')
                ->label('Ikon footer')
                // Ikon footer berwarna putih: di atas sel putih ia tidak
                // terlihat sama sekali, jadi diberi latar gelap seperti di situs.
                ->extraImgAttributes(['class' => 'bg-gray-800 rounded p-1']),
            Tables\Columns\TextColumn::make('name')
                ->label('Nama')
                ->weight(FontWeight::SemiBold)
                ->searchable(),
            Tables\Columns\TextColumn::make('url')
                ->label('URL')
                ->color('gray')
                ->limit(50)
                ->searchable(),
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
                Tables\Columns\ImageColumn::make('icon')
                    ->height(90)
                    ->extraImgAttributes(['class' => 'w-full h-24 object-contain bg-gray-800 rounded-lg p-6']),
                Tables\Columns\Layout\Stack::make([
                    Tables\Columns\TextColumn::make('name')
                        ->weight(FontWeight::SemiBold)
                        ->searchable(),
                    Tables\Columns\TextColumn::make('url')
                        ->color('gray')
                        ->limit(40)
                        ->placeholder(''),
                ])->space(1),
            ])->space(2),
        ];
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
