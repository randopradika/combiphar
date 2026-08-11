<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AboutHistoryResource\Pages;
use App\Models\AboutHistory;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Support\Enums\FontWeight;
use Filament\Tables;
use Filament\Tables\Table;

class AboutHistoryResource extends Resource
{
    protected static ?string $model = AboutHistory::class;

    protected static ?string $navigationGroup = 'Profil Perusahaan';

    protected static ?string $navigationLabel = 'Sejarah';

    protected static ?int $navigationSort = 6;

    protected static ?string $navigationIcon = 'heroicon-o-clock';

    protected static ?string $recordTitleAttribute = 'caption_id';

    /**
     * Fields the panel-wide search box looks at. Both language columns are
     * listed so a search works whichever language the editor thinks in.
     */
    public static function getGloballySearchableAttributes(): array
    {
        return ['caption_id', 'caption_en'];
    }

    public static function form(Form $form): Form
    {
        return $form
            ->columns(3)
            ->schema([
                Forms\Components\Group::make()
                    ->columnSpan(['lg' => 2])
                    ->schema([
                        Forms\Components\Section::make('Teks')
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
                        Forms\Components\Section::make('Tahun & foto')
                            ->schema([
                                Forms\Components\TextInput::make('year')
                                    ->label('Tahun')
                                    ->helperText('Menandai titik pada linimasa Sejarah di halaman Tentang Kami.')
                                    ->required()
                                    ->maxLength(255),
                                Forms\Components\FileUpload::make('photo')
                                    ->label('Foto')
                                    ->image()
                                    ->imageEditor(),
                            ]),
                    ]),

                Forms\Components\Hidden::make('sort')->default(fn () => (static::getModel()::max('sort') ?? 0) + 1),
            ]);
    }

    /** Keterangan satu titik linimasa untuk satu bahasa. */
    private static function contentFields(string $locale): array
    {
        $isId = $locale === 'id';

        return [
            Forms\Components\Textarea::make("caption_{$locale}")
                ->label($isId ? 'Teks' : 'Text')
                ->rows(5)
                ->columnSpanFull(),
        ];
    }

    public static function table(Table $table): Table
    {
        // Entri sejarah dikenali dari fotonya dan tahunnya, jadi halaman ini
        // terbuka sebagai galeri; tampilan tabel tetap tersedia.
        $livewire = $table->getLivewire();
        $gallery = method_exists($livewire, 'isGallery') && $livewire->isGallery();

        $table = $gallery
            ? $table->columns(static::galleryColumns())->contentGrid(['sm' => 2, 'lg' => 3, '2xl' => 4])
            : $table->columns(static::listColumns());

        return $table
            ->defaultSort('sort')
            ->emptyStateHeading('Belum ada entri sejarah')
            ->emptyStateDescription('Linimasa perjalanan perusahaan di halaman Tentang Kami.')
            ->emptyStateIcon('heroicon-o-clock')
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
            Tables\Columns\ImageColumn::make('photo')
                ->label(''),
            Tables\Columns\TextColumn::make('year')
                ->label('Tahun')
                ->badge()
                ->color('gray')
                ->searchable()
                ->sortable(),
            Tables\Columns\TextColumn::make('caption_id')
                ->label('Teks')
                ->limit(80)
                ->wrap(),
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
                Tables\Columns\ImageColumn::make('photo')
                    ->height(170)
                    ->extraImgAttributes(['class' => 'w-full h-44 object-cover rounded-lg']),
                Tables\Columns\Layout\Stack::make([
                    Tables\Columns\TextColumn::make('year')
                        ->weight(FontWeight::Bold)
                        ->searchable(),
                    Tables\Columns\TextColumn::make('caption_id')
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
            'index' => Pages\ListAboutHistories::route('/'),
            'create' => Pages\CreateAboutHistory::route('/create'),
            'edit' => Pages\EditAboutHistory::route('/{record}/edit'),
        ];
    }
}
