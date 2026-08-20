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
}
