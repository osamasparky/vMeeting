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
                style="background: var(--bg-surface-subtle); border: 1px solid var(--border-color); border-radius: 10px; padding: 8px 14px; color: var(--text-primary); font-size: 13px; outline: none; width: 220px; box-shadow: var(--shadow-inset-3d); font-weight: 600;"
            >
            <button type="submit" class="tactile-btn btn-primary" style="padding: 8px 16px; font-size: 12px;">🔍 {{ __('Search') }}</button>
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
                        <strong style="color: var(--text-primary); font-size: 14px;">{{ $comp->name }}</strong>
                        <div style="font-size: 11px; color: var(--text-muted); font-family: monospace;">{{ $comp->slug }}</div>
                    </td>
                    <td>
                        <div style="font-weight: 800; color: var(--text-primary);">{{ $owner?->name ?? 'Administrator' }}</div>
                        <div style="font-size: 11px; color: var(--text-muted);">{{ $owner?->email }}</div>
                    </td>
                    <td>
                        <span class="badge-status badge-plan">
                            💎 {{ $comp->plan?->name ?? 'Free' }}
                        </span>
                        <div style="font-size: 11px; color: var(--text-secondary); margin-top: 2px; font-weight: 700;">
                            ${{ number_format($comp->plan?->price ?? 0, 2) }}/mo
                        </div>
                    </td>
                    <td>
                        <div style="font-weight: 800; color: {{ !$isUnlimited && $memberCount >= $seatLimit ? '#D96B5F' : 'var(--brand-forest)' }}; font-size: 13px;">
                            {{ $memberCount }} / {{ $isUnlimited ? '∞' : $seatLimit }}
                        </div>
                        <div style="font-size: 10px; color: var(--text-muted);">{{ __('Seats used') }}</div>
                    </td>
                    <td>
                        <span style="font-weight: 800; color: var(--text-secondary);">{{ $comp->rooms->count() }} {{ __('Rooms') }}</span>
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
                                class="tactile-btn btn-secondary"
                                style="padding: 6px 12px; font-size: 11px;"
                                title="{{ __('Change Plan') }}"
                            >
                                💎 {{ __('Change Plan') }}
                            </button>

                            <form method="POST" action="{{ route('superadmin.companies.toggle', $comp) }}" style="display: inline;">
                                @csrf
                                <button type="submit" class="tactile-btn" style="padding: 6px 12px; font-size: 11px; color: {{ $isSuspended ? 'var(--brand-forest)' : '#D96B5F' }};">
                                    {{ $isSuspended ? '▶️ ' . __('Activate') : '⏸️ ' . __('Suspend') }}
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" style="text-align: center; color: var(--text-muted); padding: 40px;">
                        {{ __('No organizations found matching search criteria.') }}
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
    <div class="modal-card" style="border-radius: 24px; padding: 26px;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <h3 style="font-size: 17px; font-weight: 900; color: var(--text-primary);" id="modalCompanyTitle">💎 {{ __('Change Subscription Plan') }}</h3>
            <button onclick="closeChangePlanModal()" style="background: var(--bg-surface-subtle); border: 1px solid var(--border-color); width: 32px; height: 32px; border-radius: 50%; display: flex; align-items: center; justify-content: center; cursor: pointer; color: var(--text-primary); font-weight: 800;">✕</button>
        </div>

        <form id="changePlanForm" method="POST" action="">
            @csrf
            <div style="margin-bottom: 22px;">
                <label style="display: block; font-size: 12px; font-weight: 800; color: var(--text-secondary); margin-bottom: 8px;">
                    {{ __('Select New Subscription Tier (Seats)') }}
                </label>
                <select name="plan_id" id="modalPlanSelect" style="width: 100%; background: var(--bg-surface-subtle); border: 1px solid var(--border-color); border-radius: 12px; padding: 12px; color: var(--text-primary); font-size: 13px; outline: none; font-weight: 700; box-shadow: var(--shadow-inset-3d);">
                    @foreach($plans as $plan)
                        <option value="{{ $plan->id }}">
                            💎 {{ $plan->name }} — {{ $plan->seat_limit === 0 ? 'Unlimited' : $plan->seat_limit }} Users (${{ number_format($plan->price, 2) }}/mo)
                        </option>
                    @endforeach
                </select>
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 10px;">
                <button type="button" onclick="closeChangePlanModal()" class="tactile-btn btn-secondary">{{ __('Cancel') }}</button>
                <button type="submit" class="tactile-btn btn-primary">
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
