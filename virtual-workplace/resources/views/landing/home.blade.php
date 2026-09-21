@extends('landing.layout')

@section('title', 'UlaSpace — مساحات العمل والمكاتب الافتراضية الذكية')
@section('meta_description', 'مساحات عمل افتراضية غامرة تجمع فرق العمل عن بعد مع صوت وفيديو مكاني، وتخطيط خرائط المكاتب، والاجتماعات التفاعلية.')

@section('styles')
<style>
    /* ── 1. Hero Block (Figma Screen: Website #56:49) ── */
    .nx-hero-block {
        position: relative;
        background: #142B24;
        min-height: 520px;
        overflow: hidden;
        display: flex;
        align-items: center;
        padding: 56px 48px;
    }

    /* Diagonal Blueprint / Sand Stripes Backdrop matching Figma */
    .nx-hero-stripes-bg {
        position: absolute;
        inset: 0;
        background-color: #A38C6D;
        background-image: repeating-linear-gradient(
            -45deg,
            #B8A282 0px,
            #B8A282 8px,
            #A89273 8px,
            #A89273 24px
        );
        opacity: 0.95;
    }

    /* Scrim/Hero Linear Gradient (#56:55) */
    .nx-hero-scrim {
        position: absolute;
        inset: 0;
        background: linear-gradient(0deg, rgba(14, 28, 23, 0.8) 0%, rgba(14, 28, 23, 0.3) 55%, rgba(14, 28, 23, 0.05) 100%);
        pointer-events: none;
    }

    .nx-hero-inner {
        position: relative;
        z-index: 2;
        max-width: var(--ula-layout-max);
        margin: 0 auto;
        width: 100%;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 48px;
    }

    /* Figma Arched Floating Copy Card (#56:56) */
    .nx-hero-arch-card {
        width: 380px;
        background: #F9F6EF;
        border-radius: 999px 999px 24px 24px;
        padding: 44px 32px 32px;
        box-shadow: 0 24px 60px -12px rgba(27, 50, 35, 0.3);
        display: flex;
        flex-direction: column;
        align-items: flex-start;
        text-align: right;
        border: 1px solid rgba(237, 230, 217, 0.8);
        flex-shrink: 0;
    }

    [dir="ltr"] .nx-hero-arch-card {
        text-align: left;
    }

    .nx-hero-title {
        font-family: 'Cairo', sans-serif;
        font-size: 26px;
        font-weight: 700;
        line-height: 1.35;
        color: #142B24;
        margin-bottom: 12px;
    }

    .nx-hero-body {
        font-family: 'Cairo', sans-serif;
        font-size: 14px;
        line-height: 1.65;
        color: #4A443C;
        margin-bottom: 8px;
    }

    .nx-hero-en-sub {
        font-family: 'IBM Plex Sans', sans-serif;
        font-size: 13.5px;
        line-height: 1.45;
        color: #665D52;
        margin-bottom: 24px;
    }

    .nx-hero-buttons {
        display: flex;
        flex-direction: column;
        gap: 10px;
        width: 100%;
    }

    .nx-btn-primary-action {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        padding: 13px 24px;
        background: #1E412F;
        color: #FBF8F2;
        font-family: 'Cairo', sans-serif;
        font-size: 15px;
        font-weight: 700;
        border-radius: 14px;
        text-decoration: none;
        border: 1px solid #1E412F;
        box-shadow: 0 4px 14px rgba(30, 65, 47, 0.3);
        transition: all 0.15s ease;
    }

    .nx-btn-primary-action:hover {
        background: #27563e;
        color: #FFFFFF;
        transform: translateY(-1px);
    }

    .nx-btn-secondary-action {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 12px 24px;
        background: #F9F6EF;
        color: #1B3223;
        font-family: 'Cairo', sans-serif;
        font-size: 15px;
        font-weight: 600;
        border-radius: 14px;
        text-decoration: none;
        border: 1px solid #C1B6A6;
        transition: all 0.15s ease;
    }

    .nx-btn-secondary-action:hover {
        background: #EDE6D9;
    }

    /* Hero Overlay Typography (#56:76) */
    .nx-hero-overlay-block {
        text-align: end;
        color: #F9F6EF;
        padding-inline-end: 24px;
    }

    .nx-hero-overlay-ar {
        font-family: 'Cairo', sans-serif;
        font-size: clamp(34px, 4.5vw, 54px);
        font-weight: 800;
        line-height: 1.2;
        color: #F9F6EF;
        text-shadow: 0 4px 16px rgba(0, 0, 0, 0.35);
    }

    .nx-hero-overlay-en {
        font-family: 'IBM Plex Sans', sans-serif;
        font-size: clamp(16px, 2.2vw, 22px);
        font-weight: 300;
        color: #E3D2BB;
        letter-spacing: 0.02em;
        margin-top: 8px;
    }

    /* ── 2. 4-Pillars Features Strip (#56:85) ── */
    .nx-pillars-strip {
        background: #FBF8F2;
        padding: 38px 32px;
        border-bottom: 1px solid #E8E4DC;
    }

    .nx-pillars-container {
        max-width: var(--ula-layout-max);
        margin: 0 auto;
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 24px;
    }

    .nx-pillar-box {
        display: flex;
        flex-direction: column;
        align-items: center;
        text-align: center;
        padding: 8px;
    }

    .nx-pillar-ico {
        font-size: 28px;
        color: #142B24;
        margin-bottom: 10px;
    }

    .nx-pillar-ar {
        font-family: 'Cairo', sans-serif;
        font-size: 15px;
        font-weight: 700;
        color: #142B24;
        line-height: 1.4;
    }

    .nx-pillar-en {
        font-family: 'IBM Plex Sans', sans-serif;
        font-size: 12.5px;
        color: #665D52;
        margin-top: 2px;
    }

    /* ── 3. General Section Layout ── */
    .nx-section-wrap {
        padding: 80px 32px;
        max-width: var(--ula-layout-max);
        margin: 0 auto;
    }

    .nx-section-header {
        text-align: center;
        max-width: 720px;
        margin: 0 auto 52px;
    }

    .nx-section-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 5px 16px;
        border-radius: 999px;
        background: #E3D2BB;
        color: #142B24;
        font-size: 12px;
        font-weight: 700;
        margin-bottom: 14px;
        letter-spacing: 0.04em;
    }

    .nx-section-title {
        font-family: 'Cairo', sans-serif;
        font-size: clamp(28px, 3.4vw, 38px);
        font-weight: 800;
        color: #142B24;
        line-height: 1.25;
        margin-bottom: 12px;
    }

    .nx-section-desc {
        font-family: 'Cairo', sans-serif;
        font-size: 15px;
        color: #665D52;
        line-height: 1.7;
    }

    /* ── Spaces Explorer ── */
    .nx-spaces-split {
        display: grid;
        grid-template-columns: 1fr 1.2fr;
        gap: 36px;
        align-items: center;
        background: #FFFFFF;
        border-radius: 24px;
        border: 1px solid #E3D2BB;
        padding: 36px;
        box-shadow: 0 16px 40px -8px rgba(20, 43, 36, 0.08);
    }

    .nx-space-nav-list {
        display: flex;
        flex-direction: column;
        gap: 12px;
    }

    .nx-space-tab-card {
        display: flex;
        align-items: center;
        gap: 16px;
        padding: 16px 20px;
        border-radius: 14px;
        background: #FBF8F2;
        border: 1px solid transparent;
        cursor: pointer;
        text-align: start;
        transition: all 0.18s ease;
    }

    .nx-space-tab-card.active {
        background: #FFFFFF;
        border-color: #E3D2BB;
        box-shadow: 0 6px 18px rgba(20, 43, 36, 0.06);
    }

    .nx-space-tab-icon {
        width: 42px;
        height: 42px;
        border-radius: 10px;
        background: #EDE6D9;
        color: #142B24;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }

    .nx-space-tab-card.active .nx-space-tab-icon {
        background: #142B24;
        color: #F9F6EF;
    }

    /* ── 4. System Benefits & Capabilities Matrix (#benefits) ── */
    .nx-benefits-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 28px;
    }

    .nx-benefit-card {
        background: #FFFFFF;
        border: 1px solid #E3D2BB;
        border-radius: 20px;
        padding: 32px 28px;
        box-shadow: 0 8px 24px rgba(20, 43, 36, 0.04);
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        transition: all 0.2s ease;
        position: relative;
        overflow: hidden;
    }

    .nx-benefit-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 16px 36px rgba(20, 43, 36, 0.08);
        border-color: #C1B6A6;
    }

    .nx-benefit-icon-box {
        width: 52px;
        height: 52px;
        border-radius: 14px;
        background: #F4EDE1;
        color: #142B24;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 20px;
        border: 1px solid #E8E4DC;
    }

    .nx-benefit-card:hover .nx-benefit-icon-box {
        background: #142B24;
        color: #F9F6EF;
    }

    .nx-benefit-title {
        font-family: 'Cairo', sans-serif;
        font-size: 18px;
        font-weight: 700;
        color: #142B24;
        margin-bottom: 6px;
    }

    .nx-benefit-sub {
        font-family: 'IBM Plex Sans', sans-serif;
        font-size: 12.5px;
        color: #665D52;
        font-weight: 500;
        margin-bottom: 12px;
    }

    .nx-benefit-desc {
        font-family: 'Cairo', sans-serif;
        font-size: 13.5px;
        color: #4A443C;
        line-height: 1.7;
    }

    /* ── 5. Live Meetings Row Section (#meetings) ── */
    .nx-meetings-card {
        max-width: 860px;
        margin: 0 auto;
        background: #FFFFFF;
        border-radius: 20px;
        border: 1px solid #E3D2BB;
        padding: 24px;
        box-shadow: 0 10px 30px rgba(20, 43, 36, 0.05);
    }

    .nx-meeting-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 16px 20px;
        border-radius: 14px;
        background: #FBF8F2;
        border: 1px solid #E8E4DC;
        margin-bottom: 12px;
        gap: 16px;
        transition: all 0.15s ease;
    }

    .nx-meeting-row:hover {
        background: #F4EDE1;
        border-color: #D3A553;
        transform: translateY(-1px);
    }

    .nx-meeting-time {
        font-family: 'IBM Plex Mono', monospace;
        font-size: 13px;
        font-weight: 700;
        color: #142B24;
        padding: 6px 12px;
        border-radius: 8px;
        background: #E3D2BB;
        direction: ltr;
        unicode-bidi: isolate;
    }

    .nx-pulse-beacon {
        width: 8px;
        height: 8px;
        border-radius: 50%;
        background: #16a34a;
        box-shadow: 0 0 10px #16a34a;
        display: inline-block;
    }

    .nx-avatar-stack {
        display: flex;
        align-items: center;
    }

    .nx-avatar-item {
        width: 32px;
        height: 32px;
        border-radius: 50%;
        background: #142B24;
        color: #F9F6EF;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 11px;
        font-weight: 700;
        border: 2px solid #FFFFFF;
        margin-inline-start: -8px;
    }

    .nx-avatar-item:first-child {
        margin-inline-start: 0;
    }

    /* ── 6. Saudi Heritage & Identity Section (#56:114) ── */
    .nx-heritage-box {
        background: #F4EDE1;
        border-radius: 24px;
        border: 1px solid #E3D2BB;
        padding: 56px 48px;
        display: grid;
        grid-template-columns: 1fr 1.15fr;
        gap: 48px;
        align-items: center;
        position: relative;
        overflow: hidden;
    }

    .nx-heritage-visual-card {
        position: relative;
        height: 240px;
        border-radius: 18px;
        overflow: hidden;
        border: 1px solid #D9D3C8;
        background-color: #A38C6D;
        background-image: repeating-linear-gradient(
            -45deg,
            #B8A282 0px,
            #B8A282 8px,
            #A89273 8px,
            #A89273 24px
        );
        box-shadow: 0 14px 32px rgba(20, 43, 36, 0.12);
        display: flex;
        flex-direction: column;
        justify-content: flex-end;
    }

    .nx-heritage-scrim {
        position: absolute;
        inset: 0;
        background: linear-gradient(0deg, rgba(14, 28, 23, 0.8) 0%, rgba(14, 28, 23, 0) 70%);
    }

    .nx-heritage-caption {
        position: relative;
        z-index: 2;
        padding: 24px;
        color: #F9F6EF;
    }

    /* ── 7. Backend Subscription Plans Section (#pricing) ── */
    .nx-pricing-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
        gap: 24px;
    }

    .nx-plan-card {
        background: #FFFFFF;
        border-radius: 22px;
        border: 1px solid #E3D2BB;
        padding: 36px 28px;
        box-shadow: 0 6px 20px rgba(20, 43, 36, 0.04);
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        position: relative;
        transition: all 0.2s ease;
    }

    .nx-plan-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 16px 36px rgba(20, 43, 36, 0.08);
        border-color: #C1B6A6;
    }

    .nx-plan-card.highlighted {
        border: 2px solid #1E412F;
        box-shadow: 0 16px 40px rgba(30, 65, 47, 0.12);
    }

    .nx-plan-badge {
        position: absolute;
        top: -13px;
        inset-inline-end: 24px;
        background: #1E412F;
        color: #FBF8F2;
        font-family: 'Cairo', sans-serif;
        font-size: 11.5px;
        font-weight: 700;
        padding: 4px 14px;
        border-radius: 999px;
    }

    .nx-plan-price {
        font-family: 'IBM Plex Mono', monospace;
        font-size: 36px;
        font-weight: 800;
        color: #142B24;
        direction: ltr;
        unicode-bidi: isolate;
    }

    /* ── 8. Bottom Action Banner ── */
    .nx-bottom-banner {
        background: #142B24;
        border-radius: 28px;
        padding: 64px 36px;
        text-align: center;
        color: #F9F6EF;
        margin: 40px auto 20px;
        border: 1px solid rgba(237, 230, 217, 0.15);
    }

    /* Responsive */
    @media (max-width: 1024px) {
        .nx-hero-inner { flex-direction: column; align-items: stretch; }
        .nx-hero-arch-card { width: 100%; border-radius: 36px 36px 20px 20px; }
        .nx-hero-overlay-block { text-align: start; padding-inline-end: 0; }
        .nx-pillars-container { grid-template-columns: repeat(2, 1fr); }
        .nx-spaces-split { grid-template-columns: 1fr; }
        .nx-benefits-grid { grid-template-columns: repeat(2, 1fr); }
        .nx-heritage-box { grid-template-columns: 1fr; padding: 36px 24px; }
    }

    @media (max-width: 640px) {
        .nx-hero-block { padding: 36px 20px; }
        .nx-pillars-container { grid-template-columns: 1fr; }
        .nx-benefits-grid { grid-template-columns: 1fr; }
        .nx-meeting-row { flex-direction: column; align-items: flex-start; }
    }
