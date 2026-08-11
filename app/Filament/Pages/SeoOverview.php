<?php

namespace App\Filament\Pages;

use App\Filament\Resources\PageResource;
use App\Models\Page;
use Filament\Pages\Page as BasePage;
use Filament\Tables;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;

/**
 * Ringkasan meta title & description seluruh halaman.
 *
 * Bidangnya sudah lama ada, tetapi terkubur di dalam masing-masing record
 * halaman — sehingga satu-satunya cara mengetahui halaman mana yang belum
 * punya deskripsi adalah membuka semuanya satu per satu lalu mengingatnya.
 * Halaman ini hanya membaca; penyuntingan tetap di record halaman.
 */
class SeoOverview extends BasePage implements HasTable
{
    use InteractsWithTable;

    protected static string $view = 'filament.pages.seo-overview';

    protected static ?string $navigationGroup = 'Pengaturan';

    protected static ?string $navigationLabel = 'Ringkasan SEO';

    protected static ?int $navigationSort = 3;

    protected static ?string $navigationIcon = 'heroicon-o-magnifying-glass';

    protected static ?string $title = 'Ringkasan SEO';

    /** Panjang deskripsi yang biasanya tampil utuh di hasil pencarian. */
    private const DESC_MIN = 70;

    private const DESC_MAX = 160;

    /** Judul yang lebih panjang dari ini biasanya dipotong. */
    private const TITLE_MAX = 60;

    public function getSubheading(): string
    {
        return 'Halaman dengan bidang meta kosong tidak punya deskripsi di hasil pencarian — mesin pencari akan menyusun cuplikannya sendiri. Isinya disunting di menu Halaman.';
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(Page::query()->orderBy('slug'))
            ->paginated(false)
            ->columns([
                Tables\Columns\TextColumn::make('slug')
                    ->label('Halaman')
                    ->searchable(),
                $this->lengthColumn('meta_title_id', 'Judul (ID)', self::TITLE_MAX),
                $this->lengthColumn('meta_title_en', 'Judul (EN)', self::TITLE_MAX),
                $this->lengthColumn('meta_description_id', 'Deskripsi (ID)', self::DESC_MAX, self::DESC_MIN),
                $this->lengthColumn('meta_description_en', 'Deskripsi (EN)', self::DESC_MAX, self::DESC_MIN),
            ])
            ->actions([
                Tables\Actions\Action::make('edit')
                    ->label('Sunting')
                    ->icon('heroicon-m-pencil-square')
                    ->url(fn (Page $record) => PageResource::getUrl('edit', ['record' => $record]).'?tab=seo'),
            ]);
    }

    /**
     * Satu kolom yang menunjukkan sekaligus: terisi atau tidak, dan apakah
     * panjangnya wajar. Jumlah karakter ditampilkan apa adanya agar editor tahu
     * seberapa jauh dari batas, bukan sekadar "terlalu panjang".
     */
    private function lengthColumn(string $field, string $label, int $max, ?int $min = null): Tables\Columns\TextColumn
    {
        return Tables\Columns\TextColumn::make($field)
            ->label($label)
            ->badge()
            ->state(function (Page $record) use ($field, $max, $min) {
                $value = trim((string) $record->{$field});
                $len = mb_strlen($value);

                if ($len === 0) {
                    return 'Kosong';
                }

                if ($len > $max) {
                    return $len.' — terlalu panjang';
                }

                if ($min !== null && $len < $min) {
                    return $len.' — agak pendek';
                }

                return (string) $len;
            })
            ->color(fn (string $state) => match (true) {
                $state === 'Kosong' => 'danger',
                str_contains($state, 'terlalu panjang'), str_contains($state, 'agak pendek') => 'warning',
                default => 'success',
            })
            ->tooltip(fn (Page $record) => trim((string) $record->{$field}) ?: null);
    }
}
