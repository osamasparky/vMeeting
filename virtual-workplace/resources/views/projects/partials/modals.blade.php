    <!-- Modal: New Task -->
    <div id="new-task-modal" class="modal-overlay">
        <div class="modal-card">
            <div class="modal-header">
                <h3 style="font-size: 18px; font-weight: 900; color: var(--ula-text-primary);"><span class="material-symbols-rounded" style="font-size: 1em; vertical-align: text-bottom;">edit_note</span> {{ __('Create Task in') }} {{ $project->name }}</h3>
                <button onclick="closeNewTaskModal()" class="modal-close"><span class="material-symbols-rounded" style="font-size: 1em; vertical-align: text-bottom;">close</span></button>
            </div>
            <form id="new-task-form" onsubmit="createProjectTaskSubmit(event)" style="display: flex; flex-direction: column; gap: 14px;">
                <input type="hidden" name="project_id" value="{{ $project->id }}">
                <div>
                    <label style="display: block; font-size: 12px; font-weight: 700; color: var(--ula-text-secondary); margin-bottom: 6px;">{{ __('Task Title') }} *</label>
                    <input type="text" name="title" required placeholder="e.g. Implement payment gateway webhook" class="form-input">
                </div>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                    <div>
                        <label style="display: block; font-size: 12px; font-weight: 700; color: var(--ula-text-secondary); margin-bottom: 6px;">{{ __('Assignee') }}</label>
                        <select name="assignee_id" class="form-input">
                            <option value="">— {{ __('Unassigned') }} —</option>
                            @foreach($allMembers as $m)
                                <option value="{{ $m->user_id }}">{{ $m->user->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label style="display: block; font-size: 12px; font-weight: 700; color: var(--ula-text-secondary); margin-bottom: 6px;">{{ __('Priority') }}</label>
                        <select name="priority" class="form-input">
                            <option value="medium">{{ __('Medium') }}</option>
                            <option value="low">{{ __('Low') }}</option>
                            <option value="high">{{ __('High') }}</option>
                            <option value="urgent">{{ __('Urgent') }}</option>
                        </select>
                    </div>
                </div>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                    <div>
                        <label style="display: block; font-size: 12px; font-weight: 700; color: var(--ula-text-secondary); margin-bottom: 6px;">{{ __('Estimated Hours') }}</label>
                        <input type="number" step="0.5" name="estimated_hours" placeholder="4.0" class="form-input">
                    </div>
                    <div>
                        <label style="display: block; font-size: 12px; font-weight: 700; color: var(--ula-text-secondary); margin-bottom: 6px;">{{ __('Due Date') }}</label>
                        <input type="date" name="due_date" class="form-input">
                    </div>
                </div>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                    <div>
                        <label style="display: block; font-size: 12px; font-weight: 700; color: var(--ula-text-secondary); margin-bottom: 6px;"><span class="material-symbols-rounded" style="font-size: 1em; vertical-align: text-bottom;">flag</span> {{ __('Milestone / Phase') }}</label>
                        <select name="milestone_id" class="form-input">
                            <option value="">— {{ __('No Milestone') }} —</option>
                            @foreach($project->milestones as $pms)
                                <option value="{{ $pms->id }}">{{ $pms->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label style="display: block; font-size: 12px; font-weight: 700; color: var(--ula-text-secondary); margin-bottom: 6px;"><span class="material-symbols-rounded" style="font-size: 1em; vertical-align: text-bottom;">refresh</span> {{ __('Repeat / Recurrence') }}</label>
                        <select name="recurrence_rule" id="new-task-recurrence-rule" onchange="toggleRecurrenceDetails(this.value)" class="form-input">
                            <option value="">{{ __('No Repeat (One-time)') }}</option>
                            <option value="daily">{{ __('Daily') }}</option>
                            <option value="weekly">{{ __('Weekly') }}</option>
                            <option value="biweekly">{{ __('Biweekly (Every 2 weeks)') }}</option>
                            <option value="monthly">{{ __('Monthly') }}</option>
                            <option value="quarterly">{{ __('Quarterly (Every 3 months)') }}</option>
                        </select>
                    </div>
                </div>
                <div id="new-task-recurrence-extra" style="display: none; grid-template-columns: 1fr 1fr; gap: 12px; background: var(--ula-surface-page-alt); padding: 10px; border-radius: var(--ula-radius-sm); border: 1px dashed var(--ula-border-subtle);">
                    <div>
                        <label style="display: block; font-size: 11px; font-weight: 700; color: var(--ula-text-secondary); margin-bottom: 4px;">{{ __('Repeat Every') }}</label>
                        <div style="display: flex; align-items: center; gap: 6px;">
                            <input type="number" name="recurrence_interval" value="1" min="1" max="99" class="form-input" style="padding: 6px 8px; font-size: 12px;">
                            <span style="font-size: 11px; color: var(--ula-text-muted);">{{ __('cycle(s)') }}</span>
                        </div>
                    </div>
                    <div>
                        <label style="display: block; font-size: 11px; font-weight: 700; color: var(--ula-text-secondary); margin-bottom: 4px;">{{ __('Repeat Until (Optional)') }}</label>
                        <input type="date" name="recurrence_ends_at" class="form-input" style="padding: 6px 8px; font-size: 12px;">
                    </div>
                </div>
                <div>
                    <label style="display: block; font-size: 12px; font-weight: 700; color: var(--ula-text-secondary); margin-bottom: 6px;">{{ __('Description / Specifications') }}</label>
                    <textarea name="description" rows="3" placeholder="Task requirements..." class="form-input" style="resize: vertical;"></textarea>
                </div>
                <button type="submit" class="tactile-btn btn-primary" style="margin-top: 8px; padding: 12px; font-size: 14px;">
                    <span class="material-symbols-rounded" style="font-size: 1em; vertical-align: text-bottom;">save</span> {{ __('Create Task') }}
                </button>
            </form>
        </div>
    </div>

    <!-- Modal: Log Time -->
    <div id="manual-time-modal" class="modal-overlay">
        <div class="modal-card">
            <div class="modal-header">
                <h3 style="font-size: 18px; font-weight: 900; color: var(--ula-text-primary);"><span class="material-symbols-rounded" style="font-size: 1em; vertical-align: text-bottom;">edit</span> {{ __('Log Manual Time Entry') }}</h3>
                <button onclick="closeManualTimeModal()" class="modal-close"><span class="material-symbols-rounded" style="font-size: 1em; vertical-align: text-bottom;">close</span></button>
            </div>
            <form id="manual-time-form" onsubmit="logProjectTimeSubmit(event)" style="display: flex; flex-direction: column; gap: 14px;">
                <input type="hidden" name="project_id" value="{{ $project->id }}">
                <div>
                    <label style="display: block; font-size: 12px; font-weight: 700; color: var(--ula-text-secondary); margin-bottom: 6px;">{{ __('Associated Task') }}</label>
                    <select name="task_id" class="form-input">
                        <option value="">— {{ __('General Project Work') }} —</option>
                        @foreach($tasks as $t)
                            <option value="{{ $t->id }}">#{{ $t->task_number }} {{ $t->title }}</option>
                        @endforeach
                    </select>
                </div>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                    <div>
                        <label style="display: block; font-size: 12px; font-weight: 700; color: var(--ula-text-secondary); margin-bottom: 6px;">{{ __('Start Time') }} *</label>
                        <input type="datetime-local" name="started_at" required class="form-input">
                    </div>
                    <div>
                        <label style="display: block; font-size: 12px; font-weight: 700; color: var(--ula-text-secondary); margin-bottom: 6px;">{{ __('End Time') }} *</label>
                        <input type="datetime-local" name="ended_at" required class="form-input">
                    </div>
                </div>
                <div>
                    <label style="display: block; font-size: 12px; font-weight: 700; color: var(--ula-text-secondary); margin-bottom: 6px;">{{ __('Work Description') }}</label>
                    <input type="text" name="description" placeholder="Details of work executed..." class="form-input">
                </div>
                <button type="submit" class="tactile-btn btn-primary" style="margin-top: 8px; padding: 12px; font-size: 14px;">
                    ⏱️ {{ __('Save Time Log') }}
                </button>
            </form>
        </div>
    </div>

    <!-- Modal: Schedule Project Meeting -->
    <div id="schedule-meeting-modal" class="modal-overlay">
        <div class="modal-card">
            <div class="modal-header">
                <h3 style="font-size: 18px; font-weight: 900; color: var(--ula-text-primary);"><span class="material-symbols-rounded" style="font-size: 1em; vertical-align: text-bottom;">calendar_month</span> {{ __('Schedule Project Meeting') }}</h3>
                <button onclick="closeScheduleProjectMeetingModal()" class="modal-close"><span class="material-symbols-rounded" style="font-size: 1em; vertical-align: text-bottom;">close</span></button>
            </div>
            <form id="hub-schedule-meeting-form" onsubmit="scheduleProjectMeetingSubmit(event)" method="POST" action="{{ route('meetings.schedule') }}" style="display: flex; flex-direction: column; gap: 14px;">
                @csrf
                <input type="hidden" name="scope" value="project">
                <input type="hidden" name="project_id" value="{{ $project->id }}">

                <div>
                    <label style="display: block; font-size: 12px; font-weight: 700; color: var(--ula-text-secondary); margin-bottom: 6px;">{{ __('Meeting Title') }} *</label>
                    <input type="text" name="title" required value="{{ $project->name }} Sync" class="form-input">
                </div>

                <div>
                    <label style="display: block; font-size: 12px; font-weight: 700; color: var(--ula-text-secondary); margin-bottom: 6px;">{{ __('Agenda / Notes') }}</label>
                    <textarea name="description" rows="2" placeholder="Topics to cover..." class="form-input" style="resize: vertical;"></textarea>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                    <div>
                        <label style="display: block; font-size: 12px; font-weight: 700; color: var(--ula-text-secondary); margin-bottom: 6px;">{{ __('Meeting Room') }}</label>
                        <select name="room_id" class="form-input">
                            @foreach($rooms as $r)
                                <option value="{{ $r->id }}">{{ $r->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label style="display: block; font-size: 12px; font-weight: 700; color: var(--ula-text-secondary); margin-bottom: 6px;">{{ __('Duration') }}</label>
                        <select name="duration_minutes" class="form-input">
                            <option value="15">15 {{ __('Minutes') }}</option>
                            <option value="30" selected>30 {{ __('Minutes') }}</option>
                            <option value="45">45 {{ __('Minutes') }}</option>
                            <option value="60">1 {{ __('Hour') }}</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label style="display: block; font-size: 12px; font-weight: 700; color: var(--ula-text-secondary); margin-bottom: 6px;">{{ __('Scheduled Date & Time') }} *</label>
                    <input type="datetime-local" name="scheduled_at" id="hub-meeting-time-input" required class="form-input">
                </div>

                <!-- Attendee selection -->
                <div>
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 6px;">
                        <label style="font-size: 12px; font-weight: 700; color: var(--ula-text-secondary);">
                            <span class="material-symbols-rounded" style="font-size: 1em; vertical-align: text-bottom;">group</span> {{ __('Select Project Members to Attend') }}
                        </label>
                        <button type="button" onclick="toggleAllHubProjectAttendees()" style="background: none; border: none; font-size: 11px; font-weight: 800; color: var(--ula-text-primary); cursor: pointer;">
                            <span class="material-symbols-rounded" style="font-size: 1em; vertical-align: text-bottom;">check</span> {{ __('Select / Unselect All') }}
                        </button>
                    </div>
                    <div style="max-height: 120px; overflow-y: auto; background: var(--ula-surface-page-alt); border: 1px solid var(--ula-border-subtle); border-radius: 8px; padding: 6px 10px; display: flex; flex-direction: column; gap: 4px;">
                        @foreach($allMembers as $pm)
                            @if($pm->user_id !== $user->id)
                                <label style="display: flex; align-items: center; justify-content: space-between; font-size: 11px; color: var(--ula-text-primary); cursor: pointer; padding: 3px 4px; border-radius: 4px;">
                                    <span style="display: flex; align-items: center; gap: 6px;">
                                        <input type="checkbox" name="attendee_ids[]" value="{{ $pm->user_id }}" checked class="hub-proj-attendee-chk" style="accent-color: var(--ula-palm-900);">
                                        <strong>{{ $pm->user->name }}</strong>
                                        <span style="color: var(--ula-text-muted);">({{ $pm->user->email }})</span>
                                    </span>
                                    <span class="badge-pill badge-neutral" style="font-size: 9px;">{{ $pm->role->name ?? 'Team' }}</span>
                                </label>
                            @endif
                        @endforeach
                    </div>
                </div>

                <button type="submit" id="hub-schedule-meeting-btn" class="tactile-btn btn-primary" style="margin-top: 6px; padding: 12px; font-size: 14px;">
                    <span class="material-symbols-rounded" style="font-size: 1em; vertical-align: text-bottom;">rocket_launch</span> {{ __('Schedule Meeting & Email Team') }}
                </button>
            </form>
        </div>
    </div>

    <!-- Modal: Task Inspector & Activity Drawer (Unified) -->
    <div id="task-details-modal" class="modal-overlay">
        <div class="modal-card" style="max-width: 860px; width: 95vw; max-height: 90vh; display: flex; flex-direction: column; padding: 28px; overflow: hidden; border-radius: 24px; background: var(--ula-surface-card); box-shadow: var(--ula-shadow-xl); border: 1px solid var(--ula-border-subtle);">
            <!-- Header -->
            <div style="margin-bottom: 16px; border-bottom: 1px solid var(--ula-border-subtle); padding-bottom: 16px;">
                <!-- Top Row: Badges & Controls -->
                <div style="display: flex; justify-content: space-between; align-items: center; gap: 12px; flex-wrap: wrap;">
                    <div style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
                        <span id="task-modal-code" class="task-code-badge" style="font-size: 12px; padding: 4px 10px;">#1</span>
                        <span id="task-modal-status-badge" class="ula-badge ula-badge--live ula-badge--sm">In Progress</span>
                        <span id="task-modal-priority-badge" class="ula-badge ula-badge--attention ula-badge--sm">Medium</span>
                    </div>
                    <div style="display: flex; align-items: center; gap: 10px;">
                        <button id="task-modal-timer-btn" class="ula-btn ula-btn--secondary ula-btn--sm" type="button">
                            <span class="material-symbols-rounded ula-btn__icon">play_arrow</span>
                            <span>{{ __('Start Timer') }}</span>
                        </button>
                        <button onclick="closeTaskDetailsModal()" class="ula-icon-btn ula-icon-btn--subtle ula-icon-btn--sm" type="button" title="{{ __('Close') }}">
                            <span class="material-symbols-rounded">close</span>
                        </button>
                    </div>
                </div>

                <!-- Title -->
                <h2 id="task-modal-title" style="font-size: 20px; font-weight: 800; margin: 12px 0 10px; color: var(--ula-text-primary); line-height: 1.35; font-family: var(--ula-font-ar, 'Cairo', sans-serif);">Task Title</h2>

                <!-- Metadata Chips -->
                <div style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
                    <span class="task-modal-chip">
                        <span class="material-symbols-rounded" style="font-size: 16px; color: var(--ula-palm-600);">folder</span>
                        <span>{{ __('Project') }}: <strong id="task-modal-project">{{ $project->name }}</strong></span>
                    </span>
                    <span class="task-modal-chip">
                        <span class="material-symbols-rounded" style="font-size: 16px; color: var(--ula-accent-default);">person</span>
                        <span>{{ __('Assignee') }}: <strong id="task-modal-assignee" style="color: var(--ula-accent-default);">Assignee</strong></span>
                    </span>
                    <span class="task-modal-chip">
                        <span class="material-symbols-rounded" style="font-size: 16px; color: var(--ula-gold-500);">calendar_month</span>
                        <span>{{ __('Due Date') }}: <strong id="task-modal-due">Date</strong></span>
                    </span>
                    <span id="task-modal-milestone-chip" class="task-modal-chip" style="display: none;">
                        <span class="material-symbols-rounded" style="font-size: 16px; color: var(--ula-palm-600);">flag</span>
                        <span>{{ __('Milestone') }}: <strong id="task-modal-milestone">—</strong></span>
                    </span>
                    <span id="task-modal-recurrence-chip" class="task-modal-chip" style="display: none;">
                        <span class="material-symbols-rounded" style="font-size: 16px; color: var(--ula-accent-default);">refresh</span>
                        <span>{{ __('Repeat') }}: <strong id="task-modal-recurrence">—</strong></span>
                    </span>
                </div>
            </div>

            <!-- Task Quick Status Changer Bar & PM Approval Actions -->
            <div style="display: flex; flex-direction: column; gap: 10px; margin-bottom: 16px;">
                <div style="display: flex; justify-content: space-between; align-items: center; background: var(--ula-surface-page-alt); padding: 10px 16px; border-radius: var(--ula-radius-md, 14px); border: 1px solid var(--ula-border-subtle); flex-wrap: wrap; gap: 12px;">
                    <div style="display: flex; align-items: center; gap: 8px;">
                        <span class="material-symbols-rounded" style="font-size: 17px; color: var(--ula-gold-500);">bolt</span>
                        <span style="font-size: 12.5px; font-weight: 700; color: var(--ula-text-secondary);">{{ __('Status') }}:</span>
                        <select id="task-modal-status-select" onchange="updateCurrentTaskStatus(this.value)" style="background: var(--ula-surface-card); border: 1px solid var(--ula-border-default); color: var(--ula-text-primary); font-size: 12.5px; font-weight: 700; border-radius: 8px; padding: 4px 12px; height: 32px; outline: none; cursor: pointer;">
                            <option value="backlog">{{ __('Backlog') }}</option>
                            <option value="ready">{{ __('Ready') }}</option>
                            <option value="in_progress">{{ __('In Progress') }}</option>
                            <option value="review">{{ __('Review / QA') }}</option>
                            <option value="done">{{ __('Done') }}</option>
                        </select>
                    </div>
                    <div class="task-modal-chip" style="font-family: var(--ula-font-mono); direction: ltr; unicode-bidi: isolate; font-weight: 800; color: var(--ula-palm-900);">
                        <span class="material-symbols-rounded" style="font-size: 16px; color: var(--ula-status-success);">timer</span>
                        <span id="task-modal-hours">0.00h / 0.00h</span>
                    </div>
                </div>

                <!-- Approval Status Alert & Action Box -->
                <div id="task-modal-approval-banner" style="display: none; padding: 12px 16px; border-radius: var(--ula-radius-md); font-size: 12.5px; font-weight: 700; justify-content: space-between; align-items: center; gap: 12px; flex-wrap: wrap;">
                    <div id="task-modal-approval-text" style="display: flex; align-items: center; gap: 8px;"></div>
                    <div id="task-modal-approval-actions" style="display: flex; gap: 8px;"></div>
                </div>
            </div>

            <!-- Sub-Tabs Segmented Control -->
            <div class="task-modal-segmented-bar">
                <button type="button" onclick="switchTaskInspectorTab('details')" id="task-tab-btn-details" class="task-modal-tab-btn active">
                    <span class="material-symbols-rounded" style="font-size: 17px;">edit_note</span>
                    <span>{{ __('Details') }}</span>
                </button>
                <button type="button" onclick="switchTaskInspectorTab('checklist')" id="task-tab-btn-checklist" class="task-modal-tab-btn">
                    <span class="material-symbols-rounded" style="font-size: 17px;">check_box</span>
                    <span>{{ __('Checklist') }}</span>
                    <span id="task-checklist-count" class="task-modal-tab-count">0</span>
                </button>
                <button type="button" onclick="switchTaskInspectorTab('attachments')" id="task-tab-btn-attachments" class="task-modal-tab-btn">
                    <span class="material-symbols-rounded" style="font-size: 17px;">attach_file</span>
                    <span>{{ __('Files') }}</span>
                    <span id="task-attachments-count" class="task-modal-tab-count">0</span>
                </button>
                <button type="button" onclick="switchTaskInspectorTab('comments')" id="task-tab-btn-comments" class="task-modal-tab-btn">
                    <span class="material-symbols-rounded" style="font-size: 17px;">chat_bubble</span>
                    <span>{{ __('Discussions') }}</span>
                    <span id="task-comments-count" class="task-modal-tab-count">0</span>
                </button>
                <button type="button" onclick="switchTaskInspectorTab('dependencies')" id="task-tab-btn-dependencies" class="task-modal-tab-btn">
                    <span class="material-symbols-rounded" style="font-size: 17px;">link</span>
                    <span>{{ __('Dependencies') }}</span>
                    <span id="task-dependencies-count" class="task-modal-tab-count">0</span>
                </button>
                <button type="button" onclick="switchTaskInspectorTab('timelog')" id="task-tab-btn-timelog" class="task-modal-tab-btn">
                    <span class="material-symbols-rounded" style="font-size: 17px;">timer</span>
                    <span>{{ __('Time Log') }}</span>
                    <span id="task-timelog-count" class="task-modal-tab-count">0</span>
                </button>
            </div>

            <!-- Tab Contents -->
            <div style="flex: 1; overflow-y: auto; padding-inline-end: 4px;">
                <!-- 1. Details -->
                <div id="task-inspector-details" style="display: block;">
                    <div style="margin-bottom: 14px;">
                        <label style="display: block; font-size: 11px; font-weight: 800; color: var(--ula-text-secondary); text-transform: uppercase; margin-bottom: 6px;">{{ __('Description') }}</label>
                        <div id="task-modal-description" style="background: var(--ula-surface-page-alt); padding: 16px; border-radius: 12px; font-size: 13.5px; color: var(--ula-text-primary); line-height: 1.6; border: 1px solid var(--ula-border-subtle); box-shadow: var(--ula-shadow-xs); white-space: pre-wrap;">
                            —
                        </div>
                    </div>
                </div>

                <!-- 2. Checklist -->
                <div id="task-inspector-checklist" style="display: none;">
                    <form onsubmit="addTaskChecklistItem(event)" style="display: flex; gap: 8px; margin-bottom: 14px;">
                        <input type="text" id="new-checklist-title-input" required placeholder="{{ __('Add checklist sub-item (e.g. Write unit tests, create migration)...') }}" style="flex: 1; background: var(--ula-surface-page-alt); border: 1px solid var(--ula-border-subtle); border-radius: 10px; padding: 10px 14px; color: var(--ula-text-primary); outline: none; font-size: 13px; font-weight: 600;">
                        <button type="submit" class="ula-btn ula-btn--primary ula-btn--md" style="height: 42px; min-height: 42px;">
                            <span class="material-symbols-rounded ula-btn__icon">add</span>
                            <span>{{ __('Add Item') }}</span>
                        </button>
                    </form>
                    <div id="task-checklist-items-container" style="display: flex; flex-direction: column; gap: 8px;"></div>
                </div>

                <!-- 3. Attachments & Files -->
                <div id="task-inspector-attachments" style="display: none;">
                    <form onsubmit="uploadTaskAttachmentSubmit(event)" style="background: var(--ula-surface-page-alt); border: 1px dashed var(--ula-border-default); border-radius: 14px; padding: 20px; text-align: center; margin-bottom: 16px;">
                        <div style="font-size: 28px; margin-bottom: 6px; color: var(--ula-palm-700);"><span class="material-symbols-rounded">attach_file</span></div>
                        <div style="font-size: 13px; font-weight: 800; color: var(--ula-text-primary); margin-bottom: 10px;">{{ __('Upload Document or Attachment to Task') }}</div>
                        <div style="display: flex; justify-content: center; gap: 10px; align-items: center; max-width: 440px; margin: 0 auto; flex-wrap: wrap;">
                            <input type="file" id="task-file-input" required style="font-size: 12px; color: var(--ula-text-primary);">
                            <button type="submit" class="ula-btn ula-btn--primary ula-btn--sm">
                                <span class="material-symbols-rounded ula-btn__icon">upload</span>
                                <span>{{ __('Upload') }}</span>
                            </button>
                        </div>
                    </form>
                    <div id="task-attachments-list-container" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(220px, 1fr)); gap: 10px;"></div>
                </div>

                <!-- 4. Comments & Mentions -->
                <div id="task-inspector-comments" style="display: none;">
                    <div id="task-comments-feed" style="display: flex; flex-direction: column; gap: 10px; margin-bottom: 14px; max-height: 280px; overflow-y: auto;"></div>
                    
                    <!-- Quick Mention Suggestion Chips -->
                    <div style="display: flex; align-items: center; gap: 6px; margin-bottom: 8px; flex-wrap: wrap;">
                        <span style="font-size: 11px; font-weight: 800; color: var(--ula-text-secondary);">@ {{ __('Mention') }}:</span>
                        @foreach($allMembers->take(6) as $chipMember)
                            @if($chipMember->user_id !== $user->id)
                                <button type="button" onclick="insertMentionHandle('{{ $chipMember->user->name }}')" class="nav-badge-pill" style="cursor: pointer; font-size: 10px; border: 1px solid var(--ula-border-subtle); background: var(--ula-surface-page-alt); color: var(--ula-accent-default); font-weight: 700;" title="{{ __('Click to mention :name', ['name' => $chipMember->user->name]) }}">
                                    @<span>{{ $chipMember->user->name }}</span>
                                </button>
                            @endif
                        @endforeach
                    </div>

                    <form onsubmit="addTaskCommentSubmit(event)" style="display: flex; gap: 8px;">
                        <input type="text" id="new-comment-body-input" required placeholder="{{ __('Write a comment or status update... Type @name to mention') }}" style="flex: 1; background: var(--ula-surface-page-alt); border: 1px solid var(--ula-border-subtle); border-radius: 10px; padding: 10px 14px; color: var(--ula-text-primary); outline: none; font-size: 13px; font-weight: 600;">
                        <button type="submit" class="ula-btn ula-btn--primary ula-btn--md" style="height: 42px; min-height: 42px;">
                            <span class="material-symbols-rounded ula-btn__icon">chat_bubble</span>
                            <span>{{ __('Post') }}</span>
                        </button>
                    </form>
                </div>

                <!-- 5. Dependencies -->
                <div id="task-inspector-dependencies" style="display: none;">
                    <div style="background: var(--ula-surface-page-alt); padding: 16px; border-radius: 14px; margin-bottom: 14px; border: 1px solid var(--ula-border-subtle); box-shadow: var(--ula-shadow-xs);">
                        <label style="display: block; font-size: 11px; font-weight: 800; color: var(--ula-text-secondary); text-transform: uppercase; margin-bottom: 8px;"><span class="material-symbols-rounded" style="font-size: 14px; vertical-align: text-bottom;">link</span> {{ __('Add Predecessor / Blocker Task') }}</label>
                        <form onsubmit="addTaskDependencySubmit(event)" style="display: flex; gap: 8px; flex-wrap: wrap;">
                            <select id="dependency-blocker-select" required style="flex: 1; min-width: 220px; background: var(--ula-surface-card); border: 1px solid var(--ula-border-subtle); border-radius: 10px; padding: 8px 12px; color: var(--ula-text-primary); font-size: 12.5px; font-weight: 600;">
                                <option value="">— {{ __('Select Blocker Task') }} —</option>
                                @foreach($project->tasks as $oth)
                                    <option value="{{ $oth->id }}">#{{ $oth->task_number }} {{ $oth->title }}</option>
                                @endforeach
                            </select>
                            <button type="submit" class="ula-btn ula-btn--primary ula-btn--sm">
                                <span class="material-symbols-rounded ula-btn__icon">add</span>
                                <span>{{ __('Add Blocker') }}</span>
                            </button>
                        </form>
                    </div>
                    <div id="task-dependencies-container" style="display: flex; flex-direction: column; gap: 8px;"></div>
                </div>

                <!-- 6. Time Log -->
                <div id="task-inspector-timelog" style="display: none;">
                    <div style="overflow-x: auto; border: 1px solid var(--ula-border-subtle); border-radius: var(--ula-radius-lg); background: var(--ula-surface-card);">
                        <table class="data-table" style="margin-bottom: 0;">
                            <thead>
                                <tr>
                                    <th>{{ __('Date') }}</th>
                                    <th>{{ __('Member') }}</th>
                                    <th>{{ __('Duration') }}</th>
                                    <th>{{ __('Description') }}</th>
                                    <th>{{ __('Status') }}</th>
                                </tr>
                            </thead>
                            <tbody id="task-modal-timelog-body"></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal: New Project Document (ClickUp Docs) -->
    <div id="new-doc-modal" class="modal-overlay">
        <div class="modal-card">
            <div class="modal-header">
                <h3 style="font-size: 18px; font-weight: 900; color: var(--ula-text-primary);"><span class="material-symbols-rounded" style="font-size: 1em; vertical-align: text-bottom;">menu_book</span> {{ __('Create Project Document / Wiki') }}</h3>
                <button onclick="closeNewDocModal()" class="modal-close"><span class="material-symbols-rounded" style="font-size: 1em; vertical-align: text-bottom;">close</span></button>
            </div>
            <form id="new-doc-form" onsubmit="createProjectDocSubmit(event)" style="display: flex; flex-direction: column; gap: 14px;">
                <div style="display: grid; grid-template-columns: 60px 1fr; gap: 10px;">
                    <div>
                        <label style="display: block; font-size: 12px; font-weight: 700; color: var(--ula-text-secondary); margin-bottom: 6px;">{{ __('Icon') }}</label>
                        <input type="text" name="icon" value="📄" class="form-input" style="text-align: center; font-size: 16px;">
                    </div>
                    <div>
                        <label style="display: block; font-size: 12px; font-weight: 700; color: var(--ula-text-secondary); margin-bottom: 6px;">{{ __('Document Title') }} *</label>
                        <input type="text" name="title" required placeholder="e.g. Technical Specification & API Contracts" class="form-input">
                    </div>
                </div>
                <div>
                    <label style="display: block; font-size: 12px; font-weight: 700; color: var(--ula-text-secondary); margin-bottom: 6px;">{{ __('Markdown Content / Specification') }}</label>
                    <textarea name="content" rows="6" placeholder="# Overview&#10;&#10;Write project documentation, meeting minutes, and architectural decisions here..." class="form-input" style="resize: vertical; font-family: monospace;"></textarea>
                </div>
                <div style="display: flex; align-items: center; gap: 8px;">
                    <input type="checkbox" name="is_pinned" id="doc_pinned" style="accent-color: var(--ula-palm-900);">
                    <label for="doc_pinned" style="font-size: 12px; font-weight: 700; color: var(--ula-text-primary); cursor: pointer;"><span class="material-symbols-rounded" style="font-size: 1em; vertical-align: text-bottom;">push_pin</span> {{ __('Pin to top of knowledge wiki') }}</label>
                </div>
                <button type="submit" class="tactile-btn btn-primary" style="margin-top: 8px; padding: 12px; font-size: 14px;">
                    <span class="material-symbols-rounded" style="font-size: 1em; vertical-align: text-bottom;">save</span> {{ __('Publish Document') }}
                </button>
            </form>
        </div>
    </div>

    <!-- Modal: New Strategic Goal (ClickUp Goals) -->
    <div id="new-goal-modal" class="modal-overlay">
        <div class="modal-card">
            <div class="modal-header">
                <h3 style="font-size: 18px; font-weight: 900; color: var(--ula-text-primary);"><span class="material-symbols-rounded" style="font-size: 1em; vertical-align: text-bottom;">track_changes</span> {{ __('Create Strategic Project Goal') }}</h3>
                <button onclick="closeNewGoalModal()" class="modal-close"><span class="material-symbols-rounded" style="font-size: 1em; vertical-align: text-bottom;">close</span></button>
            </div>
            <form id="new-goal-form" onsubmit="createProjectGoalSubmit(event)" style="display: flex; flex-direction: column; gap: 14px;">
                <div>
                    <label style="display: block; font-size: 12px; font-weight: 700; color: var(--ula-text-secondary); margin-bottom: 6px;">{{ __('Goal Name') }} *</label>
                    <input type="text" name="name" required placeholder="e.g. Beta Launch & 100 User Onboarding" class="form-input">
                </div>
                <div>
                    <label style="display: block; font-size: 12px; font-weight: 700; color: var(--ula-text-secondary); margin-bottom: 6px;"><span class="material-symbols-rounded" style="font-size: 1em; vertical-align: text-bottom;">bolt</span> {{ __('Key Metric & Auto-Tracking Engine') }} *</label>
                    <select name="target_type" id="goal-target-type-select" onchange="toggleGoalMetricFields(this.value)" class="form-input" style="font-weight: 700;">
                        <option value="tasks">{{ __('Tasks Completion (Auto-calculated from done tasks)') }}</option>
                        <option value="milestones">{{ __('Milestones Delivery (Auto-calculated from completed phases)') }}</option>
                        <option value="hours">⏱️ {{ __('Hours Budget (Auto-calculated from logged timers)') }}</option>
                        <option value="number">{{ __('Custom Numeric Target (Manual KPI)') }}</option>
                    </select>
                </div>
                <div id="goal-custom-target-row" style="display: none; grid-template-columns: 1fr 1fr; gap: 10px;">
                    <div>
                        <label style="display: block; font-size: 11px; font-weight: 700; color: var(--ula-text-secondary); margin-bottom: 4px;">{{ __('Target Value') }}</label>
                        <input type="number" step="0.1" name="target_value" value="100" class="form-input">
                    </div>
                    <div>
                        <label style="display: block; font-size: 11px; font-weight: 700; color: var(--ula-text-secondary); margin-bottom: 4px;">{{ __('Unit') }}</label>
                        <input type="text" name="unit" placeholder="e.g. Users, USD, Points" class="form-input">
                    </div>
                </div>
                <div>
                    <label style="display: block; font-size: 12px; font-weight: 700; color: var(--ula-text-secondary); margin-bottom: 6px;">{{ __('Description') }}</label>
                    <textarea name="description" rows="2" placeholder="Key outcomes and deliverable expectations..." class="form-input" style="resize: vertical;"></textarea>
                </div>
                <div>
                    <label style="display: block; font-size: 12px; font-weight: 700; color: var(--ula-text-secondary); margin-bottom: 6px;">{{ __('Target Date') }}</label>
                    <input type="date" name="due_date" class="form-input">
                </div>
                <button type="submit" class="tactile-btn btn-primary" style="margin-top: 8px; padding: 12px; font-size: 14px;">
                    <span class="material-symbols-rounded" style="font-size: 1em; vertical-align: text-bottom;">rocket_launch</span> {{ __('Set Strategic Goal') }}
                </button>
            </form>
        </div>
    </div>

    <!-- Modal: New Milestone -->
    <div id="new-milestone-modal" class="modal-overlay">
        <div class="modal-card" style="max-width: 480px;">
            <div class="modal-header">
                <h3 style="font-size: 18px; font-weight: 900; color: var(--ula-text-primary);"><span class="material-symbols-rounded" style="font-size: 1em; vertical-align: text-bottom;">flag</span> {{ __('Create Project Milestone / Phase') }}</h3>
                <button onclick="closeNewMilestoneModal()" class="modal-close"><span class="material-symbols-rounded" style="font-size: 1em; vertical-align: text-bottom;">close</span></button>
            </div>
            <form id="new-milestone-form" onsubmit="createProjectMilestoneSubmit(event)" style="display: flex; flex-direction: column; gap: 14px;">
                <div>
                    <label style="display: block; font-size: 12px; font-weight: 700; color: var(--ula-text-secondary); margin-bottom: 6px;">{{ __('Milestone Name / Phase Title') }} *</label>
                    <input type="text" name="name" required placeholder="{{ __('e.g. Phase 1: MVP Delivery & User Onboarding') }}" class="form-input">
                </div>
                <div>
                    <label style="display: block; font-size: 12px; font-weight: 700; color: var(--ula-text-secondary); margin-bottom: 6px;">{{ __('Target Delivery Date') }}</label>
                    <input type="date" name="due_date" class="form-input">
                </div>
                <button type="submit" class="tactile-btn btn-primary" style="margin-top: 8px; padding: 12px; font-size: 14px;">
                    <span class="material-symbols-rounded" style="font-size: 1em; vertical-align: text-bottom;">flag</span> {{ __('Create Milestone') }}
                </button>
            </form>
        </div>
    </div>

    <!-- <span class="material-symbols-rounded" style="font-size: 1em; vertical-align: text-bottom;">star</span> CLICKUP-PARITY 3D TASK CONTEXT MENU <span class="material-symbols-rounded" style="font-size: 1em; vertical-align: text-bottom;">star</span> -->
    <div id="task-context-menu" class="task-context-menu" onclick="event.stopPropagation();">
        <div class="ctx-quick-header">
            <button type="button" class="ctx-quick-btn" onclick="ctxActionCopyLink()" title="{{ __('Copy Task Link') }}">
                <span class="material-symbols-rounded" style="font-size: 1em; vertical-align: text-bottom;">link</span> {{ __('Link') }}
            </button>
            <button type="button" class="ctx-quick-btn" onclick="ctxActionCopyId()" title="{{ __('Copy Task ID') }}">
                # {{ __('ID') }}
            </button>
            <button type="button" class="ctx-quick-btn" onclick="ctxActionOpenNewTab()" title="{{ __('Open in New Tab') }}">
                ↗ {{ __('Tab') }}
            </button>
        </div>

        <a href="javascript:void(0)" class="ctx-item" onclick="ctxActionInspect()">
            <span><span class="ctx-icon"><span class="material-symbols-rounded" style="font-size: 1em; vertical-align: text-bottom;">search</span></span>{{ __('Inspect & Edit') }}</span>
            <span style="font-size: 10px; color: var(--ula-text-muted); font-family: monospace;">↵</span>
        </a>

        <a href="javascript:void(0)" class="ctx-item" onclick="ctxActionStartTimer()">
            <span><span class="ctx-icon">⏱️</span>{{ __('Start Timer') }}</span>
            <span class="badge-pill badge-green" style="font-size: 9px;">▶ Live</span>
        </a>

        <a href="javascript:void(0)" class="ctx-item" onclick="ctxActionDuplicate()">
            <span><span class="ctx-icon"><span class="material-symbols-rounded" style="font-size: 1em; vertical-align: text-bottom;">assignment</span></span>{{ __('Duplicate Task') }}</span>
        </a>

        <a href="javascript:void(0)" class="ctx-item" onclick="ctxActionOpenMoveModal()">
            <span><span class="ctx-icon"><span class="material-symbols-rounded" style="font-size: 1em; vertical-align: text-bottom;">arrow_forward</span></span>{{ __('Move to Project') }}</span>
            <span style="font-size: 11px; color: var(--ula-text-muted);">›</span>
        </a>

        <div class="ctx-divider"></div>

        <a href="javascript:void(0)" class="ctx-item" onclick="ctxActionInspectCustomFields()">
            <span><span class="ctx-icon"><span class="material-symbols-rounded" style="font-size: 1em; vertical-align: text-bottom;">label</span></span>{{ __('Custom Fields') }}</span>
        </a>

        <a href="javascript:void(0)" class="ctx-item" onclick="ctxActionInspectDependencies()">
            <span><span class="ctx-icon"><span class="material-symbols-rounded" style="font-size: 1em; vertical-align: text-bottom;">link</span></span>{{ __('Dependencies') }}</span>
        </a>

        <div class="ctx-divider"></div>

        <a href="javascript:void(0)" class="ctx-item danger" onclick="ctxActionDelete()">
            <span><span class="ctx-icon"><span class="material-symbols-rounded" style="font-size: 1em; vertical-align: text-bottom;">delete</span></span>{{ __('Delete Task') }}</span>
            <span style="font-size: 10px; color: var(--ula-status-danger); font-family: monospace;">Del</span>
        </a>
    </div>

    <!-- Move Task Modal -->
    <div id="move-task-modal" class="modal-overlay">
        <div class="modal-card" style="max-width: 420px;">
            <div class="modal-header">
                <h3 style="font-size: 16px; font-weight: 900; color: var(--ula-text-primary);"><span class="material-symbols-rounded" style="font-size: 1em; vertical-align: text-bottom;">arrow_forward</span> {{ __('Move Task to Project') }}</h3>
                <button type="button" onclick="closeMoveTaskModal()" class="modal-close"><span class="material-symbols-rounded" style="font-size: 1em; vertical-align: text-bottom;">close</span></button>
            </div>
            <form onsubmit="submitMoveTask(event)" style="display: flex; flex-direction: column; gap: 14px; margin-top: 8px;">
                <input type="hidden" id="move-task-id-input">
                <div>
                    <label style="display: block; font-size: 11px; font-weight: 800; color: var(--ula-text-secondary); margin-bottom: 6px; text-transform: uppercase;">
                        <span class="material-symbols-rounded" style="font-size: 1em; vertical-align: text-bottom;">folder</span> {{ __('Target Project') }}
                    </label>
                    <select id="move-target-project-select" required class="form-input">
                        @foreach($allProjects as $p)
                            <option value="{{ $p->id }}" {{ $p->id === $project->id ? 'selected' : '' }}>{{ $p->name }} ({{ $p->code }})</option>
                        @endforeach
                    </select>
                </div>
                <div style="display: flex; justify-content: flex-end; gap: 10px; margin-top: 8px;">
                    <button type="button" onclick="closeMoveTaskModal()" class="tactile-btn btn-secondary" style="padding: 8px 16px; font-size: 12px;">{{ __('Cancel') }}</button>
                    <button type="submit" class="tactile-btn btn-primary" style="padding: 8px 18px; font-size: 12px;"><span class="material-symbols-rounded" style="font-size: 1em; vertical-align: text-bottom;">arrow_forward</span> {{ __('Move Task') }}</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Toast Notifications Container -->
    <div id="hub-toast-container"></div>

