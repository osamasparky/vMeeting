<?php

namespace App\Domains\Meetings\Services;

use App\Domains\Identity\Models\User;
use App\Domains\Workspace\Models\Room;

class LiveKitTokenService
{
    protected string $apiKey;
    protected string $apiSecret;

    public function __construct()
    {
        $this->apiKey = env('LIVEKIT_API_KEY', 'devkey');
        $this->apiSecret = env('LIVEKIT_API_SECRET', 'secret_livekit_key_virtual_workplace_2026');
    }

    /**
     * Mint a short-lived LiveKit WebRTC Access Token for joining a media room.
     */
    public function generateRoomToken(User $user, Room $room, int $ttlSeconds = 7200): string
    {
        $roomName = "org_{$room->organization_id}_room_{$room->id}";

        $header = base64_encode(json_encode([
            'typ' => 'JWT',
            'alg' => 'HS256',
        ]));

        $payload = base64_encode(json_encode([
            'iss' => $this->apiKey,
            'sub' => $user->id,
            'name' => $user->name,
            'video' => [
                'room' => $roomName,
                'roomJoin' => true,
                'canPublish' => true,
                'canSubscribe' => true,
                'canPublishData' => true,
            ],
            'metadata' => json_encode([
                'user_id' => $user->id,
                'avatar_url' => $user->avatar_url,
                'room_id' => $room->id,
            ]),
            'iat' => time(),
            'exp' => time() + $ttlSeconds,
        ]));

        $signature = hash_hmac('sha256', "{$header}.{$payload}", $this->apiSecret, true);
        $base64Signature = base64_encode($signature);

        return "{$header}.{$payload}.{$base64Signature}";
    }
}
