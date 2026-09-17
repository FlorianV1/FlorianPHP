<?php

namespace Database\Seeders;

use App\Models\Project;
use Illuminate\Database\Seeder;

class ProjectSeeder extends Seeder
{
    public function run(): void
    {
        $projects = [
            [
                'title' => 'BingoMC',
                'description' => 'A Minecraft minigame server platform with custom game logic, real-time leaderboards, player progression, and an economy system serving thousands of concurrent players.',
                'impact' => 'Scaled to 18k+ monthly active players with sub-100ms game tick latency.',
                'role' => 'Lead Developer',
                'project_type' => 'personal',
                'complexity' => 'high',
                'started_at' => '2023-06-01',
                'finished_at' => null,
                'is_ongoing' => true,
                'is_featured' => true,
                'is_posted' => true,
                'order' => 1,
                'responsibilities' => "Designed and implemented all core game mechanics\nBuilt real-time leaderboard and player ranking system\nCreated custom economy and progression system\nManaged server infrastructure with Docker and Linux",
                'languages' => ['Java', 'PHP'],
                'tech_stack' => ['PHP', 'Laravel', 'MySQL', 'Redis', 'Docker', 'Linux'],
                'live_url' => 'https://bingomc.net',
                'code_url' => null,
            ],
            [
                'title' => 'Techno Events',
                'description' => "Technoevents.nl is an aggregator for the Dutch techno scene. It automatically scrapes upcoming events from organizers' own websites — dates, venues, ticket links, flyers — into one browsable agenda, and reads each flyer to pull out the lineup so you can find events by artist.",
                'impact' => 'One agenda for the Dutch techno scene, searchable by artist rather than by organizer.',
                'role' => 'Solo Developer',
                'project_type' => 'personal',
                'complexity' => 'high',
                'started_at' => '2026-01-01',
                'finished_at' => null,
                'is_ongoing' => true,
                'is_featured' => true,
                'is_posted' => true,
                'order' => 2,
                'responsibilities' => "Built scrapers that pull events from organizers' own websites\nExtracted lineups from event flyers so events are searchable by artist\nDesigned the browsable agenda frontend\nBuilt the Filament admin for curating events and venues",
                'languages' => ['PHP'],
                'tech_stack' => ['PHP', 'Laravel', 'MySQL', 'FilamentPHP', 'Livewire'],
                'live_url' => 'https://technoevents.nl/',
                'code_url' => null,
            ],
            [
                'title' => 'Roadtrip-events.nl',
                'description' => 'Roadtrip Events is a custom web platform for a company that organises curated road trip experiences in the Netherlands and abroad. Full application in Laravel: public-facing website for browsing destinations and booking trips, plus Filament-powered admin panel for managing events, bookings and customers.',
                'impact' => 'Replaced a manual booking process with a self-service platform the client administers themselves.',
                'role' => 'Solo Developer',
                'project_type' => 'client',
                'complexity' => 'high',
                'started_at' => '2025-06-01',
                'finished_at' => null,
                'is_ongoing' => true,
                'is_featured' => true,
                'is_posted' => true,
                'order' => 3,
                'responsibilities' => "Built the public site for browsing destinations and booking trips\nBuilt the Filament admin for events, bookings and customers\nIntegrated Mailcoach for customer mailings\nDeployed and maintained the platform on Laravel Forge",
                'languages' => ['PHP'],
                'tech_stack' => ['FilamentPHP', 'Livewire', 'Laravel', 'mailcoach', 'Laravel Forge'],
                'live_url' => 'https://roadtrip-events.nl/',
                'code_url' => null,
            ],
        ];

        foreach ($projects as $data) {
            Project::updateOrCreate(
                ['title' => $data['title']],
                $data
            );
        }
    }
}
