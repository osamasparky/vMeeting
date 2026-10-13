{{-- Leave: my balance, request form and requests; for the company owner, the approval queue.
     Rendered by HrController::panel('leaves'). --}}
@php
    $leaveField = 'width: 100%; min-height: 44px; background: var(--ula-surface-raised); border: 1px solid var(--ula-border-subtle); border-radius: 8px; padding: 10px; color: var(--ula-text-primary); font-size: 13px; font-weight: 600;';
    $leaveLabel = 'display: block; font-size: 12px; font-weight: 700; color: var(--ula-text-secondary); margin-bottom: 4px;';
    $num = 'font-family: var(--ula-font-mono); direction: ltr; unicode-bidi: isolate;';
    $statusVariant = fn ($s) => match ($s) { 'approved' => 'live', 'rejected' => 'attention', 'cancelled' => 'cancelled', default => 'scheduled' };
@endphp

<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: var(--ula-space-4); margin-bottom: var(--ula-space-6);">
    <x-kpi-card :title="__('Annual entitlement')" :value="$balance['entitled']" icon="event_note" iconColor="muted" :caption="__('Year :year', ['year' => $balance['year']])" />
    <x-kpi-card :title="__('Days taken')" :value="$balance['used']" icon="beach_access" iconColor="emerald" />
    <x-kpi-card :title="__('Awaiting approval')" :value="$balance['pending']" icon="hourglass_top" iconColor="gold" />
    <x-kpi-card :title="__('Days remaining')" :value="max(0, $balance['remaining'])" icon="savings" iconColor="sage" />
</div>

@if($isOwner)
<section style="margin-bottom: var(--ula-space-8);" aria-labelledby="leave-pending-title">
    <h3 id="leave-pending-title" style="font-size: var(--ula-size-h4); margin: 0 0 var(--ula-space-3);">{{ __('Requests awaiting your decision') }} <span style="{{ $num }}">({{ $pending->count() }})</span></h3>
    @if($pending->isEmpty())
        <x-empty-state icon="task_alt" :title="__('Nothing to decide')" :subtitle="__('New leave requests from your team appear here.')" />
    @else
        <div style="display: flex; flex-direction: column; gap: var(--ula-space-3);">
            @foreach($pending as $leave)
                <form method="POST" action="{{ route('hr.leaves.approve', $leave) }}" class="card" style="padding: var(--ula-space-4); display: flex; flex-wrap: wrap; align-items: center; gap: var(--ula-space-3); background: var(--ula-surface-card); border: 1px solid var(--ula-border-subtle); border-radius: var(--ula-radius-lg);">
                    @csrf
                    <div style="flex: 1 1 240px; min-width: 0;">
                        <div style="font-weight: 700; color: var(--ula-text-primary);">{{ $leave->user?->name }} · {{ $leave->typeLabel() }}</div>
                        <div style="font-size: var(--ula-size-xs); color: var(--ula-text-secondary);">
                            <span style="{{ $num }}">{{ $leave->start_date->format('Y-m-d') }} → {{ $leave->end_date->format('Y-m-d') }}</span>
                            · {{ trans_choice(':count working day|:count working days', $leave->days) }}
                        </div>
                        @if($leave->reason)<div style="font-size: var(--ula-size-xs); color: var(--ula-text-muted); margin-top: 2px;">{{ $leave->reason }}</div>@endif
                    </div>
                    <label class="sr-only" for="leave-note-{{ $leave->id }}">{{ __('Note to the employee (optional)') }}</label>
                    <input type="text" name="note" id="leave-note-{{ $leave->id }}" maxlength="500" placeholder="{{ __('Note to the employee (optional)') }}" style="{{ $leaveField }} flex: 1 1 200px; width: auto;">
                    <x-btn variant="primary" size="md" type="submit" icon="check">{{ __('hr.leave.approve') }}</x-btn>
                    <x-btn variant="danger" size="md" type="submit" icon="close" formaction="{{ route('hr.leaves.reject', $leave) }}">{{ __('hr.leave.reject') }}</x-btn>
                </form>
            @endforeach
        </div>
    @endif
</section>
@endif

