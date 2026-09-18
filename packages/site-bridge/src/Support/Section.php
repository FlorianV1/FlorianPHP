<?php

declare(strict_types=1);

namespace Agency\SiteBridge\Support;

use Throwable;

/**
 * A metrics sub-section that degrades gracefully: either a value, or
 * null plus the reason it is unavailable. Never throws into a response.
 */
final readonly class Section
{
    private function __construct(
        public mixed $value,
        public ?string $reason,
    ) {}

    public static function ok(mixed $value): self
    {
        return new self($value, null);
    }

    public static function unavailable(string $reason): self
    {
        return new self(null, $reason);
    }

    /**
     * @param  array{value: mixed, reason: string|null}  $data
     */
    public static function fromArray(array $data): self
    {
        return new self($data['value'] ?? null, $data['reason'] ?? null);
    }

    /**
     * Run the resolver, converting any exception into an unavailable section.
     */
    public static function attempt(callable $resolver): self
    {
        try {
            $result = $resolver();

            return $result instanceof self ? $result : self::ok($result);
        } catch (Throwable $exception) {
            return self::unavailable($exception->getMessage());
        }
    }

    /**
     * @return array{value: mixed, reason: string|null}
     */
    public function toArray(): array
    {
        return [
            'value' => $this->value,
            'reason' => $this->reason,
        ];
    }
}
