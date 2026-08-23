<?php

namespace App\Domains\Meetings\Services;

use App\Domains\Identity\Models\User;
use App\Domains\Meetings\Models\Meeting;
use App\Domains\Workspace\Models\Room;

class LiveKitTokenService
{
    protected string $apiKey;
    protected string $apiSecret;

    public function __construct()
    {
        $this->apiKey = config('services.livekit.api_key', env('LIVEKIT_API_KEY', 'devkey'));
        $this->apiSecret = config('services.livekit.api_secret', env('LIVEKIT_API_SECRET', 'secret_livekit_key_virtual_workplace_2026'));
    }

    /**
     * Mint a short-lived LiveKit WebRTC Access Token for joining an office workspace room.
     */
    public function generateRoomToken(User $user, Room $room, bool $isHost = false, int $ttlSeconds = 7200): string
    {
        $roomName = "org_{$room->organization_id}_room_{$room->id}";

        $metadata = [
            'user_id' => $user->id,
            'organization_id' => $room->organization_id,
            'room_id' => $room->id,
            'name' => $user->name,
            'avatar_url' => $user->avatar_url,
            'job_title' => $user->profile?->job_title ?? 'Member',
            'is_host' => $isHost,
        ];

        $grants = [
            'room' => $roomName,
            'roomJoin' => true,
            'canPublish' => true,
            'canSubscribe' => true,
            'canPublishData' => true,
            'canPublishSources' => ['camera', 'microphone', 'screen_share', 'screen_share_audio'],
            'hidden' => false,
            'recorder' => false,
        ];

        if ($isHost) {
            $grants['roomAdmin'] = true;
            $grants['roomRecord'] = true;
        }

        return $this->signJwt([
            'iss' => $this->apiKey,
            'sub' => $user->id,
            'name' => $user->name,
            'video' => $grants,
            'metadata' => json_encode($metadata),
            'iat' => time(),
            'nbf' => time() - 5,
            'exp' => time() + $ttlSeconds,
        ]);
    }

    /**
     * Mint a short-lived LiveKit WebRTC Access Token for joining a formal meeting session.
     */
    public function generateMeetingToken(User $user, Meeting $meeting, bool $isHost = false, int $ttlSeconds = 7200): string
    {
        $roomName = $meeting->livekit_room_name ?: "meeting_{$meeting->organization_id}_{$meeting->id}";

        $metadata = [
            'user_id' => $user->id,
            'organization_id' => $meeting->organization_id,
            'meeting_id' => $meeting->id,
            'name' => $user->name,
            'avatar_url' => $user->avatar_url,
            'job_title' => $user->profile?->job_title ?? 'Member',
            'is_host' => $isHost,
        ];

        $grants = [
            'room' => $roomName,
            'roomJoin' => true,
            'canPublish' => true,
            'canSubscribe' => true,
            'canPublishData' => true,
            'canPublishSources' => ['camera', 'microphone', 'screen_share', 'screen_share_audio'],
            'hidden' => false,
            'recorder' => false,
        ];

        if ($isHost) {
            $grants['roomAdmin'] = true;
            $grants['roomRecord'] = true;
        }

        return $this->signJwt([
            'iss' => $this->apiKey,
            'sub' => $user->id,
            'name' => $user->name,
            'video' => $grants,
            'metadata' => json_encode($metadata),
            'iat' => time(),
            'nbf' => time() - 5,
            'exp' => time() + $ttlSeconds,
        ]);
    }

    /**
     * Produce an RFC 7519 compliant Base64URL-encoded HMAC-SHA256 JWT string.
     */
    protected function signJwt(array $payload): string
    {
        $header = [
            'typ' => 'JWT',
            'alg' => 'HS256',
        ];

        $encodedHeader = $this->base64UrlEncode(json_encode($header));
        $encodedPayload = $this->base64UrlEncode(json_encode($payload));

        $signature = hash_hmac('sha256', "{$encodedHeader}.{$encodedPayload}", $this->apiSecret, true);
        $encodedSignature = $this->base64UrlEncode($signature);

        return "{$encodedHeader}.{$encodedPayload}.{$encodedSignature}";
    }

    /**
     * Base64URL encoding (RFC 4648).
     */
    protected function base64UrlEncode(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }
}
