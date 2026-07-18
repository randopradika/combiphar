<?php

namespace App\Filament\Widgets;

use App\Models\ContactMessage;
use Filament\Widgets\ChartWidget;

/**
 * Contact-form volume over time as a line chart, filterable to the trailing
 * 3/6/12 months. Months with no messages are filled with 0 so the axis stays
 * continuous rather than skipping gaps.
 */
class ContactMessagesChart extends ChartWidget
{
    protected static ?string $heading = 'Tren Pesan Masuk';

    protected static ?int $sort = 2;

    protected int|string|array $columnSpan = 'full';

    public ?string $filter = '12';

    protected function getType(): string
    {
        return 'line';
    }

    protected function getFilters(): ?array
    {
        return [
            '3' => '3 bulan terakhir',
            '6' => '6 bulan terakhir',
            '12' => '12 bulan terakhir',
        ];
    }

    public function getDescription(): ?string
    {
        $months = $this->months();
        $total = ContactMessage::where('created_at', '>=', now()->startOfMonth()->subMonths($months - 1))->count();

        return "Total {$total} pesan dalam {$months} bulan terakhir.";
    }

    private function months(): int
    {
        // Guard against an unexpected filter value from the client.
        $months = (int) $this->filter;

        return in_array($months, [3, 6, 12], true) ? $months : 12;
    }

    protected function getData(): array
    {
        $months = $this->months();
        $start = now()->startOfMonth()->subMonths($months - 1);

        // DATE_FORMAT is MySQL-specific; this project is MySQL 8 only.
        $counts = ContactMessage::query()
            ->where('created_at', '>=', $start)
            ->selectRaw("DATE_FORMAT(created_at, '%Y-%m') as ym, COUNT(*) as total")
            ->groupBy('ym')
            ->pluck('total', 'ym');

        $labels = [];
        $data = [];

        for ($i = 0; $i < $months; $i++) {
            $month = $start->copy()->addMonths($i);
            $labels[] = $month->translatedFormat('M Y');
            $data[] = (int) ($counts[$month->format('Y-m')] ?? 0);
        }

        return [
            'datasets' => [[
                'label' => 'Pesan',
                'data' => $data,
                'borderColor' => '#DF0077',
                'backgroundColor' => 'rgba(223, 0, 119, 0.08)',
                'fill' => true,
                'tension' => 0.35,
                'pointBackgroundColor' => '#DF0077',
                'pointBorderColor' => '#fff',
                'pointRadius' => 4,
                'pointHoverRadius' => 6,
            ]],
            'labels' => $labels,
        ];
    }

    protected function getOptions(): array
    {
        return [
            'plugins' => ['legend' => ['display' => false]],
            // Counts are whole numbers — stop Chart.js inventing 0.5 gridlines.
            'scales' => ['y' => ['beginAtZero' => true, 'ticks' => ['precision' => 0]]],
        ];
    }
}
