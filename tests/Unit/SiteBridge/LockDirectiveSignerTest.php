<?php

declare(strict_types=1);

use App\SiteBridge\LockDirectiveSigner;
use App\SiteBridge\MissingSigningKeyException;

beforeEach(fn () => useTestSigningKey());

it('signs a directive that verifies and rejects tampering', function () {
    $signer = app(LockDirectiveSigner::class);

    $signed = $signer->sign([
        'site_id' => 'site-abc',
        'lock_level' => 2,
        'reason' => 'maintenance',
        'issued_at' => now()->toIso8601String(),
    ]);

    $payload = base64_decode($signed['payload']);
    $signature = base64_decode($signed['signature']);
    $publicKey = base64_decode($signer->publicKey());

    expect(sodium_crypto_sign_verify_detached($signature, $payload, $publicKey))->toBeTrue()
        ->and(sodium_crypto_sign_verify_detached($signature, $payload.'x', $publicKey))->toBeFalse();
});

it('reuses the persisted keypair across instances', function () {
    $first = app(LockDirectiveSigner::class)->publicKey();
    $second = (new LockDirectiveSigner)->publicKey();

    expect($first)->toBe($second);
});

it('fails loudly instead of minting a key when none exists', function () {
    config()->set('site-bridge.signing.keypair_path', storage_path('framework/testing/missing-'.uniqid().'.keypair'));

    expect(fn () => app(LockDirectiveSigner::class)->sign(['x' => 1]))
        ->toThrow(MissingSigningKeyException::class);
});

it('refuses to overwrite an existing keypair', function () {
    expect(fn () => app(LockDirectiveSigner::class)->generate())
        ->toThrow(RuntimeException::class, 'Refusing to overwrite');
});
