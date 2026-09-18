@extends('superadmin.layout')

@section('title', __('Subscription Requests & Payments') . ' — ' . __('Super Admin Portal'))

@section('content')
<!-- Page Header -->
<div class="page-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; flex-wrap: wrap; gap: 16px;">
    <div>
        <h1 class="page-title" style="font-size: 22px; font-weight: 800; color: var(--ula-text-primary); margin-bottom: 4px; display: flex; align-items: center; gap: 8px;">
            <span class="material-symbols-rounded" style="color: var(--ula-highlight-default); font-size: 24px;">payments</span>
            <span>{{ __('Bank Transfer Payments & Subscription Requests') }}</span>
        </h1>
        <p class="page-subtitle" style="font-size: 13px; color: var(--ula-text-secondary); margin: 0;">
            {{ __('Review wire transfers, verify deposit slips, and approve subscription upgrades for client companies.') }}
        </p>
    </div>
</div>

<!-- KPI Cards Grid -->
<div class="kpi-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px; margin-bottom: 24px;">
    <div class="kpi-card" style="background: var(--ula-surface-card); border: 1px solid var(--ula-border-subtle); border-radius: var(--ula-radius-xl); padding: 18px 20px; box-shadow: var(--ula-shadow-sm); border-inline-start: 4px solid var(--ula-status-warning);">
        <div class="kpi-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
            <span class="kpi-title" style="font-size: 12px; font-weight: 700; color: var(--ula-text-secondary);">{{ __('Pending Approvals') }}</span>
            <span class="material-symbols-rounded" style="font-size: 20px; color: var(--ula-status-warning);">hourglass_top</span>
        </div>
        <div class="kpi-value" style="font-size: 26px; font-weight: 800; color: var(--ula-status-warning); font-family: 'IBM Plex Mono', monospace;">{{ $stats['pending'] }}</div>
        <div class="kpi-subtext" style="font-size: 11px; color: var(--ula-text-muted); margin-top: 4px;">{{ __('Awaiting SuperAdmin review') }}</div>
    </div>

    <div class="kpi-card" style="background: var(--ula-surface-card); border: 1px solid var(--ula-border-subtle); border-radius: var(--ula-radius-xl); padding: 18px 20px; box-shadow: var(--ula-shadow-sm); border-inline-start: 4px solid var(--ula-status-success);">
        <div class="kpi-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
            <span class="kpi-title" style="font-size: 12px; font-weight: 700; color: var(--ula-text-secondary);">{{ __('Approved Subscriptions') }}</span>
            <span class="material-symbols-rounded" style="font-size: 20px; color: var(--ula-status-success);">check_circle</span>
        </div>
        <div class="kpi-value" style="font-size: 26px; font-weight: 800; color: var(--ula-status-success); font-family: 'IBM Plex Mono', monospace;">{{ $stats['approved'] }}</div>
        <div class="kpi-subtext" style="font-size: 11px; color: var(--ula-text-muted); margin-top: 4px;">{{ __('Active & plan provisioned') }}</div>
    </div>

    <div class="kpi-card" style="background: var(--ula-surface-card); border: 1px solid var(--ula-border-subtle); border-radius: var(--ula-radius-xl); padding: 18px 20px; box-shadow: var(--ula-shadow-sm); border-inline-start: 4px solid var(--ula-status-danger);">
        <div class="kpi-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
            <span class="kpi-title" style="font-size: 12px; font-weight: 700; color: var(--ula-text-secondary);">{{ __('Rejected Requests') }}</span>
            <span class="material-symbols-rounded" style="font-size: 20px; color: var(--ula-status-danger);">cancel</span>
        </div>
        <div class="kpi-value" style="font-size: 26px; font-weight: 800; color: var(--ula-status-danger); font-family: 'IBM Plex Mono', monospace;">{{ $stats['rejected'] }}</div>
        <div class="kpi-subtext" style="font-size: 11px; color: var(--ula-text-muted); margin-top: 4px;">{{ __('Declined due to invalid slip') }}</div>
    </div>

    <div class="kpi-card" style="background: var(--ula-surface-card); border: 1px solid var(--ula-border-subtle); border-radius: var(--ula-radius-xl); padding: 18px 20px; box-shadow: var(--ula-shadow-sm); border-inline-start: 4px solid var(--ula-palm-900);">
        <div class="kpi-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
            <span class="kpi-title" style="font-size: 12px; font-weight: 700; color: var(--ula-text-secondary);">{{ __('Total Requests') }}</span>
            <span class="material-symbols-rounded" style="font-size: 20px; color: var(--ula-text-primary);">receipt_long</span>
        </div>
        <div class="kpi-value" style="font-size: 26px; font-weight: 800; color: var(--ula-text-primary); font-family: 'IBM Plex Mono', monospace;">{{ $stats['total'] }}</div>
        <div class="kpi-subtext" style="font-size: 11px; color: var(--ula-text-muted); margin-top: 4px;">{{ __('All time wire transfer requests') }}</div>
    </div>
