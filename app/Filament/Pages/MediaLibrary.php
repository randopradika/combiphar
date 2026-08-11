<?php

namespace App\Filament\Pages;

use App\Filament\Concerns\HasGalleryToggle;
use App\Models\MediaFile;
use App\Support\MediaScanner;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Pages\Page as BasePage;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn\TextColumnSize;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Number;

/**
 * Satu tempat untuk melihat seluruh berkas yang pernah diunggah.
 *
 * Sebelumnya gambar hanya ada di dalam record masing-masing: tidak ada daftar
 * aset, tidak ada cara tahu sebuah berkas dipakai di mana, dan tidak ada cara
 * menemukan berkas yang sudah tidak dirujuk siapa pun.
 *
 * Halaman ini membaca indeks yang dibangun MediaScanner. Mengunggah tetap
 * dilakukan dari field di masing-masing record -- indeks ini tidak menggantikan
 * satu pun field unggahan yang sudah ada.
 */
class MediaLibrary extends BasePage implements HasTable
{
    use HasGalleryToggle;
    use InteractsWithTable;

    protected static string $view = 'filament.pages.media-library';

    protected static ?string $navigationGroup = 'Konten';

    protected static ?string $navigationLabel = 'Pustaka Media';

    protected static ?int $navigationSort = 7;

    protected static ?string $navigationIcon = 'heroicon-o-photo';

    protected static ?string $title = 'Pustaka Media';

    public function getSubheading(): string
    {
        $last = MediaFile::query()->max('scanned_at');

        return $last
            ? 'Daftar ini hasil pemindaian terakhir ('.$last.'). Unggahan baru belum tampil sampai dipindai ulang.'
            : 'Belum pernah dipindai. Tekan "Pindai ulang" untuk membangun daftarnya.';
    }

    protected function getHeaderActions(): array
    {
        return [
            $this->getGalleryToggleAction(),
            Actions\Action::make('pindai')
                ->label('Pindai ulang')
                ->icon('heroicon-m-arrow-path')
                ->action(function () {
                    $stats = app(MediaScanner::class)->scan();

                    Notification::make()
                        ->title('Pemindaian selesai')
                        ->body("{$stats['indexed']} berkas terindeks, {$stats['unused']} tidak terpakai, {$stats['removed']} dihapus dari indeks.")
                        ->success()
                        ->send();
                }),
        ];
    }

    public function table(Table $table): Table
    {
        // Pustaka berisi ratusan berkas yang dikenali dari gambarnya. Dalam
        // bentuk tabel, pratinjaunya hanya kotak 44px di kolom pertama.
        $gallery = $this->isGallery();

        $table = $table
            ->query(MediaFile::query())
            ->defaultSort('path');

        $table = $gallery
            ? $table->columns(static::galleryColumns())->contentGrid(['sm' => 2, 'md' => 3, 'xl' => 4, '2xl' => 6])
            : $table->columns(static::listColumns());

        return $table
            ->filters([
                Tables\Filters\SelectFilter::make('folder')
                    ->label('Folder')
                    ->options(fn () => MediaFile::query()
                        ->distinct()
                        ->orderBy('folder')
                        ->pluck('folder', 'folder')
                        ->map(fn ($f) => $f === '' ? '(akar)' : $f)
                        ->all()),
                Tables\Filters\TernaryFilter::make('usage_count')
                    ->label('Pemakaian')
                    ->placeholder('Semua')
                    ->trueLabel('Terpakai')
                    ->falseLabel('Tidak terpakai')
                    ->queries(
                        true: fn ($query) => $query->where('usage_count', '>', 0),
                        false: fn ($query) => $query->where('usage_count', 0),
                        blank: fn ($query) => $query,
                    ),
            ])
            ->actions([
                Tables\Actions\Action::make('buka')
                    ->label('Buka')
                    ->icon('heroicon-m-arrow-top-right-on-square')
                    ->url(fn (MediaFile $record) => $record->url())
                    ->openUrlInNewTab(),
                Tables\Actions\Action::make('hapus')
                    ->label('Hapus')
                    ->icon('heroicon-m-trash')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalHeading('Hapus berkas ini?')
                    ->modalDescription('Berkas dihapus permanen dari disk. Status "tidak terpakai" berasal dari pemindaian terakhir -- bila ada record yang diubah sesudah itu, pindai ulang dulu.')
                    // Hanya berkas yang tidak dirujuk siapa pun yang boleh dihapus,
                    // supaya satu klik tidak bisa mengosongkan gambar di situs.
                    ->visible(fn (MediaFile $record) => $record->isUnused())
                    ->action(function (MediaFile $record) {
                        Storage::disk('public')->delete($record->path);
                        $record->delete();

                        Notification::make()
                            ->title('Berkas dihapus')
                            ->body($record->path)
                            ->success()
                            ->send();
                    }),
            ]);
    }

