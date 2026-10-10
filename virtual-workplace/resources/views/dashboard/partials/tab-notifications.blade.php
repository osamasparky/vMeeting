{{-- All notifications: the full, paged list behind the bell (dashboard#notifications). --}}
<div id="tab-notifications" class="tab-view">
    <div class="pm-panel-head">
        <div class="ula-headline-group">
            <h2 class="ula-headline-ar" style="font-size: var(--ula-size-h1); margin: 0;">{{ __('pm.notif_all_title') }}</h2>
            @if(app()->getLocale() === 'ar')<span class="ula-headline-en" style="font-size: var(--ula-size-h4);">All notifications</span>@endif
        </div>
        <div class="pm-row">
            <x-btn variant="secondary" size="md" icon="done_all" onclick="nfMarkAll()">{{ __('Mark all as read') }}</x-btn>
            <x-btn variant="ghost" size="md" icon="tune" onclick="switchAdminTab('profile'); setTimeout(() => document.getElementById('notification-preferences')?.scrollIntoView({ block: 'start' }), 150)">{{ __('pm.notif_settings') }}</x-btn>
        </div>
    </div>

    <div class="pm-row" role="group" aria-label="{{ __('pm.notif_filter') }}" style="margin-block-end: var(--ula-space-4);">
        @foreach(['all' => __('All'), 'unread' => __('pm.notif_unread'), 'task' => __('Tasks'), 'meeting' => __('Meetings'), 'office' => __('Office')] as $nfKey => $nfLabel)
            <button type="button" class="mw-chip nf-filter" data-filter="{{ $nfKey }}" aria-pressed="{{ $nfKey === 'all' ? 'true' : 'false' }}" onclick="nfSetFilter('{{ $nfKey }}')">{{ $nfLabel }}</button>
        @endforeach
        <span class="pm-muted pm-grow" id="nf-summary" aria-live="polite" style="text-align: end;"></span>
    </div>

    <div id="nf-list" class="nf-list"></div>

    <div class="pm-row" style="justify-content: center; margin-block-start: var(--ula-space-4);">
        <x-btn variant="secondary" size="md" id="nf-more" icon="expand_more" onclick="nfLoad(false)" style="display: none;">{{ __('pm.notif_load_more') }}</x-btn>
    </div>
</div>

<script nonce="{{ $cspNonce ?? '' }}">
    (function () {
        const T = {
            empty: @json(__('pm.notif_empty')),
            total: @json(__('pm.notif_total')),
            unread: @json(__('pm.notif_unread')),
        };
        const st = { filter: 'all', page: 0, last: 1, items: [], loaded: false };
        const esc = s => String(s ?? '').replace(/[&<>"']/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
        const safeUrl = u => (typeof u === 'string' && u.startsWith('/') && !u.startsWith('//')) ? u : null;
        const csrf = () => document.querySelector('meta[name="csrf-token"]')?.content || '';
        const call = async (url, method = 'GET') => {
            const res = await fetch(url, { method, credentials: 'same-origin', headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf() } });
            if (!res.ok) throw new Error(res.statusText);
            return res.json();
        };

        function render(meta) {
            const list = document.getElementById('nf-list');
            if (!st.items.length) {
                list.innerHTML = `<div class="pm-empty"><span class="material-symbols-rounded" aria-hidden="true">notifications_off</span><br>${esc(T.empty)}</div>`;
            } else {
                list.innerHTML = st.items.map(n => `
                    <button type="button" class="notif-item nf-item ${n.is_read ? '' : 'unread'}" data-id="${esc(n.id)}">
                        <span class="notif-icon-box" aria-hidden="true">${esc(n.icon || '🔔')}</span>
                        <span class="notif-text">
                            <span class="notif-row"><strong dir="auto">${esc(n.title)}</strong><span class="notif-time">${esc(n.created_at_human)}</span></span>
                            ${n.body ? `<span class="notif-body" dir="auto">${esc(n.body)}</span>` : ''}
                        </span>
                        ${n.is_read ? '' : '<span class="notif-unread-dot" aria-hidden="true"></span>'}
                    </button>`).join('');
            }
            if (meta) {
                document.getElementById('nf-summary').innerHTML =
                    `<span class="pm-num">${meta.total}</span> ${esc(T.total)} · <span class="pm-num">${meta.unread_count}</span> ${esc(T.unread)}`;
            }
            document.getElementById('nf-more').style.display = st.page < st.last ? '' : 'none';
        }

        window.nfLoad = async function (reset) {
            if (reset) { st.page = 0; st.items = []; }
            try {
                const data = await call(`/api/notifications?page=${st.page + 1}&filter=${encodeURIComponent(st.filter)}`);
                st.page = data.current_page;
                st.last = data.last_page;
                st.items = st.items.concat(data.notifications);
                render(data);
            } catch (e) {
                document.getElementById('nf-list').innerHTML = `<div class="pm-empty">${esc(e.message)}</div>`;
            }
        };

        window.nfSetFilter = function (filter) {
            st.filter = filter;
            document.querySelectorAll('.nf-filter').forEach(b => b.setAttribute('aria-pressed', String(b.dataset.filter === filter)));
            nfLoad(true);
        };

        window.nfMarkAll = async function () {
            try { await call('/api/notifications/read-all', 'POST'); } catch (e) { return; }
            nfLoad(true);
            document.dispatchEvent(new CustomEvent('notifications:changed'));
        };

        document.addEventListener('click', async e => {
            const item = e.target.closest('#nf-list .nf-item');
            if (!item) return;
            const n = st.items.find(x => String(x.id) === item.dataset.id);
            if (!n) return;
            if (!n.is_read) {
                try { await call(`/api/notifications/${encodeURIComponent(n.id)}/read`, 'POST'); } catch (e) {}
                document.dispatchEvent(new CustomEvent('notifications:changed'));
            }
            const url = safeUrl(n.action_url);
            if (url) window.location.href = url;
            else { n.is_read = true; render(); }
        });

        // Load when the tab is first shown (directly via #notifications or from the bell link).
        const maybeLoad = () => {
            if (!st.loaded && document.getElementById('tab-notifications')?.classList.contains('active')) {
                st.loaded = true;
                nfLoad(true);
            }
        };
        new MutationObserver(maybeLoad).observe(document.getElementById('tab-notifications'), { attributes: true, attributeFilter: ['class'] });
        document.addEventListener('DOMContentLoaded', maybeLoad);
        document.addEventListener('notifications:arrived', () => { if (st.loaded) nfLoad(true); });
    })();
</script>
