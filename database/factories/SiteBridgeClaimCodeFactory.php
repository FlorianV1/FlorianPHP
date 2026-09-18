<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\SiteBridgeClaimCode;
use App\Models\Website;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<SiteBridgeClaimCode>
 */
final class SiteBridgeClaimCodeFactory extends Factory
{
    protected $model = SiteBridgeClaimCode::class;

    public function definition(): array
    {
        return [
            'website_id' => Website::factory(),
            'code_hash' => hash('sha256', 'sbc_'.Str::random(32)),
            'expires_at' => now()->addMinutes(15),
            'claimed_at' => null,
        ];
    }

    public function expired(): self
    {
        return $this->state(['expires_at' => now()->subMinute()]);
    }

    public function claimed(): self
    {
        return $this->state(['claimed_at' => now()]);
    }
}
