<?php

namespace App\Filament\Website\Widgets;

use App\Models\PageView;
use Filament\Widgets\ChartWidget;

class PageViewsChart extends ChartWidget
{
    protected static ?int $sort = 2;

    protected ?string $heading = 'Page Views — Last 30 Days';

    protected int | string | array $columnSpan = 'full';

    protected ?string $maxHeight = '260px';

    protected function getData(): array
    {
        $days   = 30;
        $start  = now()->subDays($days - 1)->startOfDay();

        $viewsByDate = PageView::query()
            ->selectRaw('DATE(created_at) as date, count(*) as count')
            ->where('created_at', '>=', $start)
            ->groupBy('date')
            ->pluck('count', 'date');

        $labels = [];
        $data   = [];

        for ($i = $days - 1; $i >= 0; $i--) {
            $date     = now()->subDays($i)->format('Y-m-d');
            $labels[] = now()->subDays($i)->format('M j');
            $data[]   = (int) ($viewsByDate->get($date, 0));
        }

        return [
            'datasets' => [
                [
                    'label'           => 'Page Views',
                    'data'            => $data,
                    'borderColor'     => '#6366f1',
                    'backgroundColor' => 'rgba(99, 102, 241, 0.08)',
                    'fill'            => true,
                    'tension'         => 0.4,
                    'pointRadius'     => 3,
                    'pointHoverRadius'=> 5,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }

    protected function getOptions(): array
    {
        return [
            'plugins' => [
                'legend' => ['display' => false],
            ],
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                    'ticks'       => ['precision' => 0],
                ],
            ],
        ];
    }
}
