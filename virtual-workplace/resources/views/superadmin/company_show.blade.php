@extends('superadmin.layout')

@section('title', $organization->name . ' — ' . __('Company Profile'))
@section('page_title', __('Company Profile'))

@section('content')
@php
    $plan = $organization->plan;
    $seatLimit = $plan?->seat_limit ?? 5;
    $isUnlimited = $seatLimit === 0;
    $memberCount = $stats['total_members'];
    $isSuspended = $organization->status === 'suspended';
    $ownerMember = $organization->members->whereIn('role.slug', ['company_admin', 'owner'])->first() ?? $organization->members->first();
    $ownerUser = $ownerMember?->user;
@endphp

<!-- ── Company Header Card ── -->
<div class="panel-card" style="margin-bottom: 24px; padding: 24px; border-radius: 20px;">
    <div style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 20px;">
        <!-- Left: Logo & Details -->
        <div style="display: flex; align-items: center; gap: 18px;">
            @if($organization->logo_url)
                <img src="{{ $organization->logo_url }}" alt="{{ $organization->name }}" style="width: 72px; height: 72px; border-radius: 18px; object-fit: cover; border: 2px solid var(--border-color); box-shadow: var(--shadow-soft-3d);">
            @else
                <div style="width: 72px; height: 72px; border-radius: 18px; background: var(--accent-gradient); color: white; display: flex; align-items: center; justify-content: center; font-size: 26px; font-weight: 900; box-shadow: var(--shadow-soft-3d);">
                    {{ strtoupper(substr($organization->name, 0, 2)) }}
                </div>
            @endif
            <div>
                <div style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap;">
                    <h2 style="font-size: 22px; font-weight: 900; color: var(--text-primary); margin: 0;">{{ $organization->name }}</h2>
                    @if($isSuspended)
                        <span class="badge-status badge-suspended">🛑 {{ __('Suspended') }}</span>
                    @else
                        <span class="badge-status badge-active">✅ {{ __('Active') }}</span>
                    @endif
                    <span class="badge-status badge-plan">💎 {{ $plan?->name ?? 'Free Plan' }}</span>
                </div>
                <div style="display: flex; align-items: center; gap: 14px; margin-top: 6px; font-size: 12px; color: var(--text-muted); flex-wrap: wrap;">
                    <span><strong>Slug:</strong> <code style="background: var(--bg-surface-subtle); padding: 2px 6px; border-radius: 6px; font-family: monospace;">{{ $organization->slug }}</code></span>
                    <span><strong>ID:</strong> <code style="background: var(--bg-surface-subtle); padding: 2px 6px; border-radius: 6px; font-family: monospace; font-size: 10px;">{{ $organization->id }}</code></span>
                    <span><strong>Created:</strong> {{ $organization->created_at?->format('M d, Y') }}</span>
                    <span><strong>Owner:</strong> {{ $ownerUser?->name ?? 'None' }} ({{ $ownerUser?->email ?? 'N/A' }})</span>
                </div>
            </div>
        </div>

        <!-- Right: Action Buttons Group -->
        <div style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
            <!-- Impersonate / Login as Company -->
            <form method="POST" action="{{ route('superadmin.companies.impersonate', $organization) }}" style="margin: 0;">
                @csrf
                <button type="submit" class="tactile-btn" style="background: linear-gradient(135deg, #2563EB, #1D4ED8); color: white; border: none; padding: 10px 18px; font-size: 13px; font-weight: 800; display: inline-flex; align-items: center; gap: 6px; box-shadow: 0 4px 12px rgba(37,99,235,0.35);" title="{{ __('Log in to this company dashboard directly as administrator') }}">
                    <span>⚡</span>
                    <span>{{ __('Login as Company (تسجيل دخول بالشركة)') }}</span>
                </button>
            </form>

            <!-- Change Plan Button -->
            <button onclick="openChangePlanModal()" class="tactile-btn btn-secondary" style="padding: 10px 14px; font-size: 12px;">
                💎 {{ __('Change Plan') }}
            </button>

            <!-- Edit Details Button -->
            <button onclick="openEditCompanyModal()" class="tactile-btn btn-secondary" style="padding: 10px 14px; font-size: 12px;">
                ✏️ {{ __('Edit Details') }}
            </button>

            <!-- Toggle Suspend Button -->
            <form method="POST" action="{{ route('superadmin.companies.toggle', $organization) }}" style="margin: 0;">
                @csrf
                <button type="submit" class="tactile-btn" style="padding: 10px 14px; font-size: 12px; color: {{ $isSuspended ? 'var(--brand-forest)' : '#D96B5F' }};">
                    {{ $isSuspended ? '▶️ ' . __('Activate') : '⏸️ ' . __('Suspend') }}
                </button>
            </form>

            <!-- Delete Company Button -->
            <button onclick="openDeleteCompanyModal()" class="tactile-btn" style="padding: 10px 14px; font-size: 12px; background: rgba(217, 107, 95, 0.15); color: #D96B5F; border: 1px solid rgba(217, 107, 95, 0.35);">
                🗑️ {{ __('Delete') }}
            </button>
        </div>
    </div>
