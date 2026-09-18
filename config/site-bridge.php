<?php

declare(strict_types=1);

return [

    /*
    | Hub-side configuration for enrolling and communicating with client
    | sites that run the site-bridge package. (The package ships its own
    | config of the same name; these keys are the dashboard's.)
    */

    'enrolment' => [
        // How long a one-time claim code stays redeemable.
        'claim_code_ttl_minutes' => (int) env('SITE_BRIDGE_CLAIM_TTL', 15),

        // Keys allowed per site at once, so rotation is add -> claim -> revoke.
        'max_active_keys_per_site' => (int) env('SITE_BRIDGE_MAX_KEYS', 2),
    ],

    'heartbeat' => [
        // Cadence the hub advertises to sites at claim time: unhurried while
        // unlocked, fast while locked so recovery is quick.
        'interval_unlocked_seconds' => (int) env('SITE_BRIDGE_INTERVAL_UNLOCKED', 300),
        'interval_locked_seconds' => (int) env('SITE_BRIDGE_INTERVAL_LOCKED', 60),

        // A site silent for longer than this is treated as stale in the UI.
        'stale_after_seconds' => (int) env('SITE_BRIDGE_STALE_AFTER', 900),

        // Raw per-check-in rows are pruned after this many days; daily
        // rollups preserve the longer-term history.
        'retention_days' => (int) env('SITE_BRIDGE_HEARTBEAT_RETENTION_DAYS', 14),
    ],

    'alerts' => [
        // ntfy topic URL (e.g. https://ntfy.sh/my-secret-topic, or a
        // self-hosted server) that receives a push when a site turns red —
        // down or silently stale — and again when it recovers. Null keeps
        // alerting off.
        'ntfy_url' => env('SITE_BRIDGE_ALERT_NTFY_URL'),
    ],

    'signing' => [
        // Ed25519 keypair used to sign lock directives. The private half must
        // stay outside the database and, crucially, survive deploys — it is
        // pinned by every enrolled site. Generate it once with
        // `php artisan site-bridge:generate-key` and back it up.
        //
        // The default lives under storage/, which Forge symlinks to a shared
        // directory outside the release path (so it survives zero-downtime
        // deploys). On any host where storage/ is NOT shared, set
        // SITE_BRIDGE_SIGNING_KEYPAIR to an absolute path that is.
        'keypair_path' => env('SITE_BRIDGE_SIGNING_KEYPAIR', storage_path('app/site-bridge/hub-signing.keypair')),

        // Reject a directive whose issued_at is older than this on the site.
        'directive_max_age_seconds' => (int) env('SITE_BRIDGE_DIRECTIVE_MAX_AGE', 900),
    ],

];
