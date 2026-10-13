<div id="tab-leaves" class="tab-view">
    <div class="ula-headline-group" style="margin-bottom: 24px;">
        <h2 class="ula-headline-ar" style="font-size: var(--ula-size-h1); margin: 0;">{{ __('nav.leaves') }}</h2>
        @if(app()->getLocale() === 'ar')<span class="ula-headline-en" style="font-size: var(--ula-size-h4);">Leave Requests</span>@endif
    </div>
    {{-- Loaded when the tab opens (see loadHrPanels in scripts). --}}
    <div data-hr-panel="leaves" aria-live="polite"></div>
</div>
