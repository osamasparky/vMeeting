<?php

namespace App\Domains\Chat\Controllers;

use App\Domains\Chat\Models\Channel;
use App\Domains\Chat\Models\Message;
use App\Domains\Identity\Models\User;
use App\Domains\Tenancy\Models\Organization;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;

class ChatController extends Controller
{
    /**
     * List accessible channels for the organization.
     */
    public function listChannels(Organization $organization): JsonResponse
    {
        $user = Auth::user();

        $channels = $organization->hasMany(Channel::class)
            ->with(['room:id,name', 'members:id,name,email'])
            ->get();

        return response()->json([
            'channels' => $channels,
        ]);
    }

    /**
     * Get or create a direct message (DM) channel between current user and another member.
     */
    public function getOrCreateDm(Organization $organization, User $targetUser): JsonResponse
    {
        $currentUser = Auth::user();

        // Find existing DM channel between the two users
        $channel = Channel::where('organization_id', $organization->id)
            ->where('type', 'dm')
            ->whereHas('members', function ($q) use ($currentUser) {
                $q->where('users.id', $currentUser->id);
            })
            ->whereHas('members', function ($q) use ($targetUser) {
                $q->where('users.id', $targetUser->id);
            })
            ->first();

        if (!$channel) {
            $channel = Channel::create([
                'organization_id' => $organization->id,
                'type' => 'dm',
                'name' => "DM: {$currentUser->name} & {$targetUser->name}",
            ]);

            $channel->members()->attach([$currentUser->id, $targetUser->id]);
        }

        return response()->json([
            'channel' => $channel->load('members:id,name,avatar_url'),
        ]);
    }

    /**
     * List paginated messages in a channel.
     */
    public function listMessages(Organization $organization, Channel $channel): JsonResponse
    {
        if ($channel->organization_id !== $organization->id) {
            return response()->json(['message' => 'Unauthorized channel access.'], 403);
        }

        $messages = $channel->messages()
            ->with('sender:id,name,avatar_url')
            ->latest()
            ->paginate(50);

        return response()->json($messages);
    }

    /**
     * Send a message to a channel.
     */
    public function sendMessage(Request $request, Organization $organization, Channel $channel): JsonResponse
    {
        if ($channel->organization_id !== $organization->id) {
            return response()->json(['message' => 'Unauthorized channel access.'], 403);
        }

        $validated = $request->validate([
            'body' => ['required', 'string', 'max:5000'],
            'attachments' => ['nullable', 'array'],
        ]);

        $message = Message::create([
            'organization_id' => $organization->id,
            'channel_id' => $channel->id,
            'sender_id' => Auth::id(),
            'body' => $validated['body'],
            'attachments' => $validated['attachments'] ?? null,
        ]);

        return response()->json([
            'message' => $message->load('sender:id,name,avatar_url'),
        ], 201);
    }
}
