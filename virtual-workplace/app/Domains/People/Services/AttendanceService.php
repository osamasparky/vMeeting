<?php

namespace App\Domains\People\Services;

use App\Domains\Identity\Models\User;
use App\Domains\People\Models\AttendanceSession;
use App\Domains\Projects\Models\ActiveTimer;
use App\Domains\Projects\Models\Task;
use App\Domains\Projects\Models\TimeEntry;
use App\Domains\Tenancy\Models\Organization;
use App\Domains\Tenancy\Models\OrganizationMember;
use App\Domains\Workspace\Services\OfficeTimezone;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class AttendanceService
{
    /**
     * Stale heartbeat threshold in minutes.
     */
    protected int $staleTimeoutMinutes = 10;

    public function __construct(protected OfficeTimezone $timezones) {}

    /**
     * Start (or continue) the user's attendance session in a room. Any other
     * session still open for the user — another room, or a duplicate opened by
     * a concurrent request — is closed, so a user is never in two places at once.
     *
     * The session records its office: the room's, or for the open space the
     * office the client reports ($floorId), so its hours count in that office's time.
     */
    public function startSession(
        User $user,
        Organization $organization,
        ?string $roomId = null,
        ?string $ip = null,
        ?string $userAgent = null,
        ?string $floorId = null
    ): AttendanceSession {
        return DB::transaction(function () use ($user, $organization, $roomId, $ip, $userAgent, $floorId) {
            $this->lockUser($user->id);
            $this->cleanupStaleSessions($user->id);

            $officeId = $this->timezones->resolveOfficeId($organization->id, $roomId, $floorId);
            $open = $this->openSessions($user->id, $organization->id)->get();
            $keep = $open->first(fn ($s) => $s->room_id === $roomId && $s->floor_id === $officeId);

            foreach ($open as $session) {
                if ($session !== $keep) {
                    $session->close('completed');
                }
            }

            if ($keep) {
                $keep->update([
                    'last_heartbeat_at' => now(),
                    'duration_seconds' => $this->secondsBetween($keep->started_at, now()),
                ]);

                return $keep;
            }

            return AttendanceSession::create([
                'organization_id' => $organization->id,
                'user_id' => $user->id,
                'room_id' => $roomId,
                'floor_id' => $officeId,
                'status' => 'active',
                'started_at' => now(),
                'last_heartbeat_at' => now(),
                'ip_address' => $ip,
                'user_agent' => $userAgent,
            ]);
        });
    }

    /**
     * End the user's attendance. With a room id this is a room exit, not an
     * office exit: the user is still in the office (open space), so presence
     * carries on in a room-less session instead of stopping until the next
     * heartbeat. If that room's session is already closed — the client's
     * "enter" for the next room got here first — nothing happens.
     */
    public function endSession(User $user, Organization $organization, ?string $roomId = null): ?AttendanceSession
    {
        return DB::transaction(function () use ($user, $organization, $roomId) {
            $this->lockUser($user->id);

            $query = $this->openSessions($user->id, $organization->id);
            if ($roomId) {
                $query->where('room_id', $roomId);
            }

            $sessions = $query->get();
            $sessions->each(fn ($s) => $s->close('completed'));

            if ($roomId && $sessions->isNotEmpty()) {
                AttendanceSession::create([
                    'organization_id' => $organization->id,
                    'user_id' => $user->id,
                    'room_id' => null,
                    'floor_id' => $sessions->first()->floor_id,
                    'status' => 'active',
                    'started_at' => now(),
                    'last_heartbeat_at' => now(),
                    'ip_address' => $sessions->first()->ip_address,
                    'user_agent' => $sessions->first()->user_agent,
                ]);
            }

            return $sessions->first();
        });
    }

    /**
     * Keep the active session alive. The duration is always measured on the
     * server from started_at; a client-reported duration is not trusted.
     *
     * Stale sessions are closed first: a heartbeat arriving after the laptop
     * slept or the tab was suspended must start a new session, not stretch the
     * old one across the gap.
     */
    public function recordHeartbeat(User $user, Organization $organization, ?string $roomId = null, ?string $floorId = null): ?AttendanceSession
    {
        $session = DB::transaction(function () use ($user, $organization) {
            $this->lockUser($user->id);
            $this->cleanupStaleSessions($user->id);

            $session = $this->openSessions($user->id, $organization->id)->first();
            $session?->update([
                'last_heartbeat_at' => now(),
                'duration_seconds' => $this->secondsBetween($session->started_at, now()),
            ]);

            return $session;
        });

        // Heartbeat without an open session (first heartbeat after a gap): start one.
        $session ??= $this->startSession($user, $organization, $roomId, null, null, $floorId);

        // Background maintenance
        $this->cleanupStaleSessions();

        return $session;
    }

    /**
     * Close sessions whose heartbeat stopped. They end at the last heartbeat —
     * the last moment the user was known to be there.
     */
    public function cleanupStaleSessions(?string $userId = null): int
    {
        $threshold = now()->subMinutes($this->staleTimeoutMinutes);

        $query = AttendanceSession::where('status', 'active')
            ->whereNull('ended_at')
            ->where(fn ($q) => $q->where('last_heartbeat_at', '<', $threshold)
                ->orWhere(fn ($q) => $q->whereNull('last_heartbeat_at')->where('started_at', '<', $threshold)));

        if ($userId) {
            $query->where('user_id', $userId);
        }

        $count = 0;
        foreach ($query->get() as $session) {
            $end = $session->last_heartbeat_at && $session->last_heartbeat_at->gt($session->started_at)
                ? $session->last_heartbeat_at
                : $session->started_at;
            $session->close('timed_out', $end);
            $count++;
        }

        return $count;
    }

    /**
     * Office-presence report for one user over a period, with a per-day
     * breakdown. The period follows the member's office time (the office they
     * were last in); each session counts on its own office's calendar day, and
     * sessions crossing midnight are split between the days they cover.
     */
    public function getUserReport(string $userId, ?string $organizationId = null, string $period = 'week'): array
    {
        $this->cleanupStaleSessions($userId);

        $tz = $organizationId ? $this->timezones->forMember($userId, $organizationId) : config('app.timezone');
        $now = now()->setTimezone($tz);
        $startDate = match ($period) {
            'today', 'day' => $now->copy()->startOfDay(),
            'month' => $now->copy()->startOfMonth(),
            default => $now->copy()->startOfWeek(),
        };

        $query = $this->sessionsOverlapping($this->db($startDate)->subDay(), now()->addDay())
            ->where('user_id', $userId)
            ->with(['room:id,name', 'organization:id,name']);

        if ($organizationId) {
            $query->where('organization_id', $organizationId);
        }

        $sessions = $query->orderBy('started_at', 'desc')->get();

        $daily = [];
        $totalSeconds = 0;
        $todaySeconds = 0;
        for ($day = $startDate->copy()->startOfDay(); $day->lte($now); $day->addDay()) {
            $date = $day->format('Y-m-d');
            $clipped = $this->officeIntervalsOn($sessions, $date);
            if ($clipped === []) {
                continue;
            }
            $seconds = TimeIntervals::union($clipped);
            $totalSeconds += $seconds;
            if ($date === $now->format('Y-m-d')) {
                $todaySeconds = $seconds;
            }
            $daily[] = [
                'date' => $date,
                'day_name' => $day->format('l'),
                'duration_seconds' => $seconds,
                'hours' => round($seconds / 3600, 2),
                'session_count' => count($clipped),
            ];
        }

        return [
            'period' => $period,
            'timezone' => $tz,
            'start_date' => $startDate->toIso8601String(),
            'end_date' => $now->toIso8601String(),
            'total_seconds' => $totalSeconds,
            'today_total_seconds' => $todaySeconds,
            'total_hours' => round($totalSeconds / 3600, 2),
            'sessions_count' => $sessions->count(),
            'daily_breakdown' => array_reverse($daily),
            'recent_sessions' => $sessions->take(15)->map(function ($s) {
                [$start, $end] = $this->sessionInterval($s);

                return [
                    'id' => $s->id,
                    'room_name' => $s->room?->name ?? 'Open Space',
                    'started_at' => $s->started_at->toIso8601String(),
                    'ended_at' => $s->ended_at?->toIso8601String(),
                    'duration_seconds' => $end - $start,
                    'status' => $s->status,
                ];
            }),
        ];
    }

    /**
     * Pause the session after the idle prompt went unanswered. The user stopped
     * being active $idleSeconds ago (prompt delay + grace countdown), so the
     * session ends then — that idle stretch is not office time.
     */
    public function pauseSessionForIdle(User $user, Organization $organization, ?int $idleSeconds = null): ?AttendanceSession
    {
        return DB::transaction(function () use ($user, $organization, $idleSeconds) {
            $this->lockUser($user->id);

            $sessions = $this->openSessions($user->id, $organization->id)->get();
            $lastActive = now()->subSeconds(max(0, (int) $idleSeconds));
            $sessions->each(fn ($s) => $s->pauseForIdle($lastActive));

            return $sessions->first();
        });
    }

    /**
     * Resume user attendance session from idle pause state.
     */
    public function resumeSessionFromIdle(User $user, Organization $organization, ?string $roomId = null, ?string $floorId = null): AttendanceSession
    {
        // Re-start active session
        return $this->startSession($user, $organization, $roomId, null, null, $floorId);
    }

    /**
     * Daily timesheet: task time and office presence for one member, or for
     * every member when $userId is empty / 'all'.
     *
     *  - total_office_seconds:     time present in the office (overlapping sessions counted once)
     *  - total_task_seconds:       time on task timers and logged entries, as timesheets count it
     *  - total_attendance_seconds: time present in the office OR working on a task — the two
     *                              overlap whenever a timer runs in the office, so they are
     *                              unioned, never added
     *  - idle_seconds:             office time with no task running
     *
     * Days are local office days: each session is clipped to the date in its own
     * office's timezone, task time to the date in the member's office timezone
     * (the office they were last in). Without a date, "today" is the member's
     * today — or the default office's in the all-members view.
     */
    public function getDailyTimesheetData(?string $userId, string $organizationId, ?string $date = null): array
    {
        // First cleanup any stale dead sessions to keep data 100% accurate
        $this->cleanupStaleSessions();

        $isAll = empty($userId) || $userId === 'all';

        $tz = $isAll ? $this->timezones->forOrganization($organizationId) : $this->timezones->forMember($userId, $organizationId);
        $targetDate = $date ? Carbon::parse($date, $tz)->startOfDay() : now()->setTimezone($tz)->startOfDay();
        $day = $targetDate->format('Y-m-d');
        [$searchFrom, $searchTo] = $this->searchRange($day);
        $memberTz = fn (string $uid) => $this->timezones->forMember($uid, $organizationId);

        // ── Section 1: Project & Task Time Entries ──
        $taskQuery = $this->entriesOverlapping($searchFrom, $searchTo)
            ->where('organization_id', $organizationId)
            ->with(['user.profiles', 'project:id,name,color,code', 'task:id,title,task_number,priority,status'])
            ->orderBy('started_at', 'desc');

        if (! $isAll) {
            $taskQuery->where('user_id', $userId);
        }

        $taskEntries = $taskQuery->get()->filter(fn ($e) => TimeIntervals::clip(
            [$this->entryInterval($e)], ...$this->dayWindow($day, $memberTz($e->user_id))
        ) !== [])->values();

        // ── Section 2: Virtual Office Attendance Sessions ──
        $attQuery = $this->sessionsOverlapping($searchFrom, $searchTo)
            ->where('organization_id', $organizationId)
            ->with(['user.profiles', 'room', 'room.floor', 'room.map'])
            ->orderBy('started_at', 'desc');

        if (! $isAll) {
            $attQuery->where('user_id', $userId);
        }

        $attendanceSessions = $attQuery->get()
            ->filter(fn ($s) => $this->officeIntervalsOn(collect([$s]), $day) !== [])->values();

        // Running timers (one per user; every member's in the all-members view)
        $activeTimerQuery = ActiveTimer::where('organization_id', $organizationId)
            ->with(['user', 'project:id,name,color', 'task:id,title,task_number']);
        if (! $isAll) {
            $activeTimerQuery->where('user_id', $userId);
        }
        $activeTimers = $activeTimerQuery->get();
        $activeTimerModel = $activeTimers->first();

        $totals = ['office' => 0, 'task' => 0, 'attendance' => 0, 'idle' => 0];
        $userIds = $attendanceSessions->pluck('user_id')
            ->merge($taskEntries->pluck('user_id'))
            ->merge($activeTimers->pluck('user_id'))
            ->unique();
        foreach ($userIds as $uid) {
            $member = $this->dayTotals(
                $attendanceSessions->where('user_id', $uid),
                $taskEntries->where('user_id', $uid),
                $activeTimers->where('user_id', $uid),
                $day,
                $memberTz($uid),
            );
            foreach ($totals as $key => $value) {
                $totals[$key] = $value + $member[$key];
            }
        }

        $activeTaskTimer = null;
        if ($activeTimerModel) {
            $activeTaskTimer = [
                'id' => $activeTimerModel->id,
                'user_id' => $activeTimerModel->user_id,
                'user_name' => $activeTimerModel->user?->name ?? 'Member',
                'task_id' => $activeTimerModel->task_id,
                'project_id' => $activeTimerModel->project_id,
                'task_title' => $activeTimerModel->task?->title ?? 'Task',
                'task_number' => $activeTimerModel->task?->task_number ?? '',
                'project_name' => $activeTimerModel->project?->name ?? 'Project',
                'project_color' => $activeTimerModel->project?->color ?? '#34D399',
                'started_at' => $activeTimerModel->started_at->toIso8601String(),
                'elapsed_seconds' => $activeTimerModel->elapsedSeconds(),
            ];
        }

        // Check if currently active in office
        $activeOfficeQuery = $this->openSessionsQuery()
            ->where('organization_id', $organizationId)
            ->latest('started_at');
        if (! $isAll) {
            $activeOfficeQuery->where('user_id', $userId);
        }
        $activeOfficeSession = $activeOfficeQuery->first();

        // Format task entries with clean human readable duration & timestamps
        $formattedTaskEntries = $taskEntries->map(function ($te) use ($memberTz) {
            $tz = $memberTz($te->user_id);
            [$start, $end] = $this->entryInterval($te);
            $sec = $end - $start;
            $durFormatted = sprintf('%02dh %02dm', floor($sec / 3600), floor(($sec % 3600) / 60));
            if ($sec < 3600) {
                $durFormatted = sprintf('%02dm %02ds', floor($sec / 60), $sec % 60);
            }

            return [
                'id' => $te->id,
                'user_id' => $te->user_id,
                'user_name' => $te->user?->name ?? 'Unknown',
                'user_avatar' => $te->user?->avatar_url,
                'task_title' => $te->task?->title ?? ($te->description ?? 'Focused Work Session'),
                'task_number' => $te->task?->task_number ?? '',
                'project_name' => $te->project?->name ?? 'General',
                'project_color' => $te->project?->color ?? '#34D399',
                'description' => $te->description,
                'started_at' => $te->started_at ? $te->started_at->copy()->setTimezone($tz)->format('h:i A') : '—',
                'ended_at' => $te->ended_at ? $te->ended_at->copy()->setTimezone($tz)->format('h:i A') : ($te->started_at ? 'Live Now' : '—'),
                'duration_seconds' => $sec,
                'duration_formatted' => $durFormatted,
                'is_billable' => (bool) $te->is_billable,
                'status' => $te->status ?? 'completed',
            ];
        });

        // Format attendance sessions with room name, branch name, timestamps, and formatted durations
        $formattedAttendanceSessions = $attendanceSessions->map(function ($s) {
            $tz = $this->timezones->forSession($s);
            [$start, $end] = $this->sessionInterval($s);
            $sec = $end - $start;

            $durFormatted = sprintf('%02dh %02dm %02ds', floor($sec / 3600), floor(($sec % 3600) / 60), $sec % 60);
            if ($sec < 3600) {
                $durFormatted = sprintf('%02dm %02ds', floor($sec / 60), $sec % 60);
            }

            $roomName = $s->room?->name ?? 'General Space';
            $branchName = $s->room?->floor?->name ?? ($s->room?->map?->floor?->name ?? 'Main Office');

            return [
                'id' => $s->id,
                'user_id' => $s->user_id,
                'user_name' => $s->user?->name ?? 'Unknown',
                'user_avatar' => $s->user?->avatar_url,
                'branch_name' => $branchName,
                'room_name' => $roomName,
                'room_id' => $s->room_id,
                'check_in' => $s->started_at ? $s->started_at->copy()->setTimezone($tz)->format('h:i:s A') : '—',
                'check_out' => $s->ended_at ? $s->ended_at->copy()->setTimezone($tz)->format('h:i:s A') : null,
                'started_at' => $s->started_at ? $s->started_at->toIso8601String() : null,
                'ended_at' => $s->ended_at ? $s->ended_at->toIso8601String() : null,
                'duration_seconds' => $sec,
                'duration_formatted' => $durFormatted,
                'status' => $s->status,
                'is_active' => $s->isActive(),
                'timezone' => $tz,
            ];
        });

        return [
            'is_all_members' => $isAll,
            'timezone' => $tz,
            'date' => $day,
            'date_formatted' => $targetDate->isoFormat('dddd, D MMMM YYYY'),
            'total_office_seconds' => $totals['office'],
            'total_office_hours' => round($totals['office'] / 3600, 2),
            'total_office_formatted' => $this->hms($totals['office']),
            'total_task_seconds' => $totals['task'],
            'total_task_hours' => round($totals['task'] / 3600, 2),
            'total_task_formatted' => $this->hms($totals['task']),
            'total_attendance_seconds' => $totals['attendance'],
            'total_attendance_hours' => round($totals['attendance'] / 3600, 2),
            'total_attendance_formatted' => $this->hms($totals['attendance']),
            'idle_seconds' => $totals['idle'],
            'is_in_office' => (bool) $activeOfficeSession,
            'has_running_task' => (bool) $activeTaskTimer,
            'active_task_timer' => $activeTaskTimer,
            'active_timer' => $activeTaskTimer,
            'task_entries' => $formattedTaskEntries,
            'attendance_sessions' => $formattedAttendanceSessions,
        ];
    }

    /**
     * Build the in-office "user spotlight" activity card for a member: their
     * current active task/timer and assigned tasks, with a strict privacy
     * rule that guest viewers never see internal task/timer detail.
     */
    public function getMemberActivitySnapshot(User $targetUser, ?OrganizationMember $targetMembership, ?string $effectiveOrgId, bool $isGuest): array
    {
        $activeTimerData = null;
        $tasks = collect([]);

        if (! $isGuest && $effectiveOrgId) {
            $activeTimer = ActiveTimer::where('organization_id', $effectiveOrgId)
                ->where('user_id', $targetUser->id)
                ->with(['project', 'task'])
                ->first();

            if ($activeTimer) {
                $activeTimerData = [
                    'id' => $activeTimer->id,
                    'project_name' => $activeTimer->project?->name ?? 'General Work',
                    'task_title' => $activeTimer->task ? ('#'.$activeTimer->task->task_number.' '.$activeTimer->task->title) : 'Focused Work Session',
                    'task_number' => $activeTimer->task?->task_number ?? '',
                    'started_at' => $activeTimer->started_at?->toIso8601String(),
                    'duration_seconds' => $activeTimer->elapsedSeconds(),
                ];
            } else {
                $openEntry = TimeEntry::where('user_id', $targetUser->id)
                    ->where('organization_id', $effectiveOrgId)
                    ->whereNull('ended_at')
                    ->with(['task', 'project'])
                    ->latest('started_at')
                    ->first();
                if ($openEntry) {
                    $activeTimerData = [
                        'id' => $openEntry->id,
                        'project_name' => $openEntry->project?->name ?? 'General Work',
                        'task_title' => $openEntry->task?->title ?? ($openEntry->description ?? 'Focused Work Session'),
                        'task_number' => $openEntry->task?->task_number ?? '',
                        'started_at' => $openEntry->started_at?->toIso8601String(),
                        'duration_seconds' => $openEntry->started_at ? (int) $openEntry->started_at->diffInSeconds(now()) : 0,
                    ];
                }
            }

            $tasks = Task::where('organization_id', $effectiveOrgId)
                ->assignedTo($targetUser->id)
                ->whereNotIn('status', ['done', 'completed', 'cancelled'])
                ->with(['project'])
                ->orderBy('priority', 'desc')
                ->orderBy('due_date', 'asc')
                ->take(15)
                ->get();
        }

        $deptName = '';
        if ($targetUser->profile) {
            if (is_object($targetUser->profile->department)) {
                $deptName = $targetUser->profile->department->name ?? '';
            } elseif (is_string($targetUser->profile->department)) {
                $deptName = $targetUser->profile->department;
            }
        }

        $teamName = '';
        if ($targetUser->profile) {
            if (is_object($targetUser->profile->team)) {
                $teamName = $targetUser->profile->team->name ?? '';
            } elseif (is_string($targetUser->profile->team)) {
                $teamName = $targetUser->profile->team;
            }
        }

        return [
            'user' => [
                'id' => $targetUser->id,
                'name' => $targetUser->name,
                'email' => $isGuest ? null : $targetUser->email,
                'avatar_url' => $targetUser->avatar_url,
                'role_name' => $targetMembership?->role?->name ?? 'Member',
                'job_title' => $targetMembership?->job_title ?? $targetUser->profile?->job_title ?? __('Team Member'),
                'department' => $deptName,
                'team' => $teamName,
                'status' => $targetMembership?->status ?? 'active',
            ],
            'is_guest_viewer' => $isGuest,
            'active_timer' => $activeTimerData,
            'tasks' => $tasks->map(function ($t) {
                return [
                    'id' => $t->id,
                    'title' => $t->title,
                    'task_number' => $t->task_number,
                    'status' => $t->status,
                    'priority' => $t->priority ?? 'medium',
                    'project_name' => $t->project?->name ?? 'Main',
                    'due_date' => $t->due_date ? $t->due_date->format('Y-m-d') : null,
                ];
            }),
        ];
    }

    /**
     * Build the live team-presence roster: who's online, where, and their
     * office + task time totals for today, across the whole organization.
     */
    public function getTeamPresenceOverview(Organization $organization): array
    {
        $this->cleanupStaleSessions();

        // Each member's "today" is their office's today; fetch wide, clip per member.
        [$searchFrom, $searchTo] = [now()->subDays(2), now()->addDay()];

        $members = $organization->members()
            ->whereHas('user', function ($q) {
                $q->where('is_super_admin', false);
            })
            ->with(['user.profiles', 'role'])
            ->get();

        $activeSessions = $this->openSessionsQuery()
            ->where('organization_id', $organization->id)
            ->with(['room', 'room.floor', 'room.map'])
            ->orderBy('started_at')
            ->get()
            ->keyBy('user_id');

        $todaySessions = $this->sessionsOverlapping($searchFrom, $searchTo)
            ->where('organization_id', $organization->id)
            ->get()
            ->groupBy('user_id');

        $todayTaskEntries = $this->entriesOverlapping($searchFrom, $searchTo)
            ->where('organization_id', $organization->id)
            ->get()
            ->groupBy('user_id');

        $activeTimers = ActiveTimer::where('organization_id', $organization->id)
            ->with(['project:id,name', 'task:id,title,task_number'])
            ->get()
            ->keyBy('user_id');

        $roster = $members->map(function ($m) use ($organization, $activeSessions, $todaySessions, $todayTaskEntries, $activeTimers) {
            $u = $m->user;
            if (! $u) {
                return null;
            }

            $activeSession = $activeSessions->get($u->id);
            $isOnline = (bool) $activeSession;
            $activeTimer = $activeTimers->get($u->id);
            $tz = $this->timezones->forMember($u->id, $organization->id);

            $day = $this->dayTotals(
                $todaySessions->get($u->id, collect()),
                $todayTaskEntries->get($u->id, collect()),
                $activeTimer ? collect([$activeTimer]) : collect(),
                now()->setTimezone($tz)->format('Y-m-d'),
                $tz,
            );

            $currentRoom = $activeSession?->room?->name ?? ($isOnline ? 'Open Space' : null);
            $currentOffice = $activeSession?->room?->floor?->name ?? ($activeSession?->room?->map?->floor?->name ?? ($isOnline ? 'Main Office' : 'Offline'));

            return [
                'user_id' => $u->id,
                'member_id' => $m->id,
                'name' => $u->name,
                'nickname' => $u->nickname,
                'email' => $u->email,
                'avatar_url' => $u->avatar_url,
                'role_name' => $m->role?->name ?? 'Member',
                'job_title' => $m->job_title ?? ($u->profiles?->first()?->job_title ?? ''),
                'is_online' => $isOnline,
                'office_name' => $currentOffice,
                'room_name' => $currentRoom,
                'total_office_seconds' => $day['office'],
                'total_office_formatted' => $this->hms($day['office']),
                'total_task_seconds' => $day['task'],
                'total_task_formatted' => $this->hms($day['task']),
                'total_attendance_seconds' => $day['attendance'],
                'total_attendance_hours' => round($day['attendance'] / 3600, 2),
                'total_attendance_formatted' => $this->hms($day['attendance']),
                'active_task' => $activeTimer ? [
                    'task_title' => $activeTimer->task?->title ?? 'Work Session',
                    'project_name' => $activeTimer->project?->name ?? 'General',
                    'elapsed_seconds' => $activeTimer->elapsedSeconds(),
                ] : null,
            ];
        })->filter()->values();

        return [
            'online_count' => $roster->where('is_online', true)->count(),
            'total_count' => $roster->count(),
            'roster' => $roster,
        ];
    }

    // ── Time arithmetic ──

    /**
     * Office hours and headcount for an organization today, each session on its
     * own office's today — the owner dashboard's "in the office today".
     *
     * @return array{seconds: int, present: int}
     */
    public function organizationOfficeToday(Organization $organization): array
    {
        $sessions = $this->sessionsOverlapping(now()->subDays(2), now()->addDay())
            ->where('organization_id', $organization->id)
            ->get();

        $seconds = 0;
        $present = [];
        foreach ($sessions->groupBy('user_id') as $uid => $userSessions) {
            $today = [];
            foreach ($userSessions as $s) {
                $today = array_merge($today, $this->officeIntervalsOn(collect([$s]), now()->setTimezone($this->timezones->forSession($s))->format('Y-m-d')));
            }
            if ($today !== []) {
                $seconds += TimeIntervals::union($today);
                $present[] = $uid;
            }
        }

        return ['seconds' => $seconds, 'present' => count($present)];
    }

    /**
     * One member's day: office presence, task time, their union and the
     * office time with no task running. Each session is clipped to $date in its
     * own office's timezone; task time to $date in the member's ($memberTz).
     *
     * @return array{office: int, task: int, attendance: int, idle: int}
     */
    protected function dayTotals(Collection $sessions, Collection $entries, Collection $timers, string $date, string $memberTz): array
    {
        $office = $this->officeIntervalsOn($sessions, $date);
        $task = TimeIntervals::clip(array_merge(
            $entries->map(fn ($e) => $this->entryInterval($e))->values()->all(),
            $timers->map(fn ($t) => [$t->started_at->getTimestamp(), max($t->started_at->getTimestamp(), now()->getTimestamp())])->values()->all(),
        ), ...$this->dayWindow($date, $memberTz));

        $officeSeconds = TimeIntervals::union($office);

        return [
            'office' => $officeSeconds,
            // Entries are summed, as timesheets and billing sum them.
            'task' => array_sum(array_map(fn ($i) => $i[1] - $i[0], $task)),
            'attendance' => TimeIntervals::union(array_merge($office, $task)),
            'idle' => $officeSeconds - TimeIntervals::overlap($office, $task),
        ];
    }

    /** Session intervals clipped to $date, each in its own office's timezone. */
    protected function officeIntervalsOn(Collection $sessions, string $date): array
    {
        $out = [];
        foreach ($sessions as $s) {
            $out = array_merge($out, TimeIntervals::clip([$this->sessionInterval($s)], ...$this->dayWindow($date, $this->timezones->forSession($s))));
        }

        return $out;
    }

    /** [start, end) of a calendar date in a timezone, as unix timestamps. */
    protected function dayWindow(string $date, string $tz): array
    {
        $start = Carbon::parse($date, $tz)->startOfDay();

        return [$start->getTimestamp(), $start->copy()->addDay()->getTimestamp()];
    }

    /**
     * A query range wide enough to hold $date in every timezone (UTC−12 to
     * UTC+14); rows are then clipped to the exact local day.
     */
    protected function searchRange(string $date): array
    {
        $utc = Carbon::parse($date, 'UTC')->startOfDay();

        return [$utc->copy()->subHours(14), $utc->copy()->addDay()->addHours(12)];
    }

    /**
     * [start, end] of a session as unix timestamps. An open session runs to
     * now while its heartbeat is fresh, otherwise to its last heartbeat.
     */
    protected function sessionInterval(AttendanceSession $session): array
    {
        $start = $session->started_at->getTimestamp();

        if ($session->ended_at) {
            $end = $session->ended_at->getTimestamp();
        } else {
            $beat = $session->last_heartbeat_at ?? $session->started_at;
            $end = $beat->gte(now()->subMinutes($this->staleTimeoutMinutes)) ? now()->getTimestamp() : $beat->getTimestamp();
        }

        return [$start, max($start, $end)];
    }

    /**
     * [start, end] of a time entry. duration_seconds is the booked time (a
     * manual entry may book less than its start–end span), so it wins when set.
     */
    protected function entryInterval(TimeEntry $entry): array
    {
        $start = ($entry->started_at ?? $entry->created_at)->getTimestamp();

        if ((int) $entry->duration_seconds > 0) {
            return [$start, $start + (int) $entry->duration_seconds];
        }

        $end = $entry->ended_at ? $entry->ended_at->getTimestamp() : now()->getTimestamp();

        return [$start, max($start, $end)];
    }

    /** Sessions that overlap [from, to), open ones included. */
    protected function sessionsOverlapping(CarbonInterface $from, CarbonInterface $to)
    {
        return AttendanceSession::where('started_at', '<', $this->db($to))
            ->where(fn ($q) => $q->whereNull('ended_at')->orWhere('ended_at', '>', $this->db($from)));
    }

    /**
     * Time entries that overlap [from, to). Matched on when the work happened,
     * never on created_at — time logged today for last week belongs to last week.
     */
    protected function entriesOverlapping(CarbonInterface $from, CarbonInterface $to)
    {
        return TimeEntry::where('started_at', '<', $this->db($to))
            ->where(fn ($q) => $q->whereNull('ended_at')->orWhere('ended_at', '>', $this->db($from)));
    }

    protected function openSessionsQuery()
    {
        return AttendanceSession::where('status', 'active')->whereNull('ended_at');
    }

    protected function openSessions(string $userId, string $organizationId)
    {
        return $this->openSessionsQuery()
            ->where('organization_id', $organizationId)
            ->where('user_id', $userId)
            ->latest('started_at');
    }

    /**
     * Serialize attendance writes per user. The office fires "enter" for the
     * next room and "leave" for the last one at the same moment; without this
     * both can open a session.
     */
    protected function lockUser(string $userId): void
    {
        DB::table('users')->where('id', $userId)->lockForUpdate()->first();
    }

    /** A boundary converted to the timezone timestamps are stored in. */
    protected function db(CarbonInterface $at): CarbonInterface
    {
        return $at->copy()->setTimezone(config('app.timezone'));
    }

    protected function secondsBetween(CarbonInterface $from, CarbonInterface $to): int
    {
        return max(0, $to->getTimestamp() - $from->getTimestamp());
    }

    protected function hms(int $seconds): string
    {
        return sprintf('%02d:%02d:%02d', intdiv($seconds, 3600), intdiv($seconds % 3600, 60), $seconds % 60);
    }
}
