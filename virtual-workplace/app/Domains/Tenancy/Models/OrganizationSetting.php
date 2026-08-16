<?php

namespace App\Domains\Tenancy\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrganizationSetting extends Model
{
    protected $primaryKey = 'organization_id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'organization_id',
        'branding',
        'policies',
    ];

    protected $casts = [
        'branding' => 'array',
        'policies' => 'array',
    ];

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }
}
