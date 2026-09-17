<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\SiteBridgeHeartbeatFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Prunable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * An append-only record of each check-in, kept for the "last seen" signal
 * and a short forensic window. Raw rows are pruned after the retention
 * window; {@see SiteBridgeHeartbeatStat} keeps the daily history. The full
 * status/metrics snapshot lives on the website's `health` column.
 *
 * @property int|null $reported_lock_level
 * @property \Carbon\CarbonImmutable $received_at
 */
final class SiteBridgeHeartbeat extends Model
{
    /** @use HasFactory<SiteBridgeHeartbeatFactory> */
    use HasFactory, Prunable;

    public $timestamps = false;

    /**
     * @return Builder<SiteBridgeHeartbeat>
     */
    public function prunable(): Builder
    {
        return self::query()->where(
            'received_at',
            '<',
            now()->subDays((int) config('site-bridge.heartbeat.retention_days', 14)),
        );
    }

    /** @return BelongsTo<Website, $this> */
    public function website(): BelongsTo
    {
        return $this->belongsTo(Website::class);
    }

    /** @return BelongsTo<SiteBridgeKey, $this> */
    public function key(): BelongsTo
    {
        return $this->belongsTo(SiteBridgeKey::class, 'site_bridge_key_id');
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'reported_lock_level' => 'integer',
            'received_at' => 'immutable_datetime',
        ];
    }
}
