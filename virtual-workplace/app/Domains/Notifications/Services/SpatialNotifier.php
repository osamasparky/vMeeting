<?php

namespace App\Domains\Notifications\Services;

use App\Domains\Identity\Models\User;
use App\Domains\Notifications\Models\WorkplaceNotification;
use App\Domains\People\Models\AttendanceSession;
use App\Domains\Tenancy\Models\OrganizationMember;
use App\Domains\Workspace\Models\Room;
use Illuminate\Validation\ValidationException;

/**
 * Knock, wave and ring between people in the virtual office. Shared by the
 * web office and the mobile API so both keep the same tenant rules: the
 * other person and the room must belong to the sender's organization.
 */
class SpatialNotifier
{
    public static function wave(User $sender, string $organizationId, string $targetId, ?string $roomName = null): ?WorkplaceNotification
    {
        $target = self::member($organizationId, $targetId, $sender);

        return NotificationService::notifyWave($target, $sender, $roomName, $organizationId);
    }

    public static function ring(User $sender, string $organizationId, string $targetId): ?WorkplaceNotification
    {
        $target = self::member($organizationId, $targetId, $sender);

        return NotificationService::send(
            $target->id,
            'user_ring',
            fn () => __('🔔 :sender is ringing you', ['sender' => $sender->name]),
            fn () => __(':sender needs your attention now.', ['sender' => $sender->name]),
            '/office',
            ['sender_id' => $sender->id, 'sender_name' => $sender->name],
            '🔔',
            $organizationId,
        );
    }

    /** @return int how many people were told */
    public static function knock(User $sender, string $organizationId, string $roomId): int
    {
        $room = Room::withoutGlobalScopes()->where('organization_id', $organizationId)->find($roomId);
        if (! $room) {
            throw ValidationException::withMessages(['room_id' => __('Room not found.')]);
        }

        // Who is inside: an open attendance session in this room (see AttendanceService).
        $occupants = User::whereIn('id', AttendanceSession::withoutGlobalScopes()
            ->where('organization_id', $organizationId)
            ->where('room_id', $room->id)
            ->where('status', 'active')
            ->whereNull('ended_at')
            ->pluck('user_id'))->get();
        if ($occupants->isEmpty()) {
            // Nobody is inside: tell the room's organization admins instead of nobody.
            $occupants = User::whereIn('id', OrganizationMember::where('organization_id', $organizationId)
                ->where('status', 'active')
                ->whereHas('role', fn ($r) => $r->where('slug', 'company_admin'))
                ->pluck('user_id'))->get();
        }

        $sent = 0;
        foreach ($occupants as $occupant) {
            if ($occupant->id !== $sender->id && NotificationService::notifyDoorKnock($room, $occupant, $sender)) {
                $sent++;
            }
        }

        return $sent;
    }

    private static function member(string $organizationId, string $targetId, User $sender): User
    {
        if ($targetId === $sender->id) {
            throw ValidationException::withMessages(['target_user_id' => __('You cannot send this to yourself.')]);
        }

        $isMember = OrganizationMember::where('organization_id', $organizationId)
            ->where('user_id', $targetId)
            ->where('status', 'active')
            ->exists();
        $target = $isMember ? User::find($targetId) : null;
        if (! $target) {
            throw ValidationException::withMessages(['target_user_id' => __('This person is not in your organization.')]);
        }

        return $target;
    }
}
