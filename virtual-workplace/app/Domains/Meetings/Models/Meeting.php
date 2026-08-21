<?php

namespace App\Domains\Meetings\Models;

use App\Domains\Identity\Models\User;
use App\Domains\Projects\Models\Project;
use App\Domains\Workspace\Models\Room;
use App\Traits\Auditable;
use App\Traits\BelongsToOrganization;
use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Meeting extends Model
{
    use HasUuid, BelongsToOrganization, Auditable;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'organization_id',
        'room_id',
        'project_id',
        'created_by',
        'title',
        'description',
        'type',
        'scope',
        'status',
        'scheduled_at',
        'duration_minutes',
        'started_at',
        'ended_at',
        'livekit_room_name',
        'recording_url',
        'settings',
        'reminders_sent',
    ];

    protected $casts = [
        'scheduled_at' => 'datetime',
        'duration_minutes' => 'integer',
        'started_at' => 'datetime',
        'ended_at' => 'datetime',
        'settings' => 'array',
        'reminders_sent' => 'boolean',
    ];

    // ── Relationships ──

    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function participants(): HasMany
    {
        return $this->hasMany(MeetingParticipant::class);
    }

    // ── Scopes ──

    public function scopeUpcoming(Builder $query): Builder
    {
        return $query->whereIn('status', ['scheduled', 'pending', 'active'])
            ->where(function ($q) {
                $q->whereNull('scheduled_at')
                  ->orWhere('scheduled_at', '>=', now()->subHours(2));
            })
            ->orderByRaw('CASE WHEN status = "active" THEN 0 WHEN status = "pending" THEN 1 ELSE 2 END')
            ->orderBy('scheduled_at', 'asc');
    }

    // ── Helpers ──

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function isScheduled(): bool
    {
        return $this->type === 'scheduled' || $this->status === 'scheduled';
    }
}

