<?php

declare(strict_types=1);

namespace App\Models;

use Carbon\CarbonImmutable;
use Database\Factories\SiteBridgeKeyFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A long-lived credential a client site uses to authenticate its outbound
 * heartbeats. Only the sha256 hash is stored; the plaintext is shown once.
 *
 * @property string $key_hash
 * @property string $key_prefix
 * @property string|null $first_seen_domain
 * @property string|null $last_seen_domain
 * @property CarbonImmutable|null $domain_mismatch_at
 * @property CarbonImmutable|null $last_used_at
 * @property CarbonImmutable|null $revoked_at
 */
final class SiteBridgeKey extends Model
{
    /** @use HasFactory<SiteBridgeKeyFactory> */
    use HasFactory;

    /**
     * Every column except the key and timestamps. Written only by the
     * heartbeat and enrolment services, never from request input, but listed
     * explicitly so a new column has to be opted in deliberately.
     *
     * @var list<string>
     */
    protected $fillable = [
        'website_id',
        'key_hash',
        'key_prefix',
        'first_seen_domain',
        'last_seen_domain',
        'domain_mismatch_at',
        'last_used_at',
        'revoked_at',
    ];

    /** @return BelongsTo<Website, $this> */
    public function website(): BelongsTo
    {
        return $this->belongsTo(Website::class);
    }

    /**
     * Keys that have not been revoked.
     *
     * @param  Builder<SiteBridgeKey>  $query
     */
    public function scopeActive(Builder $query): void
    {
        $query->whereNull('revoked_at');
    }

    public function isRevoked(): bool
    {
        return $this->revoked_at !== null;
    }

    public function hasDomainMismatch(): bool
    {
        return $this->domain_mismatch_at !== null;
    }

    public function revoke(): void
    {
        if ($this->revoked_at === null) {
            $this->forceFill(['revoked_at' => now()])->save();
        }
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'domain_mismatch_at' => 'immutable_datetime',
            'last_used_at' => 'immutable_datetime',
            'revoked_at' => 'immutable_datetime',
        ];
    }
}
