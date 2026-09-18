<?php

declare(strict_types=1);

namespace App\SiteBridge;

use App\Models\Website;

/**
 * The outcome of a successful claim: the site it was for and the one-time
 * plaintext key to hand back to that site (never persisted here).
 */
final readonly class ClaimResult
{
    public function __construct(
        public Website $website,
        public string $plaintextKey,
    ) {}
}
