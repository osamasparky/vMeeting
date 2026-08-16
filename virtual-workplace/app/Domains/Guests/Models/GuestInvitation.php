<?php

namespace App\Domains\Guests\Models;

use App\Domains\Identity\Models\User;
use App\Domains\Workspace\Models\Room;
use App\Traits\BelongsToOrganization;
use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class GuestInvitation extends Model
{
    use HasUuid, BelongsToOrganization;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'organization_id',
        'room_id',
        'invited_by',
        'guest_name',
        'guest_email',
        'token',
        'status',
        'expires_at',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
    ];

    public static function generateUniqueToken(): string
    {
        return Str::random(40);
    }

    // ── Relationships ──

    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }

    public function host(): BelongsTo
    {
        return $this->belongsTo(User::class, 'invited_by');
    }

    // ── Helpers ──

    public function isExpired(): bool
    {
        return $this->expires_at->isPast();
    }

    public function isValid(): bool
    {
        return $this->status === 'approved' && !$this->isExpired();
    }
}
