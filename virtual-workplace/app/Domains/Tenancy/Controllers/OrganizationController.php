<?php

namespace App\Domains\Tenancy\Controllers;

use App\Domains\Tenancy\Actions\CreateOrganizationAction;
use App\Domains\Tenancy\Actions\InviteMemberAction;
use App\Domains\Tenancy\Models\Organization;
use App\Domains\Tenancy\Models\OrganizationMember;
use App\Domains\Tenancy\Requests\CreateOrganizationRequest;
use App\Domains\Tenancy\Requests\InviteMemberRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class OrganizationController extends Controller
{
    /**
     * Create a new organization.
     */
    public function store(CreateOrganizationRequest $request, CreateOrganizationAction $action): JsonResponse
    {
        $organization = $action->execute($request->validated(), $request->user());

        return response()->json([
            'message' => 'Organization created successfully.',
            'organization' => $organization,
        ], 201);
    }

    /**
     * Get organization details.
     */
    public function show(Request $request, Organization $organization): JsonResponse
    {
        $organization->load('plan', 'settings');

        return response()->json(['organization' => $organization]);
    }

    /**
     * Update organization.
     */
    public function update(Request $request, Organization $organization): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'timezone' => 'sometimes|string|timezone',
            'logo_url' => 'sometimes|nullable|string|url',
        ]);

        $organization->update($validated);

        return response()->json([
            'message' => 'Organization updated.',
            'organization' => $organization->fresh(),
        ]);
    }

    /**
     * Get organization settings.
     */
    public function showSettings(Request $request, Organization $organization): JsonResponse
    {
        return response()->json(['settings' => $organization->settings]);
    }

    /**
     * Update organization settings.
     */
    public function updateSettings(Request $request, Organization $organization): JsonResponse
    {
        $validated = $request->validate([
            'branding' => 'sometimes|array',
            'policies' => 'sometimes|array',
        ]);

        $organization->settings()->updateOrCreate(
            ['organization_id' => $organization->id],
            $validated
        );

        return response()->json([
            'message' => 'Settings updated.',
            'settings' => $organization->settings()->first(),
        ]);
    }

    /**
     * List organization members.
     */
    public function listMembers(Request $request, Organization $organization): JsonResponse
    {
        $members = OrganizationMember::where('organization_id', $organization->id)
            ->with(['user', 'role'])
            ->paginate(20);

        return response()->json($members);
    }

    /**
     * Invite a member to the organization.
     */
    public function inviteMember(
        InviteMemberRequest $request,
        Organization $organization,
        InviteMemberAction $action
    ): JsonResponse {
        try {
            $member = $action->execute($organization, $request->validated());

            return response()->json([
                'message' => 'Member invited successfully.',
                'member' => $member->load('user', 'role'),
            ], 201);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    /**
     * Update a member (role, status).
     */
    public function updateMember(Request $request, Organization $organization, OrganizationMember $member): JsonResponse
    {
        if ($member->organization_id !== $organization->id) {
            return response()->json(['message' => 'Unauthorized member access.'], 403);
        }

        $validated = $request->validate([
            'status' => 'sometimes|in:active,suspended',
        ]);

        $member->update($validated);

        return response()->json([
            'message' => 'Member updated.',
            'member' => $member->fresh()->load('user', 'role'),
        ]);
    }

    /**
     * Remove a member from the organization.
     */
    public function removeMember(Request $request, Organization $organization, OrganizationMember $member): JsonResponse
    {
        if ($member->organization_id !== $organization->id) {
            return response()->json(['message' => 'Unauthorized member access.'], 403);
        }

        $member->delete();

        return response()->json(['message' => 'Member removed.']);
    }
}
