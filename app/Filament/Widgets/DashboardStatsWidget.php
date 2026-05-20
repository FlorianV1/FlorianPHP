<?php

namespace App\Filament\Widgets;

use App\Models\ContactMessage;
use App\Models\PageView;
use App\Models\Project;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class DashboardStatsWidget extends BaseWidget
{
    protected static ?int $sort = 1;

    protected int | string | array $columnSpan = 'full';

    protected function getStats(): array
    {
        $totalViews   = PageView::count();
        $todayViews   = PageView::whereDate('created_at', today())->count();
        $weekViews    = PageView::where('created_at', '>=', now()->subWeek())->count();
        $yesterdayViews = PageView::whereDate('created_at', today()->subDay())->count();

        $uniqueVisitors = PageView::distinct('ip')->count('ip');

        $topCountry = PageView::whereNotNull('country')
            ->selectRaw('country, count(*) as total')
            ->groupBy('country')
            ->orderByDesc('total')
            ->first();

        $unreadMessages = ContactMessage::unread()->count();
        $publishedProjects = Project::posted()->count();

        $todayVsYesterday = $yesterdayViews > 0
            ? round((($todayViews - $yesterdayViews) / $yesterdayViews) * 100, 1)
            : ($todayViews > 0 ? 100 : 0);

        return [
            Stat::make('Total Views', number_format($totalViews))
                ->description('All time page views')
                ->descriptionIcon('heroicon-m-eye')
                ->color('gray'),

            Stat::make('Today', $todayViews)
                ->description($todayVsYesterday >= 0
                    ? "+{$todayVsYesterday}% vs yesterday"
                    : "{$todayVsYesterday}% vs yesterday")
                ->descriptionIcon($todayVsYesterday >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color($todayVsYesterday >= 0 ? 'success' : 'danger'),

            Stat::make('This Week', number_format($weekViews))
                ->description('Last 7 days')
                ->descriptionIcon('heroicon-m-calendar-days')
                ->color('primary'),

            Stat::make('Unique Visitors', number_format($uniqueVisitors))
                ->description('Distinct IP addresses')
                ->descriptionIcon('heroicon-m-users')
                ->color('info'),

            Stat::make('Top Country', $topCountry?->country ?? '—')
                ->description($topCountry ? number_format($topCountry->total) . ' views' : 'No data yet')
                ->descriptionIcon('heroicon-m-globe-alt')
                ->color('warning'),

            Stat::make('Unread Messages', $unreadMessages)
                ->description("{$publishedProjects} projects published")
                ->descriptionIcon($unreadMessages > 0 ? 'heroicon-m-envelope' : 'heroicon-m-check-circle')
                ->color($unreadMessages > 0 ? 'danger' : 'success'),
        ];
    }
}
