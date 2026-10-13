{{-- Monthly attendance report. Rendered by HrController::panel('attendance') with ?month=Y-m&member=<user id>|all. --}}
@php
    $num = 'font-family: var(--ula-font-mono); direction: ltr; unicode-bidi: isolate;';
    $hm = fn (int $s) => sprintf('%d:%02d', intdiv($s, 3600), intdiv($s % 3600, 60));
    $field = 'min-height: 44px; background: var(--ula-surface-raised); border: 1px solid var(--ula-border-subtle); border-radius: 8px; padding: 8px 10px; color: var(--ula-text-primary); font-size: 13px; font-weight: 600;';
    $statusVariant = fn ($s) => match ($s) { 'present' => 'live', 'absent' => 'attention', 'leave' => 'accent', 'holiday' => 'scheduled', default => 'cancelled' };
    $dayNames = [__('Sun'), __('Mon'), __('Tue'), __('Wed'), __('Thu'), __('Fri'), __('Sat')];
    $exportUrl = route('hr.attendance.export', ['month' => $month, 'member' => $member]);
@endphp

<div class="card" style="padding: var(--ula-space-4) var(--ula-space-5); margin-bottom: var(--ula-space-6); display: flex; flex-wrap: wrap; align-items: end; gap: var(--ula-space-3); background: var(--ula-surface-card); border: 1px solid var(--ula-border-subtle); border-radius: var(--ula-radius-xl);">
    <div>
        <label for="att-month" style="display: block; font-size: 12px; font-weight: 700; color: var(--ula-text-secondary); margin-bottom: 4px;">{{ __('Month') }}</label>
        <input type="month" id="att-month" value="{{ $month }}" onchange="reloadAttendanceReport()" style="{{ $field }} {{ $num }}">
    </div>
    @if($canViewOthers)
    <div>
        <label for="att-member" style="display: block; font-size: 12px; font-weight: 700; color: var(--ula-text-secondary); margin-bottom: 4px;">{{ __('Employee') }}</label>
        <select id="att-member" onchange="reloadAttendanceReport()" style="{{ $field }}">
            <option value="all" @selected($member === 'all')>{{ __('All employees (summary)') }}</option>
            @foreach($members as $m)
                <option value="{{ $m->user_id }}" @selected($member === $m->user_id)>{{ $m->user?->name }}</option>
            @endforeach
        </select>
    </div>
    @endif
    <div style="margin-inline-start: auto;">
        <x-btn variant="outline" size="md" icon="table_view" :href="$exportUrl">{{ __('Export to Excel') }}</x-btn>
    </div>
</div>

@if($team !== null)
    {{-- Team summary --}}
    <x-table :headers="[__('Employee'), __('hr.day.present'), __('hr.day.absent'), __('hr.day.late'), __('hr.day.leave'), __('Hours worked'), __('Hours expected'), __('Overtime'), __('Shortfall')]">
        @foreach($team as $row)
            @php $s = $row['summary']; @endphp
            <tr>
                <td class="px-5 py-3">
                    <button type="button" onclick="reloadAttendanceReport('{{ $row['user_id'] }}')" style="background: none; border: 0; padding: 0; min-height: 44px; color: var(--ula-text-link); font-weight: 700; cursor: pointer; text-align: start;">{{ $row['name'] }}</button>
                </td>
                <td class="px-5 py-3" style="{{ $num }}">{{ $s['present'] }} / {{ $s['working_days'] }}</td>
                <td class="px-5 py-3" style="{{ $num }}">{{ $s['absent'] }}</td>
                <td class="px-5 py-3" style="{{ $num }}">{{ $s['late'] }}@if($s['late_minutes']) <span style="color: var(--ula-text-muted);">({{ $s['late_minutes'] }}m)</span>@endif</td>
                <td class="px-5 py-3" style="{{ $num }}">{{ $s['leave'] }}</td>
                <td class="px-5 py-3" style="{{ $num }}">{{ $hm($s['worked_seconds']) }}</td>
                <td class="px-5 py-3" style="{{ $num }}">{{ $hm($s['expected_seconds']) }}</td>
                <td class="px-5 py-3" style="{{ $num }}">{{ $hm($s['overtime_seconds']) }}</td>
                <td class="px-5 py-3" style="{{ $num }}">{{ $hm($s['shortfall_seconds']) }}</td>
            </tr>
        @endforeach
    </x-table>
