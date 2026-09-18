<?php

declare(strict_types=1);

namespace App\SiteBridge;

use Illuminate\Support\Facades\File;
use RuntimeException;

/**
 * Signs lock directives with the hub's Ed25519 private key. The keypair is
 * generated once, explicitly, and stored on disk outside the database — so a
 * database dump can never yield the ability to forge a directive. Client
 * sites are handed the public half at claim time and pin it thereafter.
 *
 * It never auto-generates: a silently minted key would invalidate every
 * pinned public key on deploy. When the key is missing, signing fails loudly.
 */
final class LockDirectiveSigner
{
    /**
     * Base64 of the raw signed bytes plus its detached signature. The site
     * verifies the signature against the exact `payload` bytes, then decodes
     * them — so there is no canonicalisation to disagree about.
     *
     * @param  array<string, mixed>  $directive
     * @return array{payload: string, signature: string}
     */
    public function sign(array $directive): array
    {
        $bytes = json_encode($directive, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

        if ($bytes === false) {
            // e.g. a non-UTF-8 lock reason — sign nothing rather than the
            // literal string "false", which would ship a bogus directive.
            throw new RuntimeException('Unable to encode the lock directive for signing: '.json_last_error_msg());
        }

        $signature = sodium_crypto_sign_detached($bytes, sodium_crypto_sign_secretkey($this->keypair()));

        return [
            'payload' => base64_encode($bytes),
            'signature' => base64_encode($signature),
        ];
    }

    /**
     * The hub's Ed25519 public key, base64-encoded, for delivery at claim time.
     */
    public function publicKey(): string
    {
        return base64_encode(sodium_crypto_sign_publickey($this->keypair()));
    }

    public function exists(): bool
    {
        return File::exists($this->path());
    }

    /**
     * Generate and persist a new keypair. Refuses to overwrite an existing
     * one unless forced, because overwriting orphans every enrolled site.
     */
    public function generate(bool $force = false): void
    {
        $path = $this->path();

        if (! $force && File::exists($path)) {
            throw new RuntimeException("A site-bridge signing keypair already exists at {$path}. Refusing to overwrite (use --force only if you intend to re-enrol every site).");
        }

        File::ensureDirectoryExists(dirname($path));

        if (File::put($path, sodium_bin2hex(sodium_crypto_sign_keypair())) === false) {
            throw new RuntimeException("Unable to persist the site-bridge signing keypair to {$path}.");
        }

        @chmod($path, 0600);
    }

    private function keypair(): string
    {
        if (! $this->exists()) {
            throw new MissingSigningKeyException($this->path());
        }

        return sodium_hex2bin(mb_trim(File::get($this->path())));
    }

    private function path(): string
    {
        return (string) config('site-bridge.signing.keypair_path');
    }
}
