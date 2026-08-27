<?php

namespace App\Domains\People\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class ProfileController extends Controller
{
    /**
     * Get current user's profile for a specific organization.
     */
    public function show(Request $request): JsonResponse
    {
        $user = $request->user();
        $user->load('memberships.organization', 'memberships.role');

        return response()->json(['user' => $user]);
    }

    /**
     * Update current user's profile.
     */
    public function update(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'avatar_url' => 'sometimes|nullable|string',
        ]);

        $request->user()->update($validated);

        return response()->json([
            'message' => 'Profile updated.',
            'user' => $request->user()->fresh(),
        ]);
    }

    /**
     * Update avatar configuration.
     */
    public function updateAvatar(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'avatar_url' => 'nullable|string',
        ]);

        $request->user()->update($validated);

        return response()->json([
            'message' => 'Avatar updated.',
            'user' => $request->user()->fresh(),
        ]);
    }
}
