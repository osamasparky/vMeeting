<?php

namespace Tests\Feature\Projects;

use App\Domains\Administration\Models\Role;
use App\Domains\Identity\Models\User;
use App\Domains\People\Models\AttendanceSession;
use App\Domains\Projects\Models\Project;
use App\Domains\Projects\Models\ProjectMilestone;
use App\Domains\Projects\Models\ProjectSprint;
use App\Domains\Projects\Models\Task;
use App\Domains\Projects\Models\TaskAttachment;
use App\Domains\Projects\Models\TimeEntry;
use App\Domains\Projects\Models\Timesheet;
use App\Domains\Tenancy\Actions\CreateOrganizationAction;
use App\Domains\Tenancy\Models\Organization;
use App\Domains\Tenancy\Models\OrganizationMember;
use Database\Seeders\PlansSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

/**
 * Regression coverage for the project-management fix pass (2026-10-10).
 * Each test pins one bug that previously slipped through the suite.
 */
class ProjectManagementRegressionTest extends TestCase
{
    use RefreshDatabase;

    protected User $owner;

    protected User $manager;

    protected User $employee;

    protected User $otherEmployee;

    protected User $outsider;

    protected Organization $org;

    protected Organization $otherOrg;

    protected Project $project;

    protected Project $foreignProject;

    protected Task $task;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(PlansSeeder::class);
        $this->seed(RolesAndPermissionsSeeder::class);

        $create = app(CreateOrganizationAction::class);

        $this->owner = User::factory()->create();
        $this->org = $create->execute(['name' => 'Owner Co'], $this->owner);

        $this->manager = $this->addMember($this->org, 'manager');
        $this->employee = $this->addMember($this->org, 'employee');
        $this->otherEmployee = $this->addMember($this->org, 'employee');

        $this->outsider = User::factory()->create();
        $this->otherOrg = $create->execute(['name' => 'Other Co'], $this->outsider);

        $this->project = Project::create([
            'organization_id' => $this->org->id,
            'name' => 'Main Project',
            'owner_id' => $this->owner->id,
            'manager_id' => $this->manager->id,
            'status' => 'active',
        ]);

        $this->foreignProject = Project::create([
            'organization_id' => $this->otherOrg->id,
            'name' => 'Foreign Project',
            'owner_id' => $this->outsider->id,
            'status' => 'active',
        ]);

