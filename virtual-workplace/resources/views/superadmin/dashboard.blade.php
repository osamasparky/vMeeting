@extends('superadmin.layout')

@section('title', __('Dashboard'))
@section('page_title', __('Dashboard'))

@section('content')
<!-- KPI Metric Cards -->
<div class="kpi-grid">
    <div class="kpi-card">
        <div class="kpi-icon" style="background: rgba(0, 180, 179, 0.12); color: var(--brand-teal);">🏢</div>
        <div class="kpi-info">
            <h3>{{ __('Total Companies') }}</h3>
            <div class="kpi-value">{{ $stats['total_companies'] }}</div>
        </div>
    </div>

    <div class="kpi-card">
        <div class="kpi-icon" style="background: rgba(0, 104, 71, 0.12); color: var(--brand-green);">💎</div>
        <div class="kpi-info">
            <h3>{{ __('Active Subscriptions') }}</h3>
            <div class="kpi-value">{{ $stats['active_subscriptions'] }}</div>
        </div>
    </div>

    <div class="kpi-card">
        <div class="kpi-icon" style="background: rgba(0, 72, 98, 0.12); color: var(--brand-ocean);">👥</div>
        <div class="kpi-info">
            <h3>{{ __('Total Users') }}</h3>
            <div class="kpi-value">{{ $stats['total_users'] }}</div>
        </div>
    </div>

    <div class="kpi-card">
        <div class="kpi-icon" style="background: rgba(245, 123, 54, 0.12); color: var(--brand-orange);">💵</div>
        <div class="kpi-info">
            <h3>{{ __('Estimated MRR') }}</h3>
            <div class="kpi-value">${{ number_format($stats['estimated_mrr'], 2) }}</div>
        </div>
    </div>
</div>

<!-- Recent Companies & Quick Actions -->
<div class="panel-card">
    <div class="panel-header">
        <div class="panel-title">
            <span>🏢</span>
            <span>{{ __('Registered Companies') }}</span>
        </div>
        <a href="{{ route('superadmin.companies') }}" class="btn-action">
            <span>{{ __('View All') }}</span>
            <span>→</span>
        </a>
    </div>

    <div class="data-table-container">
        <table class="data-table">
            <thead>
                <tr>
                    <th>{{ __('Company Name') }}</th>
                    <th>{{ __('Owner') }}</th>
                    <th>{{ __('Current Plan') }}</th>
                    <th>{{ __('Seat Usage') }}</th>
                    <th>{{ __('Status') }}</th>
                    <th>{{ __('Actions') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($recentCompanies as $comp)
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
                        <div style="font-size: 11px; color: var(--text-muted);">{{ $comp->slug }}</div>
                    </td>
                    <td>
                        <div style="font-weight: 700; color: var(--text-primary);">{{ $owner?->name ?? 'Administrator' }}</div>
                        <div style="font-size: 11px; color: var(--text-muted);">{{ $owner?->email }}</div>
                    </td>
                    <td>
                        <span class="badge-status badge-plan">
                            💎 {{ $comp->plan?->name ?? 'Free' }} (${{ number_format($comp->plan?->price ?? 0, 2) }}/mo)
                        </span>
                    </td>
                    <td>
                        <div style="font-weight: 800; color: {{ !$isUnlimited && $memberCount >= $seatLimit ? 'var(--brand-crimson)' : 'var(--brand-green)' }};">
                            {{ $memberCount }} / {{ $isUnlimited ? '∞' : $seatLimit }} {{ __('Seats') }}
                        </div>
                    </td>
                    <td>
                        @if($isSuspended)
                            <span class="badge-status badge-suspended">🛑 {{ __('Suspended') }}</span>
                        @else
                            <span class="badge-status badge-active">✅ {{ __('Active') }}</span>
                        @endif
                    </td>
                    <td>
                        <a href="{{ route('superadmin.companies') }}" class="btn-action">
                            ⚙️ {{ __('Manage') }}
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" style="text-align: center; color: var(--text-muted); padding: 32px;">
                        No organizations registered yet.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
