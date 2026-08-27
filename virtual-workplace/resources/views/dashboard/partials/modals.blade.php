    <!-- Modal: New Project -->
    <div id="new-project-modal" class="modal-overlay">
        <div class="modal-card" style="max-width: 540px; border-radius: 20px; padding: 24px;">
            <div class="modal-header" style="margin-bottom: 20px; display: flex; justify-content: space-between; align-items: center;">
                <h3 class="modal-title" style="font-size: 18px; font-weight: 900; color: var(--text-primary);">📁 {{ __('Create New Project') }}</h3>
                <button onclick="closeNewProjectModal()" class="modal-close" style="background: var(--bg-surface-subtle); border: 1px solid var(--border-color); width: 32px; height: 32px; border-radius: 50%; display: flex; align-items: center; justify-content: center; cursor: pointer; color: var(--text-primary); font-weight: 800;">✕</button>
            </div>
            <form id="new-project-form" onsubmit="createProjectSubmit(event)" style="display: flex; flex-direction: column; gap: 14px;">
                <div>
                    <label style="display: block; font-size: 12px; font-weight: 700; color: var(--text-secondary); margin-bottom: 6px;">{{ __('Project Name') }} *</label>
                    <input type="text" name="name" required placeholder="e.g. Mobile App Redesign, Cloud Migration" style="width: 100%; background: var(--bg-surface-subtle); border: 1px solid var(--border-color); border-radius: 10px; padding: 10px 14px; color: var(--text-primary); outline: none; font-size: 13px; font-weight: 600; box-shadow: var(--shadow-inset-3d);">
                </div>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                    <div>
                        <label style="display: block; font-size: 12px; font-weight: 700; color: var(--text-secondary); margin-bottom: 6px;">{{ __('Project Code') }}</label>
                        <input type="text" name="code" placeholder="e.g. MOB-01" style="width: 100%; background: var(--bg-surface-subtle); border: 1px solid var(--border-color); border-radius: 10px; padding: 10px 14px; color: var(--text-primary); outline: none; font-size: 13px; font-weight: 600; box-shadow: var(--shadow-inset-3d);">
                    </div>
                    <div>
                        <label style="display: block; font-size: 12px; font-weight: 700; color: var(--text-secondary); margin-bottom: 6px;">{{ __('Priority') }}</label>
                        <select name="priority" style="width: 100%; background: var(--bg-surface-subtle); border: 1px solid var(--border-color); border-radius: 10px; padding: 10px 14px; color: var(--text-primary); outline: none; font-size: 13px; font-weight: 600;">
                            <option value="medium">{{ __('Medium') }}</option>
                            <option value="low">{{ __('Low') }}</option>
                            <option value="high">{{ __('High') }}</option>
                            <option value="urgent">{{ __('Urgent') }}</option>
                        </select>
                    </div>
                </div>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                    <div>
                        <label style="display: block; font-size: 12px; font-weight: 700; color: var(--text-secondary); margin-bottom: 6px;">{{ __('Project Manager') }}</label>
                        <select name="manager_id" style="width: 100%; background: var(--bg-surface-subtle); border: 1px solid var(--border-color); border-radius: 10px; padding: 10px 14px; color: var(--text-primary); outline: none; font-size: 13px; font-weight: 600;">
                            <option value="">— {{ __('Select Manager') }} —</option>
                            @foreach($members as $m)
                                <option value="{{ $m->user_id }}">{{ $m->user->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label style="display: block; font-size: 12px; font-weight: 700; color: var(--text-secondary); margin-bottom: 6px;">{{ __('Department') }}</label>
                        <select name="department_id" style="width: 100%; background: var(--bg-surface-subtle); border: 1px solid var(--border-color); border-radius: 10px; padding: 10px 14px; color: var(--text-primary); outline: none; font-size: 13px; font-weight: 600;">
                            <option value="">— {{ __('No Department') }} —</option>
                            @foreach($departments as $d)
                                <option value="{{ $d->id }}">{{ $d->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                    <div>
                        <label style="display: block; font-size: 12px; font-weight: 700; color: var(--text-secondary); margin-bottom: 6px;">{{ __('Budget ($)') }}</label>
                        <input type="number" step="0.01" name="budget_amount" placeholder="10000" style="width: 100%; background: var(--bg-surface-subtle); border: 1px solid var(--border-color); border-radius: 10px; padding: 10px 14px; color: var(--text-primary); outline: none; font-size: 13px; font-weight: 600; box-shadow: var(--shadow-inset-3d);">
                    </div>
                    <div>
                        <label style="display: block; font-size: 12px; font-weight: 700; color: var(--text-secondary); margin-bottom: 6px;">{{ __('Planned Hours') }}</label>
                        <input type="number" step="0.5" name="planned_hours" placeholder="160" style="width: 100%; background: var(--bg-surface-subtle); border: 1px solid var(--border-color); border-radius: 10px; padding: 10px 14px; color: var(--text-primary); outline: none; font-size: 13px; font-weight: 600; box-shadow: var(--shadow-inset-3d);">
                    </div>
                </div>
                <div>
                    <label style="display: block; font-size: 12px; font-weight: 700; color: var(--text-secondary); margin-bottom: 6px;">{{ __('Due Date') }}</label>
                    <input type="date" name="due_date" style="width: 100%; background: var(--bg-surface-subtle); border: 1px solid var(--border-color); border-radius: 10px; padding: 10px 14px; color: var(--text-primary); outline: none; font-size: 13px; font-weight: 600; box-shadow: var(--shadow-inset-3d);">
                </div>
                <div>
                    <label style="display: block; font-size: 12px; font-weight: 700; color: var(--text-secondary); margin-bottom: 6px;">{{ __('Description') }}</label>
                    <textarea name="description" rows="2" placeholder="Brief project summary..." style="width: 100%; background: var(--bg-surface-subtle); border: 1px solid var(--border-color); border-radius: 10px; padding: 10px 14px; color: var(--text-primary); outline: none; font-size: 13px; font-weight: 600; box-shadow: var(--shadow-inset-3d);"></textarea>
                </div>
                <button type="submit" class="tactile-btn btn-primary" style="margin-top: 8px; padding: 12px; font-size: 14px; justify-content: center; width: 100%;">
                    🚀 {{ __('Create Project') }}
                </button>
            </form>
        </div>
    </div>

    <!-- Modal: New Task -->
    <div id="new-task-modal" class="modal-overlay">
        <div class="modal-card" style="max-width: 540px; border-radius: 20px; padding: 24px;">
            <div class="modal-header" style="margin-bottom: 20px; display: flex; justify-content: space-between; align-items: center;">
                <h3 class="modal-title" style="font-size: 18px; font-weight: 900; color: var(--text-primary);">✅ {{ __('Create New Task') }}</h3>
                <button onclick="closeNewTaskModal()" class="modal-close" style="background: var(--bg-surface-subtle); border: 1px solid var(--border-color); width: 32px; height: 32px; border-radius: 50%; display: flex; align-items: center; justify-content: center; cursor: pointer; color: var(--text-primary); font-weight: 800;">✕</button>
            </div>
            <form id="new-task-form" onsubmit="createTaskSubmit(event)" style="display: flex; flex-direction: column; gap: 14px;">
                <div>
                    <label style="display: block; font-size: 12px; font-weight: 700; color: var(--text-secondary); margin-bottom: 6px;">{{ __('Project') }} *</label>
                    <select name="project_id" required style="width: 100%; background: var(--bg-surface-subtle); border: 1px solid var(--border-color); border-radius: 10px; padding: 10px 14px; color: var(--text-primary); outline: none; font-size: 13px; font-weight: 600;">
                        @foreach($projects as $p)
                            <option value="{{ $p->id }}">📁 {{ $p->name }} ({{ $p->code }})</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label style="display: block; font-size: 12px; font-weight: 700; color: var(--text-secondary); margin-bottom: 6px;">{{ __('Task Title') }} *</label>
                    <input type="text" name="title" required placeholder="e.g. Implement authentication middleware" style="width: 100%; background: var(--bg-surface-subtle); border: 1px solid var(--border-color); border-radius: 10px; padding: 10px 14px; color: var(--text-primary); outline: none; font-size: 13px; font-weight: 600; box-shadow: var(--shadow-inset-3d);">
                </div>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                    <div>
                        <label style="display: block; font-size: 12px; font-weight: 700; color: var(--text-secondary); margin-bottom: 6px;">{{ __('Assignee') }}</label>
                        <select name="assignee_id" style="width: 100%; background: var(--bg-surface-subtle); border: 1px solid var(--border-color); border-radius: 10px; padding: 10px 14px; color: var(--text-primary); outline: none; font-size: 13px; font-weight: 600;">
                            <option value="">— {{ __('Unassigned') }} —</option>
                            @foreach($members as $m)
                                <option value="{{ $m->user_id }}">{{ $m->user->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label style="display: block; font-size: 12px; font-weight: 700; color: var(--text-secondary); margin-bottom: 6px;">{{ __('Priority') }}</label>
                        <select name="priority" style="width: 100%; background: var(--bg-surface-subtle); border: 1px solid var(--border-color); border-radius: 10px; padding: 10px 14px; color: var(--text-primary); outline: none; font-size: 13px; font-weight: 600;">
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
                        <input type="number" step="0.5" name="estimated_hours" placeholder="4.0" style="width: 100%; background: var(--bg-surface-subtle); border: 1px solid var(--border-color); border-radius: 10px; padding: 10px 14px; color: var(--text-primary); outline: none; font-size: 13px; font-weight: 600; box-shadow: var(--shadow-inset-3d);">
                    </div>
                    <div>
                        <label style="display: block; font-size: 12px; font-weight: 700; color: var(--text-secondary); margin-bottom: 6px;">{{ __('Due Date') }}</label>
                        <input type="date" name="due_date" style="width: 100%; background: var(--bg-surface-subtle); border: 1px solid var(--border-color); border-radius: 10px; padding: 10px 14px; color: var(--text-primary); outline: none; font-size: 13px; font-weight: 600; box-shadow: var(--shadow-inset-3d);">
                    </div>
                </div>
                <button type="submit" class="tactile-btn btn-primary" style="margin-top: 8px; padding: 12px; font-size: 14px; justify-content: center; width: 100%;">
                    💾 {{ __('Create Task') }}
                </button>
            </form>
        </div>
    </div>

    <!-- Modal: Schedule Meeting (General or Project) -->
    <div id="schedule-meeting-modal" class="modal-overlay">
        <div class="modal-card" style="max-width: 600px; border-radius: 20px; padding: 24px; max-height: 90vh; overflow-y: auto;">
            <div class="modal-header" style="margin-bottom: 20px; display: flex; justify-content: space-between; align-items: center;">
                <h3 class="modal-title" style="font-size: 18px; font-weight: 900; color: var(--text-primary);">📅 {{ __('Schedule Meeting & Sync Attendees') }}</h3>
                <button onclick="closeScheduleMeetingModal()" class="modal-close" style="background: var(--bg-surface-subtle); border: 1px solid var(--border-color); width: 32px; height: 32px; border-radius: 50%; display: flex; align-items: center; justify-content: center; cursor: pointer; color: var(--text-primary); font-weight: 800;">✕</button>
            </div>

            <form id="schedule-meeting-form" onsubmit="scheduleMeetingSubmit(event)" method="POST" action="{{ route('meetings.schedule') }}" style="display: flex; flex-direction: column; gap: 14px;">
                @csrf

                <!-- Meeting Scope Switcher -->
                <div>
                    <label style="display: block; font-size: 12px; font-weight: 700; color: var(--text-secondary); margin-bottom: 6px;">{{ __('Meeting Scope') }} *</label>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px; background: var(--bg-surface-subtle); padding: 4px; border-radius: 12px; border: 1px solid var(--border-color);">
                        <label id="lbl-scope-general" style="display: flex; align-items: center; justify-content: center; gap: 6px; padding: 8px; border-radius: 8px; font-size: 12px; font-weight: 800; cursor: pointer; background: var(--bg-surface); color: var(--brand-forest); box-shadow: var(--shadow-soft-3d);">
                            <input type="radio" name="scope" value="general" checked onchange="toggleMeetingScope('general')" style="display: none;">
                            <span>🌐 {{ __('General Meeting') }}</span>
                        </label>
                        <label id="lbl-scope-project" style="display: flex; align-items: center; justify-content: center; gap: 6px; padding: 8px; border-radius: 8px; font-size: 12px; font-weight: 800; cursor: pointer; color: var(--text-secondary);">
                            <input type="radio" name="scope" value="project" onchange="toggleMeetingScope('project')" style="display: none;">
                            <span>📁 {{ __('Project Team Meeting') }}</span>
                        </label>
                    </div>
                </div>

                <!-- Project Selector (Shown when scope is project) -->
                <div id="meeting-project-field" style="display: none;">
                    <label style="display: block; font-size: 12px; font-weight: 700; color: var(--text-secondary); margin-bottom: 6px;">{{ __('Target Project') }} *</label>
                    <select name="project_id" id="meeting-project-select" onchange="renderProjectAttendeesList(this.value)" style="width: 100%; background: var(--bg-surface-subtle); border: 1px solid var(--border-color); border-radius: 10px; padding: 10px 14px; color: var(--text-primary); outline: none; font-size: 13px; font-weight: 600;">
                        <option value="">— {{ __('Select Project') }} —</option>
                        @foreach($projects as $p)
                            <option value="{{ $p->id }}">📁 {{ $p->name }} ({{ $p->code }})</option>
                        @endforeach
                    </select>
                    
                    <!-- Project Team Members Checklist (Only Project-related Members) -->
                    <div id="project-attendees-selection-box" style="margin-top: 10px; display: none;">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 6px;">
                            <label style="font-size: 12px; font-weight: 700; color: var(--text-secondary);">
                                👥 {{ __('Select Project Members to Attend') }}
                            </label>
                            <button type="button" onclick="toggleAllProjectAttendees()" style="background: none; border: none; font-size: 11px; font-weight: 800; color: var(--brand-forest); cursor: pointer;">
                                ✓ {{ __('Select / Unselect All') }}
                            </button>
                        </div>
                        <div id="project-attendees-list" style="max-height: 140px; overflow-y: auto; background: var(--bg-surface-subtle); border: 1px solid var(--border-color); border-radius: 10px; padding: 8px 12px; display: flex; flex-direction: column; gap: 6px;"></div>
                    </div>
                </div>

                <!-- Meeting Title -->
                <div>
                    <label style="display: block; font-size: 12px; font-weight: 700; color: var(--text-secondary); margin-bottom: 6px;">{{ __('Meeting Title') }} *</label>
                    <input type="text" name="title" required placeholder="e.g. Weekly Strategy Sync, Milestone Review" style="width: 100%; background: var(--bg-surface-subtle); border: 1px solid var(--border-color); border-radius: 10px; padding: 10px 14px; color: var(--text-primary); outline: none; font-size: 13px; font-weight: 600; box-shadow: var(--shadow-inset-3d);">
                </div>

                <!-- Meeting Description -->
                <div>
                    <label style="display: block; font-size: 12px; font-weight: 700; color: var(--text-secondary); margin-bottom: 6px;">{{ __('Description / Agenda') }}</label>
                    <textarea name="description" rows="2" placeholder="Brief outline of topics to discuss..." style="width: 100%; background: var(--bg-surface-subtle); border: 1px solid var(--border-color); border-radius: 10px; padding: 10px 14px; color: var(--text-primary); outline: none; font-size: 13px; font-weight: 500; resize: vertical; box-shadow: var(--shadow-inset-3d);"></textarea>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                    <!-- Room Selection -->
                    <div>
                        <label style="display: block; font-size: 12px; font-weight: 700; color: var(--text-secondary); margin-bottom: 6px;">{{ __('Meeting Room') }}</label>
                        <select name="room_id" style="width: 100%; background: var(--bg-surface-subtle); border: 1px solid var(--border-color); border-radius: 10px; padding: 10px 14px; color: var(--text-primary); outline: none; font-size: 13px; font-weight: 600;">
                            @foreach($rooms as $r)
                                <option value="{{ $r->id }}">🚪 {{ $r->name }} ({{ ucfirst($r->type) }})</option>
                            @endforeach
                        </select>
                    </div>
                    <!-- Duration -->
                    <div>
                        <label style="display: block; font-size: 12px; font-weight: 700; color: var(--text-secondary); margin-bottom: 6px;">{{ __('Duration') }}</label>
                        <select name="duration_minutes" style="width: 100%; background: var(--bg-surface-subtle); border: 1px solid var(--border-color); border-radius: 10px; padding: 10px 14px; color: var(--text-primary); outline: none; font-size: 13px; font-weight: 600;">
                            <option value="15">15 {{ __('Minutes') }}</option>
                            <option value="30" selected>30 {{ __('Minutes') }}</option>
                            <option value="45">45 {{ __('Minutes') }}</option>
                            <option value="60">1 {{ __('Hour') }}</option>
                            <option value="90">1.5 {{ __('Hours') }}</option>
                            <option value="120">2 {{ __('Hours') }}</option>
                        </select>
                    </div>
                </div>

                <!-- Date & Time -->
                <div>
                    <label style="display: block; font-size: 12px; font-weight: 700; color: var(--text-secondary); margin-bottom: 6px;">{{ __('Scheduled Date & Time') }} *</label>
                    <input type="datetime-local" name="scheduled_at" id="meeting-scheduled-at-input" required style="width: 100%; background: var(--bg-surface-subtle); border: 1px solid var(--border-color); border-radius: 10px; padding: 10px 14px; color: var(--text-primary); outline: none; font-size: 13px; font-weight: 600; box-shadow: var(--shadow-inset-3d);">
                </div>

                <!-- General Attendees Selection (Shown when scope is general) -->
                <div id="meeting-general-attendees-field">
                    <label style="display: block; font-size: 12px; font-weight: 700; color: var(--text-secondary); margin-bottom: 6px;">
                        👥 {{ __('Select Attendees to Invite') }}
                    </label>
                    <div style="max-height: 140px; overflow-y: auto; background: var(--bg-surface-subtle); border: 1px solid var(--border-color); border-radius: 10px; padding: 8px 12px; display: flex; flex-direction: column; gap: 6px;">
                        @foreach($members as $m)
                            @if($m->user_id !== $user->id)
                                <label style="display: flex; align-items: center; justify-content: space-between; font-size: 12px; color: var(--text-primary); cursor: pointer; padding: 4px 6px; border-radius: 6px; transition: background 0.2s;" onmouseover="this.style.background='var(--bg-surface)'" onmouseout="this.style.background='transparent'">
                                    <span style="display: flex; align-items: center; gap: 8px;">
                                        <input type="checkbox" name="attendee_ids[]" value="{{ $m->user_id }}" style="accent-color: var(--brand-forest);">
                                        <strong>{{ $m->user->name }}</strong>
                                        <span style="font-size: 11px; color: var(--text-muted);">({{ $m->user->email }})</span>
                                    </span>
                                    <span class="nav-badge-pill" style="font-size: 10px;">{{ $m->role->name ?? 'Member' }}</span>
                                </label>
                            @endif
                        @endforeach
                    </div>
                </div>

                <div style="background: var(--bg-surface-subtle); border: 1px solid var(--border-color); border-radius: 12px; padding: 12px; font-size: 11px; color: var(--text-secondary); display: flex; align-items: center; gap: 8px;">
                    <span style="font-size: 18px;">🔔</span>
                    <span>{{ __('Email invitations with direct Join links will be dispatched automatically, and all attendees will receive sound chime alerts before the session starts.') }}</span>
                </div>

                <button type="submit" class="tactile-btn btn-primary" style="margin-top: 6px; padding: 12px; font-size: 14px; justify-content: center; width: 100%;">
                    🚀 {{ __('Schedule Meeting & Dispatch Invitations') }}
                </button>
            </form>
        </div>
    </div>

    <!-- Modal: Manual Time Entry -->
    <div id="manual-time-modal" class="modal-overlay">
        <div class="modal-card" style="max-width: 500px; border-radius: 20px; padding: 24px;">
            <div class="modal-header" style="margin-bottom: 20px; display: flex; justify-content: space-between; align-items: center;">
                <h3 class="modal-title" style="font-size: 18px; font-weight: 900; color: var(--text-primary);">✍️ {{ __('Log Manual Time Entry') }}</h3>
                <button onclick="closeManualTimeModal()" class="modal-close" style="background: var(--bg-surface-subtle); border: 1px solid var(--border-color); width: 32px; height: 32px; border-radius: 50%; display: flex; align-items: center; justify-content: center; cursor: pointer; color: var(--text-primary); font-weight: 800;">✕</button>
            </div>
            <form id="manual-time-form" onsubmit="logManualTimeSubmit(event)" style="display: flex; flex-direction: column; gap: 14px;">
                <div>
                    <label style="display: block; font-size: 12px; font-weight: 700; color: var(--text-secondary); margin-bottom: 6px;">{{ __('Project') }} *</label>
                    <select name="project_id" required style="width: 100%; background: var(--bg-surface-subtle); border: 1px solid var(--border-color); border-radius: 10px; padding: 10px 14px; color: var(--text-primary); outline: none; font-size: 13px; font-weight: 600;">
                        @foreach($projects as $p)
                            <option value="{{ $p->id }}">📁 {{ $p->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                    <div>
                        <label style="display: block; font-size: 12px; font-weight: 700; color: var(--text-secondary); margin-bottom: 6px;">{{ __('Start Time') }} *</label>
                        <input type="datetime-local" name="started_at" required style="width: 100%; background: var(--bg-surface-subtle); border: 1px solid var(--border-color); border-radius: 10px; padding: 10px 14px; color: var(--text-primary); outline: none; font-size: 12px; font-weight: 600; box-shadow: var(--shadow-inset-3d);">
                    </div>
                    <div>
                        <label style="display: block; font-size: 12px; font-weight: 700; color: var(--text-secondary); margin-bottom: 6px;">{{ __('End Time') }} *</label>
                        <input type="datetime-local" name="ended_at" required style="width: 100%; background: var(--bg-surface-subtle); border: 1px solid var(--border-color); border-radius: 10px; padding: 10px 14px; color: var(--text-primary); outline: none; font-size: 12px; font-weight: 600; box-shadow: var(--shadow-inset-3d);">
                    </div>
                </div>
                <div>
                    <label style="display: block; font-size: 12px; font-weight: 700; color: var(--text-secondary); margin-bottom: 6px;">{{ __('Description') }}</label>
                    <input type="text" name="description" placeholder="What did you work on?" style="width: 100%; background: var(--bg-surface-subtle); border: 1px solid var(--border-color); border-radius: 10px; padding: 10px 14px; color: var(--text-primary); outline: none; font-size: 13px; font-weight: 600; box-shadow: var(--shadow-inset-3d);">
                </div>
                <button type="submit" class="tactile-btn btn-primary" style="margin-top: 8px; padding: 12px; font-size: 14px; justify-content: center; width: 100%;">
                    ⏱️ {{ __('Log Time') }}
                </button>
            </form>
        </div>
    </div>

    <!-- Modal: Reject Timesheet -->
    <div id="reject-timesheet-modal" class="modal-overlay">
        <div class="modal-card" style="max-width: 480px; border-radius: 20px; padding: 24px;">
            <div class="modal-header" style="margin-bottom: 20px; display: flex; justify-content: space-between; align-items: center;">
                <h3 class="modal-title" style="font-size: 18px; font-weight: 900; color: var(--text-primary);">❌ {{ __('Reject Timesheet') }}</h3>
                <button onclick="closeRejectModal()" class="modal-close" style="background: var(--bg-surface-subtle); border: 1px solid var(--border-color); width: 32px; height: 32px; border-radius: 50%; display: flex; align-items: center; justify-content: center; cursor: pointer; color: var(--text-primary); font-weight: 800;">✕</button>
            </div>
            <form onsubmit="rejectTimesheetSubmit(event)" style="display: flex; flex-direction: column; gap: 14px;">
                <div>
                    <label style="display: block; font-size: 12px; font-weight: 700; color: var(--text-secondary); margin-bottom: 6px;">{{ __('Feedback Reason for Employee') }} *</label>
                    <textarea id="reject-reason-input" required rows="3" placeholder="Please clarify the 6 hours logged on Friday..." style="width: 100%; background: var(--bg-surface-subtle); border: 1px solid var(--border-color); border-radius: 10px; padding: 10px 14px; color: var(--text-primary); outline: none; font-size: 13px; font-weight: 600; box-shadow: var(--shadow-inset-3d);"></textarea>
                </div>
                <button type="submit" class="tactile-btn" style="margin-top: 8px; padding: 12px; font-size: 14px; justify-content: center; background: #D96B5F; color: white; width: 100%;">
                    ❌ {{ __('Confirm Rejection & Send Feedback') }}
                </button>
            </form>
        </div>
    </div>

    <!-- Modal: Project Hub & KPI Dashboard Drawer -->
    <div id="project-hub-modal" class="modal-overlay">
        <div class="modal-card" style="max-width: 1100px; width: 95vw; max-height: 90vh; display: flex; flex-direction: column; padding: 24px; overflow: hidden; border-radius: 24px;">
            <!-- Hub Header -->
            <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 16px; border-bottom: 1px solid var(--border-color); padding-bottom: 14px;">
                <div>
                    <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 6px; flex-wrap: wrap;">
                        <span id="hub-proj-code" class="nav-badge-pill" style="font-family: monospace; font-size: 12px;">PRJ-01</span>
                        <h2 id="hub-proj-name" style="font-size: 20px; font-weight: 900; margin: 0; color: var(--text-primary);">Project Name</h2>
                        <span id="hub-proj-status" class="nav-badge-pill" style="background: rgba(79, 155, 95, 0.15); color: #4F9B5F;">Active</span>
                        <span id="hub-proj-priority" class="nav-badge-pill" style="background: rgba(214, 162, 58, 0.15); color: #D6A23A;">High</span>
                    </div>
                    <div style="display: flex; align-items: center; gap: 16px; font-size: 12px; color: var(--text-muted); flex-wrap: wrap;">
                        <span>👤 {{ __('Manager') }}: <strong id="hub-proj-manager" style="color: var(--text-primary);">Name</strong></span>
                        <span>🏛️ {{ __('Department') }}: <strong id="hub-proj-dept" style="color: var(--text-primary);">Dept</strong></span>
                        <span>📅 {{ __('Due Date') }}: <strong id="hub-proj-due" style="color: var(--text-primary);">Date</strong></span>
                    </div>
                </div>
                <div style="display: flex; align-items: center; gap: 8px;">
                    <button onclick="scheduleMeetingForCurrentProject()" class="tactile-btn btn-secondary" style="padding: 8px 14px; font-size: 12px;">
                        <span>📅</span> {{ __('Schedule Meeting') }}
                    </button>
                    <button onclick="openNewTaskForCurrentProject()" class="tactile-btn btn-primary" style="padding: 8px 16px; font-size: 12px;">
                        <span>+</span> {{ __('Add Task') }}
                    </button>
                    <button onclick="closeProjectHub()" class="modal-close" style="background: var(--bg-surface-subtle); border: 1px solid var(--border-color); width: 32px; height: 32px; border-radius: 50%; display: flex; align-items: center; justify-content: center; cursor: pointer; color: var(--text-primary); font-weight: 800;">✕</button>
                </div>
            </div>

            <!-- Hub KPI Stats Bar (3D Soft Neumorphic) -->
            <div class="kpi-grid" style="grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 12px; margin-bottom: 16px;">
                <!-- Progress KPI -->
                <div class="kpi-card" style="padding: 14px;">
                    <div class="kpi-header">
                        <span class="kpi-title">{{ __('Progress') }}</span>
                        <div class="kpi-icon-box">📊</div>
                    </div>
                    <div id="hub-kpi-progress-pct" class="kpi-value" style="font-size: 20px; color: var(--brand-forest);">0%</div>
                    <div id="hub-kpi-tasks-ratio" style="font-size: 11px; color: var(--text-muted);">0 / 0 tasks done</div>
                </div>
                <!-- Hours & Effort KPI -->
                <div class="kpi-card" style="padding: 14px;">
                    <div class="kpi-header">
                        <span class="kpi-title">{{ __('Actual vs Planned') }}</span>
                        <div class="kpi-icon-box">⏱️</div>
                    </div>
                    <div id="hub-kpi-hours" class="kpi-value" style="font-size: 20px; color: var(--brand-forest);">0 / 0 h</div>
                    <div id="hub-kpi-hours-var" style="font-size: 11px; color: var(--text-muted);">Variance: 0h</div>
                </div>
                <!-- Financials & Margin KPI -->
                <div class="kpi-card" style="padding: 14px;">
                    <div class="kpi-header">
                        <span class="kpi-title">{{ __('Budget & Cost') }}</span>
                        <div class="kpi-icon-box">💰</div>
                    </div>
                    <div id="hub-kpi-budget" class="kpi-value" style="font-size: 20px; color: var(--brand-forest);">$0 / $0</div>
                    <div id="hub-kpi-margin" style="font-size: 11px; color: #4F9B5F;">Margin: $0 (0%)</div>
                </div>
                <!-- Health & Overdue KPI -->
                <div class="kpi-card" style="padding: 14px;">
                    <div class="kpi-header">
                        <span class="kpi-title">{{ __('Active & Overdue') }}</span>
                        <div class="kpi-icon-box">⚡</div>
                    </div>
                    <div id="hub-kpi-active-tasks" class="kpi-value" style="font-size: 20px; color: #D96B5F;">0 Active</div>
                    <div id="hub-kpi-overdue-tasks" style="font-size: 11px; color: #D96B5F;">0 Overdue</div>
                </div>
            </div>

            <!-- Hub Inner Navigation Tabs -->
            <div style="display: flex; gap: 8px; margin-bottom: 14px; background: var(--bg-surface-subtle); padding: 4px; border-radius: var(--radius-md); border: 1px solid var(--border-color); box-shadow: var(--shadow-inset-3d);">
                <button onclick="switchHubTab('kanban')" id="hub-tab-btn-kanban" class="tactile-btn btn-primary" style="flex: 1; padding: 8px; font-size: 12px; justify-content: center;">
                    📌 {{ __('Kanban Board') }}
                </button>
                <button onclick="switchHubTab('tasks')" id="hub-tab-btn-tasks" class="tactile-btn btn-secondary" style="flex: 1; padding: 8px; font-size: 12px; justify-content: center; background: transparent; border: none; box-shadow: none; color: var(--text-secondary);">
                    📋 {{ __('Task Table') }}
                </button>
                <button onclick="switchHubTab('timelog')" id="hub-tab-btn-timelog" class="tactile-btn btn-secondary" style="flex: 1; padding: 8px; font-size: 12px; justify-content: center; background: transparent; border: none; box-shadow: none; color: var(--text-secondary);">
                    ⏱️ {{ __('Time Entries Log') }}
                </button>
            </div>

            <!-- Hub Content Area (Scrollable) -->
            <div style="flex: 1; overflow-y: auto; padding-right: 4px;">
                <!-- 1. Kanban View -->
                <div id="hub-view-kanban" style="display: block;">
                    <div style="display: grid; grid-template-columns: repeat(5, minmax(200px, 1fr)); gap: 12px; align-items: start;">
                        <!-- Backlog -->
                        <div class="kanban-column" style="background: var(--bg-surface-subtle); border: 1px solid var(--border-color); border-radius: var(--radius-lg); padding: 12px;">
                            <div class="kanban-col-header" style="color: var(--text-secondary); display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px; font-weight: 800; font-size: 12px;">
                                <span>📌 Backlog</span>
                                <span id="col-count-backlog" class="nav-badge-pill">0</span>
                            </div>
                            <div id="kanban-col-backlog" style="display: flex; flex-direction: column; gap: 8px;"></div>
                        </div>
                        <!-- Ready -->
                        <div class="kanban-column" style="background: var(--bg-surface-subtle); border: 1px solid var(--border-color); border-radius: var(--radius-lg); padding: 12px;">
                            <div class="kanban-col-header" style="color: var(--brand-sage); display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px; font-weight: 800; font-size: 12px;">
                                <span>🎯 Ready</span>
                                <span id="col-count-ready" class="nav-badge-pill">0</span>
                            </div>
                            <div id="kanban-col-ready" style="display: flex; flex-direction: column; gap: 8px;"></div>
                        </div>
                        <!-- In Progress -->
                        <div class="kanban-column" style="background: var(--bg-surface-subtle); border: 1px solid var(--border-color); border-radius: var(--radius-lg); padding: 12px;">
                            <div class="kanban-col-header" style="color: var(--brand-forest); display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px; font-weight: 800; font-size: 12px;">
                                <span>⚡ In Progress</span>
                                <span id="col-count-in_progress" class="nav-badge-pill" style="background: rgba(79, 155, 95, 0.2); color: #4F9B5F;">0</span>
                            </div>
                            <div id="kanban-col-in_progress" style="display: flex; flex-direction: column; gap: 8px;"></div>
                        </div>
                        <!-- Review / QA -->
                        <div class="kanban-column" style="background: var(--bg-surface-subtle); border: 1px solid var(--border-color); border-radius: var(--radius-lg); padding: 12px;">
                            <div class="kanban-col-header" style="color: var(--status-warning); display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px; font-weight: 800; font-size: 12px;">
                                <span>🔍 Review / QA</span>
                                <span id="col-count-review" class="nav-badge-pill" style="background: rgba(214, 162, 58, 0.2); color: #D6A23A;">0</span>
                            </div>
                            <div id="kanban-col-review" style="display: flex; flex-direction: column; gap: 8px;"></div>
                        </div>
                        <!-- Done -->
                        <div class="kanban-column" style="background: var(--bg-surface-subtle); border: 1px solid var(--border-color); border-radius: var(--radius-lg); padding: 12px;">
                            <div class="kanban-col-header" style="color: var(--brand-forest); display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px; font-weight: 800; font-size: 12px;">
                                <span>🎉 Done</span>
                                <span id="col-count-done" class="nav-badge-pill" style="background: rgba(79, 155, 95, 0.2); color: #4F9B5F;">0</span>
                            </div>
                            <div id="kanban-col-done" style="display: flex; flex-direction: column; gap: 8px;"></div>
                        </div>
                    </div>
                </div>

                <!-- 2. Task Table View -->
                <div id="hub-view-tasks" style="display: none;">
                    <div style="overflow-x: auto;">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>{{ __('Task Title') }}</th>
                                    <th>{{ __('Assignee') }}</th>
                                    <th>{{ __('Status') }}</th>
                                    <th>{{ __('Priority') }}</th>
                                    <th>{{ __('Hours (Est/Act)') }}</th>
                                    <th>{{ __('Due Date') }}</th>
                                    <th>{{ __('Action') }}</th>
                                </tr>
                            </thead>
                            <tbody id="hub-task-table-body"></tbody>
                        </table>
                    </div>
                </div>

                <!-- 3. Time Log View -->
                <div id="hub-view-timelog" style="display: none;">
                    <div style="overflow-x: auto;">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>{{ __('Date') }}</th>
                                    <th>{{ __('Employee') }}</th>
                                    <th>{{ __('Task') }}</th>
                                    <th>{{ __('Duration') }}</th>
                                    <th>{{ __('Description') }}</th>
                                    <th>{{ __('Type') }}</th>
                                    <th>{{ __('Status') }}</th>
                                </tr>
                            </thead>
                            <tbody id="hub-timelog-table-body"></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal: Task Inspector & Activity Drawer -->
    <div id="task-details-modal" class="modal-overlay">
        <div class="modal-card" style="max-width: 850px; width: 95vw; max-height: 90vh; display: flex; flex-direction: column; padding: 24px; overflow: hidden; border-radius: 24px;">
            <!-- Header -->
            <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 14px; border-bottom: 1px solid var(--border-color); padding-bottom: 12px; flex-wrap: wrap; gap: 10px;">
                <div>
                    <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 4px; flex-wrap: wrap;">
                        <span id="task-modal-code" class="nav-badge-pill" style="font-family: monospace;">#1</span>
                        <h2 id="task-modal-title" style="font-size: 18px; font-weight: 900; margin: 0; color: var(--text-primary);">Task Title</h2>
                        <span id="task-modal-status-badge" class="nav-badge-pill" style="background: rgba(79, 155, 95, 0.2); color: #4F9B5F;">In Progress</span>
                        <span id="task-modal-priority-badge" class="nav-badge-pill" style="background: rgba(217, 107, 95, 0.2); color: #D96B5F;">Urgent</span>
                    </div>
                    <div style="display: flex; align-items: center; gap: 14px; font-size: 12px; color: var(--text-muted); flex-wrap: wrap;">
                        <span>📁 {{ __('Project') }}: <strong id="task-modal-project" style="color: var(--text-primary);">Project Name</strong></span>
                        <span onclick="if(window.currentModalTaskAssigneeMemberId) { closeTaskDetailsModal(); openMemberProfileModal(window.currentModalTaskAssigneeMemberId); }" style="cursor: pointer;" title="{{ __('Click to view member profile, tasks & hours') }}">👤 {{ __('Assignee') }}: <strong id="task-modal-assignee" style="color: var(--brand-forest); text-decoration: underline;">Assignee</strong></span>
                        <span>📅 {{ __('Due Date') }}: <strong id="task-modal-due" style="color: var(--text-primary);">Date</strong></span>
                    </div>
                </div>
                <div style="display: flex; align-items: center; gap: 10px;">
                    <button id="task-modal-timer-btn" class="tactile-btn" style="background: rgba(79, 155, 95, 0.15); color: var(--brand-forest); border: 1px solid rgba(79, 155, 95, 0.3); padding: 6px 14px; font-size: 12px;">
                        ▶ {{ __('Start Timer') }}
                    </button>
                    <button onclick="closeTaskDetailsModal()" class="modal-close" style="background: var(--bg-surface-subtle); border: 1px solid var(--border-color); width: 32px; height: 32px; border-radius: 50%; display: flex; align-items: center; justify-content: center; cursor: pointer; color: var(--text-primary); font-weight: 800;">✕</button>
                </div>
            </div>

            <!-- Task Quick Status Changer Bar & PM Approval Actions -->
            <div style="display: flex; flex-direction: column; gap: 8px; margin-bottom: 14px;">
                <div style="display: flex; justify-content: space-between; align-items: center; background: var(--bg-surface-subtle); padding: 10px 16px; border-radius: var(--radius-md); border: 1px solid var(--border-color); box-shadow: var(--shadow-inset-3d); flex-wrap: wrap; gap: 10px;">
                    <div style="display: flex; align-items: center; gap: 10px;">
                        <span style="font-size: 12px; font-weight: 800; color: var(--text-secondary);">⚡ {{ __('Status') }}:</span>
                        <select id="task-modal-status-select" onchange="updateCurrentTaskStatus(this.value)" style="background: var(--bg-surface); border: 1px solid var(--border-color); color: var(--text-primary); font-size: 12px; font-weight: 700; border-radius: 8px; padding: 5px 12px; outline: none;">
                            <option value="backlog">📌 {{ __('Backlog') }}</option>
                            <option value="ready">🎯 {{ __('Ready') }}</option>
                            <option value="in_progress">⚡ {{ __('In Progress') }}</option>
                            <option value="review">🔍 {{ __('In Review / QA') }}</option>
                            <option value="done">🎉 {{ __('Done / Completed') }}</option>
                        </select>
                    </div>
                    <div style="font-size: 12px; font-family: monospace; font-weight: 800; color: var(--brand-forest);">
                        ⏱️ <span id="task-modal-hours">0h / 0h</span>
                    </div>
                </div>

                <!-- Approval Status Alert & Action Box -->
                <div id="task-modal-approval-banner" style="display: none; padding: 12px 16px; border-radius: var(--radius-md); font-size: 12px; font-weight: 700; justify-content: space-between; align-items: center; gap: 12px; flex-wrap: wrap;">
                    <div id="task-modal-approval-text" style="display: flex; align-items: center; gap: 8px;"></div>
                    <div id="task-modal-approval-actions" style="display: flex; gap: 8px;"></div>
                </div>
            </div>

            <!-- Sub-Tabs Segmented Control -->
            <div class="task-modal-segmented-bar" style="display: flex; gap: 4px; margin-bottom: 16px; background: var(--bg-surface-subtle); padding: 4px; border-radius: 12px; border: 1px solid var(--border-color); box-shadow: var(--shadow-inset-3d); overflow-x: auto;">
                <button type="button" onclick="switchTaskInspectorTab('details')" id="task-tab-btn-details" class="tactile-btn btn-primary" style="flex: 1; min-width: 80px; padding: 8px 10px; font-size: 12px; justify-content: center; gap: 6px;">
                    <span>📝</span>
                    <span>{{ __('Details') }}</span>
                </button>
                <button type="button" onclick="switchTaskInspectorTab('checklist')" id="task-tab-btn-checklist" class="tactile-btn btn-secondary" style="flex: 1; min-width: 80px; padding: 8px 10px; font-size: 12px; justify-content: center; gap: 6px; background: transparent; border: none; box-shadow: none; color: var(--text-secondary);">
                    <span>☑️</span>
                    <span>{{ __('Checklist') }}</span>
                    <span id="task-checklist-count" style="font-size: 10px; background: rgba(36, 92, 58, 0.15); color: var(--brand-forest); padding: 1px 7px; border-radius: 9999px; font-weight: 800; font-family: monospace;">0</span>
                </button>
                <button type="button" onclick="switchTaskInspectorTab('attachments')" id="task-tab-btn-attachments" class="tactile-btn btn-secondary" style="flex: 1; min-width: 80px; padding: 8px 10px; font-size: 12px; justify-content: center; gap: 6px; background: transparent; border: none; box-shadow: none; color: var(--text-secondary);">
                    <span>📎</span>
                    <span>{{ __('Files') }}</span>
                    <span id="task-attachments-count" style="font-size: 10px; background: rgba(36, 92, 58, 0.15); color: var(--brand-forest); padding: 1px 7px; border-radius: 9999px; font-weight: 800; font-family: monospace;">0</span>
                </button>
                <button type="button" onclick="switchTaskInspectorTab('comments')" id="task-tab-btn-comments" class="tactile-btn btn-secondary" style="flex: 1; min-width: 80px; padding: 8px 10px; font-size: 12px; justify-content: center; gap: 6px; background: transparent; border: none; box-shadow: none; color: var(--text-secondary);">
                    <span>💬</span>
                    <span>{{ __('Discussions') }}</span>
                    <span id="task-comments-count" style="font-size: 10px; background: rgba(36, 92, 58, 0.15); color: var(--brand-forest); padding: 1px 7px; border-radius: 9999px; font-weight: 800; font-family: monospace;">0</span>
                </button>
                <button type="button" onclick="switchTaskInspectorTab('dependencies')" id="task-tab-btn-dependencies" class="tactile-btn btn-secondary" style="flex: 1; min-width: 80px; padding: 8px 10px; font-size: 12px; justify-content: center; gap: 6px; background: transparent; border: none; box-shadow: none; color: var(--text-secondary);">
                    <span>🔗</span>
                    <span>{{ __('Dependencies') }}</span>
                </button>
                <button type="button" onclick="switchTaskInspectorTab('timelog')" id="task-tab-btn-timelog" class="tactile-btn btn-secondary" style="flex: 1; min-width: 80px; padding: 8px 10px; font-size: 12px; justify-content: center; gap: 6px; background: transparent; border: none; box-shadow: none; color: var(--text-secondary);">
                    <span>⏱️</span>
                    <span>{{ __('Time Log') }}</span>
                </button>
            </div>

            <!-- Tab Contents -->
            <div style="flex: 1; overflow-y: auto; padding-right: 4px;">
                <!-- 1. Details -->
                <div id="task-inspector-details" style="display: block;">
                    <div style="margin-bottom: 14px;">
                        <label style="display: block; font-size: 11px; font-weight: 800; color: var(--text-secondary); text-transform: uppercase; margin-bottom: 6px;">{{ __('Description') }}</label>
                        <div id="task-modal-description" style="background: var(--bg-surface-subtle); padding: 14px; border-radius: 12px; font-size: 13px; color: var(--text-primary); line-height: 1.5; border: 1px solid var(--border-color); box-shadow: var(--shadow-inset-3d);">
                            —
                        </div>
                    </div>
                </div>

                <!-- 2. Checklist -->
                <div id="task-inspector-checklist" style="display: none;">
                    <form onsubmit="addTaskChecklistItem(event)" style="display: flex; gap: 8px; margin-bottom: 14px;">
                        <input type="text" id="new-checklist-title-input" required placeholder="{{ __('Add checklist sub-item (e.g. Write unit tests, create migration)...') }}" style="flex: 1; background: var(--bg-surface-subtle); border: 1px solid var(--border-color); border-radius: 10px; padding: 9px 12px; color: var(--text-primary); outline: none; font-size: 12px; font-weight: 600; box-shadow: var(--shadow-inset-3d);">
                        <button type="submit" class="tactile-btn btn-primary" style="padding: 8px 16px; font-size: 12px;">
                            <span>+</span> {{ __('Add Item') }}
                        </button>
                    </form>
                    <div id="task-checklist-items-container" style="display: flex; flex-direction: column; gap: 6px;"></div>
                </div>

                <!-- 3. Attachments & Files -->
                <div id="task-inspector-attachments" style="display: none;">
                    <form onsubmit="uploadTaskAttachmentSubmit(event)" style="background: var(--bg-surface-subtle); border: 1px dashed var(--border-color); border-radius: 12px; padding: 16px; text-align: center; margin-bottom: 14px;">
                        <div style="font-size: 24px; margin-bottom: 6px;">📎</div>
                        <div style="font-size: 12px; font-weight: 800; color: var(--text-primary); margin-bottom: 8px;">{{ __('Upload Document or Attachment to Task') }}</div>
                        <div style="display: flex; justify-content: center; gap: 8px; align-items: center; max-width: 420px; margin: 0 auto;">
                            <input type="file" id="task-file-input" required style="font-size: 12px; color: var(--text-primary);">
                            <button type="submit" class="tactile-btn btn-primary" style="padding: 6px 14px; font-size: 12px;">
                                📤 {{ __('Upload') }}
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
                        <span style="font-size: 11px; font-weight: 800; color: var(--text-secondary);">@ {{ __('Mention') }}:</span>
                        @foreach($members->take(6) as $chipMember)
                            @if($chipMember->user_id !== $user->id)
                                <button type="button" onclick="insertMentionHandle('{{ $chipMember->user->name }}')" class="nav-badge-pill" style="cursor: pointer; font-size: 10px; border: 1px solid var(--border-color); background: var(--bg-surface-subtle); color: var(--brand-forest); font-weight: 700;" title="{{ __('Click to mention :name', ['name' => $chipMember->user->name]) }}">
                                    @<span>{{ $chipMember->user->name }}</span>
                                </button>
                            @endif
                        @endforeach
                    </div>

                    <form onsubmit="addTaskCommentSubmit(event)" style="display: flex; gap: 8px;">
                        <input type="text" id="new-comment-body-input" required placeholder="{{ __('Write a comment or status update... Type @name to mention') }}" style="flex: 1; background: var(--bg-surface-subtle); border: 1px solid var(--border-color); border-radius: 10px; padding: 9px 12px; color: var(--text-primary); outline: none; font-size: 12px; font-weight: 600; box-shadow: var(--shadow-inset-3d);">
                        <button type="submit" class="tactile-btn btn-primary" style="padding: 8px 16px; font-size: 12px;">
                            💬 {{ __('Post') }}
                        </button>
                    </form>
                </div>

                <!-- 5. Dependencies -->
                <div id="task-inspector-dependencies" style="display: none;">
                    <div style="background: var(--bg-surface-subtle); padding: 14px; border-radius: 12px; margin-bottom: 14px; border: 1px solid var(--border-color); box-shadow: var(--shadow-inset-3d);">
                        <label style="display: block; font-size: 11px; font-weight: 800; color: var(--text-secondary); text-transform: uppercase; margin-bottom: 6px;">🔗 {{ __('Add Predecessor / Blocker Task') }}</label>
                        <form onsubmit="addTaskDependencySubmit(event)" style="display: flex; gap: 8px;">
                            <select id="dependency-blocker-select" required style="flex: 1; background: var(--bg-surface); border: 1px solid var(--border-color); border-radius: 8px; padding: 8px 12px; color: var(--text-primary); font-size: 12px; font-weight: 600;">
                                <option value="">— {{ __('Select Blocker Task') }} —</option>
                                @foreach($tasks as $oth)
                                    <option value="{{ $oth->id }}">#{{ $oth->task_number }} {{ $oth->title }} ({{ $oth->project->code ?? 'PRJ' }})</option>
                                @endforeach
                            </select>
                            <button type="submit" class="tactile-btn btn-primary" style="padding: 8px 16px; font-size: 12px;">
                                <span>+</span> {{ __('Add Blocker') }}
                            </button>
                        </form>
                    </div>
                    <div id="task-dependencies-container" style="display: flex; flex-direction: column; gap: 6px;"></div>
                </div>

                <!-- 6. Time Log -->
                <div id="task-inspector-timelog" style="display: none;">
                    <table class="data-table">
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

    <!-- Comprehensive Team Member Profile Modal -->
    <div id="member-details-modal" class="modal" style="display: none; align-items: center; justify-content: center; z-index: 9999;">
        <div class="modal-box" style="max-width: 860px; width: 95%; max-height: 90vh; display: flex; flex-direction: column; padding: 0; overflow: hidden; border-radius: var(--radius-xl); box-shadow: var(--shadow-modal-3d); border: 1px solid var(--border-color); background: var(--bg-surface);">
            
            <!-- Modal Hero Header -->
            <div style="background: linear-gradient(135deg, rgba(79, 155, 95, 0.12) 0%, rgba(36, 92, 58, 0.22) 100%); padding: 24px; border-bottom: 1px solid var(--border-color); position: relative;">
                <button onclick="closeMemberProfileModal()" style="position: absolute; top: 16px; inset-inline-end: 16px; width: 32px; height: 32px; border-radius: 50%; background: var(--bg-surface); border: 1px solid var(--border-color); color: var(--text-secondary); font-size: 16px; font-weight: 800; cursor: pointer; display: flex; align-items: center; justify-content: center; box-shadow: var(--shadow-soft-3d); transition: all 0.2s;">✕</button>

                <div style="display: flex; align-items: center; gap: 20px; flex-wrap: wrap;">
                    <!-- Avatar -->
                    <div id="mp-avatar-container" style="position: relative; width: 76px; height: 76px; border-radius: 20px; background: var(--accent-gradient); border: 3px solid #FFFDF6; box-shadow: 0 10px 25px rgba(36, 92, 58, 0.25); display: flex; align-items: center; justify-content: center; font-size: 26px; font-weight: 900; color: white; flex-shrink: 0; overflow: hidden;">
                        <img id="mp-avatar-img" src="" alt="Avatar" style="width: 100%; height: 100%; object-fit: cover; display: none;">
                        <span id="mp-avatar-fallback">AB</span>
                        <div style="position: absolute; bottom: -2px; inset-inline-end: -2px; width: 16px; height: 16px; border-radius: 50%; background: #4F9B5F; border: 3px solid #FFFDF6;" title="Online"></div>
                    </div>

                    <!-- Details -->
                    <div style="flex: 1; min-width: 200px;">
                        <div style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
                            <h2 id="mp-user-name" style="font-size: 22px; font-weight: 900; color: var(--text-primary); margin: 0;">Member Name</h2>
                            <span id="mp-user-nickname" class="nav-badge-pill" style="font-family: monospace; font-size: 11px;">@nickname</span>
                            <span id="mp-user-role" class="nav-badge-pill" style="background: rgba(79, 155, 95, 0.15); color: #4F9B5F; font-size: 11px;">Employee</span>
                        </div>
                        <div style="display: flex; align-items: center; gap: 8px; margin-top: 6px; flex-wrap: wrap; font-size: 12px; color: var(--text-secondary);">
                            <span id="mp-job-title" style="font-weight: 700; color: var(--text-primary);">Senior Engineer</span>
                            <span>•</span>
                            <span id="mp-dept-team">Engineering Team</span>
                            <span>•</span>
                            <span id="mp-work-mode" class="nav-badge-pill" style="font-size: 10px;">🏠 Remote</span>
                        </div>
                    </div>

                    <!-- Direct Chat Action -->
                    <div style="flex-shrink: 0;">
                        <button id="mp-chat-btn" onclick="openChatFromProfileModal()" class="tactile-btn btn-primary" style="padding: 10px 20px; font-size: 13px;">
                            <span>💬</span> {{ __('Send Message') }}
                        </button>
                    </div>
                </div>
            </div>

            <!-- Profile Nav Tabs -->
            <div style="display: flex; border-bottom: 1px solid var(--border-color); background: var(--bg-surface-subtle); padding: 0 16px;">
                <button onclick="switchMemberProfileTab('about')" id="mp-tab-btn-about" class="member-profile-tab-btn active" style="padding: 14px 18px; font-size: 13px; font-weight: 800; border: none; background: transparent; cursor: pointer; color: var(--brand-forest); border-bottom: 3px solid var(--brand-forest); transition: all 0.2s;">
                    👤 {{ __('Profile & About') }}
                </button>
                <button onclick="switchMemberProfileTab('tasks')" id="mp-tab-btn-tasks" class="member-profile-tab-btn" style="padding: 14px 18px; font-size: 13px; font-weight: 700; border: none; background: transparent; cursor: pointer; color: var(--text-secondary); border-bottom: 3px solid transparent; transition: all 0.2s;">
                    📋 {{ __('Assigned Tasks') }} <span id="mp-tasks-count-pill" class="nav-badge-pill" style="font-size: 10px; margin-inline-start: 4px;">0</span>
                </button>
                <button onclick="switchMemberProfileTab('time')" id="mp-tab-btn-time" class="member-profile-tab-btn" style="padding: 14px 18px; font-size: 13px; font-weight: 700; border: none; background: transparent; cursor: pointer; color: var(--text-secondary); border-bottom: 3px solid transparent; transition: all 0.2s;">
                    ⏱️ {{ __('Work Time & Logs') }} <span id="mp-hours-count-pill" class="nav-badge-pill" style="font-size: 10px; margin-inline-start: 4px;">0h</span>
                </button>
            </div>

            <!-- Profile Content Body -->
            <div style="flex: 1; overflow-y: auto; padding: 24px; max-height: calc(90vh - 200px);">
                
                <!-- TAB 1: ABOUT & INFO -->
                <div id="mp-tab-content-about" style="display: flex; flex-direction: column; gap: 20px;">
                    
                    <!-- Contact Cards Grid -->
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 12px;">
                        <div style="background: var(--bg-surface-subtle); padding: 12px 14px; border-radius: 12px; border: 1px solid var(--border-color); box-shadow: var(--shadow-inset-3d);">
                            <div style="font-size: 10px; font-weight: 800; color: var(--text-muted); text-transform: uppercase;">✉️ {{ __('Email Address') }}</div>
                            <div id="mp-info-email" style="font-size: 12px; font-weight: 700; color: var(--text-primary); margin-top: 4px; word-break: break-all;">user@company.com</div>
                        </div>
                        <div style="background: var(--bg-surface-subtle); padding: 12px 14px; border-radius: 12px; border: 1px solid var(--border-color); box-shadow: var(--shadow-inset-3d);">
                            <div style="font-size: 10px; font-weight: 800; color: var(--text-muted); text-transform: uppercase;">📱 {{ __('Phone') }}</div>
                            <div id="mp-info-phone" style="font-size: 12px; font-weight: 700; color: var(--text-primary); margin-top: 4px;">—</div>
                        </div>
                        <div style="background: var(--bg-surface-subtle); padding: 12px 14px; border-radius: 12px; border: 1px solid var(--border-color); box-shadow: var(--shadow-inset-3d);">
                            <div style="font-size: 10px; font-weight: 800; color: var(--text-muted); text-transform: uppercase;">🎂 {{ __('Birthday') }}</div>
                            <div id="mp-info-dob" style="font-size: 12px; font-weight: 700; color: var(--text-primary); margin-top: 4px;">—</div>
                        </div>
                        <div style="background: var(--bg-surface-subtle); padding: 12px 14px; border-radius: 12px; border: 1px solid var(--border-color); box-shadow: var(--shadow-inset-3d);">
                            <div style="font-size: 10px; font-weight: 800; color: var(--text-muted); text-transform: uppercase;">📅 {{ __('Joined Workspace') }}</div>
                            <div id="mp-info-joined" style="font-size: 12px; font-weight: 700; color: var(--text-primary); margin-top: 4px;">Jan 01, 2026</div>
                        </div>
                    </div>

                    <!-- Bio Section -->
                    <div style="background: var(--bg-surface-subtle); padding: 16px; border-radius: 14px; border: 1px solid var(--border-color); box-shadow: var(--shadow-inset-3d);">
                        <div style="font-size: 11px; font-weight: 800; color: var(--text-secondary); text-transform: uppercase; margin-bottom: 6px;">📝 {{ __('About / Biography') }}</div>
                        <div id="mp-info-bio" style="font-size: 13px; line-height: 1.6; color: var(--text-primary); font-weight: 500;">No bio provided.</div>
                    </div>

                    <!-- Skills & Hobbies in 2 Columns -->
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                        <div style="background: var(--bg-surface-subtle); padding: 16px; border-radius: 14px; border: 1px solid var(--border-color); box-shadow: var(--shadow-inset-3d);">
                            <div style="font-size: 11px; font-weight: 800; color: var(--text-secondary); text-transform: uppercase; margin-bottom: 8px;">⚡ {{ __('Skills & Expertise') }}</div>
                            <div id="mp-info-skills" style="display: flex; flex-wrap: wrap; gap: 6px;">
                                <span style="font-size: 11px; color: var(--text-muted);">—</span>
                            </div>
                        </div>
                        <div style="background: var(--bg-surface-subtle); padding: 16px; border-radius: 14px; border: 1px solid var(--border-color); box-shadow: var(--shadow-inset-3d);">
                            <div style="font-size: 11px; font-weight: 800; color: var(--text-secondary); text-transform: uppercase; margin-bottom: 8px;">🎯 {{ __('Hobbies & Interests') }}</div>
                            <div id="mp-info-hobbies" style="display: flex; flex-wrap: wrap; gap: 6px;">
                                <span style="font-size: 11px; color: var(--text-muted);">—</span>
                            </div>
                        </div>
                    </div>

                    <!-- Social Media Links -->
                    <div style="background: var(--bg-surface-subtle); padding: 16px; border-radius: 14px; border: 1px solid var(--border-color); box-shadow: var(--shadow-inset-3d);">
                        <div style="font-size: 11px; font-weight: 800; color: var(--text-secondary); text-transform: uppercase; margin-bottom: 10px;">🌐 {{ __('Social Profiles & Portfolio') }}</div>
                        <div id="mp-info-socials" style="display: flex; gap: 10px; flex-wrap: wrap;">
                            <span style="font-size: 11px; color: var(--text-muted);">—</span>
                        </div>
                    </div>

                    <!-- Notes -->
                    <div id="mp-notes-container" style="background: var(--bg-surface-subtle); padding: 16px; border-radius: 14px; border: 1px solid var(--border-color); box-shadow: var(--shadow-inset-3d); display: none;">
                        <div style="font-size: 11px; font-weight: 800; color: var(--text-secondary); text-transform: uppercase; margin-bottom: 6px;">📌 {{ __('Work Preferences & Notes') }}</div>
                        <div id="mp-info-notes" style="font-size: 12px; color: var(--text-primary); line-height: 1.5;"></div>
                    </div>

                </div>

                <!-- TAB 2: ASSIGNED TASKS -->
                <div id="mp-tab-content-tasks" style="display: none; flex-direction: column; gap: 16px;">
                    
                    <!-- Tasks KPIs Summary -->
                    <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 12px;">
                        <div class="kpi-card" style="margin-bottom: 0; padding: 14px;">
                            <div class="kpi-title" style="font-size: 11px;">{{ __('Total Tasks') }}</div>
                            <div id="mp-task-stat-total" class="kpi-value" style="font-size: 20px;">0</div>
                        </div>
                        <div class="kpi-card" style="margin-bottom: 0; padding: 14px;">
                            <div class="kpi-title" style="font-size: 11px;">{{ __('In Progress') }}</div>
                            <div id="mp-task-stat-progress" class="kpi-value" style="font-size: 20px; color: var(--brand-forest);">0</div>
                        </div>
                        <div class="kpi-card" style="margin-bottom: 0; padding: 14px;">
                            <div class="kpi-title" style="font-size: 11px;">{{ __('Pending / Ready') }}</div>
                            <div id="mp-task-stat-pending" class="kpi-value" style="font-size: 20px; color: var(--status-warning);">0</div>
                        </div>
                        <div class="kpi-card" style="margin-bottom: 0; padding: 14px;">
                            <div class="kpi-title" style="font-size: 11px;">{{ __('Completed') }}</div>
                            <div id="mp-task-stat-done" class="kpi-value" style="font-size: 20px; color: #4F9B5F;">0</div>
                        </div>
                    </div>

                    <!-- Tasks Feed Container -->
                    <div id="mp-tasks-list-container" style="display: flex; flex-direction: column; gap: 8px;">
                        <div style="text-align: center; color: var(--text-muted); font-size: 12px; padding: 24px;">
                            {{ __('No tasks assigned to this member.') }}
                        </div>
                    </div>

                </div>

                <!-- TAB 3: WORK TIME & LOGS -->
                <div id="mp-tab-content-time" style="display: none; flex-direction: column; gap: 16px;">
                    
                    <!-- Time KPIs -->
                    <div style="display: grid; grid-template-columns: 1.2fr 1fr; gap: 14px;">
                        <div class="kpi-card" style="margin-bottom: 0; padding: 16px; display: flex; align-items: center; justify-content: space-between;">
                            <div>
                                <div class="kpi-title" style="font-size: 11px;">{{ __('Total Logged Effort') }}</div>
                                <div id="mp-time-total-hours" class="kpi-value" style="font-size: 24px; color: var(--brand-forest);">0.0h</div>
                                <div style="font-size: 10px; color: var(--text-muted); margin-top: 2px;">{{ __('Tracked across all initiatives') }}</div>
                            </div>
                            <div style="font-size: 32px;">⏱️</div>
                        </div>

                        <div id="mp-active-timer-box" class="kpi-card" style="margin-bottom: 0; padding: 16px; background: rgba(79, 155, 95, 0.1); border: 1px solid rgba(79, 155, 95, 0.3);">
                            <div style="display: flex; align-items: center; gap: 6px; font-size: 11px; font-weight: 800; color: #4F9B5F;">
                                <span style="animation: pulse 1.5s infinite;">🟢</span> {{ __('Live Stopwatch Status') }}
                            </div>
                            <div id="mp-active-timer-text" style="font-size: 13px; font-weight: 800; color: var(--text-primary); margin-top: 6px;">
                                {{ __('No active timer running') }}
                            </div>
                        </div>
                    </div>

                    <!-- Time Entries History Table -->
                    <div class="card" style="margin-bottom: 0; padding: 0; overflow: hidden; border-radius: var(--radius-lg);">
                        <div style="padding: 12px 16px; background: var(--bg-surface-subtle); border-bottom: 1px solid var(--border-color); font-size: 12px; font-weight: 800; color: var(--text-primary);">
                            ⏱️ {{ __('Recent Work Logs') }}
                        </div>
                        <div style="overflow-x: auto;">
                            <table class="data-table">
                                <thead>
                                    <tr>
                                        <th>{{ __('Date') }}</th>
                                        <th>{{ __('Project / Initiative') }}</th>
                                        <th>{{ __('Task') }}</th>
                                        <th>{{ __('Duration') }}</th>
                                        <th>{{ __('Description') }}</th>
                                    </tr>
                                </thead>
                                <tbody id="mp-time-entries-tbody">
                                    <tr>
                                        <td colspan="5" style="text-align: center; color: var(--text-muted); padding: 20px;">{{ __('No work logs recorded yet.') }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                </div>

            </div>

        </div>
    </div>

    <!-- Invite Modal -->
    <div id="invite-modal" class="modal">
        <div class="modal-box">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                <h3 style="font-size: 18px; font-weight: 800; color: var(--brand-navy);">📨 {{ __('Invite & Guest Access') }}</h3>
                <button onclick="closeInviteModal()" style="background: none; border: none; color: #94a3b8; font-size: 20px; cursor: pointer;">✕</button>
            </div>

            <!-- Tabs -->
            <div style="display: flex; gap: 8px; margin-bottom: 20px; background: #f1f5f9; padding: 4px; border-radius: 10px;">
                <button onclick="switchInviteTab('guest')" id="tab-guest-btn" style="flex: 1; padding: 8px; border-radius: 8px; border: none; font-size: 13px; font-weight: 700; cursor: pointer; background: var(--brand-teal); color: white;">
                    🔗 {{ __('Guest Meeting Link') }}
                </button>
                <button onclick="switchInviteTab('member')" id="tab-member-btn" style="flex: 1; padding: 8px; border-radius: 8px; border: none; font-size: 13px; font-weight: 700; cursor: pointer; background: none; color: var(--text-muted);">
                    👤 {{ __('Team Member') }}
                </button>
            </div>

            <!-- Guest Form -->
            <div id="guest-tab-content">
                <div style="display: flex; flex-direction: column; gap: 14px;">
                    <div>
                        <label style="display: block; font-size: 12px; font-weight: 700; color: var(--text-secondary); margin-bottom: 6px;">{{ __('Destination Room') }}</label>
                        @php
                            $defaultOffice = $offices->firstWhere('is_default', true) ?: $offices->first();
                            $defaultOfficeId = $defaultOffice?->id;
                            $sortedRooms = $rooms->sortBy(function($r) use ($defaultOfficeId) {
                                $rFloorId = $r->floor_id ?? $r->map?->floor_id;
                                return ($rFloorId == $defaultOfficeId) ? 0 : 1;
                            });
                        @endphp
                        <select id="invite-room-select" onchange="onInviteRoomSelected(this)" style="width: 100%; background: var(--bg-elevated); border: 1px solid var(--border-color); border-radius: 8px; padding: 10px; color: var(--text-primary); outline: none; font-size: 13px; font-weight: 600;">
                            @foreach($sortedRooms as $r)
                                @php
                                    $rFloor = $r->floor ?? $r->map?->floor;
                                    $rFloorId = $r->floor_id ?? $rFloor?->id;
                                    $isDefaultBranch = ($rFloorId == $defaultOfficeId || (!$rFloorId && $loop->first));
                                    $floorName = $rFloor?->name ?? ($isDefaultBranch ? ($defaultOffice?->name ?? __('Main Office')) : __('Branch'));
                                @endphp
                                <option value="{{ $r->id }}" data-floor-id="{{ $rFloorId }}" data-floor-name="{{ $floorName }}" data-is-default="{{ $isDefaultBranch ? '1' : '0' }}">
                                    🏢 {{ $r->name }} ({{ ucfirst($r->type) }}) — [{{ $floorName }}{{ $isDefaultBranch ? ' ⭐ ' . __('Current Branch') : '' }}]
                                </option>
                            @endforeach
                        </select>
                        <div id="invite-room-branch-warning" style="display: none; background: rgba(214, 162, 58, 0.15); border: 1px solid rgba(214, 162, 58, 0.4); border-radius: 8px; padding: 10px 12px; margin-top: 8px; font-size: 12px; color: #D6A23A; line-height: 1.4;">
                            <span>⚠️ <strong>{{ __('Notice') }}:</strong></span>
                            <span id="invite-room-warning-text">{{ __('This room belongs to a different office branch. Make sure you switch to this branch to meet your guest.') }}</span>
                        </div>
                    </div>

                    <div>
                        <label style="display: block; font-size: 12px; font-weight: 700; color: var(--text-secondary); margin-bottom: 6px;">{{ __('Guest Name / Label') }}</label>
                        <input type="text" id="invite-guest-name" value="Investor / Partner" placeholder="e.g. Sarah Miller" style="width: 100%; background: var(--bg-elevated); border: 1px solid var(--border-color); border-radius: 8px; padding: 10px; color: var(--text-primary); outline: none; font-size: 13px; font-weight: 600;">
                    </div>

                    <div>
                        <label style="display: block; font-size: 12px; font-weight: 700; color: var(--text-secondary); margin-bottom: 6px;">{{ __('Link Expiration') }}</label>
                        <select id="invite-guest-hours" style="width: 100%; background: var(--bg-elevated); border: 1px solid var(--border-color); border-radius: 8px; padding: 10px; color: var(--text-primary); outline: none; font-size: 13px; font-weight: 600;">
                            <option value="1">1 Hour</option>
                            <option value="12">12 Hours</option>
                            <option value="24" selected>24 Hours (1 Day)</option>
                            <option value="72">72 Hours (3 Days)</option>
                        </select>
                    </div>

                    <button onclick="generateGuestLink()" id="btn-generate-guest" style="margin-top: 6px; background: linear-gradient(135deg, #10b981, #059669); color: white; font-weight: 800; border: none; border-radius: 10px; padding: 12px; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 8px; font-size: 14px; box-shadow: 0 4px 14px rgba(16, 185, 129, 0.25);">
                        <span>⚡</span> {{ __('Generate Instant Guest Link') }}
                    </button>

                    <div id="guest-result-box" style="display: none; background: rgba(16, 185, 129, 0.1); border: 1px solid rgba(16, 185, 129, 0.3); border-radius: 10px; padding: 12px; margin-top: 10px;">
                        <div style="font-size: 11px; font-weight: 800; color: #34d399; text-transform: uppercase; margin-bottom: 6px;">✅ Invitation Link Ready!</div>
                        <input type="text" id="guest-link-output" readonly style="width: 100%; background: var(--bg-elevated); border: 1px solid var(--border-color); border-radius: 6px; padding: 8px; color: var(--brand-teal); font-size: 12px; font-family: monospace; margin-bottom: 8px;">
                        <div style="display: flex; gap: 8px;">
                            <button type="button" onclick="copyModalGuestLink(this)" id="btn-copy-link" style="flex: 1; background: var(--brand-primary); color: white; font-weight: 700; border: none; border-radius: 6px; padding: 8px; cursor: pointer; font-size: 12px;">
                                📋 {{ __('Copy Link') }}
                            </button>
                            <a id="guest-open-link" href="#" target="_blank" style="background: var(--bg-elevated); border: 1px solid var(--border-color); color: var(--text-primary); font-weight: 700; text-decoration: none; border-radius: 6px; padding: 8px 12px; font-size: 12px; display: flex; align-items: center;">
                                👁️ {{ __('Open') }}
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Member Form (Direct Member Add & Invite) -->
            <div id="member-tab-content" style="display: none;">
                <form method="POST" action="{{ route('organization.members.store') }}" style="display: flex; flex-direction: column; gap: 14px;">
                    @csrf
                    <div>
                        <label style="display: block; font-size: 12px; font-weight: 700; color: var(--text-secondary); margin-bottom: 6px;">{{ __('Full Name (الاسم بالكامل)') }} *</label>
                        <input type="text" name="name" required placeholder="e.g. Ahmed Ali" style="width: 100%; background: var(--bg-elevated); border: 1px solid var(--border-color); border-radius: 8px; padding: 10px; color: var(--text-primary); outline: none; font-size: 13px; font-weight: 600;">
                    </div>

                    <div>
                        <label style="display: block; font-size: 12px; font-weight: 700; color: var(--text-secondary); margin-bottom: 6px;">{{ __('Email Address (البريد الإلكتروني)') }} *</label>
                        <input type="email" name="email" required placeholder="colleague@company.com" style="width: 100%; background: var(--bg-elevated); border: 1px solid var(--border-color); border-radius: 8px; padding: 10px; color: var(--text-primary); outline: none; font-size: 13px; font-weight: 600;">
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                        <div>
                            <label style="display: block; font-size: 12px; font-weight: 700; color: var(--text-secondary); margin-bottom: 6px;">{{ __('Access Role (الدور والصلاحية)') }} *</label>
                            <select name="role_id" required style="width: 100%; background: var(--bg-elevated); border: 1px solid var(--border-color); border-radius: 8px; padding: 10px; color: var(--text-primary); outline: none; font-size: 13px; font-weight: 600;">
                                @foreach($roles as $role)
                                    <option value="{{ $role->id }}">{{ $role->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label style="display: block; font-size: 12px; font-weight: 700; color: var(--text-secondary); margin-bottom: 6px;">{{ __('Initial Password (كلمة المرور)') }}</label>
                            <input type="password" name="password" minlength="8" placeholder="Default: Password@1234" style="width: 100%; background: var(--bg-elevated); border: 1px solid var(--border-color); border-radius: 8px; padding: 10px; color: var(--text-primary); outline: none; font-size: 13px; font-weight: 600;">
                        </div>
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                        <div>
                            <label style="display: block; font-size: 12px; font-weight: 700; color: var(--text-secondary); margin-bottom: 6px;">{{ __('Department (القسم)') }}</label>
                            <select name="department_id" onchange="filterTeamsForInvite(this.value)" style="width: 100%; background: var(--bg-elevated); border: 1px solid var(--border-color); border-radius: 8px; padding: 10px; color: var(--text-primary); outline: none; font-size: 13px; font-weight: 600;">
                                <option value="">— {{ __('No Department') }} —</option>
                                @foreach($departments as $d)
                                    <option value="{{ $d->id }}">{{ $d->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label style="display: block; font-size: 12px; font-weight: 700; color: var(--text-secondary); margin-bottom: 6px;">{{ __('Sub-Team (الفريق الفرعي)') }}</label>
                            <select name="team_id" id="invite-team-select" style="width: 100%; background: var(--bg-elevated); border: 1px solid var(--border-color); border-radius: 8px; padding: 10px; color: var(--text-primary); outline: none; font-size: 13px; font-weight: 600;">
                                <option value="">— {{ __('No Team') }} —</option>
                            </select>
                        </div>
                    </div>

                    <div style="display: grid; grid-template-columns: 1.2fr 0.8fr; gap: 12px;">
                        <div>
                            <label style="display: block; font-size: 12px; font-weight: 700; color: var(--text-secondary); margin-bottom: 6px;">{{ __('Job Title (المسمى الوظيفي)') }}</label>
                            <input type="text" name="job_title" placeholder="e.g. Senior Software Architect" style="width: 100%; background: var(--bg-elevated); border: 1px solid var(--border-color); border-radius: 8px; padding: 10px; color: var(--text-primary); outline: none; font-size: 13px; font-weight: 600;">
                        </div>
                        <div>
                            <label style="display: block; font-size: 12px; font-weight: 700; color: var(--text-secondary); margin-bottom: 6px;">{{ __('Status (الحالة)') }}</label>
                            <select name="status" style="width: 100%; background: var(--bg-elevated); border: 1px solid var(--border-color); border-radius: 8px; padding: 10px; color: var(--text-primary); outline: none; font-size: 13px; font-weight: 600;">
                                <option value="active">🟢 {{ __('Active (نشط)') }}</option>
                                <option value="invited">✉️ {{ __('Invited (مدعو)') }}</option>
                            </select>
                        </div>
                    </div>

                    <button type="submit" class="header-btn btn-primary" style="margin-top: 6px; padding: 12px; font-size: 14px; justify-content: center;">
                        <span>👤</span> {{ __('Create / Add Team Member (إضافة المستخدم)') }}
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal: Department Create / Edit -->
    <div id="department-modal" class="modal-overlay">
        <div class="modal-card">
            <div class="modal-header">
                <h3 class="modal-title" id="department-modal-title">🏛️ {{ __('New Department') }}</h3>
                <button onclick="closeDepartmentModal()" class="modal-close">✕</button>
            </div>
            <form id="department-form" method="POST" action="{{ route('departments.store') }}" style="display: flex; flex-direction: column; gap: 14px;">
                @csrf
                <div id="department-method-field"></div>
                <div>
                    <label style="display: block; font-size: 12px; font-weight: 700; color: var(--text-secondary); margin-bottom: 6px;">{{ __('Department Name') }}</label>
                    <input type="text" name="name" id="department-name-input" required placeholder="e.g. Engineering & IT, Marketing, Sales" style="width: 100%; background: var(--bg-elevated); border: 1px solid var(--border-color); border-radius: 8px; padding: 10px; color: var(--text-primary); outline: none; font-size: 13px; font-weight: 600;">
                </div>
                <button type="submit" class="header-btn btn-primary" style="margin-top: 6px; padding: 12px; font-size: 14px; justify-content: center;">
                    💾 {{ __('Save Department') }}
                </button>
            </form>
        </div>
    </div>

    <!-- Modal: Team Create -->
    <div id="team-modal" class="modal-overlay">
        <div class="modal-card">
            <div class="modal-header">
                <h3 class="modal-title" id="team-modal-title">👥 {{ __('Add Sub-Team') }}</h3>
                <button onclick="closeTeamModal()" class="modal-close">✕</button>
            </div>
            <form method="POST" action="{{ route('teams.store') }}" style="display: flex; flex-direction: column; gap: 14px;">
                @csrf
                <input type="hidden" name="department_id" id="team-department-id">
                <div>
                    <label style="display: block; font-size: 12px; font-weight: 700; color: var(--text-secondary); margin-bottom: 6px;">{{ __('Team Name') }}</label>
                    <input type="text" name="name" required placeholder="e.g. Frontend Team, Enterprise Sales, UI/UX Design" style="width: 100%; background: var(--bg-elevated); border: 1px solid var(--border-color); border-radius: 8px; padding: 10px; color: var(--text-primary); outline: none; font-size: 13px; font-weight: 600;">
                </div>
                <button type="submit" class="header-btn btn-primary" style="margin-top: 6px; padding: 12px; font-size: 14px; justify-content: center;">
                    💾 {{ __('Add Team') }}
                </button>
            </form>
        </div>
    </div>

    <!-- Modal: Assign Member to Department / Team / Role -->
    <div id="assign-modal" class="modal-overlay">
        <div class="modal-card">
            <div class="modal-header">
                <h3 class="modal-title">⚙️ {{ __('Assign Department & Role') }}</h3>
                <button onclick="closeAssignModal()" class="modal-close">✕</button>
            </div>
            <form id="assign-form" method="POST" action="" style="display: flex; flex-direction: column; gap: 14px;">
                @csrf
                <div>
                    <label style="display: block; font-size: 12px; font-weight: 700; color: var(--text-secondary); margin-bottom: 4px;">{{ __('Employee / Member') }}</label>
                    <div id="assign-member-name" style="font-size: 14px; font-weight: 800; color: var(--text-primary); background: var(--bg-elevated); padding: 8px 12px; border-radius: 8px; border: 1px solid var(--border-color);">
                        Member Name
                    </div>
                </div>

                <div>
                    <label style="display: block; font-size: 12px; font-weight: 700; color: var(--text-secondary); margin-bottom: 4px;">{{ __('Department') }}</label>
                    <select name="department_id" id="assign-dept-select" onchange="filterTeamsForAssign(this.value)" style="width: 100%; background: var(--bg-elevated); border: 1px solid var(--border-color); border-radius: 8px; padding: 10px; color: var(--text-primary); outline: none; font-size: 13px; font-weight: 600;">
                        <option value="">— {{ __('No Department') }} —</option>
                        @foreach($departments as $d)
                            <option value="{{ $d->id }}">{{ $d->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label style="display: block; font-size: 12px; font-weight: 700; color: var(--text-secondary); margin-bottom: 4px;">{{ __('Sub-Team') }}</label>
                    <select name="team_id" id="assign-team-select" style="width: 100%; background: var(--bg-elevated); border: 1px solid var(--border-color); border-radius: 8px; padding: 10px; color: var(--text-primary); outline: none; font-size: 13px; font-weight: 600;">
                        <option value="">— {{ __('No Team') }} —</option>
                    </select>
                </div>

                <div>
                    <label style="display: block; font-size: 12px; font-weight: 700; color: var(--text-secondary); margin-bottom: 4px;">{{ __('Job Title') }}</label>
                    <input type="text" name="job_title" id="assign-job-title" placeholder="e.g. Lead Software Architect, Growth Specialist" style="width: 100%; background: var(--bg-elevated); border: 1px solid var(--border-color); border-radius: 8px; padding: 10px; color: var(--text-primary); outline: none; font-size: 13px; font-weight: 600;">
                </div>

                <div>
                    <label style="display: block; font-size: 12px; font-weight: 700; color: var(--text-secondary); margin-bottom: 4px;">{{ __('Access Role') }}</label>
                    <select name="role_id" id="assign-role-select" style="width: 100%; background: var(--bg-elevated); border: 1px solid var(--border-color); border-radius: 8px; padding: 10px; color: var(--text-primary); outline: none; font-size: 13px; font-weight: 600;">
                        @foreach($roles as $role)
                            <option value="{{ $role->id }}">{{ $role->name }}</option>
                        @endforeach
                    </select>
                </div>

                <button type="submit" class="header-btn btn-primary" style="margin-top: 6px; padding: 12px; font-size: 14px; justify-content: center;">
                    💾 {{ __('Save Assignment') }}
                </button>
            </form>
        </div>
    </div>

    <!-- Modal: Edit Complete Member Information -->
    <div id="edit-member-modal" class="modal-overlay">
        <div class="modal-card" style="max-width: 520px;">
            <div class="modal-header">
                <h3 class="modal-title">✏️ {{ __('Edit Team Member (تعديل بيانات المستخدم)') }}</h3>
                <button onclick="closeEditMemberModal()" class="modal-close">✕</button>
            </div>
            <form id="edit-member-form" method="POST" action="" style="display: flex; flex-direction: column; gap: 14px;">
                @csrf
                @method('PUT')
                
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                    <div>
                        <label style="display: block; font-size: 12px; font-weight: 700; color: var(--text-secondary); margin-bottom: 4px;">{{ __('Full Name (الاسم)') }} *</label>
                        <input type="text" name="name" id="edit-member-name-input" required style="width: 100%; background: var(--bg-elevated); border: 1px solid var(--border-color); border-radius: 8px; padding: 10px; color: var(--text-primary); outline: none; font-size: 13px; font-weight: 600;">
                    </div>
                    <div>
                        <label style="display: block; font-size: 12px; font-weight: 700; color: var(--text-secondary); margin-bottom: 4px;">{{ __('Email Address (البريد الإلكتروني)') }} *</label>
                        <input type="email" name="email" id="edit-member-email-input" required style="width: 100%; background: var(--bg-elevated); border: 1px solid var(--border-color); border-radius: 8px; padding: 10px; color: var(--text-primary); outline: none; font-size: 13px; font-weight: 600;">
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                    <div>
                        <label style="display: block; font-size: 12px; font-weight: 700; color: var(--text-secondary); margin-bottom: 4px;">{{ __('Department (القسم)') }}</label>
                        <select name="department_id" id="edit-member-dept-select" onchange="filterTeamsForEditMember(this.value)" style="width: 100%; background: var(--bg-elevated); border: 1px solid var(--border-color); border-radius: 8px; padding: 10px; color: var(--text-primary); outline: none; font-size: 13px; font-weight: 600;">
                            <option value="">— {{ __('No Department') }} —</option>
                            @foreach($departments as $d)
                                <option value="{{ $d->id }}">{{ $d->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label style="display: block; font-size: 12px; font-weight: 700; color: var(--text-secondary); margin-bottom: 4px;">{{ __('Sub-Team (الفريق الفرعي)') }}</label>
                        <select name="team_id" id="edit-member-team-select" style="width: 100%; background: var(--bg-elevated); border: 1px solid var(--border-color); border-radius: 8px; padding: 10px; color: var(--text-primary); outline: none; font-size: 13px; font-weight: 600;">
                            <option value="">— {{ __('No Team') }} —</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label style="display: block; font-size: 12px; font-weight: 700; color: var(--text-secondary); margin-bottom: 4px;">{{ __('Job Title (المسمى الوظيفي)') }}</label>
                    <input type="text" name="job_title" id="edit-member-job-title" placeholder="e.g. Senior Project Manager, Software Engineer" style="width: 100%; background: var(--bg-elevated); border: 1px solid var(--border-color); border-radius: 8px; padding: 10px; color: var(--text-primary); outline: none; font-size: 13px; font-weight: 600;">
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                    <div>
                        <label style="display: block; font-size: 12px; font-weight: 700; color: var(--text-secondary); margin-bottom: 4px;">{{ __('Access Role (الدور / الصلاحية)') }} *</label>
                        <select name="role_id" id="edit-member-role-select" required style="width: 100%; background: var(--bg-elevated); border: 1px solid var(--border-color); border-radius: 8px; padding: 10px; color: var(--text-primary); outline: none; font-size: 13px; font-weight: 600;">
                            @foreach($roles as $role)
                                <option value="{{ $role->id }}">{{ $role->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label style="display: block; font-size: 12px; font-weight: 700; color: var(--text-secondary); margin-bottom: 4px;">{{ __('Account Status (حالة الحساب)') }} *</label>
                        <select name="status" id="edit-member-status-select" required style="width: 100%; background: var(--bg-elevated); border: 1px solid var(--border-color); border-radius: 8px; padding: 10px; color: var(--text-primary); outline: none; font-size: 13px; font-weight: 600;">
                            <option value="active">🟢 {{ __('Active (نشط)') }}</option>
                            <option value="suspended">🔴 {{ __('Suspended (معلق)') }}</option>
                            <option value="invited">✉️ {{ __('Invited (مدعو)') }}</option>
                        </select>
                    </div>
                </div>

                <!-- Granular Office Access Permissions -->
                <div style="background: var(--bg-surface-subtle); border: 1px solid var(--border-color); border-radius: 10px; padding: 12px;">
                    <label style="display: block; font-size: 12px; font-weight: 800; color: var(--text-primary); margin-bottom: 4px;">
                        🏢 {{ __('Allowed Offices / الفروع المصرح بدخولها') }}
                    </label>
                    <div style="font-size: 11px; color: var(--text-muted); margin-bottom: 8px;">
                        {{ __('Select which branches this member can enter (Leave all unchecked for full company access).') }}
                    </div>
                    <div style="display: flex; flex-direction: column; gap: 6px; max-height: 110px; overflow-y: auto;">
                        @foreach($offices as $off)
                        <label style="display: flex; align-items: center; gap: 8px; font-size: 12px; color: var(--text-primary); cursor: pointer;">
                            <input type="checkbox" name="allowed_offices[]" value="{{ $off->id }}" class="edit-member-office-cb" id="edit-office-{{ $off->id }}">
                            <span>🏢 <strong>{{ $off->name }}</strong> ({{ $off->city_location ?: __('Primary') }})</span>
                        </label>
                        @endforeach
                    </div>
                </div>

                <!-- Granular Room Access Permissions -->
                <div style="background: var(--bg-surface-subtle); border: 1px solid var(--border-color); border-radius: 10px; padding: 12px;">
                    <label style="display: block; font-size: 12px; font-weight: 800; color: var(--text-primary); margin-bottom: 4px;">
                        🚪 {{ __('Allowed Rooms / الغرف المصرح بدخولها') }}
                    </label>
                    <div style="font-size: 11px; color: var(--text-muted); margin-bottom: 8px;">
                        {{ __('Select specific private/conference rooms this user is allowed to access.') }}
                    </div>
                    <div style="display: flex; flex-direction: column; gap: 8px; max-height: 130px; overflow-y: auto;">
                        @foreach($offices as $off)
                            @if($off->rooms->count() > 0)
                            <div style="border-bottom: 1px dashed var(--border-color); padding-bottom: 4px; margin-bottom: 4px;">
                                <div style="font-size: 11px; font-weight: 800; color: var(--brand-forest); margin-bottom: 4px;">
                                    🏢 {{ $off->name }}:
                                </div>
                                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 6px;">
                                    @foreach($off->rooms as $rm)
                                    <label style="display: flex; align-items: center; gap: 6px; font-size: 11px; color: var(--text-primary); cursor: pointer;">
                                        <input type="checkbox" name="allowed_rooms[]" value="{{ $rm->id }}" class="edit-member-room-cb" id="edit-room-{{ $rm->id }}">
                                        <span>🚪 {{ $rm->name }}</span>
                                    </label>
                                    @endforeach
                                </div>
                            </div>
                            @endif
                        @endforeach
                    </div>
                </div>

                <button type="submit" class="header-btn btn-primary" style="margin-top: 8px; padding: 12px; font-size: 14px; justify-content: center;">
                    💾 {{ __('Save Member Changes (حفظ التعديلات والصلاحيات)') }}
                </button>
            </form>
        </div>
    </div>

    <!-- Modal: New Office Branch -->
    <div id="new-office-modal" class="modal-overlay">
        <div class="modal-card" style="max-width: 480px;">
            <div class="modal-header">
                <h3 class="modal-title">🏢 {{ __('Add New Office Branch (إضافة فرع جديد)') }}</h3>
                <button onclick="closeNewOfficeModal()" class="modal-close">✕</button>
            </div>
            <form method="POST" action="{{ route('offices.store') }}" style="display: flex; flex-direction: column; gap: 14px;">
                @csrf
                <div>
                    <label style="display: block; font-size: 12px; font-weight: 700; color: var(--text-secondary); margin-bottom: 4px;">{{ __('Office Branch Name (اسم الفرع / المكتب)') }} *</label>
                    <input type="text" name="name" required placeholder="e.g. Cairo Branch, Riyadh HQ, Dubai Innovation Hub" style="width: 100%; background: var(--bg-elevated); border: 1px solid var(--border-color); border-radius: 8px; padding: 10px; color: var(--text-primary); outline: none; font-size: 13px; font-weight: 600;">
                </div>

                <div>
                    <label style="display: block; font-size: 12px; font-weight: 700; color: var(--text-secondary); margin-bottom: 4px;">{{ __('City / Location (المدينة / الدولة)') }}</label>
                    <input type="text" name="city_location" placeholder="e.g. Cairo, Egypt or Riyadh, KSA" style="width: 100%; background: var(--bg-elevated); border: 1px solid var(--border-color); border-radius: 8px; padding: 10px; color: var(--text-primary); outline: none; font-size: 13px; font-weight: 600;">
                </div>

                <div>
                    <label style="display: block; font-size: 12px; font-weight: 700; color: var(--text-secondary); margin-bottom: 4px;">{{ __('Description (الوصف)') }}</label>
                    <textarea name="description" rows="3" placeholder="Brief description of this branch and its teams..." style="width: 100%; background: var(--bg-elevated); border: 1px solid var(--border-color); border-radius: 8px; padding: 10px; color: var(--text-primary); outline: none; font-size: 13px; font-weight: 600;"></textarea>
                </div>

                <div>
                    <label style="display: flex; align-items: center; gap: 8px; font-size: 13px; font-weight: 700; color: var(--text-primary); cursor: pointer;">
                        <input type="checkbox" name="is_default" value="1">
                        <span>⭐ {{ __('Set as Primary / Default Office (تعيين كمقر رئيسي)') }}</span>
                    </label>
                </div>

                <button type="submit" class="header-btn btn-primary" style="margin-top: 6px; padding: 12px; font-size: 14px; justify-content: center;">
                    🏢 {{ __('Create Office Branch (إنشاء الفرع)') }}
                </button>
            </form>
        </div>
    </div>

    <!-- Modal: Edit Office Branch -->
    <div id="edit-office-modal" class="modal-overlay">
        <div class="modal-card" style="max-width: 480px;">
            <div class="modal-header">
                <h3 class="modal-title">✏️ {{ __('Edit Office Branch (تعديل بيانات الفرع)') }}</h3>
                <button onclick="closeEditOfficeModal()" class="modal-close">✕</button>
            </div>
            <form id="edit-office-form" method="POST" action="" style="display: flex; flex-direction: column; gap: 14px;">
                @csrf
                @method('PUT')
                <div>
                    <label style="display: block; font-size: 12px; font-weight: 700; color: var(--text-secondary); margin-bottom: 4px;">{{ __('Office Branch Name') }} *</label>
                    <input type="text" name="name" id="edit-office-name-input" required style="width: 100%; background: var(--bg-elevated); border: 1px solid var(--border-color); border-radius: 8px; padding: 10px; color: var(--text-primary); outline: none; font-size: 13px; font-weight: 600;">
                </div>

                <div>
                    <label style="display: block; font-size: 12px; font-weight: 700; color: var(--text-secondary); margin-bottom: 4px;">{{ __('City / Location') }}</label>
                    <input type="text" name="city_location" id="edit-office-city-input" style="width: 100%; background: var(--bg-elevated); border: 1px solid var(--border-color); border-radius: 8px; padding: 10px; color: var(--text-primary); outline: none; font-size: 13px; font-weight: 600;">
                </div>

                <div>
                    <label style="display: block; font-size: 12px; font-weight: 700; color: var(--text-secondary); margin-bottom: 4px;">{{ __('Description') }}</label>
                    <textarea name="description" id="edit-office-desc-input" rows="3" style="width: 100%; background: var(--bg-elevated); border: 1px solid var(--border-color); border-radius: 8px; padding: 10px; color: var(--text-primary); outline: none; font-size: 13px; font-weight: 600;"></textarea>
                </div>

                <div>
                    <label style="display: flex; align-items: center; gap: 8px; font-size: 13px; font-weight: 700; color: var(--text-primary); cursor: pointer;">
                        <input type="checkbox" name="is_default" id="edit-office-default-input" value="1">
                        <span>⭐ {{ __('Set as Primary / Default Office') }}</span>
                    </label>
                </div>

                <button type="submit" class="header-btn btn-primary" style="margin-top: 6px; padding: 12px; font-size: 14px; justify-content: center;">
                    💾 {{ __('Save Branch Details') }}
                </button>
            </form>
        </div>
    </div>

    <!-- Modal: Change Member Password -->
    <div id="change-member-password-modal" class="modal-overlay">
        <div class="modal-card" style="max-width: 440px;">
            <div class="modal-header">
                <h3 class="modal-title">🔑 {{ __('Reset Member Password (تغيير كلمة المرور)') }}</h3>
                <button onclick="closeChangeMemberPasswordModal()" class="modal-close">✕</button>
            </div>
            <form id="change-member-password-form" method="POST" action="" style="display: flex; flex-direction: column; gap: 14px;">
                @csrf
                <div>
                    <label style="display: block; font-size: 12px; font-weight: 700; color: var(--text-secondary); margin-bottom: 4px;">{{ __('User Name (المستخدم)') }}</label>
                    <div id="change-password-user-name" style="font-size: 14px; font-weight: 800; color: var(--brand-forest); background: var(--bg-elevated); padding: 8px 12px; border-radius: 8px; border: 1px solid var(--border-color);">
                        User Name
                    </div>
                </div>

                <div>
                    <label style="display: block; font-size: 12px; font-weight: 700; color: var(--text-secondary); margin-bottom: 4px;">{{ __('New Password (كلمة المرور الجديدة)') }} *</label>
                    <input type="password" name="password" required minlength="8" placeholder="••••••••" style="width: 100%; background: var(--bg-elevated); border: 1px solid var(--border-color); border-radius: 8px; padding: 10px; color: var(--text-primary); outline: none; font-size: 13px; font-weight: 600;">
                    <span style="font-size: 11px; color: var(--text-muted); margin-top: 2px; display: block;">{{ __('Minimum 8 characters') }}</span>
                </div>

                <div>
                    <label style="display: block; font-size: 12px; font-weight: 700; color: var(--text-secondary); margin-bottom: 4px;">{{ __('Confirm New Password (تأكيد كلمة المرور)') }} *</label>
                    <input type="password" name="password_confirmation" required minlength="8" placeholder="••••••••" style="width: 100%; background: var(--bg-elevated); border: 1px solid var(--border-color); border-radius: 8px; padding: 10px; color: var(--text-primary); outline: none; font-size: 13px; font-weight: 600;">
                </div>

                <button type="submit" class="header-btn btn-primary" style="margin-top: 8px; padding: 12px; font-size: 14px; justify-content: center; background: linear-gradient(135deg, #D6A23A 0%, #B88628 100%);">
                    🔑 {{ __('Update Password (تعيين كلمة المرور)') }}
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
    <div id="move-task-modal" class="modal">
        <div class="modal-box" style="max-width: 420px;">
            <div class="modal-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
                <h3 style="font-size: 16px; font-weight: 900; color: var(--text-primary);">➡️ {{ __('Move Task to Project') }}</h3>
                <button type="button" onclick="closeMoveTaskModal()" style="background: none; border: none; font-size: 18px; color: var(--text-muted); cursor: pointer;">✕</button>
            </div>
            <form onsubmit="submitMoveTask(event)" style="display: flex; flex-direction: column; gap: 14px; margin-top: 14px;">
                <input type="hidden" id="move-task-id-input">
                <div>
                    <label style="display: block; font-size: 11px; font-weight: 800; color: var(--text-secondary); margin-bottom: 6px; text-transform: uppercase;">
                        📁 {{ __('Target Project') }}
                    </label>
                    <select id="move-target-project-select" required style="width: 100%; background: var(--bg-surface-subtle); border: 1px solid var(--border-color); border-radius: 10px; padding: 10px 14px; color: var(--text-primary); font-size: 13px; font-weight: 600;">
                        @foreach($projects as $p)
                            <option value="{{ $p->id }}">{{ $p->name }} ({{ $p->code }})</option>
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

    