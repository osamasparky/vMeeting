<?php

namespace App\Http\Controllers\Web;

use App\Domains\Guests\Models\GuestInvitation;
use App\Domains\Identity\Services\RealtimeTokenService;
use App\Domains\Tenancy\Models\OrganizationMember;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class GuestAccessController extends Controller
{
    /**
     * Show Guest Invitation Lobby Page.
     */
    public function guestJoin(string $token)
    {
        $invitation = GuestInvitation::where('token', $token)
            ->with(['organization', 'room', 'host'])
            ->first();

        if (! $invitation) {
            return view('guest_join', ['error' => 'This invitation link is invalid or does not exist.']);
        }

        if ($invitation->isExpired()) {
            return view('guest_join', ['error' => 'This invitation link has expired. Please ask the host for a new link.']);
        }

        return view('guest_join', compact('invitation'));
    }

    /**
     * Enter the Virtual Office as a Guest.
     */
    public function guestEnter(Request $request, string $token, RealtimeTokenService $tokenService)
    {
        $invitation = GuestInvitation::where('token', $token)
            ->with(['organization', 'room.map.floor', 'host'])
            ->first();

        if (! $invitation || $invitation->isExpired()) {
            return redirect()->route('guest.join', $token)->with('error', 'Invitation expired or invalid.');
        }

        $validated = $request->validate([
            'guest_name' => ['required', 'string', 'max:100'],
        ]);

        $guestName = $validated['guest_name'];
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

        $initialSpawn = null;
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
            ?? \App\Domains\Tenancy\Models\OrganizationSetting::getAttendancePolicy();

        return view('office', compact('user', 'invitation', 'organization', 'floor', 'map', 'room', 'allOffices', 'userAllowedOffices', 'userAllowedRoomIds', 'realtimeToken', 'wsUrl', 'initialSpawn', 'branchWarning', 'isDifferentBranch', 'orgDefaultFloor', 'attendancePolicy'));
    }

    /**
     * Clear all guest meeting links for the organization.
     */
    public function clearGuestInvitations(Request $request)
    {
        $user = Auth::user();
        $membership = OrganizationMember::where('user_id', $user->id)->with('role.permissions')->first();
        if (! $membership) {
            abort(403);
        }

        if (! $membership->hasPermission('organizations.manage') && $membership->role?->slug !== 'company_admin') {
            abort(403, 'Unauthorized: insufficient permissions.');
        }

        GuestInvitation::where('organization_id', $membership->organization_id)->delete();

        return back()->with('success', __('All guest meeting links have been cleared successfully.'));
    }
}
