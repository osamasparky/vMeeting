<?php

namespace App\Domains\Administration\Controllers;

use App\Domains\Administration\Models\AuditLog;
use App\Domains\Administration\Models\Role;
use App\Domains\Tenancy\Models\Organization;
use App\Domains\Tenancy\Models\OrganizationMember;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class AdminController extends Controller
{
    /**
     * List available roles.
     */
    public function listRoles(Request $request, Organization $organization): JsonResponse
    {
        $roles = Role::whereNull('organization_id')
            ->orWhere('organization_id', $organization->id)
            ->with('permissions')
            ->get();

        return response()->json(['roles' => $roles]);
    }

    /**
     * Update a member's role.
     */
    public function updateMemberRole(
        Request $request,
        Organization $organization,
        OrganizationMember $member
    ): JsonResponse {
        if ($member->organization_id !== $organization->id) {
            return response()->json(['message' => 'Unauthorized member access.'], 403);
        }

        $validated = $request->validate([
            'role_id' => 'required|exists:roles,id',
        ]);

        $role = Role::findOrFail($validated['role_id']);
        if ($role->organization_id && $role->organization_id !== $organization->id) {
            return response()->json(['message' => 'Invalid role selection.'], 403);
        }

        $member->update(['role_id' => $role->id]);

        return response()->json([
            'message' => 'Member role updated.',
            'member' => $member->fresh()->load('user', 'role'),
        ]);
    }

    /**
     * List audit logs for an organization.
     */
    public function listAuditLogs(Request $request, Organization $organization): JsonResponse
    {
        $logs = AuditLog::where('organization_id', $organization->id)
            ->with('actor')
            ->orderByDesc('created_at')
            ->paginate(50);

        return response()->json($logs);
    }
}
