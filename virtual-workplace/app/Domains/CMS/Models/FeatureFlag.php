<?php

namespace App\Domains\CMS\Models;

use Illuminate\Database\Eloquent\Model;

class FeatureFlag extends Model
{
    protected $table = 'feature_flags';

    protected $fillable = [
        'flag_key',
        'name_en',
        'name_ar',
        'description_en',
        'description_ar',
        'category',
        'is_enabled',
    ];

    protected $casts = [
        'is_enabled' => 'boolean',
    ];

    public static function isEnabled(string $key, bool $default = true): bool
    {
        $flag = static::where('flag_key', $key)->first();
        return $flag ? (bool)$flag->is_enabled : $default;
    }

    public function getNameAttribute(): string
    {
        $locale = app()->getLocale();
        return $locale === 'ar' ? ($this->name_ar ?: $this->name_en) : ($this->name_en ?: $this->name_ar);
    }

    public function getDescriptionAttribute(): ?string
    {
        $locale = app()->getLocale();
        return $locale === 'ar' ? ($this->description_ar ?: $this->description_en) : ($this->description_en ?: $this->description_ar);
    }
}
