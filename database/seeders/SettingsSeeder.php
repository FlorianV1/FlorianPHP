<?php

namespace Database\Seeders;

use App\Models\Setting;
use App\Models\Settings;
use Illuminate\Database\Seeder;

class SettingsSeeder extends Seeder
{
    public function run(): void
    {
        $defaults = [
            'theme' => 'default',
            'custom_colors' => [
                'app_bg' => '#0E0E10',
                'surface' => '#111418',
                'accent' => '#4A9FFF',
                'accent_hover' => '#2D7CE8',
                'text_primary' => '#E7EAF0',
                'text_secondary' => '#A8ACB3',
                'text_muted' => '#6F737A',
            ],
            'overlay' => 'none',
            'overlay_intensity' => 50,
            'sections_order' => [
                ['section' => 'hero', 'enabled' => true],
                ['section' => 'now', 'enabled' => true],
                ['section' => 'projects', 'enabled' => true],
                ['section' => 'experience', 'enabled' => true],
                ['section' => 'skills', 'enabled' => true],
                ['section' => 'about', 'enabled' => true],
                ['section' => 'contact', 'enabled' => true],
            ],
            'navbar_links' => [
                ['label' => 'Projects', 'url' => '#projects', 'enabled' => true],
                ['label' => 'Experience', 'url' => '#experience', 'enabled' => true],
                ['label' => 'Skills', 'url' => '#skills', 'enabled' => true],
                ['label' => 'About', 'url' => '#about', 'enabled' => true],
                ['label' => 'Contact', 'url' => '#contact', 'enabled' => true],
            ],
        ];

        foreach ($defaults as $key => $value) {
            Settings::set($key, $value);
        }
    }
}
