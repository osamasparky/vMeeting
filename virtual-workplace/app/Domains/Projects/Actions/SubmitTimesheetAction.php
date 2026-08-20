<?php

namespace App\Domains\Projects\Actions;

use App\Domains\Identity\Models\User;
use App\Domains\Projects\Models\TimeEntry;
use App\Domains\Projects\Models\Timesheet;
use App\Domains\Tenancy\Models\Organization;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class SubmitTimesheetAction
{
    public function execute(Organization $organization, User $user, string $periodStart, string $periodEnd): Timesheet
    {
        return DB::transaction(function () use ($organization, $user, $periodStart, $periodEnd) {
            $start = Carbon::parse($periodStart)->startOfDay();
            $end = Carbon::parse($periodEnd)->endOfDay();

            // Find or create the timesheet record
            $timesheet = Timesheet::firstOrCreate(
                [
                    'organization_id' => $organization->id,
                    'user_id' => $user->id,
                    'period_start' => $start->toDateString(),
                ],
                [
                    'period_end' => $end->toDateString(),
                    'status' => Timesheet::STATUS_DRAFT,
                ]
            );

            if ($timesheet->isApproved()) {
                throw new InvalidArgumentException('Timesheet for this period is already approved and locked.');
            }

            // Find all draft or rejected time entries for this user in this date range
            $entries = TimeEntry::where('organization_id', $organization->id)
                ->where('user_id', $user->id)
                ->whereBetween('started_at', [$start, $end])
                ->whereIn('status', [TimeEntry::STATUS_DRAFT, TimeEntry::STATUS_REJECTED])
                ->get();

            foreach ($entries as $entry) {
                $entry->update([
                    'timesheet_id' => $timesheet->id,
                    'status' => TimeEntry::STATUS_SUBMITTED,
                ]);
            }

            $timesheet->update([
                'status' => Timesheet::STATUS_SUBMITTED,
                'submitted_at' => now(),
                'rejection_reason' => null,
            ]);

            $timesheet->recalculateTotals();

            return $timesheet->fresh(['user', 'timeEntries.project', 'timeEntries.task']);
        });
    }
}
