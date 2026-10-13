<?php

namespace App\Http\Controllers\Web;

use App\Domains\People\Models\LeaveRequest;
use App\Domains\People\Models\OfficeHoliday;
use App\Domains\People\Services\LeaveService;
use App\Domains\Tenancy\Models\OrganizationMember;
use App\Domains\Workspace\Models\Floor;
use App\Domains\Workspace\Services\OfficeTimezone;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

/**
 * Personnel affairs on the dashboard: official holidays, leave requests and
 * the monthly attendance report.
 */
class HrController extends Controller
{
    public function __construct(protected LeaveService $leaves) {}

    private function currentMembership(): OrganizationMember
    {
        $membership = OrganizationMember::where('user_id', Auth::id())->with('role.permissions')->first();
        abort_unless($membership, 403);

        return $membership;
    }

    /** Holidays are office settings: whoever manages offices manages them. */
    private function canManageOffices(OrganizationMember $membership): bool
    {
        return $membership->isCompanyOwner() || $membership->hasPermission('maps.manage');
    }

    /**
     * HR panels are rendered on demand when their dashboard tab opens, so the
     * dashboard itself does not pay for their queries.
     */
    public function panel(Request $request, string $panel): Response
    {
        $membership = $this->currentMembership();
        $organization = $membership->organization;

        return match ($panel) {
            'holidays' => $this->canManageOffices($membership)
                ? response()->view('dashboard.partials.hr-holidays-card', [
                    'organization' => $organization,
                    'offices' => Floor::withoutGlobalScopes()->where('organization_id', $organization->id)->orderBy('order')->get(['id', 'name']),
                ])
                : abort(403),
            'leaves' => response()->view('dashboard.partials.hr-leaves-panel', $this->leavesPanelData($membership)),
            default => abort(404),
        };
    }

    /** Only the company owner decides leave, as with timesheets. */
    private function canDecideLeave(OrganizationMember $membership): bool
    {
        return $membership->isCompanyOwner() || (bool) Auth::user()?->isSuperAdmin();
    }

    private function leavesPanelData(OrganizationMember $membership): array
    {
        $orgId = $membership->organization_id;
        $userId = $membership->user_id;
        $year = (int) now()->setTimezone(app(OfficeTimezone::class)->forMember($userId, $orgId))->format('Y');
        $isOwner = $this->canDecideLeave($membership);

        $requests = fn () => LeaveRequest::withoutGlobalScopes()->where('organization_id', $orgId)->with('user:id,name', 'reviewer:id,name');

        return [
            'isOwner' => $isOwner,
            'balance' => $this->leaves->balance($userId, $orgId, $year),
            'myRequests' => $requests()->where('user_id', $userId)->latest()->limit(20)->get(),
            'pending' => $isOwner ? $requests()->where('status', LeaveRequest::STATUS_PENDING)->orderBy('start_date')->get() : collect(),
            'decided' => $isOwner ? $requests()->where('status', '!=', LeaveRequest::STATUS_PENDING)->latest('updated_at')->limit(30)->get() : collect(),
        ];
    }

    // ── Leave requests ──

    public function storeLeave(Request $request): RedirectResponse
    {
        $membership = $this->currentMembership();

        $validated = $request->validate([
            'type' => ['required', Rule::in(LeaveRequest::TYPES)],
            'start_date' => ['required', 'date_format:Y-m-d'],
            'end_date' => ['required', 'date_format:Y-m-d', 'after_or_equal:start_date'],
            'reason' => ['nullable', 'string', 'max:1000'],
        ]);

        if (Carbon::parse($validated['start_date'])->diffInDays(Carbon::parse($validated['end_date'])) > 90) {
            throw ValidationException::withMessages(['end_date' => __('A leave request can span at most 90 days.')]);
        }

        $this->leaves->request($membership->organization, Auth::user(), $validated);

        return redirect()->to(route('dashboard').'#leaves')->with('success', __('Leave request sent to the company owner.'));
    }

    public function approveLeave(Request $request, LeaveRequest $leave): RedirectResponse
    {
        $this->authorizeDecision($leave);
        $this->leaves->approve($leave, Auth::user(), $request->validate(['note' => ['nullable', 'string', 'max:500']])['note'] ?? null);

        return redirect()->to(route('dashboard').'#leaves')->with('success', __('Leave approved.'));
    }

    public function rejectLeave(Request $request, LeaveRequest $leave): RedirectResponse
    {
        $this->authorizeDecision($leave);
        $this->leaves->reject($leave, Auth::user(), $request->validate(['note' => ['nullable', 'string', 'max:500']])['note'] ?? null);

        return redirect()->to(route('dashboard').'#leaves')->with('success', __('Leave rejected.'));
    }

    public function cancelLeave(LeaveRequest $leave): RedirectResponse
    {
        $membership = $this->currentMembership();
        abort_unless($leave->organization_id === $membership->organization_id && $leave->user_id === $membership->user_id, 403);

        $this->leaves->cancel($leave);

        return redirect()->to(route('dashboard').'#leaves')->with('success', __('Leave request cancelled.'));
    }

    private function authorizeDecision(LeaveRequest $leave): void
    {
        $membership = $this->currentMembership();
        abort_unless($this->canDecideLeave($membership) && $leave->organization_id === $membership->organization_id, 403);
    }

    // ── Official holidays ──

    public function storeHoliday(Request $request): RedirectResponse
    {
        $membership = $this->currentMembership();
        abort_unless($this->canManageOffices($membership), 403);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'start_date' => ['required', 'date_format:Y-m-d'],
            'end_date' => ['required', 'date_format:Y-m-d', 'after_or_equal:start_date'],
            // Empty: every office.
            'floor_id' => ['nullable', 'uuid', Rule::exists('floors', 'id')->where('organization_id', $membership->organization_id)],
        ]);

        if (Carbon::parse($validated['start_date'])->diffInDays(Carbon::parse($validated['end_date'])) > 60) {
            throw ValidationException::withMessages(['end_date' => __('A holiday can span at most 60 days.')]);
        }

        OfficeHoliday::create($validated + ['organization_id' => $membership->organization_id]);

        return redirect()->to(route('dashboard').'#offices')->with('success', __('Holiday added.'));
    }

    public function deleteHoliday(OfficeHoliday $holiday): RedirectResponse
    {
        $membership = $this->currentMembership();
        abort_unless($this->canManageOffices($membership) && $holiday->organization_id === $membership->organization_id, 403);

        $holiday->delete();

        return redirect()->to(route('dashboard').'#offices')->with('success', __('Holiday removed.'));
    }
}
