<?php

declare(strict_types=1);

use App\Bugsnag\BugsnagApiException;
use App\Bugsnag\BugsnagSync;
use App\Models\Website;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;

beforeEach(function (): void {
    config()->set('bugsnag-hub.auth_token', 'test-token');
    config()->set('bugsnag-hub.organization_id', null);
});

/**
 * @param  list<array<string, mixed>>  $projects
 */
function fakeBugsnag(array $projects = [], int $openErrors = 0): void
{
    Http::fake([
        'api.bugsnag.com/user/organizations' => Http::response([['id' => 'org-1', 'name' => 'Agency']]),
        'api.bugsnag.com/organizations/*/projects*' => Http::response($projects),
        'api.bugsnag.com/projects/*/errors*' => Http::response([], 200, ['X-Total-Count' => (string) $openErrors]),
    ]);
}

it('links a website to its project by the stored notifier api key', function () {
    fakeBugsnag([
        ['id' => 'proj-other', 'name' => 'Someone else', 'api_key' => 'zzz', 'html_url' => 'https://app.bugsnag.com/agency/other'],
        ['id' => 'proj-1', 'name' => 'Acme', 'api_key' => 'abc123', 'html_url' => 'https://app.bugsnag.com/agency/acme'],
    ]);

    $website = Website::factory()->create([
        'label' => 'Acme site',
        'bugsnag_project_key' => 'abc123',
        'bugsnag_project_url' => null,
    ]);

    $result = app(BugsnagSync::class)->linkProjects();

    expect($result['linked'])->toHaveCount(1)
        ->and($result['unmatched'])->toBeEmpty();

    expect($website->refresh())
        ->bugsnag_project_id->toBe('proj-1')
        ->bugsnag_project_url->toBe('https://app.bugsnag.com/agency/acme');
});

it('falls back to matching by project url and then by name', function () {
    fakeBugsnag([
        ['id' => 'by-url', 'name' => 'Unrelated', 'api_key' => 'nope', 'html_url' => 'https://app.bugsnag.com/agency/by-url'],
        ['id' => 'by-name', 'name' => 'Named site', 'api_key' => 'nope-2', 'html_url' => null],
    ]);

    $byUrl = Website::factory()->create([
        'bugsnag_project_key' => null,
        'bugsnag_project_url' => 'https://app.bugsnag.com/agency/by-url/',
    ]);

    $byName = Website::factory()->create([
        'label' => 'Named site',
        'bugsnag_project_key' => null,
        'bugsnag_project_url' => null,
    ]);

    app(BugsnagSync::class)->linkProjects();

    expect($byUrl->refresh()->bugsnag_project_id)->toBe('by-url')
        ->and($byName->refresh()->bugsnag_project_id)->toBe('by-name');
});

it('reports sites it could not match and leaves linked sites alone', function () {
    fakeBugsnag([['id' => 'proj-1', 'name' => 'Acme', 'api_key' => 'abc123', 'html_url' => null]]);

    Website::factory()->create([
        'label' => 'Unknown site',
        'bugsnag_project_key' => null,
        'bugsnag_project_url' => null,
    ]);

    $alreadyLinked = Website::factory()->create([
        'label' => 'Already linked',
        'bugsnag_project_key' => 'abc123',
        'bugsnag_project_id' => 'pinned-by-hand',
    ]);

    $result = app(BugsnagSync::class)->linkProjects();

    expect($result['unmatched'])->toBe(['Unknown site'])
        ->and($result['linked'])->toBeEmpty()
        ->and($alreadyLinked->refresh()->bugsnag_project_id)->toBe('pinned-by-hand');
});

it('re-matches already linked sites when asked to relink', function () {
    fakeBugsnag([['id' => 'proj-1', 'name' => 'Acme', 'api_key' => 'abc123', 'html_url' => null]]);

    $website = Website::factory()->create([
        'bugsnag_project_key' => 'abc123',
        'bugsnag_project_id' => 'stale',
    ]);

    app(BugsnagSync::class)->linkProjects(relink: true);

    expect($website->refresh()->bugsnag_project_id)->toBe('proj-1');
});

