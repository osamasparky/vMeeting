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
        'city_location',
        'description',
        'is_default',
        'order',
    ];

    protected $casts = [
        'order' => 'integer',
        'is_default' => 'boolean',
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

    public function rooms(): \Illuminate\Database\Eloquent\Relations\HasManyThrough
    {
        return $this->hasManyThrough(Room::class, Map::class);
    }

    public function members(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(\App\Domains\Tenancy\Models\OrganizationMember::class, 'member_office_access', 'floor_id', 'organization_member_id')
            ->withTimestamps();
    }
}
