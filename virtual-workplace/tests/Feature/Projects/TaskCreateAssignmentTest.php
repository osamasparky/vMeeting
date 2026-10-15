<?php

namespace Tests\Feature\Projects;

use App\Domains\Projects\Models\Task;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Projects\Concerns\BuildsProjectWorkspace;
use Tests\TestCase;

/**
 * Creating a task for someone else is a lead's action (owner, tasks.assign,
 * the project's manager); anyone else may only take the task themselves or
 * leave it unassigned. Every task records who created it.
 */
class TaskCreateAssignmentTest extends TestCase
{
    use BuildsProjectWorkspace, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->buildProjectWorkspace();
    }

    private function create($as, array $data)
    {
        return $this->actingAs($as)->postJson($this->api('/tasks'), $data + ['project_id' => $this->project->id, 'title' => 'New work']);
    }

    public function test_an_employee_cannot_hand_a_new_task_to_a_colleague(): void
    {
        $this->create($this->employee, ['assignee_id' => $this->otherEmployee->id])
            ->assertStatus(422)->assertJsonValidationErrors('assignee_id');
        $this->create($this->employee, ['assignee_ids' => [$this->employee->id, $this->otherEmployee->id]])
            ->assertStatus(422);

        $this->assertSame(0, Task::where('title', 'New work')->count());
    }

    public function test_an_employee_may_take_a_task_or_leave_it_open(): void
    {
        $this->create($this->employee, ['assignee_id' => $this->employee->id])->assertCreated();
        $this->create($this->employee, [])->assertCreated();

        $task = Task::where('title', 'New work')->whereNotNull('assignee_id')->firstOrFail();
        $this->assertSame($this->employee->id, $task->reporter_id, 'the creator is recorded');
    }

    public function test_leads_assign_anyone(): void
    {
        $this->create($this->owner, ['assignee_id' => $this->employee->id])->assertCreated();
        $this->create($this->manager, ['assignee_id' => $this->otherEmployee->id])->assertCreated();
    }

    public function test_the_project_manager_assigns_in_their_project(): void
    {
        $this->project->update(['manager_id' => $this->employee->id]);

        $this->create($this->employee, ['assignee_id' => $this->otherEmployee->id])->assertCreated();
    }

    public function test_the_task_details_name_the_creator(): void
    {
        $this->create($this->owner, ['assignee_id' => $this->employee->id])->assertCreated();
        $task = Task::where('title', 'New work')->firstOrFail();

        $this->actingAs($this->employee)->getJson($this->api("/tasks/{$task->id}"))
            ->assertOk()
            ->assertJsonPath('task.reporter.id', $this->owner->id)
            ->assertJsonPath('task.reporter.name', $this->owner->name);
    }
}
