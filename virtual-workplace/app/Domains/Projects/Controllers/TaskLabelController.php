<?php

namespace App\Domains\Projects\Controllers;

use App\Domains\Projects\Actions\SetTaskAssigneesAction;
use App\Domains\Projects\Models\Project;
use App\Domains\Projects\Models\ProjectLabel;
use App\Domains\Projects\Models\Task;
use App\Domains\Tenancy\Models\Organization;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

/**
 * Project labels, and the label / assignee lists on a task.
 */
class TaskLabelController extends Controller
{
    public function index(Organization $organization, Project $project): JsonResponse
    {
        abort_unless($project->organization_id === $organization->id, 404);

        return response()->json([
            'labels' => $project->labels()->withCount('tasks')->get(),
        ]);
    }

    public function store(Request $request, Organization $organization, Project $project): JsonResponse
    {
        abort_unless($project->organization_id === $organization->id, 404);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:50', Rule::unique('project_labels')->where('project_id', $project->id)],
            'tone' => ['nullable', 'string', Rule::in(ProjectLabel::TONES)],
        ]);

        $label = $project->labels()->create([
            'organization_id' => $organization->id,
            'name' => trim($validated['name']),
            'tone' => $validated['tone'] ?? 'stone',
        ]);

        return response()->json(['message' => 'Label created.', 'label' => $label], 201);
    }

    public function update(Request $request, Organization $organization, Project $project, ProjectLabel $label): JsonResponse
    {
        abort_unless($project->organization_id === $organization->id && $label->project_id === $project->id, 404);

        $validated = $request->validate([
            'name' => ['sometimes', 'required', 'string', 'max:50', Rule::unique('project_labels')->where('project_id', $project->id)->ignore($label->id)],
            'tone' => ['sometimes', 'string', Rule::in(ProjectLabel::TONES)],
        ]);

        $label->update($validated);

        return response()->json(['message' => 'Label updated.', 'label' => $label]);
    }

    public function destroy(Organization $organization, Project $project, ProjectLabel $label): JsonResponse
    {
        abort_unless($project->organization_id === $organization->id && $label->project_id === $project->id, 404);

        $label->delete();

        return response()->json(['message' => 'Label deleted.']);
    }

    public function setTaskLabels(Request $request, Organization $organization, Task $task): JsonResponse
    {
        abort_unless($task->organization_id === $organization->id, 404);
        Gate::authorize('update', $task);

        $validated = $request->validate([
            'label_ids' => ['present', 'array', 'max:20'],
            'label_ids.*' => ['integer', Rule::exists('project_labels', 'id')->where('project_id', $task->project_id)],
        ]);

        $task->labels()->sync($validated['label_ids']);

        return response()->json(['message' => 'Labels updated.', 'labels' => $task->labels()->get()]);
    }

    public function setTaskAssignees(Request $request, Organization $organization, Task $task, SetTaskAssigneesAction $action): JsonResponse
    {
        abort_unless($task->organization_id === $organization->id, 404);

        $validated = $request->validate([
            'user_ids' => ['present', 'array', 'max:20'],
            'user_ids.*' => ['string', Rule::exists('organization_members', 'user_id')->where('organization_id', $organization->id)],
        ]);

        $task = $action->execute($task, $validated['user_ids'], Auth::user());

        return response()->json(['message' => 'Assignees updated.', 'task' => $task]);
    }
}
