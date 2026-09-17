<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\TimeEntryFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property \Carbon\CarbonImmutable $work_date
 * @property numeric-string $hours
 * @property numeric-string|null $hourly_rate
 */
final class TimeEntry extends Model
{
    /** @use HasFactory<TimeEntryFactory> */
    use HasFactory;

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

    /** @return BelongsTo<InvoiceLine, $this> */
    public function invoiceLine(): BelongsTo
    {
        return $this->belongsTo(InvoiceLine::class);
    }

    /**
     * @param  Builder<self>  $query
     * @return Builder<self>
     */
    public function scopeUninvoiced(Builder $query): Builder
    {
        return $query->whereNull('invoice_line_id');
    }

    public function isInvoiced(): bool
    {
        return $this->invoice_line_id !== null;
    }

    /**
     * The rate actually billed: the entry's own rate, or the client's.
     */
    public function effectiveRate(): float
    {
        return (float) ($this->hourly_rate ?? $this->client->hourly_rate ?? 0);
    }

    public function amount(): float
    {
        return round((float) $this->hours * $this->effectiveRate(), 2);
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'work_date' => 'immutable_date',
            'hours' => 'decimal:2',
            'hourly_rate' => 'decimal:2',
        ];
    }
}
