<?php

namespace App\Domains\Projects\Controllers;

use App\Domains\Projects\Actions\BuildSprintBurndownAction;
use App\Domains\Projects\Actions\CompleteSprintAction;
use App\Domains\Projects\Models\Project;
use App\Domains\Projects\Models\ProjectSprint;
use App\Domains\Tenancy\Models\Organization;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Validation\Rule;
use InvalidArgumentException;

/**
 * Sprint lifecycle beyond list/create (which live on ProjectController):
 * details, edit, start, complete with carry-over, burndown, delete.
 */
class SprintController extends Controller
{
    public function show(Organization $organization, Project $project, ProjectSprint $sprint, BuildSprintBurndownAction $burndown): JsonResponse
    {
        $this->guard($organization, $project, $sprint);

        $sprint->load(['tasks' => fn ($q) => $q->with(['assignee:id,name', 'labels'])->orderBy('order')]);

        return response()->json([
            'sprint' => $sprint,
            'burndown' => $burndown->execute($sprint),
        ]);
    }

    public function update(Request $request, Organization $organization, Project $project, ProjectSprint $sprint): JsonResponse
    {
        $this->guard($organization, $project, $sprint);

        $validated = $request->validate([
            'name' => ['sometimes', 'required', 'string', 'max:100'],
            'goal' => ['sometimes', 'nullable', 'string', 'max:255'],
            'start_date' => ['sometimes', 'required', 'date'],
            'end_date' => ['sometimes', 'required', 'date', 'after:start_date'],
            'retrospective_notes' => ['sometimes', 'nullable', 'string', 'max:5000'],
        ]);

        $sprint->update($validated);

        return response()->json(['message' => 'Sprint updated.', 'sprint' => $sprint]);
    }

    public function start(Organization $organization, Project $project, ProjectSprint $sprint): JsonResponse
    {
        $this->guard($organization, $project, $sprint);

        if ($sprint->status !== ProjectSprint::STATUS_PLANNED) {
            return response()->json(['message' => 'Only a planned sprint can be started.'], 422);
        }
        if ($project->sprints()->where('status', ProjectSprint::STATUS_ACTIVE)->exists()) {
            return response()->json(['message' => 'Another sprint is already active. Complete it first.'], 422);
        }

        $tasks = $sprint->tasks()->get(['story_points']);
        $sprint->update([
            'status' => ProjectSprint::STATUS_ACTIVE,
            'started_at' => now(),
            'planned_points' => (int) $tasks->sum('story_points'),
        ]);

        return response()->json(['message' => 'Sprint started.', 'sprint' => $sprint]);
    }

    public function complete(Request $request, Organization $organization, Project $project, ProjectSprint $sprint, CompleteSprintAction $action): JsonResponse
    {
        $this->guard($organization, $project, $sprint);

        $validated = $request->validate([
            'carry_to_sprint_id' => ['nullable', 'integer', Rule::exists('project_sprints', 'id')->where('project_id', $project->id)],
        ]);
        $carryTo = isset($validated['carry_to_sprint_id']) ? ProjectSprint::find($validated['carry_to_sprint_id']) : null;

        try {
            $result = $action->execute($sprint, $carryTo);
        } catch (InvalidArgumentException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        return response()->json([
            'message' => 'Sprint completed.',
            'sprint' => $result['sprint'],
            'carried_over' => $result['carried'],
        ]);
    }

    public function destroy(Organization $organization, Project $project, ProjectSprint $sprint): JsonResponse
    {
        $this->guard($organization, $project, $sprint);

        // Tasks return to the backlog (the FK is nullOnDelete, done explicitly for clarity).
        $sprint->tasks()->update(['sprint_id' => null]);
        $sprint->delete();

        return response()->json(['message' => 'Sprint deleted.']);
    }

    private function guard(Organization $organization, Project $project, ProjectSprint $sprint): void
    {
        abort_unless($project->organization_id === $organization->id && $sprint->project_id === $project->id, 404);
    }
}
