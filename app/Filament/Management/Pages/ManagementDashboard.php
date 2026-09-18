<?php

namespace App\Filament\Management\Pages;

use App\Filament\Management\Widgets\FinanceStatsWidget;
use App\Filament\Management\Widgets\LeadsOverviewWidget;
use App\Filament\Management\Widgets\LeadsTrendChart;
use App\Filament\Management\Widgets\RecentLeadsWidget;
use App\Filament\Management\Widgets\RevenueChartWidget;
use App\Filament\Management\Widgets\ShouldInvoiceWidget;
use App\Filament\Management\Widgets\SiteHealthWidget;
use App\Filament\Management\Widgets\UninvoicedWorkWidget;
use BackedEnum;
use Filament\Pages\Dashboard as BaseDashboard;
use Filament\Support\Enums\Width;

/**
 * Home page of the Management panel: the agency back office at a glance.
 *
 * Ordered by what it asks of the reader — money owed and work that needs
 * invoicing first, then anything on fire, then the trends and the inbound
 * pipeline. The explicit list wins over each widget's `$sort`.
 */
class ManagementDashboard extends BaseDashboard
{
    protected static string $routePath = '/';

    protected static ?string $title = 'Overview';

    protected static ?string $navigationLabel = 'Overview';

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-home';

    protected static ?int $navigationSort = -10;

    /**
     * @return array<int, class-string>
     */
    public function getWidgets(): array
    {
        return [
            FinanceStatsWidget::class,
            LeadsOverviewWidget::class,
            ShouldInvoiceWidget::class,
            UninvoicedWorkWidget::class,
            SiteHealthWidget::class,
            RevenueChartWidget::class,
            LeadsTrendChart::class,
            RecentLeadsWidget::class,
        ];
    }

    public function getColumns(): int|array
    {
        return 1;
    }

    public function getMaxContentWidth(): Width|string|null
    {
        return Width::Full;
    }
}
