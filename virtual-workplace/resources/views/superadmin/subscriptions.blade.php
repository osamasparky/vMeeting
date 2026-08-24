@extends('superadmin.layout')

@section('title', __('Subscription Requests & Payments') . ' — ' . __('Super Admin Portal'))

@section('content')
<!-- Page Header -->
<div class="page-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; flex-wrap: wrap; gap: 16px;">
    <div>
        <h1 class="page-title" style="font-size: 24px; font-weight: 900; color: var(--text-primary); margin-bottom: 4px; display: flex; align-items: center; gap: 10px;">
            <span>💳</span> {{ __('Bank Transfer Payments & Subscription Requests') }}
        </h1>
        <p class="page-subtitle" style="font-size: 13px; color: var(--text-secondary);">
            {{ __('Review wire transfers, verify deposit slips, and approve subscription upgrades for client companies.') }}
        </p>
    </div>
</div>

<!-- KPI Cards Grid -->
<div class="kpi-grid" style="grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px; margin-bottom: 24px;">
    <div class="kpi-card" style="border-inline-start: 4px solid var(--status-warning);">
        <div class="kpi-header">
            <span class="kpi-title">{{ __('Pending Approvals') }}</span>
            <div class="kpi-icon-box">⏳</div>
        </div>
        <div class="kpi-value" style="color: var(--status-warning);">{{ $stats['pending'] }}</div>
        <div class="kpi-subtext">{{ __('Awaiting SuperAdmin review') }}</div>
    </div>

    <div class="kpi-card" style="border-inline-start: 4px solid var(--brand-forest);">
        <div class="kpi-header">
            <span class="kpi-title">{{ __('Approved Subscriptions') }}</span>
            <div class="kpi-icon-box">✓</div>
        </div>
        <div class="kpi-value" style="color: var(--brand-forest);">{{ $stats['approved'] }}</div>
        <div class="kpi-subtext">{{ __('Active & plan provisioned') }}</div>
    </div>

    <div class="kpi-card" style="border-inline-start: 4px solid var(--status-danger);">
        <div class="kpi-header">
            <span class="kpi-title">{{ __('Rejected Requests') }}</span>
            <div class="kpi-icon-box">✕</div>
        </div>
        <div class="kpi-value" style="color: var(--status-danger);">{{ $stats['rejected'] }}</div>
        <div class="kpi-subtext">{{ __('Declined due to invalid slip') }}</div>
    </div>

    <div class="kpi-card" style="border-inline-start: 4px solid var(--border-color);">
        <div class="kpi-header">
            <span class="kpi-title">{{ __('Total Payment Requests') }}</span>
            <div class="kpi-icon-box">📊</div>
        </div>
        <div class="kpi-value">{{ $stats['total'] }}</div>
        <div class="kpi-subtext">{{ __('All time wire transfer requests') }}</div>
    </div>
</div>

<!-- Filter Bar & Search -->
<div class="card" style="padding: 16px 20px; margin-bottom: 20px; border-radius: var(--radius-lg); border: 1px solid var(--border-color); background: var(--bg-surface); display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 14px;">
    <!-- Status Filter Pills -->
    <div style="display: flex; gap: 8px; flex-wrap: wrap;">
        <a href="{{ route('superadmin.subscriptions', ['status' => 'all', 'search' => $search]) }}" class="nav-badge-pill" style="text-decoration: none; padding: 6px 14px; {{ $statusFilter === 'all' ? 'background: var(--brand-forest); color: white;' : '' }}">
            {{ __('All') }} ({{ $stats['total'] }})
        </a>
        <a href="{{ route('superadmin.subscriptions', ['status' => 'pending', 'search' => $search]) }}" class="nav-badge-pill" style="text-decoration: none; padding: 6px 14px; {{ $statusFilter === 'pending' ? 'background: var(--status-warning); color: white;' : 'color: #996D12;' }}">
            ⏳ {{ __('Pending') }} ({{ $stats['pending'] }})
        </a>
        <a href="{{ route('superadmin.subscriptions', ['status' => 'approved', 'search' => $search]) }}" class="nav-badge-pill" style="text-decoration: none; padding: 6px 14px; {{ $statusFilter === 'approved' ? 'background: var(--brand-forest); color: white;' : 'color: var(--brand-forest);' }}">
            ✓ {{ __('Approved') }} ({{ $stats['approved'] }})
        </a>
        <a href="{{ route('superadmin.subscriptions', ['status' => 'rejected', 'search' => $search]) }}" class="nav-badge-pill" style="text-decoration: none; padding: 6px 14px; {{ $statusFilter === 'rejected' ? 'background: var(--status-danger); color: white;' : 'color: var(--status-danger);' }}">
            ✕ {{ __('Rejected') }} ({{ $stats['rejected'] }})
        </a>
    </div>

    <!-- Search Form -->
    <form method="GET" action="{{ route('superadmin.subscriptions') }}" style="display: flex; gap: 8px; margin: 0;">
        <input type="hidden" name="status" value="{{ $statusFilter }}">
        <input
            type="text"
            name="search"
            class="form-input"
            style="min-width: 260px;"
            placeholder="{{ __('Search by company, sender, ref #...') }}"
            value="{{ $search }}"
        >
        <button type="submit" class="tactile-btn btn-primary" style="padding: 8px 16px; font-size: 12px;">
            🔍 {{ __('Search') }}
        </button>
        @if($search)
            <a href="{{ route('superadmin.subscriptions', ['status' => $statusFilter]) }}" class="tactile-btn" style="padding: 8px 12px; font-size: 12px;">
                ✕
            </a>
        @endif
    </form>
