<?php

namespace App\Providers\Filament;

use Filament\Auth\MultiFactor\App\AppAuthentication;
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
use Illuminate\View\Middleware\ShareErrorsFromSession;

/**
 * The agency command center: clients, websites, invoicing and the
 * site-bridge health/lock tooling. Separate from the Website panel so the
 * portfolio content and the agency back office never share navigation.
 */
class ManagementPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('management')
            ->path('management')
            ->viteTheme('resources/css/filament/theme.css')
            ->brandName('Management')
            // One company mark across all panels; the panel name beside it is
            // what tells them apart. The mark is navy on transparent, so it
            // needs a lifted variant to stay visible on a dark sidebar.
            ->brandLogo(fn () => view('filament.brand', ['icon' => 'icon-192.png']))
            ->darkModeBrandLogo(fn () => view('filament.brand', ['icon' => 'icon-192-dark.png', 'dark' => true]))
            ->brandLogoHeight('1.75rem')
            ->favicon(asset('images/brand/favicon-32.png'))
            ->login()
            ->profile()
            // Authenticator-app MFA with recovery codes. Opt-in per user from the
            // profile page; not forced, so an admin cannot lock themselves out
            // of the only account.
            ->multiFactorAuthentication([
                AppAuthentication::make()->recoverable(),
            ])
            ->sidebarCollapsibleOnDesktop()
            ->spa()
            ->colors([
                'primary' => Color::Emerald,
            ])
            ->discoverResources(in: app_path('Filament/Management/Resources'), for: 'App\Filament\Management\Resources')
            ->discoverPages(in: app_path('Filament/Management/Pages'), for: 'App\Filament\Management\Pages')
            ->unsavedChangesAlerts()
            ->discoverWidgets(in: app_path('Filament/Management/Widgets'), for: 'App\Filament\Management\Widgets')
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
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
                    ->label('CRM')
                    ->collapsible(false),
                NavigationGroup::make()
                    ->label('Billing')
                    ->collapsible(false),
            ]);
    }
}
