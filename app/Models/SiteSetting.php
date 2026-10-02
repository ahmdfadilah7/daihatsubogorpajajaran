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
     * Resolve the admin-configured WhatsApp number into a wa.me-ready,
     * digits-only international string. Admins may enter human formats like
     * '+62 812 3456 7890', '0812-3456-7890' or '62 812...'; wa.me needs digits
     * only. Rule: strip all non-digits; a leading '0' becomes '62' (Indonesian
     * local -> international); numbers already starting with '62' are kept. When
     * the setting is blank, fall back to the historical hardcoded default so WA
     * buttons never break.
     */
    public static function whatsappNumber(string $default = '6281234567890'): string
    {
        $raw = static::get('contact_whatsapp', '');
        $digits = preg_replace('/\D+/', '', (string) $raw);

        if ($digits === '' || $digits === null) {
            return $default;
        }

        if (str_starts_with($digits, '0')) {
            $digits = '62' . substr($digits, 1);
        }

        return $digits;
    }

    /**
     * Forget the cached settings so edits take effect immediately.
     */
    public static function flushCache(): void
    {
        Cache::forget(self::CACHE_KEY);
    }
}