</div>

<!-- Requests Table Card -->
<div class="card" style="padding: 0; overflow: hidden; border-radius: var(--radius-xl); border: 1px solid var(--border-color); background: var(--bg-surface); box-shadow: var(--shadow-card);">
    <div style="overflow-x: auto;">
        <table style="width: 100%; border-collapse: collapse; text-align: start; font-size: 13px;">
            <thead>
                <tr style="background: var(--bg-surface-subtle); border-bottom: 1px solid var(--border-color); color: var(--text-secondary); font-size: 11px; font-weight: 800; text-transform: uppercase;">
                    <th style="padding: 14px 18px;">{{ __('Company / Organization') }}</th>
                    <th style="padding: 14px 18px;">{{ __('Target Plan') }}</th>
                    <th style="padding: 14px 18px;">{{ __('Amount & Cycle') }}</th>
                    <th style="padding: 14px 18px;">{{ __('Transfer & Bank Details') }}</th>
                    <th style="padding: 14px 18px; text-align: center;">{{ __('Deposit Receipt') }}</th>
                    <th style="padding: 14px 18px; text-align: center;">{{ __('Status') }}</th>
                    <th style="padding: 14px 18px; text-align: center;">{{ __('Actions') }}</th>
                </tr>
            </thead>
            <tbody style="divide-y: 1px solid var(--border-color);">
                @forelse($subscriptionRequests as $req)
                <tr style="border-bottom: 1px solid var(--border-color); transition: background 0.15s;" onmouseover="this.style.background='var(--bg-surface-subtle)'" onmouseout="this.style.background=''">
                    <!-- Company Info -->
                    <td style="padding: 16px 18px;">
                        @if($req->organization)
                            <a href="{{ route('superadmin.companies.show', $req->organization_id) }}" style="font-weight: 900; color: var(--brand-forest); text-decoration: none; font-size: 14px; display: flex; align-items: center; gap: 6px;">
                                🏢 {{ $req->organization->name }}
                            </a>
                            <div style="font-size: 11px; color: var(--text-muted); margin-top: 2px;">
                                👤 {{ $req->user?->name ?? 'User' }} ({{ $req->user?->email ?? '—' }})
                            </div>
                        @else
                            <span style="color: var(--text-muted);">{{ __('Organization Deleted') }}</span>
                        @endif
                    </td>

                    <!-- Target Plan -->
                    <td style="padding: 16px 18px;">
                        <span class="badge-status badge-plan" style="font-size: 12px; font-weight: 900;">
                            💎 {{ $req->plan?->name ?? 'Plan' }}
                        </span>
                        <div style="font-size: 11px; color: var(--text-muted); margin-top: 4px;">
                            👥 {{ $req->plan?->seat_limit === 0 ? __('Unlimited') : $req->plan?->seat_limit }} {{ __('Seats') }}
                        </div>
                    </td>

                    <!-- Amount & Cycle -->
                    <td style="padding: 16px 18px;">
                        <div style="font-weight: 900; color: var(--text-primary); font-size: 15px;">
                            {{ number_format($req->amount, 2) }} <span style="font-size: 11px; font-weight: 700; color: var(--text-secondary);">{{ $req->currency }}</span>
                        </div>
                        <div style="font-size: 11px; color: var(--brand-forest); font-weight: 800; text-transform: uppercase;">
                            {{ $req->billing_cycle === 'yearly' ? '📅 ' . __('Yearly (12 Mo)') : '🔄 ' . __('Monthly') }}
                        </div>
                    </td>

                    <!-- Transfer & Bank Details -->
                    <td style="padding: 16px 18px;">
                        <div style="font-weight: 800; color: var(--text-primary);">
                            🏦 {{ $req->bank_name }}
                        </div>
                        <div style="font-size: 12px; color: var(--text-secondary); margin-top: 2px;">
                            <strong>{{ __('Sender') }}:</strong> {{ $req->sender_name }}
                        </div>
                        <div style="font-size: 11px; font-family: monospace; color: var(--brand-forest); font-weight: 800; margin-top: 2px;">
                            #{{ $req->transfer_reference }}
                        </div>
                        <div style="font-size: 10px; color: var(--text-muted); margin-top: 2px;">
                            📅 {{ $req->transfer_date ? $req->transfer_date->format('Y-m-d') : $req->created_at->format('Y-m-d') }}
                        </div>
                    </td>

                    <!-- Deposit Receipt -->
                    <td style="padding: 16px 18px; text-align: center;">
                        @if($req->receipt_path)
                            @php
                                $isPdf = str_ends_with(strtolower($req->receipt_path), '.pdf');
                            @endphp
                            <a
                                href="{{ route('superadmin.subscriptions.receipt', $req->id) }}"
                                target="_blank"
                                class="tactile-btn"
                                style="padding: 6px 12px; font-size: 11px; background: var(--bg-surface); text-decoration: none;"
                                title="{{ __('View Receipt Document') }}"
                            >
                                <span>{{ $isPdf ? '📄 PDF' : '🖼️ ' . __('Image') }}</span>
                            </a>
                        @else
                            <span style="font-size: 11px; color: var(--text-muted);">{{ __('No slip') }}</span>
                        @endif
                    </td>

                    <!-- Status -->
                    <td style="padding: 16px 18px; text-align: center;">
                        @if($req->status === 'pending')
                            <span class="badge-status" style="background: rgba(214, 162, 58, 0.2); color: #996D12; border-color: rgba(214, 162, 58, 0.4); font-weight: 900;">
                                ⏳ {{ __('Pending Approval') }}
                            </span>
                        @elseif($req->status === 'approved')
                            <span class="badge-status badge-active" style="font-weight: 900;">
                                ✓ {{ __('Approved & Active') }}
                            </span>
                            @if($req->reviewed_at)
                                <div style="font-size: 10px; color: var(--text-muted); margin-top: 3px;">
                                    {{ $req->reviewed_at->format('Y-m-d H:i') }}
                                </div>
                            @endif
                        @elseif($req->status === 'rejected')
                            <span class="badge-status badge-suspended" style="font-weight: 900;">
                                ✕ {{ __('Rejected') }}
                            </span>
                            @if($req->admin_notes)
                                <div style="font-size: 10px; color: #D96B5F; max-width: 140px; margin: 3px auto 0; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;" title="{{ $req->admin_notes }}">
                                    {{ $req->admin_notes }}
                                </div>
                            @endif
                        @else
                            <span class="badge-status" style="opacity: 0.6;">
                                {{ ucfirst($req->status) }}
                            </span>
                        @endif
                    </td>

                    <!-- Actions -->
                    <td style="padding: 16px 18px; text-align: center;">
                        @if($req->status === 'pending')
                            <div style="display: flex; justify-content: center; gap: 6px;">
                                <button
                                    type="button"
                                    onclick="openApproveModal('{{ $req->id }}', '{{ addslashes($req->organization?->name ?? 'Company') }}', '{{ addslashes($req->plan?->name ?? 'Plan') }}', '{{ number_format($req->amount, 2) }} {{ $req->currency }}')"
                                    class="tactile-btn btn-primary"
                                    style="padding: 6px 12px; font-size: 11px;"
                                >
                                    ✓ {{ __('Approve') }}
                                </button>

                                <button
                                    type="button"
                                    onclick="openRejectModal('{{ $req->id }}', '{{ addslashes($req->organization?->name ?? 'Company') }}')"
                                    class="tactile-btn"
                                    style="padding: 6px 10px; font-size: 11px; color: #D96B5F; border-color: rgba(217,107,95,0.3);"
                                >
                                    ✕
                                </button>
                            </div>
                        @else
                            <button
                                type="button"
                                onclick="openDetailsModal('{{ $req->id }}', '{{ addslashes($req->organization?->name ?? '') }}', '{{ addslashes($req->plan?->name ?? '') }}', '{{ number_format($req->amount, 2) }} {{ $req->currency }}', '{{ addslashes($req->bank_name) }}', '{{ addslashes($req->sender_name) }}', '{{ $req->transfer_reference }}', '{{ $req->status }}', '{{ addslashes($req->admin_notes ?? '') }}')"
                                class="tactile-btn"
                                style="padding: 5px 10px; font-size: 11px;"
                            >
                                🔍 {{ __('Details') }}
                            </button>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" style="text-align: center; padding: 40px; color: var(--text-muted);">
                        <div style="font-size: 32px; margin-bottom: 8px;">💳</div>
                        <div style="font-size: 14px; font-weight: 800;">{{ __('No subscription requests found') }}</div>
                        <div style="font-size: 12px;">{{ __('Incoming bank transfer payments from organizations will appear here.') }}</div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($subscriptionRequests->hasPages())
    <div style="padding: 16px 20px; border-top: 1px solid var(--border-color); background: var(--bg-surface-subtle);">
        {{ $subscriptionRequests->links() }}
    </div>
    @endif
