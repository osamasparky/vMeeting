{{-- Office timezone picker: working hours in this office are counted in its local time.
     Params: $selectId, $selected (timezone id or null). --}}
@php
    $tzNow = now();
    $tzOptions = collect(timezone_identifiers_list())
        ->map(function ($tz) use ($tzNow) {
            $offset = $tzNow->copy()->setTimezone($tz)->getOffset();

            return ['id' => $tz, 'offset' => $offset, 'label' => '(UTC'.$tzNow->copy()->setTimezone($tz)->format('P').') '.str_replace('_', ' ', $tz)];
        })
        ->sortBy([['offset', 'asc'], ['id', 'asc']]);
@endphp
<div>
    <label for="{{ $selectId }}" style="display: block; font-size: 12px; font-weight: 700; color: var(--ula-text-secondary); margin-bottom: 4px;">{{ __('Office Time Zone') }} *</label>
    <select name="timezone" id="{{ $selectId }}" required dir="ltr" style="width: 100%; min-height: 44px; background: var(--ula-surface-raised); border: 1px solid var(--ula-border-subtle); border-radius: 8px; padding: 10px; color: var(--ula-text-primary); font-family: var(--ula-font-mono); font-size: 13px; font-weight: 600;">
        @foreach($tzOptions as $opt)
            <option value="{{ $opt['id'] }}" @selected($opt['id'] === $selected)>{{ $opt['label'] }}</option>
        @endforeach
    </select>
    <p style="margin: 4px 0 0; font-size: 11px; color: var(--ula-text-muted);">{{ __('Attendance and working hours in this office are counted in this local time.') }}</p>
</div>
