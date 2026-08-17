<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ __('Map Editor') }} & {{ __('Floor Designer') }} — {{ $organization->name }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;800;900&family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            /* Clean White & Saudi Brand Theme */
            --bg-body: #f8fafc;
            --bg-panel: #ffffff;
            --bg-card: #f8fafc;
            --border-panel: #e2e8f0;
            --border-hover: #cbd5e1;

            /* Saudi Brand Colors from color.webp */
            --brand-teal: #00b4b3;
            --brand-pine: #00726c;
            --brand-ocean: #004862;
            --brand-navy: #012c41;
            --brand-green: #006847;
            --brand-lime: #a7c545;
            --brand-gold: #ffd136;
            --brand-orange: #f57b36;
            --brand-coral: #ff3600;
            --brand-crimson: #d20005;

            --text-main: #012c41;
            --text-muted: #64748b;
            --text-dim: #94a3b8;

            --font-family: {{ app()->getLocale() === 'ar' ? "'Cairo', 'Inter', sans-serif" : "'Inter', 'Cairo', sans-serif" }};
        }

        * { margin: 0; padding: 0; box-sizing: border-box; font-family: var(--font-family); }
        body {
            background: var(--bg-body);
            color: var(--text-main);
            height: 100vh;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            user-select: none;
        }

        /* ── Top Bar ── */
        .editor-header {
            height: 62px;
            background: var(--bg-panel);
            border-bottom: 1px solid var(--border-panel);
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 20px;
            z-index: 50;
            box-shadow: 0 2px 10px rgba(1, 44, 65, 0.04);
        }

        .header-left {
            display: flex;
            align-items: center;
            gap: 14px;
        }

        .back-btn {
            background: #f8fafc;
            border: 1px solid var(--border-panel);
            color: var(--brand-navy);
            padding: 7px 14px;
            border-radius: 10px;
            font-size: 13px;
            font-weight: 700;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 6px;
            transition: all 0.2s;
        }
        .back-btn:hover {
            border-color: var(--brand-teal);
            background: #ffffff;
            color: var(--brand-teal);
        }

        .map-title-group {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .map-name {
            font-size: 16px;
            font-weight: 900;
            color: var(--brand-navy);
            letter-spacing: -0.3px;
        }

        .version-badge {
            background: rgba(0, 180, 179, 0.1);
            border: 1px solid rgba(0, 180, 179, 0.3);
            color: var(--brand-teal);
            padding: 3px 10px;
            border-radius: 6px;
            font-size: 11px;
            font-weight: 800;
        }

        .header-center {
            display: flex;
            align-items: center;
            gap: 6px;
            background: #f8fafc;
            padding: 4px 6px;
            border-radius: 12px;
            border: 1px solid var(--border-panel);
        }

        .tool-chip {
            background: transparent;
            border: 1px solid transparent;
            color: var(--text-muted);
            padding: 6px 14px;
            border-radius: 8px;
            font-size: 12px;
            font-weight: 700;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 6px;
            transition: all 0.18s;
        }
        .tool-chip:hover {
            color: var(--brand-navy);
            background: #ffffff;
        }
        .tool-chip.active {
            background: var(--brand-teal);
            color: white;
            border-color: var(--brand-teal);
            box-shadow: 0 2px 8px rgba(0, 180, 179, 0.3);
        }

        .header-right {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .btn-lang-toggle {
            background: #f8fafc;
            border: 1px solid var(--border-panel);
            color: var(--brand-navy);
            padding: 7px 12px;
            border-radius: 10px;
            font-size: 12px;
            font-weight: 800;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 6px;
            transition: all 0.2s;
        }
        .btn-lang-toggle:hover {
            border-color: var(--brand-teal);
            color: var(--brand-teal);
        }

        .action-btn {
            padding: 8px 16px;
            border-radius: 10px;
            font-size: 13px;
            font-weight: 800;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 6px;
            transition: all 0.2s;
            text-decoration: none;
            border: none;
        }

        .action-btn.secondary {
            background: #ffffff;
            border: 1px solid var(--border-panel);
            color: var(--brand-navy);
        }
        .action-btn.secondary:hover {
            border-color: var(--brand-teal);
            background: #f8fafc;
        }

        .action-btn.primary {
            background: var(--brand-teal);
            color: white;
            box-shadow: 0 4px 12px rgba(0, 180, 179, 0.3);
        }
        .action-btn.primary:hover {
            background: var(--brand-pine);
        }

        .action-btn.success {
            background: linear-gradient(135deg, var(--brand-green), #004d34);
            color: white;
            box-shadow: 0 4px 12px rgba(0, 104, 71, 0.25);
        }
        .action-btn.success:hover {
            opacity: 0.95;
        }

        /* ── Main Workspace ── */
        .editor-workspace {
            flex: 1;
            display: flex;
            position: relative;
            overflow: hidden;
        }

        /* ── Canvas Viewport ── */
        .canvas-container {
            flex: 1;
            position: relative;
            background: #edf2f7;
            overflow: hidden;
            cursor: crosshair;
        }

        canvas#editor-canvas {
            display: block;
            width: 100%;
            height: 100%;
        }

        /* ── Floating Canvas View Controls (Zoom, Reset, Center) ── */
        .canvas-view-tools {
            position: absolute;
            top: 20px;
            inset-inline-start: 20px;
            background: #ffffff;
            border: 1px solid var(--border-panel);
            border-radius: 12px;
            display: flex;
            flex-direction: column;
            gap: 4px;
            padding: 6px;
            z-index: 45;
            box-shadow: 0 10px 25px rgba(1, 44, 65, 0.08);
        }
        .canvas-tool-btn {
            width: 36px;
            height: 36px;
            border-radius: 8px;
            border: none;
            background: transparent;
            color: var(--text-muted);
            font-size: 15px;
            font-weight: 800;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.15s;
        }
        .canvas-tool-btn:hover {
            background: #f1f5f9;
            color: var(--brand-navy);
        }

        /* ── Kumospace-Style Right Customizer Drawer ── */
        .customize-drawer {
            width: 340px;
            background: var(--bg-panel);
            border-inline-start: 1px solid var(--border-panel);
            display: flex;
            flex-direction: column;
            z-index: 40;
            box-shadow: -4px 0 20px rgba(1, 44, 65, 0.05);
            transition: transform 0.25s ease;
        }
        .customize-drawer.collapsed {
            transform: translateX({{ app()->getLocale() === 'ar' ? '-340px' : '340px' }});
            margin-inline-end: -340px;
        }

        .drawer-header {
            padding: 16px 20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            border-bottom: 1px solid var(--border-panel);
            background: #ffffff;
        }
        .drawer-title-dropdown {
            font-size: 15px;
            font-weight: 900;
            color: var(--brand-navy);
            display: flex;
            align-items: center;
            gap: 6px;
            cursor: pointer;
        }
        .drawer-close-btn {
            background: none;
            border: none;
            color: var(--text-dim);
            font-size: 18px;
            cursor: pointer;
            padding: 4px;
            border-radius: 6px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .drawer-close-btn:hover {
            background: #f1f5f9;
            color: var(--brand-navy);
        }

        /* ── Sub Navigation Tabs (Furniture / Rooms / Settings) ── */
        .drawer-tabs {
            display: flex;
            padding: 0 16px;
            border-bottom: 1px solid var(--border-panel);
            background: #ffffff;
            gap: 20px;
        }
        .drawer-tab {
            padding: 12px 4px;
            font-size: 13px;
            font-weight: 800;
            color: var(--text-muted);
            cursor: pointer;
            position: relative;
            transition: all 0.2s;
        }
        .drawer-tab:hover {
            color: var(--brand-navy);
        }
        .drawer-tab.active {
            color: var(--brand-teal);
            border-bottom: 2px solid var(--brand-teal);
        }

        .drawer-content {
            flex: 1;
            overflow-y: auto;
            padding: 16px;
            display: flex;
            flex-direction: column;
            gap: 16px;
        }

        /* ── Search Furniture Input ── */
        .search-box-wrapper {
            position: relative;
            width: 100%;
        }
        .search-input {
            width: 100%;
            background: #f8fafc;
            border: 1px solid var(--border-panel);
            border-radius: 10px;
            padding: 10px 14px;
            font-size: 12px;
            font-weight: 600;
            color: var(--brand-navy);
            outline: none;
            transition: all 0.2s;
        }
        .search-input:focus {
            border-color: var(--brand-teal);
            background: #ffffff;
            box-shadow: 0 0 0 3px rgba(0, 180, 179, 0.12);
        }

        /* ── Accordion Category Section ── */
        .category-accordion {
            border: 1px solid var(--border-panel);
            border-radius: 12px;
            overflow: hidden;
            background: #ffffff;
        }
        .category-header {
            padding: 12px 14px;
            background: #f8fafc;
            display: flex;
            align-items: center;
            justify-content: space-between;
            cursor: pointer;
            font-size: 12px;
            font-weight: 800;
            color: var(--brand-navy);
            user-select: none;
        }
        .category-header:hover {
            background: #f1f5f9;
        }
        .category-body {
            padding: 12px;
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 10px;
        }

        /* ── 2-Column Furniture Cards (Matching Kumospace Reference) ── */
        .furn-card {
            background: #ffffff;
            border: 1px solid var(--border-panel);
            border-radius: 12px;
            padding: 12px 8px 10px;
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
            cursor: pointer;
            transition: all 0.2s;
            position: relative;
        }
        .furn-card:hover {
            border-color: var(--brand-teal);
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(0, 180, 179, 0.12);
        }
        .furn-card.active {
            border-color: var(--brand-teal);
            background: rgba(0, 180, 179, 0.04);
            box-shadow: 0 0 0 2px var(--brand-teal);
        }
        .furn-preview-icon {
            font-size: 28px;
            margin-bottom: 6px;
            display: flex;
            align-items: center;
            justify-content: center;
            height: 38px;
        }
        .furn-name {
            font-size: 11px;
            font-weight: 800;
            color: var(--brand-navy);
            margin-bottom: 6px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            width: 100%;
        }
        .color-swatches {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 4px;
        }
        .swatch-dot {
            width: 9px;
            height: 9px;
            border-radius: 50%;
            cursor: pointer;
            transition: transform 0.15s;
        }
        .swatch-dot:hover {
            transform: scale(1.3);
        }

        /* ── Room Template Cards ── */
        .room-card {
            background: #ffffff;
            border: 1px solid var(--border-panel);
            border-radius: 12px;
            padding: 14px;
            display: flex;
            align-items: center;
            gap: 12px;
            cursor: pointer;
            transition: all 0.2s;
        }
        .room-card:hover {
            border-color: var(--brand-teal);
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(0, 180, 179, 0.1);
        }
        .room-card.active {
            border-color: var(--brand-teal);
            background: rgba(0, 180, 179, 0.05);
            box-shadow: 0 0 0 2px var(--brand-teal);
        }

        /* ── Selected Item Properties Panel ── */
        .properties-box {
            background: #ffffff;
            border: 1px solid var(--border-panel);
            border-radius: 12px;
            padding: 14px;
            display: flex;
            flex-direction: column;
            gap: 12px;
        }
        .prop-field {
            display: flex;
            flex-direction: column;
            gap: 4px;
        }
        .prop-label {
            font-size: 11px;
            font-weight: 800;
            color: var(--brand-ocean);
            text-transform: uppercase;
        }
        .prop-input {
            width: 100%;
            background: #f8fafc;
            border: 1px solid var(--border-panel);
            border-radius: 8px;
            padding: 8px 10px;
            color: var(--brand-navy);
            font-size: 12px;
            font-weight: 600;
            outline: none;
        }
        .prop-input:focus {
            border-color: var(--brand-teal);
        }

        /* ── Notification Toast ── */
        .toast {
            position: fixed;
            bottom: 24px;
            inset-inline-start: 24px;
            background: var(--brand-green);
            color: white;
            padding: 12px 22px;
            border-radius: 12px;
            font-size: 13px;
            font-weight: 800;
            box-shadow: 0 10px 25px -5px rgba(0, 104, 71, 0.4);
            display: none;
            z-index: 1000;
            animation: slideUp 0.3s ease;
        }
        @keyframes slideUp {
            from { transform: translateY(20px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }
    </style>
</head>
<body>

    <!-- Header -->
    <header class="editor-header">
        <div class="header-left">
            <a href="{{ route('dashboard') }}" class="back-btn">
                <span>←</span>
                <span>{{ __('Dashboard') }}</span>
            </a>
            <div class="map-title-group">
                <div class="map-name">{{ __('Map Editor') }}: {{ $map->name }} <span style="font-size: 12px; font-weight: 700; color: var(--text-muted);">({{ $organization->name }})</span></div>
                <span class="version-badge" id="header-version-badge">v{{ $map->version }} ({{ ucfirst($map->status) }})</span>
            </div>
        </div>

        <div class="header-center">
            <button class="tool-chip active" id="tool-select" onclick="setTool('select')">
                <span>🖱️</span> {{ __('Select') }}
            </button>
            <button class="tool-chip" id="tool-room" onclick="setTool('room')">
                <span>🏢</span> {{ __('Add Room') }}
            </button>
            <button class="tool-chip" id="tool-zone" onclick="setTool('zone')">
                <span>🎙️</span> {{ __('Audio Zone') }}
            </button>
            <button class="tool-chip" id="tool-object" onclick="setTool('object')">
                <span>🪑</span> {{ __('Furniture') }}
            </button>
            <button class="tool-chip" id="btn-delete-selected" onclick="deleteSelectedItem()" style="color: var(--brand-crimson);">
                <span>🗑️</span> {{ __('Delete') }}
            </button>
        </div>

        <div class="header-right">
            @if(app()->getLocale() === 'ar')
                <a href="{{ route('lang.switch', 'en') }}" class="btn-lang-toggle" title="Switch to English">🌐 English</a>
            @else
                <a href="{{ route('lang.switch', 'ar') }}" class="btn-lang-toggle" title="التبديل إلى العربية">🌐 العربية</a>
            @endif

            <button class="action-btn secondary" id="btn-save-draft" onclick="saveMapDraft()">
                <span>💾</span> {{ __('Save Draft') }}
            </button>
            <button class="action-btn success" id="btn-publish" onclick="publishMap()">
                <span>🚀</span> {{ __('Publish Map') }}
            </button>
            <a href="{{ route('office') }}" class="action-btn primary" target="_blank">
                <span>👁️</span> {{ __('Test Live') }}
            </a>
        </div>
    </header>

    <!-- Main Workspace -->
    <div class="editor-workspace">

        <!-- Canvas Viewport -->
        <div class="canvas-container" id="canvas-container">
            <canvas id="editor-canvas"></canvas>

            <!-- Floating Canvas View Controls (Kumospace Style) -->
            <div class="canvas-view-tools">
                <button class="canvas-tool-btn" onclick="toggleCustomizeDrawer()" title="{{ __('Toggle Customize Drawer') }}">🪑</button>
                <button class="canvas-tool-btn" onclick="zoomIn()" title="{{ __('Zoom In') }}">➕</button>
                <button class="canvas-tool-btn" onclick="zoomOut()" title="{{ __('Zoom Out') }}">➖</button>
                <button class="canvas-tool-btn" onclick="resetView()" title="{{ __('Reset View') }}">🏠</button>
                <button class="canvas-tool-btn" onclick="toggleGrid()" title="{{ __('Toggle Grid') }}">🔲</button>
            </div>
        </div>

        <!-- Kumospace-Style Right Customizer Drawer -->
        <aside class="customize-drawer" id="customize-drawer">
            <div class="drawer-header">
                <div class="drawer-title-dropdown">
                    <span>✨</span>
                    <span>{{ __('Customize') }} ▾</span>
                </div>
                <button class="drawer-close-btn" onclick="toggleCustomizeDrawer()" title="{{ __('Close') }}">✕</button>
            </div>

            <!-- Sub Navigation Tabs -->
            <div class="drawer-tabs">
                <div class="drawer-tab active" id="tab-btn-furniture" onclick="switchDrawerTab('furniture')">
                    {{ __('Furniture') }}
                </div>
                <div class="drawer-tab" id="tab-btn-rooms" onclick="switchDrawerTab('rooms')">
                    {{ __('Rooms') }}
                </div>
                <div class="drawer-tab" id="tab-btn-settings" onclick="switchDrawerTab('settings')">
                    {{ __('Settings') }}
                </div>
            </div>

            <div class="drawer-content">

                <!-- 1. FURNITURE TAB -->
                <div id="drawer-view-furniture" style="display: flex; flex-direction: column; gap: 14px;">
                    <!-- Search Input -->
                    <div class="search-box-wrapper">
                        <input type="text" id="furniture-search" class="search-input" placeholder="🔍 {{ __('Search furniture, seating, tables...') }}" oninput="filterFurniture(this.value)">
                    </div>

                    @foreach($furnitureCategories as $cat)
                    <div class="category-accordion" id="cat-{{ $cat->slug }}">
                        <div class="category-header" onclick="toggleAccordion('cat-{{ $cat->slug }}')">
                            <span>{{ $cat->icon }} {{ $cat->name }}</span>
                            <span class="acc-icon">▴</span>
                        </div>
                        <div class="category-body">
                            @foreach($cat->items as $item)
                                <div class="furn-card" onclick="selectFurnitureItem('{{ $item->slug }}', '{{ $item->colors[0] ?? '#00b4b3' }}', '{{ $item->image_url }}', {{ $item->width }}, {{ $item->height }}, {{ $item->collision ? 'true' : 'false' }})">
                                    <div class="furn-preview-icon">
                                        @if($item->image_url)
                                            <img src="{{ $item->image_url }}" alt="{{ $item->name }}" style="max-height: 36px; max-width: 100%; object-fit: contain;">
                                        @else
                                            {{ $item->icon }}
                                        @endif
                                    </div>
                                    <div class="furn-name">{{ $item->name }}</div>
                                    @if(!empty($item->colors))
                                    <div class="color-swatches">
                                        @foreach($item->colors as $col)
                                            <span class="swatch-dot" style="background: {{ $col }};" onclick="event.stopPropagation(); selectFurnitureItem('{{ $item->slug }}', '{{ $col }}', '{{ $item->image_url }}', {{ $item->width }}, {{ $item->height }}, {{ $item->collision ? 'true' : 'false' }})"></span>
                                        @endforeach
                                    </div>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>
                    @endforeach
                </div>

                <!-- 2. ROOMS TAB -->
                <div id="drawer-view-rooms" style="display: none; flex-direction: column; gap: 14px;">
                    <div style="font-size: 12px; color: var(--text-muted); font-weight: 600;">
                        {{ __('Choose a room template, then click and drag on the floor canvas to draw.') }}
                    </div>

                    <div class="room-card active" onclick="setRoomTemplate('meeting', '#00b4b3')">
                        <div style="font-size: 26px;">👥</div>
                        <div>
                            <strong style="font-size: 13px; color: var(--brand-navy);">{{ __('Meeting Room') }}</strong>
                            <div style="font-size: 11px; color: var(--text-muted);">{{ __('Open collaboration area for teams') }}</div>
                        </div>
                    </div>

                    <div class="room-card" onclick="setRoomTemplate('private', '#f57b36')">
                        <div style="font-size: 26px;">🔒</div>
                        <div>
                            <strong style="font-size: 13px; color: var(--brand-navy);">{{ __('Private Office') }}</strong>
                            <div style="font-size: 11px; color: var(--text-muted);">{{ __('Locked door with knock-to-enter approval') }}</div>
                        </div>
                    </div>

                    <div class="room-card" onclick="setRoomTemplate('reception', '#006847')">
                        <div style="font-size: 26px;">🛎️</div>
                        <div>
                            <strong style="font-size: 13px; color: var(--brand-navy);">{{ __('Reception & Lobby') }}</strong>
                            <div style="font-size: 11px; color: var(--text-muted);">{{ __('Welcoming area for guests & visitors') }}</div>
                        </div>
                    </div>

                    <div class="room-card" onclick="setRoomTemplate('lounge', '#004862')">
                        <div style="font-size: 26px;">☕</div>
                        <div>
                            <strong style="font-size: 13px; color: var(--brand-navy);">{{ __('Breakout Lounge') }}</strong>
                            <div style="font-size: 11px; color: var(--text-muted);">{{ __('Casual coffee and social zone') }}</div>
                        </div>
                    </div>

                    <!-- Selected Properties Inspector -->
                    <div class="properties-box" style="margin-top: 10px;">
                        <strong style="font-size: 12px; color: var(--brand-navy);">{{ __('Selected Item Properties') }}</strong>
                        <div class="prop-field">
                            <label class="prop-label">{{ __('Name') }}</label>
                            <input type="text" class="prop-input" id="prop-name" value="" oninput="updateSelectedProp('name', this.value)">
                        </div>
                        <div class="prop-field" id="prop-color-group">
                            <label class="prop-label">{{ __('Theme Color') }}</label>
                            <input type="color" class="prop-input" id="prop-color" value="#00b4b3" style="height: 38px; padding: 2px;" oninput="updateSelectedProp('color', this.value)">
                        </div>
                        <div class="prop-field" id="prop-capacity-group">
                            <label class="prop-label">{{ __('Seat Capacity') }}</label>
                            <input type="number" class="prop-input" id="prop-capacity" value="10" min="1" max="100" oninput="updateSelectedProp('capacity', parseInt(this.value) || 1)">
                        </div>
                    </div>
                </div>

                <!-- 3. SETTINGS TAB -->
                <div id="drawer-view-settings" style="display: none; flex-direction: column; gap: 14px;">
                    <div class="properties-box">
                        <strong style="font-size: 12px; color: var(--brand-navy);">{{ __('Floor Metrics') }}</strong>
                        <div style="display: flex; justify-content: space-between; font-size: 12px; color: var(--text-muted); border-bottom: 1px solid var(--border-panel); padding-bottom: 6px;">
                            <span>{{ __('Total Rooms') }}:</span>
                            <strong style="color: var(--brand-navy);" id="stat-rooms-count">0</strong>
                        </div>
                        <div style="display: flex; justify-content: space-between; font-size: 12px; color: var(--text-muted); border-bottom: 1px solid var(--border-panel); padding-bottom: 6px;">
                            <span>{{ __('Audio Zones') }}:</span>
                            <strong style="color: var(--brand-navy);" id="stat-zones-count">0</strong>
                        </div>
                        <div style="display: flex; justify-content: space-between; font-size: 12px; color: var(--text-muted);">
                            <span>{{ __('Furniture Placed') }}:</span>
                            <strong style="color: var(--brand-navy);" id="stat-objects-count">0</strong>
                        </div>
                    </div>

                    <div class="properties-box">
                        <strong style="font-size: 12px; color: var(--brand-navy);">{{ __('Version History') }}</strong>
                        @forelse($map->versions as $ver)
                            <div style="display: flex; justify-content: space-between; font-size: 11px; color: var(--text-muted); padding: 4px 0; border-bottom: 1px solid #f1f5f9;">
                                <span>v{{ $ver->version }} Snapshot</span>
                                <span>{{ $ver->created_at->format('M d, H:i') }}</span>
                            </div>
                        @empty
                            <div style="font-size: 11px; color: var(--text-muted);">No snapshots saved yet.</div>
                        @endforelse
                    </div>
                </div>

            </div>
        </aside>
    </div>

    <!-- Notification Toast -->
    <div class="toast" id="toast-msg">Map Saved Successfully!</div>

    <!-- Map Editor Script -->
    <script>
        const MAP_DATA = @json($map);
        const ORG_ID = "{{ $organization->id }}";
        const CSRF_TOKEN = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        const canvas = document.getElementById('editor-canvas');
        const ctx = canvas.getContext('2d');
        let width = canvas.width = window.innerWidth - 340;
        let height = canvas.height = window.innerHeight - 62;

        window.addEventListener('resize', () => {
            const drawer = document.getElementById('customize-drawer');
            const drawerWidth = drawer.classList.contains('collapsed') ? 0 : 340;
            width = canvas.width = window.innerWidth - drawerWidth;
            height = canvas.height = window.innerHeight - 62;
            draw();
        });

        const TILE_SIZE = 32;
        let currentTool = 'select'; // select | room | zone | object
        let currentRoomType = 'meeting';
        let currentRoomColor = '#00b4b3';
        let currentObjectType = 'desk';
        let currentObjectColor = '#00b4b3';
        let zoomLevel = 1.0;
        let panOffset = { x: 40, y: 30 };
        let showGrid = true;

        let rooms = MAP_DATA.rooms || [];
        let zones = MAP_DATA.zones || [];
        let objects = MAP_DATA.objects || [];

        let selectedItem = null;
        let isDrawing = false;
        let isDragging = false;
        let dragStartTileX = 0;
        let dragStartTileY = 0;
        let dragOrigX = 0;
        let dragOrigY = 0;
        let startX = 0;
        let startY = 0;
        let currentRect = null;
        let attachedObjects = [];

        const OBJECT_CONFIGS = {
            desk: { icon: '💻', name: 'Workstation', collision: true, width: 2, height: 1 },
            executive_desk: { icon: '🖥️', name: 'Executive Desk', collision: true, width: 2, height: 2 },
            chair: { icon: '🪑', name: 'Ergo Chair', collision: false, width: 1, height: 1 },
            sofa: { icon: '🛋️', name: 'Lounge Sofa', collision: true, width: 2, height: 1 },
            beanbag: { icon: '🟡', name: 'Bean Bag Chair', collision: false, width: 1, height: 1 },
            booth: { icon: '🟥', name: 'Booth Corner', collision: true, width: 2, height: 2 },
            whiteboard: { icon: '📋', name: 'Whiteboard', collision: true, width: 2, height: 1 },
            screen: { icon: '📺', name: 'AV Screen', collision: true, width: 2, height: 1 },
            plant: { icon: '🪴', name: 'Decor Plant', collision: false, width: 1, height: 1 },
            lamp: { icon: '💡', name: 'Floor Lamp', collision: false, width: 1, height: 1 },
            pingpong: { icon: '🏓', name: 'Ping Pong Table', collision: true, width: 3, height: 2 },
            water_cooler: { icon: '🚰', name: 'Water Cooler', collision: true, width: 1, height: 1 },
            cabinet: { icon: '🗄️', name: 'Filing Cabinet', collision: true, width: 1, height: 1 },
            dining_table: { icon: '🍽️', name: 'Conference Table', collision: true, width: 3, height: 2 }
        };

        const ALL_FURNITURE_ITEMS = @json($furnitureItems ?? []);
        const CUSTOM_IMAGE_CACHE = {};
        ALL_FURNITURE_ITEMS.forEach(it => {
            if (it.image_url) {
                const img = new Image();
                img.src = it.image_url;
                img.onload = () => draw();
                CUSTOM_IMAGE_CACHE[it.slug] = { img, width: it.width || 1, height: it.height || 1 };
            }
            OBJECT_CONFIGS[it.slug] = {
                icon: it.icon || '🪑',
                name: it.name,
                collision: it.collision,
                width: it.width || 1,
                height: it.height || 1,
                image_url: it.image_url
            };
        });

        let currentObjectCustom = null;

        function setTool(tool) {
            currentTool = tool;
            document.querySelectorAll('.tool-chip').forEach(el => el.classList.remove('active'));
            document.getElementById(`tool-${tool}`)?.classList.add('active');
            canvas.style.cursor = tool === 'select' ? 'default' : 'crosshair';
        }

        function setRoomTemplate(type, color) {
            setTool('room');
            currentRoomType = type;
            currentRoomColor = color;
            document.querySelectorAll('.room-card').forEach(el => el.classList.remove('active'));
            event.currentTarget.classList.add('active');
        }

        function selectFurnitureItem(type, color, imageUrl = null, w = 1, h = 1, collision = true) {
            setTool('object');
            currentObjectType = type;
            currentObjectColor = color || '#00b4b3';
            currentObjectCustom = {
                imageUrl: imageUrl || null,
                width: w || 1,
                height: h || 1,
                collision: Boolean(collision)
            };
            document.querySelectorAll('.furn-card').forEach(el => el.classList.remove('active'));
            if (event && event.currentTarget) {
                event.currentTarget.closest('.furn-card')?.classList.add('active');
            }
        }

        function toggleCustomizeDrawer() {
            const drawer = document.getElementById('customize-drawer');
            drawer.classList.toggle('collapsed');
            const drawerWidth = drawer.classList.contains('collapsed') ? 0 : 340;
            width = canvas.width = window.innerWidth - drawerWidth;
            draw();
        }

        function switchDrawerTab(tab) {
            document.querySelectorAll('.drawer-tab').forEach(el => el.classList.remove('active'));
            document.getElementById(`tab-btn-${tab}`).classList.add('active');

            document.getElementById('drawer-view-furniture').style.display = tab === 'furniture' ? 'flex' : 'none';
            document.getElementById('drawer-view-rooms').style.display = tab === 'rooms' ? 'flex' : 'none';
            document.getElementById('drawer-view-settings').style.display = tab === 'settings' ? 'flex' : 'none';
        }

        function toggleAccordion(catId) {
            const el = document.getElementById(catId);
            const body = el.querySelector('.category-body');
            const icon = el.querySelector('.acc-icon');
            if (body.style.display === 'none') {
                body.style.display = 'grid';
                icon.textContent = '▴';
            } else {
                body.style.display = 'none';
                icon.textContent = '▾';
            }
        }

        function filterFurniture(q) {
            const query = q.toLowerCase();
            document.querySelectorAll('.furn-card').forEach(card => {
                const name = card.querySelector('.furn-name').textContent.toLowerCase();
                card.style.display = name.includes(query) ? 'flex' : 'none';
            });
        }

        function zoomIn() { zoomLevel = Math.min(2.0, zoomLevel + 0.15); draw(); }
        function zoomOut() { zoomLevel = Math.max(0.6, zoomLevel - 0.15); draw(); }
        function resetView() { zoomLevel = 1.0; panOffset = { x: 40, y: 30 }; draw(); }
        function toggleGrid() { showGrid = !showGrid; draw(); }

        // ── Keyboard Shortcuts ──
        window.addEventListener('keydown', (e) => {
            if (e.key === 'Delete' || e.key === 'Backspace') {
                if (document.activeElement.tagName !== 'INPUT') {
                    deleteSelectedItem();
                }
            }
        });

        // ── Mouse Canvas Interaction ──
        canvas.addEventListener('mousedown', (e) => {
            const rect = canvas.getBoundingClientRect();
            const mouseX = (e.clientX - rect.left - panOffset.x) / zoomLevel;
            const mouseY = (e.clientY - rect.top - panOffset.y) / zoomLevel;

            const tileX = Math.floor(mouseX / TILE_SIZE);
            const tileY = Math.floor(mouseY / TILE_SIZE);

            if (currentTool === 'select') {
                selectedItem = null;
                attachedObjects = [];

                // 1. Objects first
                for (let i = objects.length - 1; i >= 0; i--) {
                    const obj = objects[i];
                    if (obj.position.x === tileX && obj.position.y === tileY) {
                        selectedItem = { type: 'object', item: obj };
                        break;
                    }
                }

                // 2. Zones
                if (!selectedItem) {
                    for (let i = zones.length - 1; i >= 0; i--) {
                        const z = zones[i];
                        if (tileX >= z.shape_data.x && tileX < z.shape_data.x + z.shape_data.width &&
                            tileY >= z.shape_data.y && tileY < z.shape_data.y + z.shape_data.height) {
                            selectedItem = { type: 'zone', item: z };
                            break;
                        }
                    }
                }

                // 3. Rooms
                if (!selectedItem) {
                    for (let i = rooms.length - 1; i >= 0; i--) {
                        const r = rooms[i];
                        if (tileX >= r.bounds.x && tileX < r.bounds.x + r.bounds.width &&
                            tileY >= r.bounds.y && tileY < r.bounds.y + r.bounds.height) {
                            selectedItem = { type: 'room', item: r };
                            break;
                        }
                    }
                }

                if (selectedItem) {
                    isDragging = true;
                    dragStartTileX = tileX;
                    dragStartTileY = tileY;

                    if (selectedItem.type === 'object') {
                        dragOrigX = selectedItem.item.position.x;
                        dragOrigY = selectedItem.item.position.y;
                    } else if (selectedItem.type === 'room') {
                        dragOrigX = selectedItem.item.bounds.x;
                        dragOrigY = selectedItem.item.bounds.y;

                        const rx = dragOrigX;
                        const ry = dragOrigY;
                        const rw = selectedItem.item.bounds.width;
                        const rh = selectedItem.item.bounds.height;

                        attachedObjects = objects.filter(obj =>
                            obj.position.x >= rx && obj.position.x < rx + rw &&
                            obj.position.y >= ry && obj.position.y < ry + rh
                        ).map(obj => ({
                            obj: obj,
                            origX: obj.position.x,
                            origY: obj.position.y
                        }));
                    }
                    canvas.style.cursor = 'grabbing';
                }

                updateInspector();
                draw();
            } else if (currentTool === 'room' || currentTool === 'zone') {
                isDrawing = true;
                startX = tileX;
                startY = tileY;
                currentRect = { x: tileX, y: tileY, width: 1, height: 1 };
            } else if (currentTool === 'object') {
                const conf = OBJECT_CONFIGS[currentObjectType] || { name: 'Object', collision: false, width: 1, height: 1 };
                const newObj = {
                    type: currentObjectType,
                    name: `${conf.name} #${objects.length + 1}`,
                    position: { x: tileX, y: tileY },
                    color: currentObjectColor,
                    image_url: currentObjectCustom?.imageUrl || conf.image_url || null,
                    width: currentObjectCustom?.width || conf.width || 1,
                    height: currentObjectCustom?.height || conf.height || 1,
                    collision: currentObjectCustom ? currentObjectCustom.collision : conf.collision
                };
                objects.push(newObj);
                selectedItem = { type: 'object', item: newObj };
                updateInspector();
                updateStats();
                draw();
            }
        });

        canvas.addEventListener('mousemove', (e) => {
            const rect = canvas.getBoundingClientRect();
            const mouseX = (e.clientX - rect.left - panOffset.x) / zoomLevel;
            const mouseY = (e.clientY - rect.top - panOffset.y) / zoomLevel;

            const tileX = Math.floor(mouseX / TILE_SIZE);
            const tileY = Math.floor(mouseY / TILE_SIZE);

            if (isDragging && selectedItem) {
                const dx = tileX - dragStartTileX;
                const dy = tileY - dragStartTileY;

                if (selectedItem.type === 'object') {
                    selectedItem.item.position.x = Math.max(0, Math.min(31, dragOrigX + dx));
                    selectedItem.item.position.y = Math.max(0, Math.min(23, dragOrigY + dy));
                } else if (selectedItem.type === 'room') {
                    const newRoomX = Math.max(0, Math.min(32 - selectedItem.item.bounds.width, dragOrigX + dx));
                    const newRoomY = Math.max(0, Math.min(24 - selectedItem.item.bounds.height, dragOrigY + dy));
                    const actualDeltaX = newRoomX - dragOrigX;
                    const actualDeltaY = newRoomY - dragOrigY;

                    selectedItem.item.bounds.x = newRoomX;
                    selectedItem.item.bounds.y = newRoomY;

                    if (attachedObjects && attachedObjects.length > 0) {
                        attachedObjects.forEach(att => {
                            att.obj.position.x = Math.max(0, Math.min(31, att.origX + actualDeltaX));
                            att.obj.position.y = Math.max(0, Math.min(23, att.origY + actualDeltaY));
                        });
                    }
                }
                draw();
                return;
            }

            if (isDrawing) {
                const x = Math.min(startX, tileX);
                const y = Math.min(startY, tileY);
                const w = Math.max(1, Math.abs(tileX - startX) + 1);
                const h = Math.max(1, Math.abs(tileY - startY) + 1);

                currentRect = { x, y, width: w, height: h };
                draw();
            }
        });

        canvas.addEventListener('mouseup', () => {
            if (isDragging) {
                isDragging = false;
                attachedObjects = [];
                canvas.style.cursor = currentTool === 'select' ? 'default' : 'crosshair';
                draw();
            }

            if (isDrawing && currentRect) {
                if (currentTool === 'room') {
                    const newRoom = {
                        name: `${currentRoomType.charAt(0).toUpperCase() + currentRoomType.slice(1)} Room`,
                        type: currentRoomType,
                        access_mode: currentRoomType === 'private' ? 'private' : 'public',
                        capacity: 10,
                        color: currentRoomColor,
                        bounds: currentRect
                    };
                    rooms.push(newRoom);
                    selectedItem = { type: 'room', item: newRoom };
                } else if (currentTool === 'zone') {
                    const newZone = {
                        name: 'Audio Zone',
                        type: 'audio',
                        shape_type: 'rectangle',
                        shape_data: currentRect,
                        audible_radius: 160
                    };
                    zones.push(newZone);
                    selectedItem = { type: 'zone', item: newZone };
                }
                isDrawing = false;
                currentRect = null;
                updateInspector();
                updateStats();
                draw();
            }
        });

        function updateInspector() {
            const nameInput = document.getElementById('prop-name');
            const colorInput = document.getElementById('prop-color');
            const capInput = document.getElementById('prop-capacity');
            const colorGroup = document.getElementById('prop-color-group');
            const capGroup = document.getElementById('prop-capacity-group');

            if (selectedItem) {
                nameInput.value = selectedItem.item.name || '';
                if (selectedItem.type === 'room') {
                    colorGroup.style.display = 'flex';
                    capGroup.style.display = 'flex';
                    colorInput.value = selectedItem.item.color || '#00b4b3';
                    capInput.value = selectedItem.item.capacity || 10;
                } else {
                    colorGroup.style.display = 'none';
                    capGroup.style.display = 'none';
                }
            } else {
                nameInput.value = 'Office Layout';
                colorGroup.style.display = 'none';
                capGroup.style.display = 'none';
            }
        }

        function updateSelectedProp(prop, val) {
            if (!selectedItem) return;
            if (prop === 'name') selectedItem.item.name = val;
            if (prop === 'color') selectedItem.item.color = val;
            if (prop === 'capacity') selectedItem.item.capacity = parseInt(val);
            draw();
        }

        function deleteSelectedItem() {
            if (!selectedItem) {
                showToast('ℹ️ Please click on an item to select it first.', '#f57b36');
                return;
            }

            const item = selectedItem.item;
            if (selectedItem.type === 'room') {
                const idx = rooms.indexOf(item);
                if (idx > -1) rooms.splice(idx, 1);
                if (item.id) {
                    fetch(`/api/v1/organizations/${ORG_ID}/rooms/${item.id}`, {
                        method: 'DELETE',
                        headers: { 'X-CSRF-TOKEN': CSRF_TOKEN, 'Accept': 'application/json' },
                        credentials: 'same-origin'
                    }).catch(console.error);
                }
                showToast('🗑️ Room removed');
            } else if (selectedItem.type === 'zone') {
                const idx = zones.indexOf(item);
                if (idx > -1) zones.splice(idx, 1);
                showToast('🗑️ Audio zone removed');
            } else if (selectedItem.type === 'object') {
                const idx = objects.indexOf(item);
                if (idx > -1) objects.splice(idx, 1);
                showToast('🗑️ Furniture removed');
            }

            selectedItem = null;
            updateInspector();
            updateStats();
            draw();
        }

        function updateStats() {
            document.getElementById('stat-rooms-count').textContent = rooms.length;
            document.getElementById('stat-zones-count').textContent = zones.length;
            document.getElementById('stat-objects-count').textContent = objects.length;
        }

        // ── Top-Down Furniture Sprites ──
        const FURN_SHEET1 = new Image();
        FURN_SHEET1.src = '/images/furniture/furniture_sheet1.webp';
        let furnSheet1Loaded = false;
        FURN_SHEET1.onload = () => { furnSheet1Loaded = true; draw(); };

        const FURN_SHEET2 = new Image();
        FURN_SHEET2.src = '/images/furniture/furniture_sheet2.webp';
        let furnSheet2Loaded = false;
        FURN_SHEET2.onload = () => { furnSheet2Loaded = true; draw(); };

        const EDITOR_3D_FURNITURE = {
            desk: (x, y) => {
                if (furnSheet1Loaded) {
                    ctx.drawImage(FURN_SHEET1, 40, 205, 260, 215, x - 4, y - 6, 40, 38);
                } else {
                    ctx.fillStyle = '#5c4033'; ctx.fillRect(x + 2, y + 4, 28, 22);
                }
            },
            executive_desk: (x, y) => {
                if (furnSheet2Loaded) {
                    ctx.drawImage(FURN_SHEET2, 500, 70, 340, 500, x - 8, y - 10, 48, 52);
                } else {
                    ctx.fillStyle = '#271610'; ctx.fillRect(x + 2, y + 3, 28, 24);
                }
            },
            chair: (x, y) => {
                if (furnSheet1Loaded) {
                    ctx.drawImage(FURN_SHEET1, 50, 68, 120, 105, x + 2, y + 2, 28, 28);
                } else {
                    ctx.fillStyle = '#00b4b3'; ctx.beginPath(); ctx.arc(x + 16, y + 16, 10, 0, Math.PI * 2); ctx.fill();
                }
            },
            sofa: (x, y) => {
                if (furnSheet1Loaded) {
                    ctx.drawImage(FURN_SHEET1, 400, 830, 245, 115, x - 4, y - 2, 40, 28);
                } else {
                    ctx.fillStyle = '#012c41'; ctx.fillRect(x + 2, y + 4, 28, 22);
                }
            },
            plant: (x, y) => {
                if (furnSheet1Loaded) {
                    ctx.drawImage(FURN_SHEET1, 310, 68, 110, 110, x + 2, y + 2, 28, 28);
                } else {
                    ctx.fillStyle = '#006847'; ctx.beginPath(); ctx.arc(x + 16, y + 16, 8, 0, Math.PI * 2); ctx.fill();
                }
            },
            dining_table: (x, y) => {
                if (furnSheet1Loaded) {
                    ctx.drawImage(FURN_SHEET1, 600, 80, 360, 320, x - 8, y - 8, 48, 48);
                } else {
                    ctx.fillStyle = '#3e2723'; ctx.fillRect(x + 2, y + 2, 28, 28);
                }
            }
        };

        // ── Ultra-High-Fidelity Procedural Office Furniture Rendering Engine ──
        function drawEnhancedOfficeFurniture(ctx, obj, ox, oy, objW, objH) {
            ctx.save();

            // 1. Check custom uploaded sprite image first
            const customSprite = CUSTOM_IMAGE_CACHE[obj.type] || (obj.image_url ? { img: (function(){ const i = new Image(); i.src = obj.image_url; return i; })() } : null);
            if (customSprite && customSprite.img && customSprite.img.complete && customSprite.img.naturalWidth > 0) {
                ctx.fillStyle = 'rgba(0, 0, 0, 0.18)';
                ctx.beginPath();
                if (ctx.roundRect) ctx.roundRect(ox + 2, oy + 4, objW - 4, objH - 4, 6);
                else ctx.rect(ox + 2, oy + 4, objW - 4, objH - 4);
                ctx.fill();

                ctx.drawImage(customSprite.img, ox, oy, objW, objH);
                ctx.restore();
                return;
            }

            const type = String(obj.type || '').toLowerCase();
            const primaryColor = obj.color || '#00b4b3';

            function roundRect(x, y, w, h, r) {
                if (ctx.roundRect) {
                    ctx.roundRect(x, y, w, h, r);
                } else {
                    ctx.beginPath();
                    ctx.moveTo(x + r, y);
                    ctx.lineTo(x + w - r, y);
                    ctx.quadraticCurveTo(x + w, y, x + w, y + r);
                    ctx.lineTo(x + w, y + h - r);
                    ctx.quadraticCurveTo(x + w, y + h, x + w - r, y + h);
                    ctx.lineTo(x + r, y + h);
                    ctx.quadraticCurveTo(x, y + h, x, y + h - r);
                    ctx.lineTo(x, y + r);
                    ctx.quadraticCurveTo(x, y, x + r, y);
                    ctx.closePath();
                }
            }

            // ── 1. ERGONOMIC OFFICE CHAIR / BEANBAG ──
            if (type === 'chair' || type === 'ergo_chair' || type === 'beanbag') {
                if (type === 'beanbag') {
                    ctx.fillStyle = 'rgba(0, 0, 0, 0.15)';
                    ctx.beginPath();
                    ctx.arc(ox + objW/2, oy + objH/2 + 3, objW/2 - 2, 0, Math.PI * 2);
                    ctx.fill();

                    const bbGrad = ctx.createRadialGradient(ox + objW/2 - 3, oy + objH/2 - 3, 2, ox + objW/2, oy + objH/2, objW/2 - 2);
                    bbGrad.addColorStop(0, primaryColor);
                    bbGrad.addColorStop(1, '#d97706');
                    ctx.fillStyle = bbGrad;
                    ctx.beginPath();
                    ctx.arc(ox + objW/2, oy + objH/2, objW/2 - 3, 0, Math.PI * 2);
                    ctx.fill();

                    ctx.fillStyle = 'rgba(0,0,0,0.15)';
                    ctx.beginPath();
                    ctx.arc(ox + objW/2, oy + objH/2 + 2, objW/4, 0, Math.PI * 2);
                    ctx.fill();

                    ctx.fillStyle = '#ffffff';
                    ctx.beginPath();
                    ctx.arc(ox + objW/2, oy + objH/2 - 4, 3, 0, Math.PI * 2);
                    ctx.fill();
                } else {
                    const cx = ox + objW / 2;
                    const cy = oy + objH / 2;

                    ctx.strokeStyle = '#475569';
                    ctx.lineWidth = 2.5;
                    for (let a = 0; a < 5; a++) {
                        const angle = (a * 2 * Math.PI) / 5 - Math.PI / 2;
                        ctx.beginPath();
                        ctx.moveTo(cx, cy);
                        ctx.lineTo(cx + Math.cos(angle) * 11, cy + Math.sin(angle) * 11);
                        ctx.stroke();

                        ctx.fillStyle = '#1e293b';
                        ctx.beginPath();
                        ctx.arc(cx + Math.cos(angle) * 11, cy + Math.sin(angle) * 11, 2, 0, Math.PI * 2);
                        ctx.fill();
                    }

                    ctx.fillStyle = 'rgba(0, 0, 0, 0.18)';
                    ctx.beginPath();
                    ctx.arc(cx, cy + 2, 10, 0, Math.PI * 2);
                    ctx.fill();

                    const seatGrad = ctx.createRadialGradient(cx - 2, cy - 2, 2, cx, cy, 10);
                    seatGrad.addColorStop(0, primaryColor);
                    seatGrad.addColorStop(1, '#004862');
                    ctx.fillStyle = seatGrad;
                    ctx.beginPath();
                    ctx.arc(cx, cy, 9, 0, Math.PI * 2);
                    ctx.fill();
                    ctx.strokeStyle = 'rgba(255,255,255,0.4)';
                    ctx.lineWidth = 1;
                    ctx.stroke();

                    ctx.fillStyle = '#0f172a';
                    roundRect(cx - 8, cy - 11, 16, 5, 2.5);
                    ctx.fill();
                    ctx.fillStyle = primaryColor;
                    roundRect(cx - 6, cy - 10, 12, 3, 1.5);
                    ctx.fill();

                    ctx.strokeStyle = '#94a3b8';
                    ctx.lineWidth = 1.5;
                    ctx.beginPath();
                    ctx.moveTo(cx - 7, cy - 9);
                    ctx.lineTo(cx + 7, cy - 9);
                    ctx.stroke();

                    ctx.fillStyle = '#1e293b';
                    roundRect(cx - 12, cy - 4, 3, 8, 1.5);
                    ctx.fill();
                    roundRect(cx + 9, cy - 4, 3, 8, 1.5);
                    ctx.fill();
                }
            }

            // ── 2. EXECUTIVE & STANDARD WORKSTATION DESKS ──
            else if (type === 'desk' || type === 'executive_desk' || type === 'workstation') {
                const isExec = type === 'executive_desk' || (objW >= 64 && objH >= 64);

                ctx.fillStyle = 'rgba(0, 0, 0, 0.2)';
                roundRect(ox + 2, oy + 4, objW - 4, objH - 4, 6);
                ctx.fill();

                const deskGrad = ctx.createLinearGradient(ox, oy, ox, oy + objH);
                if (isExec) {
                    deskGrad.addColorStop(0, '#331c12');
                    deskGrad.addColorStop(0.5, '#4a2818');
                    deskGrad.addColorStop(1, '#27140b');
                } else {
                    deskGrad.addColorStop(0, '#78350f');
                    deskGrad.addColorStop(0.5, '#92400e');
                    deskGrad.addColorStop(1, '#662d0b');
                }
                ctx.fillStyle = deskGrad;
                roundRect(ox + 1, oy + 1, objW - 2, objH - 2, 4);
                ctx.fill();

                ctx.strokeStyle = 'rgba(255, 255, 255, 0.15)';
                ctx.lineWidth = 1;
                ctx.stroke();

                const padW = Math.min(objW - 12, 38);
                const padH = Math.min(objH - 10, 20);
                const padX = ox + (objW - padW) / 2;
                const padY = oy + (objH - padH) / 2 + (isExec ? 4 : 2);

                ctx.fillStyle = '#0f172a';
                roundRect(padX, padY, padW, padH, 2);
                ctx.fill();
                ctx.strokeStyle = '#334155';
                ctx.lineWidth = 0.5;
                ctx.stroke();

                const monW = Math.min(objW - 14, 30);
                const monH = 4;
                const monX = ox + (objW - monW) / 2;
                const monY = oy + 4;

                ctx.fillStyle = '#94a3b8';
                ctx.fillRect(ox + objW/2 - 4, monY + monH, 8, 2);

                ctx.fillStyle = '#020617';
                roundRect(monX, monY, monW, monH, 1.5);
                ctx.fill();

                const screenGrad = ctx.createLinearGradient(monX + 2, monY, monX + monW - 2, monY);
                screenGrad.addColorStop(0, '#00b4b3');
                screenGrad.addColorStop(0.5, '#38bdf8');
                screenGrad.addColorStop(1, '#006847');
                ctx.fillStyle = screenGrad;
                ctx.fillRect(monX + 2, monY + 1, monW - 4, 2);

                const kbW = Math.min(padW - 14, 18);
                const kbH = 6;
                const kbX = padX + (padW - kbW) / 2 - 4;
                const kbY = padY + padH - kbH - 2;

                ctx.fillStyle = '#1e293b';
                roundRect(kbX, kbY, kbW, kbH, 1);
                ctx.fill();
                ctx.fillStyle = '#38bdf8';
                ctx.fillRect(kbX + 2, kbY + 2, kbW - 4, 2);

                ctx.fillStyle = '#334155';
                roundRect(kbX + kbW + 3, kbY + 1, 4, 5, 1.5);
                ctx.fill();

                ctx.fillStyle = '#f8fafc';
                ctx.beginPath();
                ctx.arc(ox + 8, oy + objH - 8, 3, 0, Math.PI * 2);
                ctx.fill();
                ctx.fillStyle = '#78350f';
                ctx.beginPath();
                ctx.arc(ox + 8, oy + objH - 8, 2, 0, Math.PI * 2);
                ctx.fill();

                if (isExec) {
                    ctx.fillStyle = '#d20005';
                    roundRect(ox + objW - 16, oy + 8, 10, 14, 1.5);
                    ctx.fill();
                    ctx.fillStyle = '#ffffff';
                    ctx.fillRect(ox + objW - 14, oy + 10, 6, 2);

                    const lampGrad = ctx.createRadialGradient(ox + 10, oy + 8, 2, ox + 10, oy + 8, 16);
                    lampGrad.addColorStop(0, 'rgba(254, 240, 138, 0.4)');
                    lampGrad.addColorStop(1, 'rgba(254, 240, 138, 0)');
                    ctx.fillStyle = lampGrad;
                    ctx.beginPath();
                    ctx.arc(ox + 10, oy + 8, 16, 0, Math.PI * 2);
                    ctx.fill();

                    ctx.fillStyle = '#ffd136';
                    ctx.beginPath();
                    ctx.arc(ox + 10, oy + 8, 3, 0, Math.PI * 2);
                    ctx.fill();
                }
            }

            // ── 3. CONFERENCE & MEETING TABLES ──
            else if (type === 'dining_table' || type === 'conference_table' || type === 'meeting_table') {
                ctx.fillStyle = 'rgba(0, 0, 0, 0.22)';
                roundRect(ox + 3, oy + 5, objW - 6, objH - 6, 8);
                ctx.fill();

                const confGrad = ctx.createLinearGradient(ox, oy, ox, oy + objH);
                confGrad.addColorStop(0, '#2b170e');
                confGrad.addColorStop(0.5, '#452618');
                confGrad.addColorStop(1, '#23120a');
                ctx.fillStyle = confGrad;
                roundRect(ox + 2, oy + 2, objW - 4, objH - 4, 8);
                ctx.fill();

                ctx.fillStyle = 'rgba(15, 23, 42, 0.8)';
                roundRect(ox + 10, oy + (objH - 12) / 2, objW - 20, 12, 3);
                ctx.fill();

                const numPods = Math.max(2, Math.floor(objW / 28));
                for (let i = 0; i < numPods; i++) {
                    const px = ox + 14 + (i * (objW - 28)) / (numPods - 1);
                    const py = oy + objH / 2;

                    ctx.fillStyle = '#334155';
                    ctx.beginPath();
                    ctx.arc(px, py, 3, 0, Math.PI * 2);
                    ctx.fill();

                    ctx.fillStyle = '#22c55e';
                    ctx.beginPath();
                    ctx.arc(px, py, 1, 0, Math.PI * 2);
                    ctx.fill();
                }

                const chairsCount = Math.max(2, Math.floor((objW - 16) / 22));
                for (let i = 0; i < chairsCount; i++) {
                    const cx = ox + 12 + i * 22;
                    ctx.fillStyle = '#1e293b';
                    roundRect(cx, oy - 2, 14, 4, 2);
                    ctx.fill();
                    roundRect(cx, oy + objH - 2, 14, 4, 2);
                    ctx.fill();
                }
            }

            // ── 4. MODERN SOFAS & BOOTHS ──
            else if (type === 'sofa' || type === 'lounge_sofa' || type === 'booth') {
                ctx.fillStyle = 'rgba(0, 0, 0, 0.2)';
                roundRect(ox + 2, oy + 4, objW - 4, objH - 4, 6);
                ctx.fill();

                const sofaGrad = ctx.createLinearGradient(ox, oy, ox, oy + objH);
                sofaGrad.addColorStop(0, primaryColor);
                sofaGrad.addColorStop(1, '#002535');
                ctx.fillStyle = sofaGrad;
                roundRect(ox + 1, oy + 1, objW - 2, objH - 2, 6);
                ctx.fill();

                ctx.fillStyle = 'rgba(0, 0, 0, 0.25)';
                roundRect(ox + 3, oy + 3, objW - 6, 7, 3);
                ctx.fill();

                const numCushions = objW > 48 ? 2 : 1;
                const cW = (objW - 12) / numCushions;
                for (let i = 0; i < numCushions; i++) {
                    const cx = ox + 5 + i * cW + (i > 0 ? 2 : 0);
                    ctx.fillStyle = 'rgba(255, 255, 255, 0.12)';
                    roundRect(cx, oy + 11, cW - 2, objH - 14, 3);
                    ctx.fill();
                    ctx.strokeStyle = 'rgba(0, 0, 0, 0.2)';
                    ctx.lineWidth = 1;
                    ctx.stroke();
                }

                ctx.fillStyle = 'rgba(0, 0, 0, 0.35)';
                roundRect(ox + 1, oy + 2, 4, objH - 4, 2);
                ctx.fill();
                roundRect(ox + objW - 5, oy + 2, 4, objH - 4, 2);
                ctx.fill();

                ctx.fillStyle = '#ffd136';
                roundRect(ox + 5, oy + 12, 6, 6, 2);
                ctx.fill();

                if (objW > 48) {
                    ctx.fillStyle = '#f57b36';
                    roundRect(ox + objW - 11, oy + 12, 6, 6, 2);
                    ctx.fill();
                }
            }

            // ── 5. POTTED TROPICAL OFFICE PLANTS ──
            else if (type === 'plant' || type === 'decor_plant') {
                const cx = ox + objW / 2;
                const cy = oy + objH / 2;

                ctx.fillStyle = 'rgba(0, 0, 0, 0.18)';
                ctx.beginPath();
                ctx.arc(cx, cy + 3, 11, 0, Math.PI * 2);
                ctx.fill();

                const potGrad = ctx.createRadialGradient(cx - 3, cy - 3, 2, cx, cy, 10);
                potGrad.addColorStop(0, '#f8fafc');
                potGrad.addColorStop(1, '#94a3b8');
                ctx.fillStyle = potGrad;
                ctx.beginPath();
                ctx.arc(cx, cy, 10, 0, Math.PI * 2);
                ctx.fill();

                ctx.fillStyle = '#451a03';
                ctx.beginPath();
                ctx.arc(cx, cy, 7.5, 0, Math.PI * 2);
                ctx.fill();

                const leafAngles = [0, 0.78, 1.57, 2.35, 3.14, 3.92, 4.71, 5.49];
                leafAngles.forEach((angle, idx) => {
                    const lx = cx + Math.cos(angle) * 11;
                    const ly = cy + Math.sin(angle) * 11;

                    const leafGrad = ctx.createRadialGradient(cx, cy, 2, lx, ly, 7);
                    leafGrad.addColorStop(0, idx % 2 === 0 ? '#006847' : '#00b4b3');
                    leafGrad.addColorStop(1, idx % 2 === 0 ? '#004d34' : '#00726c');
                    ctx.fillStyle = leafGrad;

                    ctx.beginPath();
                    ctx.arc(lx, ly, 4.5, 0, Math.PI * 2);
                    ctx.fill();

                    ctx.strokeStyle = '#a7c545';
                    ctx.lineWidth = 1;
                    ctx.beginPath();
                    ctx.moveTo(cx, cy);
                    ctx.lineTo(lx, ly);
                    ctx.stroke();
                });

                ctx.fillStyle = '#a7c545';
                ctx.beginPath();
                ctx.arc(cx, cy, 3, 0, Math.PI * 2);
                ctx.fill();
            }

            // ── 6. GLASS & MAGNETIC WHITEBOARDS ──
            else if (type === 'whiteboard') {
                ctx.fillStyle = 'rgba(0, 0, 0, 0.18)';
                roundRect(ox + 2, oy + 3, objW - 4, objH - 4, 3);
                ctx.fill();

                ctx.fillStyle = '#94a3b8';
                roundRect(ox + 1, oy + 1, objW - 2, objH - 2, 3);
                ctx.fill();

                const boardGrad = ctx.createLinearGradient(ox, oy, ox, oy + objH);
                boardGrad.addColorStop(0, '#ffffff');
                boardGrad.addColorStop(1, '#f1f5f9');
                ctx.fillStyle = boardGrad;
                roundRect(ox + 3, oy + 3, objW - 6, objH - 6, 2);
                ctx.fill();

                ctx.strokeStyle = '#00b4b3';
                ctx.lineWidth = 1.5;
                ctx.beginPath();
                ctx.moveTo(ox + 8, oy + 8);
                ctx.lineTo(ox + 18, oy + 8);
                ctx.lineTo(ox + 24, oy + 14);
                ctx.stroke();

                ctx.strokeStyle = '#d20005';
                ctx.beginPath();
                ctx.arc(ox + objW - 12, oy + 11, 3, 0, Math.PI * 2);
                ctx.stroke();

                ctx.fillStyle = '#475569';
                ctx.fillRect(ox + (objW - 24) / 2, oy + objH - 3, 24, 2);
                ctx.fillStyle = '#00b4b3'; ctx.fillRect(ox + (objW - 20) / 2, oy + objH - 3, 4, 1.5);
                ctx.fillStyle = '#d20005'; ctx.fillRect(ox + (objW - 20) / 2 + 6, oy + objH - 3, 4, 1.5);
                ctx.fillStyle = '#012c41'; ctx.fillRect(ox + (objW - 20) / 2 + 12, oy + objH - 3, 4, 1.5);
            }

            // ── 7. LARGE PRESENTATION AV SCREEN ──
            else if (type === 'screen' || type === 'tv') {
                ctx.fillStyle = 'rgba(0, 0, 0, 0.25)';
                roundRect(ox + 2, oy + 3, objW - 4, objH - 4, 3);
                ctx.fill();

                ctx.fillStyle = '#020617';
                roundRect(ox + 1, oy + 1, objW - 2, objH - 2, 2);
                ctx.fill();

                const scrGrad = ctx.createLinearGradient(ox, oy, ox + objW, oy + objH);
                scrGrad.addColorStop(0, '#004862');
                scrGrad.addColorStop(0.5, '#00b4b3');
                scrGrad.addColorStop(1, '#012c41');
                ctx.fillStyle = scrGrad;
                roundRect(ox + 3, oy + 3, objW - 6, objH - 6, 1);
                ctx.fill();

                ctx.fillStyle = 'rgba(255, 255, 255, 0.85)';
                ctx.fillRect(ox + 6, oy + 6, 8, 2);
                ctx.fillRect(ox + 6, oy + 10, 14, 2);
                ctx.fillStyle = '#ffd136';
                ctx.beginPath();
                ctx.arc(ox + objW - 10, oy + objH / 2, 4, 0, Math.PI * 1.4);
                ctx.lineTo(ox + objW - 10, oy + objH / 2);
                ctx.fill();
            }

            // ── 8. PING PONG / GAME TABLE ──
            else if (type === 'pingpong') {
                ctx.fillStyle = 'rgba(0, 0, 0, 0.22)';
                roundRect(ox + 3, oy + 5, objW - 6, objH - 6, 4);
                ctx.fill();

                ctx.fillStyle = '#00726c';
                roundRect(ox + 2, oy + 2, objW - 4, objH - 4, 3);
                ctx.fill();

                ctx.strokeStyle = '#ffffff';
                ctx.lineWidth = 1.5;
                ctx.strokeRect(ox + 4, oy + 4, objW - 8, objH - 8);

                ctx.beginPath();
                ctx.moveTo(ox + 4, oy + objH / 2);
                ctx.lineTo(ox + objW - 4, oy + objH / 2);
                ctx.stroke();

                ctx.fillStyle = 'rgba(255, 255, 255, 0.9)';
                ctx.fillRect(ox + objW / 2 - 1, oy + 2, 2, objH - 4);
                ctx.fillStyle = '#0f172a';
                ctx.fillRect(ox + objW / 2 - 2, oy + 1, 4, 2);
                ctx.fillRect(ox + objW / 2 - 2, oy + objH - 3, 4, 2);

                ctx.fillStyle = '#d20005';
                ctx.beginPath(); ctx.arc(ox + 12, oy + 10, 3, 0, Math.PI * 2); ctx.fill();
                ctx.fillStyle = '#004862';
                ctx.beginPath(); ctx.arc(ox + objW - 12, oy + objH - 10, 3, 0, Math.PI * 2); ctx.fill();
                ctx.fillStyle = '#ffffff';
                ctx.beginPath(); ctx.arc(ox + objW / 2 + 4, oy + 8, 1.5, 0, Math.PI * 2); ctx.fill();
            }

            // ── 9. WATER COOLER ──
            else if (type === 'water_cooler') {
                const cx = ox + objW / 2;
                const cy = oy + objH / 2;

                ctx.fillStyle = 'rgba(0, 0, 0, 0.18)';
                ctx.beginPath();
                ctx.arc(cx, cy + 2, 9, 0, Math.PI * 2);
                ctx.fill();

                ctx.fillStyle = '#f8fafc';
                roundRect(ox + 4, oy + 6, objW - 8, objH - 8, 3);
                ctx.fill();
                ctx.strokeStyle = '#cbd5e1';
                ctx.lineWidth = 1;
                ctx.stroke();

                const waterGrad = ctx.createRadialGradient(cx - 2, cy - 2, 1, cx, cy, 6);
                waterGrad.addColorStop(0, '#38bdf8');
                waterGrad.addColorStop(1, '#0284c7');
                ctx.fillStyle = waterGrad;
                ctx.beginPath();
                ctx.arc(cx, cy - 1, 6, 0, Math.PI * 2);
                ctx.fill();

                ctx.fillStyle = '#ef4444'; ctx.beginPath(); ctx.arc(cx - 3, cy + 6, 1.5, 0, Math.PI * 2); ctx.fill();
                ctx.fillStyle = '#38bdf8'; ctx.beginPath(); ctx.arc(cx + 3, cy + 6, 1.5, 0, Math.PI * 2); ctx.fill();
            }

            // ── 10. FILING CABINET ──
            else if (type === 'cabinet' || type === 'storage') {
                ctx.fillStyle = 'rgba(0, 0, 0, 0.18)';
                roundRect(ox + 2, oy + 3, objW - 4, objH - 4, 3);
                ctx.fill();

                ctx.fillStyle = '#334155';
                roundRect(ox + 2, oy + 2, objW - 4, objH - 4, 2);
                ctx.fill();

                const numDrawers = Math.max(2, Math.floor(objH / 14));
                const dH = (objH - 6) / numDrawers;
                for (let i = 0; i < numDrawers; i++) {
                    const dy = oy + 3 + i * dH;
                    ctx.fillStyle = '#475569';
                    roundRect(ox + 4, dy + 1, objW - 8, dH - 2, 1.5);
                    ctx.fill();

                    ctx.fillStyle = '#cbd5e1';
                    ctx.fillRect(ox + objW / 2 - 4, dy + dH / 2 - 1, 8, 2);
                }
            }

            // ── 11. FLOOR LAMP ──
            else if (type === 'lamp') {
                const cx = ox + objW / 2;
                const cy = oy + objH / 2;

                const lightGrad = ctx.createRadialGradient(cx, cy, 4, cx, cy, 28);
                lightGrad.addColorStop(0, 'rgba(254, 240, 138, 0.5)');
                lightGrad.addColorStop(0.5, 'rgba(254, 240, 138, 0.2)');
                lightGrad.addColorStop(1, 'rgba(254, 240, 138, 0)');
                ctx.fillStyle = lightGrad;
                ctx.beginPath();
                ctx.arc(cx, cy, 28, 0, Math.PI * 2);
                ctx.fill();

                ctx.fillStyle = '#1e293b';
                ctx.beginPath();
                ctx.arc(cx, cy, 7, 0, Math.PI * 2);
                ctx.fill();

                ctx.fillStyle = '#ffd136';
                ctx.beginPath();
                ctx.arc(cx, cy, 4.5, 0, Math.PI * 2);
                ctx.fill();
            }

            // ── 12. GENERIC FALLBACK ──
            else {
                ctx.fillStyle = 'rgba(0, 0, 0, 0.18)';
                roundRect(ox + 2, oy + 3, objW - 4, objH - 4, 4);
                ctx.fill();

                const genGrad = ctx.createLinearGradient(ox, oy, ox, oy + objH);
                genGrad.addColorStop(0, '#ffffff');
                genGrad.addColorStop(1, '#f1f5f9');
                ctx.fillStyle = genGrad;
                roundRect(ox + 2, oy + 2, objW - 4, objH - 4, 3);
                ctx.fill();

                ctx.fillStyle = primaryColor;
                roundRect(ox + 2, oy + 2, objW - 4, 4, 2);
                ctx.fill();

                ctx.strokeStyle = 'rgba(0, 0, 0, 0.1)';
                ctx.lineWidth = 1;
                ctx.stroke();
            }

            ctx.restore();
        }

        // ── Main Draw Loop ──
        function draw() {
            ctx.clearRect(0, 0, width, height);

            ctx.save();
            ctx.translate(panOffset.x, panOffset.y);
            ctx.scale(zoomLevel, zoomLevel);

            const mapW = 1024;
            const mapH = 768;

            // 1. Floor Base
            ctx.fillStyle = '#ffffff';
            ctx.fillRect(0, 0, mapW, mapH);

            // Subtle Wood / Tile Pattern
            ctx.fillStyle = '#f8fafc';
            for (let x = 0; x < mapW; x += 64) {
                for (let y = 0; y < mapH; y += 32) {
                    if ((Math.floor(x / 64) + Math.floor(y / 32)) % 2 === 0) {
                        ctx.fillRect(x, y, 64, 32);
                    }
                }
            }

            // Floor Border
            ctx.strokeStyle = '#cbd5e1';
            ctx.lineWidth = 4;
            ctx.strokeRect(0, 0, mapW, mapH);

            // 2. Grid Lines
            if (showGrid) {
                ctx.strokeStyle = 'rgba(1, 44, 65, 0.06)';
                ctx.lineWidth = 1;
                for (let x = 0; x <= mapW; x += TILE_SIZE) {
                    ctx.beginPath(); ctx.moveTo(x, 0); ctx.lineTo(x, mapH); ctx.stroke();
                }
                for (let y = 0; y <= mapH; y += TILE_SIZE) {
                    ctx.beginPath(); ctx.moveTo(0, y); ctx.lineTo(mapW, y); ctx.stroke();
                }
            }

            // 3. Audio Zones
            zones.forEach(z => {
                const zx = z.shape_data.x * TILE_SIZE;
                const zy = z.shape_data.y * TILE_SIZE;
                const zw = z.shape_data.width * TILE_SIZE;
                const zh = z.shape_data.height * TILE_SIZE;

                ctx.fillStyle = 'rgba(0, 180, 179, 0.08)';
                ctx.fillRect(zx, zy, zw, zh);

                const isSelected = selectedItem && selectedItem.type === 'zone' && selectedItem.item === z;
                ctx.strokeStyle = isSelected ? 'var(--brand-navy)' : 'rgba(0, 180, 179, 0.4)';
                ctx.lineWidth = isSelected ? 3 : 1.5;
                ctx.setLineDash([4, 4]);
                ctx.strokeRect(zx, zy, zw, zh);
                ctx.setLineDash([]);

                ctx.fillStyle = '#00726c';
                ctx.font = '700 11px Cairo, Inter';
                ctx.fillText(`🎙️ ${z.name}`, zx + 6, zy + 16);
            });

            // 4. Rooms
            rooms.forEach((r) => {
                const rx = r.bounds.x * TILE_SIZE;
                const ry = r.bounds.y * TILE_SIZE;
                const rw = r.bounds.width * TILE_SIZE;
                const rh = r.bounds.height * TILE_SIZE;

                ctx.fillStyle = 'rgba(241, 245, 249, 0.85)';
                ctx.fillRect(rx, ry, rw, rh);

                const isSelected = selectedItem && selectedItem.type === 'room' && selectedItem.item === r;
                ctx.strokeStyle = isSelected ? '#012c41' : (r.color || '#00b4b3');
                ctx.lineWidth = isSelected ? 4 : 2;
                ctx.strokeRect(rx, ry, rw, rh);

                ctx.fillStyle = '#012c41';
                ctx.font = '800 12px Cairo, Inter';
                ctx.fillText(`🏢 ${r.name}`, rx + 8, ry + 18);
            });

            // 5. Furniture Objects
            objects.forEach((obj) => {
                const ox = obj.position.x * TILE_SIZE;
                const oy = obj.position.y * TILE_SIZE;
                const objW = (obj.width || (obj.size ? obj.size.width : 1)) * TILE_SIZE;
                const objH = (obj.height || (obj.size ? obj.size.height : 1)) * TILE_SIZE;

                drawEnhancedOfficeFurniture(ctx, obj, ox, oy, objW, objH);

                const isSelected = selectedItem && selectedItem.type === 'object' && selectedItem.item === obj;
                if (isSelected) {
                    ctx.strokeStyle = '#00b4b3';
                    ctx.lineWidth = 2.5;
                    ctx.strokeRect(ox - 1, oy - 1, objW + 2, objH + 2);
                }
            });

            // 6. Drawing Preview
            if (isDrawing && currentRect) {
                const dx = currentRect.x * TILE_SIZE;
                const dy = currentRect.y * TILE_SIZE;
                const dw = currentRect.width * TILE_SIZE;
                const dh = currentRect.height * TILE_SIZE;

                ctx.fillStyle = currentTool === 'room' ? 'rgba(0, 180, 179, 0.2)' : 'rgba(0, 104, 71, 0.2)';
                ctx.fillRect(dx, dy, dw, dh);
                ctx.strokeStyle = currentTool === 'room' ? '#00b4b3' : '#006847';
                ctx.lineWidth = 2;
                ctx.strokeRect(dx, dy, dw, dh);
            }

            ctx.restore();
        }

        // ── API Actions (Save Draft & Publish) ──
        async function saveMapDraft() {
            try {
                for (const r of rooms) {
                    const payload = {
                        map_id: MAP_DATA.id,
                        name: r.name,
                        type: r.type || 'meeting',
                        access_mode: r.access_mode || 'public',
                        capacity: r.capacity || 10,
                        color: r.color || '#00b4b3',
                        bounds: r.bounds
                    };

                    if (!r.id) {
                        const res = await fetch(`/api/v1/organizations/${ORG_ID}/rooms`, {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': CSRF_TOKEN },
                            credentials: 'same-origin',
                            body: JSON.stringify(payload)
                        });
                        const data = await res.json();
                        if (data.room?.id) r.id = data.room.id;
                    } else {
                        await fetch(`/api/v1/organizations/${ORG_ID}/rooms/${r.id}`, {
                            method: 'PATCH',
                            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': CSRF_TOKEN },
                            credentials: 'same-origin',
                            body: JSON.stringify(payload)
                        });
                    }
                }

                await fetch(`/api/v1/organizations/${ORG_ID}/maps/${MAP_DATA.id}/objects/sync`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': CSRF_TOKEN },
                    credentials: 'same-origin',
                    body: JSON.stringify({ objects })
                });

                showToast('✅ {{ __('Map Draft & Furniture Saved!') }}');
                return true;
            } catch (err) {
                console.error(err);
                showToast('❌ Error saving draft', '#d20005');
                return false;
            }
        }

        async function publishMap() {
            try {
                const saved = await saveMapDraft();
                if (!saved) return;

                const res = await fetch(`/api/v1/organizations/${ORG_ID}/maps/${MAP_DATA.id}/publish`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': CSRF_TOKEN },
                    credentials: 'same-origin'
                });

                if (!res.ok) {
                    const errData = await res.json();
                    throw new Error(errData.message || 'Publishing failed.');
                }

                const data = await res.json();
                document.getElementById('header-version-badge').textContent = `v${data.map.version} (Published)`;
                showToast(`🚀 {{ __('Map Published! Snapshot is now live.') }}`);
            } catch (err) {
                console.error(err);
                showToast(`❌ Error: ${err.message || 'Could not publish map'}`, '#d20005');
            }
        }

        function showToast(msg, bg) {
            const toast = document.getElementById('toast-msg');
            toast.textContent = msg;
            if (bg) toast.style.background = bg;
            else toast.style.background = 'var(--brand-green)';
            toast.style.display = 'block';
            setTimeout(() => { toast.style.display = 'none'; }, 3500);
        }

        updateStats();
        draw();
    </script>
</body>
</html>
