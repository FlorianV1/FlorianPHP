<?php

namespace App\Filament\Portfolio\Widgets;

use App\Models\PageView;
use Filament\Widgets\ChartWidget;

/**
 * Page views bucketed by day.
 *
 * Bars, not a line: these are counts in discrete buckets, and a smoothed line
 * draws values between two days that were never measured. Colours are left to
 * Filament — the widget inherits `$color = 'primary'`, so it takes the panel's
 * Blue and follows the reader into dark mode. Setting them on the dataset, as
 * this widget used to, opts out of all of that.
 */
class PageViewsChart extends ChartWidget
{
    protected static ?int $sort = 2;

    protected ?string $heading = 'Page views';

    protected int|string|array $columnSpan = 'full';

    protected ?string $maxHeight = '260px';

    /**
     * A visitor counter is not a live dial — the page is read, not watched.
     * Filament's 5s default re-aggregated the whole table twelve times a
     * minute and redrew the chart with every pass.
     */
    protected ?string $pollingInterval = null;

    public ?string $filter = '30';

    /**
     * Daily buckets throughout, so the span is the only thing that changes.
     *
     * @return array<string, string>
     */
    protected function getFilters(): ?array
    {
        return [
            '7' => 'Last 7 days',
            '30' => 'Last 30 days',
            '90' => 'Last 90 days',
        ];
    }

    /**
     * @return array{datasets: array<int, array{label: string, data: array<int, int>}>, labels: array<int, string>}
     */
    protected function getData(): array
    {
        $days = (int) ($this->filter ?? '30');
        $start = now()->subDays($days - 1)->startOfDay();

        $viewsByDate = PageView::query()
            ->selectRaw('DATE(created_at) as date, count(*) as count')
            ->where('created_at', '>=', $start)
            ->groupBy('date')
            ->pluck('count', 'date');

        $labels = [];
        $data = [];

        for ($day = $days - 1; $day >= 0; $day--) {
            $date = now()->subDays($day);

            $labels[] = $date->format('M j');
            $data[] = (int) $viewsByDate->get($date->format('Y-m-d'), 0);
        }

        return [
            'datasets' => [
                [
                    'label' => 'Page views',
                    'data' => $data,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }

    /**
     * @return array<string, mixed>
     */
    protected function getOptions(): array
    {
        return [
            // One series, already named by the heading.
            'plugins' => ['legend' => ['display' => false]],
            'scales' => [
                'y' => ['beginAtZero' => true, 'ticks' => ['precision' => 0]],
            ],
        ];
    }
}
