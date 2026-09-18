<?php

declare(strict_types=1);

use App\Enums\ClientStatus;
use App\Models\Website;
use App\SiteBridge\LockDirectiveSigner;
use App\SiteBridge\SiteBridgeKeys;

beforeEach(fn () => useTestSigningKey());

function enrolledKey(Website $website): string
{
    return app(SiteBridgeKeys::class)->mint($website);
}

function heartbeatBody(array $overrides = []): array
{
    return array_merge([
        'status' => bridgeStatusPayload(),
        'metrics' => bridgeMetricsPayload(),
        'lock_level' => 0,
        'domain' => null,
    ], $overrides);
}

it('ingests a heartbeat and stores the health snapshot', function () {
    $website = Website::factory()->create();
    $key = enrolledKey($website);

    $this->withToken($key)
        ->postJson('api/site-bridge/heartbeat', heartbeatBody())
        ->assertOk()
        ->assertJsonPath('bridge_version', 1)
        ->assertJsonStructure(['directive' => ['payload', 'signature'], 'next_interval_seconds']);

    $website->refresh();

    expect($website->last_heartbeat_at)->not->toBeNull()
        ->and($website->last_seen_at)->not->toBeNull()
        ->and(data_get($website->health, 'ok'))->toBeTrue()
        ->and(data_get($website->health, 'status.app_name'))->toBe('Client Site')
        ->and($website->healthStatus())->toBe('green')
        ->and($website->heartbeats()->count())->toBe(1);
});

it('surfaces an enrolled site as red once its heartbeat goes stale', function () {
    $staleAfter = (int) config('site-bridge.heartbeat.stale_after_seconds', 900);

    // A green snapshot that is nonetheless stale must not read as healthy.
    $website = Website::factory()->create([
        'enrolled_at' => now()->subDay(),
        'last_heartbeat_at' => now()->subSeconds($staleAfter + 60),
        'health' => ['ok' => true, 'status' => bridgeStatusPayload()],
    ]);

    expect($website->heartbeatIsStale())->toBeTrue()
        ->and($website->healthStatus())->toBe('red');

    // A fresh check-in clears it.
    $website->forceFill(['last_heartbeat_at' => now()])->save();

    expect($website->refresh()->healthStatus())->toBe('green');
});

it('returns a directive that verifies against the hub public key', function () {
    $website = Website::factory()->create(['desired_lock_level' => 3, 'lock_reason' => 'unpaid invoice']);
    $key = enrolledKey($website);

    $directive = $this->withToken($key)
        ->postJson('api/site-bridge/heartbeat', heartbeatBody())
        ->assertOk()
        ->json('directive');

    $payload = base64_decode($directive['payload']);
    $signature = base64_decode($directive['signature']);
    $publicKey = base64_decode(app(LockDirectiveSigner::class)->publicKey());

    expect(sodium_crypto_sign_verify_detached($signature, $payload, $publicKey))->toBeTrue();

    $decoded = json_decode($payload, true);

    expect($decoded['site_id'])->toBe($website->bridge_site_id)
        ->and($decoded['lock_level'])->toBe(3)
        ->and($decoded['reason'])->toBe('unpaid invoice')
        ->and($decoded['directive_seq'])->toBe(1)
        ->and($decoded['issued_at'])->not->toBeNull();
});

it('advances the directive sequence on every heartbeat', function () {
    $website = Website::factory()->create();
    $key = enrolledKey($website);

    $seqs = collect(range(1, 3))->map(function () use ($key): int {
        $directive = $this->withToken($key)
            ->postJson('api/site-bridge/heartbeat', heartbeatBody())
            ->json('directive');

        return json_decode(base64_decode($directive['payload']), true)['directive_seq'];
    });

    // Strictly increasing, so a captured older directive is always rejected.
    expect($seqs->all())->toBe([1, 2, 3])
        ->and($website->refresh()->directive_seq)->toBe(3);
});

it('rejects a tampered directive payload', function () {
    $website = Website::factory()->create();
    $key = enrolledKey($website);

    $directive = $this->withToken($key)
        ->postJson('api/site-bridge/heartbeat', heartbeatBody())
        ->json('directive');

    $forged = str_replace('"lock_level":0', '"lock_level":3', base64_decode($directive['payload']));
    $signature = base64_decode($directive['signature']);
    $publicKey = base64_decode(app(LockDirectiveSigner::class)->publicKey());

    expect(sodium_crypto_sign_verify_detached($signature, $forged, $publicKey))->toBeFalse();
});

it('flags a domain that differs from the website record but never rejects it', function () {
    $website = Website::factory()->create(['url' => 'https://client.example']);
    $key = enrolledKey($website);

    // Matches the domain we hold for the site — no flag.
    $this->withToken($key)
        ->postJson('api/site-bridge/heartbeat', heartbeatBody(['domain' => 'client.example']))
        ->assertOk();

    $keyModel = $website->bridgeKeys()->firstOrFail();

    expect($keyModel->first_seen_domain)->toBe('client.example')
        ->and($keyModel->hasDomainMismatch())->toBeFalse();

    // Differs from the record — flagged, but the check-in still succeeds.
    $this->withToken($key)
        ->postJson('api/site-bridge/heartbeat', heartbeatBody(['domain' => 'moved.example']))
        ->assertOk();

    $keyModel->refresh();

    expect($keyModel->last_seen_domain)->toBe('moved.example')
        ->and($keyModel->hasDomainMismatch())->toBeTrue();
});

it('updates the reported lock level and syncs the client', function () {
    $website = Website::factory()->create();
    $key = enrolledKey($website);

    $this->withToken($key)
        ->postJson('api/site-bridge/heartbeat', heartbeatBody(['lock_level' => 3]))
        ->assertOk();

    expect($website->refresh()->lock_level)->toBe(3)
        ->and($website->client->refresh()->status)->toBe(ClientStatus::Locked);
});

it('falls back to the status lock level when the top-level field is omitted', function () {
    $website = Website::factory()->create();
    $key = enrolledKey($website);

    $body = heartbeatBody([
        'status' => bridgeStatusPayload([
            'lock' => ['lock_level' => 2, 'reason' => null, 'locked_at' => null, 'locked_by' => null],
        ]),
    ]);
    unset($body['lock_level']);

    $this->withToken($key)
        ->postJson('api/site-bridge/heartbeat', $body)
        ->assertOk();

    expect($website->refresh()->lock_level)->toBe(2);
});

it('rejects a heartbeat with no key', function () {
    $this->postJson('api/site-bridge/heartbeat', heartbeatBody())->assertUnauthorized();
});

it('rejects an unknown key', function () {
    $this->withToken('sb_live_'.str_repeat('x', 40))
        ->postJson('api/site-bridge/heartbeat', heartbeatBody())
        ->assertUnauthorized();
});

it('rejects a revoked key', function () {
    $website = Website::factory()->create();
    $key = enrolledKey($website);
    $website->bridgeKeys()->firstOrFail()->revoke();

    $this->withToken($key)
        ->postJson('api/site-bridge/heartbeat', heartbeatBody())
        ->assertUnauthorized();
});