</div>

<!-- Filter Bar & Search -->
<div class="card" style="padding: 16px 20px; margin-bottom: 20px; border-radius: var(--ula-radius-lg); border: 1px solid var(--ula-border-subtle); background: var(--ula-surface-card); display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 14px; box-shadow: var(--ula-shadow-sm);">
    <!-- Status Filter Pills -->
    <div style="display: flex; gap: 8px; flex-wrap: wrap;">
        <a href="{{ route('superadmin.subscriptions', ['status' => 'all', 'search' => $search]) }}" class="nav-badge-pill" style="text-decoration: none; padding: 6px 14px; border-radius: var(--ula-radius-pill); font-size: 12px; font-weight: 600; {{ $statusFilter === 'all' ? 'background: var(--ula-palm-900); color: white;' : 'background: var(--ula-surface-page-alt); color: var(--ula-text-secondary);' }}">
            {{ __('All') }} ({{ $stats['total'] }})
        </a>
        <a href="{{ route('superadmin.subscriptions', ['status' => 'pending', 'search' => $search]) }}" class="nav-badge-pill" style="text-decoration: none; padding: 6px 14px; border-radius: var(--ula-radius-pill); font-size: 12px; font-weight: 600; {{ $statusFilter === 'pending' ? 'background: var(--ula-gold-500); color: white;' : 'background: rgba(211,165,83,0.12); color: var(--ula-gold-600);' }}">
            <span class="material-symbols-rounded" style="font-size: 13px; vertical-align: middle;">hourglass_top</span>
            <span>{{ __('Pending') }} ({{ $stats['pending'] }})</span>
        </a>
        <a href="{{ route('superadmin.subscriptions', ['status' => 'approved', 'search' => $search]) }}" class="nav-badge-pill" style="text-decoration: none; padding: 6px 14px; border-radius: var(--ula-radius-pill); font-size: 12px; font-weight: 600; {{ $statusFilter === 'approved' ? 'background: var(--ula-status-success); color: white;' : 'background: rgba(60,107,76,0.12); color: var(--ula-status-success);' }}">
            <span class="material-symbols-rounded" style="font-size: 13px; vertical-align: middle;">check_circle</span>
            <span>{{ __('Approved') }} ({{ $stats['approved'] }})</span>
        </a>
        <a href="{{ route('superadmin.subscriptions', ['status' => 'rejected', 'search' => $search]) }}" class="nav-badge-pill" style="text-decoration: none; padding: 6px 14px; border-radius: var(--ula-radius-pill); font-size: 12px; font-weight: 600; {{ $statusFilter === 'rejected' ? 'background: var(--ula-status-danger); color: white;' : 'background: rgba(217,107,95,0.12); color: var(--ula-status-danger);' }}">
            <span class="material-symbols-rounded" style="font-size: 13px; vertical-align: middle;">cancel</span>
            <span>{{ __('Rejected') }} ({{ $stats['rejected'] }})</span>
        </a>
    </div>

    <!-- Search Form -->
    <form method="GET" action="{{ route('superadmin.subscriptions') }}" style="display: flex; gap: 8px; margin: 0;">
        <input type="hidden" name="status" value="{{ $statusFilter }}">
        <div style="position: relative; display: flex; align-items: center;">
            <span class="material-symbols-rounded" style="position: absolute; inset-inline-start: 12px; font-size: 16px; color: var(--ula-text-muted); pointer-events: none;">search</span>
            <input
                type="text"
                name="search"
                style="min-width: 240px; background: var(--ula-surface-page-alt); border: 1px solid var(--ula-border-subtle); border-radius: var(--ula-radius-pill); padding: 8px 14px; padding-inline-start: 36px; color: var(--ula-text-primary); font-size: 13px; outline: none;"
                placeholder="{{ __('Search by company, sender, ref #...') }}"
                value="{{ $search }}"
            >
        </div>
        <button type="submit" class="tactile-btn btn-primary" style="padding: 8px 16px; font-size: 12px; border-radius: var(--ula-radius-pill); display: inline-flex; align-items: center; gap: 6px;">
            <span class="material-symbols-rounded" style="font-size: 15px;">search</span>
            <span>{{ __('Search') }}</span>
        </button>
        @if($search)
            <a href="{{ route('superadmin.subscriptions', ['status' => $statusFilter]) }}" class="tactile-btn btn-secondary" style="padding: 8px 12px; font-size: 12px; border-radius: var(--ula-radius-pill); display: inline-flex; align-items: center;">
                <span class="material-symbols-rounded" style="font-size: 15px;">close</span>
            </a>
        @endif
    </form>
