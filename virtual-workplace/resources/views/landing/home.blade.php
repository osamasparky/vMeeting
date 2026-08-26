@extends('landing.layout')

@php
    $heroSection = $sections['home_hero'] ?? null;
    $spatialSection = $sections['home_spatial_presence'] ?? null;
    $meetingsSection = $sections['home_meetings'] ?? null;
    $editorSection = $sections['home_floorplan_editor'] ?? null;
    $aiSection = $sections['home_ai_generator'] ?? null;
    $collabSection = $sections['home_collaboration'] ?? null;
    $workspaceSection = $sections['home_company_workspace'] ?? null;
    $pricingSection = $sections['home_pricing'] ?? null;
    $testimonialsSection = $sections['home_testimonials'] ?? null;
    $faqSection = $sections['home_faq'] ?? null;
    $ctaSection = $sections['home_cta'] ?? null;
@endphp

@section('title', $page->title ?? 'NextSpace — Spatial Virtual Workplace & Meetings')
@section('meta_description', $page->meta_desc ?? "Your team's space, anywhere. Spatial virtual office with proximity audio/video, AI office generator, and meetings.")

@section('styles')
<style>
    /* ── Hero Spatial Scene Styling ── */
    .ns-hero {
        position: relative;
        min-height: 90vh;
        display: flex;
        align-items: center;
        padding: 40px 32px 80px;
        overflow: hidden;
    }

    .ns-hero-bg-glow {
        position: absolute;
        top: -10%;
        left: 50%;
        transform: translateX(-50%);
        width: 1000px;
        height: 600px;
        background: radial-gradient(circle, rgba(19, 168, 121, 0.22) 0%, rgba(7, 26, 22, 0) 70%);
        filter: blur(80px);
        pointer-events: none;
        z-index: 1;
    }

    .ns-hero-grid {
        display: grid;
        grid-template-columns: 1fr 1.15fr;
        gap: 48px;
        align-items: center;
        position: relative;
        z-index: 2;
        max-width: 1400px;
        margin: 0 auto;
        width: 100%;
    }

    .ns-hero-content {
        max-width: 580px;
    }

    .ns-hero-title {
        font-size: clamp(34px, 4.2vw, 56px);
        font-weight: 900;
        line-height: 1.15;
        margin-bottom: 20px;
        letter-spacing: -1px;
        background: linear-gradient(135deg, #FFFFFF 20%, #DDF8EF 70%, #6FE7C2 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
    }

    .ns-hero-subtitle {
        font-size: clamp(16px, 1.8vw, 19px);
        color: var(--ns-text-muted, #8BA69C);
        line-height: 1.7;
        margin-bottom: 32px;
    }

    .ns-hero-ctas {
        display: flex;
        align-items: center;
        gap: 16px;
        flex-wrap: wrap;
        margin-bottom: 36px;
    }

    .ns-hero-pills {
        display: flex;
        flex-wrap: wrap;
        gap: 12px;
    }

    .ns-hero-pill {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 6px 14px;
        border-radius: 9999px;
        background: rgba(11, 41, 34, 0.6);
        border: 1px solid rgba(111, 231, 194, 0.18);
        color: var(--ns-text-light);
        font-size: 12px;
        font-weight: 700;
    }

    /* ── 3D Living Office Stage Container ── */
    .ns-canvas-wrapper {
        position: relative;
        width: 100%;
        height: 540px;
        border-radius: 28px;
        background: radial-gradient(circle at center, #0B2922 0%, #051410 100%);
        border: 1px solid rgba(111, 231, 194, 0.25);
        box-shadow: 0 24px 60px rgba(0, 0, 0, 0.65), 0 0 40px rgba(19, 168, 121, 0.18);
        overflow: hidden;
        perspective: 1200px;
    }

    .ns-office-stage {
        position: relative;
        width: 100%;
        height: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
        transform-style: preserve-3d;
        transition: transform 0.6s cubic-bezier(0.16, 1, 0.3, 1);
    }

    .ns-floorplan-img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        display: block;
        transition: all 0.6s cubic-bezier(0.16, 1, 0.3, 1);
        filter: brightness(0.92) contrast(1.08) saturate(1.15);
    }

    /* ── Interactive Hotspots & Pins ── */
    .ns-hotspot-zone {
        position: absolute;
        border: 2px dashed rgba(111, 231, 194, 0.35);
        border-radius: 16px;
        background: rgba(19, 168, 121, 0.08);
        cursor: pointer;
        transition: all 0.3s ease;
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 5;
    }

    .ns-hotspot-zone:hover, .ns-hotspot-zone.focused {
        border-color: var(--ns-mint, #6FE7C2);
        background: rgba(19, 168, 121, 0.22);
        box-shadow: 0 0 30px rgba(111, 231, 194, 0.35), inset 0 0 20px rgba(19, 168, 121, 0.2);
    }

    .ns-hotspot-pin {
        position: relative;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .ns-pin-icon {
        width: 38px;
        height: 38px;
        border-radius: 12px;
        background: rgba(7, 26, 22, 0.9);
        backdrop-filter: blur(12px);
        border: 1px solid var(--ns-mint, #6FE7C2);
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 18px;
        box-shadow: 0 6px 18px rgba(0, 0, 0, 0.4), 0 0 15px rgba(19, 168, 121, 0.4);
        transition: all 0.2s ease;
    }

    .ns-hotspot-zone:hover .ns-pin-icon {
        transform: scale(1.15);
        background: var(--ns-gradient-emerald);
    }

    .ns-pin-tooltip {
        position: absolute;
        bottom: 120%;
        left: 50%;
        transform: translateX(-50%);
        background: rgba(7, 26, 22, 0.95);
        backdrop-filter: blur(16px);
        border: 1px solid rgba(111, 231, 194, 0.35);
        border-radius: 10px;
        padding: 6px 12px;
        white-space: nowrap;
        pointer-events: none;
        opacity: 0;
        transition: all 0.2s ease;
        box-shadow: 0 8px 24px rgba(0, 0, 0, 0.6);
        text-align: center;
        z-index: 10;
    }

    .ns-pin-tooltip strong {
        display: block;
        font-size: 11px;
        color: var(--ns-mint);
    }

    .ns-pin-tooltip span {
        display: block;
        font-size: 10px;
        color: var(--ns-text-muted);
    }

    .ns-hotspot-zone:hover .ns-pin-tooltip {
        opacity: 1;
        bottom: 130%;
    }

    /* ── Animated Avatars on Stage ── */
    .ns-avatar-agent {
        position: absolute;
        width: 36px;
        height: 36px;
        z-index: 6;
        pointer-events: none;
        transition: all 0.8s cubic-bezier(0.16, 1, 0.3, 1);
    }

    .ns-avatar-bubble {
        width: 36px;
        height: 36px;
        border-radius: 50%;
        background: var(--ns-gradient-emerald);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 18px;
        border: 2px solid #FFFFFF;
        box-shadow: 0 4px 14px rgba(0, 0, 0, 0.5), 0 0 16px rgba(19, 168, 121, 0.6);
    }

    .ns-avatar-radar {
        position: absolute;
        inset: -8px;
        border-radius: 50%;
        border: 2px solid var(--ns-mint);
        animation: radarPulse 2s cubic-bezier(0.2, 1, 0.3, 1) infinite;
    }

    @keyframes radarPulse {
        0% { transform: scale(0.6); opacity: 1; }
        100% { transform: scale(2); opacity: 0; }
    }

    /* ── Floating Interactive Overlay in 3D ── */
    .ns-3d-overlay {
        position: absolute;
        bottom: 20px;
        left: 20px;
        right: 20px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        pointer-events: none;
        gap: 12px;
    }

    .ns-room-chips {
        display: flex;
        gap: 8px;
        overflow-x: auto;
        scrollbar-width: none;
        pointer-events: auto;
    }

    .ns-room-chip {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 7px 14px;
        border-radius: 12px;
        background: rgba(7, 26, 22, 0.85);
        backdrop-filter: blur(16px);
        border: 1px solid rgba(111, 231, 194, 0.25);
        color: var(--ns-white);
        font-size: 12px;
        font-weight: 800;
        cursor: pointer;
        transition: all 0.2s ease;
        white-space: nowrap;
    }

    .ns-room-chip:hover, .ns-room-chip.active {
        background: var(--ns-emerald, #13A879);
        color: #071A16;
        border-color: var(--ns-mint);
        transform: translateY(-2px);
        box-shadow: 0 4px 14px rgba(19, 168, 121, 0.4);
    }

    /* ── Live Proximity Video Bubble Simulation ── */
    .ns-proximity-bubble {
        position: absolute;
        top: 35px;
        right: 35px;
        background: rgba(7, 26, 22, 0.9);
        backdrop-filter: blur(20px);
        border: 1px solid rgba(111, 231, 194, 0.4);
        border-radius: 18px;
        padding: 12px 16px;
        display: flex;
        align-items: center;
        gap: 12px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.5), 0 0 20px rgba(19, 168, 121, 0.3);
        animation: bubbleFloat 3s ease-in-out infinite alternate;
        pointer-events: none;
    }

    @keyframes bubbleFloat {
        from { transform: translateY(0); }
        to { transform: translateY(-6px); }
    }

    .ns-wave-ring {
        position: relative;
        width: 36px;
        height: 36px;
        border-radius: 50%;
        background: var(--ns-gradient-emerald);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 16px;
    }

    .ns-wave-ring::before {
        content: '';
        position: absolute;
        inset: -6px;
        border-radius: 50%;
        border: 2px solid var(--ns-mint);
        animation: wavePulse 1.5s cubic-bezier(0.2, 1, 0.3, 1) infinite;
    }

    @keyframes wavePulse {
        0% { transform: scale(0.8); opacity: 1; }
        100% { transform: scale(1.6); opacity: 0; }
    }

    /* ── Proximity Slider Demo ── */
    .ns-slider-track {
        width: 100%;
        height: 8px;
        border-radius: 9999px;
        background: rgba(111, 231, 194, 0.15);
        outline: none;
        -webkit-appearance: none;
    }

    .ns-slider-track::-webkit-slider-thumb {
        -webkit-appearance: none;
        width: 24px;
        height: 24px;
        border-radius: 50%;
        background: var(--ns-mint, #6FE7C2);
        cursor: pointer;
        box-shadow: 0 0 16px rgba(111, 231, 194, 0.8);
    }

    /* ── Pricing Switcher ── */
    .ns-currency-tabs {
        display: inline-flex;
        background: rgba(11, 41, 34, 0.8);
        border: 1px solid rgba(111, 231, 194, 0.2);
        padding: 4px;
        border-radius: 12px;
        gap: 4px;
        margin-bottom: 24px;
    }

    .ns-currency-tab {
        padding: 6px 14px;
        border-radius: 8px;
        background: transparent;
        border: none;
        color: var(--ns-text-muted);
        font-size: 13px;
        font-weight: 800;
        cursor: pointer;
        transition: all 0.2s;
    }

    .ns-currency-tab.active {
        background: var(--ns-emerald);
        color: #071A16;
    }

    @media (max-width: 1024px) {
        .ns-hero-grid { grid-template-columns: 1fr; }
        .ns-canvas-wrapper { height: 400px; }
    }
</style>
@endsection

@section('content')

<!-- ══════════════════════════════════════════════════════════════════════════
     1. HERO SECTION: LIVING 3D VIRTUAL OFFICE
     ══════════════════════════════════════════════════════════════════════════ -->
<section class="ns-hero" id="hero-spatial">
    <div class="ns-hero-bg-glow"></div>

    <div class="ns-hero-grid">
        <!-- Text & CTAs -->
        <div class="ns-hero-content">
            <div class="ns-badge">
                {{ $heroSection->badge ?? '✨ The Spatial Computing Workplace' }}
            </div>

            <h1 class="ns-hero-title">
                {{ $heroSection->title ?? "Your team's space, anywhere." }}
            </h1>

            <p class="ns-hero-subtitle">
                {{ $heroSection->subtitle ?? 'Create a virtual workplace where teams meet naturally, collaborate in real-time, and stay deeply connected — without meeting fatigue.' }}
            </p>

            <div class="ns-hero-ctas">
                <a href="{{ route('register') }}" class="ns-btn ns-btn-primary" style="padding: 14px 30px; font-size: 15px;">
                    <span>🚀</span>
                    <span>{{ $heroSection->content['cta_primary_text_' . app()->getLocale()] ?? __('Start Free Workplace') }}</span>
                </a>
                <a href="#spatial-presence" class="ns-btn ns-btn-secondary" style="padding: 14px 26px; font-size: 15px;">
                    <span>👇</span>
                    <span>{{ $heroSection->content['cta_secondary_text_' . app()->getLocale()] ?? __('Explore Features') }}</span>
                </a>
            </div>

            <!-- Value Highlights Pills -->
            <div class="ns-hero-pills">
                @php
                    $highlights = $heroSection->content['highlights'] ?? [
                        ['icon' => '🎧', 'text_en' => 'Proximity Audio/Video', 'text_ar' => 'صوت وفيديو مكاني تفاعلي'],
                        ['icon' => '🤖', 'text_en' => 'AI Blueprint Studio', 'text_ar' => 'توليد المكاتب بالذكاء الاصطناعي'],
                        ['icon' => '🔒', 'text_en' => 'Acoustic Sound Isolation', 'text_ar' => 'مناطق عزل صوتي للغرف'],
                    ];
                @endphp
                @foreach($highlights as $hl)
                    <div class="ns-hero-pill">
                        <span>{{ $hl['icon'] }}</span>
                        <span>{{ $hl['text_' . app()->getLocale()] ?? ($hl['text_en'] ?? '') }}</span>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Interactive Living Office Spatial Showcase Stage -->
        <div class="ns-canvas-wrapper" id="office-preview">
            @php
                $heroImageUrl = ($heroSection && $heroSection->mediaAsset) ? $heroSection->mediaAsset->url : asset('images/office_floorplan.jpg');
            @endphp

            <!-- Blueprint Image Display Stage with 3D Tilt -->
            <div class="ns-office-stage" id="heroOfficeStage">
                <img src="{{ $heroImageUrl }}" alt="NextSpace Spatial Living Virtual Office" id="heroFloorplanImg" class="ns-floorplan-img">

                <!-- Room Hotspot Overlays & Glowing Pins -->
                <!-- 1. Meeting Room Zone -->
                <div class="ns-hotspot-zone" id="zone-meeting" style="top: 12%; left: 14%; width: 34%; height: 38%;" onclick="focus3dRoom('meeting')">
                    <div class="ns-hotspot-pin">
                        <span class="ns-pin-icon">📹</span>
                        <div class="ns-pin-tooltip">
                            <strong>{{ __('Executive Meeting Room') }}</strong>
                            <span>{{ __('Soundproof • 4K Screen Sharing Active') }}</span>
                        </div>
                    </div>
                </div>

                <!-- 2. Open Workspace Desks Zone -->
                <div class="ns-hotspot-zone" id="zone-workspace" style="top: 48%; left: 18%; width: 42%; height: 42%;" onclick="focus3dRoom('workspace')">
                    <div class="ns-hotspot-pin">
                        <span class="ns-pin-icon">💼</span>
                        <div class="ns-pin-tooltip">
                            <strong>{{ __('Open Team Desks') }}</strong>
                            <span>{{ __('8 Members Online • Proximity Voice') }}</span>
                        </div>
                    </div>
                </div>

                <!-- 3. Lounge & Coffee Zone -->
                <div class="ns-hotspot-zone" id="zone-lounge" style="top: 15%; right: 14%; width: 32%; height: 35%;" onclick="focus3dRoom('lounge')">
                    <div class="ns-hotspot-pin">
                        <span class="ns-pin-icon">☕</span>
                        <div class="ns-pin-tooltip">
                            <strong>{{ __('Team Lounge & Coffee') }}</strong>
                            <span>{{ __('Casual Chats & Water-cooler Area') }}</span>
                        </div>
                    </div>
                </div>

                <!-- 4. Focus Booth Zone -->
                <div class="ns-hotspot-zone" id="zone-focus" style="bottom: 12%; right: 14%; width: 28%; height: 30%;" onclick="focus3dRoom('focus')">
                    <div class="ns-hotspot-pin">
                        <span class="ns-pin-icon">🎧</span>
                        <div class="ns-pin-tooltip">
                            <strong>{{ __('Private Focus Pod') }}</strong>
                            <span>{{ __('Acoustic DND Pod • Zero Noise') }}</span>
                        </div>
                    </div>
                </div>

                <!-- Animated Walking Avatars on Floorplan -->
                <div class="ns-avatar-agent" id="agent-omar" style="top: 52%; left: 34%;">
                    <div class="ns-avatar-bubble">👨‍💻</div>
                    <span class="ns-avatar-radar"></span>
                </div>
                <div class="ns-avatar-agent" id="agent-sarah" style="top: 54%; left: 45%;">
                    <div class="ns-avatar-bubble">👩‍💼</div>
                    <span class="ns-avatar-radar"></span>
                </div>
            </div>

            <!-- Dynamic Proximity Video Bubble Simulation -->
            <div class="ns-proximity-bubble" id="hero-proximity-indicator">
                <div class="ns-wave-ring">🎧</div>
                <div>
                    <strong style="font-size: 12px; color: var(--ns-mint); display: block;">⚡ {{ __('Proximity Active (Connected)') }}</strong>
                    <span style="font-size: 11px; color: var(--ns-text-light);">{{ __('Sarah & Omar talking in Open Office') }}</span>
                </div>
            </div>

            <!-- 3D Room Highlight Chips -->
            <div class="ns-3d-overlay">
                <div class="ns-room-chips">
                    <button type="button" class="ns-room-chip active" onclick="focus3dRoom('all')">
                        <span>🌐</span> {{ __('Full Office') }}
                    </button>
                    <button type="button" class="ns-room-chip" onclick="focus3dRoom('meeting')">
                        <span>📹</span> {{ __('Meeting Room') }}
                    </button>
                    <button type="button" class="ns-room-chip" onclick="focus3dRoom('workspace')">
                        <span>💼</span> {{ __('Desks') }}
                    </button>
                    <button type="button" class="ns-room-chip" onclick="focus3dRoom('lounge')">
                        <span>☕</span> {{ __('Lounge') }}
                    </button>
                    <button type="button" class="ns-room-chip" onclick="focus3dRoom('focus')">
                        <span>🎧</span> {{ __('Focus Pod') }}
                    </button>
                </div>
            </div>
        </div>
    </div>
</section>


<!-- ══════════════════════════════════════════════════════════════════════════
     2. SPATIAL PRESENCE: PROXIMITY AUDIO & VIDEO
     ══════════════════════════════════════════════════════════════════════════ -->
<section class="ns-section" id="spatial-presence">
    <div class="ns-container">
        <div class="ns-section-header">
            <div class="ns-badge">
                {{ $spatialSection->badge ?? '🔊 Spatial Proximity Technology' }}
            </div>
            <h2 class="ns-section-title">
                {{ $spatialSection->title ?? 'Conversations that happen naturally.' }}
            </h2>
            <p class="ns-section-subtitle">
                {{ $spatialSection->subtitle ?? 'Move freely across the spatial 2D office. Walk up to a teammate to automatically connect via HD video and spatial audio — exactly like a real office.' }}
            </p>
        </div>

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 24px;">
            <!-- Interactive Proximity Distance Simulator Card -->
            <div class="ns-card" style="grid-column: 1 / -1; background: linear-gradient(135deg, rgba(11, 41, 34, 0.85) 0%, rgba(7, 26, 22, 0.95) 100%);">
                <div style="display: grid; grid-template-columns: 1.2fr 1fr; gap: 32px; align-items: center;">
                    <div>
                        <div class="ns-badge" style="background: rgba(111, 231, 194, 0.15); margin-bottom: 12px;">
                            🧪 {{ __('Interactive Proximity Simulator') }}
                        </div>
                        <h3 style="font-size: 22px; font-weight: 900; margin-bottom: 12px;">
                            {{ __('Drag the slider to test acoustic distance attenuation') }}
                        </h3>
                        <p style="font-size: 14px; color: var(--ns-text-muted); margin-bottom: 24px;">
                            {{ __('Notice how voice clarity, camera feed, and sound isolation dynamically adapt as you walk closer to a colleague in the open office.') }}
                        </p>

                        <!-- Distance Slider Control -->
                        <div style="margin-bottom: 16px;">
                            <div style="display: flex; justify-content: space-between; font-size: 13px; font-weight: 800; margin-bottom: 8px;">
                                <span>{{ __('Distance to Colleague') }}:</span>
                                <span id="proximity-distance-val" style="color: var(--ns-mint);">3 {{ __('Meters (Connected)') }}</span>
                            </div>
                            <input type="range" min="1" max="25" value="3" class="ns-slider-track" id="proximity-slider" oninput="updateProximityDemo(this.value)">
                        </div>
                    </div>

                    <!-- Visual Distance & Sound Reaction Box -->
                    <div style="background: rgba(7, 26, 22, 0.8); border: 1px solid rgba(111, 231, 194, 0.25); border-radius: 20px; padding: 24px; text-align: center;">
                        <div style="display: flex; justify-content: center; align-items: center; gap: 24px; margin-bottom: 20px;">
                            <!-- Avatar 1 -->
                            <div style="text-align: center;">
                                <div style="width: 56px; height: 56px; border-radius: 18px; background: var(--ns-gradient-emerald); display: flex; align-items: center; justify-content: center; font-size: 24px; box-shadow: 0 0 20px rgba(19, 168, 121, 0.4);">
                                    👨‍💻
                                </div>
                                <span style="font-size: 11px; font-weight: 800; color: var(--ns-mint); margin-top: 6px; display: block;">You (Eng)</span>
                            </div>

                            <!-- Animated Audio Waves Indicator -->
                            <div id="demo-audio-waves" style="display: flex; gap: 4px; align-items: center; height: 32px;">
                                <span style="width: 4px; height: 28px; background: var(--ns-mint); border-radius: 4px; animation: eqWave 0.6s infinite alternate;"></span>
                                <span style="width: 4px; height: 18px; background: var(--ns-mint); border-radius: 4px; animation: eqWave 0.8s infinite alternate;"></span>
                                <span style="width: 4px; height: 32px; background: var(--ns-mint); border-radius: 4px; animation: eqWave 0.5s infinite alternate;"></span>
                                <span style="width: 4px; height: 22px; background: var(--ns-mint); border-radius: 4px; animation: eqWave 0.7s infinite alternate;"></span>
                            </div>

                            <!-- Avatar 2 -->
                            <div style="text-align: center;">
                                <div style="width: 56px; height: 56px; border-radius: 18px; background: linear-gradient(135deg, #3B82F6 0%, #10B981 100%); display: flex; align-items: center; justify-content: center; font-size: 24px; box-shadow: 0 0 20px rgba(59, 130, 246, 0.4);">
                                    👩‍🎨
                                </div>
                                <span style="font-size: 11px; font-weight: 800; color: #3B82F6; margin-top: 6px; display: block;">Nora (Design)</span>
                            </div>
                        </div>

                        <!-- Status Badge -->
                        <div id="demo-connection-status" style="display: inline-block; padding: 8px 18px; border-radius: 9999px; font-size: 13px; font-weight: 800; background: rgba(16, 185, 129, 0.2); color: #10B981; border: 1px solid rgba(16, 185, 129, 0.4);">
                            🟢 {{ __('HD Video & Spatial Audio Active (100% Volume)') }}
                        </div>
                    </div>
                </div>
            </div>

            <!-- Spatial Features Grid -->
            @php
                $spatialFeatures = $spatialSection->content['features'] ?? [
                    ['icon' => '🚶‍♂️', 'title_en' => 'Proximity Audio Attenuation', 'title_ar' => 'تدرج الصوت حسب المسافة', 'desc_en' => 'Voices fade in as you approach and fade out as you walk away.', 'desc_ar' => 'يعلو الصوت كلما اقتربت وينخفض بالابتعاد.'],
                    ['icon' => '🚪', 'title_en' => 'Sound Isolation Zones', 'title_ar' => 'مناطق العزل الصوتي', 'desc_en' => 'Closed meeting rooms isolate sound completely from open spaces.', 'desc_ar' => 'غرف معزولة تماماً تمنع تسرب الصوت للخارج.'],
                    ['icon' => '✊', 'title_en' => 'Door Knocking & Privacy', 'title_ar' => 'طرق الباب والاستئذان', 'desc_en' => 'Lock private offices and request entry before joining.', 'desc_ar' => 'إمكانية قفل الغرف وطلب الإذن قبل الدخول.'],
                ];
            @endphp
            @foreach($spatialFeatures as $sf)
                <div class="ns-card">
                    <div style="font-size: 32px; margin-bottom: 16px;">{{ $sf['icon'] }}</div>
                    <h3 style="font-size: 18px; font-weight: 800; color: var(--ns-white); margin-bottom: 8px;">
                        {{ $sf['title_' . app()->getLocale()] ?? ($sf['title_en'] ?? '') }}
                    </h3>
                    <p style="font-size: 13px; color: var(--ns-text-muted); line-height: 1.6;">
                        {{ $sf['desc_' . app()->getLocale()] ?? ($sf['desc_en'] ?? '') }}
                    </p>
                </div>
            @endforeach
        </div>
    </div>
</section>


<!-- ══════════════════════════════════════════════════════════════════════════
     3. AI OFFICE GENERATOR SHOWCASE
     ══════════════════════════════════════════════════════════════════════════ -->
<section class="ns-section" id="ai-generator" style="background: radial-gradient(circle at 50% 30%, rgba(19, 168, 121, 0.08) 0%, transparent 70%);">
    <div class="ns-container">
        <div class="ns-section-header">
            <div class="ns-badge">
                {{ $aiSection->badge ?? '🤖 AI Office Blueprint Engine' }}
            </div>
            <h2 class="ns-section-title">
                {{ $aiSection->title ?? 'Describe your office. Let AI build it.' }}
            </h2>
            <p class="ns-section-subtitle">
                {{ $aiSection->subtitle ?? 'Turn simple text prompts into production-ready 2D architectural blueprints. Powered by GPT Image 1 Mini and DALL-E with token compression for 95% cost savings.' }}
            </p>
        </div>

        <div class="ns-card" style="background: linear-gradient(135deg, rgba(11, 41, 34, 0.9) 0%, rgba(7, 26, 22, 0.95) 100%);">
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 36px; align-items: center;">
                <div>
                    <div style="font-size: 13px; font-weight: 800; color: var(--ns-mint); text-transform: uppercase; margin-bottom: 8px;">
                        💬 {{ __('AI Architectural Prompt') }}
                    </div>
                    <div style="background: rgba(7, 26, 22, 0.9); border: 1px solid rgba(111, 231, 194, 0.3); border-radius: 16px; padding: 18px; margin-bottom: 20px; font-family: monospace; font-size: 13px; color: var(--ns-text-light); line-height: 1.6;">
                        "{{ app()->getLocale() === 'ar' ? ($aiSection->content['prompt_example_ar'] ?? 'مكتب شركة تقنية حديث لـ 25 موظفاً مع غرفتي اجتماعات، 4 كبائن تركيز، مساحة استراحة مفتوحة وركن قهوة.') : ($aiSection->content['prompt_example'] ?? 'Modern tech company office for 25 engineers with 2 conference rooms, 4 focus booths, open lounge, and coffee bar.') }}"
                    </div>

                    <div style="display: flex; gap: 12px; flex-wrap: wrap; margin-bottom: 24px;">
                        <span class="ns-badge" style="margin-bottom: 0;">
                            💰 {{ $aiSection->content['cost_badge_' . app()->getLocale()] ?? ($aiSection->content['cost_badge'] ?? '~$0.015 per generated floorplan') }}
                        </span>
                        <span class="ns-badge" style="background: rgba(59, 130, 246, 0.15); border-color: rgba(59, 130, 246, 0.3); color: #3B82F6; margin-bottom: 0;">
                            ⚡ ~60 Tokens Compressed
                        </span>
                    </div>

                    <a href="{{ route('register') }}" class="ns-btn ns-btn-primary">
                        <span>✨</span>
                        <span>{{ __('Build My Office with AI') }}</span>
                    </a>
                </div>

                <!-- Interactive Before/After Transformation Visual -->
                <div style="position: relative; border-radius: 20px; overflow: hidden; border: 2px solid rgba(111, 231, 194, 0.3); box-shadow: 0 16px 40px rgba(0, 0, 0, 0.6);">
                    <img src="/images/office_floorplan.jpg" alt="AI Generated Office" style="width: 100%; height: auto; display: block;">
                    <div style="position: absolute; bottom: 12px; right: 12px; background: rgba(7, 26, 22, 0.85); backdrop-filter: blur(10px); padding: 6px 12px; border-radius: 8px; font-size: 11px; font-weight: 800; color: var(--ns-mint); border: 1px solid rgba(111, 231, 194, 0.3);">
                        ⚡ AI Synthesized 2D Blueprint
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>


<!-- ══════════════════════════════════════════════════════════════════════════
     4. PRODUCTIVITY & COLLABORATION SUITE
     ══════════════════════════════════════════════════════════════════════════ -->
<section class="ns-section" id="collaboration">
    <div class="ns-container">
        <div class="ns-section-header">
            <div class="ns-badge">
                {{ $collabSection->badge ?? '🚀 Unified Productivity Suite' }}
            </div>
            <h2 class="ns-section-title">
                {{ $collabSection->title ?? 'Everything your team needs. In one unified space.' }}
            </h2>
            <p class="ns-section-subtitle">
                {{ $collabSection->subtitle ?? 'Eliminate app switching. NextSpace combines collaborative whiteboards, Kanban workflow boards, time tracking logs, and secure document storage.' }}
            </p>
        </div>

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 24px;">
            @php
                $modules = $collabSection->content['modules'] ?? [
                    ['icon' => '🎨', 'title_en' => 'Interactive Whiteboard', 'title_ar' => 'اللوح الأبيض التشاركي', 'desc_en' => 'Real-time drawing, diagrams, and sticky notes for interactive brainstorming.', 'desc_ar' => 'رسم تشاركي وعصف ذهني مباشر.'],
                    ['icon' => '📋', 'title_en' => 'Kanban Workflow Boards', 'title_ar' => 'لوحات المهام والكانبان', 'desc_en' => 'Organize tasks into To-Do, In-Progress, and Done with assigned owners.', 'desc_ar' => 'تنظيم المشروعات وتوزيع المهام بالسحب والإفلات.'],
                    ['icon' => '⏱️', 'title_en' => 'Time & Attendance Logs', 'title_ar' => 'تتبع ساعات العمل والحضور', 'desc_en' => 'Automatic desk presence logs and exportable weekly timesheets.', 'desc_ar' => 'تسجيل الحضور وإحصائيات ساعات العمل.'],
                    ['icon' => '📁', 'title_en' => 'Secure File & Asset Hub', 'title_ar' => 'مكتبة الملفات والمستندات', 'desc_en' => 'Centralized file repository for company guides and roadmaps.', 'desc_ar' => 'مستودع سحابي لمشاركة ملفات المنظمة.'],
                ];
            @endphp
            @foreach($modules as $mod)
                <div class="ns-card">
                    <div style="width: 52px; height: 52px; border-radius: 16px; background: rgba(19, 168, 121, 0.15); border: 1px solid rgba(111, 231, 194, 0.25); display: flex; align-items: center; justify-content: center; font-size: 26px; margin-bottom: 20px;">
                        {{ $mod['icon'] }}
                    </div>
                    <h3 style="font-size: 18px; font-weight: 800; color: var(--ns-white); margin-bottom: 8px;">
                        {{ $mod['title_' . app()->getLocale()] ?? ($mod['title_en'] ?? '') }}
                    </h3>
                    <p style="font-size: 13px; color: var(--ns-text-muted); line-height: 1.6;">
                        {{ $mod['desc_' . app()->getLocale()] ?? ($mod['desc_en'] ?? '') }}
                    </p>
                </div>
            @endforeach
        </div>
    </div>
</section>


<!-- ══════════════════════════════════════════════════════════════════════════
     5. DYNAMIC MULTI-CURRENCY PRICING
     ══════════════════════════════════════════════════════════════════════════ -->
<section class="ns-section" id="pricing" style="background: radial-gradient(circle at 50% 50%, rgba(11, 41, 34, 0.7) 0%, transparent 80%);">
    <div class="ns-container">
        <div class="ns-section-header">
            <div class="ns-badge">
                {{ $pricingSection->badge ?? '💎 Plans & Subscriptions' }}
            </div>
            <h2 class="ns-section-title">
                {{ $pricingSection->title ?? 'Transparent pricing for teams of all sizes.' }}
            </h2>
            <p class="ns-section-subtitle">
                {{ $pricingSection->subtitle ?? 'Start free and scale as your workplace grows. Multi-currency checkout in SAR, EGP, AED, and USD with instant activation.' }}
            </p>

            <!-- Currency Tabs Switcher -->
            <div style="margin-top: 24px;">
                <div class="ns-currency-tabs">
                    <button type="button" class="ns-currency-tab active" onclick="switchCurrency('SAR', this)">🇸🇦 SAR (ر.س)</button>
                    <button type="button" class="ns-currency-tab" onclick="switchCurrency('EGP', this)">🇪🇬 EGP (ج.م)</button>
                    <button type="button" class="ns-currency-tab" onclick="switchCurrency('AED', this)">🇦🇪 AED (د.إ)</button>
                    <button type="button" class="ns-currency-tab" onclick="switchCurrency('USD', this)">🇺🇸 USD ($)</button>
                </div>
            </div>
        </div>

        <!-- Pricing Cards Grid -->
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 24px; align-items: stretch;">
            @foreach($plans as $plan)
                <div class="ns-card" style="display: flex; flex-direction: column; justify-content: space-between; {{ $plan->name === 'Business' || $plan->name === 'Pro' ? 'border-color: var(--ns-mint); box-shadow: 0 0 30px rgba(19, 168, 121, 0.3);' : '' }}">
                    @if($plan->name === 'Business' || $plan->name === 'Pro')
                        <div style="position: absolute; top: 16px; right: 16px; background: var(--ns-gradient-emerald); color: #071A16; font-size: 10px; font-weight: 900; padding: 3px 10px; border-radius: 9999px; text-transform: uppercase;">
                            POPULAR
                        </div>
                    @endif

                    <div>
                        <h3 style="font-size: 20px; font-weight: 900; color: var(--ns-white); margin-bottom: 6px;">
                            💎 {{ $plan->name }}
                        </h3>
                        <p style="font-size: 12px; color: var(--ns-text-muted); margin-bottom: 20px;">
                            {{ $plan->seat_limit === 0 ? __('Unlimited team members') : __('Up to :num seats', ['num' => $plan->seat_limit]) }}
                        </p>

                        <!-- Price Tag -->
                        <div style="margin-bottom: 24px;">
                            <span class="plan-price-val" data-base-usd="{{ $plan->price }}" style="font-size: 36px; font-weight: 900; color: var(--ns-white);">
                                {{ round($plan->price * ($rates['SAR'] ?? 3.75), 0) }}
                            </span>
                            <span class="plan-currency-symbol" style="font-size: 14px; font-weight: 800; color: var(--ns-mint);">
                                SAR
                            </span>
                            <span style="font-size: 12px; color: var(--ns-text-muted);">/ {{ __('month') }}</span>
                        </div>

                        <!-- Feature List -->
                        <ul style="list-style: none; display: flex; flex-direction: column; gap: 10px; font-size: 13px; color: var(--ns-text-light); margin-bottom: 30px;">
                            <li style="display: flex; align-items: center; gap: 8px;">
                                <span style="color: var(--ns-mint);">✓</span>
                                <span>{{ $plan->seat_limit === 0 ? __('Unlimited team seats') : $plan->seat_limit . ' ' . __('Team seats included') }}</span>
                            </li>
                            <li style="display: flex; align-items: center; gap: 8px;">
                                <span style="color: var(--ns-mint);">✓</span>
                                <span>{{ $plan->room_limit === 0 ? __('Unlimited soundproof rooms') : $plan->room_limit . ' ' . __('Acoustic meeting rooms') }}</span>
                            </li>
                            <li style="display: flex; align-items: center; gap: 8px;">
                                <span style="color: var(--ns-mint);">✓</span>
                                <span>{{ __('Spatial 2D Office & Proximity Audio') }}</span>
                            </li>
                            <li style="display: flex; align-items: center; gap: 8px;">
                                <span style="color: var(--ns-mint);">✓</span>
                                <span>{{ __('AI Office Blueprint Generator') }}</span>
                            </li>
                            <li style="display: flex; align-items: center; gap: 8px;">
                                <span style="color: var(--ns-mint);">✓</span>
                                <span>{{ __('Whiteboard & Kanban Suite') }}</span>
                            </li>
                        </ul>
                    </div>

                    <a href="{{ route('register') }}" class="ns-btn {{ $plan->name === 'Business' || $plan->name === 'Pro' ? 'ns-btn-primary' : 'ns-btn-secondary' }}" style="width: 100%; text-align: center;">
                        {{ $plan->price == 0 ? __('Get Started Free') : __('Choose Plan') }}
                    </a>
                </div>
            @endforeach
        </div>
    </div>
</section>


<!-- ══════════════════════════════════════════════════════════════════════════
     6. CUSTOMER TESTIMONIALS & REVIEWS
     ══════════════════════════════════════════════════════════════════════════ -->
<section class="ns-section" id="testimonials">
    <div class="ns-container">
        <div class="ns-section-header">
            <div class="ns-badge">
                {{ $testimonialsSection->badge ?? '⭐ Customer Stories' }}
            </div>
            <h2 class="ns-section-title">
                {{ $testimonialsSection->title ?? 'Loved by high-performing distributed teams.' }}
            </h2>
            <p class="ns-section-subtitle">
                {{ $testimonialsSection->subtitle ?? 'See how leading remote companies use NextSpace to boost team happiness and accelerate execution.' }}
            </p>
        </div>

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 24px;">
            @php
                $testimonials = $testimonialsSection->content['testimonials'] ?? [];
            @endphp
            @foreach($testimonials as $t)
                <div class="ns-card" style="display: flex; flex-direction: column; justify-content: space-between;">
                    <div style="font-size: 14px; color: var(--ns-text-light); line-height: 1.8; margin-bottom: 24px;">
                        "{{ $t['quote_' . app()->getLocale()] ?? ($t['quote_en'] ?? '') }}"
                    </div>
                    <div style="display: flex; align-items: center; gap: 14px; border-top: 1px solid rgba(111, 231, 194, 0.15); padding-top: 16px;">
                        <div style="font-size: 28px;">{{ $t['avatar'] ?? '👤' }}</div>
                        <div>
                            <strong style="font-size: 14px; color: var(--ns-white); display: block;">{{ $t['name'] }}</strong>
                            <span style="font-size: 12px; color: var(--ns-text-muted);">{{ $t['role_' . app()->getLocale()] ?? ($t['role_en'] ?? '') }}</span>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>


<!-- ══════════════════════════════════════════════════════════════════════════
     7. FREQUENTLY ASKED QUESTIONS (FAQ)
     ══════════════════════════════════════════════════════════════════════════ -->
<section class="ns-section" id="faq">
    <div class="ns-container" style="max-width: 900px;">
        <div class="ns-section-header">
            <div class="ns-badge">
                {{ $faqSection->badge ?? '❓ FAQ' }}
            </div>
            <h2 class="ns-section-title">
                {{ $faqSection->title ?? 'Frequently Asked Questions' }}
            </h2>
            <p class="ns-section-subtitle">
                {{ $faqSection->subtitle ?? 'Everything you need to know about NextSpace spatial workplace.' }}
            </p>
        </div>

        <div style="display: flex; flex-direction: column; gap: 16px;">
            @php
                $faqs = $faqSection->content['faqs'] ?? [];
            @endphp
            @foreach($faqs as $index => $faq)
                <div class="ns-card" style="padding: 20px 24px; cursor: pointer;" onclick="toggleFaq({{ $index }})">
                    <div style="display: flex; justify-content: space-between; align-items: center; gap: 16px;">
                        <h4 style="font-size: 15px; font-weight: 800; color: var(--ns-white);">
                            {{ $faq['question_' . app()->getLocale()] ?? ($faq['question_en'] ?? '') }}
                        </h4>
                        <span id="faq-icon-{{ $index }}" style="font-size: 18px; color: var(--ns-mint); transition: transform 0.2s;">+</span>
                    </div>
                    <div id="faq-answer-{{ $index }}" style="display: none; margin-top: 14px; font-size: 13px; color: var(--ns-text-muted); line-height: 1.7; border-top: 1px solid rgba(111, 231, 194, 0.12); padding-top: 12px;">
                        {{ $faq['answer_' . app()->getLocale()] ?? ($faq['answer_en'] ?? '') }}
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>


<!-- ══════════════════════════════════════════════════════════════════════════
     8. HIGH-IMPACT CALL TO ACTION (CTA)
     ══════════════════════════════════════════════════════════════════════════ -->
<section class="ns-section" style="padding-bottom: 120px;">
    <div class="ns-container">
        <div class="ns-card" style="text-align: center; padding: 60px 32px; background: linear-gradient(135deg, rgba(19, 168, 121, 0.2) 0%, rgba(7, 26, 22, 0.95) 100%); border-color: rgba(111, 231, 194, 0.4); box-shadow: 0 0 50px rgba(19, 168, 121, 0.25);">
            <div class="ns-badge" style="margin-bottom: 16px;">
                {{ $ctaSection->badge ?? '🚀 Launch Your Workplace' }}
            </div>
            <h2 style="font-size: clamp(28px, 4vw, 42px); font-weight: 900; margin-bottom: 16px; color: var(--ns-white);">
                {{ $ctaSection->title ?? 'Ready to step into your new virtual workplace?' }}
            </h2>
            <p style="font-size: 16px; color: var(--ns-text-muted); max-width: 620px; margin: 0 auto 32px; line-height: 1.7;">
                {{ $ctaSection->subtitle ?? 'Join hundreds of forward-thinking remote teams. Setup your organization in under 60 seconds.' }}
            </p>
            <div>
                <a href="{{ route('register') }}" class="ns-btn ns-btn-primary" style="padding: 16px 36px; font-size: 16px;">
                    <span>🏢</span>
                    <span>{{ $ctaSection->content['btn_text_' . app()->getLocale()] ?? __('Create Organization for Free') }}</span>
                </a>
            </div>
            <div style="font-size: 12px; color: var(--ns-text-muted); margin-top: 18px;">
                {{ $ctaSection->content['note_' . app()->getLocale()] ?? '⚡ Instant setup • No credit card required • Free forever tier available' }}
            </div>
        </div>
    </div>
</section>

@endsection

@section('scripts')
<script>
    // ── Rates Matrix for Dynamic Currency Switching ──
    const currencyRates = {
        'USD': 1.0,
        'SAR': {{ (float)($rates['SAR'] ?? 3.75) }},
        'EGP': {{ (float)($rates['EGP'] ?? 48.5) }},
        'AED': {{ (float)($rates['AED'] ?? 3.67) }}
    };

    function switchCurrency(curr, btnElement) {
        document.querySelectorAll('.ns-currency-tab').forEach(b => b.classList.remove('active'));
        if (btnElement) btnElement.classList.add('active');

        const rate = currencyRates[curr] || 1.0;

        document.querySelectorAll('.plan-price-val').forEach(el => {
            const baseUsd = parseFloat(el.getAttribute('data-base-usd')) || 0;
            if (baseUsd === 0) {
                el.innerText = '0';
            } else {
                el.innerText = Math.round(baseUsd * rate);
            }
        });

        document.querySelectorAll('.plan-currency-symbol').forEach(el => {
            el.innerText = curr;
        });
    }

    // ── FAQ Accordion Toggle ──
    function toggleFaq(index) {
        const ans = document.getElementById('faq-answer-' + index);
        const icon = document.getElementById('faq-icon-' + index);
        if (ans) {
            if (ans.style.display === 'block') {
                ans.style.display = 'none';
                if (icon) icon.innerText = '+';
            } else {
                ans.style.display = 'block';
                if (icon) icon.innerText = '−';
            }
        }
    }

    // ── Interactive Proximity Simulator Demo ──
    function updateProximityDemo(dist) {
        const valEl = document.getElementById('proximity-distance-val');
        const waves = document.getElementById('demo-audio-waves');
        const status = document.getElementById('demo-connection-status');
        const d = parseInt(dist);

        if (d <= 5) {
            valEl.innerText = d + ' {{ __("Meters (Connected)") }}';
            valEl.style.color = 'var(--ns-mint)';
            waves.style.opacity = '1';
            status.style.background = 'rgba(16, 185, 129, 0.2)';
            status.style.color = '#10B981';
            status.style.borderColor = 'rgba(16, 185, 129, 0.4)';
            status.innerText = '🟢 {{ __("HD Video & Spatial Audio Active (100% Volume)") }}';
        } else if (d <= 12) {
            valEl.innerText = d + ' {{ __("Meters (Fading)") }}';
            valEl.style.color = '#E2B348';
            waves.style.opacity = '0.5';
            status.style.background = 'rgba(226, 179, 72, 0.2)';
            status.style.color = '#E2B348';
            status.style.borderColor = 'rgba(226, 179, 72, 0.4)';
            status.innerText = '🟡 {{ __("Audio Attenuated (Distance 40% Volume)") }}';
        } else {
            valEl.innerText = d + ' {{ __("Meters (Muted Out of Range)") }}';
            valEl.style.color = '#D96B5F';
            waves.style.opacity = '0.1';
            status.style.background = 'rgba(217, 107, 95, 0.2)';
            status.style.color = '#D96B5F';
            status.style.borderColor = 'rgba(217, 107, 95, 0.4)';
            status.innerText = '🔴 {{ __("Out of Spatial Audio Range (Muted)") }}';
        }
    }

    // ── Interactive Living Office Showcase Script ──
    function focus3dRoom(roomType) {
        const stage = document.getElementById('heroOfficeStage');
        if (!stage) return;

        // Update active chip
        document.querySelectorAll('.ns-room-chip').forEach(c => c.classList.remove('active'));
        document.querySelectorAll('.ns-hotspot-zone').forEach(z => z.classList.remove('focused'));

        // Highlight matching button
        const activeChip = Array.from(document.querySelectorAll('.ns-room-chip')).find(b => b.getAttribute('onclick') && b.getAttribute('onclick').includes(roomType));
        if (activeChip) activeChip.classList.add('active');

        const indicator = document.getElementById('hero-proximity-indicator');

        if (roomType === 'meeting') {
            stage.style.transform = 'scale(1.45) translate(18%, 15%)';
            const zone = document.getElementById('zone-meeting');
            if (zone) zone.classList.add('focused');
            if (indicator) {
                indicator.style.opacity = '1';
                indicator.querySelector('strong').innerText = '📹 {{ __("Meeting in Session (8 Seats)") }}';
                indicator.querySelector('span').innerText = '{{ __("Executive Boardroom • 4K Screen Active") }}';
            }
        } else if (roomType === 'workspace') {
            stage.style.transform = 'scale(1.4) translate(-10%, -10%)';
            const zone = document.getElementById('zone-workspace');
            if (zone) zone.classList.add('focused');
            if (indicator) {
                indicator.style.opacity = '1';
                indicator.querySelector('strong').innerText = '⚡ {{ __("Open Office Active") }}';
                indicator.querySelector('span').innerText = '{{ __("Sarah & Omar talking in Open Desks") }}';
            }
        } else if (roomType === 'lounge') {
            stage.style.transform = 'scale(1.5) translate(-22%, 18%)';
            const zone = document.getElementById('zone-lounge');
            if (zone) zone.classList.add('focused');
            if (indicator) {
                indicator.style.opacity = '1';
                indicator.querySelector('strong').innerText = '☕ {{ __("Team Lounge Active") }}';
                indicator.querySelector('span').innerText = '{{ __("Casual water-cooler banter & coffee") }}';
            }
        } else if (roomType === 'focus') {
            stage.style.transform = 'scale(1.6) translate(-24%, -20%)';
            const zone = document.getElementById('zone-focus');
            if (zone) zone.classList.add('focused');
            if (indicator) {
                indicator.style.opacity = '1';
                indicator.querySelector('strong').innerText = '🎧 {{ __("Focus Pod (DND Mode)") }}';
                indicator.querySelector('span').innerText = '{{ __("Soundproof Pod • Zero Distractions") }}';
            }
        } else {
            stage.style.transform = 'scale(1) translate(0, 0)';
            if (indicator) {
                indicator.style.opacity = '1';
                indicator.querySelector('strong').innerText = '⚡ {{ __("Proximity Active (Connected)") }}';
                indicator.querySelector('span').innerText = '{{ __("Sarah & Omar talking in Open Office") }}';
            }
        }
    }

    // ── Mouse Parallax 3D Tilt on Hero Stage ──
    document.addEventListener('DOMContentLoaded', () => {
        const wrapper = document.getElementById('office-preview');
        const stage = document.getElementById('heroOfficeStage');
        if (!wrapper || !stage) return;

        wrapper.addEventListener('mousemove', (e) => {
            const rect = wrapper.getBoundingClientRect();
            const x = (e.clientX - rect.left) / rect.width - 0.5;
            const y = (e.clientY - rect.top) / rect.height - 0.5;

            // Only tilt if not zoomed into a specific room
            if (!stage.style.transform || stage.style.transform.includes('scale(1)')) {
                stage.style.transform = `perspective(1000px) rotateY(${x * 12}deg) rotateX(${-y * 12}deg)`;
            }
        });

        wrapper.addEventListener('mouseleave', () => {
            if (!stage.style.transform || stage.style.transform.includes('rotate')) {
                stage.style.transform = 'scale(1) translate(0, 0)';
            }
        });

        // Walking Avatars Loop Simulation
        const omar = document.getElementById('agent-omar');
        const sarah = document.getElementById('agent-sarah');
        if (omar && sarah) {
            let step = 0;
            setInterval(() => {
                step = (step + 1) % 4;
                if (step === 0) {
                    omar.style.top = '52%'; omar.style.left = '34%';
                    sarah.style.top = '54%'; sarah.style.left = '45%';
                } else if (step === 1) {
                    omar.style.top = '50%'; omar.style.left = '38%';
                    sarah.style.top = '52%'; sarah.style.left = '42%';
                } else if (step === 2) {
                    omar.style.top = '53%'; omar.style.left = '40%';
                    sarah.style.top = '53%'; sarah.style.left = '41%';
                } else {
                    omar.style.top = '52%'; omar.style.left = '36%';
                    sarah.style.top = '55%'; sarah.style.left = '44%';
                }
            }, 3000);
        }
    });
</script>
@endsection
