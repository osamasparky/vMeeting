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
            showHubToast('{{ __('pm.label_created') }}');
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
        const name = prompt('{{ __('pm.rename_label') }}', label.name);
        if (!name || name.trim() === label.name) return;
        try {
            await pmApi('PATCH', `/projects/${PM.projectId}/labels/${id}`, { name: name.trim() });
            label.name = name.trim();
            pmRenderLabelList();
        } catch (err) { showHubToast(pmEsc(err.message)); }
    }

    async function pmDeleteLabel(id) {
        if (!confirm('{{ __('pm.delete_label_confirm') }}')) return;
        try {
            await pmApi('DELETE', `/projects/${PM.projectId}/labels/${id}`);
            PM.labels = PM.labels.filter(l => l.id !== id);
            pmRenderLabelList();
        } catch (err) { showHubToast(pmEsc(err.message)); }
    }

    document.addEventListener('DOMContentLoaded', () => {
        pmPickNewLabelTone(PM.newLabelTone);
        pmRenderLabelList();
    });
</script>
