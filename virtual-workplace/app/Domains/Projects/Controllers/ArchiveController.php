<?php

namespace App\Domains\Projects\Controllers;

use App\Domains\Projects\Models\Project;
use App\Domains\Projects\Models\Task;
use App\Domains\Tenancy\Models\Organization;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Gate;

/**
 * Archived tasks and projects: list, restore, delete for good. Deleting from
 * the normal endpoints only archives; permanent removal happens here and only
 * for something already archived.
 */
class ArchiveController extends Controller
{
    public function tasks(Request $request, Organization $organization): JsonResponse
    {
        $query = Task::onlyTrashed()
            ->where('organization_id', $organization->id)
            ->with(['project:id,name,code', 'assignee:id,name'])
            ->latest('archived_at');

        if ($request->filled('project_id')) {
            $query->where('project_id', $request->query('project_id'));
        }

        return response()->json($query->paginate($request->integer('per_page', 50)));
    }

    public function restoreTask(Organization $organization, string $task): JsonResponse
    {
        $model = $this->archivedTask($organization, $task);
        Gate::authorize('delete', $model);

        if ($model->parent_task_id && Task::onlyTrashed()->whereKey($model->parent_task_id)->exists()) {
            return response()->json(['message' => 'Restore the parent task first.'], 422);
        }

        $model->restore();

        return response()->json(['message' => 'Task restored.', 'task' => $model->fresh()]);
    }

    public function forceDeleteTask(Organization $organization, string $task): JsonResponse
    {
        $model = $this->archivedTask($organization, $task);
        Gate::authorize('delete', $model);

        $model->forceDelete();

        return response()->json(['message' => 'Task permanently deleted.']);
    }

    public function projects(Request $request, Organization $organization): JsonResponse
    {
        return response()->json(
            Project::onlyTrashed()
                ->where('organization_id', $organization->id)
                ->withCount('tasks')
                ->latest('archived_at')
                ->paginate($request->integer('per_page', 50))
        );
    }

    public function restoreProject(Organization $organization, string $project): JsonResponse
    {
        $model = Project::onlyTrashed()->where('organization_id', $organization->id)->findOrFail($project);
        $model->restore();

        return response()->json(['message' => 'Project restored.', 'project' => $model->fresh()]);
    }

    public function forceDeleteProject(Organization $organization, string $project): JsonResponse
    {
        $model = Project::onlyTrashed()->where('organization_id', $organization->id)->findOrFail($project);
        // Tasks, time entries, files… go with it through the foreign keys.
        $model->forceDelete();

        return response()->json(['message' => 'Project permanently deleted.']);
    }

    private function archivedTask(Organization $organization, string $id): Task
    {
        return Task::onlyTrashed()->where('organization_id', $organization->id)->findOrFail($id);
    }
}
