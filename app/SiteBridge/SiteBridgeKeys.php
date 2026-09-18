<?php

declare(strict_types=1);

namespace App\SiteBridge;

use App\Models\SiteBridgeKey;
use App\Models\Website;
use Illuminate\Support\Str;

/**
 * Mints and looks up the long-lived keys client sites use to authenticate
 * their heartbeats. The hub only ever stores a sha256 hash and a short
 * display prefix — the plaintext is returned once, at mint time, and then
 * unrecoverable.
 */
final class SiteBridgeKeys
{
    public function hash(string $plaintext): string
    {
        return hash('sha256', $plaintext);
    }

    public function findActiveByPlaintext(string $plaintext): ?SiteBridgeKey
    {
        return SiteBridgeKey::query()
            ->active()
            ->where('key_hash', $this->hash($plaintext))
            ->first();
    }

    public function activeCount(Website $website): int
    {
        return $website->bridgeKeys()->active()->count();
    }

    /**
     * Mint a new key for the site and return its one-time plaintext.
     */
    public function mint(Website $website): string
    {
        $plaintext = 'sb_live_'.Str::random(40);

        $website->bridgeKeys()->create([
            'key_hash' => $this->hash($plaintext),
            'key_prefix' => mb_substr($plaintext, 0, 16),
        ]);

        return $plaintext;
    }
}
