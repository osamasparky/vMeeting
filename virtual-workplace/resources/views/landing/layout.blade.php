<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'UlaSpace — مساحات العمل والمكاتب الافتراضية الذكية')</title>
    <meta name="description" content="@yield('meta_description', 'مساحات عمل افتراضية غامرة تجمع فرق العمل عن بعد مع صوت وفيديو مكاني، وتخطيط خرائط المكاتب، والاجتماعات التفاعلية.')">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- Open Graph & Social Cards -->
    <meta property="og:title" content="@yield('title', 'UlaSpace — Virtual Workplace')">
    <meta property="og:description" content="@yield('meta_description', 'Your team has a place to meet, work, collaborate, and connect — even when remote.')">
    <meta property="og:type" content="website">

    <!-- Fonts: Cairo (Primary Arabic), IBM Plex Sans (English), IBM Plex Mono (Numbers) -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;500;600;700;800;900&family=IBM+Plex+Mono:wght@400;500;600&family=IBM+Plex+Sans+Arabic:wght@400;500;600;700&family=IBM+Plex+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;1,400&family=Material+Symbols+Rounded:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200&display=swap" rel="stylesheet">

    <!-- Design System CSS -->
    <link rel="stylesheet" href="{{ asset('css/ulaspace-tokens.css') }}">
    <link rel="stylesheet" href="{{ asset('css/modern-design-system.css') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        *, *::before, *::after {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        html {
            scroll-behavior: smooth;
        }

        section[id], div[id] {
            scroll-margin-top: 84px;
        }

        /* ── Full Bleed Page with Flush Top ── */
        body {
            background-color: var(--ula-surface-page-alt);
            color: var(--ula-text-primary);
            font-family: var(--ula-font-ar);
            min-height: 100vh;
            margin: 0;
            padding: 0;
            line-height: var(--ula-lh-body);
            -webkit-font-smoothing: antialiased;
            overflow-x: hidden;
        }

        [dir="ltr"] body {
            font-family: var(--ula-font-en);
        }

        /* ── Top Fixed Navigation Bar (#56:22 / #53:69) ── */
        .nx-header {
            position: fixed;
            top: 0;
            inset-inline: 0;
            z-index: 1000;
            background: var(--ula-surface-dark);
            border-bottom: var(--ula-border-width-hairline) solid var(--ula-border-on-dark);
            padding: var(--ula-space-4) var(--ula-space-8);
            transition: padding var(--ula-duration-base) var(--ula-ease-in-out), box-shadow var(--ula-duration-base) var(--ula-ease-in-out);
        }

        .nx-header.scrolled {
            padding: var(--ula-space-3) var(--ula-space-8);
            box-shadow: var(--ula-shadow-lg);
        }

        .nx-header-container {
            max-width: var(--ula-layout-container-max);
            margin-inline: auto;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: var(--ula-space-7);
        }

        /* Brand Lockup (#27:48) */
        .nx-brand-lockup {
            display: inline-flex;
            align-items: center;
            gap: var(--ula-space-4);
            text-decoration: none;
            color: var(--ula-text-on-dark);
        }

        .nx-brand-icon {
            width: 32px;
            height: 22px;
            flex-shrink: 0;
        }

        .nx-brand-text-block {
            display: flex;
            flex-direction: column;
        }

        .nx-brand-name {
            font-family: var(--ula-font-en);
            font-size: 18px;
            font-weight: var(--ula-weight-semibold);
            color: var(--ula-text-on-dark);
            line-height: 1.1;
        }

        .nx-brand-sub {
            font-family: var(--ula-font-en);
            font-size: 9px;
            font-weight: var(--ula-weight-semibold);
            color: var(--ula-text-on-dark-subtle);
            letter-spacing: var(--ula-tracking-brand);
            text-transform: uppercase;
            margin-top: 1px;
        }

        /* Navigation Links */
        .nx-nav-menu {
            display: flex;
            align-items: center;
            gap: var(--ula-space-7);
            list-style: none;
        }

        .nx-nav-link {
            color: var(--ula-text-on-dark-muted);
            text-decoration: none;
            font-size: var(--ula-size-sm);
            font-weight: var(--ula-weight-semibold);
            transition: color var(--ula-duration-fast) var(--ula-ease-out);
        }

        .nx-nav-link:hover {
            color: var(--ula-text-on-dark);
            text-decoration: none;
        }

        /* Header Actions */
        .nx-header-actions {
            display: flex;
            align-items: center;
            gap: var(--ula-space-4);
        }

        .nx-lang-pill {
            display: inline-flex;
            align-items: center;
            gap: var(--ula-space-3);
            height: var(--ula-size-touch-target);
            padding-inline: var(--ula-space-5);
            border-radius: var(--ula-radius-pill);
            background: var(--ula-control-dark-fill);
            border: var(--ula-border-width-hairline) solid var(--ula-control-dark-border);
            color: var(--ula-text-on-dark-subtle);
            text-decoration: none;
            font-size: var(--ula-size-xs);
            font-weight: var(--ula-weight-semibold);
            font-family: var(--ula-font-en);
            transition: all var(--ula-duration-fast) var(--ula-ease-out);
        }

        .nx-lang-pill:hover {
            background: var(--ula-control-dark-fill-hover);
            color: var(--ula-text-on-dark);
            text-decoration: none;
        }

        /* Mobile Toggle & Drawer */
        .nx-mobile-toggle {
            display: none;
            min-width: var(--ula-size-touch-target);
            min-height: var(--ula-size-touch-target);
            align-items: center;
            justify-content: center;
            background: var(--ula-control-dark-fill);
            border: var(--ula-border-width-hairline) solid var(--ula-control-dark-border);
            color: var(--ula-text-on-dark);
            font-size: 20px;
            border-radius: var(--ula-radius-sm);
            cursor: pointer;
        }

        .nx-mobile-drawer {
            display: none;
            position: fixed;
            top: 76px;
            inset-inline: var(--ula-space-5);
            background: var(--ula-surface-dark-alt);
            border: var(--ula-border-width-hairline) solid var(--ula-border-on-dark);
            border-radius: var(--ula-radius-lg);
            padding: var(--ula-space-7);
            z-index: 999;
            box-shadow: var(--ula-shadow-xl);
        }

        .nx-mobile-drawer.open {
            display: flex;
            flex-direction: column;
            gap: var(--ula-space-4);
        }

        .nx-mobile-drawer a {
            color: var(--ula-text-on-dark);
            text-decoration: none;
            font-size: var(--ula-size-body);
            font-weight: var(--ula-weight-semibold);
            padding: var(--ula-space-3) var(--ula-space-4);
            border-radius: var(--ula-radius-xs);
        }

        /* ── Master Page Container ── */
        .nx-main-wrap {
            padding-top: 64px;
            width: 100%;
        }

        /* ── Full-Width Master Footer — light surface, green mark (Brand mark rules) ── */
        .nx-footer {
            background: var(--ula-surface-page);
            color: var(--ula-text-secondary);
            padding: var(--ula-space-13) var(--ula-space-8) var(--ula-space-8);
            border-top: var(--ula-border-width-hairline) solid var(--ula-border-subtle);
        }

        .nx-footer-container {
            max-width: var(--ula-layout-container-max);
            margin-inline: auto;
            display: grid;
            grid-template-columns: 2fr 1fr 1fr 1.2fr;
            gap: var(--ula-space-10);
            margin-bottom: var(--ula-space-10);
        }

        .nx-footer-col h4 {
            font-family: var(--ula-font-en);
            font-size: var(--ula-size-sm);
            font-weight: var(--ula-weight-bold);
            color: var(--ula-text-primary);
            margin-bottom: var(--ula-space-6);
            text-transform: uppercase;
            letter-spacing: 0.08em;
        }

        .nx-footer-links {
            list-style: none;
            display: flex;
            flex-direction: column;
            gap: var(--ula-space-4);
        }

        .nx-footer-links a {
            color: var(--ula-text-secondary);
            text-decoration: none;
            font-size: var(--ula-size-sm);
            transition: color var(--ula-duration-fast) var(--ula-ease-out);
        }

        .nx-footer-links a:hover {
            color: var(--ula-text-link-hover);
            text-decoration: underline;
        }

        .nx-footer-bottom {
            max-width: var(--ula-layout-container-max);
            margin-inline: auto;
            padding-top: var(--ula-space-7);
            border-top: var(--ula-border-width-hairline) solid var(--ula-border-subtle);
            display: flex;
            align-items: center;
            justify-content: space-between;
            font-size: var(--ula-size-xs);
            color: var(--ula-text-muted);
            flex-wrap: wrap;
            gap: var(--ula-space-5);
        }

        .nx-footer-tagline {
            font-family: var(--ula-font-en);
            font-size: var(--ula-size-label);
            letter-spacing: var(--ula-tracking-brand);
            text-transform: uppercase;
            color: var(--ula-text-muted);
        }

        /* ── Currency Picker Widget in Footer ── */
        .nx-currency-box {
            margin-top: var(--ula-space-6);
            background: var(--ula-surface-page-alt);
            border: var(--ula-border-width-hairline) solid var(--ula-border-default);
            border-radius: var(--ula-radius-sm);
            padding: var(--ula-space-3) var(--ula-space-4);
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: var(--ula-space-3);
        }

        .nx-currency-select {
            background: transparent;
            color: var(--ula-text-primary);
            border: none;
            outline: none;
            font-family: var(--ula-font-ar);
            font-size: var(--ula-size-sm);
            font-weight: var(--ula-weight-bold);
            cursor: pointer;
            width: 100%;
        }

        .nx-currency-select option {
            background: var(--ula-surface-raised);
            color: var(--ula-text-primary);
        }

        /* Footer sits on surface/page, which is light in light mode but flips to a
           dark palm tone in dark mode — the mark must follow the surface, not stay
           permanently green (see FIX_BRIEF.md "pick by surface, not by page"). */
        .nx-footer-mark { fill: var(--ula-brand-mark-green); }
        @media (prefers-color-scheme: dark) {
            :root:not([data-theme="light"]) .nx-footer-mark { fill: var(--ula-brand-mark-ivory); }
        }
        [data-theme="dark"] .nx-footer-mark, .dark .nx-footer-mark {
            fill: var(--ula-brand-mark-ivory);
        }

        /* Responsive */
        @media (max-width: 1024px) {
            .nx-nav-menu { display: none; }
            .nx-mobile-toggle { display: flex; }
            .nx-footer-container { grid-template-columns: 1fr 1fr; gap: var(--ula-space-8); }
        }

        @media (max-width: 640px) {
            .nx-header { padding: var(--ula-space-3) var(--ula-space-5); }
            .nx-footer { padding: var(--ula-space-10) var(--ula-space-5) var(--ula-space-6); }
            .nx-footer-container { grid-template-columns: 1fr; gap: var(--ula-space-7); }
            .nx-footer-bottom { flex-direction: column; align-items: flex-start; }
        }
    </style>
    @yield('styles')
