<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Client;
use App\Models\Contact;
use App\Models\Invoice;
use App\Models\InvoiceLine;
use App\Models\Retainer;
use App\Models\TimeEntry;
use App\Models\Website;
use Illuminate\Database\Seeder;

final class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        $bakery = Client::factory()->create([
            'company_name' => 'Bakkerij De Korenbloem',
            'contact_name' => 'Sanne Willems',
            'contact_email' => 'sanne@korenbloem.example',
            'hourly_rate' => 85,
        ]);

        $logistics = Client::factory()->create([
            'company_name' => 'Van Dijk Logistics BV',
            'contact_name' => 'Pieter van Dijk',
            'contact_email' => 'pieter@vandijk.example',
            'hourly_rate' => 110,
        ]);

        $studio = Client::factory()->prospect()->create([
            'company_name' => 'Studio Helder',
            'contact_name' => 'Femke de Boer',
            'contact_email' => 'femke@studiohelder.example',
            'hourly_rate' => 95,
        ]);

        Contact::factory()->for($bakery)->create(['role' => 'Finance']);
        Contact::factory()->for($logistics)->count(2)->create();

        $bakeryShop = Website::factory()->for($bakery)->create([
            'label' => 'Webshop',
            'url' => 'https://korenbloem.example',
        ]);
        Website::factory()->for($bakery)->staging()->create([
            'label' => 'Webshop (staging)',
            'url' => 'https://staging.korenbloem.example',
        ]);

        $portal = Website::factory()->for($logistics)->create([
            'label' => 'Customer portal',
            'url' => 'https://portal.vandijk.example',
        ]);
        Website::factory()->for($logistics)->create([
            'label' => 'Corporate site',
            'url' => 'https://vandijk.example',
        ]);

        Website::factory()->for($studio)->create([
            'label' => 'Portfolio site',
            'url' => 'https://studiohelder.example',
        ]);
        Website::factory()->for($studio)->staging()->create([
            'label' => 'Portfolio (staging)',
            'url' => 'https://staging.studiohelder.example',
        ]);

        // A spread of paid invoices over the past year for the revenue chart,
        // plus outstanding and draft ones for the money-owed widgets.
        foreach ([$bakery, $logistics] as $client) {
            Invoice::factory()
                ->count(5)
                ->for($client)
                ->create()
                ->each(fn (Invoice $invoice) => $this->addLines($invoice));
        }

        $this->addLines(Invoice::factory()->sent()->for($bakery)->for($bakeryShop)->create());
        $this->addLines(Invoice::factory()->overdue()->for($logistics)->for($portal)->create());
        $this->addLines(Invoice::factory()->draft()->for($logistics)->create());

        Retainer::factory()->for($bakery)->for($bakeryShop)->create([
            'description' => 'Webshop maintenance & hosting',
            'amount' => 150,
        ]);
        Retainer::factory()->for($logistics)->for($portal)->quarterly()->create([
            'description' => 'Portal SLA & monitoring',
        ]);
        Retainer::factory()->for($logistics)->yearly()->create([
            'description' => 'Domains, licenses & certificates',
            'active' => true,
        ]);

        TimeEntry::factory()->count(4)->for($bakery)->for($bakeryShop)->create();
        TimeEntry::factory()->count(3)->for($logistics)->for($portal)->create();
    }

    private function addLines(Invoice $invoice): void
    {
        InvoiceLine::factory()
            ->count(random_int(1, 3))
            ->for($invoice)
            ->create();

        $invoice->recalculateTotals();
    }
}
