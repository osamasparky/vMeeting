<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ __('Map Editor & Floor Designer') }} — {{ $map->name }}</title>

    <!-- Google Fonts & Material Symbols -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Mono:wght@400;500;600;700&family=IBM+Plex+Sans+Arabic:wght@300;400;500;600;700;800&family=IBM+Plex+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;1,400&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Rounded:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" />
    <link rel="stylesheet" href="https://fonts.googleapis.com/icon?family=Material+Icons|Material+Icons+Round|Material+Icons+Outlined" />

    <!-- UlaSpace Design Tokens & Office Stylesheet -->
    <link rel="stylesheet" href="{{ asset('css/ulaspace-tokens.css') }}">
    <link rel="stylesheet" href="{{ asset('css/ulaspace-office.css') }}">
    <link rel="stylesheet" href="{{ asset('css/modern-design-system.css') }}">

    <style>
        :root[data-theme="dark"], :root {
            --brand-primary: var(--nx-palm-300, #3c6b4c);
            --brand-primary-hover: var(--nx-palm-500, #1e412f);
            --brand-accent: var(--nx-accent, #d3a553);
            --brand-gold: var(--nx-gold-400, #d3a553);
            --brand-crimson: var(--nx-terracotta-500, #9a5827);
            --brand-teal: var(--nx-palm-300, #3c6b4c);

            --bg-body: var(--nx-palm-950, #0b1410);
            --bg-dock: rgba(20, 43, 36, 0.92);
            --bg-surface: var(--nx-palm-900, #142b24);
            --bg-card: var(--nx-palm-700, #1b3223);
            --bg-input: rgba(11, 20, 16, 0.90);
            --border-color: rgba(237, 230, 217, 0.15);
            --border-card: rgba(237, 230, 217, 0.12);
            --border-panel: rgba(237, 230, 217, 0.18);

            --text-primary: var(--nx-sand-100, #f9f4ee);
            --text-secondary: var(--nx-sand-400, #e3d2bb);
            --text-muted: var(--nx-sand-500, #c1b6a6);
            --text-main: var(--nx-sand-100, #f9f4ee);
            --text-dim: var(--nx-sand-500, #c1b6a6);

            --shadow-elevated: 0 16px 36px rgba(0, 0, 0, 0.4);
            --shadow-panel: 0 8px 24px rgba(0, 0, 0, 0.35);
        }

        :root[data-theme="light"] {
            --brand-primary: #059669;
            --brand-primary-hover: #047857;
            --brand-accent: #2563EB;
            --brand-gold: #D97706;
            --brand-crimson: #DC2626;
            --brand-teal: #0D9488;

            --bg-body: #F4F7F4;
            --bg-dock: rgba(255, 255, 255, 0.95);
            --bg-surface: rgba(255, 255, 255, 0.98);
            --bg-card: rgba(240, 248, 243, 0.92);
            --bg-input: rgba(245, 250, 247, 0.95);
            --border-color: rgba(5, 150, 105, 0.20);
            --border-card: rgba(5, 150, 105, 0.14);
            --border-panel: rgba(5, 150, 105, 0.18);

            --text-primary: #0F172A;
            --text-secondary: #475569;
            --text-muted: #94A3B8;
            --text-main: #0F172A;
            --text-dim: #64748B;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            user-select: none;
        }

        body {
            font-family: 'IBM Plex Sans Arabic', 'IBM Plex Sans', -apple-system, BlinkMacSystemFont, sans-serif;
            background: var(--bg-body);
            color: var(--text-primary);
            height: 100vh;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            -webkit-font-smoothing: antialiased;
        }

        .nx-editor-screen {
            width: 95%;
            max-width: 1720px;
            height: calc(100vh - 24px);
            background: radial-gradient(circle at center, #0B1C13 0%, #050B08 100%);
            border: 2px solid rgba(237, 230, 217, 0.16);
            border-radius: 28px;
            box-shadow: 0 24px 60px rgba(0, 0, 0, 0.7), inset 0 0 80px rgba(0, 0, 0, 0.6);
            display: flex;
            flex-direction: column;
            overflow: hidden;
            position: relative;
        }

        .editor-workspace {
            flex: 1;
            display: flex;
            position: relative;
            overflow: hidden;
            height: calc(100% - 56px);
        }

        .canvas-viewport {
            flex: 1;
            height: 100%;
            position: relative;
            background: transparent;
            overflow: hidden;
            cursor: default;
        }

        #editor-canvas {
            display: block;
            width: 100%;
            height: 100%;
        }

        /* ── Tools Bar & Buttons ── */
        .segmented-tool-pill {
            display: flex;
            align-items: center;
            background: rgba(11, 20, 16, 0.9);
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
            font-weight: 700;
            cursor: pointer;
            transition: all 0.18s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .tool-btn:hover {
            background: rgba(60, 107, 76, 0.2);
            color: var(--text-primary);
        }
        .tool-btn.active {
            background: rgba(60, 107, 76, 0.35);
            border-color: rgba(134, 239, 172, 0.4);
            color: #86EFAC;
            box-shadow: 0 2px 8px rgba(60, 107, 76, 0.3);
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
            background: rgba(60, 107, 76, 0.2);
            border-color: var(--brand-primary);
            color: var(--text-primary);
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
            background: rgba(14, 25, 19, 0.98);
            backdrop-filter: blur(24px);
            border: 1px solid rgba(237, 230, 217, 0.20);
            border-radius: 14px;
            box-shadow: 0 16px 36px rgba(0,0,0,0.65);
            padding: 6px;
            z-index: 100000;
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
            color: var(--text-primary);
            font-size: 12px;
            font-weight: 600;
            cursor: pointer;
            width: 100%;
            text-align: start;
            transition: background 0.15s ease;
        }
        .editor-dropdown-item:hover {
            background: rgba(60, 107, 76, 0.25);
            color: #86EFAC;
        }
        .editor-dropdown-item.active {
            background: rgba(60, 107, 76, 0.4);
            color: #86EFAC;
        }

        .more-menu-item {
            background: transparent;
            border: none;
            color: var(--text-primary);
            padding: 9px 12px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 12px;
            font-weight: 600;
            cursor: pointer;
            width: 100%;
            text-align: start;
            transition: all 0.15s ease;
        }
        .more-menu-item:hover {
            background: rgba(255, 255, 255, 0.08);
            color: #86EFAC;
        }

        .act-btn {
            display: flex;
            align-items: center;
            gap: 6px;
            padding: 7px 12px;
            border-radius: 10px;
            font-size: 12px;
            font-weight: 700;
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
        .act-btn-secondary {
            background: var(--bg-input);
            border: 1px solid var(--border-panel);
            color: var(--text-primary);
        }
        .act-btn-secondary:hover {
            border-color: var(--brand-primary);
            color: var(--brand-primary);
        }

        /* Floating View Nav Overlay */
        .viewport-controls {
            position: absolute;
            bottom: 20px;
            inset-inline-start: 20px;
            display: flex;
            align-items: center;
            gap: 6px;
            background: rgba(14, 25, 19, 0.95);
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
            color: var(--text-primary);
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
            color: var(--text-primary);
            padding: 4px 8px;
            border-radius: 6px;
            font-size: 11px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.15s;
        }
        .float-act-btn:hover {
            background: rgba(60, 107, 76, 0.25);
            border-color: var(--brand-primary);
            color: #86EFAC;
        }

        /* ── Right Customizer Drawer ── */
        .customizer-drawer {
            width: 380px;
            height: 100%;
            background: rgba(14, 25, 19, 0.96);
            backdrop-filter: blur(28px);
            border-inline-start: 1px solid var(--border-panel);
            display: flex;
            flex-direction: column;
            z-index: 20;
            transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1), margin 0.3s cubic-bezier(0.4, 0, 0.2, 1);
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
            font-weight: 700;
            color: var(--text-primary);
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .drawer-tabs {
            display: flex;
            background: var(--bg-input);
            padding: 4px;
            margin: 10px 14px;
            border-radius: 12px;
            gap: 4px;
            border: 1px solid var(--border-card);
        }
        .drawer-tab {
            flex: 1;
            text-align: center;
            padding: 8px 4px;
            border-radius: 8px;
            font-size: 11px;
            font-weight: 700;
            color: var(--text-muted);
            cursor: pointer;
            transition: all 0.18s;
        }
        .drawer-tab:hover {
            color: var(--text-primary);
        }
        .drawer-tab.active {
            background: var(--brand-primary);
            color: white;
            box-shadow: 0 2px 8px rgba(60, 107, 76, 0.4);
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
            color: var(--text-primary);
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
            box-shadow: 0 0 10px rgba(60, 107, 76, 0.3);
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
            color: var(--text-primary);
        }

        .category-filter-bar {
            display: flex;
            gap: 6px;
            overflow-x: auto;
            padding: 2px 2px 6px 2px;
            scrollbar-width: thin;
            scrollbar-color: rgba(60, 107, 76, 0.4) transparent;
        }
        .category-filter-bar::-webkit-scrollbar {
            height: 3px;
        }
        .category-filter-bar::-webkit-scrollbar-thumb {
            background: rgba(60, 107, 76, 0.4);
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
            font-weight: 700;
            white-space: nowrap;
            cursor: pointer;
            transition: all 0.18s cubic-bezier(0.4, 0, 0.2, 1);
            user-select: none;
            flex-shrink: 0;
        }
        .cat-pill:hover {
            border-color: var(--brand-primary);
            color: var(--text-primary);
            background: rgba(60, 107, 76, 0.2);
            transform: translateY(-1px);
        }
        .cat-pill.active {
            background: linear-gradient(135deg, rgba(60, 107, 76, 0.4), rgba(30, 65, 47, 0.4));
            border-color: var(--brand-primary);
            color: #86EFAC;
            box-shadow: 0 2px 8px rgba(60, 107, 76, 0.3);
        }
        .cat-pill-count {
            font-size: 9px;
            padding: 1px 5px;
            border-radius: 8px;
            background: rgba(0, 0, 0, 0.4);
            color: #86EFAC;
        }

        .category-group {
            background: var(--bg-input);
            border: 1px solid var(--border-card);
            border-radius: 12px;
            overflow: hidden;
            transition: border-color 0.2s;
        }
        .category-group:hover {
            border-color: rgba(60, 107, 76, 0.4);
        }
        .category-title-bar {
            padding: 9px 12px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            font-size: 12px;
            font-weight: 700;
            color: var(--text-primary);
            cursor: pointer;
            background: rgba(255, 255, 255, 0.02);
            transition: background 0.15s;
        }
        .category-title-bar:hover {
            background: rgba(60, 107, 76, 0.12);
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
            background: rgba(60, 107, 76, 0.35);
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
            background: rgba(60, 107, 76, 0.2);
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(0, 0, 0, 0.4), 0 0 10px rgba(60, 107, 76, 0.3);
        }
        .furn-card.active {
            border-color: #86EFAC;
            background: rgba(60, 107, 76, 0.35);
            box-shadow: 0 0 14px rgba(60, 107, 76, 0.5);
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
            background: rgba(60, 107, 76, 0.3);
            color: #86EFAC;
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
            background: radial-gradient(circle at center, rgba(27, 50, 35, 0.9) 0%, rgba(11, 20, 16, 0.95) 100%);
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
            font-weight: 700;
            color: var(--text-primary);
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
            font-weight: 700;
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
            color: var(--text-primary);
            font-size: 12px;
            font-weight: 600;
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
            color: var(--text-primary);
            font-size: 11px;
            font-weight: 700;
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
            background: rgba(60, 107, 76, 0.95);
            backdrop-filter: blur(12px);
            color: white;
            padding: 12px 20px;
            border-radius: 14px;
            font-size: 13px;
            font-weight: 700;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.4);
            display: none;
            z-index: 100000;
            animation: popToast 0.3s ease;
        }
        @keyframes popToast {
            from { transform: translateY(20px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }
    </style>
</head>
<body>

    <div class="nx-office-viewport-container" style="display: flex; align-items: center; justify-content: center; height: 100vh; overflow: hidden; position: relative;">
        
        <div class="nx-editor-screen">
            
            <!-- ── Top Map & Editor Toolbar (UlaSpace Figma Standard) ── -->
            <header class="nx-map-toolbar" style="position: relative; top: 0; left: 0; right: 0; border-radius: 0; border-inline: none; border-block-start: none; background: rgba(14, 25, 19, 0.95); backdrop-filter: blur(24px); border-block-end: 1px solid rgba(237, 230, 217, 0.15); padding: 10px 16px; margin: 0; display: flex; align-items: center; justify-content: space-between; z-index: 100;">
                
                <!-- 1. Start Group (Top Right on RTL): Burger Menu + Brand Capsule + Branch Switcher + Version -->
                <div class="nx-toolbar-group">
                    <!-- Main Burger Dropdown -->
                    <div style="position: relative; display: inline-block;">
                        <button type="button" onclick="toggleEditorMainMenu(event)" class="nx-toolbar-btn" style="padding: 6px 10px;" title="{{ __('Menu (القائمة الرئيسية)') }}">
                            <span class="material-symbols-rounded" style="font-size: 20px;">menu</span>
                        </button>
                        
                        <div id="editor-main-menu-dropdown" style="display: none; position: absolute; top: calc(100% + 8px); inset-inline-start: 0; min-width: 260px; background: rgba(14, 25, 19, 0.98); backdrop-filter: blur(24px); border: 1px solid rgba(237, 230, 217, 0.20); border-radius: 16px; box-shadow: 0 16px 40px rgba(0,0,0,0.65); padding: 8px; z-index: 100000;">
                            <!-- Header Info -->
                            <div style="display: flex; align-items: center; gap: 10px; padding: 8px 10px 12px; border-bottom: 1px solid rgba(237, 230, 217, 0.12); margin-bottom: 6px;">
                                @if(!empty($organization->logo_url))
                                    <img src="{{ $organization->logo_url }}" alt="{{ $organization->name }}" style="height: 24px; width: auto; object-fit: contain;">
                                @else
                                    <span class="material-symbols-rounded" style="color: var(--nx-map-gold); font-size: 24px;">apartment</span>
                                @endif
                                <div style="overflow: hidden;">
                                    <strong style="display: block; font-size: 13px; color: var(--nx-map-text); white-space: nowrap; text-overflow: ellipsis; overflow: hidden;">{{ $organization->name }}</strong>
                                    <span style="font-size: 11px; color: var(--nx-map-muted);">{{ __('Floor Map Designer') }}</span>
                                </div>
                            </div>

                            <!-- Actions -->
                            <a href="{{ route('dashboard') }}" class="more-menu-item" style="display: flex; align-items: center; gap: 10px; padding: 8px 12px; border-radius: 8px; text-decoration: none; color: #F9F4EE; font-size: 12px; font-weight: 600;">
                                <span class="material-symbols-rounded" style="font-size: 18px; color: var(--nx-map-gold);">dashboard</span>
                                <span>{{ __('Dashboard (لوحة التحكم)') }}</span>
                            </a>

                            <a href="{{ route('office', ['office' => $floor->id]) }}" class="more-menu-item" style="display: flex; align-items: center; gap: 10px; padding: 8px 12px; border-radius: 8px; text-decoration: none; color: #86EFAC; font-size: 12px; font-weight: 600;">
                                <span class="material-symbols-rounded" style="font-size: 18px;">meeting_room</span>
                                <span>{{ __('Enter Live Office (دخول المكتب)') }}</span>
                            </a>

                            @if(session('superadmin_impersonator_id'))
                            <form method="POST" action="{{ route('impersonate.leave') }}" style="margin: 0;">
                                @csrf
                                <button type="submit" class="more-menu-item" style="width: 100%; display: flex; align-items: center; gap: 10px; padding: 8px 12px; border-radius: 8px; background: none; border: none; color: #93C5FD; font-size: 12px; font-weight: 600; cursor: pointer; text-align: start;">
                                    <span class="material-symbols-rounded" style="font-size: 18px;">shield</span>
                                    <span>{{ __('Return to Super Admin (الرجوع للمشرف العام)') }}</span>
                                </button>
                            </form>
                            @endif

                            <div style="height: 1px; background: rgba(237, 230, 217, 0.12); margin: 6px 0;"></div>

                            <button type="button" onclick="toggleAppTheme(); closeEditorMainMenu();" class="more-menu-item" style="width: 100%; display: flex; align-items: center; gap: 10px; padding: 8px 12px; border-radius: 8px; background: none; border: none; color: #F9F4EE; font-size: 12px; font-weight: 600; cursor: pointer; text-align: start;">
                                <span class="material-symbols-rounded" style="font-size: 18px;">light_mode</span>
                                <span>{{ __('Toggle Theme (المظهر)') }}</span>
                            </button>

                            @if(app()->getLocale() === 'ar')
                                <a href="{{ route('lang.switch', 'en') }}" class="more-menu-item" style="display: flex; align-items: center; gap: 10px; padding: 8px 12px; border-radius: 8px; text-decoration: none; color: #F9F4EE; font-size: 12px; font-weight: 600;">
                                    <span class="material-symbols-rounded" style="font-size: 18px;">language</span>
                                    <span>English (EN)</span>
                                </a>
                            @else
                                <a href="{{ route('lang.switch', 'ar') }}" class="more-menu-item" style="display: flex; align-items: center; gap: 10px; padding: 8px 12px; border-radius: 8px; text-decoration: none; color: #F9F4EE; font-size: 12px; font-weight: 600;">
                                    <span class="material-symbols-rounded" style="font-size: 18px;">language</span>
                                    <span>العربية (AR)</span>
                                </a>
                            @endif
                        </div>
                    </div>

                    <!-- Brand Capsule with Logo -->
                    <div class="nx-brand-capsule" onclick="toggleEditorMainMenu(event)" style="cursor: pointer;" title="{{ __('Click to open menu') }}">
                        <span class="nx-presence-dot"></span>
                        @if(!empty($organization->logo_url))
                            <img src="{{ $organization->logo_url }}" alt="{{ $organization->name }}" style="height: 18px; width: auto; object-fit: contain;">
                        @else
                            <span class="material-symbols-rounded" style="color: var(--nx-map-gold); font-size: 18px;">apartment</span>
                        @endif
                        <span>{{ $organization->name }}</span>
                    </div>

                    <!-- Branch Switcher -->
                    <div style="position: relative; display: inline-block;">
                        <button type="button" onclick="toggleBranchDropdown(event)" class="nx-toolbar-btn" style="color: var(--nx-map-gold); border-color: rgba(211, 165, 83, 0.35); font-weight: 600;" title="{{ __('Switch Office Branch (تغيير الفرع للتعديل)') }}">
                            <span class="material-symbols-rounded" style="font-size: 18px;">domain</span>
                            <span style="max-width: 140px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">{{ $floor->name }}</span>
                            <span class="material-symbols-rounded" style="font-size: 16px;">arrow_drop_down</span>
                        </button>
                        <div id="branch-select-dropdown" style="display: none; position: absolute; top: calc(100% + 8px); inset-inline-start: 0; min-width: 250px; background: rgba(14, 25, 19, 0.98); backdrop-filter: blur(18px); border: 1px solid rgba(237, 230, 217, 0.20); border-radius: 14px; box-shadow: 0 16px 36px rgba(0,0,0,0.65); padding: 6px; z-index: 100000;">
                            <div style="font-size: 10px; font-weight: 900; text-transform: uppercase; letter-spacing: 0.5px; color: rgba(255,255,255,0.5); padding: 6px 10px; border-bottom: 1px solid rgba(255,255,255,0.08); margin-bottom: 4px;">
                                🏢 {{ __('Select Office Branch (اختر الفرع للتعديل)') }}
                            </div>
                            @foreach($floors as $f)
                            <a href="{{ route('editor', ['office' => $f->id]) }}" style="display: flex; align-items: center; justify-content: space-between; gap: 8px; padding: 8px 12px; border-radius: 8px; text-decoration: none; color: {{ $f->id === $floor->id ? '#86EFAC' : '#E2E8F0' }}; background: {{ $f->id === $floor->id ? 'rgba(36, 92, 58, 0.45)' : 'transparent' }}; font-weight: 700; font-size: 12px; transition: background 0.15s ease;">
                                <div style="display: flex; align-items: center; gap: 8px;">
                                    <span class="material-symbols-rounded" style="font-size: 16px;">apartment</span>
                                    <span>{{ $f->name }}</span>
                                </div>
                                @if($f->id === $floor->id)
                                    <span style="font-size: 10px; color: #86EFAC; font-weight: 800;">● {{ __('Editing') }}</span>
                                @endif
                            </a>
                            @endforeach
                        </div>
                    </div>

                    <span class="nx-presence-capsule" id="header-version-badge" style="font-family: 'IBM Plex Mono', monospace; font-size: 11px; padding: 4px 10px;" title="Map version and status">
                        v{{ $map->version }} • {{ ucfirst($map->status) }}
                    </span>
                </div>

                <!-- 2. Center: Tools Selector & Quick Actions -->
                <div class="nx-toolbar-group" style="gap: 8px;">
                    <div class="segmented-tool-pill" style="background: rgba(11, 20, 16, 0.90); border: 1px solid rgba(237, 230, 217, 0.15); border-radius: 14px; padding: 3px; display: flex; gap: 2px;">
                        <button class="tool-btn active" id="tool-select" onclick="setTool('select')" title="{{ __('Select & Move Objects (V)') }}">
                            <span class="material-symbols-rounded" style="font-size: 16px;">pan_tool_alt</span>
                            <span>{{ __('Select') }}</span>
                        </button>
                        <button class="tool-btn" id="tool-room" onclick="setTool('room')" title="{{ __('Draw Meeting / Private Rooms (R)') }}">
                            <span class="material-symbols-rounded" style="font-size: 16px;">meeting_room</span>
                            <span>{{ __('Room') }}</span>
                        </button>
                        <button class="tool-btn" id="tool-object" onclick="setTool('object')" title="{{ __('Place Furniture & Decor (F)') }}">
                            <span class="material-symbols-rounded" style="font-size: 16px;">chair</span>
                            <span>{{ __('Furniture') }}</span>
                        </button>
                    </div>

                    <div style="display: flex; gap: 4px; align-items: center;">
                        <button class="nx-toolbar-btn" onclick="rotateSelectedItem(90)" title="{{ __('Rotate 90° (R)') }}" style="width: 34px; height: 34px; padding: 0; justify-content: center;">
                            <span class="material-symbols-rounded" style="font-size: 18px;">rotate_right</span>
                        </button>
                        <button class="nx-toolbar-btn" onclick="duplicateSelectedItem()" title="{{ __('Clone / Duplicate') }}" style="width: 34px; height: 34px; padding: 0; justify-content: center;">
                            <span class="material-symbols-rounded" style="font-size: 18px;">content_copy</span>
                        </button>
                        <button class="nx-toolbar-btn" onclick="deleteSelectedItem()" title="{{ __('Delete Selected (Del)') }}" style="width: 34px; height: 34px; padding: 0; justify-content: center; color: #F87171; border-color: rgba(239, 68, 68, 0.3);">
                            <span class="material-symbols-rounded" style="font-size: 18px;">delete</span>
                        </button>
                    </div>
                </div>

                <!-- 3. End Group (Top Left on RTL): AI Generator + Save + Publish + Catalog Drawer -->
                <div class="nx-toolbar-group">
                    <input type="file" id="floorplan-file-input" accept="image/jpeg,image/png,image/webp,image/jpg" style="display:none;" onchange="handleFloorplanUpload(this)">

                    <button type="button" onclick="openAiGeneratorModal()" class="nx-toolbar-btn btn-accent" style="font-weight: 700; background: linear-gradient(135deg, rgba(211, 165, 83, 0.35), rgba(184, 137, 50, 0.35)); border-color: rgba(211, 165, 83, 0.6); color: #F59E0B;" title="{{ __('Generate 3D Isometric Office Floorplan & Rooms with AI') }}">
                        <span class="material-symbols-rounded" style="font-size: 18px;">auto_awesome</span>
                        <span>{{ __('AI Generator') }}</span>
                    </button>

                    <button class="nx-toolbar-btn" onclick="saveMapDraft()" title="{{ __('Save Map Draft') }}">
                        <span class="material-symbols-rounded" style="font-size: 18px;">save</span>
                        <span>{{ __('Save') }}</span>
                    </button>

                    <button class="nx-toolbar-btn" onclick="publishMap()" style="background: rgba(60, 107, 76, 0.4); border-color: #3C6B4C; color: #86EFAC; font-weight: 700;" title="{{ __('Publish Map to Live Office') }}">
                        <span class="material-symbols-rounded" style="font-size: 18px;">rocket_launch</span>
                        <span>{{ __('Publish') }}</span>
                    </button>

                    <button class="nx-toolbar-btn" onclick="toggleCustomizerDrawer()" title="{{ __('Toggle 3D Catalog & Inspector') }}" style="background: rgba(255, 255, 255, 0.08);">
                        <span class="material-symbols-rounded" style="font-size: 18px;">dashboard_customize</span>
                        <span>{{ __('Catalog') }}</span>
                    </button>
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
                <div class="drawer-tab" id="tab-btn-floors" onclick="switchDrawerTab('floors')" style="display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 1px; padding: 4px 6px;">
                    <span style="font-size: 11px; font-weight: 700;">Floor Styles</span>
                    <span style="font-size: 9px; opacity: 0.85; font-family: 'IBM Plex Sans Arabic', sans-serif;">الأرضيات</span>
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

                <!-- 4. FLOORS & BACKGROUNDS TAB -->
                <div id="drawer-view-floors" style="display: none; flex-direction: column; gap: 12px;">
                    <!-- Quick Action Tools Bar (Moved from Burger Menu) -->
                    <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 6px; padding: 6px; background: rgba(0,0,0,0.35); border: 1px solid var(--border-panel); border-radius: 12px;">
                        <label class="tool-btn" style="cursor: pointer; display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 3px; padding: 6px 2px; font-size: 10px; text-align: center; margin: 0; background: rgba(255,255,255,0.05);" title="{{ __('Upload Custom Floorplan (رفع مخطط مخصص)') }}">
                            <span class="material-symbols-rounded" style="font-size: 20px; color: #F59E0B;">upload_file</span>
                            <span style="font-weight: 700;">{{ __('Upload') }}</span>
                            <span style="font-size: 9px; opacity: 0.8; font-family: 'IBM Plex Sans Arabic', sans-serif;">رفع مخصص</span>
                            <input type="file" accept="image/*" style="display:none;" onchange="handleCustomFloorUpload(this)">
                        </label>

                        <button type="button" class="tool-btn" onclick="deleteFloorplan()" style="display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 3px; padding: 6px 2px; font-size: 10px; text-align: center; color: #F87171; background: rgba(239,68,68,0.1); border-color: rgba(239,68,68,0.3);" title="{{ __('Reset to Default Floorplan (استعادة المخطط الافتراضي)') }}">
                            <span class="material-symbols-rounded" style="font-size: 20px; color: #F87171;">restart_alt</span>
                            <span style="font-weight: 700;">{{ __('Reset') }}</span>
                            <span style="font-size: 9px; opacity: 0.8; font-family: 'IBM Plex Sans Arabic', sans-serif;">استعادة</span>
                        </button>

                        <button type="button" class="tool-btn" onclick="clearWorkspace()" style="display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 3px; padding: 6px 2px; font-size: 10px; text-align: center; color: #FBBF24; background: rgba(245,158,11,0.1); border-color: rgba(245,158,11,0.3);" title="{{ __('Clear All Placed Furniture (تفريغ الأثاث)') }}">
                            <span class="material-symbols-rounded" style="font-size: 20px; color: #FBBF24;">cleaning_services</span>
                            <span style="font-weight: 700;">{{ __('Clear') }}</span>
                            <span style="font-size: 9px; opacity: 0.8; font-family: 'IBM Plex Sans Arabic', sans-serif;">تفريغ الأثاث</span>
                        </button>
                    </div>

                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <span style="font-size: 11px; font-weight: 800; color: #A7F3D0;">🎨 {{ __('Floor Styles Library (1200×708)') }}</span>
                        <span style="font-size: 10px; color: var(--text-muted); font-family: monospace;">18 Styles</span>
                    </div>

                    <div style="font-size: 11px; color: var(--text-muted); line-height: 1.4;">
                        {{ __('اختر نمط الأرضية لتطبيقه فوراً كخلفية للمكتب بمقاس 1200×708 بكسل:') }}
                    </div>

                    <div id="floors-catalog-grid" style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 10px; max-height: calc(100vh - 360px); overflow-y: auto; padding-right: 4px;">
                        <!-- Injected via JavaScript -->
                    </div>

                    <div style="padding-top: 8px; border-top: 1px solid var(--border-panel); display: flex; justify-content: space-between; align-items: center;">
                        <button type="button" class="tool-btn" onclick="clearCurrentFloorBackground()" style="color: #F87171; border-color: rgba(239,68,68,0.3); font-size: 11px; width: 100%; justify-content: center;">
                            🗑️ {{ __('Remove Floor Background (إزالة صورة الأرضية)') }}
                        </button>
                    </div>
                </div>

            </div>
        </aside>
    </div> <!-- .editor-workspace -->
    </div> <!-- .nx-editor-screen -->
    </div> <!-- .nx-office-viewport-container -->

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
        let MAP_WIDTH_PX = (MAP_DATA.layout_data && MAP_DATA.layout_data.background_width && Number(MAP_DATA.layout_data.background_width) >= 500)
            ? Number(MAP_DATA.layout_data.background_width)
            : 1200;
        let MAP_HEIGHT_PX = (MAP_DATA.layout_data && MAP_DATA.layout_data.background_height && Number(MAP_DATA.layout_data.background_height) >= 500)
            ? Number(MAP_DATA.layout_data.background_height)
            : 708;

        let zoomLevel = 1.0;
        let panOffset = { x: 0, y: 0 };
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

        function fitAndCenterView() {
            if (!canvas || !container) return;
            width = canvas.width = container.clientWidth;
            height = canvas.height = container.clientHeight;
            const scaleX = (width - 48) / MAP_WIDTH_PX;
            const scaleY = (height - 48) / MAP_HEIGHT_PX;
            // Scale dynamically to fill viewport comfortably
            zoomLevel = Math.max(0.15, Math.min(3.0, Math.min(scaleX, scaleY)));
            panOffset.x = Math.round((width - MAP_WIDTH_PX * zoomLevel) / 2);
            panOffset.y = Math.round((height - MAP_HEIGHT_PX * zoomLevel) / 2);
            if (typeof draw === 'function') draw();
        }

        // ── Resize Engine ──
        function resizeCanvas() {
            width = canvas.width = container.clientWidth;
            height = canvas.height = container.clientHeight;
            draw();
        }
        window.addEventListener('resize', resizeCanvas);

        // ── Background Blueprint Artwork ──
        const BLUEPRINT_IMAGE = new Image();
        let initialBgUrl = (MAP_DATA.layout_data && MAP_DATA.layout_data.background_image_url)
            ? MAP_DATA.layout_data.background_image_url
            : null;
        let blueprintLoaded = false;
        if (initialBgUrl) {
            BLUEPRINT_IMAGE.src = initialBgUrl + (initialBgUrl.includes('?') ? '&' : '?') + 'v=' + Date.now();
            BLUEPRINT_IMAGE.onload = () => {
                blueprintLoaded = true;
                if (BLUEPRINT_IMAGE.naturalWidth > 0 && BLUEPRINT_IMAGE.naturalHeight > 0) {
                    MAP_WIDTH_PX = BLUEPRINT_IMAGE.naturalWidth;
                    MAP_HEIGHT_PX = BLUEPRINT_IMAGE.naturalHeight;
                } else {
                    MAP_WIDTH_PX = 1200;
                    MAP_HEIGHT_PX = 708;
                }
                fitAndCenterView();
                draw();
            };
            BLUEPRINT_IMAGE.onerror = () => {
                blueprintLoaded = false;
                MAP_WIDTH_PX = 1200;
                MAP_HEIGHT_PX = 708;
                fitAndCenterView();
                draw();
            };
        } else {
            blueprintLoaded = false;
            MAP_WIDTH_PX = 1200;
            MAP_HEIGHT_PX = 708;
            setTimeout(fitAndCenterView, 50);
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

        const FLOOR_CATALOG = [
            { id: 'floor_natural_oak', name_en: 'Natural Oak Parquet', name_ar: 'باركيه بلوط طبيعي دافئ', url: '/images/floors/floor_natural_oak.jpg', thumb: '/images/floors/thumb_floor_natural_oak.jpg' },
            { id: 'floor_dark_walnut', name_en: 'Dark Walnut Herringbone', name_ar: 'خشب جوز داكن متعرج', url: '/images/floors/floor_dark_walnut.jpg', thumb: '/images/floors/thumb_floor_dark_walnut.jpg' },
            { id: 'floor_light_birch', name_en: 'Scandinavian Light Birch', name_ar: 'خشب زان إسكندنافي فاتح', url: '/images/floors/floor_light_birch.jpg', thumb: '/images/floors/thumb_floor_light_birch.jpg' },
            { id: 'floor_carrara_marble', name_en: 'Carrara White Marble', name_ar: 'رخام كرارا أبيض فاخر', url: '/images/floors/floor_carrara_marble.jpg', thumb: '/images/floors/thumb_floor_carrara_marble.jpg' },
            { id: 'floor_nero_marquina', name_en: 'Nero Marquina Black Marble', name_ar: 'رخام أسود مذهب ملكي', url: '/images/floors/floor_nero_marquina.jpg', thumb: '/images/floors/thumb_floor_nero_marquina.jpg' },
            { id: 'floor_industrial_concrete', name_en: 'Polished Industrial Concrete', name_ar: 'خرسانة صناعية مصقولة', url: '/images/floors/floor_industrial_concrete.jpg', thumb: '/images/floors/thumb_floor_industrial_concrete.jpg' },
            { id: 'floor_slate_tile', name_en: 'Urban Slate Grey Stone', name_ar: 'بلاط حجري رمادي حضري', url: '/images/floors/floor_slate_tile.jpg', thumb: '/images/floors/thumb_floor_slate_tile.jpg' },
            { id: 'floor_terrazzo', name_en: 'Italian Venetian Terrazzo', name_ar: 'تيرازو إيطالي حديث مذهب', url: '/images/floors/floor_terrazzo.jpg', thumb: '/images/floors/thumb_floor_terrazzo.jpg' },
            { id: 'floor_chevron_timber', name_en: 'Modern Chevron Walnut', name_ar: 'أرضية خشب شيفرون مودرن', url: '/images/floors/floor_chevron_timber.jpg', thumb: '/images/floors/thumb_floor_chevron_timber.jpg' },
            { id: 'floor_executive_carpet', name_en: 'Executive Midnight Carpet', name_ar: 'سجاد مكتبي تنفيذي كحلي', url: '/images/floors/floor_executive_carpet.jpg', thumb: '/images/floors/thumb_floor_executive_carpet.jpg' },
            { id: 'floor_charcoal_carpet', name_en: 'Charcoal Commercial Weave', name_ar: 'موكيت رمادي فحمي مكتبي', url: '/images/floors/floor_charcoal_carpet.jpg', thumb: '/images/floors/thumb_floor_charcoal_carpet.jpg' },
            { id: 'floor_ceramic_beige', name_en: 'Warm Ceramic Beige Tiles', name_ar: 'بلاط سيراميك بيج دافئ', url: '/images/floors/floor_ceramic_beige.jpg', thumb: '/images/floors/thumb_floor_ceramic_beige.jpg' },
            { id: 'floor_geometric_porcelain', name_en: 'Geometric Porcelain Grid', name_ar: 'بورسلين هندسي حديث', url: '/images/floors/floor_geometric_porcelain.jpg', thumb: '/images/floors/thumb_floor_geometric_porcelain.jpg' },
            { id: 'floor_biophilic_garden', name_en: 'Biophilic Moss & Stone Garden', name_ar: 'أرضية عشبية وحجرية بيوفيلك', url: '/images/floors/floor_biophilic_garden.jpg', thumb: '/images/floors/thumb_floor_biophilic_garden.jpg' },
            { id: 'floor_japanese_tatami', name_en: 'Japanese Tatami & Bamboo', name_ar: 'خيزران وتاتامي ياباني', url: '/images/floors/floor_japanese_tatami.jpg', thumb: '/images/floors/thumb_floor_japanese_tatami.jpg' },
            { id: 'floor_emerald_epoxy', name_en: 'Emerald Gloss Epoxy', name_ar: 'إيبوكسي أخضر زمردي لامع', url: '/images/floors/floor_emerald_epoxy.jpg', thumb: '/images/floors/thumb_floor_emerald_epoxy.jpg' },
            { id: 'floor_open_office_blueprint', name_en: 'Open Plan Architectural Plan', name_ar: 'مخطط معماري مكتبي مفتوح', url: '/images/floors/floor_open_office_blueprint.jpg', thumb: '/images/floors/thumb_floor_open_office_blueprint.jpg' },
            { id: 'floor_executive_suite_blueprint', name_en: 'Executive Suite Architectural Plan', name_ar: 'مخطط جناح تنفيذي متكامل', url: '/images/floors/floor_executive_suite_blueprint.jpg', thumb: '/images/floors/thumb_floor_executive_suite_blueprint.jpg' },
        ];

        function renderFloorsCatalog() {
            const grid = document.getElementById('floors-catalog-grid');
            if (!grid) return;
            const currentBg = MAP_DATA.layout_data?.background_image_url || '';
            const isAr = document.documentElement.lang === 'ar' || document.documentElement.dir === 'rtl';

            grid.innerHTML = FLOOR_CATALOG.map(f => {
                const isActive = currentBg.includes(f.id);
                return `
                    <div class="furn-card ${isActive ? 'selected' : ''}" style="display:flex; flex-direction:column; gap:4px; padding:6px; cursor:pointer; position:relative; border-radius:12px; border:1px solid ${isActive ? 'var(--brand-primary)' : 'var(--border-card)'}; background:var(--bg-input);" onclick="applyFloorBackground('${f.url}', 1200, 708)">
                        <div style="position:relative; width:100%; height:75px; border-radius:8px; overflow:hidden; background:#0B1C13;">
                            <img src="${f.thumb}" alt="${f.name_en}" style="width:100%; height:100%; object-fit:cover;">
                            <span style="position:absolute; bottom:3px; inset-inline-end:3px; background:rgba(0,0,0,0.7); font-size:9px; font-family:monospace; padding:1px 4px; border-radius:4px; color:#A7F3D0;">1200×708</span>
                            ${isActive ? '<span style="position:absolute; top:3px; inset-inline-start:3px; background:#10B981; font-size:9px; font-weight:800; padding:1px 6px; border-radius:4px; color:#fff;">✓ نشط</span>' : ''}
                        </div>
                        <div style="font-size:11px; font-weight:700; color:var(--text-primary); white-space:nowrap; overflow:hidden; text-overflow:ellipsis; text-align:start;">
                            ${isAr ? f.name_ar : f.name_en}
                        </div>
                    </div>
                `;
            }).join('');
        }

        async function applyFloorBackground(floorUrl, width = 1200, height = 708) {
            showToast('⏳ {{ __("Applying Floor Style (جاري تطبيق نمط الأرضية)...") }}');
            try {
                const res = await fetch(`/editor/maps/${MAP_ID}/background`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': CSRF_TOKEN,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        floor_url: floorUrl,
                        width: width,
                        height: height
                    })
                });
                const data = await res.json();
                if (res.ok) {
                    MAP_DATA.layout_data = MAP_DATA.layout_data || {};
                    MAP_DATA.layout_data.background_image_url = floorUrl;
                    MAP_DATA.layout_data.background_width = width;
                    MAP_DATA.layout_data.background_height = height;
                    
                    MAP_WIDTH_PX = width;
                    MAP_HEIGHT_PX = height;
                    
                    BLUEPRINT_IMAGE.src = floorUrl + (floorUrl.includes('?') ? '&' : '?') + 'v=' + Date.now();
                    blueprintLoaded = true;
                    
                    fitAndCenterView();
                    renderFloorsCatalog();
                    showToast('✅ {{ __("Floor Style Applied Successfully (تم تطبيق نمط الأرضية بنجاح)") }}');
                } else {
                    showToast('❌ ' + (data.message || 'Failed to apply floor style'));
                }
            } catch (err) {
                console.error(err);
                showToast('❌ Failed to apply floor style');
            }
        }

        async function handleCustomFloorUpload(input) {
            if (!input.files || !input.files[0]) return;
            const file = input.files[0];
            const formData = new FormData();
            formData.append('image', file);

            showToast('⏳ {{ __("Uploading Custom Floor Image (جاري رفع صورة الأرضية)...") }}');
            try {
                const res = await fetch(`/editor/maps/${MAP_ID}/background`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': CSRF_TOKEN,
                        'Accept': 'application/json'
                    },
                    body: formData
                });
                const data = await res.json();
                if (res.ok && data.image_url) {
                    const uploadedUrl = data.image_url;
                    MAP_DATA.layout_data = MAP_DATA.layout_data || {};
                    MAP_DATA.layout_data.background_image_url = uploadedUrl;
                    
                    BLUEPRINT_IMAGE.src = uploadedUrl + '?v=' + Date.now();
                    BLUEPRINT_IMAGE.onload = () => {
                        blueprintLoaded = true;
                        if (BLUEPRINT_IMAGE.naturalWidth > 0 && BLUEPRINT_IMAGE.naturalHeight > 0) {
                            MAP_WIDTH_PX = BLUEPRINT_IMAGE.naturalWidth;
                            MAP_HEIGHT_PX = BLUEPRINT_IMAGE.naturalHeight;
                        }
                        fitAndCenterView();
                        renderFloorsCatalog();
                        showToast('✅ {{ __("Floor Background Uploaded & Applied (تم تطبيق الأرضية بنجاح)") }}');
                    };
                } else {
                    showToast('❌ ' + (data.message || 'Upload failed'));
                }
            } catch (err) {
                console.error(err);
                showToast('❌ Upload failed');
            }
        }

        async function clearCurrentFloorBackground() {
            if (!confirm('{{ __("Are you sure you want to remove the floor background? (هل أنت متأكد من رغبتك في إزالة صورة الأرضية؟)") }}')) return;
            try {
                const res = await fetch(`/editor/maps/${MAP_ID}/background`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': CSRF_TOKEN,
                        'Accept': 'application/json'
                    }
                });
                if (res.ok) {
                    if (MAP_DATA.layout_data) {
                        delete MAP_DATA.layout_data.background_image_url;
                        MAP_DATA.layout_data.background_width = 1200;
                        MAP_DATA.layout_data.background_height = 708;
                    }
                    blueprintLoaded = false;
                    BLUEPRINT_IMAGE.removeAttribute('src');
                    BLUEPRINT_IMAGE.src = '';
                    MAP_WIDTH_PX = 1200;
                    MAP_HEIGHT_PX = 708;
                    fitAndCenterView();
                    renderFloorsCatalog();
                    draw();
                    showToast('✅ {{ __("Background removed (تمت إزالة صورة الأرضية)") }}');
                }
            } catch (err) {
                console.error(err);
            }
        }

        function switchDrawerTab(tab) {
            document.querySelectorAll('.drawer-tab').forEach(el => el.classList.remove('active'));
            document.getElementById(`tab-btn-${tab}`)?.classList.add('active');
            document.getElementById('drawer-view-furniture').style.display = tab === 'furniture' ? 'flex' : 'none';
            document.getElementById('drawer-view-floors').style.display = tab === 'floors' ? 'flex' : 'none';
            document.getElementById('drawer-view-inspector').style.display = tab === 'inspector' ? 'flex' : 'none';
            document.getElementById('drawer-view-rooms').style.display = tab === 'rooms' ? 'flex' : 'none';
            if (tab === 'floors') renderFloorsCatalog();
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
        function resetView() { fitAndCenterView(); draw(); }
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

            const hasBlueprint = blueprintLoaded && BLUEPRINT_IMAGE && BLUEPRINT_IMAGE.complete && BLUEPRINT_IMAGE.naturalWidth > 0 && BLUEPRINT_IMAGE.src && !BLUEPRINT_IMAGE.src.endsWith('/');

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

                if (badgeW > 16 && rw > 20 && rh > 18) {
                    ctx.fillStyle = 'rgba(15, 23, 42, 0.85)';
                    if (ctx.roundRect) ctx.roundRect(rx + 4, ry + 4, badgeW, 18, 6);
                    else ctx.rect(rx + 4, ry + 4, badgeW, 18);
                    ctx.fill();

                    ctx.strokeStyle = 'rgba(255, 255, 255, 0.15)';
                    ctx.lineWidth = 1;
                    if (ctx.roundRect) ctx.roundRect(rx + 4, ry + 4, badgeW, 18, 6);
                    else ctx.rect(rx + 4, ry + 4, badgeW, 18);
                    ctx.stroke();

                    ctx.fillStyle = '#F8FAFC';
                    ctx.textAlign = 'left';
                    ctx.textBaseline = 'middle';
                    ctx.fillText(labelText, rx + 10, ry + 13);
                }

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

                // If map has blueprint artwork, untextured collision items should not be painted as blue blocks
                if (hasBlueprint && !imgUrl) {
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
                    MAP_DATA.layout_data = MAP_DATA.layout_data || {};
                    MAP_DATA.layout_data.background_image_url = data.image_url;
                    
                    BLUEPRINT_IMAGE.src = newUrl;
                    BLUEPRINT_IMAGE.onload = () => {
                        blueprintLoaded = true;
                        if (BLUEPRINT_IMAGE.naturalWidth > 0 && BLUEPRINT_IMAGE.naturalHeight > 0) {
                            MAP_WIDTH_PX = BLUEPRINT_IMAGE.naturalWidth;
                            MAP_HEIGHT_PX = BLUEPRINT_IMAGE.naturalHeight;
                            MAP_DATA.layout_data.background_width = BLUEPRINT_IMAGE.naturalWidth;
                            MAP_DATA.layout_data.background_height = BLUEPRINT_IMAGE.naturalHeight;
                        }
                        fitAndCenterView();
                        draw();
                    };
                    showToast('✅ {{ __("Floorplan uploaded and active!") }}');
                } else {
                    showToast('❌ ' + (data.message || 'Upload failed'));
                }
            } catch(e) {
                console.error(e);
                showToast('❌ {{ __("Upload error") }}');
            }
        }

        async function deleteFloorplan() {
            if (!confirm('{{ __("Are you sure you want to reset the floorplan to default 1200×708? (هل أنت متأكد من استعادة المخطط الافتراضي؟)") }}')) return;
            showToast('🗑️ {{ __("Resetting floorplan...") }}');
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
                    MAP_DATA.layout_data.background_width = 1200;
                    MAP_DATA.layout_data.background_height = 708;
                    blueprintLoaded = false;
                    BLUEPRINT_IMAGE.removeAttribute('src');
                    BLUEPRINT_IMAGE.src = '';
                    MAP_WIDTH_PX = 1200;
                    MAP_HEIGHT_PX = 708;
                    fitAndCenterView();
                    renderFloorsCatalog();
                    draw();
                    showToast('✅ {{ __("Floorplan reset to default (1200×708)!") }}');
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
                    MAP_DATA.layout_data.background_width = 1200;
                    MAP_DATA.layout_data.background_height = 708;
                    BLUEPRINT_IMAGE.removeAttribute('src');
                    BLUEPRINT_IMAGE.src = '';
                    blueprintLoaded = false;
                    MAP_WIDTH_PX = 1200;
                    MAP_HEIGHT_PX = 708;
                    fitAndCenterView();
                    updateInspector();
                    hideFloatingActions();
                    renderFloorsCatalog();
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

        // ── Dropdown & Menu Handlers ──
        function toggleEditorMainMenu(e) {
            if (e) e.stopPropagation();
            const menu = document.getElementById('editor-main-menu-dropdown');
            const branchDD = document.getElementById('branch-select-dropdown');
            if (branchDD) branchDD.style.display = 'none';
            if (menu) {
                menu.style.display = (menu.style.display === 'none' || menu.style.display === '') ? 'block' : 'none';
            }
        }

        function closeEditorMainMenu() {
            const menu = document.getElementById('editor-main-menu-dropdown');
            if (menu) menu.style.display = 'none';
        }

        function toggleBranchDropdown(e) {
            if (e) e.stopPropagation();
            const menu = document.getElementById('editor-main-menu-dropdown');
            if (menu) menu.style.display = 'none';
            const dd = document.getElementById('branch-select-dropdown');
            if (dd) {
                dd.style.display = (dd.style.display === 'none' || dd.style.display === '') ? 'block' : 'none';
            }
        }

        function closeDropdowns() {
            const d1 = document.getElementById('branch-select-dropdown');
            const d2 = document.getElementById('editor-main-menu-dropdown');
            if (d1) d1.style.display = 'none';
            if (d2) d2.style.display = 'none';
        }

        function toggleAppTheme() {
            const cur = document.documentElement.getAttribute('data-theme') || 'dark';
            const next = cur === 'dark' ? 'light' : 'dark';
            document.documentElement.setAttribute('data-theme', next);
            showToast(next === 'dark' ? '🌙 {{ __("Dark mode active") }}' : '☀️ {{ __("Light mode active") }}');
        }

        document.addEventListener('click', (e) => {
            const menu = document.getElementById('editor-main-menu-dropdown');
            if (menu && menu.style.display === 'block') {
                if (!e.target.closest('#editor-main-menu-dropdown') && !e.target.closest('button[onclick*="toggleEditorMainMenu"]') && !e.target.closest('.nx-brand-capsule')) {
                    menu.style.display = 'none';
                }
            }
            const branchDD = document.getElementById('branch-select-dropdown');
            if (branchDD && branchDD.style.display === 'block') {
                if (!e.target.closest('#branch-select-dropdown') && !e.target.closest('button[onclick*="toggleBranchDropdown"]')) {
                    branchDD.style.display = 'none';
                }
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
