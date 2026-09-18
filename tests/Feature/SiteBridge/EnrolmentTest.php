<?php

declare(strict_types=1);

use App\Filament\Management\Resources\Websites\Pages\ViewWebsite;
use App\Models\SiteBridgeClaimCode;
use App\Models\SiteBridgeKey;
use App\Models\Website;
use App\SiteBridge\SiteBridgeEnrolment;

use function Pest\Livewire\livewire;

beforeEach(fn () => useTestSigningKey());

it('issues a claim code stored only as a hash with a TTL', function () {
    $website = Website::factory()->create();

    $code = app(SiteBridgeEnrolment::class)->issueClaimCode($website);

    expect($code)->toStartWith('sbc_');

    $row = SiteBridgeClaimCode::query()->firstOrFail();

    expect($row->code_hash)->toBe(hash('sha256', $code))
        ->and($row->website_id)->toBe($website->id)
        ->and($row->expires_at->isFuture())->toBeTrue()
        ->and($row->claimed_at)->toBeNull()
        // Plaintext is never persisted.
        ->and(SiteBridgeClaimCode::query()->where('code_hash', $code)->exists())->toBeFalse();
});

it('exchanges a valid claim code for a key, site id and public key', function () {
    $website = Website::factory()->create();
    $code = app(SiteBridgeEnrolment::class)->issueClaimCode($website);

    $response = $this->postJson('api/site-bridge/claim', ['code' => $code])
        ->assertOk()
        ->assertJsonStructure([
            'key',
            'site_id',
            'hub_public_key',
            'heartbeat' => ['interval_unlocked_seconds', 'interval_locked_seconds'],
        ]);

    $key = (string) $response->json('key');

    expect($key)->toStartWith('sb_live_')
        ->and($response->json('site_id'))->toBe($website->bridge_site_id);

    $stored = SiteBridgeKey::query()->firstOrFail();

    expect($stored->key_hash)->toBe(hash('sha256', $key))
        ->and($stored->key_prefix)->toBe(mb_substr($key, 0, 16))
        // Only the hash is stored — the plaintext is unrecoverable.
        ->and(SiteBridgeKey::query()->where('key_hash', $key)->exists())->toBeFalse()
        // The code is consumed.
        ->and(SiteBridgeClaimCode::query()->firstOrFail()->claimed_at)->not->toBeNull()
        // The first successful claim flips the site onto the heartbeat path.
        ->and($website->refresh()->enrolled_at)->not->toBeNull();
});

it('rejects an expired claim code', function () {
    $website = Website::factory()->create();
    $code = 'sbc_'.str_repeat('a', 32);
    SiteBridgeClaimCode::factory()->for($website)->expired()->create(['code_hash' => hash('sha256', $code)]);

    $this->postJson('api/site-bridge/claim', ['code' => $code])->assertStatus(422);

    expect(SiteBridgeKey::query()->count())->toBe(0);
});

it('rejects an already-claimed code', function () {
    $website = Website::factory()->create();
    $code = 'sbc_'.str_repeat('b', 32);
    SiteBridgeClaimCode::factory()->for($website)->claimed()->create(['code_hash' => hash('sha256', $code)]);

    $this->postJson('api/site-bridge/claim', ['code' => $code])->assertStatus(422);
});

it('rejects an unknown code', function () {
    Website::factory()->create();

    $this->postJson('api/site-bridge/claim', ['code' => 'sbc_'.str_repeat('z', 32)])->assertStatus(422);
});

it('allows a second active key for rotation but refuses a third', function () {
    $website = Website::factory()->create();
    SiteBridgeKey::factory()->for($website)->create();

    // Second key: rotation window, allowed.
    $this->postJson('api/site-bridge/claim', [
        'code' => app(SiteBridgeEnrolment::class)->issueClaimCode($website),
    ])->assertOk();

    expect($website->bridgeKeys()->active()->count())->toBe(2);

    // Third key: over the ceiling, rejected.
    $this->postJson('api/site-bridge/claim', [
        'code' => app(SiteBridgeEnrolment::class)->issueClaimCode($website),
    ])->assertStatus(409);

    expect($website->bridgeKeys()->active()->count())->toBe(2);
});

it('frees a key slot on revocation so rotation can complete', function () {
    $website = Website::factory()->create();
    $keys = SiteBridgeKey::factory()->for($website)->count(2)->create();

    // Both slots taken — a new claim is refused.
    $this->postJson('api/site-bridge/claim', [
        'code' => app(SiteBridgeEnrolment::class)->issueClaimCode($website),
    ])->assertStatus(409);

    $keys->first()->revoke();

    // Revoking one frees the slot for the rotation claim.
    $this->postJson('api/site-bridge/claim', [
        'code' => app(SiteBridgeEnrolment::class)->issueClaimCode($website),
    ])->assertOk();

    expect($website->bridgeKeys()->active()->count())->toBe(2);
});

it('generates a claim code from the website page action', function () {
    $website = Website::factory()->create();

    livewire(ViewWebsite::class, ['record' => $website->id])
        ->callAction('generateClaimCode')
        ->assertNotified();

    expect($website->claimCodes()->count())->toBe(1);
});

it('revokes a key from the website page action', function () {
    $website = Website::factory()->create();
    $key = SiteBridgeKey::factory()->for($website)->create();

    livewire(ViewWebsite::class, ['record' => $website->id])
        ->callAction('revokeKey', ['key_id' => $key->id])
        ->assertNotified();

    expect($key->refresh()->isRevoked())->toBeTrue();
});
