{{-- Official holidays: for one office or every office. Holidays are not working days in attendance reports.
     Needs $organization, $offices. --}}
@php
    $holidays = \App\Domains\People\Models\OfficeHoliday::withoutGlobalScopes()
        ->where('organization_id', $organization->id)
        ->where('end_date', '>=', now()->startOfYear()->toDateString())
        ->with('office:id,name')
        ->orderBy('start_date')
        ->get();
    $holidayFieldStyle = 'width: 100%; min-height: 44px; background: var(--ula-surface-raised); border: 1px solid var(--ula-border-subtle); border-radius: 8px; padding: 10px; color: var(--ula-text-primary); font-size: 13px; font-weight: 600;';
@endphp
<section id="hr-holidays" style="margin-top: var(--ula-space-8);" aria-labelledby="hr-holidays-title">
    <div class="ula-headline-group" style="margin-bottom: var(--ula-space-4);">
        <h3 id="hr-holidays-title" class="ula-headline-ar" style="font-size: var(--ula-size-h3); margin: 0;">{{ __('Official Holidays') }}</h3>
        @if(app()->getLocale() === 'ar')<span class="ula-headline-en" style="font-size: var(--ula-size-sm);">Official Holidays</span>@endif
    </div>

    <form method="POST" action="{{ route('hr.holidays.store') }}" class="card" style="padding: var(--ula-space-5); margin-bottom: var(--ula-space-4); display: grid; grid-template-columns: repeat(auto-fit, minmax(160px, 1fr)); gap: var(--ula-space-3); align-items: end; background: var(--ula-surface-card); border: 1px solid var(--ula-border-subtle); border-radius: var(--ula-radius-xl);">
        @csrf
        <x-input name="name" id="holiday-name" :label="__('Holiday Name')" :required="true" :placeholder="__('e.g. Eid al-Fitr')" />
        <div>
            <label for="holiday-start" style="display: block; font-size: 12px; font-weight: 700; color: var(--ula-text-secondary); margin-bottom: 4px;">{{ __('From') }} *</label>
            <input type="date" name="start_date" id="holiday-start" required style="{{ $holidayFieldStyle }} font-family: var(--ula-font-mono); direction: ltr; unicode-bidi: isolate;">
        </div>
        <div>
            <label for="holiday-end" style="display: block; font-size: 12px; font-weight: 700; color: var(--ula-text-secondary); margin-bottom: 4px;">{{ __('To') }} *</label>
            <input type="date" name="end_date" id="holiday-end" required style="{{ $holidayFieldStyle }} font-family: var(--ula-font-mono); direction: ltr; unicode-bidi: isolate;">
        </div>
        <div>
            <label for="holiday-office" style="display: block; font-size: 12px; font-weight: 700; color: var(--ula-text-secondary); margin-bottom: 4px;">{{ __('Applies To') }}</label>
            <select name="floor_id" id="holiday-office" style="{{ $holidayFieldStyle }}">
                <option value="">{{ __('All offices') }}</option>
                @foreach($offices as $off)
                    <option value="{{ $off->id }}">{{ $off->name }}</option>
                @endforeach
            </select>
        </div>
        <x-btn variant="primary" size="md" type="submit" icon="event_available">{{ __('Add Holiday') }}</x-btn>
    </form>

    @if($holidays->isEmpty())
        <x-empty-state icon="event_busy" :title="__('No official holidays yet')" :subtitle="__('Holidays added here are excluded from working days in attendance reports.')" />
    @else
        <x-table :headers="[__('Holiday Name'), __('From'), __('To'), __('Days'), __('Applies To'), '']">
            @foreach($holidays as $holiday)
                <tr>
                    <td class="px-5 py-3">{{ $holiday->name }}</td>
                    <td class="px-5 py-3" style="font-family: var(--ula-font-mono); direction: ltr; unicode-bidi: isolate;">{{ $holiday->start_date->format('Y-m-d') }}</td>
                    <td class="px-5 py-3" style="font-family: var(--ula-font-mono); direction: ltr; unicode-bidi: isolate;">{{ $holiday->end_date->format('Y-m-d') }}</td>
                    <td class="px-5 py-3" style="font-family: var(--ula-font-mono); direction: ltr; unicode-bidi: isolate;">{{ $holiday->days() }}</td>
                    <td class="px-5 py-3">{{ $holiday->office?->name ?? __('All offices') }}</td>
                    <td class="px-5 py-3" style="text-align: end;">
                        <form method="POST" action="{{ route('hr.holidays.delete', $holiday) }}" onsubmit="return confirm('{{ __('Remove this holiday?') }}');" style="margin: 0; display: inline;">
                            @csrf
                            @method('DELETE')
                            <x-btn variant="danger" size="sm" :iconOnly="true" icon="delete" type="submit" title="{{ __('Remove') }}" />
                        </form>
                    </td>
                </tr>
            @endforeach
        </x-table>
    @endif
</section>
