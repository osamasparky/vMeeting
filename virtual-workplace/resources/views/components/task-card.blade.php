@props([
    'task',
    'context' => 'alltasks', // 'mytasks', 'alltasks', 'hub'
    'project' => null,
    'canEdit' => true,
    'isManager' => false,
])

@php
    $p = $project ?? $task->project;
    $projectCode = $p->code ?? 'PRJ';
    $taskNum = $task->task_number ?? 1;
    $codeDisplay = "{$projectCode}-" . sprintf('%02d', $taskNum);
    $isDone = $task->status === 'done';
    
    // Assignee info
    $assignee = $task->assignee;
    $assigneeInitials = $assignee ? strtoupper(substr($assignee->name, 0, 2)) : '';
    $assigneeFirstName = $assignee ? explode(' ', $assignee->name)[0] : '';
    
    // Checklist info
    $totalChecklist = $task->checklistItems ? $task->checklistItems->count() : 0;
    $completedChecklist = $totalChecklist > 0 ? $task->checklistItems->where('is_completed', true)->count() : 0;
    
    // Logged hours
    $loggedHours = round($task->logged_hours ?? $task->actual_hours ?? 0, 1);
@endphp

@if($context === 'mytasks')
    <div class="kanban-task-card" 
         id="mytasks-card-{{ $task->id }}" 
         data-id="{{ $task->id }}" 
         data-status="{{ $task->status }}" 
         oncontextmenu="event.preventDefault(); event.stopPropagation(); openTaskContextMenu(event, '{{ $task->id }}', '{{ $task->project_id }}', '{{ addslashes($task->title) }}')"
         onclick="openTaskDetails('{{ $task->id }}')">
@elseif($context === 'alltasks')
    <div class="kanban-task-card global-kanban-card" 
         id="global-kanban-card-{{ $task->id }}"
         draggable="true" 
         ondragstart="handleGlobalDragStart(event, '{{ $task->id }}')" 
         ondragend="handleGlobalDragEnd(event)"
         oncontextmenu="event.preventDefault(); event.stopPropagation(); openTaskContextMenu(event, '{{ $task->id }}', '{{ $task->project_id }}', '{{ addslashes($task->title) }}')"
         data-id="{{ $task->id }}"
         data-title="{{ strtolower($task->title) }}"
         data-project-id="{{ $task->project_id }}"
         data-status="{{ $task->status }}"
         data-priority="{{ $task->priority }}"
         data-assignee-id="{{ $task->assignee_id }}"
         onclick="openTaskDetails('{{ $task->id }}')">
@else
    <div class="kanban-task-card kanban-card" 
         id="task-card-{{ $task->id }}"
         data-task-id="{{ $task->id }}"
         data-status="{{ $task->status }}"
         data-assignee="{{ $task->assignee_id ?? 'unassigned' }}"
         data-priority="{{ $task->priority ?? 'medium' }}"
         data-milestone="{{ $task->milestone_id ?? 'none' }}"
         data-due="{{ $task->due_date ? $task->due_date->format('Y-m-d') : '' }}"
         data-title="{{ strtolower($task->title) }} #{{ $task->task_number }}"
         onclick="openTaskInspector('{{ $task->id }}')"
         oncontextmenu="event.preventDefault(); event.stopPropagation(); openTaskContextMenu(event, '{{ $task->id }}', '{{ $p->id ?? $task->project_id }}', '{{ addslashes($task->title) }}')">
