<?php

namespace App\Http\Middleware;

use App\Domains\Projects\Models\Project;
use App\Domains\Tenancy\Models\OrganizationMember;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Any route with a {project}: a member who neither leads projects nor works
 * on this one (owner, manager, project member, assignee of one of its tasks)
 * gets a 404, as if it did not exist. Routes without a project pass through.
 */
class EnsureProjectVisible
{
    public function handle(Request $request, Closure $next): Response
    {
        $project = $request->route('project');
        $user = $request->user();

        if ($project instanceof Project && $user && ! $user->isSuperAdmin()) {
            $membership = $request->get('current_membership');
            if (! $membership || $membership->organization_id !== $project->organization_id) {
                $membership = OrganizationMember::forUser($user->id)
                    ->where('organization_id', $project->organization_id)
                    ->with('role.permissions')
                    ->first();
            }

            // Not a member at all is the controllers' call (they 403/404 already).
            if ($membership && ! $project->isVisibleTo($user, $membership)) {
                abort(404);
            }
        }

        return $next($request);
    }
}
