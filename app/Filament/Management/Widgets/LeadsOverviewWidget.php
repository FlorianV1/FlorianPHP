<?php

namespace App\Filament\Management\Widgets;

use App\Models\ContactMessage;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

/**
 * Inbound pipeline at a glance. The Website panel counts messages as one
 * content metric among page views; here they are the business.
 */
class LeadsOverviewWidget extends StatsOverviewWidget
{
    protected static ?int $sort = 1;

    protected int|string|array $columnSpan = 'full';

    /**
     * Filament polls stats widgets every 5s by default, but counts move when a human fills in the contact form,
     * so the dashboard was re-running these queries twelve times a minute
     * for a number that had not changed.
     */
    protected ?string $pollingInterval = null;

    protected function getStats(): array
    {
        // The model hides spam by default, so these counts are genuine leads.
        $unread = ContactMessage::unread()->count();

        $thisMonth = ContactMessage::where('created_at', '>=', now()->startOfMonth())->count();

        $lastMonth = ContactMessage::query()
            ->whereBetween('created_at', [
                now()->subMonthNoOverflow()->startOfMonth(),
                now()->subMonthNoOverflow()->endOfMonth(),
            ])
            ->count();

        $spam = ContactMessage::onlySpam()->count();
        $total = ContactMessage::count();

        $change = $lastMonth > 0
            ? round((($thisMonth - $lastMonth) / $lastMonth) * 100)
            : ($thisMonth > 0 ? 100 : 0);

        $oldestUnread = ContactMessage::unread()->oldest()->value('created_at');

        return [
            Stat::make('Unread leads', $unread)
                ->description($oldestUnread
                    ? 'Oldest waiting '.$oldestUnread->diffForHumans(syntax: true)
                    : 'Nothing waiting')
                ->descriptionIcon($unread > 0 ? 'heroicon-m-envelope' : 'heroicon-m-check-circle')
                ->color($unread > 0 ? 'warning' : 'success'),

            Stat::make('This month', $thisMonth)
                ->description($change >= 0 ? "+{$change}% vs last month" : "{$change}% vs last month")
                ->descriptionIcon($change >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color($change >= 0 ? 'success' : 'danger'),

            Stat::make('Total leads', number_format($total))
                ->description('All time, excluding spam')
                ->descriptionIcon('heroicon-m-inbox-stack')
                ->color('primary'),

            Stat::make('Spam blocked', number_format($spam))
                ->description('Quarantined, never emailed')
                ->descriptionIcon('heroicon-m-shield-check')
                ->color('gray'),
        ];
    }
}