</div>

<!-- ═══════════════════════════════════════════════════════════════
     APPROVE SUBSCRIPTION MODAL
     ═══════════════════════════════════════════════════════════════ -->
<div id="approveModal" class="modal-overlay">
    <div class="modal-card" style="max-width: 500px;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
            <h3 style="font-size: 18px; font-weight: 900; color: var(--brand-forest); display: flex; align-items: center; gap: 8px;">
                <span>✅</span> {{ __('Approve Plan & Activate Workspace') }}
            </h3>
            <button type="button" onclick="closeModal('approveModal')" style="background: none; border: none; font-size: 20px; cursor: pointer; color: var(--text-muted);">✕</button>
        </div>

        <p style="font-size: 13px; color: var(--text-secondary); margin-bottom: 16px;">
            {{ __('Are you sure you want to approve this bank transfer payment and immediately assign the subscription plan to the company?') }}
        </p>

        <div style="background: var(--bg-surface-subtle); border: 1px solid var(--border-color); border-radius: 12px; padding: 14px; margin-bottom: 18px;">
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 8px; font-size: 12px;">
                <div><span style="color: var(--text-muted);">{{ __('Company') }}:</span> <strong id="approveOrgName" style="color: var(--text-primary);">—</strong></div>
                <div><span style="color: var(--text-muted);">{{ __('Target Plan') }}:</span> <strong id="approvePlanName" style="color: var(--brand-forest);">—</strong></div>
                <div><span style="color: var(--text-muted);">{{ __('Amount') }}:</span> <strong id="approveAmount" style="color: var(--text-primary);">—</strong></div>
                <div><span style="color: var(--text-muted);">{{ __('Action') }}:</span> <strong style="color: #4F9B5F;">⚡ {{ __('Instant Activation') }}</strong></div>
            </div>
        </div>

        <form method="POST" id="approveForm" action="">
            @csrf
            <div class="form-group" style="margin-bottom: 20px;">
                <label class="form-label" style="font-size: 12px; font-weight: 800; margin-bottom: 6px; display: block;" for="approve_notes">
                    📝 {{ __('SuperAdmin Notes / Reference (Optional)') }}
                </label>
                <input
                    type="text"
                    id="approve_notes"
                    name="admin_notes"
                    class="form-input"
                    style="width: 100%;"
                    value="Approved & Verified Bank Transfer"
                    placeholder="{{ __('e.g. Verified with accounting statement #...') }}"
                >
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 10px;">
                <button type="button" onclick="closeModal('approveModal')" class="tactile-btn">
                    {{ __('Cancel') }}
                </button>
                <button type="submit" class="tactile-btn btn-primary">
                    ✓ {{ __('Confirm & Activate Plan') }}
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ═══════════════════════════════════════════════════════════════
     REJECT SUBSCRIPTION MODAL
     ═══════════════════════════════════════════════════════════════ -->
