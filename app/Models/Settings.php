<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Settings extends Model
{
    protected $fillable = ['key', 'value'];

    protected $casts = [
        'value' => 'array',
    ];

    private static ?array $loaded = null;

    private static function loadAll(): array
    {
        if (static::$loaded === null) {
            static::$loaded = static::all()->pluck('value', 'key')->toArray();
        }

        return static::$loaded;
    }

    public static function get($key, $default = null)
    {
        return static::loadAll()[$key] ?? $default;
    }

    /**
     * Drop the in-process cache. Writes through `set()` do this themselves;
     * this is for anything that changes the table behind the model's back —
     * a seeder, a migration, or a test rolling back between cases.
     */
    public static function flush(): void
    {
        static::$loaded = null;
    }

    public static function set($key, $value)
    {
        static::$loaded = null;

        return static::updateOrCreate(
            ['key' => $key],
            ['value' => $value]
        );
    }
}
