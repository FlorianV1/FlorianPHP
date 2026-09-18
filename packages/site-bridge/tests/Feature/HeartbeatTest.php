<?php

declare(strict_types=1);

use Agency\SiteBridge\Support\Credential;
use Agency\SiteBridge\Support\LockState;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

beforeEach(function () {
    // Keep the composer-outdated subprocess out of the heartbeat path in tests.
    config()->set('site-bridge.expose.updates', false);
});

function enrol(string $publicKey, string $siteId = 'site-abc'): void
{
    app(Credential::class)->store('sb_live_'.str_repeat('a', 40), 'https://hub.test', $siteId, $publicKey);
}

function fakeHubDirective(array $envelope): void
{
    Http::fake([
        'https://hub.test/api/site-bridge/heartbeat' => Http::response([
            'bridge_version' => 1,
            'directive' => $envelope,
            'next_interval_seconds' => 300,
        ]),
    ]);
}

it('skips cleanly when the site is not enrolled', function () {
    Http::fake();

    $this->artisan('site-bridge:heartbeat')->assertSuccessful();

    Http::assertNothingSent();
});

it('applies a valid signed directive', function () {
    [$secret, $public] = hubKeypair();
    enrol($public);

    fakeHubDirective(signDirective([
        'site_id' => 'site-abc',
        'lock_level' => 3,
        'reason' => 'unpaid invoice',
        'directive_seq' => 5,
        'issued_at' => now()->toIso8601String(),
    ], $secret));

    $this->artisan('site-bridge:heartbeat')->assertSuccessful();

    expect(app(LockState::class)->level())->toBe(3)
        ->and(app(LockState::class)->directiveSeq())->toBe(5);
});

it('stays fail-open and retains the last directive when the hub is unreachable', function () {
    [, $public] = hubKeypair();
    enrol($public);
    app(LockState::class)->applyDirective(3, 'locked', 4);

    Http::fake(['https://hub.test/*' => fn () => throw new ConnectionException('hub down')]);

    $this->artisan('site-bridge:heartbeat')->assertSuccessful();

    // Never self-unlocks on unreachability.
    expect(app(LockState::class)->level())->toBe(3)
        ->and(app(LockState::class)->directiveSeq())->toBe(4);
});

it('rejects a directive signed by the wrong key', function () {
    [, $public] = hubKeypair();
    [$otherSecret] = hubKeypair();
    enrol($public);
    app(LockState::class)->applyDirective(0, null, 1);

    fakeHubDirective(signDirective([
        'site_id' => 'site-abc',
        'lock_level' => 3,
        'reason' => 'forged',
        'directive_seq' => 2,
        'issued_at' => now()->toIso8601String(),
    ], $otherSecret));

    $this->artisan('site-bridge:heartbeat')->assertSuccessful();

    expect(app(LockState::class)->level())->toBe(0)
        ->and(app(LockState::class)->directiveSeq())->toBe(1);
});

it('rejects a directive addressed to another site', function () {
    [$secret, $public] = hubKeypair();
    enrol($public, 'site-abc');
    app(LockState::class)->applyDirective(0, null, 1);

    fakeHubDirective(signDirective([
        'site_id' => 'someone-else',
        'lock_level' => 3,
        'reason' => 'wrong site',
        'directive_seq' => 2,
        'issued_at' => now()->toIso8601String(),
    ], $secret));

    $this->artisan('site-bridge:heartbeat')->assertSuccessful();

    expect(app(LockState::class)->level())->toBe(0);
});

it('rejects a replayed directive at or below the last sequence', function () {
    [$secret, $public] = hubKeypair();
    enrol($public);
    app(LockState::class)->applyDirective(3, 'locked', 5);

    // An attacker replays an old level-0 unlock at seq 5.
    fakeHubDirective(signDirective([
        'site_id' => 'site-abc',
        'lock_level' => 0,
        'reason' => null,
        'directive_seq' => 5,
        'issued_at' => now()->toIso8601String(),
    ], $secret));

    $this->artisan('site-bridge:heartbeat')->assertSuccessful();

    expect(app(LockState::class)->level())->toBe(3)
        ->and(app(LockState::class)->directiveSeq())->toBe(5);
});

it('rejects a stale directive', function () {
    [$secret, $public] = hubKeypair();
    enrol($public);
    app(LockState::class)->applyDirective(0, null, 1);

    fakeHubDirective(signDirective([
        'site_id' => 'site-abc',
        'lock_level' => 3,
        'reason' => 'too old',
        'directive_seq' => 9,
        'issued_at' => now()->subHour()->toIso8601String(),
    ], $secret));

    $this->artisan('site-bridge:heartbeat')->assertSuccessful();

    expect(app(LockState::class)->level())->toBe(0);
});

it('does not send when a heartbeat is not yet due', function () {
    [, $public] = hubKeypair();
    enrol($public);
    Cache::put('site-bridge:heartbeat-sent-at', now()->toIso8601String());

    Http::fake();

    $this->artisan('site-bridge:heartbeat')->assertSuccessful();

    Http::assertNothingSent();
});
