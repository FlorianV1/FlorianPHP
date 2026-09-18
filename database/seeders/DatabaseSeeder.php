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

        $this->seedAdministrator();
    }

    /**
     * The one account that can reach any panel.
     *
     * Credentials come from the environment and are never defaulted: a
     * hardcoded password in a seeder becomes a live production account the
     * moment someone runs `db:seed` on a server. When either variable is
     * missing the seeder says so and moves on, leaving no account behind.
     */
    private function seedAdministrator(): void
    {
        $email = env('ADMIN_EMAIL');
        $password = env('ADMIN_PASSWORD');

        if (blank($email) || blank($password)) {
            $this->command?->warn(
                'Skipped the administrator: set ADMIN_EMAIL and ADMIN_PASSWORD to seed one.'
            );

            return;
        }

        User::updateOrCreate(
            ['email' => $email],
            [
                'name' => env('ADMIN_NAME', 'Administrator'),
                'password' => Hash::make($password),
                'is_admin' => true,
            ],
        );

        $this->command?->info("Administrator ready: {$email}");
    }
}
