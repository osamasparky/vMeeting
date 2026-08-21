<?php

namespace App\Domains\Projects\Controllers;

use App\Domains\Projects\Actions\CreateProjectAction;
use App\Domains\Projects\Actions\UpdateProjectAction;
use App\Domains\Projects\Models\Project;
use App\Domains\Projects\Requests\StoreProjectRequest;
use App\Domains\Projects\Requests\UpdateProjectRequest;
use App\Domains\Tenancy\Models\Organization;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;

class ProjectController extends Controller
{
    /**
     * List projects for an organization.
     */
    public function index(Request $request, Organization $organization): JsonResponse
    {
        $query = Project::forOrganization($organization->id)
            ->with(['owner:id,name,email', 'manager:id,name,email', 'department:id,name'])
            ->withCount(['tasks', 'members']);

        if ($request->filled('status')) {
            $query->where('status', $request->query('status'));
        }

        if ($request->filled('priority')) {
            $query->where('priority', $request->query('priority'));
        }

        if ($request->filled('department_id')) {
            $query->where('department_id', $request->query('department_id'));
        }

        if ($request->filled('search')) {
            $search = $request->query('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%");
            });
        }

        $projects = $query->orderByDesc('created_at')->paginate($request->integer('per_page', 20));

        return response()->json($projects);
    }

    /**
     * Create a new project.
     */
    public function store(
        StoreProjectRequest $request,
        Organization $organization,
        CreateProjectAction $action
    ): JsonResponse {
        $project = $action->execute($request->validated(), $organization, Auth::user());

        return response()->json([
            'message' => 'Project created successfully.',
            'project' => $project,
        ], 201);
    }

    /**
     * Show a project with detailed structure, tasks, and KPI statistics.
     */
    public function show(Organization $organization, Project $project): JsonResponse
    {
        if ($project->organization_id !== $organization->id) {
            return response()->json(['message' => 'Project not found.'], 404);
        }

        $project->load([
            'owner:id,name,email',
            'manager:id,name,email',
            'department:id,name',
            'members.user:id,name,email',
            'phases',
            'milestones',
            'tasks' => function ($q) {
                $q->with([
                    'assignee:id,name,email',
                    'reporter:id,name,email',
                    'subtasks.assignee:id,name,email',
                    'checklistItems',
                    'dependencies.dependsOnTask:id,title,status',
                ])->orderBy('order')->orderBy('created_at');
            },
            'timeEntries' => function ($q) {
                $q->with(['user:id,name,email', 'task:id,title,task_number'])->latest()->take(50);
            },
        ]);

        $tasks = $project->tasks;
        $totalTasks = $tasks->count();
        $completedTasks = $tasks->where('status', 'done')->count();
        $inProgressTasks = $tasks->where('status', 'in_progress')->count();
        $reviewTasks = $tasks->whereIn('status', ['review', 'qa'])->count();
        $backlogTasks = $tasks->whereIn('status', ['backlog', 'ready'])->count();

        $today = now()->toDateString();
        $overdueTasks = $tasks->filter(function ($t) use ($today) {
            return $t->due_date && $t->due_date->toDateString() < $today && $t->status !== 'done';
        })->count();

        $actualHours = $project->actualHours();
        $billableHours = $project->billableHours();
        $plannedHours = (float) ($project->planned_hours ?? 0);
        $hoursVariance = $plannedHours > 0 ? round($plannedHours - $actualHours, 1) : 0;

        $laborCost = $project->laborCost();
        $billableRevenue = $project->billableAmount();
        $budget = (float) ($project->budget_amount ?? 0);
        $budgetVariance = $budget > 0 ? round($budget - $laborCost, 2) : 0;
        $grossMargin = round($billableRevenue - $laborCost, 2);
        $grossMarginPct = $billableRevenue > 0 ? round(($grossMargin / $billableRevenue) * 100, 1) : 0;

        $kpiData = [
            'progress_pct' => $project->progressPercentage(),
            'total_tasks' => $totalTasks,
            'completed_tasks' => $completedTasks,
            'in_progress_tasks' => $inProgressTasks,
            'review_tasks' => $reviewTasks,
            'backlog_tasks' => $backlogTasks,
            'overdue_tasks' => $overdueTasks,
            'planned_hours' => $plannedHours,
            'actual_hours' => $actualHours,
            'billable_hours' => $billableHours,
            'hours_variance' => $hoursVariance,
            'budget_amount' => $budget,
            'labor_cost' => $laborCost,
            'budget_variance' => $budgetVariance,
            'billable_revenue' => $billableRevenue,
            'gross_margin' => $grossMargin,
            'gross_margin_pct' => $grossMarginPct,
            'team_members_count' => $project->members->count(),
        ];

        return response()->json([
            'project' => $project,
            'kpis' => $kpiData,
            'metrics' => $kpiData,
        ]);
    }

    /**
     * Update project details.
     */
    public function update(
        UpdateProjectRequest $request,
        Organization $organization,
        Project $project,
        UpdateProjectAction $action
    ): JsonResponse {
        if ($project->organization_id !== $organization->id) {
            return response()->json(['message' => 'Project not found.'], 404);
        }

        $updated = $action->execute($project, $request->validated());

        return response()->json([
            'message' => 'Project updated successfully.',
            'project' => $updated,
        ]);
    }

    /**
     * Delete / archive a project.
     */
    public function destroy(Organization $organization, Project $project): JsonResponse
    {
        if ($project->organization_id !== $organization->id) {
            return response()->json(['message' => 'Project not found.'], 404);
        }

        $project->delete();

        return response()->json([
            'message' => 'Project deleted successfully.',
        ]);
    }

    // ══════════════════════════════════════════════════════════════
    // CLICKUP MULTI-VIEWS & ADVANCED MODULES
    // ══════════════════════════════════════════════════════════════

    /**
     * Gantt Chart timeline data with dependencies and milestone flags.
     */
    public function gantt(Organization $organization, Project $project): JsonResponse
    {
        if ($project->organization_id !== $organization->id) {
            return response()->json(['message' => 'Project not found.'], 404);
        }

        $tasks = $project->tasks()
            ->with(['assignee:id,name', 'dependencies.dependsOnTask:id,title'])
            ->get()
            ->map(function ($t) use ($project) {
                $start = $t->start_date ?? ($t->due_date ? $t->due_date->copy()->subDays(max(1, (int) ceil(($t->estimated_hours ?? 8) / 8))) : $project->created_at);
                $end = $t->due_date ?? $start->copy()->addDays(2);
                return [
                    'id' => $t->id,
                    'title' => '#' . $t->task_number . ' ' . $t->title,
                    'status' => $t->status,
                    'priority' => $t->priority,
                    'assignee' => $t->assignee ? $t->assignee->name : 'Unassigned',
                    'start_date' => $start->format('Y-m-d'),
                    'due_date' => $end->format('Y-m-d'),
                    'progress' => $t->status === 'done' ? 100 : ($t->status === 'in_progress' ? 50 : 0),
                    'dependencies' => $t->dependencies->pluck('depends_on_task_id')->toArray(),
                ];
            });

        $milestones = $project->milestones()->get()->map(fn ($m) => [
            'id' => $m->id,
            'title' => '🚩 ' . $m->title,
            'target_date' => $m->due_date ? $m->due_date->format('Y-m-d') : null,
            'status' => $m->status,
        ]);

        return response()->json([
            'project_name' => $project->name,
            'tasks' => $tasks,
            'milestones' => $milestones,
        ]);
    }

    /**
     * Team Workload & Capacity Matrix.
     */
    public function workload(Organization $organization, Project $project): JsonResponse
    {
        if ($project->organization_id !== $organization->id) {
            return response()->json(['message' => 'Project not found.'], 404);
        }

        $members = $organization->members()->with(['user:id,name,email', 'role'])->get();
        $tasks = $project->tasks()->where('status', '!=', 'done')->get();

        $workload = $members->map(function ($m) use ($tasks) {
            $assignedTasks = $tasks->where('assignee_id', $m->user_id);
            $totalEstHours = (float) $assignedTasks->sum('estimated_hours');
            $capacity = $m->weekly_capacity_hours ?? 40.0;
            $utilization = $capacity > 0 ? round(($totalEstHours / $capacity) * 100, 1) : 0;

            return [
                'user_id' => $m->user_id,
                'name' => $m->user->name,
                'email' => $m->user->email,
                'role' => $m->role?->name ?? 'Member',
                'weekly_capacity' => (float) $capacity,
                'assigned_hours' => $totalEstHours,
                'assigned_tasks_count' => $assignedTasks->count(),
                'utilization_percentage' => (float) $utilization,
                'status' => $utilization > 100 ? 'overloaded' : ($utilization > 75 ? 'optimal' : 'underutilized'),
            ];
        });

        return response()->json([
            'workload' => $workload,
        ]);
    }

    /**
     * Custom field definitions.
     */
    public function customFields(Organization $organization, Project $project): JsonResponse
    {
        if ($project->organization_id !== $organization->id) {
            return response()->json(['message' => 'Project not found.'], 404);
        }

        $fields = $project->customFieldDefinitions()->get();
        return response()->json(['custom_fields' => $fields]);
    }

    public function storeCustomField(Request $request, Organization $organization, Project $project): JsonResponse
    {
        if ($project->organization_id !== $organization->id) {
            return response()->json(['message' => 'Project not found.'], 404);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'field_type' => 'required|string|in:text,number,dropdown,currency,date,checkbox,rating,url',
            'options' => 'nullable|array',
            'is_required' => 'boolean',
            'color' => 'nullable|string|max:20',
        ]);

        $field = $project->customFieldDefinitions()->create([
            'organization_id' => $organization->id,
            'name' => $validated['name'],
            'field_type' => $validated['field_type'],
            'options' => $validated['options'] ?? null,
            'is_required' => $validated['is_required'] ?? false,
            'color' => $validated['color'] ?? '#245C3A',
            'sort_order' => $project->customFieldDefinitions()->count() + 1,
        ]);

        return response()->json([
            'message' => 'Custom field created successfully.',
            'custom_field' => $field,
        ], 201);
    }

    /**
     * Project Documents (ClickUp Docs).
     */
    public function documents(Organization $organization, Project $project): JsonResponse
    {
        if ($project->organization_id !== $organization->id) {
            return response()->json(['message' => 'Project not found.'], 404);
        }

        $docs = $project->documents()->with('author:id,name,email')->get();
        return response()->json(['documents' => $docs]);
    }

    public function storeDocument(Request $request, Organization $organization, Project $project): JsonResponse
    {
        if ($project->organization_id !== $organization->id) {
            return response()->json(['message' => 'Project not found.'], 404);
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'nullable|string',
            'icon' => 'nullable|string|max:30',
            'is_pinned' => 'boolean',
            'parent_document_id' => 'nullable|exists:project_documents,id',
        ]);

        $doc = $project->documents()->create([
            'organization_id' => $organization->id,
            'created_by' => Auth::id(),
            'title' => $validated['title'],
            'content' => $validated['content'] ?? '',
            'icon' => $validated['icon'] ?? '📄',
            'is_pinned' => $validated['is_pinned'] ?? false,
            'parent_document_id' => $validated['parent_document_id'] ?? null,
        ]);

        return response()->json([
            'message' => 'Document created successfully.',
            'document' => $doc->load('author:id,name,email'),
        ], 201);
    }

    public function updateDocument(Request $request, Organization $organization, Project $project, \App\Domains\Projects\Models\ProjectDocument $document): JsonResponse
    {
        if ($project->organization_id !== $organization->id || $document->project_id !== $project->id) {
            return response()->json(['message' => 'Document not found.'], 404);
        }

        $validated = $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'content' => 'nullable|string',
            'icon' => 'nullable|string|max:30',
            'is_pinned' => 'boolean',
        ]);

        $document->update($validated);
        $document->increment('version');

        return response()->json([
            'message' => 'Document updated successfully.',
            'document' => $document->fresh(['author:id,name,email']),
        ]);
    }

    public function destroyDocument(Organization $organization, Project $project, \App\Domains\Projects\Models\ProjectDocument $document): JsonResponse
    {
        if ($project->organization_id !== $organization->id || $document->project_id !== $project->id) {
            return response()->json(['message' => 'Document not found.'], 404);
        }

        $document->delete();

        return response()->json([
            'message' => 'Document deleted successfully.',
        ]);
    }

    /**
     * Goals & Target Metrics (ClickUp Goals).
     */
    public function goals(Organization $organization, Project $project): JsonResponse
    {
        if ($project->organization_id !== $organization->id) {
            return response()->json(['message' => 'Project not found.'], 404);
        }

        $goals = $project->goals()->with(['owner:id,name', 'targets'])->get();
        return response()->json(['goals' => $goals]);
    }

    public function storeGoal(Request $request, Organization $organization, Project $project): JsonResponse
    {
        if ($project->organization_id !== $organization->id) {
            return response()->json(['message' => 'Project not found.'], 404);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'color' => 'nullable|string|max:20',
            'due_date' => 'nullable|date',
        ]);

        $goal = $project->goals()->create([
            'organization_id' => $organization->id,
            'owner_id' => Auth::id(),
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'color' => $validated['color'] ?? '#245C3A',
            'due_date' => $validated['due_date'] ?? null,
        ]);

        return response()->json([
            'message' => 'Goal created successfully.',
            'goal' => $goal->load('targets'),
        ], 201);
    }

    public function storeGoalTarget(Request $request, Organization $organization, Project $project, \App\Domains\Projects\Models\ProjectGoal $goal): JsonResponse
    {
        if ($project->organization_id !== $organization->id || $goal->project_id !== $project->id) {
            return response()->json(['message' => 'Goal not found.'], 404);
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'target_type' => 'required|string|in:number,currency,tasks,percentage,boolean',
            'start_value' => 'nullable|numeric',
            'target_value' => 'required|numeric',
            'current_value' => 'nullable|numeric',
            'unit' => 'nullable|string|max:30',
        ]);

        $target = $goal->targets()->create($validated);
        $goal->recalculateProgress();

        return response()->json([
            'message' => 'Goal target added successfully.',
            'target' => $target,
            'goal' => $goal->fresh('targets'),
        ], 201);
    }

    public function updateGoalTarget(Request $request, Organization $organization, Project $project, \App\Domains\Projects\Models\ProjectGoal $goal, \App\Domains\Projects\Models\ProjectGoalTarget $target): JsonResponse
    {
        if ($project->organization_id !== $organization->id || $target->goal_id !== $goal->id) {
            return response()->json(['message' => 'Target not found.'], 404);
        }

        $validated = $request->validate([
            'current_value' => 'sometimes|numeric',
            'is_completed' => 'sometimes|boolean',
        ]);

        $target->update($validated);
        $goal->recalculateProgress();

        return response()->json([
            'message' => 'Target updated.',
            'target' => $target,
            'goal' => $goal->fresh('targets'),
        ]);
    }

    /**
     * Sprints (ClickUp Sprints).
     */
    public function sprints(Organization $organization, Project $project): JsonResponse
    {
        if ($project->organization_id !== $organization->id) {
            return response()->json(['message' => 'Project not found.'], 404);
        }

        $sprints = $project->sprints()->withCount('tasks')->get();
        return response()->json(['sprints' => $sprints]);
    }

    public function storeSprint(Request $request, Organization $organization, Project $project): JsonResponse
    {
        if ($project->organization_id !== $organization->id) {
            return response()->json(['message' => 'Project not found.'], 404);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'planned_points' => 'nullable|integer',
        ]);

        $sprint = $project->sprints()->create([
            'organization_id' => $organization->id,
            'name' => $validated['name'],
            'start_date' => $validated['start_date'],
            'end_date' => $validated['end_date'],
            'planned_points' => $validated['planned_points'] ?? 0,
            'status' => 'planned',
        ]);

        return response()->json([
            'message' => 'Sprint created successfully.',
            'sprint' => $sprint,
        ], 201);
    }
}
