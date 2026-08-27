<?php

namespace App\Domains\CMS\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CmsSection extends Model
{
    protected $table = 'cms_sections';

    protected $fillable = [
        'page_id',
        'section_type',
        'section_key',
        'title_en',
        'title_ar',
        'subtitle_en',
        'subtitle_ar',
        'badge_en',
        'badge_ar',
        'content',
        'media_asset_id',
        'display_order',
        'is_active',
    ];

    protected $casts = [
        'content' => 'array',
        'is_active' => 'boolean',
        'display_order' => 'integer',
    ];

    public function page(): BelongsTo
    {
        return $this->belongsTo(CmsPage::class, 'page_id');
    }

    public function mediaAsset(): BelongsTo
    {
        return $this->belongsTo(CmsMediaAsset::class, 'media_asset_id');
    }

    public function getTitleAttribute(): ?string
    {
        $locale = app()->getLocale();

        return $locale === 'ar' ? ($this->title_ar ?: $this->title_en) : ($this->title_en ?: $this->title_ar);
    }

    public function getSubtitleAttribute(): ?string
    {
        $locale = app()->getLocale();

        return $locale === 'ar' ? ($this->subtitle_ar ?: $this->subtitle_en) : ($this->subtitle_en ?: $this->subtitle_ar);
    }

    public function getBadgeAttribute(): ?string
    {
        $locale = app()->getLocale();

        return $locale === 'ar' ? ($this->badge_ar ?: $this->badge_en) : ($this->badge_en ?: $this->badge_ar);
    }
}
