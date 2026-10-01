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
     * Read a boolean/flag setting. Interprets '1'/'true'/'on'/'yes' as true
     * and '0'/'false'/'off'/'no'/'' as false. Missing keys fall back to
     * $default so new flags behave as enabled until explicitly turned off.
     */
    public static function enabled(string $key, bool $default = true): bool
    {
        $value = static::allAsArray()[$key] ?? null;

        if ($value === null) {
            return $default;
        }

        return in_array(strtolower(trim((string) $value)), ['1', 'true', 'on', 'yes'], true);
    }

    /**
     * Forget the cached settings so edits take effect immediately.
     */
    public static function flushCache(): void
    {
        Cache::forget(self::CACHE_KEY);
    }
}
