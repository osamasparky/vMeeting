<?php

namespace App\Http\Middleware;

use App\Domains\Tenancy\Models\Organization;
use App\Domains\Tenancy\Models\OrganizationMember;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureOrganizationMember
{
    /**
     * Verify the authenticated user is a member of the requested organization.
     * Binds the organization and membership to the request for downstream use.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $organization = $request->route('organization');

        if (!$organization instanceof Organization) {
            $organization = Organization::where('id', $organization)
                ->orWhere('slug', $organization)
                ->first();
        }

        if (!$organization) {
            return response()->json(['message' => 'Organization not found.'], 404);
        }

        if (!$organization->isActive()) {
            return response()->json(['message' => 'Organization is not active.'], 403);
        }

        $user = $request->user();

        $membership = OrganizationMember::where('organization_id', $organization->id)
            ->where('user_id', $user->id)
            ->where('status', 'active')
            ->first();

        if (!$membership) {
            return response()->json(['message' => 'You are not a member of this organization.'], 403);
        }

        // Bind to request for downstream access
        $request->merge([
            'current_organization' => $organization,
            'current_membership' => $membership,
        ]);

        // Replace route parameter with model instance
        $request->route()->setParameter('organization', $organization);

        return $next($request);
    }
}
