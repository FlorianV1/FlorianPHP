<?php

declare(strict_types=1);

namespace Agency\SiteBridge\Commands;

use Agency\SiteBridge\Support\Credential;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Throwable;

/**
 * Redeems a one-time claim code from the hub for a long-lived key, storing it
 * (encrypted) in this site's own database. No .env or config editing needed.
 */
final class ClaimCommand extends Command
{
    protected $signature = 'site-bridge:claim {code : The one-time claim code from the dashboard} {--hub= : Override the hub URL}';

    protected $description = 'Exchange a claim code for a site-bridge key and enrol this site';

    public function handle(Credential $credential): int
    {
        $hubUrl = mb_rtrim((string) ($this->option('hub') ?? config('site-bridge.hub_url', '')), '/');

        if ($hubUrl === '') {
            $this->error('No hub URL configured. Set site-bridge.hub_url or pass --hub.');

            return self::FAILURE;
        }

        try {
            $response = Http::acceptJson()
                ->connectTimeout(5)
                ->timeout(10)
                ->post($hubUrl.'/api/site-bridge/claim', ['code' => (string) $this->argument('code')]);
        } catch (Throwable $exception) {
            $this->error("Could not reach the hub: {$exception->getMessage()}");

            return self::FAILURE;
        }

        if ($response->failed()) {
            $this->error("Claim rejected (HTTP {$response->status()}): ".(string) $response->json('message', 'unknown error'));

            return self::FAILURE;
        }

        $key = (string) $response->json('key');
        $siteId = (string) $response->json('site_id');
        $publicKey = (string) $response->json('hub_public_key');

        if ($key === '' || $siteId === '' || $publicKey === '') {
            $this->error('The hub response was missing the key, site id, or public key.');

            return self::FAILURE;
        }

        $credential->store($key, $hubUrl, $siteId, $publicKey);

        $this->info('Enrolled. This site will now heartbeat to the hub.');
        $this->line('Key prefix: '.mb_substr($key, 0, 16).'…');

        return self::SUCCESS;
    }
}
