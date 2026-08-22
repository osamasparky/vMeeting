<?php

namespace App\Domains\People\Models;

use App\Traits\BelongsToOrganization;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Department extends Model
{
    use BelongsToOrganization;

    protected $fillable = [
        'organization_id',
        'name',
        'parent_department_id',
    ];

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Department::class, 'parent_department_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(Department::class, 'parent_department_id');
    }

    public function teams(): HasMany
    {
        return $this->hasMany(Team::class);
    }

    public function userProfiles(): HasMany
    {
        return $this->hasMany(UserProfile::class);
    }
}
