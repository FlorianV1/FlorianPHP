<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Bugsnag\BugsnagApiException;
use App\Bugsnag\BugsnagSync;
use Illuminate\Console\Command;

/**
 * Maps each website to its Bugsnag project so the hub knows what to pull.
 * Occasional by nature — run it after adding a site or a Bugsnag project.
 */
final class LinkBugsnagProjects extends Command
{
    protected $signature = 'bugsnag:link-projects {--relink : Re-match sites that are already linked}';

    protected $description = 'Match every website to its Bugsnag project';

    public function handle(BugsnagSync $sync): int
    {
        if (! $sync->isConfigured()) {
            $this->components->warn('Bugsnag is not configured — set BUGSNAG_AUTH_TOKEN to enable it.');

            return self::SUCCESS;
        }

        try {
            $result = $sync->linkProjects(relink: (bool) $this->option('relink'));
        } catch (BugsnagApiException $exception) {
            $this->components->error($exception->getMessage());

            return self::FAILURE;
        }

        foreach ($result['linked'] as $line) {
            $this->components->twoColumnDetail($line, '<fg=green>linked</>');
        }

        foreach ($result['unmatched'] as $label) {
            $this->components->twoColumnDetail($label, '<fg=yellow>no project matched</>');
        }

        $this->newLine();
        $this->components->info(sprintf(
            'Linked %d site(s), %d unmatched.',
            count($result['linked']),
            count($result['unmatched']),
        ));

        return self::SUCCESS;
    }
}
