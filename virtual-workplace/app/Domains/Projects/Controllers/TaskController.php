<?php

namespace App\Domains\Projects\Controllers;

use App\Domains\Projects\Actions\AssignTaskAction;
use App\Domains\Projects\Actions\CreateTaskAction;
use App\Domains\Projects\Actions\UpdateTaskAction;
use App\Domains\Projects\Actions\UpdateTaskStatusAction;
use App\Domains\Projects\Models\Project;
use App\Domains\Projects\Models\Task;
use App\Domains\Projects\Requests\AssignTaskRequest;
use App\Domains\Projects\Requests\StoreTaskRequest;
use App\Domains\Projects\Requests\UpdateTaskRequest;
use App\Domains\Projects\Requests\UpdateTaskStatusRequest;
use App\Domains\Tenancy\Models\Organization;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;

class TaskController extends Controller
{
    /**
     * List tasks for an organization or specific project.
     */
    public function index(Request $request, Organization $organization): JsonResponse
    {
        $query = Task::forOrganization($organization->id)
            ->with(['project:id,name,code', 'assignee:id,name,email', 'reporter:id,name,email', 'phase:id,name', 'milestone:id,name']);

        if ($request->filled('project_id')) {
            $query->where('project_id', $request->query('project_id'));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->query('status'));
        }

        if ($request->filled('priority')) {
            $query->where('priority', $request->query('priority'));
        }

        if ($request->filled('assignee_id')) {
            $query->where('assignee_id', $request->query('assignee_id'));
        }

        if ($request->boolean('root_only', false)) {
            $query->whereNull('parent_task_id');
        }

        if ($request->filled('search')) {
            $search = $request->query('search');
            $query->where('title', 'like', "%{$search}%");
        }

        $tasks = $query->orderBy('order')->orderByDesc('created_at')->paginate($request->integer('per_page', 50));

