@php
    $mwText = [
        'widgets' => collect(\App\Domains\Projects\Controllers\MyWorkController::WIDGETS)->mapWithKeys(fn ($w) => [$w => [
            'ar' => __('pm.widget_'.$w),
            'en' => trans('pm.widget_'.$w, [], 'en'),
        ]]),
        'groups' => [
            'overdue' => __('pm.mywork_overdue'),
            'today' => __('pm.mywork_today'),
            'week' => __('pm.mywork_next7'),
            'later' => __('pm.mywork_later'),
            'none' => __('pm.mywork_no_date'),
        ],
        'priorities' => collect(\App\Domains\Projects\Models\Task::PRIORITIES)->mapWithKeys(fn ($p) => [$p => __(ucfirst($p))]),
        'statuses' => ['backlog' => __('Backlog'), 'ready' => __('Ready'), 'in_progress' => __('In Progress'), 'review' => __('Review'), 'qa' => __('QA')],
        'health' => [
            'on_track' => ['label' => __('pm.health_on_track'), 'tone' => 'palm', 'icon' => 'rocket_launch'],
            'at_risk' => ['label' => __('pm.health_at_risk'), 'tone' => 'gold', 'icon' => 'warning'],
            'off_track' => ['label' => __('pm.health_off_track'), 'tone' => 'terracotta', 'icon' => 'error'],
        ],
        'summary' => [
            'open' => __('pm.mydash_open'),
            'overdue' => __('pm.mywork_overdue'),
            'due_week' => __('pm.mydash_due_week'),
            'done_week' => __('pm.mydash_done_week'),
        ],
        'showEnglish' => app()->getLocale() === 'ar',
        'locale' => app()->getLocale(),
    ];
