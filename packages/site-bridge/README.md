# agency/site-bridge

Health reporting and a remote kill-switch for agency-managed Laravel sites. Install it into a client site; it checks in to the agency dashboard (the "hub") with a signed **outbound heartbeat** and enforces a graduated lock the hub controls. Nothing inbound is exposed — the site initiates every connection.

## Install

Via a path repository (local development):

```json
// client site's composer.json
"repositories": [
    { "type": "path", "url": "../Management-System/packages/site-bridge" }
]
```

```bash
composer require agency/site-bridge:@dev
```

Or via a private VCS/Packagist repository in production.

## Setup

```bash
# 1. Publish config (optional — env vars cover the basics)
php artisan vendor:publish --tag=site-bridge-config

# 2. Run the migration (auto-loaded; publish only if you want to customize)
php artisan migrate

# 3. Enrol with the hub, using a one-time claim code generated in the dashboard
php artisan site-bridge:claim <code>
```

```dotenv
SITE_BRIDGE_HUB_URL=https://management-system.test  # the dashboard to check in with
SITE_BRIDGE_ENABLED=true                            # master switch
```

Claiming exchanges the code for a long-lived key and the hub's public signing key, both stored in **this site's own database** — never in `.env`. No secrets ship in this package.

## How it works

The package schedules `site-bridge:heartbeat` every minute; the command itself decides whether a send is due (~5 min unlocked, ~60 s while locked). Each heartbeat POSTs the site's status, metrics and pending updates to the hub, and the hub replies with an **Ed25519-signed lock directive**. The site verifies the signature against the pinned public key, checks the directive is addressed to it, fresh, and newer than the last one applied (a monotonic sequence blocks replay), then enforces the level. If the hub is unreachable the site is **fail-open**: it retains the last verified directive and never self-escalates or self-unlocks.

## Lock levels

Enforced by a global middleware that short-circuits at level 0 and fails safe to 0 on any error. The level is set by the hub and delivered on the heartbeat — there is no inbound lock endpoint:

- **0 — Unlocked.** Normal operation.
- **1 — Deploy gate.** No user-facing effect; `php artisan site-bridge:deploy-check` exits non-zero, so add it as a CI/deploy step.
- **2 — Maintenance.** Branded 503 for public traffic. Allowlisted paths (`SITE_BRIDGE_LOCK_ALLOW_PATHS`, default `admin*`) and IPs keep working.
- **3 — Hard kill-switch.** Same as 2, plus queue workers pause and `schedule:run`/`queue:work` refuse to start. Allowlisted admin paths always keep working, and the hub can always lift the lock on the next heartbeat, so every lock is reversible remotely.

## Business metrics (optional)

Give the bridge this site's own credentials and it aggregates server-side, riding the heartbeat — the dashboard never holds them:

```dotenv
SITE_BRIDGE_BUGSNAG_TOKEN=       # Bugsnag data-access token
SITE_BRIDGE_BUGSNAG_PROJECT_ID=  # Bugsnag project id
SITE_BRIDGE_MAILCOACH_METRICS=true  # uses the MailCoach models installed in this app
```

Reported sections are versioned (`"bridge_version": 1`). Metric sub-sections degrade gracefully to `{"value": null, "reason": "..."}` — never a 500. Turn sections off per site with `site-bridge.expose.metrics` / `expose.updates`.

## Production checklist

- Serve over **HTTPS only** so the claim handshake (trust-on-first-use of the hub's public key) is protected.
- Keep `SITE_BRIDGE_HUB_URL` pointed at the real dashboard; the key and pinned public key live in the database, so back it up.
- Rotate a key by claiming a fresh code in the dashboard, then revoking the old one there.

## Tests

```bash
composer install
composer test
```
