    <!-- Modal: New Task -->
    <div id="new-task-modal" class="modal-overlay">
        <div class="modal-card">
            <div class="modal-header">
                <h3 style="font-size: 18px; font-weight: 900; color: var(--text-primary);">📝 {{ __('Create Task in') }} {{ $project->name }}</h3>
                <button onclick="closeNewTaskModal()" class="modal-close">✕</button>
            </div>
            <form id="new-task-form" onsubmit="createProjectTaskSubmit(event)" style="display: flex; flex-direction: column; gap: 14px;">
                <input type="hidden" name="project_id" value="{{ $project->id }}">
                <div>
                    <label style="display: block; font-size: 12px; font-weight: 700; color: var(--text-secondary); margin-bottom: 6px;">{{ __('Task Title') }} *</label>
                    <input type="text" name="title" required placeholder="e.g. Implement payment gateway webhook" class="form-input">
                </div>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                    <div>
                        <label style="display: block; font-size: 12px; font-weight: 700; color: var(--text-secondary); margin-bottom: 6px;">{{ __('Assignee') }}</label>
                        <select name="assignee_id" class="form-input">
                            <option value="">— {{ __('Unassigned') }} —</option>
                            @foreach($allMembers as $m)
                                <option value="{{ $m->user_id }}">{{ $m->user->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label style="display: block; font-size: 12px; font-weight: 700; color: var(--text-secondary); margin-bottom: 6px;">{{ __('Priority') }}</label>
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
                        <label style="display: block; font-size: 12px; font-weight: 700; color: var(--text-secondary); margin-bottom: 6px;">{{ __('Estimated Hours') }}</label>
                        <input type="number" step="0.5" name="estimated_hours" placeholder="4.0" class="form-input">
                    </div>
                    <div>
                        <label style="display: block; font-size: 12px; font-weight: 700; color: var(--text-secondary); margin-bottom: 6px;">{{ __('Due Date') }}</label>
                        <input type="date" name="due_date" class="form-input">
                    </div>
                </div>
                <div>
                    <label style="display: block; font-size: 12px; font-weight: 700; color: var(--text-secondary); margin-bottom: 6px;">🚩 {{ __('Project Milestone / Phase') }}</label>
                    <select name="milestone_id" class="form-input">
                        <option value="">— {{ __('No Milestone (General Task)') }} —</option>
                        @foreach($project->milestones as $pms)
                            <option value="{{ $pms->id }}">🚩 {{ $pms->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label style="display: block; font-size: 12px; font-weight: 700; color: var(--text-secondary); margin-bottom: 6px;">{{ __('Description / Specifications') }}</label>
                    <textarea name="description" rows="3" placeholder="Task requirements..." class="form-input" style="resize: vertical;"></textarea>
                </div>
                <button type="submit" class="tactile-btn btn-primary" style="margin-top: 8px; padding: 12px; font-size: 14px;">
                    💾 {{ __('Create Task') }}
                </button>
            </form>
        </div>
    </div>

    <!-- Modal: Log Time -->
    <div id="manual-time-modal" class="modal-overlay">
        <div class="modal-card">
            <div class="modal-header">
                <h3 style="font-size: 18px; font-weight: 900; color: var(--text-primary);">✍️ {{ __('Log Manual Time Entry') }}</h3>
                <button onclick="closeManualTimeModal()" class="modal-close">✕</button>
            </div>
            <form id="manual-time-form" onsubmit="logProjectTimeSubmit(event)" style="display: flex; flex-direction: column; gap: 14px;">
                <input type="hidden" name="project_id" value="{{ $project->id }}">
                <div>
                    <label style="display: block; font-size: 12px; font-weight: 700; color: var(--text-secondary); margin-bottom: 6px;">{{ __('Associated Task') }}</label>
                    <select name="task_id" class="form-input">
                        <option value="">— {{ __('General Project Work') }} —</option>
                        @foreach($tasks as $t)
                            <option value="{{ $t->id }}">#{{ $t->task_number }} {{ $t->title }}</option>
                        @endforeach
                    </select>
                </div>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                    <div>
                        <label style="display: block; font-size: 12px; font-weight: 700; color: var(--text-secondary); margin-bottom: 6px;">{{ __('Start Time') }} *</label>
                        <input type="datetime-local" name="started_at" required class="form-input">
                    </div>
                    <div>
                        <label style="display: block; font-size: 12px; font-weight: 700; color: var(--text-secondary); margin-bottom: 6px;">{{ __('End Time') }} *</label>
                        <input type="datetime-local" name="ended_at" required class="form-input">
                    </div>
                </div>
                <div>
                    <label style="display: block; font-size: 12px; font-weight: 700; color: var(--text-secondary); margin-bottom: 6px;">{{ __('Work Description') }}</label>
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
                <h3 style="font-size: 18px; font-weight: 900; color: var(--text-primary);">📅 {{ __('Schedule Project Meeting') }}</h3>
                <button onclick="closeScheduleProjectMeetingModal()" class="modal-close">✕</button>
            </div>
            <form id="hub-schedule-meeting-form" onsubmit="scheduleProjectMeetingSubmit(event)" method="POST" action="{{ route('meetings.schedule') }}" style="display: flex; flex-direction: column; gap: 14px;">
                @csrf
                <input type="hidden" name="scope" value="project">
                <input type="hidden" name="project_id" value="{{ $project->id }}">

                <div>
                    <label style="display: block; font-size: 12px; font-weight: 700; color: var(--text-secondary); margin-bottom: 6px;">{{ __('Meeting Title') }} *</label>
                    <input type="text" name="title" required value="{{ $project->name }} Sync" class="form-input">
                </div>

                <div>
                    <label style="display: block; font-size: 12px; font-weight: 700; color: var(--text-secondary); margin-bottom: 6px;">{{ __('Agenda / Notes') }}</label>
                    <textarea name="description" rows="2" placeholder="Topics to cover..." class="form-input" style="resize: vertical;"></textarea>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                    <div>
                        <label style="display: block; font-size: 12px; font-weight: 700; color: var(--text-secondary); margin-bottom: 6px;">{{ __('Meeting Room') }}</label>
                        <select name="room_id" class="form-input">
                            @foreach($rooms as $r)
                                <option value="{{ $r->id }}">🚪 {{ $r->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label style="display: block; font-size: 12px; font-weight: 700; color: var(--text-secondary); margin-bottom: 6px;">{{ __('Duration') }}</label>
                        <select name="duration_minutes" class="form-input">
                            <option value="15">15 {{ __('Minutes') }}</option>
                            <option value="30" selected>30 {{ __('Minutes') }}</option>
                            <option value="45">45 {{ __('Minutes') }}</option>
                            <option value="60">1 {{ __('Hour') }}</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label style="display: block; font-size: 12px; font-weight: 700; color: var(--text-secondary); margin-bottom: 6px;">{{ __('Scheduled Date & Time') }} *</label>
                    <input type="datetime-local" name="scheduled_at" id="hub-meeting-time-input" required class="form-input">
                </div>

                <!-- Attendee selection -->
                <div>
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 6px;">
                        <label style="font-size: 12px; font-weight: 700; color: var(--text-secondary);">
                            👥 {{ __('Select Project Members to Attend') }}
                        </label>
                        <button type="button" onclick="toggleAllHubProjectAttendees()" style="background: none; border: none; font-size: 11px; font-weight: 800; color: var(--brand-forest); cursor: pointer;">
                            ✓ {{ __('Select / Unselect All') }}
                        </button>
                    </div>
                    <div style="max-height: 120px; overflow-y: auto; background: var(--bg-surface-subtle); border: 1px solid var(--border-color); border-radius: 8px; padding: 6px 10px; display: flex; flex-direction: column; gap: 4px;">
                        @foreach($allMembers as $pm)
                            @if($pm->user_id !== $user->id)
                                <label style="display: flex; align-items: center; justify-content: space-between; font-size: 11px; color: var(--text-primary); cursor: pointer; padding: 3px 4px; border-radius: 4px;">
                                    <span style="display: flex; align-items: center; gap: 6px;">
                                        <input type="checkbox" name="attendee_ids[]" value="{{ $pm->user_id }}" checked class="hub-proj-attendee-chk" style="accent-color: var(--brand-forest);">
                                        <strong>{{ $pm->user->name }}</strong>
                                        <span style="color: var(--text-muted);">({{ $pm->user->email }})</span>
                                    </span>
                                    <span class="badge-pill badge-neutral" style="font-size: 9px;">{{ $pm->role->name ?? 'Team' }}</span>
                                </label>
                            @endif
                        @endforeach
                    </div>
                </div>

                <button type="submit" id="hub-schedule-meeting-btn" class="tactile-btn btn-primary" style="margin-top: 6px; padding: 12px; font-size: 14px;">
                    🚀 {{ __('Schedule Meeting & Email Team') }}
                </button>
            </form>
        </div>
    </div>

    <!-- Modal: Task Inspector & Activity Drawer -->
    <div id="task-details-modal" class="modal-overlay">
        <div class="modal-card" style="max-width: 850px; width: 95vw; max-height: 90vh; display: flex; flex-direction: column; padding: 22px; overflow: hidden; border-radius: 20px;">
            <div class="modal-header" style="padding-bottom: 12px; margin-bottom: 12px; border-bottom: 1px solid var(--border-color);">
                <div>
                    <div style="display: flex; align-items: center; gap: 8px;">
                        <span id="task-modal-code" class="badge-pill badge-neutral" style="font-family: monospace; font-size: 11px; font-weight: 900;">#1</span>
                        <span id="task-modal-priority-badge" class="badge-pill badge-gold" style="font-size: 10px;">⚡ Normal</span>
                    </div>
                    <h2 id="task-modal-title" style="font-size: 18px; font-weight: 900; color: var(--text-primary); margin-top: 4px;">Task Title</h2>
                </div>
                <button onclick="closeTaskInspector()" class="modal-close">✕</button>
            </div>

            <!-- Quick Status Change, Milestone, & Timer Action -->
            <div style="display: flex; flex-direction: column; gap: 10px; margin-bottom: 12px;">
                <div style="display: flex; justify-content: space-between; align-items: center; background: var(--bg-surface-subtle); padding: 8px 12px; border-radius: var(--radius-md); border: 1px solid var(--border-color); flex-wrap: wrap; gap: 8px;">
                    <div style="display: flex; align-items: center; gap: 8px;">
                        <span style="font-size: 12px; font-weight: 800; color: var(--text-secondary);">⚡ {{ __('Status') }}:</span>
                        <select id="task-modal-status-select" onchange="updateCurrentTaskStatus(this.value)" class="form-input" style="padding: 4px 8px; font-size: 12px; width: auto; font-weight: 800;">
                            <option value="backlog">📌 {{ __('Backlog') }}</option>
                            <option value="ready">🎯 {{ __('Ready') }}</option>
                            <option value="in_progress">⚡ {{ __('In Progress') }}</option>
                            <option value="review">🔍 {{ __('Review / QA') }}</option>
                            <option value="done">🎉 {{ __('Done') }}</option>
                        </select>
                    </div>

                    <div style="display: flex; align-items: center; gap: 8px;">
                        <span style="font-size: 12px; font-weight: 800; color: var(--text-secondary);">🚩 {{ __('Milestone') }}:</span>
                        <select id="task-modal-milestone-select" onchange="updateCurrentTaskMilestone(this.value)" class="form-input" style="padding: 4px 8px; font-size: 12px; width: auto; font-weight: 700;">
                            <option value="">— {{ __('No Milestone') }} —</option>
                            @foreach($project->milestones as $pms)
                                <option value="{{ $pms->id }}">🚩 {{ $pms->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div style="display: flex; align-items: center; gap: 8px;">
                        <span id="task-modal-hours-pill" style="font-family: monospace; font-size: 11px; font-weight: 800; color: var(--brand-forest);">0h / 0h</span>
                        <button id="task-modal-timer-btn" onclick="toggleTaskTimerAction()" class="tactile-btn btn-secondary" style="padding: 5px 12px; font-size: 11px;">
                            ⏱️ {{ __('Start Timer') }}
                        </button>
                    </div>
                </div>

                <!-- Approval Banner -->
                <div id="task-modal-hub-approval-banner" style="display: none; padding: 10px 14px; border-radius: var(--radius-md); font-size: 12px; font-weight: 700; justify-content: space-between; align-items: center; gap: 12px; flex-wrap: wrap;">
                    <div id="task-modal-hub-approval-text" style="display: flex; align-items: center; gap: 8px;"></div>
                    <div id="task-modal-hub-approval-actions" style="display: flex; gap: 8px;"></div>
                </div>
            </div>

            <!-- Inspector Tab Navigation -->
            <div style="display: flex; gap: 4px; border-bottom: 1px solid var(--border-color); margin-bottom: 14px;">
                <button type="button" onclick="switchInspectorTab('overview')" id="task-tab-btn-overview" class="task-inspector-tab-btn active">
                    📋 {{ __('Overview & Checklist') }}
                </button>
                <button type="button" onclick="switchInspectorTab('discussion')" id="task-tab-btn-discussion" class="task-inspector-tab-btn">
                    💬 {{ __('Discussions') }} (<span id="task-modal-comments-badge">0</span>)
                </button>
                <button type="button" onclick="switchInspectorTab('files')" id="task-tab-btn-files" class="task-inspector-tab-btn">
                    📎 {{ __('Files') }} (<span id="task-hub-attachments-count">0</span>)
                </button>
                <button type="button" onclick="switchInspectorTab('activity')" id="task-tab-btn-activity" class="task-inspector-tab-btn">
                    📜 {{ __('Activity History') }} (<span id="task-modal-activity-badge">0</span>)
                </button>
            </div>

            <!-- Scrollable Content Area -->
            <div style="flex: 1; overflow-y: auto; padding-right: 4px;">
                <!-- Tab 1: Overview & Checklist -->
                <div id="task-tab-content-overview" class="task-inspector-tab-pane" style="display: block;">
                    <!-- Description -->
                    <div style="margin-bottom: 14px;">
                        <label style="display: block; font-size: 11px; font-weight: 800; color: var(--text-muted); text-transform: uppercase; margin-bottom: 4px;">{{ __('Description') }}</label>
                        <div id="task-modal-description" style="background: var(--bg-surface-subtle); padding: 12px; border-radius: var(--radius-md); font-size: 13px; color: var(--text-primary); border: 1px solid var(--border-color); line-height: 1.5; white-space: pre-wrap;">
                            —
                        </div>
                    </div>

                    <!-- Checklist -->
                    <div style="margin-bottom: 14px;">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                            <label style="font-size: 11px; font-weight: 800; color: var(--text-muted); text-transform: uppercase;">{{ __('Checklist Sub-items') }}</label>
                        </div>
                        <div id="task-checklist-container" style="display: flex; flex-direction: column; gap: 6px; margin-bottom: 10px;"></div>
                        <form onsubmit="addTaskChecklistItem(event)" style="display: flex; gap: 8px;">
                            <input type="text" id="new-checklist-item-input" required placeholder="{{ __('Add sub-item...') }}" class="form-input" style="font-size: 12px; padding: 7px 10px;">
                            <button type="submit" class="tactile-btn btn-secondary" style="padding: 7px 12px; font-size: 11px;">+ {{ __('Add') }}</button>
                        </form>
                    </div>
                </div>

                <!-- Tab 2: Discussions & Comments -->
                <div id="task-tab-content-discussion" class="task-inspector-tab-pane" style="display: none;">
                    <div id="task-comments-feed" style="max-height: 240px; overflow-y: auto; display: flex; flex-direction: column; gap: 8px; margin-bottom: 12px;"></div>
                    
                    <!-- Mention chips -->
                    <div style="display: flex; align-items: center; gap: 6px; margin-bottom: 8px; flex-wrap: wrap;">
                        <span style="font-size: 10px; font-weight: 800; color: var(--text-muted);">@ {{ __('Mention') }}:</span>
                        @foreach($allMembers->take(6) as $chipMember)
                            @if($chipMember->user_id !== $user->id)
                                <button type="button" onclick="insertHubMentionHandle('{{ $chipMember->user->name }}')" class="badge-pill" style="cursor: pointer; font-size: 10px; border: 1px solid var(--border-color); background: var(--bg-surface-subtle); color: var(--brand-forest);" title="{{ __('Mention :name', ['name' => $chipMember->user->name]) }}">
                                    @<span>{{ $chipMember->user->name }}</span>
                                </button>
                            @endif
                        @endforeach
                    </div>

                    <form onsubmit="addTaskComment(event)" style="display: flex; gap: 8px;">
                        <input type="text" id="new-comment-input" required placeholder="{{ __('Write a comment... Type @name to mention') }}" class="form-input" style="font-size: 12px; padding: 7px 10px;">
                        <button type="submit" class="tactile-btn btn-primary" style="padding: 7px 14px; font-size: 11px;">{{ __('Post') }}</button>
                    </form>
                </div>

                <!-- Tab 3: Attachments & Files -->
                <div id="task-tab-content-files" class="task-inspector-tab-pane" style="display: none;">
                    <form onsubmit="uploadHubTaskAttachmentSubmit(event)" style="background: var(--bg-surface-subtle); border: 1px dashed var(--border-color); border-radius: var(--radius-md); padding: 12px; text-align: center; margin-bottom: 12px; display: flex; align-items: center; justify-content: center; gap: 10px; flex-wrap: wrap;">
                        <input type="file" id="hub-task-file-input" required class="form-input" style="font-size: 11px; max-width: 260px;">
                        <button type="submit" class="tactile-btn btn-primary" style="padding: 6px 12px; font-size: 11px;">📤 {{ __('Upload') }}</button>
                    </form>
                    <div id="task-hub-attachments-container" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(180px, 1fr)); gap: 8px;"></div>
                </div>

                <!-- Tab 4: Activity & Audit History (Harnessing AuditLog) -->
                <div id="task-tab-content-activity" class="task-inspector-tab-pane" style="display: none;">
                    <div style="margin-bottom: 10px; display: flex; justify-content: space-between; align-items: center;">
                        <span style="font-size: 11px; font-weight: 800; color: var(--text-muted); text-transform: uppercase;">
                            📜 {{ __('Chronological Audit Trail & Mutation History') }}
                        </span>
                        <span style="font-size: 10px; color: var(--brand-forest); font-weight: 700;">
                            🔒 {{ __('Tamper-proof Logged') }}
                        </span>
                    </div>
                    <div id="task-activity-timeline-feed" style="display: flex; flex-direction: column; gap: 10px; padding: 4px;">
                        <!-- Injected via JavaScript from AuditLog -->
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal: New Project Document (ClickUp Docs) -->
    <div id="new-doc-modal" class="modal-overlay">
        <div class="modal-card">
            <div class="modal-header">
                <h3 style="font-size: 18px; font-weight: 900; color: var(--text-primary);">📚 {{ __('Create Project Document / Wiki') }}</h3>
                <button onclick="closeNewDocModal()" class="modal-close">✕</button>
            </div>
            <form id="new-doc-form" onsubmit="createProjectDocSubmit(event)" style="display: flex; flex-direction: column; gap: 14px;">
                <div style="display: grid; grid-template-columns: 60px 1fr; gap: 10px;">
                    <div>
                        <label style="display: block; font-size: 12px; font-weight: 700; color: var(--text-secondary); margin-bottom: 6px;">{{ __('Icon') }}</label>
                        <input type="text" name="icon" value="📄" class="form-input" style="text-align: center; font-size: 16px;">
                    </div>
                    <div>
                        <label style="display: block; font-size: 12px; font-weight: 700; color: var(--text-secondary); margin-bottom: 6px;">{{ __('Document Title') }} *</label>
                        <input type="text" name="title" required placeholder="e.g. Technical Specification & API Contracts" class="form-input">
                    </div>
                </div>
                <div>
                    <label style="display: block; font-size: 12px; font-weight: 700; color: var(--text-secondary); margin-bottom: 6px;">{{ __('Markdown Content / Specification') }}</label>
                    <textarea name="content" rows="6" placeholder="# Overview&#10;&#10;Write project documentation, meeting minutes, and architectural decisions here..." class="form-input" style="resize: vertical; font-family: monospace;"></textarea>
                </div>
                <div style="display: flex; align-items: center; gap: 8px;">
                    <input type="checkbox" name="is_pinned" id="doc_pinned" style="accent-color: var(--brand-forest);">
                    <label for="doc_pinned" style="font-size: 12px; font-weight: 700; color: var(--text-primary); cursor: pointer;">📌 {{ __('Pin to top of knowledge wiki') }}</label>
                </div>
                <button type="submit" class="tactile-btn btn-primary" style="margin-top: 8px; padding: 12px; font-size: 14px;">
                    💾 {{ __('Publish Document') }}
                </button>
            </form>
        </div>
    </div>

    <!-- Modal: New Strategic Goal (ClickUp Goals) -->
    <div id="new-goal-modal" class="modal-overlay">
        <div class="modal-card">
            <div class="modal-header">
                <h3 style="font-size: 18px; font-weight: 900; color: var(--text-primary);">🎯 {{ __('Create Strategic Project Goal') }}</h3>
                <button onclick="closeNewGoalModal()" class="modal-close">✕</button>
            </div>
            <form id="new-goal-form" onsubmit="createProjectGoalSubmit(event)" style="display: flex; flex-direction: column; gap: 14px;">
                <div>
                    <label style="display: block; font-size: 12px; font-weight: 700; color: var(--text-secondary); margin-bottom: 6px;">{{ __('Goal Name') }} *</label>
                    <input type="text" name="name" required placeholder="e.g. Beta Launch & 100 User Onboarding" class="form-input">
                </div>
                <div>
                    <label style="display: block; font-size: 12px; font-weight: 700; color: var(--text-secondary); margin-bottom: 6px;">⚡ {{ __('Key Metric & Auto-Tracking Engine') }} *</label>
                    <select name="target_type" id="goal-target-type-select" onchange="toggleGoalMetricFields(this.value)" class="form-input" style="font-weight: 700;">
                        <option value="tasks">⚡ {{ __('Tasks Completion (Auto-calculated from done tasks)') }}</option>
                        <option value="milestones">🚩 {{ __('Milestones Delivery (Auto-calculated from completed phases)') }}</option>
                        <option value="hours">⏱️ {{ __('Hours Budget (Auto-calculated from logged timers)') }}</option>
                        <option value="number">🎯 {{ __('Custom Numeric Target (Manual KPI)') }}</option>
                    </select>
                </div>
                <div id="goal-custom-target-row" style="display: none; grid-template-columns: 1fr 1fr; gap: 10px;">
                    <div>
                        <label style="display: block; font-size: 11px; font-weight: 700; color: var(--text-secondary); margin-bottom: 4px;">{{ __('Target Value') }}</label>
                        <input type="number" step="0.1" name="target_value" value="100" class="form-input">
                    </div>
                    <div>
                        <label style="display: block; font-size: 11px; font-weight: 700; color: var(--text-secondary); margin-bottom: 4px;">{{ __('Unit') }}</label>
                        <input type="text" name="unit" placeholder="e.g. Users, USD, Points" class="form-input">
                    </div>
                </div>
                <div>
                    <label style="display: block; font-size: 12px; font-weight: 700; color: var(--text-secondary); margin-bottom: 6px;">{{ __('Description') }}</label>
                    <textarea name="description" rows="2" placeholder="Key outcomes and deliverable expectations..." class="form-input" style="resize: vertical;"></textarea>
                </div>
                <div>
                    <label style="display: block; font-size: 12px; font-weight: 700; color: var(--text-secondary); margin-bottom: 6px;">{{ __('Target Date') }}</label>
                    <input type="date" name="due_date" class="form-input">
                </div>
                <button type="submit" class="tactile-btn btn-primary" style="margin-top: 8px; padding: 12px; font-size: 14px;">
                    🚀 {{ __('Set Strategic Goal') }}
                </button>
            </form>
        </div>
    </div>

    <!-- Modal: New Milestone -->
    <div id="new-milestone-modal" class="modal-overlay">
        <div class="modal-card" style="max-width: 480px;">
            <div class="modal-header">
                <h3 style="font-size: 18px; font-weight: 900; color: var(--text-primary);">🚩 {{ __('Create Project Milestone / Phase') }}</h3>
                <button onclick="closeNewMilestoneModal()" class="modal-close">✕</button>
            </div>
            <form id="new-milestone-form" onsubmit="createProjectMilestoneSubmit(event)" style="display: flex; flex-direction: column; gap: 14px;">
                <div>
                    <label style="display: block; font-size: 12px; font-weight: 700; color: var(--text-secondary); margin-bottom: 6px;">{{ __('Milestone Name / Phase Title') }} *</label>
                    <input type="text" name="name" required placeholder="{{ __('e.g. Phase 1: MVP Delivery & User Onboarding') }}" class="form-input">
                </div>
                <div>
                    <label style="display: block; font-size: 12px; font-weight: 700; color: var(--text-secondary); margin-bottom: 6px;">{{ __('Target Delivery Date') }}</label>
                    <input type="date" name="due_date" class="form-input">
                </div>
                <button type="submit" class="tactile-btn btn-primary" style="margin-top: 8px; padding: 12px; font-size: 14px;">
                    🚩 {{ __('Create Milestone') }}
                </button>
            </form>
        </div>
    </div>

    <!-- 🌟 CLICKUP-PARITY 3D TASK CONTEXT MENU 🌟 -->
    <div id="task-context-menu" class="task-context-menu" onclick="event.stopPropagation();">
        <div class="ctx-quick-header">
            <button type="button" class="ctx-quick-btn" onclick="ctxActionCopyLink()" title="{{ __('Copy Task Link') }}">
                🔗 {{ __('Link') }}
            </button>
            <button type="button" class="ctx-quick-btn" onclick="ctxActionCopyId()" title="{{ __('Copy Task ID') }}">
                # {{ __('ID') }}
            </button>
            <button type="button" class="ctx-quick-btn" onclick="ctxActionOpenNewTab()" title="{{ __('Open in New Tab') }}">
                ↗ {{ __('Tab') }}
            </button>
        </div>

        <a href="javascript:void(0)" class="ctx-item" onclick="ctxActionInspect()">
            <span><span class="ctx-icon">🔍</span>{{ __('Inspect & Edit') }}</span>
            <span style="font-size: 10px; color: var(--text-muted); font-family: monospace;">↵</span>
        </a>

        <a href="javascript:void(0)" class="ctx-item" onclick="ctxActionStartTimer()">
            <span><span class="ctx-icon">⏱️</span>{{ __('Start Timer') }}</span>
            <span class="badge-pill badge-green" style="font-size: 9px;">▶ Live</span>
        </a>

        <a href="javascript:void(0)" class="ctx-item" onclick="ctxActionDuplicate()">
            <span><span class="ctx-icon">📋</span>{{ __('Duplicate Task') }}</span>
        </a>

        <a href="javascript:void(0)" class="ctx-item" onclick="ctxActionOpenMoveModal()">
            <span><span class="ctx-icon">➡️</span>{{ __('Move to Project') }}</span>
            <span style="font-size: 11px; color: var(--text-muted);">›</span>
        </a>

        <div class="ctx-divider"></div>

        <a href="javascript:void(0)" class="ctx-item" onclick="ctxActionInspectCustomFields()">
            <span><span class="ctx-icon">🏷️</span>{{ __('Custom Fields') }}</span>
        </a>

        <a href="javascript:void(0)" class="ctx-item" onclick="ctxActionInspectDependencies()">
            <span><span class="ctx-icon">🔗</span>{{ __('Dependencies') }}</span>
        </a>

        <div class="ctx-divider"></div>

        <a href="javascript:void(0)" class="ctx-item danger" onclick="ctxActionDelete()">
            <span><span class="ctx-icon">🗑️</span>{{ __('Delete Task') }}</span>
            <span style="font-size: 10px; color: #D96B5F; font-family: monospace;">Del</span>
        </a>
    </div>

    <!-- Move Task Modal -->
    <div id="move-task-modal" class="modal-overlay">
        <div class="modal-card" style="max-width: 420px;">
            <div class="modal-header">
                <h3 style="font-size: 16px; font-weight: 900; color: var(--text-primary);">➡️ {{ __('Move Task to Project') }}</h3>
                <button type="button" onclick="closeMoveTaskModal()" class="modal-close">✕</button>
            </div>
            <form onsubmit="submitMoveTask(event)" style="display: flex; flex-direction: column; gap: 14px; margin-top: 8px;">
                <input type="hidden" id="move-task-id-input">
                <div>
                    <label style="display: block; font-size: 11px; font-weight: 800; color: var(--text-secondary); margin-bottom: 6px; text-transform: uppercase;">
                        📁 {{ __('Target Project') }}
                    </label>
                    <select id="move-target-project-select" required class="form-input">
                        @foreach($allProjects as $p)
                            <option value="{{ $p->id }}" {{ $p->id === $project->id ? 'selected' : '' }}>{{ $p->name }} ({{ $p->code }})</option>
                        @endforeach
                    </select>
                </div>
                <div style="display: flex; justify-content: flex-end; gap: 10px; margin-top: 8px;">
                    <button type="button" onclick="closeMoveTaskModal()" class="tactile-btn btn-secondary" style="padding: 8px 16px; font-size: 12px;">{{ __('Cancel') }}</button>
                    <button type="submit" class="tactile-btn btn-primary" style="padding: 8px 18px; font-size: 12px;">➡️ {{ __('Move Task') }}</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Toast Notifications Container -->
    <div id="hub-toast-container"></div>

