<?php

namespace App\Domains\Guests\Actions;

use App\Domains\Guests\Models\GuestInvitation;
use App\Domains\Identity\Services\RealtimeTokenService;
use App\Domains\Tenancy\Models\OrganizationSetting;
use Illuminate\Support\Str;

/**
 * Resolves everything the `office` view needs to seat a guest: which
 * floor/map/room they land in, their realtime token, spawn point, and any
 * cross-branch warning — the logic that used to live inline in
 * GuestAccessController::guestEnter(). See Architecture Audit §8/§15.
 */
class BuildGuestOfficeSessionAction
{
    public function execute(GuestInvitation $invitation, string $guestName, RealtimeTokenService $tokenService): array
    {
        $organization = $invitation->organization;
        $room = $invitation->room;

        $targetRoom = $room;
        $floor = null;
        if ($targetRoom && $targetRoom->map && $targetRoom->map->floor) {
            $floor = $targetRoom->map->floor;
        } elseif ($targetRoom && $targetRoom->floor_id) {
            $floor = $organization->floors()->find($targetRoom->floor_id);
        }
        if (! $floor) {
            $floor = $organization->floors()->where('is_default', true)->first() ?: $organization->floors()->first();
        }

        $map = ($targetRoom && $targetRoom->map) ? $targetRoom->map : (
            $organization->maps()->where('floor_id', $floor->id)->where('status', 'published')->latest('published_at')->first()
            ?? $organization->maps()->where('floor_id', $floor->id)->latest()->first()
        );

        $map->load(['rooms', 'zones', 'objects']);

        $guestId = 'guest_'.Str::random(24);
        $realtimeToken = $tokenService->generateGuestTokenWithId($guestId, $guestName, $organization);
        $wsUrl = env('REALTIME_WS_URL', env('VITE_REALTIME_WS_URL', 'ws://127.0.0.1:8080'));

        $user = (object) [
            'id' => $guestId,
            'name' => "{$guestName} (Guest)",
            'email' => "{$guestId}@guest.local",
            'avatar_url' => null,
            'is_guest' => true,
            'gender' => 'male',
            'role' => 'guest',
            'profile' => null,
        ];

        $tileSize = $map->tile_size ?: 16;
        $targetRoom = $map->rooms->where('id', $room->id)->first() ?? $room ?? $map->rooms->first();

        if ($targetRoom && ! empty($targetRoom->bounds)) {
            $initialSpawn = [
                'x' => round(($targetRoom->bounds['x'] + ($targetRoom->bounds['width'] / 2)) * $tileSize),
                'y' => round(($targetRoom->bounds['y'] + ($targetRoom->bounds['height'] / 2)) * $tileSize),
            ];
        } else {
            $initialSpawn = [
                'x' => 320,
                'y' => 240,
            ];
        }

        $allOffices = $floor ? collect([$floor]) : collect();
        $userAllowedOffices = $allOffices;
        $userAllowedRoomIds = $targetRoom ? [$targetRoom->id] : [];

        $orgDefaultFloor = $organization->floors()->where('is_default', true)->first() ?: $organization->floors()->first();
        $isDifferentBranch = ($orgDefaultFloor && $floor && $orgDefaultFloor->id !== $floor->id);
        $branchWarning = null;
        if ($isDifferentBranch) {
            $branchWarning = __('Notice for Guest: You are currently entering branch ":branch", while your host / team\'s default active branch is ":default_branch". If you do not see your host, please notify them to switch to this branch.', [
                'branch' => $floor->name,
                'default_branch' => $orgDefaultFloor->name,
            ]);
        }

        $attendancePolicy = optional($organization->settings)->getAttendancePolicy()
            ?? OrganizationSetting::getAttendancePolicy();

        return [
            'user' => $user,
            'invitation' => $invitation,
            'organization' => $organization,
            'floor' => $floor,
            'map' => $map,
            'room' => $room,
            'allOffices' => $allOffices,
            'userAllowedOffices' => $userAllowedOffices,
            'userAllowedRoomIds' => $userAllowedRoomIds,
            'realtimeToken' => $realtimeToken,
            'wsUrl' => $wsUrl,
            'initialSpawn' => $initialSpawn,
            'branchWarning' => $branchWarning,
            'isDifferentBranch' => $isDifferentBranch,
            'orgDefaultFloor' => $orgDefaultFloor,
            'attendancePolicy' => $attendancePolicy,
        ];
    }
}
