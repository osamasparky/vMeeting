@extends('landing.layout')

@section('title', 'UlaSpace — مساحات العمل والمكاتب الافتراضية الذكية')
@section('meta_description', 'مساحات عمل افتراضية غامرة تجمع فرق العمل عن بعد مع صوت وفيديو مكاني، وتخطيط خرائط المكاتب، والاجتماعات التفاعلية.')

@section('styles')
<style>
    /* ── 1. Hero Block ── */
    .nx-hero-block {
        position: relative;
        background: var(--ula-surface-dark);
        min-height: 520px;
        overflow: hidden;
        display: flex;
        align-items: center;
        padding: var(--ula-space-11) var(--ula-space-10);
    }

    .nx-hero-stripes-bg {
        position: absolute;
        inset: 0;
        background-color: var(--ula-media-stripe-a);
        background-image: repeating-linear-gradient(
            -45deg,
            var(--ula-media-stripe-a) 0px,
            var(--ula-media-stripe-a) 8px,
            var(--ula-media-stripe-b) 8px,
            var(--ula-media-stripe-b) 24px
        );
        opacity: 0.9;
    }

    .nx-hero-scrim {
        position: absolute;
        inset: 0;
        background: var(--ula-scrim-hero);
        pointer-events: none;
    }

    .nx-hero-inner {
        position: relative;
        z-index: 2;
        max-width: var(--ula-layout-container-max);
        margin-inline: auto;
        width: 100%;
        display: grid;
        grid-template-columns: minmax(0, 1.1fr) minmax(0, 1fr);
        gap: var(--ula-space-11);
        align-items: center;
    }

    .nx-hero-copy {
        display: flex;
        flex-direction: column;
        align-items: flex-start;
        gap: var(--ula-space-7);
    }

    .nx-hero-copy .ula-headline-ar {
        font-size: var(--ula-size-display-lg);
        color: var(--ula-text-on-dark);
    }

    .nx-hero-copy .ula-headline-en {
        font-size: var(--ula-size-display-en);
        color: var(--ula-text-on-dark-muted);
        max-width: 560px;
    }

    .nx-hero-body {
        font-family: var(--ula-font-ar);
        font-size: var(--ula-size-body-lg);
        line-height: var(--ula-lh-body);
        color: var(--ula-text-on-dark-muted);
        max-width: 560px;
    }

    .nx-hero-buttons {
        display: flex;
        gap: var(--ula-space-4);
        flex-wrap: wrap;
    }

    /* Dark-chrome secondary button — no matching x-btn variant, so shape comes
       from the shared .ula-btn/.ula-btn--lg classes and colors from control/dark-* tokens. */
    .nx-btn-dark-outline {
        background: var(--ula-control-dark-fill);
        border-color: var(--ula-control-dark-border);
        color: var(--ula-text-on-dark);
    }
    .nx-btn-dark-outline:hover {
        background: var(--ula-control-dark-fill-hover);
        color: var(--ula-text-on-dark);
    }

    /* Hero glass panel — floor preview + live occupancy */
    .nx-hero-panel {
        position: relative;
        border-radius: var(--ula-radius-xl);
        background: var(--ula-surface-capsule);
        backdrop-filter: var(--ula-backdrop-blur);
        -webkit-backdrop-filter: var(--ula-backdrop-blur);
        border: var(--ula-border-width-hairline) solid var(--ula-border-on-dark);
        padding: var(--ula-space-7);
        display: flex;
        flex-direction: column;
        gap: var(--ula-space-5);
        box-shadow: var(--ula-shadow-xl);
    }

    .nx-hero-panel-preview {
        height: 220px;
        border-radius: var(--ula-radius-lg);
        background: repeating-linear-gradient(135deg, var(--ula-surface-dark-alt) 0 12px, var(--ula-surface-dark) 12px 24px);
        position: relative;
        display: flex;
        align-items: flex-end;
        justify-content: center;
        padding: var(--ula-space-4);
    }

    .nx-media-caption {
        font-family: var(--ula-font-mono);
        font-size: var(--ula-size-label);
        color: var(--ula-media-label-fg);
        background: var(--ula-media-label-bg);
        padding: var(--ula-space-2) var(--ula-space-4);
        border-radius: var(--ula-radius-pill);
        direction: ltr;
        unicode-bidi: isolate;
    }

    .nx-hero-room-row {
        display: flex;
        align-items: center;
        gap: var(--ula-space-4);
        padding: var(--ula-space-4);
        border-radius: var(--ula-radius-md);
        background: var(--ula-control-dark-fill);
        border: var(--ula-border-width-hairline) solid var(--ula-control-dark-border-subtle);
    }

    .nx-hero-room-icon {
        width: 36px;
        height: 36px;
        min-width: 36px;
        border-radius: var(--ula-radius-sm);
        background: var(--ula-control-dark-fill-strong);
        color: var(--ula-icon-highlight);
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }

    /* ── 2. 4-Pillars Features Strip ── */
    .nx-pillars-strip {
        background: var(--ula-surface-page);
        padding: var(--ula-space-9) var(--ula-space-8);
        border-bottom: var(--ula-border-width-hairline) solid var(--ula-border-subtle);
    }

    .nx-pillars-container {
        max-width: var(--ula-layout-container-max);
        margin-inline: auto;
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: var(--ula-space-7);
    }

    .nx-pillar-box {
        display: flex;
        flex-direction: column;
        align-items: center;
        text-align: center;
        gap: var(--ula-space-3);
        padding: var(--ula-space-3);
    }

    .nx-pillar-icon {
        width: 48px;
        height: 48px;
        border-radius: var(--ula-radius-md);
        background: var(--ula-surface-gold-soft);
        color: var(--ula-icon-highlight);
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }

    .nx-pillar-ar {
        font-family: var(--ula-font-ar);
        font-size: var(--ula-size-body);
        font-weight: var(--ula-weight-semibold);
        color: var(--ula-text-primary);
        line-height: var(--ula-lh-heading);
    }

    .nx-pillar-en {
        font-family: var(--ula-font-en);
        font-size: var(--ula-size-xs);
        color: var(--ula-text-secondary);
    }

    /* ── 3. General Section Layout ── */
    .nx-section-wrap {
        padding: var(--ula-layout-section-y) var(--ula-space-8);
        max-width: var(--ula-layout-container-max);
        margin-inline: auto;
    }

    .nx-section-header {
        text-align: center;
        max-width: var(--ula-layout-container-narrow);
        margin-inline: auto;
        margin-bottom: var(--ula-space-10);
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: var(--ula-space-3);
    }

    .nx-section-header .ula-headline-group {
        align-items: center;
    }

    .nx-section-badge {
        display: inline-flex;
        align-items: center;
        gap: var(--ula-space-2);
        padding: var(--ula-space-2) var(--ula-space-5);
        border-radius: var(--ula-radius-pill);
        background: var(--ula-tone-gold-bg);
        color: var(--ula-tone-gold-fg);
        font-size: var(--ula-size-xs);
        font-weight: var(--ula-weight-bold);
        letter-spacing: 0.04em;
    }

    .nx-section-header .ula-headline-ar {
        font-size: var(--ula-size-h1);
    }

    .nx-section-header .ula-headline-en {
        font-size: var(--ula-size-h1-en);
    }

    .nx-section-desc {
        font-family: var(--ula-font-ar);
        font-size: var(--ula-size-body);
        color: var(--ula-text-secondary);
        line-height: var(--ula-lh-body);
    }

    /* ── Spaces Explorer ── */
    .nx-spaces-split {
        display: grid;
        grid-template-columns: 1fr 1.2fr;
        gap: var(--ula-space-8);
        align-items: center;
        background: var(--ula-surface-raised);
        border-radius: var(--ula-radius-lg);
        border: var(--ula-border-width-hairline) solid var(--ula-border-default);
        padding: var(--ula-space-8);
        box-shadow: var(--ula-shadow-sm);
    }

    .nx-space-nav-list {
        display: flex;
        flex-direction: column;
        gap: var(--ula-space-4);
    }

    .nx-space-tab-card {
        display: flex;
        align-items: center;
        gap: var(--ula-space-5);
        padding: var(--ula-space-5);
        border-radius: var(--ula-radius-md);
        background: var(--ula-surface-page);
        border: var(--ula-border-width-hairline) solid transparent;
        cursor: pointer;
        text-align: start;
        min-height: var(--ula-size-touch-target);
        transition: all var(--ula-duration-base) var(--ula-ease-out);
    }

    .nx-space-tab-card.active {
        background: var(--ula-surface-raised);
        border-color: var(--ula-border-strong);
        box-shadow: var(--ula-shadow-sm);
    }

    .nx-space-tab-icon {
        width: 42px;
        height: 42px;
        min-width: 42px;
        border-radius: var(--ula-radius-sm);
        background: var(--ula-surface-page-alt);
        color: var(--ula-icon-primary);
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        transition: all var(--ula-duration-base) var(--ula-ease-out);
    }

    .nx-space-tab-card.active .nx-space-tab-icon {
        background: var(--ula-accent-default);
        color: var(--ula-accent-fg);
    }

    .nx-space-tab-title {
        font-family: var(--ula-font-ar);
        font-size: var(--ula-size-body);
        font-weight: var(--ula-weight-semibold);
        color: var(--ula-text-primary);
    }

    .nx-space-tab-sub {
        font-size: var(--ula-size-xs);
        color: var(--ula-text-secondary);
        margin-top: 2px;
    }

    /* ── 4. Platform Capabilities Grid ── */
    .nx-benefits-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: var(--ula-space-7);
    }

    .nx-benefit-card {
        background: var(--ula-surface-card);
        border: var(--ula-border-width-hairline) solid var(--ula-border-subtle);
        border-radius: var(--ula-radius-lg);
        overflow: hidden;
        display: flex;
        flex-direction: column;
        box-shadow: var(--ula-shadow-xs);
        transition: all var(--ula-duration-base) var(--ula-ease-out);
    }

    .nx-benefit-card:hover {
        transform: translateY(-4px);
        box-shadow: var(--ula-shadow-md);
        border-color: var(--ula-border-strong);
    }

    .nx-benefit-media {
        height: 140px;
        background: repeating-linear-gradient(135deg, var(--ula-media-stripe-a) 0 12px, var(--ula-media-stripe-b) 12px 24px);
    }

    .nx-benefit-body {
        padding: var(--ula-space-7);
        display: flex;
        flex-direction: column;
        gap: var(--ula-space-3);
    }

    .nx-benefit-icon-box {
        width: 44px;
        height: 44px;
        border-radius: var(--ula-radius-sm);
        background: var(--ula-surface-gold-soft);
        color: var(--ula-icon-highlight);
        display: flex;
        align-items: center;
        justify-content: center;
        margin-top: -46px;
        box-shadow: 0 0 0 4px var(--ula-surface-card);
    }

    .nx-benefit-title {
        font-family: var(--ula-font-ar);
        font-size: var(--ula-size-h4);
        font-weight: var(--ula-weight-semibold);
        color: var(--ula-text-primary);
    }

    .nx-benefit-sub {
        font-family: var(--ula-font-en);
        font-size: var(--ula-size-xs);
        color: var(--ula-text-muted);
        font-weight: var(--ula-weight-medium);
    }

    .nx-benefit-desc {
        font-family: var(--ula-font-ar);
        font-size: var(--ula-size-sm);
        color: var(--ula-text-body);
        line-height: var(--ula-lh-body);
    }

    /* ── 5. Live Meetings Row Section ── */
    .nx-meetings-card {
        max-width: var(--ula-layout-container-narrow);
        margin-inline: auto;
        background: var(--ula-surface-raised);
        border-radius: var(--ula-radius-lg);
        border: var(--ula-border-width-hairline) solid var(--ula-border-default);
        padding: var(--ula-space-7);
        box-shadow: var(--ula-shadow-sm);
        display: flex;
        flex-direction: column;
        gap: var(--ula-space-4);
    }

    .nx-meeting-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: var(--ula-space-5);
        border-radius: var(--ula-radius-md);
        background: var(--ula-surface-page);
        border: var(--ula-border-width-hairline) solid var(--ula-border-subtle);
        gap: var(--ula-space-5);
        transition: all var(--ula-duration-fast) var(--ula-ease-out);
    }

    .nx-meeting-row:hover {
        background: var(--ula-surface-hover);
        border-color: var(--ula-border-hover);
    }

    .nx-meeting-time {
        font-family: var(--ula-font-mono);
        font-size: var(--ula-size-sm);
        font-weight: var(--ula-weight-bold);
        color: var(--ula-text-primary);
        padding: var(--ula-space-2) var(--ula-space-4);
        border-radius: var(--ula-radius-xs);
        background: var(--ula-surface-warm);
        direction: ltr;
        unicode-bidi: isolate;
    }

    .nx-meeting-title {
        font-family: var(--ula-font-ar);
        font-size: var(--ula-size-body);
        font-weight: var(--ula-weight-semibold);
        color: var(--ula-text-primary);
    }

    .nx-meeting-sub {
        font-size: var(--ula-size-xs);
        color: var(--ula-text-secondary);
    }

    .nx-avatar-stack {
        display: flex;
        align-items: center;
    }

    .nx-avatar-item {
        width: 32px;
        height: 32px;
        border-radius: var(--ula-radius-pill);
        background: var(--ula-accent-default);
        color: var(--ula-accent-fg);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: var(--ula-size-label);
        font-weight: var(--ula-weight-bold);
        border: 2px solid var(--ula-surface-raised);
        margin-inline-start: -8px;
    }

    .nx-avatar-item:first-child {
        margin-inline-start: 0;
    }

    /* ── 6. Saudi Heritage & Identity Section ── */
    .nx-heritage-box {
        background: var(--ula-surface-page-alt);
        border-radius: var(--ula-radius-lg);
        border: var(--ula-border-width-hairline) solid var(--ula-border-default);
        padding: var(--ula-space-11) var(--ula-space-10);
        display: grid;
        grid-template-columns: 1fr 1.15fr;
        gap: var(--ula-space-10);
        align-items: center;
        position: relative;
        overflow: hidden;
    }

    .nx-heritage-visual-card {
        position: relative;
        height: 240px;
        border-radius: var(--ula-radius-md);
        overflow: hidden;
        border: var(--ula-border-width-hairline) solid var(--ula-border-default);
        background-color: var(--ula-media-stripe-a);
        background-image: repeating-linear-gradient(
            -45deg,
            var(--ula-media-stripe-a) 0px,
            var(--ula-media-stripe-a) 8px,
            var(--ula-media-stripe-b) 8px,
            var(--ula-media-stripe-b) 24px
        );
        box-shadow: var(--ula-shadow-md);
        display: flex;
        flex-direction: column;
        justify-content: flex-end;
    }

    .nx-heritage-scrim {
        position: absolute;
        inset: 0;
        background: var(--ula-scrim-caption);
    }

    .nx-heritage-caption {
        position: relative;
        z-index: 2;
        padding: var(--ula-space-7);
        color: var(--ula-text-on-dark);
    }

    .nx-heritage-quote-icon {
        color: var(--ula-icon-highlight);
        font-size: 32px;
    }

    /* ── 7. Backend Subscription Plans Section ── */
    .nx-pricing-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
        gap: var(--ula-space-6);
    }

    .nx-plan-card {
        background: var(--ula-surface-card);
        border-radius: var(--ula-radius-lg);
        border: var(--ula-border-width-hairline) solid var(--ula-border-default);
        padding: var(--ula-space-8) var(--ula-space-7);
        box-shadow: var(--ula-shadow-xs);
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        position: relative;
        transition: all var(--ula-duration-base) var(--ula-ease-out);
    }

    .nx-plan-card:hover {
        transform: translateY(-4px);
        box-shadow: var(--ula-shadow-md);
        border-color: var(--ula-border-strong);
    }

    .nx-plan-card.highlighted {
        border: 2px solid var(--ula-accent-default);
        box-shadow: var(--ula-shadow-lg);
    }

    .nx-plan-badge {
        position: absolute;
        top: -13px;
        inset-inline-end: var(--ula-space-7);
        background: var(--ula-accent-default);
        color: var(--ula-accent-fg);
        font-family: var(--ula-font-ar);
        font-size: var(--ula-size-xs);
        font-weight: var(--ula-weight-bold);
        padding: var(--ula-space-2) var(--ula-space-4);
        border-radius: var(--ula-radius-pill);
    }

    .nx-plan-price {
        font-family: var(--ula-font-mono);
        font-size: 36px;
        font-weight: var(--ula-weight-bold);
        color: var(--ula-text-primary);
        direction: ltr;
        unicode-bidi: isolate;
    }

    /* ── 8. Bottom Action Banner ── */
    .nx-bottom-banner {
        background: var(--ula-surface-dark);
        border-radius: var(--ula-radius-xl);
        padding: var(--ula-space-13) var(--ula-space-9);
        text-align: center;
        color: var(--ula-text-on-dark);
        margin: var(--ula-space-9) auto var(--ula-space-6);
        border: var(--ula-border-width-hairline) solid var(--ula-border-on-dark);
    }

    /* Responsive */
    @media (max-width: 1024px) {
        .nx-hero-inner { grid-template-columns: 1fr; }
        .nx-hero-copy { align-items: flex-start; }
        .nx-pillars-container { grid-template-columns: repeat(2, 1fr); }
        .nx-spaces-split { grid-template-columns: 1fr; }
        .nx-benefits-grid { grid-template-columns: repeat(2, 1fr); }
        .nx-heritage-box { grid-template-columns: 1fr; padding: var(--ula-space-8) var(--ula-space-6); }
    }

    @media (max-width: 640px) {
        .nx-hero-block { padding: var(--ula-space-8) var(--ula-space-5); }
        .nx-pillars-container { grid-template-columns: 1fr; }
        .nx-benefits-grid { grid-template-columns: 1fr; }
        .nx-meeting-row { flex-direction: column; align-items: flex-start; }
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

    <!-- ── 1. Hero Block ── -->
    <section id="hero" class="nx-hero-block" style="{{ $heroSec?->image_url ? 'background: linear-gradient(0deg, rgba(14, 28, 23, 0.82) 0%, rgba(14, 28, 23, 0.45) 100%), url(' . e($heroSec->image_url) . ') center/cover no-repeat;' : '' }}">
        @if(!$heroSec?->image_url)
            <div class="nx-hero-stripes-bg"></div>
        @endif
        <div class="nx-hero-scrim"></div>

        <div class="nx-hero-inner">
            <!-- Copy column -->
            <div class="nx-hero-copy">
                <x-badge variant="accent" icon="auto_awesome" size="lg">
                    {{ app()->getLocale() === 'ar' ? 'الجيل القادم من مساحات العمل الافتراضية' : 'Next-Generation Spatial Virtual Workplace' }}
                </x-badge>

                <div class="ula-headline-group">
                    <span class="ula-headline-ar">
                        {{ $heroSec?->title_ar ?: 'اجمع فريقك في مساحة واحدة ذكية' }}
                    </span>
                    <span class="ula-headline-en">
                        {{ $heroSec?->title_en ?: 'Unite your team in one intelligent space.' }}
                    </span>
                </div>

                <p class="nx-hero-body">
                    {{ app()->getLocale() === 'ar' ? ($heroSec?->subtitle_ar ?: 'مكاتب افتراضية نابضة بالحياة بالصوت والصورة والمحادثات. اجتماعات سهلة، ومشاركة أقرب.') : ($heroSec?->subtitle_en ?: 'Vibrant virtual offices with proximity audio, video, and chat. Seamless meetings and natural collaboration.') }}
                </p>

                <div class="nx-hero-buttons">
                    @auth
                        <x-btn href="{{ route('office') }}" variant="nav-cta" size="lg" icon="apartment">
                            {{ __('ادخل المقر') }}
                        </x-btn>
                    @else
                        <!-- Primary Action Button -->
                        <x-btn href="{{ $heroSec?->getContentValue('cta_primary_link', route('register')) }}" variant="nav-cta" size="lg" icon="arrow_forward">
                            {{ app()->getLocale() === 'ar' ? ($heroSec?->getContentValue('cta_primary_text_ar') ?: 'ادخل إلى مساحتك') : ($heroSec?->getContentValue('cta_primary_text_en') ?: 'Enter Your Space') }}
                        </x-btn>

                        <!-- Secondary Action Button -->
                        <a href="{{ $heroSec?->getContentValue('cta_secondary_link', '#spaces') }}" class="ula-btn ula-btn--lg nx-btn-dark-outline">
                            <span>{{ app()->getLocale() === 'ar' ? ($heroSec?->getContentValue('cta_secondary_text_ar') ?: 'شاهد العرض') : ($heroSec?->getContentValue('cta_secondary_text_en') ?: 'Watch Demo') }}</span>
                        </a>
                    @endauth
                </div>
            </div>

            <!-- Floor preview panel -->
            <div class="nx-hero-panel">
                <div style="display: flex; align-items: center; justify-content: space-between; gap: var(--ula-space-4);">
                    <div class="ula-headline-group">
                        <span class="ula-headline-ar" style="font-size: var(--ula-size-body);">الطابق الأول · المقر الرئيسي</span>
                        <span class="ula-headline-en" style="font-size: var(--ula-size-label);">Floor 1 · Main Headquarters</span>
                    </div>
                    <x-badge variant="live" dot>
                        <span style="direction: ltr; unicode-bidi: isolate; font-family: var(--ula-font-mono);">18</span>&nbsp;{{ __('عضواً متصلاً') }}
                    </x-badge>
                </div>

                <div class="nx-hero-panel-preview">
                    <span class="nx-media-caption">floor-preview.png</span>
                </div>

                <div class="nx-hero-room-row">
                    <span class="nx-hero-room-icon"><span class="material-symbols-rounded text-[20px]">meeting_room</span></span>
                    <div style="flex: 1; display: flex; flex-direction: column;">
                        <span class="ula-headline-ar" style="font-size: var(--ula-size-sm);">قاعة النخيل</span>
                        <span class="ula-headline-en" style="font-size: var(--ula-size-label);">Palm Boardroom · 4 In Call</span>
                    </div>
                    <x-badge variant="live" dot size="sm">{{ __('مباشر') }}</x-badge>
                </div>

                <div class="nx-hero-room-row">
                    <span class="nx-hero-room-icon"><span class="material-symbols-rounded text-[20px]">chair</span></span>
                    <div style="flex: 1; display: flex; flex-direction: column;">
                        <span class="ula-headline-ar" style="font-size: var(--ula-size-sm);">مساحة الابتكار</span>
                        <span class="ula-headline-en" style="font-size: var(--ula-size-label);">Innovation Lounge · 2 Desks</span>
                    </div>
                    <x-badge variant="scheduled" dot size="sm">{{ __('متاح') }}</x-badge>
                </div>
            </div>
        </div>
    </section>

    <!-- ── 2. 4-Pillars Features Strip ── -->
    <section id="spatial-presence" class="nx-pillars-strip">
        <div class="nx-pillars-container">
            <div class="nx-pillar-box">
                <span class="nx-pillar-icon"><span class="material-symbols-rounded text-[26px]">apartment</span></span>
                <div class="nx-pillar-ar">{{ app()->getLocale() === 'ar' ? 'مكاتب افتراضية حية' : 'Live Virtual Offices' }}</div>
                <div class="nx-pillar-en">Live Virtual Offices</div>
            </div>

            <div class="nx-pillar-box">
                <span class="nx-pillar-icon"><span class="material-symbols-rounded text-[26px]">videocam</span></span>
                <div class="nx-pillar-ar">{{ app()->getLocale() === 'ar' ? 'اجتماعات سلسة' : 'Seamless Meetings' }}</div>
                <div class="nx-pillar-en">Seamless Meetings</div>
            </div>

            <div class="nx-pillar-box">
                <span class="nx-pillar-icon"><span class="material-symbols-rounded text-[26px]">person_add</span></span>
                <div class="nx-pillar-ar">{{ app()->getLocale() === 'ar' ? 'دعوات بضغطة واحدة' : '1-Click Guest Access' }}</div>
                <div class="nx-pillar-en">1-Click Guest Access</div>
            </div>

            <div class="nx-pillar-box">
                <span class="nx-pillar-icon"><span class="material-symbols-rounded text-[26px]">chair</span></span>
                <div class="nx-pillar-ar">{{ app()->getLocale() === 'ar' ? 'تصميم مرن لمكتبك' : 'Design Your Space' }}</div>
                <div class="nx-pillar-en">Design Your Space</div>
            </div>
        </div>
    </section>

    <!-- ── 3. Spaces Explorer (#spaces) ── -->
    <section id="spaces" class="nx-section-wrap">
        <div class="nx-section-header">
            <div class="nx-section-badge">{{ $spacesSec?->badge ?: (app()->getLocale() === 'ar' ? 'المساحات الذكية' : 'Smart Spaces') }}</div>
            <div class="ula-headline-group">
                <span class="ula-headline-ar">{{ $spacesSec?->title_ar ?: 'مكتب افتراضي يشبه مكتبك الحقيقي' }}</span>
                <span class="ula-headline-en">{{ $spacesSec?->title_en ?: 'A virtual office that feels truly real.' }}</span>
            </div>
            <p class="nx-section-desc">
                {{ app()->getLocale() === 'ar' ? ($spacesSec?->subtitle_ar ?: 'صمم مخطط مكتبك بحرية وادعُ فريقك للتنقل والتواصل الطبيعي في غرف الاجتماعات، ومكاتب العمل، وصالات الاستراحة.') : ($spacesSec?->subtitle_en ?: 'Move naturally across boardrooms, private desks, and lounges with proximity audio.') }}
            </p>
        </div>

        <div class="nx-spaces-split">
            <div class="nx-space-nav-list">
                <button type="button" class="nx-space-tab-card active" onclick="switchSpaceTab(0)">
                    <div class="nx-space-tab-icon">
                        <span class="material-symbols-rounded text-[22px]">meeting_room</span>
                    </div>
                    <div>
                        <div class="nx-space-tab-title">{{ app()->getLocale() === 'ar' ? 'قاعة الاجتماعات الكبرى' : 'Executive Boardroom' }}</div>
                        <div class="nx-space-tab-sub">{{ app()->getLocale() === 'ar' ? 'عزل صوتي كامل ومشاركة شاشة بدقة 4K' : 'Acoustic isolation & 4K multi-screen' }}</div>
                    </div>
                </button>

                <button type="button" class="nx-space-tab-card" onclick="switchSpaceTab(1)">
                    <div class="nx-space-tab-icon">
                        <span class="material-symbols-rounded text-[22px]">workspaces</span>
                    </div>
                    <div>
                        <div class="nx-space-tab-title">{{ app()->getLocale() === 'ar' ? 'مساحة العمل المفتوحة' : 'Open Workspace Floor' }}</div>
                        <div class="nx-space-tab-sub">{{ app()->getLocale() === 'ar' ? 'صوت مكاني تلقائي عند الاقتراب' : 'Proximity instant voice' }}</div>
                    </div>
                </button>

                <button type="button" class="nx-space-tab-card" onclick="switchSpaceTab(2)">
                    <div class="nx-space-tab-icon">
                        <span class="material-symbols-rounded text-[22px]">coffee</span>
                    </div>
                    <div>
                        <div class="nx-space-tab-title">{{ app()->getLocale() === 'ar' ? 'ردهة القهوة والاستراحة' : 'Social Lounge' }}</div>
                        <div class="nx-space-tab-sub">{{ app()->getLocale() === 'ar' ? 'محادثات عفوية وراحة الفريق' : 'Watercooler spontaneous moments' }}</div>
                    </div>
                </button>
            </div>

            <div style="border-radius: var(--ula-radius-md); overflow: hidden; border: var(--ula-border-width-hairline) solid var(--ula-border-default); box-shadow: var(--ula-shadow-sm);">
                <img src="{{ $spacesSec?->image_url ?? asset('images/isometric_office_preview.jpg') }}" alt="{{ __('UlaSpace Office Preview') }}" style="width: 100%; height: auto; display: block; object-fit: cover; max-height: 440px;">
            </div>
        </div>
    </section>

    <!-- ── 4. Platform Capabilities (#benefits) ── -->
    <section id="benefits" class="nx-section-wrap" style="padding-top: 0;">
        <div class="nx-section-header">
            <div class="nx-section-badge">{{ $benefitsSec?->badge ?: (app()->getLocale() === 'ar' ? 'مميزات وقدرات المنصة' : 'Platform Benefits') }}</div>
            <div class="ula-headline-group">
                <span class="ula-headline-ar">{{ $benefitsSec?->title_ar ?: 'كل ما يحتاجه فريقك لبيئة عمل منتجة وحية' }}</span>
                <span class="ula-headline-en">{{ $benefitsSec?->title_en ?: 'Everything you need for a high-performing HQ.' }}</span>
            </div>
            <p class="nx-section-desc">
                {{ app()->getLocale() === 'ar' ? ($benefitsSec?->subtitle_ar ?: 'حلول مكانية متكاملة تدمج الصوت والفيديو والخرائط وإدارة المهام لتعزيز الإنتاجية والتواصل.') : ($benefitsSec?->subtitle_en ?: 'Integrated spatial solutions combining proximity audio, interactive floorplans, and enterprise productivity.') }}
            </p>
        </div>

        <div class="nx-benefits-grid">
            <!-- Benefit 1 -->
            <div class="nx-benefit-card">
                <div class="nx-benefit-media"></div>
                <div class="nx-benefit-body">
                    <div class="nx-benefit-icon-box">
                        <span class="material-symbols-rounded text-[24px]">graphic_eq</span>
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
                <div class="nx-benefit-media"></div>
                <div class="nx-benefit-body">
                    <div class="nx-benefit-icon-box">
                        <span class="material-symbols-rounded text-[24px]">meeting_room</span>
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
                <div class="nx-benefit-media"></div>
                <div class="nx-benefit-body">
                    <div class="nx-benefit-icon-box">
                        <span class="material-symbols-rounded text-[24px]">architecture</span>
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
                <div class="nx-benefit-media"></div>
                <div class="nx-benefit-body">
                    <div class="nx-benefit-icon-box">
                        <span class="material-symbols-rounded text-[24px]">screen_share</span>
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
                <div class="nx-benefit-media"></div>
                <div class="nx-benefit-body">
                    <div class="nx-benefit-icon-box">
                        <span class="material-symbols-rounded text-[24px]">insights</span>
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
                <div class="nx-benefit-media"></div>
                <div class="nx-benefit-body">
                    <div class="nx-benefit-icon-box">
                        <span class="material-symbols-rounded text-[24px]">verified_user</span>
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
    <section id="meetings" class="nx-section-wrap" style="padding-top: 0;">
        <div class="nx-section-header">
            <div class="nx-section-badge">{{ $meetingsSec?->badge ?: (app()->getLocale() === 'ar' ? 'الاجتماعات الحية' : 'Live Meetings') }}</div>
            <div class="ula-headline-group">
                <span class="ula-headline-ar">{{ $meetingsSec?->title_ar ?: 'جدول اجتماعاتك بلمحة واحدة' }}</span>
                <span class="ula-headline-en">{{ $meetingsSec?->title_en ?: 'Your schedule in one connected place.' }}</span>
            </div>
            <p class="nx-section-desc">
                {{ app()->getLocale() === 'ar' ? ($meetingsSec?->subtitle_ar ?: 'تعرّف على الغرف المشغولة ومن يتحدث في المكالمة، وانضم بضغطة زر.') : ($meetingsSec?->subtitle_en ?: 'See what is live, who is in the room, and jump into sessions seamlessly with one click.') }}
            </p>
        </div>

        <div class="nx-meetings-card">
            <!-- Meeting Row 1 (Live) -->
            <div class="nx-meeting-row">
                <div class="flex items-center gap-3">
                    <x-badge variant="live" dot size="sm"></x-badge>
                    <span class="nx-meeting-time">10:00 AM</span>
                    <div>
                        <div class="nx-meeting-title">{{ app()->getLocale() === 'ar' ? 'مراجعة تصميم منصة العلا' : 'UlaSpace Design Review' }}</div>
                        <div class="nx-meeting-sub">{{ app()->getLocale() === 'ar' ? 'قاعة النخيل الكبرى · 4 مشاركين' : 'Palm Boardroom · 4 Participants' }}</div>
                    </div>
                </div>

                <div class="flex items-center gap-4">
                    <div class="nx-avatar-stack">
                        <div class="nx-avatar-item">ع</div>
                        <div class="nx-avatar-item" style="background: var(--ula-accent-hover);">س</div>
                        <div class="nx-avatar-item" style="background: var(--ula-highlight-default); color: var(--ula-text-on-gold);">م</div>
                    </div>
                    <x-btn href="{{ route('office') }}" variant="primary" size="sm">
                        {{ app()->getLocale() === 'ar' ? 'انضم الآن' : 'Join Now' }}
                    </x-btn>
                </div>
            </div>

            <!-- Meeting Row 2 (Scheduled) -->
            <div class="nx-meeting-row">
                <div class="flex items-center gap-3">
                    <x-badge variant="scheduled" dot size="sm"></x-badge>
                    <span class="nx-meeting-time">11:30 AM</span>
                    <div>
                        <div class="nx-meeting-title">{{ app()->getLocale() === 'ar' ? 'مزامنة الفريق الهندسي' : 'Engineering Architecture Sync' }}</div>
                        <div class="nx-meeting-sub">{{ app()->getLocale() === 'ar' ? 'مساحة الابتكار · بعد 45 دقيقة' : 'Innovation Lounge · in 45 min' }}</div>
                    </div>
                </div>

                <div class="flex items-center gap-4">
                    <div class="nx-avatar-stack">
                        <div class="nx-avatar-item" style="background: var(--ula-accent-press);">ف</div>
                        <div class="nx-avatar-item" style="background: var(--ula-text-secondary);">ي</div>
                    </div>
                    <span class="nx-meeting-sub" style="font-weight: var(--ula-weight-semibold);">{{ app()->getLocale() === 'ar' ? 'مجدول' : 'Scheduled' }}</span>
                </div>
            </div>
        </div>
    </section>

    <!-- ── 6. Saudi Heritage & Identity Section (#identity) ── -->
    <section id="identity" class="nx-section-wrap" style="padding-top: 0;">
        <div class="nx-heritage-box">
            <!-- Visual Card with Scrim Caption -->
            <div class="nx-heritage-visual-card" style="{{ $identitySec?->image_url ? 'background-image: url(' . e($identitySec->image_url) . '); background-size: cover; background-position: center;' : '' }}">
                <div class="nx-heritage-scrim"></div>
                <div class="nx-heritage-caption">
                    <div class="ula-headline-ar" style="font-size: var(--ula-size-body);">من السعودية … إلى العالم</div>
                    <div class="ula-headline-en" style="font-size: var(--ula-size-label); color: var(--ula-text-on-dark-muted);">ALULA · SAUDI ARABIA</div>
                </div>
            </div>

            <!-- Copy Block -->
            <div>
                <span class="material-symbols-rounded nx-heritage-quote-icon">format_quote</span>
                <div class="ula-headline-group" style="margin-bottom: var(--ula-space-4); margin-top: var(--ula-space-3);">
                    <span class="ula-headline-ar" style="font-size: var(--ula-size-h2);">
                        {{ $identitySec?->title_ar ?: 'مستقبل العمل.. بروح سعودية' }}
                    </span>
                    <span class="ula-headline-en" style="font-size: var(--ula-size-h2-en);">
                        {{ $identitySec?->title_en ?: 'The future of work. A Saudi spirit.' }}
                    </span>
                </div>
                <p style="font-family: var(--ula-font-ar); font-size: var(--ula-size-sm); color: var(--ula-text-body); line-height: var(--ula-lh-body);">
                    {{ app()->getLocale() === 'ar' ? ($identitySec?->subtitle_ar ?: 'صُمّمت UlaSpace بإلهام من العلا لفرق تعمل من كل مكان: ضيافة سعودية في التفاصيل، وهندسة عالمية في الأداء والاتصال المكاني.') : ($identitySec?->subtitle_en ?: 'Designed with inspiration from AlUla for distributed teams everywhere: authentic hospitality in details, global performance in spatial connection.') }}
                </p>
                <div class="mt-6 flex items-center gap-3">
                    <span class="material-symbols-rounded" style="color: var(--ula-icon-accent); font-size: var(--ula-size-h3);">verified</span>
                    <span style="font-family: var(--ula-font-ar); font-size: var(--ula-size-sm); font-weight: var(--ula-weight-bold); color: var(--ula-text-primary);">
                        {{ app()->getLocale() === 'ar' ? 'مبني وفق أعلى معايير الخصوصية والأمان السيبراني' : 'Built to enterprise security & privacy standards' }}
                    </span>
                </div>
            </div>
        </div>
    </section>

    <!-- ── 7. Backend Subscription Plans Section (#pricing) ── -->
    <section id="pricing" class="nx-section-wrap">
        <div class="nx-section-header">
            <div class="nx-section-badge">{{ $pricingSec?->badge ?: (app()->getLocale() === 'ar' ? 'الباقات والاشتراكات' : 'Subscription Plans') }}</div>
            <div class="ula-headline-group">
                <span class="ula-headline-ar">{{ $pricingSec?->title_ar ?: 'باقة تناسب حجم وتطلعات فريقك' }}</span>
                <span class="ula-headline-en">{{ $pricingSec?->title_en ?: 'Plans that scale with your team.' }}</span>
            </div>
            <p class="nx-section-desc">
                {{ app()->getLocale() === 'ar' ? ($pricingSec?->subtitle_ar ?: 'ابدأ مجاناً اليوم، وقم بالترقية في أي وقت مع نمو وتوسع أعمالك.') : ($pricingSec?->subtitle_en ?: 'Start free, upgrade anytime as your workplace expands.') }}
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
                            <h3 class="ula-headline-ar" style="font-size: var(--ula-size-h3);">
                                {{ $planNameAr[$plan->name] ?? $plan->name }}
                            </h3>
                            <span class="ula-headline-en" style="font-size: var(--ula-size-label);">
                                {{ $plan->name }}
                            </span>
                        </div>

                        <div class="my-4 flex items-baseline gap-1.5" style="direction: ltr; unicode-bidi: isolate;">
                            <span class="nx-plan-price" data-plan-usd="{{ $plan->price }}">{{ $plan->price == 0 ? '0' : number_format($plan->price * 3.75, 0) }}</span>
                            <span class="nx-currency-symbol" style="font-size: var(--ula-size-body); font-weight: var(--ula-weight-bold); color: var(--ula-accent-default); font-family: var(--ula-font-ar);">ر.س</span>
                            <span style="font-size: var(--ula-size-sm); color: var(--ula-text-secondary);">/{{ __('شهرياً') }}</span>
                        </div>

                        <ul class="flex flex-col gap-3 my-6" style="font-size: var(--ula-size-sm); color: var(--ula-text-body);">
                            <li class="flex items-center gap-2.5">
                                <span class="material-symbols-rounded" style="color: var(--ula-icon-accent); font-size: var(--ula-size-h4);">group</span>
                                <span>
                                    <strong style="direction: ltr; unicode-bidi: isolate; font-family: var(--ula-font-mono);">{{ $plan->isUnlimitedSeats() ? '∞' : $plan->seat_limit }}</strong>
                                    {{ app()->getLocale() === 'ar' ? 'مقعداً متاحاً' : 'Seats' }}
                                </span>
                            </li>
                            <li class="flex items-center gap-2.5">
                                <span class="material-symbols-rounded" style="color: var(--ula-icon-accent); font-size: var(--ula-size-h4);">domain</span>
                                <span>
                                    <strong style="direction: ltr; unicode-bidi: isolate; font-family: var(--ula-font-mono);">{{ $plan->isUnlimitedOffices() ? '∞' : $plan->max_offices }}</strong>
                                    {{ app()->getLocale() === 'ar' ? 'مكاتب وطوابق' : 'Offices' }}
                                </span>
                            </li>
                            <li class="flex items-center gap-2.5">
                                <span class="material-symbols-rounded" style="color: var(--ula-icon-accent); font-size: var(--ula-size-h4);">cloud</span>
                                <span>
                                    <strong style="direction: ltr; unicode-bidi: isolate; font-family: var(--ula-font-mono);">{{ $plan->storage_limit_gb ?? 5 }} GB</strong>
                                    {{ app()->getLocale() === 'ar' ? 'مساحة تخزين سحابية' : 'Cloud Storage' }}
                                </span>
                            </li>
                            @if(is_array($plan->features))
                                @foreach(array_slice($plan->features, 0, 3) as $feature)
                                    <li class="flex items-center gap-2.5">
                                        <span class="material-symbols-rounded" style="color: var(--ula-icon-accent); font-size: var(--ula-size-h4);">check_circle</span>
                                        <span>{{ $feature }}</span>
                                    </li>
                                @endforeach
                            @endif
                        </ul>
                    </div>

                    <x-btn href="{{ route('register', ['plan' => $plan->slug]) }}" :variant="$isPopular ? 'primary' : 'secondary'" size="md" class="w-full">
                        {{ app()->getLocale() === 'ar' ? 'ابدأ الآن' : 'Get Started' }}
                    </x-btn>
                </div>
            @endforeach
        </div>
    </section>

    <!-- ── 8. Bottom Action Banner ── -->
    <div class="nx-section-wrap" style="padding-top: 0;">
        <div class="nx-bottom-banner" style="{{ $ctaSec?->image_url ? 'background: linear-gradient(0deg, rgba(20,43,36,0.85) 0%, rgba(20,43,36,0.65) 100%), url(' . e($ctaSec->image_url) . ') center/cover no-repeat;' : '' }}">
            <h2 style="font-family: var(--ula-font-ar); font-size: var(--ula-size-h1); font-weight: var(--ula-weight-semibold); color: var(--ula-text-on-dark); margin-bottom: var(--ula-space-4);">
                {{ app()->getLocale() === 'ar' ? ($ctaSec?->title_ar ?: 'جاهز لنقل فريقك إلى بيئة عمل المستقبل؟') : ($ctaSec?->title_en ?: 'Ready to Elevate Your Team’s Workspace?') }}
            </h2>
            <p style="font-family: var(--ula-font-ar); font-size: var(--ula-size-body); color: var(--ula-text-on-dark-muted); max-width: 580px; margin: 0 auto var(--ula-space-8);">
                {{ app()->getLocale() === 'ar' ? ($ctaSec?->subtitle_ar ?: 'انضم إلى الشركات الرائدة التي تبني ثقافة عمل قوية وحية مع UlaSpace.') : ($ctaSec?->subtitle_en ?: 'Join high-performing distributed teams building real culture and presence with UlaSpace.') }}
            </p>
            <div class="flex items-center justify-center gap-4 flex-wrap">
                <x-btn href="{{ $ctaSec?->getContentValue('cta_primary_link', route('register')) }}" variant="nav-cta" size="lg">
                    {{ app()->getLocale() === 'ar' ? ($ctaSec?->getContentValue('cta_primary_text_ar') ?: 'ابدأ التجربة المجانية الآن') : ($ctaSec?->getContentValue('cta_primary_text_en') ?: 'Start Free Trial Today') }}
                </x-btn>
                <a href="{{ $ctaSec?->getContentValue('cta_secondary_link', route('login')) }}" class="ula-btn ula-btn--lg nx-btn-dark-outline">
                    <span>{{ app()->getLocale() === 'ar' ? ($ctaSec?->getContentValue('cta_secondary_text_ar') ?: 'تسجيل الدخول') : ($ctaSec?->getContentValue('cta_secondary_text_en') ?: 'Sign In') }}</span>
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
