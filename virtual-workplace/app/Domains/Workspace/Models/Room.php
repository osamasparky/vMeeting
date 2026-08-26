<?php

namespace App\Domains\Workspace\Models;

use App\Traits\Auditable;
use App\Traits\BelongsToOrganization;
use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Room extends Model
{
    use HasUuid, BelongsToOrganization, Auditable;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'map_id',
        'organization_id',
        'name',
        'type',
        'access_mode',
        'capacity',
        'color',
        'bounds',
        'metadata',
    ];

    protected $casts = [
        'capacity' => 'integer',
        'bounds' => 'array',
        'metadata' => 'array',
    ];

    // ── Relationships ──

    public function map(): BelongsTo
    {
        return $this->belongsTo(Map::class);
    }

    public function floor()
    {
        return $this->hasOneThrough(
            Floor::class,
            Map::class,
            'id',
            'id',
            'map_id',
            'floor_id'
        );
    }

    // ── Helpers ──

    public function isPrivate(): bool
    {
        return $this->access_mode === 'private';
    }

    public function isPublic(): bool
    {
        return $this->access_mode === 'public';
    }
}
