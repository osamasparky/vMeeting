<?php

namespace App\Domains\Projects\Actions;

use App\Domains\Identity\Models\User;
use App\Domains\Projects\Models\TimeEntry;
use App\Domains\Projects\Models\Timesheet;
use App\Domains\Tenancy\Models\Organization;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class ApproveTimesheetAction
{
    public function execute(Organization $organization, Timesheet $timesheet, User $reviewer): Timesheet
    {
        return DB::transaction(function () use ($organization, $timesheet, $reviewer) {
            if ($timesheet->organization_id !== $organization->id) {
                throw new InvalidArgumentException('Unauthorized timesheet approval attempt.');
            }

            if ($timesheet->isApproved()) {
                return $timesheet;
            }

            $timesheet->update([
                'status' => Timesheet::STATUS_APPROVED,
                'reviewed_by' => $reviewer->id,
                'reviewed_at' => now(),
            ]);

            // Lock all linked time entries as approved
            $timesheet->timeEntries()->update([
                'status' => TimeEntry::STATUS_APPROVED,
                'approved_by' => $reviewer->id,
                'approved_at' => now(),
            ]);

            $timesheet->recalculateTotals();

            return $timesheet->fresh(['user', 'reviewer', 'timeEntries']);
        });
    }
}
