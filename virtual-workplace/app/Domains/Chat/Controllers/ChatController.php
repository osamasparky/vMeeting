<?php

namespace App\Domains\Chat\Controllers;

use App\Domains\Chat\Models\Channel;
use App\Domains\Chat\Models\Message;
use App\Domains\Identity\Models\User;
use App\Domains\Tenancy\Models\Organization;
use App\Domains\Tenancy\Models\OrganizationMember;
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

        $channels = Channel::where('organization_id', $organization->id)
            ->where(function ($query) use ($user) {
                $query->where('type', '!=', 'dm')
                    ->orWhereHas('members', function ($q) use ($user) {
                        $q->where('users.id', $user->id);
                    });
            })
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

        if (! $channel) {
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

    /**
     * Web session: List conversations, colleagues roster, and company channels.
     */
    public function webConversations(Request $request): JsonResponse
    {
        $user = Auth::user();
        $membership = OrganizationMember::where('user_id', $user->id)
            ->whereIn('status', ['active', 'invited'])
            ->with('organization')
            ->first();

        if (! $membership) {
            return response()->json(['message' => 'No active organization found.'], 403);
        }

        $organization = $membership->organization;

        // Ensure default company channels exist
        $generalChannel = Channel::firstOrCreate(
            ['organization_id' => $organization->id, 'name' => 'general', 'type' => 'room'],
            ['type' => 'room', 'name' => 'general']
        );
        $announcementsChannel = Channel::firstOrCreate(
            ['organization_id' => $organization->id, 'name' => 'announcements', 'type' => 'broadcast'],
            ['type' => 'broadcast', 'name' => 'announcements']
        );

        $channels = Channel::where('organization_id', $organization->id)
            ->where(function ($query) use ($user) {
                $query->whereIn('type', ['room', 'broadcast'])
                    ->orWhere(function ($q2) use ($user) {
                        $q2->whereHas('members', function ($qm) use ($user) {
                            $qm->where('users.id', $user->id);
                        });
                    });
            })
            ->with(['messages' => function ($q) {
                $q->latest()->take(1);
            }])
            ->get()
            ->map(function ($c) {
                $lastMsg = $c->messages->first();

                return [
                    'id' => $c->id,
                    'name' => $c->name,
                    'type' => $c->type,
                    'unread_count' => 0,
                    'last_message' => $lastMsg ? [
                        'body' => $lastMsg->body,
                        'created_at' => $lastMsg->created_at->diffForHumans(),
                    ] : null,
                ];
            });

        // Fetch company members for direct messaging
        $members = OrganizationMember::where('organization_id', $organization->id)
            ->where('status', 'active')
            ->with(['user.profiles' => function ($q) use ($organization) {
                $q->where('organization_id', $organization->id);
            }, 'role'])
            ->get()
            ->map(function ($m) use ($user, $organization) {
                $u = $m->user;
                $profile = $u->profiles->first();
                $isSelf = $u->id === $user->id;

                // Check DM channel
                $dmChannel = Channel::where('organization_id', $organization->id)
                    ->where('type', 'dm')
                    ->whereHas('members', function ($q) use ($user) {
                        $q->where('users.id', $user->id);
                    })
                    ->whereHas('members', function ($q) use ($u) {
                        $q->where('users.id', $u->id);
                    })
                    ->with(['messages' => function ($q) {
                        $q->latest()->take(1);
                    }])
                    ->first();

                $lastMsg = $dmChannel?->messages->first();

                return [
                    'id' => $m->id,
                    'user_id' => $u->id,
                    'name' => $u->name,
                    'nickname' => $u->nickname,
                    'email' => $u->email,
                    'avatar_url' => $u->avatar_url,
                    'job_title' => $profile?->job_title ?? $m->role?->name ?? 'Team Member',
                    'role' => $m->role?->name ?? 'Member',
                    'is_self' => $isSelf,
                    'dm_channel_id' => $dmChannel?->id,
                    'last_message' => $lastMsg ? [
                        'body' => $lastMsg->body,
                        'created_at' => $lastMsg->created_at->diffForHumans(),
                        'is_mine' => $lastMsg->sender_id === $user->id,
                    ] : null,
                ];
            });

        return response()->json([
            'channels' => $channels,
            'members' => $members,
            'current_user_id' => $user->id,
        ]);
    }

    /**
     * Web session: Get or create a direct message channel with a colleague.
     */
    public function webGetOrCreateDm(User $targetUser): JsonResponse
    {
        $currentUser = Auth::user();
        $membership = OrganizationMember::where('user_id', $currentUser->id)
            ->whereIn('status', ['active', 'invited'])
            ->first();

        if (! $membership) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        $organizationId = $membership->organization_id;

        // Verify target user belongs to same company
        $targetMembership = OrganizationMember::where('organization_id', $organizationId)
            ->where('user_id', $targetUser->id)
            ->first();

        if (! $targetMembership) {
            return response()->json(['message' => 'Target user is not a member of your company.'], 404);
        }

        $channel = Channel::where('organization_id', $organizationId)
            ->where('type', 'dm')
            ->whereHas('members', function ($q) use ($currentUser) {
                $q->where('users.id', $currentUser->id);
            })
            ->whereHas('members', function ($q) use ($targetUser) {
                $q->where('users.id', $targetUser->id);
            })
            ->first();

        if (! $channel) {
            $channel = Channel::create([
                'organization_id' => $organizationId,
                'type' => 'dm',
                'name' => "DM: {$currentUser->name} & {$targetUser->name}",
            ]);

            $channel->members()->attach([$currentUser->id, $targetUser->id]);
        }

        return response()->json([
            'channel' => $channel->load('members:id,name,avatar_url'),
            'target_user' => [
                'id' => $targetUser->id,
                'name' => $targetUser->name,
                'avatar_url' => $targetUser->avatar_url,
                'email' => $targetUser->email,
            ],
        ]);
    }

    /**
     * Web session: List messages in a channel.
     */
    public function webListMessages(Channel $channel): JsonResponse
    {
        $user = Auth::user();
        $membership = OrganizationMember::where('user_id', $user->id)
            ->whereIn('status', ['active', 'invited'])
            ->first();

        if (! $membership || $channel->organization_id !== $membership->organization_id) {
            return response()->json(['message' => 'Unauthorized channel access.'], 403);
        }

        // If DM channel, ensure user is a participant
        if ($channel->type === 'dm') {
            $isMember = $channel->members()->where('users.id', $user->id)->exists();
            if (! $isMember) {
                return response()->json(['message' => 'Unauthorized DM access.'], 403);
            }
        }

        $messages = $channel->messages()
            ->with('sender:id,name,avatar_url')
            ->orderBy('created_at', 'asc')
            ->take(100)
            ->get()
            ->map(function ($m) use ($user) {
                return [
                    'id' => $m->id,
                    'body' => $m->body,
                    'attachments' => $m->attachments,
                    'created_at' => $m->created_at->format('h:i A'),
                    'created_at_full' => $m->created_at->diffForHumans(),
                    'is_mine' => $m->sender_id === $user->id,
                    'sender' => [
                        'id' => $m->sender?->id,
                        'name' => $m->sender?->name ?? 'Unknown',
                        'avatar_url' => $m->sender?->avatar_url,
                    ],
                ];
            });

        return response()->json([
            'channel_id' => $channel->id,
            'channel_name' => $channel->name,
            'channel_type' => $channel->type,
            'messages' => $messages,
        ]);
    }

    /**
     * Web session: Post a new message.
     */
    public function webSendMessage(Request $request, Channel $channel): JsonResponse
    {
        $user = Auth::user();
        $membership = OrganizationMember::where('user_id', $user->id)
            ->whereIn('status', ['active', 'invited'])
            ->first();

        if (! $membership || $channel->organization_id !== $membership->organization_id) {
            return response()->json(['message' => 'Unauthorized channel access.'], 403);
        }

        if ($channel->type === 'dm') {
            $isMember = $channel->members()->where('users.id', $user->id)->exists();
            if (! $isMember) {
                return response()->json(['message' => 'Unauthorized DM access.'], 403);
            }
        }

        $validated = $request->validate([
            'body' => ['required', 'string', 'max:5000'],
            'attachments' => ['nullable', 'array'],
        ]);

        $message = Message::create([
            'organization_id' => $membership->organization_id,
            'channel_id' => $channel->id,
            'sender_id' => $user->id,
            'body' => $validated['body'],
            'attachments' => $validated['attachments'] ?? null,
        ]);

        return response()->json([
            'message' => [
                'id' => $message->id,
                'body' => $message->body,
                'attachments' => $message->attachments,
                'created_at' => $message->created_at->format('h:i A'),
                'created_at_full' => $message->created_at->diffForHumans(),
                'is_mine' => true,
                'sender' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'avatar_url' => $user->avatar_url,
                ],
            ],
        ], 201);
    }
}
