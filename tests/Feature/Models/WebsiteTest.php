<?php

declare(strict_types=1);

use App\Models\Website;

it('derives the traffic-light health status from the last snapshot', function (?array $health, string $expected) {
    // enrolled_at stays null so staleness never masks the snapshot logic.
    $website = Website::factory()->make(['health' => $health]);

    expect($website->healthStatus())->toBe($expected);
})->with([
    'no snapshot yet' => [null, 'unknown'],
    'pull failed' => [['ok' => false], 'red'],
    'site down' => [['ok' => true, 'status' => ['up' => false]], 'red'],
    'maintenance mode' => [['ok' => true, 'status' => ['up' => true, 'maintenance_mode' => true]], 'amber'],
    'locked' => [['ok' => true, 'status' => ['up' => true, 'lock' => ['lock_level' => 2]]], 'amber'],
    'failed jobs' => [['ok' => true, 'status' => ['up' => true], 'metrics' => ['failed_jobs' => ['value' => 3]]], 'amber'],
    'open bugsnag errors' => [['ok' => true, 'status' => ['up' => true], 'metrics' => ['bugsnag' => ['value' => ['open_errors' => 1]]]], 'amber'],
    'healthy' => [['ok' => true, 'status' => ['up' => true]], 'green'],
]);

it('prefers the count the hub pulled over the one the site reported', function () {
    $website = Website::factory()->make([
        'health' => ['metrics' => ['bugsnag' => ['value' => ['open_errors' => 9]]]],
        'bugsnag_project_id' => 'proj-1',
        'bugsnag_open_errors' => 2,
        'bugsnag_synced_at' => now(),
    ]);

    expect($website->bugsnagOpenErrors())->toBe(2)
        ->and($website->bugsnagUnavailableReason())->toBeNull();
});

it('falls back to the heartbeat count until the hub has synced', function () {
    $website = Website::factory()->make([
        'health' => ['metrics' => ['bugsnag' => ['value' => ['open_errors' => 9]]]],
        'bugsnag_project_id' => null,
        'bugsnag_synced_at' => null,
    ]);

    expect($website->bugsnagOpenErrors())->toBe(9);
});

it('counts hub-pulled bugsnag errors towards the amber health status', function () {
    $website = Website::factory()->make([
        'health' => ['ok' => true, 'status' => ['up' => true]],
        'bugsnag_project_id' => 'proj-1',
        'bugsnag_open_errors' => 3,
        'bugsnag_synced_at' => now(),
    ]);

    expect($website->healthErrorCount())->toBe(3)
        ->and($website->healthStatus())->toBe('amber');
});

it('explains a missing bugsnag count', function (array $attributes, string $expected) {
    expect(Website::factory()->make($attributes)->bugsnagUnavailableReason())->toBe($expected);
})->with([
    'never linked' => [['bugsnag_project_id' => null, 'health' => null], 'not linked to a Bugsnag project'],
    'linked, not yet pulled' => [['bugsnag_project_id' => 'proj-1', 'bugsnag_synced_at' => null], 'not synced yet'],
    'last pull failed' => [['bugsnag_project_id' => 'proj-1', 'bugsnag_sync_error' => 'Bugsnag returned HTTP 503.'], 'Bugsnag returned HTTP 503.'],
]);
