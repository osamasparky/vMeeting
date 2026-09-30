<div id="tab-meetings" class="tab-view">
    <div style="display: flex; align-items: flex-end; justify-content: space-between; gap: 16px; flex-wrap: wrap; margin-bottom: 24px;">
        <div style="display: flex; flex-direction: column; gap: 2px;">
            <h2 class="ula-headline-ar" style="font-size: var(--ula-size-h1); margin: 0;">{{ __('page.meetings') }}</h2>
            @if(app()->getLocale() === 'ar')<span class="ula-headline-en" style="font-size: var(--ula-size-h4);">Scheduled Meetings &amp; Sessions</span>@endif
        </div>
        <div style="display: flex; gap: 10px; flex-wrap: wrap;">
            <x-btn variant="primary" size="md" onclick="openScheduleMeetingModal('general')" icon="add">
                {{ __('Schedule General Meeting') }}
            </x-btn>
        </div>
    </div>

    <!-- KPI Metric Cards for Meetings -->
    <div class="ula-kpi-row">
        <x-kpi-card density="default" icon="event_upcoming" iconColor="sage" :title="__('Upcoming Meetings')" :value="$upcomingMeetings->count()" :caption="__('Ready to join')" />
        <x-kpi-card density="default" icon="folder" iconColor="gold" :title="__('Project Meetings')" :value="$allMeetings->whereNotNull('project_id')->count()" :caption="__('Team synced')" />
        <x-kpi-card density="default" icon="groups" iconColor="muted" :title="__('General Meetings')" :value="$allMeetings->whereNull('project_id')->count()" :caption="__('Ad-hoc roster')" />
        <x-kpi-card density="default" icon="check_circle" iconColor="sage" :title="__('Total Hosted')" :value="$allMeetings->count()" :caption="$allMeetings->where('status', 'ended')->count() . ' ' . __('Completed')" />
    </div>

    <!-- Meetings Table Card -->
    <div class="card" style="border-radius: var(--ula-radius-xl); overflow: hidden; padding: 0; border: 1px solid var(--ula-border-subtle); background: var(--ula-surface-card); box-shadow: var(--ula-shadow-xs);">
        <div style="padding: 16px 20px; border-bottom: 1px solid var(--ula-border-subtle); display: flex; justify-content: space-between; align-items: center; background: var(--ula-surface-card);">
            <div style="display: flex; align-items: center; gap: 10px;">
                <span class="material-symbols-rounded" style="font-size: 20px; color: var(--ula-palm-700);">event_note</span>
                <h3 style="font-size: 17px; font-weight: 700; color: var(--ula-text-primary); margin: 0;">{{ __('All Organization Meetings & Sessions') }}</h3>
                <span style="font-family: 'IBM Plex Mono', monospace; font-size: 12px; color: var(--ula-text-muted);">({{ $allMeetings->count() }})</span>
            </div>
        </div>
        <div style="overflow-x: auto;">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>{{ __('Meeting Title') }}</th>
                        <th>{{ __('Scope / Project') }}</th>
                        <th>{{ __('Date & Time') }}</th>
                        <th>{{ __('Duration') }}</th>
                        <th>{{ __('Room') }}</th>
                        <th>{{ __('Host') }}</th>
                        <th>{{ __('Attendees') }}</th>
                        <th>{{ __('Status') }}</th>
                        <th>{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($allMeetings as $m)
                        @php
                            $isLive = $m->status === 'active';
                            $isCancelled = $m->status === 'ended' && $m->scheduled_at && $m->scheduled_at->isFuture();
                            $mParts = $m->participants->take(3);
                            $moreParts = max(0, $m->participants->count() - 3);
                        @endphp
                        <tr>
                            <td>
                                <div style="font-weight: 700; color: var(--ula-text-primary); font-size: 13px;">{{ $m->title }}</div>
                                @if($m->description)
                                    <div style="font-size: 11px; color: var(--ula-text-muted);">{{ Str::limit($m->description, 40) }}</div>
                                @endif
                            </td>
                            <td>
                                @if($m->project)
                                    <span class="nav-badge-pill" style="background: rgba(60, 107, 76, 0.12); color: var(--ula-palm-700); display: inline-flex; align-items: center; gap: 4px;">
                                        <span class="material-symbols-rounded" style="font-size: 14px;">folder</span>
                                        <span>{{ $m->project->name }}</span>
                                    </span>
                                @else
                                    <span class="nav-badge-pill" style="background: rgba(211, 165, 83, 0.15); color: var(--ula-gold-600); display: inline-flex; align-items: center; gap: 4px;">
                                        <span class="material-symbols-rounded" style="font-size: 14px;">public</span>
                                        <span>{{ __('General') }}</span>
                                    </span>
                                @endif
                            </td>
                            <td>
                                <div style="font-weight: 600; color: var(--ula-text-primary); font-size: 12px; font-family: 'IBM Plex Mono', monospace;">
                                    {{ $m->scheduled_at ? $m->scheduled_at->format('M d, Y') : __('Instant') }}
                                </div>
                                <div style="font-size: 11px; color: var(--ula-text-muted); font-family: 'IBM Plex Mono', monospace;">
                                    {{ $m->scheduled_at ? $m->scheduled_at->format('h:i A') : $m->created_at->format('h:i A') }}
                                </div>
                            </td>
                            <td style="font-size: 12px; font-weight: 600; color: var(--ula-text-secondary); font-family: 'IBM Plex Mono', monospace;">
                                {{ $m->duration_minutes ?? 30 }} {{ __('min') }}
                            </td>
                            <td>
                                <span style="color: var(--ula-palm-800); font-size: 12px; font-weight: 600; display: inline-flex; align-items: center; gap: 4px;">
                                    <span class="material-symbols-rounded" style="font-size: 15px;">meeting_room</span>
                                    <span>{{ $m->room->name ?? 'Meeting Room' }}</span>
                                </span>
                            </td>
                            <td>
                                <div style="font-size: 12px; font-weight: 600; color: var(--ula-text-primary);">
                                    {{ $m->creator->name ?? 'Admin' }}
                                </div>
                            </td>
                            <td>
                                <div style="display: flex; align-items: center;">
                                    @foreach($mParts as $p)
                                        <div style="width: 24px; height: 24px; border-radius: 50%; background: var(--ula-palm-900); color: white; font-size: 9px; font-weight: 700; display: flex; align-items: center; justify-content: center; border: 2px solid var(--ula-surface-card); margin-inline-start: -6px;" title="{{ $p->user->name ?? 'Attendee' }}">
                                            {{ strtoupper(substr($p->user->name ?? 'A', 0, 1)) }}
                                        </div>
                                    @endforeach
                                    @if($moreParts > 0)
                                        <div style="width: 24px; height: 24px; border-radius: 50%; background: var(--ula-palm-800); color: white; font-size: 9px; font-weight: 700; display: flex; align-items: center; justify-content: center; border: 2px solid var(--ula-surface-card); margin-inline-start: -6px;">
                                            +{{ $moreParts }}
                                        </div>
                                    @endif
                                </div>
                            </td>
                            <td>
                                @if($isLive)
                                    <x-badge variant="live" dot="true">{{ __('LIVE') }}</x-badge>
                                @elseif($m->status === 'scheduled')
                                    <x-badge variant="scheduled" icon="schedule">{{ __('Scheduled') }}</x-badge>
                                @elseif($m->status === 'ended')
                                    <x-badge variant="default">{{ __('Completed') }}</x-badge>
                                @else
                                    <x-badge variant="default">{{ ucfirst($m->status) }}</x-badge>
                                @endif
                            </td>
                            <td>
                                <div style="display: flex; align-items: center; gap: 6px;">
                                    <x-btn variant="primary" size="sm" href="{{ route('office') }}" icon="login">
                                        {{ __('Join') }}
                                    </x-btn>
                                    @if($m->status === 'scheduled')
                                        <form method="POST" action="{{ route('meetings.cancel', $m->id) }}" onsubmit="return confirm('{{ __('Are you sure you want to cancel this meeting?') }}');" style="display: inline; margin: 0;">
                                            @csrf
                                            <x-btn variant="danger" size="sm" :iconOnly="true" icon="close" type="submit" title="{{ __('Cancel Meeting') }}" />
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" style="text-align: center; color: var(--ula-text-muted); padding: 48px 16px;">
                                <span class="material-symbols-rounded" style="font-size: 36px; color: var(--ula-sand-400); display: block; margin-bottom: 8px;">calendar_month</span>
                                <div style="font-size: 14px; font-weight: 500; color: var(--ula-text-secondary);">{{ __('No meetings scheduled yet.') }}</div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
