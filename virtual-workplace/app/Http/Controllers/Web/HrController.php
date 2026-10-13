<?php

namespace App\Http\Controllers\Web;

use App\Domains\People\Models\OfficeHoliday;
use App\Domains\Tenancy\Models\OrganizationMember;
use App\Domains\Workspace\Models\Floor;
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
            default => abort(404),
        };
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
