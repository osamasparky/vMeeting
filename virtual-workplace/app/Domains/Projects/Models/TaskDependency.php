<?php

namespace App\Domains\Projects\Models;

use App\Traits\BelongsToOrganization;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TaskDependency extends Model
{
    use BelongsToOrganization;

    public const TYPE_FINISH_TO_START = 'finish_to_start';

    public const TYPE_START_TO_START = 'start_to_start';

    public const TYPE_FINISH_TO_FINISH = 'finish_to_finish';

    protected $fillable = [
        'organization_id',
        'task_id',
        'depends_on_task_id',
        'dependency_type',
    ];

    public function task(): BelongsTo
    {
        return $this->belongsTo(Task::class, 'task_id');
    }

    public function dependsOnTask(): BelongsTo
    {
        return $this->belongsTo(Task::class, 'depends_on_task_id');
    }
}