        return response()->json($tasks);
    }

    /**
     * List current user's personal assigned tasks (My Tasks).
     */
    public function myTasks(Request $request, Organization $organization): JsonResponse
    {
        $user = Auth::user();

        $query = Task::forOrganization($organization->id)
            ->where('assignee_id', $user->id)
            ->with(['project:id,name,code', 'phase:id,name', 'milestone:id,name']);

        $allTasks = $query->orderBy('due_date', 'asc')->get();

        $today = now()->toDateString();

        $dueToday = $allTasks->filter(fn ($t) => $t->due_date && $t->due_date->toDateString() === $today && $t->status !== Task::STATUS_DONE);
        $overdue = $allTasks->filter(fn ($t) => $t->due_date && $t->due_date->toDateString() < $today && $t->status !== Task::STATUS_DONE);
        $upcoming = $allTasks->filter(fn ($t) => (!$t->due_date || $t->due_date->toDateString() > $today) && $t->status !== Task::STATUS_DONE);
        $completed = $allTasks->filter(fn ($t) => $t->status === Task::STATUS_DONE);

        return response()->json([
            'due_today' => $dueToday->values(),
            'overdue' => $overdue->values(),
            'upcoming' => $upcoming->values(),
            'completed' => $completed->values(),
            'summary' => [
                'total' => $allTasks->count(),
                'pending' => $allTasks->where('status', '!=', Task::STATUS_DONE)->count(),
                'overdue' => $overdue->count(),
            ],
        ]);
    }

    /**
     * Create a new task.
     */
    public function store(
        StoreTaskRequest $request,
        Organization $organization,
        CreateTaskAction $action
    ): JsonResponse {
        $task = $action->execute($request->validated(), $organization, Auth::user());

        return response()->json([
            'message' => 'Task created successfully.',
            'task' => $task,
        ], 201);
    }

    /**
     * Show task details.
     */
    public function show(Organization $organization, Task $task): JsonResponse
    {
        if ($task->organization_id !== $organization->id) {
            return response()->json(['message' => 'Task not found.'], 404);
        }

        $task->load([
            'project',
            'assignee:id,name,email',
            'reporter:id,name,email',
            'phase',
            'milestone',
            'parentTask',
            'subtasks.assignee:id,name,email',
            'checklistItems',
            'comments.user:id,name',
            'attachments.user:id,name',
            'dependencies.dependsOnTask:id,title,status',
        ]);

        $auditLogs = \App\Domains\Administration\Models\AuditLog::where('target_type', Task::class)
            ->where('target_id', $task->id)
            ->with(['actor:id,name,email'])
            ->latest()
            ->take(30)
            ->get()
            ->map(function ($log) {
                return [
                    'id' => $log->id,
                    'action' => $log->action,
                    'actor' => [
                        'id' => $log->actor?->id,
                        'name' => $log->actor?->name ?? 'System',
                        'email' => $log->actor?->email,
                    ],
                    'metadata' => $log->metadata ?? [],
                    'created_at' => $log->created_at->toIso8601String(),
                    'relative_time' => $log->created_at->diffForHumans(),
                ];
            });

        return response()->json([
            'task' => $task,
            'activity' => $auditLogs,
            'metrics' => [
                'actual_hours' => $task->actualHours(),
                'is_blocked' => $task->isBlocked(),
            ],
        ]);
    }

    /**
     * Get activity timeline history for a task from AuditLog.
     */
    public function activity(Organization $organization, Task $task): JsonResponse
    {
        if ($task->organization_id !== $organization->id) {
            return response()->json(['message' => 'Task not found.'], 404);
        }

        $logs = \App\Domains\Administration\Models\AuditLog::where('target_type', Task::class)
            ->where('target_id', $task->id)
            ->with(['actor:id,name,email'])
            ->latest()
            ->take(50)
            ->get()
            ->map(function ($log) {
                return [
                    'id' => $log->id,
                    'action' => $log->action,
                    'actor' => [
                        'id' => $log->actor?->id,
                        'name' => $log->actor?->name ?? 'System',
                        'email' => $log->actor?->email,
                    ],
                    'metadata' => $log->metadata ?? [],
                    'created_at' => $log->created_at->toIso8601String(),
                    'relative_time' => $log->created_at->diffForHumans(),
                ];
            });

        return response()->json([
            'success' => true,
            'activities' => $logs,
            'activity' => $logs,
            'data' => $logs,
        ]);
    }

    /**
     * Determine if authenticated user is authorized to edit or update the given task.
     * Non-admin members can only modify tasks assigned to them or created by them.
     */
    protected function authorizeTaskEdit(Organization $organization, Task $task): void
    {
        $user = Auth::user();
        if (!$user) {
            abort(401, 'Unauthenticated.');
        }

        if (method_exists($user, 'isSuperAdmin') && $user->isSuperAdmin()) {
            return;
        }

        // Direct assignee or creator can edit
        if ($task->assignee_id === $user->id || $task->creator_id === $user->id) {
            return;
        }

        // Project manager can edit all tasks in their project
        if ($task->project && $task->project->manager_id === $user->id) {
            return;
        }

        $membership = \App\Domains\Tenancy\Models\OrganizationMember::where('organization_id', $organization->id)
            ->where('user_id', $user->id)
            ->with('role.permissions')
            ->first();

        if (!$membership) {
            abort(403, 'Unauthorized.');
        }

        // Company admins, or roles with tasks.assign / tasks.delete can edit any task
        if ($membership->role?->slug === 'company_admin' || $membership->hasPermission('tasks.assign') || $membership->hasPermission('tasks.delete')) {
            return;
        }

        abort(403, 'Unauthorized. Members can only modify tasks assigned to them.');
    }

    /**
     * Update task details.
     */
    public function update(
        UpdateTaskRequest $request,
        Organization $organization,
        Task $task,
        UpdateTaskAction $action
    ): JsonResponse {
        if ($task->organization_id !== $organization->id) {
            return response()->json(['message' => 'Task not found.'], 404);
        }

        $this->authorizeTaskEdit($organization, $task);

        $updated = $action->execute($task, $request->validated());

        return response()->json([
            'message' => 'Task updated successfully.',
            'task' => $updated,
        ]);
    }

    /**
     * Quick status transition (Kanban drag & drop).
     */
    public function updateStatus(
        UpdateTaskStatusRequest $request,
        Organization $organization,
        Task $task,
        UpdateTaskStatusAction $action
    ): JsonResponse {
        if ($task->organization_id !== $organization->id) {
            return response()->json(['message' => 'Task not found.'], 404);
        }

        $this->authorizeTaskEdit($organization, $task);

        $updated = $action->execute($task, $request->validated('status'));

        return response()->json([
            'message' => 'Task status updated.',
            'task' => $updated,
        ]);
    }

    /**
     * Assign / reassign task.
     */
    public function assign(
        AssignTaskRequest $request,
        Organization $organization,
        Task $task,
        AssignTaskAction $action
    ): JsonResponse {
        if ($task->organization_id !== $organization->id) {
            return response()->json(['message' => 'Task not found.'], 404);
        }

        $updated = $action->execute($task, $request->validated('assignee_id'));

        return response()->json([
            'message' => 'Task assigned successfully.',
            'task' => $updated,
        ]);
    }

    /**
     * Delete task.
     */
    public function destroy(Organization $organization, Task $task): JsonResponse
    {
        if ($task->organization_id !== $organization->id) {
            return response()->json(['message' => 'Task not found.'], 404);
        }

        $task->delete();

        return response()->json([
            'message' => 'Task deleted successfully.',
        ]);
    }

    /**
     * Add checklist item.
     */
    public function addChecklistItem(
        Request $request,
        Organization $organization,
        Task $task,
        \App\Domains\Projects\Actions\ManageTaskChecklistAction $action
    ): JsonResponse {
        if ($task->organization_id !== $organization->id) {
            return response()->json(['message' => 'Task not found.'], 404);
        }

        $this->authorizeTaskEdit($organization, $task);

        $request->validate(['title' => 'required|string|max:255']);
        $item = $action->addItem($task, $request->input('title'));

        return response()->json([
            'message' => 'Checklist item added.',
            'item' => $item,
        ], 201);
    }

    /**
     * Toggle checklist item.
     */
    public function toggleChecklistItem(
        Organization $organization,
        Task $task,
        \App\Domains\Projects\Models\TaskChecklistItem $item,
        \App\Domains\Projects\Actions\ManageTaskChecklistAction $action
    ): JsonResponse {
        if ($task->organization_id !== $organization->id || $item->task_id !== $task->id) {
            return response()->json(['message' => 'Not found.'], 404);
        }

        $this->authorizeTaskEdit($organization, $task);

        $updated = $action->toggleItem($item);

        return response()->json([
            'message' => 'Checklist item updated.',
            'item' => $updated,
        ]);
    }

    /**
     * Add comment to task.
     */
    public function addComment(
        Request $request,
        Organization $organization,
        Task $task,
        \App\Domains\Projects\Actions\AddTaskCommentAction $action
    ): JsonResponse {
        if ($task->organization_id !== $organization->id) {
            return response()->json(['message' => 'Task not found.'], 404);
        }

        $body = $request->input('body') ?? $request->input('content');
        if (empty($body)) {
            return response()->json(['message' => 'Comment body is required.'], 422);
        }

        $comment = $action->execute($task, Auth::user(), $body);

        return response()->json([
            'message' => 'Comment posted successfully.',
            'comment' => $comment,
        ], 201);
    }

    /**
     * Add dependency relationship (with cycle detection).
     */
    public function addDependency(
        Request $request,
        Organization $organization,
        Task $task,
        \App\Domains\Projects\Actions\AddTaskDependencyAction $action
    ): JsonResponse {
        if ($task->organization_id !== $organization->id) {
            return response()->json(['message' => 'Task not found.'], 404);
        }

        $this->authorizeTaskEdit($organization, $task);

        $request->validate(['depends_on_task_id' => 'required|exists:tasks,id']);
        $dependsOnTask = Task::findOrFail($request->input('depends_on_task_id'));

        try {
            $dependency = $action->execute($organization, $task, $dependsOnTask);

            return response()->json([
                'message' => 'Dependency linked successfully.',
                'dependency' => $dependency,
            ], 201);
        } catch (\InvalidArgumentException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    /**
     * Set / update custom field value on task.
     */
    public function setCustomFieldValue(Request $request, Organization $organization, Task $task): JsonResponse
    {
        if ($task->organization_id !== $organization->id) {
            return response()->json(['message' => 'Task not found.'], 404);
        }

        $this->authorizeTaskEdit($organization, $task);

        $validated = $request->validate([
            'custom_field_definition_id' => 'required|exists:custom_field_definitions,id',
            'value_text' => 'nullable|string',
            'value_number' => 'nullable|numeric',
            'value_date' => 'nullable|date',
            'value_boolean' => 'nullable|boolean',
            'value_json' => 'nullable|array',
        ]);

        $customValue = \App\Domains\Projects\Models\TaskCustomFieldValue::updateOrCreate(
            [
                'task_id' => $task->id,
                'custom_field_definition_id' => $validated['custom_field_definition_id'],
            ],
            [
                'value_text' => $validated['value_text'] ?? null,
                'value_number' => $validated['value_number'] ?? null,
                'value_date' => $validated['value_date'] ?? null,
                'value_boolean' => $validated['value_boolean'] ?? null,
                'value_json' => $validated['value_json'] ?? null,
            ]
        );

        return response()->json([
            'message' => 'Custom field updated successfully.',
            'value' => $customValue->load('definition'),
        ]);
    }

    /**
     * Assign task to sprint.
     */
    public function setSprint(Request $request, Organization $organization, Task $task): JsonResponse
    {
        if ($task->organization_id !== $organization->id) {
            return response()->json(['message' => 'Task not found.'], 404);
        }

        $this->authorizeTaskEdit($organization, $task);

        $validated = $request->validate([
            'sprint_id' => 'nullable|exists:project_sprints,id',
            'story_points' => 'nullable|integer|min:0',
        ]);

        $task->update($validated);

        return response()->json([
            'message' => 'Task sprint updated.',
            'task' => $task->fresh('sprint'),
        ]);
    }

    /**
     * Duplicate a task with its checklist items and settings.
     */
    public function duplicate(Request $request, Organization $organization, Task $task): JsonResponse
    {
        if ($task->organization_id !== $organization->id) {
            return response()->json(['message' => 'Task not found.'], 404);
        }

        $this->authorizeTaskEdit($organization, $task);

        $user = Auth::user();

        $clone = $task->replicate([
            'completed_at',
            'created_at',
            'updated_at',
        ]);
        $clone->title = $task->title . ' (Copy)';
        $clone->reporter_id = $user->id;
        $clone->order = Task::where('project_id', $task->project_id)->max('order') + 1;
        $clone->save();

        // Replicate checklist items if any
        foreach ($task->checklistItems as $item) {
            $cloneItem = $item->replicate(['created_at', 'updated_at']);
            $cloneItem->task_id = $clone->id;
            $cloneItem->is_completed = false;
            $cloneItem->completed_at = null;
            $cloneItem->save();
        }

        // Replicate custom field values if any
        foreach ($task->customFieldValues as $cfv) {
            $cloneCfv = $cfv->replicate(['created_at', 'updated_at']);
            $cloneCfv->task_id = $clone->id;
            $cloneCfv->save();
        }

        return response()->json([
            'message' => 'Task duplicated successfully.',
            'task' => $clone->load(['project:id,name,code', 'assignee:id,name,email', 'checklistItems']),
        ], 201);
    }

    /**
     * Move task to a different project or milestone.
     */
    public function move(Request $request, Organization $organization, Task $task): JsonResponse
    {
        if ($task->organization_id !== $organization->id) {
            return response()->json(['message' => 'Task not found.'], 404);
        }

        $this->authorizeTaskEdit($organization, $task);

        $validated = $request->validate([
            'project_id' => 'required|exists:projects,id',
            'milestone_id' => 'nullable|exists:milestones,id',
            'phase_id' => 'nullable|exists:project_phases,id',
        ]);

        // Verify project belongs to same organization
        $targetProject = Project::where('id', $validated['project_id'])
            ->where('organization_id', $organization->id)
            ->firstOrFail();

        $task->update([
            'project_id' => $targetProject->id,
            'milestone_id' => $validated['milestone_id'] ?? null,
            'phase_id' => $validated['phase_id'] ?? null,
        ]);

        return response()->json([
            'message' => 'Task moved successfully.',
            'task' => $task->fresh(['project:id,name,code', 'assignee:id,name,email']),
        ]);
    }
}