        $this->task = $this->makeTask(['assignee_id' => $this->employee->id, 'reporter_id' => $this->employee->id]);
    }

    private function addMember(Organization $org, string $roleSlug): User
    {
        $user = User::factory()->create();
        OrganizationMember::create([
            'organization_id' => $org->id,
            'user_id' => $user->id,
            'role_id' => Role::where('slug', $roleSlug)->whereNull('organization_id')->value('id'),
            'status' => 'active',
            'joined_at' => now(),
        ]);

        return $user;
    }

    private function makeTask(array $attrs = []): Task
    {
        static $n = 0;
        $n++;

        return Task::create(array_merge([
            'organization_id' => $this->org->id,
            'project_id' => $this->project->id,
            'title' => "Task {$n}",
            'task_number' => $n,
            'reporter_id' => $this->owner->id,
            'status' => 'in_progress',
        ], $attrs));
    }

    private function api(string $path): string
    {
        return "/api/v1/organizations/{$this->org->id}{$path}";
    }

    // ── Phase 1: durations ────────────────────────────────────────

    public function test_stopped_timer_records_real_elapsed_time(): void
    {
        $this->freezeSecond(); // the real clock can tick between start and stop
        Sanctum::actingAs($this->employee);
        $this->postJson($this->api('/time/timer/start'), ['project_id' => $this->project->id, 'task_id' => $this->task->id])->assertCreated();

        $this->travel(2)->hours();

        $this->postJson($this->api('/time/timer/stop'))->assertOk()
            ->assertJsonPath('time_entry.duration_seconds', 7200);
    }

    public function test_switching_timers_records_real_elapsed_time_for_the_first(): void
    {
        $this->freezeSecond(); // the real clock can tick between start and stop
        Sanctum::actingAs($this->employee);
        $this->postJson($this->api('/time/timer/start'), ['project_id' => $this->project->id, 'task_id' => $this->task->id])->assertCreated();
        $this->travel(45)->minutes();
        $this->postJson($this->api('/time/timer/start'), ['project_id' => $this->project->id])->assertCreated();

        $this->assertSame(2700, TimeEntry::first()->duration_seconds);
    }

    public function test_manual_time_entry_is_accepted_and_measured(): void
    {
        Sanctum::actingAs($this->owner); // manual entries are the owner's
        $this->postJson($this->api('/time/entries/manual'), [
            'project_id' => $this->project->id,
            'started_at' => '2026-10-09 09:00:00',
            'ended_at' => '2026-10-09 11:00:00',
        ])->assertCreated()->assertJsonPath('time_entry.duration_seconds', 7200);
    }

    public function test_active_timer_elapsed_seconds_is_positive(): void
    {
        $this->freezeSecond(); // the real clock can tick between start and the check
        Sanctum::actingAs($this->employee);
        $this->postJson($this->api('/time/timer/start'), ['project_id' => $this->project->id])->assertCreated();
        $this->travel(10)->minutes();

        $this->getJson($this->api('/time/active-timer'))->assertOk()->assertJsonPath('elapsed_seconds', 600);
    }

    public function test_closed_attendance_session_keeps_its_duration(): void
    {
        $this->freezeSecond(); // the real clock can tick between create and close
        $session = AttendanceSession::create([
            'organization_id' => $this->org->id,
            'user_id' => $this->employee->id,
            'status' => 'active',
            'started_at' => now(),
        ]);
        $this->travel(30)->minutes();

        $this->assertSame(1800, $session->close()->duration_seconds);
    }

    public function test_repair_command_restores_truncated_durations(): void
    {
        $entry = TimeEntry::create([
            'organization_id' => $this->org->id, 'user_id' => $this->employee->id, 'project_id' => $this->project->id,
            'started_at' => '2026-09-01 09:00:00', 'ended_at' => '2026-09-01 10:30:00', 'duration_seconds' => 1,
        ]);
        $session = AttendanceSession::create([
            'organization_id' => $this->org->id, 'user_id' => $this->employee->id, 'status' => 'completed',
            'started_at' => '2026-09-01 08:00:00', 'ended_at' => '2026-09-01 12:00:00', 'duration_seconds' => 0,
        ]);

        $this->artisan('time:repair-durations', ['--dry-run' => true])->assertSuccessful();
        $this->assertSame(1, $entry->fresh()->duration_seconds);

        $this->artisan('time:repair-durations')->assertSuccessful();
        $this->assertSame(5400, $entry->fresh()->duration_seconds);
        $this->assertSame(14400, $session->fresh()->duration_seconds);
    }

    // ── Phase 2: silently broken features ─────────────────────────

    public function test_sprint_and_story_points_are_persisted(): void
    {
        $sprint = ProjectSprint::create([
            'organization_id' => $this->org->id,
            'project_id' => $this->project->id,
            'name' => 'S1',
            'start_date' => now(),
            'end_date' => now()->addWeeks(2),
        ]);

        Sanctum::actingAs($this->employee);
        $this->patchJson($this->api("/tasks/{$this->task->id}/sprint"), ['sprint_id' => $sprint->id, 'story_points' => 5])->assertOk();

        $this->task->refresh();
        $this->assertSame($sprint->id, $this->task->sprint_id);
        $this->assertSame(5, $this->task->story_points);
    }

    public function test_task_can_be_moved_with_a_milestone(): void
    {
        $milestone = ProjectMilestone::create(['organization_id' => $this->org->id, 'project_id' => $this->project->id, 'name' => 'M1']);

        Sanctum::actingAs($this->employee);
        $this->postJson($this->api("/tasks/{$this->task->id}/move"), [
            'project_id' => $this->project->id,
            'milestone_id' => $milestone->id,
        ])->assertOk();

        $this->assertSame($milestone->id, $this->task->fresh()->milestone_id);
    }

    public function test_auto_tasks_goal_reaches_100_percent(): void
    {
        Sanctum::actingAs($this->owner);
        $goal = $this->postJson($this->api("/projects/{$this->project->id}/goals"), ['name' => 'Ship'])->assertCreated()->json('goal');
        // Auto-tracked targets are sent by the hub with target_value 0.
        $this->postJson($this->api("/projects/{$this->project->id}/goals/{$goal['id']}/targets"), [
            'title' => 'Tasks Completed', 'target_type' => 'tasks', 'target_value' => 0, 'unit' => 'Tasks',
        ])->assertCreated();

        $this->patchJson($this->api("/tasks/{$this->task->id}/status"), ['status' => 'done'])->assertOk();

        $this->assertEquals(100.0, (float) $this->project->goals()->first()->progress_percentage);
    }

    public function test_milestones_and_hours_goal_targets_are_accepted(): void
    {
        Sanctum::actingAs($this->owner);
        $goal = $this->postJson($this->api("/projects/{$this->project->id}/goals"), ['name' => 'Deliver'])->json('goal');

        foreach (['milestones', 'hours'] as $type) {
            $this->postJson($this->api("/projects/{$this->project->id}/goals/{$goal['id']}/targets"), [
                'title' => $type, 'target_type' => $type, 'target_value' => 100,
            ])->assertCreated();
        }
    }

    public function test_hours_goal_tracks_logged_time(): void
    {
        $this->project->update(['planned_hours' => 4]);
        Sanctum::actingAs($this->owner);
        $goal = $this->postJson($this->api("/projects/{$this->project->id}/goals"), ['name' => 'Budget'])->json('goal');
        $this->postJson($this->api("/projects/{$this->project->id}/goals/{$goal['id']}/targets"), [
            'title' => 'Hours', 'target_type' => 'hours', 'target_value' => 0,
        ])->assertCreated();

        $this->postJson($this->api('/time/entries/manual'), [
            'project_id' => $this->project->id, 'task_id' => $this->task->id,
            'started_at' => '2026-10-09 09:00:00', 'ended_at' => '2026-10-09 11:00:00',
        ])->assertCreated();
        $this->patchJson($this->api("/tasks/{$this->task->id}/status"), ['status' => 'review'])->assertOk();

        $this->assertEquals(50.0, (float) $this->project->goals()->first()->progress_percentage);
    }

    // ── Phase 3: one path for status changes ──────────────────────

    public function test_employee_cannot_mark_done_through_the_generic_update(): void
    {
        Sanctum::actingAs($this->employee);
        $this->patchJson($this->api("/tasks/{$this->task->id}"), ['status' => 'done'])->assertOk();

        $this->task->refresh();
        $this->assertSame('review', $this->task->status);
        $this->assertSame('pending_approval', $this->task->approval_status);
    }

    public function test_employee_bulk_done_goes_to_review(): void
    {
        Sanctum::actingAs($this->employee);
        $this->postJson($this->api('/tasks/bulk'), [
            'task_ids' => [$this->task->id], 'action' => 'update_status', 'status' => 'done',
        ])->assertOk();

        $this->assertSame('review', $this->task->fresh()->status);
    }

    public function test_employee_cannot_mark_done_from_the_office(): void
    {
        $this->actingAs($this->employee)
            ->postJson("/api/office/tasks/{$this->task->id}/status", ['status' => 'done'])->assertOk();

        $this->assertSame('review', $this->task->fresh()->status);
    }

    public function test_approving_a_recurring_task_spawns_the_next_cycle(): void
    {
        $this->task->update(['recurrence_rule' => 'weekly', 'due_date' => now()->toDateString(), 'status' => 'review', 'approval_status' => 'pending_approval']);

        $this->actingAs($this->manager)->postJson("/tasks/{$this->task->id}/approve")->assertOk();

        $this->assertSame(2, Task::where('title', $this->task->title)->count());
    }

    // ── Phase 4: tenant isolation & authorization ─────────────────

    public function test_task_cannot_be_created_in_another_orgs_project(): void
    {
        Sanctum::actingAs($this->employee);
        $this->postJson($this->api('/tasks'), ['project_id' => $this->foreignProject->id, 'title' => 'x'])
            ->assertUnprocessable()->assertJsonValidationErrors('project_id');
    }

    public function test_task_cannot_be_assigned_to_a_non_member(): void
    {
        Sanctum::actingAs($this->manager);
        $this->patchJson($this->api("/tasks/{$this->task->id}/assign"), ['assignee_id' => $this->outsider->id])
            ->assertUnprocessable()->assertJsonValidationErrors('assignee_id');
    }

    public function test_timer_cannot_target_another_orgs_project(): void
    {
        Sanctum::actingAs($this->employee);
        $this->postJson($this->api('/time/timer/start'), ['project_id' => $this->foreignProject->id])
            ->assertUnprocessable()->assertJsonValidationErrors('project_id');
    }

    public function test_employee_cannot_start_a_timer_for_someone_else(): void
    {
        Sanctum::actingAs($this->employee);
        $this->postJson($this->api('/time/timer/start'), ['project_id' => $this->project->id, 'user_id' => $this->otherEmployee->id])
            ->assertForbidden();
    }

    public function test_manager_cannot_start_a_timer_for_an_outsider(): void
    {
        Sanctum::actingAs($this->manager);
        $this->postJson($this->api('/time/timer/start'), ['project_id' => $this->project->id, 'user_id' => $this->outsider->id])
            ->assertForbidden();
    }

    public function test_employee_cannot_edit_someone_elses_time_entry(): void
    {
        $entry = TimeEntry::create([
            'organization_id' => $this->org->id, 'user_id' => $this->otherEmployee->id, 'project_id' => $this->project->id,
            'started_at' => now()->subHour(), 'ended_at' => now(), 'duration_seconds' => 3600,
        ]);

        Sanctum::actingAs($this->employee);
        $this->patchJson($this->api("/time/entries/{$entry->id}"), ['description' => 'hijack'])->assertForbidden();
    }

    public function test_time_entry_alias_routes_enforce_permissions(): void
    {
        $guest = $this->addMember($this->org, 'guest');
        Sanctum::actingAs($guest);

        $this->postJson($this->api('/time-entries/manual'), [
            'project_id' => $this->project->id, 'started_at' => '2026-10-09 09:00:00', 'ended_at' => '2026-10-09 10:00:00',
        ])->assertForbidden();
    }

    public function test_outsider_cannot_comment_on_or_read_a_task(): void
    {
        $this->actingAs($this->outsider)->postJson("/tasks/{$this->task->id}/comments", ['body' => 'hi'])->assertForbidden();
        $this->actingAs($this->outsider)->getJson("/tasks/{$this->task->id}/comments")->assertForbidden();
    }

    public function test_attachment_must_belong_to_the_task_being_edited(): void
    {
        $otherTask = $this->makeTask(['assignee_id' => $this->employee->id]);
        $attachment = TaskAttachment::create([
            'organization_id' => $this->org->id, 'task_id' => $otherTask->id, 'user_id' => $this->employee->id,
            'file_name' => 'a.txt', 'file_path' => 'nowhere/a.txt',
        ]);

        $this->actingAs($this->employee)->deleteJson("/tasks/{$this->task->id}/attachments/{$attachment->id}")->assertNotFound();
        $this->assertModelExists($attachment);
    }

    public function test_outsider_cannot_delete_an_attachment(): void
    {
        $attachment = TaskAttachment::create([
            'organization_id' => $this->org->id, 'task_id' => $this->task->id, 'user_id' => $this->employee->id,
            'file_name' => 'a.txt', 'file_path' => 'nowhere/a.txt',
        ]);

        $this->actingAs($this->outsider)->deleteJson("/tasks/{$this->task->id}/attachments/{$attachment->id}")->assertForbidden();
        $this->assertModelExists($attachment);
    }

    public function test_bulk_update_respects_per_task_authorization(): void
    {
        $notMine = $this->makeTask(['assignee_id' => $this->otherEmployee->id, 'reporter_id' => $this->otherEmployee->id]);

        Sanctum::actingAs($this->employee);
        $this->postJson($this->api('/tasks/bulk'), ['task_ids' => [$notMine->id], 'action' => 'assign', 'assignee_id' => $this->employee->id])
            ->assertForbidden();
    }

    public function test_only_the_company_owner_can_approve_timesheets(): void
    {
        $sheet = Timesheet::create([
            'organization_id' => $this->org->id, 'user_id' => $this->employee->id,
            'period_start' => now()->startOfWeek()->toDateString(), 'period_end' => now()->endOfWeek()->toDateString(),
            'status' => 'submitted',
        ]);

        Sanctum::actingAs($this->manager);
        $this->postJson($this->api("/timesheets/{$sheet->id}/approve"))->assertForbidden();
        $this->postJson($this->api("/timesheets/{$sheet->id}/reject"), ['rejection_reason' => 'nope'])->assertForbidden();

        Sanctum::actingAs($this->owner);
        $this->postJson($this->api("/timesheets/{$sheet->id}/approve"))->assertOk();
        $this->assertSame('approved', $sheet->fresh()->status);
    }

    // ── Phase 5: smaller correctness fixes ────────────────────────

    public function test_duplicated_task_gets_a_fresh_number(): void
    {
        Sanctum::actingAs($this->employee);
        $copy = $this->postJson($this->api("/tasks/{$this->task->id}/duplicate"))->assertCreated()->json('task');

        $this->assertNotEquals($this->task->task_number, $copy['task_number']);
        $this->assertSame(Task::where('project_id', $this->project->id)->max('task_number'), $copy['task_number']);
    }

    public function test_gantt_milestones_carry_their_name(): void
    {
        ProjectMilestone::create(['organization_id' => $this->org->id, 'project_id' => $this->project->id, 'name' => 'Launch']);

        Sanctum::actingAs($this->owner);
        $this->getJson($this->api("/projects/{$this->project->id}/gantt"))->assertOk()
            ->assertJsonPath('milestones.0.title', '🚩 Launch');
    }
}
