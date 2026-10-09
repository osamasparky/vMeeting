<?php

namespace Tests\Feature\Projects;

use App\Domains\Projects\Models\ProjectLabel;
use App\Domains\Projects\Models\Task;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\Feature\Projects\Concerns\BuildsProjectWorkspace;
use Tests\TestCase;

class LabelsAndAssigneesTest extends TestCase
{
    use BuildsProjectWorkspace, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->buildProjectWorkspace();
    }

    private function label(string $name, string $tone = 'palm'): ProjectLabel
    {
        return ProjectLabel::create(['organization_id' => $this->org->id, 'project_id' => $this->project->id, 'name' => $name, 'tone' => $tone]);
    }

    public function test_labels_can_be_managed_on_a_project(): void
    {
        Sanctum::actingAs($this->manager);

        $id = $this->postJson($this->api("/projects/{$this->project->id}/labels"), ['name' => 'Bug', 'tone' => 'terracotta'])
            ->assertCreated()->json('label.id');
        $this->postJson($this->api("/projects/{$this->project->id}/labels"), ['name' => 'Bug'])->assertUnprocessable();
        $this->postJson($this->api("/projects/{$this->project->id}/labels"), ['name' => 'X', 'tone' => 'neon'])->assertUnprocessable();

        $this->patchJson($this->api("/projects/{$this->project->id}/labels/{$id}"), ['name' => 'Defect'])->assertOk();
        $this->getJson($this->api("/projects/{$this->project->id}/labels"))->assertOk()->assertJsonPath('labels.0.name', 'Defect');

        $this->deleteJson($this->api("/projects/{$this->project->id}/labels/{$id}"))->assertOk();
        $this->assertDatabaseCount('project_labels', 0);
    }

    public function test_task_labels_are_set_and_must_belong_to_the_project(): void
    {
        $bug = $this->label('Bug');
        $foreign = ProjectLabel::create(['organization_id' => $this->otherOrg->id, 'project_id' => $this->foreignProject->id, 'name' => 'Theirs']);

        Sanctum::actingAs($this->employee);
        $this->putJson($this->api("/tasks/{$this->task->id}/labels"), ['label_ids' => [$bug->id]])->assertOk()
            ->assertJsonPath('labels.0.name', 'Bug');
        $this->putJson($this->api("/tasks/{$this->task->id}/labels"), ['label_ids' => [$foreign->id]])->assertUnprocessable();

        $this->putJson($this->api("/tasks/{$this->task->id}/labels"), ['label_ids' => []])->assertOk();
        $this->assertCount(0, $this->task->labels()->get());
    }

    public function test_tasks_can_be_filtered_by_label(): void
    {
        $bug = $this->label('Bug');
        $this->task->labels()->attach($bug);
        $this->makeTask();

        Sanctum::actingAs($this->owner);
        $this->getJson($this->api("/tasks?project_id={$this->project->id}&label_id={$bug->id}"))->assertOk()
            ->assertJsonCount(1, 'data')->assertJsonPath('data.0.id', $this->task->id);
    }

    public function test_task_can_have_several_assignees_with_a_primary(): void
    {
        Sanctum::actingAs($this->manager);
        $this->putJson($this->api("/tasks/{$this->task->id}/assignees"), ['user_ids' => [$this->otherEmployee->id, $this->employee->id]])
            ->assertOk();

        $this->task->refresh();
        $this->assertSame($this->otherEmployee->id, $this->task->assignee_id);
        $this->assertEqualsCanonicalizing([$this->otherEmployee->id, $this->employee->id], $this->task->assignees()->pluck('users.id')->all());

        $this->putJson($this->api("/tasks/{$this->task->id}/assignees"), ['user_ids' => [$this->outsider->id]])->assertUnprocessable();
        $this->putJson($this->api("/tasks/{$this->task->id}/assignees"), ['user_ids' => []])->assertOk();
        $this->assertNull($this->task->fresh()->assignee_id);
    }

    public function test_employees_cannot_reassign_tasks(): void
    {
        Sanctum::actingAs($this->employee);
        $this->putJson($this->api("/tasks/{$this->task->id}/assignees"), ['user_ids' => [$this->employee->id]])->assertForbidden();
    }

    public function test_single_assignee_paths_keep_the_list_in_step(): void
    {
        $this->assertSame([$this->employee->id], $this->task->assignees()->pluck('users.id')->all());

        $this->task->update(['assignee_id' => $this->otherEmployee->id]);

        $this->assertSame([$this->otherEmployee->id], $this->task->assignees()->pluck('users.id')->all());
    }

    public function test_additional_assignee_sees_the_task_and_may_edit_it(): void
    {
        $this->task->assignees()->attach($this->otherEmployee->id);

        Sanctum::actingAs($this->otherEmployee);
        $this->getJson($this->api('/tasks/my-tasks'))->assertOk()->assertJsonPath('summary.total', 1);
        $this->patchJson($this->api("/tasks/{$this->task->id}"), ['title' => 'Shared work'])->assertOk();
        $this->getJson($this->api("/tasks?assignee_id={$this->otherEmployee->id}"))->assertJsonCount(1, 'data');
    }

    public function test_task_is_created_with_labels_and_assignees(): void
    {
        $bug = $this->label('Bug');

        Sanctum::actingAs($this->manager);
        $id = $this->postJson($this->api('/tasks'), [
            'project_id' => $this->project->id,
            'title' => 'Pair work',
            'assignee_ids' => [$this->employee->id, $this->otherEmployee->id],
            'label_ids' => [$bug->id],
        ])->assertCreated()->json('task.id');

        $task = Task::find($id);
        $this->assertSame($this->employee->id, $task->assignee_id);
        $this->assertCount(2, $task->assignees);
        $this->assertSame(['Bug'], $task->labels->pluck('name')->all());
    }
}
