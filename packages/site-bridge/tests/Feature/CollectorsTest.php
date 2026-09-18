<?php

declare(strict_types=1);

use Agency\SiteBridge\Collectors\MetricsCollector;
use Agency\SiteBridge\Collectors\UpdatesCollector;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Process;

/*
| The collectors feed the outbound heartbeat. Their behaviour used to be
| asserted through the (now removed) pull endpoints; these exercise them
| directly.
*/

it('reports outdated packages with major-bump detection', function () {
    Process::fake([
        '*' => Process::result(output: json_encode([
            'installed' => [
                ['name' => 'laravel/framework', 'version' => 'v12.1.0', 'latest' => 'v13.2.0'],
                ['name' => 'spatie/laravel-backup', 'version' => '9.1.0', 'latest' => '9.2.4'],
            ],
        ])),
    ]);

    $updates = app(UpdatesCollector::class)->collect();

    expect($updates['bridge_version'])->toBe(1)
        ->and($updates['packages']['value'])->toHaveCount(2)
        ->and($updates['packages']['value'][0]['name'])->toBe('laravel/framework')
        ->and($updates['packages']['value'][0]['is_major'])->toBeTrue()
        ->and($updates['packages']['value'][1]['is_major'])->toBeFalse();

    Process::assertRan(fn ($process): bool => implode(' ', (array) $process->command) === 'composer outdated --direct --format=json');
});

it('degrades gracefully when composer fails', function () {
    Process::fake([
        '*' => Process::result(output: '', errorOutput: 'composer: not found', exitCode: 127),
    ]);

    $updates = app(UpdatesCollector::class)->collect();

    expect($updates['packages']['value'])->toBeNull()
        ->and($updates['packages']['reason'])->toContain('composer outdated failed');
});

it('reports bugsnag open errors when credentials are configured', function () {
    config()->set('site-bridge.bugsnag.auth_token', 'secret');
    config()->set('site-bridge.bugsnag.project_id', 'proj-1');

    Http::fake([
        'https://api.bugsnag.com/*' => Http::response([], 200, ['X-Total-Count' => '7']),
    ]);

    $metrics = app(MetricsCollector::class)->collect();

    expect($metrics['bugsnag']['value']['open_errors'])->toBe(7)
        ->and($metrics['bugsnag']['reason'])->toBeNull();
});

it('degrades bugsnag gracefully on api failure', function () {
    config()->set('site-bridge.bugsnag.auth_token', 'secret');
    config()->set('site-bridge.bugsnag.project_id', 'proj-1');

    Http::fake([
        'https://api.bugsnag.com/*' => Http::response('nope', 401),
    ]);

    $metrics = app(MetricsCollector::class)->collect();

    expect($metrics['bugsnag']['value'])->toBeNull()
        ->and($metrics['bugsnag']['reason'])->toBe('bugsnag api returned HTTP 401');
});
