<?php

declare(strict_types=1);

use App\SiteBridge\LockDirectiveSigner;

it('generates a signing key and reports the public key', function () {
    config()->set('site-bridge.signing.keypair_path', storage_path('framework/testing/gen-'.uniqid().'.keypair'));

    $this->artisan('site-bridge:generate-key')
        ->expectsOutputToContain('Signing keypair generated.')
        ->assertSuccessful();

    expect(app(LockDirectiveSigner::class)->exists())->toBeTrue();
});

it('refuses to overwrite an existing key without --force', function () {
    config()->set('site-bridge.signing.keypair_path', storage_path('framework/testing/gen-'.uniqid().'.keypair'));
    app(LockDirectiveSigner::class)->generate();

    $this->artisan('site-bridge:generate-key')->assertFailed();
});

it('overwrites an existing key with --force', function () {
    config()->set('site-bridge.signing.keypair_path', storage_path('framework/testing/gen-'.uniqid().'.keypair'));
    $signer = app(LockDirectiveSigner::class);
    $signer->generate();
    $before = $signer->publicKey();

    $this->artisan('site-bridge:generate-key', ['--force' => true])->assertSuccessful();

    expect((new LockDirectiveSigner)->publicKey())->not->toBe($before);
});
