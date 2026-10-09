<?php

namespace App\Domains\Projects\Controllers;

use App\Domains\Projects\Models\Task;
use App\Domains\Tenancy\Models\Organization;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

/** Follow / unfollow a task (always for the signed-in user). */
class TaskWatcherController extends Controller
{
    public function store(Organization $organization, Task $task): JsonResponse
    {
        abort_unless($task->organization_id === $organization->id, 404);
        Gate::authorize('view', $task);

        $task->addWatchers([Auth::id()]);

        return response()->json(['message' => 'Following.', 'watchers' => $task->watchers()->get(['users.id', 'users.name'])]);
    }

    public function destroy(Organization $organization, Task $task): JsonResponse
    {
        abort_unless($task->organization_id === $organization->id, 404);

        $task->watchers()->detach(Auth::id());

        return response()->json(['message' => 'Unfollowed.', 'watchers' => $task->watchers()->get(['users.id', 'users.name'])]);
    }
}
