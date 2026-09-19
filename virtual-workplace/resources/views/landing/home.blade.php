@extends('landing.layout')

@section('title', 'UlaSpace — منصة مساحات العمل والمكاتب الافتراضية الذكية')
@section('meta_description', 'مساحات عمل افتراضية غامرة تجمع فرق العمل عن بعد مع صوت وفيديو مكاني، وتخطيط خرائط المكاتب، والاجتماعات التفاعلية.')

@section('styles')
<style>
    /* ── Hero Arch Block ── */
    .nx-hero-section {
        padding: 40px 24px 80px;
        max-width: var(--ula-layout-container-max);
        margin: 0 auto;
    }

    .nx-hero-arch-card {
        position: relative;
        background: var(--ula-palm-900);
        color: var(--ula-white);
        border-radius: var(--ula-radius-xl) var(--ula-radius-xl) var(--ula-radius-xl) var(--ula-radius-xl);
        padding: 80px 48px 64px;
        overflow: hidden;
        box-shadow: var(--ula-shadow-xl);
        border: 1px solid rgba(237, 230, 217, 0.15);
    }

    .nx-hero-scrim {
        position: absolute;
        inset: 0;
        background: var(--ula-scrim-hero);
        pointer-events: none;
    }

    .nx-hero-grid {
        position: relative;
        z-index: 2;
        display: grid;
        grid-template-columns: 1.15fr 0.85fr;
        gap: 48px;
        align-items: center;
    }

    .nx-hero-tag {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 6px 16px;
        border-radius: var(--ula-radius-pill);
        background: rgba(237, 230, 217, 0.12);
        border: 1px solid rgba(237, 230, 217, 0.25);
        color: var(--ula-sand-300);
        font-size: 13px;
        font-weight: 500;
        margin-bottom: 24px;
    }

    .nx-hero-title-ar {
        font-family: var(--ula-font-ar);
        font-size: clamp(32px, 4.2vw, 54px);
        font-weight: 600;
        line-height: 1.25;
        color: var(--ula-white);
        margin-bottom: 12px;
    }

    .nx-hero-title-en {
        font-family: var(--ula-font-en);
        font-size: clamp(16px, 2vw, 22px);
        font-weight: 300;
        color: var(--ula-sand-400);
        line-height: 1.5;
        margin-bottom: 32px;
    }

    .nx-hero-preview {
        position: relative;
        border-radius: var(--ula-radius-lg);
        overflow: hidden;
        border: 1px solid rgba(237, 230, 217, 0.2);
        box-shadow: var(--ula-shadow-lg), 0 0 0 1px rgba(211, 165, 83, 0.08), 0 24px 60px -20px rgba(211, 165, 83, 0.25);
        background: var(--ula-palm-950);
        min-height: 360px;
        display: flex;
        flex-direction: column;
    }

    .nx-hero-preview::after {
        content: "";
        position: absolute;
        inset: 0;
        pointer-events: none;
        background: radial-gradient(120% 90% at 100% 0%, rgba(211, 165, 83, 0.10) 0%, transparent 55%);
    }

    /* ── Features Section ── */
    .nx-features-section {
        padding: 80px 24px;
        max-width: var(--ula-layout-container-max);
        margin: 0 auto;
    }

    .nx-section-header {
        text-align: center;
        max-width: 760px;
        margin: 0 auto 56px;
    }

    .nx-features-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
        gap: 28px;
    }

    .nx-feature-card {
        position: relative;
        overflow: hidden;
        background: var(--ula-surface-card);
        border: 1px solid var(--ula-border-subtle);
        border-radius: var(--ula-radius-lg);
        padding: 36px 32px;
        box-shadow: var(--ula-shadow-sm);
        transition: all var(--ula-duration-base) var(--ula-ease-in-out);
        display: flex;
        flex-direction: column;
        justify-content: space-between;
    }

    .nx-feature-card::before {
        content: "";
        position: absolute;
        inset-block-start: 0;
        inset-inline: 0;
        height: 3px;
        background: var(--ula-highlight-default);
        transform: scaleX(0);
        transform-origin: center;
        transition: transform var(--ula-duration-base) var(--ula-ease-in-out);
    }

    .nx-feature-card:hover {
        transform: translateY(-4px);
        border-color: var(--ula-border-strong);
        box-shadow: var(--ula-shadow-md);
    }

    .nx-feature-card:hover::before {
        transform: scaleX(1);
    }

    .nx-feature-icon-box {
        width: 56px;
        height: 56px;
        border-radius: var(--ula-radius-md);
        background: var(--ula-tone-gold-bg);
        border: 1px solid var(--ula-border-subtle);
        color: var(--ula-tone-gold-fg);
        display: flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 24px;
        transition: transform var(--ula-duration-base) var(--ula-ease-in-out);
    }

    .nx-feature-card:hover .nx-feature-icon-box {
        transform: scale(1.06) rotate(-2deg);
    }

    /* ── Heritage Identity Quote Section ── */
    .nx-identity-section {
        padding: 60px 24px;
        max-width: var(--ula-layout-container-max);
        margin: 0 auto;
    }

    .nx-quote-card {
        background: var(--ula-palm-900);
        color: var(--ula-white);
        border-radius: var(--ula-radius-xl);
        padding: 64px 48px;
        text-align: center;
        position: relative;
        overflow: hidden;
        border: 1px solid rgba(237, 230, 217, 0.15);
    }

    .nx-quote-text {
        font-size: clamp(20px, 2.5vw, 28px);
        font-weight: 600;
        line-height: 1.6;
        color: var(--ula-sand-100);
        max-width: 860px;
        margin: 0 auto 20px;
    }

    .nx-quote-author {
        font-size: 14px;
        color: var(--ula-sand-400);
        font-weight: 400;
        letter-spacing: 0.05em;
    }

    @media (max-width: 960px) {
        .nx-hero-grid { grid-template-columns: 1fr; }
        .nx-hero-arch-card { padding: 48px 24px 40px; border-radius: 32px 32px 16px 16px; }
    }
