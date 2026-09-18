<?php

declare(strict_types=1);

namespace Agency\SiteBridge\Commands;

use Agency\SiteBridge\Collectors\MetricsCollector;
use Agency\SiteBridge\Collectors\StatusCollector;
use Agency\SiteBridge\Collectors\UpdatesCollector;
use Agency\SiteBridge\Support\Credential;
use Agency\SiteBridge\Support\DirectiveVerifier;
use Agency\SiteBridge\Support\LockState;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Checks in to the hub with this site's status/metrics and applies the signed
 * lock directive from the response. Fail-open: if the hub is unreachable the
 * site keeps working at its current lock and simply logs it — a hub outage
 * can never lock a site, nor silently unlock one.
 *
 * Runs every minute from the scheduler but only sends when due: every ~5 min
 * while unlocked, every ~60s while locked, so a lift is picked up quickly.
 */
final class HeartbeatCommand extends Command
{
    private const SENT_AT_CACHE_KEY = 'site-bridge:heartbeat-sent-at';

    protected $signature = 'site-bridge:heartbeat {--force : Send even if not yet due}';

    protected $description = 'Send an outbound heartbeat to the hub and apply the returned lock directive';

    public function handle(
        Credential $credential,
        LockState $lockState,
        DirectiveVerifier $verifier,
        StatusCollector $status,
        MetricsCollector $metrics,
        UpdatesCollector $updates,
    ): int {
        $active = $credential->active();

        if ($active === null) {
            $this->info('site-bridge: not enrolled — run site-bridge:claim first.');

            return self::SUCCESS;
        }

        $level = $lockState->level();

        if (! $this->option('force') && ! $this->isDue($level)) {
            return self::SUCCESS;
        }

        Cache::put(self::SENT_AT_CACHE_KEY, now()->toIso8601String());

        $body = [
            'status' => $status->collect(),
            'metrics' => (bool) config('site-bridge.expose.metrics', true) ? $metrics->collect() : null,
            'updates' => (bool) config('site-bridge.expose.updates', true) ? $updates->collect() : null,
            'lock_level' => $level,
            'domain' => $this->domain(),
        ];

        try {
            $response = Http::withToken($active->key)
                ->acceptJson()
                ->connectTimeout(5)
                ->timeout(10)
                ->post($active->hub_url.'/api/site-bridge/heartbeat', $body);
        } catch (Throwable $exception) {
            // Fail-open: keep the current lock, never self-lock on unreachability.
            Log::warning('site-bridge: heartbeat could not reach the hub; keeping current lock.', [
                'error' => $exception->getMessage(),
            ]);

            return self::SUCCESS;
        }

        if ($response->failed()) {
            Log::warning('site-bridge: heartbeat rejected by hub; keeping current lock.', [
                'status' => $response->status(),
            ]);

            return self::SUCCESS;
        }

        $this->applyDirective($response->json('directive'), $active, $lockState, $verifier);

        return self::SUCCESS;
    }

    /**
     * @param  object{site_id: string, hub_public_key: string}  $active
     */
    private function applyDirective(mixed $directive, object $active, LockState $lockState, DirectiveVerifier $verifier): void
    {
        if (! is_array($directive)) {
            return;
        }

        $result = $verifier->verify($directive, $active->hub_public_key, $active->site_id, $lockState->directiveSeq());

        if (! $result->accepted) {
            // Rejected directives never change the lock — the last verified
            // directive stands (retain-last), so this is safe.
            Log::warning("site-bridge: rejected lock directive ({$result->reason}); keeping current lock.");

            return;
        }

        $lockState->applyDirective(
            (int) $result->directive['lock_level'],
            $result->directive['reason'] ?? null,
            (int) $result->directive['directive_seq'],
        );
    }

    private function isDue(int $level): bool
    {
        $lastSent = Cache::get(self::SENT_AT_CACHE_KEY);

        if (! is_string($lastSent)) {
            return true;
        }

        $interval = $level > 0
            ? (int) config('site-bridge.heartbeat.interval_locked_seconds', 60)
            : (int) config('site-bridge.heartbeat.interval_unlocked_seconds', 300);

        try {
            return Carbon::parse($lastSent)->addSeconds($interval)->isPast();
        } catch (Throwable) {
            return true;
        }
    }

    private function domain(): ?string
    {
        $host = parse_url((string) config('app.url'), PHP_URL_HOST);

        return is_string($host) ? mb_strtolower($host) : null;
    }
}
