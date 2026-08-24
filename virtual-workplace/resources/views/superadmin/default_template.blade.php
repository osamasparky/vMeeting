@extends('superadmin.layout')

@section('title', __('Default Office Template & Rooms Designer'))
@section('page_title', __('Default Office Blueprint & Rooms'))

@section('content')
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; flex-wrap: wrap; gap: 16px;">
    <div>
        <h2 style="font-size: 20px; font-weight: 900; color: var(--text-primary); margin: 0 0 4px 0;">
            🏢 {{ __('System Default Office Template (المكتب الافتراضي النموذجي)') }}
        </h2>
        <p style="font-size: 13px; color: var(--text-muted); margin: 0;">
            {{ __('Define the master floorplan, default rooms, boundaries, and acoustic zones for all new organizations.') }}
        </p>
    </div>

    <div style="display: flex; gap: 10px; align-items: center; flex-wrap: wrap;">
        <button type="button" onclick="openSyncModal()" class="tactile-btn" style="background: rgba(37, 99, 235, 0.15); border-color: rgba(59, 130, 246, 0.35); color: #93C5FD; font-size: 13px;">
            🔄 {{ __('Sync to All Companies (:count)', ['count' => $totalCompanies]) }}
        </button>
        <button type="button" onclick="openAddRoomModal()" class="tactile-btn btn-primary" style="font-size: 13px;">
            ➕ {{ __('Add Default Room (إضافة غرفة)') }}
        </button>
    </div>
</div>

