@php
    $pmAutomationText = [
        'triggers' => collect(\App\Domains\Projects\Models\ProjectAutomation::TRIGGERS)->mapWithKeys(fn ($t) => [$t => __('pm.trigger_'.$t)]),
        'actions' => collect(\App\Domains\Projects\Models\ProjectAutomation::ACTIONS)->mapWithKeys(fn ($a) => [$a => __('pm.action_'.$a)]),
        'priorities' => collect(\App\Domains\Projects\Models\Task::PRIORITIES)->mapWithKeys(fn ($p) => [$p => __(ucfirst($p))]),
        'notifyGroups' => ['assignees' => __('pm.notify_assignees'), 'reporter' => __('pm.notify_reporter'), 'watchers' => __('pm.notify_watchers'), 'manager' => __('pm.notify_manager')],
    ];
@endphp
<script nonce="{{ $cspNonce ?? '' }}">
    // ── Automations: "when X happens to a task (and it matches Y), do Z" ──
    PM.automations = [];
    PM.editingAutomation = null;
    PM.automationText = @json($pmAutomationText);

    const pmStateName = s => PM.stateNames[s.name] || s.name;
    const pmMemberName = id => PM.members.find(m => m.id === id)?.name || '—';

    /** Options for an action's value, by action type. */
    function pmActionValueOptions(type) {
        const T = PM.automationText;
        switch (type) {
            case 'set_state': return PM.states.map(s => [s.id, pmStateName(s)]);
            case 'set_priority': return Object.entries(T.priorities);
            case 'add_label': return PM.labels.map(l => [l.id, l.name]);
            case 'add_assignee':
            case 'add_watcher': return PM.members.map(m => [m.id, m.name]);
            case 'notify': return [...Object.entries(T.notifyGroups), ...PM.members.map(m => [m.id, m.name])];
            default: return null; // set_due_in_days: a number
        }
    }

    function pmDescribeAction(a) {
        const T = PM.automationText;
        let v;
        switch (a.type) {
            case 'set_state': v = pmStateName(PM.states.find(s => s.id === a.value) || { name: '—' }); break;
            case 'set_priority': v = T.priorities[a.value]; break;
            case 'add_label': v = PM.labels.find(l => l.id === a.value)?.name || '—'; break;
            case 'notify': v = T.notifyGroups[a.value] || pmMemberName(a.value); break;
            case 'set_due_in_days': return `${pmEsc(T.actions[a.type])} <span class="pm-num">+${Number(a.value)}</span>`;
            default: v = pmMemberName(a.value);
        }
        return `${pmEsc(T.actions[a.type])}: <strong>${pmEsc(v)}</strong>`;
    }

    function pmDescribeConditions(c) {
        const parts = [];
        if (c?.state_id) parts.push(pmEsc(pmStateName(PM.states.find(s => s.id === c.state_id) || { name: '—' })));
        if (c?.priority) parts.push(pmEsc(PM.automationText.priorities[c.priority]));
        if (c?.label_id) { const l = PM.labels.find(x => x.id === c.label_id); parts.push(l ? pmLabelChip(l) : '—'); }
        if (c?.assignee) parts.push(pmEsc(c.assignee === 'none' ? @json(__('pm.cond_unassigned')) : c.assignee === 'any' ? @json(__('pm.cond_has_assignee')) : pmMemberName(c.assignee)));
        return parts.join(' · ');
    }

    function pmRenderAutomations() {
        const list = document.getElementById('pm-automation-list');
        if (!list) return;
        if (!PM.automations.length) {
            list.innerHTML = `<div class="pm-empty">{{ __('pm.no_automations') }}</div>`;
            return;
        }
        list.innerHTML = PM.automations.map(r => {
            const conds = pmDescribeConditions(r.conditions);
            return `
            <div class="pm-list-row pm-automation-row ${r.is_active ? '' : 'is-paused'}">
                <span class="material-symbols-rounded pm-automation-icon" aria-hidden="true">${r.is_active ? 'bolt' : 'pause_circle'}</span>
                <div class="pm-grow">
                    <div style="font-weight: 800;" dir="auto">${pmEsc(r.name)}</div>
                    <div class="pm-automation-summary">
                        <span class="pm-label pm-label--stone">{{ __('pm.when') }}</span> ${pmEsc(PM.automationText.triggers[r.trigger])}
                        ${conds ? `<span class="pm-label pm-label--stone">{{ __('pm.only_if') }}</span> ${conds}` : ''}
                        <span class="pm-label pm-label--palm">{{ __('pm.then') }}</span> ${r.actions.map(pmDescribeAction).join(' · ')}
                    </div>
                    <div class="pm-muted">{{ __('pm.automation_runs') }} <span class="pm-num">${r.run_count}</span>${r.last_run_at ? ` · <span class="pm-num">${pmEsc(r.last_run_at.replace('T', ' ').slice(0, 16))}</span>` : ''}</div>
                </div>
                ${PM.can.editProject ? `
                    <button type="button" class="ula-btn ula-btn--ghost ula-btn--sm" aria-pressed="${r.is_active}" onclick="pmToggleAutomation(${r.id})">
                        <span class="material-symbols-rounded" aria-hidden="true">${r.is_active ? 'pause' : 'play_arrow'}</span><span>${r.is_active ? @json(__('pm.pause')) : @json(__('pm.resume'))}</span></button>
                    <button type="button" class="ula-btn ula-btn--ghost ula-btn--sm" onclick="pmEditAutomation(${r.id})">
                        <span class="material-symbols-rounded" aria-hidden="true">edit</span><span>{{ __('pm.edit') }}</span></button>
                    <button type="button" class="ula-btn ula-btn--ghost ula-btn--sm" onclick="pmDeleteAutomation(${r.id})">
                        <span class="material-symbols-rounded" aria-hidden="true">delete</span><span>{{ __('pm.delete') }}</span></button>` : ''}
            </div>`;
        }).join('');
    }

    async function pmLoadAutomations() {
        try {
            const data = await pmApi('GET', `/projects/${PM.projectId}/automations`);
            PM.automations = data.automations;
            pmRenderAutomations();
        } catch (e) { showHubToast(pmEsc(e.message)); }
    }

    // ── Builder ──
    function pmOptions(pairs, selected, emptyLabel) {
        return (emptyLabel !== undefined ? `<option value="">${pmEsc(emptyLabel)}</option>` : '')
            + pairs.map(([v, t]) => `<option value="${pmEsc(String(v))}" ${String(v) === String(selected ?? '') ? 'selected' : ''}>${pmEsc(t)}</option>`).join('');
    }

    function pmActionRow(action = { type: 'set_state', value: null }) {
        const types = Object.entries(PM.automationText.actions);
        const opts = pmActionValueOptions(action.type);
        const value = opts
            ? `<select class="pm-field pm-grow" data-role="value" aria-label="{{ __('pm.action_value') }}">${pmOptions(opts, action.value)}</select>`
            : `<input class="pm-field pm-num" type="number" min="0" max="365" required data-role="value" value="${Number(action.value ?? 1)}" aria-label="{{ __('pm.days') }}">`;
        return `
            <div class="pm-row pm-automation-action">
                <select class="pm-field" data-role="type" aria-label="{{ __('pm.action_type') }}" onchange="pmChangeActionType(this)">${pmOptions(types, action.type)}</select>
                ${value}
                <button type="button" class="ula-btn ula-btn--ghost ula-btn--sm" onclick="pmRemoveAutomationAction(this)" aria-label="{{ __('pm.remove_action') }}">
                    <span class="material-symbols-rounded" aria-hidden="true">close</span></button>
            </div>`;
    }

    function pmSyncActionButtons() {
        const rows = document.querySelectorAll('#pm-automation-actions .pm-automation-action');
        document.getElementById('pm-automation-add-action').hidden = rows.length >= 5;
        rows.forEach(r => r.querySelector('button').disabled = rows.length === 1);
    }

    function pmAddAutomationAction(action) {
        document.getElementById('pm-automation-actions').insertAdjacentHTML('beforeend', pmActionRow(action));
        pmSyncActionButtons();
    }

    function pmRemoveAutomationAction(btn) {
        btn.closest('.pm-automation-action').remove();
        pmSyncActionButtons();
    }

    function pmChangeActionType(select) {
        const row = select.closest('.pm-automation-action');
        row.outerHTML = pmActionRow({ type: select.value, value: null });
        pmSyncActionButtons();
    }

    function pmEditAutomation(id) {
        const rule = id ? PM.automations.find(r => r.id === id) : null;
        PM.editingAutomation = rule?.id ?? null;
        const f = document.getElementById('pm-automation-form');
        const c = rule?.conditions || {};
        f.name.value = rule?.name || '';
        f.trigger.value = rule?.trigger || 'task_created';
        f.cond_state_id.innerHTML = pmOptions(PM.states.map(s => [s.id, pmStateName(s)]), c.state_id, @json(__('pm.cond_any_state')));
        f.cond_priority.value = c.priority || '';
        f.cond_label_id.innerHTML = pmOptions(PM.labels.map(l => [l.id, l.name]), c.label_id, @json(__('pm.cond_any_label')));
        f.cond_assignee.innerHTML = pmOptions([['none', @json(__('pm.cond_unassigned'))], ['any', @json(__('pm.cond_has_assignee'))], ...PM.members.map(m => [m.id, m.name])], c.assignee, @json(__('pm.cond_any_assignee')));
        document.getElementById('pm-automation-actions').innerHTML = '';
        (rule?.actions?.length ? rule.actions : [undefined]).forEach(a => pmAddAutomationAction(a));
        f.hidden = false;
        f.name.focus();
    }

    function pmCloseAutomationForm() {
        document.getElementById('pm-automation-form').hidden = true;
        PM.editingAutomation = null;
        document.getElementById('pm-automation-new-btn')?.focus();
    }

    async function pmSaveAutomation(e) {
        e.preventDefault();
        const f = e.target;
        const body = {
            name: f.name.value.trim(),
            trigger: f.trigger.value,
            conditions: {
                state_id: f.cond_state_id.value ? Number(f.cond_state_id.value) : null,
                priority: f.cond_priority.value || null,
                label_id: f.cond_label_id.value ? Number(f.cond_label_id.value) : null,
                assignee: f.cond_assignee.value || null,
            },
            actions: [...f.querySelectorAll('.pm-automation-action')].map(row => ({
                type: row.querySelector('[data-role=type]').value,
                value: row.querySelector('[data-role=value]').value,
            })),
        };
        try {
            if (PM.editingAutomation) {
                await pmApi('PUT', `/projects/${PM.projectId}/automations/${PM.editingAutomation}`, body);
            } else {
                await pmApi('POST', `/projects/${PM.projectId}/automations`, body);
            }
            pmCloseAutomationForm();
            await pmLoadAutomations();
            showHubToast(@json(__('pm.automation_saved')));
        } catch (err) { showHubToast(pmEsc(err.message)); }
    }

    async function pmToggleAutomation(id) {
        const rule = PM.automations.find(r => r.id === id);
        try {
            await pmApi('PUT', `/projects/${PM.projectId}/automations/${id}`, { is_active: !rule.is_active });
            rule.is_active = !rule.is_active;
            pmRenderAutomations();
        } catch (e) { showHubToast(pmEsc(e.message)); }
    }

    async function pmDeleteAutomation(id) {
        if (!confirm(@json(__('pm.delete_automation_confirm')))) return;
        try {
            await pmApi('DELETE', `/projects/${PM.projectId}/automations/${id}`);
            PM.automations = PM.automations.filter(r => r.id !== id);
            pmRenderAutomations();
        } catch (e) { showHubToast(pmEsc(e.message)); }
    }

    document.addEventListener('pm:settings-panel', e => { if (e.detail === 'automations') pmLoadAutomations(); });
</script>
