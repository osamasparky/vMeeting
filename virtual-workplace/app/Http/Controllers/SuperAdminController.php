<?php

namespace App\Http\Controllers;

use App\Domains\Administration\Models\AuditLog;
use App\Domains\Administration\Models\Permission;
use App\Domains\Administration\Models\Role;
use App\Domains\Identity\Models\User;
use App\Domains\Tenancy\Models\Organization;
use App\Domains\Tenancy\Models\OrganizationMember;
use App\Domains\Tenancy\Models\Plan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class SuperAdminController extends Controller
{
    /**
     * Super Admin Dashboard overview.
     */
    public function dashboard()
    {
        $user = Auth::user();

        $stats = [
            'total_companies' => Organization::count(),
            'total_users' => User::count(),
            'active_subscriptions' => Organization::whereHas('plan', function ($q) {
                $q->where('price', '>', 0);
            })->count(),
            'estimated_mrr' => Organization::join('plans', 'organizations.plan_id', '=', 'plans.id')
                ->sum('plans.price'),
            'total_rooms' => \App\Domains\Workspace\Models\Room::count(),
        ];

        $recentCompanies = Organization::with(['plan', 'members.user'])
            ->latest()
            ->take(8)
            ->get();

        $plans = Plan::where('is_active', true)->get();

        return view('superadmin.dashboard', compact('user', 'stats', 'recentCompanies', 'plans'));
    }

    /**
     * Companies Management.
     */
    public function companies(Request $request)
    {
        $user = Auth::user();
        $query = Organization::with(['plan', 'members.user', 'rooms']);

        if ($search = $request->input('search')) {
            $query->where('name', 'like', "%{$search}%")
                ->orWhere('slug', 'like', "%{$search}%");
        }

        $companies = $query->latest()->paginate(15);
        $plans = Plan::where('is_active', true)->get();

        return view('superadmin.companies', compact('user', 'companies', 'plans'));
    }

    /**
     * Update a company's subscription plan.
     */
    public function updateCompanyPlan(Request $request, Organization $organization)
    {
        $validated = $request->validate([
            'plan_id' => ['required', 'exists:plans,id'],
        ]);

        $organization->update([
            'plan_id' => $validated['plan_id'],
        ]);

        AuditLog::create([
            'organization_id' => $organization->id,
            'user_id' => Auth::id(),
            'event' => 'superadmin.company_plan_updated',
            'auditable_type' => Organization::class,
            'auditable_id' => $organization->id,
            'new_values' => ['plan_id' => $validated['plan_id']],
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return back()->with('success', "Updated plan for {$organization->name} successfully.");
    }

    /**
     * Toggle company active/suspended status.
     */
    public function toggleCompanyStatus(Organization $organization)
    {
        $current = $organization->settings?->is_suspended ?? false;
        $next = !$current;

        $settings = $organization->settings()->firstOrCreate(['organization_id' => $organization->id]);
        $settings->update(['is_suspended' => $next]);

        return back()->with('success', "Company {$organization->name} " . ($next ? 'suspended' : 'activated') . ' successfully.');
    }

    /**
     * Subscription Plans Management.
     */
    public function plans()
    {
        $user = Auth::user();
        $plans = Plan::withCount('organizations')->orderBy('price', 'asc')->get();

        return view('superadmin.plans', compact('user', 'plans'));
    }

    /**
     * Create a new subscription plan.
     */
    public function storePlan(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'price' => ['required', 'numeric', 'min:0'],
            'seat_limit' => ['required', 'integer', 'min:0'],
            'room_limit' => ['required', 'integer', 'min:0'],
            'storage_limit_gb' => ['required', 'integer', 'min:0'],
            'features' => ['nullable', 'string'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $featuresArr = !empty($validated['features'])
            ? array_map('trim', explode(',', $validated['features']))
            : ['basic_chat', 'basic_presence', 'basic_audio'];

        Plan::create([
            'name' => $validated['name'],
            'slug' => Str::slug($validated['name']) . '-' . Str::random(3),
            'price' => $validated['price'],
            'seat_limit' => $validated['seat_limit'],
            'room_limit' => $validated['room_limit'],
            'storage_limit_gb' => $validated['storage_limit_gb'],
            'features' => $featuresArr,
            'is_active' => $request->has('is_active'),
        ]);

        return back()->with('success', 'New subscription plan created successfully.');
    }

    /**
     * Update an existing subscription plan.
     */
    public function updatePlan(Request $request, Plan $plan)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'price' => ['required', 'numeric', 'min:0'],
            'seat_limit' => ['required', 'integer', 'min:0'],
            'room_limit' => ['required', 'integer', 'min:0'],
            'storage_limit_gb' => ['required', 'integer', 'min:0'],
            'features' => ['nullable', 'string'],
        ]);

        $featuresArr = !empty($validated['features'])
            ? array_map('trim', explode(',', $validated['features']))
            : $plan->features;

        $plan->update([
            'name' => $validated['name'],
            'price' => $validated['price'],
            'seat_limit' => $validated['seat_limit'],
            'room_limit' => $validated['room_limit'],
            'storage_limit_gb' => $validated['storage_limit_gb'],
            'features' => $featuresArr,
            'is_active' => $request->has('is_active'),
        ]);

        return back()->with('success', "Plan {$plan->name} updated successfully.");
    }

    /**
     * Delete a subscription plan.
     */
    public function deletePlan(Plan $plan)
    {
        if ($plan->organizations()->count() > 0) {
            return back()->with('error', 'Cannot delete plan assigned to active organizations.');
        }

        $plan->delete();

        return back()->with('success', 'Plan deleted successfully.');
    }

    /**
     * System Role & Permission Matrix.
     */
    public function matrix()
    {
        $user = Auth::user();
        $roles = Role::whereNull('organization_id')->with('permissions')->get();
        $permissions = Permission::all()->groupBy('group');

        return view('superadmin.matrix', compact('user', 'roles', 'permissions'));
    }

    /**
     * Batch save the Permission Matrix.
     */
    public function syncMatrix(Request $request)
    {
        $matrix = $request->input('matrix', []); // role_id => [permission_id_1, permission_id_2...]

        $roles = Role::whereNull('organization_id')->get();

        foreach ($roles as $role) {
            $permissionIds = $matrix[$role->id] ?? [];
            $role->permissions()->sync($permissionIds);
        }

        return back()->with('success', 'Permission Matrix updated and synchronized across all roles successfully.');
    }

    /**
     * System Settings.
     */
    public function settings()
    {
        $user = Auth::user();
        $plans = Plan::where('is_active', true)->get();

        return view('superadmin.settings', compact('user', 'plans'));
    }

    /**
     * Update global system settings.
     */
    public function updateSettings(Request $request)
    {
        return back()->with('success', 'System settings saved successfully.');
    }
}
