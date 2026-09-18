<?php

declare(strict_types=1);

namespace Agency\SiteBridge\Collectors;

use Agency\SiteBridge\Services\BugsnagMetrics;
use Agency\SiteBridge\Services\MailcoachMetrics;
use Agency\SiteBridge\Support\Section;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Spatie\Backup\BackupServiceProvider;

final readonly class MetricsCollector
{
    public function __construct(
        private BugsnagMetrics $bugsnag,
        private MailcoachMetrics $mailcoach,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function collect(): array
    {
        return [
            'bridge_version' => 1,
            'failed_jobs' => Section::attempt(fn (): Section => $this->failedJobs())->toArray(),
            'pending_jobs' => Section::attempt(fn (): Section => $this->pendingJobs())->toArray(),
            'last_backup_at' => Section::attempt(fn (): Section => $this->lastBackup())->toArray(),
            'database_size_bytes' => Section::attempt(fn (): Section => $this->databaseSize())->toArray(),
            'disk_free_bytes' => Section::attempt(fn (): Section => $this->diskFree())->toArray(),
            'schedule_last_run_at' => Section::unavailable('not determinable on this runtime')->toArray(),
            'mailcoach' => $this->mailcoach->metrics()->toArray(),
            'bugsnag' => $this->bugsnag->metrics()->toArray(),
        ];
    }

    private function failedJobs(): Section
    {
        if (! Schema::hasTable('failed_jobs')) {
            return Section::unavailable('failed_jobs table missing');
        }

        return Section::ok(DB::table('failed_jobs')->count());
    }

    private function pendingJobs(): Section
    {
        if (config('queue.default') !== 'database') {
            return Section::unavailable('queue driver is not database');
        }

        if (! Schema::hasTable('jobs')) {
            return Section::unavailable('jobs table missing');
        }

        return Section::ok(DB::table('jobs')->count());
    }

    private function lastBackup(): Section
    {
        if (! class_exists(BackupServiceProvider::class)) {
            return Section::unavailable('spatie/laravel-backup not installed');
        }

        $backupName = (string) config('backup.backup.name', config('app.name'));
        $newest = null;

        foreach ((array) config('backup.backup.destination.disks', []) as $disk) {
            foreach (Storage::disk($disk)->files($backupName) as $file) {
                if (! str_ends_with($file, '.zip')) {
                    continue;
                }

                $modified = Storage::disk($disk)->lastModified($file);
                $newest = max($newest ?? 0, $modified);
            }
        }

        return $newest !== null
            ? Section::ok(date('c', $newest))
            : Section::unavailable('no backup archives found');
    }

    private function databaseSize(): Section
    {
        $connection = DB::connection();

        return match ($connection->getDriverName()) {
            'sqlite' => Section::ok(
                ($path = $connection->getConfig('database')) !== ':memory:' && is_file($path)
                    ? filesize($path)
                    : 0,
            ),
            'mysql', 'mariadb' => Section::ok((int) $connection
                ->table('information_schema.TABLES')
                ->where('TABLE_SCHEMA', $connection->getDatabaseName())
                ->sum(DB::raw('DATA_LENGTH + INDEX_LENGTH'))),
            default => Section::unavailable('unsupported driver: '.$connection->getDriverName()),
        };
    }

    private function diskFree(): Section
    {
        $free = disk_free_space(base_path());

        return $free === false
            ? Section::unavailable('disk_free_space failed')
            : Section::ok((int) $free);
    }
}
