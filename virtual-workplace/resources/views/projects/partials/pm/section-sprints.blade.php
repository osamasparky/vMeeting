{{-- Sprints tab: plan, start and complete sprints; burndown and carry-over. --}}
@php $pmCanEditSprints = $user->isSuperAdmin() || $membership->hasPermission('projects.edit'); @endphp
<div id="hub-section-sprints" class="hub-section-content" style="display: none;">
    <section class="pm-panel" aria-labelledby="pm-sprints-title">
        <div class="pm-panel-head">
            <div class="ula-headline-group">
                <h3 class="ula-headline-ar" id="pm-sprints-title">{{ __('pm.sprints_title') }}</h3>
                @if(app()->getLocale() === 'ar')<span class="ula-headline-en">Sprints</span>@endif
                <span class="pm-muted">{{ __('pm.sprints_help') }}</span>
            </div>
        </div>

        @if($pmCanEditSprints)
            <form class="pm-row" style="margin-block-end: var(--ula-space-5);" onsubmit="pmCreateSprint(event)">
                <input class="pm-field pm-grow" name="name" maxlength="100" required placeholder="{{ __('pm.sprint_name') }}" aria-label="{{ __('pm.sprint_name') }}">
                <label class="pm-row" style="gap: var(--ula-space-2);"><span class="pm-extras-label">{{ __('pm.from') }}</span>
                    <input class="pm-field pm-num" type="date" name="start_date" required></label>
                <label class="pm-row" style="gap: var(--ula-space-2);"><span class="pm-extras-label">{{ __('pm.to') }}</span>
                    <input class="pm-field pm-num" type="date" name="end_date" required></label>
                <x-btn type="submit" variant="primary" size="md" icon="add">{{ __('pm.new_sprint') }}</x-btn>
            </form>
        @endif

        <div id="pm-sprint-list" class="pm-list" aria-live="polite"></div>
    </section>

    <section class="pm-panel" id="pm-sprint-detail" style="display: none;" aria-labelledby="pm-sprint-name">
        <div class="pm-panel-head">
            <div class="ula-headline-group">
                <h3 class="ula-headline-ar" id="pm-sprint-name">—</h3>
                <span class="pm-muted" id="pm-sprint-meta"></span>
            </div>
            <div class="pm-row" id="pm-sprint-actions"></div>
        </div>

        <div class="pm-bd" id="pm-burndown"></div>

        <div class="pm-row" style="justify-content: space-between; margin-block: var(--ula-space-6) var(--ula-space-3);">
            <h4 class="pm-extras-label" style="margin: 0;">{{ __('pm.sprint_tasks') }}</h4>
            @if($pmCanEditSprints)
                <span class="pm-picker"><button type="button" class="pm-picker-trigger" id="pm-sprint-add-btn" aria-haspopup="listbox" aria-expanded="false" onclick="pmPickSprintTasks(this)">
                    <span class="material-symbols-rounded" aria-hidden="true">playlist_add</span>{{ __('pm.add_tasks') }}</button></span>
            @endif
        </div>
        <div id="pm-sprint-tasks" class="pm-list"></div>
    </section>

    {{-- Complete sprint: choose where unfinished tasks go --}}
    <x-modal id="pm-complete-sprint-modal" :title="__('pm.complete_sprint')" icon="task_alt" maxWidth="480px">
        <form onsubmit="pmConfirmCompleteSprint(event)" style="display: grid; gap: var(--ula-space-4);">
            <p class="pm-muted" id="pm-complete-summary" style="margin: 0;"></p>
            <label style="display: grid; gap: var(--ula-space-2);">
                <span class="pm-extras-label">{{ __('pm.move_unfinished_to') }}</span>
                <select class="pm-field" name="carry_to" id="pm-carry-to"></select>
            </label>
            <x-btn type="submit" variant="primary" size="md" icon="check">{{ __('pm.complete_sprint') }}</x-btn>
        </form>
    </x-modal>
</div>
