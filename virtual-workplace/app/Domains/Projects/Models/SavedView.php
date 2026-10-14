<?php

namespace App\Domains\Projects\Models;

use App\Domains\Identity\Models\User;
use App\Traits\BelongsToOrganization;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SavedView extends Model
{
    use BelongsToOrganization;

    public const LAYOUTS = ['kanban', 'tasks', 'calendar'];

    /** Filter keys the hub understands (see getHubFilterState in hub.blade.php). */
    public const FILTER_KEYS = ['q', 'assignee', 'priority', 'due', 'milestone', 'label'];

    protected $fillable = [
        'organization_id',
        'project_id',
        'user_id',
        'name',
        'layout',
        'filters',
        'is_shared',
    ];

    protected $casts = [
        'filters' => 'array',
        'is_shared' => 'boolean',
    ];

    /** @return BelongsTo<Project, $this> */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** Views a user can see on a project: their own plus shared ones. */
    public function scopeVisibleTo(Builder $query, string $userId): Builder
    {
        return $query->where(fn ($q) => $q->where('user_id', $userId)->orWhere('is_shared', true));
    }
}
