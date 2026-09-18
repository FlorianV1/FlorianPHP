<?php

declare(strict_types=1);

namespace Agency\SiteBridge\Commands;

use Agency\SiteBridge\Support\LockState;
use Illuminate\Console\Command;

/**
 * CI/deploy gate: exits non-zero when the site is locked at level 1 or
 * above, so a deploy pipeline can simply run it as a step.
 */
final class DeployCheckCommand extends Command
{
    protected $signature = 'site-bridge:deploy-check';

    protected $description = 'Exit non-zero when the site-bridge lock blocks deploys (level >= 1)';

    public function handle(LockState $lockState): int
    {
        $lock = $lockState->describe();

        if ($lock['lock_level'] >= 1) {
            $this->error(sprintf(
                'Deploys blocked: lock level %d%s',
                $lock['lock_level'],
                filled($lock['reason']) ? " — {$lock['reason']}" : '',
            ));

            return self::FAILURE;
        }

        $this->info('Deploy gate open (lock level 0).');

        return self::SUCCESS;
    }
}
