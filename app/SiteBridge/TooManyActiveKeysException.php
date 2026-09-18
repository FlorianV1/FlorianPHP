<?php

declare(strict_types=1);

namespace App\SiteBridge;

use RuntimeException;

/**
 * Raised when a claim would push a site past its active-key ceiling. The
 * operator must revoke an existing key before adding another.
 */
final class TooManyActiveKeysException extends RuntimeException
{
    public function __construct(public readonly int $max)
    {
        parent::__construct("Site already has the maximum of {$max} active keys. Revoke one before claiming another.");
    }
}
