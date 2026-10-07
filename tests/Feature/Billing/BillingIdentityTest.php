<?php

declare(strict_types=1);

use App\Enums\VatRegime;
use App\Filament\Management\Pages\BillingSettings;
use App\Models\Client;
use App\Models\Invoice;
use App\Models\InvoiceLine;
use App\Models\User;
use App\Support\BillingIdentity;

use function Pest\Livewire\livewire;

it('defaults to the regime that claims the least', function () {
    // Nothing saved yet: a fresh install must not assume a registration it
    // has never been told about.
    expect(BillingIdentity::regime())->toBe(VatRegime::NotRegistered)
        ->and(BillingIdentity::vatRate())->toBe(0.0)
        ->and(BillingIdentity::get('kvk_number'))->toBeNull()
        ->and(BillingIdentity::get('vat_number'))->toBeNull();
});

it('falls back to not-registered when the stored regime is unreadable', function () {
    storeBillingIdentity(['vat_regime' => 'something_else']);

    expect(BillingIdentity::regime())->toBe(VatRegime::NotRegistered);
});

it('opens a draft with no VAT when there is no VAT registration', function () {
    storeBillingIdentity();

    $invoice = Invoice::openDraftFor(Client::factory()->create());
    InvoiceLine::factory()->for($invoice)->create(['quantity' => 1, 'unit_price' => 500]);
    $invoice->refresh()->recalculateTotals();

    expect($invoice->vat_regime)->toBe(VatRegime::NotRegistered)
        ->and((float) $invoice->vat_rate)->toBe(0.0)
        ->and((float) $invoice->vat_amount)->toBe(0.0)
        // No VAT means the client owes the subtotal and nothing more.
        ->and((float) $invoice->total)->toBe(500.0)
        ->and($invoice->chargesVat())->toBeFalse()
        ->and($invoice->vat_note)->toContain('Geen btw in rekening gebracht');
});

it('charges VAT once the standard regime is configured', function () {
    storeVatRegisteredIdentity();

    $invoice = Invoice::openDraftFor(Client::factory()->create());
    InvoiceLine::factory()->for($invoice)->create(['quantity' => 1, 'unit_price' => 500]);
    $invoice->refresh()->recalculateTotals();

    expect((float) $invoice->vat_rate)->toBe(21.0)
        ->and((float) $invoice->total)->toBe(605.0)
        ->and($invoice->vat_note)->toBeNull();
});

it('prints an invoice with no KvK number and no VAT row', function () {
    storeBillingIdentity();

    $invoice = Invoice::openDraftFor(Client::factory()->create(['company_name' => 'Acme BV']));
    InvoiceLine::factory()->for($invoice)->create(['quantity' => 1, 'unit_price' => 500]);
    $invoice->refresh()->recalculateTotals();

    $html = renderInvoiceHtml($invoice);

    expect($html)
        ->toContain('NOTA')
        ->toContain('Florian Geense')
        ->toContain('Teststraat 1')
        ->toContain('NL91ABNA0417164300')
        ->toContain('Geen btw in rekening gebracht')
        // The labels must be absent, not blank: there is no number to state.
        ->not->toContain('KvK')
        ->and($html)->not->toContain('Btw-id Florian')
        ->and($html)->not->toContain('Btw 0%');
});

it('prints the identifiers once they exist', function () {
    storeBillingIdentity([
        'vat_regime' => VatRegime::Standard->value,
        'kvk_number' => '87654321',
        'vat_number' => 'NL123456789B01',
    ]);

    $invoice = Invoice::openDraftFor(Client::factory()->create());
    InvoiceLine::factory()->for($invoice)->create(['quantity' => 1, 'unit_price' => 100]);
    $invoice->refresh()->recalculateTotals();

    expect(renderInvoiceHtml($invoice))
        ->toContain('FACTUUR')
        ->toContain('KvK 87654321')
        ->toContain('NL123456789B01')
        ->toContain('Btw 21%');
});

it('keeps an already sent invoice printing the way it was sent', function () {
    storeBillingIdentity();

    $invoice = Invoice::openDraftFor(Client::factory()->create());
    InvoiceLine::factory()->for($invoice)->create(['quantity' => 1, 'unit_price' => 500]);
    $invoice->refresh()->recalculateTotals();

    // Register with the KvK a month later.
    storeBillingIdentity([
        'vat_regime' => VatRegime::Standard->value,
        'kvk_number' => '12345678',
        'vat_number' => 'NL123456789B01',
    ]);

    $html = renderInvoiceHtml($invoice->refresh());

    // The document that went out had no KvK number and no VAT. It has to stay
    // that document for the seven years it is kept.
    expect($html)->toContain('NOTA')
        ->and($html)->not->toContain('KvK 12345678')
        ->and((float) $invoice->total)->toBe(500.0);
});

it('falls back to current settings for an invoice that never snapshotted one', function () {
    storeBillingIdentity(['legal_name' => 'Fallback Name']);

    // Invoices created before the issuer column existed carry no snapshot.
    $invoice = Invoice::factory()->create(['issuer' => null]);
    InvoiceLine::factory()->for($invoice)->create(['quantity' => 1, 'unit_price' => 10]);
    $invoice->refresh()->recalculateTotals();

    expect(renderInvoiceHtml($invoice))->toContain('Fallback Name');
});

it('renders the billing identity page', function () {
    $this->actingAs(User::factory()->admin()->create());

    livewire(BillingSettings::class)->assertOk();
});

it('saves the billing identity', function () {
    $this->actingAs(User::factory()->admin()->create());

    livewire(BillingSettings::class)
        ->fillForm([
            'legal_name' => 'Florian Geense',
            'iban' => 'NL91ABNA0417164300',
            'vat_regime' => VatRegime::NotRegistered->value,
            'payment_terms_days' => 30,
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    expect(BillingIdentity::get('legal_name'))->toBe('Florian Geense')
        ->and(BillingIdentity::paymentTermsDays())->toBe(30)
        ->and(BillingIdentity::regime())->toBe(VatRegime::NotRegistered);
});

it('refuses to charge VAT without a VAT number to charge it under', function () {
    $this->actingAs(User::factory()->admin()->create());

    livewire(BillingSettings::class)
        ->fillForm([
            'legal_name' => 'Florian Geense',
            'vat_regime' => VatRegime::Standard->value,
            'vat_number' => null,
        ])
        ->call('save')
        ->assertHasFormErrors(['vat_number']);
});

it('does not require a KvK number for any regime', function () {
    $this->actingAs(User::factory()->admin()->create());

    livewire(BillingSettings::class)
        ->fillForm([
            'legal_name' => 'Florian Geense',
            'vat_regime' => VatRegime::Standard->value,
            'vat_number' => 'NL123456789B01',
            'kvk_number' => null,
        ])
        ->call('save')
        ->assertHasNoFormErrors();
});
