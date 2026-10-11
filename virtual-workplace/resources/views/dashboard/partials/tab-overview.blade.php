<div id="tab-overview" class="tab-view active">

    <!-- ── 1. Welcome Hero Banner (Matches 06-Dashboard-Overview) ── -->
    <div class="ula-hero" style="display: grid; grid-template-columns: minmax(0, 1fr) auto; border-radius: 20px; border: var(--ula-border-width-hairline) solid var(--ula-border-subtle); background: var(--ula-surface-card); overflow: hidden; box-shadow: var(--ula-shadow-xs); margin-bottom: 24px;">
        <div style="padding: 28px; display: flex; flex-direction: column; gap: 18px;">
            @php
                $hour = (int) now()->format('H');
                $isEvening = $hour >= 12;
            @endphp
            <div style="display: flex; flex-direction: column; gap: 2px;">
                <h1 style="font-size: 30px; font-weight: 600; line-height: 1.3; color: var(--ula-text-primary);" id="nx-hero-dynamic-greeting">
                    @if($isEvening)
                        {{ __('hero.good_evening', ['name' => explode(' ', $user->name)[0]]) }}
                    @else
                        {{ __('hero.good_morning', ['name' => explode(' ', $user->name)[0]]) }}
                    @endif
                </h1>
                <span style="font-family: var(--ula-font-en); font-size: 18px; font-weight: 300; color: var(--ula-text-secondary);">Ready to Collaborate</span>
            </div>

            <div style="display: flex; flex-direction: column; gap: 4px; padding-inline-start: 14px; border-inline-start: 2px solid var(--ula-highlight-default);">
                <span style="font-size: 21px; font-weight: 600; line-height: 1.4; color: var(--ula-text-primary);">المساحات الأفضل تصنع فرقاً أعظم.</span>
                <span style="font-family: var(--ula-font-en); font-size: 14px; color: var(--ula-text-secondary);">Better spaces carve greater teams.</span>
            </div>

            <div style="display: flex; gap: 10px; flex-wrap: wrap;">
                <x-btn variant="primary" size="md" icon="login" :href="route('office')">{{ __('hero.enter_workspace') }}</x-btn>
                <x-btn variant="secondary" size="md" icon="event" onclick="openScheduleMeetingModal('general')">{{ __('hero.schedule_meeting') }}</x-btn>
                @if($membership->hasPermission('maps.manage'))
                    <x-btn variant="ghost" size="md" icon="architecture" :href="route('editor')">{{ __('nav.editor') }}</x-btn>
                @endif
            </div>
        </div>

        {{-- Profile + today tiles (design 06, replaces the welcome photo). Clock/date are kept live by initOverviewLiveClock(). --}}
        @php
            $heroNow = now()->locale(app()->getLocale());
        @endphp
        <div class="ula-hero-tiles">
            <button type="button" class="ula-hero-tile ula-hero-tile--profile" onclick="switchAdminTab('profile')" aria-label="{{ __('nav.profile') }}" title="{{ __('nav.profile') }}">
                @if($user->avatar_url)
                    <img src="{{ $user->avatar_url }}" alt="{{ $user->name }}">
                @else
                    <span class="ula-hero-initial" aria-hidden="true">{{ mb_substr($user->name, 0, 1) }}</span>
                @endif
            </button>
            <div class="ula-hero-tile ula-hero-tile--time" role="timer" aria-live="off">
                <span id="nx-hero-live-clock" class="ula-hero-clock">{{ $heroNow->format('H:i') }}</span>
                <span id="nx-hero-live-date" class="ula-hero-date">{{ $heroNow->translatedFormat('l') }}<br><span class="ula-hero-date-num">{{ $heroNow->format('j') }}</span> {{ $heroNow->translatedFormat('F') }}</span>
            </div>
        </div>
    </div>

    <!-- ── 2. KPI cards: real figures, each card opens the page the figure comes from ── -->
    @php
        $todayMeetings = $upcomingMeetings->filter(fn($m) => $m->scheduled_at && $m->scheduled_at->isToday());
        $mk = $memberKpis;
        $ok = $ownerKpis;
        $dashUrl = route('dashboard');
        $num = fn ($v) => is_float($v) ? rtrim(rtrim(number_format($v, 1, '.', ''), '0'), '.') : $v;
    @endphp

    @if($isOwnerView && $ok)
        <section class="ula-dash-kpis" aria-labelledby="dash-kpi-title">
            <div class="ula-dash-kpis-head">
                <div class="ula-headline-group">
                    <h2 class="ula-headline-ar" id="dash-kpi-title">{{ __('dash.kpi_title') }}</h2>
                    @if(app()->getLocale() === 'ar')<span class="ula-headline-en">Company at a glance</span>@endif
                </div>
                <span class="ula-dash-kpis-note">{{ __('dash.kpi_note') }}</span>
            </div>
            <div class="ula-kpi-row ula-kpi-row--links">
                <a class="ula-kpi-link" href="#dash-online" aria-label="{{ __('dash.online_now') }}">
                    <x-kpi-card id="kpi-online" icon="sensors" iconColor="sage" :title="__('dash.online_now')" :value="$ok['online']">
                        <x-kpi-chip tone="stone" :n="$ok['members']">{{ __('dash.of_members') }}</x-kpi-chip>
                    </x-kpi-card>
                </a>
                <a class="ula-kpi-link" href="{{ $dashUrl }}#timesheets" aria-label="{{ __('dash.present_today') }}">
                    <x-kpi-card icon="how_to_reg" iconColor="sage" :title="__('dash.present_today')" :value="$ok['present_today']">
                        <x-kpi-chip tone="palm" :n="$num($ok['office_hours_today'])">{{ __('dash.office_hours') }}</x-kpi-chip>
                    </x-kpi-card>
                </a>
                <a class="ula-kpi-link" href="{{ $dashUrl }}#timesheets" aria-label="{{ __('dash.hours_week') }}">
                    <x-kpi-card icon="schedule" iconColor="gold" :title="__('dash.hours_week')" :value="$num($ok['hours_week'])">
                        <x-kpi-chip tone="gold" :n="$num($ok['billable_hours_week'])">{{ __('dash.billable') }}</x-kpi-chip>
                    </x-kpi-card>
                </a>
                <a class="ula-kpi-link" href="{{ $dashUrl }}#timesheets" aria-label="{{ __('dash.timesheets_pending') }}">
                    <x-kpi-card icon="fact_check" :iconColor="$ok['timesheets_pending'] ? 'terracotta' : 'muted'" :title="__('dash.timesheets_pending')" :value="$ok['timesheets_pending']">
                        <x-kpi-chip :tone="$ok['timesheets_pending'] ? 'terracotta' : 'stone'">{{ $ok['timesheets_pending'] ? __('dash.needs_you') : __('dash.all_clear') }}</x-kpi-chip>
                    </x-kpi-card>
                </a>
                <a class="ula-kpi-link" href="{{ $dashUrl }}#all-tasks" aria-label="{{ __('dash.overdue_tasks') }}">
                    <x-kpi-card icon="assignment_late" :iconColor="$ok['overdue_tasks'] ? 'terracotta' : 'muted'" :title="__('dash.overdue_tasks')" :value="$ok['overdue_tasks']">
                        <x-kpi-chip tone="stone" :n="$ok['open_tasks']">{{ __('dash.open_tasks') }}</x-kpi-chip>
                    </x-kpi-card>
                </a>
                <a class="ula-kpi-link" href="{{ $dashUrl }}#all-tasks" aria-label="{{ __('dash.done_week') }}">
                    <x-kpi-card icon="task_alt" iconColor="sage" :title="__('dash.done_week')" :value="$ok['done_week']">
                        @if($ok['completion_rate'] !== null)
                            <x-kpi-chip tone="palm" :n="$ok['completion_rate'] . '%'">{{ __('dash.completion') }}</x-kpi-chip>
                        @endif
                    </x-kpi-card>
                </a>
                <a class="ula-kpi-link" href="{{ $dashUrl }}#projects" aria-label="{{ __('dash.active_projects') }}">
                    <x-kpi-card icon="folder_open" iconColor="gold" :title="__('dash.active_projects')" :value="$ok['active_projects']">
                        <x-kpi-chip :tone="$ok['late_projects'] ? 'terracotta' : 'stone'" :n="$ok['late_projects']">{{ __('dash.late_projects') }}</x-kpi-chip>
                    </x-kpi-card>
                </a>
                <a class="ula-kpi-link" href="{{ $dashUrl }}#meetings" aria-label="{{ __('dash.meetings_today') }}">
                    <x-kpi-card icon="event" iconColor="gold" :title="__('dash.meetings_today')" :value="$ok['meetings_today']">
                        <x-kpi-chip :tone="$ok['meetings_live'] ? 'palm' : 'stone'" :n="$ok['meetings_live']">{{ __('dash.live_now') }}</x-kpi-chip>
                    </x-kpi-card>
                </a>
                <a class="ula-kpi-link" href="{{ $dashUrl }}#rooms" aria-label="{{ __('dash.rooms_in_use') }}">
                    <x-kpi-card icon="meeting_room" iconColor="sage" :title="__('dash.rooms_in_use')" :value="$ok['rooms_in_use']">
                        <x-kpi-chip tone="stone" :n="$ok['rooms']">{{ __('dash.rooms_total') }}</x-kpi-chip>
                    </x-kpi-card>
                </a>
                <a class="ula-kpi-link" href="{{ $dashUrl }}#billing" aria-label="{{ __('dash.seats') }}">
                    <x-kpi-card icon="badge" iconColor="muted" :title="__('dash.seats')" :value="$ok['seats_used'] . ($ok['seat_limit'] ? ' / ' . $ok['seat_limit'] : '')">
                        <x-kpi-chip :tone="$ok['invited'] ? 'gold' : 'stone'" :n="$ok['invited']">{{ __('dash.invited') }}</x-kpi-chip>
                    </x-kpi-card>
                </a>
                <a class="ula-kpi-link" href="{{ $dashUrl }}#guests" aria-label="{{ __('dash.guest_links') }}">
                    <x-kpi-card icon="link" iconColor="muted" :title="__('dash.guest_links')" :value="$ok['guest_links']">
                        <x-kpi-chip tone="stone">{{ __('dash.not_used_yet') }}</x-kpi-chip>
                    </x-kpi-card>
                </a>
            </div>
        </section>
    @else
        <div class="ula-kpi-row ula-kpi-row--links">
            <a class="ula-kpi-link" href="#dash-online" aria-label="{{ __('dash.online_now') }}">
                <x-kpi-card id="kpi-online" icon="sensors" iconColor="sage" :title="__('dash.online_now')" :value="$mk['online']">
                    <x-kpi-chip tone="stone" :n="$mk['members']">{{ __('dash.of_members') }}</x-kpi-chip>
                </x-kpi-card>
            </a>
            <a class="ula-kpi-link" href="{{ $dashUrl }}#meetings" aria-label="{{ __('dash.meetings_today') }}">
                <x-kpi-card icon="event" iconColor="gold" :title="__('dash.meetings_today')" :value="$mk['meetings_today']" :caption="__('dash.scheduled_today')" />
            </a>
            <a class="ula-kpi-link" href="{{ $dashUrl }}#my-tasks" aria-label="{{ __('dash.my_open_tasks') }}">
                <x-kpi-card icon="checklist" iconColor="sage" :title="__('dash.my_open_tasks')" :value="$mk['my_open_tasks']">
                    <x-kpi-chip :tone="$mk['my_overdue_tasks'] ? 'terracotta' : 'stone'" :n="$mk['my_overdue_tasks']">{{ __('dash.overdue') }}</x-kpi-chip>
                </x-kpi-card>
            </a>
            <a class="ula-kpi-link" href="{{ $dashUrl }}#timesheets" aria-label="{{ __('dash.my_hours_week') }}">
                <x-kpi-card icon="schedule" iconColor="gold" :title="__('dash.my_hours_week')" :value="$num($mk['my_hours_week'])" :caption="__('dash.this_week')" />
            </a>
        </div>
    @endif

    <!-- ── 3. Who is in the office now + today's meetings ── -->
    <div class="ula-dash-pair">
        <section class="ula-dash-panel" id="dash-online" aria-labelledby="dash-online-title">
            <div class="ula-dash-panel-head">
                <span class="ms ula-dash-panel-icon" aria-hidden="true">sensors</span>
                <h3 id="dash-online-title">{{ __('dash.online_now') }}</h3>
                <span class="ula-dash-count" id="dash-online-count">{{ $onlineNow->count() }}</span>
                <span class="ula-dash-spacer"></span>
                <x-btn variant="secondary" size="md" icon="login" :href="route('office')">{{ __('dash.enter_office') }}</x-btn>
            </div>
            <ul class="ula-online-list" id="dash-online-list" aria-live="polite"
                data-endpoint="{{ route('dashboard.pulse') }}"
                data-open-area="{{ __('dash.open_area') }}" data-since="{{ __('dash.since') }}" data-empty="{{ __('dash.nobody_online') }}">
                @forelse($onlineNow as $person)
                    <li class="ula-online-row">
                        <span class="ula-online-avatar" aria-hidden="true">
                            @if($person['avatar_url'])<img src="{{ $person['avatar_url'] }}" alt="">@else{{ mb_substr($person['name'], 0, 1) }}@endif
                            <span class="ula-online-dot"></span>
                        </span>
                        <span class="ula-online-main">
                            <span class="ula-online-name">{{ $person['name'] }}</span>
                            <span class="ula-online-where"><span class="ms" aria-hidden="true">{{ $person['room'] ? 'meeting_room' : 'deck' }}</span>{{ $person['room'] ?? __('dash.open_area') }}</span>
                        </span>
                        <span class="ula-online-since">{{ __('dash.since') }} <span class="pm-num">{{ $person['since'] ? \Illuminate\Support\Carbon::parse($person['since'])->format('H:i') : '' }}</span></span>
                    </li>
                @empty
                    <li class="ula-online-empty">{{ __('dash.nobody_online') }}</li>
                @endforelse
            </ul>
        </section>

        <section class="ula-dash-panel" aria-labelledby="dash-meet-title">
            <div class="ula-dash-panel-head">
                <span class="ms ula-dash-panel-icon" aria-hidden="true">calendar_month</span>
                <h3 id="dash-meet-title">{{ __('dash.meetings_today') }}</h3>
                <span class="ula-dash-spacer"></span>
                <a class="ula-dash-link" href="{{ $dashUrl }}#meetings">{{ __('dash.view_all') }}</a>
            </div>
            <div class="ula-meet-list">
                @forelse($todayMeetings->take(5) as $meeting)
                    <div class="ula-meet-row">
                        <span class="ula-meet-icon {{ $meeting->status === 'live' ? 'is-live' : '' }}" aria-hidden="true"><span class="ms">{{ $meeting->status === 'live' ? 'videocam' : 'schedule' }}</span></span>
                        <span class="ula-online-main">
                            <span class="ula-online-name">{{ $meeting->title }}</span>
                            <span class="ula-online-where">{{ $meeting->room->name ?? ($meeting->project->name ?? __('dash.open_area')) }}</span>
                        </span>
                        <span class="pm-num ula-meet-time">{{ $meeting->scheduled_at?->format('H:i') }}</span>
                        <x-badge :variant="$meeting->status === 'live' ? 'success' : 'warning'">{{ $meeting->status === 'live' ? __('dash.live_now') : __('dash.scheduled') }}</x-badge>
                    </div>
                @empty
                    <div class="ula-dash-empty">
                        <span class="ms" aria-hidden="true">event_available</span>
                        <span>{{ __('dash.no_meetings_today') }}</span>
                        <x-btn variant="secondary" size="md" icon="event" onclick="openScheduleMeetingModal('general')">{{ __('hero.schedule_meeting') }}</x-btn>
                    </div>
                @endforelse
            </div>
        </section>
    </div>

    @include('dashboard.partials.my-dashboard')

    <!-- ── 4. Quick actions + rooms in use right now ── -->
    @php
        $roomsTotal = $rooms->count();
        $roomsBusy = $onlineNow->pluck('room_id')->filter()->unique()->count();
        $inOpenArea = $onlineNow->whereNull('room_id')->count();
        $busyPercent = $roomsTotal > 0 ? (int) round($roomsBusy / $roomsTotal * 100) : 0;
        $circumference = 314;
        $dashoffset = $circumference - ($busyPercent / 100 * $circumference);
    @endphp
    <div class="ula-dash-pair ula-dash-pair--even">
        <section class="ula-dash-panel" aria-labelledby="dash-qa-title">
            <div class="ula-dash-panel-head">
                <span class="ms ula-dash-panel-icon" aria-hidden="true">bolt</span>
                <h3 id="dash-qa-title">{{ __('dash.quick_actions') }}</h3>
            </div>
            <div class="ula-quick-grid">
                <button type="button" class="ula-quick" onclick="openInviteModal()"><span class="ms" aria-hidden="true">person_add</span>{{ __('dash.invite_member') }}</button>
                <a class="ula-quick" href="{{ $dashUrl }}#rooms"><span class="ms" aria-hidden="true">meeting_room</span>{{ __('dash.manage_rooms') }}</a>
                <button type="button" class="ula-quick" onclick="openNewTaskModal()"><span class="ms" aria-hidden="true">add_task</span>{{ __('dash.new_task') }}</button>
                <button type="button" class="ula-quick" onclick="openScheduleMeetingModal('general')"><span class="ms" aria-hidden="true">event</span>{{ __('hero.schedule_meeting') }}</button>
            </div>
        </section>

        <a class="ula-dash-panel ula-dash-panel--link" href="{{ $dashUrl }}#rooms" aria-labelledby="dash-use-title">
            <div class="ula-dash-panel-head">
                <span class="ms ula-dash-panel-icon" aria-hidden="true">donut_large</span>
                <h3 id="dash-use-title">{{ __('dash.office_use_now') }}</h3>
            </div>
            <div class="ula-use">
                <div class="ula-use-donut">
                    <svg width="120" height="120" viewBox="0 0 120 120" aria-hidden="true">
                        <circle cx="60" cy="60" r="50" fill="none" stroke="var(--ula-border-subtle)" stroke-width="14"></circle>
                        <circle cx="60" cy="60" r="50" fill="none" stroke="var(--ula-accent-default)" stroke-width="14" stroke-linecap="round" stroke-dasharray="314" stroke-dashoffset="{{ $dashoffset }}"></circle>
                    </svg>
                    <span class="ula-use-figure"><span class="pm-num">{{ $busyPercent }}%</span><small>{{ __('dash.rooms_busy') }}</small></span>
                </div>
                <dl class="ula-use-legend">
                    <div><dt><i class="is-busy"></i>{{ __('dash.rooms_in_use') }}</dt><dd class="pm-num">{{ $roomsBusy }}</dd></div>
                    <div><dt><i class="is-free"></i>{{ __('dash.rooms_free') }}</dt><dd class="pm-num">{{ max(0, $roomsTotal - $roomsBusy) }}</dd></div>
                    <div><dt><i class="is-open"></i>{{ __('dash.in_open_area') }}</dt><dd class="pm-num">{{ $inOpenArea }}</dd></div>
                </dl>
            </div>
        </a>
    </div>

    <script nonce="{{ $cspNonce ?? '' }}">
        // Keep "online now" fresh while the dashboard is open (the office sends a heartbeat every 45s).
        (function () {
            const list = document.getElementById('dash-online-list');
            if (!list) return;
            const esc = (v) => String(v ?? '').replace(/[&<>"']/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
            const hhmm = (iso) => { const d = iso ? new Date(iso) : null; return d && !isNaN(d) ? String(d.getHours()).padStart(2, '0') + ':' + String(d.getMinutes()).padStart(2, '0') : ''; };
            async function refresh() {
                if (document.hidden) return;
                try {
                    const res = await fetch(list.dataset.endpoint, { headers: { Accept: 'application/json' }, credentials: 'same-origin' });
                    if (!res.ok) return;
                    const data = await res.json();
                    const people = Array.isArray(data.online) ? data.online : [];
                    list.innerHTML = people.length ? people.map(p => `
                        <li class="ula-online-row">
                            <span class="ula-online-avatar" aria-hidden="true">${p.avatar_url ? `<img src="${esc(p.avatar_url)}" alt="">` : esc((p.name || '?').charAt(0))}<span class="ula-online-dot"></span></span>
                            <span class="ula-online-main">
                                <span class="ula-online-name">${esc(p.name)}</span>
                                <span class="ula-online-where"><span class="ms" aria-hidden="true">${p.room ? 'meeting_room' : 'deck'}</span>${esc(p.room || list.dataset.openArea)}</span>
                            </span>
                            <span class="ula-online-since">${esc(list.dataset.since)} <span class="pm-num">${hhmm(p.since)}</span></span>
                        </li>`).join('') : `<li class="ula-online-empty">${esc(list.dataset.empty)}</li>`;
                    const count = document.getElementById('dash-online-count');
                    if (count) count.textContent = people.length;
                    const kpi = document.querySelector('#kpi-online [data-kpi-value]');
                    if (kpi) kpi.textContent = people.length;
                } catch (e) { /* keep the last list */ }
            }
            setInterval(refresh, 60000);
            document.addEventListener('visibilitychange', () => { if (!document.hidden) refresh(); });
        })();
    </script>

</div>
