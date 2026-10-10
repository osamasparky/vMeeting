<?php

namespace App\Domains\Projects\Controllers;

use App\Domains\Projects\Actions\ProjectTemplateActions;
use App\Domains\Projects\Models\Project;
use App\Domains\Projects\Models\ProjectTemplate;
use App\Domains\Projects\Models\Task;
use App\Domains\Projects\Models\TaskTemplate;
use App\Domains\Tenancy\Models\Organization;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

/** Project templates (organization-wide) and task templates (per project or organization). */
class TemplateController extends Controller
{
    public function projectTemplates(Organization $organization): JsonResponse
    {
        return response()->json([
            'templates' => ProjectTemplate::where('organization_id', $organization->id)->latest()->get(['id', 'name', 'description', 'created_at']),
        ]);
    }

    public function saveProjectAsTemplate(Request $request, Organization $organization, Project $project, ProjectTemplateActions $actions): JsonResponse
    {
        abort_unless($project->organization_id === $organization->id, 404);
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'description' => ['nullable', 'string', 'max:1000'],
        ]);

        $template = $actions->snapshot($project, $validated['name'], $validated['description'] ?? null, Auth::user());

        return response()->json(['message' => 'Template saved.', 'template' => $template->only(['id', 'name', 'description'])], 201);
    }

    public function destroyProjectTemplate(Organization $organization, ProjectTemplate $template): JsonResponse
    {
        abort_unless($template->organization_id === $organization->id, 404);
        $template->delete();

        return response()->json(['message' => 'Template deleted.']);
    }

    public function taskTemplates(Organization $organization, Project $project): JsonResponse
    {
        abort_unless($project->organization_id === $organization->id, 404);

        return response()->json(['templates' => TaskTemplate::availableFor($project)->orderBy('name')->get()]);
    }

    /** Save an existing task's shape (fields, checklist, labels) as a template. */
    public function saveTaskAsTemplate(Request $request, Organization $organization, Task $task): JsonResponse
    {
        abort_unless($task->organization_id === $organization->id, 404);
        Gate::authorize('view', $task);
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'organization_wide' => ['sometimes', 'boolean'],
        ]);

        $task->load(['checklistItems', 'labels']);
        $template = TaskTemplate::create([
            'organization_id' => $organization->id,
            'project_id' => ($validated['organization_wide'] ?? false) ? null : $task->project_id,
            'name' => $validated['name'],
            'created_by' => Auth::id(),
            'payload' => [
                'title' => $task->title,
                'description' => $task->description,
                'priority' => $task->priority,
                'task_type' => $task->task_type,
                'estimated_hours' => (float) $task->estimated_hours,
                'story_points' => $task->story_points,
                'checklist' => $task->checklistItems->pluck('title')->all(),
                'labels' => $task->labels->pluck('name')->all(),
            ],
        ]);

        return response()->json(['message' => 'Template saved.', 'template' => $template], 201);
    }

    public function destroyTaskTemplate(Organization $organization, TaskTemplate $template): JsonResponse
    {
        abort_unless($template->organization_id === $organization->id, 404);
        $template->delete();

        return response()->json(['message' => 'Template deleted.']);
    }
}
