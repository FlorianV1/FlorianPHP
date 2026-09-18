<?php

declare(strict_types=1);

namespace App\Filament\Management\Widgets;

use App\Enums\InvoiceStatus;
use App\Models\Invoice;
use Filament\Support\Facades\FilamentColor;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Collection;

/**
 * Invoiced against paid, bucketed by month.
 *
 * The one chart here that carries two series, so it keeps a legend and needs
 * two explicit colours — Filament's `$color` covers a single series only.
 * They are read from the panel's registered palette rather than written as
 * hex, so a palette override moves them too.
 */
final class RevenueChartWidget extends ChartWidget
{
    protected ?string $heading = 'Revenue — invoiced vs. paid';

    protected static ?int $sort = 4;

    protected int|string|array $columnSpan = 'full';

    protected ?string $maxHeight = '280px';

    /**
     * Invoices change when someone issues or settles one, so there is nothing
     * to poll for. Filament's 5s default re-ran both aggregates twelve times
     * a minute.
     */
    protected ?string $pollingInterval = null;

    public ?string $filter = '12';

    /**
     * @return array<string, string>
     */
    protected function getFilters(): ?array
    {
        return [
            '6' => 'Last 6 months',
            '12' => 'Last 12 months',
            '24' => 'Last 24 months',
        ];
    }

    /**
     * @return array{labels: array<int, string>, datasets: array<int, array<string, mixed>>}
     */
    protected function getData(): array
    {
        $months = (int) ($this->filter ?? '12');

        $buckets = collect(range($months - 1, 0))
            ->map(fn (int $offset) => now()->startOfMonth()->subMonths($offset));

        $start = $buckets->first();

        $invoices = Invoice::query()
            ->whereDate('issue_date', '>=', $start)
            // Drafts aren't revenue yet; credit notes reverse it, not add to it.
            ->whereNotIn('status', [InvoiceStatus::Draft, InvoiceStatus::Credited])
            ->get(['issue_date', 'total']);

        $paid = Invoice::query()
            ->whereNotNull('paid_at')
            ->whereDate('paid_at', '>=', $start)
            ->get(['paid_at', 'total']);

        $invoicedByMonth = $invoices->groupBy(fn (Invoice $invoice): string => $invoice->issue_date->format('Y-m'));
        $paidByMonth = $paid->groupBy(fn (Invoice $invoice): string => $invoice->paid_at->format('Y-m'));

        $sum = fn (Collection $grouped, string $month): float => round(
            (float) ($grouped[$month] ?? collect())->sum('total'),
            2,
        );

        return [
            'labels' => $buckets->map(fn ($month): string => $month->format('M y'))->all(),
            'datasets' => [
                [
                    'label' => 'Invoiced',
                    'data' => $buckets->map(fn ($month): float => $sum($invoicedByMonth, $month->format('Y-m')))->all(),
                    'backgroundColor' => $this->paletteColor('info'),
                ],
                [
                    'label' => 'Paid',
                    'data' => $buckets->map(fn ($month): float => $sum($paidByMonth, $month->format('Y-m')))->all(),
                    'backgroundColor' => $this->paletteColor('success'),
                ],
            ],
        ];
    }

    /**
     * Mid shade of a registered Filament colour. Falls back to Chart.js'
     * own default when a panel has not registered the alias at all.
     */
    private function paletteColor(string $alias): ?string
    {
        return FilamentColor::getColor($alias)[500] ?? null;
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
            'scales' => [
                'y' => ['beginAtZero' => true],
            ],
        ];
    }
}
