<script nonce="{{ $cspNonce ?? '' }}">
    /**
     * Inline edit from the task list. Status goes through the workflow
     * endpoint (approval, milestones, goals); other fields use PATCH /tasks.
     */
    async function pmInlineUpdate(taskId, field, value, control) {
        const row = control.closest('tr');
        control.setAttribute('aria-busy', 'true');
        control.disabled = true;
        try {
            const data = field === 'status'
                ? await pmApi('PATCH', `/tasks/${taskId}/status`, { status: value })
                : await pmApi('PATCH', `/tasks/${taskId}`, { [field]: value });
            const task = data.task || {};

            if (field === 'status' && task.status && task.status !== value) {
                // e.g. a member asking for "done" is routed to review for approval.
                control.value = task.status;
                showHubToast(@json(__('pm.sent_for_approval')));
            } else {
                showHubToast(@json(__('pm.saved')));
            }

            // Keep the row's filter attributes in step with the new values.
            if (row) {
                if (field === 'priority') row.dataset.priority = value;
                if (field === 'due_date') row.dataset.due = value || '';
                if (field === 'assignee_id') {
                    row.dataset.assignee = value || 'unassigned';
                    row.dataset.assignees = (task.assignees || []).map(a => a.id).join(',') || (value || '');
                }
            }
            const calTask = PM.tasks.find(t => t.id === taskId);
            if (calTask) {
                if (field === 'priority') calTask.priority = value;
                if (field === 'due_date') calTask.due = value || null;
                if (field === 'status') calTask.status = task.status || value;
                if (field === 'assignee_id') calTask.assignee = value || 'unassigned';
            }
            PM.dirty = true;
        } catch (e) {
            showHubToast(pmEsc(e.message));
            control.value = control.querySelector('option[selected]')?.value ?? control.defaultValue;
        } finally {
            control.disabled = false;
            control.removeAttribute('aria-busy');
        }
    }

    /** Download tasks (with the current filters) or this project's time entries. */
    function pmExport(kind, format) {
        const params = new URLSearchParams({ format, lang: PM.locale });
        let path;
        if (kind === 'tasks') {
            const f = getHubFilterState();
            if (f.priority) params.set('priority', f.priority);
            if (f.label) params.set('label_id', f.label);
            if (f.assignee && f.assignee !== 'unassigned') params.set('assignee_id', f.assignee);
            path = `/projects/${PM.projectId}/export/tasks`;
        } else {
            params.set('project_id', PM.projectId);
            path = '/time/entries/export';
        }
        window.location.href = `/api/v1/organizations/${PM.orgId}${path}?${params}`;
    }

    // The board is server-rendered: after edits elsewhere, opening it reloads once.
    const pmOriginalSwitchSection = switchHubSection;
    switchHubSection = function (tab) {
        if (PM.dirty && tab === 'kanban') {
            window.location.hash = 'kanban';
            window.location.reload();
            return;
        }
        pmOriginalSwitchSection(tab);
        if (tab === 'calendar' && window.pmRenderCalendar) pmRenderCalendar();
        document.dispatchEvent(new CustomEvent('pm:tab-shown', { detail: tab }));
    };
</script>
