@extends('superadmin.layout')

@section('title', __('Edit Page: :title', ['title' => $page->title_en]))
@section('page_title', __('Website CMS — Section Builder (:title)', ['title' => $page->title_en]))

@section('content')
<div style="display: flex; flex-direction: column; gap: 24px;">

    <!-- Top Navigation Header -->
    <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px;">
        <div>
            <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 4px;">
                <a href="{{ route('superadmin.cms.pages') }}" class="tactile-btn btn-outline" style="padding: 4px 10px; font-size: 11px;">
                    ← {{ __('Back to Pages') }}
                </a>
                <h2 style="font-size: 20px; font-weight: 900; color: var(--text-primary); margin: 0;">
                    🧩 {{ __('Section Builder & Content Editor') }}: {{ $page->title_en }}
                </h2>
            </div>
            <p style="font-size: 13px; color: var(--text-muted); margin: 0;">
                {{ __('Reorder, customize, and publish modular sections for this page.') }}
            </p>
        </div>
        <div style="display: flex; gap: 10px;">
            <a href="{{ route('landing.home') }}" target="_blank" class="tactile-btn btn-primary">
                <span>👁️</span> {{ __('Preview Live') }}
            </a>
        </div>
    </div>

    @if(session('success'))
        <div style="background: rgba(79, 155, 95, 0.15); border: 1px solid rgba(79, 155, 95, 0.35); color: #4F9B5F; padding: 14px 18px; border-radius: var(--radius-md); font-size: 13px; font-weight: 800;">
            ✅ {{ session('success') }}
        </div>
    @endif

    <!-- Sections List Accordion -->
    <div style="display: flex; flex-direction: column; gap: 18px;">
        @foreach($page->sections as $index => $sec)
            <div class="panel-card" style="border-radius: var(--radius-xl); padding: 24px; border: 1px solid var(--border-color); background: var(--bg-surface);">
                <!-- Section Header Summary -->
                <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px; margin-bottom: 18px; padding-bottom: 14px; border-bottom: 1px solid var(--border-color);">
                    <div style="display: flex; align-items: center; gap: 12px;">
                        <span style="font-size: 12px; font-weight: 900; background: var(--bg-surface-subtle); color: var(--brand-forest); padding: 4px 10px; border-radius: 8px;">
                            #{{ $sec->display_order }}
                        </span>
                        <div>
                            <strong style="font-size: 16px; font-weight: 900; color: var(--text-primary);">
                                {{ $sec->title_en ?: ucfirst(str_replace('_', ' ', $sec->section_type)) }}
                            </strong>
                            <span style="font-size: 11px; color: var(--text-muted); display: block; font-family: monospace;">
                                Type: <strong>{{ $sec->section_type }}</strong> • Key: <strong>{{ $sec->section_key }}</strong>
                            </span>
                        </div>
                    </div>

                    <div style="display: flex; align-items: center; gap: 10px;">
                        <form method="POST" action="{{ route('superadmin.cms.sections.toggle', $sec) }}" style="margin: 0;">
                            @csrf
                            <button type="submit" class="tactile-btn" style="font-size: 11px; padding: 5px 12px; {{ $sec->is_active ? 'background: rgba(79, 155, 95, 0.15); color: #4F9B5F; border-color: rgba(79, 155, 95, 0.3);' : 'background: rgba(217, 107, 95, 0.15); color: #D96B5F; border-color: rgba(217, 107, 95, 0.3);' }}">
                                {{ $sec->is_active ? '🟢 ' . __('Active') : '🔴 ' . __('Disabled') }}
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Section Edit Form -->
                <form method="POST" action="{{ route('superadmin.cms.sections.update', $sec) }}">
                    @csrf
                    @method('PUT')

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 18px; margin-bottom: 18px;">
                        <!-- English Title -->
                        <div>
                            <label style="display: block; font-size: 11px; font-weight: 800; color: var(--text-secondary); margin-bottom: 4px; text-transform: uppercase;">
                                🇺🇸 {{ __('Title (English)') }}
                            </label>
                            <input type="text" name="title_en" value="{{ $sec->title_en }}" class="form-input" style="width: 100%; font-weight: 700;">
                        </div>

                        <!-- Arabic Title -->
                        <div>
                            <label style="display: block; font-size: 11px; font-weight: 800; color: var(--text-secondary); margin-bottom: 4px; text-transform: uppercase;">
                                🇸🇦 {{ __('Title (Arabic)') }}
                            </label>
                            <input type="text" name="title_ar" value="{{ $sec->title_ar }}" dir="rtl" class="form-input" style="width: 100%; font-weight: 700; font-family: 'Cairo', sans-serif;">
                        </div>

                        <!-- English Subtitle -->
                        <div>
                            <label style="display: block; font-size: 11px; font-weight: 800; color: var(--text-secondary); margin-bottom: 4px; text-transform: uppercase;">
                                🇺🇸 {{ __('Subtitle (English)') }}
                            </label>
                            <textarea name="subtitle_en" rows="2" class="form-input" style="width: 100%; font-size: 12px;">{{ $sec->subtitle_en }}</textarea>
                        </div>

                        <!-- Arabic Subtitle -->
                        <div>
                            <label style="display: block; font-size: 11px; font-weight: 800; color: var(--text-secondary); margin-bottom: 4px; text-transform: uppercase;">
                                🇸🇦 {{ __('Subtitle (Arabic)') }}
                            </label>
                            <textarea name="subtitle_ar" rows="2" dir="rtl" class="form-input" style="width: 100%; font-size: 12px; font-family: 'Cairo', sans-serif;">{{ $sec->subtitle_ar }}</textarea>
                        </div>

                        <!-- English Badge -->
                        <div>
                            <label style="display: block; font-size: 11px; font-weight: 800; color: var(--text-secondary); margin-bottom: 4px; text-transform: uppercase;">
                                🏷️ {{ __('Badge Pill (English)') }}
                            </label>
                            <input type="text" name="badge_en" value="{{ $sec->badge_en }}" class="form-input" style="width: 100%;">
                        </div>

                        <!-- Arabic Badge -->
                        <div>
                            <label style="display: block; font-size: 11px; font-weight: 800; color: var(--text-secondary); margin-bottom: 4px; text-transform: uppercase;">
                                🏷️ {{ __('Badge Pill (Arabic)') }}
                            </label>
                            <input type="text" name="badge_ar" value="{{ $sec->badge_ar }}" dir="rtl" class="form-input" style="width: 100%; font-family: 'Cairo', sans-serif;">
                        </div>

                        <!-- Assigned 3D / Media Asset -->
                        <div>
                            <label style="display: block; font-size: 11px; font-weight: 800; color: var(--text-secondary); margin-bottom: 4px; text-transform: uppercase;">
                                📁 {{ __('Assigned 3D Model / Media Asset') }}
                            </label>
                            <select name="media_asset_id" class="form-input" style="width: 100%;">
                                <option value="">— {{ __('No Media Asset') }} —</option>
                                @foreach($assets as $ast)
                                    <option value="{{ $ast->id }}" {{ $sec->media_asset_id == $ast->id ? 'selected' : '' }}>
                                        [{{ strtoupper($ast->asset_type) }}] {{ $ast->name }} ({{ $ast->version_tag ?? 'v1' }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Display Order -->
                        <div>
                            <label style="display: block; font-size: 11px; font-weight: 800; color: var(--text-secondary); margin-bottom: 4px; text-transform: uppercase;">
                                🔢 {{ __('Display Order') }}
                            </label>
                            <input type="number" name="display_order" value="{{ $sec->display_order }}" class="form-input" style="width: 100%; font-weight: 700;">
                        </div>
                    </div>

                    @if(!empty($sec->content))
                        <!-- Structured Content JSON Editor -->
                        <div style="margin-bottom: 18px;">
                            <label style="display: block; font-size: 11px; font-weight: 800; color: var(--text-secondary); margin-bottom: 4px; text-transform: uppercase;">
                                ⚙️ {{ __('Structured Content Configuration (JSON Payload)') }}
                            </label>
                            <textarea name="content_json" rows="4" class="form-input" style="width: 100%; font-family: monospace; font-size: 12px;">{{ json_encode($sec->content, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</textarea>
                        </div>
                    @endif

                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <label style="display: flex; align-items: center; gap: 8px; font-size: 12px; font-weight: 800; color: var(--text-primary); cursor: pointer;">
                            <input type="checkbox" name="is_active" value="1" {{ $sec->is_active ? 'checked' : '' }} style="accent-color: var(--brand-forest);">
                            <span>{{ __('Active on Live Website') }}</span>
                        </label>

                        <button type="submit" class="tactile-btn btn-primary" style="padding: 9px 24px; font-size: 12px;">
                            💾 {{ __('Save Section') }}
                        </button>
                    </div>
                </form>
            </div>
        @endforeach
    </div>
</div>
@endsection
