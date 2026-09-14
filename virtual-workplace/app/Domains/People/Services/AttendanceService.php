<?php

namespace App\Domains\People\Services;

use App\Domains\Identity\Models\User;
use App\Domains\People\Models\AttendanceSession;
use App\Domains\Projects\Models\ActiveTimer;
use App\Domains\Projects\Models\TimeEntry;
use App\Domains\Tenancy\Models\Organization;
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
            'idle_seconds' => $idlePausedSeconds,
            'is_in_office' => (bool) $activeOfficeSession,
            'has_running_task' => (bool) $activeTaskTimer,
            'active_task_timer' => $activeTaskTimer,
            'active_timer' => $activeTaskTimer,
            'task_entries' => $formattedTaskEntries,
            'attendance_sessions' => $formattedAttendanceSessions,
        ];
    }
}
