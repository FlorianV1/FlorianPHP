<?php

declare(strict_types=1);

use App\Billing\InvoiceFromWork;
use App\Enums\InvoiceStatus;
use App\Filament\Management\Resources\Invoices\Pages\ListInvoices;
use App\Filament\Management\Resources\TimeEntries\Pages\CreateTimeEntry;
use App\Filament\Management\Resources\TimeEntries\Pages\ListTimeEntries;
use App\Filament\Management\Widgets\UninvoicedWorkWidget;
use App\Models\Client;
use App\Models\Invoice;
use App\Models\InvoiceLine;
use App\Models\TimeEntry;
use Filament\Actions\Testing\TestAction;

use function Pest\Livewire\livewire;

it('renders the time entry pages', function (string $page) {
    TimeEntry::factory()->create();

    livewire($page)->assertOk();
})->with([
    [ListTimeEntries::class],
    [CreateTimeEntry::class],
]);

it('downloads an invoice PDF', function () {
    $invoice = Invoice::factory()->create();
    InvoiceLine::factory()->for($invoice)->create();
    $invoice->recalculateTotals();

    livewire(ListInvoices::class)
        ->loadTable()
        ->callAction(TestAction::make('downloadPdf')->table($invoice))
        ->assertFileDownloaded("{$invoice->number}.pdf");
});

it('bundles un-invoiced work into a draft invoice', function () {
    $client = Client::factory()->create(['hourly_rate' => 100]);
    TimeEntry::factory()->count(2)->for($client)->create(['hours' => 2]);
    TimeEntry::factory()->for($client)->create(['hours' => 1, 'hourly_rate' => 50]);

    $invoice = app(InvoiceFromWork::class)->create($client);

    expect($invoice)->not->toBeNull()
        ->and($invoice->status)->toBe(InvoiceStatus::Draft)
        ->and($invoice->lines()->count())->toBe(3)
        // 2×200 at client rate + 1×50 at entry rate = 450
        ->and((float) $invoice->subtotal)->toBe(450.0)
        ->and($client->timeEntries()->uninvoiced()->count())->toBe(0);

    // A second run has nothing left to invoice.
    expect(app(InvoiceFromWork::class)->create($client))->toBeNull();
});

it('creates a draft from the un-invoiced work widget', function () {
    $client = Client::factory()->create(['hourly_rate' => 80]);
    TimeEntry::factory()->count(2)->for($client)->create(['hours' => 1]);

    livewire(UninvoicedWorkWidget::class)
        ->callAction(TestAction::make('invoiceWork')->table($client))
        ->assertNotified();

    expect(Invoice::query()->where('client_id', $client->id)->count())->toBe(1)
        ->and($client->timeEntries()->uninvoiced()->count())->toBe(0);
});

it('derives the line amount server-side from quantity and unit price', function () {
    // A submitted amount is never trusted — the saving hook recomputes it.
    $line = InvoiceLine::factory()->create([
        'amount' => 999,
        'quantity' => 3,
        'unit_price' => 10,
    ]);

    expect((float) $line->refresh()->amount)->toBe(30.0);
});

it('marks sent invoices past due as overdue', function () {
    $overdue = Invoice::factory()->sent()->create([
        'issue_date' => today()->subDays(30),
        'due_date' => today()->subDays(5),
    ]);
    $current = Invoice::factory()->sent()->create([
        'issue_date' => today(),
        'due_date' => today()->addDays(14),
    ]);
    $paid = Invoice::factory()->create([
        'due_date' => today()->subDays(5),
    ]);

    $this->artisan('invoices:mark-overdue')
        ->expectsOutputToContain('1 invoice(s)')
        ->assertSuccessful();

    expect($overdue->refresh()->status)->toBe(InvoiceStatus::Overdue)
        ->and($current->refresh()->status)->toBe(InvoiceStatus::Sent)
        ->and($paid->refresh()->status)->toBe(InvoiceStatus::Paid);
});
