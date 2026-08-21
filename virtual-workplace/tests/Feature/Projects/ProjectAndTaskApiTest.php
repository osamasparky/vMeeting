<?php

namespace Tests\Feature\Projects;

use App\Domains\Administration\Models\Role;
use App\Domains\Identity\Models\User;
use App\Domains\Projects\Models\Project;
use App\Domains\Projects\Models\Task;
use App\Domains\Tenancy\Actions\CreateOrganizationAction;
use App\Domains\Tenancy\Models\Organization;
use App\Domains\Tenancy\Models\OrganizationMember;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ProjectAndTaskApiTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminA;
    protected User $employeeA;
    protected User $adminB;
    protected Organization $orgA;
    protected Organization $orgB;

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
            'joined_at' => now(),
        ]);

        // Org B
        $this->adminB = User::factory()->create(['email' => 'adminB@tenant-b.com']);
        $this->orgB = $action->execute(['name' => 'Tenant Beta LLC'], $this->adminB);
    }

    public function test_can_create_and_list_projects(): void
    {
        Sanctum::actingAs($this->adminA);

        // 1. Create Project
        $createRes = $this->postJson("/api/v1/organizations/{$this->orgA->id}/projects", [
            'name' => 'SaaS 2.0 Modernization',
            'code' => 'SAAS-01',
            'description' => 'Upgrading architecture',
            'priority' => 'high',
            'start_date' => now()->toDateString(),
            'due_date' => now()->addMonth()->toDateString(),
            'budget_amount' => 15000.00,
            'planned_hours' => 120.00,
        ]);

        $createRes->assertStatus(201)
            ->assertJsonPath('project.name', 'SaaS 2.0 Modernization')
            ->assertJsonPath('project.code', 'SAAS-01');

        $projectId = $createRes->json('project.id');

        // 2. List Projects
        $listRes = $this->getJson("/api/v1/organizations/{$this->orgA->id}/projects");
        $listRes->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.name', 'SaaS 2.0 Modernization');

        // 3. Show Project
        $showRes = $this->getJson("/api/v1/organizations/{$this->orgA->id}/projects/{$projectId}");
        $showRes->assertStatus(200)
            ->assertJsonPath('project.id', $projectId)
            ->assertJsonStructure(['project', 'metrics' => ['actual_hours', 'progress_pct']]);

        // 4. Update Project
        $updateRes = $this->patchJson("/api/v1/organizations/{$this->orgA->id}/projects/{$projectId}", [
            'name' => 'SaaS 2.0 Modernization (Updated)',
            'status' => 'completed',
        ]);
        $updateRes->assertStatus(200)
            ->assertJsonPath('project.name', 'SaaS 2.0 Modernization (Updated)')
            ->assertJsonPath('project.status', 'completed');
    }

    public function test_tenant_isolation_on_projects(): void
    {
        // Admin A creates a project in Org A
        $projectA = Project::create([
            'organization_id' => $this->orgA->id,
            'name' => 'Secret Project Alpha',
            'owner_id' => $this->adminA->id,
            'status' => 'active',
        ]);

        // Admin B tries to view/access Project A
        Sanctum::actingAs($this->adminB);

        $showRes = $this->getJson("/api/v1/organizations/{$this->orgA->id}/projects/{$projectA->id}");
        $showRes->assertStatus(403); // Blocked by EnsureOrganizationMember middleware

        $listRes = $this->getJson("/api/v1/organizations/{$this->orgB->id}/projects");
        $listRes->assertStatus(200)->assertJsonCount(0, 'data');
    }

    public function test_task_crud_and_status_transitions(): void
    {
        Sanctum::actingAs($this->adminA);

        $project = Project::create([
            'organization_id' => $this->orgA->id,
            'name' => 'Dev Project',
            'owner_id' => $this->adminA->id,
            'status' => 'active',
        ]);

        // 1. Create Task
        $createRes = $this->postJson("/api/v1/organizations/{$this->orgA->id}/tasks", [
            'project_id' => $project->id,
            'title' => 'Build WebSocket Client',
            'description' => 'Implement ws transport',
            'task_type' => 'feature',
            'priority' => 'high',
            'assignee_id' => $this->employeeA->id,
            'estimated_hours' => 16.00,
        ]);

        $createRes->assertStatus(201)
            ->assertJsonPath('task.title', 'Build WebSocket Client')
            ->assertJsonPath('task.task_number', 1)
            ->assertJsonPath('task.status', 'backlog');

        $taskId = $createRes->json('task.id');

        // 2. Status Transition (e.g. Kanban move)
        $statusRes = $this->patchJson("/api/v1/organizations/{$this->orgA->id}/tasks/{$taskId}/status", [
            'status' => 'in_progress',
        ]);
        $statusRes->assertStatus(200)
            ->assertJsonPath('task.status', 'in_progress');

        // 3. Create Subtask
        $subtaskRes = $this->postJson("/api/v1/organizations/{$this->orgA->id}/tasks", [
            'project_id' => $project->id,
            'parent_task_id' => $taskId,
            'title' => 'Write Unit Tests for WS Client',
            'task_type' => 'task',
            'estimated_hours' => 4.00,
        ]);
        $subtaskRes->assertStatus(201)
            ->assertJsonPath('task.parent_task_id', $taskId);

        // 4. Show Task with Subtasks
        $showRes = $this->getJson("/api/v1/organizations/{$this->orgA->id}/tasks/{$taskId}");
        $showRes->assertStatus(200)
            ->assertJsonCount(1, 'task.subtasks');
    }

    public function test_my_tasks_endpoint(): void
    {
        $project = Project::create([
            'organization_id' => $this->orgA->id,
            'name' => 'Operations Project',
            'owner_id' => $this->adminA->id,
            'status' => 'active',
        ]);

        // Task 1: Due Today
        Task::create([
            'organization_id' => $this->orgA->id,
            'project_id' => $project->id,
            'title' => 'Due Today Task',
            'reporter_id' => $this->adminA->id,
            'assignee_id' => $this->employeeA->id,
            'due_date' => now()->toDateString(),
            'status' => 'in_progress',
        ]);

        // Task 2: Overdue
        Task::create([
            'organization_id' => $this->orgA->id,
            'project_id' => $project->id,
            'title' => 'Overdue Task',
            'reporter_id' => $this->adminA->id,
            'assignee_id' => $this->employeeA->id,
            'due_date' => now()->subDays(3)->toDateString(),
            'status' => 'ready',
        ]);

        // Task 3: Upcoming
        Task::create([
            'organization_id' => $this->orgA->id,
            'project_id' => $project->id,
            'title' => 'Upcoming Task',
            'reporter_id' => $this->adminA->id,
            'assignee_id' => $this->employeeA->id,
            'due_date' => now()->addDays(5)->toDateString(),
            'status' => 'backlog',
        ]);

        Sanctum::actingAs($this->employeeA);

        $res = $this->getJson("/api/v1/organizations/{$this->orgA->id}/tasks/my-tasks");
        $res->assertStatus(200)
            ->assertJsonCount(1, 'due_today')
            ->assertJsonCount(1, 'overdue')
            ->assertJsonCount(1, 'upcoming')
            ->assertJsonPath('summary.total', 3)
            ->assertJsonPath('summary.overdue', 1);
    }

    public function test_authenticated_user_can_access_project_hub_view(): void
    {
        $project = Project::create([
            'organization_id' => $this->orgA->id,
            'name' => 'Project Alpha Hub',
            'code' => 'PAH',
            'status' => 'active',
            'owner_id' => $this->adminA->id,
            'budget_amount' => 50000,
            'planned_hours' => 200,
        ]);

        // 1. Authenticated member of same tenant can view Project Hub
        $res = $this->actingAs($this->adminA)->get(route('projects.hub', $project->id));
        $res->assertStatus(200)
            ->assertSee('Project Alpha Hub')
            ->assertSee('PAH')
            ->assertSee('Kanban Board');

        // 2. Member of another tenant cannot access
        $resB = $this->actingAs($this->adminB)->get(route('projects.hub', $project->id));
        $resB->assertStatus(404);
    }
}
