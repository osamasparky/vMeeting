@extends('superadmin.layout')

@section('title', __('Dashboard'))
@section('page_title', __('Dashboard'))

@section('content')
@php
    // design-reference "Screen 48 - Super Admin Dashboard"
    $compact = function ($n) {
        $n = (float) $n;
        if (abs($n) >= 1000000) return rtrim(rtrim(number_format($n / 1000000, 1), '0'), '.') . 'M';
        if (abs($n) >= 1000) return round($n / 1000) . 'K';
        return number_format($n);
    };
    $avgUsers = $stats['total_companies'] > 0 ? round($stats['total_users'] / $stats['total_companies'], 1) : 0;
    $freeTier = $stats['total_companies'] - $stats['active_subscriptions'];

    // Plan tiers, cheapest first, each with a tone for the stacked bar and legend.
    $planTones = ['var(--ula-tone-stone-dot)', 'var(--ula-tone-gold-dot)', 'var(--ula-tone-palm-dot)', 'var(--ula-accent-default)'];
    $planRows = $plans->sortBy('price')->values()->map(function ($plan, $i) use ($stats, $planTones) {
        return [
            'name' => $plan->name,
            'count' => $plan->organizations_count,
            'pct' => $stats['total_companies'] > 0 ? round($plan->organizations_count / $stats['total_companies'] * 100) : 0,
            'color' => $planTones[$i % count($planTones)],
        ];
    });

    // Audit action → icon + tone (removals warn, upgrades notice, creations/entries confirm).
    $auditIcon = function (string $action) {
        $verb = \Illuminate\Support\Str::afterLast($action, '.');
        return match (true) {
            in_array($verb, ['deleted', 'leave', 'company_status_toggled']) => ['block', 'terracotta'],
            in_array($verb, ['company_plan_updated']) => ['upgrade', 'gold'],
            in_array($verb, ['enter', 'company_impersonated']) => ['login', 'palm'],
            in_array($verb, ['created']) || str_ends_with($action, '.created') => ['person_add', 'palm'],
            default => ['key', 'stone'],
        };
    };
@endphp

<!-- Page header -->
<div class="sa-page-head">
    <div class="ula-headline-group" style="gap: 2px;">
        <span style="font-size: var(--ula-size-xs); color: var(--ula-text-secondary);">{{ __('sa.crumb_dashboard') }}</span>
        <h2 class="ula-headline-ar" style="margin: 0; font-size: var(--ula-size-h1);">{{ __('sa.dashboard_title') }}</h2>
        @if(app()->getLocale() === 'ar')
            <span class="ula-headline-en" style="font-size: var(--ula-size-h4);">Platform Overview &amp; SaaS Metrics</span>
        @endif
    </div>
    <div class="sa-page-actions">
        <span class="badge-status badge-active"><span class="ula-hub-tag-dot"></span>{{ __('sa.system_running') }}</span>
        <x-btn variant="primary" size="md" icon="domain" :href="route('superadmin.companies')">{{ __('Manage Companies') }}</x-btn>
    </div>
</div>

<!-- KPIs -->
<div class="sa-kpi-grid">
    <x-kpi-card icon="domain" iconColor="sage" :title="__('Total Companies')" :value="number_format($stats['total_companies'])">
        <x-kpi-chip tone="palm" :n="'+' . $stats['new_companies_month']">{{ __('sa.chip_this_month') }}</x-kpi-chip>
        <x-kpi-chip :n="$stats['active_companies']">{{ __('sa.chip_active') }}</x-kpi-chip>
        @if($stats['suspended_companies'] > 0)
            <x-kpi-chip tone="terracotta" :n="$stats['suspended_companies']">{{ __('sa.chip_suspended') }}</x-kpi-chip>
        @endif
    </x-kpi-card>
    <x-kpi-card icon="group" iconColor="gold" :title="__('Total Users')" :value="number_format($stats['total_users'])">
        <x-kpi-chip tone="palm" :n="'+' . $stats['new_users_month']">{{ __('sa.chip_new') }}</x-kpi-chip>
        <x-kpi-chip :n="$avgUsers">{{ __('sa.chip_avg_per_company') }}</x-kpi-chip>
    </x-kpi-card>
    <x-kpi-card icon="workspace_premium" iconColor="sage" :title="__('Paid Subscriptions')" :value="number_format($stats['active_subscriptions'])">
        <x-kpi-chip tone="palm" :n="$stats['active_subscriptions']">{{ __('sa.chip_paid') }}</x-kpi-chip>
        <x-kpi-chip :n="$freeTier">{{ __('sa.chip_free_tier') }}</x-kpi-chip>
    </x-kpi-card>
    <x-kpi-card icon="payments" iconColor="gold" :title="__('sa.mrr_title')" :value="'SAR ' . number_format($stats['estimated_mrr_sar'])">
        <x-kpi-chip tone="gold" :n="'SAR ' . $compact($stats['estimated_mrr_sar'] * 12)">{{ __('sa.chip_yearly') }}</x-kpi-chip>
    </x-kpi-card>
