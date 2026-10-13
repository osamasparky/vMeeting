<?php

namespace App\Domains\People\Models;

use App\Domains\Identity\Models\User;
use App\Traits\BelongsToOrganization;
use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LeaveRequest extends Model
{
    use BelongsToOrganization, HasUuid;

    protected $keyType = 'string';

    public $incrementing = false;

    public const TYPES = ['annual', 'sick', 'unpaid', 'other'];

    public const STATUS_PENDING = 'pending';

    public const STATUS_APPROVED = 'approved';

    public const STATUS_REJECTED = 'rejected';

    public const STATUS_CANCELLED = 'cancelled';

    protected $fillable = [
        'organization_id',
        'user_id',
        'type',
        'start_date',
        'end_date',
        'days',
        'reason',
        'status',
        'reviewed_by',
        'reviewed_at',
        'review_note',
    ];

    protected $casts = [
        'start_date' => 'date:Y-m-d',
        'end_date' => 'date:Y-m-d',
        'days' => 'integer',
        'reviewed_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    /** Requests that hold the dates: waiting for a decision or approved. */
    public function scopeActive(Builder $query): Builder
    {
        return $query->whereIn('status', [self::STATUS_PENDING, self::STATUS_APPROVED]);
    }

    public function typeLabel(): string
    {
        return match ($this->type) {
            'annual' => __('Annual leave'),
            'sick' => __('Sick leave'),
            'unpaid' => __('Unpaid leave'),
            default => __('Other leave'),
        };
    }

    public function statusLabel(): string
    {
        return match ($this->status) {
            self::STATUS_APPROVED => __('hr.leave.approved'),
            self::STATUS_REJECTED => __('hr.leave.rejected'),
            self::STATUS_CANCELLED => __('hr.leave.cancelled'),
            default => __('hr.leave.pending'),
        };
    }
}
