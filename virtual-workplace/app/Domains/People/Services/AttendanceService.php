<?php

namespace App\Domains\People\Services;

use App\Domains\Identity\Models\User;
use App\Domains\People\Models\AttendanceSession;
use App\Domains\Projects\Models\ActiveTimer;
use App\Domains\Projects\Models\Task;
use App\Domains\Projects\Models\TimeEntry;
use App\Domains\Tenancy\Models\Organization;
use App\Domains\Tenancy\Models\OrganizationMember;
use Carbon\Carbon;

class AttendanceService
{
    /**
     * Stale heartbeat threshold in minutes.
     */
    protected int $staleTimeoutMinutes = 10;

    /**
     * Start a new attendance session for user in organization / room.
     */
    public function startSession(
        User $user,
        Organization $organization,
        ?string $roomId = null,
        ?string $ip = null,
        ?string $userAgent = null
    ): AttendanceSession {
        // 1. Clean up / close any previous active sessions for this user
        $this->cleanupStaleSessions($user->id);

        $activeSession = AttendanceSession::where('organization_id', $organization->id)
            ->where('user_id', $user->id)
            ->where('status', 'active')
            ->whereNull('ended_at')
            ->first();

        if ($activeSession) {
            if ($activeSession->room_id === $roomId) {
                $activeSession->update(['last_heartbeat_at' => now()]);

                return $activeSession;
            }
            // User switched rooms - close previous session
            $activeSession->close('completed');
        }

        // 2. Open new session
        return AttendanceSession::create([
            'organization_id' => $organization->id,
            'user_id' => $user->id,
            'room_id' => $roomId,
            'status' => 'active',
            'started_at' => now(),
            'last_heartbeat_at' => now(),
            'ip_address' => $ip,
            'user_agent' => $userAgent,
        ]);
    }

    /**
     * End an active attendance session.
     */
    public function endSession(User $user, Organization $organization, ?string $roomId = null): ?AttendanceSession
    {
        $query = AttendanceSession::where('organization_id', $organization->id)
            ->where('user_id', $user->id)
            ->where('status', 'active')
            ->whereNull('ended_at');

        if ($roomId) {
            $query->where('room_id', $roomId);
        }

        $session = $query->latest('started_at')->first();
        if ($session) {
            $session->close('completed');
        }

        return $session;
    }

    /**
     * Record a heartbeat to keep the active session alive and track duration.
     */
    public function recordHeartbeat(
        User $user,
        Organization $organization,
        ?string $roomId = null,
        ?int $durationSeconds = null
    ): ?AttendanceSession {
        $session = AttendanceSession::where('organization_id', $organization->id)
            ->where('user_id', $user->id)
            ->where('status', 'active')
            ->whereNull('ended_at')
            ->latest('started_at')
            ->first();

        if ($session) {
            $update = ['last_heartbeat_at' => now()];
            if ($durationSeconds !== null && $durationSeconds > 0) {
                $sessionDuration = max($session->duration_seconds, $durationSeconds);
                $update['duration_seconds'] = $sessionDuration;
            } else {
                $update['duration_seconds'] = max(0, now()->diffInSeconds($session->started_at));
            }
            $session->update($update);
        } else {
            // Auto restart if heartbeat arrives without active session
            $session = $this->startSession($user, $organization, $roomId);
        }

        // Background maintenance
        $this->cleanupStaleSessions();

        return $session;
    }

    /**
     * Automatically mark abandoned sessions as timed out based on last heartbeat.
     */
    public function cleanupStaleSessions(?string $userId = null): int
    {
        $threshold = now()->subMinutes($this->staleTimeoutMinutes);

        $query = AttendanceSession::where('status', 'active')
            ->whereNull('ended_at')
            ->where('last_heartbeat_at', '<', $threshold);

        if ($userId) {
            $query->where('user_id', $userId);
        }

        $staleSessions = $query->get();
        $count = 0;

        foreach ($staleSessions as $session) {
            $started = Carbon::parse($session->started_at);
            $endTime = $session->last_heartbeat_at ? Carbon::parse($session->last_heartbeat_at) : $started->copy()->addMinutes(1);
            $session->ended_at = $endTime;
            $session->status = 'timed_out';
            $session->duration_seconds = max(1, abs($endTime->diffInSeconds($started)));
            $session->save();
            $count++;
        }

        return $count;
    }

