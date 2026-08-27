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
        $targetUser = Auth::user();
        if ($request->filled('user_id') && $request->input('user_id') !== $targetUser->id) {
            $membership = OrganizationMember::where('organization_id', $organization->id)
                ->where('user_id', $targetUser->id)
                ->first();
            $isPrivileged = $targetUser->isSuperAdmin()
                || ($membership && ($membership->role?->slug === 'company_admin' || $membership->hasPermission('time.create') || $membership->hasPermission('tasks.assign')));

            if ($isPrivileged) {
                $targetUser = User::findOrFail($request->input('user_id'));
            }
        }

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
        $targetUser = Auth::user();
        if ($request->filled('user_id') && $request->input('user_id') !== $targetUser->id) {
            $membership = OrganizationMember::where('organization_id', $organization->id)
                ->where('user_id', $targetUser->id)
                ->first();
            $isPrivileged = $targetUser->isSuperAdmin()
                || ($membership && ($membership->role?->slug === 'company_admin' || $membership->hasPermission('time.create')));

            if ($isPrivileged) {
                $targetUser = User::findOrFail($request->input('user_id'));
            }
        }

        $entry = $action->execute($organization, $targetUser, $request->input('description'));

        return response()->json([
            'message' => 'Timer stopped and time entry logged.',
            'time_entry' => $entry,
        ]);
    }

    /**
     * Log time entry manually.
     */
    public function logManual(
        LogManualTimeRequest $request,
        Organization $organization,
        LogManualTimeAction $action
    ): JsonResponse {
        $entry = $action->execute($request->validated(), $organization, Auth::user());

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

        $timeEntry->update($request->validated());

        return response()->json([
            'message' => 'Time entry updated successfully.',
            'time_entry' => $timeEntry->fresh(['project', 'task']),
        ]);
    }

    /**
     * Delete an unlocked time entry.
     */
    public function destroy(Organization $organization, TimeEntry $timeEntry): JsonResponse
    {
        if ($timeEntry->organization_id !== $organization->id) {
            return response()->json(['message' => 'Time entry not found.'], 404);
        }

        if ($timeEntry->isLocked()) {
            return response()->json([
                'message' => 'Cannot delete locked time entry.',
            ], 422);
        }

        $timeEntry->delete();

        return response()->json([
            'message' => 'Time entry deleted successfully.',
        ]);
    }
}
