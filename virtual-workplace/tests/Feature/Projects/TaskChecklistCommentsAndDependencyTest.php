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

class TaskChecklistCommentsAndDependencyTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminA;
    protected User $employeeA;
    protected Organization $orgA;
    protected Project $projectA;
    protected Task $taskA;
    protected Task $taskB;
    protected Task $taskC;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(\Database\Seeders\PlansSeeder::class);
        $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);

        $action = app(CreateOrganizationAction::class);

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

        $this->projectA = Project::create([
            'organization_id' => $this->orgA->id,
            'name' => 'Core Platform Upgrade',
            'owner_id' => $this->adminA->id,
            'status' => 'active',
        ]);

        $this->taskA = Task::create([
            'organization_id' => $this->orgA->id,
            'project_id' => $this->projectA->id,
            'title' => 'Database Schema Migration',
            'reporter_id' => $this->adminA->id,
            'assignee_id' => $this->employeeA->id,
            'status' => 'backlog',
        ]);

        $this->taskB = Task::create([
            'organization_id' => $this->orgA->id,
            'project_id' => $this->projectA->id,
            'title' => 'API Endpoint Implementation',
            'reporter_id' => $this->adminA->id,
            'assignee_id' => $this->employeeA->id,
            'status' => 'backlog',
        ]);

        $this->taskC = Task::create([
            'organization_id' => $this->orgA->id,
            'project_id' => $this->projectA->id,
            'title' => 'Frontend Integration & QA',
            'reporter_id' => $this->adminA->id,
            'assignee_id' => $this->employeeA->id,
            'status' => 'backlog',
        ]);
    }

    public function test_checklist_items_lifecycle(): void
    {
        Sanctum::actingAs($this->adminA);

        // 1. Add checklist item
        $addRes = $this->postJson("/api/v1/organizations/{$this->orgA->id}/tasks/{$this->taskA->id}/checklist", [
            'title' => 'Backup production database before migration',
        ]);

        $addRes->assertStatus(201)
            ->assertJsonPath('item.title', 'Backup production database before migration')
            ->assertJsonPath('item.is_completed', false);

        $itemId = $addRes->json('item.id');

        // 2. Toggle checklist item to completed
        $toggleRes = $this->patchJson("/api/v1/organizations/{$this->orgA->id}/tasks/{$this->taskA->id}/checklist/{$itemId}");
        $toggleRes->assertStatus(200)
            ->assertJsonPath('item.is_completed', true);

        // 3. Toggle back to uncompleted
        $toggleBackRes = $this->patchJson("/api/v1/organizations/{$this->orgA->id}/tasks/{$this->taskA->id}/checklist/{$itemId}");
        $toggleBackRes->assertStatus(200)
            ->assertJsonPath('item.is_completed', false);
    }

    public function test_task_comments_feed(): void
    {
        Sanctum::actingAs($this->employeeA);

        $commentRes = $this->postJson("/api/v1/organizations/{$this->orgA->id}/tasks/{$this->taskA->id}/comments", [
            'content' => 'Completed initial draft of migration script. Ready for review.',
        ]);

        $commentRes->assertStatus(201)
            ->assertJsonPath('comment.body', 'Completed initial draft of migration script. Ready for review.')
            ->assertJsonPath('comment.user.name', $this->employeeA->name);

        $this->assertDatabaseHas('task_comments', [
            'task_id' => $this->taskA->id,
            'user_id' => $this->employeeA->id,
        ]);
    }

    public function test_dependency_chain_and_cycle_detection(): void
    {
        Sanctum::actingAs($this->adminA);

        // 1. Task B depends on Task A (Task A must finish before Task B starts)
        $dep1 = $this->postJson("/api/v1/organizations/{$this->orgA->id}/tasks/{$this->taskB->id}/dependencies", [
            'depends_on_task_id' => $this->taskA->id,
        ]);
        $dep1->assertStatus(201);

        // 2. Task C depends on Task B (Task B must finish before Task C starts)
        $dep2 = $this->postJson("/api/v1/organizations/{$this->orgA->id}/tasks/{$this->taskC->id}/dependencies", [
            'depends_on_task_id' => $this->taskB->id,
        ]);
        $dep2->assertStatus(201);

        // 3. Attempting to make Task A depend on Task C (creates circular loop A -> B -> C -> A)
        $circularDep = $this->postJson("/api/v1/organizations/{$this->orgA->id}/tasks/{$this->taskA->id}/dependencies", [
            'depends_on_task_id' => $this->taskC->id,
        ]);

        // Must be rejected with 422 and clear cycle error
        $circularDep->assertStatus(422)
            ->assertJsonPath('message', 'Circular dependency detected! Adding this relationship would create an infinite loop.');

        // 4. Self dependency attempt (Task A depends on Task A)
        $selfDep = $this->postJson("/api/v1/organizations/{$this->orgA->id}/tasks/{$this->taskA->id}/dependencies", [
            'depends_on_task_id' => $this->taskA->id,
        ]);
        $selfDep->assertStatus(422)
            ->assertJsonPath('message', 'A task cannot depend on itself.');
    }
}