    /**
     * Compute aggregated attendance report for a user or organization.
     */
    public function getUserReport(string $userId, ?string $organizationId = null, string $period = 'week'): array
    {
        $now = now();
        $startDate = match ($period) {
            'today', 'day' => $now->copy()->startOfDay(),
            'month' => $now->copy()->startOfMonth(),
            default => $now->copy()->startOfWeek(),
        };

        $query = AttendanceSession::where('user_id', $userId)
            ->where('started_at', '>=', $startDate)
            ->with(['room:id,name', 'organization:id,name']);

        if ($organizationId) {
            $query->where('organization_id', $organizationId);
        }

        $sessions = $query->orderBy('started_at', 'desc')->get();

        $totalSeconds = $sessions->sum(function ($s) {
            if ($s->isActive()) {
                return max($s->duration_seconds ?? 0, now()->diffInSeconds($s->started_at));
            }

            return $s->duration_seconds ?? 0;
        });

        $totalHours = round($totalSeconds / 3600, 2);

        // Daily breakdown
        $daily = [];
        foreach ($sessions as $s) {
            $dayKey = $s->started_at->format('Y-m-d');
            if (! isset($daily[$dayKey])) {
                $daily[$dayKey] = [
                    'date' => $dayKey,
                    'day_name' => $s->started_at->format('l'),
                    'duration_seconds' => 0,
                    'hours' => 0,
                    'session_count' => 0,
                ];
            }
            $dur = $s->isActive() ? now()->diffInSeconds($s->started_at) : ($s->duration_seconds ?? 0);
            $daily[$dayKey]['duration_seconds'] += $dur;
            $daily[$dayKey]['hours'] = round($daily[$dayKey]['duration_seconds'] / 3600, 2);
            $daily[$dayKey]['session_count']++;
        }

        return [
            'period' => $period,
            'start_date' => $startDate->toIso8601String(),
            'end_date' => $now->toIso8601String(),
            'total_seconds' => $totalSeconds,
            'today_total_seconds' => $totalSeconds,
            'total_hours' => $totalHours,
            'sessions_count' => $sessions->count(),
            'daily_breakdown' => array_values($daily),
            'recent_sessions' => $sessions->take(15)->map(function ($s) {
                return [
                    'id' => $s->id,
                    'room_name' => $s->room?->name ?? 'Open Space',
                    'started_at' => $s->started_at->toIso8601String(),
                    'ended_at' => $s->ended_at?->toIso8601String(),
                    'duration_seconds' => $s->duration_seconds,
                    'status' => $s->status,
                ];
            }),
        ];
    }

    /**
     * Pause user attendance session due to unacknowledged inactivity/idle timeout.
     */
    public function pauseSessionForIdle(User $user, Organization $organization): ?AttendanceSession
    {
        $session = AttendanceSession::where('organization_id', $organization->id)
            ->where('user_id', $user->id)
            ->where('status', 'active')
            ->whereNull('ended_at')
            ->latest('started_at')
            ->first();

        if ($session) {
            $session->pauseForIdle();
        }

        return $session;
    }

    /**
     * Resume user attendance session from idle pause state.
     */
    public function resumeSessionFromIdle(User $user, Organization $organization, ?string $roomId = null): AttendanceSession
    {
        // Re-start active session
        return $this->startSession($user, $organization, $roomId);
    }

