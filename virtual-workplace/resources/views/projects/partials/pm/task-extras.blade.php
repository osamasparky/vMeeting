{{-- Task inspector: assignees and labels. Re-rendered on every 'hub:task-rendered'.
     Later phases add their own rows (followers, relations) through PM.extraRows. --}}
<script nonce="{{ $cspNonce ?? '' }}">
    PM.dirty = false;
    PM.extraRows = PM.extraRows || [];

    function pmExtrasRow(label, bodyHtml) {
        return `<div class="pm-extras-row"><span class="pm-extras-label">${label}</span><div class="pm-row">${bodyHtml}</div></div>`;
    }

    function pmRenderTaskExtras(t) {
        const box = document.getElementById('pm-task-extras');
        if (!box || !t) return;
        PM.currentTask = t;

        const assignees = (t.assignees && t.assignees.length) ? t.assignees : (t.assignee ? [t.assignee] : []);
        const assigneeHtml = assignees.length
            ? assignees.map(a => `<span class="pm-row" style="gap: var(--ula-space-2);"><span class="pm-avatar">${pmInitial(a.name)}</span><span style="font-size: var(--ula-size-sm); font-weight: 700;">${pmEsc(a.name)}</span></span>`).join('')
            : `<span class="pm-muted">{{ __('Unassigned') }}</span>`;
        const assigneeBtn = PM.can.assign
            ? `<span class="pm-picker"><button type="button" class="pm-picker-trigger" aria-haspopup="listbox" aria-expanded="false" onclick="pmPickAssignees(this)"><span class="material-symbols-rounded" aria-hidden="true">group_add</span>{{ __('pm.edit_assignees') }}</button></span>`
            : '';

        const labels = t.labels || [];
        const labelHtml = labels.length ? labels.map(pmLabelChip).join('') : `<span class="pm-muted">{{ __('pm.no_labels') }}</span>`;
        const labelBtn = PM.can.editTasks
            ? `<span class="pm-picker"><button type="button" class="pm-picker-trigger" aria-haspopup="listbox" aria-expanded="false" onclick="pmPickLabels(this)"><span class="material-symbols-rounded" aria-hidden="true">sell</span>{{ __('pm.edit_labels') }}</button></span>`
            : '';

        box.innerHTML = pmExtrasRow(@json(__('pm.assignees')), assigneeHtml + assigneeBtn)
            + pmExtrasRow(@json(__('pm.labels')), labelHtml + labelBtn)
            + PM.extraRows.map(fn => fn(t)).join('');
    }

    function pmPickAssignees(trigger) {
        const t = PM.currentTask;
        const current = (t.assignees || []).map(a => a.id);
        if (!current.length && t.assignee_id) current.push(t.assignee_id);
        const options = PM.members.map(m => ({ id: m.id, html: `<span class="pm-avatar">${pmInitial(m.name)}</span><span>${pmEsc(m.name)}</span>` }));
        let order = [...current];
        pmOpenPicker(trigger, options, current, async ids => {
            // Keep the existing primary first; newly ticked people go after it.
            order = order.filter(id => ids.includes(id)).concat(ids.filter(id => !order.includes(id)));
            try {
                const data = await pmApi('PUT', `/tasks/${t.id}/assignees`, { user_ids: order });
                t.assignees = data.task.assignees;
                t.assignee = data.task.assignee;
                t.assignee_id = data.task.assignee_id;
                PM.dirty = true;
                const host = document.getElementById('pm-task-extras');
                pmRenderTaskExtras(t);
                host.querySelector('[onclick="pmPickAssignees(this)"]')?.click();
            } catch (e) { showHubToast(pmEsc(e.message)); }
        });
    }

    function pmPickLabels(trigger) {
        const t = PM.currentTask;
        const current = (t.labels || []).map(l => String(l.id));
        const footer = PM.can.editProject
            ? `<form class="pm-row" style="padding: var(--ula-space-2); border-top: 1px solid var(--ula-border-subtle); margin-top: var(--ula-space-2);" onsubmit="pmCreateLabelInline(event)">
                   <input class="pm-field pm-grow" name="name" maxlength="50" required placeholder="{{ __('pm.new_label') }}" aria-label="{{ __('pm.new_label') }}">
                   <button type="submit" class="ula-btn ula-btn--primary ula-btn--sm">{{ __('pm.add') }}</button>
               </form>`
            : '';
        const options = PM.labels.map(l => ({ id: l.id, html: pmLabelChip(l) }));
        pmOpenPicker(trigger, options, current, async ids => {
            try {
                const data = await pmApi('PUT', `/tasks/${t.id}/labels`, { label_ids: ids.map(Number) });
                t.labels = data.labels;
                PM.dirty = true;
                const chips = document.querySelector('#pm-task-extras .pm-extras-row:nth-child(2) .pm-row');
                if (chips) {
                    chips.querySelectorAll('.pm-label, .pm-muted').forEach(n => n.remove());
                    chips.insertAdjacentHTML('afterbegin', t.labels.length ? t.labels.map(pmLabelChip).join('') : `<span class="pm-muted">{{ __('pm.no_labels') }}</span>`);
                }
            } catch (e) { showHubToast(pmEsc(e.message)); }
        }, footer);
    }

    async function pmCreateLabelInline(e) {
        e.preventDefault();
        const name = e.target.name.value.trim();
        if (!name) return;
        try {
            const data = await pmApi('POST', `/projects/${PM.projectId}/labels`, { name, tone: PM.tones[PM.labels.length % PM.tones.length] });
            PM.labels.push({ id: data.label.id, name: data.label.name, tone: data.label.tone });
            const t = PM.currentTask;
            const ids = (t.labels || []).map(l => l.id).concat(data.label.id);
            const res = await pmApi('PUT', `/tasks/${t.id}/labels`, { label_ids: ids });
            t.labels = res.labels;
            PM.dirty = true;
            pmRenderTaskExtras(t);
        } catch (err) { showHubToast(pmEsc(err.message)); }
    }

    document.addEventListener('hub:task-rendered', e => pmRenderTaskExtras(e.detail));

    // Board cards and list rows are server-rendered: refresh once the inspector
    // closes if anything shown on them changed.
    const pmOriginalCloseTask = closeTaskDetailsModal;
    closeTaskDetailsModal = function () {
        pmOriginalCloseTask();
        if (PM.dirty) window.location.reload();
    };
</script>
