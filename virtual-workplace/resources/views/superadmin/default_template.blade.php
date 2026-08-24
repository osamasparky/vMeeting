@extends('superadmin.layout')

@section('title', __('Default Office Template & Visual Room Designer'))
@section('page_title', __('Default Office Blueprint & Rooms'))

@section('content')
<meta name="csrf-token" content="{{ csrf_token() }}">

<!-- ── Header Banner ── -->
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; flex-wrap: wrap; gap: 16px;">
    <div>
        <h2 style="font-size: 20px; font-weight: 900; color: var(--text-primary); margin: 0 0 4px 0;">
            🏢 {{ __('Visual Default Office & Room Designer (المصمم المرئي لغرف المكتب الافتراضي)') }}
        </h2>
        <p style="font-size: 13px; color: var(--text-muted); margin: 0;">
            {{ __('Draw rooms directly on the blueprint, rename them, set acoustic boundaries, and configure capacities for all organizations.') }}
        </p>
    </div>

    <div style="display: flex; gap: 10px; align-items: center; flex-wrap: wrap;">
        <button type="button" onclick="openSyncModal()" class="tactile-btn" style="background: rgba(37, 99, 235, 0.15); border-color: rgba(59, 130, 246, 0.35); color: #93C5FD; font-size: 13px;">
            🔄 {{ __('Sync to All Companies (:count)', ['count' => $totalCompanies]) }}
        </button>
        <button type="button" onclick="saveAllRoomsToServer()" class="tactile-btn btn-primary" style="font-size: 13px; box-shadow: 0 4px 14px rgba(36, 92, 58, 0.4);">
            💾 {{ __('Save All Rooms (حفظ كافة الغرف)') }}
        </button>
    </div>
</div>

<!-- ════════════════════════════════════════════════════════════
     MAIN VISUAL ROOM DESIGNER CANVAS & LIVE INSPECTOR
     ════════════════════════════════════════════════════════════ -->
