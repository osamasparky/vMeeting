<?php

namespace App\Domains\Projects\Models;

use App\Domains\Identity\Models\User;
use App\Traits\BelongsToOrganization;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProjectMember extends Model
{
    use BelongsToOrganization;

    protected $fillable = [
        'organization_id',
        'project_id',
        'user_id',
        'project_role',
        'cost_rate',
        'billing_rate',
    ];

    protected $casts = [
        'cost_rate' => 'decimal:2',
        'billing_rate' => 'decimal:2',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
