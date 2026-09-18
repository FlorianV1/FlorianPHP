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
 * The CMS behind this site's own public pages: projects, skills, experience,
 * services, testimonials and the appearance settings.
 *
 * It is separate from the Website panel because that one is becoming
 * multi-tenant — one tenant per client site — and none of this content means
 * anything for a site that is not this one. It is the default panel for the
 * same reason: a tenant-scoped panel cannot resolve URLs without a tenant.
 */
class PortfolioPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('portfolio')
            ->path('portfolio')
            ->viteTheme('resources/css/filament/theme.css')
            ->brandName('Portfolio')
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
                'primary' => Color::Violet,
            ])
            ->discoverResources(in: app_path('Filament/Portfolio/Resources'), for: 'App\Filament\Portfolio\Resources')
            ->discoverPages(in: app_path('Filament/Portfolio/Pages'), for: 'App\Filament\Portfolio\Pages')
            ->unsavedChangesAlerts()
            ->discoverWidgets(in: app_path('Filament/Portfolio/Widgets'), for: 'App\Filament\Portfolio\Widgets')
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
                    ->label('Content')
                    ->collapsible(false),
                NavigationGroup::make()
                    ->label('Portfolio')
                    ->collapsible(false),
            ]);
    }
}