<div style="display: grid; grid-template-columns: 1fr 360px; gap: 20px; margin-bottom: 24px;">

    <!-- Left: Interactive Canvas Viewport -->
    <div class="panel-card" style="padding: 0; border-radius: 20px; overflow: hidden; display: flex; flex-direction: column; background: #07120C; border: 1px solid var(--border-color); box-shadow: var(--shadow-card);">
        
        <!-- Canvas Studio Toolbar -->
        <div style="padding: 12px 18px; background: var(--bg-surface-subtle); border-bottom: 1px solid var(--border-color); display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px;">
            <!-- Left: Tool Selector -->
            <div style="display: flex; gap: 6px; align-items: center; background: var(--bg-base); padding: 4px; border-radius: 12px; border: 1px solid var(--border-color);">
                <button type="button" id="tool-btn-select" onclick="setDrawTool('select')" class="tactile-btn" style="padding: 6px 14px; font-size: 12px; border: none; background: var(--accent-gradient); color: white;">
                    <span>🖱️</span> <span>{{ __('Select & Move') }}</span>
                </button>
                <button type="button" id="tool-btn-draw" onclick="setDrawTool('draw')" class="tactile-btn" style="padding: 6px 14px; font-size: 12px; border: none; background: transparent; color: var(--text-secondary);">
                    <span>✏️</span> <span>{{ __('Draw Room (رسم غرفة)') }}</span>
                </button>
            </div>

            <!-- Center: Blueprint Status -->
            <div style="font-size: 12px; font-weight: 800; color: var(--brand-forest); display: flex; align-items: center; gap: 8px;">
                <span>📐 {{ $template->width }}x{{ $template->height }} Grid</span>
                <span style="color: var(--text-muted);">•</span>
                <span id="canvas-rooms-count">{{ count($template->rooms_data ?: []) }} {{ __('Rooms Configured') }}</span>
            </div>

            <!-- Right: Zoom & Reset Controls -->
            <div style="display: flex; gap: 6px; align-items: center;">
                <button type="button" onclick="adjustZoom(-0.15)" class="tactile-btn btn-secondary" style="padding: 6px 10px; font-size: 12px;" title="Zoom Out">🔍−</button>
                <button type="button" onclick="adjustZoom(0.15)" class="tactile-btn btn-secondary" style="padding: 6px 10px; font-size: 12px;" title="Zoom In">🔍+</button>
                <button type="button" onclick="resetCanvasView()" class="tactile-btn btn-secondary" style="padding: 6px 12px; font-size: 12px;" title="Fit / Reset View">🎯 {{ __('Fit') }}</button>
            </div>
        </div>

        <!-- Canvas Workspace Container -->
        <div id="canvas-viewport" style="position: relative; width: 100%; height: 560px; overflow: hidden; background: radial-gradient(circle at center, #0e1e16 0%, #060e0a 100%); cursor: default;">
            <canvas id="designer-canvas" style="display: block; width: 100%; height: 100%;"></canvas>

            <!-- Floating Prompt Hint -->
            <div id="draw-hint-overlay" style="position: absolute; bottom: 14px; inset-inline-start: 14px; background: rgba(15, 23, 42, 0.9); backdrop-filter: blur(10px); border: 1px solid rgba(255,255,255,0.15); padding: 8px 14px; border-radius: 12px; font-size: 11px; color: #F8FAFC; display: flex; align-items: center; gap: 8px; pointer-events: none; transition: all 0.2s;">
                <span id="draw-hint-icon">💡</span>
                <span id="draw-hint-text">{{ __('Click on any room to select and rename it, or switch to "Draw Room" to create a new room box.') }}</span>
            </div>
        </div>

        <!-- Bottom Blueprint Upload Footer -->
        <div style="padding: 12px 18px; background: var(--bg-surface-subtle); border-top: 1px solid var(--border-color); display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px;">
            <div style="font-size: 12px; color: var(--text-secondary);">
                <span>🖼️ <strong>{{ __('Floorplan Artwork') }}:</strong> {{ basename($template->background_image_url ?: 'office_floorplan.jpg') }}</span>
            </div>
            <form method="POST" action="{{ route('superadmin.template.background') }}" enctype="multipart/form-data" style="margin: 0; display: flex; gap: 8px;">
                @csrf
                <input type="file" name="background" id="template_bg_input" accept="image/jpeg,image/png,image/webp,image/jpg" style="display:none;" onchange="this.form.submit()">
                <button type="button" onclick="document.getElementById('template_bg_input').click()" class="tactile-btn btn-secondary" style="font-size: 11px; padding: 6px 12px;">
                    📁 {{ __('Change Background Floorplan') }}
                </button>
            </form>
        </div>
    </div>

    <!-- Right: Live Room Inspector & Rename Studio -->
    <div style="display: flex; flex-direction: column; gap: 20px;">
        
        <!-- Inspector Card -->
        <div class="panel-card" style="padding: 22px; border-radius: 20px;">
            <div class="panel-header" style="margin-bottom: 16px;">
                <div class="panel-title">
                    <span>✏️</span>
                    <span id="inspector-header-title">{{ __('Room Inspector & Rename') }}</span>
                </div>
            </div>

            <!-- Empty State when no room is selected -->
            <div id="inspector-empty" style="text-align: center; padding: 30px 10px; color: var(--text-muted);">
                <div style="font-size: 38px; margin-bottom: 10px;">🚪</div>
                <strong style="display: block; font-size: 13px; color: var(--text-primary); margin-bottom: 4px;">{{ __('No Room Selected') }}</strong>
                <p style="font-size: 12px; margin: 0; line-height: 1.5;">
                    {{ __('Click on any room on the blueprint or click "Draw Room" to create and configure a new room.') }}
                </p>
            </div>

            <!-- Active Inspector Form -->
            <div id="inspector-form" style="display: none; flex-direction: column; gap: 14px;">
                
                <!-- 1. Room Name (Rename) -->
                <div>
                    <label style="display: block; font-size: 11px; font-weight: 800; color: var(--brand-forest); margin-bottom: 6px;">
                        🏷️ {{ __('Room Name (اسم الغرفة والمنطقة)') }}
                    </label>
                    <input type="text" id="insp-name" oninput="updateSelectedRoomProp('name', this.value)" placeholder="e.g. Executive Board Room" style="width: 100%; background: var(--bg-surface-subtle); border: 2px solid var(--brand-forest); border-radius: 10px; padding: 10px 12px; color: var(--text-primary); font-size: 13px; font-weight: 800; outline: none; box-shadow: var(--shadow-inset-3d);">
                </div>

                <!-- 2. Room Type & Access Mode -->
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px;">
                    <div>
                        <label style="display: block; font-size: 11px; font-weight: 800; color: var(--text-secondary); margin-bottom: 4px;">{{ __('Room Type') }}</label>
                        <select id="insp-type" onchange="updateSelectedRoomProp('type', this.value)" style="width: 100%; background: var(--bg-surface-subtle); border: 1px solid var(--border-color); border-radius: 8px; padding: 8px; color: var(--text-primary); font-size: 11px; font-weight: 700; outline: none;">
                            <option value="meeting">Meeting / Conference</option>
                            <option value="private">Private Office / Focus</option>
                            <option value="lounge">Lounge / Collaborative</option>
                            <option value="breakout">Breakout Room</option>
                            <option value="reception">Reception / Lobby</option>
                        </select>
                    </div>
                    <div>
                        <label style="display: block; font-size: 11px; font-weight: 800; color: var(--text-secondary); margin-bottom: 4px;">{{ __('Access Privacy') }}</label>
                        <select id="insp-access" onchange="updateSelectedRoomProp('access_mode', this.value)" style="width: 100%; background: var(--bg-surface-subtle); border: 1px solid var(--border-color); border-radius: 8px; padding: 8px; color: var(--text-primary); font-size: 11px; font-weight: 700; outline: none;">
                            <option value="public">🟢 Public (Open)</option>
                            <option value="knock">✊ Knock to Enter</option>
                            <option value="locked">🔒 Locked by Default</option>
                        </select>
                    </div>
                </div>

                <!-- 3. Capacity & Theme Color -->
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px;">
                    <div>
                        <label style="display: block; font-size: 11px; font-weight: 800; color: var(--text-secondary); margin-bottom: 4px;">{{ __('Capacity (Seats)') }}</label>
                        <input type="number" id="insp-capacity" min="1" max="100" oninput="updateSelectedRoomProp('capacity', parseInt(this.value) || 1)" style="width: 100%; background: var(--bg-surface-subtle); border: 1px solid var(--border-color); border-radius: 8px; padding: 8px; color: var(--text-primary); font-size: 12px; font-weight: 700; outline: none;">
                    </div>
                    <div>
                        <label style="display: block; font-size: 11px; font-weight: 800; color: var(--text-secondary); margin-bottom: 4px;">{{ __('Theme Color') }}</label>
                        <input type="color" id="insp-color" oninput="updateSelectedRoomProp('color', this.value)" style="width: 100%; height: 34px; background: var(--bg-surface-subtle); border: 1px solid var(--border-color); border-radius: 8px; padding: 2px; cursor: pointer;">
                    </div>
                </div>

                <!-- 4. Acoustic Sound Isolation Boundary -->
                <div style="background: var(--bg-surface-subtle); border: 1px solid var(--border-color); border-radius: 10px; padding: 10px;">
                    <label style="display: flex; align-items: center; gap: 8px; cursor: pointer; font-size: 11px; font-weight: 700; color: var(--text-primary);">
                        <input type="checkbox" id="insp-isolation" onchange="updateSelectedRoomIsolation(this.checked)" style="width: 16px; height: 16px;">
                        <span>🎙️ {{ __('Acoustic Sound Isolation (عزل الصوت)') }}</span>
                    </label>
                </div>

                <!-- 5. Grid Bounds (X, Y, W, H) -->
                <div style="background: var(--bg-surface-subtle); border: 1px solid var(--border-color); border-radius: 10px; padding: 10px;">
                    <span style="display: block; font-size: 10px; font-weight: 800; color: var(--text-muted); text-transform: uppercase; margin-bottom: 6px;">📐 {{ __('Grid Tile Bounds') }}</span>
                    <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 6px;">
                        <div>
                            <label style="font-size: 9px; color: var(--text-muted);">X</label>
                            <input type="number" id="insp-x" min="0" oninput="updateSelectedRoomBound('x', parseInt(this.value) || 0)" style="width: 100%; background: var(--bg-card); border: 1px solid var(--border-color); border-radius: 6px; padding: 4px; color: var(--text-primary); font-size: 11px; font-weight: 700; text-align: center;">
                        </div>
                        <div>
                            <label style="font-size: 9px; color: var(--text-muted);">Y</label>
                            <input type="number" id="insp-y" min="0" oninput="updateSelectedRoomBound('y', parseInt(this.value) || 0)" style="width: 100%; background: var(--bg-card); border: 1px solid var(--border-color); border-radius: 6px; padding: 4px; color: var(--text-primary); font-size: 11px; font-weight: 700; text-align: center;">
                        </div>
                        <div>
                            <label style="font-size: 9px; color: var(--text-muted);">W</label>
                            <input type="number" id="insp-w" min="1" oninput="updateSelectedRoomBound('width', parseInt(this.value) || 1)" style="width: 100%; background: var(--bg-card); border: 1px solid var(--border-color); border-radius: 6px; padding: 4px; color: var(--text-primary); font-size: 11px; font-weight: 700; text-align: center;">
                        </div>
                        <div>
                            <label style="font-size: 9px; color: var(--text-muted);">H</label>
                            <input type="number" id="insp-h" min="1" oninput="updateSelectedRoomBound('height', parseInt(this.value) || 1)" style="width: 100%; background: var(--bg-card); border: 1px solid var(--border-color); border-radius: 6px; padding: 4px; color: var(--text-primary); font-size: 11px; font-weight: 700; text-align: center;">
                        </div>
                    </div>
                </div>

                <!-- 6. Delete Room Button -->
                <button type="button" onclick="deleteCurrentSelectedRoom()" class="tactile-btn" style="width: 100%; justify-content: center; background: rgba(217, 107, 95, 0.15); border-color: rgba(217, 107, 95, 0.35); color: #D96B5F; font-size: 12px; padding: 8px;">
                    🗑️ {{ __('Delete this Room (حذف الغرفة)') }}
                </button>
            </div>
        </div>

        <!-- Blueprint Settings Summary Card -->
        <div class="panel-card" style="padding: 20px; border-radius: 20px;">
            <div class="panel-header" style="margin-bottom: 12px;">
                <div class="panel-title">
                    <span>⚙️</span>
                    <span>{{ __('Grid Dimensions') }}</span>
                </div>
            </div>

            <form method="POST" action="{{ route('superadmin.template.update') }}">
                @csrf
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-bottom: 12px;">
                    <div>
                        <label style="display: block; font-size: 10px; font-weight: 800; color: var(--text-secondary); margin-bottom: 4px;">{{ __('Grid Width') }}</label>
                        <input type="number" name="width" value="{{ $template->width }}" min="10" max="100" required style="width: 100%; background: var(--bg-surface-subtle); border: 1px solid var(--border-color); border-radius: 8px; padding: 8px; color: var(--text-primary); font-size: 12px; font-weight: 700;">
                    </div>
                    <div>
                        <label style="display: block; font-size: 10px; font-weight: 800; color: var(--text-secondary); margin-bottom: 4px;">{{ __('Grid Height') }}</label>
                        <input type="number" name="height" value="{{ $template->height }}" min="10" max="100" required style="width: 100%; background: var(--bg-surface-subtle); border: 1px solid var(--border-color); border-radius: 8px; padding: 8px; color: var(--text-primary); font-size: 12px; font-weight: 700;">
                    </div>
                </div>

                <input type="hidden" name="name" value="{{ $template->name }}">
                <input type="hidden" name="tile_size" value="{{ $template->tile_size }}">

                <button type="submit" class="tactile-btn btn-secondary" style="width: 100%; justify-content: center; padding: 8px; font-size: 11px;">
                    💾 {{ __('Update Grid Size') }}
                </button>
            </form>
        </div>
    </div>
