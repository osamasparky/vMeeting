<div id="tab-projects" class="tab-view">
    @if($membership->hasPermission('projects.manage') || $membership->role?->slug === 'company_admin')
    <div style="display: flex; justify-content: flex-end; margin-bottom: 20px;">
        <x-btn variant="primary" size="md" onclick="openNewProjectModal()" icon="add">
            {{ __('New Project') }}
        </x-btn>
    </div>
    @endif

    <!-- Project KPI Metrics -->
    <div class="kpi-grid" style="margin-bottom: 24px;">
        <div class="kpi-card">
            <div class="kpi-header">
                <span class="kpi-title">{{ __('Total Projects') }}</span>
                <div class="kpi-icon-box">
                    <span class="material-symbols-rounded">folder</span>
                </div>
            </div>
            <div class="kpi-value">{{ $projects->count() }}</div>
            <div class="kpi-trend" style="color: var(--ula-status-success);">
                <span class="material-symbols-rounded" style="font-size: 14px;">play_circle</span>
                <span>{{ $projects->where('status', 'active')->count() }} {{ __('Active initiatives') }}</span>
            </div>
        </div>
        <div class="kpi-card">
            <div class="kpi-header">
                <span class="kpi-title">{{ __('Total Tasks') }}</span>
                <div class="kpi-icon-box">
                    <span class="material-symbols-rounded">task_alt</span>
                </div>
            </div>
            <div class="kpi-value">{{ $tasks->count() }}</div>
            <div class="kpi-trend" style="color: var(--ula-status-success);">
                <span class="material-symbols-rounded" style="font-size: 14px;">pending_actions</span>
                <span>{{ $tasks->where('status', '!=', 'done')->count() }} {{ __('In progress / Backlog') }}</span>
            </div>
        </div>
        <div class="kpi-card">
            <div class="kpi-header">
                <span class="kpi-title">{{ __('Logged Hours') }}</span>
                <div class="kpi-icon-box">
                    <span class="material-symbols-rounded">timer</span>
                </div>
            </div>
            <div class="kpi-value">{{ round($projects->sum(fn($p) => $p->actualHours()), 1) }}h</div>
            <div class="kpi-trend" style="color: var(--ula-status-success);">
                <span class="material-symbols-rounded" style="font-size: 14px;">trending_up</span>
                <span>{{ __('Tracked across all tasks') }}</span>
            </div>
        </div>
        <div class="kpi-card">
            <div class="kpi-header">
                <span class="kpi-title">{{ __('Total Budget') }}</span>
                <div class="kpi-icon-box">
                    <span class="material-symbols-rounded">attach_money</span>
                </div>
            </div>
            <div class="kpi-value">${{ number_format($projects->sum('budget_amount'), 0) }}</div>
            <div class="kpi-trend" style="color: var(--ula-status-success);">
                <span class="material-symbols-rounded" style="font-size: 14px;">account_balance</span>
                <span>{{ __('Allocated capital') }}</span>
            </div>
        </div>
    </div>

    <!-- Projects Table -->
    <div class="card" style="border-radius: var(--ula-radius-lg); overflow: hidden; padding: 0;">
        <div style="padding: 20px 24px; border-bottom: 1px solid var(--ula-border-subtle); display: flex; justify-content: space-between; align-items: center; background: var(--ula-surface-card);">
            <h3 style="font-size: 16px; font-weight: 800; color: var(--ula-text-primary); display: flex; align-items: center; gap: 8px;">
                <span class="material-symbols-rounded" style="font-size: 18px; color: var(--ula-highlight-default);">assignment</span>
                <span>{{ __('Active Initiatives') }} ({{ $projects->count() }})</span>
            </h3>
        </div>
        <div style="overflow-x: auto;">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>{{ __('Code') }}</th>
                        <th>{{ __('Project Name') }}</th>
                        <th>{{ __('Manager') }}</th>
                        <th>{{ __('Status') }}</th>
                        <th>{{ __('Priority') }}</th>
                        <th>{{ __('Progress') }}</th>
                        <th>{{ __('Due Date') }}</th>
                        <th>{{ __('Budget') }}</th>
                        <th>{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($projects as $p)
                        @php
                            $canOpenHub = ($user->isSuperAdmin() || $membership->role?->slug === 'company_admin' || $membership->hasPermission('projects.manage') || $p->manager_id === $user->id || $p->owner_id === $user->id);
                        @endphp
                        <tr @if($canOpenHub) onclick="window.location.href='{{ route('projects.hub', $p->id) }}'" style="cursor: pointer;" title="{{ __('Click to open project dashboard & tasks') }}" @endif>
                            <td><span class="nav-badge-pill" style="font-family: 'IBM Plex Mono', monospace;">{{ $p->code ?? 'PRJ' }}</span></td>
                            <td>
                                <div style="font-weight: 700; color: var(--ula-text-primary);">{{ $p->name }}</div>
                                @if($p->description)
                                    <div style="font-size: 11px; color: var(--ula-text-muted);">{{ Str::limit($p->description, 50) }}</div>
                                @endif
                            </td>
                            <td>
                                <div style="display: flex; align-items: center; gap: 8px;">
                                    <div style="width: 28px; height: 28px; border-radius: 8px; background: var(--ula-palm-900); color: white; display: flex; align-items: center; justify-content: center; font-size: 11px; font-weight: 700; box-shadow: var(--ula-shadow-sm);">
                                        {{ strtoupper(substr($p->manager->name ?? 'NA', 0, 2)) }}
                                    </div>
                                    <span style="font-weight: 600; font-size: 13px;">{{ $p->manager->name ?? 'Unassigned' }}</span>
                                </div>
                            </td>
                            <td>
                                @if($p->status === 'active')
                                    <x-badge variant="live" dot="true">{{ __('Active') }}</x-badge>
                                @elseif($p->status === 'completed')
                                    <x-badge variant="default">{{ __('Completed') }}</x-badge>
                                @elseif($p->status === 'on_hold')
                                    <x-badge variant="scheduled">{{ __('On Hold') }}</x-badge>
                                @else
                                    <x-badge variant="default">{{ ucfirst($p->status) }}</x-badge>
                                @endif
                            </td>
                            <td>
                                @if($p->priority === 'urgent')
                                    <x-badge variant="attention" icon="local_fire_department">{{ __('Urgent') }}</x-badge>
                                @elseif($p->priority === 'high')
                                    <x-badge variant="scheduled" icon="bolt">{{ __('High') }}</x-badge>
                                @elseif($p->priority === 'medium')
                                    <x-badge variant="default">{{ __('Medium') }}</x-badge>
                                @elseif($p->priority === 'low')
                                    <x-badge variant="cancelled">{{ __('Low') }}</x-badge>
                                @else
                                    <x-badge variant="default">{{ ucfirst($p->priority) }}</x-badge>
                                @endif
                            </td>
                            <td style="min-width: 140px;">
                                @php $pct = $p->progressPercentage(); @endphp
                                <div style="display: flex; justify-content: space-between; font-size: 11px; margin-bottom: 4px; font-weight: 600; font-family: 'IBM Plex Mono', monospace;">
                                    <span>{{ $pct }}%</span>
                                    <span style="color: var(--ula-text-muted);">{{ $p->tasks_count }} {{ __('tasks') }}</span>
                                </div>
                                <div class="progress-bar-bg" style="background: var(--ula-sand-200); height: 7px; border-radius: 9999px; overflow: hidden;">
                                    <div class="progress-bar-fill" style="width: {{ $pct }}%; height: 100%; background: {{ $pct === 100 ? 'var(--ula-status-success)' : 'var(--ula-palm-900)' }}; border-radius: 9999px;"></div>
                                </div>
                            </td>
                            <td style="font-size: 12px; font-weight: 500; font-family: 'IBM Plex Mono', monospace;">{{ $p->due_date ? $p->due_date->format('M d, Y') : '—' }}</td>
                            <td style="font-weight: 700; color: var(--ula-text-primary); font-family: 'IBM Plex Mono', monospace;">${{ number_format($p->budget_amount ?? 0, 0) }}</td>
                            <td>
                                @if($canOpenHub)
                                    <x-btn variant="primary" size="sm" href="{{ route('projects.hub', $p->id) }}" onclick="event.stopPropagation();" icon="analytics">
                                        {{ __('Open Hub') }}
                                    </x-btn>
                                @else
                                    <span class="nav-badge-pill" style="font-size: 10px; color: var(--ula-text-muted); display: inline-flex; align-items: center; gap: 4px;">
                                        <span class="material-symbols-rounded" style="font-size: 12px;">visibility</span>
                                        <span>{{ __('View Details') }}</span>
                                    </span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" style="text-align: center; padding: 48px 16px; color: var(--ula-text-muted);">
                                <span class="material-symbols-rounded" style="font-size: 36px; color: var(--ula-sand-400); display: block; margin-bottom: 8px;">folder_off</span>
                                <div style="font-size: 14px; font-weight: 500; color: var(--ula-text-secondary);">{{ __('No projects created yet.') }}</div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
