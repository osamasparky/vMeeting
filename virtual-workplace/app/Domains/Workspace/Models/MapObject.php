<?php

namespace App\Domains\Workspace\Models;

use App\Traits\BelongsToOrganization;
use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MapObject extends Model
{
    use HasUuid, BelongsToOrganization;

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

    // ── Relationships ──

    public function map(): BelongsTo
    {
        return $this->belongsTo(Map::class);
    }
}
