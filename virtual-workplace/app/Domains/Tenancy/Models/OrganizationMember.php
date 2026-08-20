<?php

namespace App\Domains\Tenancy\Models;

use App\Domains\Administration\Models\Role;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrganizationMember extends Model
{
    protected $fillable = [
        'organization_id',
        'user_id',
        'role_id',
        'status',
        'cost_rate',
        'billing_rate',
        'weekly_capacity_hours',
        'joined_at',
    ];

    protected $casts = [
        'cost_rate' => 'decimal:2',
        'billing_rate' => 'decimal:2',
        'weekly_capacity_hours' => 'decimal:2',
        'joined_at' => 'datetime',
    ];

    // ── Relationships ──

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(\App\Domains\Identity\Models\User::class);
    }

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    // ── Helpers ──

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function activate(): void
    {
        $this->update([
            'status' => 'active',
            'joined_at' => now(),
        ]);
    }

    public function hasPermission(string $permissionKey): bool
    {
        return $this->role->permissions()->where('key', $permissionKey)->exists();
    }
}
