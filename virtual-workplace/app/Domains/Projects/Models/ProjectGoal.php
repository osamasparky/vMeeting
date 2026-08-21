<?php

namespace App\Domains\Projects\Models;

use App\Domains\Identity\Models\User;
use App\Traits\BelongsToOrganization;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProjectGoal extends Model
{
    use HasFactory, BelongsToOrganization;

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
        $targets = $this->targets()->get();
        if ($targets->isEmpty()) {
            return;
        }

        $totalProgress = 0;
        foreach ($targets as $target) {
            $range = max(0.0001, $target->target_value - $target->start_value);
            $current = max(0, $target->current_value - $target->start_value);
            $ratio = min(1.0, $current / $range);
            $totalProgress += $ratio;
        }

        $this->progress_percentage = round(($totalProgress / $targets->count()) * 100, 2);
        if ($this->progress_percentage >= 100) {
            $this->status = 'completed';
        }
        $this->save();
    }
}
