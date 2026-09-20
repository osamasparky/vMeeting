<?php

namespace App\Domains\Projects\Actions;

use App\Domains\Identity\Models\User;
use App\Domains\Meetings\Models\Meeting;
use App\Domains\Projects\Models\ActiveTimer;
use App\Domains\Projects\Models\Project;
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
            'tasks' => function ($q) {
                $q->with([
                    'assignee.profiles',
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
            $assignedTasks = $tasks->where('assignee_id', $m->user_id);
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

        // Interactive Gantt Tasks with safe dates & valid dependencies
        $taskIds = $tasks->pluck('id')->toArray();
        $minSafeYear = max(2024, now()->year - 1);
        $maxSafeYear = now()->year + 2;

        $ganttTasks = $tasks->map(function ($t) use ($project, $taskIds, $minSafeYear, $maxSafeYear) {
            $start = $t->start_date
                ? $t->start_date->copy()
                : ($t->due_date ? $t->due_date->copy()->subDays(max(1, (int) ceil(($t->estimated_hours ?? 8) / 8))) : ($project->created_at ? $project->created_at->copy() : now()->subDays(3)));

            $end = $t->due_date
                ? $t->due_date->copy()
                : $start->copy()->addDays(max(2, (int) ceil(($t->estimated_hours ?? 8) / 8)));

            // Normalize outlier/faker dates (e.g. 1979 or year 2099)
            if ($start->year < $minSafeYear || $start->year > $maxSafeYear) {
                $start = now()->subDays(5);
            }
            if ($end->year < $minSafeYear || $end->year > $maxSafeYear) {
                $end = $start->copy()->addDays(max(2, (int) ceil(($t->estimated_hours ?? 8) / 8)));
            }

            // Frappe Gantt requires end date > start date
            if ($end->lte($start)) {
                $end = $start->copy()->addDays(1);
            }

            // Only keep dependencies that exist in current project tasks
            $validDeps = $t->dependencies
                ->pluck('depends_on_task_id')
                ->filter(fn ($id) => in_array($id, $taskIds, true))
                ->values()
                ->toArray();

            return [
                'id' => (string) $t->id,
                'title' => '#'.$t->task_number.' '.$t->title,
                'status' => $t->status,
                'priority' => $t->priority,
                'assignee' => $t->assignee ? $t->assignee->name : __('Unassigned'),
                'start_date' => $start->format('Y-m-d'),
                'due_date' => $end->format('Y-m-d'),
                'progress' => $t->status === 'done' ? 100 : ($t->status === 'in_progress' ? 50 : ($t->status === 'review' ? 75 : 10)),
                'dependencies' => $validDeps,
            ];
        });

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
        $allProjects = $organization->projects()->select('id', 'name', 'code', 'status')->latest()->get();
        $departments = $organization->departments()->withCount('teams')->get();
        $teams = $organization->teams()->with('department')->get();
        $myTasks = $organization->tasks()->where('assignee_id', $user->id)->get();

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
            'ganttTasks' => $ganttTasks,
        ];
    }
}
