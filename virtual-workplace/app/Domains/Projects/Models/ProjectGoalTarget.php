<?php

namespace App\Domains\Projects\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProjectGoalTarget extends Model
{
    use HasFactory;

    protected $fillable = [
        'goal_id',
        'title',
        'target_type',
        'auto_track',
        'start_value',
        'target_value',
        'current_value',
        'unit',
        'is_completed',
    ];

    protected $casts = [
        'start_value' => 'decimal:2',
        'target_value' => 'decimal:2',
        'current_value' => 'decimal:2',
        'auto_track' => 'boolean',
        'is_completed' => 'boolean',
    ];

    public const AUTO_TYPES = ['tasks', 'milestones', 'hours'];

    /** @return BelongsTo<ProjectGoal, $this> */
    public function goal(): BelongsTo
    {
        return $this->belongsTo(ProjectGoal::class, 'goal_id');
    }
}
