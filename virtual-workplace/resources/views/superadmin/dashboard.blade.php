@extends('superadmin.layout')

@section('title', __('Dashboard'))
@section('page_title', __('Dashboard'))

@section('content')
<!-- Header Welcome & Live Health Status -->
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; flex-wrap: wrap; gap: 14px;">
    <div>
        <h2 style="font-size: 22px; font-weight: 800; color: var(--text-primary); margin-bottom: 4px; display: flex; align-items: center; gap: 8px;">
            <span class="material-symbols-rounded" style="color: var(--nx-palm-500, #1E412F); font-size: 26px;">analytics</span>
            <span>{{ __('Platform Overview & SaaS Metrics') }}</span>
        </h2>
        <p style="font-size: 13px; color: var(--text-secondary);">
            {{ __('Real-time multi-tenant health, subscription revenues, and spatial collaboration indicators.') }}
        </p>
    </div>
    <div style="display: flex; align-items: center; gap: 10px;">
        <span class="badge-status badge-active" style="padding: 6px 14px; font-size: 12px; display: inline-flex; align-items: center; gap: 6px;">
            <span class="material-symbols-rounded" style="font-size: 16px; color: var(--status-success);">check_circle</span>
            <span>{{ __('System Normal & All Nodes Live') }}</span>
        </span>
        <a href="{{ route('superadmin.companies') }}" class="tactile-btn btn-primary" style="padding: 8px 16px; font-size: 12px; display: inline-flex; align-items: center; gap: 6px; text-decoration: none;">
            <span class="material-symbols-rounded" style="font-size: 16px;">domain</span>
            <span>{{ __('Manage Companies') }}</span>
        </a>
    </div>
</div>

