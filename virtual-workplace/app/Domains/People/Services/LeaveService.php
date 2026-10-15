<?php

namespace App\Domains\People\Services;

use App\Domains\Identity\Models\User;
use App\Domains\Notifications\Services\NotificationService;
use App\Domains\People\Models\LeaveRequest;
use App\Domains\Tenancy\Models\Organization;
use App\Domains\Tenancy\Models\OrganizationMember;
use App\Domains\Workspace\Models\Floor;
use App\Domains\Workspace\Services\OfficeTimezone;
use Carbon\CarbonPeriod;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Leave requests: counting working days, the annual balance, and the
 * request → owner decision flow. Only the company owner decides, as with
 * timesheets.
 */
class LeaveService
{
    public function __construct(
        protected OfficeTimezone $offices,
        protected HolidayCalendar $holidays,
    ) {}

    /** The member's home office (or the office they work to), for its working days. */
    public function officeFor(string $userId, string $organizationId): ?Floor
    {
        $id = $this->offices->officeIdForMember($userId, $organizationId);

        return $id ? Floor::withoutGlobalScopes()->find($id) : null;
    }

    /** Working days in [from, to]: the office's working days that are not official holidays. */
    public function workingDays(string $userId, string $organizationId, string $from, string $to): int
    {
        if ($to < $from) {
            return 0;
        }

        $office = $this->officeFor($userId, $organizationId);
        $holidays = $this->holidays->between($organizationId, $office?->id, $from, $to);
        $workDays = $office?->workDays() ?? Floor::DEFAULT_WORK_DAYS;

        $count = 0;
        foreach (CarbonPeriod::create($from, $to) as $day) {
            if (in_array($day->dayOfWeek, $workDays, true) && ! isset($holidays[$day->format('Y-m-d')])) {
                $count++;
            }
        }

        return $count;
    }

    /**
     * Annual leave for a calendar year: entitlement, days taken (approved),
     * days waiting for a decision, and what is left to request.
     *
     * @return array{year: int, entitled: int, used: int, pending: int, remaining: int}
     */
    public function balance(string $userId, string $organizationId, int $year): array
    {
        $entitled = $this->entitlement($userId, $organizationId);
        [$from, $to] = ["{$year}-01-01", "{$year}-12-31"];

        $requests = LeaveRequest::withoutGlobalScopes()
            ->where('organization_id', $organizationId)->where('user_id', $userId)
            ->where('type', 'annual')->active()
            ->where('start_date', '<=', $to)->where('end_date', '>=', $from)
            ->get();

        $used = 0;
        $pending = 0;
        foreach ($requests as $r) {
            // A request crossing new year counts each part against its own year.
            $days = $this->workingDays($userId, $organizationId, max($r->start_date->format('Y-m-d'), $from), min($r->end_date->format('Y-m-d'), $to));
            $r->status === LeaveRequest::STATUS_APPROVED ? $used += $days : $pending += $days;
        }

        return ['year' => $year, 'entitled' => $entitled, 'used' => $used, 'pending' => $pending, 'remaining' => $entitled - $used - $pending];
    }

    /** Yearly leave days: the member's own figure when the owner set one, otherwise their office's. */
    public function entitlement(string $userId, string $organizationId): int
    {
        $own = OrganizationMember::where('organization_id', $organizationId)->where('user_id', $userId)->value('annual_leave_days');

        return (int) ($own ?? $this->officeFor($userId, $organizationId)?->annual_leave_days ?? Floor::DEFAULT_ANNUAL_LEAVE_DAYS);
    }

