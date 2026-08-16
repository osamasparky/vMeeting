<?php

namespace App\Domains\Administration\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AuditLog extends Model
{
    protected $fillable = [
        'organization_id',
        'actor_id',
        'action',
        'target_type',
        'target_id',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
    ];

    public function organization(): BelongsTo
    {
        return $this->belongsTo(\App\Domains\Tenancy\Models\Organization::class);
    }

    public function actor(): BelongsTo
    {
        return $this->belongsTo(\App\Domains\Identity\Models\User::class, 'actor_id');
    }

    /**
     * Scope to filter by action type.
     */
    public function scopeOfAction($query, string $action)
    {
        return $query->where('action', $action);
    }
}
