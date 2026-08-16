<?php

namespace App\Domains\Workspace\Models;

use App\Traits\Auditable;
use App\Traits\BelongsToOrganization;
use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Floor extends Model
{
    use HasUuid, BelongsToOrganization, Auditable;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'organization_id',
        'name',
        'order',
    ];

    protected $casts = [
        'order' => 'integer',
    ];

    // ── Relationships ──

    public function maps(): HasMany
    {
        return $this->hasMany(Map::class);
    }

    public function activeMap(): HasOne
    {
        return $this->hasOne(Map::class)->where('status', 'published')->latestOfMany();
    }
}
