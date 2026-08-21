@extends('superadmin.layout')

@section('title', __('Dashboard'))
@section('page_title', __('Dashboard'))

@section('content')
<!-- Header Welcome & Live Health Status -->
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; flex-wrap: wrap; gap: 14px;">
    <div>
        <h2 style="font-size: 22px; font-weight: 900; color: var(--text-primary); margin-bottom: 4px;">
            ⚡ {{ __('Platform Overview & SaaS Metrics') }}
        </h2>
        <p style="font-size: 13px; color: var(--text-secondary);">
            {{ __('Real-time multi-tenant health, subscription revenues, and spatial collaboration indicators.') }}
        </p>
    </div>
    <div style="display: flex; align-items: center; gap: 10px;">
        <span class="badge-status badge-active" style="padding: 6px 14px; font-size: 12px;">
            🟢 {{ __('System Normal & All Nodes Live') }}
        </span>
        <a href="{{ route('superadmin.companies') }}" class="tactile-btn btn-primary" style="padding: 8px 16px; font-size: 12px;">
            🏢 {{ __('Manage Companies') }}
        </a>
    </div>
</div>

<!-- Primary SaaS Growth & Revenue Metrics (Tier 1 KPI) -->
<div class="kpi-grid" style="grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 18px; margin-bottom: 20px;">
    <!-- Total Companies -->
    <div class="kpi-card" style="border-radius: var(--radius-xl); padding: 22px; position: relative; overflow: hidden;">
        <div class="kpi-icon" style="background: rgba(36, 92, 58, 0.12); color: var(--brand-forest); font-size: 24px;">🏢</div>
        <div class="kpi-info" style="flex: 1;">
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <h3>{{ __('Total Companies') }}</h3>
                <span class="nav-badge-pill" style="font-size: 10px; color: var(--brand-forest);">
                    +{{ $stats['new_companies_month'] }} {{ __('this mo') }}
                </span>
            </div>
            <div class="kpi-value" style="margin: 4px 0;">{{ $stats['total_companies'] }}</div>
            <div style="font-size: 11px; color: var(--text-muted); display: flex; gap: 8px;">
                <span style="color: var(--brand-forest); font-weight: 700;">✅ {{ $stats['active_companies'] }} {{ __('Active') }}</span>
                @if($stats['suspended_companies'] > 0)
                    <span style="color: #D96B5F; font-weight: 700;">🛑 {{ $stats['suspended_companies'] }} {{ __('Suspended') }}</span>
                @endif
            </div>
        </div>
    </div>

    <!-- Total Users -->
    <div class="kpi-card" style="border-radius: var(--radius-xl); padding: 22px; position: relative; overflow: hidden;">
        <div class="kpi-icon" style="background: rgba(79, 155, 95, 0.12); color: var(--brand-emerald); font-size: 24px;">👥</div>
        <div class="kpi-info" style="flex: 1;">
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <h3>{{ __('Total Users') }}</h3>
                <span class="nav-badge-pill" style="font-size: 10px; color: var(--brand-forest);">
                    +{{ $stats['new_users_month'] }} {{ __('new') }}
                </span>
            </div>
            <div class="kpi-value" style="margin: 4px 0;">{{ $stats['total_users'] }}</div>
            <div style="font-size: 11px; color: var(--text-muted); font-weight: 700;">
                {{ $stats['total_companies'] > 0 ? round($stats['total_users'] / $stats['total_companies'], 1) : 0 }} {{ __('avg users / tenant') }}
            </div>
        </div>
    </div>

    <!-- Active Subscriptions -->
    <div class="kpi-card" style="border-radius: var(--radius-xl); padding: 22px; position: relative; overflow: hidden;">
        <div class="kpi-icon" style="background: rgba(214, 162, 58, 0.12); color: #D6A23A; font-size: 24px;">💎</div>
        <div class="kpi-info" style="flex: 1;">
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <h3>{{ __('Paid Subscriptions') }}</h3>
                <span class="nav-badge-pill" style="font-size: 10px; color: #D6A23A;">
                    {{ $stats['conversion_rate'] }}% {{ __('Paid') }}
                </span>
            </div>
            <div class="kpi-value" style="margin: 4px 0;">{{ $stats['active_subscriptions'] }}</div>
            <div style="font-size: 11px; color: var(--text-muted); font-weight: 700;">
                {{ $stats['total_companies'] - $stats['active_subscriptions'] }} {{ __('Free / Starter tier') }}
            </div>
        </div>
    </div>

    <!-- Monthly Recurring Revenue (MRR) -->
    <div class="kpi-card" style="border-radius: var(--radius-xl); padding: 22px; position: relative; overflow: hidden; border-inline-start: 4px solid var(--brand-forest);">
        <div class="kpi-icon" style="background: rgba(36, 92, 58, 0.15); color: var(--brand-forest); font-size: 24px;">💵</div>
        <div class="kpi-info" style="flex: 1;">
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <h3>{{ __('Estimated MRR') }}</h3>
                <span class="nav-badge-pill" style="font-size: 10px; color: var(--brand-forest);">
                    ${{ number_format($stats['estimated_arr'], 0) }} {{ __('ARR') }}
                </span>
            </div>
            <div class="kpi-value" style="margin: 4px 0; color: var(--brand-forest);">
                ${{ number_format($stats['estimated_mrr'], 2) }}
            </div>
            <div style="font-size: 11px; color: var(--text-secondary); font-weight: 700;">
                ≈ {{ number_format($stats['estimated_mrr_sar'], 2) }} SAR / {{ __('month') }}
            </div>
        </div>
    </div>