</div>

<!-- ════════════════════════════════════════════════════════════
     ROOMS ROSTER TABLE & DIRECT JUMP LIST
     ════════════════════════════════════════════════════════════ -->
<div class="panel-card" style="border-radius: 20px; padding: 24px;">
    <div class="panel-header" style="margin-bottom: 16px;">
        <div class="panel-title">
            <span>📋</span>
            <span>{{ __('All Preconfigured Default Rooms Roster') }}</span>
        </div>
        <button type="button" onclick="setDrawTool('draw')" class="tactile-btn btn-primary" style="font-size: 12px; padding: 6px 14px;">
            ✏️ {{ __('Draw Another Room (رسم غرفة)') }}
        </button>
    </div>

    <div class="data-table-container">
        <table class="data-table" id="rooms-roster-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>{{ __('Room Name') }}</th>
                    <th>{{ __('Type') }}</th>
                    <th>{{ __('Access Mode') }}</th>
                    <th>{{ __('Capacity') }}</th>
                    <th>{{ __('Grid Bounds') }}</th>
                    <th>{{ __('Acoustic Wall') }}</th>
                    <th>{{ __('Actions') }}</th>
                </tr>
            </thead>
            <tbody id="rooms-roster-tbody">
                <!-- Rendered dynamically by JavaScript -->
            </tbody>
        </table>
    </div>