</head>
<body>

    <!-- ── Top Fixed Navigation Bar (#56:22 / #53:69) — dark chrome, ivory mark ── -->
    <header class="nx-header" id="navbar">
        <div class="nx-header-container">
            <!-- Brand Lockup (#27:48) -->
            <a href="{{ route('landing.home') }}" class="nx-brand-lockup">
                <svg class="nx-brand-icon" viewBox="0 0 100 67" fill="var(--ula-brand-mark-ivory)" xmlns="http://www.w3.org/2000/svg">
                    <path fill-rule="evenodd" clip-rule="evenodd" d="M0 67C0 24 16 0 52 0C84 0 100 24 100 67H46C46 38 38 28 28 28C18 28 14 38 14 67H0Z"/>
                </svg>
                <div class="nx-brand-text-block">
                    <span class="nx-brand-name">UlaSpace</span>
                    <span class="nx-brand-sub">{{ __('المكتب الافتراضي') }}</span>
                </div>
            </a>

            @php
                $siteNavItems = \App\Domains\CMS\Models\CmsThemeSetting::getByKey('main_navigation', [
                    ['label_en' => 'Spaces', 'label_ar' => 'المساحات الذكية', 'url' => '#spaces'],
                    ['label_en' => 'Benefits', 'label_ar' => 'مميزات النظام', 'url' => '#benefits'],
                    ['label_en' => 'Meetings', 'label_ar' => 'الاجتماعات', 'url' => '#meetings'],
                    ['label_en' => 'Heritage', 'label_ar' => 'عن المنصة', 'url' => '#identity'],
                    ['label_en' => 'Pricing', 'label_ar' => 'الباقات والاشتراكات', 'url' => '#pricing'],
                ]);
            @endphp

            <!-- Navigation Links (#53:75) -->
            <ul class="nx-nav-menu">
                @foreach($siteNavItems as $item)
                    @php
                        $rawUrl = $item['url'] ?? '#';
                        $targetUrl = str_starts_with($rawUrl, '#')
                            ? (request()->routeIs('landing.home') ? $rawUrl : route('landing.home') . $rawUrl)
                            : $rawUrl;
                        $label = (app()->getLocale() === 'ar' ? ($item['label_ar'] ?? $item['label_en'] ?? '') : ($item['label_en'] ?? $item['label_ar'] ?? ''));
                    @endphp
                    <li><a href="{{ $targetUrl }}" class="nx-nav-link">{{ $label }}</a></li>
                @endforeach
            </ul>

            <!-- Header Actions (#53:81) -->
            <div class="nx-header-actions">
                <!-- Language Switcher (#44:134) -->
                <a href="{{ route('lang.switch', app()->getLocale() === 'ar' ? 'en' : 'ar') }}" class="nx-lang-pill">
                    <span class="material-symbols-rounded text-[16px]">language</span>
                    <span>{{ app()->getLocale() === 'ar' ? 'English' : 'العربية' }}</span>
                </a>

                @auth
                    <x-btn href="{{ route('office') }}" variant="nav-cta" size="md" icon="apartment" pill>{{ __('ادخل المقر') }}</x-btn>
                @else
                    <!-- Nav CTA Pill (#53:87) — control/cta-on-dark, never gold -->
                    <x-btn href="{{ route('register') }}" variant="nav-cta" size="md" pill>{{ app()->getLocale() === 'ar' ? 'ابدأ مجاناً' : 'Try Free' }}</x-btn>
                @endauth

                <button type="button" class="nx-mobile-toggle" onclick="toggleNav()" aria-label="Menu">
                    <span class="material-symbols-rounded">menu</span>
                </button>
            </div>
        </div>

        <!-- Mobile Drawer Menu -->
        <div class="nx-mobile-drawer" id="mobileDrawer">
            @foreach($siteNavItems as $item)
                @php
                    $rawUrl = $item['url'] ?? '#';
                    $targetUrl = str_starts_with($rawUrl, '#')
                        ? (request()->routeIs('landing.home') ? $rawUrl : route('landing.home') . $rawUrl)
                        : $rawUrl;
                    $label = (app()->getLocale() === 'ar' ? ($item['label_ar'] ?? $item['label_en'] ?? '') : ($item['label_en'] ?? $item['label_ar'] ?? ''));
                @endphp
                <a href="{{ $targetUrl }}" onclick="toggleNav()">{{ $label }}</a>
            @endforeach
            <hr style="border-color: var(--ula-border-on-dark-subtle); margin-block: var(--ula-space-3);">
            @auth
                <x-btn href="{{ route('office') }}" variant="nav-cta" size="md" pill>{{ __('ادخل المقر') }}</x-btn>
            @else
                <a href="{{ route('login') }}" style="color: var(--ula-text-on-dark);">{{ __('تسجيل الدخول') }}</a>
                <x-btn href="{{ route('register') }}" variant="nav-cta" size="md" pill>{{ app()->getLocale() === 'ar' ? 'ابدأ مجاناً' : 'Try Free' }}</x-btn>
            @endauth
        </div>
    </header>

    <!-- ── Main Page Content ── -->
    <div class="nx-main-wrap">
        @yield('content')
    </div>

    <!-- ── Full-Width Master Footer — light surface, green mark (Brand mark rules §"footer") ── -->
    <footer class="nx-footer">
        <div class="nx-footer-container">
            <div>
                <div class="nx-brand-lockup" style="margin-bottom: var(--ula-space-4); color: var(--ula-text-primary);">
                    <svg class="nx-brand-icon nx-footer-mark" viewBox="0 0 100 67" xmlns="http://www.w3.org/2000/svg">
                        <path fill-rule="evenodd" clip-rule="evenodd" d="M0 67C0 24 16 0 52 0C84 0 100 24 100 67H46C46 38 38 28 28 28C18 28 14 38 14 67H0Z"/>
                    </svg>
                    <div class="nx-brand-text-block">
                        <span class="nx-brand-name" style="font-size: 20px; color: var(--ula-text-primary);">UlaSpace</span>
                        <span class="nx-brand-sub" style="color: var(--ula-text-muted);">{{ __('المكتب الافتراضي') }}</span>
                    </div>
                </div>
                <div class="nx-footer-tagline">
                    SPACES CARVED, NOT BUILT.
                </div>
                <p style="font-size: var(--ula-size-sm); color: var(--ula-text-secondary); max-width: 320px; line-height: var(--ula-lh-body); margin-top: var(--ula-space-4);">
                    {{ __("صُمّمت UlaSpace بإلهام من العلا لفرق تعمل من كل مكان: أصالة الضيافة في التفاصيل، وهندسة عالمية رائدة في الأداء.") }}
                </p>
            </div>

            <div class="nx-footer-col">
                <h4>{{ __('المنتج والمساحات') }}</h4>
                <ul class="nx-footer-links">
                    <li><a href="#spaces">{{ __('المكاتب الافتراضية') }}</a></li>
                    <li><a href="#benefits">{{ __('الصوت والفيديو المكاني') }}</a></li>
                    <li><a href="#meetings">{{ __('قاعات الاجتماعات الذكية') }}</a></li>
                    <li><a href="#pricing">{{ __('خطط الاشتراك والأسعار') }}</a></li>
                </ul>
            </div>

            <div class="nx-footer-col">
                <h4>{{ __('الشركة والموارد') }}</h4>
                <ul class="nx-footer-links">
                    <li><a href="{{ route('register') }}">{{ __('إنشاء مساحة جديدة') }}</a></li>
                    <li><a href="{{ route('login') }}">{{ __('تسجيل الدخول') }}</a></li>
                    <li><a href="#identity">{{ __('عن UlaSpace') }}</a></li>
                    <li><a href="mailto:contact@ulaspace.com">{{ __('فريق المبيعات') }}</a></li>
                </ul>
            </div>

            <div class="nx-footer-col">
                <h4>{{ __('المقر والعملة') }}</h4>
                <div style="font-family: var(--ula-font-en); font-size: var(--ula-size-label); font-weight: var(--ula-weight-semibold); letter-spacing: var(--ula-tracking-brand); color: var(--ula-text-muted); line-height: 1.8; text-transform: uppercase;">
                    ALULA · SAUDI ARABIA<br>THE WORLD
                </div>

                <!-- Currency Changer Widget in Footer (Default: SAR / ر.س) -->
                <div class="nx-currency-box">
                    <span class="material-symbols-rounded text-[18px]" style="color: var(--ula-icon-highlight);">payments</span>
                    <select id="footerCurrencySelect" onchange="setSiteCurrency(this.value)" class="nx-currency-select" aria-label="Select Currency">
                        <option value="SAR" selected>🇸🇦 ريال سعودي (ر.س)</option>
                        <option value="USD">🇺🇸 دولار أمريكي ($ USD)</option>
                        <option value="AED">🇦🇪 درهم إماراتي (د.إ)</option>
                        <option value="EGP">🇪🇬 جنيه مصري (ج.م)</option>
                    </select>
                </div>

                <div style="margin-top: var(--ula-space-5); display: inline-flex; align-items: center; gap: var(--ula-space-3); font-size: var(--ula-size-xs); color: var(--ula-text-secondary);">
                    <span style="width: 7px; height: 7px; border-radius: var(--ula-radius-pill); background: var(--ula-status-success); box-shadow: 0 0 8px var(--ula-status-success);"></span>
                    <span>{{ __('All systems operational') }}</span>
                </div>
            </div>
        </div>

        <div class="nx-footer-bottom">
            <div>
                © {{ date('Y') }} UlaSpace Inc. {{ __('جميع الحقوق محفوظة.') }}
            </div>
            <div style="font-family: var(--ula-font-en); font-size: var(--ula-size-label); color: var(--ula-text-muted);">
                Crafted for High-Performing Distributed Teams
            </div>
        </div>
    </footer>

    <script nonce="{{ $cspNonce ?? '' }}">
        // Currency conversion rates (Default: SAR)
        const CURRENCY_RATES = {
            'SAR': { rate: 3.75, symbol: 'ر.س', label: 'ر.س', pos: 'after' },
            'USD': { rate: 1.0, symbol: '$', label: '$', pos: 'before' },
            'AED': { rate: 3.67, symbol: 'د.إ', label: 'د.إ', pos: 'after' },
            'EGP': { rate: 48.5, symbol: 'ج.م', label: 'ج.م', pos: 'after' }
        };

        function setSiteCurrency(curr) {
            if (!CURRENCY_RATES[curr]) curr = 'SAR';
            localStorage.setItem('ulaspace_currency', curr);

            const select = document.getElementById('footerCurrencySelect');
            if (select && select.value !== curr) {
                select.value = curr;
            }

            const info = CURRENCY_RATES[curr];
            document.querySelectorAll('[data-plan-usd]').forEach(el => {
                const usd = parseFloat(el.getAttribute('data-plan-usd')) || 0;
                if (usd === 0) {
                    el.innerText = '0';
                } else {
                    const converted = Math.round(usd * info.rate);
                    el.innerText = converted.toLocaleString();
                }
            });

            document.querySelectorAll('.nx-currency-symbol').forEach(el => {
                el.innerText = info.symbol;
            });

            // Dispatch global event
            window.dispatchEvent(new CustomEvent('currencyChanged', { detail: { currency: curr, info } }));
        }

        // Initialize currency from localStorage or default to SAR
        document.addEventListener('DOMContentLoaded', () => {
            const saved = localStorage.getItem('ulaspace_currency') || 'SAR';
            setSiteCurrency(saved);
        });

        function toggleNav() {
            const drawer = document.getElementById('mobileDrawer');
            if (drawer) {
                drawer.classList.toggle('open');
            }
        }

        window.addEventListener('scroll', () => {
            const nav = document.getElementById('navbar');
            if (window.scrollY > 20) {
                nav?.classList.add('scrolled');
            } else {
                nav?.classList.remove('scrolled');
            }
        });
    </script>
    @yield('scripts')
</body>
</html>
