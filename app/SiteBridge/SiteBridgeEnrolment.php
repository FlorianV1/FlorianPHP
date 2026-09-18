<?php

declare(strict_types=1);

namespace App\SiteBridge;

use App\Models\SiteBridgeClaimCode;
use App\Models\Website;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Drives the enrolment handshake: the operator issues a one-time claim code
 * on the hub, the client site redeems it (via `site-bridge:claim`) for a
 * long-lived key.
 */
final readonly class SiteBridgeEnrolment
{
    public function __construct(
        private SiteBridgeKeys $keys,
    ) {}

    /**
     * Issue a one-time claim code for the site and return its plaintext. The
     * plaintext is shown once; only its hash is stored.
     */
    public function issueClaimCode(Website $website): string
    {
        $code = 'sbc_'.Str::random(32);

        $website->claimCodes()->create([
            'code_hash' => hash('sha256', $code),
            'expires_at' => now()->addMinutes(
                (int) config('site-bridge.enrolment.claim_code_ttl_minutes', 15),
            ),
        ]);

        return $code;
    }

    /**
     * Exchange a claim code for a freshly minted key. Returns null when the
     * code is unknown, expired, or already used.
     *
     * @throws TooManyActiveKeysException when the site is at its key ceiling
     */
    public function claim(string $code): ?ClaimResult
    {
        return DB::transaction(function () use ($code): ?ClaimResult {
            $claimCode = SiteBridgeClaimCode::query()
                ->usable()
                ->where('code_hash', hash('sha256', $code))
                ->lockForUpdate()
                ->first();

            if ($claimCode === null) {
                return null;
            }

            $website = $claimCode->website;

            $max = (int) config('site-bridge.enrolment.max_active_keys_per_site', 2);

            if ($this->keys->activeCount($website) >= $max) {
                throw new TooManyActiveKeysException($max);
            }

            $plaintext = $this->keys->mint($website);
            $claimCode->forceFill(['claimed_at' => now()])->save();

            // First successful claim flips the site onto the heartbeat path;
            // the legacy pull/push becomes a no-op for it from here on.
            if ($website->enrolled_at === null) {
                $website->forceFill(['enrolled_at' => now()])->save();
            }

            return new ClaimResult($website, $plaintext);
        });
    }
}