</div>

<!-- Secondary stats -->
<div class="sa-kpi-grid">
    <x-stat-card icon="meeting_room" :label="__('Meeting Rooms')" :value="number_format($stats['total_rooms'])" />
    <x-stat-card icon="folder_copy" :label="__('Total Projects')" :value="number_format($stats['total_projects'])" />
    <x-stat-card icon="schedule" :label="__('Logged Hours')" :value="number_format($stats['total_logged_hours']) . 'h'" />
    <x-stat-card icon="policy" :label="__('Audit Events')" :value="number_format($stats['total_audit_events'])" />
</div>

<!-- Plans + audit -->
<div class="sa-split">
    <section class="sa-card">
        <div class="sa-card-head">
            <div class="ula-headline-group" style="gap: 0;">
                <h3 class="sa-card-title">{{ __('sa.plans_title') }}</h3>
                @if(app()->getLocale() === 'ar')<span class="ula-headline-en" style="font-size: var(--ula-size-sm);">Plan Tiers Distribution</span>@endif
            </div>
            <a href="{{ route('superadmin.plans') }}" class="sa-link">{{ __('Manage Plans') }}</a>
        </div>
        <div class="sa-stackbar" role="img" aria-label="{{ __('sa.plans_title') }}">
            @foreach($planRows as $row)
                @if($row['pct'] > 0)<span style="width: {{ $row['pct'] }}%; background: {{ $row['color'] }};"></span>@endif
            @endforeach
        </div>
        <div style="display: flex; flex-direction: column; gap: 12px;">
            @foreach($planRows as $row)
                <div class="sa-legend-row">
                    <span class="sa-legend-dot" style="background: {{ $row['color'] }};"></span>
                    <span style="flex: 1; font-size: var(--ula-size-body);">{{ $row['name'] }}</span>
                    <span class="sa-num" style="font-size: 14px;">{{ $row['count'] }}</span>
                    <span class="sa-num" style="width: 48px; text-align: start; font-size: var(--ula-size-sm); color: var(--ula-text-muted);">{{ $row['pct'] }}%</span>
                </div>
            @endforeach
        </div>
        <div class="sa-card-foot">
            {{ __('sa.active_tenants') }}
            <span class="sa-num" style="font-size: 17px; color: var(--ula-text-primary);">{{ $stats['active_companies'] }}</span>
        </div>
    </section>

    <section class="sa-card" style="gap: 14px;">
        <div class="sa-card-head">
            <div class="ula-headline-group" style="gap: 0;">
                <h3 class="sa-card-title">{{ __('sa.audit_title') }}</h3>
                @if(app()->getLocale() === 'ar')<span class="ula-headline-en" style="font-size: var(--ula-size-sm);">Live Security &amp; Audit Trail</span>@endif
            </div>
            <span class="badge-status">{{ __('Latest Events') }}</span>
        </div>
        <div style="display: flex; flex-direction: column;">
            @forelse($recentAuditLogs as $log)
                @php
                    [$aIcon, $aTone] = $auditIcon($log->action);
                    $aKey = 'audit.' . $log->action;
                    $aLabel = __($aKey) === $aKey ? \Illuminate\Support\Str::headline($log->action) : __($aKey);
                @endphp
                <div class="sa-feed-row">
                    <span class="sa-tile sa-tile--{{ $aTone }}"><span class="material-symbols-rounded" aria-hidden="true">{{ $aIcon }}</span></span>
                    <div style="flex: 1; min-width: 0; display: flex; flex-direction: column;">
                        <span style="font-size: 14px; font-weight: var(--ula-weight-medium);">{{ $aLabel }}</span>
                        <span style="font-size: var(--ula-size-xs); color: var(--ula-text-muted);">{{ $log->actor?->name ?? __('audit.system') }} · {{ $log->organization?->name ?? __('sa.platform') }}</span>
                    </div>
                    <span class="sa-num" style="font-size: var(--ula-size-xs); color: var(--ula-text-muted);">{{ $log->created_at?->format('H:i') }}</span>
                </div>
            @empty
                <div class="sa-empty">{{ __('No recent audit logs recorded.') }}</div>
            @endforelse
        </div>
    </section>
</div>

