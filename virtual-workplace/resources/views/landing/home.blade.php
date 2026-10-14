@extends('landing.layout')

@section('title', $page?->meta_title_ar ?: 'UlaSpace — مساحات العمل والمكاتب الافتراضية الذكية')
@section('meta_description', $page?->meta_desc_ar ?: 'مساحات عمل افتراضية غامرة تجمع فرق العمل عن بعد مع صوت وفيديو مكاني، وتخطيط خرائط المكاتب، والاجتماعات التفاعلية.')

@section('styles')
<style>
    /* Landing — design-reference/01-Landing.html. Tokens only; logical properties only. */
    .lp-section { padding: 48px 32px; }
    .lp-section--alt { background: var(--ula-surface-page-alt); }
    .lp-en { font-family: var(--ula-font-en); direction: ltr; unicode-bidi: isolate; }
    .lp-num { font-family: var(--ula-font-mono); direction: ltr; unicode-bidi: isolate; }
    .lp-eyebrow { font-family: var(--ula-font-en); font-size: var(--ula-size-label); font-weight: var(--ula-weight-medium); line-height: 1.5; letter-spacing: 0.14em; text-transform: uppercase; color: var(--ula-tone-gold-fg); }
    .lp-h2 { margin: 0; font-size: var(--ula-size-h2); font-weight: var(--ula-weight-semibold); line-height: 1.4; color: var(--ula-text-primary); text-wrap: pretty; }
    .lp-lead-en { font-family: var(--ula-font-en); font-size: var(--ula-size-body); line-height: 1.5; color: var(--ula-text-secondary); }
    .lp-body { font-size: 14px; line-height: 1.6; color: var(--ula-text-body); }
    .lp-head { display: flex; flex-direction: column; align-items: flex-start; gap: 6px; }

    /* Photo placeholders: stripes until a real image is uploaded in the CMS */
    .lp-media { position: relative; overflow: hidden; background: repeating-linear-gradient(135deg, var(--ula-media-stripe-a) 0 18px, var(--ula-media-stripe-b) 18px 36px); background-size: cover; background-position: center; }
    .lp-media-label { position: absolute; padding: 8px; background: var(--ula-media-label-bg); color: var(--ula-media-label-fg); font-family: var(--ula-font-mono); font-size: 10px; line-height: 1.4; direction: ltr; }

    /* Hero */
    .lp-hero { height: 520px; }
    .lp-hero-scrim { position: absolute; inset: 0; background: var(--ula-scrim-hero); }
    .lp-hero-label { bottom: 8px; inset-inline-end: 8px; }
    .lp-hero-card { position: absolute; top: 32px; inset-inline-end: 32px; width: 380px; max-width: calc(100% - 64px); box-sizing: border-box; padding: 34px 30px 26px; background: var(--ula-surface-card); display: flex; flex-direction: column; align-items: flex-start; }
    .lp-hero-card .lp-eyebrow { margin-bottom: 12px; direction: ltr; }
    .lp-hero-title { margin: 0; font-size: 30px; font-weight: var(--ula-weight-semibold); line-height: 1.3; color: var(--ula-text-primary); text-wrap: pretty; }
    .lp-hero-sub { margin: 0; padding: 12px 0; font-size: 14px; line-height: 1.6; color: var(--ula-text-body); }
    .lp-hero-en { padding: 10px 0; font-size: 14px; line-height: 1.5; color: var(--ula-text-secondary); align-self: stretch; text-align: end; }
    .lp-hero-actions { padding: 20px 0 0; display: flex; flex-direction: column; align-items: flex-start; gap: 10px; }
    .lp-hero-actions-row { display: flex; flex-wrap: wrap; gap: 10px; }
    .lp-hero-tagline { position: absolute; bottom: 0; inset-inline-start: 0; padding: 32px; display: flex; flex-direction: column; align-items: flex-start; gap: 8px; }
    .lp-hero-tagline strong { font-size: var(--ula-size-h2); font-weight: var(--ula-weight-semibold); line-height: 1.4; color: var(--ula-text-on-dark); }
    .lp-hero-tagline span { font-size: var(--ula-size-body); line-height: 1.5; color: var(--ula-media-stripe-a); text-align: end; }

    /* Feature strip */
    .lp-features { padding: 36px 32px; background: var(--ula-surface-page); display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 28px; }
    .lp-feature { display: flex; flex-direction: column; align-items: center; gap: 12px; text-align: center; }
    .lp-feature .ms { font-size: 32px; color: var(--ula-icon-highlight); }
    .lp-feature strong { font-size: 14px; font-weight: var(--ula-weight-medium); line-height: 1.6; color: var(--ula-text-primary); }
    .lp-feature span.lp-en { font-size: var(--ula-size-xs); line-height: 1.5; color: var(--ula-text-secondary); }

    /* Identity + spaces: two equal columns */
    .lp-split { display: flex; align-items: center; gap: 24px; }
    .lp-split > * { flex: 1; min-width: 0; }
    .lp-identity-media { height: 260px; border-radius: var(--ula-radius-lg); }
    .lp-identity-media::after { content: ''; position: absolute; inset: 0; background: var(--ula-scrim-caption); }
    .lp-identity-media .lp-media-label { top: 8px; inset-inline-end: 8px; z-index: 1; }
    .lp-identity-caption { position: absolute; bottom: 16px; inset-inline-start: 20px; z-index: 1; display: flex; flex-direction: column; align-items: flex-start; }
    .lp-identity-caption strong { font-size: var(--ula-size-body); font-weight: var(--ula-weight-medium); line-height: 1.6; color: var(--ula-text-on-dark); }
    .lp-identity-caption span { font-size: var(--ula-size-xs); line-height: 1.5; color: var(--ula-media-stripe-a); }

    .lp-spaces { align-items: stretch; }
    .lp-space-tile { display: flex; align-items: center; gap: 12px; padding: 12px 14px; border-radius: var(--ula-radius-md); background: var(--ula-surface-card); border: var(--ula-border-width-hairline) solid var(--ula-border-subtle); }
    .lp-tile-icon { width: 36px; height: 36px; flex-shrink: 0; border-radius: var(--ula-radius-sm); background: var(--ula-tone-gold-bg); color: var(--ula-tone-gold-fg); display: inline-flex; align-items: center; justify-content: center; }
    .lp-tile-icon .ms { font-size: 20px; }

    .lp-floor { border-radius: var(--ula-radius-lg); background: var(--ula-surface-dark); padding: 20px; display: flex; flex-direction: column; gap: 14px; color: var(--ula-text-on-dark); }
    .lp-floor-head { display: flex; align-items: center; justify-content: space-between; gap: 12px; }
    .lp-live { height: 29px; padding: 0 12px; flex-shrink: 0; border-radius: var(--ula-radius-pill); background: var(--ula-control-dark-fill-strong); color: var(--ula-status-success-on-dark); font-size: var(--ula-size-sm); font-weight: var(--ula-weight-medium); display: inline-flex; align-items: center; gap: 6px; }
    .lp-live-dot { width: 7px; height: 7px; border-radius: var(--ula-radius-pill); background: var(--ula-status-info); }
    .lp-floor-preview { height: 150px; border-radius: var(--ula-radius-md); background: repeating-linear-gradient(135deg, var(--ula-surface-dark-alt) 0 12px, var(--ula-surface-map-canvas) 12px 24px); background-size: cover; background-position: center; }
    .lp-floor-preview .lp-media-label { bottom: 8px; inset-inline-end: 8px; }
    .lp-room { display: flex; align-items: center; gap: 12px; padding: 10px 12px; border-radius: var(--ula-radius-md); background: var(--ula-control-dark-fill); border: var(--ula-border-width-hairline) solid var(--ula-border-on-dark-subtle); }
    .lp-room-icon { width: 36px; height: 36px; flex-shrink: 0; border-radius: var(--ula-radius-sm); background: var(--ula-control-dark-fill-strong); color: var(--ula-highlight-default); display: inline-flex; align-items: center; justify-content: center; }

    /* Capabilities */
    .lp-grid-3 { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 20px; }
    .lp-card { border-radius: var(--ula-radius-lg); background: var(--ula-surface-card); border: var(--ula-border-width-hairline) solid var(--ula-border-subtle); padding: 20px; display: flex; flex-direction: column; gap: 12px; box-shadow: var(--ula-shadow-xs); }
    .lp-card-icon { width: 44px; height: 44px; border-radius: var(--ula-radius-sm); background: var(--ula-tone-gold-bg); color: var(--ula-tone-gold-fg); display: inline-flex; align-items: center; justify-content: center; }
    .lp-card h3 { margin: 0; font-size: var(--ula-size-h4); font-weight: var(--ula-weight-semibold); line-height: 1.4; color: var(--ula-text-primary); }

    /* Quote */
    .lp-quote { display: flex; justify-content: center; }
    .lp-quote-box { max-width: 880px; padding-inline-start: 24px; border-inline-start: 3px solid var(--ula-highlight-default); display: flex; flex-direction: column; align-items: flex-start; gap: 10px; }
    .lp-quote-box blockquote { margin: 0; font-size: var(--ula-size-h2); font-weight: var(--ula-weight-semibold); line-height: 1.5; color: var(--ula-text-primary); text-wrap: pretty; }
    .lp-quote-box .lp-eyebrow { color: var(--ula-text-secondary); }

    /* Pricing */
    .lp-grid-4 { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 20px; }
    .lp-plan { padding: 20px; border-radius: var(--ula-radius-lg); background: var(--ula-surface-card); border: var(--ula-border-width-hairline) solid var(--ula-border-subtle); display: flex; flex-direction: column; gap: 14px; box-shadow: var(--ula-shadow-xs); }
    .lp-plan--featured { border: 2px solid var(--ula-accent-default); }
    .lp-plan-head { display: flex; align-items: center; justify-content: space-between; gap: 8px; }
    .lp-plan-name { font-size: var(--ula-size-h4); font-weight: var(--ula-weight-semibold); line-height: 1.4; }
    .lp-popular { height: 29px; padding: 0 12px; border-radius: var(--ula-radius-pill); background: var(--ula-tone-gold-bg); color: var(--ula-tone-gold-fg); font-size: var(--ula-size-sm); font-weight: var(--ula-weight-medium); display: inline-flex; align-items: center; white-space: nowrap; }
    .lp-price { display: flex; align-items: baseline; gap: 6px; direction: ltr; justify-content: flex-end; }
    .lp-price-num { font-family: var(--ula-font-mono); font-size: var(--ula-size-h1); font-weight: var(--ula-weight-medium); color: var(--ula-text-primary); }
    .lp-price-per { font-family: var(--ula-font-en); font-size: var(--ula-size-sm); color: var(--ula-text-muted); }
    .lp-plan-line { display: flex; align-items: center; gap: 8px; font-size: 14px; color: var(--ula-text-body); }
    .lp-plan-line .ms { font-size: 18px; color: var(--ula-icon-accent); }
    .lp-plan .ula-btn { width: 100%; }

    /* CTA band */
    .lp-cta { padding: 40px; border-radius: var(--ula-radius-xl); background: var(--ula-surface-dark); display: flex; align-items: center; justify-content: space-between; gap: 32px; }
    .lp-cta h2 { margin: 0; font-size: var(--ula-size-h2); font-weight: var(--ula-weight-semibold); line-height: 1.4; color: var(--ula-text-on-dark); }
    .lp-cta p { margin: 0; font-size: 14px; line-height: 1.6; color: var(--ula-media-stripe-a); }
    .lp-cta-actions { display: flex; flex-wrap: wrap; gap: 12px; flex-shrink: 0; }
    .lp-btn-on-dark { height: 52px; padding: 0 26px; border-radius: var(--ula-radius-lg); border: var(--ula-border-width-hairline) solid var(--ula-border-on-dark); background: transparent; color: var(--ula-text-on-dark); font-family: inherit; font-size: var(--ula-size-body-lg); font-weight: var(--ula-weight-semibold); display: inline-flex; align-items: center; justify-content: center; text-decoration: none; }
    .lp-btn-on-dark:hover { background: var(--ula-control-dark-fill); color: var(--ula-text-on-dark); text-decoration: none; }
    .lp-btn-on-dark:focus-visible { outline: none; box-shadow: var(--ula-focus-ring-on-dark); }

    @media (max-width: 1024px) {
        .lp-section, .lp-features { padding-inline: 20px; }
        .lp-features, .lp-grid-4 { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        .lp-grid-3 { grid-template-columns: minmax(0, 1fr); }
        .lp-split { flex-direction: column; align-items: stretch; }
        .lp-cta { flex-direction: column; align-items: flex-start; }
    }
    @media (max-width: 640px) {
        .lp-hero { height: auto; min-height: 560px; }
        .lp-hero-card { inset-inline: 16px; top: 16px; width: auto; max-width: none; padding: 24px 20px; }
        .lp-hero-tagline { display: none; }
        .lp-features, .lp-grid-4 { grid-template-columns: minmax(0, 1fr); }
        .lp-cta { padding: 28px 20px; }
    }
</style>
@endsection

@section('content')

    @php
        // Every section below comes from the CMS (Super Admin → Website Content).
        // A section that is hidden there is null here and is not rendered.
        $hero = $sections->get('home_hero');
        $features = $sections->get('home_features');
        $identity = $sections->get('home_company_workspace');
        $spaces = $sections->get('home_floorplan_editor');
        $capabilities = $sections->get('home_collaboration');
        $quote = $sections->get('home_quote');
        $pricing = $sections->get('home_pricing');
        $cta = $sections->get('home_cta');

        $planNameAr = ['free' => 'مجاني', 'starter' => 'مبتدئ', 'business' => 'أعمال', 'enterprise' => 'مؤسسات'];
        $featureAr = [
            'basic_chat' => 'دردشة نصية', 'basic_presence' => 'التواجد المباشر', 'basic_audio' => 'صوت أساسي',
            'video' => 'مكالمات فيديو', 'screen_share' => 'مشاركة الشاشة', 'file_sharing' => 'مشاركة الملفات',
            'guest_access' => 'دعوة الضيوف', 'analytics' => 'تحليلات الأداء', 'custom_branding' => 'علامة تجارية مخصصة',
            'sso' => 'دخول موحد (SSO)', 'api_access' => 'وصول عبر API', 'priority_support' => 'دعم ذو أولوية',
            'advanced_analytics' => 'تحليلات متقدمة',
        ];
        $roomTone = ['success' => 'var(--ula-status-success-on-dark)', 'highlight' => 'var(--ula-highlight-default)'];
        $mediaStyle = fn ($sec) => $sec?->image_url ? "background-image: url('" . e($sec->image_url) . "');" : '';
    @endphp

    @if($hero)
    <!-- ── Hero ── -->
    <section class="lp-media lp-hero" style="{{ $mediaStyle($hero) }}">
        <div class="lp-hero-scrim"></div>
        @unless($hero->image_url)
            <span class="lp-media-label lp-hero-label">alula-canyon.jpg</span>
        @endunless

        <div class="lp-hero-card">
            @if($hero->field('badge_en'))<span class="lp-eyebrow">{{ $hero->field('badge_en') }}</span>@endif
            <h1 class="lp-hero-title">{{ $hero->field('title_ar') }}</h1>
            @if($hero->field('subtitle_ar'))<p class="lp-hero-sub">{{ $hero->field('subtitle_ar') }}</p>@endif
            @if($hero->field('title_en'))<span class="lp-en lp-hero-en">{{ $hero->field('title_en') }}</span>@endif
            <div class="lp-hero-actions">
                @auth
                    <x-btn variant="primary" size="md" :href="route('office')">{{ $hero->field('cta_primary_text_ar') }}</x-btn>
                @else
                    <x-btn variant="primary" size="md" :href="$hero->field('cta_primary_link') ?: route('register')">{{ $hero->field('cta_primary_text_ar') }}</x-btn>
                @endauth
                <div class="lp-hero-actions-row">
                    @if($hero->field('cta_secondary_text_ar'))
                        <x-btn variant="secondary" size="md" :href="$hero->field('cta_secondary_link') ?: '#spaces'">{{ $hero->field('cta_secondary_text_ar') }}</x-btn>
                    @endif
                    @if($hero->field('dashboard_text_ar'))
                        <x-btn variant="ghost" size="md" icon="dashboard" :href="auth()->check() ? route('dashboard') : route('login')">{{ $hero->field('dashboard_text_ar') }}</x-btn>
                    @endif
                </div>
            </div>
        </div>

        @if($hero->field('overlay_title_ar') || $hero->field('overlay_title_en'))
            <div class="lp-hero-tagline">
                @if($hero->field('overlay_title_ar'))<strong>{!! nl2br(e($hero->field('overlay_title_ar'))) !!}</strong>@endif
                @if($hero->field('overlay_title_en'))<span class="lp-en">{!! nl2br(e($hero->field('overlay_title_en'))) !!}</span>@endif
            </div>
        @endif
    </section>
    @endif

    @if($features && $features->items('items'))
    <!-- ── Feature strip ── -->
    <section id="features" class="lp-features">
        @foreach($features->items('items') as $item)
            <div class="lp-feature">
                @if(!empty($item['icon']))<span class="ms" aria-hidden="true">{{ $item['icon'] }}</span>@endif
                <div style="display: flex; flex-direction: column; align-items: center; gap: 2px;">
                    <strong>{{ $item['text_ar'] ?? '' }}</strong>
                    @if(!empty($item['text_en']))<span class="lp-en">{{ $item['text_en'] }}</span>@endif
                </div>
            </div>
        @endforeach
    </section>
    @endif

    @if($identity)
    <!-- ── Identity ── -->
    <section id="identity" class="lp-section lp-section--alt lp-split">
        <div style="display: flex; flex-direction: column; align-items: flex-start; gap: 16px;">
            <div class="lp-head">
                <h2 class="lp-h2" style="font-size: 30px; line-height: 1.3;">{{ $identity->field('title_ar') }}</h2>
                @if($identity->field('title_en'))<span class="lp-en lp-lead-en">{{ $identity->field('title_en') }}</span>@endif
            </div>
            @if($identity->field('subtitle_ar'))<p class="lp-body" style="margin: 0; max-width: 420px;">{{ $identity->field('subtitle_ar') }}</p>@endif
        </div>
        <div class="lp-media lp-identity-media" style="{{ $mediaStyle($identity) }}">
            @unless($identity->image_url)
                <span class="lp-media-label">majlis-warm-light.jpg</span>
            @endunless
            <div class="lp-identity-caption">
                @if($identity->field('badge_ar'))<strong>{{ $identity->field('badge_ar') }}</strong>@endif
                @if($identity->field('badge_en'))<span class="lp-en">{{ $identity->field('badge_en') }}</span>@endif
            </div>
        </div>
    </section>
    @endif

    @if($spaces)
    <!-- ── Smart spaces + live floor preview ── -->
    <section id="spaces" class="lp-section lp-split lp-spaces">
        <div style="display: flex; flex-direction: column; gap: 20px;">
            <div class="lp-head">
                @if($spaces->field('badge_ar'))<span class="lp-eyebrow">{{ $spaces->field('badge_ar') }}</span>@endif
                <h2 class="lp-h2">{{ $spaces->field('title_ar') }}</h2>
                @if($spaces->field('subtitle_en'))<span class="lp-en lp-lead-en" style="max-width: 560px; text-align: end;">{{ $spaces->field('subtitle_en') }}</span>@endif
            </div>
            <div style="display: flex; flex-direction: column; gap: 10px;">
                @foreach($spaces->items('tiles') as $tile)
                    <div class="lp-space-tile">
                        <span class="lp-tile-icon"><span class="ms" aria-hidden="true">{{ $tile['icon'] ?? '' }}</span></span>
                        <div style="display: flex; flex-direction: column; min-width: 0;">
                            <span style="font-size: var(--ula-size-body); font-weight: var(--ula-weight-semibold); line-height: 1.6;">{{ $tile['text_ar'] ?? '' }}</span>
                            @if(!empty($tile['text_en']))<span class="lp-en" style="font-size: var(--ula-size-xs); line-height: 1.5; color: var(--ula-text-secondary); text-align: start;">{{ $tile['text_en'] }}</span>@endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="lp-floor">
            <div class="lp-floor-head">
                <div style="display: flex; flex-direction: column;">
                    <span style="font-size: 17px; font-weight: var(--ula-weight-semibold); line-height: 1.5;">{{ $spaces->field('floor_name_ar') }}</span>
                    @if($spaces->field('floor_name_en'))<span class="lp-en" style="font-size: var(--ula-size-xs); color: var(--ula-text-on-dark-subtle); text-align: end;">{{ $spaces->field('floor_name_en') }}</span>@endif
                </div>
                @if($spaces->field('online_count') || $spaces->field('online_label_ar'))
                    <span class="lp-live"><span class="lp-live-dot"></span><span class="lp-num">{{ $spaces->field('online_count') }}</span>{{ $spaces->field('online_label_ar') }}</span>
                @endif
            </div>
            <div class="lp-media lp-floor-preview" style="{{ $mediaStyle($spaces) }}">
                @unless($spaces->image_url)
                    <span class="lp-media-label">floor-preview.png</span>
                @endunless
            </div>
            @foreach($spaces->items('rooms') as $room)
                <div class="lp-room">
                    <span class="lp-room-icon"><span class="ms" style="font-size: 20px;" aria-hidden="true">{{ $room['icon'] ?? '' }}</span></span>
                    <div style="flex: 1; display: flex; flex-direction: column; min-width: 0;">
                        <span style="font-size: 14px; font-weight: var(--ula-weight-semibold);">{{ $room['name_ar'] ?? '' }}</span>
                        @if(!empty($room['detail_en']))<span class="lp-en" style="font-size: var(--ula-size-xs); color: var(--ula-text-on-dark-subtle); text-align: start;">{{ $room['detail_en'] }}</span>@endif
                    </div>
                    <span style="font-size: var(--ula-size-sm); color: {{ $roomTone[$room['tone'] ?? 'success'] ?? $roomTone['success'] }};">{{ $room['status_ar'] ?? '' }}</span>
                </div>
            @endforeach
        </div>
    </section>
    @endif

    @if($capabilities)
    <!-- ── Capabilities ── -->
    <section id="capabilities" class="lp-section lp-section--alt" style="display: flex; flex-direction: column; gap: 28px;">
        <div class="lp-head" style="max-width: 760px;">
            @if($capabilities->field('badge_en'))<span class="lp-eyebrow lp-en">{{ $capabilities->field('badge_en') }}</span>@endif
            <h2 class="lp-h2">{{ $capabilities->field('title_ar') }}</h2>
            @if($capabilities->field('subtitle_en'))<span class="lp-en lp-lead-en">{{ $capabilities->field('subtitle_en') }}</span>@endif
        </div>
        <div class="lp-grid-3">
            @foreach($capabilities->items('cards') as $card)
                <div class="lp-card">
                    <span class="lp-card-icon"><span class="ms" style="font-size: 24px;" aria-hidden="true">{{ $card['icon'] ?? '' }}</span></span>
                    <h3>{{ $card['title_ar'] ?? '' }}</h3>
                    @if(!empty($card['body_ar']))<p class="lp-body" style="margin: 0;">{{ $card['body_ar'] }}</p>@endif
                    @if(!empty($card['caption_en']))<span class="lp-en" style="font-size: var(--ula-size-xs); line-height: 1.5; color: var(--ula-text-secondary); text-align: start;">{{ $card['caption_en'] }}</span>@endif
                </div>
            @endforeach
        </div>
    </section>
    @endif

    @if($quote && $quote->field('quote_ar'))
    <!-- ── Quote ── -->
    <section class="lp-section lp-quote">
        <div class="lp-quote-box">
            <blockquote>"{{ $quote->field('quote_ar') }}"</blockquote>
            @if($quote->field('attribution_en'))<span class="lp-eyebrow lp-en">{{ $quote->field('attribution_en') }}</span>@endif
        </div>
    </section>
    @endif

    @if($pricing)
    <!-- ── Pricing (plans from Plans & Billing) ── -->
    <section id="pricing" class="lp-section" style="display: flex; flex-direction: column; gap: 28px;">
        <div class="lp-head">
            <h2 class="lp-h2">{{ $pricing->field('title_ar') }}</h2>
            @if($pricing->field('subtitle_en'))<span class="lp-en lp-lead-en">{{ $pricing->field('subtitle_en') }}</span>@endif
        </div>
        <div class="lp-grid-4">
            @foreach($plans as $plan)
                @php
                    $isPopular = $plan->slug === $pricing->field('featured_plan');
                    $nameAr = $planNameAr[$plan->slug] ?? $plan->name;
                    $nameEn = isset($planNameAr[$plan->slug]) ? $plan->name : null;
                    $highlights = array_slice(is_array($plan->features) ? $plan->features : [], -2);
                    $price = rtrim(rtrim(number_format((float) $plan->price, 2), '0'), '.');
                @endphp
                <div class="lp-plan {{ $isPopular ? 'lp-plan--featured' : '' }}">
                    <div class="lp-plan-head">
                        <div style="display: flex; flex-direction: column;">
                            <span class="lp-plan-name">{{ $nameAr }}</span>
                            @if($nameEn)
                                <span class="lp-en" style="font-size: var(--ula-size-xs); color: var(--ula-text-secondary); text-align: start;">{{ $nameEn }}</span>
                            @endif
                        </div>
                        @if($isPopular && $pricing->field('popular_label_ar'))
                            <span class="lp-popular">{{ $pricing->field('popular_label_ar') }}</span>
                        @endif
                    </div>
                    <div class="lp-price">
                        <span class="lp-price-num">${{ $price }}</span>
                        <span class="lp-price-per">{{ $plan->isPerSeat() ? '/person/mo' : '/mo' }}</span>
                    </div>
                    <span class="lp-plan-line">
                        <span class="ms" aria-hidden="true">group</span>
                        @if($plan->isPerSeat())
                            لكل شخص · <span class="lp-num">{{ $plan->getEffectiveMinSeats() }}</span> كحد أدنى
                        @else
                            <span class="lp-num">{{ $plan->isUnlimitedSeats() ? '∞' : $plan->seat_limit }}</span> مقعداً
                        @endif
                    </span>
                    <div style="flex: 1; display: flex; flex-direction: column; gap: 8px;">
                        @foreach($highlights as $feature)
                            <span class="lp-plan-line"><span class="ms" aria-hidden="true">check_circle</span>{{ $featureAr[$feature] ?? \Illuminate\Support\Str::headline($feature) }}</span>
                        @endforeach
                    </div>
                    <x-btn :variant="$isPopular ? 'primary' : 'outline'" size="md" :href="route('register', ['plan' => $plan->slug])">{{ $pricing->field('button_text_ar') ?: 'ابدأ الآن' }}</x-btn>
                </div>
            @endforeach
        </div>
    </section>
    @endif

    @if($cta)
    <!-- ── CTA band ── -->
    <section class="lp-section">
        <div class="lp-cta">
            <div style="display: flex; flex-direction: column; align-items: flex-start; gap: 8px; max-width: 700px;">
                <h2>{{ $cta->field('title_ar') }}</h2>
                @if($cta->field('subtitle_ar'))<p>{{ $cta->field('subtitle_ar') }}</p>@endif
            </div>
            <div class="lp-cta-actions">
                @auth
                    <x-btn variant="nav-cta" size="lg" :href="route('office')">{{ $hero?->field('cta_primary_text_ar') ?: 'ادخل إلى مساحتك' }}</x-btn>
                    <a href="{{ route('dashboard') }}" class="lp-btn-on-dark">{{ $hero?->field('dashboard_text_ar') ?: 'لوحة التحكم' }}</a>
                @else
                    <x-btn variant="nav-cta" size="lg" :href="$cta->field('cta_primary_link') ?: route('register')">{{ $cta->field('cta_primary_text_ar') }}</x-btn>
                    @if($cta->field('cta_secondary_text_ar'))
                        <a href="{{ $cta->field('cta_secondary_link') ?: route('login') }}" class="lp-btn-on-dark">{{ $cta->field('cta_secondary_text_ar') }}</a>
                    @endif
                @endauth
            </div>
        </div>
    </section>
    @endif

@endsection

@section('scripts')
@endsection