    public function request(Organization $organization, User $user, array $data): LeaveRequest
    {
        return DB::transaction(function () use ($organization, $user, $data) {
            // Serialize a member's requests so two at once cannot both pass the checks.
            DB::table('users')->where('id', $user->id)->lockForUpdate()->first();

            [$from, $to] = [$data['start_date'], $data['end_date']];

            $days = $this->workingDays($user->id, $organization->id, $from, $to);
            if ($days === 0) {
                throw ValidationException::withMessages(['start_date' => __('These dates have no working days — they are weekends or official holidays.')]);
            }

            $overlap = LeaveRequest::withoutGlobalScopes()
                ->where('organization_id', $organization->id)->where('user_id', $user->id)->active()
                ->where('start_date', '<=', $to)->where('end_date', '>=', $from)
                ->exists();
            if ($overlap) {
                throw ValidationException::withMessages(['start_date' => __('You already have a leave request covering some of these dates.')]);
            }

            if ($data['type'] === 'annual') {
                $this->assertAnnualBalance($user->id, $organization->id, $from, $to);
            }

            $leave = LeaveRequest::create([
                'organization_id' => $organization->id,
                'user_id' => $user->id,
                'type' => $data['type'],
                'start_date' => $from,
                'end_date' => $to,
                'days' => $days,
                'reason' => $data['reason'] ?? null,
                'status' => LeaveRequest::STATUS_PENDING,
            ]);

            $this->notifyOwners($organization, $leave, $user);

            return $leave;
        });
    }

    public function approve(LeaveRequest $leave, User $owner, ?string $note = null): LeaveRequest
    {
        return $this->decide($leave, $owner, LeaveRequest::STATUS_APPROVED, $note);
    }

    public function reject(LeaveRequest $leave, User $owner, ?string $note = null): LeaveRequest
    {
        return $this->decide($leave, $owner, LeaveRequest::STATUS_REJECTED, $note);
    }

    public function cancel(LeaveRequest $leave): LeaveRequest
    {
        if (! $leave->isPending()) {
            throw ValidationException::withMessages(['leave' => __('Only a pending request can be cancelled.')]);
        }

        $leave->update(['status' => LeaveRequest::STATUS_CANCELLED]);

        return $leave;
    }

    private function decide(LeaveRequest $leave, User $owner, string $status, ?string $note): LeaveRequest
    {
        if (! $leave->isPending()) {
            throw ValidationException::withMessages(['leave' => __('This request has already been decided.')]);
        }

        $leave->update([
            'status' => $status,
            'reviewed_by' => $owner->id,
            'reviewed_at' => now(),
            'review_note' => $note,
        ]);

        $approved = $status === LeaveRequest::STATUS_APPROVED;
        NotificationService::notifyCustom(
            $leave->user_id,
            'leave',
            fn () => $approved ? __('Your leave request was approved') : __('Your leave request was rejected'),
            fn () => $leave->typeLabel().' · '.$leave->start_date->format('Y-m-d').' → '.$leave->end_date->format('Y-m-d').($note ? ' · '.$note : ''),
            ['leave_request_id' => $leave->id],
            $owner->id,
            $approved ? '✅' : '❌',
            '/dashboard#leaves',
            $leave->organization_id,
        );

        return $leave;
    }

    /** Annual days requested must fit in what is left for each calendar year the range touches. */
    private function assertAnnualBalance(string $userId, string $organizationId, string $from, string $to): void
    {
        for ($year = (int) substr($from, 0, 4); $year <= (int) substr($to, 0, 4); $year++) {
            $needed = $this->workingDays($userId, $organizationId, max($from, "{$year}-01-01"), min($to, "{$year}-12-31"));
            $remaining = $this->balance($userId, $organizationId, $year)['remaining'];
            if ($needed > $remaining) {
                throw ValidationException::withMessages(['type' => __('Not enough annual leave left for :year: :remaining day(s) remaining, :needed requested.', [
                    'year' => $year, 'remaining' => max(0, $remaining), 'needed' => $needed,
                ])]);
            }
        }
    }

    private function notifyOwners(Organization $organization, LeaveRequest $leave, User $requester): void
    {
        $owners = OrganizationMember::where('organization_id', $organization->id)
            ->whereHas('role', fn ($q) => $q->where('slug', 'company_admin'))
            ->pluck('user_id');

        foreach ($owners as $ownerId) {
            NotificationService::notifyCustom(
                $ownerId,
                'leave',
                fn () => __(':name requested leave', ['name' => $requester->name]),
                fn () => $leave->typeLabel().' · '.$leave->start_date->format('Y-m-d').' → '.$leave->end_date->format('Y-m-d').' · '.trans_choice(':count working day|:count working days', $leave->days),
                ['leave_request_id' => $leave->id],
                $requester->id,
                '🌴',
                '/dashboard#leaves',
                $organization->id,
            );
        }
    }
}
