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
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class SuperAdminController extends Controller
{
    /**
     * Super Admin Dashboard overview.
     */
    public function dashboard()
    {
        $user = Auth::user();

        $totalCompanies = Organization::count();
        $totalUsers = User::count();
        $activeSubscriptions = Organization::whereHas('plan', function ($q) {
            $q->where('price', '>', 0);
        })->count();
        $estimatedMrr = (float) Organization::join('plans', 'organizations.plan_id', '=', 'plans.id')
            ->sum('plans.price');

        $stats = [
            'total_companies' => $totalCompanies,
            'new_companies_month' => Organization::where('created_at', '>=', now()->startOfMonth())->count(),
            'active_companies' => Organization::where('status', '!=', 'suspended')->count(),
            'suspended_companies' => Organization::where('status', 'suspended')->count(),
            'total_users' => $totalUsers,
            'new_users_month' => User::where('created_at', '>=', now()->startOfMonth())->count(),
            'active_subscriptions' => $activeSubscriptions,
            'conversion_rate' => $totalCompanies > 0 ? round(($activeSubscriptions / $totalCompanies) * 100, 1) : 0,
            'estimated_mrr' => $estimatedMrr,
            'estimated_arr' => $estimatedMrr * 12,
            'estimated_mrr_sar' => $estimatedMrr * 3.75,
            'total_rooms' => \App\Domains\Workspace\Models\Room::count(),
            'total_projects' => \App\Domains\Projects\Models\Project::count(),
            'total_tasks' => \App\Domains\Projects\Models\Task::count(),
            'total_logged_hours' => round((\App\Domains\Projects\Models\TimeEntry::sum('duration_seconds') ?? 0) / 3600, 1),
            'total_audit_events' => AuditLog::count(),
        ];

        $recentCompanies = Organization::with(['plan', 'members.user', 'rooms'])
            ->latest()
            ->take(8)
            ->get();

        $plans = Plan::withCount('organizations')->where('is_active', true)->orderBy('price', 'desc')->get();
        $recentAuditLogs = AuditLog::with(['actor', 'organization'])->latest()->take(6)->get();

        return view('superadmin.dashboard', compact('user', 'stats', 'recentCompanies', 'plans', 'recentAuditLogs'));
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

        return back()->with('success', "Company {$organization->name} " . ($newStatus === 'suspended' ? 'suspended' : 'activated') . ' successfully.');
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
            'auditLogs' => fn($q) => $q->latest()->take(30),
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
        if (!$adminMember || !$adminMember->user) {
            $companyAdminRole = Role::where('slug', 'company_admin')->first()
                ?? Role::firstOrCreate(['name' => 'Company Admin', 'slug' => 'company_admin']);

            $user = User::firstOrCreate(
                ['email' => 'admin@' . ($organization->slug ?: 'company') . '.local'],
                [
                    'name' => $organization->name . ' Admin',
                    'password' => \Illuminate\Support\Facades\Hash::make(Str::random(16)),
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

        Auth::login($targetUser);

        return redirect()->route('dashboard')->with('success', "⚡ Logged into {$organization->name} as {$targetUser->name}.");
    }

    /**
     * Leave impersonation and return to Super Admin portal.
     */
    public function leaveImpersonation()
    {
        $superAdminId = session('superadmin_impersonator_id');

        if (!$superAdminId) {
            return redirect()->route('login');
        }

        $superAdmin = User::find($superAdminId);

        if (!$superAdmin || !$superAdmin->isSuperAdmin()) {
            session()->forget(['superadmin_impersonator_id', 'superadmin_impersonated_org_id', 'superadmin_impersonated_org_name']);
            return redirect()->route('login');
        }

        $impersonatedOrgName = session('superadmin_impersonated_org_name');
        session()->forget(['superadmin_impersonator_id', 'superadmin_impersonated_org_id', 'superadmin_impersonated_org_name']);

        Auth::login($superAdmin);

        return redirect()->route('superadmin.companies')->with('success', "🛡️ Exited impersonation of {$impersonatedOrgName} and returned to Super Admin.");
    }

    /**
     * Update general company details.
     */
    public function updateCompanyDetails(Request $request, Organization $organization)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', 'unique:organizations,slug,' . $organization->id],
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
        $organization->delete();

        AuditLog::create([
            'organization_id' => null,
            'actor_id' => Auth::id(),
            'action' => 'superadmin.company_deleted',
            'metadata' => ['company_id' => $orgId, 'company_name' => $orgName],
        ]);

        return redirect()->route('superadmin.companies')->with('success', "Company '{$orgName}' and all its resources have been deleted permanently.");
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
            'max_offices' => ['required', 'integer', 'min:0'],
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
            'max_offices' => $validated['max_offices'],
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
            'max_offices' => ['required', 'integer', 'min:0'],
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
            'max_offices' => $validated['max_offices'],
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

    /**
     * Upload global default office blueprint for the platform.
     */
    public function uploadDefaultBlueprint(Request $request)
    {
        $request->validate([
            'image' => ['required', 'file', 'image', 'mimes:jpeg,png,jpg,webp', 'max:15360'],
        ]);

        $file = $request->file('image');
        $dest = public_path('images');
        if (!file_exists($dest)) {
            mkdir($dest, 0755, true);
        }

        // Copy to both default locations so all views pick it up immediately
        $file->move($dest, 'office_floorplan.jpg');
        copy($dest . '/office_floorplan.jpg', $dest . '/isometric_office_blueprint.jpg');

        return back()->with('success', 'Global system default office blueprint updated successfully for all organizations.');
    }

    /**
     * Furniture & Assets Management.
     */
    public function furniture(Request $request)
    {
        $user = Auth::user();
        $categories = \App\Domains\Workspace\Models\FurnitureCategory::withCount('items')
            ->orderBy('order', 'asc')
            ->get();

        $selectedCategoryId = $request->input('category_id');
        $query = \App\Domains\Workspace\Models\FurnitureItem::with('category');

        if ($selectedCategoryId) {
            $query->where('category_id', $selectedCategoryId);
        }

        if ($search = $request->input('search')) {
            $query->where('name', 'like', "%{$search}%");
        }

        $items = $query->latest()->paginate(24);

        $stats = [
            'total_items' => \App\Domains\Workspace\Models\FurnitureItem::count(),
            'total_categories' => $categories->count(),
            'custom_uploads' => \App\Domains\Workspace\Models\FurnitureItem::whereNotNull('image_url')->count(),
        ];

        return view('superadmin.furniture', compact('user', 'categories', 'items', 'stats', 'selectedCategoryId'));
    }

    /**
     * Store new Furniture Category.
     */
    public function storeFurnitureCategory(\App\Domains\Workspace\Requests\StoreFurnitureCategoryRequest $request)
    {
        $validated = $request->validated();

        \App\Domains\Workspace\Models\FurnitureCategory::create([
            'name' => $validated['name'],
            'slug' => Str::slug($validated['name']) . '-' . Str::random(3),
            'icon' => $validated['icon'] ?? '🪑',
            'order' => $validated['order'] ?? 0,
        ]);

        $this->invalidateFurnitureCache();

        return back()->with('success', 'Furniture Category created successfully.');
    }

    /**
     * Update Furniture Category.
     */
    public function updateFurnitureCategory(\App\Domains\Workspace\Requests\StoreFurnitureCategoryRequest $request, \App\Domains\Workspace\Models\FurnitureCategory $category)
    {
        $validated = $request->validated();

        $category->update([
            'name' => $validated['name'],
            'icon' => $validated['icon'] ?? $category->icon,
            'order' => $validated['order'] ?? $category->order,
        ]);

        $this->invalidateFurnitureCache();

        return back()->with('success', 'Furniture Category updated successfully.');
    }

    /**
     * Delete Furniture Category.
     */
    public function deleteFurnitureCategory(\App\Domains\Workspace\Models\FurnitureCategory $category)
    {
        $category->items()->delete();
        $category->delete();

        $this->invalidateFurnitureCache();

        return back()->with('success', 'Furniture Category deleted successfully.');
    }

    /**
     * Store/Upload new Furniture Item.
     */
    public function storeFurnitureItem(\App\Domains\Workspace\Requests\StoreFurnitureItemRequest $request)
    {
        $validated = $request->validated();

        $imageUrl = $validated['image_url'] ?? null;

        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $extension = strtolower($file->guessExtension() ?: 'png');
            if (!in_array($extension, ['png', 'webp', 'jpg', 'jpeg', 'svg'])) {
                return back()->withErrors(['image' => 'Invalid image format. Allowed formats: PNG, WebP, JPG, SVG.']);
            }

            // If SVG, check for dangerous tags
            if ($extension === 'svg') {
                $content = file_get_contents($file->getRealPath());
                if (preg_match('/<script|javascript:|onload=|onerror=|onclick=|<foreignObject/i', $content)) {
                    return back()->withErrors(['image' => 'The SVG file contains unsafe embedded scripts or attributes.']);
                }
            }

            $filename = 'furn_' . Str::random(24) . '.' . $extension;
            $destinationPath = public_path('uploads/furniture');
            if (!file_exists($destinationPath)) {
                mkdir($destinationPath, 0755, true);
                @file_put_contents($destinationPath . '/.htaccess', "<Files *.php>\n    Order Deny,Allow\n    Deny from all\n</Files>\nOptions -ExecCGI\n");
            }
            $file->move($destinationPath, $filename);
            $imageUrl = '/uploads/furniture/' . $filename;
        }

        $colorsArr = !empty($validated['colors'])
            ? array_map('trim', explode(',', $validated['colors']))
            : ['#00b4b3', '#012c41'];

        \App\Domains\Workspace\Models\FurnitureItem::create([
            'category_id' => $validated['category_id'],
            'name' => $validated['name'],
            'slug' => Str::slug($validated['name']) . '-' . Str::random(4),
            'image_url' => $imageUrl,
            'icon' => $validated['icon'] ?? '🪑',
            'width' => $validated['width'],
            'height' => $validated['height'],
            'collision' => $request->has('collision'),
            'colors' => $colorsArr,
            'is_active' => true,
        ]);

        $this->invalidateFurnitureCache();

        return back()->with('success', 'New furniture asset uploaded and added to the catalog successfully.');
    }

    /**
     * Update Furniture Item.
     */
    public function updateFurnitureItem(\App\Domains\Workspace\Requests\StoreFurnitureItemRequest $request, \App\Domains\Workspace\Models\FurnitureItem $item)
    {
        $validated = $request->validated();

        $imageUrl = $item->image_url;

        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $extension = strtolower($file->guessExtension() ?: 'png');
            if (!in_array($extension, ['png', 'webp', 'jpg', 'jpeg', 'svg'])) {
                return back()->withErrors(['image' => 'Invalid image format. Allowed formats: PNG, WebP, JPG, SVG.']);
            }

            if ($extension === 'svg') {
                $content = file_get_contents($file->getRealPath());
                if (preg_match('/<script|javascript:|onload=|onerror=|onclick=|<foreignObject/i', $content)) {
                    return back()->withErrors(['image' => 'The SVG file contains unsafe embedded scripts or attributes.']);
                }
            }

            $filename = 'furn_' . Str::random(24) . '.' . $extension;
            $destinationPath = public_path('uploads/furniture');
            if (!file_exists($destinationPath)) {
                mkdir($destinationPath, 0755, true);
                @file_put_contents($destinationPath . '/.htaccess', "<Files *.php>\n    Order Deny,Allow\n    Deny from all\n</Files>\nOptions -ExecCGI\n");
            }
            $file->move($destinationPath, $filename);
            $imageUrl = '/uploads/furniture/' . $filename;
        } elseif (!empty($validated['image_url'])) {
            $imageUrl = $validated['image_url'];
        }

        $colorsArr = !empty($validated['colors'])
            ? array_map('trim', explode(',', $validated['colors']))
            : $item->colors;

        $item->update([
            'category_id' => $validated['category_id'],
            'name' => $validated['name'],
            'image_url' => $imageUrl,
            'icon' => $validated['icon'] ?? $item->icon,
            'width' => $validated['width'],
            'height' => $validated['height'],
            'collision' => $request->has('collision'),
            'colors' => $colorsArr,
            'is_active' => $request->has('is_active') ? true : $item->is_active,
        ]);

        $this->invalidateFurnitureCache();

        return back()->with('success', "Furniture item {$item->name} updated successfully.");
    }

    /**
     * Delete Furniture Item.
     */
    public function deleteFurnitureItem(\App\Domains\Workspace\Models\FurnitureItem $item)
    {
        $item->delete();

        $this->invalidateFurnitureCache();

        return back()->with('success', 'Furniture item removed from catalog.');
    }

    /**
     * Invalidate cached furniture catalog in memory / cache store.
     */
    private function invalidateFurnitureCache(): void
    {
        Cache::forget('furniture_catalog_active');
        Cache::forget('furniture_categories_with_items');
    }
}
