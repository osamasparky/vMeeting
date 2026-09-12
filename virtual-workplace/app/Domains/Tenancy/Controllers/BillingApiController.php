<?php

namespace App\Domains\Tenancy\Controllers;

use App\Domains\Tenancy\Models\Organization;
use App\Domains\Tenancy\Models\Plan;
use App\Domains\Tenancy\Models\SubscriptionRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class BillingApiController extends Controller
{
    /**
     * Get available subscription plans with regional currencies.
     */
    public function plans(Request $request, Organization $organization): JsonResponse
    {
        $plans = Plan::all();

        $currencies = [
            'SAR' => ['symbol' => 'ر.س', 'rate' => 1.0, 'name' => 'Saudi Riyal'],
            'EGP' => ['symbol' => 'ج.م', 'rate' => 13.15, 'name' => 'Egyptian Pound'],
            'AED' => ['symbol' => 'د.إ', 'rate' => 0.98, 'name' => 'UAE Dirham'],
            'USD' => ['symbol' => '$', 'rate' => 0.27, 'name' => 'US Dollar'],
        ];

        $paymentMethods = [
            [
                'id' => 'instapay',
                'name' => 'Instapay مصر (دفع فوري بالجنيه)',
                'account' => 'nextspace@instapay',
                'currency' => 'EGP',
            ],
            [
                'id' => 'stc_pay',
                'name' => 'STC Pay السعودية (دفع فوري بالريال)',
                'account' => '+966 50 123 4567',
                'currency' => 'SAR',
            ],
            [
                'id' => 'vodafone_cash',
                'name' => 'فودافون كاش مصر (محافظ رقمية)',
                'account' => '01012345678',
                'currency' => 'EGP',
            ],
            [
                'id' => 'bank_transfer',
                'name' => 'تحويل بنكي رسمي (IBAN & SWIFT)',
                'account' => 'SA0380000000608010167519',
                'bank' => 'بنك الراجحي - Al Rajhi Bank',
                'currency' => 'SAR',
            ],
        ];

        return response()->json([
            'plans' => $plans,
            'currencies' => $currencies,
            'payment_methods' => $paymentMethods,
        ]);
    }

    /**
     * Get active subscription details for the organization.
     */
    public function subscription(Request $request, Organization $organization): JsonResponse
    {
        $organization->load('plan');
        $activeRequest = SubscriptionRequest::where('organization_id', $organization->id)
            ->where('status', 'pending')
            ->latest()
            ->first();

        return response()->json([
            'current_plan' => $organization->plan,
            'status' => 'active',
            'pending_upgrade_request' => $activeRequest,
        ]);
    }

    /**
     * Submit a subscription upgrade request with receipt upload.
     */
    public function checkout(Request $request, Organization $organization): JsonResponse
    {
        $validated = $request->validate([
            'plan_id' => 'required|exists:plans,id',
            'payment_method' => 'required|string|in:instapay,stc_pay,vodafone_cash,bank_transfer',
            'currency' => 'required|string|in:SAR,EGP,AED,USD',
            'reference_number' => 'nullable|string|max:100',
            'notes' => 'nullable|string|max:500',
            'receipt' => 'nullable|image|max:10240', // Max 10MB
        ]);

        $receiptPath = null;
        if ($request->hasFile('receipt')) {
            $receiptPath = $request->file('receipt')->store('receipts', 'public');
        }

        $subscriptionRequest = SubscriptionRequest::create([
            'organization_id' => $organization->id,
            'user_id' => $request->user()->id,
            'plan_id' => $validated['plan_id'],
            'payment_method' => $validated['payment_method'],
            'currency' => $validated['currency'],
            'reference_number' => $validated['reference_number'] ?? null,
            'notes' => $validated['notes'] ?? null,
            'receipt_path' => $receiptPath,
            'status' => 'pending',
        ]);

        return response()->json([
            'message' => 'Subscription request submitted successfully. It will be reviewed by admin.',
            'subscription_request' => $subscriptionRequest,
        ], 201);
    }
}
