<?php

declare(strict_types=1);

namespace App\Bugsnag;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;

/**
 * Thin client over the Bugsnag Data Access API (api.bugsnag.com),
 * authenticated with the agency's personal auth token. Only the handful of
 * reads the dashboard needs are implemented.
 */
final class BugsnagApi
{
    public function isConfigured(): bool
    {
        return $this->token() !== null;
    }

    /**
     * The organization whose projects we link against — the configured one,
     * or the first the token can see, which is what a single-org agency
     * account resolves to.
     *
     * @throws BugsnagApiException
     */
    public function organizationId(): string
    {
        $configured = config('bugsnag-hub.organization_id');

        if (is_string($configured) && $configured !== '') {
            return $configured;
        }

        $organizations = (array) $this->get('/user/organizations')->json();

        $first = $organizations[0]['id'] ?? null;

        if (! is_string($first) || $first === '') {
            return throw new BugsnagApiException('The auth token can not see any Bugsnag organization.');
        }

        return $first;
    }

    /**
     * Every project in the organization, following the API's Link-header
     * pagination.
     *
     * @return list<BugsnagProject>
     *
     * @throws BugsnagApiException
     */
    public function projects(string $organizationId): array
    {
        $projects = [];
        $url = "/organizations/{$organizationId}/projects?per_page=100";
        $maxPages = max(1, (int) config('bugsnag-hub.max_project_pages', 20));

        for ($page = 0; $page < $maxPages && $url !== null; $page++) {
            $response = $this->get($url);

            foreach ((array) $response->json() as $payload) {
                if (is_array($payload)) {
                    $projects[] = BugsnagProject::fromArray($payload);
                }
            }

            $url = $this->nextPageUrl($response);
        }

        return $projects;
    }

    /**
     * How many errors are currently open on a project. The API reports the
     * total in a header, so a single-row page is enough to read the count.
     *
     * @throws BugsnagApiException
     */
    public function openErrorCount(string $projectId): int
    {
        $response = $this->get(
            "/projects/{$projectId}/errors"
            .'?filters[error.status][][type]=eq&filters[error.status][][value]=open&per_page=1',
        );

        $total = $response->header('X-Total-Count');

        return $total !== '' ? (int) $total : count((array) $response->json());
    }

    /**
     * @throws BugsnagApiException
     */
    private function get(string $url): Response
    {
        $token = $this->token();

        if ($token === null) {
            throw new BugsnagApiException('No Bugsnag auth token configured — set BUGSNAG_AUTH_TOKEN.');
        }

        try {
            $response = Http::withHeaders([
                'Authorization' => "token {$token}",
                'X-Version' => '2',
            ])
                ->acceptJson()
                ->timeout((int) config('bugsnag-hub.timeout_seconds', 10))
                ->get($this->absolute($url));
        } catch (ConnectionException $exception) {
            throw new BugsnagApiException("Bugsnag is unreachable: {$exception->getMessage()}");
        }

        if ($response->failed()) {
            throw new BugsnagApiException($this->failureReason($response));
        }

        return $response;
    }

    private function failureReason(Response $response): string
    {
        return match ($response->status()) {
            401, 403 => 'Bugsnag rejected the auth token (HTTP '.$response->status().').',
            404 => 'Bugsnag has no such project or organization (HTTP 404).',
            429 => 'Bugsnag rate-limited the request (HTTP 429).',
            default => "Bugsnag returned HTTP {$response->status()}.",
        };
    }

    /**
     * Bugsnag paginates with `Link: <url>; rel="next"`.
     */
    private function nextPageUrl(Response $response): ?string
    {
        $link = $response->header('Link');

        if ($link === '' || ! preg_match('/<([^>]+)>\s*;\s*rel="next"/', $link, $matches)) {
            return null;
        }

        return $matches[1];
    }

    private function absolute(string $url): string
    {
        if (str_starts_with($url, 'http://') || str_starts_with($url, 'https://')) {
            return $url;
        }

        return mb_rtrim((string) config('bugsnag-hub.api_url', 'https://api.bugsnag.com'), '/').$url;
    }

    private function token(): ?string
    {
        $token = config('bugsnag-hub.auth_token');

        return is_string($token) && $token !== '' ? $token : null;
    }
}
