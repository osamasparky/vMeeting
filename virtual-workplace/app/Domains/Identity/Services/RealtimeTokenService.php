<?php

namespace App\Domains\Identity\Services;

use App\Domains\Identity\Models\User;
use App\Domains\Tenancy\Models\Organization;

class RealtimeTokenService
{
    /**
     * Issue a signed, short-lived JWT-like token for the WebSocket server.
     */
    public function generateToken(User $user, Organization $organization, int $ttlSeconds = 3600): string
    {
        $header = base64_encode(json_encode(['typ' => 'JWT', 'alg' => 'HS256']));

        $membership = $organization->members()->where('user_id', $user->id)->first();
        $roleName = $membership?->role?->name ?? 'member';

        $payload = base64_encode(json_encode([
            'sub' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'avatar_url' => $user->avatar_url,
            'organization_id' => $organization->id,
            'role' => $roleName,
            'iat' => time(),
            'exp' => time() + $ttlSeconds,
        ]));

        $secret = $this->getSecret();
        $signature = hash_hmac('sha256', "{$header}.{$payload}", $secret, true);
        $base64Signature = base64_encode($signature);

        return "{$header}.{$payload}.{$base64Signature}";
    }

    /**
     * Issue a signed token for a guest user joining a specific room.
     */
    public function generateGuestToken(string $guestName, Organization $organization, int $ttlSeconds = 7200): string
    {
        $guestId = 'guest_' . uniqid();
        return $this->generateGuestTokenWithId($guestId, $guestName, $organization, $ttlSeconds);
    }

    /**
     * Issue a signed token for a guest user with an explicit ID.
     */
    public function generateGuestTokenWithId(string $guestId, string $guestName, Organization $organization, int $ttlSeconds = 7200): string
    {
        $header = base64_encode(json_encode(['typ' => 'JWT', 'alg' => 'HS256']));

        $payload = base64_encode(json_encode([
            'sub' => $guestId,
            'name' => "{$guestName} (Guest)",
            'email' => "{$guestId}@guest.local",
            'avatar_url' => null,
            'organization_id' => $organization->id,
            'role' => 'Guest',
            'iat' => time(),
            'exp' => time() + $ttlSeconds,
        ]));

        $secret = $this->getSecret();
        $signature = hash_hmac('sha256', "{$header}.{$payload}", $secret, true);
        $base64Signature = base64_encode($signature);

        return "{$header}.{$payload}.{$base64Signature}";
    }

    /**
     * Retrieve the cryptographic signing secret.
     */
    private function getSecret(): string
    {
        $secret = config('services.realtime.secret') ?: config('app.key');
        if (empty($secret)) {
            $secret = env('REALTIME_SECRET') ?: 'base64:9fj2ZRPjCy3ClL13gPaYCv9gl8GsE8APwzVK8EceIRM=';
        }
        return $secret;
    }
}


