{{-- Company quizzes for the office quiz machine (owner only). The list loads when the tab opens;
     the editor builds the questions in place and saves through /games/quizzes (QuizController). --}}
<div id="tab-quizzes" class="tab-view">
    <div style="display: flex; flex-wrap: wrap; align-items: flex-end; justify-content: space-between; gap: var(--ula-space-3); margin-block-end: var(--ula-space-6);">
        <div class="ula-headline-group">
            <h2 class="ula-headline-ar" style="font-size: var(--ula-size-h1); margin: 0;">{{ __('games.quiz.tab_title') }}</h2>
            @if(app()->getLocale() === 'ar')<span class="ula-headline-en" style="font-size: var(--ula-size-h4);">Office Quizzes</span>@endif
        </div>
        <x-btn variant="primary" size="md" icon="add" onclick="quizEditorOpen()">{{ __('games.quiz.new') }}</x-btn>
    </div>
    <p style="margin: 0 0 var(--ula-space-5); color: var(--ula-text-secondary); font-size: var(--ula-size-sm);">{{ __('games.quiz.tab_intro') }}</p>

    <div id="quiz-list" aria-live="polite" style="display: grid; gap: var(--ula-space-3);"></div>

    <form id="quiz-editor" class="quiz-editor" hidden onsubmit="quizEditorSave(event)">
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: var(--ula-space-3);">
            <label class="quiz-field">
                <span>{{ __('games.quiz.title') }} *</span>
                <input id="quiz-title" required maxlength="120">
            </label>
            <label class="quiz-field">
                <span>{{ __('games.quiz.description') }}</span>
                <input id="quiz-description" maxlength="300">
            </label>
        </div>
        <ol id="quiz-questions" style="margin: 0; padding: 0; list-style: none; display: grid; gap: var(--ula-space-4);"></ol>
        <div style="display: flex; flex-wrap: wrap; gap: var(--ula-space-2);">
            <x-btn variant="outline" size="md" icon="add" type="button" onclick="quizEditorAddQuestion()">{{ __('games.quiz.add_question') }}</x-btn>
            <span style="flex: 1;"></span>
            <x-btn variant="ghost" size="md" type="button" onclick="quizEditorClose()">{{ __('Cancel') }}</x-btn>
            <x-btn variant="primary" size="md" icon="save" type="submit">{{ __('Save') }}</x-btn>
        </div>
        <p id="quiz-editor-error" role="alert" style="margin: 0; color: var(--ula-status-danger); font-weight: 700;"></p>
    </form>
</div>

<style>
    .quiz-editor {
        margin-block-start: var(--ula-space-6); padding: var(--ula-space-5); background: var(--ula-surface-card);
        border: 1px solid var(--ula-border-subtle); border-radius: var(--ula-radius-xl); display: grid; gap: var(--ula-space-4);
    }
    .quiz-editor[hidden] { display: none; }
    .quiz-field { display: grid; gap: 4px; font-size: 12px; font-weight: 700; color: var(--ula-text-secondary); }
    .quiz-field input, .quiz-field select {
        min-height: 44px; padding: 8px 10px; border-radius: 8px; border: 1px solid var(--ula-border-subtle);
        background: var(--ula-surface-raised); color: var(--ula-text-primary); font-size: 14px; font-weight: 600;
    }
    .quiz-q { padding: var(--ula-space-4); border: 1px solid var(--ula-border-subtle); border-radius: var(--ula-radius-lg); background: var(--ula-surface-raised); display: grid; gap: var(--ula-space-3); }
    .quiz-q__head { display: flex; align-items: center; gap: var(--ula-space-2); font-weight: 800; }
    .quiz-q__choices { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: var(--ula-space-2); }
    .quiz-choice { display: flex; align-items: center; gap: 8px; }
    .quiz-choice input[type="radio"] { width: 22px; height: 22px; accent-color: var(--ula-accent-default); flex: none; }
    .quiz-choice input[type="text"] { flex: 1; min-height: 44px; padding: 8px 10px; border-radius: 8px; border: 1px solid var(--ula-border-subtle); background: var(--ula-surface-card); color: var(--ula-text-primary); font-weight: 600; }
    .quiz-row { display: flex; flex-wrap: wrap; align-items: center; gap: var(--ula-space-3); padding: var(--ula-space-4); background: var(--ula-surface-card); border: 1px solid var(--ula-border-subtle); border-radius: var(--ula-radius-lg); }
    .quiz-row__title { flex: 1; font-weight: 800; }
    .quiz-row__meta { color: var(--ula-text-secondary); font-size: var(--ula-size-sm); }
