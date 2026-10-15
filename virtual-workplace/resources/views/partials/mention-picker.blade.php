{{-- "@" autocomplete for the task comment box (#new-comment-body-input).
     Typing @ opens a list of colleagues filtered as you type; ↑/↓ move, Enter/Tab pick, Esc closes.
     Picking inserts "@Full Name ", which the server matches by full name.
     Params: $mentionMembers — OrganizationMember collection with ->user loaded. --}}
@php
    $mentionList = collect($mentionMembers ?? [])
        ->filter(fn ($m) => $m->user && $m->user_id !== auth()->id() && ($m->status ?? 'active') !== 'suspended')
        ->map(fn ($m) => ['name' => $m->user->name, 'email' => $m->user->email])
        ->sortBy('name')->values();
@endphp
<style>
    .mention-picker {
        position: absolute;
        inset-inline: 0;
        bottom: calc(100% + 6px);
        z-index: 30;
        margin: 0;
        padding: 4px;
        list-style: none;
        max-height: 260px;
        overflow-y: auto;
        background: var(--ula-surface-raised);
        border: 1px solid var(--ula-border-subtle);
        border-radius: var(--ula-radius-md);
        box-shadow: var(--ula-shadow-lg);
    }
    .mention-picker li {
        display: flex;
        align-items: center;
        gap: 10px;
        min-height: 44px;
        padding: 0 10px;
        border-radius: var(--ula-radius-sm);
        cursor: pointer;
        color: var(--ula-text-primary);
        font-size: 13px;
        font-weight: 700;
    }
    .mention-picker li[aria-selected="true"] {
        background: var(--ula-accent-default);
        color: var(--ula-text-on-accent);
    }
    .mention-picker .mention-picker__mail {
        margin-inline-start: auto;
        font-size: 11px;
        font-weight: 500;
        opacity: .75;
        font-family: var(--ula-font-mono);
        direction: ltr;
        unicode-bidi: isolate;
    }
</style>
<script nonce="{{ $cspNonce ?? '' }}">
    (function () {
        const input = document.getElementById('new-comment-body-input');
        if (!input || input.dataset.mentionPicker) return;
        input.dataset.mentionPicker = '1';

        const members = @json($mentionList);
        const form = input.form || input.parentElement;
        form.style.position = 'relative';

        const list = document.createElement('ul');
        list.id = 'mention-picker-list';
        list.className = 'mention-picker';
        list.setAttribute('role', 'listbox');
        list.hidden = true;
        form.appendChild(list);

        // The browser's own form history was showing up instead.
        input.setAttribute('autocomplete', 'off');
        input.setAttribute('role', 'combobox');
        input.setAttribute('aria-autocomplete', 'list');
        input.setAttribute('aria-controls', list.id);
        input.setAttribute('aria-expanded', 'false');

        let items = [];
        let active = 0;
        let atPos = -1;

        // The "@word" being typed at the caret, if any. An @ inside a word (an e-mail) doesn't count.
        function currentQuery() {
            const caret = input.selectionStart ?? input.value.length;
            const before = input.value.slice(0, caret);
            const at = before.lastIndexOf('@');
            if (at < 0 || (at > 0 && !/\s/.test(before[at - 1]))) return null;
            const q = before.slice(at + 1);
            if (q.length > 40 || /\s{2}|\n/.test(q)) return null;
            return { at, q: q.toLowerCase() };
        }

        function close() {
            list.hidden = true;
            items = [];
            input.setAttribute('aria-expanded', 'false');
            input.removeAttribute('aria-activedescendant');
        }

        function render() {
            list.innerHTML = '';
            items.forEach((m, i) => {
                const li = document.createElement('li');
                li.id = `mention-opt-${i}`;
                li.setAttribute('role', 'option');
                li.setAttribute('aria-selected', i === active ? 'true' : 'false');
                const name = document.createElement('span');
                name.textContent = m.name;
                const mail = document.createElement('span');
                mail.className = 'mention-picker__mail';
                mail.textContent = m.email || '';
                li.append(name, mail);
                li.addEventListener('mousedown', e => { e.preventDefault(); choose(i); });
                list.appendChild(li);
            });
            list.hidden = false;
            input.setAttribute('aria-expanded', 'true');
            input.setAttribute('aria-activedescendant', `mention-opt-${active}`);
            list.children[active]?.scrollIntoView({ block: 'nearest' });
        }

        function update() {
            const m = currentQuery();
            if (!m) return close();
            const q = m.q.trim();
            items = members.filter(x => x.name.toLowerCase().includes(q) || (x.email || '').toLowerCase().startsWith(q)).slice(0, 8);
            if (!items.length) return close();
            atPos = m.at;
            active = 0;
            render();
        }

        function choose(i) {
            const m = items[i];
            if (!m) return;
            const caret = input.selectionStart ?? input.value.length;
            const insert = '@' + m.name + ' ';
            input.value = input.value.slice(0, atPos) + insert + input.value.slice(caret);
            const pos = atPos + insert.length;
            input.setSelectionRange(pos, pos);
            close();
            input.focus();
        }

        input.addEventListener('input', update);
        input.addEventListener('click', update);
        input.addEventListener('blur', () => setTimeout(close, 120));
        input.addEventListener('keydown', e => {
            if (list.hidden) return;
            if (e.key === 'ArrowDown' || e.key === 'ArrowUp') {
                e.preventDefault();
                active = (active + (e.key === 'ArrowDown' ? 1 : items.length - 1)) % items.length;
                render();
            } else if (e.key === 'Enter' || e.key === 'Tab') {
                e.preventDefault();
                choose(active);
            } else if (e.key === 'Escape') {
                e.preventDefault();
                close();
            }
        });
    })();
</script>
