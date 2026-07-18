<?php

namespace App\Filament\Widgets;

use App\Models\Product;
use App\Models\ProductCategory;
use Filament\Widgets\ChartWidget;

/**
 * Products per top-level category. Products hang off sub-categories, so counts
 * are rolled up to the parent (COALESCE(parent_id, id)) to match how the site
 * groups them.
 */
class ProductsByCategoryChart extends ChartWidget
{
    protected static ?string $heading = 'Produk per Kategori';

    protected static ?int $sort = 4;

    protected function getType(): string
    {
        return 'bar';
    }

    public function getDescription(): ?string
    {
        return 'Total '.Product::count().' produk, dikelompokkan ke kategori utama.';
    }

    protected function getData(): array
    {
        $counts = Product::query()
            ->join('product_categories as pc', 'pc.id', '=', 'products.product_category_id')
            ->selectRaw('COALESCE(pc.parent_id, pc.id) as top_id, COUNT(*) as total')
            ->groupBy('top_id')
            ->orderByDesc('total')
            ->pluck('total', 'top_id');

        $names = ProductCategory::whereIn('id', $counts->keys())
            ->get()
            ->mapWithKeys(fn (ProductCategory $c) => [$c->id => $c->tr('name')]);

        return [
            'datasets' => [[
                'label' => 'Produk',
                'data' => $counts->values()->all(),
                'backgroundColor' => '#5B2D8E',
                'borderRadius' => 4,
            ]],
            'labels' => $counts->keys()
                ->map(fn ($id) => $names[$id] ?? '—')
                ->all(),
        ];
    }

    protected function getOptions(): array
    {
        return [
            'plugins' => ['legend' => ['display' => false]],
            'scales' => ['y' => ['beginAtZero' => true, 'ticks' => ['precision' => 0]]],
        ];
    }
}