@else
    @php $s = $report['summary']; $off = $report['office']; @endphp
    <p style="margin: 0 0 var(--ula-space-4); font-size: var(--ula-size-xs); color: var(--ula-text-secondary); display: flex; flex-wrap: wrap; gap: 6px; align-items: center;">
        <span class="material-symbols-rounded" style="font-size: 14px;">apartment</span>
        <span>{{ $off['name'] ?? __('Main office') }}</span>
        @if($off)
            · <span style="{{ $num }}">{{ $off['start'] }}–{{ $off['end'] }}</span>
            · <span>{{ __('Late after :minutes minutes', ['minutes' => $off['grace']]) }}</span>
        @endif
        · <span style="{{ $num }}">{{ $report['timezone'] }}</span>
    </p>

    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(190px, 1fr)); gap: var(--ula-space-4); margin-bottom: var(--ula-space-6);">
        <x-kpi-card :title="__('Days present')" :value="$s['present'].' / '.$s['elapsed_days']" icon="how_to_reg" iconColor="emerald" :caption="__(':count working days this month', ['count' => $s['working_days']])" />
        <x-kpi-card :title="__('Days absent')" :value="$s['absent']" icon="person_off" iconColor="terracotta" />
        <x-kpi-card :title="__('Late arrivals')" :value="$s['late']" icon="schedule" iconColor="gold" :caption="__(':minutes min in total', ['minutes' => $s['late_minutes']])" />
        <x-kpi-card :title="__('Leave days')" :value="$s['leave']" icon="beach_access" iconColor="sage" :caption="__(':count official holidays', ['count' => $s['holidays']])" />
        <x-kpi-card :title="__('Hours worked')" :value="$hm($s['worked_seconds'])" icon="timer" iconColor="emerald" :caption="__('of :hours expected', ['hours' => $hm($s['expected_seconds'])])" />
        <x-kpi-card :title="__('Overtime')" :value="$hm($s['overtime_seconds'])" icon="more_time" iconColor="gold" :caption="__('Shortfall :hours', ['hours' => $hm($s['shortfall_seconds'])])" />
    </div>

    <x-table :headers="[__('Date'), __('Status'), __('First arrival'), __('hr.day.late'), __('Hours worked'), __('Expected'), __('Overtime'), __('Note')]">
        @foreach($report['days'] as $d)
            <tr @if(in_array($d['status'], ['weekend', 'upcoming'], true)) style="color: var(--ula-text-muted);" @endif>
                <td class="px-5 py-3"><span style="{{ $num }}">{{ $d['date'] }}</span> <span style="font-size: var(--ula-size-xs);">{{ $dayNames[$d['weekday']] }}</span></td>
                <td class="px-5 py-3"><x-badge :variant="$statusVariant($d['status'])" size="sm">{{ \App\Http\Controllers\Web\HrController::dayStatusLabel($d['status']) }}</x-badge></td>
                <td class="px-5 py-3" style="{{ $num }}">{{ $d['first_in'] ?? '—' }}</td>
                <td class="px-5 py-3" style="{{ $num }}">@if($d['late_minutes']){{ $d['late_minutes'] }}m @else — @endif</td>
                <td class="px-5 py-3" style="{{ $num }}">{{ $d['worked_seconds'] ? $hm($d['worked_seconds']) : '—' }}</td>
                <td class="px-5 py-3" style="{{ $num }}">{{ $d['expected_seconds'] ? $hm($d['expected_seconds']) : '—' }}</td>
                <td class="px-5 py-3" style="{{ $num }}">{{ $d['overtime_seconds'] ? $hm($d['overtime_seconds']) : '—' }}</td>
                <td class="px-5 py-3" style="font-size: var(--ula-size-xs);">{{ $d['note'] }}</td>
            </tr>
        @endforeach
    </x-table>
@endif
