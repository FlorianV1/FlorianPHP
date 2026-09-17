<?php

namespace App\Filament\Website\Widgets;

use App\Models\PageView;
use Filament\Widgets\Widget;

class TopCountriesWidget extends Widget
{
    protected static bool $isDiscovered = false;

    protected static ?int $sort = 5;

    protected string $view = 'filament.widgets.top-countries';

    protected int | string | array $columnSpan = 1;

    public array $rows = [];

    public function mount(): void
    {
        $total = PageView::count();

        $known = PageView::whereNotNull('country')
            ->selectRaw('country, count(*) as views')
            ->groupBy('country')
            ->orderByDesc('views')
            ->limit(6)
            ->get()
            ->map(fn($row) => [
                'country' => $row->country,
                'views'   => $row->views,
                'pct'     => $total > 0 ? round(($row->views / $total) * 100) : 0,
            ])
            ->toArray();

        $unknown = PageView::whereNull('country')->count();
        if ($unknown > 0) {
            $known[] = [
                'country' => 'Unknown',
                'views'   => $unknown,
                'pct'     => $total > 0 ? round(($unknown / $total) * 100) : 0,
            ];
        }

        $this->rows = $known;
    }
}
