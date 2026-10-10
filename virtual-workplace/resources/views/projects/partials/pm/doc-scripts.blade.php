<script nonce="{{ $cspNonce ?? '' }}">
    // ── Document editor: rich text (sanitised server-side), version history, conflict-safe saves ──
    PM.doc = null;
    PM.docSaved = '';

    function pmDocEditor() { return document.getElementById('pm-doc-editor'); }
    function pmDocDirty() {
        if (!PM.doc) return false;
        return pmDocEditor().innerHTML !== PM.docSaved
            || document.getElementById('pm-doc-title').value !== PM.doc.title
            || document.getElementById('pm-doc-icon').value !== (PM.doc.icon || '');
    }

    function pmFillDoc(d) {
        PM.doc = d;
        const editable = PM.can.editProject;
        document.getElementById('pm-doc-icon').value = d.icon || '';
        document.getElementById('pm-doc-title').value = d.title;
        pmDocEditor().innerHTML = d.html || '';
        PM.docSaved = pmDocEditor().innerHTML;
        pmDocEditor().contentEditable = editable ? 'true' : 'false';
        document.getElementById('pm-doc-toolbar').hidden = !editable;
        document.getElementById('pm-doc-save-btn').hidden = !editable;
        document.getElementById('pm-doc-title').readOnly = !editable;
        document.getElementById('pm-doc-icon').readOnly = !editable;
        const who = d.last_editor?.name || d.author?.name || '—';
        document.getElementById('pm-doc-meta').innerHTML =
            `<span class="pm-num">v${d.version}</span> · ${pmEsc(who)} · <span class="pm-num">${pmEsc((d.updated_at || '').replace('T', ' ').slice(0, 16))}</span>`;
    }

    async function pmOpenDoc(id) {
        try {
            const { document: d } = await pmApi('GET', `/projects/${PM.projectId}/docs/${id}`);
            pmFillDoc(d);
            document.getElementById('pm-doc-history').hidden = true;
            document.getElementById('pm-doc-modal').style.display = 'flex';
            (PM.can.editProject ? pmDocEditor() : document.getElementById('pm-doc-title')).focus();
        } catch (e) { showHubToast(pmEsc(e.message)); }
    }

    function pmCloseDoc() {
        if (pmDocDirty() && !confirm(@json(__('pm.doc_discard_confirm')))) return;
        document.getElementById('pm-doc-modal').style.display = 'none';
        if (PM.docsDirty) { window.location.hash = 'docs'; window.location.reload(); }
        PM.doc = null;
    }

    async function pmSaveDoc() {
        if (!PM.doc) return;
        const btn = document.getElementById('pm-doc-save-btn');
        btn.disabled = true;
        try {
            const data = await pmApi('PUT', `/projects/${PM.projectId}/docs/${PM.doc.id}`, {
                title: document.getElementById('pm-doc-title').value.trim() || PM.doc.title,
                icon: document.getElementById('pm-doc-icon').value.trim() || null,
                content: pmDocEditor().innerHTML,
                base_version: PM.doc.version,
            });
            pmFillDoc(data.document);
            PM.docsDirty = true;
            showHubToast(@json(__('pm.doc_saved')));
            if (!document.getElementById('pm-doc-history').hidden) pmLoadDocVersions();
        } catch (e) {
            // 409: someone saved in between — reload their version rather than overwrite it.
            showHubToast(pmEsc(e.message));
            if (String(e.message).includes(@json(__('pm.doc_conflict')))) pmOpenDoc(PM.doc.id);
        } finally { btn.disabled = false; }
    }

    // Toolbar: execCommand keeps this dependency-free; the server sanitises whatever it produces.
    document.addEventListener('click', e => {
        const tool = e.target.closest('.pm-doc-tool');
        if (!tool) return;
        e.preventDefault();
        pmDocEditor().focus();
        const [cmd, arg] = tool.dataset.cmd.split(':');
        if (cmd === 'link') {
            const url = prompt(@json(__('pm.link_url_prompt')), 'https://');
            if (url && /^(https?:\/\/|mailto:)/i.test(url)) document.execCommand('createLink', false, url);
            return;
        }
        document.execCommand(cmd, false, arg ? `<${arg}>` : null);
    });

    document.addEventListener('keydown', e => {
        if (!PM.doc || document.getElementById('pm-doc-modal').style.display !== 'flex') return;
        if ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === 's') { e.preventDefault(); pmSaveDoc(); }
    });

    // Paste as plain text so foreign styling never sneaks in.
    document.addEventListener('paste', e => {
        if (!e.target.closest || !e.target.closest('#pm-doc-editor')) return;
        e.preventDefault();
        document.execCommand('insertText', false, (e.clipboardData || window.clipboardData).getData('text/plain'));
    });

    window.addEventListener('beforeunload', e => { if (pmDocDirty()) { e.preventDefault(); e.returnValue = ''; } });

    // ── History ──
    function pmToggleDocHistory() {
        const panel = document.getElementById('pm-doc-history');
        panel.hidden = !panel.hidden;
        document.getElementById('pm-doc-history-btn').setAttribute('aria-expanded', String(!panel.hidden));
        if (!panel.hidden) pmLoadDocVersions();
    }

    async function pmLoadDocVersions() {
        const list = document.getElementById('pm-doc-versions');
        document.getElementById('pm-doc-preview').hidden = true;
        try {
            const { versions } = await pmApi('GET', `/projects/${PM.projectId}/docs/${PM.doc.id}/versions`);
            list.innerHTML = versions.length ? versions.map(v => `
                <button type="button" class="pm-list-row pm-sprint-row" onclick="pmPreviewVersion(${v.id})">
                    <span class="pm-label pm-label--stone pm-num">v${v.version}</span>
                    <span class="pm-grow">${pmEsc(v.user?.name || '—')}</span>
                    <span class="pm-muted pm-num">${pmEsc((v.created_at || '').replace('T', ' ').slice(0, 16))}</span>
                </button>`).join('') : `<div class="pm-empty">{{ __('pm.no_versions') }}</div>`;
        } catch (e) { list.innerHTML = `<div class="pm-empty">${pmEsc(e.message)}</div>`; }
    }

    async function pmPreviewVersion(id) {
        try {
            const { version: v } = await pmApi('GET', `/projects/${PM.projectId}/docs/${PM.doc.id}/versions/${id}`);
            const box = document.getElementById('pm-doc-preview');
            box.hidden = false;
            box.innerHTML = `<div class="pm-row" style="margin-block-end: var(--ula-space-3);">
                    <strong class="pm-grow" dir="auto">${pmEsc(v.title)}</strong><span class="pm-num pm-muted">v${v.version}</span>
                    ${PM.can.editProject ? `<button type="button" class="ula-btn ula-btn--secondary ula-btn--sm" onclick="pmRestoreVersion(${id})"><span class="material-symbols-rounded" aria-hidden="true">restore</span><span>{{ __('pm.restore_version') }}</span></button>` : ''}
                </div><div class="pm-doc-rendered" dir="auto">${v.html}</div>`;
        } catch (e) { showHubToast(pmEsc(e.message)); }
    }

    async function pmRestoreVersion(id) {
        if (!confirm(@json(__('pm.restore_version_confirm')))) return;
        try {
            const data = await pmApi('POST', `/projects/${PM.projectId}/docs/${PM.doc.id}/versions/${id}/restore`);
            pmFillDoc(data.document);
            PM.docsDirty = true;
            pmLoadDocVersions();
            showHubToast(@json(__('pm.version_restored')));
        } catch (e) { showHubToast(pmEsc(e.message)); }
    }
</script>
