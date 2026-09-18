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

<!-- Company Header Card -->
<div class="panel-card" style="margin-bottom: 24px; padding: 24px; border-radius: var(--ula-radius-xl); background: var(--ula-surface-card); border: 1px solid var(--ula-border-subtle); box-shadow: var(--ula-shadow-sm);">
    <div style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 20px;">
        <!-- Left: Logo & Details -->
        <div style="display: flex; align-items: center; gap: 18px;">
            @if($organization->logo_url)
                <img src="{{ $organization->logo_url }}" alt="{{ $organization->name }}" style="width: 72px; height: 72px; border-radius: 18px; object-fit: cover; border: 1px solid var(--ula-border-subtle); box-shadow: var(--ula-shadow-sm);">
            @else
                <div style="width: 72px; height: 72px; border-radius: 18px; background: var(--ula-palm-900); color: white; display: flex; align-items: center; justify-content: center; font-size: 26px; font-weight: 800; box-shadow: var(--ula-shadow-sm);">
                    {{ strtoupper(substr($organization->name, 0, 2)) }}
                </div>
            @endif
            <div>
                <div style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap;">
                    <h2 style="font-size: 22px; font-weight: 800; color: var(--ula-text-primary); margin: 0;">{{ $organization->name }}</h2>
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
                    <span class="badge-status badge-plan" style="display: inline-flex; align-items: center; gap: 4px; font-size: 11px; padding: 3px 8px; border-radius: 6px; background: rgba(211,165,83,0.12); color: var(--ula-gold-600); font-weight: 700;">
                        <span class="material-symbols-rounded" style="font-size: 13px;">diamond</span>
                        <span>{{ $plan?->name ?? 'Free Plan' }}</span>
                    </span>
                </div>
                <div style="display: flex; align-items: center; gap: 14px; margin-top: 6px; font-size: 12px; color: var(--ula-text-muted); flex-wrap: wrap;">
                    <span><strong>Slug:</strong> <code style="background: var(--ula-surface-page-alt); padding: 2px 6px; border-radius: 6px; font-family: 'IBM Plex Mono', monospace;">{{ $organization->slug }}</code></span>
                    <span><strong>ID:</strong> <code style="background: var(--ula-surface-page-alt); padding: 2px 6px; border-radius: 6px; font-family: 'IBM Plex Mono', monospace; font-size: 10px;">{{ $organization->id }}</code></span>
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
                <button type="submit" class="tactile-btn btn-primary" style="padding: 9px 18px; font-size: 12px; border-radius: var(--ula-radius-pill); display: inline-flex; align-items: center; gap: 6px;" title="{{ __('Log in to this company dashboard directly as administrator') }}">
                    <span class="material-symbols-rounded" style="font-size: 16px;">bolt</span>
                    <span>{{ __('Login as Company') }}</span>
                </button>
            </form>

            <!-- Change Plan Button -->
            <button onclick="openChangePlanModal()" class="tactile-btn btn-secondary" style="padding: 9px 14px; font-size: 12px; border-radius: var(--ula-radius-pill); display: inline-flex; align-items: center; gap: 4px;">
                <span class="material-symbols-rounded" style="font-size: 15px;">diamond</span>
                <span>{{ __('Change Plan') }}</span>
            </button>

            <!-- Edit Details Button -->
            <button onclick="openEditCompanyModal()" class="tactile-btn btn-secondary" style="padding: 9px 14px; font-size: 12px; border-radius: var(--ula-radius-pill); display: inline-flex; align-items: center; gap: 4px;">
                <span class="material-symbols-rounded" style="font-size: 15px;">edit</span>
                <span>{{ __('Edit Details') }}</span>
            </button>

            <!-- Toggle Suspend Button -->
            <form method="POST" action="{{ route('superadmin.companies.toggle', $organization) }}" style="margin: 0;">
                @csrf
                <button type="submit" class="tactile-btn" style="padding: 9px 14px; font-size: 12px; border-radius: var(--ula-radius-pill); display: inline-flex; align-items: center; gap: 4px; color: {{ $isSuspended ? 'var(--ula-status-success)' : 'var(--ula-status-danger)' }}; border: 1px solid var(--ula-border-subtle);">
                    <span class="material-symbols-rounded" style="font-size: 15px;">{{ $isSuspended ? 'play_arrow' : 'pause' }}</span>
                    <span>{{ $isSuspended ? __('Activate') : __('Suspend') }}</span>
                </button>
            </form>

            <!-- Delete Company Button -->
            <button onclick="openDeleteCompanyModal()" class="tactile-btn" style="padding: 9px 14px; font-size: 12px; border-radius: var(--ula-radius-pill); display: inline-flex; align-items: center; gap: 4px; background: rgba(217, 107, 95, 0.12); color: var(--ula-status-danger); border: 1px solid rgba(217, 107, 95, 0.3);">
                <span class="material-symbols-rounded" style="font-size: 15px;">delete</span>
                <span>{{ __('Delete') }}</span>
            </button>
        </div>
    </div>
</div>

