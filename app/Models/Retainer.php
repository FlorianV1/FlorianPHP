<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\RetainerInterval;
use Carbon\CarbonImmutable;
use Database\Factories\RetainerFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property RetainerInterval $interval
 * @property numeric-string $amount
 * @property CarbonImmutable $next_due_date
 * @property bool $active
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
    ];

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
