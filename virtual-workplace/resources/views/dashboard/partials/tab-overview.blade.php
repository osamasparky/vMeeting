<div id="tab-overview" class="tab-view active">

    <!-- ── 1. Welcome Hero Banner (Matches 06-Dashboard-Overview) ── -->
    <div style="display: grid; grid-template-columns: minmax(0, 1.2fr) minmax(0, 1fr); border-radius: 20px; border: var(--ula-border-width-hairline) solid var(--ula-border-subtle); background: var(--ula-surface-card); overflow: hidden; box-shadow: var(--ula-shadow-xs); margin-bottom: 24px;">
        <div style="padding: 28px; display: flex; flex-direction: column; gap: 18px;">
            @php
                $hour = (int) now()->format('H');
                $isEvening = $hour >= 12;
            @endphp
            <div style="display: flex; flex-direction: column; gap: 2px;">
                <h1 style="font-size: 30px; font-weight: 600; line-height: 1.3; color: var(--ula-text-primary);" id="nx-hero-dynamic-greeting">
                    @if($isEvening)
                        {{ __('مساء الخير، :name', ['name' => explode(' ', $user->name)[0]]) }}
                    @else
                        {{ __('صباح الخير، :name', ['name' => explode(' ', $user->name)[0]]) }}
                    @endif
                </h1>
                <span style="font-family: var(--ula-font-en); font-size: 18px; font-weight: 300; color: var(--ula-text-secondary);">Ready to Collaborate</span>
            </div>

            <div style="display: flex; flex-direction: column; gap: 4px; padding-inline-start: 14px; border-inline-start: 2px solid var(--ula-highlight-default);">
                <span style="font-size: 21px; font-weight: 600; line-height: 1.4; color: var(--ula-palm-800);">المساحات الأفضل تصنع فرقاً أعظم.</span>
                <span style="font-family: var(--ula-font-en); font-size: 14px; color: var(--ula-text-secondary);">Better spaces carve greater teams.</span>
            </div>

            <div style="display: flex; gap: 10px; flex-wrap: wrap;">
                <a href="{{ route('office') }}" class="tactile-btn btn-primary" style="height: 44px; padding: 0 20px; border-radius: 14px; background: var(--ula-accent-default); color: var(--ula-accent-fg); font-family: var(--ula-font-ar); font-size: 15px; font-weight: 600; display: inline-flex; align-items: center; justify-content: center; gap: 8px; text-decoration: none;">
                    <span class="ms" style="font-size: 20px;">login</span>
                    <span>{{ __('ادخل مساحة العمل') }}</span>
                </a>
                
                <button type="button" onclick="openScheduleMeetingModal('general')" style="height: 44px; padding: 0 20px; border-radius: 14px; border: var(--ula-border-width-hairline) solid var(--ula-border-strong); background: var(--ula-surface-card); color: var(--ula-palm-800); font-family: var(--ula-font-ar); font-size: 15px; font-weight: 600; display: inline-flex; align-items: center; justify-content: center; gap: 8px; cursor: pointer; white-space: nowrap;">
                    <span class="ms" style="font-size: 20px;">event</span>
                    <span>{{ __('جدولة اجتماع') }}</span>
                </button>

                @if($membership->hasPermission('maps.manage'))
                    <a href="{{ route('editor') }}" style="height: 44px; padding: 0 20px; border-radius: 14px; border: var(--ula-border-width-hairline) solid transparent; background: transparent; color: var(--ula-accent-hover); font-family: var(--ula-font-ar); font-size: 15px; font-weight: 600; display: inline-flex; align-items: center; justify-content: center; gap: 8px; cursor: pointer; text-decoration: none;">
                        <span class="ms" style="font-size: 20px;">architecture</span>
                        <span>{{ __('محرر الخريطة') }}</span>
                    </a>
                @endif
            </div>
        </div>

        <div style="min-height: 220px; background: repeating-linear-gradient(135deg, var(--ula-sand-400) 0 12px, var(--ula-sand-500) 12px 24px); display: flex; align-items: flex-end; justify-content: center; padding: 14px;">
            <span style="font-family: var(--ula-font-mono); font-size: 11px; color: var(--ula-text-primary); background: rgba(251,248,242,0.72); padding: 6px 10px; border-radius: var(--ula-radius-pill); direction: ltr; unicode-bidi: isolate;">welcome-photo.jpg</span>
        </div>
    </div>

    <!-- Hidden clock ID hook to preserve script updates -->
    <span id="nx-hero-live-clock" style="display: none;"></span>

    <!-- ── 2. Stat Cards Grid (4 Columns · Weight 300 Numbers) ── -->
    @php
        $todayMeetings = $upcomingMeetings->filter(fn($m) => $m->scheduled_at && $m->scheduled_at->isToday());
        $openRooms = $rooms->where('door_status', 'open')->count();
        $pendingGuests = $guestInvitations->where('status', 'pending')->count() ?? 0;
        $activeMembersCount = $stats['members'] ?? 1;
    @endphp

    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(min(100%, 210px), 1fr)); gap: 16px; margin-bottom: 24px;">
        <!-- 1. Active Presence -->
        <div style="padding: 18px; border-radius: 20px; border: var(--ula-border-width-hairline) solid var(--ula-border-subtle); background: var(--ula-surface-card); box-shadow: var(--ula-shadow-xs); display: flex; flex-direction: column; gap: 12px;">
            <div style="display: flex; align-items: flex-start; justify-content: space-between; gap: 12px;">
                <span style="font-size: 15px; font-weight: 500; color: var(--ula-text-secondary);">{{ __('متصلون الآن') }}</span>
                <span style="width: 32px; height: 32px; flex-shrink: 0; border-radius: var(--ula-radius-pill); background: var(--ula-tone-palm-bg); color: var(--ula-tone-palm-fg); display: inline-flex; align-items: center; justify-content: center;">
                    <span class="ms" style="font-size: 18px;">person</span>
                </span>
            </div>
            <div style="display: flex; align-items: baseline; gap: 8px; flex-wrap: wrap;">
                <span style="font-size: 34px; font-weight: 300; line-height: 1.1; direction: ltr; unicode-bidi: isolate; font-family: var(--ula-font-mono);">
                    {{ $activeMembersCount }}
                </span>
                <span style="font-size: 12px; color: var(--ula-text-muted);">{{ __('من فريقك متصلون الآن') }}</span>
            </div>
        </div>

        <!-- 2. Today's Meetings -->
        <div style="padding: 18px; border-radius: 20px; border: var(--ula-border-width-hairline) solid var(--ula-border-subtle); background: var(--ula-surface-card); box-shadow: var(--ula-shadow-xs); display: flex; flex-direction: column; gap: 12px;">
            <div style="display: flex; align-items: flex-start; justify-content: space-between; gap: 12px;">
                <span style="font-size: 15px; font-weight: 500; color: var(--ula-text-secondary);">{{ __('اجتماعات اليوم') }}</span>
                <span style="width: 32px; height: 32px; flex-shrink: 0; border-radius: var(--ula-radius-pill); background: var(--ula-tone-gold-bg); color: var(--ula-tone-gold-fg); display: inline-flex; align-items: center; justify-content: center;">
                    <span class="ms" style="font-size: 18px;">event</span>
                </span>
            </div>
            <div style="display: flex; align-items: baseline; gap: 8px; flex-wrap: wrap;">
                <span style="font-size: 34px; font-weight: 300; line-height: 1.1; direction: ltr; unicode-bidi: isolate; font-family: var(--ula-font-mono);">
                    {{ $todayMeetings->count() }}
                </span>
                <span style="font-size: 12px; color: var(--ula-text-muted);">{{ __('مجدولة لهذا اليوم') }}</span>
            </div>
        </div>

        <!-- 3. Active Workspaces -->
        <div style="padding: 18px; border-radius: 20px; border: var(--ula-border-width-hairline) solid var(--ula-border-subtle); background: var(--ula-surface-card); box-shadow: var(--ula-shadow-xs); display: flex; flex-direction: column; gap: 12px;">
            <div style="display: flex; align-items: flex-start; justify-content: space-between; gap: 12px;">
                <span style="font-size: 15px; font-weight: 500; color: var(--ula-text-secondary);">{{ __('مساحات نشطة') }}</span>
                <span style="width: 32px; height: 32px; flex-shrink: 0; border-radius: var(--ula-radius-pill); background: var(--ula-tone-palm-bg); color: var(--ula-tone-palm-fg); display: inline-flex; align-items: center; justify-content: center;">
                    <span class="ms" style="font-size: 18px;">meeting_room</span>
                </span>
            </div>
            <div style="display: flex; align-items: baseline; gap: 8px; flex-wrap: wrap;">
                <span style="font-size: 34px; font-weight: 300; line-height: 1.1; direction: ltr; unicode-bidi: isolate; font-family: var(--ula-font-mono);">
                    {{ $openRooms }}
                </span>
                <span style="font-size: 12px; color: var(--ula-text-muted);">{{ __('مفتوحة للعمل') }}</span>
            </div>
        </div>

        <!-- 4. Pending Invitations -->
        <div style="padding: 18px; border-radius: 20px; border: var(--ula-border-width-hairline) solid var(--ula-border-subtle); background: var(--ula-surface-card); box-shadow: var(--ula-shadow-xs); display: flex; flex-direction: column; gap: 12px;">
            <div style="display: flex; align-items: flex-start; justify-content: space-between; gap: 12px;">
                <span style="font-size: 15px; font-weight: 500; color: var(--ula-text-secondary);">{{ __('دعوات معلقة') }}</span>
                <span style="width: 32px; height: 32px; flex-shrink: 0; border-radius: var(--ula-radius-pill); background: var(--ula-tone-terracotta-bg); color: var(--ula-tone-terracotta-fg); display: inline-flex; align-items: center; justify-content: center;">
                    <span class="ms" style="font-size: 18px;">mail</span>
                </span>
            </div>
            <div style="display: flex; align-items: baseline; gap: 8px; flex-wrap: wrap;">
                <span style="font-size: 34px; font-weight: 300; line-height: 1.1; direction: ltr; unicode-bidi: isolate; font-family: var(--ula-font-mono);">
                    {{ $pendingGuests }}
                </span>
                <span style="font-size: 12px; color: var(--ula-text-muted);">{{ __('بانتظار الانضمام') }}</span>
            </div>
        </div>
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

                    <button type="button" onclick="openCreateTaskModal()" style="padding: 16px; border-radius: 16px; border: var(--ula-border-width-hairline) solid var(--ula-border-subtle); background: var(--ula-surface-page); display: flex; flex-direction: column; align-items: flex-start; gap: 12px; cursor: pointer; font-family: var(--ula-font-ar); text-align: start;">
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
