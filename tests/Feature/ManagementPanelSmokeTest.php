<?php

use App\Filament\Management\Pages\ManagementDashboard;
use App\Filament\Management\Widgets\LeadsOverviewWidget;
use App\Filament\Management\Widgets\LeadsTrendChart;
use App\Filament\Management\Widgets\RecentLeadsWidget;
use App\Models\ContactMessage;
use App\Models\User;
use Livewire\Livewire;

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
    $portfolioResources = filament()->getPanel('portfolio')->getResources();
    $managementResources = filament()->getPanel('management')->getResources();

    expect(array_intersect($portfolioResources, $managementResources))->toBeEmpty();
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

    // Widgets lazy-load, so the dashboard response carries placeholders and
    // the real content arrives over Livewire. Assert the wiring on the page
    // and the numbers on the components themselves.
    $this->get('/management')->assertSuccessful();

    expect(Livewire::test(ManagementDashboard::class)->instance()->getWidgets())
        ->toContain(LeadsOverviewWidget::class, LeadsTrendChart::class, RecentLeadsWidget::class);

    Livewire::test(LeadsOverviewWidget::class)
        ->assertSee('Unread leads')
        ->assertSee('Spam blocked');

    // The quarantined message must be counted, never listed.
    Livewire::test(RecentLeadsWidget::class)
        ->assertSee('Latest enquiries')
        ->assertSee('Jane Prospect')
        ->assertDontSee('Spam Bot');
});

it('does not leak management widgets into the website panel', function () {
    $portfolioWidgets = filament()->getPanel('portfolio')->getWidgets();

    expect($portfolioWidgets)->not->toContain(LeadsOverviewWidget::class);
});
