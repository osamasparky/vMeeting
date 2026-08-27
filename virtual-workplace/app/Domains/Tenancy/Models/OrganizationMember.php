<?php

namespace App\Domains\Tenancy\Models;

use App\Domains\Administration\Models\Role;
use App\Domains\Identity\Models\User;
use App\Domains\Workspace\Models\Floor;
use App\Domains\Workspace\Models\Room;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

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
        return $this->belongsTo(User::class);
    }

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    public function offices(): BelongsToMany
    {
        return $this->belongsToMany(Floor::class, 'member_office_access', 'organization_member_id', 'floor_id')
            ->withTimestamps();
    }

    public function rooms(): BelongsToMany
    {
        return $this->belongsToMany(Room::class, 'member_room_access', 'organization_member_id', 'room_id')
            ->withTimestamps();
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
        if (! $this->role) {
            return false;
        }

        if ($this->role->slug === 'company_admin' || $this->role->slug === 'super_admin') {
            return true;
        }

        if ($this->relationLoaded('role') && $this->role->relationLoaded('permissions')) {
            return $this->role->permissions->contains('key', $permissionKey);
        }

        return $this->role->permissions()->where('key', $permissionKey)->exists();
    }

    /**
     * Check if member is allowed to enter a given office/floor.
     */
    public function hasOfficeAccess(?string $floorId): bool
    {
        if (! $floorId) {
            return true;
        }

        if ($this->role?->slug === 'company_admin' || $this->role?->slug === 'super_admin' || $this->user?->isSuperAdmin()) {
            return true;
        }

        // If no explicit office restrictions are configured for this member, default to all company offices
        if ($this->offices()->count() === 0) {
            return true;
        }

        return $this->offices()->where('floors.id', $floorId)->exists();
    }

    /**
     * Check if member is allowed to enter a given room.
     */
    public function hasRoomAccess(?string $roomId): bool
    {
        if (! $roomId) {
            return true;
        }

        if ($this->role?->slug === 'company_admin' || $this->role?->slug === 'super_admin' || $this->user?->isSuperAdmin()) {
            return true;
        }

        $room = Room::find($roomId);
        if (! $room) {
            return true;
        }

        // Check if member has specific room assignments
        if ($this->rooms()->count() > 0) {
            return $this->rooms()->where('rooms.id', $roomId)->exists();
        }

        // If public and no specific restrictions, allowed
        return $room->access_mode !== 'private';
    }
}
