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

    private static function load(): array
    {
        if (static::$loaded === null) {
            static::$loaded = static::all()->pluck('value', 'key')->toArray();
        }

        return static::$loaded;
    }

    public static function get($key, $default = null)
    {
        return static::load()[$key] ?? $default;
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
