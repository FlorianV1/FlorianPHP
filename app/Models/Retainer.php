<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\RetainerInterval;
use Database\Factories\RetainerFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property RetainerInterval $interval
 * @property numeric-string $amount
 * @property \Carbon\CarbonImmutable $next_due_date
 * @property bool $active
 */
final class Retainer extends Model
{
    /** @use HasFactory<RetainerFactory> */
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
        ];
    }
}
