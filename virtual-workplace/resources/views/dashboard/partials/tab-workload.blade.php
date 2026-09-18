<div id="tab-workload" class="tab-view">
    <div class="page-header" style="margin-bottom: var(--ula-space-7);">
        <h1 class="page-title" style="font-size: var(--ula-size-h3); font-weight: var(--ula-weight-bold); color: var(--ula-text-primary); margin-bottom: var(--ula-space-2); display: flex; align-items: center; gap: var(--ula-space-3);">
            <span class="material-symbols-rounded" style="font-size: 28px; color: var(--ula-accent-default);">stacked_bar_chart</span>
            <span>{{ __('Team Capacity & Workload Matrix') }}</span>
        </h1>
        <p class="page-subtitle" style="font-size: var(--ula-size-sm); color: var(--ula-text-secondary);">{{ __('Monitor weekly employee availability, assigned hours, and capacity utilization.') }}</p>
    </div>

    <!-- Team Capacity Table -->
    <div class="card" style="border-radius: var(--ula-radius-xl); overflow: hidden; padding: 0; border: 1px solid var(--ula-border-subtle); box-shadow: var(--ula-shadow-xs); background: var(--ula-surface-card);">
        <div style="padding: var(--ula-space-6) var(--ula-space-7); border-bottom: 1px solid var(--ula-border-subtle); background: var(--ula-surface-card);">
            <h3 style="font-size: var(--ula-size-body); font-weight: var(--ula-weight-bold); color: var(--ula-text-primary); display: flex; align-items: center; gap: var(--ula-space-3); margin: 0;">
                <span class="material-symbols-rounded" style="font-size: 20px; color: var(--ula-accent-default);">leaderboard</span>
                <span>{{ __('Employee Workload Distribution') }}</span>
            </h3>
        </div>
        <div style="overflow-x: auto;">
            <table class="data-table" style="width: 100%; border-collapse: collapse;">
                <thead>
                    <tr>
                        <th style="padding: 12px 16px; text-align: start; font-size: var(--ula-size-xs); font-weight: var(--ula-weight-bold); color: var(--ula-text-secondary); border-bottom: 1px solid var(--ula-border-subtle);">{{ __('Team Member') }}</th>
                        <th style="padding: 12px 16px; text-align: start; font-size: var(--ula-size-xs); font-weight: var(--ula-weight-bold); color: var(--ula-text-secondary); border-bottom: 1px solid var(--ula-border-subtle);">{{ __('Role') }}</th>
                        <th style="padding: 12px 16px; text-align: start; font-size: var(--ula-size-xs); font-weight: var(--ula-weight-bold); color: var(--ula-text-secondary); border-bottom: 1px solid var(--ula-border-subtle);">{{ __('Weekly Capacity') }}</th>
                        <th style="padding: 12px 16px; text-align: start; font-size: var(--ula-size-xs); font-weight: var(--ula-weight-bold); color: var(--ula-text-secondary); border-bottom: 1px solid var(--ula-border-subtle);">{{ __('Assigned Tasks') }}</th>
                        <th style="padding: 12px 16px; text-align: start; font-size: var(--ula-size-xs); font-weight: var(--ula-weight-bold); color: var(--ula-text-secondary); border-bottom: 1px solid var(--ula-border-subtle);">{{ __('Estimated Hours') }}</th>
                        <th style="padding: 12px 16px; text-align: start; font-size: var(--ula-size-xs); font-weight: var(--ula-weight-bold); color: var(--ula-text-secondary); border-bottom: 1px solid var(--ula-border-subtle);">{{ __('Capacity Utilization') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($members as $m)
                        @php
                            $capacity = $m->weekly_capacity_hours ?? 40.00;
                            $memberTasks = $tasks->where('assignee_id', $m->user_id)->where('status', '!=', 'done');
                            $assignedHours = $memberTasks->sum('estimated_hours');
                            $utilization = ($capacity > 0) ? round(($assignedHours / $capacity) * 100) : 0;
                        @endphp
                        <tr style="border-bottom: 1px solid var(--ula-border-subtle);">
                            <td style="padding: 14px 16px;">
                                <div style="display: flex; align-items: center; gap: var(--ula-space-4);">
                                    <div style="width: 32px; height: 32px; border-radius: var(--ula-radius-md); background: var(--ula-gradient-accent); color: var(--ula-white); display: flex; align-items: center; justify-content: center; font-weight: var(--ula-weight-bold); font-size: 12px; font-family: var(--ula-font-mono); direction: ltr; unicode-bidi: isolate; box-shadow: var(--ula-shadow-xs);">
                                        {{ strtoupper(substr($m->user->name ?? 'M', 0, 2)) }}
                                    </div>
                                    <div>
                                        <div style="font-weight: var(--ula-weight-bold); color: var(--ula-text-primary); font-size: var(--ula-size-sm);">{{ $m->user->name ?? 'Member' }}</div>
                                        <div style="font-size: var(--ula-size-xs); color: var(--ula-text-muted);">{{ $m->user->email }}</div>
                                    </div>
                                </div>
                            </td>
                            <td style="padding: 14px 16px;"><span class="nav-badge-pill" style="font-weight: var(--ula-weight-bold);">{{ $m->role->name ?? 'Member' }}</span></td>
                            <td style="padding: 14px 16px; font-weight: var(--ula-weight-bold); font-family: var(--ula-font-mono); direction: ltr; unicode-bidi: isolate; font-size: var(--ula-size-sm); color: var(--ula-text-primary);">{{ $capacity }}h / wk</td>
                            <td style="padding: 14px 16px; font-weight: var(--ula-weight-bold); font-family: var(--ula-font-mono); direction: ltr; unicode-bidi: isolate; font-size: var(--ula-size-sm); color: var(--ula-text-primary);">{{ $memberTasks->count() }} {{ __('active') }}</td>
                            <td style="padding: 14px 16px; font-weight: var(--ula-weight-bold); color: var(--ula-accent-default); font-family: var(--ula-font-mono); direction: ltr; unicode-bidi: isolate; font-size: var(--ula-size-sm);">{{ $assignedHours }}h</td>
                            <td style="padding: 14px 16px; min-width: 180px;">
                                <div style="display: flex; justify-content: space-between; font-size: var(--ula-size-xs); margin-bottom: 5px; font-weight: var(--ula-weight-bold); font-family: var(--ula-font-mono); direction: ltr; unicode-bidi: isolate;">
                                    <span style="color: {{ $utilization > 100 ? 'var(--ula-status-danger)' : ($utilization > 80 ? 'var(--ula-gold-400)' : 'var(--ula-accent-default)') }};">{{ $utilization }}%</span>
                                    <span style="color: var(--ula-text-muted);">{{ $assignedHours }} / {{ $capacity }}h</span>
                                </div>
                                <div class="progress-bar-bg" style="background: var(--ula-surface-page-alt); height: 7px; border-radius: var(--ula-radius-pill); overflow: hidden; border: 1px solid var(--ula-border-subtle);">
                                    <div class="progress-bar-fill" style="width: {{ min(100, $utilization) }}%; height: 100%; background: {{ $utilization > 100 ? 'var(--ula-status-danger)' : ($utilization > 80 ? 'var(--ula-gold-400)' : 'var(--ula-accent-default)') }}; border-radius: var(--ula-radius-pill);"></div>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
