<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Client;
use App\Models\TimeEntry;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TimeEntry>
 */
final class TimeEntryFactory extends Factory
{
    protected $model = TimeEntry::class;

    public function definition(): array
    {
        return [
            'client_id' => Client::factory(),
            'website_id' => null,
            'invoice_line_id' => null,
            'work_date' => fake()->dateTimeBetween('-1 month', 'now'),
            'hours' => fake()->randomElement([0.5, 1, 1.5, 2, 3, 4]),
            'description' => fake()->randomElement([
                'Bug fixes',
                'Feature development',
                'Content updates',
                'Performance tuning',
                'Support call',
                'Security updates',
            ]),
            'hourly_rate' => null,
        ];
    }
}
