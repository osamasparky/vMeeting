<?php

namespace App\Domains\CMS\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CmsPage extends Model
{
    protected $table = 'cms_pages';

    protected $fillable = [
        'slug',
        'title_en',
        'title_ar',
        'meta_title_en',
        'meta_title_ar',
        'meta_desc_en',
        'meta_desc_ar',
        'og_image',
        'status',
    ];

    public function sections(): HasMany
    {
        return $this->hasMany(CmsSection::class, 'page_id')->orderBy('display_order');
    }

    public function activeSections(): HasMany
    {
        return $this->hasMany(CmsSection::class, 'page_id')
            ->where('is_active', true)
            ->orderBy('display_order');
    }

    public function getTitleAttribute(): string
    {
        $locale = app()->getLocale();

        return $locale === 'ar' ? ($this->title_ar ?: $this->title_en) : ($this->title_en ?: $this->title_ar);
    }

    public function getMetaTitleAttribute(): ?string
    {
        $locale = app()->getLocale();

        return $locale === 'ar' ? ($this->meta_title_ar ?: $this->title_ar) : ($this->meta_title_en ?: $this->title_en);
    }

    public function getMetaDescAttribute(): ?string
    {
        $locale = app()->getLocale();

        return $locale === 'ar' ? ($this->meta_desc_ar ?: $this->meta_desc_en) : ($this->meta_desc_en ?: $this->meta_desc_ar);
    }
}
