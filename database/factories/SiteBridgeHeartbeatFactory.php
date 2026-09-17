<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\SiteBridgeHeartbeat;
use App\Models\Website;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SiteBridgeHeartbeat>
 */
final class SiteBridgeHeartbeatFactory extends Factory
{
    protected $model = SiteBridgeHeartbeat::class;

    public function definition(): array
    {
        return [
            'website_id' => Website::factory(),
            'site_bridge_key_id' => null,
            'ip' => fake()->ipv4(),
            'domain' => fake()->domainName(),
            'reported_lock_level' => 0,
            'received_at' => now(),
        ];
    }
}
