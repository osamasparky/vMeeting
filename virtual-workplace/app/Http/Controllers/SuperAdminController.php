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
