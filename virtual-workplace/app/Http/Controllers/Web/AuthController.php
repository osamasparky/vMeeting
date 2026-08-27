<?php

namespace App\Http\Controllers\Web;

use App\Domains\Identity\Actions\CreateOrganizationAction;
use App\Domains\Identity\Models\User;
use App\Domains\People\Models\UserProfile;
use App\Domains\Tenancy\Models\OrganizationMember;
use App\Domains\Tenancy\Models\Plan;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    /**
     * Show the login page.
     */
    public function showLogin()
    {
        if (Auth::check()) {
            if (Auth::user()->isSuperAdmin()) {
                return redirect()->route('superadmin.dashboard');
            }

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

            if (Auth::user()->isSuperAdmin()) {
                return redirect()->intended(route('superadmin.dashboard'));
            }

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
            if (Auth::user()->isSuperAdmin()) {
                return redirect()->route('superadmin.dashboard');
            }

            return redirect()->route('dashboard');
        }

        $plans = Plan::where('is_active', true)->orderBy('price', 'asc')->get();

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

        // Find requested plan
        $selectedPlan = null;
        if (! empty($validated['plan_id'])) {
            $selectedPlan = Plan::find($validated['plan_id']);
        } elseif (! empty($validated['plan_slug'])) {
            $selectedPlan = Plan::where('slug', $validated['plan_slug'])->first();
        }

        // Create organization with user as admin (initially with selected plan if free, or base plan)
        $freePlan = Plan::where('slug', 'free')->first() ?? Plan::where('price', 0)->first() ?? Plan::first();
        $isPaidPlan = $selectedPlan && (float) $selectedPlan->price > 0;

        $createOrgAction->execute(
            [
                'name' => $validated['organization_name'],
                'plan_id' => $isPaidPlan ? $freePlan?->id : ($selectedPlan?->id ?? $freePlan?->id),
            ],
            $user
        );

        // Log in
        Auth::login($user);

        if ($isPaidPlan && $selectedPlan) {
            return redirect()->route('subscription.payment', ['plan' => $selectedPlan->id])
                ->with('info', "مرحباً بك في Virtual Workplace! يرجى إتمام التحويل البنكي لتفعيل اشتراك باقة ({$selectedPlan->name}).");
        }

        return redirect()->route('dashboard');
    }

    /**
     * Update user profile information.
     */
    public function updateProfile(Request $request)
    {
        $user = Auth::user();
        $membership = OrganizationMember::where('user_id', $user->id)->first();
        if (! $membership) {
            abort(403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'nickname' => 'nullable|string|max:100',
            'email' => 'required|email|max:255|unique:users,email,'.$user->id,
            'date_of_birth' => 'nullable|date',
            'phone' => 'nullable|string|max:50',
            'job_title' => 'nullable|string|max:150',
            'work_mode' => 'nullable|string|in:remote,hybrid,onsite',
            'bio' => 'nullable|string|max:1000',
            'hobbies' => 'nullable|string|max:1000',
            'skills' => 'nullable|string|max:1000',
            'notes' => 'nullable|string|max:2000',
            'linkedin' => 'nullable|string|max:255',
            'github' => 'nullable|string|max:255',
            'twitter' => 'nullable|string|max:255',
            'website' => 'nullable|string|max:255',
            'avatar' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp|max:4096',
        ]);

        // 1. Update user fields
        $user->name = $validated['name'];
        $user->nickname = $validated['nickname'] ?? null;
        $user->email = $validated['email'];

        // Handle avatar upload
        if ($request->hasFile('avatar')) {
            $file = $request->file('avatar');
            $filename = 'user_'.$user->id.'_'.time().'.'.$file->getClientOriginalExtension();
            $path = $file->storeAs('avatars', $filename, 'public');
            $user->avatar_url = '/storage/'.$path;
        }

        $user->save();

        // 2. Update user profile
        $profile = UserProfile::firstOrNew([
            'user_id' => $user->id,
            'organization_id' => $membership->organization_id,
        ]);

        $profile->job_title = $validated['job_title'] ?? null;
        $profile->phone = $validated['phone'] ?? null;
        $profile->date_of_birth = $validated['date_of_birth'] ?? null;
        $profile->work_mode = $validated['work_mode'] ?? 'remote';
        $profile->bio = $validated['bio'] ?? null;
        $profile->hobbies = $validated['hobbies'] ?? null;
        $profile->skills = $validated['skills'] ?? null;
        $profile->notes = $validated['notes'] ?? null;

        $socialLinks = [
            'linkedin' => $validated['linkedin'] ?? '',
            'github' => $validated['github'] ?? '',
            'twitter' => $validated['twitter'] ?? '',
            'website' => $validated['website'] ?? '',
        ];
        $profile->social_links = array_filter($socialLinks);

        $profile->save();

        return redirect('/dashboard#profile')->with('success', __('Your personal profile and details have been updated successfully!'));
    }

    /**
     * Update user account security / password.
     */
    public function updatePassword(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'current_password' => 'required|current_password',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user->password = Hash::make($validated['password']);
        $user->save();

        return redirect('/dashboard#profile')->with('success', __('Your password has been changed successfully!'));
    }

    /**
     * Impersonate a team member within the organization.
     */
    public function impersonateMember(OrganizationMember $member)
    {
        $currentUser = Auth::user();
        $currentMembership = OrganizationMember::where('organization_id', $member->organization_id)
            ->where('user_id', $currentUser->id)
            ->first();

        $canImpersonate = $currentUser->isSuperAdmin()
            || ($currentMembership && ($currentMembership->role?->slug === 'company_admin' || $currentMembership->hasPermission('users.manage')));

        if (! $canImpersonate) {
            abort(403, __('You do not have authorization to impersonate members.'));
        }

        if ($member->user_id === $currentUser->id) {
            return back()->with('error', __('You cannot impersonate your own account.'));
        }

        // Store original user ID in session
        session([
            'org_impersonator_id' => $currentUser->id,
            'org_impersonator_name' => $currentUser->name,
            'org_impersonated_member_name' => $member->user->name,
        ]);

        Auth::loginUsingId($member->user_id);

        return redirect()->route('dashboard')->with('success', __('Switched session: Logged in as :name', ['name' => $member->user->name]));
    }

    /**
     * Leave member impersonation and return to admin account.
     */
    public function leaveMemberImpersonation()
    {
        $origId = session('org_impersonator_id');
        $memberName = session('org_impersonated_member_name');

        if ($origId) {
            session()->forget(['org_impersonator_id', 'org_impersonator_name', 'org_impersonated_member_name']);
            Auth::loginUsingId($origId);

            return redirect()->route('dashboard')->with('success', __('Returned to admin account from :name', ['name' => $memberName]));
        }

        return redirect()->route('dashboard');
    }

    /**
     * Log out the current user.
     */
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
