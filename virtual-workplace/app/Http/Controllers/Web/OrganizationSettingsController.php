<?php

namespace App\Http\Controllers\Web;

use App\Domains\Administration\Models\AuditLog;
use App\Domains\Tenancy\Models\OrganizationMember;
use App\Domains\Identity\Models\Role;
use App\Domains\Identity\Models\User;
use App\Domains\People\Models\Department;
use App\Domains\People\Models\Team;
use App\Domains\People\Models\UserProfile;
use App\Domains\People\Requests\StoreDepartmentRequest;
use App\Domains\People\Requests\UpdateDepartmentRequest;
use App\Domains\People\Requests\StoreTeamRequest;
use App\Domains\People\Requests\UpdateTeamRequest;
use App\Domains\People\Requests\StoreMemberRequest;
use App\Domains\People\Requests\UpdateMemberRequest;
use App\Domains\Tenancy\Models\Organization;
use App\Domains\Tenancy\Models\Plan;
use App\Domains\Tenancy\Models\SubscriptionPaymentRequest;
use App\Http\Controllers\Controller;
use App\Mail\MeetingInvitationMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class OrganizationSettingsController extends Controller
{
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
        $newPlan = Plan::findOrFail($validated['plan_id']);

        if ((float)$newPlan->price > 0) {
            return redirect()->route('subscription.payment', ['plan' => $newPlan->id]);
        }

        $organization->update(['plan_id' => $newPlan->id]);

        return back()->with('success', "تم تغيير الباقة إلى {$newPlan->name} بنجاح!");
    }

    /**
     * Show the Bank Transfer Payment & Plan Details Page.
     */
    public function showPaymentPage(Plan $plan)
    {
        $user = Auth::user();
        $membership = OrganizationMember::where('user_id', $user->id)
            ->whereIn('status', ['active', 'invited'])
            ->with(['organization.plan', 'role.permissions'])
            ->first();

        if (!$membership) {
            return redirect()->route('login');
        }

        if (!$membership->hasPermission('organizations.manage') && $membership->role?->slug !== 'company_admin') {
            abort(403, 'Unauthorized: only organization admins can manage subscription payments.');
        }

        $organization = $membership->organization;
        $pendingRequest = $organization->pendingSubscriptionRequest()->where('plan_id', $plan->id)->first()
            ?: $organization->pendingSubscriptionRequest()->with('plan')->first();

        $priceUSD = (float)$plan->price;

        $defaultBankAccounts = [
            [
                'bank_name' => 'مصرف الراجحي (Al Rajhi Bank)',
                'account_name' => 'شركة مساحات العمل الافتراضية للاتصالات وتقنية المعلومات',
                'account_name_en' => 'Virtual Workplace Information Technology Co.',
                'iban' => 'SA4480000201608010099999',
                'account_number' => '201608010099999',
                'swift_code' => 'RJHISARI',
                'currency' => 'SAR / USD',
                'badge' => '⚡ التحويل الفوري المعتمد (Instant Transfer)',
            ],
            [
                'bank_name' => 'البنك الأهلي السعودي (Saudi National Bank - SNB)',
                'account_name' => 'شركة مساحات العمل الافتراضية للاتصالات وتقنية المعلومات',
                'account_name_en' => 'Virtual Workplace Information Technology Co.',
                'iban' => 'SA0310000001234567890123',
                'account_number' => '1234567890123',
                'swift_code' => 'NCBISARI',
                'currency' => 'SAR',
                'badge' => '🏢 الحساب التجاري المعتمد (Corporate)',
            ],
        ];

        $paymentSettings = \App\Domains\Administration\Models\SystemSetting::get('payment_settings', [
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

        $sarRate = (float)($paymentSettings['usd_to_sar_rate'] ?? 3.75);
        $priceSAR = round($priceUSD * $sarRate, 2);

        $bankAccounts = !empty($paymentSettings['bank_accounts']) ? $paymentSettings['bank_accounts'] : $defaultBankAccounts;

        $cleanSlug = preg_replace('/[^a-zA-Z0-9]/', '', $organization->slug ?: 'ORG');
        $referenceCode = 'PAY-' . strtoupper(substr($cleanSlug, 0, 4))
            . '-' . strtoupper(substr($plan->slug, 0, 4))
            . '-' . strtoupper(Str::random(4));

        return view('billing.payment', compact(
            'user', 'membership', 'organization', 'plan', 'pendingRequest',
            'priceUSD', 'priceSAR', 'referenceCode', 'bankAccounts', 'paymentSettings'
        ));
    }

    /**
     * Submit Bank Transfer Payment Confirmation & Receipt.
     */
    public function submitBankTransferPayment(Request $request, Plan $plan)
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
            abort(403, 'Unauthorized: only organization admins can manage subscription payments.');
        }

        $organization = $membership->organization;

        $validated = $request->validate([
            'sender_name' => ['required', 'string', 'max:255'],
            'bank_name' => ['required', 'string', 'max:255'],
            'sender_account' => ['nullable', 'string', 'max:100'],
            'transfer_reference' => ['required', 'string', 'max:100'],
            'amount' => ['required', 'numeric', 'min:0'],
            'currency' => ['required', 'string', 'in:SAR,USD'],
            'billing_cycle' => ['required', 'string', 'in:monthly,yearly'],
            'transfer_date' => ['required', 'date'],
            'receipt' => ['required', 'file', 'mimes:jpeg,png,jpg,webp,pdf', 'max:15360'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        // Store receipt
        $receiptPath = $request->file('receipt')->store('receipts', 'public');

        // Create subscription request
        $subRequest = SubscriptionRequest::create([
            'organization_id' => $organization->id,
            'user_id' => $user->id,
            'plan_id' => $plan->id,
            'amount' => $validated['amount'],
            'currency' => $validated['currency'],
            'billing_cycle' => $validated['billing_cycle'],
            'payment_method' => 'bank_transfer',
            'bank_name' => $validated['bank_name'],
            'sender_name' => $validated['sender_name'],
            'sender_account' => $validated['sender_account'] ?? null,
            'transfer_reference' => $validated['transfer_reference'],
            'transfer_date' => $validated['transfer_date'],
            'receipt_path' => $receiptPath,
            'notes' => $validated['notes'] ?? null,
            'status' => 'pending',
        ]);

        AuditLog::create([
            'organization_id' => $organization->id,
            'actor_id' => $user->id,
            'action' => 'subscription.bank_transfer_submitted',
            'metadata' => [
                'plan_id' => $plan->id,
                'plan_name' => $plan->name,
                'amount' => $validated['amount'],
                'currency' => $validated['currency'],
                'transfer_reference' => $validated['transfer_reference'],
                'request_id' => $subRequest->id,
            ],
        ]);

        return redirect()->route('dashboard')
            ->with('success', "تم إرسال إشعار التحويل البنكي للاشتراك في باقة ({$plan->name}) بنجاح! طلبكم قيد المراجعة والاعتماد من الإدارة.");
    }

    /**
     * Cancel a pending subscription request.
     */
    public function cancelSubscriptionRequest(SubscriptionRequest $subscriptionRequest)
    {
        $user = Auth::user();
        $membership = OrganizationMember::where('user_id', $user->id)
            ->whereIn('status', ['active', 'invited'])
            ->with(['organization', 'role.permissions'])
            ->first();

        if (!$membership || $membership->organization_id !== $subscriptionRequest->organization_id) {
            abort(403, 'Unauthorized.');
        }

        if ($subscriptionRequest->isPending()) {
            $subscriptionRequest->update([
                'status' => 'cancelled',
                'admin_notes' => 'Cancelled by user',
            ]);
        }

        return back()->with('success', 'تم إلغاء طلب الاشتراك بنجاح.');
    }

    /**

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
            if ($role->slug === 'super_admin' && !$user->isSuperAdmin()) {
                abort(403, 'Unauthorized: only the System Owner (Super Admin) can assign or create a Super Admin.');
            }
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
     * Store or Invite a new Team Member in the Organization.
     */
    public function storeMember(Request $request)
    {
        $user = Auth::user();
        $membership = OrganizationMember::where('user_id', $user->id)->with('role.permissions')->first();
        if (!$membership) abort(403);

        if (!$membership->hasPermission('members.manage') && $membership->role?->slug !== 'company_admin') {
            abort(403, 'Unauthorized: insufficient permissions to manage members.');
        }

        // Strict Plan Seat Limit Enforcement
        if ($membership->organization->hasReachedSeatLimit()) {
            $limit = $membership->organization->plan->seat_limit ?? 5;
            return back()->with('error', __("You have reached the maximum team member capacity (:limit seats) for your subscription plan. Please upgrade your plan to add more team members.", ['limit' => $limit]));
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255'],
            'role_id' => ['required', 'exists:roles,id'],
            'password' => ['nullable', 'string', 'min:8'],
            'job_title' => ['nullable', 'string', 'max:255'],
            'department_id' => ['nullable', 'exists:departments,id'],
            'team_id' => ['nullable', 'exists:teams,id'],
            'status' => ['nullable', 'in:active,invited,suspended'],
            'allowed_offices' => ['nullable', 'array'],
            'allowed_offices.*' => ['uuid', 'exists:floors,id'],
            'allowed_rooms' => ['nullable', 'array'],
            'allowed_rooms.*' => ['uuid', 'exists:rooms,id'],
        ]);

        $targetRole = \App\Domains\Administration\Models\Role::findOrFail($validated['role_id']);
        if ($targetRole->slug === 'super_admin' && !$user->isSuperAdmin()) {
            abort(403, 'Unauthorized: only the System Owner (Super Admin) can assign or create a Super Admin.');
        }

        if (!empty($validated['department_id'])) {
            $dept = \App\Domains\People\Models\Department::findOrFail($validated['department_id']);
            if ($dept->organization_id !== $membership->organization_id) {
                abort(403, 'Invalid department selection.');
            }
        }

        if (!empty($validated['team_id'])) {
            $team = \App\Domains\People\Models\Team::findOrFail($validated['team_id']);
            if ($team->organization_id !== $membership->organization_id) {
                abort(403, 'Invalid team selection.');
            }
        }

        // Find or create User
        $targetUser = \App\Domains\Identity\Models\User::where('email', $validated['email'])->first();
        $plainPassword = $validated['password'] ?: 'Password@1234';
        
        if (!$targetUser) {
            $targetUser = \App\Domains\Identity\Models\User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => \Illuminate\Support\Facades\Hash::make($plainPassword),
                'email_verified_at' => now(),
            ]);
        } else {
            $targetUser->name = $validated['name'];
            if (!empty($validated['password'])) {
                $targetUser->password = \Illuminate\Support\Facades\Hash::make($validated['password']);
            }
            $targetUser->save();
        }

        // Create or update membership
        $memberStatus = $validated['status'] ?? 'active';
        $member = OrganizationMember::updateOrCreate(
            [
                'organization_id' => $membership->organization_id,
                'user_id' => $targetUser->id,
            ],
            [
                'role_id' => $validated['role_id'],
                'status' => $memberStatus,
            ]
        );

        // Sync allowed offices & rooms
        if (isset($validated['allowed_offices'])) {
            $member->offices()->sync($validated['allowed_offices']);
        }
        if (isset($validated['allowed_rooms'])) {
            $member->rooms()->sync($validated['allowed_rooms']);
        }

        // Create or update Profile
        $profile = \App\Domains\People\Models\UserProfile::firstOrNew([
            'user_id' => $targetUser->id,
            'organization_id' => $membership->organization_id,
        ]);
        $profile->department_id = $validated['department_id'] ?? null;
        $profile->team_id = $validated['team_id'] ?? null;
        $profile->job_title = $validated['job_title'] ?? null;
        $profile->save();

        \App\Domains\Administration\Models\AuditLog::create([
            'organization_id' => $membership->organization_id,
            'user_id' => $user->id,
            'action' => 'member.created',
            'target_type' => 'user',
            'target_id' => $targetUser->id,
            'metadata' => [
                'name' => $targetUser->name,
                'email' => $targetUser->email,
                'role' => $targetRole->name,
                'status' => $memberStatus,
            ],
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return redirect('/dashboard#members')->with('success', __('Team member created/invited successfully and added to workspace!'));
    }

    /**
     * Update complete Organization Member details (Name, Email, Job Title, Department, Team, Role, Status, Allowed Offices & Rooms).
     */
    public function updateOrganizationMember(Request $request, OrganizationMember $member)
    {
        $user = Auth::user();
        $membership = OrganizationMember::where('user_id', $user->id)->with('role.permissions')->first();
        if (!$membership) abort(403);

        if ($member->organization_id !== $membership->organization_id) {
            abort(403, 'Unauthorized member access.');
        }

        if (!$membership->hasPermission('members.manage') && $membership->role?->slug !== 'company_admin') {
            abort(403, 'Unauthorized: insufficient permissions to manage members.');
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,' . $member->user_id],
            'job_title' => ['nullable', 'string', 'max:255'],
            'department_id' => ['nullable', 'exists:departments,id'],
            'team_id' => ['nullable', 'exists:teams,id'],
            'role_id' => ['required', 'exists:roles,id'],
            'status' => ['required', 'in:active,invited,suspended'],
            'allowed_offices' => ['nullable', 'array'],
            'allowed_offices.*' => ['uuid', 'exists:floors,id'],
            'allowed_rooms' => ['nullable', 'array'],
            'allowed_rooms.*' => ['uuid', 'exists:rooms,id'],
        ]);

        $targetRole = \App\Domains\Administration\Models\Role::findOrFail($validated['role_id']);
        if ($targetRole->slug === 'super_admin' && !$user->isSuperAdmin()) {
            abort(403, 'Unauthorized: only the System Owner (Super Admin) can assign or create a Super Admin.');
        }

        if (!empty($validated['department_id'])) {
            $dept = \App\Domains\People\Models\Department::findOrFail($validated['department_id']);
            if ($dept->organization_id !== $membership->organization_id) {
                abort(403, 'Invalid department selection.');
            }
        }

        if (!empty($validated['team_id'])) {
            $team = \App\Domains\People\Models\Team::findOrFail($validated['team_id']);
            if ($team->organization_id !== $membership->organization_id) {
                abort(403, 'Invalid team selection.');
            }
        }

        $member->user->update([
            'name' => $validated['name'],
            'email' => $validated['email'],
        ]);

        $member->update([
            'role_id' => $validated['role_id'],
            'status' => $validated['status'],
        ]);

        // Sync allowed offices & rooms
        $member->offices()->sync($validated['allowed_offices'] ?? []);
        $member->rooms()->sync($validated['allowed_rooms'] ?? []);

        $profile = \App\Domains\People\Models\UserProfile::firstOrNew([
            'user_id' => $member->user_id,
            'organization_id' => $member->organization_id,
        ]);

        $profile->department_id = $validated['department_id'] ?? null;
        $profile->team_id = $validated['team_id'] ?? null;
        $profile->job_title = $validated['job_title'] ?? null;
        $profile->save();

        return back()->with('success', __('Member details and office/room access updated successfully.'));
    }

    /**
     * Update Member Password by Company Admin.
     */
    public function updateMemberPassword(Request $request, OrganizationMember $member)
    {
        $user = Auth::user();
        $membership = OrganizationMember::where('user_id', $user->id)->with('role.permissions')->first();
        if (!$membership) abort(403);

        if ($member->organization_id !== $membership->organization_id) {
            abort(403, 'Unauthorized member access.');
        }

        if (!$membership->hasPermission('members.manage') && $membership->role?->slug !== 'company_admin') {
            abort(403, 'Unauthorized: insufficient permissions to manage members.');
        }

        $validated = $request->validate([
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $member->user->update([
            'password' => \Illuminate\Support\Facades\Hash::make($validated['password']),
        ]);

        return back()->with('success', __('Member password has been updated successfully.'));
    }

    /**
     * Remove Member from Organization.
     */
    public function deleteOrganizationMember(OrganizationMember $member)
    {
        $user = Auth::user();
        $membership = OrganizationMember::where('user_id', $user->id)->with('role.permissions')->first();
        if (!$membership) abort(403);

        if ($member->organization_id !== $membership->organization_id) {
            abort(403, 'Unauthorized member access.');
        }

        if (!$membership->hasPermission('members.manage') && $membership->role?->slug !== 'company_admin') {
            abort(403, 'Unauthorized: insufficient permissions to manage members.');
        }

        if ($member->user_id === $user->id) {
            return back()->with('error', __('You cannot remove your own administrative account.'));
        }

        $member->offices()->detach();
        $member->rooms()->detach();
        $member->delete();

        return back()->with('success', __('Member has been removed from organization.'));
    }

    /**
     * Fetch full Team Member Profile Details (Bio, Skills, Contact, Assigned Tasks, Work Time Logs, Allowed Offices & Rooms).
     */
    public function getMemberProfileDetails(OrganizationMember $member): \Illuminate\Http\JsonResponse
    {
        $user = Auth::user();
        $membership = OrganizationMember::where('user_id', $user->id)
            ->whereIn('status', ['active', 'invited'])
            ->first();

        if (!$membership || $member->organization_id !== $membership->organization_id) {
            return response()->json(['message' => 'Unauthorized member access.'], 403);
        }

        $targetUser = $member->user;
        $profile = \App\Domains\People\Models\UserProfile::where('user_id', $targetUser->id)
            ->where('organization_id', $member->organization_id)
            ->first();

        $dept = $profile && $profile->department_id ? \App\Domains\People\Models\Department::find($profile->department_id) : null;
        $team = $profile && $profile->team_id ? \App\Domains\People\Models\Team::find($profile->team_id) : null;

        // Fetch tasks assigned to this member in this organization
        $tasks = \App\Domains\Projects\Models\Task::where('organization_id', $member->organization_id)
            ->where('assignee_id', $targetUser->id)
            ->with(['project:id,name,code', 'checklistItems'])
            ->orderBy('due_date')
            ->latest()
            ->get()
            ->map(function ($t) {
                $totalChecklist = $t->checklistItems->count();
                $doneChecklist = $t->checklistItems->where('is_completed', true)->count();

                return [
                    'id' => $t->id,
                    'task_number' => $t->task_number,
                    'title' => $t->title,
                    'status' => $t->status,
                    'priority' => $t->priority,
                    'project' => $t->project ? [
                        'id' => $t->project->id,
                        'name' => $t->project->name,
                        'code' => $t->project->code ?? 'PRJ',
                    ] : null,
                    'due_date' => $t->due_date ? $t->due_date->format('M d, Y') : null,
                    'is_overdue' => $t->due_date && $t->due_date->isPast() && $t->status !== 'done',
                    'estimated_hours' => (float)($t->estimated_hours ?? 0),
                    'actual_hours' => (float)($t->actual_hours ?? 0),
                    'checklist_count' => $totalChecklist,
                    'checklist_done' => $doneChecklist,
                ];
            });

        // Fetch time entries logged by this member in this organization
        $timeEntries = \App\Domains\Projects\Models\TimeEntry::where('organization_id', $member->organization_id)
            ->where('user_id', $targetUser->id)
            ->with(['project:id,name,code', 'task:id,task_number,title'])
            ->latest('started_at')
            ->take(20)
            ->get()
            ->map(function ($te) {
                return [
                    'id' => $te->id,
                    'date' => $te->started_at ? $te->started_at->format('M d, Y') : '—',
                    'duration_formatted' => $te->formattedDuration(),
                    'description' => $te->description ?? 'General Work Session',
                    'project_name' => $te->project?->name ?? 'General',
                    'task_title' => $te->task ? ('#' . $te->task->task_number . ' ' . $te->task->title) : '—',
                    'is_billable' => (bool)$te->is_billable,
                ];
            });

        $totalDurationSeconds = \App\Domains\Projects\Models\TimeEntry::where('organization_id', $member->organization_id)
            ->where('user_id', $targetUser->id)
            ->sum('duration_seconds');
        $totalHoursLogged = round($totalDurationSeconds / 3600, 1);

        $activeTimer = \App\Domains\Projects\Models\ActiveTimer::where('user_id', $targetUser->id)
            ->with(['project:id,name,code', 'task:id,task_number,title'])
            ->first();

        return response()->json([
            'member' => [
                'id' => $member->id,
                'user_id' => $targetUser->id,
                'name' => $targetUser->name,
                'nickname' => $targetUser->nickname,
                'email' => $targetUser->email,
                'avatar_url' => $targetUser->avatar_url,
                'role_name' => $member->role?->name ?? 'Member',
                'role_slug' => $member->role?->slug ?? 'employee',
                'role_id' => $member->role_id,
                'status' => $member->status,
                'joined_at' => $member->joined_at ? $member->joined_at->format('M d, Y') : ($member->created_at ? $member->created_at->format('M d, Y') : '—'),
                'allowed_office_ids' => $member->offices->pluck('id')->toArray(),
                'allowed_room_ids' => $member->rooms->pluck('id')->toArray(),
            ],
            'profile' => [
                'job_title' => $profile?->job_title ?? $member->role?->name ?? 'Team Member',
                'department_id' => $profile?->department_id,
                'team_id' => $profile?->team_id,
                'department_name' => $dept?->name,
                'team_name' => $team?->name,
                'work_mode' => $profile?->work_mode ?? 'remote',
                'phone' => $profile?->phone,
                'date_of_birth' => $profile?->date_of_birth ? $profile->date_of_birth->format('M d, Y') : null,
                'bio' => $profile?->bio,
                'skills' => $profile?->skills ? array_filter(array_map('trim', explode(',', $profile->skills))) : [],
                'hobbies' => $profile?->hobbies ? array_filter(array_map('trim', explode(',', $profile->hobbies))) : [],
                'notes' => $profile?->notes,
                'social_links' => (array)($profile?->social_links ?? []),
            ],
            'stats' => [
                'total_tasks' => $tasks->count(),
                'completed_tasks' => $tasks->where('status', 'done')->count(),
                'in_progress_tasks' => $tasks->where('status', 'in_progress')->count(),
                'pending_tasks' => $tasks->whereNotIn('status', ['done', 'in_progress'])->count(),
                'total_hours_logged' => $totalHoursLogged,
                'active_timer' => $activeTimer ? [
                    'id' => $activeTimer->id,
                    'started_at' => $activeTimer->started_at->toIso8601String(),
                    'project_name' => $activeTimer->project?->name,
                    'task_title' => $activeTimer->task ? ('#' . $activeTimer->task->task_number . ' ' . $activeTimer->task->title) : null,
                ] : null,
            ],
            'tasks' => $tasks,
            'time_entries' => $timeEntries,
        ]);
    }

    /**
     * Clear all guest meeting links for the organization.

    /**
     * Clear / Purge all audit logs for the organization.
     */
    public function clearAuditLogs(Request $request)
    {
        $user = Auth::user();
        $membership = OrganizationMember::where('user_id', $user->id)->with('role.permissions')->first();
        if (!$membership) abort(403);

        if (!$membership->hasPermission('audit.view') && !$membership->hasPermission('organizations.manage') && $membership->role?->slug !== 'company_admin') {
            abort(403, 'Unauthorized: insufficient permissions.');
        }

        \App\Domains\Administration\Models\AuditLog::where('organization_id', $membership->organization_id)->delete();

        return back()->with('success', __('All audit logs have been cleared successfully.'));
    }

    /**
     * Update Workspace / Organization Settings (including Logo upload).
     */
    public function updateOrganizationSettings(Request $request)
    {
        $user = Auth::user();
        $membership = OrganizationMember::where('user_id', $user->id)->with('role.permissions', 'organization')->first();
        if (!$membership) abort(403);

        if (!$membership->hasPermission('organizations.manage') && $membership->role?->slug !== 'company_admin') {
            abort(403, 'Unauthorized: insufficient permissions.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'timezone' => 'required|string|max:100',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp|max:4096',
            'mail_driver' => 'nullable|string|max:50',
            'mail_host' => 'nullable|string|max:255',
            'mail_port' => 'nullable|numeric',
            'mail_username' => 'nullable|string|max:255',
            'mail_password' => 'nullable|string|max:255',
            'mail_encryption' => 'nullable|string|max:50',
            'mail_from_address' => 'nullable|email|max:255',
            'mail_from_name' => 'nullable|string|max:255',
        ]);

        $organization = $membership->organization;
        $organization->name = $validated['name'];
        $organization->timezone = $validated['timezone'];

        if ($request->hasFile('logo')) {
            $file = $request->file('logo');
            $filename = 'org_logo_' . $organization->id . '_' . time() . '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs('logos', $filename, 'public');
            $organization->logo_url = '/storage/' . $path;
        }

        $organization->save();

        // Update SMTP Mail Settings
        $orgSettings = $organization->settings ?? OrganizationSetting::firstOrCreate([
            'organization_id' => $organization->id,
        ], [
            'branding' => [],
            'policies' => [],
            'smtp_settings' => [],
        ]);

        $currentSmtp = $orgSettings->smtp_settings ?? [];
        $newSmtp = array_merge($currentSmtp, array_filter([
            'mail_driver' => $request->input('mail_driver', 'smtp'),
            'mail_host' => $request->input('mail_host'),
            'mail_port' => $request->input('mail_port'),
            'mail_username' => $request->input('mail_username'),
            'mail_password' => $request->filled('mail_password') ? $request->input('mail_password') : ($currentSmtp['mail_password'] ?? null),
            'mail_encryption' => $request->input('mail_encryption', 'tls'),
            'mail_from_address' => $request->input('mail_from_address'),
            'mail_from_name' => $request->input('mail_from_name'),
        ], function ($val) {
            return !is_null($val);
        }));

        $orgSettings->smtp_settings = $newSmtp;

        // Update Organization OpenAI / AI Floorplan Settings
        $currentOpenAi = $orgSettings->openai_settings ?? [];
        $openAiApiKey = $request->filled('openai_api_key') ? trim($request->input('openai_api_key')) : ($currentOpenAi['api_key'] ?? '');
        $newOpenAi = [
            'api_key' => $openAiApiKey,
            'model' => $request->input('openai_model', 'gpt-image-1-mini'),
            'image_size' => $request->input('openai_image_size', '1024x1024'),
            'quality' => $request->input('openai_quality', 'standard'),
            'is_enabled' => $request->has('openai_is_enabled') || !empty($openAiApiKey),
        ];
        $orgSettings->openai_settings = $newOpenAi;

        // Update Organization Attendance & Inactivity Policies
        $currentPolicies = $orgSettings->policies ?? [];
        $currentPolicies['attendance'] = [
            'auto_attendance_enabled' => $request->has('attendance_auto_enabled') || $request->input('attendance_auto_enabled', '1') === '1',
            'idle_prompt_minutes' => max(1, (int)$request->input('attendance_idle_prompt_minutes', 15)),
            'idle_response_grace_seconds' => max(30, (int)$request->input('attendance_idle_grace_seconds', 180)),
            'allow_in_office_task_tracking' => true,
        ];
        $orgSettings->policies = $currentPolicies;

        $orgSettings->save();

        return redirect('/dashboard#settings')->with('success', __('Workspace settings, company logo, SMTP email, OpenAI configuration, and Time Tracking policies updated successfully!'));
    }

    /**
     * Test Organization OpenAI API connectivity.
     */
    public function testOrgAiConnection(Request $request)
    {
        $user = Auth::user();
        $membership = OrganizationMember::where('user_id', $user->id)->first();
        if (!$membership) abort(403);

        if (!$membership->hasPermission('organizations.manage') && $membership->role?->slug !== 'company_admin' && !$user->isSuperAdmin()) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'message' => __('Unauthorized: insufficient permissions.')], 403);
            }
            abort(403, 'Unauthorized: insufficient permissions.');
        }

        $apiKey = trim($request->input('api_key', ''));
        if (empty($apiKey)) {
            $orgSettings = $membership->organization->settings?->openai_settings ?? [];
            $apiKey = $orgSettings['api_key'] ?? '';
        }

        if (empty($apiKey)) {
            return response()->json(['success' => false, 'message' => __('OpenAI API key is missing. Please enter your OpenAI API key.')], 422);
        }

        try {
            $response = \Illuminate\Support\Facades\Http::withToken($apiKey)
                ->timeout(15)
                ->get('https://api.openai.com/v1/models');

            if ($response->successful()) {
                return response()->json(['success' => true, 'message' => __('✅ Connection to OpenAI API successful! Your organization key is valid and active.')]);
            } else {
                $err = $response->json();
                $errMsg = $err['error']['message'] ?? $response->body();
                return response()->json(['success' => false, 'message' => 'OpenAI Error: ' . $errMsg], 400);
            }
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => 'Network error connecting to OpenAI: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Apply organization SMTP settings dynamically to Laravel mailer.
     */
    protected function applyOrganizationSmtp($organization): void
    {
        $smtp = $organization->settings?->smtp_settings;
        if (!empty($smtp['mail_host'])) {
            config([
                'mail.default' => 'smtp',
                'mail.mailers.smtp.host' => $smtp['mail_host'],
                'mail.mailers.smtp.port' => (int)($smtp['mail_port'] ?? 587),
                'mail.mailers.smtp.encryption' => !empty($smtp['mail_encryption']) && $smtp['mail_encryption'] !== 'none' ? $smtp['mail_encryption'] : null,
                'mail.mailers.smtp.username' => $smtp['mail_username'] ?? null,
                'mail.mailers.smtp.password' => $smtp['mail_password'] ?? null,
                'mail.from.address' => $smtp['mail_from_address'] ?? config('mail.from.address'),
                'mail.from.name' => $smtp['mail_from_name'] ?? $organization->name,
            ]);
        }
    }

    /**
     * Test SMTP Mail Server connection and dispatch a test email.
     */
    public function testSmtpConnection(Request $request)
    {
        $user = Auth::user();
        $membership = OrganizationMember::where('user_id', $user->id)->first();
        if (!$membership) abort(403);
        $organization = $membership->organization;

        $validated = $request->validate([
            'mail_host' => 'required|string',
            'mail_port' => 'required|numeric',
            'mail_username' => 'nullable|string',
            'mail_password' => 'nullable|string',
            'mail_encryption' => 'nullable|string',
            'mail_from_address' => 'required|email',
            'mail_from_name' => 'nullable|string',
        ]);

        config([
            'mail.default' => 'smtp',
            'mail.mailers.smtp.host' => $validated['mail_host'],
            'mail.mailers.smtp.port' => (int)$validated['mail_port'],
            'mail.mailers.smtp.encryption' => !empty($validated['mail_encryption']) && $validated['mail_encryption'] !== 'none' ? $validated['mail_encryption'] : null,
            'mail.mailers.smtp.username' => $validated['mail_username'] ?? null,
            'mail.mailers.smtp.password' => $validated['mail_password'] ?? null,
            'mail.from.address' => $validated['mail_from_address'],
            'mail.from.name' => $validated['mail_from_name'] ?? $organization->name,
        ]);

        try {
            Mail::raw("Hello {$user->name},\n\nThis is a test email confirming that your SMTP settings for {$organization->name} on vMeeting Virtual Workplace are configured and working properly!\n\nDelivered at: " . now(), function ($msg) use ($user, $validated, $organization) {
                $msg->to($user->email)
                    ->subject("✅ [SMTP Test] Successful connection on {$organization->name}");
            });

            return response()->json([
                'success' => true,
                'message' => __('SMTP Connection Successful! Test email delivered to :email', ['email' => $user->email]),
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => __('SMTP Connection Failed: ') . $e->getMessage(),
            ], 422);
        }
    }
}
