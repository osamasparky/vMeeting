{{-- Notification bell: badge, dropdown and polling. Shared by every page that shows the top bar.
     Notification text comes from users (task titles, comments, public intake forms), so every
     string is escaped before it reaches the DOM. --}}
<div class="ula-shell-notif" id="notifWrapper">
    <button type="button" id="notifBellBtn" class="ula-shell-iconbtn" onclick="toggleNotificationDropdown()"
            aria-label="{{ __('nav.notifications') }}" title="{{ __('nav.notifications') }}"
            aria-haspopup="true" aria-expanded="false" aria-controls="notifDropdown">
        <span class="material-symbols-rounded" aria-hidden="true">notifications</span>
    </button>
    <span class="notification-badge-pulse" id="notifBadge" style="display: none;" aria-live="polite">0</span>

    <div class="notification-dropdown-panel" id="notifDropdown" role="region" aria-label="{{ __('Notifications') }}">
        <div class="notif-head">
            <div class="notif-head-title">
                <span class="material-symbols-rounded" aria-hidden="true">notifications</span>
                <strong>{{ __('Notifications') }}</strong>
                <span id="notifHeaderCount" class="badge-status badge-active" style="display: none;"></span>
            </div>
            <div class="notif-head-actions">
                <button type="button" class="notif-head-btn" onclick="markAllNotificationsAsRead()" title="{{ __('Mark all as read') }}">{{ __('Mark read') }}</button>
                <button type="button" class="notif-head-btn" onclick="clearAllNotificationsFromServer()" title="{{ __('Clear all') }}" aria-label="{{ __('Clear all') }}">
                    <span class="material-symbols-rounded" aria-hidden="true">delete_sweep</span>
                </button>
            </div>
        </div>
        <div class="notif-tabs" role="group" aria-label="{{ __('Notifications') }}">
            <button type="button" class="notif-tab-btn active" aria-pressed="true" onclick="filterNotifTab('all', this)">{{ __('All') }}</button>
            <button type="button" class="notif-tab-btn" aria-pressed="false" onclick="filterNotifTab('task', this)">{{ __('Tasks') }}</button>
            <button type="button" class="notif-tab-btn" aria-pressed="false" onclick="filterNotifTab('meeting', this)">{{ __('Meetings') }}</button>
            <button type="button" class="notif-tab-btn" aria-pressed="false" onclick="filterNotifTab('spatial', this)">{{ __('Office') }}</button>
        </div>
        <div id="notifListContainer" class="notif-list"></div>
    </div>
</div>

