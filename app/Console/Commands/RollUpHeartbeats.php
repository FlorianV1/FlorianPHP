<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\SiteBridgeHeartbeat;
use App\Models\SiteBridgeHeartbeatStat;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

/**
 * Aggregates a day's raw heartbeats into one row per site, so the history
 * survives the raw rows being pruned. Idempotent: safe to re-run for a day.
 */
final class RollUpHeartbeats extends Command
{
    protected $signature = 'site-bridge:rollup-heartbeats {--date= : The day to roll up (Y-m-d), defaults to yesterday}';

    protected $description = 'Roll up raw site-bridge heartbeats into daily per-site stats';

    public function handle(): int
    {
        $day = $this->option('date') !== null
            ? Carbon::parse((string) $this->option('date'))->startOfDay()
            : now()->subDay()->startOfDay();

        $byWebsite = SiteBridgeHeartbeat::query()
            ->whereBetween('received_at', [$day, $day->copy()->endOfDay()])
            ->get()
            ->groupBy('website_id');

        foreach ($byWebsite as $websiteId => $group) {
            SiteBridgeHeartbeatStat::query()->updateOrCreate(
                ['website_id' => $websiteId, 'day' => $day],
                [
                    'heartbeats' => $group->count(),
                    'max_reported_lock_level' => $group->max('reported_lock_level'),
                    'first_seen_at' => $group->min('received_at'),
                    'last_seen_at' => $group->max('received_at'),
                ],
            );
        }

        $this->info(sprintf('Rolled up %s: %d site(s).', $day->toDateString(), $byWebsite->count()));

        return self::SUCCESS;
    }
}
