<?php

namespace App\Filament\Widgets;

use App\Models\Article;
use Filament\Widgets\ChartWidget;

/**
 * Article mix by category. Only categories that actually have articles are
 * plotted, so empty ones don't clutter the ring.
 */
class ArticlesByCategoryChart extends ChartWidget
{
    protected static ?string $heading = 'Berita per Kategori';

    protected static ?int $sort = 3;

    /** Mirrors the category options in ArticleResource; unknown keys fall back to the raw value. */
    private const LABELS = [
        'pembaruan_korporasi' => 'Investor Update',
        'edukasi_gaya_hidup' => 'Health Information',
        'informasi_produk' => 'Product Info',
        'lainnya' => 'Others',
    ];

    protected function getType(): string
    {
        return 'doughnut';
    }

    public function getDescription(): ?string
    {
        return 'Total '.Article::count().' berita.';
    }

    protected function getData(): array
    {
        $counts = Article::query()
            ->selectRaw('category, COUNT(*) as total')
            ->groupBy('category')
            ->orderByDesc('total')
            ->pluck('total', 'category');

        return [
            'datasets' => [[
                'label' => 'Artikel',
                'data' => $counts->values()->all(),
                'backgroundColor' => ['#5B2D8E', '#DF0077', '#DCC4F6', '#2A005A'],
            ]],
            'labels' => $counts->keys()
                ->map(fn ($key) => self::LABELS[$key] ?? $key)
                ->all(),
        ];
    }

    protected function getOptions(): array
    {
        return [
            // A doughnut has no axes, but Filament's default chart options add
            // them — leaving a 0–1 grid drawn behind the ring.
            'scales' => [
                'x' => ['display' => false],
                'y' => ['display' => false],
            ],
        ];
    }
}
