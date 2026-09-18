<?php

declare(strict_types=1);

use App\Models\Website;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;

beforeEach(fn () => config()->set('site-bridge.alerts.ntfy_url', 'https://ntfy.example/alerts'));

it('alerts once when an enrolled site goes silent', function () {
    Http::fake();

    $website = Website::factory()->create([
        'enrolled_at' => now()->subDay(),
        'last_heartbeat_at' => now()->subHour(),
        'health' => ['ok' => true, 'status' => ['up' => true]],
    ]);

    $this->artisan('sites:check-health')
        ->expectsOutputToContain('1 alert(s)')
        ->assertSuccessful();

    Http::assertSent(fn (Request $request): bool => $request->url() === 'https://ntfy.example/alerts'
        && $request->hasHeader('Title', "Site down: {$website->label}")
        && $request->hasHeader('Priority', 'urgent')
        && str_contains($request->body(), 'has not checked in'));

    expect($website->refresh()->health_alerted_at)->not->toBeNull();

    // The next run sees the alert already sent and stays quiet.
    $this->artisan('sites:check-health')->assertSuccessful();

    Http::assertSentCount(1);
});

it('alerts when a site reports itself as down', function () {
    Http::fake();

    Website::factory()->create([
        'health' => ['ok' => true, 'status' => ['up' => false]],
    ]);

    $this->artisan('sites:check-health')->assertSuccessful();

    Http::assertSent(fn (Request $request): bool => str_contains($request->body(), 'reports itself as down'));
});

it('sends a recovery alert and clears the state once the site is healthy again', function () {
    Http::fake();

    $website = Website::factory()->create([
        'enrolled_at' => now()->subDay(),
        'last_heartbeat_at' => now(),
        'health' => ['ok' => true, 'status' => ['up' => true]],
        'health_alerted_at' => now()->subHour(),
    ]);

    $this->artisan('sites:check-health')->assertSuccessful();

    Http::assertSent(fn (Request $request): bool => $request->hasHeader('Title', "Site recovered: {$website->label}"));

    expect($website->refresh()->health_alerted_at)->toBeNull();
});

it('gives a freshly enrolled site a staleness window to make its first check-in', function () {
    Http::fake();

    Website::factory()->create(['enrolled_at' => now()->subMinute()]);

    $this->artisan('sites:check-health')->assertSuccessful();

    Http::assertNothingSent();
});

it('alerts for an enrolled site that never managed a first check-in', function () {
    Http::fake();

    Website::factory()->create(['enrolled_at' => now()->subDay()]);

    $this->artisan('sites:check-health')->assertSuccessful();

    Http::assertSent(fn (Request $request): bool => str_contains($request->body(), 'has never checked in'));
});

it('does nothing when no alert channel is configured', function () {
    config()->set('site-bridge.alerts.ntfy_url', null);
    Http::fake();

    Website::factory()->create(['enrolled_at' => now()->subDay()]);

    $this->artisan('sites:check-health')
        ->expectsOutputToContain('disabled')
        ->assertSuccessful();

    Http::assertNothingSent();
});

it('retries on the next run when delivery fails', function () {
    Http::fake(['*' => Http::response('', 500)]);

    $website = Website::factory()->create(['enrolled_at' => now()->subDay()]);

    $this->artisan('sites:check-health')
        ->expectsOutputToContain('0 alert(s)')
        ->assertSuccessful();

    // Delivery failed, so the state is untouched and the next run retries.
    expect($website->refresh()->health_alerted_at)->toBeNull();

    Http::assertSentCount(1);
});
