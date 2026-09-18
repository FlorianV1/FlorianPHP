<?php

declare(strict_types=1);

namespace Agency\SiteBridge\Support;

use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Throwable;

/**
 * Stores and reads this site's heartbeat credential(s) in its own database.
 * The key is encrypted at rest; the hub's pinned public key travels with it.
 * Two rows may coexist briefly during rotation — the newest active one wins.
 */
final class Credential
{
    /**
     * Persist a freshly claimed credential.
     */
    public function store(string $key, string $hubUrl, string $siteId, string $hubPublicKey): void
    {
        DB::table('site_bridge_credentials')->insert([
            'key' => Crypt::encryptString($key),
            'key_prefix' => mb_substr($key, 0, 16),
            'hub_url' => mb_rtrim($hubUrl, '/'),
            'site_id' => $siteId,
            'hub_public_key' => $hubPublicKey,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * The newest non-revoked credential with its key decrypted, or null when
     * the site is not enrolled (or the row can't be read/decrypted).
     *
     * @return object{key: string, key_prefix: string, hub_url: string, site_id: string, hub_public_key: string}|null
     */
    public function active(): ?object
    {
        try {
            $row = DB::table('site_bridge_credentials')
                ->whereNull('revoked_at')
                ->orderByDesc('id')
                ->first();

            if ($row === null) {
                return null;
            }

            return (object) [
                'key' => Crypt::decryptString($row->key),
                'key_prefix' => $row->key_prefix,
                'hub_url' => $row->hub_url,
                'site_id' => $row->site_id,
                'hub_public_key' => $row->hub_public_key,
            ];
        } catch (Throwable) {
            return null;
        }
    }
}
