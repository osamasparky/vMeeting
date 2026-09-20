<?php

namespace Tests\Feature\Projects;

use App\Domains\Identity\Models\User;
use App\Domains\Projects\Models\Project;
use App\Domains\Projects\Models\Task;
use App\Domains\Tenancy\Actions\CreateOrganizationAction;
use App\Domains\Tenancy\Models\Organization;
use App\Domains\Tenancy\Models\OrganizationMember;
use Database\Seeders\PlansSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClickUpAdvancedFeaturesTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected Organization $org;

    protected Project $project;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(PlansSeeder::class);
        $this->seed(RolesAndPermissionsSeeder::class);

        $this->user = User::factory()->create(['email' => 'clickup-admin@acme.com']);
        $action = app(CreateOrganizationAction::class);
        $this->org = $action->execute(['name' => 'Acme ClickUp Org'], $this->user);

        $member = OrganizationMember::where('organization_id', $this->org->id)
            ->where('user_id', $this->user->id)
            ->first();

        $member->update([
            'weekly_capacity_hours' => 40.0,
            'cost_hourly_rate' => 50.0,
            'billing_hourly_rate' => 100.0,
        ]);

        $this->project = Project::create([
            'organization_id' => $this->org->id,
            'name' => 'ClickUp Competitor Core',
            'code' => 'CCC',
            'owner_id' => $this->user->id,
            'status' => 'active',
            'budget_amount' => 100000,
            'planned_hours' => 500,
        ]);
    }

    public function test_custom_fields_definition_and_task_value_binding(): void
    {
        // 1. Create custom field definition
        $defRes = $this->actingAs($this->user)->postJson(
            "/api/v1/organizations/{$this->org->id}/projects/{$this->project->id}/custom-fields",
            [
                'name' => 'Story Points',
                'field_type' => 'number',
                'is_required' => false,
            ]
        );

        $defRes->assertStatus(201)
            ->assertJsonPath('custom_field.name', 'Story Points');

        $fieldId = $defRes->json('custom_field.id');

        // 2. Create task
        $task = Task::create([
            'organization_id' => $this->org->id,
            'project_id' => $this->project->id,
            'task_number' => 101,
            'title' => 'Implement Webhook Engine',
            'status' => 'backlog',
            'creator_id' => $this->user->id,
            'reporter_id' => $this->user->id,
        ]);

        // 3. Set custom field value on task
        $valRes = $this->actingAs($this->user)->postJson(
            "/api/v1/organizations/{$this->org->id}/tasks/{$task->id}/custom-fields",
            [
                'custom_field_definition_id' => $fieldId,
                'value_number' => 8,
            ]
        );

        $valRes->assertStatus(200)
            ->assertJsonPath('value.value_number', '8.0000');

        $this->assertDatabaseHas('task_custom_field_values', [
            'task_id' => $task->id,
            'custom_field_definition_id' => $fieldId,
            'value_number' => 8,
        ]);
    }

    public function test_project_documents_wiki_lifecycle(): void
    {
        // 1. Create Doc
        $createRes = $this->actingAs($this->user)->postJson(
            "/api/v1/organizations/{$this->org->id}/projects/{$this->project->id}/docs",
            [
                'title' => 'Architecture Blueprint',
                'content' => '# System Architecture\n\nThis is the core specification.',
                'icon' => '🏛️',
                'is_pinned' => true,
            ]
        );

        $createRes->assertStatus(201)
            ->assertJsonPath('document.title', 'Architecture Blueprint')
            ->assertJsonPath('document.version', 1);

        $docId = $createRes->json('document.id');

        // 2. Update Doc
        $updateRes = $this->actingAs($this->user)->putJson(
            "/api/v1/organizations/{$this->org->id}/projects/{$this->project->id}/docs/{$docId}",
            [
                'title' => 'Architecture Blueprint v2',
                'content' => '# System Architecture v2\nUpdated with microservices.',
            ]
        );

        $updateRes->assertStatus(200)
            ->assertJsonPath('document.title', 'Architecture Blueprint v2')
            ->assertJsonPath('document.version', 2);

        // 3. List Docs
        $listRes = $this->actingAs($this->user)->getJson(
            "/api/v1/organizations/{$this->org->id}/projects/{$this->project->id}/docs"
        );
        $listRes->assertStatus(200)
            ->assertJsonCount(1, 'documents');
    }

    public function test_project_goals_and_targets_calculation(): void
    {
        // 1. Create Goal
        $goalRes = $this->actingAs($this->user)->postJson(
            "/api/v1/organizations/{$this->org->id}/projects/{$this->project->id}/goals",
            [
                'name' => 'Q3 Release Milestone',
                'description' => 'Launch Phase 1 to early adopters',
                'due_date' => now()->addDays(30)->toDateString(),
            ]
        );

        $goalRes->assertStatus(201)
            ->assertJsonPath('goal.name', 'Q3 Release Milestone');

        $goalId = $goalRes->json('goal.id');

        // 2. Add Target 1 (50% progress)
        $t1Res = $this->actingAs($this->user)->postJson(
            "/api/v1/organizations/{$this->org->id}/projects/{$this->project->id}/goals/{$goalId}/targets",
            [
                'title' => 'Complete 20 Tasks',
                'target_type' => 'tasks',
                'start_value' => 0,
                'target_value' => 20,
                'current_value' => 10,
            ]
        );

        $t1Res->assertStatus(201)
            ->assertJsonPath('goal.progress_percentage', '50.00');

        $targetId = $t1Res->json('target.id');

        // 3. Update target to 100% completion
        $t1Update = $this->actingAs($this->user)->patchJson(
            "/api/v1/organizations/{$this->org->id}/projects/{$this->project->id}/goals/{$goalId}/targets/{$targetId}",
            [
                'current_value' => 20,
                'is_completed' => true,
            ]
        );

        $t1Update->assertStatus(200)
            ->assertJsonPath('goal.progress_percentage', '100.00')
            ->assertJsonPath('goal.status', 'completed');
    }

    public function test_goal_recalculation_does_not_repeat_task_counts_per_auto_target(): void
    {
        // Three tasks, two done — the count every auto 'tasks' target below
        // should converge on.
        Task::create(['organization_id' => $this->org->id, 'project_id' => $this->project->id, 'title' => 'A', 'task_number' => 1, 'status' => 'done', 'reporter_id' => $this->user->id]);
        Task::create(['organization_id' => $this->org->id, 'project_id' => $this->project->id, 'title' => 'B', 'task_number' => 2, 'status' => 'done', 'reporter_id' => $this->user->id]);
        Task::create(['organization_id' => $this->org->id, 'project_id' => $this->project->id, 'title' => 'C', 'task_number' => 3, 'status' => 'in_progress', 'reporter_id' => $this->user->id]);

        $goal = \App\Domains\Projects\Models\ProjectGoal::create([
            'organization_id' => $this->org->id,
            'project_id' => $this->project->id,
            'owner_id' => $this->user->id,
            'name' => 'Auto Task Tracking Goal',
        ]);

        // Four auto-computed 'tasks' targets (target_value <= 0 means
        // "derive from the project's task counts") — this is exactly the
        // shape that used to fire two fresh COUNT queries per target.
        for ($i = 0; $i < 4; $i++) {
            \App\Domains\Projects\Models\ProjectGoalTarget::create([
                'goal_id' => $goal->id,
                'title' => "Auto target {$i}",
                'target_type' => 'tasks',
                'start_value' => 0,
                'target_value' => 0,
                'current_value' => 0,
            ]);
        }

        \Illuminate\Support\Facades\DB::enableQueryLog();
        \Illuminate\Support\Facades\DB::flushQueryLog();
        $goal->recalculateProgress();
        $queryCountWithFourTargets = count(\Illuminate\Support\Facades\DB::getQueryLog());

        // Correctness: 2/3 tasks done -> 66.67%, in_progress (not complete).
        $goal->refresh();
        $this->assertEquals(66.67, (float) $goal->progress_percentage);
        $this->assertSame('in_progress', $goal->status);
        $goal->targets->each(function ($target) {
            $this->assertEquals(3, (float) $target->target_value);
            $this->assertEquals(2, (float) $target->current_value);
        });

        // Add four more identical auto targets — a per-target query (the
        // N+1 this guards against) would make the query count grow with it.
        for ($i = 4; $i < 8; $i++) {
            \App\Domains\Projects\Models\ProjectGoalTarget::create([
                'goal_id' => $goal->id,
                'title' => "Auto target {$i}",
                'target_type' => 'tasks',
                'start_value' => 0,
                'target_value' => 0,
                'current_value' => 0,
            ]);
        }

        \Illuminate\Support\Facades\DB::flushQueryLog();
        $goal->recalculateProgress();
        $queryCountWithEightTargets = count(\Illuminate\Support\Facades\DB::getQueryLog());

        // Each target still needs its own save() (a genuine per-row write,
        // not an N+1), so exact equality isn't the right bar. What this
        // guards against is the *count* queries scaling with target count:
        // pre-fix, doubling the targets would have roughly doubled the
        // query count (2 fresh COUNT queries per target); post-fix it
        // should grow by only ~1 query per extra target (its own save).
        $this->assertLessThan(
            $queryCountWithFourTargets * 1.5,
            $queryCountWithEightTargets,
            'recalculateProgress() query count grew roughly in proportion to target count — the task-count queries look like they are re-running per target again.'
        );
    }

    public function test_gantt_and_workload_matrix_endpoints(): void
    {
        // 1. Create tasks with estimated hours
        $taskA = Task::create([
            'organization_id' => $this->org->id,
            'project_id' => $this->project->id,
            'task_number' => 1,
            'title' => 'Frontend UI',
            'status' => 'in_progress',
            'assignee_id' => $this->user->id,
            'estimated_hours' => 20,
            'due_date' => now()->addDays(5),
            'creator_id' => $this->user->id,
            'reporter_id' => $this->user->id,
        ]);

        $taskB = Task::create([
            'organization_id' => $this->org->id,
            'project_id' => $this->project->id,
            'task_number' => 2,
            'title' => 'Backend API',
            'status' => 'backlog',
            'assignee_id' => $this->user->id,
            'estimated_hours' => 10,
            'due_date' => now()->addDays(10),
            'creator_id' => $this->user->id,
            'reporter_id' => $this->user->id,
        ]);

        // Link dependency (Task B depends on Task A)
        $taskB->dependencies()->create([
            'organization_id' => $this->org->id,
            'depends_on_task_id' => $taskA->id,
            'dependency_type' => 'finish_to_start',
        ]);

        // 2. Fetch Gantt data
        $ganttRes = $this->actingAs($this->user)->getJson(
            "/api/v1/organizations/{$this->org->id}/projects/{$this->project->id}/gantt"
        );

        $ganttRes->assertStatus(200)
            ->assertJsonCount(2, 'tasks')
            ->assertJsonPath('tasks.1.dependencies.0', $taskA->id);

        // 3. Fetch Workload matrix data
        $workloadRes = $this->actingAs($this->user)->getJson(
            "/api/v1/organizations/{$this->org->id}/projects/{$this->project->id}/workload"
        );

        $workloadRes->assertStatus(200)
            ->assertJsonCount(1, 'workload')
            ->assertJsonPath('workload.0.assigned_hours', 30) // 20h + 10h
            ->assertJsonPath('workload.0.weekly_capacity', 40)
            ->assertJsonPath('workload.0.utilization_percentage', 75);
    }
}
