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
use Illuminate\Support\Facades\Cache;
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

        $plans = \App\Domains\Tenancy\Models\Plan::where('is_active', true)->orderBy('price', 'asc')->get();

        return view('auth.register', compact('plans'));
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
            'plan_id' => ['nullable', 'exists:plans,id'],
            'plan_slug' => ['nullable', 'string'],
        ]);

        // Create user
        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
        ]);

        // Create organization with user as admin and assigned plan
        $createOrgAction->execute(
            [
                'name' => $validated['organization_name'],
                'plan_id' => $validated['plan_id'] ?? null,
                'plan_slug' => $validated['plan_slug'] ?? null,
            ],
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

        // Get the active/invited membership
        $membership = OrganizationMember::where('user_id', $user->id)
            ->whereIn('status', ['active', 'invited'])
            ->with(['organization.plan', 'role'])
            ->first();

        if (!$membership) {
            return redirect()->route('login')->with('error', 'No active organization found.');
        }

        if ($membership->status === 'invited') {
            $membership->update(['status' => 'active']);
        }

        $organization = $membership->organization;
        $this->ensureDefaultWorkspace($organization);

        $totalMembers = OrganizationMember::where('organization_id', $organization->id)->whereIn('status', ['active', 'invited'])->count();
        $totalDepts = $organization->departments()->count();
        $totalTeams = $organization->teams()->count();
        $totalRooms = $organization->rooms()->count();
        $totalGuests = \App\Domains\Guests\Models\GuestInvitation::where('organization_id', $organization->id)->count();
        $totalAudit = \App\Domains\Administration\Models\AuditLog::where('organization_id', $organization->id)->count();

        $stats = [
            'members' => $totalMembers,
            'departments' => $totalDepts,
            'teams' => $totalTeams,
            'rooms' => $totalRooms,
            'guests' => $totalGuests,
            'presence_rate' => 94,
            'meetings_count' => max(12, $totalAudit * 3 + 8),
            'collaboration_hours' => max(48, $totalAudit * 14 + 32),
            'occupancy_rate' => 78,
            'productivity_score' => 98.4,
            'screen_share_rate' => 91,
            'audio_quality' => '99.98%',
        ];

        $rooms = $organization->rooms()->get();
        $roles = \App\Domains\Administration\Models\Role::where('organization_id', $organization->id)->orWhereNull('organization_id')->get();
        $members = $organization->members()->with(['user.profiles', 'role'])->get();
        $departments = $organization->departments()->withCount('teams')->get();
        $teams = $organization->teams()->with('department')->get();
        $auditLogs = \App\Domains\Administration\Models\AuditLog::where('organization_id', $organization->id)->latest()->take(20)->get();
        $guestInvitations = \App\Domains\Guests\Models\GuestInvitation::where('organization_id', $organization->id)->with('room')->latest()->take(20)->get();
        $allPlans = \App\Domains\Tenancy\Models\Plan::where('is_active', true)->orderBy('price', 'asc')->get();

        return view('dashboard', compact('user', 'membership', 'organization', 'stats', 'rooms', 'roles', 'members', 'departments', 'teams', 'auditLogs', 'guestInvitations', 'allPlans'));
    }

    /**
     * Upgrade / switch company subscription plan from dashboard.
     */
    public function upgradePlan(Request $request)
    {
        $user = Auth::user();
        $membership = OrganizationMember::where('user_id', $user->id)
            ->whereIn('status', ['active', 'invited'])
            ->with(['organization', 'role.permissions'])
            ->first();

        if (!$membership) {
            return redirect()->route('login');
        }

        if (!$membership->hasPermission('organizations.manage') && $membership->role?->slug !== 'company_admin') {
            abort(403, 'Unauthorized: only organization admins can modify subscription plans.');
        }

        $validated = $request->validate([
            'plan_id' => ['required', 'exists:plans,id'],
        ]);

        $organization = $membership->organization;
        $newPlan = \App\Domains\Tenancy\Models\Plan::findOrFail($validated['plan_id']);

        $organization->update(['plan_id' => $newPlan->id]);

        return back()->with('success', "Subscription successfully upgraded to {$newPlan->name} Plan!");
    }

    /**
     * Show the interactive Virtual Office floor.
     */
    public function office(\App\Domains\Identity\Services\RealtimeTokenService $tokenService)
    {
        $user = Auth::user();

        $membership = OrganizationMember::where('user_id', $user->id)
            ->whereIn('status', ['active', 'invited'])
            ->with(['organization'])
            ->first();

        if (!$membership) {
            return redirect()->route('login')->with('error', 'No active organization found.');
        }

        if ($membership->status === 'invited') {
            $membership->update(['status' => 'active']);
        }

        $organization = $membership->organization;
        $this->ensureDefaultWorkspace($organization);

        $floor = $organization->floors()->first();
        $map = $organization->maps()->where('floor_id', $floor->id)->where('status', 'published')->latest('published_at')->first()
            ?? $organization->maps()->where('floor_id', $floor->id)->latest()->first();
        $map->load(['rooms', 'zones', 'objects']);

        $realtimeToken = $tokenService->generateToken($user, $organization);
        $wsUrl = env('REALTIME_WS_URL', 'ws://127.0.0.1:8080');

        $furnitureItems = Cache::remember('furniture_catalog_active', 86400, function () {
            return \App\Domains\Workspace\Models\FurnitureItem::where('is_active', true)->get();
        });

        return view('office', compact('user', 'organization', 'floor', 'map', 'realtimeToken', 'wsUrl', 'furnitureItems'));
    }

    /**
     * Show the Visual Office Map Editor & Floor Designer.
     */
    public function editor()
    {
        $user = Auth::user();

        $membership = OrganizationMember::where('user_id', $user->id)
            ->whereIn('status', ['active', 'invited'])
            ->with(['organization.plan'])
            ->first();

        if (!$membership) {
            return redirect()->route('login')->with('error', 'No active organization found.');
        }

        if ($membership->status === 'invited') {
            $membership->update(['status' => 'active']);
        }

        $organization = $membership->organization;
        $this->ensureDefaultWorkspace($organization);

        $floor = $organization->floors()->first();
        $map = $organization->maps()->where('floor_id', $floor->id)->where('status', 'published')->latest('published_at')->first()
            ?? $organization->maps()->where('floor_id', $floor->id)->latest()->first();
        $map->load(['rooms', 'zones', 'objects', 'versions']);
        $floors = $organization->floors()->get();

        $furnitureCategories = Cache::remember('furniture_categories_with_items', 86400, function () {
            return \App\Domains\Workspace\Models\FurnitureCategory::with('items')
                ->orderBy('order', 'asc')
                ->get();
        });

        $furnitureItems = Cache::remember('furniture_catalog_active', 86400, function () {
            return \App\Domains\Workspace\Models\FurnitureItem::where('is_active', true)->get();
        });

        return view('editor', compact('user', 'organization', 'floor', 'floors', 'map', 'furnitureCategories', 'furnitureItems'));
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

        if ($organization->departments()->count() === 0) {
            $eng = \App\Domains\People\Models\Department::create([
                'organization_id' => $organization->id,
                'name' => 'Engineering & Technology',
            ]);
            \App\Domains\People\Models\Team::create(['organization_id' => $organization->id, 'department_id' => $eng->id, 'name' => 'Frontend Team']);
            \App\Domains\People\Models\Team::create(['organization_id' => $organization->id, 'department_id' => $eng->id, 'name' => 'Backend & Cloud']);

            $sales = \App\Domains\People\Models\Department::create([
                'organization_id' => $organization->id,
                'name' => 'Sales & Business Growth',
            ]);
            \App\Domains\People\Models\Team::create(['organization_id' => $organization->id, 'department_id' => $sales->id, 'name' => 'Enterprise Sales']);

            $design = \App\Domains\People\Models\Department::create([
                'organization_id' => $organization->id,
                'name' => 'Product & Design',
            ]);
            \App\Domains\People\Models\Team::create(['organization_id' => $organization->id, 'department_id' => $design->id, 'name' => 'UI / UX Design']);
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
     * Store new Department.
     */
    public function storeDepartment(\App\Domains\People\Requests\StoreDepartmentRequest $request)
    {
        $user = Auth::user();
        $membership = OrganizationMember::where('user_id', $user->id)->with('role.permissions')->first();
        if (!$membership) abort(403);

        if (!$membership->hasPermission('departments.manage') && $membership->role?->slug !== 'company_admin') {
            abort(403, 'Unauthorized: insufficient permissions to manage departments.');
        }

        $validated = $request->validated();

        \App\Domains\People\Models\Department::create([
            'organization_id' => $membership->organization_id,
            'name' => $validated['name'],
        ]);

        return back()->with('success', 'Department created successfully.');
    }

    /**
     * Update Department.
     */
    public function updateDepartment(\App\Domains\People\Requests\StoreDepartmentRequest $request, \App\Domains\People\Models\Department $department)
    {
        $user = Auth::user();
        $membership = OrganizationMember::where('user_id', $user->id)->with('role.permissions')->first();
        if (!$membership) abort(403);

        if ($department->organization_id !== $membership->organization_id) {
            abort(403, 'Unauthorized department access.');
        }

        if (!$membership->hasPermission('departments.manage') && $membership->role?->slug !== 'company_admin') {
            abort(403, 'Unauthorized: insufficient permissions.');
        }

        $validated = $request->validated();

        $department->update(['name' => $validated['name']]);
        return back()->with('success', 'Department updated successfully.');
    }

    /**
     * Delete Department.
     */
    public function deleteDepartment(\App\Domains\People\Models\Department $department)
    {
        $user = Auth::user();
        $membership = OrganizationMember::where('user_id', $user->id)->with('role.permissions')->first();
        if (!$membership) abort(403);

        if ($department->organization_id !== $membership->organization_id) {
            abort(403, 'Unauthorized department access.');
        }

        if (!$membership->hasPermission('departments.manage') && $membership->role?->slug !== 'company_admin') {
            abort(403, 'Unauthorized: insufficient permissions.');
        }

        $department->teams()->delete();
        $department->delete();
        return back()->with('success', 'Department deleted successfully.');
    }

    /**
     * Store new Team in Department.
     */
    public function storeTeam(\App\Domains\People\Requests\StoreTeamRequest $request)
    {
        $user = Auth::user();
        $membership = OrganizationMember::where('user_id', $user->id)->with('role.permissions')->first();
        if (!$membership) abort(403);

        if (!$membership->hasPermission('teams.manage') && $membership->role?->slug !== 'company_admin') {
            abort(403, 'Unauthorized: insufficient permissions to manage teams.');
        }

        $validated = $request->validated();

        // Verify target department belongs to user's organization
        $department = \App\Domains\People\Models\Department::findOrFail($validated['department_id']);
        if ($department->organization_id !== $membership->organization_id) {
            abort(403, 'Unauthorized department access.');
        }

        \App\Domains\People\Models\Team::create([
            'organization_id' => $membership->organization_id,
            'department_id' => $department->id,
            'name' => $validated['name'],
        ]);

        return back()->with('success', 'Team created successfully.');
    }

    /**
     * Delete Team.
     */
    public function deleteTeam(\App\Domains\People\Models\Team $team)
    {
        $user = Auth::user();
        $membership = OrganizationMember::where('user_id', $user->id)->with('role.permissions')->first();
        if (!$membership) abort(403);

        if ($team->organization_id !== $membership->organization_id) {
            abort(403, 'Unauthorized team access.');
        }

        if (!$membership->hasPermission('teams.manage') && $membership->role?->slug !== 'company_admin') {
            abort(403, 'Unauthorized: insufficient permissions.');
        }

        $team->delete();
        return back()->with('success', 'Team deleted successfully.');
    }

    /**
     * Assign member to department, team, role, and job title.
     */
    public function assignMemberDepartment(\App\Domains\People\Requests\AssignMemberDepartmentRequest $request, OrganizationMember $member)
    {
        $user = Auth::user();
        $membership = OrganizationMember::where('user_id', $user->id)->with('role.permissions')->first();
        if (!$membership) abort(403);

        // Strict tenant boundary verification
        if ($member->organization_id !== $membership->organization_id) {
            abort(403, 'Unauthorized member access.');
        }

        // Administrative permission required to change members/roles
        if (!$membership->hasPermission('members.manage') && $membership->role?->slug !== 'company_admin') {
            abort(403, 'Unauthorized: insufficient permissions to manage members.');
        }

        $validated = $request->validated();

        // Verify department belongs to this organization
        if (!empty($validated['department_id'])) {
            $dept = \App\Domains\People\Models\Department::findOrFail($validated['department_id']);
            if ($dept->organization_id !== $membership->organization_id) {
                abort(403, 'Invalid department selection.');
            }
        }

        // Verify team belongs to this organization
        if (!empty($validated['team_id'])) {
            $team = \App\Domains\People\Models\Team::findOrFail($validated['team_id']);
            if ($team->organization_id !== $membership->organization_id) {
                abort(403, 'Invalid team selection.');
            }
        }

        // Verify role is global or belongs to this organization
        if (!empty($validated['role_id'])) {
            $role = \App\Domains\Administration\Models\Role::findOrFail($validated['role_id']);
            if ($role->organization_id && $role->organization_id !== $membership->organization_id) {
                abort(403, 'Invalid role selection.');
            }
            $member->update(['role_id' => $role->id]);
        }

        $profile = \App\Domains\People\Models\UserProfile::firstOrNew([
            'user_id' => $member->user_id,
            'organization_id' => $member->organization_id,
        ]);

        $profile->department_id = $validated['department_id'] ?? null;
        $profile->team_id = $validated['team_id'] ?? null;
        if (isset($validated['job_title'])) {
            $profile->job_title = $validated['job_title'];
        }
        $profile->save();

        return back()->with('success', 'Member department assignment updated.');
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



