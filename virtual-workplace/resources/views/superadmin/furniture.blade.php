@extends('superadmin.layout')

@section('title', __('Furniture & Office Assets'))
@section('page_title', __('Furniture & Assets Catalog'))

@section('content')
    <!-- KPI Summary Strip -->
    <div class="kpi-grid">
        <div class="kpi-card">
            <div class="kpi-icon" style="background: rgba(0, 180, 179, 0.12); color: var(--brand-teal);">🛋️</div>
            <div class="kpi-info">
                <h3>{{ __('Total Assets') }}</h3>
                <div class="kpi-value">{{ $stats['total_items'] }}</div>
            </div>
        </div>

        <div class="kpi-card">
            <div class="kpi-icon" style="background: rgba(0, 104, 71, 0.12); color: var(--brand-green);">📂</div>
            <div class="kpi-info">
                <h3>{{ __('Categories') }}</h3>
                <div class="kpi-value">{{ $stats['total_categories'] }}</div>
            </div>
        </div>

        <div class="kpi-card">
            <div class="kpi-icon" style="background: rgba(245, 123, 54, 0.12); color: var(--brand-orange);">🖼️</div>
            <div class="kpi-info">
                <h3>{{ __('Custom Uploads') }}</h3>
                <div class="kpi-value">{{ $stats['custom_uploads'] }}</div>
            </div>
        </div>
    </div>

    <!-- Top Action Bar -->
    <div style="display: flex; flex-wrap: wrap; justify-content: space-between; align-items: center; gap: 16px; margin-bottom: 24px;">
        <!-- Category Filter Pills -->
        <div style="display: flex; flex-wrap: wrap; gap: 8px; align-items: center;">
            <a href="{{ route('superadmin.furniture') }}" class="btn-action {{ !$selectedCategoryId ? 'btn-primary' : 'btn-outline' }}" style="padding: 8px 14px; font-size: 13px; text-decoration: none;">
                🌐 {{ __('All Categories') }}
            </a>
            @foreach($categories as $cat)
                <a href="{{ route('superadmin.furniture', ['category_id' => $cat->id]) }}" class="btn-action {{ $selectedCategoryId == $cat->id ? 'btn-primary' : 'btn-outline' }}" style="padding: 8px 14px; font-size: 13px; text-decoration: none;">
                    {{ $cat->icon }} {{ $cat->name }} ({{ $cat->items_count }})
                </a>
            @endforeach
        </div>

        <!-- Action Buttons -->
        <div style="display: flex; gap: 10px;">
            <button onclick="openCategoryModal()" class="btn-action btn-outline" style="padding: 10px 16px; font-size: 13px;">
                <span>+</span> {{ __('New Category') }}
            </button>
            <button onclick="openItemModal()" class="btn-action btn-primary" style="padding: 10px 18px; font-size: 13px;">
                <span>+</span> {{ __('Upload Furniture Item') }}
            </button>
        </div>
    </div>

    <!-- Furniture Items Grid -->
    <div class="panel-card" style="margin-bottom: 30px;">
        <div class="panel-header" style="display: flex; justify-content: space-between; align-items: center;">
            <div>
                <h2 class="panel-title">{{ __('Office Furniture & Room Objects') }}</h2>
                <p class="panel-subtitle">{{ __('Manage objects rendered inside the Floor Map Editor and Virtual Office.') }}</p>
            </div>
            <!-- Search Input -->
            <form method="GET" action="{{ route('superadmin.furniture') }}" style="display: flex; gap: 8px;">
                @if($selectedCategoryId)
                    <input type="hidden" name="category_id" value="{{ $selectedCategoryId }}">
                @endif
                <input type="text" name="search" value="{{ request('search') }}" placeholder="{{ __('Search furniture name...') }}" class="form-input" style="width: 240px; padding: 6px 12px; font-size: 12px;">
                <button type="submit" class="btn-action btn-outline" style="padding: 6px 12px; font-size: 12px;">🔍</button>
            </form>
        </div>

        <div style="padding: 24px;">
            <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(220px, 1fr)); gap: 18px;">
                @forelse($items as $item)
                    <div style="background: var(--bg-card); border: 1px solid var(--border-color); border-radius: 14px; padding: 14px; display: flex; flex-direction: column; justify-content: space-between; box-shadow: var(--shadow-card); transition: all 0.2s; position: relative;">
                        <div>
                            <!-- Image / Thumbnail Preview -->
                            <div style="height: 100px; background: var(--bg-input); border-radius: 10px; border: 1px solid var(--border-color); display: flex; align-items: center; justify-content: center; margin-bottom: 12px; overflow: hidden; position: relative;">
                                @if($item->image_url)
                                    <img src="{{ $item->image_url }}" alt="{{ $item->name }}" style="max-height: 85px; max-width: 85%; object-fit: contain;">
                                @else
                                    <span style="font-size: 42px;">{{ $item->icon }}</span>
                                @endif

                                <span style="position: absolute; top: 6px; inset-inline-end: 6px; background: rgba(1, 44, 65, 0.75); color: white; font-size: 10px; font-weight: 800; padding: 2px 6px; border-radius: 4px;">
                                    {{ $item->width }}x{{ $item->height }} Tiles
                                </span>
                            </div>

                            <!-- Name & Category -->
                            <h4 style="font-size: 13px; font-weight: 800; color: var(--text-primary); margin-bottom: 4px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                {{ $item->name }}
                            </h4>
                            <div style="display: flex; align-items: center; gap: 6px; margin-bottom: 8px;">
                                <span class="badge badge-teal" style="font-size: 10px;">{{ $item->category->icon ?? '🪑' }} {{ $item->category->name ?? 'Furniture' }}</span>
                                <span class="badge {{ $item->collision ? 'badge-amber' : 'badge-green' }}" style="font-size: 10px;">
                                    {{ $item->collision ? '🧱 ' . __('Solid') : '🚶 ' . __('Walkable') }}
                                </span>
                            </div>

                            <!-- Color Variations -->
                            @if(!empty($item->colors))
                                <div style="display: flex; gap: 4px; margin-bottom: 12px;">
                                    @foreach($item->colors as $col)
                                        <span style="width: 10px; height: 10px; border-radius: 50%; background: {{ $col }}; display: inline-block; border: 1px solid rgba(0,0,0,0.1);"></span>
                                    @endforeach
                                </div>
                            @endif
                        </div>

                        <!-- Card Action Buttons -->
                        <div style="display: flex; justify-content: flex-end; gap: 6px; padding-top: 8px; border-top: 1px solid var(--border-color);">
                            <button onclick="editItem({{ json_encode($item) }})" class="btn-action btn-outline" style="padding: 4px 8px; font-size: 11px;">
                                ✏️ {{ __('Edit') }}
                            </button>
                            <form action="{{ route('superadmin.furniture.item.delete', $item->id) }}" method="POST" onsubmit="return confirm('{{ __('Are you sure you want to delete this furniture asset?') }}');" style="display: inline;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn-action btn-outline" style="padding: 4px 8px; font-size: 11px; color: var(--brand-crimson); border-color: rgba(210,0,5,0.2);">
                                    🗑️
                                </button>
                            </form>
                        </div>
                    </div>
                @empty
                    <div style="grid-column: 1 / -1; text-align: center; color: var(--text-muted); padding: 40px;">
                        <div style="font-size: 32px; margin-bottom: 8px;">🛋️</div>
                        <h4 style="font-size: 15px; font-weight: 800; color: var(--text-primary); margin-bottom: 4px;">{{ __('No furniture items found') }}</h4>
                        <p style="font-size: 12px; color: var(--text-muted); margin-bottom: 14px;">{{ __('Upload pictures and configure furniture assets to appear in the Floor Map Editor.') }}</p>
                        <button onclick="openItemModal()" class="btn-action btn-primary" style="padding: 8px 16px; font-size: 12px;">
                            + {{ __('Upload First Item') }}
                        </button>
                    </div>
                @endforelse
            </div>

            <!-- Pagination -->
            <div style="margin-top: 20px;">
                {{ $items->links() }}
            </div>
        </div>
    </div>

    <!-- Categories Management Table -->
    <div class="panel-card">
        <div class="panel-header" style="display: flex; justify-content: space-between; align-items: center;">
            <div>
                <h2 class="panel-title">📂 {{ __('Furniture Categories') }}</h2>
                <p class="panel-subtitle">{{ __('Organize furniture accordions in the customization drawer.') }}</p>
            </div>
            <button onclick="openCategoryModal()" class="btn-action btn-primary" style="padding: 6px 14px; font-size: 12px;">
                + {{ __('New Category') }}
            </button>
        </div>
        <div style="padding: 0 24px 24px;">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>{{ __('Icon') }}</th>
                        <th>{{ __('Category Name') }}</th>
                        <th>{{ __('Slug') }}</th>
                        <th>{{ __('Assets Count') }}</th>
                        <th>{{ __('Order') }}</th>
                        <th>{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($categories as $cat)
                        <tr>
                            <td style="font-size: 20px;">{{ $cat->icon }}</td>
                            <td><strong style="color: var(--text-primary);">{{ $cat->name }}</strong></td>
                            <td style="font-family: monospace; font-size: 11px; color: var(--text-muted);">{{ $cat->slug }}</td>
                            <td><span class="badge badge-teal">{{ $cat->items_count }} {{ __('Items') }}</span></td>
                            <td>{{ $cat->order }}</td>
                            <td>
                                <button onclick="editCategory({{ json_encode($cat) }})" class="btn-action btn-outline" style="padding: 4px 8px; font-size: 11px;">✏️ {{ __('Edit') }}</button>
                                <form action="{{ route('superadmin.furniture.category.delete', $cat->id) }}" method="POST" onsubmit="return confirm('{{ __('Delete category and all its furniture items?') }}');" style="display: inline;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn-action btn-outline" style="padding: 4px 8px; font-size: 11px; color: var(--brand-crimson);">🗑️</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal 1: Upload / Edit Furniture Item (Ultra Premium UX) -->
    <div id="item-modal" class="modal-overlay" style="display: none; position: fixed; inset: 0; background: rgba(1, 44, 65, 0.7); backdrop-filter: blur(10px); z-index: 9999; align-items: center; justify-content: center; padding: 20px; overflow-y: auto;">
        <div style="background: var(--bg-surface); border: 1px solid var(--border-color); border-radius: 24px; padding: 32px; width: 100%; max-width: 640px; box-shadow: 0 25px 60px -10px rgba(0, 0, 0, 0.5); position: relative; margin: auto;">
            
            <!-- Modal Header -->
            <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 24px; border-bottom: 1px solid var(--border-color); padding-bottom: 16px;">
                <div style="display: flex; align-items: center; gap: 14px;">
                    <div style="width: 46px; height: 46px; border-radius: 14px; background: rgba(59, 130, 246, 0.15); color: var(--brand-teal); display: flex; align-items: center; justify-content: center; font-size: 24px;">
                        🛋️
                    </div>
                    <div>
                        <h3 style="font-size: 19px; font-weight: 900; color: var(--text-primary); margin-bottom: 2px;" id="item-modal-title">
                            {{ __('Upload Furniture Asset') }}
                        </h3>
                        <p style="font-size: 12px; color: var(--text-muted); font-weight: 600;">
                            {{ __('Configure asset dimensions, sprite texture, collision, and color variants.') }}
                        </p>
                    </div>
                </div>
                <button onclick="closeItemModal()" style="background: var(--bg-input); border: 1px solid var(--border-color); width: 34px; height: 34px; border-radius: 10px; font-size: 16px; color: var(--text-primary); cursor: pointer; display: flex; align-items: center; justify-content: center; transition: all 0.2s;" title="{{ __('Close') }}">
                    ✕
                </button>
            </div>

            <!-- Modal Form -->
            <form id="item-form" method="POST" action="{{ route('superadmin.furniture.item.store') }}" enctype="multipart/form-data" style="display: flex; flex-direction: column; gap: 18px;">
                @csrf
                <div id="item-method-field"></div>

                <!-- Row 1: Name & Category -->
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                    <div>
                        <label style="display: block; font-size: 12px; font-weight: 800; color: var(--text-primary); margin-bottom: 6px;">
                            {{ __('Item Name') }} <span style="color: var(--brand-crimson);">*</span>
                        </label>
                        <input type="text" name="name" id="item-name" required placeholder="e.g. Modern Executive Sofa" class="form-input" style="width: 100%; padding: 11px 14px; border-radius: 12px; font-weight: 600; font-size: 13px;">
                    </div>

                    <div>
                        <label style="display: block; font-size: 12px; font-weight: 800; color: var(--text-primary); margin-bottom: 6px;">
                            {{ __('Category') }} <span style="color: var(--brand-crimson);">*</span>
                        </label>
                        <select name="category_id" id="item-category-id" required class="form-input" style="width: 100%; padding: 11px 14px; border-radius: 12px; font-weight: 700; font-size: 13px;">
                            @foreach($categories as $c)
                                <option value="{{ $c->id }}">{{ $c->icon }} {{ $c->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <!-- Modern Interactive Image Dropzone with Live Preview -->
                <div>
                    <label style="display: block; font-size: 12px; font-weight: 800; color: var(--text-primary); margin-bottom: 6px;">
                        🖼️ {{ __('Sprite / Texture Image (PNG, WebP, SVG)') }}
                    </label>
                    <div id="dropzone-box" style="position: relative; border: 2px dashed var(--border-color); background: var(--bg-input); border-radius: 16px; padding: 20px; text-align: center; cursor: pointer; transition: all 0.2s;">
                        <input type="file" name="image" id="item-image-file" accept="image/*" onchange="previewUploadImage(this)" style="position: absolute; inset: 0; opacity: 0; cursor: pointer; width: 100%; height: 100%; z-index: 5;">
                        
                        <div id="dropzone-prompt" style="display: flex; flex-direction: column; align-items: center; gap: 8px;">
                            <div style="width: 48px; height: 48px; border-radius: 50%; background: rgba(59, 130, 246, 0.15); color: var(--brand-teal); display: flex; align-items: center; justify-content: center; font-size: 22px;">
                                ☁️
                            </div>
                            <div style="font-size: 13px; font-weight: 800; color: var(--text-primary);">
                                {{ __('Click or Drag image here to upload') }}
                            </div>
                            <div style="font-size: 11px; color: var(--text-muted); font-weight: 600;">
                                PNG, WebP, SVG or JPG (Transparent background recommended)
                            </div>
                        </div>

                        <!-- Image Preview Box -->
                        <div id="image-preview-container" style="display: none; flex-direction: column; align-items: center; gap: 8px; z-index: 10; position: relative;">
                            <div style="padding: 10px; background: var(--bg-card); border-radius: 12px; border: 1px solid var(--border-color); box-shadow: var(--shadow-card); display: inline-flex; align-items: center; justify-content: center; min-width: 120px; min-height: 90px;">
                                <img id="image-preview-img" src="#" alt="Preview" style="max-height: 90px; max-width: 180px; object-fit: contain;">
                            </div>
                            <span style="font-size: 11px; font-weight: 700; color: var(--brand-teal); background: rgba(59, 130, 246, 0.15); padding: 3px 10px; border-radius: 6px;">
                                🔄 {{ __('Click to change image') }}
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Row 2: Grid Footprint Dimensions (Interactive Steppers) -->
                <div style="background: var(--bg-input); border: 1px solid var(--border-color); border-radius: 16px; padding: 14px 18px;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
                        <span style="font-size: 12px; font-weight: 800; color: var(--text-primary);">📐 {{ __('Floor Grid Footprint') }}</span>
                        <span id="grid-dimensions-badge" style="font-size: 11px; font-weight: 800; color: var(--brand-teal); background: rgba(59, 130, 246, 0.15); padding: 3px 8px; border-radius: 6px;">
                            1 × 1 Tiles (32 × 32 px)
                        </span>
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                        <!-- Width Stepper -->
                        <div>
                            <label style="display: block; font-size: 11px; font-weight: 700; color: var(--text-muted); margin-bottom: 4px;">{{ __('Tile Width (Columns)') }}</label>
                            <div style="display: flex; align-items: center; gap: 8px;">
                                <button type="button" onclick="stepDimension('width', -1)" style="width: 36px; height: 36px; border-radius: 8px; border: 1px solid var(--border-color); background: var(--bg-card); font-size: 16px; font-weight: 800; color: var(--text-primary); cursor: pointer;">−</button>
                                <input type="number" name="width" id="item-width" value="1" min="1" max="10" required class="form-input" style="text-align: center; font-weight: 800; font-size: 14px; padding: 6px;" oninput="updateDimensionBadge()">
                                <button type="button" onclick="stepDimension('width', 1)" style="width: 36px; height: 36px; border-radius: 8px; border: 1px solid var(--border-color); background: var(--bg-card); font-size: 16px; font-weight: 800; color: var(--text-primary); cursor: pointer;">+</button>
                            </div>
                        </div>

                        <!-- Height Stepper -->
                        <div>
                            <label style="display: block; font-size: 11px; font-weight: 700; color: var(--text-muted); margin-bottom: 4px;">{{ __('Tile Height (Rows)') }}</label>
                            <div style="display: flex; align-items: center; gap: 8px;">
                                <button type="button" onclick="stepDimension('height', -1)" style="width: 36px; height: 36px; border-radius: 8px; border: 1px solid var(--border-color); background: var(--bg-card); font-size: 16px; font-weight: 800; color: var(--text-primary); cursor: pointer;">−</button>
                                <input type="number" name="height" id="item-height" value="1" min="1" max="10" required class="form-input" style="text-align: center; font-weight: 800; font-size: 14px; padding: 6px;" oninput="updateDimensionBadge()">
                                <button type="button" onclick="stepDimension('height', 1)" style="width: 36px; height: 36px; border-radius: 8px; border: 1px solid var(--border-color); background: var(--bg-card); font-size: 16px; font-weight: 800; color: var(--text-primary); cursor: pointer;">+</button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Row 3: Fallback Icon & Color Variants -->
                <div style="display: grid; grid-template-columns: 1fr 1.5fr; gap: 16px;">
                    <div>
                        <label style="display: block; font-size: 12px; font-weight: 800; color: var(--text-primary); margin-bottom: 6px;">
                            {{ __('Fallback Emoji') }}
                        </label>
                        <div style="display: flex; gap: 6px;">
                            <input type="text" name="icon" id="item-icon" value="🪑" class="form-input" style="width: 50px; text-align: center; font-size: 18px; padding: 8px;">
                            <!-- Quick Emoji Buttons -->
                            <div style="display: flex; gap: 4px; flex-wrap: wrap; align-items: center;">
                                @foreach(['🛋️', '🪑', '🖥️', '🪴', '📺', '🚰', '💡', '🏓'] as $em)
                                    <button type="button" onclick="document.getElementById('item-icon').value='{{ $em }}'" style="background: var(--bg-card); border: 1px solid var(--border-color); border-radius: 6px; padding: 4px 6px; font-size: 13px; cursor: pointer;">
                                        {{ $em }}
                                    </button>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    <div>
                        <label style="display: block; font-size: 12px; font-weight: 800; color: var(--text-primary); margin-bottom: 6px;">
                            🎨 {{ __('Color Variants (Click presets or enter Hex)') }}
                        </label>
                        <div style="display: flex; gap: 6px; align-items: center; margin-bottom: 6px;">
                            @php
                                $palette = ['#00b4b3', '#00726c', '#012c41', '#006847', '#ffd136', '#f57b36', '#d20005', '#64748b'];
                            @endphp
                            @foreach($palette as $pColor)
                                <span class="preset-color-dot" onclick="togglePresetColor('{{ $pColor }}')" style="width: 18px; height: 18px; border-radius: 50%; background: {{ $pColor }}; cursor: pointer; border: 2px solid var(--bg-card); box-shadow: 0 0 0 1px rgba(255,255,255,0.15); transition: transform 0.15s;" title="{{ $pColor }}"></span>
                            @endforeach
                        </div>
                        <input type="text" name="colors" id="item-colors" placeholder="#00b4b3, #012c41, #ffd136" class="form-input" style="width: 100%; font-size: 12px; padding: 8px 12px;">
                    </div>
                </div>

                <!-- Row 4: Physical Collision Toggle Card -->
                <div style="display: flex; align-items: center; justify-content: space-between; background: linear-gradient(135deg, rgba(59, 130, 246, 0.08), var(--bg-card)); border: 1px solid var(--border-color); border-radius: 14px; padding: 12px 18px;">
                    <div style="display: flex; align-items: center; gap: 12px;">
                        <span style="font-size: 22px;">🧱</span>
                        <div>
                            <div style="font-size: 13px; font-weight: 800; color: var(--text-primary);">{{ __('Physical Collision Boundary') }}</div>
                            <div style="font-size: 11px; color: var(--text-muted);">{{ __('Solid object that blocks avatar movement (uncheck for rugs/walkable decor)') }}</div>
                        </div>
                    </div>
                    <label style="position: relative; display: inline-block; width: 44px; height: 24px; margin-bottom: 0;">
                        <input type="checkbox" name="collision" id="item-collision" value="1" checked style="opacity: 0; width: 0; height: 0;">
                        <span id="collision-slider" onclick="toggleCollisionSwitch()" style="position: absolute; cursor: pointer; inset: 0; background-color: var(--brand-teal); transition: .3s; border-radius: 24px;">
                            <span id="collision-knob" style="position: absolute; height: 18px; width: 18px; left: 3px; bottom: 3px; background-color: white; transition: .3s; border-radius: 50%;"></span>
                        </span>
                    </label>
                </div>

                <!-- Save Action Button -->
                <button type="submit" class="btn-action btn-primary" style="margin-top: 6px; padding: 14px; font-size: 15px; font-weight: 800; justify-content: center; border-radius: 12px; box-shadow: 0 6px 18px rgba(59, 130, 246, 0.3);">
                    💾 {{ __('Save Furniture Item') }}
                </button>
            </form>
        </div>
    </div>

    <!-- Modal 2: Create / Edit Category (Ultra Premium UX) -->
    <div id="category-modal" class="modal-overlay" style="display: none; position: fixed; inset: 0; background: rgba(1, 44, 65, 0.7); backdrop-filter: blur(10px); z-index: 9999; align-items: center; justify-content: center; padding: 20px;">
        <div style="background: var(--bg-surface); border: 1px solid var(--border-color); border-radius: 24px; padding: 32px; width: 100%; max-width: 480px; box-shadow: 0 25px 60px -10px rgba(0, 0, 0, 0.5); position: relative;">
            
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 22px; border-bottom: 1px solid var(--border-color); padding-bottom: 16px;">
                <div style="display: flex; align-items: center; gap: 12px;">
                    <div style="width: 42px; height: 42px; border-radius: 12px; background: rgba(16, 185, 129, 0.15); color: #34d399; display: flex; align-items: center; justify-content: center; font-size: 22px;">
                        📂
                    </div>
                    <div>
                        <h3 style="font-size: 18px; font-weight: 900; color: var(--text-primary);" id="cat-modal-title">{{ __('New Category') }}</h3>
                        <p style="font-size: 11px; color: var(--text-muted); font-weight: 600;">{{ __('Drawer accordion section for grouping assets.') }}</p>
                    </div>
                </div>
                <button onclick="closeCategoryModal()" style="background: var(--bg-input); border: 1px solid var(--border-color); width: 32px; height: 32px; border-radius: 10px; font-size: 15px; color: var(--text-primary); cursor: pointer; display: flex; align-items: center; justify-content: center;">✕</button>
            </div>

            <form id="category-form" method="POST" action="{{ route('superadmin.furniture.category.store') }}" style="display: flex; flex-direction: column; gap: 16px;">
                @csrf
                <div id="cat-method-field"></div>

                <div>
                    <label style="display: block; font-size: 12px; font-weight: 800; color: var(--text-primary); margin-bottom: 6px;">{{ __('Category Name') }} *</label>
                    <input type="text" name="name" id="cat-name" required placeholder="e.g. Seating, Tables, Electronics, Plants" class="form-input" style="width: 100%; padding: 11px 14px; border-radius: 12px; font-weight: 600; font-size: 13px;">
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px;">
                    <div>
                        <label style="display: block; font-size: 12px; font-weight: 800; color: var(--text-primary); margin-bottom: 6px;">{{ __('Emoji Icon') }}</label>
                        <div style="display: flex; gap: 6px;">
                            <input type="text" name="icon" id="cat-icon" value="🪑" class="form-input" style="width: 50px; text-align: center; font-size: 18px; padding: 8px;">
                            <div style="display: flex; gap: 4px; flex-wrap: wrap; align-items: center;">
                                @foreach(['🪑', '🖥️', '🪴', '📺', '🗄️', '☕'] as $em)
                                    <button type="button" onclick="document.getElementById('cat-icon').value='{{ $em }}'" style="background: var(--bg-card); border: 1px solid var(--border-color); border-radius: 6px; padding: 4px 6px; font-size: 13px; cursor: pointer;">
                                        {{ $em }}
                                    </button>
                                @endforeach
                            </div>
                        </div>
                    </div>
                    <div>
                        <label style="display: block; font-size: 12px; font-weight: 800; color: var(--text-primary); margin-bottom: 6px;">{{ __('Display Order') }}</label>
                        <input type="number" name="order" id="cat-order" value="0" class="form-input" style="width: 100%; padding: 11px 14px; border-radius: 12px; font-weight: 700;">
                    </div>
                </div>

                <button type="submit" class="btn-action btn-primary" style="margin-top: 8px; padding: 14px; font-size: 14px; font-weight: 800; justify-content: center; border-radius: 12px;">
                    💾 {{ __('Save Category') }}
                </button>
            </form>
        </div>
    </div>
@endsection

@section('scripts')
    <script nonce="{{ $cspNonce ?? '' }}">
        function updateDimensionBadge() {
            const w = document.getElementById('item-width').value || 1;
            const h = document.getElementById('item-height').value || 1;
            const pxW = w * 32;
            const pxH = h * 32;
            document.getElementById('grid-dimensions-badge').textContent = `${w} × ${h} Tiles (${pxW} × ${pxH} px)`;
        }

        function stepDimension(dim, amount) {
            const input = document.getElementById(`item-${dim}`);
            let val = parseInt(input.value) || 1;
            val = Math.max(1, Math.min(10, val + amount));
            input.value = val;
            updateDimensionBadge();
        }

        function toggleCollisionSwitch() {
            const chk = document.getElementById('item-collision');
            chk.checked = !chk.checked;
            updateCollisionSwitchUI();
        }

        function updateCollisionSwitchUI() {
            const chk = document.getElementById('item-collision');
            const slider = document.getElementById('collision-slider');
            const knob = document.getElementById('collision-knob');
            if (chk.checked) {
                slider.style.backgroundColor = 'var(--brand-teal)';
                knob.style.left = '23px';
            } else {
                slider.style.backgroundColor = '#cbd5e1';
                knob.style.left = '3px';
            }
        }

        function togglePresetColor(hex) {
            const input = document.getElementById('item-colors');
            let current = input.value.split(',').map(s => s.trim()).filter(Boolean);
            if (current.includes(hex)) {
                current = current.filter(c => c !== hex);
            } else {
                current.push(hex);
            }
            input.value = current.join(', ');
        }

        function openItemModal() {
            document.getElementById('item-modal-title').textContent = '{{ __('Upload Furniture Asset') }}';
            document.getElementById('item-form').action = "{{ route('superadmin.furniture.item.store') }}";
            document.getElementById('item-method-field').innerHTML = '';
            document.getElementById('item-name').value = '';
            document.getElementById('item-width').value = 1;
            document.getElementById('item-height').value = 1;
            document.getElementById('item-icon').value = '🪑';
            document.getElementById('item-colors').value = '#00b4b3, #012c41';
            document.getElementById('item-collision').checked = true;
            updateCollisionSwitchUI();
            updateDimensionBadge();
            document.getElementById('image-preview-container').style.display = 'none';
            document.getElementById('dropzone-prompt').style.display = 'flex';
            document.getElementById('item-modal').style.display = 'flex';
        }

        function editItem(item) {
            document.getElementById('item-modal-title').textContent = '{{ __('Edit Furniture Asset') }}';
            document.getElementById('item-form').action = `/superadmin/furniture/item/${item.id}`;
            document.getElementById('item-method-field').innerHTML = '@method("PUT")';
            document.getElementById('item-name').value = item.name;
            document.getElementById('item-category-id').value = item.category_id;
            document.getElementById('item-width').value = item.width || 1;
            document.getElementById('item-height').value = item.height || 1;
            document.getElementById('item-icon').value = item.icon || '🪑';
            document.getElementById('item-colors').value = Array.isArray(item.colors) ? item.colors.join(', ') : (item.colors || '');
            document.getElementById('item-collision').checked = Boolean(item.collision);
            updateCollisionSwitchUI();
            updateDimensionBadge();

            if (item.image_url) {
                document.getElementById('image-preview-img').src = item.image_url;
                document.getElementById('image-preview-container').style.display = 'flex';
                document.getElementById('dropzone-prompt').style.display = 'none';
            } else {
                document.getElementById('image-preview-container').style.display = 'none';
                document.getElementById('dropzone-prompt').style.display = 'flex';
            }

            document.getElementById('item-modal').style.display = 'flex';
        }

        function closeItemModal() {
            document.getElementById('item-modal').style.display = 'none';
        }

        function previewUploadImage(input) {
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    document.getElementById('image-preview-img').src = e.target.result;
                    document.getElementById('image-preview-container').style.display = 'flex';
                    document.getElementById('dropzone-prompt').style.display = 'none';
                }
                reader.readAsDataURL(input.files[0]);
            }
        }

        function openCategoryModal() {
            document.getElementById('cat-modal-title').textContent = '📂 {{ __('New Category') }}';
            document.getElementById('category-form').action = "{{ route('superadmin.furniture.category.store') }}";
            document.getElementById('cat-method-field').innerHTML = '';
            document.getElementById('cat-name').value = '';
            document.getElementById('cat-icon').value = '🪑';
            document.getElementById('cat-order').value = 0;
            document.getElementById('category-modal').style.display = 'flex';
        }

        function editCategory(cat) {
            document.getElementById('cat-modal-title').textContent = '✏️ {{ __('Edit Category') }}';
            document.getElementById('category-form').action = `/superadmin/furniture/category/${cat.id}`;
            document.getElementById('cat-method-field').innerHTML = '@method("PUT")';
            document.getElementById('cat-name').value = cat.name;
            document.getElementById('cat-icon').value = cat.icon || '🪑';
            document.getElementById('cat-order').value = cat.order || 0;
            document.getElementById('category-modal').style.display = 'flex';
        }

        function closeCategoryModal() {
            document.getElementById('category-modal').style.display = 'none';
        }
    </script>
@endsection
