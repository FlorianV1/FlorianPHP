<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\SiteBridge\SiteBridgeKeys;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Authenticates an inbound heartbeat by its `sb_live_` bearer key. The
 * matched key (and, through it, the website) is stashed on the request for
 * the controller. Revoked or unknown keys get a flat 401.
 */
final readonly class AuthenticateSiteBridgeKey
{
    public const REQUEST_ATTRIBUTE = 'siteBridgeKey';

    public function __construct(
        private SiteBridgeKeys $keys,
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        $bearer = (string) $request->bearerToken();

        $key = $bearer === '' ? null : $this->keys->findActiveByPlaintext($bearer);

        if ($key === null) {
            abort(401, 'Invalid site-bridge key.');
        }

        $key->forceFill(['last_used_at' => now()])->save();

        $request->attributes->set(self::REQUEST_ATTRIBUTE, $key);

        return $next($request);
    }
}
