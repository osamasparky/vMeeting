<?php

namespace Tests\Feature\Projects;

use App\Domains\Projects\Models\ProjectUpdate;
use App\Domains\Projects\Models\TimeEntry;
use App\Domains\Tenancy\Models\OrganizationMember;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\Feature\Projects\Concerns\BuildsProjectWorkspace;
use Tests\TestCase;

class MyWorkTest extends TestCase
{
    use BuildsProjectWorkspace, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->buildProjectWorkspace();
        // Start from a clean slate: the shared fixture task is assigned to the employee.
        $this->task->forceDelete();
    }

    public function test_planner_lists_open_work_across_projects_by_scope(): void
    {
        $mine = $this->makeTask(['title' => 'Mine', 'assignee_id' => $this->employee->id, 'due_date' => now()->subDay()]);
        $this->makeTask(['title' => 'Done', 'assignee_id' => $this->employee->id, 'status' => 'done', 'completed_at' => now()]);
        $this->makeTask(['title' => 'Someone else', 'assignee_id' => $this->otherEmployee->id]);
        $created = $this->makeTask(['title' => 'Reported', 'reporter_id' => $this->employee->id]);
        $followed = $this->makeTask(['title' => 'Followed']);
        $followed->addWatchers([$this->employee->id]);
        // Another organization's task never leaks in.
        $this->foreignProject->tasks()->create(['organization_id' => $this->otherOrg->id, 'title' => 'Foreign', 'task_number' => 99, 'reporter_id' => $this->outsider->id, 'assignee_id' => $this->employee->id]);

        Sanctum::actingAs($this->employee);
        $res = $this->getJson($this->api('/my-work/tasks'))->assertOk();
        $this->assertSame(['Mine'], array_column($res->json('tasks'), 'title'));
        $this->assertSame(1, $res->json('done_this_week'));
        $this->assertSame('MAIN', $res->json('tasks.0.project.code'));

        $this->assertContains($created->title, array_column($this->getJson($this->api('/my-work/tasks?scope=created'))->json('tasks'), 'title'));
        // Assignees and reporters follow their tasks automatically, so they show here too.
        $following = array_column($this->getJson($this->api('/my-work/tasks?scope=following'))->json('tasks'), 'title');
        $this->assertContains('Followed', $following);
        $this->assertNotContains('Someone else', $following);
    }

    public function test_dashboard_defaults_and_widget_data(): void
    {
        $this->makeTask(['assignee_id' => $this->employee->id, 'due_date' => now()->subDay(), 'status' => 'in_progress']);
        $this->makeTask(['assignee_id' => $this->employee->id, 'status' => 'done', 'completed_at' => now()]);
        TimeEntry::create(['organization_id' => $this->org->id, 'user_id' => $this->employee->id, 'project_id' => $this->project->id,
            'started_at' => now()->startOfDay()->addHours(9), 'ended_at' => now()->startOfDay()->addHours(10)->addMinutes(30), 'duration_seconds' => 5400]);
        $this->project->members()->create(['organization_id' => $this->org->id, 'user_id' => $this->employee->id]);
        ProjectUpdate::create(['organization_id' => $this->org->id, 'project_id' => $this->project->id, 'user_id' => $this->manager->id, 'health' => 'at_risk', 'body' => 'x']);

        Sanctum::actingAs($this->employee);
        $res = $this->getJson($this->api('/my-work/dashboard'))->assertOk();

        $this->assertSame(['summary', 'upcoming', 'by_state', 'completed_trend', 'time_week', 'project_health'], $res->json('widgets'));
        $this->assertSame(1, $res->json('data.summary.open'));
        $this->assertSame(1, $res->json('data.summary.overdue'));
        $this->assertSame(1, $res->json('data.summary.done_week'));
        $this->assertSame(1, collect($res->json('data.by_state'))->firstWhere('key', 'in_progress')['value']);
        $this->assertCount(14, $res->json('data.completed_trend'));
        $trend = $res->json('data.completed_trend');
        $this->assertSame(1, end($trend)['value']);
        $this->assertEquals(1.5, $res->json('data.time_week.total'));
        $this->assertSame('at_risk', $res->json('data.project_health.0.health'));
    }

    public function test_layout_is_saved_per_member_and_validated(): void
    {
        Sanctum::actingAs($this->employee);
        $this->putJson($this->api('/my-work/dashboard'), ['widgets' => ['by_project', 'summary']])
            ->assertOk()->assertJsonPath('widgets', ['by_project', 'summary'])->assertJsonStructure(['data' => ['by_project', 'summary']]);

        $this->assertSame(['by_project', 'summary'], OrganizationMember::where('user_id', $this->employee->id)->where('organization_id', $this->org->id)->first()->dashboard_widgets);
        $this->putJson($this->api('/my-work/dashboard'), ['widgets' => ['nope']])->assertUnprocessable();
        $this->putJson($this->api('/my-work/dashboard'), ['widgets' => ['summary', 'summary']])->assertUnprocessable();

        // An empty dashboard is allowed and stays empty.
        $this->putJson($this->api('/my-work/dashboard'), ['widgets' => []])->assertOk()->assertJsonPath('widgets', []);
    }
}
