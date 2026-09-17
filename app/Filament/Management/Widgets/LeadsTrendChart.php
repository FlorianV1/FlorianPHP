<?php

namespace App\Filament\Management\Widgets;

use App\Models\ContactMessage;
use Carbon\CarbonImmutable;
use Filament\Widgets\ChartWidget;

class LeadsTrendChart extends ChartWidget
{
    protected ?string $heading = 'Leads per week';

    protected static ?int $sort = 2;

    protected int|string|array $columnSpan = 'full';

    protected ?string $maxHeight = '260px';

    protected function getData(): array
    {
        $weeks = 12;
        $start = CarbonImmutable::now()->startOfWeek()->subWeeks($weeks - 1);

        // Grouped in PHP rather than SQL: dev runs MySQL and the test suite
        // runs sqlite, and the two disagree on week-number functions.
        $counts = array_fill(0, $weeks, 0);
        $labels = [];

        for ($i = 0; $i < $weeks; $i++) {
            $labels[] = $start->addWeeks($i)->format('d M');
        }

        ContactMessage::query()
            ->where('created_at', '>=', $start)
            ->pluck('created_at')
            ->each(function ($createdAt) use ($start, $weeks, &$counts): void {
                $index = (int) floor($start->diffInWeeks(CarbonImmutable::parse($createdAt)));

                if ($index >= 0 && $index < $weeks) {
                    $counts[$index]++;
                }
            });

        return [
            'datasets' => [
                [
                    'label' => 'Leads',
                    'data' => $counts,
                    'borderColor' => '#10b981',
                    'backgroundColor' => 'rgba(16, 185, 129, 0.15)',
                    'fill' => true,
                    'tension' => 0.3,
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
            'plugins' => ['legend' => ['display' => false]],
            'scales' => [
                'y' => ['beginAtZero' => true, 'ticks' => ['precision' => 0]],
            ],
        ];
    }
}
