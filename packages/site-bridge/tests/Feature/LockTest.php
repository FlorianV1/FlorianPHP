<?php

declare(strict_types=1);

use Agency\SiteBridge\Support\LockState;
use Illuminate\Console\Events\CommandStarting;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;

function setLockLevel(int $level, ?string $reason = 'test'): void
{
    app(LockState::class)->set($level, $level > 0 ? $reason : null, 'tests');
}

beforeEach(function () {
    Route::get('/public-page', fn (): string => 'public ok');
    Route::get('/admin/dashboard', fn (): string => 'admin ok');
    Route::post('/admin/dashboard', fn (): string => 'admin write ok');
});

it('deploy-check passes at level 0 and fails at level 1+', function () {
    $this->artisan('site-bridge:deploy-check')->assertSuccessful();

    setLockLevel(1, 'renewal pending');

    $this->artisan('site-bridge:deploy-check')->assertFailed();
});

it('does not affect traffic at levels 0 and 1', function (int $level) {
    if ($level > 0) {
        setLockLevel($level);
    }

    $this->get('/public-page')->assertOk()->assertSee('public ok');
})->with([0, 1]);

it('serves a branded 503 to public traffic at level 2', function () {
    setLockLevel(2);

    $this->get('/public-page')
        ->assertServiceUnavailable()
        ->assertSee('temporarily unavailable')
        ->assertHeader('Retry-After');
});

it('keeps allowlisted paths working at levels 2 and 3', function (int $level) {
    setLockLevel($level);

    $this->get('/admin/dashboard')->assertOk()->assertSee('admin ok');
})->with([2, 3]);

it('blocks public traffic at level 3 but stays reversible', function () {
    setLockLevel(3);

    $this->get('/public-page')->assertServiceUnavailable();

    // The hub lifts the lock via a signed directive; simulate that landing.
    setLockLevel(0);

    $this->get('/public-page')->assertOk();
});

it('allows requests from allowlisted IPs while locked', function () {
    config()->set('site-bridge.lock.allow_ips', ['127.0.0.1']);
    config()->set('site-bridge.lock.allow_paths', []);

    setLockLevel(2);

    $this->get('/public-page')->assertOk();
});

it('fails safe to level 0 when the lock table is missing', function () {
    Schema::drop('site_bridge_lock');
    Cache::flush();

    expect(app(LockState::class)->level())->toBe(0);

    $this->get('/public-page')->assertOk();
});

// CommandStarting is rerouted from Symfony only in real CLI runs, so the
// guard is exercised by dispatching the event the way the kernel would.
it('refuses to start the scheduler and queue workers at level 3', function (string $command) {
    setLockLevel(3);

    expect(fn () => event(new CommandStarting($command, new ArrayInput([]), new NullOutput)))
        ->toThrow(RuntimeException::class, 'locked at level 3');
})->with(['schedule:run', 'queue:work']);

it('leaves other commands and lower levels alone', function () {
    setLockLevel(3);
    event(new CommandStarting('migrate', new ArrayInput([]), new NullOutput));

    setLockLevel(2);
    event(new CommandStarting('schedule:run', new ArrayInput([]), new NullOutput));

    expect(true)->toBeTrue();
});
