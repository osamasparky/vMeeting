        <!-- 3. ROOMS & SPATIAL DISTRIBUTION TAB -->
        @if($membership->hasPermission('rooms.manage'))
        <div id="tab-rooms" class="tab-view">
            <!-- Page Header -->
            <div class="page-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; flex-wrap: wrap; gap: 14px;">
                <div>
                    <h1 class="page-title" style="font-size: 22px; font-weight: 800; color: var(--ula-text-primary); margin-bottom: 4px; display: flex; align-items: center; gap: 8px;">
                        <span class="material-symbols-rounded" style="font-size: 24px; color: var(--ula-highlight-default);">meeting_room</span>
                        <span>{{ __('Meeting Rooms & Spatial Office Distribution') }}</span>
                    </h1>
                    <p class="page-subtitle" style="font-size: 13px; color: var(--ula-text-secondary);">
                        {{ __('Explore, inspect, and organize rooms across all company branches, maps, and spatial floorplans.') }}
                    </p>
                </div>
                <div style="display: flex; gap: 10px; flex-wrap: wrap; align-items: center;">
                    <x-btn variant="primary" size="md" href="{{ route('office') }}" icon="login">
                        {{ __('Enter Virtual Office') }}
                    </x-btn>
                    <x-btn variant="secondary" size="md" href="{{ route('editor') }}" icon="auto_awesome">
                        {{ __('AI Office Generator') }}
                    </x-btn>
                    <x-btn variant="outline" size="md" href="{{ route('editor') }}" icon="design_services">
                        {{ __('Floor Map Editor') }}
                    </x-btn>
                </div>
            </div>

            <!-- Top Metric Stats Cards -->
            <div class="kpi-grid" style="grid-template-columns: repeat(auto-fit, minmax(210px, 1fr)); gap: 14px; margin-bottom: 24px;">
                <!-- Total Rooms -->
                <div class="kpi-card" style="padding: 16px 18px;">
                    <div class="kpi-info">
                        <div class="kpi-title">{{ __('Total Configured Rooms') }}</div>
                        <div class="kpi-value" style="color: var(--ula-palm-900);">{{ $rooms->count() }}</div>
                        <div class="kpi-sub" style="font-size: 11px; color: var(--ula-text-secondary); display: flex; align-items: center; gap: 4px;">
                            <span class="material-symbols-rounded" style="font-size: 14px;">domain</span>
                            <span>{{ __('Across all office branches') }}</span>
                        </div>
                    </div>
                    <div class="icon-box-3d" style="width: 44px; height: 44px; font-size: 20px;">
                        <span class="material-symbols-rounded">meeting_room</span>
                    </div>
                </div>

                <!-- Active Branches -->
                <div class="kpi-card" style="padding: 16px 18px;">
                    <div class="kpi-info">
                        <div class="kpi-title">{{ __('Workplace Branches') }}</div>
                        <div class="kpi-value" style="color: var(--ula-palm-800);">{{ $offices->count() }}</div>
                        <div class="kpi-sub" style="font-size: 11px; color: var(--ula-text-secondary); display: flex; align-items: center; gap: 4px;">
                            <span class="material-symbols-rounded" style="font-size: 14px;">corporate_fare</span>
                            <span>{{ __('Physical & virtual locations') }}</span>
                        </div>
                    </div>
                    <div class="icon-box-3d" style="width: 44px; height: 44px; font-size: 20px;">
                        <span class="material-symbols-rounded">corporate_fare</span>
                    </div>
                </div>

                <!-- Seating Capacity -->
                <div class="kpi-card" style="padding: 16px 18px;">
                    <div class="kpi-info">
                        <div class="kpi-title">{{ __('Total Seating Capacity') }}</div>
                        <div class="kpi-value" style="color: var(--ula-status-success);">{{ $rooms->sum('capacity') }}</div>
                        <div class="kpi-sub" style="font-size: 11px; color: var(--ula-text-secondary); display: flex; align-items: center; gap: 4px;">
                            <span class="material-symbols-rounded" style="font-size: 14px;">chair</span>
                            <span>{{ __('Simultaneous room seats') }}</span>
                        </div>
                    </div>
                    <div class="icon-box-3d" style="width: 44px; height: 44px; font-size: 20px;">
                        <span class="material-symbols-rounded">chair</span>
                    </div>
                </div>

                <!-- Public / Open Rooms -->
                <div class="kpi-card" style="padding: 16px 18px;">
                    <div class="kpi-info">
                        <div class="kpi-title">{{ __('Open Access Rooms') }}</div>
                        <div class="kpi-value" style="color: var(--ula-status-success);">{{ $rooms->where('access_mode', '!=', 'private')->count() }}</div>
                        <div class="kpi-sub" style="font-size: 11px; color: var(--ula-status-success); display: flex; align-items: center; gap: 4px;">
                            <span class="material-symbols-rounded" style="font-size: 14px;">lock_open</span>
                            <span>{{ __('Public & walk-in spaces') }}</span>
                        </div>
                    </div>
                    <div class="icon-box-3d" style="width: 44px; height: 44px; font-size: 20px;">
                        <span class="material-symbols-rounded">lock_open</span>
                    </div>
                </div>

                <!-- Private / Locked Rooms -->
                <div class="kpi-card" style="padding: 16px 18px;">
                    <div class="kpi-info">
                        <div class="kpi-title">{{ __('Private & Locked') }}</div>
                        <div class="kpi-value" style="color: var(--ula-gold-600);">{{ $rooms->where('access_mode', 'private')->count() }}</div>
                        <div class="kpi-sub" style="font-size: 11px; color: var(--ula-gold-600); display: flex; align-items: center; gap: 4px;">
                            <span class="material-symbols-rounded" style="font-size: 14px;">lock</span>
                            <span>{{ __('Knock-to-enter access') }}</span>
                        </div>
                    </div>
                    <div class="icon-box-3d" style="width: 44px; height: 44px; font-size: 20px;">
                        <span class="material-symbols-rounded">lock</span>
                    </div>
                </div>
            </div>

            <!-- Smart Office / Branch Navigation Tabs & Filter Ribbon -->
            <div style="background: var(--ula-surface-card); border: 1px solid var(--ula-border-subtle); border-radius: var(--ula-radius-xl); padding: 14px 18px; margin-bottom: 20px; box-shadow: var(--ula-shadow-sm);">
                <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px; margin-bottom: 12px;">
                    <div style="display: flex; align-items: center; gap: 8px;">
                        <span style="font-size: 13px; font-weight: 700; color: var(--ula-text-secondary); display: flex; align-items: center; gap: 6px;">
                            <span class="material-symbols-rounded" style="font-size: 18px; color: var(--ula-highlight-default);">domain</span>
                            <span>{{ __('Filter by Office Branch:') }}</span>
                        </span>
                    </div>
                    <div style="display: flex; align-items: center; gap: 8px;">
                        <span style="font-size: 12px; color: var(--ula-text-muted);">{{ __('Showing:') }}</span>
                        <span id="rooms-visible-count-badge" class="nav-badge-pill" style="font-weight: 700; background: var(--ula-sand-200); color: var(--ula-palm-900); font-family: 'IBM Plex Mono', monospace;">
                            {{ $rooms->count() }} {{ __('Rooms') }}
                        </span>
                    </div>
                </div>

                <!-- Branch Filter Pills -->
                <div style="display: flex; gap: 8px; flex-wrap: wrap; align-items: center;">
                    <button type="button" onclick="filterRoomsByBranch('all')" id="branch-pill-all" class="tactile-btn branch-filter-pill active" style="padding: 7px 14px; font-size: 12px; border-radius: 20px; border: 1px solid var(--ula-palm-900); background: var(--ula-palm-900); color: white; display: inline-flex; align-items: center; gap: 6px;">
                        <span class="material-symbols-rounded" style="font-size: 15px;">domain</span>
                        <span>{{ __('All Offices & Branches') }}</span>
                        <span class="nav-badge-pill" style="background: rgba(255, 255, 255, 0.25); color: white; border-color: transparent; margin-inline-start: 4px; font-family: 'IBM Plex Mono', monospace;">{{ $rooms->count() }}</span>
                    </button>

                    @foreach($offices as $off)
                        @php
                            $offRoomCount = $off->rooms->count();
                        @endphp
                        <button type="button" onclick="filterRoomsByBranch('{{ $off->id }}')" id="branch-pill-{{ $off->id }}" class="tactile-btn branch-filter-pill" style="padding: 7px 14px; font-size: 12px; border-radius: 20px; border: 1px solid var(--ula-border-subtle); background: var(--ula-surface-card); color: var(--ula-text-primary); display: inline-flex; align-items: center; gap: 6px;">
                            <span class="material-symbols-rounded" style="font-size: 15px; color: var(--ula-highlight-default);">corporate_fare</span>
                            <span>{{ $off->name }}</span>
                            @if($off->city_location)
                                <span style="font-size: 10px; opacity: 0.75;">({{ $off->city_location }})</span>
                            @endif
                            @if($off->is_default)
                                <span class="material-symbols-rounded" title="{{ __('Primary Office') }}" style="font-size: 14px; color: var(--ula-gold-400);">star</span>
                            @endif
                            <span class="nav-badge-pill" style="margin-inline-start: 4px; font-family: 'IBM Plex Mono', monospace;">{{ $offRoomCount }}</span>
                        </button>
                    @endforeach
                </div>
            </div>

            <!-- Live Search & Control Filter Bar -->
            <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px; margin-bottom: 24px;">
                <!-- Search Input -->
                <div style="flex: 1; max-width: 440px; min-width: 260px; position: relative;">
                    <span class="material-symbols-rounded" style="position: absolute; inset-inline-start: 14px; top: 50%; transform: translateY(-50%); font-size: 18px; color: var(--ula-text-muted); pointer-events: none;">search</span>
                    <input type="text" id="rooms-search-input" oninput="searchAndFilterRooms()" placeholder="{{ __('Search room name, office, type, or map...') }}" style="width: 100%; background: var(--ula-surface-card); border: 1px solid var(--ula-border-subtle); border-radius: 9999px; padding: 10px 16px; padding-inline-start: 40px; color: var(--ula-text-primary); font-size: 13px; font-weight: 500; outline: none; box-shadow: var(--ula-shadow-sm);">
                </div>

                <!-- Type Selector & View Toggle Buttons -->
                <div style="display: flex; gap: 10px; align-items: center; flex-wrap: wrap;">
                    <!-- Room Type Selector -->
                    <select id="rooms-type-filter" onchange="searchAndFilterRooms()" style="background: var(--ula-surface-card); border: 1px solid var(--ula-border-subtle); border-radius: var(--ula-radius-md); padding: 9px 14px; color: var(--ula-text-primary); font-size: 12px; font-weight: 600; outline: none; box-shadow: var(--ula-shadow-sm);">
                        <option value="all">{{ __('All Room Types') }}</option>
                        <option value="office">{{ __('Private Offices') }}</option>
                        <option value="meeting">{{ __('Conference & Meeting') }}</option>
                        <option value="lounge">{{ __('Lounge & Breakout') }}</option>
                        <option value="auditorium">{{ __('Auditorium / Stage') }}</option>
                        <option value="brainstorming">{{ __('Brainstorming') }}</option>
                    </select>

                    <!-- View Switcher (Cards vs Table) -->
                    <div style="display: flex; background: var(--ula-surface-card); border: 1px solid var(--ula-border-subtle); border-radius: var(--ula-radius-md); padding: 3px; box-shadow: var(--ula-shadow-sm);">
                        <button type="button" onclick="switchRoomsViewMode('cards')" id="rooms-view-cards-btn" class="tactile-btn" style="padding: 6px 12px; font-size: 12px; border-radius: 6px; background: var(--ula-palm-900); color: white; border: none; display: inline-flex; align-items: center; gap: 4px;" title="{{ __('Visual Spatial Cards Grid') }}">
                            <span class="material-symbols-rounded" style="font-size: 15px;">view_module</span>
                            <span>{{ __('Cards Grid') }}</span>
                        </button>
                        <button type="button" onclick="switchRoomsViewMode('table')" id="rooms-view-table-btn" class="tactile-btn" style="padding: 6px 12px; font-size: 12px; border-radius: 6px; background: transparent; color: var(--ula-text-secondary); border: none; box-shadow: none; display: inline-flex; align-items: center; gap: 4px;" title="{{ __('Detailed Data Table') }}">
                            <span class="material-symbols-rounded" style="font-size: 15px;">table_rows</span>
                            <span>{{ __('Data Table') }}</span>
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

                    <div class="office-branch-section" id="office-branch-section-{{ $off->id }}" data-office-id="{{ $off->id }}" style="background: var(--ula-surface-card); border: 1px solid var(--ula-border-subtle); border-radius: var(--ula-radius-xl); overflow: hidden; box-shadow: var(--ula-shadow-sm); transition: all 0.25s ease;">
                        
                        <!-- Office Branch Distinct Header Banner -->
                        <div style="padding: 18px 24px; background: var(--ula-sand-200); border-bottom: 1px solid var(--ula-border-subtle); display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 14px;">
                            <div style="display: flex; align-items: center; gap: 14px; min-width: 0;">
                                <div class="icon-box-3d" style="width: 46px; height: 46px; border-radius: 14px; font-size: 22px; flex-shrink: 0; background: var(--ula-palm-900);">
                                    <span class="material-symbols-rounded" style="color: white; font-size: 24px;">corporate_fare</span>
                                </div>
                                <div>
                                    <div style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap; margin-bottom: 2px;">
                                        <h2 style="font-size: 17px; font-weight: 800; color: var(--ula-text-primary); margin: 0;">
                                            {{ $off->name }}
                                        </h2>
                                        @if($off->city_location)
                                            <span class="nav-badge-pill" style="font-size: 11px; display: inline-flex; align-items: center; gap: 3px;">
                                                <span class="material-symbols-rounded" style="font-size: 13px;">location_on</span>
                                                <span>{{ $off->city_location }}</span>
                                            </span>
                                        @endif
                                        @if($off->is_default)
                                            <span class="nav-badge-pill" style="font-size: 11px; background: rgba(211, 165, 83, 0.15); color: var(--ula-gold-600); border-color: rgba(211, 165, 83, 0.35); display: inline-flex; align-items: center; gap: 3px;">
                                                <span class="material-symbols-rounded" style="font-size: 13px; color: var(--ula-gold-400);">star</span>
                                                <span>{{ __('Primary Headquarters') }}</span>
                                            </span>
                                        @endif
                                        <span class="nav-badge-pill" style="font-size: 11px; display: inline-flex; align-items: center; gap: 4px;">
                                            <span class="material-symbols-rounded" style="font-size: 13px;">meeting_room</span>
                                            <span>{{ $branchRooms->count() }} {{ __('Rooms Configured') }}</span>
                                        </span>
                                    </div>
                                    <div style="font-size: 12px; color: var(--ula-text-muted); display: flex; align-items: center; gap: 12px; flex-wrap: wrap;">
                                        <span style="display: inline-flex; align-items: center; gap: 4px;">
                                            <span class="material-symbols-rounded" style="font-size: 14px;">map</span>
                                            <span>{{ __('Active Map Blueprint:') }} <strong style="color: var(--ula-text-primary);">{{ $publishedMap?->name ?? __('Standard Layout') }}</strong></span>
                                        </span>
                                        @if($publishedMap)
                                            <span style="display: inline-flex; align-items: center; gap: 4px; font-family: 'IBM Plex Mono', monospace;">
                                                <span class="material-symbols-rounded" style="font-size: 14px;">square_foot</span>
                                                <span>{{ $publishedMap->width }}x{{ $publishedMap->height }} tiles ({{ $publishedMap->tile_size }}px)</span>
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <!-- Branch Fast Actions -->
                            <div style="display: flex; gap: 8px; align-items: center;">
                                <x-btn variant="primary" size="sm" href="{{ route('office', ['office_id' => $off->id]) }}" icon="login">
                                    {{ __('Enter This Office') }}
                                </x-btn>
                                <x-btn variant="secondary" size="sm" href="{{ route('editor') }}" icon="design_services">
                                    {{ __('Edit Blueprint') }}
                                </x-btn>
                            </div>
                        </div>

                        <!-- Branch Content: Visual Cards Grid View -->
                        <div class="branch-rooms-cards-view" style="padding: 24px;">
                            @if($branchRooms->count() > 0)
                                <div class="rooms-grid-container" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 16px;">
                                    @foreach($branchRooms as $r)
                                        @php
                                            $roomColor = $r->color ?: '#142B24';
                                            $bounds = $r->bounds ?? ['x' => 0, 'y' => 0, 'width' => 10, 'height' => 10];
                                            $bx = $bounds['x'] ?? 0;
                                            $by = $bounds['y'] ?? 0;
                                            $bw = $bounds['width'] ?? ($bounds['w'] ?? 10);
                                            $bh = $bounds['height'] ?? ($bounds['h'] ?? 10);

                                            // Icon & Type determination
                                            $rType = strtolower($r->type ?? 'meeting');
                                            $typeIcon = 'group';
                                            $typeLabel = __('Conference / Meeting');
                                            if (str_contains($rType, 'office') || str_contains(strtolower($r->name), 'مكتب')) {
                                                $typeIcon = 'work';
                                                $typeLabel = __('Private Office');
                                            } elseif (str_contains($rType, 'lounge') || str_contains(strtolower($r->name), 'استراحة')) {
                                                $typeIcon = 'local_cafe';
                                                $typeLabel = __('Lounge & Breakout');
                                            } elseif (str_contains($rType, 'auditorium') || str_contains(strtolower($r->name), 'مسرح') || str_contains(strtolower($r->name), 'قاعة')) {
                                                $typeIcon = 'theater_comedy';
                                                $typeLabel = __('Auditorium / Hall');
                                            } elseif (str_contains($rType, 'brainstorm') || str_contains(strtolower($r->name), 'عصف')) {
                                                $typeIcon = 'psychology';
                                                $typeLabel = __('Ideation Space');
                                            }
                                        @endphp

                                        <div class="room-spatial-card" data-room-id="{{ $r->id }}" data-room-name="{{ strtolower($r->name) }}" data-office-name="{{ strtolower($off->name) }}" data-room-type="{{ $rType }}" style="background: var(--ula-surface-card); border: 1px solid var(--ula-border-subtle); border-radius: var(--ula-radius-lg); padding: 18px; position: relative; display: flex; flex-direction: column; justify-content: space-between; transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1); box-shadow: var(--ula-shadow-sm);">
                                            
                                            <!-- Color Accent Top Strip -->
                                            <div style="position: absolute; top: 0; inset-inline-start: 18px; inset-inline-end: 18px; height: 3px; border-radius: 0 0 4px 4px; background: {{ $roomColor }}; opacity: 0.85;"></div>

                                            <div>
                                                <!-- Card Top Row: Room Type Badge & Door Lock Status -->
                                                <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 12px; margin-top: 4px;">
                                                    <div style="display: flex; align-items: center; gap: 8px;">
                                                        <div style="width: 36px; height: 36px; border-radius: 10px; background: rgba(20, 43, 36, 0.08); border: 1px solid var(--ula-border-subtle); display: flex; align-items: center; justify-content: center; color: var(--ula-highlight-default);">
                                                            <span class="material-symbols-rounded" style="font-size: 20px;">{{ $typeIcon }}</span>
                                                        </div>
                                                        <div>
                                                            <h3 style="font-size: 15px; font-weight: 700; color: var(--ula-text-primary); margin: 0; line-height: 1.2;">
                                                                {{ $r->name }}
                                                            </h3>
                                                            <span style="font-size: 11px; color: var(--ula-text-muted); font-weight: 500;">{{ $typeLabel }}</span>
                                                        </div>
                                                    </div>

                                                    <!-- Door Status Pill -->
                                                    @if($r->access_mode === 'private')
                                                        <x-badge variant="scheduled" icon="lock">{{ __('Locked / Private') }}</x-badge>
                                                    @else
                                                        <x-badge variant="live" icon="lock_open">{{ __('Open') }}</x-badge>
                                                    @endif
                                                </div>

                                                <!-- Room Spatial Details Specs (Attribution to Office & Map) -->
                                                <div style="background: var(--ula-sand-100); border: 1px solid var(--ula-border-subtle); border-radius: 12px; padding: 12px; margin-bottom: 14px; display: flex; flex-direction: column; gap: 6px; font-size: 12px;">
                                                    <!-- Office Location Attribution -->
                                                    <div style="display: flex; justify-content: space-between; align-items: center;">
                                                        <span style="color: var(--ula-text-muted); font-size: 11px; display: flex; align-items: center; gap: 4px;">
                                                            <span class="material-symbols-rounded" style="font-size: 13px;">corporate_fare</span>
                                                            <span>{{ __('Office Branch:') }}</span>
                                                        </span>
                                                        <strong style="color: var(--ula-palm-900); font-weight: 700;">{{ $off->name }}</strong>
                                                    </div>
                                                    <!-- Map Blueprint Name -->
                                                    <div style="display: flex; justify-content: space-between; align-items: center;">
                                                        <span style="color: var(--ula-text-muted); font-size: 11px; display: flex; align-items: center; gap: 4px;">
                                                            <span class="material-symbols-rounded" style="font-size: 13px;">map</span>
                                                            <span>{{ __('Map Floorplan:') }}</span>
                                                        </span>
                                                        <span style="color: var(--ula-text-primary); font-weight: 600; font-size: 11px;">{{ $r->map?->name ?? $publishedMap?->name ?? __('Main Floor') }}</span>
                                                    </div>
                                                    <!-- Capacity -->
                                                    <div style="display: flex; justify-content: space-between; align-items: center;">
                                                        <span style="color: var(--ula-text-muted); font-size: 11px; display: flex; align-items: center; gap: 4px;">
                                                            <span class="material-symbols-rounded" style="font-size: 13px;">chair</span>
                                                            <span>{{ __('Capacity:') }}</span>
                                                        </span>
                                                        <span style="font-weight: 700; color: var(--ula-text-primary); font-family: 'IBM Plex Mono', monospace;">{{ $r->capacity }} {{ __('Seats') }}</span>
                                                    </div>
                                                    <!-- Spatial Bounds & Location -->
                                                    <div style="display: flex; justify-content: space-between; align-items: center;">
                                                        <span style="color: var(--ula-text-muted); font-size: 11px; display: flex; align-items: center; gap: 4px;">
                                                            <span class="material-symbols-rounded" style="font-size: 13px;">location_on</span>
                                                            <span>{{ __('Floor Coordinates:') }}</span>
                                                        </span>
                                                        <code style="background: var(--ula-sand-200); padding: 2px 6px; border-radius: 4px; font-size: 10px; color: var(--ula-text-secondary); border: 1px solid var(--ula-border-subtle); font-family: 'IBM Plex Mono', monospace;">
                                                            X:{{ $bx }}, Y:{{ $by }} ({{ $bw }}x{{ $bh }})
                                                        </code>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Card Quick Actions Footer -->
                                            <div style="display: flex; gap: 6px; align-items: center; border-top: 1px dashed var(--ula-border-subtle); padding-top: 12px;">
                                                <x-btn variant="primary" size="sm" href="{{ route('office', ['office_id' => $off->id, 'room_id' => $r->id]) }}" icon="login" style="flex: 1; justify-content: center;">
                                                    {{ __('Enter Room') }}
                                                </x-btn>
                                                <button type="button" onclick="openRoomGuestModal('{{ $r->id }}', '{{ addslashes($r->name) }}')" class="nx-btn nx-btn-secondary nx-btn-sm" style="padding: 7px 10px; font-size: 11px;" title="{{ __('Generate Guest Link for this room') }}">
                                                    <span class="material-symbols-rounded" style="font-size: 14px;">link</span>
                                                    <span>{{ __('Guest Link') }}</span>
                                                </button>
                                                <a href="{{ route('editor') }}" class="nx-btn nx-btn-outline nx-btn-sm" style="padding: 7px 10px; font-size: 11px;" title="{{ __('Edit in Map Editor') }}">
                                                    <span class="material-symbols-rounded" style="font-size: 14px;">design_services</span>
                                                </a>
                                            </div>

                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <div style="text-align: center; padding: 48px 20px; color: var(--ula-text-muted); background: var(--ula-surface-card); border: 1px dashed var(--ula-border-subtle); border-radius: var(--ula-radius-lg);">
                                    <span class="material-symbols-rounded" style="font-size: 36px; color: var(--ula-sand-400); display: block; margin-bottom: 8px;">meeting_room</span>
                                    <h4 style="font-size: 14px; font-weight: 700; color: var(--ula-text-primary); margin-bottom: 4px;">{{ __('No rooms created in this office branch yet.') }}</h4>
                                    <p style="font-size: 12px; margin-bottom: 14px; color: var(--ula-text-secondary);">{{ __('Use the Floor Map Editor or AI generator to design rooms for') }} {{ $off->name }}.</p>
                                    <x-btn variant="primary" size="sm" href="{{ route('editor') }}" icon="design_services">
                                        {{ __('Design Rooms in Editor') }}
                                    </x-btn>
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
                                                            <div style="width: 10px; height: 10px; border-radius: 50%; background: {{ $r->color ?: '#142B24' }}; flex-shrink: 0;"></div>
                                                            <strong style="color: var(--ula-text-primary); font-size: 13px; display: inline-flex; align-items: center; gap: 4px;">
                                                                <span class="material-symbols-rounded" style="font-size: 15px;">meeting_room</span>
                                                                <span>{{ $r->name }}</span>
                                                            </strong>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <strong style="color: var(--ula-palm-900); display: inline-flex; align-items: center; gap: 4px;">
                                                            <span class="material-symbols-rounded" style="font-size: 14px;">corporate_fare</span>
                                                            <span>{{ $off->name }}</span>
                                                        </strong>
                                                        @if($off->city_location)
                                                            <span style="font-size: 11px; color: var(--ula-text-muted);">({{ $off->city_location }})</span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        <span style="font-size: 12px; color: var(--ula-text-secondary); display: inline-flex; align-items: center; gap: 4px;">
                                                            <span class="material-symbols-rounded" style="font-size: 14px;">map</span>
                                                            <span>{{ $r->map?->name ?? $publishedMap?->name ?? __('Default Blueprint') }}</span>
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <span class="nav-badge-pill" style="text-transform: capitalize;">{{ $r->type ?: 'Meeting' }}</span>
                                                    </td>
                                                    <td>
                                                        <span style="font-weight: 700; font-family: 'IBM Plex Mono', monospace;">{{ $r->capacity }} {{ __('Seats') }}</span>
                                                    </td>
                                                    <td>
                                                        <code style="background: var(--ula-sand-200); border: 1px solid var(--ula-border-subtle); padding: 3px 8px; border-radius: 6px; font-size: 11px; color: var(--ula-text-secondary); font-family: 'IBM Plex Mono', monospace;">
                                                            X:{{ $bx }}, Y:{{ $by }} ({{ $bw }}x{{ $bh }})
                                                        </code>
                                                    </td>
                                                    <td>
                                                        @if($r->access_mode === 'private')
                                                            <x-badge variant="scheduled" icon="lock">{{ __('Locked') }}</x-badge>
                                                        @else
                                                            <x-badge variant="live" icon="lock_open">{{ __('Open') }}</x-badge>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        <div style="display: flex; gap: 6px; justify-content: center; align-items: center;">
                                                            <x-btn variant="primary" size="sm" href="{{ route('office', ['office_id' => $off->id, 'room_id' => $r->id]) }}" icon="login">
                                                                {{ __('Enter') }}
                                                            </x-btn>
                                                            <button type="button" onclick="openRoomGuestModal('{{ $r->id }}', '{{ addslashes($r->name) }}')" class="nx-btn nx-btn-secondary nx-btn-sm" style="padding: 6px 10px; font-size: 11px;" title="{{ __('Generate Guest Link') }}">
                                                                <span class="material-symbols-rounded" style="font-size: 14px;">link</span>
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
            <div id="rooms-no-results-hint" style="display: none; text-align: center; padding: 60px 20px; background: var(--ula-surface-card); border: 1px dashed var(--ula-border-subtle); border-radius: var(--ula-radius-xl); margin-top: 20px;">
                <span class="material-symbols-rounded" style="font-size: 40px; color: var(--ula-sand-400); display: block; margin-bottom: 12px;">search_off</span>
                <h3 style="font-size: 16px; font-weight: 800; color: var(--ula-text-primary); margin-bottom: 6px;">{{ __('No matching workplace rooms found') }}</h3>
                <p style="font-size: 13px; color: var(--ula-text-secondary); max-width: 400px; margin: 0 auto 16px auto;">
                    {{ __('Try adjusting your branch filter, clearing the search query, or selecting another room category.') }}
                </p>
                <x-btn variant="primary" size="sm" onclick="resetRoomsFilters()" icon="restart_alt">
                    {{ __('Reset Filters') }}
                </x-btn>
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
                    pill.style.background = 'var(--ula-surface-page-alt)';
                    pill.style.color = 'var(--ula-text-primary)';
                    pill.style.borderColor = 'var(--ula-border-subtle)';
                });

                const activePill = document.getElementById(`branch-pill-${branchId}`);
                if (activePill) {
                    activePill.classList.add('active');
                    activePill.style.background = 'var(--ula-palm-900)';
                    activePill.style.color = 'white';
                    activePill.style.borderColor = 'var(--ula-palm-900)';
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
                        cardsBtn.style.background = 'var(--ula-palm-900)';
                        cardsBtn.style.color = 'white';
                        cardsBtn.style.boxShadow = '';
                    }
                    if (tableBtn) {
                        tableBtn.style.background = 'transparent';
                        tableBtn.style.color = 'var(--ula-text-secondary)';
                        tableBtn.style.boxShadow = 'none';
                    }
                    document.querySelectorAll('.branch-rooms-cards-view').forEach(el => el.style.display = 'block');
                    document.querySelectorAll('.branch-rooms-table-view').forEach(el => el.style.display = 'none');
                } else {
                    if (tableBtn) {
                        tableBtn.style.background = 'var(--ula-palm-900)';
                        tableBtn.style.color = 'white';
                        tableBtn.style.boxShadow = '';
                    }
                    if (cardsBtn) {
                        cardsBtn.style.background = 'transparent';
                        cardsBtn.style.color = 'var(--ula-text-secondary)';
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
