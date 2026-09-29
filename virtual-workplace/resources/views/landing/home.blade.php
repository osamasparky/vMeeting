@extends('landing.layout')

@section('title', 'UlaSpace — مساحات العمل والمكاتب الافتراضية الذكية')
@section('meta_description', 'مساحات عمل افتراضية غامرة تجمع فرق العمل عن بعد مع صوت وفيديو مكاني، وتخطيط خرائط المكاتب، والاجتماعات التفاعلية.')

@section('styles')
<style>
    /* ── 1. Hero Block (Matches 01-Landing) ── */
    .ula-hero-section {
        position: relative;
        background: var(--ula-surface-dark);
        color: var(--ula-text-on-dark);
        overflow: hidden;
    }

    .ula-hero-stripes {
        position: absolute;
        inset: 0;
        background: repeating-linear-gradient(135deg, var(--ula-media-stripe-a) 0 14px, var(--ula-media-stripe-b) 14px 28px);
    }

    .ula-hero-scrim {
        position: absolute;
        inset: 0;
        background: linear-gradient(to left, rgba(14,28,23,0.94) 0%, rgba(14,28,23,0.72) 50%, rgba(14,28,23,0.35) 100%);
        pointer-events: none;
    }

    .ula-hero-grid {
        position: relative;
        z-index: 2;
        max-width: var(--ula-layout-container-max);
        margin-inline: auto;
        padding: 96px 64px 88px;
        display: grid;
        grid-template-columns: minmax(0, 1.1fr) minmax(0, 1fr);
        gap: 64px;
        align-items: center;
    }

    .ula-hero-copy {
        display: flex;
        flex-direction: column;
        gap: 28px;
        align-items: flex-start;
    }

    .ula-hero-eyebrow {
        align-self: flex-start;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        height: 32px;
        padding: 0 14px;
        border-radius: var(--ula-radius-pill);
        background: var(--ula-surface-gold-soft);
        color: var(--ula-tone-gold-fg);
        font-family: var(--ula-font-en);
        font-size: 12px;
        font-weight: 500;
        letter-spacing: var(--ula-tracking-brand);
        text-transform: uppercase;
    }

    .ula-hero-title {
        font-size: 56px;
        font-weight: 600;
        line-height: 1.25;
        color: var(--ula-text-on-dark);
    }

    .ula-hero-subtitle {
        font-family: var(--ula-font-en);
        font-size: 20px;
        font-weight: 300;
        line-height: 1.5;
        color: var(--ula-text-on-dark-muted);
        max-width: 560px;
    }

    .ula-hero-actions {
        display: flex;
        gap: 12px;
        flex-wrap: wrap;
    }

    .ula-btn-hero-primary {
        height: 52px;
        padding: 0 26px;
        border-radius: var(--ula-radius-lg);
        border: 0;
        background: var(--ula-surface-raised);
        color: var(--ula-text-primary);
        font-family: var(--ula-font-ar);
        font-size: 17px;
        font-weight: 600;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        text-decoration: none;
        cursor: pointer;
        transition: all var(--ula-duration-fast) var(--ula-ease-out);
    }

    .ula-btn-hero-primary:hover {
        background: var(--ula-surface-page-alt);
        color: var(--ula-text-primary);
    }

    .ula-btn-hero-secondary {
        height: 52px;
        padding: 0 26px;
        border-radius: var(--ula-radius-lg);
        border: var(--ula-border-width-hairline) solid var(--ula-border-on-dark);
        background: var(--ula-control-dark-fill);
        color: var(--ula-text-on-dark);
        font-family: var(--ula-font-ar);
        font-size: 17px;
        font-weight: 600;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        text-decoration: none;
        cursor: pointer;
        transition: all var(--ula-duration-fast) var(--ula-ease-out);
    }

    .ula-btn-hero-secondary:hover {
        background: var(--ula-control-dark-fill-hover);
        color: var(--ula-text-on-dark);
    }

    /* Hero Glass Panel */
    .ula-hero-panel {
        position: relative;
        border-radius: 28px;
        background: var(--ula-surface-capsule-strong);
        border: var(--ula-border-width-hairline) solid var(--ula-border-on-dark);
        padding: 24px;
        display: flex;
        flex-direction: column;
        gap: 16px;
        box-shadow: var(--ula-shadow-xl);
    }

    .ula-hero-panel-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    .ula-hero-panel-title-ar {
        font-size: 17px;
        font-weight: 600;
        color: var(--ula-text-on-dark);
    }

    .ula-hero-panel-title-en {
        font-family: var(--ula-font-en);
        font-size: 12px;
        color: var(--ula-text-on-dark-subtle);
    }

    .ula-hero-live-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        height: 29px;
        padding: 0 12px;
        border-radius: var(--ula-radius-pill);
        background: var(--ula-tone-palm-bg);
        color: var(--ula-tone-palm-fg);
        font-size: 13px;
        font-weight: 500;
    }

    .ula-hero-live-dot {
        width: 7px;
        height: 7px;
        border-radius: var(--ula-radius-pill);
        background: var(--ula-palm-400);
    }

    .ula-hero-preview-box {
        height: 220px;
        border-radius: 20px;
        background: repeating-linear-gradient(135deg, var(--ula-palm-800) 0 12px, var(--ula-palm-900) 12px 24px);
        position: relative;
        display: flex;
        align-items: flex-end;
        justify-content: center;
        padding: 12px;
        overflow: hidden;
    }

    .ula-caption-pill {
        font-family: var(--ula-font-mono);
        font-size: 11px;
        color: var(--ula-text-primary);
        background: rgba(251,248,242,0.72);
        padding: 6px 10px;
        border-radius: var(--ula-radius-pill);
        direction: ltr;
        unicode-bidi: isolate;
    }

    .ula-hero-room-card {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 12px 14px;
        border-radius: 14px;
        background: var(--ula-control-dark-fill);
        border: var(--ula-border-width-hairline) solid var(--ula-border-on-dark-subtle);
    }

    .ula-hero-room-icon-box {
        width: 36px;
        height: 36px;
        border-radius: 10px;
        background: var(--ula-control-dark-fill-strong);
        color: var(--ula-icon-highlight);
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }

    /* ── 2. Spaces Section (Matches 01-Landing) ── */
    .ula-spaces-section {
        padding: 80px 64px;
        max-width: var(--ula-layout-container-max);
        margin-inline: auto;
        display: grid;
        grid-template-columns: minmax(0, 1fr) minmax(0, 1fr);
        gap: 64px;
        align-items: center;
    }

    .ula-spaces-visual-box {
        height: 420px;
        border-radius: 28px;
        background: repeating-linear-gradient(135deg, var(--ula-sand-400) 0 14px, var(--ula-sand-500) 14px 28px);
        display: flex;
        align-items: flex-end;
        justify-content: center;
        padding: 16px;
        overflow: hidden;
        border: var(--ula-border-width-hairline) solid var(--ula-border-default);
    }

    .ula-spaces-content {
        display: flex;
        flex-direction: column;
        gap: 24px;
    }

    .ula-eyebrow-gold {
        font-family: var(--ula-font-en);
        font-size: 11px;
        font-weight: 500;
        letter-spacing: var(--ula-tracking-brand);
        text-transform: uppercase;
        color: var(--ula-tone-gold-fg);
    }

    .ula-section-heading {
        font-size: 34px;
        font-weight: 600;
        line-height: 1.25;
        color: var(--ula-text-primary);
    }

    .ula-section-subtext {
        font-family: var(--ula-font-en);
        font-size: 17px;
        font-weight: 300;
        line-height: 1.6;
        color: var(--ula-text-secondary);
    }

    .ula-space-tile {
        display: flex;
        align-items: center;
        gap: 14px;
        padding: 14px 16px;
        border-radius: 16px;
        background: var(--ula-surface-card);
        border: var(--ula-border-width-hairline) solid var(--ula-border-subtle);
    }

    .ula-space-tile-icon {
        width: 40px;
        height: 40px;
        border-radius: 12px;
        background: var(--ula-tone-gold-bg);
        color: var(--ula-tone-gold-fg);
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }

    /* ── 3. Platform Capabilities Grid ── */
    .ula-capabilities-section {
        padding: 80px 64px;
        background: var(--ula-surface-sunken);
        border-top: var(--ula-border-width-hairline) solid var(--ula-border-subtle);
        border-bottom: var(--ula-border-width-hairline) solid var(--ula-border-subtle);
    }

    .ula-capabilities-container {
        max-width: var(--ula-layout-container-max);
        margin-inline: auto;
        display: flex;
        flex-direction: column;
        gap: 40px;
    }

    .ula-capabilities-grid {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 24px;
    }

    .ula-feature-card {
        border-radius: 20px;
        background: var(--ula-surface-card);
        border: var(--ula-border-width-hairline) solid var(--ula-border-subtle);
        overflow: hidden;
        display: flex;
        flex-direction: column;
        box-shadow: var(--ula-shadow-xs);
    }

    .ula-feature-media {
        height: 180px;
        background: repeating-linear-gradient(135deg, var(--ula-sand-400) 0 12px, var(--ula-sand-500) 12px 24px);
    }

    .ula-feature-body {
        padding: 24px;
        display: flex;
        flex-direction: column;
        gap: 12px;
    }

    .ula-feature-icon-box {
        width: 44px;
        height: 44px;
        border-radius: 12px;
        background: var(--ula-tone-gold-bg);
        color: var(--ula-tone-gold-fg);
        display: inline-flex;
        align-items: center;
        justify-content: center;
        margin-top: -46px;
        box-shadow: 0 0 0 4px var(--ula-surface-card);
    }

    /* ── 4. Quote Section (Heritage) ── */
    .ula-quote-section {
        padding: 80px 64px;
        max-width: var(--ula-layout-container-max);
        margin-inline: auto;
        display: flex;
        justify-content: center;
    }

    .ula-quote-box {
        max-width: 880px;
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 16px;
        text-align: center;
    }

    .ula-quote-text {
        font-size: 30px;
        font-weight: 600;
        line-height: 1.5;
        color: var(--ula-palm-800);
    }

    /* ── 5. Pricing Section (Matches 01-Landing) ── */
    .ula-pricing-section {
        padding: 80px 64px;
        background: var(--ula-surface-sunken);
        border-top: var(--ula-border-width-hairline) solid var(--ula-border-subtle);
    }

    .ula-pricing-container {
        max-width: var(--ula-layout-container-max);
        margin-inline: auto;
        display: flex;
        flex-direction: column;
        gap: 40px;
    }

    .ula-pricing-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 20px;
        align-items: stretch;
    }

    .ula-plan-card {
        position: relative;
        padding: 28px 24px;
        border-radius: 20px;
        background: var(--ula-surface-card);
        border: var(--ula-border-width-hairline) solid var(--ula-border-subtle);
        display: flex;
        flex-direction: column;
        gap: 20px;
        box-shadow: var(--ula-shadow-xs);
    }

    .ula-plan-card.featured {
        border: 2px solid var(--ula-accent-default);
        box-shadow: var(--ula-shadow-md);
    }

    .ula-plan-popular-badge {
        position: absolute;
        top: -14px;
        inset-inline-start: 24px;
        height: 28px;
        padding: 0 12px;
        border-radius: var(--ula-radius-pill);
        background: var(--ula-highlight-default);
        color: var(--ula-text-primary);
        font-size: 12px;
        font-weight: 600;
        display: inline-flex;
        align-items: center;
    }

    .ula-plan-price-num {
        font-family: var(--ula-font-mono);
        font-size: 40px;
        font-weight: 500;
        color: var(--ula-text-primary);
    }

    .ula-btn-plan {
        height: 44px;
        border-radius: 14px;
        font-family: var(--ula-font-ar);
        font-size: 15px;
        font-weight: 600;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        text-decoration: none;
        cursor: pointer;
        transition: all var(--ula-duration-fast) var(--ula-ease-out);
    }

    .ula-btn-plan-outline {
        border: var(--ula-border-width-hairline) solid var(--ula-border-default);
        background: transparent;
        color: var(--ula-text-primary);
    }

    .ula-btn-plan-outline:hover {
        background: var(--ula-surface-page-alt);
    }

    .ula-btn-plan-primary {
        border: var(--ula-border-width-hairline) solid var(--ula-accent-default);
        background: var(--ula-accent-default);
        color: var(--ula-accent-fg);
    }

    .ula-btn-plan-primary:hover {
        background: var(--ula-accent-hover);
    }

    /* ── 6. Bottom Banner CTA ── */
    .ula-bottom-cta-wrap {
        padding: 80px 64px;
        max-width: var(--ula-layout-container-max);
        margin-inline: auto;
    }

    .ula-bottom-cta-banner {
        padding: 64px;
        border-radius: 28px;
        background: var(--ula-surface-dark);
        color: var(--ula-text-on-dark);
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 40px;
    }

    @media (max-width: 1024px) {
        .ula-hero-grid { grid-template-columns: 1fr; padding: 64px 32px 48px; }
        .ula-spaces-section { grid-template-columns: 1fr; padding: 64px 32px; }
        .ula-capabilities-section { padding: 64px 32px; }
        .ula-capabilities-grid { grid-template-columns: 1fr; }
        .ula-pricing-section { padding: 64px 32px; }
        .ula-pricing-grid { grid-template-columns: repeat(2, 1fr); }
        .ula-bottom-cta-banner { flex-direction: column; text-align: center; padding: 48px 32px; }
    }

    @media (max-width: 640px) {
        .ula-hero-title { font-size: 38px; }
        .ula-hero-grid { padding: 48px 20px 36px; }
        .ula-spaces-section { padding: 48px 20px; }
        .ula-pricing-grid { grid-template-columns: 1fr; }
        .ula-bottom-cta-wrap { padding: 48px 20px; }
    }
