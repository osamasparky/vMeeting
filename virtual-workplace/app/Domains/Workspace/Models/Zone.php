<?php

namespace App\Domains\Workspace\Models;

use App\Traits\BelongsToOrganization;
use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Zone extends Model
{
    use BelongsToOrganization, HasUuid;

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'map_id',
        'organization_id',
        'name',
        'type',
        'shape_type',
        'shape_data',
        'audible_radius',
        'metadata',
    ];

    protected $casts = [
        'shape_data' => 'array',
        'audible_radius' => 'float',
        'metadata' => 'array',
    ];

    // ── Relationships ──

    public function map(): BelongsTo
    {
        return $this->belongsTo(Map::class);
    }
}
