<?php

namespace App\Domains\People\Models;

use App\Traits\BelongsToOrganization;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Team extends Model
{
    use BelongsToOrganization;

    protected $fillable = [
        'organization_id',
        'department_id',
        'name',
    ];

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }
}