<!-- Primary SaaS Growth & Revenue Metrics (Tier 1 KPI) -->
<div class="kpi-grid" style="grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 18px; margin-bottom: 20px;">
    <!-- Total Companies -->
    <div class="kpi-card" style="border-radius: var(--radius-xl); padding: 22px; position: relative; overflow: hidden;">
        <div class="kpi-icon" style="background: rgba(30, 65, 47, 0.12); color: var(--brand-forest); font-size: 22px; display: flex; align-items: center; justify-content: center;">
            <span class="material-symbols-rounded">domain</span>
        </div>
        <div class="kpi-info" style="flex: 1;">
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <h3>{{ __('Total Companies') }}</h3>
                <span class="nav-badge-pill" style="font-size: 10px; color: var(--brand-forest); font-family: var(--font-mono);">
                    +{{ $stats['new_companies_month'] }} {{ __('this mo') }}
                </span>
            </div>
            <div class="kpi-value" style="margin: 4px 0; font-family: var(--font-mono);">{{ $stats['total_companies'] }}</div>
            <div style="font-size: 11px; color: var(--text-muted); display: flex; gap: 8px;">
                <span style="color: var(--status-success); font-weight: 700; display: inline-flex; align-items: center; gap: 3px;">
                    <span class="material-symbols-rounded" style="font-size: 14px;">check_circle</span>
                    <span>{{ $stats['active_companies'] }} {{ __('Active') }}</span>
                </span>
                @if($stats['suspended_companies'] > 0)
                    <span style="color: var(--status-danger); font-weight: 700; display: inline-flex; align-items: center; gap: 3px;">
                        <span class="material-symbols-rounded" style="font-size: 14px;">block</span>
                        <span>{{ $stats['suspended_companies'] }} {{ __('Suspended') }}</span>
                    </span>
                @endif
            </div>
        </div>
    </div>

    <!-- Total Users -->
    <div class="kpi-card" style="border-radius: var(--radius-xl); padding: 22px; position: relative; overflow: hidden;">
        <div class="kpi-icon" style="background: rgba(60, 107, 76, 0.12); color: var(--brand-emerald); font-size: 22px; display: flex; align-items: center; justify-content: center;">
            <span class="material-symbols-rounded">group</span>
        </div>
        <div class="kpi-info" style="flex: 1;">
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <h3>{{ __('Total Users') }}</h3>
                <span class="nav-badge-pill" style="font-size: 10px; color: var(--brand-forest); font-family: var(--font-mono);">
                    +{{ $stats['new_users_month'] }} {{ __('new') }}
                </span>
            </div>
            <div class="kpi-value" style="margin: 4px 0; font-family: var(--font-mono);">{{ $stats['total_users'] }}</div>
            <div style="font-size: 11px; color: var(--text-muted); font-weight: 700;">
                <span style="font-family: var(--font-mono);">{{ $stats['total_companies'] > 0 ? round($stats['total_users'] / $stats['total_companies'], 1) : 0 }}</span> {{ __('avg users / tenant') }}
            </div>
        </div>
    </div>

    <!-- Active Subscriptions -->
    <div class="kpi-card" style="border-radius: var(--radius-xl); padding: 22px; position: relative; overflow: hidden;">
        <div class="kpi-icon" style="background: rgba(211, 165, 83, 0.15); color: #D3A553; font-size: 22px; display: flex; align-items: center; justify-content: center;">
            <span class="material-symbols-rounded">workspace_premium</span>
        </div>
        <div class="kpi-info" style="flex: 1;">
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <h3>{{ __('Paid Subscriptions') }}</h3>
                <span class="nav-badge-pill" style="font-size: 10px; color: #D3A553; font-family: var(--font-mono);">
                    {{ $stats['conversion_rate'] }}% {{ __('Paid') }}
                </span>
            </div>
            <div class="kpi-value" style="margin: 4px 0; font-family: var(--font-mono);">{{ $stats['active_subscriptions'] }}</div>
            <div style="font-size: 11px; color: var(--text-muted); font-weight: 700;">
                <span style="font-family: var(--font-mono);">{{ $stats['total_companies'] - $stats['active_subscriptions'] }}</span> {{ __('Free / Starter tier') }}
            </div>
        </div>
    </div>

    <!-- Monthly Recurring Revenue (MRR) -->
    <div class="kpi-card" style="border-radius: var(--radius-xl); padding: 22px; position: relative; overflow: hidden; border-inline-start: 4px solid var(--brand-forest);">
        <div class="kpi-icon" style="background: rgba(30, 65, 47, 0.15); color: var(--brand-forest); font-size: 22px; display: flex; align-items: center; justify-content: center;">
            <span class="material-symbols-rounded">payments</span>
        </div>
        <div class="kpi-info" style="flex: 1;">
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <h3>{{ __('Estimated MRR') }}</h3>
                <span class="nav-badge-pill" style="font-size: 10px; color: var(--brand-forest); font-family: var(--font-mono);">
                    ${{ number_format($stats['estimated_arr'], 0) }} {{ __('ARR') }}
                </span>
            </div>
            <div class="kpi-value" style="margin: 4px 0; color: var(--brand-forest); font-family: var(--font-mono);">
                ${{ number_format($stats['estimated_mrr'], 2) }}
            </div>
            <div style="font-size: 11px; color: var(--text-secondary); font-weight: 700;">
                ≈ <span style="font-family: var(--font-mono);">{{ number_format($stats['estimated_mrr_sar'], 2) }}</span> SAR / {{ __('month') }}
            </div>
        </div>
    </div>
</div>

