<?php

namespace App\Domains\Projects\Actions;

use App\Domains\Identity\Models\User;
use App\Domains\Projects\Models\ActiveTimer;
use App\Domains\Projects\Models\TimeEntry;
use App\Domains\Tenancy\Models\Organization;
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
                $elapsed = max(1, (int) $existing->started_at->diffInSeconds(now()));

                // Fetch member rate snapshot
                $rates = app(ResolveTimeRatesAction::class)->execute($organization, $existing->project_id, $user);

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
}