</style>
@endsection

@section('content')

    <!-- ── 1. Hero Arch Block (Figma Screen: Website) ── -->
    <section class="nx-hero-section">
        <div class="nx-hero-arch-card">
            <div class="nx-hero-scrim"></div>
            <div class="nx-hero-grid">
                <!-- Content Column -->
                <div>
                    <div class="nx-hero-tag">
                        <span class="material-symbols-rounded text-[18px] text-[var(--ula-highlight-default)]">spark</span>
                        <span>{{ __('Next-Generation Spatial Virtual Workplace') }}</span>
                    </div>

                    <h1 class="nx-hero-title-ar">
                        مساحات عمل افتراضية ذكية تجمع الفرق عن بُعد بانسيابية تامة.
                    </h1>

                    <p class="nx-hero-title-en">
                        Next-generation spatial virtual workplaces where distributed teams meet, collaborate, and build culture naturally.
                    </p>

                    <div class="flex items-center gap-4 flex-wrap mt-8">
                        @auth
                            <x-btn href="{{ route('office') }}" variant="nav-cta" size="lg" icon="apartment">
                                <span>{{ __('Enter Workplace Floor') }}</span>
                            </x-btn>
                            <x-btn href="{{ route('dashboard') }}" variant="outline" size="lg" class="!text-white !border-white/20 hover:!border-white/40">
                                <span>{{ __('Dashboard') }}</span>
                            </x-btn>
                        @else
                            <x-btn href="{{ route('register') }}" variant="nav-cta" size="lg">
                                <span>{{ __('Book a Demo') }}</span>
                            </x-btn>
                            <x-btn href="{{ route('login') }}" variant="outline" size="lg" class="!text-white !border-white/20 hover:!border-white/40">
                                <span>{{ __('Sign In') }}</span>
                            </x-btn>
                        @endauth
                    </div>
                </div>

                <!-- Preview Column: Dark Floor Map Teaser -->
                <div class="nx-hero-preview p-6">
                    <div class="flex items-center justify-between pb-4 border-b border-white/10 mb-4">
                        <div class="flex items-center gap-2">
                            <span class="w-2.5 h-2.5 rounded-full bg-[var(--ula-tone-palm-dot)]"></span>
                            <span class="ula-headline-group">
                                <span class="ula-headline-ar" style="font-size: 12px; color: var(--ula-text-on-dark);">الطابق الأول · المقر الرئيسي</span>
                                <span class="ula-headline-en" style="font-size: 10px; color: var(--ula-text-on-dark-muted);">Floor 1 · Main Headquarters</span>
                            </span>
                        </div>
                        <span class="ula-headline-group" style="align-items: flex-end;">
                            <span class="ula-headline-ar" style="font-size: 11px; color: var(--ula-text-on-dark);"><span style="direction: ltr; unicode-bidi: isolate">18</span> عضواً متصلاً</span>
                            <span class="ula-headline-en" style="font-size: 9px; color: var(--ula-text-on-dark-muted); font-family: var(--ula-font-mono); direction: ltr; unicode-bidi: isolate;">18 Active Members</span>
                        </span>
                    </div>

                    <!-- Room capsules preview -->
                    <div class="grid grid-cols-2 gap-3 my-auto">
                        <div class="p-3.5 rounded-[var(--ula-radius-md)] bg-white/5 border border-white/10 flex flex-col gap-1.5">
                            <div class="flex items-center justify-between">
                                <span class="text-[13px] font-semibold text-white">قاعة النخيل</span>
                                <x-badge variant="live" size="sm" dot>مباشر</x-badge>
                            </div>
                            <span class="ula-headline-group">
                                <span class="ula-headline-ar" style="font-size: 11px; color: var(--ula-text-on-dark-muted);"><span style="direction: ltr; unicode-bidi: isolate">4</span> في مكالمة</span>
                                <span class="ula-headline-en" style="font-size: 9px; color: var(--ula-text-on-dark-muted); opacity: 0.75;">Palm Boardroom · 4 In Call</span>
                            </span>
                        </div>

                        <div class="p-3.5 rounded-[var(--ula-radius-md)] bg-white/5 border border-white/10 flex flex-col gap-1.5">
                            <div class="flex items-center justify-between">
                                <span class="text-[13px] font-semibold text-white">مساحة الابتكار</span>
                                <x-badge variant="scheduled" size="sm" dot>متاح</x-badge>
                            </div>
                            <span class="ula-headline-group">
                                <span class="ula-headline-ar" style="font-size: 11px; color: var(--ula-text-on-dark-muted);">مكتبان متاحان</span>
                                <span class="ula-headline-en" style="font-size: 9px; color: var(--ula-text-on-dark-muted); opacity: 0.75;">Innovation Lounge · 2 Desks</span>
                            </span>
                        </div>
                    </div>

                    <div class="mt-4 pt-3 border-t border-white/10 flex items-center justify-between text-[12px] text-[var(--ula-text-on-dark-muted)]">
                        <span class="flex items-center gap-1.5">
                            <span class="material-symbols-rounded" style="font-size: 16px; color: var(--ula-icon-accent);">graphic_eq</span>
                            {{ __('Spatial Audio Mesh') }}
                        </span>
                        <span class="flex items-center gap-1.5">
                            <span class="material-symbols-rounded" style="font-size: 16px; color: var(--ula-icon-highlight);">bolt</span>
                            {{ __('Ultra-low latency WebRTC') }}
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ── 1b. Spaces (Figma: Page Block — Feature/Media split) ── -->
    <section id="spaces" class="nx-features-section">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">
            <div>
                <x-badge variant="accent" :dot="true" class="mb-3">
                    {{ __('المساحات الذكية') }}
                </x-badge>
                <h2 class="text-[30px] font-semibold text-[var(--ula-text-primary)] leading-tight mb-4 font-['IBM_Plex_Sans_Arabic',sans-serif]">
                    مكتب افتراضي يشبه مكتبك الحقيقي
                </h2>
                <p class="text-[15px] text-[var(--ula-text-secondary)] mb-8 leading-relaxed">
                    {{ __('Design your floor once, and every teammate walks in to the same place — desks, meeting rooms, lounges, and quiet corners, all where you put them.') }}
                </p>
                <ul class="flex flex-col gap-5">
                    <li class="flex items-start gap-3">
                        <span class="material-symbols-rounded text-[22px] shrink-0" style="color: var(--ula-icon-accent);">chair</span>
                        <div>
                            <span class="block text-[15px] font-semibold text-[var(--ula-text-primary)]">مكاتب فردية ومساحات عمل مشتركة</span>
                            <span class="block text-[13px] text-[var(--ula-text-secondary)]">Private desks and open collaboration zones</span>
                        </div>
                    </li>
                    <li class="flex items-start gap-3">
                        <span class="material-symbols-rounded text-[22px] shrink-0" style="color: var(--ula-icon-accent);">meeting_room</span>
                        <div>
                            <span class="block text-[15px] font-semibold text-[var(--ula-text-primary)]">قاعات اجتماعات قابلة للقفل</span>
                            <span class="block text-[13px] text-[var(--ula-text-secondary)]">Lockable meeting rooms with knock-to-enter</span>
                        </div>
                    </li>
                    <li class="flex items-start gap-3">
                        <span class="material-symbols-rounded text-[22px] shrink-0" style="color: var(--ula-icon-accent);">workspaces</span>
                        <div>
                            <span class="block text-[15px] font-semibold text-[var(--ula-text-primary)]">صالات استراحة وزوايا هادئة</span>
                            <span class="block text-[13px] text-[var(--ula-text-secondary)]">Lounges and quiet corners for focus work</span>
                        </div>
                    </li>
                </ul>
            </div>
            <div class="rounded-[var(--ula-radius-xl)] overflow-hidden border border-[var(--ula-border-subtle)] shadow-[var(--ula-shadow-lg)]">
                <img src="{{ asset('images/isometric_office_preview.jpg') }}" alt="{{ __('UlaSpace virtual office floor preview') }}" class="w-full h-auto block">
            </div>
        </div>
    </section>

    <!-- ── 2. Features Grid (Figma Component: Feature Cards) ── -->
    <section id="features" class="nx-features-section">
        <div class="nx-section-header">
            <x-badge variant="accent" :dot="true" class="mb-3">
                {{ __('Platform Capabilities') }}
            </x-badge>
            <h2 class="text-[34px] font-semibold text-[var(--ula-text-primary)] leading-tight mb-3 font-['IBM_Plex_Sans_Arabic',sans-serif]">
                مصمم لبيئات العمل الحديثة التي تجمع بين التراث والابتكار
            </h2>
            <p class="text-[15px] text-[var(--ula-text-secondary)]">
                Everything you need to run a high-trust, collaborative virtual headquarters.
            </p>
        </div>

        <div class="nx-features-grid">
            <!-- Feature 1 -->
            <div class="nx-feature-card">
                <div>
                    <div class="nx-feature-icon-box">
                        <span class="material-symbols-rounded text-[28px]">graphic_eq</span>
                    </div>
                    <h3 class="text-[18px] font-semibold text-[var(--ula-text-primary)] mb-2">
                        الصوت والفيديو المكاني (Spatial Audio)
                    </h3>
                    <p class="text-[14px] text-[var(--ula-text-secondary)] leading-relaxed">
                        تواصل تلقائي يحاكي الواقع تماماً، حيث يقوى الصوت تدريجياً كلما اقتربت من زملائك على خريطة المقر دون الحاجة لروابط مكالمات معقدة.
                    </p>
                </div>
                <div class="mt-6 pt-4 border-t border-[var(--ula-border-subtle)] text-[12px] font-mono text-[var(--ula-text-muted)]">
                    Proximity-based WebRTC Mesh
                </div>
            </div>

            <!-- Feature 2 -->
            <div class="nx-feature-card">
                <div>
                    <div class="nx-feature-icon-box">
                        <span class="material-symbols-rounded text-[28px]">meeting_room</span>
                    </div>
                    <h3 class="text-[18px] font-semibold text-[var(--ula-text-primary)] mb-2">
                        قاعات اجتماعات ذكية وأبواب خاصة
                    </h3>
                    <p class="text-[14px] text-[var(--ula-text-secondary)] leading-relaxed">
                        أبواب غرف قابلة للقفل مع جرس استئذان ومشاركة شاشة بدقة فائقة وعزل صوتي كامل لضمان سرية المحادثات الاستراتيجية.
                    </p>
                </div>
                <div class="mt-6 pt-4 border-t border-[var(--ula-border-subtle)] text-[12px] font-mono text-[var(--ula-text-muted)]">
                    Isolated Audio Zones & Knocking
                </div>
            </div>

            <!-- Feature 3 -->
            <div class="nx-feature-card">
                <div>
                    <div class="nx-feature-icon-box">
                        <span class="material-symbols-rounded text-[28px]">architecture</span>
                    </div>
                    <h3 class="text-[18px] font-semibold text-[var(--ula-text-primary)] mb-2">
                        محرر الخرائط ومكتبة الأثاث التفاعلي
                    </h3>
                    <p class="text-[14px] text-[var(--ula-text-secondary)] leading-relaxed">
                        صمم مخطط مكتبك بالكامل بسحب وإفلات المكاتب الفاخرة، والنباتات، والسبورات البيضاء، والشاشات التفاعلية بسهولة.
                    </p>
                </div>
                <div class="mt-6 pt-4 border-t border-[var(--ula-border-subtle)] text-[12px] font-mono text-[var(--ula-text-muted)]">
                    Custom Floorplan Architect & Catalog
                </div>
            </div>
        </div>
    </section>

    <!-- ── 2b. Meetings (Figma Component: Meeting Row) ── -->
    <section id="meetings" class="nx-features-section">
        <div class="nx-section-header">
            <x-badge variant="accent" :dot="true" class="mb-3">
                {{ __('الاجتماعات') }}
            </x-badge>
            <h2 class="text-[34px] font-semibold text-[var(--ula-text-primary)] leading-tight mb-3 font-['IBM_Plex_Sans_Arabic',sans-serif]">
                جدول اجتماعاتك في مكان واحد
            </h2>
            <p class="text-[15px] text-[var(--ula-text-secondary)]">
                {{ __('See what\'s live, what\'s next, and who\'s in the room — before you even walk in.') }}
            </p>
        </div>

        <div class="max-w-2xl mx-auto rounded-[var(--ula-radius-lg)] border border-[var(--ula-border-subtle)] bg-[var(--ula-surface-card)] p-3 shadow-[var(--ula-shadow-xs)]">
            <div class="flex flex-col gap-2">
                <x-meeting-row
                    time="10:00"
                    title="مراجعة تصميم المنتج"
                    room="قاعة النخيل"
                    status="live"
                    :avatars="['أ', 'س', 'م']"
                />
                <x-meeting-row
                    time="11:30"
                    title="مزامنة الفريق الأسبوعية"
                    room="غرفة الابتكار"
                    status="scheduled"
                    :avatars="['ر', 'ن']"
                />
                <x-meeting-row
                    time="14:00"
                    title="مقابلة عميل جديد"
                    room="مساحة الاستقبال"
                    status="attention"
                    :avatars="['ي']"
                />
            </div>
            <p class="text-[11px] text-[var(--ula-text-muted)] text-center mt-3">{{ __('Sample schedule for illustration') }}</p>
        </div>
    </section>

    <!-- ── 3. Identity Quote Section (Figma: Heritage Section) ── -->
    <section class="nx-identity-section">
        <div class="nx-quote-card">
            <div class="w-12 h-12 rounded-full bg-[var(--ula-highlight-default)] text-white flex items-center justify-center mx-auto mb-6 shadow-[var(--ula-shadow-md)]">
                <span class="material-symbols-rounded text-[24px]">format_quote</span>
            </div>
            <p class="nx-quote-text">
                "المكان ليس مجرد جدران، بل مساحة تلتقي فيها العقول وتتدفق فيها الأفكار بحرية وشغف."
            </p>
            <span class="nx-quote-author">
                UlaSpace — Designed with Heritage & Modern Luxury
            </span>
        </div>
    </section>

    <!-- ── 4. Pricing (Figma Component: Plan Cards, reusing register.blade.php's $plans) ── -->
    <section id="pricing" class="py-20 px-6">
        <div class="nx-section-header">
            <x-badge variant="accent" :dot="true" class="mb-3">
                {{ __('الباقات والأسعار') }}
            </x-badge>
            <h2 class="text-[34px] font-semibold text-[var(--ula-text-primary)] leading-tight mb-3 font-['IBM_Plex_Sans_Arabic',sans-serif]">
                باقة تناسب حجم فريقك
            </h2>
            <p class="text-[15px] text-[var(--ula-text-secondary)]">
                {{ __('Start free, upgrade any time as your team grows.') }}
            </p>
        </div>

        @php
            $planNameAr = ['Free' => 'مجاني', 'Starter' => 'مبتدئ', 'Business' => 'أعمال', 'Enterprise' => 'مؤسسات'];
            $planHighlight = [
                'free' => ['دردشة وصوت أساسي', 'مكتب افتراضي واحد'],
                'starter' => ['مكالمات فيديو', 'مشاركة الشاشة'],
                'business' => ['تحليلات الأداء', 'علامة تجارية مخصصة'],
                'enterprise' => ['دخول موحد (SSO)', 'دعم ذو أولوية'],
            ];
        @endphp

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5 max-w-6xl mx-auto">
            @foreach($plans as $plan)
                <x-card
                    variant="{{ $plan->slug === 'business' ? 'elevated' : 'default' }}"
                    padding="lg"
                    hover
                    class="{{ $plan->slug === 'business' ? 'ring-2 ring-[var(--ula-accent-default)]' : '' }} flex flex-col"
                >
                    @if($plan->slug === 'business')
                        <x-badge variant="scheduled" size="sm" class="self-start mb-3">{{ __('الأكثر شيوعاً') }}</x-badge>
                    @endif

                    <div class="ula-headline-group mb-4">
                        <span class="ula-headline-ar" style="font-size: 19px;">{{ $planNameAr[$plan->name] ?? $plan->name }}</span>
                        <span class="ula-headline-en" style="font-size: 12px;">{{ $plan->name }}</span>
                    </div>

                    <div class="mb-5" style="direction: ltr; unicode-bidi: isolate;">
                        <span class="text-[28px] font-bold text-[var(--ula-text-primary)]" style="font-family: var(--ula-font-mono); direction: ltr; unicode-bidi: isolate;">${{ number_format($plan->price, 0) }}</span>
                        <span class="text-[13px] text-[var(--ula-text-muted)]">/mo</span>
                    </div>

                    <ul class="flex flex-col gap-2.5 mb-6 flex-1">
                        <li class="flex items-center gap-2 text-[13px] text-[var(--ula-text-body)]">
                            <span class="material-symbols-rounded text-[16px]" style="color: var(--ula-icon-accent);">group</span>
                            <span><span style="direction: ltr; unicode-bidi: isolate">{{ $plan->seat_limit === 0 ? '∞' : $plan->seat_limit }}</span> {{ __('مقعداً') }}</span>
                        </li>
                        @foreach($planHighlight[$plan->slug] ?? [] as $line)
                            <li class="flex items-center gap-2 text-[13px] text-[var(--ula-text-body)]">
                                <span class="material-symbols-rounded text-[16px]" style="color: var(--ula-icon-accent);">check_circle</span>
                                <span>{{ $line }}</span>
                            </li>
                        @endforeach
                    </ul>

                    <x-btn href="{{ route('register') }}" variant="{{ $plan->slug === 'business' ? 'primary' : 'outline' }}" size="md" class="w-full">
                        <span>{{ __('ابدأ الآن') }}</span>
                    </x-btn>
                </x-card>
            @endforeach
        </div>
    </section>

    <!-- ── 5. Final Call-to-Action ── -->
    <section class="py-20 px-6 text-center max-w-4xl mx-auto">
        <h2 class="text-[36px] font-semibold text-[var(--ula-text-primary)] mb-4 font-['IBM_Plex_Sans_Arabic',sans-serif]">
            جاهز لنقل فريقك إلى بيئة عمل المستقبل؟
        </h2>
        <p class="text-[16px] text-[var(--ula-text-secondary)] mb-8 max-w-xl mx-auto">
            انضم إلى المئات من الشركات الرائدة التي تبني ثقافة عمل قوية ومتصلة مع UlaSpace.
        </p>
        <div class="flex items-center justify-center gap-4 flex-wrap">
            <x-btn href="{{ route('register') }}" variant="primary" size="lg">
                <span>{{ __('ابدأ التجربة المجانية الآن') }}</span>
            </x-btn>
            <x-btn href="{{ route('login') }}" variant="secondary" size="lg">
                <span>{{ __('تسجيل الدخول') }}</span>
            </x-btn>
        </div>
    </section>

@endsection
