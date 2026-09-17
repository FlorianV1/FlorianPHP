<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\SiteBridgeKey;
use App\Models\Website;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<SiteBridgeKey>
 */
final class SiteBridgeKeyFactory extends Factory
{
    protected $model = SiteBridgeKey::class;

    public function definition(): array
    {
        $plaintext = 'sb_live_'.Str::random(40);

        return [
            'website_id' => Website::factory(),
            'key_hash' => hash('sha256', $plaintext),
            'key_prefix' => mb_substr($plaintext, 0, 16),
            'first_seen_domain' => null,
            'last_seen_domain' => null,
            'domain_mismatch_at' => null,
            'last_used_at' => null,
            'revoked_at' => null,
        ];
    }

    public function revoked(): self
    {
        return $this->state(['revoked_at' => now()]);
    }
}
