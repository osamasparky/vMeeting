<?php

namespace App\Domains\Projects\Controllers;

use App\Domains\Projects\Models\Project;
use App\Domains\Projects\Models\ProjectAutomation;
use App\Domains\Projects\Models\Task;
use App\Domains\Tenancy\Models\Organization;
use App\Domains\Tenancy\Models\OrganizationMember;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

/** A project's automation rules: list, add, edit / pause, delete. */
class AutomationController extends Controller
{
    public function index(Organization $organization, Project $project): JsonResponse
    {
        abort_unless($project->organization_id === $organization->id, 404);

        return response()->json(['automations' => $project->automations()->get()]);
    }

    public function store(Request $request, Organization $organization, Project $project): JsonResponse
    {
        abort_unless($project->organization_id === $organization->id, 404);

        $automation = $project->automations()->create([
            ...$this->validated($request, $project),
            'organization_id' => $organization->id,
            'created_by' => Auth::id(),
        ]);

        return response()->json(['message' => 'Automation created.', 'automation' => $automation], 201);
    }

    public function update(Request $request, Organization $organization, Project $project, ProjectAutomation $automation): JsonResponse
    {
        abort_unless($project->organization_id === $organization->id && $automation->project_id === $project->id, 404);

        // Pausing / resuming alone needs no full rule.
        if (! $request->has('trigger')) {
            $automation->update($request->validate(['is_active' => ['required', 'boolean']]));
        } else {
            $automation->update($this->validated($request, $project));
        }

        return response()->json(['message' => 'Automation updated.', 'automation' => $automation->fresh()]);
    }

    public function destroy(Organization $organization, Project $project, ProjectAutomation $automation): JsonResponse
    {
        abort_unless($project->organization_id === $organization->id && $automation->project_id === $project->id, 404);

        $automation->delete();

        return response()->json(['message' => 'Automation deleted.']);
    }

    /** Validate a whole rule; every id it mentions must belong to this project / organization. */
    private function validated(Request $request, Project $project): array
    {
        $states = $project->states()->pluck('id')->all();
        $labels = $project->labels()->pluck('id')->all();
        $members = OrganizationMember::where('organization_id', $project->organization_id)->pluck('user_id')->all();

        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'trigger' => ['required', Rule::in(ProjectAutomation::TRIGGERS)],
            'conditions' => ['nullable', 'array'],
            'conditions.state_id' => ['nullable', 'integer', Rule::in($states)],
            'conditions.priority' => ['nullable', Rule::in(Task::PRIORITIES)],
            'conditions.label_id' => ['nullable', 'integer', Rule::in($labels)],
            'conditions.assignee' => ['nullable', 'string', Rule::in(['none', 'any', ...$members])],
            'actions' => ['required', 'array', 'min:1', 'max:5'],
            'actions.*.type' => ['required', Rule::in(ProjectAutomation::ACTIONS)],
            'actions.*.value' => ['required'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        foreach ($data['actions'] as $i => $action) {
            $value = $action['value'];
            $ok = match ($action['type']) {
                'set_state' => in_array((int) $value, $states, true),
                'set_priority' => in_array($value, Task::PRIORITIES, true),
                'add_label' => in_array((int) $value, $labels, true),
                'add_assignee', 'add_watcher' => in_array($value, $members, true),
                'notify' => in_array($value, ['assignees', 'reporter', 'watchers', 'manager', ...$members], true),
                'set_due_in_days' => is_numeric($value) && (int) $value >= 0 && (int) $value <= 365,
            };
            if (! $ok) {
                throw ValidationException::withMessages(["actions.$i.value" => __('pm.automation_invalid_value')]);
            }
            $data['actions'][$i] = ['type' => $action['type'], 'value' => in_array($action['type'], ['set_state', 'add_label', 'set_due_in_days'], true) ? (int) $value : $value];
        }

        $data['conditions'] = array_filter($data['conditions'] ?? [], fn ($v) => $v !== null && $v !== '');
        $data['conditions'] = array_intersect_key($data['conditions'], array_flip(ProjectAutomation::CONDITIONS));

        return $data;
    }
}
