<?php

namespace App\Domains\Projects\Actions;

use App\Domains\Identity\Models\User;
use App\Domains\Projects\Models\ActiveTimer;
use App\Domains\Projects\Models\ProjectMember;
use App\Domains\Projects\Models\TimeEntry;
use App\Domains\Tenancy\Models\Organization;
use App\Domains\Tenancy\Models\OrganizationMember;
use Illuminate\Support\Facades\DB;

class StartTimerAction
{
    public function execute(array $data, Organization $organization, User $user): ActiveTimer
    {
        return DB::transaction(function () use ($data, $organization, $user) {
            // 1. Check for any existing active timer for this user
            $existing = ActiveTimer::where('user_id', $user->id)->lockForUpdate()->first();

            if ($existing) {
                // Calculate elapsed duration in seconds
                $elapsed = max(1, now()->diffInSeconds($existing->started_at));

                // Fetch member rate snapshot
                $rates = $this->resolveRates($organization, $existing->project_id, $user);

                // Convert existing timer to completed TimeEntry
                TimeEntry::create([
                    'organization_id' => $existing->organization_id,
                    'user_id' => $existing->user_id,
                    'project_id' => $existing->project_id,
                    'task_id' => $existing->task_id,
                    'started_at' => $existing->started_at,
                    'ended_at' => now(),
                    'duration_seconds' => $elapsed,
                    'description' => $existing->description ?? 'Timer work session',
                    'is_billable' => $existing->is_billable,
                    'cost_rate' => $rates['cost_rate'],
                    'billing_rate' => $rates['billing_rate'],
                    'entry_type' => TimeEntry::TYPE_TIMER,
                    'status' => TimeEntry::STATUS_DRAFT,
                ]);

                // Remove existing active timer
                $existing->delete();
            }

            // 2. Create the new active timer
            return ActiveTimer::create([
                'organization_id' => $organization->id,
                'user_id' => $user->id,
                'project_id' => $data['project_id'],
                'task_id' => $data['task_id'] ?? null,
                'started_at' => now(),
                'description' => $data['description'] ?? null,
                'is_billable' => $data['is_billable'] ?? true,
            ])->load(['project', 'task']);
        });
    }

    /**
     * Resolve cost and billing rates for member snapshot.
     */
    private function resolveRates(Organization $organization, string $projectId, User $user): array
    {
        // 1. Check project-specific override
        $projectMember = ProjectMember::where('project_id', $projectId)->where('user_id', $user->id)->first();
        if ($projectMember && ($projectMember->cost_rate > 0 || $projectMember->billing_rate > 0)) {
            return [
                'cost_rate' => $projectMember->cost_rate ?? 0.00,
                'billing_rate' => $projectMember->billing_rate ?? 0.00,
            ];
        }

        // 2. Fall back to organization member rate
        $orgMember = OrganizationMember::where('organization_id', $organization->id)->where('user_id', $user->id)->first();
        return [
            'cost_rate' => $orgMember->cost_rate ?? 0.00,
            'billing_rate' => $orgMember->billing_rate ?? 0.00,
        ];
    }
}
