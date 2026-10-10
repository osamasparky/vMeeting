<?php

namespace App\Domains\Workspace\Support;

use App\Domains\Guests\Models\GuestInvitation;
use App\Domains\Identity\Models\User;
use App\Domains\Tenancy\Models\Organization;
use App\Domains\Tenancy\Models\OrganizationMember;
use App\Domains\Workspace\Models\Room;
use Illuminate\Support\Facades\Auth;

/**
 * Who may use a room's media (LiveKit) and files.
 *
 * - A signed-in user must be an active member of the room's organization and
 *   allowed into that room by the same rules the office applies: company admins
 *   everywhere; members with assigned rooms only there; everyone else in
 *   non-private rooms (or private ones with rooms.access_private).
 * - A guest has no account, so entering through an invitation records it in
 *   the session; the guest may then use only the invited room until it expires.
 */
class RoomAccess
{
    public const GUEST_SESSION_KEY = 'office_guest_access';

    public static function rememberGuest(GuestInvitation $invitation, string $guestId, string $guestName): void
    {
        session()->put(self::GUEST_SESSION_KEY, [
            'organization_id' => $invitation->organization_id,
            'room_id' => $invitation->room_id,
            'guest_id' => $guestId,
            'guest_name' => $guestName,
            'expires_at' => $invitation->expires_at?->getTimestamp(),
        ]);
    }

    /**
     * @return array{user: ?User, membership: ?OrganizationMember, guest: ?array}|null null = no access
     */
    public static function resolve(Organization $organization, Room $room): ?array
    {
        if ($room->organization_id !== $organization->id) {
            return null;
        }

        $user = Auth::user();
        if ($user) {
            if ($user->isSuperAdmin()) {
                return ['user' => $user, 'membership' => null, 'guest' => null];
            }
            $membership = OrganizationMember::where('organization_id', $organization->id)
                ->where('user_id', $user->id)
                ->whereIn('status', ['active', 'invited'])
                ->with('role.permissions')
                ->first();

            return $membership && self::memberMayEnter($membership, $room)
                ? ['user' => $user, 'membership' => $membership, 'guest' => null]
                : null;
        }

        $guest = session(self::GUEST_SESSION_KEY);
        if (is_array($guest)
            && ($guest['organization_id'] ?? null) === $organization->id
            && ($guest['room_id'] ?? null) === $room->id
            && (empty($guest['expires_at']) || $guest['expires_at'] > time())) {
            return ['user' => null, 'membership' => null, 'guest' => $guest];
        }

        return null;
    }

    public static function memberMayEnter(OrganizationMember $membership, Room $room): bool
    {
        if (in_array($membership->role?->slug, ['company_admin', 'super_admin'], true)) {
            return true;
        }

        $assigned = $membership->rooms()->pluck('rooms.id');
        if ($assigned->isNotEmpty()) {
            return $assigned->contains($room->id);
        }

        return $room->access_mode !== 'private' || $membership->hasPermission('rooms.access_private');
    }

    /** May run the meeting in this room (mute others, end screen shares). */
    public static function isHost(?OrganizationMember $membership, ?User $user): bool
    {
        if ($user?->isSuperAdmin()) {
            return true;
        }

        return $membership !== null
            && ($membership->role?->slug === 'company_admin' || $membership->hasPermission('rooms.manage'));
    }
}
