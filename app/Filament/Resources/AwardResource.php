<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AwardResource\Pages;
use App\Models\Award;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class AwardResource extends Resource
{
    protected static ?string $model = Award::class;

    protected static ?string $navigationGroup = 'Profil Perusahaan';

    protected static ?string $navigationLabel = 'Penghargaan';

    protected static ?int $navigationSort = 2;

    protected static ?string $navigationIcon = 'heroicon-o-trophy';

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
     * Semua penghargaan dalam satu daftar. Sebelumnya terpisah menjadi dua menu
     * ("Penghargaan" dan "Penghargaan Unggulan") yang hanya dibedakan oleh satu
     * kolom boolean, sehingga mengunggulkan sebuah penghargaan berarti
     * memindahkannya antar menu. Sekarang cukup satu klik di kolom "Unggulan".
     */
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('title_id')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('title_en')
                    ->maxLength(255),
                Forms\Components\TextInput::make('year')
                    ->label('Tahun')
                    ->helperText('Mengelompokkan penghargaan pada popup "Pencapaian & Penghargaan" (filter Tahun).')
                    ->numeric(),
                Forms\Components\FileUpload::make('image')
                    ->image(),
                Forms\Components\Toggle::make('is_hero')
                    ->label('Tampilkan sebagai logo unggulan')
                    ->helperText('Logo tampil di bawah deskripsi "Pencapaian & Penghargaan" di halaman Tentang Kami (maksimal 7 pertama). Penghargaan lain tetap tampil di popup "Daftar Penghargaan".')
                    ->default(false),
                Forms\Components\Hidden::make('sort')->default(fn () => (static::getModel()::max('sort') ?? 0) + 1),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title_id')
                    ->searchable(),
                Tables\Columns\TextColumn::make('title_en')
                    ->searchable(),
                Tables\Columns\TextColumn::make('year')
                    ->sortable(),
                Tables\Columns\ImageColumn::make('image'),
                // Sama seperti "Tampil di halaman" pada Dewan: diubah langsung
                // dari daftar, tanpa membuka recordnya.
                Tables\Columns\ToggleColumn::make('is_hero')
                    ->label('Unggulan'),
                Tables\Columns\TextColumn::make('sort')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_hero')
                    ->label('Unggulan'),
            ])
            ->actions([
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
            'index' => Pages\ListAwards::route('/'),
            'create' => Pages\CreateAward::route('/create'),
            'edit' => Pages\EditAward::route('/{record}/edit'),
        ];
    }
}
