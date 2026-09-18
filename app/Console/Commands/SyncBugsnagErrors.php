<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Bugsnag\BugsnagSync;
use Illuminate\Console\Command;

/**
 * Pulls the open-error count for every linked website. Runs hourly; a site
 * whose pull fails keeps its last known count and records why.
 */
final class SyncBugsnagErrors extends Command
{
    protected $signature = 'bugsnag:sync-errors';

    protected $description = 'Pull open Bugsnag error counts for every linked website';

    public function handle(BugsnagSync $sync): int
    {
        if (! $sync->isConfigured()) {
            $this->components->warn('Bugsnag is not configured — set BUGSNAG_AUTH_TOKEN to enable it.');

            return self::SUCCESS;
        }

        $result = $sync->syncAll();

        $this->components->info("Synced {$result['synced']} site(s), {$result['failed']} failed.");

        return self::SUCCESS;
    }
}
