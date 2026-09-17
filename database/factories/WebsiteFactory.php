<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\WebsiteEnvironment;
use App\Models\Client;
use App\Models\Website;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Website>
 */
final class WebsiteFactory extends Factory
{
    protected $model = Website::class;

    public function definition(): array
    {
        $domain = fake()->unique()->slug(2).'.example';

        return [
            'client_id' => Client::factory(),
            'bridge_site_id' => (string) Str::uuid(),
            'label' => Str::title(Str::before($domain, '.')),
            'url' => "https://{$domain}",
            'environment' => WebsiteEnvironment::Production,
            'tech_stack_notes' => fake()->randomElement([
                'Laravel 12, Filament 4, MySQL 8, Redis',
                'Laravel 11, Livewire 3, MariaDB',
                'Laravel 12, Inertia + Vue 3, PostgreSQL',
            ]),
            'hosting_provider' => fake()->randomElement(['Hetzner', 'DigitalOcean', 'TransIP', 'Forge + AWS']),
            'server_host' => fake()->ipv4(),
            'repository_url' => 'https://github.com/agency/'.Str::slug($domain),
            'management_url' => "https://{$domain}/admin",
            'mailcoach_url' => fake()->boolean(60) ? "https://mail.{$domain}" : null,
            'bugsnag_project_url' => fake()->boolean(70) ? 'https://app.bugsnag.com/agency/'.Str::slug($domain) : null,
            'bugsnag_project_key' => fake()->boolean(70) ? Str::random(32) : null,
            'bugsnag_project_id' => null,
            'bugsnag_open_errors' => null,
            'bugsnag_synced_at' => null,
            'bugsnag_sync_error' => null,
            'lock_level' => 0,
            'desired_lock_level' => 0,
            'lock_reason' => null,
            'directive_seq' => 0,
            'last_seen_at' => null,
            'last_heartbeat_at' => null,
            'health_alerted_at' => null,
            'enrolled_at' => null,
            'health' => null,
        ];
    }

    public function staging(): self
    {
        return $this->state(['environment' => WebsiteEnvironment::Staging]);
    }
}
