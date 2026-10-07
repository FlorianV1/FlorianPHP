<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\RetainerInterval;
use Carbon\CarbonImmutable;
use Database\Factories\RetainerFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property RetainerInterval $interval
 * @property numeric-string $amount
 * @property CarbonImmutable $next_due_date
 * @property bool $active
 * @property bool $auto_invoice
 */
final class Retainer extends Model
{
    /** @use HasFactory<RetainerFactory> */
    use HasFactory;

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
        'description',
        'amount',
        'interval',
        'next_due_date',
        'active',
        'auto_invoice',
    ];

    /** @return BelongsTo<Client, $this> */
    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    /** @return HasMany<Invoice, $this> */
    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    /** @return BelongsTo<Website, $this> */
    public function website(): BelongsTo
    {
        return $this->belongsTo(Website::class);
    }

    /**
     * Retainers the unattended run is allowed to bill: switched on, opted in,
     * and with a period that has started. Reads are kept to this one scope so
     * the command and any dashboard count can never disagree about what is
     * due.
     *
     * @param  Builder<self>  $query
     */
    public function scopeDueForInvoicing(Builder $query): void
    {
        $query->where('active', true)
            ->where('auto_invoice', true)
            ->whereDate('next_due_date', '<=', today());
    }

    /**
     * The last day covered by a period starting on the given date.
     *
     * `addMonthsNoOverflow` matters here: Carbon's plain `addMonths` turns a
     * retainer anchored on the 31st into the 3rd of the month after next, and
     * once it has drifted it never drifts back.
     */
    public function periodEndFrom(CarbonImmutable $start): CarbonImmutable
    {
        return $start->addMonthsNoOverflow($this->interval->months())->subDay();
    }

    /**
     * The first day of the period after the one starting on the given date.
     */
    public function nextPeriodStartFrom(CarbonImmutable $start): CarbonImmutable
    {
        return $start->addMonthsNoOverflow($this->interval->months());
    }

    public function monthlyAmount(): float
    {
        return $this->interval->monthlyAmount((float) $this->amount);
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'interval' => RetainerInterval::class,
            'next_due_date' => 'immutable_date',
            'active' => 'boolean',
            'auto_invoice' => 'boolean',
        ];
    }
}
