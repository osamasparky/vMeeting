<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">
    <title>{{ $organization->name }} — {{ __('Virtual Interactive Office') }}</title>

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

            --text-primary: var(--nx-sand-100, #f9f4ee);
            --text-secondary: var(--nx-sand-400, #e3d2bb);
            --text-muted: var(--nx-sand-500, #c1b6a6);

            --shadow-dock: var(--nx-shadow-xl);
            --shadow-card: var(--nx-shadow-lg);
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

            --text-primary: #0F172A;
            --text-secondary: #475569;
            --text-muted: #94A3B8;

            --shadow-dock: 0 20px 40px rgba(0, 0, 0, 0.12);
            --shadow-card: 0 10px 25px rgba(0, 0, 0, 0.08);
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
            color: var(--text-primary);
            height: 100vh;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            -webkit-font-smoothing: antialiased;
        }


        /* ── Top Bar Overlay ── */
        .top-bar-overlay {
            position: absolute;
            top: 16px;
            inset-inline-start: 16px;
            inset-inline-end: 16px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            z-index: 50;
            pointer-events: none;
        }
        .top-bar-overlay > * {
            pointer-events: auto;
        }

        .glass-pill {
            background: var(--bg-dock);
            backdrop-filter: blur(24px);
            border: 1px solid var(--border-color);
            border-radius: 16px;
            padding: 8px 16px;
            display: flex;
            align-items: center;
            gap: 10px;
            box-shadow: var(--shadow-card);
        }

        .org-badge {
            font-size: 13px;
            font-weight: 800;
            color: var(--text-primary);
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .org-logo-img {
            width: 24px;
            height: 24px;
            border-radius: 6px;
            object-fit: contain;
            background: rgba(255, 255, 255, 0.08);
            padding: 2px;
        }

        .status-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: var(--brand-primary);
            box-shadow: 0 0 10px var(--brand-primary);
        }

        .guest-badge {
            background: rgba(59, 130, 246, 0.22);
            border: 1px solid #3B82F6;
            color: #93C5FD;
            font-size: 10px;
            font-weight: 800;
            padding: 2px 8px;
            border-radius: 6px;
        }

        .action-link-btn {
            background: var(--bg-input);
            border: 1px solid var(--border-color);
            color: var(--text-primary);
            padding: 6px 12px;
            border-radius: 10px;
            font-size: 12px;
            font-weight: 800;
            text-decoration: none;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .action-link-btn:hover {
            border-color: var(--brand-primary);
            color: var(--brand-primary);
            transform: translateY(-1px);
        }
        .action-link-btn.btn-danger {
            background: rgba(239, 68, 68, 0.15);
            border-color: rgba(239, 68, 68, 0.35);
            color: #F87171;
        }

        /* ── Canvas Viewport ── */
        .canvas-container {
            flex: 1;
            width: 100%;
            height: 100%;
            position: relative;
            background: var(--nx-map-dark-bg);
            overflow: hidden;
            z-index: 1;
        }
        #office-canvas {
            position: absolute;
            top: 0;
            left: 0;
            display: block;
            width: 100%;
            height: 100%;
            cursor: crosshair;
        }

        /* ── Bottom Floating Dock ── */
        .bottom-dock {
            position: absolute;
            bottom: 24px;
            left: 50%;
            transform: translateX(-50%);
            background: var(--bg-dock);
            backdrop-filter: blur(24px);
            border: 1px solid var(--border-color);
            border-radius: 20px;
            padding: 8px 14px;
            display: flex;
            align-items: center;
            gap: 8px;
            box-shadow: var(--shadow-dock);
            z-index: 60;
        }

        .dock-btn {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 3px;
            background: var(--bg-input);
            border: 1px solid var(--border-card);
            border-radius: 12px;
            width: 58px;
            height: 52px;
            color: var(--text-primary);
            font-size: 10px;
            font-weight: 800;
            cursor: pointer;
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .dock-btn span:first-child {
            font-size: 18px;
        }
        .dock-btn:hover {
            border-color: var(--brand-primary);
            color: var(--brand-primary);
            transform: translateY(-2px);
        }
        .dock-btn.active {
            background: rgba(16, 185, 129, 0.18);
            border-color: var(--brand-primary);
            color: #34D399;
            box-shadow: 0 0 14px rgba(16, 185, 129, 0.3);
        }
        .dock-btn.muted {
            background: rgba(239, 68, 68, 0.14);
            border-color: rgba(239, 68, 68, 0.35);
            color: #F87171;
        }

        .dock-divider {
            width: 1px;
            height: 32px;
            background: var(--border-color);
            margin: 0 4px;
        }

        /* ── Local Self Camera Floating Mirror PiP ── */
        .local-cam-card {
            position: absolute;
            bottom: 96px;
            inset-inline-start: 20px;
            width: 190px;
            height: 125px;
            background: rgba(10, 22, 16, 0.95);
            border: 2px solid var(--border-color);
            border-radius: 14px;
            box-shadow: var(--shadow-dock);
            z-index: 65;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            backdrop-filter: blur(16px);
            transition: all 0.2s ease;
        }
        .local-cam-header {
            padding: 4px 8px;
            background: rgba(0, 0, 0, 0.85);
            display: flex;
            align-items: center;
            justify-content: space-between;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            user-select: none;
        }
        .local-cam-viewport {
            flex: 1;
            position: relative;
            background: #000;
            overflow: hidden;
        }
        .local-cam-viewport video {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transform: scaleX(-1); /* Natural Mirror Selfie */
        }

        /* ── Remote Peers Video & Screen Share Matrix Overlay ── */
        .video-grid-overlay {
            position: absolute;
            top: 80px;
            inset-inline-end: 20px;
            display: flex;
            flex-direction: column;
            gap: 12px;
            z-index: 45;
            max-height: calc(100vh - 190px);
            overflow-y: auto;
            pointer-events: none;
            padding: 4px;
        }
        .video-card {
            width: 320px;
            height: 200px;
            background: rgba(10, 22, 16, 0.95);
            border: 2px solid var(--border-color);
            border-radius: 16px;
            overflow: hidden;
            position: relative;
            box-shadow: var(--shadow-dock);
            pointer-events: auto;
            display: flex;
            flex-direction: column;
            transition: width 0.25s cubic-bezier(0.4, 0, 0.2, 1), height 0.25s cubic-bezier(0.4, 0, 0.2, 1), transform 0.2s ease;
            backdrop-filter: blur(16px);
        }
        .video-card.size-small {
            width: 260px;
            height: 160px;
        }
        .video-card.size-medium {
            width: 520px;
            height: 320px;
        }
        .video-card.size-large {
            width: 840px;
            height: 520px;
            max-width: 85vw;
            max-height: 75vh;
        }
        .video-card.collapsed-mode {
            height: 40px !important;
            width: 260px !important;
        }
        .video-card.collapsed-mode .video-wrapper {
            display: none !important;
        }
        .video-card:fullscreen, .video-card:-webkit-full-screen {
            width: 100vw !important;
            height: 100vh !important;
            max-width: 100vw !important;
            max-height: 100vh !important;
            border-radius: 0 !important;
            border: none !important;
        }
        .video-card-topbar {
            padding: 6px 10px;
            background: rgba(0, 0, 0, 0.82);
            backdrop-filter: blur(8px);
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            display: flex;
            align-items: center;
            justify-content: space-between;
            z-index: 10;
            user-select: none;
        }
        .video-card-title {
            display: flex;
            align-items: center;
            gap: 6px;
            font-size: 11px;
            font-weight: 800;
            color: #F8FAFC;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        .live-dot {
            width: 8px;
            height: 8px;
            background: #10B981;
            border-radius: 50%;
            display: inline-block;
            box-shadow: 0 0 8px #10B981;
            animation: pulseDot 1.5s infinite;
        }
        @keyframes pulseDot {
            0%, 100% { opacity: 1; transform: scale(1); }
            50% { opacity: 0.5; transform: scale(0.85); }
        }
        .video-card-actions {
            display: flex;
            align-items: center;
            gap: 3px;
        }
        .v-btn {
            background: rgba(255, 255, 255, 0.08);
            border: 1px solid rgba(255, 255, 255, 0.12);
            color: #F8FAFC;
            width: 24px;
            height: 24px;
            border-radius: 6px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 11px;
            cursor: pointer;
            transition: all 0.15s ease;
        }
        .v-btn:hover {
            background: var(--brand-primary);
            border-color: var(--brand-primary);
            color: white;
            transform: translateY(-1px);
        }
        .v-btn.active {
            background: var(--brand-primary);
            border-color: var(--brand-primary);
            color: white;
        }
        .video-wrapper {
            flex: 1;
            position: relative;
            background: #000;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
        }
        .video-wrapper video {
            width: 100%;
            height: 100%;
            object-fit: contain;
            background: #000;
        }
        .video-card-label {
            position: absolute;
            bottom: 6px;
            inset-inline-start: 8px;
            background: rgba(0, 0, 0, 0.7);
            color: white;
            font-size: 10px;
            font-weight: 800;
            padding: 2px 8px;
            border-radius: 6px;
        }

        /* ── Sliding Chat Drawer ── */
        .chat-drawer {
            position: absolute;
            top: 80px;
            bottom: 96px;
            inset-inline-start: 20px;
            width: 340px;
            background: var(--bg-surface);
            backdrop-filter: blur(24px);
            border: 1px solid var(--border-color);
            border-radius: 20px;
            box-shadow: var(--shadow-dock);
            display: none;
            flex-direction: column;
            z-index: 55;
            overflow: hidden;
        }
        .chat-header {
            padding: 14px 16px;
            border-bottom: 1px solid var(--border-color);
            display: flex;
            align-items: center;
            justify-content: space-between;
            background: var(--bg-dock);
        }
        .chat-tabs {
            display: flex;
            padding: 4px;
            background: var(--bg-input);
            border-radius: 10px;
            margin: 8px 12px;
            gap: 4px;
        }
        .chat-tab {
            flex: 1;
            text-align: center;
            padding: 6px 4px;
            font-size: 11px;
            font-weight: 800;
            border-radius: 6px;
            color: var(--text-secondary);
            cursor: pointer;
            transition: all 0.15s;
        }
        .chat-tab.active {
            background: var(--brand-primary);
            color: white;
        }
        .chat-messages {
            flex: 1;
            overflow-y: auto;
            padding: 12px 14px;
            display: flex;
            flex-direction: column;
            gap: 8px;
        }
        .msg-bubble {
            background: var(--bg-card);
            border: 1px solid var(--border-card);
            border-radius: 10px;
            padding: 8px 12px;
            font-size: 12px;
            display: flex;
            flex-direction: column;
            gap: 3px;
        }
        .msg-bubble.self {
            border-color: rgba(16, 185, 129, 0.4);
            background: rgba(16, 185, 129, 0.12);
        }
        .msg-meta {
            display: flex;
            justify-content: space-between;
            font-size: 10px;
            font-weight: 800;
            color: var(--brand-primary);
        }
        .chat-input-bar {
            padding: 10px 12px;
            border-top: 1px solid var(--border-color);
            background: var(--bg-dock);
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .reaction-emoji-btn {
            background: none;
            border: none;
            font-size: 22px;
            cursor: pointer;
            padding: 4px;
            border-radius: 50%;
            transition: transform 0.15s cubic-bezier(0.34, 1.56, 0.64, 1), background 0.15s ease;
            line-height: 1;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .reaction-emoji-btn:hover {
            transform: scale(1.35) translateY(-2px);
            background: rgba(255, 255, 255, 0.15);
        }
        .reaction-emoji-btn:active {
            transform: scale(0.95);
        }

        .more-menu-item {
            background: transparent;
            border: none;
            color: var(--text-primary);
            padding: 9px 12px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 12px;
            font-weight: 700;
            cursor: pointer;
            width: 100%;
            text-align: start;
            transition: all 0.15s ease;
        }
        .more-menu-item:hover {
            background: rgba(255, 255, 255, 0.1);
            color: #6EE7B7;
            transform: translateX(3px);
        }

        /* ── In-Office Task Drawer & Quick Timer ── */
        .task-drawer {
            position: absolute;
            top: 70px;
            inset-inline-start: 16px;
            width: 360px;
            height: calc(100vh - 165px);
            background: var(--bg-dock);
            backdrop-filter: blur(28px);
            border: 1px solid var(--border-color);
            border-radius: 20px;
            box-shadow: var(--shadow-dock);
            z-index: 60;
            display: none;
            flex-direction: column;
            overflow: hidden;
            animation: drawerSlideIn 0.25s cubic-bezier(0.16, 1, 0.3, 1);
        }

        .task-card-item {
            background: var(--bg-card);
            border: 1px solid var(--border-card);
            border-radius: 12px;
            padding: 12px;
            display: flex;
            flex-direction: column;
            gap: 8px;
            transition: all 0.2s ease;
        }
        .task-card-item:hover {
            border-color: rgba(52, 211, 153, 0.4);
            transform: translateY(-1px);
        }
        .task-card-item.running {
            background: rgba(16, 185, 129, 0.14);
            border-color: #10B981;
            box-shadow: 0 0 16px rgba(16, 185, 129, 0.25);
        }

        .dock-timer-pill {
            display: none;
            align-items: center;
            gap: 8px;
            padding: 6px 14px;
            border-radius: 9999px;
            background: rgba(16, 185, 129, 0.92);
            backdrop-filter: blur(14px);
            border: 1px solid rgba(255, 255, 255, 0.35);
            color: #FFFFFF;
            font-size: 11px;
            font-weight: 800;
            cursor: pointer;
            box-shadow: 0 6px 20px rgba(16, 185, 129, 0.45);
            transition: all 0.2s ease;
            animation: pulseGlow 2s infinite alternate;
        }
        .dock-timer-pill:hover {
            transform: scale(1.04);
        }

        @keyframes pulseGlow {
            from { box-shadow: 0 4px 14px rgba(16, 185, 129, 0.35); }
            to { box-shadow: 0 6px 24px rgba(16, 185, 129, 0.65); }
        }

        /* ── Modals & Drawers ── */
        .modal-overlay {
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, 0.75);
            backdrop-filter: blur(12px);
            display: none;
            align-items: center;
            justify-content: center;
            z-index: 1000;
            animation: fadeIn 0.2s ease;
        }
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        .modal-card {
            background: var(--bg-surface);
            border: 1px solid var(--border-color);
            border-radius: 24px;
            width: 90vw;
            max-width: 600px;
            max-height: 85vh;
            overflow-y: auto;
            padding: 24px;
            display: flex;
            flex-direction: column;
            gap: 16px;
            box-shadow: var(--shadow-dock);
        }

        .modal-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            border-bottom: 1px solid var(--border-color);
            padding-bottom: 12px;
        }
        .modal-title {
            font-size: 16px;
            font-weight: 900;
            color: var(--text-primary);
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .input-group {
            display: flex;
            flex-direction: column;
            gap: 6px;
        }
        .input-label {
            font-size: 11px;
            font-weight: 800;
            color: var(--text-secondary);
            text-transform: uppercase;
        }
        .styled-input {
            width: 100%;
            background: var(--bg-input);
            border: 1px solid var(--border-color);
            border-radius: 10px;
            padding: 10px 14px;
            color: var(--text-primary);
            font-size: 13px;
            font-weight: 700;
            outline: none;
        }
        .styled-input:focus {
            border-color: var(--brand-primary);
        }

        /* Avatar Picker Grid */
        .avatar-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 14px;
        }
        .avatar-card-picker {
            background: var(--bg-card);
            border: 2px solid var(--border-color);
            border-radius: 16px;
            padding: 14px;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 10px;
            cursor: pointer;
            transition: all 0.2s;
        }
        .avatar-card-picker:hover, .avatar-card-picker.selected {
            border-color: var(--brand-primary);
            background: rgba(16, 185, 129, 0.12);
            box-shadow: 0 0 16px rgba(16, 185, 129, 0.25);
        }
        .avatar-preview-img {
            width: 100px;
            height: 140px;
            border-radius: 12px;
            object-fit: contain;
            filter: drop-shadow(0 6px 12px rgba(0, 0, 0, 0.15));
        }

        /* ── Knock Alert Dialog ── */
        .knock-alert-box {
            background: linear-gradient(135deg, rgba(16, 185, 129, 0.15), rgba(59, 130, 246, 0.15));
            border: 1px solid var(--brand-primary);
            border-radius: 16px;
            padding: 16px;
            display: flex;
            flex-direction: column;
            gap: 12px;
            text-align: center;
        }

        /* ── Whiteboard Toolbar ── */
        .wb-toolbar {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 10px 16px;
            background: var(--bg-dock);
            border-bottom: 1px solid var(--border-color);
            overflow-x: auto;
        }
        .wb-tool-btn {
            background: var(--bg-input);
            border: 1px solid var(--border-card);
            color: var(--text-primary);
            width: 34px;
            height: 34px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 14px;
            cursor: pointer;
            transition: all 0.15s;
        }
        .wb-tool-btn:hover, .wb-tool-btn.active {
            background: var(--brand-primary);
            color: white;
            border-color: var(--brand-primary);
        }
        .color-dot {
            width: 22px;
            height: 22px;
            border-radius: 50%;
            cursor: pointer;
            border: 2px solid transparent;
            transition: transform 0.15s;
        }
        .color-dot:hover, .color-dot.active {
            transform: scale(1.2);
            border-color: white;
        }

        /* ── Toast Notifications ── */
        .toast-bubble {
            position: fixed;
            bottom: 90px;
            left: 50%;
            transform: translateX(-50%);
            background: rgba(16, 185, 129, 0.95);
            backdrop-filter: blur(12px);
            color: white;
            padding: 10px 20px;
            border-radius: 14px;
            font-size: 13px;
            font-weight: 800;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.4);
            display: none;
            z-index: 10000;
        }
    </style>
</head>
<body>

    <div class="nx-office-viewport-container">
        <div class="nx-floor-map-screen">

        <!-- ── Top Floating Overlay Bar (UlaSpace Figma Floor Map Spec) ── -->
        <header class="nx-map-toolbar">
            <!-- 1. Start Group (Top Right on RTL): Burger Menu + Brand Lockup + Branch Switcher -->
            <div class="nx-toolbar-group">
                <!-- Main App Burger Menu Dropdown -->
                <div style="position: relative; display: inline-block;">
                    <button type="button" onclick="toggleOfficeMainMenu(event)" class="nx-toolbar-btn" style="padding: 6px 10px;" title="{{ __('Menu') }}">
                        <span class="material-symbols-rounded" style="font-size: 20px;">menu</span>
                    </button>
                    
                    <div id="office-main-menu-dropdown" style="display: none; position: absolute; top: calc(100% + 8px); inset-inline-start: 0; min-width: 260px; background: rgba(14, 25, 19, 0.98); backdrop-filter: blur(24px); border: 1px solid rgba(237, 230, 217, 0.20); border-radius: 16px; box-shadow: 0 16px 40px rgba(0,0,0,0.65); padding: 8px; z-index: 100000;">
                        <!-- Menu Header with User / Brand Info -->
                        <div style="display: flex; align-items: center; gap: 10px; padding: 8px 10px 12px; border-bottom: 1px solid rgba(237, 230, 217, 0.12); margin-bottom: 6px;">
                            @if(!empty($organization->logo_url))
                                <img src="{{ $organization->logo_url }}" alt="{{ $organization->name }}" style="height: 24px; width: auto; object-fit: contain;">
                            @else
                                <span class="material-symbols-rounded" style="color: var(--nx-map-gold); font-size: 24px;">apartment</span>
                            @endif
                            <div style="overflow: hidden;">
                                <strong style="display: block; font-size: 13px; color: var(--nx-map-text); white-space: nowrap; text-overflow: ellipsis; overflow: hidden;">{{ $organization->name }}</strong>
                                <span style="font-size: 11px; color: var(--nx-map-muted);">{{ $user->name ?? 'User' }}</span>
                            </div>
                        </div>

                        <!-- Menu Actions -->
                        @if(empty($user->is_guest))
                        <a href="{{ route('dashboard') }}" class="more-menu-item" style="display: flex; align-items: center; gap: 10px; padding: 8px 12px; border-radius: 8px; text-decoration: none; color: #F9F4EE; font-size: 12px; font-weight: 600; transition: background 0.15s ease;">
                            <span class="material-symbols-rounded" style="font-size: 18px; color: var(--nx-map-gold);">dashboard</span>
                            <span>{{ __('Dashboard') }}</span>
                        </a>
                        @endif

                        @if(session('superadmin_impersonator_id'))
                        <form method="POST" action="{{ route('impersonate.leave') }}" style="margin: 0;">
                            @csrf
                            <button type="submit" class="more-menu-item" style="width: 100%; display: flex; align-items: center; gap: 10px; padding: 8px 12px; border-radius: 8px; background: none; border: none; color: #93C5FD; font-size: 12px; font-weight: 600; cursor: pointer; text-align: start;">
                                <span class="material-symbols-rounded" style="font-size: 18px;">shield</span>
                                <span>{{ __('Return to Super Admin') }}</span>
                            </button>
                        </form>
                        @endif

                        @if(!empty($user) && in_array($user->role ?? 'member', ['superadmin', 'company_admin', 'manager', 'admin']))
                        <label class="more-menu-item" style="display: flex; align-items: center; gap: 10px; padding: 8px 12px; border-radius: 8px; color: #F59E0B; font-size: 12px; font-weight: 600; cursor: pointer; margin: 0;">
                            <span class="material-symbols-rounded" style="font-size: 18px; color: #F59E0B;">upload_file</span>
                            <span>{{ __('Upload Floor Image') }}</span>
                            <input type="file" accept="image/*" style="display:none;" onchange="uploadOfficeFloorImage(this); closeOfficeMainMenu();">
                        </label>

                        <a href="{{ route('editor') }}" class="more-menu-item" style="display: flex; align-items: center; gap: 10px; padding: 8px 12px; border-radius: 8px; text-decoration: none; color: #86EFAC; font-size: 12px; font-weight: 600;">
                            <span class="material-symbols-rounded" style="font-size: 18px;">draw</span>
                            <span>{{ __('Map Editor') }}</span>
                        </a>
                        @endif

                        <button type="button" onclick="openDiagnosticsModal(); closeOfficeMainMenu();" class="more-menu-item" style="width: 100%; display: flex; align-items: center; gap: 10px; padding: 8px 12px; border-radius: 8px; background: none; border: none; color: #F9F4EE; font-size: 12px; font-weight: 600; cursor: pointer; text-align: start;">
                            <span class="material-symbols-rounded" style="font-size: 18px;">network_check</span>
                            <span>{{ __('Diagnostics') }}</span>
                        </button>

                        <button type="button" onclick="toggleChatDrawer(); closeOfficeMainMenu();" class="more-menu-item" style="width: 100%; display: flex; align-items: center; gap: 10px; padding: 8px 12px; border-radius: 8px; background: none; border: none; color: #F9F4EE; font-size: 12px; font-weight: 600; cursor: pointer; text-align: start;">
                            <span class="material-symbols-rounded" style="font-size: 18px;">chat</span>
                            <span>{{ __('Chat & Notes') }}</span>
                        </button>

                        <button type="button" onclick="toggleAppTheme(); closeOfficeMainMenu();" class="more-menu-item" style="width: 100%; display: flex; align-items: center; gap: 10px; padding: 8px 12px; border-radius: 8px; background: none; border: none; color: #F9F4EE; font-size: 12px; font-weight: 600; cursor: pointer; text-align: start;">
                            <span class="material-symbols-rounded" style="font-size: 18px;">light_mode</span>
                            <span>{{ __('Toggle Theme') }}</span>
                        </button>

                        @if(app()->getLocale() === 'ar')
                            <a href="{{ route('lang.switch', 'en') }}" class="more-menu-item" style="display: flex; align-items: center; gap: 10px; padding: 8px 12px; border-radius: 8px; text-decoration: none; color: #F9F4EE; font-size: 12px; font-weight: 600;">
                                <span class="material-symbols-rounded" style="font-size: 18px;">language</span>
                                <span>English</span>
                            </a>
                        @else
                            <a href="{{ route('lang.switch', 'ar') }}" class="more-menu-item" style="display: flex; align-items: center; gap: 10px; padding: 8px 12px; border-radius: 8px; text-decoration: none; color: #F9F4EE; font-size: 12px; font-weight: 600;">
                                <span class="material-symbols-rounded" style="font-size: 18px;">language</span>
                                <span>العربية</span>
                            </a>
                        @endif
                    </div>
                </div>

                <!-- Brand Capsule with Logo -->
                <div class="nx-brand-capsule" onclick="toggleOfficeMainMenu(event)" style="cursor: pointer;" title="{{ __('Click to open menu') }}">
                    <span class="nx-presence-dot"></span>
                    @if(!empty($organization->logo_url))
                        <img src="{{ $organization->logo_url }}" alt="{{ $organization->name }}" style="height: 18px; width: auto; object-fit: contain;">
                    @elseif(!empty($organization->settings?->logo_url))
                        <img src="{{ $organization->settings->logo_url }}" alt="{{ $organization->name }}" style="height: 18px; width: auto; object-fit: contain;">
                    @else
                        <span class="material-symbols-rounded" style="color: var(--nx-map-gold); font-size: 18px;">apartment</span>
                    @endif
                    <span>{{ $organization->name }}</span>
                </div>

                <!-- Branch / Floor Switcher Button -->
                @if(isset($userAllowedOffices) && $userAllowedOffices->count() > 1 && empty($user->is_guest))
                <div style="position: relative; display: inline-block;">
                    <button type="button" onclick="toggleOfficeDropdown(event)" class="nx-toolbar-btn" style="color: var(--nx-map-gold); border-color: rgba(211, 165, 83, 0.35); font-weight: 600;" title="{{ __('Switch Office Branch') }}">
                        <span class="material-symbols-rounded" style="font-size: 18px;">domain</span>
                        <span>{{ $floor->name }}</span>
                        <span class="material-symbols-rounded" style="font-size: 16px;">arrow_drop_down</span>
                    </button>
                    <div id="office-switcher-dropdown" style="display: none; position: absolute; top: calc(100% + 8px); inset-inline-start: 0; min-width: 250px; background: rgba(14, 25, 19, 0.98); backdrop-filter: blur(18px); border: 1px solid rgba(237, 230, 217, 0.20); border-radius: 14px; box-shadow: 0 16px 36px rgba(0,0,0,0.65); padding: 6px; z-index: 100000;">
                        <div style="font-size: 10px; font-weight: 900; text-transform: uppercase; letter-spacing: 0.5px; color: rgba(255,255,255,0.5); padding: 6px 10px; border-bottom: 1px solid rgba(255,255,255,0.08); margin-bottom: 4px;">
                            🏢 {{ __('Office Branches') }}
                        </div>
                        @foreach($userAllowedOffices as $off)
                        @php
                            $offMap = $off->activeMap ?: $off->maps->first();
                            $offMapId = $offMap ? $offMap->id : '';
                        @endphp
                        <a href="{{ route('office', ['office' => $off->id]) }}" style="display: flex; align-items: center; justify-content: space-between; gap: 8px; padding: 8px 12px; border-radius: 8px; text-decoration: none; color: {{ $off->id === $floor->id ? '#86EFAC' : '#E2E8F0' }}; background: {{ $off->id === $floor->id ? 'rgba(36, 92, 58, 0.45)' : 'transparent' }}; font-weight: 700; font-size: 12px; transition: background 0.15s ease;">
                            <div style="display: flex; align-items: center; gap: 8px;">
                                <span class="material-symbols-rounded" style="font-size: 16px;">apartment</span>
                                <span>{{ $off->name }}</span>
                            </div>
                            <div style="display: flex; align-items: center; gap: 6px;">
                                <span class="branch-occupants-badge" data-map-id="{{ $offMapId }}" style="font-size: 10px; padding: 2px 6px; border-radius: 6px; background: rgba(255,255,255,0.05); color: #94A3B8; font-weight: 700;">
                                    <span style="display: inline-block; width: 6px; height: 6px; border-radius: 50%; background: {{ $off->id === $floor->id ? '#10B981' : '#64748B' }}; margin-inline-end: 4px;"></span>
                                    {{ $off->id === $floor->id ? __('Current') : __('0 active') }}
                                </span>
                                @if($off->id === $floor->id)
                                    <span style="font-size: 10px; color: #86EFAC;">●</span>
                                @endif
                            </div>
                        </a>
                        @endforeach
                    </div>
                </div>
                @endif

                @if(!empty($user->is_guest))
                    <span class="nx-toolbar-btn btn-accent guest-access-pill" style="font-weight: 700;" title="{{ __('Guest Access') }}">
                        🛡️ {{ __('Guest Access') }} ({{ $user->name }})
                    </span>
                @endif
            </div>

            <!-- 2. Center: Active Room Scrim Capsule (Room Name, Room Files, Door Lock) -->
            <div class="nx-map-room-label" id="room-status-pill" style="display: none;">
                <span id="current-room-name" style="font-weight: 600; font-size: 12px; color: #FFFFFF; display: flex; align-items: center; gap: 6px;">
                    <span class="material-symbols-rounded" style="font-size: 18px;">meeting_room</span>
                    <span>{{ __('Meeting Room') }}</span>
                </span>
                
                <button onclick="openRoomFilesModal()" id="btn-room-files" class="nx-toolbar-btn" style="height: 28px; padding: 2px 10px; font-size: 11px;">
                    <span class="material-symbols-rounded" style="font-size: 15px;">folder_open</span>
                    <span>{{ __('Room Files') }}</span>
                </button>

                @if(empty($user->is_guest))
                <button onclick="toggleRoomDoorLock()" id="btn-lock-room" class="nx-toolbar-btn" style="height: 28px; padding: 2px 10px; font-size: 11px;">
                    <span id="lock-icon" class="material-symbols-rounded" style="font-size: 15px;">lock_open</span>
                    <span id="lock-text">{{ __('Lock Door') }}</span>
                </button>
                @endif
            </div>

            <!-- 3. End Group (Top Left on RTL): Workhour Clock + Presence + Invite -->
            <div class="nx-toolbar-group">
                <!-- Live Office Attendance Timer -->
                @if(empty($user->is_guest))
                <div id="office-attendance-timer-pill" class="nx-toolbar-btn" style="background: rgba(60, 107, 76, 0.25); border-color: rgba(60, 107, 76, 0.5); color: #86EFAC; font-weight: 600; cursor: pointer;" onclick="openMyTaskDrawer()" title="{{ __('Your active time in the virtual office today') }}">
                    <span class="nx-presence-dot"></span>
                    <span class="material-symbols-rounded" style="font-size: 16px;">schedule</span>
                    <span id="office-attendance-clock" style="font-family: 'IBM Plex Mono', monospace; font-size: 12px;">00:00:00</span>
                </div>
                @endif

                <button onclick="openOccupantsModal()" class="nx-presence-capsule" id="btn-occupants-pill" title="{{ __('Office Occupants') }}" style="cursor: pointer; border: 1px solid rgba(60, 107, 76, 0.4);">
                    <span class="nx-presence-dot"></span>
                    <span class="material-symbols-rounded" style="font-size: 16px;">group</span>
                    <span id="occupants-counter">1 {{ __('Online') }}</span>
                </button>

                <button onclick="openGuestInviteModal()" class="nx-toolbar-btn btn-accent" title="{{ __('Invite Guest') }}">
                    <span class="material-symbols-rounded" style="font-size: 16px;">person_add</span>
                    <span>{{ __('Invite') }}</span>
                </button>
            </div>
        </header>
 
    <!-- ── System Card Toast Notifications Container ── -->
    <div id="nx-toast-container" class="nx-toast-container" aria-live="polite"></div>

    <!-- ── Interactive Canvas Viewport ── -->
    <div class="canvas-container" id="canvas-container">
        <canvas id="office-canvas"></canvas>
    </div>

    <!-- ── Floating Canvas Viewport Zoom & Navigation Controls ── -->
    <div class="nx-floating-viewport-controls" aria-label="{{ __('Map Zoom & Navigation Controls') }}">
        <button type="button" class="nx-viewport-ctrl-btn" onclick="zoomIn()" title="{{ __('Zoom In') }}">
            <span class="material-symbols-rounded">zoom_in</span>
        </button>
        <button type="button" class="nx-viewport-ctrl-btn" onclick="fitMapToCanvas()" title="{{ __('Fit Map to Canvas') }}">
            <span class="material-symbols-rounded">aspect_ratio</span>
        </button>
        <button type="button" class="nx-viewport-ctrl-btn" onclick="locateMe()" title="{{ __('Locate Me') }}">
            <span class="material-symbols-rounded" style="color: #34D399;">location_on</span>
        </button>
        <button type="button" class="nx-viewport-ctrl-btn" onclick="zoomOut()" title="{{ __('Zoom Out') }}">
            <span class="material-symbols-rounded">zoom_out</span>
        </button>
    </div>

    <!-- ── Floating Local Self Camera PiP ── -->
    <div class="local-cam-card" id="local-video-card" style="display: none;">
        <div class="local-cam-header">
            <span style="font-size: 10px; font-weight: 800; color: #F8FAFC; display: flex; align-items: center; gap: 4px;">
                <span class="live-dot" style="width: 6px; height: 6px;"></span>
                📹 {{ $user->name ?? __('You') }}
            </span>
            <button onclick="toggleCamera()" style="background:none; border:none; color:var(--text-muted); cursor:pointer; font-size:12px; line-height: 1;" title="{{ __('Stop Camera') }}">✕</button>
        </div>
        <div class="local-cam-viewport">
            <video id="local-video-elem" autoplay playsinline muted></video>
        </div>
    </div>

    <!-- ── Remote Video Grid Matrix Overlay (WebRTC Multi-Peer + Screen Share) ── -->
    <div class="video-grid-overlay" id="video-grid">
        <!-- Remote peers video cards appended dynamically here -->
    </div>

    <!-- ── Sliding Chat & File Sharing Drawer ── -->
    <div class="chat-drawer" id="chat-drawer">
        <div class="chat-header">
            <strong style="font-size: 13px; display: flex; align-items: center; gap: 6px;">💬 {{ __('Office & Room Chat') }}</strong>
            <div style="display: flex; align-items: center; gap: 6px;">
                <button onclick="focusActiveScreenShare()" class="action-link-btn" id="btn-chat-focus-screen" style="display: none; padding: 3px 8px; font-size: 10px; color: #34D399; border-color: rgba(52, 211, 153, 0.4);" title="{{ __('View Screen Share') }}">
                    🖥️ {{ __('Screen') }}
                </button>
                <button onclick="toggleChatDrawer()" style="background:none; border:none; color:var(--text-muted); font-size:16px; cursor:pointer;">✕</button>
            </div>
        </div>
        <div class="chat-tabs">
            <div class="chat-tab active" id="chat-tab-room" onclick="switchChatScope('room')">🏢 {{ __('Room Chat') }}</div>
            <div class="chat-tab" id="chat-tab-global" onclick="switchChatScope('global')">🌐 {{ __('Global Chat') }}</div>
        </div>
        <div class="chat-messages" id="chat-messages-container">
            <div class="msg-bubble">
                <div class="msg-meta"><span>🤖 {{ __('Smart Assistant') }}</span> <span>{{ date('H:i') }}</span></div>
                <span>{{ __('Welcome to your virtual workplace! Use chat to communicate and share notes with your team.') }}</span>
            </div>
        </div>
        <div class="chat-input-bar">
            <input type="file" id="chat-file-input" style="display:none;" onchange="handleChatFileUpload(this)">
            <button onclick="document.getElementById('chat-file-input').click()" class="action-link-btn" style="padding: 6px 8px;" title="{{ __('Attach File') }}">📎</button>
            <input type="text" id="chat-msg-input" placeholder="{{ __('Type your message here...') }}" class="styled-input" style="padding: 8px 10px; font-size: 12px;" onkeydown="if(event.key==='Enter') sendChatMessage()">
            <button onclick="sendChatMessage()" class="action-link-btn" style="background: var(--brand-primary); color: white; padding: 6px 12px;">➤</button>
        </div>
    </div>

    <!-- ── Bottom Meeting Control Bar (All Tools Restored & Styled) ── -->
    <nav class="nx-meeting-dock" aria-label="{{ __('Meeting Controls') }}">
        <button class="nx-dock-btn" id="btn-screen" onclick="toggleScreenShare()" title="{{ __('Screen Share') }}">
            <span id="screen-icon" class="material-symbols-rounded">screen_share</span>
            <span id="screen-text">{{ __('Share') }}</span>
        </button>

        <button class="nx-dock-btn muted" id="btn-cam" onclick="toggleCamera()" title="{{ __('Camera') }}">
            <span id="cam-icon" class="material-symbols-rounded">videocam_off</span>
            <span id="cam-text">{{ __('Camera') }}</span>
        </button>

        <button class="nx-dock-btn muted" id="btn-mic" onclick="toggleMicrophone()" title="{{ __('Microphone') }}">
            <span id="mic-icon" class="material-symbols-rounded">mic_off</span>
            <span id="mic-text">{{ __('Microphone') }}</span>
        </button>

        <button class="nx-dock-btn" id="btn-chat-dock" onclick="toggleChatDrawer()" title="{{ __('Chat & Notes') }}">
            <span class="material-symbols-rounded">chat</span>
            <span>{{ __('Chat') }}</span>
        </button>

        <button class="nx-dock-btn" id="btn-occupants-dock" onclick="openOccupantsModal()" title="{{ __('Participants') }}">
            <span class="material-symbols-rounded">group</span>
            <span>{{ __('Participants') }}</span>
        </button>

        <button class="nx-dock-btn" id="btn-react-dock" onclick="toggleReactionMenu(event)" title="{{ __('Reactions') }}">
            <span class="material-symbols-rounded">add_reaction</span>
            <span>{{ __('Reactions') }}</span>
        </button>

        <div class="nx-dock-divider"></div>

        <button class="nx-dock-btn" id="btn-tasks-dock" onclick="openMyTaskDrawer()" title="{{ __('My Tasks & Time Tracking') }}">
            <span class="material-symbols-rounded">task_alt</span>
            <span>{{ __('Tasks') }}</span>
        </button>

        <button class="nx-dock-btn" id="btn-whiteboard-dock" onclick="openWhiteboardModal()" title="{{ __('Collaborative Whiteboard') }}">
            <span class="material-symbols-rounded">draw</span>
            <span>{{ __('Whiteboard') }}</span>
        </button>

        <button class="nx-dock-btn" id="btn-record" onclick="toggleRecording()" title="{{ __('Record Session') }}">
            <span id="rec-icon" class="material-symbols-rounded">radio_button_checked</span>
            <span id="rec-text">{{ __('Record') }}</span>
        </button>

        <button class="nx-dock-btn" id="btn-more-dock" onclick="toggleMoreMenu(event)" title="{{ __('More Tools & Settings') }}">
            <span class="material-symbols-rounded">more_horiz</span>
            <span>{{ __('More') }}</span>
        </button>

        <!-- Floating Live Task Timer Pill In Dock -->
        <div id="floating-task-timer-pill" class="nx-toolbar-btn" style="display: none; background: rgba(211, 165, 83, 0.2); border-color: var(--nx-map-gold); color: var(--nx-map-gold); height: 46px; padding: 4px 10px; cursor: pointer; border-radius: 12px; flex-direction: column; justify-content: center; gap: 2px;" onclick="openMyTaskDrawer()" title="{{ __('Click to manage active task') }}">
            <div style="display: flex; align-items: center; gap: 4px;">
                <span class="material-symbols-rounded" style="font-size: 15px;">timer</span>
                <span id="dock-timer-task-name" style="max-width: 90px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; font-size: 10px; font-weight: 700;">{{ __('Task') }}</span>
            </div>
            <span id="dock-timer-clock" style="font-family: 'IBM Plex Mono', monospace; font-size: 10px; background: rgba(0,0,0,0.35); padding: 1px 5px; border-radius: 4px;">00:00:00</span>
        </div>

        <div class="nx-dock-divider"></div>

        <!-- Leave / Exit Office Button (Terracotta / Orange Accent matching Figma) -->
        <a href="{{ route('dashboard') }}" class="nx-dock-btn nx-dock-btn-leave" title="{{ __('Leave Office & Return to Dashboard') }}">
            <span class="material-symbols-rounded">phone_disabled</span>
            <span>{{ __('Leave') }}</span>
        </a>
    </nav>

    <!-- ── Floating More Tools & Settings Popover Menu ── -->
    <div id="floating-more-popover" style="display: none; position: absolute; bottom: 85px; left: 65%; transform: translateX(-50%); background: rgba(15, 23, 42, 0.96); backdrop-filter: blur(20px); border: 1px solid rgba(255,255,255,0.18); border-radius: 18px; padding: 8px; flex-direction: column; gap: 4px; box-shadow: 0 16px 36px rgba(0,0,0,0.6); z-index: 100000; min-width: 220px;">
        <button class="more-menu-item" onclick="toggleCameraGalleryModal(); closeMoreMenu();">
            <span>🎥</span> <span>{{ __('Live Camera Grid') }}</span>
        </button>
        <button class="more-menu-item" onclick="openRecordingsGallery(); closeMoreMenu();">
            <span>📼</span> <span>{{ __('Recordings Library') }}</span>
        </button>
    </div>

    <!-- ── Floating In-World Contextual Prompts & Menus ── -->
    <div id="furniture-sit-prompt" style="display: none; position: absolute; bottom: 85px; left: 50%; transform: translateX(-50%); background: rgba(14, 25, 19, 0.94); backdrop-filter: blur(20px); border: 1px solid rgba(211, 165, 83, 0.45); border-radius: 24px; padding: 6px 18px; color: var(--nx-sand-100, #F9F4EE); font-size: 12px; font-weight: 700; box-shadow: 0 12px 32px rgba(0, 0, 0, 0.5); z-index: 9999; pointer-events: none; transition: opacity 0.2s ease;">
        <span id="furniture-sit-prompt-text">🪑 {{ __('Press') }} <kbd style="background: rgba(211, 165, 83, 0.25); border: 1px solid rgba(211, 165, 83, 0.4); padding: 2px 7px; border-radius: 6px; font-family: monospace; font-size: 11px; color: #D3A553;">E</kbd> {{ __('to Sit at Desk') }}</span>
    </div>

    <div id="floating-reaction-popover" style="display: none; position: absolute; bottom: 85px; left: 50%; transform: translateX(-50%); background: rgba(15, 23, 42, 0.96); backdrop-filter: blur(20px); border: 1px solid rgba(255,255,255,0.18); border-radius: 32px; padding: 6px 14px; align-items: center; gap: 8px; box-shadow: 0 16px 36px rgba(0,0,0,0.6); z-index: 100000;">
        <button class="reaction-emoji-btn" onclick="sendEmojiReaction('👋')" title="{{ __('Wave') }}">👋</button>
        <button class="reaction-emoji-btn" onclick="sendEmojiReaction('👍')" title="{{ __('Thumbs Up') }}">👍</button>
        <button class="reaction-emoji-btn" onclick="sendEmojiReaction('☕')" title="{{ __('Coffee Break') }}">☕</button>
        <button class="reaction-emoji-btn" onclick="sendEmojiReaction('💡')" title="{{ __('Idea') }}">💡</button>
        <button class="reaction-emoji-btn" onclick="sendEmojiReaction('👏')" title="{{ __('Applause') }}">👏</button>
        <button class="reaction-emoji-btn" onclick="sendEmojiReaction('❓')" title="{{ __('Question') }}">❓</button>
        <button class="reaction-emoji-btn" onclick="sendEmojiReaction('🔥')" title="{{ __('Great Work') }}">🔥</button>
    </div>

        </div>
    </div>

@include('office.partials.modals')

        <script src="/js/webrtc/livekit-client.umd.min.js" nonce="{{ $cspNonce ?? '' }}"></script>
    <script src="/js/webrtc/webrtc-manager.js" nonce="{{ $cspNonce ?? '' }}"></script>

    <!-- ── JavaScript Realtime Engine, LiveKit SFU & Spatial Audio Pipeline ── -->
    <script nonce="{{ $cspNonce ?? '' }}">
        const CONFIG = {
            map: @json($map),
            currentUser: @json($user),
            org: @json($organization),
            allowedRoomIds: @json($userAllowedRoomIds ?? []),
            token: "{{ $realtimeToken }}",
            csrf: "{{ csrf_token() }}",
            wsUrl: @json($wsUrl ?? null),
            attendancePolicy: @json($attendancePolicy),
        };

        const canvas = document.getElementById('office-canvas');
        const ctx = canvas.getContext('2d');
        const container = document.getElementById('canvas-container');

        let width = canvas.width = (container && container.clientWidth) ? container.clientWidth : window.innerWidth;
        let height = canvas.height = (container && container.clientHeight) ? container.clientHeight : window.innerHeight;

        const TILE_SIZE = (CONFIG.map && CONFIG.map.tile_size) ? Number(CONFIG.map.tile_size) : 16;
        let MAP_WIDTH_PX = (CONFIG.map && CONFIG.map.layout_data && CONFIG.map.layout_data.background_width && Number(CONFIG.map.layout_data.background_width) >= 500)
            ? Number(CONFIG.map.layout_data.background_width)
            : 1200;
        let MAP_HEIGHT_PX = (CONFIG.map && CONFIG.map.layout_data && CONFIG.map.layout_data.background_height && Number(CONFIG.map.layout_data.background_height) >= 500)
            ? Number(CONFIG.map.layout_data.background_height)
            : 708;

        let zoomLevel = 1.0;
        let cameraOffset = { x: 0, y: 0 };
        const rooms = (CONFIG.map && CONFIG.map.rooms) ? CONFIG.map.rooms : [];
        const roomDoorStates = new Map();
        let pendingKnock = null;

        // Background Blueprint & Object Sorting Setup (Declared early for camera fitting)
        const MAP_BG_URL = (CONFIG.map && CONFIG.map.layout_data && CONFIG.map.layout_data.background_image_url)
            ? CONFIG.map.layout_data.background_image_url
            : null;
        const BLUEPRINT_IMAGE = new Image();
        let blueprintLoaded = false;
        if (MAP_BG_URL) {
            BLUEPRINT_IMAGE.src = MAP_BG_URL;
            BLUEPRINT_IMAGE.onload = () => {
                blueprintLoaded = true;
                if (BLUEPRINT_IMAGE.naturalWidth > 0 && BLUEPRINT_IMAGE.naturalHeight > 0) {
                    MAP_WIDTH_PX = BLUEPRINT_IMAGE.naturalWidth;
                    MAP_HEIGHT_PX = BLUEPRINT_IMAGE.naturalHeight;
                }
                if (typeof resizeCanvas === 'function') resizeCanvas();
            };
            BLUEPRINT_IMAGE.onerror = () => {
                blueprintLoaded = false;
                if (typeof resizeCanvas === 'function') resizeCanvas();
            };
            if (BLUEPRINT_IMAGE.complete && BLUEPRINT_IMAGE.naturalWidth > 0) {
                blueprintLoaded = true;
                MAP_WIDTH_PX = BLUEPRINT_IMAGE.naturalWidth;
                MAP_HEIGHT_PX = BLUEPRINT_IMAGE.naturalHeight;
            }
        }

        let sortedMapObjects = [];
        function refreshSortedMapObjects() {
            const raw = (CONFIG.map && CONFIG.map.objects) ? CONFIG.map.objects : [];
            sortedMapObjects = [...raw].sort((a, b) => {
                const elevA = typeof a.elevation === 'number' ? a.elevation : (a.interaction_config?.elevation || 1);
                const elevB = typeof b.elevation === 'number' ? b.elevation : (b.interaction_config?.elevation || 1);
                if (elevA !== elevB) return elevA - elevB;
                const yA = (a.position ? a.position.y : (a.y || 0));
                const yB = (b.position ? b.position.y : (b.y || 0));
                return yA - yB;
            });
        }
        refreshSortedMapObjects();

        const CURRENT_LOCALE = @json(app()->getLocale());
        const ROOM_NAME_MAP = {
            'meeting room': 'غرفة اجتماعات',
            'executive room': 'غرفة الإدارة',
            'board room': 'قاعة المؤتمرات',
            'boardroom': 'قاعة المؤتمرات',
            'lounge': 'الاستراحة',
            'break room': 'غرفة الاستراحة',
            'open space': 'المساحة المفتوحة',
            'focus room': 'غرفة التركيز',
            'phone booth': 'كابينة الاتصال',
            'office': 'مكتب',
            'reception': 'الاستقبال',
            'cafeteria': 'الكافيتريا',
            'غرفة اجتماعات': 'Meeting Room',
            'غرفة الاجتماعات': 'Meeting Room',
            'قاعة الاجتماعات': 'Meeting Room',
            'غرفة الإدارة': 'Executive Room',
            'غرفة الادارة': 'Executive Room',
            'قاعة المؤتمرات': 'Board Room',
            'الاستراحة': 'Lounge',
            'غرفة الاستراحة': 'Break Room',
            'المساحة المفتوحة': 'Open Space',
            'غرفة التركيز': 'Focus Room',
            'كابينة الاتصال': 'Phone Booth',
            'مكتب': 'Office',
            'الاستقبال': 'Reception',
            'الكافيتريا': 'Cafeteria'
        };

        function getLocalizedRoomName(roomOrName) {
            if (!roomOrName) return CURRENT_LOCALE === 'ar' ? 'المساحة المفتوحة' : 'Open Space';
            let raw = (typeof roomOrName === 'string') ? roomOrName : (roomOrName.name || '');
            let enProp = (typeof roomOrName === 'object' && roomOrName.english_name) ? roomOrName.english_name.trim() : '';
            let arProp = (typeof roomOrName === 'object' && roomOrName.arabic_name) ? roomOrName.arabic_name.trim() : '';

            let parts = raw.split(' - ');
            let arPart = parts[0] ? parts[0].trim() : '';
            let enPart = parts.length > 1 ? parts[1].trim() : '';

            if (CURRENT_LOCALE === 'ar') {
                if (arProp) return arProp;
                if (arPart && /[\u0600-\u06FF]/.test(arPart)) return arPart;
                if (ROOM_NAME_MAP[raw.toLowerCase()]) return ROOM_NAME_MAP[raw.toLowerCase()];
                if (ROOM_NAME_MAP[arPart.toLowerCase()]) return ROOM_NAME_MAP[arPart.toLowerCase()];
                return arPart || raw;
            } else {
                if (enProp) return enProp;
                if (enPart && !/[\u0600-\u06FF]/.test(enPart)) return enPart;
                if (arPart && !/[\u0600-\u06FF]/.test(arPart)) return arPart;
                if (ROOM_NAME_MAP[raw]) return ROOM_NAME_MAP[raw];
                if (ROOM_NAME_MAP[arPart]) return ROOM_NAME_MAP[arPart];
                if (ROOM_NAME_MAP[raw.toLowerCase()]) return ROOM_NAME_MAP[raw.toLowerCase()];
                return enPart || enProp || raw;
            }
        }

        const I18N_DICT = {
            'No route available': { ar: 'لا يوجد مسار متاح للوصول إلى هذا الموقع', en: 'No route available to this location' },
            'Moving to:': { ar: 'جاري الانتقال إلى:', en: 'Moving to:' },
            'Fit Map to Canvas': { ar: 'تمت ملاءمة كامل الخريطة مع الشاشة', en: 'Fit Map to Canvas' },
            'Locating You': { ar: 'تم تحديد وتوسيط موقعك والتقريب عليك', en: 'Locating You & Centering View' },
            'No active map ID found': { ar: 'لم يتم العثور على معرّف الخريطة', en: 'No active map ID found' },
            'Uploading Floor Image...': { ar: 'جاري رفع صورة الأرضية...', en: 'Uploading Floor Image...' },
            'Floor Image Updated': { ar: 'تم تحديث صورة الأرضية بنجاح', en: 'Floor Image Updated' },
            'Upload failed': { ar: 'فشل رفع الملف', en: 'Upload failed' },
            'Stood up': { ar: 'تم الوقوف', en: 'Stood up' },
            'Enjoying fresh drink from': { ar: 'استمتع بمشروب طازج من', en: 'Enjoying fresh drink from' },
            'Interactive Strategy Whiteboard': { ar: 'السبورة الاستراتيجية التفاعلية', en: 'Interactive Strategy Whiteboard' },
            'Chime sound effect triggered on': { ar: 'تم تشغيل نغمة التنبيه عند', en: 'Chime sound effect triggered on' },
            'Playing musical note on': { ar: 'عزف نغمة موسيقية عند', en: 'Playing musical note on' },
            'Seated at Desk': { ar: 'تم الجلوس في المكتب', en: 'Seated at Desk' },
            'Knocked on door... waiting for occupant response.': { ar: 'تم طرق الباب... بانتظار استجابة المتواجدين.', en: 'Knocked on door... waiting for occupant response.' },
            'Only occupants inside this room can control its door. Double-click inside to enter.': { ar: 'لا يمكن التحكم بالباب إلا من داخل الغرفة. انقر مرتين للدخول.', en: 'Only occupants inside this room can control its door. Double-click inside to enter.' },
            'Cannot lock an empty room. The door must remain open when empty.': { ar: 'لا يمكن قفل غرفة فارغة. يجب أن يبقى الباب مفتوحاً.', en: 'Cannot lock an empty room. The door must remain open when empty.' },
            'Cannot lock an empty room. Enter the room first to lock it.': { ar: 'لا يمكن قفل غرفة فارغة. ادخل الغرفة أولاً.', en: 'Cannot lock an empty room. Enter the room first to lock it.' },
            'door closed & locked': { ar: 'تم إغلاق وقفل الباب', en: 'door closed & locked' },
            'door opened': { ar: 'تم فتح الباب', en: 'door opened' },
            'Double-click to navigate into:': { ar: 'انقر مرتين للدخول إلى:', en: 'Double-click to navigate into:' },
            'Guests are only permitted in their designated invited room.': { ar: 'يُسمح للضيوف بالدخول فقط إلى الغرفة المحددة لدعوتهم.', en: 'Guests are only permitted in their designated invited room.' },
            'Restricted Room: You do not have permission to access': { ar: 'غرفة مقيدة: لا تملك صلاحية الوصول إلى', en: 'Restricted Room: You do not have permission to access' },
            'Restricted Room: Access not permitted for': { ar: 'غرفة مقيدة: لا تملك صلاحية الوصول إلى', en: 'Restricted Room: Access not permitted for' },
            'Room has reached full capacity': { ar: 'وصلت الغرفة إلى الحد الأقصى للسعة', en: 'Room has reached full capacity' },
            'Opening link:': { ar: 'جاري فتح الرابط:', en: 'Opening link:' },
            'Company Workplace': { ar: 'مساحة عمل الشركة', en: 'Company Workplace' },
            'You must be inside a room to lock or unlock its door.': { ar: 'يجب أن تكون داخل الغرفة للتحكم بقفل الباب.', en: 'You must be inside a room to lock or unlock its door.' },
            'Room locked': { ar: 'تم قفل الغرفة', en: 'Room locked' },
            'Room unlocked': { ar: 'تم فتح الغرفة', en: 'Room unlocked' },
            'Session replaced by another window': { ar: 'تم استبدال الجلسة بنافذة أخرى', en: 'Session replaced by another window' },
            'joined the office': { ar: 'انضم إلى المكتب', en: 'joined the office' },
            'left the office': { ar: 'غادر المكتب', en: 'left the office' },
            'changed avatar character to': { ar: 'قام بتغيير الشخصية إلى', en: 'changed avatar character to' },
            'Female': { ar: 'أنثى', en: 'Female' },
            'Male': { ar: 'ذكر', en: 'Male' },
            'Access granted by': { ar: 'تم السماح بالدخول من قبل', en: 'Access granted by' },
            'Access denied by occupant.': { ar: 'تم رفض إذن الدخول من قبل المتواجدين.', en: 'Access denied by occupant.' },
            'says HI to you!': { ar: 'يلقي التحية عليك!', en: 'says HI to you!' },
            'is ringing you!': { ar: 'يرن عليك للتنبيه!', en: 'is ringing you!' },
            'cleared the whiteboard.': { ar: 'قام بمسح السبورة.', en: 'cleared the whiteboard.' },
            'started screen presentation': { ar: 'بدأ مشاركة الشاشة', en: 'started screen presentation' },
            'Screen presentation stopped': { ar: 'تم إيقاف مشاركة الشاشة', en: 'Screen presentation stopped' },
            'Screen share window enlarged to theater view!': { ar: 'تم تكبير عرض الشاشة المشاركة!', en: 'Screen share window enlarged to theater view!' },
            'No active screen share at the moment.': { ar: 'لا توجد مشاركة شاشة نشطة حالياً.', en: 'No active screen share at the moment.' },
            'Microphone active': { ar: 'تم تشغيل المايكروفون', en: 'Microphone active' },
            'Microphone muted': { ar: 'تم كتم المايكروفون', en: 'Microphone muted' },
            'Microphone error:': { ar: 'خطأ في المايكروفون:', en: 'Microphone error:' },
            'Camera active': { ar: 'تم تشغيل الكاميرا بنجاح', en: 'Camera active' },
            'Camera stopped': { ar: 'تم إيقاف الكاميرا', en: 'Camera stopped' },
            'Camera error:': { ar: 'خطأ في الكاميرا:', en: 'Camera error:' },
            'Screen sharing started': { ar: 'تم بدء مشاركة الشاشة بنجاح', en: 'Screen sharing started' },
            'Screen sharing stopped': { ar: 'تم إيقاف مشاركة الشاشة', en: 'Screen sharing stopped' },
            'Screen sharing error:': { ar: 'خطأ في مشاركة الشاشة:', en: 'Screen sharing error:' },
            'Recording started': { ar: 'تم بدء تسجيل الجلسة', en: 'Recording started' },
            'Recording stopped and saved': { ar: 'تم إيقاف التسجيل وحفظ الفيديو بنجاح', en: 'Recording stopped and saved' },
            'Recording failed': { ar: 'فشل بدء التسجيل', en: 'Recording failed' },
            'Press': { ar: 'اضغط', en: 'Press' },
            'to sit at desk': { ar: 'للجلوس في المكتب', en: 'to sit at desk' },
            'Open Space': { ar: 'المساحة المفتوحة', en: 'Open Space' },
            'You': { ar: 'أنت', en: 'You' },
            'You / Host': { ar: 'أنت / المضيف', en: 'You / Host' },
            'Host': { ar: 'المضيف', en: 'Host' },
            'Connecting...': { ar: 'جاري الاتصال...', en: 'Connecting...' },
            'Reconnecting...': { ar: 'جاري إعادة الاتصال...', en: 'Reconnecting...' },
            'Connected': { ar: 'متصل', en: 'Connected' },
            'Disconnected': { ar: 'غير متصل', en: 'Disconnected' },
            'Task timer started': { ar: 'تم بدء مؤقت المهمة', en: 'Task timer started' },
            'Task timer paused': { ar: 'تم إيقاف مؤقت المهمة مؤقتاً', en: 'Task timer paused' },
            'Task timer stopped': { ar: 'تم إنهاء وحفظ وقت المهمة', en: 'Task timer stopped' },
            'Link copied to clipboard': { ar: 'تم نسخ الرابط إلى الحافظة', en: 'Link copied to clipboard' },
            'Sent Hi wave to colleague!': { ar: 'تم إلقاء التحية على الزميل!', en: 'Sent Hi wave to colleague!' },
            'Ringing colleague for immediate attention...': { ar: 'جاري تنبيه الزميل بالرنين المباشر...', en: 'Ringing colleague for immediate attention...' },
            'to Grab Drink': { ar: 'لتناول مشروب', en: 'to Grab Drink' },
            'to Open Whiteboard': { ar: 'لفتح السبورة', en: 'to Open Whiteboard' },
            'to Watch Stream': { ar: 'لمشاهدة البث', en: 'to Watch Stream' },
            'to Ring Bell': { ar: 'لتشغيل التنبيه', en: 'to Ring Bell' },
            'to Play Music': { ar: 'لعزف مقطوعة موسيقية', en: 'to Play Music' },
            'to Read Note': { ar: 'لقراءة الملاحظة', en: 'to Read Note' }
        };

        const LARAVEL_I18N = @json(file_exists(lang_path(app()->getLocale() . '.json')) ? json_decode(file_get_contents(lang_path(app()->getLocale() . '.json')), true) : []);

        function __(key) {
            if (I18N_DICT[key]) {
                return I18N_DICT[key][CURRENT_LOCALE] || I18N_DICT[key]['en'] || key;
            }
            if (LARAVEL_I18N && LARAVEL_I18N[key]) {
                return LARAVEL_I18N[key];
            }
            return key;
        }

        // Local User Profile Image
        const userAvatarUrl = CONFIG.currentUser?.avatar_url || null;
        let localAvatarImg = null;
        if (userAvatarUrl) {
            localAvatarImg = new Image();
            localAvatarImg.src = userAvatarUrl;
        }

        // ── Local & Remote Avatars ──
        @php
            $isGuestJs = !empty($user->is_guest);
            $guestAllowedRoomIdJs = isset($invitation) ? ($invitation->room_id ?: ($room->id ?? null)) : null;
            $userGenderJs = !empty($user->gender) ? $user->gender : (!empty($user->profile?->gender) ? $user->profile->gender : 'male');
            $spawnPosJs = $initialSpawn ?? null;
        @endphp
        const isGuest = @json($isGuestJs);
        const guestAllowedRoomId = @json($guestAllowedRoomIdJs);
        const userGender = @json($userGenderJs);
        const spawnPos = @json($spawnPosJs);

        let defaultX = 250;
        let defaultY = 200;

        if (isGuest && guestAllowedRoomId) {
            const guestRoomObj = rooms.find(r => r.id === guestAllowedRoomId);
            if (guestRoomObj && guestRoomObj.bounds) {
                defaultX = Math.round((guestRoomObj.bounds.x + (guestRoomObj.bounds.width / 2)) * TILE_SIZE);
                defaultY = Math.round((guestRoomObj.bounds.y + (guestRoomObj.bounds.height / 2)) * TILE_SIZE);
            } else if (spawnPos && spawnPos.x && spawnPos.y) {
                defaultX = spawnPos.x;
                defaultY = spawnPos.y;
            }
        } else if (spawnPos && spawnPos.x && spawnPos.y) {
            defaultX = spawnPos.x;
            defaultY = spawnPos.y;
        }

        const localAvatar = {
            id: String(CONFIG.currentUser?.id || 'usr_1'),
            name: CONFIG.currentUser?.name || 'User',
            avatarUrl: userAvatarUrl,
            avatarImg: localAvatarImg,
            jobTitle: CONFIG.currentUser?.profile?.job_title || 'Team Member',
            isGuest: isGuest,
            x: defaultX,
            y: defaultY,
            targetX: defaultX,
            targetY: defaultY,
            speed: 5.0,
            radius: 26,
            micActive: false,
            camActive: false,
            isSpeaking: false,
            isSitting: false,
            sittingFurnitureId: null,
            currentRoomId: (isGuest && guestAllowedRoomId) ? guestAllowedRoomId : null
        };
        const remoteAvatars = new Map();
        const speechBubbles = new Map(); // userId -> { text, emoji, timestamp, type }
        let nearbyChair = null;
        let isSessionReplaced = false;
        let wsReconnectAttempts = 0;

        // ── Resize, Zoom, Pan & Camera ──
        function getFloorBounds() {
            let minX = 0;
            let minY = 0;
            let maxX = MAP_WIDTH_PX;
            let maxY = MAP_HEIGHT_PX;

            if (rooms && rooms.length > 0) {
                rooms.forEach(r => {
                    if (!r.bounds) return;
                    const rx = r.bounds.x * TILE_SIZE;
                    const ry = r.bounds.y * TILE_SIZE;
                    const rw = r.bounds.width * TILE_SIZE;
                    const rh = r.bounds.height * TILE_SIZE;
                    minX = Math.min(minX, rx);
                    minY = Math.min(minY, ry);
                    maxX = Math.max(maxX, rx + rw);
                    maxY = Math.max(maxY, ry + rh);
                });
            }

            if (sortedMapObjects && sortedMapObjects.length > 0) {
                sortedMapObjects.forEach(obj => {
                    const ox = (obj.position ? obj.position.x : (obj.y || 0)) * TILE_SIZE;
                    const oy = (obj.position ? obj.position.y : (obj.y || 0)) * TILE_SIZE;
                    const ow = (obj.width || 1) * TILE_SIZE;
                    const oh = (obj.height || 1) * TILE_SIZE;
                    minX = Math.min(minX, ox);
                    minY = Math.min(minY, oy);
                    maxX = Math.max(maxX, ox + ow);
                    maxY = Math.max(maxY, oy + oh);
                });
            }

            const width = Math.max(100, maxX - minX);
            const height = Math.max(100, maxY - minY);

            return {
                minX: minX,
                minY: minY,
                maxX: maxX,
                maxY: maxY,
                width: width,
                height: height,
                centerX: minX + (width / 2),
                centerY: minY + (height / 2)
            };
        }

        function updateDebugOverlay(info) {
            let el = document.getElementById('fit-map-debug-overlay');
            if (!el) {
                el = document.createElement('div');
                el.id = 'fit-map-debug-overlay';
                el.style.cssText = 'position:fixed;top:70px;inset-inline-start:16px;background:rgba(11,20,16,0.92);backdrop-filter:blur(16px);border:1px solid rgba(211,165,83,0.5);border-radius:12px;padding:10px 14px;color:#F9F4EE;font-family:IBM Plex Mono,monospace;font-size:11px;z-index:99999;pointer-events:none;line-height:1.5;box-shadow:0 12px 30px rgba(0,0,0,0.7);';
                document.body.appendChild(el);
            }
            el.innerHTML = `
                <div style="color:#D3A553;font-weight:700;margin-bottom:4px;border-bottom:1px solid rgba(211,165,83,0.3);padding-bottom:2px;">
                    📐 FIT TO CANVAS RUNTIME INSPECTION
                </div>
                <div><b>Viewport / Canvas:</b> ${info['canvas.clientWidth']} × ${info['canvas.clientHeight']} (Rect: ${info['canvas.getBoundingClientRect().width']} × ${info['canvas.getBoundingClientRect().height']})</div>
                <div><b>Container:</b> ${info['map wrapper (#canvas-container) width']} × ${info['map wrapper (#canvas-container) height']}</div>
                <div><b>Floor:</b> ${info['floorBounds.width']} × ${info['floorBounds.height']} [(${info['floorBounds.minX']},${info['floorBounds.minY']}) to (${info['floorBounds.maxX']},${info['floorBounds.maxY']})]</div>
                <div><b>Scale / Zoom:</b> ${Number(info['camera.zoomLevel']).toFixed(4)}</div>
                <div><b>Camera Offset:</b> X: ${info['camera.offsetX']}, Y: ${info['camera.offsetY']}</div>
                <div><b>DPR:</b> ${info['devicePixelRatio']}</div>
            `;
            setTimeout(() => { if (el) el.style.display = 'none'; }, 8000);
            el.style.display = 'block';
        }

        function centerCamera() {
            if (!canvas || !container) return;
            width = canvas.width = container.clientWidth || window.innerWidth;
            height = canvas.height = container.clientHeight || window.innerHeight;

            const floor = getFloorBounds();

            // Symmetrical padding around complete floor boundary (32px)
            const padding = 32;
            const availableWidth = Math.max(100, width - (padding * 2));
            const availableHeight = Math.max(100, height - (padding * 2));

            // Fit complete floor into viewport using Math.min
            const scaleX = availableWidth / floor.width;
            const scaleY = availableHeight / floor.height;
            const fitScale = Math.min(scaleX, scaleY);

            zoomLevel = Math.max(0.05, Math.min(3.5, fitScale));

            // Perfectly center the complete floor in the viewport
            cameraOffset.x = Math.round((width / 2) - (floor.centerX * zoomLevel));
            cameraOffset.y = Math.round((height / 2) - (floor.centerY * zoomLevel));
        }

        function fitMapToCanvas() {
            centerCamera();
            if (typeof draw === 'function') draw();

            const mainContainer = document.querySelector('.nx-office-viewport-container');
            const screenContainer = document.querySelector('.nx-floor-map-screen');
            const rect = canvas ? canvas.getBoundingClientRect() : { width: 0, height: 0 };
            const floor = getFloorBounds();
            const dpr = window.devicePixelRatio || 1;

            const debugInfo = {
                'window.innerWidth': window.innerWidth,
                'document.documentElement.clientWidth': document.documentElement.clientWidth,
                'main office container (.nx-floor-map-screen) width': screenContainer ? screenContainer.clientWidth : 'N/A',
                'main office container (.nx-floor-map-screen) height': screenContainer ? screenContainer.clientHeight : 'N/A',
                'viewport wrapper (.nx-office-viewport-container) width': mainContainer ? mainContainer.clientWidth : 'N/A',
                'map wrapper (#canvas-container) width': container ? container.clientWidth : 'N/A',
                'map wrapper (#canvas-container) height': container ? container.clientHeight : 'N/A',
                'canvas.clientWidth': canvas ? canvas.clientWidth : 0,
                'canvas.clientHeight': canvas ? canvas.clientHeight : 0,
                'canvas.getBoundingClientRect().width': rect.width,
                'canvas.getBoundingClientRect().height': rect.height,
                'canvas.width': canvas ? canvas.width : 0,
                'canvas.height': canvas ? canvas.height : 0,
                'devicePixelRatio': dpr,
                'floorBounds.minX': floor.minX,
                'floorBounds.minY': floor.minY,
                'floorBounds.maxX': floor.maxX,
                'floorBounds.maxY': floor.maxY,
                'floorBounds.width': floor.width,
                'floorBounds.height': floor.height,
                'floorBounds.centerX': floor.centerX,
                'floorBounds.centerY': floor.centerY,
                'camera.zoomLevel': zoomLevel,
                'camera.offsetX': cameraOffset.x,
                'camera.offsetY': cameraOffset.y
            };

            console.log('═══════════════ FIT MAP TO CANVAS RUNTIME METRICS ═══════════════');
            console.table(debugInfo);
            window.__FIT_MAP_DEBUG = debugInfo;

            updateDebugOverlay(debugInfo);
            showToast('📐 ' + __('Fit Map to Canvas'));
        }

        function fitFloorToCanvas() {
            fitMapToCanvas();
        }

        function toggleFitMode() {
            fitMapToCanvas();
        }

        function zoomIn() {
            setZoomLevel(zoomLevel * 1.04);
        }

        function zoomOut() {
            setZoomLevel(zoomLevel * 0.96);
        }

        function getClampedCameraOffset(targetCenterX, targetCenterY, zoom) {
            const floor = getFloorBounds();
            const floorWidthOnScreen = floor.width * zoom;
            const floorHeightOnScreen = floor.height * zoom;
            
            let offsetX, offsetY;
            
            // X axis clamping:
            if (floorWidthOnScreen <= width) {
                // If the entire floor fits horizontally, center the entire floor horizontally
                offsetX = Math.round((width / 2) - (floor.centerX * zoom));
            } else {
                // If floor is wider than screen, center on target but keep within floor bounds
                const desiredOffsetX = Math.round((width / 2) - (targetCenterX * zoom));
                const minOffsetX = Math.round(width - (floor.maxX * zoom) - 24);
                const maxOffsetX = Math.round(-floor.minX * zoom + 24);
                offsetX = Math.max(minOffsetX, Math.min(maxOffsetX, desiredOffsetX));
            }
            
            // Y axis clamping:
            if (floorHeightOnScreen <= height) {
                // If the entire floor fits vertically, center the entire floor vertically
                offsetY = Math.round((height / 2) - (floor.centerY * zoom));
            } else {
                // If floor is taller than screen, center on target but keep within floor bounds
                const desiredOffsetY = Math.round((height / 2) - (targetCenterY * zoom));
                const minOffsetY = Math.round(height - (floor.maxY * zoom) - 24);
                const maxOffsetY = Math.round(-floor.minY * zoom + 24);
                offsetY = Math.max(minOffsetY, Math.min(maxOffsetY, desiredOffsetY));
            }
            
            return { x: offsetX, y: offsetY };
        }

        let locateBeaconEndTime = 0;
        function locateMe() {
            if (!localAvatar) return;

            const floor = getFloorBounds();
            const padding = 32;
            const availableWidth = Math.max(100, width - (padding * 2));
            const availableHeight = Math.max(100, height - (padding * 2));
            const fitScale = Math.min(availableWidth / floor.width, availableHeight / floor.height);

            // Gentle comfortable zoom focused on avatar
            const targetZoom = Math.max(zoomLevel, Math.min(1.30, Math.max(fitScale * 1.15, 1.10)));

            const startX = cameraOffset.x;
            const startY = cameraOffset.y;
            const startZoom = zoomLevel;

            const targetPos = getClampedCameraOffset(localAvatar.x, localAvatar.y, targetZoom);
            const endX = targetPos.x;
            const endY = targetPos.y;

            const duration = 400; // ms smooth glide
            const startTime = performance.now();

            function easeOutCubic(t) { return 1 - Math.pow(1 - t, 3); }

            function animateLocate(now) {
                const elapsed = now - startTime;
                const progress = Math.min(1.0, elapsed / duration);
                const ease = easeOutCubic(progress);

                zoomLevel = startZoom + (targetZoom - startZoom) * ease;
                cameraOffset.x = startX + (endX - startX) * ease;
                cameraOffset.y = startY + (endY - startY) * ease;

                if (typeof draw === 'function') draw();

                if (progress < 1.0) {
                    requestAnimationFrame(animateLocate);
                }
            }

            requestAnimationFrame(animateLocate);
            locateBeaconEndTime = Date.now() + 4500;
            showToast('📍 ' + __('Locating You'));
        }

        function resetCameraView() {
            locateMe();
        }

        function setZoomLevel(newZoom, centerX = (width / 2), centerY = (height / 2)) {
            const minZoom = 0.15;
            const maxZoom = 4.0;
            const clamped = Math.max(minZoom, Math.min(maxZoom, newZoom));
            if (Math.abs(clamped - zoomLevel) < 0.0005) return;

            const worldX = (centerX - cameraOffset.x) / zoomLevel;
            const worldY = (centerY - cameraOffset.y) / zoomLevel;

            zoomLevel = clamped;
            cameraOffset.x = centerX - worldX * zoomLevel;
            cameraOffset.y = centerY - worldY * zoomLevel;

            if (typeof draw === 'function') draw();
        }

        function resizeCanvas() {
            centerCamera();
            if (typeof draw === 'function') draw();
        }
        window.addEventListener('resize', resizeCanvas);
        centerCamera();

        // Canvas Mouse Wheel Zoom with Smooth Micro-Steps
        if (canvas) {
            canvas.addEventListener('wheel', (e) => {
                e.preventDefault();
                const rect = canvas.getBoundingClientRect();
                const mouseX = e.clientX - rect.left;
                const mouseY = e.clientY - rect.top;
                const factor = e.deltaY < 0 ? 1.025 : 0.975;
                setZoomLevel(zoomLevel * factor, mouseX, mouseY);
            }, { passive: false });
        }

        // Canvas Pan Drag Engine
        let isPanning = false;
        let panStartX = 0;
        let panStartY = 0;
        let panCamStartX = 0;
        let panCamStartY = 0;
        let hasMovedMouseDuringDrag = false;

        if (canvas) {
            canvas.addEventListener('mousedown', (e) => {
                if (e.button === 1 || e.button === 2 || e.altKey || e.shiftKey) {
                    isPanning = true;
                    panStartX = e.clientX;
                    panStartY = e.clientY;
                    panCamStartX = cameraOffset.x;
                    panCamStartY = cameraOffset.y;
                    canvas.style.cursor = 'grab';
                    e.preventDefault();
                } else if (e.button === 0) {
                    panStartX = e.clientX;
                    panStartY = e.clientY;
                    panCamStartX = cameraOffset.x;
                    panCamStartY = cameraOffset.y;
                    hasMovedMouseDuringDrag = false;
                }
            });

            window.addEventListener('mousemove', (e) => {
                if (isPanning) {
                    cameraOffset.x = panCamStartX + (e.clientX - panStartX);
                    cameraOffset.y = panCamStartY + (e.clientY - panStartY);
                    canvas.style.cursor = 'grabbing';
                    if (typeof draw === 'function') draw();
                } else if (e.buttons === 1) {
                    const dist = Math.hypot(e.clientX - panStartX, e.clientY - panStartY);
                    if (dist > 8) {
                        hasMovedMouseDuringDrag = true;
                        cameraOffset.x = panCamStartX + (e.clientX - panStartX);
                        cameraOffset.y = panCamStartY + (e.clientY - panStartY);
                        if (typeof draw === 'function') draw();
                    }
                }
            });

            window.addEventListener('mouseup', (e) => {
                if (isPanning) {
                    isPanning = false;
                    canvas.style.cursor = 'default';
                }
            });

            canvas.addEventListener('contextmenu', (e) => {
                e.preventDefault();
            });
        }

        // ── Upload Custom Floor Artwork ──

        async function uploadOfficeFloorImage(input) {
            if (!input.files || !input.files[0]) return;
            const file = input.files[0];
            const formData = new FormData();
            formData.append('image', file);
            const mapId = CONFIG.map?.id;
            if (!mapId) {
                showToast('❌ {{ __("No active map ID found") }}');
                return;
            }

            showToast('⏳ ' + __('Uploading Floor Image...'));
            try {
                const res = await fetch(`/editor/maps/${mapId}/background`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': CONFIG.csrf,
                        'Accept': 'application/json'
                    },
                    body: formData
                });
                const data = await res.json();
                if (res.ok && data.image_url) {
                    const uploadedUrl = data.image_url;
                    BLUEPRINT_IMAGE.src = uploadedUrl + '?v=' + Date.now();
                    BLUEPRINT_IMAGE.onload = () => {
                        blueprintLoaded = true;
                        if (BLUEPRINT_IMAGE.naturalWidth > 0 && BLUEPRINT_IMAGE.naturalHeight > 0) {
                            MAP_WIDTH_PX = BLUEPRINT_IMAGE.naturalWidth;
                            MAP_HEIGHT_PX = BLUEPRINT_IMAGE.naturalHeight;
                        }
                        centerCamera('fill');
                        if (typeof draw === 'function') draw();
                        showToast('✅ ' + __('Floor Image Updated'));
                    };
                } else {
                    showToast('❌ ' + (data.message || __('Upload failed')));
                }
            } catch (err) {
                console.error(err);
                showToast('❌ ' + __('Upload failed'));
            }
        }

        // ── LiveKit SFU Real-Time Media ──
        const peerAudioElements = new Map(); // targetUserId -> HTMLAudioElement
        const peerVideoCards = new Map(); // targetUserId -> HTMLDivElement
        let localMediaStream = null;
        let localAudioStream = null;
        let screenStream = null;
        let micActive = false;
        let camActive = false;
        let screenActive = false;
        let currentLiveKitRoomId = null;
        // ── Procedural Web Audio API Sound Synthesizer ──
        let audioCtx = null;
        function getAudioContext() {
            if (!audioCtx) {
                const AudioContextClass = window.AudioContext || window.webkitAudioContext;
                if (AudioContextClass) audioCtx = new AudioContextClass();
            }
            if (audioCtx && audioCtx.state === 'suspended') {
                audioCtx.resume();
            }
            return audioCtx;
        }

        // 1. Realistic Two-Tone Melodic Doorbell (Ding-Dong)
        function playDoorbellSound() {
            try {
                const ctx = getAudioContext();
                if (!ctx) return;
                const now = ctx.currentTime;

                // First chime tone: 880Hz (A5)
                const osc1 = ctx.createOscillator();
                const gain1 = ctx.createGain();
                osc1.type = 'sine';
                osc1.frequency.setValueAtTime(880, now);
                gain1.gain.setValueAtTime(0.35, now);
                gain1.gain.exponentialRampToValueAtTime(0.001, now + 0.7);
                osc1.connect(gain1);
                gain1.connect(ctx.destination);
                osc1.start(now);
                osc1.stop(now + 0.75);

                // Second chime tone: 659.25Hz (E5) after 280ms
                const osc2 = ctx.createOscillator();
                const gain2 = ctx.createGain();
                osc2.type = 'sine';
                osc2.frequency.setValueAtTime(659.25, now + 0.28);
                gain2.gain.setValueAtTime(0.38, now + 0.28);
                gain2.gain.exponentialRampToValueAtTime(0.001, now + 1.2);
                osc2.connect(gain2);
                gain2.connect(ctx.destination);
                osc2.start(now + 0.28);
                osc2.stop(now + 1.25);
            } catch(e) {
                console.warn('[Audio] playDoorbellSound failed:', e);
            }
        }

        // 2. Multi-Tone Telephone Attention Ring Sound (Loud & Clear Ringtone)
        function playRingSound() {
            try {
                const ctx = getAudioContext();
                if (!ctx) return;
                const now = ctx.currentTime;

                // Ring sequence: 3 energetic bursts of dual US/EU telephone frequencies
                [0, 0.35, 0.70].forEach(offset => {
                    const oscA = ctx.createOscillator();
                    const oscB = ctx.createOscillator();
                    const gain = ctx.createGain();

                    oscA.type = 'sine';
                    oscB.type = 'sine';
                    oscA.frequency.setValueAtTime(440, now + offset); // Standard 440Hz
                    oscB.frequency.setValueAtTime(480, now + offset); // Standard 480Hz

                    gain.gain.setValueAtTime(0.35, now + offset);
                    gain.gain.exponentialRampToValueAtTime(0.001, now + offset + 0.28);

                    oscA.connect(gain);
                    oscB.connect(gain);
                    gain.connect(ctx.destination);

                    oscA.start(now + offset);
                    oscB.start(now + offset);
                    oscA.stop(now + offset + 0.30);
                    oscB.stop(now + offset + 0.30);
                });
            } catch(e) {
                console.warn('[Audio] playRingSound failed:', e);
            }
        }

        // 2b. Friendly Cheerful "Hi / Wave" Chime Sound
        function playWaveSound() {
            try {
                const ctx = getAudioContext();
                if (!ctx) return;
                const now = ctx.currentTime;

                // Ascending bright major triad notes (C6 -> E6 -> G6)
                const notes = [1046.50, 1318.51, 1567.98];
                notes.forEach((freq, idx) => {
                    const osc = ctx.createOscillator();
                    const gain = ctx.createGain();
                    const t = now + (idx * 0.10);

                    osc.type = 'sine';
                    osc.frequency.setValueAtTime(freq, t);

                    gain.gain.setValueAtTime(0.30, t);
                    gain.gain.exponentialRampToValueAtTime(0.001, t + 0.35);

                    osc.connect(gain);
                    gain.connect(ctx.destination);

                    osc.start(t);
                    osc.stop(t + 0.38);
                });
            } catch(e) {
                console.warn('[Audio] playWaveSound failed:', e);
            }
        }

        // 3. Crisp Wooden Door Knock Sound
        function playDoorKnockSound() {
            try {
                const ctx = getAudioContext();
                if (!ctx) return;
                const now = ctx.currentTime;

                [0, 0.14, 0.28].forEach((offset) => {
                    const osc = ctx.createOscillator();
                    const gain = ctx.createGain();
                    osc.type = 'triangle';
                    osc.frequency.setValueAtTime(160, now + offset);
                    osc.frequency.exponentialRampToValueAtTime(50, now + offset + 0.08);

                    gain.gain.setValueAtTime(0.4, now + offset);
                    gain.gain.exponentialRampToValueAtTime(0.001, now + offset + 0.08);

                    osc.connect(gain);
                    gain.connect(ctx.destination);

                    osc.start(now + offset);
                    osc.stop(now + offset + 0.09);
                });
            } catch(e) {
                console.warn('[Audio] playDoorKnockSound failed:', e);
            }
        }

        // 4. Subtle Tactile Door Slide / Swish Sound
        function playDoorSlideSound() {
            try {
                const ctx = getAudioContext();
                if (!ctx) return;
                const now = ctx.currentTime;

                const osc = ctx.createOscillator();
                const gain = ctx.createGain();
                osc.type = 'sine';
                osc.frequency.setValueAtTime(320, now);
                osc.frequency.exponentialRampToValueAtTime(560, now + 0.2);

                gain.gain.setValueAtTime(0.12, now);
                gain.gain.exponentialRampToValueAtTime(0.001, now + 0.22);

                osc.connect(gain);
                gain.connect(ctx.destination);

                osc.start(now);
                osc.stop(now + 0.25);
            } catch(e) {}
        }

        // ── Controls & Keyboard Interaction ──
        const keys = { w: false, a: false, s: false, d: false, arrowup: false, arrowdown: false, arrowleft: false, arrowright: false };
        window.addEventListener('keydown', (e) => {
            const activeEl = document.activeElement;
            if (activeEl && (activeEl.tagName === 'INPUT' || activeEl.tagName === 'TEXTAREA' || activeEl.isContentEditable)) return;

            const k = e.key.toLowerCase();
            if (keys[k] !== undefined) {
                keys[k] = true;
                avatarWaypoints = []; // Interrupt automated walking when player uses arrow keys
            }

            // 'E' Key to Interact with Furniture / Objects / Sit
            if (k === 'e') {
                if (localAvatar.isSitting) {
                    localAvatar.isSitting = false;
                    localAvatar.sittingFurnitureId = null;
                    if (ws && ws.readyState === WebSocket.OPEN) {
                        ws.send(JSON.stringify({ type: 'user.sit', payload: { isSitting: false } }));
                    }
                    showToast('🧍 ' + __('Stood up'));
                } else if (nearbyInteractive) {
                    const item = nearbyInteractive;
                    if (item.interaction_type === 'drink') {
                        playDoorSlideSound();
                        showToast(`☕ ${__("Enjoying fresh drink from")} ${item.name}! Cheers! 🎉`);
                        triggerSpeechReaction('☕', 'emoji');
                    } else if (item.interaction_type === 'whiteboard') {
                        const boardModal = document.getElementById('whiteboard-modal');
                        if (boardModal) boardModal.style.display = 'flex';
                        else showToast(`📋 ${__("Interactive Strategy Whiteboard")}: ${item.name}`);
                    } else if (item.interaction_type === 'soundEffect') {
                        playDoorKnockSound();
                        showToast(`🔔 ${__("Chime sound effect triggered on")} ${item.name}!`);
                        triggerSpeechReaction('🎉', 'emoji');
                    } else if (item.interaction_type === 'instrument' || item.interaction_type === 'staticMusic') {
                        playDoorSlideSound();
                        showToast(`🎹 ${__("Playing musical note on")} ${item.name}! 🎶`);
                        triggerSpeechReaction('🎶', 'emoji');
                    } else if (nearbyChair) {
                        localAvatar.isSitting = true;
                        localAvatar.sittingFurnitureId = nearbyChair.id;
                        localAvatar.x = nearbyChair.x;
                        localAvatar.y = nearbyChair.y;
                        localAvatar.targetX = nearbyChair.x;
                        localAvatar.targetY = nearbyChair.y;
                        if (ws && ws.readyState === WebSocket.OPEN) {
                            ws.send(JSON.stringify({
                                type: 'user.sit',
                                payload: {
                                    isSitting: true,
                                    furnitureId: nearbyChair.id,
                                    seatPosition: { x: nearbyChair.x, y: nearbyChair.y }
                                }
                            }));
                        }
                        showToast('🪑 ' + __('Seated at Desk'));
                    }
                }
            }

            // 'R' Key to Toggle Screen Share
            if (k === 'r') {
                toggleScreenShare();
            }
        });
        window.addEventListener('keyup', (e) => {
            const k = e.key.toLowerCase();
            if (keys[k] !== undefined) keys[k] = false;
        });

        let nearbyInteractive = null;
        let lastFurnitureCheck = 0;

        function checkNearbyFurniture(force = false) {
            const now = Date.now();
            if (!force && now - lastFurnitureCheck < 150) return;
            lastFurnitureCheck = now;

            const promptEl = document.getElementById('furniture-sit-prompt');
            if (localAvatar.isSitting) {
                if (promptEl) promptEl.style.display = 'none';
                return;
            }

            const objects = (CONFIG.map && CONFIG.map.objects) || [];
            let found = null;
            for (const obj of objects) {
                const ox = (obj.position ? obj.position.x : (obj.x || 0)) * 32 + ((obj.width || (obj.size ? obj.size.width : 1)) * 16);
                const oy = (obj.position ? obj.position.y : (obj.y || 0)) * 32 + ((obj.height || (obj.size ? obj.size.height : 1)) * 16);
                const dist = Math.hypot(localAvatar.x - ox, localAvatar.y - oy);
                if (dist < 48) {
                    const behType = obj.interaction_type || (obj.interaction_config && obj.interaction_config.behavior?.type) || ((obj.type && (obj.type.includes('chair') || obj.type.includes('desk') || obj.type.includes('sofa') || obj.type.includes('seating'))) ? 'sit' : null);
                    if (behType && behType !== 'none') {
                        found = {
                            id: obj.id || `obj_${ox}_${oy}`,
                            x: ox,
                            y: oy,
                            name: obj.name || 'Furniture',
                            type: obj.type,
                            interaction_type: behType,
                            config: obj.interaction_config
                        };
                        break;
                    }
                }
            }

            nearbyInteractive = found;
            nearbyChair = (found && (found.interaction_type === 'sit' || !found.interaction_type)) ? found : null;

            if (promptEl) {
                if (found) {
                    promptEl.style.display = 'block';
                    if (found.interaction_type === 'drink') {
                        promptEl.innerHTML = `<span>☕ ${__('Press')} <kbd style="background: rgba(211, 165, 83, 0.25); border: 1px solid rgba(211, 165, 83, 0.4); padding: 2px 7px; border-radius: 6px; font-family: monospace; font-size: 11px; color: #D3A553;">E</kbd> ${__('to Grab Drink')}</span>`;
                    } else if (found.interaction_type === 'whiteboard') {
                        promptEl.innerHTML = `<span>📋 ${__('Press')} <kbd style="background: rgba(211, 165, 83, 0.25); border: 1px solid rgba(211, 165, 83, 0.4); padding: 2px 7px; border-radius: 6px; font-family: monospace; font-size: 11px; color: #D3A553;">E</kbd> ${__('to Open Whiteboard')}</span>`;
                    } else if (found.interaction_type === 'youtube') {
                        promptEl.innerHTML = `<span>📺 ${__('Press')} <kbd style="background: rgba(211, 165, 83, 0.25); border: 1px solid rgba(211, 165, 83, 0.4); padding: 2px 7px; border-radius: 6px; font-family: monospace; font-size: 11px; color: #D3A553;">E</kbd> ${__('to Watch Stream')}</span>`;
                    } else if (found.interaction_type === 'soundEffect') {
                        promptEl.innerHTML = `<span>🔔 ${__('Press')} <kbd style="background: rgba(211, 165, 83, 0.25); border: 1px solid rgba(211, 165, 83, 0.4); padding: 2px 7px; border-radius: 6px; font-family: monospace; font-size: 11px; color: #D3A553;">E</kbd> ${__('to Ring Bell')}</span>`;
                    } else if (found.interaction_type === 'instrument' || found.interaction_type === 'staticMusic') {
                        promptEl.innerHTML = `<span>🎹 ${__('Press')} <kbd style="background: rgba(211, 165, 83, 0.25); border: 1px solid rgba(211, 165, 83, 0.4); padding: 2px 7px; border-radius: 6px; font-family: monospace; font-size: 11px; color: #D3A553;">E</kbd> ${__('to Play Music')}</span>`;
                    } else if (found.interaction_type === 'stickyNote') {
                        promptEl.innerHTML = `<span>📝 ${__('Press')} <kbd style="background: rgba(211, 165, 83, 0.25); border: 1px solid rgba(211, 165, 83, 0.4); padding: 2px 7px; border-radius: 6px; font-family: monospace; font-size: 11px; color: #D3A553;">E</kbd> ${__('to Read Note')}</span>`;
                    } else {
                        promptEl.innerHTML = `<span>🪑 ${__('Press')} <kbd style="background: rgba(211, 165, 83, 0.25); border: 1px solid rgba(211, 165, 83, 0.4); padding: 2px 7px; border-radius: 6px; font-family: monospace; font-size: 11px; color: #D3A553;">E</kbd> ${__('to Sit at Desk')}</span>`;
                    }
                } else {
                    promptEl.style.display = 'none';
                }
            }
        }

        // ── Room Door Portals, Physical Walls & Waypoint Navigation Engine ──
        let avatarWaypoints = []; // [{x, y, onArrival: fn}]
        let doorAnimationStates = new Map(); // roomId -> { openProgress: 0..1 (0=closed, 1=open), isAnimating: bool }
        let lastCanvasClickTime = 0;
        let lastCanvasClickPos = { x: 0, y: 0 };
        const roomDoorPortalsCache = new Map();

        function getRoomDoorPortal(r) {
            if (!r || !r.bounds) return null;
            if (roomDoorPortalsCache.has(r.id)) {
                return roomDoorPortalsCache.get(r.id);
            }

            const rx = r.bounds.x * TILE_SIZE;
            const ry = r.bounds.y * TILE_SIZE;
            const rw = r.bounds.width * TILE_SIZE;
            const rh = r.bounds.height * TILE_SIZE;
            const doorWidth = 56; // High visibility wider portal

            // 1. User Explicit Configuration in Room Design (if specified)
            const explicitSide = r.bounds.doorSide || r.bounds.door_side || r.door_side || r.doorSide || null;
            const explicitOffset = (typeof r.bounds.doorOffset === 'number') ? r.bounds.doorOffset : null;

            // Candidate wall sides and offset samples
            const offsetSamples = (explicitOffset !== null) 
                ? [explicitOffset] 
                : [0.5, 0.75, 0.82, 0.25, 0.18, 0.65, 0.35];

            const candidates = [];
            const sides = (explicitSide && explicitSide !== 'auto') 
                ? [explicitSide.toLowerCase()] 
                : ['bottom', 'top', 'right', 'left'];

            for (const side of sides) {
                for (const off of offsetSamples) {
                    let cx = 0, cy = 0, inX = 0, inY = 0, outX = 0, outY = 0;
                    if (side === 'bottom') {
                        cx = rx + (rw * off);
                        cy = ry + rh;
                        inX = cx; inY = cy - 26;
                        outX = cx; outY = cy + 32;
                    } else if (side === 'top') {
                        cx = rx + (rw * off);
                        cy = ry;
                        inX = cx; inY = cy + 26;
                        outX = cx; outY = cy - 32;
                    } else if (side === 'right') {
                        cx = rx + rw;
                        cy = ry + (rh * off);
                        inX = cx - 26; inY = cy;
                        outX = cx + 32; outY = cy;
                    } else if (side === 'left') {
                        cx = rx;
                        cy = ry + (rh * off);
                        inX = cx + 26; inY = cy;
                        outX = cx - 32; outY = cy;
                    }

                    candidates.push({
                        side: side,
                        offset: off,
                        x: cx,
                        y: cy,
                        entryInsideX: inX,
                        entryInsideY: inY,
                        exitOutsideX: outX,
                        exitOutsideY: outY,
                    });
                }
            }

            // 2. Intelligent Placement Facing the Central Open Walkway & Avoiding Shared Walls
            const mapCenter = { x: MAP_WIDTH_PX / 2, y: MAP_HEIGHT_PX / 2 };
            const outerMargin = 60; // Outer exterior building perimeter margins

            let bestCandidate = null;
            let bestScore = -Infinity;

            for (const cand of candidates) {
                // A. Disqualify outer exterior building walls touching outer map canvas border (unless explicitly chosen)
                if (!explicitSide || explicitSide === 'auto') {
                    if (cand.exitOutsideX < outerMargin || cand.exitOutsideX > MAP_WIDTH_PX - outerMargin ||
                        cand.exitOutsideY < outerMargin || cand.exitOutsideY > MAP_HEIGHT_PX - outerMargin) {
                        continue; // Skip: Outer exterior building wall facing outside margins!
                    }
                }

                // B. Check overlap with other rooms (MUST NOT touch or enter any other room!)
                let isInsideOtherRoom = false;
                let minDistanceToOtherRooms = 99999;

                for (const other of rooms) {
                    if (other.id === r.id || !other.bounds) continue;
                    const orx = other.bounds.x * TILE_SIZE;
                    const ory = other.bounds.y * TILE_SIZE;
                    const orw = other.bounds.width * TILE_SIZE;
                    const orh = other.bounds.height * TILE_SIZE;

                    // Test if exit outside point is inside or touching other room with 12px safety margin
                    if (cand.exitOutsideX >= orx - 12 && cand.exitOutsideX <= orx + orw + 12 &&
                        cand.exitOutsideY >= ory - 12 && cand.exitOutsideY <= ory + orh + 12) {
                        isInsideOtherRoom = true;
                        break;
                    }

                    // Test if door position itself on the wall falls on a shared wall segment
                    if (cand.x >= orx - 6 && cand.x <= orx + orw + 6 &&
                        cand.y >= ory - 6 && cand.y <= ory + orh + 6) {
                        isInsideOtherRoom = true;
                        break;
                    }

                    // Calculate clearance distance to other room's rectangle
                    const dx = Math.max(orx - cand.exitOutsideX, 0, cand.exitOutsideX - (orx + orw));
                    const dy = Math.max(ory - cand.exitOutsideY, 0, cand.exitOutsideY - (ory + orh));
                    const dist = Math.hypot(dx, dy);
                    if (dist < minDistanceToOtherRooms) {
                        minDistanceToOtherRooms = dist;
                    }
                }

                if (isInsideOtherRoom) {
                    continue; // Discard: this position touches another room!
                }

                // C. Score candidate: closer to Central Open Corridor + open clearance distance
                const distToCenter = Math.hypot(cand.exitOutsideX - mapCenter.x, cand.exitOutsideY - mapCenter.y);
                const score = (1200 - distToCenter) + (minDistanceToOtherRooms * 4) + (cand.offset === 0.5 ? 25 : 0);

                if (score > bestScore) {
                    bestScore = score;
                    bestCandidate = cand;
                }
            }

            // Fallback if all sides were outer perimeter
            if (!bestCandidate) {
                candidates.sort((a, b) => {
                    const da = Math.hypot(a.exitOutsideX - mapCenter.x, a.exitOutsideY - mapCenter.y);
                    const db = Math.hypot(b.exitOutsideX - mapCenter.x, b.exitOutsideY - mapCenter.y);
                    return da - db;
                });
                bestCandidate = candidates[0];
            }

            const portal = {
                x: bestCandidate.x,
                y: bestCandidate.y,
                width: doorWidth,
                height: 20,
                wallSide: bestCandidate.side,
                entryInsideX: bestCandidate.entryInsideX,
                entryInsideY: bestCandidate.entryInsideY,
                exitOutsideX: bestCandidate.exitOutsideX,
                exitOutsideY: bestCandidate.exitOutsideY
            };

            roomDoorPortalsCache.set(r.id, portal);
            return portal;
        }

        function isClickOnDoorPortal(clickX, clickY) {
            for (const r of rooms) {
                const door = getRoomDoorPortal(r);
                if (door) {
                    if (door.wallSide === 'bottom' || door.wallSide === 'top') {
                        if (Math.abs(clickX - door.x) <= (door.width / 2) + 14 && Math.abs(clickY - door.y) <= 24) {
                            return { room: r, door: door };
                        }
                    } else { // 'left' or 'right'
                        if (Math.abs(clickY - door.y) <= (door.width / 2) + 14 && Math.abs(clickX - door.x) <= 24) {
                            return { room: r, door: door };
                        }
                    }
                }
            }
            return null;
        }

        function toggleDoorByClick(room) {
            const myCurrentRoom = getCurrentRoom(localAvatar.x, localAvatar.y);
            const isInside = myCurrentRoom && myCurrentRoom.id === room.id;

            if (!isInside) {
                const isCurrentlyLocked = !!roomDoorStates.get(room.id);
                if (isCurrentlyLocked) {
                    playDoorKnockSound();
                    if (confirm(`🚪 ${room.name} {{ __("is locked. Would you like to knock?") }}`)) {
                        if (ws && ws.readyState === WebSocket.OPEN) {
                            ws.send(JSON.stringify({ type: 'room.knock', payload: { roomId: room.id, roomName: room.name } }));
                            showToast('⏳ ' + __('Knocked on door... waiting for occupant response.'));
                        }
                    }
                } else {
                    showToast('💡 ' + __('Only occupants inside this room can control its door. Double-click inside to enter.'));
                }
                return;
            }

            const occupants = countRoomOccupants(room.id);
            const isCurrentlyLocked = !!roomDoorStates.get(room.id);
            const nextLocked = !isCurrentlyLocked;

            if (occupants === 0 && nextLocked) {
                showToast('⚠️ ' + __('Cannot lock an empty room. The door must remain open when empty.'));
                return;
            }

            roomDoorStates.set(room.id, nextLocked);

            let animState = doorAnimationStates.get(room.id) || { openProgress: 0, isAnimating: false };
            animState.openProgress = nextLocked ? 0.0 : 1.0;
            animState.isAnimating = false;
            doorAnimationStates.set(room.id, animState);

            if (nextLocked) {
                playDoorKnockSound();
                showToast(`🔒 ${room.name} ` + __('door closed & locked'));
            } else {
                playDoorSlideSound();
                showToast(`🔓 ${room.name} ` + __('door opened'));
            }

            if (ws && ws.readyState === WebSocket.OPEN) {
                ws.send(JSON.stringify({ type: 'room.door_toggle', payload: { roomId: room.id, isClosed: nextLocked } }));
            }
            updateRoomPresence();
        }

        // ── Authoritative Obstacle-Aware Navigation & Solid Wall Collision Engine ──
        const AVATAR_COLLISION_RADIUS = 12;
        const NAV_GRID_STEP = 16;

        function getRoomById(id) {
            return rooms.find(r => r.id === id) || null;
        }

        function isPointInsideRoom(px, py, room, innerMargin = 0) {
            if (!room || !room.bounds) return false;
            const rx = room.bounds.x * TILE_SIZE;
            const ry = room.bounds.y * TILE_SIZE;
            const rw = room.bounds.width * TILE_SIZE;
            const rh = room.bounds.height * TILE_SIZE;
            return (px > rx + innerMargin && px < rx + rw - innerMargin &&
                    py > ry + innerMargin && py < ry + rh - innerMargin);
        }

        function isPointInsideAnyForbiddenRoom(px, py, allowedRoomId = null) {
            for (const r of rooms) {
                if (!r.bounds || (allowedRoomId && r.id === allowedRoomId)) continue;
                if (isPointInsideRoom(px, py, r, 2)) {
                    return true;
                }
            }
            return false;
        }

        function getRoomDoors(r) {
            if (!r || !r.bounds) return [];
            if (Array.isArray(r.doors) && r.doors.length > 0) {
                return r.doors.map(d => ({
                    x: d.x,
                    y: d.y,
                    width: d.width || 56,
                    wallSide: (d.wallSide || d.side || 'bottom').toLowerCase(),
                    entryInsideX: d.entryInsideX,
                    entryInsideY: d.entryInsideY,
                    exitOutsideX: d.exitOutsideX,
                    exitOutsideY: d.exitOutsideY
                }));
            }
            if (Array.isArray(r.bounds.doors) && r.bounds.doors.length > 0) {
                return r.bounds.doors.map(d => ({
                    x: d.x,
                    y: d.y,
                    width: d.width || 56,
                    wallSide: (d.wallSide || d.side || 'bottom').toLowerCase(),
                    entryInsideX: d.entryInsideX,
                    entryInsideY: d.entryInsideY,
                    exitOutsideX: d.exitOutsideX,
                    exitOutsideY: d.exitOutsideY
                }));
            }

            const single = getRoomDoorPortal(r);
            return single ? [single] : [];
        }

        function distPointToSegment(px, py, x1, y1, x2, y2) {
            const dx = x2 - x1;
            const dy = y2 - y1;
            const lenSq = dx * dx + dy * dy;
            if (lenSq === 0) return Math.hypot(px - x1, py - y1);
            let t = ((px - x1) * dx + (py - y1) * dy) / lenSq;
            t = Math.max(0, Math.min(1, t));
            const projX = x1 + t * dx;
            const projY = y1 + t * dy;
            return Math.hypot(px - projX, py - projY);
        }

        function segmentsIntersect(x1, y1, x2, y2, x3, y3, x4, y4) {
            function ccw(ax, ay, bx, by, cx, cy) {
                return (cy - ay) * (bx - ax) > (by - ay) * (cx - ax);
            }
            return (ccw(x1, y1, x3, y3, x4, y4) !== ccw(x2, y2, x3, y3, x4, y4)) &&
                   (ccw(x1, y1, x2, y2, x3, y3) !== ccw(x1, y1, x2, y2, x4, y4));
        }

        function distBetweenSegments(x1, y1, x2, y2, x3, y3, x4, y4) {
            if (segmentsIntersect(x1, y1, x2, y2, x3, y3, x4, y4)) return 0;
            return Math.min(
                distPointToSegment(x1, y1, x3, y3, x4, y4),
                distPointToSegment(x2, y2, x3, y3, x4, y4),
                distPointToSegment(x3, y3, x1, y1, x2, y2),
                distPointToSegment(x4, y4, x1, y1, x2, y2)
            );
        }

        // Authoritative Solid Wall Geometry:
        // Walls are 100% solid by default across ALL rooms.
        // Valid unlocked doors create explicit walkable portal openings in those walls.
        function getAllSolidWallSegments() {
            const segments = [];
            for (const r of rooms) {
                if (!r.bounds) continue;
                const rx = r.bounds.x * TILE_SIZE;
                const ry = r.bounds.y * TILE_SIZE;
                const rw = r.bounds.width * TILE_SIZE;
                const rh = r.bounds.height * TILE_SIZE;
                const isLocked = !!roomDoorStates.get(r.id);
                const doors = isLocked ? [] : getRoomDoors(r);

                // 1. Top Wall (y = ry, x from rx to rx + rw)
                const topDoors = doors.filter(d => d.wallSide === 'top').sort((a, b) => a.x - b.x);
                let curX = rx;
                for (const d of topDoors) {
                    const openStart = Math.max(rx, d.x - d.width / 2);
                    const openEnd = Math.min(rx + rw, d.x + d.width / 2);
                    if (openStart > curX + 1) {
                        segments.push({ x1: curX, y1: ry, x2: openStart, y2: ry, roomId: r.id });
                    }
                    curX = Math.max(curX, openEnd);
                }
                if (rx + rw > curX + 1) {
                    segments.push({ x1: curX, y1: ry, x2: rx + rw, y2: ry, roomId: r.id });
                }

                // 2. Bottom Wall (y = ry + rh, x from rx to rx + rw)
                const bottomDoors = doors.filter(d => d.wallSide === 'bottom').sort((a, b) => a.x - b.x);
                curX = rx;
                for (const d of bottomDoors) {
                    const openStart = Math.max(rx, d.x - d.width / 2);
                    const openEnd = Math.min(rx + rw, d.x + d.width / 2);
                    if (openStart > curX + 1) {
                        segments.push({ x1: curX, y1: ry + rh, x2: openStart, y2: ry + rh, roomId: r.id });
                    }
                    curX = Math.max(curX, openEnd);
                }
                if (rx + rw > curX + 1) {
                    segments.push({ x1: curX, y1: ry + rh, x2: rx + rw, y2: ry + rh, roomId: r.id });
                }

                // 3. Left Wall (x = rx, y from ry to ry + rh)
                const leftDoors = doors.filter(d => d.wallSide === 'left').sort((a, b) => a.y - b.y);
                let curY = ry;
                for (const d of leftDoors) {
                    const openStart = Math.max(ry, d.y - d.width / 2);
                    const openEnd = Math.min(ry + rh, d.y + d.width / 2);
                    if (openStart > curY + 1) {
                        segments.push({ x1: rx, y1: curY, x2: rx, y2: openStart, roomId: r.id });
                    }
                    curY = Math.max(curY, openEnd);
                }
                if (ry + rh > curY + 1) {
                    segments.push({ x1: rx, y1: curY, x2: rx, y2: ry + rh, roomId: r.id });
                }

                // 4. Right Wall (x = rx + rw, y from ry to ry + rh)
                const rightDoors = doors.filter(d => d.wallSide === 'right').sort((a, b) => a.y - b.y);
                curY = ry;
                for (const d of rightDoors) {
                    const openStart = Math.max(ry, d.y - d.width / 2);
                    const openEnd = Math.min(ry + rh, d.y + d.width / 2);
                    if (openStart > curY + 1) {
                        segments.push({ x1: rx + rw, y1: curY, x2: rx + rw, y2: openStart, roomId: r.id });
                    }
                    curY = Math.max(curY, openEnd);
                }
                if (ry + rh > curY + 1) {
                    segments.push({ x1: rx + rw, y1: curY, x2: rx + rw, y2: ry + rh, roomId: r.id });
                }
            }
            return segments;
        }

        function checkCapsuleWallCollision(x1, y1, x2, y2, radius = AVATAR_COLLISION_RADIUS) {
            const walls = getAllSolidWallSegments();
            for (const w of walls) {
                const dist = distBetweenSegments(x1, y1, x2, y2, w.x1, w.y1, w.x2, w.y2);
                if (dist < radius) {
                    return true; // Collision detected!
                }
            }
            return false;
        }

        function isPathClear(p1, p2, allowedRoomId = null, radius = AVATAR_COLLISION_RADIUS) {
            if (!p1 || !p2) return false;
            if (checkCapsuleWallCollision(p1.x, p1.y, p2.x, p2.y, radius)) {
                return false;
            }
            const dist = Math.hypot(p2.x - p1.x, p2.y - p1.y);
            const steps = Math.max(2, Math.ceil(dist / 12));
            for (let i = 1; i < steps; i++) {
                const t = i / steps;
                const sx = p1.x + t * (p2.x - p1.x);
                const sy = p1.y + t * (p2.y - p1.y);
                if (allowedRoomId === null) {
                    if (isPointInsideAnyForbiddenRoom(sx, sy, null)) return false;
                } else {
                    const r = getRoomById(allowedRoomId);
                    if (r && r.bounds) {
                        const rx = r.bounds.x * TILE_SIZE;
                        const ry = r.bounds.y * TILE_SIZE;
                        const rw = r.bounds.width * TILE_SIZE;
                        const rh = r.bounds.height * TILE_SIZE;
                        if (sx < rx || sx > rx + rw || sy < ry || sy > ry + rh) return false;
                    }
                }
            }
            return true;
        }

        function findAStarPath(start, goal, allowedRoomId = null) {
            if (!start || !goal) return null;

            const cols = Math.ceil(MAP_WIDTH_PX / NAV_GRID_STEP);
            const rows = Math.ceil(MAP_HEIGHT_PX / NAV_GRID_STEP);

            const startC = Math.max(0, Math.min(cols - 1, Math.floor(start.x / NAV_GRID_STEP)));
            const startR = Math.max(0, Math.min(rows - 1, Math.floor(start.y / NAV_GRID_STEP)));
            const goalC = Math.max(0, Math.min(cols - 1, Math.floor(goal.x / NAV_GRID_STEP)));
            const goalR = Math.max(0, Math.min(rows - 1, Math.floor(goal.y / NAV_GRID_STEP)));

            const openSet = [{ f: 0, g: 0, c: startC, r: startR, parent: null }];
            const visited = new Map();

            const dirs = [
                { dc: 1, dr: 0, cost: 1.0 },
                { dc: -1, dr: 0, cost: 1.0 },
                { dc: 0, dr: 1, cost: 1.0 },
                { dc: 0, dr: -1, cost: 1.0 },
                { dc: 1, dr: 1, cost: 1.414 },
                { dc: 1, dr: -1, cost: 1.414 },
                { dc: -1, dr: 1, cost: 1.414 },
                { dc: -1, dr: -1, cost: 1.414 }
            ];

            let goalNode = null;
            let iterations = 0;
            const maxIterations = 4000;

            while (openSet.length > 0 && iterations++ < maxIterations) {
                let bestIdx = 0;
                for (let i = 1; i < openSet.length; i++) {
                    if (openSet[i].f < openSet[bestIdx].f) bestIdx = i;
                }
                const current = openSet.splice(bestIdx, 1)[0];
                const key = `${current.c},${current.r}`;

                if (visited.has(key) && visited.get(key).g <= current.g) continue;
                visited.set(key, current);

                if (current.c === goalC && current.r === goalR) {
                    goalNode = current;
                    break;
                }

                const curPx = current.c * NAV_GRID_STEP + (NAV_GRID_STEP / 2);
                const curPy = current.r * NAV_GRID_STEP + (NAV_GRID_STEP / 2);

                for (const d of dirs) {
                    const nc = current.c + d.dc;
                    const nr = current.r + d.dr;
                    if (nc < 0 || nc >= cols || nr < 0 || nr >= rows) continue;

                    const nKey = `${nc},${nr}`;
                    if (visited.has(nKey)) continue;

                    const npx = nc * NAV_GRID_STEP + (NAV_GRID_STEP / 2);
                    const npy = nr * NAV_GRID_STEP + (NAV_GRID_STEP / 2);

                    // Room boundary checks
                    if (allowedRoomId === null) {
                        if (isPointInsideAnyForbiddenRoom(npx, npy, null)) continue;
                    } else {
                        const r = getRoomById(allowedRoomId);
                        if (r && r.bounds) {
                            const rx = r.bounds.x * TILE_SIZE;
                            const ry = r.bounds.y * TILE_SIZE;
                            const rw = r.bounds.width * TILE_SIZE;
                            const rh = r.bounds.height * TILE_SIZE;
                            if (npx < rx + 2 || npx > rx + rw - 2 || npy < ry + 2 || npy > ry + rh - 2) continue;
                        }
                    }

                    // Diagonal corner clearance
                    if (d.dc !== 0 && d.dr !== 0) {
                        const cornerX1 = (current.c + d.dc) * NAV_GRID_STEP + (NAV_GRID_STEP / 2);
                        const cornerY1 = current.r * NAV_GRID_STEP + (NAV_GRID_STEP / 2);
                        const cornerX2 = current.c * NAV_GRID_STEP + (NAV_GRID_STEP / 2);
                        const cornerY2 = (current.r + d.dr) * NAV_GRID_STEP + (NAV_GRID_STEP / 2);

                        if (checkCapsuleWallCollision(curPx, curPy, cornerX1, cornerY1, AVATAR_COLLISION_RADIUS) ||
                            checkCapsuleWallCollision(curPx, curPy, cornerX2, cornerY2, AVATAR_COLLISION_RADIUS) ||
                            checkCapsuleWallCollision(curPx, curPy, npx, npy, AVATAR_COLLISION_RADIUS)) {
                            continue;
                        }
                        if (allowedRoomId === null) {
                            if (isPointInsideAnyForbiddenRoom(cornerX1, cornerY1, null) ||
                                isPointInsideAnyForbiddenRoom(cornerX2, cornerY2, null)) {
                                continue;
                            }
                        }
                    }

                    // Edge collision
                    if (checkCapsuleWallCollision(curPx, curPy, npx, npy, AVATAR_COLLISION_RADIUS)) continue;

                    const ng = current.g + d.cost * NAV_GRID_STEP;
                    const h = Math.hypot(npx - goal.x, npy - goal.y);
                    openSet.push({ f: ng + h, g: ng, c: nc, r: nr, parent: current });
                }
            }

            if (!goalNode) {
                return null; // Explicit failure: NEVER return [goal]
            }

            const path = [];
            let curr = goalNode;
            while (curr) {
                path.push({
                    x: curr.c * NAV_GRID_STEP + (NAV_GRID_STEP / 2),
                    y: curr.r * NAV_GRID_STEP + (NAV_GRID_STEP / 2)
                });
                curr = curr.parent;
            }
            path.reverse();
            path.push({ x: goal.x, y: goal.y });
            return path;
        }

        function simplifyPath(rawPath, allowedRoomId = null) {
            if (!rawPath || !Array.isArray(rawPath) || rawPath.length === 0) return [];
            if (rawPath.length <= 2) return [...rawPath];
            const smoothed = [rawPath[0]];
            let currentIdx = 0;

            while (currentIdx < rawPath.length - 1) {
                let furthestIdx = currentIdx + 1;
                for (let testIdx = rawPath.length - 1; testIdx > currentIdx + 1; testIdx--) {
                    if (isPathClear(rawPath[currentIdx], rawPath[testIdx], allowedRoomId, AVATAR_COLLISION_RADIUS)) {
                        furthestIdx = testIdx;
                        break;
                    }
                }
                smoothed.push(rawPath[furthestIdx]);
                currentIdx = furthestIdx;
            }
            return smoothed;
        }

        function isNearRoomDoorPortal(room, x, y, maxDist = 36) {
            if (!room) return false;
            const doors = getRoomDoors(room);
            for (const d of doors) {
                if (Math.hypot(x - d.x, y - d.y) <= maxDist) return true;
            }
            return false;
        }

        function validateCompleteRoute(route) {
            if (!route || !Array.isArray(route) || route.length === 0) return false;
            for (let i = 0; i < route.length - 1; i++) {
                const p1 = route[i];
                const p2 = route[i + 1];
                if (checkCapsuleWallCollision(p1.x, p1.y, p2.x, p2.y, AVATAR_COLLISION_RADIUS)) {
                    return false;
                }
            }
            return true;
        }

        function buildNavigationRoute(startPos, destPos, srcRoom = null, dstRoom = null) {
            if (!srcRoom) srcRoom = getCurrentRoom(startPos.x, startPos.y);
            if (!dstRoom) dstRoom = getCurrentRoom(destPos.x, destPos.y);

            // Guard: Destination or Source locked room check
            if (dstRoom && roomDoorStates.get(dstRoom.id) && (!srcRoom || srcRoom.id !== dstRoom.id)) {
                return null;
            }
            if (srcRoom && roomDoorStates.get(srcRoom.id) && (!dstRoom || dstRoom.id !== srcRoom.id)) {
                return null;
            }

            // Case 1: Same Room Movement
            if (srcRoom && dstRoom && srcRoom.id === dstRoom.id) {
                const rx = srcRoom.bounds.x * TILE_SIZE;
                const ry = srcRoom.bounds.y * TILE_SIZE;
                const rw = srcRoom.bounds.width * TILE_SIZE;
                const rh = srcRoom.bounds.height * TILE_SIZE;
                const margin = AVATAR_COLLISION_RADIUS + 4;
                const clampedX = Math.max(rx + margin, Math.min(rx + rw - margin, destPos.x));
                const clampedY = Math.max(ry + margin, Math.min(ry + rh - margin, destPos.y));
                const clampedDest = { x: clampedX, y: clampedY };

                if (isPathClear(startPos, clampedDest, srcRoom.id, AVATAR_COLLISION_RADIUS)) {
                    return [{ x: clampedX, y: clampedY, action: null }];
                }
                const raw = findAStarPath(startPos, clampedDest, srcRoom.id);
                if (!raw) return null;
                const simplified = simplifyPath(raw, srcRoom.id);
                const waypoints = simplified.map(pt => ({ x: pt.x, y: pt.y, action: null }));
                return validateCompleteRoute(waypoints) ? waypoints : null;
            }

            // Case 2: Open Space to Open Space
            if (!srcRoom && !dstRoom) {
                const clampedX = Math.max(16, Math.min(MAP_WIDTH_PX - 16, destPos.x));
                const clampedY = Math.max(16, Math.min(MAP_HEIGHT_PX - 16, destPos.y));
                const clampedDest = { x: clampedX, y: clampedY };

                if (isPathClear(startPos, clampedDest, null, AVATAR_COLLISION_RADIUS)) {
                    return [{ x: clampedX, y: clampedY, action: null }];
                }
                const raw = findAStarPath(startPos, clampedDest, null);
                if (!raw) return null;
                const simplified = simplifyPath(raw, null);
                const waypoints = simplified.map(pt => ({ x: pt.x, y: pt.y, action: null }));
                return validateCompleteRoute(waypoints) ? waypoints : null;
            }

            // Case 3: Inside Room to Open Space (Evaluate all source doors for shortest valid path)
            if (srcRoom && !dstRoom) {
                const curDoors = getRoomDoors(srcRoom);
                let bestRoute = null;
                let bestDist = Infinity;

                for (const curDoor of curDoors) {
                    let insideLeg = [{ x: curDoor.entryInsideX, y: curDoor.entryInsideY }];
                    if (!isPathClear(startPos, { x: curDoor.entryInsideX, y: curDoor.entryInsideY }, srcRoom.id, AVATAR_COLLISION_RADIUS)) {
                        const insideRaw = findAStarPath(startPos, { x: curDoor.entryInsideX, y: curDoor.entryInsideY }, srcRoom.id);
                        if (!insideRaw) continue;
                        insideLeg = simplifyPath(insideRaw, srcRoom.id).slice(1);
                    }

                    let openLeg = [{ x: destPos.x, y: destPos.y }];
                    if (!isPathClear({ x: curDoor.exitOutsideX, y: curDoor.exitOutsideY }, destPos, null, AVATAR_COLLISION_RADIUS)) {
                        const openRaw = findAStarPath({ x: curDoor.exitOutsideX, y: curDoor.exitOutsideY }, destPos, null);
                        if (!openRaw) continue;
                        openLeg = simplifyPath(openRaw, null).slice(1);
                    }

                    const candidate = [
                        ...insideLeg.slice(0, -1).map(pt => ({ x: pt.x, y: pt.y, action: null })),
                        {
                            x: curDoor.entryInsideX,
                            y: curDoor.entryInsideY,
                            action: () => {
                                playDoorSlideSound();
                                triggerDoorAnimation(srcRoom.id);
                            }
                        },
                        {
                            x: curDoor.x,
                            y: curDoor.y,
                            action: null
                        },
                        {
                            x: curDoor.exitOutsideX,
                            y: curDoor.exitOutsideY,
                            action: null
                        },
                        ...openLeg.map(pt => ({ x: pt.x, y: pt.y, action: null }))
                    ];

                    if (!validateCompleteRoute(candidate)) continue;

                    let dist = Math.hypot(startPos.x - candidate[0].x, startPos.y - candidate[0].y);
                    for (let i = 0; i < candidate.length - 1; i++) {
                        dist += Math.hypot(candidate[i+1].x - candidate[i].x, candidate[i+1].y - candidate[i].y);
                    }
                    if (dist < bestDist) {
                        bestDist = dist;
                        bestRoute = candidate;
                    }
                }
                return bestRoute;
            }

            // Case 4: Open Space to Inside Room (Evaluate all target doors for shortest valid path)
            if (!srcRoom && dstRoom) {
                const rx = dstRoom.bounds.x * TILE_SIZE;
                const ry = dstRoom.bounds.y * TILE_SIZE;
                const rw = dstRoom.bounds.width * TILE_SIZE;
                const rh = dstRoom.bounds.height * TILE_SIZE;
                const margin = AVATAR_COLLISION_RADIUS + 4;
                const clampedX = Math.max(rx + margin, Math.min(rx + rw - margin, destPos.x));
                const clampedY = Math.max(ry + margin, Math.min(ry + rh - margin, destPos.y));
                const clampedDest = { x: clampedX, y: clampedY };

                const targetDoors = getRoomDoors(dstRoom);
                let bestRoute = null;
                let bestDist = Infinity;

                for (const targetDoor of targetDoors) {
                    let openLeg = [{ x: targetDoor.exitOutsideX, y: targetDoor.exitOutsideY }];
                    if (!isPathClear(startPos, { x: targetDoor.exitOutsideX, y: targetDoor.exitOutsideY }, null, AVATAR_COLLISION_RADIUS)) {
                        const openRaw = findAStarPath(startPos, { x: targetDoor.exitOutsideX, y: targetDoor.exitOutsideY }, null);
                        if (!openRaw) continue;
                        openLeg = simplifyPath(openRaw, null).slice(1);
                    }

                    let insideLeg = [{ x: clampedX, y: clampedY }];
                    if (!isPathClear({ x: targetDoor.entryInsideX, y: targetDoor.entryInsideY }, clampedDest, dstRoom.id, AVATAR_COLLISION_RADIUS)) {
                        const insideRaw = findAStarPath({ x: targetDoor.entryInsideX, y: targetDoor.entryInsideY }, clampedDest, dstRoom.id);
                        if (!insideRaw) continue;
                        insideLeg = simplifyPath(insideRaw, dstRoom.id).slice(1);
                    }

                    const candidate = [
                        ...openLeg.slice(0, -1).map(pt => ({ x: pt.x, y: pt.y, action: null })),
                        {
                            x: targetDoor.exitOutsideX,
                            y: targetDoor.exitOutsideY,
                            action: () => {
                                playDoorSlideSound();
                                triggerDoorAnimation(dstRoom.id);
                            }
                        },
                        {
                            x: targetDoor.x,
                            y: targetDoor.y,
                            action: null
                        },
                        {
                            x: targetDoor.entryInsideX,
                            y: targetDoor.entryInsideY,
                            action: null
                        },
                        ...insideLeg.map(pt => ({ x: pt.x, y: pt.y, action: null }))
                    ];

                    if (!validateCompleteRoute(candidate)) continue;

                    let dist = Math.hypot(startPos.x - candidate[0].x, startPos.y - candidate[0].y);
                    for (let i = 0; i < candidate.length - 1; i++) {
                        dist += Math.hypot(candidate[i+1].x - candidate[i].x, candidate[i+1].y - candidate[i].y);
                    }
                    if (dist < bestDist) {
                        bestDist = dist;
                        bestRoute = candidate;
                    }
                }
                return bestRoute;
            }

            // Case 5: Room A to Room B (Evaluate all door combinations for shortest valid path)
            if (srcRoom && dstRoom && srcRoom.id !== dstRoom.id) {
                const rx = dstRoom.bounds.x * TILE_SIZE;
                const ry = dstRoom.bounds.y * TILE_SIZE;
                const rw = dstRoom.bounds.width * TILE_SIZE;
                const rh = dstRoom.bounds.height * TILE_SIZE;
                const margin = AVATAR_COLLISION_RADIUS + 4;
                const clampedX = Math.max(rx + margin, Math.min(rx + rw - margin, destPos.x));
                const clampedY = Math.max(ry + margin, Math.min(ry + rh - margin, destPos.y));
                const clampedDest = { x: clampedX, y: clampedY };

                const srcDoors = getRoomDoors(srcRoom);
                const dstDoors = getRoomDoors(dstRoom);
                let bestRoute = null;
                let bestDist = Infinity;

                for (const sDoor of srcDoors) {
                    for (const dDoor of dstDoors) {
                        let srcInsideLeg = [{ x: sDoor.entryInsideX, y: sDoor.entryInsideY }];
                        if (!isPathClear(startPos, { x: sDoor.entryInsideX, y: sDoor.entryInsideY }, srcRoom.id, AVATAR_COLLISION_RADIUS)) {
                            const srcRaw = findAStarPath(startPos, { x: sDoor.entryInsideX, y: sDoor.entryInsideY }, srcRoom.id);
                            if (!srcRaw) continue;
                            srcInsideLeg = simplifyPath(srcRaw, srcRoom.id).slice(1);
                        }

                        let openLeg = [{ x: dDoor.exitOutsideX, y: dDoor.exitOutsideY }];
                        if (!isPathClear({ x: sDoor.exitOutsideX, y: sDoor.exitOutsideY }, { x: dDoor.exitOutsideX, y: dDoor.exitOutsideY }, null, AVATAR_COLLISION_RADIUS)) {
                            const openRaw = findAStarPath({ x: sDoor.exitOutsideX, y: sDoor.exitOutsideY }, { x: dDoor.exitOutsideX, y: dDoor.exitOutsideY }, null);
                            if (!openRaw) continue;
                            openLeg = simplifyPath(openRaw, null).slice(1);
                        }

                        let dstInsideLeg = [{ x: clampedX, y: clampedY }];
                        if (!isPathClear({ x: dDoor.entryInsideX, y: dDoor.entryInsideY }, clampedDest, dstRoom.id, AVATAR_COLLISION_RADIUS)) {
                            const dstRaw = findAStarPath({ x: dDoor.entryInsideX, y: dDoor.entryInsideY }, clampedDest, dstRoom.id);
                            if (!dstRaw) continue;
                            dstInsideLeg = simplifyPath(dstRaw, dstRoom.id).slice(1);
                        }

                        const candidate = [
                            ...srcInsideLeg.slice(0, -1).map(pt => ({ x: pt.x, y: pt.y, action: null })),
                            {
                                x: sDoor.entryInsideX,
                                y: sDoor.entryInsideY,
                                action: () => {
                                    playDoorSlideSound();
                                    triggerDoorAnimation(srcRoom.id);
                                }
                            },
                            {
                                x: sDoor.x,
                                y: sDoor.y,
                                action: null
                            },
                            {
                                x: sDoor.exitOutsideX,
                                y: sDoor.exitOutsideY,
                                action: null
                            },
                            ...openLeg.slice(0, -1).map(pt => ({ x: pt.x, y: pt.y, action: null })),
                            {
                                x: dDoor.exitOutsideX,
                                y: dDoor.exitOutsideY,
                                action: () => {
                                    playDoorSlideSound();
                                    triggerDoorAnimation(dstRoom.id);
                                }
                            },
                            {
                                x: dDoor.x,
                                y: dDoor.y,
                                action: null
                            },
                            {
                                x: dDoor.entryInsideX,
                                y: dDoor.entryInsideY,
                                action: null
                            },
                            ...dstInsideLeg.map(pt => ({ x: pt.x, y: pt.y, action: null }))
                        ];

                        if (!validateCompleteRoute(candidate)) continue;

                        let dist = Math.hypot(startPos.x - candidate[0].x, startPos.y - candidate[0].y);
                        for (let i = 0; i < candidate.length - 1; i++) {
                            dist += Math.hypot(candidate[i+1].x - candidate[i].x, candidate[i+1].y - candidate[i].y);
                        }
                        if (dist < bestDist) {
                            bestDist = dist;
                            bestRoute = candidate;
                        }
                    }
                }
                return bestRoute;
            }

            return null;
        }

        function navigateToRoomWithRoute(targetRoom, destX, destY) {
            const myRoom = getCurrentRoom(localAvatar.x, localAvatar.y);
            const route = buildNavigationRoute(
                { x: localAvatar.x, y: localAvatar.y },
                { x: destX, y: destY },
                myRoom,
                targetRoom
            );
            if (route && route.length > 0) {
                avatarWaypoints = [...route];
                const firstWp = avatarWaypoints.shift();
                localAvatar.targetX = firstWp.x;
                localAvatar.targetY = firstWp.y;
                if (firstWp.action) firstWp.action();
            } else {
                showToast('⚠️ ' + __('No route available'));
            }
        }

        function triggerDoorAnimation(roomId) {
            let state = doorAnimationStates.get(roomId);
            if (!state) {
                state = { openProgress: 0, isAnimating: true };
                doorAnimationStates.set(roomId, state);
            }
            state.openProgress = 1.0; // Open door
            state.isAnimating = true;

            // Auto close door smoothly after avatar passes
            setTimeout(() => {
                if (doorAnimationStates.has(roomId)) {
                    doorAnimationStates.get(roomId).openProgress = 0.0;
                }
            }, 2400);
        }

        canvas.addEventListener('click', (e) => {
            if (hasMovedMouseDuringDrag) {
                hasMovedMouseDuringDrag = false;
                return;
            }
            const rect = canvas.getBoundingClientRect();
            const clickX = (e.clientX - rect.left - cameraOffset.x) / zoomLevel;
            const clickY = (e.clientY - rect.top - cameraOffset.y) / zoomLevel;
            const now = Date.now();

            // 1. Check if clicking an avatar (local or remote) to open Spotlight & Task List
            if (Math.hypot(clickX - localAvatar.x, clickY - localAvatar.y) < 32) {
                openUserSpotlight(localAvatar.id);
                return;
            }

            let clickedRemote = null;
            remoteAvatars.forEach(av => {
                if (Math.hypot(clickX - av.x, clickY - av.y) < 32) {
                    clickedRemote = av;
                }
            });

            if (clickedRemote) {
                openUserSpotlight(clickedRemote.id);
                return;
            }

            // 1b. Check if clicking directly on any Room Door to Open / Close / Lock
            const doorHit = isClickOnDoorPortal(clickX, clickY);
            if (doorHit) {
                toggleDoorByClick(doorHit.room);
                return;
            }

            // 1c. Check if clicking on an interactive Media / Furniture Object
            const mapObjectsList = ((CONFIG.map && CONFIG.map.objects) ? [...CONFIG.map.objects] : []);
            let clickedInteractiveObj = null;
            for (let i = mapObjectsList.length - 1; i >= 0; i--) {
                const obj = mapObjectsList[i];
                const ox = (obj.position ? obj.position.x : (obj.x || 0)) * TILE_SIZE;
                const oy = (obj.position ? obj.position.y : (obj.y || 0)) * TILE_SIZE;
                const ow = (obj.width || (obj.size ? obj.size.width : 1)) * TILE_SIZE;
                const oh = (obj.height || (obj.size ? obj.size.height : 1)) * TILE_SIZE;
                if (clickX >= ox && clickX <= ox + ow && clickY >= oy && clickY <= oy + oh) {
                    const iType = obj.interaction_type || (obj.interaction_config && obj.interaction_config.behavior?.type) || obj.type;
                    if (['stickyNote', 'sticky_note', 'link', 'custom_link', 'customImage', 'custom_image', 'branding'].includes(iType) || (obj.interaction_config && (obj.interaction_config.noteText || obj.interaction_config.url))) {
                        clickedInteractiveObj = obj;
                        break;
                    }
                }
            }

            if (clickedInteractiveObj) {
                handleInteractiveObjectClick(clickedInteractiveObj);
                return;
            }

            // Check room boundary & locking guards
            const targetRoom = getCurrentRoom(clickX, clickY);
            const myRoom = getCurrentRoom(localAvatar.x, localAvatar.y);

            // Double Click Detection for Entering Rooms
            const isDblClick = (now - lastCanvasClickTime < 380) && (Math.hypot(clickX - lastCanvasClickPos.x, clickY - lastCanvasClickPos.y) < 25);
            lastCanvasClickTime = now;
            lastCanvasClickPos = { x: clickX, y: clickY };

            if (targetRoom && targetRoom !== myRoom) {
                // Moving into another room REQUIRES Double-Click!
                if (!isDblClick) {
                    showToast(`💡 ${__('Double-click to navigate into:')} ${getLocalizedRoomName(targetRoom)}`);
                    return;
                }

                if (isGuest && guestAllowedRoomId) {
                    if (targetRoom.id !== guestAllowedRoomId) {
                        showToast(`🚫 {{ __("Guests are only permitted in their designated invited room.") }}`);
                        return;
                    }
                } else if (CONFIG.allowedRoomIds && CONFIG.allowedRoomIds.length > 0 && !CONFIG.allowedRoomIds.includes(targetRoom.id)) {
                    showToast(`🚫 {{ __("Restricted Room: You do not have permission to access ':name'.", ['name' => '']) }} ${getLocalizedRoomName(targetRoom)}`);
                    return;
                } else if (targetRoom.capacity && targetRoom.capacity > 0 && countRoomOccupants(targetRoom.id) >= targetRoom.capacity) {
                    showToast(`⚠️ {{ __("Room ':name' has reached full capacity (:max max occupants).", ['name' => '', 'max' => '']) }} ${getLocalizedRoomName(targetRoom)} (${targetRoom.capacity})`);
                    return;
                } else if (roomDoorStates.get(targetRoom.id)) {
                    playDoorKnockSound();
                    const locName = getLocalizedRoomName(targetRoom);
                    if (confirm(`🚪 ${locName} {{ __("is locked. Would you like to knock?") }}`)) {
                        if (ws && ws.readyState === WebSocket.OPEN) {
                            ws.send(JSON.stringify({ type: 'room.knock', payload: { roomId: targetRoom.id, roomName: locName } }));
                            showToast('⏳ {{ __("Knocked on door... waiting for occupant response.") }}');
                        }
                    }
                    return;
                }
            }

            // Calculate obstacle-aware collision-free route
            const route = buildNavigationRoute(
                { x: localAvatar.x, y: localAvatar.y },
                { x: clickX, y: clickY },
                myRoom,
                targetRoom
            );

            if (!route || route.length === 0) {
                showToast('⚠️ ' + __('No route available'));
                return;
            }

            avatarWaypoints = [...route];
            const firstWp = avatarWaypoints.shift();
            localAvatar.targetX = firstWp.x;
            localAvatar.targetY = firstWp.y;
            if (firstWp.action) firstWp.action();
        });

        // ── Interactive Media & Objects Click Controller ──
        function handleInteractiveObjectClick(obj) {
            const iType = obj.interaction_type || (obj.interaction_config && obj.interaction_config.behavior?.type) || obj.type;
            const cfg = obj.interaction_config || {};

            if (iType === 'stickyNote' || iType === 'sticky_note' || cfg.noteText || cfg.behavior?.type === 'stickyNote') {
                const noteText = cfg.noteText || cfg.behavior?.data?.text || obj.name || '{{ __("No note content.") }}';
                const modal = document.getElementById('sticky-note-modal');
                const title = document.getElementById('sticky-modal-title');
                const body = document.getElementById('sticky-modal-body');
                const card = document.getElementById('sticky-note-card');
                
                if (modal && body) {
                    body.textContent = noteText;
                    if (title) title.textContent = obj.name || '📌 {{ __("Sticky Note") }}';
                    
                    const col = cfg.color || 'yellow';
                    if (card) {
                        if (col === 'orange') {
                            card.style.background = '#FFEDD5';
                            card.style.borderColor = '#F97316';
                            card.style.color = '#7C2D12';
                        } else if (col === 'purple') {
                            card.style.background = '#F3E8FF';
                            card.style.borderColor = '#A855F7';
                            card.style.color = '#581C87';
                        } else if (col === 'green') {
                            card.style.background = '#DCFCE7';
                            card.style.borderColor = '#10B981';
                            card.style.color = '#14532D';
                        } else if (col === 'blue') {
                            card.style.background = '#E0F2FE';
                            card.style.borderColor = '#38BDF8';
                            card.style.color = '#0C4A6E';
                        } else {
                            card.style.background = '#FEF3C7';
                            card.style.borderColor = '#F59E0B';
                            card.style.color = '#78350F';
                        }
                    }
                    modal.style.display = 'flex';
                }
            } else if (iType === 'link' || iType === 'custom_link' || cfg.url || cfg.behavior?.type === 'link') {
                const targetUrl = cfg.url || cfg.behavior?.data?.url || '#';
                const linkTitle = cfg.title || cfg.behavior?.data?.title || obj.name || '{{ __("Interactive Link") }}';
                const openInNewTab = cfg.openInNewTab !== false && cfg.behavior?.data?.openInNewTab !== false;

                if (openInNewTab && targetUrl && targetUrl !== '#') {
                    window.open(targetUrl, '_blank');
                    showToast(`🚀 {{ __("Opening link:") }} ${linkTitle}`);
                } else {
                    const modal = document.getElementById('custom-link-modal');
                    const title = document.getElementById('custom-link-modal-title');
                    const urlBox = document.getElementById('custom-link-modal-url');
                    const btn = document.getElementById('custom-link-modal-btn');
                    if (modal && urlBox) {
                        if (title) title.textContent = linkTitle;
                        urlBox.textContent = targetUrl;
                        if (btn) btn.href = targetUrl;
                        modal.style.display = 'flex';
                    }
                }
            } else if (iType === 'customImage' || iType === 'custom_image' || cfg.behavior?.type === 'customImage') {
                const imgUrl = obj.image_url || cfg.image_url || cfg.behavior?.data?.imageUrl;
                if (imgUrl) {
                    const modal = document.getElementById('custom-image-modal');
                    const imgEl = document.getElementById('custom-image-modal-img');
                    const title = document.getElementById('custom-image-modal-title');
                    if (modal && imgEl) {
                        imgEl.src = imgUrl;
                        if (title) title.textContent = `🖼️ ${obj.name || '{{ __("Custom Image") }}'}`;
                        modal.style.display = 'flex';
                    }
                }
            } else if (iType === 'branding' || obj.type === 'branding') {
                showToast(`🏢 ${CONFIG.organization ? CONFIG.organization.name : '{{ __("Company Workplace") }}'}`);
            }
        }

        function closeStickyNoteModal() {
            const m = document.getElementById('sticky-note-modal');
            if (m) m.style.display = 'none';
        }

        function closeCustomImageModal() {
            const m = document.getElementById('custom-image-modal');
            if (m) m.style.display = 'none';
        }

        function closeCustomLinkModal() {
            const m = document.getElementById('custom-link-modal');
            if (m) m.style.display = 'none';
        }

        // ── Room Detection & Locking Logic ──
        function getCurrentRoom(x, y) {
            for (const r of rooms) {
                if (!r.bounds) continue;
                const rx = r.bounds.x * TILE_SIZE;
                const ry = r.bounds.y * TILE_SIZE;
                const rw = r.bounds.width * TILE_SIZE;
                const rh = r.bounds.height * TILE_SIZE;
                if (x >= rx && x <= rx + rw && y >= ry && y <= ry + rh) {
                    return r;
                }
            }
            return null;
        }

        function countRoomOccupants(roomId) {
            let count = 0;
            const myRoom = getCurrentRoom(localAvatar.x, localAvatar.y);
            if (myRoom && myRoom.id === roomId) count++;
            remoteAvatars.forEach(av => {
                const remRoom = getCurrentRoom(av.x, av.y);
                if (remRoom && remRoom.id === roomId) count++;
            });
            return count;
        }

        function checkAutoUnlockEmptyRooms() {
            rooms.forEach(r => {
                const occupants = countRoomOccupants(r.id);
                if (occupants === 0) {
                    // Empty rooms are unlocked and resting closed
                    roomDoorStates.set(r.id, false);
                    let animState = doorAnimationStates.get(r.id) || { openProgress: 0.0, isAnimating: false };
                    animState.openProgress = 0.0;
                    doorAnimationStates.set(r.id, animState);

                    if (ws && ws.readyState === WebSocket.OPEN) {
                        ws.send(JSON.stringify({ type: 'room.door_toggle', payload: { roomId: r.id, isClosed: false } }));
                    }
                }
            });
        }

        let cachedCurrentRoomId = undefined;
        let cachedRoomLockState = undefined;
        function updateRoomPresence() {
            const r = getCurrentRoom(localAvatar.x, localAvatar.y);
            const currentId = r ? r.id : null;
            const isLocked = r ? !!roomDoorStates.get(r.id) : false;

            if (cachedCurrentRoomId !== currentId || cachedRoomLockState !== isLocked) {
                cachedCurrentRoomId = currentId;
                cachedRoomLockState = isLocked;

                const statusPill = document.getElementById('room-status-pill');
                const roomNameEl = document.getElementById('current-room-name');
                const lockIcon = document.getElementById('lock-icon');
                const lockText = document.getElementById('lock-text');

                if (r) {
                    if (statusPill) statusPill.style.display = 'flex';
                    if (roomNameEl) roomNameEl.textContent = `🏢 ${getLocalizedRoomName(r)}`;
                    if (lockIcon) lockIcon.textContent = isLocked ? 'lock' : 'lock_open';
                    if (lockText) lockText.textContent = isLocked ? '{{ __("Unlock Door") }}' : '{{ __("Lock Door") }}';

                    if (localAvatar.currentRoomId !== r.id) {
                        const prevId = localAvatar.currentRoomId;
                        localAvatar.currentRoomId = r.id;
                        if (ws && ws.readyState === WebSocket.OPEN) {
                            ws.send(JSON.stringify({ type: 'room.enter', payload: { roomId: r.id } }));
                        }
                        syncLiveKitRoom(r.id);
                        logAttendanceInterval('enter', r.id);
                        if (prevId) {
                            logAttendanceInterval('leave', prevId);
                            checkAutoUnlockEmptyRooms();
                        }
                    }
                } else {
                    if (statusPill) statusPill.style.display = 'none';
                    if (localAvatar.currentRoomId) {
                        const prevId = localAvatar.currentRoomId;
                        localAvatar.currentRoomId = null;
                        if (ws && ws.readyState === WebSocket.OPEN) {
                            ws.send(JSON.stringify({ type: 'room.leave', payload: { roomId: prevId } }));
                        }
                        syncLiveKitRoom(null);
                        logAttendanceInterval('leave', prevId);
                        checkAutoUnlockEmptyRooms();
                    }
                }
            }
        }

        function toggleRoomDoorLock() {
            const r = getCurrentRoom(localAvatar.x, localAvatar.y);
            if (!r) {
                showToast('⚠️ {{ __("You must be inside a room to lock or unlock its door.") }}');
                return;
            }

            const isCurrentlyLocked = !!roomDoorStates.get(r.id);
            if (!isCurrentlyLocked) {
                const occupants = countRoomOccupants(r.id);
                if (occupants === 0) {
                    showToast('⚠️ {{ __("Cannot lock an empty room. Enter the room first to lock it.") }}');
                    return;
                }
            }

            const nextState = !isCurrentlyLocked;
            roomDoorStates.set(r.id, nextState);
            updateRoomPresence();

            if (ws && ws.readyState === WebSocket.OPEN) {
                ws.send(JSON.stringify({ type: 'room.door_toggle', payload: { roomId: r.id, isClosed: nextState } }));
            }
            showToast(nextState ? '🔒 {{ __("Room locked") }}' : '🔓 {{ __("Room unlocked") }}');
        }

        // ── Main Game & Render Loop with Solid Wall Collisions & Waypoint Following ──
        function update() {
            let dx = 0, dy = 0;
            if (keys['w'] || keys['arrowup']) dy -= 1;
            if (keys['s'] || keys['arrowdown']) dy += 1;
            if (keys['a'] || keys['arrowleft']) dx -= 1;
            if (keys['d'] || keys['arrowright']) dx += 1;

            let nextX = localAvatar.x;
            let nextY = localAvatar.y;

            if (localAvatar.isSitting) {
                // If user moves while sitting, auto stand up!
                if (dx !== 0 || dy !== 0) {
                    localAvatar.isSitting = false;
                    localAvatar.sittingFurnitureId = null;
                    if (ws && ws.readyState === WebSocket.OPEN) {
                        ws.send(JSON.stringify({ type: 'user.sit', payload: { isSitting: false } }));
                    }
                    showToast('🧍 {{ __("Stood up") }}');
                }
            } else {
                if (dx !== 0 || dy !== 0) {
                    avatarWaypoints = []; // Clear waypoints if manual key pressed
                    const len = Math.sqrt(dx * dx + dy * dy);
                    nextX += (dx / len) * localAvatar.speed;
                    nextY += (dy / len) * localAvatar.speed;
                    localAvatar.targetX = nextX;
                    localAvatar.targetY = nextY;
                } else {
                    const diffX = localAvatar.targetX - localAvatar.x;
                    const diffY = localAvatar.targetY - localAvatar.y;
                    const dist = Math.sqrt(diffX * diffX + diffY * diffY);
                    if (dist > 3) {
                        nextX += (diffX / dist) * localAvatar.speed;
                        nextY += (diffY / dist) * localAvatar.speed;
                    } else if (avatarWaypoints.length > 0) {
                        // Reached waypoint, advance to next waypoint!
                        const nextWp = avatarWaypoints.shift();
                        localAvatar.targetX = nextWp.x;
                        localAvatar.targetY = nextWp.y;
                        if (nextWp.action) nextWp.action();
                    }
                }
            }

            checkNearbyFurniture();

            // Check Physical Solid Wall Collisions for all rooms
            const currentR = getCurrentRoom(localAvatar.x, localAvatar.y);
            const targetR = getCurrentRoom(nextX, nextY);

            // Door lock & Room Permission Guard collision detection
            if (targetR && targetR !== currentR) {
                // 1. Guest Restriction Check
                if (isGuest && guestAllowedRoomId && targetR.id !== guestAllowedRoomId) {
                    nextX = localAvatar.x;
                    nextY = localAvatar.y;
                    localAvatar.targetX = localAvatar.x;
                    localAvatar.targetY = localAvatar.y;
                    showToast(`🚫 {{ __("Restricted Room: Guests are only permitted inside their invited meeting room.") }}`);
                }
                // 2. Member Room Permission Check
                else if (CONFIG.allowedRoomIds && CONFIG.allowedRoomIds.length > 0 && !CONFIG.allowedRoomIds.includes(targetR.id)) {
                    nextX = localAvatar.x;
                    nextY = localAvatar.y;
                    localAvatar.targetX = localAvatar.x;
                    localAvatar.targetY = localAvatar.y;
                    showToast(`🚫 {{ __("Restricted Room: Access not permitted for ':name'.", ['name' => '']) }} ${targetR.name}`);
                }
                // 3. Room Capacity Check
                else if (targetR.capacity && targetR.capacity > 0 && countRoomOccupants(targetR.id) >= targetR.capacity) {
                    nextX = localAvatar.x;
                    nextY = localAvatar.y;
                    localAvatar.targetX = localAvatar.x;
                    localAvatar.targetY = localAvatar.y;
                    showToast(`⚠️ {{ __("Room ':name' has reached full capacity (:max max occupants).", ['name' => '', 'max' => '']) }} ${targetR.name} (${targetR.capacity})`);
                }
                // 4. Door Lock Check
                else if (roomDoorStates.get(targetR.id)) {
                    nextX = localAvatar.x;
                    nextY = localAvatar.y;
                    localAvatar.targetX = localAvatar.x;
                    localAvatar.targetY = localAvatar.y;
                }
            }

            // 4. Solid Wall Physics: Block avatar from crossing solid room walls
            if (checkCapsuleWallCollision(localAvatar.x, localAvatar.y, nextX, nextY, AVATAR_COLLISION_RADIUS)) {
                nextX = localAvatar.x;
                nextY = localAvatar.y;
                localAvatar.targetX = localAvatar.x;
                localAvatar.targetY = localAvatar.y;
                avatarWaypoints = []; // Clear waypoints so avatar doesn't get stuck indefinitely
            }

            // Update Door Proximity Animation & Auto Open when approaching or inside
            rooms.forEach(r => {
                const door = getRoomDoorPortal(r);
                if (door) {
                    const isLocked = !!roomDoorStates.get(r.id);
                    let state = doorAnimationStates.get(r.id) || { openProgress: 0, isAnimating: false };

                    if (isLocked) {
                        state.openProgress = Math.max(0.0, state.openProgress - 0.1);
                    } else {
                        const distToDoor = Math.hypot(localAvatar.x - door.x, localAvatar.y - door.y);
                        const isInsideThisRoom = (currentR && currentR.id === r.id);
                        if (distToDoor < 45 || isInsideThisRoom) {
                            state.openProgress = Math.min(1.0, state.openProgress + 0.15);
                        } else if (!state.isAnimating) {
                            state.openProgress = Math.max(0.0, state.openProgress - 0.06);
                        }
                    }
                    doorAnimationStates.set(r.id, state);
                }
            });

            localAvatar.x = Math.max(10, Math.min(MAP_WIDTH_PX - 10, nextX));
            localAvatar.y = Math.max(10, Math.min(MAP_HEIGHT_PX - 10, nextY));

            updateRoomPresence();

            // Smooth remote avatar interpolation & Dynamic Spatial Audio + Screen Share Isolation
            const localRoom = getCurrentRoom(localAvatar.x, localAvatar.y);

            remoteAvatars.forEach(av => {
                av.x += (av.targetX - av.x) * 0.25;
                av.y += (av.targetY - av.y) * 0.25;

                const remoteRoom = getCurrentRoom(av.x, av.y);
                const isInSameRoom = localRoom ? (remoteRoom && remoteRoom.id === localRoom.id) : (!remoteRoom && Math.hypot(localAvatar.x - av.x, localAvatar.y - av.y) <= 250);

                // 1. Spatial Audio & Voice Isolation Engine (Strict Circle / Wall Boundary)
                const audioEl = peerAudioElements.get(av.id);
                if (audioEl) {
                    if (localRoom) {
                        // Inside Room: Only hear occupants in the SAME room
                        audioEl.volume = (remoteRoom && remoteRoom.id === localRoom.id) ? 1.0 : 0;
                    } else {
                        // Outside in Open Area: NEVER hear occupants inside rooms!
                        if (remoteRoom) {
                            audioEl.volume = 0;
                        } else {
                            // Only hear colleagues who are within our visible hearing circle radius (100px)
                            const dist = Math.hypot(localAvatar.x - av.x, localAvatar.y - av.y);
                            const hearingCircleRadius = 100;
                            if (dist > hearingCircleRadius) {
                                audioEl.volume = 0;
                            } else {
                                const factor = 1 - (dist / hearingCircleRadius);
                                audioEl.volume = Math.max(0, Math.min(1.0, factor * 1.2));
                            }
                        }
                    }
                }

                // 2. Spatial Screen Share Card Visibility
                const screenCard = peerVideoCards.get(av.id);
                if (screenCard) {
                    screenCard.style.display = isInSameRoom ? 'flex' : 'none';
                }
            });

            broadcastPosition();
        }

        let lastSentX = -999, lastSentY = -999;
        let lastPosSend = 0;
        function broadcastPosition() {
            const now = Date.now();
            const distMoved = Math.hypot(localAvatar.x - lastSentX, localAvatar.y - lastSentY);
            if ((distMoved >= 1 || (now - lastPosSend > 1500)) && (now - lastPosSend > 45) && ws && ws.readyState === WebSocket.OPEN) {
                lastPosSend = now;
                lastSentX = localAvatar.x;
                lastSentY = localAvatar.y;
                ws.send(JSON.stringify({
                    type: 'position.update',
                    payload: { x: Math.round(localAvatar.x), y: Math.round(localAvatar.y), orientation: 'down' }
                }));
            }
        }


        function draw() {
            if (container && container.clientWidth > 0 && container.clientHeight > 0) {
                if (canvas.width !== container.clientWidth || canvas.height !== container.clientHeight) {
                    width = canvas.width = container.clientWidth;
                    height = canvas.height = container.clientHeight;
                    centerCamera();
                }
            }

            update();

            ctx.setTransform(1, 0, 0, 1, 0, 0);
            ctx.clearRect(0, 0, canvas.width, canvas.height);
            ctx.save();
            ctx.translate(cameraOffset.x, cameraOffset.y);
            ctx.scale(zoomLevel, zoomLevel);

            const hasBlueprint = blueprintLoaded && BLUEPRINT_IMAGE && BLUEPRINT_IMAGE.complete && BLUEPRINT_IMAGE.naturalWidth > 0 && BLUEPRINT_IMAGE.src && !BLUEPRINT_IMAGE.src.endsWith('/');

            // 1. Draw Blueprint / Procedural Floor Background (Warm Sand Diagonal Stripes matching Figma)
            if (hasBlueprint) {
                ctx.fillStyle = '#EDE6D9';
                ctx.fillRect(0, 0, MAP_WIDTH_PX, MAP_HEIGHT_PX);
                ctx.drawImage(BLUEPRINT_IMAGE, 0, 0, MAP_WIDTH_PX, MAP_HEIGHT_PX);
            } else {
                // Pre-rendered Warm Sand with subtle diagonal striped texture
                if (!window._floorSandPattern) {
                    const pCan = document.createElement('canvas');
                    pCan.width = 32;
                    pCan.height = 32;
                    const pCtx = pCan.getContext('2d');
                    pCtx.fillStyle = '#EDE6D9';
                    pCtx.fillRect(0, 0, 32, 32);
                    pCtx.strokeStyle = '#E1D8CA';
                    pCtx.lineWidth = 4.5;
                    pCtx.beginPath();
                    pCtx.moveTo(-8, 8); pCtx.lineTo(8, -8);
                    pCtx.moveTo(0, 32); pCtx.lineTo(32, 0);
                    pCtx.moveTo(24, 40); pCtx.lineTo(40, 24);
                    pCtx.stroke();
                    window._floorSandPattern = ctx.createPattern(pCan, 'repeat');
                }
                ctx.fillStyle = window._floorSandPattern || '#EDE6D9';
                ctx.fillRect(0, 0, MAP_WIDTH_PX, MAP_HEIGHT_PX);

                // Subtle Outer Floor Border
                ctx.strokeStyle = 'rgba(0, 0, 0, 0.12)';
                ctx.lineWidth = 2;
                ctx.strokeRect(0, 0, MAP_WIDTH_PX, MAP_HEIGHT_PX);
            }

            // 1b. Draw Placed Furniture & Decor Objects Layer (Rendered with 3D Elevation Depth Sorting)
            if (!window._officeObjImgCache) window._officeObjImgCache = new Map();

            sortedMapObjects.forEach(obj => {
                const ox = (obj.position ? obj.position.x : (obj.y || 0)) * TILE_SIZE;
                const oy = (obj.position ? obj.position.y : (obj.y || 0)) * TILE_SIZE;
                const objW = (obj.width || (obj.size ? obj.size.width : 1)) * TILE_SIZE;
                const objH = (obj.height || (obj.size ? obj.size.height : 1)) * TILE_SIZE;
                let imgUrl = obj.image_url || (obj.interaction_config && obj.interaction_config.image_url);
                if (obj.type === 'branding' || obj.interaction_type === 'branding' || (obj.interaction_config && obj.interaction_config.use_company_logo)) {
                    if (CONFIG.organization && CONFIG.organization.logo_url) {
                        imgUrl = CONFIG.organization.logo_url;
                    }
                }

                if (hasBlueprint && !imgUrl) return;

                ctx.save();
                ctx.translate(ox + objW / 2, oy + objH / 2);
                const rot = (obj.position && typeof obj.position.rotation === 'number') ? obj.position.rotation : (obj.rotation || 0);
                if (rot) ctx.rotate((rot * Math.PI) / 180);

                if (imgUrl) {
                    let sprImg = window._officeObjImgCache.get(imgUrl);
                    if (!sprImg) {
                        sprImg = new Image();
                        sprImg.src = imgUrl;
                        sprImg.onload = () => { if (typeof draw === 'function') draw(); };
                        window._officeObjImgCache.set(imgUrl, sprImg);
                    }
                    if (sprImg && sprImg.complete && sprImg.naturalWidth > 0) {
                        ctx.drawImage(sprImg, -objW / 2, -objH / 2, objW, objH);
                    } else {
                        ctx.fillStyle = obj.color || 'rgba(59, 130, 246, 0.45)';
                        if (ctx.roundRect) ctx.roundRect(-objW / 2, -objH / 2, objW, objH, 4);
                        else ctx.rect(-objW / 2, -objH / 2, objW, objH);
                        ctx.fill();
                    }
                } else if (!hasBlueprint && (obj.color || obj.is_custom || (obj.interaction_config && obj.interaction_config.is_custom))) {
                    ctx.fillStyle = obj.color ? (obj.color.length === 7 ? obj.color + '99' : obj.color) : 'rgba(59, 130, 246, 0.45)';
                    if (ctx.roundRect) ctx.roundRect(-objW / 2, -objH / 2, objW, objH, 4);
                    else ctx.rect(-objW / 2, -objH / 2, objW, objH);
                    ctx.fill();
                    ctx.strokeStyle = '#FFFFFF';
                    ctx.lineWidth = 1;
                    if (ctx.roundRect) ctx.roundRect(-objW / 2, -objH / 2, objW, objH, 4);
                    else ctx.rect(-objW / 2, -objH / 2, objW, objH);
                    ctx.stroke();
                }
                ctx.restore();
            });

            // ── Architectural Solid Wall Segment Drawing Function ──
            function renderSolidWallBlock(x1, y1, x2, y2, thickness, isLocked) {
                const isHoriz = (y1 === y2);
                const l = Math.min(x1, x2);
                const r = Math.max(x1, x2);
                const t = Math.min(y1, y2);
                const b = Math.max(y1, y2);
                if (r - l <= 0 && b - t <= 0) return;

                ctx.save();
                // 1. Drop shadow onto floor
                ctx.shadowColor = 'rgba(0, 0, 0, 0.25)';
                ctx.shadowBlur = 5;
                ctx.shadowOffsetX = 1;
                ctx.shadowOffsetY = 2;

                let wx, wy, ww, wh;
                if (isHoriz) {
                    wx = l;
                    wy = y1 - thickness / 2;
                    ww = r - l;
                    wh = thickness;
                } else {
                    wx = x1 - thickness / 2;
                    wy = t;
                    ww = thickness;
                    wh = b - t;
                }

                // 2. Solid Drywall / Architectural Wall Core
                ctx.fillStyle = isLocked ? '#5F1414' : '#14231B';
                ctx.fillRect(wx, wy, ww, wh);

                // Turn off shadow for highlights
                ctx.shadowColor = 'transparent';

                // 3. Top Bevel Cap Highlight
                ctx.fillStyle = isLocked ? '#991B1B' : '#2A4034';
                if (isHoriz) {
                    ctx.fillRect(wx + 1, wy + 1, ww - 2, Math.max(2, thickness * 0.38));
                } else {
                    ctx.fillRect(wx + 1, wy + 1, Math.max(2, thickness * 0.38), wh - 2);
                }

                // 4. Subtle Architectural Edge Stroke
                ctx.strokeStyle = isLocked ? 'rgba(239, 68, 68, 0.75)' : 'rgba(237, 230, 217, 0.22)';
                ctx.lineWidth = 1;
                ctx.strokeRect(wx + 0.5, wy + 0.5, ww - 1, wh - 1);
                ctx.restore();
            }

            function renderSolidCornerPost(cx, cy, thickness, isLocked) {
                ctx.save();
                ctx.shadowColor = 'rgba(0, 0, 0, 0.30)';
                ctx.shadowBlur = 4;
                ctx.shadowOffsetX = 1;
                ctx.shadowOffsetY = 2;

                const halfT = thickness / 2;
                ctx.fillStyle = isLocked ? '#5F1414' : '#14231B';
                ctx.fillRect(cx - halfT, cy - halfT, thickness, thickness);

                ctx.shadowColor = 'transparent';
                ctx.fillStyle = isLocked ? '#B91C1C' : '#334F40';
                ctx.fillRect(cx - halfT + 1, cy - halfT + 1, thickness - 2, thickness - 2);

                ctx.strokeStyle = isLocked ? '#EF4444' : 'rgba(237, 230, 217, 0.35)';
                ctx.lineWidth = 1;
                ctx.strokeRect(cx - halfT + 0.5, cy - halfT + 0.5, thickness - 1, thickness - 1);
                ctx.restore();
            }

            // 2. Draw Solid Architectural Room Walls, Door Openings & Animated Realistic Doors
            const WALL_THICKNESS = 10;

            rooms.forEach(r => {
                if (!r.bounds) return;
                const rx = r.bounds.x * TILE_SIZE;
                const ry = r.bounds.y * TILE_SIZE;
                const rw = r.bounds.width * TILE_SIZE;
                const rh = r.bounds.height * TILE_SIZE;
                const isLocked = !!roomDoorStates.get(r.id);
                const door = getRoomDoorPortal(r);
                const doorState = doorAnimationStates.get(r.id) || { openProgress: 0 };
                const openProg = isLocked ? 0 : (doorState.openProgress || 0);

                // A. Room Floor Wash (100% Transparent to preserve natural floorplan artwork)

                // B. Real Solid 3D Architectural Perimeter Walls
                // Top Wall
                if (door && door.wallSide === 'top') {
                    const doorLeft = door.x - (door.width / 2);
                    const doorRight = door.x + (door.width / 2);
                    renderSolidWallBlock(rx, ry, doorLeft, ry, WALL_THICKNESS, isLocked);
                    renderSolidWallBlock(doorRight, ry, rx + rw, ry, WALL_THICKNESS, isLocked);
                } else {
                    renderSolidWallBlock(rx, ry, rx + rw, ry, WALL_THICKNESS, isLocked);
                }

                // Left Wall
                if (door && door.wallSide === 'left') {
                    const doorTop = door.y - (door.width / 2);
                    const doorBottom = door.y + (door.width / 2);
                    renderSolidWallBlock(rx, ry, rx, doorTop, WALL_THICKNESS, isLocked);
                    renderSolidWallBlock(rx, doorBottom, rx, ry + rh, WALL_THICKNESS, isLocked);
                } else {
                    renderSolidWallBlock(rx, ry, rx, ry + rh, WALL_THICKNESS, isLocked);
                }

                // Right Wall
                if (door && door.wallSide === 'right') {
                    const doorTop = door.y - (door.width / 2);
                    const doorBottom = door.y + (door.width / 2);
                    renderSolidWallBlock(rx + rw, ry, rx + rw, doorTop, WALL_THICKNESS, isLocked);
                    renderSolidWallBlock(rx + rw, doorBottom, rx + rw, ry + rh, WALL_THICKNESS, isLocked);
                } else {
                    renderSolidWallBlock(rx + rw, ry, rx + rw, ry + rh, WALL_THICKNESS, isLocked);
                }

                // Bottom Wall
                if (door && door.wallSide === 'bottom') {
                    const doorLeft = door.x - (door.width / 2);
                    const doorRight = door.x + (door.width / 2);
                    renderSolidWallBlock(rx, ry + rh, doorLeft, ry + rh, WALL_THICKNESS, isLocked);
                    renderSolidWallBlock(doorRight, ry + rh, rx + rw, ry + rh, WALL_THICKNESS, isLocked);
                } else {
                    renderSolidWallBlock(rx, ry + rh, rx + rw, ry + rh, WALL_THICKNESS, isLocked);
                }

                // Corner Structural Posts
                renderSolidCornerPost(rx, ry, WALL_THICKNESS, isLocked);
                renderSolidCornerPost(rx + rw, ry, WALL_THICKNESS, isLocked);
                renderSolidCornerPost(rx + rw, ry + rh, WALL_THICKNESS, isLocked);
                renderSolidCornerPost(rx, ry + rh, WALL_THICKNESS, isLocked);

                // C. Real Architectural Doors
                if (door) {
                    ctx.save();
                    ctx.translate(door.x, door.y);

                    let wallAngle = 0;
                    if (door.wallSide === 'top') wallAngle = Math.PI;
                    else if (door.wallSide === 'right') wallAngle = Math.PI / 2;
                    else if (door.wallSide === 'left') wallAngle = -Math.PI / 2;

                    ctx.rotate(wallAngle);

                    const halfW = door.width / 2;
                    const doorThick = 6;

                    // 1. Floor Threshold Plate (Warm Brass transition strip)
                    ctx.fillStyle = isLocked ? 'rgba(239, 68, 68, 0.30)' : 'rgba(211, 165, 83, 0.32)';
                    ctx.fillRect(-halfW - 2, -5, door.width + 4, 10);
                    ctx.strokeStyle = isLocked ? '#EF4444' : '#D3A553';
                    ctx.lineWidth = 1.2;
                    ctx.strokeRect(-halfW - 2, -5, door.width + 4, 10);

                    // 2. Door Frame Jamb Posts (Architectural Frame)
                    const jambW = 6;
                    const jambD = 12;
                    // Left Post
                    ctx.fillStyle = isLocked ? '#7F1D1D' : '#0B1C13';
                    ctx.fillRect(-halfW - jambW, -jambD / 2, jambW, jambD);
                    ctx.fillStyle = isLocked ? '#DC2626' : '#D3A553';
                    ctx.fillRect(-halfW - jambW, -jambD / 2 - 2, jambW, 3);
                    ctx.strokeStyle = 'rgba(237, 230, 217, 0.3)';
                    ctx.strokeRect(-halfW - jambW, -jambD / 2, jambW, jambD);

                    // Right Post
                    ctx.fillStyle = isLocked ? '#7F1D1D' : '#0B1C13';
                    ctx.fillRect(halfW, -jambD / 2, jambW, jambD);
                    ctx.fillStyle = isLocked ? '#DC2626' : '#D3A553';
                    ctx.fillRect(halfW, -jambD / 2 - 2, jambW, 3);
                    ctx.strokeRect(halfW, -jambD / 2, jambW, jambD);

                    // 3. Subtle Architectural Door Swing Arc (Only visible when open / swinging)
                    if (openProg > 0.05 && !isLocked) {
                        ctx.save();
                        ctx.strokeStyle = 'rgba(211, 165, 83, 0.28)';
                        ctx.lineWidth = 0.75;
                        ctx.setLineDash([2, 3]);
                        ctx.beginPath();
                        const currentArcAngle = Math.min(Math.PI * 0.48, openProg * (Math.PI * 0.48));
                        ctx.arc(-halfW, 0, door.width * 0.94, -currentArcAngle, 0, false);
                        ctx.stroke();
                        ctx.restore();
                    }

                    // 4. Animated 3D Door Leaf
                    ctx.save();
                    ctx.translate(-halfW, 0);
                    const swingAngle = openProg * (Math.PI * 0.48);
                    ctx.rotate(-swingAngle);

                    ctx.shadowColor = 'rgba(0,0,0,0.35)';
                    ctx.shadowBlur = 5;
                    ctx.shadowOffsetX = 1;
                    ctx.shadowOffsetY = 2;

                    const leafW = door.width * 0.94;
                    ctx.fillStyle = isLocked ? '#991B1B' : (openProg > 0.4 ? '#245C3A' : '#1A3828');
                    if (ctx.roundRect) ctx.roundRect(0, -doorThick / 2, leafW, doorThick, 2);
                    else ctx.rect(0, -doorThick / 2, leafW, doorThick);
                    ctx.fill();

                    ctx.shadowColor = 'transparent';
                    ctx.strokeStyle = isLocked ? '#FCA5A5' : (openProg > 0.4 ? '#86EFAC' : '#D3A553');
                    ctx.lineWidth = 1.2;
                    if (ctx.roundRect) ctx.roundRect(0, -doorThick / 2, leafW, doorThick, 2);
                    else ctx.rect(0, -doorThick / 2, leafW, doorThick);
                    ctx.stroke();

                    // Inset Glass Tint
                    ctx.fillStyle = isLocked ? 'rgba(255,255,255,0.1)' : 'rgba(167, 243, 208, 0.25)';
                    ctx.fillRect(leafW * 0.2, -doorThick / 2 + 1, leafW * 0.5, doorThick - 2);

                    // Metallic Knob
                    ctx.fillStyle = '#FBBF24';
                    ctx.beginPath();
                    ctx.arc(leafW * 0.82, 0, 2.5, 0, Math.PI * 2);
                    ctx.fill();
                    ctx.strokeStyle = '#78350F';
                    ctx.lineWidth = 0.8;
                    ctx.stroke();

                    ctx.restore();
                    ctx.restore();
                }

                // D. Figma Design Room Card (Smaller pill placed at the corner opposite the door on the other side)
                const displayName = getLocalizedRoomName(r);

                ctx.save();
                ctx.font = '700 11px ' + (CURRENT_LOCALE === 'ar' ? '"IBM Plex Sans Arabic", sans-serif' : '"IBM Plex Sans", sans-serif');
                const nameMetrics = ctx.measureText(displayName);
                
                const cardPadX = 8;
                const cardW = Math.max(60, Math.min(rw - 12, nameMetrics.width + (cardPadX * 2)));
                const cardH = 20;
                const cardRadius = 6;
                const margin = 8;

                // 4 Candidate corners: Top-Left, Top-Right, Bottom-Left, Bottom-Right
                const corners = [
                    { x: rx + margin, y: ry + margin },
                    { x: rx + rw - cardW - margin, y: ry + margin },
                    { x: rx + margin, y: ry + rh - cardH - margin },
                    { x: rx + rw - cardW - margin, y: ry + rh - cardH - margin }
                ];

                let chosenCorner = corners[0]; // default top-left

                if (door && typeof door.x === 'number' && typeof door.y === 'number') {
                    // Pick the corner with the maximum distance from the door (opposite corner on the other side)
                    let maxDistSq = -1;
                    for (const corner of corners) {
                        const cornerCenterX = corner.x + (cardW / 2);
                        const cornerCenterY = corner.y + (cardH / 2);
                        const dx = cornerCenterX - door.x;
                        const dy = cornerCenterY - door.y;
                        const distSq = (dx * dx) + (dy * dy);
                        if (distSq > maxDistSq) {
                            maxDistSq = distSq;
                            chosenCorner = corner;
                        }
                    }
                }

                const cardX = chosenCorner.x;
                const cardY = chosenCorner.y;

                // Subtle Card Drop Shadow
                ctx.shadowColor = 'rgba(0, 0, 0, 0.50)';
                ctx.shadowBlur = 6;
                ctx.shadowOffsetY = 2;

                // Dark high-contrast background matching Figma
                ctx.fillStyle = isLocked ? 'rgba(127, 29, 29, 0.95)' : 'rgba(10, 24, 18, 0.94)';
                if (ctx.roundRect) ctx.roundRect(cardX, cardY, cardW, cardH, cardRadius);
                else ctx.rect(cardX, cardY, cardW, cardH);
                ctx.fill();

                ctx.shadowColor = 'transparent';
                ctx.strokeStyle = isLocked ? 'rgba(239, 68, 68, 0.80)' : 'rgba(211, 165, 83, 0.70)';
                ctx.lineWidth = 1.2;
                if (ctx.roundRect) ctx.roundRect(cardX, cardY, cardW, cardH, cardRadius);
                else ctx.rect(cardX, cardY, cardW, cardH);
                ctx.stroke();

                // Localized Room Name (100% Solid Crisp Pure White #FFFFFF)
                ctx.save();
                ctx.globalAlpha = 1.0;
                ctx.shadowColor = 'transparent';
                ctx.shadowBlur = 0;
                ctx.shadowOffsetX = 0;
                ctx.shadowOffsetY = 0;
                ctx.fillStyle = '#FFFFFF';
                ctx.font = '700 11px ' + (CURRENT_LOCALE === 'ar' ? '"IBM Plex Sans Arabic", sans-serif' : '"IBM Plex Sans", sans-serif');
                ctx.textAlign = 'center';
                ctx.textBaseline = 'middle';
                ctx.fillText((isLocked ? '🔒 ' : '') + displayName, cardX + (cardW / 2), cardY + (cardH / 2) + 0.5);
                ctx.restore();
                ctx.restore();
            });

            // 3. Active Room calculation for status badge
            const activeRoom = getCurrentRoom(localAvatar.x, localAvatar.y);

            // 3b. Active Room Occupants Floating Status Badge (Figma Spec matching Screenshot)
            const roomOccupantsCount = activeRoom ? countRoomOccupants(activeRoom.id) : (remoteAvatars.size + 1);
            const totalRoomCapacity = activeRoom ? (activeRoom.capacity || 8) : 8;
            const currentRoomTitle = activeRoom ? getLocalizedRoomName(activeRoom) : (CURRENT_LOCALE === 'ar' ? 'المساحة المفتوحة' : 'Open Space');
            
            ctx.save();
            const statusBadgeText = `● ${currentRoomTitle} · ${roomOccupantsCount} ${CURRENT_LOCALE === 'ar' ? 'من' : 'of'} ${totalRoomCapacity}`;
            ctx.font = '600 11px ' + (CURRENT_LOCALE === 'ar' ? '"IBM Plex Sans Arabic", sans-serif' : '"IBM Plex Sans", sans-serif');
            const sMetrics = ctx.measureText(statusBadgeText);
            const sW = sMetrics.width + 24;
            const sH = 26;
            const sX = MAP_WIDTH_PX - sW - 20;
            const sY = MAP_HEIGHT_PX - sH - 20;

            ctx.shadowColor = 'rgba(0, 0, 0, 0.35)';
            ctx.shadowBlur = 8;
            ctx.shadowOffsetY = 3;

            ctx.fillStyle = 'rgba(20, 36, 28, 0.90)';
            if (ctx.roundRect) ctx.roundRect(sX, sY, sW, sH, 13);
            else ctx.rect(sX, sY, sW, sH);
            ctx.fill();

            ctx.shadowColor = 'transparent';
            ctx.shadowBlur = 0;
            ctx.shadowOffsetX = 0;
            ctx.shadowOffsetY = 0;
            ctx.strokeStyle = 'rgba(237, 230, 217, 0.18)';
            ctx.lineWidth = 1;
            if (ctx.roundRect) ctx.roundRect(sX, sY, sW, sH, 13);
            else ctx.rect(sX, sY, sW, sH);
            ctx.stroke();

            ctx.save();
            ctx.globalAlpha = 1.0;
            ctx.shadowColor = 'transparent';
            ctx.shadowBlur = 0;
            ctx.shadowOffsetX = 0;
            ctx.shadowOffsetY = 0;
            ctx.fillStyle = '#FFFFFF';
            ctx.textAlign = 'center';
            ctx.textBaseline = 'middle';
            ctx.fillText(statusBadgeText, sX + sW / 2, sY + sH / 2);
            ctx.restore();
            ctx.restore();

            // 4. Draw Remote Avatars (Clean 2.5D Figure without white circle)
            remoteAvatars.forEach(av => drawAvatar(av, false));

            // 5. Draw Local Avatar (Clean 2.5D Figure without white circle)
            drawAvatar(localAvatar, true);

            // 6. Draw In-World Floating Speech Bubbles & Emoji Reactions
            const nowTime = Date.now();
            speechBubbles.forEach((bubble, uid) => {
                if (bubble.expiresAt < nowTime) {
                    speechBubbles.delete(uid);
                    return;
                }
                const av = (uid === localAvatar.id) ? localAvatar : remoteAvatars.get(uid);
                if (!av) return;

                const bx = Number(av.x) || 400;
                const by = (Number(av.y) || 400) - 38;
                const text = bubble.emoji ? bubble.emoji : (bubble.text || '');
                if (!text) return;

                ctx.font = bubble.emoji ? '22px sans-serif' : 'bold 10px Cairo, Inter, sans-serif';
                const textWidth = ctx.measureText(text).width;
                const padX = bubble.emoji ? 8 : 10;
                const padY = bubble.emoji ? 4 : 6;
                const bw = Math.min(240, textWidth + (padX * 2));
                const bh = bubble.emoji ? 32 : 22;

                ctx.fillStyle = 'rgba(15, 23, 42, 0.94)';
                ctx.strokeStyle = '#10B981';
                ctx.lineWidth = 1.5;
                if (ctx.roundRect) ctx.roundRect(bx - (bw / 2), by - bh, bw, bh, 8);
                else ctx.rect(bx - (bw / 2), by - bh, bw, bh);
                ctx.fill();
                ctx.stroke();

                ctx.fillStyle = '#FFFFFF';
                ctx.textAlign = 'center';
                ctx.textBaseline = 'middle';
                ctx.fillText(text, bx, by - (bh / 2));
            });

            ctx.restore();
            requestAnimationFrame(draw);
        }

        // ── Modern Profile & Live Video Node Rendering ──
        function drawAvatar(av, isSelf) {
            const x = Number(av.x) || 400;
            const y = Number(av.y) || 400;
            const cardSize = 36;
            const radius = 18;
            const myRoom = getCurrentRoom(localAvatar.x, localAvatar.y);
            const avRoom = getCurrentRoom(av.x, av.y);

            // 1. Spatial Voice Hearing Circle (Visible when outside in open area)
            if (!avRoom) {
                const hearingRadius = 100;
                const circleGrad = ctx.createRadialGradient(x, y, 8, x, y, hearingRadius);
                circleGrad.addColorStop(0, isSelf ? 'rgba(16, 185, 129, 0.28)' : 'rgba(59, 130, 246, 0.22)');
                circleGrad.addColorStop(0.7, isSelf ? 'rgba(16, 185, 129, 0.08)' : 'rgba(59, 130, 246, 0.06)');
                circleGrad.addColorStop(1, 'rgba(0, 0, 0, 0)');
                ctx.fillStyle = circleGrad;
                ctx.beginPath();
                ctx.arc(x, y, hearingRadius, 0, Math.PI * 2);
                ctx.fill();

                // Clear, crisp circular boundary ring for voice range
                ctx.strokeStyle = isSelf ? '#10B981' : '#3B82F6';
                ctx.lineWidth = 1.8;
                ctx.setLineDash([5, 4]);
                ctx.beginPath();
                ctx.arc(x, y, hearingRadius, 0, Math.PI * 2);
                ctx.stroke();
                ctx.setLineDash([]);
            }

            // 2. Speaking Audio Pulsing Ring (Acoustic Wave)
            const isSpeaking = isSelf ? (micActive && localAvatar.isSpeaking) : (av.micActive && av.isSpeaking);
            if (isSpeaking) {
                const pulse = (Math.sin(Date.now() / 120) + 1) / 2;
                ctx.strokeStyle = '#10B981';
                ctx.lineWidth = 2.5 + pulse * 2.5;
                ctx.beginPath();
                ctx.arc(x, y, radius + 4 + pulse * 4, 0, Math.PI * 2);
                ctx.stroke();
            }

            // 3. Drop Shadow under Profile Card
            ctx.fillStyle = 'rgba(0, 0, 0, 0.45)';
            ctx.beginPath();
            ctx.ellipse(x, y + radius + 3, radius + 1, 5, 0, 0, Math.PI * 2);
            ctx.fill();

            // 4. Live Camera Video OR User Profile Picture / Gradient Monogram
            const lRoom = getCurrentRoom(localAvatar.x, localAvatar.y);
            const rRoom = isSelf ? lRoom : getCurrentRoom(av.x, av.y);
            let canSeeLiveCam = false;
            if (isSelf) {
                canSeeLiveCam = true;
            } else if (lRoom) {
                canSeeLiveCam = (rRoom && rRoom.id === lRoom.id);
            } else {
                if (!rRoom) {
                    const dist = Math.hypot(localAvatar.x - av.x, localAvatar.y - av.y);
                    canSeeLiveCam = (dist <= 280);
                }
            }

            // 4. Live Camera Video OR User Profile Picture / Gradient Monogram (Clean Circular Avatar)
            const isCamOn = isSelf ? (camActive && !!localMediaStream) : (av.camActive && !!av.videoEl && canSeeLiveCam);
            const videoEl = isSelf ? (document.getElementById('local-video-elem') || localAvatar.videoEl) : av.videoEl;

            ctx.save();
            ctx.beginPath();
            ctx.arc(x, y, radius, 0, Math.PI * 2);
            ctx.clip();

            if (isCamOn && videoEl && (videoEl.readyState >= 2 || videoEl.videoWidth > 0)) {
                // Draw Live Video Stream inside Circle
                try {
                    ctx.drawImage(videoEl, x - radius, y - radius, radius * 2, radius * 2);
                } catch(e) {
                    if (av.avatarImg && av.avatarImg.complete && av.avatarImg.naturalWidth > 0) {
                        ctx.drawImage(av.avatarImg, x - radius, y - radius, radius * 2, radius * 2);
                    }
                }
            } else if (av.avatarImg && av.avatarImg.complete && av.avatarImg.naturalWidth > 0) {
                // Draw User Profile Picture inside Circle
                ctx.drawImage(av.avatarImg, x - radius, y - radius, radius * 2, radius * 2);
            } else {
                // Draw Modern Gradient Monogram with User's Initials
                const bgGrad = ctx.createLinearGradient(x - radius, y - radius, x + radius, y + radius);
                if (isSelf) {
                    bgGrad.addColorStop(0, '#3C6B4C');
                    bgGrad.addColorStop(1, '#1E412F');
                } else {
                    bgGrad.addColorStop(0, '#2563EB');
                    bgGrad.addColorStop(1, '#1E40AF');
                }
                ctx.fillStyle = bgGrad;
                ctx.fillRect(x - radius, y - radius, radius * 2, radius * 2);

                // Initials
                const nameParts = (av.name || 'User').trim().split(' ');
                const initials = nameParts.length >= 2 
                    ? (nameParts[0][0] + nameParts[1][0]).toUpperCase()
                    : (nameParts[0].substring(0, 2)).toUpperCase();
                ctx.font = 'bold 11px "IBM Plex Sans Arabic", "IBM Plex Sans", sans-serif';
                ctx.fillStyle = '#FFFFFF';
                ctx.textAlign = 'center';
                ctx.textBaseline = 'middle';
                ctx.fillText(initials, x, y);
            }
            ctx.restore();

            // 5. Circular Avatar Border Ring
            ctx.strokeStyle = isSelf ? '#86EFAC' : (isCamOn ? '#60A5FA' : 'rgba(237, 230, 217, 0.50)');
            ctx.lineWidth = isSelf ? 2.5 : 1.8;
            ctx.beginPath();
            ctx.arc(x, y, radius, 0, Math.PI * 2);
            ctx.stroke();

            // 6. Status Indicators (Top-right Mic & Bottom-right Cam)
            const isMicOn = isSelf ? micActive : av.micActive;
            
            // Mic Badge
            ctx.fillStyle = isMicOn ? '#10B981' : 'rgba(15, 23, 42, 0.90)';
            ctx.beginPath();
            ctx.arc(x + radius - 2, y - radius + 3, 5.5, 0, Math.PI * 2);
            ctx.fill();
            ctx.strokeStyle = '#FFFFFF';
            ctx.lineWidth = 1;
            ctx.stroke();
            ctx.font = '6px sans-serif';
            ctx.fillStyle = '#FFFFFF';
            ctx.textAlign = 'center';
            ctx.textBaseline = 'middle';
            ctx.fillText(isMicOn ? '🎙️' : '🔇', x + radius - 2, y - radius + 3);

            // Cam Badge if live
            if (isCamOn) {
                ctx.fillStyle = '#3B82F6';
                ctx.beginPath();
                ctx.arc(x + radius - 2, y + radius - 3, 5.5, 0, Math.PI * 2);
                ctx.fill();
                ctx.strokeStyle = '#FFFFFF';
                ctx.lineWidth = 1;
                ctx.stroke();
                ctx.font = '6px sans-serif';
                ctx.fillStyle = '#FFFFFF';
                ctx.textAlign = 'center';
                ctx.textBaseline = 'middle';
                ctx.fillText('📷', x + radius - 2, y + radius - 3);
            }

            // 7. Small Compact Person Name Pill Under the Circular Avatar
            const isSitting = isSelf ? localAvatar.isSitting : av.isSitting;
            const displayName = isSelf 
                ? (isSitting ? `🪑 ${av.name}` : `${av.name}`)
                : (isSitting ? `🪑 ${av.name}` : av.name);
            ctx.font = '600 8.5px "IBM Plex Sans Arabic", "IBM Plex Sans", sans-serif';
            const nameW = ctx.measureText(displayName).width + 10;
            const badgeH = 14;
            const badgeY = y + radius + 3;

            ctx.fillStyle = isSitting ? 'rgba(60, 107, 76, 0.95)' : 'rgba(14, 25, 19, 0.92)';
            if (ctx.roundRect) ctx.roundRect(x - nameW / 2, badgeY, nameW, badgeH, 4);
            else ctx.rect(x - nameW / 2, badgeY, nameW, badgeH);
            ctx.fill();

            ctx.strokeStyle = isSelf ? 'rgba(134, 239, 172, 0.6)' : 'rgba(237, 230, 217, 0.25)';
            ctx.lineWidth = 0.8;
            if (ctx.roundRect) ctx.roundRect(x - nameW / 2, badgeY, nameW, badgeH, 4);
            else ctx.rect(x - nameW / 2, badgeY, nameW, badgeH);
            ctx.stroke();

            ctx.fillStyle = isSitting ? '#FFFFFF' : (isSelf ? '#86EFAC' : '#F9F4EE');
            ctx.textAlign = 'center';
            ctx.textBaseline = 'middle';
            ctx.fillText(displayName, x, badgeY + (badgeH / 2));

            // 9. Locate Me Beacon Radar Rings & Pin Marker
            if (isSelf && Date.now() < locateBeaconEndTime) {
                const remaining = locateBeaconEndTime - Date.now();
                const cycle = (Date.now() % 900) / 900;
                const beaconRadius = radius + (cycle * 54);
                const alpha = (1 - cycle) * Math.min(1, remaining / 1000);
                
                ctx.save();
                ctx.strokeStyle = `rgba(52, 211, 153, ${alpha})`;
                ctx.fillStyle = `rgba(52, 211, 153, ${alpha * 0.20})`;
                ctx.lineWidth = 3;
                ctx.beginPath();
                ctx.arc(x, y, beaconRadius, 0, Math.PI * 2);
                ctx.fill();
                ctx.stroke();

                // Draw bouncing pin icon above head
                ctx.fillStyle = '#34D399';
                ctx.font = 'bold 20px "Material Symbols Rounded", sans-serif';
                ctx.textAlign = 'center';
                ctx.textBaseline = 'bottom';
                ctx.fillText('location_on', x, y - radius - 8 - Math.sin(Date.now() / 120) * 5);
                ctx.restore();
            }

            // 8. In-World Floating Speech / Reaction Comic Bubble
            const bubble = speechBubbles.get(av.id);
            if (bubble) {
                const elapsed = Date.now() - bubble.timestamp;
                if (elapsed < 4800) {
                    const progress = Math.min(1, elapsed / 250);
                    const scale = progress < 1 ? Math.sin(progress * Math.PI / 2) * 1.08 : (elapsed > 4000 ? (4800 - elapsed) / 800 : 1.0);
                    const alpha = elapsed > 4000 ? (4800 - elapsed) / 800 : 1.0;
                    const bubbleY = y - radius - 14 - (scale * 8);

                    ctx.save();
                    ctx.globalAlpha = Math.max(0, Math.min(1, alpha));

                    if (bubble.type === 'emoji') {
                        // Big Animated Emoji Pop
                        ctx.font = '28px "Apple Color Emoji", "Segoe UI Emoji", sans-serif';
                        ctx.textAlign = 'center';
                        ctx.textBaseline = 'middle';
                        ctx.fillText(bubble.emoji, x, bubbleY);
                    } else {
                        // Comic Speech Bubble
                        const text = bubble.text || '';
                        ctx.font = 'bold 11px Cairo, Inter, sans-serif';
                        const textMetrics = ctx.measureText(text);
                        const bubbleW = Math.min(240, Math.max(60, textMetrics.width + 20));
                        const bubbleH = 26;
                        const bx = x - bubbleW / 2;
                        const by = bubbleY - bubbleH;

                        // Bubble background
                        ctx.fillStyle = 'rgba(15, 23, 42, 0.95)';
                        ctx.beginPath();
                        if (ctx.roundRect) ctx.roundRect(bx, by, bubbleW, bubbleH, 10);
                        else ctx.rect(bx, by, bubbleW, bubbleH);
                        ctx.fill();

                        ctx.strokeStyle = isSelf ? '#10B981' : '#3B82F6';
                        ctx.lineWidth = 1.5;
                        if (ctx.roundRect) ctx.roundRect(bx, by, bubbleW, bubbleH, 10);
                        else ctx.rect(bx, by, bubbleW, bubbleH);
                        ctx.stroke();

                        // Pointer Tail
                        ctx.fillStyle = 'rgba(15, 23, 42, 0.95)';
                        ctx.beginPath();
                        ctx.moveTo(x - 5, by + bubbleH);
                        ctx.lineTo(x, by + bubbleH + 6);
                        ctx.lineTo(x + 5, by + bubbleH);
                        ctx.fill();

                        ctx.strokeStyle = isSelf ? '#10B981' : '#3B82F6';
                        ctx.beginPath();
                        ctx.moveTo(x - 5, by + bubbleH);
                        ctx.lineTo(x, by + bubbleH + 6);
                        ctx.lineTo(x + 5, by + bubbleH);
                        ctx.stroke();

                        // Bubble Text
                        ctx.fillStyle = '#FFFFFF';
                        ctx.textAlign = 'center';
                        ctx.textBaseline = 'middle';
                        ctx.fillText(text.length > 30 ? text.substring(0, 28) + '...' : text, x, by + bubbleH / 2);
                    }
                    ctx.restore();
                } else {
                    speechBubbles.delete(av.id);
                }
            }

            ctx.restore();
        }

        // ── WebSocket Realtime Connection & Presence Protocol ──
        let ws = null;
        let wsReconnectTimer = null;

        function connectWebSocket() {
            let wsUrl;
            const encodedToken = encodeURIComponent(CONFIG.token || '');
            if (CONFIG.wsUrl && !CONFIG.wsUrl.includes('127.0.0.1') && !CONFIG.wsUrl.includes('localhost')) {
                wsUrl = `${CONFIG.wsUrl}${CONFIG.wsUrl.includes('?') ? '&' : '?'}token=${encodedToken}`;
            } else if (window.location.protocol === 'https:') {
                wsUrl = `wss://${window.location.host}/ws?token=${encodedToken}`;
            } else {
                wsUrl = `ws://${window.location.hostname || '127.0.0.1'}:8080?token=${encodedToken}`;
            }

            try {
                if (ws) {
                    try { ws.close(); } catch(e) {}
                }
                ws = new WebSocket(wsUrl);
                ws.onopen = () => {
                    console.log('⚡ WebSocket Connected successfully via:', wsUrl);
                    wsReconnectAttempts = 0;
                    if (wsReconnectTimer) {
                        clearTimeout(wsReconnectTimer);
                        wsReconnectTimer = null;
                    }
                    // CRITICAL: Send map.join with gender to register presence and receive full occupant roster!
                    ws.send(JSON.stringify({
                        type: 'map.join',
                        payload: {
                            mapId: CONFIG.map.id,
                            initialPosition: { x: localAvatar.x, y: localAvatar.y },
                            gender: userGender
                        }
                    }));

                    // Immediate position beacon to guarantee instant rendering for all peers
                    setTimeout(() => {
                        if (ws && ws.readyState === WebSocket.OPEN) {
                            ws.send(JSON.stringify({
                                type: 'position.update',
                                payload: { x: localAvatar.x, y: localAvatar.y, isMoving: false }
                            }));
                        }
                    }, 100);

                    const activeRoom = getCurrentRoom(localAvatar.x, localAvatar.y);
                    if (activeRoom) {
                        localAvatar.currentRoomId = activeRoom.id;
                        ws.send(JSON.stringify({ type: 'room.enter', payload: { roomId: activeRoom.id } }));
                        syncLiveKitRoom(activeRoom.id);
                    }

                    // Heartbeat ping interval to keep connection alive even when tab is backgrounded
                    if (window._wsPingTimer) clearInterval(window._wsPingTimer);
                    window._wsPingTimer = setInterval(() => {
                        if (ws && ws.readyState === WebSocket.OPEN) {
                            ws.send(JSON.stringify({ type: 'status.update', payload: { status: 'online' } }));
                        }
                    }, 10000);

                    // Continuous Fast Roster Sync Interval (every 4 seconds) to guarantee zero desync
                    if (window._wsRosterSyncTimer) clearInterval(window._wsRosterSyncTimer);
                    window._wsRosterSyncTimer = setInterval(() => {
                        if (ws && ws.readyState === WebSocket.OPEN) {
                            ws.send(JSON.stringify({ type: 'map.sync' }));
                        }
                    }, 4000);
                };

                ws.onclose = (ev) => {
                    console.log('⚠️ WebSocket disconnected. Code:', ev.code, 'Reason:', ev.reason);
                    if (window._wsPingTimer) clearInterval(window._wsPingTimer);
                    if (window._wsRosterSyncTimer) clearInterval(window._wsRosterSyncTimer);
                    if (!isSessionReplaced && !wsReconnectTimer) {
                        wsReconnectAttempts++;
                        const delay = Math.min(800 * Math.pow(1.3, wsReconnectAttempts), 5000);
                        console.log(`⏳ Reconnecting WebSocket (attempt #${wsReconnectAttempts}) in ${delay}ms...`);
                        wsReconnectTimer = setTimeout(() => {
                            wsReconnectTimer = null;
                            connectWebSocket();
                        }, delay);
                    }
                };

                ws.onerror = (err) => {
                    console.error('WebSocket Error:', err);
                };

                // Helper to synchronously reconcile occupant roster
                function applyOccupantsRoster(occupants) {
                    if (!Array.isArray(occupants)) return;
                    const activeIds = new Set(occupants.map(o => o.userId).filter(id => id && id !== localAvatar.id));

                    occupants.forEach(occ => {
                        if (!occ.userId || occ.userId === localAvatar.id) return;
                        const posX = Number(occ.position?.x) || 400;
                        const posY = Number(occ.position?.y) || 400;

                        if (!remoteAvatars.has(occ.userId)) {
                            let avImg = null;
                            if (occ.avatarUrl) {
                                avImg = new Image();
                                avImg.crossOrigin = 'anonymous';
                                avImg.src = occ.avatarUrl;
                            }
                            remoteAvatars.set(occ.userId, {
                                id: occ.userId,
                                name: occ.name || 'Member',
                                avatarUrl: occ.avatarUrl || null,
                                avatarImg: avImg,
                                isGuest: !!occ.isGuest || (occ.name && occ.name.includes('(Guest)')),
                                x: posX,
                                y: posY,
                                targetX: posX,
                                targetY: posY,
                                camActive: !!occ.camActive,
                                micActive: !!occ.micActive,
                                isSpeaking: false,
                                gender: occ.gender || 'male'
                            });
                        } else {
                            const av = remoteAvatars.get(occ.userId);
                            av.name = occ.name || av.name;
                            av.gender = occ.gender || av.gender;
                            if (occ.camActive !== undefined) av.camActive = !!occ.camActive;
                            if (occ.micActive !== undefined) av.micActive = !!occ.micActive;
                            if (occ.position && (!av.targetX || Math.hypot(av.x - posX, av.y - posY) > 300)) {
                                av.targetX = posX;
                                av.targetY = posY;
                            }
                        }
                    });

                    // Remove disconnected users
                    for (const [id] of remoteAvatars.entries()) {
                        if (!activeIds.has(id)) {
                            remoteAvatars.delete(id);
                            const card = peerVideoCards.get(id);
                            if (card) { card.remove(); peerVideoCards.delete(id); }
                            const audio = peerAudioElements.get(id);
                            if (audio) { audio.remove(); peerAudioElements.delete(id); }
                        }
                    }

                    updateOccupantsCounter();
                    updateGalleryGrid();
                }

                ws.onmessage = (e) => {
                    try {
                        const data = JSON.parse(e.data);

                        // 0. Session Replaced Event (Multi-tab / Multi-office lock)
                        if (data.type === 'session.replaced') {
                            isSessionReplaced = true;
                            if (wsReconnectTimer) { clearTimeout(wsReconnectTimer); wsReconnectTimer = null; }
                            if (window._wsPingTimer) { clearInterval(window._wsPingTimer); }
                            if (window._wsRosterSyncTimer) { clearInterval(window._wsRosterSyncTimer); }
                            if (ws) { try { ws.close(); } catch(err) {} }
                            const reasonEl = document.getElementById('session-replaced-reason');
                            if (reasonEl && data.payload?.reason) {
                                reasonEl.textContent = data.payload.reason;
                            }
                            const modal = document.getElementById('session-replaced-modal');
                            if (modal) modal.style.display = 'flex';
                            showToast('⚠️ {{ __("Session replaced by another window") }}');
                            return;
                        }

                        // 1. Welcome / Map Occupants Sync packet
                        if ((data.type === 'welcome' || data.type === 'map.occupants_sync') && data.payload?.occupants) {
                            applyOccupantsRoster(data.payload.occupants);
                            syncLiveKitRoom(localAvatar.currentRoomId);
                        }

                        // 2. User joined the map
                        else if (data.type === 'user.joined' && data.payload) {
                            const u = data.payload;
                            if (u.userId && u.userId !== localAvatar.id) {
                                let avImg = null;
                                if (u.avatarUrl) {
                                    avImg = new Image();
                                    avImg.crossOrigin = 'anonymous';
                                    avImg.src = u.avatarUrl;
                                }
                                const posX = Number(u.position?.x) || 400;
                                const posY = Number(u.position?.y) || 400;
                                remoteAvatars.set(u.userId, {
                                    id: u.userId,
                                    name: u.name || 'Member',
                                    avatarUrl: u.avatarUrl || null,
                                    avatarImg: avImg,
                                    isGuest: !!u.isGuest || (u.name && u.name.includes('(Guest)')),
                                    x: posX,
                                    y: posY,
                                    targetX: posX,
                                    targetY: posY,
                                    camActive: !!u.camActive,
                                    micActive: !!u.micActive,
                                    isSpeaking: false,
                                    gender: u.gender || 'male'
                                });
                                showToast(`👋 ${u.name} {{ __("joined the office") }}`);
                                updateOccupantsCounter();
                            }
                        }

                        // 3. User moved
                        else if (data.type === 'position.updated' && data.payload) {
                            const p = data.payload;
                            if (p.userId && p.userId !== localAvatar.id) {
                                let av = remoteAvatars.get(p.userId);
                                if (!av) {
                                    av = { id: p.userId, name: 'Member', isGuest: false, x: p.position?.x || 500, y: p.position?.y || 500, targetX: p.position?.x || 500, targetY: p.position?.y || 500, gender: 'male' };
                                    remoteAvatars.set(p.userId, av);
                                    updateOccupantsCounter();
                                } else if (p.position) {
                                    av.targetX = p.position.x;
                                    av.targetY = p.position.y;
                                }
                            }
                        }

                        // 3b. Avatar appearance updated in realtime
                        else if (data.type === 'avatar.updated' && data.payload) {
                            const { userId, gender } = data.payload;
                            if (userId && remoteAvatars.has(userId)) {
                                const av = remoteAvatars.get(userId);
                                av.gender = gender;
                                showToast(`🎭 ${av.name} {{ __("changed avatar character to") }} ${gender === 'female' ? '👩 Female' : '👨 Male'}`);
                            }
                        }

                        // 4. User left the map
                        else if ((data.type === 'user.left' || data.type === 'presence.leave') && data.payload?.userId) {
                            const leftId = data.payload.userId;
                            remoteAvatars.delete(leftId);
                            const card = peerVideoCards.get(leftId);
                            if (card) {
                                card.remove();
                                peerVideoCards.delete(leftId);
                            }
                            const audio = peerAudioElements.get(leftId);
                            if (audio) {
                                audio.remove();
                                peerAudioElements.delete(leftId);
                            }
                            checkAutoUnlockEmptyRooms();
                            updateOccupantsCounter();
                            updateGalleryGrid();
                        }

                        // 5. Door Lock Sync
                        else if (data.type === 'room.door_updated' && data.payload) {
                            roomDoorStates.set(data.payload.roomId, !!data.payload.isClosed);
                            updateRoomPresence();
                        }

                        // 6. Knock on Door Request
                        else if (data.type === 'room.knock_request' && data.payload) {
                            const myR = getCurrentRoom(localAvatar.x, localAvatar.y);
                            if (myR && myR.id === data.payload.roomId) {
                                pendingKnock = data.payload;
                                playDoorbellSound();
                                playDoorKnockSound();
                                document.getElementById('knock-requester-name').textContent = `${data.payload.requesterName || 'A colleague'} is knocking on the door...`;
                                document.getElementById('knock-alert-modal').style.display = 'flex';
                            }
                        }

                        // 7. Knock Response Result
                        else if (data.type === 'room.knock_result' && data.payload) {
                            if (data.payload.approved) {
                                playDoorSlideSound();
                                showToast(`🚪 {{ __("Access granted by") }} ${data.payload.responderName}!`);
                                roomDoorStates.set(data.payload.roomId, false);
                                updateRoomPresence();
                            } else {
                                showToast(`🚫 {{ __("Access denied by occupant.") }}`);
                            }
                        }

                        // 8. Chat Message
                        else if (data.type === 'chat.message' && data.payload) {
                            const p = data.payload;
                            // Skip echo for local sender (already added locally)
                            if (p.senderId === localAvatar.id) {
                                return;
                            }

                            // Room isolation: if message is room-scoped, only show if user is in that exact room
                            if (p.scope === 'room' && p.roomId) {
                                const myRoom = getCurrentRoom(localAvatar.x, localAvatar.y);
                                if (!myRoom || myRoom.id !== p.roomId) {
                                    return; // Ignore room chat when outside the room
                                }
                            }

                            appendChatMessage({
                                senderId: p.senderId,
                                senderName: p.senderName,
                                body: p.body,
                                scope: p.scope || 'room',
                                roomId: p.roomId || null,
                                time: new Date(p.timestamp || Date.now()).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' }),
                                file: null
                            }, false);
                        }

                        // 8b. In-World Floating Speech Bubble
                        else if (data.type === 'chat.bubble' && data.payload) {
                            const p = data.payload;
                            if (p.userId === localAvatar.id) {
                                return;
                            }

                            // Room isolation for floating speech bubble
                            if (p.scope === 'room' && p.roomId) {
                                const myRoom = getCurrentRoom(localAvatar.x, localAvatar.y);
                                if (!myRoom || myRoom.id !== p.roomId) {
                                    return;
                                }
                            }

                            spawnSpeechBubble(p.userId, p.userName, p.text);
                        }

                        // 8c. In-World Floating Emoji Reaction
                        else if (data.type === 'user.reaction' && data.payload) {
                            spawnSpeechBubble(data.payload.userId, data.payload.userName, null, data.payload.emoji);
                        }

                        // 8d. Shoulder-Tap / Wave (Say Hi)
                        else if (data.type === 'user.wave' && data.payload) {
                            if (data.payload.targetUserId === localAvatar.id) {
                                playWaveSound();
                                showToast(`👋 ${data.payload.senderName || 'A Colleague'} ${__('says HI to you!')}`);
                                spawnSpeechBubble(data.payload.senderUserId, data.payload.senderName, `👋 ${data.payload.senderName || 'Colleague'} says HI!`, '👋');
                            }
                        }

                        // 8d-2. Colleague Ring / Attention Call
                        else if (data.type === 'user.ring' && data.payload) {
                            if (data.payload.targetUserId === localAvatar.id) {
                                currentIncomingRing = data.payload;
                                playRingSound();
                                spawnSpeechBubble(data.payload.senderUserId, data.payload.senderName, `🔔 Ringing!`, '🔔');
                                const titleEl = document.getElementById('incoming-ring-title');
                                const descEl = document.getElementById('incoming-ring-desc');
                                if (titleEl) titleEl.textContent = `🔔 ${data.payload.senderName || 'A Colleague'} ${__('is ringing you!')}`;
                                if (descEl) descEl.textContent = `${__('Immediate attention requested by')} ${data.payload.senderName}.`;
                                const ringModal = document.getElementById('incoming-ring-modal');
                                if (ringModal) ringModal.style.display = 'flex';
                                showToast(`🔔 ${data.payload.senderName} ${__('is ringing you!')}`);
                            }
                        }

                        // 8e. User Seating State Updated
                        else if (data.type === 'user.sit_updated' && data.payload) {
                            const { userId, isSitting, furnitureId, seatPosition } = data.payload;
                            const av = remoteAvatars.get(userId);
                            if (av) {
                                av.isSitting = !!isSitting;
                                av.sittingFurnitureId = furnitureId || null;
                                if (seatPosition) {
                                    av.x = seatPosition.x;
                                    av.y = seatPosition.y;
                                    av.targetX = seatPosition.x;
                                    av.targetY = seatPosition.y;
                                }
                            }
                        }

                        // 8f. Collaborative Whiteboard Stroke
                        else if (data.type === 'whiteboard.draw' && data.payload) {
                            renderRemoteWbStroke(data.payload.stroke);
                        }

                        // 8g. Collaborative Whiteboard Cleared
                        else if (data.type === 'whiteboard.clear' && data.payload) {
                            if (wbCtx && wbCanvas) {
                                wbCtx.clearRect(0, 0, wbCanvas.width, wbCanvas.height);
                                showToast(`🧹 ${data.payload.clearedBy} {{ __("cleared the whiteboard.") }}`);
                            }
                        }

                        // 10. Remote Peer Media State Updated (Cam / Mic toggled)
                        else if (data.type === 'media.state_updated' && data.payload) {
                            const { userId, camActive, micActive } = data.payload;
                            const av = remoteAvatars.get(userId);
                            if (av) {
                                av.camActive = !!camActive;
                                av.micActive = !!micActive;
                            }
                            if (!camActive) {
                                const card = peerVideoCards.get(userId);
                                if (card) {
                                    card.remove();
                                    peerVideoCards.delete(userId);
                                    if (peerVideoCards.size === 0) {
                                        const chatBtn = document.getElementById('btn-chat-focus-screen');
                                        if (chatBtn) chatBtn.style.display = 'none';
                                    }
                                }
                            }
                            updateGalleryGrid();
                        }

                        // 11. Presentation started/stopped
                        else if (data.type === 'presentation.started' && data.payload) {
                            if (data.payload.presenterId) activeScreenSharers.add(data.payload.presenterId);
                            showToast(`🖥️ ${data.payload.presenterName || 'Colleague'} {{ __("started screen presentation") }}`);
                            const btn = document.getElementById('btn-chat-focus-screen');
                            if (btn) btn.style.display = 'inline-flex';
                        }
                        else if (data.type === 'presentation.stopped' || data.type === 'presentation.stop') {
                            const pId = data.payload?.presenterId;
                            if (pId) {
                                activeScreenSharers.delete(pId);
                                const card = peerVideoCards.get(pId);
                                if (card) {
                                    card.remove();
                                    peerVideoCards.delete(pId);
                                }
                            }
                            if (peerVideoCards.size === 0) {
                                const btn = document.getElementById('btn-chat-focus-screen');
                                if (btn) btn.style.display = 'none';
                            }
                            showToast(`⏹️ {{ __("Screen presentation stopped") }}`);
                        }

                        // 12. Live Organization-Wide Map Occupancy for Branch Switcher
                        else if (data.type === 'organization.map_occupancy' && data.payload?.counts) {
                            updateBranchOccupancyBadges(data.payload.counts);
                        }
                    } catch(err) {
                        console.error('[WS] Error processing message:', err);
                    }
                };
            } catch(err) {
                if (!wsReconnectTimer) {
                    wsReconnectTimer = setTimeout(connectWebSocket, 3000);
                }
            }
        }
        connectWebSocket();

        // ── Auto-resync when returning to tab from background / another window ──
        document.addEventListener('visibilitychange', () => {
            if (!document.hidden) {
                if (!ws || ws.readyState !== WebSocket.OPEN) {
                    connectWebSocket();
                } else {
                    ws.send(JSON.stringify({ type: 'map.sync' }));
                    ws.send(JSON.stringify({
                        type: 'position.update',
                        payload: { x: localAvatar.x, y: localAvatar.y, isMoving: false }
                    }));
                }
            }
        });

        window.addEventListener('focus', () => {
            if (ws && ws.readyState === WebSocket.OPEN) {
                ws.send(JSON.stringify({ type: 'map.sync' }));
            }
        });

        function updateBranchOccupancyBadges(counts) {
            if (!counts) return;
            document.querySelectorAll('.branch-occupants-badge').forEach(badge => {
                const mapId = badge.getAttribute('data-map-id');
                const count = counts[mapId] || 0;
                if (count > 0) {
                    badge.innerHTML = `<span style="display: inline-block; width: 6px; height: 6px; border-radius: 50%; background: #10B981; margin-inline-end: 4px;"></span>${count} {{ __("active") }}`;
                    badge.style.color = '#86EFAC';
                    badge.style.background = 'rgba(16, 185, 129, 0.18)';
                } else {
                    badge.innerHTML = `<span style="display: inline-block; width: 6px; height: 6px; border-radius: 50%; background: #64748B; margin-inline-end: 4px;"></span>0 {{ __("active") }}`;
                    badge.style.color = '#94A3B8';
                    badge.style.background = 'rgba(255, 255, 255, 0.05)';
                }
            });
        }

        function respondToKnock(approved) {
            document.getElementById('knock-alert-modal').style.display = 'none';
            if (pendingKnock && ws && ws.readyState === WebSocket.OPEN) {
                ws.send(JSON.stringify({
                    type: 'room.knock_response',
                    payload: {
                        roomId: pendingKnock.roomId,
                        requesterUserId: pendingKnock.requesterUserId,
                        approved: approved
                    }
                }));
                if (approved) {
                    roomDoorStates.set(pendingKnock.roomId, false);
                    updateRoomPresence();
                }
            }
            pendingKnock = null;
        }

        // ── LiveKit SFU Real-Time Media Engine ──
        let activeScreenSharers = new Set();

        async function syncLiveKitRoom(roomId) {
            const targetRoomId = roomId || (CONFIG.map?.rooms && CONFIG.map.rooms[0] ? CONFIG.map.rooms[0].id : null);
            if (!targetRoomId) return;

            if (currentLiveKitRoomId === targetRoomId && window.VWorkWebRTC && window.VWorkWebRTC.livekitRoom && window.VWorkWebRTC.livekitRoom.state === 'connected') {
                return;
            }
            currentLiveKitRoomId = targetRoomId;

            try {
                const guestInfo = isGuest ? { guestId: localAvatar.id, guestName: localAvatar.name } : null;
                const res = await window.VWorkWebRTC.fetchRoomToken(CONFIG.org.id, targetRoomId, guestInfo);
                if (!res || !res.token) return;

                console.log(`[LiveKit SFU] Connecting to room: ${targetRoomId}...`);
                await window.VWorkWebRTC.joinLiveKitRoom(res.livekit_host, res.token, {
                    onTrackSubscribed: (track, publication, participant) => {
                        handleLiveKitTrackSubscribed(track, publication, participant);
                    },
                    onTrackUnsubscribed: (track, publication, participant) => {
                        handleLiveKitTrackUnsubscribed(track, publication, participant);
                    },
                    onParticipantDisconnected: (participant) => {
                        handleLiveKitParticipantDisconnected(participant);
                    },
                    onActiveSpeakersChanged: (speakers) => {
                        handleLiveKitActiveSpeakers(speakers);
                    }
                });

                // Re-publish active states to SFU if enabled
                if (micActive && window.VWorkWebRTC) await window.VWorkWebRTC.setMicrophoneEnabled(true).catch(()=>{});
                if (camActive && window.VWorkWebRTC) await window.VWorkWebRTC.setCameraEnabled(true).catch(()=>{});
                if (screenActive && window.VWorkWebRTC) await window.VWorkWebRTC.setScreenShareEnabled(true).catch(()=>{});

            } catch (err) {
                console.warn('[LiveKit SFU] Connection error:', err);
            }
        }

        function handleLiveKitTrackSubscribed(track, publication, participant) {
            const userId = participant.identity;
            const trackSource = publication?.source || track?.source || 'unknown';
            console.log(`[LiveKit SFU] Track subscribed: ${track.kind} (${trackSource}) from ${userId}`);
            const av = remoteAvatars.get(userId);

            if (track.kind === 'audio') {
                let audioEl = peerAudioElements.get(userId);
                if (!audioEl) {
                    audioEl = track.attach();
                    audioEl.autoplay = true;
                    document.body.appendChild(audioEl);
                    peerAudioElements.set(userId, audioEl);
                } else {
                    track.attach(audioEl);
                }
                audioEl.play().catch(()=>{});
            } else if (track.kind === 'video') {
                const isScreen = trackSource === 'screen_share' || (track.mediaStreamTrack?.label || '').toLowerCase().includes('screen') || activeScreenSharers.has(userId);

                if (isScreen) {
                    let videoCard = peerVideoCards.get(userId);
                    const presenterName = (av && av.name) ? av.name : (participant.name || 'Colleague');

                    if (!videoCard) {
                        videoCard = document.createElement('div');
                        videoCard.id = `peer-video-${userId}`;
                        videoCard.className = 'video-card size-medium';
                        videoCard.innerHTML = `
                            <div class="video-card-topbar">
                                <div class="video-card-title">
                                    <span class="live-dot"></span>
                                    <span class="user-title">🖥️ ${presenterName} ({{ __('Screen Share') }})</span>
                                </div>
                                <div class="video-card-actions">
                                    <button class="v-btn" id="vbtn-sm-${userId}" onclick="resizeVideoCard('${userId}', 'small')" title="{{ __('Small View') }}">📱</button>
                                    <button class="v-btn active" id="vbtn-med-${userId}" onclick="resizeVideoCard('${userId}', 'medium')" title="{{ __('Medium View') }}">💻</button>
                                    <button class="v-btn" id="vbtn-lg-${userId}" onclick="resizeVideoCard('${userId}', 'large')" title="{{ __('Theater / Large') }}">📺</button>
                                    <button class="v-btn" onclick="toggleFullscreenVideo('${userId}')" title="{{ __('Full Screen') }}">⛶</button>
                                    <button class="v-btn" onclick="togglePipVideo('${userId}')" title="{{ __('Picture in Picture') }}">🗖</button>
                                    <button class="v-btn" onclick="toggleCollapseVideo('${userId}')" title="{{ __('Minimize') }}">➖</button>
                                </div>
                            </div>
                            <div class="video-wrapper"></div>
                        `;
                        const wrapper = videoCard.querySelector('.video-wrapper');
                        const videoEl = track.attach();
                        videoEl.autoplay = true;
                        videoEl.playsInline = true;
                        wrapper.appendChild(videoEl);

                        document.getElementById('video-grid').appendChild(videoCard);
                        peerVideoCards.set(userId, videoCard);

                        const chatBtn = document.getElementById('btn-chat-focus-screen');
                        if (chatBtn) chatBtn.style.display = 'inline-flex';
                    } else {
                        const wrapper = videoCard.querySelector('.video-wrapper');
                        wrapper.innerHTML = '';
                        const videoEl = track.attach();
                        videoEl.autoplay = true;
                        videoEl.playsInline = true;
                        wrapper.appendChild(videoEl);
                    }
                } else {
                    // Camera Track -> Attach to avatar videoEl for Canvas Avatar, Spotlight, and Cameras Gallery
                    let camVideoEl = av?.videoEl;
                    if (!camVideoEl) {
                        camVideoEl = track.attach();
                        camVideoEl.autoplay = true;
                        camVideoEl.playsInline = true;
                        camVideoEl.muted = true;
                    } else {
                        track.attach(camVideoEl);
                    }
                    if (av) {
                        av.videoEl = camVideoEl;
                        av.livekitVideoTrack = track;
                        av.camActive = true;
                    }
                    camVideoEl.play().catch(()=>{});
                }
                if (av && isScreen) {
                    av.livekitScreenTrack = track;
                }
                updateGalleryGrid();
            }
        }

        function handleLiveKitTrackUnsubscribed(track, publication, participant) {
            const userId = participant.identity;
            const trackSource = publication?.source || track?.source || 'unknown';
            const isScreen = trackSource === 'screen_share' || (track.mediaStreamTrack?.label || '').toLowerCase().includes('screen') || (track === remoteAvatars.get(userId)?.livekitScreenTrack);
            console.log(`[LiveKit SFU] Track unsubscribed: ${track.kind} (${trackSource}, isScreen: ${isScreen}) from ${userId}`);

            if (track.kind === 'video') {
                if (isScreen) {
                    const card = peerVideoCards.get(userId);
                    if (card) {
                        card.remove();
                        peerVideoCards.delete(userId);
                    }
                    const av = remoteAvatars.get(userId);
                    if (av) {
                        av.livekitScreenTrack = null;
                    }
                    if (peerVideoCards.size === 0) {
                        const chatBtn = document.getElementById('btn-chat-focus-screen');
                        if (chatBtn) chatBtn.style.display = 'none';
                    }
                } else {
                    const av = remoteAvatars.get(userId);
                    if (av && (av.livekitVideoTrack === track || !av.livekitVideoTrack)) {
                        av.livekitVideoTrack = null;
                        av.camActive = false;
                        av.videoEl = null;
                    }
                }
                updateGalleryGrid();
            } else if (track.kind === 'audio') {
                const audio = peerAudioElements.get(userId);
                if (audio) {
                    audio.remove();
                    peerAudioElements.delete(userId);
                }
            }
        }

        function handleLiveKitParticipantDisconnected(participant) {
            const userId = participant.identity;
            console.log(`[LiveKit SFU] Participant disconnected: ${userId}`);
            const card = peerVideoCards.get(userId);
            if (card) {
                card.remove();
                peerVideoCards.delete(userId);
            }
            const audio = peerAudioElements.get(userId);
            if (audio) {
                audio.remove();
                peerAudioElements.delete(userId);
            }
            const av = remoteAvatars.get(userId);
            if (av) {
                av.videoEl = null;
                av.camActive = false;
                av.isSpeaking = false;
            }
            updateGalleryGrid();
        }

        function handleLiveKitActiveSpeakers(speakers) {
            const speakerIds = new Set(speakers.map(s => s.identity));
            remoteAvatars.forEach((av, id) => {
                av.isSpeaking = speakerIds.has(id);
            });
            localAvatar.isSpeaking = speakerIds.has(localAvatar.id);
        }

        // ── Video & Screen Share Window Sizing Controls ──
        function resizeVideoCard(userId, size) {
            const card = document.getElementById(`peer-video-${userId}`);
            if (!card) return;
            card.classList.remove('size-small', 'size-medium', 'size-large', 'collapsed-mode');
            card.classList.add(`size-${size}`);

            card.querySelectorAll('.v-btn').forEach(b => b.classList.remove('active'));
            if (size === 'small') card.querySelector(`#vbtn-sm-${userId}`)?.classList.add('active');
            if (size === 'medium') card.querySelector(`#vbtn-med-${userId}`)?.classList.add('active');
            if (size === 'large') card.querySelector(`#vbtn-lg-${userId}`)?.classList.add('active');
        }

        function toggleFullscreenVideo(userId) {
            const card = document.getElementById(`peer-video-${userId}`);
            if (!card) return;
            if (!document.fullscreenElement) {
                if (card.requestFullscreen) card.requestFullscreen();
                else if (card.webkitRequestFullscreen) card.webkitRequestFullscreen();
            } else {
                if (document.exitFullscreen) document.exitFullscreen();
            }
        }

        async function togglePipVideo(userId) {
            const card = document.getElementById(`peer-video-${userId}`);
            if (!card) return;
            const video = card.querySelector('video');
            if (!video) return;
            try {
                if (document.pictureInPictureElement) {
                    await document.exitPictureInPicture();
                } else {
                    await video.requestPictureInPicture();
                }
            } catch(e) {
                console.error(e);
            }
        }

        function toggleCollapseVideo(userId) {
            const card = document.getElementById(`peer-video-${userId}`);
            if (!card) return;
            card.classList.toggle('collapsed-mode');
        }

        function focusActiveScreenShare() {
            const firstCard = document.querySelector('.video-grid-overlay .video-card');
            if (firstCard) {
                firstCard.classList.remove('collapsed-mode', 'size-small');
                firstCard.classList.add('size-large');
                firstCard.scrollIntoView({ behavior: 'smooth' });
                showToast('🖥️ {{ __("Screen share window enlarged to theater view!") }}');
            } else {
                showToast('ℹ️ {{ __("No active screen share at the moment.") }}');
            }
        }

        // ── Camera, Microphone & Screen Media Controls (Pure LiveKit SFU) ──
        async function toggleMicrophone() {
            try {
                micActive = !micActive;
                localAvatar.micActive = micActive;

                if (window.VWorkWebRTC) {
                    await window.VWorkWebRTC.setMicrophoneEnabled(micActive).catch(err => {
                        console.warn('[LiveKit SFU] setMicrophoneEnabled error:', err);
                    });
                }

                if (micActive) {
                    showToast('🎙️ {{ __("Microphone active") }}');
                } else {
                    showToast('🔇 {{ __("Microphone muted") }}');
                }

                if (ws && ws.readyState === WebSocket.OPEN) {
                    ws.send(JSON.stringify({ type: 'media.state', payload: { camActive: camActive, micActive: micActive } }));
                }
                updateGalleryGrid();
            } catch(e) {
                console.error('[Audio] error:', e);
                micActive = false;
                localAvatar.micActive = false;
                showToast(`❌ {{ __("خطأ في الميكروفون:") }} ${e.message || e.name}`);
            }

            const btn = document.getElementById('btn-mic');
            btn.classList.toggle('muted', !micActive);
            btn.classList.toggle('active', micActive);
            document.getElementById('mic-icon').textContent = micActive ? 'mic' : 'mic_off';
            document.getElementById('mic-text').textContent = micActive ? '{{ __("المايك يعمل") }}' : '{{ __("كتم المايك") }}';
        }

        // Setup browser native screen-share stop handler
        window.onScreenShareEndedByBrowser = function() {
            console.log('[LiveKit SFU] Screen share stopped via browser floating control');
            if (screenActive) {
                toggleScreenShare();
            }
        };

        async function toggleCamera() {
            try {
                camActive = !camActive;
                localAvatar.camActive = camActive;
                const videoElem = document.getElementById('local-video-elem');
                const card = document.getElementById('local-video-card');

                if (camActive) {
                    if (window.VWorkWebRTC) {
                        try {
                            await window.VWorkWebRTC.setCameraEnabled(true);
                        } catch(sfuErr) {
                            console.warn('[LiveKit SFU] setCameraEnabled notice:', sfuErr);
                        }
                    }

                    // Retrieve local camera stream directly from LiveKit publication
                    let localTrack = null;
                    if (window.VWorkWebRTC?.livekitRoom?.localParticipant?.videoTrackPublications) {
                        const pubs = Array.from(window.VWorkWebRTC.livekitRoom.localParticipant.videoTrackPublications.values());
                        localTrack = pubs.find(p => (p.source === 'camera' || !p.source) && p.track?.mediaStreamTrack)?.track;
                    }

                    if (localTrack?.mediaStreamTrack) {
                        localMediaStream = new MediaStream([localTrack.mediaStreamTrack]);
                    } else if (!localMediaStream || !localMediaStream.active) {
                        try {
                            localMediaStream = await navigator.mediaDevices.getUserMedia({
                                video: { width: { ideal: 640 }, height: { ideal: 360 } },
                                audio: false
                            });
                        } catch(mediaErr) {
                            console.warn('[Camera] local preview getUserMedia notice:', mediaErr);
                        }
                    }

                    if (localMediaStream && videoElem) {
                        videoElem.srcObject = localMediaStream;
                        videoElem.play().catch(()=>{});
                    }
                    if (card) card.style.display = 'flex';
                    if (localMediaStream) {
                        localAvatar.videoEl = videoElem;
                    }
                    showToast('📹 {{ __("تم تشغيل الكاميرا بنجاح") }}');
                } else {
                    if (window.VWorkWebRTC) {
                        await window.VWorkWebRTC.setCameraEnabled(false).catch(()=>{});
                    }
                    if (localMediaStream) {
                        localMediaStream.getVideoTracks().forEach(t => t.stop());
                        localMediaStream = null;
                    }
                    if (videoElem) videoElem.srcObject = null;
                    if (card) card.style.display = 'none';
                    localAvatar.videoEl = null;
                    showToast('📷 {{ __("تم إيقاف الكاميرا") }}');
                }

                if (ws && ws.readyState === WebSocket.OPEN) {
                    ws.send(JSON.stringify({ type: 'media.state', payload: { camActive: camActive, micActive: micActive } }));
                }
                updateGalleryGrid();
            } catch(e) {
                console.error('[Video] error:', e);
                camActive = false;
                localAvatar.camActive = false;
                showToast(`❌ {{ __("خطأ في الكاميرا:") }} ${e.message || e.name}`);
            }

            const btn = document.getElementById('btn-cam');
            btn.classList.toggle('muted', !camActive);
            btn.classList.toggle('active', camActive);
            document.getElementById('cam-icon').textContent = camActive ? 'videocam' : 'videocam_off';
            document.getElementById('cam-text').textContent = camActive ? '{{ __("الكاميرا تعمل") }}' : '{{ __("إيقاف الكاميرا") }}';
        }

        async function toggleScreenShare() {
            try {
                screenActive = !screenActive;

                if (screenActive) {
                    if (window.VWorkWebRTC) {
                        try {
                            await window.VWorkWebRTC.setScreenShareEnabled(true);
                        } catch(sfuErr) {
                            console.warn('[LiveKit SFU] setScreenShareEnabled notice:', sfuErr);
                        }
                    }

                    const btn = document.getElementById('btn-screen');
                    const text = document.getElementById('screen-text');
                    btn.classList.add('active');
                    document.getElementById('screen-icon').textContent = 'stop_screen_share';
                    text.textContent = '{{ __("إيقاف المشاركة") }}';
                    showToast('🖥️ {{ __("تم بدء مشاركة الشاشة") }}');
                    if (ws && ws.readyState === WebSocket.OPEN) {
                        ws.send(JSON.stringify({ type: 'presentation.start', payload: {} }));
                    }
                } else {
                    if (window.VWorkWebRTC) {
                        await window.VWorkWebRTC.setScreenShareEnabled(false).catch(()=>{});
                    }
                    if (screenStream) {
                        screenStream.getTracks().forEach(t => t.stop());
                        screenStream = null;
                    }
                    const btn = document.getElementById('btn-screen');
                    const text = document.getElementById('screen-text');
                    btn.classList.remove('active');
                    document.getElementById('screen-icon').textContent = 'screen_share';
                    text.textContent = '{{ __("مشاركة الشاشة") }}';
                    showToast('⏹️ {{ __("تم إيقاف مشاركة الشاشة") }}');
                    if (ws && ws.readyState === WebSocket.OPEN) {
                        ws.send(JSON.stringify({ type: 'presentation.stop', payload: {} }));
                    }

                    // Restore camera publication if camera was active
                    if (camActive && window.VWorkWebRTC) {
                        try {
                            await window.VWorkWebRTC.setCameraEnabled(true);
                        } catch(camErr) {
                            console.warn('[Camera] Re-verify camera after screen share stopped:', camErr);
                        }
                    }
                }
            } catch(e) {
                console.error('[Screen] error:', e);
                screenActive = false;
                const btn = document.getElementById('btn-screen');
                btn.classList.remove('active');
                document.getElementById('screen-icon').textContent = 'screen_share';
                document.getElementById('screen-text').textContent = '{{ __("مشاركة الشاشة") }}';
                if (e.name !== 'NotAllowedError') {
                    showToast(`❌ {{ __("خطأ في مشاركة الشاشة:") }} ${e.message || e.name}`);
                }
            }
        }

        // ── Meeting Recording Engine (MP4 Container) ──
        let mediaRecorder = null;
        let recordedChunks = [];
        let recordStartTime = 0;
        let isRecording = false;

        function toggleRecording() {
            if (isRecording) {
                stopRecordingSession();
            } else {
                startRecordingSession();
            }
        }

        function startRecordingSession() {
            try {
                const canvasStream = canvas.captureStream(30);
                if (localMediaStream && localMediaStream.getAudioTracks().length > 0) {
                    localMediaStream.getAudioTracks().forEach(t => canvasStream.addTrack(t));
                }

                recordedChunks = [];
                const supportedMime = MediaRecorder.isTypeSupported('video/mp4;codecs=avc1') ? 'video/mp4' : (MediaRecorder.isTypeSupported('video/webm;codecs=vp9,opus') ? 'video/webm;codecs=vp9,opus' : 'video/webm');
                mediaRecorder = new MediaRecorder(canvasStream, { mimeType: supportedMime });
                mediaRecorder.ondataavailable = (e) => { if (e.data.size > 0) recordedChunks.push(e.data); };
                mediaRecorder.onstop = uploadRecordingToServer;
                mediaRecorder.start(1000);

                isRecording = true;
                recordStartTime = Date.now();
                document.getElementById('btn-record').classList.add('active');
                document.getElementById('rec-icon').textContent = 'stop_circle';
                document.getElementById('rec-text').textContent = '{{ __("Stop") }}';
                showToast('⏺️ {{ __("Recording started...") }}');
            } catch(e) {
                console.error(e);
                showToast('❌ {{ __("Recording failed to start") }}');
            }
        }

        function stopRecordingSession() {
            if (mediaRecorder && isRecording) {
                mediaRecorder.stop();
                isRecording = false;
                document.getElementById('btn-record').classList.remove('active');
                document.getElementById('rec-icon').textContent = 'radio_button_checked';
                document.getElementById('rec-text').textContent = '{{ __("Record") }}';
                showToast('⏳ {{ __("Processing recording...") }}');
            }
        }

        async function uploadRecordingToServer() {
            if (recordedChunks.length === 0) return;
            const blob = new Blob(recordedChunks, { type: 'video/mp4' });
            const duration = Math.max(1, Math.round((Date.now() - recordStartTime) / 1000));
            const myRoom = getCurrentRoom(localAvatar.x, localAvatar.y);

            const formData = new FormData();
            formData.append('video', blob, `session_${Date.now()}.mp4`);
            formData.append('title', `Office Session ${new Date().toLocaleTimeString()} — ${myRoom ? myRoom.name : 'Main Floor'}`);
            if (myRoom && myRoom.id) formData.append('room_id', myRoom.id);
            formData.append('duration_seconds', duration);
            formData.append('recorded_by_name', localAvatar.name || 'Member');

            try {
                const res = await fetch(`/organizations/${CONFIG.org.id}/recordings`, {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': CONFIG.csrf, 'Accept': 'application/json' },
                    credentials: 'same-origin',
                    body: formData
                });
                if (res.ok) {
                    showToast('✅ {{ __("Session recording saved to gallery!") }}');
                } else {
                    showToast('❌ {{ __("Failed to save recording") }}');
                }
            } catch(e) {
                console.error(e);
                showToast('❌ {{ __("Upload error") }}');
            }
        }

        // ── Room Files Vault Repository ──
        async function openRoomFilesModal() {
            const myRoom = getCurrentRoom(localAvatar.x, localAvatar.y);
            if (!myRoom) return;
            if (isGuest && guestAllowedRoomId && myRoom.id !== guestAllowedRoomId) {
                showToast('🚫 {{ __("Guests are only permitted to view files in their designated invited room.") }}');
                return;
            }
            document.getElementById('room-files-title').textContent = `📁 ${myRoom.name} — {{ __('Documents & Assets') }}`;
            document.getElementById('room-files-modal').style.display = 'flex';
            await loadRoomFiles(myRoom.id);
        }
        function closeRoomFilesModal() { document.getElementById('room-files-modal').style.display = 'none'; }

        async function loadRoomFiles(roomId) {
            const list = document.getElementById('room-files-list');
            list.innerHTML = `<div style="text-align:center; padding:20px; color:var(--text-muted);">⏳ {{ __("Loading files...") }}</div>`;

            try {
                const res = await fetch(`/organizations/${CONFIG.org.id}/rooms/${roomId}/files`, {
                    headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': CONFIG.csrf },
                    credentials: 'same-origin'
                });
                const data = await res.json();
                const files = data.files || [];

                if (files.length === 0) {
                    list.innerHTML = `<div style="text-align:center; padding:30px; color:var(--text-muted);">📂 {{ __("No documents uploaded to this room yet.") }}</div>`;
                    return;
                }

                let html = '';
                files.forEach(f => {
                    const sizeKb = (f.file_size / 1024).toFixed(1);
                    html += `
                        <div style="background: var(--bg-card); border: 1px solid var(--border-card); border-radius: 10px; padding: 10px 14px; display: flex; justify-content: space-between; align-items: center;">
                            <div>
                                <strong style="font-size: 12px; color: var(--text-primary); display: block;">📄 ${f.name}</strong>
                                <span style="font-size: 10px; color: var(--text-secondary);">${f.uploader_name} • ${sizeKb} KB • ${new Date(f.created_at).toLocaleDateString()}</span>
                            </div>
                            <div style="display: flex; gap: 6px;">
                                <a href="${f.file_url}" download class="action-link-btn" style="padding: 4px 8px; font-size: 11px;">💾 {{ __("Download") }}</a>
                                <button onclick="deleteRoomFile('${roomId}', '${f.id}')" class="action-link-btn btn-danger" style="padding: 4px 8px; font-size: 11px;">🗑️</button>
                            </div>
                        </div>
                    `;
                });
                list.innerHTML = html;
            } catch(e) {
                list.innerHTML = `<div style="color:var(--brand-crimson); text-align:center; padding:20px;">❌ {{ __("Failed to load room files") }}</div>`;
            }
        }

        async function handleRoomFileUpload(input) {
            if (!input.files || !input.files[0]) return;
            const myRoom = getCurrentRoom(localAvatar.x, localAvatar.y);
            if (!myRoom) return;

            const formData = new FormData();
            formData.append('file', input.files[0]);
            showToast('⏳ {{ __("Uploading file to room...") }}');

            try {
                const res = await fetch(`/organizations/${CONFIG.org.id}/rooms/${myRoom.id}/files`, {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': CONFIG.csrf, 'Accept': 'application/json' },
                    credentials: 'same-origin',
                    body: formData
                });
                if (res.ok) {
                    showToast('✅ {{ __("Document saved in room repository!") }}');
                    loadRoomFiles(myRoom.id);
                } else {
                    showToast('❌ {{ __("Upload failed") }}');
                }
            } catch(e) {
                showToast('❌ {{ __("Upload error") }}');
            }
        }

        async function deleteRoomFile(roomId, fileId) {
            if (!confirm('{{ __("Delete this file from the room?") }}')) return;
            try {
                await fetch(`/organizations/${CONFIG.org.id}/rooms/${roomId}/files/${fileId}`, {
                    method: 'DELETE',
                    headers: { 'X-CSRF-TOKEN': CONFIG.csrf, 'Accept': 'application/json' },
                    credentials: 'same-origin'
                });
                showToast('🗑️ {{ __("File deleted") }}');
                loadRoomFiles(roomId);
            } catch(e) {
                showToast('❌ {{ __("Delete failed") }}');
            }
        }

        function toggleReactionMenu(e) {
            if (e) e.stopPropagation();
            const p = document.getElementById('floating-reaction-popover');
            if (p) {
                p.style.display = (p.style.display === 'flex' || p.style.display === 'block') ? 'none' : 'flex';
            }
        }

        function sendEmojiReaction(emoji) {
            spawnSpeechBubble(localAvatar.id, localAvatar.name, null, emoji);
            if (ws && ws.readyState === WebSocket.OPEN) {
                ws.send(JSON.stringify({
                    type: 'user.reaction',
                    payload: { emoji }
                }));
            }
            const p = document.getElementById('floating-reaction-popover');
            if (p) p.style.display = 'none';
            showToast(`${emoji} {{ __("Reaction sent!") }}`);
        }

        function sendWaveToSpotlightUser() {
            const modal = document.getElementById('user-spotlight-modal');
            const targetId = modal ? modal.getAttribute('data-active-user-id') : null;
            if (targetId && ws && ws.readyState === WebSocket.OPEN) {
                ws.send(JSON.stringify({
                    type: 'user.wave',
                    payload: { targetUserId: targetId, senderUserId: localAvatar.id, senderName: localAvatar.name }
                }));
                playWaveSound();
                spawnSpeechBubble(localAvatar.id, localAvatar.name, `👋 Hi!`, '👋');
                showToast('👋 ' + __('Sent Hi wave to colleague!'));
            }
        }

        function ringSpotlightUser() {
            const modal = document.getElementById('user-spotlight-modal');
            const targetId = modal ? modal.getAttribute('data-active-user-id') : null;
            if (targetId && ws && ws.readyState === WebSocket.OPEN) {
                ws.send(JSON.stringify({
                    type: 'user.ring',
                    payload: { targetUserId: targetId, senderUserId: localAvatar.id, senderName: localAvatar.name }
                }));
                playRingSound();
                spawnSpeechBubble(localAvatar.id, localAvatar.name, `🔔 Ringing...`, '🔔');
                showToast('🔔 ' + __('Ringing colleague for immediate attention...'));
            }
        }

        let currentIncomingRing = null;
        function acceptIncomingRing() {
            dismissIncomingRing();
            if (currentIncomingRing) {
                const senderId = currentIncomingRing.senderUserId;
                const senderAv = remoteAvatars.get(senderId);
                if (senderAv) {
                    // Navigate user near the calling colleague
                    const destRoom = getCurrentRoom(senderAv.x, senderAv.y);
                    if (destRoom) {
                        navigateToRoomWithRoute(destRoom, senderAv.x, senderAv.y);
                    } else {
                        localAvatar.targetX = senderAv.x + 35;
                        localAvatar.targetY = senderAv.y;
                    }
                    openUserSpotlight(senderId);
                }
            }
        }

        function dismissIncomingRing() {
            const modal = document.getElementById('incoming-ring-modal');
            if (modal) modal.style.display = 'none';
            currentIncomingRing = null;
        }

        function toggleMoreMenu(e) {
            if (e) e.stopPropagation();
            const p = document.getElementById('floating-more-popover');
            if (p) {
                p.style.display = (p.style.display === 'flex' || p.style.display === 'block') ? 'none' : 'flex';
            }
        }

        function closeMoreMenu() {
            const p = document.getElementById('floating-more-popover');
            if (p) p.style.display = 'none';
        }

        document.addEventListener('click', (e) => {
            const reactPop = document.getElementById('floating-reaction-popover');
            const reactBtn = document.getElementById('btn-react-dock');
            if (reactPop && reactPop.style.display === 'flex' && !reactPop.contains(e.target) && !reactBtn.contains(e.target)) {
                reactPop.style.display = 'none';
            }

            const morePop = document.getElementById('floating-more-popover');
            const moreBtn = document.getElementById('btn-more-dock');
            if (morePop && morePop.style.display === 'flex' && !morePop.contains(e.target) && (!moreBtn || !moreBtn.contains(e.target))) {
                morePop.style.display = 'none';
            }
        });

        // ── Chat File Upload Handler ──
        async function handleChatFileUpload(input) {
            if (!input.files || !input.files[0]) return;
            const formData = new FormData();
            formData.append('file', input.files[0]);
            showToast('⏳ {{ __("Uploading attachment...") }}');

            try {
                const res = await fetch(`/organizations/${CONFIG.org.id}/files`, {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': CONFIG.csrf, 'Accept': 'application/json' },
                    credentials: 'same-origin',
                    body: formData
                });
                if (res.ok) {
                    const data = await res.json();
                    const fileData = data.file || { name: input.files[0].name, url: '#' };
                    const myRoom = getCurrentRoom(localAvatar.x, localAvatar.y);
                    const fileMsgText = `📎 {{ __("Shared a file:") }} ${fileData.name}`;

                    appendChatMessage({
                        id: 'local_' + Date.now(),
                        senderName: localAvatar.name,
                        senderId: localAvatar.id,
                        body: fileMsgText,
                        time: new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' }),
                        scope: chatScope,
                        roomId: myRoom ? myRoom.id : null,
                        file: fileData
                    }, true);

                    if (ws && ws.readyState === WebSocket.OPEN) {
                        ws.send(JSON.stringify({
                            type: 'chat.send',
                            payload: {
                                body: `${fileMsgText} - ${fileData.url}`,
                                scope: chatScope,
                                roomId: myRoom ? myRoom.id : null
                            }
                        }));
                    }
                    showToast('✅ {{ __("File shared in chat!") }}');
                }
            } catch(e) {
                showToast('❌ {{ __("Failed to upload file") }}');
            }
        }

        // ── Rich Realtime Collaborative Whiteboard Engine ──
        let wbCanvas, wbCtx;
        let wbTool = 'pen';
        let wbColor = '#0F172A';
        let wbDrawing = false;
        let wbStartX = 0, wbStartY = 0;
        let wbHistory = [];
        let selectedStickyColor = { bg: '#FEF08A', border: '#FACC15', text: '#713F12' };

        function setWbTool(tool) {
            wbTool = tool;
            document.querySelectorAll('.wb-tool-btn').forEach(btn => btn.classList.remove('active'));
            document.getElementById(`wb-tool-${tool}`)?.classList.add('active');
            if (tool === 'note') {
                toggleWbStickyForm(true);
            }
        }
        function setWbColor(color) {
            wbColor = color;
            document.querySelectorAll('.color-dot').forEach(d => d.classList.toggle('active', d.style.background === color));
        }

        function openWhiteboardModal() {
            document.getElementById('whiteboard-modal').style.display = 'flex';
            wbCanvas = document.getElementById('wb-canvas');
            wbCtx = wbCanvas.getContext('2d');
            wbCanvas.width = wbCanvas.parentElement.clientWidth - 260;
            wbCanvas.height = wbCanvas.parentElement.clientHeight;
            setupWhiteboardEvents();
            loadWbStickyNotes();
        }
        function closeWhiteboardModal() { document.getElementById('whiteboard-modal').style.display = 'none'; }

        function toggleWbStickyForm(forceOpen = null) {
            const form = document.getElementById('wb-sticky-form');
            if (!form) return;
            const isOpen = (forceOpen !== null) ? forceOpen : (form.style.display !== 'flex');
            form.style.display = isOpen ? 'flex' : 'none';
            if (isOpen) {
                const inp = document.getElementById('wb-sticky-text-input');
                if (inp) inp.focus();
            }
        }

        function selectWbStickyColor(bg, border, text, el) {
            selectedStickyColor = { bg, border, text };
            document.querySelectorAll('.sticky-color-pick').forEach(p => {
                p.style.border = '1px solid ' + p.getAttribute('data-border');
            });
            if (el) el.style.border = '2px solid #000000';
            const form = document.getElementById('wb-sticky-form');
            if (form) form.style.background = bg;
        }

        function getWbStorageKey() {
            return `wb_stickies_${CONFIG.org?.id || 'org'}_${CONFIG.currentUser?.id || 'usr'}`;
        }

        function loadWbStickyNotes() {
            const list = document.getElementById('wb-sticky-list');
            if (!list) return;
            try {
                const raw = localStorage.getItem(getWbStorageKey());
                const stickies = raw ? JSON.parse(raw) : [];
                if (stickies.length === 0) {
                    list.innerHTML = `
                        <div style="text-align: center; color: #94A3B8; font-size: 11px; padding: 24px 10px;">
                            📌 {{ __('No sticky notes saved yet. Click + Add to create notes.') }}
                        </div>
                    `;
                    return;
                }
                list.innerHTML = stickies.map((s, idx) => `
                    <div style="background: ${s.bg || '#FEF08A'}; border: 1px solid ${s.border || '#FACC15'}; color: ${s.text || '#713F12'}; border-radius: 10px; padding: 10px 12px; box-shadow: 0 2px 6px rgba(0,0,0,0.06); display: flex; flex-direction: column; gap: 6px; position: relative;">
                        <div style="display: flex; justify-content: space-between; align-items: flex-start; gap: 6px;">
                            <span style="font-size: 10px; font-weight: 800; opacity: 0.75;">📌 ${s.time || ''}</span>
                            <button onclick="deleteWbStickyNote(${idx})" style="background: none; border: none; color: ${s.text || '#713F12'}; opacity: 0.6; cursor: pointer; font-size: 12px; line-height: 1;" title="{{ __('Delete Note') }}">✕</button>
                        </div>
                        <div style="font-size: 12px; font-weight: 700; line-height: 1.4; word-break: break-word; white-space: pre-wrap;">${escapeHtml(s.content)}</div>
                    </div>
                `).join('');
            } catch(e) {
                console.warn('Error loading sticky notes:', e);
            }
        }

        function saveWbStickyNote() {
            const input = document.getElementById('wb-sticky-text-input');
            const text = input ? input.value.trim() : '';
            if (!text) {
                showToast('⚠️ {{ __("Please enter text for the sticky note.") }}');
                return;
            }
            try {
                const key = getWbStorageKey();
                const raw = localStorage.getItem(key);
                const stickies = raw ? JSON.parse(raw) : [];
                stickies.unshift({
                    content: text,
                    bg: selectedStickyColor.bg,
                    border: selectedStickyColor.border,
                    text: selectedStickyColor.text,
                    time: new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' }),
                    timestamp: Date.now()
                });
                localStorage.setItem(key, JSON.stringify(stickies));
                if (input) input.value = '';
                toggleWbStickyForm(false);
                loadWbStickyNotes();
                showToast('📌 {{ __("Sticky note saved to your office whiteboard!") }}');
            } catch(e) {
                console.error(e);
            }
        }

        function deleteWbStickyNote(idx) {
            try {
                const key = getWbStorageKey();
                const raw = localStorage.getItem(key);
                const stickies = raw ? JSON.parse(raw) : [];
                stickies.splice(idx, 1);
                localStorage.setItem(key, JSON.stringify(stickies));
                loadWbStickyNotes();
                showToast('🗑️ {{ __("Sticky note removed.") }}');
            } catch(e) {}
        }

        function setupWhiteboardEvents() {
            wbCanvas.onmousedown = (e) => {
                wbDrawing = true;
                const rect = wbCanvas.getBoundingClientRect();
                wbStartX = e.clientX - rect.left;
                wbStartY = e.clientY - rect.top;

                if (wbTool === 'pen' || wbTool === 'highlighter' || wbTool === 'eraser') {
                    wbCtx.beginPath();
                    wbCtx.moveTo(wbStartX, wbStartY);
                } else if (wbTool === 'text') {
                    const txt = prompt('Enter text:');
                    if (txt) {
                        wbCtx.font = 'bold 16px Cairo, sans-serif';
                        wbCtx.fillStyle = wbColor;
                        wbCtx.fillText(txt, wbStartX, wbStartY);
                        broadcastWbStroke({ tool: 'text', color: wbColor, startX: wbStartX, startY: wbStartY, text: txt });
                        saveWbState();
                    }
                    wbDrawing = false;
                } else if (wbTool === 'note') {
                    toggleWbStickyForm(true);
                    wbDrawing = false;
                }
            };

            wbCanvas.onmousemove = (e) => {
                if (!wbDrawing) return;
                const rect = wbCanvas.getBoundingClientRect();
                const x = e.clientX - rect.left;
                const y = e.clientY - rect.top;

                if (wbTool === 'pen') {
                    wbCtx.strokeStyle = wbColor;
                    wbCtx.lineWidth = 3;
                    wbCtx.lineCap = 'round';
                    wbCtx.lineTo(x, y);
                    wbCtx.stroke();
                } else if (wbTool === 'highlighter') {
                    wbCtx.strokeStyle = wbColor + '55';
                    wbCtx.lineWidth = 14;
                    wbCtx.lineCap = 'square';
                    wbCtx.lineTo(x, y);
                    wbCtx.stroke();
                } else if (wbTool === 'eraser') {
                    wbCtx.strokeStyle = '#FFFFFF';
                    wbCtx.lineWidth = 20;
                    wbCtx.lineTo(x, y);
                    wbCtx.stroke();
                }
            };

            wbCanvas.onmouseup = (e) => {
                if (!wbDrawing) return;
                wbDrawing = false;
                const rect = wbCanvas.getBoundingClientRect();
                const x = e.clientX - rect.left;
                const y = e.clientY - rect.top;

                wbCtx.strokeStyle = wbColor;
                wbCtx.fillStyle = wbColor + '33';
                wbCtx.lineWidth = 3;

                const strokeData = {
                    tool: wbTool,
                    color: wbColor,
                    startX: wbStartX,
                    startY: wbStartY,
                    endX: x,
                    endY: y
                };

                if (wbTool === 'rect') {
                    wbCtx.strokeRect(wbStartX, wbStartY, x - wbStartX, y - wbStartY);
                } else if (wbTool === 'circle') {
                    const rad = Math.hypot(x - wbStartX, y - wbStartY);
                    wbCtx.beginPath();
                    wbCtx.arc(wbStartX, wbStartY, rad, 0, Math.PI * 2);
                    wbCtx.stroke();
                    strokeData.rad = rad;
                } else if (wbTool === 'line') {
                    wbCtx.beginPath();
                    wbCtx.moveTo(wbStartX, wbStartY);
                    wbCtx.lineTo(x, y);
                    wbCtx.stroke();
                } else if (wbTool === 'arrow') {
                    wbCtx.beginPath();
                    wbCtx.moveTo(wbStartX, wbStartY);
                    wbCtx.lineTo(x, y);
                    wbCtx.stroke();
                    const angle = Math.atan2(y - wbStartY, x - wbStartX);
                    wbCtx.lineTo(x - 15 * Math.cos(angle - Math.PI / 6), y - 15 * Math.sin(angle - Math.PI / 6));
                    wbCtx.moveTo(x, y);
                    wbCtx.lineTo(x - 15 * Math.cos(angle + Math.PI / 6), y - 15 * Math.sin(angle + Math.PI / 6));
                    wbCtx.stroke();
                }

                broadcastWbStroke(strokeData);
                saveWbState();
            };
        }

        function broadcastWbStroke(stroke) {
            if (ws && ws.readyState === WebSocket.OPEN) {
                const myRoom = getCurrentRoom(localAvatar.x, localAvatar.y);
                ws.send(JSON.stringify({
                    type: 'whiteboard.draw',
                    payload: {
                        roomId: myRoom ? myRoom.id : 'global',
                        stroke
                    }
                }));
            }
        }

        function renderRemoteWbStroke(s) {
            if (!wbCtx || !wbCanvas) return;
            wbCtx.save();
            wbCtx.strokeStyle = s.color || '#0F172A';
            wbCtx.fillStyle = (s.color || '#0F172A') + '33';
            wbCtx.lineWidth = 3;

            if (s.tool === 'pen' || s.tool === 'line') {
                wbCtx.beginPath();
                wbCtx.moveTo(s.startX, s.startY);
                wbCtx.lineTo(s.endX, s.endY);
                wbCtx.stroke();
            } else if (s.tool === 'rect') {
                wbCtx.strokeRect(s.startX, s.startY, s.endX - s.startX, s.endY - s.startY);
            } else if (s.tool === 'circle') {
                const rad = s.rad || Math.hypot(s.endX - s.startX, s.endY - s.startY);
                wbCtx.beginPath();
                wbCtx.arc(s.startX, s.startY, rad, 0, Math.PI * 2);
                wbCtx.stroke();
            } else if (s.tool === 'text') {
                wbCtx.font = 'bold 16px Cairo, sans-serif';
                wbCtx.fillStyle = s.color || '#0F172A';
                wbCtx.fillText(s.text || '', s.startX, s.startY);
            } else if (s.tool === 'note') {
                wbCtx.fillStyle = '#FEF08A';
                wbCtx.fillRect(s.startX, s.startY, 140, 100);
                wbCtx.strokeRect(s.startX, s.startY, 140, 100);
                wbCtx.fillStyle = '#0F172A';
                wbCtx.font = '12px Cairo, sans-serif';
                wbCtx.fillText('📌 Note', s.startX + 10, s.startY + 20);
            }
            wbCtx.restore();
        }

        function saveWbState() {
            if (wbCanvas && wbHistory.length < 20) {
                wbHistory.push(wbCanvas.toDataURL());
            }
        }

        function undoWhiteboard() {
            if (wbHistory.length > 1) {
                wbHistory.pop();
                const img = new Image();
                img.src = wbHistory[wbHistory.length - 1];
                img.onload = () => {
                    wbCtx.clearRect(0, 0, wbCanvas.width, wbCanvas.height);
                    wbCtx.drawImage(img, 0, 0);
                };
            }
        }

        function clearWhiteboard() {
            wbCtx.clearRect(0, 0, wbCanvas.width, wbCanvas.height);
            saveWbState();
        }

        function exportWhiteboard() {
            const a = document.createElement('a');
            a.download = `whiteboard_${Date.now()}.png`;
            a.href = wbCanvas.toDataURL();
            a.click();
        }

        // ── Recordings Gallery & Direct MP4 Downloads ──
        async function openRecordingsGallery() {
            document.getElementById('recordings-modal').style.display = 'flex';
            const list = document.getElementById('recordings-list');
            list.innerHTML = `<div style="text-align:center; padding:40px 0; color:var(--text-muted);">⏳ {{ __("Loading recordings...") }}</div>`;

            try {
                const res = await fetch(`/organizations/${CONFIG.org.id}/recordings`, {
                    headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': CONFIG.csrf },
                    credentials: 'same-origin'
                });
                const data = await res.json();
                const recs = data.recordings || [];
                if (recs.length === 0) {
                    list.innerHTML = `<div style="text-align:center; padding:40px 0; color:var(--text-muted);">📼 {{ __("No recordings saved yet.") }}</div>`;
                    return;
                }
                let html = '';
                recs.forEach(r => {
                    const downloadUrl = `/organizations/${CONFIG.org.id}/recordings/${r.id}/download`;
                    html += `
                        <div style="background: var(--bg-card); border: 1px solid var(--border-color); border-radius: 12px; padding: 14px; display: flex; justify-content: space-between; align-items: center;">
                            <div>
                                <strong style="font-size: 13px; color: var(--text-primary); display: block;">${r.title}</strong>
                                <span style="font-size: 11px; color: var(--text-secondary);">${new Date(r.created_at).toLocaleString()} • ${Math.round(r.duration_seconds || 0)}s • ${r.recorded_by_name || 'Member'}</span>
                            </div>
                            <div style="display: flex; gap: 8px;">
                                <a href="${downloadUrl}" download="meeting_recording.mp4" class="action-link-btn">💾 {{ __("Download MP4") }}</a>
                                <button onclick="deleteRecording('${r.id}')" class="action-link-btn btn-danger">🗑️</button>
                            </div>
                        </div>
                    `;
                });
                list.innerHTML = html;
            } catch(e) {
                list.innerHTML = `<div style="color:var(--brand-crimson); text-align:center; padding:20px;">❌ {{ __("Failed to load recordings") }}</div>`;
            }
        }
        function closeRecordingsGallery() { document.getElementById('recordings-modal').style.display = 'none'; }

        async function deleteRecording(id) {
            if (!confirm('{{ __("Delete this recording?") }}')) return;
            try {
                await fetch(`/organizations/${CONFIG.org.id}/recordings/${id}`, {
                    method: 'DELETE',
                    headers: { 'X-CSRF-TOKEN': CONFIG.csrf, 'Accept': 'application/json' },
                    credentials: 'same-origin'
                });
                showToast('🗑️ {{ __("Recording deleted") }}');
                openRecordingsGallery();
            } catch(e) {
                showToast('❌ {{ __("Delete failed") }}');
            }
        }

        // ── Guest Invite Link ──
        function openGuestInviteModal() { document.getElementById('guest-modal').style.display = 'flex'; }
        function closeGuestModal() { document.getElementById('guest-modal').style.display = 'none'; }

        let currentGuestJoinUrl = '';
        async function generateGuestLink() {
            const roomId = document.getElementById('invite-room-select').value;
            const guestName = document.getElementById('invite-guest-name').value || 'Guest';

            try {
                const res = await fetch(`/api/v1/organizations/${CONFIG.org.id}/rooms/${roomId}/guest-invitations`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CONFIG.csrf, 'Accept': 'application/json' },
                    credentials: 'same-origin',
                    body: JSON.stringify({ guest_name: guestName, expires_in_hours: 24 })
                });
                const data = await res.json();
                if (res.ok && data.join_url) {
                    currentGuestJoinUrl = data.join_url;
                    document.getElementById('guest-link-result').style.display = 'flex';
                    document.getElementById('guest-link-input').value = data.join_url;
                    showToast('⚡ {{ __("Guest link ready!") }}');
                }
            } catch(e) {
                showToast('❌ {{ __("Failed to generate link") }}');
            }
        }

        function copyGuestLink() {
            const inp = document.getElementById('guest-link-input');
            inp.select();
            navigator.clipboard.writeText(inp.value);
            showToast('📋 {{ __("Link copied to clipboard!") }}');
        }

        function openGuestInNewWindow() {
            if (!currentGuestJoinUrl) return;
            window.open(currentGuestJoinUrl, '_blank');
            closeGuestModal();
            showToast('🚀 {{ __("Opening guest window...") }}');
        }

        // ── Live Online Occupants Modal & Roster ──
        function updateOccupantsCounter() {
            const total = 1 + remoteAvatars.size;
            const counterEl = document.getElementById('occupants-counter');
            if (counterEl) {
                counterEl.textContent = `${total} {{ __('Online') }}`;
            }
        }

        function openOccupantsModal() {
            const modal = document.getElementById('occupants-modal');
            const list = document.getElementById('occupants-list');
            modal.style.display = 'flex';

            const localRoom = getCurrentRoom(localAvatar.x, localAvatar.y);
            let html = `
                <div style="background: var(--bg-card); border: 1px solid var(--border-color); border-radius: 12px; padding: 12px 14px; display: flex; justify-content: space-between; align-items: center;">
                    <div style="display: flex; align-items: center; gap: 10px;">
                        <span style="font-size: 20px;">${userGender === 'female' ? '👩' : '👨'}</span>
                        <div>
                            <strong style="font-size: 13px; color: var(--text-primary); display: block;">
                                ${localAvatar.name} <span style="font-size: 10px; color: #34D399; font-weight: 800;">({{ __('You / Host') }})</span>
                            </strong>
                            <span style="font-size: 11px; color: var(--text-secondary);">🏢 ${localRoom ? localRoom.name : '{{ __("Open Floor") }}'}</span>
                        </div>
                    </div>
                    <span style="font-size: 10px; background: rgba(16, 185, 129, 0.15); color: #10B981; padding: 3px 8px; border-radius: 6px; font-weight: 800;">🟢 {{ __('Active') }}</span>
                </div>
            `;

            if (remoteAvatars.size === 0) {
                html += `
                    <div style="text-align: center; padding: 24px; color: var(--text-muted); font-size: 12px;">
                        👥 {{ __("No other colleagues or guests in this office yet.") }}
                        <div style="margin-top: 10px;">
                            <button onclick="closeOccupantsModal(); openGuestInviteModal();" class="action-link-btn" style="display: inline-flex; background: var(--brand-primary); color: white; padding: 6px 12px; font-size: 11px;">
                                ⚡ {{ __("Invite a Guest Now") }}
                            </button>
                        </div>
                    </div>
                `;
            } else {
                remoteAvatars.forEach(av => {
                    const r = getCurrentRoom(av.x, av.y);
                    const avGender = av.gender || 'male';
                    html += `
                        <div style="background: var(--bg-card); border: 1px solid var(--border-card); border-radius: 12px; padding: 12px 14px; display: flex; justify-content: space-between; align-items: center;">
                            <div style="display: flex; align-items: center; gap: 10px;">
                                <span style="font-size: 20px;">${avGender === 'female' ? '👩' : '👨'}</span>
                                <div>
                                    <strong style="font-size: 13px; color: var(--text-primary); display: block;">
                                        ${av.name} ${av.isGuest ? '<span style="font-size: 10px; color: #F59E0B; font-weight: 800;">(Guest)</span>' : ''}
                                    </strong>
                                    <span style="font-size: 11px; color: var(--text-secondary);">🏢 ${r ? r.name : '{{ __("Open Floor") }}'}</span>
                                </div>
                            </div>
                            <div style="display: flex; gap: 6px; align-items: center;">
                                <span style="font-size: 10px; background: rgba(16, 185, 129, 0.15); color: #10B981; padding: 3px 8px; border-radius: 6px; font-weight: 800;">🟢 Online</span>
                                <button onclick="teleportToUser('${av.id}')" class="action-link-btn" style="padding: 4px 8px; font-size: 10px;" title="{{ __('Walk / Teleport to colleague') }}">🎯 {{ __('Go To') }}</button>
                            </div>
                        </div>
                    `;
                });
            }

            list.innerHTML = html;
        }

        function closeOccupantsModal() {
            document.getElementById('occupants-modal').style.display = 'none';
        }

        function teleportToUser(userId) {
            const av = remoteAvatars.get(userId);
            if (!av) return;
            const destRoom = getCurrentRoom(av.x, av.y);
            const myRoom = getCurrentRoom(localAvatar.x, localAvatar.y);
            const route = buildNavigationRoute(
                { x: localAvatar.x, y: localAvatar.y },
                { x: av.x + 30, y: av.y },
                myRoom,
                destRoom
            );
            if (route && route.length > 0) {
                avatarWaypoints = [...route];
                const firstWp = avatarWaypoints.shift();
                localAvatar.targetX = firstWp.x;
                localAvatar.targetY = firstWp.y;
                if (firstWp.action) firstWp.action();
            } else {
                localAvatar.targetX = av.x + 30;
                localAvatar.targetY = av.y;
            }
            closeOccupantsModal();
            showToast(`🎯 {{ __('Moving to') }} ${av.name}...`);
        }

        // ── User Spotlight & Activity Drawer ──
        let spotlightTimerInterval = null;

        async function openUserSpotlight(userId) {
            const isSelf = (userId === localAvatar.id);
            const modal = document.getElementById('user-spotlight-modal');
            modal.setAttribute('data-active-user-id', userId);

            const waveBtn = document.getElementById('spotlight-wave-btn');
            if (waveBtn) {
                waveBtn.style.display = isSelf ? 'none' : 'inline-flex';
            }

            const ringBtn = document.getElementById('spotlight-ring-btn');
            if (ringBtn) {
                ringBtn.style.display = isSelf ? 'none' : 'inline-flex';
            }

            const videoPlayer = document.getElementById('spotlight-video-player');
            const noVideoBox = document.getElementById('spotlight-no-video');
            const nameEl = document.getElementById('spotlight-user-name');
            const subEl = document.getElementById('spotlight-user-subtitle');
            const avBox = document.getElementById('spotlight-avatar-box');
            const bigAv = document.getElementById('spotlight-big-avatar');
            const timerBox = document.getElementById('spotlight-active-timer-box');
            const timerTask = document.getElementById('spotlight-timer-task');
            const timerClock = document.getElementById('spotlight-timer-clock');
            const tasksList = document.getElementById('spotlight-tasks-list');
            const tasksCount = document.getElementById('spotlight-tasks-count');

            if (spotlightTimerInterval) clearInterval(spotlightTimerInterval);

            // Set Video Stream (enforcing spatial/room privacy)
            let hasVideo = false;
            videoPlayer.muted = true;
            videoPlayer.playsInline = true;
            videoPlayer.autoplay = true;

            if (isSelf && camActive && localMediaStream) {
                videoPlayer.srcObject = localMediaStream;
                videoPlayer.style.display = 'block';
                noVideoBox.style.display = 'none';
                videoPlayer.play().catch(()=>{});
                hasVideo = true;
            } else if (!isSelf) {
                const av = remoteAvatars.get(userId);
                const lRoom = getCurrentRoom(localAvatar.x, localAvatar.y);
                const rRoom = av ? getCurrentRoom(av.x, av.y) : null;
                const canSee = lRoom ? (rRoom && rRoom.id === lRoom.id) : (!rRoom && av && Math.hypot(localAvatar.x - av.x, localAvatar.y - av.y) <= 300);

                if (canSee && av) {
                    if (av.livekitVideoTrack) {
                        try {
                            av.livekitVideoTrack.attach(videoPlayer);
                            videoPlayer.style.display = 'block';
                            noVideoBox.style.display = 'none';
                            videoPlayer.play().catch(()=>{});
                            hasVideo = true;
                        } catch(e) {
                            console.warn('[Spotlight] attach error:', e);
                        }
                    }
                    if (!hasVideo) {
                        const vidEl = av.videoEl || peerVideoCards.get(userId)?.querySelector('video');
                        if (vidEl && (vidEl.srcObject || vidEl.src)) {
                            videoPlayer.srcObject = vidEl.srcObject;
                            videoPlayer.style.display = 'block';
                            noVideoBox.style.display = 'none';
                            videoPlayer.play().catch(()=>{});
                            hasVideo = true;
                        }
                    }
                }
            }

            if (!hasVideo) {
                videoPlayer.srcObject = null;
                videoPlayer.style.display = 'none';
                noVideoBox.style.display = 'flex';
            }

            // Quick default placeholders
            const avObj = isSelf ? localAvatar : remoteAvatars.get(userId);
            const displayName = avObj ? avObj.name : 'Team Member';
            nameEl.textContent = displayName;
            subEl.textContent = isSelf ? '{{ __("You (Current Session)") }}' : (avObj?.jobTitle || '{{ __("Colleague") }}');
            
            const initials = displayName.split(' ').map(n=>n[0]).join('').substring(0,2).toUpperCase();
            avBox.textContent = initials;
            bigAv.textContent = initials;

            if (avObj && avObj.avatarUrl) {
                avBox.innerHTML = `<img src="${avObj.avatarUrl}" style="width:100%;height:100%;object-fit:cover;">`;
                bigAv.innerHTML = `<img src="${avObj.avatarUrl}" style="width:100%;height:100%;object-fit:cover;">`;
            }

            tasksList.innerHTML = `<div style="text-align:center; padding: 12px; color: var(--text-muted); font-size:12px;">⏳ {{ __("Loading profile activity...") }}</div>`;
            modal.style.display = 'flex';

            // Fetch live activity & task list from server API
            try {
                const res = await fetch(`/api/members/${userId}/activity?organization_id=${CONFIG.org.id}`, {
                    headers: { 'Accept': 'application/json' }
                });
                if (res.ok) {
                    const data = await res.json();
                    if (data.user) {
                        nameEl.textContent = data.user.name;
                        subEl.textContent = `${data.user.role_name} • ${data.user.job_title || ''} ${data.user.department ? '('+data.user.department+')' : ''}`;
                        if (data.user.avatar_url) {
                            avBox.innerHTML = `<img src="${data.user.avatar_url}" style="width:100%;height:100%;object-fit:cover;">`;
                            bigAv.innerHTML = `<img src="${data.user.avatar_url}" style="width:100%;height:100%;object-fit:cover;">`;
                        }
                    }

                    if (isGuest || data.is_guest_viewer) {
                        // Privacy protection: completely hide timer and tasks for guest viewers
                        if (timerBox) timerBox.style.display = 'none';
                        if (tasksCount) tasksCount.textContent = '🔒 {{ __("Restricted") }}';
                        tasksList.innerHTML = `<div style="text-align:center; padding: 14px; color: var(--text-muted); font-size:12px; font-weight: 600;">🔒 {{ __("المهام خاصة بأعضاء الفريق") }}</div>`;
                    } else {
                        // Active Timer for members
                        if (data.active_timer) {
                            timerBox.style.display = 'flex';
                            timerTask.textContent = `${data.active_timer.task_title} (${data.active_timer.project_name})`;
                            let elapsed = data.active_timer.duration_seconds || 0;
                            function updateTimerClock() {
                                elapsed++;
                                const hrs = String(Math.floor(elapsed / 3600)).padStart(2, '0');
                                const mins = String(Math.floor((elapsed % 3600) / 60)).padStart(2, '0');
                                const secs = String(elapsed % 60).padStart(2, '0');
                                timerClock.textContent = `${hrs}:${mins}:${secs}`;
                            }
                            updateTimerClock();
                            spotlightTimerInterval = setInterval(updateTimerClock, 1000);
                        } else {
                            timerBox.style.display = 'none';
                        }

                        // Tasks List for members
                        tasksCount.textContent = `${data.tasks.length} ${data.tasks.length === 1 ? 'Task' : 'Tasks'}`;
                        if (data.tasks.length > 0) {
                            tasksList.innerHTML = data.tasks.map(t => `
                                <div style="background: var(--bg-card); border: 1px solid var(--border-card); border-radius: 10px; padding: 10px 14px; display: flex; align-items: center; justify-content: space-between; gap: 8px;">
                                    <div style="display: flex; align-items: center; gap: 8px;">
                                        <span style="font-size: 14px;">${t.status === 'done' ? '✅' : (t.status === 'in_progress' ? '⚡' : '📌')}</span>
                                        <div>
                                            <div style="font-size: 12px; font-weight: 800; color: var(--text-primary); text-decoration: ${t.status === 'done' ? 'line-through' : 'none'};">${t.title}</div>
                                            <div style="font-size: 10px; color: var(--text-secondary);">${t.project_name} ${t.due_date ? '• 📅 ' + t.due_date : ''}</div>
                                        </div>
                                    </div>
                                    <span class="guest-badge" style="text-transform: uppercase; font-size: 9px;">${t.status.replace('_', ' ')}</span>
                                </div>
                            `).join('');
                        } else {
                            tasksList.innerHTML = `<div style="text-align:center; padding: 12px; color: var(--text-muted); font-size:12px;">☕ {{ __("No pending tasks assigned.") }}</div>`;
                        }
                    }
                }
            } catch(err) {
                console.error(err);
            }
        }

        function closeUserSpotlight() {
            if (spotlightTimerInterval) clearInterval(spotlightTimerInterval);
            const modal = document.getElementById('user-spotlight-modal');
            const activeUserId = modal?.getAttribute('data-active-user-id');
            const videoPlayer = document.getElementById('spotlight-video-player');
            if (videoPlayer) {
                if (activeUserId && remoteAvatars.has(activeUserId)) {
                    const av = remoteAvatars.get(activeUserId);
                    if (av?.livekitVideoTrack) {
                        try { av.livekitVideoTrack.detach(videoPlayer); } catch(e) {}
                    }
                }
                videoPlayer.srcObject = null;
                videoPlayer.style.display = 'none';
            }
            if (modal) modal.style.display = 'none';
        }

        // ── IN-OFFICE MY TASKS DRAWER & QUICK TIME TRACKER ──
        let myOfficeTasksList = [];
        let activeOfficeTimer = null;
        let activeOfficeTimerInterval = null;

        function openMyTaskDrawer() {
            if (isGuest) {
                showToast('🔒 {{ __("Tasks are only available for workspace team members.") }}');
                return;
            }
            const drawer = document.getElementById('my-task-drawer');
            if (drawer) {
                drawer.style.display = 'flex';
                loadMyOfficeTasks();
            }
        }

        function closeMyTaskDrawer() {
            const drawer = document.getElementById('my-task-drawer');
            if (drawer) drawer.style.display = 'none';
        }

        async function loadMyOfficeTasks() {
            if (isGuest) return;
            const listEl = document.getElementById('office-tasks-list');
            const heroEl = document.getElementById('office-active-timer-hero');
            const dockPill = document.getElementById('floating-task-timer-pill');

            try {
                const res = await fetch('/api/office/my-tasks', {
                    headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': CONFIG.csrf }
                });
                if (!res.ok) return;
                const data = await res.json();
                myOfficeTasksList = data.tasks || [];
                activeOfficeTimer = data.active_timer || null;

                // 1. Update Active Timer Hero Card and Dock Timer Pill
                if (activeOfficeTimer) {
                    if (heroEl) {
                        heroEl.style.display = 'block';
                        const tTitle = document.getElementById('office-timer-title');
                        const tProj = document.getElementById('office-timer-project');
                        if (tTitle) tTitle.textContent = activeOfficeTimer.task_title || 'Work Session';
                        if (tProj) tProj.textContent = `📁 ${activeOfficeTimer.project_name || 'General'}`;
                    }
                    if (dockPill) {
                        dockPill.style.display = 'flex';
                        const nameEl = document.getElementById('dock-timer-task-name');
                        if (nameEl) nameEl.textContent = activeOfficeTimer.task_title || 'Task';
                    }

                    let elapsed = activeOfficeTimer.elapsed_seconds || 0;
                    if (activeOfficeTimerInterval) clearInterval(activeOfficeTimerInterval);

                    function updateClock() {
                        elapsed++;
                        const h = String(Math.floor(elapsed / 3600)).padStart(2, '0');
                        const m = String(Math.floor((elapsed % 3600) / 60)).padStart(2, '0');
                        const s = String(elapsed % 60).padStart(2, '0');
                        const timeStr = `${h}:${m}:${s}`;
                        const c1 = document.getElementById('office-timer-clock');
                        const c2 = document.getElementById('dock-timer-clock');
                        if (c1) c1.textContent = timeStr;
                        if (c2) c2.textContent = timeStr;
                    }
                    updateClock();
                    activeOfficeTimerInterval = setInterval(updateClock, 1000);
                } else {
                    if (heroEl) heroEl.style.display = 'none';
                    if (dockPill) dockPill.style.display = 'none';
                    if (activeOfficeTimerInterval) {
                        clearInterval(activeOfficeTimerInterval);
                        activeOfficeTimerInterval = null;
                    }
                }

                // 2. Render Tasks List
                renderOfficeTasksList(myOfficeTasksList);

            } catch (e) {
                console.error('Error loading office tasks:', e);
                if (listEl) {
                    listEl.innerHTML = `<div style="text-align: center; color: var(--text-muted); font-size: 12px; padding: 20px;">❌ {{ __('Failed to load tasks.') }}</div>`;
                }
            }
        }

        function renderOfficeTasksList(tasks) {
            const listEl = document.getElementById('office-tasks-list');
            if (!listEl) return;

            if (!tasks || !tasks.length) {
                listEl.innerHTML = `
                    <div style="text-align: center; padding: 30px 14px; color: var(--text-muted); font-size: 12px;">
                        <div style="font-size: 28px; margin-bottom: 6px;">☕</div>
                        {{ __('No pending tasks assigned to you.') }}
                    </div>
                `;
                return;
            }

            const priorityColors = {
                'urgent': 'background: rgba(217, 107, 95, 0.15); color: #D96B5F; border: 1px solid rgba(217, 107, 95, 0.3);',
                'high': 'background: rgba(214, 162, 58, 0.15); color: #D6A23A; border: 1px solid rgba(214, 162, 58, 0.3);',
                'normal': 'background: rgba(79, 155, 95, 0.15); color: #4F9B5F; border: 1px solid rgba(79, 155, 95, 0.3);',
                'low': 'background: rgba(148, 163, 184, 0.15); color: #64748B; border: 1px solid rgba(148, 163, 184, 0.3);'
            };

            listEl.innerHTML = tasks.map(t => {
                const isRunning = activeOfficeTimer && activeOfficeTimer.task_id === t.id;
                const pStyle = priorityColors[t.priority] || priorityColors['normal'];
                const isDone = t.status === 'done';

                return `
                    <div class="task-card-item ${isRunning ? 'running' : ''}" id="office-task-${t.id}" style="${isDone ? 'opacity: 0.75;' : ''}">
                        <div style="display: flex; justify-content: space-between; align-items: flex-start; gap: 8px;">
                            <div style="min-width: 0;">
                                <div style="font-size: 10px; font-weight: 800; color: var(--brand-primary); margin-bottom: 2px;">
                                    📁 ${escapeHtml(t.project_name || 'General')}
                                </div>
                                <div style="font-size: 13px; font-weight: 800; color: var(--text-primary); line-height: 1.3; ${isDone ? 'text-decoration: line-through;' : ''}">
                                    ${escapeHtml(t.title)}
                                </div>
                            </div>
                            <span class="guest-badge" style="${pStyle} font-size: 9px; text-transform: uppercase;">
                                ${t.priority || 'normal'}
                            </span>
                        </div>

                        <!-- Status Selector & Action Row -->
                        <div style="display: flex; justify-content: space-between; align-items: center; gap: 8px; margin-top: 6px; padding-top: 6px; border-top: 1px dashed var(--border-card);">
                            <div style="display: flex; align-items: center; gap: 6px;">
                                <select onchange="updateOfficeTaskStatus('${t.id}', this.value)" style="background: rgba(15, 23, 42, 0.75); border: 1px solid var(--border-card); color: var(--text-primary); border-radius: 6px; padding: 3px 6px; font-size: 10px; font-weight: 800; cursor: pointer; outline: none;">
                                    <option value="backlog" ${t.status === 'backlog' ? 'selected' : ''}>📋 {{ __('Backlog') }}</option>
                                    <option value="ready" ${t.status === 'ready' ? 'selected' : ''}>📌 {{ __('Ready') }}</option>
                                    <option value="in_progress" ${t.status === 'in_progress' ? 'selected' : ''}>⚡ {{ __('In Progress') }}</option>
                                    <option value="review" ${t.status === 'review' ? 'selected' : ''}>🔍 {{ __('Review') }}</option>
                                    <option value="qa" ${t.status === 'qa' ? 'selected' : ''}>🧪 {{ __('QA') }}</option>
                                    <option value="done" ${t.status === 'done' ? 'selected' : ''}>✅ {{ __('Done') }}</option>
                                </select>
                                <span style="font-size: 10px; color: var(--text-muted);">
                                    ${t.due_date ? `📅 ${t.due_date}` : ''}
                                </span>
                            </div>

                            ${isRunning ? `
                                <button type="button" onclick="stopActiveOfficeTask()" class="tactile-btn" style="background: rgba(239, 68, 68, 0.2); color: #F87171; border: 1px solid rgba(239, 68, 68, 0.4); padding: 4px 10px; font-size: 11px; font-weight: 800;">
                                    ⏹️ {{ __('Stop') }}
                                </button>
                            ` : `
                                <button type="button" onclick="startTaskTimerInOffice('${t.id}', '${t.project_id}', '${escapeAttr(t.title)}', '${escapeAttr(t.project_name || 'Project')}')" class="tactile-btn" style="background: rgba(16, 185, 129, 0.18); color: #34D399; border: 1px solid rgba(52, 211, 153, 0.4); padding: 4px 10px; font-size: 11px; font-weight: 800;">
                                    ▶ {{ __('Start') }}
                                </button>
                            `}
                        </div>
                    </div>
                `;
            }).join('');
        }

        async function updateOfficeTaskStatus(taskId, newStatus) {
            try {
                const res = await fetch(`/api/office/tasks/${taskId}/status`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': CONFIG.csrf
                    },
                    body: JSON.stringify({ status: newStatus })
                });
                const data = await res.json();
                if (res.ok && data.success) {
                    showToast(`✅ {{ __("Task status updated to:") }} ${newStatus.replace('_', ' ')}`);
                    loadMyOfficeTasks();
                } else {
                    showToast(`❌ ${data.message || '{{ __("Failed to update status") }}'}`);
                }
            } catch (e) {
                console.error('Error updating task status:', e);
                showToast('❌ {{ __("Network error updating status.") }}');
            }
        }

        function filterOfficeTasks(query) {
            const q = (query || '').toLowerCase().trim();
            if (!q) {
                renderOfficeTasksList(myOfficeTasksList);
                return;
            }
            const filtered = myOfficeTasksList.filter(t => 
                (t.title || '').toLowerCase().includes(q) || 
                (t.project_name || '').toLowerCase().includes(q)
            );
            renderOfficeTasksList(filtered);
        }

        async function startTaskTimerInOffice(taskId, projectId, title, projectName) {
            try {
                const res = await fetch('/api/office/task-timer/start', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': CONFIG.csrf
                    },
                    body: JSON.stringify({
                        task_id: taskId,
                        project_id: projectId
                    })
                });
                const data = await res.json();
                if (res.ok && data.success) {
                    showToast(`⚡ {{ __("Task timer started for:") }} ${title}`);
                    loadMyOfficeTasks();
                } else {
                    showToast(`❌ ${data.message || '{{ __("Failed to start timer") }}'}`);
                }
            } catch (e) {
                console.error('Error starting task timer:', e);
                showToast('❌ {{ __("Network error starting timer.") }}');
            }
        }

        async function stopActiveOfficeTask() {
            try {
                const res = await fetch('/api/office/task-timer/stop', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': CONFIG.csrf
                    },
                    body: JSON.stringify({})
                });
                const data = await res.json();
                if (res.ok && data.success) {
                    showToast('⏹️ {{ __("Task timer stopped and logged to timesheet!") }}');
                    loadMyOfficeTasks();
                } else {
                    showToast(`❌ ${data.message || '{{ __("Failed to stop timer") }}'}`);
                }
            } catch (e) {
                console.error('Error stopping task timer:', e);
                showToast('❌ {{ __("Network error stopping timer.") }}');
            }
        }

        // ── CHAT & FILE SHARING ENGINE (ROOM-SCOPED & GLOBAL) ──
        let chatScope = 'room'; // 'room' or 'global'
        const roomMessages = [];
        const globalMessages = [];

        function toggleChatDrawer() {
            const drawer = document.getElementById('chat-drawer');
            if (!drawer) return;
            const isShown = drawer.style.display === 'flex';
            drawer.style.display = isShown ? 'none' : 'flex';
            if (!isShown) {
                renderChatMessages();
                const inp = document.getElementById('chat-msg-input');
                if (inp) inp.focus();
            }
        }

        function switchChatScope(scope) {
            chatScope = scope;
            const roomTab = document.getElementById('chat-tab-room');
            const globalTab = document.getElementById('chat-tab-global');
            if (roomTab) roomTab.classList.toggle('active', scope === 'room');
            if (globalTab) globalTab.classList.toggle('active', scope === 'global');
            renderChatMessages();
        }

        function renderChatMessages() {
            const container = document.getElementById('chat-messages-container');
            if (!container) return;

            const msgs = chatScope === 'global' ? globalMessages : roomMessages;
            const myRoom = getCurrentRoom(localAvatar.x, localAvatar.y);

            let headerHint = '';
            if (chatScope === 'room') {
                if (myRoom) {
                    headerHint = `<div style="text-align: center; padding: 4px 8px; margin-bottom: 8px; font-size: 10px; font-weight: 800; color: #34D399; background: rgba(16, 185, 129, 0.12); border-radius: 6px;">🏢 {{ __("Acoustic Room Channel:") }} ${escapeHtml(myRoom.name)}</div>`;
                } else {
                    headerHint = `<div style="text-align: center; padding: 4px 8px; margin-bottom: 8px; font-size: 10px; font-weight: 800; color: #F59E0B; background: rgba(245, 158, 11, 0.12); border-radius: 6px;">🚪 {{ __("Hallway / Open Space (Enter a room to chat with room occupants)") }}</div>`;
                }
            } else {
                headerHint = `<div style="text-align: center; padding: 4px 8px; margin-bottom: 8px; font-size: 10px; font-weight: 800; color: #60A5FA; background: rgba(59, 130, 246, 0.12); border-radius: 6px;">🌐 {{ __("Company-Wide General Office Channel") }}</div>`;
            }

            if (!msgs.length) {
                container.innerHTML = headerHint + `
                    <div style="text-align: center; padding: 24px 10px; color: var(--text-muted); font-size: 11px;">
                        💬 ${chatScope === 'global' ? '{{ __("No general messages yet. Send a message to everyone!") }}' : '{{ __("No messages in this room yet.") }}'}
                    </div>
                `;
                return;
            }

            container.innerHTML = headerHint + msgs.map(m => {
                const isSelf = m.senderId === localAvatar.id;
                return `
                    <div class="msg-bubble ${isSelf ? 'self' : ''}">
                        <div class="msg-meta">
                            <span>${isSelf ? '{{ __("You") }}' : escapeHtml(m.senderName)}</span>
                            <span>${m.time || ''}</span>
                        </div>
                        <span style="word-break: break-word;">${escapeHtml(m.body || m.text || '')}</span>
                    </div>
                `;
            }).join('');

            container.scrollTop = container.scrollHeight;
        }

        function appendChatMessage(msgPayload, isLocalSender = false) {
            const targetList = msgPayload.scope === 'global' ? globalMessages : roomMessages;
            targetList.push(msgPayload);

            if (targetList.length > 100) targetList.shift();

            // Re-render if current tab matches
            if (chatScope === msgPayload.scope) {
                renderChatMessages();
            } else {
                showToast(`💬 ${escapeHtml(msgPayload.senderName)}: ${escapeHtml((msgPayload.body || msgPayload.text || '').substring(0, 30))}`);
            }
        }

        function sendChatMessage() {
            const inp = document.getElementById('chat-msg-input');
            if (!inp) return;
            const text = inp.value.trim();
            if (!text) return;
            inp.value = '';

            const myRoom = getCurrentRoom(localAvatar.x, localAvatar.y);
            const activeScope = chatScope;
            const roomId = myRoom ? myRoom.id : null;

            const timeStr = new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
            const msgPayload = {
                id: 'local_' + Date.now(),
                senderId: localAvatar.id,
                senderName: localAvatar.name,
                body: text,
                time: timeStr,
                scope: activeScope,
                roomId: roomId,
            };

            // 1. Append locally immediately
            appendChatMessage(msgPayload, true);

            // 2. Spawn floating bubble on canvas
            spawnSpeechBubble(localAvatar.id, localAvatar.name, text);

            // 3. Send over WebSocket
            if (ws && ws.readyState === WebSocket.OPEN) {
                ws.send(JSON.stringify({
                    type: 'chat.send',
                    payload: {
                        body: text,
                        scope: activeScope,
                        roomId: roomId,
                    }
                }));

                ws.send(JSON.stringify({
                    type: 'chat.bubble',
                    payload: {
                        text: text,
                        scope: activeScope,
                        roomId: roomId,
                    }
                }));
            }
        }

        function spawnSpeechBubble(userId, userName, text, emoji = null) {
            if (!userId) return;
            speechBubbles.set(userId, {
                text: text || '',
                emoji: emoji || null,
                userName: userName || 'Member',
                expiresAt: Date.now() + 6000 // 6 seconds display
            });
        }

        function escapeHtml(str) {
            if (!str) return '';
            return String(str).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;').replace(/'/g, '&#039;');
        }

        function escapeAttr(str) {
            if (!str) return '';
            return String(str).replace(/'/g, "\\'").replace(/"/g, '&quot;');
        }

        // ── Camera Gallery Grid Overlay ──
        function toggleCameraGalleryModal() {
            const modal = document.getElementById('camera-gallery-modal');
            if (!modal) return;
            const isShown = modal.style.display === 'flex';
            if (isShown) {
                closeCameraGalleryModal();
            } else {
                modal.style.display = 'flex';
                updateGalleryGrid();
            }
        }

        function closeCameraGalleryModal() {
            const modal = document.getElementById('camera-gallery-modal');
            if (modal) modal.style.display = 'none';
        }

        function updateGalleryGrid() {
            const grid = document.getElementById('camera-gallery-grid');
            const modal = document.getElementById('camera-gallery-modal');
            if (!grid || !modal || modal.style.display !== 'flex') return;

            grid.innerHTML = '';

            const localRoom = getCurrentRoom(localAvatar.x, localAvatar.y);

            // Local user card
            const selfCard = document.createElement('div');
            selfCard.style.cssText = 'position: relative; height: 200px; background: #08120D; border-radius: 14px; overflow: hidden; border: 2px solid var(--brand-primary); display: flex; align-items: center; justify-content: center; cursor: pointer;';
            const localSrc = localMediaStream || (localAvatar.videoEl ? localAvatar.videoEl.srcObject : null);
            if (camActive && localSrc) {
                const selfVid = document.createElement('video');
                selfVid.autoplay = true;
                selfVid.playsInline = true;
                selfVid.muted = true;
                selfVid.srcObject = localSrc;
                selfVid.style.cssText = 'width: 100%; height: 100%; object-fit: cover;';
                selfCard.appendChild(selfVid);
            } else {
                const init = (localAvatar.name || 'You').substring(0, 2).toUpperCase();
                selfCard.innerHTML = `<div style="display:flex; flex-direction:column; align-items:center; gap:8px;"><div style="width:52px;height:52px;border-radius:50%;background:rgba(16,185,129,0.2);display:flex;align-items:center;justify-content:center;font-size:18px;font-weight:900;color:#6EE7B7;">${init}</div><span style="font-size:11px;color:var(--text-muted);">{{ __("Camera Off") }}</span></div>`;
            }
            const selfLabel = document.createElement('div');
            selfLabel.style.cssText = 'position: absolute; bottom: 8px; left: 8px; background: rgba(0,0,0,0.7); backdrop-filter: blur(8px); padding: 3px 8px; border-radius: 6px; font-size: 11px; font-weight: 800; color: #6EE7B7;';
            selfLabel.textContent = `${localAvatar.name} ({{ __("You") }}) ${micActive ? '🎙️' : '🔇'}`;
            selfCard.appendChild(selfLabel);
            selfCard.onclick = () => openUserSpotlight(localAvatar.id);
            grid.appendChild(selfCard);

            // Remote users
            remoteAvatars.forEach(av => {
                const rCard = document.createElement('div');
                rCard.style.cssText = 'position: relative; height: 200px; background: #0F172A; border-radius: 14px; overflow: hidden; border: 1px solid var(--border-color); display: flex; align-items: center; justify-content: center; cursor: pointer;';
                
                const remoteRoom = getCurrentRoom(av.x, av.y);
                let canViewRemoteVideo = false;
                if (localRoom) {
                    // Inside a room: only see colleagues in the same room
                    canViewRemoteVideo = (remoteRoom && remoteRoom.id === localRoom.id);
                } else {
                    // On open floor: only see colleagues on open floor within proximity
                    if (!remoteRoom) {
                        const dist = Math.hypot(localAvatar.x - av.x, localAvatar.y - av.y);
                        canViewRemoteVideo = (dist <= 300);
                    }
                }

                const vidEl = av.videoEl || peerVideoCards.get(av.id)?.querySelector('video');
                let videoAttached = false;

                if (canViewRemoteVideo) {
                    if (av.livekitVideoTrack) {
                        const liveVid = document.createElement('video');
                        liveVid.autoplay = true;
                        liveVid.playsInline = true;
                        liveVid.muted = true;
                        av.livekitVideoTrack.attach(liveVid);
                        liveVid.style.cssText = 'width: 100%; height: 100%; object-fit: cover;';
                        rCard.appendChild(liveVid);
                        liveVid.play().catch(()=>{});
                        videoAttached = true;
                    } else if (vidEl && vidEl.srcObject) {
                        const cloneVid = document.createElement('video');
                        cloneVid.autoplay = true;
                        cloneVid.playsInline = true;
                        cloneVid.srcObject = vidEl.srcObject;
                        cloneVid.style.cssText = 'width: 100%; height: 100%; object-fit: cover;';
                        rCard.appendChild(cloneVid);
                        cloneVid.play().catch(()=>{});
                        videoAttached = true;
                    }
                }

                if (!videoAttached) {
                    const init = (av.name || 'User').substring(0, 2).toUpperCase();
                    let statusHtml = '';
                    if (remoteRoom && (!localRoom || localRoom.id !== remoteRoom.id)) {
                        statusHtml = `<span style="font-size:11px; color:#FCA5A5; font-weight:700;">🔒 {{ __("In Private Room:") }} ${remoteRoom.name.split(' - ')[0]}</span>`;
                    } else if (av.camActive && !canViewRemoteVideo) {
                        statusHtml = `<span style="font-size:11px; color:var(--text-muted);">🏢 {{ __("Out of visual range") }}</span>`;
                    } else {
                        statusHtml = `<span style="font-size:11px; color:var(--text-muted);">${av.camActive ? '🟢 {{ __("Camera Active") }}' : '{{ __("Camera Off") }}'}</span>`;
                    }
                    rCard.innerHTML = `<div style="display:flex; flex-direction:column; align-items:center; gap:8px; text-align:center; padding:10px;"><div style="width:52px;height:52px;border-radius:50%;background:rgba(59,130,246,0.2);display:flex;align-items:center;justify-content:center;font-size:18px;font-weight:900;color:#93C5FD;">${init}</div>${statusHtml}</div>`;
                }
                const rLabel = document.createElement('div');
                rLabel.style.cssText = 'position: absolute; bottom: 8px; left: 8px; background: rgba(0,0,0,0.7); backdrop-filter: blur(8px); padding: 3px 8px; border-radius: 6px; font-size: 11px; font-weight: 800; color: #FFFFFF;';
                rLabel.textContent = `${av.name} ${av.micActive ? '🎙️' : '🔇'}`;
                rCard.appendChild(rLabel);
                rCard.onclick = () => openUserSpotlight(av.id);
                grid.appendChild(rCard);
            });
        }

        // ── Room Attendance & Working Hours Logger ──
        let roomEnterTimestamp = Date.now();
        async function logAttendanceInterval(action, roomId) {
            try {
                let duration = 0;
                if (action === 'leave') {
                    duration = Math.round((Date.now() - roomEnterTimestamp) / 1000);
                } else if (action === 'enter') {
                    roomEnterTimestamp = Date.now();
                }
                fetch('/api/office/attendance/log', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CONFIG.csrf, 'Accept': 'application/json' },
                    credentials: 'same-origin',
                    body: JSON.stringify({ action: action, room_id: roomId, duration_seconds: duration })
                }).catch(()=>{});
            } catch(e) {}
        }

        // ── WebRTC Device Settings & Diagnostics Modals ──
        let latestDiagResults = null;

        async function openDeviceSettingsModal() {
            const modal = document.getElementById('device-settings-modal');
            if (!modal) return;
            modal.style.display = 'flex';

            const selCam = document.getElementById('select-video-input');
            const selMic = document.getElementById('select-audio-input');
            const selSpk = document.getElementById('select-audio-output');
            const previewVideo = document.getElementById('device-preview-video');
            const noPreview = document.getElementById('device-no-preview');

            try {
                if (window.VWorkWebRTC && window.VWorkWebRTC.deviceManager) {
                    const dev = await window.VWorkWebRTC.deviceManager.enumerateDevices();
                    if (dev) {
                        selCam.innerHTML = dev.cams.map(c => `<option value="${c.deviceId}">${c.label || 'Camera ' + c.deviceId.substring(0,5)}</option>`).join('') || '<option value="default">{{ __("Default Camera") }}</option>';
                        selMic.innerHTML = dev.mics.map(m => `<option value="${m.deviceId}">${m.label || 'Mic ' + m.deviceId.substring(0,5)}</option>`).join('') || '<option value="default">{{ __("Default Microphone") }}</option>';
                        selSpk.innerHTML = dev.speakers.map(s => `<option value="${s.deviceId}">${s.label || 'Speaker ' + s.deviceId.substring(0,5)}</option>`).join('') || '<option value="default">{{ __("Default Speaker") }}</option>';
                        
                        selCam.value = window.VWorkWebRTC.deviceManager.selectedVideoInputId;
                        selMic.value = window.VWorkWebRTC.deviceManager.selectedAudioInputId;
                        selSpk.value = window.VWorkWebRTC.deviceManager.selectedAudioOutputId;
                    }

                    // Start camera preview & mic meter
                    try {
                        await window.VWorkWebRTC.deviceManager.startCameraPreview(previewVideo);
                        previewVideo.style.display = 'block';
                        noPreview.style.display = 'none';
                    } catch(e) {
                        previewVideo.style.display = 'none';
                        noPreview.style.display = 'block';
                    }

                    window.VWorkWebRTC.deviceManager.startMicLevelMeter((volume) => {
                        const bar = document.getElementById('mic-level-bar');
                        const val = document.getElementById('mic-level-val');
                        if (bar) bar.style.width = `${volume}%`;
                        if (val) val.textContent = `${volume}%`;
                    });
                }
            } catch(err) {
                console.error(err);
            }
        }

        function closeDeviceSettingsModal() {
            if (window.VWorkWebRTC && window.VWorkWebRTC.deviceManager) {
                window.VWorkWebRTC.deviceManager.stopCameraPreview();
                window.VWorkWebRTC.deviceManager.stopMicLevelMeter();
            }
            const modal = document.getElementById('device-settings-modal');
            if (modal) modal.style.display = 'none';
        }

        function onCameraDeviceChanged(devId) {
            if (window.VWorkWebRTC && window.VWorkWebRTC.deviceManager) {
                window.VWorkWebRTC.deviceManager.setVideoInput(devId);
                const previewVideo = document.getElementById('device-preview-video');
                window.VWorkWebRTC.deviceManager.startCameraPreview(previewVideo, devId).catch(()=>{});
            }
        }

        function onMicDeviceChanged(devId) {
            if (window.VWorkWebRTC && window.VWorkWebRTC.deviceManager) {
                window.VWorkWebRTC.deviceManager.setAudioInput(devId);
                window.VWorkWebRTC.deviceManager.startMicLevelMeter((volume) => {
                    const bar = document.getElementById('mic-level-bar');
                    const val = document.getElementById('mic-level-val');
                    if (bar) bar.style.width = `${volume}%`;
                    if (val) val.textContent = `${volume}%`;
                }, devId);
            }
        }

        function onSpeakerDeviceChanged(devId) {
            if (window.VWorkWebRTC && window.VWorkWebRTC.deviceManager) {
                window.VWorkWebRTC.deviceManager.setAudioOutput(devId);
            }
        }

        async function openDiagnosticsModal() {
            const modal = document.getElementById('diagnostics-modal');
            if (!modal) return;
            modal.style.display = 'flex';
            await runDiagnosticsCheck();
        }

        function closeDiagnosticsModal() {
            const modal = document.getElementById('diagnostics-modal');
            if (modal) modal.style.display = 'none';
        }

        async function runDiagnosticsCheck() {
            const loading = document.getElementById('diag-loading');
            const content = document.getElementById('diag-content');
            loading.style.display = 'block';
            content.style.display = 'none';

            try {
                const configRes = await fetch(`/organizations/${CONFIG.org.id}/webrtc/diagnostics-config`, {
                    headers: { 'Accept': 'application/json' }
                });
                const config = configRes.ok ? await configRes.json() : {};

                if (window.VWorkWebRTC && window.VWorkWebRTC.diagnostics) {
                    const results = await window.VWorkWebRTC.diagnostics.runFullDiagnostics(config);
                    latestDiagResults = results;

                    // Populate UI
                    document.getElementById('diag-overall-text').textContent = `${results.overall}`;
                    document.getElementById('diag-overall-badge').textContent = results.overall === 'Excellent' ? '🟢' : (results.overall.includes('Good') ? '🟡' : '🔴');
                    document.getElementById('diag-cam-status').textContent = results.camera.passed ? '✓ Active' : '✗ ' + results.camera.message;
                    document.getElementById('diag-cam-status').style.color = results.camera.passed ? '#10B981' : '#EF4444';
                    document.getElementById('diag-mic-status').textContent = results.microphone.passed ? '✓ Active' : '✗ ' + results.microphone.message;
                    document.getElementById('diag-mic-status').style.color = results.microphone.passed ? '#10B981' : '#EF4444';
                    document.getElementById('diag-ping-status').textContent = `${results.internet.latencyMs} ms`;
                    document.getElementById('diag-turn-status').textContent = results.turn.passed ? '✓ Active (Coturn)' : '✗ Inactive';
                    document.getElementById('diag-livekit-host').textContent = results.livekit.host;
                    document.getElementById('diag-packet-loss').textContent = `${results.networkStats.packetLoss}%`;
                    document.getElementById('diag-jitter').textContent = `${results.networkStats.jitter} ms`;
                    document.getElementById('diag-fps').textContent = `${results.networkStats.fps} FPS`;
                }
            } catch(e) {
                console.error(e);
            }

            loading.style.display = 'none';
            content.style.display = 'flex';
        }

        function copyDiagnosticsReport() {
            if (!latestDiagResults || !window.VWorkWebRTC || !window.VWorkWebRTC.diagnostics) return;
            const text = window.VWorkWebRTC.diagnostics.formatDiagnosticsReport(latestDiagResults);
            navigator.clipboard.writeText(text).then(() => {
                showToast('📋 {{ __("Diagnostics report copied to clipboard!") }}');
            });
        }

        // Listen for realtime connection quality changes
        if (window.VWorkWebRTC && window.VWorkWebRTC.connectionMonitor) {
            window.VWorkWebRTC.connectionMonitor.onQualityChange((quality, stats) => {
                const dot = document.getElementById('webrtc-quality-dot');
                const text = document.getElementById('webrtc-quality-text');
                if (dot && text) {
                    dot.style.background = quality === 'excellent' ? '#10B981' : (quality === 'good' || quality === 'fair' ? '#F59E0B' : '#EF4444');
                    text.textContent = quality === 'excellent' ? '{{ __("Excellent") }}' : (quality === 'good' ? '{{ __("Good") }}' : quality.toUpperCase());
                }
            });
        }

        function toggleAppTheme() {
            const cur = document.documentElement.getAttribute('data-theme') || 'dark';
            const next = cur === 'dark' ? 'light' : 'dark';
            document.documentElement.setAttribute('data-theme', next);
            document.getElementById('theme-icon').textContent = next === 'dark' ? '☀️' : '🌙';
        }

        function showToast(msg, customType = null) {
            const container = document.getElementById('nx-toast-container');
            if (!container) return;

            const card = document.createElement('div');
            let icon = 'info';
            let type = customType || 'info';

            const str = String(msg || '');
            if (str.includes('🚫') || str.includes('❌') || str.includes('denied') || str.includes('خطأ')) {
                icon = 'cancel'; type = 'error';
            } else if (str.includes('✅') || str.includes('▶️') || str.includes('unlocked') || str.includes('بنجاح') || str.includes('granted')) {
                icon = 'check_circle'; type = 'success';
            } else if (str.includes('⚠️') || str.includes('🔒') || str.includes('locked') || str.includes('تحذير')) {
                icon = 'warning'; type = 'warning';
            } else if (str.includes('🚪') || str.includes('Door') || str.includes('Room')) {
                icon = 'meeting_room';
            } else if (str.includes('☕')) {
                icon = 'coffee';
            } else if (str.includes('🎙️') || str.includes('🔇') || str.includes('Microphone')) {
                icon = 'mic';
            } else if (str.includes('📹') || str.includes('📷') || str.includes('Camera')) {
                icon = 'videocam';
            } else if (str.includes('🖥️') || str.includes('Screen')) {
                icon = 'screen_share';
            } else if (str.includes('👋')) {
                icon = 'waving_hand';
            } else if (str.includes('🎯')) {
                icon = 'center_focus_strong';
            } else if (str.includes('🪑')) {
                icon = 'chair';
            } else if (str.includes('🔔')) {
                icon = 'notifications_active';
            }

            card.className = `nx-toast-card nx-toast-${type}`;
            card.innerHTML = `
                <div class="nx-toast-icon-wrap">
                    <span class="material-symbols-rounded" style="font-size: 18px;">${icon}</span>
                </div>
                <div class="nx-toast-content">
                    <div class="nx-toast-msg">${str}</div>
                </div>
                <button type="button" class="nx-toast-close" onclick="this.closest('.nx-toast-card').remove()" title="Close">✕</button>
            `;

            container.appendChild(card);
            setTimeout(() => {
                card.classList.add('nx-toast-fadeout');
                setTimeout(() => { if (card.parentElement) card.remove(); }, 250);
            }, 3500);
        }

        function toggleOfficeMainMenu(e) {
            if (e) e.stopPropagation();
            const menu = document.getElementById('office-main-menu-dropdown');
            const branchDD = document.getElementById('office-switcher-dropdown');
            if (branchDD) branchDD.style.display = 'none';
            if (menu) {
                menu.style.display = (menu.style.display === 'none' || menu.style.display === '') ? 'block' : 'none';
            }
        }

        function closeOfficeMainMenu() {
            const menu = document.getElementById('office-main-menu-dropdown');
            if (menu) menu.style.display = 'none';
        }

        function toggleOfficeDropdown(e) {
            if (e) e.stopPropagation();
            const menu = document.getElementById('office-main-menu-dropdown');
            if (menu) menu.style.display = 'none';
            const dd = document.getElementById('office-switcher-dropdown');
            if (dd) {
                dd.style.display = (dd.style.display === 'none' || dd.style.display === '') ? 'block' : 'none';
            }
        }

        // Close dropdowns on outside click
        window.addEventListener('click', function(e) {
            const menu = document.getElementById('office-main-menu-dropdown');
            if (menu && menu.style.display === 'block') {
                if (!e.target.closest('#office-main-menu-dropdown') && !e.target.closest('button[onclick*="toggleOfficeMainMenu"]') && !e.target.closest('.nx-brand-capsule')) {
                    menu.style.display = 'none';
                }
            }
            const branchDD = document.getElementById('office-switcher-dropdown');
            if (branchDD && branchDD.style.display === 'block') {
                if (!e.target.closest('#office-switcher-dropdown') && !e.target.closest('button[onclick*="toggleOfficeDropdown"]')) {
                    branchDD.style.display = 'none';
                }
            }
        });

        // ══════════════════════════════════════════════════════════════════════
        // ⏱️ TIME & ATTENDANCE, IN-OFFICE TASK TRACKING & SMART IDLE DETECTOR
        // ══════════════════════════════════════════════════════════════════════

        let officeAttendanceSessionActive = true;
        let isOfficePresencePaused = false;
        let hasActiveTaskRunning = false;
        let activeTaskTimerData = null;
        let activeTaskTimerInterval = null;
        let cachedAssignedTasks = [];

        // Idle Detector Configuration
        const idlePolicy = CONFIG.attendancePolicy || {};
        const idlePromptMinutes = Number(idlePolicy.idle_prompt_minutes || 15);
        const idleGraceSeconds = Number(idlePolicy.idle_response_grace_seconds || 180);
        let lastUserActivityTimestamp = Date.now();
        let idleCountdownSeconds = idleGraceSeconds;
        let idleCountdownInterval = null;
        let isIdleCheckModalOpen = false;

        // 1. Attendance Heartbeat & Presence Logger & Live Office Clock
        let officeAttendanceTotalSeconds = 0;
        let officeAttendanceTimerInterval = null;

        async function fetchInitialAttendanceSummary() {
            if (isGuest) return;
            try {
                const res = await fetch(`/api/office/attendance/summary?organization_id=${CONFIG.org?.id}`, {
                    headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': CONFIG.csrf }
                });
                if (res.ok) {
                    const data = await res.json();
                    if (data && typeof data.today_total_seconds !== 'undefined') {
                        officeAttendanceTotalSeconds = Math.floor(Number(data.today_total_seconds) || 0);
                    }
                }
            } catch(e) {}
            startOfficeAttendanceTicker();
        }

        function startOfficeAttendanceTicker() {
            if (officeAttendanceTimerInterval) clearInterval(officeAttendanceTimerInterval);
            const clockEl = document.getElementById('office-attendance-clock');
            
            function tick() {
                if (!isOfficePresencePaused) {
                    officeAttendanceTotalSeconds = Math.floor(officeAttendanceTotalSeconds) + 1;
                    if (clockEl) {
                        const totalSecs = Math.floor(officeAttendanceTotalSeconds);
                        const hrs = String(Math.floor(totalSecs / 3600)).padStart(2, '0');
                        const mins = String(Math.floor((totalSecs % 3600) / 60)).padStart(2, '0');
                        const secs = String(totalSecs % 60).padStart(2, '0');
                        clockEl.textContent = `${hrs}:${mins}:${secs}`;
                    }
                }
            }
            tick();
            officeAttendanceTimerInterval = setInterval(tick, 1000);
        }

        async function logAttendance(action, duration = null, roomId = null) {
            if (isGuest) return; // Only log for registered members
            try {
                const targetRoom = roomId || (currentRoom ? currentRoom.id : null);
                await fetch('/api/office/attendance/log', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': CONFIG.csrf,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        action: action,
                        room_id: targetRoom,
                        duration_seconds: duration
                    })
                });
            } catch (err) {
                console.error('[Attendance] Log failed:', err);
            }
        }

        // Initialize Attendance on Office Join
        if (!isGuest && (idlePolicy.auto_attendance_enabled !== false)) {
            logAttendance('enter');
            fetchInitialAttendanceSummary();

            // Periodic heartbeat every 45 seconds
            setInterval(() => {
                if (!isOfficePresencePaused) {
                    logAttendance('heartbeat', 45);
                }
            }, 45000);

            // Log exit on page unload
            window.addEventListener('beforeunload', () => {
                if (!isOfficePresencePaused) {
                    navigator.sendBeacon('/api/office/attendance/log', new Blob([JSON.stringify({
                        action: 'leave',
                        _token: CONFIG.csrf
                    })], { type: 'application/json' }));
                }
            });
        }

        // 3. Smart Idle & Inactivity Detector ("Are you still online?")
        function registerUserActivity() {
            lastUserActivityTimestamp = Date.now();
        }

        ['mousemove', 'mousedown', 'keydown', 'touchstart', 'scroll', 'click'].forEach(evt => {
            window.addEventListener(evt, registerUserActivity, { passive: true });
        });

        // Inactivity Check Loop (Runs every 10 seconds)
        setInterval(() => {
            if (isGuest || isOfficePresencePaused || isIdleCheckModalOpen) return;

            // ⚠️ CRITICAL RULE: If user has an active running task, DO NOT log him out or pause!
            if (hasActiveTaskRunning) {
                return;
            }

            const elapsedIdleMs = Date.now() - lastUserActivityTimestamp;
            const thresholdMs = idlePromptMinutes * 60 * 1000;

            if (elapsedIdleMs >= thresholdMs) {
                showIdleCheckModal();
            }
        }, 10000);

        function showIdleCheckModal() {
            isIdleCheckModalOpen = true;
            idleCountdownSeconds = idleGraceSeconds;
            const modal = document.getElementById('office-idle-check-modal');
            const clock = document.getElementById('idle-countdown-clock');
            const bar = document.getElementById('idle-countdown-bar');

            if (modal) modal.style.display = 'flex';
            updateIdleCountdownUI();

            if (idleCountdownInterval) clearInterval(idleCountdownInterval);

            idleCountdownInterval = setInterval(() => {
                idleCountdownSeconds--;
                updateIdleCountdownUI();

                if (idleCountdownSeconds <= 0) {
                    clearInterval(idleCountdownInterval);
                    idleCountdownInterval = null;
                    handleIdleTimeoutExpiration();
                }
            }, 1000);
        }

        function updateIdleCountdownUI() {
            const clock = document.getElementById('idle-countdown-clock');
            const bar = document.getElementById('idle-countdown-bar');
            const mins = String(Math.floor(idleCountdownSeconds / 60)).padStart(2, '0');
            const secs = String(idleCountdownSeconds % 60).padStart(2, '0');
            if (clock) clock.textContent = `${mins}:${secs}`;
            if (bar) {
                const pct = Math.max(0, Math.min(100, (idleCountdownSeconds / idleGraceSeconds) * 100));
                bar.style.width = `${pct}%`;
            }
        }

        function confirmUserPresence() {
            isIdleCheckModalOpen = false;
            if (idleCountdownInterval) {
                clearInterval(idleCountdownInterval);
                idleCountdownInterval = null;
            }
            const modal = document.getElementById('office-idle-check-modal');
            if (modal) modal.style.display = 'none';
            lastUserActivityTimestamp = Date.now();
            logAttendance('heartbeat');
            showToast('🟢 {{ __("Presence confirmed! Your time calculation is active.") }}');
        }

        function handleIdleTimeoutExpiration() {
            isIdleCheckModalOpen = false;
            const checkModal = document.getElementById('office-idle-check-modal');
            if (checkModal) checkModal.style.display = 'none';

            // Show Paused Overlay and Pause Server Attendance Session
            const pausedOverlay = document.getElementById('office-idle-paused-overlay');
            if (pausedOverlay) pausedOverlay.style.display = 'flex';
            isOfficePresencePaused = true;
            logAttendance('idle_pause');
        }

        function resumeUserPresenceFromPaused() {
            const pausedOverlay = document.getElementById('office-idle-paused-overlay');
            if (pausedOverlay) pausedOverlay.style.display = 'none';
            isOfficePresencePaused = false;
            lastUserActivityTimestamp = Date.now();
            logAttendance('idle_resume');
            showToast('▶️ {{ __("Welcome back! Presence tracking resumed.") }}');
        }

        // On Page Load, fetch active timer state if member
        if (!isGuest) {
            loadMyOfficeTasks();
        }

        // Start animation loop
        requestAnimationFrame(draw);
    </script>
</body>
</html>
