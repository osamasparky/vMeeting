<?php

namespace App\Http\Controllers\Web;

use App\Domains\Guests\Actions\BuildGuestOfficeSessionAction;
use App\Domains\Guests\Models\GuestInvitation;
use App\Domains\Guests\Requests\GuestEnterRequest;
use App\Domains\Identity\Services\RealtimeTokenService;
use App\Domains\Tenancy\Models\OrganizationMember;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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
    public function guestEnter(GuestEnterRequest $request, string $token, RealtimeTokenService $tokenService, BuildGuestOfficeSessionAction $buildSession)
    {
        $invitation = GuestInvitation::where('token', $token)
            ->with(['organization', 'room.map.floor', 'host'])
            ->first();

        if (! $invitation || $invitation->isExpired()) {
            return redirect()->route('guest.join', $token)->with('error', 'Invitation expired or invalid.');
        }

        $sessionData = $buildSession->execute($invitation, $request->validated('guest_name'), $tokenService);

        return view('office', $sessionData);
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
