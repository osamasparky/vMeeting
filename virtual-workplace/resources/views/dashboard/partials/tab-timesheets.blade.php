<div id="tab-timesheets" class="tab-view">
            <!-- Top Controls & Filters Bar -->
            <div class="page-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; flex-wrap: wrap; gap: 14px;">
                <div>
                    <h1 class="page-title" style="font-size: 22px; font-weight: 900; color: var(--text-primary); margin-bottom: 4px;">⏱️ {{ __('Timesheets & Time Tracking') }}</h1>
                    <p class="page-subtitle" style="font-size: 13px; color: var(--text-secondary);">{{ __('Automated virtual office attendance, project task duration, and daily productivity analytics.') }}</p>
                </div>
                <div style="display: flex; gap: 10px; flex-wrap: wrap; align-items: center;">
                    <button onclick="openManualTimeModal()" class="tactile-btn btn-secondary" style="padding: 9px 16px; font-size: 12px; font-weight: 800;">
                        <span>✍️</span> {{ __('Manual Time Entry') }}
                    </button>
                    <button onclick="submitMyCurrentTimesheet()" class="tactile-btn btn-primary" style="padding: 9px 18px; font-size: 12px; font-weight: 800;">
                        <span>📤</span> {{ __('Submit Weekly Timesheet') }}
                    </button>
                </div>
            </div>

            <!-- Interactive Date & Member Filter Ribbon -->
            <div class="card" style="padding: 14px 20px; margin-bottom: 20px; border-radius: var(--radius-lg); display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 14px; background: var(--bg-surface);">
                <!-- Date Navigation Bar -->
                <div style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
                    <span style="font-size: 11px; font-weight: 800; color: var(--text-secondary); text-transform: uppercase;">📅 {{ __('Date') }}:</span>
                    <button type="button" onclick="shiftTimesheetDate(-1)" class="tactile-btn btn-secondary" style="padding: 6px 12px; font-size: 12px;" title="{{ __('Previous Day') }}">◀</button>
                    <input type="date" id="ts-filter-date" value="{{ date('Y-m-d') }}" onchange="handleTimesheetDateChange(this.value)" style="background: var(--bg-surface-subtle); border: 1px solid var(--border-color); border-radius: 8px; padding: 6px 12px; color: var(--text-primary); font-size: 12px; font-weight: 800; outline: none; box-shadow: var(--shadow-inset-3d);">
                    <button type="button" onclick="setTimesheetToday()" class="tactile-btn btn-secondary" style="padding: 6px 12px; font-size: 12px; font-weight: 800;">{{ __('Today') }}</button>
                    <button type="button" onclick="shiftTimesheetDate(1)" class="tactile-btn btn-secondary" style="padding: 6px 12px; font-size: 12px;" title="{{ __('Next Day') }}">▶</button>
                </div>

                <!-- Member Selector (for Managers & Admins) -->
                @php
                    $canSelectMember = $membership->hasPermission('reports.view') || $membership->hasPermission('members.manage') || $membership->role?->slug === 'company_admin' || $user->isSuperAdmin();
                @endphp
                <div style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap;">
                    @if($canSelectMember)
                    <div style="display: flex; align-items: center; gap: 6px;">
                        <span style="font-size: 11px; font-weight: 800; color: var(--text-secondary); text-transform: uppercase;">👤 {{ __('Member') }}:</span>
                        <select id="ts-filter-user" onchange="handleTimesheetUserChange(this.value)" style="background: var(--bg-surface-subtle); border: 1px solid var(--border-color); border-radius: 8px; padding: 6px 12px; color: var(--text-primary); font-size: 12px; font-weight: 700; outline: none;">
                            <option value="{{ $user->id }}">{{ __('My Timesheet') }} ({{ $user->name }})</option>
                            @foreach($members as $m)
                                @if($m->user_id !== $user->id)
                                    <option value="{{ $m->user_id }}">{{ $m->user->name }} ({{ $m->role->name ?? 'Member' }})</option>
                                @endif
                            @endforeach
                        </select>
                    </div>
                    @else
                        <input type="hidden" id="ts-filter-user" value="{{ $user->id }}">
                    @endif

                    <button type="button" onclick="refreshDailyTimesheet()" class="tactile-btn btn-secondary" style="padding: 6px 12px; font-size: 12px;" title="{{ __('Refresh Data') }}">
                        🔄 {{ __('Refresh') }}
                    </button>
                </div>
            </div>

            <!-- Daily Summary Metric KPI Cards (4-Grid) -->
            <div class="kpi-grid" style="margin-bottom: 24px;">
                <!-- 1. Total Office Time -->
                <div class="kpi-card">
                    <div class="kpi-header">
                        <span class="kpi-title">{{ __('Time in Virtual Office') }}</span>
                        <div class="kpi-icon-box" style="background: rgba(36, 92, 58, 0.15); color: var(--brand-forest);">🏢</div>
                    </div>
                    <div class="kpi-value" id="ts-kpi-office-time" style="font-family: monospace;">00:00:00</div>
                    <div class="kpi-trend" style="color: var(--brand-forest);">
                        <span>🟢</span> {{ __('Automated presence tracking') }}
                    </div>
                </div>

                <!-- 2. Productive Task Time -->
                <div class="kpi-card">
                    <div class="kpi-header">
                        <span class="kpi-title">{{ __('Productive Task Work') }}</span>
                        <div class="kpi-icon-box" style="background: rgba(79, 155, 95, 0.15); color: #4F9B5F;">⏱️</div>
                    </div>
                    <div class="kpi-value" id="ts-kpi-task-time" style="font-family: monospace; color: var(--brand-forest);">00:00:00</div>
                    <div class="kpi-trend" style="color: var(--brand-forest);">
                        <span>📈</span> {{ __('Logged against active tasks') }}
                    </div>
                </div>

                <!-- 3. Idle / Paused Time -->
                <div class="kpi-card">
                    <div class="kpi-header">
                        <span class="kpi-title">{{ __('Idle / Paused Time') }}</span>
                        <div class="kpi-icon-box" style="background: rgba(214, 162, 58, 0.15); color: #D6A23A;">⏸️</div>
                    </div>
                    <div class="kpi-value" id="ts-kpi-idle-time" style="font-family: monospace; color: #D6A23A;">00:00:00</div>
                    <div class="kpi-trend" style="color: var(--text-muted);">
                        <span>⏳</span> {{ __('Inactivity stops excluded') }}
                    </div>
                </div>

                <!-- 4. Productivity Ratio -->
                <div class="kpi-card">
                    <div class="kpi-header">
                        <span class="kpi-title">{{ __('Productivity Ratio') }}</span>
                        <div class="kpi-icon-box" style="background: rgba(59, 130, 246, 0.15); color: #3B82F6;">📊</div>
                    </div>
                    <div class="kpi-value" id="ts-kpi-ratio" style="font-family: monospace;">0%</div>
                    <div class="kpi-trend" style="color: var(--brand-forest);">
                        <span>⚡</span> {{ __('Task Time ÷ Office Time') }}
                    </div>
                </div>
            </div>

            <!-- Live Active Timer Banner (Dynamic) -->
            <div id="ts-live-timer-banner" style="display: none; background: linear-gradient(135deg, rgba(79, 155, 95, 0.15) 0%, rgba(36, 92, 58, 0.08) 100%); border: 1px solid rgba(79, 155, 95, 0.4); border-radius: var(--radius-lg); padding: 14px 20px; margin-bottom: 24px; box-shadow: var(--shadow-soft-3d);">
                <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px;">
                    <div style="display: flex; align-items: center; gap: 12px;">
                        <span class="live-indicator-dot pulse"></span>
                        <div>
                            <div style="display: flex; align-items: center; gap: 8px;">
                                <span style="font-size: 11px; font-weight: 900; color: #4F9B5F; text-transform: uppercase;">{{ __('Active Task Running in Office') }}</span>
                                <span class="nav-badge-pill" id="ts-banner-project-pill" style="background: rgba(79, 155, 95, 0.2); color: #4F9B5F; font-size: 10px;">Project</span>
                            </div>
                            <h4 id="ts-banner-task-title" style="font-size: 15px; font-weight: 800; color: var(--text-primary); margin: 2px 0 0 0;">Task Title</h4>
                        </div>
                    </div>
                    <div style="display: flex; align-items: center; gap: 14px;">
                        <div id="ts-banner-clock" style="font-size: 20px; font-weight: 900; font-family: monospace; color: var(--brand-forest);">00:00:00</div>
                        <button type="button" onclick="stopGlobalTimer()" class="tactile-btn" style="background: #D96B5F; color: white; border: none; padding: 7px 14px; font-size: 11px; font-weight: 800;">
                            ⏹ {{ __('Stop Timer') }}
                        </button>
                    </div>
                </div>
            </div>

            <!-- ========================================== -->
            <!-- SECTION 1: PROJECT & TASK WORK DETAILS -->
            <!-- ========================================== -->
            <div class="card" style="border-radius: var(--radius-lg); overflow: hidden; padding: 0; margin-bottom: 24px; border: 1px solid var(--border-color); box-shadow: var(--shadow-card);">
                <div style="padding: 18px 24px; border-bottom: 1px solid var(--border-color); display: flex; justify-content: space-between; align-items: center; background: var(--bg-surface);">
                    <div style="display: flex; align-items: center; gap: 10px;">
                        <div style="width: 32px; height: 32px; border-radius: 8px; background: rgba(79, 155, 95, 0.15); color: #4F9B5F; display: flex; align-items: center; justify-content: center; font-size: 16px;">📋</div>
                        <div>
                            <h3 style="font-size: 16px; font-weight: 900; color: var(--text-primary); margin: 0;">{{ __('Section 1: Project & Task Work Details') }} ({{ __('ساعات إنجاز المهام والمشاريع') }})</h3>
                            <p style="font-size: 11px; color: var(--text-secondary); margin: 2px 0 0 0;">{{ __('Detailed breakdown of all work orders, milestones, and task sessions completed on this date.') }}</p>
                        </div>
                    </div>
                    <span class="nav-badge-pill" id="ts-tasks-count-pill" style="background: rgba(79, 155, 95, 0.15); color: #4F9B5F; font-size: 11px; font-weight: 800;">0 {{ __('Tasks') }}</span>
                </div>
                <div style="overflow-x: auto;">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>{{ __('Task & Code') }}</th>
                                <th>{{ __('Project') }}</th>
                                <th>{{ __('Time Window') }}</th>
                                <th>{{ __('Duration') }}</th>
                                <th>{{ __('Type / Billing') }}</th>
                                <th>{{ __('Status') }}</th>
                            </tr>
                        </thead>
                        <tbody id="ts-tasks-tbody">
                            <tr>
                                <td colspan="6" style="text-align: center; padding: 32px; color: var(--text-muted);">
                                    ⏳ {{ __('Loading task time entries...') }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- ========================================== -->
            <!-- SECTION 2: VIRTUAL OFFICE ATTENDANCE LOG -->
            <!-- ========================================== -->
            <div class="card" style="border-radius: var(--radius-lg); overflow: hidden; padding: 0; margin-bottom: 24px; border: 1px solid var(--border-color); box-shadow: var(--shadow-card);">
                <div style="padding: 18px 24px; border-bottom: 1px solid var(--border-color); display: flex; justify-content: space-between; align-items: center; background: var(--bg-surface);">
                    <div style="display: flex; align-items: center; gap: 10px;">
                        <div style="width: 32px; height: 32px; border-radius: 8px; background: rgba(36, 92, 58, 0.15); color: var(--brand-forest); display: flex; align-items: center; justify-content: center; font-size: 16px;">🏢</div>
                        <div>
                            <h3 style="font-size: 16px; font-weight: 900; color: var(--text-primary); margin: 0;">{{ __('Section 2: Virtual Office Attendance & Presence Log') }} ({{ __('سجلات التواجد وساعات العمل في المكتب') }})</h3>
                            <p style="font-size: 11px; color: var(--text-secondary); margin: 2px 0 0 0;">{{ __('Recorded 3D office presence sessions, check-ins, idle pauses, and branch room presence.') }}</p>
                        </div>
                    </div>
                    <span class="nav-badge-pill" id="ts-attendance-count-pill" style="background: rgba(36, 92, 58, 0.15); color: var(--brand-forest); font-size: 11px; font-weight: 800;">0 {{ __('Sessions') }}</span>
                </div>
                <div style="overflow-x: auto;">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>{{ __('Branch Office / Zone') }}</th>
                                <th>{{ __('Check-In Time') }}</th>
                                <th>{{ __('Check-Out Time') }}</th>
                                <th>{{ __('Duration') }}</th>
                                <th>{{ __('Session Status') }}</th>
                            </tr>
                        </thead>
                        <tbody id="ts-attendance-tbody">
                            <tr>
                                <td colspan="5" style="text-align: center; padding: 32px; color: var(--text-muted);">
                                    ⏳ {{ __('Loading office attendance sessions...') }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- ========================================== -->
            <!-- SECTION 3: MANAGER TIMESHEET SUBMISSIONS REVIEW -->
            <!-- ========================================== -->
            <div class="card" style="border-radius: var(--radius-lg); overflow: hidden; padding: 0; border: 1px solid var(--border-color); box-shadow: var(--shadow-card);">
                <div style="padding: 18px 24px; border-bottom: 1px solid var(--border-color); background: var(--bg-surface); display: flex; justify-content: space-between; align-items: center;">
                    <div style="display: flex; align-items: center; gap: 10px;">
                        <div style="width: 32px; height: 32px; border-radius: 8px; background: rgba(214, 162, 58, 0.15); color: #D6A23A; display: flex; align-items: center; justify-content: center; font-size: 16px;">📑</div>
                        <div>
                            <h3 style="font-size: 16px; font-weight: 900; color: var(--text-primary); margin: 0;">{{ __('Timesheet Submissions Review Queue') }}</h3>
                            <p style="font-size: 11px; color: var(--text-secondary); margin: 2px 0 0 0;">{{ __('Weekly employee submissions pending manager approval and payroll audit lock.') }}</p>
                        </div>
                    </div>
                </div>
                <div style="overflow-x: auto;">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>{{ __('Employee') }}</th>
                                <th>{{ __('Period') }}</th>
                                <th>{{ __('Total Hours') }}</th>
                                <th>{{ __('Billable') }}</th>
                                <th>{{ __('Status') }}</th>
                                <th>{{ __('Action') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($allTimesheets as $ts)
                                <tr>
                                    <td style="font-weight: 800; color: var(--text-primary);">{{ $ts->user->name ?? 'Member' }}</td>
                                    <td>{{ $ts->period_start->format('M d') }} — {{ $ts->period_end->format('M d, Y') }}</td>
                                    <td style="font-weight: 900; color: var(--text-primary); font-family: monospace;">{{ $ts->total_hours }}h</td>
                                    <td style="color: var(--brand-forest); font-weight: 800; font-family: monospace;">{{ $ts->billable_hours }}h</td>
                                    <td>
                                        @if($ts->status === 'approved')
                                            <span class="nav-badge-pill" style="background: rgba(79, 155, 95, 0.15); color: #4F9B5F;">✅ {{ __('Approved') }}</span>
                                        @elseif($ts->status === 'submitted')
                                            <span class="nav-badge-pill" style="background: rgba(214, 162, 58, 0.15); color: #D6A23A;">⏳ {{ __('Pending Review') }}</span>
                                        @elseif($ts->status === 'rejected')
                                            <span class="nav-badge-pill" style="background: rgba(217, 107, 95, 0.15); color: #D96B5F;">❌ {{ __('Rejected') }}</span>
                                        @else
                                            <span class="nav-badge-pill">{{ ucfirst($ts->status) }}</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($ts->status === 'submitted' && ($membership->hasPermission('timesheets.approve') || $user->isSuperAdmin()))
                                            <div style="display: flex; gap: 6px;">
                                                <button onclick="approveTimesheet('{{ $ts->id }}')" class="tactile-btn" style="background: rgba(79, 155, 95, 0.15); color: var(--brand-forest); border: 1px solid rgba(79, 155, 95, 0.3); padding: 4px 10px; font-size: 11px;">
                                                    ✓ {{ __('Approve') }}
                                                </button>
                                                <button onclick="openRejectModal('{{ $ts->id }}')" class="tactile-btn" style="background: rgba(217, 107, 95, 0.15); color: #D96B5F; border: 1px solid rgba(217, 107, 95, 0.3); padding: 4px 10px; font-size: 11px;">
                                                    ✕ {{ __('Reject') }}
                                                </button>
                                            </div>
                                        @else
                                            <span style="font-size: 11px; color: var(--text-muted);">—</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" style="text-align: center; padding: 32px; color: var(--text-muted);">
                                        {{ __('No timesheets submitted for review.') }}
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- 11. TEAM WORKLOAD TAB -->
        