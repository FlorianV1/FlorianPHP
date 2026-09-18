<?php

use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Filament\Auth\MultiFactor\App\AppAuthentication;
use Filament\Facades\Filament;
use Illuminate\Support\Facades\Hash;

/**
 * Panel access IS the authorization boundary in this app — there are no
 * per-resource policies yet, so anyone who reaches a panel has full CRUD
 * inside it. These tests pin that boundary, the MFA wiring, and the promise
 * that no seeder ever leaves a usable account behind by default.
 */
it('requires the admin flag on every panel', function (string $panelId) {
    $panel = Filament::getPanel($panelId);

    expect(User::factory()->create()->canAccessPanel($panel))->toBeFalse()
        ->and(User::factory()->admin()->create()->canAccessPanel($panel))->toBeTrue();
})->with([
    'portfolio' => ['portfolio'],
    'management' => ['management'],
]);

it('treats a missing is_admin column as not an admin', function () {
    // Filament calls canAccessPanel for every panel from the topbar, on
    // whatever model instance it has — including one hydrated without this
    // column. That must read as "no", never as a 500.
    $user = new User;

    expect($user->canAccessPanel(Filament::getDefaultPanel()))->toBeFalse();
});

it('locks a non-admin out of every panel over HTTP', function (string $path) {
    $this->actingAs(User::factory()->create());

    $this->get($path)->assertForbidden();
})->with([
    'portfolio' => ['/portfolio'],
    'management' => ['/management'],
]);

it('offers app-based multi-factor authentication with recovery codes', function (string $panelId) {
    $providers = Filament::getPanel($panelId)->getMultiFactorAuthenticationProviders();

    expect($providers)->not->toBeEmpty();

    $app = collect($providers)->first(fn ($provider): bool => $provider instanceof AppAuthentication);

    expect($app)->not->toBeNull()
        ->and($app->isRecoverable())->toBeTrue();
})->with([
    'portfolio' => ['portfolio'],
    'management' => ['management'],
]);

it('does not force multi-factor, so the only admin cannot be locked out', function () {
    expect(Filament::getDefaultPanel()->isMultiFactorAuthenticationRequired())->toBeFalse();
});

it('seeds no administrator when the env credentials are absent', function () {
    putenv('ADMIN_EMAIL');
    putenv('ADMIN_PASSWORD');

    $this->seed(DatabaseSeeder::class);

    expect(User::query()->where('is_admin', true)->count())->toBe(0);
});

it('seeds an administrator from the environment', function () {
    putenv('ADMIN_EMAIL=owner@example.test');
    putenv('ADMIN_PASSWORD=a-long-generated-password');
    putenv('ADMIN_NAME=Owner');

    $this->seed(DatabaseSeeder::class);

    $admin = User::query()->where('email', 'owner@example.test')->sole();

    expect($admin->is_admin)->toBeTrue()
        ->and($admin->name)->toBe('Owner')
        // Never stored in the clear.
        ->and($admin->password)->not->toBe('a-long-generated-password')
        ->and(Hash::check('a-long-generated-password', $admin->password))->toBeTrue();
})->after(function (): void {
    putenv('ADMIN_EMAIL');
    putenv('ADMIN_PASSWORD');
    putenv('ADMIN_NAME');
});

it('hides the bugsnag browser snippet until a key is configured', function () {
    config()->set('services.bugsnag.browser_key', null);

    $this->get('/')
        ->assertSuccessful()
        ->assertDontSee('bugsnag.min.js');
});

it('renders the bugsnag browser snippet with the configured key', function () {
    config()->set('services.bugsnag.browser_key', 'key-from-the-environment');

    $this->get('/')
        ->assertSuccessful()
        ->assertSee('bugsnag.min.js')
        ->assertSee('key-from-the-environment');
});

it('keeps the bugsnag browser key out of the blade layout', function () {
    $layout = file_get_contents(resource_path('views/components/layouts/portfolio.blade.php'));

    expect($layout)->toContain('services.bugsnag.browser_key')
        // The key that used to be hardcoded here.
        ->and($layout)->not->toContain('1d5f0db939c8f8209f8a37107ddd2f2a');
});

it('renders the authenticator set-up affordance on the profile page', function (string $path) {
    // Not merely "the page did not 500": the MFA columns are $hidden, so
    // attributesToArray() omits them and a careless read throws under strict
    // mode. Proving the set-up action is actually on the page is what shows
    // the defensive accessors did not simply hide broken MFA.
    $this->actingAs(User::factory()->admin()->create());

    $this->get($path)
        ->assertSuccessful()
        ->assertSee('Authenticator app');
})->with([
    'portfolio' => ['/portfolio/profile'],
    'management' => ['/management/profile'],
]);
