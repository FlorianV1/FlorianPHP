<?php

use App\Providers\AppServiceProvider;
use App\Providers\Filament\ManagementPanelProvider;
use App\Providers\Filament\PortfolioPanelProvider;
use Bugsnag\BugsnagLaravel\BugsnagServiceProvider;

return [
    BugsnagServiceProvider::class,
    AppServiceProvider::class,
    PortfolioPanelProvider::class,
    ManagementPanelProvider::class,
];
