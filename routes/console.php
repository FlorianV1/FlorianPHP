<?php

use App\Models\SiteBridgeHeartbeat;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('invoices:mark-overdue')->dailyAt('06:00');

// Draft (never send) the invoice for every retainer whose period has started,
// then mail the admins what is waiting. Runs after mark-overdue so the summary
// mail reflects the day's statuses.
Schedule::command('invoices:issue-recurring')->dailyAt('06:15')->withoutOverlapping();

// Push an alert the minute a site turns red (down or silent) or recovers.
Schedule::command('sites:check-health')->everyMinute();

// Pull open Bugsnag error counts for every linked site. No-ops until an
// agency auth token is configured.
Schedule::command('bugsnag:sync-errors')->hourly()->withoutOverlapping();

// Preserve heartbeat history as daily rollups, then prune the raw rows.
Schedule::command('site-bridge:rollup-heartbeats')->dailyAt('00:15');

Schedule::command('model:prune', ['--model' => [SiteBridgeHeartbeat::class]])->dailyAt('00:30');
