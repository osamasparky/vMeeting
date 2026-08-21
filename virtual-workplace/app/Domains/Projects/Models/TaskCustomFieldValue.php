<?php

namespace App\Domains\Projects\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TaskCustomFieldValue extends Model
{
    use HasFactory;

    protected $fillable = [
        'task_id',
        'custom_field_definition_id',
        'value_text',
        'value_number',
        'value_date',
        'value_boolean',
        'value_json',
    ];

    protected $casts = [
        'value_number' => 'decimal:4',
        'value_date' => 'datetime',
        'value_boolean' => 'boolean',
        'value_json' => 'array',
    ];

    public function task(): BelongsTo
    {
        return $this->belongsTo(Task::class);
    }

    public function definition(): BelongsTo
    {
        return $this->belongsTo(CustomFieldDefinition::class, 'custom_field_definition_id');
    }

    public function getFormattedValueAttribute()
    {
        $type = $this->definition->field_type ?? 'text';
        return match ($type) {
            'number' => $this->value_number,
            'currency' => ($this->definition->options['currency_symbol'] ?? '$') . ' ' . number_format((float)$this->value_number, 2),
            'date' => $this->value_date ? $this->value_date->format('Y-m-d') : null,
            'checkbox' => (bool)$this->value_boolean,
            'rating' => $this->value_number . ' ⭐',
            'dropdown' => $this->value_text,
            default => $this->value_text,
        };
    }
}
