<?php

namespace Tests\Feature\Projects;

use App\Domains\Projects\Models\ProjectSprint;
use App\Domains\Projects\Models\Task;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\Feature\Projects\Concerns\BuildsProjectWorkspace;
use Tests\TestCase;

class SprintLifecycleTest extends TestCase
{
    use BuildsProjectWorkspace, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->buildProjectWorkspace();
        $this->travelTo(now()->setDate(2026, 10, 5)->setTime(10, 0));
    }

    private function sprint(array $attrs = []): ProjectSprint
    {
        return ProjectSprint::create(array_merge([
            'organization_id' => $this->org->id,
            'project_id' => $this->project->id,
            'name' => 'S1',
            'start_date' => '2026-10-01',
            'end_date' => '2026-10-10',
            'status' => 'planned',
        ], $attrs));
    }

    public function test_only_one_sprint_can_be_active(): void
    {
        $a = $this->sprint();
        $b = $this->sprint(['name' => 'S2']);

        Sanctum::actingAs($this->manager);
        $this->postJson($this->api("/projects/{$this->project->id}/sprints/{$a->id}/start"))->assertOk();
        $this->postJson($this->api("/projects/{$this->project->id}/sprints/{$b->id}/start"))->assertUnprocessable();
        $this->postJson($this->api("/projects/{$this->project->id}/sprints/{$a->id}/start"))->assertUnprocessable();
    }

    public function test_burndown_counts_points_and_stops_at_today(): void
    {
        $s = $this->sprint(['status' => 'active']);
        $this->task->update(['sprint_id' => $s->id, 'story_points' => 5]);
        $this->makeTask(['sprint_id' => $s->id, 'story_points' => 3, 'status' => 'done', 'completed_at' => '2026-10-03 15:00:00']);

        Sanctum::actingAs($this->employee);
        $bd = $this->getJson($this->api("/projects/{$this->project->id}/sprints/{$s->id}"))->assertOk()->json('burndown');

        $this->assertSame('points', $bd['unit']);
        $this->assertEquals(8, $bd['total']);
        $this->assertEquals(3, $bd['completed']);
        $byDate = collect($bd['days'])->keyBy('date');
        $this->assertEquals(8, $byDate['2026-10-02']['remaining']);
        $this->assertEquals(5, $byDate['2026-10-03']['remaining']);
        $this->assertNull($byDate['2026-10-06']['remaining']);
        $this->assertEquals(8, $byDate['2026-10-01']['ideal']);
        $this->assertEquals(0, $byDate['2026-10-10']['ideal']);
    }

    public function test_completing_carries_unfinished_tasks_and_freezes_the_burndown(): void
    {
        $s1 = $this->sprint(['status' => 'active']);
        $s2 = $this->sprint(['name' => 'S2', 'start_date' => '2026-10-11', 'end_date' => '2026-10-20']);
        $this->task->update(['sprint_id' => $s1->id]);
        $done = $this->makeTask(['sprint_id' => $s1->id, 'status' => 'done', 'completed_at' => now()]);

        Sanctum::actingAs($this->manager);
        $this->postJson($this->api("/projects/{$this->project->id}/sprints/{$s1->id}/complete"), ['carry_to_sprint_id' => $s2->id])
            ->assertOk()->assertJsonPath('carried_over', 1);

        $this->assertSame($s2->id, $this->task->fresh()->sprint_id);
        $this->assertSame($s1->id, $done->fresh()->sprint_id);
        $s1->refresh();
        $this->assertSame('completed', $s1->status);
        $this->assertEquals(2, $s1->burndown_snapshot['total']);

        // The frozen burndown survives the carried task leaving.
        $this->getJson($this->api("/projects/{$this->project->id}/sprints/{$s1->id}"))->assertJsonPath('burndown.total', 2);
        $this->postJson($this->api("/projects/{$this->project->id}/sprints/{$s1->id}/complete"))->assertUnprocessable();
    }

    public function test_unfinished_tasks_return_to_the_backlog_by_default(): void
    {
        $s1 = $this->sprint(['status' => 'active']);
        $this->task->update(['sprint_id' => $s1->id]);

        Sanctum::actingAs($this->manager);
        $this->postJson($this->api("/projects/{$this->project->id}/sprints/{$s1->id}/complete"))->assertOk();

        $this->assertNull($this->task->fresh()->sprint_id);
    }

    public function test_cannot_carry_into_a_completed_or_foreign_sprint_nor_add_tasks_to_a_closed_one(): void
    {
        $s1 = $this->sprint(['status' => 'active']);
        $closed = $this->sprint(['name' => 'Old', 'status' => 'completed']);

        Sanctum::actingAs($this->manager);
        $this->postJson($this->api("/projects/{$this->project->id}/sprints/{$s1->id}/complete"), ['carry_to_sprint_id' => $closed->id])
            ->assertUnprocessable();
        $this->patchJson($this->api("/tasks/{$this->task->id}/sprint"), ['sprint_id' => $closed->id])->assertUnprocessable();
    }

    public function test_employees_cannot_run_the_sprint_lifecycle(): void
    {
        $s = $this->sprint();

        Sanctum::actingAs($this->employee);
        $this->postJson($this->api("/projects/{$this->project->id}/sprints/{$s->id}/start"))->assertForbidden();
        $this->deleteJson($this->api("/projects/{$this->project->id}/sprints/{$s->id}"))->assertForbidden();
    }

    public function test_deleting_a_sprint_returns_its_tasks_to_the_backlog(): void
    {
        $s = $this->sprint();
        $this->task->update(['sprint_id' => $s->id]);

        Sanctum::actingAs($this->manager);
        $this->deleteJson($this->api("/projects/{$this->project->id}/sprints/{$s->id}"))->assertOk();

        $this->assertNull($this->task->fresh()->sprint_id);
        $this->assertDatabaseMissing('project_sprints', ['id' => $s->id]);
    }
}
