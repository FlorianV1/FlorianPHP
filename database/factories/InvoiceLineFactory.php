<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Invoice;
use App\Models\InvoiceLine;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<InvoiceLine>
 */
final class InvoiceLineFactory extends Factory
{
    protected $model = InvoiceLine::class;

    public function definition(): array
    {
        $quantity = fake()->randomElement([1, 2, 4, 6, 8, 10]);
        $unitPrice = fake()->randomElement([75, 85, 95, 110, 250]);

        return [
            'invoice_id' => Invoice::factory(),
            'description' => fake()->randomElement([
                'Maintenance & updates',
                'Feature development',
                'Hosting & monitoring',
                'Performance optimization',
                'Consulting call',
                'Bug fixes & support',
            ]),
            'quantity' => $quantity,
            'unit_price' => $unitPrice,
            'amount' => round($quantity * $unitPrice, 2),
        ];
    }
}
