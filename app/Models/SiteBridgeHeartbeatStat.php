<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A daily rollup of a site's check-ins, kept for the long-term history that
 * outlives the pruned raw {@see SiteBridgeHeartbeat} rows.
 *
 * @property \Carbon\CarbonImmutable $day
 * @property int $heartbeats
 * @property int|null $max_reported_lock_level
 * @property \Carbon\CarbonImmutable|null $first_seen_at
 * @property \Carbon\CarbonImmutable|null $last_seen_at
 */
final class SiteBridgeHeartbeatStat extends Model
{
    /** @return BelongsTo<Website, $this> */
    public function website(): BelongsTo
    {
        return $this->belongsTo(Website::class);
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'day' => 'immutable_date',
            'heartbeats' => 'integer',
            'max_reported_lock_level' => 'integer',
            'first_seen_at' => 'immutable_datetime',
            'last_seen_at' => 'immutable_datetime',
        ];
    }
}