@endphp
<script nonce="{{ $cspNonce ?? '' }}">
    // ── My work: planner across projects + personal dashboard widgets ──
    const MW = { text: @json($mwText), scope: 'assigned', view: 'planner', layout: [], available: [], data: {}, loaded: {} };

    const mwEsc = s => String(s ?? '').replace(/[&<>"']/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
    const mwTaskUrl = t => t.project ? `/projects/${encodeURIComponent(t.project.id)}?task=${encodeURIComponent(t.id)}` : '/dashboard#all-tasks';
    const mwPriorityTone = { urgent: 'terracotta', high: 'gold', medium: 'sand', low: 'stone' };
    const mwRtl = () => document.documentElement.dir === 'rtl';

    async function mwApi(method, path, body) {
        const res = await fetch(`/api/v1/organizations/${ORG_ID}${path}`, {
            method, credentials: 'same-origin',
            headers: { 'Accept': 'application/json', 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF_TOKEN },
            body: body === undefined ? undefined : JSON.stringify(body),
        });
        const data = await res.json().catch(() => ({}));
        if (!res.ok) throw new Error(data.message || res.statusText);
        return data;
    }

    function mwStore(key, value) { try { localStorage.setItem(key, value); } catch (e) {} }
    function mwRead(key) { try { return localStorage.getItem(key); } catch (e) { return null; } }

    // ── Planner ──
    function mwSetView(view) {
        MW.view = view;
        mwStore('mw.view', view);
        document.getElementById('mywork-planner').hidden = view !== 'planner';
        document.getElementById('mywork-board').hidden = view !== 'board';
        document.getElementById('mw-scopes').hidden = view !== 'planner';
        document.getElementById('mw-view-planner').setAttribute('aria-selected', String(view === 'planner'));
        document.getElementById('mw-view-board').setAttribute('aria-selected', String(view === 'board'));
        if (view === 'planner') mwLoadPlanner();
    }

    function mwSetScope(scope) {
        MW.scope = scope;
        document.querySelectorAll('#mw-scopes .mw-chip').forEach(b => b.setAttribute('aria-pressed', String(b.dataset.scope === scope)));
        mwLoadPlanner();
    }

    async function mwLoadPlanner() {
        const host = document.getElementById('mywork-planner');
        try {
            const data = await mwApi('GET', `/my-work/tasks?scope=${MW.scope}&lang=${MW.text.locale}`);
            mwRenderPlanner(host, data);
        } catch (e) { host.innerHTML = `<div class="pm-empty">${mwEsc(e.message)}</div>`; }
    }

    function mwGroupOf(due, today) {
        if (!due) return 'none';
        if (due < today) return 'overdue';
        if (due === today) return 'today';
        const diff = (new Date(due) - new Date(today)) / 86400000;
        return diff <= 7 ? 'week' : 'later';
    }

    function mwRenderPlanner(host, data) {
        const T = MW.text;
        document.getElementById('mw-summary').innerHTML =
            `<span class="pm-num">${data.tasks.length}</span> {{ __('pm.mydash_open') }} · <span class="pm-num">${data.done_this_week}</span> {{ __('pm.mydash_done_week') }}`;
        if (!data.tasks.length) {
            host.innerHTML = `<div class="pm-empty"><span class="material-symbols-rounded" aria-hidden="true">task_alt</span><br>{{ __('pm.mywork_empty') }}</div>`;
            return;
        }
        const groups = { overdue: [], today: [], week: [], later: [], none: [] };
        data.tasks.forEach(t => groups[mwGroupOf(t.due, data.today)].push(t));
        host.innerHTML = Object.entries(groups).filter(([, list]) => list.length).map(([key, list]) => `
            <details class="mw-group mw-group--${key}" open>
                <summary>
                    <span class="material-symbols-rounded mw-group-chevron" aria-hidden="true">expand_more</span>
                    <span class="mw-group-title">${mwEsc(T.groups[key])}</span>
                    <span class="pm-label pm-label--${key === 'overdue' ? 'terracotta' : 'stone'} pm-num">${list.length}</span>
                </summary>
                <ul class="mw-task-list">${list.map(t => `
                    <li class="mw-task">
                        <span class="pm-label pm-label--${mwPriorityTone[t.priority] || 'stone'}">${mwEsc(T.priorities[t.priority] || t.priority)}</span>
                        <a class="mw-task-title" href="${mwTaskUrl(t)}"><span dir="auto">${mwEsc(t.title)}</span></a>
                        <span class="mw-task-meta">
                            ${t.labels.map(l => `<span class="pm-label pm-label--${mwEsc(l.tone)}">${mwEsc(l.name)}</span>`).join('')}
                            <span class="pm-muted">${mwEsc(t.state || T.statuses[t.status] || t.status)}</span>
                            ${t.project ? `<span class="pm-label pm-label--stone pm-num" title="${mwEsc(t.project.name)}">${mwEsc(t.project.code || t.project.name)}</span>` : ''}
                            ${t.due ? `<span class="pm-num mw-task-due ${key === 'overdue' ? 'is-overdue' : ''}">${mwEsc(t.due)}</span>` : ''}
                        </span>
                    </li>`).join('')}
                </ul>
            </details>`).join('');
    }

    // ── Dashboard widgets ──
    function mwWidgetTitle(key) {
        const w = MW.text.widgets[key];
        return `<div class="ula-headline-group mw-widget-head">
            <h3 class="ula-headline-ar">${mwEsc(w.ar)}</h3>
            ${MW.text.showEnglish ? `<span class="ula-headline-en">${mwEsc(w.en)}</span>` : ''}
        </div>`;
    }

    /** Horizontal bars: one series, value labelled at the bar end. */
    function mwBars(rows, unit = '') {
        const max = Math.max(1, ...rows.map(r => r.value));
        if (!rows.some(r => r.value)) return `<div class="pm-empty">{{ __('pm.mydash_nothing_yet') }}</div>`;
        return `<ul class="mw-bars">${rows.map(r => `
            <li class="mw-bar-row">
                <span class="mw-bar-label" dir="auto" title="${mwEsc(r.label)}">${mwEsc(r.label)}</span>
                <span class="mw-bar-track"><span class="mw-bar" style="inline-size: ${Math.max(r.value ? 2 : 0, (r.value / max) * 100)}%"></span></span>
                <span class="mw-bar-value pm-num">${r.value}${unit}</span>
            </li>`).join('')}</ul>`;
    }

    /** Daily columns with a hover / focus tooltip and a table view. */
    function mwColumns(days, label, unit) {
        const max = Math.max(1, ...days.map(d => d.value));
        const every = days.length > 7 ? 2 : 1;
        const fmt = d => d.slice(5);
        return `
            <div class="mw-cols" role="img" aria-label="${mwEsc(label)}">
                ${days.map((d, i) => `
                    <div class="mw-col" tabindex="0" aria-label="${mwEsc(d.date)}: ${d.value} ${mwEsc(unit)}">
                        <span class="mw-col-bar ${d.value ? '' : 'is-zero'}" style="block-size: ${d.value ? Math.max(4, (d.value / max) * 100) : 0}%"></span>
                        <span class="mw-col-tip pm-num">${mwEsc(d.date)} · ${d.value} ${mwEsc(unit)}</span>
                        <span class="mw-col-x pm-num" aria-hidden="true">${(days.length - 1 - i) % every === 0 ? fmt(d.date) : ''}</span>
                    </div>`).join('')}
            </div>
            <details class="pm-bd-table">
                <summary>{{ __('pm.show_table') }}</summary>
                <table><thead><tr><th>{{ __('pm.date') }}</th><th>${mwEsc(unit)}</th></tr></thead>
                <tbody>${days.map(d => `<tr><td class="pm-num">${mwEsc(d.date)}</td><td class="pm-num">${d.value}</td></tr>`).join('')}</tbody></table>
            </details>`;
    }

    const MW_RENDER = {
        summary: d => `<div class="mw-stats">${Object.entries(MW.text.summary).map(([k, label]) => `
            <div class="mw-stat ${k === 'overdue' && d[k] ? 'is-alert' : ''}">
                <span class="mw-stat-value pm-num">${d[k]}</span>
                <span class="mw-stat-label">${k === 'overdue' && d[k] ? '<span class="material-symbols-rounded" aria-hidden="true">warning</span>' : ''}${mwEsc(label)}</span>
            </div>`).join('')}</div>`,

        upcoming: d => d.length ? `<ul class="mw-task-list">${d.map(t => `
            <li class="mw-task">
                <span class="pm-num mw-task-due">${mwEsc(t.due)}</span>
                <a class="mw-task-title" href="${mwTaskUrl(t)}"><span dir="auto">${mwEsc(t.title)}</span></a>
                ${t.project ? `<span class="pm-label pm-label--stone pm-num">${mwEsc(t.project.code)}</span>` : ''}
            </li>`).join('')}</ul>` : `<div class="pm-empty">{{ __('pm.mydash_no_upcoming') }}</div>`,

        by_state: d => mwBars(d.map(r => ({ label: MW.text.statuses[r.key] || r.key, value: r.value }))),

        completed_trend: d => mwColumns(d, MW.text.widgets.completed_trend.ar, @json(__('pm.tasks_word'))),

        time_week: d => `<div class="mw-hero"><span class="pm-num">${d.total}</span> <span class="pm-muted">{{ __('pm.hours_short') }}</span></div>`
            + mwColumns(d.days, MW.text.widgets.time_week.ar, @json(__('pm.hours_short'))),

        by_project: d => mwBars(d.map(r => ({ label: r.label, value: r.value }))),

        project_health: d => d.length ? `<ul class="mw-task-list">${d.map(p => {
            const h = MW.text.health[p.health];
            return `<li class="mw-task">
                <a class="mw-task-title" href="/projects/${encodeURIComponent(p.id)}"><span dir="auto">${mwEsc(p.name)}</span></a>
                ${h ? `<span class="pm-label pm-label--${h.tone}"><span class="material-symbols-rounded" aria-hidden="true" style="font-size: 14px;">${h.icon}</span>${mwEsc(h.label)}</span>
                       <span class="pm-num pm-muted">${mwEsc(p.updated)}</span>`
                    : `<span class="pm-muted">{{ __('pm.mydash_no_update') }}</span>`}
            </li>`; }).join('')}</ul>` : `<div class="pm-empty">{{ __('pm.mydash_no_projects') }}</div>`,
    };

    function mwRenderDashboard() {
        const grid = document.getElementById('mw-dash-grid');
        if (!MW.layout.length) {
            grid.innerHTML = `<div class="pm-empty">{{ __('pm.mydash_empty') }}</div>`;
            return;
        }
        grid.innerHTML = MW.layout.map(key => `
            <article class="mw-widget mw-widget--${key}">
                ${mwWidgetTitle(key)}
                ${MW.data[key] !== undefined ? MW_RENDER[key](MW.data[key]) : ''}
            </article>`).join('');
    }

    async function mwLoadDashboard() {
        try {
            const data = await mwApi('GET', '/my-work/dashboard');
            Object.assign(MW, { layout: data.widgets, available: data.available, data: data.data });
            mwRenderDashboard();
        } catch (e) { document.getElementById('mw-dash-grid').innerHTML = `<div class="pm-empty">${mwEsc(e.message)}</div>`; }
    }

    // Customise: tick widgets and order them.
    function mwToggleEditor(force) {
        const panel = document.getElementById('mw-dash-editor');
        const open = force === undefined ? panel.hidden : force;
        panel.hidden = !open;
        document.getElementById('mw-dash-customize').setAttribute('aria-expanded', String(open));
        if (open) {
            MW.draft = [...MW.layout, ...MW.available.filter(k => !MW.layout.includes(k))]
                .map(k => ({ key: k, on: MW.layout.includes(k) }));
            mwRenderEditor();
        }
    }

    function mwRenderEditor() {
        document.getElementById('mw-dash-editor-list').innerHTML = MW.draft.map((w, i) => `
            <div class="pm-list-row">
                <label class="mw-check pm-grow">
                    <input type="checkbox" ${w.on ? 'checked' : ''} onchange="MW.draft[${i}].on = this.checked">
                    <span>${mwEsc(MW.text.widgets[w.key].ar)}</span>
                </label>
                <button type="button" class="ula-btn ula-btn--ghost ula-btn--sm" ${i === 0 ? 'disabled' : ''} onclick="mwMoveWidget(${i}, -1)" aria-label="{{ __('pm.move_up') }} ${mwEsc(MW.text.widgets[w.key].ar)}"><span class="material-symbols-rounded" aria-hidden="true">arrow_upward</span></button>
                <button type="button" class="ula-btn ula-btn--ghost ula-btn--sm" ${i === MW.draft.length - 1 ? 'disabled' : ''} onclick="mwMoveWidget(${i}, 1)" aria-label="{{ __('pm.move_down') }} ${mwEsc(MW.text.widgets[w.key].ar)}"><span class="material-symbols-rounded" aria-hidden="true">arrow_downward</span></button>
            </div>`).join('');
    }

    function mwMoveWidget(i, dir) {
        const j = i + dir;
        [MW.draft[i], MW.draft[j]] = [MW.draft[j], MW.draft[i]];
        mwRenderEditor();
        document.querySelectorAll('#mw-dash-editor-list .pm-list-row')[j]?.querySelectorAll('button')[dir < 0 ? 0 : 1]?.focus();
    }

    async function mwSaveLayout() {
        try {
            const data = await mwApi('PUT', '/my-work/dashboard', { widgets: MW.draft.filter(w => w.on).map(w => w.key) });
            Object.assign(MW, { layout: data.widgets, data: data.data });
            mwToggleEditor(false);
            mwRenderDashboard();
            showToastNotification(@json(__('pm.mydash_saved')));
        } catch (e) { showToastNotification(mwEsc(e.message), 'error'); }
    }

    document.addEventListener('DOMContentLoaded', () => {
        if (document.getElementById('mw-dash-grid')) mwLoadDashboard();
        if (document.getElementById('mywork-planner')) mwSetView(mwRead('mw.view') === 'board' ? 'board' : 'planner');
    });
</script>
