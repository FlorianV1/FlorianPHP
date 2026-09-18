<?php

declare(strict_types=1);

namespace Agency\SiteBridge\Support;

use Illuminate\Support\Carbon;
use Throwable;

/**
 * Verifies a signed lock directive against the pinned hub public key. A
 * directive is accepted only if the signature is valid, it is addressed to
 * this exact site, it is fresh, and its sequence advances past the last one
 * accepted. Anything else is rejected and the current lock is left untouched.
 */
final class DirectiveVerifier
{
    /**
     * @param  array<string, mixed>  $envelope  {payload, signature}, both base64
     */
    public function verify(array $envelope, string $publicKeyBase64, string $expectedSiteId, int $lastSeq): DirectiveResult
    {
        $payload = $envelope['payload'] ?? null;
        $signature = $envelope['signature'] ?? null;

        if (! is_string($payload) || ! is_string($signature)) {
            return DirectiveResult::reject('malformed envelope');
        }

        $bytes = base64_decode($payload, true);
        $sig = base64_decode($signature, true);
        $publicKey = base64_decode($publicKeyBase64, true);

        if ($bytes === false || $sig === false || $publicKey === false) {
            return DirectiveResult::reject('malformed base64');
        }

        try {
            $valid = sodium_crypto_sign_verify_detached($sig, $bytes, $publicKey);
        } catch (Throwable) {
            return DirectiveResult::reject('signature verification error');
        }

        if (! $valid) {
            return DirectiveResult::reject('bad signature');
        }

        $directive = json_decode($bytes, true);

        if (! is_array($directive)) {
            return DirectiveResult::reject('unparseable payload');
        }

        if (($directive['site_id'] ?? null) !== $expectedSiteId) {
            return DirectiveResult::reject('site_id mismatch');
        }

        if ($this->isStale($directive['issued_at'] ?? null)) {
            return DirectiveResult::reject('stale issued_at');
        }

        if ((int) ($directive['directive_seq'] ?? -1) <= $lastSeq) {
            return DirectiveResult::reject('replayed or stale sequence');
        }

        return DirectiveResult::accept($directive);
    }

    private function isStale(mixed $issuedAt): bool
    {
        if (! is_string($issuedAt)) {
            return true;
        }

        try {
            $maxAge = (int) config('site-bridge.directive_max_age_seconds', 900);

            return Carbon::parse($issuedAt)->lt(now()->subSeconds($maxAge));
        } catch (Throwable) {
            return true;
        }
    }
}
