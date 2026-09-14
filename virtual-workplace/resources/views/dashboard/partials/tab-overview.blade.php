<div id="tab-overview" class="tab-view active">

    <!-- ── 1. Welcome Hero Banner (Figma Dashboard Screen) ── -->
    <div class="nx-hero-welcome">
        <!-- Left / RTL Start: Greeting & Actions -->
        <div style="flex: 1; min-width: 280px;">
            <div class="nx-hero-greeting-pill">
                <span style="width: 8px; height: 8px; border-radius: 50%; background: #3C6B4C; display: inline-block;"></span>
                <span>{{ __('Ready to Collaborate') }}</span>
                <span style="color: var(--nx-text-mute);">·</span>
                <span style="color: var(--nx-text-mute);">{{ $organization->name }}</span>
            </div>

            <h1 class="nx-hero-title">
                {{ __('Good morning, :name!', ['name' => explode(' ', $user->name)[0]]) }}
            </h1>
            
            <p class="nx-hero-tagline">
                {{ __('Your workspace is ready. Let\'s make today productive!') }}
                <span style="display: block; font-size: 12px; color: var(--nx-text-mute); margin-top: 2px;">
                    ” كل فكرة عظيمة تبدأ بمحادثة “
                </span>
            </p>

            <!-- Action CTAs -->
            <div class="nx-hero-actions">
                <a href="{{ route('office') }}" class="nx-btn-primary">
                    <span class="material-symbols-rounded" style="font-size: 18px;">apartment</span>
                    <span>{{ __('Enter Workspace') }}</span>
                </a>
                
                <button type="button" onclick="openScheduleMeetingModal('general')" class="nx-btn-secondary">
                    <span class="material-symbols-rounded" style="font-size: 18px;">calendar_add_on</span>
                    <span>{{ __('Schedule Meeting') }}</span>
                </button>

                @if($membership->hasPermission('maps.manage'))
                    <a href="{{ route('editor') }}" class="nx-btn-outline">
                        <span class="material-symbols-rounded" style="font-size: 18px;">design_services</span>
                        <span>{{ __('Floor Editor') }}</span>
                    </a>
                @endif
            </div>
        </div>

        <!-- Right / RTL End: Date Capsule & User Avatar -->
        <div class="nx-hero-date-card">
            <!-- User Avatar with Online status -->
            <div style="position: relative; cursor: pointer;" onclick="switchAdminTab('profile')" title="{{ __('View Profile') }}">
                <div style="width: 52px; height: 52px; border-radius: 50%; border: 2px solid #FFFFFF; box-shadow: 0 2px 8px rgba(20,43,36,0.08); overflow: hidden; background: #F4EDE1; display: flex; align-items: center; justify-content: center; font-size: 18px; font-weight: 700; color: #142B24;">
                    @if($user->avatar_url)
                        <img src="{{ $user->avatar_url }}" alt="{{ $user->name }}" style="width: 100%; height: 100%; object-fit: cover;">
                    @else
                        <span>{{ strtoupper(substr($user->name, 0, 2)) }}</span>
                    @endif
                </div>
                <div style="position: absolute; bottom: 0; inset-inline-end: 0; width: 13px; height: 13px; border-radius: 50%; background: #3C6B4C; border: 2px solid #FFFFFF;" title="{{ __('Online') }}"></div>
            </div>

            <!-- Date Info Block -->
            <div style="display: flex; flex-direction: column; text-align: start;">
                <span style="font-size: 11px; font-weight: 600; color: #D3A553; text-transform: uppercase; letter-spacing: 0.5px;">
                    {{ now()->locale(app()->getLocale())->translatedFormat('l') }}
                </span>
                <span style="font-size: 18px; font-weight: 300; color: #142B24; line-height: 1.2;">
                    {{ now()->format('d') }} {{ now()->locale(app()->getLocale())->translatedFormat('F') }}
                </span>
                <span style="font-size: 11px; color: #8E9D95; font-family: 'IBM Plex Mono', monospace;">
                    {{ now()->format('Y') }}
                </span>
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
                    <span class="nx-stat-title">المتواجدون الآن</span>
                    <span class="nx-stat-subtitle">Active Presence</span>
                </div>
                <div class="nx-stat-icon-wrap emerald">
                    <span class="material-symbols-rounded" style="font-size: 19px;">group</span>
                </div>
            </div>
            <div>
                <div class="nx-stat-number {{ $activeMembersCount == 0 ? 'is-zero' : '' }}">
                    {{ $activeMembersCount }}
                </div>
                <div class="nx-stat-caption">من فريقك متصل حالياً</div>
            </div>
        </div>

        <!-- 2. Today's Meetings -->
        <div class="nx-stat-card">
            <div class="nx-stat-top">
                <div>
                    <span class="nx-stat-title">اجتماعات اليوم</span>
                    <span class="nx-stat-subtitle">Today's Sessions</span>
                </div>
                <div class="nx-stat-icon-wrap gold">
                    <span class="material-symbols-rounded" style="font-size: 19px;">calendar_month</span>
                </div>
            </div>
            <div>
                <div class="nx-stat-number {{ $todayMeetings->count() == 0 ? 'is-zero' : '' }}">
                    {{ $todayMeetings->count() }}
                </div>
                <div class="nx-stat-caption">مواعيد مجدولة لليوم</div>
            </div>
        </div>

        <!-- 3. Active Workspaces -->
        <div class="nx-stat-card">
            <div class="nx-stat-top">
                <div>
                    <span class="nx-stat-title">مكاتب نشطة</span>
                    <span class="nx-stat-subtitle">Active Workspaces</span>
                </div>
                <div class="nx-stat-icon-wrap sage">
                    <span class="material-symbols-rounded" style="font-size: 19px;">meeting_room</span>
                </div>
            </div>
            <div>
                <div class="nx-stat-number {{ $openRooms == 0 ? 'is-zero' : '' }}">
                    {{ $openRooms }}
                </div>
                <div class="nx-stat-caption">قاعات مفتوحة للعمل</div>
            </div>
        </div>

        <!-- 4. Pending Invitations -->
        <div class="nx-stat-card">
            <div class="nx-stat-top">
                <div>
                    <span class="nx-stat-title">الدعوات الجديدة</span>
                    <span class="nx-stat-subtitle">Pending Invites</span>
                </div>
                <div class="nx-stat-icon-wrap muted">
                    <span class="material-symbols-rounded" style="font-size: 19px;">mail</span>
                </div>
            </div>
            <div>
                <div class="nx-stat-number {{ $pendingGuests == 0 ? 'is-zero' : '' }}">
                    {{ $pendingGuests }}
                </div>
                <div class="nx-stat-caption">بانتظار الانضمام</div>
            </div>
        </div>
    </div>

    <!-- ── 3. Three Panels (Figma Screen Layout) ── -->
    <div class="nx-panels-grid">
        
        <!-- Panel 1: Quick Actions -->
        <div class="nx-panel-card">
            <div class="nx-panel-header">
                <h3 class="nx-panel-title">{{ __('Quick Actions (إجراءات سريعة)') }}</h3>
            </div>

            <div class="nx-quick-grid">
                <button type="button" class="nx-quick-tile" onclick="openScheduleMeetingModal('general')">
                    <span class="material-symbols-rounded nx-quick-tile-icon">calendar_add_on</span>
                    <div>
                        <span class="nx-quick-tile-title">جدولة اجتماع</span>
                        <span class="nx-quick-tile-subtitle">Schedule</span>
                    </div>
                </button>

                <button type="button" class="nx-quick-tile" onclick="openInviteModal()">
                    <span class="material-symbols-rounded nx-quick-tile-icon">person_add</span>
                    <div>
                        <span class="nx-quick-tile-title">دعوة عضو</span>
                        <span class="nx-quick-tile-subtitle">Invite</span>
                    </div>
                </button>

                <button type="button" class="nx-quick-tile" onclick="switchAdminTab('rooms')">
                    <span class="material-symbols-rounded nx-quick-tile-icon">meeting_room</span>
                    <div>
                        <span class="nx-quick-tile-title">إدارة القاعات</span>
                        <span class="nx-quick-tile-subtitle">Rooms</span>
                    </div>
                </button>

                <button type="button" class="nx-quick-tile" onclick="openCreateTaskModal()">
                    <span class="material-symbols-rounded nx-quick-tile-icon">add_task</span>
                    <div>
                        <span class="nx-quick-tile-title">مهمة جديدة</span>
                        <span class="nx-quick-tile-subtitle">Task</span>
                    </div>
                </button>
            </div>
        </div>

        <!-- Panel 2: Today's Meetings -->
        <div class="nx-panel-card">
            <div class="nx-panel-header">
                <div style="display: flex; align-items: center; gap: 8px;">
                    <h3 class="nx-panel-title">{{ __('Today\'s Scheduled Meetings (اجتماعات اليوم)') }}</h3>
                    <span style="font-size: 11px; font-weight: 700; padding: 2px 7px; border-radius: 9999px; background: #E7F3EC; color: #3C6B4C;">
                        {{ $todayMeetings->count() }}
                    </span>
                </div>
                <button type="button" onclick="switchAdminTab('meetings')" style="background: none; border: none; font-size: 12px; font-weight: 600; color: #D3A553; cursor: pointer;">
                    {{ __('View All (عرض الكل)') }} →
                </button>
            </div>

            <div class="nx-meeting-list">
                @forelse($todayMeetings->take(3) as $meeting)
                    <div class="nx-meeting-row">
                        <div style="display: flex; align-items: center; gap: 12px; min-width: 0;">
                            <span class="nx-meeting-time">
                                {{ $meeting->scheduled_at ? $meeting->scheduled_at->format('h:i A') : 'الآن' }}
                            </span>
                            <div style="display: flex; flex-direction: column; min-width: 0;">
                                <span style="font-size: 13px; font-weight: 500; color: #142B24; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                                    {{ $meeting->title }}
                                </span>
                                <span style="font-size: 11px; color: #5A6B63;">
                                    {{ $meeting->room->name ?? ($meeting->project->name ?? 'قاعة عامة') }}
                                </span>
                            </div>
                        </div>

                        <div class="nx-meeting-status-badge {{ $meeting->status === 'live' ? 'live' : 'scheduled' }}" title="{{ $meeting->status === 'live' ? 'مباشر الآن' : 'مجدول' }}">
                            <span class="material-symbols-rounded" style="font-size: 16px;">
                                {{ $meeting->status === 'live' ? 'videocam' : 'schedule' }}
                            </span>
                        </div>
                    </div>
                @empty
                    <div style="padding: 24px 16px; text-align: center; border-radius: 12px; border: 1px dashed rgba(20,43,36,0.12); background: #FAF6F0;">
                        <span class="material-symbols-rounded" style="font-size: 28px; color: #D3A553; display: block; margin-bottom: 6px;">calendar_month</span>
                        <p style="font-size: 13px; font-weight: 500; color: #142B24; margin: 0 0 4px 0;">
                            {{ __('No meetings scheduled for today') }}
                        </p>
                        <p style="font-size: 11px; color: #8E9D95; margin: 0 0 12px 0;">
                            {{ __('All clear for today. You can schedule a new meeting anytime.') }}
                        </p>
                        <button type="button" onclick="openScheduleMeetingModal('general')" class="nx-btn-secondary" style="height: 34px; padding: 0 14px; font-size: 12px;">
                            <span class="material-symbols-rounded" style="font-size: 15px;">add</span>
                            <span>{{ __('Schedule Meeting') }}</span>
                        </button>
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
                <h3 class="nx-panel-title">{{ __('Workspace (مساحة العمل)') }}</h3>
            </div>

            <div class="nx-donut-wrap">
                <!-- SVG Donut Chart -->
                <x-donut-chart 
                    :percent="$occupancyPercent" 
                    size="default" 
                    label="{{ $occupancyPercent }}%" 
                    caption="قيد الاستخدام"
                    accentColor="#142B24"
                    trackColor="#F4EDE1"
                />

                <!-- Legend -->
                <div class="nx-legend-list">
                    <div class="nx-legend-item">
                        <span style="display: flex; align-items: center;">
                            <span class="nx-legend-dot" style="background: #3C6B4C;"></span>
                            <span>غرف مفتوحة</span>
                        </span>
                        <span style="font-family: 'IBM Plex Mono', monospace; font-weight: 600; color: #142B24;">{{ $openRooms }}</span>
                    </div>

                    <div class="nx-legend-item">
                        <span style="display: flex; align-items: center;">
                            <span class="nx-legend-dot" style="background: #D3A553;"></span>
                            <span>غرف مغلقة</span>
                        </span>
                        <span style="font-family: 'IBM Plex Mono', monospace; font-weight: 600; color: #142B24;">{{ $closedRooms }}</span>
                    </div>

                    <div class="nx-legend-item">
                        <span style="display: flex; align-items: center;">
                            <span class="nx-legend-dot" style="background: #E8DECC;"></span>
                            <span>معدل الشغور</span>
                        </span>
                        <span style="font-family: 'IBM Plex Mono', monospace; font-weight: 500; color: #8E9D95;">{{ 100 - $occupancyPercent }}%</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ── 4. Quote Banner Strip (Figma Spec) ── -->
    <div class="nx-quote-banner">
        <div style="display: flex; align-items: center; gap: 12px;">
            <span class="material-symbols-rounded" style="font-size: 22px; color: #D3A553;">format_quote</span>
            <div style="display: flex; flex-direction: column;">
                <span style="font-size: 13px; font-weight: 500; color: #142B24;">
                    ” مساحات أفضل تصنع فرقاً أعظم “
                </span>
                <span style="font-size: 11px; color: #8E9D95;">
                    Better spaces carve greater teams.
                </span>
            </div>
        </div>
        <div style="display: flex; align-items: center; gap: 8px; font-size: 11px; color: #8E9D95;">
            <span>UlaSpace Workplace</span>
            <span>·</span>
            <span>ALULA</span>
        </div>
    </div>

</div>
