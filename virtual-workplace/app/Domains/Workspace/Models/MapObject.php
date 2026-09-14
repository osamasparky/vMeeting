<?php

namespace App\Domains\Workspace\Models;

use App\Traits\BelongsToOrganization;
use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MapObject extends Model
{
    use BelongsToOrganization, HasUuid;

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'map_id',
        'organization_id',
        'type',
        'name',
        'position',
        'size',
        'collision',
        'interaction_config',
    ];

    protected $casts = [
        'position' => 'array',
        'size' => 'array',
        'collision' => 'boolean',
        'interaction_config' => 'array',
    ];

    protected $appends = [
        'image_url',
        'width',
        'height',
        'elevation',
        'is_custom',
        'interaction_type',
    ];

    public function getImageUrlAttribute()
    {
        if (!empty($this->attributes['image_url'])) {
            return $this->attributes['image_url'];
        }
        if (!empty($this->interaction_config['image_url'])) {
            return $this->interaction_config['image_url'];
        }
        if (!empty($this->type)) {
            $catItem = FurnitureItem::where('slug', $this->type)->first();
            if ($catItem && !empty($catItem->image_url)) {
                return $catItem->image_url;
            }
        }
        return null;
    }

    public function getWidthAttribute()
    {
        return $this->size['width'] ?? ($this->interaction_config['width'] ?? 1);
    }

    public function getHeightAttribute()
    {
        return $this->size['height'] ?? ($this->interaction_config['height'] ?? 1);
    }

    public function getElevationAttribute()
    {
        return $this->interaction_config['elevation'] ?? 1;
    }

    public function getIsCustomAttribute()
    {
        return $this->interaction_config['is_custom'] ?? true;
    }

    public function getInteractionTypeAttribute()
    {
        return $this->interaction_config['interaction_type'] ?? ($this->interaction_config['behavior']['type'] ?? 'none');
    }

    // ── Relationships ──

    public function map(): BelongsTo
    {
        return $this->belongsTo(Map::class);
    }
}
