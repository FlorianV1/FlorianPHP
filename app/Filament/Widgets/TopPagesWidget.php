<?php

namespace App\Filament\Widgets;

use App\Models\PageView;
use Filament\Widgets\Widget;

class TopPagesWidget extends Widget
{
    protected static ?int $sort = 4;

    protected string $view = 'filament.widgets.top-pages';

    protected int | string | array $columnSpan = 1;

    public array $rows = [];

    public function mount(): void
    {
        $total = PageView::count();

        $this->rows = PageView::selectRaw('page, count(*) as views')
            ->groupBy('page')
            ->orderByDesc('views')
            ->limit(7)
            ->get()
            ->map(fn($row) => [
                'page'  => $row->page ?: '/',
                'views' => $row->views,
                'pct'   => $total > 0 ? round(($row->views / $total) * 100) : 0,
            ])
            ->toArray();
    }
}
