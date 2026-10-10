<?php

namespace Tests\Feature\Projects;

use App\Domains\Projects\Models\ProjectState;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\Feature\Projects\Concerns\BuildsProjectWorkspace;
use Tests\TestCase;

class ProjectStatesTest extends TestCase
{
    use BuildsProjectWorkspace, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->buildProjectWorkspace();
        ProjectState::ensureDefaults($this->project);
    }

    private function state(string $group): ProjectState
    {
        return $this->project->states()->where('group', $group)->first();
    }

    public function test_new_projects_get_the_standard_states(): void
    {
        Sanctum::actingAs($this->owner);
        $id = $this->postJson($this->api('/projects'), ['name' => 'Fresh'])->assertCreated()->json('project.id');

        $this->getJson($this->api("/projects/{$id}/states"))->assertOk()
            ->assertJsonCount(5, 'states')->assertJsonPath('states.0.group', 'backlog');
    }

    public function test_tasks_land_in_the_state_of_their_status(): void
    {
        $task = $this->makeTask(['status' => 'ready']);
        $this->assertSame($this->state('ready')->id, $task->state_id);

        $task->update(['status' => 'done']);
        $this->assertSame($this->state('done')->id, $task->fresh()->state_id);
    }

    public function test_moving_to_a_custom_state_sets_its_group_as_status(): void
    {
        Sanctum::actingAs($this->manager);
        $design = $this->postJson($this->api("/projects/{$this->project->id}/states"), ['name' => 'Design review', 'group' => 'review'])
            ->assertCreated()->json('state');

        $this->patchJson($this->api("/tasks/{$this->task->id}/status"), ['state_id' => $design['id']])->assertOk();

        $this->task->refresh();
        $this->assertSame('review', $this->task->status);
        $this->assertSame($design['id'], $this->task->state_id);
    }

    public function test_member_asking_for_a_done_state_is_routed_to_review_for_approval(): void
    {
        Sanctum::actingAs($this->employee);
        $this->patchJson($this->api("/tasks/{$this->task->id}/status"), ['state_id' => $this->state('done')->id])->assertOk();

        $this->task->refresh();
        $this->assertSame('review', $this->task->status);
        $this->assertSame($this->state('review')->id, $this->task->state_id);
        $this->assertSame('pending_approval', $this->task->approval_status);
    }

    public function test_states_are_scoped_to_the_project(): void
    {
        $foreign = ProjectState::create(['organization_id' => $this->otherOrg->id, 'project_id' => $this->foreignProject->id, 'name' => 'X', 'group' => 'done']);

        Sanctum::actingAs($this->manager);
        $this->patchJson($this->api("/tasks/{$this->task->id}/status"), ['state_id' => $foreign->id])->assertUnprocessable();
    }

    public function test_reorder_default_and_delete_rules(): void
    {
        Sanctum::actingAs($this->manager);
        $ready = $this->state('ready');
        $this->patchJson($this->api("/projects/{$this->project->id}/states/{$ready->id}"), ['move' => 'up'])->assertOk()
            ->assertJsonPath('states.0.id', $ready->id);

        $this->patchJson($this->api("/projects/{$this->project->id}/states/{$ready->id}"), ['is_default' => true])->assertOk();
        $this->assertSame(1, $this->project->states()->where('is_default', true)->count());
        $created = $this->postJson($this->api('/tasks'), ['project_id' => $this->project->id, 'title' => 'New'])->json('task');
        $this->assertSame('ready', $created['status']);

        // The only state of a group can't go; a second one can, and its tasks move.
        $this->deleteJson($this->api("/projects/{$this->project->id}/states/{$this->state('in_progress')->id}"))->assertUnprocessable();
        $extra = $this->postJson($this->api("/projects/{$this->project->id}/states"), ['name' => 'Doing 2', 'group' => 'in_progress'])->json('state');
        $this->patchJson($this->api("/tasks/{$this->task->id}/status"), ['state_id' => $extra['id']])->assertOk();
        $this->deleteJson($this->api("/projects/{$this->project->id}/states/{$extra['id']}"))->assertOk();
        $this->assertSame($this->state('in_progress')->id, $this->task->fresh()->state_id);
    }

    public function test_employees_cannot_manage_states(): void
    {
        Sanctum::actingAs($this->employee);
        $this->postJson($this->api("/projects/{$this->project->id}/states"), ['name' => 'Mine', 'group' => 'ready'])->assertForbidden();
    }

    public function test_board_renders_custom_columns(): void
    {
        ProjectState::create(['organization_id' => $this->org->id, 'project_id' => $this->project->id, 'name' => 'Waiting on client', 'group' => 'in_progress', 'position' => 9]);

        $this->actingAs($this->owner)->get("/projects/{$this->project->id}")->assertOk()->assertSee('Waiting on client');
    }
}
