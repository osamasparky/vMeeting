/**
 * Office assistant — the floating avatar, its speech bubble and its panel.
 *
 * Data comes only from the server, for the signed-in person:
 *  - GET  /assistant/briefing        summary (meetings, invitations, tasks, messages, alerts)
 *  - POST /assistant/ask             answers to typed requests (rule-based, read-only)
 *  - GET  /api/notifications?page=1  the existing notifications, with the bell's filters
 *  - POST /api/notifications/{id}/read and /read-all — the existing "mark read"
 * No polling of its own for alerts: it follows the bell's "notifications:updated"
 * events, and tells the bell "notifications:changed" after marking something read.
 *
 * Actions only ever navigate: walk to a room (window.ulaGoToRoom), open a direct
 * message (window.ulaOpenDm), open a same-site link, or switch the panel's tab.
 */
(function () {
    'use strict';

    const root = document.getElementById('ula-assistant');
    if (!root) return;

    const T = window.UlaAssistantText || {};
    const t = (k, rep) => {
        let s = T[k] || k;
        if (rep) for (const r in rep) s = s.replace(':' + r, rep[r]);
        return s;
    };
    const $ = (id) => document.getElementById(id);
    const csrf = document.querySelector('meta[name="csrf-token"]')?.content || '';
    const store = (() => { try { return window.sessionStorage; } catch (e) { return null; } })();

    const ui = {
        toggle: $('ula-assistant-toggle'),
        badge: $('ula-assistant-badge'),
        bubble: $('ula-assistant-bubble'),
        bubbleText: $('ula-assistant-bubble-text'),
        bubbleAction: $('ula-assistant-bubble-action'),
        bubbleClose: $('ula-assistant-bubble-close'),
        panel: $('ula-assistant-panel'),
        close: $('ula-assistant-close'),
        body: $('ula-assistant-body'),
        tabCount: $('ula-assistant-tab-count'),
        offline: $('ula-assistant-offline'),
        askForm: $('ula-assistant-ask'),
        input: $('ula-assistant-input'),
    };

    const state = {
        tab: 'summary',
        filter: 'all',
        briefing: null,
        briefingAt: 0,
        briefingError: false,
        alerts: null,
        alertsError: false,
        unread: 0,
        log: [],
        suggestions: [],
        bubbleTimer: 0,
    };

    // ── data ──

    async function call(url, method = 'GET', body = null) {
        const res = await fetch(url, {
            method,
            credentials: 'same-origin',
            headers: { Accept: 'application/json', 'X-CSRF-TOKEN': csrf, ...(body ? { 'Content-Type': 'application/json' } : {}) },
            body: body ? JSON.stringify(body) : null,
        });
        if (!res.ok) throw new Error(String(res.status));
        return res.json();
    }

    async function loadBriefing() {
        try {
            state.briefing = await call('/assistant/briefing');
            state.briefingAt = Date.now();
            state.briefingError = false;
            state.suggestions = state.briefing.suggestions || [];
            if (!state.unread) setUnread(state.briefing.notifications?.unread || 0);
            maybeSpeak();
        } catch (e) {
            state.briefingError = true;
        }
        if (isOpen() && state.tab === 'summary') render();
    }

    async function loadAlerts() {
        try {
            const data = await call(`/api/notifications?page=1&filter=${encodeURIComponent(state.filter)}`);
            state.alerts = data.notifications || [];
            state.alertsError = false;
            setUnread(data.unread_count || 0);
        } catch (e) {
            state.alertsError = true;
        }
        if (isOpen() && state.tab === 'alerts') render();
    }

    function setUnread(n) {
        state.unread = n;
        ui.badge.hidden = n <= 0;
        ui.badge.textContent = n > 99 ? '99+' : String(n);
        ui.tabCount.textContent = n > 0 ? String(n) : '';
        ui.toggle.setAttribute('aria-label', n > 0 ? `${T.open || ''} — ${t('unread_badge', { count: n })}` : (T.open || ui.toggle.getAttribute('aria-label')));
    }

    // ── actions (navigation only) ──

    function safeUrl(url) {
        // Same-site paths only: never javascript:, data: or another site.
        return typeof url === 'string' && url.startsWith('/') && !url.startsWith('//') ? url : null;
    }

    function perform(action) {
        if (!action) return;
        if (action.type === 'panel') {
            openPanel(action.tab || 'summary', action.filter);
            return;
        }
        if (action.type === 'room') {
            if (typeof window.ulaGoToRoom === 'function' && window.ulaGoToRoom(action.room_id)) {
                closePanel(false);
                hideBubble();
                return;
            }
            say(t('room_elsewhere'));
            return;
        }
        if (action.type === 'dm') {
            if (typeof window.ulaOpenDm === 'function' && window.ulaOpenDm(action.user_id)) {
                closePanel(false);
                hideBubble();
                return;
            }
        }
        const url = safeUrl(action.url);
        if (url) window.location.href = url;
    }

    async function markRead(id) {
        try {
            const data = await call(`/api/notifications/${encodeURIComponent(id)}/read`, 'POST');
            setUnread(data.unread_count || 0);
            document.dispatchEvent(new CustomEvent('notifications:changed'));
        } catch (e) { /* the list reloads below either way */ }
        loadAlerts();
    }

    async function markAll() {
        try {
            await call('/api/notifications/read-all', 'POST');
            setUnread(0);
            document.dispatchEvent(new CustomEvent('notifications:changed'));
        } catch (e) { /* shown by the reload */ }
        loadAlerts();
    }

    async function ask(text) {
        const message = String(text || '').trim().slice(0, 300);
        if (!message) return;
        state.log.push({ me: true, text: message });
        state.log.push({ pending: true });
        render();
        try {
            const answer = await call('/assistant/ask', 'POST', { message });
            state.log.pop();
            state.log.push({ me: false, ...answer });
            if (answer.suggestions) state.suggestions = answer.suggestions;
        } catch (e) {
            state.log.pop();
            state.log.push({ me: false, text: t('error'), error: true });
        }
        render();
    }

    // ── speech bubble ──

    function say(text, action) {
        ui.bubbleText.textContent = text;
        ui.bubbleAction.hidden = !action;
        ui.bubbleAction.onclick = action ? () => { hideBubble(); perform(action); } : null;
        if (action) ui.bubbleAction.textContent = action.label || T['action.open'] || '';
        ui.bubble.hidden = false;
        clearTimeout(state.bubbleTimer);
        state.bubbleTimer = setTimeout(hideBubble, 14000);
    }

    function hideBubble() {
        clearTimeout(state.bubbleTimer);
        ui.bubble.hidden = true;
    }

    /** Say the headline once per thing per session, never while the panel is open. */
    function maybeSpeak() {
        const h = state.briefing?.headline;
        if (!h || isOpen()) return;
        const key = 'ula-assistant-said:' + h.key;
        if (store?.getItem(key)) return;
        store?.setItem(key, '1');
        say(`${state.briefing.greeting} — ${h.text}`, h.action ? { ...h.action, label: h.action.label || T['action.open'] } : null);
    }

    // ── panel ──

    const isOpen = () => !ui.panel.hidden;

    function openPanel(tab, filter) {
        hideBubble();
        if (tab) state.tab = tab;
        if (filter) state.filter = filter;
        ui.panel.hidden = false;
        root.classList.add('is-open');
        ui.toggle.setAttribute('aria-expanded', 'true');
        selectTab(state.tab);
        if (!state.briefing || Date.now() - state.briefingAt > 60000) loadBriefing();
        if (state.tab === 'alerts') loadAlerts();
        ui.panel.querySelector(`[data-tab="${state.tab}"]`)?.focus();
    }

    function closePanel(returnFocus = true) {
        ui.panel.hidden = true;
        root.classList.remove('is-open');
        ui.toggle.setAttribute('aria-expanded', 'false');
        if (returnFocus) ui.toggle.focus();
    }

    function selectTab(tab) {
        state.tab = tab;
        ui.panel.querySelectorAll('[role="tab"]').forEach((b) => {
            const on = b.dataset.tab === tab;
            b.setAttribute('aria-selected', on ? 'true' : 'false');
            b.tabIndex = on ? 0 : -1;
        });
        ui.body.setAttribute('aria-labelledby', `ula-assistant-tab-${tab}`);
        ui.askForm.hidden = tab !== 'ask';
        render();
        if (tab === 'alerts') loadAlerts();
        if (tab === 'ask') setTimeout(() => ui.input.focus(), 0);
    }

    // ── rendering (text only: never HTML from data) ──

    function el(tag, cls, text) {
        const n = document.createElement(tag);
        if (cls) n.className = cls;
        if (text !== undefined && text !== null) n.textContent = text;
        return n;
    }

    function icon(name) {
        const i = el('span', 'material-symbols-rounded', name);
        i.setAttribute('aria-hidden', 'true');
        return i;
    }

    function actionButton(action, variant = 'outline') {
        const b = el('button', `ula-btn ula-btn--${variant} ula-btn--sm`, action.label || T['action.open']);
        b.type = 'button';
        b.addEventListener('click', () => perform(action));
        return b;
    }

    function item({ iconName, title, sub, meta, metaClass, unread, actions }) {
        const li = el('li', 'ula-assistant__item' + (unread ? ' is-unread' : ''));
        li.appendChild(icon(iconName));
        const text = el('div', 'ula-assistant__item-text');
        text.appendChild(el('p', 'ula-assistant__item-title', title));
        if (sub || meta) {
            const p = el('p', 'ula-assistant__item-sub');
            if (meta) p.appendChild(el('span', metaClass || 'ula-assistant__meta', meta));
            if (meta && sub) p.append(' · ');
            if (sub) p.append(sub);
            text.appendChild(p);
        }
        li.appendChild(text);
        if (actions?.length) {
            const box = el('div', 'ula-assistant__item-actions');
            actions.forEach((a) => box.appendChild(a));
            li.appendChild(box);
        }
        return li;
    }

    function section(title, items) {
        const s = el('section', 'ula-assistant__section');
        s.appendChild(el('h3', '', title));
        const ul = el('ul', 'ula-assistant__list');
        items.forEach((i) => ul.appendChild(i));
        s.appendChild(ul);
        return s;
    }

    function stateMessage(text, retry) {
        const box = el('div', 'ula-assistant__empty');
        box.appendChild(el('p', '', text));
        if (retry) {
            const b = el('button', 'ula-btn ula-btn--outline ula-btn--sm', t('retry'));
            b.type = 'button';
            b.addEventListener('click', retry);
            box.appendChild(b);
        }
        return box;
    }

    const meetingItem = (m) => item({
        iconName: m.status === 'active' ? 'videocam' : 'event',
        title: m.title,
        meta: m.status === 'active' ? t('live_now') : [m.day, m.time].filter(Boolean).join(' '),
        metaClass: m.status === 'active' ? 'ula-assistant__tag-live' : 'ula-assistant__meta',
        sub: [m.room, m.invited && m.organizer ? m.organizer : null].filter(Boolean).join(' · '),
        actions: m.action ? [actionButton(m.action, m.status === 'active' ? 'primary' : 'outline')] : [],
    });

    const taskItem = (x) => item({
        iconName: 'task_alt',
        title: x.title,
        meta: x.overdue ? t('overdue') : x.due,
        metaClass: x.overdue ? 'ula-assistant__tag-late' : 'ula-assistant__meta',
        sub: x.project,
        actions: x.action ? [actionButton(x.action)] : [],
    });

    function renderSummary() {
        const b = state.briefing;
        if (!b) {
            ui.body.appendChild(state.briefingError ? stateMessage(t('error'), loadBriefing) : stateMessage(t('loading')));
            return;
        }
        ui.body.appendChild(el('p', 'ula-assistant__greeting', b.greeting));
        ui.body.appendChild(el('p', 'ula-assistant__headline', b.headline ? b.headline.text : t('answer.all_clear')));
        if (b.meetings.length) ui.body.appendChild(section(t('section.meetings'), b.meetings.map(meetingItem)));
        if (b.invitations.length) ui.body.appendChild(section(t('section.invitations'), b.invitations.map(meetingItem)));
        const tasks = [...b.tasks.overdue, ...b.tasks.today];
        if (tasks.length) ui.body.appendChild(section(t('section.tasks'), tasks.map(taskItem)));
        if (b.messages.length) {
            ui.body.appendChild(section(t('section.messages'), b.messages.map((m) => item({
                iconName: 'chat', title: m.from, sub: m.preview, meta: m.ago, metaClass: 'ula-assistant__item-sub', actions: [actionButton(m.action)],
            }))));
        }
        if (b.notifications.latest.length) {
            ui.body.appendChild(section(t('section.alerts'), b.notifications.latest.map((n) => item({
                iconName: 'notifications', title: n.title, sub: n.body, meta: n.ago, metaClass: 'ula-assistant__item-sub', unread: true,
                actions: n.action ? [actionButton({ ...n.action, label: T['action.open'] })] : [],
            }))));
        }
        ui.body.appendChild(el('p', 'ula-assistant__note', t('rules_note')));
    }

    function renderAlerts() {
        const filters = el('div', 'ula-assistant__filters');
        ['all', 'unread', 'task', 'meeting', 'office'].forEach((f) => {
            const c = el('button', 'ula-assistant__chip', t('filter.' + f));
            c.type = 'button';
            c.setAttribute('aria-pressed', state.filter === f ? 'true' : 'false');
            c.addEventListener('click', () => { state.filter = f; state.alerts = null; render(); loadAlerts(); });
            filters.appendChild(c);
        });
        ui.body.appendChild(filters);

        if (state.unread > 0) {
            const all = el('button', 'ula-btn ula-btn--ghost ula-btn--sm', t('mark_all'));
            all.type = 'button';
            all.addEventListener('click', markAll);
            ui.body.appendChild(all);
        }
        if (state.alertsError) { ui.body.appendChild(stateMessage(t('error'), loadAlerts)); return; }
        if (!state.alerts) { ui.body.appendChild(stateMessage(t('loading'))); return; }
        if (!state.alerts.length) { ui.body.appendChild(stateMessage(t('empty_alerts'))); return; }

        const ul = el('ul', 'ula-assistant__list');
        state.alerts.forEach((n) => {
            const actions = [];
            const url = safeUrl(n.action_url);
            if (url) {
                const open = el('button', 'ula-btn ula-btn--outline ula-btn--sm', T['action.open']);
                open.type = 'button';
                open.addEventListener('click', async () => { if (!n.is_read) await markRead(n.id); window.location.href = url; });
                actions.push(open);
            }
            if (!n.is_read) {
                const read = el('button', 'ula-assistant__icon-btn');
                read.type = 'button';
                read.setAttribute('aria-label', t('mark_read'));
                read.title = t('mark_read');
                read.appendChild(icon('done'));
                read.addEventListener('click', () => markRead(n.id));
                actions.push(read);
            }
            ul.appendChild(item({
                iconName: n.type?.startsWith('task') ? 'task_alt' : n.type?.startsWith('meeting') ? 'event' : 'notifications',
                title: n.title, sub: n.body, meta: n.created_at_human, metaClass: 'ula-assistant__item-sub', unread: !n.is_read, actions,
            }));
        });
        ui.body.appendChild(ul);
    }

    function renderAsk() {
        const log = el('div', 'ula-assistant__log');
        if (!state.log.length && state.briefing) log.appendChild(el('div', 'ula-assistant__msg is-bot', state.briefing.greeting));
        state.log.forEach((m) => {
            if (m.pending) { log.appendChild(el('div', 'ula-assistant__msg is-bot', '…')); return; }
            const bubble = el('div', 'ula-assistant__msg ' + (m.me ? 'is-me' : 'is-bot'), m.text);
            log.appendChild(bubble);
            if (!m.me && m.items?.length) {
                const ul = el('ul', 'ula-assistant__list');
                m.items.forEach((x) => ul.appendChild(item({
                    iconName: x.kind === 'meeting' ? (x.live ? 'videocam' : 'event') : x.kind === 'task' ? 'task_alt' : 'chat',
                    title: x.title, sub: x.subtitle,
                    meta: x.overdue ? t('overdue') : x.meta,
                    metaClass: x.overdue ? 'ula-assistant__tag-late' : x.live ? 'ula-assistant__tag-live' : 'ula-assistant__meta',
                    actions: x.action ? [actionButton(x.action, x.live ? 'primary' : 'outline')] : [],
                })));
                log.appendChild(ul);
            }
            if (!m.me && m.action) log.appendChild(actionButton({ ...m.action, label: m.action.label || T['action.view'] }));
        });
        ui.body.appendChild(log);

        const chips = el('div', 'ula-assistant__filters');
        (state.suggestions || []).forEach((s) => {
            const c = el('button', 'ula-assistant__chip', s);
            c.type = 'button';
            c.addEventListener('click', () => ask(s));
            chips.appendChild(c);
        });
        ui.body.appendChild(chips);
        ui.body.scrollTop = ui.body.scrollHeight;
    }

    function render() {
        if (!isOpen()) return;
        ui.body.textContent = '';
        if (state.tab === 'summary') renderSummary();
        else if (state.tab === 'alerts') renderAlerts();
        else renderAsk();
    }

    // ── wiring ──

    ui.toggle.addEventListener('click', () => (isOpen() ? closePanel() : openPanel()));
    ui.close.addEventListener('click', () => closePanel());
    ui.bubbleClose.addEventListener('click', hideBubble);
    ui.bubble.addEventListener('mouseenter', () => clearTimeout(state.bubbleTimer));
    ui.bubble.addEventListener('focusin', () => clearTimeout(state.bubbleTimer));

    ui.panel.querySelectorAll('[role="tab"]').forEach((b) => b.addEventListener('click', () => selectTab(b.dataset.tab)));
    ui.panel.querySelector('[role="tablist"]').addEventListener('keydown', (e) => {
        if (!['ArrowLeft', 'ArrowRight', 'Home', 'End'].includes(e.key)) return;
        const tabs = [...ui.panel.querySelectorAll('[role="tab"]')];
        let i = tabs.findIndex((x) => x.dataset.tab === state.tab);
        const forward = (e.key === 'ArrowRight') !== (document.dir === 'rtl' || document.documentElement.dir === 'rtl');
        if (e.key === 'Home') i = 0;
        else if (e.key === 'End') i = tabs.length - 1;
        else i = (i + (forward ? 1 : tabs.length - 1)) % tabs.length;
        e.preventDefault();
        selectTab(tabs[i].dataset.tab);
        tabs[i].focus();
    });

    ui.askForm.addEventListener('submit', (e) => {
        e.preventDefault();
        const v = ui.input.value;
        ui.input.value = '';
        ask(v);
    });

    // Keys pressed inside the assistant must not walk the avatar or trigger office shortcuts.
    root.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            if (isOpen()) { e.preventDefault(); closePanel(); } else if (!ui.bubble.hidden) { hideBubble(); }
        }
        e.stopPropagation();
    });

    // Follow the bell: no polling of our own for alerts.
    document.addEventListener('notifications:updated', (e) => {
        setUnread(e.detail?.unread || 0);
        if (isOpen() && state.tab === 'alerts') loadAlerts();
    });
    let arrivedTimer = 0;
    document.addEventListener('notifications:arrived', () => {
        clearTimeout(arrivedTimer);
        arrivedTimer = setTimeout(loadBriefing, 1500);
    });

    const showOnline = () => { ui.offline.hidden = navigator.onLine !== false; };
    window.addEventListener('online', () => { showOnline(); loadBriefing(); });
    window.addEventListener('offline', showOnline);
    showOnline();

    // The briefing (meeting countdowns) refreshes every 5 minutes while the tab is visible.
    setInterval(() => { if (document.visibilityState === 'visible') loadBriefing(); }, 5 * 60 * 1000);
    setTimeout(loadBriefing, 1500);
})();
