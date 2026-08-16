<?php

namespace App\Domains\Meetings\Models;

use App\Domains\Identity\Models\User;
use App\Domains\Workspace\Models\Room;
use App\Traits\Auditable;
use App\Traits\BelongsToOrganization;
use App\Traits\HasUuid;
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
        'created_by',
        'title',
        'type',
        'status',
        'started_at',
        'ended_at',
        'livekit_room_name',
        'recording_url',
        'settings',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'ended_at' => 'datetime',
        'settings' => 'array',
    ];

    // ── Relationships ──

    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function participants(): HasMany
    {
        return $this->hasMany(MeetingParticipant::class);
    }

    // ── Helpers ──

    public function isActive(): bool
    {
        return $this->status === 'active';
    }
}
