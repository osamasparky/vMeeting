<?php

namespace Tests\Feature\Hr;

use App\Domains\People\Models\AttendanceSession;
use App\Domains\Projects\Models\Project;
use App\Domains\Projects\Models\TimeEntry;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Laravel\Sanctum\Sanctum;
use Tests\Feature\Hr\Concerns\BuildsHrWorkspace;
use Tests\TestCase;

/**
 * Times a member types and "today" both mean office time (Riyadh, UTC+3),
 * not the server's UTC.
 */
class OfficeLocalTimeTest extends TestCase
{
    use BuildsHrWorkspace, RefreshDatabase;

    protected Project $project;

    protected function setUp(): void
    {
        parent::setUp();
        $this->buildHrWorkspace();
        $this->memberOf($this->owner)->update(['home_floor_id' => $this->office->id]);
        $this->project = Project::create([
            'organization_id' => $this->organization->id, 'name' => 'Portal', 'code' => 'P-1',
            'owner_id' => $this->owner->id, 'status' => 'active', 'priority' => 'medium',
        ]);
    }

    private function logManual(string $from, string $to)
    {
        Sanctum::actingAs($this->owner);

        return $this->postJson("/api/v1/organizations/{$this->organization->id}/time/entries/manual", [
            'project_id' => $this->project->id, 'started_at' => $from, 'ended_at' => $to,
        ]);
    }

    public function test_a_typed_time_without_an_offset_is_office_time(): void
    {
        // What a datetime-local field sends: 09:00 in the office = 06:00 UTC.
        $this->logManual('2026-10-11T09:00', '2026-10-11T13:00')->assertCreated();

        $entry = TimeEntry::firstOrFail();
        $this->assertSame('2026-10-11 06:00:00', $entry->started_at->utc()->format('Y-m-d H:i:s'));
        $this->assertSame(4 * 3600, $entry->duration_seconds);
    }

    public function test_a_typed_time_with_an_offset_keeps_it(): void
    {
        $this->logManual('2026-10-11T09:05:00+03:00', '2026-10-11T16:30:00+03:00')->assertCreated();

        $this->assertSame('2026-10-11 06:05:00', TimeEntry::firstOrFail()->started_at->utc()->format('Y-m-d H:i:s'));
    }

    public function test_editing_an_entry_reads_the_new_times_in_office_time(): void
    {
        $this->logManual('2026-10-11T09:00', '2026-10-11T13:00')->assertCreated();
        $entry = TimeEntry::firstOrFail();

        $this->patchJson("/api/v1/organizations/{$this->organization->id}/time/entries/{$entry->id}", [
            'started_at' => '2026-10-11T10:00', 'ended_at' => '2026-10-11T12:00',
        ])->assertOk();

        $entry->refresh();
        $this->assertSame('2026-10-11 07:00:00', $entry->started_at->utc()->format('Y-m-d H:i:s'));
        $this->assertSame(2 * 3600, $entry->duration_seconds);
    }

    public function test_the_daily_summary_without_a_date_is_todays_office_date(): void
    {
        // 22:30 UTC on the 13th is 01:30 on the 14th in Riyadh.
        $this->travelTo(Carbon::parse('2026-10-13 22:30:00', 'UTC'));
        AttendanceSession::create([
            'organization_id' => $this->organization->id, 'user_id' => $this->owner->id, 'floor_id' => $this->office->id,
            'status' => 'completed', 'started_at' => now()->subMinutes(20), 'ended_at' => now()->subMinutes(10), 'last_heartbeat_at' => now()->subMinutes(10),
        ]);

        $this->actingAs($this->owner)->getJson('/api/timesheets/daily-summary')
            ->assertOk()
            ->assertJsonPath('date', '2026-10-14')
            ->assertJsonPath('total_office_seconds', 600);

        $this->actingAs($this->owner)->getJson('/api/timesheets/daily-summary?user_id=all')
            ->assertOk()
            ->assertJsonPath('date', '2026-10-14')
            ->assertJsonPath('total_office_seconds', 600);
    }
}
