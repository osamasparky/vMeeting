<?php

namespace App\Domains\Workspace\Models;

use App\Traits\Auditable;
use App\Traits\BelongsToOrganization;
use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Map extends Model
{
    use HasUuid, BelongsToOrganization, Auditable;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'floor_id',
        'organization_id',
        'name',
        'status',
        'version',
        'width',
        'height',
        'tile_size',
        'layout_data',
        'published_at',
    ];

    protected $casts = [
        'version' => 'integer',
        'width' => 'integer',
        'height' => 'integer',
        'tile_size' => 'integer',
        'layout_data' => 'array',
        'published_at' => 'datetime',
    ];

    // ── Relationships ──

    public function floor(): BelongsTo
    {
        return $this->belongsTo(Floor::class);
    }

    public function versions(): HasMany
    {
        return $this->hasMany(MapVersion::class)->orderByDesc('version');
    }

    public function rooms(): HasMany
    {
        return $this->hasMany(Room::class);
    }

    public function zones(): HasMany
    {
        return $this->hasMany(Zone::class);
    }

    public function objects(): HasMany
    {
        return $this->hasMany(MapObject::class);
    }

    // ── Helpers ──

    public function isPublished(): bool
    {
        return $this->status === 'published';
    }

    public function isDraft(): bool
    {
        return $this->status === 'draft';
    }
}
