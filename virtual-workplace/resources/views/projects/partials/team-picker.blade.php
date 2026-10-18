{{--
    Who works on a project: people grouped by department. Tick a whole department
    (one or several) or pick people inside one. Submits member_ids[].

    @include('projects.partials.team-picker', [
        'pickerId' => 'unique-id',
        'pickerMembers' => $members,          // OrganizationMember with user.profiles
        'pickerDepartments' => $departments,
        'pickerSelected' => [...user ids],
        'pickerOrganizationId' => $organization->id,
    ])
--}}
@php
    $tpSelected = collect($pickerSelected ?? [])->map(fn ($id) => (string) $id)->all();
    $tpPeople = collect($pickerMembers)
        ->filter(fn ($m) => $m->user && empty($m->user->is_super_admin) && in_array($m->status, ['active', 'invited'], true))
        ->sortBy(fn ($m) => mb_strtolower($m->user->name));
    $tpDeptOf = fn ($m) => optional($m->user->profiles->firstWhere('organization_id', $pickerOrganizationId))->department_id;
    $tpGroups = collect($pickerDepartments)
        ->map(fn ($d) => ['key' => 'd' . $d->id, 'name' => $d->name, 'people' => $tpPeople->filter(fn ($m) => $tpDeptOf($m) === $d->id)->values()])
        ->push(['key' => 'none', 'name' => __('pm.team_no_department'), 'people' => $tpPeople->filter(fn ($m) => ! $tpDeptOf($m) || ! collect($pickerDepartments)->contains('id', $tpDeptOf($m)))->values()])
        ->filter(fn ($g) => $g['people']->isNotEmpty())
        ->values();
@endphp
<div class="pm-team" id="{{ $pickerId }}" data-team-picker>
    <div class="pm-team-head">
        <span class="pm-extras-label">{{ __('pm.team_title') }}</span>
        <span class="pm-team-count"><span class="pm-num" data-team-count>{{ count($tpSelected) }}</span> {{ __('pm.team_selected') }}</span>
    </div>
    <p class="pm-muted pm-team-hint">{{ __('pm.team_hint') }}</p>
    <input type="search" class="pm-field pm-team-search" data-team-search placeholder="{{ __('pm.team_search') }}" aria-label="{{ __('pm.team_search') }}">
    <div class="pm-team-groups">
        @forelse($tpGroups as $group)
            <fieldset class="pm-team-group" data-team-group>
                <legend class="pm-team-group-head">
                    <x-checkbox :id="$pickerId . '-' . $group['key']" :label="$group['name']" data-team-dept />
                    <span class="pm-team-group-count"><span class="pm-num"><span data-team-group-count>0</span>/{{ $group['people']->count() }}</span></span>
                </legend>
                <div class="pm-team-people">
                    @foreach($group['people'] as $m)
                        <div class="pm-team-person" data-team-person data-name="{{ mb_strtolower($m->user->name . ' ' . ($m->job_title ?? '')) }}">
                            <x-checkbox :id="$pickerId . '-u-' . $m->user_id" name="member_ids[]" :value="$m->user_id" :checked="in_array((string) $m->user_id, $tpSelected, true)" :label="$m->user->name" data-team-member />
                            @if($m->job_title || $m->role)
                                <span class="pm-team-role">{{ $m->job_title ?: $m->role?->name }}</span>
                            @endif
                        </div>
                    @endforeach
                </div>
            </fieldset>
        @empty
            <p class="pm-muted">{{ __('pm.team_empty') }}</p>
        @endforelse
    </div>
</div>

@once
    <script nonce="{{ $cspNonce ?? '' }}">
        // Team picker: department boxes tick everyone in them; counts and search stay in step.
        window.UlaTeamPicker = (function () {
            function sync(root) {
                const boxes = [...root.querySelectorAll('[data-team-member]')];
                root.querySelector('[data-team-count]').textContent = new Set(boxes.filter((b) => b.checked).map((b) => b.value)).size;
                root.querySelectorAll('[data-team-group]').forEach((group) => {
                    const people = [...group.querySelectorAll('[data-team-member]')];
                    const on = people.filter((b) => b.checked).length;
                    const dept = group.querySelector('[data-team-dept]');
                    dept.checked = on > 0 && on === people.length;
                    dept.indeterminate = on > 0 && on < people.length;
                    group.querySelector('[data-team-group-count]').textContent = on;
                });
            }
            function init(root) {
                if (!root || root.dataset.teamReady) return;
                root.dataset.teamReady = '1';
                root.addEventListener('change', (e) => {
                    if (e.target.matches('[data-team-dept]')) {
                        // Only the people the search shows: "Design" filtered to two names ticks those two.
                        e.target.closest('[data-team-group]').querySelectorAll('[data-team-person]:not([hidden]) [data-team-member]')
                            .forEach((b) => { b.checked = e.target.checked; });
                    }
                    sync(root);
                });
                root.querySelector('[data-team-search]')?.addEventListener('input', (e) => {
                    const q = e.target.value.trim().toLowerCase();
                    root.querySelectorAll('[data-team-group]').forEach((group) => {
                        let shown = 0;
                        group.querySelectorAll('[data-team-person]').forEach((p) => {
                            p.hidden = !!q && !p.dataset.name.includes(q);
                            if (!p.hidden) shown++;
                        });
                        group.hidden = shown === 0;
                    });
                });
                sync(root);
            }
            function selected(root) {
                return [...new Set([...root.querySelectorAll('[data-team-member]:checked')].map((b) => b.value))];
            }
            function reset(root, ids) {
                const want = new Set((ids || []).map(String));
                root.querySelectorAll('[data-team-member]').forEach((b) => { b.checked = want.has(b.value); });
                sync(root);
            }
            document.addEventListener('DOMContentLoaded', () => document.querySelectorAll('[data-team-picker]').forEach(init));
            return { init, selected, reset };
        })();
    </script>
@endonce
