{{-- Personal dashboard: widgets each person picks and orders (stored per membership). --}}
<section class="mw-dash" aria-labelledby="mw-dash-title">
    <div class="pm-panel-head">
        <div class="ula-headline-group">
            <h2 class="ula-headline-ar" id="mw-dash-title">{{ __('pm.mydash_title') }}</h2>
            @if(app()->getLocale() === 'ar')<span class="ula-headline-en">My dashboard</span>@endif
        </div>
        <x-btn variant="secondary" size="md" icon="tune" id="mw-dash-customize" aria-expanded="false" aria-controls="mw-dash-editor" onclick="mwToggleEditor()">{{ __('pm.mydash_customize') }}</x-btn>
    </div>

    <div id="mw-dash-editor" class="mw-dash-editor" hidden>
        <p class="pm-muted" style="margin: 0;">{{ __('pm.mydash_customize_help') }}</p>
        <div id="mw-dash-editor-list" class="pm-list"></div>
        <div class="pm-row" style="justify-content: flex-end;">
            <x-btn variant="ghost" size="md" onclick="mwToggleEditor(false)">{{ __('Cancel') }}</x-btn>
            <x-btn variant="primary" size="md" icon="save" onclick="mwSaveLayout()">{{ __('pm.save') }}</x-btn>
        </div>
    </div>

    <div id="mw-dash-grid" class="mw-dash-grid" aria-live="polite">
        <div class="pm-empty">{{ __('pm.loading') }}</div>
    </div>
</section>
