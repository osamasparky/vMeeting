<div id="tab-my-tasks" class="tab-view">
            <div class="page-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px;">
                <div>
                    <h1 class="page-title" style="font-size: 22px; font-weight: 900; color: var(--text-primary); margin-bottom: 4px;">✅ {{ __('My Tasks & Action Items') }}</h1>
                    <p class="page-subtitle" style="font-size: 13px; color: var(--text-secondary);">{{ __('Track and log time against your personal assigned tasks.') }}</p>
                </div>
                <div style="display: flex; gap: 10px;">
                    <button onclick="openNewTaskModal()" class="tactile-btn btn-primary" style="padding: 10px 18px; font-size: 13px;">
                        <span>+</span> {{ __('New Task') }}
                    </button>
                </div>
            </div>

            <!-- Task Status Columns Grid (5-Column Kanban matching All Tasks) -->
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 16px;">
                @php
                    $myKanbanCols = [
                        'backlog' => ['title' => '📌 ' . __('Backlog'), 'color' => 'var(--text-secondary)', 'border' => 'var(--border-color)'],
                        'ready' => ['title' => '🎯 ' . __('Ready'), 'color' => 'var(--brand-sage)', 'border' => 'var(--brand-sage)'],
                        'in_progress' => ['title' => '⚡ ' . __('In Progress'), 'color' => 'var(--brand-forest)', 'border' => 'var(--brand-forest)'],
                        'review' => ['title' => '🔍 ' . __('Review / QA'), 'color' => 'var(--status-warning)', 'border' => '#D6A23A'],
                        'done' => ['title' => '🎉 ' . __('Done'), 'color' => 'var(--brand-forest)', 'border' => '#4F9B5F'],
                    ];
                @endphp

                @foreach($myKanbanCols as $colKey => $colMeta)
                    @php
                        $colTasks = ($colKey === 'review') ? $myTasks->whereIn('status', ['review', 'qa']) : $myTasks->where('status', $colKey);
                    @endphp
                    <div class="card mytasks-kanban-column" id="mytasks-kanban-zone-{{ $colKey }}" style="border-radius: var(--radius-lg); padding: 14px; background: var(--bg-surface-subtle); display: flex; flex-direction: column;">
                        <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 2px solid {{ $colMeta['border'] }}; padding-bottom: 10px; margin-bottom: 12px;">
                            <h3 style="font-size: 14px; font-weight: 900; color: var(--text-primary); display: flex; align-items: center; gap: 6px;">
                                <span>{{ $colMeta['title'] }}</span>
                            </h3>
                            <span class="nav-badge-pill" id="mytasks-kanban-cnt-{{ $colKey }}" style="font-weight: 800;">{{ $colTasks->count() }}</span>
                        </div>

                        <div class="kanban-cards-container" id="mytasks-kanban-col-{{ $colKey }}" data-status="{{ $colKey }}" style="display: flex; flex-direction: column; gap: 10px; flex: 1; min-height: 120px;">
                            @forelse($colTasks as $t)
                                @php
                                    $canEditThisTask = $user->can('update', $t);
                                    $isManager = ($user->isSuperAdmin() || $membership->role?->slug === 'company_admin' || ($t->project && $t->project->manager_id === $user->id));
                                @endphp
                                <div class="kanban-task-card" 
                                     id="mytasks-card-{{ $t->id }}" 
                                     data-id="{{ $t->id }}" 
                                     data-status="{{ $t->status }}" 
                                     oncontextmenu="event.preventDefault(); event.stopPropagation(); openTaskContextMenu(event, '{{ $t->id }}', '{{ $t->project_id }}', '{{ addslashes($t->title) }}')"
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
                                            @if($isManager)
                                                <button type="button" onclick="event.stopPropagation(); quickApproveTask('{{ $t->id }}')" class="tactile-btn" style="background: #4F9B5F; color: white; padding: 2px 6px; font-size: 9px; border: none; border-radius: 4px;">✓ {{ __('Approve') }}</button>
                                            @endif
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

                                    <!-- Footer: Direct Status Dropdown & Timer -->
                                    <div class="task-card-footer">
                                        <div style="display: flex; align-items: center; gap: 6px;">
                                            <select onclick="event.stopPropagation()" onchange="updateTaskStatusDirect('{{ $t->id }}', this.value)" class="card-status-select" {{ $canEditThisTask ? '' : 'disabled' }}>
                                                <option value="backlog" {{ $t->status === 'backlog' ? 'selected' : '' }}>📌 {{ __('Backlog') }}</option>
                                                <option value="ready" {{ $t->status === 'ready' ? 'selected' : '' }}>🎯 {{ __('Ready') }}</option>
                                                <option value="in_progress" {{ $t->status === 'in_progress' ? 'selected' : '' }}>⚡ {{ __('In Progress') }}</option>
                                                <option value="review" {{ $t->status === 'review' || $t->status === 'qa' ? 'selected' : '' }}>🔍 {{ __('Review') }}</option>
                                                <option value="done" {{ $t->status === 'done' ? 'selected' : '' }}>🎉 {{ __('Done') }}</option>
                                            </select>
                                        </div>

                                        <button type="button" onclick="event.stopPropagation(); startTaskTimer('{{ $t->project_id }}', '{{ $t->id }}', '{{ addslashes($t->title) }}', '{{ addslashes($t->project->name ?? 'Project') }}')" class="tactile-btn" style="background: rgba(79, 155, 95, 0.15); color: var(--brand-forest); border: 1px solid rgba(79, 155, 95, 0.3); padding: 3px 8px; font-size: 10.5px; border-radius: var(--radius-full);" title="{{ __('Start Timer') }}">
                                            ▶ {{ round($t->logged_hours ?? $t->actual_hours ?? 0, 1) }}h
                                        </button>
                                    </div>
                                </div>
                            @empty
                                <div class="mytasks-empty-hint" id="mytasks-empty-{{ $colKey }}" style="text-align: center; padding: 18px 8px; color: var(--text-muted); font-size: 11px; border: 1px dashed var(--border-color); border-radius: var(--radius-md);">
                                    {{ __('No tasks in this stage.') }}
                                </div>
                            @endforelse
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- 10. TIMESHEETS & TIME TRACKING TAB -->
        