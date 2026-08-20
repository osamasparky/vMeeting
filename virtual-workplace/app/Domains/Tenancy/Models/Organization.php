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
}
