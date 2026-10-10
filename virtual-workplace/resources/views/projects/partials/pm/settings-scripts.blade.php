<script nonce="{{ $cspNonce ?? '' }}">
    function pmShowSettingsPanel(key) {
        document.querySelectorAll('#hub-section-settings [role="tabpanel"]').forEach(p => p.style.display = p.id === `pm-settings-${key}` ? '' : 'none');
        document.querySelectorAll('#hub-section-settings [role="tab"]').forEach(b => b.setAttribute('aria-selected', String(b.id === `pm-settings-tab-${key}`)));
        document.dispatchEvent(new CustomEvent('pm:settings-panel', { detail: key }));
    }

    // ── Labels ───────────────────────────────────────────────────────────
    PM.newLabelTone = 'palm';

    function pmToneSwatches(selected, onPick) {
        return PM.tones.map(tone => `
            <button type="button" class="pm-swatch pm-swatch--${tone}" role="radio" aria-checked="${tone === selected}" aria-pressed="${tone === selected}"
                    aria-label="${tone}" data-tone="${tone}" onclick="${onPick}('${tone}', this)"><span></span></button>`).join('');
    }

    function pmPickNewLabelTone(tone) {
        PM.newLabelTone = tone;
        const box = document.getElementById('pm-new-label-tones');
        if (box) box.innerHTML = pmToneSwatches(tone, 'pmPickNewLabelTone');
    }

    function pmRenderLabelList() {
        const list = document.getElementById('pm-label-list');
        if (!list) return;
        if (!PM.labels.length) {
            list.innerHTML = `<div class="pm-empty">{{ __('pm.no_labels_yet') }}</div>`;
            return;
        }
        list.innerHTML = PM.labels.map(l => `
            <div class="pm-list-row" data-label-id="${l.id}">
                <span class="pm-grow">${pmLabelChip(l)}</span>
                ${PM.can.editProject ? `
                    <span class="pm-swatches" role="radiogroup" aria-label="{{ __('pm.label_color') }}">${pmToneSwatches(l.tone, `pmRecolorLabel.bind(null, ${l.id})`)}</span>
                    <button type="button" class="ula-btn ula-btn--ghost ula-btn--sm" onclick="pmRenameLabel(${l.id})">
                        <span class="material-symbols-rounded" aria-hidden="true">edit</span><span>{{ __('pm.rename') }}</span></button>
                    <button type="button" class="ula-btn ula-btn--ghost ula-btn--sm" onclick="pmDeleteLabel(${l.id})">
                        <span class="material-symbols-rounded" aria-hidden="true">delete</span><span>{{ __('pm.delete') }}</span></button>` : ''}
            </div>`).join('');
    }

    async function pmCreateLabel(e) {
        e.preventDefault();
        const name = e.target.name.value.trim();
        try {
            const data = await pmApi('POST', `/projects/${PM.projectId}/labels`, { name, tone: PM.newLabelTone });
            PM.labels.push({ id: data.label.id, name: data.label.name, tone: data.label.tone });
            PM.labels.sort((a, b) => a.name.localeCompare(b.name));
            e.target.reset();
            pmRenderLabelList();
            showHubToast(@json(__('pm.label_created')));
        } catch (err) { showHubToast(pmEsc(err.message)); }
    }

    async function pmRecolorLabel(id, tone) {
        try {
            await pmApi('PATCH', `/projects/${PM.projectId}/labels/${id}`, { tone });
            PM.labels.find(l => l.id === id).tone = tone;
            pmRenderLabelList();
        } catch (err) { showHubToast(pmEsc(err.message)); }
    }

    async function pmRenameLabel(id) {
        const label = PM.labels.find(l => l.id === id);
        const name = prompt(@json(__('pm.rename_label')), label.name);
        if (!name || name.trim() === label.name) return;
        try {
            await pmApi('PATCH', `/projects/${PM.projectId}/labels/${id}`, { name: name.trim() });
            label.name = name.trim();
            pmRenderLabelList();
        } catch (err) { showHubToast(pmEsc(err.message)); }
    }

    async function pmDeleteLabel(id) {
        if (!confirm(@json(__('pm.delete_label_confirm')))) return;
        try {
            await pmApi('DELETE', `/projects/${PM.projectId}/labels/${id}`);
            PM.labels = PM.labels.filter(l => l.id !== id);
            pmRenderLabelList();
        } catch (err) { showHubToast(pmEsc(err.message)); }
    }

    // ── States ───────────────────────────────────────────────────────────
    function pmRenderStateList() {
        const list = document.getElementById('pm-state-list');
        if (!list) return;
        list.innerHTML = PM.states.map((s, i) => `
            <div class="pm-list-row">
                <span class="pm-grow" style="font-weight: 800;">${pmEsc(PM.stateNames[s.name] || s.name)}
                    ${s.is_default ? `<span class="pm-label pm-label--palm" style="margin-inline-start: var(--ula-space-2);">{{ __('pm.default_state') }}</span>` : ''}</span>
                <span class="pm-label pm-label--stone">${pmEsc(PM.groupLabels[s.group] || s.group)}</span>
                ${PM.can.editProject ? `
                    <button type="button" class="ula-btn ula-btn--ghost ula-btn--sm" ${i === 0 ? 'disabled' : ''} onclick="pmUpdateState(${s.id}, {move: 'up'})" aria-label="{{ __('pm.move_up') }} ${pmEsc(s.name)}"><span class="material-symbols-rounded" aria-hidden="true">arrow_upward</span></button>
                    <button type="button" class="ula-btn ula-btn--ghost ula-btn--sm" ${i === PM.states.length - 1 ? 'disabled' : ''} onclick="pmUpdateState(${s.id}, {move: 'down'})" aria-label="{{ __('pm.move_down') }} ${pmEsc(s.name)}"><span class="material-symbols-rounded" aria-hidden="true">arrow_downward</span></button>
                    ${s.is_default ? '' : `<button type="button" class="ula-btn ula-btn--ghost ula-btn--sm" onclick="pmUpdateState(${s.id}, {is_default: true})"><span class="material-symbols-rounded" aria-hidden="true">star</span><span>{{ __('pm.make_default') }}</span></button>`}
                    <button type="button" class="ula-btn ula-btn--ghost ula-btn--sm" onclick="pmRenameState(${s.id})"><span class="material-symbols-rounded" aria-hidden="true">edit</span><span>{{ __('pm.rename') }}</span></button>
                    <button type="button" class="ula-btn ula-btn--ghost ula-btn--sm" onclick="pmDeleteState(${s.id})"><span class="material-symbols-rounded" aria-hidden="true">delete</span><span>{{ __('pm.delete') }}</span></button>` : ''}
            </div>`).join('');
    }

    async function pmReloadStates() {
        const data = await pmApi('GET', `/projects/${PM.projectId}/states`);
        PM.states = data.states.map(s => ({ id: s.id, name: s.name, group: s.group, is_default: s.is_default }));
        PM.dirty = true;
        pmRenderStateList();
    }

    async function pmCreateState(e) {
        e.preventDefault();
        try {
            await pmApi('POST', `/projects/${PM.projectId}/states`, { name: e.target.name.value.trim(), group: e.target.group.value });
            e.target.reset();
            await pmReloadStates();
        } catch (err) { showHubToast(pmEsc(err.message)); }
    }

    async function pmUpdateState(id, body) {
        try {
            await pmApi('PATCH', `/projects/${PM.projectId}/states/${id}`, body);
            await pmReloadStates();
        } catch (err) { showHubToast(pmEsc(err.message)); }
    }

    function pmRenameState(id) {
        const s = PM.states.find(x => x.id === id);
        const name = prompt(@json(__('pm.rename_state')), s.name);
        if (name && name.trim() !== s.name) pmUpdateState(id, { name: name.trim() });
    }

    async function pmDeleteState(id) {
        if (!confirm(@json(__('pm.delete_state_confirm')))) return;
        try {
            await pmApi('DELETE', `/projects/${PM.projectId}/states/${id}`);
            await pmReloadStates();
        } catch (err) { showHubToast(pmEsc(err.message)); }
    }

    document.addEventListener('DOMContentLoaded', pmRenderStateList);

    // ── Templates ────────────────────────────────────────────────────────
    function pmRenderTemplateLists() {
        const tasks = document.getElementById('pm-task-template-list');
        if (tasks) {
            tasks.innerHTML = PM.taskTemplates.length ? PM.taskTemplates.map(t => `
                <div class="pm-list-row">
                    <span class="material-symbols-rounded" aria-hidden="true" style="color: var(--ula-icon-accent);">task</span>
                    <span class="pm-grow" style="font-weight: 700;">${pmEsc(t.name)}</span>
                    <span class="pm-label pm-label--${t.project_id ? 'stone' : 'palm'}">${t.project_id ? @json(__('pm.this_project')) : @json(__('pm.organization_wide'))}</span>
                    <span class="pm-muted"><span class="pm-num">${(t.payload.checklist || []).length}</span> {{ __('pm.checklist_items') }}</span>
                    ${PM.can.editProject ? `<button type="button" class="ula-btn ula-btn--ghost ula-btn--sm" onclick="pmDeleteTemplate('task', ${t.id})"><span class="material-symbols-rounded" aria-hidden="true">delete</span><span>{{ __('pm.delete') }}</span></button>` : ''}
                </div>`).join('') : `<div class="pm-empty">{{ __('pm.no_task_templates') }}</div>`;
        }
        const projects = document.getElementById('pm-project-template-list');
        if (projects) {
            projects.innerHTML = PM.projectTemplates.length ? PM.projectTemplates.map(t => `
                <div class="pm-list-row">
                    <span class="material-symbols-rounded" aria-hidden="true" style="color: var(--ula-icon-accent);">folder_copy</span>
                    <span class="pm-grow"><span style="font-weight: 700;">${pmEsc(t.name)}</span>${t.description ? `<br><span class="pm-muted">${pmEsc(t.description)}</span>` : ''}</span>
                    ${PM.can.createProjects ? `<button type="button" class="ula-btn ula-btn--ghost ula-btn--sm" onclick="pmDeleteTemplate('project', ${t.id})"><span class="material-symbols-rounded" aria-hidden="true">delete</span><span>{{ __('pm.delete') }}</span></button>` : ''}
                </div>`).join('') : `<div class="pm-empty">{{ __('pm.no_project_templates') }}</div>`;
        }
    }

    async function pmSaveProjectTemplate(e) {
        e.preventDefault();
        try {
            const data = await pmApi('POST', `/projects/${PM.projectId}/save-as-template`, { name: e.target.name.value.trim(), description: e.target.description.value.trim() || null });
            PM.projectTemplates.unshift(data.template);
            pmRenderTemplateLists();
            showHubToast(@json(__('pm.template_saved')));
        } catch (err) { showHubToast(pmEsc(err.message)); }
    }

    async function pmDeleteTemplate(kind, id) {
        if (!confirm(@json(__('pm.delete_template_confirm')))) return;
        try {
            await pmApi('DELETE', kind === 'task' ? `/task-templates/${id}` : `/project-templates/${id}`);
            if (kind === 'task') PM.taskTemplates = PM.taskTemplates.filter(t => t.id !== id);
            else PM.projectTemplates = PM.projectTemplates.filter(t => t.id !== id);
            pmRenderTemplateLists();
        } catch (err) { showHubToast(pmEsc(err.message)); }
    }

    document.addEventListener('DOMContentLoaded', pmRenderTemplateLists);

    // ── Archive ──────────────────────────────────────────────────────────
    async function pmLoadArchive() {
        const list = document.getElementById('pm-archive-list');
        if (!list) return;
        try {
            const data = await pmApi('GET', `/archive/tasks?project_id=${PM.projectId}`);
            const rows = data.data || [];
            list.innerHTML = rows.length ? rows.map(t => `
                <div class="pm-list-row">
                    <span class="pm-num pm-muted">#${t.task_number}</span>
                    <span class="pm-grow" style="font-weight: 700;">${pmEsc(t.title)}</span>
                    <span class="pm-muted">{{ __('pm.archived_on') }} <span class="pm-num">${pmEsc((t.archived_at || '').slice(0, 10))}</span></span>
                    ${PM.can.deleteTasks ? `
                        <button type="button" class="ula-btn ula-btn--secondary ula-btn--sm" onclick="pmRestoreTask('${pmEsc(t.id)}')"><span class="material-symbols-rounded" aria-hidden="true">unarchive</span><span>{{ __('pm.restore') }}</span></button>
                        <button type="button" class="ula-btn ula-btn--ghost ula-btn--sm" onclick="pmPurgeTask('${pmEsc(t.id)}')"><span class="material-symbols-rounded" aria-hidden="true">delete_forever</span><span>{{ __('pm.delete_forever') }}</span></button>` : ''}
                </div>`).join('') : `<div class="pm-empty">{{ __('pm.archive_empty') }}</div>`;
        } catch (e) { list.innerHTML = `<div class="pm-empty">${pmEsc(e.message)}</div>`; }
    }

    async function pmRestoreTask(id) {
        try {
            await pmApi('POST', `/archive/tasks/${id}/restore`);
            PM.dirty = true;
            showHubToast(@json(__('pm.task_restored')));
            pmLoadArchive();
        } catch (e) { showHubToast(pmEsc(e.message)); }
    }

    async function pmPurgeTask(id) {
        if (!confirm(@json(__('pm.delete_forever_confirm')))) return;
        try {
            await pmApi('DELETE', `/archive/tasks/${id}`);
            pmLoadArchive();
        } catch (e) { showHubToast(pmEsc(e.message)); }
    }

    async function pmArchiveProject() {
        if (!confirm(@json(__('pm.archive_project_confirm')))) return;
        try {
            await pmApi('DELETE', `/projects/${PM.projectId}`);
            window.location.href = @json(route('dashboard')) + '#projects';
        } catch (e) { showHubToast(pmEsc(e.message)); }
    }

    document.addEventListener('pm:settings-panel', e => { if (e.detail === 'archive') pmLoadArchive(); });

    document.addEventListener('DOMContentLoaded', () => {
        pmPickNewLabelTone(PM.newLabelTone);
        pmRenderLabelList();
    });
</script>
