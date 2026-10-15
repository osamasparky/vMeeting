<?php

namespace Tests\Feature\Projects;

use App\Domains\Projects\Models\ProjectMember;
use App\Domains\Projects\Models\SavedView;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\Feature\Projects\Concerns\BuildsProjectWorkspace;
use Tests\TestCase;

class SavedViewsAndInlineEditTest extends TestCase
{
    use BuildsProjectWorkspace, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->buildProjectWorkspace();
        // Both employees work on the project; these tests are about view ownership, not visibility.
        ProjectMember::create(['organization_id' => $this->org->id, 'project_id' => $this->project->id, 'user_id' => $this->otherEmployee->id]);
    }

    public function test_member_saves_a_private_view_with_known_filters_only(): void
    {
        Sanctum::actingAs($this->employee);
        $this->postJson($this->api("/projects/{$this->project->id}/views"), [
            'name' => 'My urgent',
            'layout' => 'calendar',
            'filters' => ['priority' => 'urgent', 'label' => '', 'evil' => 'x'],
        ])->assertCreated()
            ->assertJsonPath('view.layout', 'calendar')
            ->assertJsonPath('view.filters', ['priority' => 'urgent']);

        $this->postJson($this->api("/projects/{$this->project->id}/views"), ['name' => 'Bad', 'layout' => 'gantt'])
            ->assertUnprocessable();
    }

    public function test_private_views_are_invisible_to_others_and_shared_ones_are_listed(): void
    {
        SavedView::create(['organization_id' => $this->org->id, 'project_id' => $this->project->id, 'user_id' => $this->employee->id, 'name' => 'Mine']);
        SavedView::create(['organization_id' => $this->org->id, 'project_id' => $this->project->id, 'user_id' => $this->employee->id, 'name' => 'Team', 'is_shared' => true]);

        Sanctum::actingAs($this->otherEmployee);
        $this->getJson($this->api("/projects/{$this->project->id}/views"))->assertOk()
            ->assertJsonCount(1, 'views')->assertJsonPath('views.0.name', 'Team');
    }

    public function test_only_the_owner_or_a_manager_can_remove_a_view(): void
    {
        $private = SavedView::create(['organization_id' => $this->org->id, 'project_id' => $this->project->id, 'user_id' => $this->employee->id, 'name' => 'Mine']);
        $shared = SavedView::create(['organization_id' => $this->org->id, 'project_id' => $this->project->id, 'user_id' => $this->employee->id, 'name' => 'Team', 'is_shared' => true]);

        Sanctum::actingAs($this->otherEmployee);
        $this->deleteJson($this->api("/projects/{$this->project->id}/views/{$shared->id}"))->assertForbidden();
        $this->patchJson($this->api("/projects/{$this->project->id}/views/{$private->id}"), ['name' => 'x'])->assertForbidden();

        Sanctum::actingAs($this->manager);
        $this->deleteJson($this->api("/projects/{$this->project->id}/views/{$shared->id}"))->assertOk();
        $this->deleteJson($this->api("/projects/{$this->project->id}/views/{$private->id}"))->assertForbidden();

        Sanctum::actingAs($this->employee);
        $this->patchJson($this->api("/projects/{$this->project->id}/views/{$private->id}"), ['name' => 'Renamed'])->assertOk()
            ->assertJsonPath('view.name', 'Renamed');
    }

    public function test_calendar_reschedule_updates_the_due_date(): void
    {
        Sanctum::actingAs($this->employee);
        $this->patchJson($this->api("/tasks/{$this->task->id}"), ['due_date' => '2026-11-03'])->assertOk();

        $this->assertSame('2026-11-03', $this->task->fresh()->due_date->format('Y-m-d'));
    }

    public function test_reassigning_through_the_generic_update_needs_team_lead_rights(): void
    {
        Sanctum::actingAs($this->employee);
        $this->patchJson($this->api("/tasks/{$this->task->id}"), ['assignee_id' => $this->otherEmployee->id])->assertForbidden();

        Sanctum::actingAs($this->manager);
        $this->patchJson($this->api("/tasks/{$this->task->id}"), ['assignee_id' => $this->otherEmployee->id])->assertOk();
        $this->assertSame($this->otherEmployee->id, $this->task->fresh()->assignee_id);
    }

    public function test_hub_renders_the_calendar_tab_and_views_control(): void
    {
        $this->actingAs($this->owner)->get("/projects/{$this->project->id}")
            ->assertOk()
            ->assertSee('id="hub-section-calendar"', false)
            ->assertSee('id="pm-views-btn"', false)
            ->assertSee('class="pm-cell-select"', false);
    }
}
