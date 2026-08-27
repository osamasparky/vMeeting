<?php

namespace App\Domains\People\Models;

use App\Domains\Identity\Models\User;
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
        'date_of_birth',
        'hobbies',
        'skills',
        'social_links',
        'notes',
        'work_mode',
    ];

    protected $casts = [
        'date_of_birth' => 'date',
        'social_links' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
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
