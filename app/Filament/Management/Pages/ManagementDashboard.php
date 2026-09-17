<?php

namespace App\Filament\Management\Pages;

use App\Filament\Management\Widgets\LeadsOverviewWidget;
use App\Filament\Management\Widgets\LeadsTrendChart;
use App\Filament\Management\Widgets\RecentLeadsWidget;
use BackedEnum;
use Filament\Pages\Dashboard as BaseDashboard;
use Filament\Support\Enums\Width;
use JohnRivera7\FilamentWidgetGrid\Concerns\HasWidgetGrid;

/**
 * Home page of the Management panel. Until the agency domain (clients,
 * websites, invoicing) is ported across, the one real business dataset here
 * is the inbound pipeline, so that is what this shows.
 */
class ManagementDashboard extends BaseDashboard
{
    use HasWidgetGrid;

    protected static string $routePath = '/';

    protected static ?string $title = 'Overview';

    protected static ?string $navigationLabel = 'Overview';

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-home';

    protected static ?int $navigationSort = -10;

    public function getWidgets(): array
    {
        return [
            LeadsOverviewWidget::class,
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
