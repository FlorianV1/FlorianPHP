<?php

declare(strict_types=1);

use App\Filament\Management\Resources\Websites\Pages\ViewWebsite;
use App\Models\Website;
use Filament\Actions\Testing\TestAction;
use Illuminate\Support\Facades\Http;

use function Pest\Livewire\livewire;

it('stages a desired lock level and never pushes to the site', function () {
    Http::fake();

    $website = Website::factory()->create(['enrolled_at' => now(), 'lock_level' => 0, 'desired_lock_level' => 0]);

    livewire(ViewWebsite::class, ['record' => $website->id])
        ->callAction(TestAction::make('lock'), [
            'level' => 3,
            'reason' => 'unpaid invoice',
        ])
        ->assertNotified();

    // The change rides the next heartbeat as a signed directive: nothing is
    // pushed synchronously, and the enforced level holds until the site
    // confirms it back.
    Http::assertNothingSent();

    $website->refresh();

    expect($website->desired_lock_level)->toBe(3)
        ->and($website->lock_reason)->toBe('unpaid invoice')
        ->and($website->lock_level)->toBe(0)
        ->and($website->lockChangePending())->toBeTrue();
});

it('reports no change when the site is already at the desired level', function () {
    Http::fake();

    $website = Website::factory()->create(['enrolled_at' => now(), 'desired_lock_level' => 2, 'lock_level' => 2]);

    livewire(ViewWebsite::class, ['record' => $website->id])
        ->callAction(TestAction::make('lock'), [
            'level' => 2,
            'reason' => 'still unpaid',
        ])
        ->assertNotified();

    // Unchanged: lock_reason is not overwritten on a no-op.
    expect($website->refresh()->lock_reason)->toBeNull();
    Http::assertNothingSent();
});

it('records an activity log entry for the requested lock change', function () {
    $website = Website::factory()->create(['enrolled_at' => now(), 'lock_level' => 0, 'desired_lock_level' => 0]);

    livewire(ViewWebsite::class, ['record' => $website->id])
        ->callAction(TestAction::make('lock'), [
            'level' => 1,
            'reason' => 'deploy gate',
        ]);

    expect($website->activitiesAsSubject()->latest('id')->first()?->description)
        ->toContain('Requested lock level 1');
});

it('requires a reason in the lock action', function () {
    $website = Website::factory()->create(['enrolled_at' => now()]);

    livewire(ViewWebsite::class, ['record' => $website->id])
        ->callAction(TestAction::make('lock'), [
            'level' => 2,
            'reason' => null,
        ])
        ->assertHasActionErrors(['reason' => 'required']);
});
