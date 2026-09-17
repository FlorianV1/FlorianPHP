<?php

use App\Filament\Management\Widgets\LeadsOverviewWidget;
use App\Models\ContactMessage;
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

it('renders the management dashboard for an admin', function () {
    $this->actingAs(User::factory()->admin()->create());

    $this->get('/management')
        ->assertSuccessful()
        ->assertSee('Overview');
});

it('keeps the management dashboard away from non-admins', function () {
    $this->actingAs(User::factory()->create());

    $this->get('/management')->assertForbidden();
});

it('shows the inbound pipeline on the dashboard', function () {
    $this->actingAs(User::factory()->admin()->create());

    ContactMessage::create([
        'name' => 'Jane Prospect',
        'email' => 'jane@example.com',
        'message' => 'We need a booking platform.',
    ]);

    ContactMessage::create([
        'name' => 'Spam Bot',
        'email' => 'bot@example.com',
        'message' => 'Cheap pills.',
        'is_spam' => true,
    ]);

    $page = $this->get('/management');

    $page->assertSuccessful()
        ->assertSee('Unread leads')
        ->assertSee('Spam blocked')
        ->assertSee('Latest enquiries');

    // The quarantined message must be counted, never listed.
    $page->assertDontSee('Spam Bot');
});

it('does not leak management widgets into the website panel', function () {
    $websiteWidgets = filament()->getPanel('website')->getWidgets();

    expect($websiteWidgets)->not->toContain(LeadsOverviewWidget::class);
});
