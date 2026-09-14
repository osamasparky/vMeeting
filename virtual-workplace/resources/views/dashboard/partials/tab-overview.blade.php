        <div id="tab-overview" class="tab-view active">

            <!-- ── 1. Welcome Banner Card & Workplace Status (Figma Dashboard Screen) ── -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
                <!-- Welcome Banner (2 cols on lg) -->
                <div class="lg:col-span-2 relative overflow-hidden rounded-[var(--nx-radius-xl)] border border-[var(--nx-border-subtle)] bg-[var(--nx-bg-surface)] p-8 shadow-[var(--nx-shadow-sm)] flex items-center justify-between">
                    <div class="relative z-10 max-w-[65%]">
                        <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-[var(--nx-sand-200)] border border-[var(--nx-border-subtle)] text-[12px] font-semibold text-[var(--nx-palm-900)] mb-3">
                            <span class="w-2 h-2 rounded-full bg-[var(--nx-status-live)]"></span>
                            <span>{{ __('Ready to Collaborate') }}</span>
                        </div>
                        <h2 class="text-[26px] font-semibold text-[var(--nx-text-primary)] leading-tight mb-2 font-['IBM_Plex_Sans_Arabic',sans-serif]">
                            {{ __('Good morning, :name!', ['name' => explode(' ', $user->name)[0]]) }}
                        </h2>
                        <p class="text-[14px] text-[var(--nx-text-secondary)] mb-6 font-normal">
                            {{ __('Your workspace is ready. Let\'s make today productive!') }}
                        </p>
                        <div class="flex items-center gap-3 flex-wrap">
                            <x-btn href="{{ route('office') }}" variant="primary" size="md" icon="apartment">
                                <span>{{ __('Enter Workspace') }}</span>
                            </x-btn>
                            @if($membership->hasPermission('maps.manage'))
                                <x-btn href="{{ route('editor') }}" variant="outline" size="md" icon="design_services">
                                    <span>{{ __('Floor Editor') }}</span>
                                </x-btn>
                            @endif
                        </div>
                    </div>

                    <!-- User Avatar & Status -->
                    <div class="relative z-10 shrink-0 flex items-center justify-center cursor-pointer" onclick="switchAdminTab('profile')" title="{{ __('View Profile') }}">
                        <div class="w-28 h-28 rounded-full border-4 border-[var(--nx-bg-surface)] shadow-[var(--nx-shadow-lg)] overflow-hidden bg-[var(--nx-sand-200)] flex items-center justify-center text-[var(--nx-palm-900)] font-bold text-3xl">
                            @if($user->avatar_url)
                                <img src="{{ $user->avatar_url }}" alt="{{ $user->name }}" class="w-full h-full object-cover">
                            @else
                                <span>{{ strtoupper(substr($user->name, 0, 2)) }}</span>
                            @endif
                        </div>
                        <div class="absolute bottom-1 inset-inline-end-1 w-6 h-6 rounded-full bg-[var(--nx-status-live)] border-2 border-[var(--nx-bg-surface)] shadow-md" title="{{ __('Online') }}"></div>
                    </div>
                </div>

                <!-- Workplace Quick Info Card (1 col on lg) -->
                <div class="relative overflow-hidden rounded-[var(--nx-radius-xl)] border border-[var(--nx-border-subtle)] bg-[var(--nx-bg-surface)] p-6 shadow-[var(--nx-shadow-sm)] flex flex-col justify-between">
                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-[15px] font-semibold text-[var(--nx-text-primary)]">{{ __('Your Workspace') }}</span>
                            <span class="text-[12px] font-semibold text-[var(--nx-status-live)] flex items-center gap-1">
                                <span class="w-2 h-2 rounded-full bg-[var(--nx-status-live)]"></span>
                                {{ $stats['members'] }} {{ __('Active') }}
                            </span>
                        </div>
                        <p class="text-[13px] text-[var(--nx-text-muted)]">
                            📍 {{ $organization->name }} • {{ $rooms->count() }} {{ __('Rooms Available') }}
                        </p>
                    </div>

                    <div class="grid grid-cols-2 gap-3 my-4">
                        <div class="p-3 rounded-[var(--nx-radius-md)] bg-[var(--nx-sand-100)] border border-[var(--nx-border-subtle)] text-center">
                            <span class="text-[20px] font-light text-[var(--nx-text-primary)] block">{{ $rooms->where('door_status', 'open')->count() }}</span>
                            <span class="text-[11px] text-[var(--nx-text-muted)]">{{ __('Open Rooms') }}</span>
                        </div>
                        <div class="p-3 rounded-[var(--nx-radius-md)] bg-[var(--nx-sand-100)] border border-[var(--nx-border-subtle)] text-center">
                            <span class="text-[20px] font-light text-[var(--nx-text-primary)] block">{{ $stats['active_meetings'] ?? 0 }}</span>
                            <span class="text-[11px] text-[var(--nx-text-muted)]">{{ __('Live Meetings') }}</span>
                        </div>
                    </div>

                    <x-btn href="{{ route('office') }}" variant="secondary" size="sm" class="w-full justify-center">
                        <span>{{ __('View 3D Live Floor') }}</span>
                    </x-btn>
                </div>
            </div>

            <!-- ── 2. Top KPI Band (Donut percentage charts & 300 weight numbers) ── -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
                <!-- 1. Active Presence Attendance -->
                @php
                    $attendanceRate = $stats['total_users'] > 0 ? round(($stats['members'] / $stats['total_users']) * 100) : 78;
                @endphp
                <x-kpi-card 
                    title="نسبة الحضور والتواجد"
                    subtitle="Daily Presence Rate"
                    value="{{ $attendanceRate }}%"
                    metric="{{ $stats['members'] }} من إجمالي {{ $stats['total_users'] }} عضو نشط"
                    :donut="$attendanceRate"
                    donutSize="lg"
                    trend="+8%"
                    trendDirection="up"
                />

                <!-- 2. Rooms Utilization -->
                @php
                    $roomUtil = $rooms->count() > 0 ? round(($rooms->where('door_status', 'open')->count() / $rooms->count()) * 100) : 65;
                @endphp
                <x-kpi-card 
                    title="إشغال القاعات والمساحات"
                    subtitle="Room Occupancy & Availability"
                    value="{{ $rooms->count() }}"
                    metric="{{ $rooms->where('door_status', 'open')->count() }} قاعة مفتوحة للعمل"
                    :donut="$roomUtil"
                    donutSize="lg"
                    trend="+14%"
                    trendDirection="up"
                />

                <!-- 3. Meetings Today -->
                @php
                    $todayMeetingsCount = $upcomingMeetings->filter(fn($m) => $m->scheduled_at && $m->scheduled_at->isToday())->count();
                    $meetingsRate = min(100, $todayMeetingsCount * 25);
                @endphp
                <x-kpi-card 
                    title="اجتماعات وجلسات اليوم"
                    subtitle="Today's Sessions"
                    value="{{ $todayMeetingsCount }}"
                    metric="إجمالي الجلسات المجدولة لليوم"
                    :donut="$meetingsRate"
                    donutSize="lg"
                    trend="منتظم"
                    trendDirection="neutral"
                />
            </div>

            <!-- ── 3. Quick Action Tiles Grid & Meetings Section ── -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
                
                <!-- Left: Quick Action Tiles (2x2 Grid) -->
                <div class="flex flex-col gap-4">
                    <div class="flex items-center justify-between">
                        <h3 class="text-[16px] font-semibold text-[var(--nx-text-primary)]">
                            {{ __('Quick Actions (إجراءات سريعة)') }}
                        </h3>
                    </div>

                    <div class="grid grid-cols-2 gap-3.5">
                        <x-quick-tile 
                            icon="video_camera_front"
                            title="جلسة سريعة"
                            subtitle="Instant Meeting"
                            onclick="openScheduleMeetingModal('general')"
                        />
                        <x-quick-tile 
                            icon="person_add"
                            title="دعوة عضو"
                            subtitle="Invite Member"
                            onclick="openInviteMemberModal()"
                        />
                        <x-quick-tile 
                            icon="add_task"
                            title="مهمة جديدة"
                            subtitle="Create Task"
                            onclick="openCreateTaskModal()"
                        />
                        <x-quick-tile 
                            icon="meeting_room"
                            title="إدارة القاعات"
                            subtitle="Manage Rooms"
                            onclick="switchAdminTab('rooms')"
                        />
                    </div>
                </div>

                <!-- Right: Recent & Live Meetings (2 cols on lg) -->
                <div class="lg:col-span-2 flex flex-col gap-4">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <h3 class="text-[16px] font-semibold text-[var(--nx-text-primary)]">
                                {{ __('Today\'s Scheduled Meetings (اجتماعات اليوم)') }}
                            </h3>
                            <x-badge variant="live" size="sm" :dot="true">
                                {{ $upcomingMeetings->count() }}
                            </x-badge>
                        </div>
                        <button onclick="switchAdminTab('meetings')" class="text-[13px] font-medium text-[var(--nx-accent)] hover:underline">
                            {{ __('View All (عرض الكل)') }} →
                        </button>
                    </div>

                    <div class="flex flex-col gap-3">
                        @forelse($upcomingMeetings->take(4) as $meeting)
                            <x-meeting-row 
                                :time="$meeting->scheduled_at ? $meeting->scheduled_at->format('h:i A') : 'الآن'"
                                :title="$meeting->title"
                                :room="$meeting->room->name ?? ($meeting->project->name ?? 'قاعة عامة')"
                                :status="$meeting->status === 'live' ? 'live' : ($meeting->status === 'cancelled' ? 'cancelled' : 'scheduled')"
                            />
                        @empty
                            <x-empty-state 
                                icon="calendar_month" 
                                title="لا توجد اجتماعات مجدولة لليوم"
                                subtitle="All clear for today. You can schedule a new meeting anytime."
                                actionLabel="جدولة اجتماع جديد"
                                actionIcon="add"
                                onclick="openScheduleMeetingModal('general')"
                            />
                        @endforelse
                    </div>
                </div>
            </div>

        </div>
