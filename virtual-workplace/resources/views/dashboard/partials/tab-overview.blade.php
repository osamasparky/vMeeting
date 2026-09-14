<div id="tab-overview" class="tab-view active">

    <!-- ── 1. Welcome Hero Banner (Figma Dashboard Screen) ── -->
    <div class="relative overflow-hidden rounded-[var(--nx-radius-xl)] border border-[var(--nx-border-subtle)] bg-[var(--nx-bg-surface)] p-6 sm:p-8 mb-6 shadow-[var(--nx-shadow-sm)]">
        <!-- Subtle Arch Gradient Glow -->
        <div class="absolute inset-0 bg-gradient-to-br from-[var(--nx-palm-900)]/[0.02] via-transparent to-[var(--nx-gold-400)]/[0.05] pointer-events-none"></div>

        <div class="relative z-10 flex flex-col md:flex-row items-start md:items-center justify-between gap-6">
            <!-- Left (RTL Start): Greeting & Actions -->
            <div class="flex-1 min-w-0">
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-[var(--nx-sand-200)] border border-[var(--nx-border-subtle)] text-[12px] font-medium text-[var(--nx-palm-900)] mb-3">
                    <span class="w-2 h-2 rounded-full bg-[var(--nx-status-live)] animate-pulse"></span>
                    <span>{{ __('Ready to Collaborate') }}</span>
                    <span class="text-[var(--nx-text-muted)]">·</span>
                    <span class="text-[var(--nx-text-muted)]">{{ $organization->name }}</span>
                </div>

                <h1 class="text-[24px] sm:text-[28px] font-semibold text-[var(--nx-text-primary)] leading-tight mb-1.5 font-['IBM_Plex_Sans_Arabic',sans-serif]">
                    {{ __('Good morning, :name!', ['name' => explode(' ', $user->name)[0]]) }}
                </h1>
                
                <p class="text-[13px] sm:text-[14px] text-[var(--nx-text-secondary)] font-normal mb-5 max-w-xl">
                    {{ __('Your workspace is ready. Let\'s make today productive!') }}
                    <span class="text-[var(--nx-text-muted)] block text-[12px] mt-0.5 font-['IBM_Plex_Sans',sans-serif]">
                        ” كل فكرة عظيمة تبدأ بمحادثة “
                    </span>
                </p>

                <!-- Action CTAs -->
                <div class="flex items-center gap-3 flex-wrap">
                    <x-btn href="{{ route('office') }}" variant="primary" size="md" icon="apartment">
                        <span>{{ __('Enter Workspace') }}</span>
                    </x-btn>
                    
                    <x-btn onclick="openScheduleMeetingModal('general')" variant="secondary" size="md" icon="calendar_add_on">
                        <span>{{ __('Schedule Meeting') }}</span>
                    </x-btn>

                    @if($membership->hasPermission('maps.manage'))
                        <x-btn href="{{ route('editor') }}" variant="ghost" size="md" icon="design_services">
                            <span>{{ __('Floor Editor') }}</span>
                        </x-btn>
                    @endif
                </div>
            </div>

            <!-- Right (RTL End): Date Capsule & User Mini Card -->
            <div class="flex items-center gap-4 shrink-0 p-4 rounded-[var(--nx-radius-lg)] bg-[var(--nx-sand-100)] border border-[var(--nx-border-subtle)]">
                <!-- User Avatar -->
                <div class="relative cursor-pointer" onclick="switchAdminTab('profile')" title="{{ __('View Profile') }}">
                    <div class="w-14 h-14 rounded-full border-2 border-[var(--nx-bg-surface)] shadow-[var(--nx-shadow-sm)] overflow-hidden bg-[var(--nx-sand-200)] flex items-center justify-center text-[var(--nx-palm-900)] font-bold text-lg">
                        @if($user->avatar_url)
                            <img src="{{ $user->avatar_url }}" alt="{{ $user->name }}" class="w-full h-full object-cover">
                        @else
                            <span>{{ strtoupper(substr($user->name, 0, 2)) }}</span>
                        @endif
                    </div>
                    <div class="absolute bottom-0 inset-inline-end-0 w-3.5 h-3.5 rounded-full bg-[var(--nx-status-live)] border-2 border-[var(--nx-bg-surface)]" title="{{ __('Online') }}"></div>
                </div>

                <!-- Date Info Block -->
                <div class="flex flex-col text-start">
                    <span class="text-[11px] font-semibold text-[var(--nx-accent)] uppercase tracking-wider">
                        {{ now()->locale(app()->getLocale())->translatedFormat('l') }}
                    </span>
                    <span class="text-[20px] font-light text-[var(--nx-text-primary)] font-['IBM_Plex_Sans_Arabic',sans-serif] leading-tight">
                        {{ now()->format('d') }} {{ now()->locale(app()->getLocale())->translatedFormat('F') }}
                    </span>
                    <span class="text-[11px] text-[var(--nx-text-muted)] font-mono">
                        {{ now()->format('Y') }}
                    </span>
                </div>
            </div>
        </div>
    </div>

    <!-- ── 2. Stat Cards Grid (4 Columns · Figma Density=Compact) ── -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        @php
            $todayMeetings = $upcomingMeetings->filter(fn($m) => $m->scheduled_at && $m->scheduled_at->isToday());
            $openRooms = $rooms->where('door_status', 'open')->count();
            $pendingGuests = $guestInvitations->where('status', 'pending')->count() ?? 0;
            $activeMembersCount = $stats['members'] ?? 1;
        @endphp

        <!-- 1. Active Presence -->
        <x-kpi-card 
            title="المتواجدون الآن"
            subtitle="Active Presence"
            value="{{ $activeMembersCount }}"
            caption="من فريقك متصل حالياً"
            icon="group"
            iconColor="emerald"
            density="compact"
        />

        <!-- 2. Today's Meetings -->
        <x-kpi-card 
            title="اجتماعات اليوم"
            subtitle="Today's Sessions"
            value="{{ $todayMeetings->count() }}"
            caption="مواعيد مجدولة لليوم"
            icon="calendar_month"
            iconColor="gold"
            density="compact"
        />

        <!-- 3. Active Rooms -->
        <x-kpi-card 
            title="مكاتب نشطة"
            subtitle="Active Workspaces"
            value="{{ $openRooms }}"
            caption="قاعات مفتوحة للعمل"
            icon="meeting_room"
            iconColor="sage"
            density="compact"
        />

        <!-- 4. Pending Invitations -->
        <x-kpi-card 
            title="الدعوات الجديدة"
            subtitle="Pending Invites"
            value="{{ $pendingGuests }}"
            caption="بانتظار الانضمام"
            icon="mail"
            iconColor="muted"
            density="compact"
        />
    </div>

    <!-- ── 3. Three Panels (Figma Screen Layout) ── -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 mb-6">
        
        <!-- Panel 1: Quick Actions (3 Cols on LG) -->
        <div class="lg:col-span-4 flex flex-col gap-3">
            <div class="flex items-center justify-between">
                <h3 class="text-[15px] font-semibold text-[var(--nx-text-primary)] font-['IBM_Plex_Sans_Arabic',sans-serif]">
                    {{ __('Quick Actions (إجراءات سريعة)') }}
                </h3>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <x-quick-tile 
                    icon="calendar_add_on"
                    title="جدولة اجتماع"
                    subtitle="Schedule Meeting"
                    onclick="openScheduleMeetingModal('general')"
                />
                <x-quick-tile 
                    icon="person_add"
                    title="دعوة عضو"
                    subtitle="Invite Member"
                    onclick="openInviteMemberModal()"
                />
                <x-quick-tile 
                    icon="meeting_room"
                    title="إدارة القاعات"
                    subtitle="Manage Rooms"
                    onclick="switchAdminTab('rooms')"
                />
                <x-quick-tile 
                    icon="add_task"
                    title="مهمة جديدة"
                    subtitle="Create Task"
                    onclick="openCreateTaskModal()"
                />
            </div>
        </div>

        <!-- Panel 2: Today's Meetings (5 Cols on LG) -->
        <div class="lg:col-span-5 flex flex-col gap-3">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <h3 class="text-[15px] font-semibold text-[var(--nx-text-primary)] font-['IBM_Plex_Sans_Arabic',sans-serif]">
                        {{ __('Today\'s Scheduled Meetings (اجتماعات اليوم)') }}
                    </h3>
                    <x-badge variant="live" size="sm" :dot="true">
                        {{ $todayMeetings->count() }}
                    </x-badge>
                </div>
                <button type="button" onclick="switchAdminTab('meetings')" class="text-[12px] font-medium text-[var(--nx-accent)] hover:underline">
                    {{ __('View All (عرض الكل)') }} →
                </button>
            </div>

            <div class="flex flex-col gap-2.5">
                @forelse($todayMeetings->take(4) as $meeting)
                    <x-meeting-row 
                        :time="$meeting->scheduled_at ? $meeting->scheduled_at->format('h:i A') : 'الآن'"
                        :title="$meeting->title"
                        :room="$meeting->room->name ?? ($meeting->project->name ?? 'قاعة عامة')"
                        :status="$meeting->status === 'live' ? 'live' : ($meeting->status === 'cancelled' ? 'cancelled' : 'scheduled')"
                        density="compact"
                    />
                @empty
                    @if($upcomingMeetings->count() > 0)
                        @foreach($upcomingMeetings->take(3) as $meeting)
                            <x-meeting-row 
                                :time="$meeting->scheduled_at ? $meeting->scheduled_at->format('M d, h:i A') : 'قريباً'"
                                :title="$meeting->title"
                                :room="$meeting->room->name ?? ($meeting->project->name ?? 'قاعة عامة')"
                                :status="$meeting->status === 'live' ? 'live' : ($meeting->status === 'cancelled' ? 'cancelled' : 'scheduled')"
                                density="compact"
                            />
                        @endforeach
                    @else
                        <div class="p-6 text-center rounded-[var(--nx-radius-lg)] border border-dashed border-[var(--nx-border-default)] bg-[var(--nx-bg-surface)]">
                            <div class="w-10 h-10 mx-auto rounded-full bg-[var(--nx-sand-200)] flex items-center justify-center text-[var(--nx-accent)] mb-2">
                                <span class="material-symbols-rounded text-[20px]">calendar_month</span>
                            </div>
                            <p class="text-[13px] font-medium text-[var(--nx-text-primary)] mb-1">
                                {{ __('No meetings scheduled for today') }}
                            </p>
                            <p class="text-[11px] text-[var(--nx-text-muted)] mb-3">
                                {{ __('All clear for today. You can schedule a new meeting anytime.') }}
                            </p>
                            <x-btn onclick="openScheduleMeetingModal('general')" variant="outline" size="sm" icon="add">
                                <span>{{ __('Schedule Meeting') }}</span>
                            </x-btn>
                        </div>
                    @endif
                @endforelse
            </div>
        </div>

        <!-- Panel 3: Workspace Utilization Donut (3 Cols on LG) -->
        @php
            $totalRooms = max(1, $rooms->count());
            $occupancyPercent = round(($openRooms / $totalRooms) * 100);
            $closedRooms = $totalRooms - $openRooms;
        @endphp
        <div class="lg:col-span-3 flex flex-col gap-3">
            <div class="flex items-center justify-between">
                <h3 class="text-[15px] font-semibold text-[var(--nx-text-primary)] font-['IBM_Plex_Sans_Arabic',sans-serif]">
                    {{ __('Workspace (مساحة العمل)') }}
                </h3>
            </div>

            <div class="flex flex-col items-center justify-between rounded-[var(--nx-radius-lg)] border border-[var(--nx-border-subtle)] bg-[var(--nx-bg-surface)] p-5 shadow-[var(--nx-shadow-sm)]">
                <!-- Donut Chart -->
                <div class="my-2">
                    <x-donut-chart 
                        :percent="$occupancyPercent" 
                        size="default" 
                        label="{{ $occupancyPercent }}%" 
                        caption="قيد الاستخدام"
                    />
                </div>

                <!-- Legend -->
                <div class="w-full flex flex-col gap-2 mt-4 pt-3 border-t border-[var(--nx-border-subtle)] text-[12px]">
                    <div class="flex items-center justify-between">
                        <span class="flex items-center gap-2 text-[var(--nx-text-secondary)]">
                            <span class="w-2.5 h-2.5 rounded-full bg-[var(--nx-status-live)]"></span>
                            <span>غرف مفتوحة</span>
                        </span>
                        <span class="font-mono font-medium text-[var(--nx-text-primary)]">{{ $openRooms }}</span>
                    </div>

                    <div class="flex items-center justify-between">
                        <span class="flex items-center gap-2 text-[var(--nx-text-secondary)]">
                            <span class="w-2.5 h-2.5 rounded-full bg-[var(--nx-gold-400)]"></span>
                            <span>غرف مغلقة</span>
                        </span>
                        <span class="font-mono font-medium text-[var(--nx-text-primary)]">{{ $closedRooms }}</span>
                    </div>

                    <div class="flex items-center justify-between">
                        <span class="flex items-center gap-2 text-[var(--nx-text-muted)]">
                            <span class="w-2.5 h-2.5 rounded-full bg-[var(--nx-stone-track)]"></span>
                            <span>معدل الشغور</span>
                        </span>
                        <span class="font-mono font-medium text-[var(--nx-text-muted)]">{{ 100 - $occupancyPercent }}%</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ── 4. Quote Banner Strip (Figma Spec) ── -->
    <div class="relative overflow-hidden rounded-[var(--nx-radius-lg)] border border-[var(--nx-border-subtle)] bg-[var(--nx-sand-100)] p-4 shadow-[var(--nx-shadow-sm)] flex items-center justify-between gap-4">
        <div class="flex items-center gap-3">
            <span class="material-symbols-rounded text-[22px] text-[var(--nx-accent)]">format_quote</span>
            <div class="flex flex-col">
                <span class="text-[13px] font-medium text-[var(--nx-text-primary)] font-['IBM_Plex_Sans_Arabic',sans-serif]">
                    ” مساحات أفضل تصنع فرقاً أعظم “
                </span>
                <span class="text-[11px] text-[var(--nx-text-muted)] font-['IBM_Plex_Sans',sans-serif]">
                    Better spaces carve greater teams.
                </span>
            </div>
        </div>
        <div class="hidden sm:flex items-center gap-2 text-[11px] text-[var(--nx-text-muted)]">
            <span>UlaSpace Workplace</span>
            <span>·</span>
            <span>ALULA</span>
        </div>
    </div>

</div>
