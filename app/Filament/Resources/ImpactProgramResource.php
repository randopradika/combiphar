<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ImpactProgramResource\Pages;
use App\Models\ImpactProgram;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Support\Enums\FontWeight;
use Filament\Tables;
use Filament\Tables\Table;

class ImpactProgramResource extends Resource
{
    protected static ?string $model = ImpactProgram::class;

    protected static ?string $navigationGroup = 'Halaman';

    protected static ?string $navigationLabel = 'Beranda: Program Dampak';

    protected static ?int $navigationSort = 2;

    protected static ?string $navigationIcon = 'heroicon-o-chart-bar';

    protected static ?string $recordTitleAttribute = 'title_id';

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
                    ]),

                Forms\Components\Group::make()
                    ->columnSpan(['lg' => 1])
                    ->schema([
                        Forms\Components\Section::make('Gambar')
                            ->schema([
                                Forms\Components\FileUpload::make('image')
                                    ->label('Gambar program')
                                    ->helperText('Tampil di kartu slider Program Dampak pada halaman Beranda.')
                                    ->image(),
                            ]),
                    ]),

                Forms\Components\Hidden::make('sort')->default(fn () => (static::getModel()::max('sort') ?? 0) + 1),
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
        // Kartu program dikenali dari gambarnya, jadi halaman ini terbuka
        // sebagai galeri. Tampilan tabel tetap ada di balik tombolnya.
        $livewire = $table->getLivewire();
        $gallery = method_exists($livewire, 'isGallery') && $livewire->isGallery();

        $table = $gallery
            ? $table->columns(static::galleryColumns())->contentGrid(['sm' => 2, 'lg' => 3, '2xl' => 4])
            : $table->columns(static::listColumns());

        return $table
            ->filters([
                //
            ])
            ->emptyStateHeading('Belum ada program dampak')
            ->emptyStateDescription('Slider Program Dampak di halaman Beranda.')
            ->emptyStateIcon('heroicon-o-heart')
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
            Tables\Columns\TextColumn::make('title_id')
                ->label('Judul')
                ->weight(FontWeight::SemiBold)
                ->searchable()
                ->wrap(),
            Tables\Columns\TextColumn::make('title_en')
                ->label('Title (EN)')
                ->searchable()
                ->toggleable(isToggledHiddenByDefault: true),
            Tables\Columns\TextColumn::make('body_id')
                ->label('Deskripsi')
                ->limit(60)
                ->color('gray'),
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

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListImpactPrograms::route('/'),
            'create' => Pages\CreateImpactProgram::route('/create'),
            'edit' => Pages\EditImpactProgram::route('/{record}/edit'),
        ];
    }
}