<div id="rejectModal" class="modal-overlay">
    <div class="modal-card" style="max-width: 500px;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
            <h3 style="font-size: 18px; font-weight: 900; color: #D96B5F; display: flex; align-items: center; gap: 8px;">
                <span>✕</span> {{ __('Reject Bank Transfer Request') }}
            </h3>
            <button type="button" onclick="closeModal('rejectModal')" style="background: none; border: none; font-size: 20px; cursor: pointer; color: var(--text-muted);">✕</button>
        </div>

        <p style="font-size: 13px; color: var(--text-secondary); margin-bottom: 16px;">
            {{ __('Please state the reason for rejecting this payment request. The organization admin will see this reason.') }}
        </p>

        <form method="POST" id="rejectForm" action="">
            @csrf
            <div class="form-group" style="margin-bottom: 20px;">
                <label class="form-label" style="font-size: 12px; font-weight: 800; margin-bottom: 6px; display: block;" for="reject_notes">
                    ⚠️ {{ __('Rejection Reason') }} <span style="color: #D96B5F;">*</span>
                </label>
                <textarea
                    id="reject_notes"
                    name="admin_notes"
                    rows="3"
                    class="form-input"
                    style="width: 100%; resize: vertical;"
                    placeholder="{{ __('e.g. Deposit slip is unreadable, amount does not match, or funds not received in bank account.') }}"
                    required
                ></textarea>
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 10px;">
                <button type="button" onclick="closeModal('rejectModal')" class="tactile-btn">
                    {{ __('Cancel') }}
                </button>
                <button type="submit" class="tactile-btn" style="background: #D96B5F; color: white; border: 1px solid #B54F44;">
                    ✕ {{ __('Reject Request') }}
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ═══════════════════════════════════════════════════════════════
     VIEW DETAILS MODAL
     ═══════════════════════════════════════════════════════════════ -->
