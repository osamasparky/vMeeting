<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">
    <title>{{ $organization->name }} — {{ __('Virtual Workplace') }}</title>
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;800;900&family=IBM+Plex+Sans+Arabic:wght@400;500;600;700;800&family=Inter:wght@400;500;600;700;800&family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    
    <script>
        // Synchronous theme initializer to prevent flash of unstyled content
        const savedTheme = localStorage.getItem('vw_theme') || 'dark';
        document.documentElement.setAttribute('data-theme', savedTheme);
    </script>

    <style>
        :root {
            /* Futuristic Digital Workplace OS Tokens (Dark Mode Default) */
            --bg-canvas: #090d16;
            --bg-base: #0b0f19;
            --bg-surface: rgba(17, 24, 39, 0.92);
            --bg-sidebar: rgba(15, 23, 42, 0.95);
            --bg-card: rgba(30, 41, 59, 0.85);
            --bg-input: #1e293b;
            --bg-dock: rgba(15, 23, 42, 0.88);
            --border-color: rgba(255, 255, 255, 0.12);
            --border-glow: rgba(59, 130, 246, 0.35);

            --brand-primary: #3b82f6;
            --brand-secondary: #8b5cf6;
            --brand-teal: #06b6d4;
            --brand-green: #10b981;
            --brand-orange: #f97316;
            --brand-crimson: #ef4444;
            --brand-gold: #f59e0b;

            --text-primary: #f8fafc;
            --text-secondary: #cbd5e1;
            --text-muted: #94a3b8;
            --text-dim: #64748b;

            --shadow-card: 0 10px 30px -5px rgba(0, 0, 0, 0.5);
            --shadow-dock: 0 20px 45px -10px rgba(0, 0, 0, 0.6), 0 0 1px 1px rgba(255, 255, 255, 0.1);
            --shadow-hover: 0 12px 30px rgba(59, 130, 246, 0.25);

            --font-family: {{ app()->getLocale() === 'ar' ? "'Cairo', 'IBM Plex Sans Arabic', 'Plus Jakarta Sans', sans-serif" : "'Plus Jakarta Sans', 'Inter', sans-serif" }};
        }

        [data-theme="light"] {
            --bg-canvas: #f1f5f9;
            --bg-base: #f8fafc;
            --bg-surface: rgba(255, 255, 255, 0.95);
            --bg-sidebar: rgba(255, 255, 255, 0.98);
            --bg-card: #ffffff;
            --bg-input: #f1f5f9;
            --bg-dock: rgba(255, 255, 255, 0.92);
            --border-color: #e2e8f0;
            --border-glow: rgba(59, 130, 246, 0.2);

            --text-primary: #0f172a;
            --text-secondary: #334155;
            --text-muted: #64748b;
            --text-dim: #94a3b8;

            --shadow-card: 0 10px 30px -5px rgba(15, 23, 42, 0.08);
            --shadow-dock: 0 20px 45px -10px rgba(15, 23, 42, 0.15), 0 0 1px 1px rgba(0, 0, 0, 0.05);
            --shadow-hover: 0 12px 30px rgba(59, 130, 246, 0.15);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: var(--font-family);
        }

        body {
            background: var(--bg-canvas);
            color: var(--text-primary);
            height: 100vh;
            overflow: hidden;
            user-select: none;
            display: flex;
            transition: background 0.3s, color 0.3s;
        }

        /* ── Glassmorphism Sidebar ── */
        .sidebar {
            width: 320px;
            height: 100vh;
            background: var(--bg-sidebar);
            backdrop-filter: blur(20px);
            border-inline-end: 1px solid var(--border-color);
            display: flex;
            flex-direction: column;
            z-index: 50;
            box-shadow: 4px 0 25px rgba(0, 0, 0, 0.15);
            transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1), margin 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .sidebar.collapsed {
            transform: translateX({{ app()->getLocale() === 'ar' ? '320px' : '-320px' }});
            margin-inline-end: -320px;
        }

        .sidebar-header {
            padding: 18px 20px;
            border-bottom: 1px solid var(--border-color);
            background: var(--bg-surface);
        }
        .org-switcher {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
        }
        .org-title {
            font-weight: 900;
            font-size: 15px;
            color: var(--text-primary);
            display: flex;
            align-items: center;
            gap: 8px;
            cursor: pointer;
            text-decoration: none;
            transition: opacity 0.2s;
        }
        .org-title:hover { opacity: 0.85; }

        .role-badge {
            font-size: 10px;
            font-weight: 800;
            padding: 3px 8px;
            border-radius: 6px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            display: inline-flex;
            align-items: center;
            gap: 4px;
        }
        .badge-host {
            background: rgba(59, 130, 246, 0.15);
            color: #60a5fa;
            border: 1px solid rgba(59, 130, 246, 0.3);
        }
        .badge-guest {
            background: rgba(16, 185, 129, 0.15);
            color: #34d399;
            border: 1px solid rgba(16, 185, 129, 0.3);
        }

        .space-subtitle {
            font-size: 11px;
            color: var(--text-muted);
            margin-top: 3px;
            font-weight: 600;
        }
        .header-btn-row {
            display: flex;
            align-items: center;
            gap: 6px;
        }
        .btn-icon-square {
            width: 32px;
            height: 32px;
            background: var(--bg-input);
            border: 1px solid var(--border-color);
            border-radius: 8px;
            color: var(--text-primary);
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            text-decoration: none;
            font-size: 13px;
            transition: all 0.2s;
        }
        .btn-icon-square:hover {
            border-color: var(--brand-primary);
            transform: translateY(-1px);
        }

        /* ── Sidebar Tabs ── */
        .sidebar-tabs {
            display: flex;
            padding: 8px 16px;
            border-bottom: 1px solid var(--border-color);
            gap: 8px;
            background: var(--bg-surface);
        }
        .sidebar-tab {
            flex: 1;
            text-align: center;
            color: var(--text-muted);
            cursor: pointer;
            padding: 8px 6px;
            font-size: 12px;
            font-weight: 800;
            border-radius: 8px;
            transition: all 0.2s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
        }
        .sidebar-tab:hover {
            color: var(--text-primary);
            background: var(--bg-input);
        }
        .sidebar-tab.active {
            color: white;
            background: linear-gradient(135deg, var(--brand-primary), var(--brand-secondary));
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.25);
        }

        .sidebar-tab-content {
            flex: 1;
            overflow-y: auto;
            display: flex;
            flex-direction: column;
            background: transparent;
        }

        /* ── Chat Container ── */
        .chat-container {
            display: flex;
            flex-direction: column;
            height: 100%;
        }
        .chat-messages {
            flex: 1;
            overflow-y: auto;
            padding: 14px;
            display: flex;
            flex-direction: column;
            gap: 10px;
            font-size: 12px;
        }
        .chat-bubble {
            padding: 10px 14px;
            border-radius: 12px;
            max-width: 85%;
            font-size: 12px;
            line-height: 1.4;
            word-break: break-word;
        }
        .chat-bubble.mine {
            background: linear-gradient(135deg, var(--brand-primary), #2563eb);
            color: white;
            align-self: flex-end;
            border-bottom-inline-end-radius: 2px;
            box-shadow: 0 4px 12px rgba(37, 99, 235, 0.2);
        }
        .chat-bubble.peer {
            background: var(--bg-card);
            color: var(--text-primary);
            border: 1px solid var(--border-color);
            align-self: flex-start;
            border-bottom-inline-start-radius: 2px;
        }
        .chat-sender {
            font-size: 10px;
            font-weight: 800;
            color: var(--brand-teal);
            margin-bottom: 3px;
            display: flex;
            align-items: center;
            gap: 4px;
        }
        .chat-input-area {
            padding: 12px 14px;
            border-top: 1px solid var(--border-color);
            display: flex;
            align-items: center;
            gap: 8px;
            background: var(--bg-surface);
        }
        .chat-input-area input[type="text"] {
            flex: 1;
            background: var(--bg-input);
            border: 1px solid var(--border-color);
            border-radius: 10px;
            padding: 10px 12px;
            color: var(--text-primary);
            font-size: 12px;
            font-weight: 600;
            outline: none;
            transition: border-color 0.2s;
        }
        .chat-input-area input[type="text"]:focus {
            border-color: var(--brand-primary);
        }
        .chat-input-area button, .chat-input-area label {
            background: var(--bg-input);
            border: 1px solid var(--border-color);
            color: var(--text-primary);
            border-radius: 10px;
            width: 36px;
            height: 36px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            font-size: 14px;
            transition: all 0.2s;
        }
        .chat-input-area button:hover, .chat-input-area label:hover {
            border-color: var(--brand-primary);
            background: var(--bg-card);
        }

        /* ── Occupants & Rooms Directory ── */
        .directory-container {
            padding: 14px;
            display: flex;
            flex-direction: column;
            gap: 16px;
        }
        .room-group-card {
            background: var(--bg-card);
            border: 1px solid var(--border-color);
            border-radius: 14px;
            padding: 12px;
            display: flex;
            flex-direction: column;
            gap: 8px;
            box-shadow: var(--shadow-card);
        }
        .room-group-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding-bottom: 6px;
            border-bottom: 1px solid var(--border-color);
            font-size: 12px;
            font-weight: 800;
            color: var(--text-primary);
        }
        .user-occupant-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 8px 10px;
            border-radius: 10px;
            font-size: 12px;
            font-weight: 700;
            color: var(--text-primary);
            background: var(--bg-input);
            border: 1px solid var(--border-color);
            transition: transform 0.15s;
        }
        .user-occupant-item:hover {
            transform: translateX(2px);
        }
        .user-occupant-left {
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .status-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: var(--brand-green);
            box-shadow: 0 0 8px var(--brand-green);
            flex-shrink: 0;
        }

        /* ── Main Viewport Canvas ── */
        .viewport {
            flex: 1;
            height: 100vh;
            position: relative;
            overflow: hidden;
            background: var(--bg-canvas);
        }
        canvas#office-canvas {
            display: block;
            width: 100%;
            height: 100%;
            cursor: crosshair;
        }

        /* ── Floating Top Glass Bar ── */
        .floating-top-bar {
            position: fixed;
            top: 18px;
            inset-inline-end: 80px;
            display: flex;
            align-items: center;
            gap: 10px;
            z-index: 90;
        }
        .btn-glass-pill {
            background: var(--bg-surface);
            backdrop-filter: blur(14px);
            border: 1px solid var(--border-color);
            color: var(--text-primary);
            font-size: 12px;
            font-weight: 800;
            padding: 8px 14px;
            border-radius: 12px;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 6px;
            box-shadow: var(--shadow-card);
            cursor: pointer;
            transition: all 0.2s;
        }
        .btn-glass-pill:hover {
            border-color: var(--brand-primary);
            transform: translateY(-1px);
            box-shadow: var(--shadow-hover);
        }

        /* ── Floating Room Door Control Pill ── */
        .room-door-pill {
            position: fixed;
            top: 20px;
            left: 50%;
            transform: translateX(-50%);
            background: var(--bg-surface);
            backdrop-filter: blur(16px);
            border: 1px solid var(--border-color);
            border-radius: 16px;
            padding: 8px 18px;
            display: none;
            align-items: center;
            gap: 14px;
            z-index: 90;
            box-shadow: var(--shadow-dock);
            font-size: 13px;
            font-weight: 800;
            color: var(--text-primary);
            animation: fadeInDown 0.3s ease;
        }
        .btn-door-toggle {
            background: rgba(239, 68, 68, 0.15);
            border: 1px solid rgba(239, 68, 68, 0.4);
            color: #f87171;
            padding: 6px 14px;
            border-radius: 10px;
            cursor: pointer;
            font-weight: 800;
            font-size: 12px;
            display: flex;
            align-items: center;
            gap: 6px;
            transition: all 0.2s;
        }
        .btn-door-toggle.unlocked {
            background: rgba(16, 185, 129, 0.15);
            border-color: rgba(16, 185, 129, 0.4);
            color: #34d399;
        }

        /* ── Right Floating Toolbar ── */
        .right-toolbar {
            position: fixed;
            top: 18px;
            inset-inline-end: 20px;
            background: var(--bg-surface);
            backdrop-filter: blur(16px);
            border: 1px solid var(--border-color);
            border-radius: 14px;
            display: flex;
            flex-direction: column;
            gap: 6px;
            padding: 6px;
            z-index: 90;
            box-shadow: var(--shadow-card);
        }
        .tool-btn {
            width: 38px;
            height: 38px;
            border-radius: 10px;
            border: none;
            background: transparent;
            color: var(--text-muted);
            font-size: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.2s;
        }
        .tool-btn:hover {
            background: var(--bg-input);
            color: var(--text-primary);
            transform: scale(1.05);
        }

        /* ── Bottom Control Dock (Ultra Modern Glassmorphism) ── */
        .bottom-dock {
            position: fixed;
            bottom: 24px;
            left: 50%;
            transform: translateX(-50%);
            background: var(--bg-dock);
            backdrop-filter: blur(20px);
            border: 1px solid var(--border-color);
            border-radius: 20px;
            padding: 8px 16px;
            display: flex;
            align-items: center;
            gap: 10px;
            z-index: 100;
            box-shadow: var(--shadow-dock);
        }
        .dock-btn {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            background: transparent;
            border: 1px solid transparent;
            color: var(--text-secondary);
            padding: 8px 14px;
            border-radius: 14px;
            cursor: pointer;
            font-size: 11px;
            font-weight: 800;
            gap: 4px;
            transition: all 0.2s;
            min-width: 68px;
        }
        .dock-btn .icon { font-size: 18px; transition: transform 0.2s; }
        .dock-btn:hover {
            background: var(--bg-input);
            color: var(--text-primary);
            transform: translateY(-2px);
        }
        .dock-btn.active {
            background: rgba(59, 130, 246, 0.15);
            color: #60a5fa;
            border-color: rgba(59, 130, 246, 0.4);
            box-shadow: 0 4px 15px rgba(59, 130, 246, 0.2);
        }
        .dock-btn.active-green {
            background: rgba(16, 185, 129, 0.15);
            color: #34d399;
            border-color: rgba(16, 185, 129, 0.4);
            box-shadow: 0 4px 15px rgba(16, 185, 129, 0.2);
        }

        /* ── Floating Video Grid ── */
        .video-grid {
            position: fixed;
            top: 24px;
            inset-inline-start: 340px;
            display: none;
            gap: 12px;
            z-index: 95;
            flex-wrap: wrap;
            max-width: 650px;
        }
        .video-tile {
            width: 180px;
            height: 125px;
            background: #0f172a;
            border: 2px solid var(--brand-primary);
            border-radius: 14px;
            overflow: hidden;
            position: relative;
            box-shadow: var(--shadow-card);
        }
        .video-tile video { width: 100%; height: 100%; object-fit: cover; }
        .video-tile-name {
            position: absolute;
            bottom: 6px;
            inset-inline-start: 8px;
            background: rgba(15, 23, 42, 0.85);
            padding: 3px 8px;
            border-radius: 6px;
            font-size: 10px;
            font-weight: 800;
            color: #ffffff;
            backdrop-filter: blur(4px);
        }

        /* ── Modals & Drawers ── */
        .modal-overlay {
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, 0.65);
            backdrop-filter: blur(12px);
            display: none;
            align-items: center;
            justify-content: center;
            z-index: 1000;
            padding: 20px;
        }
        .modal-box {
            background: var(--bg-surface);
            backdrop-filter: blur(20px);
            border: 1px solid var(--border-color);
            border-radius: 24px;
            padding: 28px;
            width: 100%;
            max-width: 520px;
            box-shadow: 0 25px 60px rgba(0, 0, 0, 0.5);
            color: var(--text-primary);
            animation: zoomIn 0.25s ease;
        }

        /* Avatar Picker Cards */
        .avatar-card-picker {
            border: 2px solid var(--border-color);
            border-radius: 16px;
            padding: 14px;
            cursor: pointer;
            text-align: center;
            background: var(--bg-input);
            transition: all 0.2s;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 10px;
        }
        .avatar-card-picker:hover {
            border-color: var(--brand-primary);
            transform: translateY(-2px);
        }
        .avatar-card-picker.selected {
            border-color: var(--brand-primary);
            background: rgba(59, 130, 246, 0.12);
            box-shadow: 0 4px 16px rgba(59, 130, 246, 0.25);
        }
        .avatar-preview-circle {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            overflow: hidden;
            background: var(--bg-card);
            border: 3px solid var(--border-color);
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .avatar-preview-circle img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        @keyframes fadeInDown {
            from { opacity: 0; transform: translate(-50%, -15px); }
            to { opacity: 1; transform: translate(-50%, 0); }
        }
        @keyframes zoomIn {
            from { opacity: 0; transform: scale(0.95); }
            to { opacity: 1; transform: scale(1); }
        }
    </style>
</head>
<body>

    <!-- Left / Right Glassmorphic Sidebar -->
    <aside class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <div class="org-switcher">
                <div>
                    <a href="{{ route('dashboard') }}" class="org-title">
                        <span>🏢</span>
                        <span>{{ $organization->name }}</span>
                    </a>
                    <div style="display: flex; align-items: center; gap: 6px; margin-top: 4px;">
                        @if(!empty($user->is_guest))
                            <span class="role-badge badge-guest">
                                👤 GUEST ACCESS
                            </span>
                        @else
                            <span class="role-badge badge-host">
                                👑 {{ __('Host / Member') }}
                            </span>
                        @endif
                        <span class="space-subtitle">• {{ $floor->name }}</span>
                    </div>
                </div>
                <div class="header-btn-row">
                    <!-- Theme Toggle in Sidebar -->
                    <button class="btn-icon-square" onclick="toggleAppTheme()" title="{{ __('Toggle Dark / Light Mode') }}">
                        <span id="sidebar-theme-icon">🌙</span>
                    </button>

                    <!-- Language Switcher -->
                    @if(app()->getLocale() === 'ar')
                        <a href="{{ route('lang.switch', 'en') }}" class="btn-icon-square" title="Switch to English" style="font-weight: 800; font-size: 11px;">EN</a>
                    @else
                        <a href="{{ route('lang.switch', 'ar') }}" class="btn-icon-square" title="التبديل إلى العربية" style="font-weight: 800; font-size: 11px;">عربي</a>
                    @endif

                    @if(empty($user->is_guest))
                        <button class="btn-icon-square" onclick="openInviteModal()" title="{{ __('Invite Guests & Members') }}">🔗</button>
                        <a href="{{ route('editor') }}" class="btn-icon-square" title="{{ __('Floor Map Designer') }}">🎨</a>
                    @endif
                </div>
            </div>
        </div>

        <!-- Sidebar Navigation Tabs -->
        <div class="sidebar-tabs">
            <div class="sidebar-tab active" onclick="switchSidebarTab('chat')" id="tab-btn-chat">
                <span>💬</span>
                <span>{{ __('Live Chat') }}</span>
            </div>
            <div class="sidebar-tab" onclick="switchSidebarTab('occupants')" id="tab-btn-occupants">
                <span>👥</span>
                <span>{{ __('People & Rooms') }}</span>
            </div>
            <div class="sidebar-tab" onclick="switchSidebarTab('help')" id="tab-btn-help">
                <span>ℹ️</span>
                <span>{{ __('Shortcuts') }}</span>
            </div>
        </div>

        <!-- Tab 1: Live Chat -->
        <div class="sidebar-tab-content" id="tab-content-chat">
            <div class="chat-container">
                <div class="chat-messages" id="chat-messages-container">
                    <div style="color: var(--text-muted); font-size: 11px; text-align: center; padding: 6px 0;">
                        🔒 {{ __('Spatial audio and realtime messages active for') }} {{ $organization->name }}.
                    </div>
                </div>
                <div class="chat-input-area">
                    <input type="text" id="chat-text-input" placeholder="{{ __('Type message...') }}" onkeydown="if(event.key==='Enter') sendChatMessage()">
                    <label for="chat-file-input" title="{{ __('Attach File / Image') }}">📎</label>
                    <input type="file" id="chat-file-input" style="display: none;" onchange="handleChatFileUpload(this)">
                    <button onclick="sendChatMessage()" title="{{ __('Send') }}">➤</button>
                </div>
            </div>
        </div>

        <!-- Tab 2: Occupants & Room Directory -->
        <div class="sidebar-tab-content" id="tab-content-occupants" style="display: none;">
            <div class="directory-container" id="occupants-directory">
                <!-- Dynamically populated room directory with occupants -->
                <div class="room-group-card">
                    <div class="room-group-header">
                        <span>🏢 {{ __('Main Floor') }}</span>
                        <span id="floor-occupants-count" style="color: var(--brand-teal); font-size: 11px;">1 Online</span>
                    </div>
                    <div style="display: flex; flex-direction: column; gap: 6px;" id="sidebar-floor-occupants">
                        <div class="user-occupant-item">
                            <div class="user-occupant-left">
                                <span class="status-dot"></span>
                                <span>{{ $user->name }}</span>
                            </div>
                            <span class="role-badge {{ !empty($user->is_guest) ? 'badge-guest' : 'badge-host' }}" style="font-size: 9px;">
                                {{ !empty($user->is_guest) ? __('Guest') : __('You') }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tab 3: Interactive Shortcuts & Help -->
        <div class="sidebar-tab-content" id="tab-content-help" style="display: none; padding: 20px;">
            <div style="display: flex; flex-direction: column; gap: 14px;">
                <h4 style="font-size: 13px; font-weight: 800; color: var(--text-primary);">⌨️ {{ __('Movement & Controls') }}</h4>
                <div style="font-size: 12px; color: var(--text-secondary); display: flex; flex-direction: column; gap: 8px; line-height: 1.5;">
                    <div>• <strong>WASD / Arrow Keys:</strong> {{ __('Walk your avatar across the office floor.') }}</div>
                    <div>• <strong>Floor Click:</strong> {{ __('Instant navigation path to target position.') }}</div>
                    <div>• <strong>Proximity Audio:</strong> {{ __('Approach colleagues to automatically hear and see them.') }}</div>
                    <div>• <strong>Room Doors:</strong> {{ __('Walk inside or click room pill to lock/unlock private meetings.') }}</div>
                    <div>• <strong>Scroll Wheel:</strong> {{ __('Zoom in and out on the office canvas.') }}</div>
                </div>
            </div>
        </div>
    </aside>

    <!-- Main Viewport Canvas -->
    <main class="viewport">
        <canvas id="office-canvas"></canvas>

        <!-- Floating Top Bar -->
        <div class="floating-top-bar">
            <!-- Theme Toggle Button -->
            <button class="btn-glass-pill" onclick="toggleAppTheme()" title="{{ __('Toggle Dark / Light Mode') }}">
                <span id="floating-theme-icon">🌙</span>
                <span id="floating-theme-text">{{ __('Dark Mode') }}</span>
            </button>

            <!-- Language Switcher -->
            @if(app()->getLocale() === 'ar')
                <a href="{{ route('lang.switch', 'en') }}" class="btn-glass-pill" title="Switch to English">🌐 English</a>
            @else
                <a href="{{ route('lang.switch', 'ar') }}" class="btn-glass-pill" title="التبديل إلى العربية">🌐 العربية</a>
            @endif

            <!-- Return to Executive Dashboard -->
            <a href="{{ route('dashboard') }}" class="btn-glass-pill" style="background: linear-gradient(135deg, var(--brand-primary), #2563eb); color: white; border-color: var(--brand-primary);" title="{{ __('Dashboard') }}">
                <span>🏢</span>
                <span>{{ __('Executive Dashboard') }}</span>
            </a>
        </div>

        <!-- Floating Room Door Control Pill -->
        <div class="room-door-pill" id="room-door-pill">
            <span id="room-door-name">🏢 Executive Conference</span>
            <button class="btn-door-toggle" id="btn-toggle-room-door" onclick="toggleCurrentRoomDoor()">
                <span>🔒</span>
                <span id="room-door-status-text">{{ __('Lock Door') }}</span>
            </button>
        </div>

        <!-- Right Tool Dock -->
        <div class="right-toolbar">
            <button class="tool-btn" onclick="openAvatarPickerModal()" title="{{ __('Choose Avatar Character') }}">🎭</button>
            <button class="tool-btn" onclick="openWhiteboard()" title="{{ __('Team Whiteboard') }}">📋</button>
            @if(empty($user->is_guest))
            <a href="{{ route('editor') }}" class="tool-btn" title="{{ __('Edit Floor Furniture') }}">🪑</a>
            @endif
            <button class="tool-btn" id="btn-toggle-sidebar" title="{{ __('Toggle Sidebar') }}">🔲</button>
            <button class="tool-btn" id="btn-reset-view" title="{{ __('Reset View') }}">🏠</button>
            <button class="tool-btn" id="btn-center-avatar" title="{{ __('Center on Me') }}">🎯</button>
            <div style="height: 1px; background: var(--border-color); margin: 2px 0;"></div>
            <button class="tool-btn" id="btn-zoom-in" title="{{ __('Zoom In') }}">➕</button>
            <button class="tool-btn" id="btn-zoom-out" title="{{ __('Zoom Out') }}">➖</button>
        </div>

        <!-- Floating Video Grid -->
        <div class="video-grid" id="office-video-grid">
            <div class="video-tile" id="local-video-container" style="display: none;">
                <video id="local-video-preview" autoplay muted playsinline></video>
                <div class="video-tile-name">You (Camera)</div>
            </div>
        </div>

        <!-- Bottom Floating Control Dock -->
        <div class="bottom-dock">
            <button class="dock-btn" id="mic-btn" onclick="toggleMicrophone()" title="{{ __('Microphone') }}">
                <span class="icon">🎤</span>
                <span>{{ __('Mic') }}</span>
            </button>
            <button class="dock-btn" id="cam-btn" onclick="toggleCamera()" title="{{ __('Camera') }}">
                <span class="icon">📹</span>
                <span>{{ __('Camera') }}</span>
            </button>
            <button class="dock-btn" id="avatar-dock-btn" onclick="openAvatarPickerModal()" title="{{ __('Change Avatar') }}">
                <span class="icon">🎭</span>
                <span>{{ __('Avatar') }}</span>
            </button>
            <button class="dock-btn" id="present-btn" onclick="toggleScreenShare()" title="{{ __('Screen Share') }}">
                <span class="icon">🖥️</span>
                <span id="present-btn-text">{{ __('Present') }}</span>
            </button>
            <button class="dock-btn" id="record-btn" onclick="toggleMeetingRecording()" title="{{ __('Record Session to Server') }}">
                <span class="icon" id="record-icon">⏺️</span>
                <span id="record-btn-text">{{ __('Record') }}</span>
            </button>
            <button class="dock-btn" id="gallery-btn" onclick="openRecordingsGallery()" title="{{ __('Saved Recordings Gallery') }}">
                <span class="icon">📼</span>
                <span>{{ __('Gallery') }}</span>
            </button>
            <button class="dock-btn" id="status-dock-btn" title="{{ __('Status') }}">
                <span class="icon">🟢</span>
                <span>{{ __('Available') }}</span>
            </button>
            <button class="dock-btn" onclick="openWhiteboard()" title="{{ __('Whiteboard') }}">
                <span class="icon">📋</span>
                <span>{{ __('Whiteboard') }}</span>
            </button>
            <button class="dock-btn" onclick="window.location.href='{{ route('dashboard') }}'" title="{{ __('Exit to Dashboard') }}" style="color: var(--brand-crimson);">
                <span class="icon">🚪</span>
                <span>{{ __('Exit') }}</span>
            </button>
        </div>
    </main>

    <!-- 🎭 Avatar Customizer & Character Selector Modal -->
    <div id="avatar-picker-modal" class="modal-overlay">
        <div class="modal-box">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                <h3 style="font-size: 17px; font-weight: 900; color: var(--text-primary);">🎭 {{ __('Choose Your Avatar Character') }}</h3>
                <button onclick="closeAvatarPickerModal()" style="background: none; border: none; color: var(--text-muted); font-size: 20px; cursor: pointer;">✕</button>
            </div>

            <p style="font-size: 13px; color: var(--text-secondary); margin-bottom: 18px;">
                {{ __('Select a 2.5D character avatar sprite to represent you on the virtual office floor.') }}
            </p>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 24px;">
                <!-- Male Avatar Card -->
                <div class="avatar-card-picker" id="picker-male" onclick="selectAvatarGender('male')">
                    <div class="avatar-preview-circle">
                        <img src="/images/avatars/male.jpg" alt="Male Character">
                    </div>
                    <strong style="font-size: 14px; color: var(--text-primary);">👨 {{ __('Male Avatar') }}</strong>
                    <span style="font-size: 11px; color: var(--text-muted);">{{ __('Modern Business Suit') }}</span>
                </div>

                <!-- Female Avatar Card -->
                <div class="avatar-card-picker" id="picker-female" onclick="selectAvatarGender('female')">
                    <div class="avatar-preview-circle">
                        <img src="/images/avatars/female.jpg" alt="Female Character">
                    </div>
                    <strong style="font-size: 14px; color: var(--text-primary);">👩 {{ __('Female Avatar') }}</strong>
                    <span style="font-size: 11px; color: var(--text-muted);">{{ __('Executive Teal Blazer') }}</span>
                </div>
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 10px;">
                <button onclick="closeAvatarPickerModal()" class="btn-glass-pill" style="padding: 10px 18px;">{{ __('Done') }}</button>
            </div>
        </div>
    </div>

    <!-- 🚪 Knock on Door Prompt Modal -->
    <div id="knock-prompt-modal" class="modal-overlay">
        <div class="modal-box" style="max-width: 440px; text-align: center;">
            <div style="font-size: 40px; margin-bottom: 10px;">🔒</div>
            <h3 style="font-size: 18px; font-weight: 800; margin-bottom: 6px;" id="knock-room-title">{{ __('Meeting Room is Locked') }}</h3>
            <p style="font-size: 13px; color: var(--text-muted); margin-bottom: 20px; line-height: 1.5;" id="knock-room-desc">
                {{ __('This room is currently in a private session. You must knock to request entry permission.') }}
            </p>
            <div style="display: flex; gap: 10px;">
                <button onclick="confirmKnock()" id="btn-knock-send" style="flex: 1; background: var(--brand-primary); color: white; font-weight: 800; border: none; border-radius: 12px; padding: 12px; cursor: pointer; font-size: 13px; display: flex; align-items: center; justify-content: center; gap: 6px;">
                    <span>🔔</span> {{ __('Knock on Door') }}
                </button>
                <button onclick="closeKnockPrompt()" class="btn-glass-pill" style="padding: 12px 18px;">
                    {{ __('Cancel') }}
                </button>
            </div>
            <div id="knock-status-msg" style="margin-top: 14px; font-size: 12px; display: none;"></div>
        </div>
    </div>

    <!-- 🔔 Knock Request Alert Modal -->
    <div id="knock-alert-modal" class="modal-overlay">
        <div class="modal-box" style="max-width: 440px; text-align: center; border: 2px solid var(--brand-primary);">
            <div style="font-size: 42px; margin-bottom: 10px;">🔔</div>
            <h3 style="font-size: 18px; font-weight: 800; margin-bottom: 6px;">{{ __('Knock Knock! Request to Enter') }}</h3>
            <p style="font-size: 13px; color: var(--text-secondary); margin-bottom: 20px;" id="knock-alert-desc">
                <strong>John Doe</strong> {{ __('is knocking on the door to enter') }} <strong>Executive Room</strong>.
            </p>
            <div style="display: flex; gap: 10px;">
                <button onclick="respondToKnock(true)" style="flex: 1; background: var(--brand-green); color: white; font-weight: 800; border: none; border-radius: 12px; padding: 12px; cursor: pointer; font-size: 13px;">
                    ✅ {{ __('Allow Entry') }}
                </button>
                <button onclick="respondToKnock(false)" style="background: rgba(239, 68, 68, 0.15); border: 1px solid rgba(239, 68, 68, 0.4); color: #fca5a5; font-weight: 700; border-radius: 12px; padding: 12px 18px; cursor: pointer; font-size: 13px;">
                    ❌ {{ __('Deny') }}
                </button>
            </div>
        </div>
    </div>

    <!-- Invitation & Member Modal -->
    <div id="invite-modal" class="modal-overlay">
        <div class="modal-box">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
                <h3 style="font-size: 16px; font-weight: 800; color: var(--text-primary);">👥 {{ __('Invite & Guest Access') }}</h3>
                <button onclick="closeInviteModal()" style="background: none; border: none; color: var(--text-muted); font-size: 20px; cursor: pointer;">✕</button>
            </div>

            <!-- Tab Switcher -->
            <div style="display: flex; gap: 8px; margin-bottom: 18px; background: var(--bg-input); padding: 4px; border-radius: 12px;">
                <button class="modal-tab-btn active" id="modal-tab-guest" onclick="switchInviteTab('guest')" style="flex: 1; padding: 8px; border-radius: 8px; border: none; font-size: 12px; font-weight: 700; cursor: pointer; background: var(--brand-primary); color: white;">🔗 {{ __('Guest Meeting Link') }}</button>
                <button class="modal-tab-btn" id="modal-tab-member" onclick="switchInviteTab('member')" style="flex: 1; padding: 8px; border-radius: 8px; border: none; font-size: 12px; font-weight: 700; cursor: pointer; background: none; color: var(--text-muted);">👤 {{ __('Add Team Member') }}</button>
            </div>

            <!-- Section 1: Guest Link -->
            <div id="tab-section-guest" style="display: flex; flex-direction: column; gap: 12px;">
                <div>
                    <label style="display: block; font-size: 11px; font-weight: 700; color: var(--text-secondary); margin-bottom: 4px;">{{ __('Destination Room') }}</label>
                    <select id="invite-room-select" style="width: 100%; background: var(--bg-input); border: 1px solid var(--border-color); border-radius: 8px; padding: 10px; color: var(--text-primary); outline: none; font-size: 13px; font-weight: 600;">
                        @foreach($map->rooms as $r)
                            <option value="{{ $r->id }}">🏢 {{ $r->name }} ({{ ucfirst($r->type) }})</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label style="display: block; font-size: 11px; font-weight: 700; color: var(--text-secondary); margin-bottom: 4px;">{{ __('Guest Name / Label') }}</label>
                    <input type="text" id="invite-guest-name" value="Investor / Partner" style="width: 100%; background: var(--bg-input); border: 1px solid var(--border-color); border-radius: 8px; padding: 10px; color: var(--text-primary); outline: none; font-size: 13px; font-weight: 600;">
                </div>
                <div>
                    <label style="display: block; font-size: 11px; font-weight: 700; color: var(--text-secondary); margin-bottom: 4px;">{{ __('Link Expiration') }}</label>
                    <select id="invite-guest-hours" style="width: 100%; background: var(--bg-input); border: 1px solid var(--border-color); border-radius: 8px; padding: 10px; color: var(--text-primary); outline: none; font-size: 13px; font-weight: 600;">
                        <option value="1">1 {{ __('Hour') }}</option>
                        <option value="24" selected>24 {{ __('Hours') }} (1 Day)</option>
                        <option value="72">72 {{ __('Hours') }} (3 Days)</option>
                    </select>
                </div>
                <button onclick="generateGuestLink()" id="btn-gen-guest" style="margin-top: 4px; background: linear-gradient(135deg, var(--brand-green), #059669); color: white; font-weight: 800; border: none; border-radius: 10px; padding: 12px; cursor: pointer; font-size: 13px; box-shadow: 0 4px 12px rgba(16, 185, 129, 0.25);">
                    ⚡ {{ __('Generate Instant Guest Link') }}
                </button>
                <div id="guest-result-box" style="display: none; background: rgba(16, 185, 129, 0.1); border: 1px solid rgba(16, 185, 129, 0.3); border-radius: 10px; padding: 12px;">
                    <div style="font-size: 11px; font-weight: 800; color: #34d399; margin-bottom: 6px;">✅ {{ __('Invitation Link Ready!') }}</div>
                    <input type="text" id="guest-link-output" readonly style="width: 100%; background: var(--bg-input); border: 1px solid var(--border-color); border-radius: 6px; padding: 8px; color: var(--brand-teal); font-size: 12px; font-family: monospace; margin-bottom: 8px;">
                    <div style="display: flex; gap: 8px;">
                        <button onclick="copyGuestLink()" id="btn-copy-link" style="flex: 1; background: var(--brand-primary); color: white; font-weight: 700; border: none; border-radius: 6px; padding: 8px; cursor: pointer; font-size: 12px;">📋 {{ __('Copy Link') }}</button>
                        <a id="guest-open-link" href="#" target="_blank" style="background: var(--bg-input); border: 1px solid var(--border-color); color: var(--text-primary); font-weight: 700; text-decoration: none; border-radius: 6px; padding: 8px 12px; font-size: 12px;">👁️ {{ __('Open') }}</a>
                    </div>
                </div>
            </div>

            <!-- Section 2: Permanent Team Member -->
            <div id="tab-section-member" style="display: none; flex-direction: column; gap: 12px;">
                <div>
                    <label style="display: block; font-size: 11px; font-weight: 700; color: var(--text-secondary); margin-bottom: 4px;">{{ __('Full Name') }}</label>
                    <input type="text" id="member-name" placeholder="e.g. Sarah Miller" style="width: 100%; background: var(--bg-input); border: 1px solid var(--border-color); border-radius: 8px; padding: 10px; color: var(--text-primary); outline: none; font-size: 13px; font-weight: 600;">
                </div>
                <div>
                    <label style="display: block; font-size: 11px; font-weight: 700; color: var(--text-secondary); margin-bottom: 4px;">{{ __('Email Address') }}</label>
                    <input type="email" id="member-email" placeholder="sarah@example.com" style="width: 100%; background: var(--bg-input); border: 1px solid var(--border-color); border-radius: 8px; padding: 10px; color: var(--text-primary); outline: none; font-size: 13px; font-weight: 600;">
                </div>
                <div>
                    <label style="display: block; font-size: 11px; font-weight: 700; color: var(--text-secondary); margin-bottom: 4px;">{{ __('Password') }}</label>
                    <input type="text" id="member-password" placeholder="e.g. Secret123" style="width: 100%; background: var(--bg-input); border: 1px solid var(--border-color); border-radius: 8px; padding: 10px; color: var(--text-primary); outline: none; font-size: 13px; font-weight: 600;">
                </div>
                <div>
                    <label style="display: block; font-size: 11px; font-weight: 700; color: var(--text-secondary); margin-bottom: 4px;">{{ __('Role') }}</label>
                    <select id="member-role" style="width: 100%; background: var(--bg-input); border: 1px solid var(--border-color); border-radius: 8px; padding: 10px; color: var(--text-primary); outline: none; font-size: 13px; font-weight: 600;">
                        <option value="employee" selected>{{ __('Member') }}</option>
                        <option value="manager">{{ __('Manager') }}</option>
                        <option value="company_admin">{{ __('Company Admin') }}</option>
                    </select>
                </div>
                <button onclick="createTeamMember()" id="btn-create-member" style="margin-top: 4px; background: var(--brand-primary); color: white; font-weight: 800; border: none; border-radius: 10px; padding: 12px; cursor: pointer; font-size: 13px;">
                    ✨ {{ __('Add Member to Team') }}
                </button>
                <div id="member-result-box" style="display: none; background: rgba(59, 130, 246, 0.1); border: 1px solid rgba(59, 130, 246, 0.3); border-radius: 10px; padding: 12px; font-size: 12px; color: var(--text-primary);">
                    <div style="font-weight: 800; margin-bottom: 4px; color: #60a5fa;">✅ {{ __('Member Created Successfully!') }}</div>
                    <div>{{ __('The member can now login directly at /login') }}</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Whiteboard Modal -->
    <div id="whiteboard-modal" class="modal-overlay">
        <div style="background: var(--bg-surface); border: 1px solid var(--border-color); border-radius: 24px; width: 90vw; max-width: 1000px; height: 80vh; display: flex; flex-direction: column; overflow: hidden; box-shadow: var(--shadow-dock);">
            <div style="display: flex; align-items: center; justify-content: space-between; padding: 14px 22px; background: var(--bg-input); border-bottom: 1px solid var(--border-color);">
                <div style="display: flex; align-items: center; gap: 8px; font-weight: 800; color: var(--text-primary);">
                    <span>📋</span> {{ __('Team Collaborative Whiteboard') }}
                </div>
                <div style="display: flex; align-items: center; gap: 8px;">
                    <button class="tool-btn active" id="wb-tool-pen" onclick="setWbTool('pen')" title="Pen">✏️</button>
                    <button class="tool-btn" id="wb-tool-highlighter" onclick="setWbTool('highlighter')" title="Highlighter">🖍️</button>
                    <button class="tool-btn" id="wb-tool-eraser" onclick="setWbTool('eraser')" title="Eraser">🧹</button>
                    <div style="display: flex; gap: 6px; margin: 0 6px;">
                        <span onclick="setWbColor('#3b82f6')" style="width: 20px; height: 20px; border-radius: 50%; background: #3b82f6; cursor: pointer;"></span>
                        <span onclick="setWbColor('#10b981')" style="width: 20px; height: 20px; border-radius: 50%; background: #10b981; cursor: pointer;"></span>
                        <span onclick="setWbColor('#f59e0b')" style="width: 20px; height: 20px; border-radius: 50%; background: #f59e0b; cursor: pointer;"></span>
                        <span onclick="setWbColor('#ef4444')" style="width: 20px; height: 20px; border-radius: 50%; background: #ef4444; cursor: pointer;"></span>
                    </div>
                    <button class="tool-btn" onclick="clearWhiteboard()" title="Clear Board" style="color: var(--brand-crimson);">🗑️</button>
                    <button class="tool-btn" onclick="exportWhiteboard()" title="Download PNG">💾</button>
                    <button onclick="closeWhiteboard()" style="background: none; border: none; color: var(--text-muted); font-size: 20px; cursor: pointer; margin-inline-start: 10px;">✕</button>
                </div>
            </div>
            <div style="flex: 1; position: relative; background: #ffffff;" id="wb-container">
                <canvas id="wb-canvas" style="width: 100%; height: 100%; cursor: crosshair;"></canvas>
            </div>
        </div>
    </div>

    <!-- Presentation Modal -->
    <div id="presentation-modal" class="modal-overlay">
        <div style="background: rgba(15, 23, 42, 0.98); border: 1px solid var(--border-color); border-radius: 20px; width: 85vw; max-width: 1100px; height: 80vh; display: flex; flex-direction: column; overflow: hidden;">
            <div style="display: flex; justify-content: space-between; align-items: center; padding: 14px 20px; border-bottom: 1px solid var(--border-color); background: rgba(0,0,0,0.4);">
                <div style="display: flex; align-items: center; gap: 8px;">
                    <span>🖥️</span> <strong>Presentation / Screen Share</strong>
                </div>
                <div style="display: flex; gap: 10px;">
                    <button onclick="stopPresentation()" style="background: #ef4444; color: white; border: none; padding: 6px 14px; border-radius: 8px; font-size: 12px; font-weight: 700; cursor: pointer;">⏹️ Stop Sharing</button>
                    <button onclick="closePresentationModal()" style="background: none; border: none; color: #94a3b8; font-size: 20px; cursor: pointer;">✕</button>
                </div>
            </div>
            <div style="flex: 1; display: flex; align-items: center; justify-content: center; background: #000;">
                <video id="presentation-video" autoplay playsinline style="max-width: 100%; max-height: 100%;"></video>
            </div>
        </div>
    </div>

    <!-- 📼 Recordings & Media Gallery Modal -->
    <div id="recordings-gallery-modal" class="modal-overlay">
        <div style="background: var(--bg-surface); border: 1px solid var(--border-color); border-radius: 24px; width: 90vw; max-width: 1000px; height: 85vh; display: flex; flex-direction: column; overflow: hidden; box-shadow: var(--shadow-dock);">
            <div style="display: flex; align-items: center; justify-content: space-between; padding: 16px 24px; background: var(--bg-input); border-bottom: 1px solid var(--border-color);">
                <div style="display: flex; align-items: center; gap: 10px; font-weight: 800; font-size: 16px; color: var(--text-primary);">
                    <span>📼</span> {{ __('Session Recordings & Gallery') }}
                </div>
                <button onclick="closeRecordingsGallery()" style="background: none; border: none; color: var(--text-muted); font-size: 22px; cursor: pointer;">✕</button>
            </div>
            <div style="flex: 1; overflow-y: auto; padding: 22px;" id="recordings-gallery-content">
                <div style="display: flex; justify-content: center; padding: 40px 0; color: var(--text-muted);">
                    ⏳ {{ __('Loading saved recordings from server...') }}
                </div>
            </div>
        </div>
    </div>

    <!-- JavaScript Realtime Engine & High-Fidelity Canvas Rendering -->
    <script>
        // ── Global Theme Toggle Engine ──
        function toggleAppTheme() {
            const current = document.documentElement.getAttribute('data-theme') || 'dark';
            const next = current === 'dark' ? 'light' : 'dark';
            document.documentElement.setAttribute('data-theme', next);
            localStorage.setItem('vw_theme', next);
            updateThemeIcons(next);
            if (typeof draw === 'function') draw();
        }

        function updateThemeIcons(theme) {
            const sideIcon = document.getElementById('sidebar-theme-icon');
            const floatIcon = document.getElementById('floating-theme-icon');
            const floatText = document.getElementById('floating-theme-text');
            if (sideIcon) sideIcon.textContent = theme === 'dark' ? '☀️' : '🌙';
            if (floatIcon) floatIcon.textContent = theme === 'dark' ? '☀️' : '🌙';
            if (floatText) floatText.textContent = theme === 'dark' ? '{{ __("Light Mode") }}' : '{{ __("Dark Mode") }}';
        }
        updateThemeIcons(savedTheme);

        // ── Sidebar Tabs ──
        function switchSidebarTab(tab) {
            ['chat', 'occupants', 'help'].forEach(t => {
                const btn = document.getElementById(`tab-btn-${t}`);
                const content = document.getElementById(`tab-content-${t}`);
                if (btn) btn.classList.toggle('active', t === tab);
                if (content) content.style.display = t === tab ? 'flex' : 'none';
            });
        }

        // ── Realtime & Map Configuration ──
        const wsProtocol = window.location.protocol === 'https:' ? 'wss:' : 'ws:';
        const wsHost = window.location.hostname || '127.0.0.1';
        const wsPort = 8080;
        const dynamicWsUrl = `${wsProtocol}//${wsHost}:${wsPort}`;

        const CONFIG = {
            wsUrl: dynamicWsUrl,
            token: "{{ $realtimeToken }}",
            map: @json($map),
            currentUser: @json($user),
            org: @json($organization)
        };

        const canvas = document.getElementById('office-canvas');
        const ctx = canvas.getContext('2d');
        const container = canvas.parentElement;

        let width = canvas.width = (container?.clientWidth) || window.innerWidth || 1200;
        let height = canvas.height = (container?.clientHeight) || window.innerHeight || 800;

        function resizeCanvas() {
            if (canvas) {
                width = canvas.width = (container?.clientWidth) || window.innerWidth || 1200;
                height = canvas.height = (container?.clientHeight) || window.innerHeight || 800;
            }
        }
        window.addEventListener('resize', resizeCanvas);
        setTimeout(resizeCanvas, 150);

        // ── Preloaded 2.5D Avatars (Nanobanana Sprites) ──
        const AVATAR_SPRITES = {
            male: new Image(),
            female: new Image()
        };
        AVATAR_SPRITES.male.src = '/images/avatars/male.jpg';
        AVATAR_SPRITES.female.src = '/images/avatars/female.jpg';

        let chosenAvatarGender = localStorage.getItem('vw_avatar_gender') || 'male';

        function openAvatarPickerModal() {
            selectAvatarGender(chosenAvatarGender);
            document.getElementById('avatar-picker-modal').style.display = 'flex';
        }
        function closeAvatarPickerModal() {
            document.getElementById('avatar-picker-modal').style.display = 'none';
        }

        // ── Distinct Spawn & Color Engine ──
        const isGuest = {{ !empty($user->is_guest) ? 'true' : 'false' }};
        const overrideSpawn = @json($initialSpawn ?? null);

        function calculateInitialSpawn(uid, isG, override) {
            if (override && typeof override.x === 'number') return override;
            if (isG) return { x: 260, y: 260 };
            let hash = 0;
            const str = String(uid || '');
            for (let i = 0; i < str.length; i++) hash = ((hash << 5) - hash) + str.charCodeAt(i);
            const col = Math.abs(hash) % 4;
            const row = Math.abs(hash >> 3) % 3;
            return {
                x: 380 + col * 130,
                y: 380 + row * 110
            };
        }

        const mySpawn = calculateInitialSpawn(String(CONFIG.currentUser.id), isGuest, overrideSpawn);

        const localAvatar = {
            id: String(CONFIG.currentUser.id),
            name: CONFIG.currentUser.name || 'User',
            isGuest: isGuest,
            x: mySpawn.x,
            y: mySpawn.y,
            targetX: mySpawn.x,
            targetY: mySpawn.y,
            speed: 5.2,
            radius: 24,
            proximityRadius: 170,
            currentRoomId: null,
            status: 'available'
        };

        const remoteAvatars = new Map();
        const rooms = CONFIG.map.rooms || [];
        const roomDoorStates = new Map();

        const FURNITURE_CATALOG = @json($furnitureItems ?? []);
        const CUSTOM_FURNITURE_SPRITES = {};
        FURNITURE_CATALOG.forEach(it => {
            if (it.image_url) {
                const img = new Image();
                img.src = it.image_url;
                img.onload = () => { if (typeof draw === 'function') draw(); };
                CUSTOM_FURNITURE_SPRITES[it.slug] = { img, width: it.width || 1, height: it.height || 1 };
            }
        });

        let zoomLevel = 1.0;
        let cameraOffset = { x: 0, y: 0 };
        setTimeout(() => {
            if (width && height && localAvatar) {
                cameraOffset.x = (width / 2) - localAvatar.x * zoomLevel;
                cameraOffset.y = (height / 2) - localAvatar.y * zoomLevel;
            }
        }, 150);

        // ── Web Audio Synthesizer ──
        let audioCtx = null;
        function getAudioContext() {
            if (!audioCtx) audioCtx = new (window.AudioContext || window.webkitAudioContext)();
            return audioCtx;
        }

        function playKnockSound() {
            try {
                const ctx = getAudioContext();
                [0, 0.14].forEach(delay => {
                    const osc = ctx.createOscillator();
                    const gain = ctx.createGain();
                    osc.type = 'triangle';
                    osc.frequency.setValueAtTime(140, ctx.currentTime + delay);
                    osc.frequency.exponentialRampToValueAtTime(40, ctx.currentTime + delay + 0.1);
                    gain.gain.setValueAtTime(0.7, ctx.currentTime + delay);
                    gain.gain.exponentialRampToValueAtTime(0.01, ctx.currentTime + delay + 0.1);
                    osc.connect(gain);
                    gain.connect(ctx.destination);
                    osc.start(ctx.currentTime + delay);
                    osc.stop(ctx.currentTime + delay + 0.12);
                });
            } catch(e) {}
        }

        function playChimeSound() {
            try {
                const ctx = getAudioContext();
                [ { f: 587.33, d: 0 }, { f: 880, d: 0.18 } ].forEach(tone => {
                    const osc = ctx.createOscillator();
                    const gain = ctx.createGain();
                    osc.type = 'sine';
                    osc.frequency.setValueAtTime(tone.f, ctx.currentTime + tone.d);
                    gain.gain.setValueAtTime(0.4, ctx.currentTime + tone.d);
                    gain.gain.exponentialRampToValueAtTime(0.001, ctx.currentTime + tone.d + 0.5);
                    osc.connect(gain);
                    gain.connect(ctx.destination);
                    osc.start(ctx.currentTime + tone.d);
                    osc.stop(ctx.currentTime + tone.d + 0.55);
                });
            } catch(e) {}
        }

        // ── WebRTC Peer Mesh & Spatial Audio ──
        const RTC_CONFIG = {
            iceServers: [
                { urls: 'stun:stun.l.google.com:19302' },
                { urls: 'stun:stun1.l.google.com:19302' }
            ]
        };
        const peerConnections = new Map();
        const peerAudioElements = new Map();
        let isCamActive = false;
        let isMicActive = false;
        let localMediaStream = null;
        let presentStream = null;
        let activeRemotePresenterId = null;

        async function getLocalMediaStream() {
            if (!localMediaStream) {
                try {
                    localMediaStream = await navigator.mediaDevices.getUserMedia({
                        video: { width: 320, height: 240, frameRate: 24 },
                        audio: true
                    });
                    localMediaStream.getVideoTracks().forEach(t => t.enabled = isCamActive);
                    localMediaStream.getAudioTracks().forEach(t => t.enabled = isMicActive);
                    
                    const localVid = document.getElementById('local-video-preview');
                    if (localVid) {
                        localVid.srcObject = localMediaStream;
                        localVid.play().catch(() => {});
                    }
                } catch (e) {
                    console.log('[Media] Device access note:', e.message);
                }
            }
            return localMediaStream;
        }

        async function toggleMicrophone() {
            isMicActive = !isMicActive;
            const btn = document.getElementById('mic-btn');
            if (!localMediaStream) {
                await getLocalMediaStream();
            }
            if (localMediaStream) {
                localMediaStream.getAudioTracks().forEach(t => t.enabled = isMicActive);
                peerConnections.forEach(pc => {
                    const senders = pc.getSenders();
                    const audioTrack = localMediaStream.getAudioTracks()[0];
                    if (audioTrack && !senders.find(s => s.track && s.track.kind === 'audio')) {
                        pc.addTrack(audioTrack, localMediaStream);
                    }
                });
            }
            if (isMicActive) {
                btn?.classList.add('active');
                showToast('🎙️ {{ __("Microphone Unmuted") }}');
            } else {
                btn?.classList.remove('active');
                showToast('🔇 {{ __("Microphone Muted") }}');
            }
        }

        async function toggleCamera() {
            isCamActive = !isCamActive;
            const btn = document.getElementById('cam-btn');
            const localCont = document.getElementById('local-video-container');
            const grid = document.getElementById('office-video-grid');

            if (!localMediaStream) {
                await getLocalMediaStream();
            }
            if (localMediaStream) {
                localMediaStream.getVideoTracks().forEach(t => t.enabled = isCamActive);
                peerConnections.forEach(pc => {
                    const senders = pc.getSenders();
                    const videoTrack = localMediaStream.getVideoTracks()[0];
                    if (videoTrack) {
                        const s = senders.find(s => s.track && s.track.kind === 'video');
                        if (s) s.replaceTrack(isCamActive ? videoTrack : null);
                        else if (isCamActive) pc.addTrack(videoTrack, localMediaStream);
                    }
                });
            }

            if (isCamActive) {
                btn?.classList.add('active');
                if (localCont) localCont.style.display = 'block';
                if (grid) grid.style.display = 'flex';
                showToast('📹 {{ __("Camera Turned ON") }}');
            } else {
                btn?.classList.remove('active');
                if (localCont) localCont.style.display = 'none';
                showToast('📷 {{ __("Camera Turned OFF") }}');
            }
        }

        function createPeerConnection(peerUserId) {
            if (peerConnections.has(peerUserId)) return peerConnections.get(peerUserId);

            const pc = new RTCPeerConnection(RTC_CONFIG);
            peerConnections.set(peerUserId, pc);

            if (localMediaStream) {
                localMediaStream.getTracks().forEach(track => pc.addTrack(track, localMediaStream));
            }

            pc.onicecandidate = (e) => {
                if (e.candidate && ws && ws.readyState === WebSocket.OPEN) {
                    ws.send(JSON.stringify({
                        type: 'webrtc.signal',
                        payload: { targetUserId: peerUserId, signal: { type: 'candidate', candidate: e.candidate } }
                    }));
                }
            };

            pc.ontrack = (e) => {
                const remoteStream = e.streams[0] || new MediaStream([e.track]);

                if (e.track.kind === 'audio') {
                    let audioEl = peerAudioElements.get(peerUserId);
                    if (!audioEl) {
                        audioEl = new Audio();
                        audioEl.autoplay = true;
                        audioEl.srcObject = remoteStream;
                        peerAudioElements.set(peerUserId, audioEl);
                    } else {
                        audioEl.srcObject = remoteStream;
                    }
                }

                if (e.track.kind === 'video') {
                    let remoteVidTile = document.getElementById(`peer-video-tile-${peerUserId}`);
                    const grid = document.getElementById('office-video-grid');
                    if (!remoteVidTile && grid) {
                        remoteVidTile = document.createElement('div');
                        remoteVidTile.className = 'video-tile';
                        remoteVidTile.id = `peer-video-tile-${peerUserId}`;
                        const av = remoteAvatars.get(peerUserId);
                        const name = av?.name || 'Colleague';
                        remoteVidTile.innerHTML = `
                            <video id="peer-vid-${peerUserId}" autoplay playsinline></video>
                            <div class="video-tile-name">${escapeHtml(name)}</div>
                        `;
                        grid.appendChild(remoteVidTile);
                        grid.style.display = 'flex';
                    }
                    const vidEl = document.getElementById(`peer-vid-${peerUserId}`);
                    if (vidEl) {
                        vidEl.srcObject = remoteStream;
                        vidEl.play().catch(() => {});
                    }
                    const av = remoteAvatars.get(peerUserId);
                    if (av) av.videoElement = vidEl;
                }
            };

            return pc;
        }

        async function callPeer(peerUserId) {
            const pc = createPeerConnection(peerUserId);
            try {
                const offer = await pc.createOffer({ offerToReceiveAudio: true, offerToReceiveVideo: true });
                await pc.setLocalDescription(offer);
                if (ws && ws.readyState === WebSocket.OPEN) {
                    ws.send(JSON.stringify({
                        type: 'webrtc.signal',
                        payload: { targetUserId: peerUserId, signal: { type: 'offer', sdp: pc.localDescription } }
                    }));
                }
            } catch(err) { console.error('[WebRTC] Call peer error:', err); }
        }

        // ── WebSocket Realtime Connection ──
        let ws = null;
        function connectWebSocket() {
            try {
                ws = new WebSocket(`${CONFIG.wsUrl}?token=${CONFIG.token}`);

                ws.onopen = () => {
                    console.log('[WS] Virtual Workplace live connected.');
                    ws.send(JSON.stringify({
                        type: 'map.join',
                        payload: {
                            mapId: CONFIG.map.id,
                            gender: chosenAvatarGender,
                            isGuest: isGuest,
                            initialPosition: { x: Math.round(localAvatar.x), y: Math.round(localAvatar.y), direction: 'down' }
                        }
                    }));

                    setInterval(() => {
                        if (ws && ws.readyState === WebSocket.OPEN) {
                            ws.send(JSON.stringify({
                                type: 'position.update',
                                payload: { x: Math.round(localAvatar.x), y: Math.round(localAvatar.y), direction: 'down', isMoving: false }
                            }));
                        }
                    }, 1000);
                };

                ws.onmessage = (event) => {
                    try {
                        const msg = JSON.parse(event.data);
                        handleWebSocketMessage(msg);
                    } catch(e) { console.error('WS parse error:', e); }
                };

                ws.onclose = () => { setTimeout(connectWebSocket, 2000); };
            } catch(err) {
                console.error('WS connection error:', err);
                setTimeout(connectWebSocket, 2000);
            }
        }
        connectWebSocket();

        let pendingKnockRequesterId = null;
        let pendingKnockRoomId = null;
        let activeTargetKnockRoom = null;

        async function handleWebSocketMessage(event) {
            switch(event.type) {
                case 'welcome': {
                    const { occupants } = event.payload || {};
                    (occupants || []).forEach(u => {
                        const uid = String(u.userId || u.id || '');
                        if (uid && uid !== String(localAvatar.id)) {
                            const isG = (u.name && u.name.includes('(Guest)')) || u.role === 'Guest' || u.isGuest;
                            const defaultSpawn = calculateInitialSpawn(uid, isG, null);
                            const px = (typeof u.position?.x === 'number' && !isNaN(u.position.x) && u.position.x > 0) ? u.position.x : defaultSpawn.x;
                            const py = (typeof u.position?.y === 'number' && !isNaN(u.position.y) && u.position.y > 0) ? u.position.y : defaultSpawn.y;
                            remoteAvatars.set(uid, {
                                id: uid,
                                name: u.name || 'Colleague',
                                isGuest: isG,
                                gender: u.gender || (Math.random() > 0.5 ? 'female' : 'male'),
                                x: px, y: py, targetX: px, targetY: py,
                                status: u.status || 'available',
                                currentRoomId: u.currentRoomId || null
                            });
                            if (localMediaStream) callPeer(uid);
                        }
                    });
                    updateOccupantsUI();
                    break;
                }
                case 'user.joined': {
                    const u = event.payload?.user || event.payload;
                    if (u) {
                        const uid = String(u.userId || u.id || '');
                        if (uid && uid !== String(localAvatar.id)) {
                            const isG = (u.name && u.name.includes('(Guest)')) || u.role === 'Guest' || u.isGuest;
                            const defaultSpawn = calculateInitialSpawn(uid, isG, null);
                            const px = (typeof u.position?.x === 'number' && !isNaN(u.position.x) && u.position.x > 0) ? u.position.x : defaultSpawn.x;
                            const py = (typeof u.position?.y === 'number' && !isNaN(u.position.y) && u.position.y > 0) ? u.position.y : defaultSpawn.y;
                            remoteAvatars.set(uid, {
                                id: uid,
                                name: u.name || 'Colleague',
                                isGuest: isG,
                                gender: u.gender || (Math.random() > 0.5 ? 'female' : 'male'),
                                x: px, y: py, targetX: px, targetY: py,
                                status: u.status || 'available',
                                currentRoomId: u.currentRoomId || null
                            });
                            updateOccupantsUI();
                            callPeer(uid);
                        }
                    }
                    break;
                }
                case 'user.left': {
                    const uid = String(event.payload?.userId || event.payload?.id || event.payload || '');
                    if (uid) {
                        remoteAvatars.delete(uid);
                        updateOccupantsUI();
                        document.getElementById(`peer-video-tile-${uid}`)?.remove();
                        if (peerConnections.has(uid)) {
                            peerConnections.get(uid).close();
                            peerConnections.delete(uid);
                        }
                        if (peerAudioElements.has(uid)) {
                            peerAudioElements.get(uid).srcObject = null;
                            peerAudioElements.delete(uid);
                        }
                    }
                    break;
                }
                case 'position.updated': {
                    const { userId, position } = event.payload || {};
                    const uid = String(userId || '');
                    if (uid && uid !== String(localAvatar.id)) {
                        let av = remoteAvatars.get(uid);
                        if (av && position) {
                            av.targetX = Number(position.x) || av.targetX;
                            av.targetY = Number(position.y) || av.targetY;
                        }
                    }
                    break;
                }
                case 'room.door_updated': {
                    const { roomId, isClosed } = event.payload || {};
                    if (roomId) {
                        roomDoorStates.set(roomId, !!isClosed);
                        updateRoomDoorPill();
                    }
                    break;
                }
                case 'room.knock_request': {
                    const { roomId, roomName, requesterUserId, requesterName } = event.payload || {};
                    if (requesterUserId === String(localAvatar.id)) break;

                    const myCurrentRoom = getCurrentRoom(localAvatar.x, localAvatar.y);
                    if (!myCurrentRoom || myCurrentRoom.id === roomId || roomDoorStates.get(roomId)) {
                        pendingKnockRequesterId = requesterUserId;
                        pendingKnockRoomId = roomId;
                        playKnockSound();
                        
                        document.getElementById('knock-alert-desc').innerHTML = `
                            <strong>${escapeHtml(requesterName || 'A colleague')}</strong> {{ __('is knocking on the door to enter') }} <strong>${escapeHtml(roomName || 'this room')}</strong>.
                        `;
                        document.getElementById('knock-alert-modal').style.display = 'flex';
                    }
                    break;
                }
                case 'room.knock_result': {
                    const { roomId, approved, responderName } = event.payload || {};
                    const msgEl = document.getElementById('knock-status-msg');
                    if (approved) {
                        playChimeSound();
                        if (msgEl) {
                            msgEl.style.display = 'block';
                            msgEl.style.color = '#34d399';
                            msgEl.innerHTML = `✅ <strong>{{ __('Entry Approved!') }}</strong> {{ __('Door opened by') }} ${escapeHtml(responderName || 'host')}.`;
                        }
                        const targetR = rooms.find(r => r.id === roomId);
                        if (targetR) {
                            const cx = (targetR.bounds.x + targetR.bounds.width / 2) * 32;
                            const cy = (targetR.bounds.y + targetR.bounds.height / 2) * 32;
                            localAvatar.targetX = cx;
                            localAvatar.targetY = cy;
                        }
                        setTimeout(closeKnockPrompt, 1500);
                    } else {
                        if (msgEl) {
                            msgEl.style.display = 'block';
                            msgEl.style.color = '#f87171';
                            msgEl.innerHTML = `❌ <strong>{{ __('Knock Denied') }}</strong>. {{ __('Occupants are in a confidential session.') }}`;
                        }
                    }
                    break;
                }
                case 'chat.message': {
                    const { senderName, body, fileUrl, fileName } = event.payload || {};
                    if (senderName && !senderName.includes('(You)')) {
                        renderChatMessage(senderName, body, fileUrl, fileName);
                    }
                    break;
                }
                case 'webrtc.signal': {
                    const { senderUserId, signal } = event.payload || {};
                    if (!senderUserId || !signal) break;
                    let pc = peerConnections.get(senderUserId);
                    if (!pc) {
                        pc = createPeerConnection(senderUserId);
                    }
                    if (signal.type === 'offer') {
                        try {
                            await pc.setRemoteDescription(new RTCSessionDescription(signal.sdp));
                            if (localMediaStream) {
                                const senders = pc.getSenders();
                                localMediaStream.getTracks().forEach(track => {
                                    if (!senders.find(s => s.track && s.track.kind === track.kind)) {
                                        pc.addTrack(track, localMediaStream);
                                    }
                                });
                            }
                            const answer = await pc.createAnswer();
                            await pc.setLocalDescription(answer);
                            if (ws && ws.readyState === WebSocket.OPEN) {
                                ws.send(JSON.stringify({
                                    type: 'webrtc.signal',
                                    payload: {
                                        targetUserId: senderUserId,
                                        signal: { type: 'answer', sdp: pc.localDescription }
                                    }
                                }));
                            }
                        } catch(err) { console.warn('[WebRTC] Offer error:', err); }
                    } else if (signal.type === 'answer') {
                        try {
                            await pc.setRemoteDescription(new RTCSessionDescription(signal.sdp));
                        } catch(err) { console.warn('[WebRTC] Answer error:', err); }
                    } else if (signal.type === 'candidate' && signal.candidate) {
                        try {
                            await pc.addIceCandidate(new RTCIceCandidate(signal.candidate));
                        } catch(err) { console.warn('[WebRTC] Candidate error:', err); }
                    }
                    break;
                }
                case 'avatar.updated': {
                    const { userId, gender } = event.payload || {};
                    if (userId && remoteAvatars.has(userId)) {
                        remoteAvatars.get(userId).gender = gender;
                        if (typeof draw === 'function') draw();
                    }
                    break;
                }
                case 'presentation.started': {
                    const { presenterId, presenterName } = event.payload || {};
                    activeRemotePresenterId = presenterId;
                    showToast(`🖥️ ${escapeHtml(presenterName || 'Colleague')} {{ __('started screen presentation') }}`);
                    break;
                }
                case 'presentation.stopped': {
                    activeRemotePresenterId = null;
                    closePresentationModal();
                    showToast('⏹️ {{ __('Screen presentation ended') }}');
                    break;
                }
            }
        }

        function updateOccupantsUI() {
            const countEl = document.getElementById('floor-occupants-count');
            if (countEl) countEl.textContent = `${remoteAvatars.size + 1} {{ __('Online') }}`;

            const dir = document.getElementById('occupants-directory');
            if (!dir) return;

            // Group occupants by rooms
            let roomsMap = { 'floor': [] };
            rooms.forEach(r => { roomsMap[r.id] = []; });

            // Add local user
            const myRoom = getCurrentRoom(localAvatar.x, localAvatar.y);
            const myRoomKey = myRoom ? myRoom.id : 'floor';
            if (!roomsMap[myRoomKey]) roomsMap[myRoomKey] = [];
            roomsMap[myRoomKey].push({ name: localAvatar.name, isGuest: localAvatar.isGuest, isMe: true });

            // Add remote users
            remoteAvatars.forEach(av => {
                const r = getCurrentRoom(av.x, av.y);
                const rKey = r ? r.id : 'floor';
                if (!roomsMap[rKey]) roomsMap[rKey] = [];
                roomsMap[rKey].push({ name: av.name, isGuest: av.isGuest, isMe: false });
            });

            let html = `
                <div class="room-group-card">
                    <div class="room-group-header">
                        <span>🏢 {{ __('Open Office Floor') }}</span>
                        <span style="color: var(--brand-teal); font-size: 11px;">${roomsMap['floor'].length}</span>
                    </div>
                    <div style="display: flex; flex-direction: column; gap: 6px;">
                        ${roomsMap['floor'].map(u => `
                            <div class="user-occupant-item">
                                <div class="user-occupant-left">
                                    <span class="status-dot"></span>
                                    <span>${escapeHtml(u.name)}</span>
                                </div>
                                <span class="role-badge ${u.isGuest ? 'badge-guest' : 'badge-host'}" style="font-size: 9px;">
                                    ${u.isGuest ? '👤 {{ __("Guest") }}' : (u.isMe ? '👑 {{ __("You") }}' : '👑 {{ __("Host") }}')}
                                </span>
                            </div>
                        `).join('')}
                    </div>
                </div>
            `;

            rooms.forEach(r => {
                const occupants = roomsMap[r.id] || [];
                const isLocked = !!roomDoorStates.get(r.id);
                html += `
                    <div class="room-group-card">
                        <div class="room-group-header">
                            <span>${isLocked ? '🔒' : '🚪'} ${escapeHtml(r.name)}</span>
                            <span style="color: var(--brand-teal); font-size: 11px;">${occupants.length}</span>
                        </div>
                        <div style="display: flex; flex-direction: column; gap: 6px;">
                            ${occupants.length === 0 ? `<div style="font-size: 11px; color: var(--text-dim); padding: 4px;">— {{ __('Room is empty') }} —</div>` : occupants.map(u => `
                                <div class="user-occupant-item">
                                    <div class="user-occupant-left">
                                        <span class="status-dot"></span>
                                        <span>${escapeHtml(u.name)}</span>
                                    </div>
                                    <span class="role-badge ${u.isGuest ? 'badge-guest' : 'badge-host'}" style="font-size: 9px;">
                                        ${u.isGuest ? '👤 {{ __("Guest") }}' : (u.isMe ? '👑 {{ __("You") }}' : '👑 {{ __("Host") }}')}
                                    </span>
                                </div>
                            `).join('')}
                        </div>
                    </div>
                `;
            });

            dir.innerHTML = html;
        }

        // ── Smooth Room Detection with Hysteresis & Edge Protection ──
        function getCurrentRoom(x, y, activeRoom = null) {
            // Check active room first with generous 4px buffer to eliminate jitter
            if (activeRoom) {
                const rx = activeRoom.bounds.x * 32 - 4;
                const ry = activeRoom.bounds.y * 32 - 4;
                const rw = activeRoom.bounds.width * 32 + 8;
                const rh = activeRoom.bounds.height * 32 + 8;
                if (x >= rx && x <= rx + rw && y >= ry && y <= ry + rh) {
                    return activeRoom;
                }
            }

            const margin = 2;
            for (const r of rooms) {
                const rx = r.bounds.x * 32;
                const ry = r.bounds.y * 32;
                const rw = r.bounds.width * 32;
                const rh = r.bounds.height * 32;
                if (x >= rx + margin && x < rx + rw - margin && y >= ry + margin && y < ry + rh - margin) {
                    return r;
                }
            }
            return null;
        }

        function updateRoomDoorPill() {
            const pill = document.getElementById('room-door-pill');
            if (!pill) return;
            const currentRoom = getCurrentRoom(localAvatar.x, localAvatar.y, localAvatar.currentRoomObject);
            if (currentRoom) {
                pill.style.display = 'flex';
                document.getElementById('room-door-name').textContent = `🏢 ${currentRoom.name}`;
                const isLocked = !!roomDoorStates.get(currentRoom.id);
                const btn = document.getElementById('btn-toggle-room-door');
                const txt = document.getElementById('room-door-status-text');
                if (isLocked) {
                    btn.classList.remove('unlocked');
                    btn.querySelector('span:first-child').textContent = '🔒';
                    txt.textContent = '{{ __("Unlock Door") }}';
                } else {
                    btn.classList.add('unlocked');
                    btn.querySelector('span:first-child').textContent = '🔓';
                    txt.textContent = '{{ __("Lock Door") }}';
                }
            } else {
                pill.style.display = 'none';
            }
        }

        function toggleCurrentRoomDoor() {
            const currentRoom = getCurrentRoom(localAvatar.x, localAvatar.y, localAvatar.currentRoomObject);
            if (!currentRoom) return;
            const isCurrentlyLocked = !!roomDoorStates.get(currentRoom.id);
            const nextState = !isCurrentlyLocked;
            roomDoorStates.set(currentRoom.id, nextState);
            updateRoomDoorPill();

            if (ws && ws.readyState === WebSocket.OPEN) {
                ws.send(JSON.stringify({
                    type: 'room.door_toggle',
                    payload: { roomId: currentRoom.id, isClosed: nextState }
                }));
            }
        }

        function selectAvatarGender(gender) {
            chosenAvatarGender = gender;
            localStorage.setItem('vw_avatar_gender', gender);
            document.querySelectorAll('.avatar-card-picker').forEach(el => el.classList.remove('selected'));
            document.getElementById(`picker-${gender}`)?.classList.add('selected');
            if (ws && ws.readyState === WebSocket.OPEN) {
                ws.send(JSON.stringify({
                    type: 'avatar.update',
                    payload: { gender: gender }
                }));
            }
            if (typeof draw === 'function') draw();
            showToast(`🎭 {{ __('Avatar set to') }} ${gender === 'female' ? '👩 {{ __("Executive Female") }}' : '👨 {{ __("Business Male") }}'}`);
        }

        // ── Screen Sharing Engine ──
        let screenMediaStream = null;
        let isScreenSharing = false;

        async function toggleScreenShare() {
            if (isScreenSharing) {
                stopPresentation();
                return;
            }

            try {
                screenMediaStream = await navigator.mediaDevices.getDisplayMedia({
                    video: { cursor: "always" },
                    audio: true
                });

                isScreenSharing = true;
                document.getElementById('present-btn')?.classList.add('active');
                document.getElementById('present-btn-text').textContent = '{{ __("Stop Share") }}';

                const presVid = document.getElementById('presentation-video');
                if (presVid) {
                    presVid.srcObject = screenMediaStream;
                    presVid.play().catch(() => {});
                    document.getElementById('presentation-modal').style.display = 'flex';
                }

                const screenTrack = screenMediaStream.getVideoTracks()[0];
                if (screenTrack) {
                    screenTrack.onended = () => stopPresentation();

                    peerConnections.forEach((pc) => {
                        const senders = pc.getSenders();
                        const videoSender = senders.find(s => s.track && s.track.kind === 'video');
                        if (videoSender) {
                            videoSender.replaceTrack(screenTrack);
                        } else {
                            pc.addTrack(screenTrack, screenMediaStream);
                        }
                    });
                }

                if (ws && ws.readyState === WebSocket.OPEN) {
                    ws.send(JSON.stringify({ type: 'presentation.start', payload: {} }));
                }

                showToast('🖥️ {{ __("Screen sharing active") }}');
            } catch (err) {
                console.log('[ScreenShare] Cancelled or denied:', err.message);
            }
        }

        function stopPresentation() {
            if (screenMediaStream) {
                screenMediaStream.getTracks().forEach(t => t.stop());
                screenMediaStream = null;
            }
            isScreenSharing = false;
            document.getElementById('present-btn')?.classList.remove('active');
            document.getElementById('present-btn-text').textContent = '{{ __("Present") }}';
            closePresentationModal();

            if (localMediaStream) {
                const camTrack = localMediaStream.getVideoTracks()[0];
                peerConnections.forEach((pc) => {
                    const senders = pc.getSenders();
                    const videoSender = senders.find(s => s.track && s.track.kind === 'video');
                    if (videoSender && camTrack) {
                        videoSender.replaceTrack(camTrack);
                    }
                });
            }

            if (ws && ws.readyState === WebSocket.OPEN) {
                ws.send(JSON.stringify({ type: 'presentation.stop', payload: {} }));
            }
        }

        function closePresentationModal() {
            document.getElementById('presentation-modal').style.display = 'none';
        }

        // ── Meeting Recording Engine (Saved Directly to Server Gallery) ──
        let mediaRecorder = null;
        let recordedChunks = [];
        let isRecording = false;
        let recordStartTime = 0;
        let recordTimerInterval = null;

        async function toggleMeetingRecording() {
            if (isRecording) {
                stopMeetingRecording();
            } else {
                startMeetingRecording();
            }
        }

        async function startMeetingRecording() {
            try {
                const canvasStream = canvas.captureStream ? canvas.captureStream(30) : (canvas.mozCaptureStream ? canvas.mozCaptureStream(30) : null);
                if (!canvasStream) throw new Error('Canvas capture not supported in this browser');

                const combinedStream = new MediaStream();
                canvasStream.getVideoTracks().forEach(t => combinedStream.addTrack(t));

                if (localMediaStream && localMediaStream.getAudioTracks().length > 0) {
                    combinedStream.addTrack(localMediaStream.getAudioTracks()[0]);
                }

                let options = {};
                if (typeof MediaRecorder.isTypeSupported === 'function') {
                    if (MediaRecorder.isTypeSupported('video/webm;codecs=vp9,opus')) {
                        options = { mimeType: 'video/webm;codecs=vp9,opus' };
                    } else if (MediaRecorder.isTypeSupported('video/webm;codecs=vp8,opus')) {
                        options = { mimeType: 'video/webm;codecs=vp8,opus' };
                    } else if (MediaRecorder.isTypeSupported('video/webm')) {
                        options = { mimeType: 'video/webm' };
                    } else if (MediaRecorder.isTypeSupported('video/mp4')) {
                        options = { mimeType: 'video/mp4' };
                    }
                }

                mediaRecorder = new MediaRecorder(combinedStream, options);
                recordedChunks = [];

                mediaRecorder.ondataavailable = (e) => {
                    if (e.data && e.data.size > 0) recordedChunks.push(e.data);
                };

                mediaRecorder.onstop = async () => {
                    await uploadRecordedSession();
                };

                mediaRecorder.start(1000);
                isRecording = true;
                recordStartTime = Date.now();

                document.getElementById('record-btn')?.classList.add('active');
                document.getElementById('record-icon').textContent = '🔴';

                recordTimerInterval = setInterval(() => {
                    const elapsed = Math.floor((Date.now() - recordStartTime) / 1000);
                    const m = String(Math.floor(elapsed / 60)).padStart(2, '0');
                    const s = String(elapsed % 60).padStart(2, '0');
                    document.getElementById('record-btn-text').textContent = `REC ${m}:${s}`;
                }, 1000);

                showToast('🔴 {{ __("Session recording started...") }}');
            } catch(err) {
                console.error('Recording start error:', err);
                showToast('❌ {{ __("Could not start recording") }}: ' + err.message, '#ef4444');
            }
        }

        function stopMeetingRecording() {
            if (mediaRecorder && isRecording) {
                mediaRecorder.stop();
                isRecording = false;
                clearInterval(recordTimerInterval);
                document.getElementById('record-btn')?.classList.remove('active');
                document.getElementById('record-icon').textContent = '⏺️';
                document.getElementById('record-btn-text').textContent = '{{ __("Record") }}';
                showToast('⏳ {{ __("Processing & uploading recording to server...") }}');
            }
        }

        async function uploadRecordedSession() {
            if (recordedChunks.length === 0) return;
            const mime = mediaRecorder?.mimeType || 'video/webm';
            const ext = mime.includes('mp4') ? 'mp4' : 'webm';
            const blob = new Blob(recordedChunks, { type: mime });
            const duration = Math.max(1, Math.round((Date.now() - recordStartTime) / 1000));
            const myRoom = getCurrentRoom(localAvatar.x, localAvatar.y, localAvatar.currentRoomObject);

            const formData = new FormData();
            formData.append('video', blob, `session_${Date.now()}.${ext}`);
            formData.append('title', `Office Session ${new Date().toLocaleTimeString()} — ${myRoom ? myRoom.name : 'Main Floor'}`);
            formData.append('room_id', myRoom ? myRoom.id : '');
            formData.append('duration_seconds', duration);
            formData.append('recorded_by_name', localAvatar.name || 'Member');

            try {
                const res = await fetch(`/organizations/${CONFIG.org.id}/recordings`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    credentials: 'same-origin',
                    body: formData
                });

                if (res.ok) {
                    showToast('✅ {{ __("Recording saved to server gallery!") }}');
                    if (document.getElementById('recordings-gallery-modal').style.display === 'flex') {
                        loadRecordingsList();
                    }
                } else {
                    const err = await res.json().catch(() => ({}));
                    showToast('❌ {{ __("Error saving recording") }}: ' + (err.message || res.statusText), '#ef4444');
                }
            } catch (e) {
                console.error('Upload recording failed:', e);
                showToast('❌ {{ __("Upload failed") }}: ' + e.message, '#ef4444');
            }
        }

        // ── Recordings Gallery ──
        async function openRecordingsGallery() {
            document.getElementById('recordings-gallery-modal').style.display = 'flex';
            await loadRecordingsList();
        }

        function closeRecordingsGallery() {
            document.getElementById('recordings-gallery-modal').style.display = 'none';
        }

        async function loadRecordingsList() {
            const container = document.getElementById('recordings-gallery-content');
            container.innerHTML = `<div style="display: flex; justify-content: center; padding: 40px 0; color: var(--text-muted);">⏳ {{ __("Loading saved recordings from server...") }}</div>`;

            try {
                const res = await fetch(`/organizations/${CONFIG.org.id}/recordings`, {
                    headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                    credentials: 'same-origin'
                });
                const data = await res.json();
                const recs = data.recordings || [];

                if (recs.length === 0) {
                    container.innerHTML = `
                        <div style="text-align: center; padding: 60px 20px; color: var(--text-muted);">
                            <div style="font-size: 48px; margin-bottom: 12px;">📼</div>
                            <strong style="font-size: 16px; color: var(--text-primary); display: block; margin-bottom: 6px;">{{ __("No Recordings Yet") }}</strong>
                            <span>{{ __("Click the Record button on the bottom bar to record meetings and save them securely on the server.") }}</span>
                        </div>
                    `;
                    return;
                }

                let html = `<div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 18px;">`;
                recs.forEach(r => {
                    const dateStr = new Date(r.created_at).toLocaleString();
                    const durM = Math.floor((r.duration_seconds || 0) / 60);
                    const durS = (r.duration_seconds || 0) % 60;
                    const durStr = `${durM}:${String(durS).padStart(2, '0')}`;
                    const sizeMb = (r.file_size / (1024 * 1024)).toFixed(1);

                    html += `
                        <div style="background: var(--bg-card); border: 1px solid var(--border-color); border-radius: 16px; overflow: hidden; display: flex; flex-direction: column; box-shadow: 0 4px 12px rgba(0,0,0,0.15);">
                            <div style="background: #000; position: relative; height: 160px; display: flex; align-items: center; justify-content: center;">
                                <video src="${r.file_url}" controls style="width: 100%; height: 100%; object-fit: contain;"></video>
                            </div>
                            <div style="padding: 14px; display: flex; flex-direction: column; gap: 6px; flex: 1;">
                                <strong style="font-size: 13px; color: var(--text-primary); line-height: 1.4;">${escapeHtml(r.title)}</strong>
                                <div style="font-size: 11px; color: var(--text-muted); display: flex; justify-content: space-between;">
                                    <span>⏱️ ${durStr} (${sizeMb} MB)</span>
                                    <span>📅 ${dateStr}</span>
                                </div>
                                <div style="font-size: 11px; color: var(--brand-teal); font-weight: 600;">👤 ${escapeHtml(r.recorded_by_name || 'Member')}</div>
                                <div style="margin-top: auto; padding-top: 10px; display: flex; gap: 8px;">
                                    <a href="${r.file_url}" download style="flex: 1; text-align: center; background: var(--bg-input); border: 1px solid var(--border-color); color: var(--text-primary); font-size: 12px; font-weight: 700; text-decoration: none; padding: 8px; border-radius: 8px;">💾 {{ __("Download") }}</a>
                                    <button onclick="deleteRecording('${r.id}')" style="background: rgba(239, 68, 68, 0.15); border: 1px solid rgba(239, 68, 68, 0.3); color: #fca5a5; font-size: 12px; font-weight: 700; padding: 8px 12px; border-radius: 8px; cursor: pointer;">🗑️</button>
                                </div>
                            </div>
                        </div>
                    `;
                });
                html += `</div>`;
                container.innerHTML = html;
            } catch(err) {
                container.innerHTML = `<div style="color: #ef4444; text-align: center; padding: 30px;">❌ {{ __("Failed to load recordings.") }}</div>`;
            }
        }

        async function deleteRecording(id) {
            if (!confirm('{{ __("Are you sure you want to delete this recording from the server?") }}')) return;
            try {
                await fetch(`/organizations/${CONFIG.org.id}/recordings/${id}`, {
                    method: 'DELETE',
                    headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
                    credentials: 'same-origin'
                });
                showToast('🗑️ {{ __("Recording deleted") }}');
                loadRecordingsList();
            } catch (e) {
                showToast('❌ {{ __("Delete failed") }}', '#ef4444');
            }
        }

        function openKnockPrompt(room) {
            activeTargetKnockRoom = room;
            document.getElementById('knock-room-title').textContent = `🚪 ${room.name} {{ __('is Locked') }}`;
            document.getElementById('knock-room-desc').innerHTML = `
                {{ __('This meeting room door is locked for a private session.') }}<br>
                {{ __('Click below to knock and request entry permission from the occupants.') }}
            `;
            document.getElementById('knock-status-msg').style.display = 'none';
            document.getElementById('knock-prompt-modal').style.display = 'flex';
        }

        function closeKnockPrompt() {
            document.getElementById('knock-prompt-modal').style.display = 'none';
            activeTargetKnockRoom = null;
        }

        function confirmKnock() {
            if (!activeTargetKnockRoom) return;
            playKnockSound();
            const msgEl = document.getElementById('knock-status-msg');
            msgEl.style.display = 'block';
            msgEl.style.color = 'var(--text-secondary)';
            msgEl.innerHTML = `⏳ <em>{{ __('Knocking on door... waiting for occupant response.') }}</em>`;

            if (ws && ws.readyState === WebSocket.OPEN) {
                ws.send(JSON.stringify({
                    type: 'room.knock',
                    payload: { roomId: activeTargetKnockRoom.id, roomName: activeTargetKnockRoom.name }
                }));
            }
        }

        function respondToKnock(approved) {
            document.getElementById('knock-alert-modal').style.display = 'none';
            if (pendingKnockRequesterId && pendingKnockRoomId && ws && ws.readyState === WebSocket.OPEN) {
                ws.send(JSON.stringify({
                    type: 'room.knock_response',
                    payload: {
                        roomId: pendingKnockRoomId,
                        requesterUserId: pendingKnockRequesterId,
                        approved: approved
                    }
                }));

                if (approved) {
                    roomDoorStates.set(pendingKnockRoomId, false);
                    updateRoomDoorPill();
                    ws.send(JSON.stringify({
                        type: 'room.door_toggle',
                        payload: { roomId: pendingKnockRoomId, isClosed: false }
                    }));
                }
            }
            pendingKnockRequesterId = null;
            pendingKnockRoomId = null;
        }

        // ── Input & Movement ──
        const keys = {};
        window.addEventListener('keydown', (e) => {
            const k = e.key.toLowerCase();
            if (['w', 'a', 's', 'd', 'arrowup', 'arrowleft', 'arrowdown', 'arrowright'].includes(k)) {
                keys[k] = true;
            }
        });
        window.addEventListener('keyup', (e) => {
            const k = e.key.toLowerCase();
            if (keys[k] !== undefined) keys[k] = false;
        });

        canvas.addEventListener('click', (e) => {
            const rect = canvas.getBoundingClientRect();
            const clickX = (e.clientX - rect.left - cameraOffset.x) / zoomLevel;
            const clickY = (e.clientY - rect.top - cameraOffset.y) / zoomLevel;

            const clickedRoom = getCurrentRoom(clickX, clickY);
            const myRoom = getCurrentRoom(localAvatar.x, localAvatar.y);

            if (clickedRoom && clickedRoom !== myRoom && roomDoorStates.get(clickedRoom.id)) {
                openKnockPrompt(clickedRoom);
                return;
            }

            localAvatar.targetX = clickX;
            localAvatar.targetY = clickY;
        });

        function update() {
            let moved = false;
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
                moved = true;
            } else {
                const diffX = localAvatar.targetX - localAvatar.x;
                const diffY = localAvatar.targetY - localAvatar.y;
                const dist = Math.sqrt(diffX * diffX + diffY * diffY);
                if (dist > 3) {
                    nextX += (diffX / dist) * localAvatar.speed;
                    nextY += (diffY / dist) * localAvatar.speed;
                    moved = true;
                }
            }

            // Check Door Lock Collision
            const currentR = getCurrentRoom(localAvatar.x, localAvatar.y);
            const targetR = getCurrentRoom(nextX, nextY);

            if (targetR && targetR !== currentR && roomDoorStates.get(targetR.id)) {
                nextX = localAvatar.x;
                nextY = localAvatar.y;
                localAvatar.targetX = localAvatar.x;
                localAvatar.targetY = localAvatar.y;
                moved = false;

                if (!document.getElementById('knock-prompt-modal').style.display || document.getElementById('knock-prompt-modal').style.display === 'none') {
                    openKnockPrompt(targetR);
                }
            }

            localAvatar.x = Math.max(localAvatar.radius, Math.min(1300, nextX));
            localAvatar.y = Math.max(localAvatar.radius, Math.min(950, nextY));

            updateRoomDoorPill();

            // Track Room Enter / Leave with Hysteresis Stability
            const curRNow = getCurrentRoom(localAvatar.x, localAvatar.y, localAvatar.currentRoomObject);
            localAvatar.currentRoomObject = curRNow;
            const curRId = curRNow ? curRNow.id : null;
            if (curRId !== localAvatar.currentRoomId) {
                const prevRId = localAvatar.currentRoomId;
                localAvatar.currentRoomId = curRId;
                updateOccupantsUI();
                if (ws && ws.readyState === WebSocket.OPEN) {
                    if (curRId) ws.send(JSON.stringify({ type: 'room.enter', payload: { roomId: curRId } }));
                    else if (prevRId) ws.send(JSON.stringify({ type: 'room.leave', payload: { roomId: prevRId } }));
                }
            }

            // Smooth remote avatar interpolation & Dynamic Spatial Audio Isolation Engine
            const localRoom = curRNow;

            remoteAvatars.forEach(av => {
                av.x += (av.targetX - av.x) * 0.25;
                av.y += (av.targetY - av.y) * 0.25;

                const audioEl = peerAudioElements.get(av.id);
                if (audioEl) {
                    const remoteRoom = getCurrentRoom(av.x, av.y, av.currentRoomObject);
                    av.currentRoomObject = remoteRoom;

                    // ── Strict Acoustic Room Isolation ──
                    // If local user is inside a room, only hear users in the SAME room
                    if (localRoom) {
                        if (!remoteRoom || remoteRoom.id !== localRoom.id) {
                            audioEl.volume = 0;
                            return;
                        }
                    } else {
                        // If local user is in the open office / hallway, never hear users inside rooms
                        if (remoteRoom) {
                            audioEl.volume = 0;
                            return;
                        }
                    }

                    // Spatial distance falloff within the shared acoustic space
                    const dist = Math.hypot(localAvatar.x - av.x, localAvatar.y - av.y);
                    const maxDist = localRoom ? 500 : (localAvatar.proximityRadius || 170);
                    if (dist > maxDist) {
                        audioEl.volume = 0;
                    } else {
                        const factor = 1 - (dist / maxDist);
                        audioEl.volume = Math.max(0, Math.min(1, factor * factor));
                    }
                }
            });

            if (moved && ws && ws.readyState === WebSocket.OPEN) {
                maybeBroadcastPosition();
            }
        }

        let lastBroadcast = 0;
        function maybeBroadcastPosition() {
            const now = Date.now();
            if (now - lastBroadcast > 40) {
                lastBroadcast = now;
                ws.send(JSON.stringify({
                    type: 'position.update',
                    payload: { x: Math.round(localAvatar.x), y: Math.round(localAvatar.y), direction: 'down', isMoving: true }
                }));
            }
        }

        const IMAGE_CACHE_MAP = {};
        function getLoadedImage(url) {
            if (!url) return null;
            if (!IMAGE_CACHE_MAP[url]) {
                const img = new Image();
                img.src = url;
                img.onload = () => { if (typeof draw === 'function') draw(); };
                IMAGE_CACHE_MAP[url] = img;
            }
            return IMAGE_CACHE_MAP[url];
        }

        // ── Ultra-High-Fidelity 3D Top-Down Office Furniture Rendering Engine ──
        function drawEnhancedOfficeFurniture(ctx, obj, ox, oy, objW, objH) {
            ctx.save();
            const rot = (obj.position && typeof obj.position.rotation === 'number') ? obj.position.rotation : (obj.rotation || 0);

            ctx.translate(ox + objW / 2, oy + objH / 2);
            if (rot !== 0) {
                ctx.rotate((rot * Math.PI) / 180);
            }

            const imgUrl = obj.image_url || (obj.interaction_config && obj.interaction_config.image_url) || null;
            const customSprite = CUSTOM_FURNITURE_SPRITES[obj.type] || (imgUrl ? { img: getLoadedImage(imgUrl) } : null);

            if (customSprite && customSprite.img && customSprite.img.complete && customSprite.img.naturalWidth > 0) {
                ctx.drawImage(customSprite.img, -objW / 2, -objH / 2, objW, objH);
                ctx.restore();
                return;
            }

            const type = String(obj.type || '').toLowerCase();
            const primaryColor = obj.color || '#334155';

            function roundRectCentered(w, h, r) {
                const hx = -w / 2;
                const hy = -h / 2;
                if (ctx.roundRect) {
                    ctx.roundRect(hx, hy, w, h, r);
                } else {
                    ctx.beginPath();
                    ctx.moveTo(hx + r, hy);
                    ctx.lineTo(hx + w - r, hy);
                    ctx.quadraticCurveTo(hx + w, hy, hx + w, hy + r);
                    ctx.lineTo(hx + w, hy + h - r);
                    ctx.quadraticCurveTo(hx + w, hy + h, hx + w - r, hy + h);
                    ctx.lineTo(hx + r, hy + h);
                    ctx.quadraticCurveTo(hx, hy + h, hx, hy + h - r);
                    ctx.lineTo(hx, hy + r);
                    ctx.quadraticCurveTo(hx, hy, hx + r, hy);
                    ctx.closePath();
                }
            }

            if (type.includes('chair') || type.includes('beanbag')) {
                ctx.fillStyle = 'rgba(0, 0, 0, 0.2)';
                ctx.beginPath();
                ctx.arc(0, 2, 10, 0, Math.PI * 2);
                ctx.fill();

                const seatGrad = ctx.createRadialGradient(-2, -2, 2, 0, 0, 10);
                seatGrad.addColorStop(0, primaryColor);
                seatGrad.addColorStop(1, '#0f172a');
                ctx.fillStyle = seatGrad;
                ctx.beginPath();
                ctx.arc(0, 0, 9, 0, Math.PI * 2);
                ctx.fill();

                ctx.fillStyle = '#0f172a';
                if (ctx.roundRect) ctx.roundRect(-8, -11, 16, 5, 2.5);
                else ctx.rect(-8, -11, 16, 5);
                ctx.fill();
            } else if (type.includes('desk') || type.includes('workstation') || type.includes('table')) {
                ctx.fillStyle = 'rgba(0, 0, 0, 0.22)';
                roundRectCentered(objW - 4, objH - 4, 6);
                ctx.fill();

                const deskGrad = ctx.createLinearGradient(0, -objH / 2, 0, objH / 2);
                deskGrad.addColorStop(0, '#2b211b');
                deskGrad.addColorStop(1, '#1c1511');
                ctx.fillStyle = deskGrad;
                roundRectCentered(objW - 2, objH - 2, 4);
                ctx.fill();

                const monW = Math.min(objW - 14, 30);
                ctx.fillStyle = '#020617';
                if (ctx.roundRect) ctx.roundRect(-monW / 2, -objH / 2 + 4, monW, 4, 1.5);
                else ctx.rect(-monW / 2, -objH / 2 + 4, monW, 4);
                ctx.fill();
                ctx.fillStyle = '#38bdf8';
                ctx.fillRect(-monW / 2 + 2, -objH / 2 + 5, monW - 4, 2);
            } else if (type.includes('plant')) {
                ctx.fillStyle = 'rgba(0, 0, 0, 0.2)';
                ctx.beginPath(); ctx.arc(0, 2, 10, 0, Math.PI * 2); ctx.fill();
                ctx.fillStyle = '#64748b';
                ctx.beginPath(); ctx.arc(0, 0, 9, 0, Math.PI * 2); ctx.fill();
                ctx.fillStyle = '#10b981';
                ctx.beginPath(); ctx.arc(0, 0, 7, 0, Math.PI * 2); ctx.fill();
            } else {
                ctx.fillStyle = 'rgba(0, 0, 0, 0.2)';
                roundRectCentered(objW - 4, objH - 4, 4);
                ctx.fill();
                ctx.fillStyle = '#1e293b';
                roundRectCentered(objW - 2, objH - 2, 3);
                ctx.fill();
                ctx.strokeStyle = 'rgba(255, 255, 255, 0.1)';
                ctx.lineWidth = 1;
                roundRectCentered(objW - 2, objH - 2, 3);
                ctx.stroke();
            }

            ctx.restore();
        }

        // ── Render Avatar with Nanobanana 2.5D Sprites ──
        function drawAvatarCharacter(ctx, av, isSelf) {
            const x = Number(av.x) || 400;
            const y = Number(av.y) || 400;
            const isGuestUser = !!av.isGuest;
            const gender = isSelf ? chosenAvatarGender : (av.gender || 'male');
            const spriteImg = AVATAR_SPRITES[gender] || AVATAR_SPRITES.male;

            // 1. Spatial Audio Hearing Aura
            const auraRadius = isSelf ? (localAvatar.proximityRadius || 170) : 150;
            const auraGrad = ctx.createRadialGradient(x, y, 10, x, y, auraRadius);
            if (isSelf) {
                auraGrad.addColorStop(0, 'rgba(59, 130, 246, 0.25)');
                auraGrad.addColorStop(0.7, 'rgba(59, 130, 246, 0.08)');
                auraGrad.addColorStop(1, 'rgba(59, 130, 246, 0)');
            } else {
                auraGrad.addColorStop(0, 'rgba(16, 185, 129, 0.20)');
                auraGrad.addColorStop(1, 'rgba(16, 185, 129, 0)');
            }
            ctx.fillStyle = auraGrad;
            ctx.beginPath();
            ctx.arc(x, y, auraRadius, 0, Math.PI * 2);
            ctx.fill();

            // 2. Soft Floor Drop Shadow
            ctx.fillStyle = 'rgba(0, 0, 0, 0.28)';
            ctx.beginPath();
            ctx.ellipse(x, y + 14, 16, 7, 0, 0, Math.PI * 2);
            ctx.fill();

            // 3. Avatar Portrait Image or Fallback Circle
            if (spriteImg && spriteImg.complete && spriteImg.naturalWidth > 0) {
                ctx.save();
                ctx.beginPath();
                ctx.arc(x, y, 18, 0, Math.PI * 2);
                ctx.closePath();
                ctx.clip();
                ctx.drawImage(spriteImg, x - 18, y - 18, 36, 36);
                ctx.restore();

                ctx.strokeStyle = isSelf ? '#3b82f6' : (isGuestUser ? '#10b981' : '#f59e0b');
                ctx.lineWidth = 2.5;
                ctx.beginPath();
                ctx.arc(x, y, 18, 0, Math.PI * 2);
                ctx.stroke();
            } else {
                ctx.fillStyle = isSelf ? '#3b82f6' : '#10b981';
                ctx.beginPath();
                ctx.arc(x, y, 18, 0, Math.PI * 2);
                ctx.fill();
                ctx.fillStyle = '#ffffff';
                ctx.font = 'bold 14px sans-serif';
                ctx.textAlign = 'center';
                ctx.textBaseline = 'middle';
                ctx.fillText(String(av.name || 'U').charAt(0).toUpperCase(), x, y);
            }

            // 4. Role Crown or Guest Icon
            ctx.font = '12px sans-serif';
            ctx.textAlign = 'center';
            if (!isGuestUser) {
                ctx.fillText('👑', x + 16, y - 16);
            } else {
                ctx.fillText('👤', x + 16, y - 16);
            }

            // 5. Name Badge & Status Pill
            const displayName = isSelf ? `${av.name} ({{ __("You") }})` : av.name;
            ctx.font = 'bold 11px sans-serif';
            const nameMetrics = ctx.measureText(displayName);
            const pillW = Math.max(70, nameMetrics.width + 18);
            const pillH = 20;

            ctx.fillStyle = 'rgba(15, 23, 42, 0.92)';
            ctx.beginPath();
            if (ctx.roundRect) ctx.roundRect(x - pillW / 2, y + 24, pillW, pillH, 6);
            else ctx.rect(x - pillW / 2, y + 24, pillW, pillH);
            ctx.fill();

            ctx.strokeStyle = isSelf ? 'rgba(59, 130, 246, 0.6)' : 'rgba(255, 255, 255, 0.2)';
            ctx.lineWidth = 1;
            ctx.stroke();

            ctx.fillStyle = isSelf ? '#60a5fa' : (isGuestUser ? '#34d399' : '#f8fafc');
            ctx.textBaseline = 'middle';
            ctx.fillText(displayName, x, y + 34);
        }

        // ── High Performance Offscreen Background Buffer ──
        let bgCanvas = null;
        let bgThemeCached = null;

        function renderStaticBackground() {
            const isDark = (document.documentElement.getAttribute('data-theme') || 'dark') === 'dark';
            const mapW = (CONFIG.map.width || 32) * 32;
            const mapH = (CONFIG.map.height || 24) * 32;

            if (!bgCanvas) {
                bgCanvas = document.createElement('canvas');
                bgCanvas.width = mapW;
                bgCanvas.height = mapH;
            }
            const bctx = bgCanvas.getContext('2d');
            bctx.clearRect(0, 0, mapW, mapH);

            // 1. Digital Workplace Main Floor
            bctx.fillStyle = isDark ? '#0b0f19' : '#f1f5f9';
            bctx.fillRect(0, 0, mapW, mapH);

            // Subtle Floor Grid Pattern
            bctx.strokeStyle = isDark ? 'rgba(255, 255, 255, 0.035)' : 'rgba(0, 0, 0, 0.04)';
            bctx.lineWidth = 1;
            for (let x = 0; x <= mapW; x += 32) {
                bctx.beginPath(); bctx.moveTo(x, 0); bctx.lineTo(x, mapH); bctx.stroke();
            }
            for (let y = 0; y <= mapH; y += 32) {
                bctx.beginPath(); bctx.moveTo(0, y); bctx.lineTo(mapW, y); bctx.stroke();
            }

            // Outer Floor Boundary
            bctx.strokeStyle = isDark ? '#1e293b' : '#cbd5e1';
            bctx.lineWidth = 3;
            bctx.strokeRect(0, 0, mapW, mapH);

            // 2. Rooms
            rooms.forEach(r => {
                const rx = r.bounds.x * 32;
                const ry = r.bounds.y * 32;
                const rw = r.bounds.width * 32;
                const rh = r.bounds.height * 32;

                bctx.fillStyle = isDark ? 'rgba(17, 24, 39, 0.90)' : 'rgba(255, 255, 255, 0.90)';
                bctx.fillRect(rx, ry, rw, rh);

                bctx.strokeStyle = isDark ? 'rgba(255, 255, 255, 0.025)' : 'rgba(0, 0, 0, 0.03)';
                bctx.lineWidth = 1;
                for (let gx = rx; gx <= rx + rw; gx += 32) {
                    bctx.beginPath(); bctx.moveTo(gx, ry); bctx.lineTo(gx, ry + rh); bctx.stroke();
                }
                for (let gy = ry; gy <= ry + rh; gy += 32) {
                    bctx.beginPath(); bctx.moveTo(rx, gy); bctx.lineTo(rx + rw, gy); bctx.stroke();
                }

                bctx.strokeStyle = isDark ? '#334155' : '#cbd5e1';
                bctx.lineWidth = 2.5;
                bctx.strokeRect(rx, ry, rw, rh);

                // Room Title Badge
                const badgeW = Math.min(rw - 16, 160);
                bctx.fillStyle = isDark ? 'rgba(15, 23, 42, 0.95)' : 'rgba(241, 245, 249, 0.95)';
                if (bctx.roundRect) bctx.roundRect(rx + 8, ry + 8, badgeW, 24, 6);
                else bctx.rect(rx + 8, ry + 8, badgeW, 24);
                bctx.fill();

                bctx.strokeStyle = isDark ? 'rgba(255, 255, 255, 0.12)' : 'rgba(0, 0, 0, 0.1)';
                bctx.lineWidth = 1;
                if (bctx.roundRect) bctx.roundRect(rx + 8, ry + 8, badgeW, 24, 6);
                else bctx.rect(rx + 8, ry + 8, badgeW, 24);
                bctx.stroke();

                bctx.fillStyle = isDark ? '#f8fafc' : '#0f172a';
                bctx.font = 'bold 11px sans-serif';
                bctx.fillText(`🏢 ${r.name}`, rx + 14, ry + 24);
            });

            bgThemeCached = isDark;
        }

        // ── Canvas Main Draw Loop ──
        function draw() {
            try {
                const isDark = (document.documentElement.getAttribute('data-theme') || 'dark') === 'dark';
                ctx.clearRect(0, 0, width, height);
                ctx.save();
                ctx.translate(cameraOffset.x, cameraOffset.y);
                ctx.scale(zoomLevel, zoomLevel);

                // 1. Draw Pre-rendered Background Buffer
                if (!bgCanvas || bgThemeCached !== isDark) {
                    renderStaticBackground();
                }
                ctx.drawImage(bgCanvas, 0, 0);

                // 2. Door Thresholds & Locks
                rooms.forEach(r => {
                    const rx = r.bounds.x * 32;
                    const ry = r.bounds.y * 32;
                    const rw = r.bounds.width * 32;
                    const rh = r.bounds.height * 32;
                    const isLocked = !!roomDoorStates.get(r.id);

                    const doorW = 54;
                    const doorX = rx + rw / 2 - doorW / 2;
                    const doorY = ry + rh - 4;

                    if (isLocked) {
                        ctx.fillStyle = '#ef4444';
                        ctx.fillRect(doorX, doorY - 2, doorW, 6);
                        ctx.fillStyle = '#fee2e2';
                        ctx.font = 'bold 9px sans-serif';
                        ctx.fillText('🔒 LOCKED', doorX + 4, doorY + 3);
                    } else {
                        ctx.fillStyle = '#10b981';
                        ctx.fillRect(doorX, doorY, doorW, 4);
                    }
                });

                // 3. Furniture Objects (With 3D sprites & rotation)
                (CONFIG.map.objects || []).forEach(obj => {
                    const ox = (obj.position ? obj.position.x : 0) * 32;
                    const oy = (obj.position ? obj.position.y : 0) * 32;
                    const objW = (obj.width || (obj.size ? obj.size.width : 1)) * 32;
                    const objH = (obj.height || (obj.size ? obj.size.height : 1)) * 32;
                    drawEnhancedOfficeFurniture(ctx, obj, ox, oy, objW, objH);
                });

                // 4. Remote Avatars
                remoteAvatars.forEach(av => drawAvatarCharacter(ctx, av, false));

                // 5. Local Avatar
                drawAvatarCharacter(ctx, localAvatar, true);

                ctx.restore();
            } catch(e) { console.error('Render error:', e); }
        }

        // ── Main Game Loop ──
        function gameLoop() {
            update();
            draw();
            requestAnimationFrame(gameLoop);
        }
        requestAnimationFrame(gameLoop);

        const STATUS_MODES = [
            { label: 'Available', icon: '🟢', value: 'available' },
            { label: 'Busy', icon: '🔴', value: 'busy' },
            { label: 'Away', icon: '🟡', value: 'away' },
            { label: 'In Meeting', icon: '💬', value: 'in_meeting' }
        ];
        let statusIdx = 0;
        function toggleStatus() {
            statusIdx = (statusIdx + 1) % STATUS_MODES.length;
            const s = STATUS_MODES[statusIdx];
            const btn = document.getElementById('status-dock-btn');
            if (btn) {
                btn.querySelector('.icon').textContent = s.icon;
                btn.querySelector('span:not(.icon)').textContent = s.label;
            }
            localAvatar.status = s.value;
            if (ws && ws.readyState === WebSocket.OPEN) {
                ws.send(JSON.stringify({ type: 'status.update', payload: { status: s.value } }));
            }
        }

        // Attach listeners
        document.getElementById('status-dock-btn')?.addEventListener('click', toggleStatus);

        // Navigation Toolbar
        document.getElementById('btn-toggle-sidebar')?.addEventListener('click', () => {
            document.getElementById('sidebar')?.classList.toggle('collapsed');
            setTimeout(() => {
                width = canvas.width = container.clientWidth;
                height = canvas.height = container.clientHeight;
            }, 320);
        });
        document.getElementById('btn-zoom-in')?.addEventListener('click', () => { zoomLevel = Math.min(2.0, zoomLevel + 0.15); });
        document.getElementById('btn-zoom-out')?.addEventListener('click', () => { zoomLevel = Math.max(0.6, zoomLevel - 0.15); });
        document.getElementById('btn-reset-view')?.addEventListener('click', () => { zoomLevel = 1.0; cameraOffset = { x: 0, y: 0 }; });
        document.getElementById('btn-center-avatar')?.addEventListener('click', () => {
            cameraOffset.x = (width / 2) - localAvatar.x * zoomLevel;
            cameraOffset.y = (height / 2) - localAvatar.y * zoomLevel;
        });

        // ── Live Chat Functions ──
        function sendChatMessage() {
            const input = document.getElementById('chat-text-input');
            const text = input.value.trim();
            if (!text) return;
            input.value = '';

            renderChatMessage(localAvatar.name + ' ({{ __("You") }})', text);
            if (ws && ws.readyState === WebSocket.OPEN) {
                ws.send(JSON.stringify({
                    type: 'chat.message',
                    payload: { senderName: localAvatar.name, body: text }
                }));
            }
        }

        function renderChatMessage(sender, body, fileUrl = null, fileName = null) {
            const container = document.getElementById('chat-messages-container');
            const isMe = sender.includes('(You)') || sender.includes(localAvatar.name);
            const bubble = document.createElement('div');
            bubble.className = `chat-bubble ${isMe ? 'mine' : 'peer'}`;

            let content = `<div class="chat-sender">${escapeHtml(sender)}</div><div>${escapeHtml(body)}</div>`;
            if (fileUrl) {
                content += `<div style="margin-top: 6px;"><a href="${fileUrl}" target="_blank" style="color: #38bdf8; font-weight: 700;">📎 ${escapeHtml(fileName || 'Attachment')}</a></div>`;
            }
            bubble.innerHTML = content;
            container.appendChild(bubble);
            container.scrollTop = container.scrollHeight;
        }

        function handleChatFileUpload(input) {
            const file = input.files[0];
            if (!file) return;
            const url = URL.createObjectURL(file);
            renderChatMessage(localAvatar.name + ' ({{ __("You") }})', `[Sent file: ${file.name}]`, url, file.name);
            if (ws && ws.readyState === WebSocket.OPEN) {
                ws.send(JSON.stringify({
                    type: 'chat.message',
                    payload: { senderName: localAvatar.name, body: `[Shared a file: ${file.name}]` }
                }));
            }
        }

        // ── Whiteboard Logic ──
        let wbTool = 'pen';
        let wbColor = '#3b82f6';
        let isDrawing = false;
        const wbCanvas = document.getElementById('wb-canvas');
        const wbCtx = wbCanvas?.getContext('2d');

        function openWhiteboard() {
            const modal = document.getElementById('whiteboard-modal');
            modal.style.display = 'flex';
            if (wbCanvas) {
                wbCanvas.width = wbCanvas.parentElement.clientWidth;
                wbCanvas.height = wbCanvas.parentElement.clientHeight;
                wbCtx.lineCap = 'round';
                wbCtx.lineJoin = 'round';
            }
        }
        function closeWhiteboard() { document.getElementById('whiteboard-modal').style.display = 'none'; }
        function setWbTool(t) {
            wbTool = t;
            ['pen', 'highlighter', 'eraser'].forEach(x => document.getElementById(`wb-tool-${x}`)?.classList.toggle('active', x === t));
        }
        function setWbColor(c) { wbColor = c; }
        function clearWhiteboard() { wbCtx?.clearRect(0, 0, wbCanvas.width, wbCanvas.height); }
        function exportWhiteboard() {
            const link = document.createElement('a');
            link.download = 'whiteboard.png';
            link.href = wbCanvas.toDataURL();
            link.click();
        }

        wbCanvas?.addEventListener('mousedown', (e) => {
            isDrawing = true;
            wbCtx.beginPath();
            wbCtx.moveTo(e.offsetX, e.offsetY);
        });
        wbCanvas?.addEventListener('mousemove', (e) => {
            if (!isDrawing) return;
            wbCtx.strokeStyle = wbTool === 'eraser' ? '#ffffff' : wbColor;
            wbCtx.lineWidth = wbTool === 'highlighter' ? 14 : (wbTool === 'eraser' ? 24 : 3);
            wbCtx.globalAlpha = wbTool === 'highlighter' ? 0.35 : 1.0;
            wbCtx.lineTo(e.offsetX, e.offsetY);
            wbCtx.stroke();
        });
        window.addEventListener('mouseup', () => { isDrawing = false; });

        // ── Helper ──
        function escapeHtml(str) {
            return String(str || '').replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
        }
    </script>
</body>
</html>
