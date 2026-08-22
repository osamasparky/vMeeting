<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $organization->name }} — {{ __('Virtual Interactive Office') }}</title>

    <!-- Google Fonts: Cairo (Arabic) & Inter (English) -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;800;900&family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <style>
        :root[data-theme="dark"] {
            --brand-primary: #10B981;
            --brand-primary-hover: #059669;
            --brand-accent: #3B82F6;
            --brand-gold: #F59E0B;
            --brand-crimson: #EF4444;
            --brand-teal: #14B8A6;

            --bg-body: #060D09;
            --bg-dock: rgba(10, 22, 16, 0.95);
            --bg-surface: rgba(15, 30, 22, 0.98);
            --bg-card: rgba(22, 44, 32, 0.88);
            --bg-input: rgba(8, 17, 12, 0.90);
            --border-color: rgba(52, 211, 153, 0.18);
            --border-card: rgba(52, 211, 153, 0.12);

            --text-primary: #F8FAFC;
            --text-secondary: #94A3B8;
            --text-muted: #64748B;

            --shadow-dock: 0 20px 40px rgba(0, 0, 0, 0.55);
            --shadow-card: 0 10px 25px rgba(0, 0, 0, 0.45);
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
            width: 100vw;
            height: 100vh;
            position: relative;
            background: radial-gradient(circle at center, #0B1C13 0%, #050B08 100%);
            overflow: hidden;
        }
        #office-canvas {
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
    @if(session('superadmin_impersonator_id'))
    <div style="background: linear-gradient(90deg, #1E3A8A, #2563EB); color: #ffffff; padding: 8px 24px; display: flex; align-items: center; justify-content: space-between; position: fixed; top: 0; left: 0; right: 0; z-index: 999999; box-shadow: 0 4px 14px rgba(37,99,235,0.4); font-weight: 800; font-size: 12px;">
        <div style="display: flex; align-items: center; gap: 8px;">
            <span style="font-size: 16px;">⚡</span>
            <span>{{ __('Logged in as company:') }} <strong>{{ session('superadmin_impersonated_org_name') }}</strong> ({{ Auth::user()->name }})</span>
        </div>
        <form method="POST" action="{{ route('impersonate.leave') }}" style="margin: 0;">
            @csrf
            <button type="submit" style="background: #ffffff; color: #1E3A8A; border: none; padding: 4px 14px; border-radius: 9999px; font-weight: 900; font-size: 11px; cursor: pointer; display: flex; align-items: center; gap: 4px; box-shadow: 0 2px 6px rgba(0,0,0,0.15);">
                <span>🛡️</span>
                <span>{{ __('Return to Super Admin') }}</span>
            </button>
        </form>
    </div>
    @endif

    <!-- ── Top Floating Overlay Bar ── -->
    <div class="top-bar-overlay" style="{{ session('superadmin_impersonator_id') ? 'top: 50px;' : '' }}">
        <div class="glass-pill">
            <a href="{{ route('dashboard') }}" class="action-link-btn" title="{{ __('Back to Dashboard (الخروج إلى لوحة التحكم)') }}">
                <span>🏠</span> <span>{{ __('Dashboard') }}</span>
            </a>

            <div class="org-badge">
                <div class="status-dot"></div>
                @if(!empty($organization->logo_url))
                    <img src="{{ $organization->logo_url }}" alt="{{ $organization->name }}" class="org-logo-img">
                @elseif(!empty($organization->settings?->logo_url))
                    <img src="{{ $organization->settings->logo_url }}" alt="{{ $organization->name }}" class="org-logo-img">
                @else
                    <span>🏢</span>
                @endif
                <span>{{ $organization->name }}</span>
            </div>

            @if(!empty($user->is_guest))
                <span class="guest-badge">
                    GUEST ACCESS ({{ $user->name }})
                </span>
            @endif
        </div>

        <div class="glass-pill" id="room-status-pill" style="display: none;">
            <span id="current-room-name" style="font-weight: 800; font-size: 12px; color: #34D399;">🏢 Conference Room</span>
            
            <button onclick="openRoomFilesModal()" class="action-link-btn" style="padding: 4px 8px; font-size: 11px;">
                <span>📁</span> <span>{{ __('Room Files') }}</span>
            </button>

            <button onclick="toggleRoomDoorLock()" id="btn-lock-room" class="action-link-btn" style="padding: 4px 8px; font-size: 11px;">
                <span id="lock-icon">🔓</span> <span id="lock-text">{{ __('Lock Door') }}</span>
            </button>
        </div>

        <div class="glass-pill">
            <button onclick="openOccupantsModal()" class="action-link-btn" id="btn-occupants-pill" title="{{ __('Online Members & Guests (المتواجدون في المكتب)') }}">
                <span class="live-dot" style="width: 7px; height: 7px;"></span>
                <span>👥</span> <span id="occupants-counter">1 {{ __('Online') }}</span>
            </button>

            <button onclick="toggleChatDrawer()" class="action-link-btn" title="{{ __('Chat & Files') }}">
                <span>💬</span> <span>{{ __('Chat') }}</span>
            </button>

            <button onclick="toggleAppTheme()" class="action-link-btn" title="{{ __('Toggle Theme') }}">
                <span id="theme-icon">☀️</span>
            </button>

            @if(app()->getLocale() === 'ar')
                <a href="{{ route('lang.switch', 'en') }}" class="action-link-btn" title="English">🌐 EN</a>
            @else
                <a href="{{ route('lang.switch', 'ar') }}" class="action-link-btn" title="العربية">🌐 عربي</a>
            @endif

            @if(!empty($user) && in_array($user->role ?? 'member', ['superadmin', 'company_admin', 'manager']))
                <a href="{{ route('editor') }}" class="action-link-btn" style="color: var(--brand-primary); font-weight: 800;">
                    <span>🛠️</span> {{ __('Map Editor') }}
                </a>
            @endif
        </div>
    </div>

    <!-- ── Interactive Canvas Viewport ── -->
    <div class="canvas-container" id="canvas-container">
        <canvas id="office-canvas"></canvas>
    </div>

    <!-- ── Floating Local Self Camera PiP ── -->
    <div class="local-cam-card" id="local-video-card" style="display: none;">
        <div class="local-cam-header">
            <span style="font-size: 10px; font-weight: 800; color: #F8FAFC; display: flex; align-items: center; gap: 4px;">
                <span class="live-dot" style="width: 6px; height: 6px;"></span>
                📹 {{ $user->name ?? 'You' }} ({{ __('You') }})
            </span>
            <button onclick="toggleCamera()" style="background:none; border:none; color:var(--text-muted); cursor:pointer; font-size:12px; line-height: 1;" title="{{ __('Turn Off Camera') }}">✕</button>
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
                <button onclick="focusActiveScreenShare()" class="action-link-btn" id="btn-chat-focus-screen" style="display: none; padding: 3px 8px; font-size: 10px; color: #34D399; border-color: rgba(52, 211, 153, 0.4);" title="{{ __('View Active Screen Share (عرض الشاشة المشاركة)') }}">
                    🖥️ {{ __('Screen') }}
                </button>
                <button onclick="toggleChatDrawer()" style="background:none; border:none; color:var(--text-muted); font-size:16px; cursor:pointer;">✕</button>
            </div>
        </div>
        <div class="chat-tabs">
            <div class="chat-tab active" id="chat-tab-room" onclick="switchChatScope('room')">🏢 {{ __('Room') }}</div>
            <div class="chat-tab" id="chat-tab-global" onclick="switchChatScope('global')">🌐 {{ __('General') }}</div>
        </div>
        <div class="chat-messages" id="chat-messages-container">
            <div class="msg-bubble">
                <div class="msg-meta"><span>🤖 Assistant</span> <span>{{ date('H:i') }}</span></div>
                <span>{{ __('Welcome to the collaborative office! Use chat to share notes and files with your team.') }}</span>
            </div>
        </div>
        <div class="chat-input-bar">
            <input type="file" id="chat-file-input" style="display:none;" onchange="handleChatFileUpload(this)">
            <button onclick="document.getElementById('chat-file-input').click()" class="action-link-btn" style="padding: 6px 8px;" title="{{ __('Attach File') }}">📎</button>
            <input type="text" id="chat-msg-input" placeholder="{{ __('Type message...') }}" class="styled-input" style="padding: 8px 10px; font-size: 12px;" onkeydown="if(event.key==='Enter') sendChatMessage()">
            <button onclick="sendChatMessage()" class="action-link-btn" style="background: var(--brand-primary); color: white; padding: 6px 12px;">➤</button>
        </div>
    </div>

    <!-- ── Bottom Floating Dock ── -->
    <div class="bottom-dock">
        <button class="dock-btn muted" id="btn-mic" onclick="toggleMicrophone()">
            <span id="mic-icon">🔇</span>
            <span id="mic-text">{{ __('Mic Off') }}</span>
        </button>
        <button class="dock-btn muted" id="btn-cam" onclick="toggleCamera()">
            <span id="cam-icon">📷</span>
            <span id="cam-text">{{ __('Cam Off') }}</span>
        </button>
        <button class="dock-btn" id="btn-screen" onclick="toggleScreenShare()">
            <span id="screen-icon">🖥️</span>
            <span id="screen-text">{{ __('Share') }}</span>
        </button>

        <div class="dock-divider"></div>

        <button class="dock-btn" onclick="openAvatarModal()">
            <span>🎭</span>
            <span>{{ __('Avatar') }}</span>
        </button>
        <button class="dock-btn" onclick="openGuestInviteModal()">
            <span>⚡</span>
            <span>{{ __('Invite') }}</span>
        </button>
        <button class="dock-btn" onclick="openWhiteboardModal()">
            <span>📋</span>
            <span>{{ __('Board') }}</span>
        </button>
        <button class="dock-btn" id="btn-record" onclick="toggleRecording()">
            <span id="rec-icon">⏺️</span>
            <span id="rec-text">{{ __('Record') }}</span>
        </button>
        <button class="dock-btn" onclick="openRecordingsGallery()">
            <span>📼</span>
            <span>{{ __('Gallery') }}</span>
        </button>
    </div>

    <!-- ── Modals ── -->

    <!-- 1. Avatar Picker Modal -->
    <div id="avatar-modal" class="modal-overlay">
        <div class="modal-card">
            <div class="modal-header">
                <div class="modal-title"><span>🎭</span> {{ __('Select 2.5D Avatar Character') }}</div>
                <button onclick="closeAvatarModal()" style="background:none; border:none; color:var(--text-muted); font-size:18px; cursor:pointer;">✕</button>
            </div>
            <div class="avatar-grid">
                <div class="avatar-card-picker" id="pick-male" onclick="setAvatarGender('male')">
                    <img src="/images/avatars/male.png" class="avatar-preview-img" alt="Male Character">
                    <strong style="font-size: 13px;">👨 {{ __('Business Male') }}</strong>
                </div>
                <div class="avatar-card-picker" id="pick-female" onclick="setAvatarGender('female')">
                    <img src="/images/avatars/female.png" class="avatar-preview-img" alt="Female Character">
                    <strong style="font-size: 13px;">👩 {{ __('Executive Female') }}</strong>
                </div>
            </div>
        </div>
    </div>

    <!-- 2. Instant Guest Link Modal -->
    <div id="guest-modal" class="modal-overlay">
        <div class="modal-card">
            <div class="modal-header">
                <div class="modal-title"><span>⚡</span> {{ __('Instant Guest Invitation Link') }}</div>
                <button onclick="closeGuestModal()" style="background:none; border:none; color:var(--text-muted); font-size:18px; cursor:pointer;">✕</button>
            </div>
            <div class="input-group">
                <label class="input-label">{{ __('Select Target Meeting Room') }}</label>
                <select class="styled-input" id="invite-room-select">
                    @foreach($map->rooms as $r)
                        <option value="{{ $r->id }}">🏢 {{ $r->name }} ({{ ucfirst($r->type) }})</option>
                    @endforeach
                </select>
            </div>
            <div class="input-group">
                <label class="input-label">{{ __('Guest Label / Name') }}</label>
                <input type="text" class="styled-input" id="invite-guest-name" value="Investor / Partner">
            </div>
            <button onclick="generateGuestLink()" class="action-link-btn" style="background: var(--brand-primary); color: white; justify-content: center; padding: 12px; font-size: 13px;">
                ⚡ {{ __('Generate Instant Guest Link') }}
            </button>
            <div id="guest-link-result" style="display: none; background: rgba(16, 185, 129, 0.1); border: 1px solid var(--border-color); border-radius: 12px; padding: 12px; flex-direction: column; gap: 8px;">
                <input type="text" id="guest-link-input" readonly class="styled-input" style="font-family: monospace; font-size: 11px;">
                <div style="display: flex; gap: 8px;">
                    <button onclick="copyGuestLink()" class="action-link-btn" style="flex: 1; justify-content: center;">📋 {{ __('Copy Link') }}</button>
                    <button onclick="openGuestInNewWindow()" class="action-link-btn" style="flex: 1; background: var(--brand-accent); color: white; justify-content: center;">🚀 {{ __('Open Guest (اختبار)') }}</button>
                </div>
            </div>
        </div>
    </div>

    <!-- 2b. Live Online Occupants Modal -->
    <div id="occupants-modal" class="modal-overlay">
        <div class="modal-card">
            <div class="modal-header">
                <div class="modal-title"><span>👥</span> {{ __('Active People in Office (المتواجدون حالياً)') }}</div>
                <button onclick="closeOccupantsModal()" style="background:none; border:none; color:var(--text-muted); font-size:18px; cursor:pointer;">✕</button>
            </div>
            <div id="occupants-list" style="display: flex; flex-direction: column; gap: 8px; max-height: 380px; overflow-y: auto;">
                <!-- Populated dynamically via JS -->
            </div>
        </div>
    </div>

    <!-- 3. Room Persistent Files Modal -->
    <div id="room-files-modal" class="modal-overlay">
        <div class="modal-card">
            <div class="modal-header">
                <div class="modal-title"><span>📁</span> <span id="room-files-title">{{ __('Room Documents & Assets') }}</span></div>
                <button onclick="closeRoomFilesModal()" style="background:none; border:none; color:var(--text-muted); font-size:18px; cursor:pointer;">✕</button>
            </div>
            
            <!-- Upload Box -->
            <div style="background: var(--bg-input); border: 2px dashed var(--border-color); border-radius: 14px; padding: 18px; text-align: center; display: flex; flex-direction: column; align-items: center; gap: 8px;">
                <input type="file" id="room-file-input" style="display:none;" onchange="handleRoomFileUpload(this)">
                <span style="font-size: 28px;">📤</span>
                <span style="font-size: 12px; font-weight: 700;">{{ __('Upload PDF, Slides, or Images to this Room Repository') }}</span>
                <button onclick="document.getElementById('room-file-input').click()" class="action-link-btn" style="background: var(--brand-primary); color: white;">
                    <span>⬆️</span> {{ __('Choose File to Upload') }}
                </button>
            </div>

            <!-- Files List -->
            <div id="room-files-list" style="max-height: 280px; overflow-y: auto; display: flex; flex-direction: column; gap: 8px;"></div>
        </div>
    </div>

    <!-- 4. Rich Collaborative Whiteboard Modal -->
    <div id="whiteboard-modal" class="modal-overlay">
        <div class="modal-card" style="max-width: 1100px; height: 85vh; padding: 0; overflow: hidden;">
            
            <!-- Rich Whiteboard Toolbar -->
            <div class="wb-toolbar">
                <button class="wb-tool-btn active" id="wb-tool-pen" onclick="setWbTool('pen')" title="Pen">✏️</button>
                <button class="wb-tool-btn" id="wb-tool-highlighter" onclick="setWbTool('highlighter')" title="Highlighter">🖍️</button>
                <button class="wb-tool-btn" id="wb-tool-rect" onclick="setWbTool('rect')" title="Rectangle">🔲</button>
                <button class="wb-tool-btn" id="wb-tool-circle" onclick="setWbTool('circle')" title="Circle">⭕</button>
                <button class="wb-tool-btn" id="wb-tool-arrow" onclick="setWbTool('arrow')" title="Arrow">➡️</button>
                <button class="wb-tool-btn" id="wb-tool-line" onclick="setWbTool('line')" title="Straight Line">📏</button>
                <button class="wb-tool-btn" id="wb-tool-text" onclick="setWbTool('text')" title="Add Text">🔤</button>
                <button class="wb-tool-btn" id="wb-tool-note" onclick="setWbTool('note')" title="Sticky Note">📌</button>
                <button class="wb-tool-btn" id="wb-tool-eraser" onclick="setWbTool('eraser')" title="Eraser">🧹</button>

                <div class="dock-divider"></div>

                <!-- Palette -->
                <div style="display: flex; gap: 6px; align-items: center;">
                    <div class="color-dot active" style="background:#0F172A;" onclick="setWbColor('#0F172A')"></div>
                    <div class="color-dot" style="background:#3B82F6;" onclick="setWbColor('#3B82F6')"></div>
                    <div class="color-dot" style="background:#10B981;" onclick="setWbColor('#10B981')"></div>
                    <div class="color-dot" style="background:#F59E0B;" onclick="setWbColor('#F59E0B')"></div>
                    <div class="color-dot" style="background:#EF4444;" onclick="setWbColor('#EF4444')"></div>
                    <div class="color-dot" style="background:#8B5CF6;" onclick="setWbColor('#8B5CF6')"></div>
                </div>

                <div class="dock-divider"></div>

                <button onclick="undoWhiteboard()" class="wb-tool-btn" title="Undo">↩️</button>
                <button onclick="clearWhiteboard()" class="wb-tool-btn" title="Clear Board" style="color: var(--brand-crimson);">🗑️</button>
                <button onclick="exportWhiteboard()" class="action-link-btn" style="padding: 6px 12px;">💾 {{ __('Export PNG') }}</button>
                <button onclick="closeWhiteboardModal()" style="background:none; border:none; color:var(--text-muted); font-size:20px; cursor:pointer; margin-inline-start: auto;">✕</button>
            </div>

            <!-- Whiteboard Drawing Canvas -->
            <div style="flex: 1; position: relative; background: #FFFFFF;" id="wb-container">
                <canvas id="wb-canvas" style="width: 100%; height: 100%; cursor: crosshair;"></canvas>
            </div>
        </div>
    </div>

    <!-- 5. Recordings Gallery Modal -->
    <div id="recordings-modal" class="modal-overlay">
        <div class="modal-card" style="max-width: 900px; height: 80vh;">
            <div class="modal-header">
                <div class="modal-title"><span>📼</span> {{ __('Session Recordings & Gallery') }}</div>
                <button onclick="closeRecordingsGallery()" style="background:none; border:none; color:var(--text-muted); font-size:18px; cursor:pointer;">✕</button>
            </div>
            <div id="recordings-list" style="flex: 1; overflow-y: auto; display: flex; flex-direction: column; gap: 12px;"></div>
        </div>
    </div>

    <!-- 6. Knock Alert Dialog Modal (For Occupants) -->
    <div id="knock-alert-modal" class="modal-overlay">
        <div class="modal-card" style="max-width: 420px;">
            <div class="modal-header">
                <div class="modal-title"><span>🚪</span> {{ __('Door Knock Request') }}</div>
            </div>
            <div class="knock-alert-box">
                <div style="font-size: 32px;">🚪✊</div>
                <strong id="knock-requester-name" style="font-size: 14px; color: var(--text-primary);">A colleague is knocking...</strong>
                <span style="font-size: 12px; color: var(--text-secondary);">{{ __('They are requesting permission to enter this locked private room.') }}</span>
                <div style="display: flex; gap: 10px; margin-top: 6px;">
                    <button onclick="respondToKnock(true)" class="action-link-btn" style="flex: 1; justify-content: center; background: var(--brand-primary); color: white;">
                        ✅ {{ __('Let In') }}
                    </button>
                    <button onclick="respondToKnock(false)" class="action-link-btn" style="flex: 1; justify-content: center; background: rgba(239, 68, 68, 0.15); color: #F87171;">
                        ✕ {{ __('Decline') }}
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Toast Notification -->
    <div id="toast-bubble" class="toast-bubble"></div>

    <!-- ── JavaScript Realtime Engine, Multi-Peer WebRTC Mesh & Spatial Audio Pipeline ── -->
    <script>
        const CONFIG = {
            map: @json($map),
            currentUser: @json($user),
            org: @json($organization),
            token: "{{ $realtimeToken }}",
            wsUrl: @json($wsUrl ?? null),
            csrf: document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        };

        const canvas = document.getElementById('office-canvas');
        const ctx = canvas.getContext('2d');
        const container = document.getElementById('canvas-container');

        let width = canvas.width = container.clientWidth;
        let height = canvas.height = container.clientHeight;

        const TILE_SIZE = (CONFIG.map && CONFIG.map.tile_size) ? Number(CONFIG.map.tile_size) : 16;
        const MAP_WIDTH_PX = 1024;
        const MAP_HEIGHT_PX = 909;

        let zoomLevel = 1.0;
        let cameraOffset = { x: 0, y: 0 };
        const rooms = CONFIG.map.rooms || [];
        const roomDoorStates = new Map();
        let pendingKnock = null;

        // ── Preloaded Background & 2.5D Avatars ──
        const MAP_BG_URL = (CONFIG.map.layout_data && CONFIG.map.layout_data.background_image_url)
            ? CONFIG.map.layout_data.background_image_url
            : '/images/office_floorplan.jpg';
        const BLUEPRINT_IMAGE = new Image();
        BLUEPRINT_IMAGE.src = MAP_BG_URL + (MAP_BG_URL.includes('?') ? '&' : '?') + 'v=' + Date.now();
        let blueprintLoaded = false;
        BLUEPRINT_IMAGE.onload = () => {
            blueprintLoaded = true;
            centerCamera();
            draw();
        };

        const AVATAR_SPRITES = {
            male: new Image(),
            female: new Image()
        };
        AVATAR_SPRITES.male.src = '/images/avatars/male.png';
        AVATAR_SPRITES.female.src = '/images/avatars/female.png';

        let userGender = localStorage.getItem('vw_gender') || 'male';

        // ── Local & Remote Avatars ──
        const isGuest = {{ !empty($user->is_guest) ? 'true' : 'false' }};
        const spawnPos = @json($initialSpawn ?? null);
        const defaultX = isGuest ? 310 : 250;
        const defaultY = isGuest ? 220 : 200;
        const localAvatar = {
            id: String(CONFIG.currentUser?.id || 'usr_1'),
            name: CONFIG.currentUser?.name || 'User',
            isGuest: isGuest,
            x: (spawnPos && spawnPos.x) ? spawnPos.x : defaultX,
            y: (spawnPos && spawnPos.y) ? spawnPos.y : defaultY,
            targetX: (spawnPos && spawnPos.x) ? spawnPos.x : defaultX,
            targetY: (spawnPos && spawnPos.y) ? spawnPos.y : defaultY,
            speed: 5.0,
            radius: 18,
            gender: userGender,
            currentRoomId: null
        };
        const remoteAvatars = new Map();

        // ── WebRTC Multi-Peer Mesh Media ──
        const peerConnections = new Map(); // targetUserId -> RTCPeerConnection
        const peerAudioElements = new Map(); // targetUserId -> HTMLAudioElement
        const peerVideoCards = new Map(); // targetUserId -> HTMLDivElement
        const pendingIceCandidates = new Map(); // targetUserId -> Array of RTCIceCandidate
        let localMediaStream = null;
        let screenStream = null;
        let micActive = false;
        let camActive = false;

        const RTC_CONFIG = {
            iceServers: [
                { urls: 'stun:stun.l.google.com:19302' },
                { urls: 'stun:stun1.l.google.com:19302' },
                { urls: 'stun:stun2.l.google.com:19302' },
                { urls: 'stun:stun3.l.google.com:19302' }
            ]
        };

        // ── Resize & Camera ──
        function centerCamera() {
            if (width && height) {
                const scaleX = (width - 40) / MAP_WIDTH_PX;
                const scaleY = (height - 40) / MAP_HEIGHT_PX;
                zoomLevel = Math.min(1.0, Math.max(0.65, Math.min(scaleX, scaleY)));
                cameraOffset.x = (width - MAP_WIDTH_PX * zoomLevel) / 2;
                cameraOffset.y = (height - MAP_HEIGHT_PX * zoomLevel) / 2;
            }
        }
        function resizeCanvas() {
            width = canvas.width = container.clientWidth;
            height = canvas.height = container.clientHeight;
            centerCamera();
            draw();
        }
        window.addEventListener('resize', resizeCanvas);
        centerCamera();

        // ── Movement & Controls ──
        const keys = {};
        window.addEventListener('keydown', (e) => {
            if (['input', 'textarea', 'select'].includes(document.activeElement.tagName.toLowerCase())) return;
            const k = e.key.toLowerCase();
            if (['w', 'a', 's', 'd', 'arrowup', 'arrowleft', 'arrowdown', 'arrowright'].includes(k)) keys[k] = true;
        });
        window.addEventListener('keyup', (e) => {
            const k = e.key.toLowerCase();
            if (keys[k] !== undefined) keys[k] = false;
        });

        canvas.addEventListener('click', (e) => {
            const rect = canvas.getBoundingClientRect();
            const clickX = (e.clientX - rect.left - cameraOffset.x) / zoomLevel;
            const clickY = (e.clientY - rect.top - cameraOffset.y) / zoomLevel;

            // Check if clicking a locked room to knock
            const targetRoom = getCurrentRoom(clickX, clickY);
            const myRoom = getCurrentRoom(localAvatar.x, localAvatar.y);
            if (targetRoom && targetRoom !== myRoom && roomDoorStates.get(targetRoom.id)) {
                if (confirm(`🚪 ${targetRoom.name} {{ __("is locked. Would you like to knock?") }}`)) {
                    if (ws && ws.readyState === WebSocket.OPEN) {
                        ws.send(JSON.stringify({ type: 'room.knock', payload: { roomId: targetRoom.id, roomName: targetRoom.name } }));
                        showToast('⏳ {{ __("Knocked on door... waiting for occupant response.") }}');
                    }
                }
                return;
            }

            localAvatar.targetX = Math.max(10, Math.min(MAP_WIDTH_PX - 10, clickX));
            localAvatar.targetY = Math.max(10, Math.min(MAP_HEIGHT_PX - 10, clickY));
        });

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
                if (roomDoorStates.get(r.id)) {
                    const occupants = countRoomOccupants(r.id);
                    if (occupants === 0) {
                        roomDoorStates.set(r.id, false);
                        if (ws && ws.readyState === WebSocket.OPEN) {
                            ws.send(JSON.stringify({ type: 'room.door_toggle', payload: { roomId: r.id, isClosed: false } }));
                        }
                    }
                }
            });
        }

        function updateRoomPresence() {
            const r = getCurrentRoom(localAvatar.x, localAvatar.y);
            const statusPill = document.getElementById('room-status-pill');
            const roomNameEl = document.getElementById('current-room-name');
            const lockIcon = document.getElementById('lock-icon');
            const lockText = document.getElementById('lock-text');

            if (r) {
                statusPill.style.display = 'flex';
                roomNameEl.textContent = `🏢 ${r.name}`;
                const isLocked = !!roomDoorStates.get(r.id);
                lockIcon.textContent = isLocked ? '🔒' : '🔓';
                lockText.textContent = isLocked ? '{{ __("Unlock Door") }}' : '{{ __("Lock Door") }}';

                if (localAvatar.currentRoomId !== r.id) {
                    const prevId = localAvatar.currentRoomId;
                    localAvatar.currentRoomId = r.id;
                    if (ws && ws.readyState === WebSocket.OPEN) {
                        ws.send(JSON.stringify({ type: 'room.enter', payload: { roomId: r.id } }));
                    }
                    if (prevId) checkAutoUnlockEmptyRooms();
                }
            } else {
                statusPill.style.display = 'none';
                if (localAvatar.currentRoomId) {
                    const prevId = localAvatar.currentRoomId;
                    localAvatar.currentRoomId = null;
                    if (ws && ws.readyState === WebSocket.OPEN) {
                        ws.send(JSON.stringify({ type: 'room.leave', payload: { roomId: prevId } }));
                    }
                    checkAutoUnlockEmptyRooms();
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

        // ── Main Game & Render Loop ──
        function update() {
            let dx = 0, dy = 0;
            if (keys['w'] || keys['arrowup']) dy -= 1;
            if (keys['s'] || keys['arrowdown']) dy += 1;
            if (keys['a'] || keys['arrowleft']) dx -= 1;
            if (keys['d'] || keys['arrowright']) dx += 1;

            let nextX = localAvatar.x;
            let nextY = localAvatar.y;

            if (dx !== 0 || dy !== 0) {
                const len = Math.sqrt(dx * dx + dy * dy);
                nextX += (dx / len) * localAvatar.speed;
                nextY += (dy / len) * localAvatar.speed;
                localAvatar.targetX = nextX;
                localAvatar.targetY = nextY;
            } else {
                const diffX = localAvatar.targetX - localAvatar.x;
                const diffY = localAvatar.targetY - localAvatar.y;
                const dist = Math.sqrt(diffX * diffX + diffY * diffY);
                if (dist > 2) {
                    nextX += (diffX / dist) * localAvatar.speed;
                    nextY += (diffY / dist) * localAvatar.speed;
                }
            }

            // Door lock collision detection
            const currentR = getCurrentRoom(localAvatar.x, localAvatar.y);
            const targetR = getCurrentRoom(nextX, nextY);
            if (targetR && targetR !== currentR && roomDoorStates.get(targetR.id)) {
                nextX = localAvatar.x;
                nextY = localAvatar.y;
                localAvatar.targetX = localAvatar.x;
                localAvatar.targetY = localAvatar.y;
            }

            localAvatar.x = Math.max(10, Math.min(MAP_WIDTH_PX - 10, nextX));
            localAvatar.y = Math.max(10, Math.min(MAP_HEIGHT_PX - 10, nextY));

            updateRoomPresence();

            // Smooth remote avatar interpolation & Dynamic Spatial Audio Isolation
            const localRoom = getCurrentRoom(localAvatar.x, localAvatar.y);

            remoteAvatars.forEach(av => {
                av.x += (av.targetX - av.x) * 0.25;
                av.y += (av.targetY - av.y) * 0.25;

                // Spatial Audio Isolation Engine
                const audioEl = peerAudioElements.get(av.id);
                if (audioEl) {
                    const remoteRoom = getCurrentRoom(av.x, av.y);

                    if (localRoom) {
                        // Inside Room: Only hear occupants in the SAME room
                        if (!remoteRoom || remoteRoom.id !== localRoom.id) {
                            audioEl.volume = 0;
                        } else {
                            audioEl.volume = 1.0;
                        }
                    } else {
                        // In Open Area: Never hear occupants inside rooms; hear open area colleagues by distance
                        if (remoteRoom) {
                            audioEl.volume = 0;
                        } else {
                            const dist = Math.hypot(localAvatar.x - av.x, localAvatar.y - av.y);
                            const maxDist = 220;
                            if (dist > maxDist) {
                                audioEl.volume = 0;
                            } else {
                                const factor = 1 - (dist / maxDist);
                                audioEl.volume = Math.max(0, Math.min(1, factor * factor));
                            }
                        }
                    }
                }
            });

            broadcastPosition();
        }

        let lastPosSend = 0;
        function broadcastPosition() {
            const now = Date.now();
            if (now - lastPosSend > 45 && ws && ws.readyState === WebSocket.OPEN) {
                lastPosSend = now;
                ws.send(JSON.stringify({
                    type: 'position.update',
                    payload: { x: Math.round(localAvatar.x), y: Math.round(localAvatar.y), orientation: 'down' }
                }));
            }
        }

        function draw() {
            update();

            ctx.setTransform(1, 0, 0, 1, 0, 0);
            ctx.clearRect(0, 0, canvas.width, canvas.height);
            ctx.save();
            ctx.translate(cameraOffset.x, cameraOffset.y);
            ctx.scale(zoomLevel, zoomLevel);

            // 1. Draw Blueprint Background
            if (BLUEPRINT_IMAGE && BLUEPRINT_IMAGE.complete && BLUEPRINT_IMAGE.naturalWidth > 0) {
                ctx.fillStyle = '#ECE8DB';
                ctx.fillRect(0, 0, MAP_WIDTH_PX, MAP_HEIGHT_PX);
                ctx.drawImage(BLUEPRINT_IMAGE, 0, 0, MAP_WIDTH_PX, MAP_HEIGHT_PX);
            } else {
                ctx.fillStyle = '#0F1E16';
                ctx.fillRect(0, 0, MAP_WIDTH_PX, MAP_HEIGHT_PX);
            }

            // 2. Draw Rooms & Sleek Glass Tags
            rooms.forEach(r => {
                if (!r.bounds) return;
                const rx = r.bounds.x * TILE_SIZE;
                const ry = r.bounds.y * TILE_SIZE;
                const rw = r.bounds.width * TILE_SIZE;
                const rh = r.bounds.height * TILE_SIZE;
                const isLocked = !!roomDoorStates.get(r.id);

                ctx.strokeStyle = isLocked ? 'rgba(239, 68, 68, 0.6)' : 'rgba(79, 155, 95, 0.40)';
                ctx.lineWidth = 1.2;
                ctx.setLineDash([4, 4]);
                ctx.strokeRect(rx, ry, rw, rh);
                ctx.setLineDash([]);

                const labelText = `${isLocked ? '🔒 ' : '🏢 '}${r.name.split(' - ')[0]}`;
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

                ctx.fillStyle = isLocked ? '#FCA5A5' : '#F8FAFC';
                ctx.textAlign = 'left';
                ctx.textBaseline = 'middle';
                ctx.fillText(labelText, rx + 10, ry + 13);
            });

            // 3. Rectangular Acoustic Sound Isolation Aura for Active Room
            const activeRoom = getCurrentRoom(localAvatar.x, localAvatar.y);
            if (activeRoom && activeRoom.bounds) {
                const rx = activeRoom.bounds.x * TILE_SIZE;
                const ry = activeRoom.bounds.y * TILE_SIZE;
                const rw = activeRoom.bounds.width * TILE_SIZE;
                const rh = activeRoom.bounds.height * TILE_SIZE;

                ctx.fillStyle = 'rgba(79, 155, 95, 0.14)';
                if (ctx.roundRect) ctx.roundRect(rx - 4, ry - 4, rw + 8, rh + 8, 8);
                else ctx.rect(rx - 4, ry - 4, rw + 8, rh + 8);
                ctx.fill();

                ctx.strokeStyle = 'rgba(79, 155, 95, 0.75)';
                ctx.lineWidth = 2;
                ctx.setLineDash([6, 6]);
                if (ctx.roundRect) ctx.roundRect(rx - 2, ry - 2, rw + 4, rh + 4, 6);
                else ctx.rect(rx - 2, ry - 2, rw + 4, rh + 4);
                ctx.stroke();
                ctx.setLineDash([]);
            }

            // 4. Draw Remote Avatars (Clean 2.5D Figure without white circle)
            remoteAvatars.forEach(av => drawAvatar(av, false));

            // 5. Draw Local Avatar (Clean 2.5D Figure without white circle)
            drawAvatar(localAvatar, true);

            ctx.restore();
            requestAnimationFrame(draw);
        }

        // ── Clean 3D Figure Avatar Rendering (Without White Circle) ──
        function drawAvatar(av, isSelf) {
            const x = Number(av.x) || 400;
            const y = Number(av.y) || 400;
            const gender = isSelf ? userGender : (av.gender || 'male');
            const spriteImg = AVATAR_SPRITES[gender] || AVATAR_SPRITES.male;

            // 1. Spatial Audio Hearing Aura
            const auraRadius = isSelf ? 150 : 130;
            const auraGrad = ctx.createRadialGradient(x, y, 10, x, y, auraRadius);
            auraGrad.addColorStop(0, isSelf ? 'rgba(79, 155, 95, 0.20)' : 'rgba(59, 130, 246, 0.15)');
            auraGrad.addColorStop(1, 'rgba(0, 0, 0, 0)');
            ctx.fillStyle = auraGrad;
            ctx.beginPath();
            ctx.arc(x, y, auraRadius, 0, Math.PI * 2);
            ctx.fill();

            // 2. Soft Floor Ground Drop Shadow
            ctx.fillStyle = 'rgba(0, 0, 0, 0.35)';
            ctx.beginPath();
            ctx.ellipse(x, y + 18, 16, 6, 0, 0, Math.PI * 2);
            ctx.fill();

            // 3. Clean 2.5D Figure (Standing 3D Character)
            if (spriteImg && spriteImg.complete && spriteImg.naturalWidth > 0) {
                ctx.save();
                const figW = 42;
                const figH = 66;
                ctx.drawImage(spriteImg, x - figW / 2, y - figH + 18, figW, figH);
                ctx.restore();
            } else {
                ctx.fillStyle = isSelf ? '#10B981' : '#3B82F6';
                ctx.beginPath();
                ctx.arc(x, y, 16, 0, Math.PI * 2);
                ctx.fill();
            }

            // 4. Sleek Name Pill
            const displayName = isSelf ? `${av.name} ({{ __("You") }})` : av.name;
            ctx.font = 'bold 10px Cairo, Inter, sans-serif';
            const nameW = ctx.measureText(displayName).width + 16;
            ctx.fillStyle = 'rgba(15, 23, 42, 0.92)';
            if (ctx.roundRect) ctx.roundRect(x - nameW / 2, y + 22, nameW, 18, 6);
            else ctx.rect(x - nameW / 2, y + 22, nameW, 18);
            ctx.fill();

            ctx.strokeStyle = isSelf ? 'rgba(16, 185, 129, 0.6)' : 'rgba(255, 255, 255, 0.2)';
            ctx.lineWidth = 1;
            if (ctx.roundRect) ctx.roundRect(x - nameW / 2, y + 22, nameW, 18, 6);
            else ctx.rect(x - nameW / 2, y + 22, nameW, 18);
            ctx.stroke();

            ctx.fillStyle = isSelf ? '#6EE7B7' : '#F8FAFC';
            ctx.textAlign = 'center';
            ctx.textBaseline = 'middle';
            ctx.fillText(displayName, x, y + 31);
        }

        // ── WebSocket Realtime Connection & Presence Protocol ──
        let ws = null;
        let wsReconnectTimer = null;

        function connectWebSocket() {
            let wsUrl;
            if (CONFIG.wsUrl && !CONFIG.wsUrl.includes('127.0.0.1') && !CONFIG.wsUrl.includes('localhost')) {
                wsUrl = `${CONFIG.wsUrl}${CONFIG.wsUrl.includes('?') ? '&' : '?'}token=${CONFIG.token}`;
            } else if (window.location.protocol === 'https:') {
                wsUrl = `wss://${window.location.host}/ws?token=${CONFIG.token}`;
            } else {
                wsUrl = `ws://${window.location.hostname || '127.0.0.1'}:8080?token=${CONFIG.token}`;
            }

            try {
                if (ws) {
                    try { ws.close(); } catch(e) {}
                }
                ws = new WebSocket(wsUrl);
                ws.onopen = () => {
                    console.log('⚡ WebSocket Connected successfully via:', wsUrl);
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

                    // Heartbeat ping interval to keep connection alive even when tab is backgrounded
                    if (window._wsPingTimer) clearInterval(window._wsPingTimer);
                    window._wsPingTimer = setInterval(() => {
                        if (ws && ws.readyState === WebSocket.OPEN) {
                            ws.send(JSON.stringify({ type: 'status.update', payload: { status: 'online' } }));
                        }
                    }, 10000);
                };

                ws.onclose = (ev) => {
                    console.log('⚠️ WebSocket disconnected. Reconnecting in 3s...', ev);
                    if (!wsReconnectTimer) {
                        wsReconnectTimer = setTimeout(() => connectWebSocket(), 3000);
                    }
                };

                ws.onerror = (err) => {
                    console.error('WebSocket Error:', err);
                };

                ws.onmessage = (e) => {
                    try {
                        const data = JSON.parse(e.data);

                        // 1. Welcome packet with current map occupants
                        if (data.type === 'welcome' && data.payload?.occupants) {
                            data.payload.occupants.forEach(occ => {
                                if (occ.userId && occ.userId !== localAvatar.id) {
                                    remoteAvatars.set(occ.userId, {
                                        id: occ.userId,
                                        name: occ.name || 'Member',
                                        isGuest: !!occ.isGuest || (occ.name && occ.name.includes('(Guest)')),
                                        x: occ.position?.x || 500,
                                        y: occ.position?.y || 500,
                                        targetX: occ.position?.x || 500,
                                        targetY: occ.position?.y || 500,
                                        gender: occ.gender || 'male'
                                    });
                                    initiatePeerConnection(occ.userId, true);
                                }
                            });
                            updateOccupantsCounter();
                        }

                        // 2. User joined the map
                        else if (data.type === 'user.joined' && data.payload) {
                            const u = data.payload;
                            if (u.userId && u.userId !== localAvatar.id) {
                                remoteAvatars.set(u.userId, {
                                    id: u.userId,
                                    name: u.name || 'Member',
                                    isGuest: !!u.isGuest || (u.name && u.name.includes('(Guest)')),
                                    x: u.position?.x || 500,
                                    y: u.position?.y || 500,
                                    targetX: u.position?.x || 500,
                                    targetY: u.position?.y || 500,
                                    gender: u.gender || 'male'
                                });
                                initiatePeerConnection(u.userId, false);
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
                            closePeerConnection(leftId);
                            checkAutoUnlockEmptyRooms();
                            updateOccupantsCounter();
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
                                document.getElementById('knock-requester-name').textContent = `${data.payload.requesterName || 'A colleague'} is knocking on the door...`;
                                document.getElementById('knock-alert-modal').style.display = 'flex';
                            }
                        }

                        // 7. Knock Response Result
                        else if (data.type === 'room.knock_result' && data.payload) {
                            if (data.payload.approved) {
                                showToast(`🚪 {{ __("Access granted by") }} ${data.payload.responderName}!`);
                                roomDoorStates.set(data.payload.roomId, false);
                                updateRoomPresence();
                            } else {
                                showToast(`🚫 {{ __("Access denied by occupant.") }}`);
                            }
                        }

                        // 8. Chat Message
                        else if (data.type === 'chat.message' && data.payload) {
                            appendChatMessage({
                                senderName: data.payload.senderName,
                                text: data.payload.body,
                                time: new Date(data.payload.timestamp).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' }),
                                file: null
                            });
                        }

                        // 9. WebRTC Signaling Dispatch
                        else if (data.type === 'webrtc.signal' && data.payload) {
                            handleIncomingWebRTCSignal(data.payload);
                        }

                        // 10. Remote Peer Media State Updated (Cam / Mic toggled)
                        else if (data.type === 'media.state_updated' && data.payload) {
                            const { userId, camActive, micActive } = data.payload;
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
                        }

                        // 11. Presentation started/stopped
                        else if (data.type === 'presentation.started' && data.payload) {
                            showToast(`🖥️ ${data.payload.presenterName || 'Colleague'} {{ __("started screen presentation") }}`);
                            const btn = document.getElementById('btn-chat-focus-screen');
                            if (btn) btn.style.display = 'inline-flex';
                        }
                        else if (data.type === 'presentation.stopped' || data.type === 'presentation.stop') {
                            const pId = data.payload?.presenterId;
                            if (pId) {
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

        function updateOccupantsCounter() {
            const total = 1 + remoteAvatars.size;
            const counterEl = document.getElementById('occupants-counter');
            if (counterEl) {
                counterEl.textContent = `${total} {{ __("Online") }}`;
            }
        }

        function openOccupantsModal() {
            const modal = document.getElementById('occupants-modal');
            const list = document.getElementById('occupants-list');
            if (!modal || !list) return;

            const occupants = [
                {
                    id: localAvatar.id,
                    name: `${localAvatar.name} ({{ __("You") }})`,
                    gender: userGender,
                    isGuest: localAvatar.isGuest,
                    isSelf: true
                },
                ...Array.from(remoteAvatars.values())
            ];

            list.innerHTML = occupants.map(occ => {
                const initials = (occ.name || 'U').substring(0, 2).toUpperCase();
                return `
                    <div style="display: flex; align-items: center; justify-content: space-between; padding: 10px 14px; background: var(--bg-input); border: 1px solid var(--border-color); border-radius: 12px;">
                        <div style="display: flex; align-items: center; gap: 10px;">
                            <div style="width: 32px; height: 32px; border-radius: 50%; background: ${occ.isSelf ? 'var(--brand-primary)' : 'var(--brand-accent)'}; color: white; display: flex; align-items: center; justify-content: center; font-size: 11px; font-weight: 800;">
                                ${initials}
                            </div>
                            <div>
                                <div style="font-size: 13px; font-weight: 800; color: var(--text-primary);">${escapeHtml(occ.name)}</div>
                                <div style="font-size: 11px; color: var(--text-muted);">${occ.isGuest ? '🔗 {{ __("Guest Access") }}' : '🏢 {{ __("Team Member") }}'} • ${occ.gender === 'female' ? '👩 Female' : '👨 Male'}</div>
                            </div>
                        </div>
                        <span class="live-dot" style="width: 8px; height: 8px;" title="Online"></span>
                    </div>
                `;
            }).join('');

            modal.style.display = 'flex';
        }

        function closeOccupantsModal() {
            const modal = document.getElementById('occupants-modal');
            if (modal) modal.style.display = 'none';
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

        // ── WebRTC Multi-Peer Mesh Media Engine ──
        function initiatePeerConnection(targetUserId, isInitiator) {
            if (peerConnections.has(targetUserId)) {
                return peerConnections.get(targetUserId);
            }

            const pc = new RTCPeerConnection(RTC_CONFIG);
            peerConnections.set(targetUserId, pc);
            if (!pendingIceCandidates.has(targetUserId)) {
                pendingIceCandidates.set(targetUserId, []);
            }

            // Add local mic/camera tracks
            if (localMediaStream) {
                localMediaStream.getTracks().forEach(track => {
                    try { pc.addTrack(track, localMediaStream); } catch(e) {}
                });
            }
            // Add screen share tracks
            if (screenStream) {
                screenStream.getTracks().forEach(track => {
                    try { pc.addTrack(track, screenStream); } catch(e) {}
                });
            }

            pc.onicecandidate = (event) => {
                if (event.candidate && ws && ws.readyState === WebSocket.OPEN) {
                    ws.send(JSON.stringify({
                        type: 'webrtc.signal',
                        payload: {
                            targetUserId: targetUserId,
                            signal: { type: 'candidate', candidate: event.candidate }
                        }
                    }));
                }
            };

            pc.ontrack = (event) => {
                const stream = event.streams[0] || new MediaStream([event.track]);

                if (event.track.kind === 'audio') {
                    let audioEl = peerAudioElements.get(targetUserId);
                    if (!audioEl) {
                        audioEl = document.createElement('audio');
                        audioEl.autoplay = true;
                        document.body.appendChild(audioEl);
                        peerAudioElements.set(targetUserId, audioEl);
                    }
                    audioEl.srcObject = stream;
                    audioEl.play().catch(()=>{});
                } else if (event.track.kind === 'video') {
                    let videoCard = peerVideoCards.get(targetUserId);
                    const av = remoteAvatars.get(targetUserId);
                    const presenterName = av ? av.name : 'Colleague';

                    if (!videoCard) {
                        videoCard = document.createElement('div');
                        videoCard.id = `peer-video-${targetUserId}`;
                        videoCard.className = 'video-card size-medium';
                        videoCard.innerHTML = `
                            <div class="video-card-topbar">
                                <div class="video-card-title">
                                    <span class="live-dot"></span>
                                    <span class="user-title">🖥️ ${presenterName}</span>
                                </div>
                                <div class="video-card-actions">
                                    <button class="v-btn" id="vbtn-sm-${targetUserId}" onclick="resizeVideoCard('${targetUserId}', 'small')" title="{{ __('Small View (عرض صغير)') }}">📱</button>
                                    <button class="v-btn active" id="vbtn-med-${targetUserId}" onclick="resizeVideoCard('${targetUserId}', 'medium')" title="{{ __('Medium View (عرض متوسط)') }}">💻</button>
                                    <button class="v-btn" id="vbtn-lg-${targetUserId}" onclick="resizeVideoCard('${targetUserId}', 'large')" title="{{ __('Theater / Large (عرض كبير)') }}">📺</button>
                                    <button class="v-btn" onclick="toggleFullscreenVideo('${targetUserId}')" title="{{ __('Full Screen (شاشة كاملة)') }}">⛶</button>
                                    <button class="v-btn" onclick="togglePipVideo('${targetUserId}')" title="{{ __('Picture in Picture') }}">🗖</button>
                                    <button class="v-btn" onclick="toggleCollapseVideo('${targetUserId}')" title="{{ __('Minimize (تصغير)') }}">➖</button>
                                </div>
                            </div>
                            <div class="video-wrapper">
                                <video autoplay playsinline></video>
                            </div>
                        `;
                        const videoEl = videoCard.querySelector('video');
                        videoEl.srcObject = stream;
                        videoEl.play().catch(()=>{});

                        document.getElementById('video-grid').appendChild(videoCard);
                        peerVideoCards.set(targetUserId, videoCard);

                        const chatBtn = document.getElementById('btn-chat-focus-screen');
                        if (chatBtn) chatBtn.style.display = 'inline-flex';
                    } else {
                        const videoEl = videoCard.querySelector('video');
                        if (videoEl) {
                            videoEl.srcObject = stream;
                            videoEl.play().catch(()=>{});
                        }
                    }
                }
            };

            if (isInitiator) {
                createAndSendOffer(targetUserId);
            }

            return pc;
        }

        async function createAndSendOffer(targetUserId) {
            const pc = peerConnections.get(targetUserId);
            if (!pc) return;
            try {
                const offer = await pc.createOffer({ offerToReceiveAudio: true, offerToReceiveVideo: true });
                await pc.setLocalDescription(offer);
                if (ws && ws.readyState === WebSocket.OPEN) {
                    ws.send(JSON.stringify({
                        type: 'webrtc.signal',
                        payload: { targetUserId: targetUserId, signal: offer }
                    }));
                }
            } catch(err) {
                console.error('[WebRTC] Error creating offer for', targetUserId, err);
            }
        }

        async function handleIncomingWebRTCSignal(payload) {
            const senderUserId = payload.senderUserId;
            const signal = payload.signal;
            if (!senderUserId || !signal) return;

            let pc = peerConnections.get(senderUserId);
            if (!pc) {
                pc = initiatePeerConnection(senderUserId, false);
            }

            try {
                if (signal.type === 'offer') {
                    await pc.setRemoteDescription(new RTCSessionDescription(signal));

                    // Drain any queued ICE candidates
                    const queued = pendingIceCandidates.get(senderUserId) || [];
                    while (queued.length > 0) {
                        const cand = queued.shift();
                        try { await pc.addIceCandidate(new RTCIceCandidate(cand)); } catch(e) {}
                    }

                    const answer = await pc.createAnswer();
                    await pc.setLocalDescription(answer);
                    if (ws && ws.readyState === WebSocket.OPEN) {
                        ws.send(JSON.stringify({
                            type: 'webrtc.signal',
                            payload: { targetUserId: senderUserId, signal: answer }
                        }));
                    }
                } else if (signal.type === 'answer') {
                    await pc.setRemoteDescription(new RTCSessionDescription(signal));

                    // Drain any queued ICE candidates
                    const queued = pendingIceCandidates.get(senderUserId) || [];
                    while (queued.length > 0) {
                        const cand = queued.shift();
                        try { await pc.addIceCandidate(new RTCIceCandidate(cand)); } catch(e) {}
                    }
                } else if (signal.type === 'candidate' && signal.candidate) {
                    if (pc.remoteDescription && pc.remoteDescription.type) {
                        await pc.addIceCandidate(new RTCIceCandidate(signal.candidate));
                    } else {
                        if (!pendingIceCandidates.has(senderUserId)) {
                            pendingIceCandidates.set(senderUserId, []);
                        }
                        pendingIceCandidates.get(senderUserId).push(signal.candidate);
                    }
                }
            } catch(err) {
                console.error('[WebRTC] Signal handling error:', err);
            }
        }

        function closePeerConnection(targetUserId) {
            const pc = peerConnections.get(targetUserId);
            if (pc) {
                try { pc.close(); } catch(e) {}
                peerConnections.delete(targetUserId);
            }
            pendingIceCandidates.delete(targetUserId);

            const audio = peerAudioElements.get(targetUserId);
            if (audio) {
                audio.remove();
                peerAudioElements.delete(targetUserId);
            }
            const card = peerVideoCards.get(targetUserId);
            if (card) {
                card.remove();
                peerVideoCards.delete(targetUserId);
            }
            if (peerVideoCards.size === 0) {
                const chatBtn = document.getElementById('btn-chat-focus-screen');
                if (chatBtn) chatBtn.style.display = 'none';
            }
        }

        function updateTracksInAllPeerConnections() {
            peerConnections.forEach((pc, targetUserId) => {
                try {
                    const senders = pc.getSenders();
                    senders.forEach(s => {
                        try { pc.removeTrack(s); } catch(e) {}
                    });
                    if (localMediaStream) {
                        localMediaStream.getTracks().forEach(t => {
                            try { pc.addTrack(t, localMediaStream); } catch(e) {}
                        });
                    }
                    if (screenStream) {
                        screenStream.getTracks().forEach(t => {
                            try { pc.addTrack(t, screenStream); } catch(e) {}
                        });
                    }
                    createAndSendOffer(targetUserId);
                } catch(e) {
                    console.error('[WebRTC] Update tracks error for', targetUserId, e);
                }
            });
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

        // ── Camera, Microphone Media Toggling & Browser Permission Engine ──
        async function toggleMicrophone() {
            if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
                showToast('⚠️ {{ __("Microphone requires HTTPS or localhost.") }}');
                return;
            }

            try {
                if (!micActive) {
                    let audioTrack = null;
                    if (localMediaStream) {
                        const existingTracks = localMediaStream.getAudioTracks();
                        if (existingTracks.length > 0) {
                            audioTrack = existingTracks[0];
                            audioTrack.enabled = true;
                        }
                    }

                    if (!audioTrack) {
                        const stream = await navigator.mediaDevices.getUserMedia({ audio: true });
                        audioTrack = stream.getAudioTracks()[0];
                        if (localMediaStream) {
                            localMediaStream.addTrack(audioTrack);
                        } else {
                            localMediaStream = stream;
                        }
                    }

                    micActive = true;
                    showToast('🎙️ {{ __("Microphone active") }}');
                } else {
                    if (localMediaStream) {
                        localMediaStream.getAudioTracks().forEach(t => t.enabled = false);
                    }
                    micActive = false;
                    showToast('🔇 {{ __("Microphone muted") }}');
                }

                if (ws && ws.readyState === WebSocket.OPEN) {
                    ws.send(JSON.stringify({ type: 'media.state', payload: { camActive: camActive, micActive: micActive } }));
                }

                updateTracksInAllPeerConnections();
            } catch(e) {
                console.error('[Audio] getUserMedia error:', e);
                micActive = false;
                if (e.name === 'NotAllowedError' || e.name === 'PermissionDeniedError') {
                    showToast('🚫 {{ __("Microphone permission denied by browser. Please allow microphone access in your browser settings.") }}');
                } else if (e.name === 'NotFoundError' || e.name === 'DevicesNotFoundError') {
                    showToast('⚠️ {{ __("No microphone found on this device.") }}');
                } else {
                    showToast(`❌ {{ __("Microphone error:") }} ${e.message || e.name}`);
                }
            }

            const btn = document.getElementById('btn-mic');
            btn.classList.toggle('muted', !micActive);
            btn.classList.toggle('active', micActive);
            document.getElementById('mic-icon').textContent = micActive ? '🎙️' : '🔇';
            document.getElementById('mic-text').textContent = micActive ? '{{ __("Mic On") }}' : '{{ __("Mic Off") }}';
        }

        async function toggleCamera() {
            if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
                showToast('⚠️ {{ __("Camera requires HTTPS or localhost.") }}');
                return;
            }

            try {
                if (!camActive) {
                    let videoTrack = null;
                    if (localMediaStream) {
                        const existingTracks = localMediaStream.getVideoTracks();
                        if (existingTracks.length > 0) {
                            videoTrack = existingTracks[0];
                            videoTrack.enabled = true;
                        }
                    }

                    if (!videoTrack) {
                        const stream = await navigator.mediaDevices.getUserMedia({
                            video: { width: { ideal: 640 }, height: { ideal: 480 } }
                        });
                        videoTrack = stream.getVideoTracks()[0];
                        if (localMediaStream) {
                            localMediaStream.addTrack(videoTrack);
                        } else {
                            localMediaStream = stream;
                        }
                    }

                    camActive = true;
                    const videoElem = document.getElementById('local-video-elem');
                    videoElem.srcObject = localMediaStream;
                    document.getElementById('local-video-card').style.display = 'flex';
                    showToast('📹 {{ __("Camera active") }}');
                    if (ws && ws.readyState === WebSocket.OPEN) {
                        ws.send(JSON.stringify({ type: 'media.state', payload: { camActive: true, micActive: micActive } }));
                    }
                } else {
                    if (localMediaStream) {
                        localMediaStream.getVideoTracks().forEach(t => {
                            t.enabled = false;
                            t.stop();
                            localMediaStream.removeTrack(t);
                        });
                    }
                    camActive = false;
                    document.getElementById('local-video-card').style.display = 'none';
                    showToast('📷 {{ __("Camera turned off") }}');
                    if (ws && ws.readyState === WebSocket.OPEN) {
                        ws.send(JSON.stringify({ type: 'media.state', payload: { camActive: false, micActive: micActive } }));
                    }
                }

                updateTracksInAllPeerConnections();
            } catch(e) {
                console.error('[Video] getUserMedia error:', e);
                camActive = false;
                if (e.name === 'NotAllowedError' || e.name === 'PermissionDeniedError') {
                    showToast('🚫 {{ __("Camera permission denied by browser.") }}');
                } else if (e.name === 'NotFoundError' || e.name === 'DevicesNotFoundError') {
                    showToast('⚠️ {{ __("No camera found on this device.") }}');
                } else {
                    showToast(`❌ {{ __("Camera error:") }} ${e.message || e.name}`);
                }
            }

            const btn = document.getElementById('btn-cam');
            btn.classList.toggle('muted', !camActive);
            btn.classList.toggle('active', camActive);
            document.getElementById('cam-icon').textContent = camActive ? '📹' : '📷';
            document.getElementById('cam-text').textContent = camActive ? '{{ __("Cam On") }}' : '{{ __("Cam Off") }}';
        }

        // ── Screen Sharing Across All Peers ──
        async function toggleScreenShare() {
            if (screenStream) {
                screenStream.getTracks().forEach(t => t.stop());
                screenStream = null;
                updateTracksInAllPeerConnections();
                if (ws && ws.readyState === WebSocket.OPEN) {
                    ws.send(JSON.stringify({ type: 'presentation.stop', payload: {} }));
                }
                document.getElementById('btn-screen').classList.remove('active');
                document.getElementById('screen-text').textContent = '{{ __("Share") }}';
                showToast('⏹️ {{ __("Screen share stopped") }}');
                return;
            }

            try {
                screenStream = await navigator.mediaDevices.getDisplayMedia({ video: true, audio: true });
                
                // Handle when user stops sharing from browser toolbar
                screenStream.getVideoTracks()[0].onended = () => {
                    if (screenStream) {
                        screenStream = null;
                        updateTracksInAllPeerConnections();
                        if (ws && ws.readyState === WebSocket.OPEN) {
                            ws.send(JSON.stringify({ type: 'presentation.stop', payload: {} }));
                        }
                        document.getElementById('btn-screen').classList.remove('active');
                        document.getElementById('screen-text').textContent = '{{ __("Share") }}';
                        showToast('⏹️ {{ __("Screen share stopped") }}');
                    }
                };

                updateTracksInAllPeerConnections();

                if (ws && ws.readyState === WebSocket.OPEN) {
                    ws.send(JSON.stringify({ type: 'presentation.start', payload: {} }));
                }

                document.getElementById('btn-screen').classList.add('active');
                document.getElementById('screen-text').textContent = '{{ __("Sharing") }}';
                showToast('🖥️ {{ __("Screen sharing active") }}');
            } catch(e) {
                console.error('[Screen] getDisplayMedia error:', e);
                document.getElementById('btn-screen').classList.remove('active');
                document.getElementById('screen-text').textContent = '{{ __("Share") }}';
                if (e.name !== 'NotAllowedError') {
                    showToast('❌ {{ __("Could not start screen share") }}');
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
                document.getElementById('rec-icon').textContent = '⏹️';
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
                document.getElementById('rec-icon').textContent = '⏺️';
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

        // ── Chat & File Sharing ──
        let chatScope = 'room';
        function toggleChatDrawer() {
            const drawer = document.getElementById('chat-drawer');
            drawer.style.display = drawer.style.display === 'flex' ? 'none' : 'flex';
        }
        function switchChatScope(scope) {
            chatScope = scope;
            document.getElementById('chat-tab-room').classList.toggle('active', scope === 'room');
            document.getElementById('chat-tab-global').classList.toggle('active', scope === 'global');
        }

        function sendChatMessage() {
            const inp = document.getElementById('chat-msg-input');
            const text = inp.value.trim();
            if (!text) return;
            inp.value = '';

            const myRoom = getCurrentRoom(localAvatar.x, localAvatar.y);
            const msgPayload = {
                senderName: localAvatar.name,
                senderId: localAvatar.id,
                text: text,
                time: new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' }),
                scope: chatScope,
                roomId: myRoom ? myRoom.id : null,
                file: null
            };

            appendChatMessage(msgPayload, true);
            if (ws && ws.readyState === WebSocket.OPEN) {
                ws.send(JSON.stringify({
                    type: 'chat.send',
                    payload: { channelId: 'general', body: text }
                }));
            }
        }

        async function handleChatFileUpload(input) {
            if (!input.files || !input.files[0]) return;
            const formData = new FormData();
            formData.append('file', input.files[0]);
            showToast('⏳ {{ __("Uploading attachment...") }}');

            try {
                const res = await fetch(`/organizations/${CONFIG.org.id}/chat/upload`, {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': CONFIG.csrf, 'Accept': 'application/json' },
                    credentials: 'same-origin',
                    body: formData
                });
                const fileData = await res.json();
                if (res.ok) {
                    const myRoom = getCurrentRoom(localAvatar.x, localAvatar.y);
                    const msgPayload = {
                        senderName: localAvatar.name,
                        senderId: localAvatar.id,
                        text: `📎 Shared file: ${fileData.name}`,
                        time: new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' }),
                        scope: chatScope,
                        roomId: myRoom ? myRoom.id : null,
                        file: fileData
                    };
                    appendChatMessage(msgPayload, true);
                    if (ws && ws.readyState === WebSocket.OPEN) {
                        ws.send(JSON.stringify({
                            type: 'chat.send',
                            payload: { channelId: 'general', body: `📎 ${fileData.name} - ${fileData.url}` }
                        }));
                    }
                    showToast('✅ {{ __("File shared in chat!") }}');
                }
            } catch(e) {
                showToast('❌ {{ __("Failed to upload file") }}');
            }
        }

        function appendChatMessage(msg, isSelf = false) {
            const container = document.getElementById('chat-messages-container');
            const el = document.createElement('div');
            el.className = `msg-bubble ${isSelf ? 'self' : ''}`;

            let fileHtml = '';
            if (msg.file && msg.file.url) {
                fileHtml = `<div style="margin-top:4px;"><a href="${msg.file.url}" target="_blank" download style="color:var(--brand-primary); font-weight:800; text-decoration:none;">💾 ${msg.file.name}</a></div>`;
            }

            el.innerHTML = `
                <div class="msg-meta"><span>${msg.senderName || 'Member'}</span> <span>${msg.time || ''}</span></div>
                <span>${msg.text || ''}</span>
                ${fileHtml}
            `;
            container.appendChild(el);
            container.scrollTop = container.scrollHeight;
        }

        // ── Rich Collaborative Whiteboard Engine ──
        let wbCanvas, wbCtx;
        let wbTool = 'pen';
        let wbColor = '#0F172A';
        let wbDrawing = false;
        let wbStartX = 0, wbStartY = 0;
        let wbHistory = [];

        function setWbTool(tool) {
            wbTool = tool;
            document.querySelectorAll('.wb-tool-btn').forEach(btn => btn.classList.remove('active'));
            document.getElementById(`wb-tool-${tool}`)?.classList.add('active');
        }
        function setWbColor(color) {
            wbColor = color;
            document.querySelectorAll('.color-dot').forEach(d => d.classList.toggle('active', d.style.background === color));
        }

        function openWhiteboardModal() {
            document.getElementById('whiteboard-modal').style.display = 'flex';
            wbCanvas = document.getElementById('wb-canvas');
            wbCtx = wbCanvas.getContext('2d');
            wbCanvas.width = wbCanvas.parentElement.clientWidth;
            wbCanvas.height = wbCanvas.parentElement.clientHeight;
            setupWhiteboardEvents();
        }
        function closeWhiteboardModal() { document.getElementById('whiteboard-modal').style.display = 'none'; }

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
                        saveWbState();
                    }
                    wbDrawing = false;
                } else if (wbTool === 'note') {
                    wbCtx.fillStyle = '#FEF08A';
                    wbCtx.fillRect(wbStartX, wbStartY, 140, 100);
                    wbCtx.strokeRect(wbStartX, wbStartY, 140, 100);
                    wbCtx.fillStyle = '#0F172A';
                    wbCtx.font = '12px Cairo, sans-serif';
                    wbCtx.fillText('📌 Note', wbStartX + 10, wbStartY + 20);
                    saveWbState();
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

                if (wbTool === 'rect') {
                    wbCtx.strokeRect(wbStartX, wbStartY, x - wbStartX, y - wbStartY);
                } else if (wbTool === 'circle') {
                    const rad = Math.hypot(x - wbStartX, y - wbStartY);
                    wbCtx.beginPath();
                    wbCtx.arc(wbStartX, wbStartY, rad, 0, Math.PI * 2);
                    wbCtx.stroke();
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
                saveWbState();
            };
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
            localAvatar.targetX = av.x + 30;
            localAvatar.targetY = av.y;
            closeOccupantsModal();
            showToast(`🎯 {{ __('Moving to') }} ${av.name}...`);
        }

        // ── Avatar Character Modal ──
        function openAvatarModal() {
            document.getElementById('avatar-modal').style.display = 'flex';
            document.getElementById('pick-male').classList.toggle('selected', userGender === 'male');
            document.getElementById('pick-female').classList.toggle('selected', userGender === 'female');
        }
        function closeAvatarModal() { document.getElementById('avatar-modal').style.display = 'none'; }
        function setAvatarGender(g) {
            userGender = g;
            localAvatar.gender = g;
            localStorage.setItem('vw_gender', g);
            if (ws && ws.readyState === WebSocket.OPEN) {
                ws.send(JSON.stringify({ type: 'avatar.update', payload: { gender: g } }));
            }
            closeAvatarModal();
            showToast(`🎭 {{ __('Avatar set to') }} ${g === 'female' ? '👩 Female' : '👨 Male'}`);
        }

        function toggleAppTheme() {
            const cur = document.documentElement.getAttribute('data-theme') || 'dark';
            const next = cur === 'dark' ? 'light' : 'dark';
            document.documentElement.setAttribute('data-theme', next);
            document.getElementById('theme-icon').textContent = next === 'dark' ? '☀️' : '🌙';
        }

        function showToast(msg) {
            const t = document.getElementById('toast-bubble');
            t.textContent = msg;
            t.style.display = 'block';
            setTimeout(() => { t.style.display = 'none'; }, 3200);
        }

        // Start animation loop
        requestAnimationFrame(draw);
    </script>
</body>
</html>
