<?php

namespace App\Domains\Projects\Models;

use App\Traits\Auditable;
use App\Traits\BelongsToOrganization;
use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProjectMilestone extends Model
{
    use Auditable, BelongsToOrganization, HasUuid;

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'organization_id',
        'project_id',
        'name',
        'due_date',
        'status',
        'completed_at',
    ];

    protected $casts = [
        'due_date' => 'date',
        'completed_at' => 'datetime',
    ];

    protected $appends = [
        'title',
        'progress_percentage',
        'tasks_count',
        'completed_tasks_count',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class, 'milestone_id');
    }

    public function getTitleAttribute(): string
    {
        return $this->name ?? '';
    }

    public function getTasksCountAttribute(): int
    {
        return $this->tasks()->count();
    }

    public function getCompletedTasksCountAttribute(): int
    {
        return $this->tasks()->where('status', 'done')->count();
    }

    public function getProgressPercentageAttribute(): float
    {
        $total = $this->getTasksCountAttribute();
        if ($total === 0) {
            return ($this->status === 'completed') ? 100.0 : 0.0;
        }

        return (float) round(($this->getCompletedTasksCountAttribute() / $total) * 100, 1);
    }

    public function actualHours(): float
    {
        return (float) round(
            $this->tasks()->with('timeEntries')->get()->sum(fn (Task $t) => $t->actualHours()),
            2
        );
    }

    public function checkAndUpdateStatus(): void
    {
        $total = $this->tasks()->count();
        $done = $this->tasks()->where('status', 'done')->count();

        if ($total > 0 && $done === $total && $this->status !== 'completed') {
            $this->status = 'completed';
            $this->completed_at = now();
            $this->save();
        } elseif ($total > 0 && $done < $total && $this->status === 'completed') {
            $this->status = 'pending';
            $this->completed_at = null;
            $this->save();
        }
    }
}
