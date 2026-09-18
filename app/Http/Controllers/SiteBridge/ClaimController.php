<?php

declare(strict_types=1);

namespace App\Http\Controllers\SiteBridge;

use App\Http\Controllers\Controller;
use App\SiteBridge\LockDirectiveSigner;
use App\SiteBridge\SiteBridgeEnrolment;
use App\SiteBridge\TooManyActiveKeysException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Exchanges a one-time claim code for a long-lived key. The response also
 * carries the site's pinned id, the hub's public signing key (trust on
 * first use over TLS), and the heartbeat cadence to adopt.
 */
final class ClaimController extends Controller
{
    public function __invoke(Request $request, SiteBridgeEnrolment $enrolment, LockDirectiveSigner $signer): JsonResponse
    {
        $validated = $request->validate([
            'code' => ['required', 'string'],
        ]);

        try {
            $result = $enrolment->claim($validated['code']);
        } catch (TooManyActiveKeysException $exception) {
            abort(409, $exception->getMessage());
        }

        if ($result === null) {
            abort(422, 'Invalid or expired claim code.');
        }

        return response()->json([
            'bridge_version' => 1,
            'key' => $result->plaintextKey,
            'site_id' => $result->website->bridge_site_id,
            'hub_public_key' => $signer->publicKey(),
            'heartbeat' => [
                'interval_unlocked_seconds' => (int) config('site-bridge.heartbeat.interval_unlocked_seconds', 300),
                'interval_locked_seconds' => (int) config('site-bridge.heartbeat.interval_locked_seconds', 60),
            ],
        ]);
    }
}
