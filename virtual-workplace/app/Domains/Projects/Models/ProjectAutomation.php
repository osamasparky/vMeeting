<?php

namespace App\Domains\Projects\Models;

use App\Domains\Identity\Models\User;
use App\Traits\BelongsToOrganization;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A project rule: when `trigger` fires for a task that meets every condition,
 * run the actions in order. See AutomationEngine.
 */
class ProjectAutomation extends Model
{
    use BelongsToOrganization;

    public const TRIGGERS = ['task_created', 'state_changed', 'priority_changed', 'assignee_changed', 'label_added'];

    /** Condition key => what it compares against. All present conditions must hold. */
    public const CONDITIONS = ['state_id', 'priority', 'label_id', 'assignee'];

    public const ACTIONS = ['set_state', 'set_priority', 'add_assignee', 'add_label', 'add_watcher', 'notify', 'set_due_in_days'];

    protected $fillable = [
        'organization_id',
        'project_id',
        'created_by',
        'name',
        'trigger',
        'conditions',
        'actions',
        'is_active',
    ];

    protected $casts = [
        'conditions' => 'array',
        'actions' => 'array',
        'is_active' => 'boolean',
        'run_count' => 'integer',
        'last_run_at' => 'datetime',
    ];

    /** @return BelongsTo<Project, $this> */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    /** @return BelongsTo<User, $this> */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
