{{-- Office working hours: working days, start/end time and the grace period before an arrival counts as late.
     Params: $prefix (element id prefix), $office (Floor or null — null uses the defaults). --}}
@php
    $schedDays = $office?->workDays() ?? \App\Domains\Workspace\Models\Floor::DEFAULT_WORK_DAYS;
    $schedDayNames = [0 => __('Sunday'), 1 => __('Monday'), 2 => __('Tuesday'), 3 => __('Wednesday'), 4 => __('Thursday'), 5 => __('Friday'), 6 => __('Saturday')];
    $schedInput = 'width: 100%; min-height: 44px; background: var(--ula-surface-raised); border: 1px solid var(--ula-border-subtle); border-radius: 8px; padding: 10px; color: var(--ula-text-primary); font-family: var(--ula-font-mono); font-size: 13px; font-weight: 600; direction: ltr; unicode-bidi: isolate;';
    $schedLabel = 'display: block; font-size: 12px; font-weight: 700; color: var(--ula-text-secondary); margin-bottom: 4px;';
@endphp
<fieldset style="border: 1px solid var(--ula-border-subtle); border-radius: 8px; padding: 12px; margin: 0; display: flex; flex-direction: column; gap: 10px;">
    <legend style="padding: 0 6px; font-size: 12px; font-weight: 700; color: var(--ula-text-secondary);">{{ __('Working Hours') }}</legend>

    <div>
        <span style="{{ $schedLabel }}">{{ __('Working Days') }} *</span>
        <div style="display: flex; flex-wrap: wrap; gap: 6px;">
            @foreach($schedDayNames as $dow => $dayName)
                <label style="display: inline-flex; align-items: center; gap: 6px; min-height: 44px; padding: 0 10px; border: 1px solid var(--ula-border-subtle); border-radius: 8px; background: var(--ula-surface-raised); font-size: 12px; font-weight: 700; color: var(--ula-text-primary); cursor: pointer;">
                    <input type="checkbox" name="work_days[]" value="{{ $dow }}" id="{{ $prefix }}-day-{{ $dow }}" @checked(in_array($dow, $schedDays, true))>
                    <span>{{ $dayName }}</span>
                </label>
            @endforeach
        </div>
    </div>

    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(120px, 1fr)); gap: 10px;">
        <div>
            <label for="{{ $prefix }}-start" style="{{ $schedLabel }}">{{ __('Day Starts') }} *</label>
            <input type="time" name="work_start" id="{{ $prefix }}-start" required value="{{ $office?->work_start ?? '09:00' }}" style="{{ $schedInput }}">
        </div>
        <div>
            <label for="{{ $prefix }}-end" style="{{ $schedLabel }}">{{ __('Day Ends') }} *</label>
            <input type="time" name="work_end" id="{{ $prefix }}-end" required value="{{ $office?->work_end ?? '17:00' }}" style="{{ $schedInput }}">
        </div>
        <div>
            <label for="{{ $prefix }}-grace" style="{{ $schedLabel }}">{{ __('Late After (minutes)') }} *</label>
            <input type="number" name="late_grace_minutes" id="{{ $prefix }}-grace" required min="0" max="240" value="{{ $office?->late_grace_minutes ?? 15 }}" style="{{ $schedInput }}">
        </div>
    </div>
    <p style="margin: 0; font-size: 11px; color: var(--ula-text-muted);">{{ __('Attendance reports compare each member against their home office’s working days and hours.') }}</p>
</fieldset>
