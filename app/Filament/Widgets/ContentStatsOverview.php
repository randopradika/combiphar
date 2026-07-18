<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\ArticleResource;
use App\Filament\Resources\ContactMessageResource;
use App\Filament\Resources\JobVacancyResource;
use App\Filament\Resources\ProductResource;
use App\Models\Article;
use App\Models\ContactMessage;
use App\Models\JobVacancy;
use App\Models\Office;
use App\Models\Product;
use App\Models\ProductCategory;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Database\Eloquent\Builder;

/**
 * Dashboard headline numbers. Auto-registered via the panel's
 * discoverWidgets() — no explicit registration needed.
 *
 * Each stat deep-links to its resource and the time-based ones carry a
 * 12-month sparkline so trends are visible at a glance.
 */
class ContentStatsOverview extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $articles = Article::count();
        $published = Article::published()->count();
        $messages = ContactMessage::count();
        $vacancies = JobVacancy::count();
        $open = JobVacancy::where('is_open', true)->count();
        $topCategories = ProductCategory::whereNull('parent_id')->count();

        $thisMonth = ContactMessage::where('created_at', '>=', now()->startOfMonth())->count();
        $prevMonth = ContactMessage::whereBetween('created_at', [
            now()->subMonthNoOverflow()->startOfMonth(),
            now()->startOfMonth(),
        ])->count();
        $diff = $thisMonth - $prevMonth;

        return [
            Stat::make('Produk', Product::count())
                ->description($topCategories.' kategori · '.Office::count().' lokasi kantor & pabrik')
                ->descriptionIcon('heroicon-m-cube')
                ->color('primary')
                ->url(ProductResource::getUrl()),

            Stat::make('Berita', $articles)
                ->description($published === $articles
                    ? 'Semua sudah terbit'
                    : $published.' terbit, '.($articles - $published).' draf')
                ->descriptionIcon('heroicon-m-newspaper')
                ->color('primary')
                ->chart($this->monthlySparkline(Article::published(), 'published_at'))
                ->url(ArticleResource::getUrl()),

            Stat::make('Pesan Masuk', $messages)
                ->description(match (true) {
                    $diff > 0 => $thisMonth.' bulan ini · naik '.$diff.' dari bulan lalu',
                    $diff < 0 => $thisMonth.' bulan ini · turun '.abs($diff).' dari bulan lalu',
                    default => $thisMonth.' bulan ini · sama dengan bulan lalu',
                })
                ->descriptionIcon(match (true) {
                    $diff > 0 => 'heroicon-m-arrow-trending-up',
                    $diff < 0 => 'heroicon-m-arrow-trending-down',
                    default => 'heroicon-m-minus',
                })
                ->color($thisMonth > 0 ? 'magenta' : 'gray')
                ->chart($this->monthlySparkline(ContactMessage::query()))
                ->url(ContactMessageResource::getUrl()),

            Stat::make('Lowongan Aktif', $open)
                ->description($vacancies - $open > 0
                    ? ($vacancies - $open).' lowongan ditutup'
                    : 'Semua lowongan terbuka')
                ->descriptionIcon('heroicon-m-briefcase')
                ->color($open > 0 ? 'primary' : 'gray')
                ->url(JobVacancyResource::getUrl()),
        ];
    }

    /**
     * Counts per month over the trailing 12 months (oldest → newest), with
     * empty months as 0 — the shape Stat::chart() expects for a sparkline.
     */
    private function monthlySparkline(Builder $query, string $column = 'created_at'): array
    {
        $start = now()->startOfMonth()->subMonths(11);

        // DATE_FORMAT is MySQL-specific; this project is MySQL 8 only.
        $counts = $query
            ->where($column, '>=', $start)
            ->selectRaw("DATE_FORMAT({$column}, '%Y-%m') as ym, COUNT(*) as total")
            ->groupBy('ym')
            ->pluck('total', 'ym');

        return collect(range(0, 11))
            ->map(fn (int $i) => (int) ($counts[$start->copy()->addMonths($i)->format('Y-m')] ?? 0))
            ->all();
    }
}
