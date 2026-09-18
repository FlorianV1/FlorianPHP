<?php

declare(strict_types=1);

namespace Agency\SiteBridge\Services;

use Agency\SiteBridge\Support\Section;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

/**
 * Reports the site's Bugsnag open-error count using its own credentials,
 * so the dashboard never needs to hold them.
 */
final class BugsnagMetrics
{
    public function metrics(): Section
    {
        $token = (string) config('site-bridge.bugsnag.auth_token');
        $projectId = (string) config('site-bridge.bugsnag.project_id');

        if (blank($token) || blank($projectId)) {
            return Section::unavailable('bugsnag credentials not configured');
        }

        $cached = Cache::remember(
            'site-bridge:bugsnag',
            300,
            fn (): array => Section::attempt(fn (): Section => $this->fetch($token, $projectId))->toArray(),
        );

        return Section::fromArray($cached);
    }

    private function fetch(string $token, string $projectId): Section
    {
        $response = Http::withHeaders(['Authorization' => "token {$token}"])
            ->acceptJson()
            ->timeout(5)
            ->get(
                "https://api.bugsnag.com/projects/{$projectId}/errors"
                .'?filters[error.status][][type]=eq&filters[error.status][][value]=open&per_page=1',
            );

        if ($response->failed()) {
            return Section::unavailable("bugsnag api returned HTTP {$response->status()}");
        }

        $total = $response->header('X-Total-Count');

        return Section::ok([
            'open_errors' => $total !== '' ? (int) $total : count((array) $response->json()),
        ]);
    }
}
