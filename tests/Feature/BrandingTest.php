<?php

use App\Models\Settings;
use App\Models\User;
use Filament\Facades\Filament;

/**
 * The FlorianPHP mark and wordmark. The mark is navy on a transparent
 * background, so anywhere it can land on a dark surface needs the lifted
 * variant — these tests pin that both exist and that both are actually wired
 * up, since a missing asset fails silently as a broken image.
 */
it('ships every brand asset it references', function (string $file) {
    expect(public_path("images/brand/{$file}"))->toBeFile();
})->with([
    'mark 512' => ['mark-512.png'],
    'icon 192' => ['icon-192.png'],
    'icon 192 dark' => ['icon-192-dark.png'],
    'favicon 32' => ['favicon-32.png'],
    'favicon 32 dark' => ['favicon-32-dark.png'],
    'apple touch icon' => ['apple-touch-icon.png'],
    'wordmark' => ['wordmark.png'],
    'wordmark white' => ['wordmark-white.png'],
    'lockup' => ['lockup.png'],
]);

it('gives every panel the brand logo, a dark variant and a favicon', function (string $panelId) {
    $panel = Filament::getPanel($panelId);

    expect($panel->getBrandLogo())->not->toBeNull()
        // Without this the navy mark disappears on a dark sidebar.
        ->and($panel->getDarkModeBrandLogo())->not->toBeNull()
        ->and($panel->getFavicon())->toContain('images/brand/favicon-32.png');
})->with([
    'portfolio' => ['portfolio'],
    'management' => ['management'],
]);

it('keeps the panel name in the logo so the panels stay tellable apart', function (string $panelId, string $expected) {
    $this->actingAs(User::factory()->admin()->create());

    $this->get("/{$panelId}")
        ->assertSuccessful()
        ->assertSee('images/brand/icon-192.png')
        ->assertSee($expected);
})->with([
    'portfolio' => ['portfolio', 'Portfolio'],
    'management' => ['management', 'Management'],
]);

it('falls back to the brand mark for the public favicon', function () {
    $this->get('/')
        ->assertSuccessful()
        ->assertSee('images/brand/favicon-32.png')
        ->assertSee('images/brand/apple-touch-icon.png')
        // The generic placeholder it used to fall back to.
        ->assertDontSee('favicon.svg');
});

it('lets a CMS favicon upload win over the brand mark', function () {
    Settings::set('favicon', 'uploads/custom-favicon.png');

    $this->get('/')
        ->assertSuccessful()
        ->assertSee('uploads/custom-favicon.png')
        // ...but the home-screen icon stays the mark either way.
        ->assertSee('images/brand/apple-touch-icon.png');
});
