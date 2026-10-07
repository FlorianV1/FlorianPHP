<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\InvoiceStatus;
use App\Enums\VatRegime;
use App\Support\BillingIdentity;
use Carbon\CarbonImmutable;
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
 * @property VatRegime $vat_regime
 * @property array<string, mixed>|null $issuer
 * @property CarbonImmutable|null $period_start
 * @property CarbonImmutable|null $period_end
 * @property numeric-string $subtotal
 * @property numeric-string $vat_rate
 * @property numeric-string $vat_amount
 * @property numeric-string $total
 * @property CarbonImmutable $issue_date
 * @property CarbonImmutable $due_date
 * @property CarbonImmutable|null $paid_at
 */
final class Invoice extends Model
{
    /** @use HasFactory<InvoiceFactory> */
    use HasFactory, LogsActivity;

    /**
     * Fallback payment term, used only when the billing settings have never
     * been saved. The rate is no longer a constant: it comes from the VAT
     * regime, because charging 21% needs a VAT registration to charge it
     * under.
     */
    private const FALLBACK_PAYMENT_TERMS_DAYS = 14;

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
     *
     * @param  array<string, mixed>  $attributes  Extra columns — the recurring
     *                                            run passes the retainer and
     *                                            the period it covers.
     */
    public static function openDraftFor(Client $client, ?Website $website = null, array $attributes = []): self
    {
        $regime = BillingIdentity::regime();
        $terms = BillingIdentity::paymentTermsDays() ?: self::FALLBACK_PAYMENT_TERMS_DAYS;

        return self::query()->create(array_merge([
            'client_id' => $client->id,
            'website_id' => $website?->id,
            'number' => self::nextNumber(),
            'issue_date' => today(),
            'due_date' => today()->addDays($terms),
            'status' => InvoiceStatus::Draft,
            // A snapshot, not a live read: an invoice sent before the
            // Handelsregister entry existed has to keep printing without a
            // KvK number for the rest of its seven years.
            'issuer' => BillingIdentity::snapshot(),
            'vat_regime' => $regime,
            'vat_rate' => $regime->rate(),
            'vat_note' => $regime->invoiceNote(),
        ], $attributes));
    }

    /**
     * Every column except the key and timestamps. These models are reached
     * only through the admin panel and internal services, never from request
     * input, but they are listed explicitly rather than unguarded so that a
     * new column has to be opted in deliberately.
     *
     * @var list<string>
     */
    protected $fillable = [
        'client_id',
        'website_id',
        'retainer_id',
        'issuer',
        'period_start',
        'period_end',
        'number',
        'issue_date',
        'due_date',
        'status',
        'subtotal',
        'vat_rate',
        'vat_regime',
        'vat_note',
        'vat_amount',
        'total',
        'paid_at',
        'external_reference',
        'notes',
    ];

    /** @return BelongsTo<Client, $this> */
    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    /** @return BelongsTo<Retainer, $this> */
    public function retainer(): BelongsTo
    {
        return $this->belongsTo(Retainer::class);
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

    /**
     * The sender block for the PDF. Invoices from before the snapshot existed
     * have none and fall back to the current settings.
     *
     * @return array<string, mixed>
     */
    public function issuerDetails(): array
    {
        return $this->issuer ?? BillingIdentity::snapshot();
    }

    /**
     * Whether this invoice carries a VAT row. Read off the stored rate, not
     * the regime, so an invoice issued under a regime that has since changed
     * still prints the way it was sent.
     */
    public function chargesVat(): bool
    {
        return (float) $this->vat_rate > 0.0;
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
            'vat_regime' => VatRegime::class,
            'issuer' => 'array',
            'period_start' => 'immutable_date',
            'period_end' => 'immutable_date',
            'subtotal' => 'decimal:2',
            'vat_rate' => 'decimal:2',
            'vat_amount' => 'decimal:2',
            'total' => 'decimal:2',
            'paid_at' => 'immutable_datetime',
        ];
    }
}
