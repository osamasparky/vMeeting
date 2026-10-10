<?php

namespace App\Domains\Projects\Models;

use App\Traits\BelongsToOrganization;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class TaskTemplate extends Model
{
    use BelongsToOrganization;

    /** Payload keys a task template may carry. */
    public const FIELDS = ['title', 'description', 'priority', 'task_type', 'estimated_hours', 'story_points', 'checklist', 'labels'];

    protected $fillable = ['organization_id', 'project_id', 'name', 'payload', 'created_by'];

    protected $casts = ['payload' => 'array'];

    /** Templates usable in a project: its own plus organization-wide ones. */
    public function scopeAvailableFor(Builder $query, Project $project): Builder
    {
        return $query->where('organization_id', $project->organization_id)
            ->where(fn ($q) => $q->where('project_id', $project->id)->orWhereNull('project_id'));
    }
}