<div style="display: grid; grid-template-columns: 1fr 340px; gap: 24px; margin-bottom: 28px;">
    <!-- Left: Blueprint Preview & Interactive Canvas Info -->
    <div class="panel-card" style="padding: 24px; border-radius: 20px;">
        <div class="panel-header" style="margin-bottom: 18px;">
            <div class="panel-title">
                <span>🖼️</span>
                <span>{{ __('Master 3D Floorplan Blueprint Preview') }}</span>
            </div>
            <span class="badge-status badge-active">
                📐 {{ $template->width }}x{{ $template->height }} {{ __('Grid Tiles') }} ({{ $template->tile_size }}px)
            </span>
        </div>

        <div style="position: relative; border-radius: 16px; overflow: hidden; border: 2px solid var(--border-color); background: #0b1812; box-shadow: var(--shadow-inset-3d); min-height: 380px; display: flex; align-items: center; justify-content: center;">
            <img src="{{ $template->background_image_url ?: '/images/office_floorplan.jpg' }}" alt="Default Blueprint" style="width: 100%; height: auto; max-height: 440px; object-fit: contain; display: block;">

            <!-- Subtle Blueprint Overlay Tag -->
            <div style="position: absolute; bottom: 12px; inset-inline-start: 12px; background: rgba(15, 23, 42, 0.85); backdrop-filter: blur(12px); border: 1px solid rgba(255,255,255,0.15); padding: 8px 14px; border-radius: 12px; font-size: 12px; color: #F8FAFC; display: flex; align-items: center; gap: 8px;">
                <span>💎</span>
                <span><strong>{{ count($template->rooms_data ?: []) }} {{ __('Preconfigured Rooms & Zones') }}</strong></span>
            </div>
        </div>

        <!-- Upload Background Blueprint Form -->
        <div style="margin-top: 18px; padding-top: 18px; border-top: 1px solid var(--border-color); display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px;">
            <div>
                <strong style="font-size: 13px; color: var(--text-primary);">{{ __('Change Master Blueprint Floorplan') }}</strong>
                <p style="font-size: 11px; color: var(--text-muted); margin: 2px 0 0 0;">{{ __('Upload a high-resolution 3D isometric or top-view render (JPG, PNG, WEBP - Max 50MB)') }}</p>
            </div>
            <form method="POST" action="{{ route('superadmin.template.background') }}" enctype="multipart/form-data" style="margin: 0; display: flex; gap: 8px;">
                @csrf
                <input type="file" name="background" id="template_bg_input" accept="image/jpeg,image/png,image/webp,image/jpg" style="display:none;" onchange="this.form.submit()">
                <button type="button" onclick="document.getElementById('template_bg_input').click()" class="tactile-btn btn-secondary" style="font-size: 12px;">
                    📁 {{ __('Upload New Floorplan Image') }}
                </button>
            </form>
        </div>
    </div>

    <!-- Right: Blueprint Metadata & Dimensions -->
    <div class="panel-card" style="padding: 24px; border-radius: 20px;">
        <div class="panel-header" style="margin-bottom: 16px;">
            <div class="panel-title">
                <span>⚙️</span>
                <span>{{ __('Blueprint Settings') }}</span>
            </div>
        </div>

        <form method="POST" action="{{ route('superadmin.template.update') }}">
            @csrf
            <div style="margin-bottom: 14px;">
                <label style="display: block; font-size: 11px; font-weight: 800; color: var(--text-secondary); margin-bottom: 6px;">{{ __('Template Name') }}</label>
                <input type="text" name="name" value="{{ $template->name }}" required style="width: 100%; background: var(--bg-surface-subtle); border: 1px solid var(--border-color); border-radius: 10px; padding: 10px; color: var(--text-primary); font-size: 12px; font-weight: 700; outline: none;">
            </div>

            <div style="margin-bottom: 14px;">
                <label style="display: block; font-size: 11px; font-weight: 800; color: var(--text-secondary); margin-bottom: 6px;">{{ __('Description') }}</label>
                <textarea name="description" rows="2" style="width: 100%; background: var(--bg-surface-subtle); border: 1px solid var(--border-color); border-radius: 10px; padding: 10px; color: var(--text-primary); font-size: 12px; font-weight: 600; outline: none;">{{ $template->description }}</textarea>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-bottom: 14px;">
                <div>
                    <label style="display: block; font-size: 11px; font-weight: 800; color: var(--text-secondary); margin-bottom: 6px;">{{ __('Grid Width (Tiles)') }}</label>
                    <input type="number" name="width" value="{{ $template->width }}" min="10" max="100" required style="width: 100%; background: var(--bg-surface-subtle); border: 1px solid var(--border-color); border-radius: 10px; padding: 10px; color: var(--text-primary); font-size: 12px; font-weight: 700; outline: none;">
                </div>
                <div>
                    <label style="display: block; font-size: 11px; font-weight: 800; color: var(--text-secondary); margin-bottom: 6px;">{{ __('Grid Height (Tiles)') }}</label>
                    <input type="number" name="height" value="{{ $template->height }}" min="10" max="100" required style="width: 100%; background: var(--bg-surface-subtle); border: 1px solid var(--border-color); border-radius: 10px; padding: 10px; color: var(--text-primary); font-size: 12px; font-weight: 700; outline: none;">
                </div>
            </div>

            <div style="margin-bottom: 20px;">
                <label style="display: block; font-size: 11px; font-weight: 800; color: var(--text-secondary); margin-bottom: 6px;">{{ __('Tile Size (Pixels)') }}</label>
                <select name="tile_size" style="width: 100%; background: var(--bg-surface-subtle); border: 1px solid var(--border-color); border-radius: 10px; padding: 10px; color: var(--text-primary); font-size: 12px; font-weight: 700; outline: none;">
                    <option value="16" {{ $template->tile_size == 16 ? 'selected' : '' }}>16px (Dense Grid)</option>
                    <option value="32" {{ $template->tile_size == 32 ? 'selected' : '' }}>32px (Standard Spatial)</option>
                    <option value="48" {{ $template->tile_size == 48 ? 'selected' : '' }}>48px (Large Grid)</option>
                </select>
            </div>

            <button type="submit" class="tactile-btn btn-primary" style="width: 100%; justify-content: center; padding: 10px; font-size: 12px;">
                💾 {{ __('Save Template Settings') }}
            </button>
        </form>
    </div>
</div>

