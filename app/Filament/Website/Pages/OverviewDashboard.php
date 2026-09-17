<?php

namespace App\Filament\Website\Pages;

use BackedEnum;
use Filament\Pages\Dashboard as BaseDashboard;
use Filament\Support\Enums\Width;
use JohnRivera7\FilamentWidgetGrid\Concerns\HasWidgetGrid;

class OverviewDashboard extends BaseDashboard
{
    // Makes this dashboard draggable/resizable instead of the fixed column
    // grid. The plugin ships its own Dashboard page, but this panel already
    // had a custom one, so it takes the concern directly.
    use HasWidgetGrid;

    protected static string $routePath = '/';

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-home';

    protected static ?int $navigationSort = -10;

    protected static string|null|\UnitEnum $navigationGroup = 'Content';

    public function getHeading(): string
    {
        return '';
    }

    public function getColumns(): int|array
    {
        return [
            'default' => 1,
            'lg' => 3,
            'xl' => 3,
        ];
    }

    public function getMaxContentWidth(): Width|null|string
    {
        return Width::Full;
    }
}