</style>
@endsection

@section('content')

    @php
        $heroSec = $sections->get('home_hero');
        $spatialSec = $sections->get('home_spatial_presence');
        $spacesSec = $sections->get('home_floorplan_editor') ?? $sections->get('home_spaces');
        $benefitsSec = $sections->get('home_collaboration') ?? $sections->get('home_benefits');
        $meetingsSec = $sections->get('home_meetings');
        $identitySec = $sections->get('home_company_workspace') ?? $sections->get('home_identity');
        $pricingSec = $sections->get('home_pricing');
        $ctaSec = $sections->get('home_cta');
    @endphp

    <!-- ── 01. Hero Section (Screen 01) ── -->
    <section id="hero" class="ula-hero-section">
        @if(!$heroSec?->image_url)
            <div class="ula-hero-stripes"></div>
        @endif
        <div class="ula-hero-scrim"></div>

        <div class="ula-hero-grid">
            <!-- Copy column -->
            <div class="ula-hero-copy">
                <span class="ula-hero-eyebrow">
                    <span class="ms" style="font-size: 16px;">auto_awesome</span>
                    Next-Generation Spatial Virtual Workplace
                </span>

                <div style="display: flex; flex-direction: column; gap: 12px;">
                    <h1 class="ula-hero-title">
                        {{ $heroSec?->title_ar ?: 'مساحات عمل افتراضية ذكية تجمع الفرق عن بُعد بانسيابية تامة.' }}
                    </h1>
                    <p class="ula-hero-subtitle">
                        {{ $heroSec?->title_en ?: 'Next-generation spatial virtual workplaces where distributed teams meet, collaborate, and build culture naturally.' }}
                    </p>
                </div>

                <div class="ula-hero-actions">
                    @auth
                        <a href="{{ route('office') }}" class="ula-btn-hero-primary">
                            <span class="ms" style="font-size: 22px;">login</span>
                            <span>{{ __('ادخل إلى المكتب') }}</span>
                        </a>
                        <a href="{{ route('dashboard') }}" class="ula-btn-hero-secondary">
                            <span class="ms" style="font-size: 22px;">dashboard</span>
                            <span>{{ __('لوحة التحكم') }}</span>
                        </a>
                    @else
                        <a href="{{ $heroSec?->getContentValue('cta_primary_link', route('register')) }}" class="ula-btn-hero-primary">
                            <span class="ms" style="font-size: 22px;">login</span>
                            <span>{{ app()->getLocale() === 'ar' ? ($heroSec?->getContentValue('cta_primary_text_ar') ?: 'ادخل إلى المكتب') : ($heroSec?->getContentValue('cta_primary_text_en') ?: 'Enter Workplace') }}</span>
                        </a>
                        <a href="{{ $heroSec?->getContentValue('cta_secondary_link', route('login')) }}" class="ula-btn-hero-secondary">
                            <span class="ms" style="font-size: 22px;">dashboard</span>
                            <span>{{ app()->getLocale() === 'ar' ? ($heroSec?->getContentValue('cta_secondary_text_ar') ?: 'تسجيل الدخول') : ($heroSec?->getContentValue('cta_secondary_text_en') ?: 'Sign In') }}</span>
                        </a>
                    @endauth
                </div>
            </div>

            <!-- Floor Preview Glass Panel -->
            <div class="ula-hero-panel">
                <div class="ula-hero-panel-header">
                    <div style="display: flex; flex-direction: column; gap: 2px;">
                        <span class="ula-hero-panel-title-ar">الطابق الأول · المقر الرئيسي</span>
                        <span class="ula-hero-panel-title-en">Floor 1 · Main Headquarters</span>
                    </div>
                    <span class="ula-hero-live-badge">
                        <span class="ula-hero-live-dot"></span>
                        <span style="font-family: var(--ula-font-mono); direction: ltr; unicode-bidi: isolate;">18</span>
                        <span>عضواً متصلاً</span>
                    </span>
                </div>

                <div class="ula-hero-preview-box">
                    <span class="ula-caption-pill">floor-preview.png</span>
                </div>

                <!-- Room 1 -->
                <div class="ula-hero-room-card">
                    <span class="ula-hero-room-icon-box">
                        <span class="ms" style="font-size: 20px;">meeting_room</span>
                    </span>
                    <div style="flex: 1; display: flex; flex-direction: column;">
                        <span style="font-size: 15px; font-weight: 600;">قاعة النخيل</span>
                        <span style="font-family: var(--ula-font-en); font-size: 12px; color: var(--ula-text-on-dark-subtle);">Palm Boardroom · 4 In Call</span>
                    </div>
                    <span style="font-size: 13px; color: var(--ula-palm-300);">في مكالمة</span>
                </div>

                <!-- Room 2 -->
                <div class="ula-hero-room-card">
                    <span class="ula-hero-room-icon-box">
                        <span class="ms" style="font-size: 20px;">chair</span>
                    </span>
                    <div style="flex: 1; display: flex; flex-direction: column;">
                        <span style="font-size: 15px; font-weight: 600;">مساحة الابتكار</span>
                        <span style="font-family: var(--ula-font-en); font-size: 12px; color: var(--ula-text-on-dark-subtle);">Innovation Lounge · 2 Desks</span>
                    </div>
                    <span style="font-size: 13px; color: var(--ula-gold-300);">مكتبان متاحان</span>
                </div>
            </div>
        </div>
    </section>

    <!-- ── 02. Spaces Section (#spaces) ── -->
    <section id="spaces" class="ula-spaces-section">
        <div class="ula-spaces-visual-box">
            <span class="ula-caption-pill">office-floor.jpg</span>
        </div>

        <div class="ula-spaces-content">
            <span class="ula-eyebrow-gold">{{ $spacesSec?->badge ?: 'المساحات الذكية' }}</span>
            <div style="display: flex; flex-direction: column; gap: 8px;">
                <h2 class="ula-section-heading">{{ $spacesSec?->title_ar ?: 'مكتب افتراضي يشبه مكتبك الحقيقي' }}</h2>
                <p class="ula-section-subtext">{{ $spacesSec?->title_en ?: 'Design your floor once, and every teammate walks in to the same place — desks, meeting rooms, lounges, and quiet corners, all where you put them.' }}</p>
            </div>

            <div style="display: flex; flex-direction: column; gap: 12px;">
                <div class="ula-space-tile">
                    <span class="ula-space-tile-icon"><span class="ms" style="font-size: 22px;">chair</span></span>
                    <div style="display: flex; flex-direction: column;">
                        <span style="font-size: 15px; font-weight: 600;">مكاتب فردية ومساحات عمل مشتركة</span>
                        <span style="font-family: var(--ula-font-en); font-size: 13px; color: var(--ula-text-secondary);">Private desks and open collaboration zones</span>
                    </div>
                </div>

                <div class="ula-space-tile">
                    <span class="ula-space-tile-icon"><span class="ms" style="font-size: 22px;">meeting_room</span></span>
                    <div style="display: flex; flex-direction: column;">
                        <span style="font-size: 15px; font-weight: 600;">قاعات اجتماعات قابلة للقفل</span>
                        <span style="font-family: var(--ula-font-en); font-size: 13px; color: var(--ula-text-secondary);">Lockable meeting rooms with knock-to-enter</span>
                    </div>
                </div>

                <div class="ula-space-tile">
                    <span class="ula-space-tile-icon"><span class="ms" style="font-size: 22px;">workspaces</span></span>
                    <div style="display: flex; flex-direction: column;">
                        <span style="font-size: 15px; font-weight: 600;">صالات استراحة وزوايا هادئة</span>
                        <span style="font-family: var(--ula-font-en); font-size: 13px; color: var(--ula-text-secondary);">Lounges and quiet corners for focus work</span>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ── 03. Platform Capabilities (#benefits) ── -->
    <section id="benefits" class="ula-capabilities-section">
        <div class="ula-capabilities-container">
            <div style="display: flex; flex-direction: column; gap: 8px; max-width: 760px;">
                <span class="ula-eyebrow-gold">Platform Capabilities</span>
                <h2 class="ula-section-heading">{{ $benefitsSec?->title_ar ?: 'مصمم لبيئات العمل الحديثة التي تجمع بين التراث والابتكار' }}</h2>
                <p class="ula-section-subtext">{{ $benefitsSec?->title_en ?: 'Everything you need to run a high-trust, collaborative virtual headquarters.' }}</p>
            </div>

            <div class="ula-capabilities-grid">
                <!-- Feature 1 -->
                <div class="ula-feature-card">
                    <div class="ula-feature-media"></div>
                    <div class="ula-feature-body">
                        <span class="ula-feature-icon-box"><span class="ms" style="font-size: 24px;">graphic_eq</span></span>
                        <h3 style="font-size: 21px; font-weight: 600; line-height: 1.4;">الصوت والفيديو المكاني (Spatial Audio)</h3>
                        <p style="font-size: 15px; line-height: 1.6; color: var(--ula-text-body);">تواصل تلقائي يحاكي الواقع تماماً، حيث يقوى الصوت تدريجياً كلما اقتربت من زملائك على خريطة المقر دون الحاجة لروابط مكالمات معقدة.</p>
                        <span style="font-family: var(--ula-font-en); font-size: 13px; color: var(--ula-text-muted);">Proximity-based WebRTC Mesh</span>
                    </div>
                </div>

                <!-- Feature 2 -->
                <div class="ula-feature-card">
                    <div class="ula-feature-media"></div>
                    <div class="ula-feature-body">
                        <span class="ula-feature-icon-box"><span class="ms" style="font-size: 24px;">lock</span></span>
                        <h3 style="font-size: 21px; font-weight: 600; line-height: 1.4;">قاعات اجتماعات ذكية وأبواب خاصة</h3>
                        <p style="font-size: 15px; line-height: 1.6; color: var(--ula-text-body);">أبواب غرف قابلة للقفل مع جرس استئذان ومشاركة شاشة بدقة فائقة وعزل صوتي كامل لضمان سرية المحادثات الاستراتيجية.</p>
                        <span style="font-family: var(--ula-font-en); font-size: 13px; color: var(--ula-text-muted);">Isolated Audio Zones & Knocking</span>
                    </div>
                </div>

                <!-- Feature 3 -->
                <div class="ula-feature-card">
                    <div class="ula-feature-media"></div>
                    <div class="ula-feature-body">
                        <span class="ula-feature-icon-box"><span class="ms" style="font-size: 24px;">architecture</span></span>
                        <h3 style="font-size: 21px; font-weight: 600; line-height: 1.4;">محرر الخرائط ومكتبة الأثاث التفاعلي</h3>
                        <p style="font-size: 15px; line-height: 1.6; color: var(--ula-text-body);">صمم مخطط مكتبك بالكامل بسحب وإفلات المكاتب الفاخرة، والنباتات، والسبورات البيضاء، والشاشات التفاعلية بسهولة.</p>
                        <span style="font-family: var(--ula-font-en); font-size: 13px; color: var(--ula-text-muted);">Custom Floorplan Architect & Catalog</span>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ── 04. Quote Section (#identity) ── -->
    <section id="identity" class="ula-quote-section">
        <div class="ula-quote-box">
            <span class="ms" style="font-size: 40px; color: var(--ula-highlight-default);">format_quote</span>
            <blockquote class="ula-quote-text">
                "المكان ليس مجرد جدران، بل مساحة تلتقي فيها العقول وتتدفق فيها الأفكار بحرية وشغف."
            </blockquote>
            <span style="font-family: var(--ula-font-en); font-size: 14px; color: var(--ula-text-secondary);">
                UlaSpace — Designed with Heritage & Modern Luxury
            </span>
        </div>
    </section>

    <!-- ── 05. Subscription Plans Section (#pricing) ── -->
    <section id="pricing" class="ula-pricing-section">
        <div class="ula-pricing-container">
            <div style="display: flex; flex-direction: column; gap: 8px; align-items: center; text-align: center;">
                <h2 class="ula-section-heading">باقة تناسب حجم فريقك</h2>
                <p class="ula-section-subtext">Start free, upgrade any time as your team grows.</p>
            </div>

            <div class="ula-pricing-grid">
                @php
                    $planNameAr = [
                        'Free' => 'مجاني',
                        'Starter' => 'مبتدئ',
                        'Business' => 'أعمال',
                        'Enterprise' => 'مؤسسات'
                    ];
                @endphp

                @foreach($plans as $plan)
                    @php
                        $isPopular = ($plan->slug === 'business' || $plan->slug === 'pro' || strtolower($plan->name) === 'business');
                    @endphp
                    <div class="ula-plan-card {{ $isPopular ? 'featured' : '' }}">
                        @if($isPopular)
                            <span class="ula-plan-popular-badge">الأكثر شيوعاً</span>
                        @endif

                        <div style="display: flex; flex-direction: column; gap: 2px;">
                            <span style="font-size: 21px; font-weight: 600;">{{ $planNameAr[$plan->name] ?? $plan->name }}</span>
                            <span style="font-family: var(--ula-font-en); font-size: 13px; color: var(--ula-text-muted);">{{ $plan->name }}</span>
                        </div>

                        <div style="display: flex; align-items: baseline; gap: 6px; direction: ltr; justify-content: flex-end;">
                            <span class="ula-plan-price-num" data-plan-usd="{{ $plan->price }}">${{ $plan->price }}</span>
                            <span style="font-family: var(--ula-font-en); font-size: 14px; color: var(--ula-text-muted);">/mo</span>
                        </div>

                        <span style="display: flex; align-items: center; gap: 8px; font-size: 14px; color: var(--ula-text-secondary);">
                            <span class="ms" style="font-size: 18px; color: var(--ula-palm-500);">group</span>
                            <span style="font-family: var(--ula-font-mono); direction: ltr; unicode-bidi: isolate;">{{ $plan->isUnlimitedSeats() ? '∞' : $plan->seat_limit }}</span>
                            <span>مقعداً</span>
                        </span>

                        <div style="display: flex; flex-direction: column; gap: 10px; flex: 1;">
                            @if(is_array($plan->features))
                                @foreach(array_slice($plan->features, 0, 3) as $feature)
                                    <span style="display: flex; align-items: center; gap: 8px; font-size: 14px; color: var(--ula-text-body);">
                                        <span class="ms" style="font-size: 18px; color: var(--ula-palm-500);">check_circle</span>
                                        <span>{{ $feature }}</span>
                                    </span>
                                @endforeach
                            @endif
                        </div>

                        <a href="{{ route('register', ['plan' => $plan->slug]) }}" class="ula-btn-plan {{ $isPopular ? 'ula-btn-plan-primary' : 'ula-btn-plan-outline' }}">
                            ابدأ الآن
                        </a>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <!-- Hidden anchor targets to preserve smooth scroll targets without affecting layout -->
    <div id="spatial-presence" style="display: none;"></div>
    <div id="meetings" style="display: none;"></div>

    <!-- ── 06. Bottom Action Banner ── -->
    <div class="ula-bottom-cta-wrap">
        <div class="ula-bottom-cta-banner">
            <div style="display: flex; flex-direction: column; gap: 10px; max-width: 700px;">
                <h2 style="font-size: 34px; font-weight: 600; line-height: 1.3;">
                    {{ app()->getLocale() === 'ar' ? ($ctaSec?->title_ar ?: 'جاهز لنقل فريقك إلى بيئة عمل المستقبل؟') : ($ctaSec?->title_en ?: 'Ready to Elevate Your Team’s Workspace?') }}
                </h2>
                <p style="font-size: 17px; line-height: 1.6; color: var(--ula-sand-400);">
                    {{ app()->getLocale() === 'ar' ? ($ctaSec?->subtitle_ar ?: 'انضم إلى المئات من الشركات الرائدة التي تبني ثقافة عمل قوية ومتصلة مع UlaSpace.') : ($ctaSec?->subtitle_en ?: 'Join high-performing distributed teams building real culture and presence with UlaSpace.') }}
                </p>
            </div>
            <div style="display: flex; gap: 12px; flex-wrap: wrap;">
                <a href="{{ $ctaSec?->getContentValue('cta_primary_link', route('register')) }}" class="ula-btn-hero-primary">
                    {{ app()->getLocale() === 'ar' ? ($ctaSec?->getContentValue('cta_primary_text_ar') ?: 'ابدأ التجربة المجانية الآن') : ($ctaSec?->getContentValue('cta_primary_text_en') ?: 'Start Free Trial') }}
                </a>
                <a href="{{ $ctaSec?->getContentValue('cta_secondary_link', route('login')) }}" class="ula-btn-hero-secondary">
                    {{ app()->getLocale() === 'ar' ? ($ctaSec?->getContentValue('cta_secondary_text_ar') ?: 'تسجيل الدخول') : ($ctaSec?->getContentValue('cta_secondary_text_en') ?: 'Sign In') }}
                </a>
            </div>
        </div>
    </div>

@endsection

@section('scripts')
@endsection
