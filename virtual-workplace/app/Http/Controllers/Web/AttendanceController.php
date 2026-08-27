<?php

namespace App\Http\Controllers\Web;

use App\Domains\Administration\Models\AuditLog;
use App\Domains\Tenancy\Models\OrganizationMember;
use App\Domains\Identity\Models\User;
use App\Domains\People\Services\AttendanceService;
use App\Domains\Projects\Actions\StartTimerAction;
use App\Domains\Projects\Actions\StopTimerAction;
use App\Domains\Projects\Models\ActiveTimer;
use App\Domains\Projects\Models\Task;
use App\Domains\Projects\Models\TimeEntry;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AttendanceController extends Controller
{
    /**
     * Log Room and Floor presence intervals for time & attendance tracking.
     */
    public function logRoomAttendance(Request $request, AttendanceService $attendanceService)
    {
        $user = Auth::user();
        $membership = OrganizationMember::where('user_id', $user->id)->first();
        if (! $membership) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'room_id' => 'nullable|uuid|exists:rooms,id',
            'action' => 'required|in:enter,leave,heartbeat,idle_pause,idle_resume',
            'duration_seconds' => 'nullable|integer',
        ]);

        $organization = $membership->organization;
        $roomId = $validated['room_id'] ?? null;
        $action = $validated['action'];

        // 1. Manage dedicated attendance_sessions record
        if ($action === 'enter') {
            $attendanceService->startSession($user, $organization, $roomId, $request->ip(), $request->userAgent());
        } elseif ($action === 'leave') {
            $attendanceService->endSession($user, $organization, $roomId);
        } elseif ($action === 'heartbeat') {
            $attendanceService->recordHeartbeat($user, $organization, $roomId, $validated['duration_seconds'] ?? null);
        } elseif ($action === 'idle_pause') {
            $attendanceService->pauseSessionForIdle($user, $organization);
        } elseif ($action === 'idle_resume') {
            $attendanceService->resumeSessionFromIdle($user, $organization, $roomId);
        }

        // 2. Also log to AuditLog for audit trail history
        AuditLog::create([
            'organization_id' => $membership->organization_id,
            'user_id' => $user->id,
            'action' => 'room.'.$action,
            'target_type' => 'room',
            'target_id' => $roomId,
            'metadata' => [
                'user_name' => $user->name,
                'room_id' => $roomId,
                'duration_seconds' => $validated['duration_seconds'] ?? 0,
                'timestamp' => now()->toISOString(),
            ],
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return response()->json(['status' => 'logged', 'action' => $action]);
    }

    /**
     * Get dual-section Daily Timesheet report (Tasks + Office Attendance).
     */
    public function getDailyTimesheetsReport(Request $request, AttendanceService $attendanceService)
    {
        $user = Auth::user();
        $membership = OrganizationMember::where('user_id', $user->id)->first();
        if (! $membership) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $targetUserId = $user->id;
        $requestedUserId = $request->query('user_id');

        // If manager/admin wants to view another member's timesheet
        if ($requestedUserId && $requestedUserId !== $user->id) {
            $isPrivileged = $user->isSuperAdmin()
                || ($membership->role?->slug === 'company_admin')
                || $membership->hasPermission('timesheets.approve')
                || $membership->hasPermission('organizations.manage');

            if ($isPrivileged) {
                $targetUserId = $requestedUserId;
            }
        }

        $date = $request->query('date', now()->format('Y-m-d'));
        $data = $attendanceService->getDailyTimesheetData($targetUserId, $membership->organization_id, $date);

        return response()->json($data);
    }

    /**
     * Get active task timer and assigned tasks for in-office task drawer.
     */
    public function getOfficeTasksAndTimer(Request $request)
    {
        $user = Auth::user();
        $membership = OrganizationMember::where('user_id', $user->id)->first();
        if (! $membership) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $organizationId = $membership->organization_id;

        // 1. Active running timer
        $activeTimer = ActiveTimer::where('organization_id', $organizationId)
            ->where('user_id', $user->id)
            ->with(['project:id,name,color,code', 'task:id,title,task_number,priority,status'])
            ->first();

        $activeTimerData = null;
        if ($activeTimer) {
            $activeTimerData = [
                'id' => $activeTimer->id,
                'task_id' => $activeTimer->task_id,
                'project_id' => $activeTimer->project_id,
                'task_title' => $activeTimer->task?->title ?? 'Task',
                'task_number' => $activeTimer->task?->task_number ?? '',
                'project_name' => $activeTimer->project?->name ?? 'Project',
                'project_color' => $activeTimer->project?->color ?? '#34D399',
                'started_at' => $activeTimer->started_at->toIso8601String(),
                'elapsed_seconds' => $activeTimer->elapsedSeconds(),
            ];
        }

        // 2. Assigned tasks for this user in this organization
        $tasks = Task::where('organization_id', $organizationId)
            ->where('assignee_id', $user->id)
            ->whereNotIn('status', ['done', 'completed', 'cancelled'])
            ->with(['project:id,name,color,code'])
            ->orderBy('priority', 'desc')
            ->orderBy('due_date', 'asc')
            ->take(30)
            ->get();

        return response()->json([
            'has_active_timer' => (bool) $activeTimer,
            'active_timer' => $activeTimerData,
            'tasks' => $tasks->map(function ($t) use ($activeTimer) {
                return [
                    'id' => $t->id,
                    'project_id' => $t->project_id,
                    'title' => $t->title,
                    'task_number' => $t->task_number,
                    'priority' => $t->priority,
                    'status' => $t->status,
                    'project_name' => $t->project?->name ?? 'Project',
                    'project_color' => $t->project?->color ?? '#34D399',
                    'due_date' => $t->due_date ? $t->due_date->format('Y-m-d') : null,
                    'is_running' => $activeTimer && $activeTimer->task_id === $t->id,
                ];
            }),
        ]);
    }

    /**
     * Start task timer directly from Virtual Office.
     */
    public function startOfficeTaskTimer(Request $request, StartTimerAction $action)
    {
        $user = Auth::user();
        $membership = OrganizationMember::where('user_id', $user->id)->first();
        if (! $membership) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'task_id' => 'required|uuid|exists:tasks,id',
            'project_id' => 'nullable|uuid|exists:projects,id',
            'description' => 'nullable|string|max:500',
        ]);

        $task = Task::findOrFail($validated['task_id']);
        $projectId = $validated['project_id'] ?? $task->project_id;

        $timer = $action->execute([
            'task_id' => $task->id,
            'project_id' => $projectId,
            'description' => $validated['description'] ?? $task->title,
        ], $membership->organization, $user);

        return response()->json([
            'success' => true,
            'message' => __('Task timer started.'),
            'timer' => [
                'id' => $timer->id,
                'task_id' => $timer->task_id,
                'task_title' => $task->title,
                'task_number' => $task->task_number,
                'project_name' => $timer->project?->name ?? 'Project',
                'started_at' => $timer->started_at->toIso8601String(),
                'elapsed_seconds' => 0,
            ],
        ]);
    }

    /**
     * Stop task timer directly from Virtual Office.
     */
    public function stopOfficeTaskTimer(Request $request, StopTimerAction $action)
    {
        $user = Auth::user();
        $membership = OrganizationMember::where('user_id', $user->id)->first();
        if (! $membership) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $entry = $action->execute($membership->organization, $user, $request->input('description'));

        return response()->json([
            'success' => true,
            'message' => __('Task timer stopped and logged.'),
            'time_entry' => $entry,
        ]);
    }

    /**
     * Update task workflow status directly from Virtual Office.
     */
    public function updateOfficeTaskStatus(Request $request, string $taskId)
    {
        $user = Auth::user();
        $membership = OrganizationMember::where('user_id', $user->id)->first();
        if (! $membership) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $task = Task::where('organization_id', $membership->organization_id)->findOrFail($taskId);

        $validated = $request->validate([
            'status' => 'required|string|in:backlog,ready,in_progress,review,qa,done,completed',
        ]);

        $newStatus = $validated['status'] === 'completed' ? 'done' : $validated['status'];
        $task->update([
            'status' => $newStatus,
            'completed_at' => $newStatus === 'done' ? now() : null,
        ]);

        return response()->json([
            'success' => true,
            'message' => __('Task status updated successfully.'),
            'task' => [
                'id' => $task->id,
                'status' => $task->status,
            ],
        ]);
    }

    /**
     * Get user attendance hours and sessions report.
     */
    public function getAttendanceSummary(Request $request, AttendanceService $attendanceService)
    {
        $user = Auth::user();
        $membership = OrganizationMember::where('user_id', $user->id)->first();
        if (! $membership) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $period = (string) $request->query('period', 'week');
        $report = $attendanceService->getUserReport($user->id, $membership->organization_id, $period);

        return response()->json($report);
    }

    /**
     * Get member live activity, active timer, and assigned tasks for in-office user spotlight card.
     */
    public function getMemberActivity(Request $request, string $userId)
    {
        $user = Auth::user();
        $membership = OrganizationMember::where('user_id', $user->id)->first();
        if (! $membership) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }
        $organizationId = $membership->organization_id;

        $targetUser = User::with('profile')->find($userId);
        if (! $targetUser) {
            return response()->json(['message' => 'User not found'], 404);
        }

        $targetMember = OrganizationMember::where('organization_id', $organizationId)
            ->where('user_id', $userId)
            ->with(['role'])
            ->first();

        // Active running timer
        $activeTimer = ActiveTimer::where('organization_id', $organizationId)
            ->where('user_id', $userId)
            ->with(['project', 'task'])
            ->first();

        // Pending assigned tasks
        $tasks = Task::where('organization_id', $organizationId)
            ->where('assignee_id', $userId)
            ->whereNotIn('status', ['done', 'completed', 'cancelled'])
            ->with('project')
            ->orderBy('priority', 'desc')
            ->orderBy('due_date', 'asc')
            ->take(20)
            ->get();

        return response()->json([
            'user' => [
                'id' => $targetUser->id,
                'name' => $targetUser->name,
                'email' => $targetUser->email,
                'role_name' => $targetMember?->role?->name ?? 'Member',
                'job_title' => $targetMember?->job_title ?? $targetUser->profile?->job_title ?? '',
                'department' => $targetUser->profile?->department ?? '',
                'avatar_url' => $targetUser->avatar_url ?? null,
            ],
            'active_timer' => $activeTimer ? [
                'task_title' => $activeTimer->task?->title ?? 'Task',
                'task_number' => $activeTimer->task?->task_number ?? '',
                'project_name' => $activeTimer->project?->name ?? 'Project',
                'duration_seconds' => $activeTimer->elapsedSeconds(),
                'started_at' => $activeTimer->started_at->toIso8601String(),
            ] : null,
            'tasks' => $tasks->map(function ($t) {
                return [
                    'id' => $t->id,
                    'title' => $t->title,
                    'task_number' => $t->task_number,
                    'status' => $t->status,
                    'priority' => $t->priority,
                    'project_name' => $t->project?->name ?? 'General',
                    'due_date' => $t->due_date ? $t->due_date->format('Y-m-d') : null,
                ];
            }),
        ]);
    }

    /**
     * Get member live activity for modal card (supports guest viewers).
     */
    public function memberActivity(Request $request, $userId)
    {
        $viewer = Auth::user();
        $isGuest = empty($viewer);
        $orgId = null;

        if ($viewer) {
            $viewerMembership = OrganizationMember::where('user_id', $viewer->id)->first();
            if (! $viewerMembership) {
                return response()->json(['message' => 'Unauthorized'], 403);
            }
            $orgId = $viewerMembership->organization_id;
        } else {
            // Guest viewer
            $orgId = $request->input('organization_id');
        }

        $targetUser = User::with(['profile.department', 'profile.team'])->find($userId);
        if (! $targetUser) {
            return response()->json(['message' => 'Member not found'], 404);
        }

        $targetMembership = OrganizationMember::where('user_id', $targetUser->id)
            ->when($orgId, function ($q) use ($orgId) {
                $q->where('organization_id', $orgId);
            })
            ->with(['role'])
            ->first();

        if (! $targetMembership && $orgId) {
            return response()->json(['message' => 'Member not found in organization'], 404);
        }

        $effectiveOrgId = $targetMembership ? $targetMembership->organization_id : $orgId;

        // Privacy rule: Guests are NEVER permitted to inspect team tasks or internal active timers
        $activeTimer = null;
        $tasks = collect([]);

        if (! $isGuest && $effectiveOrgId) {
            // Active timer (what they are working on right now)
            $activeTimer = TimeEntry::where('user_id', $targetUser->id)
                ->where('organization_id', $effectiveOrgId)
                ->whereNull('ended_at')
                ->with(['task', 'project'])
                ->latest('started_at')
                ->first();

            // Tasks assigned to this user
            $tasks = Task::where('organization_id', $effectiveOrgId)
                ->where(function ($q) use ($targetUser) {
                    $q->where('assignee_id', $targetUser->id);
                })
                ->with(['project'])
                ->latest()
                ->take(15)
                ->get();
        }

        return response()->json([
            'user' => [
                'id' => $targetUser->id,
                'name' => $targetUser->name,
                'email' => $isGuest ? null : $targetUser->email,
                'avatar_url' => $targetUser->avatar_url,
                'role_name' => $targetMembership?->role?->name ?? 'Member',
                'job_title' => $targetUser->profile?->job_title ?? __('Team Member'),
                'department' => $targetUser->profile?->department?->name,
                'team' => $targetUser->profile?->team?->name,
                'status' => $targetMembership?->status ?? 'active',
            ],
            'is_guest_viewer' => $isGuest,
            'active_timer' => $activeTimer ? [
                'id' => $activeTimer->id,
                'project_name' => $activeTimer->project?->name ?? 'General Work',
                'task_title' => $activeTimer->task?->title ?? ($activeTimer->description ?? 'Focused Work Session'),
                'started_at' => $activeTimer->started_at?->toISOString(),
                'duration_seconds' => $activeTimer->started_at ? now()->diffInSeconds($activeTimer->started_at) : 0,
            ] : null,
            'tasks' => $tasks->map(function ($t) {
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
}
