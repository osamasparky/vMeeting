<?php

namespace App\Domains\Guests\Controllers;

use App\Domains\Guests\Models\GuestInvitation;
use App\Domains\Tenancy\Models\Organization;
use App\Domains\Workspace\Models\Room;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;

class GuestController extends Controller
{
    /**
     * Create an expiring guest invitation link for a specific room.
     */
    public function createInvitation(Request $request, Organization $organization, Room $room): JsonResponse
    {
        if ($room->organization_id !== $organization->id) {
            return response()->json(['message' => 'Unauthorized room access.'], 403);
        }

        $validated = $request->validate([
            'guest_name' => ['required', 'string', 'max:255'],
            'guest_email' => ['nullable', 'email', 'max:255'],
            'expires_in_hours' => ['nullable', 'integer', 'min:1', 'max:72'],
        ]);

        $hours = $validated['expires_in_hours'] ?? 24;

        $invitation = GuestInvitation::create([
            'organization_id' => $organization->id,
            'room_id' => $room->id,
            'invited_by' => Auth::id(),
            'guest_name' => $validated['guest_name'],
            'guest_email' => $validated['guest_email'] ?? null,
            'token' => GuestInvitation::generateUniqueToken(),
            'status' => 'approved', // Auto-approved by inviter
            'expires_at' => now()->addHours($hours),
        ]);

        $joinUrl = url("/guest/join/{$invitation->token}");

        return response()->json([
            'message' => 'Guest invitation link generated.',
            'invitation' => $invitation,
            'join_url' => $joinUrl,
        ], 201);
    }

    /**
     * Public endpoint: Validate a guest invitation token.
     */
    public function verifyToken(string $token): JsonResponse
    {
        $invitation = GuestInvitation::where('token', $token)
            ->with(['organization:id,name,logo_url', 'room:id,name,type'])
            ->first();

        if (!$invitation) {
            return response()->json(['message' => 'Invalid invitation link.'], 404);
        }

        if ($invitation->isExpired()) {
            return response()->json(['message' => 'Invitation has expired.'], 410);
        }

        return response()->json([
            'valid' => true,
            'invitation' => $invitation,
        ]);
    }
}
