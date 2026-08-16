<?php

namespace App\Domains\Meetings\Controllers;

use App\Domains\Meetings\Models\Meeting;
use App\Domains\Meetings\Services\LiveKitTokenService;
use App\Domains\Tenancy\Models\Organization;
use App\Domains\Workspace\Models\Room;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;

class MeetingController extends Controller
{
    /**
     * Issue a short-lived LiveKit WebRTC Access Token for joining a room audio/video stream.
     */
    public function getLiveKitToken(
        Organization $organization,
        Room $room,
        LiveKitTokenService $service
    ): JsonResponse {
        if ($room->organization_id !== $organization->id) {
            return response()->json(['message' => 'Unauthorized room access.'], 403);
        }

        $user = Auth::user();
        $token = $service->generateRoomToken($user, $room);

        return response()->json([
            'token' => $token,
            'livekit_host' => env('LIVEKIT_HOST', 'wss://livekit.virtualworkplace.local'),
            'room_name' => "org_{$room->organization_id}_room_{$room->id}",
        ]);
    }

    /**
     * List meetings for the organization.
     */
    public function listMeetings(Organization $organization): JsonResponse
    {
        $meetings = $organization->hasMany(Meeting::class)
            ->with(['room:id,name', 'creator:id,name', 'participants.user:id,name,avatar_url'])
            ->orderByDesc('created_at')
            ->paginate(20);

        return response()->json($meetings);
    }

    /**
     * Start an instant or scheduled meeting.
     */
    public function createMeeting(Request $request, Organization $organization): JsonResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'room_id' => ['nullable', 'uuid', 'exists:rooms,id'],
            'type' => ['nullable', 'string', 'in:instant,scheduled'],
        ]);

        $user = Auth::user();

        $meeting = Meeting::create([
            'organization_id' => $organization->id,
            'room_id' => $validated['room_id'] ?? null,
            'created_by' => $user->id,
            'title' => $validated['title'],
            'type' => $validated['type'] ?? 'instant',
            'status' => 'active',
            'started_at' => now(),
            'livekit_room_name' => "meeting_{$organization->id}_" . uniqid(),
        ]);

        $meeting->participants()->create([
            'user_id' => $user->id,
            'role' => 'host',
            'joined_at' => now(),
        ]);

        return response()->json([
            'message' => 'Meeting started successfully.',
            'meeting' => $meeting->load(['room', 'creator', 'participants.user']),
        ], 201);
    }

    /**
     * End a meeting.
     */
    public function endMeeting(Organization $organization, Meeting $meeting): JsonResponse
    {
        if ($meeting->organization_id !== $organization->id) {
            return response()->json(['message' => 'Unauthorized meeting access.'], 403);
        }

        $meeting->update([
            'status' => 'ended',
            'ended_at' => now(),
        ]);

        return response()->json([
            'message' => 'Meeting ended successfully.',
            'meeting' => $meeting,
        ]);
    }
}
