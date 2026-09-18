<?php

use App\Models\User;
use Filament\Facades\Filament;

/**
 * Renders every page of the Portfolio panel — the CMS behind this site's own
 * public pages. Split out of the Website panel so that one can become
 * multi-tenant without dragging content that only ever applies to this site.
 */
beforeEach(function (): void {
    $this->actingAs(User::factory()->admin()->create());
});

it('renders a panel page', function (string $path) {
    $this->get($path)->assertSuccessful();
})->with([
    'dashboard' => ['/portfolio'],
    'messages' => ['/portfolio/messages'],
    'projects' => ['/portfolio/projects'],
    'projects/create' => ['/portfolio/projects/create'],
    'experiences' => ['/portfolio/experiences'],
    'experiences/create' => ['/portfolio/experiences/create'],
    'skills' => ['/portfolio/skills'],
    'skills/create' => ['/portfolio/skills/create'],
    'now-items' => ['/portfolio/now-items'],
    'now-items/create' => ['/portfolio/now-items/create'],
    'services' => ['/portfolio/services'],
    'services/create' => ['/portfolio/services/create'],
    'testimonials' => ['/portfolio/testimonials'],
    'testimonials/create' => ['/portfolio/testimonials/create'],
    'stats' => ['/portfolio/stats'],
    'stats/create' => ['/portfolio/stats/create'],
    'site-settings' => ['/portfolio/site-settings'],
    'profile-page' => ['/portfolio/profile-page'],
    'profile' => ['/portfolio/profile'],
]);

it('is the default panel, so URL generation needs no tenant', function () {
    expect(Filament::getDefaultPanel()->getId())->toBe('portfolio');
});

it('has exactly two panels, kept separate', function () {
    $panels = array_keys(Filament::getPanels());

    // This repo holds the company site and the command centre, nothing else.
    expect($panels)->toEqualCanonicalizing(['portfolio', 'management'])
        ->and(array_intersect(
            Filament::getPanel('portfolio')->getResources(),
            Filament::getPanel('management')->getResources(),
        ))->toBeEmpty();
});
