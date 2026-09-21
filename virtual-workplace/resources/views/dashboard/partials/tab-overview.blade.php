<div id="tab-overview" class="tab-view active">

    <!-- ── 1. Welcome Hero Banner (Figma Dashboard Screen) ── -->
    <div class="nx-hero-welcome">
        <!-- Left / RTL Start: Greeting & Actions -->
        <div style="flex: 1; min-width: 280px;">
            <div class="nx-hero-greeting-pill">
                <span style="width: 8px; height: 8px; border-radius: 50%; background: var(--ula-palm-500); display: inline-block;"></span>
                <span>{{ __('Ready to Collaborate') }}</span>
                <span style="color: var(--ula-text-muted);">·</span>
                <span style="color: var(--ula-text-muted);">{{ $organization->name }}</span>
            </div>

            @php
                $hour = (int) now()->format('H');
                $isEvening = $hour >= 12;
            @endphp
            <h1 class="nx-hero-title" id="nx-hero-dynamic-greeting">
                @if($isEvening)
                    {{ __('Good evening, :name!', ['name' => explode(' ', $user->name)[0]]) }}
                @else
                    {{ __('Good morning, :name!', ['name' => explode(' ', $user->name)[0]]) }}
                @endif
            </h1>
            
            <p class="nx-hero-tagline">
                {{ __('Your workspace is ready. Let\'s make today productive!') }}
            </p>

            <!-- Action CTAs -->
            <div class="nx-hero-actions">
                <x-btn variant="primary" size="md" href="{{ route('office') }}" icon="apartment">
                    {{ __('Enter Workspace') }}
                </x-btn>
                
                <x-btn variant="secondary" size="md" type="button" onclick="openScheduleMeetingModal('general')" icon="calendar_add_on">
                    {{ __('Schedule Meeting') }}
                </x-btn>

                @if($membership->hasPermission('maps.manage'))
                    <x-btn variant="outline" size="md" href="{{ route('editor') }}" icon="design_services">
                        {{ __('Floor Editor') }}
                    </x-btn>
                @endif
            </div>
        </div>

        <!-- Right / RTL End: Date Capsule & Live Working Clock & User Avatar -->
        <div class="nx-hero-date-card" style="display: flex; align-items: center; gap: 14px; background: var(--ula-surface-card); border: 1px solid var(--ula-border-subtle); padding: 12px 18px; border-radius: 20px; box-shadow: var(--ula-shadow-xs);">
            <!-- User Avatar with Online status -->
            <div style="position: relative; cursor: pointer;" onclick="switchAdminTab('profile')" title="{{ __('View Profile') }}">
                <div style="width: 52px; height: 52px; border-radius: 50%; border: 2px solid var(--ula-white); box-shadow: 0 2px 8px rgba(20,43,36,0.08); overflow: hidden; background: var(--ula-sand-200); display: flex; align-items: center; justify-content: center; font-size: 18px; font-weight: 700; color: var(--ula-text-primary);">
                    @if($user->avatar_url)
                        <img src="{{ $user->avatar_url }}" alt="{{ $user->name }}" style="width: 100%; height: 100%; object-fit: cover;">
                    @else
                        <span>{{ strtoupper(substr($user->name, 0, 2)) }}</span>
                    @endif
                </div>
                <div style="position: absolute; bottom: 0; inset-inline-end: 0; width: 13px; height: 13px; border-radius: 50%; background: var(--ula-palm-500); border: 2px solid var(--ula-white);" title="{{ __('Online') }}"></div>
            </div>

            <!-- Date Info Block -->
            <div style="display: flex; flex-direction: column; text-align: start;">
                <span style="font-size: 11px; font-weight: 700; color: var(--ula-gold-500); text-transform: uppercase; letter-spacing: 0.5px;">
                    {{ now()->locale(app()->getLocale())->translatedFormat('l') }}
                </span>
                <span style="font-size: 17px; font-weight: 700; color: var(--ula-text-primary); line-height: 1.2;">
                    {{ now()->format('d') }} {{ now()->locale(app()->getLocale())->translatedFormat('F') }}
                </span>
                <span style="font-size: 11px; color: var(--ula-text-muted); font-family: var(--ula-font-mono); direction: ltr; unicode-bidi: isolate;">
                    {{ now()->format('Y') }}
                </span>
            </div>

            <!-- Working Live Time Clock (Item 2) -->
            <div style="display: flex; flex-direction: column; align-items: center; justify-content: center; padding-inline-start: 12px; border-inline-start: 1px solid var(--ula-border-subtle);">
                <span style="font-size: 10px; font-weight: 700; color: var(--ula-text-muted); text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 2px;">
                    {{ __('Current Time') }}
                </span>
                <div style="background: var(--ula-palm-900); color: #FFFFFF; padding: 4px 12px; border-radius: var(--ula-radius-pill); font-family: var(--ula-font-mono); font-size: 13.5px; font-weight: 800; display: inline-flex; align-items: center; gap: 5px; direction: ltr; unicode-bidi: isolate; box-shadow: 0 2px 6px rgba(27,53,36,0.15);">
                    <span class="material-symbols-rounded" style="font-size: 14px; color: var(--ula-gold-400);">schedule</span>
                    <span id="nx-hero-live-clock">{{ now()->format('h:i:s A') }}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- ── 2. Stat Cards Grid (4 Columns · Weight 300 Numbers) ── -->
    @php
        $todayMeetings = $upcomingMeetings->filter(fn($m) => $m->scheduled_at && $m->scheduled_at->isToday());
        $openRooms = $rooms->where('door_status', 'open')->count();
        $pendingGuests = $guestInvitations->where('status', 'pending')->count() ?? 0;
        $activeMembersCount = $stats['members'] ?? 1;
    @endphp

    <div class="nx-stat-grid">
        <!-- 1. Active Presence -->
        <div class="nx-stat-card">
            <div class="nx-stat-top">
                <div>
                    <span class="nx-stat-title">{{ __('Active Presence') }}</span>
                </div>
                <div class="nx-stat-icon-wrap emerald">
                    <span class="material-symbols-rounded" style="font-size: 19px;">group</span>
                </div>
            </div>
            <div>
                <div class="nx-stat-number {{ $activeMembersCount == 0 ? 'is-zero' : '' }}">
                    {{ $activeMembersCount }}
                </div>
                <div class="nx-stat-caption">{{ __('from your team online now') }}</div>
            </div>
        </div>

        <!-- 2. Today's Meetings -->
        <div class="nx-stat-card">
            <div class="nx-stat-top">
                <div>
                    <span class="nx-stat-title">{{ __('Today\'s Meetings') }}</span>
                </div>
                <div class="nx-stat-icon-wrap gold">
                    <span class="material-symbols-rounded" style="font-size: 19px;">calendar_month</span>
                </div>
            </div>
            <div>
                <div class="nx-stat-number {{ $todayMeetings->count() == 0 ? 'is-zero' : '' }}">
                    {{ $todayMeetings->count() }}
                </div>
                <div class="nx-stat-caption">{{ __('scheduled for today') }}</div>
            </div>
        </div>

        <!-- 3. Active Workspaces -->
        <div class="nx-stat-card">
            <div class="nx-stat-top">
                <div>
                    <span class="nx-stat-title">{{ __('Active Workspaces') }}</span>
                </div>
                <div class="nx-stat-icon-wrap sage">
                    <span class="material-symbols-rounded" style="font-size: 19px;">meeting_room</span>
                </div>
            </div>
            <div>
                <div class="nx-stat-number {{ $openRooms == 0 ? 'is-zero' : '' }}">
                    {{ $openRooms }}
                </div>
                <div class="nx-stat-caption">{{ __('open for work') }}</div>
            </div>
        </div>

        <!-- 4. Pending Invitations -->
        <div class="nx-stat-card">
            <div class="nx-stat-top">
                <div>
                    <span class="nx-stat-title">{{ __('Pending Invitations') }}</span>
                </div>
                <div class="nx-stat-icon-wrap muted">
                    <span class="material-symbols-rounded" style="font-size: 19px;">mail</span>
                </div>
            </div>
            <div>
                <div class="nx-stat-number {{ $pendingGuests == 0 ? 'is-zero' : '' }}">
                    {{ $pendingGuests }}
                </div>
                <div class="nx-stat-caption">{{ __('awaiting join') }}</div>
            </div>
        </div>
    </div>

    <!-- ── 3. Three Panels (Figma Screen Layout) ── -->
    <div class="nx-panels-grid">
        
        <!-- Panel 1: Quick Actions -->
        <div class="nx-panel-card">
            <div class="nx-panel-header">
                <h3 class="nx-panel-title">{{ __('Quick Actions') }}</h3>
            </div>

            <div class="nx-quick-grid">
                <button type="button" class="nx-quick-tile" onclick="openScheduleMeetingModal('general')">
                    <span class="material-symbols-rounded nx-quick-tile-icon">calendar_add_on</span>
                    <div>
                        <span class="nx-quick-tile-title">{{ __('Schedule Meeting') }}</span>
                    </div>
                </button>

                <button type="button" class="nx-quick-tile" onclick="openInviteModal()">
                    <span class="material-symbols-rounded nx-quick-tile-icon">person_add</span>
                    <div>
                        <span class="nx-quick-tile-title">{{ __('Invite Member') }}</span>
                    </div>
                </button>

                <button type="button" class="nx-quick-tile" onclick="switchAdminTab('rooms')">
                    <span class="material-symbols-rounded nx-quick-tile-icon">meeting_room</span>
                    <div>
                        <span class="nx-quick-tile-title">{{ __('Manage Rooms') }}</span>
                    </div>
                </button>

                <button type="button" class="nx-quick-tile" onclick="openCreateTaskModal()">
                    <span class="material-symbols-rounded nx-quick-tile-icon">add_task</span>
                    <div>
                        <span class="nx-quick-tile-title">{{ __('New Task') }}</span>
                    </div>
                </button>
            </div>
        </div>

        <!-- Panel 2: Today's Meetings -->
        <div class="nx-panel-card">
            <div class="nx-panel-header">
                <div style="display: flex; align-items: center; gap: 8px;">
                    <h3 class="nx-panel-title">{{ __('Today\'s Scheduled Meetings') }}</h3>
                    <span style="font-size: 11px; font-weight: 700; padding: 2px 7px; border-radius: 9999px; background: var(--ula-tone-palm-bg); color: var(--ula-palm-500);">
                        {{ $todayMeetings->count() }}
                    </span>
                </div>
                <button type="button" onclick="switchAdminTab('meetings')" style="background: none; border: none; font-size: 12px; font-weight: 600; color: var(--ula-gold-400); cursor: pointer;">
                    {{ __('View All') }} →
                </button>
            </div>

            <div class="nx-meeting-list">
                @forelse($todayMeetings->take(3) as $meeting)
                    <div class="nx-meeting-row">
                        <div style="display: flex; align-items: center; gap: 12px; min-width: 0;">
                            <span class="nx-meeting-time">
                                {{ $meeting->scheduled_at ? $meeting->scheduled_at->format('h:i A') : __('Now') }}
                            </span>
                            <div style="display: flex; flex-direction: column; min-width: 0;">
                                <span style="font-size: 13px; font-weight: 500; color: var(--ula-text-primary); overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                                    {{ $meeting->title }}
                                </span>
                                <span style="font-size: 11px; color: var(--ula-stone-600);">
                                    {{ $meeting->room->name ?? ($meeting->project->name ?? __('General Room')) }}
                                </span>
                            </div>
                        </div>

                        <div class="nx-meeting-status-badge {{ $meeting->status === 'live' ? 'live' : 'scheduled' }}" title="{{ $meeting->status === 'live' ? __('Live Now') : __('Scheduled') }}">
                            <span class="material-symbols-rounded" style="font-size: 16px;">
                                {{ $meeting->status === 'live' ? 'videocam' : 'schedule' }}
                            </span>
                        </div>
                    </div>
                @empty
                    <div style="padding: 24px 16px; text-align: center; border-radius: 12px; border: 1px dashed rgba(20,43,36,0.12); background: var(--ula-sand-50);">
                        <span class="material-symbols-rounded" style="font-size: 28px; color: var(--ula-gold-400); display: block; margin-bottom: 6px;">calendar_month</span>
                        <p style="font-size: 13px; font-weight: 500; color: var(--ula-text-primary); margin: 0 0 4px 0;">
                            {{ __('No meetings scheduled for today') }}
                        </p>
                        <p style="font-size: 11px; color: var(--ula-stone-500); margin: 0 0 12px 0;">
                            {{ __('All clear for today. You can schedule a new meeting anytime.') }}
                        </p>
                        <x-btn variant="secondary" size="sm" type="button" onclick="openScheduleMeetingModal('general')" icon="add">
                            {{ __('Schedule Meeting') }}
                        </x-btn>
                    </div>
                @endforelse
            </div>
        </div>

        <!-- Panel 3: Workspace Utilization Donut -->
        @php
            $totalRooms = max(1, $rooms->count());
            $occupancyPercent = round(($openRooms / $totalRooms) * 100);
            $closedRooms = $totalRooms - $openRooms;
        @endphp
        <div class="nx-panel-card">
            <div class="nx-panel-header">
                <h3 class="nx-panel-title">{{ __('Workspace Utilization') }}</h3>
            </div>

            <div class="nx-donut-wrap">
                <!-- SVG Donut Chart -->
                <x-donut-chart 
                    :percent="$occupancyPercent" 
                    size="default" 
                    label="{{ $occupancyPercent }}%" 
                    caption="{{ __('In Use') }}"
                    accentColor="var(--ula-palm-900)"
                    trackColor="var(--ula-sand-200)"
                />

                <!-- Legend -->
                <div class="nx-legend-list">
                    <div class="nx-legend-item">
                        <span style="display: flex; align-items: center;">
                            <span class="nx-legend-dot" style="background: var(--ula-palm-500);"></span>
                            <span>{{ __('Open Rooms') }}</span>
                        </span>
                        <span style="font-family: 'IBM Plex Mono', monospace; font-weight: 600; color: var(--ula-text-primary);">{{ $openRooms }}</span>
                    </div>

                    <div class="nx-legend-item">
                        <span style="display: flex; align-items: center;">
                            <span class="nx-legend-dot" style="background: var(--ula-gold-400);"></span>
                            <span>{{ __('Locked Rooms') }}</span>
                        </span>
                        <span style="font-family: 'IBM Plex Mono', monospace; font-weight: 600; color: var(--ula-text-primary);">{{ $closedRooms }}</span>
                    </div>

                    <div class="nx-legend-item">
                        <span style="display: flex; align-items: center;">
                            <span class="nx-legend-dot" style="background: var(--ula-sand-400);"></span>
                            <span>{{ __('Vacancy Rate') }}</span>
                        </span>
                        <span style="font-family: 'IBM Plex Mono', monospace; font-weight: 500; color: var(--ula-stone-500);">{{ 100 - $occupancyPercent }}%</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ── 4. Quote Banner Strip (Figma Spec) ── -->
    <div class="nx-quote-banner">
        <div style="display: flex; align-items: center; gap: 12px;">
            <span class="material-symbols-rounded" style="font-size: 22px; color: var(--ula-gold-400);">format_quote</span>
            <div style="display: flex; flex-direction: column;">
                <span style="font-size: 13px; font-weight: 500; color: var(--ula-text-primary);">
                    {{ __('Better spaces carve greater teams.') }}
                </span>
            </div>
        </div>
        <div style="display: flex; align-items: center; gap: 8px; font-size: 11px; color: var(--ula-stone-500);">
            <span>{{ __('UlaSpace Workplace') }}</span>
            <span>·</span>
            <span>ALULA</span>
        </div>
    </div>

</div>
