<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\RetainerInterval;
use App\Models\Client;
use App\Models\Retainer;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Retainer>
 */
final class RetainerFactory extends Factory
{
    protected $model = Retainer::class;

    public function definition(): array
    {
        return [
            'client_id' => Client::factory(),
            'website_id' => null,
            'description' => fake()->randomElement([
                'Maintenance & hosting retainer',
                'SLA support contract',
                'Monitoring & backups',
                'Marketing site care plan',
            ]),
            'amount' => fake()->randomElement([95, 150, 250, 450]),
            'interval' => RetainerInterval::Monthly,
            'next_due_date' => now()->startOfMonth()->addDays(fake()->numberBetween(0, 27)),
            'active' => true,
            'auto_invoice' => false,
        ];
    }

    /**
     * Opted in to the unattended run, and due today so a test does not have
     * to travel in time to trigger it.
     */
    public function automatic(): self
    {
        return $this->state([
            'auto_invoice' => true,
            'next_due_date' => today(),
        ]);
    }

    public function quarterly(): self
    {
        return $this->state([
            'interval' => RetainerInterval::Quarterly,
            'amount' => fake()->randomElement([450, 750]),
        ]);
    }

    public function yearly(): self
    {
        return $this->state([
            'interval' => RetainerInterval::Yearly,
            'amount' => fake()->randomElement([950, 1800]),
        ]);
    }
}
