<?php

namespace App\Domains\Tenancy\Actions;

use App\Domains\Administration\Models\AuditLog;
use App\Domains\Administration\Models\Role;
use App\Domains\Guests\Models\GuestInvitation;
use App\Domains\Identity\Models\User;
use App\Domains\Meetings\Models\Meeting;
use App\Domains\People\Models\Department;
use App\Domains\People\Models\Team;
use App\Domains\People\Models\UserProfile;
use App\Domains\Projects\Models\ActiveTimer;
use App\Domains\Tenancy\Models\Organization;
use App\Domains\Tenancy\Models\OrganizationMember;
use App\Domains\Tenancy\Models\OrganizationSetting;
use App\Domains\Tenancy\Models\Plan;
use Carbon\Carbon;
use Database\Seeders\BlueprintOfficeSeeder;
use Illuminate\Support\Facades\Log;

/**
 * Assembles every piece of data the main organization dashboard view needs:
 * stats, rooms/offices, members, projects/tasks, meetings, and settings.
 * Extracted verbatim from DashboardController::dashboard(), which had grown
 * to ~190 lines of view-data aggregation. See Architecture Audit §8/§15.
 */
class BuildOrganizationDashboardAction
{
    public function execute(User $user, OrganizationMember $membership): array
    {
        if ($membership->status === 'invited') {
            $membership->update(['status' => 'active']);
        }

        $organization = $membership->organization;
        $this->ensureDefaultWorkspace($organization);

        $rooms = $organization->rooms()->with(['floor', 'map.floor'])->get();
        $offices = $organization->offices()->with(['rooms', 'maps.rooms', 'activeMap.rooms'])->get();
        $roles = Role::where('slug', '!=', 'super_admin')
            ->where(function ($q) use ($organization) {
                $q->where('organization_id', $organization->id)->orWhereNull('organization_id');
            })->get();
        $members = $organization->members()
            ->whereHas('user', function ($q) {
                $q->where('is_super_admin', false);
            })
            ->with(['user.profiles', 'role', 'offices', 'rooms'])
            ->get();
        $departments = $organization->departments()->withCount('teams')->get();
        $teams = $organization->teams()->with('department')->get();
        $auditLogs = AuditLog::where('organization_id', $organization->id)->latest()->take(20)->get();
        $guestInvitations = GuestInvitation::where('organization_id', $organization->id)->with('room')->latest()->take(20)->get();
        $allPlans = Plan::where('is_active', true)->orderBy('price', 'asc')->get();

        $activeMembersCount = $members->where('status', 'active')->count();
        $invitedMembersCount = $members->where('status', 'invited')->count();
        $totalMembers = $activeMembersCount + $invitedMembersCount;
        $totalDepts = $departments->count();
        $totalTeams = $teams->count();
        $totalRooms = $rooms->count();
        $totalGuests = $guestInvitations->count();
        $totalAudit = $auditLogs->count();

        // Calculate actual dynamic metrics
        $todayMeetingsCount = 0;
        $totalMeetingsCount = 0;
        $totalTrackedSeconds = 0;
        $collaborationHours = 0;
        $occupancyRate = 0;
        $productivityScore = 100.0;
        $presenceRate = 100;

        try {
            $todayMeetingsCount = Meeting::where('organization_id', $organization->id)
                ->whereDate('scheduled_at', Carbon::today())
                ->count();
            $totalMeetingsCount = Meeting::where('organization_id', $organization->id)->count();

            $totalTrackedSeconds = (int) ($organization->timeEntries()->sum('duration_seconds') ?? 0);
            $collaborationHours = round($totalTrackedSeconds / 3600, 1);
            if ($collaborationHours == 0 && $todayMeetingsCount > 0) {
                $collaborationHours = round($todayMeetingsCount * 1.5, 1);
            }

            $inUseRoomsCount = $rooms->filter(function ($r) {
                return ($r->members_count ?? 0) > 0 || ($r->active_call ?? false);
            })->count();
            $occupancyRate = $totalRooms > 0 ? round(($inUseRoomsCount / $totalRooms) * 100) : 0;

            $totalTasksCount = $organization->tasks()->count();
            $completedTasksCount = $organization->tasks()->where('status', 'done')->count();
            $productivityScore = $totalTasksCount > 0 ? round(($completedTasksCount / $totalTasksCount) * 100, 1) : 100.0;

            $presenceRate = $totalMembers > 0 ? round(($activeMembersCount / max(1, $totalMembers)) * 100) : 100;
        } catch (\Throwable $e) {
            Log::warning('DashboardController dynamic stats calculation: '.$e->getMessage());
        }

        $stats = [
            'members' => $totalMembers,
            'active_members' => $activeMembersCount,
            'invited_members' => $invitedMembersCount,
            'departments' => $totalDepts,
            'teams' => $totalTeams,
            'rooms' => $totalRooms,
            'guests' => $totalGuests,
            'presence_rate' => $presenceRate,
            'meetings_count' => $todayMeetingsCount > 0 ? $todayMeetingsCount : $totalMeetingsCount,
            'today_meetings' => $todayMeetingsCount,
            'collaboration_hours' => $collaborationHours,
            'occupancy_rate' => $occupancyRate,
            'productivity_score' => $productivityScore,
            'screen_share_rate' => 95,
            'audio_quality' => '100%',
        ];

        // Project Management entities
        $projects = $organization->projects()->with(['owner', 'manager', 'department'])->withCount('tasks')->latest()->get();
        $tasks = $organization->tasks()->with(['project', 'assignee', 'reporter', 'phase', 'milestone'])->orderBy('order')->latest()->get();
        $myTasks = $tasks->where('assignee_id', $user->id)->values();
        $activeTimer = ActiveTimer::where('user_id', $user->id)->with(['project', 'task'])->first();
        $recentTimeEntries = $organization->timeEntries()->where('user_id', $user->id)->with(['project', 'task'])->latest()->take(30)->get();
        $allTimesheets = $organization->timesheets()->with(['user', 'reviewer'])->latest()->take(15)->get();
        $currentMember = $members->firstWhere('user_id', $user->id);
        $myProfile = $currentMember?->user?->profiles?->first() ?? new UserProfile([
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
        $attendancePolicy = $organization->settings?->getAttendancePolicy() ?? OrganizationSetting::getAttendancePolicy();

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

        $tasksByProject = $tasks->groupBy('project_id');
        $projectMembersMap = [];
        foreach ($projects as $p) {
            $pMembers = collect();
            if ($p->owner) {
                $pMembers->push($p->owner);
            }
            if ($p->manager) {
                $pMembers->push($p->manager);
            }
            $pTasks = $tasksByProject->get($p->id, collect());
            $pTaskUserIds = $pTasks->whereNotNull('assignee_id')->pluck('assignee_id')->unique();
            $pTaskUsers = $members->whereIn('user_id', $pTaskUserIds)->pluck('user');
            $pMembers = $pMembers->concat($pTaskUsers)->filter()->unique('id');
            $projectMembersMap[$p->id] = $pMembers->map(fn ($pm) => [
                'id' => $pm->id,
                'name' => $pm->name,
                'email' => $pm->email,
            ])->values()->all();
        }

        $pendingSubscriptionRequest = $organization->pendingSubscriptionRequest()->with('plan')->first();

        return [
            'user' => $user,
            'membership' => $membership,
            'organization' => $organization,
            'stats' => $stats,
            'rooms' => $rooms,
            'offices' => $offices,
            'roles' => $roles,
            'members' => $members,
            'departments' => $departments,
            'teams' => $teams,
            'auditLogs' => $auditLogs,
            'guestInvitations' => $guestInvitations,
            'allPlans' => $allPlans,
            'projects' => $projects,
            'tasks' => $tasks,
            'myTasks' => $myTasks,
            'activeTimer' => $activeTimer,
            'recentTimeEntries' => $recentTimeEntries,
            'allTimesheets' => $allTimesheets,
            'myProfile' => $myProfile,
            'upcomingMeetings' => $upcomingMeetings,
            'allMeetings' => $allMeetings,
            'smtpSettings' => $smtpSettings,
            'openAiSettings' => $openAiSettings,
            'upcomingMeetingsJson' => $upcomingMeetingsJson,
            'pendingSubscriptionRequest' => $pendingSubscriptionRequest,
            'attendancePolicy' => $attendancePolicy,
            'projectMembersMap' => $projectMembersMap,
        ];
    }

    /**
     * Guarantee default floor, map, and blueprint layout, plus a default
     * department/team structure, exist for a freshly created organization.
     */
    private function ensureDefaultWorkspace(Organization $organization): void
    {
        if ($organization->floors()->count() === 0) {
            $seeder = new BlueprintOfficeSeeder;
            $seeder->seedOrganizationOffice($organization);
        }

        if ($organization->departments()->count() === 0) {
            $eng = Department::create([
                'organization_id' => $organization->id,
                'name' => 'Engineering & Technology',
            ]);
            Team::create(['organization_id' => $organization->id, 'department_id' => $eng->id, 'name' => 'Frontend Team']);
            Team::create(['organization_id' => $organization->id, 'department_id' => $eng->id, 'name' => 'Backend & Cloud']);

            $sales = Department::create([
                'organization_id' => $organization->id,
                'name' => 'Sales & Business Growth',
            ]);
            Team::create(['organization_id' => $organization->id, 'department_id' => $sales->id, 'name' => 'Enterprise Sales']);

            $design = Department::create([
                'organization_id' => $organization->id,
                'name' => 'Product & Design',
            ]);
            Team::create(['organization_id' => $organization->id, 'department_id' => $design->id, 'name' => 'UI / UX Design']);
        }
    }
}
