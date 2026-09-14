<?php

namespace App\Domains\Workspace\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FurnitureItem extends Model
{
    protected $fillable = [
        'category_id',
        'name',
        'slug',
        'image_url',
        'thumbnail_url',
        'icon',
        'width',
        'height',
        'collision',
        'elevation',
        'interaction_type',
        'interaction_config',
        'colors',
        'is_active',
    ];

    protected $casts = [
        'collision' => 'boolean',
        'is_active' => 'boolean',
        'colors' => 'array',
        'interaction_config' => 'array',
        'width' => 'integer',
        'height' => 'integer',
        'elevation' => 'integer',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(FurnitureCategory::class, 'category_id');
    }
}
