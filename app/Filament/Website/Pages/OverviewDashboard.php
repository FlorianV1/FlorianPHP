<?php

namespace App\Filament\Website\Pages;

use BackedEnum;
use Filament\Pages\Dashboard as BaseDashboard;
use Filament\Support\Enums\Width;

class OverviewDashboard extends BaseDashboard
{
    protected static string $routePath = '/';

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-home';

    protected static ?int $navigationSort = -10;

    protected static string|null|\UnitEnum $navigationGroup = 'Content';

    public function getHeading(): string
    {
        return '';
    }
    public function getColumns(): int | array
    {
        return [
            'default' => 1,
            'lg'      => 3,
            'xl'      => 3,
        ];
    }

    public function getMaxContentWidth(): Width | null | string
    {
        return Width::Full;
    }
}
