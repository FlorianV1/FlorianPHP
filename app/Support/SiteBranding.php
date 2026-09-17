<?php

namespace App\Support;

use App\Models\Profile;
use App\Models\Settings;

/**
 * One source of truth for the strings that used to be hardcoded in three
 * different Blade files: the `~/florian.dev` logo (navbar and footer disagreed),
 * the `florian@dev` terminal prompt, and the <title>.
 */
class SiteBranding
{
    public const KEY = 'site_branding';

    public static function defaults(): array
    {
        return [
            'full_name' => 'Florian',
            'logo_prefix' => '~/',
            'logo_text' => 'florian.dev',
            'terminal_user' => 'florian',
            'terminal_host' => 'dev',
            'terminal_path' => '~/portfolio',
            'site_title' => null,
            'meta_description' => null,
            'og_image' => null,
        ];
    }

    public static function all(): array
    {
        $stored = Settings::get(self::KEY, []);

        return array_merge(self::defaults(), is_array($stored) ? array_filter($stored, fn ($v) => $v !== null) : []);
    }

    public static function get(string $key, mixed $fallback = null): mixed
    {
        return self::all()[$key] ?? $fallback;
    }

    /** "florian@dev" */
    public static function terminalPrompt(): string
    {
        return self::get('terminal_user') . '@' . self::get('terminal_host');
    }

    /** "~/florian.dev" */
    public static function logo(): string
    {
        return self::get('logo_prefix') . self::get('logo_text');
    }

    /**
     * Page <title>. Falls back to the profile so an empty CMS field never
     * produces a blank tab.
     */
    public static function title(?Profile $profile = null): string
    {
        if (filled($configured = self::get('site_title'))) {
            return $configured;
        }

        $profile ??= Profile::first();

        return trim(self::get('full_name') . ' — ' . ($profile?->role ?? 'Software Developer'));
    }

    public static function metaDescription(?Profile $profile = null): string
    {
        if (filled($configured = self::get('meta_description'))) {
            return $configured;
        }

        $profile ??= Profile::first();

        return (string) ($profile?->tagline ?? '');
    }
}
