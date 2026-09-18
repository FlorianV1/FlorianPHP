<?php

declare(strict_types=1);

use Agency\SiteBridge\Tests\TestCase;

uses(TestCase::class)->in('Feature');

/**
 * A throwaway Ed25519 keypair standing in for the hub.
 *
 * @return array{0: string, 1: string} [secret key (raw), public key (base64)]
 */
function hubKeypair(): array
{
    $keypair = sodium_crypto_sign_keypair();

    return [
        sodium_crypto_sign_secretkey($keypair),
        base64_encode(sodium_crypto_sign_publickey($keypair)),
    ];
}

/**
 * Sign a directive exactly as the hub does, returning the {payload, signature}
 * envelope the site receives.
 *
 * @param  array<string, mixed>  $directive
 * @return array{payload: string, signature: string}
 */
function signDirective(array $directive, string $secretKey): array
{
    $bytes = (string) json_encode($directive, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

    return [
        'payload' => base64_encode($bytes),
        'signature' => base64_encode(sodium_crypto_sign_detached($bytes, $secretKey)),
    ];
}
