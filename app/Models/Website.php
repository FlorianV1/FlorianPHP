<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\WebsiteEnvironment;
use Database\Factories\WebsiteFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

/**
 * @property WebsiteEnvironment $environment
 * @property string|null $bugsnag_project_id
 * @property int|null $bugsnag_open_errors
 * @property \Carbon\CarbonImmutable|null $bugsnag_synced_at
 * @property string|null $bugsnag_sync_error
 * @property string|null $bridge_site_id
 * @property int $lock_level
 * @property int $desired_lock_level
 * @property string|null $lock_reason
 * @property int $directive_seq
 * @property \Carbon\CarbonImmutable|null $last_seen_at
 * @property \Carbon\CarbonImmutable|null $last_heartbeat_at
 * @property \Carbon\CarbonImmutable|null $health_alerted_at
 * @property \Carbon\CarbonImmutable|null $enrolled_at
 * @property array<string, mixed>|null $health
 */
final class Website extends Model
{
    /** @use HasFactory<WebsiteFactory> */
    use HasFactory, LogsActivity;

    /**
     * Attributes that must never appear in logs or serialized output.
     *
     * @var list<string>
     */
    protected $hidden = [
        'bugsnag_project_key',
    ];

    /** @return BelongsTo<Client, $this> */
    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    /** @return HasMany<Invoice, $this> */
    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    /** @return HasMany<Retainer, $this> */
    public function retainers(): HasMany
    {
        return $this->hasMany(Retainer::class);
    }

    /** @return HasMany<SiteBridgeKey, $this> */
    public function bridgeKeys(): HasMany
    {
        return $this->hasMany(SiteBridgeKey::class);
    }

    /** @return HasMany<SiteBridgeClaimCode, $this> */
    public function claimCodes(): HasMany
    {
        return $this->hasMany(SiteBridgeClaimCode::class);
    }

    /** @return HasMany<SiteBridgeHeartbeat, $this> */
    public function heartbeats(): HasMany
    {
        return $this->hasMany(SiteBridgeHeartbeat::class);
    }

    public function isLocked(): bool
    {
        return $this->lock_level > 0;
    }

    /**
     * True when an operator has requested a lock level the site has not yet
     * confirmed. Lock changes ride the heartbeat as a signed directive, so
     * the requested level stays pending until the site reports it back.
     */
    public function lockChangePending(): bool
    {
        return $this->desired_lock_level !== $this->lock_level;
    }

    /**
     * Badge colour for the lock level, shared by the site table and the
     * website page: amber while a change is pending confirmation, otherwise
     * green when unlocked and red when locked.
     */
    public function lockBadgeColor(): string
    {
        if ($this->lockChangePending()) {
            return 'warning';
        }

        return $this->lock_level === 0 ? 'success' : 'danger';
    }

    /**
     * True once the site has claimed a key. Enrolled sites are driven solely
     * by the heartbeat + signed-directive path; the legacy pull/push is a
     * no-op for them.
     */
    public function isEnrolled(): bool
    {
        return $this->enrolled_at !== null;
    }

    /**
     * The host of the site's primary URL — the domain we expect its
     * heartbeats to self-report, used only to flag (never enforce) drift.
     */
    public function domain(): ?string
    {
        $host = parse_url((string) $this->url, PHP_URL_HOST);

        return is_string($host) ? mb_strtolower($host) : null;
    }

    /**
     * True once the site has stopped checking in for longer than the
     * configured window — surfaced prominently so a silently-stuck site is
     * visible rather than inferred.
     */
    public function heartbeatIsStale(): bool
    {
        if ($this->last_heartbeat_at === null) {
            return true;
        }

        $staleAfter = (int) config('site-bridge.heartbeat.stale_after_seconds', 900);

        return $this->last_heartbeat_at->addSeconds($staleAfter)->isPast();
    }