<!-- ── Default Rooms Roster Table ── -->
<div class="panel-card" style="border-radius: 20px; padding: 24px;">
    <div class="panel-header" style="margin-bottom: 16px;">
        <div class="panel-title">
            <span>🚪</span>
            <span>{{ __('Preconfigured Default Rooms & Spatial Zones') }}</span>
        </div>
        <button type="button" onclick="openAddRoomModal()" class="tactile-btn btn-secondary" style="font-size: 12px; padding: 6px 12px;">
            ➕ {{ __('Add Room') }}
        </button>
    </div>

    <div class="data-table-container">
        <table class="data-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>{{ __('Room Name') }}</th>
                    <th>{{ __('Type') }}</th>
                    <th>{{ __('Access Mode') }}</th>
                    <th>{{ __('Capacity') }}</th>
                    <th>{{ __('Grid Bounds (X, Y, W, H)') }}</th>
                    <th>{{ __('Audio Isolation') }}</th>
                    <th>{{ __('Actions') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($template->rooms_data ?: [] as $idx => $room)
                @php
                    $b = $room['bounds'] ?? ['x' => 0, 'y' => 0, 'width' => 1, 'height' => 1];
                    $isIsolated = ($room['metadata']['audio_isolation'] ?? true) !== false;
                @endphp
                <tr>
                    <td><strong style="color: var(--text-muted);">{{ $idx + 1 }}</strong></td>
                    <td>
                        <div style="display: flex; align-items: center; gap: 8px;">
                            <div style="width: 12px; height: 12px; border-radius: 4px; background: {{ $room['color'] ?? '#3F7D4F' }};"></div>
                            <strong style="color: var(--text-primary); font-size: 13px;">{{ $room['name'] }}</strong>
                        </div>
                    </td>
                    <td>
                        <span class="badge-status badge-plan" style="font-size: 11px; text-transform: uppercase;">
                            {{ $room['type'] ?? 'meeting' }}
                        </span>
                    </td>
                    <td>
                        <span class="badge-status badge-active" style="font-size: 11px;">
                            {{ ucfirst($room['access_mode'] ?? 'public') }}
                        </span>
                    </td>
                    <td>
                        <strong>{{ $room['capacity'] ?? 10 }}</strong> <span style="font-size: 11px; color: var(--text-muted);">{{ __('seats') }}</span>
                    </td>
                    <td>
                        <code style="background: var(--bg-surface-subtle); padding: 4px 8px; border-radius: 6px; font-family: monospace; font-size: 11px; color: var(--brand-forest);">
                            X: {{ $b['x'] }}, Y: {{ $b['y'] }} ({{ $b['width'] }}x{{ $b['height'] }})
                        </code>
                    </td>
                    <td>
                        @if($isIsolated)
                            <span class="badge-status badge-active" style="font-size: 11px;">🎙️ {{ __('Acoustic Wall') }}</span>
                        @else
                            <span class="badge-status" style="font-size: 11px; background: rgba(59, 130, 246, 0.15); color: #60A5FA;">🔊 {{ __('Open Zone') }}</span>
                        @endif
                    </td>
                    <td>
                        <div style="display: flex; gap: 6px;">
                            <button type="button" onclick="openEditRoomModal({{ $idx }}, {{ json_encode($room) }})" class="tactile-btn btn-secondary" style="padding: 4px 8px; font-size: 11px;">
                                ✏️ {{ __('Edit') }}
                            </button>
                            <form method="POST" action="{{ route('superadmin.template.room.delete', $idx) }}" onsubmit="return confirm('{{ __('Remove this room from default template?') }}');" style="margin: 0;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="tactile-btn" style="padding: 4px 8px; font-size: 11px; color: #D96B5F; border-color: rgba(217,107,95,0.3);">
                                    🗑️
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" style="text-align: center; color: var(--text-muted); padding: 30px;">
                        {{ __('No default rooms configured yet. Click "Add Default Room" to create one.') }}
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- ── Modal: Add / Edit Default Room ── -->
<div id="roomModal" class="modal-overlay">
    <div class="modal-card" style="border-radius: 24px; padding: 26px; max-width: 520px;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <h3 id="roomModalTitle" style="font-size: 17px; font-weight: 900; color: var(--text-primary);">🚪 {{ __('Add Default Room') }}</h3>
            <button onclick="closeRoomModal()" style="background: var(--bg-surface-subtle); border: 1px solid var(--border-color); width: 32px; height: 32px; border-radius: 50%; display: flex; align-items: center; justify-content: center; cursor: pointer; color: var(--text-primary); font-weight: 800;">✕</button>
        </div>

        <form method="POST" action="{{ route('superadmin.template.room.save') }}">
            @csrf
            <input type="hidden" name="room_index" id="modal_room_index" value="">

            <div style="margin-bottom: 14px;">
                <label style="display: block; font-size: 12px; font-weight: 800; color: var(--text-secondary); margin-bottom: 6px;">{{ __('Room Name') }}</label>
                <input type="text" name="name" id="modal_room_name" required placeholder="e.g. Conference Room (10 Seats)" style="width: 100%; background: var(--bg-surface-subtle); border: 1px solid var(--border-color); border-radius: 10px; padding: 10px; color: var(--text-primary); font-size: 13px; font-weight: 700; outline: none;">
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 14px;">
                <div>
                    <label style="display: block; font-size: 12px; font-weight: 800; color: var(--text-secondary); margin-bottom: 6px;">{{ __('Room Type') }}</label>
                    <select name="type" id="modal_room_type" style="width: 100%; background: var(--bg-surface-subtle); border: 1px solid var(--border-color); border-radius: 10px; padding: 10px; color: var(--text-primary); font-size: 12px; font-weight: 700; outline: none;">
                        <option value="meeting">Meeting / Conference</option>
                        <option value="private">Private Office / Focus</option>
                        <option value="lounge">Lounge / Collaborative</option>
                        <option value="breakout">Breakout Room</option>
                        <option value="reception">Reception / Lobby</option>
                    </select>
                </div>
                <div>
                    <label style="display: block; font-size: 12px; font-weight: 800; color: var(--text-secondary); margin-bottom: 6px;">{{ __('Access Mode') }}</label>
                    <select name="access_mode" id="modal_room_access" style="width: 100%; background: var(--bg-surface-subtle); border: 1px solid var(--border-color); border-radius: 10px; padding: 10px; color: var(--text-primary); font-size: 12px; font-weight: 700; outline: none;">
                        <option value="public">🟢 Public (Open Entry)</option>
                        <option value="knock">✊ Knock to Enter</option>
                        <option value="locked">🔒 Locked by Default</option>
                    </select>
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 14px;">
                <div>
                    <label style="display: block; font-size: 12px; font-weight: 800; color: var(--text-secondary); margin-bottom: 6px;">{{ __('Capacity (Seats)') }}</label>
                    <input type="number" name="capacity" id="modal_room_capacity" value="8" min="1" max="100" required style="width: 100%; background: var(--bg-surface-subtle); border: 1px solid var(--border-color); border-radius: 10px; padding: 10px; color: var(--text-primary); font-size: 12px; font-weight: 700; outline: none;">
                </div>
                <div>
                    <label style="display: block; font-size: 12px; font-weight: 800; color: var(--text-secondary); margin-bottom: 6px;">{{ __('Theme Color') }}</label>
                    <input type="color" name="color" id="modal_room_color" value="#3F7D4F" style="width: 100%; height: 38px; background: var(--bg-surface-subtle); border: 1px solid var(--border-color); border-radius: 10px; padding: 4px; cursor: pointer;">
                </div>
            </div>

            <div style="background: var(--bg-surface-subtle); border: 1px solid var(--border-color); border-radius: 12px; padding: 12px; margin-bottom: 16px;">
                <label style="display: block; font-size: 11px; font-weight: 800; color: var(--brand-forest); margin-bottom: 8px;">📐 {{ __('Grid Coordinates & Tile Bounds') }}</label>
                <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 8px;">
                    <div>
                        <label style="font-size: 10px; color: var(--text-muted); font-weight: 700;">X (Tile)</label>
                        <input type="number" name="x" id="modal_room_x" value="1" min="0" required style="width: 100%; background: var(--bg-card); border: 1px solid var(--border-color); border-radius: 8px; padding: 8px; color: var(--text-primary); font-size: 12px; font-weight: 700; outline: none;">
                    </div>
                    <div>
                        <label style="font-size: 10px; color: var(--text-muted); font-weight: 700;">Y (Tile)</label>
                        <input type="number" name="y" id="modal_room_y" value="1" min="0" required style="width: 100%; background: var(--bg-card); border: 1px solid var(--border-color); border-radius: 8px; padding: 8px; color: var(--text-primary); font-size: 12px; font-weight: 700; outline: none;">
                    </div>
                    <div>
                        <label style="font-size: 10px; color: var(--text-muted); font-weight: 700;">Width</label>
                        <input type="number" name="width" id="modal_room_w" value="10" min="1" required style="width: 100%; background: var(--bg-card); border: 1px solid var(--border-color); border-radius: 8px; padding: 8px; color: var(--text-primary); font-size: 12px; font-weight: 700; outline: none;">
                    </div>
                    <div>
                        <label style="font-size: 10px; color: var(--text-muted); font-weight: 700;">Height</label>
                        <input type="number" name="height" id="modal_room_h" value="8" min="1" required style="width: 100%; background: var(--bg-card); border: 1px solid var(--border-color); border-radius: 8px; padding: 8px; color: var(--text-primary); font-size: 12px; font-weight: 700; outline: none;">
                    </div>
                </div>
            </div>

            <div style="margin-bottom: 20px;">
                <label style="display: flex; align-items: center; gap: 8px; cursor: pointer; font-size: 12px; font-weight: 700; color: var(--text-primary);">
                    <input type="checkbox" name="audio_isolation" id="modal_room_isolation" value="1" checked style="width: 16px; height: 16px;">
                    <span>🎙️ {{ __('Enable Acoustic Sound Isolation Boundary (عزل الصوت)') }}</span>
                </label>
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 10px;">
                <button type="button" onclick="closeRoomModal()" class="tactile-btn btn-secondary">{{ __('Cancel') }}</button>
                <button type="submit" class="tactile-btn btn-primary">💾 {{ __('Save Room') }}</button>
            </div>
        </form>
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
            {{ __('This will update the default floor, clean untextured clutter objects, and synchronize the master blueprint floorplan across all :count registered organizations.', ['count' => $totalCompanies]) }}
        </p>

        <form method="POST" action="{{ route('superadmin.template.sync') }}">
            @csrf
            <div style="background: rgba(214, 162, 58, 0.1); border: 1px solid rgba(214, 162, 58, 0.3); border-radius: 12px; padding: 14px; margin-bottom: 20px;">
                <label style="display: flex; align-items: flex-start; gap: 10px; cursor: pointer; font-size: 12px; font-weight: 700; color: var(--text-primary);">
                    <input type="checkbox" name="overwrite_rooms" value="1" style="margin-top: 2px;">
                    <span>⚠️ {{ __('Also overwrite and reset room boundaries to the template configuration for all companies (إعادة تعيين الغرف بالكامل)') }}</span>
                </label>
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 10px;">
                <button type="button" onclick="closeSyncModal()" class="tactile-btn btn-secondary">{{ __('Cancel') }}</button>
                <button type="submit" class="tactile-btn btn-primary">🚀 {{ __('Execute Sync Now') }}</button>
            </div>
        </form>
    </div>
</div>

<script>
function openAddRoomModal() {
    document.getElementById('roomModalTitle').textContent = "🚪 {{ __('Add Default Room') }}";
    document.getElementById('modal_room_index').value = "";
    document.getElementById('modal_room_name').value = "";
    document.getElementById('modal_room_type').value = "meeting";
    document.getElementById('modal_room_access').value = "public";
    document.getElementById('modal_room_capacity').value = "8";
    document.getElementById('modal_room_color').value = "#3F7D4F";
    document.getElementById('modal_room_x').value = "1";
    document.getElementById('modal_room_y').value = "1";
    document.getElementById('modal_room_w').value = "10";
    document.getElementById('modal_room_h').value = "8";
    document.getElementById('modal_room_isolation').checked = true;
    document.getElementById('roomModal').style.display = 'flex';
}

function openEditRoomModal(idx, room) {
    document.getElementById('roomModalTitle').textContent = "✏️ {{ __('Edit Default Room') }}";
    document.getElementById('modal_room_index').value = idx;
    document.getElementById('modal_room_name').value = room.name || '';
    document.getElementById('modal_room_type').value = room.type || 'meeting';
    document.getElementById('modal_room_access').value = room.access_mode || 'public';
    document.getElementById('modal_room_capacity').value = room.capacity || 8;
    document.getElementById('modal_room_color').value = room.color || '#3F7D4F';

    const b = room.bounds || { x: 1, y: 1, width: 10, height: 8 };
    document.getElementById('modal_room_x').value = b.x || 0;
    document.getElementById('modal_room_y').value = b.y || 0;
    document.getElementById('modal_room_w').value = b.width || 10;
    document.getElementById('modal_room_h').value = b.height || 8;

    const iso = (room.metadata && room.metadata.audio_isolation !== undefined) ? room.metadata.audio_isolation : true;
    document.getElementById('modal_room_isolation').checked = !!iso;

    document.getElementById('roomModal').style.display = 'flex';
}

function closeRoomModal() {
    document.getElementById('roomModal').style.display = 'none';
}

function openSyncModal() {
    document.getElementById('syncModal').style.display = 'flex';
}

function closeSyncModal() {
    document.getElementById('syncModal').style.display = 'none';
}
</script>
@endsection
