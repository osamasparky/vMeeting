<?php

namespace App\Domains\Administration\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class SystemSetting extends Model
{
    protected $table = 'system_settings';
    protected $primaryKey = 'key';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'key',
        'value',
    ];

    /**
     * Get a setting value by key with optional default.
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        try {
            $val = Cache::remember("sys_setting_{$key}", 3600, function () use ($key) {
                $setting = static::find($key);
                return $setting ? $setting->value : null;
            });

            if ($val === null) {
                return $default;
            }

            $decoded = json_decode($val, true);
            return (json_last_error() === JSON_ERROR_NONE) ? $decoded : $val;
        } catch (\Throwable $e) {
            return $default;
        }
    }

    /**
     * Set a setting value by key.
     */
    public static function set(string $key, mixed $value): static
    {
        $encoded = is_array($value) || is_object($value) ? json_encode($value, JSON_UNESCAPED_UNICODE) : (string) $value;

        $setting = static::updateOrCreate(
            ['key' => $key],
            ['value' => $encoded]
        );

        Cache::forget("sys_setting_{$key}");

        return $setting;
    }
}
