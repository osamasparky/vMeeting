<div id="tab-timesheets" class="tab-view">
    <!-- Top Controls & Filters Bar -->
    <div class="page-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: var(--nx-spacing-5); flex-wrap: wrap; gap: var(--nx-spacing-4);">
        <div>
            <h1 class="page-title" style="font-size: var(--nx-font-size-2xl); font-weight: var(--nx-font-weight-black); color: var(--nx-text-primary); margin-bottom: var(--nx-spacing-1); display: flex; align-items: center; gap: var(--nx-spacing-2);">
                <span class="material-symbols-rounded" style="font-size: 28px; color: var(--nx-primary-500);">schedule</span>
                <span>{{ __('Timesheets & Time Tracking') }}</span>
            </h1>
            <p class="page-subtitle" style="font-size: var(--nx-font-size-sm); color: var(--nx-text-secondary);">{{ __('Automated virtual office attendance, project task duration, and daily productivity analytics.') }}</p>
        </div>
        <div style="display: flex; gap: var(--nx-spacing-3); flex-wrap: wrap; align-items: center;">
            <button onclick="openManualTimeModal()" class="tactile-btn btn-secondary" style="padding: 9px 16px; font-size: var(--nx-font-size-xs); font-weight: var(--nx-font-weight-bold); display: inline-flex; align-items: center; gap: var(--nx-spacing-2);">
                <span class="material-symbols-rounded" style="font-size: 16px;">edit_note</span>
                <span>{{ __('Manual Time Entry') }}</span>
            </button>
            <button onclick="submitMyCurrentTimesheet()" class="tactile-btn btn-primary" style="padding: 9px 18px; font-size: var(--nx-font-size-xs); font-weight: var(--nx-font-weight-bold); display: inline-flex; align-items: center; gap: var(--nx-spacing-2);">
                <span class="material-symbols-rounded" style="font-size: 16px;">publish</span>
                <span>{{ __('Submit Weekly Timesheet') }}</span>
            </button>
        </div>
    </div>

    <!-- Interactive Date & Member Filter Ribbon -->
    <div class="card" style="padding: var(--nx-spacing-4) var(--nx-spacing-5); margin-bottom: var(--nx-spacing-5); border-radius: var(--nx-radius-xl); display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: var(--nx-spacing-4); background: var(--nx-bg-surface); border: 1px solid var(--nx-border-subtle); box-shadow: var(--nx-shadow-sm);">
        <!-- Date Navigation Bar -->
        <div style="display: flex; align-items: center; gap: var(--nx-spacing-2); flex-wrap: wrap;">
            <span style="font-size: var(--nx-font-size-xs); font-weight: var(--nx-font-weight-bold); color: var(--nx-text-secondary); text-transform: uppercase; display: flex; align-items: center; gap: 4px; letter-spacing: 0.04em;">
                <span class="material-symbols-rounded" style="font-size: 14px; color: var(--nx-text-muted);">calendar_today</span>
                <span>{{ __('Date') }}:</span>
            </span>
            <button type="button" onclick="shiftTimesheetDate(-1)" class="tactile-btn btn-secondary" style="padding: 6px 12px; font-size: 12px; display: inline-flex; align-items: center; justify-content: center;" title="{{ __('Previous Day') }}">
                <span class="material-symbols-rounded" style="font-size: 14px;">chevron_left</span>
            </button>
            <input type="date" id="ts-filter-date" value="{{ date('Y-m-d') }}" onchange="handleTimesheetDateChange(this.value)" style="background: var(--nx-bg-surface-subtle); border: 1px solid var(--nx-border-subtle); border-radius: var(--nx-radius-md); padding: 6px 12px; color: var(--nx-text-primary); font-size: var(--nx-font-size-xs); font-weight: var(--nx-font-weight-bold); font-family: var(--nx-font-mono); outline: none; box-shadow: var(--nx-shadow-inset-3d);">
            <button type="button" onclick="setTimesheetToday()" class="tactile-btn btn-secondary" style="padding: 6px 12px; font-size: var(--nx-font-size-xs); font-weight: var(--nx-font-weight-bold);">{{ __('Today') }}</button>
            <button type="button" onclick="shiftTimesheetDate(1)" class="tactile-btn btn-secondary" style="padding: 6px 12px; font-size: 12px; display: inline-flex; align-items: center; justify-content: center;" title="{{ __('Next Day') }}">
                <span class="material-symbols-rounded" style="font-size: 14px;">chevron_right</span>
            </button>
        </div>

        <!-- Member Selector (for Managers & Admins) -->
        @php
            $canSelectMember = $membership->hasPermission('reports.view') || $membership->hasPermission('members.manage') || $membership->role?->slug === 'company_admin' || $user->isSuperAdmin();
        @endphp
        <div style="display: flex; align-items: center; gap: var(--nx-spacing-3); flex-wrap: wrap;">
            @if($canSelectMember)
            <div style="display: flex; align-items: center; gap: 6px;">
                <span style="font-size: var(--nx-font-size-xs); font-weight: var(--nx-font-weight-bold); color: var(--nx-text-secondary); text-transform: uppercase; display: flex; align-items: center; gap: 4px; letter-spacing: 0.04em;">
                    <span class="material-symbols-rounded" style="font-size: 14px; color: var(--nx-text-muted);">person</span>
                    <span>{{ __('Member / View') }}:</span>
                </span>
                <select id="ts-filter-user" onchange="handleTimesheetUserChange(this.value)" style="background: var(--nx-bg-surface-subtle); border: 1px solid var(--nx-border-subtle); border-radius: var(--nx-radius-md); padding: 6px 12px; color: var(--nx-text-primary); font-size: var(--nx-font-size-xs); font-weight: 700; outline: none;">
                    <option value="all">{{ __('All Employees (Company Overview)') }}</option>
                    <option value="{{ $user->id }}" selected>{{ __('My Timesheet') }} ({{ $user->name }})</option>
                    @foreach($members as $m)
                        @if($m->user_id !== $user->id && $m->user)
                            <option value="{{ $m->user_id }}">{{ $m->user->name }} ({{ $m->role->name ?? 'Member' }})</option>
                        @endif
                    @endforeach
                </select>
            </div>
            @else
                <input type="hidden" id="ts-filter-user" value="{{ $user->id }}">
            @endif

            <button type="button" onclick="refreshDailyTimesheet()" class="tactile-btn btn-secondary" style="padding: 6px 12px; font-size: var(--nx-font-size-xs); display: inline-flex; align-items: center; gap: 4px;" title="{{ __('Refresh Data') }}">
                <span class="material-symbols-rounded" style="font-size: 14px;">refresh</span>
                <span>{{ __('Refresh') }}</span>
            </button>
        </div>
    </div>

    <!-- ========================================== -->
    <!-- ALL OFFICES LIVE PRESENCE & DAILY ATTENDANCE ROSTER -->
    <!-- ========================================== -->
    <div class="card" style="border-radius: var(--nx-radius-xl); overflow: hidden; padding: 0; margin-bottom: var(--nx-spacing-6); border: 1px solid var(--nx-border-subtle); box-shadow: var(--nx-shadow-card); background: var(--nx-bg-surface);">
        <div style="padding: var(--nx-spacing-4) var(--nx-spacing-5); border-bottom: 1px solid var(--nx-border-subtle); display: flex; justify-content: space-between; align-items: center; background: var(--nx-bg-surface); flex-wrap: wrap; gap: 10px;">
            <div style="display: flex; align-items: center; gap: var(--nx-spacing-3);">
                <div style="width: 32px; height: 32px; border-radius: var(--nx-radius-md); background: var(--nx-primary-surface); color: var(--nx-primary-500); display: flex; align-items: center; justify-content: center;">
                    <span class="material-symbols-rounded" style="font-size: 18px;">public</span>
                </div>
                <div>
                    <h3 style="font-size: var(--nx-font-size-sm); font-weight: var(--nx-font-weight-black); color: var(--nx-text-primary); margin: 0;">{{ __('Team Live Presence & Daily Attendance Across All Offices') }}</h3>
                    <p style="font-size: 11px; color: var(--nx-text-secondary); margin: 2px 0 0 0;">{{ __('Realtime online / offline status across all company branches, today total tracked time, and detailed session inspector.') }}</p>
                </div>
            </div>
            <div style="display: flex; align-items: center; gap: 10px;">
                <span class="nav-badge-pill" id="ts-online-count-pill" style="background: rgba(79, 155, 95, 0.2); color: #4F9B5F; font-weight: var(--nx-font-weight-bold); font-size: 11px; font-family: var(--nx-font-mono); display: inline-flex; align-items: center; gap: 4px;">
                    <span style="width: 6px; height: 6px; border-radius: 50%; background: #4F9B5F;"></span>
                    <span>0 {{ __('Online Now') }}</span>
                </span>
                <span class="nav-badge-pill" id="ts-total-members-pill" style="background: var(--nx-bg-surface-subtle); color: var(--nx-text-secondary); font-size: 11px; font-family: var(--nx-font-mono);">{{ count($members) }} {{ __('Total Team') }}</span>
            </div>
        </div>
        <div style="overflow-x: auto;">
            <table class="data-table" style="font-size: var(--nx-font-size-xs); width: 100%; border-collapse: collapse;">
                <thead>
                    <tr>
                        <th style="padding: 12px 16px; text-align: start; font-weight: var(--nx-font-weight-bold); color: var(--nx-text-secondary); border-bottom: 1px solid var(--nx-border-subtle);">{{ __('Employee') }}</th>
                        <th style="padding: 12px 16px; text-align: start; font-weight: var(--nx-font-weight-bold); color: var(--nx-text-secondary); border-bottom: 1px solid var(--nx-border-subtle);">{{ __('Live Status') }}</th>
                        <th style="padding: 12px 16px; text-align: start; font-weight: var(--nx-font-weight-bold); color: var(--nx-text-secondary); border-bottom: 1px solid var(--nx-border-subtle);">{{ __('Current Office / Zone') }}</th>
                        <th style="padding: 12px 16px; text-align: start; font-weight: var(--nx-font-weight-bold); color: var(--nx-text-secondary); border-bottom: 1px solid var(--nx-border-subtle);">{{ __('Today Office Time') }}</th>
                        <th style="padding: 12px 16px; text-align: start; font-weight: var(--nx-font-weight-bold); color: var(--nx-text-secondary); border-bottom: 1px solid var(--nx-border-subtle);">{{ __('Today Task Time') }}</th>
                        <th style="padding: 12px 16px; text-align: start; font-weight: var(--nx-font-weight-bold); color: var(--nx-text-secondary); border-bottom: 1px solid var(--nx-border-subtle);">{{ __('Active Task / Focus') }}</th>
                        <th style="padding: 12px 16px; text-align: start; font-weight: var(--nx-font-weight-bold); color: var(--nx-text-secondary); border-bottom: 1px solid var(--nx-border-subtle);">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody id="ts-team-roster-tbody">
                    <tr>
                        <td colspan="7" style="text-align: center; padding: 24px; color: var(--nx-text-muted);">
                            <div style="display: flex; align-items: center; justify-content: center; gap: 6px;">
                                <span class="material-symbols-rounded" style="font-size: 16px;">hourglass_top</span>
                                <span>{{ __('Loading team presence across all offices...') }}</span>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Daily Summary Metric KPI Cards (4-Grid) -->
    <div class="kpi-grid" style="margin-bottom: var(--nx-spacing-6); display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: var(--nx-spacing-4);">
        <!-- 1. Total Office Time -->
        <div class="kpi-card" style="background: var(--nx-bg-surface); border: 1px solid var(--nx-border-subtle); border-radius: var(--nx-radius-xl); padding: var(--nx-spacing-4); box-shadow: var(--nx-shadow-card);">
            <div class="kpi-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: var(--nx-spacing-2);">
                <span class="kpi-title" style="font-size: var(--nx-font-size-xs); font-weight: var(--nx-font-weight-bold); color: var(--nx-text-secondary);">{{ __('Time in Virtual Office') }}</span>
                <div class="kpi-icon-box" style="width: 32px; height: 32px; border-radius: var(--nx-radius-md); background: var(--nx-primary-surface); color: var(--nx-primary-500); display: flex; align-items: center; justify-content: center;">
                    <span class="material-symbols-rounded" style="font-size: 18px;">apartment</span>
                </div>
            </div>
            <div class="kpi-value" id="ts-kpi-office-time" style="font-family: var(--nx-font-mono); font-size: var(--nx-font-size-2xl); font-weight: var(--nx-font-weight-black); color: var(--nx-text-primary);">00:00:00</div>
            <div class="kpi-trend" style="color: var(--nx-primary-500); font-size: var(--nx-font-size-xs); margin-top: 4px; display: flex; align-items: center; gap: 4px;">
                <span class="material-symbols-rounded" style="font-size: 14px;">check_circle</span>
                <span>{{ __('Automated presence tracking') }}</span>
            </div>
        </div>

        <!-- 2. Productive Task Time -->
        <div class="kpi-card" style="background: var(--nx-bg-surface); border: 1px solid var(--nx-border-subtle); border-radius: var(--nx-radius-xl); padding: var(--nx-spacing-4); box-shadow: var(--nx-shadow-card);">
            <div class="kpi-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: var(--nx-spacing-2);">
                <span class="kpi-title" style="font-size: var(--nx-font-size-xs); font-weight: var(--nx-font-weight-bold); color: var(--nx-text-secondary);">{{ __('Productive Task Work') }}</span>
                <div class="kpi-icon-box" style="width: 32px; height: 32px; border-radius: var(--nx-radius-md); background: rgba(79, 155, 95, 0.12); color: #4F9B5F; display: flex; align-items: center; justify-content: center;">
                    <span class="material-symbols-rounded" style="font-size: 18px;">schedule</span>
                </div>
            </div>
            <div class="kpi-value" id="ts-kpi-task-time" style="font-family: var(--nx-font-mono); font-size: var(--nx-font-size-2xl); font-weight: var(--nx-font-weight-black); color: #4F9B5F;">00:00:00</div>
            <div class="kpi-trend" style="color: #4F9B5F; font-size: var(--nx-font-size-xs); margin-top: 4px; display: flex; align-items: center; gap: 4px;">
                <span class="material-symbols-rounded" style="font-size: 14px;">trending_up</span>
                <span>{{ __('Logged against active tasks') }}</span>
            </div>
        </div>

        <!-- 3. Idle / Paused Time -->
        <div class="kpi-card" style="background: var(--nx-bg-surface); border: 1px solid var(--nx-border-subtle); border-radius: var(--nx-radius-xl); padding: var(--nx-spacing-4); box-shadow: var(--nx-shadow-card);">
            <div class="kpi-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: var(--nx-spacing-2);">
                <span class="kpi-title" style="font-size: var(--nx-font-size-xs); font-weight: var(--nx-font-weight-bold); color: var(--nx-text-secondary);">{{ __('Idle / Paused Time') }}</span>
                <div class="kpi-icon-box" style="width: 32px; height: 32px; border-radius: var(--nx-radius-md); background: rgba(214, 162, 58, 0.12); color: #D6A23A; display: flex; align-items: center; justify-content: center;">
                    <span class="material-symbols-rounded" style="font-size: 18px;">pause_circle</span>
                </div>
            </div>
            <div class="kpi-value" id="ts-kpi-idle-time" style="font-family: var(--nx-font-mono); font-size: var(--nx-font-size-2xl); font-weight: var(--nx-font-weight-black); color: #D6A23A;">00:00:00</div>
            <div class="kpi-trend" style="color: var(--nx-text-muted); font-size: var(--nx-font-size-xs); margin-top: 4px; display: flex; align-items: center; gap: 4px;">
                <span class="material-symbols-rounded" style="font-size: 14px;">hourglass_empty</span>
                <span>{{ __('Inactivity stops excluded') }}</span>
            </div>
        </div>

        <!-- 4. Productivity Ratio -->
        <div class="kpi-card" style="background: var(--nx-bg-surface); border: 1px solid var(--nx-border-subtle); border-radius: var(--nx-radius-xl); padding: var(--nx-spacing-4); box-shadow: var(--nx-shadow-card);">
            <div class="kpi-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: var(--nx-spacing-2);">
                <span class="kpi-title" style="font-size: var(--nx-font-size-xs); font-weight: var(--nx-font-weight-bold); color: var(--nx-text-secondary);">{{ __('Productivity Ratio') }}</span>
                <div class="kpi-icon-box" style="width: 32px; height: 32px; border-radius: var(--nx-radius-md); background: rgba(59, 130, 246, 0.12); color: #3B82F6; display: flex; align-items: center; justify-content: center;">
                    <span class="material-symbols-rounded" style="font-size: 18px;">analytics</span>
                </div>
            </div>
            <div class="kpi-value" id="ts-kpi-ratio" style="font-family: var(--nx-font-mono); font-size: var(--nx-font-size-2xl); font-weight: var(--nx-font-weight-black); color: #3B82F6;">0%</div>
            <div class="kpi-trend" style="color: var(--nx-primary-500); font-size: var(--nx-font-size-xs); margin-top: 4px; display: flex; align-items: center; gap: 4px;">
                <span class="material-symbols-rounded" style="font-size: 14px;">bolt</span>
                <span>{{ __('Task Time ÷ Office Time') }}</span>
            </div>
        </div>
    </div>

    <!-- Live Active Timer Banner (Dynamic) -->
    <div id="ts-live-timer-banner" style="display: none; background: var(--nx-primary-surface); border: 1px solid var(--nx-border-subtle); border-radius: var(--nx-radius-lg); padding: 14px 20px; margin-bottom: var(--nx-spacing-6); box-shadow: var(--nx-shadow-sm);">
        <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px;">
            <div style="display: flex; align-items: center; gap: 12px;">
                <span class="live-indicator-dot pulse"></span>
                <div>
                    <div style="display: flex; align-items: center; gap: 8px;">
                        <span style="font-size: 11px; font-weight: 900; color: var(--nx-primary-500); text-transform: uppercase;">{{ __('Active Task Running in Office') }}</span>
                        <span class="nav-badge-pill" id="ts-banner-project-pill" style="background: rgba(79, 155, 95, 0.2); color: #4F9B5F; font-size: 10px;">Project</span>
                    </div>
                    <h4 id="ts-banner-task-title" style="font-size: 15px; font-weight: 800; color: var(--nx-text-primary); margin: 2px 0 0 0;">Task Title</h4>
                </div>
            </div>
            <div style="display: flex; align-items: center; gap: 14px;">
                <div id="ts-banner-clock" style="font-size: 20px; font-weight: 900; font-family: var(--nx-font-mono); color: var(--nx-primary-500);">00:00:00</div>
                <button type="button" onclick="stopGlobalTimer()" class="tactile-btn" style="background: #D96B5F; color: white; border: none; padding: 7px 14px; font-size: 11px; font-weight: 800; display: inline-flex; align-items: center; gap: 4px; border-radius: var(--nx-radius-md);">
                    <span class="material-symbols-rounded" style="font-size: 14px;">stop</span>
                    <span>{{ __('Stop Timer') }}</span>
                </button>
            </div>
        </div>
    </div>

    <!-- ========================================== -->
    <!-- SECTION 1: PROJECT & TASK WORK DETAILS -->
    <!-- ========================================== -->
    <div class="card" style="border-radius: var(--nx-radius-xl); overflow: hidden; padding: 0; margin-bottom: var(--nx-spacing-6); border: 1px solid var(--nx-border-subtle); box-shadow: var(--nx-shadow-card); background: var(--nx-bg-surface);">
        <div style="padding: 18px 24px; border-bottom: 1px solid var(--nx-border-subtle); display: flex; justify-content: space-between; align-items: center; background: var(--nx-bg-surface);">
            <div style="display: flex; align-items: center; gap: 10px;">
                <div style="width: 32px; height: 32px; border-radius: var(--nx-radius-md); background: rgba(79, 155, 95, 0.15); color: #4F9B5F; display: flex; align-items: center; justify-content: center;">
                    <span class="material-symbols-rounded" style="font-size: 18px;">assignment</span>
                </div>
                <div>
                    <h3 style="font-size: var(--nx-font-size-md); font-weight: var(--nx-font-weight-black); color: var(--nx-text-primary); margin: 0;">{{ __('Project & Task Work Details') }}</h3>
                    <p style="font-size: 11px; color: var(--nx-text-secondary); margin: 2px 0 0 0;">{{ __('Detailed breakdown of all work orders, milestones, and task sessions completed on this date.') }}</p>
                </div>
            </div>
            <span class="nav-badge-pill" id="ts-tasks-count-pill" style="background: rgba(79, 155, 95, 0.15); color: #4F9B5F; font-size: 11px; font-weight: 800; font-family: var(--nx-font-mono);">0 {{ __('Tasks') }}</span>
        </div>
        <div style="overflow-x: auto;">
            <table class="data-table" style="width: 100%; border-collapse: collapse;">
                <thead>
                    <tr>
                        <th style="padding: 12px 16px; text-align: start; font-size: var(--nx-font-size-xs); font-weight: var(--nx-font-weight-bold); color: var(--nx-text-secondary); border-bottom: 1px solid var(--nx-border-subtle);">{{ __('Employee') }}</th>
                        <th style="padding: 12px 16px; text-align: start; font-size: var(--nx-font-size-xs); font-weight: var(--nx-font-weight-bold); color: var(--nx-text-secondary); border-bottom: 1px solid var(--nx-border-subtle);">{{ __('Task & Code') }}</th>
                        <th style="padding: 12px 16px; text-align: start; font-size: var(--nx-font-size-xs); font-weight: var(--nx-font-weight-bold); color: var(--nx-text-secondary); border-bottom: 1px solid var(--nx-border-subtle);">{{ __('Project') }}</th>
                        <th style="padding: 12px 16px; text-align: start; font-size: var(--nx-font-size-xs); font-weight: var(--nx-font-weight-bold); color: var(--nx-text-secondary); border-bottom: 1px solid var(--nx-border-subtle);">{{ __('Time Window') }}</th>
                        <th style="padding: 12px 16px; text-align: start; font-size: var(--nx-font-size-xs); font-weight: var(--nx-font-weight-bold); color: var(--nx-text-secondary); border-bottom: 1px solid var(--nx-border-subtle);">{{ __('Duration') }}</th>
                        <th style="padding: 12px 16px; text-align: start; font-size: var(--nx-font-size-xs); font-weight: var(--nx-font-weight-bold); color: var(--nx-text-secondary); border-bottom: 1px solid var(--nx-border-subtle);">{{ __('Type / Billing') }}</th>
                        <th style="padding: 12px 16px; text-align: start; font-size: var(--nx-font-size-xs); font-weight: var(--nx-font-weight-bold); color: var(--nx-text-secondary); border-bottom: 1px solid var(--nx-border-subtle);">{{ __('Status') }}</th>
                    </tr>
                </thead>
                <tbody id="ts-tasks-tbody">
                    <tr>
                        <td colspan="7" style="text-align: center; padding: 32px; color: var(--nx-text-muted);">
                            <div style="display: flex; align-items: center; justify-content: center; gap: 6px;">
                                <span class="material-symbols-rounded" style="font-size: 16px;">hourglass_top</span>
                                <span>{{ __('Loading task time entries...') }}</span>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- ========================================== -->
    <!-- SECTION 2: VIRTUAL OFFICE ATTENDANCE LOG -->
    <!-- ========================================== -->
    <div class="card" style="border-radius: var(--nx-radius-xl); overflow: hidden; padding: 0; margin-bottom: var(--nx-spacing-6); border: 1px solid var(--nx-border-subtle); box-shadow: var(--nx-shadow-card); background: var(--nx-bg-surface);">
        <div style="padding: 18px 24px; border-bottom: 1px solid var(--nx-border-subtle); display: flex; justify-content: space-between; align-items: center; background: var(--nx-bg-surface);">
            <div style="display: flex; align-items: center; gap: 10px;">
                <div style="width: 32px; height: 32px; border-radius: var(--nx-radius-md); background: var(--nx-primary-surface); color: var(--nx-primary-500); display: flex; align-items: center; justify-content: center;">
                    <span class="material-symbols-rounded" style="font-size: 18px;">apartment</span>
                </div>
                <div>
                    <h3 style="font-size: var(--nx-font-size-md); font-weight: var(--nx-font-weight-black); color: var(--nx-text-primary); margin: 0;">{{ __('Virtual Office Attendance & Presence Log') }}</h3>
                    <p style="font-size: 11px; color: var(--nx-text-secondary); margin: 2px 0 0 0;">{{ __('Recorded 3D office presence sessions, check-ins, idle pauses, and branch room presence.') }}</p>
                </div>
            </div>
            <span class="nav-badge-pill" id="ts-attendance-count-pill" style="background: var(--nx-primary-surface); color: var(--nx-primary-500); font-size: 11px; font-weight: 800; font-family: var(--nx-font-mono);">0 {{ __('Sessions') }}</span>
        </div>
        <div style="overflow-x: auto;">
            <table class="data-table" style="width: 100%; border-collapse: collapse;">
                <thead>
                    <tr>
                        <th style="padding: 12px 16px; text-align: start; font-size: var(--nx-font-size-xs); font-weight: var(--nx-font-weight-bold); color: var(--nx-text-secondary); border-bottom: 1px solid var(--nx-border-subtle);">{{ __('Employee') }}</th>
                        <th style="padding: 12px 16px; text-align: start; font-size: var(--nx-font-size-xs); font-weight: var(--nx-font-weight-bold); color: var(--nx-text-secondary); border-bottom: 1px solid var(--nx-border-subtle);">{{ __('Branch Office / Zone') }}</th>
                        <th style="padding: 12px 16px; text-align: start; font-size: var(--nx-font-size-xs); font-weight: var(--nx-font-weight-bold); color: var(--nx-text-secondary); border-bottom: 1px solid var(--nx-border-subtle);">{{ __('Check-In Time') }}</th>
                        <th style="padding: 12px 16px; text-align: start; font-size: var(--nx-font-size-xs); font-weight: var(--nx-font-weight-bold); color: var(--nx-text-secondary); border-bottom: 1px solid var(--nx-border-subtle);">{{ __('Check-Out Time') }}</th>
                        <th style="padding: 12px 16px; text-align: start; font-size: var(--nx-font-size-xs); font-weight: var(--nx-font-weight-bold); color: var(--nx-text-secondary); border-bottom: 1px solid var(--nx-border-subtle);">{{ __('Duration') }}</th>
                        <th style="padding: 12px 16px; text-align: start; font-size: var(--nx-font-size-xs); font-weight: var(--nx-font-weight-bold); color: var(--nx-text-secondary); border-bottom: 1px solid var(--nx-border-subtle);">{{ __('Session Status') }}</th>
                    </tr>
                </thead>
                <tbody id="ts-attendance-tbody">
                    <tr>
                        <td colspan="6" style="text-align: center; padding: 32px; color: var(--nx-text-muted);">
                            <div style="display: flex; align-items: center; justify-content: center; gap: 6px;">
                                <span class="material-symbols-rounded" style="font-size: 16px;">hourglass_top</span>
                                <span>{{ __('Loading office attendance sessions...') }}</span>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- ========================================== -->
    <!-- SECTION 3: MANAGER TIMESHEET SUBMISSIONS REVIEW -->
    <!-- ========================================== -->
    <div class="card" style="border-radius: var(--nx-radius-xl); overflow: hidden; padding: 0; border: 1px solid var(--nx-border-subtle); box-shadow: var(--nx-shadow-card); background: var(--nx-bg-surface);">
        <div style="padding: 18px 24px; border-bottom: 1px solid var(--nx-border-subtle); background: var(--nx-bg-surface); display: flex; justify-content: space-between; align-items: center;">
            <div style="display: flex; align-items: center; gap: 10px;">
                <div style="width: 32px; height: 32px; border-radius: var(--nx-radius-md); background: rgba(214, 162, 58, 0.15); color: #D6A23A; display: flex; align-items: center; justify-content: center;">
                    <span class="material-symbols-rounded" style="font-size: 18px;">fact_check</span>
                </div>
                <div>
                    <h3 style="font-size: var(--nx-font-size-md); font-weight: var(--nx-font-weight-black); color: var(--nx-text-primary); margin: 0;">{{ __('Timesheet Submissions Review Queue') }}</h3>
                    <p style="font-size: 11px; color: var(--nx-text-secondary); margin: 2px 0 0 0;">{{ __('Weekly employee submissions pending manager approval and payroll audit lock.') }}</p>
                </div>
            </div>
        </div>
        <div style="overflow-x: auto;">
            <table class="data-table" style="width: 100%; border-collapse: collapse;">
                <thead>
                    <tr>
                        <th style="padding: 12px 16px; text-align: start; font-size: var(--nx-font-size-xs); font-weight: var(--nx-font-weight-bold); color: var(--nx-text-secondary); border-bottom: 1px solid var(--nx-border-subtle);">{{ __('Employee') }}</th>
                        <th style="padding: 12px 16px; text-align: start; font-size: var(--nx-font-size-xs); font-weight: var(--nx-font-weight-bold); color: var(--nx-text-secondary); border-bottom: 1px solid var(--nx-border-subtle);">{{ __('Period') }}</th>
                        <th style="padding: 12px 16px; text-align: start; font-size: var(--nx-font-size-xs); font-weight: var(--nx-font-weight-bold); color: var(--nx-text-secondary); border-bottom: 1px solid var(--nx-border-subtle);">{{ __('Total Hours') }}</th>
                        <th style="padding: 12px 16px; text-align: start; font-size: var(--nx-font-size-xs); font-weight: var(--nx-font-weight-bold); color: var(--nx-text-secondary); border-bottom: 1px solid var(--nx-border-subtle);">{{ __('Billable') }}</th>
                        <th style="padding: 12px 16px; text-align: start; font-size: var(--nx-font-size-xs); font-weight: var(--nx-font-weight-bold); color: var(--nx-text-secondary); border-bottom: 1px solid var(--nx-border-subtle);">{{ __('Status') }}</th>
                        <th style="padding: 12px 16px; text-align: start; font-size: var(--nx-font-size-xs); font-weight: var(--nx-font-weight-bold); color: var(--nx-text-secondary); border-bottom: 1px solid var(--nx-border-subtle);">{{ __('Action') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($allTimesheets as $ts)
                        <tr style="border-bottom: 1px solid var(--nx-border-subtle);">
                            <td style="padding: 14px 16px; font-weight: var(--nx-font-weight-bold); color: var(--nx-text-primary); font-size: var(--nx-font-size-sm);">{{ $ts->user->name ?? 'Member' }}</td>
                            <td style="padding: 14px 16px; font-size: var(--nx-font-size-xs); color: var(--nx-text-secondary); font-family: var(--nx-font-mono);">{{ $ts->period_start->format('M d') }} — {{ $ts->period_end->format('M d, Y') }}</td>
                            <td style="padding: 14px 16px; font-weight: var(--nx-font-weight-bold); color: var(--nx-text-primary); font-family: var(--nx-font-mono); font-size: var(--nx-font-size-xs);">{{ $ts->total_hours }}h</td>
                            <td style="padding: 14px 16px; color: var(--nx-primary-500); font-weight: var(--nx-font-weight-bold); font-family: var(--nx-font-mono); font-size: var(--nx-font-size-xs);">{{ $ts->billable_hours }}h</td>
                            <td style="padding: 14px 16px;">
                                @if($ts->status === 'approved')
                                    <span class="nav-badge-pill" style="background: rgba(79, 155, 95, 0.15); color: #4F9B5F; display: inline-flex; align-items: center; gap: 3px;">
                                        <span class="material-symbols-rounded" style="font-size: 13px;">check_circle</span>
                                        <span>{{ __('Approved') }}</span>
                                    </span>
                                @elseif($ts->status === 'submitted')
                                    <span class="nav-badge-pill" style="background: rgba(214, 162, 58, 0.15); color: #D6A23A; display: inline-flex; align-items: center; gap: 3px;">
                                        <span class="material-symbols-rounded" style="font-size: 13px;">hourglass_top</span>
                                        <span>{{ __('Pending Review') }}</span>
                                    </span>
                                @elseif($ts->status === 'rejected')
                                    <span class="nav-badge-pill" style="background: rgba(217, 107, 95, 0.15); color: #D96B5F; display: inline-flex; align-items: center; gap: 3px;">
                                        <span class="material-symbols-rounded" style="font-size: 13px;">cancel</span>
                                        <span>{{ __('Rejected') }}</span>
                                    </span>
                                @else
                                    <span class="nav-badge-pill">{{ ucfirst($ts->status) }}</span>
                                @endif
                            </td>
                            <td style="padding: 14px 16px;">
                                @if($ts->status === 'submitted' && ($membership->hasPermission('timesheets.approve') || $user->isSuperAdmin()))
                                    <div style="display: flex; gap: 6px;">
                                        <button onclick="approveTimesheet('{{ $ts->id }}')" class="tactile-btn" style="background: rgba(79, 155, 95, 0.15); color: var(--nx-primary-500); border: 1px solid rgba(79, 155, 95, 0.3); padding: 4px 10px; font-size: 11px; display: inline-flex; align-items: center; gap: 2px; border-radius: var(--nx-radius-md);">
                                            <span class="material-symbols-rounded" style="font-size: 13px;">check</span>
                                            <span>{{ __('Approve') }}</span>
                                        </button>
                                        <button onclick="openRejectModal('{{ $ts->id }}')" class="tactile-btn" style="background: rgba(217, 107, 95, 0.15); color: #D96B5F; border: 1px solid rgba(217, 107, 95, 0.3); padding: 4px 10px; font-size: 11px; display: inline-flex; align-items: center; gap: 2px; border-radius: var(--nx-radius-md);">
                                            <span class="material-symbols-rounded" style="font-size: 13px;">close</span>
                                            <span>{{ __('Reject') }}</span>
                                        </button>
                                    </div>
                                @else
                                    <span style="font-size: 11px; color: var(--nx-text-muted);">—</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" style="text-align: center; padding: 32px; color: var(--nx-text-muted);">
                                <p style="margin: 0; font-size: var(--nx-font-size-sm);">{{ __('No timesheets submitted for review.') }}</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>