    /**
     * Traffic-light state derived from the last health pull.
     *
     * @return 'unknown'|'green'|'amber'|'red'
     */
    public function healthStatus(): string
    {
        // An enrolled site that has gone quiet can't be trusted to still be
        // in its last-reported state — surface that as red rather than let a
        // stale-but-green snapshot mask a site that stopped checking in.
        if ($this->isEnrolled() && $this->heartbeatIsStale()) {
            return 'red';
        }

        if ($this->health === null) {
            return 'unknown';
        }

        if (data_get($this->health, 'ok') !== true || data_get($this->health, 'status.up') === false) {
            return 'red';
        }

        $amber = (bool) data_get($this->health, 'status.maintenance_mode', false)
            || ((int) data_get($this->health, 'status.lock.lock_level', 0)) > 0
            || $this->healthErrorCount() > 0;

        return $amber ? 'amber' : 'green';
    }

    public function healthStatusColor(): string
    {
        return match ($this->healthStatus()) {
            'green' => 'success',
            'amber' => 'warning',
            'red' => 'danger',
            default => 'gray',
        };
    }

    /**
     * Failed jobs plus open Bugsnag errors.
     */
    public function healthErrorCount(): int
    {
        return (int) data_get($this->health, 'metrics.failed_jobs.value', 0)
            + ($this->bugsnagOpenErrors() ?? 0);
    }

    /**
     * True once the site is mapped to a Bugsnag project the hub can pull.
     */
    public function bugsnagIsLinked(): bool
    {
        return filled($this->bugsnag_project_id);
    }

    /**
     * Open Bugsnag errors, preferring what the hub pulled itself over what
     * the site reported on its heartbeat. The hub's figure covers sites
     * that aren't enrolled at all, so it is the more complete source; the
     * heartbeat value remains the fallback for a site that reports its own.
     */
    public function bugsnagOpenErrors(): ?int
    {
        if ($this->bugsnag_synced_at !== null) {
            return $this->bugsnag_open_errors;
        }

        $reported = data_get($this->health, 'metrics.bugsnag.value.open_errors');

        return is_numeric($reported) ? (int) $reported : null;
    }

    /**
     * Why the open-error count is missing or stale, for the UI to show in
     * place of a number.
     */
    public function bugsnagUnavailableReason(): ?string
    {
        if (filled($this->bugsnag_sync_error)) {
            return $this->bugsnag_sync_error;
        }

        if (! $this->bugsnagIsLinked()) {
            return data_get($this->health, 'metrics.bugsnag.reason') ?? 'not linked to a Bugsnag project';
        }

        return $this->bugsnag_synced_at === null ? 'not synced yet' : null;
    }

    /**
     * Pending composer updates reported by the bridge's /updates endpoint.
     */
    public function healthPendingUpdates(): ?int
    {
        $packages = data_get($this->health, 'updates.packages.value');

        return is_array($packages) ? count($packages) : null;
    }

    /**
     * How many of the pending updates are major version bumps.
     */
    public function healthPendingMajorUpdates(): ?int
    {
        $packages = data_get($this->health, 'updates.packages.value');

        return is_array($packages)
            ? collect($packages)->where('is_major', true)->count()
            : null;
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['lock_level', 'label', 'url', 'environment'])
            ->logOnlyDirty()
            ->dontLogEmptyChanges();
    }

    protected static function booted(): void
    {
        // Every website carries a stable public id the client site pins at
        // claim time and checks against every signed lock directive.
        self::creating(function (Website $website): void {
            if (blank($website->bridge_site_id)) {
                $website->bridge_site_id = (string) Str::uuid();
            }
        });
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'environment' => WebsiteEnvironment::class,
            'bugsnag_project_key' => 'encrypted',
            'bugsnag_open_errors' => 'integer',
            'bugsnag_synced_at' => 'immutable_datetime',
            'lock_level' => 'integer',
            'desired_lock_level' => 'integer',
            'directive_seq' => 'integer',
            'last_seen_at' => 'immutable_datetime',
            'last_heartbeat_at' => 'immutable_datetime',
            'health_alerted_at' => 'immutable_datetime',
            'enrolled_at' => 'immutable_datetime',
            'health' => 'array',
        ];
    }
}
