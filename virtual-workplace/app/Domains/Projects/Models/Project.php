<?php

namespace App\Domains\Projects\Models;

use App\Domains\Identity\Models\User;
use App\Domains\People\Models\Department;
use App\Traits\Auditable;
use App\Traits\BelongsToOrganization;
use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Project extends Model
{
    use Auditable, BelongsToOrganization, HasFactory, HasUuid, SoftDeletes;

    /** "Deleting" archives the project; forceDelete() removes it for good. */
    public const DELETED_AT = 'archived_at';

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'organization_id',
        'name',
        'code',
        'description',
        'owner_id',
        'manager_id',
        'department_id',
        'status',
        'priority',
        'start_date',
        'due_date',
        'completed_at',
        'budget_amount',
        'planned_hours',
        'color',
        'metadata',
    ];

    protected $casts = [
        'start_date' => 'date',
        'due_date' => 'date',
        'completed_at' => 'datetime',
        'budget_amount' => 'decimal:2',
        'planned_hours' => 'decimal:2',
        'metadata' => 'array',
    ];

    // ── Relationships ──

    /** @return BelongsTo<User, $this> */
    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    /** @return BelongsTo<User, $this> */
    public function manager(): BelongsTo
    {
        return $this->belongsTo(User::class, 'manager_id');
    }

    /** @return BelongsTo<Department, $this> */
    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    /** @return HasMany<ProjectMember, $this> */
    public function members(): HasMany
    {
        return $this->hasMany(ProjectMember::class);
    }

    /** @return BelongsToMany<User, $this> */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'project_members')
            ->withPivot('project_role', 'cost_rate', 'billing_rate')
            ->withTimestamps();
    }

    /** @return HasMany<ProjectPhase, $this> */
    public function phases(): HasMany
    {
        return $this->hasMany(ProjectPhase::class)->orderBy('order');
    }

    /** @return HasMany<ProjectMilestone, $this> */
    public function milestones(): HasMany
    {
        return $this->hasMany(ProjectMilestone::class)->orderBy('due_date');
    }

    /** @return HasMany<Task, $this> */
    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class)->orderBy('order');
    }

    /** @return HasMany<Task, $this> */
    public function rootTasks(): HasMany
    {
        return $this->hasMany(Task::class)->whereNull('parent_task_id')->orderBy('order');
    }

    /** @return HasMany<TimeEntry, $this> */
    public function timeEntries(): HasMany
    {
        return $this->hasMany(TimeEntry::class);
    }

    /** @return HasMany<ProjectFile, $this> */
    public function files(): HasMany
    {
        return $this->hasMany(ProjectFile::class)->latest();
    }

    /** @return HasMany<ActiveTimer, $this> */
    public function activeTimers(): HasMany
    {
        return $this->hasMany(ActiveTimer::class);
    }

    // ── Helpers & Metrics ──

    public function actualHours(): float
    {
        return (float) round($this->timeEntries()->sum('duration_seconds') / 3600, 2);
    }

    public function billableHours(): float
    {
        return (float) round($this->timeEntries()->where('is_billable', true)->sum('duration_seconds') / 3600, 2);
    }

    public function laborCost(): float
    {
        return (float) round(
            $this->timeEntries()->get()->sum(fn ($e) => ($e->duration_seconds / 3600) * ($e->cost_rate ?? 0)),
            2
        );
    }

    public function billableAmount(): float
    {
        return (float) round(
            $this->timeEntries()->where('is_billable', true)->get()->sum(fn ($e) => ($e->duration_seconds / 3600) * ($e->billing_rate ?? 0)),
            2
        );
    }

    /** @return HasMany<CustomFieldDefinition, $this> */
    public function customFieldDefinitions(): HasMany
    {
        return $this->hasMany(CustomFieldDefinition::class)->orderBy('sort_order');
    }

    /** @return HasMany<ProjectDocument, $this> */
    public function documents(): HasMany
    {
        return $this->hasMany(ProjectDocument::class)->latest();
    }

    /** @return HasMany<ProjectGoal, $this> */
    public function goals(): HasMany
    {
        return $this->hasMany(ProjectGoal::class)->with('targets');
    }

    /** @return HasMany<ProjectSprint, $this> */
    public function sprints(): HasMany
    {
        return $this->hasMany(ProjectSprint::class)->latest('start_date');
    }

    /** @return HasMany<IntakeRequest, $this> */
    public function intakeRequests(): HasMany
    {
        return $this->hasMany(IntakeRequest::class);
    }

    /** @return HasMany<ProjectUpdate, $this> */
    public function updates(): HasMany
    {
        return $this->hasMany(ProjectUpdate::class);
    }

    /**
     * Health from the most recent status update (null until one is posted).
     *
     * @return HasOne<ProjectUpdate, $this>
     */
    public function latestUpdate(): HasOne
    {
        return $this->hasOne(ProjectUpdate::class)->latestOfMany();
    }

    /** @return HasMany<ProjectState, $this> */
    public function states(): HasMany
    {
        return $this->hasMany(ProjectState::class)->orderBy('position');
    }

    /** @return HasMany<ProjectLabel, $this> */
    public function labels(): HasMany
    {
        return $this->hasMany(ProjectLabel::class)->orderBy('name');
    }

    /** @return HasMany<ProjectAutomation, $this> */
    public function automations(): HasMany
    {
        return $this->hasMany(ProjectAutomation::class)->orderBy('id');
    }

    public function progressPercentage(): int
    {
        $total = $this->tasks()->count();
        if ($total === 0) {
            return 0;
        }
        $completed = $this->tasks()->where('status', 'done')->count();

        return (int) round(($completed / $total) * 100);
    }

    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }
}
