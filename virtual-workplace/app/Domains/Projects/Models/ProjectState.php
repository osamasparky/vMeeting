<?php

namespace App\Domains\Projects\Models;

use App\Traits\BelongsToOrganization;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * A project's own workflow state, e.g. "Design review" in the review group.
 * The group decides behaviour (approval, completion, reports).
 */
class ProjectState extends Model
{
    use BelongsToOrganization;

    /** Standard states created for every new project. */
    public const DEFAULTS = [
        ['Backlog', 'backlog'],
        ['Ready', 'ready'],
        ['In Progress', 'in_progress'],
        ['Review', 'review'],
        ['Done', 'done'],
    ];

    protected $fillable = ['organization_id', 'project_id', 'name', 'group', 'position', 'is_default'];

    protected $casts = ['position' => 'integer', 'is_default' => 'boolean'];

    /** @return BelongsTo<Project, $this> */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    /** @return HasMany<Task, $this> */
    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class, 'state_id');
    }

    /** Standard names are stored in English and shown in the reader's language. */
    public function displayName(): string
    {
        return __($this->name);
    }

    /** qa tasks live in review states when a project has no QA state of its own. */
    public static function groupsMatch(string $stateGroup, string $status): bool
    {
        return $stateGroup === $status || ($status === Task::STATUS_QA && $stateGroup === Task::STATUS_REVIEW);
    }

    /** Create the standard states for a project that has none yet. */
    public static function ensureDefaults(Project $project): void
    {
        if ($project->states()->exists()) {
            return;
        }
        foreach (self::DEFAULTS as $i => [$name, $group]) {
            $project->states()->create([
                'organization_id' => $project->organization_id,
                // Stored in English; shown translated (see displayName()).
                'name' => $name,
                'group' => $group,
                'position' => $i,
                'is_default' => $group === Task::STATUS_BACKLOG,
            ]);
        }
    }

    /** The state a task with this status should sit in when none is chosen. */
    public static function defaultFor(string $projectId, string $status): ?self
    {
        $states = self::where('project_id', $projectId)->orderBy('position')->get();

        return $states->firstWhere('group', $status)
            ?? ($status === Task::STATUS_QA ? $states->firstWhere('group', Task::STATUS_REVIEW) : null);
    }
}