</div>

<!-- ── Metrics Stat Grid ── -->
<div class="metrics-grid" style="grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); margin-bottom: 24px;">
    <!-- Stat 1: Members -->
    <div class="metric-card">
        <div class="metric-header">
            <span class="metric-title">{{ __('Members & Capacity') }}</span>
            <div class="metric-icon-badge" style="background: rgba(36, 92, 58, 0.12); color: var(--brand-forest);">👥</div>
        </div>
        <div class="metric-value">
            {{ $memberCount }} <span style="font-size: 14px; color: var(--text-muted); font-weight: 600;">/ {{ $isUnlimited ? '∞' : $seatLimit }}</span>
        </div>
        <div class="metric-trend" style="color: var(--text-secondary); font-size: 11px;">
            <span>🟢 {{ $stats['active_members'] }} {{ __('Active') }}</span> • 
            <span>✉️ {{ $stats['invited_members'] }} {{ __('Invited') }}</span> • 
            <span>🛑 {{ $stats['suspended_members'] }} {{ __('Suspended') }}</span>
        </div>
    </div>

    <!-- Stat 2: Departments & Teams -->
    <div class="metric-card">
        <div class="metric-header">
            <span class="metric-title">{{ __('Organization Structure') }}</span>
            <div class="metric-icon-badge" style="background: rgba(63, 125, 79, 0.12); color: var(--brand-sage);">🏛️</div>
        </div>
        <div class="metric-value">
            {{ $stats['departments_count'] }} <span style="font-size: 14px; color: var(--text-muted); font-weight: 600;">{{ __('Depts') }}</span>
        </div>
        <div class="metric-trend" style="color: var(--text-secondary); font-size: 11px;">
            <span>👥 {{ $stats['teams_count'] }} {{ __('Sub-Teams configured') }}</span>
        </div>
    </div>

    <!-- Stat 3: Workspace & Rooms -->
    <div class="metric-card">
        <div class="metric-header">
            <span class="metric-title">{{ __('Meeting Rooms & Spaces') }}</span>
            <div class="metric-icon-badge" style="background: rgba(37, 99, 235, 0.12); color: #2563EB);">🏢</div>
        </div>
        <div class="metric-value">
            {{ $stats['rooms_count'] }} <span style="font-size: 14px; color: var(--text-muted); font-weight: 600;">{{ __('Rooms') }}</span>
        </div>
        <div class="metric-trend" style="color: var(--text-secondary); font-size: 11px;">
            <span>🗺️ {{ $organization->floors->count() }} {{ __('Floor(s)') }} • {{ $organization->maps->count() }} {{ __('Map(s)') }}</span>
        </div>
    </div>

    <!-- Stat 4: Projects & Tasks -->
    <div class="metric-card">
        <div class="metric-header">
            <span class="metric-title">{{ __('Projects & Tasks') }}</span>
            <div class="metric-icon-badge" style="background: rgba(214, 162, 58, 0.12); color: var(--status-warning);">📋</div>
        </div>
        <div class="metric-value">
            {{ $stats['projects_count'] }} <span style="font-size: 14px; color: var(--text-muted); font-weight: 600;">{{ __('Projects') }}</span>
        </div>
        <div class="metric-trend" style="color: var(--text-secondary); font-size: 11px;">
            <span>✅ {{ $stats['tasks_count'] }} {{ __('Total Tasks Tracked') }}</span>
        </div>
    </div>
</div>

