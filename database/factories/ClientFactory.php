<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\ClientStatus;
use App\Models\Client;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Client>
 */
final class ClientFactory extends Factory
{
    protected $model = Client::class;

    public function definition(): array
    {
        return [
            'company_name' => fake()->company(),
            'contact_name' => fake()->name(),
            'contact_email' => fake()->unique()->companyEmail(),
            'contact_phone' => fake()->phoneNumber(),
            'vat_number' => 'NL'.fake()->numerify('#########B##'),
            'registration_number' => fake()->numerify('########'),
            'billing_address' => fake()->streetAddress()."\n".fake()->postcode().' '.fake()->city(),
            'notes' => '<p>'.fake()->paragraph().'</p>',
            'status' => ClientStatus::Active,
            'hourly_rate' => fake()->randomElement([75, 85, 95, 110]),
            'currency' => 'EUR',
            'onboarded_at' => fake()->dateTimeBetween('-3 years', '-2 months'),
        ];
    }

    public function prospect(): self
    {
        return $this->state([
            'status' => ClientStatus::Prospect,
            'onboarded_at' => null,
        ]);
    }

    public function archived(): self
    {
        return $this->state(['status' => ClientStatus::Archived]);
    }
}
