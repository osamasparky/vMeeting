{{-- Project settings tab: states, labels, automations, templates, archive. --}}
@php
    $pmSettingsPanels = [
        ['states', 'view_column', __('pm.states')],
        ['labels', 'sell', __('pm.labels')],
        ['automations', 'bolt', __('pm.automations')],
        ['templates', 'content_copy', __('pm.templates')],
        ['archive', 'inventory_2', __('pm.archive')],
    ];
@endphp
<div id="hub-section-settings" class="hub-section-content" style="display: none;">
    <div class="pm-subnav" role="tablist" aria-label="{{ __('pm.settings') }}">
        @foreach($pmSettingsPanels as [$panelKey, $panelIcon, $panelLabel])
            <button type="button" role="tab" id="pm-settings-tab-{{ $panelKey }}" aria-controls="pm-settings-{{ $panelKey }}"
                    aria-selected="{{ $loop->first ? 'true' : 'false' }}" onclick="pmShowSettingsPanel('{{ $panelKey }}')">
                <span class="material-symbols-rounded" aria-hidden="true" style="font-size: 18px; vertical-align: text-bottom;">{{ $panelIcon }}</span>
                {{ $panelLabel }}
            </button>
        @endforeach
    </div>

    {{-- States --}}
    <section id="pm-settings-states" class="pm-panel" role="tabpanel" aria-labelledby="pm-settings-tab-states">
        <div class="pm-panel-head">
            <div class="ula-headline-group">
                <h3 class="ula-headline-ar">{{ __('pm.states_title') }}</h3>
                @if(app()->getLocale() === 'ar')<span class="ula-headline-en">Workflow states</span>@endif
                <span class="pm-muted">{{ __('pm.states_help') }}</span>
            </div>
        </div>
        @if($user->isSuperAdmin() || $membership->hasPermission('projects.edit'))
            <form class="pm-row" style="margin-block-end: var(--ula-space-5);" onsubmit="pmCreateState(event)">
                <input class="pm-field pm-grow" name="name" maxlength="60" required placeholder="{{ __('pm.new_state') }}" aria-label="{{ __('pm.new_state') }}">
                <select class="pm-field" name="group" aria-label="{{ __('pm.state_group') }}">
                    @foreach(['backlog', 'ready', 'in_progress', 'review', 'qa', 'done'] as $g)
                        <option value="{{ $g }}">{{ __('pm.group_'.$g) }}</option>
                    @endforeach
                </select>
                <x-btn type="submit" variant="primary" size="md" icon="add">{{ __('pm.add_state') }}</x-btn>
            </form>
        @endif
        <div id="pm-state-list" class="pm-list"></div>
    </section>

    {{-- Labels --}}
    <section id="pm-settings-labels" class="pm-panel" role="tabpanel" aria-labelledby="pm-settings-tab-labels" style="display: none;">
        <div class="pm-panel-head">
            <div class="ula-headline-group">
                <h3 class="ula-headline-ar">{{ __('pm.labels_title') }}</h3>
                @if(app()->getLocale() === 'ar')<span class="ula-headline-en">Labels</span>@endif
                <span class="pm-muted">{{ __('pm.labels_help') }}</span>
            </div>
        </div>

        @if($user->isSuperAdmin() || $membership->hasPermission('projects.edit'))
            <form class="pm-row" style="margin-block-end: var(--ula-space-5);" onsubmit="pmCreateLabel(event)">
                <input class="pm-field pm-grow" name="name" maxlength="50" required placeholder="{{ __('pm.new_label') }}" aria-label="{{ __('pm.new_label') }}">
                <div class="pm-swatches" role="radiogroup" aria-label="{{ __('pm.label_color') }}" id="pm-new-label-tones"></div>
                <x-btn type="submit" variant="primary" size="md" icon="add">{{ __('pm.add_label') }}</x-btn>
            </form>
        @endif

        <div id="pm-label-list" class="pm-list"></div>
    </section>

    {{-- Automations --}}
    <section id="pm-settings-automations" class="pm-panel" role="tabpanel" aria-labelledby="pm-settings-tab-automations" style="display: none;">
        <div class="pm-panel-head">
            <div class="ula-headline-group">
                <h3 class="ula-headline-ar">{{ __('pm.automations_title') }}</h3>
                @if(app()->getLocale() === 'ar')<span class="ula-headline-en">Automations</span>@endif
                <span class="pm-muted">{{ __('pm.automations_help') }}</span>
            </div>
            @if($user->isSuperAdmin() || $membership->hasPermission('projects.edit'))
                <x-btn variant="primary" size="md" icon="add" id="pm-automation-new-btn" onclick="pmEditAutomation(null)">{{ __('pm.new_automation') }}</x-btn>
            @endif
        </div>

        @if($user->isSuperAdmin() || $membership->hasPermission('projects.edit'))
            <form id="pm-automation-form" class="pm-automation-form" hidden onsubmit="pmSaveAutomation(event)" aria-labelledby="pm-automation-form-title">
                <h4 class="pm-extras-label" id="pm-automation-form-title" style="margin: 0;">{{ __('pm.automation_rule') }}</h4>
                <input class="pm-field" name="name" maxlength="120" required placeholder="{{ __('pm.automation_name') }}" aria-label="{{ __('pm.automation_name') }}">

                <div class="pm-automation-step">
                    <span class="pm-automation-step-label">{{ __('pm.when') }}</span>
                    <select class="pm-field pm-grow" name="trigger" aria-label="{{ __('pm.when') }}">
                        @foreach(\App\Domains\Projects\Models\ProjectAutomation::TRIGGERS as $trigger)
                            <option value="{{ $trigger }}">{{ __('pm.trigger_'.$trigger) }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="pm-automation-step">
                    <span class="pm-automation-step-label">{{ __('pm.only_if') }}</span>
                    <div class="pm-row pm-grow">
                        <select class="pm-field" name="cond_state_id" aria-label="{{ __('pm.cond_state') }}"></select>
                        <select class="pm-field" name="cond_priority" aria-label="{{ __('pm.cond_priority') }}">
                            <option value="">{{ __('pm.cond_any_priority') }}</option>
                            @foreach(\App\Domains\Projects\Models\Task::PRIORITIES as $priority)
                                <option value="{{ $priority }}">{{ __(ucfirst($priority)) }}</option>
                            @endforeach
                        </select>
                        <select class="pm-field" name="cond_label_id" aria-label="{{ __('pm.cond_label') }}"></select>
                        <select class="pm-field" name="cond_assignee" aria-label="{{ __('pm.cond_assignee') }}"></select>
                    </div>
                </div>

                <div class="pm-automation-step">
                    <span class="pm-automation-step-label">{{ __('pm.then') }}</span>
                    <div class="pm-grow">
                        <div id="pm-automation-actions" class="pm-list"></div>
                        <button type="button" class="ula-btn ula-btn--ghost ula-btn--sm" id="pm-automation-add-action" onclick="pmAddAutomationAction()" style="margin-block-start: var(--ula-space-2);">
                            <span class="material-symbols-rounded" aria-hidden="true">add</span><span>{{ __('pm.add_action') }}</span></button>
                    </div>
                </div>

                <div class="pm-row" style="justify-content: flex-end;">
                    <x-btn variant="ghost" size="md" onclick="pmCloseAutomationForm()">{{ __('Cancel') }}</x-btn>
                    <x-btn type="submit" variant="primary" size="md" icon="save">{{ __('pm.save') }}</x-btn>
                </div>
            </form>
        @endif

        <div id="pm-automation-list" class="pm-list" aria-live="polite"></div>
    </section>

    {{-- Templates --}}
    <section id="pm-settings-templates" class="pm-panel" role="tabpanel" aria-labelledby="pm-settings-tab-templates" style="display: none;">
        <div class="pm-panel-head">
            <div class="ula-headline-group">
                <h3 class="ula-headline-ar">{{ __('pm.templates_title') }}</h3>
                @if(app()->getLocale() === 'ar')<span class="ula-headline-en">Templates</span>@endif
                <span class="pm-muted">{{ __('pm.templates_help') }}</span>
            </div>
        </div>

        <h4 class="pm-extras-label" style="margin: 0 0 var(--ula-space-3);">{{ __('pm.task_templates') }}</h4>
        <div id="pm-task-template-list" class="pm-list" style="margin-block-end: var(--ula-space-6);"></div>

        <h4 class="pm-extras-label" style="margin: 0 0 var(--ula-space-3);">{{ __('pm.project_templates') }}</h4>
        @if($user->isSuperAdmin() || $membership->hasPermission('projects.create'))
            <form class="pm-row" style="margin-block-end: var(--ula-space-4);" onsubmit="pmSaveProjectTemplate(event)">
                <input class="pm-field pm-grow" name="name" maxlength="120" required value="{{ $project->name }}" aria-label="{{ __('pm.template_name') }}">
                <input class="pm-field pm-grow" name="description" maxlength="1000" placeholder="{{ __('pm.template_description') }}" aria-label="{{ __('pm.template_description') }}">
                <x-btn type="submit" variant="primary" size="md" icon="content_copy">{{ __('pm.save_project_template') }}</x-btn>
            </form>
        @endif
        <div id="pm-project-template-list" class="pm-list"></div>
    </section>

    {{-- Archive --}}
    <section id="pm-settings-archive" class="pm-panel" role="tabpanel" aria-labelledby="pm-settings-tab-archive" style="display: none;">
        <div class="pm-panel-head">
            <div class="ula-headline-group">
                <h3 class="ula-headline-ar">{{ __('pm.archive_title') }}</h3>
                @if(app()->getLocale() === 'ar')<span class="ula-headline-en">Archive</span>@endif
                <span class="pm-muted">{{ __('pm.archive_help') }}</span>
            </div>
        </div>
        <div id="pm-archive-list" class="pm-list" aria-live="polite"></div>

        @if($user->isSuperAdmin() || $membership->hasPermission('projects.delete'))
            <div class="pm-list-row" style="margin-block-start: var(--ula-space-7); border-color: var(--ula-border-danger);">
                <div class="pm-grow">
                    <div style="font-weight: 800;">{{ __('pm.archive_project') }}</div>
                    <div class="pm-muted">{{ __('pm.archive_project_help') }}</div>
                </div>
                <x-btn variant="danger" size="md" icon="inventory_2" onclick="pmArchiveProject()">{{ __('pm.archive_project') }}</x-btn>
            </div>
        @endif
    </section>

    {{-- Further panels are included here by later features. --}}
</div>
