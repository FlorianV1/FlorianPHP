<?php

use App\Models\User;

/**
 * The Management panel is being populated with the agency command center.
 * For now this proves the panel itself is registered and reachable, and
 * that it is kept separate from the Website panel's navigation.
 */
beforeEach(function (): void {
    $this->actingAs(User::factory()->create());
});

it('registers the management panel', function () {
    expect(filament()->getPanel('management'))->not->toBeNull()
        ->and(filament()->getPanel('management')->getPath())->toBe('management');
});

it('keeps the two panels separate', function () {
    $websiteResources = filament()->getPanel('website')->getResources();
    $managementResources = filament()->getPanel('management')->getResources();

    expect(array_intersect($websiteResources, $managementResources))->toBeEmpty();
});

it('serves the management login to a guest', function () {
    auth()->logout();

    $this->get('/management/login')->assertSuccessful();
});

it('keeps the management panel behind auth', function () {
    auth()->logout();

    $this->get('/management')->assertRedirect('/management/login');
});
