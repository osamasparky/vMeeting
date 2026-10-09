<script nonce="{{ $cspNonce ?? '' }}">
    // ── Saved views: named filters + layout, private or shared with the project ──
    PM.views = [];
    PM.activeViewId = null;

    async function pmLoadViews() {
        try {
            PM.views = (await pmApi('GET', `/projects/${PM.projectId}/views`)).views;
        } catch (e) { PM.views = []; }
    }

    function pmCurrentLayout() {
        const active = document.querySelector('.hub-tab-btn.active');
        const tab = active ? active.id.replace('hub-nav-btn-', '') : 'kanban';
        return ['kanban', 'tasks', 'calendar'].includes(tab) ? tab : 'kanban';
    }

    function pmOpenViewsMenu(trigger) {
        document.querySelectorAll('.pm-picker-menu').forEach(m => m.remove());
        const menu = document.createElement('div');
        menu.className = 'pm-picker-menu';
        menu.setAttribute('role', 'menu');
        const layoutIcon = { kanban: 'view_kanban', tasks: 'table_rows', calendar: 'calendar_month' };

        const items = PM.views.map(v => `
            <div class="pm-view-item">
                <button type="button" class="pm-picker-option" role="menuitem" onclick="pmApplyView(${v.id})">
                    <span class="material-symbols-rounded" aria-hidden="true">${layoutIcon[v.layout] || 'bookmark'}</span>
                    <span class="pm-grow">${pmEsc(v.name)}</span>
                    ${v.is_shared ? `<span class="pm-label pm-label--palm">{{ __('pm.shared') }}</span>` : ''}
                </button>
                ${v.user_id === PM.userId ? `<button type="button" class="ula-btn ula-btn--ghost ula-btn--sm" aria-label="{{ __('pm.delete') }} ${pmEsc(v.name)}" onclick="pmDeleteView(${v.id})"><span class="material-symbols-rounded" aria-hidden="true">delete</span></button>` : ''}
            </div>`).join('');

        menu.innerHTML = `
            ${items || `<div class="pm-muted" style="padding: var(--ula-space-4);">{{ __('pm.no_views') }}</div>`}
            ${PM.activeViewId ? `<button type="button" class="pm-picker-option" role="menuitem" onclick="pmClearView()"><span class="material-symbols-rounded" aria-hidden="true">filter_alt_off</span>{{ __('pm.clear_view') }}</button>` : ''}
            <form style="padding: var(--ula-space-3); border-top: 1px solid var(--ula-border-subtle); margin-top: var(--ula-space-2); display: grid; gap: var(--ula-space-3);" onsubmit="pmSaveView(event)">
                <span class="pm-extras-label">{{ __('pm.save_current_view') }}</span>
                <input class="pm-field" name="name" maxlength="80" required placeholder="{{ __('pm.view_name') }}" aria-label="{{ __('pm.view_name') }}">
                <label class="pm-row" style="gap: var(--ula-space-2); font-size: var(--ula-size-sm);"><input type="checkbox" name="is_shared"> {{ __('pm.share_with_team') }}</label>
                <button type="submit" class="ula-btn ula-btn--primary ula-btn--sm">{{ __('pm.save_view') }}</button>
            </form>`;

        trigger.insertAdjacentElement('afterend', menu);
        trigger.setAttribute('aria-expanded', 'true');
        const close = (e) => {
            if (e.type === 'keydown' && e.key !== 'Escape') return;
            if (e.type === 'click' && (menu.contains(e.target) || trigger.contains(e.target))) return;
            menu.remove();
            trigger.setAttribute('aria-expanded', 'false');
            document.removeEventListener('click', close, true);
            document.removeEventListener('keydown', close, true);
        };
        setTimeout(() => { document.addEventListener('click', close, true); document.addEventListener('keydown', close, true); });
    }

    function pmApplyView(id) {
        const view = PM.views.find(v => v.id === id);
        if (!view) return;
        PM.activeViewId = id;
        document.getElementById('pm-views-current').textContent = view.name;
        switchHubSection(view.layout || 'kanban');
        setHubFilterState(view.filters || {});
        document.querySelectorAll('.pm-picker-menu').forEach(m => m.remove());
    }

    function pmClearView() {
        PM.activeViewId = null;
        document.getElementById('pm-views-current').textContent = @json(__('pm.views'));
        clearHubTaskFilters();
        document.querySelectorAll('.pm-picker-menu').forEach(m => m.remove());
    }

    async function pmSaveView(e) {
        e.preventDefault();
        const form = e.target;
        try {
            const data = await pmApi('POST', `/projects/${PM.projectId}/views`, {
                name: form.name.value.trim(),
                layout: pmCurrentLayout(),
                filters: getHubFilterState(),
                is_shared: form.is_shared.checked,
            });
            PM.views.push(data.view);
            PM.views.sort((a, b) => a.name.localeCompare(b.name));
            PM.activeViewId = data.view.id;
            document.getElementById('pm-views-current').textContent = data.view.name;
            document.querySelectorAll('.pm-picker-menu').forEach(m => m.remove());
            showHubToast(@json(__('pm.view_saved')));
        } catch (err) { showHubToast(pmEsc(err.message)); }
    }

    async function pmDeleteView(id) {
        if (!confirm(@json(__('pm.delete_view_confirm')))) return;
        try {
            await pmApi('DELETE', `/projects/${PM.projectId}/views/${id}`);
            PM.views = PM.views.filter(v => v.id !== id);
            if (PM.activeViewId === id) pmClearView();
            document.querySelectorAll('.pm-picker-menu').forEach(m => m.remove());
        } catch (err) { showHubToast(pmEsc(err.message)); }
    }

    document.addEventListener('DOMContentLoaded', pmLoadViews);
</script>
