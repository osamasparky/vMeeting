<?php

namespace App\Domains\Projects\Models;

use App\Traits\BelongsToOrganization;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CustomFieldDefinition extends Model
{
    use HasFactory, BelongsToOrganization;

    protected $fillable = [
        'organization_id',
        'project_id',
        'name',
        'field_type',
        'options',
        'is_required',
        'color',
        'sort_order',
    ];

    protected $casts = [
        'options' => 'array',
        'is_required' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function values(): HasMany
    {
        return $this->hasMany(TaskCustomFieldValue::class, 'custom_field_definition_id');
    }
}
