@extends('superadmin.layout')

@section('title', __('Edit Website Content: :title', ['title' => $page->title_en]))
@section('page_title', __('Website CMS — Section & Image Editor'))

@section('styles')
<style>
    .cms-section-card {
        background: var(--ula-surface-card);
        border: 1px solid var(--ula-border-subtle);
        border-radius: var(--ula-radius-xl);
        padding: 28px;
        margin-bottom: 24px;
        box-shadow: var(--ula-shadow-xs);
        transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
        scroll-margin-top: 90px;
    }
    .cms-section-card:hover {
        border-color: var(--ula-border-default);
        box-shadow: var(--ula-shadow-sm);
    }
    .cms-section-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 16px;
        margin-bottom: 22px;
        padding-bottom: 18px;
        border-bottom: 1px solid var(--ula-border-subtle);
    }
    .cms-sec-icon-box {
        width: 44px;
        height: 44px;
        border-radius: 12px;
        background: var(--ula-surface-page-alt);
        border: 1px solid var(--ula-border-subtle);
        display: flex;
        align-items: center;
        justify-content: center;
        color: var(--ula-palm-700);
        font-size: 22px;
        flex-shrink: 0;
    }
    .cms-quick-nav {
        display: flex;
        gap: 8px;
        overflow-x: auto;
        padding: 6px 2px 14px;
        scrollbar-width: none;
        position: sticky;
        top: 70px;
        z-index: 30;
        background: rgba(249, 246, 239, 0.92);
        backdrop-filter: blur(12px);
        margin-bottom: 20px;
        border-radius: 16px;
    }
    [data-theme="dark"] .cms-quick-nav {
        background: rgba(14, 28, 23, 0.92);
    }
    .cms-nav-pill {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 7px 14px;
        background: var(--ula-surface-card);
        border: 1px solid var(--ula-border-subtle);
        border-radius: 9999px;
        font-size: 12px;
        font-weight: 800;
        color: var(--ula-text-secondary);
        text-decoration: none;
        white-space: nowrap;
        transition: all 0.15s ease;
        box-shadow: var(--ula-shadow-xs);
    }
    .cms-nav-pill:hover {
        background: var(--ula-surface-page-alt);
        color: var(--ula-text-primary);
        border-color: var(--ula-palm-700);
        transform: translateY(-1px);
    }
    .cms-img-box {
        background: var(--ula-surface-page-alt);
        border: 1px solid var(--ula-border-subtle);
        border-radius: 16px;
        padding: 20px;
        margin-bottom: 20px;
    }
    .cms-img-preview-frame {
        width: 160px;
        height: 105px;
        border-radius: 12px;
        overflow: hidden;
        border: 1px solid var(--ula-border-default);
        background: var(--ula-surface-card);
        display: flex;
        align-items: center;
        justify-content: center;
        position: relative;
        box-shadow: var(--ula-shadow-xs);
    }
    .cms-img-preview-frame img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
</style>
@endsection

