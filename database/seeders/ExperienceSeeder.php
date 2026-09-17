<?php

namespace Database\Seeders;

use App\Models\Experience;
use Illuminate\Database\Seeder;

class ExperienceSeeder extends Seeder
{
    public function run(): void
    {
        $experiences = [
            [
                'title'           => 'Junior Software Developer',
                'company'         => 'TFC',
                'company_url'     => null,
                'company_logo'    => null,
                'location'        => 'Breda, Netherlands',
                'employment_type' => 'full-time',
                'started_at'      => '2025-12-01',
                'ended_at'        => null,
                'is_current'      => true,
                'description'     => "I work at TFC as a Software Developer, where I build internal tools and backend dashboards using Laravel and Filament. I'm part of a team of four developers, with our current focus on the backend dashboard side of things. Alongside development, I also handle some support responsibilities.",
                'responsibilities' => [
                    ['responsibility' => 'Build internal tools and backend dashboards with Laravel and Filament'],
                    ['responsibility' => 'Work in a team of four developers, focused on the backend dashboard side'],
                    ['responsibility' => 'Handle support responsibilities alongside development'],
                ],
                'skills'          => ['Laravel', 'FilamentPHP', 'IT Support', 'Tailwind'],
                'order'           => 1,
                'is_active'       => true,
            ],
            [
                'title'           => 'Software Developer | Administrator',
                'company'         => 'BingoMC',
                'company_url'     => 'https://bingomc.net',
                'company_logo'    => null,
                'location'        => 'Remote',
                'employment_type' => 'part-time',
                'started_at'      => '2023-06-01',
                'ended_at'        => null,
                'is_current'      => true,
                'description'     => 'Building and maintaining a Minecraft minigame platform from the ground up — game server logic, web backend, and community tooling.',
                'responsibilities' => [
                    ['responsibility' => 'Built a real-time leaderboard, player ranking, and economy system'],
                    ['responsibility' => 'Grew the platform to 18k+ monthly active players and 2.2k+ Discord members'],
                    ['responsibility' => 'Handled all community management, feature prioritisation, and roadmap planning'],
                ],
                'skills'          => ['PHP', 'Laravel', 'Java', 'MySQL', 'Redis', 'Docker'],
                'order'           => 2,
                'is_active'       => true,
            ],
        ];

        foreach ($experiences as $data) {
            Experience::updateOrCreate(
                ['title' => $data['title'], 'company' => $data['company']],
                $data
            );
        }
    }
}
