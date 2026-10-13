@extends('superadmin.layout')

@section('title', __('Theme & Branding Studio'))
@section('page_title', __('Website CMS — Theme & Branding Studio'))

@section('content')
<div style="display: flex; flex-direction: column; gap: 24px;">

    <!-- Top Action Bar -->
    <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px;">
        <div>
            <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 4px;">
                <div style="width: 36px; height: 36px; border-radius: 10px; background: var(--ula-surface-page-alt); border: 1px solid var(--ula-border-subtle); display: flex; align-items: center; justify-content: center; color: var(--ula-palm-700);">
                    <span class="material-symbols-rounded" style="font-size: 22px;">palette</span>
                </div>
                <h2 style="font-size: 20px; font-weight: 900; color: var(--ula-text-primary); margin: 0;">
                    {{ __('Theme & Branding Tokens Studio') }}
                </h2>
            </div>
            <p style="font-size: 13px; color: var(--ula-text-muted); margin: 0;">
                {{ __('Control the global color palette, spatial tokens, typography, and styles for the UlaSpace public website.') }}
            </p>
        </div>
        <div style="display: flex; gap: 10px;">
            <a href="{{ route('landing.home') }}" target="_blank" class="tactile-btn btn-primary" style="display: inline-flex; align-items: center; gap: 6px;">
                <span class="material-symbols-rounded" style="font-size: 18px;">visibility</span>
                <span>{{ __('Preview Live Website') }}</span>
            </a>
        </div>
    </div>

    @if(session('success'))
        <div style="background: rgba(79, 155, 95, 0.15); border: 1px solid rgba(79, 155, 95, 0.35); color: var(--ula-status-success); padding: 14px 18px; border-radius: var(--ula-radius-sm); font-size: 13px; font-weight: 800; display: flex; align-items: center; gap: 8px;">
            <span class="material-symbols-rounded">check_circle</span>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    <form method="POST" action="{{ route('superadmin.cms.theme.update') }}">
        @csrf

        <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 24px; align-items: start;">
            <!-- Main Token Customizer -->
            <div style="display: flex; flex-direction: column; gap: 24px;">
                <!-- 1. Color Palette Tokens -->
                <div class="panel-card" style="border-radius: var(--ula-radius-xl); padding: 24px;">
                    <div class="panel-header" style="margin-bottom: 20px;">
                        <div class="panel-title">
                            <span><span class="material-symbols-rounded" style="font-size: 1.15em; vertical-align: -0.2em;" aria-hidden="true">palette</span> </span>
                            <span>{{ __('Primary Color Palette & Spatial Tokens') }}</span>
                        </div>
                    </div>

                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px;">
                        <div>
                            <label style="display: block; font-size: 11px; font-weight: 800; color: var(--ula-text-secondary); margin-bottom: 6px;">
                                Deep Space (Canvas Background)
                            </label>
                            <div style="display: flex; gap: 8px;">
                                <input type="color" name="color_deep_space" value="{{ $tokens['color_deep_space'] ?? '#071A16' }}" style="width: 40px; height: 38px; border: none; border-radius: 8px; cursor: pointer;">
                                <input type="text" value="{{ $tokens['color_deep_space'] ?? '#071A16' }}" class="form-input" style="flex: 1; font-family: monospace; font-weight: 700;">
                            </div>
                        </div>

                        <div>
                            <label style="display: block; font-size: 11px; font-weight: 800; color: var(--ula-text-secondary); margin-bottom: 6px;">
                                Dark Green (Cards & Panels)
                            </label>
                            <div style="display: flex; gap: 8px;">
                                <input type="color" name="color_dark_green" value="{{ $tokens['color_dark_green'] ?? '#0B2922' }}" style="width: 40px; height: 38px; border: none; border-radius: 8px; cursor: pointer;">
                                <input type="text" value="{{ $tokens['color_dark_green'] ?? '#0B2922' }}" class="form-input" style="flex: 1; font-family: monospace; font-weight: 700;">
                            </div>
                        </div>

                        <div>
                            <label style="display: block; font-size: 11px; font-weight: 800; color: var(--ula-text-secondary); margin-bottom: 6px;">
                                Emerald (Primary Accent & Brand)
                            </label>
                            <div style="display: flex; gap: 8px;">
                                <input type="color" name="color_emerald" value="{{ $tokens['color_emerald'] ?? '#13A879' }}" style="width: 40px; height: 38px; border: none; border-radius: 8px; cursor: pointer;">
                                <input type="text" value="{{ $tokens['color_emerald'] ?? '#13A879' }}" class="form-input" style="flex: 1; font-family: monospace; font-weight: 700;">
                            </div>
                        </div>

                        <div>
                            <label style="display: block; font-size: 11px; font-weight: 800; color: var(--ula-text-secondary); margin-bottom: 6px;">
                                Mint (Highlights & Active Waves)
                            </label>
                            <div style="display: flex; gap: 8px;">
                                <input type="color" name="color_mint" value="{{ $tokens['color_mint'] ?? '#6FE7C2' }}" style="width: 40px; height: 38px; border: none; border-radius: 8px; cursor: pointer;">
                                <input type="text" value="{{ $tokens['color_mint'] ?? '#6FE7C2' }}" class="form-input" style="flex: 1; font-family: monospace; font-weight: 700;">
                            </div>
                        </div>

                        <div>
                            <label style="display: block; font-size: 11px; font-weight: 800; color: var(--ula-text-secondary); margin-bottom: 6px;">
                                Soft Mint (Badges & Pills)
                            </label>
                            <div style="display: flex; gap: 8px;">
                                <input type="color" name="color_soft_mint" value="{{ $tokens['color_soft_mint'] ?? '#DDF8EF' }}" style="width: 40px; height: 38px; border: none; border-radius: 8px; cursor: pointer;">
                                <input type="text" value="{{ $tokens['color_soft_mint'] ?? '#DDF8EF' }}" class="form-input" style="flex: 1; font-family: monospace; font-weight: 700;">
                            </div>
                        </div>

                        <div>
                            <label style="display: block; font-size: 11px; font-weight: 800; color: var(--ula-text-secondary); margin-bottom: 6px;">
                                Text Light (Body Text)
                            </label>
                            <div style="display: flex; gap: 8px;">
                                <input type="color" name="color_text_light" value="{{ $tokens['color_text_light'] ?? '#F4FBF7' }}" style="width: 40px; height: 38px; border: none; border-radius: 8px; cursor: pointer;">
                                <input type="text" value="{{ $tokens['color_text_light'] ?? '#F4FBF7' }}" class="form-input" style="flex: 1; font-family: monospace; font-weight: 700;">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 2. Typography & Geometry -->
                <div class="panel-card" style="border-radius: var(--ula-radius-xl); padding: 24px;">
                    <div class="panel-header" style="margin-bottom: 20px;">
                        <div class="panel-title">
                            <span><span class="material-symbols-rounded" style="font-size: 1.15em; vertical-align: -0.2em;" aria-hidden="true">straighten</span> </span>
                            <span>{{ __('Typography & Corner Geometry') }}</span>
                        </div>
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                        <div>
                            <label style="display: block; font-size: 11px; font-weight: 800; color: var(--ula-text-secondary); margin-bottom: 4px;">
                                Latin Font Family (English)
                            </label>
                            <input type="text" name="font_family_latin" value="{{ $tokens['font_family_latin'] ?? "'Inter', sans-serif" }}" class="form-input" style="width: 100%; font-family: monospace;">
                        </div>

                        <div>
                            <label style="display: block; font-size: 11px; font-weight: 800; color: var(--ula-text-secondary); margin-bottom: 4px;">
                                Arabic Font Family (Arabic)
                            </label>
                            <input type="text" name="font_family_arabic" value="{{ $tokens['font_family_arabic'] ?? "'Cairo', sans-serif" }}" class="form-input" style="width: 100%; font-family: monospace;">
                        </div>

                        <div>
                            <label style="display: block; font-size: 11px; font-weight: 800; color: var(--ula-text-secondary); margin-bottom: 4px;">
                                Button Border Radius
                            </label>
                            <input type="text" name="radius_btn" value="{{ $tokens['radius_btn'] ?? '12px' }}" placeholder="12px" class="form-input" style="width: 100%;">
                        </div>

                        <div>
                            <label style="display: block; font-size: 11px; font-weight: 800; color: var(--ula-text-secondary); margin-bottom: 4px;">
                                Card Border Radius
                            </label>
                            <input type="text" name="radius_card" value="{{ $tokens['radius_card'] ?? '20px' }}" placeholder="20px" class="form-input" style="width: 100%;">
                        </div>
                    </div>
                </div>

                <!-- 3. Main Navigation Menu Editor -->
                <div class="panel-card" style="border-radius: var(--ula-radius-xl); padding: 24px;">
                    <div class="panel-header" style="margin-bottom: 20px;">
                        <div class="panel-title">
                            <span><span class="material-symbols-rounded" style="font-size: 1.15em; vertical-align: -0.2em;" aria-hidden="true">menu</span> </span>
                            <span>{{ __('Main Navigation Menu Links') }}</span>
                        </div>
                        <p class="panel-subtitle">{{ __('Customize top navigation links in Arabic and English.') }}</p>
                    </div>

                    @php
                        $currentNavItems = $navItems ?? \App\Domains\CMS\Models\CmsThemeSetting::getByKey('main_navigation', [
                            ['label_en' => 'Spaces', 'label_ar' => 'المساحات الذكية', 'url' => '#spaces'],
                            ['label_en' => 'Benefits', 'label_ar' => 'مميزات النظام', 'url' => '#benefits'],
                            ['label_en' => 'Meetings', 'label_ar' => 'الاجتماعات', 'url' => '#meetings'],
                            ['label_en' => 'Heritage', 'label_ar' => 'عن المنصة', 'url' => '#identity'],
                            ['label_en' => 'Pricing', 'label_ar' => 'الباقات والاشتراكات', 'url' => '#pricing'],
                        ]);
                    @endphp

                    <div id="nav-items-container" style="display: flex; flex-direction: column; gap: 12px; margin-bottom: 16px;">
                        @foreach($currentNavItems as $idx => $nav)
                            <div class="nav-item-row" style="display: grid; grid-template-columns: 1.2fr 1.2fr 1.5fr auto; gap: 12px; align-items: center; background: var(--ula-surface-page-alt); padding: 10px 14px; border-radius: 12px; border: 1px solid var(--ula-border-subtle);">
                                <div>
                                    <label style="display: block; font-size: 10px; font-weight: 800; color: var(--ula-text-muted); margin-bottom: 2px;">Label (EN)</label>
                                    <input type="text" name="nav_labels_en[]" value="{{ $nav['label_en'] ?? '' }}" class="form-input" style="width: 100%; font-size: 12px; padding: 6px 10px;">
                                </div>
                                <div>
                                    <label style="display: block; font-size: 10px; font-weight: 800; color: var(--ula-text-muted); margin-bottom: 2px;">Label (AR)</label>
                                    <input type="text" name="nav_labels_ar[]" value="{{ $nav['label_ar'] ?? '' }}" dir="rtl" class="form-input" style="width: 100%; font-size: 12px; padding: 6px 10px; font-family: 'Cairo', sans-serif;">
                                </div>
                                <div>
                                    <label style="display: block; font-size: 10px; font-weight: 800; color: var(--ula-text-muted); margin-bottom: 2px;"><span class="material-symbols-rounded" style="font-size: 1.15em; vertical-align: -0.2em;" aria-hidden="true">link</span> Target URL</label>
                                    <input type="text" name="nav_urls[]" value="{{ $nav['url'] ?? '' }}" class="form-input" style="width: 100%; font-size: 12px; padding: 6px 10px; font-family: monospace;">
                                </div>
                                <div style="padding-top: 14px;">
                                    <button type="button" onclick="this.closest('.nav-item-row').remove()" aria-label="{{ __('Delete') }}" class="tactile-btn" style="padding: 6px 10px; font-size: 11px; background: rgba(217, 107, 95, 0.15); color: #D96B5F; border-color: rgba(217, 107, 95, 0.3);">
                                        <span class="material-symbols-rounded" style="font-size: 18px;" aria-hidden="true">close</span>
                                    </button>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <button type="button" onclick="addNavRow()" class="tactile-btn btn-secondary" style="font-size: 12px; padding: 6px 14px;">
                        <span class="material-symbols-rounded" style="font-size: 1.15em; vertical-align: -0.2em;" aria-hidden="true">add</span> {{ __('Add Menu Link') }}
                    </button>
                </div>

                <div style="display: flex; justify-content: flex-end;">
                    <button type="submit" class="tactile-btn btn-primary" style="padding: 12px 36px; font-size: 14px;">
                        <span class="material-symbols-rounded" style="font-size: 1.15em; vertical-align: -0.2em;" aria-hidden="true">save</span> {{ __('Save & Propagate Theme & Navigation') }}
                    </button>
                </div>
            </div>

            <!-- Live Theme Preview Card -->
            <div class="panel-card" style="border-radius: var(--ula-radius-xl); padding: 24px; position: sticky; top: 90px; background: #071A16; color: white; border: 1px solid rgba(111, 231, 194, 0.3);">
                <div style="font-size: 12px; font-weight: 900; color: #6FE7C2; text-transform: uppercase; margin-bottom: 12px;">
                    <span class="material-symbols-rounded" style="font-size: 1.15em; vertical-align: -0.2em;" aria-hidden="true">visibility</span> {{ __('Live Theme Swatch Preview') }}
                </div>

                <div style="background: rgba(11, 41, 34, 0.8); border: 1px solid rgba(111, 231, 194, 0.25); border-radius: 16px; padding: 20px; margin-bottom: 16px;">
                    <span style="display: inline-block; padding: 4px 10px; border-radius: 9999px; background: rgba(19, 168, 121, 0.2); color: #6FE7C2; font-size: 10px; font-weight: 800; margin-bottom: 8px;">
                        PREVIEW BADGE
                    </span>
                    <h4 style="font-size: 16px; font-weight: 900; color: #FFFFFF; margin-bottom: 6px;">
                        Spatial Workplace SaaS
                    </h4>
                    <p style="font-size: 12px; color: #8BA69C; margin-bottom: 14px;">
                        This preview reflects your current Deep Space Green and Emerald theme tokens.
                    </p>
                    <button type="button" class="tactile-btn btn-primary" style="padding: 8px 16px; font-size: 12px;">
                        Sample Button
                    </button>
                </div>
            </div>
