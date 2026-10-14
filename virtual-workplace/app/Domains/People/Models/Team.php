<?php

namespace App\Domains\People\Models;

use App\Traits\BelongsToOrganization;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Team extends Model
{
    use BelongsToOrganization;

    protected $fillable = [
        'organization_id',
        'department_id',
        'name',
    ];

    /** @return BelongsTo<Department, $this> */
    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    /** @return HasMany<UserProfile, $this> */
    public function userProfiles(): HasMany
    {
        return $this->hasMany(UserProfile::class);
    }
}