</div>

<!-- Secondary Platform Activity & Spatial Health KPI Grid (Tier 2 KPI) -->
<div class="kpi-grid" style="grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 16px; margin-bottom: 28px;">
    <div class="kpi-card" style="padding: 16px 18px; border-radius: var(--radius-lg);">
        <div class="kpi-icon" style="width: 40px; height: 40px; font-size: 18px;">🚪</div>
        <div class="kpi-info">
            <h3 style="font-size: 10px;">{{ __('Meeting Rooms') }}</h3>
            <div class="kpi-value" style="font-size: 19px;">{{ $stats['total_rooms'] }}</div>
        </div>
    </div>

    <div class="kpi-card" style="padding: 16px 18px; border-radius: var(--radius-lg);">
        <div class="kpi-icon" style="width: 40px; height: 40px; font-size: 18px;">📁</div>
        <div class="kpi-info">
            <h3 style="font-size: 10px;">{{ __('Total Projects') }}</h3>
            <div class="kpi-value" style="font-size: 19px;">{{ $stats['total_projects'] }}</div>
        </div>
    </div>

    <div class="kpi-card" style="padding: 16px 18px; border-radius: var(--radius-lg);">
        <div class="kpi-icon" style="width: 40px; height: 40px; font-size: 18px;">⏱️</div>
        <div class="kpi-info">
            <h3 style="font-size: 10px;">{{ __('Logged Hours') }}</h3>
            <div class="kpi-value" style="font-size: 19px;">{{ $stats['total_logged_hours'] }}h</div>
        </div>
    </div>

    <div class="kpi-card" style="padding: 16px 18px; border-radius: var(--radius-lg);">
        <div class="kpi-icon" style="width: 40px; height: 40px; font-size: 18px;">🛡️</div>
        <div class="kpi-info">
            <h3 style="font-size: 10px;">{{ __('Audit Events') }}</h3>
            <div class="kpi-value" style="font-size: 19px;">{{ $stats['total_audit_events'] }}</div>
        </div>
    </div>
</div>

