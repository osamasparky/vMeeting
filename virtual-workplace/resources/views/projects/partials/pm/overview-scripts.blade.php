<script nonce="{{ $cspNonce ?? '' }}">
    // ── Overview: status updates feed ──
    const PM_HEALTH = {
        on_track: { label: @json(__('pm.health_on_track')), tone: 'palm', icon: 'rocket_launch' },
        at_risk: { label: @json(__('pm.health_at_risk')), tone: 'gold', icon: 'warning' },
        off_track: { label: @json(__('pm.health_off_track')), tone: 'terracotta', icon: 'error' },
    };

    async function pmLoadUpdates() {
        const feed = document.getElementById('pm-update-feed');
        if (!feed) return;
        try {
            const { updates } = await pmApi('GET', `/projects/${PM.projectId}/updates`);
            feed.innerHTML = updates.length ? updates.map(u => {
                const h = PM_HEALTH[u.health] || PM_HEALTH.on_track;
                const mine = u.user_id === PM.userId || PM.can.editProject;
                return `<article class="pm-update">
                    <div class="pm-row">
                        <span class="pm-label pm-label--${h.tone}" style="gap: var(--ula-space-2);"><span class="material-symbols-rounded" aria-hidden="true" style="font-size: 16px;">${h.icon}</span>${h.label}</span>
                        <span style="font-weight: 700;">${pmEsc(u.user?.name || '—')}</span>
                        <span class="pm-muted pm-num pm-grow">${pmEsc((u.created_at || '').replace('T', ' ').slice(0, 16))}</span>
                        ${mine ? `<button type="button" class="ula-btn ula-btn--ghost ula-btn--sm" aria-label="{{ __('pm.delete') }}" onclick="pmDeleteUpdate(${u.id})"><span class="material-symbols-rounded" aria-hidden="true">delete</span></button>` : ''}
                    </div>
                    <p class="pm-update-body" dir="auto">${pmEsc(u.body)}</p>
                </article>`;
            }).join('') : `<div class="pm-empty">{{ __('pm.no_updates') }}</div>`;
        } catch (e) { feed.innerHTML = `<div class="pm-empty">${pmEsc(e.message)}</div>`; }
    }

    async function pmPostUpdate(e) {
        e.preventDefault();
        const form = e.target;
        try {
            await pmApi('POST', `/projects/${PM.projectId}/updates`, { health: form.health.value, body: form.body.value.trim() });
            form.body.value = '';
            showHubToast(@json(__('pm.update_posted')));
            // The health badge is server-rendered; refresh to show the new one.
            window.location.hash = 'overview';
            window.location.reload();
        } catch (err) { showHubToast(pmEsc(err.message)); }
    }

    async function pmDeleteUpdate(id) {
        if (!confirm(@json(__('pm.delete_update_confirm')))) return;
        try {
            await pmApi('DELETE', `/projects/${PM.projectId}/updates/${id}`);
            pmLoadUpdates();
        } catch (err) { showHubToast(pmEsc(err.message)); }
    }

    document.addEventListener('pm:tab-shown', e => { if (e.detail === 'overview') pmLoadUpdates(); });
    // The overview is the landing tab when the URL names no other.
    document.addEventListener('DOMContentLoaded', () => {
        if (!window.location.hash) switchHubSection('overview');
    });
</script>
