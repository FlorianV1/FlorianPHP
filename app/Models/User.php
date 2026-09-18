<?php

namespace App\Models;

use Filament\Auth\MultiFactor\App\Contracts\HasAppAuthentication;
use Filament\Auth\MultiFactor\App\Contracts\HasAppAuthenticationRecovery;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable implements FilamentUser, HasAppAuthentication, HasAppAuthenticationRecovery
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'is_admin',
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'app_authentication_secret',
        'app_authentication_recovery_codes',
    ];

    /**
     * Every panel requires the admin flag.
     *
     * This repo is a company hub, not a product: the only people who sign in
     * are staff. There are no policies yet, so panel access IS the
     * authorization boundary — any authenticated user reaching a panel would
     * have full CRUD on everything in it. Until per-resource policies exist,
     * the flag stays the single gate for all panels, never a bare
     * "is authenticated".
     */
    public function canAccessPanel(Panel $panel): bool
    {
        // Read defensively: Filament calls this for every registered panel,
        // including from the topbar on a model that may have been hydrated
        // without this column (a `select()` that omits it, or a factory that
        // never set it). Missing must mean "not an admin", not a 500.
        return $this->isAttributeLoaded('is_admin') && (bool) $this->is_admin;
    }

    /**
     * Read defensively, like canAccessPanel above: a model hydrated without
     * these columns — a factory that never set them, or a select() that
     * omitted them — must read as "no secret yet", not throw. Strict mode
     * turns a missing attribute into an exception, which would 500 the
     * profile page.
     */
    public function getAppAuthenticationSecret(): ?string
    {
        return $this->isAttributeLoaded('app_authentication_secret')
            ? $this->app_authentication_secret
            : null;
    }

    public function saveAppAuthenticationSecret(?string $secret): void
    {
        $this->app_authentication_secret = $secret;
        $this->save();
    }

    public function getAppAuthenticationHolderName(): string
    {
        return $this->email;
    }

    /** @return array<int, string>|null */
    public function getAppAuthenticationRecoveryCodes(): ?array
    {
        return $this->isAttributeLoaded('app_authentication_recovery_codes')
            ? $this->app_authentication_recovery_codes
            : null;
    }

    public function saveAppAuthenticationRecoveryCodes(?array $codes): void
    {
        $this->app_authentication_recovery_codes = $codes;
        $this->save();
    }

    /**
     * Whether a column was actually hydrated onto this instance.
     *
     * Not `hasAttribute()`: that also returns true when the key merely
     * appears in $casts, which every column below does — so it reports
     * "present" for an attribute that was never retrieved, and reading it
     * then throws under strict mode. Only the loaded attribute bag is
     * authoritative.
     */
    private function isAttributeLoaded(string $key): bool
    {
        return array_key_exists($key, $this->getAttributes());
    }

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_admin' => 'boolean',
            'app_authentication_secret' => 'encrypted',
            'app_authentication_recovery_codes' => 'encrypted:array',
        ];
    }
}
