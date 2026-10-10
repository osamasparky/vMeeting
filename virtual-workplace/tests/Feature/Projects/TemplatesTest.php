<?php

namespace Tests\Feature\Projects;

use App\Domains\Projects\Models\Project;
use App\Domains\Projects\Models\ProjectLabel;
use App\Domains\Projects\Models\ProjectMilestone;
use App\Domains\Projects\Models\ProjectState;
use App\Domains\Projects\Models\Task;
use App\Domains\Projects\Models\TaskTemplate;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\Feature\Projects\Concerns\BuildsProjectWorkspace;
use Tests\TestCase;

class TemplatesTest extends TestCase
{
    use BuildsProjectWorkspace, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->buildProjectWorkspace();
    }

    public function test_project_round_trips_through_a_template(): void
    {
        $this->project->update(['start_date' => '2026-01-01', 'due_date' => '2026-01-31']);
        ProjectState::ensureDefaults($this->project);
        ProjectState::create(['organization_id' => $this->org->id, 'project_id' => $this->project->id, 'name' => 'Client review', 'group' => 'review', 'position' => 9]);
        $label = ProjectLabel::create(['organization_id' => $this->org->id, 'project_id' => $this->project->id, 'name' => 'Design', 'tone' => 'gold']);
        $ms = ProjectMilestone::create(['organization_id' => $this->org->id, 'project_id' => $this->project->id, 'name' => 'Beta', 'due_date' => '2026-01-15']);
        $this->task->update(['title' => 'Wireframes', 'due_date' => '2026-01-10', 'milestone_id' => $ms->id, 'status' => 'done']);
        $this->task->labels()->attach($label);
        $this->task->checklistItems()->create(['organization_id' => $this->org->id, 'title' => 'Mobile']);

        Sanctum::actingAs($this->owner);
        $templateId = $this->postJson($this->api("/projects/{$this->project->id}/save-as-template"), ['name' => 'Website'])
            ->assertCreated()->json('template.id');

        $newId = $this->postJson($this->api('/projects'), ['name' => 'Client B site', 'template_id' => $templateId, 'start_date' => '2026-03-01'])
            ->assertCreated()->json('project.id');

        $new = Project::find($newId);
        $this->assertSame('2026-03-31', $new->due_date->format('Y-m-d'));
        $this->assertTrue($new->states()->where('name', 'Client review')->exists());
        $this->assertSame(['Design'], $new->labels()->pluck('name')->all());
        $this->assertSame('2026-03-15', $new->milestones()->first()->due_date->format('Y-m-d'));

        $copy = Task::where('project_id', $newId)->first();
        $this->assertSame('Wireframes', $copy->title);
        $this->assertSame('2026-03-10', $copy->due_date->format('Y-m-d'));
        $this->assertNotSame('done', $copy->status); // status starts fresh
        $this->assertSame('Beta', $copy->milestone->name);
        $this->assertSame(['Design'], $copy->labels->pluck('name')->all());
        $this->assertSame(['Mobile'], $copy->checklistItems->pluck('title')->all());
    }

    public function test_task_template_fills_the_new_task(): void
    {
        ProjectLabel::create(['organization_id' => $this->org->id, 'project_id' => $this->project->id, 'name' => 'Bug']);
        $this->task->update(['title' => 'Bug report', 'priority' => 'high', 'estimated_hours' => 3]);
        $this->task->labels()->attach(ProjectLabel::first());
        $this->task->checklistItems()->create(['organization_id' => $this->org->id, 'title' => 'Reproduce']);

        Sanctum::actingAs($this->employee);
        $templateId = $this->postJson($this->api("/tasks/{$this->task->id}/save-as-template"), ['name' => 'Bug'])->assertCreated()->json('template.id');

        $created = $this->postJson($this->api('/tasks'), ['project_id' => $this->project->id, 'template_id' => $templateId])
            ->assertCreated()->json('task');

        $task = Task::find($created['id']);
        $this->assertSame('Bug report', $task->title);
        $this->assertSame('high', $task->priority);
        $this->assertEquals(3, $task->estimated_hours);
        $this->assertSame(['Reproduce'], $task->checklistItems->pluck('title')->all());
        $this->assertSame(['Bug'], $task->labels->pluck('name')->all());
    }

    public function test_explicit_fields_beat_the_template(): void
    {
        $tpl = TaskTemplate::create(['organization_id' => $this->org->id, 'project_id' => $this->project->id, 'name' => 'T', 'payload' => ['title' => 'From template', 'priority' => 'low']]);

        Sanctum::actingAs($this->employee);
        $created = $this->postJson($this->api('/tasks'), ['project_id' => $this->project->id, 'template_id' => $tpl->id, 'title' => 'Mine', 'priority' => 'urgent'])->json('task');

        $this->assertSame('Mine', $created['title']);
        $this->assertSame('urgent', $created['priority']);
    }

    public function test_templates_are_scoped(): void
    {
        $foreign = TaskTemplate::create(['organization_id' => $this->otherOrg->id, 'project_id' => $this->foreignProject->id, 'name' => 'X', 'payload' => ['title' => 'x']]);
        $otherProject = Project::create(['organization_id' => $this->org->id, 'name' => 'P2', 'owner_id' => $this->owner->id]);
        $otherProjects = TaskTemplate::create(['organization_id' => $this->org->id, 'project_id' => $otherProject->id, 'name' => 'Y', 'payload' => ['title' => 'y']]);

        Sanctum::actingAs($this->employee);
        $this->postJson($this->api('/tasks'), ['project_id' => $this->project->id, 'template_id' => $foreign->id])->assertUnprocessable();
        $this->postJson($this->api('/tasks'), ['project_id' => $this->project->id, 'template_id' => $otherProjects->id])->assertUnprocessable();
        $this->getJson($this->api("/projects/{$this->project->id}/task-templates"))->assertJsonCount(0, 'templates');
    }

    public function test_employees_cannot_save_project_templates(): void
    {
        Sanctum::actingAs($this->employee);
        $this->postJson($this->api("/projects/{$this->project->id}/save-as-template"), ['name' => 'Mine'])->assertForbidden();
    }
}