</style>

@php
    $quizEditorText = [
            'question' => __('games.quiz.question'),
            'choice' => __('games.quiz.choice'),
            'correct' => __('games.quiz.correct'),
            'seconds' => __('games.quiz.seconds'),
            'remove' => __('games.quiz.remove'),
            'edit' => __('Edit'),
            'delete' => __('Delete'),
            'confirm_delete' => __('games.quiz.confirm_delete'),
            'empty' => __('games.quiz.empty'),
            'count' => __('games.quiz.count'),
            'save_failed' => __('games.quiz.save_failed'),
            'need_question' => __('games.quiz.need_question'),
        ];
@endphp
<script nonce="{{ $cspNonce ?? '' }}">
    (function () {
        const T = @json($quizEditorText);
        const csrf = document.querySelector('meta[name="csrf-token"]')?.content || '';
        let quizzes = [];
        let editingId = null;
        const $ = (id) => document.getElementById(id);
        const el = (tag, attrs = {}, text) => {
            const n = document.createElement(tag);
            for (const [k, v] of Object.entries(attrs)) n.setAttribute(k, v);
            if (text !== undefined) n.textContent = text;
            return n;
        };
        const btn = (label, variant, onClick) => {
            const b = el('button', { type: 'button', class: `ula-btn ula-btn--${variant} ula-btn--md` }, label);
            b.addEventListener('click', onClick);
            return b;
        };

        async function load() {
            const res = await fetch('/games/quizzes?manage=1', { headers: { Accept: 'application/json' }, credentials: 'same-origin' });
            quizzes = res.ok ? (await res.json()).quizzes : [];
            const list = $('quiz-list');
            list.textContent = '';
            if (!quizzes.length) {
                list.appendChild(el('p', { style: 'margin: 0; color: var(--ula-text-muted);' }, T.empty));
                return;
            }
            for (const q of quizzes) {
                const row = el('div', { class: 'quiz-row' });
                const meta = el('span', { class: 'quiz-row__meta' });
                meta.appendChild(el('span', { style: 'font-family: var(--ula-font-mono); direction: ltr; unicode-bidi: isolate;' }, String(q.count)));
                meta.append(' ' + T.count);
                row.append(el('span', { class: 'quiz-row__title' }, q.title), meta,
                    btn(T.edit, 'outline', () => window.quizEditorOpen(q.id)),
                    btn(T.delete, 'danger', () => remove(q)));
                list.appendChild(row);
            }
        }

        async function remove(q) {
            if (!confirm(T.confirm_delete.replace(':title', q.title))) return;
            await fetch(`/games/quizzes/${q.id}`, { method: 'DELETE', credentials: 'same-origin', headers: { Accept: 'application/json', 'X-CSRF-TOKEN': csrf } });
            load();
        }

        function questionNode(data) {
            const li = el('li', { class: 'quiz-q' });
            const name = 'quiz-correct-' + Math.random().toString(36).slice(2);
            const head = el('div', { class: 'quiz-q__head' });
            const num = el('span');
            head.append(num, el('span', { style: 'flex: 1;' }), btn(T.remove, 'ghost', () => { li.remove(); renumber(); }));
            const text = el('input', { class: 'quiz-q__text', required: '', maxlength: '300', 'aria-label': T.question });
            text.value = data?.text || '';
            const textLabel = el('label', { class: 'quiz-field' });
            textLabel.append(el('span', {}, T.question + ' *'), text);

            const choices = el('div', { class: 'quiz-q__choices' });
            for (let i = 0; i < 4; i++) {
                const row = el('label', { class: 'quiz-choice' });
                const radio = el('input', { type: 'radio', name, value: String(i), 'aria-label': T.correct });
                if ((data?.correct_index ?? 0) === i) radio.checked = true;
                const input = el('input', { type: 'text', maxlength: '80', placeholder: `${T.choice} ${i + 1}` + (i < 2 ? ' *' : '') });
                if (i < 2) input.required = true;
                input.value = data?.choices?.[i] || '';
                row.append(radio, input);
                choices.appendChild(row);
            }

            const secs = el('select', { class: 'quiz-q__seconds' });
            for (const s of [10, 15, 20, 30, 45, 60]) {
                const o = el('option', { value: String(s) }, String(s));
                if ((data?.seconds || 20) === s) o.selected = true;
                secs.appendChild(o);
            }
            const secsLabel = el('label', { class: 'quiz-field', style: 'max-width: 160px;' });
            secsLabel.append(el('span', {}, T.seconds), secs);

            li.append(head, textLabel, el('span', { class: 'quiz-field' }, T.correct), choices, secsLabel);
            li._num = num;
            return li;
        }

        function renumber() {
            [...$('quiz-questions').children].forEach((li, i) => { li._num.textContent = `${T.question} ${i + 1}`; });
        }

        window.quizEditorAddQuestion = (data) => {
            $('quiz-questions').appendChild(questionNode(data));
            renumber();
        };

        window.quizEditorOpen = (id = null) => {
            editingId = id;
            const q = quizzes.find((x) => x.id === id);
            $('quiz-title').value = q?.title || '';
            $('quiz-description').value = q?.description || '';
            $('quiz-questions').textContent = '';
            $('quiz-editor-error').textContent = '';
            (q?.questions?.length ? q.questions : [null]).forEach((x) => window.quizEditorAddQuestion(x));
            $('quiz-editor').hidden = false;
            $('quiz-title').focus();
        };

        window.quizEditorClose = () => { $('quiz-editor').hidden = true; editingId = null; };

        window.quizEditorSave = async (e) => {
            e.preventDefault();
            const questions = [...$('quiz-questions').children].map((li) => {
                const inputs = [...li.querySelectorAll('.quiz-choice input[type="text"]')];
                const picked = Number(li.querySelector('.quiz-choice input[type="radio"]:checked')?.value ?? 0);
                // Keep filled choices only, and keep the "correct" mark on the same choice.
                const kept = inputs.map((x, i) => ({ v: x.value.trim(), i })).filter((x) => x.v !== '');
                return {
                    text: li.querySelector('.quiz-q__text').value.trim(),
                    choices: kept.map((x) => x.v),
                    correct_index: Math.max(0, kept.findIndex((x) => x.i === picked)),
                    seconds: Number(li.querySelector('.quiz-q__seconds').value),
                };
            });
            if (!questions.length) { $('quiz-editor-error').textContent = T.need_question; return; }
            const res = await fetch(editingId ? `/games/quizzes/${editingId}` : '/games/quizzes', {
                method: editingId ? 'PUT' : 'POST',
                credentials: 'same-origin',
                headers: { Accept: 'application/json', 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf },
                body: JSON.stringify({ title: $('quiz-title').value.trim(), description: $('quiz-description').value.trim() || null, questions }),
            });
            if (!res.ok) {
                const data = await res.json().catch(() => ({}));
                $('quiz-editor-error').textContent = data.message || T.save_failed;
                return;
            }
            window.quizEditorClose();
            load();
        };

        // Load when the tab is first opened.
        const tab = $('tab-quizzes');
        let loaded = false;
        const check = () => { if (!loaded && tab.classList.contains('active')) { loaded = true; load(); } };
        new MutationObserver(check).observe(tab, { attributes: true, attributeFilter: ['class'] });
        check();
    })();
</script>
