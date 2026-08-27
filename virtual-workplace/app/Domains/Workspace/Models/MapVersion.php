<?php

namespace App\Domains\Workspace\Models;

use App\Domains\Identity\Models\User;
use App\Traits\BelongsToOrganization;
use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MapVersion extends Model
{
    use BelongsToOrganization, HasUuid;

    public $timestamps = false;

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'map_id',
        'organization_id',
        'published_by',
        'version',
        'layout_snapshot',
        'created_at',
    ];

    protected $casts = [
        'version' => 'integer',
        'layout_snapshot' => 'array',
        'created_at' => 'datetime',
    ];

    // ── Relationships ──

    public function map(): BelongsTo
    {
        return $this->belongsTo(Map::class);
    }

    public function publishedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'published_by');
    }
}
