{{--
    Inline editing for the task details modal (#task-details-modal): the title and the
    description each get an edit button that swaps the text for a field. Shared by the
    dashboard and the project hub; relies on their ORG_ID, CSRF_TOKEN, activeInspectorTaskId,
    openTaskDetails() and closeTaskDetailsModal(). Saves through PATCH /tasks/{task}, which
    applies the task "update" policy.
--}}
<style>
    .task-edit-row { display: flex; align-items: flex-start; gap: 8px; }
    .task-edit-row > :first-child { flex: 1; min-width: 0; }
    .task-edit-field {
        width: 100%; box-sizing: border-box; background: var(--ula-surface-card); color: var(--ula-text-primary);
        border: 1px solid var(--ula-border-default); border-radius: var(--ula-radius-sm); padding: 10px 14px;
        font: inherit; outline: none; min-height: 44px;
    }
    .task-edit-field:focus-visible { box-shadow: var(--ula-focus-ring); }
    textarea.task-edit-field { min-height: 120px; resize: vertical; line-height: 1.6; font-size: 13.5px; }
    .task-edit-actions { display: flex; gap: 8px; margin-top: 8px; justify-content: flex-end; }
</style>
<script>
    (function () {
        const LABELS = {
            editTitle: @json(__('Edit title')),
            editDescription: @json(__('Edit description')),
            save: @json(__('Save')),
            cancel: @json(__('Cancel')),
            saved: @json(__('Task updated.')),
            failed: @json(__('pm.save_failed')),
            empty: @json(__('No description provided.')),
        };
        // A changed title shows on the list/board behind the modal, so refresh it on close.
        let titleChanged = false;

        function notify(message) {
            if (typeof showHubToast === 'function') return showHubToast(message);
            if (typeof showToastNotification === 'function') return showToastNotification(message);
            alert(message);
        }

        function editButton(label, onClick) {
            const btn = document.createElement('button');
            btn.type = 'button';
            btn.className = 'ula-icon-btn ula-icon-btn--ghost ula-icon-btn--sm';
            btn.title = label;
            btn.setAttribute('aria-label', label);
            btn.innerHTML = '<span class="material-symbols-rounded" aria-hidden="true">edit</span>';
            btn.addEventListener('click', onClick);
            return btn;
        }

        async function saveField(field, value) {
            const res = await fetch(`/api/v1/organizations/${ORG_ID}/tasks/${activeInspectorTaskId}`, {
                method: 'PATCH',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': CSRF_TOKEN },
                credentials: 'same-origin',
                body: JSON.stringify({ [field]: value }),
            });
            if (!res.ok) {
                const err = await res.json().catch(() => ({}));
                throw new Error(err.message || LABELS.failed);
            }
        }

        // Swap `display` for an input/textarea until saved or cancelled.
        function startEdit(display, field, multiline) {
            if (!activeInspectorTaskId || display.dataset.editing) return;
            const task = (typeof currentInspectorTask !== 'undefined' && currentInspectorTask) || {};
            const original = task[field] ?? (field === 'title' ? display.textContent.trim() : '');

            const wrap = document.createElement('div');
            const input = document.createElement(multiline ? 'textarea' : 'input');
            input.className = 'task-edit-field';
            input.value = original || '';
            if (!multiline) { input.type = 'text'; input.maxLength = 255; input.required = true; }

            const actions = document.createElement('div');
            actions.className = 'task-edit-actions';
            const cancelBtn = document.createElement('button');
            cancelBtn.type = 'button';
            cancelBtn.className = 'ula-btn ula-btn--secondary ula-btn--sm';
            cancelBtn.textContent = LABELS.cancel;
            const saveBtn = document.createElement('button');
            saveBtn.type = 'button';
            saveBtn.className = 'ula-btn ula-btn--primary ula-btn--sm';
            saveBtn.textContent = LABELS.save;
            actions.append(cancelBtn, saveBtn);
            wrap.append(input, actions);

            const row = display.parentElement;
            display.dataset.editing = '1';
            row.style.display = 'none';
            row.after(wrap);
            input.focus();

            const finish = () => { wrap.remove(); row.style.display = ''; delete display.dataset.editing; };

            const commit = async () => {
                const value = input.value.trim();
                if (!multiline && !value) { input.focus(); return; }
                if (value === (original || '').trim()) return finish();
                saveBtn.disabled = true;
                try {
                    await saveField(field, multiline ? (value || null) : value);
                    if (field === 'title') titleChanged = true;
                    if (task) task[field] = value;
                    display.textContent = value || (multiline ? LABELS.empty : '');
                    finish();
                    notify(LABELS.saved);
                    openTaskDetails(activeInspectorTaskId);
                } catch (e) {
                    saveBtn.disabled = false;
                    notify(e.message || LABELS.failed);
                }
            };

            cancelBtn.addEventListener('click', finish);
            saveBtn.addEventListener('click', commit);
            input.addEventListener('keydown', (e) => {
                if (e.key === 'Escape') { e.preventDefault(); e.stopPropagation(); finish(); }
                if (e.key === 'Enter' && (!multiline || e.ctrlKey || e.metaKey)) { e.preventDefault(); commit(); }
            });
        }

        // Put `el` and an edit button side by side in a new row.
        function attach(el, label, field, multiline) {
            if (!el || el.dataset.editable) return;
            el.dataset.editable = '1';
            const row = document.createElement('div');
            row.className = 'task-edit-row';
            el.before(row);
            row.append(el, editButton(label, () => startEdit(el, field, multiline)));
        }

        document.addEventListener('DOMContentLoaded', () => {
            attach(document.getElementById('task-modal-title'), LABELS.editTitle, 'title', false);
            attach(document.getElementById('task-modal-description'), LABELS.editDescription, 'description', true);

            const close = window.closeTaskDetailsModal;
            if (typeof close === 'function') {
                window.closeTaskDetailsModal = function () {
                    document.querySelectorAll('#task-details-modal [data-editing]').forEach((el) => {
                        el.parentElement.nextElementSibling?.remove();
                        el.parentElement.style.display = '';
                        delete el.dataset.editing;
                    });
                    close.apply(this, arguments);
                    if (titleChanged) window.location.reload();
                };
            }
        });
    })();
</script>
