<?php

namespace Tests\Feature\Hr;

use App\Domains\Identity\Models\User;
use App\Domains\People\Models\AttendanceSession;
use App\Domains\Tenancy\Actions\CreateOrganizationAction;
use App\Domains\Workspace\Models\Floor;
use App\Domains\Workspace\Services\OfficeTimezone;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Hr\Concerns\BuildsHrWorkspace;
use Tests\TestCase;

class WorkScheduleTest extends TestCase
{
    use BuildsHrWorkspace, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->buildHrWorkspace();
    }

    public function test_an_office_defaults_to_sunday_to_thursday_nine_to_five(): void
    {
        $this->assertSame([0, 1, 2, 3, 4], $this->office->workDays());
        $this->assertTrue($this->office->isWorkDay(0));
        $this->assertFalse($this->office->isWorkDay(5));
        $this->assertSame(8 * 3600, $this->office->scheduledSeconds());
        $this->assertSame('2026-10-14 09:00:00 Asia/Riyadh', $this->office->shiftStartOn('2026-10-14')->format('Y-m-d H:i:s e'));
    }

    public function test_a_night_shift_runs_past_midnight(): void
    {
        $this->office->update(['work_start' => '22:00', 'work_end' => '06:00']);

        $this->assertSame(8 * 3600, $this->office->fresh()->scheduledSeconds());
    }

    public function test_the_office_form_stores_the_schedule(): void
    {
        $this->actingAs($this->owner)->post(route('offices.store'), [
            'name' => 'Cairo',
            'timezone' => 'Africa/Cairo',
            'work_days' => ['6', '0', '1', '2', '3'],
            'work_start' => '08:30',
            'work_end' => '16:30',
            'late_grace_minutes' => 10,
        ])->assertSessionHasNoErrors();

        $cairo = Floor::where('name', 'Cairo')->firstOrFail();
        $this->assertEqualsCanonicalizing([6, 0, 1, 2, 3], $cairo->workDays());
        $this->assertSame('08:30', $cairo->work_start);
        $this->assertSame(10, $cairo->late_grace_minutes);

        $this->actingAs($this->owner)->put(route('offices.update', $cairo), [
            'name' => 'Cairo',
            'timezone' => 'Africa/Cairo',
            'work_days' => ['0', '1', '2', '3', '4'],
            'work_start' => '09:00',
            'work_end' => '17:00',
            'late_grace_minutes' => 0,
        ])->assertSessionHasNoErrors();
        $this->assertSame([0, 1, 2, 3, 4], $cairo->fresh()->workDays());
        $this->assertSame(0, $cairo->fresh()->late_grace_minutes);
    }

    public function test_the_office_form_rejects_a_schedule_without_days_or_with_bad_times(): void
    {
        $base = ['name' => 'X', 'timezone' => 'UTC', 'work_start' => '09:00', 'work_end' => '17:00', 'late_grace_minutes' => 15];

        $this->actingAs($this->owner)->post(route('offices.store'), $base + ['work_days' => []])->assertSessionHasErrors('work_days');
        $this->actingAs($this->owner)->post(route('offices.store'), ['work_days' => [0]] + array_merge($base, ['work_start' => '25:00']))->assertSessionHasErrors('work_start');
        $this->actingAs($this->owner)->post(route('offices.store'), ['work_days' => [9]] + $base)->assertSessionHasErrors('work_days.0');
        $this->actingAs($this->owner)->post(route('offices.store'), ['work_days' => [0]] + array_merge($base, ['work_end' => '09:00']))->assertSessionHasErrors('work_end');
    }

    public function test_an_office_created_without_a_schedule_gets_the_default(): void
    {
        $this->actingAs($this->owner)->post(route('offices.store'), ['name' => 'Plain', 'timezone' => 'UTC'])->assertSessionHasNoErrors();

        $plain = Floor::where('name', 'Plain')->firstOrFail();
        $this->assertSame([0, 1, 2, 3, 4], $plain->workDays());
        $this->assertSame('09:00', $plain->work_start);
    }

    public function test_the_home_office_sets_the_members_timezone(): void
    {
        $cairo = Floor::create(['organization_id' => $this->organization->id, 'name' => 'Cairo', 'timezone' => 'Africa/Cairo']);
        $member = $this->memberOf($this->employee);

        // Last seen in Riyadh, but works for Cairo.
        AttendanceSession::create([
            'organization_id' => $this->organization->id, 'user_id' => $this->employee->id, 'floor_id' => $this->office->id,
            'status' => 'completed', 'started_at' => now()->subHour(), 'ended_at' => now(),
        ]);
        $this->assertSame('Asia/Riyadh', app(OfficeTimezone::class)->forMember($this->employee->id, $this->organization->id));

        $member->update(['home_floor_id' => $cairo->id]);
        $this->assertSame('Africa/Cairo', app(OfficeTimezone::class)->forMember($this->employee->id, $this->organization->id));
    }

    public function test_the_member_form_sets_a_home_office_of_the_same_organization_only(): void
    {
        $member = $this->memberOf($this->employee);
        $payload = [
            'name' => $this->employee->name,
            'email' => $this->employee->email,
            'role_id' => $member->role_id,
            'status' => 'active',
        ];

        $this->actingAs($this->owner)->put(route('organization.members.update', $member), $payload + ['home_floor_id' => $this->office->id])
            ->assertSessionHasNoErrors();
        $this->assertSame($this->office->id, $member->fresh()->home_floor_id);

        $other = app(CreateOrganizationAction::class)->execute(['name' => 'Other'], User::factory()->create());
        $foreign = Floor::create(['organization_id' => $other->id, 'name' => 'Theirs']);
        $this->actingAs($this->owner)->put(route('organization.members.update', $member), $payload + ['home_floor_id' => $foreign->id])
            ->assertSessionHasErrors('home_floor_id');
        $this->assertSame($this->office->id, $member->fresh()->home_floor_id);
    }
}
