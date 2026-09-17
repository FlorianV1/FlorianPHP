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
use Illuminate\View\Middleware\ShareErrorsFromSession;
use JohnRivera7\FilamentWidgetGrid\FilamentWidgetGridPlugin;
use pxlrbt\FilamentEnvironmentIndicator\EnvironmentIndicatorPlugin;

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
            ->viteTheme('resources/css/filament/website/theme.css')
            ->brandName('Management')
            ->login()
            ->profile()
            ->sidebarCollapsibleOnDesktop()
            ->spa()
            ->colors([
                'primary' => Color::Emerald,
            ])
            ->discoverResources(in: app_path('Filament/Management/Resources'), for: 'App\Filament\Management\Resources')
            ->discoverPages(in: app_path('Filament/Management/Pages'), for: 'App\Filament\Management\Pages')
            ->unsavedChangesAlerts()
            ->discoverWidgets(in: app_path('Filament/Management/Widgets'), for: 'App\Filament\Management\Widgets')
            ->plugins([
                EnvironmentIndicatorPlugin::make(),
                FilamentWidgetGridPlugin::make(),
            ])
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
                    ->label('Clients')
                    ->collapsible(false),
                NavigationGroup::make()
                    ->label('Billing')
                    ->collapsible(false),
            ]);
    }
}
