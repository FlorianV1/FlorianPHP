<?php

declare(strict_types=1);

namespace App\Models;

use Carbon\CarbonImmutable;
use Database\Factories\SiteBridgeClaimCodeFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A one-time, short-lived code the operator generates on the hub and the
 * client site redeems (via `site-bridge:claim`) for a long-lived key.
 *
 * @property string $code_hash
 * @property CarbonImmutable $expires_at
 * @property CarbonImmutable|null $claimed_at
 */
final class SiteBridgeClaimCode extends Model
{
    /** @use HasFactory<SiteBridgeClaimCodeFactory> */
    use HasFactory;

    /**
     * Every column except the key and timestamps. Written only by the
     * heartbeat and enrolment services, never from request input, but listed
     * explicitly so a new column has to be opted in deliberately.
     *
     * @var list<string>
     */
    protected $fillable = [
        'website_id',
        'code_hash',
        'expires_at',
        'claimed_at',
    ];

    /** @return BelongsTo<Website, $this> */
    public function website(): BelongsTo
    {
        return $this->belongsTo(Website::class);
    }

    /**
     * Codes that are still redeemable: neither claimed nor expired.
     *
     * @param  Builder<SiteBridgeClaimCode>  $query
     */
    public function scopeUsable(Builder $query): void
    {
        $query->whereNull('claimed_at')->where('expires_at', '>', now());
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'expires_at' => 'immutable_datetime',
            'claimed_at' => 'immutable_datetime',
        ];
    }
}
