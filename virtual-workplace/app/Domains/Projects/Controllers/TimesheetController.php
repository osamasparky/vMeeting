<?php

namespace App\Domains\Projects\Controllers;

use App\Domains\Projects\Actions\ApproveTimesheetAction;
use App\Domains\Projects\Actions\RejectTimesheetAction;
use App\Domains\Projects\Actions\SubmitTimesheetAction;
use App\Domains\Projects\Models\Timesheet;
use App\Domains\Projects\Requests\RejectTimesheetRequest;
use App\Domains\Projects\Requests\SubmitTimesheetRequest;
use App\Domains\Tenancy\Models\Organization;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;

class TimesheetController extends Controller
{
    /**
     * List timesheets (review queue for managers, or personal history).
     */
    public function index(Request $request, Organization $organization): JsonResponse
    {
        $user = Auth::user();
        $membership = $request->get('current_membership');

        $query = Timesheet::forOrganization($organization->id)
            ->with(['user:id,name,email', 'reviewer:id,name,email']);

        if (! $membership || (! $membership->hasPermission('timesheets.approve') && $membership->role?->slug === 'employee')) {
            $query->where('user_id', $user->id);
        } elseif ($request->filled('user_id')) {
            $query->where('user_id', $request->query('user_id'));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->query('status'));
        }

        $timesheets = $query->orderByDesc('period_start')->paginate($request->integer('per_page', 20));

        return response()->json($timesheets);
    }

    /**
     * Get or initialize current week timesheet for logged in user.
     */
    public function myCurrent(Request $request, Organization $organization): JsonResponse
    {
        $user = Auth::user();
        $start = now()->startOfWeek()->toDateString();
        $end = now()->endOfWeek()->toDateString();

        $timesheet = Timesheet::firstOrCreate(
            [
                'organization_id' => $organization->id,
                'user_id' => $user->id,
                'period_start' => $start,
            ],
            [
                'period_end' => $end,
                'status' => Timesheet::STATUS_DRAFT,
            ]
        );

        $timesheet->load(['timeEntries.project:id,name,code', 'timeEntries.task:id,title,task_number']);
        $timesheet->recalculateTotals();

        return response()->json([
            'timesheet' => $timesheet->fresh(['timeEntries.project', 'timeEntries.task']),
        ]);
    }

    /**
     * Show a specific timesheet with entries.
     */
    public function show(Organization $organization, Timesheet $timesheet): JsonResponse
    {
        if ($timesheet->organization_id !== $organization->id) {
            return response()->json(['message' => 'Timesheet not found.'], 404);
        }

        $timesheet->load(['user', 'reviewer', 'timeEntries.project', 'timeEntries.task']);

        return response()->json([
            'timesheet' => $timesheet,
        ]);
    }

    /**
     * Submit timesheet for approval.
     */
    public function submit(
        SubmitTimesheetRequest $request,
        Organization $organization,
        SubmitTimesheetAction $action
    ): JsonResponse {
        $timesheet = $action->execute(
            $organization,
            Auth::user(),
            $request->validated('period_start'),
            $request->validated('period_end')
        );

        return response()->json([
            'message' => 'Timesheet submitted successfully for approval.',
            'timesheet' => $timesheet,
        ]);
    }

    /**
     * Approve timesheet (Manager / Admin).
     */
    public function approve(
        Request $request,
        Organization $organization,
        Timesheet $timesheet,
        ApproveTimesheetAction $action
    ): JsonResponse {
        $approved = $action->execute($organization, $timesheet, Auth::user());

        return response()->json([
            'message' => 'Timesheet approved and locked.',
            'timesheet' => $approved,
        ]);
    }

    /**
     * Reject timesheet with feedback note (Manager / Admin).
     */
    public function reject(
        RejectTimesheetRequest $request,
        Organization $organization,
        Timesheet $timesheet,
        RejectTimesheetAction $action
    ): JsonResponse {
        $rejected = $action->execute(
            $organization,
            $timesheet,
            Auth::user(),
            $request->validated('rejection_reason')
        );

        return response()->json([
            'message' => 'Timesheet rejected and returned for revisions.',
            'timesheet' => $rejected,
        ]);
    }
}
