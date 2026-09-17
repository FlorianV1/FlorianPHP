<?php

namespace App\Providers;

use App\Support\WindowsSafeFilesystem;
use BezhanSalleh\PanelSwitch\PanelSwitch;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Windows fails rename() when another process holds the destination
        // open, which happens whenever Filament's SPA fires parallel requests
        // that compile the same Blade view on a cold cache. `view:cache` does
        // not help — CLI and web hash view paths with different slashes.
        if (PHP_OS_FAMILY === 'Windows') {
            $this->app->singleton('files', fn (): WindowsSafeFilesystem => new WindowsSafeFilesystem);
        }
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->configurePanelSwitch();
    }

    /**
     * The switcher between the public-site panel and the agency back office.
     * It only appears once a user can reach both panels.
     */
    private function configurePanelSwitch(): void
    {
        PanelSwitch::configureUsing(function (PanelSwitch $panelSwitch): void {
            $panelSwitch
                ->modalHeading('Switch panel')
                ->slideOver()
                ->labels([
                    'website' => 'Website',
                    'management' => 'Management',
                ])
                ->icons([
                    'website' => 'heroicon-o-globe-alt',
                    'management' => 'heroicon-o-briefcase',
                ], asImage: false)
                ->iconSize(16);
        });
    }
}
