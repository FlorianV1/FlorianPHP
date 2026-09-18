<?php

declare(strict_types=1);

use App\Filament\Management\Resources\Websites\Pages\ViewWebsite;
use App\Models\Website;
use Filament\Actions\Testing\TestAction;
use Illuminate\Support\Facades\Http;

use function Pest\Livewire\livewire;

beforeEach(fn () => config()->set('bugsnag-hub.auth_token', 'test-token'));

it('syncs a linked site on demand from the website page', function () {
    Http::fake(['api.bugsnag.com/projects/*/errors*' => Http::response([], 200, ['X-Total-Count' => '5'])]);

    $website = Website::factory()->create(['bugsnag_project_id' => 'proj-1']);

    livewire(ViewWebsite::class, ['record' => $website->id])
        ->callAction(TestAction::make('syncBugsnag'))
        ->assertNotified('5 open error(s)');

    expect($website->refresh()->bugsnag_open_errors)->toBe(5);
});

it('reports the reason when an on-demand sync fails', function () {
    Http::fake(['api.bugsnag.com/*' => Http::response([], 503)]);

    $website = Website::factory()->create(['bugsnag_project_id' => 'proj-1']);

    livewire(ViewWebsite::class, ['record' => $website->id])
        ->callAction(TestAction::make('syncBugsnag'))
        ->assertNotified('Bugsnag sync failed');
});

it('hides the sync action for a site with no linked project', function () {
    $website = Website::factory()->create(['bugsnag_project_id' => null]);

    livewire(ViewWebsite::class, ['record' => $website->id])
        ->assertActionHidden(TestAction::make('syncBugsnag'));
});

it('hides the sync action when bugsnag is not configured', function () {
    config()->set('bugsnag-hub.auth_token', null);

    $website = Website::factory()->create(['bugsnag_project_id' => 'proj-1']);

    livewire(ViewWebsite::class, ['record' => $website->id])
        ->assertActionHidden(TestAction::make('syncBugsnag'));
});
