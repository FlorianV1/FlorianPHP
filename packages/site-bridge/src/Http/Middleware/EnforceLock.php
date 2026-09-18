<?php

declare(strict_types=1);

namespace Agency\SiteBridge\Http\Middleware;

use Agency\SiteBridge\Support\LockState;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Registered globally. Levels: 0 unlocked, 1 deploy gate (no user-facing
 * effect), 2 maintenance for non-allowlisted traffic, 3 hard kill-switch.
 * The allowlisted admin paths/IPs always keep working, so a lock can always
 * be reverted from the panel; the lock itself is lifted out-of-band by the
 * hub's signed directive on the next heartbeat.
 */
final readonly class EnforceLock
{
    public function __construct(
        private LockState $lockState,
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        // LockState::level() is cached briefly and fail-safe (0 on any
        // error), so this is a near-free check on unlocked sites.
        $level = $this->lockState->level();

        if ($level <= 1) {
            return $next($request);
        }

        if ($this->isAllowed($request)) {
            return $next($request);
        }

        return response()
            ->view('site-bridge::locked', ['level' => $level], 503)
            ->header('Retry-After', '3600');
    }

    private function isAllowed(Request $request): bool
    {
        $allowedIps = (array) config('site-bridge.lock.allow_ips', []);

        if ($allowedIps !== [] && in_array($request->ip(), $allowedIps, true)) {
            return true;
        }

        $allowedPaths = (array) config('site-bridge.lock.allow_paths', []);

        return $allowedPaths !== [] && $request->is(...$allowedPaths);
    }
}
