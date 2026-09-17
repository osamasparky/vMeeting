<div id="tab-all-tasks" class="tab-view">
    <div class="page-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: var(--ula-space-7); flex-wrap: wrap; gap: var(--ula-space-5);">
        <div>
            <p class="page-subtitle" style="font-size: var(--ula-size-sm); color: var(--ula-text-secondary);">{{ __('Workspace-wide task tracking, workload distribution, and Kanban workflow control.') }}</p>
        </div>
        <div style="display: flex; gap: var(--ula-space-4); flex-wrap: wrap; align-items: center;">
            <div style="display: flex; gap: 4px; background: var(--ula-surface-page-alt); padding: 4px; border-radius: var(--ula-radius-lg); border: 1px solid var(--ula-border-subtle); box-shadow: var(--ula-shadow-xs);">
                <button onclick="switchAllTasksView('table')" id="alltasks-btn-table" class="tactile-btn btn-primary" style="padding: 7px 14px; font-size: var(--ula-size-xs); display: inline-flex; align-items: center; gap: 4px;">
                    <span class="material-symbols-rounded" style="font-size: 16px;">table_rows</span>
                    <span>{{ __('Table View') }}</span>
                </button>
                <button onclick="switchAllTasksView('kanban')" id="alltasks-btn-kanban" class="tactile-btn btn-secondary" style="padding: 7px 14px; font-size: var(--ula-size-xs); background: transparent; border: none; box-shadow: none; color: var(--ula-text-secondary); display: inline-flex; align-items: center; gap: 4px;">
                    <span class="material-symbols-rounded" style="font-size: 16px;">view_kanban</span>
                    <span>{{ __('Kanban Board') }}</span>
                </button>
            </div>
            <button onclick="openNewTaskModal()" class="tactile-btn btn-primary" style="padding: 10px 18px; font-size: var(--ula-size-sm); display: inline-flex; align-items: center; gap: var(--ula-space-3);">
                <span class="material-symbols-rounded" style="font-size: 18px;">add</span>
                <span>{{ __('New Task') }}</span>
            </button>
        </div>
    </div>

    <!-- Task KPIs Summary (3D Soft Neumorphic) -->
    <div class="kpi-grid" style="margin-bottom: var(--ula-space-7); display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: var(--ula-space-5);">
        <div class="kpi-card" style="background: var(--ula-surface-card); border: 1px solid var(--ula-border-subtle); border-radius: var(--ula-radius-xl); padding: var(--ula-space-5); box-shadow: var(--ula-shadow-xs);">
            <div class="kpi-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: var(--ula-space-3);">
                <span class="kpi-title" style="font-size: var(--ula-size-xs); font-weight: var(--ula-weight-bold); color: var(--ula-text-secondary);">{{ __('Total Tasks') }}</span>
                <div class="kpi-icon-box" style="width: 32px; height: 32px; border-radius: var(--ula-radius-md); background: var(--ula-surface-accent-soft); color: var(--ula-accent-default); display: flex; align-items: center; justify-content: center;">
                    <span class="material-symbols-rounded" style="font-size: 18px;">assignment</span>
                </div>
            </div>
            <div class="kpi-value" style="font-size: var(--ula-size-h3); font-weight: var(--ula-weight-bold); color: var(--ula-text-primary); font-family: var(--ula-font-mono);">{{ $tasks->count() }}</div>
            <div class="kpi-trend" style="color: var(--ula-accent-default); font-size: var(--ula-size-xs); margin-top: 4px; display: flex; align-items: center; gap: 4px;">
                <span class="material-symbols-rounded" style="font-size: 14px;">folder</span>
                <span>{{ __('Across active projects') }}</span>
            </div>
        </div>

        <div class="kpi-card" style="background: var(--ula-surface-card); border: 1px solid var(--ula-border-subtle); border-radius: var(--ula-radius-xl); padding: var(--ula-space-5); box-shadow: var(--ula-shadow-xs);">
            <div class="kpi-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: var(--ula-space-3);">
                <span class="kpi-title" style="font-size: var(--ula-size-xs); font-weight: var(--ula-weight-bold); color: var(--ula-text-secondary);">{{ __('In Progress') }}</span>
                <div class="kpi-icon-box" style="width: 32px; height: 32px; border-radius: var(--ula-radius-md); background: rgba(59, 130, 246, 0.12); color: #3B82F6; display: flex; align-items: center; justify-content: center;">
                    <span class="material-symbols-rounded" style="font-size: 18px;">bolt</span>
                </div>
            </div>
            <div class="kpi-value" style="font-size: var(--ula-size-h3); font-weight: var(--ula-weight-bold); color: var(--ula-text-primary); font-family: var(--ula-font-mono);">{{ $tasks->where('status', 'in_progress')->count() }}</div>
            <div class="kpi-trend" style="color: #3B82F6; font-size: var(--ula-size-xs); margin-top: 4px; display: flex; align-items: center; gap: 4px;">
                <span class="material-symbols-rounded" style="font-size: 14px;">trending_up</span>
                <span>{{ __('Active work execution') }}</span>
            </div>
        </div>

        <div class="kpi-card" style="background: var(--ula-surface-card); border: 1px solid var(--ula-border-subtle); border-radius: var(--ula-radius-xl); padding: var(--ula-space-5); box-shadow: var(--ula-shadow-xs);">
            <div class="kpi-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: var(--ula-space-3);">
                <span class="kpi-title" style="font-size: var(--ula-size-xs); font-weight: var(--ula-weight-bold); color: var(--ula-text-secondary);">{{ __('Under Review') }}</span>
                <div class="kpi-icon-box" style="width: 32px; height: 32px; border-radius: var(--ula-radius-md); background: rgba(214, 162, 58, 0.12); color: #D6A23A; display: flex; align-items: center; justify-content: center;">
                    <span class="material-symbols-rounded" style="font-size: 18px;">pageview</span>
                </div>
            </div>
            <div class="kpi-value" style="font-size: var(--ula-size-h3); font-weight: var(--ula-weight-bold); color: var(--ula-text-primary); font-family: var(--ula-font-mono);">{{ $tasks->whereIn('status', ['review', 'qa'])->count() }}</div>
            <div class="kpi-trend" style="color: #D6A23A; font-size: var(--ula-size-xs); margin-top: 4px; display: flex; align-items: center; gap: 4px;">
                <span class="material-symbols-rounded" style="font-size: 14px;">hourglass_top</span>
                <span>{{ __('Pending QA / signoff') }}</span>
            </div>
        </div>

        <div class="kpi-card" style="background: var(--ula-surface-card); border: 1px solid var(--ula-border-subtle); border-radius: var(--ula-radius-xl); padding: var(--ula-space-5); box-shadow: var(--ula-shadow-xs);">
            <div class="kpi-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: var(--ula-space-3);">
                <span class="kpi-title" style="font-size: var(--ula-size-xs); font-weight: var(--ula-weight-bold); color: var(--ula-text-secondary);">{{ __('Completed') }}</span>
                <div class="kpi-icon-box" style="width: 32px; height: 32px; border-radius: var(--ula-radius-md); background: rgba(79, 155, 95, 0.12); color: #4F9B5F; display: flex; align-items: center; justify-content: center;">
                    <span class="material-symbols-rounded" style="font-size: 18px;">check_circle</span>
                </div>
            </div>
            <div class="kpi-value" style="font-size: var(--ula-size-h3); font-weight: var(--ula-weight-bold); color: var(--ula-text-primary); font-family: var(--ula-font-mono);">{{ $tasks->where('status', 'done')->count() }}</div>
            <div class="kpi-trend" style="color: #4F9B5F; font-size: var(--ula-size-xs); margin-top: 4px; display: flex; align-items: center; gap: 4px;">
                <span class="material-symbols-rounded" style="font-size: 14px;">verified</span>
                <span>{{ __('Delivered features') }}</span>
            </div>
        </div>

        <div class="kpi-card" style="background: var(--ula-surface-card); border: 1px solid var(--ula-border-subtle); border-radius: var(--ula-radius-xl); padding: var(--ula-space-5); box-shadow: var(--ula-shadow-xs);">
            <div class="kpi-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: var(--ula-space-3);">
                <span class="kpi-title" style="font-size: var(--ula-size-xs); font-weight: var(--ula-weight-bold); color: var(--ula-text-secondary);">{{ __('Estimated Effort') }}</span>
                <div class="kpi-icon-box" style="width: 32px; height: 32px; border-radius: var(--ula-radius-md); background: var(--ula-surface-accent-soft); color: var(--ula-accent-default); display: flex; align-items: center; justify-content: center;">
                    <span class="material-symbols-rounded" style="font-size: 18px;">schedule</span>
                </div>
            </div>
            <div class="kpi-value" style="font-size: var(--ula-size-body-lg); font-weight: var(--ula-weight-bold); color: var(--ula-text-primary); font-family: var(--ula-font-mono);">{{ $tasks->sum('estimated_hours') }}h / {{ round($projects->sum(fn($p) => $p->actualHours()), 1) }}h</div>
            <div class="kpi-trend" style="color: var(--ula-accent-default); font-size: var(--ula-size-xs); margin-top: 4px; display: flex; align-items: center; gap: 4px;">
                <span class="material-symbols-rounded" style="font-size: 14px;">analytics</span>
                <span>{{ __('Planned vs Tracked') }}</span>
            </div>
        </div>
    </div>

    <!-- Filter Toolbar -->
    <div class="card" style="padding: var(--ula-space-5) var(--ula-space-6); margin-bottom: var(--ula-space-6); border-radius: var(--ula-radius-xl); background: var(--ula-surface-card); border: 1px solid var(--ula-border-subtle); box-shadow: var(--ula-shadow-sm);">
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: var(--ula-space-4); align-items: center;">
            <div>
                <label style="display: flex; align-items: center; gap: 4px; font-size: var(--ula-size-xs); font-weight: var(--ula-weight-bold); color: var(--ula-text-secondary); margin-bottom: 5px; text-transform: uppercase; letter-spacing: 0.04em;">
                    <span class="material-symbols-rounded" style="font-size: 14px; color: var(--ula-text-muted);">search</span>
                    <span>{{ __('Search Tasks') }}</span>
                </label>
                <input type="text" id="alltasks-filter-search" oninput="filterAllTasksTable()" placeholder="{{ __('Search title or #...') }}" style="width: 100%; background: var(--ula-surface-page-alt); border: 1px solid var(--ula-border-subtle); border-radius: var(--ula-radius-md); padding: 8px 12px; color: var(--ula-text-primary); outline: none; font-size: var(--ula-size-xs); font-weight: 600;">
            </div>
            <div>
                <label style="display: flex; align-items: center; gap: 4px; font-size: var(--ula-size-xs); font-weight: var(--ula-weight-bold); color: var(--ula-text-secondary); margin-bottom: 5px; text-transform: uppercase; letter-spacing: 0.04em;">
                    <span class="material-symbols-rounded" style="font-size: 14px; color: var(--ula-text-muted);">folder</span>
                    <span>{{ __('Project') }}</span>
                </label>
                <select id="alltasks-filter-project" onchange="filterAllTasksTable()" class="custom-select-control" style="width: 100%;">
                    <option value="">{{ __('All Projects') }}</option>
                    @foreach($projects as $p)
                        <option value="{{ $p->id }}">{{ $p->name }} ({{ $p->code }})</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label style="display: flex; align-items: center; gap: 4px; font-size: var(--ula-size-xs); font-weight: var(--ula-weight-bold); color: var(--ula-text-secondary); margin-bottom: 5px; text-transform: uppercase; letter-spacing: 0.04em;">
                    <span class="material-symbols-rounded" style="font-size: 14px; color: var(--ula-text-muted);">flag</span>
                    <span>{{ __('Status') }}</span>
                </label>
                <select id="alltasks-filter-status" onchange="filterAllTasksTable()" class="custom-select-control" style="width: 100%;">
                    <option value="">{{ __('All Statuses') }}</option>
                    <option value="backlog">{{ __('Backlog') }}</option>
                    <option value="ready">{{ __('Ready') }}</option>
                    <option value="in_progress">{{ __('In Progress') }}</option>
                    <option value="review">{{ __('Review / QA') }}</option>
                    <option value="done">{{ __('Done') }}</option>
                </select>
            </div>
            <div>
                <label style="display: flex; align-items: center; gap: 4px; font-size: var(--ula-size-xs); font-weight: var(--ula-weight-bold); color: var(--ula-text-secondary); margin-bottom: 5px; text-transform: uppercase; letter-spacing: 0.04em;">
                    <span class="material-symbols-rounded" style="font-size: 14px; color: var(--ula-text-muted);">priority_high</span>
                    <span>{{ __('Priority') }}</span>
                </label>
                <select id="alltasks-filter-priority" onchange="filterAllTasksTable()" class="custom-select-control" style="width: 100%;">
                    <option value="">{{ __('All Priorities') }}</option>
                    <option value="urgent">{{ __('Urgent') }}</option>
                    <option value="high">{{ __('High') }}</option>
                    <option value="medium">{{ __('Medium') }}</option>
                    <option value="low">{{ __('Low') }}</option>
                </select>
            </div>
            <div>
                <label style="display: flex; align-items: center; gap: 4px; font-size: var(--ula-size-xs); font-weight: var(--ula-weight-bold); color: var(--ula-text-secondary); margin-bottom: 5px; text-transform: uppercase; letter-spacing: 0.04em;">
                    <span class="material-symbols-rounded" style="font-size: 14px; color: var(--ula-text-muted);">person</span>
                    <span>{{ __('Assignee') }}</span>
                </label>
                <select id="alltasks-filter-assignee" onchange="filterAllTasksTable()" class="custom-select-control" style="width: 100%;">
                    <option value="">{{ __('All Members') }}</option>
                    @foreach($members as $m)
                        <option value="{{ $m->user_id }}">{{ $m->user->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>

    <!-- View 1: Tasks Table / List -->
    <div id="alltasks-view-table" class="card" style="display: block; border-radius: var(--ula-radius-xl); overflow: hidden; padding: 0; background: var(--ula-surface-card); border: 1px solid var(--ula-border-subtle); box-shadow: var(--ula-shadow-xs);">
        <div style="padding: var(--ula-space-6) var(--ula-space-7); border-bottom: 1px solid var(--ula-border-subtle); display: flex; justify-content: space-between; align-items: center; background: var(--ula-surface-card);">
            <h3 style="font-size: var(--ula-size-body); font-weight: var(--ula-weight-bold); color: var(--ula-text-primary); display: flex; align-items: center; gap: var(--ula-space-3); margin: 0;">
                <span class="material-symbols-rounded" style="font-size: 20px; color: var(--ula-accent-default);">table_rows</span>
                <span>{{ __('All Organization Tasks') }}</span>
                <span class="nav-badge-pill" style="font-family: var(--ula-font-mono);"><span id="alltasks-filtered-count">{{ $tasks->count() }}</span></span>
            </h3>
        </div>
        <div style="overflow-x: auto;">
            <table class="data-table" style="width: 100%; border-collapse: collapse;">
                <thead>
                    <tr>
                        <th style="padding: 12px 16px; text-align: start; font-size: var(--ula-size-xs); font-weight: var(--ula-weight-bold); color: var(--ula-text-secondary); border-bottom: 1px solid var(--ula-border-subtle);">#</th>
                        <th style="padding: 12px 16px; text-align: start; font-size: var(--ula-size-xs); font-weight: var(--ula-weight-bold); color: var(--ula-text-secondary); border-bottom: 1px solid var(--ula-border-subtle);">{{ __('Task Title') }}</th>
                        <th style="padding: 12px 16px; text-align: start; font-size: var(--ula-size-xs); font-weight: var(--ula-weight-bold); color: var(--ula-text-secondary); border-bottom: 1px solid var(--ula-border-subtle);">{{ __('Project') }}</th>
                        <th style="padding: 12px 16px; text-align: start; font-size: var(--ula-size-xs); font-weight: var(--ula-weight-bold); color: var(--ula-text-secondary); border-bottom: 1px solid var(--ula-border-subtle);">{{ __('Assignee') }}</th>
                        <th style="padding: 12px 16px; text-align: start; font-size: var(--ula-size-xs); font-weight: var(--ula-weight-bold); color: var(--ula-text-secondary); border-bottom: 1px solid var(--ula-border-subtle);">{{ __('Status') }}</th>
                        <th style="padding: 12px 16px; text-align: start; font-size: var(--ula-size-xs); font-weight: var(--ula-weight-bold); color: var(--ula-text-secondary); border-bottom: 1px solid var(--ula-border-subtle);">{{ __('Priority') }}</th>
                        <th style="padding: 12px 16px; text-align: start; font-size: var(--ula-size-xs); font-weight: var(--ula-weight-bold); color: var(--ula-text-secondary); border-bottom: 1px solid var(--ula-border-subtle);">{{ __('Estimated / Actual') }}</th>
                        <th style="padding: 12px 16px; text-align: start; font-size: var(--ula-size-xs); font-weight: var(--ula-weight-bold); color: var(--ula-text-secondary); border-bottom: 1px solid var(--ula-border-subtle);">{{ __('Due Date') }}</th>
                        <th style="padding: 12px 16px; text-align: start; font-size: var(--ula-size-xs); font-weight: var(--ula-weight-bold); color: var(--ula-text-secondary); border-bottom: 1px solid var(--ula-border-subtle);">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody id="alltasks-table-body">
                    @forelse($tasks as $t)
                        <tr class="alltask-row" 
                            data-id="{{ $t->id }}"
                            data-title="{{ strtolower($t->title) }}"
                            data-project-id="{{ $t->project_id }}"
                            data-status="{{ $t->status }}"
                            data-priority="{{ $t->priority }}"
                            data-assignee-id="{{ $t->assignee_id }}"
                            onclick="openTaskDetails('{{ $t->id }}')"
                            oncontextmenu="event.preventDefault(); event.stopPropagation(); openTaskContextMenu(event, '{{ $t->id }}', '{{ $t->project_id }}', '{{ addslashes($t->title) }}')"
                            style="border-bottom: 1px solid var(--ula-border-subtle); cursor: pointer;">
                            <td style="padding: 14px 16px;"><span class="nav-badge-pill" style="font-family: var(--ula-font-mono);">#{{ $t->task_number ?? 1 }}</span></td>
                            <td style="padding: 14px 16px;">
                                <div style="font-weight: var(--ula-weight-bold); color: var(--ula-text-primary); display: flex; align-items: center; gap: 6px; font-size: var(--ula-size-sm);">
                                    <span>{{ $t->title }}</span>
                                    @if($t->checklistItems && $t->checklistItems->count() > 0)
                                        <span class="nav-badge-pill" style="font-size: 9px; background: rgba(79, 155, 95, 0.15); color: #4F9B5F; font-family: var(--ula-font-mono); display: inline-flex; align-items: center; gap: 2px;">
                                            <span class="material-symbols-rounded" style="font-size: 10px;">check_box</span>
                                            <span>{{ $t->checklistItems->where('is_completed', true)->count() }}/{{ $t->checklistItems->count() }}</span>
                                        </span>
                                    @endif
                                </div>
                                @if($t->description)
                                    <div style="font-size: 11px; color: var(--ula-text-muted);">{{ Str::limit($t->description, 45) }}</div>
                                @endif
                            </td>
                            <td style="padding: 14px 16px;">
                                <span class="nav-badge-pill" style="font-weight: var(--ula-weight-bold); display: inline-flex; align-items: center; gap: 3px;">
                                    <span class="material-symbols-rounded" style="font-size: 13px; color: var(--ula-text-muted);">folder</span>
                                    <span>{{ $t->project->name ?? 'General' }}</span>
                                </span>
                            </td>
                            <td style="padding: 14px 16px;">
                                @if($t->assignee)
                                    <div style="display: flex; align-items: center; gap: var(--ula-space-3);">
                                        <div style="width: 26px; height: 26px; border-radius: var(--ula-radius-sm); background: var(--ula-gradient-accent); color: white; display: flex; align-items: center; justify-content: center; font-size: 10px; font-weight: var(--ula-weight-bold); font-family: var(--ula-font-mono); box-shadow: var(--ula-shadow-xs);">
                                            {{ strtoupper(substr($t->assignee->name, 0, 2)) }}
                                        </div>
                                        <span style="font-weight: 600; font-size: var(--ula-size-xs); color: var(--ula-text-primary);">{{ $t->assignee->name }}</span>
                                    </div>
                                @else
                                    <span style="color: var(--ula-text-muted); font-size: 11px;">— {{ __('Unassigned') }} —</span>
                                @endif
                            </td>
                            <td style="padding: 14px 16px;">
                                <select onchange="event.stopPropagation(); updateTaskStatusDirect('{{ $t->id }}', this.value)" style="background: var(--ula-surface-page-alt); border: 1px solid var(--ula-border-subtle); color: var(--ula-text-primary); font-size: 11px; font-weight: var(--ula-weight-bold); border-radius: var(--ula-radius-md); padding: 4px 8px; outline: none; cursor: pointer;">
                                    <option value="backlog" {{ $t->status === 'backlog' ? 'selected' : '' }}>{{ __('Backlog') }}</option>
                                    <option value="ready" {{ $t->status === 'ready' ? 'selected' : '' }}>{{ __('Ready') }}</option>
                                    <option value="in_progress" {{ $t->status === 'in_progress' ? 'selected' : '' }}>{{ __('In Progress') }}</option>
                                    <option value="review" {{ $t->status === 'review' || $t->status === 'qa' ? 'selected' : '' }}>{{ __('Review') }}</option>
                                    <option value="done" {{ $t->status === 'done' ? 'selected' : '' }}>{{ __('Done') }}</option>
                                </select>
                            </td>
                            <td style="padding: 14px 16px;">
                                @if($t->priority === 'urgent')
                                    <span class="nav-badge-pill" style="background: rgba(217, 107, 95, 0.15); color: #D96B5F; border-color: rgba(217, 107, 95, 0.3); display: inline-flex; align-items: center; gap: 2px;">
                                        <span class="material-symbols-rounded" style="font-size: 12px;">local_fire_department</span>
                                        <span>{{ __('Urgent') }}</span>
                                    </span>
                                @elseif($t->priority === 'high')
                                    <span class="nav-badge-pill" style="background: rgba(214, 162, 58, 0.15); color: #D6A23A; border-color: rgba(214, 162, 58, 0.3); display: inline-flex; align-items: center; gap: 2px;">
                                        <span class="material-symbols-rounded" style="font-size: 12px;">bolt</span>
                                        <span>{{ __('High') }}</span>
                                    </span>
                                @else
                                    <span class="nav-badge-pill">{{ ucfirst($t->priority) }}</span>
                                @endif
                            </td>
                            <td style="padding: 14px 16px; font-family: var(--ula-font-mono); font-weight: var(--ula-weight-bold); font-size: var(--ula-size-xs); color: var(--ula-text-primary);">
                                {{ $t->estimated_hours ?? 0 }}h / {{ $t->actualHours() }}h
                            </td>
                            <td style="padding: 14px 16px;">
                                @php
                                    $isOverdue = $t->due_date && $t->due_date->isPast() && $t->status !== 'done';
                                @endphp
                                <span style="font-size: var(--ula-size-xs); font-weight: var(--ula-weight-bold); color: {{ $isOverdue ? '#D96B5F' : 'var(--ula-text-secondary)' }}; font-family: var(--ula-font-mono);">
                                    {{ $t->due_date ? $t->due_date->format('M d, Y') : '—' }}
                                    @if($isOverdue) <span class="nav-badge-pill" style="background: rgba(217, 107, 95, 0.15); color: #D96B5F; font-size: 9px;">{{ __('Overdue') }}</span> @endif
                                </span>
                            </td>
                            <td style="padding: 14px 16px;">
                                <div style="display: flex; gap: 6px;" onclick="event.stopPropagation();">
                                    <button onclick="startTaskTimer('{{ $t->project_id }}', '{{ $t->id }}', '{{ addslashes($t->title) }}', '{{ addslashes($t->project->name ?? 'Project') }}')" class="tactile-btn" style="background: var(--ula-surface-accent-soft); color: var(--ula-accent-default); border: 1px solid var(--ula-border-subtle); padding: 4px 10px; font-size: 11px; display: inline-flex; align-items: center; gap: 2px; border-radius: var(--ula-radius-md);">
                                        <span class="material-symbols-rounded" style="font-size: 13px;">play_arrow</span>
                                        <span>{{ __('Timer') }}</span>
                                    </button>
                                    <button onclick="openTaskDetails('{{ $t->id }}')" class="tactile-btn btn-secondary" style="padding: 4px 10px; font-size: 11px; display: inline-flex; align-items: center; gap: 2px;">
                                        <span class="material-symbols-rounded" style="font-size: 13px;">visibility</span>
                                        <span>{{ __('Inspect') }}</span>
                                    </button>
                                    <button onclick="openTaskContextMenu(event, '{{ $t->id }}', '{{ $t->project_id }}', '{{ addslashes($t->title) }}')" class="tactile-btn btn-secondary" style="padding: 4px 8px; font-size: 11px; display: inline-flex; align-items: center; justify-content: center;" title="{{ __('More Actions') }}">
                                        <span class="material-symbols-rounded" style="font-size: 14px;">more_horiz</span>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" style="text-align: center; padding: 36px; color: var(--ula-text-muted);">
                                <div style="font-size: 32px; margin-bottom: var(--ula-space-3); color: var(--ula-text-muted); display: flex; justify-content: center;">
                                    <span class="material-symbols-rounded" style="font-size: 40px;">assignment_late</span>
                                </div>
                                <p style="margin: 0; font-size: var(--ula-size-sm);">{{ __('No tasks created yet. Click "+ New Task" to create one.') }}</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- View 2: Global Drag & Drop 3D Kanban Board -->
    <div id="alltasks-view-kanban" style="display: none; margin-top: var(--ula-space-5);">
        <div class="kanban-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: var(--ula-space-5);">
            @php
                $kanbanColumns = [
                    'backlog' => ['title' => __('Backlog'), 'icon' => 'inventory_2', 'color' => 'var(--ula-text-secondary)', 'bg' => 'var(--ula-surface-page-alt)'],
                    'ready' => ['title' => __('Ready'), 'icon' => 'adjust', 'color' => 'var(--ula-accent-default)', 'bg' => 'var(--ula-surface-page-alt)'],
                    'in_progress' => ['title' => __('In Progress'), 'icon' => 'bolt', 'color' => 'var(--ula-accent-press)', 'bg' => 'var(--ula-surface-accent-soft)'],
                    'review' => ['title' => __('Review / QA'), 'icon' => 'pageview', 'color' => '#D6A23A', 'bg' => 'rgba(214, 162, 58, 0.08)'],
                    'done' => ['title' => __('Done'), 'icon' => 'check_circle', 'color' => '#4F9B5F', 'bg' => 'rgba(79, 155, 95, 0.12)'],
                ];
            @endphp

            @foreach($kanbanColumns as $statusKey => $colMeta)
            <div class="kanban-column" 
                 id="global-kanban-zone-{{ $statusKey }}"
                 ondragover="handleGlobalDragOver(event)" 
                 ondragleave="handleGlobalDragLeave(event)" 
                 ondrop="handleGlobalDrop(event, '{{ $statusKey }}')"
                 style="background: var(--ula-surface-page-alt); border-radius: var(--ula-radius-lg); padding: var(--ula-space-5); display: flex; flex-direction: column; border: 1px solid var(--ula-border-subtle);">
                
                <div class="kanban-col-header" style="color: {{ $colMeta['color'] }}; display: flex; justify-content: space-between; align-items: center; border-bottom: 2px solid {{ $colMeta['color'] }}; padding-bottom: 10px; margin-bottom: var(--ula-space-4);">
                    <span style="display: flex; align-items: center; gap: 6px; font-weight: var(--ula-weight-bold); font-size: var(--ula-size-sm); color: var(--ula-text-primary);">
                        <span class="material-symbols-rounded" style="font-size: 16px; color: {{ $colMeta['color'] }};">{{ $colMeta['icon'] }}</span>
                        <span>{{ $colMeta['title'] }}</span>
                    </span>
                    <span class="nav-badge-pill" id="global-kanban-cnt-{{ $statusKey }}" style="font-family: var(--ula-font-mono); font-weight: var(--ula-weight-bold);">
                        {{ $statusKey === 'review' ? $tasks->whereIn('status', ['review', 'qa'])->count() : $tasks->where('status', $statusKey)->count() }}
                    </span>
                </div>

                <div class="kanban-cards-container" id="global-kanban-col-{{ $statusKey }}" style="display: flex; flex-direction: column; gap: 10px; flex: 1; min-height: 120px;">
                    @php
                        $colTasks = ($statusKey === 'review') ? $tasks->whereIn('status', ['review', 'qa']) : $tasks->where('status', $statusKey);
                    @endphp

                    @forelse($colTasks as $t)
                        <div class="kanban-task-card global-kanban-card" 
                             id="global-kanban-card-{{ $t->id }}"
                             draggable="true" 
                             ondragstart="handleGlobalDragStart(event, '{{ $t->id }}')" 
                             ondragend="handleGlobalDragEnd(event)"
                             oncontextmenu="event.preventDefault(); event.stopPropagation(); openTaskContextMenu(event, '{{ $t->id }}', '{{ $t->project_id }}', '{{ addslashes($t->title) }}')"
                             data-id="{{ $t->id }}"
                             data-title="{{ strtolower($t->title) }}"
                             data-project-id="{{ $t->project_id }}"
                             data-status="{{ $t->status }}"
                             data-priority="{{ $t->priority }}"
                             data-assignee-id="{{ $t->assignee_id }}"
                             onclick="openTaskDetails('{{ $t->id }}')">
                            
                            <!-- Header: Project Code & Action Buttons -->
                            <div class="task-card-header">
                                <div class="task-card-tags">
                                    <span class="task-code-badge" style="font-family: var(--ula-font-mono); direction: ltr; display: inline-block; unicode-bidi: isolate;">
                                        {{ $t->project->code ?? 'PRJ' }}-#{{ $t->task_number ?? 1 }}
                                    </span>
                                    @if($t->checklistItems && $t->checklistItems->count() > 0)
                                        <span class="badge-pill badge-green" style="font-size: 9.5px; font-family: var(--ula-font-mono); display: inline-flex; align-items: center; gap: 2px;" title="{{ __('Checklist Progress') }}">
                                            <span class="material-symbols-rounded" style="font-size: 11px;">check_box</span>
                                            <span>{{ $t->checklistItems->where('is_completed', true)->count() }}/{{ $t->checklistItems->count() }}</span>
                                        </span>
                                    @endif
                                </div>

                                <div class="task-card-actions">
                                    @if($t->priority === 'urgent')
                                        <span class="badge-pill badge-danger" style="display: inline-flex; align-items: center; gap: 2px;">
                                            <span class="material-symbols-rounded" style="font-size: 12px;">local_fire_department</span>
                                            <span>{{ __('Urgent') }}</span>
                                        </span>
                                    @elseif($t->priority === 'high')
                                        <span class="badge-pill badge-gold" style="display: inline-flex; align-items: center; gap: 2px;">
                                            <span class="material-symbols-rounded" style="font-size: 12px;">bolt</span>
                                            <span>{{ __('High') }}</span>
                                        </span>
                                    @endif

                                    <button type="button" onclick="event.stopPropagation(); openTaskContextMenu(event, '{{ $t->id }}', '{{ $t->project_id }}', '{{ addslashes($t->title) }}')" class="task-dots-btn" title="{{ __('More actions') }}" style="display: inline-flex; align-items: center; justify-content: center;">
                                        <span class="material-symbols-rounded" style="font-size: 16px;">more_horiz</span>
                                    </button>
                                </div>
                            </div>

                            <!-- Body: Title -->
                            <h4 class="task-card-title">
                                {{ $t->title }}
                            </h4>

                            @if($t->approval_status === 'pending_approval')
                                <div style="background: rgba(214, 162, 58, 0.15); border: 1px solid rgba(214, 162, 58, 0.35); color: #D6A23A; font-size: 10px; font-weight: var(--ula-weight-bold); padding: 4px 8px; border-radius: var(--ula-radius-sm); display: flex; align-items: center; justify-content: space-between;">
                                    <span style="display: inline-flex; align-items: center; gap: 3px;">
                                        <span class="material-symbols-rounded" style="font-size: 12px;">hourglass_top</span>
                                        <span>{{ __('Pending PM Approval') }}</span>
                                    </span>
                                    <button type="button" onclick="event.stopPropagation(); quickApproveTask('{{ $t->id }}')" class="tactile-btn" style="background: #4F9B5F; color: white; padding: 2px 6px; font-size: 9px; border: none; border-radius: 4px; display: inline-flex; align-items: center; gap: 2px;">
                                        <span class="material-symbols-rounded" style="font-size: 11px;">check</span>
                                        <span>{{ __('Approve') }}</span>
                                    </button>
                                </div>
                            @elseif($t->approval_status === 'rejected')
                                <div style="background: rgba(217, 107, 95, 0.15); border: 1px solid rgba(217, 107, 95, 0.35); color: #D96B5F; font-size: 10px; font-weight: var(--ula-weight-bold); padding: 4px 8px; border-radius: var(--ula-radius-sm); display: inline-flex; align-items: center; gap: 3px;">
                                    <span class="material-symbols-rounded" style="font-size: 12px;">warning</span>
                                    <span>{{ __('Changes Requested') }}</span>
                                </div>
                            @endif

                            <!-- Metadata: Project & Assignee & Due Date -->
                            <div class="task-card-meta">
                                <span class="task-project-name" style="display: inline-flex; align-items: center; gap: 3px;">
                                    <span class="material-symbols-rounded" style="font-size: 13px; color: var(--ula-text-muted);">folder</span>
                                    <span>{{ $t->project->name ?? 'General' }}</span>
                                </span>
                                <div style="display: flex; align-items: center; gap: 6px;">
                                    @if($t->assignee)
                                        <div class="task-assignee-chip" title="{{ $t->assignee->name }}">
                                            <div class="task-avatar-circle" style="font-family: var(--ula-font-mono);">
                                                {{ strtoupper(substr($t->assignee->name, 0, 2)) }}
                                            </div>
                                            <span style="max-width: 65px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">{{ explode(' ', $t->assignee->name)[0] }}</span>
                                        </div>
                                    @endif
                                    @if($t->due_date)
                                        <span class="task-due-date {{ $t->due_date->isPast() && $t->status !== 'done' ? 'is-overdue' : '' }}" style="display: inline-flex; align-items: center; gap: 3px; font-family: var(--ula-font-mono);">
                                            <span class="material-symbols-rounded" style="font-size: 12px;">calendar_today</span>
                                            <span>{{ $t->due_date->format('M d') }}</span>
                                        </span>
                                    @endif
                                </div>
                            </div>

                            <!-- Footer: Direct Status Dropdown & Timer -->
                            <div class="task-card-footer">
                                <div style="display: flex; align-items: center; gap: 6px; flex: 1; min-width: 0;">
                                    <select onclick="event.stopPropagation()" onchange="updateTaskStatusDirect('{{ $t->id }}', this.value)" class="card-status-select" style="max-width: 100%;">
                                        <option value="backlog" {{ $t->status === 'backlog' ? 'selected' : '' }}>{{ __('Backlog') }}</option>
                                        <option value="ready" {{ $t->status === 'ready' ? 'selected' : '' }}>{{ __('Ready') }}</option>
                                        <option value="in_progress" {{ $t->status === 'in_progress' ? 'selected' : '' }}>{{ __('In Progress') }}</option>
                                        <option value="review" {{ $t->status === 'review' || $t->status === 'qa' ? 'selected' : '' }}>{{ __('Review') }}</option>
                                        <option value="done" {{ $t->status === 'done' ? 'selected' : '' }}>{{ __('Done') }}</option>
                                    </select>
                                </div>

                                <button type="button" onclick="event.stopPropagation(); startTaskTimer('{{ $t->project_id }}', '{{ $t->id }}', '{{ addslashes($t->title) }}', '{{ addslashes($t->project->name ?? 'Project') }}')" class="tactile-btn" style="background: var(--ula-surface-accent-soft); color: var(--ula-accent-default); border: 1px solid var(--ula-border-subtle); padding: 3px 8px; font-size: 10.5px; border-radius: var(--ula-radius-pill); white-space: nowrap; flex-shrink: 0; display: inline-flex; align-items: center; gap: 2px; font-family: var(--ula-font-mono);" title="{{ __('Start Timer') }}">
                                    <span class="material-symbols-rounded" style="font-size: 12px;">play_arrow</span>
                                    <span>{{ round($t->logged_hours ?? $t->actual_hours ?? 0, 1) }}h</span>
                                </button>
                            </div>
                        </div>
                    @empty
                        <div class="kanban-empty-hint" style="text-align: center; padding: 26px 12px; color: var(--ula-text-muted); font-size: var(--ula-size-xs); border: 1px dashed var(--ula-border-subtle); border-radius: var(--ula-radius-md); background: var(--ula-surface-card);">
                            {{ __('No tasks in this stage.') }}
                        </div>
                    @endforelse
                </div>
            </div>
            @endforeach
        </div>
    </div>
</div>