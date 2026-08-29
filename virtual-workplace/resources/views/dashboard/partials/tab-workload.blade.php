                    <div id="tab-workload" class="tab-view">
            <div class="page-header" style="margin-bottom: 24px;">
                <h1 class="page-title" style="font-size: 22px; font-weight: 900; color: var(--text-primary); margin-bottom: 4px;">👥 {{ __('Team Capacity & Workload Matrix') }}</h1>
                <p class="page-subtitle" style="font-size: 13px; color: var(--text-secondary);">{{ __('Monitor weekly employee availability, assigned hours, and capacity utilization.') }}</p>
            </div>

            <!-- Team Capacity Table -->
            <div class="card" style="border-radius: var(--radius-lg); overflow: hidden; padding: 0;">
                <div style="padding: 20px 24px; border-bottom: 1px solid var(--border-color); background: var(--bg-surface);">
                    <h3 style="font-size: 16px; font-weight: 900; color: var(--text-primary);">📊 {{ __('Employee Workload Distribution') }}</h3>
                </div>
                <div style="overflow-x: auto;">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>{{ __('Team Member') }}</th>
                                <th>{{ __('Role') }}</th>
                                <th>{{ __('Weekly Capacity') }}</th>
                                <th>{{ __('Assigned Tasks') }}</th>
                                <th>{{ __('Estimated Hours') }}</th>
                                <th>{{ __('Capacity Utilization') }}</th>
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
                                <tr>
                                    <td>
                                        <div style="display: flex; align-items: center; gap: 10px;">
                                            <div style="width: 32px; height: 32px; border-radius: 10px; background: var(--accent-gradient); color: white; display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 12px; box-shadow: var(--shadow-soft-3d);">
                                                {{ strtoupper(substr($m->user->name ?? 'M', 0, 2)) }}
                                            </div>
                                            <div>
                                                <div style="font-weight: 800; color: var(--text-primary);">{{ $m->user->name ?? 'Member' }}</div>
                                                <div style="font-size: 11px; color: var(--text-muted);">{{ $m->user->email }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td><span class="nav-badge-pill" style="font-weight: 700;">{{ $m->role->name ?? 'Member' }}</span></td>
                                    <td style="font-weight: 800; font-family: monospace;">{{ $capacity }}h / wk</td>
                                    <td style="font-weight: 700;">{{ $memberTasks->count() }} {{ __('active') }}</td>
                                    <td style="font-weight: 800; color: var(--brand-forest); font-family: monospace;">{{ $assignedHours }}h</td>
                                    <td style="min-width: 180px;">
                                        <div style="display: flex; justify-content: space-between; font-size: 11px; margin-bottom: 5px; font-weight: 800;">
                                            <span style="color: {{ $utilization > 100 ? '#D96B5F' : ($utilization > 80 ? '#D6A23A' : '#4F9B5F') }};">{{ $utilization }}%</span>
                                            <span style="color: var(--text-muted);">{{ $assignedHours }} / {{ $capacity }}h</span>
                                        </div>
                                        <div class="progress-bar-bg" style="background: var(--bg-surface-subtle); height: 7px; border-radius: 9999px; overflow: hidden;">
                                            <div class="progress-bar-fill" style="width: {{ min(100, $utilization) }}%; height: 100%; background: {{ $utilization > 100 ? '#D96B5F' : ($utilization > 80 ? '#D6A23A' : '#4F9B5F') }}; border-radius: 9999px;"></div>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>


