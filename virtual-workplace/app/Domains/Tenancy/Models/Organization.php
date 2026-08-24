<?php

namespace App\Domains\Tenancy\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Organization extends Model
{
    use HasUuid;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'name',
        'slug',
        'logo_url',
        'timezone',
        'status',
        'plan_id',
    ];

    protected $casts = [
        'status' => 'string',
    ];

    // ── Relationships ──

    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }

    public function settings(): HasOne
    {
        return $this->hasOne(OrganizationSetting::class);
    }

    public function members(): HasMany
    {
        return $this->hasMany(OrganizationMember::class);
    }

    public function subscription(): HasOne
    {
        return $this->hasOne(Subscription::class)->latestOfMany();
    }

    public function departments(): HasMany
    {
        return $this->hasMany(\App\Domains\People\Models\Department::class);
    }

    public function teams(): HasMany
    {
        return $this->hasMany(\App\Domains\People\Models\Team::class);
    }

    public function auditLogs(): HasMany
    {
        return $this->hasMany(\App\Domains\Administration\Models\AuditLog::class);
    }

    public function floors(): HasMany
    {
        return $this->hasMany(\App\Domains\Workspace\Models\Floor::class);
    }

    public function maps(): HasMany
    {
        return $this->hasMany(\App\Domains\Workspace\Models\Map::class);
    }

    public function rooms(): HasMany
    {
        return $this->hasMany(\App\Domains\Workspace\Models\Room::class);
    }

    public function projects(): HasMany
    {
        return $this->hasMany(\App\Domains\Projects\Models\Project::class);
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(\App\Domains\Projects\Models\Task::class);
    }

    public function timeEntries(): HasMany
    {
        return $this->hasMany(\App\Domains\Projects\Models\TimeEntry::class);
    }

    public function timesheets(): HasMany
    {
        return $this->hasMany(\App\Domains\Projects\Models\Timesheet::class);
    }

    public function subscriptionRequests(): HasMany
    {
        return $this->hasMany(SubscriptionRequest::class)->latest();
    }

    public function pendingSubscriptionRequest(): HasOne
    {
        return $this->hasOne(SubscriptionRequest::class)->where('status', 'pending')->latestOfMany();
    }


    // ── Helpers ──

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function activeMembersCount(): int
    {
        return $this->members()->where('status', 'active')->count();
    }

    public function hasReachedSeatLimit(): bool
    {
        if (!$this->plan || $this->plan->seat_limit === 0) {
            return false; // Unlimited
        }

        return $this->activeMembersCount() >= $this->plan->seat_limit;
    }

    public function offices(): HasMany
    {
        return $this->hasMany(\App\Domains\Workspace\Models\Floor::class)->orderBy('order', 'asc');
    }

    public function defaultOffice(): ?\App\Domains\Workspace\Models\Floor
    {
        return $this->offices()->where('is_default', true)->first() ?: $this->offices()->first();
    }

    public function hasReachedOfficeLimit(): bool
    {
        if (!$this->plan || $this->plan->max_offices === 0) {
            return false; // Unlimited
        }

        return $this->offices()->count() >= $this->plan->max_offices;
    }

    public function hasReachedRoomLimit(): bool
    {
        if (!$this->plan || $this->plan->room_limit === 0) {
            return false; // Unlimited
        }

        return $this->rooms()->count() >= $this->plan->room_limit;
    }

    public function activeGuestInvitationsCount(): int
    {
        return \App\Domains\Guests\Models\GuestInvitation::where('organization_id', $this->id)
            ->where(function ($q) {
                $q->whereNull('expires_at')->orWhere('expires_at', '>', now());
            })
            ->count();
    }

    public function hasReachedGuestInvitationLimit(): bool
    {
        if (!$this->plan) return false;
        $maxGuests = $this->plan->max_guest_invitations ?? 5;
        if ($maxGuests === 0) return false; // Unlimited

        return $this->activeGuestInvitationsCount() >= $maxGuests;
    }

    /**
     * Complete Plan Usage Metrics Summary for Dashboard & Billing.
     */
    public function getPlanUsageSummary(): array
    {
        $plan = $this->plan;
        $activeMembers = $this->activeMembersCount();
        $totalRooms = $this->rooms()->count();
        $totalOffices = $this->offices()->count();
        $activeGuests = $this->activeGuestInvitationsCount();

        $seatLimit = $plan ? $plan->seat_limit : 5;
        $roomLimit = $plan ? $plan->room_limit : 3;
        $officeLimit = $plan ? ($plan->max_offices ?? 1) : 1;
        $guestLimit = $plan ? ($plan->max_guest_invitations ?? 5) : 5;
        $storageLimit = $plan ? $plan->storage_limit_gb : 1;

        $isSeatsExceeded = ($seatLimit > 0 && $activeMembers > $seatLimit);
        $isRoomsExceeded = ($roomLimit > 0 && $totalRooms > $roomLimit);
        $isOfficesExceeded = ($officeLimit > 0 && $totalOffices > $officeLimit);
        $isGuestsExceeded = ($guestLimit > 0 && $activeGuests > $guestLimit);

        return [
            'plan_name' => $plan ? $plan->name : 'Free',
            'plan_slug' => $plan ? $plan->slug : 'free',
            'is_active' => $this->isActive(),
            'members' => [
                'used' => $activeMembers,
                'limit' => $seatLimit,
                'is_unlimited' => $seatLimit === 0,
                'is_exceeded' => $isSeatsExceeded,
                'percentage' => $seatLimit > 0 ? min(100, round(($activeMembers / $seatLimit) * 100)) : 0,
            ],
            'rooms' => [
                'used' => $totalRooms,
                'limit' => $roomLimit,
                'is_unlimited' => $roomLimit === 0,
                'is_exceeded' => $isRoomsExceeded,
                'percentage' => $roomLimit > 0 ? min(100, round(($totalRooms / $roomLimit) * 100)) : 0,
            ],
            'offices' => [
                'used' => $totalOffices,
                'limit' => $officeLimit,
                'is_unlimited' => $officeLimit === 0,
                'is_exceeded' => $isOfficesExceeded,
            ],
            'guests' => [
                'used' => $activeGuests,
                'limit' => $guestLimit,
                'is_unlimited' => $guestLimit === 0,
                'is_exceeded' => $isGuestsExceeded,
            ],
            'storage' => [
                'limit_gb' => $storageLimit,
                'is_unlimited' => $storageLimit === 0,
            ],
            'has_exceeded_any_limit' => ($isSeatsExceeded || $isRoomsExceeded || $isOfficesExceeded || $isGuestsExceeded),
        ];
    }
}
