<?php

namespace App\Filament\Portfolio\Pages;

use App\Filament\Portfolio\Widgets\ContentOverviewWidget;
use App\Filament\Portfolio\Widgets\DashboardStatsWidget;
use App\Filament\Portfolio\Widgets\PageViewsChart;
use App\Filament\Portfolio\Widgets\RecentMessagesWidget;
use App\Filament\Portfolio\Widgets\TopCountriesWidget;
use App\Filament\Portfolio\Widgets\TopPagesWidget;
use BackedEnum;
use Filament\Pages\Dashboard as BaseDashboard;
use Filament\Support\Enums\Width;

/**
 * Home page of the Portfolio panel: this site's own content and its traffic.
 *
 * Traffic and enquiries used to sit on a separate Website panel. There is no
 * such panel any more — this repo holds the company site and the command
 * centre, nothing else, and "website" now only ever means a client site
 * record over in Management.
 */
class PortfolioDashboard extends BaseDashboard
{
    protected static string $routePath = '/';

    protected static ?string $title = 'Overview';

    protected static ?string $navigationLabel = 'Overview';

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-home';

    protected static ?int $navigationSort = -10;

    protected static string|\UnitEnum|null $navigationGroup = 'Content';

    /**
     * @return array<int, class-string>
     */
    public function getWidgets(): array
    {
        return [
            DashboardStatsWidget::class,
            ContentOverviewWidget::class,
            PageViewsChart::class,
            TopPagesWidget::class,
            TopCountriesWidget::class,
            RecentMessagesWidget::class,
        ];
    }

    /**
     * Three columns on desktop: the stats, chart and content rows span the
     * full width, while top pages, countries and recent messages each claim
     * one column and sit side by side.
     */
    public function getColumns(): int|array
    {
        return [
            'default' => 1,
            'lg' => 3,
            'xl' => 3,
        ];
    }

    public function getMaxContentWidth(): Width|string|null
    {
        return Width::Full;
    }
}
