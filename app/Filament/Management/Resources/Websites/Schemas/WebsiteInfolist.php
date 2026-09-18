<?php

declare(strict_types=1);

namespace App\Filament\Management\Resources\Websites\Schemas;

use App\Models\SiteBridgeKey;
use App\Models\Website;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

final class WebsiteInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Overview')
                    ->columnSpanFull()
                    ->schema([
                        Grid::make(4)->schema([
                            TextEntry::make('client.company_name')
                                ->label('Client'),
                            TextEntry::make('environment')
                                ->badge(),
                            TextEntry::make('lock_level')
                                ->badge()
                                ->color(fn (Website $record): string => $record->lockBadgeColor())
                                ->formatStateUsing(fn (int $state, Website $record): string => $record->lockChangePending()
                                    ? "L{$state} → L{$record->desired_lock_level} pending"
                                    : ($state === 0 ? 'Unlocked' : "Locked (L{$state})")),
                            TextEntry::make('last_seen_at')
                                ->since()
                                ->placeholder('Never'),
                        ]),
                        Grid::make(2)->schema([
                            TextEntry::make('url')
                                ->label('Primary URL')
                                ->url(fn (Website $record): string => $record->url)
                                ->openUrlInNewTab(),
                            TextEntry::make('tech_stack_notes')
                                ->placeholder('—'),
                        ]),
                    ]),
                Section::make('Health')
                    ->description('The latest snapshot the site reported on its last heartbeat.')
                    ->columnSpanFull()
                    ->schema([
                        Grid::make(4)->schema([
                            TextEntry::make('health_state')
                                ->label('State')
                                ->state(fn (Website $record): string => ucfirst($record->healthStatus()))
                                ->badge()
                                ->color(fn (Website $record): string => $record->healthStatusColor()),
                            TextEntry::make('health_pulled_at')
                                ->label('Pulled')
                                ->state(fn (Website $record): ?string => data_get($record->health, 'pulled_at'))
                                ->since()
                                ->placeholder('Never'),
                            TextEntry::make('health_versions')
                                ->label('Laravel / PHP')
                                ->state(fn (Website $record): ?string => data_get($record->health, 'status.laravel_version') !== null
                                    ? data_get($record->health, 'status.laravel_version').' / '.data_get($record->health, 'status.php_version')
                                    : null)
                                ->placeholder('—'),
                            TextEntry::make('health_site_lock')
                                ->label('Lock (reported by site)')
                                ->state(fn (Website $record): string => 'L'.data_get($record->health, 'status.lock.lock_level', '?'))
                                ->badge()
                                ->color(fn (Website $record): string => ((int) data_get($record->health, 'status.lock.lock_level', 0)) === 0 ? 'success' : 'danger'),
                        ]),
                        Grid::make(4)->schema([
                            TextEntry::make('health_failed_jobs')
                                ->label('Failed jobs')
                                ->state(fn (Website $record): mixed => data_get($record->health, 'metrics.failed_jobs.value'))
                                ->placeholder(fn (Website $record): string => data_get($record->health, 'metrics.failed_jobs.reason') ?? '—'),
                            TextEntry::make('health_disk_free')
                                ->label('Disk free')
                                ->state(function (Website $record): ?string {
                                    $bytes = data_get($record->health, 'metrics.disk_free_bytes.value');

                                    return $bytes !== null ? number_format($bytes / 1_073_741_824, 1).' GB' : null;
                                })
                                ->placeholder('—'),
                            TextEntry::make('health_mailcoach')
                                ->label('MailCoach subscribers')
                                ->state(fn (Website $record): mixed => data_get($record->health, 'metrics.mailcoach.value.subscribers'))
                                ->placeholder(fn (Website $record): string => data_get($record->health, 'metrics.mailcoach.reason') ?? '—'),
                            TextEntry::make('health_bugsnag')
                                ->label('Bugsnag open errors')
                                ->state(fn (Website $record): mixed => $record->bugsnagOpenErrors())
                                ->color(fn (Website $record): ?string => ($record->bugsnagOpenErrors() ?? 0) > 0 ? 'warning' : null)
                                ->helperText(fn (Website $record): ?string => $record->bugsnag_synced_at !== null
                                    ? 'Pulled '.$record->bugsnag_synced_at->diffForHumans()
                                    : null)
                                ->placeholder(fn (Website $record): string => $record->bugsnagUnavailableReason() ?? '—'),
                            TextEntry::make('health_updates')
                                ->label('Pending updates')
                                ->state(function (Website $record): ?string {
                                    $count = $record->healthPendingUpdates();

                                    return $count !== null
                                        ? "{$count} ({$record->healthPendingMajorUpdates()} major)"
                                        : null;
                                })
                                ->color(fn (Website $record): ?string => ($record->healthPendingMajorUpdates() ?? 0) > 0 ? 'warning' : null)
                                ->placeholder('—'),
                        ]),
                        TextEntry::make('health_error')
                            ->label('Last error')
                            ->state(fn (Website $record): ?string => data_get($record->health, 'error'))
                            ->color('danger')
                            ->visible(fn (Website $record): bool => filled(data_get($record->health, 'error'))),
                        TextEntry::make('health_raw')
                            ->label('Raw payload')
                            ->state(fn (Website $record): string => $record->health !== null
                                ? json_encode($record->health, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)
                                : 'No health data pulled yet.')
                            ->fontFamily('mono')
                            ->extraAttributes(['class' => 'text-xs'])
                            ->columnSpanFull(),
                    ]),
                Section::make('Hosting & repository')
                    ->columnSpanFull()
                    ->columns(3)
                    ->schema([
                        TextEntry::make('hosting_provider')
                            ->placeholder('—'),
                        TextEntry::make('server_host')
                            ->label('Server')
                            ->copyable()
                            ->placeholder('—'),
                        TextEntry::make('repository_url')
                            ->url(fn (Website $record): ?string => $record->repository_url)
                            ->openUrlInNewTab()
                            ->placeholder('—'),
                    ]),
                Section::make('Enrolment')
                    ->description('Keys client sites check in with. Generate a claim code to enrol a new site or rotate a key.')
                    ->columnSpanFull()
                    ->columns(3)
                    ->schema([
                        TextEntry::make('bridge_site_id')
                            ->label('Site ID')
                            ->copyable()
                            ->placeholder('—'),
                        TextEntry::make('active_keys')
                            ->label('Active keys')
                            ->state(fn (Website $record): array => $record->bridgeKeys
                                ->whereNull('revoked_at')
                                ->map(fn (SiteBridgeKey $key): string => $key->key_prefix.'…'
                                    .($key->first_seen_domain !== null ? " ({$key->first_seen_domain})" : '')
                                    .($key->hasDomainMismatch() ? ' ⚠ domain mismatch' : ''))
                                ->values()
                                ->all())
                            ->listWithLineBreaks()
                            ->placeholder('None — generate a claim code'),
                        TextEntry::make('heartbeat_state')
                            ->label('Heartbeat')
                            ->state(fn (Website $record): string => $record->last_heartbeat_at === null
                                ? 'Never'
                                : ($record->heartbeatIsStale() ? 'Stale — no check-in' : 'OK'))
                            ->badge()
                            ->color(fn (Website $record): string => $record->last_heartbeat_at !== null && ! $record->heartbeatIsStale()
                                ? 'success'
                                : 'danger'),
                    ]),
            ]);
    }
}
