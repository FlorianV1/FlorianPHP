<?php

declare(strict_types=1);

use App\Models\SiteBridgeHeartbeat;
use App\Models\SiteBridgeHeartbeatStat;
use App\Models\Website;

it('rolls up a day of heartbeats into one stat row per site', function () {
    $website = Website::factory()->create();
    $day = now()->subDay()->startOfDay();

    SiteBridgeHeartbeat::factory()->count(3)->for($website)->create([
        'received_at' => $day->copy()->setTime(10, 0),
        'reported_lock_level' => 0,
    ]);
    SiteBridgeHeartbeat::factory()->for($website)->create([
        'received_at' => $day->copy()->setTime(12, 0),
        'reported_lock_level' => 3,
    ]);

    $this->artisan('site-bridge:rollup-heartbeats')->assertSuccessful();

    $stat = SiteBridgeHeartbeatStat::query()->firstOrFail();

    expect($stat->website_id)->toBe($website->id)
        ->and($stat->heartbeats)->toBe(4)
        ->and($stat->max_reported_lock_level)->toBe(3)
        ->and($stat->day->toDateString())->toBe($day->toDateString());
});

it('is idempotent when re-run for the same day', function () {
    $website = Website::factory()->create();
    $day = now()->subDay()->startOfDay();
    SiteBridgeHeartbeat::factory()->count(2)->for($website)->create(['received_at' => $day->copy()->setTime(9, 0)]);

    $this->artisan('site-bridge:rollup-heartbeats')->assertSuccessful();
    $this->artisan('site-bridge:rollup-heartbeats')->assertSuccessful();

    expect(SiteBridgeHeartbeatStat::query()->count())->toBe(1)
        ->and(SiteBridgeHeartbeatStat::query()->firstOrFail()->heartbeats)->toBe(2);
});

it('prunes raw heartbeats past the retention window but keeps recent ones', function () {
    $website = Website::factory()->create();

    $old = SiteBridgeHeartbeat::factory()->for($website)->create(['received_at' => now()->subDays(30)]);
    $recent = SiteBridgeHeartbeat::factory()->for($website)->create(['received_at' => now()->subDay()]);

    $this->artisan('model:prune', ['--model' => [SiteBridgeHeartbeat::class]])->assertSuccessful();

    expect(SiteBridgeHeartbeat::query()->find($old->id))->toBeNull()
        ->and(SiteBridgeHeartbeat::query()->find($recent->id))->not->toBeNull();
});
