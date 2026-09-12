<?php

namespace App\Domains\Workspace\Controllers;

use App\Domains\Identity\Models\User;
use App\Domains\Notifications\Models\Notification;
use App\Domains\Tenancy\Models\Organization;
use App\Domains\Workspace\Models\Room;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class SpatialInteractionsApiController extends Controller
{
    /**
     * Send door knocking request to a private sound-isolated room.
     */
    public function knock(Request $request, Organization $organization): JsonResponse
    {
        $validated = $request->validate([
            'room_id' => 'required|exists:rooms,id',
            'message' => 'nullable|string|max:200',
        ]);

        $user = $request->user();
        $room = Room::findOrFail($validated['room_id']);

        // Create a notification for room members or admin
        $notification = Notification::create([
            'organization_id' => $organization->id,
            'user_id' => $user->id,
            'type' => 'door_knock',
            'data' => [
                'sender_id' => $user->id,
                'sender_name' => $user->name,
                'room_id' => $room->id,
                'room_name' => $room->name,
                'message' => $validated['message'] ?? 'طلب استئذان بالدخول إلى الغرفة.',
                'timestamp' => now()->toIso8601String(),
            ],
            'read_at' => null,
        ]);

        return response()->json([
            'message' => 'Door knock sent successfully.',
            'notification' => $notification,
            'room' => $room,
        ]);
    }

    /**
     * Send friendly wave to another teammate.
     */
    public function wave(Request $request, Organization $organization): JsonResponse
    {
        $validated = $request->validate([
            'target_user_id' => 'required|exists:users,id',
        ]);

        $user = $request->user();
        $targetUser = User::findOrFail($validated['target_user_id']);

        $notification = Notification::create([
            'organization_id' => $organization->id,
            'user_id' => $targetUser->id,
            'type' => 'user_wave',
            'data' => [
                'sender_id' => $user->id,
                'sender_name' => $user->name,
                'message' => "لوّح لك {$user->name} بالتحية 👋",
                'timestamp' => now()->toIso8601String(),
            ],
            'read_at' => null,
        ]);

        return response()->json([
            'message' => 'Wave sent successfully.',
            'target_user' => $targetUser->only(['id', 'name']),
        ]);
    }

    /**
     * Send direct attention ringing alert to another teammate.
     */
    public function ring(Request $request, Organization $organization): JsonResponse
    {
        $validated = $request->validate([
            'target_user_id' => 'required|exists:users,id',
        ]);

        $user = $request->user();
        $targetUser = User::findOrFail($validated['target_user_id']);

        $notification = Notification::create([
            'organization_id' => $organization->id,
            'user_id' => $targetUser->id,
            'type' => 'user_ring',
            'data' => [
                'sender_id' => $user->id,
                'sender_name' => $user->name,
                'message' => "تنبيه صوتي عاجل من {$user->name} 🔔",
                'timestamp' => now()->toIso8601String(),
            ],
            'read_at' => null,
        ]);

        return response()->json([
            'message' => 'Ring sent successfully.',
            'target_user' => $targetUser->only(['id', 'name']),
        ]);
    }

    /**
     * Get user in-app notifications.
     */
    public function notifications(Request $request): JsonResponse
    {
        $notifications = Notification::where('user_id', $request->user()->id)
            ->latest()
            ->paginate(20);

        return response()->json($notifications);
    }

    /**
     * Mark notification as read.
     */
    public function markAsRead(Request $request, string $id): JsonResponse
    {
        $notification = Notification::where('user_id', $request->user()->id)
            ->where('id', $id)
            ->firstOrFail();

        $notification->update(['read_at' => now()]);

        return response()->json(['message' => 'Notification marked as read.']);
    }
}
