<div id="tab-attendance" class="tab-view">
    <div class="ula-headline-group" style="margin-bottom: 24px;">
        <h2 class="ula-headline-ar" style="font-size: var(--ula-size-h1); margin: 0;">{{ __('nav.attendance') }}</h2>
        @if(app()->getLocale() === 'ar')<span class="ula-headline-en" style="font-size: var(--ula-size-h4);">Monthly Attendance Report</span>@endif
    </div>
    {{-- Loaded when the tab opens (see loadHrPanels in scripts); month/member go in data-hr-query. --}}
    <div id="hr-attendance-panel" data-hr-panel="attendance" aria-live="polite"></div>
</div>
