<?php

namespace App\Domains\Projects\Models;

use App\Traits\BelongsToOrganization;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProjectSprint extends Model
{
    use HasFactory, BelongsToOrganization;

    protected $fillable = [
        'organization_id',
        'project_id',
        'name',
        'start_date',
        'end_date',
        'status',
        'planned_points',
        'completed_points',
        'retrospective_notes',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'planned_points' => 'integer',
        'completed_points' => 'integer',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class, 'sprint_id');
    }
}
