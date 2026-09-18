<?php

namespace App\Filament\Management\Widgets;

use App\Models\ContactMessage;
use Carbon\CarbonImmutable;
use Filament\Widgets\ChartWidget;

/**
 * Inbound leads bucketed by week.
 *
 * Bars, not a line: these are counts in discrete buckets, and a smoothed line
 * draws values between two weeks that were never measured. Colours are left
 * to Filament — the widget inherits `$color = 'primary'`, so it takes the
 * panel's Emerald and follows the reader into dark mode. Setting them on the
 * dataset, as this widget used to, opts out of all of that.
 */
class LeadsTrendChart extends ChartWidget
{
    protected ?string $heading = 'Leads per week';

    protected static ?int $sort = 2;

    protected int|string|array $columnSpan = 'full';

    protected ?string $maxHeight = '260px';

    /**
     * Leads arrive when a human fills in the contact form, so there is nothing
     * to poll for. Filament's 5s default re-counted the table twelve times a
     * minute and redrew the chart with every pass.
     */
    protected ?string $pollingInterval = null;

    public ?string $filter = '12';

    /**
     * Weekly buckets throughout, so the span is the only thing that changes.
     *
     * @return array<string, string>
     */
    protected function getFilters(): ?array
    {
        return [
            '8' => 'Last 8 weeks',
            '12' => 'Last 12 weeks',
            '26' => 'Last 26 weeks',
            '52' => 'Last 52 weeks',
        ];
    }

    /**
     * @return array{datasets: array<int, array{label: string, data: array<int, int>}>, labels: array<int, string>}
     */
    protected function getData(): array
    {
        $weeks = (int) ($this->filter ?? '12');
        $start = CarbonImmutable::now()->startOfWeek()->subWeeks($weeks - 1);

        // Bucketed in PHP rather than SQL: dev runs MySQL and the test suite
        // runs sqlite, and the two disagree on week-number functions. Keying
        // by the week's start date means a row lands in its bucket or in none.
        $counts = [];
        $labels = [];

        for ($week = 0; $week < $weeks; $week++) {
            $weekStart = $start->addWeeks($week);

            $counts[$weekStart->toDateString()] = 0;
            $labels[] = $weekStart->format('d M');
        }

        ContactMessage::query()
            ->where('created_at', '>=', $start)
            ->pluck('created_at')
            ->each(function ($createdAt) use (&$counts): void {
                $bucket = CarbonImmutable::parse($createdAt)->startOfWeek()->toDateString();

                if (array_key_exists($bucket, $counts)) {
                    $counts[$bucket]++;
                }
            });

        return [
            'datasets' => [
                [
                    'label' => 'Leads',
                    'data' => array_values($counts),
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
