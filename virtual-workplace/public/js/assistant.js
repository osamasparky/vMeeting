/**
 * The personal assistant — on every member page (dashboard, project hub, office, map editor).
 *
 * Speech bubble, two modes:
 *  - alerts:  one unread notification at a time (kind, time, title, detail), with Open,
 *             Mark as read and previous / next. It pops up when a new one arrives.
 *  - message: the one thing that matters now (a meeting starting, overdue tasks…).
 * Panel: the person's summary and a box to ask for things.
 *
 * Data comes only from the server, for the signed-in person:
 *  - GET  /assistant/briefing   summary (meetings, invitations, tasks, messages, alerts)
 *  - POST /assistant/ask        answers to typed requests (rule-based, read-only)
 *  - the existing notifications: the bell's "notifications:updated" events (no polling of our
 *    own where the bell is on the page), GET /api/notifications otherwise, and
 *    POST /api/notifications/{id}/read for "mark as read" (then "notifications:changed" to the bell).
 *
 * Actions only navigate: walk to a room in the office (window.ulaGoToRoom) or open the office at
 * that room, open a direct message (window.ulaOpenDm) or the chat, open a same-site link.
 */
(function () {
    'use strict';

    const root = document.getElementById('ula-assistant');
    if (!root) return;

    const T = window.UlaAssistantText || {};
    const OFFICE_TYPES = window.UlaAssistantOfficeTypes || [];
    const t = (k, rep) => {
        let s = T[k] || k;
        if (rep) for (const r in rep) s = s.split(':' + r).join(rep[r]);
        return s;
    };
    const $ = (id) => document.getElementById(id);
    const csrf = document.querySelector('meta[name="csrf-token"]')?.content || '';
    const store = (() => { try { return window.sessionStorage; } catch (e) { return null; } })();
    const rtl = () => (document.documentElement.dir || document.body.dir) === 'rtl';

    const ui = {
        toggle: $('ula-assistant-toggle'),
        badge: $('ula-assistant-badge'),
        bubble: $('ula-assistant-bubble'),
        bubbleIcon: $('ula-assistant-bubble-icon'),
        bubbleKicker: $('ula-assistant-bubble-kicker'),
        bubbleCounter: $('ula-assistant-bubble-counter'),
        bubbleText: $('ula-assistant-bubble-text'),
        bubbleSub: $('ula-assistant-bubble-sub'),
        bubbleAction: $('ula-assistant-bubble-action'),
        bubbleRead: $('ula-assistant-bubble-read'),
        bubblePrev: $('ula-assistant-bubble-prev'),
        bubbleNext: $('ula-assistant-bubble-next'),
        bubbleClose: $('ula-assistant-bubble-close'),
        panel: $('ula-assistant-panel'),
        alertsBtn: $('ula-assistant-alerts'),
        close: $('ula-assistant-close'),
        body: $('ula-assistant-body'),
        offline: $('ula-assistant-offline'),
        askForm: $('ula-assistant-ask'),
        input: $('ula-assistant-input'),
    };

    const state = {
        tab: 'summary',
        briefing: null,
        briefingAt: 0,
        briefingError: false,
        items: null, // latest notifications (from the bell, or fetched)
        unread: 0,
        bellSeen: false,
        mode: null, // 'alerts' | 'message' | null
        filter: 'all',
        index: 0,
        message: null,
        hideTimer: 0,
        log: [],
        suggestions: [],
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
            if (!state.bellSeen) setUnread(state.briefing.notifications?.unread || 0);
        } catch (e) {
            state.briefingError = true;
        }
        if (isOpen()) render();
    }

    /** Pages without the bell (the map editor) fetch the same list the bell uses. */
    async function loadItems() {
        try {
            const data = await call('/api/notifications');
            receiveItems(data.notifications || [], data.unread_count || 0);
        } catch (e) { /* the next tick retries */ }
    }

    function receiveItems(items, unread) {
        const before = new Set(unreadItems('all').map((n) => String(n.id)));
        state.items = items;
        setUnread(unread);
        const fresh = unreadItems('all').filter((n) => !before.has(String(n.id)));
        if (state.mode === 'alerts') renderBubble();
        return fresh;
    }

    function setUnread(n) {
        state.unread = n;
        ui.badge.hidden = n <= 0;
        ui.badge.textContent = n > 99 ? '99+' : String(n);
        ui.toggle.setAttribute('aria-label', n > 0 ? `${t('open')} — ${t('unread_badge', { count: n })}` : t('open'));
    }

    const categoryOf = (type) => {
        type = String(type || '');
        if (type.startsWith('task')) return 'task';
        if (type.startsWith('meeting')) return 'meeting';
        if (OFFICE_TYPES.includes(type) || type.startsWith('room')) return 'office';
        return 'other';
    };

    function unreadItems(filter) {
        return (state.items || []).filter((n) => !n.is_read && (filter === 'all' || categoryOf(n.type) === filter));
    }

    // ── actions (navigation only) ──

    function safeUrl(url) {
        // Same-site paths only: never javascript:, data: or another site.
        return typeof url === 'string' && url.startsWith('/') && !url.startsWith('//') ? url : null;
    }

    function perform(action) {
        if (!action) return;
        if (action.type === 'alerts') return showAlerts(action.filter || 'all', true);
        if (action.type === 'panel') return openPanel(action.tab || 'summary');
        if (action.type === 'room' && typeof window.ulaGoToRoom === 'function') {
            if (window.ulaGoToRoom(action.room_id)) { closePanel(false); hideBubble(); return; }
            return showMessage({ text: t('room_elsewhere') });
        }
        if (action.type === 'dm' && typeof window.ulaOpenDm === 'function' && window.ulaOpenDm(action.user_id)) {
            closePanel(false);
            hideBubble();
            return;
        }
        const url = safeUrl(action.url);
        if (url) window.location.href = url;
    }

    async function markRead(n) {
        n.is_read = true;
        setUnread(Math.max(0, state.unread - 1));
        try {
            const data = await call(`/api/notifications/${encodeURIComponent(n.id)}/read`, 'POST');
            setUnread(data.unread_count || 0);
        } catch (e) { /* the bell's next refresh corrects the count */ }
        document.dispatchEvent(new CustomEvent('notifications:changed'));
    }

    // ── speech bubble ──

    function hideBubble() {
        clearTimeout(state.hideTimer);
        state.mode = null;
        ui.bubble.hidden = true;
    }

    /** Hide by itself only when it popped up on its own, and not while being read. */
    function autoHide(ms) {
        clearTimeout(state.hideTimer);
        state.hideTimer = setTimeout(() => {
            if (!ui.bubble.matches(':hover') && !ui.bubble.contains(document.activeElement)) hideBubble();
        }, ms);
    }

    function showAlerts(filter = 'all', focus = false, startAt = 0) {
        closePanel(false);
        state.mode = 'alerts';
        state.filter = filter;
        state.index = startAt;
        if (!state.items) loadItems();
        renderBubble();
        clearTimeout(state.hideTimer);
        if (focus) (ui.bubbleAction.hidden ? ui.bubbleClose : ui.bubbleAction).focus();
    }

    function showMessage(message, auto = false) {
        state.mode = 'message';
        state.message = message;
        renderBubble();
        if (auto) autoHide(14000);
    }

    function renderBubble() {
        if (!state.mode) return;
        ui.bubble.hidden = false;
        ui.bubbleRead.hidden = true;
        ui.bubblePrev.hidden = true;
        ui.bubbleNext.hidden = true;
        ui.bubbleCounter.textContent = '';

        if (state.mode === 'message') {
            const m = state.message;
            ui.bubbleIcon.textContent = 'tips_and_updates';
            ui.bubbleKicker.textContent = m.kicker || t('name');
            ui.bubbleText.textContent = m.text;
            ui.bubbleSub.textContent = m.sub || '';
            setAction(m.action);
            return;
        }

        const list = unreadItems(state.filter);
        if (!list.length) {
            ui.bubbleIcon.textContent = 'notifications';
            ui.bubbleKicker.textContent = t('show_alerts');
            ui.bubbleText.textContent = state.items === null ? t('loading') : t(state.filter === 'all' ? 'no_more_alerts' : 'no_alerts_filtered');
            ui.bubbleSub.textContent = '';
            setAction(null);
            if (state.items !== null) autoHide(4000);
            return;
        }
        state.index = Math.min(Math.max(0, state.index), list.length - 1);
        const n = list[state.index];
        const kind = categoryOf(n.type);
        ui.bubbleIcon.textContent = { task: 'task_alt', meeting: 'event', office: 'apartment' }[kind] || 'notifications';
        ui.bubbleKicker.textContent = [t('alert_kind.' + kind), n.created_at_human].filter(Boolean).join(' · ');
        ui.bubbleCounter.textContent = list.length > 1 ? t('counter', { i: state.index + 1, n: list.length }) : '';
        ui.bubbleText.textContent = n.title || '';
        ui.bubbleSub.textContent = n.body || '';
        const url = safeUrl(n.action_url);
        setAction(url ? { type: 'url', url, label: t('action.open'), notification: n } : null);
        ui.bubbleRead.hidden = false;
        ui.bubbleRead.onclick = async () => {
            await markRead(n);
            renderBubble();
            (ui.bubbleAction.hidden ? ui.bubbleClose : ui.bubbleAction).focus();
        };
        ui.bubblePrev.hidden = ui.bubbleNext.hidden = list.length < 2;
        ui.bubblePrev.disabled = state.index === 0;
        ui.bubbleNext.disabled = state.index >= list.length - 1;
    }

    function setAction(action) {
        ui.bubbleAction.hidden = !action;
        if (!action) { ui.bubbleAction.onclick = null; return; }
        ui.bubbleAction.textContent = action.label || t('action.open');
        ui.bubbleAction.onclick = async () => {
            if (action.notification && !action.notification.is_read) await markRead(action.notification);
            if (state.mode === 'message') hideBubble();
            perform(action);
        };
    }

    function page(step) {
        if (state.mode !== 'alerts') return;
        state.index += step;
        renderBubble();
    }

    /** What to say on arrival: a meeting about to start beats alerts; otherwise the newest unread alert. */
    function greetOnce() {
        const b = state.briefing;
        if (!b || isOpen() || state.mode) return;
        const h = b.headline;
        const urgent = h && /^(live|soon)-/.test(h.key);
        const newest = unreadItems('all')[0];
        if (urgent) {
            const key = 'ula-assistant-said:' + h.key;
            if (store?.getItem(key)) return;
            store?.setItem(key, '1');
            return showMessage({ kicker: b.greeting, text: h.text, action: h.action }, true);
        }
        if (newest) {
            const key = 'ula-assistant-alert:' + newest.id;
            if (store?.getItem(key)) return;
            store?.setItem(key, '1');
            showAlerts('all');
            return autoHide(20000);
        }
        if (h) {
            const key = 'ula-assistant-said:' + h.key;
            if (store?.getItem(key)) return;
            store?.setItem(key, '1');
            showMessage({ kicker: b.greeting, text: h.text, action: h.action }, true);
        }
    }

    // ── panel ──

    const isOpen = () => !ui.panel.hidden;

    function openPanel(tab) {
        hideBubble();
        if (tab) state.tab = tab;
        ui.panel.hidden = false;
        root.classList.add('is-open');
        ui.toggle.setAttribute('aria-expanded', 'true');
        selectTab(state.tab);
        if (!state.briefing || Date.now() - state.briefingAt > 60000) loadBriefing();
        ui.panel.querySelector(`[data-tab="${state.tab}"]`)?.focus();
    }

    function closePanel(returnFocus = true) {
        if (!isOpen()) return;
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
        if (tab === 'ask') setTimeout(() => ui.input.focus(), 0);
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
            state.log.push({ me: false, text: t('error') });
        }
        render();
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
        const b = el('button', `ula-btn ula-btn--${variant} ula-btn--sm`, action.label || t('action.open'));
        b.type = 'button';
        b.addEventListener('click', () => perform(action));
        return b;
    }

    function item({ iconName, title, sub, meta, metaClass, actions }) {
        const li = el('li', 'ula-assistant__item');
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
        if (state.unread > 0) {
            const cta = el('div', 'ula-assistant__alerts-cta');
            cta.append(icon('notifications'), el('p', '', t('unread_cta', { count: state.unread })), actionButton({ type: 'alerts', filter: 'all', label: t('show_alerts') }, 'primary'));
            ui.body.appendChild(cta);
        }
        if (b.meetings.length) ui.body.appendChild(section(t('section.meetings'), b.meetings.map(meetingItem)));
        if (b.invitations.length) ui.body.appendChild(section(t('section.invitations'), b.invitations.map(meetingItem)));
        const tasks = [...b.tasks.overdue, ...b.tasks.today];
        if (tasks.length) ui.body.appendChild(section(t('section.tasks'), tasks.map(taskItem)));
        if (b.messages.length) {
            ui.body.appendChild(section(t('section.messages'), b.messages.map((m) => item({
                iconName: 'chat', title: m.from, sub: m.preview, meta: m.ago, metaClass: 'ula-assistant__item-sub', actions: [actionButton(m.action)],
            }))));
        }
        ui.body.appendChild(el('p', 'ula-assistant__note', t('rules_note')));
    }

    function renderAsk() {
        const log = el('div', 'ula-assistant__log');
        if (!state.log.length && state.briefing) log.appendChild(el('div', 'ula-assistant__msg is-bot', state.briefing.greeting));
        state.log.forEach((m) => {
            if (m.pending) { log.appendChild(el('div', 'ula-assistant__msg is-bot', '…')); return; }
            log.appendChild(el('div', 'ula-assistant__msg ' + (m.me ? 'is-me' : 'is-bot'), m.text));
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
            if (!m.me && m.action) log.appendChild(actionButton({ ...m.action, label: m.action.label || t('action.view') }));
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
        if (state.tab === 'ask') renderAsk();
        else renderSummary();
    }

    // ── wiring ──

    ui.toggle.addEventListener('click', () => (isOpen() ? closePanel() : openPanel()));
    ui.close.addEventListener('click', () => closePanel());
    ui.alertsBtn.addEventListener('click', () => showAlerts('all', true));
    ui.bubbleClose.addEventListener('click', () => { hideBubble(); ui.toggle.focus(); });
    ui.bubblePrev.addEventListener('click', () => page(-1));
    ui.bubbleNext.addEventListener('click', () => page(1));
    ui.bubble.addEventListener('mouseenter', () => clearTimeout(state.hideTimer));
    ui.bubble.addEventListener('focusin', () => clearTimeout(state.hideTimer));

    ui.panel.querySelectorAll('[role="tab"]').forEach((b) => b.addEventListener('click', () => selectTab(b.dataset.tab)));
    ui.panel.querySelector('[role="tablist"]').addEventListener('keydown', (e) => {
        if (!['ArrowLeft', 'ArrowRight', 'Home', 'End'].includes(e.key)) return;
        const tabs = [...ui.panel.querySelectorAll('[role="tab"]')];
        let i = tabs.findIndex((x) => x.dataset.tab === state.tab);
        const forward = (e.key === 'ArrowRight') !== rtl();
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

    // Inside the assistant, keys belong to it: no avatar walking or page shortcuts underneath.
    root.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            if (isOpen()) { e.preventDefault(); closePanel(); } else if (!ui.bubble.hidden) { e.preventDefault(); hideBubble(); ui.toggle.focus(); }
        } else if (ui.bubble.contains(e.target) && state.mode === 'alerts' && (e.key === 'ArrowLeft' || e.key === 'ArrowRight')) {
            e.preventDefault();
            page((e.key === 'ArrowLeft') === rtl() ? 1 : -1);
        }
        e.stopPropagation();
    });

    // Follow the bell where it is on the page.
    document.addEventListener('notifications:updated', (e) => {
        const first = !state.bellSeen;
        state.bellSeen = true;
        const fresh = receiveItems(e.detail?.items || [], e.detail?.unread || 0);
        if (first) return greetOnce();
        // Something new arrived: show it (unless the person is busy in the panel).
        if (fresh.length && !isOpen() && state.mode !== 'message') {
            store?.setItem('ula-assistant-alert:' + fresh[0].id, '1');
            showAlerts('all', false, 0);
            autoHide(20000);
        }
    });
    let arrivedTimer = 0;
    document.addEventListener('notifications:arrived', () => {
        clearTimeout(arrivedTimer);
        arrivedTimer = setTimeout(loadBriefing, 1500);
    });

    const showOnline = () => { ui.offline.hidden = navigator.onLine !== false; };
    window.addEventListener('online', () => { showOnline(); loadBriefing(); if (!state.bellSeen) loadItems(); });
    window.addEventListener('offline', showOnline);
    showOnline();

    // In the office, "/office?room=…" (a Join from another page) walks to that room once the map is ready.
    const room = new URLSearchParams(window.location.search).get('room');
    if (room && /^[\w-]{1,64}$/.test(room)) {
        let tries = 0;
        const go = () => {
            if (typeof window.ulaGoToRoom === 'function' && window.ulaGoToRoom(room)) return;
            if (++tries < 20) setTimeout(go, 500);
        };
        setTimeout(go, 800);
    }

    // The briefing (meeting countdowns) refreshes every 5 minutes while the page is visible; pages
    // without the bell also refresh the alert list then.
    setInterval(() => {
        if (document.visibilityState !== 'visible') return;
        loadBriefing();
        if (!state.bellSeen) loadItems();
    }, 5 * 60 * 1000);
    setTimeout(async () => {
        await loadBriefing();
        if (!state.bellSeen) await loadItems();
        greetOnce();
    }, 1500);
})();
