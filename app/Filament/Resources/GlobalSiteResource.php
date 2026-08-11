<?php

namespace App\Filament\Resources;

use App\Filament\Resources\GlobalSiteResource\Pages;
use App\Models\GlobalSite;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class GlobalSiteResource extends Resource
{
    protected static ?string $model = GlobalSite::class;

    protected static ?string $navigationGroup = 'Halaman';

    protected static ?string $navigationLabel = 'Tentang Kami: Jangkauan Bisnis';

    protected static ?int $navigationSort = 5;

    protected static ?string $navigationIcon = 'heroicon-o-globe-alt';

    protected static ?string $recordTitleAttribute = 'name';

    /**
     * Fields the panel-wide search box looks at. Both language columns are
     * listed so a search works whichever language the editor thinks in.
     */
    public static function getGloballySearchableAttributes(): array
    {
        return ['name'];
    }

    public static function form(Form $form): Form
    {
        return $form
            ->columns(3)
            ->schema([
                Forms\Components\Group::make()
                    ->columnSpan(['lg' => 2])
                    ->schema([
                        Forms\Components\Section::make('Lokasi')
                            ->schema([
                                Forms\Components\Grid::make(2)->schema([
                                    Forms\Components\TextInput::make('name')
                                        ->label('Nama')
                                        ->required()
                                        ->maxLength(255),
                                    Forms\Components\TextInput::make('region')
                                        ->label('Wilayah')
                                        ->maxLength(255),
                                ]),
                            ]),

                        Forms\Components\Section::make('Alamat')
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
                                    ->label('Gambar')
                                    ->image(),
                            ]),
                    ]),

                Forms\Components\Hidden::make('sort')->default(fn () => (static::getModel()::max('sort') ?? 0) + 1),
            ]);
    }

    /** Alamat untuk satu bahasa. */
    private static function contentFields(string $locale): array
    {
        $isId = $locale === 'id';

        return [
            Forms\Components\Textarea::make("address_{$locale}")
                ->label($isId ? 'Alamat' : 'Address')
                ->rows(4)
                ->columnSpanFull(),
        ];
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('region')
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
                    ->label('Diperbarui')
                    ->since()
                    ->dateTimeTooltip()
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->emptyStateHeading('Belum ada jangkauan bisnis')
            ->emptyStateDescription('Negara tujuan ekspor pada section peta dunia di halaman Tentang Kami.')
            ->emptyStateIcon('heroicon-o-globe-alt')
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

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListGlobalSites::route('/'),
            'create' => Pages\CreateGlobalSite::route('/create'),
            'edit' => Pages\EditGlobalSite::route('/{record}/edit'),
        ];
    }
}