<!-- Metrics Stat Grid -->
<div class="metrics-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 16px; margin-bottom: 24px;">
    <!-- Stat 1: Members -->
    <div class="metric-card" style="background: var(--ula-surface-card); border: 1px solid var(--ula-border-subtle); border-radius: var(--ula-radius-xl); padding: 18px 20px; box-shadow: var(--ula-shadow-sm);">
        <div class="metric-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
            <span class="metric-title" style="font-size: 12px; font-weight: 700; color: var(--ula-text-secondary);">{{ __('Members & Capacity') }}</span>
            <span class="material-symbols-rounded" style="font-size: 20px; color: var(--ula-text-primary);">group</span>
        </div>
        <div class="metric-value" style="font-size: 24px; font-weight: 800; color: var(--ula-text-primary); font-family: 'IBM Plex Mono', monospace;">
            {{ $memberCount }} <span style="font-size: 14px; color: var(--ula-text-muted); font-weight: 600;">/ {{ $isUnlimited ? '∞' : $seatLimit }}</span>
        </div>
        <div class="metric-trend" style="color: var(--ula-text-secondary); font-size: 11px; margin-top: 4px;">
            <span>{{ $stats['active_members'] }} {{ __('Active') }}</span> • 
            <span>{{ $stats['invited_members'] }} {{ __('Invited') }}</span> • 
            <span>{{ $stats['suspended_members'] }} {{ __('Suspended') }}</span>
        </div>
    </div>

    <!-- Stat 2: Departments & Teams -->
    <div class="metric-card" style="background: var(--ula-surface-card); border: 1px solid var(--ula-border-subtle); border-radius: var(--ula-radius-xl); padding: 18px 20px; box-shadow: var(--ula-shadow-sm);">
        <div class="metric-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
            <span class="metric-title" style="font-size: 12px; font-weight: 700; color: var(--ula-text-secondary);">{{ __('Organization Structure') }}</span>
            <span class="material-symbols-rounded" style="font-size: 20px; color: var(--ula-palm-800);">corporate_fare</span>
        </div>
        <div class="metric-value" style="font-size: 24px; font-weight: 800; color: var(--ula-text-primary); font-family: 'IBM Plex Mono', monospace;">
            {{ $stats['departments_count'] }} <span style="font-size: 14px; color: var(--ula-text-muted); font-weight: 600;">{{ __('Depts') }}</span>
        </div>
        <div class="metric-trend" style="color: var(--ula-text-secondary); font-size: 11px; margin-top: 4px;">
            <span>{{ $stats['teams_count'] }} {{ __('Sub-Teams configured') }}</span>
        </div>
    </div>

    <!-- Stat 3: Workspace & Rooms -->
    <div class="metric-card" style="background: var(--ula-surface-card); border: 1px solid var(--ula-border-subtle); border-radius: var(--ula-radius-xl); padding: 18px 20px; box-shadow: var(--ula-shadow-sm);">
        <div class="metric-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
            <span class="metric-title" style="font-size: 12px; font-weight: 700; color: var(--ula-text-secondary);">{{ __('Meeting Rooms & Spaces') }}</span>
            <span class="material-symbols-rounded" style="font-size: 20px; color: var(--ula-highlight-default);">meeting_room</span>
        </div>
        <div class="metric-value" style="font-size: 24px; font-weight: 800; color: var(--ula-text-primary); font-family: 'IBM Plex Mono', monospace;">
            {{ $stats['rooms_count'] }} <span style="font-size: 14px; color: var(--ula-text-muted); font-weight: 600;">{{ __('Rooms') }}</span>
        </div>
        <div class="metric-trend" style="color: var(--ula-text-secondary); font-size: 11px; margin-top: 4px;">
            <span>{{ $organization->floors->count() }} {{ __('Floor(s)') }} • {{ $organization->maps->count() }} {{ __('Map(s)') }}</span>
        </div>
    </div>

    <!-- Stat 4: Projects & Tasks -->
    <div class="metric-card" style="background: var(--ula-surface-card); border: 1px solid var(--ula-border-subtle); border-radius: var(--ula-radius-xl); padding: 18px 20px; box-shadow: var(--ula-shadow-sm);">
        <div class="metric-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
            <span class="metric-title" style="font-size: 12px; font-weight: 700; color: var(--ula-text-secondary);">{{ __('Projects & Tasks') }}</span>
            <span class="material-symbols-rounded" style="font-size: 20px; color: var(--ula-status-success);">task_alt</span>
        </div>
        <div class="metric-value" style="font-size: 24px; font-weight: 800; color: var(--ula-text-primary); font-family: 'IBM Plex Mono', monospace;">
            {{ $stats['projects_count'] }} <span style="font-size: 14px; color: var(--ula-text-muted); font-weight: 600;">{{ __('Projects') }}</span>
        </div>
        <div class="metric-trend" style="color: var(--ula-text-secondary); font-size: 11px; margin-top: 4px;">
            <span>{{ $stats['tasks_count'] }} {{ __('Total Tasks Tracked') }}</span>
        </div>
    </div>
</div>

<!-- Navigation Tabs -->
<div style="display: flex; gap: 8px; margin-bottom: 20px; border-bottom: 1px solid var(--ula-border-subtle); padding-bottom: 12px; overflow-x: auto;">
    <button onclick="switchTab('members')" id="tab-btn-members" class="tactile-btn tab-nav-btn active-tab" style="padding: 8px 16px; font-size: 12px; display: inline-flex; align-items: center; gap: 6px;">
        <span class="material-symbols-rounded" style="font-size: 16px;">group</span>
        <span>{{ __('Members & Staff') }} ({{ $organization->members->count() }})</span>
    </button>
    <button onclick="switchTab('departments')" id="tab-btn-departments" class="tactile-btn tab-nav-btn" style="padding: 8px 16px; font-size: 12px; display: inline-flex; align-items: center; gap: 6px;">
        <span class="material-symbols-rounded" style="font-size: 16px;">corporate_fare</span>
        <span>{{ __('Departments & Teams') }} ({{ $organization->departments->count() }})</span>
    </button>
    <button onclick="switchTab('rooms')" id="tab-btn-rooms" class="tactile-btn tab-nav-btn" style="padding: 8px 16px; font-size: 12px; display: inline-flex; align-items: center; gap: 6px;">
        <span class="material-symbols-rounded" style="font-size: 16px;">meeting_room</span>
        <span>{{ __('Rooms & Map Blueprint') }} ({{ $organization->rooms->count() }})</span>
    </button>
    <button onclick="switchTab('plan')" id="tab-btn-plan" class="tactile-btn tab-nav-btn" style="padding: 8px 16px; font-size: 12px; display: inline-flex; align-items: center; gap: 6px;">
        <span class="material-symbols-rounded" style="font-size: 16px;">diamond</span>
        <span>{{ __('Plan & Quotas') }}</span>
    </button>
    <button onclick="switchTab('payments')" id="tab-btn-payments" class="tactile-btn tab-nav-btn" style="padding: 8px 16px; font-size: 12px; display: inline-flex; align-items: center; gap: 6px;">
        <span class="material-symbols-rounded" style="font-size: 16px;">payments</span>
        <span>{{ __('Payments & Wire Transfers') }} ({{ $organization->subscriptionRequests->count() }})</span>
    </button>
    <button onclick="switchTab('audit')" id="tab-btn-audit" class="tactile-btn tab-nav-btn" style="padding: 8px 16px; font-size: 12px; display: inline-flex; align-items: center; gap: 6px;">
        <span class="material-symbols-rounded" style="font-size: 16px;">history</span>
        <span>{{ __('Activity Logs') }}</span>
    </button>
