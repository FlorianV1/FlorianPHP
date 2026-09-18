<?php

namespace Database\Seeders;

use App\Models\NowItem;
use Illuminate\Database\Seeder;

class NowItemSeeder extends Seeder
{
    public function run(): void
    {
        NowItem::truncate();

        $items = [
            [
                'description' => 'Starting up my own Software Company',
                'order'       => 1,
                'is_active'   => true,
            ],
            [
                'description' => 'Scaling BingoMC — improving game performance and adding new minigame modes.',
                'order'       => 2,
                'is_active'   => true,
            ],
            [
                'description' => 'Working on Tuneroom — a shared music listening experience, built better than Spotify\'s version',
                'order'       => 3,
                'is_active'   => true,
            ],
            [
                'description' => 'Building this portfolio CMS with Laravel, Filament v4, and a fully custom Blade frontend.',
                'order'       => 4,
                'is_active'   => true,
            ],
        ];

        foreach ($items as $item) {
            NowItem::create($item);
        }
    }
}
