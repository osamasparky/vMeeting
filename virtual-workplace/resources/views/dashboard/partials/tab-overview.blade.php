        <div id="tab-overview" class="tab-view active">

            <!-- Hero Section: Welcome & 3D Isometric Workspace Preview -->
            <div style="display: grid; grid-template-columns: 1.35fr 1fr; gap: 20px; margin-bottom: 24px;">
                <!-- Left: Welcome Banner Card -->
                <div class="card hero-welcome-card" style="background: linear-gradient(135deg, #DCEAD8 0%, #EDF5EA 40%, #FFFDF6 100%); border: 1px solid #C8D8BE; display: flex; align-items: center; justify-content: space-between; overflow: hidden; padding: 28px; box-shadow: 0 16px 36px rgba(32, 64, 42, 0.09), inset 0 1px 0 rgba(255, 255, 255, 0.95);">
                    <div style="max-width: 60%; z-index: 2;">
                        <div style="display: inline-flex; align-items: center; gap: 6px; background: rgba(36, 92, 58, 0.12); border: 1px solid rgba(36, 92, 58, 0.25); padding: 4px 12px; border-radius: var(--radius-full); font-size: 11px; font-weight: 800; color: var(--brand-forest); margin-bottom: 12px; box-shadow: 0 2px 4px rgba(36, 92, 58, 0.06);">
                            <span>🌿</span> {{ __('Ready to Collaborate') }}
                        </div>
                        <h2 style="font-size: 24px; font-weight: 900; color: var(--text-primary); line-height: 1.25; margin-bottom: 8px;">
                            {{ __('Good morning, :name!', ['name' => explode(' ', $user->name)[0]]) }}
                        </h2>
                        <p style="font-size: 13px; color: var(--text-secondary); line-height: 1.5; margin-bottom: 20px; font-weight: 600;">
                            {{ __('Your workspace is ready. Let\'s make today productive!') }}
                        </p>
                        <div style="display: flex; gap: 12px; align-items: center;">
                            <a href="{{ route('office') }}" class="tactile-btn btn-primary" style="font-size: 13px; padding: 11px 22px;">
                                <span>{{ __('Enter Workspace') }}</span>
                                <span>{{ app()->getLocale() === 'ar' ? '←' : '→' }}</span>
                            </a>
                        </div>
                    </div>
                    <div style="width: 160px; height: 160px; flex-shrink: 0; position: relative; display: flex; align-items: center; justify-content: center; cursor: pointer;" onclick="switchAdminTab('profile')" title="{{ __('View Profile') }}">
                        @if($user->avatar_url)
                            <img src="{{ $user->avatar_url }}" alt="{{ $user->name }}" style="width: 140px; height: 140px; object-fit: cover; border-radius: 50%; box-shadow: 0 12px 30px rgba(36, 92, 58, 0.22), inset 0 2px 4px rgba(255,255,255,0.8); border: 4px solid #FFFDF6; transition: transform 0.3s ease;" onmouseover="this.style.transform='scale(1.05)'" onmouseout="this.style.transform='scale(1)'">
                        @else
                            <div style="width: 140px; height: 140px; border-radius: 50%; background: var(--accent-gradient); color: #FFFDF6; display: flex; align-items: center; justify-content: center; font-size: 44px; font-weight: 900; box-shadow: 0 12px 30px rgba(36, 92, 58, 0.25); border: 4px solid #FFFDF6; transition: transform 0.3s ease;" onmouseover="this.style.transform='scale(1.05)'" onmouseout="this.style.transform='scale(1)'">
                                {{ strtoupper(substr($user->name, 0, 2)) }}
                            </div>
                        @endif
                        <div style="position: absolute; bottom: 14px; inset-inline-end: 18px; width: 22px; height: 22px; border-radius: 50%; background: #4F9B5F; border: 3px solid #FFFDF6; box-shadow: 0 0 10px rgba(79, 155, 95, 0.8);" title="{{ __('Online') }}"></div>
                    </div>
                </div>

                <!-- Right: "Your Workspace" 3D Isometric Preview Card -->
                <div class="card" style="padding: 20px; display: flex; flex-direction: column; justify-content: space-between; background: linear-gradient(145deg, #FFFDF6 0%, #F5F9F1 100%); border: 1px solid #D2E0CC; box-shadow: 0 16px 36px rgba(32, 64, 42, 0.08), inset 0 1px 0 rgba(255, 255, 255, 0.95); overflow: hidden;">
                    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 12px;">
                        <div>
                            <div style="font-size: 15px; font-weight: 900; color: var(--text-primary);">{{ __('Your Workspace') }}</div>
                            <div style="font-size: 11px; color: var(--brand-sage); font-weight: 700;">🟢 {{ $stats['members'] }} {{ __('Members Online') }} • {{ $rooms->count() }} {{ __('Rooms') }}</div>
                        </div>
                        @if($membership->hasPermission('maps.manage'))
                        <a href="{{ route('editor') }}" class="tactile-btn btn-secondary" style="padding: 6px 12px; font-size: 11px;">
                            <span>🛠️</span> {{ __('Customize Space') }}
                        </a>
                        @endif
                    </div>
                    <div style="width: 100%; height: 130px; border-radius: 14px; overflow: hidden; position: relative; box-shadow: var(--shadow-inset-3d); border: 1px solid var(--border-color);">
                        <img src="/images/isometric_office_preview.jpg" alt="3D Office Preview" style="width: 100%; height: 100%; object-fit: cover; transition: transform 0.4s ease;" onmouseover="this.style.transform='scale(1.05)'" onmouseout="this.style.transform='scale(1)'">
                        <div style="position: absolute; bottom: 8px; inset-inline-start: 8px; background: rgba(255, 253, 246, 0.92); backdrop-filter: blur(4px); padding: 4px 10px; border-radius: 8px; font-size: 10px; font-weight: 800; color: var(--brand-forest); border: 1px solid var(--border-color);">
                            📍 {{ $organization->name }} {{ __('Headquarters') }}
                        </div>
                    </div>
                </div>
            </div>

            <!-- 5 Top KPI Cards -->
            <div class="kpi-grid">
                <!-- 1. Active Now -->
                <div class="kpi-card">
                    <div class="icon-box-3d green">
                        👥
                    </div>
                    <div class="kpi-info">
                        <div class="kpi-title">{{ __('Active Now') }}</div>
                        <div class="kpi-value">{{ $stats['members'] }}</div>
                        <div class="kpi-sub">↑ {{ $stats['members'] }} {{ __('this week') }}</div>
                    </div>
                </div>

                <!-- 2. Rooms -->
                <div class="kpi-card">
                    <div class="icon-box-3d">
                        🏢
                    </div>
                    <div class="kpi-info">
                        <div class="kpi-title">{{ __('Rooms') }}</div>
                        <div class="kpi-value">{{ $rooms->count() }}</div>
                        <div class="kpi-sub">{{ $rooms->where('door_status', 'open')->count() }} {{ __('Open') }}</div>
                    </div>
                </div>

                <!-- 3. Meetings Today -->
                @php
                    $todayMeetingsCount = $upcomingMeetings->filter(fn($m) => $m->scheduled_at && $m->scheduled_at->isToday())->count();
                    $nextMeeting = $upcomingMeetings->filter(fn($m) => $m->scheduled_at && $m->scheduled_at->isAfter(now()))->first();
                @endphp
                <div class="kpi-card">
                    <div class="icon-box-3d">
                        📅
                    </div>
                    <div class="kpi-info">
                        <div class="kpi-title">{{ __('Meetings Today') }}</div>
                        <div class="kpi-value">{{ $todayMeetingsCount }}</div>
                        <div class="kpi-sub" style="color: var(--text-secondary);">
                            {{ $nextMeeting ? __('Next: :time', ['time' => $nextMeeting->scheduled_at->format('h:i A')]) : __('No more meetings today') }}
                        </div>
                    </div>
                </div>

                <!-- 4. Tasks -->
                @php
                    $isTaskManager = ($membership->role?->slug === 'company_admin' || $membership->hasPermission('tasks.assign') || $membership->hasPermission('tasks.delete'));
                    $relevantPendingTasks = $isTaskManager ? $tasks->where('status', '!=', 'done')->count() : $myTasks->where('status', '!=', 'done')->count();
                    $relevantDueToday = $isTaskManager ? $tasks->filter(fn($t) => $t->due_date && $t->due_date->isToday() && $t->status !== 'done')->count() : $myTasks->filter(fn($t) => $t->due_date && $t->due_date->isToday() && $t->status !== 'done')->count();
                @endphp
                <div class="kpi-card" onclick="switchAdminTab('{{ $isTaskManager ? 'all-tasks' : 'my-tasks' }}')" style="cursor: pointer;">
                    <div class="icon-box-3d">
                        📋
                    </div>
                    <div class="kpi-info">
                        <div class="kpi-title">{{ $isTaskManager ? __('Workspace Tasks') : __('My Tasks') }}</div>
                        <div class="kpi-value">{{ $relevantPendingTasks }}</div>
                        <div class="kpi-sub" style="color: {{ $relevantDueToday > 0 ? 'var(--status-warning)' : 'var(--text-muted)' }};">
                            {{ $relevantDueToday }} {{ __('due today') }}
                        </div>
                    </div>
                </div>

                <!-- 5. Unread Messages -->
                <div class="kpi-card">
                    <div class="icon-box-3d">
                        💬
                    </div>
                    <div class="kpi-info">
                        <div class="kpi-title">{{ __('Unread Messages') }}</div>
                        <div class="kpi-value">0</div>
                        <div class="kpi-sub" style="color: var(--text-secondary);">{{ __('All caught up') }}</div>
                    </div>
                </div>
            </div>

            <!-- Analytics Grid: Workspace Activity + Upcoming Meetings + Workspace Overview -->
            <div style="display: grid; grid-template-columns: 1.4fr 1.1fr 1fr; gap: 20px; margin-bottom: 24px;">
                <!-- 1. Workspace Activity Curve Chart -->
                <div class="card" style="margin-bottom: 0;">
                    <div class="card-header">
                        <div>
                            <h3 class="card-title">{{ __('Workspace Activity') }}</h3>
                            <div style="font-size: 11px; color: var(--text-muted);">{{ __('Weekly team engagement & presence') }}</div>
                        </div>
                        <select style="background: var(--bg-surface-subtle); border: 1px solid var(--border-color); border-radius: 8px; font-size: 11px; font-weight: 700; padding: 4px 10px; color: var(--text-secondary); outline: none;">
                            <option>{{ __('This Week') }}</option>
                            <option>{{ __('Last Week') }}</option>
                            <option>{{ __('This Month') }}</option>
                        </select>
                    </div>

                    <!-- Clean Responsive SVG Area Chart -->
                    <div style="position: relative; width: 100%; height: 170px; margin-top: 10px;">
                        <svg viewBox="0 0 400 150" style="width: 100%; height: 100%; overflow: visible;">
                            <defs>
                                <linearGradient id="activityGrad" x1="0%" y1="0%" x2="0%" y2="100%">
                                    <stop offset="0%" stop-color="#3F7D4F" stop-opacity="0.35"/>
                                    <stop offset="100%" stop-color="#3F7D4F" stop-opacity="0.0"/>
                                </linearGradient>
                            </defs>
                            <!-- Grid Lines -->
                            <line x1="0" y1="30" x2="400" y2="30" stroke="rgba(213, 222, 208, 0.4)" stroke-dasharray="4"/>
                            <line x1="0" y1="75" x2="400" y2="75" stroke="rgba(213, 222, 208, 0.4)" stroke-dasharray="4"/>
                            <line x1="0" y1="120" x2="400" y2="120" stroke="rgba(213, 222, 208, 0.4)" stroke-dasharray="4"/>

                            <!-- Area Curve -->
                            <path d="M 0,110 Q 60,40 120,60 T 240,40 T 320,80 T 400,25 L 400,140 L 0,140 Z" fill="url(#activityGrad)"/>
                            <!-- Stroke Curve -->
                            <path d="M 0,110 Q 60,40 120,60 T 240,40 T 320,80 T 400,25" fill="none" stroke="#245C3A" stroke-width="3.5" stroke-linecap="round"/>

                            <!-- Data Points -->
                            <circle cx="0" cy="110" r="4" fill="#245C3A" stroke="#FFFDF6" stroke-width="2"/>
                            <circle cx="66" cy="48" r="4" fill="#245C3A" stroke="#FFFDF6" stroke-width="2"/>
                            <circle cx="133" cy="62" r="4" fill="#245C3A" stroke="#FFFDF6" stroke-width="2"/>
                            <circle cx="200" cy="50" r="4" fill="#245C3A" stroke="#FFFDF6" stroke-width="2"/>
                            <circle cx="266" cy="45" r="4" fill="#245C3A" stroke="#FFFDF6" stroke-width="2"/>
                            <circle cx="333" cy="78" r="4" fill="#245C3A" stroke="#FFFDF6" stroke-width="2"/>
                            <circle cx="400" cy="25" r="5" fill="#4F9B5F" stroke="#FFFDF6" stroke-width="2"/>
                        </svg>
                        <div style="display: flex; justify-content: space-between; font-size: 10px; font-weight: 700; color: var(--text-muted); margin-top: 6px;">
                            <span>{{ __('Mon') }}</span><span>{{ __('Tue') }}</span><span>{{ __('Wed') }}</span><span>{{ __('Thu') }}</span><span>{{ __('Fri') }}</span><span>{{ __('Sat') }}</span><span>{{ __('Sun') }}</span>
                        </div>
                    </div>
                </div>

                <!-- 2. Upcoming Meetings (Dynamic Live Sessions & Schedule) -->
                <div class="card" style="margin-bottom: 0; display: flex; flex-direction: column; justify-content: space-between;">
                    <div>
                        <div class="card-header" style="margin-bottom: 14px;">
                            <div style="display: flex; align-items: center; gap: 8px;">
                                <h3 class="card-title">{{ __('Upcoming Meetings') }}</h3>
                                <span class="nav-badge-pill" style="background: rgba(79, 155, 95, 0.15); color: #4F9B5F;">{{ $upcomingMeetings->count() }}</span>
                            </div>
                            <button onclick="openScheduleMeetingModal()" class="tactile-btn btn-primary" style="padding: 4px 10px; font-size: 11px; text-decoration: none;">
                                + {{ __('Schedule') }}
                            </button>
                        </div>

                        <div style="display: flex; flex-direction: column; gap: 10px;">
                            @forelse($upcomingMeetings->take(4) as $m)
                                @php
                                    $isLive = $m->status === 'active';
                                    $schedTime = $m->scheduled_at ? $m->scheduled_at->format('h:i A') : __('Instant');
                                    $schedDate = $m->scheduled_at ? ($m->scheduled_at->isToday() ? __('Today') : ($m->scheduled_at->isTomorrow() ? __('Tomorrow') : $m->scheduled_at->format('M d'))) : '';
                                    $mParts = $m->participants->take(3);
                                    $moreParts = max(0, $m->participants->count() - 3);
                                @endphp
                                <div class="meeting-list-card" style="display: flex; align-items: center; justify-content: space-between; background: var(--bg-surface-subtle); padding: 10px 12px; border-radius: 14px; border: 1px solid {{ $isLive ? 'var(--brand-forest)' : 'var(--border-color)' }}; box-shadow: var(--shadow-soft-3d); transition: all 0.2s;" onmouseover="this.style.borderColor='var(--brand-forest)'" onmouseout="this.style.borderColor='{{ $isLive ? 'var(--brand-forest)' : 'var(--border-color)' }}'">
                                    <div style="display: flex; align-items: center; gap: 10px; min-width: 0;">
                                        <div style="width: 40px; height: 40px; border-radius: 12px; background: linear-gradient(145deg, #437E51 0%, #245A36 100%); border: 1px solid #1C482B; display: flex; align-items: center; justify-content: center; font-size: 16px; color: #FFFFFF !important; flex-shrink: 0; box-shadow: 0 4px 12px rgba(36, 92, 58, 0.32), inset 0 1px 1px rgba(255, 255, 255, 0.45); text-shadow: 0 1px 2px rgba(0,0,0,0.2);">
                                            {{ $m->project ? '📁' : '📅' }}
                                        </div>
                                        <div style="min-width: 0;">
                                            <div style="display: flex; align-items: center; gap: 6px; flex-wrap: wrap;">
                                                <div style="font-size: 13px; font-weight: 800; color: var(--text-primary); white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">{{ $m->title }}</div>
                                                @if($isLive)
                                                    <span class="nav-badge-pill" style="background: rgba(217, 107, 95, 0.2); color: #D96B5F; font-size: 9px; animation: pulse 1.5s infinite;">🔴 LIVE</span>
                                                @endif
                                            </div>
                                            <div style="font-size: 11px; color: var(--text-muted); display: flex; align-items: center; gap: 6px; margin-top: 2px;">
                                                <span>⏰ {{ $schedDate }} {{ $schedTime }}</span>
                                                <span>•</span>
                                                <span>🚪 {{ $m->room->name ?? __('Meeting Room') }}</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div style="display: flex; align-items: center; gap: 8px; flex-shrink: 0;">
                                        <div style="display: flex; align-items: center; margin-inline-end: 4px;">
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
                                        <a href="{{ route('office') }}" class="tactile-btn btn-secondary" style="padding: 5px 10px; font-size: 11px; text-decoration: none;" title="{{ __('Enter Meeting Room') }}">
                                            🚀
                                        </a>
                                    </div>
                                </div>
                            @empty
                                <div style="text-align: center; padding: 24px; color: var(--text-muted); font-size: 12px; background: var(--bg-surface-subtle); border-radius: 12px; border: 1px dashed var(--border-color);">
                                    <div style="font-size: 24px; margin-bottom: 6px;">📅</div>
                                    {{ __('No scheduled meetings upcoming.') }}
                                    <div style="margin-top: 8px;">
                                        <button onclick="openScheduleMeetingModal()" class="tactile-btn btn-primary" style="padding: 6px 14px; font-size: 11px;">
                                            + {{ __('Schedule First Meeting') }}
                                        </button>
                                    </div>
                                </div>
                            @endforelse
                        </div>
                    </div>
                    @if($upcomingMeetings->count() > 4)
                        <div style="text-align: center; margin-top: 10px;">
                            <a href="javascript:void(0)" onclick="switchAdminTab('meetings')" style="font-size: 11px; font-weight: 800; color: var(--brand-forest); text-decoration: none;">
                                {{ __('View all :count scheduled meetings →', ['count' => $upcomingMeetings->count()]) }}
                            </a>
                        </div>
                    @endif
                </div>

                <!-- 3. Workspace Overview Donut Chart -->
                <div class="card" style="margin-bottom: 0;">
                    <div class="card-header">
                        <h3 class="card-title">{{ __('Workspace Overview') }}</h3>
                    </div>
                    <div style="display: flex; flex-direction: column; align-items: center;">
                        <!-- SVG Donut -->
                        <div style="position: relative; width: 120px; height: 120px; margin-bottom: 12px;">
                            <svg viewBox="0 0 36 36" style="width: 100%; height: 100%; transform: rotate(-90deg);">
                                <circle cx="18" cy="18" r="14" fill="none" stroke="#E8EFE2" stroke-width="4.5"/>
                                <!-- Meetings 45% -->
                                <circle cx="18" cy="18" r="14" fill="none" stroke="#245C3A" stroke-width="4.5" stroke-dasharray="39.6 88" stroke-dashoffset="0"/>
                                <!-- Focus Time 25% -->
                                <circle cx="18" cy="18" r="14" fill="none" stroke="#3F7D4F" stroke-width="4.5" stroke-dasharray="22 88" stroke-dashoffset="-39.6"/>
                                <!-- Collaboration 15% -->
                                <circle cx="18" cy="18" r="14" fill="none" stroke="#719B73" stroke-width="4.5" stroke-dasharray="13.2 88" stroke-dashoffset="-61.6"/>
                                <!-- Learning 10% -->
                                <circle cx="18" cy="18" r="14" fill="none" stroke="#D6A23A" stroke-width="4.5" stroke-dasharray="8.8 88" stroke-dashoffset="-74.8"/>
                                <!-- Other 5% -->
                                <circle cx="18" cy="18" r="14" fill="none" stroke="#D96B5F" stroke-width="4.5" stroke-dasharray="4.4 88" stroke-dashoffset="-83.6"/>
                            </svg>
                            <div style="position: absolute; inset: 0; display: flex; flex-direction: column; align-items: center; justify-content: center;">
                                <span style="font-size: 16px; font-weight: 900; color: var(--text-primary);">100%</span>
                                <span style="font-size: 9px; font-weight: 700; color: var(--text-muted);">{{ __('Activity') }}</span>
                            </div>
                        </div>

                        <!-- Legend -->
                        <div style="width: 100%; display: grid; grid-template-columns: 1fr 1fr; gap: 6px; font-size: 11px; font-weight: 700;">
                            <div style="display: flex; align-items: center; gap: 6px;"><span style="width: 8px; height: 8px; border-radius: 50%; background: #245C3A;"></span> {{ __('Meetings') }} (45%)</div>
                            <div style="display: flex; align-items: center; gap: 6px;"><span style="width: 8px; height: 8px; border-radius: 50%; background: #3F7D4F;"></span> {{ __('Focus') }} (25%)</div>
                            <div style="display: flex; align-items: center; gap: 6px;"><span style="width: 8px; height: 8px; border-radius: 50%; background: #719B73;"></span> {{ __('Collaboration') }} (15%)</div>
                            <div style="display: flex; align-items: center; gap: 6px;"><span style="width: 8px; height: 8px; border-radius: 50%; background: #D6A23A;"></span> {{ __('Learning') }} (10%)</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Bottom Grid: Recently Accessed + My Tasks + Quick Actions -->
            <div style="display: grid; grid-template-columns: 1.1fr 1.3fr 1.2fr; gap: 20px;">
                <!-- 1. Recently Accessed Projects & Workspaces -->
                <div class="card" style="margin-bottom: 0;">
                    <div class="card-header">
                        <h3 class="card-title">{{ __('Recently Accessed') }}</h3>
                        <a href="javascript:void(0)" onclick="switchAdminTab('projects')" style="font-size: 11px; font-weight: 800; color: var(--brand-forest); text-decoration: none;">{{ __('View All') }}</a>
                    </div>
                    <div style="display: flex; flex-direction: column; gap: 10px;">
                        @forelse($projects->take(3) as $p)
                            @php
                                $canOpenHub = ($user->isSuperAdmin() || $membership->role?->slug === 'company_admin' || $membership->hasPermission('projects.manage') || $p->manager_id === $user->id || $p->owner_id === $user->id);
                            @endphp
                            @if($canOpenHub)
                                <a href="{{ route('projects.hub', $p->id) }}" style="display: flex; align-items: center; justify-content: space-between; background: var(--bg-surface-subtle); padding: 10px 12px; border-radius: 12px; border: 1px solid var(--border-color); text-decoration: none; transition: transform 0.2s;" onmouseover="this.style.transform='translateY(-2px)'" onmouseout="this.style.transform='translateY(0)'" title="{{ __('Click to open project dashboard & tasks') }}">
                                    <div style="display: flex; align-items: center; gap: 10px; min-width: 0;">
                                        <div style="width: 32px; height: 32px; border-radius: 8px; background: rgba(79, 155, 95, 0.15); color: var(--brand-forest); display: flex; align-items: center; justify-content: center; font-size: 14px; font-weight: 900; flex-shrink: 0;">📁</div>
                                        <div style="min-width: 0;">
                                            <div style="font-size: 12px; font-weight: 800; color: var(--text-primary); white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">{{ $p->name }}</div>
                                            <div style="font-size: 10px; color: var(--text-muted);">{{ $p->code ?? 'PRJ' }} • {{ $p->tasks_count ?? $p->tasks->count() }} {{ __('tasks') }}</div>
                                        </div>
                                    </div>
                                    <span class="nav-badge-pill" style="font-size: 10px; flex-shrink: 0;">{{ __($p->status) }}</span>
                                </a>
                            @else
                                <div style="display: flex; align-items: center; justify-content: space-between; background: var(--bg-surface-subtle); padding: 10px 12px; border-radius: 12px; border: 1px solid var(--border-color);">
                                    <div style="display: flex; align-items: center; gap: 10px; min-width: 0;">
                                        <div style="width: 32px; height: 32px; border-radius: 8px; background: rgba(79, 155, 95, 0.15); color: var(--brand-forest); display: flex; align-items: center; justify-content: center; font-size: 14px; font-weight: 900; flex-shrink: 0;">📁</div>
                                        <div style="min-width: 0;">
                                            <div style="font-size: 12px; font-weight: 800; color: var(--text-primary); white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">{{ $p->name }}</div>
                                            <div style="font-size: 10px; color: var(--text-muted);">{{ $p->code ?? 'PRJ' }} • {{ $p->tasks_count ?? $p->tasks->count() }} {{ __('tasks') }}</div>
                                        </div>
                                    </div>
                                    <span class="nav-badge-pill" style="font-size: 10px; flex-shrink: 0;">{{ __($p->status) }}</span>
                                </div>
                            @endif
                        @empty
                            <div style="text-align: center; padding: 24px; color: var(--text-muted); font-size: 12px; background: var(--bg-surface-subtle); border-radius: 12px; border: 1px dashed var(--border-color);">
                                <div style="font-size: 22px; margin-bottom: 4px;">📁</div>
                                {{ __('No projects created yet.') }}
                            </div>
                        @endforelse
                    </div>
                </div>

                <!-- 2. My Tasks Checklist (Real DB Data) -->
                <div class="card" style="margin-bottom: 0;">
                    <div class="card-header">
                        <h3 class="card-title">{{ __('My Tasks') }}</h3>
                        <a href="javascript:void(0)" onclick="switchAdminTab('my-tasks')" style="font-size: 11px; font-weight: 800; color: var(--brand-forest); text-decoration: none;">+ {{ __('New Task') }}</a>
                    </div>
                    <div style="display: flex; flex-direction: column; gap: 10px;">
                        @forelse($myTasks->take(4) as $t)
                            <div style="display: flex; align-items: center; justify-content: space-between; background: var(--bg-surface-subtle); padding: 10px 12px; border-radius: 12px; border: 1px solid var(--border-color);">
                                <div style="display: flex; align-items: center; gap: 10px; min-width: 0;">
                                    <input type="checkbox" {{ $t->status === 'done' ? 'checked' : '' }} onchange="toggleTaskDone('{{ $t->id }}', this.checked)" style="width: 16px; height: 16px; accent-color: var(--brand-forest); cursor: pointer;">
                                    <div style="min-width: 0;">
                                        <div style="font-size: 12px; font-weight: 800; color: var(--text-primary); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; {{ $t->status === 'done' ? 'text-decoration: line-through; opacity: 0.6;' : '' }}">
                                            {{ $t->title }}
                                        </div>
                                        <div style="font-size: 10px; color: var(--text-muted);">{{ $t->due_date ? $t->due_date->format('M d') : __('Today') }}</div>
                                    </div>
                                </div>
                                <span class="badge badge-green" style="font-size: 10px; flex-shrink: 0;">{{ $t->project->name ?? __('General') }}</span>
                            </div>
                        @empty
                            <div style="text-align: center; padding: 24px; color: var(--text-muted); font-size: 12px; background: var(--bg-surface-subtle); border-radius: 12px; border: 1px dashed var(--border-color);">
                                <div style="font-size: 22px; margin-bottom: 4px;">✅</div>
                                {{ __('No tasks assigned to you yet.') }}
                                <div style="margin-top: 8px;">
                                    <button onclick="switchAdminTab('my-tasks')" class="tactile-btn btn-primary" style="padding: 5px 12px; font-size: 11px;">
                                        + {{ __('Create Your First Task') }}
                                    </button>
                                </div>
                            </div>
                        @endforelse
                    </div>
                </div>

                <!-- 3. Quick Actions Grid -->
                <div class="card" style="margin-bottom: 0;">
                    <div class="card-header">
                        <h3 class="card-title">{{ __('Quick Actions') }}</h3>
                    </div>
                    <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 8px;">
                        @if($membership->hasPermission('rooms.manage'))
                        <div onclick="switchAdminTab('rooms')" style="background: var(--bg-surface-subtle); border: 1px solid var(--border-color); border-radius: 14px; padding: 12px 6px; text-align: center; cursor: pointer; transition: all 0.2s; box-shadow: var(--shadow-soft-3d);" onmouseover="this.style.transform='translateY(-2px)'" onmouseout="this.style.transform='translateY(0)'">
                            <div style="font-size: 20px; margin-bottom: 3px;">🏢</div>
                            <div style="font-size: 11px; font-weight: 800; color: var(--text-primary); line-height: 1.2;">{{ __('Create Room') }}</div>
                        </div>
                        @else
                        <div onclick="window.location.href='{{ route('office') }}'" style="background: var(--bg-surface-subtle); border: 1px solid var(--border-color); border-radius: 14px; padding: 12px 6px; text-align: center; cursor: pointer; transition: all 0.2s; box-shadow: var(--shadow-soft-3d);" onmouseover="this.style.transform='translateY(-2px)'" onmouseout="this.style.transform='translateY(0)'">
                            <div style="font-size: 20px; margin-bottom: 3px;">🚀</div>
                            <div style="font-size: 11px; font-weight: 800; color: var(--text-primary); line-height: 1.2;">{{ __('Enter Office') }}</div>
                        </div>
                        @endif

                        <div onclick="openScheduleMeetingModal()" style="background: var(--bg-surface-subtle); border: 1px solid var(--border-color); border-radius: 14px; padding: 12px 6px; text-align: center; cursor: pointer; transition: all 0.2s; box-shadow: var(--shadow-soft-3d);" onmouseover="this.style.transform='translateY(-2px)'" onmouseout="this.style.transform='translateY(0)'">
                            <div style="font-size: 20px; margin-bottom: 3px;">📅</div>
                            <div style="font-size: 11px; font-weight: 800; color: var(--text-primary); line-height: 1.2;">{{ __('Schedule') }}</div>
                        </div>

                        @if($membership->hasPermission('members.manage') || $membership->role?->slug === 'company_admin')
                        <div onclick="openInviteModal()" style="background: var(--bg-surface-subtle); border: 1px solid var(--border-color); border-radius: 14px; padding: 12px 6px; text-align: center; cursor: pointer; transition: all 0.2s; box-shadow: var(--shadow-soft-3d);" onmouseover="this.style.transform='translateY(-2px)'" onmouseout="this.style.transform='translateY(0)'">
                            <div style="font-size: 20px; margin-bottom: 3px;">👥</div>
                            <div style="font-size: 11px; font-weight: 800; color: var(--text-primary); line-height: 1.2;">{{ __('Invite People') }}</div>
                        </div>
                        @else
                        <div onclick="switchAdminTab('profile')" style="background: var(--bg-surface-subtle); border: 1px solid var(--border-color); border-radius: 14px; padding: 12px 6px; text-align: center; cursor: pointer; transition: all 0.2s; box-shadow: var(--shadow-soft-3d);" onmouseover="this.style.transform='translateY(-2px)'" onmouseout="this.style.transform='translateY(0)'">
                            <div style="font-size: 20px; margin-bottom: 3px;">👤</div>
                            <div style="font-size: 11px; font-weight: 800; color: var(--text-primary); line-height: 1.2;">{{ __('My Profile') }}</div>
                        </div>
                        @endif

                        <div onclick="switchAdminTab('projects')" style="background: var(--bg-surface-subtle); border: 1px solid var(--border-color); border-radius: 14px; padding: 12px 6px; text-align: center; cursor: pointer; transition: all 0.2s; box-shadow: var(--shadow-soft-3d);" onmouseover="this.style.transform='translateY(-2px)'" onmouseout="this.style.transform='translateY(0)'">
                            <div style="font-size: 20px; margin-bottom: 3px;">📎</div>
                            <div style="font-size: 11px; font-weight: 800; color: var(--text-primary); line-height: 1.2;">{{ __('Share File') }}</div>
                        </div>

                        <div onclick="switchAdminTab('my-tasks')" style="background: var(--bg-surface-subtle); border: 1px solid var(--border-color); border-radius: 14px; padding: 12px 6px; text-align: center; cursor: pointer; transition: all 0.2s; box-shadow: var(--shadow-soft-3d);" onmouseover="this.style.transform='translateY(-2px)'" onmouseout="this.style.transform='translateY(0)'">
                            <div style="font-size: 20px; margin-bottom: 3px;">⚡</div>
                            <div style="font-size: 11px; font-weight: 800; color: var(--text-primary); line-height: 1.2;">{{ __('My Tasks') }}</div>
                        </div>

                        <div onclick="toggleFocusMode()" id="quick-action-focus" style="background: linear-gradient(145deg, #42774C 0%, #2A5D37 100%); color: #FFFDF6; border: 1px solid #1E4E31; border-radius: 14px; padding: 12px 6px; text-align: center; cursor: pointer; transition: all 0.2s; box-shadow: var(--shadow-tactile-btn);" onmouseover="this.style.transform='translateY(-2px)'" onmouseout="this.style.transform='translateY(0)'">
                            <div style="font-size: 20px; margin-bottom: 3px;">🌿</div>
                            <div style="font-size: 11px; font-weight: 800; color: #FFFDF6; line-height: 1.2;">{{ __('Focus Mode') }}</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Bottom Floating Tip Banner -->
            <div class="focus-mode-banner">
                <div style="display: flex; align-items: center; gap: 12px;">
                    <div style="width: 36px; height: 36px; border-radius: 10px; background: var(--bg-surface-subtle); border: 1px solid var(--border-color); display: flex; align-items: center; justify-content: center; font-size: 18px;">
                        🌿
                    </div>
                    <div>
                        <div style="font-size: 13px; font-weight: 800; color: var(--text-primary);">
                            {{ __('Tip: Focus time helps you get more done.') }}
                        </div>
                        <div style="font-size: 11px; color: var(--text-secondary);">
                            {{ __('Block notifications, customize ambient workplace sounds, and boost daily productivity.') }}
                        </div>
                    </div>
                </div>
                <button onclick="toggleFocusMode()" class="tactile-btn btn-primary" style="font-size: 12px; padding: 8px 18px;">
                    {{ __('Enable Focus Mode →') }}
                </button>
            </div>
        </div>

        <!-- 1.5 TEAM CHAT & DMS TAB -->
