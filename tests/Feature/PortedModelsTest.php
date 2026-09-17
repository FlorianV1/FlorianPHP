<?php

use App\Enums\ClientStatus;
use App\Enums\InvoiceStatus;
use App\Models\Client;
use App\Models\Contact;
use App\Models\Invoice;
use App\Models\InvoiceLine;
use App\Models\Retainer;
use App\Models\SiteBridgeClaimCode;
use App\Models\SiteBridgeHeartbeat;
use App\Models\SiteBridgeKey;
use App\Models\TimeEntry;
use App\Models\User;
use App\Models\Website;

/**
 * Proves the command center's domain models survived the move into this
 * codebase: they persist, their casts and enums resolve, and their
 * relationships wire up against the ported migrations.
 */
it('persists every ported model through its factory', function (string $model) {
    $record = $model::factory()->create();

    expect($record->exists)->toBeTrue()
        ->and($model::query()->count())->toBe(1);
})->with([
    'client' => [Client::class],
    'website' => [Website::class],
    'contact' => [Contact::class],
    'invoice' => [Invoice::class],
    'retainer' => [Retainer::class],
    'time entry' => [TimeEntry::class],
    'bridge key' => [SiteBridgeKey::class],
    'claim code' => [SiteBridgeClaimCode::class],
    'heartbeat' => [SiteBridgeHeartbeat::class],
]);

it('casts enums on the ported models', function () {
    $client = Client::factory()->create(['status' => ClientStatus::Active]);
    $invoice = Invoice::factory()->create(['status' => InvoiceStatus::Draft]);

    expect($client->status)->toBeInstanceOf(ClientStatus::class)
        ->and($invoice->status)->toBeInstanceOf(InvoiceStatus::class);
});

it('wires up the client relationships', function () {
    $client = Client::factory()->create();
    $website = Website::factory()->for($client)->create();

    expect($website->client->is($client))->toBeTrue()
        ->and($client->websites()->count())->toBe(1);
});

it('recomputes invoice totals from its lines', function () {
    $invoice = Invoice::factory()->create(['vat_rate' => 21]);

    InvoiceLine::factory()->for($invoice)->create([
        'quantity' => 2,
        'unit_price' => 100,
        'amount' => 200,
    ]);

    $invoice->recalculateTotals();

    expect((float) $invoice->subtotal)->toBe(200.0)
        ->and((float) $invoice->vat_amount)->toBe(42.0)
        ->and((float) $invoice->total)->toBe(242.0);
});

it('gives every website a bridge site id on create', function () {
    expect(Website::factory()->create()->bridge_site_id)->not->toBeNull();
});

it('derives website health status', function () {
    $website = Website::factory()->create([
        'health' => ['ok' => true, 'status' => ['up' => true]],
    ]);

    expect($website->healthStatus())->toBe('green');
});

it('gates the management panel on the admin flag', function () {
    $admin = User::factory()->create(['is_admin' => true]);
    $plain = User::factory()->create(['is_admin' => false]);

    $management = filament()->getPanel('management');
    $website = filament()->getPanel('website');

    expect($admin->canAccessPanel($management))->toBeTrue()
        ->and($plain->canAccessPanel($management))->toBeFalse()
        ->and($plain->canAccessPanel($website))->toBeTrue();
});
