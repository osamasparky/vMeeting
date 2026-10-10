{{-- Intake tab: requests from outside the team (public link), triaged into tasks. --}}
<div id="hub-section-intake" class="hub-section-content" style="display: none;">
    @if($user->isSuperAdmin() || $membership->hasPermission('projects.edit'))
        <section class="pm-panel" aria-labelledby="pm-intake-link-title">
            <div class="pm-panel-head" style="margin-block-end: var(--ula-space-3);">
                <div class="ula-headline-group">
                    <h3 class="ula-headline-ar" id="pm-intake-link-title">{{ __('pm.intake_link') }}</h3>
                    @if(app()->getLocale() === 'ar')<span class="ula-headline-en">Request link</span>@endif
                    <span class="pm-muted">{{ __('pm.intake_link_help') }}</span>
                </div>
                <label class="pm-row" style="gap: var(--ula-space-3); min-block-size: var(--ula-size-touch-target); cursor: pointer;">
                    <input type="checkbox" id="pm-intake-enabled" onchange="pmIntakeSettings({ enabled: this.checked })">
                    <span style="font-weight: 700;">{{ __('pm.intake_accepting') }}</span>
                </label>
            </div>
            <div class="pm-row">
                <input class="pm-field pm-grow pm-num" id="pm-intake-url" readonly aria-label="{{ __('pm.intake_link') }}">
                <x-btn variant="outline" size="md" icon="content_copy" onclick="pmCopyIntakeLink()">{{ __('pm.copy') }}</x-btn>
                <x-btn variant="ghost" size="md" icon="autorenew" onclick="pmIntakeSettings({ regenerate: true })">{{ __('pm.new_link') }}</x-btn>
            </div>
        </section>
    @endif

    <section class="pm-panel" aria-labelledby="pm-intake-title">
        <div class="pm-panel-head">
            <div class="ula-headline-group">
                <h3 class="ula-headline-ar" id="pm-intake-title">{{ __('pm.intake_title_list') }}</h3>
                @if(app()->getLocale() === 'ar')<span class="ula-headline-en">Intake</span>@endif
            </div>
            <div class="pm-subnav" role="tablist" style="margin: 0;">
                @foreach(['open', 'accepted', 'declined', 'duplicate', 'snoozed'] as $iv)
                    <button type="button" role="tab" data-intake-view="{{ $iv }}" aria-selected="{{ $iv === 'open' ? 'true' : 'false' }}" onclick="pmLoadIntake('{{ $iv }}')">{{ __('pm.intake_view_'.$iv) }}</button>
                @endforeach
            </div>
        </div>
        <div id="pm-intake-list" class="pm-list" aria-live="polite"></div>
    </section>
</div>
