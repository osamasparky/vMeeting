<?php

namespace App\Domains\Tenancy\Models;

use App\Domains\Identity\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SubscriptionRequest extends Model
{
    protected $fillable = [
        'organization_id',
        'user_id',
        'plan_id',
        'seats',
        'price_per_seat',
        'amount',
        'currency',
        'billing_cycle',
        'payment_method',
        'bank_name',
        'sender_name',
        'sender_account',
        'transfer_reference',
        'transfer_date',
        'receipt_path',
        'notes',
        'status',
        'request_type',
        'reviewed_by',
        'reviewed_at',
        'admin_notes',
    ];

    protected $casts = [
        'seats' => 'integer',
        'price_per_seat' => 'decimal:2',
        'amount' => 'decimal:2',
        'transfer_date' => 'date',
        'reviewed_at' => 'datetime',
    ];

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    public function isRejected(): bool
    {
        return $this->status === 'rejected';
    }

    public function isCancelled(): bool
    {
        return $this->status === 'cancelled';
    }
}
