<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">
    <title>{{ $organization->name }} — Virtual Workplace</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;800;900&family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            /* Clean White & Saudi Brand Theme */
            --bg-canvas: #f8fafc;
            --bg-sidebar: #ffffff;
            --bg-panel: rgba(255, 255, 255, 0.96);
            --border-panel: #e2e8f0;

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

            --accent-green: #00b4b3;
            --accent-glow: rgba(0, 180, 179, 0.4);
            --text-main: #012c41;
            --text-muted: #64748b;
            --text-dim: #94a3b8;

            --font-family: {{ app()->getLocale() === 'ar' ? "'Cairo', 'Inter', sans-serif" : "'Inter', 'Cairo', sans-serif" }};
        }

        * { margin: 0; padding: 0; box-sizing: border-box; font-family: var(--font-family); }
        body {
            background: var(--bg-canvas);
            color: var(--text-main);
            height: 100vh;
            overflow: hidden;
            user-select: none;
            display: flex;
        }

        /* ── Sidebar ── */
        .sidebar {
            width: 280px;
            height: 100vh;
            background: var(--bg-sidebar);
            border-inline-end: 1px solid var(--border-panel);
            display: flex;
            flex-direction: column;
            z-index: 50;
            box-shadow: 2px 0 16px rgba(1, 44, 65, 0.05);
            transition: transform 0.25s ease;
        }
        .sidebar.collapsed {
            transform: translateX({{ app()->getLocale() === 'ar' ? '280px' : '-280px' }});
            margin-inline-end: -280px;
        }

        .sidebar-header {
            padding: 16px;
            border-bottom: 1px solid var(--border-panel);
            background: #f8fafc;
        }
        .org-switcher {
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .org-title {
            font-weight: 900;
            font-size: 15px;
            color: var(--brand-navy);
            display: flex;
            align-items: center;
            gap: 6px;
            cursor: pointer;
        }
        .space-subtitle {
            font-size: 11px;
            color: var(--text-muted);
            margin-top: 2px;
            font-weight: 600;
        }
        .header-btn-row {
            display: flex;
            align-items: center;
            gap: 6px;
        }
        .btn-invite-pill {
            background: linear-gradient(135deg, var(--brand-green), #004d34);
            color: white;
            font-size: 11px;
            font-weight: 800;
            padding: 5px 12px;
            border-radius: 12px;
            border: none;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 4px;
            box-shadow: 0 4px 10px rgba(0, 104, 71, 0.25);
        }
        .btn-icon-square {
            width: 30px;
            height: 30px;
            background: #ffffff;
            border: 1px solid var(--border-panel);
            border-radius: 8px;
            color: var(--brand-navy);
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            text-decoration: none;
            font-size: 13px;
        }

        .sidebar-tabs {
            display: flex;
            padding: 10px 16px;
            border-bottom: 1px solid var(--border-panel);
            gap: 16px;
            font-size: 12px;
            font-weight: 700;
            background: #ffffff;
        }
        .sidebar-tab {
            color: var(--text-muted);
            cursor: pointer;
            padding-bottom: 4px;
            transition: color 0.2s;
        }
        .sidebar-tab:hover {
            color: var(--brand-navy);
        }
        .sidebar-tab.active {
            color: var(--brand-teal);
            border-bottom: 2px solid var(--brand-teal);
        }

        .sidebar-content {
            flex: 1;
            overflow-y: auto;
            display: flex;
            flex-direction: column;
            background: #ffffff;
        }

        .chat-container {
            display: flex;
            flex-direction: column;
            height: 250px;
            border-bottom: 1px solid var(--border-panel);
            background: #f8fafc;
        }
        .chat-messages {
            flex: 1;
            overflow-y: auto;
            padding: 10px 14px;
            display: flex;
            flex-direction: column;
            gap: 8px;
            font-size: 12px;
            text-align: {{ app()->getLocale() === 'ar' ? 'right' : 'left' }};
        }
        .chat-input-area {
            padding: 8px 12px;
            border-top: 1px solid var(--border-panel);
            display: flex;
            align-items: center;
            gap: 6px;
            background: #ffffff;
        }
        .chat-input-area input[type="text"] {
            flex: 1;
            background: #f8fafc;
            border: 1px solid var(--border-panel);
            border-radius: 8px;
            padding: 8px 10px;
            color: var(--brand-navy);
            font-size: 12px;
            font-weight: 600;
            outline: none;
            text-align: {{ app()->getLocale() === 'ar' ? 'right' : 'left' }};
        }
        .chat-input-area button, .chat-input-area label {
            background: #f1f5f9;
            border: 1px solid var(--border-panel);
            color: var(--brand-navy);
            border-radius: 8px;
            width: 30px;
            height: 30px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            font-size: 13px;
        }

        .sidebar-section-title {
            padding: 12px 14px 6px;
            font-size: 11px;
            font-weight: 800;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: 0.05em;
            display: flex;
            justify-content: space-between;
        }
        .occupant-list {
            padding: 4px 10px;
            display: flex;
            flex-direction: column;
            gap: 4px;
        }
        .user-occupant-item {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 8px 10px;
            border-radius: 8px;
            font-size: 12px;
            font-weight: 700;
            color: var(--brand-navy);
            background: #f8fafc;
            border: 1px solid var(--border-panel);
        }
        .status-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: var(--brand-green);
            box-shadow: 0 0 6px var(--brand-green);
        }

        /* ── Main Viewport ── */
        .viewport {
            flex: 1;
            height: 100vh;
            position: relative;
            overflow: hidden;
            background: #f1f5f9;
        }
        canvas#office-canvas {
            display: block;
            width: 100%;
            height: 100%;
            cursor: crosshair;
        }

        /* ── Floating Top Language & Navigation Bar ── */
        .floating-top-bar {
            position: fixed;
            top: 18px;
            inset-inline-end: 80px;
            display: flex;
            align-items: center;
            gap: 10px;
            z-index: 90;
        }
        .btn-lang-float {
            background: #ffffff;
            border: 1px solid var(--border-panel);
            color: var(--brand-navy);
            font-size: 12px;
            font-weight: 800;
            padding: 8px 14px;
            border-radius: 10px;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 6px;
            box-shadow: 0 4px 14px rgba(1, 44, 65, 0.08);
            transition: all 0.2s;
        }
        .btn-lang-float:hover {
            border-color: var(--brand-teal);
            transform: translateY(-1px);
            box-shadow: 0 6px 18px rgba(0, 180, 179, 0.2);
        }

        /* ── Floating Room Door Control Pill ── */
        .room-door-pill {
            position: fixed;
            top: 20px;
            left: 50%;
            transform: translateX(-50%);
            background: #ffffff;
            border: 1px solid var(--border-panel);
            border-radius: 14px;
            padding: 8px 16px;
            display: none;
            align-items: center;
            gap: 12px;
            z-index: 90;
            box-shadow: 0 10px 30px rgba(1, 44, 65, 0.12);
            font-size: 12px;
            font-weight: 800;
            color: var(--brand-navy);
        }
        .btn-door-toggle {
            background: rgba(210, 0, 5, 0.1);
            border: 1px solid rgba(210, 0, 5, 0.3);
            color: var(--brand-crimson);
            padding: 4px 12px;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 800;
            font-size: 12px;
            display: flex;
            align-items: center;
            gap: 6px;
            transition: all 0.2s;
        }
        .btn-door-toggle.unlocked {
            background: rgba(0, 104, 71, 0.1);
            border-color: rgba(0, 104, 71, 0.3);
            color: var(--brand-green);
        }

        /* ── Right Toolbar ── */
        .right-toolbar {
            position: fixed;
            top: 18px;
            inset-inline-end: 20px;
            background: #ffffff;
            border: 1px solid var(--border-panel);
            border-radius: 12px;
            display: flex;
            flex-direction: column;
            gap: 4px;
            padding: 6px;
            z-index: 90;
            box-shadow: 0 10px 25px rgba(1, 44, 65, 0.08);
        }
        .tool-btn {
            width: 36px;
            height: 36px;
            border-radius: 8px;
            border: none;
            background: transparent;
            color: var(--text-muted);
            font-size: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.15s;
        }
        .tool-btn:hover { background: #f1f5f9; color: var(--brand-navy); }

        /* ── Bottom Control Dock ── */
        .bottom-dock {
            position: fixed;
            bottom: 20px;
            left: 50%;
            transform: translateX(-50%);
            background: #ffffff;
            border: 1px solid var(--border-panel);
            border-radius: 18px;
            padding: 8px 14px;
            display: flex;
            align-items: center;
            gap: 8px;
            z-index: 100;
            box-shadow: 0 15px 35px rgba(1, 44, 65, 0.15);
        }
        .dock-btn {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            background: transparent;
            border: none;
            color: var(--text-secondary);
            padding: 6px 12px;
            border-radius: 10px;
            cursor: pointer;
            font-size: 11px;
            font-weight: 700;
            gap: 3px;
            transition: all 0.15s;
            min-width: 60px;
        }
        .dock-btn .icon { font-size: 17px; }
        .dock-btn:hover { background: #f1f5f9; color: var(--brand-navy); }
        .dock-btn.active {
            background: rgba(0, 180, 179, 0.12);
            color: var(--brand-teal);
            border: 1px solid rgba(0, 180, 179, 0.3);
        }

        /* ── Video Stage ── */
        .video-grid {
            position: fixed;
            top: 24px;
            inset-inline-start: 300px;
            display: none;
            gap: 10px;
            z-index: 95;
            flex-wrap: wrap;
            max-width: 650px;
        }
        .video-tile {
            width: 170px;
            height: 120px;
            background: #012c41;
            border: 2px solid var(--brand-teal);
            border-radius: 12px;
            overflow: hidden;
            position: relative;
            box-shadow: 0 10px 25px rgba(1, 44, 65, 0.2);
        }
        .video-tile video { width: 100%; height: 100%; object-fit: cover; }
        .video-tile-name {
            position: absolute;
            bottom: 4px;
            inset-inline-start: 6px;
            background: rgba(1, 44, 65, 0.85);
            padding: 2px 6px;
            border-radius: 4px;
            font-size: 10px;
            font-weight: 800;
            color: #ffffff;
        }

        /* ── Modals ── */
        .modal-overlay {
            position: fixed;
            inset: 0;
            background: rgba(1, 44, 65, 0.5);
            backdrop-filter: blur(8px);
            display: none;
            align-items: center;
            justify-content: center;
            z-index: 1000;
            padding: 20px;
        }
        .modal-box {
            background: #ffffff;
            border: 1px solid var(--border-panel);
            border-radius: 20px;
            padding: 24px;
            width: 100%;
            max-width: 520px;
            box-shadow: 0 25px 60px rgba(1, 44, 65, 0.25);
            color: var(--brand-navy);
        }
    </style>
</head>
<body>

    <!-- Left / Right Sidebar -->
    <aside class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <div class="org-switcher">
                <div>
                    <div class="org-title" onclick="window.location.href='{{ route('dashboard') }}'">
                        <span>{{ $organization->name }}</span>
                        @if(!empty($user->is_guest))
                            <span style="background: rgba(0, 104, 71, 0.1); border: 1px solid var(--brand-green); color: var(--brand-green); padding: 2px 6px; border-radius: 4px; font-size: 10px; font-weight: 800; margin-inline-start: 6px;">GUEST ACCESS</span>
                        @endif
                    </div>
                    <div class="space-subtitle">{{ $floor->name }} {{ __('Floor') }}</div>
                </div>
                <div class="header-btn-row">
                    @if(app()->getLocale() === 'ar')
                        <a href="{{ route('lang.switch', 'en') }}" class="btn-icon-square" title="English" style="font-weight: 800; font-size: 11px;">EN</a>
                    @else
                        <a href="{{ route('lang.switch', 'ar') }}" class="btn-icon-square" title="العربية" style="font-weight: 800; font-size: 11px;">عربي</a>
                    @endif

                    @if(empty($user->is_guest))
                        <button class="btn-invite-pill" onclick="openInviteModal()">+ {{ __('Invite') }}</button>
                        <a href="{{ route('editor') }}" class="btn-icon-square" title="Office Editor">⚙️</a>
                    @endif
                </div>
            </div>
        </div>

        <div class="sidebar-tabs">
            <span class="sidebar-tab active">{{ __('Chat') }}</span>
            <span class="sidebar-tab" onclick="alert('📅 Scheduled Meetings:\n1. All-Hands Sync (10:00 AM)\n2. Product Review (2:00 PM)')">{{ __('Calendar') }}</span>
            <span class="sidebar-tab" onclick="alert('Virtual Workplace Shortcuts:\n• Arrow Keys / WASD: Move Avatar\n• Click Floor: Move to Location\n• Scroll: Zoom In/Out\n• Doors: Click room tag or door to lock/unlock')">{{ __('Help') }}</span>
        </div>

        <div class="sidebar-content">
            <!-- Chat -->
            <div class="chat-container">
                <div class="chat-messages" id="chat-messages-container">
                    <div style="color: #64748b; font-size: 11px; text-align: center; padding: 4px 0;">{{ __('Welcome to') }} {{ $organization->name }} {{ __('team chat!') }}</div>
                </div>
                <div class="chat-input-area">
                    <input type="text" id="chat-text-input" placeholder="{{ __('Type a message...') }}" onkeydown="if(event.key==='Enter') sendChatMessage()">
                    <label for="chat-file-input" title="Attach Image or File">📎</label>
                    <input type="file" id="chat-file-input" style="display: none;" onchange="handleChatFileUpload(this)">
                    <button onclick="sendChatMessage()" title="Send">➤</button>
                </div>
            </div>

            <!-- Online Occupants -->
            <div class="sidebar-section-title">
                <span>{{ __('Online Colleagues') }}</span>
                <span id="sidebar-online-count" style="color: var(--brand-teal);">1</span>
            </div>
            <div class="occupant-list" id="sidebar-online-list">
                <div class="user-occupant-item">
                    <span class="status-dot"></span>
                    <strong>{{ $user->name }} ({{ __('You') }})</strong>
                </div>
            </div>
        </div>
    </aside>

    <!-- Main Viewport Canvas -->
    <main class="viewport">
        <canvas id="office-canvas"></canvas>

        <!-- Floating Top Bar with Language Switcher and Dashboard Link -->
        <div class="floating-top-bar">
            @if(app()->getLocale() === 'ar')
                <a href="{{ route('lang.switch', 'en') }}" class="btn-lang-float" title="Switch to English">🌐 English</a>
            @else
                <a href="{{ route('lang.switch', 'ar') }}" class="btn-lang-float" title="التبديل إلى العربية">🌐 العربية</a>
            @endif

            <a href="{{ route('dashboard') }}" class="btn-lang-float" style="background: var(--brand-teal); color: white; border-color: var(--brand-teal);" title="{{ __('Dashboard') }}">
                <span>🏢</span>
                <span>{{ __('Dashboard') }}</span>
            </a>
        </div>

        <!-- Floating Room Door Control Pill -->
        <div class="room-door-pill" id="room-door-pill">
            <span id="room-door-name">🏢 Executive Room</span>
            <button class="btn-door-toggle" id="btn-toggle-room-door" onclick="toggleCurrentRoomDoor()">
                <span>🔒</span>
                <span id="room-door-status-text">{{ __('Lock Door') }}</span>
            </button>
        </div>

        <!-- Right Tool Dock -->
        <div class="right-toolbar">
            <button class="tool-btn" onclick="openWhiteboard()" title="{{ __('Whiteboard') }}">📋</button>
            @if(empty($user->is_guest))
            <a href="{{ route('editor') }}" class="tool-btn" title="{{ __('Edit Furniture & Floor') }}">🪑</a>
            @endif
            <button class="tool-btn" id="btn-toggle-sidebar" title="{{ __('Toggle Sidebar') }}">🔲</button>
            <button class="tool-btn" id="btn-reset-view" title="{{ __('Reset View') }}">🏠</button>
            <button class="tool-btn" id="btn-center-avatar" title="{{ __('Center on Me') }}">🎯</button>
            <div style="height: 1px; background: var(--border-panel); margin: 2px 0;"></div>
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
            <button class="dock-btn" id="cam-btn" title="{{ __('Camera') }}">
                <span class="icon">📹</span>
                <span>{{ __('Camera') }}</span>
            </button>
            <button class="dock-btn" id="mic-btn" title="{{ __('Mic') }}">
                <span class="icon">🎤</span>
                <span>{{ __('Mic') }}</span>
            </button>
            <button class="dock-btn" id="status-dock-btn" title="{{ __('Available') }}">
                <span class="icon">🟢</span>
                <span>{{ __('Available') }}</span>
            </button>
            <button class="dock-btn" id="whiteboard-dock-btn" onclick="openWhiteboard()" title="{{ __('Whiteboard') }}">
                <span class="icon">📋</span>
                <span>{{ __('Whiteboard') }}</span>
            </button>
            <button class="dock-btn" id="present-btn" title="{{ __('Present') }}">
                <span class="icon">🖥️</span>
                <span>{{ __('Present') }}</span>
            </button>
            @if(empty($user->is_guest))
            <button class="dock-btn" id="record-btn" title="{{ __('Record') }}">
                <span class="icon">⭕</span>
                <span>{{ __('Record') }}</span>
            </button>
            @endif
            <button class="dock-btn" id="gallery-btn" title="{{ __('Gallery') }}">
                <span class="icon">🪟</span>
                <span>{{ __('Gallery') }}</span>
            </button>
            <button class="dock-btn" onclick="window.location.href='{{ route('dashboard') }}'" title="{{ __('Dashboard') }}">
                <span class="icon">↗️</span>
                <span>{{ __('Exit') }}</span>
            </button>
        </div>
    </main>

    <!-- 🚪 Knock on Door Prompt Modal (When entering a locked room) -->
    <div id="knock-prompt-modal" class="modal-overlay">
        <div class="modal-box" style="max-width: 440px; text-align: center;">
            <div style="font-size: 40px; margin-bottom: 10px;">🔒</div>
            <h3 style="font-size: 18px; font-weight: 800; margin-bottom: 6px;" id="knock-room-title">{{ __('Meeting Room is Locked') }}</h3>
            <p style="font-size: 13px; color: var(--text-muted); margin-bottom: 20px; line-height: 1.5;" id="knock-room-desc">
                {{ __('This room is currently in a private session. You must knock to request entry permission.') }}
            </p>
            <div style="display: flex; gap: 10px;">
                <button onclick="confirmKnock()" id="btn-knock-send" style="flex: 1; background: var(--brand-teal); color: white; font-weight: 800; border: none; border-radius: 12px; padding: 12px; cursor: pointer; font-size: 13px; display: flex; align-items: center; justify-content: center; gap: 6px;">
                    <span>🔔</span> {{ __('Knock on Door') }}
                </button>
                <button onclick="closeKnockPrompt()" style="background: rgba(255,255,255,0.08); border: 1px solid var(--border-panel); color: #94a3b8; border-radius: 12px; padding: 12px 18px; cursor: pointer; font-size: 13px;">
                    {{ __('Cancel') }}
                </button>
            </div>
            <div id="knock-status-msg" style="margin-top: 14px; font-size: 12px; display: none;"></div>
        </div>
    </div>

    <!-- 🔔 Knock Request Alert Modal (For occupants inside the locked room) -->
    <div id="knock-alert-modal" class="modal-overlay">
        <div class="modal-box" style="max-width: 440px; text-align: center; border: 2px solid var(--brand-teal);">
            <div style="font-size: 42px; margin-bottom: 10px; animation: bounce 0.6s infinite alternate;">🔔</div>
            <h3 style="font-size: 18px; font-weight: 800; margin-bottom: 6px;">{{ __('Knock Knock! Request to Enter') }}</h3>
            <p style="font-size: 13px; color: #cbd5e1; margin-bottom: 20px;" id="knock-alert-desc">
                <strong>John Doe</strong> {{ __('is knocking on the door to enter') }} <strong>Executive Room</strong>.
            </p>
            <div style="display: flex; gap: 10px;">
                <button onclick="respondToKnock(true)" style="flex: 1; background: var(--brand-teal); color: white; font-weight: 800; border: none; border-radius: 12px; padding: 12px; cursor: pointer; font-size: 13px;">
                    ✅ {{ __('Allow Entry') }}
                </button>
                <button onclick="respondToKnock(false)" style="background: rgba(239, 68, 68, 0.15); border: 1px solid rgba(239, 68, 68, 0.4); color: #fca5a5; font-weight: 700; border-radius: 12px; padding: 12px 18px; cursor: pointer; font-size: 13px;">
                    ❌ {{ __('Deny') }}
                </button>
            </div>
        </div>
    </div>

    <!-- Comprehensive Invitation & Team Member Modal -->
    <div id="invite-modal" class="modal-overlay">
        <div class="modal-box">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
                <h3 style="font-size: 16px; font-weight: 800; color: var(--brand-navy);">👥 {{ __('Invite & Guest Access') }}</h3>
                <button onclick="closeInviteModal()" style="background: none; border: none; color: #94a3b8; font-size: 20px; cursor: pointer;">✕</button>
            </div>

            <!-- Tab Switcher -->
            <div style="display: flex; gap: 8px; margin-bottom: 18px; background: #f1f5f9; padding: 4px; border-radius: 10px;">
                <button class="modal-tab-btn active" id="modal-tab-guest" onclick="switchInviteTab('guest')" style="flex: 1; padding: 8px; border-radius: 8px; border: none; font-size: 12px; font-weight: 700; cursor: pointer; background: var(--brand-teal); color: white;">🔗 {{ __('Guest Meeting Link') }}</button>
                <button class="modal-tab-btn" id="modal-tab-member" onclick="switchInviteTab('member')" style="flex: 1; padding: 8px; border-radius: 8px; border: none; font-size: 12px; font-weight: 700; cursor: pointer; background: none; color: var(--text-muted);">👤 {{ __('Add Team Member') }}</button>
            </div>

            <!-- Section 1: Guest Link -->
            <div id="tab-section-guest" style="display: flex; flex-direction: column; gap: 12px;">
                <div>
                    <label style="display: block; font-size: 11px; font-weight: 700; color: var(--brand-ocean); margin-bottom: 4px;">{{ __('Destination Room') }}</label>
                    <select id="invite-room-select" style="width: 100%; background: #f8fafc; border: 1px solid var(--border-panel); border-radius: 8px; padding: 10px; color: var(--brand-navy); outline: none; font-size: 13px; font-weight: 600;">
                        @foreach($map->rooms as $r)
                            <option value="{{ $r->id }}">🏢 {{ $r->name }} ({{ ucfirst($r->type) }})</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label style="display: block; font-size: 11px; font-weight: 700; color: var(--brand-ocean); margin-bottom: 4px;">{{ __('Guest Name / Label') }}</label>
                    <input type="text" id="invite-guest-name" value="Guest / Partner" style="width: 100%; background: #f8fafc; border: 1px solid var(--border-panel); border-radius: 8px; padding: 10px; color: var(--brand-navy); outline: none; font-size: 13px; font-weight: 600;">
                </div>
                <div>
                    <label style="display: block; font-size: 11px; font-weight: 700; color: var(--brand-ocean); margin-bottom: 4px;">{{ __('Link Expiration') }}</label>
                    <select id="invite-guest-hours" style="width: 100%; background: #f8fafc; border: 1px solid var(--border-panel); border-radius: 8px; padding: 10px; color: var(--brand-navy); outline: none; font-size: 13px; font-weight: 600;">
                        <option value="1">1 {{ __('Hour') }}</option>
                        <option value="24" selected>24 {{ __('Hours') }} (1 Day)</option>
                        <option value="72">72 {{ __('Hours') }} (3 Days)</option>
                    </select>
                </div>
                <button onclick="generateGuestLink()" id="btn-gen-guest" style="margin-top: 4px; background: linear-gradient(135deg, var(--brand-green), #004d34); color: white; font-weight: 800; border: none; border-radius: 10px; padding: 12px; cursor: pointer; font-size: 13px; box-shadow: 0 4px 12px rgba(0, 104, 71, 0.2);">
                    ⚡ {{ __('Generate Instant Guest Link') }}
                </button>
                <div id="guest-result-box" style="display: none; background: rgba(0, 104, 71, 0.08); border: 1px solid rgba(0, 104, 71, 0.25); border-radius: 10px; padding: 12px;">
                    <div style="font-size: 11px; font-weight: 800; color: var(--brand-green); margin-bottom: 6px;">✅ {{ __('Invitation Link Ready!') }}</div>
                    <input type="text" id="guest-link-output" readonly style="width: 100%; background: #ffffff; border: 1px solid var(--border-panel); border-radius: 6px; padding: 8px; color: var(--brand-navy); font-size: 12px; font-family: monospace; margin-bottom: 8px;">
                    <div style="display: flex; gap: 8px;">
                        <button onclick="copyGuestLink()" id="btn-copy-link" style="flex: 1; background: var(--brand-teal); color: white; font-weight: 700; border: none; border-radius: 6px; padding: 8px; cursor: pointer; font-size: 12px;">📋 {{ __('Copy Link') }}</button>
                        <a id="guest-open-link" href="#" target="_blank" style="background: #ffffff; border: 1px solid var(--border-panel); color: var(--brand-navy); font-weight: 700; text-decoration: none; border-radius: 6px; padding: 8px 12px; font-size: 12px;">👁️ {{ __('Open') }}</a>
                    </div>
                </div>
            </div>

            <!-- Section 2: Permanent Team Member with Password -->
            <div id="tab-section-member" style="display: none; flex-direction: column; gap: 12px;">
                <div>
                    <label style="display: block; font-size: 11px; font-weight: 700; color: var(--brand-ocean); margin-bottom: 4px;">{{ __('Full Name') }}</label>
                    <input type="text" id="member-name" placeholder="e.g. John Doe" style="width: 100%; background: #f8fafc; border: 1px solid var(--border-panel); border-radius: 8px; padding: 10px; color: var(--brand-navy); outline: none; font-size: 13px; font-weight: 600;">
                </div>
                <div>
                    <label style="display: block; font-size: 11px; font-weight: 700; color: var(--brand-ocean); margin-bottom: 4px;">{{ __('Email Address') }}</label>
                    <input type="email" id="member-email" placeholder="john@example.com" style="width: 100%; background: #f8fafc; border: 1px solid var(--border-panel); border-radius: 8px; padding: 10px; color: var(--brand-navy); outline: none; font-size: 13px; font-weight: 600;">
                </div>
                <div>
                    <label style="display: block; font-size: 11px; font-weight: 700; color: var(--brand-ocean); margin-bottom: 4px;">{{ __('Password') }}</label>
                    <input type="text" id="member-password" placeholder="e.g. Secret123" style="width: 100%; background: #f8fafc; border: 1px solid var(--border-panel); border-radius: 8px; padding: 10px; color: var(--brand-navy); outline: none; font-size: 13px; font-weight: 600;">
                </div>
                <div>
                    <label style="display: block; font-size: 11px; font-weight: 700; color: var(--brand-ocean); margin-bottom: 4px;">{{ __('Role') }}</label>
                    <select id="member-role" style="width: 100%; background: #f8fafc; border: 1px solid var(--border-panel); border-radius: 8px; padding: 10px; color: var(--brand-navy); outline: none; font-size: 13px; font-weight: 600;">
                        <option value="employee" selected>{{ __('Member') }}</option>
                        <option value="manager">{{ __('Manager') }}</option>
                        <option value="company_admin">{{ __('Company Admin') }}</option>
                    </select>
                </div>
                <button onclick="createTeamMember()" id="btn-create-member" style="margin-top: 4px; background: var(--brand-teal); color: white; font-weight: 800; border: none; border-radius: 10px; padding: 12px; cursor: pointer; font-size: 13px;">
                    ✨ {{ __('Add Member to Team') }}
                </button>
                <div id="member-result-box" style="display: none; background: rgba(0, 180, 179, 0.1); border: 1px solid rgba(0, 180, 179, 0.3); border-radius: 10px; padding: 12px; font-size: 12px; color: var(--brand-navy);">
                    <div style="font-weight: 800; margin-bottom: 4px; color: var(--brand-teal);">✅ {{ __('Member Created Successfully!') }}</div>
                    <div>{{ __('The member can now login directly at /login') }}</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Whiteboard Modal -->
    <div id="whiteboard-modal" class="modal-overlay">
        <div style="background: #ffffff; border: 1px solid var(--border-panel); border-radius: 20px; width: 90vw; max-width: 1000px; height: 80vh; display: flex; flex-direction: column; overflow: hidden; box-shadow: 0 25px 60px rgba(1, 44, 65, 0.25);">
            <div style="display: flex; align-items: center; justify-content: space-between; padding: 12px 20px; background: #f8fafc; border-bottom: 1px solid var(--border-panel);">
                <div style="display: flex; align-items: center; gap: 8px; font-weight: 800; color: var(--brand-navy);">
                    <span>📋</span> {{ __('Team Whiteboard') }}
                </div>
                <div style="display: flex; align-items: center; gap: 8px;">
                    <button class="tool-btn active" id="wb-tool-pen" onclick="setWbTool('pen')" title="Pen">✏️</button>
                    <button class="tool-btn" id="wb-tool-highlighter" onclick="setWbTool('highlighter')" title="Highlighter">🖍️</button>
                    <button class="tool-btn" id="wb-tool-eraser" onclick="setWbTool('eraser')" title="Eraser">🧹</button>
                    <div style="display: flex; gap: 4px; margin: 0 6px;">
                        <span onclick="setWbColor('#00b4b3')" style="width: 20px; height: 20px; border-radius: 50%; background: #00b4b3; cursor: pointer;"></span>
                        <span onclick="setWbColor('#006847')" style="width: 20px; height: 20px; border-radius: 50%; background: #006847; cursor: pointer;"></span>
                        <span onclick="setWbColor('#f57b36')" style="width: 20px; height: 20px; border-radius: 50%; background: #f57b36; cursor: pointer;"></span>
                        <span onclick="setWbColor('#d20005')" style="width: 20px; height: 20px; border-radius: 50%; background: #d20005; cursor: pointer;"></span>
                    </div>
                    <button class="tool-btn" onclick="clearWhiteboard()" title="Clear Board" style="color: var(--brand-crimson);">🗑️</button>
                    <button class="tool-btn" onclick="exportWhiteboard()" title="Download PNG">💾</button>
                    <button onclick="closeWhiteboard()" style="background: none; border: none; color: #94a3b8; font-size: 20px; cursor: pointer; margin-inline-start: 10px;">✕</button>
                </div>
            </div>
            <div style="flex: 1; position: relative; background: #ffffff;" id="wb-container">
                <canvas id="wb-canvas" style="width: 100%; height: 100%; cursor: crosshair;"></canvas>
            </div>
        </div>
    </div>

    <!-- Presentation Modal -->
    <div id="presentation-modal" class="modal-overlay">
        <div style="background: rgba(15, 23, 42, 0.98); border: 1px solid var(--border-panel); border-radius: 20px; width: 85vw; max-width: 1100px; height: 80vh; display: flex; flex-direction: column; overflow: hidden;">
            <div style="display: flex; justify-content: space-between; align-items: center; padding: 14px 20px; border-bottom: 1px solid var(--border-panel); background: rgba(0,0,0,0.4);">
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

    <!-- JavaScript Engine with Room Lock, Knocking, Full WebRTC Mesh & Spatial Audio -->
    <script>
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

        let width = canvas.width = container.clientWidth;
        let height = canvas.height = container.clientHeight;

        window.addEventListener('resize', () => {
            width = canvas.width = container.clientWidth;
            height = canvas.height = container.clientHeight;
        });

        // ── Distinct Spawn & Color Engine ──
        const AVATAR_PALETTE = ['#3b82f6', '#8b5cf6', '#ec4899', '#f59e0b', '#10b981', '#06b6d4', '#f43f5e', '#6366f1'];
        function getUserColor(uid, isG) {
            if (isG) return '#22c55e';
            let hash = 0;
            const str = String(uid || '');
            for (let i = 0; i < str.length; i++) hash = ((hash << 5) - hash) + str.charCodeAt(i);
            return AVATAR_PALETTE[Math.abs(hash) % AVATAR_PALETTE.length];
        }

        function calculateInitialSpawn(uid, isG, override) {
            if (override && typeof override.x === 'number') return override;
            if (isG) return { x: 220, y: 220 };
            let hash = 0;
            const str = String(uid || '');
            for (let i = 0; i < str.length; i++) hash = ((hash << 5) - hash) + str.charCodeAt(i);
            const col = Math.abs(hash) % 4;
            const row = Math.abs(hash >> 3) % 3;
            return {
                x: 360 + col * 130,
                y: 360 + row * 110
            };
        }

        const isGuest = {{ !empty($user->is_guest) ? 'true' : 'false' }};
        const overrideSpawn = @json($initialSpawn ?? null);
        const mySpawn = calculateInitialSpawn(String(CONFIG.currentUser.id), isGuest, overrideSpawn);

        const localAvatar = {
            id: String(CONFIG.currentUser.id),
            name: CONFIG.currentUser.name || 'User',
            x: mySpawn.x,
            y: mySpawn.y,
            targetX: mySpawn.x,
            targetY: mySpawn.y,
            speed: 5.0,
            radius: 24,
            color: getUserColor(String(CONFIG.currentUser.id), isGuest),
            proximityRadius: 160,
            currentRoom: null,
            status: 'available'
        };

        const remoteAvatars = new Map(); // userId -> { id, name, x, y, targetX, targetY, color, status, currentRoom, videoElement }
        const rooms = CONFIG.map.rooms || [];
        const roomDoorStates = new Map(); // roomId -> boolean (true: locked/closed, false: open)

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

        // ── Web Audio Synthesizer (Knock & Chime) ──
        let audioCtx = null;
        function getAudioContext() {
            if (!audioCtx) {
                audioCtx = new (window.AudioContext || window.webkitAudioContext)();
            }
            return audioCtx;
        }

        function playKnockSound() {
            try {
                const ctx = getAudioContext();
                [0, 0.15].forEach(delay => {
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

        // ── WebRTC Peer Mesh State ──
        const RTC_CONFIG = {
            iceServers: [
                { urls: 'stun:stun.l.google.com:19302' },
                { urls: 'stun:stun1.l.google.com:19302' },
                { urls: 'stun:stun.services.mozilla.com' }
            ]
        };
        const peerConnections = new Map(); // userId -> RTCPeerConnection
        const peerAudioElements = new Map(); // userId -> Audio
        let isCamActive = false;
        let isMicActive = false;
        let localMediaStream = null;

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
                    console.log('[Media] Device access request:', e.message);
                }
            }
            return localMediaStream;
        }

        function createPeerConnection(peerUserId) {
            if (peerConnections.has(peerUserId)) {
                return peerConnections.get(peerUserId);
            }

            const pc = new RTCPeerConnection(RTC_CONFIG);
            peerConnections.set(peerUserId, pc);

            if (localMediaStream) {
                localMediaStream.getTracks().forEach(track => {
                    pc.addTrack(track, localMediaStream);
                });
            }
            if (presentStream) {
                presentStream.getTracks().forEach(track => {
                    pc.addTrack(track, presentStream);
                });
            }

            pc.onicecandidate = (e) => {
                if (e.candidate && ws && ws.readyState === WebSocket.OPEN) {
                    ws.send(JSON.stringify({
                        type: 'webrtc.signal',
                        payload: {
                            targetUserId: peerUserId,
                            signal: { type: 'candidate', candidate: e.candidate }
                        }
                    }));
                }
            };

            pc.ontrack = (e) => {
                const remoteStream = e.streams[0] || new MediaStream([e.track]);

                // 1. Audio element for spatial audio
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

                // 2. Screen Presentation or Video Camera
                if (activeRemotePresenterId === peerUserId || e.track.label.toLowerCase().includes('screen') || e.track.label.toLowerCase().includes('window')) {
                    const presModal = document.getElementById('presentation-modal');
                    const presVid = document.getElementById('presentation-video');
                    if (presVid && presModal) {
                        presVid.srcObject = remoteStream;
                        presVid.play().catch(() => {});
                        presModal.style.display = 'flex';
                    }
                } else {
                    // Regular Camera Video Tile
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

                    // Store reference for canvas rendering
                    const av = remoteAvatars.get(peerUserId);
                    if (av) {
                        av.videoElement = vidEl;
                    }
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
                        payload: {
                            targetUserId: peerUserId,
                            signal: { type: 'offer', sdp: pc.localDescription }
                        }
                    }));
                }
            } catch(err) {
                console.error('[WebRTC] Call peer error:', err);
            }
        }

        // ── WebSocket Realtime Connection ──
        let ws = null;
        function connectWebSocket() {
            try {
                ws = new WebSocket(`${CONFIG.wsUrl}?token=${CONFIG.token}`);

                ws.onopen = () => {
                    console.log('[WS] Connected to Virtual Workplace Realtime server.');
                    ws.send(JSON.stringify({
                        type: 'map.join',
                        payload: {
                            mapId: CONFIG.map.id,
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

        function handleWebSocketMessage(event) {
            switch(event.type) {
                case 'welcome': {
                    const { occupants } = event.payload || {};
                    (occupants || []).forEach(u => {
                        const uid = String(u.userId || u.id || '');
                        if (uid && uid !== String(localAvatar.id)) {
                            const isG = (u.name && u.name.includes('(Guest)')) || u.role === 'Guest';
                            const defaultSpawn = calculateInitialSpawn(uid, isG, null);
                            const px = (typeof u.position?.x === 'number' && !isNaN(u.position.x) && u.position.x > 0) ? u.position.x : defaultSpawn.x;
                            const py = (typeof u.position?.y === 'number' && !isNaN(u.position.y) && u.position.y > 0) ? u.position.y : defaultSpawn.y;
                            remoteAvatars.set(uid, {
                                id: uid,
                                name: u.name || 'Colleague',
                                x: px, y: py, targetX: px, targetY: py,
                                color: getUserColor(uid, isG),
                                status: u.status || 'available',
                                currentRoom: u.currentRoomId || null
                            });
                            if (localMediaStream) {
                                callPeer(uid);
                            }
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
                            const isG = (u.name && u.name.includes('(Guest)')) || u.role === 'Guest';
                            const defaultSpawn = calculateInitialSpawn(uid, isG, null);
                            const px = (typeof u.position?.x === 'number' && !isNaN(u.position.x) && u.position.x > 0) ? u.position.x : defaultSpawn.x;
                            const py = (typeof u.position?.y === 'number' && !isNaN(u.position.y) && u.position.y > 0) ? u.position.y : defaultSpawn.y;
                            remoteAvatars.set(uid, {
                                id: uid,
                                name: u.name || 'Colleague',
                                x: px, y: py, targetX: px, targetY: py,
                                color: getUserColor(uid, isG),
                                status: u.status || 'available',
                                currentRoom: u.currentRoomId || null
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
                        if (!av && position) {
                            av = {
                                id: uid,
                                name: 'Colleague',
                                x: Number(position.x) || 300,
                                y: Number(position.y) || 300,
                                targetX: Number(position.x) || 300,
                                targetY: Number(position.y) || 300,
                                color: '#8b5cf6',
                                status: 'available',
                                currentRoom: null
                            };
                            remoteAvatars.set(uid, av);
                            updateOccupantsUI();
                        }
                        if (av && position) {
                            av.targetX = Number(position.x) || av.targetX;
                            av.targetY = Number(position.y) || av.targetY;
                        }
                    }
                    break;
                }
                case 'room.door_updated': {
                    const { roomId, isClosed, toggledBy } = event.payload || {};
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
                    // Show alert if occupant is inside the room or on the workplace floor
                    if (!myCurrentRoom || myCurrentRoom.id === roomId || roomDoorStates.get(roomId)) {
                        pendingKnockRequesterId = requesterUserId;
                        pendingKnockRoomId = roomId;
                        playKnockSound();
                        
                        document.getElementById('knock-alert-desc').innerHTML = `
                            <strong>${escapeHtml(requesterName || 'A colleague')}</strong> is knocking at the door of <strong>${escapeHtml(roomName || 'this room')}</strong>.
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
                            msgEl.style.color = '#86efac';
                            msgEl.innerHTML = `✅ <strong>Entry Approved!</strong> Door opened by ${escapeHtml(responderName || 'host')}.`;
                        }
                        // Move avatar inside room!
                        const targetR = rooms.find(r => r.id === roomId);
                        if (targetR) {
                            const cx = (targetR.bounds.x + targetR.bounds.width / 2) * 32;
                            const cy = (targetR.bounds.y + targetR.bounds.height / 2) * 32;
                            localAvatar.targetX = cx;
                            localAvatar.targetY = cy;
                        }
                        setTimeout(() => { closeKnockPrompt(); }, 1200);
                    } else {
                        if (msgEl) {
                            msgEl.style.display = 'block';
                            msgEl.style.color = '#fca5a5';
                            msgEl.innerHTML = `❌ <strong>Entry Denied.</strong> The occupant is in a private meeting.`;
                        }
                    }
                    break;
                }
                case 'webrtc.signal': {
                    const { senderUserId, signal } = event.payload || {};
                    if (!senderUserId || senderUserId === String(localAvatar.id)) break;

                    let pc = peerConnections.get(senderUserId);
                    if (!pc) {
                        pc = createPeerConnection(senderUserId);
                    }

                    if (signal.type === 'offer') {
                        pc.setRemoteDescription(new RTCSessionDescription(signal.sdp)).then(async () => {
                            if (localMediaStream) {
                                localMediaStream.getTracks().forEach(track => {
                                    const senders = pc.getSenders();
                                    if (!senders.some(s => s.track === track)) {
                                        pc.addTrack(track, localMediaStream);
                                    }
                                });
                            }
                            const answer = await pc.createAnswer();
                            await pc.setLocalDescription(answer);
                            ws.send(JSON.stringify({
                                type: 'webrtc.signal',
                                payload: {
                                    targetUserId: senderUserId,
                                    signal: { type: 'answer', sdp: pc.localDescription }
                                }
                            }));
                        }).catch(e => console.error('Offer handling error:', e));
                    } else if (signal.type === 'answer') {
                        pc.setRemoteDescription(new RTCSessionDescription(signal.sdp)).catch(e => console.error('Answer error:', e));
                    } else if (signal.type === 'candidate' && signal.candidate) {
                        pc.addIceCandidate(new RTCIceCandidate(signal.candidate)).catch(() => {});
                    }
                    break;
                }
                case 'presence.updated': {
                    const { userId, status } = event.payload || {};
                    const uid = String(userId || '');
                    const av = remoteAvatars.get(uid);
                    if (av) { av.status = status; updateOccupantsUI(); }
                    break;
                }
                case 'chat.message': {
                    const { senderName, body, fileUrl, fileName } = event.payload || {};
                    if (senderName && !senderName.includes('(You)')) {
                        renderChatMessage(senderName, body, fileUrl, fileName);
                    }
                    break;
                }
                case 'presentation.started': {
                    const { presenterId, presenterName } = event.payload || {};
                    activeRemotePresenterId = presenterId;
                    const presModal = document.getElementById('presentation-modal');
                    if (presModal) {
                        presModal.style.display = 'flex';
                        const titleEl = presModal.querySelector('strong');
                        if (titleEl) titleEl.textContent = `🖥️ ${escapeHtml(presenterName || 'Colleague')}'s Screen Share`;
                    }
                    // Trigger peer renegotiation to receive presentation track
                    if (presenterId) callPeer(presenterId);
                    break;
                }
                case 'presentation.stopped': {
                    activeRemotePresenterId = null;
                    const presModal = document.getElementById('presentation-modal');
                    const presVid = document.getElementById('presentation-video');
                    if (presVid) presVid.srcObject = null;
                    if (presModal) presModal.style.display = 'none';
                    break;
                }
            }
        }

        function updateOccupantsUI() {
            const countEl = document.getElementById('sidebar-online-count');
            if (countEl) countEl.textContent = remoteAvatars.size + 1;

            const listEl = document.getElementById('sidebar-online-list');
            if (listEl) {
                let html = `
                    <div class="user-occupant-item">
                        <span class="status-dot"></span>
                        <strong>${escapeHtml(localAvatar.name)} (You)</strong>
                    </div>
                `;
                remoteAvatars.forEach(av => {
                    html += `
                        <div class="user-occupant-item">
                            <span class="status-dot" style="background: ${av.color}; box-shadow: 0 0 6px ${av.color};"></span>
                            <span>${escapeHtml(av.name)}</span>
                        </div>
                    `;
                });
                listEl.innerHTML = html;
            }
        }

        // ── Room Lock & Door Functions ──
        function getCurrentRoom(x, y) {
            for (const r of rooms) {
                const rx = r.bounds.x * 32;
                const ry = r.bounds.y * 32;
                const rw = r.bounds.width * 32;
                const rh = r.bounds.height * 32;
                if (x >= rx && x <= rx + rw && y >= ry && y <= ry + rh) {
                    return r;
                }
            }
            return null;
        }

        function updateRoomDoorPill() {
            const pill = document.getElementById('room-door-pill');
            if (!pill) return;
            const currentRoom = getCurrentRoom(localAvatar.x, localAvatar.y);
            if (currentRoom) {
                pill.style.display = 'flex';
                document.getElementById('room-door-name').textContent = `🏢 ${currentRoom.name}`;
                const isLocked = !!roomDoorStates.get(currentRoom.id);
                const btn = document.getElementById('btn-toggle-room-door');
                const txt = document.getElementById('room-door-status-text');
                if (isLocked) {
                    btn.classList.remove('unlocked');
                    btn.querySelector('span:first-child').textContent = '🔒';
                    txt.textContent = 'Unlock Door';
                } else {
                    btn.classList.add('unlocked');
                    btn.querySelector('span:first-child').textContent = '🔓';
                    txt.textContent = 'Lock Door';
                }
            } else {
                pill.style.display = 'none';
            }
        }

        function toggleCurrentRoomDoor() {
            const currentRoom = getCurrentRoom(localAvatar.x, localAvatar.y);
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

        function openKnockPrompt(room) {
            activeTargetKnockRoom = room;
            document.getElementById('knock-room-title').textContent = `🚪 ${room.name} is Locked`;
            document.getElementById('knock-room-desc').innerHTML = `
                This meeting room door is locked for a private session.<br>
                Click below to knock and request entry permission from the occupants.
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
            msgEl.style.color = '#e2e8f0';
            msgEl.innerHTML = `⏳ <em>Knocking on door... waiting for occupant response.</em>`;

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

                // If approved, automatically unlock room door
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

        // ── User Input & Movement ──
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

            // Check if clicking locked room tag to toggle or knock
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

            // Check Door Lock Collision Boundary
            const currentR = getCurrentRoom(localAvatar.x, localAvatar.y);
            const targetR = getCurrentRoom(nextX, nextY);

            if (targetR && targetR !== currentR && roomDoorStates.get(targetR.id)) {
                // Block entry into locked room
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

            // Track Room Enter / Leave transitions
            const curRNow = getCurrentRoom(localAvatar.x, localAvatar.y);
            const curRId = curRNow ? curRNow.id : null;
            if (curRId !== localAvatar.currentRoomId) {
                const prevRId = localAvatar.currentRoomId;
                localAvatar.currentRoomId = curRId;
                if (ws && ws.readyState === WebSocket.OPEN) {
                    if (curRId) {
                        ws.send(JSON.stringify({ type: 'room.enter', payload: { roomId: curRId } }));
                    } else if (prevRId) {
                        ws.send(JSON.stringify({ type: 'room.leave', payload: { roomId: prevRId } }));
                    }
                }
            }

            // Smooth remote avatar interpolation & Dynamic Spatial Audio
            remoteAvatars.forEach(av => {
                av.x += (av.targetX - av.x) * 0.25;
                av.y += (av.targetY - av.y) * 0.25;

                // Anti-overlap separation
                const dxx = localAvatar.x - av.x;
                const dyy = localAvatar.y - av.y;
                const sepDist = Math.hypot(dxx, dyy);
                if (sepDist < 36 && sepDist > 0) {
                    const pushAmount = (36 - sepDist) * 0.1;
                    localAvatar.x += (dxx / sepDist) * pushAmount;
                    localAvatar.y += (dyy / sepDist) * pushAmount;
                }

                const audioEl = peerAudioElements.get(av.id);
                if (audioEl) {
                    const dist = Math.hypot(localAvatar.x - av.x, localAvatar.y - av.y);
                    const maxDist = localAvatar.proximityRadius || 160;
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

        // ── Canvas Rendering Loop ──
        function draw() {
            try {
                ctx.clearRect(0, 0, width, height);
                ctx.save();
                ctx.translate(cameraOffset.x, cameraOffset.y);
                ctx.scale(zoomLevel, zoomLevel);

                const mapW = 1200;
                const mapH = 900;

                // 1. Wood Plank Floor
                ctx.fillStyle = '#b8a68f';
                ctx.fillRect(80, 80, mapW, mapH);
                ctx.fillStyle = '#ad9a82';
                for (let x = 80; x < 80 + mapW; x += 64) {
                    for (let y = 80; y < 80 + mapH; y += 18) {
                        if ((Math.floor(x / 64) + Math.floor(y / 18)) % 2 === 0) {
                            ctx.fillRect(x, y, 62, 16);
                        }
                    }
                }
                ctx.strokeStyle = '#1e293b';
                ctx.lineWidth = 8;
                ctx.strokeRect(80, 80, mapW, mapH);

                // 2. Rooms with Dynamic Door Lock Visualization
                rooms.forEach(r => {
                    const rx = r.bounds.x * 32;
                    const ry = r.bounds.y * 32;
                    const rw = r.bounds.width * 32;
                    const rh = r.bounds.height * 32;
                    const isLocked = !!roomDoorStates.get(r.id);

                    ctx.fillStyle = '#788288';
                    ctx.fillRect(rx, ry, rw, rh);
                    ctx.fillStyle = 'rgba(255, 255, 255, 0.05)';
                    for (let cx = rx; cx < rx + rw; cx += 8) { ctx.fillRect(cx, ry, 4, rh); }

                    ctx.fillStyle = isLocked ? 'rgba(239, 68, 68, 0.15)' : 'rgba(226, 232, 240, 0.12)';
                    ctx.fillRect(rx, ry, rw, rh);

                    ctx.strokeStyle = isLocked ? '#ef4444' : '#1e293b';
                    ctx.lineWidth = 4;
                    ctx.strokeRect(rx, ry, rw, rh);

                    // Door Threshold / Barrier at Bottom of Room
                    const doorW = 54;
                    const doorX = rx + rw / 2 - doorW / 2;
                    const doorY = ry + rh - 4;

                    if (isLocked) {
                        // Glowing Red Laser Barrier
                        ctx.fillStyle = '#ef4444';
                        ctx.fillRect(doorX, doorY - 2, doorW, 8);
                        ctx.strokeStyle = 'rgba(255, 255, 255, 0.8)';
                        ctx.lineWidth = 1.5;
                        ctx.strokeRect(doorX, doorY - 2, doorW, 8);
                        ctx.fillStyle = '#fee2e2';
                        ctx.font = 'bold 9px Inter, sans-serif';
                        ctx.fillText('🔒 LOCKED', doorX + 4, doorY + 5);
                    } else {
                        // Open Welcome Threshold
                        ctx.fillStyle = '#22c55e';
                        ctx.fillRect(doorX, doorY, doorW, 4);
                    }

                    // Room Tag Pill
                    ctx.fillStyle = isLocked ? 'rgba(30, 10, 15, 0.95)' : 'rgba(15, 23, 42, 0.92)';
                    ctx.fillRect(rx + 8, ry + 8, 150, 26);
                    ctx.strokeStyle = isLocked ? 'rgba(239, 68, 68, 0.5)' : 'rgba(255, 255, 255, 0.2)';
                    ctx.lineWidth = 1;
                    ctx.strokeRect(rx + 8, ry + 8, 150, 26);
                    ctx.fillStyle = isLocked ? '#fca5a5' : '#f8fafc';
                    ctx.font = '700 11px Inter, sans-serif';
                    ctx.fillText(`${isLocked ? '🔒' : '🏢'} ${r.name}`, rx + 14, ry + 25);
                });

                // 3. Furniture Objects
                (CONFIG.map.objects || []).forEach(obj => {
                    const ox = obj.position.x * 32;
                    const oy = obj.position.y * 32;
                    const objW = (obj.width || (obj.size ? obj.size.width : 1)) * 32;
                    const objH = (obj.height || (obj.size ? obj.size.height : 1)) * 32;

                    const customSprite = CUSTOM_FURNITURE_SPRITES[obj.type] || (obj.image_url ? { img: (function(){ const i = new Image(); i.src = obj.image_url; return i; })() } : null);

                    if (customSprite && customSprite.img && customSprite.img.complete && customSprite.img.naturalWidth > 0) {
                        ctx.drawImage(customSprite.img, ox, oy, objW, objH);
                    } else if (obj.type === 'desk' || obj.type === 'executive_desk') {
                        ctx.fillStyle = '#b45309'; ctx.fillRect(ox + 2, oy + 4, objW - 4, objH - 8);
                        ctx.fillStyle = '#d97706'; ctx.fillRect(ox + 4, oy + 6, objW - 8, objH - 12);
                        ctx.fillStyle = '#0f172a'; ctx.fillRect(ox + 6, oy + 8, 10, 4);
                        ctx.fillStyle = '#38bdf8'; ctx.fillRect(ox + 7, oy + 9, 8, 2);
                        ctx.fillStyle = '#1e293b'; ctx.beginPath(); ctx.arc(ox + objW / 2, oy + objH - 8, 6, 0, Math.PI * 2); ctx.fill();
                    } else if (obj.type === 'chair') {
                        ctx.fillStyle = obj.color || '#00b4b3'; ctx.beginPath(); ctx.arc(ox + 16, oy + 16, 7, 0, Math.PI * 2); ctx.fill();
                    } else if (obj.type === 'sofa') {
                        ctx.fillStyle = obj.color || '#012c41'; ctx.fillRect(ox + 2, oy + 4, objW - 4, objH - 8);
                        ctx.fillStyle = '#27272a'; ctx.fillRect(ox + 4, oy + 6, (objW - 12) / 2, objH - 12); ctx.fillRect(ox + (objW / 2) + 2, oy + 6, (objW - 12) / 2, objH - 12);
                    } else if (obj.type === 'plant') {
                        ctx.fillStyle = '#78350f'; ctx.beginPath(); ctx.arc(ox + 16, oy + 16, 7, 0, Math.PI * 2); ctx.fill();
                        [0, 1.05, 2.1, 3.14, 4.2, 5.25].forEach(a => {
                            ctx.fillStyle = '#006847'; ctx.beginPath();
                            ctx.arc(ox + 16 + Math.cos(a) * 7, oy + 16 + Math.sin(a) * 7, 4, 0, Math.PI * 2); ctx.fill();
                        });
                    } else if (obj.type === 'whiteboard') {
                        ctx.fillStyle = '#ffffff'; ctx.fillRect(ox + 2, oy + 2, objW - 4, objH - 4);
                        ctx.strokeStyle = '#64748b'; ctx.lineWidth = 2; ctx.strokeRect(ox + 2, oy + 2, objW - 4, objH - 4);
                    } else {
                        ctx.fillStyle = obj.color || '#00b4b3'; ctx.fillRect(ox + 4, oy + 4, objW - 8, objH - 8);
                    }
                });

                // 4. Remote Avatars with Live Video Texture / Circle
                remoteAvatars.forEach(av => {
                    const ax = Number(av.x) || 400;
                    const ay = Number(av.y) || 400;

                    // Range Aura
                    const grad = ctx.createRadialGradient(ax, ay, 10, ax, ay, 160);
                    grad.addColorStop(0, 'rgba(34, 197, 94, 0.25)');
                    grad.addColorStop(1, 'rgba(34, 197, 94, 0)');
                    ctx.fillStyle = grad;
                    ctx.beginPath();
                    ctx.arc(ax, ay, 160, 0, Math.PI * 2);
                    ctx.fill();

                    // Check if peer has active live video
                    const peerVid = av.videoElement || document.getElementById(`peer-vid-${av.id}`);
                    const hasPeerVid = peerVid && peerVid.readyState >= 2 && !peerVid.paused;

                    if (hasPeerVid) {
                        ctx.save();
                        ctx.beginPath();
                        ctx.arc(ax, ay, 22, 0, Math.PI * 2);
                        ctx.clip();
                        ctx.drawImage(peerVid, ax - 22, ay - 22, 44, 44);
                        ctx.restore();
                    } else {
                        ctx.fillStyle = av.color || '#8b5cf6';
                        ctx.beginPath();
                        ctx.arc(ax, ay, 22, 0, Math.PI * 2);
                        ctx.fill();

                        ctx.fillStyle = '#ffffff';
                        ctx.font = 'bold 16px Inter, sans-serif';
                        ctx.textAlign = 'center';
                        ctx.textBaseline = 'middle';
                        ctx.fillText(String(av.name || 'U').charAt(0).toUpperCase(), ax, ay);
                    }

                    // Border (Glowing green when video is live, white otherwise)
                    ctx.strokeStyle = hasPeerVid ? '#22c55e' : '#ffffff';
                    ctx.lineWidth = 3;
                    ctx.beginPath();
                    ctx.arc(ax, ay, 22, 0, Math.PI * 2);
                    ctx.stroke();

                    // Name Tag
                    ctx.fillStyle = 'rgba(15, 23, 42, 0.95)';
                    ctx.fillRect(ax - 50, ay + 26, 100, 20);
                    ctx.strokeStyle = 'rgba(255, 255, 255, 0.25)';
                    ctx.lineWidth = 1;
                    ctx.strokeRect(ax - 50, ay + 26, 100, 20);
                    ctx.fillStyle = '#f8fafc';
                    ctx.font = 'bold 11px Inter, sans-serif';
                    ctx.fillText(String(av.name || 'Colleague'), ax, ay + 36);
                });

                // 5. Local Avatar
                const lx = Number(localAvatar.x) || 450;
                const ly = Number(localAvatar.y) || 450;

                // Golden Hearing Aura
                const myGrad = ctx.createRadialGradient(lx, ly, 10, lx, ly, localAvatar.proximityRadius || 160);
                myGrad.addColorStop(0, 'rgba(254, 240, 138, 0.4)');
                myGrad.addColorStop(0.7, 'rgba(254, 240, 138, 0.15)');
                myGrad.addColorStop(1, 'rgba(254, 240, 138, 0)');
                ctx.fillStyle = myGrad;
                ctx.beginPath();
                ctx.arc(lx, ly, localAvatar.proximityRadius || 160, 0, Math.PI * 2);
                ctx.fill();

                // Local Avatar Circle
                const localVid = document.getElementById('local-video-preview');
                const hasLocalVid = isCamActive && localVid && localVid.readyState >= 2;

                if (hasLocalVid) {
                    ctx.save();
                    ctx.beginPath();
                    ctx.arc(lx, ly, 22, 0, Math.PI * 2);
                    ctx.clip();
                    ctx.drawImage(localVid, lx - 22, ly - 22, 44, 44);
                    ctx.restore();
                } else {
                    ctx.fillStyle = localAvatar.color || '#3b82f6';
                    ctx.beginPath();
                    ctx.arc(lx, ly, 22, 0, Math.PI * 2);
                    ctx.fill();

                    ctx.fillStyle = '#ffffff';
                    ctx.font = 'bold 17px Inter, sans-serif';
                    ctx.textAlign = 'center';
                    ctx.textBaseline = 'middle';
                    ctx.fillText(String(localAvatar.name || 'U').charAt(0).toUpperCase(), lx, ly);
                }

                // Glowing White Border
                ctx.strokeStyle = hasLocalVid ? '#22c55e' : '#ffffff';
                ctx.lineWidth = 3;
                ctx.beginPath();
                ctx.arc(lx, ly, 22, 0, Math.PI * 2);
                ctx.stroke();

                // Name Badge Pill
                ctx.fillStyle = 'rgba(15, 23, 42, 0.95)';
                ctx.fillRect(lx - 55, ly + 26, 110, 20);
                ctx.strokeStyle = 'rgba(255, 255, 255, 0.25)';
                ctx.lineWidth = 1;
                ctx.strokeRect(lx - 55, ly + 26, 110, 20);
                ctx.fillStyle = '#4ade80';
                ctx.font = 'bold 11px Inter, sans-serif';
                ctx.fillText(`${String(localAvatar.name || 'You')} (You)`, lx, ly + 36);

                ctx.restore();
            } catch(e) { console.error('Render error:', e); }

            requestAnimationFrame(() => {
                update();
                draw();
            });
        }
        draw();

        // ── Camera & Microphone Toggles ──
        async function toggleCamera() {
            const btn = document.getElementById('cam-btn');
            const videoGrid = document.getElementById('office-video-grid');

            if (!isCamActive) {
                isCamActive = true;
                btn.classList.add('active');
                videoGrid.style.display = 'flex';
                document.getElementById('local-video-container').style.display = 'block';
                await getLocalMediaStream();
                if (localMediaStream) {
                    localMediaStream.getVideoTracks().forEach(t => t.enabled = true);
                }
                remoteAvatars.forEach((_, peerId) => callPeer(peerId));
            } else {
                isCamActive = false;
                btn.classList.remove('active');
                if (localMediaStream) {
                    localMediaStream.getVideoTracks().forEach(t => t.enabled = false);
                }
                document.getElementById('local-video-container').style.display = 'none';
                if (!peerConnections.size) videoGrid.style.display = 'none';
            }
        }

        async function toggleMicrophone() {
            const btn = document.getElementById('mic-btn');
            if (!isMicActive) {
                isMicActive = true;
                btn.classList.add('active');
                await getLocalMediaStream();
                if (localMediaStream) {
                    localMediaStream.getAudioTracks().forEach(t => t.enabled = true);
                }
                remoteAvatars.forEach((_, peerId) => callPeer(peerId));
            } else {
                isMicActive = false;
                btn.classList.remove('active');
                if (localMediaStream) {
                    localMediaStream.getAudioTracks().forEach(t => t.enabled = false);
                }
            }
        }

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

        // Attach dock listeners
        document.getElementById('cam-btn')?.addEventListener('click', toggleCamera);
        document.getElementById('mic-btn')?.addEventListener('click', toggleMicrophone);
        document.getElementById('status-dock-btn')?.addEventListener('click', toggleStatus);
        document.getElementById('gallery-btn')?.addEventListener('click', () => {
            const g = document.getElementById('office-video-grid');
            g.style.display = g.style.display === 'flex' ? 'none' : 'flex';
        });

        // Navigation Toolbar
        document.getElementById('btn-toggle-sidebar')?.addEventListener('click', () => {
            document.getElementById('sidebar')?.classList.toggle('collapsed');
            setTimeout(() => {
                width = canvas.width = container.clientWidth;
                height = canvas.height = container.clientHeight;
            }, 260);
        });
        document.getElementById('btn-zoom-in')?.addEventListener('click', () => { zoomLevel = Math.min(2.0, zoomLevel + 0.15); });
        document.getElementById('btn-zoom-out')?.addEventListener('click', () => { zoomLevel = Math.max(0.6, zoomLevel - 0.15); });
        document.getElementById('btn-reset-view')?.addEventListener('click', () => { zoomLevel = 1.0; cameraOffset = { x: 0, y: 0 }; });
        document.getElementById('btn-center-avatar')?.addEventListener('click', () => {
            cameraOffset.x = (width / 2) - (localAvatar.x * zoomLevel);
            cameraOffset.y = (height / 2) - (localAvatar.y * zoomLevel);
        });

        // ── Whiteboard Logic ──
        const wbCanvas = document.getElementById('wb-canvas');
        const wbCtx = wbCanvas?.getContext('2d');
        let wbDrawing = false, wbTool = 'pen', wbColor = '#38bdf8', wbLastX = 0, wbLastY = 0;

        function openWhiteboard() {
            const modal = document.getElementById('whiteboard-modal');
            if (modal) {
                modal.style.display = 'flex';
                setTimeout(() => {
                    const cont = document.getElementById('wb-container');
                    if (cont && wbCanvas) {
                        wbCanvas.width = cont.clientWidth;
                        wbCanvas.height = cont.clientHeight;
                    }
                }, 50);
            }
        }
        function closeWhiteboard() {
            const modal = document.getElementById('whiteboard-modal');
            if (modal) modal.style.display = 'none';
        }
        function setWbTool(t) {
            wbTool = t;
            document.querySelectorAll('#whiteboard-modal .tool-btn').forEach(b => b.classList.remove('active'));
            document.getElementById(`wb-tool-${t}`)?.classList.add('active');
        }
        function setWbColor(c) { wbColor = c; }
        function clearWhiteboard() { if(wbCanvas && wbCtx) wbCtx.clearRect(0, 0, wbCanvas.width, wbCanvas.height); }
        function exportWhiteboard() {
            if(!wbCanvas) return;
            const a = document.createElement('a');
            a.download = `whiteboard-${Date.now()}.png`;
            a.href = wbCanvas.toDataURL();
            a.click();
        }

        wbCanvas?.addEventListener('mousedown', (e) => {
            const r = wbCanvas.getBoundingClientRect();
            wbDrawing = true; wbLastX = e.clientX - r.left; wbLastY = e.clientY - r.top;
        });
        wbCanvas?.addEventListener('mousemove', (e) => {
            if (!wbDrawing || !wbCtx) return;
            const r = wbCanvas.getBoundingClientRect();
            const cx = e.clientX - r.left, cy = e.clientY - r.top;
            wbCtx.beginPath();
            wbCtx.moveTo(wbLastX, wbLastY);
            wbCtx.lineTo(cx, cy);
            wbCtx.strokeStyle = wbTool === 'eraser' ? '#0b0f19' : wbColor;
            wbCtx.lineWidth = wbTool === 'eraser' ? 24 : (wbTool === 'highlighter' ? 14 : 3);
            wbCtx.lineCap = 'round';
            wbCtx.stroke();
            wbLastX = cx; wbLastY = cy;
        });
        wbCanvas?.addEventListener('mouseup', () => { wbDrawing = false; });
        wbCanvas?.addEventListener('mouseleave', () => { wbDrawing = false; });

        // ── Presentation / Screen Sharing ──
        let presentStream = null;
        let activeRemotePresenterId = null;
        const presentBtn = document.getElementById('present-btn');
        const presentModal = document.getElementById('presentation-modal');
        const presentVideo = document.getElementById('presentation-video');

        presentBtn?.addEventListener('click', async () => {
            if (presentStream) {
                stopPresentation();
            } else {
                try {
                    presentStream = await navigator.mediaDevices.getDisplayMedia({ video: { cursor: 'always' }, audio: true });
                    if (presentVideo) {
                        presentVideo.srcObject = presentStream;
                        presentVideo.play().catch(() => {});
                    }
                    if (presentModal) {
                        presentModal.style.display = 'flex';
                        const titleEl = presentModal.querySelector('strong');
                        if (titleEl) titleEl.textContent = `🖥️ You are sharing your screen`;
                    }
                    presentBtn.classList.add('active');

                    // Add screen share tracks to all active peer connections
                    presentStream.getTracks().forEach(track => {
                        peerConnections.forEach(pc => {
                            try { pc.addTrack(track, presentStream); } catch(e) {}
                        });
                        track.onended = () => { stopPresentation(); };
                    });

                    // Broadcast offer to peers so they receive screen stream
                    remoteAvatars.forEach((_, peerId) => callPeer(peerId));

                    if (ws && ws.readyState === WebSocket.OPEN) {
                        ws.send(JSON.stringify({ type: 'presentation.start', payload: {} }));
                    }
                } catch(e) { console.log('Screen sharing cancelled:', e); }
            }
        });

        function stopPresentation() {
            if (presentStream) {
                presentStream.getTracks().forEach(t => t.stop());
                presentStream = null;
            }
            if (presentVideo && !activeRemotePresenterId) presentVideo.srcObject = null;
            if (presentModal && !activeRemotePresenterId) presentModal.style.display = 'none';
            if (presentBtn) presentBtn.classList.remove('active');

            if (ws && ws.readyState === WebSocket.OPEN) {
                ws.send(JSON.stringify({ type: 'presentation.stop', payload: {} }));
            }
            remoteAvatars.forEach((_, peerId) => callPeer(peerId));
        }

        function closePresentationModal() {
            if (presentModal) presentModal.style.display = 'none';
        }

        // ── Recording Studio ──
        let mediaRecorder = null;
        let recordChunks = [];
        const recordBtn = document.getElementById('record-btn');
        recordBtn?.addEventListener('click', () => {
            if (mediaRecorder && mediaRecorder.state === 'recording') {
                mediaRecorder.stop();
                recordBtn.classList.remove('active');
                recordBtn.querySelector('.icon').textContent = '⭕';
            } else {
                try {
                    const stream = canvas.captureStream(30);
                    recordChunks = [];
                    mediaRecorder = new MediaRecorder(stream, { mimeType: 'video/webm' });
                    mediaRecorder.ondataavailable = (e) => { if(e.data.size > 0) recordChunks.push(e.data); };
                    mediaRecorder.onstop = () => {
                        const blob = new Blob(recordChunks, { type: 'video/webm' });
                        const a = document.createElement('a');
                        a.href = URL.createObjectURL(blob);
                        a.download = `office-recording-${Date.now()}.webm`;
                        a.click();
                    };
                    mediaRecorder.start(1000);
                    recordBtn.classList.add('active');
                    recordBtn.querySelector('.icon').textContent = '⏹️';
                } catch(e) { alert('Recording not supported.'); }
            }
        });

        // ── Invite Modal & Member Creation ──
        function openInviteModal() { document.getElementById('invite-modal').style.display = 'flex'; }
        function closeInviteModal() { document.getElementById('invite-modal').style.display = 'none'; }

        function switchInviteTab(tab) {
            document.getElementById('modal-tab-guest').classList.toggle('active', tab === 'guest');
            document.getElementById('modal-tab-member').classList.toggle('active', tab === 'member');
            document.getElementById('tab-section-guest').style.display = tab === 'guest' ? 'flex' : 'none';
            document.getElementById('tab-section-member').style.display = tab === 'member' ? 'flex' : 'none';
        }

        async function generateGuestLink() {
            const roomId = document.getElementById('invite-room-select').value;
            const guestName = document.getElementById('invite-guest-name').value.trim() || 'Guest';
            const hours = parseInt(document.getElementById('invite-guest-hours').value) || 24;
            const btn = document.getElementById('btn-gen-guest');
            btn.textContent = '⏳ Generating...';

            try {
                const res = await fetch(`/api/v1/organizations/${CONFIG.org.id}/rooms/${roomId}/guest-invitations`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                    credentials: 'same-origin',
                    body: JSON.stringify({ guest_name: guestName, expires_in_hours: hours })
                });
                const data = await res.json();
                if (data.join_url) {
                    document.getElementById('guest-link-output').value = data.join_url;
                    document.getElementById('guest-open-link').href = data.join_url;
                    document.getElementById('guest-result-box').style.display = 'block';
                } else {
                    alert(data.message || 'Failed to generate link');
                }
            } catch(e) { alert('Error creating invitation'); }
            finally { btn.textContent = '⚡ Generate Instant Link'; }
        }

        function copyGuestLink() {
            const input = document.getElementById('guest-link-output');
            input.select();
            navigator.clipboard.writeText(input.value);
            const btn = document.getElementById('btn-copy-link');
            btn.textContent = '✅ Copied!';
            setTimeout(() => { btn.textContent = '📋 Copy Link'; }, 2000);
        }

        async function createTeamMember() {
            const name = document.getElementById('member-name').value.trim();
            const email = document.getElementById('member-email').value.trim();
            const password = document.getElementById('member-password').value.trim();
            const role = document.getElementById('member-role').value;
            const btn = document.getElementById('btn-create-member');

            if (!email) {
                alert('Please enter an email address.');
                return;
            }
            if (!password || password.length < 6) {
                alert('Password must be at least 6 characters.');
                return;
            }

            btn.textContent = '⏳ Creating Member...';
            try {
                const res = await fetch(`/api/v1/organizations/${CONFIG.org.id}/members/invite`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    credentials: 'same-origin',
                    body: JSON.stringify({ name, email, password, role })
                });

                const data = await res.json();
                if (res.ok) {
                    document.getElementById('member-result-box').style.display = 'block';
                    document.getElementById('member-name').value = '';
                    document.getElementById('member-email').value = '';
                    document.getElementById('member-password').value = '';
                } else {
                    alert(data.message || 'Failed to create member.');
                }
            } catch(e) {
                alert('Error creating team member.');
            } finally {
                btn.textContent = '✨ Add Member to Team';
            }
        }

        // ── Chat Functions ──
        function sendChatMessage() {
            const input = document.getElementById('chat-text-input');
            const text = input.value.trim();
            if (!text) return;
            input.value = '';

            renderChatMessage(`${localAvatar.name} (You)`, text);
            if (ws && ws.readyState === WebSocket.OPEN) {
                ws.send(JSON.stringify({ type: 'chat.send', payload: { channelId: 'general', body: text } }));
            }
        }

        function handleChatFileUpload(input) {
            const file = input.files[0];
            if (!file) return;
            const reader = new FileReader();
            reader.onload = function(e) {
                const dataUrl = e.target.result;
                const isImg = file.type.startsWith('image/');
                renderChatMessage(`${localAvatar.name} (You)`, isImg ? '' : `File: ${file.name}`, isImg ? dataUrl : null, file.name);
                if (ws && ws.readyState === WebSocket.OPEN) {
                    ws.send(JSON.stringify({
                        type: 'chat.send',
                        payload: { channelId: 'general', body: isImg ? 'Shared an image' : `File: ${file.name}`, fileUrl: isImg ? dataUrl : null, fileName: file.name }
                    }));
                }
            };
            reader.readAsDataURL(file);
            input.value = '';
        }

        function renderChatMessage(sender, text, fileUrl, fileName) {
            const box = document.getElementById('chat-messages-container');
            if (!box) return;
            const div = document.createElement('div');
            div.style.background = 'rgba(255,255,255,0.04)';
            div.style.borderRadius = '8px';
            div.style.padding = '6px 8px';
            div.style.border = '1px solid rgba(255,255,255,0.06)';

            let h = `<div style="font-size: 10px; font-weight: 700; color: #818cf8; margin-bottom: 2px;">${escapeHtml(sender)}</div>`;
            if (text) h += `<div style="color: #e2e8f0; word-break: break-word;">${escapeHtml(text)}</div>`;
            if (fileUrl) h += `<img src="${fileUrl}" style="max-width: 100%; border-radius: 6px; margin-top: 4px; border: 1px solid rgba(255,255,255,0.1);">`;
            div.innerHTML = h;
            box.appendChild(div);
            box.scrollTop = box.scrollHeight;
        }

        function escapeHtml(str) {
            const d = document.createElement('div');
            d.textContent = str || '';
            return d.innerHTML;
        }
    </script>
</body>
</html>
