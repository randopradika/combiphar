<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AwardResource\Pages;
use App\Models\Award;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Support\Enums\FontWeight;
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
        // Penghargaan dikenali dari sertifikat atau logonya, bukan dari judulnya,
        // jadi halaman ini terbuka sebagai galeri. Tampilan tabel tetap ada di
        // balik tombol "Tampilan tabel" untuk memilih banyak baris sekaligus.
        $livewire = $table->getLivewire();
        $gallery = method_exists($livewire, 'isGallery') && $livewire->isGallery();

        $table = $gallery
            ? $table->columns(static::galleryColumns())->contentGrid(['sm' => 2, 'lg' => 3, '2xl' => 4])
            : $table->columns(static::listColumns());

        return $table
            ->filters([
                Tables\Filters\TernaryFilter::make('is_hero')
                    ->label('Unggulan'),
                // 97 penghargaan sejak 2005 tanpa satu pun penyaring: mencari
                // "sertifikat 2019" sebelumnya berarti membaca baris satu per satu.
                Tables\Filters\SelectFilter::make('year')
                    ->label('Tahun')
                    ->options(fn () => static::getModel()::query()
                        ->whereNotNull('year')
                        ->distinct()
                        ->orderByDesc('year')
                        ->pluck('year', 'year')
                        ->all()),
            ])
            ->emptyStateHeading('Belum ada penghargaan')
            ->emptyStateDescription('Penghargaan tampil pada popup "Pencapaian & Penghargaan" di halaman Tentang Kami. Tandai maksimal tujuh sebagai Unggulan agar logonya tampil langsung di halaman.')
            ->emptyStateIcon('heroicon-o-trophy')
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

    /**
     * Tampilan tabel: dipertahankan untuk memilih banyak baris dan mengubah
     * "Unggulan" satu klik tanpa membuka record.
     */
    private static function listColumns(): array
    {
        return [
            Tables\Columns\ImageColumn::make('image')
                ->label(''),
            Tables\Columns\TextColumn::make('title_id')
                ->label('Judul')
                ->searchable()
                ->wrap(),
            Tables\Columns\TextColumn::make('title_en')
                ->label('Title (EN)')
                ->searchable()
                ->toggleable(isToggledHiddenByDefault: true),
            Tables\Columns\TextColumn::make('year')
                ->label('Tahun')
                ->badge()
                ->color('gray')
                ->sortable(),
            // Sama seperti "Tampil di halaman" pada Dewan: diubah langsung
            // dari daftar, tanpa membuka recordnya.
            Tables\Columns\ToggleColumn::make('is_hero')
                ->label('Unggulan'),
            Tables\Columns\TextColumn::make('updated_at')
                ->label('Diperbarui')
                ->since()
                ->dateTimeTooltip()
                ->sortable(),
        ];
    }

    /**
     * Tampilan galeri: gambar dulu, judul di bawahnya.
     *
     * Judul EN sengaja tidak ikut -- pada kartu ia hanya mengulang judul ID
     * dengan kata yang hampir sama dan menggandakan tinggi setiap kartu.
     */
    private static function galleryColumns(): array
    {
        return [
            Tables\Columns\Layout\Stack::make([
                Tables\Columns\ImageColumn::make('image')
                    ->height(160)
                    ->extraImgAttributes(['class' => 'w-full h-40 object-contain bg-white rounded-lg p-3']),
                Tables\Columns\Layout\Stack::make([
                    Tables\Columns\TextColumn::make('title_id')
                        ->weight(FontWeight::SemiBold)
                        ->searchable()
                        ->wrap()
                        ->limit(70),
                    Tables\Columns\TextColumn::make('year')
                        ->badge()
                        ->color('gray')
                        ->sortable(),
                    // Hanya muncul pada yang diunggulkan; sisanya tidak perlu
                    // baris kosong bertuliskan "bukan unggulan".
                    Tables\Columns\TextColumn::make('is_hero')
                        ->badge()
                        ->color('warning')
                        ->state(fn (Award $record) => $record->is_hero ? 'Unggulan' : null)
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
            'index' => Pages\ListAwards::route('/'),
            'create' => Pages\CreateAward::route('/create'),
            'edit' => Pages\EditAward::route('/{record}/edit'),
        ];
    }
}
