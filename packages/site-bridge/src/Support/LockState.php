<?php

declare(strict_types=1);

namespace Agency\SiteBridge\Support;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Throwable;

final class LockState
{
    private const CACHE_KEY = 'site-bridge:lock';

    /**
     * Current lock level. Fail-safe: any failure (missing table, no DB)
     * reads as 0 — the bridge must never lock a site by accident.
     */
    public function level(): int
    {
        try {
            $cacheSeconds = (int) config('site-bridge.lock.cache_seconds', 15);

            return (int) Cache::remember(
                self::CACHE_KEY,
                $cacheSeconds,
                fn (): int => (int) (DB::table('site_bridge_lock')->value('lock_level') ?? 0),
            );
        } catch (Throwable) {
            return 0;
        }
    }

    /**
     * @return array{lock_level: int, reason: string|null, locked_at: string|null, locked_by: string|null}
     */
    public function describe(): array
    {
        try {
            $row = DB::table('site_bridge_lock')->first();
        } catch (Throwable) {
            $row = null;
        }

        return [
            'lock_level' => (int) ($row->lock_level ?? 0),
            'reason' => $row->reason ?? null,
            'locked_at' => $row->locked_at ?? null,
            'locked_by' => $row->locked_by ?? null,
        ];
    }

    public function set(int $level, ?string $reason, ?string $lockedBy): void
    {
        DB::table('site_bridge_lock')->updateOrInsert(
            ['id' => 1],
            [
                'lock_level' => $level,
                'reason' => $reason,
                'locked_at' => $level > 0 ? now() : null,
                'locked_by' => $lockedBy,
                'updated_at' => now(),
            ],
        );

        Cache::forget(self::CACHE_KEY);
    }

    /**
     * Sequence number of the last hub directive this site accepted. Fail-safe
     * to 0 so a read error can never make a replayed directive look newer.
     */
    public function directiveSeq(): int
    {
        try {
            return (int) (DB::table('site_bridge_lock')->value('directive_seq') ?? 0);
        } catch (Throwable) {
            return 0;
        }
    }

    /**
     * Apply a verified lock directive and record its sequence, so any later
     * directive at or below this one is rejected as a replay.
     */
    public function applyDirective(int $level, ?string $reason, int $seq): void
    {
        DB::table('site_bridge_lock')->updateOrInsert(
            ['id' => 1],
            [
                'lock_level' => $level,
                'reason' => $reason,
                'locked_at' => $level > 0 ? now() : null,
                'locked_by' => 'hub-directive',
                'directive_seq' => $seq,
                'updated_at' => now(),
            ],
        );

        Cache::forget(self::CACHE_KEY);
    }
}
