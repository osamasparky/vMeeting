<?php

namespace App\Domains\Projects\Controllers;

use App\Domains\Identity\Models\User;
use App\Domains\Projects\Actions\LogManualTimeAction;
use App\Domains\Projects\Actions\StartTimerAction;
use App\Domains\Projects\Actions\StopTimerAction;
use App\Domains\Projects\Models\ActiveTimer;
use App\Domains\Projects\Models\TimeEntry;
use App\Domains\Projects\Requests\LogManualTimeRequest;
use App\Domains\Projects\Requests\StartTimerRequest;
use App\Domains\Projects\Requests\UpdateTimeEntryRequest;
use App\Domains\Tenancy\Models\Organization;
use App\Domains\Tenancy\Models\OrganizationMember;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use InvalidArgumentException;

class TimeTrackingController extends Controller
{
    /**
     * Get user's active running timer (if any).
     */
    public function getActiveTimer(Request $request, Organization $organization): JsonResponse
    {
        $user = Auth::user();
        $timer = ActiveTimer::where('user_id', $user->id)
            ->with(['project:id,name,code', 'task:id,title,task_number'])
            ->first();

        return response()->json([
            'timer' => $timer,
            'elapsed_seconds' => $timer ? $timer->elapsedSeconds() : 0,
        ]);
    }

    /**
     * Start running timer (stops and logs existing timer automatically).
     */
    public function startTimer(
        StartTimerRequest $request,
        Organization $organization,
        StartTimerAction $action
    ): JsonResponse {
        $targetUser = $this->resolveTargetUser($request, $organization);

        $timer = $action->execute($request->validated(), $organization, $targetUser);

        return response()->json([
            'message' => 'Timer started successfully.',
            'timer' => $timer,
        ], 201);
    }

    /**
     * Stop currently running timer and log completed TimeEntry.
     */
    public function stopTimer(
        Request $request,
        Organization $organization,
        StopTimerAction $action
    ): JsonResponse {
        $targetUser = $this->resolveTargetUser($request, $organization);

        try {
            $entry = $action->execute($organization, $targetUser, $request->input('description'));
        } catch (InvalidArgumentException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        return response()->json([
            'message' => 'Timer stopped and time entry logged.',
            'time_entry' => $entry,
        ]);
    }

    /**
     * The user a timer call acts for. Acting for someone else needs
     * team-lead rights and a target who is a member of this organization.
     */
    private function resolveTargetUser(Request $request, Organization $organization): User
    {
        $actor = Auth::user();
        $targetId = $request->input('user_id');

        if (! $targetId || $targetId === $actor->id) {
            return $actor;
        }

        abort_unless($this->canManageOthersTime($request, $actor), 403, 'You cannot track time for another member.');

        $isMember = OrganizationMember::where('organization_id', $organization->id)
            ->where('user_id', $targetId)
            ->exists();
        abort_unless($isMember, 403, 'That user is not a member of this organization.');

        return User::findOrFail($targetId);
    }

    private function canManageOthersTime(Request $request, User $actor): bool
    {
        $membership = $request->get('current_membership');

        return $actor->isSuperAdmin()
            || ($membership && ($membership->role?->slug === 'company_admin' || $membership->hasPermission('tasks.assign')));
    }

    /**
     * Log time entry manually.
     */
    public function logManual(
        LogManualTimeRequest $request,
        Organization $organization,
        LogManualTimeAction $action
    ): JsonResponse {
        try {
            $entry = $action->execute($request->validated(), $organization, Auth::user());
        } catch (InvalidArgumentException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        return response()->json([
            'message' => 'Time logged successfully.',
            'time_entry' => $entry,
        ], 201);
    }

    /**
     * List time entries with filters.
     */
    public function index(Request $request, Organization $organization): JsonResponse
    {
        $user = Auth::user();
        $membership = $request->get('current_membership');

        $query = TimeEntry::forOrganization($organization->id)
            ->with(['project:id,name,code', 'task:id,title,task_number', 'user:id,name,email']);

        // Non-managers only see their own time entries
        if (! $membership || (! $membership->hasPermission('reports.view') && $membership->role?->slug === 'employee')) {
            $query->where('user_id', $user->id);
        } elseif ($request->filled('user_id')) {
            $query->where('user_id', $request->query('user_id'));
        }

        if ($request->filled('project_id')) {
            $query->where('project_id', $request->query('project_id'));
        }

        if ($request->filled('task_id')) {
            $query->where('task_id', $request->query('task_id'));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->query('status'));
        }

        if ($request->filled('from_date')) {
            $query->whereDate('started_at', '>=', $request->query('from_date'));
        }

        if ($request->filled('to_date')) {
            $query->whereDate('started_at', '<=', $request->query('to_date'));
        }

        $entries = $query->orderByDesc('started_at')->paginate($request->integer('per_page', 50));

        return response()->json($entries);
    }

    /**
     * Update an unlocked time entry.
     */
    public function update(
        UpdateTimeEntryRequest $request,
        Organization $organization,
        TimeEntry $timeEntry
    ): JsonResponse {
        if ($timeEntry->organization_id !== $organization->id) {
            return response()->json(['message' => 'Time entry not found.'], 404);
        }

        if ($timeEntry->isLocked()) {
            return response()->json([
                'message' => 'Cannot edit locked time entry. Entry is part of a submitted or approved timesheet.',
            ], 422);
        }

        $this->authorizeEntryOwner($request, $timeEntry);

        $data = $request->validated();
        $timeEntry->fill($data);

        // Keep the stored duration in step with an edited start/end window.
        if (! isset($data['duration_seconds']) && $timeEntry->isDirty(['started_at', 'ended_at']) && $timeEntry->ended_at) {
            if ($timeEntry->ended_at->lessThanOrEqualTo($timeEntry->started_at)) {
                return response()->json(['message' => 'End time must be strictly after start time.'], 422);
            }
            $timeEntry->duration_seconds = (int) $timeEntry->started_at->diffInSeconds($timeEntry->ended_at);
        }

        $timeEntry->save();

        return response()->json([
            'message' => 'Time entry updated successfully.',
            'time_entry' => $timeEntry->fresh(['project', 'task']),
        ]);
    }

    /**
     * Delete an unlocked time entry.
     */
    public function destroy(Request $request, Organization $organization, TimeEntry $timeEntry): JsonResponse
    {
        if ($timeEntry->organization_id !== $organization->id) {
            return response()->json(['message' => 'Time entry not found.'], 404);
        }

        if ($timeEntry->isLocked()) {
            return response()->json([
                'message' => 'Cannot delete locked time entry.',
            ], 422);
        }

        $this->authorizeEntryOwner($request, $timeEntry);

        $timeEntry->delete();

        return response()->json([
            'message' => 'Time entry deleted successfully.',
        ]);
    }

    /**
     * Members manage their own time; team leads may correct anyone's.
     */
    private function authorizeEntryOwner(Request $request, TimeEntry $timeEntry): void
    {
        $actor = Auth::user();

        abort_unless(
            $timeEntry->user_id === $actor->id || $this->canManageOthersTime($request, $actor),
            403,
            'You can only change your own time entries.'
        );
    }
}
