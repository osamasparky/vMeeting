<?php

namespace App\Policies;

use App\Domains\Identity\Models\User;
use App\Domains\Projects\Models\Task;
use App\Domains\Tenancy\Models\OrganizationMember;

class TaskPolicy
{
    /**
     * Determine whether the user can view the task.
     */
    public function view(User $user, Task $task): bool
    {
        $membership = OrganizationMember::where('user_id', $user->id)
            ->where('organization_id', $task->organization_id)
            ->first();

        return (bool) $membership;
    }

    /**
     * Determine whether the user can update the task.
     */
    public function update(User $user, Task $task): bool
    {
        $membership = OrganizationMember::where('user_id', $user->id)
            ->where('organization_id', $task->organization_id)
            ->first();

        if (! $membership) {
            return false;
        }

        return $user->isSuperAdmin()
            || $membership->role?->slug === 'company_admin'
            || $membership->hasPermission('tasks.assign')
            || $membership->hasPermission('tasks.delete')
            || ($task->project && $task->project->manager_id === $user->id)
            || $task->assignee_id === $user->id
            || $task->reporter_id === $user->id;
    }

    /**
     * Determine whether the user can delete the task.
     */
    public function delete(User $user, Task $task): bool
    {
        $membership = OrganizationMember::where('user_id', $user->id)
            ->where('organization_id', $task->organization_id)
            ->first();

        if (! $membership) {
            return false;
        }

        return $user->isSuperAdmin()
            || $membership->role?->slug === 'company_admin'
            || $membership->hasPermission('tasks.delete')
            || ($task->project && $task->project->manager_id === $user->id);
    }
}
