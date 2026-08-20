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

class Project extends Model
{
    use HasFactory, HasUuid, BelongsToOrganization, Auditable;

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

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function manager(): BelongsTo
    {
        return $this->belongsTo(User::class, 'manager_id');
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function members(): HasMany
    {
        return $this->hasMany(ProjectMember::class);
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'project_members')
            ->withPivot('project_role', 'cost_rate', 'billing_rate')
            ->withTimestamps();
    }

    public function phases(): HasMany
    {
        return $this->hasMany(ProjectPhase::class)->orderBy('order');
    }

    public function milestones(): HasMany
    {
        return $this->hasMany(ProjectMilestone::class)->orderBy('due_date');
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class)->orderBy('order');
    }

    public function rootTasks(): HasMany
    {
        return $this->hasMany(Task::class)->whereNull('parent_task_id')->orderBy('order');
    }

    public function timeEntries(): HasMany
    {
        return $this->hasMany(TimeEntry::class);
    }

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
