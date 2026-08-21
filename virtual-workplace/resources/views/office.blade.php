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

    <!-- LiveKit WebRTC SDK -->
    <script src="https://cdn.jsdelivr.net/npm/livekit-client/dist/livekit-client.umd.min.js"></script>

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

        /* ── Floating Video Matrix Overlay ── */
        .video-grid-overlay {
            position: absolute;
            top: 80px;
            inset-inline-end: 20px;
            display: flex;
            flex-direction: column;
            gap: 10px;
            z-index: 45;
            max-height: calc(100vh - 200px);
            overflow-y: auto;
            pointer-events: none;
        }
        .video-card {
            width: 220px;
            height: 140px;
            background: rgba(0, 0, 0, 0.85);
            border: 2px solid var(--border-color);
            border-radius: 14px;
            overflow: hidden;
            position: relative;
            box-shadow: var(--shadow-card);
            pointer-events: auto;
        }
        .video-card video {
            width: 100%;
            height: 100%;
            object-fit: cover;
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
            max-width: 580px;
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
            width: 80px;
            height: 80px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid var(--brand-primary);
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
            <div class="org-badge">
                <div class="status-dot"></div>
                <span>🏢 {{ $organization->name }}</span>
            </div>
            @if(!empty($user->is_guest))
                <span class="guest-badge">
                    GUEST ACCESS ({{ $user->name }})
                </span>
            @endif
            <div style="font-size: 12px; color: var(--text-secondary); font-weight: 700;">
                {{ $map->name }}
            </div>
        </div>

        <div class="glass-pill" id="room-status-pill" style="display: none;">
            <span id="current-room-name" style="font-weight: 800; font-size: 12px; color: #34D399;">🏢 Conference Room</span>
            <button onclick="toggleRoomDoorLock()" id="btn-lock-room" class="action-link-btn" style="padding: 4px 8px; font-size: 11px;">
                <span id="lock-icon">🔓</span> <span id="lock-text">{{ __('Lock Door') }}</span>
            </button>
        </div>

        <div class="glass-pill">
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

    <!-- ── Video Grid Matrix Overlay ── -->
    <div class="video-grid-overlay" id="video-grid">
        <!-- Self Video -->
        <div class="video-card" id="local-video-card" style="display: none;">
            <video id="local-video-elem" autoplay playsinline muted></video>
            <div class="video-card-label">{{ $user->name ?? 'You' }} ({{ __('You') }})</div>
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
            <span>🖥️</span>
            <span>{{ __('Share') }}</span>
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
                    <img src="/images/avatars/male.jpg" class="avatar-preview-img" alt="Male Character">
                    <strong style="font-size: 13px;">👨 {{ __('Business Male') }}</strong>
                </div>
                <div class="avatar-card-picker" id="pick-female" onclick="setAvatarGender('female')">
                    <img src="/images/avatars/female.jpg" class="avatar-preview-img" alt="Female Character">
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
                <button onclick="copyGuestLink()" class="action-link-btn" style="justify-content: center;">📋 {{ __('Copy Link') }}</button>
            </div>
        </div>
    </div>

    <!-- 3. Collaborative Whiteboard Modal -->
    <div id="whiteboard-modal" class="modal-overlay">
        <div class="modal-card" style="max-width: 1000px; height: 80vh; padding: 0; overflow: hidden;">
            <div style="padding: 12px 18px; background: var(--bg-dock); border-bottom: 1px solid var(--border-color); display: flex; justify-content: space-between; align-items: center;">
                <strong style="font-size: 14px; display: flex; align-items: center; gap: 6px;">📋 {{ __('Collaborative Team Whiteboard') }}</strong>
                <div style="display: flex; gap: 8px;">
                    <button onclick="clearWhiteboard()" class="action-link-btn" style="color: var(--brand-crimson);">🗑️ {{ __('Clear') }}</button>
                    <button onclick="exportWhiteboard()" class="action-link-btn">💾 {{ __('Export PNG') }}</button>
                    <button onclick="closeWhiteboardModal()" style="background:none; border:none; color:var(--text-muted); font-size:18px; cursor:pointer;">✕</button>
                </div>
            </div>
            <div style="flex: 1; position: relative; background: #FFFFFF;" id="wb-container">
                <canvas id="wb-canvas" style="width: 100%; height: 100%; cursor: crosshair;"></canvas>
            </div>
        </div>
    </div>

    <!-- 4. Recordings Gallery Modal -->
    <div id="recordings-modal" class="modal-overlay">
        <div class="modal-card" style="max-width: 900px; height: 80vh;">
            <div class="modal-header">
                <div class="modal-title"><span>📼</span> {{ __('Session Recordings & Gallery') }}</div>
                <button onclick="closeRecordingsGallery()" style="background:none; border:none; color:var(--text-muted); font-size:18px; cursor:pointer;">✕</button>
            </div>
            <div id="recordings-list" style="flex: 1; overflow-y: auto; display: flex; flex-direction: column; gap: 12px;"></div>
        </div>
    </div>

    <!-- 5. Knock Alert Dialog Modal (For Occupants) -->
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

    <!-- ── JavaScript Realtime Engine & Canvas Pipeline ── -->
    <script>
        const CONFIG = {
            map: @json($map),
            currentUser: @json($user),
            org: @json($organization),
            token: "{{ $realtimeToken }}",
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
        AVATAR_SPRITES.male.src = '/images/avatars/male.jpg';
        AVATAR_SPRITES.female.src = '/images/avatars/female.jpg';

        let userGender = localStorage.getItem('vw_gender') || 'male';

        // ── Local & Remote Avatars ──
        const isGuest = {{ !empty($user->is_guest) ? 'true' : 'false' }};
        const localAvatar = {
            id: String(CONFIG.currentUser?.id || 'usr_1'),
            name: CONFIG.currentUser?.name || 'User',
            isGuest: isGuest,
            x: 512,
            y: 480,
            targetX: 512,
            targetY: 480,
            speed: 5.0,
            radius: 18,
            currentRoomId: null
        };
        const remoteAvatars = new Map();

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
                        ws.send(JSON.stringify({ type: 'room.knock', payload: { roomId: targetRoom.id, requesterName: localAvatar.name } }));
                        showToast('⏳ {{ __("Knocked on door... waiting for occupant response.") }}');
                    }
                }
                return;
            }

            localAvatar.targetX = Math.max(10, Math.min(MAP_WIDTH_PX - 10, clickX));
            localAvatar.targetY = Math.max(10, Math.min(MAP_HEIGHT_PX - 10, clickY));
        });

        // ── Room Detection & Acoustic Isolation ──
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
                    localAvatar.currentRoomId = r.id;
                    if (ws && ws.readyState === WebSocket.OPEN) {
                        ws.send(JSON.stringify({ type: 'room.enter', payload: { roomId: r.id } }));
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
                }
            }
        }

        function toggleRoomDoorLock() {
            const r = getCurrentRoom(localAvatar.x, localAvatar.y);
            if (!r) return;
            const cur = !!roomDoorStates.get(r.id);
            roomDoorStates.set(r.id, !cur);
            updateRoomPresence();
            if (ws && ws.readyState === WebSocket.OPEN) {
                ws.send(JSON.stringify({ type: 'room.door_toggle', payload: { roomId: r.id, isClosed: !cur } }));
            }
            showToast(!cur ? '🔒 {{ __("Room locked") }}' : '🔓 {{ __("Room unlocked") }}');
        }

        // ── Main Game / Render Loop ──
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

            // Smooth remote avatar interpolation
            remoteAvatars.forEach(av => {
                av.x += (av.targetX - av.x) * 0.25;
                av.y += (av.targetY - av.y) * 0.25;
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
                    payload: { x: Math.round(localAvatar.x), y: Math.round(localAvatar.y), gender: userGender }
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

                // Subtle perimeter
                ctx.strokeStyle = 'rgba(79, 155, 95, 0.40)';
                ctx.lineWidth = 1.2;
                ctx.setLineDash([4, 4]);
                ctx.strokeRect(rx, ry, rw, rh);
                ctx.setLineDash([]);

                // Pill Tag
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

                ctx.fillStyle = '#F8FAFC';
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

            // 4. Draw Remote Avatars
            remoteAvatars.forEach(av => drawAvatar(av, false));

            // 5. Draw Local Avatar
            drawAvatar(localAvatar, true);

            ctx.restore();
            requestAnimationFrame(draw);
        }

        function drawAvatar(av, isSelf) {
            const x = Number(av.x) || 400;
            const y = Number(av.y) || 400;
            const gender = isSelf ? userGender : (av.gender || 'male');
            const spriteImg = AVATAR_SPRITES[gender] || AVATAR_SPRITES.male;

            // Soft Drop Shadow
            ctx.fillStyle = 'rgba(0, 0, 0, 0.3)';
            ctx.beginPath();
            ctx.ellipse(x, y + 14, 15, 6, 0, 0, Math.PI * 2);
            ctx.fill();

            // Avatar Portrait
            if (spriteImg && spriteImg.complete && spriteImg.naturalWidth > 0) {
                ctx.save();
                ctx.beginPath();
                ctx.arc(x, y, 18, 0, Math.PI * 2);
                ctx.clip();
                ctx.drawImage(spriteImg, x - 18, y - 18, 36, 36);
                ctx.restore();

                ctx.strokeStyle = isSelf ? '#10B981' : (av.isGuest ? '#3B82F6' : '#F59E0B');
                ctx.lineWidth = 2.5;
                ctx.beginPath();
                ctx.arc(x, y, 18, 0, Math.PI * 2);
                ctx.stroke();
            } else {
                ctx.fillStyle = isSelf ? '#10B981' : '#3B82F6';
                ctx.beginPath();
                ctx.arc(x, y, 18, 0, Math.PI * 2);
                ctx.fill();
            }

            // Name Pill
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

        // ── WebSocket Realtime Connection ──
        let ws = null;
        function connectWebSocket() {
            const wsProtocol = window.location.protocol === 'https:' ? 'wss:' : 'ws:';
            const wsHost = window.location.hostname || '127.0.0.1';
            const wsUrl = `${wsProtocol}//${wsHost}:8080?token=${CONFIG.token}`;

            try {
                ws = new WebSocket(wsUrl);
                ws.onopen = () => console.log('⚡ WebSocket Connected');
                ws.onmessage = (e) => {
                    try {
                        const data = JSON.parse(e.data);
                        if (data.type === 'position.update' && data.payload) {
                            const p = data.payload;
                            if (p.userId && p.userId !== localAvatar.id) {
                                let av = remoteAvatars.get(p.userId);
                                if (!av) {
                                    av = { id: p.userId, name: p.name || 'Member', isGuest: !!p.isGuest, x: p.x, y: p.y, targetX: p.x, targetY: p.y, gender: p.gender || 'male' };
                                    remoteAvatars.set(p.userId, av);
                                } else {
                                    av.targetX = p.x;
                                    av.targetY = p.y;
                                    if (p.gender) av.gender = p.gender;
                                }
                            }
                        } else if (data.type === 'presence.leave' && data.payload?.userId) {
                            remoteAvatars.delete(data.payload.userId);
                        } else if (data.type === 'room.door_toggle' && data.payload) {
                            roomDoorStates.set(data.payload.roomId, !!data.payload.isClosed);
                            updateRoomPresence();
                        } else if (data.type === 'room.knock' && data.payload) {
                            // Check if current user is inside this room
                            const myR = getCurrentRoom(localAvatar.x, localAvatar.y);
                            if (myR && myR.id === data.payload.roomId) {
                                pendingKnock = data.payload;
                                document.getElementById('knock-requester-name').textContent = `${data.payload.requesterName || 'A colleague'} is knocking on the door...`;
                                document.getElementById('knock-alert-modal').style.display = 'flex';
                            }
                        }
                    } catch(err) {}
                };
                ws.onclose = () => setTimeout(connectWebSocket, 3000);
            } catch(err) {
                setTimeout(connectWebSocket, 3000);
            }
        }
        connectWebSocket();

        function respondToKnock(approved) {
            document.getElementById('knock-alert-modal').style.display = 'none';
            if (pendingKnock && ws && ws.readyState === WebSocket.OPEN) {
                ws.send(JSON.stringify({
                    type: 'room.knock_response',
                    payload: { roomId: pendingKnock.roomId, approved: approved }
                }));
                if (approved) {
                    roomDoorStates.set(pendingKnock.roomId, false);
                    updateRoomPresence();
                }
            }
            pendingKnock = null;
        }

        // ── Modals & Actions ──
        function toggleAppTheme() {
            const cur = document.documentElement.getAttribute('data-theme') || 'dark';
            const next = cur === 'dark' ? 'light' : 'dark';
            document.documentElement.setAttribute('data-theme', next);
            document.getElementById('theme-icon').textContent = next === 'dark' ? '☀️' : '🌙';
        }

        function openAvatarModal() {
            document.getElementById('avatar-modal').style.display = 'flex';
            document.getElementById('pick-male').classList.toggle('selected', userGender === 'male');
            document.getElementById('pick-female').classList.toggle('selected', userGender === 'female');
        }
        function closeAvatarModal() { document.getElementById('avatar-modal').style.display = 'none'; }
        function setAvatarGender(g) {
            userGender = g;
            localStorage.setItem('vw_gender', g);
            closeAvatarModal();
            showToast(`🎭 {{ __('Avatar set to') }} ${g === 'female' ? '👩 Female' : '👨 Male'}`);
        }

        function openGuestInviteModal() { document.getElementById('guest-modal').style.display = 'flex'; }
        function closeGuestModal() { document.getElementById('guest-modal').style.display = 'none'; }

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
                    document.getElementById('guest-link-result').style.display = 'flex';
                    document.getElementById('guest-link-input').value = data.join_url;
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

        // ── Whiteboard ──
        function openWhiteboardModal() {
            document.getElementById('whiteboard-modal').style.display = 'flex';
            const wbCanvas = document.getElementById('wb-canvas');
            wbCanvas.width = wbCanvas.parentElement.clientWidth;
            wbCanvas.height = wbCanvas.parentElement.clientHeight;
        }
        function closeWhiteboardModal() { document.getElementById('whiteboard-modal').style.display = 'none'; }
        function clearWhiteboard() {
            const wb = document.getElementById('wb-canvas');
            const wbCtx = wb.getContext('2d');
            wbCtx.clearRect(0, 0, wb.width, wb.height);
        }
        function exportWhiteboard() {
            const wb = document.getElementById('wb-canvas');
            const a = document.createElement('a');
            a.download = `whiteboard_${Date.now()}.png`;
            a.href = wb.toDataURL();
            a.click();
        }

        // ── Audio & Video Toggles ──
        let micEnabled = false;
        let camEnabled = false;
        function toggleMicrophone() {
            micEnabled = !micEnabled;
            const btn = document.getElementById('btn-mic');
            btn.classList.toggle('muted', !micEnabled);
            btn.classList.toggle('active', micEnabled);
            document.getElementById('mic-icon').textContent = micEnabled ? '🎙️' : '🔇';
            document.getElementById('mic-text').textContent = micEnabled ? '{{ __("Mic On") }}' : '{{ __("Mic Off") }}';
            showToast(micEnabled ? '🎙️ {{ __("Microphone active") }}' : '🔇 {{ __("Microphone muted") }}');
        }

        function toggleCamera() {
            camEnabled = !camEnabled;
            const btn = document.getElementById('btn-cam');
            btn.classList.toggle('muted', !camEnabled);
            btn.classList.toggle('active', camEnabled);
            document.getElementById('cam-icon').textContent = camEnabled ? '📹' : '📷';
            document.getElementById('cam-text').textContent = camEnabled ? '{{ __("Cam On") }}' : '{{ __("Cam Off") }}';
            document.getElementById('local-video-card').style.display = camEnabled ? 'block' : 'none';
        }

        function toggleScreenShare() { showToast('🖥️ {{ __("Screen share requested") }}'); }

        // ── Recordings ──
        let isRecording = false;
        function toggleRecording() {
            isRecording = !isRecording;
            const btn = document.getElementById('btn-record');
            btn.classList.toggle('active', isRecording);
            document.getElementById('rec-icon').textContent = isRecording ? '⏹️' : '⏺️';
            document.getElementById('rec-text').textContent = isRecording ? '{{ __("Stop") }}' : '{{ __("Record") }}';
            showToast(isRecording ? '⏺️ {{ __("Recording started") }}' : '💾 {{ __("Recording saved to gallery") }}');
        }

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
                    html += `
                        <div style="background: var(--bg-card); border: 1px solid var(--border-color); border-radius: 12px; padding: 14px; display: flex; justify-content: space-between; align-items: center;">
                            <div>
                                <strong style="font-size: 13px; color: var(--text-primary); display: block;">${r.title}</strong>
                                <span style="font-size: 11px; color: var(--text-secondary);">${new Date(r.created_at).toLocaleString()} • ${Math.round(r.duration_seconds || 0)}s</span>
                            </div>
                            <a href="${r.file_url}" download class="action-link-btn">💾 {{ __("Download") }}</a>
                        </div>
                    `;
                });
                list.innerHTML = html;
            } catch(e) {
                list.innerHTML = `<div style="color:var(--brand-crimson); text-align:center; padding:20px;">❌ {{ __("Failed to load recordings") }}</div>`;
            }
        }
        function closeRecordingsGallery() { document.getElementById('recordings-modal').style.display = 'none'; }

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
