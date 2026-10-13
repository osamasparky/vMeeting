<?php

namespace App\Domains\Projects\Actions;

use App\Domains\Identity\Models\User;
use App\Domains\Projects\Models\TimeEntry;
use App\Domains\Tenancy\Models\Organization;
use App\Domains\Workspace\Services\OfficeTimezone;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class LogManualTimeAction
{
    public function execute(array $data, Organization $organization, User $user): TimeEntry
    {
        return DB::transaction(function () use ($data, $organization, $user) {
            // Typed in the member's office time unless an offset is given; stored in UTC.
            $tz = app(OfficeTimezone::class)->forMember($user->id, $organization->id);
            $startedAt = OfficeTimezone::toStorage($data['started_at'], $tz);
            $endedAt = OfficeTimezone::toStorage($data['ended_at'], $tz);

            if ($endedAt->lessThanOrEqualTo($startedAt)) {
                throw new InvalidArgumentException('End time must be strictly after start time.');
            }

            $duration = $data['duration_seconds'] ?? (int) $startedAt->diffInSeconds($endedAt);
            if ($duration <= 0) {
                throw new InvalidArgumentException('Duration must be greater than zero.');
            }

            $rates = app(ResolveTimeRatesAction::class)->execute($organization, $data['project_id'], $user);

            return TimeEntry::create([
                'organization_id' => $organization->id,
                'user_id' => $user->id,
                'project_id' => $data['project_id'],
                'task_id' => $data['task_id'] ?? null,
                'started_at' => $startedAt,
                'ended_at' => $endedAt,
                'duration_seconds' => $duration,
                'description' => $data['description'] ?? null,
                'is_billable' => $data['is_billable'] ?? true,
                'cost_rate' => $rates['cost_rate'],
                'billing_rate' => $rates['billing_rate'],
                'entry_type' => TimeEntry::TYPE_MANUAL,
                'status' => TimeEntry::STATUS_DRAFT,
            ])->load(['project', 'task']);
        });
    }
}
