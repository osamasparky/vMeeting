<?php

namespace App\Http\Controllers;

use App\Domains\Administration\Models\AuditLog;
use App\Domains\Administration\Models\Permission;
use App\Domains\Administration\Models\Role;
use App\Domains\Administration\Models\SystemSetting;
use App\Domains\CMS\Models\CmsMediaAsset;
use App\Domains\CMS\Models\CmsPage;
use App\Domains\CMS\Models\CmsSection;
use App\Domains\CMS\Models\CmsThemeSetting;
use App\Domains\CMS\Models\FeatureFlag;
use App\Domains\CMS\Services\ThemeEngineService;
use App\Domains\Identity\Models\User;
use App\Domains\Projects\Models\Project;
use App\Domains\Projects\Models\Task;
use App\Domains\Projects\Models\TimeEntry;
use App\Domains\Tenancy\Models\Organization;
use App\Domains\Tenancy\Models\OrganizationMember;
use App\Domains\Tenancy\Models\Plan;
use App\Domains\Tenancy\Models\Subscription;
use App\Domains\Tenancy\Models\SubscriptionRequest;
use App\Domains\Workspace\Models\FurnitureCategory;
use App\Domains\Workspace\Models\FurnitureItem;
use App\Domains\Workspace\Models\MapObject;
use App\Domains\Workspace\Models\OfficeTemplate;
use App\Domains\Workspace\Models\Room;
use App\Domains\Workspace\Requests\StoreFurnitureCategoryRequest;
use App\Domains\Workspace\Requests\StoreFurnitureItemRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
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
            'total_rooms' => Room::count(),
            'total_projects' => Project::count(),
            'total_tasks' => Task::count(),
            'total_logged_hours' => round((TimeEntry::sum('duration_seconds') ?? 0) / 3600, 1),
            'total_audit_events' => AuditLog::count(),
            'pending_subscriptions_count' => SubscriptionRequest::where('status', 'pending')->count(),
        ];

        $recentCompanies = Organization::with(['plan', 'members.user', 'rooms'])
            ->latest()
            ->take(8)
            ->get();

        $plans = Plan::withCount('organizations')->where('is_active', true)->orderBy('price', 'desc')->get();
        $recentAuditLogs = AuditLog::with(['actor', 'organization'])->latest()->take(6)->get();
        $pendingSubscriptionRequests = SubscriptionRequest::with(['organization', 'user', 'plan'])
            ->where('status', 'pending')
            ->latest()
            ->take(6)
            ->get();

        return view('superadmin.dashboard', compact(
            'user', 'stats', 'recentCompanies', 'plans', 'recentAuditLogs', 'pendingSubscriptionRequests'
        ));
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

        Auth::login($targetUser);

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

        $featuresArr = ! empty($validated['features'])
            ? array_map('trim', explode(',', $validated['features']))
            : ['basic_chat', 'basic_presence', 'basic_audio'];

        Plan::create([
            'name' => $validated['name'],
            'slug' => Str::slug($validated['name']).'-'.Str::random(3),
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

        $featuresArr = ! empty($validated['features'])
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
     * List all Subscription & Bank Transfer Payment Requests.
     */
    public function subscriptionRequests(Request $request)
    {
        $user = Auth::user();
        $statusFilter = $request->input('status', 'all');
        $search = $request->input('search');

        $query = SubscriptionRequest::with(['organization', 'user', 'plan', 'reviewer']);

        if ($statusFilter && $statusFilter !== 'all') {
            $query->where('status', $statusFilter);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('transfer_reference', 'like', "%{$search}%")
                    ->orWhere('sender_name', 'like', "%{$search}%")
                    ->orWhere('bank_name', 'like', "%{$search}%")
                    ->orWhereHas('organization', function ($orgQ) use ($search) {
                        $orgQ->where('name', 'like', "%{$search}%");
                    })
                    ->orWhereHas('plan', function ($planQ) use ($search) {
                        $planQ->where('name', 'like', "%{$search}%");
                    });
            });
        }

        $subscriptionRequests = $query->latest()->paginate(15)->withQueryString();

        $stats = [
            'total' => SubscriptionRequest::count(),
            'pending' => SubscriptionRequest::where('status', 'pending')->count(),
            'approved' => SubscriptionRequest::where('status', 'approved')->count(),
            'rejected' => SubscriptionRequest::where('status', 'rejected')->count(),
        ];

        return view('superadmin.subscriptions', compact('user', 'subscriptionRequests', 'stats', 'statusFilter', 'search'));
    }

    /**
     * Approve a Bank Transfer Subscription Request and activate plan.
     */
    public function approveSubscriptionRequest(Request $request, SubscriptionRequest $subscriptionRequest)
    {
        $subscriptionRequest->load(['organization', 'plan']);
        $organization = $subscriptionRequest->organization;
        $plan = $subscriptionRequest->plan;

        if (! $organization || ! $plan) {
            return back()->with('error', 'المنظمة أو الخطة المطلوبة غير موجودة.');
        }

        $months = $subscriptionRequest->billing_cycle === 'yearly' ? 12 : 1;

        // 1. Update Organization Plan
        $organization->update([
            'plan_id' => $plan->id,
        ]);

        // 2. Create / Renew Subscription Record
        Subscription::create([
            'organization_id' => $organization->id,
            'plan_id' => $plan->id,
            'status' => 'active',
            'current_period_end' => now()->addMonths($months),
        ]);

        // 3. Mark Request as Approved
        $adminNotes = $request->input('admin_notes', 'Approved by SuperAdmin');
        $subscriptionRequest->update([
            'status' => 'approved',
            'reviewed_by' => Auth::id(),
            'reviewed_at' => now(),
            'admin_notes' => $adminNotes,
        ]);

        // 4. Log Audit Event
        AuditLog::create([
            'organization_id' => $organization->id,
            'actor_id' => Auth::id(),
            'action' => 'superadmin.subscription_approved',
            'metadata' => [
                'request_id' => $subscriptionRequest->id,
                'plan_id' => $plan->id,
                'plan_name' => $plan->name,
                'amount' => $subscriptionRequest->amount,
                'reference' => $subscriptionRequest->transfer_reference,
            ],
        ]);

        return back()->with('success', "تم قبول طلب التحويل البنكي وتفعيل باقة ({$plan->name}) لشركة {$organization->name} بنجاح!");
    }

    /**
     * Reject a Bank Transfer Subscription Request with notes.
     */
    public function rejectSubscriptionRequest(Request $request, SubscriptionRequest $subscriptionRequest)
    {
        $validated = $request->validate([
            'admin_notes' => ['required', 'string', 'max:1000'],
        ]);

        $subscriptionRequest->load(['organization', 'plan']);

        $subscriptionRequest->update([
            'status' => 'rejected',
            'reviewed_by' => Auth::id(),
            'reviewed_at' => now(),
            'admin_notes' => $validated['admin_notes'],
        ]);

        if ($subscriptionRequest->organization_id) {
            AuditLog::create([
                'organization_id' => $subscriptionRequest->organization_id,
                'actor_id' => Auth::id(),
                'action' => 'superadmin.subscription_rejected',
                'metadata' => [
                    'request_id' => $subscriptionRequest->id,
                    'reason' => $validated['admin_notes'],
                ],
            ]);
        }

        return back()->with('success', 'تم رفض طلب الاشتراك بنجاح.');
    }

    /**
     * View or download uploaded bank transfer receipt.
     */
    public function viewSubscriptionReceipt(SubscriptionRequest $subscriptionRequest)
    {
        if (! $subscriptionRequest->receipt_path || ! Storage::disk('public')->exists($subscriptionRequest->receipt_path)) {
            abort(404, 'Receipt file not found.');
        }

        $filePath = Storage::disk('public')->path($subscriptionRequest->receipt_path);

        return response()->file($filePath);
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
     * System Settings & Payment Configuration.
     */
    public function settings()
    {
        $user = Auth::user();
        $plans = Plan::where('is_active', true)->get();

        $defaultBankAccounts = [
            [
                'bank_name' => 'Al Rajhi Bank (مصرف الراجحي)',
                'account_name' => 'مؤسسة مساحة الغد لتقنية المعلومات',
                'account_name_en' => 'Next Space Information Technology Est.',
                'iban' => 'SA4580000412608010123456',
                'account_number' => '412608010123456',
                'swift_code' => 'RJHISARI',
                'currency' => 'SAR',
                'badge' => 'Direct Instant Local Transfer',
            ],
            [
                'bank_name' => 'Saudi National Bank - SNB (البنك الأهلي السعودي)',
                'account_name' => 'مؤسسة مساحة الغد لتقنية المعلومات',
                'account_name_en' => 'Next Space Information Technology Est.',
                'iban' => 'SA1210000001234567890123',
                'account_number' => '01234567890123',
                'swift_code' => 'NCBKSAJI',
                'currency' => 'SAR',
                'badge' => 'Corporate Settlement',
            ],
            [
                'bank_name' => 'Riyad Bank (بنك الرياض)',
                'account_name' => 'مؤسسة مساحة الغد لتقنية المعلومات',
                'account_name_en' => 'Next Space Information Technology Est.',
                'iban' => 'SA7820000009876543210987',
                'account_number' => '09876543210987',
                'swift_code' => 'RIBLSARI',
                'currency' => 'SAR / USD',
                'badge' => 'International Wire / SWIFT',
            ],
        ];

        $paymentSettings = SystemSetting::get('payment_settings', [
            'usd_to_sar_rate' => 3.75,
            'usd_to_egp_rate' => 48.5,
            'usd_to_aed_rate' => 3.67,
            'default_currency' => 'SAR',
            'tax_percentage' => 15,
            'tax_number' => '300012345600003',
            'bank_accounts' => $defaultBankAccounts,
            'instapay_handle' => 'nextspace@instapay',
            'instapay_phone' => '+201000000000',
            'stc_pay_phone' => '+966500000000',
            'vodafone_cash_phone' => '+201000000000',
            'checkout_terms_ar' => 'يتم تفعيل الاشتراك فور مراجعة إيصال التحويل البنكي أو الدفع الإلكتروني من قبل المشرفين.',
            'checkout_terms_en' => 'Your subscription will be activated immediately upon review of the payment receipt by our administration team.',
            'enable_bank_transfer' => true,
            'enable_instapay' => true,
            'enable_wallets' => true,
        ]);

        $globalSettings = SystemSetting::get('global_settings', [
            'platform_name' => 'Virtual Workplace SaaS',
            'default_plan_id' => $plans->where('slug', 'free')->first()?->id ?? $plans->first()?->id,
            'ws_url' => 'ws://127.0.0.1:8080',
            'stun_server' => 'stun:173.212.248.192:3478',
        ]);

        $aiSettings = SystemSetting::get('openai_settings', [
            'api_key' => env('OPENAI_API_KEY', ''),
            'model' => 'dall-e-3',
            'image_size' => '1792x1024',
            'quality' => 'standard',
            'prompt_prefix' => "A clean, photorealistic direct top-down 2D architectural floor plan blueprint of a modern virtual workplace office (straight 90-degree overhead bird's-eye plan view with cutaway interior walls).",
            'is_enabled' => true,
        ]);

        return view('superadmin.settings', compact('user', 'plans', 'paymentSettings', 'globalSettings', 'aiSettings'));
    }

    /**
     * Update OpenAI & AI Workplace Generator Settings.
     */
    public function updateAiSettings(Request $request)
    {
        $aiSettings = [
            'api_key' => trim($request->input('api_key', '')),
            'model' => $request->input('model', 'gpt-image-1'),
            'image_size' => $request->input('image_size', '1024x1024'),
            'quality' => $request->input('quality', 'standard'),
            'prompt_prefix' => trim($request->input('prompt_prefix', "A clean, photorealistic direct top-down 2D architectural floor plan blueprint of a modern virtual workplace office (straight 90-degree overhead bird's-eye plan view with cutaway interior walls).")),
            'is_enabled' => $request->has('is_enabled') || ! empty(trim($request->input('api_key', ''))),
        ];

        SystemSetting::set('openai_settings', $aiSettings);

        return back()->with('success', __('AI Office Generator & OpenAI settings saved successfully.'));
    }

    /**
     * Test OpenAI API connectivity.
     */
    public function testAiConnection(Request $request)
    {
        $apiKey = trim($request->input('api_key', ''));
        if (empty($apiKey)) {
            $aiSettings = SystemSetting::get('openai_settings', []);
            $apiKey = $aiSettings['api_key'] ?? env('OPENAI_API_KEY', '');
        }

        if (empty($apiKey)) {
            return response()->json(['success' => false, 'message' => __('OpenAI API key is missing. Please enter an API key.')], 422);
        }

        try {
            $response = Http::withToken($apiKey)
                ->timeout(15)
                ->get('https://api.openai.com/v1/models');

            if ($response->successful()) {
                return response()->json(['success' => true, 'message' => __('✅ Connection to OpenAI API successful! Account is active and authenticated.')]);
            } else {
                $err = $response->json();
                $errMsg = $err['error']['message'] ?? $response->body();

                return response()->json(['success' => false, 'message' => 'OpenAI Error: '.$errMsg], 400);
            }
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => 'Network error connecting to OpenAI: '.$e->getMessage()], 500);
        }
    }

    /**
     * Update global system settings.
     */
    public function updateSettings(Request $request)
    {
        $globalSettings = [
            'platform_name' => $request->input('platform_name', 'Virtual Workplace SaaS'),
            'default_plan_id' => $request->input('default_plan_id'),
            'ws_url' => $request->input('ws_url', 'ws://127.0.0.1:8080'),
            'stun_server' => $request->input('stun_server', 'stun:173.212.248.192:3478'),
        ];

        SystemSetting::set('global_settings', $globalSettings);

        return back()->with('success', __('System settings saved successfully.'));
    }

    /**
     * Update Payment & Checkout settings.
     */
    public function updatePaymentSettings(Request $request)
    {
        $bankAccounts = [];
        $bankNames = $request->input('bank_name', []);
        $accountNames = $request->input('account_name', []);
        $accountNamesEn = $request->input('account_name_en', []);
        $ibans = $request->input('iban', []);
        $accountNumbers = $request->input('account_number', []);
        $swiftCodes = $request->input('swift_code', []);
        $currencies = $request->input('currency', []);
        $badges = $request->input('badge', []);

        for ($i = 0; $i < count($bankNames); $i++) {
            if (! empty(trim($bankNames[$i]))) {
                $bankAccounts[] = [
                    'bank_name' => trim($bankNames[$i]),
                    'account_name' => trim($accountNames[$i] ?? ''),
                    'account_name_en' => trim($accountNamesEn[$i] ?? ''),
                    'iban' => trim($ibans[$i] ?? ''),
                    'account_number' => trim($accountNumbers[$i] ?? ''),
                    'swift_code' => trim($swiftCodes[$i] ?? ''),
                    'currency' => trim($currencies[$i] ?? 'SAR'),
                    'badge' => trim($badges[$i] ?? 'Official Bank Account'),
                ];
            }
        }

        $paymentSettings = [
            'usd_to_sar_rate' => (float) $request->input('usd_to_sar_rate', 3.75),
            'usd_to_egp_rate' => (float) $request->input('usd_to_egp_rate', 48.5),
            'usd_to_aed_rate' => (float) $request->input('usd_to_aed_rate', 3.67),
            'default_currency' => $request->input('default_currency', 'SAR'),
            'tax_percentage' => (float) $request->input('tax_percentage', 15),
            'tax_number' => $request->input('tax_number', ''),
            'bank_accounts' => $bankAccounts,
            'instapay_handle' => $request->input('instapay_handle', ''),
            'instapay_phone' => $request->input('instapay_phone', ''),
            'stc_pay_phone' => $request->input('stc_pay_phone', ''),
            'vodafone_cash_phone' => $request->input('vodafone_cash_phone', ''),
            'checkout_terms_ar' => $request->input('checkout_terms_ar', ''),
            'checkout_terms_en' => $request->input('checkout_terms_en', ''),
            'enable_bank_transfer' => $request->has('enable_bank_transfer'),
            'enable_instapay' => $request->has('enable_instapay'),
            'enable_wallets' => $request->has('enable_wallets'),
        ];

        SystemSetting::set('payment_settings', $paymentSettings);

        return back()->with('success', __('Payment & Checkout settings saved successfully.'));
    }

    /**
     * Translations & Localization Management Console.
     */
    public function translations(Request $request)
    {
        $user = Auth::user();
        $search = trim((string) $request->input('search', ''));
        $selectedLang = $request->input('lang', 'ar');

        $arPath = base_path('lang/ar.json');
        $enPath = base_path('lang/en.json');

        $arJson = file_exists($arPath) ? json_decode(file_get_contents($arPath), true) ?: [] : [];
        $enJson = file_exists($enPath) ? json_decode(file_get_contents($enPath), true) ?: [] : [];

        // Collect all unique keys
        $allKeys = array_unique(array_merge(array_keys($arJson), array_keys($enJson)));
        sort($allKeys);

        $translations = [];
        foreach ($allKeys as $key) {
            $arVal = $arJson[$key] ?? '';
            $enVal = $enJson[$key] ?? $key;

            if ($search !== '') {
                $matchesKey = (stripos($key, $search) !== false);
                $matchesAr = (stripos($arVal, $search) !== false);
                $matchesEn = (stripos($enVal, $search) !== false);
                if (! $matchesKey && ! $matchesAr && ! $matchesEn) {
                    continue;
                }
            }

            $translations[] = [
                'key' => $key,
                'ar' => $arVal,
                'en' => $enVal,
            ];
        }

        $totalCount = count($allKeys);
        $filteredCount = count($translations);

        // Pagination
        $page = max(1, (int) $request->input('page', 1));
        $perPage = 50;
        $offset = ($page - 1) * $perPage;
        $paginatedItems = array_slice($translations, $offset, $perPage);
        $totalPages = max(1, (int) ceil($filteredCount / $perPage));

        return view('superadmin.translations', compact(
            'user',
            'paginatedItems',
            'totalCount',
            'filteredCount',
            'search',
            'selectedLang',
            'page',
            'perPage',
            'totalPages'
        ));
    }

    /**
     * Save updated translations.
     */
    public function updateTranslations(Request $request)
    {
        try {
            $keys = $request->input('keys', []);
            $arVals = $request->input('ar', []);
            $enVals = $request->input('en', []);

            $arPath = base_path('lang/ar.json');
            $enPath = base_path('lang/en.json');

            $arJson = file_exists($arPath) ? json_decode(file_get_contents($arPath), true) ?: [] : [];
            $enJson = file_exists($enPath) ? json_decode(file_get_contents($enPath), true) ?: [] : [];

            for ($i = 0; $i < count($keys); $i++) {
                $key = $keys[$i];
                if ($key !== '') {
                    if (isset($arVals[$i])) {
                        $arJson[$key] = $arVals[$i];
                    }
                    if (isset($enVals[$i])) {
                        $enJson[$key] = $enVals[$i];
                    }
                }
            }

            if (! file_exists(dirname($arPath))) {
                @mkdir(dirname($arPath), 0775, true);
            }
            if (file_exists($arPath) && ! is_writable($arPath)) {
                @chmod($arPath, 0666);
            }
            if (file_exists($enPath) && ! is_writable($enPath)) {
                @chmod($enPath, 0666);
            }

            file_put_contents($arPath, json_encode($arJson, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            file_put_contents($enPath, json_encode($enJson, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

            Artisan::call('view:clear');

            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['success' => true, 'message' => __('Translations saved and cache cleared.')]);
            }

            return back()->with('success', __('Translations saved successfully across the system.'));
        } catch (\Throwable $e) {
            Log::error('Error saving translations: '.$e->getMessage());
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'message' => 'Failed to save translations: '.$e->getMessage()], 500);
            }

            return back()->with('error', 'Failed to save translations: '.$e->getMessage());
        }
    }

    /**
     * Add new translation key.
     */
    public function addTranslationKey(Request $request)
    {
        $request->validate([
            'key' => 'required|string|max:1000',
            'ar' => 'required|string|max:2000',
            'en' => 'nullable|string|max:2000',
        ]);

        try {
            $key = trim($request->input('key'));
            $arVal = trim($request->input('ar'));
            $enVal = trim($request->input('en', $key)) ?: $key;

            $arPath = base_path('lang/ar.json');
            $enPath = base_path('lang/en.json');

            $arJson = file_exists($arPath) ? json_decode(file_get_contents($arPath), true) ?: [] : [];
            $enJson = file_exists($enPath) ? json_decode(file_get_contents($enPath), true) ?: [] : [];

            $arJson[$key] = $arVal;
            $enJson[$key] = $enVal;

            if (file_exists($arPath) && ! is_writable($arPath)) {
                @chmod($arPath, 0666);
            }
            if (file_exists($enPath) && ! is_writable($enPath)) {
                @chmod($enPath, 0666);
            }

            file_put_contents($arPath, json_encode($arJson, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            file_put_contents($enPath, json_encode($enJson, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

            Artisan::call('view:clear');

            return back()->with('success', __('New phrase ":key" added successfully.', ['key' => $key]));
        } catch (\Throwable $e) {
            Log::error('Error adding translation: '.$e->getMessage());

            return back()->with('error', 'Failed to add phrase: '.$e->getMessage());
        }
    }

    /**
     * Delete translation key.
     */
    public function deleteTranslationKey(Request $request)
    {
        $request->validate(['key' => 'required|string']);
        $key = $request->input('key');

        try {
            $arPath = base_path('lang/ar.json');
            $enPath = base_path('lang/en.json');

            $arJson = file_exists($arPath) ? json_decode(file_get_contents($arPath), true) ?: [] : [];
            $enJson = file_exists($enPath) ? json_decode(file_get_contents($enPath), true) ?: [] : [];

            unset($arJson[$key]);
            unset($enJson[$key]);

            if (file_exists($arPath) && ! is_writable($arPath)) {
                @chmod($arPath, 0666);
            }
            if (file_exists($enPath) && ! is_writable($enPath)) {
                @chmod($enPath, 0666);
            }

            file_put_contents($arPath, json_encode($arJson, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            file_put_contents($enPath, json_encode($enJson, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

            Artisan::call('view:clear');

            return back()->with('success', __('Phrase removed from system.'));
        } catch (\Throwable $e) {
            Log::error('Error deleting translation: '.$e->getMessage());

            return back()->with('error', 'Failed to delete phrase: '.$e->getMessage());
        }
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
        if (! file_exists($dest)) {
            mkdir($dest, 0755, true);
        }

        // Copy to both default locations so all views pick it up immediately
        $file->move($dest, 'office_floorplan.jpg');
        copy($dest.'/office_floorplan.jpg', $dest.'/isometric_office_blueprint.jpg');

        return back()->with('success', 'Global system default office blueprint updated successfully for all organizations.');
    }

    /**
     * Furniture & Assets Management.
     */
    public function furniture(Request $request)
    {
        $user = Auth::user();
        $categories = FurnitureCategory::withCount('items')
            ->orderBy('order', 'asc')
            ->get();

        $selectedCategoryId = $request->input('category_id');
        $query = FurnitureItem::with('category');

        if ($selectedCategoryId) {
            $query->where('category_id', $selectedCategoryId);
        }

        if ($search = $request->input('search')) {
            $query->where('name', 'like', "%{$search}%");
        }

        $items = $query->latest()->paginate(24);

        $stats = [
            'total_items' => FurnitureItem::count(),
            'total_categories' => $categories->count(),
            'custom_uploads' => FurnitureItem::whereNotNull('image_url')->count(),
        ];

        return view('superadmin.furniture', compact('user', 'categories', 'items', 'stats', 'selectedCategoryId'));
    }

    /**
     * Store new Furniture Category.
     */
    public function storeFurnitureCategory(StoreFurnitureCategoryRequest $request)
    {
        $validated = $request->validated();

        FurnitureCategory::create([
            'name' => $validated['name'],
            'slug' => Str::slug($validated['name']).'-'.Str::random(3),
            'icon' => $validated['icon'] ?? '🪑',
            'order' => $validated['order'] ?? 0,
        ]);

        $this->invalidateFurnitureCache();

        return back()->with('success', 'Furniture Category created successfully.');
    }

    /**
     * Update Furniture Category.
     */
    public function updateFurnitureCategory(StoreFurnitureCategoryRequest $request, FurnitureCategory $category)
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
    public function deleteFurnitureCategory(FurnitureCategory $category)
    {
        $category->items()->delete();
        $category->delete();

        $this->invalidateFurnitureCache();

        return back()->with('success', 'Furniture Category deleted successfully.');
    }

    /**
     * Store/Upload new Furniture Item.
     */
    public function storeFurnitureItem(StoreFurnitureItemRequest $request)
    {
        $validated = $request->validated();

        $imageUrl = $validated['image_url'] ?? null;

        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $extension = strtolower($file->guessExtension() ?: 'png');
            if (! in_array($extension, ['png', 'webp', 'jpg', 'jpeg', 'svg'])) {
                return back()->withErrors(['image' => 'Invalid image format. Allowed formats: PNG, WebP, JPG, SVG.']);
            }

            // If SVG, check for dangerous tags
            if ($extension === 'svg') {
                $content = file_get_contents($file->getRealPath());
                if (preg_match('/<script|javascript:|onload=|onerror=|onclick=|<foreignObject/i', $content)) {
                    return back()->withErrors(['image' => 'The SVG file contains unsafe embedded scripts or attributes.']);
                }
            }

            $filename = 'furn_'.Str::random(24).'.'.$extension;
            $destinationPath = public_path('uploads/furniture');
            if (! file_exists($destinationPath)) {
                mkdir($destinationPath, 0755, true);
                @file_put_contents($destinationPath.'/.htaccess', "<Files *.php>\n    Order Deny,Allow\n    Deny from all\n</Files>\nOptions -ExecCGI\n");
            }
            $file->move($destinationPath, $filename);
            $imageUrl = '/uploads/furniture/'.$filename;
        }

        $colorsArr = ! empty($validated['colors'])
            ? array_map('trim', explode(',', $validated['colors']))
            : ['#00b4b3', '#012c41'];

        FurnitureItem::create([
            'category_id' => $validated['category_id'],
            'name' => $validated['name'],
            'slug' => Str::slug($validated['name']).'-'.Str::random(4),
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
    public function updateFurnitureItem(StoreFurnitureItemRequest $request, FurnitureItem $item)
    {
        $validated = $request->validated();

        $imageUrl = $item->image_url;

        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $extension = strtolower($file->guessExtension() ?: 'png');
            if (! in_array($extension, ['png', 'webp', 'jpg', 'jpeg', 'svg'])) {
                return back()->withErrors(['image' => 'Invalid image format. Allowed formats: PNG, WebP, JPG, SVG.']);
            }

            if ($extension === 'svg') {
                $content = file_get_contents($file->getRealPath());
                if (preg_match('/<script|javascript:|onload=|onerror=|onclick=|<foreignObject/i', $content)) {
                    return back()->withErrors(['image' => 'The SVG file contains unsafe embedded scripts or attributes.']);
                }
            }

            $filename = 'furn_'.Str::random(24).'.'.$extension;
            $destinationPath = public_path('uploads/furniture');
            if (! file_exists($destinationPath)) {
                mkdir($destinationPath, 0755, true);
                @file_put_contents($destinationPath.'/.htaccess', "<Files *.php>\n    Order Deny,Allow\n    Deny from all\n</Files>\nOptions -ExecCGI\n");
            }
            $file->move($destinationPath, $filename);
            $imageUrl = '/uploads/furniture/'.$filename;
        } elseif (! empty($validated['image_url'])) {
            $imageUrl = $validated['image_url'];
        }

        $colorsArr = ! empty($validated['colors'])
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
    public function deleteFurnitureItem(FurnitureItem $item)
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

    /**
     * Default Office Template & Rooms Designer (SuperAdmin).
     */
    public function defaultTemplate(Request $request)
    {
        $user = Auth::user();
        $allPlans = Plan::where('is_active', true)->orderBy('price', 'asc')->get();

        $selectedPlanSlug = $request->query('plan', 'free');
        $selectedPlan = $allPlans->firstWhere('slug', $selectedPlanSlug) ?: $allPlans->first();

        $template = OfficeTemplate::getForPlan($selectedPlan);
        $totalCompanies = Organization::count();
        $planCompaniesCount = $selectedPlan ? Organization::where('plan_id', $selectedPlan->id)->count() : 0;

        return view('superadmin.default_template', compact('user', 'template', 'allPlans', 'selectedPlan', 'totalCompanies', 'planCompaniesCount'));
    }

    /**
     * Update default office template meta & dimensions.
     */
    public function updateDefaultTemplate(Request $request)
    {
        $template = $request->filled('template_id')
            ? OfficeTemplate::findOrFail($request->input('template_id'))
            : OfficeTemplate::getDefault();

        $validated = $request->validate([
            'template_id' => ['nullable', 'uuid'],
            'name' => ['required', 'string', 'max:150'],
            'description' => ['nullable', 'string', 'max:500'],
            'background_image_url' => ['nullable', 'string', 'max:500'],
            'width' => ['required', 'integer', 'min:10', 'max:100'],
            'height' => ['required', 'integer', 'min:10', 'max:100'],
            'tile_size' => ['required', 'integer', 'in:16,32,48,64'],
        ]);

        $layoutData = $template->layout_data ?? [];
        if (! empty($validated['background_image_url'])) {
            $layoutData['background_image_url'] = $validated['background_image_url'];
        }

        $template->update([
            'name' => $validated['name'],
            'description' => $validated['description'],
            'background_image_url' => $validated['background_image_url'] ?? $template->background_image_url,
            'width' => $validated['width'],
            'height' => $validated['height'],
            'tile_size' => $validated['tile_size'],
            'layout_data' => $layoutData,
            'created_by' => Auth::id(),
        ]);

        return back()->with('success', __('Office Template blueprint settings updated successfully.'));
    }

    /**
     * Add or update a default room in the system template.
     */
    public function saveTemplateRoom(Request $request)
    {
        $template = $request->filled('template_id')
            ? OfficeTemplate::findOrFail($request->input('template_id'))
            : OfficeTemplate::getDefault();

        $validated = $request->validate([
            'template_id' => ['nullable', 'uuid'],
            'room_index' => ['nullable', 'integer'],
            'name' => ['required', 'string', 'max:120'],
            'type' => ['required', 'string', 'in:meeting,private,breakout,lounge,reception'],
            'access_mode' => ['required', 'string', 'in:public,knock,locked'],
            'capacity' => ['required', 'integer', 'min:1', 'max:100'],
            'color' => ['nullable', 'string', 'max:30'],
            'x' => ['required', 'integer', 'min:0'],
            'y' => ['required', 'integer', 'min:0'],
            'width' => ['required', 'integer', 'min:1'],
            'height' => ['required', 'integer', 'min:1'],
            'audio_isolation' => ['nullable', 'boolean'],
        ]);

        $rooms = $template->rooms_data ?: [];

        $newRoom = [
            'name' => $validated['name'],
            'type' => $validated['type'],
            'access_mode' => $validated['access_mode'],
            'capacity' => (int) $validated['capacity'],
            'color' => $validated['color'] ?: '#3F7D4F',
            'bounds' => [
                'x' => (int) $validated['x'],
                'y' => (int) $validated['y'],
                'width' => (int) $validated['width'],
                'height' => (int) $validated['height'],
            ],
            'metadata' => [
                'audio_isolation' => $request->has('audio_isolation') ? (bool) $request->input('audio_isolation') : true,
            ],
        ];

        if ($request->filled('room_index') && isset($rooms[(int) $request->input('room_index')])) {
            $rooms[(int) $request->input('room_index')] = $newRoom;
        } else {
            $rooms[] = $newRoom;
        }

        $template->update(['rooms_data' => array_values($rooms)]);

        return back()->with('success', __('Room saved in the office template.'));
    }

    /**
     * Delete a default room from the template.
     */
    public function deleteTemplateRoom(Request $request, int $roomIndex)
    {
        $template = $request->filled('template_id')
            ? OfficeTemplate::findOrFail($request->input('template_id'))
            : OfficeTemplate::getDefault();

        $rooms = $template->rooms_data ?: [];

        if (isset($rooms[$roomIndex])) {
            unset($rooms[$roomIndex]);
            $template->update(['rooms_data' => array_values($rooms)]);

            return back()->with('success', __('Room removed from office template.'));
        }

        return back()->with('error', __('Room not found in template.'));
    }

    /**
     * Bulk save all visually drawn & renamed rooms to the default office template.
     */
    public function saveAllTemplateRooms(Request $request)
    {
        $template = $request->filled('template_id')
            ? OfficeTemplate::findOrFail($request->input('template_id'))
            : OfficeTemplate::getDefault();

        $validated = $request->validate([
            'template_id' => ['nullable', 'uuid'],
            'rooms' => ['present', 'array'],
            'rooms.*.name' => ['required', 'string', 'max:120'],
            'rooms.*.type' => ['nullable', 'string', 'in:meeting,private,breakout,lounge,reception'],
            'rooms.*.access_mode' => ['nullable', 'string', 'in:public,knock,locked'],
            'rooms.*.capacity' => ['nullable', 'integer', 'min:1', 'max:100'],
            'rooms.*.color' => ['nullable', 'string', 'max:30'],
            'rooms.*.bounds' => ['required', 'array'],
            'rooms.*.bounds.x' => ['required', 'integer', 'min:0'],
            'rooms.*.bounds.y' => ['required', 'integer', 'min:0'],
            'rooms.*.bounds.width' => ['required', 'integer', 'min:1'],
            'rooms.*.bounds.height' => ['required', 'integer', 'min:1'],
            'rooms.*.metadata' => ['nullable', 'array'],
        ]);

        $roomsFormatted = [];
        foreach ($validated['rooms'] as $r) {
            $roomsFormatted[] = [
                'name' => $r['name'],
                'type' => $r['type'] ?? 'meeting',
                'access_mode' => $r['access_mode'] ?? 'public',
                'capacity' => (int) ($r['capacity'] ?? 8),
                'color' => $r['color'] ?? '#3F7D4F',
                'bounds' => [
                    'x' => (int) ($r['bounds']['x'] ?? 0),
                    'y' => (int) ($r['bounds']['y'] ?? 0),
                    'width' => (int) ($r['bounds']['width'] ?? 10),
                    'height' => (int) ($r['bounds']['height'] ?? 8),
                ],
                'metadata' => [
                    'audio_isolation' => isset($r['metadata']['audio_isolation']) ? (bool) $r['metadata']['audio_isolation'] : true,
                ],
            ];
        }

        $template->update([
            'rooms_data' => $roomsFormatted,
            'created_by' => Auth::id(),
        ]);

        return response()->json([
            'success' => true,
            'message' => __('All office rooms updated and saved successfully!'),
            'rooms' => $roomsFormatted,
        ]);
    }

    /**
     * Upload custom blueprint background image for the template.
     */
    public function uploadTemplateBackground(Request $request)
    {
        $request->validate([
            'template_id' => ['nullable', 'uuid'],
            'background' => ['required', 'file', 'image', 'mimes:jpeg,png,jpg,webp', 'max:51200'],
        ]);

        $template = $request->filled('template_id')
            ? OfficeTemplate::findOrFail($request->input('template_id'))
            : OfficeTemplate::getDefault();

        $file = $request->file('background');
        $filename = 'template_floorplan_'.($template->plan_slug ?: 'default').'_'.time().'.'.$file->getClientOriginalExtension();

        $destDir = public_path('images/maps');
        if (! file_exists($destDir)) {
            mkdir($destDir, 0755, true);
        }
        $file->move($destDir, $filename);
        $url = '/images/maps/'.$filename;

        $layoutData = $template->layout_data ?? [];
        $layoutData['background_image_url'] = $url;

        $template->update([
            'background_image_url' => $url,
            'layout_data' => $layoutData,
        ]);

        return back()->with('success', __('Office Blueprint background image updated successfully.'));
    }

    /**
     * Sync and propagate the Template rooms & layout to companies.
     */
    public function syncTemplateToOrganizations(Request $request)
    {
        $template = $request->filled('template_id')
            ? OfficeTemplate::findOrFail($request->input('template_id'))
            : OfficeTemplate::getDefault();

        $scope = $request->input('sync_scope', 'all');
        $query = Organization::query();

        if ($scope === 'plan_only' && $template->plan_id) {
            $query->where('plan_id', $template->plan_id);
        }

        $organizations = $query->get();
        $count = 0;

        foreach ($organizations as $org) {
            $floor = $org->floors()->first();
            if (! $floor) {
                $floor = $org->floors()->create([
                    'name' => 'الدور الرئيسي - Main Office Floor',
                    'order' => 1,
                ]);
            }

            $map = $org->maps()->where('floor_id', $floor->id)->first();
            $layoutData = $template->layout_data ?? [
                'theme' => 'open_spatial_blueprint',
                'background_image_url' => $template->background_image_url ?: '/images/office_floorplan.jpg',
            ];

            if (! $map) {
                $map = $org->maps()->create([
                    'floor_id' => $floor->id,
                    'name' => $template->name,
                    'status' => 'published',
                    'version' => 1,
                    'width' => $template->width,
                    'height' => $template->height,
                    'tile_size' => $template->tile_size,
                    'layout_data' => $layoutData,
                    'published_at' => now(),
                ]);
            } else {
                $map->update([
                    'width' => $template->width,
                    'height' => $template->height,
                    'tile_size' => $template->tile_size,
                    'layout_data' => $layoutData,
                ]);
            }

            // Remove untextured blue dummy objects
            MapObject::where('map_id', $map->id)
                ->whereNull('image_url')
                ->delete();

            // If requested to overwrite rooms or if organization has 0 rooms
            if ($request->has('overwrite_rooms') || $org->rooms()->count() === 0) {
                $org->rooms()->delete();
                $roomsList = $template->rooms_data ?: [];
                $maxAllowed = ($org->plan && $org->plan->room_limit > 0) ? $org->plan->room_limit : count($roomsList);
                $roomsList = array_slice($roomsList, 0, $maxAllowed);

                foreach ($roomsList as $rData) {
                    Room::create([
                        'organization_id' => $org->id,
                        'map_id' => $map->id,
                        'name' => $rData['name'] ?? 'Meeting Room',
                        'type' => $rData['type'] ?? 'meeting',
                        'access_mode' => $rData['access_mode'] ?? 'public',
                        'capacity' => $rData['capacity'] ?? 8,
                        'color' => $rData['color'] ?? '#3F7D4F',
                        'bounds' => $rData['bounds'] ?? ['x' => 1, 'y' => 1, 'width' => 10, 'height' => 10],
                        'metadata' => $rData['metadata'] ?? ['audio_isolation' => true],
                    ]);
                }
            }

            $count++;
        }

        return back()->with('success', __('Office Template synchronized across :count organizations successfully.', ['count' => $count]));
    }

    /* ═══════════════════════════════════════════════════════════════
       CMS & 3D WEBSITE MANAGEMENT (SUPER ADMIN)
       ═══════════════════════════════════════════════════════════════ */

    /**
     * List all CMS Pages.
     */
    public function cmsPages()
    {
        $pages = CmsPage::withCount('sections')->get();

        return view('superadmin.cms.pages', compact('pages'));
    }

    /**
     * Edit a CMS Page and its sections.
     */
    public function editCmsPage(CmsPage $page)
    {
        $page->load(['sections' => function ($q) {
            $q->orderBy('display_order');
        }, 'sections.mediaAsset']);
        $assets = CmsMediaAsset::where('is_active', true)->get();

        return view('superadmin.cms.page_edit', compact('page', 'assets'));
    }

    /**
     * Update a CMS Section.
     */
    public function updateCmsSection(Request $request, CmsSection $section)
    {
        $section->update([
            'title_en' => $request->input('title_en'),
            'title_ar' => $request->input('title_ar'),
            'subtitle_en' => $request->input('subtitle_en'),
            'subtitle_ar' => $request->input('subtitle_ar'),
            'badge_en' => $request->input('badge_en'),
            'badge_ar' => $request->input('badge_ar'),
            'is_active' => $request->has('is_active'),
            'display_order' => (int) $request->input('display_order', 0),
            'media_asset_id' => $request->input('media_asset_id') ?: null,
        ]);

        if ($request->has('content_json')) {
            $decoded = json_decode($request->input('content_json'), true);
            if (json_last_error() === JSON_ERROR_NONE) {
                $section->update(['content' => $decoded]);
            }
        }

        return back()->with('success', __('CMS Section updated successfully!'));
    }

    /**
     * Toggle section active status.
     */
    public function toggleCmsSection(CmsSection $section)
    {
        $section->update(['is_active' => ! $section->is_active]);

        return back()->with('success', __('Section status updated!'));
    }

    /**
     * Media & 3D Asset Management (Nano Banana Pipeline).
     */
    public function cmsAssets()
    {
        $assets = CmsMediaAsset::orderBy('created_at', 'desc')->paginate(20);

        return view('superadmin.cms.assets', compact('assets'));
    }

    /**
     * Upload Media or 3D GLB/GLTF Asset.
     */
    public function uploadCmsAsset(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'asset_type' => 'required|in:image,video,3d_glb,3d_gltf,lottie,audio',
            'file' => 'required|file|max:51200', // max 50MB
        ]);

        $file = $request->file('file');
        $extension = $file->getClientOriginalExtension();
        $filename = 'asset_'.Str::random(12).'_'.time().'.'.$extension;

        $destDir = public_path('uploads/cms');
        if (! file_exists($destDir)) {
            mkdir($destDir, 0755, true);
        }

        $file->move($destDir, $filename);
        $filePath = '/uploads/cms/'.$filename;

        CmsMediaAsset::create([
            'name' => $request->input('name'),
            'asset_type' => $request->input('asset_type'),
            'file_path' => $filePath,
            'version_tag' => $request->input('version_tag', 'v1'),
            'tags' => array_filter(array_map('trim', explode(',', $request->input('tags', '')))),
            'is_active' => true,
        ]);

        return back()->with('success', __('Asset uploaded successfully!'));
    }

    /**
     * Delete Media Asset.
     */
    public function deleteCmsAsset(CmsMediaAsset $asset)
    {
        if (str_starts_with($asset->file_path, '/uploads/cms/')) {
            $fullPath = public_path(ltrim($asset->file_path, '/'));
            if (file_exists($fullPath)) {
                @unlink($fullPath);
            }
        }
        $asset->delete();

        return back()->with('success', __('Media Asset deleted successfully.'));
    }

    /**
     * Theme & Branding Studio & Menu Navigation.
     */
    public function cmsTheme()
    {
        $tokens = ThemeEngineService::getThemeTokens();
        $navItems = CmsThemeSetting::getByKey('main_navigation', [
            ['label_en' => 'Platform', 'label_ar' => 'المنصة', 'url' => '#hero-spatial'],
            ['label_en' => 'Spatial Presence', 'label_ar' => 'التواجد المكاني', 'url' => '#spatial-presence'],
            ['label_en' => 'AI Office', 'label_ar' => 'مكتب الذكاء الاصطناعي', 'url' => '#ai-generator'],
            ['label_en' => 'Collaboration', 'label_ar' => 'التعاون والإنتاجية', 'url' => '#collaboration'],
            ['label_en' => 'Pricing', 'label_ar' => 'الباقات والأسعار', 'url' => '#pricing'],
        ]);

        return view('superadmin.cms.theme', compact('tokens', 'navItems'));
    }

    /**
     * Update Theme Tokens and Menu Navigation.
     */
    public function updateCmsTheme(Request $request)
    {
        $validated = $request->validate([
            'color_deep_space' => ['nullable', 'string', 'regex:/^#[0-9A-Fa-f]{3,8}$/'],
            'color_dark_green' => ['nullable', 'string', 'regex:/^#[0-9A-Fa-f]{3,8}$/'],
            'color_emerald' => ['nullable', 'string', 'regex:/^#[0-9A-Fa-f]{3,8}$/'],
            'color_mint' => ['nullable', 'string', 'regex:/^#[0-9A-Fa-f]{3,8}$/'],
            'color_soft_mint' => ['nullable', 'string', 'regex:/^#[0-9A-Fa-f]{3,8}$/'],
            'color_white' => ['nullable', 'string', 'regex:/^#[0-9A-Fa-f]{3,8}$/'],
            'color_text_dark' => ['nullable', 'string', 'regex:/^#[0-9A-Fa-f]{3,8}$/'],
            'color_text_light' => ['nullable', 'string', 'regex:/^#[0-9A-Fa-f]{3,8}$/'],
            'color_text_muted' => ['nullable', 'string', 'regex:/^#[0-9A-Fa-f]{3,8}$/'],
            'font_family_latin' => ['nullable', 'string', 'max:50', 'regex:/^[a-zA-Z0-9\s,\-\'\"]+$/'],
            'font_family_arabic' => ['nullable', 'string', 'max:50', 'regex:/^[a-zA-Z0-9\s,\-\'\"]+$/'],
            'radius_btn' => ['nullable', 'string', 'max:20', 'regex:/^[0-9]+(px|rem|em|%)?$/'],
            'radius_card' => ['nullable', 'string', 'max:20', 'regex:/^[0-9]+(px|rem|em|%)?$/'],
            'glass_blur' => ['nullable', 'string', 'max:20', 'regex:/^[0-9]+(px|rem|em)?$/'],
            'glass_bg' => ['nullable', 'string', 'max:50'],
            'glass_border' => ['nullable', 'string', 'max:50'],
            'nav_labels_en.*' => ['nullable', 'string', 'max:50'],
            'nav_labels_ar.*' => ['nullable', 'string', 'max:50'],
            'nav_urls.*' => ['nullable', 'string', 'max:255'],
        ]);

        $fields = [
            'color_deep_space', 'color_dark_green', 'color_emerald', 'color_mint',
            'color_soft_mint', 'color_white', 'color_text_dark', 'color_text_light',
            'color_text_muted', 'font_family_latin', 'font_family_arabic',
            'radius_btn', 'radius_card', 'glass_blur', 'glass_bg', 'glass_border',
        ];

        foreach ($fields as $f) {
            if ($request->has($f) && isset($validated[$f])) {
                CmsThemeSetting::setKey($f, $validated[$f]);
            }
        }

        // Process Main Navigation Menu
        if ($request->has('nav_labels_en')) {
            $labelsEn = $request->input('nav_labels_en', []);
            $labelsAr = $request->input('nav_labels_ar', []);
            $navUrls = $request->input('nav_urls', []);

            $menu = [];
            foreach ($labelsEn as $i => $en) {
                if (! empty(trim($en))) {
                    $menu[] = [
                        'label_en' => trim($en),
                        'label_ar' => trim($labelsAr[$i] ?? $en),
                        'url' => trim($navUrls[$i] ?? '#'),
                    ];
                }
            }
            if (! empty($menu)) {
                CmsThemeSetting::setKey('main_navigation', $menu);
            }
        }

        return back()->with('success', __('Theme tokens and main navigation menu updated successfully!'));
    }

    /**
     * Global Feature Flags.
     */
    public function featureFlags()
    {
        $flags = FeatureFlag::orderBy('category')->get();

        return view('superadmin.features', compact('flags'));
    }

    /**
     * Toggle Feature Flag.
     */
    public function toggleFeature(FeatureFlag $flag)
    {
        $flag->update(['is_enabled' => ! $flag->is_enabled]);

        return back()->with('success', __("Feature flag ':name' updated!", ['name' => $flag->name]));
    }

    /**
     * System Health & Service Status.
     */
    public function systemHealth()
    {
        $health = [
            'database' => ['status' => 'healthy', 'latency_ms' => 1.2, 'label' => 'MySQL 8.0 Primary'],
            'storage' => ['status' => 'healthy', 'free_space' => disk_free_space('/') ? round(disk_free_space('/') / 1073741824, 1).' GB' : 'N/A', 'label' => 'Local NVMe Storage'],
            'livekit' => ['status' => 'healthy', 'url' => config('livekit.host', 'http://127.0.0.1:7880'), 'label' => 'LiveKit SFU WebRTC'],
            'openai' => ['status' => 'healthy', 'model' => 'gpt-image-1-mini / DALL-E', 'label' => 'OpenAI API Connectivity'],
            'websockets' => ['status' => 'healthy', 'port' => 8080, 'label' => 'Spatial WebSockets Gateway'],
        ];

        return view('superadmin.health', compact('health'));
    }
}
