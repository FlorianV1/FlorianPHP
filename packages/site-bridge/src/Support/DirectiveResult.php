<?php

declare(strict_types=1);

namespace Agency\SiteBridge\Support;

/**
 * The outcome of verifying a signed lock directive. When not accepted, the
 * reason is logged and the site keeps its current lock unchanged.
 */
final readonly class DirectiveResult
{
    /**
     * @param  array<string, mixed>|null  $directive
     */
    private function __construct(
        public bool $accepted,
        public ?array $directive,
        public string $reason,
    ) {}

    /**
     * @param  array<string, mixed>  $directive
     */
    public static function accept(array $directive): self
    {
        return new self(true, $directive, 'ok');
    }

    public static function reject(string $reason): self
    {
        return new self(false, null, $reason);
    }
}
