<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\InvoiceStatus;
use App\Models\Client;
use App\Models\Invoice;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Invoice>
 */
final class InvoiceFactory extends Factory
{
    protected $model = Invoice::class;

    public function definition(): array
    {
        $issueDate = CarbonImmutable::instance(fake()->dateTimeBetween('-12 months', 'now'));

        return [
            'client_id' => Client::factory(),
            'website_id' => null,
            'number' => 'INV-'.fake()->unique()->numerify('####-###'),
            'issue_date' => $issueDate,
            'due_date' => $issueDate->addDays(14),
            'status' => InvoiceStatus::Paid,
            'vat_rate' => 21,
            'paid_at' => $issueDate->addDays(fake()->numberBetween(3, 20)),
            'external_reference' => null,
            'notes' => null,
        ];
    }

    public function draft(): self
    {
        return $this->state([
            'status' => InvoiceStatus::Draft,
            'paid_at' => null,
        ]);
    }

    public function sent(): self
    {
        return $this->state(fn (): array => [
            'status' => InvoiceStatus::Sent,
            'issue_date' => $issueDate = CarbonImmutable::instance(fake()->dateTimeBetween('-10 days', 'now')),
            'due_date' => $issueDate->addDays(14),
            'paid_at' => null,
        ]);
    }

    public function overdue(): self
    {
        return $this->state(fn (): array => [
            'status' => InvoiceStatus::Overdue,
            'issue_date' => $issueDate = CarbonImmutable::instance(fake()->dateTimeBetween('-3 months', '-1 month')),
            'due_date' => $issueDate->addDays(14),
            'paid_at' => null,
        ]);
    }
}
