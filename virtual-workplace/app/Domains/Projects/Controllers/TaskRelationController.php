<?php

namespace App\Domains\Projects\Controllers;

use App\Domains\Projects\Models\Task;
use App\Domains\Projects\Models\TaskRelation;
use App\Domains\Tenancy\Models\Organization;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

/** "Relates to" and "duplicate of" links between tasks of one organization. */
class TaskRelationController extends Controller
{
    public function index(Organization $organization, Task $task): JsonResponse
    {
        abort_unless($task->organization_id === $organization->id, 404);

        return response()->json(['relations' => self::present($task)]);
    }

    public function store(Request $request, Organization $organization, Task $task): JsonResponse
    {
        abort_unless($task->organization_id === $organization->id, 404);
        Gate::authorize('update', $task);

        $validated = $request->validate([
            'related_task_id' => ['required', 'string', Rule::exists('tasks', 'id')->where('organization_id', $organization->id)->whereNull('archived_at')],
            'type' => ['required', Rule::in(TaskRelation::TYPES)],
        ]);
        if ($validated['related_task_id'] === $task->id) {
            return response()->json(['message' => 'A task cannot be related to itself.'], 422);
        }

        // "relates_to" is symmetric: one row is enough in either direction.
        $exists = TaskRelation::where('type', $validated['type'])
            ->where(fn ($q) => $q->where(['task_id' => $task->id, 'related_task_id' => $validated['related_task_id']])
                ->when($validated['type'] === 'relates_to', fn ($q2) => $q2->orWhere(['task_id' => $validated['related_task_id'], 'related_task_id' => $task->id])))
            ->exists();

        if (! $exists) {
            TaskRelation::create([
                'organization_id' => $organization->id,
                'task_id' => $task->id,
                'related_task_id' => $validated['related_task_id'],
                'type' => $validated['type'],
                'created_by' => Auth::id(),
            ]);
        }

        return response()->json(['message' => 'Relation added.', 'relations' => self::present($task)], 201);
    }

    public function destroy(Organization $organization, Task $task, TaskRelation $relation): JsonResponse
    {
        abort_unless($task->organization_id === $organization->id, 404);
        abort_unless($relation->task_id === $task->id || $relation->related_task_id === $task->id, 404);
        Gate::authorize('update', $task);

        $relation->delete();

        return response()->json(['message' => 'Relation removed.', 'relations' => self::present($task)]);
    }

    /**
     * Both directions, phrased from this task's point of view.
     *
     * @return list<array{id: int, kind: string, task: array}>
     */
    public static function present(Task $task): array
    {
        $out = [];
        $columns = ['id', 'title', 'task_number', 'status', 'project_id'];

        foreach ($task->relations()->with(['relatedTask' => fn ($q) => $q->select($columns)])->get() as $r) {
            if ($r->relatedTask) {
                $out[] = ['id' => $r->id, 'kind' => $r->type, 'task' => $r->relatedTask];
            }
        }
        foreach ($task->inverseRelations()->with(['task' => fn ($q) => $q->select($columns)])->get() as $r) {
            if ($r->task) {
                $out[] = ['id' => $r->id, 'kind' => $r->type === 'duplicate_of' ? 'duplicated_by' : 'relates_to', 'task' => $r->task];
            }
        }

        return $out;
    }
}
