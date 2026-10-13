<?php

namespace Tests\Feature;

use App\Domains\Identity\Models\User;
use App\Domains\People\Models\AttendanceSession;
use App\Domains\People\Services\AttendanceService;
use App\Domains\Projects\Models\ActiveTimer;
use App\Domains\Projects\Models\Project;
use App\Domains\Projects\Models\TimeEntry;
use App\Domains\Tenancy\Actions\CreateOrganizationAction;
use App\Domains\Tenancy\Models\Organization;
use App\Domains\Workspace\Models\Floor;
use App\Domains\Workspace\Models\Map;
use App\Domains\Workspace\Models\Room;
use Carbon\Carbon;
use Database\Seeders\PlansSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

/**
 * Office presence and task time must add up to what actually happened:
 * no gaps counted as work, nothing counted twice, every second on the right day.
 */
class AttendanceTimeAccuracyTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected Organization $organization;

    protected Room $room;

    protected Project $project;

    protected AttendanceService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(PlansSeeder::class);
        $this->seed(RolesAndPermissionsSeeder::class);

        $this->travelTo(Carbon::parse('2026-10-13 12:00:00', 'UTC'));

        $this->user = User::factory()->create();
        $this->organization = app(CreateOrganizationAction::class)->execute(['name' => 'Acme Corp'], $this->user);

        $floor = Floor::create(['organization_id' => $this->organization->id, 'name' => 'Main Floor', 'level' => 1]);
        $map = Map::create(['organization_id' => $this->organization->id, 'floor_id' => $floor->id, 'name' => 'Map', 'status' => 'published']);
        $this->room = Room::create([
            'organization_id' => $this->organization->id,
            'map_id' => $map->id,
            'name' => 'Focus Room',
            'type' => 'meeting',
            'access_mode' => 'public',
            'capacity' => 4,
            'bounds' => ['x' => 0, 'y' => 0, 'width' => 10, 'height' => 10],
        ]);

        $this->project = Project::create([
            'organization_id' => $this->organization->id,
            'name' => 'Timekeeping',
            'code' => 'TK-01',
            'owner_id' => $this->user->id,
            'status' => 'active',
            'priority' => 'medium',
        ]);

        $this->service = app(AttendanceService::class);
    }

    private function officeSession(string $start, ?string $end, string $status = 'completed'): AttendanceSession
    {
        return AttendanceSession::create([
            'organization_id' => $this->organization->id,
            'user_id' => $this->user->id,
            'status' => $end ? $status : 'active',
            'started_at' => Carbon::parse($start, 'UTC'),
            'ended_at' => $end ? Carbon::parse($end, 'UTC') : null,
            'last_heartbeat_at' => $end ? Carbon::parse($end, 'UTC') : now(),
            'duration_seconds' => $end ? Carbon::parse($end)->getTimestamp() - Carbon::parse($start)->getTimestamp() : 0,
        ]);
    }

    private function entry(string $start, int $seconds): TimeEntry
    {
        return TimeEntry::create([
            'organization_id' => $this->organization->id,
            'user_id' => $this->user->id,
            'project_id' => $this->project->id,
            'started_at' => Carbon::parse($start, 'UTC'),
            'ended_at' => Carbon::parse($start, 'UTC')->addSeconds($seconds),
            'duration_seconds' => $seconds,
            'entry_type' => TimeEntry::TYPE_MANUAL,
            'status' => TimeEntry::STATUS_DRAFT,
        ]);
    }

    private function day(?string $date = null): array
    {
        return $this->service->getDailyTimesheetData($this->user->id, $this->organization->id, $date);
    }

    public function test_a_heartbeat_after_sleep_does_not_count_the_gap(): void
    {
        $this->travelTo(Carbon::parse('2026-10-13 08:00:00', 'UTC'));
        $first = $this->service->startSession($this->user, $this->organization, $this->room->id);

        $this->travel(5)->minutes();
        $this->service->recordHeartbeat($this->user, $this->organization, $this->room->id);

        // Laptop sleeps for two hours, then the next heartbeat arrives.
        $this->travelTo(Carbon::parse('2026-10-13 10:05:00', 'UTC'));
        $current = $this->service->recordHeartbeat($this->user, $this->organization, $this->room->id);

        $this->assertNotSame($first->id, $current->id);
        $first->refresh();
        $this->assertSame('timed_out', $first->status);
        $this->assertSame(300, $first->duration_seconds);

        $this->assertSame(300, $this->day()['total_office_seconds']);
    }

    public function test_idle_pause_ends_the_session_at_the_last_activity(): void
    {
        $this->travelTo(Carbon::parse('2026-10-13 09:00:00', 'UTC'));
        $this->service->startSession($this->user, $this->organization, $this->room->id);

        // Last input at 09:45; the prompt and grace countdown expire at 10:00.
        $this->travelTo(Carbon::parse('2026-10-13 10:00:00', 'UTC'));
        $session = $this->service->pauseSessionForIdle($this->user, $this->organization, 15 * 60);

        $this->assertSame('idle_paused', $session->status);
        $this->assertSame('2026-10-13 09:45:00', $session->ended_at->format('Y-m-d H:i:s'));
        $this->assertSame(45 * 60, $session->duration_seconds);
    }

    public function test_total_work_is_the_union_of_office_and_task_time_not_their_sum(): void
    {
        $this->officeSession('2026-10-13 09:00:00', '2026-10-13 11:00:00');
        $this->entry('2026-10-13 09:30:00', 3600);   // worked in the office
        $this->entry('2026-10-13 11:30:00', 1800);   // worked after leaving

        $day = $this->day();

        $this->assertSame(7200, $day['total_office_seconds']);
        $this->assertSame(5400, $day['total_task_seconds']);
        $this->assertSame(9000, $day['total_attendance_seconds']);
        $this->assertSame(3600, $day['idle_seconds']);
    }

    public function test_a_running_timer_counts_once_toward_total_work(): void
    {
        $this->officeSession('2026-10-13 11:00:00', null);
        ActiveTimer::create([
            'organization_id' => $this->organization->id,
            'user_id' => $this->user->id,
            'project_id' => $this->project->id,
            'started_at' => Carbon::parse('2026-10-13 11:30:00', 'UTC'),
        ]);

        $day = $this->day();

        $this->assertSame(3600, $day['total_office_seconds']);
        $this->assertSame(1800, $day['total_task_seconds']);
        $this->assertSame(3600, $day['total_attendance_seconds']);
        $this->assertTrue($day['has_running_task']);
    }

    public function test_time_logged_today_for_an_earlier_day_belongs_to_that_day(): void
    {
        $this->entry('2026-10-12 10:00:00', 3600);

        $this->assertSame(0, $this->day()['total_task_seconds']);
        $this->assertSame(3600, $this->day('2026-10-12')['total_task_seconds']);
    }

    public function test_a_session_across_midnight_is_split_between_the_two_days(): void
    {
        $this->officeSession('2026-10-12 23:00:00', '2026-10-13 01:00:00');

        $this->assertSame(3600, $this->day('2026-10-12')['total_office_seconds']);
        $this->assertSame(3600, $this->day()['total_office_seconds']);
    }

    public function test_overlapping_sessions_are_counted_once(): void
    {
        $this->officeSession('2026-10-13 09:00:00', '2026-10-13 10:00:00');
        $this->officeSession('2026-10-13 09:30:00', '2026-10-13 10:30:00');

        $this->assertSame(5400, $this->day()['total_office_seconds']);
    }

    public function test_entering_another_room_closes_every_other_open_session(): void
    {
        $this->officeSession('2026-10-13 11:00:00', null);
        $this->officeSession('2026-10-13 11:10:00', null);

        $this->service->startSession($this->user, $this->organization, $this->room->id);

        $open = AttendanceSession::where('user_id', $this->user->id)->whereNull('ended_at')->get();
        $this->assertCount(1, $open);
        $this->assertSame($this->room->id, $open->first()->room_id);
    }

    public function test_leaving_a_room_keeps_the_user_present_in_the_office(): void
    {
        $this->service->startSession($this->user, $this->organization, $this->room->id);

        $this->service->endSession($this->user, $this->organization, $this->room->id);
        $open = AttendanceSession::where('user_id', $this->user->id)->whereNull('ended_at')->get();
        $this->assertCount(1, $open);
        $this->assertNull($open->first()->room_id);

        // A late "leave" for a room already left changes nothing.
        $this->service->endSession($this->user, $this->organization, $this->room->id);
        $this->assertSame(1, AttendanceSession::where('user_id', $this->user->id)->whereNull('ended_at')->count());

        // Closing the page (no room) ends presence.
        $this->service->endSession($this->user, $this->organization);
        $this->assertSame(0, AttendanceSession::where('user_id', $this->user->id)->whereNull('ended_at')->count());
    }

    public function test_the_office_clock_gets_todays_total_not_the_weeks(): void
    {
        $this->officeSession('2026-10-12 09:00:00', '2026-10-12 10:00:00'); // Monday
        $this->officeSession('2026-10-13 09:00:00', '2026-10-13 10:00:00'); // today

        $this->actingAs($this->user);

        $this->getJson('/api/office/attendance/summary?period=today')
            ->assertOk()
            ->assertJsonPath('today_total_seconds', 3600)
            ->assertJsonPath('total_seconds', 3600);

        $this->getJson('/api/office/attendance/summary?period=week')
            ->assertOk()
            ->assertJsonPath('today_total_seconds', 3600)
            ->assertJsonPath('total_seconds', 7200);
    }

    public function test_days_and_clock_times_follow_the_organization_timezone(): void
    {
        $this->organization->update(['timezone' => 'Asia/Riyadh']); // UTC+3

        // 22:00–23:00 UTC on the 12th is 01:00–02:00 on the 13th in Riyadh.
        $this->officeSession('2026-10-12 22:00:00', '2026-10-12 23:00:00');

        $day = $this->day();
        $this->assertSame('2026-10-13', $day['date']);
        $this->assertSame(3600, $day['total_office_seconds']);
        $this->assertSame('01:00:00 AM', $day['attendance_sessions'][0]['check_in']);
    }

    public function test_mobile_clock_out_books_the_real_duration(): void
    {
        ActiveTimer::create([
            'organization_id' => $this->organization->id,
            'user_id' => $this->user->id,
            'project_id' => $this->project->id,
            'started_at' => now()->subMinutes(30),
        ]);

        Sanctum::actingAs($this->user);
        $this->postJson("/api/v1/organizations/{$this->organization->id}/attendance/clock-out")->assertOk();

        $this->assertSame(1800, (int) TimeEntry::where('user_id', $this->user->id)->value('duration_seconds'));

        $this->getJson("/api/v1/organizations/{$this->organization->id}/attendance/summary")
            ->assertOk()
            ->assertJsonPath('today_total_seconds', 1800);
    }
}
