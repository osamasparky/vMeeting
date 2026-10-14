<?php

namespace App\Domains\Projects\Models;

use App\Domains\Identity\Models\User;
use App\Domains\People\Models\Team;
use App\Domains\Projects\Services\AutomationEngine;
use App\Traits\Auditable;
use App\Traits\BelongsToOrganization;
use App\Traits\HasUuid;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Task extends Model
{
    use Auditable, BelongsToOrganization, HasFactory, HasUuid, SoftDeletes;

    /** "Deleting" archives the task; forceDelete() removes it for good. */
    public const DELETED_AT = 'archived_at';

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

    // Recurrence rules
    public const RECURRENCE_DAILY = 'daily';

    public const RECURRENCE_WEEKLY = 'weekly';

    public const RECURRENCE_BIWEEKLY = 'biweekly';

    public const RECURRENCE_MONTHLY = 'monthly';

    public const RECURRENCE_QUARTERLY = 'quarterly';

    public const RECURRENCE_RULES = [
        self::RECURRENCE_DAILY,
        self::RECURRENCE_WEEKLY,
        self::RECURRENCE_BIWEEKLY,
        self::RECURRENCE_MONTHLY,
        self::RECURRENCE_QUARTERLY,
    ];

    protected $fillable = [
        'organization_id',
        'project_id',
        'sprint_id',
        'phase_id',
        'milestone_id',
        'parent_task_id',
        'title',
        'description',
        'task_number',
        'task_type',
        'status',
        'state_id',
        'priority',
        'assignee_id',
        'reporter_id',
        'team_id',
        'start_date',
        'due_date',
        'completed_at',
        'estimated_hours',
        'story_points',
        'is_billable',
        'order',
        'metadata',
        'recurrence_rule',
        'recurrence_interval',
        'recurrence_ends_at',
        'last_recurred_at',
        'approval_status',
        'approved_by',
        'approved_at',
        'rejection_reason',
    ];

    protected $casts = [
        'task_number' => 'integer',
        'start_date' => 'date',
        'due_date' => 'date',
        'recurrence_ends_at' => 'date',
        'last_recurred_at' => 'datetime',
        'recurrence_interval' => 'integer',
        'completed_at' => 'datetime',
        'approved_at' => 'datetime',
        'estimated_hours' => 'decimal:2',
        'story_points' => 'integer',
        'is_billable' => 'boolean',
        'order' => 'integer',
        'metadata' => 'array',
    ];

    public function isRecurring(): bool
    {
        return ! empty($this->recurrence_rule);
    }

    public function calculateNextDueDate(): ?Carbon
    {
        if (! $this->isRecurring()) {
            return null;
        }

        $base = $this->due_date ? $this->due_date->copy() : now();
        $interval = max(1, (int) $this->recurrence_interval);

        $nextDate = match ($this->recurrence_rule) {
            self::RECURRENCE_DAILY => $base->addDays($interval),
            self::RECURRENCE_WEEKLY => $base->addWeeks($interval),
            self::RECURRENCE_BIWEEKLY => $base->addWeeks(2 * $interval),
            self::RECURRENCE_MONTHLY => $base->addMonths($interval),
            self::RECURRENCE_QUARTERLY => $base->addMonths(3 * $interval),
            default => null,
        };

        if ($nextDate && $this->recurrence_ends_at && $nextDate->isAfter($this->recurrence_ends_at)) {
            return null;
        }

        return $nextDate;
    }

    // ── Relationships ──

    /** @return BelongsTo<User, $this> */
    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /** @return BelongsTo<Project, $this> */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    /** @return BelongsTo<ProjectPhase, $this> */
    public function phase(): BelongsTo
    {
        return $this->belongsTo(ProjectPhase::class, 'phase_id');
    }

    /** @return BelongsTo<ProjectMilestone, $this> */
    public function milestone(): BelongsTo
    {
        return $this->belongsTo(ProjectMilestone::class, 'milestone_id');
    }

    /** @return BelongsTo<Task, $this> */
    public function parentTask(): BelongsTo
    {
        return $this->belongsTo(Task::class, 'parent_task_id');
    }

    /** @return HasMany<Task, $this> */
    public function subtasks(): HasMany
    {
        return $this->hasMany(Task::class, 'parent_task_id')->orderBy('order');
    }

    /** @return BelongsTo<User, $this> */
    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assignee_id');
    }

    /** @return BelongsTo<User, $this> */
    public function reporter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reporter_id');
    }

    /** @return BelongsTo<Team, $this> */
    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    /** @return HasMany<TaskChecklistItem, $this> */
    public function checklistItems(): HasMany
    {
        return $this->hasMany(TaskChecklistItem::class)->orderBy('order');
    }

    /** @return HasMany<TaskComment, $this> */
    public function comments(): HasMany
    {
        return $this->hasMany(TaskComment::class)->orderBy('created_at', 'asc');
    }

    /** @return HasMany<TaskAttachment, $this> */
    public function attachments(): HasMany
    {
        return $this->hasMany(TaskAttachment::class)->orderBy('created_at', 'desc');
    }

    /** @return HasMany<TaskDependency, $this> */
    public function dependencies(): HasMany
    {
        return $this->hasMany(TaskDependency::class, 'task_id');
    }

    /** @return BelongsToMany<Task, $this> */
    public function dependsOnTasks(): BelongsToMany
    {
        return $this->belongsToMany(Task::class, 'task_dependencies', 'task_id', 'depends_on_task_id')
            ->withPivot('dependency_type')
            ->withTimestamps();
    }

    /** @return BelongsToMany<Task, $this> */
    public function dependentTasks(): BelongsToMany
    {
        return $this->belongsToMany(Task::class, 'task_dependencies', 'depends_on_task_id', 'task_id')
            ->withPivot('dependency_type')
            ->withTimestamps();
    }

    /** @return HasMany<TimeEntry, $this> */
    public function timeEntries(): HasMany
    {
        return $this->hasMany(TimeEntry::class);
    }

    /** @return BelongsTo<ProjectSprint, $this> */
    public function sprint(): BelongsTo
    {
        return $this->belongsTo(ProjectSprint::class, 'sprint_id');
    }

    /** @return HasMany<TaskCustomFieldValue, $this> */
    public function customFieldValues(): HasMany
    {
        return $this->hasMany(TaskCustomFieldValue::class, 'task_id')->with('definition');
    }

    /** @return BelongsTo<ProjectState, $this> */
    public function state(): BelongsTo
    {
        return $this->belongsTo(ProjectState::class, 'state_id');
    }

    /** @return BelongsToMany<ProjectLabel, $this> */
    public function labels(): BelongsToMany
    {
        return $this->belongsToMany(ProjectLabel::class, 'task_labels', 'task_id', 'label_id')->orderBy('name');
    }

    /**
     * Everyone working on the task. assignee_id is the primary (first) one and
     * is always part of this list — see booted().
     *
     * @return BelongsToMany<User, $this>
     */
    public function assignees(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'task_assignees', 'task_id', 'user_id')->withTimestamps();
    }

    /**
     * Tasks a user is assigned to, as primary or additional assignee.
     */
    public function scopeAssignedTo($query, string $userId)
    {
        return $query->where(function ($q) use ($userId) {
            $q->where($q->qualifyColumn('assignee_id'), $userId)
                ->orWhereHas('assignees', fn ($a) => $a->where('users.id', $userId));
        });
    }

    /**
     * People following the task — told about status changes and comments.
     *
     * @return BelongsToMany<User, $this>
     */
    public function watchers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'task_watchers', 'task_id', 'user_id')->withTimestamps();
    }

    public function addWatchers(array $userIds): void
    {
        $ids = array_values(array_unique(array_filter($userIds)));
        if ($ids) {
            $this->watchers()->syncWithoutDetaching($ids);
        }
    }

    /** Set while restoring so subtasks archived together come back too. */
    public $archivedAtBeforeRestore = null;

    /** Milestone completion and goal progress after the task set changed. */
    public function refreshRollups(): void
    {
        $this->milestone?->checkAndUpdateStatus();
        $this->project?->goals->each->recalculateProgress();
    }

    /** @return HasMany<TaskRelation, $this> */
    public function relations(): HasMany
    {
        return $this->hasMany(TaskRelation::class, 'task_id');
    }

    /** @return HasMany<TaskRelation, $this> */
    public function inverseRelations(): HasMany
    {
        return $this->hasMany(TaskRelation::class, 'related_task_id');
    }

    public function isAssignedTo(string $userId): bool
    {
        return $this->assignee_id === $userId
            || $this->assignees()->where('users.id', $userId)->exists();
    }

    protected static function booted(): void
    {
        // Keep the assignee list in step with the primary assignee when it is
        // set through the single-assignee paths (create, assign, bulk, office).
        // Keep the task's state inside its status group: any path that only
        // sets `status` (bulk, office, recurrence, approval) lands the task in
        // the project's first state of that group.
        static::saving(function (Task $task) {
            if (! $task->project_id || (! $task->isDirty(['status', 'state_id', 'project_id']) && $task->exists)) {
                return;
            }
            $current = $task->state_id ? ProjectState::find($task->state_id) : null;
            if ($current && $current->project_id === $task->project_id && ProjectState::groupsMatch($current->group, $task->status)) {
                return;
            }
            $task->state_id = ProjectState::defaultFor($task->project_id, $task->status ?? self::STATUS_BACKLOG)?->id;
        });

        static::created(function (Task $task) {
            if ($task->assignee_id) {
                $task->assignees()->syncWithoutDetaching([$task->assignee_id]);
            }
            // The reporter and the assignee follow the task from the start.
            $task->addWatchers([$task->reporter_id, $task->assignee_id]);
            AutomationEngine::fire($task, 'task_created');
        });

        // Archiving a task archives its subtasks with it; restoring brings back
        // the ones archived in the same action. Rollups follow either way.
        static::deleted(function (Task $task) {
            if ($task->isForceDeleting()) {
                return;
            }
            // Same timestamp as the parent, at every depth, so restore can match them.
            $parentIds = [$task->id];
            while ($parentIds) {
                $children = Task::whereIn('parent_task_id', $parentIds)->pluck('id')->all();
                if ($children) {
                    Task::whereIn('id', $children)->update([self::DELETED_AT => $task->archived_at]);
                }
                $parentIds = $children;
            }
            $task->refreshRollups();
        });
        static::restoring(function (Task $task) {
            $task->archivedAtBeforeRestore = $task->archived_at;
        });
        static::restored(function (Task $task) {
            if ($task->archivedAtBeforeRestore) {
                $parentIds = [$task->id];
                while ($parentIds) {
                    $children = Task::onlyTrashed()
                        ->whereIn('parent_task_id', $parentIds)
                        ->where(self::DELETED_AT, $task->archivedAtBeforeRestore)
                        ->pluck('id')->all();
                    if ($children) {
                        Task::onlyTrashed()->whereIn('id', $children)->update([self::DELETED_AT => null]);
                    }
                    $parentIds = $children;
                }
            }
            $task->refreshRollups();
        });

        static::updated(function (Task $task) {
            if ($task->wasChanged('state_id')) {
                AutomationEngine::fire($task, 'state_changed');
            }
            if ($task->wasChanged('priority')) {
                AutomationEngine::fire($task, 'priority_changed');
            }
            if ($task->wasChanged('assignee_id') && $task->assignee_id) {
                AutomationEngine::fire($task, 'assignee_changed');
            }
        });

        static::updated(function (Task $task) {
            if (! $task->wasChanged('assignee_id')) {
                return;
            }
            // Original values are still pre-save inside `updated`.
            $previous = $task->getOriginal('assignee_id');
            if ($previous) {
                $task->assignees()->detach($previous);
            }
            if ($task->assignee_id) {
                $task->assignees()->syncWithoutDetaching([$task->assignee_id]);
                $task->addWatchers([$task->assignee_id]);
            }
        });
    }

    /** @return HasMany<ActiveTimer, $this> */
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

        if (! in_array($toStatus, self::STATUSES, true)) {
            return false;
        }

        return true;
    }
}
