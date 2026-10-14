<?php

namespace Tests\Feature\Projects;

use App\Domains\Notifications\Models\WorkplaceNotification;
use App\Domains\Projects\Actions\UpdateTaskStatusAction;
use App\Domains\Projects\Models\ProjectAutomation;
use App\Domains\Projects\Models\ProjectLabel;
use App\Domains\Projects\Models\ProjectState;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\Feature\Projects\Concerns\BuildsProjectWorkspace;
use Tests\TestCase;

class AutomationTest extends TestCase
{
    use BuildsProjectWorkspace, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->buildProjectWorkspace();
        ProjectState::ensureDefaults($this->project);
    }

    private function rule(string $trigger, array $actions, array $conditions = []): ProjectAutomation
    {
        return $this->project->automations()->create([
            'organization_id' => $this->org->id, 'name' => 'Rule', 'trigger' => $trigger,
            'conditions' => $conditions, 'actions' => $actions,
        ]);
    }

    private function state(string $group): ProjectState
    {
        return $this->project->states()->where('group', $group)->first();
    }

    public function test_new_tasks_get_a_label_and_a_due_date(): void
    {
        $label = ProjectLabel::create(['organization_id' => $this->org->id, 'project_id' => $this->project->id, 'name' => 'Triage', 'tone' => 'gold']);
        $this->rule('task_created', [['type' => 'add_label', 'value' => $label->id], ['type' => 'set_due_in_days', 'value' => 3]]);

        $task = $this->makeTask(['title' => 'Fresh']);

        $this->assertTrue($task->labels()->whereKey($label->id)->exists());
        $this->assertSame(now()->addDays(3)->toDateString(), $task->fresh()->due_date->toDateString());
        $this->assertSame(1, ProjectAutomation::first()->run_count);
    }

    public function test_conditions_must_all_hold(): void
    {
        $this->rule('task_created', [['type' => 'set_priority', 'value' => 'urgent']], ['priority' => 'high']);

        $this->assertSame('urgent', $this->makeTask(['priority' => 'high'])->fresh()->priority);
        $this->assertSame('low', $this->makeTask(['priority' => 'low'])->fresh()->priority);
    }

    public function test_moving_to_a_state_assigns_a_reviewer_and_notifies(): void
    {
        $review = $this->state('review');
        $rule = $this->rule('state_changed', [['type' => 'add_assignee', 'value' => $this->manager->id], ['type' => 'notify', 'value' => 'reporter']], ['state_id' => $review->id]);

        $task = $this->makeTask(['reporter_id' => $this->owner->id]);
        $this->actingAs($this->owner);
        app(UpdateTaskStatusAction::class)->execute($task->fresh(), 'review', $review->id);

        $this->assertTrue($task->fresh()->isAssignedTo($this->manager->id));
        $this->assertTrue(WorkplaceNotification::where('user_id', $this->owner->id)->where('data->automation_id', $rule->id)->exists());
    }

    public function test_rules_do_not_trigger_each_other(): void
    {
        $this->rule('task_created', [['type' => 'set_priority', 'value' => 'high']]);
        $second = $this->rule('priority_changed', [['type' => 'set_priority', 'value' => 'low']]);

        $this->assertSame('high', $this->makeTask(['priority' => 'medium'])->fresh()->priority);
        $this->assertSame(0, $second->fresh()->run_count);
    }

    public function test_label_added_matches_the_new_label_only(): void
    {
        [$bug, $ui] = collect(['Bug', 'UI'])->map(fn ($n) => ProjectLabel::create(['organization_id' => $this->org->id, 'project_id' => $this->project->id, 'name' => $n, 'tone' => 'stone']))->all();
        $this->rule('label_added', [['type' => 'set_priority', 'value' => 'urgent']], ['label_id' => $bug->id]);
        $task = $this->makeTask(['priority' => 'low']);

        Sanctum::actingAs($this->manager);
        $this->putJson($this->api("/tasks/{$task->id}/labels"), ['label_ids' => [$ui->id]])->assertOk();
        $this->assertSame('low', $task->fresh()->priority);

        $this->putJson($this->api("/tasks/{$task->id}/labels"), ['label_ids' => [$ui->id, $bug->id]])->assertOk();
        $this->assertSame('urgent', $task->fresh()->priority);
    }

    public function test_paused_rules_do_nothing(): void
    {
        $rule = $this->rule('task_created', [['type' => 'set_priority', 'value' => 'urgent']]);
        Sanctum::actingAs($this->manager);
        $this->putJson($this->api("/projects/{$this->project->id}/automations/{$rule->id}"), ['is_active' => false])->assertOk();

        $this->assertSame('low', $this->makeTask(['priority' => 'low'])->fresh()->priority);
    }

    public function test_api_validates_ids_against_the_project(): void
    {
        Sanctum::actingAs($this->manager);
        $url = $this->api("/projects/{$this->project->id}/automations");

        $this->postJson($url, ['name' => 'x', 'trigger' => 'task_created', 'actions' => [['type' => 'add_assignee', 'value' => $this->outsider->id]]])
            ->assertUnprocessable()->assertJsonValidationErrors('actions.0.value');
        $this->postJson($url, ['name' => 'x', 'trigger' => 'nope', 'actions' => [['type' => 'set_priority', 'value' => 'high']]])
            ->assertUnprocessable()->assertJsonValidationErrors('trigger');
        $this->postJson($url, ['name' => 'Urgent bugs', 'trigger' => 'task_created', 'conditions' => ['priority' => 'urgent'], 'actions' => [['type' => 'notify', 'value' => 'manager']]])
            ->assertCreated()->assertJsonPath('automation.conditions.priority', 'urgent');
    }

    public function test_employees_cannot_manage_rules(): void
    {
        Sanctum::actingAs($this->employee);
        $this->getJson($this->api("/projects/{$this->project->id}/automations"))->assertOk();
        $this->postJson($this->api("/projects/{$this->project->id}/automations"), ['name' => 'x', 'trigger' => 'task_created', 'actions' => [['type' => 'set_priority', 'value' => 'high']]])
            ->assertForbidden();
    }
}
