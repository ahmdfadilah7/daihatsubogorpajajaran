<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class SiteSetting extends Model
{
    protected $fillable = [
        'key',
        'value',
    ];

    public const CACHE_KEY = 'site_settings';

    /**
     * All settings as a ['key' => 'value'] array, memoized forever.
     *
     * @return array<string, string|null>
     */
    public static function allAsArray(): array
    {
        return Cache::rememberForever(self::CACHE_KEY, fn () => static::query()->pluck('value', 'key')->all());
    }

    /**
     * Read a single setting by key, returning $default when missing or empty.
     */
    public static function get(string $key, $default = null)
    {
        $value = static::allAsArray()[$key] ?? null;

        if ($value === null || $value === '') {
            return $default;
        }

        return $value;
    }

    /**
     * Forget the cached settings so edits take effect immediately.
     */
    public static function flushCache(): void
    {
        Cache::forget(self::CACHE_KEY);
    }
}
