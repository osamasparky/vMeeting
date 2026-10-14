<?php

namespace App\Domains\People\Models;

use App\Domains\Identity\Models\User;
use App\Domains\Tenancy\Models\Organization;
use App\Domains\Workspace\Models\Floor;
use App\Domains\Workspace\Models\Room;
use App\Traits\BelongsToOrganization;
use App\Traits\HasUuid;
use Carbon\CarbonInterface;
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
        'floor_id',
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

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** @return BelongsTo<Organization, $this> */
    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    /** @return BelongsTo<Room, $this> */
    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }

    /**
     * The office the session was in.
     *
     * @return BelongsTo<Floor, $this>
     */
    public function floor(): BelongsTo
    {
        return $this->belongsTo(Floor::class);
    }

    public function isActive(): bool
    {
        return $this->status === 'active' && is_null($this->ended_at);
    }

    public function isIdlePaused(): bool
    {
        return $this->status === 'idle_paused';
    }

    /** End the session at the user's last activity, not when the idle prompt ran out. */
    public function pauseForIdle(?CarbonInterface $lastActiveAt = null): self
    {
        return $this->close('idle_paused', $lastActiveAt);
    }

    public function close(string $status = 'completed', ?CarbonInterface $at = null): self
    {
        $end = $at ?? now();
        if ($end->lt($this->started_at)) {
            $end = $this->started_at;
        }

        $this->ended_at = $end;
        $this->status = $status;
        $this->duration_seconds = max(0, $end->getTimestamp() - $this->started_at->getTimestamp());
        $this->save();

        return $this;
    }
}
