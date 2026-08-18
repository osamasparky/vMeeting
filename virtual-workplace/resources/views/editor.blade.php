<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}" data-theme="dark">
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
            --bg-body: #090d16;
            --bg-panel: #0f172a;
            --bg-card: #1e293b;
            --bg-card-hover: #334155;
            --bg-input: rgba(15, 23, 42, 0.85);
            --border-panel: rgba(255, 255, 255, 0.08);
            --border-hover: rgba(255, 255, 255, 0.18);

            --brand-primary: #3b82f6;
            --brand-teal: #06b6d4;
            --brand-pine: #0d9488;
            --brand-green: #10b981;
            --brand-gold: #f59e0b;
            --brand-crimson: #ef4444;

            --text-main: #f8fafc;
            --text-muted: #94a3b8;
            --text-dim: #64748b;

            --font-family: {{ app()->getLocale() === 'ar' ? "'Cairo', sans-serif" : "'Inter', 'Cairo', sans-serif" }};
        }

        [data-theme="light"] {
            --bg-body: #f1f5f9;
            --bg-panel: #ffffff;
            --bg-card: #f8fafc;
            --bg-card-hover: #e2e8f0;
            --bg-input: #ffffff;
            --border-panel: #e2e8f0;
            --border-hover: #cbd5e1;
            --text-main: #0f172a;
            --text-muted: #64748b;
            --text-dim: #94a3b8;
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
            height: 60px;
            background: var(--bg-panel);
            border-bottom: 1px solid var(--border-panel);
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 18px;
            z-index: 50;
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.2);
        }

        .header-left {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .back-btn {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid var(--border-panel);
            color: var(--text-main);
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
            border-color: var(--brand-primary);
            background: rgba(59, 130, 246, 0.1);
            color: #60a5fa;
        }

        .map-title-group {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .map-name {
            font-size: 15px;
            font-weight: 800;
            color: var(--text-main);
            letter-spacing: -0.2px;
        }

        .version-badge {
            background: rgba(59, 130, 246, 0.15);
            border: 1px solid rgba(59, 130, 246, 0.35);
            color: #60a5fa;
            padding: 3px 9px;
            border-radius: 6px;
            font-size: 11px;
            font-weight: 800;
        }

        .header-center {
            display: flex;
            align-items: center;
            gap: 6px;
            background: var(--bg-body);
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
            color: var(--text-main);
            background: rgba(255, 255, 255, 0.05);
        }
        .tool-chip.active {
            background: var(--brand-primary);
            color: white;
            border-color: var(--brand-primary);
            box-shadow: 0 2px 10px rgba(59, 130, 246, 0.35);
        }

        .header-right {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .action-btn {
            padding: 7px 14px;
            border-radius: 10px;
            font-size: 12px;
            font-weight: 800;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 6px;
            border: none;
            transition: all 0.2s;
            text-decoration: none;
        }
        .action-btn.primary {
            background: var(--brand-primary);
            color: white;
            box-shadow: 0 2px 8px rgba(59, 130, 246, 0.3);
        }
        .action-btn.success {
            background: linear-gradient(135deg, var(--brand-green), #059669);
            color: white;
            box-shadow: 0 2px 8px rgba(16, 185, 129, 0.3);
        }
        .action-btn.secondary {
            background: rgba(255, 255, 255, 0.06);
            border: 1px solid var(--border-panel);
            color: var(--text-main);
        }
        .action-btn:hover {
            transform: translateY(-1px);
            opacity: 0.92;
        }

        /* ── Workspace ── */
        .editor-workspace {
            display: flex;
            flex: 1;
            position: relative;
            overflow: hidden;
        }

        .canvas-container {
            flex: 1;
            position: relative;
            background: var(--bg-body);
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        #editor-canvas {
            display: block;
            cursor: crosshair;
        }

        /* ── Floating Controls ── */
        .canvas-view-tools {
            position: absolute;
            bottom: 20px;
            inset-inline-start: 20px;
            display: flex;
            align-items: center;
            gap: 8px;
            background: rgba(15, 23, 42, 0.85);
            backdrop-filter: blur(12px);
            padding: 6px 10px;
            border-radius: 14px;
            border: 1px solid var(--border-panel);
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.35);
            z-index: 40;
        }
        .canvas-tool-btn {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid var(--border-panel);
            color: var(--text-main);
            width: 32px;
            height: 32px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 13px;
            cursor: pointer;
            transition: all 0.15s;
        }
        .canvas-tool-btn:hover {
            background: rgba(255, 255, 255, 0.12);
            border-color: var(--brand-primary);
        }

        /* Floating Quick Action Bar (Over Selected Item) */
        .floating-item-actions {
            position: absolute;
            display: none;
            align-items: center;
            gap: 6px;
            background: rgba(15, 23, 42, 0.95);
            backdrop-filter: blur(12px);
            border: 1px solid var(--brand-primary);
            padding: 4px 8px;
            border-radius: 10px;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.45);
            z-index: 45;
            transform: translate(-50%, -100%);
            margin-top: -10px;
        }
        .float-act-btn {
            background: rgba(255, 255, 255, 0.06);
            border: 1px solid var(--border-panel);
            color: var(--text-main);
            padding: 4px 8px;
            border-radius: 6px;
            font-size: 11px;
            font-weight: 700;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 4px;
            transition: all 0.15s;
        }
        .float-act-btn:hover {
            background: var(--brand-primary);
            color: white;
        }

        /* ── Right Customizer Drawer ── */
        .customize-drawer {
            width: 360px;
            background: var(--bg-panel);
            border-inline-start: 1px solid var(--border-panel);
            display: flex;
            flex-direction: column;
            z-index: 40;
            transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: -4px 0 20px rgba(0, 0, 0, 0.25);
        }
        .customize-drawer.collapsed {
            transform: translateX(100%);
        }
        [dir="rtl"] .customize-drawer.collapsed {
            transform: translateX(-100%);
        }

        .drawer-header {
            padding: 14px 18px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            border-bottom: 1px solid var(--border-panel);
        }
        .drawer-title {
            font-size: 14px;
            font-weight: 800;
            color: var(--text-main);
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .drawer-tabs {
            display: flex;
            border-bottom: 1px solid var(--border-panel);
            background: var(--bg-body);
        }
        .drawer-tab {
            flex: 1;
            text-align: center;
            padding: 10px 4px;
            font-size: 12px;
            font-weight: 700;
            color: var(--text-muted);
            cursor: pointer;
            border-bottom: 2px solid transparent;
            transition: all 0.2s;
        }
        .drawer-tab:hover {
            color: var(--text-main);
        }
        .drawer-tab.active {
            color: var(--brand-primary);
            border-bottom-color: var(--brand-primary);
            background: rgba(59, 130, 246, 0.06);
        }

        .drawer-content {
            flex: 1;
            overflow-y: auto;
            padding: 16px;
            display: flex;
            flex-direction: column;
            gap: 16px;
        }

        .search-input {
            width: 100%;
            background: var(--bg-input);
            border: 1px solid var(--border-panel);
            border-radius: 10px;
            padding: 9px 12px;
            color: var(--text-main);
            font-size: 12px;
            font-weight: 600;
            outline: none;
            transition: border-color 0.2s;
        }
        .search-input:focus {
            border-color: var(--brand-primary);
        }

        .category-filter-bar {
            display: flex;
            gap: 6px;
            overflow-x: auto;
            padding-bottom: 4px;
            scrollbar-width: thin;
        }
        .category-filter-bar::-webkit-scrollbar {
            height: 4px;
        }
        .category-filter-bar::-webkit-scrollbar-thumb {
            background: rgba(255, 255, 255, 0.1);
            border-radius: 4px;
        }
        .cat-filter-chip {
            background: var(--bg-card);
            border: 1px solid var(--border-panel);
            color: var(--text-muted);
            padding: 5px 10px;
            border-radius: 8px;
            font-size: 11px;
            font-weight: 700;
            white-space: nowrap;
            cursor: pointer;
            transition: all 0.15s;
        }
        .cat-filter-chip:hover {
            color: var(--text-main);
            border-color: var(--brand-primary);
        }
        .cat-filter-chip.active {
            background: var(--brand-primary);
            color: white;
            border-color: var(--brand-primary);
        }

        .category-accordion {
            background: var(--bg-card);
            border: 1px solid var(--border-panel);
            border-radius: 12px;
            overflow: hidden;
        }
        .category-header {
            padding: 12px 14px;
            font-size: 13px;
            font-weight: 800;
            color: var(--text-main);
            display: flex;
            align-items: center;
            justify-content: space-between;
            cursor: pointer;
            background: rgba(255, 255, 255, 0.02);
            transition: background 0.2s;
        }
        .category-header:hover {
            background: rgba(255, 255, 255, 0.05);
        }
        .category-body {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 8px;
            padding: 10px;
            border-top: 1px solid var(--border-panel);
        }

        .furn-card {
            background: var(--bg-panel);
            border: 1px solid var(--border-panel);
            border-radius: 10px;
            padding: 10px 8px;
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
            gap: 6px;
            cursor: pointer;
            transition: all 0.2s;
        }
        .furn-card:hover {
            border-color: var(--brand-primary);
            background: var(--bg-card-hover);
            transform: translateY(-2px);
        }
        .furn-card.active {
            border-color: var(--brand-primary);
            background: rgba(59, 130, 246, 0.15);
            box-shadow: 0 0 0 1.5px var(--brand-primary);
        }
        .furn-preview-icon {
            height: 48px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .furn-preview-icon img {
            max-height: 44px;
            max-width: 100%;
            object-fit: contain;
            filter: drop-shadow(0 2px 4px rgba(0,0,0,0.3));
        }
        .furn-name {
            font-size: 11px;
            font-weight: 700;
            color: var(--text-main);
            line-height: 1.3;
            max-height: 28px;
            overflow: hidden;
        }

        /* ── Properties Box ── */
        .properties-box {
            background: var(--bg-card);
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
            color: var(--text-muted);
            text-transform: uppercase;
        }
        .prop-input {
            width: 100%;
            background: var(--bg-input);
            border: 1px solid var(--border-panel);
            border-radius: 8px;
            padding: 8px 10px;
            color: var(--text-main);
            font-size: 12px;
            font-weight: 600;
            outline: none;
        }
        .prop-input:focus {
            border-color: var(--brand-primary);
        }

        .rotation-pill-group {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 6px;
        }
        .rot-pill {
            background: var(--bg-panel);
            border: 1px solid var(--border-panel);
            color: var(--text-main);
            padding: 6px 0;
            border-radius: 6px;
            font-size: 11px;
            font-weight: 800;
            text-align: center;
            cursor: pointer;
            transition: all 0.15s;
        }
        .rot-pill:hover, .rot-pill.active {
            background: var(--brand-primary);
            border-color: var(--brand-primary);
            color: white;
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
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.5);
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
            <a href="{{ route('office') }}" class="back-btn">
                <span>🏢</span>
                <span>{{ __('Virtual Office') }}</span>
            </a>
            <div class="map-title-group">
                <div class="map-name">{{ __('Map Editor') }}: {{ $map->name }} <span style="font-size: 12px; font-weight: 700; color: var(--text-muted);">({{ $organization->name }})</span></div>
                <span class="version-badge" id="header-version-badge">v{{ $map->version }} ({{ ucfirst($map->status) }})</span>
            </div>
        </div>

        <div class="header-center">
            <button class="tool-chip active" id="tool-select" onclick="setTool('select')">
                <span>🖱️</span> {{ __('Select / Edit') }}
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
            <button class="tool-chip" id="btn-rotate-top" onclick="rotateSelectedItem(90)" title="{{ __('Rotate 90° (R)') }}">
                <span>🔄</span> {{ __('Rotate') }}
            </button>
            <button class="tool-chip" id="btn-delete-selected" onclick="deleteSelectedItem()" style="color: var(--brand-crimson);">
                <span>🗑️</span> {{ __('Delete') }}
            </button>
        </div>

        <div class="header-right">
            @if(app()->getLocale() === 'ar')
                <a href="{{ route('lang.switch', 'en') }}" class="back-btn" title="Switch to English">🌐 English</a>
            @else
                <a href="{{ route('lang.switch', 'ar') }}" class="back-btn" title="التبديل إلى العربية">🌐 العربية</a>
            @endif

            <button class="action-btn secondary" id="btn-save-draft" onclick="saveMapDraft()">
                <span>💾</span> {{ __('Save Draft') }}
            </button>
            <button class="action-btn success" id="btn-publish" onclick="publishMap()">
                <span>🚀</span> {{ __('Publish Map') }}
            </button>
            <a href="{{ route('office') }}" class="action-btn primary">
                <span>👁️</span> {{ __('Live View') }}
            </a>
        </div>
    </header>

    <!-- Main Workspace -->
    <div class="editor-workspace">

        <!-- Canvas Viewport -->
        <div class="canvas-container" id="canvas-container">
            <canvas id="editor-canvas"></canvas>

            <!-- Floating Selected Object Action Bar -->
            <div class="floating-item-actions" id="floating-actions">
                <button class="float-act-btn" onclick="rotateSelectedItem(90)">🔄 +90°</button>
                <button class="float-act-btn" onclick="duplicateSelectedItem()">📋 {{ __('Clone') }}</button>
                <button class="float-act-btn" onclick="deleteSelectedItem()" style="color: #f87171;">🗑️</button>
            </div>

            <!-- Floating View Controls -->
            <div class="canvas-view-tools">
                <button class="canvas-tool-btn" onclick="toggleCustomizeDrawer()" title="{{ __('Toggle Drawer') }}">🪑</button>
                <button class="canvas-tool-btn" onclick="zoomIn()" title="{{ __('Zoom In') }}">➕</button>
                <button class="canvas-tool-btn" onclick="zoomOut()" title="{{ __('Zoom Out') }}">➖</button>
                <button class="canvas-tool-btn" onclick="resetView()" title="{{ __('Reset View') }}">🏠</button>
                <button class="canvas-tool-btn" onclick="toggleGrid()" title="{{ __('Toggle Grid') }}">🔲</button>
            </div>
        </div>

        <!-- Right Customizer Drawer -->
        <aside class="customize-drawer" id="customize-drawer">
            <div class="drawer-header">
                <div class="drawer-title">
                    <span>✨</span>
                    <span>{{ __('Customize Floor & Furniture') }}</span>
                </div>
                <button onclick="toggleCustomizeDrawer()" style="background:none; border:none; color:var(--text-muted); font-size:16px; cursor:pointer;">✕</button>
            </div>

            <!-- Sub Tabs -->
            <div class="drawer-tabs">
                <div class="drawer-tab active" id="tab-btn-furniture" onclick="switchDrawerTab('furniture')">
                    {{ __('3D Furniture') }}
                </div>
                <div class="drawer-tab" id="tab-btn-inspector" onclick="switchDrawerTab('inspector')">
                    {{ __('Selected Item') }}
                </div>
                <div class="drawer-tab" id="tab-btn-rooms" onclick="switchDrawerTab('rooms')">
                    {{ __('Rooms') }}
                </div>
            </div>

            <div class="drawer-content">

                <!-- 1. FURNITURE TAB -->
                <div id="drawer-view-furniture" style="display: flex; flex-direction: column; gap: 12px;">
                    <input type="text" id="furniture-search" class="search-input" placeholder="🔍 {{ __('Search 3D furniture, desks, chairs...') }}" oninput="filterFurniture(this.value)">

                    <!-- Quick Category Filter Pills -->
                    <div class="category-filter-bar" id="category-filter-bar">
                        <button class="cat-filter-chip active" onclick="filterByCategory('all')">🌟 {{ __('All Categories') }}</button>
                        @foreach($furnitureCategories as $cat)
                            <button class="cat-filter-chip" onclick="filterByCategory('{{ $cat->slug }}')">{{ $cat->icon }} {{ $cat->name }}</button>
                        @endforeach
                    </div>

                    @foreach($furnitureCategories as $idx => $cat)
                    <div class="category-accordion" id="cat-{{ $cat->slug }}">
                        <div class="category-header" onclick="toggleAccordion('cat-{{ $cat->slug }}')">
                            <span>{{ $cat->icon }} {{ $cat->name }} ({{ $cat->items->count() }})</span>
                            <span>▾</span>
                        </div>
                        <div class="category-body" style="{{ $idx === 0 ? 'display: grid;' : 'display: none;' }}">
                            @foreach($cat->items as $item)
                                <div class="furn-card" onclick="selectFurnitureItem('{{ $item->slug }}', '{{ $item->colors[0] ?? '#3b82f6' }}', '{{ $item->image_url }}', {{ $item->width }}, {{ $item->height }}, {{ $item->collision ? 'true' : 'false' }})">
                                    <div class="furn-preview-icon">
                                        @if($item->image_url)
                                            <img src="{{ $item->image_url }}" alt="{{ $item->name }}" loading="lazy" decoding="async">
                                        @else
                                            <span style="font-size: 24px;">{{ $item->icon }}</span>
                                        @endif
                                    </div>
                                    <div class="furn-name">{{ $item->name }}</div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                    @endforeach
                </div>

                <!-- 2. SELECTED ITEM INSPECTOR -->
                <div id="drawer-view-inspector" style="display: none; flex-direction: column; gap: 14px;">
                    <div class="properties-box" id="inspector-empty-msg">
                        <div style="font-size: 13px; color: var(--text-muted); text-align: center; padding: 20px 0;">
                            👆 {{ __('Click any object or room on the map to edit its position, rotation, dimensions and settings.') }}
                        </div>
                    </div>

                    <div class="properties-box" id="inspector-content" style="display: none;">
                        <strong style="font-size: 13px; color: var(--text-main);">{{ __('Object Properties') }}</strong>
                        
                        <div class="prop-field">
                            <label class="prop-label">{{ __('Name') }}</label>
                            <input type="text" class="prop-input" id="prop-name" oninput="updateSelectedProp('name', this.value)">
                        </div>

                        <div class="prop-field" id="prop-rotation-group">
                            <label class="prop-label">{{ __('Rotation (Degrees)') }}</label>
                            <div class="rotation-pill-group">
                                <div class="rot-pill" onclick="setRotation(0)">0°</div>
                                <div class="rot-pill" onclick="setRotation(90)">90°</div>
                                <div class="rot-pill" onclick="setRotation(180)">180°</div>
                                <div class="rot-pill" onclick="setRotation(270)">270°</div>
                            </div>
                            <button onclick="rotateSelectedItem(90)" class="action-btn secondary" style="margin-top: 6px; justify-content: center;">
                                🔄 {{ __('Rotate +90°') }}
                            </button>
                        </div>

                        <div class="prop-field" id="prop-size-group">
                            <label class="prop-label">{{ __('Grid Footprint (Tiles)') }}</label>
                            <div style="display: flex; gap: 8px;">
                                <input type="number" class="prop-input" id="prop-width" placeholder="W" min="1" max="10" oninput="updateSelectedProp('width', parseInt(this.value) || 1)">
                                <input type="number" class="prop-input" id="prop-height" placeholder="H" min="1" max="10" oninput="updateSelectedProp('height', parseInt(this.value) || 1)">
                            </div>
                        </div>

                        <div class="prop-field" id="prop-color-group" style="display: none;">
                            <label class="prop-label">{{ __('Theme Color') }}</label>
                            <input type="color" class="prop-input" id="prop-color" value="#3b82f6" style="height: 38px; padding: 2px;" oninput="updateSelectedProp('color', this.value)">
                        </div>

                        <div style="display: flex; gap: 8px; margin-top: 10px;">
                            <button onclick="duplicateSelectedItem()" class="action-btn secondary" style="flex: 1; justify-content: center;">
                                📋 {{ __('Clone') }}
                            </button>
                            <button onclick="deleteSelectedItem()" class="action-btn" style="background: rgba(239, 68, 68, 0.15); border: 1px solid rgba(239, 68, 68, 0.4); color: #fca5a5; justify-content: center;">
                                🗑️ {{ __('Delete') }}
                            </button>
                        </div>
                    </div>
                </div>

                <!-- 3. ROOMS TAB -->
                <div id="drawer-view-rooms" style="display: none; flex-direction: column; gap: 14px;">
                    <div style="font-size: 12px; color: var(--text-muted); font-weight: 600;">
                        {{ __('Choose a room template, then click and drag on the floor canvas to draw.') }}
                    </div>

                    <div class="furn-card" onclick="setRoomTemplate('meeting', '#6366F1')" style="flex-direction: row; justify-content: flex-start; text-align: left; padding: 12px;">
                        <span style="font-size: 24px;">👥</span>
                        <div>
                            <strong style="font-size: 13px; color: var(--text-main);">{{ __('Meeting Room') }}</strong>
                            <div style="font-size: 11px; color: var(--text-muted);">{{ __('Open collaborative team area') }}</div>
                        </div>
                    </div>

                    <div class="furn-card" onclick="setRoomTemplate('private', '#F59E0B')" style="flex-direction: row; justify-content: flex-start; text-align: left; padding: 12px;">
                        <span style="font-size: 24px;">🔒</span>
                        <div>
                            <strong style="font-size: 13px; color: var(--text-main);">{{ __('Private Office') }}</strong>
                            <div style="font-size: 11px; color: var(--text-muted);">{{ __('Locked door with knock approval') }}</div>
                        </div>
                    </div>

                    <div class="furn-card" onclick="setRoomTemplate('reception', '#10B981')" style="flex-direction: row; justify-content: flex-start; text-align: left; padding: 12px;">
                        <span style="font-size: 24px;">🛎️</span>
                        <div>
                            <strong style="font-size: 13px; color: var(--text-main);">{{ __('Reception Lobby') }}</strong>
                            <div style="font-size: 11px; color: var(--text-muted);">{{ __('Welcoming area for guests') }}</div>
                        </div>
                    </div>
                </div>

            </div>
        </aside>
    </div>

    <!-- Notification Toast -->
    <div class="toast" id="toast-msg">Map Saved Successfully!</div>

    <!-- JavaScript Map Editor Engine -->
    <script>
        const MAP_DATA = @json($map);
        const ORG_ID = "{{ $organization->id }}";
        const CSRF_TOKEN = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        const canvas = document.getElementById('editor-canvas');
        const ctx = canvas.getContext('2d');
        let width = canvas.width = window.innerWidth - 360;
        let height = canvas.height = window.innerHeight - 60;

        window.addEventListener('resize', () => {
            const drawer = document.getElementById('customize-drawer');
            const drawerWidth = drawer.classList.contains('collapsed') ? 0 : 360;
            width = canvas.width = window.innerWidth - drawerWidth;
            height = canvas.height = window.innerHeight - 60;
            draw();
        });

        const TILE_SIZE = 32;
        let currentTool = 'select'; // select | room | zone | object
        let currentRoomType = 'meeting';
        let currentRoomColor = '#6366F1';
        let currentObjectType = 'FUR-DESK-EMP-001';
        let currentObjectColor = '#3b82f6';
        let currentObjectCustom = null;

        let zoomLevel = 1.0;
        let panOffset = { x: 50, y: 40 };
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

        // ── 3D Sprite Cache Map ──
        const ALL_FURNITURE_ITEMS = @json($furnitureItems ?? []);
        const CUSTOM_IMAGE_CACHE = {};
        ALL_FURNITURE_ITEMS.forEach(it => {
            if (it.image_url) {
                const img = new Image();
                img.src = it.image_url;
                img.onload = () => draw();
                CUSTOM_IMAGE_CACHE[it.slug] = { img, width: it.width || 1, height: it.height || 1 };
                CUSTOM_IMAGE_CACHE[it.image_url] = { img, width: it.width || 1, height: it.height || 1 };
            }
        });

        function getLoadedImage(url) {
            if (!url) return null;
            if (!CUSTOM_IMAGE_CACHE[url]) {
                const img = new Image();
                img.src = url;
                img.onload = () => draw();
                CUSTOM_IMAGE_CACHE[url] = { img, width: 1, height: 1 };
            }
            return CUSTOM_IMAGE_CACHE[url].img;
        }

        // ── Tool Selection ──
        function setTool(tool) {
            currentTool = tool;
            document.querySelectorAll('.tool-chip').forEach(el => el.classList.remove('active'));
            document.getElementById(`tool-${tool}`)?.classList.add('active');
            canvas.style.cursor = tool === 'select' ? 'default' : 'crosshair';
            if (tool !== 'select') hideFloatingActions();
        }

        function setRoomTemplate(type, color) {
            setTool('room');
            currentRoomType = type;
            currentRoomColor = color;
        }

        function selectFurnitureItem(slug, color, imageUrl = null, w = 1, h = 1, collision = true) {
            setTool('object');
            currentObjectType = slug;
            currentObjectColor = color || '#3b82f6';
            currentObjectCustom = {
                imageUrl: imageUrl || null,
                width: w || 1,
                height: h || 1,
                collision: Boolean(collision)
            };
            document.querySelectorAll('.furn-card').forEach(el => el.classList.remove('active'));
            if (event && event.currentTarget) {
                event.currentTarget.classList.add('active');
            }
        }

        // ── Navigation & Zoom ──
        function zoomIn() { zoomLevel = Math.min(2.5, zoomLevel + 0.15); draw(); }
        function zoomOut() { zoomLevel = Math.max(0.4, zoomLevel - 0.15); draw(); }
        function resetView() { zoomLevel = 1.0; panOffset = { x: 50, y: 40 }; draw(); }
        function toggleGrid() { showGrid = !showGrid; draw(); }
        function toggleCustomizeDrawer() {
            const drawer = document.getElementById('customize-drawer');
            drawer.classList.toggle('collapsed');
            const drawerWidth = drawer.classList.contains('collapsed') ? 0 : 360;
            width = canvas.width = window.innerWidth - drawerWidth;
            draw();
        }

        function switchDrawerTab(tab) {
            document.querySelectorAll('.drawer-tab').forEach(el => el.classList.remove('active'));
            document.getElementById(`tab-btn-${tab}`)?.classList.add('active');
            document.getElementById('drawer-view-furniture').style.display = tab === 'furniture' ? 'flex' : 'none';
            document.getElementById('drawer-view-inspector').style.display = tab === 'inspector' ? 'flex' : 'none';
            document.getElementById('drawer-view-rooms').style.display = tab === 'rooms' ? 'flex' : 'none';
        }

        function toggleAccordion(id) {
            const body = document.querySelector(`#${id} .category-body`);
            if (body) body.style.display = body.style.display === 'none' ? 'grid' : 'none';
        }

        function filterByCategory(slug) {
            document.querySelectorAll('.cat-filter-chip').forEach(c => c.classList.remove('active'));
            if (event && event.currentTarget) event.currentTarget.classList.add('active');

            document.querySelectorAll('.category-accordion').forEach(acc => {
                if (slug === 'all' || acc.id === `cat-${slug}`) {
                    acc.style.display = 'block';
                    const body = acc.querySelector('.category-body');
                    if (body) body.style.display = 'grid';
                } else {
                    acc.style.display = 'none';
                }
            });
        }

        function filterFurniture(q) {
            const term = q.toLowerCase();
            document.querySelectorAll('.furn-card').forEach(card => {
                const name = card.querySelector('.furn-name')?.textContent.toLowerCase() || '';
                card.style.display = name.includes(term) ? 'flex' : 'none';
            });
            document.querySelectorAll('.category-accordion').forEach(acc => {
                const visibleCards = acc.querySelectorAll('.furn-card[style*="display: flex"], .furn-card:not([style*="display: none"])');
                acc.style.display = (visibleCards.length > 0 || !term) ? 'block' : 'none';
            });
        }

        let roomContainedObjects = [];

        // ── Canvas Interaction Handlers ──
        canvas.addEventListener('mousedown', (e) => {
            const rect = canvas.getBoundingClientRect();
            const mouseX = (e.clientX - rect.left - panOffset.x) / zoomLevel;
            const mouseY = (e.clientY - rect.top - panOffset.y) / zoomLevel;

            const tileX = Math.floor(mouseX / TILE_SIZE);
            const tileY = Math.floor(mouseY / TILE_SIZE);

            if (currentTool === 'select') {
                // Find clicked object
                let clicked = null;
                for (let i = objects.length - 1; i >= 0; i--) {
                    const obj = objects[i];
                    const ow = obj.width || (obj.size ? obj.size.width : 1);
                    const oh = obj.height || (obj.size ? obj.size.height : 1);
                    if (tileX >= obj.position.x && tileX < obj.position.x + ow &&
                        tileY >= obj.position.y && tileY < obj.position.y + oh) {
                        clicked = { type: 'object', item: obj };
                        break;
                    }
                }

                // If no object, check room
                if (!clicked) {
                    for (let i = rooms.length - 1; i >= 0; i--) {
                        const r = rooms[i];
                        if (tileX >= r.bounds.x && tileX < r.bounds.x + r.bounds.width &&
                            tileY >= r.bounds.y && tileY < r.bounds.y + r.bounds.height) {
                            clicked = { type: 'room', item: r };
                            break;
                        }
                    }
                }

                selectedItem = clicked;
                if (selectedItem) {
                    isDragging = true;
                    dragStartTileX = tileX;
                    dragStartTileY = tileY;

                    if (selectedItem.type === 'object') {
                        dragOrigX = selectedItem.item.position.x;
                        dragOrigY = selectedItem.item.position.y;
                        roomContainedObjects = [];
                    } else if (selectedItem.type === 'room') {
                        dragOrigX = selectedItem.item.bounds.x;
                        dragOrigY = selectedItem.item.bounds.y;
                        const rb = selectedItem.item.bounds;

                        // Find and link all furniture contained within this room
                        roomContainedObjects = objects.filter(obj => {
                            const ox = (obj.position ? obj.position.x : 0);
                            const oy = (obj.position ? obj.position.y : 0);
                            return (ox >= rb.x && ox < rb.x + rb.width && oy >= rb.y && oy < rb.y + rb.height);
                        }).map(obj => ({
                            obj: obj,
                            relX: (obj.position ? obj.position.x : 0) - rb.x,
                            relY: (obj.position ? obj.position.y : 0) - rb.y
                        }));
                    }
                    canvas.style.cursor = 'grabbing';
                }

                updateInspector();
                updateFloatingActions();
                draw();
            } else if (currentTool === 'room' || currentTool === 'zone') {
                isDrawing = true;
                startX = tileX;
                startY = tileY;
                currentRect = { x: tileX, y: tileY, width: 1, height: 1 };
            } else if (currentTool === 'object') {
                const newObj = {
                    type: currentObjectType,
                    name: `Object #${objects.length + 1}`,
                    position: { x: tileX, y: tileY, rotation: 0 },
                    color: currentObjectColor,
                    image_url: currentObjectCustom?.imageUrl || null,
                    width: currentObjectCustom?.width || 1,
                    height: currentObjectCustom?.height || 1,
                    collision: currentObjectCustom ? currentObjectCustom.collision : true
                };
                objects.push(newObj);
                selectedItem = { type: 'object', item: newObj };
                setTool('select');
                updateInspector();
                updateFloatingActions();
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
                    selectedItem.item.position.y = Math.max(0, Math.min(31, dragOrigY + dy));
                } else if (selectedItem.type === 'room') {
                    const newRoomX = Math.max(0, Math.min(32 - selectedItem.item.bounds.width, dragOrigX + dx));
                    const newRoomY = Math.max(0, Math.min(32 - selectedItem.item.bounds.height, dragOrigY + dy));
                    selectedItem.item.bounds.x = newRoomX;
                    selectedItem.item.bounds.y = newRoomY;

                    // Automatically move all contained furniture together!
                    if (roomContainedObjects && roomContainedObjects.length > 0) {
                        roomContainedObjects.forEach(entry => {
                            if (entry.obj && entry.obj.position) {
                                entry.obj.position.x = Math.max(0, Math.min(31, newRoomX + entry.relX));
                                entry.obj.position.y = Math.max(0, Math.min(31, newRoomY + entry.relY));
                            }
                        });
                    }

                    // Move associated audio zone
                    const associatedZone = zones.find(z => z.room_id === selectedItem.item.id || (z.metadata && z.metadata.room_id === selectedItem.item.id));
                    if (associatedZone && associatedZone.shape_data) {
                        associatedZone.shape_data.x = newRoomX;
                        associatedZone.shape_data.y = newRoomY;
                    }
                    invalidateEditorBg();
                }
                updateFloatingActions();
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
                roomContainedObjects = [];
                canvas.style.cursor = currentTool === 'select' ? 'default' : 'crosshair';
                invalidateEditorBg();
                updateFloatingActions();
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
                        bounds: { ...currentRect }
                    };
                    rooms.push(newRoom);
                    selectedItem = { type: 'room', item: newRoom };

                    // Automatically create an acoustic audio zone for this room
                    const newAudioZone = {
                        name: `Audio Zone — ${newRoom.name}`,
                        type: 'audio',
                        shape_type: 'rectangle',
                        shape_data: { ...currentRect },
                        audible_radius: 200,
                        metadata: { auto_created: true, room_name: newRoom.name }
                    };
                    zones.push(newAudioZone);

                    invalidateEditorBg();
                    showToast('🏢 Room & Isolated Audio Zone created!');
                }
                isDrawing = false;
                currentRect = null;
                setTool('select');
                updateInspector();
                updateFloatingActions();
                draw();
            }
        });

        // Keyboard Shortcuts
        window.addEventListener('keydown', (e) => {
            if (['input', 'textarea'].includes(document.activeElement.tagName.toLowerCase())) return;
            const k = e.key.toLowerCase();
            if (k === 'r' && selectedItem && selectedItem.type === 'object') {
                rotateSelectedItem(90);
            } else if ((k === 'delete' || k === 'backspace') && selectedItem) {
                deleteSelectedItem();
            } else if (k === 'd' && selectedItem && selectedItem.type === 'object') {
                duplicateSelectedItem();
            } else if (k === 'escape') {
                selectedItem = null;
                updateInspector();
                hideFloatingActions();
                draw();
            }
        });

        // ── Rotation & Object Modification Engine ──
        function rotateSelectedItem(degrees = 90) {
            if (!selectedItem || selectedItem.type !== 'object') return;
            const obj = selectedItem.item;
            if (!obj.position) obj.position = { x: 0, y: 0, rotation: 0 };
            const currentRot = typeof obj.position.rotation === 'number' ? obj.position.rotation : (obj.rotation || 0);
            obj.position.rotation = (currentRot + degrees) % 360;
            obj.rotation = obj.position.rotation;

            updateInspector();
            updateFloatingActions();
            draw();
            showToast(`🔄 Rotated to ${obj.position.rotation}°`);
        }

        function setRotation(deg) {
            if (!selectedItem || selectedItem.type !== 'object') return;
            const obj = selectedItem.item;
            if (!obj.position) obj.position = { x: 0, y: 0, rotation: 0 };
            obj.position.rotation = deg;
            obj.rotation = deg;

            updateInspector();
            updateFloatingActions();
            draw();
        }

        function duplicateSelectedItem() {
            if (!selectedItem || selectedItem.type !== 'object') return;
            const orig = selectedItem.item;
            const cloned = JSON.parse(JSON.stringify(orig));
            delete cloned.id;
            cloned.position.x = Math.min(30, (cloned.position.x || 0) + 1);
            cloned.position.y = Math.min(22, (cloned.position.y || 0) + 1);
            cloned.name = `${cloned.name || 'Object'} (Copy)`;
            objects.push(cloned);
            selectedItem = { type: 'object', item: cloned };

            updateInspector();
            updateFloatingActions();
            draw();
            showToast('📋 Item Duplicated!');
        }

        function deleteSelectedItem() {
            if (!selectedItem) return;
            const item = selectedItem.item;
            if (selectedItem.type === 'object') {
                const idx = objects.indexOf(item);
                if (idx > -1) objects.splice(idx, 1);
                showToast('🗑️ Furniture removed');
            } else if (selectedItem.type === 'room') {
                const idx = rooms.indexOf(item);
                if (idx > -1) rooms.splice(idx, 1);
                invalidateEditorBg();
                if (item.id) {
                    fetch(`/api/v1/organizations/${ORG_ID}/rooms/${item.id}`, {
                        method: 'DELETE',
                        headers: { 'X-CSRF-TOKEN': CSRF_TOKEN, 'Accept': 'application/json' },
                        credentials: 'same-origin'
                    }).catch(console.error);
                }
                showToast('🗑️ Room removed');
            }
            selectedItem = null;
            updateInspector();
            hideFloatingActions();
            draw();
        }

        function updateSelectedProp(prop, val) {
            if (!selectedItem) return;
            if (prop === 'name') selectedItem.item.name = val;
            if (prop === 'color') { selectedItem.item.color = val; invalidateEditorBg(); }
            if (prop === 'width') selectedItem.item.width = val;
            if (prop === 'height') selectedItem.item.height = val;
            draw();
        }

        function updateInspector() {
            const emptyBox = document.getElementById('inspector-empty-msg');
            const contentBox = document.getElementById('inspector-content');
            if (!selectedItem) {
                emptyBox.style.display = 'block';
                contentBox.style.display = 'none';
                return;
            }

            emptyBox.style.display = 'none';
            contentBox.style.display = 'flex';

            const item = selectedItem.item;
            document.getElementById('prop-name').value = item.name || '';
            
            if (selectedItem.type === 'object') {
                document.getElementById('prop-rotation-group').style.display = 'flex';
                document.getElementById('prop-size-group').style.display = 'flex';
                document.getElementById('prop-color-group').style.display = 'none';
                document.getElementById('prop-width').value = item.width || (item.size ? item.size.width : 1);
                document.getElementById('prop-height').value = item.height || (item.size ? item.size.height : 1);

                const rot = (item.position && typeof item.position.rotation === 'number') ? item.position.rotation : (item.rotation || 0);
                document.querySelectorAll('.rot-pill').forEach(pill => {
                    pill.classList.toggle('active', pill.textContent.includes(`${rot}°`));
                });
            } else if (selectedItem.type === 'room') {
                document.getElementById('prop-rotation-group').style.display = 'none';
                document.getElementById('prop-size-group').style.display = 'none';
                document.getElementById('prop-color-group').style.display = 'flex';
                document.getElementById('prop-color').value = item.color || '#6366F1';
            }
        }

        function updateFloatingActions() {
            const floatBox = document.getElementById('floating-actions');
            if (!selectedItem || selectedItem.type !== 'object') {
                floatBox.style.display = 'none';
                return;
            }

            const obj = selectedItem.item;
            const ox = (obj.position ? obj.position.x : 0) * TILE_SIZE;
            const oy = (obj.position ? obj.position.y : 0) * TILE_SIZE;
            const ow = (obj.width || (obj.size ? obj.size.width : 1)) * TILE_SIZE;

            const screenX = ox * zoomLevel + panOffset.x + (ow * zoomLevel) / 2;
            const screenY = oy * zoomLevel + panOffset.y;

            floatBox.style.left = `${screenX}px`;
            floatBox.style.top = `${screenY}px`;
            floatBox.style.display = 'flex';
        }

        function hideFloatingActions() {
            document.getElementById('floating-actions').style.display = 'none';
        }

        // ── High Performance Offscreen Background Buffer ──
        let editorBgCanvas = null;
        let editorBgDirty = true;

        function invalidateEditorBg() {
            editorBgDirty = true;
        }

        function renderEditorStaticBackground() {
            const mapW = (MAP_DATA.width || 32) * TILE_SIZE;
            const mapH = (MAP_DATA.height || 24) * TILE_SIZE;

            if (!editorBgCanvas) {
                editorBgCanvas = document.createElement('canvas');
                editorBgCanvas.width = mapW;
                editorBgCanvas.height = mapH;
            }
            const bctx = editorBgCanvas.getContext('2d');
            bctx.clearRect(0, 0, mapW, mapH);

            // 1. Digital Workplace Main Floor
            bctx.fillStyle = '#0b0f19';
            bctx.fillRect(0, 0, mapW, mapH);

            // Floor Grid Lines
            if (showGrid) {
                bctx.strokeStyle = 'rgba(255, 255, 255, 0.035)';
                bctx.lineWidth = 1;
                for (let x = 0; x <= mapW; x += TILE_SIZE) {
                    bctx.beginPath(); bctx.moveTo(x, 0); bctx.lineTo(x, mapH); bctx.stroke();
                }
                for (let y = 0; y <= mapH; y += TILE_SIZE) {
                    bctx.beginPath(); bctx.moveTo(0, y); bctx.lineTo(mapW, y); bctx.stroke();
                }
            }

            // Floor Outer Boundary
            bctx.strokeStyle = '#1e293b';
            bctx.lineWidth = 3;
            bctx.strokeRect(0, 0, mapW, mapH);

            // 2. Rooms
            rooms.forEach((r) => {
                const rx = r.bounds.x * TILE_SIZE;
                const ry = r.bounds.y * TILE_SIZE;
                const rw = r.bounds.width * TILE_SIZE;
                const rh = r.bounds.height * TILE_SIZE;

                bctx.fillStyle = 'rgba(17, 24, 39, 0.90)';
                bctx.fillRect(rx, ry, rw, rh);

                // Room Grid
                bctx.strokeStyle = 'rgba(255, 255, 255, 0.025)';
                bctx.lineWidth = 1;
                for (let gx = rx; gx <= rx + rw; gx += TILE_SIZE) {
                    bctx.beginPath(); bctx.moveTo(gx, ry); bctx.lineTo(gx, ry + rh); bctx.stroke();
                }
                for (let gy = ry; gy <= ry + rh; gy += TILE_SIZE) {
                    bctx.beginPath(); bctx.moveTo(rx, gy); bctx.lineTo(rx + rw, gy); bctx.stroke();
                }

                bctx.strokeStyle = r.color || '#334155';
                bctx.lineWidth = 2;
                bctx.strokeRect(rx, ry, rw, rh);

                // Room Title Badge
                const badgeW = Math.min(rw - 16, 160);
                bctx.fillStyle = 'rgba(15, 23, 42, 0.95)';
                if (bctx.roundRect) bctx.roundRect(rx + 8, ry + 8, badgeW, 24, 6);
                else bctx.rect(rx + 8, ry + 8, badgeW, 24);
                bctx.fill();

                bctx.strokeStyle = 'rgba(255, 255, 255, 0.12)';
                bctx.lineWidth = 1;
                if (bctx.roundRect) bctx.roundRect(rx + 8, ry + 8, badgeW, 24, 6);
                else bctx.rect(rx + 8, ry + 8, badgeW, 24);
                bctx.stroke();

                bctx.fillStyle = '#f8fafc';
                bctx.font = 'bold 11px Cairo, Inter';
                bctx.fillText(`🏢 ${r.name}`, rx + 14, ry + 24);
            });

            editorBgDirty = false;
        }

        // ── Ultra-High-Fidelity 3D Canvas Rendering Loop ──
        function draw() {
            ctx.clearRect(0, 0, width, height);

            ctx.save();
            ctx.translate(panOffset.x, panOffset.y);
            ctx.scale(zoomLevel, zoomLevel);

            // 1. Draw Pre-rendered Background Buffer
            if (!editorBgCanvas || editorBgDirty) {
                renderEditorStaticBackground();
            }
            ctx.drawImage(editorBgCanvas, 0, 0);

            // 2. Selected Room Highlight
            if (selectedItem && selectedItem.type === 'room') {
                const r = selectedItem.item;
                const rx = r.bounds.x * TILE_SIZE;
                const ry = r.bounds.y * TILE_SIZE;
                const rw = r.bounds.width * TILE_SIZE;
                const rh = r.bounds.height * TILE_SIZE;
                ctx.strokeStyle = '#3b82f6';
                ctx.lineWidth = 3;
                ctx.strokeRect(rx, ry, rw, rh);
            }

            // 3. 3D Furniture Objects (with full Rotation & Sprites)
            objects.forEach((obj) => {
                const ox = (obj.position ? obj.position.x : 0) * TILE_SIZE;
                const oy = (obj.position ? obj.position.y : 0) * TILE_SIZE;
                const objW = (obj.width || (obj.size ? obj.size.width : 1)) * TILE_SIZE;
                const objH = (obj.height || (obj.size ? obj.size.height : 1)) * TILE_SIZE;
                const rot = (obj.position && typeof obj.position.rotation === 'number') ? obj.position.rotation : (obj.rotation || 0);

                ctx.save();
                ctx.translate(ox + objW / 2, oy + objH / 2);
                if (rot !== 0) ctx.rotate((rot * Math.PI) / 180);

                const imgUrl = obj.image_url || (obj.interaction_config && obj.interaction_config.image_url) || null;
                const spriteObj = CUSTOM_IMAGE_CACHE[obj.type] || (imgUrl ? { img: getLoadedImage(imgUrl) } : null);

                if (spriteObj && spriteObj.img && spriteObj.img.complete && spriteObj.img.naturalWidth > 0) {
                    ctx.drawImage(spriteObj.img, -objW / 2, -objH / 2, objW, objH);
                } else {
                    // Stylish Dark Fallback Block
                    ctx.fillStyle = '#1e293b';
                    if (ctx.roundRect) ctx.roundRect(-objW / 2 + 2, -objH / 2 + 2, objW - 4, objH - 4, 4);
                    else ctx.rect(-objW / 2 + 2, -objH / 2 + 2, objW - 4, objH - 4);
                    ctx.fill();
                    ctx.strokeStyle = 'rgba(255, 255, 255, 0.15)';
                    ctx.lineWidth = 1;
                    ctx.stroke();
                }

                ctx.restore();

                // Selection Box & Rotation Indicator
                const isSelected = selectedItem && selectedItem.type === 'object' && selectedItem.item === obj;
                if (isSelected) {
                    ctx.strokeStyle = '#3b82f6';
                    ctx.lineWidth = 2;
                    ctx.strokeRect(ox - 2, oy - 2, objW + 4, objH + 4);

                    // Glowing Corners
                    ctx.fillStyle = '#60a5fa';
                    ctx.fillRect(ox - 4, oy - 4, 6, 6);
                    ctx.fillRect(ox + objW - 2, oy - 4, 6, 6);
                    ctx.fillRect(ox - 4, oy + objH - 2, 6, 6);
                    ctx.fillRect(ox + objW - 2, oy + objH - 2, 6, 6);
                }
            });

            // 4. Drawing Drag Preview
            if (isDrawing && currentRect) {
                const dx = currentRect.x * TILE_SIZE;
                const dy = currentRect.y * TILE_SIZE;
                const dw = currentRect.width * TILE_SIZE;
                const dh = currentRect.height * TILE_SIZE;

                ctx.fillStyle = 'rgba(59, 130, 246, 0.2)';
                ctx.fillRect(dx, dy, dw, dh);
                ctx.strokeStyle = '#3b82f6';
                ctx.lineWidth = 2;
                ctx.strokeRect(dx, dy, dw, dh);
            }

            ctx.restore();
        }

        // ── API Actions (Save Draft & Publish) ──
        async function saveMapDraft() {
            try {
                // Save Rooms
                for (const r of rooms) {
                    const payload = {
                        map_id: MAP_DATA.id,
                        name: r.name,
                        type: r.type || 'meeting',
                        access_mode: r.access_mode || 'public',
                        capacity: r.capacity || 10,
                        color: r.color || '#6366F1',
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

                // Save Zones
                for (const z of (zones || [])) {
                    if (!z.id && z.shape_data) {
                        try {
                            const res = await fetch(`/api/v1/organizations/${ORG_ID}/zones`, {
                                method: 'POST',
                                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': CSRF_TOKEN },
                                credentials: 'same-origin',
                                body: JSON.stringify({
                                    map_id: MAP_DATA.id,
                                    name: z.name || 'Audio Zone',
                                    type: z.type || 'audio',
                                    shape_type: z.shape_type || 'rectangle',
                                    shape_data: z.shape_data,
                                    audible_radius: z.audible_radius || 200,
                                    metadata: z.metadata || null
                                })
                            });
                            const data = await res.json();
                            if (data.zone?.id) z.id = data.zone.id;
                        } catch (e) {
                            console.warn('Zone save error:', e);
                        }
                    }
                }

                // Format Objects with rotation & size
                const syncObjectsPayload = objects.map(o => ({
                    id: o.id || undefined,
                    type: o.type,
                    name: o.name || 'Office Object',
                    position: {
                        x: o.position ? o.position.x : 0,
                        y: o.position ? o.position.y : 0,
                        rotation: (o.position && typeof o.position.rotation === 'number') ? o.position.rotation : (o.rotation || 0)
                    },
                    size: {
                        width: o.width || (o.size ? o.size.width : 1),
                        height: o.height || (o.size ? o.size.height : 1)
                    },
                    collision: o.collision ?? true,
                    interaction_config: o.interaction_config || (o.image_url ? { image_url: o.image_url } : null)
                }));

                await fetch(`/api/v1/organizations/${ORG_ID}/maps/${MAP_DATA.id}/objects/sync`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': CSRF_TOKEN },
                    credentials: 'same-origin',
                    body: JSON.stringify({ objects: syncObjectsPayload })
                });

                showToast('✅ {{ __('Map Draft & Furniture Saved!') }}');
                return true;
            } catch (err) {
                console.error(err);
                showToast('❌ Error saving draft', '#ef4444');
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
                showToast(`🚀 {{ __('Map Published! Floor is live.') }}`);
            } catch (err) {
                console.error(err);
                showToast(`❌ Error: ${err.message || 'Could not publish map'}`, '#ef4444');
            }
        }

        function showToast(msg, bg) {
            const toast = document.getElementById('toast-msg');
            toast.textContent = msg;
            if (bg) toast.style.background = bg;
            else toast.style.background = 'var(--brand-green)';
            toast.style.display = 'block';
            setTimeout(() => { toast.style.display = 'none'; }, 3000);
        }

        draw();
    </script>
</body>
</html>