    /**
     * Get dual-section Daily Timesheet & Attendance Data for dashboard.
     */
    public function getDailyTimesheetData(?string $userId, string $organizationId, ?string $date = null): array
    {
        // First cleanup any stale dead sessions to keep data 100% accurate
        $this->cleanupStaleSessions();

        $targetDate = $date ? Carbon::parse($date)->startOfDay() : now()->startOfDay();
        $startOfDay = $targetDate->copy()->startOfDay();
        $endOfDay = $targetDate->copy()->endOfDay();

        $isAll = empty($userId) || $userId === 'all';

        // ── Section 1: Project & Task Time Entries ──
        $taskQuery = TimeEntry::where('organization_id', $organizationId)
            ->where(function ($q) use ($startOfDay, $endOfDay) {
                $q->whereBetween('started_at', [$startOfDay, $endOfDay])
                    ->orWhereBetween('created_at', [$startOfDay, $endOfDay]);
            })
            ->with(['user.profiles', 'project:id,name,color,code', 'task:id,title,task_number,priority,status'])
            ->orderBy('started_at', 'desc');

        if (! $isAll) {
            $taskQuery->where('user_id', $userId);
        }

        $taskEntries = $taskQuery->get();

        $totalTaskSeconds = $taskEntries->sum(function ($entry) {
            if (! $entry->ended_at && $entry->started_at) {
                return max(0, now()->diffInSeconds($entry->started_at));
            }

            return $entry->duration_seconds ?? 0;
        });

        // ── Section 2: Virtual Office Attendance Sessions ──
        $attQuery = AttendanceSession::where('organization_id', $organizationId)
            ->whereBetween('started_at', [$startOfDay, $endOfDay])
            ->with(['user.profiles', 'room', 'room.floor', 'room.map'])
            ->orderBy('started_at', 'desc');

        if (! $isAll) {
            $attQuery->where('user_id', $userId);
        }

        $attendanceSessions = $attQuery->get();

        $totalOfficeSeconds = $attendanceSessions->sum(function ($session) {
            if ($session->isActive()) {
                return max($session->duration_seconds ?? 0, now()->diffInSeconds($session->started_at));
            }

            return $session->duration_seconds ?? 0;
        });

        $idlePausedSeconds = $attendanceSessions->where('status', 'idle_paused')->sum('duration_seconds');

        // Check active timers
        $activeTimerQuery = ActiveTimer::where('organization_id', $organizationId)
            ->with(['user', 'project:id,name,color', 'task:id,title,task_number']);
        if (! $isAll) {
            $activeTimerQuery->where('user_id', $userId);
        }
        $activeTimerModel = $activeTimerQuery->first();

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
            $totalTaskSeconds += $activeTimerModel->elapsedSeconds();
        }

        // Check if currently active in office
        $activeOfficeQuery = AttendanceSession::where('organization_id', $organizationId)
            ->where('status', 'active')
            ->whereNull('ended_at')
            ->latest('started_at');
        if (! $isAll) {
            $activeOfficeQuery->where('user_id', $userId);
        }
        $activeOfficeSession = $activeOfficeQuery->first();