@endif

    <!-- 1. Header: Code Badge & Tags & Action Buttons -->
    <div class="task-card-header">
        <div class="task-card-tags">
            <span class="task-code-badge" title="{{ __('Task Code') }}">
                {{ $codeDisplay }}
            </span>

            @if($totalChecklist > 0)
                <span class="ula-badge ula-badge--live ula-badge--sm" style="font-size: 10px; font-family: var(--ula-font-mono); direction: ltr; unicode-bidi: isolate; gap: 3px;" title="{{ __('Checklist Progress') }}">
                    <span class="material-symbols-rounded" style="font-size: 12px;">check_box</span>
                    <span>{{ $completedChecklist }}/{{ $totalChecklist }}</span>
                </span>
            @endif

            @if($task->isRecurring())
                <span class="ula-badge ula-badge--attention ula-badge--sm" style="font-size: 10px; gap: 3px;" title="{{ __('Recurring: :rule', ['rule' => $task->recurrence_rule]) }}">
                    <span class="material-symbols-rounded" style="font-size: 12px;">refresh</span>
                    <span>{{ ucfirst($task->recurrence_rule) }}</span>
                </span>
            @endif
        </div>

        <div class="task-card-actions">
            @if($task->priority === 'urgent')
                <span class="ula-badge ula-badge--live ula-badge--sm" style="background: var(--ula-surface-danger-soft); color: var(--ula-status-danger); border-color: rgba(154, 88, 39, 0.3); gap: 2px;" title="{{ __('Urgent Priority') }}">
                    <span class="material-symbols-rounded" style="font-size: 13px;">local_fire_department</span>
                    <span>{{ __('Urgent') }}</span>
                </span>
            @elseif($task->priority === 'high')
                <span class="ula-badge ula-badge--attention ula-badge--sm" style="gap: 2px;" title="{{ __('High Priority') }}">
                    <span class="material-symbols-rounded" style="font-size: 13px;">bolt</span>
                    <span>{{ __('High') }}</span>
                </span>
            @endif

            <button type="button" 
                    onclick="event.stopPropagation(); openTaskContextMenu(event, '{{ $task->id }}', '{{ $p->id ?? $task->project_id }}', '{{ addslashes($task->title) }}')" 
                    class="task-dots-btn" 
                    title="{{ __('More actions') }}">
                <span class="material-symbols-rounded">more_horiz</span>
            </button>
        </div>
    </div>

    <!-- 2. Body: Title -->
    <h4 class="task-card-title" style="{{ $isDone ? 'text-decoration: line-through; opacity: 0.6;' : '' }}">
        {{ $task->title }}
    </h4>

    <!-- 3. Milestone / Sprint Badge -->
    @if($task->milestone)
        <div class="task-card-milestone" title="{{ __('Milestone: :name', ['name' => $task->milestone->name]) }}">
            <span class="material-symbols-rounded" style="font-size: 13px;">flag</span>
            <span>{{ $task->milestone->name }}</span>
        </div>
    @endif

    <!-- 4. Approval Status Warning Banners -->
    @if($task->approval_status === 'pending_approval')
        <div style="background: var(--ula-surface-warning-soft, rgba(214, 162, 58, 0.12)); border: 1px solid rgba(214, 162, 58, 0.35); color: var(--ula-gold-500); font-size: 11px; font-weight: 700; padding: 6px 10px; border-radius: 8px; display: flex; align-items: center; justify-content: space-between;">
            <span style="display: inline-flex; align-items: center; gap: 4px;">
                <span class="material-symbols-rounded" style="font-size: 14px;">hourglass_top</span>
                <span>{{ __('Pending PM Approval') }}</span>
            </span>
            @if($isManager)
                <button type="button" onclick="event.stopPropagation(); {{ $context === 'hub' ? 'quickApproveHubTask' : 'quickApproveTask' }}('{{ $task->id }}')" class="ula-btn ula-btn--primary ula-btn--sm" style="height: 24px; min-height: 24px; padding: 0 8px; font-size: 10px; border-radius: 6px;">
                    <span class="material-symbols-rounded" style="font-size: 12px;">check</span>
                    <span>{{ __('Approve') }}</span>
                </button>
            @endif
        </div>
    @elseif($task->approval_status === 'rejected')
        <div style="background: var(--ula-surface-danger-soft); border: 1px solid rgba(154, 88, 39, 0.3); color: var(--ula-status-danger); font-size: 11px; font-weight: 700; padding: 6px 10px; border-radius: 8px; display: inline-flex; align-items: center; gap: 4px;">
            <span class="material-symbols-rounded" style="font-size: 14px;">warning</span>
            <span>{{ __('Changes Requested') }}</span>
        </div>
    @endif

    <!-- 5. Metadata: Project Folder & Assignee & Due Date -->
    <div class="task-card-meta">
        <span class="task-project-name" title="{{ $p->name ?? 'Project' }}">
            <span class="material-symbols-rounded" style="font-size: 14px; color: var(--ula-text-muted);">folder</span>
            <span>{{ $p->name ?? 'General' }}</span>
        </span>

        <div style="display: flex; align-items: center; gap: 6px;">
            @if($assignee)
                <div class="task-assignee-chip" title="{{ $assignee->name }}">
                    <div class="task-avatar-circle">
                        {{ $assigneeInitials }}
                    </div>
                    <span style="max-width: 65px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">{{ $assigneeFirstName }}</span>
                </div>
            @endif

            @if($task->due_date)
                <span class="task-due-date {{ $task->due_date->isPast() && !$isDone ? 'is-overdue' : '' }}" title="{{ __('Due Date: :date', ['date' => $task->due_date->format('Y-m-d')]) }}">
                    <span class="material-symbols-rounded" style="font-size: 13px;">calendar_today</span>
                    <span>{{ $task->due_date->format('M d') }}</span>
                </span>
            @endif
        </div>
    </div>

    <!-- 6. Footer: Direct Status Dropdown & Timer Button -->
    <div class="task-card-footer">
        <div style="display: flex; align-items: center; gap: 6px; flex: 1; min-width: 0;">
            <select onclick="event.stopPropagation()" 
                    onchange="{{ $context === 'hub' ? 'updateHubTaskStatusDirect' : 'updateTaskStatusDirect' }}('{{ $task->id }}', this.value)" 
                    class="card-status-select" 
                    style="max-width: 100%;"
                    {{ $canEdit ? '' : 'disabled' }}>
                <option value="backlog" {{ $task->status === 'backlog' ? 'selected' : '' }}>{{ __('Backlog') }}</option>
                <option value="ready" {{ $task->status === 'ready' ? 'selected' : '' }}>{{ __('Ready') }}</option>
                <option value="in_progress" {{ $task->status === 'in_progress' ? 'selected' : '' }}>{{ __('In Progress') }}</option>
                <option value="review" {{ $task->status === 'review' || $task->status === 'qa' ? 'selected' : '' }}>{{ __('Review') }}</option>
                <option value="done" {{ $task->status === 'done' ? 'selected' : '' }}>{{ __('Done') }}</option>
            </select>
        </div>

        <button type="button" 
                onclick="event.stopPropagation(); {{ $context === 'hub' ? 'startHubTaskTimerDirect' : 'startTaskTimer' }}('{{ $p->id ?? $task->project_id }}', '{{ $task->id }}', '{{ addslashes($task->title) }}', '{{ addslashes($p->name ?? 'Project') }}')" 
                class="task-timer-pill" 
                title="{{ __('Start Timer') }}">
            <span class="material-symbols-rounded" style="font-size: 13px;">play_arrow</span>
            <span>{{ $loggedHours }}h</span>
        </button>
    </div>
</div>
