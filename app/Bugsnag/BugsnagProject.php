<?php

declare(strict_types=1);

namespace App\Bugsnag;

/**
 * One project as the Data Access API describes it. `apiKey` is the
 * project's notifier key — the value stored per website — and is what
 * links a website to its project.
 */
final readonly class BugsnagProject
{
    public function __construct(
        public string $id,
        public string $name,
        public ?string $apiKey,
        public ?string $url,
    ) {}

    /**
     * @param  array<string, mixed>  $payload
     */
    public static function fromArray(array $payload): self
    {
        return new self(
            id: (string) ($payload['id'] ?? ''),
            name: (string) ($payload['name'] ?? ''),
            apiKey: isset($payload['api_key']) ? (string) $payload['api_key'] : null,
            url: isset($payload['html_url']) ? (string) $payload['html_url'] : null,
        );
    }
}
