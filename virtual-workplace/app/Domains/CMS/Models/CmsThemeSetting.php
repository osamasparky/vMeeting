<?php

namespace App\Domains\CMS\Models;

use Illuminate\Database\Eloquent\Model;

class CmsThemeSetting extends Model
{
    protected $table = 'cms_theme_settings';

    protected $fillable = [
        'setting_key',
        'setting_value',
    ];

    public static function getByKey(string $key, mixed $default = null): mixed
    {
        $item = static::where('setting_key', $key)->first();
        if (! $item) {
            return $default;
        }

        $decoded = json_decode($item->setting_value, true);

        return (json_last_error() === JSON_ERROR_NONE) ? $decoded : $item->setting_value;
    }

    public static function setKey(string $key, mixed $value): void
    {
        $val = is_array($value) ? json_encode($value) : (string) $value;
        static::updateOrCreate(['setting_key' => $key], ['setting_value' => $val]);
    }
}
