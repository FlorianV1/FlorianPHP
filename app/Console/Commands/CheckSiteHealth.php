<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Website;
use App\SiteBridge\HealthAlerts;
use Illuminate\Console\Command;

/**
 * Watches every site's traffic-light status and pushes an alert on the
 * red transition (down, failed pull, or silently stale) and again on
 * recovery. Alert state is only advanced when delivery succeeds, so a
 * failed push is retried on the next run rather than lost.
 */
final class CheckSiteHealth extends Command
{
    protected $signature = 'sites:check-health';

    protected $description = 'Push an alert when a site turns red and when it recovers';

    public function handle(HealthAlerts $alerts): int
    {
        if (! $alerts->isConfigured()) {
            $this->info('Health alerts are disabled — set SITE_BRIDGE_ALERT_NTFY_URL to enable them.');

            return self::SUCCESS;
        }

        $sent = 0;

        foreach (Website::query()->with('client')->get() as $website) {
            $isRed = $website->healthStatus() === 'red';

            if ($isRed && $website->health_alerted_at === null && ! $this->awaitingFirstCheckIn($website)) {
                if ($alerts->siteDown($website, $this->downReason($website))) {
                    $website->forceFill(['health_alerted_at' => now()])->save();
                    $sent++;
                }
            } elseif (! $isRed && $website->health_alerted_at !== null) {
                if ($alerts->siteRecovered($website)) {
                    $website->forceFill(['health_alerted_at' => null])->save();
                    $sent++;
                }
            }
        }

        $this->info("Sent {$sent} alert(s).");

        return self::SUCCESS;
    }

    /**
     * A just-enrolled site reads as stale (and therefore red) until its first
     * heartbeat lands. Give it one full staleness window before alerting.
     */
    private function awaitingFirstCheckIn(Website $website): bool
    {
        if ($website->last_heartbeat_at !== null || $website->enrolled_at === null) {
            return false;
        }

        $staleAfter = (int) config('site-bridge.heartbeat.stale_after_seconds', 900);

        return ! $website->enrolled_at->addSeconds($staleAfter)->isPast();
    }

    private function downReason(Website $website): string
    {
        if ($website->isEnrolled() && $website->heartbeatIsStale()) {
            return $website->last_heartbeat_at === null
                ? 'has never checked in'
                : 'has not checked in since '.$website->last_heartbeat_at->diffForHumans();
        }

        return 'reports itself as down';
    }
}
