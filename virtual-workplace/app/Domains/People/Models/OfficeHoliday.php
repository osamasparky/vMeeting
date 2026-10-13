<?php

namespace App\Domains\People\Models;

use App\Domains\Workspace\Models\Floor;
use App\Traits\BelongsToOrganization;
use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * An official holiday for one office, or for all of the organization's
 * offices when floor_id is null. Inclusive date range.
 */
class OfficeHoliday extends Model
{
    use BelongsToOrganization, HasUuid;

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'organization_id',
        'floor_id',
        'name',
        'start_date',
        'end_date',
    ];

    protected $casts = [
        'start_date' => 'date:Y-m-d',
        'end_date' => 'date:Y-m-d',
    ];

    public function office(): BelongsTo
    {
        return $this->belongsTo(Floor::class, 'floor_id');
    }

    public function days(): int
    {
        return (int) $this->start_date->diffInDays($this->end_date) + 1;
    }
}
