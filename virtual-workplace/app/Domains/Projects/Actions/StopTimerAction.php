<?php

namespace App\Domains\Projects\Actions;

use App\Domains\Identity\Models\User;
use App\Domains\Projects\Models\ActiveTimer;
use App\Domains\Projects\Models\ProjectMember;
use App\Domains\Projects\Models\TimeEntry;
use App\Domains\Tenancy\Models\Organization;
use App\Domains\Tenancy\Models\OrganizationMember;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class StopTimerAction
{
    public function execute(Organization $organization, User $user, ?string $description = null): TimeEntry
    {
        return DB::transaction(function () use ($organization, $user, $description) {
            $activeTimer = ActiveTimer::where('user_id', $user->id)->lockForUpdate()->first();

            if (! $activeTimer) {
                throw new InvalidArgumentException('No active running timer found to stop.');
            }

            $elapsed = max(1, now()->diffInSeconds($activeTimer->started_at));
            $rates = $this->resolveRates($organization, $activeTimer->project_id, $user);

            $entry = TimeEntry::create([
                'organization_id' => $activeTimer->organization_id,
                'user_id' => $activeTimer->user_id,
                'project_id' => $activeTimer->project_id,
                'task_id' => $activeTimer->task_id,
                'started_at' => $activeTimer->started_at,
                'ended_at' => now(),
                'duration_seconds' => $elapsed,
                'description' => $description ?? $activeTimer->description ?? 'Timer work session',
                'is_billable' => $activeTimer->is_billable,
                'cost_rate' => $rates['cost_rate'],
                'billing_rate' => $rates['billing_rate'],
                'entry_type' => TimeEntry::TYPE_TIMER,
                'status' => TimeEntry::STATUS_DRAFT,
            ]);

            $activeTimer->delete();

            return $entry->load(['project', 'task']);
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