<!-- Pending approvals -->
<section class="sa-card sa-card--table">
    <div class="sa-card-head sa-card-head--padded">
        <div style="display: flex; align-items: center; gap: 10px;">
            <h3 class="sa-card-title">{{ __('Pending Subscription Approvals') }}</h3>
            <x-kpi-chip tone="gold" :n="$pendingSubscriptionRequests->count()">{{ __('sa.requests') }}</x-kpi-chip>
        </div>
        <a href="{{ route('superadmin.subscriptions', ['status' => 'pending']) }}" class="sa-link">{{ __('Review All Requests') }}</a>
    </div>
    <div class="data-table-container sa-flat-table">
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
                @forelse($pendingSubscriptionRequests as $pReq)
                    <tr>
                        <td>
                            <div style="display: flex; align-items: center; gap: 10px; min-width: 0;">
                                <span class="sa-tile sa-tile--palm">{{ mb_substr($pReq->organization?->name ?? '?', 0, 1) }}</span>
                                <strong style="white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">{{ $pReq->organization?->name ?? __('Company') }}</strong>
                            </div>
                        </td>
                        <td><span class="badge-status badge-active">{{ $pReq->plan?->name ?? __('Plan') }}</span></td>
                        <td class="sa-num">{{ $pReq->currency }} {{ number_format($pReq->amount) }}</td>
                        <td>
                            <div style="display: flex; flex-direction: column;">
                                <span>{{ $pReq->bank_name }}</span>
                                <span class="sa-num" style="font-size: var(--ula-size-xs); color: var(--ula-text-muted);">{{ $pReq->transfer_reference }}</span>
                            </div>
                        </td>
                        <td>
                            @if($pReq->receipt_path)
                                <a href="{{ route('superadmin.subscriptions.receipt', $pReq->id) }}" target="_blank" rel="noopener" class="sa-link" style="display: inline-flex; align-items: center; gap: 4px;">
                                    <span class="material-symbols-rounded" style="font-size: 18px;" aria-hidden="true">receipt_long</span>{{ __('sa.view') }}
                                </a>
                            @else
                                <span style="color: var(--ula-text-muted);">—</span>
                            @endif
                        </td>
                        <td class="sa-num" style="color: var(--ula-text-secondary);">{{ $pReq->created_at->format('d M') }}</td>
                        <td>
                            <div style="display: flex; gap: 8px;">
                                @if($pReq->receipt_path)
                                    <x-btn variant="outline" size="sm" icon="visibility" :href="route('superadmin.subscriptions.receipt', $pReq->id)" target="_blank" rel="noopener">{{ __('View Receipt') }}</x-btn>
                                @endif
                                <x-btn variant="primary" size="sm" icon="task_alt" :href="route('superadmin.subscriptions')">{{ __('Review & Approve') }}</x-btn>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7">
                            <div class="sa-empty"><span class="material-symbols-rounded" style="font-size: 20px; color: var(--ula-status-success);" aria-hidden="true">check_circle</span>{{ __('sa.no_pending') }}</div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</section>

<!-- Recent organizations -->
<section class="sa-card sa-card--table">
    <div class="sa-card-head sa-card-head--padded">
        <h3 class="sa-card-title">{{ __('Recent Registered Organizations') }}</h3>
        <a href="{{ route('superadmin.companies') }}" class="sa-link">{{ __('View All Companies') }}</a>
    </div>
    <div class="data-table-container sa-flat-table">
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
                        $seatPct = $isUnlimited ? min(100, $memberCount * 10) : ($seatLimit > 0 ? min(100, round($memberCount / $seatLimit * 100)) : 0);
                        $seatFull = ! $isUnlimited && $seatPct >= 95;
                        $owner = $comp->members->first()?->user;
                        $isSuspended = $comp->status === 'suspended';
                    @endphp
                    <tr>
                        <td>
                            <div style="display: flex; align-items: center; gap: 10px;">
                                <span class="sa-tile sa-tile--gold">{{ mb_substr($comp->name, 0, 1) }}</span>
                                <strong>{{ $comp->name }}</strong>
                            </div>
                        </td>
                        <td>
                            <div style="display: flex; flex-direction: column; min-width: 0;">
                                <span>{{ $owner?->name ?? __('Administrator') }}</span>
                                <span class="sa-num" style="font-size: var(--ula-size-xs); color: var(--ula-text-muted); white-space: nowrap;">{{ $owner?->email }}</span>
                            </div>
                        </td>
                        <td>{{ $comp->plan?->name ?? 'Free' }}</td>
                        <td>
                            <div style="display: flex; align-items: center; gap: 8px; min-width: 140px;">
                                <div class="sa-seatbar"><div style="width: {{ $seatPct }}%; background: {{ $seatFull ? 'var(--ula-tone-terracotta-dot)' : 'var(--ula-tone-palm-dot)' }};"></div></div>
                                <span class="sa-num" style="font-size: var(--ula-size-xs); color: var(--ula-text-secondary);">{{ $memberCount }}/{{ $isUnlimited ? '∞' : $seatLimit }}</span>
                            </div>
                        </td>
                        <td class="sa-num">{{ $comp->rooms->count() }}</td>
                        <td>
                            <span class="badge-status {{ $isSuspended ? 'badge-suspended' : 'badge-active' }}"><span class="ula-hub-tag-dot"></span>{{ $isSuspended ? __('Suspended') : __('Active') }}</span>
                        </td>
                        <td>
                            <a href="{{ route('superadmin.companies.show', $comp->id) }}" class="sa-link" style="display: inline-flex; align-items: center; gap: 4px;">
                                {{ __('Manage') }}<span class="material-symbols-rounded sa-chevron" style="font-size: 16px;" aria-hidden="true">chevron_left</span>
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7"><div class="sa-empty">{{ __('No organizations registered yet.') }}</div></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</section>
@endsection
