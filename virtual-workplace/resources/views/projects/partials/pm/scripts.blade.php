{{-- Project-management feature scripts for the project hub. Each feature lives
     in its own partial; this file wires the shared data and helpers they use. --}}
@php
    $pmCan = fn (string $perm) => $user->isSuperAdmin() || $membership->hasPermission($perm);
    $pmBoot = [
        'orgId' => $organization->id,
        'projectId' => $project->id,
        'userId' => $user->id,
        'can' => [
            'assign' => $pmCan('tasks.assign'),
            'editProject' => $pmCan('projects.edit'),
            'editTasks' => $pmCan('tasks.edit'),
            'deleteTasks' => $pmCan('tasks.delete'),
        ],
        'labels' => $project->labels->map(fn ($l) => ['id' => $l->id, 'name' => $l->name, 'tone' => $l->tone])->values(),
        'members' => $allMembers->filter(fn ($m) => $m->user)->map(fn ($m) => ['id' => $m->user_id, 'name' => $m->user->name])->values(),
        'tones' => \App\Domains\Projects\Models\ProjectLabel::TONES,
        'locale' => app()->getLocale(),
        'statusLabels' => ['backlog' => __('Backlog'), 'ready' => __('Ready'), 'in_progress' => __('In Progress'), 'review' => __('Review'), 'qa' => __('QA'), 'done' => __('Done')],
        // Calendar data: one row per task with the attributes the shared filter reads.
        'tasks' => $tasks->map(fn ($t) => [
            'id' => $t->id,
            'number' => $t->task_number,
            'title' => $t->title,
            'status' => $t->status,
            'priority' => $t->priority,
            'due' => $t->due_date?->format('Y-m-d'),
            'start' => $t->start_date?->format('Y-m-d'),
            'assignee' => $t->assignee_id ?? 'unassigned',
            'assignees' => $t->assignees->pluck('id')->implode(','),
            'labels' => $t->labels->pluck('id')->implode(','),
            'milestone' => $t->milestone_id ?? 'none',
            'canEdit' => $user->can('update', $t),
            'sprint' => $t->sprint_id,
            'points' => $t->story_points,
        ])->values(),
    ];
@endphp
<script nonce="{{ $cspNonce ?? '' }}">
    window.PM = @json($pmBoot);

    /** JSON request against the v1 API for this organization. Throws with the server message. */
    async function pmApi(method, path, body) {
        const res = await fetch(`/api/v1/organizations/${PM.orgId}${path}`, {
            method,
            credentials: 'same-origin',
            headers: { 'Accept': 'application/json', 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF_TOKEN },
            body: body === undefined ? undefined : JSON.stringify(body),
        });
        const data = await res.json().catch(() => ({}));
        if (!res.ok) {
            const firstError = data.errors ? Object.values(data.errors)[0]?.[0] : null;
            throw new Error(firstError || data.message || `HTTP ${res.status}`);
        }
        return data;
    }

    function pmEsc(s) {
        return String(s ?? '').replace(/[&<>"']/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
    }

    function pmLabelChip(label) {
        return `<span class="pm-label pm-label--${pmEsc(label.tone || 'stone')}">${pmEsc(label.name)}</span>`;
    }

    function pmInitial(name) {
        return pmEsc((name || '?').trim().charAt(0));
    }

    /**
     * Multi-select popover anchored to a trigger button. Options are
     * {id, html}; onChange receives the new list of selected ids.
     */
    function pmOpenPicker(trigger, options, selectedIds, onChange, footerHtml) {
        document.querySelectorAll('.pm-picker-menu').forEach(m => m.remove());
        const selected = new Set(selectedIds.map(String));
        const menu = document.createElement('div');
        menu.className = 'pm-picker-menu';
        menu.setAttribute('role', 'listbox');
        menu.setAttribute('aria-multiselectable', 'true');

        const render = () => {
            menu.innerHTML = options.length
                ? options.map(o => `
                    <button type="button" class="pm-picker-option" role="option" data-id="${pmEsc(o.id)}" aria-selected="${selected.has(String(o.id))}">
                        <span class="material-symbols-rounded" aria-hidden="true">${selected.has(String(o.id)) ? 'check_box' : 'check_box_outline_blank'}</span>
                        ${o.html}
                    </button>`).join('')
                : `<div class="pm-muted" style="padding: var(--ula-space-4);">{{ __('pm.nothing_to_pick') }}</div>`;
            if (footerHtml) menu.insertAdjacentHTML('beforeend', footerHtml);
        };
        render();

        menu.addEventListener('click', e => {
            const opt = e.target.closest('.pm-picker-option');
            if (!opt) return;
            e.stopPropagation();
            const id = opt.dataset.id;
            selected.has(id) ? selected.delete(id) : selected.add(id);
            render();
            onChange([...selected]);
        });

        trigger.parentElement.style.position = 'relative';
        trigger.insertAdjacentElement('afterend', menu);
        trigger.setAttribute('aria-expanded', 'true');
        menu.querySelector('.pm-picker-option')?.focus();

        const close = (e) => {
            if (e.type === 'keydown' && e.key !== 'Escape') return;
            if (e.type === 'click' && (menu.contains(e.target) || e.target === trigger)) return;
            menu.remove();
            trigger.setAttribute('aria-expanded', 'false');
            if (e.type === 'keydown') trigger.focus();
            document.removeEventListener('click', close, true);
            document.removeEventListener('keydown', close, true);
        };
        setTimeout(() => {
            document.addEventListener('click', close, true);
            document.addEventListener('keydown', close, true);
        });
        return menu;
    }
</script>

@include('projects.partials.pm.task-extras')
@include('projects.partials.pm.settings-scripts')
@include('projects.partials.pm.list-scripts')
@include('projects.partials.pm.views-scripts')
@include('projects.partials.pm.calendar-scripts')
@include('projects.partials.pm.sprints-scripts')
