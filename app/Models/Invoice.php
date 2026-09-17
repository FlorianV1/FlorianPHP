<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\InvoiceStatus;
use Database\Factories\InvoiceFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

/**
 * @property InvoiceStatus $status
 * @property numeric-string $subtotal
 * @property numeric-string $vat_rate
 * @property numeric-string $vat_amount
 * @property numeric-string $total
 * @property \Carbon\CarbonImmutable $issue_date
 * @property \Carbon\CarbonImmutable $due_date
 * @property \Carbon\CarbonImmutable|null $paid_at
 */
final class Invoice extends Model
{
    /** @use HasFactory<InvoiceFactory> */
    use HasFactory, LogsActivity;

    /**
     * Days until an invoice falls due, and the default VAT percentage — the
     * terms every draft is created with.
     */
    private const PAYMENT_TERMS_DAYS = 14;

    private const DEFAULT_VAT_RATE = 21;

    /**
     * The next `INV-YYYY-NNN` for the current year. Derived from the highest
     * existing suffix (not a row count), so it stays monotonic even after an
     * invoice is deleted and never reissues a number. The unique index on
     * `number` is the final guard against a concurrent collision.
     */
    public static function nextNumber(): string
    {
        $year = now()->format('Y');

        // Zero-padded to 3, so lexical max equals numeric max within a year.
        $latest = self::query()->where('number', 'like', "INV-{$year}-%")->max('number');

        $sequence = $latest !== null ? ((int) mb_substr((string) $latest, -3)) + 1 : 1;

        return sprintf('INV-%s-%03d', $year, $sequence);
    }

    /**
     * Open a fresh draft invoice for a client (optionally tied to a website)
     * with the standard terms. Shared by the retainer and logged-work flows
     * so draft creation lives in exactly one place.
     */
    public static function openDraftFor(Client $client, ?Website $website = null): self
    {
        return self::query()->create([
            'client_id' => $client->id,
            'website_id' => $website?->id,
            'number' => self::nextNumber(),
            'issue_date' => today(),
            'due_date' => today()->addDays(self::PAYMENT_TERMS_DAYS),
            'status' => InvoiceStatus::Draft,
            'vat_rate' => self::DEFAULT_VAT_RATE,
        ]);
    }

    /** @return BelongsTo<Client, $this> */
    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    /** @return BelongsTo<Website, $this> */
    public function website(): BelongsTo
    {
        return $this->belongsTo(Website::class);
    }

    /** @return HasMany<InvoiceLine, $this> */
    public function lines(): HasMany
    {
        return $this->hasMany(InvoiceLine::class);
    }

    /**
     * Recompute subtotal, VAT, and total from the line items and persist.
     */
    public function recalculateTotals(): void
    {
        $subtotal = round((float) $this->lines()->sum('amount'), 2);
        $vatAmount = round($subtotal * ((float) $this->vat_rate / 100), 2);

        $this->forceFill([
            'subtotal' => $subtotal,
            'vat_amount' => $vatAmount,
            'total' => round($subtotal + $vatAmount, 2),
        ])->save();
    }

    public function isOutstanding(): bool
    {
        return in_array($this->status, InvoiceStatus::outstanding(), true);
    }

    /**
     * Outstanding invoices past their due date — the single definition of
     * "overdue" for reads (the table filter and the finance widget). The
     * daily `invoices:mark-overdue` transition is separate: it only flips
     * still-`Sent` invoices, so it queries that status explicitly.
     *
     * @param  Builder<self>  $query
     */
    public function scopeOverdue(Builder $query): void
    {
        $query->whereIn('status', InvoiceStatus::outstanding())
            ->whereDate('due_date', '<', today());
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['status', 'number', 'total', 'paid_at'])
            ->logOnlyDirty()
            ->dontLogEmptyChanges();
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'issue_date' => 'immutable_date',
            'due_date' => 'immutable_date',
            'status' => InvoiceStatus::class,
            'subtotal' => 'decimal:2',
            'vat_rate' => 'decimal:2',
            'vat_amount' => 'decimal:2',
            'total' => 'decimal:2',
            'paid_at' => 'immutable_datetime',
        ];
    }
}
