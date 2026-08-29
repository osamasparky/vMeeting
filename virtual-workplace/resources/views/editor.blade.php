<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ __('Map Editor & Floor Designer') }} — {{ $map->name }}</title>

    <!-- Google Fonts: Cairo (Arabic) & Inter (English) -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;800;900&family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <style>
        :root {
            --brand-primary: #10B981;
            --brand-primary-hover: #059669;
            --brand-accent: #3B82F6;
            --brand-gold: #F59E0B;
            --brand-crimson: #EF4444;
            --brand-teal: #14B8A6;

            --bg-body: #09120E;
            --bg-header: rgba(13, 27, 20, 0.94);
            --bg-panel: rgba(18, 36, 27, 0.96);
            --bg-card: rgba(24, 48, 36, 0.85);
            --bg-input: rgba(11, 22, 16, 0.85);
            --border-panel: rgba(52, 211, 153, 0.18);
            --border-card: rgba(52, 211, 153, 0.12);

            --text-main: #F8FAFC;
            --text-muted: #94A3B8;
            --text-dim: #64748B;

            --shadow-elevated: 0 16px 36px rgba(0, 0, 0, 0.4);
            --shadow-panel: 0 8px 24px rgba(0, 0, 0, 0.35);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            user-select: none;
        }

        body {
            font-family: 'Cairo', 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            background: var(--bg-body);
            color: var(--text-main);
            height: 100vh;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            -webkit-font-smoothing: antialiased;
        }

        /* ── Header Navigation ── */
        .editor-header {
            height: 60px;
            background: var(--bg-header);
            backdrop-filter: blur(20px);
            border-bottom: 1px solid var(--border-panel);
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 18px;
            z-index: 100;
            box-shadow: 0 4px 20px rgba(0,0,0,0.3);
        }

        .header-left, .header-center, .header-right {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .brand-btn {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 8px 14px;
            background: var(--bg-input);
            border: 1px solid var(--border-panel);
            border-radius: 10px;
            color: var(--text-main);
            font-size: 12px;
            font-weight: 800;
            text-decoration: none;
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .brand-btn:hover {
            border-color: var(--brand-primary);
            color: var(--brand-primary);
            transform: translateY(-1px);
        }

        .map-meta {
            display: flex;
            flex-direction: column;
            gap: 2px;
        }
        .map-name {
            font-size: 13px;
            font-weight: 800;
            color: var(--text-main);
            display: flex;
            align-items: center;
            gap: 6px;
        }
        .map-version-badge {
            font-size: 10px;
            font-weight: 800;
            padding: 1px 8px;
            border-radius: 6px;
            background: rgba(16, 185, 129, 0.15);
            color: #6EE7B7;
            border: 1px solid rgba(16, 185, 129, 0.35);
            width: fit-content;
        }

        /* ── Tools Bar ── */
        .segmented-tool-pill {
            display: flex;
            align-items: center;
            background: rgba(11, 22, 16, 0.9);
            border: 1px solid var(--border-panel);
            border-radius: 12px;
            padding: 3px;
            gap: 2px;
        }

        .tool-btn {
            display: flex;
            align-items: center;
            gap: 6px;
            padding: 6px 12px;
            background: transparent;
            border: 1px solid transparent;
            border-radius: 9px;
            color: var(--text-muted);
            font-size: 12px;
            font-weight: 800;
            cursor: pointer;
            transition: all 0.18s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .tool-btn:hover {
            background: rgba(52, 211, 153, 0.1);
            color: var(--text-main);
        }
        .tool-btn.active {
            background: rgba(16, 185, 129, 0.22);
            border-color: rgba(52, 211, 153, 0.45);
            color: #6EE7B7;
            box-shadow: 0 2px 8px rgba(16, 185, 129, 0.2);
        }

        .tool-icon-btn {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 34px;
            height: 34px;
            background: var(--bg-input);
            border: 1px solid var(--border-panel);
            border-radius: 10px;
            color: var(--text-muted);
            font-size: 13px;
            cursor: pointer;
            transition: all 0.18s;
        }
        .tool-icon-btn:hover {
            background: rgba(52, 211, 153, 0.12);
            border-color: var(--brand-primary);
            color: var(--text-main);
            transform: scale(1.05);
        }
        .tool-icon-btn.danger:hover {
            background: rgba(239, 68, 68, 0.15);
            border-color: rgba(239, 68, 68, 0.5);
            color: #F87171;
        }

        /* ── Editor Dropdowns ── */
        .editor-dropdown-menu {
            position: absolute;
            top: calc(100% + 8px);
            inset-inline-start: 0;
            background: rgba(14, 28, 20, 0.98);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(52, 211, 153, 0.25);
            border-radius: 12px;
            box-shadow: 0 16px 36px rgba(0,0,0,0.6);
            padding: 6px;
            z-index: 10000;
            display: flex;
            flex-direction: column;
            gap: 2px;
            animation: fadeInDown 0.15s ease;
        }
        @keyframes fadeInDown {
            from { opacity: 0; transform: translateY(-4px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .editor-dropdown-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
            padding: 8px 12px;
            border-radius: 8px;
            text-decoration: none;
            background: transparent;
            border: none;
            color: var(--text-main);
            font-size: 12px;
            font-weight: 700;
            cursor: pointer;
            width: 100%;
            text-align: start;
            transition: background 0.15s ease;
        }
        .editor-dropdown-item:hover {
            background: rgba(52, 211, 153, 0.12);
            color: #6EE7B7;
        }
        .editor-dropdown-item.active {
            background: rgba(16, 185, 129, 0.2);
            color: #6EE7B7;
        }

        .act-btn {
            display: flex;
            align-items: center;
            gap: 6px;
            padding: 7px 12px;
            border-radius: 10px;
            font-size: 12px;
            font-weight: 800;
            cursor: pointer;
            border: none;
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
            text-decoration: none;
        }
        .act-btn-emerald {
            background: linear-gradient(135deg, #10B981, #059669);
            color: white;
            box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);
        }
        .act-btn-emerald:hover {
            box-shadow: 0 6px 16px rgba(16, 185, 129, 0.45);
            transform: translateY(-1px);
        }
        .act-btn-amber {
            background: rgba(245, 158, 11, 0.15);
            border: 1px solid rgba(245, 158, 11, 0.4);
            color: #FBBF24;
        }
        .act-btn-amber:hover {
            background: rgba(245, 158, 11, 0.25);
            transform: translateY(-1px);
        }
        .act-btn-crimson {
            background: rgba(239, 68, 68, 0.14);
            border: 1px solid rgba(239, 68, 68, 0.35);
            color: #F87171;
        }
        .act-btn-crimson:hover {
            background: rgba(239, 68, 68, 0.25);
            transform: translateY(-1px);
        }
        .act-btn-secondary {
            background: var(--bg-input);
            border: 1px solid var(--border-panel);
            color: var(--text-main);
        }
        .act-btn-secondary:hover {
            border-color: var(--brand-primary);
            color: var(--brand-primary);
        }

        /* ── Workspace Layout ── */
        .editor-workspace {
            flex: 1;
            display: flex;
            position: relative;
            overflow: hidden;
        }

        .canvas-viewport {
            flex: 1;
            height: calc(100vh - 60px);
            position: relative;
            background: radial-gradient(circle at center, #0F2319 0%, #08120D 100%);
            overflow: hidden;
            cursor: default;
        }
        #editor-canvas {
            display: block;
            width: 100%;
            height: 100%;
        }

        /* Floating View Nav Overlay */
        .viewport-controls {
            position: absolute;
            bottom: 20px;
            inset-inline-start: 20px;
            display: flex;
            align-items: center;
            gap: 6px;
            background: var(--bg-header);
            backdrop-filter: blur(14px);
            border: 1px solid var(--border-panel);
            padding: 6px 10px;
            border-radius: 14px;
            box-shadow: var(--shadow-panel);
            z-index: 10;
        }
        .view-btn {
            background: var(--bg-input);
            border: 1px solid var(--border-card);
            color: var(--text-main);
            width: 32px;
            height: 32px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            font-size: 13px;
            font-weight: 800;
            transition: all 0.15s;
        }
        .view-btn:hover {
            border-color: var(--brand-primary);
            color: var(--brand-primary);
            transform: scale(1.05);
        }

        /* Floating Action Bar on Selected Item */
        .floating-item-actions {
            position: absolute;
            transform: translate(-50%, -100%);
            margin-top: -12px;
            background: rgba(13, 27, 20, 0.95);
            backdrop-filter: blur(16px);
            border: 1px solid var(--brand-primary);
            border-radius: 10px;
            padding: 4px 8px;
            display: none;
            align-items: center;
            gap: 6px;
            z-index: 50;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.45);
        }
        .float-act-btn {
            background: var(--bg-input);
            border: 1px solid var(--border-card);
            color: var(--text-main);
            padding: 4px 8px;
            border-radius: 6px;
            font-size: 11px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.15s;
        }
        .float-act-btn:hover {
            background: rgba(52, 211, 153, 0.15);
            border-color: var(--brand-primary);
            color: #6EE7B7;
        }

        /* ── Right Customizer Drawer ── */
        .customizer-drawer {
            width: 370px;
            height: calc(100vh - 60px);
            background: var(--bg-panel);
            backdrop-filter: blur(24px);
            border-inline-start: 1px solid var(--border-panel);
            display: flex;
            flex-direction: column;
            z-index: 20;
            transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: var(--shadow-panel);
        }
        .customizer-drawer.collapsed {
            transform: translateX(100%);
            margin-inline-end: -370px;
        }
        [dir="rtl"] .customizer-drawer.collapsed {
            transform: translateX(-100%);
            margin-inline-end: -370px;
        }

        .drawer-header {
            padding: 16px 20px;
            border-bottom: 1px solid var(--border-panel);
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .drawer-title {
            font-size: 14px;
            font-weight: 900;
            color: var(--text-main);
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .drawer-tabs {
            display: flex;
            background: var(--bg-input);
            padding: 4px;
            margin: 12px 16px;
            border-radius: 10px;
            gap: 4px;
            border: 1px solid var(--border-card);
        }
        .drawer-tab {
            flex: 1;
            text-align: center;
            padding: 8px 4px;
            border-radius: 8px;
            font-size: 11px;
            font-weight: 800;
            color: var(--text-muted);
            cursor: pointer;
            transition: all 0.18s;
        }
        .drawer-tab:hover {
            color: var(--text-main);
        }
        .drawer-tab.active {
            background: var(--brand-primary);
            color: white;
            box-shadow: 0 2px 8px rgba(16, 185, 129, 0.3);
        }

        .drawer-body {
            flex: 1;
            overflow-y: auto;
            padding: 0 16px 20px 16px;
            display: flex;
            flex-direction: column;
            gap: 12px;
        }

        /* ── Search & Filter Bars ── */
        .search-box {
            width: 100%;
            background: var(--bg-input);
            border: 1px solid var(--border-panel);
            border-radius: 10px;
            padding: 10px 12px;
            color: var(--text-main);
            font-size: 12px;
            font-weight: 600;
            outline: none;
            transition: border-color 0.2s;
        }
        .search-box:focus {
            border-color: var(--brand-primary);
        }

        .category-filter-bar {
            display: flex;
            gap: 6px;
            overflow-x: auto;
            padding-bottom: 4px;
        }
        .cat-pill {
            padding: 5px 10px;
            background: var(--bg-input);
            border: 1px solid var(--border-card);
            border-radius: 8px;
            color: var(--text-muted);
            font-size: 11px;
            font-weight: 700;
            white-space: nowrap;
            cursor: pointer;
            transition: all 0.15s;
        }
        .cat-pill:hover, .cat-pill.active {
            background: rgba(16, 185, 129, 0.18);
            border-color: var(--brand-primary);
            color: #6EE7B7;
        }

        /* ── Furniture Cards ── */
        .category-group {
            background: var(--bg-input);
            border: 1px solid var(--border-card);
            border-radius: 12px;
            overflow: hidden;
        }
        .category-title-bar {
            padding: 10px 14px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            font-size: 12px;
            font-weight: 800;
            color: var(--text-main);
            cursor: pointer;
            background: rgba(255, 255, 255, 0.02);
        }
        .furniture-grid {
            padding: 10px;
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 8px;
        }
        .furn-card {
            background: var(--bg-card);
            border: 1px solid var(--border-card);
            border-radius: 10px;
            padding: 10px;
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
            cursor: pointer;
            gap: 6px;
            transition: all 0.18s;
        }
        .furn-card:hover {
            border-color: var(--brand-primary);
            background: rgba(16, 185, 129, 0.1);
            transform: translateY(-2px);
        }
        .furn-card.active {
            border-color: var(--brand-primary);
            box-shadow: 0 0 12px rgba(16, 185, 129, 0.3);
        }
        .furn-icon {
            width: 44px;
            height: 44px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 8px;
            background: rgba(0, 0, 0, 0.25);
            overflow: hidden;
        }
        .furn-icon img {
            max-width: 100%;
            max-height: 100%;
            object-fit: contain;
        }
        .furn-label {
            font-size: 11px;
            font-weight: 800;
            color: var(--text-main);
            line-height: 1.3;
        }

        /* ── Inspector Controls ── */
        .prop-section {
            background: var(--bg-input);
            border: 1px solid var(--border-card);
            border-radius: 12px;
            padding: 14px;
            display: flex;
            flex-direction: column;
            gap: 10px;
        }
        .prop-label {
            font-size: 11px;
            font-weight: 800;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: 0.04em;
        }
        .prop-input {
            width: 100%;
            background: rgba(0, 0, 0, 0.35);
            border: 1px solid var(--border-panel);
            border-radius: 8px;
            padding: 8px 12px;
            color: var(--text-main);
            font-size: 12px;
            font-weight: 700;
            outline: none;
            transition: border-color 0.2s;
        }
        .prop-input:focus {
            border-color: var(--brand-primary);
        }

        .rotation-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 6px;
        }
        .rot-btn {
            padding: 6px 0;
            text-align: center;
            background: var(--bg-card);
            border: 1px solid var(--border-card);
            border-radius: 6px;
            color: var(--text-main);
            font-size: 11px;
            font-weight: 800;
            cursor: pointer;
            transition: all 0.15s;
        }
        .rot-btn:hover, .rot-btn.active {
            background: var(--brand-primary);
            color: white;
        }

        /* ── Toast Notifications ── */
        .toast-bubble {
            position: fixed;
            bottom: 24px;
            inset-inline-start: 24px;
            background: #10B981;
            color: white;
            padding: 12px 20px;
            border-radius: 12px;
            font-size: 13px;
            font-weight: 800;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.4);
            display: none;
            z-index: 1000;
            animation: popToast 0.3s ease;
        }
        @keyframes popToast {
            from { transform: translateY(20px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }
    </style>
</head>
<body>

    <!-- ── Header Navigation ── -->
    <header class="editor-header">
        <!-- Left: Navigation & Branch Selector -->
        <div class="header-left">
            <a href="{{ route('dashboard') }}" class="brand-btn" title="{{ __('Back to Dashboard') }}">
                <span>📊</span>
                <span>{{ __('Dashboard') }}</span>
            </a>

            <div style="display: flex; align-items: center; gap: 6px; padding: 0 4px;">
                <strong style="font-size: 13px; font-weight: 900; color: #FFFFFF; letter-spacing: -0.01em;">{{ __('Map Editor') }}</strong>
                <span style="font-size: 11px; font-weight: 700; color: var(--brand-primary); background: rgba(16, 185, 129, 0.12); padding: 2px 8px; border-radius: 6px; border: 1px solid rgba(52, 211, 153, 0.25);">{{ $organization->name }}</span>
            </div>

            <!-- Office / Branch Switcher Dropdown -->
            <div style="position: relative; display: inline-block;">
                <button type="button" onclick="toggleBranchDropdown(event)" class="brand-btn" style="background: rgba(16, 185, 129, 0.12); border-color: rgba(52, 211, 153, 0.35); color: #6EE7B7; display: flex; align-items: center; gap: 8px;" title="{{ __('Select Office Branch to Edit') }}">
                    <span>🏢</span>
                    <span style="max-width: 140px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">{{ $floor->name }}</span>
                    <span style="font-size: 8px; opacity: 0.7;">▼</span>
                </button>

                <div id="branch-select-dropdown" class="editor-dropdown-menu" style="display: none; min-width: 230px;">
                    <div style="font-size: 10px; font-weight: 900; text-transform: uppercase; letter-spacing: 0.5px; color: var(--text-muted); padding: 8px 12px; border-bottom: 1px solid var(--border-card);">
                        🏢 {{ __('Select Office Branch (اختر الفرع للتعديل)') }}
                    </div>
                    @foreach($floors as $f)
                    <a href="{{ route('editor', ['office' => $f->id]) }}" class="editor-dropdown-item {{ $f->id === $floor->id ? 'active' : '' }}">
                        <div style="display: flex; flex-direction: column; gap: 2px;">
                            <strong style="font-size: 12px; color: {{ $f->id === $floor->id ? '#6EE7B7' : 'var(--text-main)' }};">
                                {{ $f->name }}
                            </strong>
                            <span style="font-size: 10px; color: var(--text-dim);">
                                📍 {{ $f->city_location ?: __('Primary Location') }}
                            </span>
                        </div>
                        @if($f->id === $floor->id)
                            <span style="font-size: 10px; color: #6EE7B7; font-weight: 800;">● {{ __('Editing') }}</span>
                        @endif
                    </a>
                    @endforeach
                </div>
            </div>

            <span class="map-version-badge" id="header-version-badge" title="Map version and publishing status">
                v{{ $map->version }} • {{ ucfirst($map->status) }}
            </span>
        </div>

        <!-- Center: Tool Selector Segmented Pill -->
        <div class="header-center">
            <div class="segmented-tool-pill">
                <button class="tool-btn active" id="tool-select" onclick="setTool('select')" title="{{ __('Select & Move Objects (V)') }}">
                    <span>🖱️</span> <span>{{ __('Select') }}</span>
                </button>
                <button class="tool-btn" id="tool-room" onclick="setTool('room')" title="{{ __('Draw Meeting / Private Rooms (R)') }}">
                    <span>🚪</span> <span>{{ __('Room') }}</span>
                </button>
                <button class="tool-btn" id="tool-object" onclick="setTool('object')" title="{{ __('Place Furniture & Decor (F)') }}">
                    <span>🪑</span> <span>{{ __('Furniture') }}</span>
                </button>
            </div>

            <div style="display: flex; gap: 4px; align-items: center;">
                <button class="tool-icon-btn" onclick="rotateSelectedItem(90)" title="{{ __('Rotate 90° (R)') }}">
                    <span>🔄</span>
                </button>
                <button class="tool-icon-btn danger" onclick="deleteSelectedItem()" title="{{ __('Delete Selected (Del)') }}">
                    <span>🗑️</span>
                </button>
            </div>
        </div>

        <!-- Right: Actions Group -->
        <div class="header-right">
            <!-- ✨ AI Office & Blueprint Generator Button -->
            <button type="button" onclick="openAiGeneratorModal()" class="act-btn" style="background: linear-gradient(135deg, #10B981, #059669); color: white; border: 1px solid rgba(52, 211, 153, 0.4); display: flex; align-items: center; gap: 6px; font-weight: 800; box-shadow: 0 4px 14px rgba(16, 185, 129, 0.35);" title="{{ __('Generate 3D Isometric Office Floorplan & Rooms with AI') }}">
                <span>✨</span>
                <span>{{ __('AI Office Generator (توليد بالذكاء الاصطناعي)') }}</span>
            </button>

            <input type="file" id="floorplan-file-input" accept="image/jpeg,image/png,image/webp,image/jpg" style="display:none;" onchange="handleFloorplanUpload(this)">

            <!-- Floorplan Dropdown Menu -->
            <div style="position: relative; display: inline-block;">
                <button type="button" onclick="toggleFloorplanDropdown(event)" class="act-btn act-btn-secondary" style="display: flex; align-items: center; gap: 6px;" title="{{ __('Floorplan Background & Clear Options') }}">
                    <span>🖼️</span>
                    <span>{{ __('Floorplan') }}</span>
                    <span style="font-size: 8px; opacity: 0.7;">▼</span>
                </button>

                <div id="floorplan-actions-dropdown" class="editor-dropdown-menu" style="display: none; min-width: 220px; inset-inline-end: 0; inset-inline-start: auto;">
                    <button type="button" onclick="triggerFloorplanUpload(); closeDropdowns();" class="editor-dropdown-item">
                        <span>⬆️</span>
                        <span>{{ __('Upload Custom Floorplan') }}</span>
                    </button>
                    <button type="button" onclick="deleteFloorplan(); closeDropdowns();" class="editor-dropdown-item" style="color: #F87171;">
                        <span>🔄</span>
                        <span>{{ __('Reset to Default Floorplan') }}</span>
                    </button>
                    <div style="height: 1px; background: var(--border-card); margin: 4px 0;"></div>
                    <button type="button" onclick="clearWorkspace(); closeDropdowns();" class="editor-dropdown-item" style="color: #FBBF24;">
                        <span>🧹</span>
                        <span>{{ __('Clear All Furniture (تفريغ)') }}</span>
                    </button>
                </div>
            </div>

            <button class="act-btn act-btn-secondary" onclick="saveMapDraft()" title="{{ __('Save Map Draft') }}">
                <span>💾</span> <span>{{ __('Save') }}</span>
            </button>

            <button class="act-btn act-btn-emerald" onclick="publishMap()" title="{{ __('Publish Map to Live Office') }}">
                <span>🚀</span> <span>{{ __('Publish') }}</span>
            </button>

            <a href="{{ route('office', ['office' => $floor->id]) }}" class="act-btn act-btn-secondary" style="background: rgba(37, 99, 235, 0.15); border-color: rgba(59, 130, 246, 0.35); color: #93C5FD;" title="{{ __('Enter Live Office Branch') }}">
                <span>👁️</span> <span>{{ __('Live View') }}</span>
            </a>

            @if(app()->getLocale() === 'ar')
                <a href="{{ route('lang.switch', 'en') }}" class="brand-btn" style="padding: 6px 10px; font-size: 11px;" title="Switch to English">EN</a>
            @else
                <a href="{{ route('lang.switch', 'ar') }}" class="brand-btn" style="padding: 6px 10px; font-size: 11px;" title="التبديل إلى العربية">عربي</a>
            @endif
        </div>
    </header>

    <!-- ── Main Workspace ── -->
    <div class="editor-workspace">
        
        <!-- Canvas Viewport -->
        <div class="canvas-viewport" id="canvas-container">
            <canvas id="editor-canvas"></canvas>

            <!-- Floating Selected Object Actions -->
            <div class="floating-item-actions" id="floating-actions">
                <button class="float-act-btn" onclick="rotateSelectedItem(90)">🔄 +90°</button>
                <button class="float-act-btn" onclick="duplicateSelectedItem()">📋 {{ __('Clone') }}</button>
                <button class="float-act-btn" onclick="deleteSelectedItem()" style="color: var(--brand-crimson);">🗑️</button>
            </div>

            <!-- View Navigation Controls -->
            <div class="viewport-controls">
                <button class="view-btn" onclick="toggleCustomizerDrawer()" title="{{ __('Toggle Catalog Drawer') }}">🪑</button>
                <button class="view-btn" onclick="zoomIn()" title="{{ __('Zoom In') }}">➕</button>
                <button class="view-btn" onclick="zoomOut()" title="{{ __('Zoom Out') }}">➖</button>
                <button class="view-btn" onclick="resetView()" title="{{ __('Reset View (100%)') }}">🏠</button>
                <button class="view-btn" onclick="toggleGrid()" title="{{ __('Toggle Grid') }}">🔲</button>
            </div>
        </div>

        <!-- Right Customizer Drawer -->
        <aside class="customizer-drawer" id="customizer-drawer">
            <div class="drawer-header">
                <div class="drawer-title">
                    <span>✨</span>
                    <span>{{ __('Customize Floor & Furniture') }}</span>
                </div>
                <button onclick="toggleCustomizerDrawer()" style="background:none; border:none; color:var(--text-muted); font-size:18px; cursor:pointer;">✕</button>
            </div>

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

            <div class="drawer-body">
                
                <!-- 1. FURNITURE CATALOG TAB -->
                <div id="drawer-view-furniture" style="display: flex; flex-direction: column; gap: 10px;">
                    <input type="text" class="search-box" placeholder="🔍 {{ __('Search 3D furniture, desks, chairs...') }}" oninput="filterFurniture(this.value)">

                    <div class="category-filter-bar">
                        <button class="cat-pill active" onclick="filterByCategory('all')">🌟 {{ __('All') }}</button>
                        <button class="cat-pill" onclick="filterByCategory('blueprint')">📐 {{ __('Blueprint Assets') }}</button>
                        @foreach($furnitureCategories as $cat)
                            <button class="cat-pill" onclick="filterByCategory('{{ $cat->slug }}')">{{ $cat->icon }} {{ $cat->name }}</button>
                        @endforeach
                    </div>

                    <!-- Blueprint Suite Assets -->
                    <div class="category-group" id="cat-blueprint">
                        <div class="category-title-bar" onclick="toggleCategoryGroup('cat-blueprint')">
                            <span>📐 {{ __('Isometric Blueprint Objects') }} (18)</span>
                            <span>▾</span>
                        </div>
                        <div class="furniture-grid">
                            <div class="furn-card" onclick="selectFurnitureItem('living_wall', '#2D6A4F', null, 5, 2, true)">
                                <div class="furn-icon"><span style="font-size: 24px;">🌿</span></div>
                                <div class="furn-label">{{ __('Living Plant Wall') }} (5x2)</div>
                            </div>
                            <div class="furn-card" onclick="selectFurnitureItem('conference_table', '#D8B589', null, 8, 3, true)">
                                <div class="furn-icon"><span style="font-size: 24px;">🤝</span></div>
                                <div class="furn-label">{{ __('Oak Boardroom Table') }} (8x3)</div>
                            </div>
                            <div class="furn-card" onclick="selectFurnitureItem('chair_white', '#FFFFFF', null, 1, 1, false)">
                                <div class="furn-icon"><span style="font-size: 24px;">🪑</span></div>
                                <div class="furn-label">{{ __('White Executive Chair') }}</div>
                            </div>
                            <div class="furn-card" onclick="selectFurnitureItem('pod_workstation', '#D8B589', null, 3, 2, true)">
                                <div class="furn-icon"><span style="font-size: 24px;">🎧</span></div>
                                <div class="furn-label">{{ __('Focus Pod Desk') }} (3x2)</div>
                            </div>
                            <div class="furn-card" onclick="selectFurnitureItem('wood_panel_wall', '#C49A6C', null, 7, 1, true)">
                                <div class="furn-icon"><span style="font-size: 24px;">🪵</span></div>
                                <div class="furn-label">{{ __('Wood Feature Wall') }} (7x1)</div>
                            </div>
                            <div class="furn-card" onclick="selectFurnitureItem('stairs_wood', '#C49A6C', null, 3, 4, true)">
                                <div class="furn-icon"><span style="font-size: 24px;">🪜</span></div>
                                <div class="furn-label">{{ __('Wooden Staircase') }} (3x4)</div>
                            </div>
                            <div class="furn-card" onclick="selectFurnitureItem('tech_workbench', '#D8B589', null, 4, 2, true)">
                                <div class="furn-icon"><span style="font-size: 24px;">🛠️</span></div>
                                <div class="furn-label">{{ __('Tech 3D Workbench') }} (4x2)</div>
                            </div>
                            <div class="furn-card" onclick="selectFurnitureItem('reception_counter', '#F4EFE6', null, 4, 2, true)">
                                <div class="furn-icon"><span style="font-size: 24px;">🛎️</span></div>
                                <div class="furn-label">{{ __('Reception Desk') }} (4x2)</div>
                            </div>
                            <div class="furn-card" onclick="selectFurnitureItem('sofa_cream', '#F4EFE6', null, 3, 2, true)">
                                <div class="furn-icon"><span style="font-size: 24px;">🛋️</span></div>
                                <div class="furn-label">{{ __('Cream 3-Seater Sofa') }} (3x2)</div>
                            </div>
                            <div class="furn-card" onclick="selectFurnitureItem('armchair_sage', '#8BA888', null, 2, 2, true)">
                                <div class="furn-icon"><span style="font-size: 24px;">🛋️</span></div>
                                <div class="furn-label">{{ __('Sage Armchair') }} (2x2)</div>
                            </div>
                            <div class="furn-card" onclick="selectFurnitureItem('coffee_table_oak', '#D8B589', null, 2, 1, true)">
                                <div class="furn-icon"><span style="font-size: 24px;">☕</span></div>
                                <div class="furn-label">{{ __('Oak Coffee Table') }} (2x1)</div>
                            </div>
                            <div class="furn-card" onclick="selectFurnitureItem('whiteboard_strategy', '#FFFFFF', null, 4, 1, true)">
                                <div class="furn-icon"><span style="font-size: 24px;">📋</span></div>
                                <div class="furn-label">{{ __('Strategy Board') }} (4x1)</div>
                            </div>
                        </div>
                    </div>

                    <!-- Dynamic Catalog Categories -->
                    @foreach($furnitureCategories as $cat)
                    <div class="category-group" id="cat-{{ $cat->slug }}">
                        <div class="category-title-bar" onclick="toggleCategoryGroup('cat-{{ $cat->slug }}')">
                            <span>{{ $cat->icon }} {{ $cat->name }} ({{ $cat->items->count() }})</span>
                            <span>▾</span>
                        </div>
                        <div class="furniture-grid" style="display: none;">
                            @foreach($cat->items as $item)
                                <div class="furn-card" onclick="selectFurnitureItem('{{ $item->slug }}', '{{ $item->colors[0] ?? '#3b82f6' }}', '{{ $item->image_url }}', {{ $item->width }}, {{ $item->height }}, {{ $item->collision ? 'true' : 'false' }})">
                                    <div class="furn-icon">
                                        @if($item->image_url)
                                            <img src="{{ $item->image_url }}" alt="{{ $item->name }}" loading="lazy">
                                        @else
                                            <span style="font-size: 24px;">{{ $item->icon }}</span>
                                        @endif
                                    </div>
                                    <div class="furn-label">{{ $item->name }}</div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                    @endforeach
                </div>

                <!-- 2. SELECTED ITEM INSPECTOR TAB -->
                <div id="drawer-view-inspector" style="display: none; flex-direction: column; gap: 12px;">
                    <div class="prop-section" id="inspector-empty-msg">
                        <div style="font-size: 12px; color: var(--text-muted); text-align: center; padding: 24px 0;">
                            👆 {{ __('Click any object or room on the map to edit its properties, rotation, boundaries, and acoustic settings.') }}
                        </div>
                    </div>

                    <div id="inspector-content" style="display: none; flex-direction: column; gap: 12px;">
                        
                        <!-- Object Fields -->
                        <div id="inspector-object-fields" class="prop-section" style="display: none;">
                            <strong style="font-size: 13px; color: var(--text-main);">🪑 {{ __('Object Properties') }}</strong>
                            <div>
                                <label class="prop-label">{{ __('Name') }}</label>
                                <input type="text" class="prop-input" id="prop-name" oninput="updateSelectedProp('name', this.value)">
                            </div>
                            <div>
                                <label class="prop-label">{{ __('Rotation') }}</label>
                                <div class="rotation-grid">
                                    <div class="rot-btn" onclick="setRotation(0)">0°</div>
                                    <div class="rot-btn" onclick="setRotation(90)">90°</div>
                                    <div class="rot-btn" onclick="setRotation(180)">180°</div>
                                    <div class="rot-btn" onclick="setRotation(270)">270°</div>
                                </div>
                            </div>
                            <div>
                                <label class="prop-label">{{ __('Dimensions (Width × Height Tiles)') }}</label>
                                <div style="display: flex; gap: 8px;">
                                    <input type="number" class="prop-input" id="prop-width" placeholder="W" min="1" max="20" oninput="updateSelectedProp('width', parseInt(this.value) || 1)">
                                    <input type="number" class="prop-input" id="prop-height" placeholder="H" min="1" max="20" oninput="updateSelectedProp('height', parseInt(this.value) || 1)">
                                </div>
                            </div>
                        </div>

                        <!-- Room Fields -->
                        <div id="inspector-room-fields" class="prop-section" style="display: none;">
                            <strong style="font-size: 13px; color: var(--text-main);">🏢 {{ __('Room Properties & Audio') }}</strong>
                            <div>
                                <label class="prop-label">{{ __('Room Name (اسم الغرفة)') }}</label>
                                <input type="text" class="prop-input" id="prop-room-name" placeholder="{{ __('e.g. Conference Room A') }}" oninput="updateRoomProp('name', this.value)">
                            </div>
                            <div>
                                <label class="prop-label">{{ __('Room Type (نوع الغرفة)') }}</label>
                                <select class="prop-input" id="prop-room-type" onchange="updateRoomProp('type', this.value)">
                                    <option value="meeting">👥 {{ __('Meeting Room (قاعة اجتماعات)') }}</option>
                                    <option value="private">🔒 {{ __('Private Office (مكتب خاص)') }}</option>
                                    <option value="focus">🎯 {{ __('Focus Pod (كابينة تركيز)') }}</option>
                                    <option value="breakout">☕ {{ __('Breakout Lounge (استراحة)') }}</option>
                                    <option value="reception">🛎️ {{ __('Reception Lobby (استقبال)') }}</option>
                                </select>
                            </div>
                            
                            <!-- Acoustic Isolation Box -->
                            <div style="background: rgba(16, 185, 129, 0.12); border: 1px solid rgba(16, 185, 129, 0.3); border-radius: 10px; padding: 12px; display: flex; flex-direction: column; gap: 8px;">
                                <div style="display: flex; align-items: center; justify-content: space-between;">
                                    <span style="font-size: 12px; font-weight: 800; color: #34D399;">🎙️ {{ __('Acoustic Isolation (العزل الصوتي)') }}</span>
                                    <input type="checkbox" id="prop-room-isolation" onchange="updateRoomProp('audio_isolation', this.checked)" style="width: 18px; height: 18px; accent-color: var(--brand-primary); cursor: pointer;">
                                </div>
                                <span style="font-size: 11px; color: var(--text-muted);" id="prop-room-bounds-label"></span>
                            </div>

                            <div>
                                <label class="prop-label">{{ __('Capacity (السعة)') }}</label>
                                <input type="number" class="prop-input" id="prop-room-capacity" min="1" max="200" oninput="updateRoomProp('capacity', this.value)">
                            </div>

                            <button class="act-btn act-btn-emerald" onclick="saveSelectedRoom()" style="margin-top: 6px; justify-content: center;">
                                💾 {{ __('Save Room Settings (حفظ التعديلات)') }}
                            </button>
                        </div>

                    </div>
                </div>

                <!-- 3. ROOMS DIRECTORY TAB -->
                <div id="drawer-view-rooms" style="display: none; flex-direction: column; gap: 10px;">
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <span style="font-size: 12px; font-weight: 800; color: var(--text-muted);">{{ __('All Configured Rooms') }}</span>
                        <button class="tool-btn" onclick="setTool('room')">➕ {{ __('New Room') }}</button>
                    </div>
                    <div id="rooms-list-container" style="display: flex; flex-direction: column; gap: 8px;"></div>
                </div>

            </div>
        </aside>
    </div>

    <!-- Toast Notification -->
    <div id="toast-bubble" class="toast-bubble"></div>

    <!-- ── JavaScript Realtime Engine & Editor Pipeline ── -->
    <script nonce="{{ $cspNonce ?? '' }}">
        const MAP_DATA = @json($map);
        const MAP_ID = "{{ $map->id }}";
        const ORG_ID = "{{ $organization->id }}";
        const PLAN_MAX_ROOMS = {{ ($organization->plan && $organization->plan->room_limit > 0) ? $organization->plan->room_limit : 0 }};
        const CSRF_TOKEN = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        const canvas = document.getElementById('editor-canvas');
        const ctx = canvas.getContext('2d');
        const container = document.getElementById('canvas-container');

        let width = canvas.width = container.clientWidth;
        let height = canvas.height = container.clientHeight;

        const TILE_SIZE = 16;
        const MAP_WIDTH_PX = 1024;
        const MAP_HEIGHT_PX = 909;

        let zoomLevel = 1.0;
        let panOffset = { x: 50, y: 40 };
        let showGrid = true;

        let currentTool = 'select'; // select | room | object
        let currentRoomType = 'meeting';
        let currentRoomColor = '#4F9B5F';
        let currentObjectType = 'living_wall';
        let currentObjectColor = '#2D6A4F';
        let currentObjectCustom = null;

        let rooms = (MAP_DATA.rooms || []).map(r => ({
            id: r.id,
            name: r.name || 'Room',
            type: r.type || 'meeting',
            access_mode: r.access_mode || 'public',
            capacity: r.capacity || 10,
            color: r.color || '#4F9B5F',
            bounds: r.bounds || { x: 1, y: 1, width: 10, height: 8 },
            metadata: r.metadata || { audio_isolation: true }
        }));
        let objects = MAP_DATA.objects || [];

        let selectedItem = null;
        let isDragging = false;
        let isDrawing = false;
        let isPanning = false;
        let panStartX = 0;
        let panStartY = 0;
        let dragStartTileX = 0;
        let dragStartTileY = 0;
        let dragOrigX = 0;
        let dragOrigY = 0;
        let startX = 0;
        let startY = 0;
        let currentRect = null;
        let roomContainedObjects = [];

        // ── Resize Engine ──
        function resizeCanvas() {
            width = canvas.width = container.clientWidth;
            height = canvas.height = container.clientHeight;
            draw();
        }
        window.addEventListener('resize', resizeCanvas);

        // ── Background Blueprint Artwork ──
        const BLUEPRINT_IMAGE = new Image();
        const initialBgUrl = (MAP_DATA.layout_data && MAP_DATA.layout_data.background_image_url)
            ? MAP_DATA.layout_data.background_image_url
            : '/images/office_floorplan.jpg';
        BLUEPRINT_IMAGE.src = initialBgUrl + (initialBgUrl.includes('?') ? '&' : '?') + 'v=' + Date.now();
        let blueprintLoaded = false;
        BLUEPRINT_IMAGE.onload = () => {
            blueprintLoaded = true;
            draw();
        };

        // ── Navigation & Tools ──
        function setTool(tool) {
            currentTool = tool;
            document.querySelectorAll('.tool-btn').forEach(btn => btn.classList.remove('active'));
            document.getElementById(`tool-${tool}`)?.classList.add('active');
            canvas.style.cursor = tool === 'select' ? 'default' : 'crosshair';
            if (tool !== 'select') hideFloatingActions();
        }

        function toggleCustomizerDrawer() {
            const drawer = document.getElementById('customizer-drawer');
            drawer.classList.toggle('collapsed');
            setTimeout(resizeCanvas, 320);
        }

        function switchDrawerTab(tab) {
            document.querySelectorAll('.drawer-tab').forEach(el => el.classList.remove('active'));
            document.getElementById(`tab-btn-${tab}`)?.classList.add('active');
            document.getElementById('drawer-view-furniture').style.display = tab === 'furniture' ? 'flex' : 'none';
            document.getElementById('drawer-view-inspector').style.display = tab === 'inspector' ? 'flex' : 'none';
            document.getElementById('drawer-view-rooms').style.display = tab === 'rooms' ? 'flex' : 'none';
            if (tab === 'rooms') renderRoomsDirectory();
        }

        function toggleCategoryGroup(id) {
            const grid = document.querySelector(`#${id} .furniture-grid`);
            if (grid) grid.style.display = grid.style.display === 'none' ? 'grid' : 'none';
        }

        function filterByCategory(slug) {
            document.querySelectorAll('.cat-pill').forEach(c => c.classList.remove('active'));
            if (event && event.currentTarget) event.currentTarget.classList.add('active');
            document.querySelectorAll('.category-group').forEach(acc => {
                acc.style.display = (slug === 'all' || acc.id === `cat-${slug}`) ? 'block' : 'none';
                const grid = acc.querySelector('.furniture-grid');
                if (grid && (slug === 'all' || acc.id === `cat-${slug}`)) grid.style.display = 'grid';
            });
        }

        function filterFurniture(q) {
            const term = q.toLowerCase();
            document.querySelectorAll('.furn-card').forEach(card => {
                const name = card.querySelector('.furn-label')?.textContent.toLowerCase() || '';
                card.style.display = name.includes(term) ? 'flex' : 'none';
            });
        }

        function selectFurnitureItem(slug, color, imgUrl = null, w = 1, h = 1, col = true) {
            setTool('object');
            currentObjectType = slug;
            currentObjectColor = color || '#3B82F6';
            currentObjectCustom = { imageUrl: imgUrl, width: w, height: h, collision: Boolean(col) };
            document.querySelectorAll('.furn-card').forEach(el => el.classList.remove('active'));
            if (event && event.currentTarget) event.currentTarget.classList.add('active');
        }

        function zoomIn() { zoomLevel = Math.min(2.5, zoomLevel + 0.15); draw(); }
        function zoomOut() { zoomLevel = Math.max(0.4, zoomLevel - 0.15); draw(); }
        function resetView() { zoomLevel = 1.0; panOffset = { x: 50, y: 40 }; draw(); }
        function toggleGrid() { showGrid = !showGrid; draw(); }

        // ── Canvas Interaction Handlers ──
        canvas.addEventListener('contextmenu', (e) => e.preventDefault());

        canvas.addEventListener('wheel', (e) => {
            e.preventDefault();
            const zoomDelta = e.deltaY < 0 ? 0.12 : -0.12;
            const newZoom = Math.max(0.35, Math.min(3.0, zoomLevel + zoomDelta));
            if (newZoom !== zoomLevel) {
                const rect = canvas.getBoundingClientRect();
                const mouseX = e.clientX - rect.left;
                const mouseY = e.clientY - rect.top;
                panOffset.x -= (mouseX - panOffset.x) * (newZoom / zoomLevel - 1);
                panOffset.y -= (mouseY - panOffset.y) * (newZoom / zoomLevel - 1);
                zoomLevel = newZoom;
                draw();
            }
        }, { passive: false });

        canvas.addEventListener('mousedown', (e) => {
            if (e.button === 1 || e.button === 2 || (e.button === 0 && e.altKey)) {
                isPanning = true;
                panStartX = e.clientX - panOffset.x;
                panStartY = e.clientY - panOffset.y;
                canvas.style.cursor = 'grab';
                return;
            }

            const rect = canvas.getBoundingClientRect();
            const mouseX = (e.clientX - rect.left - panOffset.x) / zoomLevel;
            const mouseY = (e.clientY - rect.top - panOffset.y) / zoomLevel;

            const tileX = Math.floor(mouseX / TILE_SIZE);
            const tileY = Math.floor(mouseY / TILE_SIZE);

            if (currentTool === 'select') {
                let clicked = null;
                // Objects first
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
                // Rooms second
                if (!clicked) {
                    for (let i = rooms.length - 1; i >= 0; i--) {
                        const r = rooms[i];
                        if (!r.bounds) continue;
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
            } else if (currentTool === 'room') {
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
            if (isPanning) {
                panOffset.x = e.clientX - panStartX;
                panOffset.y = e.clientY - panStartY;
                draw();
                return;
            }

            const rect = canvas.getBoundingClientRect();
            const mouseX = (e.clientX - rect.left - panOffset.x) / zoomLevel;
            const mouseY = (e.clientY - rect.top - panOffset.y) / zoomLevel;

            const tileX = Math.floor(mouseX / TILE_SIZE);
            const tileY = Math.floor(mouseY / TILE_SIZE);

            if (isDragging && selectedItem) {
                const maxTilesX = Math.floor(MAP_WIDTH_PX / TILE_SIZE);
                const maxTilesY = Math.floor(MAP_HEIGHT_PX / TILE_SIZE);
                const dx = tileX - dragStartTileX;
                const dy = tileY - dragStartTileY;

                if (selectedItem.type === 'object') {
                    const objW = selectedItem.item.width || (selectedItem.item.size ? selectedItem.item.size.width : 1);
                    const objH = selectedItem.item.height || (selectedItem.item.size ? selectedItem.item.size.height : 1);
                    selectedItem.item.position.x = Math.max(0, Math.min(maxTilesX - objW, dragOrigX + dx));
                    selectedItem.item.position.y = Math.max(0, Math.min(maxTilesY - objH, dragOrigY + dy));
                } else if (selectedItem.type === 'room') {
                    const rw = selectedItem.item.bounds.width || 1;
                    const rh = selectedItem.item.bounds.height || 1;
                    const newRoomX = Math.max(0, Math.min(maxTilesX - rw, dragOrigX + dx));
                    const newRoomY = Math.max(0, Math.min(maxTilesY - rh, dragOrigY + dy));
                    selectedItem.item.bounds.x = newRoomX;
                    selectedItem.item.bounds.y = newRoomY;

                    if (roomContainedObjects && roomContainedObjects.length > 0) {
                        roomContainedObjects.forEach(entry => {
                            if (entry.obj && entry.obj.position) {
                                entry.obj.position.x = Math.max(0, Math.min(maxTilesX - 1, newRoomX + entry.relX));
                                entry.obj.position.y = Math.max(0, Math.min(maxTilesY - 1, newRoomY + entry.relY));
                            }
                        });
                    }
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

        window.addEventListener('mouseup', () => {
            if (isPanning) {
                isPanning = false;
                canvas.style.cursor = currentTool === 'select' ? 'default' : 'crosshair';
            }

            if (isDragging) {
                isDragging = false;
                roomContainedObjects = [];
                canvas.style.cursor = currentTool === 'select' ? 'default' : 'crosshair';
                updateFloatingActions();
                draw();
            }

            if (isDrawing && currentRect) {
                if (currentTool === 'room') {
                    if (PLAN_MAX_ROOMS > 0 && rooms.length >= PLAN_MAX_ROOMS) {
                        isDrawing = false;
                        currentRect = null;
                        draw();
                        alert(`{{ __('Room Limit Exceeded!') }}\n{{ __('Your subscription plan allows a maximum of :limit rooms.', ['limit' => '']) }}${PLAN_MAX_ROOMS}\n{{ __('Please upgrade your plan to add more rooms.') }}`);
                        setTool('select');
                        return;
                    }

                    const newRoom = {
                        name: `${currentRoomType.charAt(0).toUpperCase() + currentRoomType.slice(1)} Room`,
                        type: currentRoomType,
                        access_mode: currentRoomType === 'private' ? 'private' : 'public',
                        capacity: 10,
                        color: currentRoomColor,
                        bounds: { ...currentRect },
                        metadata: { audio_isolation: true }
                    };
                    rooms.push(newRoom);
                    selectedItem = { type: 'room', item: newRoom };

                    // Save room to backend
                    fetch('/editor/rooms', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': CSRF_TOKEN,
                            'Accept': 'application/json'
                        },
                        credentials: 'same-origin',
                        body: JSON.stringify({
                            organization_id: ORG_ID,
                            map_id: MAP_ID,
                            name: newRoom.name,
                            type: newRoom.type,
                            access_mode: newRoom.access_mode,
                            capacity: newRoom.capacity,
                            color: newRoom.color,
                            bounds: newRoom.bounds,
                            metadata: newRoom.metadata
                        })
                    }).then(res => res.json()).then(data => {
                        if (data.room && data.room.id) newRoom.id = data.room.id;
                    }).catch(console.error);

                    switchDrawerTab('inspector');
                    showToast('🏢 {{ __("Room created!") }}');
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
            if (['input', 'textarea', 'select'].includes(document.activeElement.tagName.toLowerCase())) return;
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

        // ── Main Live Canvas Draw Loop (60 FPS Butter Smooth) ──
        function draw() {
            // Guarantee complete clean buffer wipe without transform accumulation
            ctx.setTransform(1, 0, 0, 1, 0, 0);
            ctx.clearRect(0, 0, canvas.width, canvas.height);

            ctx.save();
            ctx.translate(panOffset.x, panOffset.y);
            ctx.scale(zoomLevel, zoomLevel);

            const hasBlueprint = BLUEPRINT_IMAGE && BLUEPRINT_IMAGE.complete && BLUEPRINT_IMAGE.naturalWidth > 0;

            // 1. Draw Background Blueprint Layer
            if (hasBlueprint) {
                ctx.fillStyle = '#ECE8DB';
                ctx.fillRect(0, 0, MAP_WIDTH_PX, MAP_HEIGHT_PX);
                ctx.drawImage(BLUEPRINT_IMAGE, 0, 0, MAP_WIDTH_PX, MAP_HEIGHT_PX);
            } else {
                ctx.fillStyle = '#0F1E16';
                ctx.fillRect(0, 0, MAP_WIDTH_PX, MAP_HEIGHT_PX);

                if (showGrid) {
                    ctx.strokeStyle = 'rgba(79, 155, 95, 0.08)';
                    ctx.lineWidth = 1;
                    for (let x = 0; x <= MAP_WIDTH_PX; x += TILE_SIZE) {
                        ctx.beginPath(); ctx.moveTo(x, 0); ctx.lineTo(x, MAP_HEIGHT_PX); ctx.stroke();
                    }
                    for (let y = 0; y <= MAP_HEIGHT_PX; y += TILE_SIZE) {
                        ctx.beginPath(); ctx.moveTo(0, y); ctx.lineTo(MAP_WIDTH_PX, y); ctx.stroke();
                    }
                }
                ctx.strokeStyle = '#2D5C3E';
                ctx.lineWidth = 2.5;
                ctx.strokeRect(0, 0, MAP_WIDTH_PX, MAP_HEIGHT_PX);
            }

            // 2. Draw Unselected Rooms Dynamically (Sleek Glass Pills)
            rooms.forEach((r) => {
                const isSelected = selectedItem && selectedItem.type === 'room' && selectedItem.item === r;
                if (isSelected || !r.bounds) return;

                const rx = r.bounds.x * TILE_SIZE;
                const ry = r.bounds.y * TILE_SIZE;
                const rw = r.bounds.width * TILE_SIZE;
                const rh = r.bounds.height * TILE_SIZE;

                // Subtle transparent wash & dashed boundary
                ctx.fillStyle = 'rgba(79, 155, 95, 0.06)';
                ctx.fillRect(rx, ry, rw, rh);

                ctx.strokeStyle = 'rgba(79, 155, 95, 0.45)';
                ctx.lineWidth = 1.2;
                ctx.setLineDash([4, 4]);
                ctx.strokeRect(rx, ry, rw, rh);
                ctx.setLineDash([]);

                // Sleek Dark Glass Floating Room Pill Tag
                const labelText = `🏢 ${r.name.split(' - ')[0]}`;
                ctx.font = 'bold 9px Cairo, Inter, sans-serif';
                const textWidth = ctx.measureText(labelText).width;
                const badgeW = Math.min(rw - 8, textWidth + 14);

                ctx.fillStyle = 'rgba(15, 23, 42, 0.85)';
                if (ctx.roundRect) ctx.roundRect(rx + 4, ry + 4, badgeW, 18, 9);
                else ctx.rect(rx + 4, ry + 4, badgeW, 18);
                ctx.fill();

                ctx.strokeStyle = 'rgba(255, 255, 255, 0.15)';
                ctx.lineWidth = 1;
                if (ctx.roundRect) ctx.roundRect(rx + 4, ry + 4, badgeW, 18, 9);
                else ctx.rect(rx + 4, ry + 4, badgeW, 18);
                ctx.stroke();

                ctx.fillStyle = '#F8FAFC';
                ctx.textAlign = 'left';
                ctx.textBaseline = 'middle';
                ctx.fillText(labelText, rx + 10, ry + 13);
            });

            // 3. Selected Room Rectangular Acoustic Sound Isolation Aura & Handles
            if (selectedItem && selectedItem.type === 'room' && selectedItem.item.bounds) {
                const r = selectedItem.item;
                const rx = r.bounds.x * TILE_SIZE;
                const ry = r.bounds.y * TILE_SIZE;
                const rw = r.bounds.width * TILE_SIZE;
                const rh = r.bounds.height * TILE_SIZE;

                r.metadata = r.metadata || {};
                const isIsolated = r.metadata.audio_isolation !== false;

                // Acoustic Aura Backdrop
                ctx.fillStyle = isIsolated ? 'rgba(79, 155, 95, 0.22)' : 'rgba(59, 130, 246, 0.15)';
                if (ctx.roundRect) ctx.roundRect(rx - 6, ry - 6, rw + 12, rh + 12, 10);
                else ctx.rect(rx - 6, ry - 6, rw + 12, rh + 12);
                ctx.fill();

                // Acoustic Sound Boundary Border
                ctx.strokeStyle = isIsolated ? 'rgba(79, 155, 95, 0.90)' : 'rgba(59, 130, 246, 0.80)';
                ctx.lineWidth = 2.5;
                ctx.setLineDash([8, 6]);
                if (ctx.roundRect) ctx.roundRect(rx - 2, ry - 2, rw + 4, rh + 4, 8);
                else ctx.rect(rx - 2, ry - 2, rw + 4, rh + 4);
                ctx.stroke();
                ctx.setLineDash([]);

                // Corner Grab Accent Nodes
                const corners = [
                    { x: rx - 2, y: ry - 2 },
                    { x: rx + rw + 2, y: ry - 2 },
                    { x: rx + rw + 2, y: ry + rh + 2 },
                    { x: rx - 2, y: ry + rh + 2 }
                ];
                corners.forEach(c => {
                    ctx.fillStyle = '#4F9B5F';
                    ctx.beginPath();
                    ctx.arc(c.x, c.y, 4, 0, Math.PI * 2);
                    ctx.fill();
                    ctx.strokeStyle = '#FFFFFF';
                    ctx.lineWidth = 1.5;
                    ctx.stroke();
                });

                // Acoustic Badge Indicator
                const badgeText = isIsolated ? `🎙️ ${r.name || 'Room'} (Acoustic Boundary)` : `🔊 ${r.name || 'Room'} (Open Area)`;
                ctx.font = 'bold 11px Cairo, Inter, sans-serif';
                const bMetrics = ctx.measureText(badgeText);
                const bW = bMetrics.width + 22;
                const badgeX = rx + rw / 2 - bW / 2;
                const badgeY = ry - 30;

                ctx.fillStyle = 'rgba(15, 23, 42, 0.92)';
                if (ctx.roundRect) ctx.roundRect(badgeX, badgeY, badgeW, 24, 6);
                else ctx.rect(badgeX, badgeY, badgeW, 24);
                ctx.fill();

                ctx.strokeStyle = isIsolated ? '#4F9B5F' : '#3B82F6';
                ctx.lineWidth = 1.5;
                if (ctx.roundRect) ctx.roundRect(badgeX, badgeY, badgeW, 24, 6);
                else ctx.rect(badgeX, badgeY, badgeW, 24);
                ctx.stroke();

                ctx.fillStyle = isIsolated ? '#7EE092' : '#93C5FD';
                ctx.textAlign = 'center';
                ctx.textBaseline = 'middle';
                ctx.fillText(badgeText, rx + rw / 2, ry - 18);
            }

            // 4. Draw Furniture Objects
            objects.forEach(obj => {
                // If map has blueprint artwork, seeded untextured placeholder collision items should not be painted as blue blocks
                if (hasBlueprint && !obj.image_url && !obj.is_custom) {
                    // Only show subtle dashed selection box when user specifically selects it
                    if (selectedItem && selectedItem.type === 'object' && selectedItem.item === obj) {
                        const ox = (obj.position ? obj.position.x : 0) * TILE_SIZE;
                        const oy = (obj.position ? obj.position.y : 0) * TILE_SIZE;
                        const objW = (obj.width || (obj.size ? obj.size.width : 1)) * TILE_SIZE;
                        const objH = (obj.height || (obj.size ? obj.size.height : 1)) * TILE_SIZE;
                        ctx.save();
                        ctx.strokeStyle = '#10B981';
                        ctx.lineWidth = 1.5;
                        ctx.setLineDash([4, 4]);
                        ctx.strokeRect(ox, oy, objW, objH);
                        ctx.restore();
                    }
                    return;
                }

                const ox = (obj.position ? obj.position.x : 0) * TILE_SIZE;
                const oy = (obj.position ? obj.position.y : 0) * TILE_SIZE;
                const objW = (obj.width || (obj.size ? obj.size.width : 1)) * TILE_SIZE;
                const objH = (obj.height || (obj.size ? obj.size.height : 1)) * TILE_SIZE;
                const isSelected = selectedItem && selectedItem.type === 'object' && selectedItem.item === obj;

                ctx.save();
                ctx.translate(ox + objW / 2, oy + objH / 2);
                const rot = (obj.position && typeof obj.position.rotation === 'number') ? obj.position.rotation : (obj.rotation || 0);
                if (rot) ctx.rotate((rot * Math.PI) / 180);

                if (obj.image_url) {
                    if (!window._objImgCache) window._objImgCache = new Map();
                    let sprImg = window._objImgCache.get(obj.image_url);
                    if (!sprImg) {
                        sprImg = new Image();
                        sprImg.src = obj.image_url;
                        sprImg.onload = () => { if (typeof draw === 'function') draw(); };
                        window._objImgCache.set(obj.image_url, sprImg);
                    }
                    if (sprImg && sprImg.complete && sprImg.naturalWidth > 0) {
                        ctx.drawImage(sprImg, -objW / 2, -objH / 2, objW, objH);
                    } else {
                        ctx.fillStyle = 'rgba(59, 130, 246, 0.15)';
                        if (ctx.roundRect) ctx.roundRect(-objW / 2, -objH / 2, objW, objH, 4);
                        else ctx.rect(-objW / 2, -objH / 2, objW, objH);
                        ctx.fill();
                    }
                } else if (obj.is_custom) {
                    ctx.fillStyle = obj.color ? (obj.color + '44') : 'rgba(59, 130, 246, 0.25)';
                    if (ctx.roundRect) ctx.roundRect(-objW / 2, -objH / 2, objW, objH, 4);
                    else ctx.rect(-objW / 2, -objH / 2, objW, objH);
                    ctx.fill();
                }

                if (isSelected) {
                    ctx.strokeStyle = '#10B981';
                    ctx.lineWidth = 2;
                    ctx.setLineDash([4, 4]);
                    if (ctx.roundRect) ctx.roundRect(-objW / 2 - 2, -objH / 2 - 2, objW + 4, objH + 4, 4);
                    else ctx.rect(-objW / 2 - 2, -objH / 2 - 2, objW + 4, objH + 4);
                    ctx.stroke();
                    ctx.setLineDash([]);
                }

                ctx.restore();
            });

            // 5. Drawing Box
            if (isDrawing && currentRect) {
                const dx = currentRect.x * TILE_SIZE;
                const dy = currentRect.y * TILE_SIZE;
                const dw = currentRect.width * TILE_SIZE;
                const dh = currentRect.height * TILE_SIZE;

                ctx.fillStyle = 'rgba(16, 185, 129, 0.18)';
                ctx.fillRect(dx, dy, dw, dh);
                ctx.strokeStyle = '#10B981';
                ctx.lineWidth = 2;
                ctx.setLineDash([4, 4]);
                ctx.strokeRect(dx, dy, dw, dh);
                ctx.setLineDash([]);
            }

            ctx.restore();
        }

        // ── Inspector & Property Management ──
        function updateInspector() {
            const emptyBox = document.getElementById('inspector-empty-msg');
            const contentBox = document.getElementById('inspector-content');
            const objFields = document.getElementById('inspector-object-fields');
            const roomFields = document.getElementById('inspector-room-fields');

            if (!selectedItem) {
                emptyBox.style.display = 'block';
                contentBox.style.display = 'none';
                return;
            }

            emptyBox.style.display = 'none';
            contentBox.style.display = 'flex';
            const item = selectedItem.item;

            if (selectedItem.type === 'object') {
                objFields.style.display = 'flex';
                roomFields.style.display = 'none';
                document.getElementById('prop-name').value = item.name || '';
                document.getElementById('prop-width').value = item.width || (item.size ? item.size.width : 1);
                document.getElementById('prop-height').value = item.height || (item.size ? item.size.height : 1);
                const rot = (item.position && typeof item.position.rotation === 'number') ? item.position.rotation : (item.rotation || 0);
                document.querySelectorAll('.rot-btn').forEach(btn => {
                    btn.classList.toggle('active', btn.textContent.includes(`${rot}°`));
                });
            } else if (selectedItem.type === 'room') {
                objFields.style.display = 'none';
                roomFields.style.display = 'flex';
                item.metadata = item.metadata || {};

                document.getElementById('prop-room-name').value = item.name || '';
                document.getElementById('prop-room-type').value = item.type || 'meeting';
                document.getElementById('prop-room-capacity').value = item.capacity || 10;
                document.getElementById('prop-room-isolation').checked = item.metadata.audio_isolation !== false;

                const bounds = item.bounds || { width: 1, height: 1 };
                document.getElementById('prop-room-bounds-label').textContent = `${bounds.width}×${bounds.height} Tiles (${bounds.width * TILE_SIZE}×${bounds.height * TILE_SIZE}px)`;
            }
        }

        function updateRoomProp(prop, val) {
            if (!selectedItem || selectedItem.type !== 'room') return;
            const r = selectedItem.item;
            r.metadata = r.metadata || {};

            if (prop === 'name') r.name = val;
            else if (prop === 'type') r.type = val;
            else if (prop === 'capacity') r.capacity = parseInt(val) || 10;
            else if (prop === 'color') r.color = val;
            else if (prop === 'audio_isolation') r.metadata.audio_isolation = !!val;

            draw();
        }

        function updateSelectedProp(prop, val) {
            if (!selectedItem) return;
            if (prop === 'name') selectedItem.item.name = val;
            if (prop === 'color') selectedItem.item.color = val;
            if (prop === 'width') selectedItem.item.width = val;
            if (prop === 'height') selectedItem.item.height = val;
            draw();
        }

        function setRotation(deg) {
            if (!selectedItem || selectedItem.type !== 'object') return;
            const obj = selectedItem.item;
            if (!obj.position) obj.position = { x: 0, y: 0, rotation: 0 };
            obj.position.rotation = deg;
            obj.rotation = deg;
            updateInspector();
            draw();
        }

        function rotateSelectedItem(deg = 90) {
            if (!selectedItem || selectedItem.type !== 'object') return;
            const obj = selectedItem.item;
            if (!obj.position) obj.position = { x: 0, y: 0, rotation: 0 };
            const curRot = typeof obj.position.rotation === 'number' ? obj.position.rotation : (obj.rotation || 0);
            setRotation((curRot + deg) % 360);
        }

        function duplicateSelectedItem() {
            if (!selectedItem || selectedItem.type !== 'object') return;
            const orig = selectedItem.item;
            const cloned = JSON.parse(JSON.stringify(orig));
            cloned.name = `${orig.name || 'Object'} (Copy)`;
            cloned.position.x = Math.min(Math.floor(MAP_WIDTH_PX / TILE_SIZE) - 2, cloned.position.x + 2);
            cloned.position.y = Math.min(Math.floor(MAP_HEIGHT_PX / TILE_SIZE) - 2, cloned.position.y + 2);
            objects.push(cloned);
            selectedItem = { type: 'object', item: cloned };
            updateInspector();
            updateFloatingActions();
            draw();
            showToast('📋 {{ __("Object cloned!") }}');
        }

        function deleteSelectedItem() {
            if (!selectedItem) return;
            if (selectedItem.type === 'object') {
                const idx = objects.indexOf(selectedItem.item);
                if (idx > -1) objects.splice(idx, 1);
            } else if (selectedItem.type === 'room') {
                const r = selectedItem.item;
                const idx = rooms.indexOf(r);
                if (idx > -1) rooms.splice(idx, 1);
                if (r.id) {
                    fetch(`/editor/rooms/${r.id}`, {
                        method: 'DELETE',
                        headers: { 'X-CSRF-TOKEN': CSRF_TOKEN, 'Accept': 'application/json' },
                        credentials: 'same-origin'
                    }).catch(console.error);
                }
            }
            selectedItem = null;
            updateInspector();
            hideFloatingActions();
            draw();
            showToast('🗑️ {{ __("Item deleted") }}');
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

        function renderRoomsDirectory() {
            const container = document.getElementById('rooms-list-container');
            if (!container) return;
            if (rooms.length === 0) {
                container.innerHTML = `<div style="font-size: 11px; color: var(--text-muted); text-align: center; padding: 16px;">{{ __("No rooms configured yet. Click Add Room to create one.") }}</div>`;
                return;
            }
            let html = '';
            rooms.forEach((r, idx) => {
                html += `
                    <div style="background: var(--bg-card); border: 1px solid var(--border-card); border-radius: 8px; padding: 10px; display: flex; align-items: center; justify-content: space-between;">
                        <div style="display: flex; flex-direction: column; gap: 2px;">
                            <strong style="font-size: 12px; color: var(--text-main);">🏢 ${r.name}</strong>
                            <span style="font-size: 10px; color: var(--text-muted);">${r.type || 'meeting'} • ${r.capacity || 10} seats</span>
                        </div>
                        <button onclick="selectRoomByIndex(${idx})" class="tool-btn" style="padding: 4px 8px; font-size: 11px;">🔍</button>
                    </div>
                `;
            });
            container.innerHTML = html;
        }

        function selectRoomByIndex(idx) {
            if (rooms[idx]) {
                selectedItem = { type: 'room', item: rooms[idx] };
                switchDrawerTab('inspector');
                updateInspector();
                draw();
            }
        }

        // ── Backend Sync: Save & Publish ──
        async function saveSelectedRoom() {
            if (!selectedItem || selectedItem.type !== 'room') return;
            const r = selectedItem.item;
            showToast('💾 {{ __("Saving room settings...") }}');

            try {
                let isUuid = r.id && typeof r.id === 'string' && r.id.length === 36 && r.id.includes('-');
                let url = isUuid ? `/editor/rooms/${r.id}` : `/editor/rooms`;
                let method = isUuid ? 'PATCH' : 'POST';
                let body = {
                    organization_id: ORG_ID,
                    map_id: MAP_ID,
                    name: r.name || 'Meeting Room',
                    type: r.type || 'meeting',
                    access_mode: r.access_mode || 'public',
                    capacity: parseInt(r.capacity) || 10,
                    color: r.color || '#4F9B5F',
                    bounds: r.bounds || { x: 1, y: 1, width: 8, height: 6 },
                    metadata: r.metadata || {}
                };

                const res = await fetch(url, {
                    method: method,
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF_TOKEN, 'Accept': 'application/json' },
                    credentials: 'same-origin',
                    body: JSON.stringify(body)
                });
                const data = await res.json();
                if (res.ok) {
                    if (data.room && data.room.id) r.id = data.room.id;
                    draw();
                    showToast('✅ {{ __("Room saved successfully!") }}');
                } else {
                    showToast('❌ ' + (data.message || 'Save failed'));
                }
            } catch(e) {
                console.error(e);
                showToast('❌ {{ __("Failed to save room") }}');
            }
        }

        async function saveMapDraft() {
            showToast('💾 {{ __("Saving map draft...") }}');
            try {
                const payload = {
                    name: MAP_DATA.name,
                    layout_data: MAP_DATA.layout_data || {},
                    rooms: rooms.map(r => ({
                        id: r.id,
                        name: r.name,
                        type: r.type || 'meeting',
                        access_mode: r.access_mode || 'public',
                        capacity: parseInt(r.capacity) || 10,
                        color: r.color || '#4F9B5F',
                        bounds: r.bounds,
                        metadata: r.metadata || {}
                    })),
                    objects: objects.map(o => ({
                        type: o.type,
                        name: o.name,
                        position: o.position || { x: 0, y: 0, rotation: 0 },
                        size: o.size || { width: o.width || 1, height: o.height || 1 },
                        rotation: o.position?.rotation || o.rotation || 0,
                        color: o.color,
                        interaction_config: o.interaction_config
                    }))
                };

                const res = await fetch(`/editor/maps/${MAP_ID}/save`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF_TOKEN, 'Accept': 'application/json' },
                    credentials: 'same-origin',
                    body: JSON.stringify(payload)
                });
                const data = await res.json();
                if (res.ok) {
                    showToast('✅ {{ __("Draft saved successfully!") }}');
                } else {
                    showToast('❌ ' + (data.message || 'Save failed'));
                }
            } catch(e) {
                console.error(e);
                showToast('❌ {{ __("Failed to save draft") }}');
            }
        }

        async function publishMap() {
            if (!confirm('{{ __("Are you sure you want to publish this map layout to all live office users?") }}')) return;
            showToast('🚀 {{ __("Publishing map...") }}');
            try {
                await saveMapDraft();

                const res = await fetch(`/editor/maps/${MAP_ID}/publish`, {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': CSRF_TOKEN, 'Accept': 'application/json' },
                    credentials: 'same-origin'
                });
                const data = await res.json();
                if (res.ok) {
                    showToast('🎉 {{ __("Map published successfully!") }}');
                    const badge = document.getElementById('header-version-badge');
                    if (badge && data.map) {
                        badge.textContent = `v${data.map.version} (${data.map.status})`;
                    }
                } else {
                    showToast('❌ ' + (data.message || 'Publish failed'));
                }
            } catch(e) {
                console.error(e);
                showToast('❌ {{ __("Publish error") }}');
            }
        }

        // ── Custom Floorplan Background Upload & Clear ──
        function triggerFloorplanUpload() {
            document.getElementById('floorplan-file-input').click();
        }

        async function handleFloorplanUpload(input) {
            if (!input.files || !input.files[0]) return;
            const file = input.files[0];
            const formData = new FormData();
            formData.append('image', file);

            showToast('⏳ {{ __("Uploading floorplan image...") }}');

            try {
                const res = await fetch(`/editor/maps/${MAP_ID}/background`, {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': CSRF_TOKEN, 'Accept': 'application/json' },
                    body: formData,
                    credentials: 'same-origin'
                });
                const data = await res.json();
                if (res.ok && data.image_url) {
                    const newUrl = data.image_url + (data.image_url.includes('?') ? '&' : '?') + 'v=' + Date.now();
                    BLUEPRINT_IMAGE.src = newUrl;
                    BLUEPRINT_IMAGE.onload = () => {
                        blueprintLoaded = true;
                        draw();
                    };
                    MAP_DATA.layout_data = MAP_DATA.layout_data || {};
                    MAP_DATA.layout_data.background_image_url = data.image_url;
                    showToast('✅ {{ __("Floorplan uploaded and active!") }}');
                    draw();
                } else {
                    showToast('❌ ' + (data.message || 'Upload failed'));
                }
            } catch(e) {
                console.error(e);
                showToast('❌ {{ __("Upload error") }}');
            }
        }

        async function deleteFloorplan() {
            if (!confirm('{{ __("Are you sure you want to remove the custom floorplan and reset to system default?") }}')) return;
            showToast('🗑️ {{ __("Removing floorplan...") }}');
            try {
                const res = await fetch(`/editor/maps/${MAP_ID}/background`, {
                    method: 'DELETE',
                    headers: { 'X-CSRF-TOKEN': CSRF_TOKEN, 'Accept': 'application/json' },
                    credentials: 'same-origin'
                });
                const data = await res.json();
                if (res.ok) {
                    MAP_DATA.layout_data = MAP_DATA.layout_data || {};
                    delete MAP_DATA.layout_data.background_image_url;
                    BLUEPRINT_IMAGE.src = '/images/office_floorplan.jpg?v=' + Date.now();
                    showToast('✅ {{ __("Floorplan reset to default!") }}');
                    draw();
                } else {
                    showToast('❌ ' + (data.message || 'Reset failed'));
                }
            } catch(e) {
                console.error(e);
                showToast('❌ {{ __("Failed to delete floorplan") }}');
            }
        }

        async function clearWorkspace() {
            if (!confirm('{{ __("Are you sure you want to clear all furniture and reset the canvas for a fresh layout?") }}')) return;
            showToast('🧹 {{ __("Clearing canvas...") }}');
            try {
                const res = await fetch(`/editor/maps/${MAP_ID}/clear`, {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': CSRF_TOKEN, 'Accept': 'application/json' },
                    credentials: 'same-origin'
                });
                const data = await res.json();
                if (res.ok) {
                    objects.length = 0;
                    selectedItem = null;
                    MAP_DATA.layout_data = MAP_DATA.layout_data || {};
                    delete MAP_DATA.layout_data.background_image_url;
                    BLUEPRINT_IMAGE.src = '';
                    blueprintLoaded = false;
                    updateInspector();
                    hideFloatingActions();
                    draw();
                    showToast('✨ {{ __("Canvas cleared! You can now upload a new floorplan and place rooms/furniture.") }}');
                } else {
                    showToast('❌ ' + (data.message || 'Clear failed'));
                }
            } catch(e) {
                console.error(e);
                showToast('❌ {{ __("Failed to clear canvas") }}');
            }
        }

        // ── Dropdown Handlers ──
        function toggleBranchDropdown(e) {
            e.stopPropagation();
            const dd = document.getElementById('branch-select-dropdown');
            const other = document.getElementById('floorplan-actions-dropdown');
            if (other) other.style.display = 'none';
            if (dd) dd.style.display = dd.style.display === 'none' ? 'flex' : 'none';
        }

        function toggleFloorplanDropdown(e) {
            e.stopPropagation();
            const dd = document.getElementById('floorplan-actions-dropdown');
            const other = document.getElementById('branch-select-dropdown');
            if (other) other.style.display = 'none';
            if (dd) dd.style.display = dd.style.display === 'none' ? 'flex' : 'none';
        }

        function closeDropdowns() {
            const d1 = document.getElementById('branch-select-dropdown');
            const d2 = document.getElementById('floorplan-actions-dropdown');
            if (d1) d1.style.display = 'none';
            if (d2) d2.style.display = 'none';
        }

        document.addEventListener('click', (e) => {
            if (!e.target.closest('#branch-select-dropdown') && !e.target.closest('#floorplan-actions-dropdown')) {
                closeDropdowns();
            }
        });

        function showToast(msg) {
            const t = document.getElementById('toast-bubble');
            t.textContent = msg;
            t.style.display = 'block';
            setTimeout(() => { t.style.display = 'none'; }, 3200);
        }

        // ── AI Workplace Generator Logic ──
        const PLAN_ROOM_LIMIT = {{ $plan && $plan->room_limit > 0 ? $plan->room_limit : 9999 }};
        const PLAN_SEAT_LIMIT = {{ $plan && $plan->seat_limit > 0 ? $plan->seat_limit : 9999 }};

        function openAiGeneratorModal() {
            calculateAiQuotas();
            document.getElementById('ai-generator-modal').style.display = 'flex';
        }

        function closeAiGeneratorModal() {
            document.getElementById('ai-generator-modal').style.display = 'none';
        }

        function selectAiStyle(styleKey) {
            document.querySelectorAll('.ai-style-card').forEach(el => {
                el.classList.remove('active');
                el.style.borderColor = 'var(--border-card)';
                el.style.background = 'var(--bg-surface)';
            });
            const sel = document.getElementById('ai-style-' + styleKey);
            if (sel) {
                sel.classList.add('active');
                sel.style.borderColor = 'var(--brand-primary)';
                sel.style.background = 'rgba(16, 185, 129, 0.1)';
            }
            const radio = document.querySelector(`input[name="ai_style"][value="${styleKey}"]`);
            if (radio) radio.checked = true;
        }

        function changeAiCounter(fieldId, delta, minVal, maxVal) {
            const inp = document.getElementById(fieldId);
            if (!inp) return;
            let val = parseInt(inp.value) || 0;
            val = Math.max(minVal, Math.min(maxVal, val + delta));
            inp.value = val;
            calculateAiQuotas();
        }

        function getAiFieldValue(id, fallback = 0) {
            const el = document.getElementById(id);
            if (!el) return fallback;
            const parsed = parseInt(el.value, 10);
            return isNaN(parsed) ? fallback : parsed;
        }

        function calculateAiQuotas() {
            const meeting = getAiFieldValue('ai-inp-meeting', 0);
            const office = getAiFieldValue('ai-inp-office', 1);
            const desks = getAiFieldValue('ai-inp-desks', 1);
            const thinking = getAiFieldValue('ai-inp-thinking', 0);
            const rest = getAiFieldValue('ai-inp-rest', 0);
            const theater = getAiFieldValue('ai-inp-theater', 0);

            // Only count actual custom rooms selected by the user
            const totalRooms = meeting + office + thinking + rest + theater;
            // Only count team office workstations/desks
            const totalDesks = (office * desks);

            const roomBadge = document.getElementById('ai-quota-rooms-val');
            const seatBadge = document.getElementById('ai-quota-seats-val');
            const quotaWarning = document.getElementById('ai-quota-warning-box');
            const generateBtn = document.getElementById('btn-ai-submit-generate');

            if (roomBadge) {
                roomBadge.textContent = `${totalRooms} / ${PLAN_ROOM_LIMIT < 9999 ? PLAN_ROOM_LIMIT : '∞'}`;
                roomBadge.style.color = (PLAN_ROOM_LIMIT < 9999 && totalRooms > PLAN_ROOM_LIMIT) ? '#EF4444' : '#10B981';
            }

            if (seatBadge) {
                seatBadge.textContent = `${totalDesks} / ${PLAN_SEAT_LIMIT < 9999 ? PLAN_SEAT_LIMIT : '∞'}`;
                seatBadge.style.color = (PLAN_SEAT_LIMIT < 9999 && totalDesks > PLAN_SEAT_LIMIT) ? '#EF4444' : '#3B82F6';
            }

            let hasError = false;
            let errorMsg = '';

            if (PLAN_ROOM_LIMIT < 9999 && totalRooms > PLAN_ROOM_LIMIT) {
                hasError = true;
                errorMsg = `{{ __('Total rooms (:total) exceed your plan limit (:limit). Please reduce room count or upgrade plan.', ['total' => '__TOTAL__', 'limit' => '__LIMIT__']) }}`
                    .replace('__TOTAL__', totalRooms)
                    .replace('__LIMIT__', PLAN_ROOM_LIMIT);
            } else if (PLAN_SEAT_LIMIT < 9999 && totalDesks > PLAN_SEAT_LIMIT) {
                hasError = true;
                errorMsg = `{{ __('Total office desks (:total) exceed your subscription capacity (:limit seats). Please reduce desk count or offices.', ['total' => '__TOTAL__', 'limit' => '__LIMIT__']) }}`
                    .replace('__TOTAL__', totalDesks)
                    .replace('__LIMIT__', PLAN_SEAT_LIMIT);
            }

            if (quotaWarning) {
                if (hasError) {
                    quotaWarning.style.display = 'block';
                    quotaWarning.innerHTML = `⚠️ ${errorMsg}`;
                    generateBtn.disabled = true;
                    generateBtn.style.opacity = '0.5';
                    generateBtn.style.cursor = 'not-allowed';
                } else {
                    quotaWarning.style.display = 'none';
                    generateBtn.disabled = false;
                    generateBtn.style.opacity = '1';
                    generateBtn.style.cursor = 'pointer';
                }
            }
        }

        async function generateAiOfficeOnCanvas() {
            const styleRadio = document.querySelector('input[name="ai_style"]:checked');
            const styleKey = styleRadio ? styleRadio.value : 'modern_glass_luxury';
            const meeting = getAiFieldValue('ai-inp-meeting', 0);
            const office = getAiFieldValue('ai-inp-office', 1);
            const desks = getAiFieldValue('ai-inp-desks', 1);
            const thinking = getAiFieldValue('ai-inp-thinking', 0);
            const rest = getAiFieldValue('ai-inp-rest', 0);
            const theater = getAiFieldValue('ai-inp-theater', 0);

            const modalContent = document.getElementById('ai-modal-form-content');
            const loadingBox = document.getElementById('ai-modal-loading-box');
            const statusStep = document.getElementById('ai-loading-step-text');

            modalContent.style.display = 'none';
            loadingBox.style.display = 'flex';

            const steps = [
                '🧠 {{ __("Analyzing room requirements & architectural parameters...") }}',
                '🎨 {{ __("Calling OpenAI DALL-E 3 to generate 3D isometric blueprint...") }}',
                '📐 {{ __("Computing geometric spatial partitions & room isolation boundaries...") }}',
                '🚀 {{ __("Mapping isolated rooms, collision zones, and canvas layout...") }}'
            ];

            let stepIdx = 0;
            statusStep.textContent = steps[stepIdx];
            const interval = setInterval(() => {
                stepIdx = (stepIdx + 1) % steps.length;
                statusStep.textContent = steps[stepIdx];
            }, 3500);

            try {
                const res = await fetch('/organization/ai-map/generate', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': CSRF_TOKEN
                    },
                    credentials: 'same-origin',
                    body: JSON.stringify({
                        target_floor_id: '{{ $floor->id }}',
                        style: styleKey,
                        meeting_rooms: meeting,
                        office_rooms: office,
                        desks_per_office: desks,
                        thinking_rooms: thinking,
                        rest_areas: rest,
                        theaters: theater
                    })
                });

                clearInterval(interval);

                const data = await res.json();
                if (!res.ok || !data.success) {
                    throw new Error(data.message || 'AI Generation failed.');
                }

                // Apply new background image
                if (data.background_image_url) {
                    MAP_DATA.layout_data = MAP_DATA.layout_data || {};
                    MAP_DATA.layout_data.background_image_url = data.background_image_url;
                    BLUEPRINT_IMAGE.src = data.background_image_url + (data.background_image_url.includes('?') ? '&' : '?') + 'v=' + Date.now();
                }

                selectedItem = null;
                updateInspector();
                hideFloatingActions();
                draw();

                closeAiGeneratorModal();
                modalContent.style.display = 'block';
                loadingBox.style.display = 'none';

                showToast('✨ ' + (data.message || '{{ __("AI Virtual Office floorplan generated successfully!") }}'));
            } catch (err) {
                clearInterval(interval);
                modalContent.style.display = 'block';
                loadingBox.style.display = 'none';
                alert('⚠️ ' + (err.message || 'An error occurred during AI generation.'));
            }
        }

        // Initial draw
        draw();
    </script>

    <!-- ── AI Office & Floorplan Generator Modal ── -->
    <div id="ai-generator-modal" style="display: none; position: fixed; inset: 0; background: rgba(6, 13, 9, 0.85); backdrop-filter: blur(14px); z-index: 99999; align-items: center; justify-content: center; padding: 20px;">
        <div style="background: var(--bg-dock); border: 1px solid var(--border-card); border-radius: var(--radius-xl); width: 100%; max-width: 820px; max-height: 90vh; overflow-y: auto; box-shadow: var(--shadow-modal); display: flex; flex-direction: column;">
            
            <!-- Modal Header -->
            <div style="padding: 22px 26px; border-bottom: 1px solid var(--border-card); display: flex; justify-content: space-between; align-items: center; background: var(--bg-surface);">
                <div style="display: flex; align-items: center; gap: 12px;">
                    <div style="width: 42px; height: 42px; border-radius: 12px; background: linear-gradient(135deg, #10B981, #059669); color: white; display: flex; align-items: center; justify-content: center; font-size: 20px; box-shadow: 0 4px 12px rgba(16, 185, 129, 0.35);">
                        ✨
                    </div>
                    <div>
                        <h2 style="font-size: 17px; font-weight: 900; color: var(--text-main); margin-bottom: 2px;">
                            {{ __('AI Virtual Office & Blueprint Generator') }}
                        </h2>
                        <p style="font-size: 12px; color: var(--text-dim);">
                            {{ __('Generate bespoke 3D isometric floorplans using OpenAI DALL-E 3 with automatic room isolation.') }}
                        </p>
                    </div>
                </div>
                <button type="button" onclick="closeAiGeneratorModal()" style="background: none; border: none; color: var(--text-dim); font-size: 22px; cursor: pointer; padding: 4px;">✕</button>
            </div>

            <!-- Loading State Overlay -->
            <div id="ai-modal-loading-box" style="display: none; flex-direction: column; align-items: center; justify-content: center; padding: 60px 30px; text-align: center; gap: 18px;">
                <div style="width: 64px; height: 64px; border: 4px solid rgba(16, 185, 129, 0.2); border-top-color: #10B981; border-radius: 50%; animation: spin 1s linear infinite;"></div>
                <h3 style="font-size: 18px; font-weight: 900; color: var(--text-main);">
                    ✨ {{ __('Generating 3D Isometric Office Blueprint...') }}
                </h3>
                <div id="ai-loading-step-text" style="font-size: 13px; color: #34D399; font-weight: 700; max-width: 480px;">
                    🧠 {{ __('Analyzing room requirements & architectural parameters...') }}
                </div>
                <p style="font-size: 11px; color: var(--text-dim); max-width: 420px;">
                    {{ __('DALL-E 3 creates high-definition architectural renders. This process usually takes between 15 to 30 seconds.') }}
                </p>
            </div>

            <!-- Form Content -->
            <div id="ai-modal-form-content" style="padding: 24px 26px; display: flex; flex-direction: column; gap: 20px;">
                
                <!-- Plan Quota Header Pill Card -->
                <div style="background: var(--bg-surface); border: 1px solid var(--border-card); border-radius: var(--radius-lg); padding: 14px 18px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px;">
                    <div>
                        <span style="font-size: 10px; font-weight: 800; text-transform: uppercase; color: var(--text-dim); display: block;">{{ __('Active Subscription Tier') }}</span>
                        <strong style="font-size: 14px; color: var(--brand-forest);">⭐ {{ $plan->name ?? 'Standard Plan' }}</strong>
                    </div>
                    <div style="display: flex; gap: 16px; align-items: center;">
                        <div style="text-align: center;">
                            <span style="font-size: 10px; color: var(--text-dim); display: block;">🏢 {{ __('Total Rooms') }}</span>
                            <span id="ai-quota-rooms-val" style="font-size: 14px; font-weight: 900; color: #10B981;">0 / ∞</span>
                        </div>
                        <div style="width: 1px; height: 26px; background: var(--border-card);"></div>
                        <div style="text-align: center;">
                            <span style="font-size: 10px; color: var(--text-dim); display: block;">🖥️ {{ __('Total Workstations / Desks (إجمالي المكاتب)') }}</span>
                            <span id="ai-quota-seats-val" style="font-size: 14px; font-weight: 900; color: #3B82F6;">0 / ∞</span>
                        </div>
                    </div>
                </div>

                <!-- 1. Architectural Style Selection -->
                <div>
                    <label style="display: block; font-size: 12px; font-weight: 800; color: var(--text-main); margin-bottom: 10px;">
                        🎨 {{ __('1. Choose Office Architectural Style (نمط المكتب المعماري)') }}
                    </label>
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 10px;">
                        @foreach($aiStyles as $key => $style)
                        <div class="ai-style-card {{ $loop->first ? 'active' : '' }}" id="ai-style-{{ $key }}" onclick="selectAiStyle('{{ $key }}')" style="background: {{ $loop->first ? 'rgba(16, 185, 129, 0.1)' : 'var(--bg-surface)' }}; border: 1px solid {{ $loop->first ? 'var(--brand-primary)' : 'var(--border-card)' }}; border-radius: var(--radius-md); padding: 12px; cursor: pointer; transition: all 0.2s ease;">
                            <label style="display: flex; align-items: flex-start; gap: 8px; cursor: pointer;">
                                <input type="radio" name="ai_style" value="{{ $key }}" {{ $loop->first ? 'checked' : '' }} style="margin-top: 3px; accent-color: var(--brand-forest);">
                                <div>
                                    <strong style="font-size: 12px; color: var(--text-main); display: block;">{{ $style['name'] }}</strong>
                                    <span style="font-size: 10px; color: var(--text-dim); line-height: 1.3; display: block; margin-top: 2px;">{{ $style['name_ar'] }}</span>
                                </div>
                            </label>
                        </div>
                        @endforeach
                    </div>
                </div>

                <!-- 2. Room Breakdown & Desks Steppers -->
                <div>
                    <label style="display: block; font-size: 12px; font-weight: 800; color: var(--text-main); margin-bottom: 10px;">
                        🏢 {{ __('2. Customize Room Quantities & Desk Counts (تخصيص الغرف والمكاتب)') }}
                    </label>

                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(230px, 1fr)); gap: 12px;">
                        
                        <!-- Meeting Rooms -->
                        <div style="background: var(--bg-surface); border: 1px solid var(--border-card); border-radius: var(--radius-md); padding: 12px;">
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                                <div>
                                    <strong style="font-size: 12px; color: #8B5CF6; display: block;">🏢 {{ __('Meeting Boardrooms') }}</strong>
                                    <span style="font-size: 10px; color: var(--text-dim);">{{ __('غرف اجتماعات زجاجية') }}</span>
                                </div>
                                <div style="display: flex; align-items: center; gap: 6px;">
                                    <button type="button" onclick="changeAiCounter('ai-inp-meeting', -1, 0, 6)" class="tactile-btn" style="width: 26px; height: 26px; padding: 0; display: flex; align-items: center; justify-content: center; font-weight: 900;">-</button>
                                    <input type="text" id="ai-inp-meeting" value="1" readonly style="width: 32px; text-align: center; background: none; border: none; font-weight: 800; color: var(--text-main); font-size: 13px;">
                                    <button type="button" onclick="changeAiCounter('ai-inp-meeting', 1, 0, 6)" class="tactile-btn" style="width: 26px; height: 26px; padding: 0; display: flex; align-items: center; justify-content: center; font-weight: 900;">+</button>
                                </div>
                            </div>
                        </div>

                        <!-- Team Offices & Desks -->
                        <div style="background: var(--bg-surface); border: 1px solid var(--border-card); border-radius: var(--radius-md); padding: 12px;">
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 6px;">
                                <div>
                                    <strong style="font-size: 12px; color: #3B82F6; display: block;">💼 {{ __('Team Offices') }}</strong>
                                    <span style="font-size: 10px; color: var(--text-dim);">{{ __('مكاتب عمل جماعية/فردية') }}</span>
                                </div>
                                <div style="display: flex; align-items: center; gap: 6px;">
                                    <button type="button" onclick="changeAiCounter('ai-inp-office', -1, 1, 8)" class="tactile-btn" style="width: 26px; height: 26px; padding: 0; display: flex; align-items: center; justify-content: center; font-weight: 900;">-</button>
                                    <input type="text" id="ai-inp-office" value="1" readonly style="width: 32px; text-align: center; background: none; border: none; font-weight: 800; color: var(--text-main); font-size: 13px;">
                                    <button type="button" onclick="changeAiCounter('ai-inp-office', 1, 1, 8)" class="tactile-btn" style="width: 26px; height: 26px; padding: 0; display: flex; align-items: center; justify-content: center; font-weight: 900;">+</button>
                                </div>
                            </div>
                            <div style="display: flex; justify-content: space-between; align-items: center; border-top: 1px dashed var(--border-card); padding-top: 6px; margin-top: 4px;">
                                <span style="font-size: 10px; color: var(--text-dim);">🖥️ {{ __('Desks per office') }}:</span>
                                <div style="display: flex; align-items: center; gap: 4px;">
                                    <button type="button" onclick="changeAiCounter('ai-inp-desks', -1, 1, 12)" class="tactile-btn" style="width: 22px; height: 22px; padding: 0; display: flex; align-items: center; justify-content: center; font-size: 10px;">-</button>
                                    <input type="text" id="ai-inp-desks" value="2" readonly style="width: 24px; text-align: center; background: none; border: none; font-weight: 800; color: var(--text-main); font-size: 11px;">
                                    <button type="button" onclick="changeAiCounter('ai-inp-desks', 1, 1, 12)" class="tactile-btn" style="width: 22px; height: 22px; padding: 0; display: flex; align-items: center; justify-content: center; font-size: 10px;">+</button>
                                </div>
                            </div>
                        </div>

                        <!-- Thinking & Focus Pods -->
                        <div style="background: var(--bg-surface); border: 1px solid var(--border-card); border-radius: var(--radius-md); padding: 12px;">
                            <div style="display: flex; justify-content: space-between; align-items: center;">
                                <div>
                                    <strong style="font-size: 12px; color: #06B6D4; display: block;">💡 {{ __('Thinking / Focus Pods') }}</strong>
                                    <span style="font-size: 10px; color: var(--text-dim);">{{ __('غرف التركيز والعصف الذهني') }}</span>
                                </div>
                                <div style="display: flex; align-items: center; gap: 6px;">
                                    <button type="button" onclick="changeAiCounter('ai-inp-thinking', -1, 0, 4)" class="tactile-btn" style="width: 26px; height: 26px; padding: 0; display: flex; align-items: center; justify-content: center; font-weight: 900;">-</button>
                                    <input type="text" id="ai-inp-thinking" value="0" readonly style="width: 32px; text-align: center; background: none; border: none; font-weight: 800; color: var(--text-main); font-size: 13px;">
                                    <button type="button" onclick="changeAiCounter('ai-inp-thinking', 1, 0, 4)" class="tactile-btn" style="width: 26px; height: 26px; padding: 0; display: flex; align-items: center; justify-content: center; font-weight: 900;">+</button>
                                </div>
                            </div>
                        </div>

                        <!-- Rest & Gaming Lounge -->
                        <div style="background: var(--bg-surface); border: 1px solid var(--border-card); border-radius: var(--radius-md); padding: 12px;">
                            <div style="display: flex; justify-content: space-between; align-items: center;">
                                <div>
                                    <strong style="font-size: 12px; color: #EC4899; display: block;">🛋️ {{ __('Rest & Gaming Lounge') }}</strong>
                                    <span style="font-size: 10px; color: var(--text-dim);">{{ __('صالة الاستراحة والترفيه') }}</span>
                                </div>
                                <div style="display: flex; align-items: center; gap: 6px;">
                                    <button type="button" onclick="changeAiCounter('ai-inp-rest', -1, 0, 3)" class="tactile-btn" style="width: 26px; height: 26px; padding: 0; display: flex; align-items: center; justify-content: center; font-weight: 900;">-</button>
                                    <input type="text" id="ai-inp-rest" value="0" readonly style="width: 32px; text-align: center; background: none; border: none; font-weight: 800; color: var(--text-main); font-size: 13px;">
                                    <button type="button" onclick="changeAiCounter('ai-inp-rest', 1, 0, 3)" class="tactile-btn" style="width: 26px; height: 26px; padding: 0; display: flex; align-items: center; justify-content: center; font-weight: 900;">+</button>
                                </div>
                            </div>
                        </div>

                        <!-- Presentation Theater / Auditorium -->
                        <div style="background: var(--bg-surface); border: 1px solid var(--border-card); border-radius: var(--radius-md); padding: 12px;">
                            <div style="display: flex; justify-content: space-between; align-items: center;">
                                <div>
                                    <strong style="font-size: 12px; color: #E11D48; display: block;">🎭 {{ __('Presentation Theater') }}</strong>
                                    <span style="font-size: 10px; color: var(--text-dim);">{{ __('مسرح وقاعة عروض ومؤتمرات') }}</span>
                                </div>
                                <div style="display: flex; align-items: center; gap: 6px;">
                                    <button type="button" onclick="changeAiCounter('ai-inp-theater', -1, 0, 2)" class="tactile-btn" style="width: 26px; height: 26px; padding: 0; display: flex; align-items: center; justify-content: center; font-weight: 900;">-</button>
                                    <input type="text" id="ai-inp-theater" value="0" readonly style="width: 32px; text-align: center; background: none; border: none; font-weight: 800; color: var(--text-main); font-size: 13px;">
                                    <button type="button" onclick="changeAiCounter('ai-inp-theater', 1, 0, 2)" class="tactile-btn" style="width: 26px; height: 26px; padding: 0; display: flex; align-items: center; justify-content: center; font-weight: 900;">+</button>
                                </div>
                            </div>
                        </div>

                        <!-- Default Amenities Card -->
                        <div style="background: rgba(16, 185, 129, 0.08); border: 1px dashed rgba(52, 211, 153, 0.35); border-radius: var(--radius-md); padding: 12px; display: flex; flex-direction: column; justify-content: center;">
                            <strong style="font-size: 11px; color: #34D399; display: flex; align-items: center; gap: 6px;">
                                <span>☕</span> {{ __('Coffee Corner & Reception') }}
                            </strong>
                            <span style="font-size: 10px; color: var(--text-dim); margin-top: 2px;">
                                ✓ {{ __('Always included automatically in every floorplan') }}
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Live Quota Warning Box -->
                <div id="ai-quota-warning-box" style="display: none; background: rgba(217, 107, 95, 0.15); border: 1px solid rgba(217, 107, 95, 0.35); border-radius: 10px; padding: 12px 16px; font-size: 12px; color: #D96B5F; font-weight: 700;"></div>

                <!-- Action Buttons -->
                <div style="display: flex; justify-content: space-between; align-items: center; padding-top: 8px; border-top: 1px solid var(--border-card);">
                    <button type="button" onclick="closeAiGeneratorModal()" class="tactile-btn" style="padding: 10px 20px; font-size: 13px;">
                        {{ __('Cancel') }}
                    </button>
                    <button type="button" onclick="generateAiOfficeOnCanvas()" id="btn-ai-submit-generate" class="tactile-btn btn-primary" style="padding: 12px 28px; font-size: 14px; font-weight: 900; display: flex; align-items: center; gap: 8px; box-shadow: 0 4px 16px rgba(16, 185, 129, 0.35);">
                        <span>✨</span> {{ __('Generate Office with AI (توليد الخريطة بالذكاء الاصطناعي)') }}
                    </button>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
