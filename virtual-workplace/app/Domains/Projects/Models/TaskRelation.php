<?php

namespace App\Domains\Projects\Models;

use App\Traits\BelongsToOrganization;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A non-scheduling link between two tasks. "relates_to" reads the same from
 * both ends; "duplicate_of" reads "duplicate of X" on the task and
 * "duplicated by Y" on the other one.
 */
class TaskRelation extends Model
{
    use BelongsToOrganization;

    public const TYPES = ['relates_to', 'duplicate_of'];

    protected $fillable = ['organization_id', 'task_id', 'related_task_id', 'type', 'created_by'];

    /** @return BelongsTo<Task, $this> */
    public function task(): BelongsTo
    {
        return $this->belongsTo(Task::class);
    }

    /** @return BelongsTo<Task, $this> */
    public function relatedTask(): BelongsTo
    {
        return $this->belongsTo(Task::class, 'related_task_id');
    }
}
