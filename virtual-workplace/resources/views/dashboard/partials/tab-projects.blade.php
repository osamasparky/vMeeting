<div id="tab-projects" class="tab-view">
    <div style="display: flex; align-items: flex-end; justify-content: space-between; gap: 16px; flex-wrap: wrap; margin-bottom: 24px;">
        <div style="display: flex; flex-direction: column; gap: 2px;">
            <h2 style="font-size: 30px; font-weight: 700; line-height: 1.25; color: var(--ula-text-primary); margin: 0;">{{ __('Projects Portfolio & Strategic Initiatives') }}</h2>
            <span style="font-family: 'IBM Plex Sans', sans-serif; font-size: 16px; font-weight: 400; color: var(--ula-text-secondary);">Projects Portfolio &amp; Strategic Initiatives</span>
        </div>
        @if($membership->hasPermission('projects.manage') || $membership->role?->slug === 'company_admin')
        <div style="display: flex; gap: 10px; flex-wrap: wrap;">
            <x-btn variant="primary" size="md" onclick="openNewProjectModal()" icon="add">
                {{ __('New Project') }}
            </x-btn>
        </div>
        @endif
    </div>

    <!-- Project KPI Metrics -->
    <div class="kpi-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(min(100%, 210px), 1fr)); gap: 16px; margin-bottom: 24px;">
        <div class="kpi-card" style="padding: 18px; border-radius: var(--ula-radius-xl); border: 1px solid var(--ula-border-subtle); background: var(--ula-surface-card); box-shadow: var(--ula-shadow-xs); display: flex; flex-direction: column; gap: 12px;">
            <div style="display: flex; align-items: flex-start; justify-content: space-between; gap: 12px;">
                <span style="font-size: 14px; font-weight: 600; color: var(--ula-text-secondary);">{{ __('Total Projects') }}</span>
                <span style="width: 32px; height: 32px; flex-shrink: 0; border-radius: 999px; background: var(--ula-control-tone-palm-fill); color: var(--ula-palm-700); display: inline-flex; align-items: center; justify-content: center;">
                    <span class="material-symbols-rounded" style="font-size: 18px;">folder</span>
                </span>
            </div>
            <div style="display: flex; align-items: baseline; gap: 8px; flex-wrap: wrap;">
                <span style="font-size: 32px; font-weight: 400; line-height: 1.1; font-family: 'IBM Plex Mono', monospace;">{{ $projects->count() }}</span>
                <span style="font-size: 12px; color: var(--ula-status-success);">{{ $projects->where('status', 'active')->count() }} {{ __('Active initiatives') }}</span>
            </div>
        </div>
        <div class="kpi-card" style="padding: 18px; border-radius: var(--ula-radius-xl); border: 1px solid var(--ula-border-subtle); background: var(--ula-surface-card); box-shadow: var(--ula-shadow-xs); display: flex; flex-direction: column; gap: 12px;">
            <div style="display: flex; align-items: flex-start; justify-content: space-between; gap: 12px;">
                <span style="font-size: 14px; font-weight: 600; color: var(--ula-text-secondary);">{{ __('Total Tasks') }}</span>
                <span style="width: 32px; height: 32px; flex-shrink: 0; border-radius: 999px; background: var(--ula-control-tone-gold-fill); color: var(--ula-gold-700); display: inline-flex; align-items: center; justify-content: center;">
                    <span class="material-symbols-rounded" style="font-size: 18px;">task_alt</span>
                </span>
            </div>
            <div style="display: flex; align-items: baseline; gap: 8px; flex-wrap: wrap;">
                <span style="font-size: 32px; font-weight: 400; line-height: 1.1; font-family: 'IBM Plex Mono', monospace;">{{ $tasks->count() }}</span>
                <span style="font-size: 12px; color: var(--ula-text-muted);">{{ $tasks->where('status', '!=', 'done')->count() }} {{ __('In progress / Backlog') }}</span>
            </div>
        </div>
        <div class="kpi-card" style="padding: 18px; border-radius: var(--ula-radius-xl); border: 1px solid var(--ula-border-subtle); background: var(--ula-surface-card); box-shadow: var(--ula-shadow-xs); display: flex; flex-direction: column; gap: 12px;">
            <div style="display: flex; align-items: flex-start; justify-content: space-between; gap: 12px;">
                <span style="font-size: 14px; font-weight: 600; color: var(--ula-text-secondary);">{{ __('Logged Hours') }}</span>
                <span style="width: 32px; height: 32px; flex-shrink: 0; border-radius: 999px; background: var(--ula-surface-page-alt); color: var(--ula-text-primary); display: inline-flex; align-items: center; justify-content: center;">
                    <span class="material-symbols-rounded" style="font-size: 18px;">timer</span>
                </span>
            </div>
            <div style="display: flex; align-items: baseline; gap: 8px; flex-wrap: wrap;">
                <span style="font-size: 32px; font-weight: 400; line-height: 1.1; font-family: 'IBM Plex Mono', monospace;">{{ round($projects->sum(fn($p) => $p->actualHours()), 1) }}h</span>
                <span style="font-size: 12px; color: var(--ula-status-success);">{{ __('Tracked across all tasks') }}</span>
            </div>
        </div>
        <div class="kpi-card" style="padding: 18px; border-radius: var(--ula-radius-xl); border: 1px solid var(--ula-border-subtle); background: var(--ula-surface-card); box-shadow: var(--ula-shadow-xs); display: flex; flex-direction: column; gap: 12px;">
            <div style="display: flex; align-items: flex-start; justify-content: space-between; gap: 12px;">
                <span style="font-size: 14px; font-weight: 600; color: var(--ula-text-secondary);">{{ __('Total Budget') }}</span>
                <span style="width: 32px; height: 32px; flex-shrink: 0; border-radius: 999px; background: var(--ula-control-tone-palm-fill); color: var(--ula-palm-700); display: inline-flex; align-items: center; justify-content: center;">
                    <span class="material-symbols-rounded" style="font-size: 18px;">attach_money</span>
                </span>
            </div>
            <div style="display: flex; align-items: baseline; gap: 8px; flex-wrap: wrap;">
                <span style="font-size: 32px; font-weight: 400; line-height: 1.1; font-family: 'IBM Plex Mono', monospace;">${{ number_format($projects->sum('budget_amount'), 0) }}</span>
                <span style="font-size: 12px; color: var(--ula-status-success);">{{ __('Allocated capital') }}</span>
            </div>
        </div>
    </div>

    <!-- Projects Table -->
    <div class="card" style="border-radius: var(--ula-radius-xl); overflow: hidden; padding: 0; border: 1px solid var(--ula-border-subtle); background: var(--ula-surface-card); box-shadow: var(--ula-shadow-xs);">
        <div style="padding: 16px 20px; border-bottom: 1px solid var(--ula-border-subtle); display: flex; justify-content: space-between; align-items: center; background: var(--ula-surface-card);">
            <div style="display: flex; align-items: center; gap: 10px;">
                <span class="material-symbols-rounded" style="font-size: 20px; color: var(--ula-palm-700);">assignment</span>
                <h3 style="font-size: 17px; font-weight: 700; color: var(--ula-text-primary); margin: 0;">{{ __('Active Initiatives') }}</h3>
                <span style="font-family: 'IBM Plex Mono', monospace; font-size: 12px; color: var(--ula-text-muted);">({{ $projects->count() }})</span>
            </div>
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