<!-- Secondary Platform Activity & Spatial Health KPI Grid (Tier 2 KPI) -->
<div class="kpi-grid" style="grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 16px; margin-bottom: 28px;">
    <div class="kpi-card" style="padding: 16px 18px; border-radius: var(--radius-lg);">
        <div class="kpi-icon" style="width: 40px; height: 40px; font-size: 18px; display: flex; align-items: center; justify-content: center;">
            <span class="material-symbols-rounded">door_front</span>
        </div>
        <div class="kpi-info">
            <h3 style="font-size: 11px;">{{ __('Meeting Rooms') }}</h3>
            <div class="kpi-value" style="font-size: 19px; font-family: var(--font-mono);">{{ $stats['total_rooms'] }}</div>
        </div>
    </div>

    <div class="kpi-card" style="padding: 16px 18px; border-radius: var(--radius-lg);">
        <div class="kpi-icon" style="width: 40px; height: 40px; font-size: 18px; display: flex; align-items: center; justify-content: center;">
            <span class="material-symbols-rounded">folder</span>
        </div>
        <div class="kpi-info">
            <h3 style="font-size: 11px;">{{ __('Total Projects') }}</h3>
            <div class="kpi-value" style="font-size: 19px; font-family: var(--font-mono);">{{ $stats['total_projects'] }}</div>
        </div>
    </div>

    <div class="kpi-card" style="padding: 16px 18px; border-radius: var(--radius-lg);">
        <div class="kpi-icon" style="width: 40px; height: 40px; font-size: 18px; display: flex; align-items: center; justify-content: center;">
            <span class="material-symbols-rounded">schedule</span>
        </div>
        <div class="kpi-info">
            <h3 style="font-size: 11px;">{{ __('Logged Hours') }}</h3>
            <div class="kpi-value" style="font-size: 19px; font-family: var(--font-mono);">{{ $stats['total_logged_hours'] }}h</div>
        </div>
    </div>

    <div class="kpi-card" style="padding: 16px 18px; border-radius: var(--radius-lg);">
        <div class="kpi-icon" style="width: 40px; height: 40px; font-size: 18px; display: flex; align-items: center; justify-content: center;">
            <span class="material-symbols-rounded">security</span>
        </div>
        <div class="kpi-info">
            <h3 style="font-size: 11px;">{{ __('Audit Events') }}</h3>
            <div class="kpi-value" style="font-size: 19px; font-family: var(--font-mono);">{{ $stats['total_audit_events'] }}</div>
        </div>
    </div>
</div>

<!-- Plan Distribution & Live Platform Activity -->
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(360px, 1fr)); gap: 24px; margin-bottom: 28px;">
    <!-- Subscription Plan Distribution -->
    <div class="panel-card" style="margin-bottom: 0; display: flex; flex-direction: column; justify-content: space-between;">
        <div>
            <div class="panel-header" style="margin-bottom: 16px; padding-bottom: 12px;">
                <div class="panel-title" style="display: flex; align-items: center; gap: 8px;">
                    <span class="material-symbols-rounded" style="color: #D3A553;">workspace_premium</span>
                    <span>{{ __('Plan Tiers Distribution') }}</span>
                </div>
                <a href="{{ route('superadmin.plans') }}" class="tactile-btn btn-secondary" style="padding: 6px 12px; font-size: 11px; text-decoration: none;">
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
                        <span style="font-weight: 700; color: var(--text-primary); display: inline-flex; align-items: center; gap: 6px;">
                            <span class="material-symbols-rounded" style="font-size: 16px; color: var(--nx-palm-500, #1E412F);">verified</span>
                            <span>{{ $plan->name }} (<span style="font-family: var(--font-mono);">${{ number_format($plan->price, 0) }}/mo</span>)</span>
                        </span>
                        <div style="display: flex; align-items: center; gap: 6px;">
                            <span style="font-weight: 800; color: var(--brand-forest); font-family: var(--font-mono);">{{ $plan->organizations_count }}</span>
                            <span style="font-size: 11px; color: var(--text-muted); font-family: var(--font-mono);">({{ $percentage }}%)</span>
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
            <span style="color: var(--text-secondary); font-weight: 600;">{{ __('Total Active Tenants') }}:</span>
            <strong style="color: var(--brand-forest); font-size: 14px; font-family: var(--font-mono);">{{ $stats['total_companies'] }} {{ __('Organizations') }}</strong>
        </div>
    </div>

    <!-- Live Platform Activity Logs -->
    <div class="panel-card" style="margin-bottom: 0;">
        <div class="panel-header" style="margin-bottom: 16px; padding-bottom: 12px;">
            <div class="panel-title" style="display: flex; align-items: center; gap: 8px;">
                <span class="material-symbols-rounded" style="color: var(--brand-forest);">security</span>
                <span>{{ __('Live Security & Audit Trail') }}</span>
            </div>
            <span class="nav-badge-pill" style="font-size: 10px;">{{ __('Latest Events') }}</span>
        </div>

        <div style="display: flex; flex-direction: column; gap: 10px;">
            @forelse($recentAuditLogs as $log)
            <div style="display: flex; align-items: center; justify-content: space-between; padding: 10px 14px; background: var(--bg-surface-subtle); border-radius: var(--radius-md); border: 1px solid var(--border-color); font-size: 12px;">
                <div style="display: flex; align-items: center; gap: 10px;">
                    <span class="material-symbols-rounded" style="font-size: 18px; color: var(--brand-forest);">bolt</span>
                    <div>
                        <div style="font-weight: 700; color: var(--text-primary);">
                            {{ $log->actor?->name ?? 'System' }}
                            <span style="font-weight: 500; color: var(--text-muted); font-size: 11px;">
                                ({{ $log->action }})
                            </span>
                        </div>
                        <div style="font-size: 10px; color: var(--text-muted);">
                            {{ $log->organization?->name ?? 'Global Platform' }}
                        </div>
                    </div>
                </div>
                <div style="font-size: 10px; color: var(--text-dim); font-weight: 600; font-family: var(--font-mono);">
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

