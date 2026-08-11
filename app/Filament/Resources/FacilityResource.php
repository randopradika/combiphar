<?php

namespace App\Filament\Resources;

use App\Filament\Resources\FacilityResource\Pages;
use App\Models\Facility;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Support\Enums\FontWeight;
use Filament\Tables;
use Filament\Tables\Table;

class FacilityResource extends Resource
{
    protected static ?string $model = Facility::class;

    protected static ?string $navigationGroup = 'Profil Perusahaan';

    protected static ?string $navigationLabel = 'Fasilitas Produksi';

    protected static ?int $navigationSort = 3;

    protected static ?string $navigationIcon = 'heroicon-o-building-office-2';

    protected static ?string $recordTitleAttribute = 'name';

    /**
     * Fields the panel-wide search box looks at. Both language columns are
     * listed so a search works whichever language the editor thinks in.
     */
    public static function getGloballySearchableAttributes(): array
    {
        return ['name', 'region'];
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('region')
                    ->maxLength(255),
                Forms\Components\TextInput::make('plants')
                    ->maxLength(255),
                Forms\Components\TextInput::make('area')
                    ->maxLength(255),
                Forms\Components\TextInput::make('category_id')
                    ->label('Kategori Produk (ID)')
                    ->maxLength(255),
                Forms\Components\TextInput::make('category_en')
                    ->label('Kategori Produk (EN)')
                    ->maxLength(255),
                Forms\Components\Textarea::make('detail_id')
                    ->label('Bentuk Sediaan / Dosage (ID)')
                    ->columnSpanFull(),
                Forms\Components\Textarea::make('detail_en')
                    ->label('Bentuk Sediaan / Dosage (EN)')
                    ->columnSpanFull(),
                Forms\Components\FileUpload::make('image')
                    ->image(),
                Forms\Components\Hidden::make('sort')->default(fn () => (static::getModel()::max('sort') ?? 0) + 1),
            ]);
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
                Tables\Filters\SelectFilter::make('region')
                    ->label('Wilayah')
                    ->options(fn () => static::getModel()::query()
                        ->whereNotNull('region')
                        ->distinct()
                        ->orderBy('region')
                        ->pluck('region', 'region')
                        ->all()),
            ])
            ->emptyStateHeading('Belum ada fasilitas produksi')
            ->emptyStateDescription('Fasilitas tampil pada popup "Fasilitas Produksi" di halaman Tentang Kami.')
            ->emptyStateIcon('heroicon-o-building-office-2')
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
            Tables\Columns\TextColumn::make('name')
                ->label('Nama')
                ->searchable()
                ->weight(FontWeight::SemiBold)
                ->wrap(),
            Tables\Columns\TextColumn::make('region')
                ->label('Wilayah')
                ->badge()
                ->color('gray')
                ->searchable(),
            Tables\Columns\TextColumn::make('plants')
                ->label('Pabrik')
                ->searchable(),
            Tables\Columns\TextColumn::make('area')
                ->label('Luas')
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
                    Tables\Columns\TextColumn::make('name')
                        ->weight(FontWeight::SemiBold)
                        ->searchable()
                        ->wrap(),
                    Tables\Columns\TextColumn::make('region')
                        ->badge()
                        ->color('gray'),
                    Tables\Columns\TextColumn::make('area')
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
            'index' => Pages\ListFacilities::route('/'),
            'create' => Pages\CreateFacility::route('/create'),
            'edit' => Pages\EditFacility::route('/{record}/edit'),
        ];
    }
}
