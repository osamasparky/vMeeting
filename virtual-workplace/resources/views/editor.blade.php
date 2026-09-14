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
            width: 380px;
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
            margin-inline-end: -380px;
        }
        [dir="rtl"] .customizer-drawer.collapsed {
            transform: translateX(-100%);
            margin-inline-end: -380px;
        }

        .drawer-header {
            padding: 14px 18px;
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
            margin: 10px 14px;
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
            padding: 0 14px 20px 14px;
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        /* ── Search & Category Filter Navigation ── */
        .search-box-wrapper {
            position: relative;
            display: flex;
            align-items: center;
        }
        .search-box {
            width: 100%;
            background: var(--bg-input);
            border: 1px solid var(--border-panel);
            border-radius: 10px;
            padding: 9px 36px 9px 12px;
            color: var(--text-main);
            font-size: 12px;
            font-weight: 600;
            outline: none;
            transition: border-color 0.2s, box-shadow 0.2s;
        }
        [dir="rtl"] .search-box {
            padding: 9px 12px 9px 36px;
        }
        .search-box:focus {
            border-color: var(--brand-primary);
            box-shadow: 0 0 10px rgba(16, 185, 129, 0.2);
        }
        .search-clear-btn {
            position: absolute;
            inset-inline-end: 10px;
            background: none;
            border: none;
            color: var(--text-muted);
            cursor: pointer;
            font-size: 13px;
            display: none;
            padding: 2px 4px;
        }
        .search-clear-btn:hover {
            color: var(--text-main);
        }

        .category-filter-bar {
            display: flex;
            gap: 6px;
            overflow-x: auto;
            padding: 2px 2px 6px 2px;
            scrollbar-width: thin;
            scrollbar-color: rgba(52, 211, 153, 0.3) transparent;
        }
        .category-filter-bar::-webkit-scrollbar {
            height: 3px;
        }
        .category-filter-bar::-webkit-scrollbar-thumb {
            background: rgba(52, 211, 153, 0.3);
            border-radius: 3px;
        }
        .cat-pill {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 5px 10px;
            background: var(--bg-input);
            border: 1px solid var(--border-card);
            border-radius: 18px;
            color: var(--text-muted);
            font-size: 11px;
            font-weight: 800;
            white-space: nowrap;
            cursor: pointer;
            transition: all 0.18s cubic-bezier(0.4, 0, 0.2, 1);
            user-select: none;
            flex-shrink: 0;
        }
        .cat-pill:hover {
            border-color: var(--brand-primary);
            color: var(--text-main);
            background: rgba(16, 185, 129, 0.12);
            transform: translateY(-1px);
        }
        .cat-pill.active {
            background: linear-gradient(135deg, rgba(16, 185, 129, 0.25), rgba(5, 150, 105, 0.25));
            border-color: var(--brand-primary);
            color: #6EE7B7;
            box-shadow: 0 2px 8px rgba(16, 185, 129, 0.25);
        }
        .cat-pill-count {
            font-size: 9px;
            padding: 1px 5px;
            border-radius: 8px;
            background: rgba(0, 0, 0, 0.4);
            color: #A7F3D0;
        }

        /* ── Furniture Category Accordions & 2-Column Cards ── */
        .category-group {
            background: var(--bg-input);
            border: 1px solid var(--border-card);
            border-radius: 12px;
            overflow: hidden;
            transition: border-color 0.2s;
        }
        .category-group:hover {
            border-color: rgba(52, 211, 153, 0.3);
        }
        .category-title-bar {
            padding: 9px 12px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            font-size: 12px;
            font-weight: 800;
            color: var(--text-main);
            cursor: pointer;
            background: rgba(255, 255, 255, 0.02);
            transition: background 0.15s;
        }
        .category-title-bar:hover {
            background: rgba(16, 185, 129, 0.08);
        }
        .cat-chevron {
            font-size: 11px;
            color: var(--text-muted);
            transition: transform 0.2s;
        }

        .furniture-grid {
            padding: 8px;
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 8px;
            max-height: 480px;
            overflow-y: auto;
        }
        .furniture-grid::-webkit-scrollbar {
            width: 4px;
        }
        .furniture-grid::-webkit-scrollbar-thumb {
            background: rgba(52, 211, 153, 0.25);
            border-radius: 4px;
        }

        .furn-card {
            background: var(--bg-card);
            border: 1px solid var(--border-card);
            border-radius: 10px;
            padding: 8px 6px;
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
            cursor: pointer;
            gap: 5px;
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: hidden;
        }
        .furn-card:hover {
            border-color: var(--brand-primary);
            background: rgba(16, 185, 129, 0.12);
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(0, 0, 0, 0.4), 0 0 10px rgba(16, 185, 129, 0.2);
        }
        .furn-card.active {
            border-color: #10B981;
            background: rgba(16, 185, 129, 0.22);
            box-shadow: 0 0 14px rgba(16, 185, 129, 0.4);
        }

        .furn-card-top-badges {
            width: 100%;
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 9px;
            font-weight: 800;
            padding: 0 2px;
        }
        .furn-dim-badge {
            background: rgba(0, 0, 0, 0.5);
            border: 1px solid rgba(255, 255, 255, 0.1);
            color: #94A3B8;
            padding: 1px 5px;
            border-radius: 4px;
        }
        .furn-type-badge {
            background: rgba(16, 185, 129, 0.2);
            color: #6EE7B7;
            padding: 1px 5px;
            border-radius: 4px;
        }

        .furn-icon {
            width: 60px;
            height: 60px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 8px;
            background: radial-gradient(circle at center, rgba(24, 48, 36, 0.9) 0%, rgba(10, 22, 16, 0.95) 100%);
            border: 1px solid rgba(255, 255, 255, 0.06);
            overflow: hidden;
            position: relative;
            padding: 3px;
        }
        .furn-icon img {
            max-width: 100%;
            max-height: 100%;
            object-fit: contain;
            filter: drop-shadow(0 3px 6px rgba(0,0,0,0.5));
            transition: transform 0.2s ease;
        }
        .furn-card:hover .furn-icon img {
            transform: scale(1.1);
        }
        .furn-label {
            font-size: 11px;
            font-weight: 800;
            color: var(--text-main);
            line-height: 1.25;
            max-width: 100%;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
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
                    
                    <!-- Search Box with Clear Button -->
                    <div class="search-box-wrapper">
                        <input type="text" id="furniture-search-input" class="search-box" placeholder="🔍 {{ __('Search 3D furniture, desks, rugs, plants...') }}" oninput="filterFurniture(this.value)">
                        <button type="button" id="search-clear-btn" class="search-clear-btn" onclick="clearFurnitureSearch()">✕</button>
                    </div>

                    <!-- Catalog Quick Stats & Expand/Collapse Toggle -->
                    <div style="display: flex; justify-content: space-between; align-items: center; font-size: 11px; color: var(--text-muted); padding: 0 4px;">
                        @php
                            $totalCatalogCount = $furnitureCategories->sum(function($c) { return $c->items->count(); }) + 12;
                        @endphp
                        <span id="catalog-count-label" style="font-weight: 700; color: #A7F3D0;">✨ {{ $totalCatalogCount }} {{ __('Items Available') }}</span>
                        <button type="button" onclick="expandAllCategories()" style="background:none; border:none; color:var(--brand-primary); font-size:11px; font-weight:800; cursor:pointer; text-decoration: underline;">
                            {{ __('Toggle All') }}
                        </button>
                    </div>

                    <!-- Modern Category Filter Horizontal Bar -->
                    <div class="category-filter-bar">
                        <button type="button" class="cat-pill active" onclick="filterByCategory('all')">
                            <span>🌟</span>
                            <span>{{ __('All') }}</span>
                            <span class="cat-pill-count">{{ $totalCatalogCount }}</span>
                        </button>
                        <button type="button" class="cat-pill" onclick="filterByCategory('blueprint')">
                            <span>📐</span>
                            <span>{{ __('Blueprint') }}</span>
                            <span class="cat-pill-count">12</span>
                        </button>
                        @foreach($furnitureCategories as $cat)
                            @php
                                $shortName = trim(preg_replace('/\s*\(.*?\)\s*/', '', $cat->name));
                            @endphp
                            <button type="button" class="cat-pill" onclick="filterByCategory('{{ $cat->slug }}')" title="{{ $cat->name }}">
                                <span>{{ $cat->icon }}</span>
                                <span>{{ $shortName }}</span>
                                <span class="cat-pill-count">{{ $cat->items->count() }}</span>
                            </button>
                        @endforeach
                    </div>

                    <!-- 1. Blueprint Suite Assets -->
                    <div class="category-group" id="cat-blueprint">
                        <div class="category-title-bar" onclick="toggleCategoryGroup('cat-blueprint')">
                            <div style="display: flex; align-items: center; gap: 8px;">
                                <span style="font-size: 15px;">📐</span>
                                <span>{{ __('Isometric Blueprint Objects') }}</span>
                                <span class="cat-pill-count">12</span>
                            </div>
                            <span class="cat-chevron" id="chevron-cat-blueprint">▾</span>
                        </div>
                        <div class="furniture-grid">
                            <div class="furn-card" data-name="living plant wall botanical" onclick="selectFurnitureItem('living_wall', '#2D6A4F', null, 5, 2, true, 'none', null, 3, '{{ __('Living Plant Wall') }}')">
                                <div class="furn-card-top-badges">
                                    <span class="furn-dim-badge">5×2</span>
                                    <span class="furn-type-badge">🌿 {{ __('Plant') }}</span>
                                </div>
                                <div class="furn-icon"><span style="font-size: 30px;">🌿</span></div>
                                <div class="furn-label" title="{{ __('Living Plant Wall') }}">{{ __('Living Plant Wall') }}</div>
                            </div>

                            <div class="furn-card" data-name="oak boardroom table conference" onclick="selectFurnitureItem('conference_table', '#D8B589', null, 8, 3, true, 'sit', null, 2, '{{ __('Oak Boardroom Table') }}')">
                                <div class="furn-card-top-badges">
                                    <span class="furn-dim-badge">8×3</span>
                                    <span class="furn-type-badge">🤝 {{ __('Table') }}</span>
                                </div>
                                <div class="furn-icon"><span style="font-size: 30px;">🤝</span></div>
                                <div class="furn-label" title="{{ __('Oak Boardroom Table') }}">{{ __('Oak Boardroom Table') }}</div>
                            </div>

                            <div class="furn-card" data-name="white executive chair seating" onclick="selectFurnitureItem('chair_white', '#FFFFFF', null, 1, 1, false, 'sit', null, 1, '{{ __('White Executive Chair') }}')">
                                <div class="furn-card-top-badges">
                                    <span class="furn-dim-badge">1×1</span>
                                    <span class="furn-type-badge">🪑 {{ __('Sit') }}</span>
                                </div>
                                <div class="furn-icon"><span style="font-size: 30px;">🪑</span></div>
                                <div class="furn-label" title="{{ __('White Executive Chair') }}">{{ __('White Executive Chair') }}</div>
                            </div>

                            <div class="furn-card" data-name="focus pod desk workstation" onclick="selectFurnitureItem('pod_workstation', '#D8B589', null, 3, 2, true, 'sit', null, 2, '{{ __('Focus Pod Desk') }}')">
                                <div class="furn-card-top-badges">
                                    <span class="furn-dim-badge">3×2</span>
                                    <span class="furn-type-badge">🎧 {{ __('Desk') }}</span>
                                </div>
                                <div class="furn-icon"><span style="font-size: 30px;">🎧</span></div>
                                <div class="furn-label" title="{{ __('Focus Pod Desk') }}">{{ __('Focus Pod Desk') }}</div>
                            </div>

                            <div class="furn-card" data-name="wood feature wall partition" onclick="selectFurnitureItem('wood_panel_wall', '#C49A6C', null, 7, 1, true, 'none', null, 3, '{{ __('Wood Feature Wall') }}')">
                                <div class="furn-card-top-badges">
                                    <span class="furn-dim-badge">7×1</span>
                                    <span class="furn-type-badge">🪵 {{ __('Wall') }}</span>
                                </div>
                                <div class="furn-icon"><span style="font-size: 30px;">🪵</span></div>
                                <div class="furn-label" title="{{ __('Wood Feature Wall') }}">{{ __('Wood Feature Wall') }}</div>
                            </div>

                            <div class="furn-card" data-name="wooden staircase stairs" onclick="selectFurnitureItem('stairs_wood', '#C49A6C', null, 3, 4, true, 'none', null, 2, '{{ __('Wooden Staircase') }}')">
                                <div class="furn-card-top-badges">
                                    <span class="furn-dim-badge">3×4</span>
                                    <span class="furn-type-badge">🪜 {{ __('Stairs') }}</span>
                                </div>
                                <div class="furn-icon"><span style="font-size: 30px;">🪜</span></div>
                                <div class="furn-label" title="{{ __('Wooden Staircase') }}">{{ __('Wooden Staircase') }}</div>
                            </div>

                            <div class="furn-card" data-name="tech 3d workbench desk" onclick="selectFurnitureItem('tech_workbench', '#D8B589', null, 4, 2, true, 'none', null, 2, '{{ __('Tech 3D Workbench') }}')">
                                <div class="furn-card-top-badges">
                                    <span class="furn-dim-badge">4×2</span>
                                    <span class="furn-type-badge">🛠️ {{ __('Bench') }}</span>
                                </div>
                                <div class="furn-icon"><span style="font-size: 30px;">🛠️</span></div>
                                <div class="furn-label" title="{{ __('Tech 3D Workbench') }}">{{ __('Tech 3D Workbench') }}</div>
                            </div>

                            <div class="furn-card" data-name="reception counter desk" onclick="selectFurnitureItem('reception_counter', '#F4EFE6', null, 4, 2, true, 'drink', null, 2, '{{ __('Reception Desk') }}')">
                                <div class="furn-card-top-badges">
                                    <span class="furn-dim-badge">4×2</span>
                                    <span class="furn-type-badge">🛎️ {{ __('Lobby') }}</span>
                                </div>
                                <div class="furn-icon"><span style="font-size: 30px;">🛎️</span></div>
                                <div class="furn-label" title="{{ __('Reception Desk') }}">{{ __('Reception Desk') }}</div>
                            </div>

                            <div class="furn-card" data-name="cream 3 seater sofa lounge" onclick="selectFurnitureItem('sofa_cream', '#F4EFE6', null, 3, 2, true, 'sit', null, 1, '{{ __('Cream 3-Seater Sofa') }}')">
                                <div class="furn-card-top-badges">
                                    <span class="furn-dim-badge">3×2</span>
                                    <span class="furn-type-badge">🛋️ {{ __('Sofa') }}</span>
                                </div>
                                <div class="furn-icon"><span style="font-size: 30px;">🛋️</span></div>
                                <div class="furn-label" title="{{ __('Cream 3-Seater Sofa') }}">{{ __('Cream 3-Seater Sofa') }}</div>
                            </div>

                            <div class="furn-card" data-name="sage armchair single lounge" onclick="selectFurnitureItem('armchair_sage', '#8BA888', null, 2, 2, true, 'sit', null, 1, '{{ __('Sage Armchair') }}')">
                                <div class="furn-card-top-badges">
                                    <span class="furn-dim-badge">2×2</span>
                                    <span class="furn-type-badge">🛋️ {{ __('Chair') }}</span>
                                </div>
                                <div class="furn-icon"><span style="font-size: 30px;">🛋️</span></div>
                                <div class="furn-label" title="{{ __('Sage Armchair') }}">{{ __('Sage Armchair') }}</div>
                            </div>

                            <div class="furn-card" data-name="oak coffee table lounge" onclick="selectFurnitureItem('coffee_table_oak', '#D8B589', null, 2, 1, true, 'drink', null, 2, '{{ __('Oak Coffee Table') }}')">
                                <div class="furn-card-top-badges">
                                    <span class="furn-dim-badge">2×1</span>
                                    <span class="furn-type-badge">☕ {{ __('Table') }}</span>
                                </div>
                                <div class="furn-icon"><span style="font-size: 30px;">☕</span></div>
                                <div class="furn-label" title="{{ __('Oak Coffee Table') }}">{{ __('Oak Coffee Table') }}</div>
                            </div>

                            <div class="furn-card" data-name="strategy whiteboard presentation" onclick="selectFurnitureItem('whiteboard_strategy', '#FFFFFF', null, 4, 1, true, 'whiteboard', null, 3, '{{ __('Strategy Board') }}')">
                                <div class="furn-card-top-badges">
                                    <span class="furn-dim-badge">4×1</span>
                                    <span class="furn-type-badge">📋 {{ __('Board') }}</span>
                                </div>
                                <div class="furn-icon"><span style="font-size: 30px;">📋</span></div>
                                <div class="furn-label" title="{{ __('Strategy Board') }}">{{ __('Strategy Board') }}</div>
                            </div>
                        </div>
                    </div>

                    <!-- 2. Dynamic Catalog Categories (936+ Items) -->
                    @foreach($furnitureCategories as $cat)
                    @php
                        $cleanCatName = trim(preg_replace('/\s*\(.*?\)\s*/', '', $cat->name));
                        $itemsList = $cat->items;
                        if ($cat->slug === 'custom') {
                            $allowedCustomSlugs = ['branding', 'sticky_note', 'custom_link', 'custom_image'];
                            $itemsList = $itemsList->whereIn('slug', $allowedCustomSlugs);
                        }
                    @endphp
                    @if($itemsList->count() > 0)
                    <div class="category-group" id="cat-{{ $cat->slug }}">
                        <div class="category-title-bar" onclick="toggleCategoryGroup('cat-{{ $cat->slug }}')">
                            <div style="display: flex; align-items: center; gap: 8px;">
                                <span style="font-size: 15px;">{{ $cat->icon }}</span>
                                <span>{{ $cleanCatName }}</span>
                                <span class="cat-pill-count">{{ $itemsList->count() }}</span>
                            </div>
                            <span class="cat-chevron" id="chevron-cat-{{ $cat->slug }}">▾</span>
                        </div>
                        <div class="furniture-grid" style="display: none;">
                            @foreach($itemsList as $item)
                                @php
                                    $itemWidth = $item->width ?? 1;
                                    $itemHeight = $item->height ?? 1;
                                    $itemElev = $item->elevation ?? ($cat->slug === 'rugs' ? 0 : 1);
                                    $itemImg = $item->image_url;
                                    if ($item->slug === 'branding' && !empty($organization->logo_url)) {
                                        $itemImg = asset($organization->logo_url);
                                    }
                                    $itemTypeTag = ($cat->slug === 'rugs' || $itemElev === 0) ? '🧶 ' . __('Rug') : ($item->interaction_type !== 'none' ? '⚡ ' . ucfirst($item->interaction_type) : "{$itemWidth}×{$itemHeight}");
                                    if ($item->slug === 'branding') $itemTypeTag = '🏢 ' . __('Logo');
                                    if ($item->slug === 'sticky_note') $itemTypeTag = '📝 ' . __('Note');
                                    if ($item->slug === 'custom_link') $itemTypeTag = '🔗 ' . __('URL');
                                    if ($item->slug === 'custom_image') $itemTypeTag = '🖼️ ' . __('Image');
                                @endphp
                                <div class="furn-card" 
                                     data-name="{{ strtolower($item->name . ' ' . $cleanCatName . ' ' . $cat->slug) }}"
                                     onclick="selectFurnitureItem('{{ $item->slug }}', '{{ $item->colors[0] ?? '#3b82f6' }}', '{{ $itemImg }}', {{ $itemWidth }}, {{ $itemHeight }}, {{ $item->collision ? 'true' : 'false' }}, '{{ $item->interaction_type }}', {{ json_encode($item->interaction_config) }}, {{ $itemElev }}, '{{ addslashes($item->name) }}')">
                                    <div class="furn-card-top-badges">
                                        <span class="furn-dim-badge">{{ $itemWidth }}×{{ $itemHeight }}</span>
                                        <span class="furn-type-badge">{{ $itemTypeTag }}</span>
                                    </div>
                                    <div class="furn-icon">
                                        @if($itemImg)
                                            <img src="{{ $itemImg }}" alt="{{ $item->name }}" loading="lazy" style="max-height: 48px; max-width: 48px; object-fit: contain;">
                                        @else
                                            <span style="font-size: 26px;">{{ $item->icon }}</span>
                                        @endif
                                    </div>
                                    <div class="furn-label" title="{{ $item->name }}">{{ $item->name }}</div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                    @endif
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
                            <div>
                                <label class="prop-label">{{ __('Layer & Elevation (الطبقة والارتفاع)') }}</label>
                                <select class="prop-input" id="prop-elevation" onchange="updateSelectedProp('elevation', parseInt(this.value))">
                                    <option value="0">🧶 {{ __('Ground / Rug (أرضية / سجاد)') }}</option>
                                    <option value="1">🪑 {{ __('Default Furniture (أثاث عادي)') }}</option>
                                    <option value="2">💼 {{ __('Desk / Table Surface (سطح مكتب)') }}</option>
                                    <option value="3">🌿 {{ __('Tall Plant / Partition (حاجز / نبتة طويلة)') }}</option>
                                    <option value="5">💡 {{ __('Ceiling / Overhead (إضاءة وسقف)') }}</option>
                                </select>
                            </div>
                            <div>
                                <label class="prop-label">{{ __('Interaction (نوع التفاعل)') }}</label>
                                <div id="prop-interaction-badge" style="font-size: 11px; font-weight: 700; color: var(--brand-primary); padding: 4px 8px; background: rgba(16,185,129,0.1); border-radius: 6px; display: inline-block;">NONE</div>
                            </div>

                            <!-- 🏢 1. Company Logo / Branding Inspector Box -->
                            <div id="inspector-branding-box" style="display: none; background: rgba(16, 185, 129, 0.08); border: 1px solid rgba(52, 211, 153, 0.25); border-radius: 10px; padding: 12px; flex-direction: column; gap: 8px; margin-top: 4px;">
                                <div style="display: flex; align-items: center; justify-content: space-between;">
                                    <span style="font-size: 12px; font-weight: 800; color: #34D399;">🏢 {{ __('Company Logo (شعار الشركة)') }}</span>
                                    <span class="furn-type-badge" style="background: rgba(16, 185, 129, 0.2); color: #6EE7B7;">Logo</span>
                                </div>
                                <div style="font-size: 11px; color: var(--text-muted); line-height: 1.4;">
                                    {{ __('Displays your company logo on the workplace floor or reception.') }}
                                </div>
                                @if($organization->logo_url)
                                <button type="button" class="act-btn act-btn-emerald" onclick="applyOrgLogoToSelected()" style="justify-content: center; padding: 8px; font-size: 11px; width: 100%;">
                                    <span>🏢</span> <span>{{ __('Use Official Logo from Settings') }}</span>
                                </button>
                                @endif
                                <div>
                                    <label class="prop-label">{{ __('Custom Logo URL') }}</label>
                                    <input type="text" class="prop-input" id="prop-branding-url" placeholder="https://.../logo.png" oninput="updateSelectedLogoUrl(this.value)">
                                </div>
                                <div>
                                    <input type="file" id="branding-upload-input" accept="image/*" style="display: none;" onchange="uploadObjectImageDirectly(this, 'branding')">
                                    <button type="button" class="tool-btn" onclick="document.getElementById('branding-upload-input').click()" style="width: 100%; justify-content: center; padding: 7px; font-size: 11px;">
                                        📤 {{ __('Upload Custom Logo File') }}
                                    </button>
                                </div>
                            </div>

                            <!-- 📝 2. Sticky Note Inspector Box -->
                            <div id="inspector-stickynote-box" style="display: none; background: rgba(245, 158, 11, 0.08); border: 1px solid rgba(245, 158, 11, 0.3); border-radius: 10px; padding: 12px; flex-direction: column; gap: 8px; margin-top: 4px;">
                                <div style="display: flex; align-items: center; justify-content: space-between;">
                                    <span style="font-size: 12px; font-weight: 800; color: #FBBF24;">📝 {{ __('Sticky Note (ملاحظة لاصقة)') }}</span>
                                    <span class="furn-type-badge" style="background: rgba(245, 158, 11, 0.2); color: #FCD34D;">Note</span>
                                </div>
                                <div>
                                    <label class="prop-label">{{ __('Note Text (نص الملاحظة التفاعلية)') }}</label>
                                    <textarea class="prop-input" id="prop-stickynote-text" rows="3" placeholder="{{ __('Write your note or announcement here...') }}" oninput="updateSelectedStickyText(this.value)" style="resize: vertical; min-height: 65px;"></textarea>
                                </div>
                                <div>
                                    <label class="prop-label">{{ __('Color Theme (لون الملاحظة)') }}</label>
                                    <div style="display: flex; gap: 6px;">
                                        <button type="button" class="rot-btn" style="flex: 1; background: rgba(245, 158, 11, 0.2); border-color: #F59E0B; color: #FCD34D;" onclick="setStickyColor('yellow')" title="Yellow">🟡</button>
                                        <button type="button" class="rot-btn" style="flex: 1; background: rgba(234, 88, 12, 0.2); border-color: #EA580C; color: #FDBA74;" onclick="setStickyColor('orange')" title="Orange">🟠</button>
                                        <button type="button" class="rot-btn" style="flex: 1; background: rgba(168, 85, 247, 0.2); border-color: #A855F7; color: #D8B4FE;" onclick="setStickyColor('purple')" title="Purple">🌸</button>
                                        <button type="button" class="rot-btn" style="flex: 1; background: rgba(16, 185, 129, 0.2); border-color: #10B981; color: #6EE7B7;" onclick="setStickyColor('green')" title="Green">🟢</button>
                                        <button type="button" class="rot-btn" style="flex: 1; background: rgba(59, 130, 246, 0.2); border-color: #3B82F6; color: #93C5FD;" onclick="setStickyColor('blue')" title="Blue">🔵</button>
                                    </div>
                                </div>
                            </div>

                            <!-- 🔗 3. Custom Link Inspector Box -->
                            <div id="inspector-link-box" style="display: none; background: rgba(59, 130, 246, 0.08); border: 1px solid rgba(59, 130, 246, 0.3); border-radius: 10px; padding: 12px; flex-direction: column; gap: 8px; margin-top: 4px;">
                                <div style="display: flex; align-items: center; justify-content: space-between;">
                                    <span style="font-size: 12px; font-weight: 800; color: #60A5FA;">🔗 {{ __('Interactive Web Link (رابط مخصص)') }}</span>
                                    <span class="furn-type-badge" style="background: rgba(59, 130, 246, 0.2); color: #93C5FD;">URL</span>
                                </div>
                                <div>
                                    <label class="prop-label">{{ __('Target URL (رابط الموقع أو المستند)') }}</label>
                                    <input type="url" class="prop-input" id="prop-link-url" placeholder="https://example.com/doc" oninput="updateSelectedLinkProp('url', this.value)">
                                </div>
                                <div>
                                    <label class="prop-label">{{ __('Link Title / Label (عنوان الرابط)') }}</label>
                                    <input type="text" class="prop-input" id="prop-link-title" placeholder="{{ __('e.g. Project Notion Board') }}" oninput="updateSelectedLinkProp('title', this.value)">
                                </div>
                                <div style="display: flex; align-items: center; justify-content: space-between; margin-top: 4px;">
                                    <span style="font-size: 11px; color: var(--text-secondary);">{{ __('Open in New Browser Tab (فتح بنافذة جديدة)') }}</span>
                                    <input type="checkbox" id="prop-link-newtab" checked onchange="updateSelectedLinkProp('openInNewTab', this.checked)" style="accent-color: var(--brand-primary); cursor: pointer; width: 16px; height: 16px;">
                                </div>
                            </div>

                            <!-- 🖼️ 4. Custom Image Inspector Box -->
                            <div id="inspector-customimage-box" style="display: none; background: rgba(139, 92, 246, 0.08); border: 1px solid rgba(139, 92, 246, 0.3); border-radius: 10px; padding: 12px; flex-direction: column; gap: 8px; margin-top: 4px;">
                                <div style="display: flex; align-items: center; justify-content: space-between;">
                                    <span style="font-size: 12px; font-weight: 800; color: #C084FC;">🖼️ {{ __('Custom Image / Banner (صورة مخصصة)') }}</span>
                                    <span class="furn-type-badge" style="background: rgba(139, 92, 246, 0.2); color: #D8B4FE;">Image</span>
                                </div>
                                <div>
                                    <label class="prop-label">{{ __('Image URL (رابط الصورة)') }}</label>
                                    <input type="url" class="prop-input" id="prop-customimage-url" placeholder="https://.../banner.png" oninput="updateSelectedCustomImageUrl(this.value)">
                                </div>
                                <div>
                                    <input type="file" id="customimage-upload-input" accept="image/*" style="display: none;" onchange="uploadObjectImageDirectly(this, 'custom_image')">
                                    <button type="button" class="tool-btn" onclick="document.getElementById('customimage-upload-input').click()" style="width: 100%; justify-content: center; padding: 7px; font-size: 11px;">
                                        📤 {{ __('Upload Image File (رفع صورة من جهازك)') }}
                                    </button>
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
                                <label class="prop-label">{{ __('Door Placement (موقع باب الغرفة)') }}</label>
                                <select class="prop-input" id="prop-room-door-side" onchange="updateRoomProp('doorSide', this.value)">
                                    <option value="auto">🌟 {{ __('Auto Corridor (تلقائي نحو الممر المفتوح)') }}</option>
                                    <option value="bottom">⬇️ {{ __('Bottom Wall (الجدار السفلي)') }}</option>
                                    <option value="top">⬆️ {{ __('Top Wall (الجدار العلوي)') }}</option>
                                    <option value="left">⬅️ {{ __('Left Wall (الجدار الأيسر)') }}</option>
                                    <option value="right">➡️ {{ __('Right Wall (الجدار الأيمن)') }}</option>
                                </select>
                            </div>

                            <div>
                                <label class="prop-label">{{ __('Door Position on Wall (موضع الباب على الجدار)') }}</label>
                                <div style="display: flex; align-items: center; gap: 8px;">
                                    <input type="range" class="prop-input" id="prop-room-door-offset" min="15" max="85" value="50" step="5" oninput="updateRoomProp('doorOffset', this.value / 100); document.getElementById('door-offset-val').textContent = this.value + '%';">
                                    <span id="door-offset-val" style="font-size: 11px; font-weight: 800; color: var(--brand-primary); min-width: 32px;">50%</span>
                                </div>
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
        const COMPANY_LOGO_URL = @json($organization->logo_url ? asset($organization->logo_url) : '');
        const COMPANY_NAME = @json($organization->name);

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
        let objects = (MAP_DATA.objects || []).map(o => ({
            ...o,
            image_url: o.image_url || (o.interaction_config && o.interaction_config.image_url) || null,
            is_custom: typeof o.is_custom !== 'undefined' ? o.is_custom : (o.interaction_config && typeof o.interaction_config.is_custom !== 'undefined' ? o.interaction_config.is_custom : true),
            width: o.width || (o.size ? o.size.width : (o.interaction_config && o.interaction_config.width ? o.interaction_config.width : 1)),
            height: o.height || (o.size ? o.size.height : (o.interaction_config && o.interaction_config.height ? o.interaction_config.height : 1)),
            collision: typeof o.collision === 'boolean' ? o.collision : true
        }));

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
            : null;
        let blueprintLoaded = false;
        if (initialBgUrl) {
            BLUEPRINT_IMAGE.src = initialBgUrl + (initialBgUrl.includes('?') ? '&' : '?') + 'v=' + Date.now();
            BLUEPRINT_IMAGE.onload = () => {
                blueprintLoaded = true;
                draw();
            };
            BLUEPRINT_IMAGE.onerror = () => {
                blueprintLoaded = false;
                draw();
            };
        }

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
            const chevron = document.querySelector(`#chevron-${id}`);
            if (grid) {
                const isHidden = (grid.style.display === 'none' || getComputedStyle(grid).display === 'none');
                grid.style.display = isHidden ? 'grid' : 'none';
                if (chevron) chevron.textContent = isHidden ? '▴' : '▾';
            }
        }

        function filterByCategory(slug) {
            document.querySelectorAll('.cat-pill').forEach(c => c.classList.remove('active'));
            if (window.event && window.event.currentTarget) window.event.currentTarget.classList.add('active');
            
            let visibleCount = 0;
            document.querySelectorAll('.category-group').forEach(acc => {
                const match = (slug === 'all' || acc.id === `cat-${slug}`);
                acc.style.display = match ? 'block' : 'none';
                const grid = acc.querySelector('.furniture-grid');
                const chevron = acc.querySelector('.cat-chevron');
                if (grid) {
                    if (match) {
                        grid.style.display = 'grid';
                        if (chevron) chevron.textContent = '▴';
                        visibleCount += grid.querySelectorAll('.furn-card').length;
                    } else {
                        grid.style.display = 'none';
                        if (chevron) chevron.textContent = '▾';
                    }
                }
            });
            const countLabel = document.getElementById('catalog-count-label');
            if (countLabel) countLabel.textContent = `✨ ${visibleCount} {{ __('Items shown') }}`;
        }

        function filterFurniture(q) {
            const term = (q || '').trim().toLowerCase();
            const clearBtn = document.getElementById('search-clear-btn');
            if (clearBtn) clearBtn.style.display = term ? 'block' : 'none';

            let visibleTotal = 0;
            document.querySelectorAll('.category-group').forEach(group => {
                let groupHasMatch = false;
                group.querySelectorAll('.furn-card').forEach(card => {
                    const name = card.getAttribute('data-name') || card.querySelector('.furn-label')?.textContent.toLowerCase() || '';
                    const match = !term || name.includes(term);
                    card.style.display = match ? 'flex' : 'none';
                    if (match) {
                        groupHasMatch = true;
                        visibleTotal++;
                    }
                });
                group.style.display = (groupHasMatch || !term) ? 'block' : 'none';
                const grid = group.querySelector('.furniture-grid');
                const chevron = group.querySelector('.cat-chevron');
                if (term) {
                    if (grid) grid.style.display = groupHasMatch ? 'grid' : 'none';
                    if (chevron) chevron.textContent = groupHasMatch ? '▴' : '▾';
                }
            });

            const countLabel = document.getElementById('catalog-count-label');
            if (countLabel) countLabel.textContent = term ? `🔍 ${visibleTotal} {{ __('Matches found') }}` : `✨ ${visibleTotal} {{ __('Items available') }}`;
        }

        function clearFurnitureSearch() {
            const inp = document.getElementById('furniture-search-input');
            if (inp) { inp.value = ''; filterFurniture(''); }
        }

        function expandAllCategories() {
            const grids = document.querySelectorAll('.furniture-grid');
            const anyClosed = Array.from(grids).some(g => g.style.display === 'none' || getComputedStyle(g).display === 'none');
            grids.forEach(g => g.style.display = anyClosed ? 'grid' : 'none');
            document.querySelectorAll('.cat-chevron').forEach(ch => ch.textContent = anyClosed ? '▴' : '▾');
        }

        function selectFurnitureItem(slug, color, imgUrl = null, w = 1, h = 1, col = true, interactionType = 'none', interactionConfig = null, elevation = 1, itemName = null) {
            setTool('object');
            currentObjectType = slug;
            currentObjectColor = color || '#3B82F6';
            currentObjectCustom = {
                name: itemName,
                imageUrl: imgUrl,
                width: parseInt(w) || 1,
                height: parseInt(h) || 1,
                collision: Boolean(col),
                interactionType: interactionType || 'none',
                interactionConfig: interactionConfig || null,
                elevation: (typeof elevation !== 'undefined' && elevation !== null) ? parseInt(elevation) : 1
            };
            document.querySelectorAll('.furn-card').forEach(el => el.classList.remove('active'));
            if (window.event && window.event.currentTarget) window.event.currentTarget.classList.add('active');
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
                let objImgUrl = currentObjectCustom?.imageUrl || null;
                let initConfig = currentObjectCustom?.interactionConfig ? JSON.parse(JSON.stringify(currentObjectCustom.interactionConfig)) : {};
                let iType = currentObjectCustom?.interactionType || 'none';

                if (currentObjectType === 'branding') {
                    iType = 'branding';
                    if (COMPANY_LOGO_URL) {
                        objImgUrl = COMPANY_LOGO_URL;
                    }
                    initConfig = {
                        behavior: { type: 'branding', data: { use_company_logo: true } },
                        use_company_logo: true,
                        image_url: objImgUrl
                    };
                } else if (currentObjectType === 'sticky_note') {
                    iType = 'stickyNote';
                    initConfig = {
                        behavior: { type: 'stickyNote', data: { text: '' } },
                        noteText: '',
                        color: 'yellow'
                    };
                } else if (currentObjectType === 'custom_link') {
                    iType = 'link';
                    initConfig = {
                        behavior: { type: 'link', data: { url: '', title: '', openInNewTab: true } },
                        url: '',
                        title: '',
                        openInNewTab: true
                    };
                } else if (currentObjectType === 'custom_image') {
                    iType = 'customImage';
                    initConfig = {
                        behavior: { type: 'customImage', data: { imageUrl: objImgUrl, openInNewTab: false } },
                        image_url: objImgUrl
                    };
                }

                const newObj = {
                    type: currentObjectType,
                    name: currentObjectCustom?.name || `${currentObjectType.replace(/_/g, ' ')} #${objects.length + 1}`,
                    position: { x: tileX, y: tileY, rotation: 0 },
                    color: currentObjectColor,
                    image_url: objImgUrl,
                    width: currentObjectCustom?.width || 1,
                    height: currentObjectCustom?.height || 1,
                    collision: currentObjectCustom ? currentObjectCustom.collision : true,
                    elevation: currentObjectCustom?.elevation || 1,
                    interaction_type: iType,
                    interaction_config: initConfig,
                    is_custom: true
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

                // Visual Door Indicator on Wall
                const doorSide = (r.bounds && r.bounds.doorSide && r.bounds.doorSide !== 'auto') ? r.bounds.doorSide : 'bottom';
                const doorOffset = (r.bounds && typeof r.bounds.doorOffset === 'number') ? r.bounds.doorOffset : 0.5;
                const dW = Math.min(42, (doorSide === 'top' || doorSide === 'bottom') ? rw * 0.45 : rh * 0.45);
                let dX = rx + rw * doorOffset, dY = ry + rh;
                if (doorSide === 'top') { dX = rx + rw * doorOffset; dY = ry; }
                else if (doorSide === 'left') { dX = rx; dY = ry + rh * doorOffset; }
                else if (doorSide === 'right') { dX = rx + rw; dY = ry + rh * doorOffset; }

                ctx.save();
                ctx.fillStyle = '#10B981';
                ctx.strokeStyle = '#FFFFFF';
                ctx.lineWidth = 1.5;
                if (doorSide === 'top' || doorSide === 'bottom') {
                    ctx.fillRect(dX - dW/2, dY - 3, dW, 6);
                    ctx.strokeRect(dX - dW/2, dY - 3, dW, 6);
                } else {
                    ctx.fillRect(dX - 3, dY - dW/2, 6, dW);
                    ctx.strokeRect(dX - 3, dY - dW/2, 6, dW);
                }
                ctx.restore();
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

            // 4. Draw Furniture Objects with Elevation Depth Sorting (Floor Rugs -> Furniture -> Surfaces -> Ceiling)
            const sortedObjects = [...objects].sort((a, b) => {
                const isRugA = (a.type && (a.type.includes('rug') || a.type.includes('carpet'))) || (a.name && (a.name.toLowerCase().includes('rug') || a.name.toLowerCase().includes('carpet') || a.name.includes('سجاد')));
                const isRugB = (b.type && (b.type.includes('rug') || b.type.includes('carpet'))) || (b.name && (b.name.toLowerCase().includes('rug') || b.name.toLowerCase().includes('carpet') || b.name.includes('سجاد')));
                const defaultElevA = isRugA ? 0 : 1;
                const defaultElevB = isRugB ? 0 : 1;
                const elevA = typeof a.elevation === 'number' ? a.elevation : (a.interaction_config?.elevation ?? defaultElevA);
                const elevB = typeof b.elevation === 'number' ? b.elevation : (b.interaction_config?.elevation ?? defaultElevB);
                if (elevA !== elevB) return elevA - elevB;
                const yA = (a.position ? a.position.y : 0);
                const yB = (b.position ? b.position.y : 0);
                return yA - yB;
            });

            sortedObjects.forEach(obj => {
                const isSelected = selectedItem && selectedItem.type === 'object' && selectedItem.item === obj;
                const ox = (obj.position ? obj.position.x : 0) * TILE_SIZE;
                const oy = (obj.position ? obj.position.y : 0) * TILE_SIZE;
                const objW = (obj.width || (obj.size ? obj.size.width : 1)) * TILE_SIZE;
                const objH = (obj.height || (obj.size ? obj.size.height : 1)) * TILE_SIZE;
                const imgUrl = obj.image_url || (obj.interaction_config && obj.interaction_config.image_url);

                // If map has blueprint artwork, seeded untextured placeholder collision items should not be painted as blue blocks
                if (hasBlueprint && !imgUrl && !obj.is_custom) {
                    if (isSelected) {
                        ctx.save();
                        ctx.strokeStyle = '#10B981';
                        ctx.lineWidth = 1.5;
                        ctx.setLineDash([4, 4]);
                        ctx.strokeRect(ox, oy, objW, objH);
                        ctx.restore();
                    }
                    return;
                }

                ctx.save();
                ctx.translate(ox + objW / 2, oy + objH / 2);
                const rot = (obj.position && typeof obj.position.rotation === 'number') ? obj.position.rotation : (obj.rotation || 0);
                if (rot) ctx.rotate((rot * Math.PI) / 180);

                if (imgUrl) {
                    if (!window._objImgCache) window._objImgCache = new Map();
                    let sprImg = window._objImgCache.get(imgUrl);
                    if (!sprImg) {
                        sprImg = new Image();
                        sprImg.src = imgUrl;
                        sprImg.onload = () => { if (typeof draw === 'function') draw(); };
                        sprImg.onerror = () => { sprImg._error = true; };
                        window._objImgCache.set(imgUrl, sprImg);
                    }
                    if (sprImg && sprImg.complete && sprImg.naturalWidth > 0) {
                        ctx.drawImage(sprImg, -objW / 2, -objH / 2, objW, objH);
                    } else if (isSelected) {
                        // Subtle emerald placeholder box only when actively selected
                        ctx.fillStyle = 'rgba(16, 185, 129, 0.15)';
                        if (ctx.roundRect) ctx.roundRect(-objW / 2, -objH / 2, objW, objH, 4);
                        else ctx.rect(-objW / 2, -objH / 2, objW, objH);
                        ctx.fill();
                    }
                } else if (obj.is_custom || obj.color) {
                    ctx.fillStyle = obj.color ? (obj.color.length === 7 ? obj.color + '99' : obj.color) : 'rgba(59, 130, 246, 0.35)';
                    if (ctx.roundRect) ctx.roundRect(-objW / 2, -objH / 2, objW, objH, 4);
                    else ctx.rect(-objW / 2, -objH / 2, objW, objH);
                    ctx.fill();
                    ctx.strokeStyle = 'rgba(255, 255, 255, 0.35)';
                    ctx.lineWidth = 1;
                    if (ctx.roundRect) ctx.roundRect(-objW / 2, -objH / 2, objW, objH, 4);
                    else ctx.rect(-objW / 2, -objH / 2, objW, objH);
                    ctx.stroke();
                }

                if (isSelected) {
                    ctx.strokeStyle = '#10B981';
                    ctx.lineWidth = 2;
                    ctx.setLineDash([4, 4]);
                    if (ctx.roundRect) ctx.roundRect(-objW / 2 - 2, -objH / 2 - 2, objW + 4, objH + 4, 4);
                    else ctx.rect(-objW / 2 - 2, -objH / 2 - 2, objW + 4, objH + 4);
                    ctx.stroke();
                    ctx.setLineDash([]);

                    // Corner Accent Grab Nodes
                    const hw = objW / 2 + 2;
                    const hh = objH / 2 + 2;
                    const grabPoints = [
                        { x: -hw, y: -hh }, { x: hw, y: -hh },
                        { x: hw, y: hh }, { x: -hw, y: hh }
                    ];
                    grabPoints.forEach(p => {
                        ctx.fillStyle = '#10B981';
                        ctx.beginPath();
                        ctx.arc(p.x, p.y, 3.5, 0, Math.PI * 2);
                        ctx.fill();
                        ctx.strokeStyle = '#FFFFFF';
                        ctx.lineWidth = 1.2;
                        ctx.stroke();
                    });
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
                const elevEl = document.getElementById('prop-elevation');
                if (elevEl) elevEl.value = item.elevation || 1;
                const badgeEl = document.getElementById('prop-interaction-badge');
                const iType = item.interaction_type || (item.interaction_config && item.interaction_config.behavior?.type) || (item.type === 'branding' ? 'branding' : (item.type === 'sticky_note' ? 'stickyNote' : (item.type === 'custom_link' ? 'link' : (item.type === 'custom_image' ? 'customImage' : 'none'))));
                if (badgeEl) {
                    badgeEl.textContent = iType.toUpperCase();
                }

                // ── Interactive Media Panels Toggle ──
                const brandingBox = document.getElementById('inspector-branding-box');
                const stickyBox = document.getElementById('inspector-stickynote-box');
                const linkBox = document.getElementById('inspector-link-box');
                const imgBox = document.getElementById('inspector-customimage-box');

                if (brandingBox) brandingBox.style.display = (item.type === 'branding' || iType === 'branding') ? 'flex' : 'none';
                if (stickyBox) stickyBox.style.display = (item.type === 'sticky_note' || iType === 'stickyNote' || iType === 'stickynote') ? 'flex' : 'none';
                if (linkBox) linkBox.style.display = (item.type === 'custom_link' || iType === 'link') ? 'flex' : 'none';
                if (imgBox) imgBox.style.display = (item.type === 'custom_image' || (iType === 'customImage' && item.type !== 'branding')) ? 'flex' : 'none';

                // Populate values
                if (item.type === 'branding' || iType === 'branding') {
                    const bUrlInp = document.getElementById('prop-branding-url');
                    if (bUrlInp) bUrlInp.value = item.image_url || (item.interaction_config?.image_url) || '';
                }
                if (item.type === 'sticky_note' || iType === 'stickyNote' || iType === 'stickynote') {
                    const sTxtInp = document.getElementById('prop-stickynote-text');
                    const noteContent = item.interaction_config?.noteText || item.interaction_config?.behavior?.data?.text || '';
                    if (sTxtInp) sTxtInp.value = noteContent;
                }
                if (item.type === 'custom_link' || iType === 'link') {
                    const lUrlInp = document.getElementById('prop-link-url');
                    const lTitleInp = document.getElementById('prop-link-title');
                    const lNewTabInp = document.getElementById('prop-link-newtab');
                    if (lUrlInp) lUrlInp.value = item.interaction_config?.url || item.interaction_config?.behavior?.data?.url || '';
                    if (lTitleInp) lTitleInp.value = item.interaction_config?.title || item.interaction_config?.behavior?.data?.title || item.name || '';
                    if (lNewTabInp) lNewTabInp.checked = item.interaction_config?.openInNewTab !== false;
                }
                if (item.type === 'custom_image' || (iType === 'customImage' && item.type !== 'branding')) {
                    const imgUrlInp = document.getElementById('prop-customimage-url');
                    if (imgUrlInp) imgUrlInp.value = item.image_url || item.interaction_config?.behavior?.data?.imageUrl || '';
                }
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

                const currentDoorSide = bounds.doorSide || 'auto';
                document.getElementById('prop-room-door-side').value = currentDoorSide;
                const currentDoorOffset = Math.round((typeof bounds.doorOffset === 'number' ? bounds.doorOffset : 0.5) * 100);
                document.getElementById('prop-room-door-offset').value = currentDoorOffset;
                document.getElementById('door-offset-val').textContent = currentDoorOffset + '%';
            }
        }

        function updateRoomProp(prop, val) {
            if (!selectedItem || selectedItem.type !== 'room') return;
            const r = selectedItem.item;
            r.metadata = r.metadata || {};
            r.bounds = r.bounds || {};

            if (prop === 'name') r.name = val;
            else if (prop === 'type') r.type = val;
            else if (prop === 'capacity') r.capacity = parseInt(val) || 10;
            else if (prop === 'color') r.color = val;
            else if (prop === 'audio_isolation') r.metadata.audio_isolation = !!val;
            else if (prop === 'doorSide') r.bounds.doorSide = val;
            else if (prop === 'doorOffset') r.bounds.doorOffset = parseFloat(val);

            draw();
        }

        function updateSelectedProp(prop, val) {
            if (!selectedItem) return;
            if (prop === 'name') selectedItem.item.name = val;
            if (prop === 'color') selectedItem.item.color = val;
            if (prop === 'width') selectedItem.item.width = val;
            if (prop === 'height') selectedItem.item.height = val;
            if (prop === 'elevation') selectedItem.item.elevation = parseInt(val) || 1;
            draw();
        }

        // ── Custom Media & Interactive Helpers ──
        function applyOrgLogoToSelected() {
            if (!selectedItem || selectedItem.type !== 'object') return;
            const obj = selectedItem.item;
            if (COMPANY_LOGO_URL) {
                obj.image_url = COMPANY_LOGO_URL;
                if (!obj.interaction_config) obj.interaction_config = {};
                obj.interaction_config.image_url = COMPANY_LOGO_URL;
                obj.interaction_config.use_company_logo = true;
                const input = document.getElementById('prop-branding-url');
                if (input) input.value = COMPANY_LOGO_URL;
                if (window._objImgCache) window._objImgCache.delete(obj.image_url);
                draw();
                showToast('🏢 {{ __("Company logo applied!") }}');
            }
        }

        function updateSelectedLogoUrl(val) {
            if (!selectedItem || selectedItem.type !== 'object') return;
            const obj = selectedItem.item;
            obj.image_url = val;
            if (!obj.interaction_config) obj.interaction_config = {};
            obj.interaction_config.image_url = val;
            if (window._objImgCache) window._objImgCache.delete(val);
            draw();
        }

        function updateSelectedStickyText(val) {
            if (!selectedItem || selectedItem.type !== 'object') return;
            const obj = selectedItem.item;
            if (!obj.interaction_config) obj.interaction_config = {};
            obj.interaction_config.noteText = val;
            if (!obj.interaction_config.behavior) obj.interaction_config.behavior = { type: 'stickyNote' };
            if (!obj.interaction_config.behavior.data) obj.interaction_config.behavior.data = {};
            obj.interaction_config.behavior.data.text = val;
            obj.name = val ? `Sticky: ${val.substring(0, 14)}...` : 'Sticky Note';
            const nameInput = document.getElementById('prop-name');
            if (nameInput) nameInput.value = obj.name;
        }

        const STICKY_COLOR_MAP = {
            yellow: 'https://assets.kumospace.com/furniture/decor/sticky-yellow-01/yella-large_240.png',
            orange: 'https://assets.kumospace.com/furniture/decor/sticky-orange-01/orange-large_240.png',
            purple: 'https://assets.kumospace.com/furniture/decor/sticky-purple-01/purple-large_240.png',
            green: 'https://assets.kumospace.com/furniture/decor/sticky-green-01/green-large_240.png',
            blue: 'https://assets.kumospace.com/furniture/decor/sticky-blue-01/blue-large_240.png'
        };

        function setStickyColor(colorKey) {
            if (!selectedItem || selectedItem.type !== 'object') return;
            const obj = selectedItem.item;
            const imgUrl = STICKY_COLOR_MAP[colorKey] || STICKY_COLOR_MAP.yellow;
            obj.image_url = imgUrl;
            if (!obj.interaction_config) obj.interaction_config = {};
            obj.interaction_config.image_url = imgUrl;
            obj.interaction_config.color = colorKey;
            if (window._objImgCache) window._objImgCache.delete(imgUrl);
            draw();
            showToast('🎨 {{ __("Sticky note color changed!") }}');
        }

        function updateSelectedLinkProp(prop, val) {
            if (!selectedItem || selectedItem.type !== 'object') return;
            const obj = selectedItem.item;
            if (!obj.interaction_config) obj.interaction_config = {};
            if (!obj.interaction_config.behavior) obj.interaction_config.behavior = { type: 'link' };
            if (!obj.interaction_config.behavior.data) obj.interaction_config.behavior.data = {};
            
            if (prop === 'url') {
                obj.interaction_config.url = val;
                obj.interaction_config.behavior.data.url = val;
            } else if (prop === 'title') {
                obj.interaction_config.title = val;
                obj.interaction_config.behavior.data.title = val;
                obj.name = val || 'Custom Link';
                const nameInp = document.getElementById('prop-name');
                if (nameInp) nameInp.value = obj.name;
            } else if (prop === 'openInNewTab') {
                obj.interaction_config.openInNewTab = !!val;
                obj.interaction_config.behavior.data.openInNewTab = !!val;
            }
        }

        function updateSelectedCustomImageUrl(val) {
            if (!selectedItem || selectedItem.type !== 'object') return;
            const obj = selectedItem.item;
            obj.image_url = val;
            if (!obj.interaction_config) obj.interaction_config = {};
            obj.interaction_config.image_url = val;
            if (!obj.interaction_config.behavior) obj.interaction_config.behavior = { type: 'customImage' };
            if (!obj.interaction_config.behavior.data) obj.interaction_config.behavior.data = {};
            obj.interaction_config.behavior.data.imageUrl = val;
            if (window._objImgCache) window._objImgCache.delete(val);
            draw();
        }

        async function uploadObjectImageDirectly(fileInput, targetType) {
            const file = fileInput.files[0];
            if (!file) return;
            showToast('⏳ {{ __("Uploading image...") }}');

            const fd = new FormData();
            fd.append('image', file);
            fd.append('_token', CSRF_TOKEN);

            try {
                const res = await fetch('/editor/upload-object-image', {
                    method: 'POST',
                    body: fd,
                    credentials: 'same-origin'
                });
                const data = await res.json();
                if (data.success && data.url) {
                    if (targetType === 'branding') {
                        updateSelectedLogoUrl(data.url);
                        const inp = document.getElementById('prop-branding-url');
                        if (inp) inp.value = data.url;
                    } else {
                        updateSelectedCustomImageUrl(data.url);
                        const inp = document.getElementById('prop-customimage-url');
                        if (inp) inp.value = data.url;
                    }
                    showToast('✅ {{ __("Image uploaded successfully!") }}');
                } else {
                    showToast('❌ ' + (data.message || '{{ __("Upload failed") }}'));
                }
            } catch (err) {
                console.error(err);
                showToast('❌ {{ __("Failed to upload image.") }}');
            }
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
            delete cloned.id;
            cloned.is_custom = true;
            cloned.name = `${orig.name || 'Object'} (Copy)`;
            const maxTx = Math.floor(MAP_WIDTH_PX / TILE_SIZE);
            const maxTy = Math.floor(MAP_HEIGHT_PX / TILE_SIZE);
            const w = cloned.width || 1;
            const h = cloned.height || 1;
            cloned.position = {
                x: Math.min(maxTx - w, (orig.position ? orig.position.x : 0) + 1),
                y: Math.min(maxTy - h, (orig.position ? orig.position.y : 0) + 1),
                rotation: orig.position?.rotation || orig.rotation || 0
            };
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
                        size: { width: o.width || (o.size ? o.size.width : 1), height: o.height || (o.size ? o.size.height : 1) },
                        width: o.width || (o.size ? o.size.width : 1),
                        height: o.height || (o.size ? o.size.height : 1),
                        rotation: o.position?.rotation || o.rotation || 0,
                        color: o.color,
                        image_url: o.image_url || null,
                        is_custom: typeof o.is_custom !== 'undefined' ? o.is_custom : true,
                        collision: typeof o.collision === 'boolean' ? o.collision : true,
                        interaction_config: o.interaction_config || { image_url: o.image_url, is_custom: true }
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
