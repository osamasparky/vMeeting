<div id="tab-all-tasks" class="tab-view">
    <div class="page-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: var(--nx-spacing-6); flex-wrap: wrap; gap: var(--nx-spacing-4);">
        <div>
            <h1 class="page-title" style="font-size: var(--nx-font-size-2xl); font-weight: var(--nx-font-weight-black); color: var(--nx-text-primary); margin-bottom: var(--nx-spacing-1); display: flex; align-items: center; gap: var(--nx-spacing-2);">
                <span class="material-symbols-rounded" style="font-size: 28px; color: var(--nx-primary-500);">assignment</span>
                <span>{{ __('All Tasks & Work Orders') }}</span>
            </h1>
            <p class="page-subtitle" style="font-size: var(--nx-font-size-sm); color: var(--nx-text-secondary);">{{ __('Workspace-wide task tracking, workload distribution, and Kanban workflow control.') }}</p>
        </div>
        <div style="display: flex; gap: var(--nx-spacing-3); flex-wrap: wrap; align-items: center;">
            <div style="display: flex; gap: 4px; background: var(--nx-bg-surface-subtle); padding: 4px; border-radius: var(--nx-radius-lg); border: 1px solid var(--nx-border-subtle); box-shadow: var(--nx-shadow-inset-3d);">
                <button onclick="switchAllTasksView('table')" id="alltasks-btn-table" class="tactile-btn btn-primary" style="padding: 7px 14px; font-size: var(--nx-font-size-xs); display: inline-flex; align-items: center; gap: 4px;">
                    <span class="material-symbols-rounded" style="font-size: 16px;">table_rows</span>
                    <span>{{ __('Table View') }}</span>
                </button>
                <button onclick="switchAllTasksView('kanban')" id="alltasks-btn-kanban" class="tactile-btn btn-secondary" style="padding: 7px 14px; font-size: var(--nx-font-size-xs); background: transparent; border: none; box-shadow: none; color: var(--nx-text-secondary); display: inline-flex; align-items: center; gap: 4px;">
                    <span class="material-symbols-rounded" style="font-size: 16px;">view_kanban</span>
                    <span>{{ __('Kanban Board') }}</span>
                </button>
            </div>
            <button onclick="openNewTaskModal()" class="tactile-btn btn-primary" style="padding: 10px 18px; font-size: var(--nx-font-size-sm); display: inline-flex; align-items: center; gap: var(--nx-spacing-2);">
                <span class="material-symbols-rounded" style="font-size: 18px;">add</span>
                <span>{{ __('New Task') }}</span>
            </button>
        </div>
    </div>

    <!-- Task KPIs Summary (3D Soft Neumorphic) -->
    <div class="kpi-grid" style="margin-bottom: var(--nx-spacing-6); display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: var(--nx-spacing-4);">
        <div class="kpi-card" style="background: var(--nx-bg-surface); border: 1px solid var(--nx-border-subtle); border-radius: var(--nx-radius-xl); padding: var(--nx-spacing-4); box-shadow: var(--nx-shadow-card);">
            <div class="kpi-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: var(--nx-spacing-2);">
                <span class="kpi-title" style="font-size: var(--nx-font-size-xs); font-weight: var(--nx-font-weight-bold); color: var(--nx-text-secondary);">{{ __('Total Tasks') }}</span>
                <div class="kpi-icon-box" style="width: 32px; height: 32px; border-radius: var(--nx-radius-md); background: var(--nx-primary-surface); color: var(--nx-primary-500); display: flex; align-items: center; justify-content: center;">
                    <span class="material-symbols-rounded" style="font-size: 18px;">assignment</span>
                </div>
            </div>
            <div class="kpi-value" style="font-size: var(--nx-font-size-2xl); font-weight: var(--nx-font-weight-black); color: var(--nx-text-primary); font-family: var(--nx-font-mono);">{{ $tasks->count() }}</div>
            <div class="kpi-trend" style="color: var(--nx-primary-500); font-size: var(--nx-font-size-xs); margin-top: 4px; display: flex; align-items: center; gap: 4px;">
                <span class="material-symbols-rounded" style="font-size: 14px;">folder</span>
                <span>{{ __('Across active projects') }}</span>
            </div>
        </div>

        <div class="kpi-card" style="background: var(--nx-bg-surface); border: 1px solid var(--nx-border-subtle); border-radius: var(--nx-radius-xl); padding: var(--nx-spacing-4); box-shadow: var(--nx-shadow-card);">
            <div class="kpi-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: var(--nx-spacing-2);">
                <span class="kpi-title" style="font-size: var(--nx-font-size-xs); font-weight: var(--nx-font-weight-bold); color: var(--nx-text-secondary);">{{ __('In Progress') }}</span>
                <div class="kpi-icon-box" style="width: 32px; height: 32px; border-radius: var(--nx-radius-md); background: rgba(59, 130, 246, 0.12); color: #3B82F6; display: flex; align-items: center; justify-content: center;">
                    <span class="material-symbols-rounded" style="font-size: 18px;">bolt</span>
                </div>
            </div>
            <div class="kpi-value" style="font-size: var(--nx-font-size-2xl); font-weight: var(--nx-font-weight-black); color: var(--nx-text-primary); font-family: var(--nx-font-mono);">{{ $tasks->where('status', 'in_progress')->count() }}</div>
            <div class="kpi-trend" style="color: #3B82F6; font-size: var(--nx-font-size-xs); margin-top: 4px; display: flex; align-items: center; gap: 4px;">
                <span class="material-symbols-rounded" style="font-size: 14px;">trending_up</span>
                <span>{{ __('Active work execution') }}</span>
            </div>
        </div>

        <div class="kpi-card" style="background: var(--nx-bg-surface); border: 1px solid var(--nx-border-subtle); border-radius: var(--nx-radius-xl); padding: var(--nx-spacing-4); box-shadow: var(--nx-shadow-card);">
            <div class="kpi-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: var(--nx-spacing-2);">
                <span class="kpi-title" style="font-size: var(--nx-font-size-xs); font-weight: var(--nx-font-weight-bold); color: var(--nx-text-secondary);">{{ __('Under Review') }}</span>
                <div class="kpi-icon-box" style="width: 32px; height: 32px; border-radius: var(--nx-radius-md); background: rgba(214, 162, 58, 0.12); color: #D6A23A; display: flex; align-items: center; justify-content: center;">
                    <span class="material-symbols-rounded" style="font-size: 18px;">pageview</span>
                </div>
            </div>
            <div class="kpi-value" style="font-size: var(--nx-font-size-2xl); font-weight: var(--nx-font-weight-black); color: var(--nx-text-primary); font-family: var(--nx-font-mono);">{{ $tasks->whereIn('status', ['review', 'qa'])->count() }}</div>
            <div class="kpi-trend" style="color: #D6A23A; font-size: var(--nx-font-size-xs); margin-top: 4px; display: flex; align-items: center; gap: 4px;">
                <span class="material-symbols-rounded" style="font-size: 14px;">hourglass_top</span>
                <span>{{ __('Pending QA / signoff') }}</span>
            </div>
        </div>

        <div class="kpi-card" style="background: var(--nx-bg-surface); border: 1px solid var(--nx-border-subtle); border-radius: var(--nx-radius-xl); padding: var(--nx-spacing-4); box-shadow: var(--nx-shadow-card);">
            <div class="kpi-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: var(--nx-spacing-2);">
                <span class="kpi-title" style="font-size: var(--nx-font-size-xs); font-weight: var(--nx-font-weight-bold); color: var(--nx-text-secondary);">{{ __('Completed') }}</span>
                <div class="kpi-icon-box" style="width: 32px; height: 32px; border-radius: var(--nx-radius-md); background: rgba(79, 155, 95, 0.12); color: #4F9B5F; display: flex; align-items: center; justify-content: center;">
                    <span class="material-symbols-rounded" style="font-size: 18px;">check_circle</span>
                </div>
            </div>
            <div class="kpi-value" style="font-size: var(--nx-font-size-2xl); font-weight: var(--nx-font-weight-black); color: var(--nx-text-primary); font-family: var(--nx-font-mono);">{{ $tasks->where('status', 'done')->count() }}</div>
            <div class="kpi-trend" style="color: #4F9B5F; font-size: var(--nx-font-size-xs); margin-top: 4px; display: flex; align-items: center; gap: 4px;">
                <span class="material-symbols-rounded" style="font-size: 14px;">verified</span>
                <span>{{ __('Delivered features') }}</span>
            </div>
        </div>

        <div class="kpi-card" style="background: var(--nx-bg-surface); border: 1px solid var(--nx-border-subtle); border-radius: var(--nx-radius-xl); padding: var(--nx-spacing-4); box-shadow: var(--nx-shadow-card);">
            <div class="kpi-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: var(--nx-spacing-2);">
                <span class="kpi-title" style="font-size: var(--nx-font-size-xs); font-weight: var(--nx-font-weight-bold); color: var(--nx-text-secondary);">{{ __('Estimated Effort') }}</span>
                <div class="kpi-icon-box" style="width: 32px; height: 32px; border-radius: var(--nx-radius-md); background: var(--nx-primary-surface); color: var(--nx-primary-500); display: flex; align-items: center; justify-content: center;">
                    <span class="material-symbols-rounded" style="font-size: 18px;">schedule</span>
                </div>
            </div>
            <div class="kpi-value" style="font-size: var(--nx-font-size-lg); font-weight: var(--nx-font-weight-black); color: var(--nx-text-primary); font-family: var(--nx-font-mono);">{{ $tasks->sum('estimated_hours') }}h / {{ round($projects->sum(fn($p) => $p->actualHours()), 1) }}h</div>
            <div class="kpi-trend" style="color: var(--nx-primary-500); font-size: var(--nx-font-size-xs); margin-top: 4px; display: flex; align-items: center; gap: 4px;">
                <span class="material-symbols-rounded" style="font-size: 14px;">analytics</span>
                <span>{{ __('Planned vs Tracked') }}</span>
            </div>
        </div>
    </div>

    <!-- Filter Toolbar -->
    <div class="card" style="padding: var(--nx-spacing-4) var(--nx-spacing-5); margin-bottom: var(--nx-spacing-5); border-radius: var(--nx-radius-xl); background: var(--nx-bg-surface); border: 1px solid var(--nx-border-subtle); box-shadow: var(--nx-shadow-sm);">
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: var(--nx-spacing-3); align-items: center;">
            <div>
                <label style="display: flex; align-items: center; gap: 4px; font-size: var(--nx-font-size-xs); font-weight: var(--nx-font-weight-bold); color: var(--nx-text-secondary); margin-bottom: 5px; text-transform: uppercase; letter-spacing: 0.04em;">
                    <span class="material-symbols-rounded" style="font-size: 14px; color: var(--nx-text-muted);">search</span>
                    <span>{{ __('Search Tasks') }}</span>
                </label>
                <input type="text" id="alltasks-filter-search" oninput="filterAllTasksTable()" placeholder="{{ __('Search title or #...') }}" style="width: 100%; background: var(--nx-bg-surface-subtle); border: 1px solid var(--nx-border-subtle); border-radius: var(--nx-radius-md); padding: 8px 12px; color: var(--nx-text-primary); outline: none; font-size: var(--nx-font-size-xs); font-weight: 600;">
            </div>
            <div>
                <label style="display: flex; align-items: center; gap: 4px; font-size: var(--nx-font-size-xs); font-weight: var(--nx-font-weight-bold); color: var(--nx-text-secondary); margin-bottom: 5px; text-transform: uppercase; letter-spacing: 0.04em;">
                    <span class="material-symbols-rounded" style="font-size: 14px; color: var(--nx-text-muted);">folder</span>
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
                <label style="display: flex; align-items: center; gap: 4px; font-size: var(--nx-font-size-xs); font-weight: var(--nx-font-weight-bold); color: var(--nx-text-secondary); margin-bottom: 5px; text-transform: uppercase; letter-spacing: 0.04em;">
                    <span class="material-symbols-rounded" style="font-size: 14px; color: var(--nx-text-muted);">flag</span>
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
                <label style="display: flex; align-items: center; gap: 4px; font-size: var(--nx-font-size-xs); font-weight: var(--nx-font-weight-bold); color: var(--nx-text-secondary); margin-bottom: 5px; text-transform: uppercase; letter-spacing: 0.04em;">
                    <span class="material-symbols-rounded" style="font-size: 14px; color: var(--nx-text-muted);">priority_high</span>
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
                <label style="display: flex; align-items: center; gap: 4px; font-size: var(--nx-font-size-xs); font-weight: var(--nx-font-weight-bold); color: var(--nx-text-secondary); margin-bottom: 5px; text-transform: uppercase; letter-spacing: 0.04em;">
                    <span class="material-symbols-rounded" style="font-size: 14px; color: var(--nx-text-muted);">person</span>
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
    <div id="alltasks-view-table" class="card" style="display: block; border-radius: var(--nx-radius-xl); overflow: hidden; padding: 0; background: var(--nx-bg-surface); border: 1px solid var(--nx-border-subtle); box-shadow: var(--nx-shadow-card);">
        <div style="padding: var(--nx-spacing-5) var(--nx-spacing-6); border-bottom: 1px solid var(--nx-border-subtle); display: flex; justify-content: space-between; align-items: center; background: var(--nx-bg-surface);">
            <h3 style="font-size: var(--nx-font-size-md); font-weight: var(--nx-font-weight-black); color: var(--nx-text-primary); display: flex; align-items: center; gap: var(--nx-spacing-2); margin: 0;">
                <span class="material-symbols-rounded" style="font-size: 20px; color: var(--nx-primary-500);">table_rows</span>
                <span>{{ __('All Organization Tasks') }}</span>
                <span class="nav-badge-pill" style="font-family: var(--nx-font-mono);"><span id="alltasks-filtered-count">{{ $tasks->count() }}</span></span>
            </h3>
        </div>
        <div style="overflow-x: auto;">
            <table class="data-table" style="width: 100%; border-collapse: collapse;">
                <thead>
                    <tr>
                        <th style="padding: 12px 16px; text-align: start; font-size: var(--nx-font-size-xs); font-weight: var(--nx-font-weight-bold); color: var(--nx-text-secondary); border-bottom: 1px solid var(--nx-border-subtle);">#</th>
                        <th style="padding: 12px 16px; text-align: start; font-size: var(--nx-font-size-xs); font-weight: var(--nx-font-weight-bold); color: var(--nx-text-secondary); border-bottom: 1px solid var(--nx-border-subtle);">{{ __('Task Title') }}</th>
                        <th style="padding: 12px 16px; text-align: start; font-size: var(--nx-font-size-xs); font-weight: var(--nx-font-weight-bold); color: var(--nx-text-secondary); border-bottom: 1px solid var(--nx-border-subtle);">{{ __('Project') }}</th>
                        <th style="padding: 12px 16px; text-align: start; font-size: var(--nx-font-size-xs); font-weight: var(--nx-font-weight-bold); color: var(--nx-text-secondary); border-bottom: 1px solid var(--nx-border-subtle);">{{ __('Assignee') }}</th>
                        <th style="padding: 12px 16px; text-align: start; font-size: var(--nx-font-size-xs); font-weight: var(--nx-font-weight-bold); color: var(--nx-text-secondary); border-bottom: 1px solid var(--nx-border-subtle);">{{ __('Status') }}</th>
                        <th style="padding: 12px 16px; text-align: start; font-size: var(--nx-font-size-xs); font-weight: var(--nx-font-weight-bold); color: var(--nx-text-secondary); border-bottom: 1px solid var(--nx-border-subtle);">{{ __('Priority') }}</th>
                        <th style="padding: 12px 16px; text-align: start; font-size: var(--nx-font-size-xs); font-weight: var(--nx-font-weight-bold); color: var(--nx-text-secondary); border-bottom: 1px solid var(--nx-border-subtle);">{{ __('Estimated / Actual') }}</th>
                        <th style="padding: 12px 16px; text-align: start; font-size: var(--nx-font-size-xs); font-weight: var(--nx-font-weight-bold); color: var(--nx-text-secondary); border-bottom: 1px solid var(--nx-border-subtle);">{{ __('Due Date') }}</th>
                        <th style="padding: 12px 16px; text-align: start; font-size: var(--nx-font-size-xs); font-weight: var(--nx-font-weight-bold); color: var(--nx-text-secondary); border-bottom: 1px solid var(--nx-border-subtle);">{{ __('Actions') }}</th>
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
                            style="border-bottom: 1px solid var(--nx-border-subtle); cursor: pointer;">
                            <td style="padding: 14px 16px;"><span class="nav-badge-pill" style="font-family: var(--nx-font-mono);">#{{ $t->task_number ?? 1 }}</span></td>
                            <td style="padding: 14px 16px;">
                                <div style="font-weight: var(--nx-font-weight-bold); color: var(--nx-text-primary); display: flex; align-items: center; gap: 6px; font-size: var(--nx-font-size-sm);">
                                    <span>{{ $t->title }}</span>
                                    @if($t->checklistItems && $t->checklistItems->count() > 0)
                                        <span class="nav-badge-pill" style="font-size: 9px; background: rgba(79, 155, 95, 0.15); color: #4F9B5F; font-family: var(--nx-font-mono); display: inline-flex; align-items: center; gap: 2px;">
                                            <span class="material-symbols-rounded" style="font-size: 10px;">check_box</span>
                                            <span>{{ $t->checklistItems->where('is_completed', true)->count() }}/{{ $t->checklistItems->count() }}</span>
                                        </span>
                                    @endif
                                </div>
                                @if($t->description)
                                    <div style="font-size: 11px; color: var(--nx-text-muted);">{{ Str::limit($t->description, 45) }}</div>
                                @endif
                            </td>
                            <td style="padding: 14px 16px;">
                                <span class="nav-badge-pill" style="font-weight: var(--nx-font-weight-bold); display: inline-flex; align-items: center; gap: 3px;">
                                    <span class="material-symbols-rounded" style="font-size: 13px; color: var(--nx-text-muted);">folder</span>
                                    <span>{{ $t->project->name ?? 'General' }}</span>
                                </span>
                            </td>
                            <td style="padding: 14px 16px;">
                                @if($t->assignee)
                                    <div style="display: flex; align-items: center; gap: var(--nx-spacing-2);">
                                        <div style="width: 26px; height: 26px; border-radius: var(--nx-radius-sm); background: var(--nx-accent-gradient); color: white; display: flex; align-items: center; justify-content: center; font-size: 10px; font-weight: var(--nx-font-weight-bold); font-family: var(--nx-font-mono); box-shadow: var(--nx-shadow-soft-3d);">
                                            {{ strtoupper(substr($t->assignee->name, 0, 2)) }}
                                        </div>
                                        <span style="font-weight: 600; font-size: var(--nx-font-size-xs); color: var(--nx-text-primary);">{{ $t->assignee->name }}</span>
                                    </div>
                                @else
                                    <span style="color: var(--nx-text-muted); font-size: 11px;">— {{ __('Unassigned') }} —</span>
                                @endif
                            </td>
                            <td style="padding: 14px 16px;">
                                <select onchange="event.stopPropagation(); updateTaskStatusDirect('{{ $t->id }}', this.value)" style="background: var(--nx-bg-surface-subtle); border: 1px solid var(--nx-border-subtle); color: var(--nx-text-primary); font-size: 11px; font-weight: var(--nx-font-weight-bold); border-radius: var(--nx-radius-md); padding: 4px 8px; outline: none; cursor: pointer;">
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
                            <td style="padding: 14px 16px; font-family: var(--nx-font-mono); font-weight: var(--nx-font-weight-bold); font-size: var(--nx-font-size-xs); color: var(--nx-text-primary);">
                                {{ $t->estimated_hours ?? 0 }}h / {{ $t->actualHours() }}h
                            </td>
                            <td style="padding: 14px 16px;">
                                @php
                                    $isOverdue = $t->due_date && $t->due_date->isPast() && $t->status !== 'done';
                                @endphp
                                <span style="font-size: var(--nx-font-size-xs); font-weight: var(--nx-font-weight-bold); color: {{ $isOverdue ? '#D96B5F' : 'var(--nx-text-secondary)' }}; font-family: var(--nx-font-mono);">
                                    {{ $t->due_date ? $t->due_date->format('M d, Y') : '—' }}
                                    @if($isOverdue) <span class="nav-badge-pill" style="background: rgba(217, 107, 95, 0.15); color: #D96B5F; font-size: 9px;">{{ __('Overdue') }}</span> @endif
                                </span>
                            </td>
                            <td style="padding: 14px 16px;">
                                <div style="display: flex; gap: 6px;" onclick="event.stopPropagation();">
                                    <button onclick="startTaskTimer('{{ $t->project_id }}', '{{ $t->id }}', '{{ addslashes($t->title) }}', '{{ addslashes($t->project->name ?? 'Project') }}')" class="tactile-btn" style="background: var(--nx-primary-surface); color: var(--nx-primary-500); border: 1px solid var(--nx-border-subtle); padding: 4px 10px; font-size: 11px; display: inline-flex; align-items: center; gap: 2px; border-radius: var(--nx-radius-md);">
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
                            <td colspan="9" style="text-align: center; padding: 36px; color: var(--nx-text-muted);">
                                <div style="font-size: 32px; margin-bottom: var(--nx-spacing-2); color: var(--nx-text-muted); display: flex; justify-content: center;">
                                    <span class="material-symbols-rounded" style="font-size: 40px;">assignment_late</span>
                                </div>
                                <p style="margin: 0; font-size: var(--nx-font-size-sm);">{{ __('No tasks created yet. Click "+ New Task" to create one.') }}</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- View 2: Global Drag & Drop 3D Kanban Board -->
    <div id="alltasks-view-kanban" style="display: none; margin-top: var(--nx-spacing-4);">
        <div class="kanban-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: var(--nx-spacing-4);">
            @php
                $kanbanColumns = [
                    'backlog' => ['title' => __('Backlog'), 'icon' => 'inventory_2', 'color' => 'var(--nx-text-secondary)', 'bg' => 'var(--nx-bg-surface-subtle)'],
                    'ready' => ['title' => __('Ready'), 'icon' => 'adjust', 'color' => 'var(--nx-primary-500)', 'bg' => 'var(--nx-bg-surface-subtle)'],
                    'in_progress' => ['title' => __('In Progress'), 'icon' => 'bolt', 'color' => 'var(--nx-primary-600)', 'bg' => 'var(--nx-primary-surface)'],
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
                 style="background: var(--nx-bg-surface-subtle); border-radius: var(--nx-radius-lg); padding: var(--nx-spacing-4); display: flex; flex-direction: column; border: 1px solid var(--nx-border-subtle);">
                
                <div class="kanban-col-header" style="color: {{ $colMeta['color'] }}; display: flex; justify-content: space-between; align-items: center; border-bottom: 2px solid {{ $colMeta['color'] }}; padding-bottom: 10px; margin-bottom: var(--nx-spacing-3);">
                    <span style="display: flex; align-items: center; gap: 6px; font-weight: var(--nx-font-weight-black); font-size: var(--nx-font-size-sm); color: var(--nx-text-primary);">
                        <span class="material-symbols-rounded" style="font-size: 16px; color: {{ $colMeta['color'] }};">{{ $colMeta['icon'] }}</span>
                        <span>{{ $colMeta['title'] }}</span>
                    </span>
                    <span class="nav-badge-pill" id="global-kanban-cnt-{{ $statusKey }}" style="font-family: var(--nx-font-mono); font-weight: var(--nx-font-weight-bold);">
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
                                    <span class="task-code-badge" style="font-family: var(--nx-font-mono);">
                                        {{ $t->project->code ?? 'PRJ' }}-#{{ $t->task_number ?? 1 }}
                                    </span>
                                    @if($t->checklistItems && $t->checklistItems->count() > 0)
                                        <span class="badge-pill badge-green" style="font-size: 9.5px; font-family: var(--nx-font-mono); display: inline-flex; align-items: center; gap: 2px;" title="{{ __('Checklist Progress') }}">
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
                                <div style="background: rgba(214, 162, 58, 0.15); border: 1px solid rgba(214, 162, 58, 0.35); color: #D6A23A; font-size: 10px; font-weight: var(--nx-font-weight-bold); padding: 4px 8px; border-radius: var(--nx-radius-sm); display: flex; align-items: center; justify-content: space-between;">
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
                                <div style="background: rgba(217, 107, 95, 0.15); border: 1px solid rgba(217, 107, 95, 0.35); color: #D96B5F; font-size: 10px; font-weight: var(--nx-font-weight-bold); padding: 4px 8px; border-radius: var(--nx-radius-sm); display: inline-flex; align-items: center; gap: 3px;">
                                    <span class="material-symbols-rounded" style="font-size: 12px;">warning</span>
                                    <span>{{ __('Changes Requested') }}</span>
                                </div>
                            @endif

                            <!-- Metadata: Project & Assignee & Due Date -->
                            <div class="task-card-meta">
                                <span class="task-project-name" style="display: inline-flex; align-items: center; gap: 3px;">
                                    <span class="material-symbols-rounded" style="font-size: 13px; color: var(--nx-text-muted);">folder</span>
                                    <span>{{ $t->project->name ?? 'General' }}</span>
                                </span>
                                <div style="display: flex; align-items: center; gap: 6px;">
                                    @if($t->assignee)
                                        <div class="task-assignee-chip" title="{{ $t->assignee->name }}">
                                            <div class="task-avatar-circle" style="font-family: var(--nx-font-mono);">
                                                {{ strtoupper(substr($t->assignee->name, 0, 2)) }}
                                            </div>
                                            <span style="max-width: 65px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">{{ explode(' ', $t->assignee->name)[0] }}</span>
                                        </div>
                                    @endif
                                    @if($t->due_date)
                                        <span class="task-due-date {{ $t->due_date->isPast() && $t->status !== 'done' ? 'is-overdue' : '' }}" style="display: inline-flex; align-items: center; gap: 3px; font-family: var(--nx-font-mono);">
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

                                <button type="button" onclick="event.stopPropagation(); startTaskTimer('{{ $t->project_id }}', '{{ $t->id }}', '{{ addslashes($t->title) }}', '{{ addslashes($t->project->name ?? 'Project') }}')" class="tactile-btn" style="background: var(--nx-primary-surface); color: var(--nx-primary-500); border: 1px solid var(--nx-border-subtle); padding: 3px 8px; font-size: 10.5px; border-radius: var(--nx-radius-full); white-space: nowrap; flex-shrink: 0; display: inline-flex; align-items: center; gap: 2px; font-family: var(--nx-font-mono);" title="{{ __('Start Timer') }}">
                                    <span class="material-symbols-rounded" style="font-size: 12px;">play_arrow</span>
                                    <span>{{ round($t->logged_hours ?? $t->actual_hours ?? 0, 1) }}h</span>
                                </button>
                            </div>
                        </div>
                    @empty
                        <div class="kanban-empty-hint" style="text-align: center; padding: 26px 12px; color: var(--nx-text-muted); font-size: var(--nx-font-size-xs); border: 1px dashed var(--nx-border-subtle); border-radius: var(--nx-radius-md); background: var(--nx-bg-surface);">
                            {{ __('No tasks in this stage.') }}
                        </div>
                    @endforelse
                </div>
            </div>
            @endforeach
        </div>
    </div>
</div>