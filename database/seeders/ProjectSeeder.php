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
                'title' => 'Tuneroom',
                'description' => 'A shared music listening platform that lets you listen together with friends in real time — built better than what Spotify offers.',
                'impact' => 'Bringing synchronized listening to anyone, without the limitations of existing solutions.',
                'role' => 'Solo Developer',
                'project_type' => 'personal',
                'complexity' => 'high',
                'started_at' => '2026-03-01',
                'finished_at' => null,
                'is_ongoing' => true,
                'is_featured' => true,
                'is_posted' => true,
                'order' => 1,
                'responsibilities' => "Designing real-time sync architecture for shared playback\nBuilding Spotify integration via OAuth and Web Playback SDK\nDeveloping room and session management system\nCrafting the frontend listening experience",
                'languages' => ['PHP'],
                'tech_stack' => ['PHP', 'Laravel', 'Vue.js', 'WebSockets', 'Spotify API', 'MySQL', 'Redis'],
                'live_url' => null,
                'code_url' => null,
            ],
            [
                'title' => 'Coming Soon',
                'description' => 'Something new is in the works. More details will be revealed once Tuneroom ships.',
                'impact' => null,
                'role' => null,
                'project_type' => 'personal',
                'complexity' => null,
                'started_at' => null,
                'finished_at' => null,
                'is_ongoing' => false,
                'is_featured' => false,
                'is_posted' => true,
                'order' => 2,
                'responsibilities' => null,
                'languages' => [],
                'tech_stack' => [],
                'live_url' => null,
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
