<?php

namespace Tests\Feature\Hr;

use App\Domains\Identity\Models\User;
use App\Domains\Notifications\Models\WorkplaceNotification;
use App\Domains\People\Models\LeaveRequest;
use App\Domains\People\Models\OfficeHoliday;
use App\Domains\People\Services\LeaveService;
use App\Domains\Tenancy\Actions\CreateOrganizationAction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Hr\Concerns\BuildsHrWorkspace;
use Tests\TestCase;

/**
 * 2026-10-18 is a Sunday. The office works Sun–Thu, so Sun 18 – Sat 24 holds
 * five working days.
 */
class LeaveRequestTest extends TestCase
{
    use BuildsHrWorkspace, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->travelTo(now()->setDate(2026, 10, 14)->setTime(9, 0));
        $this->buildHrWorkspace();
        $this->memberOf($this->employee)->update(['home_floor_id' => $this->office->id]);
    }

    private function ask(array $data, ?User $as = null)
    {
        return $this->actingAs($as ?? $this->employee)->post(route('hr.leaves.store'), $data + ['type' => 'annual']);
    }

    public function test_only_working_days_are_counted(): void
    {
        $leaves = app(LeaveService::class);
        $this->assertSame(5, $leaves->workingDays($this->employee->id, $this->organization->id, '2026-10-18', '2026-10-24'));

        OfficeHoliday::create(['organization_id' => $this->organization->id, 'name' => 'Eid', 'start_date' => '2026-10-20', 'end_date' => '2026-10-21']);
        $this->assertSame(3, $leaves->workingDays($this->employee->id, $this->organization->id, '2026-10-18', '2026-10-24'));

        // A different home office schedule: Sat–Wed.
        $this->office->update(['work_days' => [6, 0, 1, 2, 3]]);
        $this->assertSame(3, $leaves->workingDays($this->employee->id, $this->organization->id, '2026-10-18', '2026-10-24')); // Sun, Mon, Sat (Tue, Wed are Eid)
    }

    public function test_an_employee_requests_leave_and_the_owner_is_notified(): void
    {
        $this->ask(['start_date' => '2026-10-18', 'end_date' => '2026-10-24', 'reason' => 'Family trip'])
            ->assertRedirect()->assertSessionHasNoErrors();

        $leave = LeaveRequest::firstOrFail();
        $this->assertSame(5, $leave->days);
        $this->assertSame('pending', $leave->status);
        $this->assertTrue(WorkplaceNotification::where('user_id', $this->owner->id)->where('type', 'leave')->exists());

        $balance = app(LeaveService::class)->balance($this->employee->id, $this->organization->id, 2026);
        $this->assertSame(['year' => 2026, 'entitled' => 21, 'used' => 0, 'pending' => 5, 'remaining' => 16], $balance);
    }

    public function test_a_range_of_weekends_and_holidays_only_is_refused(): void
    {
        $this->ask(['start_date' => '2026-10-23', 'end_date' => '2026-10-24'])->assertSessionHasErrors('start_date');
        $this->assertSame(0, LeaveRequest::count());
    }

    public function test_overlapping_requests_are_refused(): void
    {
        $this->ask(['start_date' => '2026-10-18', 'end_date' => '2026-10-20'])->assertSessionHasNoErrors();
        $this->ask(['start_date' => '2026-10-20', 'end_date' => '2026-10-22', 'type' => 'sick'])->assertSessionHasErrors('start_date');
        $this->assertSame(1, LeaveRequest::count());
    }

    public function test_the_office_sets_the_balance_and_a_member_figure_overrides_it(): void
    {
        $leaves = app(LeaveService::class);
        $this->assertSame(21, $leaves->balance($this->employee->id, $this->organization->id, 2026)['entitled']);

        $this->actingAs($this->owner)->put(route('offices.update', $this->office), [
            'name' => 'Riyadh HQ', 'timezone' => 'Asia/Riyadh', 'annual_leave_days' => 30, 'attendance_starts_on' => '2026-10-15',
        ])->assertSessionHasNoErrors();
        $this->assertSame(30, $leaves->balance($this->employee->id, $this->organization->id, 2026)['entitled']);
        $this->assertSame('2026-10-15', $this->office->fresh()->attendance_starts_on->format('Y-m-d'));

        $this->memberOf($this->employee)->update(['annual_leave_days' => 10]);
        $this->assertSame(10, $leaves->balance($this->employee->id, $this->organization->id, 2026)['entitled']);
    }

    public function test_annual_leave_cannot_exceed_the_balance_but_other_types_can(): void
    {
        $this->memberOf($this->employee)->update(['annual_leave_days' => 3]);

        $this->ask(['start_date' => '2026-10-18', 'end_date' => '2026-10-22'])->assertSessionHasErrors('type');
        $this->ask(['start_date' => '2026-10-18', 'end_date' => '2026-10-22', 'type' => 'unpaid'])->assertSessionHasNoErrors();
    }

    public function test_the_owner_approves_and_the_employee_is_notified(): void
    {
        $this->ask(['start_date' => '2026-10-18', 'end_date' => '2026-10-19']);
        $leave = LeaveRequest::firstOrFail();

        $this->actingAs($this->owner)->post(route('hr.leaves.approve', $leave), ['note' => 'Enjoy'])->assertRedirect();

        $leave->refresh();
        $this->assertSame('approved', $leave->status);
        $this->assertSame($this->owner->id, $leave->reviewed_by);
        $this->assertSame('Enjoy', $leave->review_note);
        $this->assertTrue(WorkplaceNotification::where('user_id', $this->employee->id)->where('type', 'leave')->exists());
        $this->assertSame(2, app(LeaveService::class)->balance($this->employee->id, $this->organization->id, 2026)['used']);

        // Decided once: no second decision.
        $this->actingAs($this->owner)->post(route('hr.leaves.reject', $leave))->assertSessionHasErrors('leave');
        $this->assertSame('approved', $leave->fresh()->status);
    }

    public function test_only_the_owner_decides(): void
    {
        $this->ask(['start_date' => '2026-10-18', 'end_date' => '2026-10-19']);
        $leave = LeaveRequest::firstOrFail();

        $this->actingAs($this->employee)->post(route('hr.leaves.approve', $leave))->assertForbidden();

        $outsider = User::factory()->create();
        app(CreateOrganizationAction::class)->execute(['name' => 'Other'], $outsider);
        $this->actingAs($outsider)->post(route('hr.leaves.approve', $leave))->assertForbidden();

        $this->assertSame('pending', $leave->fresh()->status);
    }

    public function test_a_member_cancels_only_their_own_pending_request(): void
    {
        $this->ask(['start_date' => '2026-10-18', 'end_date' => '2026-10-19']);
        $leave = LeaveRequest::firstOrFail();

        $this->actingAs($this->owner)->post(route('hr.leaves.cancel', $leave))->assertForbidden();

        $this->actingAs($this->employee)->post(route('hr.leaves.cancel', $leave))->assertRedirect();
        $this->assertSame('cancelled', $leave->fresh()->status);

        // Cancelled frees the dates and the balance.
        $this->ask(['start_date' => '2026-10-18', 'end_date' => '2026-10-19'])->assertSessionHasNoErrors();
        $this->assertSame(2, app(LeaveService::class)->balance($this->employee->id, $this->organization->id, 2026)['pending']);
    }

    public function test_a_request_across_new_year_counts_against_each_year(): void
    {
        $this->ask(['start_date' => '2026-12-27', 'end_date' => '2027-01-07'])->assertSessionHasNoErrors();

        // Sun 27 – Thu 31 Dec: 5 days in 2026; Sun 3 – Thu 7 Jan: 5 days in 2027.
        $leaves = app(LeaveService::class);
        $this->assertSame(10, LeaveRequest::firstOrFail()->days);
        $this->assertSame(5, $leaves->balance($this->employee->id, $this->organization->id, 2026)['pending']);
        $this->assertSame(5, $leaves->balance($this->employee->id, $this->organization->id, 2027)['pending']);
    }

    public function test_the_leave_panel_shows_the_queue_to_the_owner_only(): void
    {
        $this->ask(['start_date' => '2026-10-18', 'end_date' => '2026-10-19', 'reason' => 'Wedding']);

        $this->actingAs($this->owner)->get(route('hr.panel', 'leaves'))->assertOk()
            ->assertSee(__('Requests awaiting your decision'))->assertSee('Wedding');
        $this->actingAs($this->employee)->get(route('hr.panel', 'leaves'))->assertOk()
            ->assertDontSee(__('Requests awaiting your decision'))->assertSee(__('My leave requests'));
    }
}
