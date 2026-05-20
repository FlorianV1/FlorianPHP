<?php

namespace Database\Seeders;

use App\Models\Profile;
use Illuminate\Database\Seeder;

class ProfileSeeder extends Seeder
{
    public function run(): void
    {
        Profile::updateOrCreate(
            ['id' => 1],
            [
                'name' => 'Florian',
                'role' => 'Software Developer',
                'tagline' => 'I build web applications with a focus on reliability, performance, and clear code.',
                'subtitle' => 'Currently building portfolio tooling with Laravel & Filament.',
                'status_text' => 'available for projects',
                'status_available' => true,
                'email' => 'florian.geense@gmail.com',
                'location' => 'Netherlands',
                'location_timezone' => 'UTC+1 / CET',

                'hero_cta_primary_label' => 'View my work',
                'hero_cta_primary_url' => '#projects',
                'hero_cta_secondary_label' => 'Get in touch',
                'hero_cta_secondary_url' => '#contact',

                'contact_intro' => "If you want to talk about work, collaboration, or just an idea — I'm all ears. I typically respond within 24 hours.",

                'stat_1_value' => '2.0',
                'stat_1_label' => 'Current Projects',
                'stat_2_value' => '1+ year',
                'stat_2_label' => 'FilamentPHP Expierence',
                'stat_3_value' => '2+ year',
                'stat_3_label' => 'Development experience',

                'about_stack_primary' => 'PHP / Laravel',
                'about_stack_secondary' => '+ FilamentPHP, MySQL, Tailwind',

                'footer_tagline' => 'Built with Laravel & love.',

                'about_text' => '<p>I\'m a <strong>Software Developer</strong> based in the Netherlands, focused on building web applications that are fast, reliable, and a pleasure to maintain. I care deeply about clean architecture and writing code that other developers can actually understand.</p><p>My primary stack is <strong>PHP / Laravel</strong>, paired with Vue.js on the frontend and MySQL, Redis, and Docker in the backend. I enjoy the full cycle — from database design and API development through to polished UIs.</p>',

                'social_links' => [
                    ['platform' => 'GitHub', 'url' => 'https://github.com/FlorianV1'],
                    ['platform' => 'LinkedIn', 'url' => 'https://linkedin.com/in/florian'],
                    ['platform' => 'Discord', 'url' => 'https://discord.com/users/1138188681308540938'],
                ],
            ]
        );
    }
}