it('pulls the open error count for every linked site', function () {
    fakeBugsnag(openErrors: 7);

    $linked = Website::factory()->create(['bugsnag_project_id' => 'proj-1']);
    $unlinked = Website::factory()->create(['bugsnag_project_id' => null]);

    $this->artisan('bugsnag:sync-errors')
        ->expectsOutputToContain('Synced 1 site(s), 0 failed')
        ->assertSuccessful();

    expect($linked->refresh())
        ->bugsnag_open_errors->toBe(7)
        ->bugsnag_synced_at->not->toBeNull()
        ->bugsnag_sync_error->toBeNull();

    expect($unlinked->refresh()->bugsnag_synced_at)->toBeNull();

    Http::assertSent(fn (Request $request): bool => str_contains($request->url(), '/projects/proj-1/errors')
        && str_contains($request->url(), 'open')
        && $request->hasHeader('Authorization', 'token test-token'));
});

it('keeps the last known count and records why when bugsnag fails', function () {
    Http::fake(['api.bugsnag.com/*' => Http::response([], 503)]);

    $website = Website::factory()->create([
        'bugsnag_project_id' => 'proj-1',
        'bugsnag_open_errors' => 4,
        'bugsnag_synced_at' => now()->subHour(),
    ]);

    $this->artisan('bugsnag:sync-errors')
        ->expectsOutputToContain('Synced 0 site(s), 1 failed')
        ->assertSuccessful();

    expect($website->refresh())
        ->bugsnag_open_errors->toBe(4)
        ->bugsnag_sync_error->toContain('HTTP 503');

    // The successful-sync timestamp must not advance on a failure: the count
    // has to read as old rather than freshly confirmed.
    expect($website->bugsnag_synced_at->isBefore(now()->subMinutes(30)))->toBeTrue();
});

it('explains an unusable auth token instead of leaving a blank count', function () {
    Http::fake(['api.bugsnag.com/*' => Http::response([], 401)]);

    $website = Website::factory()->create(['bugsnag_project_id' => 'proj-1']);

    app(BugsnagSync::class)->syncWebsite($website);

    expect($website->refresh()->bugsnag_sync_error)->toContain('rejected the auth token');
});

it('follows link-header pagination when listing projects', function () {
    Http::fake([
        'api.bugsnag.com/user/organizations' => Http::response([['id' => 'org-1']]),
        'api.bugsnag.com/organizations/org-1/projects?per_page=100' => Http::response(
            [['id' => 'p1', 'name' => 'One', 'api_key' => 'k1', 'html_url' => null]],
            200,
            ['Link' => '<https://api.bugsnag.com/organizations/org-1/projects?offset=100>; rel="next"'],
        ),
        'api.bugsnag.com/organizations/org-1/projects?offset=100' => Http::response(
            [['id' => 'p2', 'name' => 'Two', 'api_key' => 'k2', 'html_url' => null]],
        ),
    ]);

    $website = Website::factory()->create(['bugsnag_project_key' => 'k2']);

    app(BugsnagSync::class)->linkProjects();

    expect($website->refresh()->bugsnag_project_id)->toBe('p2');
});

it('does nothing at all until an auth token is configured', function () {
    config()->set('bugsnag-hub.auth_token', null);
    Http::fake();

    Website::factory()->create(['bugsnag_project_id' => 'proj-1']);

    $this->artisan('bugsnag:sync-errors')
        ->expectsOutputToContain('not configured')
        ->assertSuccessful();

    $this->artisan('bugsnag:link-projects')
        ->expectsOutputToContain('not configured')
        ->assertSuccessful();

    Http::assertNothingSent();
});

it('surfaces an organization-level failure as a failed command', function () {
    Http::fake(['api.bugsnag.com/*' => Http::response([], 403)]);

    $this->artisan('bugsnag:link-projects')->assertFailed();
});

it('throws when the token can not see any organization', function () {
    Http::fake(['api.bugsnag.com/user/organizations' => Http::response([])]);

    app(BugsnagSync::class)->linkProjects();
})->throws(BugsnagApiException::class, 'can not see any Bugsnag organization');
