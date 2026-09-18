<?php

namespace App\Providers;

use App\Http\Middleware\AuthenticateSiteBridgeKey;
use App\Models\SiteBridgeKey;
use App\SiteBridge\LockDirectiveSigner;
use App\Support\WindowsSafeFilesystem;
use BezhanSalleh\PanelSwitch\PanelSwitch;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Windows fails rename() when another process holds the destination
        // open, which happens whenever Filament's SPA fires parallel requests
        // that compile the same Blade view on a cold cache. `view:cache` does
        // not help — CLI and web hash view paths with different slashes.
        if (PHP_OS_FAMILY === 'Windows') {
            $this->app->singleton('files', fn (): WindowsSafeFilesystem => new WindowsSafeFilesystem);
        }
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->configurePanelSwitch();
        $this->guardSigningKey();
        $this->configureRateLimiting();
    }

    /**
     * The switcher between the two panels this repo has: the company site's
     * own CMS, and the agency back office. Entries only appear for panels the
     * signed-in user can actually reach.
     */
    private function configurePanelSwitch(): void
    {
        PanelSwitch::configureUsing(function (PanelSwitch $panelSwitch): void {
            $panelSwitch
                ->simple()
                ->labels([
                    'portfolio' => 'Portfolio',
                    'management' => 'Management',
                ])
                ->icons([
                    'portfolio' => 'heroicon-o-identification',
                    'management' => 'heroicon-o-briefcase',
                ], asImage: false)
                ->iconSize(16);
        });
    }

    /**
     * Rate-limit heartbeats per authenticated key, not per IP: many client
     * sites share one Cloudflare/Forge egress IP, so an IP bucket would let
     * one noisy site throttle the rest. Falls back to IP for unauthenticated
     * hits (which the auth middleware rejects anyway).
     */
    private function configureRateLimiting(): void
    {
        RateLimiter::for('site-bridge-heartbeat', function (Request $request): Limit {
            $key = $request->attributes->get(AuthenticateSiteBridgeKey::REQUEST_ATTRIBUTE);

            return Limit::perMinute(120)->by(
                $key instanceof SiteBridgeKey ? "sbk:{$key->getKey()}" : (string) $request->ip(),
            );
        });
    }

    /**
     * Fail loudly — never silently — when the directive signing key is
     * absent in production. A missing key means locks cannot be changed on
     * any enrolled site, so it must be visible immediately.
     */
    private function guardSigningKey(): void
    {
        if (! $this->app->isProduction()) {
            return;
        }

        if (! $this->app->make(LockDirectiveSigner::class)->exists()) {
            Log::critical('site-bridge: signing keypair is missing — lock directives cannot be signed. Run `php artisan site-bridge:generate-key` on a persistent path and restore your backup.');
        }
    }
}
