<?php

namespace App\Http\Controllers\Web;

use App\Domains\Meetings\Models\Meeting;
use App\Domains\Notifications\Services\NotificationService;
use App\Domains\Projects\Models\ActiveTimer;
use App\Domains\Projects\Models\Project;
use App\Domains\Projects\Models\ProjectFile;
use App\Domains\Projects\Models\Task;
use App\Domains\Tenancy\Models\OrganizationMember;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProjectHubController extends Controller
{
    /**
     * Render the Enterprise Project Hub Dashboard.
     */
    public function show(Request $request, Project $project)
    {
        $user = Auth::user();
        if (! $user) {
            return redirect()->route('login');
        }

        $membership = OrganizationMember::where('user_id', $user->id)
            ->whereIn('status', ['active', 'invited'])
            ->with(['organization.plan', 'role.permissions'])
            ->first();

        if (! $membership || $project->organization_id !== $membership->organization_id) {
            abort(404);
        }

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

        // Interactive Gantt Tasks
        $ganttTasks = $tasks->map(function ($t) use ($project) {
            $start = $t->start_date ?? ($t->due_date ? $t->due_date->copy()->subDays(max(1, (int) ceil(($t->estimated_hours ?? 8) / 8))) : $project->created_at);
            $end = $t->due_date ?? $start->copy()->addDays(2);

            return [
                'id' => $t->id,
                'title' => '#'.$t->task_number.' '.$t->title,
                'status' => $t->status,
                'priority' => $t->priority,
                'assignee' => $t->assignee ? $t->assignee->name : 'Unassigned',
                'start_date' => $start->format('Y-m-d'),
                'due_date' => $end->format('Y-m-d'),
                'progress' => $t->status === 'done' ? 100 : ($t->status === 'in_progress' ? 50 : 0),
                'dependencies' => $t->dependencies->pluck('depends_on_task_id')->toArray(),
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

        return view('projects.hub', compact(
            'user', 'membership', 'organization', 'project', 'tasks', 'kpis',
            'projectMeetings', 'upcomingProjectMeetings', 'rooms', 'allMembers', 'activeTimer',
            'stats', 'allProjects', 'departments', 'teams', 'myTasks',
            'workloadMatrix', 'ganttTasks'
        ));
    }

    /**
     * Store a file asset attached to the project.
     */
    public function storeFile(Request $request, Project $project)
    {
        $user = Auth::user();
        $request->validate([
            'file' => 'required|file|max:51200', // 50MB max
            'category' => 'nullable|string|max:50',
            'description' => 'nullable|string|max:255',
        ]);

        $uploadedFile = $request->file('file');
        $originalName = $uploadedFile->getClientOriginalName();
        $mimeType = $uploadedFile->getClientMimeType();
        $size = $uploadedFile->getSize();

        $path = $uploadedFile->store('projects/'.$project->id.'/files', 'public');

        $file = $project->files()->create([
            'organization_id' => $project->organization_id,
            'user_id' => $user->id,
            'file_name' => $originalName,
            'file_path' => $path,
            'file_type' => $mimeType,
            'file_size' => $size,
            'category' => $request->input('category', 'general'),
            'description' => $request->input('description'),
        ]);

        return response()->json([
            'success' => true,
            'message' => __('File uploaded successfully.'),
            'file' => $file->load('user:id,name,email'),
        ], 201);
    }

    /**
     * Delete a project file.
     */
    public function destroyFile(Project $project, ProjectFile $file)
    {
        $user = Auth::user();
        $membership = OrganizationMember::where('organization_id', $project->organization_id)->where('user_id', $user->id)->first();

        $canDelete = $user->isSuperAdmin()
            || ($membership && $membership->role?->slug === 'company_admin')
            || ($project->manager_id === $user->id)
            || ($file->user_id === $user->id);

        if (! $canDelete) {
            abort(403, __('Unauthorized to delete this file.'));
        }

        if (Storage::disk('public')->exists($file->file_path)) {
            Storage::disk('public')->delete($file->file_path);
        }

        $file->delete();

        return redirect()->back()->with('success', __('File deleted successfully.'));
    }

    /**
     * Post a comment on a project task.
     */
    public function addComment(Request $request, Task $task)
    {
        $user = Auth::user();
        $validated = $request->validate([
            'body' => 'required|string|max:3000',
        ]);

        $comment = $task->comments()->create([
            'user_id' => $user->id,
            'body' => $validated['body'],
        ]);

        // Auto-check for mentions and notify
        preg_match_all('/@([a-zA-Z0-9_\.\p{Arabic}]+)/u', $validated['body'], $matches);
        if (! empty($matches[1])) {
            $mentionedNames = $matches[1];
            $mentionedMembers = OrganizationMember::where('organization_id', $task->organization_id)
                ->whereHas('user', function ($q) use ($mentionedNames) {
                    $q->whereIn('name', $mentionedNames);
                })->with('user')->get();

            foreach ($mentionedMembers as $mem) {
                if ($mem->user_id !== $user->id) {
                    NotificationService::notifyCustom(
                        $mem->user_id,
                        'task_mention',
                        __('🔔 :name mentioned you in task ":task"', ['name' => $user->name, 'task' => $task->title]),
                        Str::limit($validated['body'], 120),
                        ['task_id' => $task->id, 'project_id' => $task->project_id],
                        $user->id
                    );
                }
            }
        }

        if ($task->assignee_id && $task->assignee_id !== $user->id) {
            NotificationService::notifyCustom(
                $task->assignee_id,
                'task_comment',
                __('💬 New comment on your task ":task" by :name', ['task' => $task->title, 'name' => $user->name]),
                Str::limit($validated['body'], 120),
                ['task_id' => $task->id, 'project_id' => $task->project_id],
                $user->id
            );
        }

        return response()->json([
            'success' => true,
            'message' => __('Comment posted.'),
            'comment' => $comment->load('user:id,name,email'),
        ]);
    }

    /**
     * Approve a task as completed (by Project Manager / Admin).
     */
    public function approveTask(Request $request, Task $task)
    {
        $user = Auth::user();
        $membership = OrganizationMember::where('organization_id', $task->organization_id)->where('user_id', $user->id)->first();
        $isManager = $user->isSuperAdmin()
            || ($membership && ($membership->role?->slug === 'company_admin' || $membership->hasPermission('tasks.assign')))
            || ($task->project && $task->project->manager_id === $user->id);

        if (! $isManager) {
            return response()->json(['message' => __('Only Project Managers or Admins can approve tasks.')], 403);
        }

        $task->update([
            'status' => 'done',
            'approval_status' => 'approved',
            'approved_by' => $user->id,
            'approved_at' => now(),
            'completed_at' => now(),
            'rejection_reason' => null,
        ]);

        // Auto-recalculate milestone and goals
        $task->milestone?->checkAndUpdateStatus();
        $task->project?->goals->each->recalculateProgress();

        if ($task->assignee_id && $task->assignee_id !== $user->id) {
            NotificationService::notifyCustom(
                $task->assignee_id,
                'task_approved',
                __('🎉 Task Approved: ":task" has been approved as Completed by :name', ['task' => $task->title, 'name' => $user->name]),
                __('Great work! Your task was reviewed and approved.'),
                ['task_id' => $task->id, 'project_id' => $task->project_id],
                $user->id
            );
        }

        return response()->json([
            'success' => true,
            'message' => __('Task approved and marked as Completed!'),
            'task' => $task->fresh(['project', 'assignee', 'approver']),
        ]);
    }

    /**
     * Reject / Request changes on a task (by Project Manager / Admin).
     */
    public function rejectTask(Request $request, Task $task)
    {
        $user = Auth::user();
        $membership = OrganizationMember::where('organization_id', $task->organization_id)->where('user_id', $user->id)->first();
        $isManager = $user->isSuperAdmin()
            || ($membership && ($membership->role?->slug === 'company_admin' || $membership->hasPermission('tasks.assign')))
            || ($task->project && $task->project->manager_id === $user->id);

        if (! $isManager) {
            return response()->json(['message' => __('Only Project Managers or Admins can review tasks.')], 403);
        }

        $validated = $request->validate([
            'rejection_reason' => 'required|string|max:1000',
        ]);

        $task->update([
            'status' => 'in_progress',
            'approval_status' => 'rejected',
            'approved_by' => $user->id,
            'rejection_reason' => $validated['rejection_reason'],
        ]);

        // Auto-recalculate milestone and goals
        $task->milestone?->checkAndUpdateStatus();
        $task->project?->goals->each->recalculateProgress();

        if ($task->assignee_id && $task->assignee_id !== $user->id) {
            NotificationService::notifyCustom(
                $task->assignee_id,
                'task_rejected',
                __('⚠️ Changes Requested on task ":task" by :name', ['task' => $task->title, 'name' => $user->name]),
                $validated['rejection_reason'],
                ['task_id' => $task->id, 'project_id' => $task->project_id],
                $user->id
            );
        }

        return response()->json([
            'success' => true,
            'message' => __('Task returned for revisions.'),
            'task' => $task->fresh(['project', 'assignee']),
        ]);
    }
}
