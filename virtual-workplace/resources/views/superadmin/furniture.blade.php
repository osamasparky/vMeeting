@extends('superadmin.layout')

@section('title', __('Furniture & Office Assets'))
@section('page_title', __('Furniture & Assets Catalog'))

@section('content')
    <!-- KPI Summary Strip -->
    <div class="kpi-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px; margin-bottom: 24px;">
        <div class="kpi-card" style="background: var(--ula-surface-card); border: 1px solid var(--ula-border-subtle); border-radius: var(--ula-radius-xl); padding: 18px 20px; box-shadow: var(--ula-shadow-sm); border-top: 4px solid var(--ula-palm-900);">
            <div class="kpi-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                <span class="kpi-title" style="font-size: 12px; font-weight: 700; color: var(--ula-text-secondary);">{{ __('Total Assets') }}</span>
                <span class="material-symbols-rounded" style="font-size: 20px; color: var(--ula-palm-900);">chair</span>
            </div>
            <div class="kpi-value" style="font-size: 26px; font-weight: 800; color: var(--ula-text-primary); font-family: 'IBM Plex Mono', monospace;">{{ $stats['total_items'] }}</div>
        </div>

        <div class="kpi-card" style="background: var(--ula-surface-card); border: 1px solid var(--ula-border-subtle); border-radius: var(--ula-radius-xl); padding: 18px 20px; box-shadow: var(--ula-shadow-sm); border-top: 4px solid var(--ula-status-success);">
            <div class="kpi-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                <span class="kpi-title" style="font-size: 12px; font-weight: 700; color: var(--ula-text-secondary);">{{ __('Categories') }}</span>
                <span class="material-symbols-rounded" style="font-size: 20px; color: var(--ula-status-success);">category</span>
            </div>
            <div class="kpi-value" style="font-size: 26px; font-weight: 800; color: var(--ula-status-success); font-family: 'IBM Plex Mono', monospace;">{{ $stats['total_categories'] }}</div>
        </div>

        <div class="kpi-card" style="background: var(--ula-surface-card); border: 1px solid var(--ula-border-subtle); border-radius: var(--ula-radius-xl); padding: 18px 20px; box-shadow: var(--ula-shadow-sm); border-top: 4px solid var(--ula-gold-500);">
            <div class="kpi-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                <span class="kpi-title" style="font-size: 12px; font-weight: 700; color: var(--ula-text-secondary);">{{ __('Custom Uploads') }}</span>
                <span class="material-symbols-rounded" style="font-size: 20px; color: var(--ula-gold-500);">image</span>
            </div>
            <div class="kpi-value" style="font-size: 26px; font-weight: 800; color: var(--ula-gold-600); font-family: 'IBM Plex Mono', monospace;">{{ $stats['custom_uploads'] }}</div>
        </div>
    </div>

    <!-- Top Action Bar -->
    <div style="display: flex; flex-wrap: wrap; justify-content: space-between; align-items: center; gap: 16px; margin-bottom: 24px;">
        <!-- Category Filter Pills -->
        <div style="display: flex; flex-wrap: wrap; gap: 8px; align-items: center;">
            <a href="{{ route('superadmin.furniture') }}" class="nav-badge-pill" style="text-decoration: none; padding: 7px 16px; border-radius: var(--ula-radius-pill); font-size: 12px; font-weight: 600; {{ !$selectedCategoryId ? 'background: var(--ula-palm-900); color: white;' : 'background: var(--ula-surface-card); color: var(--ula-text-secondary); border: 1px solid var(--ula-border-subtle);' }}">
                <span>{{ __('All Categories') }}</span>
            </a>
            @foreach($categories as $cat)
                <a href="{{ route('superadmin.furniture', ['category_id' => $cat->id]) }}" class="nav-badge-pill" style="text-decoration: none; padding: 7px 16px; border-radius: var(--ula-radius-pill); font-size: 12px; font-weight: 600; {{ $selectedCategoryId == $cat->id ? 'background: var(--ula-palm-900); color: white;' : 'background: var(--ula-surface-card); color: var(--ula-text-secondary); border: 1px solid var(--ula-border-subtle);' }}">
                    <span>{{ $cat->name }} ({{ $cat->items_count }})</span>
                </a>
            @endforeach
        </div>

        <!-- Action Buttons -->
        <div style="display: flex; gap: 10px;">
            <button onclick="openCategoryModal()" class="tactile-btn btn-secondary" style="padding: 9px 16px; font-size: 12px; border-radius: var(--ula-radius-pill); display: inline-flex; align-items: center; gap: 6px;">
                <span class="material-symbols-rounded" style="font-size: 16px;">add</span>
                <span>{{ __('New Category') }}</span>
            </button>
            <button onclick="openItemModal()" class="tactile-btn btn-primary" style="padding: 9px 18px; font-size: 12px; border-radius: var(--ula-radius-pill); display: inline-flex; align-items: center; gap: 6px;">
                <span class="material-symbols-rounded" style="font-size: 16px;">upload</span>
                <span>{{ __('Upload Furniture Item') }}</span>
            </button>
        </div>
    </div>

    <!-- Furniture Items Grid -->
    <div class="panel-card" style="background: var(--ula-surface-card); border: 1px solid var(--ula-border-subtle); border-radius: var(--ula-radius-xl); box-shadow: var(--ula-shadow-sm); margin-bottom: 30px; overflow: hidden;">
        <div class="panel-header" style="padding: 20px 24px; border-bottom: 1px solid var(--ula-border-subtle); display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 14px;">
            <div>
                <h2 class="panel-title" style="font-size: 16px; font-weight: 800; color: var(--ula-text-primary); margin: 0; display: flex; align-items: center; gap: 8px;">
                    <span class="material-symbols-rounded" style="color: var(--ula-highlight-default); font-size: 20px;">chair</span>
                    <span>{{ __('Office Furniture & Room Objects') }}</span>
                </h2>
                <p class="panel-subtitle" style="font-size: 12px; color: var(--ula-text-secondary); margin: 2px 0 0 0;">{{ __('Manage objects rendered inside the Floor Map Editor and Virtual Office.') }}</p>
            </div>
            <!-- Search Input -->
            <form method="GET" action="{{ route('superadmin.furniture') }}" style="display: flex; gap: 8px; margin: 0;">
                @if($selectedCategoryId)
                    <input type="hidden" name="category_id" value="{{ $selectedCategoryId }}">
                @endif
                <div style="position: relative; display: flex; align-items: center;">
                    <span class="material-symbols-rounded" style="position: absolute; inset-inline-start: 12px; font-size: 16px; color: var(--ula-text-muted); pointer-events: none;">search</span>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="{{ __('Search furniture name...') }}" style="background: var(--ula-surface-page-alt); border: 1px solid var(--ula-border-subtle); border-radius: var(--ula-radius-pill); padding: 8px 14px; padding-inline-start: 36px; font-size: 12px; color: var(--ula-text-primary); outline: none; width: 220px;">
                </div>
                <button type="submit" class="tactile-btn btn-secondary" style="padding: 8px 14px; font-size: 12px; border-radius: var(--ula-radius-pill); display: inline-flex; align-items: center;">
                    <span class="material-symbols-rounded" style="font-size: 15px;">search</span>
                </button>
            </form>
        </div>

        <div style="padding: 24px;">
            <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(220px, 1fr)); gap: 18px;">
                @forelse($items as $item)
                    <div style="background: var(--ula-surface-card); border: 1px solid var(--ula-border-subtle); border-radius: var(--ula-radius-lg); padding: 14px; display: flex; flex-direction: column; justify-content: space-between; box-shadow: var(--ula-shadow-sm); transition: transform 0.2s ease, box-shadow 0.2s ease; position: relative;">
                        <div>
                            <!-- Image / Thumbnail Preview -->
                            <div style="height: 100px; background: var(--ula-surface-page-alt); border-radius: 12px; border: 1px solid var(--ula-border-subtle); display: flex; align-items: center; justify-content: center; margin-bottom: 12px; overflow: hidden; position: relative;">
                                @if($item->image_url)
                                    <img src="{{ $item->image_url }}" alt="{{ $item->name }}" style="max-height: 85px; max-width: 85%; object-fit: contain;">
                                @else
                                    <span class="material-symbols-rounded" style="font-size: 38px; color: var(--ula-palm-800);">chair</span>
                                @endif

                                <span style="position: absolute; top: 6px; inset-inline-end: 6px; background: rgba(20, 43, 36, 0.85); color: white; font-size: 10px; font-weight: 700; padding: 2px 6px; border-radius: 4px; font-family: 'IBM Plex Mono', monospace;">
                                    {{ $item->width }}x{{ $item->height }}
                                </span>
                            </div>

                            <!-- Name & Category -->
                            <h4 style="font-size: 13px; font-weight: 700; color: var(--ula-text-primary); margin-bottom: 4px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                {{ $item->name }}
                            </h4>
                            <div style="display: flex; align-items: center; gap: 6px; margin-bottom: 8px;">
                                <span class="nav-badge-pill" style="font-size: 10px; padding: 2px 6px; background: rgba(20,43,36,0.06); color: var(--ula-palm-900);">{{ $item->category->name ?? 'Furniture' }}</span>
                                <span class="badge-status {{ $item->collision ? 'badge-suspended' : 'badge-active' }}" style="font-size: 10px; padding: 2px 6px; border-radius: 4px;">
                                    {{ $item->collision ? __('Solid') : __('Walkable') }}
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
                        <div style="display: flex; justify-content: flex-end; gap: 6px; padding-top: 8px; border-top: 1px solid var(--ula-border-subtle);">
                            <button onclick="editItem({{ json_encode($item) }})" class="tactile-btn btn-secondary" style="padding: 4px 10px; font-size: 11px; display: inline-flex; align-items: center; gap: 4px;">
                                <span class="material-symbols-rounded" style="font-size: 13px;">edit</span>
                                <span>{{ __('Edit') }}</span>
                            </button>
                            <form action="{{ route('superadmin.furniture.item.delete', $item->id) }}" method="POST" onsubmit="return confirm('{{ __('Are you sure you want to delete this furniture asset?') }}');" style="display: inline; margin: 0;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="tactile-btn" style="padding: 4px 8px; font-size: 11px; color: var(--ula-status-danger); display: inline-flex; align-items: center;" title="{{ __('Delete') }}">
                                    <span class="material-symbols-rounded" style="font-size: 14px;">delete</span>
                                </button>
                            </form>
                        </div>
                    </div>
                @empty
                    <div style="grid-column: 1 / -1; text-align: center; color: var(--ula-text-muted); padding: 48px;">
                        <span class="material-symbols-rounded" style="font-size: 36px; display: block; margin-bottom: 8px; opacity: 0.5;">chair</span>
                        <h4 style="font-size: 15px; font-weight: 700; color: var(--ula-text-primary); margin-bottom: 4px;">{{ __('No furniture items found') }}</h4>
                        <p style="font-size: 12px; color: var(--ula-text-muted); margin-bottom: 14px;">{{ __('Upload pictures and configure furniture assets to appear in the Floor Map Editor.') }}</p>
                        <button onclick="openItemModal()" class="tactile-btn btn-primary" style="padding: 8px 18px; font-size: 12px; border-radius: var(--ula-radius-pill); display: inline-flex; align-items: center; gap: 6px;">
                            <span class="material-symbols-rounded" style="font-size: 16px;">add</span>
                            <span>{{ __('Upload First Item') }}</span>
                        </button>
                    </div>
                @endforelse
            </div>

            <!-- Pagination -->
            @if($items->hasPages())
            <div style="margin-top: 20px;">
                {{ $items->links() }}
            </div>
            @endif
        </div>
    </div>

    <!-- Categories Management Table -->
    <div class="panel-card" style="background: var(--ula-surface-card); border: 1px solid var(--ula-border-subtle); border-radius: var(--ula-radius-xl); box-shadow: var(--ula-shadow-sm); overflow: hidden;">
        <div class="panel-header" style="padding: 20px 24px; border-bottom: 1px solid var(--ula-border-subtle); display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 14px;">
            <div>
                <h2 class="panel-title" style="font-size: 16px; font-weight: 800; color: var(--ula-text-primary); margin: 0; display: flex; align-items: center; gap: 8px;">
                    <span class="material-symbols-rounded" style="color: var(--ula-highlight-default); font-size: 20px;">category</span>
                    <span>{{ __('Furniture Categories') }}</span>
                </h2>
                <p class="panel-subtitle" style="font-size: 12px; color: var(--ula-text-secondary); margin: 2px 0 0 0;">{{ __('Organize furniture accordions in the customization drawer.') }}</p>
            </div>
            <button onclick="openCategoryModal()" class="tactile-btn btn-primary" style="padding: 8px 16px; font-size: 12px; border-radius: var(--ula-radius-pill); display: inline-flex; align-items: center; gap: 6px;">
                <span class="material-symbols-rounded" style="font-size: 16px;">add</span>
                <span>{{ __('New Category') }}</span>
            </button>
        </div>
        <div style="padding: 0 24px 24px; overflow-x: auto;">
            <table class="data-table" style="width: 100%; border-collapse: collapse; text-align: start;">
                <thead>
                    <tr style="border-bottom: 1px solid var(--ula-border-subtle);">
                        <th style="padding: 12px 16px; font-size: 11px; font-weight: 700; color: var(--ula-text-muted); text-transform: uppercase; letter-spacing: 0.5px; text-align: start;">{{ __('Category Name') }}</th>
                        <th style="padding: 12px 16px; font-size: 11px; font-weight: 700; color: var(--ula-text-muted); text-transform: uppercase; letter-spacing: 0.5px; text-align: start;">{{ __('Slug') }}</th>
                        <th style="padding: 12px 16px; font-size: 11px; font-weight: 700; color: var(--ula-text-muted); text-transform: uppercase; letter-spacing: 0.5px; text-align: start;">{{ __('Assets Count') }}</th>
                        <th style="padding: 12px 16px; font-size: 11px; font-weight: 700; color: var(--ula-text-muted); text-transform: uppercase; letter-spacing: 0.5px; text-align: start;">{{ __('Order') }}</th>
                        <th style="padding: 12px 16px; font-size: 11px; font-weight: 700; color: var(--ula-text-muted); text-transform: uppercase; letter-spacing: 0.5px; text-align: end;">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($categories as $cat)
                        <tr style="border-bottom: 1px solid var(--ula-border-subtle); transition: background 0.15s ease;">
                            <td style="padding: 12px 16px;"><strong style="color: var(--ula-text-primary); font-size: 13px;">{{ $cat->name }}</strong></td>
                            <td style="padding: 12px 16px; font-family: 'IBM Plex Mono', monospace; font-size: 11px; color: var(--ula-text-muted);">{{ $cat->slug }}</td>
                            <td style="padding: 12px 16px;"><span class="nav-badge-pill" style="font-size: 11px; padding: 2px 8px; background: rgba(20,43,36,0.06); color: var(--ula-palm-900); font-family: 'IBM Plex Mono', monospace;">{{ $cat->items_count }} {{ __('Items') }}</span></td>
                            <td style="padding: 12px 16px; font-family: 'IBM Plex Mono', monospace; font-size: 12px; color: var(--ula-text-secondary);">{{ $cat->order }}</td>
                            <td style="padding: 12px 16px; text-align: end;">
                                <div style="display: inline-flex; gap: 6px;">
                                    <button onclick="editCategory({{ json_encode($cat) }})" class="tactile-btn btn-secondary" style="padding: 4px 10px; font-size: 11px; display: inline-flex; align-items: center; gap: 4px;">
                                        <span class="material-symbols-rounded" style="font-size: 13px;">edit</span>
                                        <span>{{ __('Edit') }}</span>
                                    </button>
                                    <form action="{{ route('superadmin.furniture.category.delete', $cat->id) }}" method="POST" onsubmit="return confirm('{{ __('Delete category and all its furniture items?') }}');" style="display: inline; margin: 0;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="tactile-btn" style="padding: 4px 8px; font-size: 11px; color: var(--ula-status-danger); display: inline-flex; align-items: center;" title="{{ __('Delete') }}">
                                            <span class="material-symbols-rounded" style="font-size: 14px;">delete</span>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal 1: Upload / Edit Furniture Item -->
    <div id="item-modal" class="modal-overlay" style="display: none; position: fixed; inset: 0; background: rgba(20, 43, 36, 0.45); backdrop-filter: blur(8px); z-index: 9999; align-items: center; justify-content: center; padding: 20px; overflow-y: auto;">
        <div class="modal-card" style="border-radius: var(--ula-radius-xl); padding: 28px; width: 100%; max-width: 580px; position: relative; margin: auto;">
            
            <!-- Modal Header -->
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; border-bottom: 1px solid var(--ula-border-subtle); padding-bottom: 14px;">
                <div style="display: flex; align-items: center; gap: 10px;">
                    <span class="material-symbols-rounded" style="color: var(--ula-highlight-default); font-size: 24px;">chair</span>
                    <div>
                        <h3 style="font-size: 17px; font-weight: 800; color: var(--ula-text-primary); margin: 0;" id="item-modal-title">
                            {{ __('Upload Furniture Asset') }}
                        </h3>
                    </div>
                </div>
                <button onclick="closeItemModal()" style="background: var(--ula-surface-page-alt); border: 1px solid var(--ula-border-subtle); width: 32px; height: 32px; border-radius: 50%; font-size: 16px; color: var(--ula-text-primary); cursor: pointer; display: flex; align-items: center; justify-content: center;" title="{{ __('Close') }}">
                    <span class="material-symbols-rounded" style="font-size: 16px;">close</span>
                </button>
            </div>

            <!-- Modal Form -->
            <form id="item-form" method="POST" action="{{ route('superadmin.furniture.item.store') }}" enctype="multipart/form-data" style="display: flex; flex-direction: column; gap: 16px;">
                @csrf
                <div id="item-method-field"></div>

                <!-- Row 1: Name & Category -->
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px;">
                    <div>
                        <label style="display: block; font-size: 11px; font-weight: 700; color: var(--ula-text-secondary); margin-bottom: 6px; text-transform: uppercase;">
                            {{ __('Item Name') }} <span style="color: var(--ula-status-danger);">*</span>
                        </label>
                        <input type="text" name="name" id="item-name" required placeholder="e.g. Executive Sofa" style="width: 100%; background: var(--ula-surface-page-alt); border: 1px solid var(--ula-border-subtle); border-radius: 10px; padding: 10px 14px; font-size: 13px; color: var(--ula-text-primary); outline: none;">
                    </div>

                    <div>
                        <label style="display: block; font-size: 11px; font-weight: 700; color: var(--ula-text-secondary); margin-bottom: 6px; text-transform: uppercase;">
                            {{ __('Category') }} <span style="color: var(--ula-status-danger);">*</span>
                        </label>
                        <select name="category_id" id="item-category-id" required style="width: 100%; background: var(--ula-surface-page-alt); border: 1px solid var(--ula-border-subtle); border-radius: 10px; padding: 10px 14px; font-size: 13px; color: var(--ula-text-primary); outline: none;">
                            @foreach($categories as $c)
                                <option value="{{ $c->id }}">{{ $c->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <!-- Image Dropzone -->
                <div>
                    <label style="display: block; font-size: 11px; font-weight: 700; color: var(--ula-text-secondary); margin-bottom: 6px; text-transform: uppercase;">
                        {{ __('Sprite / Texture Image (PNG, WebP, SVG)') }}
                    </label>
                    <div id="dropzone-box" style="position: relative; border: 2px dashed var(--ula-border-subtle); background: var(--ula-surface-page-alt); border-radius: 14px; padding: 18px; text-align: center; cursor: pointer;">
                        <input type="file" name="image" id="item-image-file" accept="image/*" onchange="previewUploadImage(this)" style="position: absolute; inset: 0; opacity: 0; cursor: pointer; width: 100%; height: 100%; z-index: 5;">
                        
                        <div id="dropzone-prompt" style="display: flex; flex-direction: column; align-items: center; gap: 6px;">
                            <span class="material-symbols-rounded" style="font-size: 28px; color: var(--ula-highlight-default);">cloud_upload</span>
                            <div style="font-size: 13px; font-weight: 700; color: var(--ula-text-primary);">
                                {{ __('Click or Drag image here to upload') }}
                            </div>
                            <div style="font-size: 11px; color: var(--ula-text-muted);">
                                PNG, WebP, SVG (Transparent background recommended)
                            </div>
                        </div>

                        <!-- Image Preview Box -->
                        <div id="image-preview-container" style="display: none; flex-direction: column; align-items: center; gap: 6px; z-index: 10; position: relative;">
                            <div style="padding: 8px; background: var(--ula-surface-card); border-radius: 10px; border: 1px solid var(--ula-border-subtle); display: inline-flex; align-items: center; justify-content: center; min-width: 100px; min-height: 80px;">
                                <img id="image-preview-img" src="#" alt="Preview" style="max-height: 80px; max-width: 160px; object-fit: contain;">
                            </div>
                            <span style="font-size: 11px; font-weight: 600; color: var(--ula-palm-900);">
                                {{ __('Click to change image') }}
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Footprint Dimensions -->
                <div style="background: var(--ula-surface-page-alt); border: 1px solid var(--ula-border-subtle); border-radius: 14px; padding: 14px 16px;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
                        <span style="font-size: 12px; font-weight: 700; color: var(--ula-text-primary);">{{ __('Floor Grid Footprint') }}</span>
                        <span id="grid-dimensions-badge" style="font-size: 11px; font-weight: 700; color: var(--ula-palm-900); font-family: 'IBM Plex Mono', monospace;">
                            1 × 1 Tiles (32 × 32 px)
                        </span>
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px;">
                        <div>
                            <label style="display: block; font-size: 11px; font-weight: 600; color: var(--ula-text-muted); margin-bottom: 4px;">{{ __('Tile Width (Columns)') }}</label>
                            <div style="display: flex; align-items: center; gap: 6px;">
                                <button type="button" onclick="stepDimension('width', -1)" style="width: 32px; height: 32px; border-radius: 8px; border: 1px solid var(--ula-border-subtle); background: var(--ula-surface-card); font-size: 14px; font-weight: 800; cursor: pointer;">−</button>
                                <input type="number" name="width" id="item-width" value="1" min="1" max="10" required style="width: 100%; text-align: center; font-weight: 700; font-size: 13px; padding: 6px; background: var(--ula-surface-card); border: 1px solid var(--ula-border-subtle); border-radius: 8px;" oninput="updateDimensionBadge()">
                                <button type="button" onclick="stepDimension('width', 1)" style="width: 32px; height: 32px; border-radius: 8px; border: 1px solid var(--ula-border-subtle); background: var(--ula-surface-card); font-size: 14px; font-weight: 800; cursor: pointer;">+</button>
                            </div>
                        </div>

                        <div>
                            <label style="display: block; font-size: 11px; font-weight: 600; color: var(--ula-text-muted); margin-bottom: 4px;">{{ __('Tile Height (Rows)') }}</label>
                            <div style="display: flex; align-items: center; gap: 6px;">
                                <button type="button" onclick="stepDimension('height', -1)" style="width: 32px; height: 32px; border-radius: 8px; border: 1px solid var(--ula-border-subtle); background: var(--ula-surface-card); font-size: 14px; font-weight: 800; cursor: pointer;">−</button>
                                <input type="number" name="height" id="item-height" value="1" min="1" max="10" required style="width: 100%; text-align: center; font-weight: 700; font-size: 13px; padding: 6px; background: var(--ula-surface-card); border: 1px solid var(--ula-border-subtle); border-radius: 8px;" oninput="updateDimensionBadge()">
                                <button type="button" onclick="stepDimension('height', 1)" style="width: 32px; height: 32px; border-radius: 8px; border: 1px solid var(--ula-border-subtle); background: var(--ula-surface-card); font-size: 14px; font-weight: 800; cursor: pointer;">+</button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Collision Boundary -->
                <div style="display: flex; align-items: center; justify-content: space-between; background: var(--ula-surface-page-alt); border: 1px solid var(--ula-border-subtle); border-radius: 12px; padding: 12px 16px;">
                    <div>
                        <div style="font-size: 12px; font-weight: 700; color: var(--ula-text-primary);">{{ __('Physical Collision Boundary') }}</div>
                        <div style="font-size: 11px; color: var(--ula-text-muted);">{{ __('Solid object that blocks avatar movement (uncheck for rugs/walkable decor)') }}</div>
                    </div>
                    <label style="display: inline-flex; align-items: center; cursor: pointer;">
                        <input type="checkbox" name="collision" id="item-collision" value="1" checked style="accent-color: var(--ula-palm-900); width: 18px; height: 18px;">
                    </label>
                </div>

                <!-- Save Action Button -->
                <div style="display: flex; justify-content: flex-end; gap: 10px; margin-top: 6px;">
                    <button type="button" onclick="closeItemModal()" class="tactile-btn btn-secondary">{{ __('Cancel') }}</button>
                    <button type="submit" class="tactile-btn btn-primary" style="display: inline-flex; align-items: center; gap: 6px;">
                        <span class="material-symbols-rounded" style="font-size: 16px;">save</span>
                        <span>{{ __('Save Furniture Item') }}</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal 2: Create / Edit Category -->
    <div id="category-modal" class="modal-overlay" style="display: none; position: fixed; inset: 0; background: rgba(20, 43, 36, 0.45); backdrop-filter: blur(8px); z-index: 9999; align-items: center; justify-content: center; padding: 20px;">
        <div class="modal-card" style="border-radius: var(--ula-radius-xl); padding: 28px; width: 100%; max-width: 440px; position: relative;">
            
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; border-bottom: 1px solid var(--ula-border-subtle); padding-bottom: 14px;">
                <div style="display: flex; align-items: center; gap: 8px;">
                    <span class="material-symbols-rounded" style="color: var(--ula-highlight-default); font-size: 22px;">category</span>
                    <h3 style="font-size: 17px; font-weight: 800; color: var(--ula-text-primary); margin: 0;" id="cat-modal-title">{{ __('New Category') }}</h3>
                </div>
                <button onclick="closeCategoryModal()" style="background: var(--ula-surface-page-alt); border: 1px solid var(--ula-border-subtle); width: 32px; height: 32px; border-radius: 50%; font-size: 15px; color: var(--ula-text-primary); cursor: pointer; display: flex; align-items: center; justify-content: center;">
                    <span class="material-symbols-rounded" style="font-size: 16px;">close</span>
                </button>
            </div>

            <form id="category-form" method="POST" action="{{ route('superadmin.furniture.category.store') }}" style="display: flex; flex-direction: column; gap: 14px;">
                @csrf
                <div id="cat-method-field"></div>

                <div>
                    <label style="display: block; font-size: 11px; font-weight: 700; color: var(--ula-text-secondary); margin-bottom: 6px; text-transform: uppercase;">{{ __('Category Name') }} *</label>
                    <input type="text" name="name" id="cat-name" required placeholder="e.g. Seating, Tables, Electronics" style="width: 100%; background: var(--ula-surface-page-alt); border: 1px solid var(--ula-border-subtle); border-radius: 10px; padding: 10px 14px; font-size: 13px; color: var(--ula-text-primary); outline: none;">
                </div>

                <div>
                    <label style="display: block; font-size: 11px; font-weight: 700; color: var(--ula-text-secondary); margin-bottom: 6px; text-transform: uppercase;">{{ __('Display Order') }}</label>
                    <input type="number" name="order" id="cat-order" value="0" style="width: 100%; background: var(--ula-surface-page-alt); border: 1px solid var(--ula-border-subtle); border-radius: 10px; padding: 10px 14px; font-size: 13px; color: var(--ula-text-primary); outline: none;">
                </div>

                <div style="display: flex; justify-content: flex-end; gap: 10px; margin-top: 6px;">
                    <button type="button" onclick="closeCategoryModal()" class="tactile-btn btn-secondary">{{ __('Cancel') }}</button>
                    <button type="submit" class="tactile-btn btn-primary" style="display: inline-flex; align-items: center; gap: 6px;">
                        <span class="material-symbols-rounded" style="font-size: 16px;">save</span>
                        <span>{{ __('Save Category') }}</span>
                    </button>
                </div>
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

        function openItemModal() {
            document.getElementById('item-modal-title').textContent = '{{ __('Upload Furniture Asset') }}';
            document.getElementById('item-form').action = "{{ route('superadmin.furniture.item.store') }}";
            document.getElementById('item-method-field').innerHTML = '';
            document.getElementById('item-name').value = '';
            document.getElementById('item-width').value = 1;
            document.getElementById('item-height').value = 1;
            document.getElementById('item-collision').checked = true;
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
            document.getElementById('item-collision').checked = Boolean(item.collision);
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
            document.getElementById('cat-modal-title').textContent = '{{ __('New Category') }}';
            document.getElementById('category-form').action = "{{ route('superadmin.furniture.category.store') }}";
            document.getElementById('cat-method-field').innerHTML = '';
            document.getElementById('cat-name').value = '';
            document.getElementById('cat-order').value = 0;
            document.getElementById('category-modal').style.display = 'flex';
        }

        function editCategory(cat) {
            document.getElementById('cat-modal-title').textContent = '{{ __('Edit Category') }}';
            document.getElementById('category-form').action = `/superadmin/furniture/category/${cat.id}`;
            document.getElementById('cat-method-field').innerHTML = '@method("PUT")';
            document.getElementById('cat-name').value = cat.name;
            document.getElementById('cat-order').value = cat.order || 0;
            document.getElementById('category-modal').style.display = 'flex';
        }

        function closeCategoryModal() {
            document.getElementById('category-modal').style.display = 'none';
        }
    </script>
@endsection
