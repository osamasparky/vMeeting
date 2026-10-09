<?php

namespace App\Domains\Projects\Actions;

use App\Domains\Identity\Models\User;
use App\Domains\Projects\Models\ActiveTimer;
use App\Domains\Projects\Models\TimeEntry;
use App\Domains\Tenancy\Models\Organization;
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

            $elapsed = max(1, (int) $activeTimer->started_at->diffInSeconds(now()));
            $rates = app(ResolveTimeRatesAction::class)->execute($organization, $activeTimer->project_id, $user);

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
}
