<?php

namespace App\Domains\Projects\Controllers;

use App\Domains\Projects\Models\Project;
use App\Domains\Projects\Models\ProjectState;
use App\Domains\Projects\Models\Task;
use App\Domains\Tenancy\Models\Organization;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

/** A project's workflow states: list, add, rename / regroup, reorder, default, delete. */
class ProjectStateController extends Controller
{
    public function index(Organization $organization, Project $project): JsonResponse
    {
        $this->guard($organization, $project);
        ProjectState::ensureDefaults($project);

        return response()->json(['states' => $project->states()->withCount('tasks')->get()]);
    }

    public function store(Request $request, Organization $organization, Project $project): JsonResponse
    {
        $this->guard($organization, $project);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:60', $this->uniqueName($project)],
            'group' => ['required', Rule::in(Task::STATUSES)],
        ]);

        // New states go to the end of their group.
        $after = $project->states()->where('group', $validated['group'])->max('position')
            ?? $project->states()->max('position') ?? -1;
        $project->states()->where('position', '>', $after)->increment('position');

        $state = $project->states()->create([
            'organization_id' => $organization->id,
            'name' => trim($validated['name']),
            'group' => $validated['group'],
            'position' => $after + 1,
        ]);

        return response()->json(['message' => 'State added.', 'state' => $state], 201);
    }

    public function update(Request $request, Organization $organization, Project $project, ProjectState $state): JsonResponse
    {
        $this->guard($organization, $project, $state);

        $validated = $request->validate([
            'name' => ['sometimes', 'required', 'string', 'max:60', $this->uniqueName($project, $state->id)],
            'is_default' => ['sometimes', 'accepted'],
            'move' => ['sometimes', Rule::in(['up', 'down'])],
        ]);

        DB::transaction(function () use ($validated, $project, $state) {
            if (isset($validated['name'])) {
                $state->update(['name' => trim($validated['name'])]);
            }
            if (! empty($validated['is_default'])) {
                $project->states()->update(['is_default' => false]);
                $state->update(['is_default' => true]);
            }
            if (isset($validated['move'])) {
                $neighbour = $project->states()
                    ->where('position', $validated['move'] === 'up' ? '<' : '>', $state->position)
                    ->orderBy('position', $validated['move'] === 'up' ? 'desc' : 'asc')
                    ->first();
                if ($neighbour) {
                    [$a, $b] = [$state->position, $neighbour->position];
                    $state->update(['position' => $b]);
                    $neighbour->update(['position' => $a]);
                }
            }
        });

        return response()->json(['message' => 'State updated.', 'states' => $project->states()->withCount('tasks')->get()]);
    }

    public function destroy(Organization $organization, Project $project, ProjectState $state): JsonResponse
    {
        $this->guard($organization, $project, $state);

        // Tasks need somewhere of the same group to go.
        $fallback = $project->states()->where('group', $state->group)->whereKeyNot($state->id)->orderBy('position')->first();
        if (! $fallback) {
            return response()->json(['message' => __('pm.state_last_in_group')], 422);
        }

        DB::transaction(function () use ($project, $state, $fallback) {
            Task::withTrashed()->where('state_id', $state->id)->update(['state_id' => $fallback->id]);
            if ($state->is_default) {
                $fallback->update(['is_default' => true]);
            }
            $state->delete();
            $project->states()->orderBy('position')->get()->values()->each(fn ($s, $i) => $s->update(['position' => $i]));
        });

        return response()->json(['message' => 'State deleted.']);
    }

    private function uniqueName(Project $project, ?int $ignoreId = null)
    {
        return Rule::unique('project_states', 'name')->where('project_id', $project->id)->ignore($ignoreId);
    }

    private function guard(Organization $organization, Project $project, ?ProjectState $state = null): void
    {
        abort_unless($project->organization_id === $organization->id && (! $state || $state->project_id === $project->id), 404);
    }
}
