<?php

declare(strict_types=1);

use App\Enums\InvoiceStatus;
use App\Filament\Management\Resources\Clients\Pages\CreateClient;
use App\Filament\Management\Resources\Clients\Pages\ListClients;
use App\Filament\Management\Resources\Clients\Pages\ManageClientActivities;
use App\Filament\Management\Resources\Clients\Pages\ManageClientContacts;
use App\Filament\Management\Resources\Clients\Pages\ManageClientInvoices;
use App\Filament\Management\Resources\Clients\Pages\ManageClientRetainers;
use App\Filament\Management\Resources\Clients\Pages\ManageClientWebsites;
use App\Filament\Management\Resources\Clients\Pages\ViewClient;
use App\Filament\Management\Resources\Invoices\Pages\CreateInvoice;
use App\Filament\Management\Resources\Invoices\Pages\ListInvoices;
use App\Filament\Management\Resources\Retainers\Pages\CreateRetainer;
use App\Filament\Management\Resources\Retainers\Pages\ListRetainers;
use App\Filament\Management\Resources\Websites\Pages\CreateWebsite;
use App\Filament\Management\Resources\Websites\Pages\ListWebsites;
use App\Filament\Management\Resources\Websites\Pages\ViewWebsite;
use App\Models\Client;
use App\Models\Contact;
use App\Models\Invoice;
use App\Models\Retainer;
use App\Models\Website;
use Filament\Actions\Testing\TestAction;

use function Pest\Livewire\livewire;

it('renders the list pages', function (string $page) {
    livewire($page)->assertOk();
})->with([
    ListClients::class,
    ListWebsites::class,
    ListInvoices::class,
    ListRetainers::class,
]);

it('renders the create pages', function (string $page) {
    livewire($page)->assertOk();
})->with([
    CreateClient::class,
    CreateWebsite::class,
    CreateInvoice::class,
    CreateRetainer::class,
]);

it('renders the client view page', function () {
    $client = Client::factory()
        ->has(Website::factory())
        ->has(Invoice::factory())
        ->has(Retainer::factory())
        ->create();

    livewire(ViewClient::class, ['record' => $client->id])
        ->assertOk();
});

it('renders the client sub-navigation pages', function (string $page) {
    $client = Client::factory()
        ->has(Website::factory())
        ->has(Invoice::factory())
        ->has(Retainer::factory())
        ->has(Contact::factory())
        ->create();

    livewire($page, ['record' => $client->id])
        ->assertOk();
})->with([
    [ManageClientWebsites::class],
    [ManageClientInvoices::class],
    [ManageClientRetainers::class],
    [ManageClientContacts::class],
    [ManageClientActivities::class],
]);

it('renders the website view page', function () {
    $website = Website::factory()->create();

    livewire(ViewWebsite::class, ['record' => $website->id])
        ->assertOk();
});

it('creates an invoice with lines and computes totals', function () {
    $client = Client::factory()->create();

    livewire(CreateInvoice::class)
        ->fillForm([
            'client_id' => $client->id,
            'issue_date' => today()->toDateString(),
            'due_date' => today()->addDays(14)->toDateString(),
            'vat_rate' => 21,
            'lines' => [
                [
                    'description' => 'Development work',
                    'quantity' => 2,
                    'unit_price' => 100,
                    'amount' => 200,
                ],
            ],
        ])
        ->call('create')
        ->assertNotified()
        ->assertHasNoFormErrors();

    $invoice = Invoice::query()->latest('id')->first();

    expect($invoice)->not->toBeNull()
        ->and((float) $invoice->subtotal)->toBe(200.0)
        ->and((float) $invoice->vat_amount)->toBe(42.0)
        ->and((float) $invoice->total)->toBe(242.0);
});

it('transitions an invoice from draft to sent to paid', function () {
    $invoice = Invoice::factory()->draft()->create();

    livewire(ListInvoices::class)
        ->loadTable()
        ->callAction(TestAction::make('markSent')->table($invoice));

    expect($invoice->refresh()->status)->toBe(InvoiceStatus::Sent);

    livewire(ListInvoices::class)
        ->loadTable()
        ->callAction(TestAction::make('markPaid')->table($invoice));

    expect($invoice->refresh()->status)->toBe(InvoiceStatus::Paid)
        ->and($invoice->paid_at)->not->toBeNull();
});

it('duplicates an invoice as a new draft', function () {
    $invoice = Invoice::factory()->create();
    $invoice->lines()->create([
        'description' => 'Line',
        'quantity' => 1,
        'unit_price' => 100,
        'amount' => 100,
    ]);
    $invoice->recalculateTotals();

    livewire(ListInvoices::class)
        ->loadTable()
        ->callAction(TestAction::make('duplicate')->table($invoice));

    $copy = Invoice::query()->latest('id')->first();

    expect($copy->id)->not->toBe($invoice->id)
        ->and($copy->status)->toBe(InvoiceStatus::Draft)
        ->and($copy->number)->not->toBe($invoice->number)
        ->and($copy->lines()->count())->toBe(1)
        ->and((float) $copy->total)->toBe(121.0);
});
