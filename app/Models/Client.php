<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\ClientStatus;
use App\Enums\InvoiceStatus;
use Database\Factories\ClientFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

/**
 * @property ClientStatus $status
 * @property ClientStatus|null $status_before_lock
 * @property numeric-string|null $hourly_rate
 * @property \Carbon\CarbonImmutable|null $onboarded_at
 */
final class Client extends Model
{
    /** @use HasFactory<ClientFactory> */
    use HasFactory, LogsActivity;

    /** @return HasMany<Website, $this> */
    public function websites(): HasMany
    {
        return $this->hasMany(Website::class);
    }

    /** @return HasMany<Contact, $this> */
    public function contacts(): HasMany
    {
        return $this->hasMany(Contact::class);
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

    /** @return HasMany<TimeEntry, $this> */
    public function timeEntries(): HasMany
    {
        return $this->hasMany(TimeEntry::class);
    }

    /**
     * Value of logged work not yet attached to an invoice.
     */
    public function uninvoicedWorkAmount(): float
    {
        return round(
            $this->timeEntries()
                ->uninvoiced()
                ->get()
                ->sum(fn (TimeEntry $entry): float => $entry->amount()),
            2,
        );
    }

    /**
     * Sum of sent + overdue invoice totals.
     */
    public function outstandingBalance(): float
    {
        return (float) $this->invoices()
            ->whereIn('status', InvoiceStatus::outstanding())
            ->sum('total');
    }

    /**
     * Active retainer revenue normalized to a monthly amount. Reads the
     * `retainers` relation (using it if already eager-loaded), so a list can
     * eager-load once instead of querying per row.
     */
    public function monthlyRecurringRevenue(): float
    {
        return round(
            $this->retainers
                ->where('active', true)
                ->sum(fn (Retainer $retainer): float => $retainer->monthlyAmount()),
            2,
        );
    }

    /**
     * A client with any hard-locked (level 3) site shows as locked. Locking
     * stashes the previous status so lifting the last hard lock restores it
     * rather than forcing every client back to Active.
     */
    public function syncLockStatus(): void
    {
        $hasHardLockedSite = $this->websites()->where('lock_level', 3)->exists();

        if ($hasHardLockedSite && $this->status !== ClientStatus::Locked) {
            $this->update([
                'status_before_lock' => $this->status,
                'status' => ClientStatus::Locked,
            ]);
        } elseif (! $hasHardLockedSite && $this->status === ClientStatus::Locked) {
            $this->update([
                'status' => $this->status_before_lock ?? ClientStatus::Active,
                'status_before_lock' => null,
            ]);
        }
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['status', 'company_name', 'hourly_rate'])
            ->logOnlyDirty()
            ->dontLogEmptyChanges();
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => ClientStatus::class,
            'status_before_lock' => ClientStatus::class,
            'hourly_rate' => 'decimal:2',
            'onboarded_at' => 'immutable_date',
        ];
    }
}
