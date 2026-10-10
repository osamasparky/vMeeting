<?php

namespace Tests\Feature\Projects;

use App\Domains\Projects\Models\Project;
use App\Domains\Projects\Models\Task;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\Feature\Projects\Concerns\BuildsProjectWorkspace;
use Tests\TestCase;

class ArchiveAndRelationsTest extends TestCase
{
    use BuildsProjectWorkspace, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->buildProjectWorkspace();
    }

    public function test_deleting_a_task_archives_it_with_its_subtasks(): void
    {
        $child = $this->makeTask(['parent_task_id' => $this->task->id]);
        $grandchild = $this->makeTask(['parent_task_id' => $child->id]);

        Sanctum::actingAs($this->manager);
        $this->deleteJson($this->api("/tasks/{$this->task->id}"))->assertOk();

        $this->assertSoftDeleted($this->task, deletedAtColumn: 'archived_at');
        $this->assertSoftDeleted($child, deletedAtColumn: 'archived_at');
        $this->assertSoftDeleted($grandchild, deletedAtColumn: 'archived_at');
        $this->getJson($this->api("/tasks?project_id={$this->project->id}"))->assertJsonCount(0, 'data');
        $this->getJson($this->api("/archive/tasks?project_id={$this->project->id}"))->assertJsonCount(3, 'data');
    }

    public function test_restoring_brings_back_subtasks_archived_together_only(): void
    {
        $child = $this->makeTask(['parent_task_id' => $this->task->id]);
        $archivedEarlier = $this->makeTask(['parent_task_id' => $this->task->id]);
        $archivedEarlier->delete();
        $this->travel(5)->minutes();
        $this->task->delete();

        Sanctum::actingAs($this->manager);
        $this->postJson($this->api("/archive/tasks/{$this->task->id}/restore"))->assertOk();

        $this->assertNotSoftDeleted($this->task, deletedAtColumn: 'archived_at');
        $this->assertNotSoftDeleted($child, deletedAtColumn: 'archived_at');
        $this->assertSoftDeleted($archivedEarlier, deletedAtColumn: 'archived_at');
    }

    public function test_a_subtask_cannot_be_restored_before_its_parent(): void
    {
        $child = $this->makeTask(['parent_task_id' => $this->task->id]);
        $this->task->delete();

        Sanctum::actingAs($this->manager);
        $this->postJson($this->api("/archive/tasks/{$child->id}/restore"))->assertUnprocessable();
    }

    public function test_permanent_delete_only_from_the_archive(): void
    {
        Sanctum::actingAs($this->manager);
        $this->deleteJson($this->api("/archive/tasks/{$this->task->id}"))->assertNotFound();

        $this->task->delete();
        $this->deleteJson($this->api("/archive/tasks/{$this->task->id}"))->assertOk();
        $this->assertDatabaseMissing('tasks', ['id' => $this->task->id]);
    }

    public function test_employees_cannot_restore_or_purge(): void
    {
        $this->task->delete();

        Sanctum::actingAs($this->employee);
        $this->postJson($this->api("/archive/tasks/{$this->task->id}/restore"))->assertForbidden();
        $this->deleteJson($this->api("/archive/tasks/{$this->task->id}"))->assertForbidden();
    }

    public function test_archiving_a_done_task_updates_milestone_and_goal_rollups(): void
    {
        $open = $this->makeTask(['status' => 'in_progress']);
        $done = $this->makeTask(['status' => 'done']);
        $this->task->update(['status' => 'done']);

        Sanctum::actingAs($this->owner);
        $goalId = $this->postJson($this->api("/projects/{$this->project->id}/goals"), ['name' => 'Ship'])->json('goal.id');
        $this->postJson($this->api("/projects/{$this->project->id}/goals/{$goalId}/targets"), ['title' => 'T', 'target_type' => 'tasks', 'target_value' => 0])->assertCreated();

        $this->deleteJson($this->api("/tasks/{$open->id}"))->assertOk();

        $this->assertEquals(100.0, (float) $this->project->goals()->first()->progress_percentage);
    }

    public function test_projects_archive_restore_and_purge(): void
    {
        Sanctum::actingAs($this->owner);
        $this->deleteJson($this->api("/projects/{$this->project->id}"))->assertOk();
        $this->assertSoftDeleted($this->project, deletedAtColumn: 'archived_at');
        $this->getJson($this->api('/projects'))->assertJsonCount(0, 'data');
        $this->get("/projects/{$this->project->id}")->assertNotFound();

        $this->postJson($this->api("/archive/projects/{$this->project->id}/restore"))->assertOk();
        $this->getJson($this->api('/projects'))->assertJsonCount(1, 'data');

        Project::find($this->project->id)->delete();
        $this->deleteJson($this->api("/archive/projects/{$this->project->id}"))->assertOk();
        $this->assertDatabaseMissing('projects', ['id' => $this->project->id]);
        $this->assertSame(0, Task::withTrashed()->where('project_id', $this->project->id)->count());
    }

    public function test_relations_read_from_both_sides(): void
    {
        $other = $this->makeTask();

        Sanctum::actingAs($this->employee);
        $this->postJson($this->api("/tasks/{$this->task->id}/relations"), ['related_task_id' => $other->id, 'type' => 'duplicate_of'])
            ->assertCreated()->assertJsonPath('relations.0.kind', 'duplicate_of');

        $this->getJson($this->api("/tasks/{$other->id}/relations"))->assertJsonPath('relations.0.kind', 'duplicated_by');
        $this->getJson($this->api("/tasks/{$this->task->id}"))->assertJsonPath('task.task_relations.0.task.id', $other->id);
    }

    public function test_relations_are_deduplicated_and_scoped(): void
    {
        $other = $this->makeTask();
        $foreign = Task::create(['organization_id' => $this->otherOrg->id, 'project_id' => $this->foreignProject->id, 'title' => 'x', 'reporter_id' => $this->outsider->id]);

        Sanctum::actingAs($this->manager);
        $this->postJson($this->api("/tasks/{$this->task->id}/relations"), ['related_task_id' => $other->id, 'type' => 'relates_to'])->assertCreated();
        $this->postJson($this->api("/tasks/{$other->id}/relations"), ['related_task_id' => $this->task->id, 'type' => 'relates_to'])
            ->assertCreated()->assertJsonCount(1, 'relations');
        $this->postJson($this->api("/tasks/{$this->task->id}/relations"), ['related_task_id' => $this->task->id, 'type' => 'relates_to'])->assertUnprocessable();
        $this->postJson($this->api("/tasks/{$this->task->id}/relations"), ['related_task_id' => $foreign->id, 'type' => 'relates_to'])->assertUnprocessable();

        $relationId = $this->getJson($this->api("/tasks/{$other->id}/relations"))->json('relations.0.id');
        $this->deleteJson($this->api("/tasks/{$other->id}/relations/{$relationId}"))->assertOk()->assertJsonCount(0, 'relations');
    }
}
