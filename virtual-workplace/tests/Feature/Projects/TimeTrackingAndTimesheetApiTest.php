<?php

namespace Tests\Feature\Projects;

use App\Domains\Administration\Models\Role;
use App\Domains\Identity\Models\User;
use App\Domains\Projects\Models\ActiveTimer;
use App\Domains\Projects\Models\Project;
use App\Domains\Projects\Models\Task;
use App\Domains\Projects\Models\TimeEntry;
use App\Domains\Projects\Models\Timesheet;
use App\Domains\Tenancy\Actions\CreateOrganizationAction;
use App\Domains\Tenancy\Models\Organization;
use App\Domains\Tenancy\Models\OrganizationMember;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class TimeTrackingAndTimesheetApiTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminA;
    protected User $employeeA;
    protected User $adminB;
    protected Organization $orgA;
    protected Organization $orgB;
    protected Project $projectA;
    protected Task $taskA1;
    protected Task $taskA2;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(\Database\Seeders\PlansSeeder::class);
        $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);

        $action = app(CreateOrganizationAction::class);

        // Org A
        $this->adminA = User::factory()->create(['email' => 'adminA@tenant-a.com']);
        $this->employeeA = User::factory()->create(['email' => 'employeeA@tenant-a.com']);
        $this->orgA = $action->execute(['name' => 'Tenant Alpha Corp'], $this->adminA);

        $employeeRole = Role::where('slug', 'employee')->whereNull('organization_id')->first();
        OrganizationMember::create([
            'organization_id' => $this->orgA->id,
            'user_id' => $this->employeeA->id,
            'role_id' => $employeeRole->id,
            'status' => 'active',
            'cost_rate' => 50.00,
            'billing_rate' => 150.00,
            'weekly_capacity_hours' => 40.00,
            'joined_at' => now(),
        ]);

        $this->projectA = Project::create([
            'organization_id' => $this->orgA->id,
            'name' => 'Project Alpha',
            'owner_id' => $this->adminA->id,
            'status' => 'active',
        ]);

        $this->taskA1 = Task::create([
            'organization_id' => $this->orgA->id,
            'project_id' => $this->projectA->id,
            'title' => 'Task One',
            'reporter_id' => $this->adminA->id,
            'assignee_id' => $this->employeeA->id,
            'status' => 'in_progress',
        ]);

        $this->taskA2 = Task::create([
            'organization_id' => $this->orgA->id,
            'project_id' => $this->projectA->id,
            'title' => 'Task Two',
            'reporter_id' => $this->adminA->id,
            'assignee_id' => $this->employeeA->id,
            'status' => 'ready',
        ]);

        // Org B
        $this->adminB = User::factory()->create(['email' => 'adminB@tenant-b.com']);
        $this->orgB = $action->execute(['name' => 'Tenant Beta LLC'], $this->adminB);
    }

    public function test_timer_concurrency_and_automatic_switching(): void
    {
        Sanctum::actingAs($this->employeeA);

        // 1. Start timer for Task One
        $startRes1 = $this->postJson("/api/v1/organizations/{$this->orgA->id}/time/timer/start", [
            'project_id' => $this->projectA->id,
            'task_id' => $this->taskA1->id,
            'description' => 'Working on task 1',
        ]);

        $startRes1->assertStatus(201)
            ->assertJsonPath('timer.project_id', $this->projectA->id)
            ->assertJsonPath('timer.task_id', $this->taskA1->id);

        $this->assertDatabaseCount('active_timers', 1);
        $this->assertDatabaseCount('time_entries', 0);

        // 2. Start timer for Task Two while timer 1 is running
        $startRes2 = $this->postJson("/api/v1/organizations/{$this->orgA->id}/time/timer/start", [
            'project_id' => $this->projectA->id,
            'task_id' => $this->taskA2->id,
            'description' => 'Switched to task 2',
        ]);

        $startRes2->assertStatus(201)
            ->assertJsonPath('timer.task_id', $this->taskA2->id);

        // Assert strictly 1 active timer in DB and 1 converted completed TimeEntry
        $this->assertDatabaseCount('active_timers', 1);
        $this->assertDatabaseCount('time_entries', 1);

        $this->assertDatabaseHas('time_entries', [
            'project_id' => $this->projectA->id,
            'task_id' => $this->taskA1->id,
            'cost_rate' => 50.00,
            'billing_rate' => 150.00,
        ]);

        // 3. Stop timer 2
        $stopRes = $this->postJson("/api/v1/organizations/{$this->orgA->id}/time/timer/stop");
        $stopRes->assertStatus(200);

        $this->assertDatabaseCount('active_timers', 0);
        $this->assertDatabaseCount('time_entries', 2);
    }

    public function test_manual_time_logging_and_validation(): void
    {
        Sanctum::actingAs($this->employeeA);

        // Invalid: end before start
        $invalidRes = $this->postJson("/api/v1/organizations/{$this->orgA->id}/time/entries/manual", [
            'project_id' => $this->projectA->id,
            'task_id' => $this->taskA1->id,
            'started_at' => now()->toDateTimeString(),
            'ended_at' => now()->subHour()->toDateTimeString(),
        ]);
        $invalidRes->assertStatus(422);

        // Valid manual entry
        $validRes = $this->postJson("/api/v1/organizations/{$this->orgA->id}/time/entries/manual", [
            'project_id' => $this->projectA->id,
            'task_id' => $this->taskA1->id,
            'started_at' => now()->subHours(3)->toDateTimeString(),
            'ended_at' => now()->toDateTimeString(),
            'duration_seconds' => 10800,
            'description' => 'Manual review session',
        ]);

        $validRes->assertStatus(201)
            ->assertJsonPath('time_entry.duration_seconds', 10800)
            ->assertJsonPath('time_entry.entry_type', 'manual');
    }

    public function test_timesheet_submission_approval_and_locking_workflow(): void
    {
        Sanctum::actingAs($this->employeeA);

        // Log 4 hours for today
        $entry = TimeEntry::create([
            'organization_id' => $this->orgA->id,
            'user_id' => $this->employeeA->id,
            'project_id' => $this->projectA->id,
            'task_id' => $this->taskA1->id,
            'started_at' => now()->startOfWeek()->addHours(2),
            'ended_at' => now()->startOfWeek()->addHours(6),
            'duration_seconds' => 14400,
            'cost_rate' => 50.00,
            'billing_rate' => 150.00,
            'status' => 'draft',
        ]);

        // 1. Employee submits weekly timesheet
        $startOfWeek = now()->startOfWeek()->toDateString();
        $endOfWeek = now()->endOfWeek()->toDateString();

        $submitRes = $this->postJson("/api/v1/organizations/{$this->orgA->id}/timesheets/submit", [
            'period_start' => $startOfWeek,
            'period_end' => $endOfWeek,
        ]);

        $submitRes->assertStatus(200)
            ->assertJsonPath('timesheet.status', 'submitted')
            ->assertJsonPath('timesheet.total_hours', '4.00');

        $timesheetId = $submitRes->json('timesheet.id');

        // Assert time entry is now locked in submitted state
        $this->assertEquals('submitted', $entry->fresh()->status);

        // 2. Employee cannot edit a submitted time entry
        $editRes = $this->patchJson("/api/v1/organizations/{$this->orgA->id}/time/entries/{$entry->id}", [
            'description' => 'Attempting forbidden edit',
        ]);
        $editRes->assertStatus(422);

        // 3. Manager/Admin approves timesheet
        Sanctum::actingAs($this->adminA);

        $approveRes = $this->postJson("/api/v1/organizations/{$this->orgA->id}/timesheets/{$timesheetId}/approve");
        $approveRes->assertStatus(200)
            ->assertJsonPath('timesheet.status', 'approved');

        // Assert entry permanently approved
        $this->assertEquals('approved', $entry->fresh()->status);
    }

    public function test_tenant_isolation_on_timesheets(): void
    {
        $timesheet = Timesheet::create([
            'organization_id' => $this->orgA->id,
            'user_id' => $this->employeeA->id,
            'period_start' => now()->startOfWeek()->toDateString(),
            'period_end' => now()->endOfWeek()->toDateString(),
            'status' => 'submitted',
        ]);

        // Admin B from Org B attempts to access Org A timesheet
        Sanctum::actingAs($this->adminB);

        $res = $this->postJson("/api/v1/organizations/{$this->orgA->id}/timesheets/{$timesheet->id}/approve");
        $res->assertStatus(403); // Blocked by EnsureOrganizationMember middleware
    }
}
