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
