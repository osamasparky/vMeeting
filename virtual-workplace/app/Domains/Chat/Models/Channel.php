<?php

namespace App\Domains\Chat\Models;

use App\Domains\Identity\Models\User;
use App\Domains\Workspace\Models\Room;
use App\Traits\BelongsToOrganization;
use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Channel extends Model
{
    use HasUuid, BelongsToOrganization;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'organization_id',
        'type',
        'name',
        'room_id',
    ];

    // ── Relationships ──

    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }

    public function members(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'channel_members')
            ->withPivot('last_read_at')
            ->withTimestamps();
    }

    public function messages(): HasMany
    {
        return $this->hasMany(Message::class)->orderBy('created_at', 'asc');
    }
}
