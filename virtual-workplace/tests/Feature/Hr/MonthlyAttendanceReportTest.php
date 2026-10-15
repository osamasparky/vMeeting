<?php

namespace Tests\Feature\Hr;

use App\Domains\People\Models\AttendanceSession;
use App\Domains\People\Models\LeaveRequest;
use App\Domains\People\Models\OfficeHoliday;
use App\Domains\People\Services\MonthlyAttendanceReport;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\Feature\Hr\Concerns\BuildsHrWorkspace;
use Tests\TestCase;

/**
 * October 2026 in a Riyadh office (UTC+3) working Sun–Thu 09:00–17:00, 15
 * minutes' grace. "Today" is Wed 14 Oct, 12:00 in Riyadh.
 */
class MonthlyAttendanceReportTest extends TestCase
{
    use BuildsHrWorkspace, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->travelTo(Carbon::parse('2026-10-14 09:00:00', 'UTC'));
        $this->buildHrWorkspace();
        // Joined in September, so all of October counts.
        $this->memberOf($this->employee)->update(['home_floor_id' => $this->office->id, 'joined_at' => Carbon::parse('2026-09-01', 'UTC')]);
        $this->memberOf($this->owner)->update(['joined_at' => Carbon::parse('2026-09-01', 'UTC')]);
    }

    /** A session in Riyadh local time. */
    private function present(string $date, string $from, string $to): void
    {
        AttendanceSession::create([
            'organization_id' => $this->organization->id, 'user_id' => $this->employee->id, 'floor_id' => $this->office->id,
            'status' => 'completed',
            'started_at' => Carbon::parse("$date $from", 'Asia/Riyadh')->utc(),
            'ended_at' => Carbon::parse("$date $to", 'Asia/Riyadh')->utc(),
            'last_heartbeat_at' => Carbon::parse("$date $to", 'Asia/Riyadh')->utc(),
        ]);
    }

    private function seedMonth(): void
    {
        $this->present('2026-10-01', '09:00', '17:00'); // Thu: on time, full day
        $this->present('2026-10-04', '09:30', '17:00'); // Sun: 30 min late
        $this->present('2026-10-05', '09:10', '17:00'); // Mon: within grace
        // Tue 6: absent
        LeaveRequest::create(['organization_id' => $this->organization->id, 'user_id' => $this->employee->id, 'type' => 'sick',
            'start_date' => '2026-10-07', 'end_date' => '2026-10-07', 'days' => 1, 'status' => 'approved']);
        OfficeHoliday::create(['organization_id' => $this->organization->id, 'name' => 'Founding Day', 'start_date' => '2026-10-08', 'end_date' => '2026-10-08']);
        $this->present('2026-10-09', '10:00', '12:00'); // Fri (weekend): 2 h overtime
        // Sun 11 – Tue 13: absent; Wed 14 (today): nothing yet
    }

    public function test_each_day_is_classified_against_the_office_schedule(): void
    {
        $this->seedMonth();

        $report = app(MonthlyAttendanceReport::class)->forMember($this->employee->id, $this->organization->id, '2026-10');
        $byDate = collect($report['days'])->keyBy('date');

        $this->assertSame('Asia/Riyadh', $report['timezone']);
        $this->assertCount(31, $report['days']);

        $this->assertSame(['present', '09:00', 0], [$byDate['2026-10-01']['status'], $byDate['2026-10-01']['first_in'], $byDate['2026-10-01']['late_minutes']]);
        $this->assertSame(['present', '09:30', 30], [$byDate['2026-10-04']['status'], $byDate['2026-10-04']['first_in'], $byDate['2026-10-04']['late_minutes']]);
        $this->assertSame(['present', 0], [$byDate['2026-10-05']['status'], $byDate['2026-10-05']['late_minutes']]);
        $this->assertSame('absent', $byDate['2026-10-06']['status']);
        $this->assertSame('leave', $byDate['2026-10-07']['status']);
        $this->assertSame(['holiday', 'Founding Day'], [$byDate['2026-10-08']['status'], $byDate['2026-10-08']['note']]);
        $this->assertSame(['weekend', 7200, 7200], [$byDate['2026-10-09']['status'], $byDate['2026-10-09']['worked_seconds'], $byDate['2026-10-09']['overtime_seconds']]);
        $this->assertSame('weekend', $byDate['2026-10-10']['status']);
        $this->assertSame('absent', $byDate['2026-10-13']['status']);
        $this->assertSame('today', $byDate['2026-10-14']['status']);
        $this->assertSame('upcoming', $byDate['2026-10-15']['status']);
    }

    public function test_the_month_summary_adds_up(): void
    {
        $this->seedMonth();

        $s = app(MonthlyAttendanceReport::class)->forMember($this->employee->id, $this->organization->id, '2026-10')['summary'];

        $this->assertSame(20, $s['working_days']);      // 21 Sun–Thu days minus the holiday
        $this->assertSame(8, $s['elapsed_days']);       // 3 present + 4 absent + today
        $this->assertSame(3, $s['present']);
        $this->assertSame(4, $s['absent']);             // 6, 11, 12, 13
        $this->assertSame(1, $s['late']);
        $this->assertSame(30, $s['late_minutes']);
        $this->assertSame(1, $s['leave']);
        $this->assertSame(1, $s['holidays']);
        $this->assertSame(8 * 3600 + (7 * 3600 + 30 * 60) + (7 * 3600 + 50 * 60) + 2 * 3600, $s['worked_seconds']);
        $this->assertSame(8 * 8 * 3600, $s['expected_seconds']); // 3 present + 4 absent + today
        $this->assertSame(2 * 3600, $s['overtime_seconds']);
        $this->assertSame(30 * 60 + 10 * 60 + 4 * 8 * 3600, $s['shortfall_seconds']);
    }

    public function test_an_employee_sees_only_their_own_report(): void
    {
        $this->seedMonth();
        $this->present('2026-10-01', '09:00', '17:00');

        // Asking for the owner's report or the team summary still returns their own.
        $this->actingAs($this->employee)->get(route('hr.panel', ['panel' => 'attendance', 'month' => '2026-10', 'member' => $this->owner->id]))
            ->assertOk()->assertDontSee(__('All employees (summary)'))->assertSee('Founding Day');
        $this->actingAs($this->employee)->get(route('hr.panel', ['panel' => 'attendance', 'month' => '2026-10', 'member' => 'all']))
            ->assertOk()->assertSee('Founding Day');
    }

    public function test_the_owner_sees_the_team_and_any_member(): void
    {
        $this->seedMonth();

        $this->actingAs($this->owner)->get(route('hr.panel', ['panel' => 'attendance', 'month' => '2026-10', 'member' => 'all']))
            ->assertOk()->assertSee($this->employee->name)->assertSee($this->owner->name);
        $this->actingAs($this->owner)->get(route('hr.panel', ['panel' => 'attendance', 'month' => '2026-10', 'member' => $this->employee->id]))
            ->assertOk()->assertSee('Founding Day');
    }

    public function test_the_report_exports_to_excel(): void
    {
        $this->seedMonth();

        $this->actingAs($this->owner)->get(route('hr.attendance.export', ['month' => '2026-10', 'member' => $this->employee->id]))
            ->assertOk()->assertDownload();
        $this->actingAs($this->owner)->get(route('hr.attendance.export', ['month' => '2026-10', 'member' => 'all']))
            ->assertOk()->assertDownload();
    }

    public function test_a_bad_month_falls_back_to_the_current_one(): void
    {
        $this->actingAs($this->employee)->get(route('hr.panel', ['panel' => 'attendance', 'month' => '2026-13']))
            ->assertOk()->assertSee('value="2026-10"', false);
    }

    public function test_days_before_the_office_went_live_are_not_absences(): void
    {
        $this->office->update(['attendance_starts_on' => '2026-10-13']);
        $this->present('2026-10-04', '09:00', '10:00'); // trial use before go-live: shown, not counted
        $this->present('2026-10-13', '09:00', '17:00');

        $report = app(MonthlyAttendanceReport::class)->forMember($this->employee->id, $this->organization->id, '2026-10');
        $byDate = collect($report['days'])->keyBy('date');
        $s = $report['summary'];

        $this->assertSame('2026-10-13', $report['tracking_from']);
        $this->assertSame('untracked', $byDate['2026-10-01']['status']);
        $this->assertSame(['untracked', 3600], [$byDate['2026-10-04']['status'], $byDate['2026-10-04']['worked_seconds']]);
        $this->assertSame('weekend', $byDate['2026-10-09']['status']);
        $this->assertSame('untracked', $byDate['2026-10-12']['status']);
        $this->assertSame('present', $byDate['2026-10-13']['status']);
        // Working days counted: 13, 14 (today) and 15–29 upcoming; only the 13th is past.
        $this->assertSame(0, $s['absent']);
        $this->assertSame(1, $s['present']);
        $this->assertSame(2, $s['elapsed_days']);
        $this->assertSame(8 * 3600, $s['worked_seconds']);
        $this->assertSame(0, $s['overtime_seconds']);
    }

    public function test_a_member_who_joined_mid_month_is_counted_from_their_join_day(): void
    {
        $this->memberOf($this->employee)->update(['joined_at' => Carbon::parse('2026-10-12 06:00', 'UTC')]);

        $report = app(MonthlyAttendanceReport::class)->forMember($this->employee->id, $this->organization->id, '2026-10');
        $byDate = collect($report['days'])->keyBy('date');

        $this->assertSame('untracked', $byDate['2026-10-11']['status']);
        $this->assertSame('absent', $byDate['2026-10-12']['status']);
        $this->assertSame(2, $report['summary']['absent'], '12th and 13th');
    }

    public function test_a_later_hire_date_wins_over_the_join_date(): void
    {
        $this->memberOf($this->employee)->update(['hire_date' => '2026-10-13']);

        $report = app(MonthlyAttendanceReport::class)->forMember($this->employee->id, $this->organization->id, '2026-10');

        $this->assertSame('2026-10-13', $report['tracking_from']);
        $this->assertSame(1, $report['summary']['absent']);
    }
}