<section style="margin-bottom: var(--ula-space-8);" aria-labelledby="leave-request-title">
    <h3 id="leave-request-title" style="font-size: var(--ula-size-h4); margin: 0 0 var(--ula-space-3);">{{ __('Request leave') }}</h3>
    <form method="POST" action="{{ route('hr.leaves.store') }}" class="card" style="padding: var(--ula-space-5); display: grid; grid-template-columns: repeat(auto-fit, minmax(170px, 1fr)); gap: var(--ula-space-3); align-items: end; background: var(--ula-surface-card); border: 1px solid var(--ula-border-subtle); border-radius: var(--ula-radius-xl);">
        @csrf
        <div>
            <label for="leave-type" style="{{ $leaveLabel }}">{{ __('Leave type') }} *</label>
            <select name="type" id="leave-type" required style="{{ $leaveField }}">
                <option value="annual">{{ __('Annual leave') }}</option>
                <option value="sick">{{ __('Sick leave') }}</option>
                <option value="unpaid">{{ __('Unpaid leave') }}</option>
                <option value="other">{{ __('Other leave') }}</option>
            </select>
        </div>
        <div>
            <label for="leave-start" style="{{ $leaveLabel }}">{{ __('From') }} *</label>
            <input type="date" name="start_date" id="leave-start" required style="{{ $leaveField }} {{ $num }}">
        </div>
        <div>
            <label for="leave-end" style="{{ $leaveLabel }}">{{ __('To') }} *</label>
            <input type="date" name="end_date" id="leave-end" required style="{{ $leaveField }} {{ $num }}">
        </div>
        <div style="grid-column: 1 / -1;">
            <label for="leave-reason" style="{{ $leaveLabel }}">{{ __('Reason') }}</label>
            <textarea name="reason" id="leave-reason" rows="2" maxlength="1000" style="{{ $leaveField }}"></textarea>
        </div>
        <div style="grid-column: 1 / -1; display: flex; justify-content: space-between; align-items: center; gap: var(--ula-space-3); flex-wrap: wrap;">
            <span style="font-size: var(--ula-size-xs); color: var(--ula-text-muted);">{{ __('Only working days count: weekends and official holidays in your office are excluded.') }}</span>
            <x-btn variant="primary" size="md" type="submit" icon="send">{{ __('Send request') }}</x-btn>
        </div>
    </form>
</section>

<section style="margin-bottom: var(--ula-space-8);" aria-labelledby="leave-mine-title">
    <h3 id="leave-mine-title" style="font-size: var(--ula-size-h4); margin: 0 0 var(--ula-space-3);">{{ __('My leave requests') }}</h3>
    @if($myRequests->isEmpty())
        <x-empty-state icon="beach_access" :title="__('No leave requests yet')" :subtitle="__('Requests you send appear here with their decision.')" />
    @else
        <x-table :headers="[__('Leave type'), __('From'), __('To'), __('Days'), __('Status'), __('Note'), '']">
            @foreach($myRequests as $leave)
                <tr>
                    <td class="px-5 py-3">{{ $leave->typeLabel() }}</td>
                    <td class="px-5 py-3" style="{{ $num }}">{{ $leave->start_date->format('Y-m-d') }}</td>
                    <td class="px-5 py-3" style="{{ $num }}">{{ $leave->end_date->format('Y-m-d') }}</td>
                    <td class="px-5 py-3" style="{{ $num }}">{{ $leave->days }}</td>
                    <td class="px-5 py-3"><x-badge :variant="$statusVariant($leave->status)" size="sm">{{ $leave->statusLabel() }}</x-badge></td>
                    <td class="px-5 py-3" style="font-size: var(--ula-size-xs); color: var(--ula-text-secondary);">{{ $leave->review_note }}</td>
                    <td class="px-5 py-3" style="text-align: end;">
                        @if($leave->isPending())
                            <form method="POST" action="{{ route('hr.leaves.cancel', $leave) }}" onsubmit="return confirm('{{ __('Cancel this leave request?') }}');" style="margin: 0; display: inline;">
                                @csrf
                                <x-btn variant="secondary" size="sm" type="submit" icon="undo">{{ __('Cancel') }}</x-btn>
                            </form>
                        @endif
                    </td>
                </tr>
            @endforeach
        </x-table>
    @endif
</section>

@if($isOwner && $decided->isNotEmpty())
<section aria-labelledby="leave-decided-title">
    <h3 id="leave-decided-title" style="font-size: var(--ula-size-h4); margin: 0 0 var(--ula-space-3);">{{ __('Recent decisions') }}</h3>
    <x-table :headers="[__('Employee'), __('Leave type'), __('From'), __('To'), __('Days'), __('Status')]">
        @foreach($decided as $leave)
            <tr>
                <td class="px-5 py-3">{{ $leave->user?->name }}</td>
                <td class="px-5 py-3">{{ $leave->typeLabel() }}</td>
                <td class="px-5 py-3" style="{{ $num }}">{{ $leave->start_date->format('Y-m-d') }}</td>
                <td class="px-5 py-3" style="{{ $num }}">{{ $leave->end_date->format('Y-m-d') }}</td>
                <td class="px-5 py-3" style="{{ $num }}">{{ $leave->days }}</td>
                <td class="px-5 py-3"><x-badge :variant="$statusVariant($leave->status)" size="sm">{{ $leave->statusLabel() }}</x-badge></td>
            </tr>
        @endforeach
    </x-table>
</section>
@endif
