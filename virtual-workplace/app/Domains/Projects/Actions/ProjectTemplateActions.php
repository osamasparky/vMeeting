<?php

namespace App\Domains\Projects\Actions;

use App\Domains\Identity\Models\User;
use App\Domains\Projects\Models\Project;
use App\Domains\Projects\Models\ProjectState;
use App\Domains\Projects\Models\ProjectTemplate;
use App\Domains\Projects\Models\Task;
use App\Domains\Tenancy\Models\Organization;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Save a project as a template, and build a new project from one. Dates are
 * stored as day offsets from the project's start so a template can be reused
 * at any time; task status is not copied (everything starts fresh).
 */
class ProjectTemplateActions
{
    public function __construct(private readonly CreateProjectAction $createProject) {}

    public function snapshot(Project $project, string $name, ?string $description, User $creator): ProjectTemplate
    {
        $start = ($project->start_date ?? $project->created_at)->copy()->startOfDay();
        $offset = fn ($date) => $date ? (int) $start->diffInDays(Carbon::parse($date)->startOfDay(), false) : null;

        $project->load(['states', 'labels', 'customFieldDefinitions', 'milestones', 'rootTasks.checklistItems', 'rootTasks.labels']);

        $payload = [
            'description' => $project->description,
            'priority' => $project->priority,
            'planned_hours' => $project->planned_hours,
            'duration_days' => $project->due_date ? $offset($project->due_date) : null,
            'states' => $project->states->map(fn ($s) => ['name' => $s->name, 'group' => $s->group, 'is_default' => $s->is_default])->values()->all(),
            'labels' => $project->labels->map(fn ($l) => ['name' => $l->name, 'tone' => $l->tone])->values()->all(),
            'custom_fields' => $project->customFieldDefinitions->map(fn ($f) => [
                'name' => $f->name, 'field_type' => $f->field_type, 'options' => $f->options, 'is_required' => $f->is_required,
            ])->values()->all(),
            'milestones' => $project->milestones->map(fn ($m) => ['name' => $m->name, 'due_offset' => $offset($m->due_date)])->values()->all(),
            'tasks' => $project->rootTasks->map(fn (Task $t) => [
                'title' => $t->title,
                'description' => $t->description,
                'priority' => $t->priority,
                'task_type' => $t->task_type,
                'estimated_hours' => (float) $t->estimated_hours,
                'start_offset' => $offset($t->start_date),
                'due_offset' => $offset($t->due_date),
                'milestone' => $t->milestone?->name,
                'labels' => $t->labels->pluck('name')->all(),
                'checklist' => $t->checklistItems->pluck('title')->all(),
            ])->values()->all(),
        ];

        return ProjectTemplate::create([
            'organization_id' => $project->organization_id,
            'name' => $name,
            'description' => $description,
            'payload' => $payload,
            'created_by' => $creator->id,
        ]);
    }

    /** @param  array<string, mixed>  $data  the normal create-project fields (name, code, dates…) */
    public function instantiate(ProjectTemplate $template, array $data, Organization $organization, User $creator): Project
    {
        $p = $template->payload;

        return DB::transaction(function () use ($p, $data, $organization, $creator) {
            $start = Carbon::parse($data['start_date'] ?? now())->startOfDay();
            $at = fn (?int $days) => $days === null ? null : $start->copy()->addDays($days)->toDateString();

            $project = $this->createProject->execute(array_merge([
                'description' => $p['description'] ?? null,
                'priority' => $p['priority'] ?? 'medium',
                'planned_hours' => $p['planned_hours'] ?? null,
                'start_date' => $start->toDateString(),
                'due_date' => $at($p['duration_days'] ?? null),
            ], array_filter($data, fn ($v) => $v !== null && $v !== '')), $organization, $creator);

            if (! empty($p['states'])) {
                $project->states()->delete();
                foreach ($p['states'] as $i => $s) {
                    $project->states()->create([
                        'organization_id' => $organization->id,
                        'name' => $s['name'], 'group' => $s['group'], 'position' => $i, 'is_default' => (bool) ($s['is_default'] ?? false),
                    ]);
                }
            }

            $labelIds = [];
            foreach ($p['labels'] ?? [] as $l) {
                $labelIds[$l['name']] = $project->labels()->create([
                    'organization_id' => $organization->id, 'name' => $l['name'], 'tone' => $l['tone'] ?? 'stone',
                ])->id;
            }

            foreach ($p['custom_fields'] ?? [] as $i => $f) {
                $project->customFieldDefinitions()->create([
                    'organization_id' => $organization->id,
                    'name' => $f['name'], 'field_type' => $f['field_type'], 'options' => $f['options'] ?? null,
                    'is_required' => (bool) ($f['is_required'] ?? false), 'sort_order' => $i + 1,
                ]);
            }

            $milestoneIds = [];
            foreach ($p['milestones'] ?? [] as $m) {
                $milestoneIds[$m['name']] = $project->milestones()->create([
                    'organization_id' => $organization->id, 'name' => $m['name'], 'due_date' => $at($m['due_offset'] ?? null),
                ])->id;
            }

            $defaultState = ProjectState::where('project_id', $project->id)->where('is_default', true)->first()
                ?? ProjectState::where('project_id', $project->id)->orderBy('position')->first();
            foreach ($p['tasks'] ?? [] as $n => $t) {
                $task = Task::create([
                    'organization_id' => $organization->id,
                    'project_id' => $project->id,
                    'title' => $t['title'],
                    'description' => $t['description'] ?? null,
                    'priority' => $t['priority'] ?? 'medium',
                    'task_type' => $t['task_type'] ?? 'task',
                    'estimated_hours' => $t['estimated_hours'] ?? 0,
                    'start_date' => $at($t['start_offset'] ?? null),
                    'due_date' => $at($t['due_offset'] ?? null),
                    'milestone_id' => $milestoneIds[$t['milestone'] ?? ''] ?? null,
                    'status' => $defaultState?->group ?? Task::STATUS_BACKLOG,
                    'state_id' => $defaultState?->id,
                    'task_number' => $n + 1,
                    'order' => $n + 1,
                    'reporter_id' => $creator->id,
                ]);
                $task->labels()->sync(array_values(array_intersect_key($labelIds, array_flip($t['labels'] ?? []))));
                foreach (array_values($t['checklist'] ?? []) as $i => $title) {
                    $task->checklistItems()->create(['organization_id' => $organization->id, 'title' => $title, 'order' => $i + 1]);
                }
            }

            return $project->fresh(['owner', 'manager', 'department', 'members.user']);
        });
    }
}
