<?php

declare(strict_types=1);

namespace App\Models;

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
 * @property \Carbon\CarbonImmutable $expires_at
 * @property \Carbon\CarbonImmutable|null $claimed_at
 */
final class SiteBridgeClaimCode extends Model
{
    /** @use HasFactory<SiteBridgeClaimCodeFactory> */
    use HasFactory;

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
