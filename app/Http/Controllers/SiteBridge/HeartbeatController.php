<?php

declare(strict_types=1);

namespace App\Http\Controllers\SiteBridge;

use App\Http\Controllers\Controller;
use App\Http\Middleware\AuthenticateSiteBridgeKey;
use App\Models\SiteBridgeKey;
use App\Models\Website;
use App\SiteBridge\LockDirectiveSigner;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Ingests an outbound heartbeat: stores the reported status/metrics as the
 * site's health snapshot, records the check-in, and answers with a signed
 * lock directive telling the site which level to enforce.
 */
final class HeartbeatController extends Controller
{
    public function __invoke(Request $request, LockDirectiveSigner $signer): JsonResponse
    {
        /** @var SiteBridgeKey $key */
        $key = $request->attributes->get(AuthenticateSiteBridgeKey::REQUEST_ATTRIBUTE);
        $website = $key->website;

        $validated = $request->validate([
            'status' => ['required', 'array'],
            'metrics' => ['nullable', 'array'],
            'updates' => ['nullable', 'array'],
            'lock_level' => ['nullable', 'integer', 'between:0,3'],
            'domain' => ['nullable', 'string', 'max:255'],
        ]);

        $reportedLevel = $validated['lock_level']
            ?? (int) data_get($validated['status'], 'lock.lock_level', $website->lock_level);

        $this->trackDomain($key, $website, $validated['domain'] ?? null);

        $website->forceFill([
            'health' => [
                'ok' => true,
                'pulled_at' => now()->toIso8601String(),
                'error' => null,
                'metrics_error' => null,
                'status' => $validated['status'],
                'metrics' => $validated['metrics'] ?? null,
                'updates' => $validated['updates'] ?? null,
            ],
            'lock_level' => (int) $reportedLevel,
            'last_heartbeat_at' => now(),
            // last_seen_at tracks the most recent contact in either direction;
            // for enrolled sites the heartbeat is that contact.
            'last_seen_at' => now(),
            // Monotonic, per-site: the site rejects any directive at or below the
            // last it accepted, so an old signed directive can't be replayed.
            // Folded into this save so a heartbeat is a single UPDATE. A site
            // checks in serially, so a read-modify-write is safe here.
            'directive_seq' => $website->directive_seq + 1,
        ])->save();

        $website->heartbeats()->create([
            'site_bridge_key_id' => $key->id,
            'ip' => $request->ip(),
            'domain' => $validated['domain'] ?? null,
            'reported_lock_level' => (int) $reportedLevel,
            'received_at' => now(),
        ]);

        $website->client->syncLockStatus();

        return response()->json([
            'bridge_version' => 1,
            'directive' => $signer->sign([
                'site_id' => $website->bridge_site_id,
                'lock_level' => $website->desired_lock_level,
                'reason' => $website->lock_reason,
                'directive_seq' => $website->directive_seq,
                'issued_at' => now()->toIso8601String(),
            ]),
            'next_interval_seconds' => $website->desired_lock_level > 0
                ? (int) config('site-bridge.heartbeat.interval_locked_seconds', 60)
                : (int) config('site-bridge.heartbeat.interval_unlocked_seconds', 300),
        ]);
    }

    /**
     * Record the domain a key checks in from and flag — never reject — a
     * mismatch against the domain we already hold for the site. Compared
     * against the Website record, not the source IP, since behind Forge or
     * Cloudflare the IP is shared or a proxy. Purely diagnostic.
     */
    private function trackDomain(SiteBridgeKey $key, Website $website, ?string $reported): void
    {
        if ($reported === null) {
            return;
        }

        $expected = $website->domain();
        $mismatch = $expected !== null && ! hash_equals($expected, mb_strtolower($reported));

        $key->forceFill([
            'first_seen_domain' => $key->first_seen_domain ?? $reported,
            'last_seen_domain' => $reported,
            'domain_mismatch_at' => $mismatch ? now() : $key->domain_mismatch_at,
        ])->save();
    }
}
