<?php

declare(strict_types=1);

use Agency\SiteBridge\Support\Credential;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

it('claims a code and stores an encrypted credential', function () {
    [, $public] = hubKeypair();

    Http::fake([
        'https://hub.test/api/site-bridge/claim' => Http::response([
            'bridge_version' => 1,
            'key' => 'sb_live_'.str_repeat('a', 40),
            'site_id' => 'site-abc',
            'hub_public_key' => $public,
            'heartbeat' => ['interval_unlocked_seconds' => 300, 'interval_locked_seconds' => 60],
        ]),
    ]);

    $this->artisan('site-bridge:claim', ['code' => 'sbc_code', '--hub' => 'https://hub.test'])
        ->assertSuccessful();

    $active = app(Credential::class)->active();

    expect($active)->not->toBeNull()
        ->and($active->site_id)->toBe('site-abc')
        ->and($active->hub_url)->toBe('https://hub.test')
        ->and($active->key)->toStartWith('sb_live_')
        ->and($active->hub_public_key)->toBe($public);

    // Persisted encrypted, never as plaintext.
    $raw = DB::table('site_bridge_credentials')->first();
    expect($raw->key)->not->toContain('sb_live_');

    Http::assertSent(fn ($request): bool => $request['code'] === 'sbc_code');
});

it('fails and stores nothing when the hub rejects the code', function () {
    Http::fake([
        'https://hub.test/api/site-bridge/claim' => Http::response(['message' => 'Invalid or expired claim code.'], 422),
    ]);

    $this->artisan('site-bridge:claim', ['code' => 'bad', '--hub' => 'https://hub.test'])
        ->assertFailed();

    expect(app(Credential::class)->active())->toBeNull();
});
