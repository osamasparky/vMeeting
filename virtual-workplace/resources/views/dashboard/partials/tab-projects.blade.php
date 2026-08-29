        <div id="tab-projects" class="tab-view">
            <div class="page-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px;">
                <div>
                    <h1 class="page-title" style="font-size: 22px; font-weight: 900; color: var(--text-primary); margin-bottom: 4px;">📁 {{ __('Projects Portfolio') }}</h1>
                    <p class="page-subtitle" style="font-size: 13px; color: var(--text-secondary);">{{ __('Manage company initiatives, milestones, tasks, and budgets.') }}</p>
                </div>
                @if($membership->hasPermission('projects.manage') || $membership->role?->slug === 'company_admin')
                <div style="display: flex; gap: 10px;">
                    <button onclick="openNewProjectModal()" class="tactile-btn btn-primary" style="padding: 10px 18px; font-size: 13px;">
                        <span>+</span> {{ __('New Project') }}
                    </button>
                </div>
                @endif
            </div>

            <!-- Project KPI Metrics (3D Soft Neumorphic) -->
            <div class="kpi-grid" style="margin-bottom: 24px;">
                <div class="kpi-card">
                    <div class="kpi-header">
                        <span class="kpi-title">{{ __('Total Projects') }}</span>
                        <div class="kpi-icon-box">📁</div>
                    </div>
                    <div class="kpi-value">{{ $projects->count() }}</div>
                    <div class="kpi-trend" style="color: var(--brand-forest);">
                        <span>🟢</span> {{ $projects->where('status', 'active')->count() }} {{ __('Active initiatives') }}
                    </div>
                </div>
                <div class="kpi-card">
                    <div class="kpi-header">
                        <span class="kpi-title">{{ __('Total Tasks') }}</span>
                        <div class="kpi-icon-box">✅</div>
                    </div>
                    <div class="kpi-value">{{ $tasks->count() }}</div>
                    <div class="kpi-trend" style="color: var(--brand-forest);">
                        <span>⚡</span> {{ $tasks->where('status', '!=', 'done')->count() }} {{ __('In progress / Backlog') }}
                    </div>
                </div>
                <div class="kpi-card">
                    <div class="kpi-header">
                        <span class="kpi-title">{{ __('Logged Hours') }}</span>
                        <div class="kpi-icon-box">⏱️</div>
                    </div>
                    <div class="kpi-value">{{ round($projects->sum(fn($p) => $p->actualHours()), 1) }}h</div>
                    <div class="kpi-trend" style="color: var(--brand-forest);">
                        <span>📈</span> {{ __('Tracked across all tasks') }}
                    </div>
                </div>
                <div class="kpi-card">
                    <div class="kpi-header">
                        <span class="kpi-title">{{ __('Total Budget') }}</span>
                        <div class="kpi-icon-box">💰</div>
                    </div>
                    <div class="kpi-value">${{ number_format($projects->sum('budget_amount'), 0) }}</div>
                    <div class="kpi-trend" style="color: var(--brand-forest);">
                        <span>💎</span> {{ __('Allocated capital') }}
                    </div>
                </div>
            </div>

            <!-- Projects Table -->
            <div class="card" style="border-radius: var(--radius-lg); overflow: hidden; padding: 0;">
                <div style="padding: 20px 24px; border-bottom: 1px solid var(--border-color); display: flex; justify-content: space-between; align-items: center; background: var(--bg-surface);">
                    <h3 style="font-size: 16px; font-weight: 900; color: var(--text-primary);">📋 {{ __('Active Initiatives') }} ({{ $projects->count() }})</h3>
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
                                    <td><span class="nav-badge-pill" style="font-family: monospace;">{{ $p->code ?? 'PRJ' }}</span></td>
                                    <td>
                                        <div style="font-weight: 800; color: var(--text-primary);">{{ $p->name }}</div>
                                        <div style="font-size: 11px; color: var(--text-muted);">{{ Str::limit($p->description, 50) }}</div>
                                    </td>
                                    <td>
                                        <div style="display: flex; align-items: center; gap: 8px;">
                                            <div style="width: 26px; height: 26px; border-radius: 8px; background: var(--accent-gradient); color: white; display: flex; align-items: center; justify-content: center; font-size: 10px; font-weight: 800; box-shadow: var(--shadow-soft-3d);">
                                                {{ strtoupper(substr($p->manager->name ?? 'NA', 0, 2)) }}
                                            </div>
                                            <span style="font-weight: 600; font-size: 13px;">{{ $p->manager->name ?? 'Unassigned' }}</span>
                                        </div>
                                    </td>
                                    <td>
                                        @if($p->status === 'active')
                                            <span class="nav-badge-pill" style="background: rgba(79, 155, 95, 0.15); color: #4F9B5F; border-color: rgba(79, 155, 95, 0.3);">{{ __('Active') }}</span>
                                        @elseif($p->status === 'completed')
                                            <span class="nav-badge-pill" style="background: rgba(113, 155, 115, 0.15); color: #719B73; border-color: rgba(113, 155, 115, 0.3);">{{ __('Completed') }}</span>
                                        @elseif($p->status === 'on_hold')
                                            <span class="nav-badge-pill" style="background: rgba(214, 162, 58, 0.15); color: #D6A23A; border-color: rgba(214, 162, 58, 0.3);">{{ __('On Hold') }}</span>
                                        @else
                                            <span class="nav-badge-pill">{{ ucfirst($p->status) }}</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($p->priority === 'urgent')
                                            <span class="nav-badge-pill" style="background: rgba(217, 107, 95, 0.15); color: #D96B5F; border-color: rgba(217, 107, 95, 0.3);">🔥 {{ __('Urgent') }}</span>
                                        @elseif($p->priority === 'high')
                                            <span class="nav-badge-pill" style="background: rgba(214, 162, 58, 0.15); color: #D6A23A; border-color: rgba(214, 162, 58, 0.3);">⚡ {{ __('High') }}</span>
                                        @else
                                            <span class="nav-badge-pill">{{ ucfirst($p->priority) }}</span>
                                        @endif
                                    </td>
                                    <td style="min-width: 140px;">
                                        @php $pct = $p->progressPercentage(); @endphp
                                        <div style="display: flex; justify-content: space-between; font-size: 11px; margin-bottom: 4px; font-weight: 700;">
                                            <span>{{ $pct }}%</span>
                                            <span style="color: var(--text-muted);">{{ $p->tasks_count }} {{ __('tasks') }}</span>
                                        </div>
                                        <div class="progress-bar-bg" style="background: var(--bg-surface-subtle); height: 7px; border-radius: 9999px; overflow: hidden;">
                                            <div class="progress-bar-fill" style="width: {{ $pct }}%; height: 100%; background: {{ $pct === 100 ? '#4F9B5F' : 'var(--accent-gradient)' }}; border-radius: 9999px;"></div>
                                        </div>
                                    </td>
                                    <td style="font-size: 12px; font-weight: 600;">{{ $p->due_date ? $p->due_date->format('M d, Y') : '—' }}</td>
                                    <td style="font-weight: 800; color: var(--brand-forest);">${{ number_format($p->budget_amount ?? 0, 0) }}</td>
                                    <td>
                                        @if($canOpenHub)
                                            <a href="{{ route('projects.hub', $p->id) }}" onclick="event.stopPropagation();" class="tactile-btn btn-primary" style="padding: 6px 12px; font-size: 11px; text-decoration: none;">
                                                📊 {{ __('Open Hub') }}
                                            </a>
                                        @else
                                            <span class="nav-badge-pill" style="font-size: 10px; color: var(--text-muted);">
                                                👁️ {{ __('View Details') }}
                                            </span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" style="text-align: center; padding: 36px; color: var(--text-muted);">
                                        📁 {{ __('No projects created yet.') }}
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- 9. ALL TASKS MANAGER TAB (Project Manager View) -->
