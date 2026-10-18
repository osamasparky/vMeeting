<?php

namespace Tests\Feature\Projects;

use App\Domains\People\Models\Department;
use App\Domains\People\Models\UserProfile;
use App\Domains\Projects\Models\Project;
use App\Domains\Projects\Models\ProjectMember;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Projects\Concerns\BuildsProjectWorkspace;
use Tests\TestCase;

/** Editing a project after it is created, and choosing who works on it — at creation or any time. */
class ProjectEditAndMembersTest extends TestCase
{
    use BuildsProjectWorkspace, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->buildProjectWorkspace();
    }

    private function memberIds(Project $project): array
    {
        return ProjectMember::where('project_id', $project->id)->pluck('user_id')->sort()->values()->all();
    }

    public function test_project_created_with_people_from_several_departments(): void
    {
        $design = Department::create(['organization_id' => $this->org->id, 'name' => 'Design']);
        $sales = Department::create(['organization_id' => $this->org->id, 'name' => 'Sales']);
        UserProfile::create(['user_id' => $this->employee->id, 'organization_id' => $this->org->id, 'department_id' => $design->id]);
        UserProfile::create(['user_id' => $this->otherEmployee->id, 'organization_id' => $this->org->id, 'department_id' => $sales->id]);

        $res = $this->actingAs($this->owner)->postJson($this->api('/projects'), [
            'name' => 'Launch',
            'member_ids' => [$this->employee->id, $this->otherEmployee->id],
        ])->assertCreated();

        $project = Project::findOrFail($res->json('project.id'));
        $this->assertSame(
            collect([$this->owner->id, $this->employee->id, $this->otherEmployee->id])->sort()->values()->all(),
            $this->memberIds($project)
        );
        // Being on the team is what lets an employee see it.
        $this->assertTrue($project->isVisibleTo($this->otherEmployee, $this->otherEmployee->memberships()->first()));
    }

    public function test_every_project_field_can_be_edited_after_creation(): void
    {
        $this->actingAs($this->owner)->patchJson($this->api("/projects/{$this->project->id}"), [
            'name' => 'Renamed',
            'code' => 'NEW-1',
            'description' => 'Now with a scope',
            'status' => 'on_hold',
            'priority' => 'high',
            'manager_id' => $this->employee->id,
            'start_date' => '2026-11-01',
            'due_date' => '2026-12-15',
            'planned_hours' => 320,
            'budget_amount' => 50000,
            'color' => '#3c6b4c',
        ])->assertOk();

        $p = $this->project->fresh();
        $this->assertSame('Renamed', $p->name);
        $this->assertSame('NEW-1', $p->code);
        $this->assertSame('on_hold', $p->status);
        $this->assertSame('high', $p->priority);
        $this->assertSame($this->employee->id, $p->manager_id);
        $this->assertSame('2026-12-15', $p->due_date->format('Y-m-d'));
        $this->assertEquals(320, (float) $p->planned_hours);
        $this->assertEquals(50000, (float) $p->budget_amount);
    }

    public function test_team_can_be_changed_any_time_keeping_owner_and_manager(): void
    {
        ProjectMember::create(['organization_id' => $this->org->id, 'project_id' => $this->project->id, 'user_id' => $this->employee->id, 'project_role' => 'contributor', 'cost_rate' => 40]);

        // Swap employee for otherEmployee; owner and manager are not in the list but stay.
        $this->actingAs($this->owner)->patchJson($this->api("/projects/{$this->project->id}"), [
            'member_ids' => [$this->otherEmployee->id],
        ])->assertOk();

        $this->assertSame(
            collect([$this->owner->id, $this->manager->id, $this->otherEmployee->id])->sort()->values()->all(),
            $this->memberIds($this->project)
        );

        // A member who stays keeps their project rate.
        ProjectMember::where('project_id', $this->project->id)->where('user_id', $this->otherEmployee->id)->update(['cost_rate' => 55]);
        $this->actingAs($this->owner)->patchJson($this->api("/projects/{$this->project->id}"), [
            'member_ids' => [$this->otherEmployee->id, $this->employee->id],
        ])->assertOk();
        $this->assertEquals(55, (float) ProjectMember::where('project_id', $this->project->id)->where('user_id', $this->otherEmployee->id)->value('cost_rate'));
        $this->assertContains($this->employee->id, $this->memberIds($this->project));
    }

    public function test_editing_without_member_ids_leaves_the_team_alone(): void
    {
        ProjectMember::create(['organization_id' => $this->org->id, 'project_id' => $this->project->id, 'user_id' => $this->employee->id, 'project_role' => 'contributor']);
        $before = $this->memberIds($this->project);

        $this->actingAs($this->owner)->patchJson($this->api("/projects/{$this->project->id}"), ['name' => 'Only the name'])->assertOk();

        $this->assertSame($before, $this->memberIds($this->project));
    }

    public function test_people_outside_the_organization_cannot_be_added(): void
    {
        $this->actingAs($this->owner)->patchJson($this->api("/projects/{$this->project->id}"), [
            'member_ids' => [$this->outsider->id],
        ])->assertUnprocessable()->assertJsonValidationErrors('member_ids.0');
    }

    public function test_budget_is_only_changed_by_those_who_see_financials(): void
    {
        $this->project->update(['budget_amount' => 1000]);

        // A manager edits projects but does not see financials.
        $this->actingAs($this->manager)->patchJson($this->api("/projects/{$this->project->id}"), [
            'name' => 'Manager edit',
            'budget_amount' => 999999,
        ])->assertOk();

        $this->assertSame('Manager edit', $this->project->fresh()->name);
        $this->assertEquals(1000, (float) $this->project->fresh()->budget_amount);
    }

    public function test_project_page_offers_editing_only_to_those_who_can_edit(): void
    {
        $this->project->update(['due_date' => '2026-12-15']);

        $page = $this->actingAs($this->owner)->get("/projects/{$this->project->id}")->assertOk();
        $page->assertSee('pm-edit-project-modal', false)->assertSee('pm-edit-team', false)->assertSee('15 Dec 2026');

        // An employee on the project sees it, without the edit button or modal.
        ProjectMember::create(['organization_id' => $this->org->id, 'project_id' => $this->project->id, 'user_id' => $this->employee->id, 'project_role' => 'contributor']);
        $this->actingAs($this->employee)->get("/projects/{$this->project->id}")->assertOk()->assertDontSee('pm-edit-project-modal', false);
    }

    public function test_new_project_form_offers_the_team_picker(): void
    {
        $this->actingAs($this->owner)->get('/dashboard')->assertOk()->assertSee('new-project-team', false);
    }

    public function test_employees_cannot_edit_projects(): void
    {
        $this->actingAs($this->employee)->patchJson($this->api("/projects/{$this->project->id}"), ['name' => 'Nope'])->assertForbidden();
        $this->assertSame('Main Project', $this->project->fresh()->name);
    }
}
