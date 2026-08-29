        <!-- 3. ROOMS & SPATIAL DISTRIBUTION TAB -->
        @if($membership->hasPermission('rooms.manage'))
        <div id="tab-rooms" class="tab-view">
            <!-- Page Header -->
            <div class="page-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; flex-wrap: wrap; gap: 14px;">
                <div>
                    <h1 class="page-title" style="font-size: 22px; font-weight: 900; color: var(--text-primary); margin-bottom: 4px; display: flex; align-items: center; gap: 8px;">
                        <span>🚪</span> {{ __('Meeting Rooms & Spatial Office Distribution') }}
                    </h1>
                    <p class="page-subtitle" style="font-size: 13px; color: var(--text-secondary);">
                        {{ __('Explore, inspect, and organize rooms across all company branches, maps, and spatial floorplans.') }}
                    </p>
                </div>
                <div style="display: flex; gap: 10px; flex-wrap: wrap; align-items: center;">
                    <a href="{{ route('office') }}" class="tactile-btn btn-primary" style="padding: 10px 18px; font-size: 13px; text-decoration: none;">
                        <span>🚀</span> {{ __('Enter Virtual Office') }}
                    </a>
                    <a href="{{ route('editor') }}" class="tactile-btn" style="background: linear-gradient(135deg, #10B981, #059669); color: white; padding: 10px 18px; font-size: 13px; text-decoration: none; font-weight: 800; box-shadow: 0 4px 14px rgba(16, 185, 129, 0.35);">
                        <span>✨</span> {{ __('AI Office Generator') }}
                    </a>
                    <a href="{{ route('editor') }}" class="tactile-btn btn-secondary" style="padding: 10px 16px; font-size: 13px; text-decoration: none;">
                        <span>🎨</span> {{ __('Floor Map Editor') }}
                    </a>
                </div>
            </div>

            <!-- Top Metric Stats Cards (3D Soft Neumorphic KPI Grid) -->
            <div class="kpi-grid" style="grid-template-columns: repeat(auto-fit, minmax(210px, 1fr)); gap: 14px; margin-bottom: 24px;">
                <!-- Total Rooms -->
                <div class="kpi-card" style="padding: 16px 18px;">
                    <div class="kpi-info">
                        <div class="kpi-title">{{ __('Total Configured Rooms') }}</div>
                        <div class="kpi-value" style="color: var(--brand-forest);">{{ $rooms->count() }}</div>
                        <div class="kpi-sub" style="font-size: 11px; color: var(--text-secondary);">
                            <span>🏢</span> {{ __('Across all office branches') }}
                        </div>
                    </div>
                    <div class="icon-box-3d" style="width: 44px; height: 44px; font-size: 20px;">🚪</div>
                </div>

                <!-- Active Branches -->
                <div class="kpi-card" style="padding: 16px 18px;">
                    <div class="kpi-info">
                        <div class="kpi-title">{{ __('Workplace Branches') }}</div>
                        <div class="kpi-value" style="color: #3B82F6;">{{ $offices->count() }}</div>
                        <div class="kpi-sub" style="font-size: 11px; color: var(--text-secondary);">
                            <span>🏛️</span> {{ __('Physical & virtual locations') }}
                        </div>
                    </div>
                    <div class="icon-box-3d" style="width: 44px; height: 44px; font-size: 20px; background: linear-gradient(145deg, #3B82F6, #1D4ED8); border-color: #1E40AF;">🏢</div>
                </div>

                <!-- Seating Capacity -->
                <div class="kpi-card" style="padding: 16px 18px;">
                    <div class="kpi-info">
                        <div class="kpi-title">{{ __('Total Seating Capacity') }}</div>
                        <div class="kpi-value" style="color: #10B981;">{{ $rooms->sum('capacity') }}</div>
                        <div class="kpi-sub" style="font-size: 11px; color: var(--text-secondary);">
                            <span>👥</span> {{ __('Simultaneous room seats') }}
                        </div>
                    </div>
                    <div class="icon-box-3d" style="width: 44px; height: 44px; font-size: 20px; background: linear-gradient(145deg, #10B981, #047857); border-color: #065F46;">🪑</div>
                </div>

                <!-- Public / Open Rooms -->
                <div class="kpi-card" style="padding: 16px 18px;">
                    <div class="kpi-info">
                        <div class="kpi-title">{{ __('Open Access Rooms') }}</div>
                        <div class="kpi-value" style="color: #4F9B5F;">{{ $rooms->where('access_mode', '!=', 'private')->count() }}</div>
                        <div class="kpi-sub" style="font-size: 11px; color: #4F9B5F;">
                            <span>🔓</span> {{ __('Public & walk-in spaces') }}
                        </div>
                    </div>
                    <div class="icon-box-3d" style="width: 44px; height: 44px; font-size: 20px; background: linear-gradient(145deg, #4F9B5F, #2E6F3D); border-color: #245C3A;">🔓</div>
                </div>

                <!-- Private / Locked Rooms -->
                <div class="kpi-card" style="padding: 16px 18px;">
                    <div class="kpi-info">
                        <div class="kpi-title">{{ __('Private & Locked') }}</div>
                        <div class="kpi-value" style="color: #D6A23A;">{{ $rooms->where('access_mode', 'private')->count() }}</div>
                        <div class="kpi-sub" style="font-size: 11px; color: #D6A23A;">
                            <span>🔒</span> {{ __('Knock-to-enter access') }}
                        </div>
                    </div>
                    <div class="icon-box-3d" style="width: 44px; height: 44px; font-size: 20px; background: linear-gradient(145deg, #D6A23A, #B45309); border-color: #92400E;">🔒</div>
                </div>
            </div>

            <!-- Smart Office / Branch Navigation Tabs & Filter Ribbon -->
            <div style="background: var(--bg-surface); border: 1px solid var(--border-color); border-radius: var(--radius-xl); padding: 14px 18px; margin-bottom: 20px; box-shadow: var(--shadow-card);">
                <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px; margin-bottom: 12px;">
                    <div style="display: flex; align-items: center; gap: 8px;">
                        <span style="font-size: 13px; font-weight: 800; color: var(--text-secondary);">🏢 {{ __('Filter by Office Branch:') }}</span>
                    </div>
                    <div style="display: flex; align-items: center; gap: 8px;">
                        <span style="font-size: 12px; color: var(--text-muted);">{{ __('Showing:') }}</span>
                        <span id="rooms-visible-count-badge" class="nav-badge-pill" style="font-weight: 900; background: var(--bg-surface-subtle); color: var(--brand-forest);">
                            {{ $rooms->count() }} {{ __('Rooms') }}
                        </span>
                    </div>
                </div>

                <!-- Branch Filter Pills -->
                <div style="display: flex; gap: 8px; flex-wrap: wrap; align-items: center;">
                    <button type="button" onclick="filterRoomsByBranch('all')" id="branch-pill-all" class="tactile-btn branch-filter-pill active" style="padding: 7px 14px; font-size: 12px; border-radius: 20px; border: 1px solid var(--brand-forest); background: var(--brand-forest); color: white;">
                        <span>🏢</span> {{ __('All Offices & Branches (كل المكاتب والفروع)') }}
                        <span class="nav-badge-pill" style="background: rgba(255, 255, 255, 0.25); color: white; border-color: transparent; margin-inline-start: 4px;">{{ $rooms->count() }}</span>
                    </button>

                    @foreach($offices as $off)
                        @php
                            $offRoomCount = $off->rooms->count();
                        @endphp
                        <button type="button" onclick="filterRoomsByBranch('{{ $off->id }}')" id="branch-pill-{{ $off->id }}" class="tactile-btn branch-filter-pill" style="padding: 7px 14px; font-size: 12px; border-radius: 20px; border: 1px solid var(--border-color); background: var(--bg-surface-subtle); color: var(--text-primary);">
                            <span>🏛️</span> {{ $off->name }}
                            @if($off->city_location)
                                <span style="font-size: 10px; opacity: 0.75;">({{ $off->city_location }})</span>
                            @endif
                            @if($off->is_default)
                                <span title="{{ __('Primary Office') }}" style="color: #D6A23A;">⭐</span>
                            @endif
                            <span class="nav-badge-pill" style="margin-inline-start: 4px;">{{ $offRoomCount }}</span>
                        </button>
                    @endforeach
                </div>
            </div>

            <!-- Live Search & Control Filter Bar -->
            <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px; margin-bottom: 24px;">
                <!-- Search Input -->
                <div style="flex: 1; max-width: 440px; min-width: 260px; position: relative;">
                    <span style="position: absolute; inset-inline-start: 14px; top: 50%; transform: translateY(-50%); font-size: 14px; color: var(--text-muted); pointer-events: none;">🔍</span>
                    <input type="text" id="rooms-search-input" oninput="searchAndFilterRooms()" placeholder="{{ __('Search room name, office, type, or map...') }}" style="width: 100%; background: var(--bg-surface); border: 1px solid var(--border-color); border-radius: var(--radius-full); padding: 10px 16px; padding-inline-start: 38px; color: var(--text-primary); font-size: 13px; font-weight: 600; outline: none; box-shadow: var(--shadow-inset-3d);">
                </div>

                <!-- Type Selector & View Toggle Buttons -->
                <div style="display: flex; gap: 10px; align-items: center; flex-wrap: wrap;">
                    <!-- Room Type Selector -->
                    <select id="rooms-type-filter" onchange="searchAndFilterRooms()" style="background: var(--bg-surface); border: 1px solid var(--border-color); border-radius: var(--radius-md); padding: 9px 14px; color: var(--text-primary); font-size: 12px; font-weight: 700; outline: none; box-shadow: var(--shadow-soft-3d);">
                        <option value="all">🏷️ {{ __('All Room Types (كل الأنواع)') }}</option>
                        <option value="office">💼 {{ __('Private Offices (مكاتب خاصة)') }}</option>
                        <option value="meeting">👥 {{ __('Conference & Meeting (قاعات اجتماعات)') }}</option>
                        <option value="lounge">☕ {{ __('Lounge & Breakout (استراحات)') }}</option>
                        <option value="auditorium">🎭 {{ __('Auditorium / Stage (مسارح)') }}</option>
                        <option value="brainstorming">🧠 {{ __('Brainstorming (عصف ذهني)') }}</option>
                    </select>

                    <!-- View Switcher (Cards vs Table) -->
                    <div style="display: flex; background: var(--bg-surface); border: 1px solid var(--border-color); border-radius: var(--radius-md); padding: 3px; box-shadow: var(--shadow-soft-3d);">
                        <button type="button" onclick="switchRoomsViewMode('cards')" id="rooms-view-cards-btn" class="tactile-btn" style="padding: 6px 12px; font-size: 12px; border-radius: 6px; background: var(--brand-forest); color: white; border: none;" title="{{ __('Visual Spatial Cards Grid') }}">
                            <span>🎴</span> {{ __('Cards Grid') }}
                        </button>
                        <button type="button" onclick="switchRoomsViewMode('table')" id="rooms-view-table-btn" class="tactile-btn" style="padding: 6px 12px; font-size: 12px; border-radius: 6px; background: transparent; color: var(--text-secondary); border: none; box-shadow: none;" title="{{ __('Detailed Data Table') }}">
                            <span>📋</span> {{ __('Data Table') }}
                        </button>
                    </div>
                </div>
            </div>

            <!-- Grouped Workplace Offices & Rooms Container -->
            <div id="office-branches-container" style="display: flex; flex-direction: column; gap: 28px;">
                @foreach($offices as $off)
                    @php
                        $branchRooms = $off->rooms;
                        $publishedMap = $off->activeMap ?: $off->maps->first();
                    @endphp

                    <div class="office-branch-section" id="office-branch-section-{{ $off->id }}" data-office-id="{{ $off->id }}" style="background: var(--bg-surface); border: 1px solid var(--border-color); border-radius: var(--radius-xl); overflow: hidden; box-shadow: var(--shadow-card); transition: all 0.25s ease;">
                        
                        <!-- Office Branch Distinct Header Banner -->
                        <div style="padding: 18px 24px; background: var(--bg-surface-subtle); border-bottom: 1px solid var(--border-color); display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 14px;">
                            <div style="display: flex; align-items: center; gap: 14px; min-width: 0;">
                                <div class="icon-box-3d" style="width: 46px; height: 46px; border-radius: 14px; font-size: 22px; flex-shrink: 0; background: linear-gradient(145deg, #245C3A 0%, #153B23 100%);">
                                    🏛️
                                </div>
                                <div>
                                    <div style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap; margin-bottom: 2px;">
                                        <h2 style="font-size: 17px; font-weight: 900; color: var(--text-primary); margin: 0;">
                                            {{ $off->name }}
                                        </h2>
                                        @if($off->city_location)
                                            <span class="nav-badge-pill" style="font-size: 11px; background: rgba(59, 130, 246, 0.12); color: #3B82F6; border-color: rgba(59, 130, 246, 0.3);">
                                                📍 {{ $off->city_location }}
                                            </span>
                                        @endif
                                        @if($off->is_default)
                                            <span class="nav-badge-pill" style="font-size: 11px; background: rgba(214, 162, 58, 0.15); color: #D6A23A; border-color: rgba(214, 162, 58, 0.35);">
                                                ⭐ {{ __('Primary Headquarters (المقر الرئيسي)') }}
                                            </span>
                                        @endif
                                        <span class="nav-badge-pill" style="font-size: 11px;">
                                            🚪 {{ $branchRooms->count() }} {{ __('Rooms Configured') }}
                                        </span>
                                    </div>
                                    <div style="font-size: 12px; color: var(--text-muted); display: flex; align-items: center; gap: 12px; flex-wrap: wrap;">
                                        <span>🗺️ {{ __('Active Map Blueprint:') }} <strong style="color: var(--text-primary);">{{ $publishedMap?->name ?? __('Standard Layout') }}</strong></span>
                                        @if($publishedMap)
                                            <span>📐 {{ $publishedMap->width }}x{{ $publishedMap->height }} tiles ({{ $publishedMap->tile_size }}px)</span>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <!-- Branch Fast Actions -->
                            <div style="display: flex; gap: 8px; align-items: center;">
                                <a href="{{ route('office', ['office_id' => $off->id]) }}" class="tactile-btn btn-primary" style="padding: 8px 16px; font-size: 12px; text-decoration: none;">
                                    <span>🚀</span> {{ __('Enter This Office (دخول الفرع)') }}
                                </a>
                                <a href="{{ route('editor') }}" class="tactile-btn btn-secondary" style="padding: 8px 14px; font-size: 12px; text-decoration: none;">
                                    <span>🎨</span> {{ __('Edit Blueprint') }}
                                </a>
                            </div>
                        </div>

                        <!-- Branch Content: Visual Cards Grid View -->
                        <div class="branch-rooms-cards-view" style="padding: 24px;">
                            @if($branchRooms->count() > 0)
                                <div class="rooms-grid-container" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 16px;">
                                    @foreach($branchRooms as $r)
                                        @php
                                            $roomColor = $r->color ?: '#245C3A';
                                            $bounds = $r->bounds ?? ['x' => 0, 'y' => 0, 'width' => 10, 'height' => 10];
                                            $bx = $bounds['x'] ?? 0;
                                            $by = $bounds['y'] ?? 0;
                                            $bw = $bounds['width'] ?? ($bounds['w'] ?? 10);
                                            $bh = $bounds['height'] ?? ($bounds['h'] ?? 10);

                                            // Icon & Type determination
                                            $rType = strtolower($r->type ?? 'meeting');
                                            $typeIcon = '👥';
                                            $typeLabel = __('Conference / Meeting');
                                            if (str_contains($rType, 'office') || str_contains(strtolower($r->name), 'مكتب')) {
                                                $typeIcon = '💼';
                                                $typeLabel = __('Private Office');
                                            } elseif (str_contains($rType, 'lounge') || str_contains(strtolower($r->name), 'استراحة')) {
                                                $typeIcon = '☕';
                                                $typeLabel = __('Lounge & Breakout');
                                            } elseif (str_contains($rType, 'auditorium') || str_contains(strtolower($r->name), 'مسرح') || str_contains(strtolower($r->name), 'قاعة')) {
                                                $typeIcon = '🎭';
                                                $typeLabel = __('Auditorium / Hall');
                                            } elseif (str_contains($rType, 'brainstorm') || str_contains(strtolower($r->name), 'عصف')) {
                                                $typeIcon = '🧠';
                                                $typeLabel = __('Ideation Space');
                                            }
                                        @endphp

                                        <div class="room-spatial-card" data-room-id="{{ $r->id }}" data-room-name="{{ strtolower($r->name) }}" data-office-name="{{ strtolower($off->name) }}" data-room-type="{{ $rType }}" style="background: var(--bg-surface-subtle); border: 1px solid var(--border-color); border-radius: 16px; padding: 18px; position: relative; display: flex; flex-direction: column; justify-content: space-between; transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1); box-shadow: var(--shadow-soft-3d);">
                                            
                                            <!-- Color Accent Top Strip -->
                                            <div style="position: absolute; top: 0; inset-inline-start: 18px; inset-inline-end: 18px; height: 3px; border-radius: 0 0 4px 4px; background: {{ $roomColor }}; opacity: 0.85;"></div>

                                            <div>
                                                <!-- Card Top Row: Room Type Badge & Door Lock Status -->
                                                <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 12px; margin-top: 4px;">
                                                    <div style="display: flex; align-items: center; gap: 8px;">
                                                        <div style="width: 36px; height: 36px; border-radius: 10px; background: rgba(36, 92, 58, 0.12); border: 1px solid rgba(36, 92, 58, 0.25); display: flex; align-items: center; justify-content: center; font-size: 18px;">
                                                            {{ $typeIcon }}
                                                        </div>
                                                        <div>
                                                            <h3 style="font-size: 15px; font-weight: 900; color: var(--text-primary); margin: 0; line-height: 1.2;">
                                                                {{ $r->name }}
                                                            </h3>
                                                            <span style="font-size: 11px; color: var(--text-muted); font-weight: 600;">{{ $typeLabel }}</span>
                                                        </div>
                                                    </div>

                                                    <!-- Door Status Pill -->
                                                    <span class="nav-badge-pill" style="font-size: 10px; {{ $r->access_mode === 'private' ? 'background: rgba(214, 162, 58, 0.15); color: #D6A23A; border-color: rgba(214, 162, 58, 0.3);' : 'background: rgba(79, 155, 95, 0.15); color: #4F9B5F; border-color: rgba(79, 155, 95, 0.3);' }}">
                                                        {{ $r->access_mode === 'private' ? '🔒 ' . __('Locked / Private') : '🔓 ' . __('Open') }}
                                                    </span>
                                                </div>

                                                <!-- Room Spatial Details Specs (Attribution to Office & Map) -->
                                                <div style="background: var(--bg-surface); border: 1px solid var(--border-color); border-radius: 12px; padding: 12px; margin-bottom: 14px; display: flex; flex-direction: column; gap: 6px; font-size: 12px;">
                                                    <!-- Office Location Attribution -->
                                                    <div style="display: flex; justify-content: space-between; align-items: center;">
                                                        <span style="color: var(--text-muted); font-size: 11px;">🏛️ {{ __('Office Branch:') }}</span>
                                                        <strong style="color: var(--brand-forest); font-weight: 800;">{{ $off->name }}</strong>
                                                    </div>
                                                    <!-- Map Blueprint Name -->
                                                    <div style="display: flex; justify-content: space-between; align-items: center;">
                                                        <span style="color: var(--text-muted); font-size: 11px;">🗺️ {{ __('Map Floorplan:') }}</span>
                                                        <span style="color: var(--text-primary); font-weight: 700; font-size: 11px;">{{ $r->map?->name ?? $publishedMap?->name ?? __('Main Floor') }}</span>
                                                    </div>
                                                    <!-- Capacity -->
                                                    <div style="display: flex; justify-content: space-between; align-items: center;">
                                                        <span style="color: var(--text-muted); font-size: 11px;">👥 {{ __('Capacity:') }}</span>
                                                        <span style="font-weight: 800; color: var(--text-primary); font-family: monospace;">{{ $r->capacity }} {{ __('Seats (مقاعد)') }}</span>
                                                    </div>
                                                    <!-- Spatial Bounds & Location -->
                                                    <div style="display: flex; justify-content: space-between; align-items: center;">
                                                        <span style="color: var(--text-muted); font-size: 11px;">📍 {{ __('Floor Coordinates:') }}</span>
                                                        <code style="background: var(--bg-surface-subtle); padding: 2px 6px; border-radius: 4px; font-size: 10px; color: var(--text-secondary); border: 1px solid var(--border-color);">
                                                            X:{{ $bx }}, Y:{{ $by }} ({{ $bw }}x{{ $bh }})
                                                        </code>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Card Quick Actions Footer -->
                                            <div style="display: flex; gap: 6px; align-items: center; border-top: 1px dashed var(--border-color); padding-top: 12px;">
                                                <a href="{{ route('office', ['office_id' => $off->id, 'room_id' => $r->id]) }}" class="tactile-btn btn-primary" style="flex: 1; padding: 7px 10px; font-size: 11px; text-decoration: none; justify-content: center;">
                                                    <span>🚀</span> {{ __('Enter Room') }}
                                                </a>
                                                <button type="button" onclick="openRoomGuestModal('{{ $r->id }}', '{{ addslashes($r->name) }}')" class="tactile-btn btn-secondary" style="padding: 7px 10px; font-size: 11px;" title="{{ __('Generate Guest Link for this room') }}">
                                                    <span>🔗</span> {{ __('Guest Link') }}
                                                </button>
                                                <a href="{{ route('editor') }}" class="tactile-btn btn-secondary" style="padding: 7px 10px; font-size: 11px; text-decoration: none;" title="{{ __('Edit in Map Editor') }}">
                                                    <span>🎨</span>
                                                </a>
                                            </div>

                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <div style="text-align: center; padding: 36px 20px; color: var(--text-muted); background: var(--bg-surface-subtle); border: 1px dashed var(--border-color); border-radius: 14px;">
                                    <div style="font-size: 32px; margin-bottom: 8px;">📂</div>
                                    <h4 style="font-size: 14px; font-weight: 800; color: var(--text-primary); margin-bottom: 4px;">{{ __('No rooms created in this office branch yet.') }}</h4>
                                    <p style="font-size: 12px; margin-bottom: 14px;">{{ __('Use the Floor Map Editor or AI generator to design rooms for') }} {{ $off->name }}.</p>
                                    <a href="{{ route('editor') }}" class="tactile-btn btn-primary" style="padding: 8px 16px; font-size: 12px; text-decoration: none;">
                                        <span>🎨</span> {{ __('Design Rooms in Editor') }}
                                    </a>
                                </div>
                            @endif
                        </div>

                        <!-- Branch Content: Detailed Data Table View -->
                        <div class="branch-rooms-table-view" style="display: none; padding: 0;">
                            @if($branchRooms->count() > 0)
                                <div style="overflow-x: auto;">
                                    <table class="data-table">
                                        <thead>
                                            <tr>
                                                <th>{{ __('Room Name') }}</th>
                                                <th>{{ __('Office Branch') }}</th>
                                                <th>{{ __('Map Blueprint') }}</th>
                                                <th>{{ __('Type') }}</th>
                                                <th>{{ __('Capacity') }}</th>
                                                <th>{{ __('Spatial Coordinates') }}</th>
                                                <th>{{ __('Door Status') }}</th>
                                                <th style="text-align: center;">{{ __('Actions') }}</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($branchRooms as $r)
                                                @php
                                                    $rType = strtolower($r->type ?? 'meeting');
                                                    $bounds = $r->bounds ?? ['x' => 0, 'y' => 0, 'width' => 10, 'height' => 10];
                                                    $bx = $bounds['x'] ?? 0;
                                                    $by = $bounds['y'] ?? 0;
                                                    $bw = $bounds['width'] ?? ($bounds['w'] ?? 10);
                                                    $bh = $bounds['height'] ?? ($bounds['h'] ?? 10);
                                                @endphp
                                                <tr class="room-table-row" data-room-id="{{ $r->id }}" data-room-name="{{ strtolower($r->name) }}" data-office-name="{{ strtolower($off->name) }}" data-room-type="{{ $rType }}">
                                                    <td>
                                                        <div style="display: flex; align-items: center; gap: 8px;">
                                                            <div style="width: 10px; height: 10px; border-radius: 50%; background: {{ $r->color ?: '#245C3A' }}; flex-shrink: 0;"></div>
                                                            <strong style="color: var(--text-primary); font-size: 13px;">🚪 {{ $r->name }}</strong>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <strong style="color: var(--brand-forest);">🏛️ {{ $off->name }}</strong>
                                                        @if($off->city_location)
                                                            <span style="font-size: 11px; color: var(--text-muted);">({{ $off->city_location }})</span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        <span style="font-size: 12px; color: var(--text-secondary);">🗺️ {{ $r->map?->name ?? $publishedMap?->name ?? __('Default Blueprint') }}</span>
                                                    </td>
                                                    <td>
                                                        <span class="nav-badge-pill" style="text-transform: capitalize;">{{ $r->type ?: 'Meeting' }}</span>
                                                    </td>
                                                    <td>
                                                        <span style="font-weight: 700; font-family: monospace;">{{ $r->capacity }} {{ __('Seats') }}</span>
                                                    </td>
                                                    <td>
                                                        <code style="background: var(--bg-surface-subtle); border: 1px solid var(--border-color); padding: 3px 8px; border-radius: 6px; font-size: 11px; color: var(--text-secondary);">
                                                            X:{{ $bx }}, Y:{{ $by }} ({{ $bw }}x{{ $bh }})
                                                        </code>
                                                    </td>
                                                    <td>
                                                        <span class="nav-badge-pill" style="{{ $r->access_mode === 'private' ? 'background: rgba(214, 162, 58, 0.15); color: #D6A23A;' : 'background: rgba(79, 155, 95, 0.15); color: #4F9B5F;' }}">
                                                            {{ $r->access_mode === 'private' ? '🔒 ' . __('Locked') : '🔓 ' . __('Open') }}
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <div style="display: flex; gap: 6px; justify-content: center; align-items: center;">
                                                            <a href="{{ route('office', ['office_id' => $off->id, 'room_id' => $r->id]) }}" class="tactile-btn btn-primary" style="padding: 6px 12px; font-size: 11px; text-decoration: none;">
                                                                🚀 {{ __('Enter') }}
                                                            </a>
                                                            <button type="button" onclick="openRoomGuestModal('{{ $r->id }}', '{{ addslashes($r->name) }}')" class="tactile-btn btn-secondary" style="padding: 6px 10px; font-size: 11px;" title="{{ __('Generate Guest Link') }}">
                                                                🔗
                                                            </button>
                                                        </div>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @endif
                        </div>

                    </div>
                @endforeach
            </div>

            <!-- Global No Results Empty State (when search returns 0 matches) -->
            <div id="rooms-no-results-hint" style="display: none; text-align: center; padding: 60px 20px; background: var(--bg-surface); border: 1px dashed var(--border-color); border-radius: var(--radius-xl); margin-top: 20px;">
                <div style="font-size: 40px; margin-bottom: 12px;">🔍</div>
                <h3 style="font-size: 16px; font-weight: 900; color: var(--text-primary); margin-bottom: 6px;">{{ __('No matching workplace rooms found') }}</h3>
                <p style="font-size: 13px; color: var(--text-secondary); max-width: 400px; margin: 0 auto 16px auto;">
                    {{ __('Try adjusting your branch filter, clearing the search query, or selecting another room category.') }}
                </p>
                <button type="button" onclick="resetRoomsFilters()" class="tactile-btn btn-primary" style="padding: 9px 18px; font-size: 12px;">
                    🔄 {{ __('Reset Filters') }}
                </button>
            </div>

        </div>

        <script nonce="{{ $cspNonce ?? '' }}">
            // ── Rooms Interactive Filtering & Spatial View Engine ──
            let activeBranchFilter = 'all';
            let currentRoomsView = 'cards';

            function filterRoomsByBranch(branchId) {
                activeBranchFilter = branchId;

                // Update Filter Pills styling
                document.querySelectorAll('.branch-filter-pill').forEach(pill => {
                    pill.classList.remove('active');
                    pill.style.background = 'var(--bg-surface-subtle)';
                    pill.style.color = 'var(--text-primary)';
                    pill.style.borderColor = 'var(--border-color)';
                });

                const activePill = document.getElementById(`branch-pill-${branchId}`);
                if (activePill) {
                    activePill.classList.add('active');
                    activePill.style.background = 'var(--brand-forest)';
                    activePill.style.color = 'white';
                    activePill.style.borderColor = 'var(--brand-forest)';
                }

                // Show/hide office sections
                const sections = document.querySelectorAll('.office-branch-section');
                sections.forEach(sec => {
                    const secId = sec.dataset.officeId;
                    if (branchId === 'all' || secId === branchId) {
                        sec.style.display = 'block';
                    } else {
                        sec.style.display = 'none';
                    }
                });

                searchAndFilterRooms();
            }

            function searchAndFilterRooms() {
                const query = (document.getElementById('rooms-search-input')?.value || '').toLowerCase().trim();
                const typeFilter = document.getElementById('rooms-type-filter')?.value || 'all';

                let totalVisibleRooms = 0;

                const sections = document.querySelectorAll('.office-branch-section');
                sections.forEach(sec => {
                    const secId = sec.dataset.officeId;
                    const isBranchVisible = (activeBranchFilter === 'all' || secId === activeBranchFilter);

                    if (!isBranchVisible) {
                        sec.style.display = 'none';
                        return;
                    }

                    let branchVisibleCards = 0;

                    // Filter cards
                    sec.querySelectorAll('.room-spatial-card').forEach(card => {
                        const name = card.dataset.roomName || '';
                        const office = card.dataset.officeName || '';
                        const rType = card.dataset.roomType || '';

                        const matchesQuery = !query || name.includes(query) || office.includes(query) || rType.includes(query);
                        const matchesType = (typeFilter === 'all') || rType.includes(typeFilter) || (typeFilter === 'meeting' && (rType.includes('meeting') || rType.includes('conference')));

                        if (matchesQuery && matchesType) {
                            card.style.display = 'flex';
                            branchVisibleCards++;
                            totalVisibleRooms++;
                        } else {
                            card.style.display = 'none';
                        }
                    });

                    // Filter table rows
                    sec.querySelectorAll('.room-table-row').forEach(row => {
                        const name = row.dataset.roomName || '';
                        const office = row.dataset.officeName || '';
                        const rType = row.dataset.roomType || '';

                        const matchesQuery = !query || name.includes(query) || office.includes(query) || rType.includes(query);
                        const matchesType = (typeFilter === 'all') || rType.includes(typeFilter) || (typeFilter === 'meeting' && (rType.includes('meeting') || rType.includes('conference')));

                        if (matchesQuery && matchesType) {
                            row.style.display = '';
                        } else {
                            row.style.display = 'none';
                        }
                    });

                    // If branch has no visible cards/rows for this search, hide or keep visible based on search
                    if (query && branchVisibleCards === 0) {
                        sec.style.display = 'none';
                    } else {
                        sec.style.display = 'block';
                    }
                });

                // Update visible count badge
                const cntBadge = document.getElementById('rooms-visible-count-badge');
                if (cntBadge) {
                    cntBadge.textContent = `${totalVisibleRooms} {{ __('Rooms') }}`;
                }

                // Show empty state if 0 visible
                const hint = document.getElementById('rooms-no-results-hint');
                if (hint) {
                    hint.style.display = (totalVisibleRooms === 0) ? 'block' : 'none';
                }
            }

            function switchRoomsViewMode(mode) {
                currentRoomsView = mode;
                localStorage.setItem('rooms_view_mode', mode);

                const cardsBtn = document.getElementById('rooms-view-cards-btn');
                const tableBtn = document.getElementById('rooms-view-table-btn');

                if (mode === 'cards') {
                    if (cardsBtn) {
                        cardsBtn.style.background = 'var(--brand-forest)';
                        cardsBtn.style.color = 'white';
                        cardsBtn.style.boxShadow = '';
                    }
                    if (tableBtn) {
                        tableBtn.style.background = 'transparent';
                        tableBtn.style.color = 'var(--text-secondary)';
                        tableBtn.style.boxShadow = 'none';
                    }
                    document.querySelectorAll('.branch-rooms-cards-view').forEach(el => el.style.display = 'block');
                    document.querySelectorAll('.branch-rooms-table-view').forEach(el => el.style.display = 'none');
                } else {
                    if (tableBtn) {
                        tableBtn.style.background = 'var(--brand-forest)';
                        tableBtn.style.color = 'white';
                        tableBtn.style.boxShadow = '';
                    }
                    if (cardsBtn) {
                        cardsBtn.style.background = 'transparent';
                        cardsBtn.style.color = 'var(--text-secondary)';
                        cardsBtn.style.boxShadow = 'none';
                    }
                    document.querySelectorAll('.branch-rooms-cards-view').forEach(el => el.style.display = 'none');
                    document.querySelectorAll('.branch-rooms-table-view').forEach(el => el.style.display = 'block');
                }
            }

            function resetRoomsFilters() {
                const sInput = document.getElementById('rooms-search-input');
                if (sInput) sInput.value = '';
                const tFilter = document.getElementById('rooms-type-filter');
                if (tFilter) tFilter.value = 'all';
                filterRoomsByBranch('all');
            }

            function openRoomGuestModal(roomId, roomName) {
                if (typeof openInviteModal === 'function') {
                    openInviteModal();
                    if (typeof switchInviteTab === 'function') {
                        switchInviteTab('guest');
                    }
                    const sel = document.getElementById('invite-room-select');
                    if (sel && roomId) {
                        sel.value = roomId;
                        if (typeof onInviteRoomSelected === 'function') {
                            onInviteRoomSelected(sel);
                        }
                    }
                } else {
                    switchAdminTab('guests');
                }
            }

            // Restore view mode from localStorage on load
            (function() {
                const savedMode = localStorage.getItem('rooms_view_mode') || 'cards';
                if (savedMode === 'table') {
                    setTimeout(() => switchRoomsViewMode('table'), 50);
                }
            })();
        </script>
        @endif
