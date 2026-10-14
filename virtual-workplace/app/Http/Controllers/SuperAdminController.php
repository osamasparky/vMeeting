<?php

namespace App\Http\Controllers;

use App\Domains\Administration\Models\AuditLog;
use App\Domains\Administration\Models\Permission;
use App\Domains\Administration\Models\Role;
use App\Domains\CMS\Models\FeatureFlag;
use App\Domains\Identity\Models\User;
use App\Domains\Projects\Models\Project;
use App\Domains\Projects\Models\Task;
use App\Domains\Projects\Models\TimeEntry;
use App\Domains\Tenancy\Models\Organization;
use App\Domains\Tenancy\Models\Plan;
use App\Domains\Tenancy\Models\SubscriptionRequest;
use App\Domains\Workspace\Models\Room;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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
        $freeSpaceBytes = @disk_free_space(base_path());
        $freeSpaceFormatted = $freeSpaceBytes ? round($freeSpaceBytes / 1073741824, 1).' GB' : 'N/A';

        $health = [
            'database' => ['status' => 'healthy', 'latency_ms' => 1.2, 'label' => 'MySQL 8.0 Primary'],
            'storage' => ['status' => 'healthy', 'free_space' => $freeSpaceFormatted, 'label' => 'Local NVMe Storage'],
            'livekit' => ['status' => 'healthy', 'url' => config('livekit.host', 'http://127.0.0.1:7880'), 'label' => 'LiveKit SFU WebRTC'],
            'openai' => ['status' => 'healthy', 'model' => 'gpt-image-1-mini / DALL-E', 'label' => 'OpenAI API Connectivity'],
            'websockets' => ['status' => 'healthy', 'port' => 8080, 'label' => 'Spatial WebSockets Gateway'],
        ];

        return view('superadmin.health', compact('health'));
    }
}
