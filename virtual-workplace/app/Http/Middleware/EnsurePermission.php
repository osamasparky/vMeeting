<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsurePermission
{
    /**
     * Check if the current member has the required permission.
     * Must be used after EnsureOrganizationMember middleware.
     */
    public function handle(Request $request, Closure $next, string $permission): Response
    {
        $membership = $request->get('current_membership');

        if (!$membership) {
            return response()->json(['message' => 'Organization context not set.'], 403);
        }

        if (!$membership->hasPermission($permission)) {
            return response()->json([
                'message' => 'You do not have permission to perform this action.',
                'required_permission' => $permission,
            ], 403);
        }

        return $next($request);
    }
}
