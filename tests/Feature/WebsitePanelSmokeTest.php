<?php

use App\Models\User;

/**
 * Renders every page of the panel as an authenticated user. Thin, but it is
 * what catches a framework upgrade breaking a resource — the rest of the
 * suite never touches Filament.
 */
beforeEach(function (): void {
    $this->actingAs(User::factory()->create());
});

it('renders a panel page', function (string $path) {
    $this->get($path)->assertSuccessful();
})->with([
    'dashboard' => ['/website'],
    'projects' => ['/website/projects'],
    'projects/create' => ['/website/projects/create'],
    'experiences' => ['/website/experiences'],
    'experiences/create' => ['/website/experiences/create'],
    'skills' => ['/website/skills'],
    'skills/create' => ['/website/skills/create'],
    'now-items' => ['/website/now-items'],
    'now-items/create' => ['/website/now-items/create'],
    'messages' => ['/website/messages'],
    'site-settings' => ['/website/site-settings'],
    'profile-page' => ['/website/profile-page'],
    'profile' => ['/website/profile'],
]);