@section('content')
<div style="display: flex; flex-direction: column; gap: 20px;">

    <!-- Top Action Bar -->
    <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px;">
        <div>
            <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 4px;">
                <a href="{{ route('superadmin.cms.pages') }}" class="tactile-btn btn-outline" style="padding: 5px 12px; font-size: 12px; display: inline-flex; align-items: center; gap: 6px;">
                    <span class="material-symbols-rounded" style="font-size: 16px;">arrow_back</span>
                    <span>{{ __('Back to Pages') }}</span>
                </a>
                <h2 style="font-size: 21px; font-weight: 900; color: var(--ula-text-primary); margin: 0;">
                    {{ __('Website Content & Image Manager') }}
                </h2>
            </div>
            <p style="font-size: 13px; color: var(--ula-text-muted); margin: 0;">
                {{ __('Visually edit live headlines, descriptions, action buttons, and replace images for all 8 website sections.') }}
            </p>
        </div>
        <div style="display: flex; gap: 10px; flex-wrap: wrap;">
            <a href="{{ route('landing.home') }}" target="_blank" class="tactile-btn btn-outline" style="display: inline-flex; align-items: center; gap: 6px;">
                <span class="material-symbols-rounded" style="font-size: 18px;">visibility</span>
                <span>{{ __('Preview Live Website') }}</span>
            </a>
            <a href="{{ route('superadmin.cms.assets') }}" class="tactile-btn btn-secondary" style="display: inline-flex; align-items: center; gap: 6px;">
                <span class="material-symbols-rounded" style="font-size: 18px;">photo_library</span>
                <span>{{ __('Media Library') }}</span>
            </a>
            <a href="{{ route('superadmin.cms.theme') }}" class="tactile-btn btn-primary" style="display: inline-flex; align-items: center; gap: 6px;">
                <span class="material-symbols-rounded" style="font-size: 18px;">palette</span>
                <span>{{ __('Theme & Branding') }}</span>
            </a>
        </div>
    </div>

    @if(session('success'))
        <div style="background: rgba(79, 155, 95, 0.15); border: 1px solid rgba(79, 155, 95, 0.35); color: var(--ula-status-success); padding: 14px 18px; border-radius: var(--ula-radius-sm); font-size: 13.5px; font-weight: 800; display: flex; align-items: center; gap: 10px;">
            <span class="material-symbols-rounded" style="font-size: 20px;">check_circle</span>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    @php
        $sectionMeta = [
            'home_hero' => [
                'name_ar' => '1. البانر الرئيسي والمقدمة (Hero)',
                'name_en' => 'Hero Banner & Interactive Office',
                'icon' => 'apartment',
                'has_image' => true,
                'img_desc' => 'خلفية البانر الرئيسي ومخطط المقر (Recommended: 1920x1080 WebP/JPG)'
            ],
            'home_spatial_presence' => [
                'name_ar' => '2. أركان المنصة الأربعة (4 Pillars)',
                'name_en' => '4 Platform Pillars Feature Strip',
                'icon' => 'view_column',
                'has_image' => false,
                'img_desc' => ''
            ],
            'home_floorplan_editor' => [
                'name_ar' => '3. استكشاف المساحات الذكية (Smart Spaces)',
                'name_en' => 'Smart Spaces Floorplan Explorer',
                'icon' => 'map',
                'has_image' => true,
                'img_desc' => 'صورة استعراض المخطط التفاعلي ومكاتب العمل (Recommended: 1200x800 WebP/JPG)'
            ],
            'home_collaboration' => [
                'name_ar' => '4. مميزات وقدرات المنصة (Capabilities Matrix)',
                'name_en' => 'Platform Capabilities & Spatial Mesh',
                'icon' => 'hub',
                'has_image' => false,
                'img_desc' => ''
            ],
            'home_meetings' => [
                'name_ar' => '5. جدول الاجتماعات الحية (Live Meetings)',
                'name_en' => 'Live Meetings & Realtime Presence',
                'icon' => 'videocam',
                'has_image' => false,
                'img_desc' => ''
            ],
            'home_company_workspace' => [
                'name_ar' => '6. الهوية السعودية ومقرات الشركات (Saudi Identity)',
                'name_en' => 'Saudi Heritage & Multi-Tenancy HQ',
                'icon' => 'flag',
                'has_image' => true,
                'img_desc' => 'صورة بطاقة هوية العلا وتراث السعودية (Recommended: 800x600 WebP/JPG)'
            ],
            'home_pricing' => [
                'name_ar' => '7. باقات وخطط الاشتراك (Pricing Plans)',
                'name_en' => 'SaaS Subscription Plans',
                'icon' => 'payments',
                'has_image' => false,
                'img_desc' => ''
            ],
            'home_cta' => [
                'name_ar' => '8. بانر الإجراء والدعوة للانضمام (Action Banner)',
                'name_en' => 'Bottom Conversion & CTA Banner',
                'icon' => 'rocket_launch',
                'has_image' => true,
                'img_desc' => 'خلفية بانر التسجيل والتجربة المجانية (Recommended: 1920x600 WebP/JPG)'
            ],
        ];
    @endphp

    <!-- Sticky Table of Contents Quick Nav -->
    <div class="cms-quick-nav">
        @foreach($page->sections as $sec)
            @php $meta = $sectionMeta[$sec->section_key] ?? null; @endphp
            <a href="#sec_{{ $sec->section_key }}" class="cms-nav-pill">
                <span class="material-symbols-rounded" style="font-size: 15px; color: var(--ula-palm-700);">{{ $meta['icon'] ?? 'view_quilt' }}</span>
                <span>{{ $meta['name_ar'] ?? ($sec->title_en ?: $sec->section_key) }}</span>
            </a>
        @endforeach
    </div>

    <!-- Sections List -->
    <div style="display: flex; flex-direction: column;">
        @foreach($page->sections as $sec)
            @php
                $meta = $sectionMeta[$sec->section_key] ?? [
                    'name_ar' => $sec->title_ar ?: 'قسم مخصص',
                    'name_en' => $sec->title_en ?: $sec->section_key,
                    'icon' => 'view_quilt',
                    'has_image' => false,
                    'img_desc' => ''
                ];
            @endphp
            <div class="cms-section-card" id="sec_{{ $sec->section_key }}">
                <!-- Section Header Summary -->
                <div class="cms-section-header">
                    <div style="display: flex; align-items: center; gap: 14px;">
                        <div class="cms-sec-icon-box">
                            <span class="material-symbols-rounded">{{ $meta['icon'] }}</span>
                        </div>
                        <div>
                            <div style="display: flex; align-items: center; gap: 8px;">
                                <span style="font-size: 11px; font-weight: 900; background: var(--ula-surface-page-alt); color: var(--ula-palm-900); padding: 2px 10px; border-radius: 6px; border: 1px solid var(--ula-border-subtle); font-family: var(--ula-font-mono);">
                                    #{{ $sec->display_order }}
                                </span>
                                <strong style="font-size: 17px; font-weight: 900; color: var(--ula-text-primary);">
                                    {{ $meta['name_ar'] }}
                                </strong>
                            </div>
                            <span style="font-size: 12px; color: var(--ula-text-muted); display: block; margin-top: 3px;">
                                {{ $meta['name_en'] }} · Key: <strong style="font-family: var(--ula-font-mono); color: var(--ula-palm-700);">{{ $sec->section_key }}</strong>
                            </span>
                        </div>
                    </div>

                    <div style="display: flex; align-items: center; gap: 10px;">
                        <form method="POST" action="{{ route('superadmin.cms.sections.toggle', $sec) }}" style="margin: 0;">
                            @csrf
                            <button type="submit" class="tactile-btn" style="font-size: 11.5px; padding: 6px 14px; {{ $sec->is_active ? 'background: rgba(79, 155, 95, 0.15); color: var(--ula-status-success); border-color: rgba(79, 155, 95, 0.3);' : 'background: rgba(217, 107, 95, 0.15); color: var(--ula-status-danger); border-color: rgba(217, 107, 95, 0.3);' }}">
                                <span class="material-symbols-rounded" style="font-size: 15px;">{{ $sec->is_active ? 'check_circle' : 'visibility_off' }}</span>
                                <span>{{ $sec->is_active ? __('Active on Website') : __('Hidden / Disabled') }}</span>
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Section Edit Form with Direct File Upload -->
                <form method="POST" action="{{ route('superadmin.cms.sections.update', $sec) }}" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')

                    <!-- Bilingual Headlines -->
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 16px; margin-bottom: 18px;">
                        <!-- Arabic Title -->
                        <div>
                            <label style="display: flex; align-items: center; gap: 6px; font-size: 11.5px; font-weight: 800; color: var(--ula-text-secondary); margin-bottom: 6px; text-transform: uppercase;">
                                <span class="badge-status" style="font-family: var(--ula-font-en);">AR</span>
                                <span>{{ __('العنوان الرئيسي (Arabic Headline)') }}</span>
                            </label>
                            <input type="text" name="title_ar" value="{{ $sec->title_ar }}" dir="rtl" class="form-input" style="width: 100%; font-weight: 700; font-family: 'Cairo', sans-serif;">
                        </div>

                        <!-- English Title -->
                        <div>
                            <label style="display: flex; align-items: center; gap: 6px; font-size: 11.5px; font-weight: 800; color: var(--ula-text-secondary); margin-bottom: 6px; text-transform: uppercase;">
                                <span class="badge-status" style="font-family: var(--ula-font-en);">EN</span>
                                <span>{{ __('English Headline') }}</span>
                            </label>
                            <input type="text" name="title_en" value="{{ $sec->title_en }}" class="form-input" style="width: 100%; font-weight: 700; font-family: 'IBM Plex Sans', sans-serif;">
                        </div>

                        <!-- Arabic Subtitle -->
                        <div>
                            <label style="display: flex; align-items: center; gap: 6px; font-size: 11.5px; font-weight: 800; color: var(--ula-text-secondary); margin-bottom: 6px; text-transform: uppercase;">
                                <span class="badge-status" style="font-family: var(--ula-font-en);">AR</span>
                                <span>{{ __('الوصف الفرعي (Arabic Subtitle / Description)') }}</span>
                            </label>
                            <textarea name="subtitle_ar" rows="2" dir="rtl" class="form-input" style="width: 100%; font-size: 13px; font-family: 'Cairo', sans-serif;">{{ $sec->subtitle_ar }}</textarea>
                        </div>

                        <!-- English Subtitle -->
                        <div>
                            <label style="display: flex; align-items: center; gap: 6px; font-size: 11.5px; font-weight: 800; color: var(--ula-text-secondary); margin-bottom: 6px; text-transform: uppercase;">
                                <span class="badge-status" style="font-family: var(--ula-font-en);">EN</span>
                                <span>{{ __('English Subtitle / Description') }}</span>
                            </label>
                            <textarea name="subtitle_en" rows="2" class="form-input" style="width: 100%; font-size: 13px; font-family: 'IBM Plex Sans', sans-serif;">{{ $sec->subtitle_en }}</textarea>
                        </div>

                        <!-- Arabic Badge -->
                        <div>
                            <label style="display: flex; align-items: center; gap: 6px; font-size: 11.5px; font-weight: 800; color: var(--ula-text-secondary); margin-bottom: 6px; text-transform: uppercase;">
                                <span class="material-symbols-rounded" style="font-size: 15px; color: var(--ula-palm-700);">label</span>
                                <span>{{ __('شارة القسم (Badge Pill - Arabic)') }}</span>
                            </label>
                            <input type="text" name="badge_ar" value="{{ $sec->badge_ar }}" dir="rtl" class="form-input" style="width: 100%; font-family: 'Cairo', sans-serif;">
                        </div>

                        <!-- English Badge -->
                        <div>
                            <label style="display: flex; align-items: center; gap: 6px; font-size: 11.5px; font-weight: 800; color: var(--ula-text-secondary); margin-bottom: 6px; text-transform: uppercase;">
                                <span class="material-symbols-rounded" style="font-size: 15px; color: var(--ula-palm-700);">label</span>
                                <span>{{ __('Badge Pill (English)') }}</span>
                            </label>
                            <input type="text" name="badge_en" value="{{ $sec->badge_en }}" class="form-input" style="width: 100%;">
                        </div>
                    </div>

                    <!-- Image & Visual Asset Management Box (Only for sections that actually use images) -->
                    @if($meta['has_image'] ?? false)
                        <div class="cms-img-box">
                            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 14px; flex-wrap: wrap; gap: 8px;">
                                <label style="display: inline-flex; align-items: center; gap: 6px; font-size: 12px; font-weight: 900; color: var(--ula-text-primary); text-transform: uppercase; margin: 0;">
                                <span class="material-symbols-rounded" style="font-size: 18px; color: var(--ula-palm-700);">photo_camera</span>
                                <span>{{ __('Section Image & Visual Preview') }}</span>
                            </label>
                            <span style="font-size: 11.5px; color: var(--ula-text-muted);">{{ $meta['img_desc'] }}</span>
                        </div>

                        <div style="display: grid; grid-template-columns: 160px 1fr; gap: 20px; align-items: center; flex-wrap: wrap;">
                            <!-- Current Image Preview -->
                            <div class="cms-img-preview-frame">
                                @php
                                    $currentImg = $sec->image_url;
                                    if (!$currentImg && ($sec->section_key === 'home_spaces' || $sec->section_key === 'home_floorplan_editor')) {
                                        $currentImg = asset('images/isometric_office_preview.jpg');
                                    } elseif (!$currentImg && $sec->section_key === 'home_hero') {
                                        $currentImg = asset('images/office_floorplan.jpg');
                                    }
                                @endphp
                                @if($currentImg)
                                    <img src="{{ $currentImg }}" alt="Preview">
                                @else
                                    <span style="font-size: 11px; color: var(--ula-text-muted); text-align: center; padding: 8px;">{{ __('No custom image') }}</span>
                                @endif
                            </div>

                            <!-- Upload or Select from Media Library -->
                            <div style="display: flex; flex-direction: column; gap: 12px;">
                                <div>
                                    <label style="display: block; font-size: 11px; font-weight: 800; color: var(--ula-text-secondary); margin-bottom: 5px;">
                                        {{ __('Upload New Section Image / Screenshot') }} (JPG, PNG, WebP)
                                    </label>
                                    <input type="file" name="image_file" accept="image/*" class="form-input" style="width: 100%; font-size: 12px; padding: 7px 12px;">
                                </div>

                                <div style="display: grid; grid-template-columns: 1fr 140px; gap: 12px;">
                                    <div>
                                        <label style="display: block; font-size: 11px; font-weight: 800; color: var(--ula-text-secondary); margin-bottom: 5px;">
                                            {{ __('Or Select Existing Asset from Media Library') }}
                                        </label>
                                        <select name="media_asset_id" class="form-input" style="width: 100%; font-size: 12px;">
                                            <option value="">— {{ __('No Assigned Media Asset') }} —</option>
                                            @foreach(($assets ?? []) as $ast)
                                                <option value="{{ $ast->id }}" {{ $sec->media_asset_id == $ast->id ? 'selected' : '' }}>
                                                    [{{ strtoupper($ast->asset_type) }}] {{ $ast->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div>
                                        <label style="display: block; font-size: 11px; font-weight: 800; color: var(--ula-text-secondary); margin-bottom: 5px;">
                                            {{ __('Display Order') }}
                                        </label>
                                        <input type="number" name="display_order" value="{{ $sec->display_order }}" class="form-input" style="width: 100%; font-weight: 700;">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    @else
                        <!-- No image used on website for this section; simple display order field -->
                        <div style="margin-bottom: 18px; max-width: 200px;">
                            <label style="display: block; font-size: 11px; font-weight: 800; color: var(--ula-text-secondary); margin-bottom: 5px;">
                                {{ __('Display Order') }}
                            </label>
                            <input type="number" name="display_order" value="{{ $sec->display_order }}" class="form-input" style="width: 100%; font-weight: 700;">
                        </div>
                    @endif

                    <!-- Section-Specific Action Buttons / CTAs -->
                    @if($sec->section_key === 'home_hero' || $sec->section_type === 'hero_3d')
                        <div style="background: var(--ula-surface-card); border: 1px solid var(--ula-border-subtle); border-radius: 14px; padding: 18px; margin-bottom: 18px;">
                            <label style="display: flex; align-items: center; gap: 6px; font-size: 12px; font-weight: 900; color: var(--ula-text-primary); text-transform: uppercase; margin-bottom: 12px;">
                                <span class="material-symbols-rounded" style="font-size: 17px; color: var(--ula-palm-700);">smart_button</span>
                                <span>{{ __('Hero Action Buttons & Overlay Typography') }}</span>
                            </label>

                            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 12px; margin-bottom: 14px;">
                                <div>
                                    <label style="font-size: 11px; font-weight: 700; color: var(--ula-text-muted);">{{ __('Primary Button (Arabic)') }}</label>
                                    <input type="text" name="content[cta_primary_text_ar]" value="{{ $sec->getContentValue('cta_primary_text_ar', 'ادخل إلى مساحتك') }}" dir="rtl" class="form-input" style="width: 100%; font-size: 12.5px; font-family: 'Cairo', sans-serif;">
                                </div>
                                <div>
                                    <label style="font-size: 11px; font-weight: 700; color: var(--ula-text-muted);">{{ __('Primary Button (English)') }}</label>
                                    <input type="text" name="content[cta_primary_text_en]" value="{{ $sec->getContentValue('cta_primary_text_en', 'Enter Your Space') }}" class="form-input" style="width: 100%; font-size: 12.5px;">
                                </div>
                                <div>
                                    <label style="font-size: 11px; font-weight: 700; color: var(--ula-text-muted);">{{ __('Primary Button Link URL') }}</label>
                                    <input type="text" name="content[cta_primary_link]" value="{{ $sec->getContentValue('cta_primary_link', '/register') }}" class="form-input" style="width: 100%; font-size: 12.5px;">
                                </div>
                                <div>
                                    <label style="font-size: 11px; font-weight: 700; color: var(--ula-text-muted);">{{ __('Secondary Button (Arabic)') }}</label>
                                    <input type="text" name="content[cta_secondary_text_ar]" value="{{ $sec->getContentValue('cta_secondary_text_ar', 'شاهد العرض') }}" dir="rtl" class="form-input" style="width: 100%; font-size: 12.5px; font-family: 'Cairo', sans-serif;">
                                </div>
                                <div>
                                    <label style="font-size: 11px; font-weight: 700; color: var(--ula-text-muted);">{{ __('Secondary Button (English)') }}</label>
                                    <input type="text" name="content[cta_secondary_text_en]" value="{{ $sec->getContentValue('cta_secondary_text_en', 'Watch Demo') }}" class="form-input" style="width: 100%; font-size: 12.5px;">
                                </div>
                                <div>
                                    <label style="font-size: 11px; font-weight: 700; color: var(--ula-text-muted);">{{ __('Secondary Button Link URL') }}</label>
                                    <input type="text" name="content[cta_secondary_link]" value="{{ $sec->getContentValue('cta_secondary_link', '#spaces') }}" class="form-input" style="width: 100%; font-size: 12.5px;">
                                </div>
                            </div>

                            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 12px; border-top: 1px dashed var(--ula-border-subtle); padding-top: 14px;">
                                <div>
                                    <label style="font-size: 11px; font-weight: 700; color: var(--ula-text-muted);">{{ __('Large Overlay Headline (Arabic)') }}</label>
                                    <textarea name="content[overlay_title_ar]" rows="2" dir="rtl" class="form-input" style="width: 100%; font-size: 12.5px; font-family: 'Cairo', sans-serif;">{{ $sec->getContentValue('overlay_title_ar', "أكثر من\nمكان العمل") }}</textarea>
                                </div>
                                <div>
                                    <label style="font-size: 11px; font-weight: 700; color: var(--ula-text-muted);">{{ __('Large Overlay Headline (English)') }}</label>
                                    <textarea name="content[overlay_title_en]" rows="2" class="form-input" style="width: 100%; font-size: 12.5px;">{{ $sec->getContentValue('overlay_title_en', "A more human\nplace to work.") }}</textarea>
                                </div>
                                <div>
                                    <label style="font-size: 11px; font-weight: 700; color: var(--ula-text-muted);">{{ __('English Tagline Under Arch') }}</label>
                                    <input type="text" name="content[hero_sub_en]" value="{{ $sec->getContentValue('hero_sub_en', 'A more human way to work together.') }}" class="form-input" style="width: 100%; font-size: 12.5px;">
                                </div>
                            </div>
                        </div>
                    @elseif($sec->section_key === 'home_cta' || $sec->section_type === 'cta')
                        <div style="background: var(--ula-surface-card); border: 1px solid var(--ula-border-subtle); border-radius: 14px; padding: 18px; margin-bottom: 18px;">
                            <label style="display: flex; align-items: center; gap: 6px; font-size: 12px; font-weight: 900; color: var(--ula-text-primary); text-transform: uppercase; margin-bottom: 12px;">
                                <span class="material-symbols-rounded" style="font-size: 17px; color: var(--ula-palm-700);">rocket_launch</span>
                                <span>{{ __('Action Banner Configuration') }}</span>
                            </label>

                            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 12px;">
                                <div>
                                    <label style="font-size: 11px; font-weight: 700; color: var(--ula-text-muted);">{{ __('Primary Button Text (Arabic)') }}</label>
                                    <input type="text" name="content[cta_primary_text_ar]" value="{{ $sec->getContentValue('cta_primary_text_ar', 'ابدأ التجربة المجانية الآن') }}" dir="rtl" class="form-input" style="width: 100%; font-size: 12.5px; font-family: 'Cairo', sans-serif;">
                                </div>
                                <div>
                                    <label style="font-size: 11px; font-weight: 700; color: var(--ula-text-muted);">{{ __('Primary Button Text (English)') }}</label>
                                    <input type="text" name="content[cta_primary_text_en]" value="{{ $sec->getContentValue('cta_primary_text_en', 'Start Free Trial Today') }}" class="form-input" style="width: 100%; font-size: 12.5px;">
                                </div>
                                <div>
                                    <label style="font-size: 11px; font-weight: 700; color: var(--ula-text-muted);">{{ __('Primary Button Link') }}</label>
                                    <input type="text" name="content[cta_primary_link]" value="{{ $sec->getContentValue('cta_primary_link', '/register') }}" class="form-input" style="width: 100%; font-size: 12.5px;">
                                </div>
                                <div>
                                    <label style="font-size: 11px; font-weight: 700; color: var(--ula-text-muted);">{{ __('Secondary Button Text (Arabic)') }}</label>
                                    <input type="text" name="content[cta_secondary_text_ar]" value="{{ $sec->getContentValue('cta_secondary_text_ar', 'تسجيل الدخول') }}" dir="rtl" class="form-input" style="width: 100%; font-size: 12.5px; font-family: 'Cairo', sans-serif;">
                                </div>
                                <div>
                                    <label style="font-size: 11px; font-weight: 700; color: var(--ula-text-muted);">{{ __('Secondary Button Text (English)') }}</label>
                                    <input type="text" name="content[cta_secondary_text_en]" value="{{ $sec->getContentValue('cta_secondary_text_en', 'Sign In') }}" class="form-input" style="width: 100%; font-size: 12.5px;">
                                </div>
                                <div>
                                    <label style="font-size: 11px; font-weight: 700; color: var(--ula-text-muted);">{{ __('Secondary Button Link') }}</label>
                                    <input type="text" name="content[cta_secondary_link]" value="{{ $sec->getContentValue('cta_secondary_link', '/login') }}" class="form-input" style="width: 100%; font-size: 12.5px;">
                                </div>
                            </div>
                        </div>
                    @endif

                    <!-- Advanced JSON payload toggle -->
                    <details style="margin-bottom: 18px;">
                        <summary style="font-size: 11px; font-weight: 800; color: var(--ula-text-muted); cursor: pointer; text-transform: uppercase; display: inline-flex; align-items: center; gap: 4px;">
                            <span class="material-symbols-rounded" style="font-size: 14px;">settings</span>
                            <span>{{ __('Advanced Structured Content (JSON Payload)') }}</span>
                        </summary>
                        <div style="margin-top: 8px;">
                            <textarea name="content_json" rows="3" class="form-input" style="width: 100%; font-family: var(--ula-font-mono); font-size: 11px;">{{ json_encode($sec->content, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</textarea>
                        </div>
                    </details>

                    <!-- Save Actions -->
                    <div style="display: flex; justify-content: space-between; align-items: center; border-top: 1px solid var(--ula-border-subtle); padding-top: 16px; flex-wrap: wrap; gap: 12px;">
                        <label style="display: flex; align-items: center; gap: 8px; font-size: 12.5px; font-weight: 800; color: var(--ula-text-primary); cursor: pointer;">
                            <input type="checkbox" name="is_active" value="1" {{ $sec->is_active ? 'checked' : '' }} style="accent-color: var(--ula-palm-900); width: 18px; height: 18px;">
                            <span>{{ __('Active & Displayed on Website') }}</span>
                        </label>

                        <button type="submit" class="tactile-btn btn-primary" style="padding: 9px 26px; font-size: 13px; display: inline-flex; align-items: center; gap: 6px;">
                            <span class="material-symbols-rounded" style="font-size: 17px;">save</span>
                            <span>{{ __('Save Section Changes') }}</span>
                        </button>
                    </div>
                </form>
            </div>
        @endforeach
    </div>
</div>
@endsection


