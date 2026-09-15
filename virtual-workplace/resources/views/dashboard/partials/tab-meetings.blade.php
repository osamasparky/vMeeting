<div id="tab-meetings" class="tab-view">
    <div class="page-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; flex-wrap: wrap; gap: 14px;">
        <div>
            <h1 class="page-title" style="font-size: 22px; font-weight: 800; color: var(--nx-text-primary); margin-bottom: 4px; display: flex; align-items: center; gap: 8px;">
                <span class="material-symbols-rounded" style="font-size: 24px; color: var(--nx-accent);">calendar_month</span>
                <span>{{ __('Scheduled Meetings & Sessions') }}</span>
            </h1>
            <p class="page-subtitle" style="font-size: 13px; color: var(--nx-text-secondary);">{{ __('Schedule general or project meetings, manage attendee invitations, and broadcast sound alerts.') }}</p>
        </div>
        <div style="display: flex; gap: 10px; align-items: center;">
            <x-btn variant="primary" size="md" onclick="openScheduleMeetingModal('general')" icon="add">
                {{ __('Schedule General Meeting') }}
            </x-btn>
        </div>
    </div>

    <!-- KPI Metric Cards for Meetings -->
    <div class="kpi-grid" style="margin-bottom: 24px;">
        <div class="kpi-card">
            <div class="kpi-header">
                <span class="kpi-title">{{ __('Upcoming Meetings') }}</span>
                <div class="kpi-icon-box">
                    <span class="material-symbols-rounded">calendar_today</span>
                </div>
            </div>
            <div class="kpi-value">{{ $upcomingMeetings->count() }}</div>
            <div class="kpi-trend" style="color: var(--nx-status-live);">
                <span class="material-symbols-rounded" style="font-size: 14px;">play_circle</span>
                <span>{{ __('Ready to join') }}</span>
            </div>
        </div>
        <div class="kpi-card">
            <div class="kpi-header">
                <span class="kpi-title">{{ __('Project Meetings') }}</span>
                <div class="kpi-icon-box">
                    <span class="material-symbols-rounded">folder</span>
                </div>
            </div>
            <div class="kpi-value">{{ $allMeetings->whereNotNull('project_id')->count() }}</div>
            <div class="kpi-trend">
                <span class="material-symbols-rounded" style="font-size: 14px;">groups</span>
                <span>{{ __('Team synced') }}</span>
            </div>
        </div>
        <div class="kpi-card">
            <div class="kpi-header">
                <span class="kpi-title">{{ __('General Meetings') }}</span>
                <div class="kpi-icon-box">
                    <span class="material-symbols-rounded">public</span>
                </div>
            </div>
            <div class="kpi-value">{{ $allMeetings->whereNull('project_id')->count() }}</div>
            <div class="kpi-trend">
                <span class="material-symbols-rounded" style="font-size: 14px;">shield</span>
                <span>{{ __('Ad-hoc roster') }}</span>
            </div>
        </div>
        <div class="kpi-card">
            <div class="kpi-header">
                <span class="kpi-title">{{ __('Total Hosted') }}</span>
                <div class="kpi-icon-box">
                    <span class="material-symbols-rounded">bolt</span>
                </div>
            </div>
            <div class="kpi-value">{{ $allMeetings->count() }}</div>
            <div class="kpi-trend" style="color: var(--nx-status-live);">
                <span class="material-symbols-rounded" style="font-size: 14px;">check_circle</span>
                <span>{{ $allMeetings->where('status', 'ended')->count() }} {{ __('Completed') }}</span>
            </div>
        </div>
    </div>

    <!-- Meetings Table Card -->
    <div class="card" style="border-radius: var(--nx-radius-lg); overflow: hidden; padding: 0;">
        <div style="padding: 20px 24px; border-bottom: 1px solid var(--nx-border-subtle); display: flex; justify-content: space-between; align-items: center; background: var(--nx-bg-surface);">
            <h3 style="font-size: 16px; font-weight: 800; color: var(--nx-text-primary); display: flex; align-items: center; gap: 8px;">
                <span class="material-symbols-rounded" style="font-size: 18px; color: var(--nx-accent);">event_note</span>
                <span>{{ __('All Organization Meetings & Sessions') }} ({{ $allMeetings->count() }})</span>
            </h3>
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
                                <div style="font-weight: 700; color: var(--nx-text-primary); font-size: 13px;">{{ $m->title }}</div>
                                @if($m->description)
                                    <div style="font-size: 11px; color: var(--nx-text-muted);">{{ Str::limit($m->description, 40) }}</div>
                                @endif
                            </td>
                            <td>
                                @if($m->project)
                                    <span class="nav-badge-pill" style="background: rgba(60, 107, 76, 0.12); color: var(--nx-palm-500); display: inline-flex; align-items: center; gap: 4px;">
                                        <span class="material-symbols-rounded" style="font-size: 14px;">folder</span>
                                        <span>{{ $m->project->name }}</span>
                                    </span>
                                @else
                                    <span class="nav-badge-pill" style="background: rgba(211, 165, 83, 0.15); color: var(--nx-gold-600); display: inline-flex; align-items: center; gap: 4px;">
                                        <span class="material-symbols-rounded" style="font-size: 14px;">public</span>
                                        <span>{{ __('General') }}</span>
                                    </span>
                                @endif
                            </td>
                            <td>
                                <div style="font-weight: 600; color: var(--nx-text-primary); font-size: 12px; font-family: 'IBM Plex Mono', monospace;">
                                    {{ $m->scheduled_at ? $m->scheduled_at->format('M d, Y') : __('Instant') }}
                                </div>
                                <div style="font-size: 11px; color: var(--nx-text-muted); font-family: 'IBM Plex Mono', monospace;">
                                    {{ $m->scheduled_at ? $m->scheduled_at->format('h:i A') : $m->created_at->format('h:i A') }}
                                </div>
                            </td>
                            <td style="font-size: 12px; font-weight: 600; color: var(--nx-text-secondary); font-family: 'IBM Plex Mono', monospace;">
                                {{ $m->duration_minutes ?? 30 }} {{ __('min') }}
                            </td>
                            <td>
                                <span style="color: var(--nx-palm-700); font-size: 12px; font-weight: 600; display: inline-flex; align-items: center; gap: 4px;">
                                    <span class="material-symbols-rounded" style="font-size: 15px;">meeting_room</span>
                                    <span>{{ $m->room->name ?? 'Meeting Room' }}</span>
                                </span>
                            </td>
                            <td>
                                <div style="font-size: 12px; font-weight: 600; color: var(--nx-text-primary);">
                                    {{ $m->creator->name ?? 'Admin' }}
                                </div>
                            </td>
                            <td>
                                <div style="display: flex; align-items: center;">
                                    @foreach($mParts as $p)
                                        <div style="width: 24px; height: 24px; border-radius: 50%; background: var(--nx-palm-900); color: white; font-size: 9px; font-weight: 700; display: flex; align-items: center; justify-content: center; border: 2px solid var(--nx-bg-surface); margin-inline-start: -6px;" title="{{ $p->user->name ?? 'Attendee' }}">
                                            {{ strtoupper(substr($p->user->name ?? 'A', 0, 1)) }}
                                        </div>
                                    @endforeach
                                    @if($moreParts > 0)
                                        <div style="width: 24px; height: 24px; border-radius: 50%; background: var(--nx-palm-700); color: white; font-size: 9px; font-weight: 700; display: flex; align-items: center; justify-content: center; border: 2px solid var(--nx-bg-surface); margin-inline-start: -6px;">
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
                                        <form method="POST" action="{{ route('meetings.cancel', $m->id) }}" onsubmit="return confirm('{{ __('Are you sure you want to cancel this meeting?') }}');" style="display: inline;">
                                            @csrf
                                            <button type="submit" class="nx-btn nx-btn-danger nx-btn-sm" style="padding: 6px 8px;" title="{{ __('Cancel Meeting') }}">
                                                <span class="material-symbols-rounded" style="font-size: 14px;">close</span>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" style="text-align: center; color: var(--nx-text-muted); padding: 48px 16px;">
                                <span class="material-symbols-rounded" style="font-size: 36px; color: var(--nx-sand-400); display: block; margin-bottom: 8px;">calendar_month</span>
                                <div style="font-size: 14px; font-weight: 500; color: var(--nx-text-secondary);">{{ __('No meetings scheduled yet.') }}</div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
