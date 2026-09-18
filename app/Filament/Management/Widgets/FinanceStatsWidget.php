<?php

declare(strict_types=1);

namespace App\Filament\Management\Widgets;

use App\Enums\InvoiceStatus;
use App\Models\Invoice;
use App\Models\Retainer;
use App\Models\TimeEntry;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Number;

final class FinanceStatsWidget extends StatsOverviewWidget
{
    protected static ?int $sort = 1;

    /**
     * Filament polls stats widgets every 5s by default, but invoices change
     * when someone issues or settles one — not twelve times a minute.
     */
    protected ?string $pollingInterval = null;

    protected function getStats(): array
    {
        $outstanding = (float) Invoice::query()
            ->whereIn('status', InvoiceStatus::outstanding())
            ->sum('total');

        $overdueCount = Invoice::query()->overdue()->count();

        $mrr = round(
            Retainer::query()
                ->where('active', true)
                ->get()
                ->sum(fn (Retainer $retainer): float => $retainer->monthlyAmount()),
            2,
        );

        $uninvoicedWork = round(
            TimeEntry::query()
                ->uninvoiced()
                ->with('client')
                ->get()
                ->sum(fn (TimeEntry $entry): float => $entry->amount()),
            2,
        );

        $shouldInvoice = (float) Retainer::query()
            ->where('active', true)
            ->whereDate('next_due_date', '<=', now()->endOfMonth())
            ->sum('amount') + $uninvoicedWork;

        return [
            Stat::make('Owed to me', Number::currency($outstanding, 'EUR'))
                ->description('Sent + overdue invoices')
                ->color($outstanding > 0 ? 'warning' : 'success'),
            Stat::make('Overdue invoices', (string) $overdueCount)
                ->description('Past their due date')
                ->color($overdueCount > 0 ? 'danger' : 'success'),
            Stat::make('MRR', Number::currency($mrr, 'EUR'))
                ->description('Active retainers, normalized monthly')
                ->color('info'),
            Stat::make('Should invoice', Number::currency($shouldInvoice, 'EUR'))
                ->description('Retainers due this month + un-invoiced work')
                ->color($shouldInvoice > 0 ? 'warning' : 'success'),
        ];
    }
}
