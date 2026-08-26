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

    /* ── 3D Living Office Canvas Container ── */
    .ns-canvas-wrapper {
        position: relative;
        width: 100%;
        height: 540px;
        border-radius: 28px;
        background: radial-gradient(circle at center, #0B2922 0%, #051410 100%);
        border: 1px solid rgba(111, 231, 194, 0.25);
        box-shadow: 0 24px 60px rgba(0, 0, 0, 0.65), 0 0 40px rgba(19, 168, 121, 0.18);
        overflow: hidden;
    }

    #spatial-3d-canvas {
        width: 100%;
        height: 100%;
        display: block;
        cursor: grab;
    }

    #spatial-3d-canvas:active {
        cursor: grabbing;
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

        <!-- Interactive 3D Spatial Canvas Wrapper -->
        <div class="ns-canvas-wrapper" id="office-preview">
            <!-- Three.js Canvas Container -->
            <canvas id="spatial-3d-canvas"></canvas>

            <!-- Dynamic Proximity Video Bubble Simulation -->
            <div class="ns-proximity-bubble" id="hero-proximity-indicator">
                <div class="ns-wave-ring">🎧</div>
                <div>
                    <strong style="font-size: 12px; color: var(--ns-mint); display: block;">⚡ {{ __('Proximity Active') }}</strong>
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

    // ── 3D Living Virtual Office WebGL Scene (Three.js) ──
    let scene, camera, renderer, officeGroup, avatars = [];

    function init3dOffice() {
        const canvas = document.getElementById('spatial-3d-canvas');
        if (!canvas) return;

        const width = canvas.parentElement.clientWidth;
        const height = canvas.parentElement.clientHeight;

        scene = new THREE.Scene();
        scene.background = new THREE.Color(0x071A16);

        camera = new THREE.PerspectiveCamera(45, width / height, 0.1, 1000);
        camera.position.set(0, 35, 45);
        camera.lookAt(0, 0, 0);

        renderer = new THREE.WebGLRenderer({ canvas: canvas, antialias: true, alpha: true });
        renderer.setSize(width, height);
        renderer.setPixelRatio(Math.min(window.devicePixelRatio, 2));

        // Ambient & Spot Lighting
        const ambientLight = new THREE.AmbientLight(0xdff8ef, 0.7);
        scene.add(ambientLight);

        const dirLight = new THREE.DirectionalLight(0x6fe7c2, 1.2);
        dirLight.position.set(20, 40, 20);
        scene.add(dirLight);

        const accentPoint = new THREE.PointLight(0x13a879, 2, 50);
        accentPoint.position.set(-10, 10, 0);
        scene.add(accentPoint);

        officeGroup = new THREE.Group();
        scene.add(officeGroup);

        // Office Floor Grid
        const floorGeo = new THREE.PlaneGeometry(60, 40);
        const floorMat = new THREE.MeshStandardMaterial({
            color: 0x0b2922,
            roughness: 0.4,
            metalness: 0.2
        });
        const floor = new THREE.Mesh(floorGeo, floorMat);
        floor.rotation.x = -Math.PI / 2;
        officeGroup.add(floor);

        const grid = new THREE.GridHelper(60, 30, 0x13a879, 0x113a30);
        grid.position.y = 0.05;
        officeGroup.add(grid);

        // Build Low-Poly Furniture & Room Partitions
        create3dMeetingRoom(officeGroup, -16, 0);
        create3dOpenDesks(officeGroup, 8, -5);
        create3dLounge(officeGroup, 10, 10);

        // Animated Avatars
        createAvatar(officeGroup, -2, 0, 0x13a879);
        createAvatar(officeGroup, 4, 0, 0x3b82f6);

        // Window Resize Handling
        window.addEventListener('resize', () => {
            if (!canvas.parentElement) return;
            const newW = canvas.parentElement.clientWidth;
            const newH = canvas.parentElement.clientHeight;
            camera.aspect = newW / newH;
            camera.updateProjectionMatrix();
            renderer.setSize(newW, newH);
        });

        // Animation Loop
        let clock = new THREE.Clock();
        function animate() {
            requestAnimationFrame(animate);
            const time = clock.getElapsedTime();

            // Subtle rotation / life breathing
            officeGroup.rotation.y = Math.sin(time * 0.2) * 0.05;

            // Avatar walking simulation
            if (avatars.length >= 2) {
                avatars[0].position.x = -4 + Math.sin(time * 1.2) * 2;
                avatars[1].position.x = 4 - Math.sin(time * 1.2) * 2;
            }

            renderer.render(scene, camera);
        }
        animate();
    }

    function create3dMeetingRoom(parent, x, z) {
        const roomGroup = new THREE.Group();
        roomGroup.position.set(x, 0, z);

        // Glass Walls
        const wallMat = new THREE.MeshStandardMaterial({
            color: 0x6fe7c2,
            transparent: true,
            opacity: 0.25,
            roughness: 0.1
        });
        const wallGeo = new THREE.BoxGeometry(16, 6, 0.4);
        const wall1 = new THREE.Mesh(wallGeo, wallMat);
        wall1.position.set(0, 3, -10);
        roomGroup.add(wall1);

        // Conference Table
        const tableGeo = new THREE.BoxGeometry(10, 1.2, 5);
        const tableMat = new THREE.MeshStandardMaterial({ color: 0x10231f, roughness: 0.3 });
        const table = new THREE.Mesh(tableGeo, tableMat);
        table.position.set(0, 1.8, -4);
        roomGroup.add(table);

        parent.add(roomGroup);
    }

    function create3dOpenDesks(parent, x, z) {
        const deskMat = new THREE.MeshStandardMaterial({ color: 0x192d21, roughness: 0.5 });
        for (let i = 0; i < 4; i++) {
            const deskGeo = new THREE.BoxGeometry(5, 1.4, 3);
            const desk = new THREE.Mesh(deskGeo, deskMat);
            desk.position.set(x + (i % 2) * 7, 0.7, z + Math.floor(i / 2) * 5);
            parent.add(desk);
        }
    }

    function create3dLounge(parent, x, z) {
        const sofaMat = new THREE.MeshStandardMaterial({ color: 0x13a879, roughness: 0.8 });
        const sofaGeo = new THREE.BoxGeometry(6, 1.5, 3);
        const sofa = new THREE.Mesh(sofaGeo, sofaMat);
        sofa.position.set(x, 0.8, z);
        parent.add(sofa);
    }

    function createAvatar(parent, x, z, colorHex) {
        const avGroup = new THREE.Group();
        avGroup.position.set(x, 0, z);

        const bodyGeo = new THREE.CapsuleGeometry(0.8, 1.8, 4, 8);
        const bodyMat = new THREE.MeshStandardMaterial({ color: colorHex, roughness: 0.3 });
        const body = new THREE.Mesh(bodyGeo, bodyMat);
        body.position.y = 1.8;
        avGroup.add(body);

        parent.add(avGroup);
        avatars.push(avGroup);
    }

    function focus3dRoom(roomType) {
        if (!camera) return;
        document.querySelectorAll('.ns-room-chip').forEach(c => c.classList.remove('active'));
        if (event && event.currentTarget) event.currentTarget.classList.add('active');

        if (roomType === 'meeting') {
            camera.position.set(-16, 20, 25);
            camera.lookAt(-16, 0, -4);
        } else if (roomType === 'workspace') {
            camera.position.set(10, 20, 20);
            camera.lookAt(10, 0, -3);
        } else if (roomType === 'lounge') {
            camera.position.set(10, 18, 25);
            camera.lookAt(10, 0, 10);
        } else {
            camera.position.set(0, 35, 45);
            camera.lookAt(0, 0, 0);
        }
    }

    document.addEventListener('DOMContentLoaded', () => {
        try {
            init3dOffice();
        } catch(e) {
            console.warn('Three.js 3D WebGL fallback active:', e);
        }
    });
</script>
@endsection
