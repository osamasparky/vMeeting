<?php

namespace App\Domains\Projects\Controllers;

use App\Domains\Notifications\Services\NotificationService;
use App\Domains\Projects\Models\Project;
use App\Domains\Projects\Models\ProjectUpdate;
use App\Domains\Tenancy\Models\Organization;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

/** Project status updates (health + note); the latest one is the project's health. */
class ProjectUpdateController extends Controller
{
    public function index(Organization $organization, Project $project): JsonResponse
    {
        abort_unless($project->organization_id === $organization->id, 404);

        return response()->json([
            'updates' => $project->updates()->with('user:id,name')->latest()->limit(50)->get(),
        ]);
    }

    public function store(Request $request, Organization $organization, Project $project): JsonResponse
    {
        abort_unless($project->organization_id === $organization->id, 404);

        $validated = $request->validate([
            'health' => ['required', Rule::in(ProjectUpdate::HEALTH)],
            'body' => ['required', 'string', 'max:5000'],
        ]);

        $update = $project->updates()->create([
            'organization_id' => $organization->id,
            'user_id' => Auth::id(),
            'health' => $validated['health'],
            'body' => $validated['body'],
        ]);

        // Everyone on the project hears about it (the author excepted).
        $recipients = $project->members()->pluck('user_id')
            ->push($project->manager_id, $project->owner_id)->filter()->unique()
            ->reject(fn ($id) => $id === Auth::id());
        foreach ($recipients as $userId) {
            NotificationService::send(
                $userId,
                'project_update',
                __('📣 :project: :health', ['project' => $project->name, 'health' => __('pm.health_'.$update->health)]),
                Str::limit($update->body, 160),
                '/projects/'.$project->id.'#overview',
                ['project_id' => $project->id, 'update_id' => $update->id],
                '📣',
                $organization->id,
            );
        }

        return response()->json(['message' => 'Update posted.', 'update' => $update->load('user:id,name')], 201);
    }

    public function destroy(Request $request, Organization $organization, Project $project, ProjectUpdate $update): JsonResponse
    {
        abort_unless($project->organization_id === $organization->id && $update->project_id === $project->id, 404);

        $user = Auth::user();
        $mayModerate = $user->isSuperAdmin() || $project->manager_id === $user->id
            || $request->get('current_membership')?->hasPermission('projects.edit');
        abort_unless($update->user_id === $user->id || $mayModerate, 403);

        $update->delete();

        return response()->json(['message' => 'Update deleted.']);
    }
}