<script nonce="{{ $cspNonce ?? '' }}">
    (function () {
        const T = {
            newCount: @json(__('new')),
            caughtUp: @json(__('All caught up!')),
            emptyCategory: @json(__('No notifications in this category.')),
            clearConfirm: @json(__('Clear all notifications?')),
            fallbackTitle: @json(__('Notifications')),
        };
        const state = { items: [], filter: 'all', unread: 0, first: true };

        const esc = s => String(s ?? '').replace(/[&<>"']/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
        // Only same-site paths are followed from a notification.
        const safeUrl = u => (typeof u === 'string' && u.startsWith('/') && !u.startsWith('//')) ? u : null;
        const csrf = () => document.querySelector('meta[name="csrf-token"]')?.content || '';

        async function call(url, method = 'GET') {
            const res = await fetch(url, {
                method,
                credentials: 'same-origin',
                headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf() },
            });
            if (!res.ok) throw new Error(res.statusText);
            return res.json();
        }

        function toast(n) {
            const html = `<strong>${esc(n.title)}</strong>${n.body ? `<br><small>${esc(n.body)}</small>` : ''}`;
            if (typeof window.showToastNotification === 'function') window.showToastNotification(html);
            else if (typeof window.showHubToast === 'function') window.showHubToast(html);
        }

        function chime() {
            try {
                const ctx = new (window.AudioContext || window.webkitAudioContext)();
                const now = ctx.currentTime;
                const osc = ctx.createOscillator();
                const gain = ctx.createGain();
                osc.type = 'sine';
                osc.frequency.setValueAtTime(587.33, now);
                osc.frequency.exponentialRampToValueAtTime(880, now + 0.12);
                gain.gain.setValueAtTime(0.08, now);
                gain.gain.exponentialRampToValueAtTime(0.001, now + 0.35);
                osc.connect(gain);
                gain.connect(ctx.destination);
                osc.start(now);
                osc.stop(now + 0.35);
            } catch (e) { /* audio needs a user gesture first */ }
        }

        function desktop(n) {
            if (!('Notification' in window) || Notification.permission !== 'granted') return;
            try {
                // The Notification API renders plain text, so no escaping is needed here.
                const d = new Notification(n.title || T.fallbackTitle, { body: n.body || '', icon: '/favicon.ico' });
                d.onclick = () => { window.focus(); const u = safeUrl(n.action_url); if (u) window.location.href = u; };
            } catch (e) {}
        }

        function renderBadge() {
            const badge = document.getElementById('notifBadge');
            const head = document.getElementById('notifHeaderCount');
            if (badge) {
                badge.textContent = state.unread > 99 ? '99+' : String(state.unread);
                badge.style.display = state.unread > 0 ? 'flex' : 'none';
            }
            if (head) {
                head.innerHTML = `<span class="pm-num">${state.unread}</span> ${esc(T.newCount)}`;
                head.style.display = state.unread > 0 ? 'inline-flex' : 'none';
            }
        }

        function matchesFilter(n) {
            const type = String(n.type || '');
            if (state.filter === 'task') return type.startsWith('task');
            if (state.filter === 'meeting') return type.startsWith('meeting');
            if (state.filter === 'spatial') return ['door_knock', 'wave', 'user_wave', 'user_ring'].includes(type) || type.startsWith('room');
            return true;
        }

        function renderList() {
            const box = document.getElementById('notifListContainer');
            if (!box) return;
            const list = state.items.filter(matchesFilter);
            if (!list.length) {
                box.innerHTML = `<div class="notif-empty">
                    <span class="material-symbols-rounded" aria-hidden="true">celebration</span>
                    <strong>${esc(T.caughtUp)}</strong><span>${esc(T.emptyCategory)}</span></div>`;
                return;
            }
            box.innerHTML = list.map(n => `
                <button type="button" class="notif-item ${n.is_read ? '' : 'unread'}" data-id="${esc(n.id)}">
                    <span class="notif-icon-box" aria-hidden="true">${esc(n.icon || '🔔')}</span>
                    <span class="notif-text">
                        <span class="notif-row"><strong dir="auto">${esc(n.title)}</strong><span class="notif-time">${esc(n.created_at_human)}</span></span>
                        ${n.body ? `<span class="notif-body" dir="auto">${esc(n.body)}</span>` : ''}
                    </span>
                    ${n.is_read ? '' : '<span class="notif-unread-dot" aria-hidden="true"></span>'}
                </button>`).join('');
        }

        async function refresh() {
            try {
                const data = await call('/api/notifications');
                const unread = data.unread_count || 0;
                state.items = data.notifications || [];
                if (!state.first && unread > state.unread) {
                    const newest = state.items.find(n => !n.is_read) || state.items[0];
                    if (newest) { chime(); toast(newest); desktop(newest); }
                }
                state.unread = unread;
                state.first = false;
                renderBadge();
                renderList();
            } catch (e) { /* polling failures are retried on the next tick */ }
        }

        async function open(id) {
            const n = state.items.find(x => String(x.id) === String(id));
            if (!n) return;
            if (!n.is_read) {
                try { await call(`/api/notifications/${encodeURIComponent(n.id)}/read`, 'POST'); } catch (e) {}
                n.is_read = true;
                state.unread = Math.max(0, state.unread - 1);
                renderBadge();
                renderList();
            }
            const url = safeUrl(n.action_url);
            if (url) window.location.href = url;
        }

        window.toggleNotificationDropdown = function () {
            const panel = document.getElementById('notifDropdown');
            if (!panel) return;
            const opening = panel.style.display !== 'flex';
            panel.style.display = opening ? 'flex' : 'none';
            document.getElementById('notifBellBtn')?.setAttribute('aria-expanded', String(opening));
            if (opening) {
                if ('Notification' in window && Notification.permission === 'default') Notification.requestPermission();
                renderList();
            }
        };

        window.filterNotifTab = function (tab, btn) {
            state.filter = tab;
            document.querySelectorAll('.notif-tab-btn').forEach(b => {
                const on = b === btn;
                b.classList.toggle('active', on);
                b.setAttribute('aria-pressed', String(on));
            });
            renderList();
        };

        window.markAllNotificationsAsRead = async function () {
            try { await call('/api/notifications/read-all', 'POST'); } catch (e) { return; }
            state.items.forEach(n => n.is_read = true);
            state.unread = 0;
            renderBadge();
            renderList();
        };

        window.clearAllNotificationsFromServer = async function () {
            if (!confirm(T.clearConfirm)) return;
            try { await call('/api/notifications/clear', 'DELETE'); } catch (e) { return; }
            state.items = [];
            state.unread = 0;
            renderBadge();
            renderList();
        };

        document.addEventListener('click', e => {
            const item = e.target.closest('#notifListContainer .notif-item');
            if (item) { open(item.dataset.id); return; }
            const wrapper = document.getElementById('notifWrapper');
            const panel = document.getElementById('notifDropdown');
            if (wrapper && panel && !wrapper.contains(e.target) && panel.style.display === 'flex') {
                panel.style.display = 'none';
                document.getElementById('notifBellBtn')?.setAttribute('aria-expanded', 'false');
            }
        });

        document.addEventListener('keydown', e => {
            const panel = document.getElementById('notifDropdown');
            if (e.key === 'Escape' && panel?.style.display === 'flex') {
                panel.style.display = 'none';
                const bell = document.getElementById('notifBellBtn');
                bell?.setAttribute('aria-expanded', 'false');
                bell?.focus();
            }
        });

        const start = () => { refresh(); setInterval(refresh, 15000); };
        if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', start);
        else start();
    })();
</script>
