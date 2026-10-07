<?php

declare(strict_types=1);

use App\Billing\IssueRetainerInvoice;
use App\Enums\InvoiceStatus;
use App\Enums\RetainerInterval;
use App\Mail\RecurringInvoicesDrafted;
use App\Models\Invoice;
use App\Models\Retainer;
use App\Models\User;
use Illuminate\Support\Facades\Mail;

it('drafts an invoice for every retainer whose period has started', function () {
    $due = Retainer::factory()->automatic()->create(['amount' => 150]);
    $notYet = Retainer::factory()->automatic()->create([
        'amount' => 99,
        'next_due_date' => today()->addDay(),
    ]);
    $manual = Retainer::factory()->create([
        'amount' => 80,
        'next_due_date' => today(),
    ]);

    $this->artisan('invoices:issue-recurring')->assertSuccessful();

    expect($due->invoices()->count())->toBe(1)
        // A period that has not started yet is not owed anything.
        ->and($notYet->invoices()->count())->toBe(0)
        // Opting out means opting out: the button still works, the cron does not.
        ->and($manual->invoices()->count())->toBe(0);

    $invoice = $due->invoices()->sole();

    expect($invoice->status)->toBe(InvoiceStatus::Draft)
        ->and((float) $invoice->subtotal)->toBe(150.0)
        ->and($invoice->period_start->toDateString())->toBe(today()->toDateString())
        ->and($invoice->lines()->count())->toBe(1);
});

it('advances the retainer past the period it just billed', function () {
    $retainer = Retainer::factory()->automatic()->create([
        'interval' => RetainerInterval::Quarterly,
        'next_due_date' => today(),
    ]);

    $this->artisan('invoices:issue-recurring')->assertSuccessful();

    $invoice = $retainer->invoices()->sole();

    expect($retainer->refresh()->next_due_date->toDateString())
        ->toBe(today()->addMonthsNoOverflow(3)->toDateString())
        // The period billed runs up to the day before the next one starts.
        ->and($invoice->period_end->toDateString())
        ->toBe(today()->addMonthsNoOverflow(3)->subDay()->toDateString());
});

it('never bills the same period twice', function () {
    $retainer = Retainer::factory()->automatic()->create(['amount' => 150]);

    // Two runs in a row is the realistic failure: an overlapping cron, a
    // retried job, or a hand-click right after the schedule fired.
    $this->artisan('invoices:issue-recurring')->assertSuccessful();
    $this->artisan('invoices:issue-recurring')->assertSuccessful();

    expect($retainer->invoices()->count())->toBe(1);

    // And re-issuing that same period by hand is a no-op, not a second charge.
    expect(app(IssueRetainerInvoice::class)->issue($retainer->refresh(), today()->toImmutable()))->toBeNull()
        ->and($retainer->invoices()->count())->toBe(1);
});

it('does not move the schedule backwards when an old period is re-issued', function () {
    $retainer = Retainer::factory()->automatic()->create(['next_due_date' => today()]);

    $issuer = app(IssueRetainerInvoice::class);
    $issuer->issue($retainer);

    $advanced = $retainer->refresh()->next_due_date;

    $issuer->issue($retainer, today()->subMonthsNoOverflow(6)->toImmutable());

    expect($retainer->refresh()->next_due_date->toDateString())->toBe($advanced->toDateString());
});

it('keeps a retainer anchored to the end of the month', function () {
    // Carbon's plain addMonths turns 31 January into 3 March and never drifts
    // back. The retainer has to stay on the last day it was anchored to.
    $retainer = Retainer::factory()->automatic()->create([
        'interval' => RetainerInterval::Monthly,
        'next_due_date' => '2026-01-31',
    ]);

    app(IssueRetainerInvoice::class)->issue($retainer);

    expect($retainer->refresh()->next_due_date->toDateString())->toBe('2026-02-28');
});

it('catches up a retainer that is a few periods behind', function () {
    $retainer = Retainer::factory()->automatic()->create([
        'interval' => RetainerInterval::Monthly,
        'next_due_date' => today()->subMonthsNoOverflow(2),
    ]);

    $invoices = app(IssueRetainerInvoice::class)->catchUp($retainer);

    expect($invoices)->toHaveCount(3)
        ->and($retainer->refresh()->next_due_date->isFuture())->toBeTrue()
        // One invoice per period, each covering its own month.
        ->and($retainer->invoices()->pluck('period_start')->unique())->toHaveCount(3);
});

it('leaves a retainer that is further behind than the cap for a human', function () {
    $retainer = Retainer::factory()->automatic()->create([
        'interval' => RetainerInterval::Monthly,
        'next_due_date' => today()->subMonthsNoOverflow(11),
    ]);

    $issuer = app(IssueRetainerInvoice::class);

    expect($issuer->catchUp($retainer))->toHaveCount(IssueRetainerInvoice::MAX_CATCH_UP_PERIODS)
        // Still due, which is the signal the run reports rather than billing
        // a year of back periods to a client who was never warned.
        ->and($issuer->needsAttention($retainer->refresh()))->toBeTrue();
});

it('mails the admins what is waiting and sends nothing to the client', function () {
    Mail::fake();

    $admin = User::factory()->admin()->create();
    User::factory()->create();

    Retainer::factory()->automatic()->create();

    $this->artisan('invoices:issue-recurring')->assertSuccessful();

    Mail::assertSent(RecurringInvoicesDrafted::class, fn (RecurringInvoicesDrafted $mail): bool => $mail->hasTo($admin->email)
        && count($mail->invoices) === 1);

    // Exactly one mail, to the admin — never to the client.
    Mail::assertSentCount(1);

    expect(Invoice::query()->where('status', '!=', InvoiceStatus::Draft)->count())->toBe(0);
});

it('reports what it would bill without touching anything on a dry run', function () {
    $retainer = Retainer::factory()->automatic()->create();

    $this->artisan('invoices:issue-recurring', ['--dry-run' => true])->assertSuccessful();

    expect($retainer->invoices()->count())->toBe(0)
        ->and($retainer->refresh()->next_due_date->toDateString())->toBe(today()->toDateString());
});

it('does nothing when no retainer is due', function () {
    Mail::fake();

    Retainer::factory()->create(['next_due_date' => today()->addMonth()]);

    $this->artisan('invoices:issue-recurring')
        ->expectsOutputToContain('Nothing due.')
        ->assertSuccessful();

    Mail::assertNothingSent();
});
