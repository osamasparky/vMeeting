<?php

namespace App\Domains\Projects\Models;

use App\Traits\BelongsToOrganization;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class ProjectLabel extends Model
{
    use BelongsToOrganization;

    /** Design-system tones a label may use (--ula-tone-{tone}-bg/fg). */
    public const TONES = ['palm', 'gold', 'terracotta', 'sand', 'stone'];

    protected $fillable = [
        'organization_id',
        'project_id',
        'name',
        'tone',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function tasks(): BelongsToMany
    {
        return $this->belongsToMany(Task::class, 'task_labels', 'label_id', 'task_id');
    }
}
