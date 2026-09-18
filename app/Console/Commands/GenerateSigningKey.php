<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\SiteBridge\LockDirectiveSigner;
use Illuminate\Console\Command;

/**
 * Generates the hub's Ed25519 keypair for signing lock directives. Run once,
 * pointing `site-bridge.signing.keypair_path` at a persistent location
 * outside the release directory, then back the file up.
 */
final class GenerateSigningKey extends Command
{
    protected $signature = 'site-bridge:generate-key {--force : Overwrite an existing keypair — invalidates every enrolled site}';

    protected $description = 'Generate the hub Ed25519 keypair used to sign lock directives';

    public function handle(LockDirectiveSigner $signer): int
    {
        if ($signer->exists() && ! $this->option('force')) {
            $this->error('A signing keypair already exists. Refusing to overwrite — use --force only if you intend to re-enrol every site.');

            return self::FAILURE;
        }

        $signer->generate(force: (bool) $this->option('force'));

        $this->info('Signing keypair generated.');
        $this->newLine();
        $this->line('Hub public key (base64):');
        $this->line($signer->publicKey());
        $this->newLine();
        $this->warn('Keep this file on a PERSISTENT path outside the release directory and back it up. Losing it means re-enrolling every client by hand.');

        return self::SUCCESS;
    }
}
