<?php

namespace Tests\Feature\Projects;

use App\Domains\Projects\Models\TimeEntry;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Projects\Concerns\BuildsProjectWorkspace;
use Tests\TestCase;

/**
 * Employees see the projects they work on, without the money; leads see all.
 * Hours typed in by hand are the owner's to add.
 *
 * In the fixture $employee has a task in $project; $otherEmployee has none.
 */
class ProjectVisibilityAndManualTimeTest extends TestCase
{
    use BuildsProjectWorkspace, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->buildProjectWorkspace();
        $this->project->update(['budget_amount' => 50000]);
    }

    public function test_an_employee_without_tasks_in_a_project_cannot_see_it(): void
    {
        $this->actingAs($this->otherEmployee)->getJson($this->api('/projects'))
            ->assertOk()->assertJsonCount(0, 'data');
        $this->actingAs($this->otherEmployee)->getJson($this->api("/projects/{$this->project->id}"))->assertNotFound();
        $this->actingAs($this->otherEmployee)->getJson($this->api("/projects/{$this->project->id}/gantt"))->assertNotFound();
        $this->actingAs($this->otherEmployee)->get("/projects/{$this->project->id}")->assertNotFound();
        $this->actingAs($this->otherEmployee)->postJson($this->api('/tasks'), ['project_id' => $this->project->id, 'title' => 'x'])->assertNotFound();
    }

    public function test_an_employee_sees_a_project_they_have_a_task_in_but_no_money(): void
    {
        $this->actingAs($this->employee)->getJson($this->api('/projects'))
            ->assertOk()->assertJsonCount(1, 'data')->assertJsonMissingPath('data.0.budget_amount');

        $this->actingAs($this->employee)->getJson($this->api("/projects/{$this->project->id}"))
            ->assertOk()
            ->assertJsonMissingPath('project.budget_amount')
            ->assertJsonPath('kpis.budget_amount', null)
            ->assertJsonPath('kpis.labor_cost', null)
            ->assertJsonPath('kpis.gross_margin_pct', null);

        $this->actingAs($this->employee)->get("/projects/{$this->project->id}")
            ->assertOk()->assertDontSee(__('hub.kpi_budget'))->assertDontSee(__('Billing Rate'));
    }

    public function test_the_dashboard_lists_only_visible_projects_without_budget(): void
    {
        $html = $this->actingAs($this->otherEmployee)->get('/dashboard')->assertOk()->getContent();
        $this->assertStringNotContainsString('Main Project', $html);
        $this->assertStringNotContainsString(__('Total Budget'), $html);
    }

    public function test_the_owner_sees_everything_and_the_budget(): void
    {
        $this->actingAs($this->owner)->getJson($this->api("/projects/{$this->project->id}"))
            ->assertOk()->assertJsonPath('kpis.budget_amount', 50000);
        $this->actingAs($this->owner)->get("/projects/{$this->project->id}")->assertOk()->assertSee(__('hub.kpi_budget'));
    }

    public function test_a_lead_sees_every_project(): void
    {
        $this->project->update(['manager_id' => $this->owner->id]);
        $this->actingAs($this->manager)->getJson($this->api('/projects'))->assertOk()->assertJsonCount(1, 'data');
    }

    public function test_an_employee_cannot_log_time_by_hand(): void
    {
        $this->actingAs($this->employee)->postJson($this->api('/time/entries/manual'), [
            'project_id' => $this->project->id, 'started_at' => '2026-10-15 09:00', 'ended_at' => '2026-10-15 17:00',
        ])->assertForbidden();
        $this->actingAs($this->employee)->postJson($this->api('/time-entries/manual'), [
            'project_id' => $this->project->id, 'started_at' => '2026-10-15 09:00', 'ended_at' => '2026-10-15 17:00',
        ])->assertForbidden();

        $this->assertSame(0, TimeEntry::count());
        $this->actingAs($this->employee)->get('/dashboard')->assertOk()->assertDontSee('onclick="openManualTimeModal()"', false);
    }

    public function test_the_owner_logs_time_for_an_employee(): void
    {
        $this->actingAs($this->owner)->postJson($this->api('/time/entries/manual'), [
            'project_id' => $this->project->id, 'user_id' => $this->employee->id,
            'started_at' => '2026-10-15 09:00', 'ended_at' => '2026-10-15 11:00',
        ])->assertCreated();

        $entry = TimeEntry::firstOrFail();
        $this->assertSame($this->employee->id, $entry->user_id);
        $this->assertSame(7200, $entry->duration_seconds);
    }

    public function test_an_employee_cannot_stretch_their_own_entry(): void
    {
        $entry = TimeEntry::create([
            'organization_id' => $this->org->id, 'user_id' => $this->employee->id, 'project_id' => $this->project->id,
            'started_at' => now()->subHour(), 'ended_at' => now(), 'duration_seconds' => 3600,
            'entry_type' => TimeEntry::TYPE_TIMER, 'status' => TimeEntry::STATUS_DRAFT,
        ]);

        $this->actingAs($this->employee)->patchJson($this->api("/time/entries/{$entry->id}"), [
            'ended_at' => now()->addHours(7)->format('Y-m-d H:i'),
        ])->assertForbidden();
        $this->assertSame(3600, $entry->fresh()->duration_seconds);

        $this->actingAs($this->employee)->patchJson($this->api("/time/entries/{$entry->id}"), ['description' => 'Fixed the login bug'])
            ->assertOk();
    }
}