<!-- ── Navigation Tabs ── -->
<div style="display: flex; gap: 8px; margin-bottom: 20px; border-bottom: 2px solid var(--border-color); padding-bottom: 12px; overflow-x: auto;">
    <button onclick="switchTab('members')" id="tab-btn-members" class="tactile-btn tab-nav-btn active-tab" style="padding: 9px 18px; font-size: 13px;">
        👥 {{ __('Members & Staff') }} ({{ $organization->members->count() }})
    </button>
    <button onclick="switchTab('departments')" id="tab-btn-departments" class="tactile-btn tab-nav-btn" style="padding: 9px 18px; font-size: 13px;">
        🏛️ {{ __('Departments & Teams') }} ({{ $organization->departments->count() }})
    </button>
    <button onclick="switchTab('rooms')" id="tab-btn-rooms" class="tactile-btn tab-nav-btn" style="padding: 9px 18px; font-size: 13px;">
        🏢 {{ __('Rooms & Map Blueprint') }} ({{ $organization->rooms->count() }})
    </button>
    <button onclick="switchTab('plan')" id="tab-btn-plan" class="tactile-btn tab-nav-btn" style="padding: 9px 18px; font-size: 13px;">
        💎 {{ __('Plan & Quotas') }}
    </button>
    <button onclick="switchTab('payments')" id="tab-btn-payments" class="tactile-btn tab-nav-btn" style="padding: 9px 18px; font-size: 13px;">
        💳 {{ __('Payments & Wire Transfers') }} ({{ $organization->subscriptionRequests->count() }})
    </button>
    <button onclick="switchTab('audit')" id="tab-btn-audit" class="tactile-btn tab-nav-btn" style="padding: 9px 18px; font-size: 13px;">
        📜 {{ __('Activity Logs') }}
    </button>
</div>

<!-- ═══════════════════════════════════════════════════════
     TAB 1: MEMBERS & STAFF
     ═══════════════════════════════════════════════════════ -->
