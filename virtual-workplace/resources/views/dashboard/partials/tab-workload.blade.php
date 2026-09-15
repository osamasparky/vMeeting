<div id="tab-workload" class="tab-view">
    <div class="page-header" style="margin-bottom: var(--nx-spacing-6);">
        <h1 class="page-title" style="font-size: var(--nx-font-size-2xl); font-weight: var(--nx-font-weight-black); color: var(--nx-text-primary); margin-bottom: var(--nx-spacing-1); display: flex; align-items: center; gap: var(--nx-spacing-2);">
            <span class="material-symbols-rounded" style="font-size: 28px; color: var(--nx-primary-500);">stacked_bar_chart</span>
            <span>{{ __('Team Capacity & Workload Matrix') }}</span>
        </h1>
        <p class="page-subtitle" style="font-size: var(--nx-font-size-sm); color: var(--nx-text-secondary);">{{ __('Monitor weekly employee availability, assigned hours, and capacity utilization.') }}</p>
    </div>

    <!-- Team Capacity Table -->
    <div class="card" style="border-radius: var(--nx-radius-xl); overflow: hidden; padding: 0; border: 1px solid var(--nx-border-subtle); box-shadow: var(--nx-shadow-card); background: var(--nx-bg-surface);">
        <div style="padding: var(--nx-spacing-5) var(--nx-spacing-6); border-bottom: 1px solid var(--nx-border-subtle); background: var(--nx-bg-surface);">
            <h3 style="font-size: var(--nx-font-size-md); font-weight: var(--nx-font-weight-black); color: var(--nx-text-primary); display: flex; align-items: center; gap: var(--nx-spacing-2); margin: 0;">
                <span class="material-symbols-rounded" style="font-size: 20px; color: var(--nx-primary-500);">leaderboard</span>
                <span>{{ __('Employee Workload Distribution') }}</span>
            </h3>
        </div>
        <div style="overflow-x: auto;">
            <table class="data-table" style="width: 100%; border-collapse: collapse;">
                <thead>
                    <tr>
                        <th style="padding: 12px 16px; text-align: start; font-size: var(--nx-font-size-xs); font-weight: var(--nx-font-weight-bold); color: var(--nx-text-secondary); border-bottom: 1px solid var(--nx-border-subtle);">{{ __('Team Member') }}</th>
                        <th style="padding: 12px 16px; text-align: start; font-size: var(--nx-font-size-xs); font-weight: var(--nx-font-weight-bold); color: var(--nx-text-secondary); border-bottom: 1px solid var(--nx-border-subtle);">{{ __('Role') }}</th>
                        <th style="padding: 12px 16px; text-align: start; font-size: var(--nx-font-size-xs); font-weight: var(--nx-font-weight-bold); color: var(--nx-text-secondary); border-bottom: 1px solid var(--nx-border-subtle);">{{ __('Weekly Capacity') }}</th>
                        <th style="padding: 12px 16px; text-align: start; font-size: var(--nx-font-size-xs); font-weight: var(--nx-font-weight-bold); color: var(--nx-text-secondary); border-bottom: 1px solid var(--nx-border-subtle);">{{ __('Assigned Tasks') }}</th>
                        <th style="padding: 12px 16px; text-align: start; font-size: var(--nx-font-size-xs); font-weight: var(--nx-font-weight-bold); color: var(--nx-text-secondary); border-bottom: 1px solid var(--nx-border-subtle);">{{ __('Estimated Hours') }}</th>
                        <th style="padding: 12px 16px; text-align: start; font-size: var(--nx-font-size-xs); font-weight: var(--nx-font-weight-bold); color: var(--nx-text-secondary); border-bottom: 1px solid var(--nx-border-subtle);">{{ __('Capacity Utilization') }}</th>
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
                        <tr style="border-bottom: 1px solid var(--nx-border-subtle);">
                            <td style="padding: 14px 16px;">
                                <div style="display: flex; align-items: center; gap: var(--nx-spacing-3);">
                                    <div style="width: 32px; height: 32px; border-radius: var(--nx-radius-md); background: var(--nx-accent-gradient); color: white; display: flex; align-items: center; justify-content: center; font-weight: var(--nx-font-weight-bold); font-size: 12px; font-family: var(--nx-font-mono); box-shadow: var(--nx-shadow-soft-3d);">
                                        {{ strtoupper(substr($m->user->name ?? 'M', 0, 2)) }}
                                    </div>
                                    <div>
                                        <div style="font-weight: var(--nx-font-weight-bold); color: var(--nx-text-primary); font-size: var(--nx-font-size-sm);">{{ $m->user->name ?? 'Member' }}</div>
                                        <div style="font-size: var(--nx-font-size-xs); color: var(--nx-text-muted);">{{ $m->user->email }}</div>
                                    </div>
                                </div>
                            </td>
                            <td style="padding: 14px 16px;"><span class="nav-badge-pill" style="font-weight: var(--nx-font-weight-bold);">{{ $m->role->name ?? 'Member' }}</span></td>
                            <td style="padding: 14px 16px; font-weight: var(--nx-font-weight-bold); font-family: var(--nx-font-mono); font-size: var(--nx-font-size-sm); color: var(--nx-text-primary);">{{ $capacity }}h / wk</td>
                            <td style="padding: 14px 16px; font-weight: var(--nx-font-weight-bold); font-family: var(--nx-font-mono); font-size: var(--nx-font-size-sm); color: var(--nx-text-primary);">{{ $memberTasks->count() }} {{ __('active') }}</td>
                            <td style="padding: 14px 16px; font-weight: var(--nx-font-weight-bold); color: var(--nx-primary-500); font-family: var(--nx-font-mono); font-size: var(--nx-font-size-sm);">{{ $assignedHours }}h</td>
                            <td style="padding: 14px 16px; min-width: 180px;">
                                <div style="display: flex; justify-content: space-between; font-size: var(--nx-font-size-xs); margin-bottom: 5px; font-weight: var(--nx-font-weight-bold); font-family: var(--nx-font-mono);">
                                    <span style="color: {{ $utilization > 100 ? '#D96B5F' : ($utilization > 80 ? '#D6A23A' : 'var(--nx-primary-500)') }};">{{ $utilization }}%</span>
                                    <span style="color: var(--nx-text-muted);">{{ $assignedHours }} / {{ $capacity }}h</span>
                                </div>
                                <div class="progress-bar-bg" style="background: var(--nx-bg-surface-subtle); height: 7px; border-radius: var(--nx-radius-full); overflow: hidden; border: 1px solid var(--nx-border-subtle);">
                                    <div class="progress-bar-fill" style="width: {{ min(100, $utilization) }}%; height: 100%; background: {{ $utilization > 100 ? '#D96B5F' : ($utilization > 80 ? '#D6A23A' : 'var(--nx-primary-500)') }}; border-radius: var(--nx-radius-full);"></div>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
