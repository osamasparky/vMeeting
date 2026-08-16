<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Map Editor & Floor Designer — {{ $organization->name }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg-dark: #070913;
            --bg-panel: rgba(15, 23, 42, 0.94);
            --border-panel: rgba(255, 255, 255, 0.08);
            --accent: #6366f1;
            --accent-hover: #4f46e5;
            --accent-glow: rgba(99, 102, 241, 0.35);
            --text-main: #f8fafc;
            --text-muted: #94a3b8;
            --success: #10b981;
            --warning: #f59e0b;
            --danger: #ef4444;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Inter', sans-serif;
            background: var(--bg-dark);
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
            padding: 0 20px;
            z-index: 50;
        }

        .header-left {
            display: flex;
            align-items: center;
            gap: 16px;
        }

        .back-btn {
            background: rgba(255, 255, 255, 0.06);
            border: 1px solid var(--border-panel);
            color: var(--text-main);
            padding: 6px 12px;
            border-radius: 8px;
            font-size: 13px;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 6px;
            transition: all 0.2s;
        }

        .back-btn:hover {
            background: rgba(255, 255, 255, 0.12);
        }

        .map-title-group {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .map-name {
            font-size: 16px;
            font-weight: 700;
            letter-spacing: -0.3px;
        }

        .version-badge {
            background: rgba(99, 102, 241, 0.15);
            border: 1px solid rgba(99, 102, 241, 0.3);
            color: #a5b4fc;
            padding: 3px 8px;
            border-radius: 6px;
            font-size: 11px;
            font-weight: 600;
        }

        .header-center {
            display: flex;
            align-items: center;
            gap: 8px;
            background: rgba(0, 0, 0, 0.3);
            padding: 4px 8px;
            border-radius: 10px;
            border: 1px solid var(--border-panel);
        }

        .tool-chip {
            background: none;
            border: 1px solid transparent;
            color: var(--text-muted);
            padding: 6px 12px;
            border-radius: 8px;
            font-size: 12px;
            font-weight: 500;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 6px;
            transition: all 0.2s;
        }

        .tool-chip:hover {
            color: var(--text-main);
            background: rgba(255, 255, 255, 0.05);
        }

        .tool-chip.active {
            background: var(--accent);
            color: white;
            border-color: var(--accent);
            box-shadow: 0 0 12px var(--accent-glow);
        }

        .header-right {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .action-btn {
            padding: 7px 14px;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 6px;
            transition: all 0.2s;
            text-decoration: none;
            border: none;
        }

        .action-btn.secondary {
            background: rgba(255, 255, 255, 0.06);
            border: 1px solid var(--border-panel);
            color: var(--text-main);
        }

        .action-btn.secondary:hover {
            background: rgba(255, 255, 255, 0.12);
        }

        .action-btn.primary {
            background: var(--accent);
            color: white;
            box-shadow: 0 0 14px var(--accent-glow);
        }

        .action-btn.primary:hover {
            background: var(--accent-hover);
        }

        .action-btn.success {
            background: var(--success);
            color: white;
            box-shadow: 0 0 14px rgba(16, 185, 129, 0.35);
        }

        .action-btn.success:hover {
            background: #059669;
        }

        /* ── Main Layout ── */
        .editor-workspace {
            flex: 1;
            display: flex;
            position: relative;
            overflow: hidden;
        }

        /* ── Left Palette ── */
        .palette-sidebar {
            width: 280px;
            background: var(--bg-panel);
            border-right: 1px solid var(--border-panel);
            display: flex;
            flex-direction: column;
            padding: 16px;
            gap: 18px;
            overflow-y: auto;
            z-index: 40;
        }

        .palette-section-title {
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.6px;
            color: var(--text-muted);
            margin-bottom: 10px;
        }

        .objects-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 8px;
        }

        .object-card {
            background: rgba(255, 255, 255, 0.03);
            border: 1px solid var(--border-panel);
            border-radius: 10px;
            padding: 10px 8px;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 5px;
            cursor: pointer;
            transition: all 0.2s;
            font-size: 11px;
            color: var(--text-muted);
            text-align: center;
        }

        .object-card:hover {
            background: rgba(99, 102, 241, 0.12);
            border-color: rgba(99, 102, 241, 0.35);
            color: var(--text-main);
            transform: translateY(-2px);
        }

        .object-card.active {
            background: rgba(99, 102, 241, 0.25);
            border-color: var(--accent);
            color: white;
            box-shadow: 0 0 12px var(--accent-glow);
        }

        .object-card-icon {
            font-size: 22px;
        }

        /* ── Center Canvas Viewport ── */
        .canvas-container {
            flex: 1;
            position: relative;
            background: radial-gradient(circle at center, #0f172a 0%, #030712 100%);
            overflow: hidden;
            cursor: crosshair;
        }

        canvas#editor-canvas {
            display: block;
            width: 100%;
            height: 100%;
        }

        /* ── Right Properties Inspector ── */
        .inspector-sidebar {
            width: 290px;
            background: var(--bg-panel);
            border-left: 1px solid var(--border-panel);
            display: flex;
            flex-direction: column;
            padding: 18px;
            gap: 16px;
            z-index: 40;
            overflow-y: auto;
        }

        .inspector-title {
            font-size: 14px;
            font-weight: 700;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .prop-group {
            display: flex;
            flex-direction: column;
            gap: 6px;
        }

        .prop-label {
            font-size: 11px;
            font-weight: 600;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .prop-input {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid var(--border-panel);
            border-radius: 8px;
            padding: 8px 12px;
            color: var(--text-main);
            font-size: 13px;
            outline: none;
        }

        .prop-input:focus {
            border-color: var(--accent);
            box-shadow: 0 0 8px var(--accent-glow);
        }

        .stats-box {
            background: rgba(255, 255, 255, 0.02);
            border: 1px solid var(--border-panel);
            border-radius: 10px;
            padding: 12px;
            display: flex;
            flex-direction: column;
            gap: 8px;
            font-size: 12px;
        }

        .stat-row {
            display: flex;
            justify-content: space-between;
            color: var(--text-muted);
        }

        .stat-row strong {
            color: var(--text-main);
        }

        /* ── Notifications Toast ── */
        .toast {
            position: fixed;
            bottom: 24px;
            right: 24px;
            background: rgba(16, 185, 129, 0.95);
            color: white;
            padding: 12px 20px;
            border-radius: 12px;
            font-size: 13px;
            font-weight: 600;
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
            <a href="{{ route('dashboard') }}" class="back-btn">← Dashboard</a>
            <div class="map-title-group">
                <div class="map-name">{{ $map->name }}</div>
                <span class="version-badge" id="header-version-badge">v{{ $map->version }} ({{ ucfirst($map->status) }})</span>
            </div>
        </div>

        <div class="header-center">
            <button class="tool-chip active" id="tool-select" onclick="setTool('select')">
                <span>🖱️</span> Select
            </button>
            <button class="tool-chip" id="tool-room" onclick="setTool('room')">
                <span>🏢</span> Add Room
            </button>
            <button class="tool-chip" id="tool-zone" onclick="setTool('zone')">
                <span>🎙️</span> Audio Zone
            </button>
            <button class="tool-chip" id="tool-object" onclick="setTool('object')">
                <span>🪑</span> Furniture
            </button>
        </div>

        <div class="header-right">
            <button class="action-btn secondary" id="btn-save-draft" onclick="saveMapDraft()">
                <span>💾</span> Save Draft
            </button>
            <button class="action-btn success" id="btn-publish" onclick="publishMap()">
                <span>🚀</span> Publish Map
            </button>
            <a href="{{ route('office') }}" class="action-btn primary" target="_blank">
                <span>👁️</span> Test Live
            </a>
        </div>
    </header>

    <!-- Main Workspace -->
    <div class="editor-workspace">

        <!-- Left Palette -->
        <aside class="palette-sidebar">
            <div>
                <div class="palette-section-title">Room Templates</div>
                <div class="objects-grid">
                    <div class="object-card active" onclick="setRoomType('meeting', '#6366F1')">
                        <div class="object-card-icon">👥</div>
                        <span>Meeting Room</span>
                    </div>
                    <div class="object-card" onclick="setRoomType('private', '#F59E0B')">
                        <div class="object-card-icon">🔒</div>
                        <span>Private Office</span>
                    </div>
                    <div class="object-card" onclick="setRoomType('reception', '#10B981')">
                        <div class="object-card-icon">🛎️</div>
                        <span>Reception</span>
                    </div>
                    <div class="object-card" onclick="setRoomType('lounge', '#EC4899')">
                        <div class="object-card-icon">☕</div>
                        <span>Team Lounge</span>
                    </div>
                </div>
            </div>

            <div>
                <div class="palette-section-title">Office Furniture & Items (16 Items)</div>
                <div class="objects-grid">
                    <div class="object-card" onclick="selectObjectType('desk')">
                        <div class="object-card-icon">💻</div>
                        <span>Workstation</span>
                    </div>
                    <div class="object-card" onclick="selectObjectType('executive_desk')">
                        <div class="object-card-icon">🖥️</div>
                        <span>Exec Desk</span>
                    </div>
                    <div class="object-card" onclick="selectObjectType('chair')">
                        <div class="object-card-icon">🪑</div>
                        <span>Ergo Chair</span>
                    </div>
                    <div class="object-card" onclick="selectObjectType('sofa')">
                        <div class="object-card-icon">🛋️</div>
                        <span>Lounge Sofa</span>
                    </div>
                    <div class="object-card" onclick="selectObjectType('coffee_bar')">
                        <div class="object-card-icon">☕</div>
                        <span>Coffee Bar</span>
                    </div>
                    <div class="object-card" onclick="selectObjectType('whiteboard')">
                        <div class="object-card-icon">📋</div>
                        <span>Whiteboard</span>
                    </div>
                    <div class="object-card" onclick="selectObjectType('screen')">
                        <div class="object-card-icon">📺</div>
                        <span>AV Screen</span>
                    </div>
                    <div class="object-card" onclick="selectObjectType('plant')">
                        <div class="object-card-icon">🪴</div>
                        <span>Plant</span>
                    </div>
                    <div class="object-card" onclick="selectObjectType('wall')">
                        <div class="object-card-icon">🧱</div>
                        <span>Glass Wall</span>
                    </div>
                    <div class="object-card" onclick="selectObjectType('door')">
                        <div class="object-card-icon">🚪</div>
                        <span>Door</span>
                    </div>
                    <div class="object-card" onclick="selectObjectType('pingpong')">
                        <div class="object-card-icon">🏓</div>
                        <span>Ping Pong</span>
                    </div>
                    <div class="object-card" onclick="selectObjectType('water_cooler')">
                        <div class="object-card-icon">🚰</div>
                        <span>Water Cooler</span>
                    </div>
                    <div class="object-card" onclick="selectObjectType('bookshelf')">
                        <div class="object-card-icon">📚</div>
                        <span>Bookshelf</span>
                    </div>
                    <div class="object-card" onclick="selectObjectType('cabinet')">
                        <div class="object-card-icon">🗄️</div>
                        <span>Cabinet</span>
                    </div>
                    <div class="object-card" onclick="selectObjectType('dining_table')">
                        <div class="object-card-icon">🍽️</div>
                        <span>Dining Table</span>
                    </div>
                    <div class="object-card" onclick="selectObjectType('lamp')">
                        <div class="object-card-icon">💡</div>
                        <span>Floor Lamp</span>
                    </div>
                </div>
            </div>

            <div>
                <div class="palette-section-title">Selected Action</div>
                <button class="action-btn secondary" id="btn-delete-selected" style="width: 100%; justify-content: center; color: var(--danger); border-color: rgba(239, 68, 68, 0.4); background: rgba(239, 68, 68, 0.08);" onclick="deleteSelectedItem()">
                    <span>🗑️</span> Delete Selected
                </button>
                <div style="font-size: 11px; color: var(--text-muted); text-align: center; margin-top: 6px;">
                    Or press <strong style="color: #cbd5e1;">Delete / Backspace</strong>
                </div>
            </div>
        </aside>

        <!-- Center Canvas -->
        <div class="canvas-container" id="canvas-container">
            <canvas id="editor-canvas"></canvas>
        </div>

        <!-- Right Properties Inspector -->
        <aside class="inspector-sidebar">
            <div class="inspector-title">
                <span>Properties</span>
                <span id="selection-type-badge" style="font-size: 11px; color: var(--text-muted); background: rgba(255,255,255,0.08); padding: 2px 6px; border-radius: 4px;">FLOOR</span>
            </div>

            <div id="inspector-form" style="display: flex; flex-direction: column; gap: 14px;">
                <div class="prop-group">
                    <label class="prop-label">Name / Label</label>
                    <input type="text" class="prop-input" id="prop-name" value="{{ $map->name }}" oninput="updateSelectedProp('name', this.value)">
                </div>

                <div class="prop-group" id="prop-color-group">
                    <label class="prop-label">Theme Color</label>
                    <input type="color" class="prop-input" id="prop-color" value="#6366F1" style="height: 38px; padding: 2px;" oninput="updateSelectedProp('color', this.value)">
                </div>

                <div class="prop-group" id="prop-capacity-group">
                    <label class="prop-label">Seat Capacity</label>
                    <input type="number" class="prop-input" id="prop-capacity" value="10" min="1" max="100" oninput="updateSelectedProp('capacity', parseInt(this.value) || 1)">
                </div>
            </div>

            <div class="palette-section-title" style="margin-top: 10px;">Floor Statistics</div>
            <div class="stats-box">
                <div class="stat-row">
                    <span>Grid Size:</span>
                    <strong>32 × 24 (32px)</strong>
                </div>
                <div class="stat-row">
                    <span>Total Rooms:</span>
                    <strong id="stat-rooms-count">0</strong>
                </div>
                <div class="stat-row">
                    <span>Audio Zones:</span>
                    <strong id="stat-zones-count">0</strong>
                </div>
                <div class="stat-row">
                    <span>Objects Placed:</span>
                    <strong id="stat-objects-count">0</strong>
                </div>
            </div>

            <div class="palette-section-title" style="margin-top: 10px;">Version Snapshots</div>
            <div class="stats-box" id="snapshots-list" style="gap: 6px;">
                @forelse($map->versions as $ver)
                    <div class="stat-row">
                        <span>v{{ $ver->version }} Snapshot</span>
                        <span>{{ $ver->created_at->format('M d, H:i') }}</span>
                    </div>
                @empty
                    <div style="color: var(--text-muted); font-size: 11px;">No previous snapshots yet.</div>
                @endforelse
            </div>
        </aside>
    </div>

    <!-- Notification Toast -->
    <div class="toast" id="toast-msg">Map Saved Successfully!</div>

    <!-- Map Editor Script -->
    <script>
        const MAP_DATA = @json($map);
        const ORG_ID = "{{ $organization->id }}";
        const CSRF_TOKEN = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        const canvas = document.getElementById('editor-canvas');
        const ctx = canvas.getContext('2d');
        let width = canvas.width = window.innerWidth - 570;
        let height = canvas.height = window.innerHeight - 60;

        window.addEventListener('resize', () => {
            width = canvas.width = window.innerWidth - 570;
            height = canvas.height = window.innerHeight - 60;
            draw();
        });

        const TILE_SIZE = 32;
        let currentTool = 'select'; // select | room | zone | object
        let currentRoomType = 'meeting';
        let currentRoomColor = '#6366F1';
        let currentObjectType = 'desk';

        let rooms = MAP_DATA.rooms || [];
        let zones = MAP_DATA.zones || [];
        let objects = MAP_DATA.objects || [];

        let selectedItem = null; // { type: 'object'|'room'|'zone', item: ref }
        let isDrawing = false;
        let isDragging = false;
        let dragStartTileX = 0;
        let dragStartTileY = 0;
        let dragOrigX = 0;
        let dragOrigY = 0;
        let startX = 0;
        let startY = 0;
        let currentRect = null;

        const OBJECT_CONFIGS = {
            desk: { icon: '💻', name: 'Workstation', collision: true },
            executive_desk: { icon: '🖥️', name: 'Executive Desk', collision: true },
            chair: { icon: '🪑', name: 'Ergo Chair', collision: false },
            sofa: { icon: '🛋️', name: 'Lounge Sofa', collision: true },
            coffee_bar: { icon: '☕', name: 'Coffee Bar', collision: true },
            whiteboard: { icon: '📋', name: 'Whiteboard', collision: true },
            screen: { icon: '📺', name: 'AV Screen', collision: true },
            plant: { icon: '🪴', name: 'Decor Plant', collision: false },
            wall: { icon: '🧱', name: 'Glass Wall', collision: true },
            door: { icon: '🚪', name: 'Office Door', collision: false },
            pingpong: { icon: '🏓', name: 'Ping Pong Table', collision: true },
            water_cooler: { icon: '🚰', name: 'Water Cooler', collision: true },
            bookshelf: { icon: '📚', name: 'Bookshelf', collision: true },
            cabinet: { icon: '🗄️', name: 'Filing Cabinet', collision: true },
            dining_table: { icon: '🍽️', name: 'Dining Table', collision: true },
            lamp: { icon: '💡', name: 'Floor Lamp', collision: false }
        };

        function setTool(tool) {
            currentTool = tool;
            document.querySelectorAll('.tool-chip').forEach(el => el.classList.remove('active'));
            document.getElementById(`tool-${tool}`)?.classList.add('active');
            canvas.style.cursor = tool === 'select' ? 'default' : 'crosshair';
        }

        function setRoomType(type, color) {
            setTool('room');
            currentRoomType = type;
            currentRoomColor = color;
        }

        function selectObjectType(type) {
            setTool('object');
            currentObjectType = type;
        }

        // ── Keyboard Delete Support ──
        window.addEventListener('keydown', (e) => {
            if (e.key === 'Delete' || e.key === 'Backspace') {
                if (document.activeElement.tagName !== 'INPUT') {
                    deleteSelectedItem();
                }
            }
        });

        // ── Mouse & Canvas Interactions ──
        canvas.addEventListener('mousedown', (e) => {
            const rect = canvas.getBoundingClientRect();
            const mouseX = e.clientX - rect.left;
            const mouseY = e.clientY - rect.top;

            const tileX = Math.floor(mouseX / TILE_SIZE);
            const tileY = Math.floor(mouseY / TILE_SIZE);

            if (currentTool === 'select') {
                selectedItem = null;
                attachedObjects = [];

                // 1. Check Objects FIRST (allows clicking objects inside rooms)
                for (let i = objects.length - 1; i >= 0; i--) {
                    const obj = objects[i];
                    if (obj.position.x === tileX && obj.position.y === tileY) {
                        selectedItem = { type: 'object', item: obj };
                        break;
                    }
                }

                // 2. Check Zones
                if (!selectedItem) {
                    for (let i = zones.length - 1; i >= 0; i--) {
                        const z = zones[i];
                        if (tileX >= z.shape_data.x && tileX < z.shape_data.x + z.shape_data.width &&
                            tileY >= z.shape_data.y && tileY < z.shape_data.y + z.shape_data.height) {
                            selectedItem = { type: 'zone', item: z };
                            break;
                        }
                    }
                }

                // 3. Check Rooms
                if (!selectedItem) {
                    for (let i = rooms.length - 1; i >= 0; i--) {
                        const r = rooms[i];
                        if (tileX >= r.bounds.x && tileX < r.bounds.x + r.bounds.width &&
                            tileY >= r.bounds.y && tileY < r.bounds.y + r.bounds.height) {
                            selectedItem = { type: 'room', item: r };
                            break;
                        }
                    }
                }

                if (selectedItem) {
                    isDragging = true;
                    dragStartTileX = tileX;
                    dragStartTileY = tileY;

                    if (selectedItem.type === 'object') {
                        dragOrigX = selectedItem.item.position.x;
                        dragOrigY = selectedItem.item.position.y;
                    } else if (selectedItem.type === 'room') {
                        dragOrigX = selectedItem.item.bounds.x;
                        dragOrigY = selectedItem.item.bounds.y;

                        // Attach all objects currently located inside this room!
                        const rx = dragOrigX;
                        const ry = dragOrigY;
                        const rw = selectedItem.item.bounds.width;
                        const rh = selectedItem.item.bounds.height;

                        attachedObjects = objects.filter(obj =>
                            obj.position.x >= rx && obj.position.x < rx + rw &&
                            obj.position.y >= ry && obj.position.y < ry + rh
                        ).map(obj => ({
                            obj: obj,
                            origX: obj.position.x,
                            origY: obj.position.y
                        }));
                    } else if (selectedItem.type === 'zone') {
                        dragOrigX = selectedItem.item.shape_data.x;
                        dragOrigY = selectedItem.item.shape_data.y;
                    }

                    canvas.style.cursor = 'grabbing';
                }

                updateInspector();
                draw();
            } else if (currentTool === 'room' || currentTool === 'zone') {
                isDrawing = true;
                startX = tileX;
                startY = tileY;
                currentRect = { x: tileX, y: tileY, width: 1, height: 1 };
            } else if (currentTool === 'object') {
                const conf = OBJECT_CONFIGS[currentObjectType] || { name: 'Object', collision: false };
                const newObj = {
                    type: currentObjectType,
                    name: `${conf.name} #${objects.length + 1}`,
                    position: { x: tileX, y: tileY },
                    collision: conf.collision
                };
                objects.push(newObj);
                selectedItem = { type: 'object', item: newObj };
                updateInspector();
                updateStats();
                draw();
            }
        });

        canvas.addEventListener('mousemove', (e) => {
            const rect = canvas.getBoundingClientRect();
            const mouseX = e.clientX - rect.left;
            const mouseY = e.clientY - rect.top;

            const tileX = Math.floor(mouseX / TILE_SIZE);
            const tileY = Math.floor(mouseY / TILE_SIZE);

            if (isDragging && selectedItem) {
                const dx = tileX - dragStartTileX;
                const dy = tileY - dragStartTileY;

                if (selectedItem.type === 'object') {
                    selectedItem.item.position.x = Math.max(0, Math.min(31, dragOrigX + dx));
                    selectedItem.item.position.y = Math.max(0, Math.min(23, dragOrigY + dy));
                } else if (selectedItem.type === 'room') {
                    const newRoomX = Math.max(0, Math.min(32 - selectedItem.item.bounds.width, dragOrigX + dx));
                    const newRoomY = Math.max(0, Math.min(24 - selectedItem.item.bounds.height, dragOrigY + dy));
                    const actualDeltaX = newRoomX - dragOrigX;
                    const actualDeltaY = newRoomY - dragOrigY;

                    selectedItem.item.bounds.x = newRoomX;
                    selectedItem.item.bounds.y = newRoomY;

                    // Synchronously shift all attached furniture with the room
                    if (attachedObjects && attachedObjects.length > 0) {
                        attachedObjects.forEach(att => {
                            att.obj.position.x = Math.max(0, Math.min(31, att.origX + actualDeltaX));
                            att.obj.position.y = Math.max(0, Math.min(23, att.origY + actualDeltaY));
                        });
                    }
                } else if (selectedItem.type === 'zone') {
                    selectedItem.item.shape_data.x = Math.max(0, Math.min(32 - selectedItem.item.shape_data.width, dragOrigX + dx));
                    selectedItem.item.shape_data.y = Math.max(0, Math.min(24 - selectedItem.item.shape_data.height, dragOrigY + dy));
                }

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
                return;
            }

            // Hover cursor preview in select mode
            if (currentTool === 'select') {
                let hover = false;
                for (let i = objects.length - 1; i >= 0; i--) {
                    if (objects[i].position.x === tileX && objects[i].position.y === tileY) {
                        hover = true;
                        break;
                    }
                }
                if (!hover) {
                    for (let i = rooms.length - 1; i >= 0; i--) {
                        const r = rooms[i];
                        if (tileX >= r.bounds.x && tileX < r.bounds.x + r.bounds.width &&
                            tileY >= r.bounds.y && tileY < r.bounds.y + r.bounds.height) {
                            hover = true;
                            break;
                        }
                    }
                }
                canvas.style.cursor = hover ? 'grab' : 'default';
            }
        });

        canvas.addEventListener('mouseup', () => {
            if (isDragging) {
                isDragging = false;
                attachedObjects = [];
                canvas.style.cursor = currentTool === 'select' ? 'grab' : 'crosshair';
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
                        bounds: currentRect
                    };
                    rooms.push(newRoom);
                    selectedItem = { type: 'room', item: newRoom };
                } else if (currentTool === 'zone') {
                    const newZone = {
                        name: 'Audio Zone',
                        type: 'audio',
                        shape_type: 'rectangle',
                        shape_data: currentRect,
                        audible_radius: 160
                    };
                    zones.push(newZone);
                    selectedItem = { type: 'zone', item: newZone };
                }
                isDrawing = false;
                currentRect = null;
                updateInspector();
                updateStats();
                draw();
            }
        });

        function updateInspector() {
            const badge = document.getElementById('selection-type-badge');
            const nameInput = document.getElementById('prop-name');
            const colorInput = document.getElementById('prop-color');
            const capInput = document.getElementById('prop-capacity');
            const colorGroup = document.getElementById('prop-color-group');
            const capGroup = document.getElementById('prop-capacity-group');

            if (selectedItem) {
                badge.textContent = selectedItem.type.toUpperCase();
                nameInput.value = selectedItem.item.name || '';

                if (selectedItem.type === 'room') {
                    colorGroup.style.display = 'flex';
                    capGroup.style.display = 'flex';
                    colorInput.value = selectedItem.item.color || '#6366F1';
                    capInput.value = selectedItem.item.capacity || 10;
                } else {
                    colorGroup.style.display = 'none';
                    capGroup.style.display = 'none';
                }
            } else {
                badge.textContent = 'FLOOR';
                nameInput.value = 'Office Layout';
                colorGroup.style.display = 'none';
                capGroup.style.display = 'none';
            }
        }

        function updateSelectedProp(prop, val) {
            if (!selectedItem) return;
            if (prop === 'name') selectedItem.item.name = val;
            if (prop === 'color') selectedItem.item.color = val;
            if (prop === 'capacity') selectedItem.item.capacity = parseInt(val);
            draw();
        }

        function deleteSelectedItem() {
            if (!selectedItem) {
                showToast('ℹ️ Please click on an item to select it first.', '#F59E0B');
                return;
            }

            const item = selectedItem.item;

            if (selectedItem.type === 'room') {
                const idx = rooms.indexOf(item);
                if (idx > -1) rooms.splice(idx, 1);
                if (item.id) {
                    fetch(`/api/v1/organizations/${ORG_ID}/rooms/${item.id}`, {
                        method: 'DELETE',
                        headers: { 'X-CSRF-TOKEN': CSRF_TOKEN, 'Accept': 'application/json' },
                        credentials: 'same-origin'
                    }).catch(console.error);
                }
                showToast('🗑️ Room removed');
            } else if (selectedItem.type === 'zone') {
                const idx = zones.indexOf(item);
                if (idx > -1) zones.splice(idx, 1);
                if (item.id) {
                    fetch(`/api/v1/organizations/${ORG_ID}/zones/${item.id}`, {
                        method: 'DELETE',
                        headers: { 'X-CSRF-TOKEN': CSRF_TOKEN, 'Accept': 'application/json' },
                        credentials: 'same-origin'
                    }).catch(console.error);
                }
                showToast('🗑️ Audio zone removed');
            } else if (selectedItem.type === 'object') {
                const idx = objects.indexOf(item);
                if (idx > -1) objects.splice(idx, 1);
                showToast('🗑️ Furniture removed');
            }

            selectedItem = null;
            updateInspector();
            updateStats();
            draw();
        }

        function updateStats() {
            document.getElementById('stat-rooms-count').textContent = rooms.length;
            document.getElementById('stat-zones-count').textContent = zones.length;
            document.getElementById('stat-objects-count').textContent = objects.length;
        }

        // ── Helper Canvas Round Rect ──
        function drawRoundedRect(ctx, x, y, width, height, radius) {
            ctx.beginPath();
            ctx.moveTo(x + radius, y);
            ctx.lineTo(x + width - radius, y);
            ctx.quadraticCurveTo(x + width, y, x + width, y + radius);
            ctx.lineTo(x + width, y + height - radius);
            ctx.quadraticCurveTo(x + width, y + height, x + width - radius, y + height);
            ctx.lineTo(x + radius, y + height);
            ctx.quadraticCurveTo(x, y + height, x, y + height - radius);
            ctx.lineTo(x, y + radius);
            ctx.quadraticCurveTo(x, y, x + radius, y);
            ctx.closePath();
        }

        // ── Top-Down Furniture Sprites for Editor ──
        const FURN_SHEET1 = new Image();
        FURN_SHEET1.src = '/images/furniture/furniture_sheet1.webp';
        let furnSheet1Loaded = false;
        FURN_SHEET1.onload = () => { furnSheet1Loaded = true; draw(); };

        const FURN_SHEET2 = new Image();
        FURN_SHEET2.src = '/images/furniture/furniture_sheet2.webp';
        let furnSheet2Loaded = false;
        FURN_SHEET2.onload = () => { furnSheet2Loaded = true; draw(); };

        const EDITOR_3D_FURNITURE = {
            desk: (x, y) => {
                if (furnSheet1Loaded) {
                    ctx.drawImage(FURN_SHEET1, 40, 205, 260, 215, x - 4, y - 6, 40, 38);
                } else {
                    ctx.fillStyle = 'rgba(0,0,0,0.3)'; ctx.fillRect(x + 3, y + 6, 27, 21);
                    ctx.fillStyle = '#5c4033'; ctx.fillRect(x + 2, y + 4, 28, 22);
                    ctx.fillStyle = '#38bdf8'; ctx.fillRect(x + 9, y + 11, 12, 5);
                }
            },
            executive_desk: (x, y) => {
                if (furnSheet2Loaded) {
                    ctx.drawImage(FURN_SHEET2, 500, 70, 340, 500, x - 8, y - 10, 48, 52);
                } else {
                    ctx.fillStyle = 'rgba(0,0,0,0.35)'; ctx.fillRect(x + 3, y + 5, 27, 23);
                    ctx.fillStyle = '#271610'; ctx.fillRect(x + 2, y + 3, 28, 24);
                    ctx.fillStyle = '#38bdf8'; ctx.fillRect(x + 5, y + 6, 9, 5);
                }
            },
            chair: (x, y) => {
                if (furnSheet1Loaded) {
                    ctx.drawImage(FURN_SHEET1, 50, 68, 120, 105, x + 2, y + 2, 28, 28);
                } else {
                    ctx.fillStyle = '#18181b'; drawRoundedRect(ctx, x + 8, y + 8, 16, 16, 4); ctx.fill();
                }
            },
            sofa: (x, y) => {
                if (furnSheet1Loaded) {
                    ctx.drawImage(FURN_SHEET1, 400, 830, 245, 115, x - 4, y - 2, 40, 28);
                } else {
                    ctx.fillStyle = '#18181b'; drawRoundedRect(ctx, x + 2, y + 4, 28, 22, 4); ctx.fill();
                }
            },
            plant: (x, y) => {
                if (furnSheet1Loaded) {
                    ctx.drawImage(FURN_SHEET1, 310, 68, 110, 110, x + 2, y + 2, 28, 28);
                } else {
                    ctx.fillStyle = '#78350f'; ctx.beginPath(); ctx.arc(x + 16, y + 16, 7, 0, Math.PI * 2); ctx.fill();
                    ctx.fillStyle = '#16a34a'; ctx.beginPath(); ctx.arc(x + 16, y + 16, 5, 0, Math.PI * 2); ctx.fill();
                }
            },
            dining_table: (x, y) => {
                if (furnSheet1Loaded) {
                    ctx.drawImage(FURN_SHEET1, 600, 80, 360, 320, x - 8, y - 8, 48, 48);
                } else {
                    ctx.fillStyle = '#3e2723'; drawRoundedRect(ctx, x + 2, y + 2, 28, 28, 5); ctx.fill();
                }
            },
            whiteboard: (x, y) => {
                ctx.fillStyle = '#ffffff'; ctx.fillRect(x + 2, y + 6, 28, 14);
                ctx.strokeStyle = '#64748b'; ctx.lineWidth = 1.5; ctx.strokeRect(x + 2, y + 6, 28, 14);
                ctx.fillStyle = '#38bdf8'; ctx.fillRect(x + 6, y + 8, 4, 3);
            },
            screen: (x, y) => {
                ctx.fillStyle = '#09090b'; ctx.fillRect(x + 2, y + 4, 28, 16);
                ctx.fillStyle = '#0284c7'; ctx.fillRect(x + 4, y + 6, 24, 12);
            },
            water_cooler: (x, y) => {
                ctx.fillStyle = '#e2e8f0'; ctx.fillRect(x + 8, y + 10, 16, 18);
                ctx.fillStyle = '#0284c7'; ctx.beginPath(); ctx.arc(x + 16, y + 10, 7, 0, Math.PI * 2); ctx.fill();
            }
        };

        // ── Render Function ──
        function draw() {
            ctx.clearRect(0, 0, width, height);

            // 1. Grid
            ctx.strokeStyle = 'rgba(255, 255, 255, 0.04)';
            ctx.lineWidth = 1;
            for (let x = 0; x < width; x += TILE_SIZE) {
                ctx.beginPath();
                ctx.moveTo(x, 0);
                ctx.lineTo(x, height);
                ctx.stroke();
            }
            for (let y = 0; y < height; y += TILE_SIZE) {
                ctx.beginPath();
                ctx.moveTo(0, y);
                ctx.lineTo(width, y);
                ctx.stroke();
            }

            // 2. Zones
            zones.forEach(z => {
                const zx = z.shape_data.x * TILE_SIZE;
                const zy = z.shape_data.y * TILE_SIZE;
                const zw = z.shape_data.width * TILE_SIZE;
                const zh = z.shape_data.height * TILE_SIZE;

                ctx.fillStyle = 'rgba(16, 185, 129, 0.08)';
                ctx.fillRect(zx, zy, zw, zh);

                const isSelected = selectedItem && selectedItem.type === 'zone' && selectedItem.item === z;
                ctx.strokeStyle = isSelected ? '#ffffff' : 'rgba(16, 185, 129, 0.4)';
                ctx.lineWidth = isSelected ? 2.5 : 1.5;
                ctx.setLineDash([4, 4]);
                ctx.strokeRect(zx, zy, zw, zh);
                ctx.setLineDash([]);

                ctx.fillStyle = '#6ee7b7';
                ctx.font = '11px Inter';
                ctx.fillText(`🎙️ ${z.name}`, zx + 6, zy + 16);
            });

            // 3. Rooms
            rooms.forEach((r) => {
                const rx = r.bounds.x * TILE_SIZE;
                const ry = r.bounds.y * TILE_SIZE;
                const rw = r.bounds.width * TILE_SIZE;
                const rh = r.bounds.height * TILE_SIZE;

                ctx.fillStyle = 'rgba(30, 41, 59, 0.7)';
                ctx.fillRect(rx, ry, rw, rh);

                const isSelected = selectedItem && selectedItem.type === 'room' && selectedItem.item === r;
                ctx.strokeStyle = isSelected ? '#ffffff' : (r.color || '#6366F1');
                ctx.lineWidth = isSelected ? 3 : 2;
                ctx.strokeRect(rx, ry, rw, rh);

                if (isSelected) {
                    ctx.fillStyle = 'rgba(255, 255, 255, 0.05)';
                    ctx.fillRect(rx, ry, rw, rh);
                }

                ctx.fillStyle = '#f8fafc';
                ctx.font = '700 12px Inter';
                ctx.fillText(`🏢 ${r.name}`, rx + 8, ry + 20);
            });

            // 4. 3D Objects
            objects.forEach((obj) => {
                const ox = obj.position.x * TILE_SIZE;
                const oy = obj.position.y * TILE_SIZE;

                if (EDITOR_3D_FURNITURE[obj.type]) {
                    EDITOR_3D_FURNITURE[obj.type](ox, oy);
                } else {
                    ctx.fillStyle = 'rgba(255, 255, 255, 0.08)';
                    ctx.fillRect(ox + 2, oy + 2, TILE_SIZE - 4, TILE_SIZE - 4);
                    ctx.font = '18px Inter';
                    ctx.textAlign = 'center';
                    ctx.textBaseline = 'middle';
                    const conf = OBJECT_CONFIGS[obj.type] || { icon: '📦' };
                    ctx.fillText(conf.icon, ox + TILE_SIZE / 2, oy + TILE_SIZE / 2);
                    ctx.textAlign = 'start';
                    ctx.textBaseline = 'alphabetic';
                }

                const isSelected = selectedItem && selectedItem.type === 'object' && selectedItem.item === obj;
                if (isSelected) {
                    ctx.strokeStyle = '#ffffff';
                    ctx.lineWidth = 2.5;
                    ctx.strokeRect(ox, oy, TILE_SIZE, TILE_SIZE);
                }
            });

            // 5. Active Drawing Rect
            if (isDrawing && currentRect) {
                const dx = currentRect.x * TILE_SIZE;
                const dy = currentRect.y * TILE_SIZE;
                const dw = currentRect.width * TILE_SIZE;
                const dh = currentRect.height * TILE_SIZE;

                ctx.fillStyle = currentTool === 'room' ? 'rgba(99, 102, 241, 0.2)' : 'rgba(16, 185, 129, 0.2)';
                ctx.fillRect(dx, dy, dw, dh);
                ctx.strokeStyle = currentTool === 'room' ? '#6366F1' : '#10B981';
                ctx.lineWidth = 2;
                ctx.strokeRect(dx, dy, dw, dh);
            }
        }

        // ── API Actions (Save Draft & Publish) ──
        async function saveMapDraft() {
            try {
                // 1. Sync Rooms (creates new ones and updates moved/edited existing ones)
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
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': CSRF_TOKEN
                            },
                            credentials: 'same-origin',
                            body: JSON.stringify(payload)
                        });
                        const data = await res.json();
                        if (data.room?.id) r.id = data.room.id;
                    } else {
                        await fetch(`/api/v1/organizations/${ORG_ID}/rooms/${r.id}`, {
                            method: 'PATCH',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': CSRF_TOKEN
                            },
                            credentials: 'same-origin',
                            body: JSON.stringify(payload)
                        });
                    }
                }

                // 2. Sync Objects
                await fetch(`/api/v1/organizations/${ORG_ID}/maps/${MAP_DATA.id}/objects/sync`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': CSRF_TOKEN
                    },
                    credentials: 'same-origin',
                    body: JSON.stringify({ objects })
                });

                showToast('✅ Map Draft & Furniture Saved!');
                return true;
            } catch (err) {
                console.error(err);
                showToast('❌ Error saving draft', '#EF4444');
                return false;
            }
        }

        async function publishMap() {
            try {
                const saved = await saveMapDraft();
                if (!saved) return;

                const res = await fetch(`/api/v1/organizations/${ORG_ID}/maps/${MAP_DATA.id}/publish`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': CSRF_TOKEN
                    },
                    credentials: 'same-origin'
                });

                if (!res.ok) {
                    const errData = await res.json();
                    throw new Error(errData.message || 'Publishing failed.');
                }

                const data = await res.json();
                document.getElementById('header-version-badge').textContent = `v${data.map.version} (Published)`;
                showToast(`🚀 Map Published! Snapshot v${data.map.version} is now live.`);
            } catch (err) {
                console.error(err);
                showToast(`❌ Error: ${err.message || 'Could not publish map'}`, '#EF4444');
            }
        }

        function showToast(msg, bg) {
            const toast = document.getElementById('toast-msg');
            toast.textContent = msg;
            if (bg) toast.style.background = bg;
            else toast.style.background = 'rgba(16, 185, 129, 0.95)';
            toast.style.display = 'block';
            setTimeout(() => { toast.style.display = 'none'; }, 3500);
        }

        updateStats();
        draw();
    </script>
</body>
</html>
