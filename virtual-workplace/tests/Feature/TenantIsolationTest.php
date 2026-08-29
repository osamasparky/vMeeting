<?php

namespace Tests\Feature;

use App\Domains\Identity\Models\User;
use App\Domains\Tenancy\Actions\CreateOrganizationAction;
use App\Domains\Tenancy\Models\Organization;
use App\Domains\Workspace\Models\Floor;
use App\Domains\Workspace\Models\Map;
use App\Domains\Workspace\Models\Room;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class TenantIsolationTest extends TestCase
{
    use RefreshDatabase;

    protected User $userA;
    protected User $userB;
    protected Organization $orgA;
    protected Organization $orgB;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\PlansSeeder::class);
        $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);

        $action = app(CreateOrganizationAction::class);

        $this->userA = User::factory()->create(['email' => 'admin@tenant-a.com']);
        $this->orgA = $action->execute(['name' => 'Tenant Alpha'], $this->userA);

        $this->userB = User::factory()->create(['email' => 'admin@tenant-b.com']);
        $this->orgB = $action->execute(['name' => 'Tenant Beta'], $this->userB);
    }

    public function test_tenant_cannot_list_other_tenant_members(): void
    {
        Sanctum::actingAs($this->userA);

        // Attempt to list Tenant Beta members
        $response = $this->getJson("/api/v1/organizations/{$this->orgB->id}/members");
        $response->assertStatus(403);
    }

    public function test_tenant_cannot_access_other_tenant_settings(): void
    {
        Sanctum::actingAs($this->userA);

        // Attempt to view Tenant Beta settings
        $response = $this->getJson("/api/v1/organizations/{$this->orgB->id}/settings");
        $response->assertStatus(403);
    }

    public function test_tenant_cannot_mutate_other_tenant_workspace(): void
    {
        Sanctum::actingAs($this->userA);

        // Attempt to create a floor in Tenant Beta
        $response = $this->postJson("/api/v1/organizations/{$this->orgB->id}/floors", [
            'name' => 'Hacked Floor',
        ]);
        $response->assertStatus(403);
    }

    public function test_tenant_cannot_link_task_dependency_from_another_organization(): void
    {
        Sanctum::actingAs($this->userA);

        $projectA = \App\Domains\Projects\Models\Project::create([
            'organization_id' => $this->orgA->id,
            'owner_id' => $this->userA->id,
            'name' => 'Project A',
            'code' => 'PRJ-A',
            'status' => 'active',
        ]);
        $taskA = \App\Domains\Projects\Models\Task::create([
            'organization_id' => $this->orgA->id,
            'project_id' => $projectA->id,
            'reporter_id' => $this->userA->id,
            'title' => 'Task A in Org A',
            'status' => 'in_progress',
        ]);

        $projectB = \App\Domains\Projects\Models\Project::create([
            'organization_id' => $this->orgB->id,
            'owner_id' => $this->userB->id,
            'name' => 'Project B',
            'code' => 'PRJ-B',
            'status' => 'active',
        ]);
        $taskB = \App\Domains\Projects\Models\Task::create([
            'organization_id' => $this->orgB->id,
            'project_id' => $projectB->id,
            'reporter_id' => $this->userB->id,
            'title' => 'Task B in Org B',
            'status' => 'in_progress',
        ]);

        // Attempt to link taskA with cross-tenant taskB
        $response = $this->postJson("/api/v1/organizations/{$this->orgA->id}/tasks/{$taskA->id}/dependencies", [
            'depends_on_task_id' => $taskB->id,
        ]);

        // Must fail with 404 (or 422) preventing cross-tenant IDOR leak
        $this->assertTrue(in_array($response->getStatusCode(), [404, 422]));
    }

    public function test_tenant_cannot_start_timer_on_task_from_another_organization(): void
    {
        $this->actingAs($this->userA);

        $projectB = \App\Domains\Projects\Models\Project::create([
            'organization_id' => $this->orgB->id,
            'owner_id' => $this->userB->id,
            'name' => 'Project B',
            'code' => 'PRJ-B',
            'status' => 'active',
        ]);
        $taskB = \App\Domains\Projects\Models\Task::create([
            'organization_id' => $this->orgB->id,
            'project_id' => $projectB->id,
            'reporter_id' => $this->userB->id,
            'title' => 'Secret Task in Org B',
            'status' => 'in_progress',
        ]);

        // Attempt to start timer on Org B task from Org A web session
        $response = $this->postJson('/api/office/task-timer/start', [
            'task_id' => $taskB->id,
        ]);

        $response->assertStatus(404);
    }

    public function test_guest_viewer_cannot_inspect_member_tasks_or_active_timer(): void
    {
        // Unauthenticated guest request with organization_id
        $response = $this->getJson("/api/members/{$this->userA->id}/activity?organization_id={$this->orgA->id}");
        $response->assertStatus(200);

        $data = $response->json();
        $this->assertTrue($data['is_guest_viewer']);
        $this->assertNull($data['user']['email']);
        $this->assertNull($data['active_timer']);
        $this->assertEmpty($data['tasks']);
    }
}
