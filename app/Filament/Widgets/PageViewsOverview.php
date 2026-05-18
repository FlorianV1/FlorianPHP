<?php

namespace App\Filament\Widgets;

use App\Models\PageView;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class PageViewsOverview extends BaseWidget
{
    protected function getStats(): array
    {
        $topCountry = PageView::whereNotNull('country')
            ->selectRaw('country, count(*) as total')
            ->groupBy('country')
            ->orderByDesc('total')
            ->first();

        return [
            Stat::make('Total Views', PageView::count())
                ->description('All time')
                ->icon('heroicon-o-eye'),

            Stat::make('Today', PageView::whereDate('created_at', today())->count())
                ->description('Views today')
                ->icon('heroicon-o-calendar'),

            Stat::make('This Week', PageView::where('created_at', '>=', now()->subWeek())->count())
                ->description('Last 7 days')
                ->icon('heroicon-o-chart-bar'),

            Stat::make('Unique Visitors', PageView::distinct('ip')->count('ip'))
                ->description('By IP address')
                ->icon('heroicon-o-users'),

            Stat::make('Top Country', $topCountry?->country ?? '—')
                ->description($topCountry ? $topCountry->total . ' views' : 'No data yet')
                ->icon('heroicon-o-globe-alt'),
        ];
    }
}