</div>

<!-- ── Modal: Sync to All Companies ── -->
<div id="syncModal" class="modal-overlay">
    <div class="modal-card" style="border-radius: 24px; padding: 26px; max-width: 480px;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
            <h3 style="font-size: 17px; font-weight: 900; color: var(--text-primary);">🔄 {{ __('Sync Template to Organizations') }}</h3>
            <button onclick="closeSyncModal()" style="background: var(--bg-surface-subtle); border: 1px solid var(--border-color); width: 32px; height: 32px; border-radius: 50%; display: flex; align-items: center; justify-content: center; cursor: pointer; color: var(--text-primary); font-weight: 800;">✕</button>
        </div>

        <p style="font-size: 13px; color: var(--text-secondary); line-height: 1.6; margin-bottom: 20px;">
            {{ __('This will synchronize the master blueprint floorplan and your newly drawn and renamed default rooms across all :count registered organizations.', ['count' => $totalCompanies]) }}
        </p>

        <form method="POST" action="{{ route('superadmin.template.sync') }}">
            @csrf
            <div style="background: rgba(214, 162, 58, 0.1); border: 1px solid rgba(214, 162, 58, 0.3); border-radius: 12px; padding: 14px; margin-bottom: 20px;">
                <label style="display: flex; align-items: flex-start; gap: 10px; cursor: pointer; font-size: 12px; font-weight: 700; color: var(--text-primary);">
                    <input type="checkbox" name="overwrite_rooms" value="1" checked style="margin-top: 2px;">
                    <span>⚠️ {{ __('Overwrite and apply these exact rooms & boundaries to all companies (إعادة تعيين الغرف بالكامل)') }}</span>
                </label>
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 10px;">
                <button type="button" onclick="closeSyncModal()" class="tactile-btn btn-secondary">{{ __('Cancel') }}</button>
                <button type="submit" class="tactile-btn btn-primary">🚀 {{ __('Execute Sync Now') }}</button>
            </div>
        </form>
    </div>
</div>

@endsection