</style>
@endsection

@section('content')

    <!-- ── 1. Hero Block (Figma Screen: Website #56:49) ── -->
    <section class="nx-hero-block">
        <div class="nx-hero-stripes-bg"></div>
        <div class="nx-hero-scrim"></div>

        <div class="nx-hero-inner">
            <!-- Floating Arch Card (#56:56) -->
            <div class="nx-hero-arch-card">
                <h1 class="nx-hero-title">
                    {{ app()->getLocale() === 'ar' ? 'اجمع فريقك في مساحة واحدة ذكية' : 'Unite Your Team in One Intelligent Space' }}
                </h1>

                <p class="nx-hero-body">
                    {{ app()->getLocale() === 'ar' ? 'مكاتب افتراضية نابضة بالحياة بالصوت والصورة والمحادثات. اجتماعات سهلة، ومشاركة أقرب.' : 'Vibrant virtual offices with proximity audio, video, and chat. Seamless meetings and natural collaboration.' }}
                </p>

                <div class="nx-hero-en-sub">
                    A more human way to work together.
                </div>

                <div class="nx-hero-buttons">
                    @auth
                        <a href="{{ route('office') }}" class="nx-btn-primary-action">
                            <span class="material-symbols-rounded text-[18px]">apartment</span>
                            <span>{{ __('ادخل المقر') }}</span>
                        </a>
                    @else
                        <!-- Primary Action Button (#56:63) -->
                        <a href="{{ route('register') }}" class="nx-btn-primary-action">
                            <span class="material-symbols-rounded text-[18px]">arrow_forward</span>
                            <span>{{ app()->getLocale() === 'ar' ? 'ادخل إلى مساحتك' : 'Enter Your Space' }}</span>
                        </a>

                        <!-- Secondary Action Button (#56:70) -->
                        <a href="#spaces" class="nx-btn-secondary-action">
                            <span>{{ app()->getLocale() === 'ar' ? 'شاهد العرض' : 'Watch Demo' }}</span>
                        </a>
                    @endauth
                </div>
            </div>

            <!-- Overlay Headline (#56:76) -->
            <div class="nx-hero-overlay-block">
                <div class="nx-hero-overlay-ar">
                    أكثر من<br>مكان العمل
                </div>
                <div class="nx-hero-overlay-en">
                    A more human<br>place to work.
                </div>
            </div>
        </div>
    </section>

    <!-- ── 2. 4-Pillars Features Strip (Figma #56:85) ── -->
    <section class="nx-pillars-strip">
        <div class="nx-pillars-container">
            <!-- Pillar 1: Live Virtual Offices -->
            <div class="nx-pillar-box">
                <span class="material-symbols-rounded nx-pillar-ico">apartment</span>
                <div class="nx-pillar-ar">{{ app()->getLocale() === 'ar' ? 'مكاتب افتراضية حية' : 'Live Virtual Offices' }}</div>
                <div class="nx-pillar-en">Live Virtual Offices</div>
            </div>

            <!-- Pillar 2: Seamless Meetings -->
            <div class="nx-pillar-box">
                <span class="material-symbols-rounded nx-pillar-ico">videocam</span>
                <div class="nx-pillar-ar">{{ app()->getLocale() === 'ar' ? 'اجتماعات سلسة' : 'Seamless Meetings' }}</div>
                <div class="nx-pillar-en">Seamless Meetings</div>
            </div>

            <!-- Pillar 3: 1-Click Guest Access -->
            <div class="nx-pillar-box">
                <span class="material-symbols-rounded nx-pillar-ico">person_add</span>
                <div class="nx-pillar-ar">{{ app()->getLocale() === 'ar' ? 'دعوات بضغطة واحدة' : '1-Click Guest Access' }}</div>
                <div class="nx-pillar-en">1-Click Guest Access</div>
            </div>

            <!-- Pillar 4: Flexible Design -->
            <div class="nx-pillar-box">
                <span class="material-symbols-rounded nx-pillar-ico">chair</span>
                <div class="nx-pillar-ar">{{ app()->getLocale() === 'ar' ? 'تصميم مرن لمكتبك' : 'Design Your Space' }}</div>
                <div class="nx-pillar-en">Design Your Space</div>
            </div>
        </div>
    </section>

    <!-- ── 3. Spaces Explorer (#spaces) ── -->
    <section id="spaces" class="nx-section-wrap">
        <div class="nx-section-header">
            <div class="nx-section-badge">{{ app()->getLocale() === 'ar' ? 'المساحات الذكية' : 'Smart Spaces' }}</div>
            <h2 class="nx-section-title">
                {{ app()->getLocale() === 'ar' ? 'مكتب افتراضي يشبه مكتبك الحقيقي' : 'A Virtual Office That Feels Truly Real' }}
            </h2>
            <p class="nx-section-desc">
                {{ app()->getLocale() === 'ar' ? 'صمم مخطط مكتبك بحرية وادعُ فريقك للتنقل والتواصل الطبيعي في غرف الاجتماعات، ومكاتب العمل، وصالات الاستراحة.' : 'Move naturally across boardrooms, private desks, and lounges with proximity audio.' }}
            </p>
        </div>

        <div class="nx-spaces-split">
            <div class="nx-space-nav-list">
                <button type="button" class="nx-space-tab-card active" onclick="switchSpaceTab(0)">
                    <div class="nx-space-tab-icon">
                        <span class="material-symbols-rounded text-[22px]">meeting_room</span>
                    </div>
                    <div>
                        <div style="font-family: 'Cairo', sans-serif; font-size: 16px; font-weight: 700; color: #142B24;">
                            {{ app()->getLocale() === 'ar' ? 'قاعة الاجتماعات الكبرى' : 'Executive Boardroom' }}
                        </div>
                        <div style="font-size: 12.5px; color: #665D52; margin-top: 2px;">
                            {{ app()->getLocale() === 'ar' ? 'عزل صوتي كامل ومشاركة شاشة بدقة 4K' : 'Acoustic isolation & 4K multi-screen' }}
                        </div>
                    </div>
                </button>

                <button type="button" class="nx-space-tab-card" onclick="switchSpaceTab(1)">
                    <div class="nx-space-tab-icon">
                        <span class="material-symbols-rounded text-[22px]">workspaces</span>
                    </div>
                    <div>
                        <div style="font-family: 'Cairo', sans-serif; font-size: 16px; font-weight: 700; color: #142B24;">
                            {{ app()->getLocale() === 'ar' ? 'مساحة العمل المفتوحة' : 'Open Workspace Floor' }}
                        </div>
                        <div style="font-size: 12.5px; color: #665D52; margin-top: 2px;">
                            {{ app()->getLocale() === 'ar' ? 'صوت مكاني تلقائي عند الاقتراب' : 'Proximity instant voice' }}
                        </div>
                    </div>
                </button>

                <button type="button" class="nx-space-tab-card" onclick="switchSpaceTab(2)">
                    <div class="nx-space-tab-icon">
                        <span class="material-symbols-rounded text-[22px]">coffee</span>
                    </div>
                    <div>
                        <div style="font-family: 'Cairo', sans-serif; font-size: 16px; font-weight: 700; color: #142B24;">
                            {{ app()->getLocale() === 'ar' ? 'ردهة القهوة والاستراحة' : 'Social Lounge' }}
                        </div>
                        <div style="font-size: 12.5px; color: #665D52; margin-top: 2px;">
                            {{ app()->getLocale() === 'ar' ? 'محادثات عفوية وراحة الفريق' : 'Watercooler spontaneous moments' }}
                        </div>
                    </div>
                </button>
            </div>

            <div style="border-radius: 18px; overflow: hidden; border: 1px solid #E3D2BB; box-shadow: 0 12px 28px rgba(20,43,36,0.1);">
                <img src="{{ asset('images/isometric_office_preview.jpg') }}" alt="{{ __('UlaSpace Office Preview') }}" style="width: 100%; height: auto; display: block;">
            </div>
        </div>
    </section>

    <!-- ── 4. System Benefits & Capabilities Matrix (#benefits) ── -->
    <section id="benefits" class="nx-section-wrap" style="padding-top: 20px;">
        <div class="nx-section-header">
            <div class="nx-section-badge">{{ app()->getLocale() === 'ar' ? 'مميزات وقدرات المنصة' : 'Platform Benefits' }}</div>
            <h2 class="nx-section-title">
                {{ app()->getLocale() === 'ar' ? 'كل ما يحتاجه فريقك لبيئة عمل منتجة وحية' : 'Everything You Need for a High-Performing HQ' }}
            </h2>
            <p class="nx-section-desc">
                {{ app()->getLocale() === 'ar' ? 'حلول مكانية متكاملة تدمج الصوت والفيديو والخرائط وإدارة المهام لتعزيز الإنتاجية والتواصل.' : 'Integrated spatial solutions combining proximity audio, interactive floorplans, and enterprise productivity.' }}
            </p>
        </div>

        <div class="nx-benefits-grid">
            <!-- Benefit 1 -->
            <div class="nx-benefit-card">
                <div>
                    <div class="nx-benefit-icon-box">
                        <span class="material-symbols-rounded text-[28px]">graphic_eq</span>
                    </div>
                    <div class="nx-benefit-title">{{ app()->getLocale() === 'ar' ? 'الصوت والفيديو المكاني' : 'Spatial Proximity Audio' }}</div>
                    <div class="nx-benefit-sub">WebRTC Proximity Mesh</div>
                    <p class="nx-benefit-desc">
                        {{ app()->getLocale() === 'ar' ? 'تواصل تلقائي يحاكي الواقع تماماً؛ يقوى الصوت كلما اقتربت من زملائك على الخريطة دون روابط اتصال معقدة.' : 'Natural voice communication that mimics real life as you walk near teammates without cumbersome invite links.' }}
                    </p>
                </div>
            </div>

            <!-- Benefit 2 -->
            <div class="nx-benefit-card">
                <div>
                    <div class="nx-benefit-icon-box">
                        <span class="material-symbols-rounded text-[28px]">meeting_room</span>
                    </div>
                    <div class="nx-benefit-title">{{ app()->getLocale() === 'ar' ? 'قاعات اجتماعات معزولة' : 'Acoustic Boardrooms' }}</div>
                    <div class="nx-benefit-sub">Isolated Audio & Knocking</div>
                    <p class="nx-benefit-desc">
                        {{ app()->getLocale() === 'ar' ? 'غرف مقفلة مع ميزة الاستئذان وجرس الدخول وعزل صوتي تام يضمن سرية الاجتماعات الاستراتيجية.' : 'Locked rooms with knock-to-enter and complete acoustic isolation for private and strategic sessions.' }}
                    </p>
                </div>
            </div>

            <!-- Benefit 3 -->
            <div class="nx-benefit-card">
                <div>
                    <div class="nx-benefit-icon-box">
                        <span class="material-symbols-rounded text-[28px]">architecture</span>
                    </div>
                    <div class="nx-benefit-title">{{ app()->getLocale() === 'ar' ? 'محرر المخططات والأثاث' : 'Visual Map Architect' }}</div>
                    <div class="nx-benefit-sub">Drag & Drop Floor Editor</div>
                    <p class="nx-benefit-desc">
                        {{ app()->getLocale() === 'ar' ? 'صمم وخصص مقر عملك بسحب وإفلات المكاتب والنباتات والشاشات والسبورات التفاعلية بحرية مطلقة.' : 'Build custom office blueprints by placing desks, greenery, meeting tables, and presentation screens.' }}
                    </p>
                </div>
            </div>

            <!-- Benefit 4 -->
            <div class="nx-benefit-card">
                <div>
                    <div class="nx-benefit-icon-box">
                        <span class="material-symbols-rounded text-[28px]">screen_share</span>
                    </div>
                    <div class="nx-benefit-title">{{ app()->getLocale() === 'ar' ? 'مشاركة شاشة متزامنة 4K' : 'Multi-Screen 4K Sharing' }}</div>
                    <div class="nx-benefit-sub">Ultra-HD Collaboration</div>
                    <p class="nx-benefit-desc">
                        {{ app()->getLocale() === 'ar' ? 'مشاركة شاشات متعددة في نفس الغرفة لدعم فرق البرمجة والتصميم وإدارة المشاريع بسلاسة فائقة.' : 'Simultaneous high-framerate screen sharing designed for engineering, design, and product reviews.' }}
                    </p>
                </div>
            </div>

            <!-- Benefit 5 -->
            <div class="nx-benefit-card">
                <div>
                    <div class="nx-benefit-icon-box">
                        <span class="material-symbols-rounded text-[28px]">insights</span>
                    </div>
                    <div class="nx-benefit-title">{{ app()->getLocale() === 'ar' ? 'حضور حي وتحليلات تفاعل' : 'Live Presence & Stats' }}</div>
                    <div class="nx-benefit-sub">Team Activity Analytics</div>
                    <p class="nx-benefit-desc">
                        {{ app()->getLocale() === 'ar' ? 'رؤية فورية لتواجد الزملاء ومشاركتهم في الغرف دون مراقبة مزعجة، مما يبني ثقافة عمل قائمة على الثقة.' : 'Real-time visibility into active members and meeting occupancy, fostering high-trust remote culture.' }}
                    </p>
                </div>
            </div>

            <!-- Benefit 6 -->
            <div class="nx-benefit-card">
                <div>
                    <div class="nx-benefit-icon-box">
                        <span class="material-symbols-rounded text-[28px]">verified_user</span>
                    </div>
                    <div class="nx-benefit-title">{{ app()->getLocale() === 'ar' ? 'أمان وخصوصية المؤسسات' : 'Enterprise Security & SSO' }}</div>
                    <div class="nx-benefit-sub">End-to-End Privacy</div>
                    <p class="nx-benefit-desc">
                        {{ app()->getLocale() === 'ar' ? 'تشفير كامل للاتصالات ودعم تسجيل الدخول الموحد وعزل بيانات المنظمات وفق أعلى المعايير.' : 'Strict tenant isolation, encrypted WebRTC streams, and enterprise access control policies.' }}
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- ── 5. Live Meetings Schedule (#meetings) ── -->
    <section id="meetings" class="nx-section-wrap" style="padding-top: 20px;">
        <div class="nx-section-header">
            <div class="nx-section-badge">{{ app()->getLocale() === 'ar' ? 'الاجتماعات الحية' : 'Live Meetings' }}</div>
            <h2 class="nx-section-title">
                {{ app()->getLocale() === 'ar' ? 'جدول اجتماعاتك بلمحة واحدة' : 'Your Schedule in One Connected Place' }}
            </h2>
            <p class="nx-section-desc">
                {{ app()->getLocale() === 'ar' ? 'تعرّف على الغرف المشغولة ومن يتحدث في المكالمة، وانضم بضغطة زر.' : 'See what is live, who is in the room, and jump into sessions seamlessly with one click.' }}
            </p>
        </div>

        <div class="nx-meetings-card">
            <!-- Meeting Row 1 (Live) -->
            <div class="nx-meeting-row">
                <div class="flex items-center gap-3">
                    <span class="nx-pulse-beacon"></span>
                    <span class="nx-meeting-time">10:00 AM</span>
                    <div>
                        <div style="font-family: 'Cairo', sans-serif; font-size: 15px; font-weight: 700; color: #142B24;">
                            {{ app()->getLocale() === 'ar' ? 'مراجعة تصميم منصة العلا' : 'UlaSpace Design Review' }}
                        </div>
                        <div style="font-size: 12px; color: #665D52;">
                            {{ app()->getLocale() === 'ar' ? 'قاعة النخيل الكبرى · 4 مشاركين' : 'Palm Boardroom · 4 Participants' }}
                        </div>
                    </div>
                </div>

                <div class="flex items-center gap-4">
                    <div class="nx-avatar-stack">
                        <div class="nx-avatar-item">ع</div>
                        <div class="nx-avatar-item" style="background: #3C6B4C;">س</div>
                        <div class="nx-avatar-item" style="background: #D3A553;">م</div>
                    </div>
                    <a href="{{ route('office') }}" class="px-3.5 py-1.5 rounded-lg bg-[#1E412F] text-white text-[12px] font-bold hover:bg-[#25523b] transition">
                        {{ app()->getLocale() === 'ar' ? 'انضم الآن' : 'Join Now' }}
                    </a>
                </div>
            </div>

            <!-- Meeting Row 2 (Scheduled) -->
            <div class="nx-meeting-row" style="margin-bottom: 0;">
                <div class="flex items-center gap-3">
                    <span style="width: 8px; height: 8px; border-radius: 50%; background: #D3A553; display: inline-block;"></span>
                    <span class="nx-meeting-time">11:30 AM</span>
                    <div>
                        <div style="font-family: 'Cairo', sans-serif; font-size: 15px; font-weight: 700; color: #142B24;">
                            {{ app()->getLocale() === 'ar' ? 'مزامنة الفريق الهندسي' : 'Engineering Architecture Sync' }}
                        </div>
                        <div style="font-size: 12px; color: #665D52;">
                            {{ app()->getLocale() === 'ar' ? 'مساحة الابتكار · بعد 45 دقيقة' : 'Innovation Lounge · in 45 min' }}
                        </div>
                    </div>
                </div>

                <div class="flex items-center gap-4">
                    <div class="nx-avatar-stack">
                        <div class="nx-avatar-item" style="background: #142B24;">ف</div>
                        <div class="nx-avatar-item" style="background: #665D52;">ي</div>
                    </div>
                    <span class="text-[12px] text-[#665D52] font-semibold">{{ app()->getLocale() === 'ar' ? 'مجدول' : 'Scheduled' }}</span>
                </div>
            </div>
        </div>
    </section>

    <!-- ── 6. Saudi Heritage & Identity Section (#56:114) ── -->
    <section id="identity" class="nx-section-wrap" style="padding-top: 20px;">
        <div class="nx-heritage-box">
            <!-- Visual Card with Scrim Caption (#56:120) -->
            <div class="nx-heritage-visual-card">
                <div class="nx-heritage-scrim"></div>
                <div class="nx-heritage-caption">
                    <div style="font-family: 'Cairo', sans-serif; font-size: 16px; font-weight: 700; color: #F9F6EF;">
                        من السعودية … إلى العالم
                    </div>
                    <div style="font-family: 'IBM Plex Sans', sans-serif; font-size: 12px; color: #E3D2BB; margin-top: 2px;">
                        ALULA · SAUDI ARABIA
                    </div>
                </div>
            </div>

            <!-- Copy Block (#56:115) -->
            <div>
                <h3 style="font-family: 'Cairo', sans-serif; font-size: 28px; font-weight: 800; color: #142B24; line-height: 1.3; margin-bottom: 4px;">
                    مستقبل العمل.. بروح سعودية
                </h3>
                <div style="font-family: 'IBM Plex Sans', sans-serif; font-size: 15px; color: #665D52; margin-bottom: 18px;">
                    The future of work. A Saudi spirit.
                </div>
                <p style="font-family: 'Cairo', sans-serif; font-size: 14.5px; color: #4A443C; line-height: 1.8;">
                    صُمّمت UlaSpace بإلهام من العلا لفرق تعمل من كل مكان: ضيافة سعودية في التفاصيل، وهندسة عالمية في الأداء والاتصال المكاني.
                </p>
                <div class="mt-6 flex items-center gap-3">
                    <span class="material-symbols-rounded text-[#1E412F] text-[24px]">verified</span>
                    <span style="font-family: 'Cairo', sans-serif; font-size: 13.5px; font-weight: 700; color: #142B24;">
                        {{ app()->getLocale() === 'ar' ? 'مبني وفق أعلى معايير الخصوصية والأمان السيبراني' : 'Built to enterprise security & privacy standards' }}
                    </span>
                </div>
            </div>
        </div>
    </section>

    <!-- ── 7. Backend Subscription Plans Section (#pricing) ── -->
    <section id="pricing" class="nx-section-wrap">
        <div class="nx-section-header">
            <div class="nx-section-badge">{{ app()->getLocale() === 'ar' ? 'الباقات والاشتراكات' : 'Subscription Plans' }}</div>
            <h2 class="nx-section-title">
                {{ app()->getLocale() === 'ar' ? 'باقة تناسب حجم وتطلعات فريقك' : 'Plans That Scale With Your Team' }}
            </h2>
            <p class="nx-section-desc">
                {{ app()->getLocale() === 'ar' ? 'ابدأ مجاناً اليوم، وقم بالترقية في أي وقت مع نمو وتوسع أعمالك.' : 'Start free, upgrade anytime as your workplace expands.' }}
            </p>
        </div>

        @php
            $planNameAr = [
                'Free' => 'مجاني',
                'Starter' => 'مبتدئ',
                'Business' => 'أعمال',
                'Enterprise' => 'مؤسسات'
            ];
        @endphp

        <div class="nx-pricing-grid">
            @foreach($plans as $plan)
                @php
                    $isPopular = ($plan->slug === 'business' || $plan->slug === 'pro' || strtolower($plan->name) === 'business');
                @endphp
                <div class="nx-plan-card {{ $isPopular ? 'highlighted' : '' }}">
                    @if($isPopular)
                        <div class="nx-plan-badge">
                            {{ app()->getLocale() === 'ar' ? 'الأكثر طلباً' : 'Most Popular' }}
                        </div>
                    @endif

                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <h3 style="font-family: 'Cairo', sans-serif; font-size: 21px; font-weight: 800; color: #142B24;">
                                {{ $planNameAr[$plan->name] ?? $plan->name }}
                            </h3>
                            <span style="font-family: 'IBM Plex Mono', monospace; font-size: 12px; color: #665D52;">
                                {{ $plan->name }}
                            </span>
                        </div>

                        <div class="my-4 flex items-baseline gap-1.5" style="direction: ltr; unicode-bidi: isolate;">
                            <span class="nx-plan-price" data-plan-usd="{{ $plan->price }}">{{ $plan->price == 0 ? '0' : number_format($plan->price * 3.75, 0) }}</span>
                            <span class="nx-currency-symbol" style="font-size: 15px; font-weight: 700; color: #1E412F; font-family: 'Cairo', sans-serif;">ر.س</span>
                            <span style="font-size: 13px; color: #665D52;">/{{ __('شهرياً') }}</span>
                        </div>

                        <ul class="flex flex-col gap-3 my-6 text-[13.5px] text-[#4A443C]">
                            <li class="flex items-center gap-2.5">
                                <span class="material-symbols-rounded text-[#1E412F] text-[18px]">group</span>
                                <span>
                                    <strong style="direction: ltr; unicode-bidi: isolate;">{{ $plan->isUnlimitedSeats() ? '∞' : $plan->seat_limit }}</strong>
                                    {{ app()->getLocale() === 'ar' ? 'مقعداً متاحاً' : 'Seats' }}
                                </span>
                            </li>
                            <li class="flex items-center gap-2.5">
                                <span class="material-symbols-rounded text-[#1E412F] text-[18px]">domain</span>
                                <span>
                                    <strong style="direction: ltr; unicode-bidi: isolate;">{{ $plan->isUnlimitedOffices() ? '∞' : $plan->max_offices }}</strong>
                                    {{ app()->getLocale() === 'ar' ? 'مكاتب وطوابق' : 'Offices' }}
                                </span>
                            </li>
                            <li class="flex items-center gap-2.5">
                                <span class="material-symbols-rounded text-[#1E412F] text-[18px]">cloud</span>
                                <span>
                                    <strong style="direction: ltr; unicode-bidi: isolate;">{{ $plan->storage_limit_gb ?? 5 }} GB</strong>
                                    {{ app()->getLocale() === 'ar' ? 'مساحة تخزين سحابية' : 'Cloud Storage' }}
                                </span>
                            </li>
                            @if(is_array($plan->features))
                                @foreach(array_slice($plan->features, 0, 3) as $feature)
                                    <li class="flex items-center gap-2.5">
                                        <span class="material-symbols-rounded text-[#1E412F] text-[18px]">check_circle</span>
                                        <span>{{ $feature }}</span>
                                    </li>
                                @endforeach
                            @endif
                        </ul>
                    </div>

                    <a href="{{ route('register', ['plan' => $plan->slug]) }}" class="w-full py-3.5 rounded-xl font-bold text-[14px] text-center transition block {{ $isPopular ? 'bg-[#1E412F] text-white hover:bg-[#27563e]' : 'bg-[#F4EDE1] text-[#142B24] hover:bg-[#E3D2BB]' }}">
                        {{ app()->getLocale() === 'ar' ? 'ابدأ الآن' : 'Get Started' }}
                    </a>
                </div>
            @endforeach
        </div>
    </section>

    <!-- ── 8. Bottom Action Banner ── -->
    <div class="nx-section-wrap" style="padding-top: 0;">
        <div class="nx-bottom-banner">
            <h2 style="font-family: 'Cairo', sans-serif; font-size: clamp(28px, 3.5vw, 38px); font-weight: 800; color: #FFFFFF; margin-bottom: 12px;">
                {{ app()->getLocale() === 'ar' ? 'جاهز لنقل فريقك إلى بيئة عمل المستقبل؟' : 'Ready to Elevate Your Team’s Workspace?' }}
            </h2>
            <p style="font-family: 'Cairo', sans-serif; font-size: 15.5px; color: #EDE6D9; max-width: 580px; margin: 0 auto 32px;">
                {{ app()->getLocale() === 'ar' ? 'انضم إلى الشركات الرائدة التي تبني ثقافة عمل قوية وحية مع UlaSpace.' : 'Join high-performing distributed teams building real culture and presence with UlaSpace.' }}
            </p>
            <div class="flex items-center justify-center gap-4 flex-wrap">
                <a href="{{ route('register') }}" class="nx-btn-primary-action" style="padding: 14px 32px; font-size: 16px;">
                    <span>{{ app()->getLocale() === 'ar' ? 'ابدأ التجربة المجانية الآن' : 'Start Free Trial Today' }}</span>
                </a>
                <a href="{{ route('login') }}" class="nx-btn-secondary-action" style="padding: 14px 32px; font-size: 16px; background: rgba(255,255,255,0.1); color: white; border-color: rgba(255,255,255,0.2);">
                    <span>{{ __('تسجيل الدخول') }}</span>
                </a>
            </div>
        </div>
    </div>

@endsection

@section('scripts')
<script nonce="{{ $cspNonce ?? '' }}">
    function switchSpaceTab(index) {
        const buttons = document.querySelectorAll('.nx-space-tab-card');
        buttons.forEach((btn, idx) => {
            if (idx === index) {
                btn.classList.add('active');
            } else {
                btn.classList.remove('active');
            }
        });
    }
</script>
@endsection
