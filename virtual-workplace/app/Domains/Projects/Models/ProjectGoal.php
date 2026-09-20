<?php

namespace App\Domains\Projects\Models;

use App\Domains\Identity\Models\User;
use App\Traits\BelongsToOrganization;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProjectGoal extends Model
{
    use BelongsToOrganization, HasFactory;

    protected $fillable = [
        'organization_id',
        'project_id',
        'owner_id',
        'name',
        'description',
        'color',
        'due_date',
        'status',
        'progress_percentage',
    ];

    protected $casts = [
        'due_date' => 'date',
        'progress_percentage' => 'decimal:2',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function targets(): HasMany
    {
        return $this->hasMany(ProjectGoalTarget::class, 'goal_id');
    }

    public function recalculateProgress(): void
    {
        /** @var Project|null $project */
        $project = $this->project;
        if (! $project) {
            return;
        }

        /** @var Collection<int, ProjectGoalTarget> $targets */
        $targets = $this->targets()->get();
        if ($targets->isEmpty()) {
            [$totalTasks, $doneTasks] = $this->taskCounts($project);
            $this->progress_percentage = $totalTasks > 0 ? (float) round(($doneTasks / $totalTasks) * 100, 2) : 0.0;
        } else {
            $totalProgress = 0;
            // Cached lazily so a goal with several auto-computed 'tasks' (or
            // 'milestones') targets counts the project's tasks/milestones
            // once per recalculateProgress() call, not once per target —
            // $project doesn't change across this loop. See Architecture
            // Audit §11/§15.
            $taskCounts = null;
            $milestoneCounts = null;
            /** @var ProjectGoalTarget $target */
            foreach ($targets as $target) {
                $targetVal = (float) $target->target_value;
                $currentVal = (float) $target->current_value;
                $startVal = (float) $target->start_value;

                if ($target->target_type === 'tasks' && $targetVal <= 0) {
                    $taskCounts ??= $this->taskCounts($project);
                    [$totalTasks, $doneTasks] = $taskCounts;
                    $target->target_value = max(1, $totalTasks);
                    $target->current_value = $doneTasks;
                    $target->unit = $target->unit ?: 'Tasks';
                    $target->is_completed = ($totalTasks > 0 && $doneTasks >= $totalTasks);
                    $target->save();
                    $ratio = $totalTasks > 0 ? min(1.0, $doneTasks / $totalTasks) : 0.0;
                    $totalProgress += $ratio;
                } elseif ($target->target_type === 'milestones' && $targetVal <= 0) {
                    $milestoneCounts ??= $this->milestoneCounts($project);
                    [$totalMilestones, $completedMilestones] = $milestoneCounts;
                    $target->target_value = max(1, $totalMilestones);
                    $target->current_value = $completedMilestones;
                    $target->unit = $target->unit ?: 'Milestones';
                    $target->is_completed = ($totalMilestones > 0 && $completedMilestones >= $totalMilestones);
                    $target->save();
                    $ratio = $totalMilestones > 0 ? min(1.0, $completedMilestones / $totalMilestones) : 0.0;
                    $totalProgress += $ratio;
                } elseif ($target->target_type === 'hours' && $targetVal <= 0) {
                    $loggedHours = $project->actualHours();
                    $target->current_value = $loggedHours;
                    $target->unit = $target->unit ?: 'Hours';
                    $target->is_completed = ($targetVal > 0 && $loggedHours >= $targetVal);
                    $target->save();
                    $ratio = $targetVal > 0 ? min(1.0, $loggedHours / $targetVal) : 0.0;
                    $totalProgress += $ratio;
                } else {
                    $range = max(0.0001, $targetVal - $startVal);
                    $current = max(0, $currentVal - $startVal);
                    $ratio = min(1.0, $current / $range);
                    $target->is_completed = ($currentVal >= $targetVal);
                    $target->save();
                    $totalProgress += $ratio;
                }
            }
            $this->progress_percentage = (float) round(($totalProgress / max(1, $targets->count())) * 100, 2);
        }

        if ($this->progress_percentage >= 100) {
            $this->status = 'completed';
        } elseif ($this->progress_percentage > 0) {
            $this->status = 'in_progress';
        }
        $this->save();
    }

    /**
     * @return array{0: int, 1: int} [totalTasks, doneTasks]
     */
    private function taskCounts(Project $project): array
    {
        return [$project->tasks()->count(), $project->tasks()->where('status', 'done')->count()];
    }

    /**
     * @return array{0: int, 1: int} [totalMilestones, completedMilestones]
     */
    private function milestoneCounts(Project $project): array
    {
        return [$project->milestones()->count(), $project->milestones()->where('status', 'completed')->count()];
    }
}
