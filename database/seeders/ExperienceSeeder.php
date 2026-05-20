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
                'title'           => 'Lead Software Developer',
                'company'         => 'BingoMC',
                'company_url'     => 'https://bingomc.net',
                'company_logo'    => null,
                'location'        => 'Remote',
                'employment_type' => 'self-employed',
                'started_at'      => '2023-06-01',
                'ended_at'        => null,
                'is_current'      => true,
                'description'     => 'Building and maintaining a Minecraft minigame platform from the ground up — game server logic, web backend, and community tooling.',
                'responsibilities'=> [
                    ['responsibility' => 'Designed and implemented all core minigame mechanics in Java and PHP'],
                    ['responsibility' => 'Built a real-time leaderboard, player ranking, and economy system'],
                    ['responsibility' => 'Managed Linux server infrastructure and Docker-based deployment pipeline'],
                    ['responsibility' => 'Grew the platform to 18k+ monthly active players and 2.2k+ Discord members'],
                    ['responsibility' => 'Handled all community management, feature prioritisation, and roadmap planning'],
                ],
                'skills'          => ['PHP', 'Laravel', 'Java', 'MySQL', 'Redis', 'Docker', 'Linux'],
                'order'           => 1,
                'is_active'       => true,
            ],
            [
                'title'           => 'Freelance Web Developer',
                'company'         => 'Freelance',
                'company_url'     => null,
                'company_logo'    => null,
                'location'        => 'Remote',
                'employment_type' => 'freelance',
                'started_at'      => '2022-09-01',
                'ended_at'        => '2023-05-01',
                'is_current'      => false,
                'description'     => 'Delivered custom PHP/Laravel web applications and REST APIs for small businesses and startups across the Netherlands.',
                'responsibilities'=> [
                    ['responsibility' => 'Built full-stack web applications with Laravel and Vue.js'],
                    ['responsibility' => 'Developed REST APIs consumed by mobile and SPA clients'],
                    ['responsibility' => 'Integrated third-party services including payment gateways and email providers'],
                    ['responsibility' => 'Maintained and refactored legacy PHP codebases'],
                ],
                'skills'          => ['PHP', 'Laravel', 'Vue.js', 'MySQL', 'REST APIs', 'Git'],
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
