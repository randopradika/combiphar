<?php

namespace App\Filament\Resources;

use App\Filament\Resources\NavItemResource\Pages;
use App\Models\NavItem;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

/**
 * Menu navigasi situs (bar atas dan menu mobile).
 *
 * Sebelumnya menu ditulis di lang/{id,en}/site.php, sehingga menambah satu
 * tautan berarti menyunting kode dan menunggu deploy. Bentuk datanya tidak
 * berubah — HandleInertiaRequests menyusun baris-baris ini menjadi struktur
 * yang sama, jadi menu desktop dan menu mobile memakainya sekaligus.
 */
class NavItemResource extends Resource
{
    protected static ?string $model = NavItem::class;

    protected static ?string $navigationGroup = 'Pengaturan';

    protected static ?string $navigationLabel = 'Menu Navigasi';

    protected static ?int $navigationSort = 1;

    protected static ?string $navigationIcon = 'heroicon-o-bars-3';

    protected static ?string $modelLabel = 'Item Menu';

    protected static ?string $pluralModelLabel = 'Menu Navigasi';

    protected static ?string $recordTitleAttribute = 'label_id';

    public static function getGloballySearchableAttributes(): array
    {
        return ['label_id', 'label_en'];
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('section')
                    ->label('Menu induk')
                    ->helperText('Menu di bar atas tempat item ini muncul.')
                    ->options(NavItem::SECTIONS)
                    ->required()
                    ->live()
                    ->afterStateUpdated(fn (Forms\Set $set) => $set('parent_id', null)),
                Forms\Components\Select::make('parent_id')
                    ->label('Berada di bawah')
                    ->helperText('Kosongkan untuk item tingkat atas. Pilih item lain untuk menjorok ke dalam sebagai sub-item.')
                    ->options(fn (Forms\Get $get, ?NavItem $record) => NavItem::query()
                        ->where('section', $get('section'))
                        ->whereNull('parent_id')
                        ->when($record, fn (Builder $q) => $q->whereKeyNot($record->getKey()))
                        ->orderBy('sort')
                        ->pluck('label_id', 'id')
                        ->all())
                    ->placeholder('— Item tingkat atas —')
                    ->searchable()
                    ->preload()
                    ->nullable(),
                Forms\Components\TextInput::make('label_id')
                    ->label('Teks (ID)')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('label_en')
                    ->label('Text (EN)')
                    ->helperText('Kosongkan untuk memakai teks Indonesia.')
                    ->maxLength(255),
                Forms\Components\TextInput::make('suffix')
                    ->label('Tujuan')
                    ->helperText('Ditambahkan ke alamat halaman menu induk. Contoh: "#sejarah" melompat ke bagian pada halaman itu, "?tab=karir" membuka tab tertentu, "/education" membuka halaman detail.')
                    ->maxLength(255),
                Forms\Components\Select::make('base')
                    ->label('Ambil alamat dari menu lain (opsional)')
                    ->helperText('Hampir selalu dibiarkan kosong. Dipakai bila tujuannya berada di halaman bagian lain — dokumen governance milik Investor sebenarnya ada di bawah halaman Keberlanjutan.')
                    ->options(NavItem::SECTIONS)
                    ->nullable(),
                Forms\Components\Toggle::make('is_head')
                    ->label('Tampilkan sebagai judul kelompok')
                    ->helperText('Dicetak tebal, dengan sub-item menjorok di bawahnya.'),
                Forms\Components\TextInput::make('sort')
                    ->label('Urutan')
                    ->helperText('Angka kecil tampil lebih dulu.')
                    ->numeric()
                    ->default(fn () => (NavItem::max('sort') ?? 0) + 1),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('sort')
            ->defaultGroup('section')
            ->columns([
                Tables\Columns\TextColumn::make('label_id')
                    ->label('Teks')
                    // Sub-item diberi indentasi agar susunannya terbaca sebagai
                    // pohon, bukan daftar datar.
                    ->formatStateUsing(fn (NavItem $record, string $state) => $record->parent_id ? '— ' . $state : $state)
                    ->searchable(),
                Tables\Columns\TextColumn::make('label_en')
                    ->label('Text (EN)')
                    ->placeholder('—')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('suffix')
                    ->label('Tujuan')
                    ->placeholder('—'),
                Tables\Columns\TextColumn::make('base')
                    ->label('Dari menu')
                    ->placeholder('—')
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\IconColumn::make('is_head')
                    ->label('Judul kelompok')
                    ->boolean(),
                Tables\Columns\TextColumn::make('sort')
                    ->label('Urutan')
                    ->numeric()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('section')
                    ->label('Menu induk')
                    ->options(NavItem::SECTIONS),
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

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListNavItems::route('/'),
            'create' => Pages\CreateNavItem::route('/create'),
            'edit' => Pages\EditNavItem::route('/{record}/edit'),
        ];
    }
}
