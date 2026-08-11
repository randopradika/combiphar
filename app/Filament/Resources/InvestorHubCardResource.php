<?php

namespace App\Filament\Resources;

use App\Filament\Resources\InvestorHubCardResource\Pages;
use App\Models\InvestorHubCard;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Support\Enums\FontWeight;
use Filament\Tables;
use Filament\Tables\Table;

/**
 * The Investor hub sub-menu cards (Figma 577:1260). Fixed row set — the `key`
 * decides which Investor section a card opens, so there is no create/delete;
 * admins edit the title, upload a thumbnail, reorder, and toggle visibility.
 */
class InvestorHubCardResource extends Resource
{
    protected static ?string $model = InvestorHubCard::class;

    protected static ?string $slug = 'investor-hub-cards';

    protected static ?string $navigationGroup = 'Halaman';

    protected static ?string $navigationLabel = 'Investor: Kartu Halaman';

    protected static ?int $navigationSort = 6;

    protected static ?string $navigationIcon = 'heroicon-o-squares-2x2';

    protected static ?string $recordTitleAttribute = 'title_id';

    /**
     * Fields the panel-wide search box looks at. Both language columns are
     * listed so a search works whichever language the editor thinks in.
     */
    public static function getGloballySearchableAttributes(): array
    {
        return ['title_id', 'title_en'];
    }

    /**
     * Formulir ini tampil di dalam modal (halaman ini memakai ManageRecords),
     * jadi bagiannya ditumpuk, bukan dipisah ke kolom samping.
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

            Forms\Components\Section::make('Gambar')
                ->schema([
                    Forms\Components\FileUpload::make('image')
                        ->label('Gambar Kartu')
                        ->helperText('Rasio 1:1 (mis. 1000×1000). Kosongkan untuk memakai warna ungu bawaan. Judul tampil putih di atas gambar, jadi hindari gambar yang sangat terang di bagian bawah.')
                        ->image()
                        ->imageEditor()
                        ->directory('investor-hub')
                        ->columnSpanFull(),
                ]),

            Forms\Components\Section::make('Tampilan')
                ->schema([
                    Forms\Components\Toggle::make('is_visible')
                        ->label('Tampilkan di halaman Investor')
                        ->default(true),
                    Forms\Components\TextInput::make('key')
                        ->label('Kunci Bagian')
                        ->helperText('Menentukan bagian yang dibuka kartu ini — tidak dapat diubah.')
                        ->disabled()
                        ->dehydrated(false),
                ]),
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
        // Kartu ini dikenali dari gambarnya. ⚠️ Pengurutan tarik-lepas hanya
        // tersedia pada tampilan tabel, jadi tombol tampilan tetap penting.
        $livewire = $table->getLivewire();
        $gallery = method_exists($livewire, 'isGallery') && $livewire->isGallery();

        $table = $gallery
            ? $table->columns(static::galleryColumns())->contentGrid(['sm' => 2, 'lg' => 3, '2xl' => 4])
            : $table->columns(static::listColumns());

        return $table
            ->defaultSort('sort')
            ->reorderable('sort')
            ->emptyStateHeading('Belum ada kartu halaman investor')
            ->emptyStateDescription('Kartu tautan di halaman Investor.')
            ->emptyStateIcon('heroicon-o-rectangle-group')
            ->actions([
                Tables\Actions\ViewAction::make()->slideOver(),
                Tables\Actions\EditAction::make(),
            ]);
    }

    private static function listColumns(): array
    {
        return [
            Tables\Columns\ImageColumn::make('image')->label('Gambar'),
            Tables\Columns\TextColumn::make('title_id')
                ->label('Judul')
                ->weight(FontWeight::SemiBold)
                ->searchable(),
            Tables\Columns\TextColumn::make('title_en')->label('Title (EN)')->searchable(),
            Tables\Columns\IconColumn::make('is_visible')->label('Tampil')->boolean(),
        ];
    }

    private static function galleryColumns(): array
    {
        return [
            Tables\Columns\Layout\Stack::make([
                Tables\Columns\ImageColumn::make('image')
                    ->height(170)
                    ->extraImgAttributes(['class' => 'w-full h-44 object-cover rounded-lg bg-gray-100']),
                Tables\Columns\Layout\Stack::make([
                    Tables\Columns\TextColumn::make('title_id')
                        ->weight(FontWeight::SemiBold)
                        ->searchable()
                        ->wrap(),
                    // Hanya kartu yang disembunyikan yang perlu ditandai; yang
                    // tampil tidak butuh baris bertuliskan "tampil".
                    Tables\Columns\TextColumn::make('is_visible')
                        ->badge()
                        ->color('danger')
                        ->state(fn ($record) => $record->is_visible ? null : 'Disembunyikan')
                        ->placeholder(''),
                ])->space(1),
            ])->space(2),
        ];
    }

    public static function getPages(): array
    {
        return ['index' => Pages\ManageInvestorHubCards::route('/')];
    }
}
