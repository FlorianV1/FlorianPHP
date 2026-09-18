<?php

declare(strict_types=1);

namespace Agency\SiteBridge\Collectors;

use Agency\SiteBridge\Support\Section;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Process;

final readonly class UpdatesCollector
{
    /**
     * @return array<string, mixed>
     */
    public function collect(): array
    {
        return [
            'bridge_version' => 1,
            'packages' => $this->outdated(),
            'generated_at' => now()->toIso8601String(),
        ];
    }

    /**
     * Composer runs are expensive, so the result is cached. The command is
     * a fixed whitelist — no request input ever reaches the process.
     *
     * @return array{value: mixed, reason: string|null}
     */
    private function outdated(): array
    {
        $cacheSeconds = (int) config('site-bridge.updates_cache_seconds', 3600);

        return Cache::remember('site-bridge:updates', $cacheSeconds, function (): array {
            return Section::attempt(function (): Section {
                $result = Process::path(base_path())
                    ->timeout(90)
                    ->env(['COMPOSER_NO_INTERACTION' => '1'])
                    ->run(['composer', 'outdated', '--direct', '--format=json']);

                if ($result->failed()) {
                    return Section::unavailable('composer outdated failed: '.mb_trim($result->errorOutput()));
                }

                $decoded = json_decode($result->output(), true);

                if (! is_array($decoded) || ! array_key_exists('installed', $decoded)) {
                    return Section::unavailable('unexpected composer output');
                }

                return Section::ok(
                    collect($decoded['installed'])
                        ->map(fn (array $package): array => [
                            'name' => (string) ($package['name'] ?? ''),
                            'current' => (string) ($package['version'] ?? ''),
                            'latest' => (string) ($package['latest'] ?? ''),
                            'is_major' => $this->isMajorBump(
                                (string) ($package['version'] ?? ''),
                                (string) ($package['latest'] ?? ''),
                            ),
                        ])
                        ->values()
                        ->all(),
                );
            })->toArray();
        });
    }

    private function isMajorBump(string $current, string $latest): bool
    {
        $major = fn (string $version): string => explode('.', mb_ltrim($version, 'v'))[0];

        return $current !== '' && $latest !== '' && $major($current) !== $major($latest);
    }
}
