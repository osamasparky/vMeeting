@extends('landing.layout')

@section('title', 'UlaSpace — مساحات العمل والمكاتب الافتراضية الذكية')
@section('meta_description', 'مساحات عمل افتراضية غامرة تجمع فرق العمل عن بعد مع صوت وفيديو مكاني، وتخطيط خرائط المكاتب، والاجتماعات التفاعلية.')

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
        $heroSec = $sections->get('home_hero');
        $identitySec = $sections->get('home_company_workspace') ?? $sections->get('home_identity');
        $spacesSec = $sections->get('home_floorplan_editor') ?? $sections->get('home_spaces');
        $capabilitiesSec = $sections->get('home_collaboration') ?? $sections->get('home_benefits');
        $pricingSec = $sections->get('home_pricing');
        $ctaSec = $sections->get('home_cta');

        $planNameAr = ['free' => 'مجاني', 'starter' => 'مبتدئ', 'business' => 'أعمال', 'enterprise' => 'مؤسسات'];
        $featureAr = [
            'basic_chat' => 'دردشة نصية', 'basic_presence' => 'التواجد المباشر', 'basic_audio' => 'صوت أساسي',
            'video' => 'مكالمات فيديو', 'screen_share' => 'مشاركة الشاشة', 'file_sharing' => 'مشاركة الملفات',
            'guest_access' => 'دعوة الضيوف', 'analytics' => 'تحليلات الأداء', 'custom_branding' => 'علامة تجارية مخصصة',
            'sso' => 'دخول موحد (SSO)', 'api_access' => 'وصول عبر API', 'priority_support' => 'دعم ذو أولوية',
            'advanced_analytics' => 'تحليلات متقدمة',
        ];
        $mediaStyle = fn ($sec) => $sec?->image_url ? "background-image: url('" . e($sec->image_url) . "');" : '';
    @endphp

    <!-- ── Hero ── -->
    <section class="lp-media lp-hero" style="{{ $mediaStyle($heroSec) }}">
        <div class="lp-hero-scrim"></div>
        @unless($heroSec?->image_url)
            <span class="lp-media-label lp-hero-label">alula-canyon.jpg</span>
        @endunless

        <div class="lp-hero-card">
            <span class="lp-eyebrow">{{ $heroSec?->badge_en ?: 'Next-Generation Virtual Workplace' }}</span>
            <h1 class="lp-hero-title">{{ $heroSec?->title_ar ?: 'اجمع فريقك في مساحة واحدة ذكية' }}</h1>
            <p class="lp-hero-sub">{{ $heroSec?->subtitle_ar ?: 'مكاتب افتراضية نابضة بالحياة بالصوت والصورة والمحادثات. اجتماعات سهلة، ومشاركة أقرب.' }}</p>
            <span class="lp-en lp-hero-en">{{ $heroSec?->title_en ?: 'A more human way to work together.' }}</span>
            <div class="lp-hero-actions">
                @auth
                    <x-btn variant="primary" size="md" :href="route('office')">{{ $heroSec?->getContentValue('cta_primary_text_ar') ?: 'ادخل إلى مساحتك' }}</x-btn>
                @else
                    <x-btn variant="primary" size="md" :href="$heroSec?->getContentValue('cta_primary_link', route('register')) ?? route('register')">{{ $heroSec?->getContentValue('cta_primary_text_ar') ?: 'ادخل إلى مساحتك' }}</x-btn>
                @endauth
                <div class="lp-hero-actions-row">
                    <x-btn variant="secondary" size="md" href="#spaces">{{ $heroSec?->getContentValue('cta_secondary_text_ar') ?: 'شاهد العرض' }}</x-btn>
                    <x-btn variant="ghost" size="md" icon="dashboard" :href="auth()->check() ? route('dashboard') : route('login')">لوحة التحكم</x-btn>
                </div>
            </div>
        </div>

        <div class="lp-hero-tagline">
            <strong>أكثر من<br>مكان العمل</strong>
            <span class="lp-en">A more human<br>place to work.</span>
        </div>
    </section>

    <!-- ── Feature strip ── -->
    <section id="features" class="lp-features">
        @foreach([
            ['apartment', 'مكاتب افتراضية حية', 'Live Virtual Offices'],
            ['videocam', 'اجتماعات سلسة', 'Seamless Meetings'],
            ['person_add', 'دعوات بضغطة واحدة', '1-Click Guest Access'],
            ['palette', 'تصميم مرن لمكتبك', 'Design Your Space'],
        ] as [$icon, $ar, $en])
            <div class="lp-feature">
                <span class="ms" aria-hidden="true">{{ $icon }}</span>
                <div style="display: flex; flex-direction: column; align-items: center; gap: 2px;">
                    <strong>{{ $ar }}</strong>
                    <span class="lp-en">{{ $en }}</span>
                </div>
            </div>
        @endforeach
    </section>

    <!-- ── Identity ── -->
    <section id="identity" class="lp-section lp-section--alt lp-split">
        <div style="display: flex; flex-direction: column; align-items: flex-start; gap: 16px;">
            <div class="lp-head">
                <h2 class="lp-h2" style="font-size: 30px; line-height: 1.3;">{{ $identitySec?->title_ar ?: 'مستقبل العمل.. بروح سعودية' }}</h2>
                <span class="lp-en lp-lead-en">{{ $identitySec?->title_en ?: 'The future of work. A Saudi spirit.' }}</span>
            </div>
            <p class="lp-body" style="margin: 0; max-width: 420px;">{{ $identitySec?->subtitle_ar ?: 'صُمّمت UlaSpace بإلهام من العلا لفرق تعمل من كل مكان: ضيافة سعودية في التفاصيل، وهندسة عالمية في الأداء.' }}</p>
        </div>
        <div class="lp-media lp-identity-media" style="{{ $mediaStyle($identitySec) }}">
            @unless($identitySec?->image_url)
                <span class="lp-media-label">majlis-warm-light.jpg</span>
            @endunless
            <div class="lp-identity-caption">
                <strong>{{ $identitySec?->badge_ar ?: 'من السعودية … إلى العالم' }}</strong>
                <span class="lp-en">{{ $identitySec?->badge_en ?: 'From Saudi Arabia, to the world.' }}</span>
            </div>
        </div>
    </section>

    <!-- ── Smart spaces + live floor preview ── -->
    <section id="spaces" class="lp-section lp-split lp-spaces">
        <div style="display: flex; flex-direction: column; gap: 20px;">
            <div class="lp-head">
                <span class="lp-eyebrow">{{ $spacesSec?->badge_ar ?: 'المساحات الذكية' }}</span>
                <h2 class="lp-h2">{{ $spacesSec?->title_ar ?: 'مكتب افتراضي يشبه مكتبك الحقيقي' }}</h2>
                <span class="lp-en lp-lead-en" style="max-width: 560px; text-align: end;">{{ $spacesSec?->subtitle_en ?: 'Design your floor once, and every teammate walks in to the same place — desks, meeting rooms, lounges, and quiet corners.' }}</span>
            </div>
            <div style="display: flex; flex-direction: column; gap: 10px;">
                @foreach([
                    ['chair', 'مكاتب فردية ومساحات عمل مشتركة', 'Private desks and open collaboration zones'],
                    ['meeting_room', 'قاعات اجتماعات قابلة للقفل', 'Lockable meeting rooms with knock-to-enter'],
                    ['workspaces', 'صالات استراحة وزوايا هادئة', 'Lounges and quiet corners for focus work'],
                ] as [$icon, $ar, $en])
                    <div class="lp-space-tile">
                        <span class="lp-tile-icon"><span class="ms" aria-hidden="true">{{ $icon }}</span></span>
                        <div style="display: flex; flex-direction: column; min-width: 0;">
                            <span style="font-size: var(--ula-size-body); font-weight: var(--ula-weight-semibold); line-height: 1.6;">{{ $ar }}</span>
                            <span class="lp-en" style="font-size: var(--ula-size-xs); line-height: 1.5; color: var(--ula-text-secondary); text-align: start;">{{ $en }}</span>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="lp-floor">
            <div class="lp-floor-head">
                <div style="display: flex; flex-direction: column;">
                    <span style="font-size: 17px; font-weight: var(--ula-weight-semibold); line-height: 1.5;">الطابق الأول · المقر الرئيسي</span>
                    <span class="lp-en" style="font-size: var(--ula-size-xs); color: var(--ula-text-on-dark-subtle); text-align: end;">Floor 1 · Main Headquarters</span>
                </div>
                <span class="lp-live"><span class="lp-live-dot"></span><span class="lp-num">18</span>عضواً متصلاً</span>
            </div>
            <div class="lp-media lp-floor-preview" style="{{ $mediaStyle($spacesSec) }}">
                @unless($spacesSec?->image_url)
                    <span class="lp-media-label">floor-preview.png</span>
                @endunless
            </div>
            @foreach([
                ['meeting_room', 'قاعة النخيل', 'Palm Boardroom · 4 In Call', 'في مكالمة', 'var(--ula-status-success-on-dark)'],
                ['chair', 'مساحة الابتكار', 'Innovation Lounge · 2 Desks', 'مكتبان متاحان', 'var(--ula-highlight-default)'],
            ] as [$icon, $name, $en, $status, $color])
                <div class="lp-room">
                    <span class="lp-room-icon"><span class="ms" style="font-size: 20px;" aria-hidden="true">{{ $icon }}</span></span>
                    <div style="flex: 1; display: flex; flex-direction: column; min-width: 0;">
                        <span style="font-size: 14px; font-weight: var(--ula-weight-semibold);">{{ $name }}</span>
                        <span class="lp-en" style="font-size: var(--ula-size-xs); color: var(--ula-text-on-dark-subtle); text-align: start;">{{ $en }}</span>
                    </div>
                    <span style="font-size: var(--ula-size-sm); color: {{ $color }};">{{ $status }}</span>
                </div>
            @endforeach
        </div>
    </section>

    <!-- ── Capabilities ── -->
    <section id="capabilities" class="lp-section lp-section--alt" style="display: flex; flex-direction: column; gap: 28px;">
        <div class="lp-head" style="max-width: 760px;">
            <span class="lp-eyebrow lp-en">{{ $capabilitiesSec?->badge_en ?: 'Platform Capabilities' }}</span>
            <h2 class="lp-h2">{{ $capabilitiesSec?->title_ar ?: 'مصمم لبيئات العمل الحديثة التي تجمع بين التراث والابتكار' }}</h2>
            <span class="lp-en lp-lead-en">{{ $capabilitiesSec?->subtitle_en ?: 'Everything you need to run a high-trust, collaborative virtual headquarters.' }}</span>
        </div>
        <div class="lp-grid-3">
            @foreach([
                ['graphic_eq', 'الصوت والفيديو المكاني', 'تواصل تلقائي يحاكي الواقع، حيث يقوى الصوت تدريجياً كلما اقتربت من زملائك على خريطة المقر دون روابط مكالمات معقدة.', 'Proximity-based WebRTC Mesh'],
                ['lock', 'قاعات اجتماعات ذكية وأبواب خاصة', 'أبواب غرف قابلة للقفل مع جرس استئذان ومشاركة شاشة بدقة عالية وعزل صوتي كامل لسرية المحادثات.', 'Isolated Audio Zones & Knocking'],
                ['architecture', 'محرر الخرائط ومكتبة الأثاث', 'صمم مخطط مكتبك بسحب وإفلات المكاتب والنباتات والسبورات البيضاء والشاشات التفاعلية بسهولة.', 'Custom Floorplan Architect & Catalog'],
            ] as [$icon, $title, $body, $en])
                <div class="lp-card">
                    <span class="lp-card-icon"><span class="ms" style="font-size: 24px;" aria-hidden="true">{{ $icon }}</span></span>
                    <h3>{{ $title }}</h3>
                    <p class="lp-body" style="margin: 0;">{{ $body }}</p>
                    <span class="lp-en" style="font-size: var(--ula-size-xs); line-height: 1.5; color: var(--ula-text-secondary); text-align: start;">{{ $en }}</span>
                </div>
            @endforeach
        </div>
    </section>

    <!-- ── Quote ── -->
    <section class="lp-section lp-quote">
        <div class="lp-quote-box">
            <blockquote>"المكان ليس مجرد جدران، بل مساحة تلتقي فيها العقول وتتدفق فيها الأفكار بحرية وشغف."</blockquote>
            <span class="lp-eyebrow lp-en">UlaSpace — Designed with Heritage &amp; Modern Luxury</span>
        </div>
    </section>

    <!-- ── Pricing (plans from the database) ── -->
    <section id="pricing" class="lp-section" style="display: flex; flex-direction: column; gap: 28px;">
        <div class="lp-head">
            <h2 class="lp-h2">{{ $pricingSec?->title_ar ?: 'باقة تناسب حجم فريقك' }}</h2>
            <span class="lp-en lp-lead-en">{{ $pricingSec?->subtitle_en ?: 'Start free, upgrade any time as your team grows.' }}</span>
        </div>
        <div class="lp-grid-4">
            @foreach($plans as $plan)
                @php
                    $isPopular = $plan->slug === 'business';
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
                        @if($isPopular)
                            <span class="lp-popular">الأكثر شيوعاً</span>
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
                    <x-btn :variant="$isPopular ? 'primary' : 'outline'" size="md" :href="route('register', ['plan' => $plan->slug])">ابدأ الآن</x-btn>
                </div>
            @endforeach
        </div>
    </section>

    <!-- ── CTA band ── -->
    <section class="lp-section">
        <div class="lp-cta">
            <div style="display: flex; flex-direction: column; align-items: flex-start; gap: 8px; max-width: 700px;">
                <h2>{{ $ctaSec?->title_ar ?: 'جاهز لنقل فريقك إلى بيئة عمل المستقبل؟' }}</h2>
                <p>{{ $ctaSec?->subtitle_ar ?: 'انضم إلى المئات من الشركات الرائدة التي تبني ثقافة عمل قوية ومتصلة مع UlaSpace.' }}</p>
            </div>
            <div class="lp-cta-actions">
                @auth
                    <x-btn variant="nav-cta" size="lg" :href="route('office')">ادخل إلى مساحتك</x-btn>
                    <a href="{{ route('dashboard') }}" class="lp-btn-on-dark">لوحة التحكم</a>
                @else
                    <x-btn variant="nav-cta" size="lg" :href="$ctaSec?->getContentValue('cta_primary_link', route('register')) ?? route('register')">{{ $ctaSec?->getContentValue('cta_primary_text_ar') ?: 'ابدأ التجربة المجانية الآن' }}</x-btn>
                    <a href="{{ $ctaSec?->getContentValue('cta_secondary_link', route('login')) ?? route('login') }}" class="lp-btn-on-dark">{{ $ctaSec?->getContentValue('cta_secondary_text_ar') ?: 'تسجيل الدخول' }}</a>
                @endauth
            </div>
        </div>
    </section>

@endsection

@section('scripts')
@endsection