<div id="detailsModal" class="modal-overlay">
    <div class="modal-card" style="max-width: 520px;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
            <h3 style="font-size: 18px; font-weight: 900; color: var(--text-primary);">
                📄 {{ __('Subscription Request Details') }}
            </h3>
            <button type="button" onclick="closeModal('detailsModal')" style="background: none; border: none; font-size: 20px; cursor: pointer; color: var(--text-muted);">✕</button>
        </div>

        <div style="display: flex; flex-direction: column; gap: 12px; font-size: 13px;">
            <div style="display: flex; justify-content: space-between; padding-bottom: 8px; border-bottom: 1px solid var(--border-color);">
                <span style="color: var(--text-muted);">{{ __('Company') }}:</span>
                <strong id="detOrg"></strong>
            </div>
            <div style="display: flex; justify-content: space-between; padding-bottom: 8px; border-bottom: 1px solid var(--border-color);">
                <span style="color: var(--text-muted);">{{ __('Plan') }}:</span>
                <strong id="detPlan" style="color: var(--brand-forest);"></strong>
            </div>
            <div style="display: flex; justify-content: space-between; padding-bottom: 8px; border-bottom: 1px solid var(--border-color);">
                <span style="color: var(--text-muted);">{{ __('Amount') }}:</span>
                <strong id="detAmount"></strong>
            </div>
            <div style="display: flex; justify-content: space-between; padding-bottom: 8px; border-bottom: 1px solid var(--border-color);">
                <span style="color: var(--text-muted);">{{ __('Bank Name') }}:</span>
                <span id="detBank"></span>
            </div>
            <div style="display: flex; justify-content: space-between; padding-bottom: 8px; border-bottom: 1px solid var(--border-color);">
                <span style="color: var(--text-muted);">{{ __('Sender Name') }}:</span>
                <span id="detSender"></span>
            </div>
            <div style="display: flex; justify-content: space-between; padding-bottom: 8px; border-bottom: 1px solid var(--border-color);">
                <span style="color: var(--text-muted);">{{ __('Reference #') }}:</span>
                <span id="detRef" style="font-family: monospace; font-weight: 800;"></span>
            </div>
            <div style="display: flex; justify-content: space-between; padding-bottom: 8px; border-bottom: 1px solid var(--border-color);">
                <span style="color: var(--text-muted);">{{ __('Status') }}:</span>
                <span id="detStatus" style="font-weight: 800;"></span>
            </div>
            <div style="padding-top: 4px;">
                <span style="color: var(--text-muted); display: block; margin-bottom: 4px;">{{ __('Admin Remarks') }}:</span>
                <div id="detNotes" style="background: var(--bg-surface-subtle); padding: 10px; border-radius: 8px; font-size: 12px;"></div>
            </div>
        </div>

        <div style="display: flex; justify-content: flex-end; margin-top: 20px;">
            <button type="button" onclick="closeModal('detailsModal')" class="tactile-btn">
                {{ __('Close') }}
            </button>
        </div>
    </div>
</div>

<script>
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
