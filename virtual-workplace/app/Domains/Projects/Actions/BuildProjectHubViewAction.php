<?php

namespace App\Domains\Projects\Actions;

use App\Domains\Identity\Models\User;
use App\Domains\Meetings\Models\Meeting;
use App\Domains\Projects\Models\ActiveTimer;
use App\Domains\Projects\Models\Project;
use App\Domains\Projects\Models\TimeEntry;
use App\Domains\Projects\Models\ProjectState;
use App\Domains\Tenancy\Models\OrganizationMember;

/**
 * Assembles every piece of data the Project Hub view needs: KPIs, the
 * workload matrix, Gantt-chart tasks, and project meetings. Extracted
 * verbatim from ProjectHubController::show(), which had grown to ~200 lines
 * of view-data aggregation. See Architecture Audit §8/§15.
 */
class BuildProjectHubViewAction
{
    public function execute(User $user, OrganizationMember $membership, Project $project): array
    {
        $organization = $membership->organization;

        ProjectState::ensureDefaults($project);

        // Eager load project relations with optimal queries
        $project->load([
            'owner:id,name,email',
            'manager:id,name,email',
            'department:id,name',
            'members.user.profiles',
            'phases',
            'milestones.tasks',
            'customFieldDefinitions',
            'documents.author',
            'goals.targets',
            'sprints.tasks',
            'files.user',
            'labels',
            'states',
            'tasks' => function ($q) {
                $q->with([
                    'assignee.profiles',
                    'assignees:id,name',
                    'labels',
                    'subtasks',
                    'checklistItems',
                    'dependencies.dependsOnTask',
                    'customFieldValues.definition',
                    'sprint',
                    'timeEntries',
                    'comments.user',
                    'attachments.user',
                    'approver',
                    'milestone',
                ])->orderBy('order')->orderBy('created_at');
            },
            'timeEntries' => function ($q) {
                $q->with(['user', 'task'])->latest()->take(100);
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

        $progressPct = $totalTasks > 0 ? (float) round(($completedTasks / $totalTasks) * 100) : 0.0;

        $actualHours = $project->actualHours();
        $billableHours = $project->billableHours();
        $plannedHours = (float) ($project->planned_hours ?? 0);
        $hoursVariance = $plannedHours > 0 ? round($plannedHours - $actualHours, 1) : 0.0;

        $laborCost = $project->laborCost();
        $billableRevenue = $project->billableAmount();
        $budget = (float) ($project->budget_amount ?? 0);
        $budgetVariance = $budget > 0 ? round($budget - $laborCost, 2) : 0.0;
        $grossMargin = round($billableRevenue - $laborCost, 2);
        $grossMarginPct = $billableRevenue > 0 ? round(($grossMargin / $billableRevenue) * 100, 1) : 0.0;

        $kpis = [
            'total_tasks' => $totalTasks,
            'completed_tasks' => $completedTasks,
            'in_progress_tasks' => $inProgressTasks,
            'review_tasks' => $reviewTasks,
            'backlog_tasks' => $backlogTasks,
            'overdue_tasks' => $overdueTasks,
            'progress_pct' => $progressPct,
            'actual_hours' => $actualHours,
            'billable_hours' => $billableHours,
            'planned_hours' => $plannedHours,
            'hours_variance' => $hoursVariance,
            'budget' => $budget,
            'budget_amount' => $budget,
            'labor_cost' => $laborCost,
            'budget_variance' => $budgetVariance,
            'billable_revenue' => $billableRevenue,
            'gross_margin' => $grossMargin,
            'gross_margin_pct' => $grossMarginPct,
            'team_members_count' => $project->members->count(),
        ];

        // Workload Matrix Calculation
        $allMembers = $organization->members()->with(['user.profiles', 'role'])->get();
        $workloadMatrix = $allMembers->map(function ($m) use ($tasks) {
            $assignedTasks = $tasks->filter(fn ($t) => $t->assignee_id === $m->user_id || $t->assignees->contains('id', $m->user_id));
            $totalEstHours = (float) $assignedTasks->sum('estimated_hours');
            $capacity = (float) ($m->weekly_capacity_hours ?? 40.0);
            $utilization = $capacity > 0 ? round(($totalEstHours / $capacity) * 100, 1) : 0;

            return [
                'member' => $m,
                'assigned_hours' => $totalEstHours,
                'tasks_count' => $assignedTasks->count(),
                'capacity' => $capacity,
                'utilization' => $utilization,
                'status' => $utilization > 100 ? 'overloaded' : ($utilization > 75 ? 'optimal' : 'underutilized'),
            ];
        });

        // Gantt: only tasks that really have a date. A task with just a due date (or just a start)
        // is drawn as that one day; tasks without any date are counted, not placed at made-up dates.
        $ganttSource = $tasks->filter(fn ($t) => ! $t->archived_at && ($t->start_date || $t->due_date))->values();
        $ganttUnscheduled = $tasks->filter(fn ($t) => ! $t->archived_at && ! $t->start_date && ! $t->due_date)->count();
        $taskIds = $ganttSource->pluck('id')->map(fn ($id) => (string) $id)->all();

        $ganttTasks = $ganttSource->map(function ($t) use ($taskIds) {
            $start = ($t->start_date ?? $t->due_date)->copy();
            $end = ($t->due_date ?? $t->start_date)->copy();
            if ($end->lt($start)) { // a due date before the start: show it where it is due
                $start = $end->copy();
            }

            // Progress: the share of the checklist that is ticked; otherwise done = 100%, anything else 0%.
            $checklist = $t->checklistItems;
            $progress = $t->status === 'done' ? 100
                : ($checklist->count() > 0 ? (int) round($checklist->where('is_completed', true)->count() / $checklist->count() * 100) : 0);

            return [
                'id' => (string) $t->id,
                'title' => '#'.$t->task_number.' '.$t->title,
                'status' => $t->status,
                'priority' => $t->priority,
                'assignee' => $t->assignee ? $t->assignee->name : __('Unassigned'),
                'start_date' => $start->format('Y-m-d'),
                'due_date' => $end->format('Y-m-d'),
                'has_start' => (bool) $t->start_date,
                'has_due' => (bool) $t->due_date,
                'overdue' => $t->status !== 'done' && $t->due_date && $t->due_date->lt(now()->startOfDay()),
                'progress' => $progress,
                'dependencies' => $t->dependencies
                    ->pluck('depends_on_task_id')
                    ->map(fn ($id) => (string) $id)
                    ->filter(fn ($id) => in_array($id, $taskIds, true))
                    ->values()
                    ->all(),
            ];
        })->values();

        // Project Meetings
        $projectMeetings = Meeting::where('project_id', $project->id)
            ->with(['room', 'creator', 'participants.user'])
            ->latest()
            ->get();

        $upcomingProjectMeetings = $projectMeetings->filter(function ($m) {
            return in_array($m->status, ['scheduled', 'pending', 'active'])
                && (is_null($m->scheduled_at) || $m->scheduled_at->gte(now()->subHours(2)));
        })->sortBy(function ($m) {
            $statusWeight = $m->status === 'active' ? 0 : ($m->status === 'pending' ? 1 : 2);
            $timeWeight = $m->scheduled_at ? $m->scheduled_at->timestamp : 0;

            return sprintf('%d-%012d', $statusWeight, $timeWeight);
        })->values();

        $rooms = $organization->rooms()->get();
        $activeTimer = ActiveTimer::where('user_id', $user->id)->with(['project', 'task'])->first();

        $stats = [
            'active_members' => $allMembers->where('status', 'active')->count(),
            'total_rooms' => $rooms->count(),
            'total_departments' => $organization->departments()->count(),
            'total_projects' => $organization->projects()->count(),
            'total_tasks' => $organization->tasks()->count(),
        ];
        $allProjects = $organization->projects()->visibleTo($user, $membership)->select('id', 'name', 'code', 'status')->latest()->get();
        $departments = $organization->departments()->withCount('teams')->get();
        $teams = $organization->teams()->with('department')->get();
        $myTasks = $organization->tasks()->assignedTo($user->id)->get();

        return [
            'user' => $user,
            'membership' => $membership,
            'organization' => $organization,
            'project' => $project,
            'tasks' => $tasks,
            'kpis' => $kpis,
            'projectMeetings' => $projectMeetings,
            'upcomingProjectMeetings' => $upcomingProjectMeetings,
            'rooms' => $rooms,
            'allMembers' => $allMembers,
            'activeTimer' => $activeTimer,
            'stats' => $stats,
            'allProjects' => $allProjects,
            'departments' => $departments,
            'teams' => $teams,
            'myTasks' => $myTasks,
            'workloadMatrix' => $workloadMatrix,
            // The bulk-assign menu reads $projectMembers; it was never passed, so it rendered empty.
            'projectMembers' => $allMembers,
            // Assigning to someone else is for leads; others may only take a task themselves.
            'canLogManualTime' => TimeEntry::canLogManually($user, $membership),
            'canSeeFinancials' => Project::seesFinancials($user, $membership),
            // Edit details and team: same rule as the PATCH route (permission:projects.edit).
            'canEditProject' => $user->isSuperAdmin() || $membership->role?->slug === 'company_admin' || $membership->hasPermission('projects.edit'),
            'canAssignOthers' => $user->isSuperAdmin() || $membership->role?->slug === 'company_admin'
                || $membership->hasPermission('tasks.assign') || $project->manager_id === $user->id,
            'ganttTasks' => $ganttTasks,
            'ganttUnscheduled' => $ganttUnscheduled,
        ];
    }
}
