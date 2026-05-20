<?php

namespace App\Filament\Widgets;

use App\Models\ContactMessage;
use App\Models\Experience;
use App\Models\Project;
use App\Models\Skill;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class ContentOverviewWidget extends BaseWidget
{
    protected static ?int $sort = 3;

    protected int | string | array $columnSpan = 'full';

    protected function getStats(): array
    {
        $totalProjects    = Project::count();
        $publishedProjects = Project::posted()->count();
        $totalMessages    = ContactMessage::count();
        $unreadMessages   = ContactMessage::unread()->count();

        return [
            Stat::make('Projects', "{$publishedProjects} / {$totalProjects}")
                ->description('published / total')
                ->descriptionIcon('heroicon-m-folder')
                ->color('primary'),

            Stat::make('Experiences', Experience::count())
                ->description('work entries')
                ->descriptionIcon('heroicon-m-briefcase')
                ->color('info'),

            Stat::make('Skills', Skill::count())
                ->description('technologies listed')
                ->descriptionIcon('heroicon-m-cpu-chip')
                ->color('success'),

            Stat::make('Messages', "{$unreadMessages} unread")
                ->description("{$totalMessages} total received")
                ->descriptionIcon('heroicon-m-inbox')
                ->color($unreadMessages > 0 ? 'danger' : 'gray'),
        ];
    }
}
