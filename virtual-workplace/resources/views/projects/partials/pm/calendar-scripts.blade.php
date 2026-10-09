<script nonce="{{ $cspNonce ?? '' }}">
    // ── Calendar: tasks on their due date; drag a task (or an unscheduled chip) onto a day to reschedule.
    (function () {
        const now = new Date();
        PM.cal = { year: now.getFullYear(), month: now.getMonth() };
        // Weeks start on Saturday in Arabic, Sunday in English; digits stay Latin like the rest of the app.
        const weekStart = PM.locale === 'ar' ? 6 : 0;
        const intlLocale = PM.locale === 'ar' ? 'ar-u-nu-latn' : 'en';
        const iso = d => `${d.getFullYear()}-${String(d.getMonth() + 1).padStart(2, '0')}-${String(d.getDate()).padStart(2, '0')}`;
        const MAX_PER_DAY = 3;

        function taskAttrs(t) {
            return `data-task-id="${pmEsc(t.id)}" data-title="${pmEsc((t.title || '').toLowerCase() + ' #' + t.number)}"
                    data-assignee="${pmEsc(t.assignee)}" data-assignees="${pmEsc(t.assignees)}" data-labels="${pmEsc(t.labels)}"
                    data-priority="${pmEsc(t.priority)}" data-milestone="${pmEsc(t.milestone)}" data-due="${pmEsc(t.due || '')}"`;
        }

        function taskButton(t) {
            const cls = ['pm-cal-task', `pm-cal-task--${t.priority}`, t.status === 'done' ? 'pm-cal-task--done' : ''].join(' ');
            return `<button type="button" class="${cls}" ${taskAttrs(t)} draggable="${t.canEdit}"
                        title="#${t.number} ${pmEsc(t.title)}" onclick="openTaskInspector('${pmEsc(t.id)}')">
                        <span class="pm-num">#${t.number}</span> ${pmEsc(t.title)}</button>`;
        }

        function visible(t) {
            const probe = document.createElement('div');
            probe.innerHTML = `<div ${taskAttrs(t)}></div>`;
            return hubTaskMatches(probe.firstElementChild, getHubFilterState());
        }

        window.pmRenderCalendar = function () {
            const grid = document.getElementById('pm-cal-grid');
            if (!grid) return;
            const { year, month } = PM.cal;
            const first = new Date(year, month, 1);
            document.getElementById('pm-cal-title').textContent =
                new Intl.DateTimeFormat(intlLocale, { month: 'long', year: 'numeric' }).format(first);

            const tasks = PM.tasks.filter(visible);
            const byDay = {};
            tasks.filter(t => t.due).forEach(t => (byDay[t.due] = byDay[t.due] || []).push(t));
            const todayIso = iso(new Date());

            // Header row of weekday names.
            const dowFmt = new Intl.DateTimeFormat(intlLocale, { weekday: 'short' });
            let html = '';
            for (let i = 0; i < 7; i++) {
                // 2024-01-07 is a Sunday, so +w lands on weekday w.
                html += `<div class="pm-cal-dow" role="columnheader">${dowFmt.format(new Date(2024, 0, 7 + ((weekStart + i) % 7)))}</div>`;
            }

            const offset = (first.getDay() - weekStart + 7) % 7;
            const start = new Date(year, month, 1 - offset);
            for (let i = 0; i < 42; i++) {
                const d = new Date(start.getFullYear(), start.getMonth(), start.getDate() + i);
                const key = iso(d);
                const dayTasks = byDay[key] || [];
                const cls = ['pm-cal-day', d.getMonth() !== month ? 'pm-cal-day--other' : '', key === todayIso ? 'pm-cal-day--today' : ''].join(' ');
                html += `<div class="${cls}" role="gridcell" data-date="${key}" aria-label="${pmEsc(new Intl.DateTimeFormat(intlLocale, { dateStyle: 'full' }).format(d))}">
                            <span class="pm-cal-date pm-num">${d.getDate()}</span>
                            ${dayTasks.slice(0, MAX_PER_DAY).map(taskButton).join('')}
                            ${dayTasks.length > MAX_PER_DAY ? `<span class="pm-cal-more">+${dayTasks.length - MAX_PER_DAY} {{ __('pm.more') }}</span>` : ''}
                         </div>`;
                if (i === 34 && new Date(start.getFullYear(), start.getMonth(), start.getDate() + 35).getMonth() !== month) break;
            }
            grid.innerHTML = html;

            // Narrow screens: agenda list of this month's days that have tasks.
            const agenda = document.getElementById('pm-cal-agenda');
            const monthKeys = Object.keys(byDay).filter(k => k.startsWith(`${year}-${String(month + 1).padStart(2, '0')}`)).sort();
            agenda.innerHTML = monthKeys.length
                ? monthKeys.map(k => `<div><div class="pm-extras-label pm-num" style="margin-block-end: var(--ula-space-2);">${k}</div><div class="pm-list">${byDay[k].map(taskButton).join('')}</div></div>`).join('')
                : `<div class="pm-empty">{{ __('pm.no_tasks_this_month') }}</div>`;

            const unscheduled = tasks.filter(t => !t.due);
            document.getElementById('pm-cal-unscheduled').innerHTML = unscheduled.length
                ? unscheduled.map(t => taskButton(t).replace('pm-cal-task ', 'pm-cal-task pm-label ')).join('')
                : `<span class="pm-muted">{{ __('pm.all_scheduled') }}</span>`;
        };

        window.pmCalShift = function (delta) {
            const d = new Date(PM.cal.year, PM.cal.month + delta, 1);
            PM.cal = { year: d.getFullYear(), month: d.getMonth() };
            pmRenderCalendar();
        };
        window.pmCalToday = function () {
            const d = new Date();
            PM.cal = { year: d.getFullYear(), month: d.getMonth() };
            pmRenderCalendar();
        };

        async function reschedule(taskId, date) {
            const t = PM.tasks.find(x => x.id === taskId);
            if (!t || t.due === date) return;
            const previous = t.due;
            t.due = date;
            pmRenderCalendar();
            try {
                await pmApi('PATCH', `/tasks/${taskId}`, { due_date: date });
                PM.dirty = true;
                showHubToast(@json(__('pm.rescheduled')));
            } catch (e) {
                t.due = previous;
                pmRenderCalendar();
                showHubToast(pmEsc(e.message));
            }
        }

        const section = document.getElementById('hub-section-calendar');
        if (section) {
            section.addEventListener('dragstart', e => {
                const el = e.target.closest('.pm-cal-task');
                if (el) e.dataTransfer.setData('text/plain', el.dataset.taskId);
            });
            section.addEventListener('dragover', e => {
                const day = e.target.closest('.pm-cal-day');
                if (day) { e.preventDefault(); day.classList.add('pm-cal-day--drop'); }
            });
            section.addEventListener('dragleave', e => e.target.closest('.pm-cal-day')?.classList.remove('pm-cal-day--drop'));
            section.addEventListener('drop', e => {
                const day = e.target.closest('.pm-cal-day');
                if (!day) return;
                e.preventDefault();
                day.classList.remove('pm-cal-day--drop');
                reschedule(e.dataTransfer.getData('text/plain'), day.dataset.date);
            });
        }

        document.addEventListener('hub:filters-changed', () => pmRenderCalendar());
        document.addEventListener('DOMContentLoaded', () => pmRenderCalendar());
    })();
</script>
