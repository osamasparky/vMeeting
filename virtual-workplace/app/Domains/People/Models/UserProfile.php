<?php

namespace App\Domains\People\Models;

use App\Traits\BelongsToOrganization;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserProfile extends Model
{
    use BelongsToOrganization;

    protected $primaryKey = 'user_id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'user_id',
        'organization_id',
        'job_title',
        'department_id',
        'team_id',
        'bio',
        'phone',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(\App\Domains\Identity\Models\User::class);
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }
}
