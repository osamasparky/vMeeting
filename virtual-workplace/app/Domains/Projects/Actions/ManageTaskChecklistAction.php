<?php

namespace App\Domains\Projects\Actions;

use App\Domains\Projects\Models\Task;
use App\Domains\Projects\Models\TaskChecklistItem;

class ManageTaskChecklistAction
{
    public function addItem(Task $task, string $title): TaskChecklistItem
    {
        $maxOrder = TaskChecklistItem::where('task_id', $task->id)->max('order') ?? 0;

        return TaskChecklistItem::create([
            'task_id' => $task->id,
            'title' => $title,
            'is_completed' => false,
            'order' => $maxOrder + 1,
        ]);
    }

    public function toggleItem(TaskChecklistItem $item): TaskChecklistItem
    {
        $item->update([
            'is_completed' => !$item->is_completed,
            'completed_at' => !$item->is_completed ? now() : null,
        ]);

        return $item;
    }

    public function deleteItem(TaskChecklistItem $item): void
    {
        $item->delete();
    }
}
