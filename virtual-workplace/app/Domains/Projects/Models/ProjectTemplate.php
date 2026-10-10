<?php

namespace App\Domains\Projects\Models;

use App\Traits\BelongsToOrganization;
use Illuminate\Database\Eloquent\Model;

class ProjectTemplate extends Model
{
    use BelongsToOrganization;

    protected $fillable = ['organization_id', 'name', 'description', 'payload', 'created_by'];

    protected $casts = ['payload' => 'array'];
}