</div>

<!-- TAB 1: MEMBERS & STAFF -->
<div id="tab-content-members" class="tab-pane" style="display: block;">
    <div class="panel-card" style="background: var(--ula-surface-card); border: 1px solid var(--ula-border-subtle); border-radius: var(--ula-radius-xl); overflow: hidden; box-shadow: var(--ula-shadow-sm);">
        <div class="panel-header" style="padding: 18px 24px; border-bottom: 1px solid var(--ula-border-subtle); display: flex; justify-content: space-between; align-items: center;">
            <div class="panel-title" style="font-size: 15px; font-weight: 800; color: var(--ula-text-primary); display: flex; align-items: center; gap: 8px;">
                <span class="material-symbols-rounded" style="color: var(--ula-highlight-default); font-size: 20px;">group</span>
                <span>{{ __('Company Team Members Roster') }}</span>
            </div>
            <span style="font-size: 12px; color: var(--ula-text-muted); font-weight: 600; font-family: 'IBM Plex Mono', monospace;">
                {{ $organization->members->count() }} {{ __('registered users') }}
            </span>
        </div>

        <div class="data-table-container" style="overflow-x: auto;">
            <table class="data-table" style="width: 100%; border-collapse: collapse; text-align: start;">
                <thead>
                    <tr style="background: var(--ula-surface-page-alt); border-bottom: 1px solid var(--ula-border-subtle);">
                        <th style="padding: 12px 20px; font-size: 11px; font-weight: 700; color: var(--ula-text-muted); text-transform: uppercase; letter-spacing: 0.5px; text-align: start;">{{ __('User') }}</th>
                        <th style="padding: 12px 20px; font-size: 11px; font-weight: 700; color: var(--ula-text-muted); text-transform: uppercase; letter-spacing: 0.5px; text-align: start;">{{ __('Role') }}</th>
                        <th style="padding: 12px 20px; font-size: 11px; font-weight: 700; color: var(--ula-text-muted); text-transform: uppercase; letter-spacing: 0.5px; text-align: start;">{{ __('Department / Team') }}</th>
                        <th style="padding: 12px 20px; font-size: 11px; font-weight: 700; color: var(--ula-text-muted); text-transform: uppercase; letter-spacing: 0.5px; text-align: start;">{{ __('Job Title') }}</th>
                        <th style="padding: 12px 20px; font-size: 11px; font-weight: 700; color: var(--ula-text-muted); text-transform: uppercase; letter-spacing: 0.5px; text-align: start;">{{ __('Status') }}</th>
                        <th style="padding: 12px 20px; font-size: 11px; font-weight: 700; color: var(--ula-text-muted); text-transform: uppercase; letter-spacing: 0.5px; text-align: start;">{{ __('Joined Date') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($organization->members as $m)
                    <tr style="border-bottom: 1px solid var(--ula-border-subtle); transition: background 0.15s ease;">
                        <td style="padding: 14px 20px;">
                            <div style="display: flex; align-items: center; gap: 10px;">
                                @if($m->user?->avatar_url)
                                    <img src="{{ $m->user->avatar_url }}" style="width: 32px; height: 32px; border-radius: 50%; object-fit: cover;">
                                @else
                                    <div style="width: 32px; height: 32px; border-radius: 50%; background: var(--ula-palm-900); color: white; display: flex; align-items: center; justify-content: center; font-size: 11px; font-weight: 700;">
                                        {{ strtoupper(substr($m->user?->name ?? 'U', 0, 2)) }}
                                    </div>
                                @endif
                                <div>
                                    <strong style="color: var(--ula-text-primary); font-size: 13px;">{{ $m->user?->name ?? 'Unnamed User' }}</strong>
                                    <div style="font-size: 11px; color: var(--ula-text-muted); font-family: 'IBM Plex Mono', monospace;">{{ $m->user?->email }}</div>
                                </div>
                            </div>
                        </td>
                        <td style="padding: 14px 20px;">
                            <span class="nav-badge-pill" style="font-size: 11px; padding: 2px 8px; background: rgba(20,43,36,0.06); color: var(--ula-text-primary); font-weight: 600;">
                                {{ $m->role?->name ?? 'Member' }}
                            </span>
                        </td>
                        <td style="padding: 14px 20px;">
                            <div style="font-weight: 700; color: var(--ula-text-primary); font-size: 12px;">{{ $m->user?->profile?->department?->name ?? '—' }}</div>
                            <div style="font-size: 11px; color: var(--ula-text-muted);">{{ $m->user?->profile?->team?->name ?? '—' }}</div>
                        </td>
                        <td style="padding: 14px 20px;">
                            <span style="font-size: 12px; color: var(--ula-text-secondary); font-weight: 500;">{{ $m->user?->profile?->job_title ?: '—' }}</span>
                        </td>
                        <td style="padding: 14px 20px;">
                            @if($m->status === 'active')
                                <span class="badge-status badge-active" style="display: inline-flex; align-items: center; gap: 4px; font-size: 11px; padding: 2px 8px; border-radius: 6px; background: rgba(60,107,76,0.12); color: var(--ula-status-success); font-weight: 700;">
                                    <span class="material-symbols-rounded" style="font-size: 12px;">check_circle</span>
                                    <span>{{ __('Active') }}</span>
                                </span>
                            @elseif($m->status === 'invited')
                                <span class="badge-status badge-plan" style="display: inline-flex; align-items: center; gap: 4px; font-size: 11px; padding: 2px 8px; border-radius: 6px; background: rgba(211,165,83,0.12); color: var(--ula-gold-600); font-weight: 700;">
                                    <span class="material-symbols-rounded" style="font-size: 12px;">mail</span>
                                    <span>{{ __('Invited') }}</span>
                                </span>
                            @else
                                <span class="badge-status badge-suspended" style="display: inline-flex; align-items: center; gap: 4px; font-size: 11px; padding: 2px 8px; border-radius: 6px; background: rgba(217,107,95,0.12); color: var(--ula-status-danger); font-weight: 700;">
                                    <span class="material-symbols-rounded" style="font-size: 12px;">block</span>
                                    <span>{{ __('Suspended') }}</span>
                                </span>
                            @endif
                        </td>
                        <td style="padding: 14px 20px; font-size: 11px; color: var(--ula-text-muted); font-family: 'IBM Plex Mono', monospace;">
                            {{ $m->created_at?->format('M d, Y') }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" style="text-align: center; color: var(--ula-text-muted); padding: 36px;">
                            {{ __('No members found in this organization.') }}
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- TAB 2: DEPARTMENTS & TEAMS -->
<div id="tab-content-departments" class="tab-pane" style="display: none;">
    <div class="panel-card" style="background: var(--ula-surface-card); border: 1px solid var(--ula-border-subtle); border-radius: var(--ula-radius-xl); padding: 24px; box-shadow: var(--ula-shadow-sm);">
        <div class="panel-header" style="margin-bottom: 20px; border-bottom: 1px solid var(--ula-border-subtle); padding-bottom: 14px;">
            <div class="panel-title" style="font-size: 15px; font-weight: 800; color: var(--ula-text-primary); display: flex; align-items: center; gap: 8px;">
                <span class="material-symbols-rounded" style="color: var(--ula-highlight-default); font-size: 20px;">corporate_fare</span>
                <span>{{ __('Organizational Hierarchy') }}</span>
            </div>
        </div>

        <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 16px;">
            @forelse($organization->departments as $dept)
            <div style="background: var(--ula-surface-page-alt); border: 1px solid var(--ula-border-subtle); border-radius: var(--ula-radius-lg); padding: 18px;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
                    <div style="display: flex; align-items: center; gap: 8px;">
                        <span class="material-symbols-rounded" style="font-size: 20px; color: var(--ula-text-primary);">corporate_fare</span>
                        <strong style="font-size: 14px; color: var(--ula-text-primary);">{{ $dept->name }}</strong>
                    </div>
                    <span class="nav-badge-pill" style="font-size: 11px; padding: 2px 8px; background: rgba(20,43,36,0.06); color: var(--ula-text-primary); font-family: 'IBM Plex Mono', monospace;">
                        {{ $dept->userProfiles->count() }} {{ __('Staff') }}
                    </span>
                </div>

                <div style="font-size: 11px; font-weight: 700; color: var(--ula-text-muted); text-transform: uppercase; margin-bottom: 8px;">
                    {{ __('Sub-Teams') }} ({{ $dept->teams->count() }}):
                </div>

                @if($dept->teams->count() > 0)
                    <div style="display: flex; flex-wrap: wrap; gap: 6px;">
                        @foreach($dept->teams as $tm)
                            <span style="background: var(--ula-surface-card); border: 1px solid var(--ula-border-subtle); padding: 4px 10px; border-radius: 8px; font-size: 11px; font-weight: 600; color: var(--ula-text-secondary); display: inline-flex; align-items: center; gap: 4px;">
                                <span class="material-symbols-rounded" style="font-size: 13px;">groups</span>
                                <span>{{ $tm->name }}</span>
                            </span>
                        @endforeach
                    </div>
                @else
                    <span style="font-size: 12px; color: var(--ula-text-muted); font-style: italic;">{{ __('No sub-teams created yet.') }}</span>
                @endif
            </div>
            @empty
            <div style="grid-column: 1/-1; text-align: center; color: var(--ula-text-muted); padding: 40px;">
                {{ __('No departments configured in this organization.') }}
            </div>
            @endforelse
        </div>
    </div>
</div>

<!-- TAB 3: ROOMS & OFFICE BLUEPRINT -->
<div id="tab-content-rooms" class="tab-pane" style="display: none;">
    <div class="panel-card" style="background: var(--ula-surface-card); border: 1px solid var(--ula-border-subtle); border-radius: var(--ula-radius-xl); padding: 24px; box-shadow: var(--ula-shadow-sm);">
        <div class="panel-header" style="margin-bottom: 20px; border-bottom: 1px solid var(--ula-border-subtle); padding-bottom: 14px;">
            <div class="panel-title" style="font-size: 15px; font-weight: 800; color: var(--ula-text-primary); display: flex; align-items: center; gap: 8px;">
                <span class="material-symbols-rounded" style="color: var(--ula-highlight-default); font-size: 20px;">meeting_room</span>
                <span>{{ __('Office Spaces & Meeting Rooms') }}</span>
            </div>
        </div>

        <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 16px;">
            @forelse($organization->rooms as $room)
            <div style="background: var(--ula-surface-page-alt); border: 1px solid var(--ula-border-subtle); border-radius: var(--ula-radius-lg); padding: 18px;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                    <strong style="font-size: 14px; color: var(--ula-text-primary); display: flex; align-items: center; gap: 6px;">
                        <span class="material-symbols-rounded" style="font-size: 18px; color: var(--ula-highlight-default);">meeting_room</span>
                        <span>{{ $room->name }}</span>
                    </strong>
                    <span class="badge-status badge-plan" style="font-size: 10px; padding: 2px 6px; border-radius: 4px;">
                        {{ ucfirst($room->type ?? 'Meeting') }}
                    </span>
                </div>
                <div style="font-size: 12px; color: var(--ula-text-secondary); margin-bottom: 4px;">
                    <strong>Capacity:</strong> {{ $room->capacity ?? 10 }} {{ __('seats') }}
                </div>
                <div style="font-size: 11px; color: var(--ula-text-muted); font-family: 'IBM Plex Mono', monospace;">
                    Bounds: X:{{ $room->bounds['x'] ?? 0 }}, Y:{{ $room->bounds['y'] ?? 0 }}, W:{{ $room->bounds['width'] ?? 0 }}, H:{{ $room->bounds['height'] ?? 0 }}
                </div>
            </div>
            @empty
            <div style="grid-column: 1/-1; text-align: center; color: var(--ula-text-muted); padding: 40px;">
                {{ __('No rooms configured for this office floor.') }}
            </div>
            @endforelse
        </div>
    </div>
</div>

<!-- TAB 4: PLAN & RESOURCE QUOTAS -->
<div id="tab-content-plan" class="tab-pane" style="display: none;">
    <div class="panel-card" style="background: var(--ula-surface-card); border: 1px solid var(--ula-border-subtle); border-radius: var(--ula-radius-xl); padding: 24px; box-shadow: var(--ula-shadow-sm);">
        <div class="panel-header" style="margin-bottom: 20px; border-bottom: 1px solid var(--ula-border-subtle); padding-bottom: 14px; display: flex; justify-content: space-between; align-items: center;">
            <div class="panel-title" style="font-size: 15px; font-weight: 800; color: var(--ula-text-primary); display: flex; align-items: center; gap: 8px;">
                <span class="material-symbols-rounded" style="color: var(--ula-highlight-default); font-size: 20px;">diamond</span>
                <span>{{ __('Subscription Tier & Feature Entitlements') }}</span>
            </div>
            <button onclick="openChangePlanModal()" class="tactile-btn btn-primary" style="padding: 7px 16px; font-size: 12px; border-radius: var(--ula-radius-pill); display: inline-flex; align-items: center; gap: 4px;">
                <span class="material-symbols-rounded" style="font-size: 14px;">diamond</span>
                <span>{{ __('Change Plan') }}</span>
            </button>
        </div>

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 20px;">
            <div style="background: var(--ula-surface-page-alt); border: 1px solid var(--ula-border-subtle); border-radius: var(--ula-radius-lg); padding: 22px;">
                <h4 style="margin: 0 0 12px 0; font-size: 16px; font-weight: 800; color: var(--ula-text-primary);">{{ $plan?->name ?? 'Free Tier' }}</h4>
                <div style="font-size: 28px; font-weight: 800; color: var(--ula-text-primary); margin-bottom: 12px; font-family: 'IBM Plex Mono', monospace;">
                    ${{ number_format($plan?->price ?? 0, 2) }} <span style="font-size: 14px; color: var(--ula-text-muted); font-weight: 600; font-family: var(--ula-font-family);">/ {{ $plan?->billing_interval ?? 'month' }}</span>
                </div>
                <p style="font-size: 13px; color: var(--ula-text-secondary); line-height: 1.5; margin: 0 0 16px 0;">
                    {{ $plan?->description ?? __('Basic standard tier with core workspace features.') }}
                </p>
                <div style="display: flex; flex-direction: column; gap: 8px; font-size: 12px; font-weight: 600; color: var(--ula-text-secondary);">
                    <div><strong>{{ __('Seat Limit') }}:</strong> {{ $isUnlimited ? __('Unlimited') : $seatLimit . ' ' . __('users') }}</div>
                    <div><strong>{{ __('Max Rooms') }}:</strong> {{ $plan?->max_rooms ?? 10 }} {{ __('rooms') }}</div>
                    <div><strong>{{ __('Storage Limit') }}:</strong> {{ $plan?->storage_limit_mb ? ($plan->storage_limit_mb / 1024) . ' GB' : '1 GB' }}</div>
                </div>
            </div>

            <div style="background: var(--ula-surface-page-alt); border: 1px solid var(--ula-border-subtle); border-radius: var(--ula-radius-lg); padding: 22px;">
                <h4 style="margin: 0 0 12px 0; font-size: 16px; font-weight: 800; color: var(--ula-text-primary);">{{ __('Company System Settings') }}</h4>
                <div style="display: flex; flex-direction: column; gap: 10px; font-size: 13px; color: var(--ula-text-secondary);">
                    <div><strong>Timezone:</strong> {{ $organization->timezone ?: 'UTC' }}</div>
                    <div><strong>Guest Access:</strong> {{ ($organization->settings?->allow_guest_access ?? true) ? __('Enabled') : __('Disabled') }}</div>
                    <div><strong>Screen Sharing:</strong> {{ ($organization->settings?->allow_screen_share ?? true) ? __('Enabled') : __('Disabled') }}</div>
                    <div><strong>Max Simultaneous Guests:</strong> {{ $organization->settings?->max_guests_per_room ?? 15 }}</div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- TAB 5: BANK PAYMENTS & SUBSCRIPTION REQUESTS -->
<div id="tab-content-payments" class="tab-pane" style="display: none;">
    <div class="panel-card" style="background: var(--ula-surface-card); border: 1px solid var(--ula-border-subtle); border-radius: var(--ula-radius-xl); overflow: hidden; box-shadow: var(--ula-shadow-sm);">
        <div class="panel-header" style="padding: 18px 24px; border-bottom: 1px solid var(--ula-border-subtle); display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px;">
            <div class="panel-title" style="font-size: 15px; font-weight: 800; color: var(--ula-text-primary); display: flex; align-items: center; gap: 8px;">
                <span class="material-symbols-rounded" style="color: var(--ula-highlight-default); font-size: 20px;">payments</span>
                <span>{{ __('Bank Transfer Payments & Upgrade Requests') }}</span>
            </div>
            <a href="{{ route('superadmin.subscriptions') }}" class="tactile-btn btn-secondary" style="padding: 6px 14px; font-size: 11px; border-radius: var(--ula-radius-pill); text-decoration: none;">
                {{ __('All System Subscriptions') }} →
            </a>
        </div>

        <div class="data-table-container" style="overflow-x: auto;">
            <table class="data-table" style="width: 100%; border-collapse: collapse; text-align: start;">
                <thead>
                    <tr style="background: var(--ula-surface-page-alt); border-bottom: 1px solid var(--ula-border-subtle);">
                        <th style="padding: 12px 18px; font-size: 11px; font-weight: 700; color: var(--ula-text-muted); text-transform: uppercase; letter-spacing: 0.5px; text-align: start;">{{ __('Requested Plan') }}</th>
                        <th style="padding: 12px 18px; font-size: 11px; font-weight: 700; color: var(--ula-text-muted); text-transform: uppercase; letter-spacing: 0.5px; text-align: start;">{{ __('Amount') }}</th>
                        <th style="padding: 12px 18px; font-size: 11px; font-weight: 700; color: var(--ula-text-muted); text-transform: uppercase; letter-spacing: 0.5px; text-align: start;">{{ __('Bank & Sender') }}</th>
                        <th style="padding: 12px 18px; font-size: 11px; font-weight: 700; color: var(--ula-text-muted); text-transform: uppercase; letter-spacing: 0.5px; text-align: start;">{{ __('Transfer Ref #') }}</th>
                        <th style="padding: 12px 18px; font-size: 11px; font-weight: 700; color: var(--ula-text-muted); text-transform: uppercase; letter-spacing: 0.5px; text-align: start;">{{ __('Receipt Slip') }}</th>
                        <th style="padding: 12px 18px; font-size: 11px; font-weight: 700; color: var(--ula-text-muted); text-transform: uppercase; letter-spacing: 0.5px; text-align: start;">{{ __('Status') }}</th>
                        <th style="padding: 12px 18px; font-size: 11px; font-weight: 700; color: var(--ula-text-muted); text-transform: uppercase; letter-spacing: 0.5px; text-align: start;">{{ __('Date') }}</th>
                        <th style="padding: 12px 18px; font-size: 11px; font-weight: 700; color: var(--ula-text-muted); text-transform: uppercase; letter-spacing: 0.5px; text-align: start;">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($organization->subscriptionRequests as $req)
                    <tr style="border-bottom: 1px solid var(--ula-border-subtle); transition: background 0.15s ease;">
                        <td style="padding: 14px 18px;">
                            <span class="badge-status badge-plan" style="font-size: 11px; padding: 2px 8px; border-radius: 6px;">{{ $req->plan?->name ?? 'Plan' }}</span>
                        </td>
                        <td style="padding: 14px 18px;">
                            <strong style="font-family: 'IBM Plex Mono', monospace; font-size: 13px;">{{ number_format($req->amount, 2) }} {{ $req->currency }}</strong>
                            <div style="font-size: 10px; color: var(--ula-text-muted); text-transform: uppercase;">{{ $req->billing_cycle }}</div>
                        </td>
                        <td style="padding: 14px 18px;">
                            <div style="font-weight: 700; font-size: 12px;">{{ $req->bank_name }}</div>
                            <div style="font-size: 11px; color: var(--ula-text-muted);">{{ $req->sender_name }}</div>
                        </td>
                        <td style="padding: 14px 18px;">
                            <code style="font-family: 'IBM Plex Mono', monospace; font-weight: 700; color: var(--ula-text-primary);">#{{ $req->transfer_reference }}</code>
                        </td>
                        <td style="padding: 14px 18px;">
                            @if($req->receipt_path)
                                <a href="{{ route('superadmin.subscriptions.receipt', $req->id) }}" target="_blank" class="tactile-btn btn-secondary" style="padding: 4px 8px; font-size: 11px; text-decoration: none;">
                                    {{ __('View') }}
                                </a>
                            @else
                                <span style="color: var(--ula-text-muted); font-size: 11px;">—</span>
                            @endif
                        </td>
                        <td style="padding: 14px 18px;">
                            @if($req->status === 'pending')
                                <span class="badge-status" style="background: rgba(211,165,83,0.12); color: var(--ula-gold-600); font-weight: 700; font-size: 11px; padding: 2px 8px; border-radius: 6px;">
                                    {{ __('Pending') }}
                                </span>
                            @elseif($req->status === 'approved')
                                <span class="badge-status badge-active" style="font-size: 11px; padding: 2px 8px; border-radius: 6px;">{{ __('Approved') }}</span>
                            @elseif($req->status === 'rejected')
                                <span class="badge-status badge-suspended" style="font-size: 11px; padding: 2px 8px; border-radius: 6px;">{{ __('Rejected') }}</span>
                            @else
                                <span class="badge-status" style="font-size: 11px; padding: 2px 8px; border-radius: 6px;">{{ ucfirst($req->status) }}</span>
                            @endif
                        </td>
                        <td style="padding: 14px 18px; font-size: 11px; color: var(--ula-text-muted); font-family: 'IBM Plex Mono', monospace;">
                            {{ $req->created_at->format('Y-m-d') }}
                        </td>
                        <td style="padding: 14px 18px;">
                            @if($req->status === 'pending')
                                <div style="display: flex; gap: 6px;">
                                    <form method="POST" action="{{ route('superadmin.subscriptions.approve', $req->id) }}" onsubmit="return confirm('{{ __('Approve this transfer and activate the plan for this company?') }}');" style="margin: 0;">
                                        @csrf
                                        <button type="submit" class="tactile-btn btn-primary" style="padding: 4px 8px; font-size: 11px;">
                                            {{ __('Approve') }}
                                        </button>
                                    </form>
                                    <form method="POST" action="{{ route('superadmin.subscriptions.reject', $req->id) }}" onsubmit="return confirm('{{ __('Reject this transfer request?') }}');" style="margin: 0;">
                                        @csrf
                                        <input type="hidden" name="admin_notes" value="Rejected from company profile">
                                        <button type="submit" class="tactile-btn" style="padding: 4px 8px; font-size: 11px; color: var(--ula-status-danger); border-color: rgba(217,107,95,0.3);">
                                            <span class="material-symbols-rounded" style="font-size: 1em; vertical-align: text-bottom;">close</span>
                                        </button>
                                    </form>
                                </div>
                            @else
                                <span style="font-size: 11px; color: var(--ula-text-muted);">—</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" style="text-align: center; color: var(--ula-text-muted); padding: 36px;">
                            {{ __('No wire transfer payment requests recorded for this company.') }}
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- TAB 6: AUDIT LOGS -->
<div id="tab-content-audit" class="tab-pane" style="display: none;">
    <div class="panel-card" style="background: var(--ula-surface-card); border: 1px solid var(--ula-border-subtle); border-radius: var(--ula-radius-xl); overflow: hidden; box-shadow: var(--ula-shadow-sm);">
        <div class="panel-header" style="padding: 18px 24px; border-bottom: 1px solid var(--ula-border-subtle);">
            <div class="panel-title" style="font-size: 15px; font-weight: 800; color: var(--ula-text-primary); display: flex; align-items: center; gap: 8px;">
                <span class="material-symbols-rounded" style="color: var(--ula-highlight-default); font-size: 20px;">history</span>
                <span>{{ __('Recent Activity & Audit Trail') }}</span>
            </div>
        </div>

        <div class="data-table-container" style="overflow-x: auto;">
            <table class="data-table" style="width: 100%; border-collapse: collapse; text-align: start;">
                <thead>
                    <tr style="background: var(--ula-surface-page-alt); border-bottom: 1px solid var(--ula-border-subtle);">
                        <th style="padding: 12px 20px; font-size: 11px; font-weight: 700; color: var(--ula-text-muted); text-transform: uppercase; letter-spacing: 0.5px; text-align: start;">{{ __('Event') }}</th>
                        <th style="padding: 12px 20px; font-size: 11px; font-weight: 700; color: var(--ula-text-muted); text-transform: uppercase; letter-spacing: 0.5px; text-align: start;">{{ __('Actor / User') }}</th>
                        <th style="padding: 12px 20px; font-size: 11px; font-weight: 700; color: var(--ula-text-muted); text-transform: uppercase; letter-spacing: 0.5px; text-align: start;">{{ __('IP Address') }}</th>
                        <th style="padding: 12px 20px; font-size: 11px; font-weight: 700; color: var(--ula-text-muted); text-transform: uppercase; letter-spacing: 0.5px; text-align: start;">{{ __('Timestamp') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($organization->auditLogs as $log)
                    <tr style="border-bottom: 1px solid var(--ula-border-subtle); transition: background 0.15s ease;">
                        <td style="padding: 12px 20px;">
                            <strong style="color: var(--ula-text-primary); font-size: 12px; font-family: 'IBM Plex Mono', monospace;">{{ $log->event }}</strong>
                        </td>
                        <td style="padding: 12px 20px;">
                            <span style="font-size: 12px; color: var(--ula-text-secondary);">{{ $log->user?->name ?? 'System' }}</span>
                        </td>
                        <td style="padding: 12px 20px;">
                            <code style="font-size: 11px; color: var(--ula-text-muted); font-family: 'IBM Plex Mono', monospace;">{{ $log->ip_address ?: '—' }}</code>
                        </td>
                        <td style="padding: 12px 20px; font-size: 11px; color: var(--ula-text-muted); font-family: 'IBM Plex Mono', monospace;">
                            {{ $log->created_at?->format('Y-m-d H:i:s') }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" style="text-align: center; color: var(--ula-text-muted); padding: 36px;">
                            {{ __('No audit logs recorded for this company yet.') }}
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- 1. Change Plan Modal -->
<div id="changePlanModal" class="modal-overlay">
    <div class="modal-card" style="border-radius: var(--ula-radius-xl); padding: 26px; max-width: 480px; width: 100%;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <div style="display: flex; align-items: center; gap: 8px;">
                <span class="material-symbols-rounded" style="color: var(--ula-highlight-default); font-size: 22px;">diamond</span>
                <h3 style="font-size: 17px; font-weight: 800; color: var(--ula-text-primary); margin: 0;">{{ __('Change Subscription Plan') }}</h3>
            </div>
            <button onclick="closeChangePlanModal()" style="background: var(--ula-surface-page-alt); border: 1px solid var(--ula-border-subtle); width: 32px; height: 32px; border-radius: 50%; display: flex; align-items: center; justify-content: center; cursor: pointer; color: var(--ula-text-primary);">
                <span class="material-symbols-rounded" style="font-size: 16px;">close</span>
            </button>
        </div>

        <form method="POST" action="{{ route('superadmin.companies.plan', $organization) }}">
            @csrf
            <div style="margin-bottom: 22px;">
                <label style="display: block; font-size: 12px; font-weight: 700; color: var(--ula-text-secondary); margin-bottom: 8px;">
                    {{ __('Select New Subscription Tier') }}
                </label>
                <select name="plan_id" style="width: 100%; background: var(--ula-surface-page-alt); border: 1px solid var(--ula-border-subtle); border-radius: 12px; padding: 12px; color: var(--ula-text-primary); font-size: 13px; outline: none; font-weight: 600;">
                    @foreach($allPlans as $p)
                        <option value="{{ $p->id }}" {{ $organization->plan_id == $p->id ? 'selected' : '' }}>
                            {{ $p->name }} — {{ $p->seat_limit === 0 ? 'Unlimited' : $p->seat_limit }} Users (${{ number_format($p->price, 2) }}/mo)
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

<!-- 2. Edit Company Details Modal -->
<div id="editCompanyModal" class="modal-overlay">
    <div class="modal-card" style="border-radius: var(--ula-radius-xl); padding: 26px; max-width: 520px; width: 100%;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <div style="display: flex; align-items: center; gap: 8px;">
                <span class="material-symbols-rounded" style="color: var(--ula-highlight-default); font-size: 22px;">edit</span>
                <h3 style="font-size: 17px; font-weight: 800; color: var(--ula-text-primary); margin: 0;">{{ __('Edit Company Details') }}</h3>
            </div>
            <button onclick="closeEditCompanyModal()" style="background: var(--ula-surface-page-alt); border: 1px solid var(--ula-border-subtle); width: 32px; height: 32px; border-radius: 50%; display: flex; align-items: center; justify-content: center; cursor: pointer; color: var(--ula-text-primary);">
                <span class="material-symbols-rounded" style="font-size: 16px;">close</span>
            </button>
        </div>

        <form method="POST" action="{{ route('superadmin.companies.update', $organization) }}">
            @csrf
            @method('PUT')

            <div style="margin-bottom: 16px;">
                <label style="display: block; font-size: 11px; font-weight: 700; color: var(--ula-text-secondary); margin-bottom: 6px; text-transform: uppercase;">{{ __('Company Name') }}</label>
                <input type="text" name="name" value="{{ $organization->name }}" required style="width: 100%; background: var(--ula-surface-page-alt); border: 1px solid var(--ula-border-subtle); border-radius: 10px; padding: 10px 14px; color: var(--ula-text-primary); font-size: 13px; font-weight: 600; outline: none;">
            </div>

            <div style="margin-bottom: 16px;">
                <label style="display: block; font-size: 11px; font-weight: 700; color: var(--ula-text-secondary); margin-bottom: 6px; text-transform: uppercase;">{{ __('Slug') }}</label>
                <input type="text" name="slug" value="{{ $organization->slug }}" required style="width: 100%; background: var(--ula-surface-page-alt); border: 1px solid var(--ula-border-subtle); border-radius: 10px; padding: 10px 14px; color: var(--ula-text-primary); font-size: 13px; font-family: 'IBM Plex Mono', monospace; outline: none;">
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px; margin-bottom: 16px;">
                <div>
                    <label style="display: block; font-size: 11px; font-weight: 700; color: var(--ula-text-secondary); margin-bottom: 6px; text-transform: uppercase;">{{ __('Status') }}</label>
                    <select name="status" style="width: 100%; background: var(--ula-surface-page-alt); border: 1px solid var(--ula-border-subtle); border-radius: 10px; padding: 10px 14px; color: var(--ula-text-primary); font-size: 13px; font-weight: 600; outline: none;">
                        <option value="active" {{ $organization->status === 'active' ? 'selected' : '' }}>{{ __('Active') }}</option>
                        <option value="suspended" {{ $organization->status === 'suspended' ? 'selected' : '' }}>{{ __('Suspended') }}</option>
                        <option value="trial" {{ $organization->status === 'trial' ? 'selected' : '' }}>{{ __('Trial') }}</option>
                    </select>
                </div>
                <div>
                    <label style="display: block; font-size: 11px; font-weight: 700; color: var(--ula-text-secondary); margin-bottom: 6px; text-transform: uppercase;">{{ __('Plan') }}</label>
                    <select name="plan_id" style="width: 100%; background: var(--ula-surface-page-alt); border: 1px solid var(--ula-border-subtle); border-radius: 10px; padding: 10px 14px; color: var(--ula-text-primary); font-size: 13px; font-weight: 600; outline: none;">
                        @foreach($allPlans as $p)
                            <option value="{{ $p->id }}" {{ $organization->plan_id == $p->id ? 'selected' : '' }}>{{ $p->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div style="margin-bottom: 22px;">
                <label style="display: block; font-size: 11px; font-weight: 700; color: var(--ula-text-secondary); margin-bottom: 6px; text-transform: uppercase;">{{ __('Timezone') }}</label>
                <input type="text" name="timezone" value="{{ $organization->timezone ?: 'UTC' }}" style="width: 100%; background: var(--ula-surface-page-alt); border: 1px solid var(--ula-border-subtle); border-radius: 10px; padding: 10px 14px; color: var(--ula-text-primary); font-size: 13px; outline: none;">
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 10px;">
                <button type="button" onclick="closeEditCompanyModal()" class="tactile-btn btn-secondary">{{ __('Cancel') }}</button>
                <button type="submit" class="tactile-btn btn-primary" style="display: inline-flex; align-items: center; gap: 6px;">
                    <span class="material-symbols-rounded" style="font-size: 16px;">save</span>
                    <span>{{ __('Save Changes') }}</span>
                </button>
            </div>
        </form>
    </div>
</div>

<!-- 3. Delete Company Modal -->
<div id="deleteCompanyModal" class="modal-overlay">
    <div class="modal-card" style="border-radius: var(--ula-radius-xl); padding: 26px; max-width: 460px; width: 100%;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
            <div style="display: flex; align-items: center; gap: 8px;">
                <span class="material-symbols-rounded" style="color: var(--ula-status-danger); font-size: 22px;">warning</span>
                <h3 style="font-size: 17px; font-weight: 800; color: var(--ula-status-danger); margin: 0;">{{ __('Delete Company') }}</h3>
            </div>
            <button onclick="closeDeleteCompanyModal()" style="background: var(--ula-surface-page-alt); border: 1px solid var(--ula-border-subtle); width: 32px; height: 32px; border-radius: 50%; display: flex; align-items: center; justify-content: center; cursor: pointer; color: var(--ula-text-primary);">
                <span class="material-symbols-rounded" style="font-size: 16px;">close</span>
            </button>
        </div>

        <p style="font-size: 13px; color: var(--ula-text-secondary); line-height: 1.6; margin-bottom: 20px;">
            {{ __('Are you sure you want to permanently delete') }} <strong>{{ $organization->name }}</strong>? {{ __('This will remove all associated members, departments, rooms, and files. This action cannot be undone.') }}
        </p>

        <form method="POST" action="{{ route('superadmin.companies.delete', $organization) }}">
            @csrf
            @method('DELETE')
            <div style="display: flex; justify-content: flex-end; gap: 10px;">
                <button type="button" onclick="closeDeleteCompanyModal()" class="tactile-btn btn-secondary">{{ __('Cancel') }}</button>
                <button type="submit" class="tactile-btn" style="background: var(--ula-status-danger); color: white; border: none; padding: 8px 18px; border-radius: var(--ula-radius-pill); font-weight: 700; display: inline-flex; align-items: center; gap: 6px;">
                    <span class="material-symbols-rounded" style="font-size: 16px;">delete_forever</span>
                    <span>{{ __('Delete Permanently') }}</span>
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
        color: var(--ula-text-muted);
        border: none;
        border-radius: var(--ula-radius-pill);
        font-weight: 700;
        cursor: pointer;
        transition: all 0.2s ease;
    }
    .tab-nav-btn:hover {
        background: var(--ula-surface-page-alt);
        color: var(--ula-text-primary);
    }
    .tab-nav-btn.active-tab {
        background: var(--ula-palm-900);
        color: white !important;
        box-shadow: var(--ula-shadow-sm);
    }
</style>

<script nonce="{{ $cspNonce ?? '' }}">
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
