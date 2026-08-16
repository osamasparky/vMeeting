<?php

namespace App\Domains\People\Models;

use App\Traits\BelongsToOrganization;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Avatar extends Model
{
    use BelongsToOrganization;

    protected $fillable = [
        'user_id',
        'organization_id',
        'sprite_config',
    ];

    protected $casts = [
        'sprite_config' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(\App\Domains\Identity\Models\User::class);
    }
}
