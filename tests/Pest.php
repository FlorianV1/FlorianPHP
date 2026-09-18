<?php

use App\SiteBridge\LockDirectiveSigner;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
|
| The closure you provide to your test functions is always bound to a specific PHPUnit test
| case class. By default, that class is "PHPUnit\Framework\TestCase". Of course, you may
| need to change it using the "pest()" function to bind a different classes or traits.
|
| Unit is included as well as Feature: the ported site-bridge unit tests resolve
| services out of the container, which needs a booted application.
|
*/

pest()->extend(TestCase::class)
    ->use(RefreshDatabase::class)
    ->in('Feature', 'Unit');

/*
| The command-center tests render pages that belong to the Management panel.
| This app's DEFAULT panel is `website`, so without pinning the current panel
| Filament resolves resource URLs against the wrong one and every render dies
| on `Route [filament.portfolio.resources.*] not defined`. Scoped to the ported
| directories so the portfolio tests keep the website panel.
*/
pest()->beforeEach(function (): void {
    Filament\Facades\Filament::setCurrentPanel('management');
})->in(
    'Feature/Billing',
    'Feature/Bugsnag',
    'Feature/Filament',
    'Feature/Models',
    'Feature/SiteBridge',
);

/*
|--------------------------------------------------------------------------
| Expectations
|--------------------------------------------------------------------------
|
| When you're writing tests, you often need to check that values meet certain conditions. The
| "expect()" function gives you access to a set of "expectations" methods that you can use
| to assert different things. Of course, you may extend the Expectation API at any time.
|
*/

expect()->extend('toBeOne', function () {
    return $this->toBe(1);
});

/*
|--------------------------------------------------------------------------
| Functions
|--------------------------------------------------------------------------
|
| While Pest is very powerful out-of-the-box, you may have some testing code specific to your
| project that you don't want to repeat in every file. Here you can also expose helpers as
| global functions to help you to reduce the number of lines of code in your test files.
|
*/

/**
 * Point the signer at a throwaway keypair path and ensure it exists. The
 * signer never auto-generates in the app, so tests must create it first.
 */
function useTestSigningKey(): void
{
    config()->set('site-bridge.signing.keypair_path', storage_path('framework/testing/site-bridge-signing.keypair'));

    $signer = app(LockDirectiveSigner::class);

    if (! $signer->exists()) {
        $signer->generate();
    }
}

/**
 * @param  array<string, mixed>  $overrides
 * @return array<string, mixed>
 */
function bridgeStatusPayload(array $overrides = []): array
{
    return array_merge([
        'bridge_version' => 1,
        'app_name' => 'Client Site',
        'environment' => 'production',
        'laravel_version' => '13.0.0',
        'php_version' => '8.4.0',
        'up' => true,
        'maintenance_mode' => false,
        'lock' => ['lock_level' => 0, 'reason' => null, 'locked_at' => null, 'locked_by' => null],
    ], $overrides);
}

/**
 * @param  array<string, mixed>  $overrides
 * @return array<string, mixed>
 */
function bridgeMetricsPayload(array $overrides = []): array
{
    return array_merge([
        'bridge_version' => 1,
        'failed_jobs' => ['value' => 0, 'reason' => null],
        'disk_free_bytes' => ['value' => 50_000_000_000, 'reason' => null],
        'mailcoach' => ['value' => null, 'reason' => 'not installed'],
        'bugsnag' => ['value' => null, 'reason' => 'not configured'],
    ], $overrides);
}
