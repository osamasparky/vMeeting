<?php

namespace App\Domains\CMS\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class CmsMediaAsset extends Model
{
    protected $table = 'cms_media_assets';

    protected $fillable = [
        'name',
        'asset_type',
        'file_path',
        'thumbnail_path',
        'dimensions',
        'file_size',
        'tags',
        'version_tag',
        'is_active',
    ];

    protected $casts = [
        'tags' => 'array',
        'is_active' => 'boolean',
    ];

    public function sections(): HasMany
    {
        return $this->hasMany(CmsSection::class, 'media_asset_id');
    }

    public function getUrlAttribute(): string
    {
        if (str_starts_with($this->file_path, 'http://') || str_starts_with($this->file_path, 'https://')) {
            return $this->file_path;
        }
        if (str_starts_with($this->file_path, '/')) {
            return $this->file_path;
        }
        return asset($this->file_path);
    }
}
