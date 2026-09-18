@extends('superadmin.layout')

@section('title', __('Companies'))
@section('page_title', __('Companies'))

@section('content')
<div class="panel-card" style="background: var(--ula-surface-card); border: 1px solid var(--ula-border-subtle); border-radius: var(--ula-radius-xl); box-shadow: var(--ula-shadow-sm); overflow: hidden;">
    <div class="panel-header" style="padding: 20px 24px; border-bottom: 1px solid var(--ula-border-subtle); display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 14px;">
        <div class="panel-title" style="display: flex; align-items: center; gap: 10px; font-size: 16px; font-weight: 800; color: var(--ula-text-primary);">
            <span class="material-symbols-rounded" style="color: var(--ula-highlight-default); font-size: 22px;">domain</span>
            <span>{{ __('Registered Companies') }}</span>
            <span class="nav-badge-pill" style="font-size: 11px; padding: 2px 8px; background: rgba(20,43,36,0.06); color: var(--ula-text-primary); font-family: 'IBM Plex Mono', monospace;">{{ $companies->total() }}</span>
        </div>
        <form method="GET" action="{{ route('superadmin.companies') }}" style="display: flex; gap: 8px; margin: 0;">
            <div style="position: relative; display: flex; align-items: center;">
                <span class="material-symbols-rounded" style="position: absolute; inset-inline-start: 12px; font-size: 16px; color: var(--ula-text-muted); pointer-events: none;">search</span>
                <input
                    type="text"
                    name="search"
                    value="{{ request('search') }}"
                    placeholder="{{ __('Search by name or slug...') }}"
                    style="background: var(--ula-surface-page-alt); border: 1px solid var(--ula-border-subtle); border-radius: var(--ula-radius-pill); padding: 8px 14px; padding-inline-start: 36px; color: var(--ula-text-primary); font-size: 13px; outline: none; width: 240px; font-weight: 500;"
                >
            </div>
            <button type="submit" class="tactile-btn btn-primary" style="padding: 8px 18px; font-size: 12px; border-radius: var(--ula-radius-pill); display: inline-flex; align-items: center; gap: 6px;">
                <span class="material-symbols-rounded" style="font-size: 15px;">search</span>
                <span>{{ __('Search') }}</span>
            </button>
            @if(request('search'))
                <a href="{{ route('superadmin.companies') }}" class="tactile-btn btn-secondary" style="padding: 8px 12px; font-size: 12px; border-radius: var(--ula-radius-pill); display: inline-flex; align-items: center;" title="{{ __('Clear search') }}">
                    <span class="material-symbols-rounded" style="font-size: 15px;">close</span>
                </a>
            @endif
        </form>
    </div>

    <div class="data-table-container" style="overflow-x: auto;">
        <table class="data-table" style="width: 100%; border-collapse: collapse; text-align: start;">
            <thead>
                <tr>
                    <th style="padding: 12px 20px; font-size: 11px; font-weight: 700; color: var(--ula-text-muted); text-transform: uppercase; letter-spacing: 0.5px; border-bottom: 1px solid var(--ula-border-subtle); text-align: start;">{{ __('Company Name') }}</th>
                    <th style="padding: 12px 20px; font-size: 11px; font-weight: 700; color: var(--ula-text-muted); text-transform: uppercase; letter-spacing: 0.5px; border-bottom: 1px solid var(--ula-border-subtle); text-align: start;">{{ __('Owner') }}</th>
                    <th style="padding: 12px 20px; font-size: 11px; font-weight: 700; color: var(--ula-text-muted); text-transform: uppercase; letter-spacing: 0.5px; border-bottom: 1px solid var(--ula-border-subtle); text-align: start;">{{ __('Current Plan') }}</th>
                    <th style="padding: 12px 20px; font-size: 11px; font-weight: 700; color: var(--ula-text-muted); text-transform: uppercase; letter-spacing: 0.5px; border-bottom: 1px solid var(--ula-border-subtle); text-align: start;">{{ __('Seat Usage') }}</th>
                    <th style="padding: 12px 20px; font-size: 11px; font-weight: 700; color: var(--ula-text-muted); text-transform: uppercase; letter-spacing: 0.5px; border-bottom: 1px solid var(--ula-border-subtle); text-align: start;">{{ __('Rooms') }}</th>
                    <th style="padding: 12px 20px; font-size: 11px; font-weight: 700; color: var(--ula-text-muted); text-transform: uppercase; letter-spacing: 0.5px; border-bottom: 1px solid var(--ula-border-subtle); text-align: start;">{{ __('Status') }}</th>
                    <th style="padding: 12px 20px; font-size: 11px; font-weight: 700; color: var(--ula-text-muted); text-transform: uppercase; letter-spacing: 0.5px; border-bottom: 1px solid var(--ula-border-subtle); text-align: start;">{{ __('Actions') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($companies as $comp)
                @php
                    $seatLimit = $comp->plan?->seat_limit ?? 5;
                    $memberCount = $comp->members->count();
                    $isUnlimited = $seatLimit === 0;
                    $owner = $comp->members->first()?->user;
                    $isSuspended = $comp->status === 'suspended';
                @endphp
                <tr style="border-bottom: 1px solid var(--ula-border-subtle); transition: background 0.15s ease;">
                    <td style="padding: 14px 20px;">
                        <a href="{{ route('superadmin.companies.show', $comp) }}" style="text-decoration: none; display: block;" title="{{ __('View Full Company Profile') }}">
                            <strong style="color: var(--ula-text-primary); font-size: 14px; display: inline-flex; align-items: center; gap: 8px;">
                                <div style="width: 30px; height: 30px; border-radius: 8px; background: rgba(20,43,36,0.08); display: flex; align-items: center; justify-content: center; color: var(--ula-text-primary); font-size: 13px; font-weight: 700;">
                                    {{ strtoupper(substr($comp->name, 0, 2)) }}
                                </div>
                                <span>{{ $comp->name }}</span>
                            </strong>
                        </a>
                        <div style="font-size: 11px; color: var(--ula-text-muted); font-family: 'IBM Plex Mono', monospace; margin-inline-start: 38px;">{{ $comp->slug }}</div>
                    </td>
                    <td style="padding: 14px 20px;">
                        <div style="font-weight: 700; color: var(--ula-text-primary); font-size: 13px;">{{ $owner?->name ?? 'Administrator' }}</div>
                        <div style="font-size: 11px; color: var(--ula-text-muted); font-family: 'IBM Plex Mono', monospace;">{{ $owner?->email }}</div>
                    </td>
                    <td style="padding: 14px 20px;">
                        <span class="badge-status badge-plan" style="display: inline-flex; align-items: center; gap: 4px; font-size: 11px; padding: 3px 8px; border-radius: 6px; background: rgba(211,165,83,0.12); color: var(--ula-gold-600); font-weight: 700;">
                            <span class="material-symbols-rounded" style="font-size: 14px;">diamond</span>
                            <span>{{ $comp->plan?->name ?? 'Free' }}</span>
                        </span>
                        <div style="font-size: 11px; color: var(--ula-text-secondary); margin-top: 2px; font-weight: 600; font-family: 'IBM Plex Mono', monospace;">
                            ${{ number_format($comp->plan?->price ?? 0, 2) }}/mo
                        </div>
                    </td>
                    <td style="padding: 14px 20px;">
                        <div style="font-weight: 700; color: {{ !$isUnlimited && $memberCount >= $seatLimit ? 'var(--ula-status-danger)' : 'var(--ula-status-success)' }}; font-size: 13px; font-family: 'IBM Plex Mono', monospace;">
                            {{ $memberCount }} / {{ $isUnlimited ? '∞' : $seatLimit }}
                        </div>
                        <div style="font-size: 10px; color: var(--ula-text-muted);">{{ __('Seats used') }}</div>
                    </td>
                    <td style="padding: 14px 20px;">
                        <span style="font-weight: 700; color: var(--ula-text-secondary); font-size: 12px; font-family: 'IBM Plex Mono', monospace;">
                            {{ $comp->rooms->count() }} {{ __('Rooms') }}
                        </span>
                    </td>
                    <td style="padding: 14px 20px;">
                        @if($isSuspended)
                            <span class="badge-status badge-suspended" style="display: inline-flex; align-items: center; gap: 4px; font-size: 11px; padding: 3px 8px; border-radius: 6px; background: rgba(217,107,95,0.12); color: var(--ula-status-danger); font-weight: 700;">
                                <span class="material-symbols-rounded" style="font-size: 13px;">block</span>
                                <span>{{ __('Suspended') }}</span>
                            </span>
                        @else
                            <span class="badge-status badge-active" style="display: inline-flex; align-items: center; gap: 4px; font-size: 11px; padding: 3px 8px; border-radius: 6px; background: rgba(60,107,76,0.12); color: var(--ula-status-success); font-weight: 700;">
                                <span class="material-symbols-rounded" style="font-size: 13px;">check_circle</span>
                                <span>{{ __('Active') }}</span>
                            </span>
                        @endif
                    </td>
                    <td style="padding: 14px 20px;">
                        <div style="display: flex; gap: 6px; align-items: center;">
                            <!-- Details Page Button -->
                            <a href="{{ route('superadmin.companies.show', $comp) }}" class="tactile-btn btn-secondary" style="padding: 6px 12px; font-size: 11px; text-decoration: none; display: inline-flex; align-items: center; gap: 4px;" title="{{ __('View Details') }}">
                                <span class="material-symbols-rounded" style="font-size: 14px;">visibility</span>
                                <span>{{ __('Details') }}</span>
                            </a>

                            <!-- Impersonate Button -->
                            <form method="POST" action="{{ route('superadmin.companies.impersonate', $comp) }}" style="display: inline; margin: 0;">
                                @csrf
                                <button type="submit" class="tactile-btn" style="background: var(--ula-palm-900); color: white; border: none; padding: 6px 12px; font-size: 11px; font-weight: 700; display: inline-flex; align-items: center; gap: 4px;" title="{{ __('Log in as this company owner') }}">
                                    <span class="material-symbols-rounded" style="font-size: 14px;">bolt</span>
                                    <span>{{ __('Login') }}</span>
                                </button>
                            </form>

                            <button
                                onclick="openChangePlanModal('{{ $comp->id }}', '{{ $comp->name }}', '{{ $comp->plan_id }}')"
                                class="tactile-btn btn-secondary"
                                style="padding: 6px 10px; font-size: 11px; display: inline-flex; align-items: center; gap: 4px;"
                                title="{{ __('Change Plan') }}"
                            >
                                <span class="material-symbols-rounded" style="font-size: 14px;">diamond</span>
                                <span>{{ __('Plan') }}</span>
                            </button>

                            <form method="POST" action="{{ route('superadmin.companies.toggle', $comp) }}" style="display: inline; margin: 0;">
                                @csrf
                                <button type="submit" class="tactile-btn" style="padding: 6px 10px; font-size: 11px; color: {{ $isSuspended ? 'var(--ula-status-success)' : 'var(--ula-status-danger)' }};" title="{{ $isSuspended ? __('Activate Company') : __('Suspend Company') }}">
                                    <span class="material-symbols-rounded" style="font-size: 15px;">{{ $isSuspended ? 'play_arrow' : 'pause' }}</span>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" style="text-align: center; color: var(--ula-text-muted); padding: 48px;">
                        <span class="material-symbols-rounded" style="font-size: 36px; display: block; margin-bottom: 8px; opacity: 0.5;">domain_disabled</span>
                        {{ __('No organizations found matching search criteria.') }}
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($companies->hasPages())
    <div style="padding: 16px 24px; border-top: 1px solid var(--ula-border-subtle);">
        {{ $companies->links() }}
    </div>
    @endif
</div>

<!-- Change Plan Modal -->
<div id="changePlanModal" class="modal-overlay">
    <div class="modal-card" style="border-radius: var(--ula-radius-xl); padding: 26px; max-width: 480px; width: 100%;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <div style="display: flex; align-items: center; gap: 8px;">
                <span class="material-symbols-rounded" style="color: var(--ula-highlight-default); font-size: 22px;">diamond</span>
                <h3 style="font-size: 16px; font-weight: 800; color: var(--ula-text-primary); margin: 0;" id="modalCompanyTitle">{{ __('Change Subscription Plan') }}</h3>
            </div>
            <button onclick="closeChangePlanModal()" style="background: var(--ula-surface-page-alt); border: 1px solid var(--ula-border-subtle); width: 32px; height: 32px; border-radius: 50%; display: flex; align-items: center; justify-content: center; cursor: pointer; color: var(--ula-text-primary);">
                <span class="material-symbols-rounded" style="font-size: 16px;">close</span>
            </button>
        </div>

        <form id="changePlanForm" method="POST" action="">
            @csrf
            <div style="margin-bottom: 22px;">
                <label style="display: block; font-size: 12px; font-weight: 700; color: var(--ula-text-secondary); margin-bottom: 8px;">
                    {{ __('Select New Subscription Tier (Seats)') }}
                </label>
                <select name="plan_id" id="modalPlanSelect" style="width: 100%; background: var(--ula-surface-page-alt); border: 1px solid var(--ula-border-subtle); border-radius: 12px; padding: 12px; color: var(--ula-text-primary); font-size: 13px; outline: none; font-weight: 600;">
                    @foreach($plans as $plan)
                        <option value="{{ $plan->id }}">
                            {{ $plan->name }} — {{ $plan->seat_limit === 0 ? 'Unlimited' : $plan->seat_limit }} Users (${{ number_format($plan->price, 2) }}/mo)
                        </option>
                    @endforeach
                </select>
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 10px;">
                <button type="button" onclick="closeChangePlanModal()" class="tactile-btn btn-secondary">{{ __('Cancel') }}</button>
                <button type="submit" class="tactile-btn btn-primary" style="display: inline-flex; align-items: center; gap: 6px;">
                    <span class="material-symbols-rounded" style="font-size: 16px;">save</span>
                    <span>{{ __('Save Changes') }}</span>
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script nonce="{{ $cspNonce ?? '' }}">
    function openChangePlanModal(orgId, orgName, currentPlanId) {
        document.getElementById('modalCompanyTitle').textContent = `Change Plan — ${orgName}`;
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
