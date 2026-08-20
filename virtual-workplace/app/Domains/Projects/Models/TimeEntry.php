<?php

namespace App\Domains\Projects\Models;

use App\Domains\Identity\Models\User;
use App\Traits\Auditable;
use App\Traits\BelongsToOrganization;
use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TimeEntry extends Model
{
    use HasFactory, HasUuid, BelongsToOrganization, Auditable;

    protected $keyType = 'string';
    public $incrementing = false;

    public const STATUS_DRAFT = 'draft';
    public const STATUS_SUBMITTED = 'submitted';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_REJECTED = 'rejected';

    public const TYPE_TIMER = 'timer';
    public const TYPE_MANUAL = 'manual';

    protected $fillable = [
        'organization_id',
        'user_id',
        'project_id',
        'task_id',
        'timesheet_id',
        'started_at',
        'ended_at',
        'duration_seconds',
        'description',
        'is_billable',
        'cost_rate',
        'billing_rate',
        'entry_type',
        'status',
        'approved_by',
        'approved_at',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'ended_at' => 'datetime',
        'duration_seconds' => 'integer',
        'is_billable' => 'boolean',
        'cost_rate' => 'decimal:2',
        'billing_rate' => 'decimal:2',
        'approved_at' => 'datetime',
    ];

    // ── Relationships ──

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function task(): BelongsTo
    {
        return $this->belongsTo(Task::class);
    }

    public function timesheet(): BelongsTo
    {
        return $this->belongsTo(Timesheet::class);
    }

    public function approvedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    // ── Helpers ──

    public function hours(): float
    {
        return (float) round($this->duration_seconds / 3600, 2);
    }

    public function isLocked(): bool
    {
        return in_array($this->status, [self::STATUS_SUBMITTED, self::STATUS_APPROVED], true);
    }

    public function laborCost(): float
    {
        return (float) round(($this->duration_seconds / 3600) * (float) $this->cost_rate, 2);
    }

    public function billableRevenue(): float
    {
        if (!$this->is_billable) {
            return 0.00;
        }
        return (float) round(($this->duration_seconds / 3600) * (float) $this->billing_rate, 2);
    }
}
