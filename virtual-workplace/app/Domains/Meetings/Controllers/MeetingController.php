<?php

namespace App\Domains\Meetings\Controllers;

use App\Domains\Meetings\Models\Meeting;
use App\Domains\Meetings\Services\LiveKitTokenService;
use App\Domains\Tenancy\Models\Organization;
use App\Domains\Tenancy\Models\OrganizationMember;
use App\Domains\Workspace\Models\Room;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;

class MeetingController extends Controller
{
    /**
     * Issue a short-lived LiveKit WebRTC Access Token for joining an office workspace room.
     */
    public function getLiveKitToken(
        Request $request,
        Organization $organization,
        Room $room,
        LiveKitTokenService $service
    ): JsonResponse {
        if ($room->organization_id !== $organization->id) {
            return response()->json(['message' => 'Unauthorized: Room does not belong to this organization.'], 403);
        }

        $user = Auth::user();
        $isHost = false;

        if ($user) {
            $membership = OrganizationMember::where('organization_id', $organization->id)
                ->where('user_id', $user->id)
                ->first();

            $isHost = $membership && in_array($membership->role, ['owner', 'admin', 'manager']);
            $token = $service->generateRoomToken($user, $room, $isHost);
        } else {
            $guestName = (string) ($request->input('guest_name') ?: 'Guest User');
            $guestId = (string) ($request->input('guest_id') ?: ('guest_'.uniqid()));
            $token = $service->generateGuestRoomToken($guestId, $guestName, $room);
        }

        $livekitHost = config('services.livekit.host', env('LIVEKIT_HOST', 'wss://nextspace.munazzah.com/livekit'));

        return response()->json([
            'token' => $token,
            'livekit_host' => $livekitHost,
            'room_name' => "org_{$room->organization_id}_room_{$room->id}",
            'participant_identity' => $user ? $user->id : $guestId,
            'is_host' => $isHost,
            'ice_servers' => $this->getIceServersList(),
        ]);
    }

    /**
     * Issue a short-lived LiveKit WebRTC Access Token for joining a formal meeting session.
     */
    public function getMeetingToken(
        Request $request,
        Organization $organization,
        Meeting $meeting,
        LiveKitTokenService $service
    ): JsonResponse {
        if ($meeting->organization_id !== $organization->id) {
            return response()->json(['message' => 'Unauthorized: Meeting does not belong to this organization.'], 403);
        }

        $user = Auth::user();
        if (! $user) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        $isHost = ($meeting->created_by === $user->id);

        $token = $service->generateMeetingToken($user, $meeting, $isHost);
        $livekitHost = config('services.livekit.host', env('LIVEKIT_HOST', 'wss://nextspace.munazzah.com/livekit'));

        return response()->json([
            'token' => $token,
            'livekit_host' => $livekitHost,
            'room_name' => $meeting->livekit_room_name ?: "meeting_{$meeting->organization_id}_{$meeting->id}",
            'is_host' => $isHost,
            'ice_servers' => $this->getIceServersList(),
        ]);
    }

    /**
     * Get WebRTC STUN/TURN & Diagnostics configuration.
     */
    public function getDiagnosticsConfig(Request $request, Organization $organization): JsonResponse
    {
        return response()->json([
            'livekit_host' => config('services.livekit.host', env('LIVEKIT_HOST', 'wss://nextspace.munazzah.com/livekit')),
            'ice_servers' => $this->getIceServersList(),
            'organization_id' => $organization->id,
            'server_time' => now()->toIso8601String(),
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
            'livekit_room_name' => "meeting_{$organization->id}_".uniqid(),
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

    /**
     * Internal helper to build ICE servers list with STUN & Coturn TURN fallbacks.
     */
    protected function getIceServersList(): array
    {
        $turnUrl = config('services.turn.url', env('TURN_URL', 'turn:173.212.248.192:3478'));
        $turnUser = config('services.turn.username', env('TURN_USERNAME', 'vw_turn_user'));
        $turnPass = config('services.turn.credential', env('TURN_CREDENTIAL', 'vw_turn_password_2026'));

        $stunUrl = str_replace('turn:', 'stun:', $turnUrl);

        return [
            ['urls' => $stunUrl],
            [
                'urls' => [
                    $turnUrl.'?transport=udp',
                    $turnUrl.'?transport=tcp',
                ],
                'username' => $turnUser,
                'credential' => $turnPass,
            ],
        ];
    }
}
