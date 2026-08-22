<?php

namespace App\Domains\Tenancy\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Plan extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'seat_limit',
        'max_offices',
        'room_limit',
        'storage_limit_gb',
        'features',
        'price',
        'is_active',
    ];

    protected $casts = [
        'features' => 'array',
        'price' => 'decimal:2',
        'seat_limit' => 'integer',
        'max_offices' => 'integer',
        'room_limit' => 'integer',
        'storage_limit_gb' => 'integer',
        'is_active' => 'boolean',
    ];

    public function organizations(): HasMany
    {
        return $this->hasMany(Organization::class);
    }

    public function isUnlimitedSeats(): bool
    {
        return $this->seat_limit === 0;
    }

    public function isUnlimitedOffices(): bool
    {
        return $this->max_offices === 0;
    }

    public function isUnlimitedRooms(): bool
    {
        return $this->room_limit === 0;
    }

    public function hasFeature(string $feature): bool
    {
        return in_array($feature, $this->features ?? []);
    }
}