<!-- Pending Subscription Approvals Alert Panel -->
@if(isset($pendingSubscriptionRequests) && $pendingSubscriptionRequests->count() > 0)
<div class="panel-card" style="border: 2px solid #D3A553; background: var(--bg-surface); margin-bottom: 28px;">
    <div class="panel-header" style="border-bottom: 1px solid rgba(211, 165, 83, 0.3); padding-bottom: 14px; margin-bottom: 16px;">
        <div class="panel-title" style="color: #D3A553; display: flex; align-items: center; gap: 8px;">
            <span class="material-symbols-rounded">hourglass_top</span>
            <span>{{ __('Pending Subscription Approvals') }} ({{ $stats['pending_subscriptions_count'] ?? $pendingSubscriptionRequests->count() }})</span>
        </div>
        <a href="{{ route('superadmin.subscriptions', ['status' => 'pending']) }}" class="tactile-btn" style="font-size: 12px; padding: 6px 14px; background: #D3A553; color: white; border: 1px solid #B4831B; text-decoration: none; display: inline-flex; align-items: center; gap: 6px;">
            <span>{{ __('Review All Requests') }}</span>
            <span class="material-symbols-rounded" style="font-size: 14px;">arrow_forward</span>
        </a>
    </div>

    <div class="data-table-container">
        <table class="data-table">
            <thead>
                <tr>
                    <th>{{ __('Company') }}</th>
                    <th>{{ __('Target Plan') }}</th>
                    <th>{{ __('Amount') }}</th>
                    <th>{{ __('Bank & Reference') }}</th>
                    <th>{{ __('Receipt Slip') }}</th>
                    <th>{{ __('Submitted') }}</th>
                    <th>{{ __('Quick Action') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach($pendingSubscriptionRequests as $pReq)
                <tr>
                    <td>
                        <div style="display: flex; align-items: center; gap: 6px;">
                            <span class="material-symbols-rounded" style="font-size: 16px; color: var(--text-secondary);">domain</span>
                            <strong style="color: var(--text-primary);">{{ $pReq->organization?->name ?? 'Company' }}</strong>
                        </div>
                        <div style="font-size: 11px; color: var(--text-muted); display: flex; align-items: center; gap: 4px; margin-top: 2px;">
                            <span class="material-symbols-rounded" style="font-size: 13px;">person</span>
                            <span>{{ $pReq->sender_name }}</span>
                        </div>
                    </td>
                    <td>
                        <span class="badge-status badge-plan" style="display: inline-flex; align-items: center; gap: 4px;">
                            <span class="material-symbols-rounded" style="font-size: 14px;">workspace_premium</span>
                            <span>{{ $pReq->plan?->name ?? 'Plan' }}</span>
                        </span>
                    </td>
                    <td>
                        <strong style="font-family: var(--font-mono);">{{ number_format($pReq->amount, 2) }} {{ $pReq->currency }}</strong>
                    </td>
                    <td>
                        <div style="display: flex; align-items: center; gap: 4px;">
                            <span class="material-symbols-rounded" style="font-size: 14px; color: var(--text-secondary);">account_balance</span>
                            <span>{{ $pReq->bank_name }}</span>
                        </div>
                        <div style="font-family: var(--font-mono); font-size: 11px; color: var(--brand-forest); font-weight: 700;">#{{ $pReq->transfer_reference }}</div>
                    </td>
                    <td>
                        @if($pReq->receipt_path)
                            <a href="{{ route('superadmin.subscriptions.receipt', $pReq->id) }}" target="_blank" class="tactile-btn" style="padding: 4px 10px; font-size: 11px; text-decoration: none; display: inline-flex; align-items: center; gap: 4px;">
                                <span class="material-symbols-rounded" style="font-size: 14px;">receipt_long</span>
                                <span>{{ __('View Receipt') }}</span>
                            </a>
                        @else
                            <span style="color: var(--text-muted); font-size: 11px;">—</span>
                        @endif
                    </td>
                    <td style="font-size: 11px; color: var(--text-muted); font-family: var(--font-mono);">
                        {{ $pReq->created_at->diffForHumans() }}
                    </td>
                    <td>
                        <a href="{{ route('superadmin.subscriptions') }}" class="tactile-btn btn-primary" style="padding: 6px 12px; font-size: 11px; text-decoration: none; display: inline-flex; align-items: center; gap: 4px;">
                            <span class="material-symbols-rounded" style="font-size: 14px;">task_alt</span>
                            <span>{{ __('Review & Approve') }}</span>
                        </a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif

<!-- Recent Companies & Tenant Directory -->
<div class="panel-card">
    <div class="panel-header">
        <div class="panel-title" style="display: flex; align-items: center; gap: 8px;">
            <span class="material-symbols-rounded" style="color: var(--brand-forest);">domain</span>
            <span>{{ __('Recent Registered Organizations') }}</span>
        </div>
        <a href="{{ route('superadmin.companies') }}" class="tactile-btn btn-primary" style="font-size: 12px; padding: 8px 16px; text-decoration: none; display: inline-flex; align-items: center; gap: 6px;">
            <span>{{ __('View All Companies') }}</span>
            <span class="material-symbols-rounded" style="font-size: 16px;">arrow_forward</span>
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
                        <div style="font-size: 11px; color: var(--text-muted); font-family: var(--font-mono);">{{ $comp->slug }}</div>
                    </td>
                    <td>
                        <div style="font-weight: 700; color: var(--text-primary);">{{ $owner?->name ?? 'Administrator' }}</div>
                        <div style="font-size: 11px; color: var(--text-muted);">{{ $owner?->email }}</div>
                    </td>
                    <td>
                        <span class="badge-status badge-plan" style="display: inline-flex; align-items: center; gap: 4px;">
                            <span class="material-symbols-rounded" style="font-size: 14px;">workspace_premium</span>
                            <span>{{ $comp->plan?->name ?? 'Free' }} (<span style="font-family: var(--font-mono);">${{ number_format($comp->plan?->price ?? 0, 2) }}/mo</span>)</span>
                        </span>
                    </td>
                    <td>
                        <div style="font-weight: 700; font-family: var(--font-mono); color: {{ !$isUnlimited && $memberCount >= $seatLimit ? 'var(--status-danger)' : 'var(--brand-forest)' }};">
                            {{ $memberCount }} / {{ $isUnlimited ? '∞' : $seatLimit }} {{ __('Seats') }}
                        </div>
                    </td>
                    <td>
                        <span style="font-weight: 700; color: var(--text-secondary); font-family: var(--font-mono);">{{ $comp->rooms->count() }} {{ __('Rooms') }}</span>
                    </td>
                    <td>
                        @if($isSuspended)
                            <span class="badge-status badge-suspended" style="display: inline-flex; align-items: center; gap: 4px;">
                                <span class="material-symbols-rounded" style="font-size: 14px;">block</span>
                                <span>{{ __('Suspended') }}</span>
                            </span>
                        @else
                            <span class="badge-status badge-active" style="display: inline-flex; align-items: center; gap: 4px;">
                                <span class="material-symbols-rounded" style="font-size: 14px;">check_circle</span>
                                <span>{{ __('Active') }}</span>
                            </span>
                        @endif
                    </td>
                    <td>
                        <a href="{{ route('superadmin.companies') }}" class="tactile-btn btn-secondary" style="padding: 6px 12px; font-size: 11px; text-decoration: none; display: inline-flex; align-items: center; gap: 4px;">
                            <span class="material-symbols-rounded" style="font-size: 14px;">settings</span>
                            <span>{{ __('Manage') }}</span>
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
