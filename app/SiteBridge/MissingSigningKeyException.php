<?php

declare(strict_types=1);

namespace App\SiteBridge;

use RuntimeException;

/**
 * Raised when a lock directive must be signed but the hub's keypair is
 * absent. We fail loudly rather than mint a fresh keypair: a new key would
 * invalidate every public key already pinned by enrolled sites, and with
 * fail-open-retain-last that would freeze every site at its current level.
 */
final class MissingSigningKeyException extends RuntimeException
{
    public function __construct(string $path)
    {
        parent::__construct(
            "site-bridge signing keypair is missing at {$path}. "
            .'Run `php artisan site-bridge:generate-key` (once, on a persistent path outside the release directory) and back the file up. '
            .'Never regenerate it on a hub that already has enrolled sites.',
        );
    }
}