<!-- Plan Distribution & Live Platform Activity -->
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(360px, 1fr)); gap: 24px; margin-bottom: 28px;">
    <!-- Subscription Plan Distribution -->
    <div class="panel-card" style="margin-bottom: 0; display: flex; flex-direction: column; justify-content: space-between;">
        <div>
            <div class="panel-header" style="margin-bottom: 16px; padding-bottom: 12px;">
                <div class="panel-title">
                    <span>💎</span>
                    <span>{{ __('Plan Tiers Distribution') }}</span>
                </div>
                <a href="{{ route('superadmin.plans') }}" class="tactile-btn btn-secondary" style="padding: 6px 12px; font-size: 11px;">
                    {{ __('Manage Plans') }}
                </a>
            </div>

            <div style="display: flex; flex-direction: column; gap: 14px;">
                @foreach($plans as $plan)
                @php
                    $percentage = $stats['total_companies'] > 0 ? round(($plan->organizations_count / $stats['total_companies']) * 100, 1) : 0;
                @endphp
                <div>
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 6px; font-size: 13px;">
                        <span style="font-weight: 800; color: var(--text-primary);">
                            💎 {{ $plan->name }} (${{ number_format($plan->price, 0) }}/mo)
                        </span>
                        <div style="display: flex; align-items: center; gap: 6px;">
                            <span style="font-weight: 900; color: var(--brand-forest);">{{ $plan->organizations_count }}</span>
                            <span style="font-size: 11px; color: var(--text-muted);">({{ $percentage }}%)</span>
                        </div>
                    </div>
                    <div style="width: 100%; height: 8px; background: var(--bg-surface-subtle); border-radius: 9999px; overflow: hidden; border: 1px solid var(--border-color);">
                        <div style="width: {{ max($percentage, 3) }}%; height: 100%; background: var(--accent-gradient); border-radius: 9999px; transition: width 0.4s ease;"></div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>

        <div style="margin-top: 20px; padding-top: 14px; border-top: 1px solid var(--border-color); display: flex; justify-content: space-between; align-items: center; font-size: 12px;">
            <span style="color: var(--text-secondary); font-weight: 700;">{{ __('Total Active Tenants') }}:</span>
            <strong style="color: var(--brand-forest); font-size: 14px;">{{ $stats['total_companies'] }} {{ __('Organizations') }}</strong>
        </div>
    </div>

    <!-- Live Platform Activity Logs -->
    <div class="panel-card" style="margin-bottom: 0;">
        <div class="panel-header" style="margin-bottom: 16px; padding-bottom: 12px;">
            <div class="panel-title">
                <span>🛡️</span>
                <span>{{ __('Live Security & Audit Trail') }}</span>
            </div>
            <span class="nav-badge-pill" style="font-size: 10px;">{{ __('Latest Events') }}</span>
        </div>

        <div style="display: flex; flex-direction: column; gap: 10px;">
            @forelse($recentAuditLogs as $log)
            <div style="display: flex; align-items: center; justify-content: space-between; padding: 10px 14px; background: var(--bg-surface-subtle); border-radius: var(--radius-md); border: 1px solid var(--border-color); font-size: 12px;">
                <div style="display: flex; align-items: center; gap: 10px;">
                    <span style="font-size: 16px;">⚡</span>
                    <div>
                        <div style="font-weight: 800; color: var(--text-primary);">
                            {{ $log->actor?->name ?? 'System' }}
                            <span style="font-weight: 600; color: var(--text-muted); font-size: 11px;">
                                ({{ $log->action }})
                            </span>
                        </div>
                        <div style="font-size: 10px; color: var(--text-muted);">
                            {{ $log->organization?->name ?? 'Global Platform' }}
                        </div>
                    </div>
                </div>
                <div style="font-size: 10px; color: var(--text-dim); font-weight: 700; font-family: monospace;">
                    {{ $log->created_at?->diffForHumans() }}
                </div>
            </div>
            @empty
            <div style="text-align: center; color: var(--text-muted); padding: 24px; font-size: 13px;">
                {{ __('No recent audit logs recorded.') }}
            </div>
            @endforelse
        </div>
    </div>
</div>

<!-- Recent Companies & Tenant Directory -->
<div class="panel-card">
    <div class="panel-header">
        <div class="panel-title">
            <span>🏢</span>
            <span>{{ __('Recent Registered Organizations') }}</span>
        </div>
        <a href="{{ route('superadmin.companies') }}" class="tactile-btn btn-primary" style="font-size: 12px; padding: 8px 16px;">
            <span>{{ __('View All Companies') }}</span>
            <span>→</span>
        </a>
    </div>

    <div class="data-table-container">
        <table class="data-table">
            <thead>
                <tr>
                    <th>{{ __('Company Name') }}</th>
                    <th>{{ __('Owner / Admin') }}</th>
                    <th>{{ __('Current Plan') }}</th>
                    <th>{{ __('Seat Usage') }}</th>
                    <th>{{ __('Rooms') }}</th>
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
                    $isSuspended = $comp->status === 'suspended';
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
                            💎 {{ $comp->plan?->name ?? 'Free' }} (${{ number_format($comp->plan?->price ?? 0, 2) }}/mo)
                        </span>
                    </td>
                    <td>
                        <div style="font-weight: 800; color: {{ !$isUnlimited && $memberCount >= $seatLimit ? '#D96B5F' : 'var(--brand-forest)' }};">
                            {{ $memberCount }} / {{ $isUnlimited ? '∞' : $seatLimit }} {{ __('Seats') }}
                        </div>
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
                        <a href="{{ route('superadmin.companies') }}" class="tactile-btn btn-secondary" style="padding: 6px 12px; font-size: 11px;">
                            ⚙️ {{ __('Manage') }}
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" style="text-align: center; color: var(--text-muted); padding: 40px;">
                        {{ __('No organizations registered yet.') }}
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
