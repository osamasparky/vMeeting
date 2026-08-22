<?php

namespace App\Domains\Identity\Models;

use App\Traits\HasUuid;
use App\Domains\Tenancy\Models\Organization;
use App\Domains\Tenancy\Models\OrganizationMember;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, HasApiTokens, HasUuid;

    protected static function newFactory()
    {
        return \Database\Factories\UserFactory::new();
    }

    protected $keyType = 'string';
    public $incrementing = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'nickname',
        'email',
        'password',
        'avatar_url',
        'is_super_admin',
        'two_factor_secret',
        'two_factor_recovery_codes',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_secret',
        'two_factor_recovery_codes',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_super_admin' => 'boolean',
            'two_factor_recovery_codes' => 'array',
        ];
    }

    // ── Relationships ──

    public function memberships(): HasMany
    {
        return $this->hasMany(OrganizationMember::class);
    }

    public function organizations()
    {
        return $this->belongsToMany(Organization::class, 'organization_members')
            ->withPivot('role_id', 'status', 'joined_at')
            ->withTimestamps();
    }

    public function profiles(): HasMany
    {
        return $this->hasMany(\App\Domains\People\Models\UserProfile::class);
    }

    public function profile(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(\App\Domains\People\Models\UserProfile::class);
    }

    public function projectMemberships(): HasMany
    {
        return $this->hasMany(\App\Domains\Projects\Models\ProjectMember::class);
    }

    public function projects(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(\App\Domains\Projects\Models\Project::class, 'project_members')
            ->withPivot('project_role', 'cost_rate', 'billing_rate')
            ->withTimestamps();
    }

    public function assignedTasks(): HasMany
    {
        return $this->hasMany(\App\Domains\Projects\Models\Task::class, 'assignee_id');
    }

    public function createdTasks(): HasMany
    {
        return $this->hasMany(\App\Domains\Projects\Models\Task::class, 'reporter_id');
    }

    public function timeEntries(): HasMany
    {
        return $this->hasMany(\App\Domains\Projects\Models\TimeEntry::class);
    }

    public function activeTimer(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(\App\Domains\Projects\Models\ActiveTimer::class);
    }

    public function timesheets(): HasMany
    {
        return $this->hasMany(\App\Domains\Projects\Models\Timesheet::class);
    }

    // ── Helpers ──

    public function membershipFor(string $organizationId): ?OrganizationMember
    {
        return $this->memberships()
            ->where('organization_id', $organizationId)
            ->first();
    }

    public function isMemberOf(string $organizationId): bool
    {
        return $this->memberships()
            ->where('organization_id', $organizationId)
            ->where('status', 'active')
            ->exists();
    }

    public function hasPermissionInOrg(string $organizationId, string $permissionKey): bool
    {
        $membership = $this->membershipFor($organizationId);

        if (!$membership) {
            return false;
        }

        return $membership->hasPermission($permissionKey);
    }

    public function hasTwoFactorEnabled(): bool
    {
        return !is_null($this->two_factor_secret);
    }

    public function isSuperAdmin(): bool
    {
        if ((bool)($this->is_super_admin ?? false)) {
            return true;
        }

        $superAdminEmails = array_filter(array_map('trim', explode(',', env('SUPER_ADMIN_EMAILS', 'admin@nextspace.munazzah.com,info@meemdtt.com'))));
        if (in_array($this->email, $superAdminEmails, true)) {
            return true;
        }

        return $this->memberships()->whereHas('role', function ($q) {
            $q->where('slug', 'super_admin');
        })->exists();
    }
}
