<div id="tab-all-tasks" class="tab-view">
            <div class="page-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; flex-wrap: wrap; gap: 14px;">
                <div>
                    <h1 class="page-title" style="font-size: 22px; font-weight: 900; color: var(--text-primary); margin-bottom: 4px;">📑 {{ __('All Tasks & Work Orders') }}</h1>
                    <p class="page-subtitle" style="font-size: 13px; color: var(--text-secondary);">{{ __('Workspace-wide task tracking, workload distribution, and Kanban workflow control.') }}</p>
                </div>
                <div style="display: flex; gap: 10px; flex-wrap: wrap; align-items: center;">
                    <div style="display: flex; gap: 4px; background: var(--bg-surface-subtle); padding: 4px; border-radius: var(--radius-md); border: 1px solid var(--border-color); box-shadow: var(--shadow-inset-3d);">
                        <button onclick="switchAllTasksView('table')" id="alltasks-btn-table" class="tactile-btn btn-primary" style="padding: 7px 14px; font-size: 12px;">
                            📋 {{ __('Table View') }}
                        </button>
                        <button onclick="switchAllTasksView('kanban')" id="alltasks-btn-kanban" class="tactile-btn btn-secondary" style="padding: 7px 14px; font-size: 12px; background: transparent; border: none; box-shadow: none; color: var(--text-secondary);">
                            📌 {{ __('Kanban Board') }}
                        </button>
                    </div>
                    <button onclick="openNewTaskModal()" class="tactile-btn btn-primary" style="padding: 10px 18px; font-size: 13px;">
                        <span>+</span> {{ __('New Task') }}
                    </button>
                </div>
            </div>

            <!-- Task KPIs Summary (3D Soft Neumorphic) -->
            <div class="kpi-grid" style="margin-bottom: 24px;">
                <div class="kpi-card">
                    <div class="kpi-header">
                        <span class="kpi-title">{{ __('Total Tasks') }}</span>
                        <div class="kpi-icon-box">📑</div>
                    </div>
                    <div class="kpi-value">{{ $tasks->count() }}</div>
                    <div class="kpi-trend" style="color: var(--brand-forest);">
                        <span>📁</span> {{ __('Across active projects') }}
                    </div>
                </div>
                <div class="kpi-card">
                    <div class="kpi-header">
                        <span class="kpi-title">{{ __('In Progress') }}</span>
                        <div class="kpi-icon-box">⚡</div>
                    </div>
                    <div class="kpi-value">{{ $tasks->where('status', 'in_progress')->count() }}</div>
                    <div class="kpi-trend" style="color: var(--brand-forest);">
                        <span>🏃</span> {{ __('Active work execution') }}
                    </div>
                </div>
                <div class="kpi-card">
                    <div class="kpi-header">
                        <span class="kpi-title">{{ __('Under Review') }}</span>
                        <div class="kpi-icon-box">🔍</div>
                    </div>
                    <div class="kpi-value">{{ $tasks->whereIn('status', ['review', 'qa'])->count() }}</div>
                    <div class="kpi-trend" style="color: var(--status-warning);">
                        <span>⏳</span> {{ __('Pending QA / signoff') }}
                    </div>
                </div>
                <div class="kpi-card">
                    <div class="kpi-header">
                        <span class="kpi-title">{{ __('Completed') }}</span>
                        <div class="kpi-icon-box">🎉</div>
                    </div>
                    <div class="kpi-value">{{ $tasks->where('status', 'done')->count() }}</div>
                    <div class="kpi-trend" style="color: var(--brand-forest);">
                        <span>✅</span> {{ __('Delivered features') }}
                    </div>
                </div>
                <div class="kpi-card">
                    <div class="kpi-header">
                        <span class="kpi-title">{{ __('Estimated Effort') }}</span>
                        <div class="kpi-icon-box">⏱️</div>
                    </div>
                    <div class="kpi-value" style="font-size: 20px;">{{ $tasks->sum('estimated_hours') }}h / {{ round($projects->sum(fn($p) => $p->actualHours()), 1) }}h</div>
                    <div class="kpi-trend" style="color: var(--brand-forest);">
                        <span>📊</span> {{ __('Planned vs Tracked') }}
                    </div>
                </div>
            </div>

            <!-- Filter Toolbar -->
            <div class="card" style="padding: 16px 20px; margin-bottom: 20px; border-radius: var(--radius-lg);">
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 12px; align-items: center;">
                    <div>
                        <label style="display: block; font-size: 11px; font-weight: 800; color: var(--text-secondary); margin-bottom: 5px; text-transform: uppercase;">🔍 {{ __('Search Tasks') }}</label>
                        <input type="text" id="alltasks-filter-search" oninput="filterAllTasksTable()" placeholder="{{ __('Search title or #...') }}" style="width: 100%; background: var(--bg-surface-subtle); border: 1px solid var(--border-color); border-radius: var(--radius-md); padding: 8px 12px; color: var(--text-primary); outline: none; font-size: 12px; font-weight: 600;">
                    </div>
                    <div>
                        <label style="display: block; font-size: 11px; font-weight: 800; color: var(--text-secondary); margin-bottom: 5px; text-transform: uppercase;">📁 {{ __('Project') }}</label>
                        <select id="alltasks-filter-project" onchange="filterAllTasksTable()" class="custom-select-control" style="width: 100%;">
                            <option value="">{{ __('All Projects') }}</option>
                            @foreach($projects as $p)
                                <option value="{{ $p->id }}">{{ $p->name }} ({{ $p->code }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label style="display: block; font-size: 11px; font-weight: 800; color: var(--text-secondary); margin-bottom: 5px; text-transform: uppercase;">⚡ {{ __('Status') }}</label>
                        <select id="alltasks-filter-status" onchange="filterAllTasksTable()" class="custom-select-control" style="width: 100%;">
                            <option value="">{{ __('All Statuses') }}</option>
                            <option value="backlog">📌 {{ __('Backlog') }}</option>
                            <option value="ready">🎯 {{ __('Ready') }}</option>
                            <option value="in_progress">⚡ {{ __('In Progress') }}</option>
                            <option value="review">🔍 {{ __('Review / QA') }}</option>
                            <option value="done">🎉 {{ __('Done') }}</option>
                        </select>
                    </div>
                    <div>
                        <label style="display: block; font-size: 11px; font-weight: 800; color: var(--text-secondary); margin-bottom: 5px; text-transform: uppercase;">⚡ {{ __('Priority') }}</label>
                        <select id="alltasks-filter-priority" onchange="filterAllTasksTable()" class="custom-select-control" style="width: 100%;">
                            <option value="">{{ __('All Priorities') }}</option>
                            <option value="urgent">🔥 {{ __('Urgent') }}</option>
                            <option value="high">⚡ {{ __('High') }}</option>
                            <option value="medium">{{ __('Medium') }}</option>
                            <option value="low">{{ __('Low') }}</option>
                        </select>
                    </div>
                    <div>
                        <label style="display: block; font-size: 11px; font-weight: 800; color: var(--text-secondary); margin-bottom: 5px; text-transform: uppercase;">👤 {{ __('Assignee') }}</label>
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
            <div id="alltasks-view-table" class="card" style="display: block; border-radius: var(--radius-lg); overflow: hidden; padding: 0;">
                <div style="padding: 20px 24px; border-bottom: 1px solid var(--border-color); display: flex; justify-content: space-between; align-items: center; background: var(--bg-surface);">
                    <h3 style="font-size: 16px; font-weight: 900; color: var(--text-primary);">📋 {{ __('All Organization Tasks') }} (<span id="alltasks-filtered-count">{{ $tasks->count() }}</span>)</h3>
                </div>
                <div style="overflow-x: auto;">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>{{ __('Task Title') }}</th>
                                <th>{{ __('Project') }}</th>
                                <th>{{ __('Assignee') }}</th>
                                <th>{{ __('Status') }}</th>
                                <th>{{ __('Priority') }}</th>
                                <th>{{ __('Estimated / Actual') }}</th>
                                <th>{{ __('Due Date') }}</th>
                                <th>{{ __('Actions') }}</th>
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
                                    <td><span class="nav-badge-pill" style="font-family: monospace;">#{{ $t->task_number ?? 1 }}</span></td>
                                    <td>
                                        <div style="font-weight: 800; color: var(--text-primary); display: flex; align-items: center; gap: 6px;">
                                            <span>{{ $t->title }}</span>
                                            @if($t->checklistItems && $t->checklistItems->count() > 0)
                                                <span class="nav-badge-pill" style="font-size: 9px; background: rgba(79, 155, 95, 0.15); color: #4F9B5F;">⊞ {{ $t->checklistItems->where('is_completed', true)->count() }}/{{ $t->checklistItems->count() }}</span>
                                            @endif
                                        </div>
                                        @if($t->description)
                                            <div style="font-size: 11px; color: var(--text-muted);">{{ Str::limit($t->description, 45) }}</div>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="nav-badge-pill" style="font-weight: 700;">📁 {{ $t->project->name ?? 'General' }}</span>
                                    </td>
                                    <td>
                                        @if($t->assignee)
                                            <div style="display: flex; align-items: center; gap: 8px;">
                                                <div style="width: 24px; height: 24px; border-radius: 8px; background: var(--accent-gradient); color: white; display: flex; align-items: center; justify-content: center; font-size: 9px; font-weight: 800;">
                                                    {{ strtoupper(substr($t->assignee->name, 0, 2)) }}
                                                </div>
                                                <span style="font-weight: 600; font-size: 13px;">{{ $t->assignee->name }}</span>
                                            </div>
                                        @else
                                            <span style="color: var(--text-muted); font-size: 11px;">— {{ __('Unassigned') }} —</span>
                                        @endif
                                    </td>
                                    <td>
                                        <select onchange="event.stopPropagation(); updateTaskStatusDirect('{{ $t->id }}', this.value)" style="background: var(--bg-surface-subtle); border: 1px solid var(--border-color); color: var(--text-primary); font-size: 11px; font-weight: 700; border-radius: 8px; padding: 4px 8px; outline: none; cursor: pointer;">
                                            <option value="backlog" {{ $t->status === 'backlog' ? 'selected' : '' }}>📌 {{ __('Backlog') }}</option>
                                            <option value="ready" {{ $t->status === 'ready' ? 'selected' : '' }}>🎯 {{ __('Ready') }}</option>
                                            <option value="in_progress" {{ $t->status === 'in_progress' ? 'selected' : '' }}>⚡ {{ __('In Progress') }}</option>
                                            <option value="review" {{ $t->status === 'review' || $t->status === 'qa' ? 'selected' : '' }}>🔍 {{ __('Review') }}</option>
                                            <option value="done" {{ $t->status === 'done' ? 'selected' : '' }}>🎉 {{ __('Done') }}</option>
                                        </select>
                                    </td>
                                    <td>
                                        @if($t->priority === 'urgent')
                                            <span class="nav-badge-pill" style="background: rgba(217, 107, 95, 0.15); color: #D96B5F; border-color: rgba(217, 107, 95, 0.3);">🔥 {{ __('Urgent') }}</span>
                                        @elseif($t->priority === 'high')
                                            <span class="nav-badge-pill" style="background: rgba(214, 162, 58, 0.15); color: #D6A23A; border-color: rgba(214, 162, 58, 0.3);">⚡ {{ __('High') }}</span>
                                        @else
                                            <span class="nav-badge-pill">{{ ucfirst($t->priority) }}</span>
                                        @endif
                                    </td>
                                    <td style="font-family: monospace; font-weight: 700;">
                                        {{ $t->estimated_hours ?? 0 }}h / {{ $t->actualHours() }}h
                                    </td>
                                    <td>
                                        @php
                                            $isOverdue = $t->due_date && $t->due_date->isPast() && $t->status !== 'done';
                                        @endphp
                                        <span style="font-size: 12px; font-weight: 700; color: {{ $isOverdue ? '#D96B5F' : 'var(--text-secondary)' }};">
                                            {{ $t->due_date ? $t->due_date->format('M d, Y') : '—' }}
                                            @if($isOverdue) <span class="nav-badge-pill" style="background: rgba(217, 107, 95, 0.15); color: #D96B5F; font-size: 9px;">{{ __('Overdue') }}</span> @endif
                                        </span>
                                    </td>
                                    <td>
                                        <div style="display: flex; gap: 6px;" onclick="event.stopPropagation();">
                                            <button onclick="startTaskTimer('{{ $t->project_id }}', '{{ $t->id }}', '{{ addslashes($t->title) }}', '{{ addslashes($t->project->name ?? 'Project') }}')" class="tactile-btn" style="background: rgba(79, 155, 95, 0.15); color: var(--brand-forest); border: 1px solid rgba(79, 155, 95, 0.3); padding: 4px 10px; font-size: 11px;">
                                                ▶ {{ __('Timer') }}
                                            </button>
                                            <button onclick="openTaskDetails('{{ $t->id }}')" class="tactile-btn btn-secondary" style="padding: 4px 10px; font-size: 11px;">
                                                🔍 {{ __('Inspect') }}
                                            </button>
                                            <button onclick="openTaskContextMenu(event, '{{ $t->id }}', '{{ $t->project_id }}', '{{ addslashes($t->title) }}')" class="tactile-btn btn-secondary" style="padding: 4px 8px; font-size: 11px;" title="{{ __('More Actions') }}">
                                                •••
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" style="text-align: center; padding: 36px; color: var(--text-muted);">
                                        📑 {{ __('No tasks created yet. Click "+ New Task" to create one.') }}
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- View 2: Global Drag & Drop 3D Kanban Board -->
            <div id="alltasks-view-kanban" style="display: none; margin-top: 14px;">
                <div class="kanban-grid">
                    @php
                        $kanbanColumns = [
                            'backlog' => ['title' => '📌 ' . __('Backlog'), 'color' => 'var(--text-secondary)', 'bg' => 'var(--bg-surface-subtle)'],
                            'ready' => ['title' => '🎯 ' . __('Ready'), 'color' => 'var(--brand-sage)', 'bg' => 'var(--bg-surface-subtle)'],
                            'in_progress' => ['title' => '⚡ ' . __('In Progress'), 'color' => 'var(--brand-forest)', 'bg' => 'rgba(79, 155, 95, 0.08)'],
                            'review' => ['title' => '🔍 ' . __('Review / QA'), 'color' => 'var(--status-warning)', 'bg' => 'rgba(214, 162, 58, 0.08)'],
                            'done' => ['title' => '🎉 ' . __('Done'), 'color' => 'var(--status-success)', 'bg' => 'rgba(79, 155, 95, 0.12)'],
                        ];
                    @endphp

                    @foreach($kanbanColumns as $statusKey => $colMeta)
                    <div class="kanban-column" 
                         id="global-kanban-zone-{{ $statusKey }}"
                         ondragover="handleGlobalDragOver(event)" 
                         ondragleave="handleGlobalDragLeave(event)" 
                         ondrop="handleGlobalDrop(event, '{{ $statusKey }}')">
                        
                        <div class="kanban-col-header" style="color: {{ $colMeta['color'] }};">
                            <span style="display: flex; align-items: center; gap: 6px;">
                                <span>{{ $colMeta['title'] }}</span>
                            </span>
                            <span class="nav-badge-pill" id="global-kanban-cnt-{{ $statusKey }}">
                                {{ $statusKey === 'review' ? $tasks->whereIn('status', ['review', 'qa'])->count() : $tasks->where('status', $statusKey)->count() }}
                            </span>
                        </div>

                        <div class="kanban-cards-container" id="global-kanban-col-{{ $statusKey }}">
                            @php
                                $colTasks = ($statusKey === 'review') ? $tasks->whereIn('status', ['review', 'qa']) : $tasks->where('status', $statusKey);
                            @endphp

                            @forelse($colTasks as $t)
                                <div class="global-kanban-card kanban-card" 
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
                                            <span class="task-code-badge">
                                                {{ $t->project->code ?? 'PRJ' }}-#{{ $t->task_number ?? 1 }}
                                            </span>
                                            @if($t->checklistItems && $t->checklistItems->count() > 0)
                                                <span class="badge-pill badge-green" style="font-size: 9.5px;" title="{{ __('Checklist Progress') }}">
                                                    ⊞ {{ $t->checklistItems->where('is_completed', true)->count() }}/{{ $t->checklistItems->count() }}
                                                </span>
                                            @endif
                                        </div>

                                        <div class="task-card-actions">
                                            @if($t->priority === 'urgent')
                                                <span class="badge-pill badge-danger">🔥 {{ __('Urgent') }}</span>
                                            @elseif($t->priority === 'high')
                                                <span class="badge-pill badge-gold">⚡ {{ __('High') }}</span>
                                            @endif

                                            <button type="button" onclick="event.stopPropagation(); openTaskContextMenu(event, '{{ $t->id }}', '{{ $t->project_id }}', '{{ addslashes($t->title) }}')" class="task-dots-btn" title="{{ __('More actions') }}">
                                                •••
                                            </button>
                                        </div>
                                    </div>

                                    <!-- Body: Title -->
                                    <h4 class="task-card-title">
                                        {{ $t->title }}
                                    </h4>

                                    @if($t->approval_status === 'pending_approval')
                                        <div style="background: rgba(214, 162, 58, 0.15); border: 1px solid rgba(214, 162, 58, 0.35); color: #D6A23A; font-size: 10px; font-weight: 800; padding: 4px 8px; border-radius: 8px; display: flex; align-items: center; justify-content: space-between;">
                                            <span>⏳ {{ __('Pending PM Approval') }}</span>
                                            <button type="button" onclick="event.stopPropagation(); quickApproveTask('{{ $t->id }}')" class="tactile-btn" style="background: #4F9B5F; color: white; padding: 2px 6px; font-size: 9px; border: none; border-radius: 4px;">✓ {{ __('Approve') }}</button>
                                        </div>
                                    @elseif($t->approval_status === 'rejected')
                                        <div style="background: rgba(217, 107, 95, 0.15); border: 1px solid rgba(217, 107, 95, 0.35); color: #D96B5F; font-size: 10px; font-weight: 800; padding: 4px 8px; border-radius: 8px;">
                                            <span>⚠️ {{ __('Changes Requested') }}</span>
                                        </div>
                                    @endif

                                    <!-- Metadata: Project & Due Date -->
                                    <div class="task-card-meta">
                                        <span class="task-project-name">📁 {{ $t->project->name ?? 'General' }}</span>
                                        @if($t->due_date)
                                            <span class="task-due-date {{ $t->due_date->isPast() && $t->status !== 'done' ? 'is-overdue' : '' }}">
                                                📅 {{ $t->due_date->format('M d') }}
                                            </span>
                                        @endif
                                    </div>

                                    <!-- Footer: Assignee & Direct Status Dropdown & Timer -->
                                    <div class="task-card-footer">
                                        <div class="task-assignee-chip">
                                            @if($t->assignee)
                                                <div class="task-avatar-circle" title="{{ $t->assignee->name }}">
                                                    {{ strtoupper(substr($t->assignee->name, 0, 2)) }}
                                                </div>
                                                <span style="max-width: 80px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">{{ explode(' ', $t->assignee->name)[0] }}</span>
                                            @else
                                                <span style="color: var(--text-muted); font-size: 10.5px;">👤 {{ __('Unassigned') }}</span>
                                            @endif
                                        </div>

                                        <div style="display: flex; align-items: center; gap: 6px;">
                                            <select onclick="event.stopPropagation()" onchange="updateTaskStatusDirect('{{ $t->id }}', this.value)" class="card-status-select">
                                                <option value="backlog" {{ $t->status === 'backlog' ? 'selected' : '' }}>📌 {{ __('Backlog') }}</option>
                                                <option value="ready" {{ $t->status === 'ready' ? 'selected' : '' }}>🎯 {{ __('Ready') }}</option>
                                                <option value="in_progress" {{ $t->status === 'in_progress' ? 'selected' : '' }}>⚡ {{ __('In Progress') }}</option>
                                                <option value="review" {{ $t->status === 'review' || $t->status === 'qa' ? 'selected' : '' }}>🔍 {{ __('Review') }}</option>
                                                <option value="done" {{ $t->status === 'done' ? 'selected' : '' }}>🎉 {{ __('Done') }}</option>
                                            </select>

                                            <button type="button" onclick="event.stopPropagation(); startTaskTimer('{{ $t->project_id }}', '{{ $t->id }}', '{{ addslashes($t->title) }}', '{{ addslashes($t->project->name ?? 'Project') }}')" class="tactile-btn" style="background: rgba(79, 155, 95, 0.15); color: var(--brand-forest); border: 1px solid rgba(79, 155, 95, 0.3); padding: 3px 8px; font-size: 10.5px; border-radius: var(--radius-full);" title="{{ __('Start Timer') }}">
                                                ▶ {{ round($t->logged_hours ?? $t->actual_hours ?? 0, 1) }}h
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <div class="kanban-empty-hint" style="text-align: center; padding: 26px 12px; color: var(--text-muted); font-size: 11px; border: 1px dashed var(--border-color); border-radius: var(--radius-md); background: rgba(255, 255, 255, 0.4);">
                                    {{ __('No tasks in this stage.') }}
                                </div>
                            @endforelse
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
        

        <!-- 10. MY TASKS TAB -->
        