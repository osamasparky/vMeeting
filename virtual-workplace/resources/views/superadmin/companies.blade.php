@extends('superadmin.layout')

@section('title', __('Companies'))
@section('page_title', __('Companies'))

@section('content')
<div class="panel-card">
    <div class="panel-header">
        <div class="panel-title">
            <span>🏢</span>
            <span>{{ __('Registered Companies') }}</span>
        </div>
        <form method="GET" action="{{ route('superadmin.companies') }}" style="display: flex; gap: 8px;">
            <input
                type="text"
                name="search"
                value="{{ request('search') }}"
                placeholder="{{ __('Search by name or slug...') }}"
                style="background: #f8fafc; border: 1px solid var(--border-color); border-radius: 8px; padding: 8px 14px; color: var(--brand-navy); font-size: 13px; outline: none; width: 220px;"
            >
            <button type="submit" class="btn-action">🔍 {{ __('Search') }}</button>
        </form>
    </div>

    <div class="data-table-container">
        <table class="data-table">
            <thead>
                <tr>
                    <th>{{ __('Company Name') }}</th>
                    <th>{{ __('Owner') }}</th>
                    <th>{{ __('Current Plan') }}</th>
                    <th>{{ __('Seat Usage') }}</th>
                    <th>{{ __('Rooms') }}</th>
                    <th>{{ __('Status') }}</th>
                    <th>{{ __('Actions') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($companies as $comp)
                @php
                    $seatLimit = $comp->plan?->seat_limit ?? 5;
                    $memberCount = $comp->members->count();
                    $isUnlimited = $seatLimit === 0;
                    $owner = $comp->members->first()?->user;
                    $isSuspended = $comp->settings?->is_suspended ?? false;
                @endphp
                <tr>
                    <td>
                        <strong style="color: var(--brand-navy); font-size: 14px;">{{ $comp->name }}</strong>
                        <div style="font-size: 11px; color: var(--text-muted);">{{ $comp->slug }}</div>
                    </td>
                    <td>
                        <div style="font-weight: 700; color: var(--brand-ocean);">{{ $owner?->name ?? 'Administrator' }}</div>
                        <div style="font-size: 11px; color: var(--text-muted);">{{ $owner?->email }}</div>
                    </td>
                    <td>
                        <span class="badge-status badge-plan">
                            💎 {{ $comp->plan?->name ?? 'Free' }}
                        </span>
                        <div style="font-size: 11px; color: var(--text-secondary); margin-top: 2px;">
                            ${{ number_format($comp->plan?->price ?? 0, 2) }}/mo
                        </div>
                    </td>
                    <td>
                        <div style="font-weight: 800; color: {{ !$isUnlimited && $memberCount >= $seatLimit ? 'var(--brand-crimson)' : 'var(--brand-green)' }}; font-size: 13px;">
                            {{ $memberCount }} / {{ $isUnlimited ? '∞' : $seatLimit }}
                        </div>
                        <div style="font-size: 10px; color: var(--text-muted);">{{ __('Seats used') }}</div>
                    </td>
                    <td>
                        <span style="font-weight: 700; color: var(--brand-ocean);">{{ $comp->rooms->count() }} {{ __('Rooms') }}</span>
                    </td>
                    <td>
                        @if($isSuspended)
                            <span class="badge-status badge-suspended">🛑 {{ __('Suspended') }}</span>
                        @else
                            <span class="badge-status badge-active">✅ {{ __('Active') }}</span>
                        @endif
                    </td>
                    <td>
                        <div style="display: flex; gap: 6px;">
                            <button
                                onclick="openChangePlanModal('{{ $comp->id }}', '{{ $comp->name }}', '{{ $comp->plan_id }}')"
                                class="btn-action"
                                title="{{ __('Change Plan') }}"
                            >
                                💎 {{ __('Change Plan') }}
                            </button>

                            <form method="POST" action="{{ route('superadmin.companies.toggle', $comp) }}" style="display: inline;">
                                @csrf
                                <button type="submit" class="btn-action" style="color: {{ $isSuspended ? 'var(--brand-green)' : 'var(--brand-crimson)' }};">
                                    {{ $isSuspended ? '▶️ ' . __('Activate') : '⏸️ ' . __('Suspend') }}
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" style="text-align: center; color: var(--text-muted); padding: 32px;">
                        No organizations found matching search criteria.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div style="margin-top: 20px;">
        {{ $companies->links() }}
    </div>
</div>

<!-- Change Plan Modal -->
<div id="changePlanModal" class="modal-overlay">
    <div class="modal-card">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 18px;">
            <h3 style="font-size: 16px; font-weight: 800; color: var(--brand-navy);" id="modalCompanyTitle">💎 {{ __('Change Subscription Plan') }}</h3>
            <button onclick="closeChangePlanModal()" style="background: none; border: none; color: #94a3b8; font-size: 20px; cursor: pointer;">✕</button>
        </div>

        <form id="changePlanForm" method="POST" action="">
            @csrf
            <div style="margin-bottom: 20px;">
                <label style="display: block; font-size: 12px; font-weight: 700; color: var(--text-secondary); margin-bottom: 8px;">
                    {{ __('Select New Subscription Tier (Seats)') }}
                </label>
                <select name="plan_id" id="modalPlanSelect" style="width: 100%; background: #f8fafc; border: 1px solid var(--border-color); border-radius: 10px; padding: 12px; color: var(--brand-navy); font-size: 13px; outline: none; font-weight: 600;">
                    @foreach($plans as $plan)
                        <option value="{{ $plan->id }}">
                            💎 {{ $plan->name }} — {{ $plan->seat_limit === 0 ? 'Unlimited' : $plan->seat_limit }} Users (${{ number_format($plan->price, 2) }}/mo)
                        </option>
                    @endforeach
                </select>
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 10px;">
                <button type="button" onclick="closeChangePlanModal()" class="btn-action">{{ __('Cancel') }}</button>
                <button type="submit" class="btn-action" style="background: var(--brand-teal); border-color: var(--brand-teal); color: white;">
                    💾 {{ __('Save Changes') }}
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script>
    function openChangePlanModal(orgId, orgName, currentPlanId) {
        document.getElementById('modalCompanyTitle').textContent = `💎 Change Plan for ${orgName}`;
        document.getElementById('changePlanForm').action = `/superadmin/companies/${orgId}/plan`;
        if (currentPlanId) {
            document.getElementById('modalPlanSelect').value = currentPlanId;
        }
        document.getElementById('changePlanModal').style.display = 'flex';
    }

    function closeChangePlanModal() {
        document.getElementById('changePlanModal').style.display = 'none';
    }
</script>
@endsection
