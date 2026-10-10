<?php

namespace App\Domains\Workspace\Controllers;

use App\Domains\Notifications\Models\WorkplaceNotification;
use App\Domains\Notifications\Services\SpatialNotifier;
use App\Domains\Tenancy\Models\Organization;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

/**
 * Mobile / API side of the office interactions and the notification feed.
 * Uses the same notifications table as the web bell.
 */
class SpatialInteractionsApiController extends Controller
{
    /**
     * Knock on a room door: the people inside are notified.
     */
    public function knock(Request $request, Organization $organization): JsonResponse
    {
        $validated = $request->validate([
            'room_id' => ['required', 'string'],
            'message' => ['nullable', 'string', 'max:200'],
        ]);

        $sent = SpatialNotifier::knock($request->user(), $organization->id, $validated['room_id']);

        return response()->json(['message' => 'Door knock sent successfully.', 'notified' => $sent]);
    }

    /**
     * Wave at a teammate.
     */
    public function wave(Request $request, Organization $organization): JsonResponse
    {
        $validated = $request->validate(['target_user_id' => ['required', 'string']]);

        SpatialNotifier::wave($request->user(), $organization->id, $validated['target_user_id']);

        return response()->json(['message' => 'Wave sent successfully.']);
    }

    /**
     * Ring a teammate for urgent attention.
     */
    public function ring(Request $request, Organization $organization): JsonResponse
    {
        $validated = $request->validate(['target_user_id' => ['required', 'string']]);

        SpatialNotifier::ring($request->user(), $organization->id, $validated['target_user_id']);

        return response()->json(['message' => 'Ring sent successfully.']);
    }

    /**
     * The signed-in user's notifications, newest first.
     */
    public function notifications(Request $request): JsonResponse
    {
        $page = WorkplaceNotification::forUser($request->user()->id)
            ->latest()
            ->paginate(20, ['id', 'organization_id', 'type', 'title', 'body', 'icon', 'action_url', 'data', 'is_read', 'read_at', 'created_at']);

        return response()->json($page->toArray() + [
            'unread_count' => WorkplaceNotification::forUser($request->user()->id)->unread()->count(),
        ]);
    }

    /**
     * Mark one of the user's notifications as read.
     */
    public function markAsRead(Request $request, string $id): JsonResponse
    {
        WorkplaceNotification::forUser($request->user()->id)->whereKey($id)->firstOrFail()->markAsRead();

        return response()->json(['message' => 'Notification marked as read.']);
    }
}
