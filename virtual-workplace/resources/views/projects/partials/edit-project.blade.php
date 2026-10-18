{{-- Edit a project after it was created: every detail, and who works on it. --}}
@if($canEditProject)
<x-modal id="pm-edit-project-modal" :title="__('pm.edit_project')" icon="edit" maxWidth="680px">
    <form id="pm-edit-project-form" onsubmit="pmSaveProject(event)" class="pm-edit-form" novalidate>
        <label class="pm-edit-full">
            <span class="pm-extras-label">{{ __('Project Name') }} *</span>
            <input class="pm-field" name="name" required maxlength="255" value="{{ $project->name }}">
        </label>
        <label>
            <span class="pm-extras-label">{{ __('Project Code') }}</span>
            <input class="pm-field pm-num" name="code" maxlength="32" value="{{ $project->code }}">
        </label>
        <label>
            <span class="pm-extras-label">{{ __('Status') }}</span>
            <select class="pm-field" name="status">
                @foreach(['planning', 'active', 'on_hold', 'completed', 'cancelled'] as $st)
                    <option value="{{ $st }}" @selected($project->status === $st)>{{ __(\Illuminate\Support\Str::headline($st)) }}</option>
                @endforeach
            </select>
        </label>
        <label>
            <span class="pm-extras-label">{{ __('Priority') }}</span>
            <select class="pm-field" name="priority">
                @foreach(['low', 'medium', 'high', 'urgent'] as $pr)
                    <option value="{{ $pr }}" @selected($project->priority === $pr)>{{ __(ucfirst($pr)) }}</option>
                @endforeach
            </select>
        </label>
        <label>
            <span class="pm-extras-label">{{ __('Project Manager') }}</span>
            <select class="pm-field" name="manager_id">
                <option value="">— {{ __('Select Manager') }} —</option>
                @foreach($allMembers->filter(fn ($m) => $m->user && empty($m->user->is_super_admin)) as $m)
                    <option value="{{ $m->user_id }}" @selected($project->manager_id === $m->user_id)>{{ $m->user->name }}</option>
                @endforeach
            </select>
        </label>
        <label>
            <span class="pm-extras-label">{{ __('pm.lead_department') }}</span>
            <select class="pm-field" name="department_id">
                <option value="">— {{ __('No Department') }} —</option>
                @foreach($departments as $d)
                    <option value="{{ $d->id }}" @selected((string) $project->department_id === (string) $d->id)>{{ $d->name }}</option>
                @endforeach
            </select>
        </label>
        <label>
            <span class="pm-extras-label">{{ __('Start Date') }}</span>
            <input class="pm-field" type="date" name="start_date" value="{{ $project->start_date?->format('Y-m-d') }}">
        </label>
        <label>
            <span class="pm-extras-label">{{ __('Due Date') }}</span>
            <input class="pm-field" type="date" name="due_date" value="{{ $project->due_date?->format('Y-m-d') }}">
        </label>
        <label>
            <span class="pm-extras-label">{{ __('Planned Hours') }}</span>
            <input class="pm-field pm-num" type="number" step="0.5" min="0" name="planned_hours" value="{{ $project->planned_hours !== null ? $project->planned_hours + 0 : '' }}">
        </label>
        @if($canSeeFinancials)
        <label>
            <span class="pm-extras-label">{{ __('pm.budget_sar') }}</span>
            <input class="pm-field pm-num" type="number" step="0.01" min="0" name="budget_amount" value="{{ $project->budget_amount !== null ? $project->budget_amount + 0 : '' }}">
        </label>
        @endif
        <label class="pm-edit-full">
            <span class="pm-extras-label">{{ __('Description') }}</span>
            <textarea class="pm-field" name="description" rows="3">{{ $project->description }}</textarea>
        </label>

        <div class="pm-edit-full">
            @include('projects.partials.team-picker', [
                'pickerId' => 'pm-edit-team',
                'pickerMembers' => $allMembers,
                'pickerDepartments' => $departments,
                'pickerSelected' => $project->members->pluck('user_id')->all(),
                'pickerOrganizationId' => $organization->id,
            ])
            <p class="pm-muted pm-team-note">{{ __('pm.team_owner_note') }}</p>
        </div>

        <p class="pm-edit-error pm-edit-full" id="pm-edit-project-error" role="alert" hidden></p>
        <div class="pm-edit-actions pm-edit-full">
            <x-btn type="button" variant="ghost" size="md" onclick="document.getElementById('pm-edit-project-modal').style.display='none'">{{ __('Cancel') }}</x-btn>
            <x-btn type="submit" variant="primary" size="md" icon="check" id="pm-edit-project-save">{{ __('pm.save_changes') }}</x-btn>
        </div>
    </form>
</x-modal>

<script nonce="{{ $cspNonce ?? '' }}">
    function openEditProjectModal() {
        const modal = document.getElementById('pm-edit-project-modal');
        modal.style.display = 'flex';
        window.UlaTeamPicker?.init(document.getElementById('pm-edit-team'));
        document.getElementById('pm-edit-project-error').hidden = true;
        modal.querySelector('input[name="name"]').focus();
    }

    async function pmSaveProject(e) {
        e.preventDefault();
        const form = e.target;
        const errorEl = document.getElementById('pm-edit-project-error');
        const save = document.getElementById('pm-edit-project-save');
        errorEl.hidden = true;
        if (!form.name.value.trim()) {
            errorEl.textContent = @json(__('pm.project_name_required'));
            errorEl.hidden = false;
            form.name.focus();
            return;
        }

        const body = {};
        ['name', 'code', 'status', 'priority', 'manager_id', 'department_id', 'start_date', 'due_date', 'planned_hours', 'budget_amount', 'description']
            .forEach((key) => {
                if (!form.elements[key]) return;
                const v = form.elements[key].value.trim();
                body[key] = v === '' ? null : v;
            });
        body.name = form.name.value.trim();
        body.member_ids = window.UlaTeamPicker.selected(document.getElementById('pm-edit-team'));

        save.disabled = true;
        try {
            const res = await fetch(`/api/v1/organizations/${ORG_ID}/projects/${PROJECT_ID}`, {
                method: 'PATCH',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': CSRF_TOKEN },
                credentials: 'same-origin',
                body: JSON.stringify(body),
            });
            const data = await res.json().catch(() => ({}));
            if (!res.ok) {
                const first = data.errors ? Object.values(data.errors)[0][0] : null;
                errorEl.textContent = first || data.message || @json(__('pm.save_failed'));
                errorEl.hidden = false;
                return;
            }
            window.location.reload();
        } catch (err) {
            errorEl.textContent = @json(__('pm.save_failed'));
            errorEl.hidden = false;
        } finally {
            save.disabled = false;
        }
    }
</script>
@endif
