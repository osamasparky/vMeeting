<?php

namespace App\Domains\Workspace\Models;

use App\Domains\Tenancy\Models\Organization;
use App\Domains\Tenancy\Models\OrganizationMember;
use App\Traits\Auditable;
use App\Traits\BelongsToOrganization;
use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Carbon;

class Floor extends Model
{
    use Auditable, BelongsToOrganization, HasUuid;

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'organization_id',
        'name',
        'city_location',
        'timezone',
        'work_days',
        'work_start',
        'work_end',
        'late_grace_minutes',
        'description',
        'is_default',
        'order',
    ];

    protected $casts = [
        'order' => 'integer',
        'is_default' => 'boolean',
        'work_days' => 'array',
        'late_grace_minutes' => 'integer',
    ];

    /** Sunday–Thursday, the working week in Egypt and Saudi Arabia. Carbon numbering: 0 = Sunday. */
    public const DEFAULT_WORK_DAYS = [0, 1, 2, 3, 4];

    /**
     * Every office has a timezone: working hours are counted in its local
     * time. One created without an explicit choice takes its organization's.
     */
    protected static function booted(): void
    {
        static::saving(function (Floor $floor) {
            if (! self::isValidTimezone($floor->timezone)) {
                $orgTz = $floor->organization_id
                    ? Organization::whereKey($floor->organization_id)->value('timezone')
                    : null;
                $floor->timezone = self::isValidTimezone($orgTz) ? $orgTz : 'UTC';
            }
        });
    }

    public static function isValidTimezone(?string $tz): bool
    {
        return $tz !== null && $tz !== '' && in_array($tz, timezone_identifiers_list(), true);
    }

    // ── Work schedule ──

    /** @return list<int> Carbon day-of-week numbers (0 = Sunday … 6 = Saturday). */
    public function workDays(): array
    {
        $days = is_array($this->work_days) ? array_map('intval', $this->work_days) : self::DEFAULT_WORK_DAYS;

        return array_values(array_unique(array_filter($days, fn ($d) => $d >= 0 && $d <= 6)));
    }

    public function isWorkDay(int $dayOfWeek): bool
    {
        return in_array($dayOfWeek, $this->workDays(), true);
    }

    /** Length of the scheduled working day; a shift ending at or before it starts runs past midnight. */
    public function scheduledSeconds(): int
    {
        [$start, $end] = [$this->minutesOf($this->work_start ?? '09:00'), $this->minutesOf($this->work_end ?? '17:00')];

        return (($end > $start ? $end : $end + 24 * 60) - $start) * 60;
    }

    /** When the working day starts on $date (Y-m-d), in the office's local time. */
    public function shiftStartOn(string $date): Carbon
    {
        return Carbon::parse($date.' '.($this->work_start ?? '09:00'), $this->timezone ?: 'UTC');
    }

    private function minutesOf(string $hhmm): int
    {
        [$h, $m] = array_map('intval', explode(':', $hhmm) + [0, 0]);

        return $h * 60 + $m;
    }

    // ── Relationships ──

    /** @return HasMany<Map, $this> */
    public function maps(): HasMany
    {
        return $this->hasMany(Map::class);
    }

    /** @return HasOne<Map, $this> */
    public function activeMap(): HasOne
    {
        return $this->hasOne(Map::class)->where('status', 'published')->latestOfMany();
    }

    public function rooms(): HasManyThrough
    {
        return $this->hasManyThrough(Room::class, Map::class);
    }

    /** @return BelongsToMany<OrganizationMember, $this> */
    public function members(): BelongsToMany
    {
        return $this->belongsToMany(OrganizationMember::class, 'member_office_access', 'floor_id', 'organization_member_id')
            ->withTimestamps();
    }
}
