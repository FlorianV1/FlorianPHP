<?php

declare(strict_types=1);

namespace App\SiteBridge;

use App\Models\Website;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Pushes site health alerts to an ntfy topic (https://ntfy.sh — hosted or
 * self-hosted), so a red site reaches the operator's phone instead of
 * waiting for someone to open the dashboard. Disabled until a topic URL is
 * configured.
 */
final class HealthAlerts
{
    public function isConfigured(): bool
    {
        return $this->topicUrl() !== null;
    }

    public function siteDown(Website $website, string $reason): bool
    {
        return $this->push(
            title: "Site down: {$website->label}",
            message: "{$website->url} {$reason}. Client: {$website->client->company_name}.",
            priority: 'urgent',
            tags: 'rotating_light',
        );
    }

    public function siteRecovered(Website $website): bool
    {
        return $this->push(
            title: "Site recovered: {$website->label}",
            message: "{$website->url} is healthy again.",
            priority: 'default',
            tags: 'white_check_mark',
        );
    }

    private function push(string $title, string $message, string $priority, string $tags): bool
    {
        $url = $this->topicUrl();

        if ($url === null) {
            return false;
        }

        try {
            $response = Http::timeout(5)
                ->withHeaders([
                    'Title' => $title,
                    'Priority' => $priority,
                    'Tags' => $tags,
                ])
                ->withBody($message, 'text/plain')
                ->post($url);
        } catch (ConnectionException $exception) {
            Log::warning('Health alert could not be delivered.', [
                'title' => $title,
                'error' => $exception->getMessage(),
            ]);

            return false;
        }

        if (! $response->successful()) {
            Log::warning('Health alert was rejected by the ntfy endpoint.', [
                'title' => $title,
                'status' => $response->status(),
            ]);

            return false;
        }

        return true;
    }

    private function topicUrl(): ?string
    {
        $url = config('site-bridge.alerts.ntfy_url');

        return is_string($url) && $url !== '' ? $url : null;
    }
}
