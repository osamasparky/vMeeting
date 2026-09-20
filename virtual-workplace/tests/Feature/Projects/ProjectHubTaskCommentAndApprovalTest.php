<?php

namespace Tests\Feature\Projects;

use App\Domains\Administration\Models\Role;
use App\Domains\Identity\Models\User;
use App\Domains\Notifications\Models\WorkplaceNotification;
use App\Domains\Projects\Models\Project;
use App\Domains\Projects\Models\Task;
use App\Domains\Tenancy\Actions\CreateOrganizationAction;
use App\Domains\Tenancy\Models\Organization;
use App\Domains\Tenancy\Models\OrganizationMember;
use Database\Seeders\PlansSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Regression coverage for ProjectHubController::storeTaskComment(),
 * ::approveTask() and ::rejectTask(), whose logic was extracted into
 * PostTaskCommentWithNotificationsAction and a shared isTaskManager()
 * helper. None of these three web endpoints had prior test coverage — the
 * existing comment test in TaskChecklistCommentsAndDependencyTest exercises
 * the separate API route, which intentionally has no mention/notification
 * behavior (see Architecture Audit §9).
 */
class ProjectHubTaskCommentAndApprovalTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected User $assignee;

    protected Organization $organization;

    protected Project $project;

    protected Task $task;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(PlansSeeder::class);
        $this->seed(RolesAndPermissionsSeeder::class);

        $this->admin = User::factory()->create(['email' => 'hub-admin@example.com']);
        $this->organization = app(CreateOrganizationAction::class)->execute(['name' => 'Hub Co'], $this->admin);

        $this->assignee = User::factory()->create(['email' => 'assignee@example.com', 'name' => 'Assignee Person']);
        $employeeRole = Role::where('slug', 'employee')->whereNull('organization_id')->first();
        OrganizationMember::create([
            'organization_id' => $this->organization->id,
            'user_id' => $this->assignee->id,
            'role_id' => $employeeRole->id,
            'status' => 'active',
        ]);

        $this->project = Project::create([
            'organization_id' => $this->organization->id,
            'name' => 'Hub Project',
            'code' => 'HUB-01',
            'owner_id' => $this->admin->id,
            'status' => 'active',
            'priority' => 'medium',
        ]);

        $this->task = Task::create([
            'organization_id' => $this->organization->id,
            'project_id' => $this->project->id,
            'title' => 'Ship the feature',
            'task_number' => 1,
            'status' => 'in_progress',
            'reporter_id' => $this->admin->id,
            'assignee_id' => $this->assignee->id,
        ]);
    }

    public function test_commenting_notifies_the_task_assignee(): void
    {
        $response = $this->actingAs($this->admin)->postJson("/tasks/{$this->task->id}/comments", [
            'body' => 'Looks good, one nit to fix.',
        ]);

        $response->assertOk()->assertJsonPath('comment.body', 'Looks good, one nit to fix.');

        $this->assertDatabaseHas('task_comments', [
            'task_id' => $this->task->id,
            'user_id' => $this->admin->id,
            'body' => 'Looks good, one nit to fix.',
        ]);

        $this->assertDatabaseHas('workplace_notifications', [
            'user_id' => $this->assignee->id,
            'type' => 'task_comment',
        ]);
    }

    public function test_commenting_with_a_mention_notifies_the_mentioned_member(): void
    {
        $response = $this->actingAs($this->admin)->postJson("/tasks/{$this->task->id}/comments", [
            'body' => '@Assignee please take a look',
        ]);

        $response->assertOk();

        $mentionNotification = WorkplaceNotification::where('user_id', $this->assignee->id)
            ->where('type', 'task_mention')
            ->first();

        $this->assertNotNull($mentionNotification);
    }

    public function test_project_admin_can_approve_a_task(): void
    {
        $response = $this->actingAs($this->admin)->postJson("/tasks/{$this->task->id}/approve");

        $response->assertOk()->assertJsonPath('success', true);

        $this->assertDatabaseHas('tasks', [
            'id' => $this->task->id,
            'status' => 'done',
            'approval_status' => 'approved',
        ]);
        $this->assertDatabaseHas('workplace_notifications', [
            'user_id' => $this->assignee->id,
            'type' => 'task_approved',
        ]);
    }

    public function test_project_admin_can_reject_a_task_with_a_reason(): void
    {
        $response = $this->actingAs($this->admin)->postJson("/tasks/{$this->task->id}/reject", [
            'rejection_reason' => 'Please add tests before resubmitting.',
        ]);

        $response->assertOk()->assertJsonPath('success', true);

        $this->assertDatabaseHas('tasks', [
            'id' => $this->task->id,
            'status' => 'in_progress',
            'approval_status' => 'rejected',
            'rejection_reason' => 'Please add tests before resubmitting.',
        ]);
    }

    public function test_a_plain_member_cannot_approve_a_task(): void
    {
        $response = $this->actingAs($this->assignee)->postJson("/tasks/{$this->task->id}/approve");

        $response->assertStatus(403);
        $this->assertDatabaseHas('tasks', ['id' => $this->task->id, 'status' => 'in_progress']);
    }
}