<div id="tab-content-members" class="tab-pane" style="display: block;">
    <div class="panel-card">
        <div class="panel-header">
            <div class="panel-title">
                <span>👥</span>
                <span>{{ __('Company Team Members Roster') }}</span>
            </div>
            <span style="font-size: 12px; color: var(--text-muted); font-weight: 700;">
                {{ $organization->members->count() }} {{ __('registered users') }}
            </span>
        </div>

        <div class="data-table-container">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>{{ __('User') }}</th>
                        <th>{{ __('Role') }}</th>
                        <th>{{ __('Department / Team') }}</th>
                        <th>{{ __('Job Title') }}</th>
                        <th>{{ __('Status') }}</th>
                        <th>{{ __('Joined Date') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($organization->members as $m)
                    <tr>
                        <td>
                            <div style="display: flex; align-items: center; gap: 10px;">
                                @if($m->user?->avatar_url)
                                    <img src="{{ $m->user->avatar_url }}" style="width: 32px; height: 32px; border-radius: 50%; object-fit: cover;">
                                @else
                                    <div style="width: 32px; height: 32px; border-radius: 50%; background: var(--accent-gradient); color: white; display: flex; align-items: center; justify-content: center; font-size: 11px; font-weight: 900;">
                                        {{ strtoupper(substr($m->user?->name ?? 'U', 0, 2)) }}
                                    </div>
                                @endif
                                <div>
                                    <strong style="color: var(--text-primary); font-size: 13px;">{{ $m->user?->name ?? 'Unnamed User' }}</strong>
                                    <div style="font-size: 11px; color: var(--text-muted);">{{ $m->user?->email }}</div>
                                </div>
                            </div>
                        </td>
                        <td>
                            <span class="badge-status" style="background: rgba(36, 92, 58, 0.12); color: var(--brand-forest);">
                                🛡️ {{ $m->role?->name ?? 'Member' }}
                            </span>
                        </td>
                        <td>
                            <div style="font-weight: 700; color: var(--text-primary); font-size: 12px;">{{ $m->user?->profile?->department?->name ?? '—' }}</div>
                            <div style="font-size: 11px; color: var(--text-muted);">{{ $m->user?->profile?->team?->name ?? '—' }}</div>
                        </td>
                        <td>
                            <span style="font-size: 12px; color: var(--text-secondary); font-weight: 600;">{{ $m->user?->profile?->job_title ?: '—' }}</span>
                        </td>
                        <td>
                            @if($m->status === 'active')
                                <span class="badge-status badge-active">🟢 {{ __('Active') }}</span>
                            @elseif($m->status === 'invited')
                                <span class="badge-status badge-plan">✉️ {{ __('Invited') }}</span>
                            @else
                                <span class="badge-status badge-suspended">🛑 {{ __('Suspended') }}</span>
                            @endif
                        </td>
                        <td>
                            <span style="font-size: 11px; color: var(--text-muted);">{{ $m->created_at?->format('M d, Y H:i') }}</span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" style="text-align: center; color: var(--text-muted); padding: 30px;">
                            {{ __('No members found in this organization.') }}
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- ═══════════════════════════════════════════════════════
     TAB 2: DEPARTMENTS & TEAMS
     ═══════════════════════════════════════════════════════ -->
<div id="tab-content-departments" class="tab-pane" style="display: none;">
    <div class="panel-card">
        <div class="panel-header">
            <div class="panel-title">
                <span>🏛️</span>
                <span>{{ __('Organizational Hierarchy') }}</span>
            </div>
        </div>

        <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 16px;">
            @forelse($organization->departments as $dept)
            <div style="background: var(--bg-surface-subtle); border: 1px solid var(--border-color); border-radius: 14px; padding: 18px; box-shadow: var(--shadow-soft-3d);">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
                    <div style="display: flex; align-items: center; gap: 8px;">
                        <span style="font-size: 20px;">🏛️</span>
                        <strong style="font-size: 15px; color: var(--text-primary);">{{ $dept->name }}</strong>
                    </div>
                    <span class="badge-status" style="background: var(--bg-card); color: var(--brand-forest); font-size: 11px;">
                        👥 {{ $dept->userProfiles->count() }} {{ __('Staff') }}
                    </span>
                </div>

                <div style="font-size: 11px; font-weight: 800; color: var(--text-muted); text-transform: uppercase; margin-bottom: 8px;">
                    {{ __('Sub-Teams') }} ({{ $dept->teams->count() }}):
                </div>

                @if($dept->teams->count() > 0)
                    <div style="display: flex; flex-wrap: wrap; gap: 6px;">
                        @foreach($dept->teams as $tm)
                            <span style="background: var(--bg-card); border: 1px solid var(--border-color); padding: 4px 10px; border-radius: 8px; font-size: 12px; font-weight: 700; color: var(--text-secondary);">
                                👥 {{ $tm->name }}
                            </span>
                        @endforeach
                    </div>
                @else
                    <span style="font-size: 12px; color: var(--text-muted); font-style: italic;">{{ __('No sub-teams created yet.') }}</span>
                @endif
            </div>
            @empty
            <div style="grid-column: 1/-1; text-align: center; color: var(--text-muted); padding: 40px;">
                {{ __('No departments configured in this organization.') }}
            </div>
            @endforelse
        </div>
    </div>
</div>

<!-- ═══════════════════════════════════════════════════════
     TAB 3: ROOMS & OFFICE BLUEPRINT
     ═══════════════════════════════════════════════════════ -->
<div id="tab-content-rooms" class="tab-pane" style="display: none;">
    <div class="panel-card">
        <div class="panel-header">
            <div class="panel-title">
                <span>🏢</span>
                <span>{{ __('Office Spaces & Meeting Rooms') }}</span>
            </div>
        </div>

        <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 16px;">
            @forelse($organization->rooms as $room)
            <div style="background: var(--bg-surface-subtle); border: 1px solid var(--border-color); border-radius: 14px; padding: 18px; box-shadow: var(--shadow-soft-3d);">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                    <strong style="font-size: 14px; color: var(--text-primary); display: flex; align-items: center; gap: 6px;">
                        <span>🚪</span> {{ $room->name }}
                    </strong>
                    <span class="badge-status badge-plan" style="font-size: 10px;">
                        {{ ucfirst($room->type ?? 'Meeting') }}
                    </span>
                </div>
                <div style="font-size: 12px; color: var(--text-secondary); margin-bottom: 4px;">
                    <strong>Capacity:</strong> {{ $room->capacity ?? 10 }} {{ __('seats') }}
                </div>
                <div style="font-size: 11px; color: var(--text-muted); font-family: monospace;">
                    Bounds: X:{{ $room->bounds['x'] ?? 0 }}, Y:{{ $room->bounds['y'] ?? 0 }}, W:{{ $room->bounds['width'] ?? 0 }}, H:{{ $room->bounds['height'] ?? 0 }}
                </div>
            </div>
            @empty
            <div style="grid-column: 1/-1; text-align: center; color: var(--text-muted); padding: 40px;">
                {{ __('No rooms configured for this office floor.') }}
            </div>
            @endforelse
        </div>
    </div>
</div>

<!-- ═══════════════════════════════════════════════════════
     TAB 4: PLAN & RESOURCE QUOTAS
     ═══════════════════════════════════════════════════════ -->
<div id="tab-content-plan" class="tab-pane" style="display: none;">
    <div class="panel-card">
        <div class="panel-header">
            <div class="panel-title">
                <span>💎</span>
                <span>{{ __('Subscription Tier & Feature Entitlements') }}</span>
            </div>
            <button onclick="openChangePlanModal()" class="tactile-btn btn-primary" style="padding: 7px 14px; font-size: 12px;">
                💎 {{ __('Change Plan') }}
            </button>
        </div>

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 20px;">
            <div style="background: var(--bg-surface-subtle); border: 1px solid var(--border-color); border-radius: 16px; padding: 22px;">
                <h4 style="margin: 0 0 12px 0; font-size: 16px; font-weight: 900; color: var(--text-primary);">💎 {{ $plan?->name ?? 'Free Tier' }}</h4>
                <div style="font-size: 28px; font-weight: 900; color: var(--brand-forest); margin-bottom: 12px;">
                    ${{ number_format($plan?->price ?? 0, 2) }} <span style="font-size: 14px; color: var(--text-muted); font-weight: 600;">/ {{ $plan?->billing_interval ?? 'month' }}</span>
                </div>
                <p style="font-size: 13px; color: var(--text-secondary); line-height: 1.5; margin: 0 0 16px 0;">
                    {{ $plan?->description ?? __('Basic standard tier with core workspace features.') }}
                </p>
                <div style="display: flex; flex-direction: column; gap: 8px; font-size: 12px; font-weight: 700; color: var(--text-secondary);">
                    <div>👥 <strong>{{ __('Seat Limit') }}:</strong> {{ $isUnlimited ? __('Unlimited') : $seatLimit . ' ' . __('users') }}</div>
                    <div>🏢 <strong>{{ __('Max Rooms') }}:</strong> {{ $plan?->max_rooms ?? 10 }} {{ __('rooms') }}</div>
                    <div>📁 <strong>{{ __('Storage Limit') }}:</strong> {{ $plan?->storage_limit_mb ? ($plan->storage_limit_mb / 1024) . ' GB' : '1 GB' }}</div>
                    <div>📹 <strong>{{ __('Meeting Recordings') }}:</strong> {{ ($plan?->features['recordings'] ?? true) ? '✅ Enabled' : '❌ Disabled' }}</div>
                    <div>📊 <strong>{{ __('Advanced Analytics') }}:</strong> {{ ($plan?->features['analytics'] ?? true) ? '✅ Enabled' : '❌ Disabled' }}</div>
                </div>
            </div>

            <div style="background: var(--bg-surface-subtle); border: 1px solid var(--border-color); border-radius: 16px; padding: 22px;">
                <h4 style="margin: 0 0 12px 0; font-size: 16px; font-weight: 900; color: var(--text-primary);">⚙️ {{ __('Company System Settings') }}</h4>
                <div style="display: flex; flex-direction: column; gap: 10px; font-size: 13px; color: var(--text-secondary);">
                    <div><strong>Timezone:</strong> {{ $organization->timezone ?: 'UTC' }}</div>
                    <div><strong>Guest Access:</strong> {{ ($organization->settings?->allow_guest_access ?? true) ? '✅ Enabled' : '❌ Disabled' }}</div>
                    <div><strong>Screen Sharing:</strong> {{ ($organization->settings?->allow_screen_share ?? true) ? '✅ Enabled' : '❌ Disabled' }}</div>
                    <div><strong>Max Simultaneous Guests:</strong> {{ $organization->settings?->max_guests_per_room ?? 15 }}</div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ═══════════════════════════════════════════════════════
     TAB 5: BANK PAYMENTS & SUBSCRIPTION REQUESTS
     ═══════════════════════════════════════════════════════ -->
<div id="tab-content-payments" class="tab-pane" style="display: none;">
    <div class="panel-card">
        <div class="panel-header">
            <div class="panel-title">
                <span>💳</span>
                <span>{{ __('Bank Transfer Payments & Upgrade Requests') }}</span>
            </div>
            <a href="{{ route('superadmin.subscriptions') }}" class="tactile-btn btn-secondary" style="padding: 6px 12px; font-size: 11px;">
                {{ __('All System Subscriptions') }} →
            </a>
        </div>

        <div class="data-table-container">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>{{ __('Requested Plan') }}</th>
                        <th>{{ __('Amount') }}</th>
                        <th>{{ __('Bank & Sender') }}</th>
                        <th>{{ __('Transfer Ref #') }}</th>
                        <th>{{ __('Receipt Slip') }}</th>
                        <th>{{ __('Status') }}</th>
                        <th>{{ __('Date') }}</th>
                        <th>{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($organization->subscriptionRequests as $req)
                    <tr>
                        <td>
                            <span class="badge-status badge-plan">💎 {{ $req->plan?->name ?? 'Plan' }}</span>
                        </td>
                        <td>
                            <strong>{{ number_format($req->amount, 2) }} {{ $req->currency }}</strong>
                            <div style="font-size: 10px; color: var(--text-muted); text-transform: uppercase;">{{ $req->billing_cycle }}</div>
                        </td>
                        <td>
                            <div style="font-weight: 800;">🏦 {{ $req->bank_name }}</div>
                            <div style="font-size: 11px; color: var(--text-muted);">👤 {{ $req->sender_name }}</div>
                        </td>
                        <td>
                            <code style="font-family: monospace; font-weight: 800; color: var(--brand-forest);">#{{ $req->transfer_reference }}</code>
                        </td>
                        <td>
                            @if($req->receipt_path)
                                <a href="{{ route('superadmin.subscriptions.receipt', $req->id) }}" target="_blank" class="tactile-btn" style="padding: 4px 8px; font-size: 11px; text-decoration: none;">
                                    📄 {{ __('View') }}
                                </a>
                            @else
                                <span style="color: var(--text-muted); font-size: 11px;">—</span>
                            @endif
                        </td>
                        <td>
                            @if($req->status === 'pending')
                                <span class="badge-status" style="background: rgba(214, 162, 58, 0.2); color: #996D12; border-color: rgba(214, 162, 58, 0.4);">
                                    ⏳ {{ __('Pending') }}
                                </span>
                            @elseif($req->status === 'approved')
                                <span class="badge-status badge-active">✓ {{ __('Approved') }}</span>
                            @elseif($req->status === 'rejected')
                                <span class="badge-status badge-suspended">✕ {{ __('Rejected') }}</span>
                            @else
                                <span class="badge-status">{{ ucfirst($req->status) }}</span>
                            @endif
                        </td>
                        <td style="font-size: 11px; color: var(--text-muted);">
                            {{ $req->created_at->format('Y-m-d H:i') }}
                        </td>
                        <td>
                            @if($req->status === 'pending')
                                <div style="display: flex; gap: 6px;">
                                    <form method="POST" action="{{ route('superadmin.subscriptions.approve', $req->id) }}" onsubmit="return confirm('{{ __('Approve this transfer and activate the plan for this company?') }}');" style="margin: 0;">
                                        @csrf
                                        <button type="submit" class="tactile-btn btn-primary" style="padding: 4px 8px; font-size: 11px;">
                                            ✓ {{ __('Approve') }}
                                        </button>
                                    </form>
                                    <form method="POST" action="{{ route('superadmin.subscriptions.reject', $req->id) }}" onsubmit="return confirm('{{ __('Reject this transfer request?') }}');" style="margin: 0;">
                                        @csrf
                                        <input type="hidden" name="admin_notes" value="Rejected from company profile">
                                        <button type="submit" class="tactile-btn" style="padding: 4px 8px; font-size: 11px; color: #D96B5F; border-color: rgba(217,107,95,0.3);">
                                            ✕
                                        </button>
                                    </form>
                                </div>
                            @else
                                <span style="font-size: 11px; color: var(--text-muted);">—</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" style="text-align: center; color: var(--text-muted); padding: 30px;">
                            {{ __('No wire transfer payment requests recorded for this company.') }}
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- ═══════════════════════════════════════════════════════
     TAB 6: AUDIT LOGS
     ═══════════════════════════════════════════════════════ -->
<div id="tab-content-audit" class="tab-pane" style="display: none;">
    <div class="panel-card">
        <div class="panel-header">
            <div class="panel-title">
                <span>📜</span>
                <span>{{ __('Recent Activity & Audit Trail') }}</span>
            </div>
        </div>

        <div class="data-table-container">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>{{ __('Event') }}</th>
                        <th>{{ __('Actor / User') }}</th>
                        <th>{{ __('IP Address') }}</th>
                        <th>{{ __('Timestamp') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($organization->auditLogs as $log)
                    <tr>
                        <td>
                            <strong style="color: var(--text-primary); font-size: 12px; font-family: monospace;">{{ $log->event }}</strong>
                        </td>
                        <td>
                            <span style="font-size: 12px; color: var(--text-secondary);">{{ $log->user?->name ?? 'System' }}</span>
                        </td>
                        <td>
                            <code style="font-size: 11px; color: var(--text-muted);">{{ $log->ip_address ?: '—' }}</code>
                        </td>
                        <td>
                            <span style="font-size: 11px; color: var(--text-muted);">{{ $log->created_at?->format('Y-m-d H:i:s') }}</span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" style="text-align: center; color: var(--text-muted); padding: 30px;">
                            {{ __('No audit logs recorded for this company yet.') }}
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- ═══════════════════════════════════════════════════════
     MODALS
     ═══════════════════════════════════════════════════════ -->

<!-- 1. Change Plan Modal -->
<div id="changePlanModal" class="modal-overlay">
    <div class="modal-card" style="border-radius: 24px; padding: 26px;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <h3 style="font-size: 17px; font-weight: 900; color: var(--text-primary);">💎 {{ __('Change Subscription Plan') }}</h3>
            <button onclick="closeChangePlanModal()" style="background: var(--bg-surface-subtle); border: 1px solid var(--border-color); width: 32px; height: 32px; border-radius: 50%; display: flex; align-items: center; justify-content: center; cursor: pointer; color: var(--text-primary); font-weight: 800;">✕</button>
        </div>

        <form method="POST" action="{{ route('superadmin.companies.plan', $organization) }}">
            @csrf
            <div style="margin-bottom: 22px;">
                <label style="display: block; font-size: 12px; font-weight: 800; color: var(--text-secondary); margin-bottom: 8px;">
                    {{ __('Select New Subscription Tier') }}
                </label>
                <select name="plan_id" style="width: 100%; background: var(--bg-surface-subtle); border: 1px solid var(--border-color); border-radius: 12px; padding: 12px; color: var(--text-primary); font-size: 13px; outline: none; font-weight: 700; box-shadow: var(--shadow-inset-3d);">
                    @foreach($allPlans as $p)
                        <option value="{{ $p->id }}" {{ $organization->plan_id == $p->id ? 'selected' : '' }}>
                            💎 {{ $p->name }} — {{ $p->seat_limit === 0 ? 'Unlimited' : $p->seat_limit }} Users (${{ number_format($p->price, 2) }}/mo)
                        </option>
                    @endforeach
                </select>
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 10px;">
                <button type="button" onclick="closeChangePlanModal()" class="tactile-btn btn-secondary">{{ __('Cancel') }}</button>
                <button type="submit" class="tactile-btn btn-primary">💾 {{ __('Save Changes') }}</button>
            </div>
        </form>
    </div>
</div>

<!-- 2. Edit Company Details Modal -->
<div id="editCompanyModal" class="modal-overlay">
    <div class="modal-card" style="border-radius: 24px; padding: 26px; max-width: 540px;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <h3 style="font-size: 17px; font-weight: 900; color: var(--text-primary);">✏️ {{ __('Edit Company Details') }}</h3>
            <button onclick="closeEditCompanyModal()" style="background: var(--bg-surface-subtle); border: 1px solid var(--border-color); width: 32px; height: 32px; border-radius: 50%; display: flex; align-items: center; justify-content: center; cursor: pointer; color: var(--text-primary); font-weight: 800;">✕</button>
        </div>

        <form method="POST" action="{{ route('superadmin.companies.update', $organization) }}">
            @csrf
            @method('PUT')

            <div style="margin-bottom: 16px;">
                <label style="display: block; font-size: 12px; font-weight: 800; color: var(--text-secondary); margin-bottom: 6px;">{{ __('Company Name') }}</label>
                <input type="text" name="name" value="{{ $organization->name }}" required style="width: 100%; background: var(--bg-surface-subtle); border: 1px solid var(--border-color); border-radius: 12px; padding: 12px; color: var(--text-primary); font-size: 13px; font-weight: 700; outline: none; box-shadow: var(--shadow-inset-3d);">
            </div>

            <div style="margin-bottom: 16px;">
                <label style="display: block; font-size: 12px; font-weight: 800; color: var(--text-secondary); margin-bottom: 6px;">{{ __('Slug') }}</label>
                <input type="text" name="slug" value="{{ $organization->slug }}" required style="width: 100%; background: var(--bg-surface-subtle); border: 1px solid var(--border-color); border-radius: 12px; padding: 12px; color: var(--text-primary); font-size: 13px; font-family: monospace; outline: none; box-shadow: var(--shadow-inset-3d);">
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px; margin-bottom: 16px;">
                <div>
                    <label style="display: block; font-size: 12px; font-weight: 800; color: var(--text-secondary); margin-bottom: 6px;">{{ __('Status') }}</label>
                    <select name="status" style="width: 100%; background: var(--bg-surface-subtle); border: 1px solid var(--border-color); border-radius: 12px; padding: 12px; color: var(--text-primary); font-size: 13px; font-weight: 700; outline: none;">
                        <option value="active" {{ $organization->status === 'active' ? 'selected' : '' }}>🟢 Active</option>
                        <option value="suspended" {{ $organization->status === 'suspended' ? 'selected' : '' }}>🛑 Suspended</option>
                        <option value="trial" {{ $organization->status === 'trial' ? 'selected' : '' }}>🟡 Trial</option>
                    </select>
                </div>
                <div>
                    <label style="display: block; font-size: 12px; font-weight: 800; color: var(--text-secondary); margin-bottom: 6px;">{{ __('Plan') }}</label>
                    <select name="plan_id" style="width: 100%; background: var(--bg-surface-subtle); border: 1px solid var(--border-color); border-radius: 12px; padding: 12px; color: var(--text-primary); font-size: 13px; font-weight: 700; outline: none;">
                        @foreach($allPlans as $p)
                            <option value="{{ $p->id }}" {{ $organization->plan_id == $p->id ? 'selected' : '' }}>{{ $p->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div style="margin-bottom: 22px;">
                <label style="display: block; font-size: 12px; font-weight: 800; color: var(--text-secondary); margin-bottom: 6px;">{{ __('Timezone') }}</label>
                <input type="text" name="timezone" value="{{ $organization->timezone ?: 'UTC' }}" style="width: 100%; background: var(--bg-surface-subtle); border: 1px solid var(--border-color); border-radius: 12px; padding: 12px; color: var(--text-primary); font-size: 13px; font-weight: 700; outline: none; box-shadow: var(--shadow-inset-3d);">
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 10px;">
                <button type="button" onclick="closeEditCompanyModal()" class="tactile-btn btn-secondary">{{ __('Cancel') }}</button>
                <button type="submit" class="tactile-btn btn-primary">💾 {{ __('Save Changes') }}</button>
            </div>
        </form>
    </div>
</div>

<!-- 3. Delete Company Modal -->
<div id="deleteCompanyModal" class="modal-overlay">
    <div class="modal-card" style="border-radius: 24px; padding: 26px; max-width: 480px;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
            <h3 style="font-size: 17px; font-weight: 900; color: #D96B5F;">⚠️ {{ __('Delete Company') }}</h3>
            <button onclick="closeDeleteCompanyModal()" style="background: var(--bg-surface-subtle); border: 1px solid var(--border-color); width: 32px; height: 32px; border-radius: 50%; display: flex; align-items: center; justify-content: center; cursor: pointer; color: var(--text-primary); font-weight: 800;">✕</button>
        </div>

        <p style="font-size: 13px; color: var(--text-secondary); line-height: 1.6; margin-bottom: 20px;">
            {{ __('Are you sure you want to permanently delete') }} <strong>{{ $organization->name }}</strong>? {{ __('This will remove all associated members, departments, rooms, and files. This action cannot be undone.') }}
        </p>

        <form method="POST" action="{{ route('superadmin.companies.delete', $organization) }}">
            @csrf
            @method('DELETE')
            <div style="display: flex; justify-content: flex-end; gap: 10px;">
                <button type="button" onclick="closeDeleteCompanyModal()" class="tactile-btn btn-secondary">{{ __('Cancel') }}</button>
                <button type="submit" class="tactile-btn" style="background: #D96B5F; color: white; border: none; font-weight: 800;">
                    🗑️ {{ __('Delete Permanently') }}
                </button>
            </div>
        </form>
    </div>
</div>

@endsection

@section('scripts')
<style>
    .tab-nav-btn {
        background: transparent;
        color: var(--text-muted);
        border: none;
        border-radius: 12px;
        font-weight: 800;
        cursor: pointer;
        transition: all 0.2s ease;
    }
    .tab-nav-btn:hover {
        background: var(--bg-surface-subtle);
        color: var(--text-primary);
    }
    .tab-nav-btn.active-tab {
        background: var(--accent-gradient);
        color: white !important;
        box-shadow: var(--shadow-tactile-btn);
    }
</style>

<script>
    function switchTab(tabId) {
        document.querySelectorAll('.tab-pane').forEach(el => el.style.display = 'none');
        document.querySelectorAll('.tab-nav-btn').forEach(btn => btn.classList.remove('active-tab'));

        const targetPane = document.getElementById(`tab-content-${tabId}`);
        const targetBtn = document.getElementById(`tab-btn-${tabId}`);

        if (targetPane) targetPane.style.display = 'block';
        if (targetBtn) targetBtn.classList.add('active-tab');
    }

    function openChangePlanModal() {
        document.getElementById('changePlanModal').style.display = 'flex';
    }
    function closeChangePlanModal() {
        document.getElementById('changePlanModal').style.display = 'none';
    }

    function openEditCompanyModal() {
        document.getElementById('editCompanyModal').style.display = 'flex';
    }
    function closeEditCompanyModal() {
        document.getElementById('editCompanyModal').style.display = 'none';
    }

    function openDeleteCompanyModal() {
        document.getElementById('deleteCompanyModal').style.display = 'flex';
    }
    function closeDeleteCompanyModal() {
        document.getElementById('deleteCompanyModal').style.display = 'none';
    }
</script>
@endsection
