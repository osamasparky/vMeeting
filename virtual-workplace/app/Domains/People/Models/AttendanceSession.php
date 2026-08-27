<?php

namespace App\Domains\People\Models;

use App\Domains\Identity\Models\User;
use App\Domains\Tenancy\Models\Organization;
use App\Domains\Workspace\Models\Room;
use App\Traits\BelongsToOrganization;
use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AttendanceSession extends Model
{
    use BelongsToOrganization, HasUuid;

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'organization_id',
        'user_id',
        'room_id',
        'status',
        'started_at',
        'ended_at',
        'duration_seconds',
        'last_heartbeat_at',
        'ip_address',
        'user_agent',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'ended_at' => 'datetime',
        'last_heartbeat_at' => 'datetime',
        'duration_seconds' => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }

    public function isActive(): bool
    {
        return $this->status === 'active' && is_null($this->ended_at);
    }

    public function isIdlePaused(): bool
    {
        return $this->status === 'idle_paused';
    }

    public function pauseForIdle(): self
    {
        $now = now();
        $this->ended_at = $now;
        $this->status = 'idle_paused';
        $this->duration_seconds = max(0, $now->diffInSeconds($this->started_at));
        $this->save();

        return $this;
    }

    public function close(string $status = 'completed'): self
    {
        $now = now();
        $this->ended_at = $now;
        $this->status = $status;
        $this->duration_seconds = max(0, $now->diffInSeconds($this->started_at));
        $this->save();

        return $this;
    }
}
