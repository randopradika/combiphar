<?php

namespace App\Filament\Resources;

use App\Filament\Resources\WellnessProgramResource\Pages;
use App\Models\WellnessProgram;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Support\Enums\FontWeight;
use Filament\Tables;
use Filament\Tables\Table;

/**
 * "Employee Wellness Program" circles on the Karir tab (Figma 987:51). Unlike
 * the Investor hub cards this is not a fixed row set — admins may add or remove
 * programmes, and the grid on the page reflows to whatever count exists.
 *
 * The section heading and its lead paragraph live on the contact Page record
 * (Karir & Kontak → banner / teks halaman), not here.
 */
class WellnessProgramResource extends Resource
{
    protected static ?string $model = WellnessProgram::class;

    protected static ?string $slug = 'wellness-programs';

    protected static ?string $navigationGroup = 'Halaman';

    protected static ?string $navigationLabel = 'Karir: Program Kesejahteraan';

    protected static ?int $navigationSort = 7;

    protected static ?string $navigationIcon = 'heroicon-o-heart';

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
            Forms\Components\Section::make('Isi program')
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

            Forms\Components\Section::make('Ikon')
                ->schema([
                    Forms\Components\FileUpload::make('icon')
                        ->label('Ikon')
                        ->helperText('Gambar persegi dengan latar transparan (mis. PNG 512×512). Ikon tampil di dalam lingkaran ungu; lingkaran keempat berlatar gelap, jadi gunakan ikon terang di posisi tersebut.')
                        ->image()
                        ->imageEditor()
                        ->directory('wellness')
                        ->columnSpanFull(),
                ]),

            // Admins never type a sort number — new programmes append (project-wide convention).
            Forms\Components\Hidden::make('sort')
                ->default(fn () => (int) (static::getModel()::max('sort') ?? 0) + 1),
        ]);
    }

    /** Judul dan deskripsi untuk satu bahasa. */
    private static function contentFields(string $locale): array
    {
        $isId = $locale === 'id';

        return [
            Forms\Components\TextInput::make("title_{$locale}")
                ->label($isId ? 'Judul' : 'Title')
                ->required($isId)
                ->maxLength(255)
                ->columnSpanFull(),
            Forms\Components\Textarea::make("body_{$locale}")
                ->label($isId ? 'Deskripsi' : 'Description')
                ->rows(4)
                ->columnSpanFull(),
        ];
    }

    public static function table(Table $table): Table
    {
        // Program ini dikenali dari ikonnya. ⚠️ Pengurutan tarik-lepas hanya
        // tersedia pada tampilan tabel.
        $livewire = $table->getLivewire();
        $gallery = method_exists($livewire, 'isGallery') && $livewire->isGallery();

        $table = $gallery
            ? $table->columns(static::galleryColumns())->contentGrid(['sm' => 2, 'lg' => 3, '2xl' => 4])
            : $table->columns(static::listColumns());

        return $table
            ->defaultSort('sort')
            ->reorderable('sort')
            ->emptyStateHeading('Belum ada program kesejahteraan')
            ->emptyStateDescription('Lingkaran program karyawan di tab Karir pada halaman Karir & Kontak.')
            ->emptyStateIcon('heroicon-o-sun')
            ->actions([
                Tables\Actions\ViewAction::make()->slideOver(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    private static function listColumns(): array
    {
        return [
            Tables\Columns\ImageColumn::make('icon')->label('Ikon'),
            Tables\Columns\TextColumn::make('title_id')
                ->label('Judul')
                ->weight(FontWeight::SemiBold)
                ->searchable(),
            Tables\Columns\TextColumn::make('title_en')->label('Title (EN)')->searchable(),
            Tables\Columns\TextColumn::make('body_id')->label('Deskripsi')->limit(60),
        ];
    }

    private static function galleryColumns(): array
    {
        return [
            Tables\Columns\Layout\Stack::make([
                // Ikon berlatar transparan: object-contain dengan sedikit
                // padding, bukan cover yang akan memotong tepinya.
                Tables\Columns\ImageColumn::make('icon')
                    ->height(110)
                    ->extraImgAttributes(['class' => 'w-full h-28 object-contain bg-white rounded-lg p-4']),
                Tables\Columns\Layout\Stack::make([
                    Tables\Columns\TextColumn::make('title_id')
                        ->weight(FontWeight::SemiBold)
                        ->searchable()
                        ->wrap(),
                    Tables\Columns\TextColumn::make('body_id')
                        ->color('gray')
                        ->limit(90)
                        ->wrap()
                        ->placeholder(''),
                ])->space(1),
            ])->space(2),
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageWellnessPrograms::route('/'),
        ];
    }
}
