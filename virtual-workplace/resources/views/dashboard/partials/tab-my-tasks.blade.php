<div id="tab-my-tasks" class="tab-view">
    <div class="page-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: var(--ula-space-7); flex-wrap: wrap; gap: var(--ula-space-5);">
        <div>
            <h1 class="page-title" style="font-size: var(--ula-size-h3); font-weight: var(--ula-weight-bold); color: var(--ula-text-primary); margin-bottom: var(--ula-space-2); display: flex; align-items: center; gap: var(--ula-space-3);">
                <span class="material-symbols-rounded" style="font-size: 28px; color: var(--ula-accent-default);">task_alt</span>
                <span>{{ __('My Tasks & Action Items') }}</span>
            </h1>
            <p class="page-subtitle" style="font-size: var(--ula-size-sm); color: var(--ula-text-secondary);">{{ __('Track and log time against your personal assigned tasks.') }}</p>
        </div>
        <div style="display: flex; gap: var(--ula-space-4);">
            <button onclick="openNewTaskModal()" class="tactile-btn btn-primary" style="padding: 10px 18px; font-size: var(--ula-size-sm); display: inline-flex; align-items: center; gap: var(--ula-space-3);">
                <span class="material-symbols-rounded" style="font-size: 18px;">add</span>
                <span>{{ __('New Task') }}</span>
            </button>
        </div>
    </div>

    <!-- Task Status Columns Grid (5-Column Kanban matching All Tasks) -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: var(--ula-space-5);">
        @php
            $myKanbanCols = [
                'backlog' => ['title' => __('Backlog'), 'icon' => 'inventory_2', 'color' => 'var(--ula-text-secondary)', 'border' => 'var(--ula-border-subtle)'],
                'ready' => ['title' => __('Ready'), 'icon' => 'adjust', 'color' => 'var(--ula-accent-default)', 'border' => 'var(--ula-accent-default)'],
                'in_progress' => ['title' => __('In Progress'), 'icon' => 'bolt', 'color' => 'var(--ula-accent-press)', 'border' => 'var(--ula-accent-press)'],
                'review' => ['title' => __('Review / QA'), 'icon' => 'pageview', 'color' => 'var(--ula-gold-400)', 'border' => 'var(--ula-gold-400)'],
                'done' => ['title' => __('Done'), 'icon' => 'check_circle', 'color' => 'var(--ula-status-success)', 'border' => 'var(--ula-status-success)'],
            ];
        @endphp

        @foreach($myKanbanCols as $colKey => $colMeta)
            @php
                $colTasks = ($colKey === 'review') ? $myTasks->whereIn('status', ['review', 'qa']) : $myTasks->where('status', $colKey);
            @endphp
            <div class="card mytasks-kanban-column" id="mytasks-kanban-zone-{{ $colKey }}" style="border-radius: var(--ula-radius-lg); padding: var(--ula-space-5); background: var(--ula-surface-page-alt); display: flex; flex-direction: column; border: 1px solid var(--ula-border-subtle);">
                <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 2px solid {{ $colMeta['border'] }}; padding-bottom: 10px; margin-bottom: var(--ula-space-4);">
                    <h3 style="font-size: var(--ula-size-sm); font-weight: var(--ula-weight-bold); color: var(--ula-text-primary); display: flex; align-items: center; gap: 6px; margin: 0;">
                        <span class="material-symbols-rounded" style="font-size: 16px; color: {{ $colMeta['color'] }};">{{ $colMeta['icon'] }}</span>
                        <span>{{ $colMeta['title'] }}</span>
                    </h3>
                    <span class="nav-badge-pill" id="mytasks-kanban-cnt-{{ $colKey }}" style="font-weight: var(--ula-weight-bold); font-family: var(--ula-font-mono); direction: ltr; unicode-bidi: isolate;">{{ $colTasks->count() }}</span>
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
                                    <span class="task-code-badge" style="font-family: var(--ula-font-mono); direction: ltr; display: inline-block; unicode-bidi: isolate;">
                                        {{ $t->project->code ?? 'PRJ' }}-#{{ $t->task_number ?? 1 }}
                                    </span>
                                    @if($t->checklistItems && $t->checklistItems->count() > 0)
                                        <span class="badge-pill badge-green" style="font-size: 9.5px; font-family: var(--ula-font-mono); direction: ltr; unicode-bidi: isolate; display: inline-flex; align-items: center; gap: 2px;" title="{{ __('Checklist Progress') }}">
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
                                <div style="background: rgba(214, 162, 58, 0.15); border: 1px solid rgba(214, 162, 58, 0.35); color: var(--ula-gold-400); font-size: 10px; font-weight: var(--ula-weight-bold); padding: 4px 8px; border-radius: var(--ula-radius-sm); display: flex; align-items: center; justify-content: space-between;">
                                    <span style="display: inline-flex; align-items: center; gap: 3px;">
                                        <span class="material-symbols-rounded" style="font-size: 12px;">hourglass_top</span>
                                        <span>{{ __('Pending PM Approval') }}</span>
                                    </span>
                                    @if($isManager)
                                        <button type="button" onclick="event.stopPropagation(); quickApproveTask('{{ $t->id }}')" class="tactile-btn" style="background: var(--ula-status-success); color: var(--ula-white); padding: 2px 6px; font-size: 9px; border: none; border-radius: 4px; display: inline-flex; align-items: center; gap: 2px;">
                                            <span class="material-symbols-rounded" style="font-size: 11px;">check</span>
                                            <span>{{ __('Approve') }}</span>
                                        </button>
                                    @endif
                                </div>
                            @elseif($t->approval_status === 'rejected')
                                <div style="background: rgba(217, 107, 95, 0.15); border: 1px solid rgba(217, 107, 95, 0.35); color: var(--ula-status-danger); font-size: 10px; font-weight: var(--ula-weight-bold); padding: 4px 8px; border-radius: var(--ula-radius-sm); display: inline-flex; align-items: center; gap: 3px;">
                                    <span class="material-symbols-rounded" style="font-size: 12px;">warning</span>
                                    <span>{{ __('Changes Requested') }}</span>
                                </div>
                            @endif

                            <!-- Metadata: Project & Due Date -->
                            <div class="task-card-meta">
                                <span class="task-project-name" style="display: inline-flex; align-items: center; gap: 3px;">
                                    <span class="material-symbols-rounded" style="font-size: 13px; color: var(--ula-text-muted);">folder</span>
                                    <span>{{ $t->project->name ?? 'General' }}</span>
                                </span>
                                @if($t->due_date)
                                    <span class="task-due-date {{ $t->due_date->isPast() && $t->status !== 'done' ? 'is-overdue' : '' }}" style="display: inline-flex; align-items: center; gap: 3px; font-family: var(--ula-font-mono); direction: ltr; unicode-bidi: isolate;">
                                        <span class="material-symbols-rounded" style="font-size: 12px;">calendar_today</span>
                                        <span>{{ $t->due_date->format('M d') }}</span>
                                    </span>
                                @endif
                            </div>

                            <!-- Footer: Direct Status Dropdown & Timer -->
                            <div class="task-card-footer">
                                <div style="display: flex; align-items: center; gap: 6px;">
                                    <select onclick="event.stopPropagation()" onchange="updateTaskStatusDirect('{{ $t->id }}', this.value)" class="card-status-select" {{ $canEditThisTask ? '' : 'disabled' }}>
                                        <option value="backlog" {{ $t->status === 'backlog' ? 'selected' : '' }}>{{ __('Backlog') }}</option>
                                        <option value="ready" {{ $t->status === 'ready' ? 'selected' : '' }}>{{ __('Ready') }}</option>
                                        <option value="in_progress" {{ $t->status === 'in_progress' ? 'selected' : '' }}>{{ __('In Progress') }}</option>
                                        <option value="review" {{ $t->status === 'review' || $t->status === 'qa' ? 'selected' : '' }}>{{ __('Review') }}</option>
                                        <option value="done" {{ $t->status === 'done' ? 'selected' : '' }}>{{ __('Done') }}</option>
                                    </select>
                                </div>

                                <button type="button" onclick="event.stopPropagation(); startTaskTimer('{{ $t->project_id }}', '{{ $t->id }}', '{{ addslashes($t->title) }}', '{{ addslashes($t->project->name ?? 'Project') }}')" class="tactile-btn" style="background: var(--ula-surface-accent-soft); color: var(--ula-accent-default); border: 1px solid var(--ula-border-subtle); padding: 3px 8px; font-size: 10.5px; border-radius: var(--ula-radius-pill); display: inline-flex; align-items: center; gap: 2px; font-family: var(--ula-font-mono); direction: ltr; unicode-bidi: isolate;" title="{{ __('Start Timer') }}">
                                    <span class="material-symbols-rounded" style="font-size: 12px;">play_arrow</span>
                                    <span>{{ round($t->logged_hours ?? $t->actual_hours ?? 0, 1) }}h</span>
                                </button>
                            </div>
                        </div>
                    @empty
                        <div class="mytasks-empty-hint" id="mytasks-empty-{{ $colKey }}" style="text-align: center; padding: 18px 8px; color: var(--ula-text-muted); font-size: var(--ula-size-xs); border: 1px dashed var(--ula-border-subtle); border-radius: var(--ula-radius-md);">
                            {{ __('No tasks in this stage.') }}
                        </div>
                    @endforelse
                </div>
            </div>
        @endforeach
    </div>
</div>