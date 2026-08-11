<?php

namespace App\Filament\Resources;

use App\Filament\Resources\InvestorDocumentResource\Pages;
use App\Models\InvestorDocument;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

/**
 * Semua dokumen investor dalam satu daftar, dipisahkan oleh tab kategori.
 *
 * Sebelumnya kategori dipecah menjadi lima menu terpisah (Informasi Keuangan,
 * Laporan Tahunan, Keberlanjutan, Presentasi Perusahaan, Keterbukaan Informasi)
 * yang isinya identik kecuali satu kolom, sementara resource gabungan ini
 * justru disembunyikan dari navigasi. Akibatnya kategori "Informasi Saham"
 * tidak punya menu sama sekali sehingga tidak dapat dikelola dari panel.
 *
 * Menambah kategori baru sekarang cukup satu baris pada CATEGORIES.
 */
class InvestorDocumentResource extends Resource
{
    protected static ?string $model = InvestorDocument::class;

    protected static ?string $navigationGroup = 'Konten';

    protected static ?string $navigationLabel = 'Dokumen Investor';

    protected static ?int $navigationSort = 5;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $recordTitleAttribute = 'title_id';

    /** Satu-satunya sumber kebenaran untuk pilihan kategori, kolom, tab dan filter. */
    public const CATEGORIES = [
        'financial' => 'Informasi Keuangan',
        'annual_report' => 'Laporan Tahunan',
        'sustainability' => 'Laporan Keberlanjutan',
        'presentation' => 'Presentasi Perusahaan',
        'disclosure' => 'Keterbukaan Informasi',
        'stock' => 'Informasi Saham',
    ];

    /**
     * Fields the panel-wide search box looks at. Both language columns are
     * listed so a search works whichever language the editor thinks in.
     */
    public static function getGloballySearchableAttributes(): array
    {
        return ['title_id', 'title_en'];
    }

    public static function form(Form $form): Form
    {
        return $form
            ->columns(3)
            ->schema([
                Forms\Components\Group::make()
                    ->columnSpan(['lg' => 2])
                    ->schema([
                        Forms\Components\Section::make('Dokumen')
                            ->schema([
                                // Judul DAN berkasnya dipisah per bahasa: satu
                                // dokumen bisa punya PDF Indonesia dan PDF
                                // Inggris yang berbeda, jadi keduanya berada di
                                // dalam tab bahasa yang sama.
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
                                Forms\Components\Select::make('category')
                                    ->label('Kategori')
                                    ->options(static::CATEGORIES)
                                    ->helperText('Menentukan di bagian mana dokumen ini tampil pada halaman Investor.')
                                    ->required()
                                    ->default('annual_report'),
                                Forms\Components\TextInput::make('year')
                                    ->label('Tahun')
                                    ->numeric(),
                            ]),
                    ]),

                Forms\Components\Hidden::make('sort')->default(fn () => (static::getModel()::max('sort') ?? 0) + 1),
            ]);
    }

    /**
     * Judul dan berkas untuk satu bahasa.
     *
     * ⚠️ `file_id` adalah berkas BAHASA INDONESIA, bukan kolom kunci — lihat
     * pasangannya `file_en`.
     */
    private static function contentFields(string $locale): array
    {
        $isId = $locale === 'id';

        return [
            Forms\Components\TextInput::make("title_{$locale}")
                ->label($isId ? 'Judul' : 'Title')
                ->required($isId)
                ->maxLength(255)
                ->columnSpanFull(),
            Forms\Components\FileUpload::make("file_{$locale}")
                ->label($isId ? 'Berkas PDF' : 'PDF file')
                ->acceptedFileTypes(['application/pdf'])
                ->downloadable()
                ->columnSpanFull(),
        ];
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('sort')
            ->columns([
                Tables\Columns\TextColumn::make('category')
                    ->label('Kategori')
                    ->badge()
                    ->formatStateUsing(fn (?string $state) => static::CATEGORIES[$state] ?? $state),
                Tables\Columns\TextColumn::make('title_id')
                    ->label('Judul')
                    ->searchable()
                    ->wrap(),
                Tables\Columns\TextColumn::make('year')
                    ->label('Tahun')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\IconColumn::make('file_id')
                    ->label('File ID')
                    ->boolean(),
                Tables\Columns\IconColumn::make('file_en')
                    ->label('File EN')
                    ->boolean(),
                Tables\Columns\TextColumn::make('sort')
                    ->label('Urutan')
                    ->numeric()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Diperbarui')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('category')
                    ->label('Kategori')
                    ->options(static::CATEGORIES),
            ])
            ->emptyStateHeading('Belum ada dokumen investor')
            ->emptyStateDescription('Laporan dan dokumen yang dapat diunduh di halaman Investor, dikelompokkan menurut kategorinya.')
            ->emptyStateIcon('heroicon-o-document-text')
            ->actions([
                Tables\Actions\ViewAction::make()->slideOver(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
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
            'index' => Pages\ListInvestorDocuments::route('/'),
            'create' => Pages\CreateInvestorDocument::route('/create'),
            'edit' => Pages\EditInvestorDocument::route('/{record}/edit'),
        ];
    }
}
