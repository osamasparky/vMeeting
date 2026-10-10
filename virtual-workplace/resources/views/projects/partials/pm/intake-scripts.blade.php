<script nonce="{{ $cspNonce ?? '' }}">
    // ── Intake: triage outside requests ──
    PM.intakeView = 'open';

    async function pmLoadIntake(view) {
        PM.intakeView = view || PM.intakeView;
        document.querySelectorAll('[data-intake-view]').forEach(b => b.setAttribute('aria-selected', String(b.dataset.intakeView === PM.intakeView)));
        const list = document.getElementById('pm-intake-list');
        if (!list) return;
        try {
            const data = await pmApi('GET', `/projects/${PM.projectId}/intake?view=${PM.intakeView}`);
            pmIntakeBadge(data.open_count);
            pmShowIntakeSettings(data.settings);
            const open = PM.intakeView === 'open';
            list.innerHTML = data.requests.length ? data.requests.map(r => `
                <article class="pm-update" id="pm-intake-${r.id}">
                    <div class="pm-row">
                        <strong class="pm-grow" dir="auto">${pmEsc(r.title)}</strong>
                        <span class="pm-muted pm-num">${pmEsc((r.created_at || '').slice(0, 10))}</span>
                    </div>
                    <span class="pm-muted">${pmEsc(r.requester_name)}${r.requester_email ? ` · <span class="pm-num">${pmEsc(r.requester_email)}</span>` : ''}</span>
                    ${r.description ? `<p class="pm-update-body" dir="auto">${pmEsc(r.description)}</p>` : ''}
                    ${r.task ? `<button type="button" class="pm-label pm-label--palm" style="border: 0; cursor: pointer;" onclick="openTaskInspector('${pmEsc(r.task.id)}')">{{ __('pm.became_task') }} <span class="pm-num">#${r.task.task_number}</span></button>` : ''}
                    ${r.duplicate_of ? `<span class="pm-label pm-label--gold">{{ __('pm.rel_duplicate_of') }} <span class="pm-num">#${r.duplicate_of.task_number}</span></span>` : ''}
                    ${r.decline_reason ? `<span class="pm-muted">{{ __('pm.decline_reason') }}: ${pmEsc(r.decline_reason)}</span>` : ''}
                    ${open && PM.can.createTasks ? `
                        <div class="pm-row">
                            <button type="button" class="ula-btn ula-btn--primary ula-btn--sm" onclick="pmIntakeAccept(${r.id})"><span class="material-symbols-rounded" aria-hidden="true">add_task</span><span>{{ __('pm.accept') }}</span></button>
                            <button type="button" class="ula-btn ula-btn--secondary ula-btn--sm" onclick="pmIntakeAction(${r.id}, 'decline')"><span class="material-symbols-rounded" aria-hidden="true">block</span><span>{{ __('pm.decline') }}</span></button>
                            <button type="button" class="ula-btn ula-btn--ghost ula-btn--sm" onclick="pmIntakeAction(${r.id}, 'snooze')"><span class="material-symbols-rounded" aria-hidden="true">snooze</span><span>{{ __('pm.snooze') }}</span></button>
                            <button type="button" class="ula-btn ula-btn--ghost ula-btn--sm" onclick="pmIntakeAction(${r.id}, 'duplicate')"><span class="material-symbols-rounded" aria-hidden="true">content_copy</span><span>{{ __('pm.mark_duplicate') }}</span></button>
                        </div>` : ''}
                </article>`).join('') : `<div class="pm-empty">{{ __('pm.intake_empty') }}</div>`;
        } catch (e) { list.innerHTML = `<div class="pm-empty">${pmEsc(e.message)}</div>`; }
    }

    function pmIntakeBadge(count) {
        const btn = document.getElementById('hub-nav-btn-intake');
        if (!btn) return;
        btn.querySelector('.pm-intake-count')?.remove();
        if (count > 0) btn.insertAdjacentHTML('beforeend', ` <span class="pm-label pm-label--gold pm-num pm-intake-count">${count}</span>`);
    }

    function pmShowIntakeSettings(s) {
        const box = document.getElementById('pm-intake-enabled');
        if (!box || !s) return;
        box.checked = !!s.enabled;
        document.getElementById('pm-intake-url').value = s.url || '';
    }

    async function pmIntakeSettings(body) {
        try {
            const data = await pmApi('PATCH', `/projects/${PM.projectId}/intake/settings`, body);
            pmShowIntakeSettings(data.settings);
            showHubToast(@json(__('pm.saved')));
        } catch (e) { showHubToast(pmEsc(e.message)); }
    }

    async function pmCopyIntakeLink() {
        const input = document.getElementById('pm-intake-url');
        if (!input.value) await pmIntakeSettings({});
        try { await navigator.clipboard.writeText(input.value); } catch (e) { input.select(); document.execCommand('copy'); }
        showHubToast(@json(__('pm.link_copied')));
    }

    async function pmIntakeAccept(id) {
        try {
            const data = await pmApi('POST', `/projects/${PM.projectId}/intake/${id}/accept`, {});
            PM.dirty = true;
            showHubToast(@json(__('pm.intake_accepted')).replace(':n', data.task.task_number));
            pmLoadIntake();
        } catch (e) { showHubToast(pmEsc(e.message)); }
    }

    async function pmIntakeAction(id, action) {
        let body = {};
        if (action === 'decline') {
            const reason = prompt(@json(__('pm.decline_reason_prompt')), '');
            if (reason === null) return;
            body = { reason };
        } else if (action === 'snooze') {
            const d = new Date(); d.setDate(d.getDate() + 7);
            const until = prompt(@json(__('pm.snooze_until_prompt')), d.toISOString().slice(0, 10));
            if (!until) return;
            body = { until };
        } else if (action === 'duplicate') {
            const ref = prompt(@json(__('pm.duplicate_prompt')), '');
            const task = ref && PM.tasks.find(t => String(t.number) === ref.replace('#', '').trim());
            if (!task) { if (ref) showHubToast(@json(__('pm.task_not_found'))); return; }
            body = { task_id: task.id };
        }
        try {
            await pmApi('POST', `/projects/${PM.projectId}/intake/${id}/${action}`, body);
            pmLoadIntake();
        } catch (e) { showHubToast(pmEsc(e.message)); }
    }

    document.addEventListener('pm:tab-shown', e => { if (e.detail === 'intake') pmLoadIntake('open'); });
    // Show the waiting-requests badge on load.
    document.addEventListener('DOMContentLoaded', async () => {
        try { pmIntakeBadge((await pmApi('GET', `/projects/${PM.projectId}/intake?view=open`)).open_count); } catch (e) {}
    });
</script>