</div>
@endsection

@section('scripts')
<script nonce="{{ $cspNonce ?? '' }}">
    function addNavRow() {
        const container = document.getElementById('nav-items-container');
        const div = document.createElement('div');
        div.className = 'nav-item-row';
        div.style = 'display: grid; grid-template-columns: 1.2fr 1.2fr 1.5fr auto; gap: 12px; align-items: center; background: var(--ula-surface-page-alt); padding: 10px 14px; border-radius: 12px; border: 1px solid var(--ula-border-subtle);';
        div.innerHTML = `
            <div>
                <label style="display: block; font-size: 10px; font-weight: 800; color: var(--ula-text-muted); margin-bottom: 2px;">Label (EN)</label>
                <input type="text" name="nav_labels_en[]" placeholder="e.g. Features" class="form-input" style="width: 100%; font-size: 12px; padding: 6px 10px;">
            </div>
            <div>
                <label style="display: block; font-size: 10px; font-weight: 800; color: var(--ula-text-muted); margin-bottom: 2px;">Label (AR)</label>
                <input type="text" name="nav_labels_ar[]" placeholder="مثال: الميزات" dir="rtl" class="form-input" style="width: 100%; font-size: 12px; padding: 6px 10px; font-family: 'Cairo', sans-serif;">
            </div>
            <div>
                <label style="display: block; font-size: 10px; font-weight: 800; color: var(--ula-text-muted); margin-bottom: 2px;"><span class="material-symbols-rounded" style="font-size: 1.15em; vertical-align: -0.2em;" aria-hidden="true">link</span> Target URL</label>
                <input type="text" name="nav_urls[]" placeholder="#features or /url" class="form-input" style="width: 100%; font-size: 12px; padding: 6px 10px; font-family: monospace;">
            </div>
            <div style="padding-top: 14px;">
                <button type="button" onclick="this.closest('.nav-item-row').remove()" aria-label="{{ __('Delete') }}" class="tactile-btn" style="padding: 6px 10px; font-size: 11px; background: rgba(217, 107, 95, 0.15); color: #D96B5F; border-color: rgba(217, 107, 95, 0.3);">
                    <span class="material-symbols-rounded" style="font-size: 18px;" aria-hidden="true">close</span>
                </button>
            </div>
        `;
        container.appendChild(div);
    }
</script>
@endsection
