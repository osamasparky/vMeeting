<?php

namespace App\Http\Controllers;

use App\Domains\Administration\Models\Role;
use App\Domains\Identity\Models\User;
use App\Domains\Identity\Services\RealtimeTokenService;
use App\Domains\Meetings\Models\Meeting;
use App\Domains\Projects\Models\Project;
use App\Domains\Tenancy\Actions\CreateOrganizationAction;
use App\Domains\Tenancy\Models\OrganizationMember;
use App\Domains\Tenancy\Models\OrganizationSetting;
use App\Mail\MeetingInvitationMail;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class WebAuthController extends Controller
{
    /**
     * Show the login page.
     */
    public function showLogin()
    {
        if (Auth::check()) {
            if (Auth::user()->isSuperAdmin()) {
                return redirect()->route('superadmin.dashboard');
            }
            return redirect()->route('dashboard');
        }

        return view('auth.login');
    }

    /**
     * Handle login form submission.
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();

            if (Auth::user()->isSuperAdmin()) {
                return redirect()->intended(route('superadmin.dashboard'));
            }

            return redirect()->intended(route('dashboard'));
        }

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ])->onlyInput('email');
    }

    /**
     * Show the registration page.
     */
    public function showRegister()
    {
        if (Auth::check()) {
            if (Auth::user()->isSuperAdmin()) {
                return redirect()->route('superadmin.dashboard');
            }
            return redirect()->route('dashboard');
        }

        $plans = \App\Domains\Tenancy\Models\Plan::where('is_active', true)->orderBy('price', 'asc')->get();

        return view('auth.register', compact('plans'));
    }

    /**
     * Handle registration form submission.
     */
    public function register(Request $request, CreateOrganizationAction $createOrgAction)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'organization_name' => ['required', 'string', 'max:255'],
            'plan_id' => ['nullable', 'exists:plans,id'],
            'plan_slug' => ['nullable', 'string'],
        ]);

        // Create user
        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
        ]);

        // Create organization with user as admin and assigned plan
        $createOrgAction->execute(
            [
                'name' => $validated['organization_name'],
                'plan_id' => $validated['plan_id'] ?? null,
                'plan_slug' => $validated['plan_slug'] ?? null,
            ],
            $user
        );

        // Log in
        Auth::login($user);

        return redirect()->route('dashboard');
    }

    /**
     * Show the dashboard.
     */
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

        $rooms = $organization->rooms()->get();
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

        return view('dashboard', compact(
            'user', 'membership', 'organization', 'stats', 'rooms', 'offices', 'roles', 'members',
            'departments', 'teams', 'auditLogs', 'guestInvitations', 'allPlans',
            'projects', 'tasks', 'myTasks', 'activeTimer', 'recentTimeEntries', 'allTimesheets', 'myProfile',
            'upcomingMeetings', 'allMeetings', 'smtpSettings', 'upcomingMeetingsJson'
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
            'tasks' => function ($q) {
                $q->with([
                    'assignee.profiles',
                    'subtasks',
                    'checklistItems',
                    'dependencies.dependsOnTask',
                    'customFieldValues.definition',
                    'sprint',
                    'timeEntries',
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
    public function upgradePlan(Request $request)
    {
        $user = Auth::user();
        $membership = OrganizationMember::where('user_id', $user->id)
            ->whereIn('status', ['active', 'invited'])
            ->with(['organization', 'role.permissions'])
            ->first();

        if (!$membership) {
            return redirect()->route('login');
        }

        if (!$membership->hasPermission('organizations.manage') && $membership->role?->slug !== 'company_admin') {
            abort(403, 'Unauthorized: only organization admins can modify subscription plans.');
        }

        $validated = $request->validate([
            'plan_id' => ['required', 'exists:plans,id'],
        ]);

        $organization = $membership->organization;
        $newPlan = \App\Domains\Tenancy\Models\Plan::findOrFail($validated['plan_id']);

        $organization->update(['plan_id' => $newPlan->id]);

        return back()->with('success', "Subscription successfully upgraded to {$newPlan->name} Plan!");
    }

    /**
     * Show the interactive Virtual Office floor with multi-branch switcher and room access guard.
     */
    public function office(\App\Domains\Identity\Services\RealtimeTokenService $tokenService)
    {
        $user = Auth::user();

        $membership = OrganizationMember::where('user_id', $user->id)
            ->whereIn('status', ['active', 'invited'])
            ->with(['organization.plan', 'role.permissions', 'offices', 'rooms'])
            ->first();

        if (!$membership) {
            return redirect()->route('login')->with('error', 'No active organization found.');
        }

        if ($membership->status === 'invited') {
            $membership->update(['status' => 'active']);
        }

        $organization = $membership->organization;
        $this->ensureDefaultWorkspace($organization);

        $requestedOfficeId = request('office');
        $allOffices = $organization->offices()->with(['rooms', 'activeMap'])->get();

        // Determine user allowed offices
        $isFullAdmin = $membership->role?->slug === 'company_admin' || $membership->role?->slug === 'super_admin' || $user->isSuperAdmin();
        $userAllowedOffices = $isFullAdmin || $membership->offices()->count() === 0
            ? $allOffices
            : $membership->offices()->with(['rooms', 'activeMap'])->get();

        if ($requestedOfficeId) {
            $floor = $organization->floors()->find($requestedOfficeId);
            if (!$floor) {
                return redirect()->route('office')->with('error', __('Requested office branch not found.'));
            }
            if (!$membership->hasOfficeAccess($floor->id)) {
                return redirect()->route('dashboard')->with('error', __('You do not have access permission to enter this office branch (ليس لديك صلاحية لدخول هذا الفرع).'));
            }
        } else {
            // Find default allowed office
            $floor = $userAllowedOffices->firstWhere('is_default', true)
                ?: $userAllowedOffices->first()
                ?: $organization->floors()->first();
        }

        if (!$floor) {
            return redirect()->route('dashboard')->with('error', __('No active office available.'));
        }

        $map = $organization->maps()->where('floor_id', $floor->id)->where('status', 'published')->latest('published_at')->first()
            ?? $organization->maps()->where('floor_id', $floor->id)->latest()->first();

        if (!$map) {
            // Auto generate initial map for this office
            $map = $organization->maps()->create([
                'floor_id' => $floor->id,
                'name' => $floor->name . ' Blueprint',
                'status' => 'published',
                'version' => 1,
                'width' => 32,
                'height' => 26,
                'tile_size' => 16,
                'layout_data' => [
                    'theme' => 'open_spatial_blueprint',
                    'wall_sign_text' => strtoupper($floor->name),
                ],
                'published_at' => now(),
            ]);
        }

        $map->load(['rooms', 'zones', 'objects']);

        // Determine allowed room IDs for this user
        $userAllowedRoomIds = [];
        if ($isFullAdmin) {
            $userAllowedRoomIds = $map->rooms->pluck('id')->toArray();
        } else {
            $assignedRoomIds = $membership->rooms()->pluck('rooms.id')->toArray();
            if (count($assignedRoomIds) > 0) {
                $userAllowedRoomIds = $assignedRoomIds;
            } else {
                // If no specific room restrictions assigned, allow all public rooms in this map
                $userAllowedRoomIds = $map->rooms->where('access_mode', '!=', 'private')->pluck('id')->toArray();
            }
        }

        $realtimeToken = $tokenService->generateToken($user, $organization);
        $wsUrl = env('REALTIME_WS_URL', 'ws://127.0.0.1:8080');

        $furnitureItems = Cache::remember('furniture_catalog_active', 86400, function () {
            return \App\Domains\Workspace\Models\FurnitureItem::where('is_active', true)->get();
        });

        return view('office', compact('user', 'organization', 'membership', 'floor', 'map', 'allOffices', 'userAllowedOffices', 'userAllowedRoomIds', 'realtimeToken', 'wsUrl', 'furnitureItems'));
    }

    /**
     * Create a new Office branch for the organization.
     */
    public function storeOffice(Request $request)
    {
        $user = Auth::user();
        $membership = OrganizationMember::where('user_id', $user->id)->with(['organization.plan', 'role.permissions'])->first();
        if (!$membership) abort(403);

        if (!$membership->hasPermission('maps.manage') && $membership->role?->slug !== 'company_admin') {
            abort(403, 'Unauthorized: only organization admins can create offices.');
        }

        $organization = $membership->organization;

        if ($organization->hasReachedOfficeLimit()) {
            return back()->with('error', __('Office limit reached for your current plan (:limit offices max). Please upgrade your subscription.', [
                'limit' => $organization->plan?->max_offices ?? 1
            ]));
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'city_location' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'is_default' => ['nullable', 'boolean'],
        ]);

        if (!empty($validated['is_default'])) {
            $organization->floors()->update(['is_default' => false]);
        }

        $floor = $organization->floors()->create([
            'name' => $validated['name'],
            'city_location' => $validated['city_location'] ?? null,
            'description' => $validated['description'] ?? null,
            'is_default' => !empty($validated['is_default']),
            'order' => $organization->floors()->count() + 1,
        ]);

        // Create default published map
        $map = $organization->maps()->create([
            'floor_id' => $floor->id,
            'name' => $floor->name . ' Blueprint',
            'status' => 'published',
            'version' => 1,
            'width' => 32,
            'height' => 26,
            'tile_size' => 16,
            'layout_data' => [
                'theme' => 'open_spatial_blueprint',
                'wall_sign_text' => strtoupper($floor->name),
            ],
            'published_at' => now(),
        ]);

        \App\Domains\Administration\Models\AuditLog::create([
            'organization_id' => $organization->id,
            'actor_id' => $user->id,
            'action' => 'office.created',
            'metadata' => [
                'office_id' => $floor->id,
                'name' => $floor->name,
                'city' => $floor->city_location,
            ],
        ]);

        return back()->with('success', __("Office ':name' created successfully!", ['name' => $floor->name]));
    }

    /**
     * Update Office branch details.
     */
    public function updateOffice(Request $request, \App\Domains\Workspace\Models\Floor $floor)
    {
        $user = Auth::user();
        $membership = OrganizationMember::where('user_id', $user->id)->with(['organization', 'role.permissions'])->first();
        if (!$membership || $floor->organization_id !== $membership->organization_id) abort(403);

        if (!$membership->hasPermission('maps.manage') && $membership->role?->slug !== 'company_admin') {
            abort(403, 'Unauthorized');
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'city_location' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'is_default' => ['nullable', 'boolean'],
        ]);

        if (!empty($validated['is_default'])) {
            $membership->organization->floors()->where('id', '!=', $floor->id)->update(['is_default' => false]);
        }

        $floor->update([
            'name' => $validated['name'],
            'city_location' => $validated['city_location'] ?? null,
            'description' => $validated['description'] ?? null,
            'is_default' => !empty($validated['is_default']),
        ]);

        return back()->with('success', __("Office ':name' updated successfully.", ['name' => $floor->name]));
    }

    /**
     * Delete an Office branch.
     */
    public function deleteOffice(\App\Domains\Workspace\Models\Floor $floor)
    {
        $user = Auth::user();
        $membership = OrganizationMember::where('user_id', $user->id)->with(['organization', 'role.permissions'])->first();
        if (!$membership || $floor->organization_id !== $membership->organization_id) abort(403);

        if (!$membership->hasPermission('maps.manage') && $membership->role?->slug !== 'company_admin') {
            abort(403, 'Unauthorized');
        }

        if ($membership->organization->floors()->count() <= 1) {
            return back()->with('error', __('You cannot delete the only remaining office branch.'));
        }

        $officeName = $floor->name;
        $floor->rooms()->delete();
        $floor->maps()->delete();
        $floor->delete();

        return back()->with('success', __("Office ':name' deleted successfully.", ['name' => $officeName]));
    }

    /**
     * Show the Visual Office Map Editor & Floor Designer.
     */
    public function editor()
    {
        $user = Auth::user();

        $membership = OrganizationMember::where('user_id', $user->id)
            ->whereIn('status', ['active', 'invited'])
            ->with(['organization.plan'])
            ->first();

        if (!$membership) {
            return redirect()->route('login')->with('error', 'No active organization found.');
        }

        if ($membership->status === 'invited') {
            $membership->update(['status' => 'active']);
        }

        if (!$membership->hasPermission('maps.manage') && $membership->role?->slug !== 'company_admin') {
            return redirect()->route('dashboard')->with('error', __('Unauthorized: You do not have permission to access the Floor Map Editor.'));
        }

        $organization = $membership->organization;
        $this->ensureDefaultWorkspace($organization);

        $requestedOfficeId = request('office');
        if ($requestedOfficeId) {
            $floor = $organization->floors()->where('id', $requestedOfficeId)->first() ?? $organization->defaultOffice() ?? $organization->floors()->first();
        } else {
            $floor = $organization->defaultOffice() ?? $organization->floors()->first();
        }

        if (!$floor) {
            $floor = $organization->floors()->create([
                'name' => $organization->name . ' HQ',
                'is_default' => true,
                'order' => 1,
            ]);
        }

        $map = $organization->maps()->where('floor_id', $floor->id)->where('status', 'published')->latest('published_at')->first()
            ?? $organization->maps()->where('floor_id', $floor->id)->latest()->first();

        if (!$map) {
            $map = $organization->maps()->create([
                'floor_id' => $floor->id,
                'name' => $floor->name . ' Blueprint',
                'status' => 'published',
                'version' => 1,
                'width' => 32,
                'height' => 26,
                'tile_size' => 16,
                'layout_data' => [
                    'theme' => 'open_spatial_blueprint',
                    'wall_sign_text' => strtoupper($floor->name),
                ],
                'published_at' => now(),
            ]);
        }

        $map->load(['rooms', 'zones', 'objects', 'versions']);
        $floors = $organization->floors()->orderBy('is_default', 'desc')->orderBy('name', 'asc')->get();

        $furnitureCategories = Cache::remember('furniture_categories_with_items', 86400, function () {
            return \App\Domains\Workspace\Models\FurnitureCategory::with('items')
                ->orderBy('order', 'asc')
                ->get();
        });

        $furnitureItems = Cache::remember('furniture_catalog_active', 86400, function () {
            return \App\Domains\Workspace\Models\FurnitureItem::where('is_active', true)->get();
        });

        return view('editor', compact('user', 'organization', 'floor', 'floors', 'map', 'furnitureCategories', 'furnitureItems'));
    }

    /**
     * Upload custom floorplan background image via web session.
     */
    public function uploadMapBackground(Request $request, \App\Domains\Workspace\Models\Map $map)
    {
        $user = Auth::user();
        $membership = OrganizationMember::where('user_id', $user->id)
            ->where('organization_id', $map->organization_id)
            ->first();

        if (!$membership) {
            return response()->json(['message' => 'Unauthorized access.'], 403);
        }

        $file = $request->file('image') ?? $request->file('background');

        if (!$file) {
            return response()->json(['message' => 'No image file provided.'], 422);
        }

        $request->validate([
            'image' => ['nullable', 'file', 'image', 'mimes:jpeg,png,jpg,webp', 'max:51200'],
            'background' => ['nullable', 'file', 'image', 'mimes:jpeg,png,jpg,webp', 'max:51200'],
        ]);

        $filename = 'floorplan_' . $map->id . '_' . time() . '.' . $file->getClientOriginalExtension();
        
        $destDir = public_path('images/maps');
        if (!file_exists($destDir)) {
            mkdir($destDir, 0755, true);
        }
        $file->move($destDir, $filename);
        $url = '/images/maps/' . $filename;

        $layoutData = $map->layout_data ?? [];
        $layoutData['background_image_url'] = $url;

        $imageSize = @getimagesize(public_path('images/maps/' . $filename));
        if ($imageSize) {
            $layoutData['background_width'] = $imageSize[0];
            $layoutData['background_height'] = $imageSize[1];
        }

        $map->update([
            'layout_data' => $layoutData,
        ]);

        return response()->json([
            'message' => 'Floorplan uploaded successfully.',
            'image_url' => $url,
            'map' => $map->fresh(['floor', 'rooms', 'zones', 'objects']),
        ]);
    }

    /**
     * Remove custom floorplan and revert to system default.
     */
    public function deleteMapBackground(Request $request, \App\Domains\Workspace\Models\Map $map)
    {
        $user = Auth::user();
        $membership = OrganizationMember::where('user_id', $user->id)
            ->where('organization_id', $map->organization_id)
            ->first();

        if (!$membership) {
            return response()->json(['message' => 'Unauthorized access.'], 403);
        }

        $layoutData = $map->layout_data ?? [];
        unset($layoutData['background_image_url']);
        unset($layoutData['background_width']);
        unset($layoutData['background_height']);

        $map->update([
            'layout_data' => $layoutData,
        ]);

        return response()->json([
            'message' => 'Floorplan removed successfully. Reverted to default.',
            'map' => $map->fresh(['floor', 'rooms', 'zones', 'objects']),
        ]);
    }

    /**
     * Completely clear all furniture objects and reset floorplan for a fresh canvas.
     */
    public function clearEditorMap(Request $request, \App\Domains\Workspace\Models\Map $map)
    {
        $user = Auth::user();
        $membership = OrganizationMember::where('user_id', $user->id)
            ->where('organization_id', $map->organization_id)
            ->first();

        if (!$membership) {
            return response()->json(['message' => 'Unauthorized access.'], 403);
        }

        // Delete all furniture objects
        \App\Domains\Workspace\Models\MapObject::where('map_id', $map->id)->delete();

        // Clear custom background image
        $layoutData = $map->layout_data ?? [];
        unset($layoutData['background_image_url']);
        unset($layoutData['background_width']);
        unset($layoutData['background_height']);

        $map->update([
            'layout_data' => $layoutData,
        ]);

        return response()->json([
            'message' => 'Canvas completely cleared. Ready for new layout.',
            'map' => $map->fresh(['floor', 'rooms', 'zones', 'objects']),
        ]);
    }

    /**
     * Save draft map objects and layout data via web session.
     */
    public function saveEditorMap(Request $request, \App\Domains\Workspace\Models\Map $map)
    {
        $user = Auth::user();
        $membership = OrganizationMember::where('user_id', $user->id)
            ->where('organization_id', $map->organization_id)
            ->first();

        if (!$membership) {
            return response()->json(['message' => 'Unauthorized access.'], 403);
        }

        $validated = $request->validate([
            'name' => 'nullable|string|max:120',
            'layout_data' => 'nullable|array',
            'rooms' => 'nullable|array',
            'objects' => 'nullable|array',
        ]);

        if (!empty($validated['name'])) {
            $map->name = $validated['name'];
        }
        if (isset($validated['layout_data'])) {
            $map->layout_data = array_merge($map->layout_data ?? [], $validated['layout_data']);
        }
        $map->tile_size = 16;
        $map->save();

        if (isset($validated['rooms'])) {
            \App\Domains\Workspace\Models\Room::where('map_id', $map->id)->delete();
            foreach ($validated['rooms'] as $r) {
                \App\Domains\Workspace\Models\Room::create([
                    'id' => (!empty($r['id']) && strlen($r['id']) === 36 && str_contains($r['id'], '-')) ? $r['id'] : (string) \Illuminate\Support\Str::uuid(),
                    'organization_id' => $map->organization_id,
                    'map_id' => $map->id,
                    'name' => $r['name'] ?? 'Meeting Room',
                    'type' => $r['type'] ?? 'meeting',
                    'access_mode' => $r['access_mode'] ?? 'public',
                    'capacity' => $r['capacity'] ?? 10,
                    'color' => $r['color'] ?? '#4F9B5F',
                    'bounds' => $r['bounds'] ?? ['x' => 1, 'y' => 1, 'width' => 8, 'height' => 6],
                    'metadata' => $r['metadata'] ?? [],
                ]);
            }
        }

        if (isset($validated['objects'])) {
            \App\Domains\Workspace\Models\MapObject::where('map_id', $map->id)->delete();
            foreach ($validated['objects'] as $obj) {
                \App\Domains\Workspace\Models\MapObject::create([
                    'map_id' => $map->id,
                    'type' => $obj['type'] ?? 'desk',
                    'name' => $obj['name'] ?? null,
                    'position' => $obj['position'] ?? ['x' => 0, 'y' => 0],
                    'size' => $obj['size'] ?? ['width' => 1, 'height' => 1],
                    'rotation' => $obj['rotation'] ?? 0,
                    'color' => $obj['color'] ?? null,
                    'interaction_config' => $obj['interaction_config'] ?? null,
                ]);
            }
        }

        return response()->json([
            'message' => 'Map saved successfully.',
            'map' => $map->fresh(['floor', 'rooms', 'zones', 'objects']),
        ]);
    }

    /**
     * Publish map via web session.
     */
    public function publishEditorMap(Request $request, \App\Domains\Workspace\Models\Map $map, \App\Domains\Workspace\Actions\PublishMapAction $action)
    {
        $user = Auth::user();
        $membership = OrganizationMember::where('user_id', $user->id)
            ->where('organization_id', $map->organization_id)
            ->first();

        if (!$membership) {
            return response()->json(['message' => 'Unauthorized access.'], 403);
        }

        $publishedMap = $action->execute($map, $user);

        return response()->json([
            'message' => 'Map published successfully.',
            'map' => $publishedMap,
        ]);
    }

    /**
     * Create room via web session.
     */
    public function saveEditorRoom(Request $request)
    {
        $user = Auth::user();
        $orgId = $request->input('organization_id');
        $membership = OrganizationMember::where('user_id', $user->id)
            ->where('organization_id', $orgId)
            ->first();

        if (!$membership) {
            return response()->json(['message' => 'Unauthorized access.'], 403);
        }

        $validated = $request->validate([
            'organization_id' => 'required|uuid',
            'map_id' => 'required|uuid',
            'name' => 'required|string|max:100',
            'type' => 'required|string',
            'access_mode' => 'nullable|string|in:public,private',
            'capacity' => 'nullable|integer|min:1|max:200',
            'color' => 'nullable|string|max:20',
            'bounds' => 'required|array',
            'metadata' => 'nullable|array',
        ]);

        $room = \App\Domains\Workspace\Models\Room::create($validated);

        return response()->json([
            'message' => 'Room created successfully.',
            'room' => $room,
        ], 201);
    }

    /**
     * Update or create room via web session.
     */
    public function updateEditorRoom(Request $request, $room)
    {
        $user = Auth::user();
        $roomModel = $room instanceof \App\Domains\Workspace\Models\Room ? $room : \App\Domains\Workspace\Models\Room::find($room);

        if (!$roomModel) {
            $orgId = $request->input('organization_id');
            $membership = OrganizationMember::where('user_id', $user->id)
                ->where('organization_id', $orgId)
                ->first();

            if (!$membership) {
                return response()->json(['message' => 'Unauthorized access.'], 403);
            }

            $validated = $request->validate([
                'organization_id' => 'required|uuid',
                'map_id' => 'required|uuid',
                'name' => 'required|string|max:100',
                'type' => 'required|string',
                'access_mode' => 'nullable|string',
                'capacity' => 'nullable|integer',
                'color' => 'nullable|string',
                'bounds' => 'required|array',
                'metadata' => 'nullable|array',
            ]);

            $roomModel = \App\Domains\Workspace\Models\Room::create($validated);

            return response()->json([
                'message' => 'Room created successfully.',
                'room' => $roomModel,
            ]);
        }

        $membership = OrganizationMember::where('user_id', $user->id)
            ->where('organization_id', $roomModel->organization_id)
            ->first();

        if (!$membership) {
            return response()->json(['message' => 'Unauthorized access.'], 403);
        }

        $roomModel->update($request->only([
            'name',
            'type',
            'access_mode',
            'capacity',
            'color',
            'bounds',
            'metadata'
        ]));

        return response()->json([
            'message' => 'Room updated successfully.',
            'room' => $roomModel->fresh(),
        ]);
    }

    /**
     * Delete room via web session.
     */
    public function deleteEditorRoom(Request $request, \App\Domains\Workspace\Models\Room $room)
    {
        $user = Auth::user();
        $membership = OrganizationMember::where('user_id', $user->id)
            ->where('organization_id', $room->organization_id)
            ->first();

        if (!$membership) {
            return response()->json(['message' => 'Unauthorized access.'], 403);
        }

        $room->delete();

        return response()->json([
            'message' => 'Room deleted successfully.'
        ]);
    }

    /**
     * Helper to guarantee default floor, map, and Nanobanaba isometric blueprint layout exist.
     */
    private function ensureDefaultWorkspace(\App\Domains\Tenancy\Models\Organization $organization): void
    {
        if ($organization->floors()->count() === 0) {
            $seeder = new \Database\Seeders\BlueprintOfficeSeeder();
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


    /**
     * Show Guest Invitation Lobby Page.
     */
    public function guestJoin(string $token)
    {
        $invitation = \App\Domains\Guests\Models\GuestInvitation::where('token', $token)
            ->with(['organization', 'room', 'host'])
            ->first();

        if (!$invitation) {
            return view('guest_join', ['error' => 'This invitation link is invalid or does not exist.']);
        }

        if ($invitation->isExpired()) {
            return view('guest_join', ['error' => 'This invitation link has expired. Please ask the host for a new link.']);
        }

        return view('guest_join', compact('invitation'));
    }

    /**
     * Enter the Virtual Office as a Guest.
     */
    public function guestEnter(Request $request, string $token, RealtimeTokenService $tokenService)
    {
        $invitation = \App\Domains\Guests\Models\GuestInvitation::where('token', $token)
            ->with(['organization', 'room.map.floor', 'host'])
            ->first();

        if (!$invitation || $invitation->isExpired()) {
            return redirect()->route('guest.join', $token)->with('error', 'Invitation expired or invalid.');
        }

        $validated = $request->validate([
            'guest_name' => ['required', 'string', 'max:100'],
        ]);

        $guestName = $validated['guest_name'];
        $organization = $invitation->organization;
        $room = $invitation->room;
        
        $targetRoom = $room;
        $floor = null;
        if ($targetRoom && $targetRoom->map && $targetRoom->map->floor) {
            $floor = $targetRoom->map->floor;
        } elseif ($targetRoom && $targetRoom->floor_id) {
            $floor = $organization->floors()->find($targetRoom->floor_id);
        }
        if (!$floor) {
            $floor = $organization->floors()->where('is_default', true)->first() ?: $organization->floors()->first();
        }

        $map = ($targetRoom && $targetRoom->map) ? $targetRoom->map : (
            $organization->maps()->where('floor_id', $floor->id)->where('status', 'published')->latest('published_at')->first()
            ?? $organization->maps()->where('floor_id', $floor->id)->latest()->first()
        );

        $map->load(['rooms', 'zones', 'objects']);

        $guestId = 'guest_' . md5($token . '_' . microtime(true));
        $realtimeToken = $tokenService->generateGuestTokenWithId($guestId, $guestName, $organization);
        $wsUrl = env('REALTIME_WS_URL', 'ws://127.0.0.1:8080');

        $user = (object)[
            'id' => $guestId,
            'name' => "{$guestName} (Guest)",
            'email' => "{$guestId}@guest.local",
            'avatar_url' => null,
            'is_guest' => true,
        ];

        $targetRoom = $map->rooms->where('id', $room->id)->first() ?? $map->rooms->first() ?? $room;

        $initialSpawn = [
            'x' => ($targetRoom->bounds['x'] + $targetRoom->bounds['width'] / 2) * 32,
            'y' => ($targetRoom->bounds['y'] + $targetRoom->bounds['height'] / 2) * 32,
        ];

        return view('office', compact('user', 'invitation', 'organization', 'floor', 'map', 'room', 'realtimeToken', 'wsUrl', 'initialSpawn'));
    }

    /**
     * Store new Department.
     */
    public function storeDepartment(\App\Domains\People\Requests\StoreDepartmentRequest $request)
    {
        $user = Auth::user();
        $membership = OrganizationMember::where('user_id', $user->id)->with('role.permissions')->first();
        if (!$membership) abort(403);

        if (!$membership->hasPermission('departments.manage') && $membership->role?->slug !== 'company_admin') {
            abort(403, 'Unauthorized: insufficient permissions to manage departments.');
        }

        $validated = $request->validated();

        \App\Domains\People\Models\Department::create([
            'organization_id' => $membership->organization_id,
            'name' => $validated['name'],
        ]);

        return back()->with('success', 'Department created successfully.');
    }

    /**
     * Update Department.
     */
    public function updateDepartment(\App\Domains\People\Requests\StoreDepartmentRequest $request, \App\Domains\People\Models\Department $department)
    {
        $user = Auth::user();
        $membership = OrganizationMember::where('user_id', $user->id)->with('role.permissions')->first();
        if (!$membership) abort(403);

        if ($department->organization_id !== $membership->organization_id) {
            abort(403, 'Unauthorized department access.');
        }

        if (!$membership->hasPermission('departments.manage') && $membership->role?->slug !== 'company_admin') {
            abort(403, 'Unauthorized: insufficient permissions.');
        }

        $validated = $request->validated();

        $department->update(['name' => $validated['name']]);
        return back()->with('success', 'Department updated successfully.');
    }

    /**
     * Delete Department.
     */
    public function deleteDepartment(\App\Domains\People\Models\Department $department)
    {
        $user = Auth::user();
        $membership = OrganizationMember::where('user_id', $user->id)->with('role.permissions')->first();
        if (!$membership) abort(403);

        if ($department->organization_id !== $membership->organization_id) {
            abort(403, 'Unauthorized department access.');
        }

        if (!$membership->hasPermission('departments.manage') && $membership->role?->slug !== 'company_admin') {
            abort(403, 'Unauthorized: insufficient permissions.');
        }

        $department->teams()->delete();
        $department->delete();
        return back()->with('success', 'Department deleted successfully.');
    }

    /**
     * Store new Team in Department.
     */
    public function storeTeam(\App\Domains\People\Requests\StoreTeamRequest $request)
    {
        $user = Auth::user();
        $membership = OrganizationMember::where('user_id', $user->id)->with('role.permissions')->first();
        if (!$membership) abort(403);

        if (!$membership->hasPermission('teams.manage') && $membership->role?->slug !== 'company_admin') {
            abort(403, 'Unauthorized: insufficient permissions to manage teams.');
        }

        $validated = $request->validated();

        // Verify target department belongs to user's organization
        $department = \App\Domains\People\Models\Department::findOrFail($validated['department_id']);
        if ($department->organization_id !== $membership->organization_id) {
            abort(403, 'Unauthorized department access.');
        }

        \App\Domains\People\Models\Team::create([
            'organization_id' => $membership->organization_id,
            'department_id' => $department->id,
            'name' => $validated['name'],
        ]);

        return back()->with('success', 'Team created successfully.');
    }

    /**
     * Delete Team.
     */
    public function deleteTeam(\App\Domains\People\Models\Team $team)
    {
        $user = Auth::user();
        $membership = OrganizationMember::where('user_id', $user->id)->with('role.permissions')->first();
        if (!$membership) abort(403);

        if ($team->organization_id !== $membership->organization_id) {
            abort(403, 'Unauthorized team access.');
        }

        if (!$membership->hasPermission('teams.manage') && $membership->role?->slug !== 'company_admin') {
            abort(403, 'Unauthorized: insufficient permissions.');
        }

        $team->delete();
        return back()->with('success', 'Team deleted successfully.');
    }

    /**
     * Assign member to department, team, role, and job title.
     */
    public function assignMemberDepartment(\App\Domains\People\Requests\AssignMemberDepartmentRequest $request, OrganizationMember $member)
    {
        $user = Auth::user();
        $membership = OrganizationMember::where('user_id', $user->id)->with('role.permissions')->first();
        if (!$membership) abort(403);

        // Strict tenant boundary verification
        if ($member->organization_id !== $membership->organization_id) {
            abort(403, 'Unauthorized member access.');
        }

        // Administrative permission required to change members/roles
        if (!$membership->hasPermission('members.manage') && $membership->role?->slug !== 'company_admin') {
            abort(403, 'Unauthorized: insufficient permissions to manage members.');
        }

        $validated = $request->validated();

        // Verify department belongs to this organization
        if (!empty($validated['department_id'])) {
            $dept = \App\Domains\People\Models\Department::findOrFail($validated['department_id']);
            if ($dept->organization_id !== $membership->organization_id) {
                abort(403, 'Invalid department selection.');
            }
        }

        // Verify team belongs to this organization
        if (!empty($validated['team_id'])) {
            $team = \App\Domains\People\Models\Team::findOrFail($validated['team_id']);
            if ($team->organization_id !== $membership->organization_id) {
                abort(403, 'Invalid team selection.');
            }
        }

        // Verify role is global or belongs to this organization
        if (!empty($validated['role_id'])) {
            $role = \App\Domains\Administration\Models\Role::findOrFail($validated['role_id']);
            if ($role->slug === 'super_admin' && !$user->isSuperAdmin()) {
                abort(403, 'Unauthorized: only the System Owner (Super Admin) can assign or create a Super Admin.');
            }
            if ($role->organization_id && $role->organization_id !== $membership->organization_id) {
                abort(403, 'Invalid role selection.');
            }
            $member->update(['role_id' => $role->id]);
        }

        $profile = \App\Domains\People\Models\UserProfile::firstOrNew([
            'user_id' => $member->user_id,
            'organization_id' => $member->organization_id,
        ]);

        $profile->department_id = $validated['department_id'] ?? null;
        $profile->team_id = $validated['team_id'] ?? null;
        if (isset($validated['job_title'])) {
            $profile->job_title = $validated['job_title'];
        }
        $profile->save();

        return back()->with('success', 'Member department assignment updated.');
    }

    /**
     * Store or Invite a new Team Member in the Organization.
     */
    public function storeMember(Request $request)
    {
        $user = Auth::user();
        $membership = OrganizationMember::where('user_id', $user->id)->with('role.permissions')->first();
        if (!$membership) abort(403);

        if (!$membership->hasPermission('members.manage') && $membership->role?->slug !== 'company_admin') {
            abort(403, 'Unauthorized: insufficient permissions to manage members.');
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255'],
            'role_id' => ['required', 'exists:roles,id'],
            'password' => ['nullable', 'string', 'min:8'],
            'job_title' => ['nullable', 'string', 'max:255'],
            'department_id' => ['nullable', 'exists:departments,id'],
            'team_id' => ['nullable', 'exists:teams,id'],
            'status' => ['nullable', 'in:active,invited,suspended'],
            'allowed_offices' => ['nullable', 'array'],
            'allowed_offices.*' => ['uuid', 'exists:floors,id'],
            'allowed_rooms' => ['nullable', 'array'],
            'allowed_rooms.*' => ['uuid', 'exists:rooms,id'],
        ]);

        $targetRole = \App\Domains\Administration\Models\Role::findOrFail($validated['role_id']);
        if ($targetRole->slug === 'super_admin' && !$user->isSuperAdmin()) {
            abort(403, 'Unauthorized: only the System Owner (Super Admin) can assign or create a Super Admin.');
        }

        if (!empty($validated['department_id'])) {
            $dept = \App\Domains\People\Models\Department::findOrFail($validated['department_id']);
            if ($dept->organization_id !== $membership->organization_id) {
                abort(403, 'Invalid department selection.');
            }
        }

        if (!empty($validated['team_id'])) {
            $team = \App\Domains\People\Models\Team::findOrFail($validated['team_id']);
            if ($team->organization_id !== $membership->organization_id) {
                abort(403, 'Invalid team selection.');
            }
        }

        // Find or create User
        $targetUser = \App\Domains\Identity\Models\User::where('email', $validated['email'])->first();
        $plainPassword = $validated['password'] ?: 'Password@1234';
        
        if (!$targetUser) {
            $targetUser = \App\Domains\Identity\Models\User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => \Illuminate\Support\Facades\Hash::make($plainPassword),
                'email_verified_at' => now(),
            ]);
        } else {
            $targetUser->name = $validated['name'];
            if (!empty($validated['password'])) {
                $targetUser->password = \Illuminate\Support\Facades\Hash::make($validated['password']);
            }
            $targetUser->save();
        }

        // Create or update membership
        $memberStatus = $validated['status'] ?? 'active';
        $member = OrganizationMember::updateOrCreate(
            [
                'organization_id' => $membership->organization_id,
                'user_id' => $targetUser->id,
            ],
            [
                'role_id' => $validated['role_id'],
                'status' => $memberStatus,
            ]
        );

        // Sync allowed offices & rooms
        if (isset($validated['allowed_offices'])) {
            $member->offices()->sync($validated['allowed_offices']);
        }
        if (isset($validated['allowed_rooms'])) {
            $member->rooms()->sync($validated['allowed_rooms']);
        }

        // Create or update Profile
        $profile = \App\Domains\People\Models\UserProfile::firstOrNew([
            'user_id' => $targetUser->id,
            'organization_id' => $membership->organization_id,
        ]);
        $profile->department_id = $validated['department_id'] ?? null;
        $profile->team_id = $validated['team_id'] ?? null;
        $profile->job_title = $validated['job_title'] ?? null;
        $profile->save();

        \App\Domains\Administration\Models\AuditLog::create([
            'organization_id' => $membership->organization_id,
            'user_id' => $user->id,
            'action' => 'member.created',
            'target_type' => 'user',
            'target_id' => $targetUser->id,
            'metadata' => [
                'name' => $targetUser->name,
                'email' => $targetUser->email,
                'role' => $targetRole->name,
                'status' => $memberStatus,
            ],
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return redirect('/dashboard#members')->with('success', __('Team member created/invited successfully and added to workspace!'));
    }

    /**
     * Update complete Organization Member details (Name, Email, Job Title, Department, Team, Role, Status, Allowed Offices & Rooms).
     */
    public function updateOrganizationMember(Request $request, OrganizationMember $member)
    {
        $user = Auth::user();
        $membership = OrganizationMember::where('user_id', $user->id)->with('role.permissions')->first();
        if (!$membership) abort(403);

        if ($member->organization_id !== $membership->organization_id) {
            abort(403, 'Unauthorized member access.');
        }

        if (!$membership->hasPermission('members.manage') && $membership->role?->slug !== 'company_admin') {
            abort(403, 'Unauthorized: insufficient permissions to manage members.');
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,' . $member->user_id],
            'job_title' => ['nullable', 'string', 'max:255'],
            'department_id' => ['nullable', 'exists:departments,id'],
            'team_id' => ['nullable', 'exists:teams,id'],
            'role_id' => ['required', 'exists:roles,id'],
            'status' => ['required', 'in:active,invited,suspended'],
            'allowed_offices' => ['nullable', 'array'],
            'allowed_offices.*' => ['uuid', 'exists:floors,id'],
            'allowed_rooms' => ['nullable', 'array'],
            'allowed_rooms.*' => ['uuid', 'exists:rooms,id'],
        ]);

        $targetRole = \App\Domains\Administration\Models\Role::findOrFail($validated['role_id']);
        if ($targetRole->slug === 'super_admin' && !$user->isSuperAdmin()) {
            abort(403, 'Unauthorized: only the System Owner (Super Admin) can assign or create a Super Admin.');
        }

        if (!empty($validated['department_id'])) {
            $dept = \App\Domains\People\Models\Department::findOrFail($validated['department_id']);
            if ($dept->organization_id !== $membership->organization_id) {
                abort(403, 'Invalid department selection.');
            }
        }

        if (!empty($validated['team_id'])) {
            $team = \App\Domains\People\Models\Team::findOrFail($validated['team_id']);
            if ($team->organization_id !== $membership->organization_id) {
                abort(403, 'Invalid team selection.');
            }
        }

        $member->user->update([
            'name' => $validated['name'],
            'email' => $validated['email'],
        ]);

        $member->update([
            'role_id' => $validated['role_id'],
            'status' => $validated['status'],
        ]);

        // Sync allowed offices & rooms
        $member->offices()->sync($validated['allowed_offices'] ?? []);
        $member->rooms()->sync($validated['allowed_rooms'] ?? []);

        $profile = \App\Domains\People\Models\UserProfile::firstOrNew([
            'user_id' => $member->user_id,
            'organization_id' => $member->organization_id,
        ]);

        $profile->department_id = $validated['department_id'] ?? null;
        $profile->team_id = $validated['team_id'] ?? null;
        $profile->job_title = $validated['job_title'] ?? null;
        $profile->save();

        return back()->with('success', __('Member details and office/room access updated successfully.'));
    }

    /**
     * Update Member Password by Company Admin.
     */
    public function updateMemberPassword(Request $request, OrganizationMember $member)
    {
        $user = Auth::user();
        $membership = OrganizationMember::where('user_id', $user->id)->with('role.permissions')->first();
        if (!$membership) abort(403);

        if ($member->organization_id !== $membership->organization_id) {
            abort(403, 'Unauthorized member access.');
        }

        if (!$membership->hasPermission('members.manage') && $membership->role?->slug !== 'company_admin') {
            abort(403, 'Unauthorized: insufficient permissions to manage members.');
        }

        $validated = $request->validate([
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $member->user->update([
            'password' => \Illuminate\Support\Facades\Hash::make($validated['password']),
        ]);

        return back()->with('success', __('Member password has been updated successfully.'));
    }

    /**
     * Remove Member from Organization.
     */
    public function deleteOrganizationMember(OrganizationMember $member)
    {
        $user = Auth::user();
        $membership = OrganizationMember::where('user_id', $user->id)->with('role.permissions')->first();
        if (!$membership) abort(403);

        if ($member->organization_id !== $membership->organization_id) {
            abort(403, 'Unauthorized member access.');
        }

        if (!$membership->hasPermission('members.manage') && $membership->role?->slug !== 'company_admin') {
            abort(403, 'Unauthorized: insufficient permissions to manage members.');
        }

        if ($member->user_id === $user->id) {
            return back()->with('error', __('You cannot remove your own administrative account.'));
        }

        $member->offices()->detach();
        $member->rooms()->detach();
        $member->delete();

        return back()->with('success', __('Member has been removed from organization.'));
    }

    /**
     * Fetch full Team Member Profile Details (Bio, Skills, Contact, Assigned Tasks, Work Time Logs, Allowed Offices & Rooms).
     */
    public function getMemberProfileDetails(OrganizationMember $member): \Illuminate\Http\JsonResponse
    {
        $user = Auth::user();
        $membership = OrganizationMember::where('user_id', $user->id)
            ->whereIn('status', ['active', 'invited'])
            ->first();

        if (!$membership || $member->organization_id !== $membership->organization_id) {
            return response()->json(['message' => 'Unauthorized member access.'], 403);
        }

        $targetUser = $member->user;
        $profile = \App\Domains\People\Models\UserProfile::where('user_id', $targetUser->id)
            ->where('organization_id', $member->organization_id)
            ->first();

        $dept = $profile && $profile->department_id ? \App\Domains\People\Models\Department::find($profile->department_id) : null;
        $team = $profile && $profile->team_id ? \App\Domains\People\Models\Team::find($profile->team_id) : null;

        // Fetch tasks assigned to this member in this organization
        $tasks = \App\Domains\Projects\Models\Task::where('organization_id', $member->organization_id)
            ->where('assignee_id', $targetUser->id)
            ->with(['project:id,name,code', 'checklistItems'])
            ->orderBy('due_date')
            ->latest()
            ->get()
            ->map(function ($t) {
                $totalChecklist = $t->checklistItems->count();
                $doneChecklist = $t->checklistItems->where('is_completed', true)->count();

                return [
                    'id' => $t->id,
                    'task_number' => $t->task_number,
                    'title' => $t->title,
                    'status' => $t->status,
                    'priority' => $t->priority,
                    'project' => $t->project ? [
                        'id' => $t->project->id,
                        'name' => $t->project->name,
                        'code' => $t->project->code ?? 'PRJ',
                    ] : null,
                    'due_date' => $t->due_date ? $t->due_date->format('M d, Y') : null,
                    'is_overdue' => $t->due_date && $t->due_date->isPast() && $t->status !== 'done',
                    'estimated_hours' => (float)($t->estimated_hours ?? 0),
                    'actual_hours' => (float)($t->actual_hours ?? 0),
                    'checklist_count' => $totalChecklist,
                    'checklist_done' => $doneChecklist,
                ];
            });

        // Fetch time entries logged by this member in this organization
        $timeEntries = \App\Domains\Projects\Models\TimeEntry::where('organization_id', $member->organization_id)
            ->where('user_id', $targetUser->id)
            ->with(['project:id,name,code', 'task:id,task_number,title'])
            ->latest('started_at')
            ->take(20)
            ->get()
            ->map(function ($te) {
                return [
                    'id' => $te->id,
                    'date' => $te->started_at ? $te->started_at->format('M d, Y') : '—',
                    'duration_formatted' => $te->formattedDuration(),
                    'description' => $te->description ?? 'General Work Session',
                    'project_name' => $te->project?->name ?? 'General',
                    'task_title' => $te->task ? ('#' . $te->task->task_number . ' ' . $te->task->title) : '—',
                    'is_billable' => (bool)$te->is_billable,
                ];
            });

        $totalDurationSeconds = \App\Domains\Projects\Models\TimeEntry::where('organization_id', $member->organization_id)
            ->where('user_id', $targetUser->id)
            ->sum('duration_seconds');
        $totalHoursLogged = round($totalDurationSeconds / 3600, 1);

        $activeTimer = \App\Domains\Projects\Models\ActiveTimer::where('user_id', $targetUser->id)
            ->with(['project:id,name,code', 'task:id,task_number,title'])
            ->first();

        return response()->json([
            'member' => [
                'id' => $member->id,
                'user_id' => $targetUser->id,
                'name' => $targetUser->name,
                'nickname' => $targetUser->nickname,
                'email' => $targetUser->email,
                'avatar_url' => $targetUser->avatar_url,
                'role_name' => $member->role?->name ?? 'Member',
                'role_slug' => $member->role?->slug ?? 'employee',
                'role_id' => $member->role_id,
                'status' => $member->status,
                'joined_at' => $member->joined_at ? $member->joined_at->format('M d, Y') : ($member->created_at ? $member->created_at->format('M d, Y') : '—'),
                'allowed_office_ids' => $member->offices->pluck('id')->toArray(),
                'allowed_room_ids' => $member->rooms->pluck('id')->toArray(),
            ],
            'profile' => [
                'job_title' => $profile?->job_title ?? $member->role?->name ?? 'Team Member',
                'department_id' => $profile?->department_id,
                'team_id' => $profile?->team_id,
                'department_name' => $dept?->name,
                'team_name' => $team?->name,
                'work_mode' => $profile?->work_mode ?? 'remote',
                'phone' => $profile?->phone,
                'date_of_birth' => $profile?->date_of_birth ? $profile->date_of_birth->format('M d, Y') : null,
                'bio' => $profile?->bio,
                'skills' => $profile?->skills ? array_filter(array_map('trim', explode(',', $profile->skills))) : [],
                'hobbies' => $profile?->hobbies ? array_filter(array_map('trim', explode(',', $profile->hobbies))) : [],
                'notes' => $profile?->notes,
                'social_links' => (array)($profile?->social_links ?? []),
            ],
            'stats' => [
                'total_tasks' => $tasks->count(),
                'completed_tasks' => $tasks->where('status', 'done')->count(),
                'in_progress_tasks' => $tasks->where('status', 'in_progress')->count(),
                'pending_tasks' => $tasks->whereNotIn('status', ['done', 'in_progress'])->count(),
                'total_hours_logged' => $totalHoursLogged,
                'active_timer' => $activeTimer ? [
                    'id' => $activeTimer->id,
                    'started_at' => $activeTimer->started_at->toIso8601String(),
                    'project_name' => $activeTimer->project?->name,
                    'task_title' => $activeTimer->task ? ('#' . $activeTimer->task->task_number . ' ' . $activeTimer->task->title) : null,
                ] : null,
            ],
            'tasks' => $tasks,
            'time_entries' => $timeEntries,
        ]);
    }

    /**
     * Clear all guest meeting links for the organization.
     */
    public function clearGuestInvitations(Request $request)
    {
        $user = Auth::user();
        $membership = OrganizationMember::where('user_id', $user->id)->with('role.permissions')->first();
        if (!$membership) abort(403);

        if (!$membership->hasPermission('organizations.manage') && $membership->role?->slug !== 'company_admin') {
            abort(403, 'Unauthorized: insufficient permissions.');
        }

        \App\Domains\Guests\Models\GuestInvitation::where('organization_id', $membership->organization_id)->delete();

        return back()->with('success', __('All guest meeting links have been cleared successfully.'));
    }

    /**
     * Clear / Purge all audit logs for the organization.
     */
    public function clearAuditLogs(Request $request)
    {
        $user = Auth::user();
        $membership = OrganizationMember::where('user_id', $user->id)->with('role.permissions')->first();
        if (!$membership) abort(403);

        if (!$membership->hasPermission('audit.view') && !$membership->hasPermission('organizations.manage') && $membership->role?->slug !== 'company_admin') {
            abort(403, 'Unauthorized: insufficient permissions.');
        }

        \App\Domains\Administration\Models\AuditLog::where('organization_id', $membership->organization_id)->delete();

        return back()->with('success', __('All audit logs have been cleared successfully.'));
    }

    /**
     * Update Workspace / Organization Settings (including Logo upload).
     */
    public function updateOrganizationSettings(Request $request)
    {
        $user = Auth::user();
        $membership = OrganizationMember::where('user_id', $user->id)->with('role.permissions', 'organization')->first();
        if (!$membership) abort(403);

        if (!$membership->hasPermission('organizations.manage') && $membership->role?->slug !== 'company_admin') {
            abort(403, 'Unauthorized: insufficient permissions.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'timezone' => 'required|string|max:100',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp|max:4096',
            'mail_driver' => 'nullable|string|max:50',
            'mail_host' => 'nullable|string|max:255',
            'mail_port' => 'nullable|numeric',
            'mail_username' => 'nullable|string|max:255',
            'mail_password' => 'nullable|string|max:255',
            'mail_encryption' => 'nullable|string|max:50',
            'mail_from_address' => 'nullable|email|max:255',
            'mail_from_name' => 'nullable|string|max:255',
        ]);

        $organization = $membership->organization;
        $organization->name = $validated['name'];
        $organization->timezone = $validated['timezone'];

        if ($request->hasFile('logo')) {
            $file = $request->file('logo');
            $filename = 'org_logo_' . $organization->id . '_' . time() . '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs('logos', $filename, 'public');
            $organization->logo_url = '/storage/' . $path;
        }

        $organization->save();

        // Update SMTP Mail Settings
        $orgSettings = $organization->settings ?? OrganizationSetting::firstOrCreate([
            'organization_id' => $organization->id,
        ], [
            'branding' => [],
            'policies' => [],
            'smtp_settings' => [],
        ]);

        $currentSmtp = $orgSettings->smtp_settings ?? [];
        $newSmtp = array_merge($currentSmtp, array_filter([
            'mail_driver' => $request->input('mail_driver', 'smtp'),
            'mail_host' => $request->input('mail_host'),
            'mail_port' => $request->input('mail_port'),
            'mail_username' => $request->input('mail_username'),
            'mail_password' => $request->filled('mail_password') ? $request->input('mail_password') : ($currentSmtp['mail_password'] ?? null),
            'mail_encryption' => $request->input('mail_encryption', 'tls'),
            'mail_from_address' => $request->input('mail_from_address'),
            'mail_from_name' => $request->input('mail_from_name'),
        ], function ($val) {
            return !is_null($val);
        }));

        $orgSettings->smtp_settings = $newSmtp;
        $orgSettings->save();

        return redirect('/dashboard#settings')->with('success', __('Workspace settings, company logo, and SMTP email configuration updated successfully!'));
    }

    /**
     * Apply organization SMTP settings dynamically to Laravel mailer.
     */
    protected function applyOrganizationSmtp($organization): void
    {
        $smtp = $organization->settings?->smtp_settings;
        if (!empty($smtp['mail_host'])) {
            config([
                'mail.default' => 'smtp',
                'mail.mailers.smtp.host' => $smtp['mail_host'],
                'mail.mailers.smtp.port' => (int)($smtp['mail_port'] ?? 587),
                'mail.mailers.smtp.encryption' => !empty($smtp['mail_encryption']) && $smtp['mail_encryption'] !== 'none' ? $smtp['mail_encryption'] : null,
                'mail.mailers.smtp.username' => $smtp['mail_username'] ?? null,
                'mail.mailers.smtp.password' => $smtp['mail_password'] ?? null,
                'mail.from.address' => $smtp['mail_from_address'] ?? config('mail.from.address'),
                'mail.from.name' => $smtp['mail_from_name'] ?? $organization->name,
            ]);
        }
    }

    /**
     * Store and schedule a new meeting (Project Meeting or General Administration Meeting).
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
        return redirect("/dashboard#{$tabRedirect}")->with('success', __('Meeting ":title" scheduled successfully!', ['title' => $meeting->title]) . $emailMsg);
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

    /**
     * Test SMTP Mail Server connection and dispatch a test email.
     */
    public function testSmtpConnection(Request $request)
    {
        $user = Auth::user();
        $membership = OrganizationMember::where('user_id', $user->id)->first();
        if (!$membership) abort(403);
        $organization = $membership->organization;

        $validated = $request->validate([
            'mail_host' => 'required|string',
            'mail_port' => 'required|numeric',
            'mail_username' => 'nullable|string',
            'mail_password' => 'nullable|string',
            'mail_encryption' => 'nullable|string',
            'mail_from_address' => 'required|email',
            'mail_from_name' => 'nullable|string',
        ]);

        config([
            'mail.default' => 'smtp',
            'mail.mailers.smtp.host' => $validated['mail_host'],
            'mail.mailers.smtp.port' => (int)$validated['mail_port'],
            'mail.mailers.smtp.encryption' => !empty($validated['mail_encryption']) && $validated['mail_encryption'] !== 'none' ? $validated['mail_encryption'] : null,
            'mail.mailers.smtp.username' => $validated['mail_username'] ?? null,
            'mail.mailers.smtp.password' => $validated['mail_password'] ?? null,
            'mail.from.address' => $validated['mail_from_address'],
            'mail.from.name' => $validated['mail_from_name'] ?? $organization->name,
        ]);

        try {
            Mail::raw("Hello {$user->name},\n\nThis is a test email confirming that your SMTP settings for {$organization->name} on vMeeting Virtual Workplace are configured and working properly!\n\nDelivered at: " . now(), function ($msg) use ($user, $validated, $organization) {
                $msg->to($user->email)
                    ->subject("✅ [SMTP Test] Successful connection on {$organization->name}");
            });

            return response()->json([
                'success' => true,
                'message' => __('SMTP Connection Successful! Test email delivered to :email', ['email' => $user->email]),
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => __('SMTP Connection Failed: ') . $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Update user personal profile, professional details, avatar image, hobbies, skills, social links, and notes.
     */
    public function updateProfile(Request $request)
    {
        $user = Auth::user();
        $membership = OrganizationMember::where('user_id', $user->id)->first();
        if (!$membership) abort(403);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'nickname' => 'nullable|string|max:100',
            'email' => 'required|email|max:255|unique:users,email,' . $user->id,
            'date_of_birth' => 'nullable|date',
            'phone' => 'nullable|string|max:50',
            'job_title' => 'nullable|string|max:150',
            'work_mode' => 'nullable|string|in:remote,hybrid,onsite',
            'bio' => 'nullable|string|max:1000',
            'hobbies' => 'nullable|string|max:1000',
            'skills' => 'nullable|string|max:1000',
            'notes' => 'nullable|string|max:2000',
            'linkedin' => 'nullable|string|max:255',
            'github' => 'nullable|string|max:255',
            'twitter' => 'nullable|string|max:255',
            'website' => 'nullable|string|max:255',
            'avatar' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp|max:4096',
        ]);

        // 1. Update user fields
        $user->name = $validated['name'];
        $user->nickname = $validated['nickname'] ?? null;
        $user->email = $validated['email'];

        // Handle avatar upload
        if ($request->hasFile('avatar')) {
            $file = $request->file('avatar');
            $filename = 'user_' . $user->id . '_' . time() . '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs('avatars', $filename, 'public');
            $user->avatar_url = '/storage/' . $path;
        }

        $user->save();

        // 2. Update user profile
        $profile = \App\Domains\People\Models\UserProfile::firstOrNew([
            'user_id' => $user->id,
            'organization_id' => $membership->organization_id,
        ]);

        $profile->job_title = $validated['job_title'] ?? null;
        $profile->phone = $validated['phone'] ?? null;
        $profile->date_of_birth = $validated['date_of_birth'] ?? null;
        $profile->work_mode = $validated['work_mode'] ?? 'remote';
        $profile->bio = $validated['bio'] ?? null;
        $profile->hobbies = $validated['hobbies'] ?? null;
        $profile->skills = $validated['skills'] ?? null;
        $profile->notes = $validated['notes'] ?? null;

        $socialLinks = [
            'linkedin' => $validated['linkedin'] ?? '',
            'github' => $validated['github'] ?? '',
            'twitter' => $validated['twitter'] ?? '',
            'website' => $validated['website'] ?? '',
        ];
        $profile->social_links = array_filter($socialLinks);

        $profile->save();

        return redirect('/dashboard#profile')->with('success', __('Your personal profile and details have been updated successfully!'));
    }

    /**
     * Update user account security / password.
     */
    public function updatePassword(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'current_password' => 'required|current_password',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user->password = Hash::make($validated['password']);
        $user->save();

        return redirect('/dashboard#profile')->with('success', __('Your password has been changed successfully!'));
    }

    /**
     * List all persistent documents and files for a specific room.
     */
    public function listRoomFiles(\App\Domains\Tenancy\Models\Organization $organization, \App\Domains\Workspace\Models\Room $room)
    {
        $files = \App\Domains\Workspace\Models\RoomFile::where('organization_id', $organization->id)
            ->where('room_id', $room->id)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'files' => $files,
        ]);
    }

    /**
     * Upload a persistent document or file to a specific room.
     */
    public function uploadRoomFile(Request $request, \App\Domains\Tenancy\Models\Organization $organization, \App\Domains\Workspace\Models\Room $room)
    {
        $request->validate([
            'file' => 'required|file|max:51200', // max 50MB
        ]);

        $uploadedFile = $request->file('file');
        $originalName = $uploadedFile->getClientOriginalName();
        $mime = $uploadedFile->getMimeType();
        $size = $uploadedFile->getSize();
        $filename = 'room_file_' . \Illuminate\Support\Str::uuid() . '.' . ($uploadedFile->getClientOriginalExtension() ?: 'bin');
        $path = $uploadedFile->storeAs("public/room_files/{$organization->id}/{$room->id}", $filename);
        $url = \Illuminate\Support\Facades\Storage::url($path);

        $user = Auth::user();

        $roomFile = \App\Domains\Workspace\Models\RoomFile::create([
            'organization_id' => $organization->id,
            'room_id' => $room->id,
            'uploaded_by_user_id' => $user?->id,
            'uploader_name' => $user?->name ?: 'Team Member',
            'name' => $originalName,
            'file_path' => $path,
            'file_url' => $url,
            'file_size' => $size,
            'mime_type' => $mime,
        ]);

        return response()->json([
            'message' => 'File uploaded successfully.',
            'file' => $roomFile,
        ], 201);
    }

    /**
     * Delete a persistent file from a room.
     */
    public function deleteRoomFile(\App\Domains\Tenancy\Models\Organization $organization, \App\Domains\Workspace\Models\Room $room, \App\Domains\Workspace\Models\RoomFile $file)
    {
        if ($file->organization_id !== $organization->id || $file->room_id !== $room->id) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        \Illuminate\Support\Facades\Storage::delete($file->file_path);
        $file->delete();

        return response()->json([
            'message' => 'File deleted successfully.',
        ]);
    }

    /**
     * Upload an attachment for office/room chat.
     */
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
    public function memberActivity(Request $request, $userId)
    {
        $viewer = Auth::user();
        $viewerMembership = OrganizationMember::where('user_id', $viewer->id)->first();
        if (!$viewerMembership) return response()->json(['message' => 'Unauthorized'], 403);

        $orgId = $viewerMembership->organization_id;
        $targetUser = \App\Domains\Identity\Models\User::with(['profile.department', 'profile.team'])->findOrFail($userId);
        $targetMembership = OrganizationMember::where('user_id', $targetUser->id)
            ->where('organization_id', $orgId)
            ->with(['role'])
            ->first();

        if (!$targetMembership) {
            return response()->json(['message' => 'Member not found in organization'], 404);
        }

        // Active timer (what they are working on right now)
        $activeTimer = \App\Domains\Projects\Models\TimeEntry::where('user_id', $targetUser->id)
            ->where('organization_id', $orgId)
            ->whereNull('ended_at')
            ->with(['task', 'project'])
            ->latest('started_at')
            ->first();

        // Tasks assigned to this user
        $tasks = \App\Domains\Projects\Models\Task::where('organization_id', $orgId)
            ->where(function($q) use ($targetUser) {
                $q->where('assignee_id', $targetUser->id);
            })
            ->with(['project'])
            ->latest()
            ->take(15)
            ->get();

        return response()->json([
            'user' => [
                'id' => $targetUser->id,
                'name' => $targetUser->name,
                'email' => $targetUser->email,
                'avatar_url' => $targetUser->avatar_url,
                'role_name' => $targetMembership->role?->name ?? 'Member',
                'job_title' => $targetUser->profile?->job_title ?? __('Team Member'),
                'department' => $targetUser->profile?->department?->name,
                'team' => $targetUser->profile?->team?->name,
                'status' => $targetMembership->status,
            ],
            'active_timer' => $activeTimer ? [
                'id' => $activeTimer->id,
                'project_name' => $activeTimer->project?->name ?? 'General Work',
                'task_title' => $activeTimer->task?->title ?? ($activeTimer->description ?? 'Focused Work Session'),
                'started_at' => $activeTimer->started_at?->toISOString(),
                'duration_seconds' => $activeTimer->started_at ? now()->diffInSeconds($activeTimer->started_at) : 0,
            ] : null,
            'tasks' => $tasks->map(function($t) {
                return [
                    'id' => $t->id,
                    'title' => $t->title,
                    'status' => $t->status,
                    'priority' => $t->priority ?? 'medium',
                    'project_name' => $t->project?->name ?? 'Main',
                    'due_date' => $t->due_date ? $t->due_date->format('Y-m-d') : null,
                ];
            }),
        ]);
    }

    /**
     * Log Room and Floor presence intervals for time & attendance tracking.
     */
    public function logRoomAttendance(Request $request)
    {
        $user = Auth::user();
        $membership = OrganizationMember::where('user_id', $user->id)->first();
        if (!$membership) return response()->json(['message' => 'Unauthorized'], 403);

        $validated = $request->validate([
            'room_id' => 'nullable|uuid|exists:rooms,id',
            'action' => 'required|in:enter,leave,heartbeat',
            'duration_seconds' => 'nullable|integer',
        ]);

        \App\Domains\Administration\Models\AuditLog::create([
            'organization_id' => $membership->organization_id,
            'user_id' => $user->id,
            'action' => 'room.' . $validated['action'],
            'target_type' => 'room',
            'target_id' => $validated['room_id'] ?? null,
            'metadata' => [
                'user_name' => $user->name,
                'room_id' => $validated['room_id'] ?? null,
                'duration_seconds' => $validated['duration_seconds'] ?? 0,
                'timestamp' => now()->toISOString(),
            ],
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return response()->json(['status' => 'logged']);
    }

    /**
     * Logout.
     */
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}




