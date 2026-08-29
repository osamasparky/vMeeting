<?php

namespace App\Console\Commands;

use App\Domains\Projects\Actions\ProcessRecurringTaskAction;
use App\Domains\Projects\Models\Task;
use Illuminate\Console\Command;

class ProcessRecurringTasksCommand extends Command
{
    protected $signature = 'tasks:process-recurring';
    protected $description = 'Process and spawn overdue completed or scheduled recurring tasks';

    public function handle(ProcessRecurringTaskAction $action): int
    {
        $this->info('Processing recurring tasks...');

        $recurringTasks = Task::whereNotNull('recurrence_rule')
            ->where('status', Task::STATUS_DONE)
            ->whereNull('last_recurred_at')
            ->get();

        $count = 0;
        foreach ($recurringTasks as $task) {
            $next = $action->execute($task);
            if ($next) {
                $count++;
                $this->line("Created recurring task #{$next->task_number} for '{$next->title}'");
            }
        }

        $this->info("Successfully processed {$count} recurring task(s).");
        return self::SUCCESS;
    }
}
