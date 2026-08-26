<?php

namespace App\Domains\People\Services;

use App\Domains\Identity\Models\User;
use App\Domains\People\Models\AttendanceSession;
use App\Domains\Tenancy\Models\Organization;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

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
            $started = \Carbon\Carbon::parse($session->started_at);
            $endTime = $session->last_heartbeat_at ? \Carbon\Carbon::parse($session->last_heartbeat_at) : $started->copy()->addMinutes(1);
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
                return max($s->duration_seconds, now()->diffInSeconds($s->started_at));
            }
            return $s->duration_seconds;
        });

        $totalHours = round($totalSeconds / 3600, 2);

        // Daily breakdown
        $daily = [];
        foreach ($sessions as $s) {
            $dayKey = $s->started_at->format('Y-m-d');
            if (!isset($daily[$dayKey])) {
                $daily[$dayKey] = [
                    'date' => $dayKey,
                    'day_name' => $s->started_at->format('l'),
                    'duration_seconds' => 0,
                    'hours' => 0,
                    'session_count' => 0,
                ];
            }
            $dur = $s->isActive() ? now()->diffInSeconds($s->started_at) : $s->duration_seconds;
            $daily[$dayKey]['duration_seconds'] += $dur;
            $daily[$dayKey]['hours'] = round($daily[$dayKey]['duration_seconds'] / 3600, 2);
            $daily[$dayKey]['session_count']++;
        }

        return [
            'period' => $period,
            'start_date' => $startDate->toIso8601String(),
            'end_date' => $now->toIso8601String(),
            'total_seconds' => $totalSeconds,
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
    public function getDailyTimesheetData(string $userId, string $organizationId, ?string $date = null): array
    {
        $targetDate = $date ? Carbon::parse($date)->startOfDay() : now()->startOfDay();
        $startOfDay = $targetDate->copy()->startOfDay();
        $endOfDay = $targetDate->copy()->endOfDay();

        // ── Section 1: Project & Task Time Entries ──
        $taskEntries = \App\Domains\Projects\Models\TimeEntry::where('organization_id', $organizationId)
            ->where('user_id', $userId)
            ->where(function ($q) use ($startOfDay, $endOfDay) {
                $q->whereBetween('started_at', [$startOfDay, $endOfDay])
                  ->orWhereBetween('created_at', [$startOfDay, $endOfDay]);
            })
            ->with(['project:id,name,color,code', 'task:id,title,task_number,priority,status'])
            ->orderBy('started_at', 'asc')
            ->get();

        $totalTaskSeconds = $taskEntries->sum(function ($entry) {
            if (!$entry->ended_at && $entry->started_at) {
                return max(0, now()->diffInSeconds($entry->started_at));
            }
            return $entry->duration_seconds ?? 0;
        });

        // ── Section 2: Virtual Office Attendance Sessions ──
        $attendanceSessions = AttendanceSession::where('organization_id', $organizationId)
            ->where('user_id', $userId)
            ->whereBetween('started_at', [$startOfDay, $endOfDay])
            ->with(['room', 'room.floor', 'room.map'])
            ->orderBy('started_at', 'asc')
            ->get();

        $totalOfficeSeconds = $attendanceSessions->sum(function ($session) {
            if ($session->isActive()) {
                return max($session->duration_seconds, now()->diffInSeconds($session->started_at));
            }
            return $session->duration_seconds ?? 0;
        });

        $idlePausedSeconds = $attendanceSessions->where('status', 'idle_paused')->sum('duration_seconds');

        // Check if there is an active running timer right now via ActiveTimer or open TimeEntry
        $activeTimerModel = \App\Domains\Projects\Models\ActiveTimer::where('organization_id', $organizationId)
            ->where('user_id', $userId)
            ->with(['project:id,name,color', 'task:id,title,task_number'])
            ->first();

        $activeTaskTimer = null;
        if ($activeTimerModel) {
            $activeTaskTimer = [
                'id' => $activeTimerModel->id,
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
        $activeOfficeSession = AttendanceSession::where('organization_id', $organizationId)
            ->where('user_id', $userId)
            ->where('status', 'active')
            ->whereNull('ended_at')
            ->latest('started_at')
            ->first();

        return [
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
            'task_entries' => $taskEntries,
            'attendance_sessions' => $attendanceSessions,
        ];
    }
}
