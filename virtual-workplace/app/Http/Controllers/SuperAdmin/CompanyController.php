<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Domains\Administration\Models\AuditLog;
use App\Domains\Administration\Models\Role;
use App\Domains\Identity\Models\User;
use App\Domains\Identity\Support\SwitchSessionUser;
use App\Domains\Tenancy\Models\Organization;
use App\Domains\Tenancy\Models\OrganizationMember;
use App\Domains\Tenancy\Models\Plan;
use App\Domains\Tenancy\Models\Subscription;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * Super Admin — Companies: list, plan, status, details, impersonation, deletion.
 * Moved verbatim from SuperAdminController (refactor phase 7).
 */
class CompanyController extends Controller
{
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
            'actor_id' => Auth::id(),
            'action' => 'superadmin.company_plan_updated',
            'metadata' => ['plan_id' => $validated['plan_id']],
        ]);

        return back()->with('success', "Updated plan for {$organization->name} successfully.");
    }

    /**
     * Toggle company active/suspended status.
     */
    public function toggleCompanyStatus(Organization $organization)
    {
        $newStatus = $organization->status === 'suspended' ? 'active' : 'suspended';
        $organization->update(['status' => $newStatus]);

        AuditLog::create([
            'organization_id' => $organization->id,
            'actor_id' => Auth::id(),
            'action' => 'superadmin.company_status_toggled',
            'metadata' => ['status' => $newStatus],
        ]);

        return back()->with('success', "Company {$organization->name} ".($newStatus === 'suspended' ? 'suspended' : 'activated').' successfully.');
    }

    /**
     * Show comprehensive company detail profile page.
     */
    public function showCompany(Organization $organization)
    {
        $user = Auth::user();

        $organization->load([
            'plan',
            'settings',
            'departments.teams',
            'teams',
            'floors.maps.rooms',
            'maps.rooms',
            'rooms',
            'members.user.profile.department',
            'members.user.profile.team',
            'members.role',
            'members.offices',
            'members.rooms',
            'projects.tasks',
            'subscriptionRequests.plan',
            'subscriptionRequests.user',
            'subscriptionRequests.reviewer',
            'auditLogs' => fn ($q) => $q->latest()->take(30),
        ]);

        $allPlans = Plan::where('is_active', true)->orderBy('price', 'asc')->get();

        $stats = [
            'total_members' => $organization->members->count(),
            'active_members' => $organization->members->where('status', 'active')->count(),
            'invited_members' => $organization->members->where('status', 'invited')->count(),
            'suspended_members' => $organization->members->where('status', 'suspended')->count(),
            'departments_count' => $organization->departments->count(),
            'teams_count' => $organization->teams->count(),
            'offices_count' => $organization->floors->count(),
            'rooms_count' => $organization->rooms->count(),
            'projects_count' => $organization->projects->count(),
            'tasks_count' => $organization->tasks()->count(),
            'active_floor' => $organization->floors->first(),
            'active_map' => $organization->maps->first(),
        ];

        return view('superadmin.company_show', compact('user', 'organization', 'allPlans', 'stats'));
    }

    /**
     * Impersonate / Log into a company directly as an owner/admin.
     */
    public function impersonateCompany(Organization $organization)
    {
        $superAdminUser = Auth::user();

        // 1. Find the highest privileged member of this company (Admin, Manager, or first member)
        $adminMember = $organization->members()
            ->whereHas('role', function ($q) {
                $q->whereIn('slug', ['company_admin', 'owner', 'manager']);
            })
            ->first() ?? $organization->members()->first();

        // If company has no members, create an owner member linked to a user
        if (! $adminMember || ! $adminMember->user) {
            $companyAdminRole = Role::where('slug', 'company_admin')->first()
                ?? Role::firstOrCreate(['name' => 'Company Admin', 'slug' => 'company_admin']);

            $user = User::firstOrCreate(
                ['email' => 'admin@'.($organization->slug ?: 'company').'.local'],
                [
                    'name' => $organization->name.' Admin',
                    'password' => Hash::make(Str::random(16)),
                ]
            );

            $adminMember = OrganizationMember::create([
                'organization_id' => $organization->id,
                'user_id' => $user->id,
                'role_id' => $companyAdminRole->id,
                'status' => 'active',
                'joined_at' => now(),
            ]);
        }

        $targetUser = $adminMember->user;

        // Store impersonation metadata in session
        session([
            'superadmin_impersonator_id' => $superAdminUser->id,
            'superadmin_impersonated_org_id' => $organization->id,
            'superadmin_impersonated_org_name' => $organization->name,
        ]);

        AuditLog::create([
            'organization_id' => $organization->id,
            'actor_id' => $superAdminUser->id,
            'action' => 'superadmin.company_impersonated',
            'metadata' => [
                'target_user_id' => $targetUser->id,
                'target_user_email' => $targetUser->email,
                'company_name' => $organization->name,
            ],
        ]);

        SwitchSessionUser::to($targetUser);

        return redirect()->route('dashboard')->with('success', "⚡ Logged into {$organization->name} as {$targetUser->name}.");
    }

    /**
     * Leave impersonation and return to Super Admin portal.
     */
    public function leaveImpersonation()
    {
        $superAdminId = session('superadmin_impersonator_id');

        if (! $superAdminId) {
            return redirect()->route('login');
        }

        $superAdmin = User::find($superAdminId);

        if (! $superAdmin || ! $superAdmin->isSuperAdmin()) {
            session()->forget(['superadmin_impersonator_id', 'superadmin_impersonated_org_id', 'superadmin_impersonated_org_name']);

            return redirect()->route('login');
        }

        $impersonatedOrgName = session('superadmin_impersonated_org_name');
        session()->forget(['superadmin_impersonator_id', 'superadmin_impersonated_org_id', 'superadmin_impersonated_org_name']);

        SwitchSessionUser::to($superAdmin);

        return redirect()->route('superadmin.companies')->with('success', "🛡️ Exited impersonation of {$impersonatedOrgName} and returned to Super Admin.");
    }

    /**
     * Update general company details.
     */
    public function updateCompanyDetails(Request $request, Organization $organization)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', 'unique:organizations,slug,'.$organization->id],
            'timezone' => ['nullable', 'string', 'max:100'],
            'status' => ['required', 'in:active,suspended,trial'],
            'plan_id' => ['required', 'exists:plans,id'],
        ]);

        $organization->update($validated);

        AuditLog::create([
            'organization_id' => $organization->id,
            'actor_id' => Auth::id(),
            'action' => 'superadmin.company_updated',
            'metadata' => $validated,
        ]);

        return back()->with('success', "Company {$organization->name} details updated successfully.");
    }

    /**
     * Permanently delete a company.
     */
    public function deleteCompany(Organization $organization)
    {
        $orgName = $organization->name;
        $orgId = $organization->id;

        // Clean up relations
        $organization->members()->delete();
        $organization->departments()->delete();
        $organization->teams()->delete();
        $organization->rooms()->delete();
        $organization->maps()->delete();
        $organization->floors()->delete();
        $organization->projects()->delete();
        // Organization now has SoftDeletes (see architecture-audit ADR-007);
        // forceDelete() here keeps this endpoint's own advertised behavior
        // ("permanently") unchanged. A plain delete() elsewhere in the app --
        // accidental or from a future bug -- is recoverable instead.
        $organization->forceDelete();

        AuditLog::create([
            'organization_id' => null,
            'actor_id' => Auth::id(),
            'action' => 'superadmin.company_deleted',
            'metadata' => ['company_id' => $orgId, 'company_name' => $orgName],
        ]);

        return redirect()->route('superadmin.companies')->with('success', "Company '{$orgName}' and all its resources have been deleted permanently.");
    }
}
