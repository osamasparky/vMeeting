<?php

namespace App\Domains\Projects\Models;

use App\Domains\Identity\Models\User;
use App\Domains\People\Models\Team;
use App\Traits\Auditable;
use App\Traits\BelongsToOrganization;
use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Task extends Model
{
    use HasFactory, HasUuid, BelongsToOrganization, Auditable;

    protected $keyType = 'string';
    public $incrementing = false;

    // Default workflow statuses
    public const STATUS_BACKLOG = 'backlog';
    public const STATUS_READY = 'ready';
    public const STATUS_IN_PROGRESS = 'in_progress';
    public const STATUS_REVIEW = 'review';
    public const STATUS_QA = 'qa';
    public const STATUS_DONE = 'done';

    public const STATUSES = [
        self::STATUS_BACKLOG,
        self::STATUS_READY,
        self::STATUS_IN_PROGRESS,
        self::STATUS_REVIEW,
        self::STATUS_QA,
        self::STATUS_DONE,
    ];

    // Priority levels
    public const PRIORITY_LOW = 'low';
    public const PRIORITY_MEDIUM = 'medium';
    public const PRIORITY_HIGH = 'high';
    public const PRIORITY_URGENT = 'urgent';

    public const PRIORITIES = [
        self::PRIORITY_LOW,
        self::PRIORITY_MEDIUM,
        self::PRIORITY_HIGH,
        self::PRIORITY_URGENT,
    ];

    protected $fillable = [
        'organization_id',
        'project_id',
        'phase_id',
        'milestone_id',
        'parent_task_id',
        'title',
        'description',
        'task_number',
        'task_type',
        'status',
        'priority',
        'assignee_id',
        'reporter_id',
        'team_id',
        'start_date',
        'due_date',
        'completed_at',
        'estimated_hours',
        'is_billable',
        'order',
        'metadata',
    ];

    protected $casts = [
        'task_number' => 'integer',
        'start_date' => 'date',
        'due_date' => 'date',
        'completed_at' => 'datetime',
        'estimated_hours' => 'decimal:2',
        'is_billable' => 'boolean',
        'order' => 'integer',
        'metadata' => 'array',
    ];

    // ── Relationships ──

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function phase(): BelongsTo
    {
        return $this->belongsTo(ProjectPhase::class, 'phase_id');
    }

    public function milestone(): BelongsTo
    {
        return $this->belongsTo(ProjectMilestone::class, 'milestone_id');
    }

    public function parentTask(): BelongsTo
    {
        return $this->belongsTo(Task::class, 'parent_task_id');
    }

    public function subtasks(): HasMany
    {
        return $this->hasMany(Task::class, 'parent_task_id')->orderBy('order');
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assignee_id');
    }

    public function reporter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reporter_id');
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    public function checklistItems(): HasMany
    {
        return $this->hasMany(TaskChecklistItem::class)->orderBy('order');
    }

    public function comments(): HasMany
    {
        return $this->hasMany(TaskComment::class)->orderBy('created_at', 'asc');
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(TaskAttachment::class)->orderBy('created_at', 'desc');
    }

    public function dependencies(): HasMany
    {
        return $this->hasMany(TaskDependency::class, 'task_id');
    }

    public function dependsOnTasks(): BelongsToMany
    {
        return $this->belongsToMany(Task::class, 'task_dependencies', 'task_id', 'depends_on_task_id')
            ->withPivot('dependency_type')
            ->withTimestamps();
    }

    public function dependentTasks(): BelongsToMany
    {
        return $this->belongsToMany(Task::class, 'task_dependencies', 'depends_on_task_id', 'task_id')
            ->withPivot('dependency_type')
            ->withTimestamps();
    }

    public function timeEntries(): HasMany
    {
        return $this->hasMany(TimeEntry::class);
    }

    public function activeTimers(): HasMany
    {
        return $this->hasMany(ActiveTimer::class);
    }

    // ── Helpers ──

    public function actualHours(): float
    {
        return (float) round($this->timeEntries()->sum('duration_seconds') / 3600, 2);
    }

    public function isCompleted(): bool
    {
        return $this->status === self::STATUS_DONE;
    }

    public function isBlocked(): bool
    {
        return $this->dependsOnTasks()->where('status', '!=', self::STATUS_DONE)->exists();
    }

    /**
     * Determine if a status transition is valid.
     */
    public static function isValidStatusTransition(string $fromStatus, string $toStatus): bool
    {
        if ($fromStatus === $toStatus) {
            return true;
        }

        if (!in_array($toStatus, self::STATUSES, true)) {
            return false;
        }

        return true;
    }
}
