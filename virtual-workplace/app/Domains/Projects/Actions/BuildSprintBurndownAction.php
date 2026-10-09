<?php

namespace App\Domains\Projects\Actions;

use App\Domains\Projects\Models\ProjectSprint;
use App\Domains\Projects\Models\Task;
use Carbon\CarbonPeriod;

class BuildSprintBurndownAction
{
    /**
     * Remaining work per day across the sprint, plus the ideal straight line.
     * Weighs by story points when the sprint has any, otherwise counts tasks.
     * Days after today are null so the line stops at "now".
     *
     * @return array{unit: string, total: float, completed: float, days: list<array{date: string, remaining: ?float, ideal: float}>}
     */
    public function execute(ProjectSprint $sprint): array
    {
        if ($sprint->isCompleted() && $sprint->burndown_snapshot) {
            return $sprint->burndown_snapshot;
        }

        $tasks = $sprint->tasks()->get(['id', 'status', 'story_points', 'completed_at']);
        $usePoints = $tasks->contains(fn (Task $t) => (int) $t->story_points > 0);
        $weight = fn (Task $t) => $usePoints ? (float) ($t->story_points ?? 0) : 1.0;

        $total = (float) $tasks->sum($weight);
        $done = $tasks->filter(fn (Task $t) => $t->status === Task::STATUS_DONE);

        $start = $sprint->start_date->copy()->startOfDay();
        $end = $sprint->end_date->copy()->startOfDay();
        if ($end->lt($start)) {
            $end = $start->copy();
        }
        $period = CarbonPeriod::create($start, $end);
        $span = max(1, $period->count() - 1);
        $today = now()->startOfDay();

        $days = [];
        foreach ($period as $i => $day) {
            $dayEnd = $day->copy()->endOfDay();
            $burned = $done
                ->filter(fn (Task $t) => $t->completed_at && $t->completed_at->lte($dayEnd))
                ->sum($weight);
            $days[] = [
                'date' => $day->toDateString(),
                'remaining' => $day->gt($today) ? null : round($total - $burned, 2),
                'ideal' => round($total * (1 - $i / $span), 2),
            ];
        }

        return [
            'unit' => $usePoints ? 'points' : 'tasks',
            'total' => $total,
            'completed' => (float) $done->sum($weight),
            'days' => $days,
        ];
    }
}
