<script nonce="{{ $cspNonce ?? '' }}">
    // ── Sprints: list, create, start, complete (with carry-over), tasks, burndown ──
    PM.sprints = [];
    PM.sprint = null;
    const PM_SPRINT_STATUS = {
        planned: { label: @json(__('pm.sprint_planned')), tone: 'stone' },
        active: { label: @json(__('pm.sprint_active')), tone: 'palm' },
        completed: { label: @json(__('pm.sprint_completed')), tone: 'sand' },
        closed: { label: @json(__('pm.sprint_completed')), tone: 'sand' },
    };
    const pmIsRtl = () => document.documentElement.dir === 'rtl' || getComputedStyle(document.body).direction === 'rtl';

    async function pmLoadSprints(selectId) {
        try {
            PM.sprints = (await pmApi('GET', `/projects/${PM.projectId}/sprints`)).sprints;
        } catch (e) { PM.sprints = []; }
        pmRenderSprintList();
        const pick = selectId || PM.sprint?.id || (PM.sprints.find(s => s.status === 'active') || PM.sprints[0])?.id;
        if (pick) pmSelectSprint(pick);
    }

    function pmRenderSprintList() {
        const list = document.getElementById('pm-sprint-list');
        if (!list) return;
        if (!PM.sprints.length) {
            list.innerHTML = `<div class="pm-empty">{{ __('pm.no_sprints') }}</div>`;
            return;
        }
        list.innerHTML = PM.sprints.map(s => {
            const st = PM_SPRINT_STATUS[s.status] || PM_SPRINT_STATUS.planned;
            return `<button type="button" class="pm-list-row pm-sprint-row" aria-current="${PM.sprint?.id === s.id}" onclick="pmSelectSprint(${s.id})">
                        <span class="pm-grow" style="font-weight: 800;">${pmEsc(s.name)}</span>
                        <span class="pm-label pm-label--${st.tone}">${st.label}</span>
                        <span class="pm-muted pm-num">${pmEsc((s.start_date || '').slice(0, 10))} → ${pmEsc((s.end_date || '').slice(0, 10))}</span>
                        <span class="pm-muted"><span class="pm-num">${s.tasks_count ?? 0}</span> {{ __('pm.tasks_word') }}</span>
                    </button>`;
        }).join('');
    }

    async function pmSelectSprint(id) {
        try {
            const data = await pmApi('GET', `/projects/${PM.projectId}/sprints/${id}`);
            PM.sprint = data.sprint;
            PM.sprintBurndown = data.burndown;
            pmRenderSprintList();
            pmRenderSprintDetail();
        } catch (e) { showHubToast(pmEsc(e.message)); }
    }

    function pmRenderSprintDetail() {
        const s = PM.sprint;
        const box = document.getElementById('pm-sprint-detail');
        if (!box || !s) return;
        box.style.display = '';
        const st = PM_SPRINT_STATUS[s.status] || PM_SPRINT_STATUS.planned;
        document.getElementById('pm-sprint-name').textContent = s.name;
        document.getElementById('pm-sprint-meta').innerHTML =
            `<span class="pm-label pm-label--${st.tone}">${st.label}</span> · <span class="pm-num">${pmEsc(s.start_date.slice(0, 10))} → ${pmEsc(s.end_date.slice(0, 10))}</span>${s.goal ? ' · ' + pmEsc(s.goal) : ''}`;

        const actions = [];
        if (PM.can.editProject && s.status === 'planned') {
            actions.push(`<button type="button" class="ula-btn ula-btn--primary ula-btn--md" onclick="pmStartSprint()"><span class="material-symbols-rounded" aria-hidden="true">play_arrow</span><span>{{ __('pm.start_sprint') }}</span></button>`);
        }
        if (PM.can.editProject && s.status === 'active') {
            actions.push(`<button type="button" class="ula-btn ula-btn--primary ula-btn--md" onclick="pmOpenCompleteSprint()"><span class="material-symbols-rounded" aria-hidden="true">task_alt</span><span>{{ __('pm.complete_sprint') }}</span></button>`);
        }
        if (PM.can.editProject && s.status !== 'completed') {
            actions.push(`<button type="button" class="ula-btn ula-btn--ghost ula-btn--md" onclick="pmDeleteSprint()"><span class="material-symbols-rounded" aria-hidden="true">delete</span><span>{{ __('pm.delete') }}</span></button>`);
        }
        document.getElementById('pm-sprint-actions').innerHTML = actions.join('');
        const addBtn = document.getElementById('pm-sprint-add-btn');
        if (addBtn) addBtn.style.display = s.status === 'completed' ? 'none' : '';

        const tasks = s.tasks || [];
        const editable = PM.can.editTasks && s.status !== 'completed';
        document.getElementById('pm-sprint-tasks').innerHTML = tasks.length ? tasks.map(t => `
            <div class="pm-list-row">
                <span class="pm-num pm-muted">#${t.task_number}</span>
                <button type="button" class="pm-grow" style="all: unset; cursor: pointer; font-weight: 700; min-block-size: var(--ula-size-touch-target); display: flex; align-items: center;" onclick="openTaskInspector('${pmEsc(t.id)}')">${pmEsc(t.title)}</button>
                ${(t.labels || []).map(pmLabelChip).join('')}
                <span class="pm-label pm-label--${t.status === 'done' ? 'palm' : 'stone'}">${pmEsc(PM.statusLabels[t.status] || t.status)}</span>
                ${editable
                    ? `<label class="pm-row" style="gap: var(--ula-space-2);"><span class="pm-muted">{{ __('pm.points') }}</span><input type="number" min="0" max="100" class="pm-field pm-num pm-points-input" value="${t.story_points ?? ''}" onchange="pmSetPoints('${pmEsc(t.id)}', this.value)" aria-label="{{ __('pm.points') }} #${t.task_number}"></label>
                       <button type="button" class="ula-btn ula-btn--ghost ula-btn--sm" onclick="pmRemoveFromSprint('${pmEsc(t.id)}')" aria-label="{{ __('pm.remove_from_sprint') }} #${t.task_number}"><span class="material-symbols-rounded" aria-hidden="true">remove_circle</span></button>`
                    : `<span class="pm-muted"><span class="pm-num">${t.story_points ?? 0}</span> {{ __('pm.points') }}</span>`}
            </div>`).join('') : `<div class="pm-empty">{{ __('pm.sprint_empty') }}</div>`;

        pmRenderBurndown(document.getElementById('pm-burndown'), PM.sprintBurndown);
    }

    async function pmCreateSprint(e) {
        e.preventDefault();
        const f = e.target;
        try {
            const data = await pmApi('POST', `/projects/${PM.projectId}/sprints`, {
                name: f.name.value.trim(), start_date: f.start_date.value, end_date: f.end_date.value,
            });
            f.reset();
            showHubToast(@json(__('pm.sprint_created')));
            pmLoadSprints(data.sprint.id);
        } catch (err) { showHubToast(pmEsc(err.message)); }
    }

    async function pmStartSprint() {
        try {
            await pmApi('POST', `/projects/${PM.projectId}/sprints/${PM.sprint.id}/start`);
            showHubToast(@json(__('pm.sprint_started')));
            pmLoadSprints(PM.sprint.id);
        } catch (err) { showHubToast(pmEsc(err.message)); }
    }

    function pmOpenCompleteSprint() {
        const s = PM.sprint;
        const open = (s.tasks || []).filter(t => t.status !== 'done').length;
        document.getElementById('pm-complete-summary').textContent =
            @json(__('pm.complete_summary')).replace(':open', open).replace(':total', (s.tasks || []).length);
        const targets = PM.sprints.filter(x => x.id !== s.id && !['completed', 'closed'].includes(x.status));
        document.getElementById('pm-carry-to').innerHTML =
            `<option value="">{{ __('pm.backlog') }}</option>` + targets.map(x => `<option value="${x.id}">${pmEsc(x.name)}</option>`).join('');
        document.getElementById('pm-complete-sprint-modal').style.display = 'flex';
    }

    async function pmConfirmCompleteSprint(e) {
        e.preventDefault();
        const target = e.target.carry_to.value;
        try {
            const data = await pmApi('POST', `/projects/${PM.projectId}/sprints/${PM.sprint.id}/complete`, { carry_to_sprint_id: target ? Number(target) : null });
            document.getElementById('pm-complete-sprint-modal').style.display = 'none';
            showHubToast(@json(__('pm.sprint_completed_toast')).replace(':n', data.carried_over));
            PM.dirty = true;
            pmLoadSprints(PM.sprint.id);
        } catch (err) { showHubToast(pmEsc(err.message)); }
    }

    async function pmDeleteSprint() {
        if (!confirm(@json(__('pm.delete_sprint_confirm')))) return;
        try {
            await pmApi('DELETE', `/projects/${PM.projectId}/sprints/${PM.sprint.id}`);
            PM.sprint = null;
            document.getElementById('pm-sprint-detail').style.display = 'none';
            PM.tasks.forEach(t => { if (t.sprint === PM.sprint?.id) t.sprint = null; });
            pmLoadSprints();
        } catch (err) { showHubToast(pmEsc(err.message)); }
    }

    async function pmSetSprint(taskId, sprintId, points) {
        const body = { sprint_id: sprintId };
        if (points !== undefined) body.story_points = points === '' ? null : Number(points);
        await pmApi('PATCH', `/tasks/${taskId}/sprint`, body);
        const t = PM.tasks.find(x => x.id === taskId);
        if (t) { t.sprint = sprintId; if (points !== undefined) t.points = body.story_points; }
        PM.dirty = true;
    }

    async function pmSetPoints(taskId, value) {
        try {
            await pmSetSprint(taskId, PM.sprint.id, value);
            pmSelectSprint(PM.sprint.id);
        } catch (err) { showHubToast(pmEsc(err.message)); }
    }

    async function pmRemoveFromSprint(taskId) {
        try {
            await pmSetSprint(taskId, null);
            pmLoadSprints(PM.sprint.id);
        } catch (err) { showHubToast(pmEsc(err.message)); }
    }

    function pmPickSprintTasks(trigger) {
        const candidates = PM.tasks.filter(t => !t.sprint && t.status !== 'done');
        const options = candidates.map(t => ({ id: t.id, html: `<span class="pm-num pm-muted">#${t.number}</span><span>${pmEsc(t.title)}</span>` }));
        let picked = [];
        pmOpenPicker(trigger, options, [], ids => { picked = ids; },
            `<div style="padding: var(--ula-space-2); border-top: 1px solid var(--ula-border-subtle); margin-top: var(--ula-space-2);">
                <button type="button" class="ula-btn ula-btn--primary ula-btn--sm" style="inline-size: 100%;" onclick="pmAddPickedToSprint()">{{ __('pm.add_to_sprint') }}</button></div>`);
        window.pmAddPickedToSprint = async () => {
            try {
                for (const id of picked) await pmSetSprint(id, PM.sprint.id);
                document.querySelectorAll('.pm-picker-menu').forEach(m => m.remove());
                pmLoadSprints(PM.sprint.id);
            } catch (err) { showHubToast(pmEsc(err.message)); }
        };
    }

    // ── Burndown: remaining (solid + wash) vs ideal (dashed); legend, end label, crosshair, table ──
    function pmRenderBurndown(host, bd) {
        if (!host) return;
        if (!bd || !bd.days || !bd.days.length || !bd.total) {
            host.innerHTML = `<div class="pm-empty">{{ __('pm.burndown_empty') }}</div>`;
            return;
        }
        const unit = bd.unit === 'points' ? @json(__('pm.points')) : @json(__('pm.tasks_word'));
        const W = Math.max(320, host.clientWidth || 640), H = 240;
        const m = { top: 16, bottom: 28, start: 40, end: 72 };
        const rtl = pmIsRtl();
        const n = bd.days.length;
        const niceMax = (v) => { const p = Math.pow(10, Math.floor(Math.log10(v || 1))); return Math.ceil(v / p) * p; };
        const yMax = niceMax(bd.total);
        const plotW = W - m.start - m.end, plotH = H - m.top - m.bottom;
        // In RTL the time axis runs right-to-left, like the rest of the page. SVG text
        // inherits the page direction, so text-anchor start/end already flip — only offsets do.
        const x = i => { const pos = n === 1 ? 0 : (i / (n - 1)) * plotW; return rtl ? W - m.start - pos : m.start + pos; };
        const y = v => m.top + plotH - (v / yMax) * plotH;

        const ticks = [0, 0.25, 0.5, 0.75, 1].map(f => Math.round(yMax * f));
        // Plot spans [start, W-end] in LTR and [end, W-start] in RTL.
        const gx1 = rtl ? m.end : m.start, gx2 = rtl ? W - m.start : W - m.end;
        const grid = ticks.map(t => `<line class="pm-bd-grid" x1="${gx1}" x2="${gx2}" y1="${y(t)}" y2="${y(t)}"/>
            <text class="pm-bd-axis-text" x="${rtl ? W - m.start + 12 : m.start - 12}" y="${y(t) + 4}" text-anchor="end">${t}</text>`).join('');
        const labelEvery = Math.ceil(n / 7);
        const xLabels = bd.days.map((d, i) => (i % labelEvery === 0 || i === n - 1)
            ? `<text class="pm-bd-axis-text" x="${x(i)}" y="${H - 8}" text-anchor="middle">${d.date.slice(5)}</text>` : '').join('');

        const ideal = bd.days.map((d, i) => `${i ? 'L' : 'M'}${x(i)},${y(d.ideal)}`).join(' ');
        const pts = bd.days.map((d, i) => [i, d.remaining]).filter(p => p[1] !== null);
        const actual = pts.map(([i, v], k) => `${k ? 'L' : 'M'}${x(i)},${y(v)}`).join(' ');
        const area = pts.length ? `${actual} L${x(pts[pts.length - 1][0])},${y(0)} L${x(pts[0][0])},${y(0)} Z` : '';
        const last = pts[pts.length - 1];
        const endLabel = last ? `<circle class="pm-bd-dot" cx="${x(last[0])}" cy="${y(last[1])}" r="5"/>
            <text class="pm-bd-label" x="${x(last[0]) + (rtl ? -10 : 10)}" y="${y(last[1]) + 4}" text-anchor="start">${last[1]} ${pmEsc(unit)}</text>` : '';

        const keySolid = `<svg viewBox="0 0 24 8" aria-hidden="true"><line x1="0" x2="24" y1="4" y2="4" class="pm-bd-actual"/></svg>`;
        const keyDash = `<svg viewBox="0 0 24 8" aria-hidden="true"><line x1="0" x2="24" y1="4" y2="4" class="pm-bd-ideal"/></svg>`;

        host.innerHTML = `
            <div class="pm-bd-legend">
                <span class="pm-bd-key">${keySolid}{{ __('pm.remaining') }}</span>
                <span class="pm-bd-key">${keyDash}{{ __('pm.ideal') }}</span>
                <span class="pm-muted">· <span class="pm-num">${bd.completed}/${bd.total}</span> ${pmEsc(unit)} {{ __('pm.done_word') }}</span>
            </div>
            <svg viewBox="0 0 ${W} ${H}" role="img" aria-label="{{ __('pm.burndown') }}">
                ${grid}${xLabels}
                <path class="pm-bd-area" d="${area}"/>
                <path class="pm-bd-ideal" d="${ideal}"/>
                <path class="pm-bd-actual" d="${actual}"/>
                ${endLabel}
                <line class="pm-bd-cross" id="pm-bd-cross" y1="${m.top}" y2="${m.top + plotH}" style="display:none"/>
                <rect x="0" y="0" width="${W}" height="${H}" fill="transparent" id="pm-bd-hit"/>
            </svg>
            <div class="pm-bd-tip" id="pm-bd-tip" style="display:none"></div>
            <details class="pm-bd-table">
                <summary>{{ __('pm.show_table') }}</summary>
                <table><thead><tr><th>{{ __('pm.date') }}</th><th>{{ __('pm.remaining') }}</th><th>{{ __('pm.ideal') }}</th></tr></thead>
                <tbody>${bd.days.map(d => `<tr><td class="pm-num">${d.date}</td><td class="pm-num">${d.remaining ?? '—'}</td><td class="pm-num">${d.ideal}</td></tr>`).join('')}</tbody></table>
            </details>`;

        const svg = host.querySelector('svg[role="img"]');
        const hit = host.querySelector('#pm-bd-hit');
        const cross = host.querySelector('#pm-bd-cross');
        const tip = host.querySelector('#pm-bd-tip');
        hit.addEventListener('mousemove', ev => {
            const r = svg.getBoundingClientRect();
            const px = (ev.clientX - r.left) * (W / r.width);
            const raw = rtl ? (W - m.start - px) / plotW : (px - m.start) / plotW;
            const i = Math.max(0, Math.min(n - 1, Math.round(raw * (n - 1))));
            const d = bd.days[i];
            cross.setAttribute('x1', x(i)); cross.setAttribute('x2', x(i)); cross.style.display = '';
            tip.innerHTML = `<div class="pm-num" style="font-weight:800;">${d.date}</div>
                <div>{{ __('pm.remaining') }}: <span class="pm-num">${d.remaining ?? '—'}</span></div>
                <div>{{ __('pm.ideal') }}: <span class="pm-num">${d.ideal}</span></div>`;
            const physical = x(i) * (r.width / W);
            tip.style.insetInlineStart = (rtl ? r.width - physical : physical) + 'px';
            tip.style.insetBlockStart = (y(d.remaining ?? d.ideal) * (r.height / H) + 40) + 'px';
            tip.style.transform = `translate(${rtl ? '50%' : '-50%'}, calc(-100% - 12px))`;
            tip.style.display = '';
        });
        hit.addEventListener('mouseleave', () => { cross.style.display = 'none'; tip.style.display = 'none'; });
    }

    document.addEventListener('pm:tab-shown', e => { if (e.detail === 'sprints') pmLoadSprints(); });
</script>
