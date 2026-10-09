{{-- Project settings tab: labels (+ states, automations, templates, archive in later panels). --}}
@php
    $pmSettingsPanels = [
        ['labels', 'sell', __('pm.labels')],
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

    {{-- Labels --}}
    <section id="pm-settings-labels" class="pm-panel" role="tabpanel" aria-labelledby="pm-settings-tab-labels">
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

    {{-- Further panels are included here by later features. --}}
</div>
