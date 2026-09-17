<?php

namespace App\Providers\Filament;

use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\NavigationGroup;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;

/**
 * Runs the public-facing site: portfolio content, contact messages and site
 * settings. The agency tooling lives in its own panel — see
 * ManagementPanelProvider.
 */
class WebsitePanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('website')
            ->path('website')
            ->brandName('Website')
            ->login()
            ->profile()
            ->sidebarCollapsibleOnDesktop()
            ->spa()
            ->colors([
                'primary' => Color::Blue,
            ])
            ->discoverResources(in: app_path('Filament/Website/Resources'), for: 'App\Filament\Website\Resources')
            ->discoverPages(in: app_path('Filament/Website/Pages'), for: 'App\Filament\Website\Pages')
            ->unsavedChangesAlerts()
            ->discoverWidgets(in: app_path('Filament/Website/Widgets'), for: 'App\Filament\Website\Widgets')
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                \Illuminate\View\Middleware\ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ])
            ->navigationGroups([
                NavigationGroup::make()
                    ->label('Content')
                    ->collapsible(false),
                NavigationGroup::make()
                    ->label('Portfolio')
                    ->collapsible(false),
            ]);
    }
}