@section('scripts')
<script>
    // ── Template Data Initialization ──
    const CSRF_TOKEN = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    const MAP_WIDTH_TILES = {{ $template->width ?? 32 }};
    const MAP_HEIGHT_TILES = {{ $template->height ?? 26 }};
    const TILE_SIZE = 32;
    const MAP_WIDTH_PX = MAP_WIDTH_TILES * TILE_SIZE;
    const MAP_HEIGHT_PX = MAP_HEIGHT_TILES * TILE_SIZE;

    let defaultRooms = @json($template->rooms_data ?: []);
    let selectedRoomIndex = null;
    let currentTool = 'select'; // 'select' or 'draw'

    // Canvas Setup
    const canvas = document.getElementById('designer-canvas');
    const ctx = canvas.getContext('2d');
    const viewport = document.getElementById('canvas-viewport');

    let zoomLevel = 1.0;
    let panOffset = { x: 0, y: 0 };
    let isPanning = false;
    let startPan = { x: 0, y: 0 };

    let isDrawing = false;
    let drawStartTile = null;
    let currentDragRect = null;

    let isDraggingRoom = false;
    let dragRoomStart = { x: 0, y: 0 };
    let dragRoomInitialBounds = null;

    let isResizing = false;
    let resizeHandle = null; // 'nw', 'ne', 'se', 'sw'

    // Preload Blueprint Background Image
    const BG_URL = "{{ $template->background_image_url ?: '/images/office_floorplan.jpg' }}";
    const BLUEPRINT_IMG = new Image();
    BLUEPRINT_IMG.src = BG_URL + (BG_URL.includes('?') ? '&' : '?') + 'v=' + Date.now();
    let bgLoaded = false;
    BLUEPRINT_IMG.onload = () => {
        bgLoaded = true;
        resetCanvasView();
    };
    BLUEPRINT_IMG.onerror = () => {
        bgLoaded = false;
        resetCanvasView();
    };

    function resizeCanvasBuffer() {
        if (!viewport) return;
        canvas.width = viewport.clientWidth || 800;
        canvas.height = viewport.clientHeight || 560;
        drawCanvas();
    }
    window.addEventListener('resize', resizeCanvasBuffer);

    function resetCanvasView() {
        resizeCanvasBuffer();
        if (canvas.width && canvas.height) {
            const scaleX = (canvas.width - 40) / MAP_WIDTH_PX;
            const scaleY = (canvas.height - 40) / MAP_HEIGHT_PX;
            zoomLevel = Math.min(scaleX, scaleY, 1.0);
            panOffset.x = (canvas.width - MAP_WIDTH_PX * zoomLevel) / 2;
            panOffset.y = (canvas.height - MAP_HEIGHT_PX * zoomLevel) / 2;
        }
        drawCanvas();
    }

    function adjustZoom(delta) {
        zoomLevel = Math.max(0.3, Math.min(2.5, zoomLevel + delta));
        drawCanvas();
    }

    function setDrawTool(tool) {
        currentTool = tool;
        document.getElementById('tool-btn-select').style.background = tool === 'select' ? 'var(--accent-gradient)' : 'transparent';
        document.getElementById('tool-btn-select').style.color = tool === 'select' ? 'white' : 'var(--text-secondary)';
        document.getElementById('tool-btn-draw').style.background = tool === 'draw' ? 'var(--accent-gradient)' : 'transparent';
        document.getElementById('tool-btn-draw').style.color = tool === 'draw' ? 'white' : 'var(--text-secondary)';

        const hintEl = document.getElementById('draw-hint-text');
        if (tool === 'draw') {
            canvas.style.cursor = 'crosshair';
            hintEl.textContent = "{{ __('Draw Mode: Click and drag anywhere on the blueprint to draw a new room.') }}";
        } else {
            canvas.style.cursor = 'default';
            hintEl.textContent = "{{ __('Select Mode: Click a room to rename it or drag handles to resize.') }}";
        }
        drawCanvas();
    }

    // ── Mouse & Touch Event Handlers ──
    function screenToTile(e) {
        const rect = canvas.getBoundingClientRect();
        const screenX = e.clientX - rect.left;
        const screenY = e.clientY - rect.top;
        const worldX = (screenX - panOffset.x) / zoomLevel;
        const worldY = (screenY - panOffset.y) / zoomLevel;
        return {
            x: Math.floor(worldX / TILE_SIZE),
            y: Math.floor(worldY / TILE_SIZE),
            worldX,
            worldY
        };
    }

    canvas.addEventListener('mousedown', (e) => {
        if (e.button === 1 || (e.button === 0 && e.shiftKey)) { // Middle click or Shift+drag = Pan
            isPanning = true;
            startPan = { x: e.clientX - panOffset.x, y: e.clientY - panOffset.y };
            return;
        }

        if (e.button !== 0) return;
        const pos = screenToTile(e);

        if (currentTool === 'draw') {
            isDrawing = true;
            drawStartTile = { x: Math.max(0, pos.x), y: Math.max(0, pos.y) };
            currentDragRect = { x: drawStartTile.x, y: drawStartTile.y, width: 1, height: 1 };
            drawCanvas();
            return;
        }

        // Check if clicked inside a room or resize handle
        let clickedRoomIdx = null;
        for (let i = defaultRooms.length - 1; i >= 0; i--) {
            const r = defaultRooms[i];
            const b = r.bounds;
            if (b && pos.x >= b.x && pos.x < (b.x + b.width) && pos.y >= b.y && pos.y < (b.y + b.height)) {
                clickedRoomIdx = i;
                break;
            }
        }

        selectRoom(clickedRoomIdx);

        if (clickedRoomIdx !== null) {
            isDraggingRoom = true;
            dragRoomStart = { x: pos.x, y: pos.y };
            const curB = defaultRooms[clickedRoomIdx].bounds;
            dragRoomInitialBounds = { ...curB };
        }
    });

    canvas.addEventListener('mousemove', (e) => {
        if (isPanning) {
            panOffset.x = e.clientX - startPan.x;
            panOffset.y = e.clientY - startPan.y;
            drawCanvas();
            return;
        }

        const pos = screenToTile(e);

        if (isDrawing && drawStartTile) {
            const minX = Math.min(drawStartTile.x, pos.x);
            const minY = Math.min(drawStartTile.y, pos.y);
            const maxX = Math.max(drawStartTile.x, pos.x);
            const maxY = Math.max(drawStartTile.y, pos.y);
            currentDragRect = {
                x: Math.max(0, minX),
                y: Math.max(0, minY),
                width: Math.max(1, maxX - minX + 1),
                height: Math.max(1, maxY - minY + 1)
            };
            drawCanvas();
            return;
        }

        if (isDraggingRoom && selectedRoomIndex !== null && dragRoomInitialBounds) {
            const dx = pos.x - dragRoomStart.x;
            const dy = pos.y - dragRoomStart.y;
            const targetX = Math.max(0, Math.min(MAP_WIDTH_TILES - dragRoomInitialBounds.width, dragRoomInitialBounds.x + dx));
            const targetY = Math.max(0, Math.min(MAP_HEIGHT_TILES - dragRoomInitialBounds.height, dragRoomInitialBounds.y + dy));

            defaultRooms[selectedRoomIndex].bounds.x = targetX;
            defaultRooms[selectedRoomIndex].bounds.y = targetY;
            updateInspectorInputs();
            drawCanvas();
        }
    });

    window.addEventListener('mouseup', () => {
        if (isPanning) {
            isPanning = false;
        }

        if (isDrawing && currentDragRect) {
            if (currentDragRect.width >= 2 && currentDragRect.height >= 2) {
                // Create New Room
                const newRoomNum = defaultRooms.length + 1;
                const newRoom = {
                    name: `غرفة جديدة - Room ${newRoomNum}`,
                    type: 'meeting',
                    access_mode: 'public',
                    capacity: 8,
                    color: '#3F7D4F',
                    bounds: { ...currentDragRect },
                    metadata: { audio_isolation: true }
                };
                defaultRooms.push(newRoom);
                selectRoom(defaultRooms.length - 1);
                renderRosterTable();
                setDrawTool('select');
                document.getElementById('insp-name').focus();
                document.getElementById('insp-name').select();
            }
            isDrawing = false;
            drawStartTile = null;
            currentDragRect = null;
            drawCanvas();
        }

        if (isDraggingRoom) {
            isDraggingRoom = false;
            dragRoomInitialBounds = null;
            renderRosterTable();
        }
    });

    // ── Selection & Inspector Management ──
    function selectRoom(index) {
        selectedRoomIndex = index;
        const emptyBox = document.getElementById('inspector-empty');
        const formBox = document.getElementById('inspector-form');
        const headerTitle = document.getElementById('inspector-header-title');

        if (index === null || !defaultRooms[index]) {
            emptyBox.style.display = 'block';
            formBox.style.display = 'none';
            headerTitle.textContent = "{{ __('Room Inspector & Rename') }}";
        } else {
            emptyBox.style.display = 'none';
            formBox.style.display = 'flex';
            const r = defaultRooms[index];
            headerTitle.textContent = `🚪 ${r.name || 'Room'}`;
            updateInspectorInputs();
        }
        renderRosterTable();
        drawCanvas();
    }

    function updateInspectorInputs() {
        if (selectedRoomIndex === null || !defaultRooms[selectedRoomIndex]) return;
        const r = defaultRooms[selectedRoomIndex];
        const b = r.bounds || { x: 0, y: 0, width: 8, height: 6 };

        document.getElementById('insp-name').value = r.name || '';
        document.getElementById('insp-type').value = r.type || 'meeting';
        document.getElementById('insp-access').value = r.access_mode || 'public';
        document.getElementById('insp-capacity').value = r.capacity || 8;
        document.getElementById('insp-color').value = r.color || '#3F7D4F';
        document.getElementById('insp-x').value = b.x;
        document.getElementById('insp-y').value = b.y;
        document.getElementById('insp-w').value = b.width;
        document.getElementById('insp-h').value = b.height;
        document.getElementById('insp-isolation').checked = (r.metadata && r.metadata.audio_isolation !== undefined) ? !!r.metadata.audio_isolation : true;
    }

    function updateSelectedRoomProp(prop, value) {
        if (selectedRoomIndex === null || !defaultRooms[selectedRoomIndex]) return;
        defaultRooms[selectedRoomIndex][prop] = value;
        if (prop === 'name') {
            document.getElementById('inspector-header-title').textContent = `🚪 ${value || 'Room'}`;
        }
        renderRosterTable();
        drawCanvas();
    }

    function updateSelectedRoomBound(axis, val) {
        if (selectedRoomIndex === null || !defaultRooms[selectedRoomIndex]) return;
        defaultRooms[selectedRoomIndex].bounds = defaultRooms[selectedRoomIndex].bounds || {};
        defaultRooms[selectedRoomIndex].bounds[axis] = val;
        renderRosterTable();
        drawCanvas();
    }

    function updateSelectedRoomIsolation(checked) {
        if (selectedRoomIndex === null || !defaultRooms[selectedRoomIndex]) return;
        defaultRooms[selectedRoomIndex].metadata = defaultRooms[selectedRoomIndex].metadata || {};
        defaultRooms[selectedRoomIndex].metadata.audio_isolation = checked;
        drawCanvas();
    }

    function deleteCurrentSelectedRoom() {
        if (selectedRoomIndex === null) return;
        if (!confirm('{{ __("Are you sure you want to delete this room from the default template?") }}')) return;
        defaultRooms.splice(selectedRoomIndex, 1);
        selectRoom(null);
        renderRosterTable();
        drawCanvas();
    }

    // ── Table Roster Rendering ──
    function renderRosterTable() {
        const tbody = document.getElementById('rooms-roster-tbody');
        document.getElementById('canvas-rooms-count').textContent = `${defaultRooms.length} {{ __('Rooms Configured') }}`;
        if (!tbody) return;

        tbody.innerHTML = '';
        if (defaultRooms.length === 0) {
            tbody.innerHTML = `<tr><td colspan="8" style="text-align: center; color: var(--text-muted); padding: 30px;">{{ __('No rooms drawn yet. Use "Draw Room" tool above to draw rooms.') }}</td></tr>`;
            return;
        }

        defaultRooms.forEach((r, idx) => {
            const b = r.bounds || { x: 0, y: 0, width: 1, height: 1 };
            const isSel = selectedRoomIndex === idx;
            const isIsolated = (r.metadata && r.metadata.audio_isolation !== undefined) ? r.metadata.audio_isolation : true;

            const tr = document.createElement('tr');
            tr.style.cursor = 'pointer';
            if (isSel) tr.style.background = 'rgba(79, 155, 95, 0.12)';
            tr.onclick = () => selectRoom(idx);

            tr.innerHTML = `
                <td><strong style="color: var(--text-muted);">${idx + 1}</strong></td>
                <td>
                    <div style="display: flex; align-items: center; gap: 8px;">
                        <div style="width: 12px; height: 12px; border-radius: 4px; background: ${r.color || '#3F7D4F'};"></div>
                        <strong style="color: var(--text-primary); font-size: 13px;">${r.name || 'Room'}</strong>
                        ${isSel ? '<span class="badge-status badge-active" style="font-size: 9px; padding: 2px 6px;">● {{ __("Selected") }}</span>' : ''}
                    </div>
                </td>
                <td><span class="badge-status badge-plan" style="font-size: 11px; text-transform: uppercase;">${r.type || 'meeting'}</span></td>
                <td><span class="badge-status badge-active" style="font-size: 11px;">${r.access_mode || 'public'}</span></td>
                <td><strong>${r.capacity || 8}</strong> <span style="font-size: 11px; color: var(--text-muted);">{{ __('seats') }}</span></td>
                <td><code style="background: var(--bg-surface-subtle); padding: 4px 8px; border-radius: 6px; font-family: monospace; font-size: 11px; color: var(--brand-forest);">X:${b.x}, Y:${b.y} (${b.width}x${b.height})</code></td>
                <td>${isIsolated ? '<span class="badge-status badge-active" style="font-size: 11px;">🎙️ {{ __("Acoustic") }}</span>' : '<span class="badge-status" style="font-size: 11px; background: rgba(59, 130, 246, 0.15); color: #60A5FA;">🔊 {{ __("Open") }}</span>'}</td>
                <td>
                    <button type="button" onclick="event.stopPropagation(); selectRoom(${idx});" class="tactile-btn btn-secondary" style="padding: 4px 8px; font-size: 11px;">
                        ✏️ {{ __('Edit & Rename') }}
                    </button>
                </td>
            `;
            tbody.appendChild(tr);
        });
    }

    // ── Live Canvas Draw Loop ──
    function drawCanvas() {
        ctx.setTransform(1, 0, 0, 1, 0, 0);
        ctx.clearRect(0, 0, canvas.width, canvas.height);

        ctx.save();
        ctx.translate(panOffset.x, panOffset.y);
        ctx.scale(zoomLevel, zoomLevel);

        // 1. Draw Background Blueprint
        if (bgLoaded && BLUEPRINT_IMG.complete && BLUEPRINT_IMG.naturalWidth > 0) {
            ctx.fillStyle = '#ECE8DB';
            ctx.fillRect(0, 0, MAP_WIDTH_PX, MAP_HEIGHT_PX);
            ctx.drawImage(BLUEPRINT_IMG, 0, 0, MAP_WIDTH_PX, MAP_HEIGHT_PX);
        } else {
            ctx.fillStyle = '#0F1E16';
            ctx.fillRect(0, 0, MAP_WIDTH_PX, MAP_HEIGHT_PX);
            ctx.strokeStyle = '#2D5C3E';
            ctx.lineWidth = 2;
            ctx.strokeRect(0, 0, MAP_WIDTH_PX, MAP_HEIGHT_PX);
        }

        // 2. Subtle Grid overlay
        ctx.strokeStyle = 'rgba(79, 155, 95, 0.12)';
        ctx.lineWidth = 1;
        for (let x = 0; x <= MAP_WIDTH_PX; x += TILE_SIZE) {
            ctx.beginPath(); ctx.moveTo(x, 0); ctx.lineTo(x, MAP_HEIGHT_PX); ctx.stroke();
        }
        for (let y = 0; y <= MAP_HEIGHT_PX; y += TILE_SIZE) {
            ctx.beginPath(); ctx.moveTo(0, y); ctx.lineTo(MAP_WIDTH_PX, y); ctx.stroke();
        }

        // 3. Draw All Configured Rooms
        defaultRooms.forEach((r, idx) => {
            const b = r.bounds;
            if (!b) return;
            const isSelected = selectedRoomIndex === idx;

            const rx = b.x * TILE_SIZE;
            const ry = b.y * TILE_SIZE;
            const rw = b.width * TILE_SIZE;
            const rh = b.height * TILE_SIZE;

            const isIsolated = (r.metadata && r.metadata.audio_isolation !== undefined) ? r.metadata.audio_isolation : true;

            if (isSelected) {
                // Selected Room Acoustic Aura & Highlight
                ctx.fillStyle = isIsolated ? 'rgba(79, 155, 95, 0.22)' : 'rgba(59, 130, 246, 0.18)';
                if (ctx.roundRect) ctx.roundRect(rx, ry, rw, rh, 8);
                else ctx.rect(rx, ry, rw, rh);
                ctx.fill();

                ctx.strokeStyle = isIsolated ? '#4F9B5F' : '#3B82F6';
                ctx.lineWidth = 2.5;
                ctx.setLineDash([8, 6]);
                if (ctx.roundRect) ctx.roundRect(rx, ry, rw, rh, 8);
                else ctx.rect(rx, ry, rw, rh);
                ctx.stroke();
                ctx.setLineDash([]);

                // 4 Corner Grab Nodes
                const corners = [
                    { x: rx, y: ry },
                    { x: rx + rw, y: ry },
                    { x: rx + rw, y: ry + rh },
                    { x: rx, y: ry + rh }
                ];
                corners.forEach(c => {
                    ctx.fillStyle = '#4F9B5F';
                    ctx.beginPath(); ctx.arc(c.x, c.y, 5, 0, Math.PI * 2); ctx.fill();
                    ctx.strokeStyle = '#FFFFFF'; ctx.lineWidth = 2; ctx.stroke();
                });
            } else {
                // Unselected Room Subtle Boundary
                ctx.fillStyle = 'rgba(79, 155, 95, 0.08)';
                ctx.fillRect(rx, ry, rw, rh);

                ctx.strokeStyle = 'rgba(79, 155, 95, 0.5)';
                ctx.lineWidth = 1.5;
                ctx.setLineDash([6, 4]);
                ctx.strokeRect(rx, ry, rw, rh);
                ctx.setLineDash([]);
            }

            // Room Name Tag (Floating Glass Badge)
            const label = `🚪 ${r.name || 'Room'}`;
            ctx.font = 'bold 11px Cairo, Inter, sans-serif';
            const tw = ctx.measureText(label).width;
            const badgeW = Math.min(rw - 8, tw + 20);
            const badgeH = 22;

            ctx.fillStyle = isSelected ? 'rgba(15, 23, 42, 0.95)' : 'rgba(15, 23, 42, 0.85)';
            if (ctx.roundRect) ctx.roundRect(rx + 6, ry + 6, badgeW, badgeH, 6);
            else ctx.rect(rx + 6, ry + 6, badgeW, badgeH);
            ctx.fill();

            ctx.strokeStyle = isSelected ? '#4F9B5F' : 'rgba(255, 255, 255, 0.2)';
            ctx.lineWidth = 1;
            if (ctx.roundRect) ctx.roundRect(rx + 6, ry + 6, badgeW, badgeH, 6);
            else ctx.rect(rx + 6, ry + 6, badgeW, badgeH);
            ctx.stroke();

            ctx.fillStyle = isSelected ? '#7EE092' : '#F8FAFC';
            ctx.textAlign = 'left';
            ctx.textBaseline = 'middle';
            ctx.fillText(label, rx + 12, ry + 6 + badgeH / 2, badgeW - 12);
        });

        // 4. Draw Current Drawing Box
        if (isDrawing && currentDragRect) {
            const dx = currentDragRect.x * TILE_SIZE;
            const dy = currentDragRect.y * TILE_SIZE;
            const dw = currentDragRect.width * TILE_SIZE;
            const dh = currentDragRect.height * TILE_SIZE;

            ctx.fillStyle = 'rgba(16, 185, 129, 0.25)';
            ctx.fillRect(dx, dy, dw, dh);

            ctx.strokeStyle = '#10B981';
            ctx.lineWidth = 2.5;
            ctx.setLineDash([6, 4]);
            ctx.strokeRect(dx, dy, dw, dh);
            ctx.setLineDash([]);

            ctx.fillStyle = '#10B981';
            ctx.font = 'bold 12px Cairo, Inter, sans-serif';
            ctx.fillText(`📐 ${currentDragRect.width}x${currentDragRect.height}`, dx + 8, dy + 20);
        }

        ctx.restore();
    }

    // ── Save All Rooms via AJAX ──
    async function saveAllRoomsToServer() {
        const btn = document.querySelector('button[onclick="saveAllRoomsToServer()"]');
        const origText = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '⏳ {{ __("Saving...") }}';

        try {
            const res = await fetch("{{ route('superadmin.template.rooms.bulk') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': CSRF_TOKEN,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ rooms: defaultRooms })
            });

            const data = await res.json();
            if (res.ok && data.success) {
                alert('🎉 ' + data.message);
            } else {
                alert('❌ ' + (data.message || 'Failed to save rooms.'));
            }
        } catch (err) {
            console.error(err);
            alert('❌ Network error while saving rooms.');
        } finally {
            btn.disabled = false;
            btn.innerHTML = origText;
        }
    }

    function openSyncModal() {
        document.getElementById('syncModal').style.display = 'flex';
    }
    function closeSyncModal() {
        document.getElementById('syncModal').style.display = 'none';
    }

    // Initial load
    renderRosterTable();
    resetCanvasView();
</script>
@endsection
