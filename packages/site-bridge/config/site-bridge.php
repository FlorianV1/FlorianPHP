<?php

declare(strict_types=1);

return [

    /*
    | Master switch. When false the site stops sending heartbeats and the
    | lock enforcement middleware stands down.
    */
    'enabled' => env('SITE_BRIDGE_ENABLED', true),

    /*
    | Outbound heartbeat transport. The site checks in to the hub, which
    | replies with a signed lock directive. Enrol with
    | `php artisan site-bridge:claim <code>`; the key it receives is stored
    | in this site's own database, never in .env.
    */
    'hub_url' => env('SITE_BRIDGE_HUB_URL', 'https://management-system.test'),

    'heartbeat' => [
        // Adaptive cadence: unhurried while unlocked, fast while locked so a
        // lift is picked up quickly. The scheduler runs every minute; the
        // command decides whether a send is due.
        'interval_unlocked_seconds' => (int) env('SITE_BRIDGE_INTERVAL_UNLOCKED', 300),
        'interval_locked_seconds' => (int) env('SITE_BRIDGE_INTERVAL_LOCKED', 60),
    ],

    /*
    | Reject a signed directive whose issued_at is older than this. Replay of
    | an older directive is already blocked by the monotonic sequence; this is
    | a secondary bound on clock-skew-independent freshness.
    */
    'directive_max_age_seconds' => (int) env('SITE_BRIDGE_DIRECTIVE_MAX_AGE', 900),

    /*
    | Toggle which optional data sections ride the heartbeat. Status is
    | always reported; these two can be turned off per site.
    */
    'expose' => [
        'metrics' => true,
        'updates' => env('SITE_BRIDGE_EXPOSE_UPDATES', true),
    ],

    /*
    | `composer outdated` is slow, so /updates caches its result.
    */
    'updates_cache_seconds' => env('SITE_BRIDGE_UPDATES_CACHE', 3600),

    /*
    | Paths and IPs that keep working when the site is locked (level 2+),
    | so you can always reach the admin panel and the bridge itself.
    */
    'lock' => [
        'allow_paths' => array_filter(explode(',', (string) env('SITE_BRIDGE_LOCK_ALLOW_PATHS', 'admin*'))),
        'allow_ips' => array_filter(explode(',', (string) env('SITE_BRIDGE_LOCK_ALLOW_IPS', ''))),
        'cache_seconds' => 15,
    ],

    /*
    | Optional credentials this site already has, so the bridge can report
    | business metrics server-side. The dashboard never needs these.
    */
    'bugsnag' => [
        'auth_token' => env('SITE_BRIDGE_BUGSNAG_TOKEN'),
        'project_id' => env('SITE_BRIDGE_BUGSNAG_PROJECT_ID'),
    ],

    'mailcoach' => [
        // Uses the MailCoach models installed in this app when available.
        'enabled' => env('SITE_BRIDGE_MAILCOACH_METRICS', true),
    ],

];
