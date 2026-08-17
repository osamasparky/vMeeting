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
        'email',
        'password',
        'avatar_url',
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
        $superAdminEmails = array_filter(array_map('trim', explode(',', env('SUPER_ADMIN_EMAILS', 'info@meemdtt.com'))));
        return in_array($this->email, $superAdminEmails) ||
            $this->memberships()->whereHas('role', function ($q) {
                $q->where('slug', 'super_admin');
            })->exists();
    }
}
