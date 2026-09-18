<?php

declare(strict_types=1);

use App\Http\Controllers\SiteBridge\ClaimController;
use App\Http\Controllers\SiteBridge\HeartbeatController;
use App\Http\Middleware\AuthenticateSiteBridgeKey;
use Illuminate\Support\Facades\Route;

/*
| Stateless machine-to-machine endpoints client sites call. Registered
| outside the web group, so no session or CSRF. Prefixed with
| `api/site-bridge` in bootstrap/app.php.
*/

Route::post('/claim', ClaimController::class)
    ->middleware('throttle:20,1')
    ->name('site-bridge.claim');

Route::post('/heartbeat', HeartbeatController::class)
    ->middleware([AuthenticateSiteBridgeKey::class, 'throttle:site-bridge-heartbeat'])
    ->name('site-bridge.heartbeat');
