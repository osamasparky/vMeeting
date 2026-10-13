<?php

namespace App\Domains\People\Services;

use App\Domains\People\Models\LeaveRequest;
use App\Domains\Tenancy\Models\OrganizationMember;
use App\Domains\Workspace\Models\Floor;
use App\Domains\Workspace\Services\OfficeTimezone;
use Carbon\CarbonPeriod;
use Illuminate\Support\Carbon;

/**
 * A member's month measured against their home office's schedule.
 *
 * Each day is one of:
 *  - weekend   not a working day of the office
 *  - holiday   an official holiday
 *  - leave     an approved leave covers it
 *  - present   attended; `late_minutes` > 0 when the first arrival came after
 *              the day's start plus the grace period
 *  - absent    a past working day with no attendance at all
 *  - today     today, nothing recorded yet
 *  - upcoming  later this month
 *
 * Worked time is attendance: office presence or task time, overlaps counted
 * once. Expected time is the office's scheduled day on every working day that
 * is not a holiday or leave, up to today. Overtime is work beyond the expected
 * day (all of it on weekends and holidays); shortfall is expected time not worked
 * on past days.
 */
class MonthlyAttendanceReport
{
    public function __construct(
        protected AttendanceService $attendance,
        protected HolidayCalendar $holidays,
        protected OfficeTimezone $timezones,
        protected LeaveService $leaves,
    ) {}

    public function forMember(string $userId, string $organizationId, string $month): array
    {
        $office = $this->leaves->officeFor($userId, $organizationId);
        $tz = $office?->timezone ?: $this->timezones->forMember($userId, $organizationId);
        $workDays = $office?->workDays() ?? Floor::DEFAULT_WORK_DAYS;
        $scheduled = $office?->scheduledSeconds() ?? 8 * 3600;
        $grace = $office?->late_grace_minutes ?? 15;

        $first = Carbon::parse($month.'-01', $tz)->startOfMonth();
        [$from, $to] = [$first->format('Y-m-d'), $first->copy()->endOfMonth()->format('Y-m-d')];
        $today = now()->setTimezone($tz)->format('Y-m-d');

        $holidays = $this->holidays->between($organizationId, $office?->id, $from, $to);
        $leaveDays = $this->approvedLeaveDays($userId, $organizationId, $from, $to);
        $worked = $from <= $today ? $this->attendance->memberDays($userId, $organizationId, $from, min($to, $today), $tz) : [];

        $days = [];
        // elapsed_days: working days up to today that someone was expected in (no leave, no holiday).
        $sum = ['working_days' => 0, 'elapsed_days' => 0, 'present' => 0, 'absent' => 0, 'late' => 0, 'late_minutes' => 0, 'leave' => 0, 'holidays' => 0,
            'worked_seconds' => 0, 'expected_seconds' => 0, 'overtime_seconds' => 0, 'shortfall_seconds' => 0];

        foreach (CarbonPeriod::create($from, $to) as $day) {
            $date = $day->format('Y-m-d');
            $isWorkDay = in_array($day->dayOfWeek, $workDays, true);
            $w = $worked[$date] ?? ['attendance' => 0, 'office' => 0, 'task' => 0, 'first_in' => null];
            $seconds = (int) $w['attendance'];

            $row = ['date' => $date, 'weekday' => $day->dayOfWeek, 'status' => null, 'first_in' => null, 'late_minutes' => 0,
                'worked_seconds' => $seconds, 'office_seconds' => (int) $w['office'], 'task_seconds' => (int) $w['task'],
                'expected_seconds' => 0, 'overtime_seconds' => 0, 'note' => null];

            if ($w['first_in']) {
                $row['first_in'] = Carbon::createFromTimestamp($w['first_in'])->setTimezone($tz)->format('H:i');
            }

            if (! $isWorkDay) {
                $row['status'] = 'weekend';
            } elseif (isset($holidays[$date])) {
                $row['status'] = 'holiday';
                $row['note'] = $holidays[$date];
                $sum['holidays']++;
            } elseif (isset($leaveDays[$date])) {
                $row['status'] = 'leave';
                $row['note'] = $leaveDays[$date];
                $sum['leave']++;
                $sum['working_days']++;
            } else {
                $sum['working_days']++;
                if ($date > $today) {
                    $row['status'] = 'upcoming';
                } else {
                    $row['expected_seconds'] = $scheduled;
                    $sum['elapsed_days']++;
                    if ($seconds > 0) {
                        $row['status'] = 'present';
                        $sum['present']++;
                        $start = $office ? $office->shiftStartOn($date) : Carbon::parse($date.' 09:00', $tz);
                        $lateBy = (int) floor(($w['first_in'] - $start->getTimestamp()) / 60);
                        if ($lateBy > $grace) {
                            $row['late_minutes'] = $lateBy;
                            $sum['late']++;
                            $sum['late_minutes'] += $lateBy;
                        }
                    } else {
                        $row['status'] = $date === $today ? 'today' : 'absent';
                        if ($date !== $today) {
                            $sum['absent']++;
                        }
                    }
                }
            }

            $row['overtime_seconds'] = max(0, $seconds - $row['expected_seconds']);
            if ($date < $today && $row['expected_seconds'] > 0) {
                $sum['shortfall_seconds'] += max(0, $row['expected_seconds'] - $seconds);
            }
            $sum['worked_seconds'] += $seconds;
            $sum['expected_seconds'] += $row['expected_seconds'];
            $sum['overtime_seconds'] += $row['overtime_seconds'];

            $days[] = $row;
        }

        return [
            'month' => $first->format('Y-m'),
            'timezone' => $tz,
            'office' => $office ? ['id' => $office->id, 'name' => $office->name, 'start' => $office->work_start, 'end' => $office->work_end, 'grace' => $grace] : null,
            'summary' => $sum,
            'days' => $days,
        ];
    }

    /**
     * Every active member's monthly summary.
     *
     * @return list<array{user_id: string, name: string, summary: array}>
     */
    public function forOrganization(string $organizationId, string $month): array
    {
        return OrganizationMember::where('organization_id', $organizationId)
            ->where('status', 'active')
            ->whereHas('user', fn ($q) => $q->where('is_super_admin', false))
            ->with('user:id,name')
            ->get()
            ->sortBy(fn ($m) => $m->user?->name)
            ->map(fn ($m) => ['user_id' => $m->user_id, 'name' => $m->user?->name ?? '—', 'summary' => $this->forMember($m->user_id, $organizationId, $month)['summary']])
            ->values()
            ->all();
    }

    /** @return array<string, string> Y-m-d => leave type label, approved leave only. */
    private function approvedLeaveDays(string $userId, string $organizationId, string $from, string $to): array
    {
        $dates = [];
        LeaveRequest::withoutGlobalScopes()
            ->where('organization_id', $organizationId)->where('user_id', $userId)
            ->where('status', LeaveRequest::STATUS_APPROVED)
            ->where('start_date', '<=', $to)->where('end_date', '>=', $from)
            ->get()
            ->each(function (LeaveRequest $leave) use (&$dates, $from, $to) {
                foreach (CarbonPeriod::create(max($leave->start_date->format('Y-m-d'), $from), min($leave->end_date->format('Y-m-d'), $to)) as $d) {
                    $dates[$d->format('Y-m-d')] = $leave->typeLabel();
                }
            });

        return $dates;
    }
}
