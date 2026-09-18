<?php

declare(strict_types=1);

namespace Agency\SiteBridge;

use Agency\SiteBridge\Commands\ClaimCommand;
use Agency\SiteBridge\Commands\DeployCheckCommand;
use Agency\SiteBridge\Commands\HeartbeatCommand;
use Agency\SiteBridge\Http\Middleware\EnforceLock;
use Agency\SiteBridge\Support\LockState;
use Illuminate\Console\Events\CommandStarting;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Contracts\Http\Kernel;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\ServiceProvider;
use RuntimeException;

final class SiteBridgeServiceProvider extends ServiceProvider
{
    /**
     * Commands that must not run while the site is hard-locked (level 3).
     */
    private const GUARDED_COMMANDS = ['schedule:run', 'schedule:work', 'queue:work', 'queue:listen'];

    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/site-bridge.php', 'site-bridge');
    }

    public function boot(): void
    {
        $this->publishes([
            __DIR__.'/../config/site-bridge.php' => config_path('site-bridge.php'),
        ], 'site-bridge-config');

        $this->publishes([
            __DIR__.'/../database/migrations' => database_path('migrations'),
        ], 'site-bridge-migrations');

        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');

        $this->loadViewsFrom(__DIR__.'/../resources/views', 'site-bridge');

        $this->app->make(Kernel::class)->pushMiddleware(EnforceLock::class);

        // Level 3: pause queue daemons (they stop popping jobs on the next
        // loop) and refuse to start workers or the scheduler.
        Queue::looping(fn (): bool => $this->app->make(LockState::class)->level() < 3);

        Event::listen(CommandStarting::class, function (CommandStarting $event): void {
            if (! in_array($event->command, self::GUARDED_COMMANDS, true)) {
                return;
            }

            if ($this->app->make(LockState::class)->level() >= 3) {
                throw new RuntimeException(
                    "site-bridge: application is locked at level 3 — {$event->command} is disabled until the lock is lifted.",
                );
            }
        });

        if ($this->app->runningInConsole()) {
            $this->commands([
                DeployCheckCommand::class,
                ClaimCommand::class,
                HeartbeatCommand::class,
            ]);

            // Runs every minute; the command itself decides whether a send is
            // due, giving an adaptive cadence without two schedule entries.
            $this->app->booted(function (): void {
                $this->app->make(Schedule::class)
                    ->command('site-bridge:heartbeat')
                    ->everyMinute()
                    ->withoutOverlapping();
            });
        }
    }
}
