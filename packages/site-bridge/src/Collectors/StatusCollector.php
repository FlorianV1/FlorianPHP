<?php

declare(strict_types=1);

namespace Agency\SiteBridge\Collectors;

use Agency\SiteBridge\Support\LockState;
use Agency\SiteBridge\Support\Section;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\File;

final readonly class StatusCollector
{
    public function __construct(
        private LockState $lockState,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function collect(): array
    {
        return [
            'bridge_version' => 1,
            'app_name' => (string) config('app.name'),
            'environment' => (string) app()->environment(),
            'laravel_version' => Application::VERSION,
            'php_version' => PHP_VERSION,
            'up' => true,
            'maintenance_mode' => app()->isDownForMaintenance(),
            'lock' => $this->lockState->describe(),
            'git_sha' => Section::attempt(fn (): Section => $this->gitSha())->toArray(),
            'ssl_expires_at' => Section::attempt(fn (): Section => $this->sslExpiry())->toArray(),
            'queue_connection' => (string) config('queue.default'),
            'cache_driver' => (string) config('cache.default'),
            'server_time' => now()->toIso8601String(),
        ];
    }

    private function gitSha(): Section
    {
        $head = base_path('.git/HEAD');

        if (! File::exists($head)) {
            return Section::unavailable('no .git directory');
        }

        $contents = mb_trim(File::get($head));

        if (str_starts_with($contents, 'ref: ')) {
            $refPath = base_path('.git/'.mb_substr($contents, 5));

            if (File::exists($refPath)) {
                return Section::ok(mb_substr(mb_trim(File::get($refPath)), 0, 12));
            }

            return Section::unavailable('ref not readable');
        }

        return Section::ok(mb_substr($contents, 0, 12));
    }

    private function sslExpiry(): Section
    {
        $url = (string) config('app.url');

        if (! str_starts_with($url, 'https://')) {
            return Section::unavailable('app.url is not https');
        }

        $host = (string) parse_url($url, PHP_URL_HOST);

        $context = stream_context_create([
            'ssl' => ['capture_peer_cert' => true, 'verify_peer' => false, 'verify_peer_name' => false],
        ]);

        $socket = @stream_socket_client(
            "ssl://{$host}:443",
            $errorCode,
            $errorMessage,
            timeout: 3,
            context: $context,
        );

        if ($socket === false) {
            return Section::unavailable("tls connect failed: {$errorMessage}");
        }

        $params = stream_context_get_params($socket);
        fclose($socket);

        $certificate = $params['options']['ssl']['peer_certificate'] ?? null;

        if ($certificate === null) {
            return Section::unavailable('no peer certificate');
        }

        $parsed = openssl_x509_parse($certificate);

        if (! is_array($parsed) || ! isset($parsed['validTo_time_t'])) {
            return Section::unavailable('certificate not parseable');
        }

        return Section::ok(date('c', (int) $parsed['validTo_time_t']));
    }
}
