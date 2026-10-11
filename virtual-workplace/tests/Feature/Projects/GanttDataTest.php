<?php

namespace Tests\Feature\Projects;

use App\Domains\Projects\Models\TaskChecklistItem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Projects\Concerns\BuildsProjectWorkspace;
use Tests\TestCase;

class GanttDataTest extends TestCase
{
    use BuildsProjectWorkspace, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->buildProjectWorkspace();
    }

    public function test_the_chart_shows_real_dates_only(): void
    {
        $this->task->update(['start_date' => null, 'due_date' => null]);                      // no dates
        $dueOnly = $this->makeTask(['due_date' => '2026-03-10', 'start_date' => null]);
        $far = $this->makeTask(['start_date' => '2030-01-01', 'due_date' => '2030-02-01']);   // far ahead: kept
        $checked = $this->makeTask(['start_date' => '2026-03-01', 'due_date' => '2026-03-05', 'status' => 'in_progress']);
        foreach ([true, true, false, false] as $i => $done) {
            TaskChecklistItem::create(['organization_id' => $this->org->id, 'task_id' => $checked->id, 'title' => "Step {$i}", 'is_completed' => $done, 'order' => $i]);
        }

        $view = $this->actingAs($this->owner)->get("/projects/{$this->project->id}")->assertOk();
        $gantt = collect($view->viewData('ganttTasks'))->keyBy('id');

        $this->assertFalse($gantt->has((string) $this->task->id), 'a task without dates is not placed on the chart');
        $this->assertSame(1, $view->viewData('ganttUnscheduled'));
        $this->assertSame(['2026-03-10', '2026-03-10'], [$gantt[$dueOnly->id]['start_date'], $gantt[$dueOnly->id]['due_date']]);
        $this->assertFalse($gantt[$dueOnly->id]['has_start']);
        $this->assertSame(['2030-01-01', '2030-02-01'], [$gantt[$far->id]['start_date'], $gantt[$far->id]['due_date']]);
        $this->assertSame(50, $gantt[$checked->id]['progress'], 'progress is the ticked share of the checklist');
        $view->assertSee(__('gantt.unscheduled', ['count' => 1]));
    }
}