</div>

<!-- Requests Table Card -->
<div class="card" style="padding: 0; overflow: hidden; border-radius: var(--ula-radius-xl); border: 1px solid var(--ula-border-subtle); background: var(--ula-surface-card); box-shadow: var(--ula-shadow-sm);">
    <div style="overflow-x: auto;">
        <table style="width: 100%; border-collapse: collapse; text-align: start; font-size: 13px;">
            <thead>
                <tr style="background: var(--ula-surface-page-alt); border-bottom: 1px solid var(--ula-border-subtle); color: var(--ula-text-muted); font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px;">
                    <th style="padding: 14px 20px; text-align: start;">{{ __('Company / Organization') }}</th>
                    <th style="padding: 14px 20px; text-align: start;">{{ __('Target Plan') }}</th>
                    <th style="padding: 14px 20px; text-align: start;">{{ __('Amount & Cycle') }}</th>
                    <th style="padding: 14px 20px; text-align: start;">{{ __('Transfer & Bank Details') }}</th>
                    <th style="padding: 14px 20px; text-align: center;">{{ __('Deposit Receipt') }}</th>
                    <th style="padding: 14px 20px; text-align: center;">{{ __('Status') }}</th>
                    <th style="padding: 14px 20px; text-align: center;">{{ __('Actions') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($subscriptionRequests as $req)
                <tr style="border-bottom: 1px solid var(--ula-border-subtle); transition: background 0.15s ease;">
                    <!-- Company Info -->
                    <td style="padding: 16px 20px;">
                        @if($req->organization)
                            <a href="{{ route('superadmin.companies.show', $req->organization_id) }}" style="font-weight: 700; color: var(--ula-text-primary); text-decoration: none; font-size: 14px; display: inline-flex; align-items: center; gap: 8px;">
                                <div style="width: 28px; height: 28px; border-radius: 8px; background: rgba(20,43,36,0.08); display: flex; align-items: center; justify-content: center; color: var(--ula-text-primary); font-size: 12px; font-weight: 700;">
                                    {{ strtoupper(substr($req->organization->name, 0, 2)) }}
                                </div>
                                <span>{{ $req->organization->name }}</span>
                            </a>
                            <div style="font-size: 11px; color: var(--ula-text-muted); margin-top: 2px; margin-inline-start: 36px;">
                                {{ $req->user?->name ?? 'User' }} ({{ $req->user?->email ?? '—' }})
                            </div>
                        @else
                            <span style="color: var(--ula-text-muted);">{{ __('Organization Deleted') }}</span>
                        @endif
                    </td>

                    <!-- Target Plan -->
                    <td style="padding: 16px 20px;">
                        <span class="badge-status badge-plan" style="display: inline-flex; align-items: center; gap: 4px; font-size: 11px; padding: 3px 8px; border-radius: 6px; background: rgba(211,165,83,0.12); color: var(--ula-gold-600); font-weight: 700;">
                            <span class="material-symbols-rounded" style="font-size: 13px;">diamond</span>
                            <span>{{ $req->plan?->name ?? 'Plan' }}</span>
                        </span>
                        <div style="font-size: 11px; color: var(--ula-text-muted); margin-top: 4px; font-family: 'IBM Plex Mono', monospace;">
                            {{ $req->plan?->seat_limit === 0 ? __('Unlimited') : $req->plan?->seat_limit }} {{ __('Seats') }}
                        </div>
                    </td>

                    <!-- Amount & Cycle -->
                    <td style="padding: 16px 20px;">
                        <div style="font-weight: 800; color: var(--ula-text-primary); font-size: 14px; font-family: 'IBM Plex Mono', monospace;">
                            {{ number_format($req->amount, 2) }} <span style="font-size: 11px; font-weight: 600; color: var(--ula-text-secondary);">{{ $req->currency }}</span>
                        </div>
                        <div style="font-size: 11px; color: var(--ula-text-primary); font-weight: 700;">
                            {{ $req->billing_cycle === 'yearly' ? __('Yearly') : __('Monthly') }}
                        </div>
                    </td>

                    <!-- Transfer & Bank Details -->
                    <td style="padding: 16px 20px;">
                        <div style="font-weight: 700; color: var(--ula-text-primary); display: flex; align-items: center; gap: 4px;">
                            <span class="material-symbols-rounded" style="font-size: 16px; color: var(--ula-text-muted);">account_balance</span>
                            <span>{{ $req->bank_name }}</span>
                        </div>
                        <div style="font-size: 11px; color: var(--ula-text-secondary); margin-top: 2px;">
                            <strong>{{ __('Sender') }}:</strong> {{ $req->sender_name }}
                        </div>
                        <div style="font-size: 11px; font-family: 'IBM Plex Mono', monospace; color: var(--ula-text-primary); font-weight: 700; margin-top: 2px;">
                            #{{ $req->transfer_reference }}
                        </div>
                        <div style="font-size: 10px; color: var(--ula-text-muted); margin-top: 2px; font-family: 'IBM Plex Mono', monospace;">
                            {{ $req->transfer_date ? $req->transfer_date->format('Y-m-d') : $req->created_at->format('Y-m-d') }}
                        </div>
                    </td>

                    <!-- Deposit Receipt -->
                    <td style="padding: 16px 20px; text-align: center;">
                        @if($req->receipt_path)
                            @php
                                $isPdf = str_ends_with(strtolower($req->receipt_path), '.pdf');
                            @endphp
                            <a
                                href="{{ route('superadmin.subscriptions.receipt', $req->id) }}"
                                target="_blank"
                                class="tactile-btn btn-secondary"
                                style="padding: 5px 10px; font-size: 11px; text-decoration: none; display: inline-flex; align-items: center; gap: 4px;"
                                title="{{ __('View Receipt Document') }}"
                            >
                                <span class="material-symbols-rounded" style="font-size: 14px;">{{ $isPdf ? 'picture_as_pdf' : 'image' }}</span>
                                <span>{{ $isPdf ? 'PDF' : __('Image') }}</span>
                            </a>
                        @else
                            <span style="font-size: 11px; color: var(--ula-text-muted);">{{ __('No slip') }}</span>
                        @endif
                    </td>

                    <!-- Status -->
                    <td style="padding: 16px 20px; text-align: center;">
                        @if($req->status === 'pending')
                            <span class="badge-status" style="display: inline-flex; align-items: center; gap: 4px; font-size: 11px; padding: 3px 8px; border-radius: 6px; background: rgba(211,165,83,0.12); color: var(--ula-gold-600); font-weight: 700;">
                                <span class="material-symbols-rounded" style="font-size: 12px;">hourglass_top</span>
                                <span>{{ __('Pending Approval') }}</span>
                            </span>
                        @elseif($req->status === 'approved')
                            <span class="badge-status badge-active" style="display: inline-flex; align-items: center; gap: 4px; font-size: 11px; padding: 3px 8px; border-radius: 6px; background: rgba(60,107,76,0.12); color: var(--ula-status-success); font-weight: 700;">
                                <span class="material-symbols-rounded" style="font-size: 12px;">check_circle</span>
                                <span>{{ __('Approved') }}</span>
                            </span>
                            @if($req->reviewed_at)
                                <div style="font-size: 10px; color: var(--ula-text-muted); margin-top: 3px; font-family: 'IBM Plex Mono', monospace;">
                                    {{ $req->reviewed_at->format('Y-m-d H:i') }}
                                </div>
                            @endif
                        @elseif($req->status === 'rejected')
                            <span class="badge-status badge-suspended" style="display: inline-flex; align-items: center; gap: 4px; font-size: 11px; padding: 3px 8px; border-radius: 6px; background: rgba(217,107,95,0.12); color: var(--ula-status-danger); font-weight: 700;">
                                <span class="material-symbols-rounded" style="font-size: 12px;">cancel</span>
                                <span>{{ __('Rejected') }}</span>
                            </span>
                            @if($req->admin_notes)
                                <div style="font-size: 10px; color: var(--ula-status-danger); max-width: 140px; margin: 3px auto 0; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;" title="{{ $req->admin_notes }}">
                                    {{ $req->admin_notes }}
                                </div>
                            @endif
                        @else
                            <span class="badge-status" style="font-size: 11px; padding: 3px 8px;">
                                {{ ucfirst($req->status) }}
                            </span>
                        @endif
                    </td>

                    <!-- Actions -->
                    <td style="padding: 16px 20px; text-align: center;">
                        @if($req->status === 'pending')
                            <div style="display: flex; justify-content: center; gap: 6px;">
                                <button
                                    type="button"
                                    onclick="openApproveModal('{{ $req->id }}', '{{ addslashes($req->organization?->name ?? 'Company') }}', '{{ addslashes($req->plan?->name ?? 'Plan') }}', '{{ number_format($req->amount, 2) }} {{ $req->currency }}')"
                                    class="tactile-btn btn-primary"
                                    style="padding: 6px 12px; font-size: 11px; display: inline-flex; align-items: center; gap: 4px;"
                                >
                                    <span class="material-symbols-rounded" style="font-size: 14px;">check</span>
                                    <span>{{ __('Approve') }}</span>
                                </button>

                                <button
                                    type="button"
                                    onclick="openRejectModal('{{ $req->id }}', '{{ addslashes($req->organization?->name ?? 'Company') }}')"
                                    class="tactile-btn"
                                    style="padding: 6px 10px; font-size: 11px; color: var(--ula-status-danger); border-color: rgba(217,107,95,0.3); display: inline-flex; align-items: center;"
                                    title="{{ __('Reject') }}"
                                >
                                    <span class="material-symbols-rounded" style="font-size: 14px;">close</span>
                                </button>
                            </div>
                        @else
                            <button
                                type="button"
                                onclick="openDetailsModal('{{ $req->id }}', '{{ addslashes($req->organization?->name ?? '') }}', '{{ addslashes($req->plan?->name ?? '') }}', '{{ number_format($req->amount, 2) }} {{ $req->currency }}', '{{ addslashes($req->bank_name) }}', '{{ addslashes($req->sender_name) }}', '{{ $req->transfer_reference }}', '{{ $req->status }}', '{{ addslashes($req->admin_notes ?? '') }}')"
                                class="tactile-btn btn-secondary"
                                style="padding: 5px 10px; font-size: 11px; display: inline-flex; align-items: center; gap: 4px;"
                            >
                                <span class="material-symbols-rounded" style="font-size: 14px;">visibility</span>
                                <span>{{ __('Details') }}</span>
                            </button>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" style="text-align: center; padding: 48px; color: var(--ula-text-muted);">
                        <span class="material-symbols-rounded" style="font-size: 36px; display: block; margin-bottom: 8px; opacity: 0.5;">receipt_long</span>
                        <div style="font-size: 14px; font-weight: 700; color: var(--ula-text-primary);">{{ __('No subscription requests found') }}</div>
                        <div style="font-size: 12px; margin-top: 4px;">{{ __('Incoming bank transfer payments from organizations will appear here.') }}</div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($subscriptionRequests->hasPages())
    <div style="padding: 16px 20px; border-top: 1px solid var(--ula-border-subtle); background: var(--ula-surface-page-alt);">
        {{ $subscriptionRequests->links() }}
    </div>
    @endif
</div>

<!-- APPROVE SUBSCRIPTION MODAL -->
<div id="approveModal" class="modal-overlay">
    <div class="modal-card" style="border-radius: var(--ula-radius-xl); padding: 26px; max-width: 480px; width: 100%;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
            <div style="display: flex; align-items: center; gap: 8px;">
                <span class="material-symbols-rounded" style="color: var(--ula-status-success); font-size: 22px;">verified</span>
                <h3 style="font-size: 17px; font-weight: 800; color: var(--ula-text-primary); margin: 0;">
                    {{ __('Approve Plan & Activate Workspace') }}
                </h3>
            </div>
            <button type="button" onclick="closeModal('approveModal')" style="background: var(--ula-surface-page-alt); border: 1px solid var(--ula-border-subtle); width: 32px; height: 32px; border-radius: 50%; display: flex; align-items: center; justify-content: center; cursor: pointer; color: var(--ula-text-primary);">
                <span class="material-symbols-rounded" style="font-size: 16px;">close</span>
            </button>
        </div>

        <p style="font-size: 13px; color: var(--ula-text-secondary); margin-bottom: 16px;">
            {{ __('Are you sure you want to approve this bank transfer payment and immediately assign the subscription plan to the company?') }}
        </p>

        <div style="background: var(--ula-surface-page-alt); border: 1px solid var(--ula-border-subtle); border-radius: 12px; padding: 14px; margin-bottom: 18px;">
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 8px; font-size: 12px;">
                <div><span style="color: var(--ula-text-muted);">{{ __('Company') }}:</span> <strong id="approveOrgName" style="color: var(--ula-text-primary);">—</strong></div>
                <div><span style="color: var(--ula-text-muted);">{{ __('Target Plan') }}:</span> <strong id="approvePlanName" style="color: var(--ula-text-primary);">—</strong></div>
                <div><span style="color: var(--ula-text-muted);">{{ __('Amount') }}:</span> <strong id="approveAmount" style="color: var(--ula-text-primary);">—</strong></div>
                <div><span style="color: var(--ula-text-muted);">{{ __('Action') }}:</span> <strong style="color: var(--ula-status-success);">{{ __('Instant Activation') }}</strong></div>
            </div>
        </div>

        <form method="POST" id="approveForm" action="">
            @csrf
            <div class="form-group" style="margin-bottom: 20px;">
                <label class="form-label" style="font-size: 12px; font-weight: 700; color: var(--ula-text-secondary); margin-bottom: 6px; display: block;" for="approve_notes">
                    {{ __('SuperAdmin Notes / Reference (Optional)') }}
                </label>
                <input
                    type="text"
                    id="approve_notes"
                    name="admin_notes"
                    class="form-input"
                    style="width: 100%; background: var(--ula-surface-page-alt); border: 1px solid var(--ula-border-subtle); border-radius: 10px; padding: 10px 14px; font-size: 13px; color: var(--ula-text-primary); outline: none;"
                    value="Approved & Verified Bank Transfer"
                    placeholder="{{ __('e.g. Verified with accounting statement #...') }}"
                >
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 10px;">
                <button type="button" onclick="closeModal('approveModal')" class="tactile-btn btn-secondary">
                    {{ __('Cancel') }}
                </button>
                <button type="submit" class="tactile-btn btn-primary" style="display: inline-flex; align-items: center; gap: 6px;">
                    <span class="material-symbols-rounded" style="font-size: 16px;">check_circle</span>
                    <span>{{ __('Confirm & Activate Plan') }}</span>
                </button>
            </div>
        </form>
    </div>
</div>

<!-- REJECT SUBSCRIPTION MODAL -->
<div id="rejectModal" class="modal-overlay">
    <div class="modal-card" style="border-radius: var(--ula-radius-xl); padding: 26px; max-width: 480px; width: 100%;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
            <div style="display: flex; align-items: center; gap: 8px;">
                <span class="material-symbols-rounded" style="color: var(--ula-status-danger); font-size: 22px;">cancel</span>
                <h3 style="font-size: 17px; font-weight: 800; color: var(--ula-text-primary); margin: 0;">
                    {{ __('Reject Bank Transfer Request') }}
                </h3>
            </div>
            <button type="button" onclick="closeModal('rejectModal')" style="background: var(--ula-surface-page-alt); border: 1px solid var(--ula-border-subtle); width: 32px; height: 32px; border-radius: 50%; display: flex; align-items: center; justify-content: center; cursor: pointer; color: var(--ula-text-primary);">
                <span class="material-symbols-rounded" style="font-size: 16px;">close</span>
            </button>
        </div>

        <p style="font-size: 13px; color: var(--ula-text-secondary); margin-bottom: 16px;">
            {{ __('Please state the reason for rejecting this payment request. The organization admin will see this reason.') }}
        </p>

        <form method="POST" id="rejectForm" action="">
            @csrf
            <div class="form-group" style="margin-bottom: 20px;">
                <label class="form-label" style="font-size: 12px; font-weight: 700; color: var(--ula-text-secondary); margin-bottom: 6px; display: block;" for="reject_notes">
                    {{ __('Rejection Reason') }} <span style="color: var(--ula-status-danger);">*</span>
                </label>
                <textarea
                    id="reject_notes"
                    name="admin_notes"
                    rows="3"
                    class="form-input"
                    style="width: 100%; background: var(--ula-surface-page-alt); border: 1px solid var(--ula-border-subtle); border-radius: 10px; padding: 10px 14px; font-size: 13px; color: var(--ula-text-primary); outline: none; resize: vertical;"
                    placeholder="{{ __('e.g. Deposit slip is unreadable, amount does not match, or funds not received in bank account.') }}"
                    required
                ></textarea>
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 10px;">
                <button type="button" onclick="closeModal('rejectModal')" class="tactile-btn btn-secondary">
                    {{ __('Cancel') }}
                </button>
                <button type="submit" class="tactile-btn" style="background: var(--ula-status-danger); color: white; border: none; padding: 8px 18px; border-radius: var(--ula-radius-pill); display: inline-flex; align-items: center; gap: 6px; font-weight: 700;">
                    <span class="material-symbols-rounded" style="font-size: 16px;">cancel</span>
                    <span>{{ __('Reject Request') }}</span>
                </button>
            </div>
        </form>
    </div>
</div>

<!-- VIEW DETAILS MODAL -->
<div id="detailsModal" class="modal-overlay">
    <div class="modal-card" style="border-radius: var(--ula-radius-xl); padding: 26px; max-width: 500px; width: 100%;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
            <div style="display: flex; align-items: center; gap: 8px;">
                <span class="material-symbols-rounded" style="color: var(--ula-highlight-default); font-size: 22px;">receipt_long</span>
                <h3 style="font-size: 17px; font-weight: 800; color: var(--ula-text-primary); margin: 0;">
                    {{ __('Subscription Request Details') }}
                </h3>
            </div>
            <button type="button" onclick="closeModal('detailsModal')" style="background: var(--ula-surface-page-alt); border: 1px solid var(--ula-border-subtle); width: 32px; height: 32px; border-radius: 50%; display: flex; align-items: center; justify-content: center; cursor: pointer; color: var(--ula-text-primary);">
                <span class="material-symbols-rounded" style="font-size: 16px;">close</span>
            </button>
        </div>

        <div style="display: flex; flex-direction: column; gap: 10px; font-size: 13px;">
            <div style="display: flex; justify-content: space-between; padding-bottom: 8px; border-bottom: 1px solid var(--ula-border-subtle);">
                <span style="color: var(--ula-text-muted);">{{ __('Company') }}:</span>
                <strong id="detOrg" style="color: var(--ula-text-primary);"></strong>
            </div>
            <div style="display: flex; justify-content: space-between; padding-bottom: 8px; border-bottom: 1px solid var(--ula-border-subtle);">
                <span style="color: var(--ula-text-muted);">{{ __('Plan') }}:</span>
                <strong id="detPlan" style="color: var(--ula-text-primary);"></strong>
            </div>
            <div style="display: flex; justify-content: space-between; padding-bottom: 8px; border-bottom: 1px solid var(--ula-border-subtle);">
                <span style="color: var(--ula-text-muted);">{{ __('Amount') }}:</span>
                <strong id="detAmount" style="color: var(--ula-text-primary); font-family: 'IBM Plex Mono', monospace;"></strong>
            </div>
            <div style="display: flex; justify-content: space-between; padding-bottom: 8px; border-bottom: 1px solid var(--ula-border-subtle);">
                <span style="color: var(--ula-text-muted);">{{ __('Bank Name') }}:</span>
                <span id="detBank" style="color: var(--ula-text-primary);"></span>
            </div>
            <div style="display: flex; justify-content: space-between; padding-bottom: 8px; border-bottom: 1px solid var(--ula-border-subtle);">
                <span style="color: var(--ula-text-muted);">{{ __('Sender Name') }}:</span>
                <span id="detSender" style="color: var(--ula-text-primary);"></span>
            </div>
            <div style="display: flex; justify-content: space-between; padding-bottom: 8px; border-bottom: 1px solid var(--ula-border-subtle);">
                <span style="color: var(--ula-text-muted);">{{ __('Reference #') }}:</span>
                <span id="detRef" style="font-family: 'IBM Plex Mono', monospace; font-weight: 700; color: var(--ula-text-primary);"></span>
            </div>
            <div style="display: flex; justify-content: space-between; padding-bottom: 8px; border-bottom: 1px solid var(--ula-border-subtle);">
                <span style="color: var(--ula-text-muted);">{{ __('Status') }}:</span>
                <span id="detStatus" style="font-weight: 700;"></span>
            </div>
            <div style="padding-top: 4px;">
                <span style="color: var(--ula-text-muted); display: block; margin-bottom: 4px;">{{ __('Admin Remarks') }}:</span>
                <div id="detNotes" style="background: var(--ula-surface-page-alt); padding: 10px; border-radius: 8px; font-size: 12px; color: var(--ula-text-secondary);"></div>
            </div>
        </div>

        <div style="display: flex; justify-content: flex-end; margin-top: 20px;">
            <button type="button" onclick="closeModal('detailsModal')" class="tactile-btn btn-secondary">
                {{ __('Close') }}
            </button>
        </div>
    </div>
</div>

<script nonce="{{ $cspNonce ?? '' }}">
function openApproveModal(id, orgName, planName, amount) {
    document.getElementById('approveOrgName').innerText = orgName;
    document.getElementById('approvePlanName').innerText = planName;
    document.getElementById('approveAmount').innerText = amount;
    document.getElementById('approveForm').action = '/superadmin/subscriptions/' + id + '/approve';
    document.getElementById('approveModal').style.display = 'flex';
}

function openRejectModal(id, orgName) {
    document.getElementById('rejectForm').action = '/superadmin/subscriptions/' + id + '/reject';
    document.getElementById('rejectModal').style.display = 'flex';
}

function openDetailsModal(id, org, plan, amount, bank, sender, ref, status, notes) {
    document.getElementById('detOrg').innerText = org;
    document.getElementById('detPlan').innerText = plan;
    document.getElementById('detAmount').innerText = amount;
    document.getElementById('detBank').innerText = bank;
    document.getElementById('detSender').innerText = sender;
    document.getElementById('detRef').innerText = ref;
    document.getElementById('detStatus').innerText = status;
    document.getElementById('detNotes').innerText = notes || '{{ __("No remarks") }}';
    document.getElementById('detailsModal').style.display = 'flex';
}

function closeModal(modalId) {
    document.getElementById(modalId).style.display = 'none';
}

// Close modals when clicking outside
window.addEventListener('click', function(e) {
    ['approveModal', 'rejectModal', 'detailsModal'].forEach(id => {
        const modal = document.getElementById(id);
        if (modal && e.target === modal) {
            modal.style.display = 'none';
        }
    });
});
</script>
@endsection