    /**
     * Tampilan tabel: untuk mengurutkan menurut ukuran atau pemakaian, dan
     * untuk membaca nama berkas selengkapnya.
     */
    private static function listColumns(): array
    {
        return [
            Tables\Columns\ImageColumn::make('path')
                ->label('')
                ->disk('public')
                ->height(44)
                // Berkas non-gambar (PDF, video) tidak punya pratinjau, jadi
                // state-nya dikosongkan alih-alih menampilkan ikon rusak.
                // CATATAN: bukan ->visibility(), yang mengatur visibilitas
                // berkas di disk (public/private), bukan tampil-tidaknya baris.
                ->state(fn (MediaFile $record) => $record->kind() === 'gambar' ? $record->path : null),
            Tables\Columns\TextColumn::make('filename')
                ->label('Nama berkas')
                ->searchable()
                ->sortable()
                ->wrap(),
            Tables\Columns\TextColumn::make('folder')
                ->label('Folder')
                ->badge()
                ->color('gray')
                ->searchable()
                ->sortable()
                ->placeholder('(akar)'),
            Tables\Columns\TextColumn::make('extension')
                ->label('Jenis')
                ->badge()
                ->color('gray')
                ->sortable(),
            Tables\Columns\TextColumn::make('size')
                ->label('Ukuran')
                ->sortable()
                ->formatStateUsing(fn ($state) => Number::fileSize((int) $state, precision: 1)),
            Tables\Columns\TextColumn::make('usage_count')
                ->label('Dipakai')
                ->badge()
                ->sortable()
                ->state(fn (MediaFile $record) => $record->isUnused()
                    ? 'Tidak terpakai'
                    : $record->usage_count.' tempat')
                ->color(fn (MediaFile $record) => $record->isUnused() ? 'danger' : 'success')
                // Daftar lengkapnya bisa panjang, jadi ditaruh di tooltip
                // ketimbang menambah satu kolom lebar.
                ->tooltip(fn (MediaFile $record) => $record->usageSummary()),
        ];
    }

    /**
     * Tampilan galeri: pratinjau sebesar mungkin, nama berkas di bawahnya.
     *
     * Folder dan ukuran sengaja tidak ikut ke kartu -- keduanya masih ada di
     * tampilan tabel dan di penyaring, sedangkan di kartu keduanya hanya
     * menambah dua baris teks kecil pada setiap ubin.
     */
    private static function galleryColumns(): array
    {
        return [
            Tables\Columns\Layout\Stack::make([
                Tables\Columns\ImageColumn::make('path')
                    ->disk('public')
                    ->height(140)
                    ->extraImgAttributes(['class' => 'w-full h-36 object-cover rounded-lg bg-gray-100'])
                    ->state(fn (MediaFile $record) => $record->kind() === 'gambar' ? $record->path : null),
                Tables\Columns\Layout\Stack::make([
                    Tables\Columns\TextColumn::make('filename')
                        ->size(TextColumnSize::Small)
                        ->searchable()
                        ->sortable()
                        ->wrap()
                        ->limit(40),
                    Tables\Columns\TextColumn::make('usage_count')
                        ->badge()
                        ->state(fn (MediaFile $record) => $record->isUnused()
                            ? 'Tidak terpakai'
                            : $record->usage_count.' tempat')
                        ->color(fn (MediaFile $record) => $record->isUnused() ? 'danger' : 'success')
                        ->tooltip(fn (MediaFile $record) => $record->usageSummary()),
                ])->space(1),
            ])->space(2),
        ];
    }
}
