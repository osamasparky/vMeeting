<?php

namespace App\Domains\Projects\Models;

use App\Domains\Identity\Models\User;
use App\Traits\BelongsToOrganization;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TaskChecklistItem extends Model
{
    use BelongsToOrganization;

    protected $fillable = [
        'organization_id',
        'task_id',
        'title',
        'is_completed',
        'completed_by',
        'completed_at',
        'order',
    ];

    protected $casts = [
        'is_completed' => 'boolean',
        'completed_at' => 'datetime',
        'order' => 'integer',
    ];

    public function task(): BelongsTo
    {
        return $this->belongsTo(Task::class);
    }

    public function completedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'completed_by');
    }
}
