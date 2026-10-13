<?php

namespace App\Domains\People\Services;

use App\Domains\People\Models\OfficeHoliday;
use Carbon\CarbonPeriod;

/**
 * Which dates are official holidays for an office: its own holidays plus the
 * organization-wide ones.
 */
class HolidayCalendar
{
    /**
     * @return array<string, string> Y-m-d => holiday name, for dates in [$from, $to].
     */
    public function between(string $organizationId, ?string $officeId, string $from, string $to): array
    {
        $holidays = OfficeHoliday::withoutGlobalScopes()
            ->where('organization_id', $organizationId)
            ->where(fn ($q) => $q->whereNull('floor_id')->when($officeId, fn ($q) => $q->orWhere('floor_id', $officeId)))
            ->where('start_date', '<=', $to)
            ->where('end_date', '>=', $from)
            ->orderBy('start_date')
            ->get();

        $dates = [];
        foreach ($holidays as $holiday) {
            $start = max($holiday->start_date->format('Y-m-d'), $from);
            $end = min($holiday->end_date->format('Y-m-d'), $to);
            foreach (CarbonPeriod::create($start, $end) as $day) {
                $dates[$day->format('Y-m-d')] ??= $holiday->name;
            }
        }

        return $dates;
    }
}
