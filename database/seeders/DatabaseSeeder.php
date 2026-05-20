<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        // Site settings (theme, colours, sections order, nav links)
        $this->call(SettingsSeeder::class);

        // Portfolio content
        $this->call(ProfileSeeder::class);
        $this->call(SkillSeeder::class);
        $this->call(ProjectSeeder::class);
        $this->call(ExperienceSeeder::class);
        $this->call(NowItemSeeder::class);

        // Sample contact messages
        $this->call(ContactMessageSeeder::class);

        // Admin user
        User::updateOrCreate(
            ['email' => 'florian@admin.dev'],
            [
                'name'     => 'Florian',
                'email'    => 'florian@admin.dev',
                'password' => Hash::make('password'),
            ]
        );
    }
}
