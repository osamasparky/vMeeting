<?php

namespace Tests\Feature\Projects;

use App\Domains\Notifications\Models\WorkplaceNotification;
use App\Domains\Projects\Models\ProjectMember;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Laravel\Sanctum\Sanctum;
use Tests\Feature\Projects\Concerns\BuildsProjectWorkspace;
use Tests\TestCase;

class ProjectUpdatesTest extends TestCase
{
    use BuildsProjectWorkspace, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Mail::fake();
        $this->buildProjectWorkspace();
        ProjectMember::create(['organization_id' => $this->org->id, 'project_id' => $this->project->id, 'user_id' => $this->employee->id]);
        // On the project too, so the checks below are about authorship, not visibility.
        ProjectMember::create(['organization_id' => $this->org->id, 'project_id' => $this->project->id, 'user_id' => $this->otherEmployee->id]);
    }

    public function test_posting_an_update_sets_health_and_notifies_the_project(): void
    {
        Sanctum::actingAs($this->manager);
        $this->postJson($this->api("/projects/{$this->project->id}/updates"), ['health' => 'at_risk', 'body' => 'Vendor is late'])
            ->assertCreated()->assertJsonPath('update.health', 'at_risk');

        $this->assertSame('at_risk', $this->project->fresh()->latestUpdate->health);
        $this->assertSame(1, WorkplaceNotification::where('user_id', $this->employee->id)->where('type', 'project_update')->count());
        $this->assertSame(0, WorkplaceNotification::where('user_id', $this->manager->id)->where('type', 'project_update')->count());
    }

    public function test_health_and_body_are_validated(): void
    {
        Sanctum::actingAs($this->manager);
        $this->postJson($this->api("/projects/{$this->project->id}/updates"), ['health' => 'great', 'body' => 'x'])->assertUnprocessable();
        $this->postJson($this->api("/projects/{$this->project->id}/updates"), ['health' => 'on_track'])->assertUnprocessable();
    }

    public function test_only_author_or_manager_can_delete(): void
    {
        Sanctum::actingAs($this->employee);
        $id = $this->postJson($this->api("/projects/{$this->project->id}/updates"), ['health' => 'on_track', 'body' => 'Fine'])->json('update.id');

        Sanctum::actingAs($this->otherEmployee);
        $this->deleteJson($this->api("/projects/{$this->project->id}/updates/{$id}"))->assertForbidden();

        Sanctum::actingAs($this->manager);
        $this->deleteJson($this->api("/projects/{$this->project->id}/updates/{$id}"))->assertOk();
    }

    public function test_updates_are_listed_newest_first_and_scoped(): void
    {
        Sanctum::actingAs($this->manager);
        $this->postJson($this->api("/projects/{$this->project->id}/updates"), ['health' => 'on_track', 'body' => 'One']);
        $this->travel(1)->minutes();
        $this->postJson($this->api("/projects/{$this->project->id}/updates"), ['health' => 'off_track', 'body' => 'Two']);

        $this->getJson($this->api("/projects/{$this->project->id}/updates"))->assertOk()->assertJsonPath('updates.0.body', 'Two');

        Sanctum::actingAs($this->outsider);
        $this->getJson("/api/v1/organizations/{$this->otherOrg->id}/projects/{$this->project->id}/updates")->assertNotFound();
    }

    public function test_overview_tab_renders_health(): void
    {
        Sanctum::actingAs($this->manager);
        $this->postJson($this->api("/projects/{$this->project->id}/updates"), ['health' => 'off_track', 'body' => 'Blocked']);

        $this->actingAs($this->owner)->get("/projects/{$this->project->id}")
            ->assertOk()->assertSee('id="hub-section-overview"', false)->assertSee(__('pm.health_off_track'));
    }
}