        // Format task entries with clean human readable duration & timestamps
        $formattedTaskEntries = $taskEntries->map(function ($te) {
            $sec = $te->duration_seconds ?? 0;
            if (! $te->ended_at && $te->started_at) {
                $sec = max(0, now()->diffInSeconds($te->started_at));
            }
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
                'started_at' => $te->started_at ? Carbon::parse($te->started_at)->format('h:i A') : '—',
                'ended_at' => $te->ended_at ? Carbon::parse($te->ended_at)->format('h:i A') : ($te->started_at ? 'Live Now' : '—'),
                'duration_seconds' => $sec,
                'duration_formatted' => $durFormatted,
                'is_billable' => (bool) $te->is_billable,
                'status' => $te->status ?? 'completed',
            ];
        });

        // Format attendance sessions with room name, branch name, timestamps, and formatted durations
        $formattedAttendanceSessions = $attendanceSessions->map(function ($s) {
            $sec = $s->duration_seconds ?? 0;
            if ($s->isActive()) {
                $sec = max(0, now()->diffInSeconds($s->started_at));
            }

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
                'check_in' => $s->started_at ? Carbon::parse($s->started_at)->format('h:i:s A') : '—',
                'check_out' => $s->ended_at ? Carbon::parse($s->ended_at)->format('h:i:s A') : null,
                'started_at' => $s->started_at ? $s->started_at->toIso8601String() : null,
                'ended_at' => $s->ended_at ? $s->ended_at->toIso8601String() : null,
                'duration_seconds' => $sec,
                'duration_formatted' => $durFormatted,
                'status' => $s->status,
                'is_active' => $s->isActive(),
            ];
        });

        $totalAttendanceSeconds = $totalOfficeSeconds + $totalTaskSeconds;

        return [
            'is_all_members' => $isAll,
            'date' => $targetDate->format('Y-m-d'),
            'date_formatted' => $targetDate->isoFormat('dddd, D MMMM YYYY'),
            'total_office_seconds' => $totalOfficeSeconds,
            'total_office_hours' => round($totalOfficeSeconds / 3600, 2),
            'total_office_formatted' => sprintf('%02d:%02d:%02d', floor($totalOfficeSeconds / 3600), floor(($totalOfficeSeconds % 3600) / 60), $totalOfficeSeconds % 60),
            'total_task_seconds' => $totalTaskSeconds,
            'total_task_hours' => round($totalTaskSeconds / 3600, 2),
            'total_task_formatted' => sprintf('%02d:%02d:%02d', floor($totalTaskSeconds / 3600), floor(($totalTaskSeconds % 3600) / 60), $totalTaskSeconds % 60),
            'total_attendance_seconds' => $totalAttendanceSeconds,
            'total_attendance_hours' => round($totalAttendanceSeconds / 3600, 2),
            'total_attendance_formatted' => sprintf('%02d:%02d:%02d', floor($totalAttendanceSeconds / 3600), floor(($totalAttendanceSeconds % 3600) / 60), $totalAttendanceSeconds % 60),
            'idle_seconds' => $idlePausedSeconds,
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
                        'duration_seconds' => $openEntry->started_at ? now()->diffInSeconds($openEntry->started_at) : 0,
                    ];
                }
            }

            $tasks = Task::where('organization_id', $effectiveOrgId)
                ->where('assignee_id', $targetUser->id)
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

        $startOfDay = now()->startOfDay();
        $endOfDay = now()->endOfDay();

        $members = $organization->members()
            ->whereHas('user', function ($q) {
                $q->where('is_super_admin', false);
            })
            ->with(['user.profiles', 'role'])
            ->get();

        $activeSessions = AttendanceSession::where('organization_id', $organization->id)
            ->where('status', 'active')
            ->whereNull('ended_at')
            ->with(['room', 'room.floor', 'room.map'])
            ->get()
            ->keyBy('user_id');

        $todaySessions = AttendanceSession::where('organization_id', $organization->id)
            ->whereBetween('started_at', [$startOfDay, $endOfDay])
            ->get()
            ->groupBy('user_id');

        $todayTaskEntries = TimeEntry::where('organization_id', $organization->id)
            ->where(function ($q) use ($startOfDay, $endOfDay) {
                $q->whereBetween('started_at', [$startOfDay, $endOfDay])
                    ->orWhereBetween('created_at', [$startOfDay, $endOfDay]);
            })
            ->get()
            ->groupBy('user_id');

        $activeTimers = ActiveTimer::where('organization_id', $organization->id)
            ->with(['project:id,name', 'task:id,title,task_number'])
            ->get()
            ->keyBy('user_id');

        $roster = $members->map(function ($m) use ($activeSessions, $todaySessions, $todayTaskEntries, $activeTimers) {
            $u = $m->user;
            if (! $u) {
                return null;
            }

            $activeSession = $activeSessions->get($u->id);
            $isOnline = (bool) $activeSession;
            $userTodaySessions = $todaySessions->get($u->id, collect());
            $userTodayTasks = $todayTaskEntries->get($u->id, collect());
            $activeTimer = $activeTimers->get($u->id);

            $totalOfficeSec = $userTodaySessions->sum(function ($s) {
                if ($s->isActive()) {
                    return max($s->duration_seconds ?? 0, now()->diffInSeconds($s->started_at));
                }

                return $s->duration_seconds ?? 0;
            });

            $totalTaskSec = $userTodayTasks->sum(function ($te) {
                if (! $te->ended_at && $te->started_at) {
                    return max(0, now()->diffInSeconds($te->started_at));
                }

                return $te->duration_seconds ?? 0;
            });

            if ($activeTimer) {
                $totalTaskSec += $activeTimer->elapsedSeconds();
            }

            $currentRoom = $activeSession?->room?->name ?? ($isOnline ? 'Open Space' : null);
            $currentOffice = $activeSession?->room?->floor?->name ?? ($activeSession?->room?->map?->floor?->name ?? ($isOnline ? 'Main Office' : 'Offline'));

            $totalAttSec = $totalOfficeSec + $totalTaskSec;

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
                'total_office_seconds' => $totalOfficeSec,
                'total_office_formatted' => sprintf('%02d:%02d:%02d', floor($totalOfficeSec / 3600), floor(($totalOfficeSec % 3600) / 60), $totalOfficeSec % 60),
                'total_task_seconds' => $totalTaskSec,
                'total_task_formatted' => sprintf('%02d:%02d:%02d', floor($totalTaskSec / 3600), floor(($totalTaskSec % 3600) / 60), $totalTaskSec % 60),
                'total_attendance_seconds' => $totalAttSec,
                'total_attendance_hours' => round($totalAttSec / 3600, 2),
                'total_attendance_formatted' => sprintf('%02d:%02d:%02d', floor($totalAttSec / 3600), floor(($totalAttSec % 3600) / 60), $totalAttSec % 60),
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
}
