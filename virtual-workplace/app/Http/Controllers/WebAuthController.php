<?php

namespace App\Http\Controllers;

use App\Domains\Administration\Models\Role;
use App\Domains\Identity\Models\User;
use App\Domains\Identity\Services\RealtimeTokenService;
use App\Domains\Tenancy\Actions\CreateOrganizationAction;
use App\Domains\Tenancy\Models\OrganizationMember;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class WebAuthController extends Controller
{
    /**
     * Show the login page.
     */
    public function showLogin()
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }

        return view('auth.login');
    }

    /**
     * Handle login form submission.
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();

            return redirect()->intended(route('dashboard'));
        }

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ])->onlyInput('email');
    }

    /**
     * Show the registration page.
     */
    public function showRegister()
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }

        return view('auth.register');
    }

    /**
     * Handle registration form submission.
     */
    public function register(Request $request, CreateOrganizationAction $createOrgAction)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'organization_name' => ['required', 'string', 'max:255'],
        ]);

        // Create user
        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
        ]);

        // Create organization with user as admin
        $createOrgAction->execute(
            ['name' => $validated['organization_name']],
            $user
        );

        // Log in
        Auth::login($user);

        return redirect()->route('dashboard');
    }

    /**
     * Show the dashboard.
     */
    public function dashboard()
    {
        $user = Auth::user();

        // Get the first active membership
        $membership = OrganizationMember::where('user_id', $user->id)
            ->where('status', 'active')
            ->with(['organization.plan', 'role'])
            ->first();

        if (!$membership) {
            return redirect()->route('login')->with('error', 'No active organization found.');
        }

        $organization = $membership->organization;
        $this->ensureDefaultWorkspace($organization);

        $stats = [
            'members' => OrganizationMember::where('organization_id', $organization->id)->where('status', 'active')->count(),
            'departments' => $organization->departments()->count(),
            'teams' => $organization->teams()->count(),
        ];

        $rooms = $organization->rooms()->get();
        $roles = \App\Domains\Administration\Models\Role::where('organization_id', $organization->id)->orWhereNull('organization_id')->get();
        $members = $organization->members()->with(['user', 'role'])->get();
        $departments = $organization->departments()->withCount('teams')->get();
        $teams = $organization->teams()->with('department')->get();
        $auditLogs = \App\Domains\Administration\Models\AuditLog::where('organization_id', $organization->id)->latest()->take(20)->get();
        $guestInvitations = \App\Domains\Guests\Models\GuestInvitation::where('organization_id', $organization->id)->with('room')->latest()->take(20)->get();

        return view('dashboard', compact('user', 'membership', 'organization', 'stats', 'rooms', 'roles', 'members', 'departments', 'teams', 'auditLogs', 'guestInvitations'));
    }

    /**
     * Show the interactive Virtual Office floor.
     */
    public function office(\App\Domains\Identity\Services\RealtimeTokenService $tokenService)
    {
        $user = Auth::user();

        $membership = OrganizationMember::where('user_id', $user->id)
            ->where('status', 'active')
            ->with(['organization'])
            ->first();

        if (!$membership) {
            return redirect()->route('login')->with('error', 'No active organization found.');
        }

        $organization = $membership->organization;
        $this->ensureDefaultWorkspace($organization);

        $floor = $organization->floors()->first();
        $map = $organization->maps()->where('floor_id', $floor->id)->where('status', 'published')->latest('published_at')->first()
            ?? $organization->maps()->where('floor_id', $floor->id)->latest()->first();
        $map->load(['rooms', 'zones', 'objects']);

        $realtimeToken = $tokenService->generateToken($user, $organization);
        $wsUrl = env('REALTIME_WS_URL', 'ws://127.0.0.1:8080');

        return view('office', compact('user', 'organization', 'floor', 'map', 'realtimeToken', 'wsUrl'));
    }

    /**
     * Show the Visual Office Map Editor & Floor Designer.
     */
    public function editor()
    {
        $user = Auth::user();

        $membership = OrganizationMember::where('user_id', $user->id)
            ->where('status', 'active')
            ->with(['organization.plan'])
            ->first();

        if (!$membership) {
            return redirect()->route('login')->with('error', 'No active organization found.');
        }

        $organization = $membership->organization;
        $this->ensureDefaultWorkspace($organization);

        $floor = $organization->floors()->first();
        $map = $organization->maps()->where('floor_id', $floor->id)->where('status', 'published')->latest('published_at')->first()
            ?? $organization->maps()->where('floor_id', $floor->id)->latest()->first();
        $map->load(['rooms', 'zones', 'objects', 'versions']);
        $floors = $organization->floors()->get();

        return view('editor', compact('user', 'organization', 'floor', 'floors', 'map'));
    }

    /**
     * Helper to guarantee default floor, map, and meeting rooms exist.
     */
    private function ensureDefaultWorkspace(\App\Domains\Tenancy\Models\Organization $organization): void
    {
        $floor = $organization->floors()->firstOrCreate(
            ['name' => 'Main Floor'],
            ['order' => 1]
        );

        $map = $organization->maps()->where('floor_id', $floor->id)->first();
        if (!$map) {
            $map = \App\Domains\Workspace\Models\Map::create([
                'organization_id' => $organization->id,
                'floor_id' => $floor->id,
                'name' => 'Main Headquarters',
                'status' => 'published',
                'version' => 1,
                'width' => 32,
                'height' => 24,
                'tile_size' => 32,
                'layout_data' => [
                    'theme' => 'modern_dark',
                ],
                'published_at' => now(),
            ]);
        }

        if ($organization->rooms()->count() === 0) {
            \App\Domains\Workspace\Models\Room::create([
                'organization_id' => $organization->id,
                'map_id' => $map->id,
                'name' => 'Main Conference Room',
                'type' => 'meeting',
                'access_mode' => 'public',
                'capacity' => 12,
                'color' => '#6366F1',
                'bounds' => ['x' => 2, 'y' => 2, 'width' => 8, 'height' => 6],
            ]);

            \App\Domains\Workspace\Models\Room::create([
                'organization_id' => $organization->id,
                'map_id' => $map->id,
                'name' => 'Design Studio',
                'type' => 'meeting',
                'access_mode' => 'public',
                'capacity' => 8,
                'color' => '#EC4899',
                'bounds' => ['x' => 22, 'y' => 2, 'width' => 8, 'height' => 6],
            ]);

            \App\Domains\Workspace\Models\Room::create([
                'organization_id' => $organization->id,
                'map_id' => $map->id,
                'name' => 'Executive Office',
                'type' => 'private',
                'access_mode' => 'private',
                'capacity' => 4,
                'color' => '#F59E0B',
                'bounds' => ['x' => 22, 'y' => 16, 'width' => 8, 'height' => 6],
            ]);
        }
    }


    /**
     * Show Guest Invitation Lobby Page.
     */
    public function guestJoin(string $token)
    {
        $invitation = \App\Domains\Guests\Models\GuestInvitation::where('token', $token)
            ->with(['organization', 'room', 'host'])
            ->first();

        if (!$invitation) {
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
        $invitation = \App\Domains\Guests\Models\GuestInvitation::where('token', $token)
            ->with(['organization', 'room.map.floor', 'host'])
            ->first();

        if (!$invitation || $invitation->isExpired()) {
            return redirect()->route('guest.join', $token)->with('error', 'Invitation expired or invalid.');
        }

        $validated = $request->validate([
            'guest_name' => ['required', 'string', 'max:100'],
        ]);

        $guestName = $validated['guest_name'];
        $organization = $invitation->organization;
        $room = $invitation->room;
        
        $floor = $organization->floors()->first();
        $map = $organization->maps()->where('floor_id', $floor->id)->where('status', 'published')->latest('published_at')->first()
            ?? $organization->maps()->where('floor_id', $floor->id)->latest()->first();

        $map->load(['rooms', 'zones', 'objects']);

        $guestId = 'guest_' . md5($token . '_' . microtime(true));
        $realtimeToken = $tokenService->generateGuestTokenWithId($guestId, $guestName, $organization);
        $wsUrl = env('REALTIME_WS_URL', 'ws://127.0.0.1:8080');

        $user = (object)[
            'id' => $guestId,
            'name' => "{$guestName} (Guest)",
            'email' => "{$guestId}@guest.local",
            'avatar_url' => null,
            'is_guest' => true,
        ];

        $targetRoom = $map->rooms->where('id', $room->id)->first() ?? $map->rooms->first() ?? $room;

        $initialSpawn = [
            'x' => ($targetRoom->bounds['x'] + $targetRoom->bounds['width'] / 2) * 32,
            'y' => ($targetRoom->bounds['y'] + $targetRoom->bounds['height'] / 2) * 32,
        ];

        return view('office', compact('user', 'invitation', 'organization', 'floor', 'map', 'room', 'realtimeToken', 'wsUrl', 'initialSpawn'));
    }

    /**
     * Logout.
     */
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}



