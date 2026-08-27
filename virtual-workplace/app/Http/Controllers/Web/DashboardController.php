<?php

namespace App\Http\Controllers\Web;

use App\Domains\Tenancy\Models\OrganizationMember;
use App\Domains\Identity\Models\User;
use App\Domains\Meetings\Models\Meeting;
use App\Domains\Notifications\Models\WorkplaceNotification;
use App\Domains\Notifications\Services\NotificationService;
use App\Domains\Projects\Models\Project;
use App\Domains\Tenancy\Models\Organization;
use App\Http\Controllers\Controller;
use App\Mail\MeetingInvitationMail;
use Carbon\Carbon;
use Database\Seeders\BlueprintOfficeSeeder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class DashboardController extends Controller
{
    public function dashboard()
    {
        $user = Auth::user();

        // If Super Admin accesses tenant dashboard without a tenant membership, route cleanly to Super Admin Dashboard
        if ($user->isSuperAdmin()) {
            $hasMembership = OrganizationMember::where('user_id', $user->id)
                ->whereIn('status', ['active', 'invited'])
                ->exists();
            if (!$hasMembership) {
                return redirect()->route('superadmin.dashboard');
            }
        }

        // Get the active/invited membership
        $membership = OrganizationMember::where('user_id', $user->id)
            ->whereIn('status', ['active', 'invited'])
            ->with(['organization.plan', 'organization.subscription', 'role'])
            ->first();

        if (!$membership) {
            return redirect()->route('login')->with('error', 'No active organization found.');
        }

        if ($membership->status === 'invited') {
            $membership->update(['status' => 'active']);
        }

        $organization = $membership->organization;
        $this->ensureDefaultWorkspace($organization);

        $totalMembers = OrganizationMember::where('organization_id', $organization->id)->whereIn('status', ['active', 'invited'])->count();
        $totalDepts = $organization->departments()->count();
        $totalTeams = $organization->teams()->count();
        $totalRooms = $organization->rooms()->count();
        $totalGuests = \App\Domains\Guests\Models\GuestInvitation::where('organization_id', $organization->id)->count();
        $totalAudit = \App\Domains\Administration\Models\AuditLog::where('organization_id', $organization->id)->count();

        $stats = [
            'members' => $totalMembers,
            'departments' => $totalDepts,
            'teams' => $totalTeams,
            'rooms' => $totalRooms,
            'guests' => $totalGuests,
            'presence_rate' => 94,
            'meetings_count' => max(12, $totalAudit * 3 + 8),
            'collaboration_hours' => max(48, $totalAudit * 14 + 32),
            'occupancy_rate' => 78,
            'productivity_score' => 98.4,
            'screen_share_rate' => 91,
            'audio_quality' => '99.98%',
        ];

        $rooms = $organization->rooms()->with(['floor', 'map.floor'])->get();
        $offices = $organization->offices()->with(['rooms', 'activeMap'])->get();
        $roles = \App\Domains\Administration\Models\Role::where('slug', '!=', 'super_admin')
            ->where(function($q) use ($organization) {
                $q->where('organization_id', $organization->id)->orWhereNull('organization_id');
            })->get();
        $members = $organization->members()->with(['user.profiles', 'role', 'offices', 'rooms'])->get();
        $departments = $organization->departments()->withCount('teams')->get();
        $teams = $organization->teams()->with('department')->get();
        $auditLogs = \App\Domains\Administration\Models\AuditLog::where('organization_id', $organization->id)->latest()->take(20)->get();
        $guestInvitations = \App\Domains\Guests\Models\GuestInvitation::where('organization_id', $organization->id)->with('room')->latest()->take(20)->get();
        $allPlans = \App\Domains\Tenancy\Models\Plan::where('is_active', true)->orderBy('price', 'asc')->get();

        // Project Management entities
        $projects = $organization->projects()->with(['owner', 'manager', 'department'])->withCount('tasks')->latest()->get();
        $tasks = $organization->tasks()->with(['project', 'assignee', 'reporter', 'phase', 'milestone'])->orderBy('order')->latest()->get();
        $myTasks = $tasks->where('assignee_id', $user->id)->values();
        $activeTimer = \App\Domains\Projects\Models\ActiveTimer::where('user_id', $user->id)->with(['project', 'task'])->first();
        $recentTimeEntries = $organization->timeEntries()->where('user_id', $user->id)->with(['project', 'task'])->latest()->take(30)->get();
        $allTimesheets = $organization->timesheets()->with(['user', 'reviewer'])->latest()->take(15)->get();
        $currentMember = $members->firstWhere('user_id', $user->id);
        $myProfile = $currentMember?->user?->profiles?->first() ?? new \App\Domains\People\Models\UserProfile([
            'user_id' => $user->id,
            'organization_id' => $organization->id,
        ]);

        // Scheduled Meetings (Eager Loaded & Collection Filtered)
        $allMeetings = Meeting::where('organization_id', $organization->id)
            ->with(['room', 'project', 'creator', 'participants.user'])
            ->latest()
            ->take(50)
            ->get();

        $upcomingMeetings = $allMeetings->filter(function ($m) {
            return in_array($m->status, ['scheduled', 'pending', 'active'])
                && (is_null($m->scheduled_at) || $m->scheduled_at->gte(now()->subHours(2)));
        })->sortBy(function ($m) {
            $statusWeight = $m->status === 'active' ? 0 : ($m->status === 'pending' ? 1 : 2);
            $timeWeight = $m->scheduled_at ? $m->scheduled_at->timestamp : 0;
            return sprintf('%d-%012d', $statusWeight, $timeWeight);
        })->take(10)->values();

        $smtpSettings = $organization->settings?->smtp_settings ?? [];
        $openAiSettings = $organization->settings?->openai_settings ?? [];
        $attendancePolicy = $organization->settings?->getAttendancePolicy() ?? \App\Domains\Tenancy\Models\OrganizationSetting::getAttendancePolicy();

        $upcomingMeetingsJson = $upcomingMeetings->map(function ($m) {
            return [
                'id' => $m->id,
                'title' => $m->title,
                'scope' => $m->scope,
                'project_name' => $m->project?->name,
                'room_name' => $m->room?->name ?? 'Meeting Room',
                'scheduled_at' => $m->scheduled_at ? $m->scheduled_at->toIso8601String() : null,
                'status' => $m->status,
            ];
        })->values();

        $pendingSubscriptionRequest = $organization->pendingSubscriptionRequest()->with('plan')->first();

        return view('dashboard', compact(
            'user', 'membership', 'organization', 'stats', 'rooms', 'offices', 'roles', 'members',
            'departments', 'teams', 'auditLogs', 'guestInvitations', 'allPlans',
            'projects', 'tasks', 'myTasks', 'activeTimer', 'recentTimeEntries', 'allTimesheets', 'myProfile',
            'upcomingMeetings', 'allMeetings', 'smtpSettings', 'openAiSettings', 'upcomingMeetingsJson',
            'pendingSubscriptionRequest', 'attendancePolicy'
        ));
    }

    /**
     * Dedicated Project Hub Page with comprehensive KPIs, Kanban, Tasks, Timelogs, Meetings, Team, and Roadmap.
     */
    public function projectHub(Project $project)
    {
        $user = Auth::user();
        $membership = OrganizationMember::where('user_id', $user->id)
            ->whereIn('status', ['active', 'invited'])
            ->with(['organization.plan', 'role.permissions'])
            ->first();

        if (!$membership || $project->organization_id !== $membership->organization_id) {
            abort(404);
        }

        $organization = $membership->organization;

        // Eager load project relations
        $project->load([
            'owner:id,name,email',
            'manager:id,name,email',
            'department:id,name',
            'members.user.profiles',
            'phases',
            'milestones',
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

        $progressPct = $totalTasks > 0 ? round(($completedTasks / $totalTasks) * 100) : 0;

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
            'labor_cost' => $laborCost,
            'budget_variance' => $budgetVariance,
            'billable_revenue' => $billableRevenue,
            'gross_margin' => $grossMargin,
            'gross_margin_pct' => $grossMarginPct,
        ];

        // ClickUp Workload Matrix Calculation
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

        // ClickUp Gantt Timeline Tasks
        $ganttTasks = $tasks->map(function ($t) use ($project) {
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
        $activeTimer = \App\Domains\Projects\Models\ActiveTimer::where('user_id', $user->id)->with(['project', 'task'])->first();

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
     * Upgrade / switch company subscription plan from dashboard.
     */

    public function storeScheduledMeeting(Request $request)
    {
        $user = Auth::user();
        $membership = OrganizationMember::where('user_id', $user->id)->first();
        if (!$membership) abort(403);
        $organization = $membership->organization;

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'scope' => 'required|string|in:project,general',
            'project_id' => 'nullable|exists:projects,id',
            'room_id' => 'nullable|exists:rooms,id',
            'scheduled_at' => 'required|date',
            'duration_minutes' => 'nullable|integer|min:5|max:480',
            'attendee_ids' => 'nullable|array',
            'attendee_ids.*' => 'exists:users,id',
        ]);

        $project = null;
        if ($validated['scope'] === 'project' && !empty($validated['project_id'])) {
            $project = $organization->projects()->findOrFail($validated['project_id']);
        }

        $room = null;
        if (!empty($validated['room_id'])) {
            $room = $organization->rooms()->find($validated['room_id']);
        }
        if (!$room) {
            $room = $organization->rooms()->first();
        }

        $meeting = Meeting::create([
            'organization_id' => $organization->id,
            'room_id' => $room?->id,
            'project_id' => $project?->id,
            'created_by' => $user->id,
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'type' => 'scheduled',
            'scope' => $validated['scope'],
            'status' => 'scheduled',
            'scheduled_at' => Carbon::parse($validated['scheduled_at']),
            'duration_minutes' => (int)($validated['duration_minutes'] ?? 30),
            'livekit_room_name' => "meeting_{$organization->id}_" . uniqid(),
            'settings' => [
                'allow_screen_share' => true,
                'allow_chat' => true,
            ],
        ]);

        // Host participant
        $meeting->participants()->create([
            'user_id' => $user->id,
            'role' => 'host',
            'joined_at' => now(),
        ]);

        // Collect recipient users
        $recipients = collect();

        if ($validated['scope'] === 'project' && $project) {
            if ($project->owner_id && $project->owner) $recipients->push($project->owner);
            if ($project->manager_id && $project->manager) $recipients->push($project->manager);
            $taskAssigneeIds = $project->tasks()->whereNotNull('assignee_id')->pluck('assignee_id')->unique();
            $taskUsers = User::whereIn('id', $taskAssigneeIds)->get();
            $recipients = $recipients->concat($taskUsers)->unique('id');
        } elseif (!empty($validated['attendee_ids'])) {
            $recipients = User::whereIn('id', $validated['attendee_ids'])->get();
        }

        foreach ($recipients as $recipient) {
            if ($recipient->id !== $user->id) {
                $meeting->participants()->firstOrCreate([
                    'user_id' => $recipient->id,
                ], [
                    'role' => 'participant',
                    'joined_at' => now(),
                ]);

                // Send live in-app & database notification
                \App\Domains\Notifications\Services\NotificationService::notifyMeetingScheduled($meeting, $recipient, $user);
            }
        }

        // Send Email Invitations
        $this->applyOrganizationSmtp($organization);
        $joinUrl = route('office');
        $sentCount = 0;

        foreach ($recipients as $recipient) {
            if (!empty($recipient->email)) {
                try {
                    Mail::to($recipient->email)->send(
                        new MeetingInvitationMail($meeting, $recipient, $joinUrl)
                    );
                    $sentCount++;
                } catch (\Throwable $e) {
                    Log::warning("Could not send meeting invitation email to {$recipient->email}: " . $e->getMessage());
                }
            }
        }

        $tabRedirect = $validated['scope'] === 'project' ? 'projects' : 'meetings';
        $emailMsg = $sentCount > 0 ? " (" . __(':count invitations emailed', ['count' => $sentCount]) . ")" : "";
        $successMsg = __('Meeting ":title" scheduled successfully!', ['title' => $meeting->title]) . $emailMsg;

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => $successMsg,
                'meeting' => $meeting,
                'redirect_url' => "/dashboard#{$tabRedirect}",
            ]);
        }

        return redirect("/dashboard#{$tabRedirect}")->with('success', $successMsg);
    }

    /**
     * Cancel an upcoming scheduled meeting.
     */
    public function cancelMeeting(Meeting $meeting)
    {
        $user = Auth::user();
        $membership = OrganizationMember::where('user_id', $user->id)->first();
        if (!$membership || $meeting->organization_id !== $membership->organization_id) {
            abort(403);
        }

        $meeting->update(['status' => 'ended']);

        return back()->with('success', __('Meeting ":title" has been cancelled.', ['title' => $meeting->title]));
    }


    public function uploadChatAttachment(Request $request, \App\Domains\Tenancy\Models\Organization $organization)
    {
        $request->validate([
            'file' => 'required|file|max:20480', // max 20MB
        ]);

        $uploadedFile = $request->file('file');
        $originalName = $uploadedFile->getClientOriginalName();
        $mime = $uploadedFile->getMimeType();
        $size = $uploadedFile->getSize();
        $filename = 'chat_' . \Illuminate\Support\Str::uuid() . '.' . ($uploadedFile->getClientOriginalExtension() ?: 'bin');
        $path = $uploadedFile->storeAs("public/chat_files/{$organization->id}", $filename);
        $url = \Illuminate\Support\Facades\Storage::url($path);

        return response()->json([
            'name' => $originalName,
            'url' => $url,
            'size' => $size,
            'mime_type' => $mime,
        ]);
    }

    /**
     * Fetch user profile, live working timer and task list for office spotlight / inspector.
     */

     public function getUserNotifications(Request $request)
     {
         $user = Auth::user();
         if (!$user) {
             return response()->json(['unread_count' => 0, 'notifications' => []]);
         }

         $unreadCount = \App\Domains\Notifications\Models\WorkplaceNotification::forUser($user->id)->unread()->count();
         $notifications = \App\Domains\Notifications\Models\WorkplaceNotification::forUser($user->id)
             ->orderByDesc('created_at')
             ->limit(35)
             ->get()
             ->map(function ($n) {
                 return [
                     'id' => $n->id,
                     'type' => $n->type,
                     'title' => $n->title,
                     'body' => $n->body,
                     'icon' => $n->icon ?: '🔔',
                     'action_url' => $n->action_url,
                     'is_read' => (bool) $n->is_read,
                     'data' => $n->data,
                     'created_at_human' => $n->created_at ? $n->created_at->diffForHumans() : '',
                     'created_at' => $n->created_at ? $n->created_at->toISOString() : '',
                 ];
             });

         return response()->json([
             'unread_count' => $unreadCount,
             'notifications' => $notifications,
         ]);
     }

     /**
      * Mark a specific notification as read.
      */
     public function markNotificationRead(Request $request, string $id)
     {
         $user = Auth::user();
         $notification = \App\Domains\Notifications\Models\WorkplaceNotification::forUser($user->id)->where('id', $id)->first();

         if ($notification) {
             $notification->markAsRead();
         }

         $unreadCount = \App\Domains\Notifications\Models\WorkplaceNotification::forUser($user->id)->unread()->count();

         return response()->json([
             'success' => true,
             'unread_count' => $unreadCount,
         ]);
     }

     /**
      * Mark all notifications as read for current user.
      */
     public function markAllNotificationsRead(Request $request)
     {
         $user = Auth::user();
         \App\Domains\Notifications\Models\WorkplaceNotification::forUser($user->id)
             ->unread()
             ->update([
                 'is_read' => true,
                 'read_at' => now(),
             ]);

         return response()->json([
             'success' => true,
             'unread_count' => 0,
         ]);
     }

     /**
      * Clear / delete all notifications for current user.
      */
     public function clearAllNotifications(Request $request)
     {
         $user = Auth::user();
         \App\Domains\Notifications\Models\WorkplaceNotification::forUser($user->id)->delete();

         return response()->json([
             'success' => true,
             'unread_count' => 0,
             'notifications' => [],
         ]);
     }
    /**
     * Apply organization-level SMTP settings to current runtime configuration.
     */
    private function applyOrganizationSmtp(Organization $organization): void
    {
        $smtp = $organization->settings?->smtp_settings ?? [];
        if (! empty($smtp['host'])) {
            Config::set('mail.mailers.smtp.host', $smtp['host']);
            Config::set('mail.mailers.smtp.port', $smtp['port'] ?? 587);
            Config::set('mail.mailers.smtp.encryption', $smtp['encryption'] ?? 'tls');
            Config::set('mail.mailers.smtp.username', $smtp['username'] ?? null);
            Config::set('mail.mailers.smtp.password', $smtp['password'] ?? null);
            Config::set('mail.from.address', $smtp['from_address'] ?? env('MAIL_FROM_ADDRESS'));
            Config::set('mail.from.name', $smtp['from_name'] ?? $organization->name);
        }
    }

    /**
     * Helper to guarantee default floor, map, and Nanobanaba isometric blueprint layout exist.
     */
    private function ensureDefaultWorkspace(Organization $organization): void
    {
        if ($organization->floors()->count() === 0) {
            $seeder = new BlueprintOfficeSeeder();
            $seeder->seedOrganizationOffice($organization);
        }

        if ($organization->departments()->count() === 0) {
            $eng = \App\Domains\People\Models\Department::create([
                'organization_id' => $organization->id,
                'name' => 'Engineering & Technology',
            ]);
            \App\Domains\People\Models\Team::create(['organization_id' => $organization->id, 'department_id' => $eng->id, 'name' => 'Frontend Team']);
            \App\Domains\People\Models\Team::create(['organization_id' => $organization->id, 'department_id' => $eng->id, 'name' => 'Backend & Cloud']);

            $sales = \App\Domains\People\Models\Department::create([
                'organization_id' => $organization->id,
                'name' => 'Sales & Business Growth',
            ]);
            \App\Domains\People\Models\Team::create(['organization_id' => $organization->id, 'department_id' => $sales->id, 'name' => 'Enterprise Sales']);

            $design = \App\Domains\People\Models\Department::create([
                'organization_id' => $organization->id,
                'name' => 'Product & Design',
            ]);
            \App\Domains\People\Models\Team::create(['organization_id' => $organization->id, 'department_id' => $design->id, 'name' => 'UI / UX Design']);
        }
    }
}
