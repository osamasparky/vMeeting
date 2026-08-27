<div id="tab-meetings" class="tab-view">
            <div class="page-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; flex-wrap: wrap; gap: 14px;">
                <div>
                    <h1 class="page-title" style="font-size: 22px; font-weight: 900; color: var(--text-primary); margin-bottom: 4px;">📅 {{ __('Scheduled Meetings & Sessions') }}</h1>
                    <p class="page-subtitle" style="font-size: 13px; color: var(--text-secondary);">{{ __('Schedule general or project meetings, manage attendee invitations, and broadcast sound alerts.') }}</p>
                </div>
                <div style="display: flex; gap: 10px; align-items: center;">
                    <button onclick="openScheduleMeetingModal('general')" class="tactile-btn btn-primary" style="padding: 10px 18px; font-size: 13px;">
                        <span>+</span> {{ __('Schedule General Meeting') }}
                    </button>
                </div>
            </div>

            <!-- KPI Metric Cards for Meetings -->
            <div class="kpi-grid" style="margin-bottom: 24px;">
                <div class="kpi-card">
                    <div class="kpi-header">
                        <span class="kpi-title">{{ __('Upcoming Meetings') }}</span>
                        <div class="kpi-icon-box">📅</div>
                    </div>
                    <div class="kpi-value">{{ $upcomingMeetings->count() }}</div>
                    <div class="kpi-trend" style="color: var(--brand-forest);">
                        <span>🟢</span> {{ __('Ready to join') }}
                    </div>
                </div>
                <div class="kpi-card">
                    <div class="kpi-header">
                        <span class="kpi-title">{{ __('Project Meetings') }}</span>
                        <div class="kpi-icon-box">📁</div>
                    </div>
                    <div class="kpi-value">{{ $allMeetings->whereNotNull('project_id')->count() }}</div>
                    <div class="kpi-trend">
                        <span>👥</span> {{ __('Team synced') }}
                    </div>
                </div>
                <div class="kpi-card">
                    <div class="kpi-header">
                        <span class="kpi-title">{{ __('General Meetings') }}</span>
                        <div class="kpi-icon-box">🌐</div>
                    </div>
                    <div class="kpi-value">{{ $allMeetings->whereNull('project_id')->count() }}</div>
                    <div class="kpi-trend">
                        <span>🛡️</span> {{ __('Ad-hoc roster') }}
                    </div>
                </div>
                <div class="kpi-card">
                    <div class="kpi-header">
                        <span class="kpi-title">{{ __('Total Hosted') }}</span>
                        <div class="kpi-icon-box">⚡</div>
                    </div>
                    <div class="kpi-value">{{ $allMeetings->count() }}</div>
                    <div class="kpi-trend" style="color: var(--brand-forest);">
                        <span>🎉</span> {{ $allMeetings->where('status', 'ended')->count() }} {{ __('Completed') }}
                    </div>
                </div>
            </div>

            <!-- Meetings Table Card -->
            <div class="card" style="border-radius: var(--radius-lg); overflow: hidden; padding: 0;">
                <div style="padding: 20px 24px; border-bottom: 1px solid var(--border-color); display: flex; justify-content: space-between; align-items: center; background: var(--bg-surface);">
                    <h3 style="font-size: 16px; font-weight: 900; color: var(--text-primary);">📅 {{ __('All Organization Meetings & Sessions') }} ({{ $allMeetings->count() }})</h3>
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
                                        <div style="font-weight: 800; color: var(--text-primary); font-size: 13px;">{{ $m->title }}</div>
                                        @if($m->description)
                                            <div style="font-size: 11px; color: var(--text-muted);">{{ Str::limit($m->description, 40) }}</div>
                                        @endif
                                    </td>
                                    <td>
                                        @if($m->project)
                                            <span class="nav-badge-pill" style="background: rgba(79, 155, 95, 0.15); color: #4F9B5F;">
                                                📁 {{ $m->project->name }}
                                            </span>
                                        @else
                                            <span class="nav-badge-pill" style="background: rgba(214, 162, 58, 0.15); color: #D6A23A;">
                                                🌐 {{ __('General') }}
                                            </span>
                                        @endif
                                    </td>
                                    <td>
                                        <div style="font-weight: 700; color: var(--text-primary); font-size: 12px;">
                                            {{ $m->scheduled_at ? $m->scheduled_at->format('M d, Y') : __('Instant') }}
                                        </div>
                                        <div style="font-size: 11px; color: var(--text-muted);">
                                            {{ $m->scheduled_at ? $m->scheduled_at->format('h:i A') : $m->created_at->format('h:i A') }}
                                        </div>
                                    </td>
                                    <td style="font-size: 12px; font-weight: 700; color: var(--text-secondary);">
                                        {{ $m->duration_minutes ?? 30 }} {{ __('min') }}
                                    </td>
                                    <td>
                                        <strong style="color: var(--brand-forest); font-size: 12px;">🚪 {{ $m->room->name ?? 'Meeting Room' }}</strong>
                                    </td>
                                    <td>
                                        <div style="font-size: 12px; font-weight: 700; color: var(--text-primary);">
                                            {{ $m->creator->name ?? 'Admin' }}
                                        </div>
                                    </td>
                                    <td>
                                        <div style="display: flex; align-items: center;">
                                            @foreach($mParts as $p)
                                                <div style="width: 22px; height: 22px; border-radius: 50%; background: var(--accent-gradient); color: white; font-size: 9px; font-weight: 800; display: flex; align-items: center; justify-content: center; border: 2px solid var(--bg-surface); margin-inline-start: -6px;" title="{{ $p->user->name ?? 'Attendee' }}">
                                                    {{ strtoupper(substr($p->user->name ?? 'A', 0, 1)) }}
                                                </div>
                                            @endforeach
                                            @if($moreParts > 0)
                                                <div style="width: 22px; height: 22px; border-radius: 50%; background: var(--brand-forest); color: white; font-size: 9px; font-weight: 800; display: flex; align-items: center; justify-content: center; border: 2px solid var(--bg-surface); margin-inline-start: -6px;">
                                                    +{{ $moreParts }}
                                                </div>
                                            @endif
                                        </div>
                                    </td>
                                    <td>
                                        @if($isLive)
                                            <span class="nav-badge-pill" style="background: rgba(217, 107, 95, 0.2); color: #D96B5F; font-weight: 800;">🔴 LIVE</span>
                                        @elseif($m->status === 'scheduled')
                                            <span class="nav-badge-pill" style="background: rgba(79, 155, 95, 0.15); color: #4F9B5F; font-weight: 800;">📅 {{ __('Scheduled') }}</span>
                                        @elseif($m->status === 'ended')
                                            <span class="nav-badge-pill" style="background: var(--bg-surface-subtle); color: var(--text-muted);">{{ __('Completed') }}</span>
                                        @else
                                            <span class="nav-badge-pill">{{ ucfirst($m->status) }}</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div style="display: flex; align-items: center; gap: 6px;">
                                            <a href="{{ route('office') }}" class="tactile-btn btn-primary" style="padding: 6px 12px; font-size: 11px; text-decoration: none;">
                                                🚀 {{ __('Join') }}
                                            </a>
                                            @if($m->status === 'scheduled')
                                                <form method="POST" action="{{ route('meetings.cancel', $m->id) }}" onsubmit="return confirm('{{ __('Are you sure you want to cancel this meeting?') }}');" style="display: inline;">
                                                    @csrf
                                                    <button type="submit" class="tactile-btn" style="background: rgba(217, 107, 95, 0.15); color: #D96B5F; border: 1px solid rgba(217, 107, 95, 0.3); padding: 6px 10px; font-size: 11px;" title="{{ __('Cancel Meeting') }}">
                                                        ✕
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" style="text-align: center; color: var(--text-muted); padding: 40px;">
                                        <div style="font-size: 32px; margin-bottom: 8px;">📅</div>
                                        {{ __('No meetings scheduled yet.') }}
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
