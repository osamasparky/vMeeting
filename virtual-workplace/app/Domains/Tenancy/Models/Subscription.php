<?php

namespace App\Domains\Tenancy\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Subscription extends Model
{
    protected $fillable = [
        'organization_id',
        'plan_id',
        'seats',
        'billing_cycle',
        'price_per_seat',
        'status',
        'current_period_end',
    ];

    protected $casts = [
        'seats' => 'integer',
        'price_per_seat' => 'decimal:2',
        'current_period_end' => 'datetime',
    ];

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function getEffectiveSeats(): int
    {
        if ($this->plan && $this->plan->isPerSeat()) {
            return max($this->plan->getEffectiveMinSeats(), (int) ($this->seats ?: $this->plan->getEffectiveMinSeats()));
        }

        return (int) ($this->plan?->seat_limit ?? 0);
    }
}
