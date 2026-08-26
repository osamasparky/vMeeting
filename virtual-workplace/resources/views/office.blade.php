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
            position: absolute;
            inset: 0;
            width: 100vw;
            height: 100vh;
            background: radial-gradient(circle at center, #0B1C13 0%, #050B08 100%);
            overflow: hidden;
            z-index: 1;
        }
        #office-canvas {
            display: block;
            width: 100vw;
            height: 100vh;
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

    <!-- ── Top Floating Overlay Bar ── -->
    <div class="top-bar-overlay">
        <div class="glass-pill">
            @if(empty($user->is_guest))
            <a href="{{ route('dashboard') }}" class="action-link-btn" title="{{ __('Back to Dashboard (الخروج إلى لوحة التحكم)') }}">
                <span>🏠</span> <span>{{ __('Dashboard') }}</span>
            </a>
            @endif

            @if(session('superadmin_impersonator_id'))
            <form method="POST" action="{{ route('impersonate.leave') }}" style="margin: 0; display: inline-flex;">
                @csrf
                <button type="submit" class="action-link-btn" style="background: rgba(37, 99, 235, 0.25); color: #93C5FD; border: 1px solid rgba(147, 197, 253, 0.45); font-weight: 800;" title="{{ __('Return to Super Admin (الرجوع للوحة التحكم)') }}">
                    <span>🛡️</span> <span>{{ __('Super Admin') }}</span>
                </button>
            </form>
            @endif

            <!-- Office / Branch Switcher (Internal Team Members Only) -->
            @if(isset($userAllowedOffices) && $userAllowedOffices->count() > 1 && empty($user->is_guest))
            <div style="position: relative; display: inline-block;">
                <button type="button" onclick="toggleOfficeDropdown(event)" class="action-link-btn" style="background: rgba(36, 92, 58, 0.35); color: #86EFAC; border: 1px solid rgba(134, 239, 172, 0.45); font-weight: 800; display: flex; align-items: center; gap: 6px;" title="{{ __('Switch Office Branch (تغيير الفرع)') }}">
                    <span>🏢</span>
                    <span>{{ $floor->name }}</span>
                    <span style="font-size: 8px;">▼</span>
                </button>
                <div id="office-switcher-dropdown" style="display: none; position: absolute; top: calc(100% + 8px); inset-inline-start: 0; min-width: 250px; background: rgba(18, 28, 22, 0.96); backdrop-filter: blur(18px); border: 1px solid rgba(255,255,255,0.18); border-radius: 12px; box-shadow: 0 16px 36px rgba(0,0,0,0.6); padding: 6px; z-index: 100000;">
                    <div style="font-size: 10px; font-weight: 900; text-transform: uppercase; letter-spacing: 0.5px; color: rgba(255,255,255,0.5); padding: 6px 10px; border-bottom: 1px solid rgba(255,255,255,0.08); margin-bottom: 4px;">
                        🏢 {{ __('Office Branches (فروع الشركة)') }}
                    </div>
                    @foreach($userAllowedOffices as $off)
                    @php
                        $offMap = $off->activeMap ?: $off->maps->first();
                        $offMapId = $offMap ? $offMap->id : '';
                    @endphp
                    <a href="{{ route('office', ['office' => $off->id]) }}" style="display: flex; align-items: center; justify-content: space-between; gap: 8px; padding: 8px 12px; border-radius: 8px; text-decoration: none; color: {{ $off->id === $floor->id ? '#86EFAC' : '#E2E8F0' }}; background: {{ $off->id === $floor->id ? 'rgba(36, 92, 58, 0.45)' : 'transparent' }}; font-weight: 700; font-size: 12px; transition: background 0.15s ease;">
                        <div style="display: flex; align-items: center; gap: 8px;">
                            <span>🏢</span>
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
                <span class="guest-badge" style="font-weight: 800;">
                    🛡️ {{ __('دخول ضيف') }} ({{ $user->name }})
                </span>
            @endif
        </div>

        @if(!empty($branchWarning))
            <div id="guest-branch-warning-banner" style="position: absolute; top: 65px; left: 50%; transform: translateX(-50%); background: rgba(214, 162, 58, 0.95); backdrop-filter: blur(12px); border: 1px solid rgba(255, 255, 255, 0.3); border-radius: 20px; padding: 8px 18px; color: #1E1B18; font-size: 12px; font-weight: 800; box-shadow: 0 10px 25px rgba(0,0,0,0.4); z-index: 99999; display: flex; align-items: center; gap: 8px; max-width: 90vw; pointer-events: auto;">
                <span>⚠️</span>
                <span>{{ $branchWarning }}</span>
                <button onclick="document.getElementById('guest-branch-warning-banner').remove()" style="background: none; border: none; font-size: 14px; font-weight: 900; cursor: pointer; color: #1E1B18; margin-inline-start: 6px;">✕</button>
            </div>
        @endif

        <div class="glass-pill" id="room-status-pill" style="display: none;">
            <span id="current-room-name" style="font-weight: 800; font-size: 12px; color: #34D399;">🏢 {{ __('غرفة الاجتماعات') }}</span>
            
            <button onclick="openRoomFilesModal()" id="btn-room-files" class="action-link-btn" style="padding: 4px 8px; font-size: 11px;">
                <span>📁</span> <span>{{ __('ملفات الغرفة') }}</span>
            </button>

            @if(empty($user->is_guest))
            <button onclick="toggleRoomDoorLock()" id="btn-lock-room" class="action-link-btn" style="padding: 4px 8px; font-size: 11px;">
                <span id="lock-icon">🔓</span> <span id="lock-text">{{ __('قفل الباب') }}</span>
            </button>
            @endif
        </div>

        <div class="glass-pill">
            <button onclick="openOccupantsModal()" class="action-link-btn" id="btn-occupants-pill" title="{{ __('المتواجدون في المكتب') }}">
                <span class="live-dot" style="width: 7px; height: 7px;"></span>
                <span>👥</span> <span id="occupants-counter">1 {{ __('متصل الآن') }}</span>
            </button>

            <button onclick="openDiagnosticsModal()" class="action-link-btn" id="btn-webrtc-quality-pill" title="{{ __('جودة الاتصال بالسيرفر') }}">
                <span id="webrtc-quality-dot" style="width: 7px; height: 7px; border-radius: 50%; background: #10B981; display: inline-block;"></span>
                <span id="webrtc-quality-text">{{ __('ممتاز') }}</span>
            </button>

            <button onclick="toggleChatDrawer()" class="action-link-btn" title="{{ __('المحادثة والمستندات') }}">
                <span>💬</span> <span>{{ __('المحادثة') }}</span>
            </button>

            <button onclick="toggleAppTheme()" class="action-link-btn" title="{{ __('تغيير مظهر الشاشة') }}">
                <span id="theme-icon">☀️</span>
            </button>

            @if(app()->getLocale() === 'ar')
                <a href="{{ route('lang.switch', 'en') }}" class="action-link-btn" title="English">🌐 EN</a>
            @else
                <a href="{{ route('lang.switch', 'ar') }}" class="action-link-btn" title="العربية">🌐 عربي</a>
            @endif

            @if(!empty($user) && in_array($user->role ?? 'member', ['superadmin', 'company_admin', 'manager']))
                <a href="{{ route('editor') }}" class="action-link-btn" style="color: var(--brand-primary); font-weight: 800;">
                    <span>🛠️</span> {{ __('محرر الخريطة') }}
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
                📹 {{ $user->name ?? 'You' }} ({{ __('أنت') }})
            </span>
            <button onclick="toggleCamera()" style="background:none; border:none; color:var(--text-muted); cursor:pointer; font-size:12px; line-height: 1;" title="{{ __('إيقاف الكاميرا') }}">✕</button>
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
            <strong style="font-size: 13px; display: flex; align-items: center; gap: 6px;">💬 {{ __('محادثة المكتب والغرف') }}</strong>
            <div style="display: flex; align-items: center; gap: 6px;">
                <button onclick="focusActiveScreenShare()" class="action-link-btn" id="btn-chat-focus-screen" style="display: none; padding: 3px 8px; font-size: 10px; color: #34D399; border-color: rgba(52, 211, 153, 0.4);" title="{{ __('عرض الشاشة المشاركة') }}">
                    🖥️ {{ __('الشاشة') }}
                </button>
                <button onclick="toggleChatDrawer()" style="background:none; border:none; color:var(--text-muted); font-size:16px; cursor:pointer;">✕</button>
            </div>
        </div>
        <div class="chat-tabs">
            <div class="chat-tab active" id="chat-tab-room" onclick="switchChatScope('room')">🏢 {{ __('شات الغرفة') }}</div>
            <div class="chat-tab" id="chat-tab-global" onclick="switchChatScope('global')">🌐 {{ __('العام') }}</div>
        </div>
        <div class="chat-messages" id="chat-messages-container">
            <div class="msg-bubble">
                <div class="msg-meta"><span>🤖 {{ __('المساعد الذكي') }}</span> <span>{{ date('H:i') }}</span></div>
                <span>{{ __('مرحبًا بك في المكتب الافتراضي! يمكنك استخدام الشات للتواصل ومشاركة الملفات والملاحظات مع فريقك.') }}</span>
            </div>
        </div>
        <div class="chat-input-bar">
            <input type="file" id="chat-file-input" style="display:none;" onchange="handleChatFileUpload(this)">
            <button onclick="document.getElementById('chat-file-input').click()" class="action-link-btn" style="padding: 6px 8px;" title="{{ __('إرفاق ملف') }}">📎</button>
            <input type="text" id="chat-msg-input" placeholder="{{ __('اكتب رسالتك هنا...') }}" class="styled-input" style="padding: 8px 10px; font-size: 12px;" onkeydown="if(event.key==='Enter') sendChatMessage()">
            <button onclick="sendChatMessage()" class="action-link-btn" style="background: var(--brand-primary); color: white; padding: 6px 12px;">➤</button>
        </div>
    </div>

    <!-- ── Bottom Floating Dock ── -->
    <div class="bottom-dock">
        <button class="dock-btn muted" id="btn-mic" onclick="toggleMicrophone()">
            <span id="mic-icon">🔇</span>
            <span id="mic-text">{{ __('كتم المايك') }}</span>
        </button>
        <button class="dock-btn muted" id="btn-cam" onclick="toggleCamera()">
            <span id="cam-icon">📷</span>
            <span id="cam-text">{{ __('إيقاف الكاميرا') }}</span>
        </button>
        <button class="dock-btn" id="btn-screen" onclick="toggleScreenShare()">
            <span id="screen-icon">🖥️</span>
            <span id="screen-text">{{ __('مشاركة الشاشة') }}</span>
        </button>

        <div class="dock-divider"></div>

        <button class="dock-btn" onclick="openMyTaskDrawer()" title="{{ __('قائمة مهامي وتتبع الوقت') }}">
            <span>📝</span>
            <span>{{ __('مهامي') }}</span>
        </button>
        <button class="dock-btn" onclick="openGuestInviteModal()">
            <span>⚡</span>
            <span>{{ __('دعوة ضيف') }}</span>
        </button>
        <button class="dock-btn" id="btn-react-dock" onclick="toggleReactionMenu(event)" title="{{ __('التفاعلات السريعة والفقاعات') }}">
            <span>😀</span>
            <span>{{ __('تفاعل') }}</span>
        </button>
        <button class="dock-btn" onclick="openWhiteboardModal()">
            <span>📋</span>
            <span>{{ __('السبورة') }}</span>
        </button>
        <button class="dock-btn" id="btn-record" onclick="toggleRecording()">
            <span id="rec-icon">⏺️</span>
            <span id="rec-text">{{ __('تسجيل') }}</span>
        </button>

        <div class="dock-divider"></div>

        <button class="dock-btn" id="btn-more-dock" onclick="toggleMoreMenu(event)" title="{{ __('المزيد من الأدوات والإعدادات') }}">
            <span style="font-size: 16px; font-weight: 900; letter-spacing: 2px;">•••</span>
            <span>{{ __('المزيد') }}</span>
        </button>
    </div>

    <!-- ── Floating More Tools & Settings Popover Menu ── -->
    <div id="floating-more-popover" style="display: none; position: absolute; bottom: 85px; left: 65%; transform: translateX(-50%); background: rgba(15, 23, 42, 0.96); backdrop-filter: blur(20px); border: 1px solid rgba(255,255,255,0.18); border-radius: 18px; padding: 8px; flex-direction: column; gap: 4px; box-shadow: 0 16px 36px rgba(0,0,0,0.6); z-index: 100000; min-width: 220px;">
        <button class="more-menu-item" onclick="toggleCameraGalleryModal(); closeMoreMenu();">
            <span>🎥</span> <span>{{ __('شبكة الكاميرات المباشرة') }}</span>
        </button>
        <button class="more-menu-item" onclick="openRecordingsGallery(); closeMoreMenu();">
            <span>📼</span> <span>{{ __('مكتبة التسجيلات') }}</span>
        </button>
    </div>

    <!-- ── Floating In-World Contextual Prompts & Menus ── -->
    <div id="furniture-sit-prompt" style="display: none; position: absolute; bottom: 85px; left: 50%; transform: translateX(-50%); background: rgba(16, 185, 129, 0.95); backdrop-filter: blur(14px); border: 1px solid rgba(255,255,255,0.4); border-radius: 24px; padding: 6px 18px; color: #FFFFFF; font-size: 12px; font-weight: 900; box-shadow: 0 10px 28px rgba(16,185,129,0.45); z-index: 9999; pointer-events: none; transition: opacity 0.2s ease;">
        <span>🪑 {{ __('Press') }} <kbd style="background: rgba(0,0,0,0.35); padding: 2px 7px; border-radius: 6px; font-family: monospace; font-size: 11px;">E</kbd> {{ __('to Sit at Desk / Table (الجلوس)') }}</span>
    </div>

    <div id="floating-reaction-popover" style="display: none; position: absolute; bottom: 85px; left: 50%; transform: translateX(-50%); background: rgba(15, 23, 42, 0.96); backdrop-filter: blur(20px); border: 1px solid rgba(255,255,255,0.18); border-radius: 32px; padding: 6px 14px; align-items: center; gap: 8px; box-shadow: 0 16px 36px rgba(0,0,0,0.6); z-index: 100000;">
        <button class="reaction-emoji-btn" onclick="sendEmojiReaction('👋')" title="Wave (تحية)">👋</button>
        <button class="reaction-emoji-btn" onclick="sendEmojiReaction('👍')" title="Thumbs Up (موافق)">👍</button>
        <button class="reaction-emoji-btn" onclick="sendEmojiReaction('☕')" title="Coffee Break (استراحة)">☕</button>
        <button class="reaction-emoji-btn" onclick="sendEmojiReaction('💡')" title="Idea (فكرة)">💡</button>
        <button class="reaction-emoji-btn" onclick="sendEmojiReaction('👏')" title="Applause (تصفيق)">👏</button>
        <button class="reaction-emoji-btn" onclick="sendEmojiReaction('🎯')" title="Focus Mode (تركيز)">🎯</button>
        <button class="reaction-emoji-btn" onclick="sendEmojiReaction('❓')" title="Question (سؤال)">❓</button>
        <button class="reaction-emoji-btn" onclick="sendEmojiReaction('🔥')" title="Great Work (رائع)">🔥</button>
    </div>

    <!-- ── Modals & Overlays ── -->

    <!-- 0a. Device Settings & Pre-Join Test Modal -->
    <div id="device-settings-modal" class="modal-overlay">
        <div class="modal-card" style="max-width: 540px;">
            <div class="modal-header">
                <div class="modal-title"><span>⚙️</span> {{ __('Audio & Video Device Settings') }}</div>
                <button onclick="closeDeviceSettingsModal()" style="background:none; border:none; color:var(--text-muted); font-size:20px; cursor:pointer;">✕</button>
            </div>
            
            <!-- Video Preview Box -->
            <div style="position: relative; width: 100%; height: 200px; background: #070F0A; border-radius: 12px; overflow: hidden; border: 1px solid var(--border-color); display: flex; align-items: center; justify-content: center; margin-bottom: 12px;">
                <video id="device-preview-video" autoplay playsinline muted style="width: 100%; height: 100%; object-fit: cover;"></video>
                <div id="device-no-preview" style="display: none; color: var(--text-muted); font-size: 12px; font-weight: 700;">📷 {{ __('Camera Preview Inactive') }}</div>
            </div>

            <!-- Mic Volume Level Meter -->
            <div style="margin-bottom: 14px;">
                <div style="display: flex; justify-content: space-between; font-size: 11px; font-weight: 800; color: var(--text-secondary); margin-bottom: 4px;">
                    <span>🎙️ {{ __('Microphone Input Test') }}</span>
                    <span id="mic-level-val">0%</span>
                </div>
                <div style="width: 100%; height: 8px; background: rgba(255,255,255,0.08); border-radius: 4px; overflow: hidden;">
                    <div id="mic-level-bar" style="width: 0%; height: 100%; background: #10B981; transition: width 0.08s ease;"></div>
                </div>
            </div>

            <!-- Selectors -->
            <div class="input-group">
                <label class="input-label">📹 {{ __('Camera Device') }}</label>
                <select class="styled-input" id="select-video-input" onchange="onCameraDeviceChanged(this.value)">
                    <option value="default">{{ __('Default Camera') }}</option>
                </select>
            </div>
            <div class="input-group">
                <label class="input-label">🎙️ {{ __('Microphone Device') }}</label>
                <select class="styled-input" id="select-audio-input" onchange="onMicDeviceChanged(this.value)">
                    <option value="default">{{ __('Default Microphone') }}</option>
                </select>
            </div>
            <div class="input-group">
                <label class="input-label">🔊 {{ __('Audio Output Speaker') }}</label>
                <select class="styled-input" id="select-audio-output" onchange="onSpeakerDeviceChanged(this.value)">
                    <option value="default">{{ __('Default Speaker') }}</option>
                </select>
            </div>

            <div style="display: flex; gap: 8px; margin-top: 8px;">
                <button onclick="closeDeviceSettingsModal()" class="action-link-btn" style="flex: 1; background: var(--brand-primary); color: white; justify-content: center; padding: 10px;">
                    ✓ {{ __('Done & Save Settings') }}
                </button>
            </div>
        </div>
    </div>

    <!-- 0b. WebRTC & Network Diagnostics Modal -->
    <div id="diagnostics-modal" class="modal-overlay">
        <div class="modal-card" style="max-width: 600px;">
            <div class="modal-header">
                <div class="modal-title"><span>🩺</span> {{ __('WebRTC & Media Diagnostics (فحص جودة الاتصال)') }}</div>
                <button onclick="closeDiagnosticsModal()" style="background:none; border:none; color:var(--text-muted); font-size:20px; cursor:pointer;">✕</button>
            </div>

            <div id="diag-loading" style="text-align: center; padding: 20px; color: var(--text-muted); font-size: 13px;">
                ⏳ {{ __('Running automated WebRTC & STUN/TURN checks...') }}
            </div>

            <div id="diag-content" style="display: none; flex-direction: column; gap: 12px;">
                <!-- Overall Status Banner -->
                <div id="diag-overall-box" style="padding: 12px 16px; border-radius: 12px; background: rgba(16, 185, 129, 0.12); border: 1px solid rgba(52, 211, 153, 0.3); display: flex; justify-content: space-between; align-items: center;">
                    <div>
                        <div style="font-size: 10px; font-weight: 800; color: var(--brand-primary); text-transform: uppercase;">{{ __('Overall Connection Quality') }}</div>
                        <div id="diag-overall-text" style="font-size: 16px; font-weight: 900; color: #6EE7B7;">Excellent (ممتاز)</div>
                    </div>
                    <span id="diag-overall-badge" style="font-size: 24px;">🟢</span>
                </div>

                <!-- Diagnostics Grid -->
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px;">
                    <div style="background: var(--bg-card); border: 1px solid var(--border-card); border-radius: 10px; padding: 10px;">
                        <div style="font-size: 10px; color: var(--text-muted); font-weight: 800;">📷 {{ __('Camera Access') }}</div>
                        <div id="diag-cam-status" style="font-size: 13px; font-weight: 800; color: #10B981;">✓ Verified</div>
                    </div>
                    <div style="background: var(--bg-card); border: 1px solid var(--border-card); border-radius: 10px; padding: 10px;">
                        <div style="font-size: 10px; color: var(--text-muted); font-weight: 800;">🎙️ {{ __('Microphone Access') }}</div>
                        <div id="diag-mic-status" style="font-size: 13px; font-weight: 800; color: #10B981;">✓ Verified</div>
                    </div>
                    <div style="background: var(--bg-card); border: 1px solid var(--border-card); border-radius: 10px; padding: 10px;">
                        <div style="font-size: 10px; color: var(--text-muted); font-weight: 800;">⚡ {{ __('Internet Ping (RTT)') }}</div>
                        <div id="diag-ping-status" style="font-size: 13px; font-weight: 800; color: #6EE7B7;">32 ms</div>
                    </div>
                    <div style="background: var(--bg-card); border: 1px solid var(--border-card); border-radius: 10px; padding: 10px;">
                        <div style="font-size: 10px; color: var(--text-muted); font-weight: 800;">🌐 {{ __('STUN & TURN Relay') }}</div>
                        <div id="diag-turn-status" style="font-size: 13px; font-weight: 800; color: #10B981;">✓ Active (Coturn)</div>
                    </div>
                </div>

                <!-- Telemetry Stats Table -->
                <div style="background: rgba(15, 23, 42, 0.6); border: 1px solid var(--border-color); border-radius: 10px; padding: 12px; font-family: monospace; font-size: 11px; line-height: 1.6; color: var(--text-secondary);">
                    <div style="display: flex; justify-content: space-between;"><span>SFU Host:</span> <span id="diag-livekit-host" style="color: #93C5FD;">wss://nextspace.munazzah.com/livekit</span></div>
                    <div style="display: flex; justify-content: space-between;"><span>Packet Loss:</span> <span id="diag-packet-loss" style="color: #6EE7B7;">0.0%</span></div>
                    <div style="display: flex; justify-content: space-between;"><span>Jitter:</span> <span id="diag-jitter" style="color: #6EE7B7;">4 ms</span></div>
                    <div style="display: flex; justify-content: space-between;"><span>Framerate (FPS):</span> <span id="diag-fps" style="color: #6EE7B7;">30 FPS</span></div>
                </div>

                <div style="display: flex; gap: 8px;">
                    <button onclick="runDiagnosticsCheck()" class="action-link-btn" style="flex: 1; justify-content: center;">🔄 {{ __('Re-run Check') }}</button>
                    <button onclick="copyDiagnosticsReport()" class="action-link-btn" style="flex: 1; background: var(--brand-accent); color: white; justify-content: center;">📋 {{ __('Copy Report for Support') }}</button>
                </div>
            </div>
        </div>
    </div>

    <!-- 1. User Spotlight & Live Video Modal -->
    <div id="user-spotlight-modal" class="modal-overlay">
        <div class="modal-card" style="max-width: 650px; padding: 20px;">
            <div class="modal-header">
                <div class="modal-title" style="display: flex; align-items: center; gap: 12px;">
                    <div id="spotlight-avatar-box" style="width: 42px; height: 42px; border-radius: 12px; overflow: hidden; background: var(--bg-card); display: flex; align-items: center; justify-content: center; font-weight: 900; font-size: 15px; color: var(--brand-primary); border: 2px solid var(--border-color);">
                    </div>
                    <div>
                        <div id="spotlight-user-name" style="font-size: 16px; font-weight: 800; color: var(--text-primary);"></div>
                        <div id="spotlight-user-subtitle" style="font-size: 11px; color: var(--text-secondary);"></div>
                    </div>
                </div>
                <div style="display: flex; align-items: center; gap: 8px;">
                    <button id="spotlight-wave-btn" onclick="sendWaveToSpotlightUser()" class="action-link-btn" style="background: rgba(59, 130, 246, 0.2); border-color: rgba(59, 130, 246, 0.4); color: #93C5FD; font-size: 11px; padding: 4px 10px;">
                        <span>👋</span> {{ __('Wave (استئذان)') }}
                    </button>
                    <button onclick="closeUserSpotlight()" style="background:none; border:none; color:var(--text-muted); font-size:20px; cursor:pointer;">✕</button>
                </div>
            </div>

            <!-- Spotlight Video Viewport -->
            <div id="spotlight-video-container" style="position: relative; width: 100%; height: 320px; background: #070F0A; border-radius: 16px; overflow: hidden; display: flex; align-items: center; justify-content: center; border: 1px solid var(--border-color); box-shadow: inset 0 0 40px rgba(0,0,0,0.8);">
                <video id="spotlight-video-player" autoplay playsinline style="width: 100%; height: 100%; object-fit: contain; display: none;"></video>
                <div id="spotlight-no-video" style="display: flex; flex-direction: column; align-items: center; gap: 12px; color: var(--text-muted);">
                    <div id="spotlight-big-avatar" style="width: 86px; height: 86px; border-radius: 24px; background: rgba(16, 185, 129, 0.15); border: 2px solid var(--brand-primary); display: flex; align-items: center; justify-content: center; font-size: 32px; font-weight: 900; color: #6EE7B7; overflow: hidden;">
                    </div>
                    <span style="font-size: 13px; font-weight: 700;">{{ __('Live camera stream is currently offline') }}</span>
                </div>
            </div>

            <!-- Live Work Activity & Task List Section -->
            <div style="display: flex; flex-direction: column; gap: 12px; margin-top: 4px;">
                <div id="spotlight-active-timer-box" style="background: rgba(16, 185, 129, 0.08); border: 1px solid rgba(52, 211, 153, 0.25); border-radius: 12px; padding: 12px; display: none; align-items: center; justify-content: space-between;">
                    <div style="display: flex; align-items: center; gap: 10px;">
                        <span style="font-size: 22px;">⏱️</span>
                        <div>
                            <div style="font-size: 10px; font-weight: 800; color: var(--brand-primary); text-transform: uppercase; letter-spacing: 0.5px;">{{ __('Currently Working On:') }}</div>
                            <div id="spotlight-timer-task" style="font-size: 13px; font-weight: 800; color: var(--text-primary);"></div>
                        </div>
                    </div>
                    <div id="spotlight-timer-clock" style="font-family: monospace; font-size: 16px; font-weight: 900; color: #6EE7B7; letter-spacing: 1px;"></div>
                </div>

                <!-- Assigned Tasks List -->
                <div>
                    <div style="font-size: 11px; font-weight: 900; color: var(--text-secondary); text-transform: uppercase; margin-bottom: 8px; display: flex; justify-content: space-between; align-items: center;">
                        <span>📋 {{ __('Assigned Tasks & Progress') }}</span>
                        <span id="spotlight-tasks-count" class="guest-badge" style="background: rgba(16, 185, 129, 0.15); border-color: rgba(52, 211, 153, 0.3); color: #6EE7B7;">0 Tasks</span>
                    </div>
                    <div id="spotlight-tasks-list" style="display: flex; flex-direction: column; gap: 6px; max-height: 180px; overflow-y: auto;">
                        <!-- Injected dynamically via JS -->
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 1b. All-Users Camera Gallery Grid Modal -->
    <div id="camera-gallery-modal" class="modal-overlay">
        <div class="modal-card" style="max-width: 1100px; height: 85vh;">
            <div class="modal-header">
                <div class="modal-title"><span>🎥</span> {{ __('Office Live Cameras Gallery (شبكة الكاميرات المباشرة)') }}</div>
                <button onclick="closeCameraGalleryModal()" style="background:none; border:none; color:var(--text-muted); font-size:20px; cursor:pointer;">✕</button>
            </div>
            <div id="camera-gallery-grid" style="flex: 1; overflow-y: auto; display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 16px; padding: 6px;">
                <!-- Populated dynamically via JS -->
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

    <!-- ── Session Replaced Alert Modal ── -->
    <div id="session-replaced-modal" class="modal-overlay" style="display: none; z-index: 1000000;">
        <div class="modal-card" style="max-width: 440px; text-align: center; border: 1px solid rgba(239, 68, 68, 0.4); box-shadow: 0 20px 50px rgba(239, 68, 68, 0.3);">
            <div style="font-size: 48px; margin-bottom: 12px;">🚪</div>
            <div class="modal-title" style="color: #F87171; justify-content: center;">{{ __('Session Terminated (تم إنهاء الجلسة)') }}</div>
            <p id="session-replaced-reason" style="font-size: 13px; color: var(--text-secondary); margin: 16px 0 24px; line-height: 1.6;">
                {{ __('Your session was opened in another window, tab, or office location. This window has been disconnected to prevent concurrent sessions.') }}
            </p>
            <div style="display: flex; gap: 10px; justify-content: center;">
                <button onclick="location.reload()" class="tactile-btn btn-primary" style="padding: 10px 24px;">
                    🔄 {{ __('Reconnect Here (إعادة الاتصال هنا)') }}
                </button>
                <a href="{{ route('dashboard') }}" class="tactile-btn btn-secondary" style="padding: 10px 20px; text-decoration: none;">
                    📊 {{ __('Go to Dashboard') }}
                </a>
            </div>
        </div>
    </div>

    <!-- Toast Notification -->
    <div id="toast-bubble" class="toast-bubble"></div>

    <!-- ── LiveKit Client SFU SDK (Self-Hosted on Server) & WebRTC Media Layer ── -->
    <script src="/js/webrtc/livekit-client.umd.min.js"></script>
    <script src="/js/webrtc/webrtc-manager.js"></script>

    <!-- ── JavaScript Realtime Engine, LiveKit SFU & Spatial Audio Pipeline ── -->
    <script>
        const CONFIG = {
            map: @json($map),
            currentUser: @json($user),
            org: @json($organization),
            allowedRoomIds: @json($userAllowedRoomIds ?? []),
            token: "{{ $realtimeToken }}",
            wsUrl: @json($wsUrl ?? null),
            csrf: document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        };

        const canvas = document.getElementById('office-canvas');
        const ctx = canvas.getContext('2d');
        const container = document.getElementById('canvas-container');

        let width = canvas.width = (container && container.clientWidth) ? container.clientWidth : window.innerWidth;
        let height = canvas.height = (container && container.clientHeight) ? container.clientHeight : window.innerHeight;

        const TILE_SIZE = (CONFIG.map && CONFIG.map.tile_size) ? Number(CONFIG.map.tile_size) : 16;
        const MAP_WIDTH_PX = 1024;
        const MAP_HEIGHT_PX = 909;

        let zoomLevel = 1.0;
        let cameraOffset = { x: 0, y: 0 };
        const rooms = (CONFIG.map && CONFIG.map.rooms) ? CONFIG.map.rooms : [];
        const roomDoorStates = new Map();
        let pendingKnock = null;

        // Local User Profile Image
        const userAvatarUrl = CONFIG.currentUser?.avatar_url || null;
        let localAvatarImg = null;
        if (userAvatarUrl) {
            localAvatarImg = new Image();
            localAvatarImg.src = userAvatarUrl;
        }

        // ── Local & Remote Avatars ──
        const isGuest = {{ !empty($user->is_guest) ? 'true' : 'false' }};
        const guestAllowedRoomId = @json(isset($invitation) ? ($invitation->room_id ?: ($room->id ?? null)) : null);
        const userGender = @json(!empty($user->gender) ? $user->gender : (!empty($user->profile?->gender) ? $user->profile->gender : 'male'));
        const spawnPos = @json($initialSpawn ?? null);

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

        // ── Resize & Camera ──
        function centerCamera() {
            if (!canvas || !container) return;
            width = canvas.width = container.clientWidth || window.innerWidth;
            height = canvas.height = container.clientHeight || window.innerHeight;

            const scaleX = (width - 40) / MAP_WIDTH_PX;
            const scaleY = (height - 40) / MAP_HEIGHT_PX;
            zoomLevel = Math.min(1.0, Math.max(0.65, Math.min(scaleX, scaleY)));

            const targetX = (typeof localAvatar !== 'undefined' && localAvatar && localAvatar.x) ? localAvatar.x : (MAP_WIDTH_PX / 2);
            const targetY = (typeof localAvatar !== 'undefined' && localAvatar && localAvatar.y) ? localAvatar.y : (MAP_HEIGHT_PX / 2);

            if (MAP_WIDTH_PX * zoomLevel <= width && MAP_HEIGHT_PX * zoomLevel <= height) {
                cameraOffset.x = (width - MAP_WIDTH_PX * zoomLevel) / 2;
                cameraOffset.y = (height - MAP_HEIGHT_PX * zoomLevel) / 2;
            } else {
                cameraOffset.x = (width / 2) - (targetX * zoomLevel);
                cameraOffset.y = (height / 2) - (targetY * zoomLevel);
            }
        }

        function resizeCanvas() {
            centerCamera();
            if (typeof draw === 'function') draw();
        }
        window.addEventListener('resize', resizeCanvas);
        centerCamera();

        // ── Preloaded Background & Realtime User Profile Avatars ──
        const MAP_BG_URL = (CONFIG.map && CONFIG.map.layout_data && CONFIG.map.layout_data.background_image_url)
            ? CONFIG.map.layout_data.background_image_url
            : '/images/office_floorplan.jpg';
        const BLUEPRINT_IMAGE = new Image();
        BLUEPRINT_IMAGE.src = MAP_BG_URL + (MAP_BG_URL.includes('?') ? '&' : '?') + 'v=' + Date.now();
        let blueprintLoaded = false;
        BLUEPRINT_IMAGE.onload = () => {
            blueprintLoaded = true;
            resizeCanvas();
        };
        BLUEPRINT_IMAGE.onerror = () => {
            blueprintLoaded = false;
            resizeCanvas();
        };

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

        // ── Movement & Controls ──
        const keys = {};
        window.addEventListener('keydown', (e) => {
            if (['input', 'textarea', 'select'].includes(document.activeElement.tagName.toLowerCase())) return;
            const k = e.key.toLowerCase();
            if (['w', 'a', 's', 'd', 'arrowup', 'arrowleft', 'arrowdown', 'arrowright'].includes(k)) {
                keys[k] = true;
            }

            // 'E' Key to Sit / Stand at Desk or Chair
            if (k === 'e') {
                if (localAvatar.isSitting) {
                    localAvatar.isSitting = false;
                    localAvatar.sittingFurnitureId = null;
                    if (ws && ws.readyState === WebSocket.OPEN) {
                        ws.send(JSON.stringify({ type: 'user.sit', payload: { isSitting: false } }));
                    }
                    showToast('🧍 {{ __("Stood up (نهوض)") }}');
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
                    showToast('🪑 {{ __("Seated at Desk / Table (جلوس في المكتب)") }}');
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

        function checkNearbyFurniture() {
            const promptEl = document.getElementById('furniture-sit-prompt');
            if (localAvatar.isSitting) {
                if (promptEl) promptEl.style.display = 'none';
                return;
            }

            const objects = (CONFIG.map && CONFIG.map.objects) || [];
            let found = null;
            for (const obj of objects) {
                const ox = (obj.x + (obj.width || 1) / 2) * 32;
                const oy = (obj.y + (obj.height || 1) / 2) * 32;
                const dist = Math.hypot(localAvatar.x - ox, localAvatar.y - oy);
                if (dist < 48) {
                    found = { id: obj.id || `chair_${obj.x}_${obj.y}`, x: ox, y: oy, name: obj.name || 'Desk / Chair' };
                    break;
                }
            }

            if (!found && CONFIG.map && CONFIG.map.rooms) {
                for (const r of CONFIG.map.rooms) {
                    const rx = (r.bounds.x + r.bounds.width / 2) * 32;
                    const ry = (r.bounds.y + r.bounds.height / 2) * 32;
                    const dist = Math.hypot(localAvatar.x - rx, localAvatar.y - ry);
                    if (dist < 55) {
                        found = { id: `room_center_${r.id}`, x: rx, y: ry, name: r.name };
                        break;
                    }
                }
            }

            nearbyChair = found;
            if (promptEl) {
                promptEl.style.display = found ? 'block' : 'none';
            }
        }

        canvas.addEventListener('click', (e) => {
            const rect = canvas.getBoundingClientRect();
            const clickX = (e.clientX - rect.left - cameraOffset.x) / zoomLevel;
            const clickY = (e.clientY - rect.top - cameraOffset.y) / zoomLevel;

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

            // Check room boundary & locking guards
            const targetRoom = getCurrentRoom(clickX, clickY);
            const myRoom = getCurrentRoom(localAvatar.x, localAvatar.y);
            if (isGuest && guestAllowedRoomId) {
                if (!targetRoom || targetRoom.id !== guestAllowedRoomId) {
                    showToast(`🚫 {{ __("Guests are only permitted in their designated invited room.") }}`);
                    return;
                }
            } else if (targetRoom && targetRoom !== myRoom) {
                if (roomDoorStates.get(targetRoom.id)) {
                    if (confirm(`🚪 ${targetRoom.name} {{ __("is locked. Would you like to knock?") }}`)) {
                        if (ws && ws.readyState === WebSocket.OPEN) {
                            ws.send(JSON.stringify({ type: 'room.knock', payload: { roomId: targetRoom.id, roomName: targetRoom.name } }));
                            showToast('⏳ {{ __("Knocked on door... waiting for occupant response.") }}');
                        }
                    }
                    return;
                }
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
                if (statusPill) statusPill.style.display = 'flex';
                if (roomNameEl) roomNameEl.textContent = `🏢 ${r.name}`;
                const isLocked = !!roomDoorStates.get(r.id);
                if (lockIcon) lockIcon.textContent = isLocked ? '🔒' : '🔓';
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
                statusPill.style.display = 'none';
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
            }

            checkNearbyFurniture();

            // Door lock & Room Permission Guard collision detection
            const currentR = getCurrentRoom(localAvatar.x, localAvatar.y);
            const targetR = getCurrentRoom(nextX, nextY);
            if (targetR && targetR !== currentR) {
                // 1. Guest Restriction Check: Guests can ONLY enter their designated invited room
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
                // 3. Door Lock Check
                else if (roomDoorStates.get(targetR.id)) {
                    nextX = localAvatar.x;
                    nextY = localAvatar.y;
                    localAvatar.targetX = localAvatar.x;
                    localAvatar.targetY = localAvatar.y;
                }
            }

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

                // 1. Spatial Audio Isolation Engine
                const audioEl = peerAudioElements.get(av.id);
                if (audioEl) {
                    if (localRoom) {
                        // Inside Room: Only hear occupants in the SAME room
                        audioEl.volume = (remoteRoom && remoteRoom.id === localRoom.id) ? 1.0 : 0;
                    } else {
                        // In Open Area: Never hear occupants inside closed rooms
                        if (remoteRoom) {
                            audioEl.volume = 0;
                        } else {
                            const dist = Math.hypot(localAvatar.x - av.x, localAvatar.y - av.y);
                            const maxDist = 250;
                            if (dist > maxDist) {
                                audioEl.volume = 0;
                            } else {
                                const factor = 1 - (dist / maxDist);
                                audioEl.volume = Math.max(0, Math.min(1, factor * factor));
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

        // ── Modern Profile & Live Video Node Rendering (Replacing Sprite Characters) ──
        function drawAvatar(av, isSelf) {
            const x = Number(av.x) || 400;
            const y = Number(av.y) || 400;
            const cardSize = 46;
            const radius = cardSize / 2;

            // 1. Spatial Audio Hearing Aura (Translucent Ambient Glow)
            const auraRadius = isSelf ? 150 : 130;
            const auraGrad = ctx.createRadialGradient(x, y, 10, x, y, auraRadius);
            auraGrad.addColorStop(0, isSelf ? 'rgba(16, 185, 129, 0.18)' : 'rgba(59, 130, 246, 0.14)');
            auraGrad.addColorStop(1, 'rgba(0, 0, 0, 0)');
            ctx.fillStyle = auraGrad;
            ctx.beginPath();
            ctx.arc(x, y, auraRadius, 0, Math.PI * 2);
            ctx.fill();

            // 2. Speaking Audio Pulsing Ring (Acoustic Wave)
            const isSpeaking = isSelf ? (micActive && localAvatar.isSpeaking) : (av.micActive && av.isSpeaking);
            if (isSpeaking) {
                const pulse = (Math.sin(Date.now() / 120) + 1) / 2;
                ctx.strokeStyle = '#10B981';
                ctx.lineWidth = 3 + pulse * 3;
                ctx.beginPath();
                ctx.arc(x, y, radius + 5 + pulse * 5, 0, Math.PI * 2);
                ctx.stroke();
            }

            // 3. Drop Shadow under Profile Card
            ctx.fillStyle = 'rgba(0, 0, 0, 0.45)';
            ctx.beginPath();
            ctx.ellipse(x, y + radius + 4, radius + 2, 7, 0, 0, Math.PI * 2);
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

            const isCamOn = isSelf ? (camActive && !!localMediaStream) : (av.camActive && !!av.videoEl && canSeeLiveCam);
            const videoEl = isSelf ? (document.getElementById('local-video-elem') || localAvatar.videoEl) : av.videoEl;

            ctx.save();
            ctx.beginPath();
            if (ctx.roundRect) ctx.roundRect(x - radius, y - radius, cardSize, cardSize, 14);
            else ctx.rect(x - radius, y - radius, cardSize, cardSize);
            ctx.clip();

            if (isCamOn && videoEl && (videoEl.readyState >= 2 || videoEl.videoWidth > 0)) {
                // Draw Live Video Stream directly inside the Canvas Profile Square!
                try {
                    ctx.drawImage(videoEl, x - radius, y - radius, cardSize, cardSize);
                } catch(e) {
                    if (av.avatarImg && av.avatarImg.complete && av.avatarImg.naturalWidth > 0) {
                        ctx.drawImage(av.avatarImg, x - radius, y - radius, cardSize, cardSize);
                    }
                }
            } else if (av.avatarImg && av.avatarImg.complete && av.avatarImg.naturalWidth > 0) {
                // Draw User Profile Picture
                ctx.drawImage(av.avatarImg, x - radius, y - radius, cardSize, cardSize);
            } else {
                // Draw Modern Gradient Monogram with User's Initials
                const bgGrad = ctx.createLinearGradient(x - radius, y - radius, x + radius, y + radius);
                if (isSelf) {
                    bgGrad.addColorStop(0, '#10B981');
                    bgGrad.addColorStop(1, '#047857');
                } else {
                    bgGrad.addColorStop(0, '#3B82F6');
                    bgGrad.addColorStop(1, '#1D4ED8');
                }
                ctx.fillStyle = bgGrad;
                ctx.fillRect(x - radius, y - radius, cardSize, cardSize);

                // Initials
                const nameParts = (av.name || 'User').trim().split(' ');
                const initials = nameParts.length >= 2 
                    ? (nameParts[0][0] + nameParts[1][0]).toUpperCase()
                    : (nameParts[0].substring(0, 2)).toUpperCase();
                ctx.font = '900 15px Cairo, Inter, sans-serif';
                ctx.fillStyle = '#FFFFFF';
                ctx.textAlign = 'center';
                ctx.textBaseline = 'middle';
                ctx.fillText(initials, x, y);
            }
            ctx.restore();

            // 5. Card Border Frame
            ctx.strokeStyle = isSelf ? '#10B981' : (isCamOn ? '#3B82F6' : 'rgba(255, 255, 255, 0.4)');
            ctx.lineWidth = isSelf ? 2.5 : 2;
            ctx.beginPath();
            if (ctx.roundRect) ctx.roundRect(x - radius, y - radius, cardSize, cardSize, 14);
            else ctx.rect(x - radius, y - radius, cardSize, cardSize);
            ctx.stroke();

            // 6. Status Indicators (Top-right Mic & Bottom-right Cam)
            const isMicOn = isSelf ? micActive : av.micActive;
            
            // Mic Badge
            ctx.fillStyle = isMicOn ? '#10B981' : 'rgba(15, 23, 42, 0.85)';
            ctx.beginPath();
            ctx.arc(x + radius - 4, y - radius + 4, 8, 0, Math.PI * 2);
            ctx.fill();
            ctx.strokeStyle = '#FFFFFF';
            ctx.lineWidth = 1;
            ctx.stroke();
            ctx.font = '8px Cairo, Inter, sans-serif';
            ctx.fillStyle = '#FFFFFF';
            ctx.textAlign = 'center';
            ctx.textBaseline = 'middle';
            ctx.fillText(isMicOn ? '🎙️' : '🔇', x + radius - 4, y - radius + 4);

            // Cam Badge if live
            if (isCamOn) {
                ctx.fillStyle = '#3B82F6';
                ctx.beginPath();
                ctx.arc(x + radius - 4, y + radius - 4, 8, 0, Math.PI * 2);
                ctx.fill();
                ctx.strokeStyle = '#FFFFFF';
                ctx.lineWidth = 1;
                ctx.stroke();
                ctx.font = '8px Cairo, Inter, sans-serif';
                ctx.fillStyle = '#FFFFFF';
                ctx.textAlign = 'center';
                ctx.textBaseline = 'middle';
                ctx.fillText('📷', x + radius - 4, y + radius - 4);
            }

            // 7. Sleek User Name Pill
            // 7. Sleek User Name Pill (with Seated Desk indicator)
            const isSitting = isSelf ? localAvatar.isSitting : av.isSitting;
            const displayName = isSelf 
                ? (isSitting ? `🪑 ${av.name} ({{ __("At Desk") }})` : `${av.name} ({{ __("You") }})`)
                : (isSitting ? `🪑 ${av.name}` : av.name);
            ctx.font = 'bold 10px Cairo, Inter, sans-serif';
            const nameW = ctx.measureText(displayName).width + 16;
            ctx.fillStyle = isSitting ? 'rgba(16, 185, 129, 0.95)' : 'rgba(15, 23, 42, 0.92)';
            if (ctx.roundRect) ctx.roundRect(x - nameW / 2, y + radius + 8, nameW, 18, 6);
            else ctx.rect(x - nameW / 2, y + radius + 8, nameW, 18);
            ctx.fill();

            ctx.strokeStyle = isSelf ? 'rgba(16, 185, 129, 0.6)' : 'rgba(255, 255, 255, 0.2)';
            ctx.lineWidth = 1;
            if (ctx.roundRect) ctx.roundRect(x - nameW / 2, y + radius + 8, nameW, 18, 6);
            else ctx.rect(x - nameW / 2, y + radius + 8, nameW, 18);
            ctx.stroke();

            ctx.fillStyle = isSitting ? '#FFFFFF' : (isSelf ? '#6EE7B7' : '#F8FAFC');
            ctx.textAlign = 'center';
            ctx.textBaseline = 'middle';
            ctx.fillText(displayName, x, y + radius + 17);

            // 8. In-World Floating Speech / Reaction Comic Bubble
            const bubble = speechBubbles.get(av.id);
            if (bubble) {
                const elapsed = Date.now() - bubble.timestamp;
                if (elapsed < 4800) {
                    const progress = Math.min(1, elapsed / 250);
                    const scale = progress < 1 ? Math.sin(progress * Math.PI / 2) * 1.08 : (elapsed > 4000 ? (4800 - elapsed) / 800 : 1.0);
                    const alpha = elapsed > 4000 ? (4800 - elapsed) / 800 : 1.0;
                    const bubbleY = y - radius - 18 - (scale * 10);

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
                };

                ws.onclose = (ev) => {
                    console.log('⚠️ WebSocket disconnected. Code:', ev.code, 'Reason:', ev.reason);
                    if (window._wsPingTimer) clearInterval(window._wsPingTimer);
                    if (!isSessionReplaced && !wsReconnectTimer) {
                        wsReconnectAttempts++;
                        const delay = Math.min(1000 * Math.pow(1.5, wsReconnectAttempts), 8000);
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

                ws.onmessage = (e) => {
                    try {
                        const data = JSON.parse(e.data);

                        // 0. Session Replaced Event (Multi-tab / Multi-office lock)
                        if (data.type === 'session.replaced') {
                            isSessionReplaced = true;
                            if (wsReconnectTimer) { clearTimeout(wsReconnectTimer); wsReconnectTimer = null; }
                            if (window._wsPingTimer) { clearInterval(window._wsPingTimer); }
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

                        // 1. Welcome packet with current map occupants
                        if (data.type === 'welcome' && data.payload?.occupants) {
                            data.payload.occupants.forEach(occ => {
                                if (occ.userId && occ.userId !== localAvatar.id) {
                                    let avImg = null;
                                    if (occ.avatarUrl) {
                                        avImg = new Image();
                                        avImg.crossOrigin = 'anonymous';
                                        avImg.src = occ.avatarUrl;
                                    }
                                    const posX = Number(occ.position?.x) || 400;
                                    const posY = Number(occ.position?.y) || 400;
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
                                }
                            });
                            updateOccupantsCounter();
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

                        // 8b. In-World Floating Speech Bubble
                        else if (data.type === 'chat.bubble' && data.payload) {
                            spawnSpeechBubble(data.payload.userId, data.payload.userName, data.payload.text);
                        }

                        // 8c. In-World Floating Emoji Reaction
                        else if (data.type === 'user.reaction' && data.payload) {
                            spawnSpeechBubble(data.payload.userId, data.payload.userName, null, data.payload.emoji);
                        }

                        // 8d. Shoulder-Tap / Wave
                        else if (data.type === 'user.wave' && data.payload) {
                            if (data.payload.targetUserId === localAvatar.id) {
                                showToast(`👋 ${data.payload.senderName} {{ __("waved at you for a quick chat! (ألقى التحية عليك)") }}`);
                                spawnSpeechBubble(data.payload.senderUserId, data.payload.senderName, null, '👋');
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
                                    <button class="v-btn" id="vbtn-sm-${userId}" onclick="resizeVideoCard('${userId}', 'small')" title="{{ __('Small View (عرض صغير)') }}">📱</button>
                                    <button class="v-btn active" id="vbtn-med-${userId}" onclick="resizeVideoCard('${userId}', 'medium')" title="{{ __('Medium View (عرض متوسط)') }}">💻</button>
                                    <button class="v-btn" id="vbtn-lg-${userId}" onclick="resizeVideoCard('${userId}', 'large')" title="{{ __('Theater / Large (عرض كبير)') }}">📺</button>
                                    <button class="v-btn" onclick="toggleFullscreenVideo('${userId}')" title="{{ __('Full Screen (شاشة كاملة)') }}">⛶</button>
                                    <button class="v-btn" onclick="togglePipVideo('${userId}')" title="{{ __('Picture in Picture') }}">🗖</button>
                                    <button class="v-btn" onclick="toggleCollapseVideo('${userId}')" title="{{ __('Minimize (تصغير)') }}">➖</button>
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
                updateGalleryGrid();
            }
        }

        function handleLiveKitTrackUnsubscribed(track, publication, participant) {
            const userId = participant.identity;
            const trackSource = publication?.source || track?.source || 'unknown';
            console.log(`[LiveKit SFU] Track unsubscribed: ${track.kind} (${trackSource}) from ${userId}`);

            if (track.kind === 'video') {
                const av = remoteAvatars.get(userId);
                if (av) {
                    av.livekitVideoTrack = null;
                    av.camActive = false;
                    av.videoEl = null;
                }
                if (trackSource === 'screen_share') {
                    const card = peerVideoCards.get(userId);
                    if (card) {
                        card.remove();
                        peerVideoCards.delete(userId);
                    }
                } else {
                    const av = remoteAvatars.get(userId);
                    if (av) {
                        av.videoEl = null;
                        av.camActive = false;
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
            document.getElementById('mic-icon').textContent = micActive ? '🎙️' : '🔇';
            document.getElementById('mic-text').textContent = micActive ? '{{ __("المايك يعمل") }}' : '{{ __("كتم المايك") }}';
        }

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
                        localTrack = pubs.find(p => p.source === 'camera' || !p.source)?.track;
                    }

                    if (localTrack?.mediaStreamTrack) {
                        localMediaStream = new MediaStream([localTrack.mediaStreamTrack]);
                    } else if (!localMediaStream) {
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
            document.getElementById('cam-icon').textContent = camActive ? '📹' : '📷';
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
                    text.textContent = '{{ __("مشاركة الشاشة") }}';
                    showToast('⏹️ {{ __("تم إيقاف مشاركة الشاشة") }}');
                    if (ws && ws.readyState === WebSocket.OPEN) {
                        ws.send(JSON.stringify({ type: 'presentation.stop', payload: {} }));
                    }
                }
            } catch(e) {
                console.error('[Screen] error:', e);
                screenActive = false;
                const btn = document.getElementById('btn-screen');
                btn.classList.remove('active');
                document.getElementById('screen-text').textContent = '{{ __("Share") }}';
                if (e.name !== 'NotAllowedError') {
                    showToast(`❌ {{ __("Screen share error:") }} ${e.message || e.name}`);
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

        function spawnSpeechBubble(userId, userName, text, emoji) {
            speechBubbles.set(userId, {
                userId,
                userName,
                text: text || null,
                emoji: emoji || null,
                timestamp: Date.now(),
                type: emoji ? 'emoji' : 'text'
            });
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
                    payload: { targetUserId: targetId }
                }));
                spawnSpeechBubble(localAvatar.id, localAvatar.name, null, '👋');
                showToast('👋 {{ __("Waved at colleague! (تم إلقاء التحية)") }}');
            }
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

            // 1. Spawn floating speech bubble on canvas
            spawnSpeechBubble(localAvatar.id, localAvatar.name, text, null);

            // 2. Append to chat drawer
            appendChatMessage(msgPayload, true);

            // 3. Send over WebSocket
            if (ws && ws.readyState === WebSocket.OPEN) {
                ws.send(JSON.stringify({
                    type: 'chat.bubble',
                    payload: { text }
                }));
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

                    appendChatMessage({
                        senderName: localAvatar.name,
                        senderId: localAvatar.id,
                        text: `📎 {{ __("Shared a file:") }} ${fileData.name}`,
                        time: new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' }),
                        scope: chatScope,
                        roomId: myRoom ? myRoom.id : null,
                        file: fileData
                    }, true);

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

        // ── Rich Realtime Collaborative Whiteboard Engine ──
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
                        broadcastWbStroke({ tool: 'text', color: wbColor, startX: wbStartX, startY: wbStartY, text: txt });
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
                    broadcastWbStroke({ tool: 'note', startX: wbStartX, startY: wbStartY });
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
            localAvatar.targetX = av.x + 30;
            localAvatar.targetY = av.y;
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

                    if (isGuest) {
                        // Privacy protection: completely hide timer and tasks for guest viewers
                        if (timerBox) timerBox.style.display = 'none';
                        if (tasksCount) tasksCount.textContent = '🔒 {{ __("Restricted") }}';
                        tasksList.innerHTML = `<div style="text-align:center; padding: 14px; color: var(--text-muted); font-size:12px;">🔒 {{ __("Internal team tasks and project tracking are private to team members.") }}</div>`;
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

        function openMyTaskDrawer() {
            openUserSpotlight(localAvatar.id);
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

        function showToast(msg) {
            const t = document.getElementById('toast-bubble');
            t.textContent = msg;
            t.style.display = 'block';
            setTimeout(() => { t.style.display = 'none'; }, 3200);
        }

        function toggleOfficeDropdown(e) {
            e.stopPropagation();
            const dd = document.getElementById('office-switcher-dropdown');
            if (dd) {
                dd.style.display = dd.style.display === 'none' ? 'block' : 'none';
            }
        }

        document.addEventListener('click', (e) => {
            const dd = document.getElementById('office-switcher-dropdown');
            if (dd && !e.target.closest('#office-switcher-dropdown')) {
                dd.style.display = 'none';
            }
        });

        // Start animation loop
        requestAnimationFrame(draw);
    </script>
</body>
</html>
