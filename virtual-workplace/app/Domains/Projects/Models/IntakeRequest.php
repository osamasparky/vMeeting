<?php

namespace App\Domains\Projects\Models;

use App\Traits\BelongsToOrganization;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** A request submitted from outside the team, waiting to be triaged. */
class IntakeRequest extends Model
{
    use BelongsToOrganization;

    public const STATUSES = ['pending', 'accepted', 'declined', 'snoozed', 'duplicate'];

    protected $fillable = [
        'organization_id', 'project_id', 'title', 'description', 'requester_name', 'requester_email',
        'status', 'snoozed_until', 'task_id', 'duplicate_of_task_id', 'decline_reason', 'reviewed_by', 'reviewed_at',
    ];

    protected $casts = ['snoozed_until' => 'date', 'reviewed_at' => 'datetime'];

    /** @return BelongsTo<Project, $this> */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    /** @return BelongsTo<Task, $this> */
    public function task(): BelongsTo
    {
        return $this->belongsTo(Task::class);
    }

    /** @return BelongsTo<Task, $this> */
    public function duplicateOf(): BelongsTo
    {
        return $this->belongsTo(Task::class, 'duplicate_of_task_id');
    }

    /** Needs a decision now: pending, or snoozed until today or earlier. */
    public function scopeOpen(Builder $query): Builder
    {
        return $query->where(fn ($q) => $q->where('status', 'pending')
            ->orWhere(fn ($s) => $s->where('status', 'snoozed')->whereDate('snoozed_until', '<=', now()->toDateString())));
    }
}
