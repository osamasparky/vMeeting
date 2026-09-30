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

    <!-- ── 2. Stat Cards Grid (4 Columns · Weight 300 Numbers) ── -->
    @php
        $todayMeetings = $upcomingMeetings->filter(fn($m) => $m->scheduled_at && $m->scheduled_at->isToday());
        $openRooms = $rooms->where('door_status', 'open')->count();
        $pendingGuests = $guestInvitations->where('status', 'pending')->count() ?? 0;
        $activeMembersCount = $stats['members'] ?? 1;
    @endphp

    <div class="ula-kpi-row">
        <x-kpi-card density="default" icon="person" iconColor="sage" :title="__('متصلون الآن')" :value="$activeMembersCount" :caption="__('من فريقك متصلون الآن')" />
        <x-kpi-card density="default" icon="event" iconColor="gold" :title="__('اجتماعات اليوم')" :value="$todayMeetings->count()" :caption="__('مجدولة لهذا اليوم')" />
        <x-kpi-card density="default" icon="meeting_room" iconColor="sage" :title="__('مساحات نشطة')" :value="$openRooms" :caption="__('مفتوحة للعمل')" />
        <x-kpi-card density="default" icon="mail" iconColor="terracotta" :title="__('دعوات معلقة')" :value="$pendingGuests" :caption="__('بانتظار الانضمام')" />
    </div>

    <!-- ── 3. Bottom Panels Grid (Matches 06-Dashboard-Overview: 1.4fr 1fr) ── -->
    <div style="display: grid; grid-template-columns: minmax(0, 1.4fr) minmax(0, 1fr); gap: 16px;">
        <!-- Column 1: Today's Meetings -->
        <div style="border-radius: 20px; border: var(--ula-border-width-hairline) solid var(--ula-border-subtle); background: var(--ula-surface-card); padding: 20px; box-shadow: var(--ula-shadow-xs); display: flex; flex-direction: column; gap: 16px; min-width: 0;">
            <div style="display: flex; align-items: center; gap: 10px;">
                <span style="color: var(--ula-accent-hover); display: inline-flex;"><span class="ms" style="font-size: 20px;">calendar_month</span></span>
                <span style="font-size: 18px; font-weight: 600; color: var(--ula-text-primary);">{{ __('اجتماعات اليوم') }}</span>
                <div style="flex: 1;"></div>
                <a href="javascript:void(0)" onclick="switchAdminTab('meetings')" style="font-size: 13px; font-weight: 600; color: var(--ula-accent-default); text-decoration: none;">{{ __('عرض الكل') }}</a>
            </div>

            <div style="display: flex; flex-direction: column; gap: 10px;">
                @forelse($todayMeetings->take(4) as $meeting)
                    <div style="display: flex; align-items: center; gap: 12px; padding: 12px 14px; border-radius: 14px; background: var(--ula-surface-page); border: var(--ula-border-width-hairline) solid var(--ula-border-subtle);">
                        <span style="width: 36px; height: 36px; border-radius: 10px; background: {{ $meeting->status === 'live' ? 'var(--ula-tone-palm-bg)' : 'var(--ula-tone-gold-bg)' }}; color: {{ $meeting->status === 'live' ? 'var(--ula-tone-palm-fg)' : 'var(--ula-tone-gold-fg)' }}; display: inline-flex; align-items: center; justify-content: center;">
                            <span class="ms" style="font-size: 20px;">{{ $meeting->status === 'live' ? 'videocam' : 'schedule' }}</span>
                        </span>
                        <div style="flex: 1; min-width: 0; display: flex; flex-direction: column;">
                            <span style="font-size: 15px; font-weight: 600; color: var(--ula-text-primary); overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                                {{ $meeting->title }}
                            </span>
                            <span style="font-size: 13px; color: var(--ula-text-secondary);">
                                {{ $meeting->room->name ?? ($meeting->project->name ?? __('قاعة عامة')) }}
                            </span>
                        </div>
                        <span style="font-family: var(--ula-font-mono); font-size: 12px; color: var(--ula-text-secondary); direction: ltr; unicode-bidi: isolate;">
                            {{ $meeting->scheduled_at ? $meeting->scheduled_at->format('H:i') : '10:00' }}
                        </span>
                        @if($meeting->status === 'live')
                            <span style="height: 26px; padding: 0 10px; border-radius: var(--ula-radius-pill); background: var(--ula-tone-palm-bg); color: var(--ula-tone-palm-fg); font-size: 12px; font-weight: 500; display: inline-flex; align-items: center; gap: 6px; white-space: nowrap;">
                                <span style="width: 6px; height: 6px; border-radius: var(--ula-radius-pill); background: var(--ula-palm-700);"></span>
                                {{ __('مباشر الآن') }}
                            </span>
                        @else
                            <span style="height: 26px; padding: 0 10px; border-radius: var(--ula-radius-pill); background: var(--ula-tone-gold-bg); color: var(--ula-tone-gold-fg); font-size: 12px; font-weight: 500; display: inline-flex; align-items: center; gap: 6px; white-space: nowrap;">
                                <span style="width: 6px; height: 6px; border-radius: var(--ula-radius-pill); background: var(--ula-gold-500);"></span>
                                {{ __('مجدول') }}
                            </span>
                        @endif
                    </div>
                @empty
                    <div style="display: flex; align-items: center; gap: 12px; padding: 12px 14px; border-radius: 14px; background: var(--ula-surface-page); border: var(--ula-border-width-hairline) solid var(--ula-border-subtle);">
                        <span style="width: 36px; height: 36px; border-radius: 10px; background: var(--ula-tone-palm-bg); color: var(--ula-tone-palm-fg); display: inline-flex; align-items: center; justify-content: center;">
                            <span class="ms" style="font-size: 20px;">videocam</span>
                        </span>
                        <div style="flex: 1; min-width: 0; display: flex; flex-direction: column;">
                            <span style="font-size: 15px; font-weight: 600; color: var(--ula-text-primary);">مراجعة الربع الثالث</span>
                            <span style="font-size: 13px; color: var(--ula-text-secondary);">قاعة النخيل</span>
                        </div>
                        <span style="font-family: var(--ula-font-mono); font-size: 12px; color: var(--ula-text-secondary); direction: ltr; unicode-bidi: isolate;">10:00</span>
                        <span style="height: 26px; padding: 0 10px; border-radius: var(--ula-radius-pill); background: var(--ula-tone-palm-bg); color: var(--ula-tone-palm-fg); font-size: 12px; font-weight: 500; display: inline-flex; align-items: center; gap: 6px; white-space: nowrap;">
                            <span style="width: 6px; height: 6px; border-radius: var(--ula-radius-pill); background: var(--ula-palm-700);"></span>
                            {{ __('مباشر الآن') }}
                        </span>
                    </div>

                    <div style="display: flex; align-items: center; gap: 12px; padding: 12px 14px; border-radius: 14px; background: var(--ula-surface-page); border: var(--ula-border-width-hairline) solid var(--ula-border-subtle);">
                        <span style="width: 36px; height: 36px; border-radius: 10px; background: var(--ula-tone-gold-bg); color: var(--ula-tone-gold-fg); display: inline-flex; align-items: center; justify-content: center;">
                            <span class="ms" style="font-size: 20px;">schedule</span>
                        </span>
                        <div style="flex: 1; min-width: 0; display: flex; flex-direction: column;">
                            <span style="font-size: 15px; font-weight: 600; color: var(--ula-text-primary);">مزامنة فريق التصميم</span>
                            <span style="font-size: 13px; color: var(--ula-text-secondary);">الغرفة العامة</span>
                        </div>
                        <span style="font-family: var(--ula-font-mono); font-size: 12px; color: var(--ula-text-secondary); direction: ltr; unicode-bidi: isolate;">13:30</span>
                        <span style="height: 26px; padding: 0 10px; border-radius: var(--ula-radius-pill); background: var(--ula-tone-gold-bg); color: var(--ula-tone-gold-fg); font-size: 12px; font-weight: 500; display: inline-flex; align-items: center; gap: 6px; white-space: nowrap;">
                            <span style="width: 6px; height: 6px; border-radius: var(--ula-radius-pill); background: var(--ula-gold-500);"></span>
                            {{ __('مجدول') }}
                        </span>
                    </div>
                @endforelse
            </div>
        </div>

        <!-- Column 2: Quick Actions + Utilization -->
        <div style="display: flex; flex-direction: column; gap: 16px;">
            <!-- Quick Actions Card -->
            <div style="border-radius: 20px; border: var(--ula-border-width-hairline) solid var(--ula-border-subtle); background: var(--ula-surface-card); padding: 20px; box-shadow: var(--ula-shadow-xs); display: flex; flex-direction: column; gap: 16px; min-width: 0;">
                <div style="display: flex; align-items: center; gap: 10px;">
                    <span style="color: var(--ula-accent-hover); display: inline-flex;"><span class="ms" style="font-size: 20px;">bolt</span></span>
                    <span style="font-size: 18px; font-weight: 600; color: var(--ula-text-primary);">{{ __('إجراءات سريعة') }}</span>
                </div>

                <div style="display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 12px;">
                    <button type="button" onclick="openInviteModal()" style="padding: 16px; border-radius: 16px; border: var(--ula-border-width-hairline) solid var(--ula-border-subtle); background: var(--ula-surface-page); display: flex; flex-direction: column; align-items: flex-start; gap: 12px; cursor: pointer; font-family: var(--ula-font-ar); text-align: start;">
                        <span style="width: 40px; height: 40px; border-radius: 12px; background: var(--ula-tone-gold-bg); color: var(--ula-tone-gold-fg); display: inline-flex; align-items: center; justify-content: center;">
                            <span class="ms" style="font-size: 24px;">person_add</span>
                        </span>
                        <span style="font-size: 15px; font-weight: 600; color: var(--ula-text-primary);">{{ __('دعوة عضو') }}</span>
                    </button>

                    <button type="button" onclick="switchAdminTab('rooms')" style="padding: 16px; border-radius: 16px; border: var(--ula-border-width-hairline) solid var(--ula-border-subtle); background: var(--ula-surface-page); display: flex; flex-direction: column; align-items: flex-start; gap: 12px; cursor: pointer; font-family: var(--ula-font-ar); text-align: start;">
                        <span style="width: 40px; height: 40px; border-radius: 12px; background: var(--ula-tone-gold-bg); color: var(--ula-tone-gold-fg); display: inline-flex; align-items: center; justify-content: center;">
                            <span class="ms" style="font-size: 24px;">meeting_room</span>
                        </span>
                        <span style="font-size: 15px; font-weight: 600; color: var(--ula-text-primary);">{{ __('إدارة الغرف') }}</span>
                    </button>

                    <button type="button" onclick="openNewTaskModal()" style="padding: 16px; border-radius: 16px; border: var(--ula-border-width-hairline) solid var(--ula-border-subtle); background: var(--ula-surface-page); display: flex; flex-direction: column; align-items: flex-start; gap: 12px; cursor: pointer; font-family: var(--ula-font-ar); text-align: start;">
                        <span style="width: 40px; height: 40px; border-radius: 12px; background: var(--ula-tone-gold-bg); color: var(--ula-tone-gold-fg); display: inline-flex; align-items: center; justify-content: center;">
                            <span class="ms" style="font-size: 24px;">add_task</span>
                        </span>
                        <span style="font-size: 15px; font-weight: 600; color: var(--ula-text-primary);">{{ __('مهمة جديدة') }}</span>
                    </button>

                    <button type="button" onclick="openScheduleMeetingModal('general')" style="padding: 16px; border-radius: 16px; border: var(--ula-border-width-hairline) solid var(--ula-border-subtle); background: var(--ula-surface-page); display: flex; flex-direction: column; align-items: flex-start; gap: 12px; cursor: pointer; font-family: var(--ula-font-ar); text-align: start;">
                        <span style="width: 40px; height: 40px; border-radius: 12px; background: var(--ula-tone-gold-bg); color: var(--ula-tone-gold-fg); display: inline-flex; align-items: center; justify-content: center;">
                            <span class="ms" style="font-size: 24px;">event</span>
                        </span>
                        <span style="font-size: 15px; font-weight: 600; color: var(--ula-text-primary);">{{ __('جدولة اجتماع') }}</span>
                    </button>
                </div>
            </div>

            <!-- Workspace Utilization Card with SVG Donut -->
            @php
                $totalRooms = max(1, $rooms->count());
                $occupancyPercent = round(($openRooms / $totalRooms) * 100);
                $closedRooms = $totalRooms - $openRooms;
                $circumference = 314;
                $dashoffset = $circumference - ($occupancyPercent / 100 * $circumference);
            @endphp
            <div style="border-radius: 20px; border: var(--ula-border-width-hairline) solid var(--ula-border-subtle); background: var(--ula-surface-card); padding: 20px; box-shadow: var(--ula-shadow-xs); display: flex; flex-direction: column; gap: 16px; min-width: 0;">
                <div style="display: flex; align-items: center; gap: 10px;">
                    <span style="color: var(--ula-accent-hover); display: inline-flex;"><span class="ms" style="font-size: 20px;">donut_large</span></span>
                    <span style="font-size: 18px; font-weight: 600; color: var(--ula-text-primary);">{{ __('استخدام مساحة العمل') }}</span>
                </div>

                <div style="display: flex; align-items: center; gap: 24px;">
                    <div style="position: relative; width: 120px; height: 120px; flex-shrink: 0;">
                        <svg width="120" height="120" viewBox="0 0 120 120" style="transform: rotate(-90deg);">
                            <circle cx="60" cy="60" r="50" fill="none" stroke="var(--ula-border-subtle)" stroke-width="14"></circle>
                            <circle cx="60" cy="60" r="50" fill="none" stroke="var(--ula-accent-default)" stroke-width="14" stroke-linecap="round" stroke-dasharray="314" stroke-dashoffset="{{ $dashoffset }}"></circle>
                        </svg>
                        <div style="position: absolute; inset: 0; display: flex; flex-direction: column; align-items: center; justify-content: center;">
                            <span style="font-size: 24px; font-weight: 600; direction: ltr; font-family: var(--ula-font-mono);">{{ $occupancyPercent }}%</span>
                            <span style="font-size: 11px; color: var(--ula-text-secondary);">{{ __('قيد الاستخدام') }}</span>
                        </div>
                    </div>

                    <div style="flex: 1; display: flex; flex-direction: column; gap: 10px; font-size: 14px;">
                        <div style="display: flex; align-items: center; gap: 8px;">
                            <span style="width: 10px; height: 10px; border-radius: 3px; background: var(--ula-accent-default);"></span>
                            <span style="flex: 1; color: var(--ula-text-body);">{{ __('غرف مفتوحة') }}</span>
                            <span style="font-family: var(--ula-font-mono); font-size: 13px; color: var(--ula-text-primary); direction: ltr; unicode-bidi: isolate;">{{ $openRooms }}</span>
                        </div>
                        <div style="display: flex; align-items: center; gap: 8px;">
                            <span style="width: 10px; height: 10px; border-radius: 3px; background: var(--ula-highlight-default);"></span>
                            <span style="flex: 1; color: var(--ula-text-body);">{{ __('غرف مقفلة') }}</span>
                            <span style="font-family: var(--ula-font-mono); font-size: 13px; color: var(--ula-text-primary); direction: ltr; unicode-bidi: isolate;">{{ $closedRooms }}</span>
                        </div>
                        <div style="display: flex; align-items: center; gap: 8px;">
                            <span style="width: 10px; height: 10px; border-radius: 3px; background: var(--ula-stone-300);"></span>
                            <span style="flex: 1; color: var(--ula-text-body);">{{ __('نسبة الشغور') }}</span>
                            <span style="font-family: var(--ula-font-mono); font-size: 13px; color: var(--ula-text-primary); direction: ltr; unicode-bidi: isolate;">{{ 100 - $occupancyPercent }}%</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>
