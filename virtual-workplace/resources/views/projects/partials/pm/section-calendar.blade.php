{{-- Calendar tab: tasks placed on their due date; drag to reschedule. --}}
<div id="hub-section-calendar" class="hub-section-content" style="display: none;">
    <section class="pm-panel" aria-labelledby="pm-cal-title">
        <div class="pm-cal-head">
            <div class="ula-headline-group">
                <h3 class="pm-cal-title" id="pm-cal-title">—</h3>
                <span class="pm-muted">{{ __('pm.calendar_help') }}</span>
            </div>
            <div class="pm-row">
                <x-btn variant="outline" size="md" :icon="app()->getLocale() === 'ar' ? 'chevron_right' : 'chevron_left'" onclick="pmCalShift(-1)" aria-label="{{ __('pm.prev_month') }}">{{ __('pm.prev') }}</x-btn>
                <x-btn variant="outline" size="md" icon="today" onclick="pmCalToday()">{{ __('pm.today') }}</x-btn>
                <x-btn variant="outline" size="md" icon="chevron_right" iconPosition="end" onclick="pmCalShift(1)" aria-label="{{ __('pm.next_month') }}">{{ __('pm.next') }}</x-btn>
            </div>
        </div>

        <div class="pm-cal-grid" id="pm-cal-grid" role="grid" aria-labelledby="pm-cal-title"></div>
        <div class="pm-cal-agenda" id="pm-cal-agenda"></div>

        <div class="pm-cal-unscheduled">
            <h4 class="pm-extras-label" style="margin-block-end: var(--ula-space-3);">{{ __('pm.unscheduled') }}</h4>
            <div class="pm-labels" id="pm-cal-unscheduled"></div>
        </div>
    </section>
</div>
