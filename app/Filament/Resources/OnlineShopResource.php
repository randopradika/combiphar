<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OnlineShopResource\Pages;
use App\Models\OnlineShop;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Support\Enums\FontWeight;
use Filament\Tables;
use Filament\Tables\Table;

class OnlineShopResource extends Resource
{
    protected static ?string $model = OnlineShop::class;

    protected static ?string $navigationGroup = 'Profil Perusahaan';

    protected static ?string $navigationLabel = 'Toko Online Resmi';

    protected static ?int $navigationSort = 7;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-bag';

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
        // Tidak ada pasangan bidang ID/EN di sini -- nama toko dan alamatnya
        // sama dalam kedua bahasa -- jadi yang dibutuhkan hanya pemisahan
        // antara identitas toko dan logonya.
        return $form
            ->columns(3)
            ->schema([
                Forms\Components\Group::make()
                    ->columnSpan(['lg' => 2])
                    ->schema([
                        Forms\Components\Section::make('Toko')
                            ->schema([
                                Forms\Components\TextInput::make('name')
                                    ->label('Nama toko')
                                    ->required()
                                    ->maxLength(255),
                                Forms\Components\TextInput::make('url')
                                    ->label('Tautan toko')
                                    ->helperText('Tujuan tombol "Tersedia di" pada popup detail produk.')
                                    ->maxLength(255),
                            ]),
                    ]),

                Forms\Components\Group::make()
                    ->columnSpan(['lg' => 1])
                    ->schema([
                        Forms\Components\Section::make('Logo')
                            ->schema([
                                Forms\Components\FileUpload::make('logo')
                                    ->label('Logo')
                                    ->helperText('Tampil pada latar terang — gunakan logo berwarna.')
                                    ->image()
                                    ->imageEditor(),
                            ]),
                    ]),

                Forms\Components\Hidden::make('sort')->default(fn () => (static::getModel()::max('sort') ?? 0) + 1),
            ]);
    }

    public static function table(Table $table): Table
    {
        // Toko dikenali dari logonya (Tokopedia, Shopee, Blibli) jauh lebih
        // cepat daripada dari nama atau URL-nya.
        $livewire = $table->getLivewire();
        $gallery = method_exists($livewire, 'isGallery') && $livewire->isGallery();

        $table = $gallery
            ? $table->columns(static::galleryColumns())->contentGrid(['sm' => 2, 'lg' => 3, '2xl' => 4])
            : $table->columns(static::listColumns());

        return $table
            ->filters([
                //
            ])
            ->emptyStateHeading('Belum ada toko online')
            ->emptyStateDescription('Tombol Tersedia di pada popup detail produk.')
            ->emptyStateIcon('heroicon-o-shopping-bag')
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
            Tables\Columns\ImageColumn::make('logo')
                ->label(''),
            Tables\Columns\TextColumn::make('name')
                ->label('Nama')
                ->weight(FontWeight::SemiBold)
                ->searchable(),
            Tables\Columns\TextColumn::make('url')
                ->label('Tautan')
                ->color('gray')
                ->limit(50)
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
                // object-contain, bukan cover: logo toko punya ruang kosong di
                // sekelilingnya dan akan terpotong bila dipaksa memenuhi kartu.
                Tables\Columns\ImageColumn::make('logo')
                    ->height(120)
                    ->extraImgAttributes(['class' => 'w-full h-28 object-contain bg-white rounded-lg p-3']),
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

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOnlineShops::route('/'),
            'create' => Pages\CreateOnlineShop::route('/create'),
            'edit' => Pages\EditOnlineShop::route('/{record}/edit'),
        ];
    }
}
