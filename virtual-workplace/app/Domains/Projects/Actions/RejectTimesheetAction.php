<?php

namespace App\Domains\Projects\Actions;

use App\Domains\Identity\Models\User;
use App\Domains\Projects\Models\TimeEntry;
use App\Domains\Projects\Models\Timesheet;
use App\Domains\Tenancy\Models\Organization;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class RejectTimesheetAction
{
    public function execute(Organization $organization, Timesheet $timesheet, User $reviewer, string $reason): Timesheet
    {
        return DB::transaction(function () use ($organization, $timesheet, $reviewer, $reason) {
            if ($timesheet->organization_id !== $organization->id) {
                throw new InvalidArgumentException('Unauthorized timesheet rejection attempt.');
            }

            if ($timesheet->isApproved()) {
                throw new InvalidArgumentException('Approved timesheets cannot be rejected.');
            }

            $timesheet->update([
                'status' => Timesheet::STATUS_REJECTED,
                'reviewed_by' => $reviewer->id,
                'reviewed_at' => now(),
                'rejection_reason' => $reason,
            ]);

            // Unlock linked time entries to rejected/editable status
            $timesheet->timeEntries()->update([
                'status' => TimeEntry::STATUS_REJECTED,
            ]);

            return $timesheet->fresh(['user', 'reviewer', 'timeEntries']);
        });
    }
}
