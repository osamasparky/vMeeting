<?php

namespace App\Domains\Projects\Actions;

use App\Domains\Projects\Models\ProjectSprint;
use App\Domains\Projects\Models\Task;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class CompleteSprintAction
{
    public function __construct(private readonly BuildSprintBurndownAction $burndown) {}

    /**
     * Close a sprint: freeze its burndown, record completed points and move
     * every unfinished task to $carryTo (another open sprint of the project)
     * or back to the backlog when null.
     *
     * @return array{sprint: ProjectSprint, carried: int}
     */
    public function execute(ProjectSprint $sprint, ?ProjectSprint $carryTo): array
    {
        if ($sprint->isCompleted()) {
            throw new InvalidArgumentException('This sprint is already completed.');
        }
        if ($carryTo && ($carryTo->project_id !== $sprint->project_id || $carryTo->id === $sprint->id || $carryTo->isCompleted())) {
            throw new InvalidArgumentException('Unfinished tasks can only move to another open sprint of this project.');
        }

        return DB::transaction(function () use ($sprint, $carryTo) {
            $snapshot = $this->burndown->execute($sprint);

            $carried = $sprint->tasks()
                ->where('status', '!=', Task::STATUS_DONE)
                ->update(['sprint_id' => $carryTo?->id]);

            $sprint->update([
                'status' => ProjectSprint::STATUS_COMPLETED,
                'completed_at' => now(),
                'completed_points' => (int) round($snapshot['completed']),
                'burndown_snapshot' => $snapshot,
            ]);

            return ['sprint' => $sprint->fresh(), 'carried' => $carried];
        });
    }
}
