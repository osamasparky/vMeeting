<?php

namespace App\Domains\Projects\Actions;

use App\Domains\Identity\Models\User;
use App\Domains\Projects\Models\ProjectMember;
use App\Domains\Projects\Models\TimeEntry;
use App\Domains\Tenancy\Models\Organization;
use App\Domains\Tenancy\Models\OrganizationMember;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class LogManualTimeAction
{
    public function execute(array $data, Organization $organization, User $user): TimeEntry
    {
        return DB::transaction(function () use ($data, $organization, $user) {
            $startedAt = Carbon::parse($data['started_at']);
            $endedAt = Carbon::parse($data['ended_at']);

            if ($endedAt->lessThanOrEqualTo($startedAt)) {
                throw new InvalidArgumentException('End time must be strictly after start time.');
            }

            $duration = $data['duration_seconds'] ?? $endedAt->diffInSeconds($startedAt);
            if ($duration <= 0) {
                throw new InvalidArgumentException('Duration must be greater than zero.');
            }

            $rates = $this->resolveRates($organization, $data['project_id'], $user);

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

    private function resolveRates(Organization $organization, string $projectId, User $user): array
    {
        $projectMember = ProjectMember::where('project_id', $projectId)->where('user_id', $user->id)->first();
        if ($projectMember && ($projectMember->cost_rate > 0 || $projectMember->billing_rate > 0)) {
            return [
                'cost_rate' => $projectMember->cost_rate ?? 0.00,
                'billing_rate' => $projectMember->billing_rate ?? 0.00,
            ];
        }

        $orgMember = OrganizationMember::where('organization_id', $organization->id)->where('user_id', $user->id)->first();
        return [
            'cost_rate' => $orgMember->cost_rate ?? 0.00,
            'billing_rate' => $orgMember->billing_rate ?? 0.00,
        ];
    }
}
