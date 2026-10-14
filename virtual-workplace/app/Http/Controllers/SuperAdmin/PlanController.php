<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Domains\Administration\Models\AuditLog;
use App\Domains\Tenancy\Models\Organization;
use App\Domains\Tenancy\Models\Plan;
use App\Domains\Tenancy\Models\Subscription;
use App\Domains\Tenancy\Models\SubscriptionRequest;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Super Admin — Plans, subscription requests and receipts.
 * Moved verbatim from SuperAdminController (refactor phase 7).
 */
class PlanController extends Controller
{
    /**
     * Subscription Plans Management.
     */
    public function plans()
    {
        $user = Auth::user();
        $plans = Plan::withCount(['organizations', 'subscriptions'])->orderBy('price', 'asc')->get();

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
            'is_per_seat' => ['nullable', 'boolean'],
            'min_seats' => ['nullable', 'integer', 'min:1'],
            'max_seats' => ['nullable', 'integer', 'min:1'],
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
            'is_per_seat' => $request->has('is_per_seat'),
            'min_seats' => $validated['min_seats'] ?? 2,
            'max_seats' => $validated['max_seats'] ?? null,
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
            'is_per_seat' => ['nullable', 'boolean'],
            'min_seats' => ['nullable', 'integer', 'min:1'],
            'max_seats' => ['nullable', 'integer', 'min:1'],
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
            'is_per_seat' => $request->has('is_per_seat'),
            'min_seats' => $validated['min_seats'] ?? 2,
            'max_seats' => $validated['max_seats'] ?? null,
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
        if ($plan->organizations()->count() > 0 || $plan->subscriptions()->count() > 0) {
            return back()->with('error', __('Cannot delete plan assigned to active organizations or subscriptions.'));
        }

        $plan->delete();

        return back()->with('success', __('Plan deleted successfully.'));
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
        $seats = $subscriptionRequest->seats ?: ($plan->isPerSeat() ? $plan->getEffectiveMinSeats() : $plan->seat_limit);

        // 1. Update Organization Plan
        $organization->update([
            'plan_id' => $plan->id,
        ]);

        // 2. Create / Renew / Update Subscription Record
        $existingSub = Subscription::where('organization_id', $organization->id)->first();
        if ($existingSub) {
            $existingSub->update([
                'plan_id' => $plan->id,
                'seats' => $seats,
                'billing_cycle' => $subscriptionRequest->billing_cycle ?: 'monthly',
                'price_per_seat' => $subscriptionRequest->price_per_seat,
                'status' => 'active',
                'current_period_end' => now()->addMonths($months),
            ]);
        } else {
            Subscription::create([
                'organization_id' => $organization->id,
                'plan_id' => $plan->id,
                'seats' => $seats,
                'billing_cycle' => $subscriptionRequest->billing_cycle ?: 'monthly',
                'price_per_seat' => $subscriptionRequest->price_per_seat,
                'status' => 'active',
                'current_period_end' => now()->addMonths($months),
            ]);
        }

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
                'seats' => $seats,
                'amount' => $subscriptionRequest->amount,
                'reference' => $subscriptionRequest->transfer_reference,
            ],
        ]);

        return back()->with('success', "تم قبول طلب التحويل البنكي وتفعيل باقة ({$plan->name}) لعدد ({$seats}) مقاعد لشركة {$organization->name} بنجاح!");
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
}
