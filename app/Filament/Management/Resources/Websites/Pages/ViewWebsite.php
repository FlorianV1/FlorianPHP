<?php

declare(strict_types=1);

namespace App\Filament\Management\Resources\Websites\Pages;

use App\Bugsnag\BugsnagSync;
use App\Filament\Management\Resources\Websites\WebsiteResource;
use App\Models\Website;
use App\SiteBridge\SiteBridgeEnrolment;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Filament\Support\Icons\Heroicon;

final class ViewWebsite extends ViewRecord
{
    protected static string $resource = WebsiteResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('management')
                ->label('Admin')
                ->icon(Heroicon::OutlinedWrenchScrewdriver)
                ->url(fn (): ?string => $this->website()->management_url)
                ->openUrlInNewTab()
                ->visible(fn (): bool => filled($this->website()->management_url)),
            Action::make('mailcoach')
                ->label('MailCoach')
                ->icon(Heroicon::OutlinedEnvelope)
                ->url(fn (): ?string => $this->website()->mailcoach_url)
                ->openUrlInNewTab()
                ->visible(fn (): bool => filled($this->website()->mailcoach_url)),
            Action::make('bugsnag')
                ->label('Bugsnag')
                ->icon(Heroicon::OutlinedBugAnt)
                ->url(fn (): ?string => $this->website()->bugsnag_project_url)
                ->openUrlInNewTab()
                ->visible(fn (): bool => filled($this->website()->bugsnag_project_url)),
            Action::make('syncBugsnag')
                ->label('Sync errors')
                ->icon(Heroicon::OutlinedArrowPath)
                ->color('gray')
                ->visible(fn (): bool => app(BugsnagSync::class)->isConfigured() && $this->website()->bugsnagIsLinked())
                ->action(function (): void {
                    $website = $this->website();

                    if (app(BugsnagSync::class)->syncWebsite($website)) {
                        Notification::make()
                            ->title("{$website->refresh()->bugsnag_open_errors} open error(s)")
                            ->success()
                            ->send();

                        return;
                    }

                    Notification::make()
                        ->title('Bugsnag sync failed')
                        ->body($website->refresh()->bugsnag_sync_error ?? 'Unknown error.')
                        ->danger()
                        ->send();
                }),
            Action::make('generateClaimCode')
                ->label('Claim code')
                ->icon(Heroicon::OutlinedKey)
                ->color('gray')
                ->requiresConfirmation()
                ->modalHeading('Generate a one-time claim code')
                ->modalDescription('Run "php artisan site-bridge:claim <code>" on the client site within 15 minutes. It exchanges the code for a long-lived key.')
                ->modalSubmitActionLabel('Generate')
                ->action(function (): void {
                    $code = app(SiteBridgeEnrolment::class)->issueClaimCode($this->website());

                    Notification::make()
                        ->title('Claim code (shown once)')
                        ->body($code)
                        ->success()
                        ->persistent()
                        ->send();
                }),
            Action::make('revokeKey')
                ->label('Revoke key')
                ->icon(Heroicon::OutlinedTrash)
                ->color('danger')
                ->visible(fn (): bool => $this->website()->bridgeKeys()->active()->exists())
                ->schema([
                    Select::make('key_id')
                        ->label('Active key')
                        ->options(fn (): array => $this->website()->bridgeKeys()->active()->pluck('key_prefix', 'id')->all())
                        ->required(),
                ])
                ->requiresConfirmation()
                ->modalHeading('Revoke a key')
                ->modalDescription('The site can no longer check in with this key. Rotate by generating a new claim code first.')
                ->action(function (array $data): void {
                    $this->website()->bridgeKeys()->active()->whereKey($data['key_id'])->first()?->revoke();

                    Notification::make()->title('Key revoked')->success()->send();
                }),
            Action::make('lock')
                ->label('Lock control')
                ->icon(Heroicon::OutlinedLockClosed)
                ->color(fn (): string => $this->website()->isLocked() ? 'danger' : 'gray')
                ->badge(fn (): ?string => $this->website()->isLocked() ? "L{$this->website()->lock_level}" : null)
                ->schema([
                    Select::make('level')
                        ->label('Lock level')
                        ->options([
                            0 => '0 — Unlocked (normal operation)',
                            1 => '1 — Deploy gate (blocks deploys, no user-facing effect)',
                            2 => '2 — Maintenance (503 for public traffic)',
                            3 => '3 — Hard kill-switch (blocks site, queues & scheduler)',
                        ])
                        ->default(fn (): int => $this->website()->desired_lock_level)
                        ->required(),
                    Textarea::make('reason')
                        ->required()
                        ->maxLength(500)
                        ->placeholder('e.g. Invoice INV-2026-014 unpaid for 60 days'),
                ])
                ->requiresConfirmation()
                ->modalHeading('Change lock level')
                ->modalDescription('The site applies this on its next check-in — within ~60s once locked, ~5 min otherwise. The directive is Ed25519-signed; the site confirms the new level in its following heartbeat.')
                ->action(fn (array $data) => $this->requestLock((int) $data['level'], $data['reason'])),
            EditAction::make(),
        ];
    }

    /**
     * Stage a lock change for an enrolled site. The level is not enforced
     * here: it rides the next heartbeat as a signed directive and the site
     * confirms it on the following check-in. Until then it shows as pending.
     */
    private function requestLock(int $level, string $reason): void
    {
        $website = $this->website();

        if ($level === $website->desired_lock_level) {
            Notification::make()
                ->title("Already set to lock level {$level}")
                ->body('No change — the site is already targeting this level.')
                ->info()
                ->send();

            return;
        }

        $website->forceFill([
            'desired_lock_level' => $level,
            'lock_reason' => $reason,
        ])->save();

        activity()
            ->performedOn($website)
            ->causedBy(auth()->user())
            ->withProperties(['lock_level' => $level, 'reason' => $reason])
            ->log("Requested lock level {$level} — applies on next heartbeat.");

        Notification::make()
            ->title("Lock level {$level} queued")
            ->body($website->heartbeatIsStale()
                ? 'The site has not checked in recently — it will apply this when it next reaches the hub.'
                : 'The site applies this on its next check-in.')
            ->success()
            ->send();
    }

    private function website(): Website
    {
        /** @var Website */
        return $this->getRecord();
    }
}
