<?php

namespace App\Domains\People\Controllers;

use App\Domains\People\Services\AttendanceService;
use App\Domains\Projects\Models\ActiveTimer;
use App\Domains\Projects\Models\TimeEntry;
use App\Domains\Tenancy\Models\Organization;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class AttendanceApiController extends Controller
{
    /**
     * Get attendance summary for today.
     */
    public function summary(Request $request, Organization $organization, AttendanceService $attendance): JsonResponse
    {
        $user = $request->user();

        // Check for an active running timer
        $activeTimer = ActiveTimer::where('user_id', $user->id)
            ->where('organization_id', $organization->id)
            ->first();

        // Task time today (organization's timezone), running timer included — same figure as the web timesheet.
        $day = $attendance->getDailyTimesheetData($user->id, $organization->id);
        $totalSeconds = (int) $day['total_task_seconds'];

        return response()->json([
            'is_clocked_in' => $activeTimer !== null,
            'clock_in_time' => $activeTimer ? $activeTimer->started_at : null,
            'today_total_seconds' => $totalSeconds,
            'today_total_formatted' => sprintf('%02d:%02d:%02d', intdiv($totalSeconds, 3600), intdiv($totalSeconds % 3600, 60), $totalSeconds % 60),
            'date' => $day['date'],
        ]);
    }

    /**
     * Clock in — start attendance session.
     */
    public function clockIn(Request $request, Organization $organization): JsonResponse
    {
        $user = $request->user();

        $existingTimer = ActiveTimer::where('user_id', $user->id)
            ->where('organization_id', $organization->id)
            ->first();

        if ($existingTimer) {
            return response()->json([
                'message' => 'You are already clocked in.',
                'timer' => $existingTimer,
            ], 200);
        }

        $timer = ActiveTimer::create([
            'organization_id' => $organization->id,
            'user_id' => $user->id,
            'started_at' => now(),
            'description' => $request->input('description', 'General Work Shift'),
        ]);

        return response()->json([
            'message' => 'Clocked in successfully.',
            'timer' => $timer,
        ], 201);
    }

    /**
     * Clock out — stop attendance session and log TimeEntry.
     */
    public function clockOut(Request $request, Organization $organization): JsonResponse
    {
        $user = $request->user();

        $activeTimer = ActiveTimer::where('user_id', $user->id)
            ->where('organization_id', $organization->id)
            ->first();

        if (! $activeTimer) {
            return response()->json([
                'message' => 'No active clock-in session found.',
            ], 400);
        }

        $startedAt = Carbon::parse($activeTimer->started_at);
        $endedAt = now();
        $durationSeconds = max(1, $endedAt->getTimestamp() - $startedAt->getTimestamp());
        $durationMinutes = intdiv($durationSeconds, 60);

        // duration_seconds is the column; the old duration_minutes key was silently dropped, booking 0.
        $timeEntry = TimeEntry::create([
            'organization_id' => $organization->id,
            'user_id' => $user->id,
            'project_id' => $activeTimer->project_id,
            'task_id' => $activeTimer->task_id,
            'started_at' => $startedAt,
            'ended_at' => $endedAt,
            'duration_seconds' => $durationSeconds,
            'description' => $activeTimer->description ?? 'General Work Shift',
            'status' => 'approved',
        ]);

        $activeTimer->delete();

        return response()->json([
            'message' => 'Clocked out successfully.',
            'time_entry' => $timeEntry,
            'duration_minutes' => $durationMinutes,
        ]);
    }

    /**
     * Get monthly attendance logs.
     */
    public function logs(Request $request, Organization $organization): JsonResponse
    {
        $user = $request->user();
        $month = $request->input('month', Carbon::now()->month);
        $year = $request->input('year', Carbon::now()->year);

        $entries = TimeEntry::where('user_id', $user->id)
            ->where('organization_id', $organization->id)
            ->whereMonth('started_at', $month)
            ->whereYear('started_at', $year)
            ->orderBy('started_at', 'desc')
            ->paginate(30);

        return response()->json($entries);
    }
}
