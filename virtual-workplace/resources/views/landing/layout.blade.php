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
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;500;600;700&family=IBM+Plex+Mono:wght@400;500&family=IBM+Plex+Sans:wght@300;400;500;600&family=Material+Symbols+Rounded:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200&display=swap" rel="stylesheet">

    <!-- Design System CSS -->
    <link rel="stylesheet" href="{{ asset('css/ulaspace-tokens.css') }}">
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
            scroll-margin-top: 80px;
        }

        body {
            background-color: var(--ula-surface-page);
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

        .ms {
            font-family: 'Material Symbols Rounded';
            font-weight: 400;
            font-style: normal;
            line-height: 1;
            letter-spacing: normal;
            text-transform: none;
            display: inline-block;
            white-space: nowrap;
            direction: ltr;
            font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24;
        }

        /* ── Top Fixed Navigation Bar (Matches 01-Landing: 80px, 64px padding) ── */
        .ula-landing-nav {
            position: fixed;
            top: 0;
            inset-inline: 0;
            z-index: 1000;
            height: 80px;
            padding: 0 64px;
            display: flex;
            align-items: center;
            gap: 32px;
            background: var(--ula-surface-dark);
            border-bottom: var(--ula-border-width-hairline) solid var(--ula-border-on-dark);
            transition: box-shadow var(--ula-duration-base) var(--ula-ease-in-out), background var(--ula-duration-base) var(--ula-ease-in-out);
        }

        .ula-landing-nav.scrolled {
            box-shadow: var(--ula-shadow-lg);
            background: var(--ula-surface-map-canvas);
        }

        .ula-brand-lockup {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            text-decoration: none;
            color: var(--ula-text-on-dark);
        }

        .ula-brand-lockup svg {
            flex-shrink: 0;
            display: block;
        }

        .ula-brand-name {
            font-family: var(--ula-font-en);
            font-size: 18px;
            font-weight: 500;
            color: var(--ula-text-on-dark);
        }

        .ula-nav-links {
            display: flex;
            align-items: center;
            gap: 28px;
            font-size: 15px;
            list-style: none;
        }

        .ula-nav-links a {
            color: var(--ula-text-on-dark-muted);
            text-decoration: none;
            font-size: 15px;
            transition: color var(--ula-duration-fast) var(--ula-ease-out);
        }

        .ula-nav-links a:hover {
            color: var(--ula-text-on-dark);
            text-decoration: none;
        }

        .ula-nav-spacer {
            flex: 1;
        }

        .ula-nav-actions {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .ula-nav-lang-btn {
            width: 44px;
            height: 44px;
            border-radius: var(--ula-radius-sm);
            border: var(--ula-border-width-hairline) solid var(--ula-control-dark-border);
            background: var(--ula-control-dark-fill);
            color: var(--ula-text-on-dark-subtle);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            text-decoration: none;
            cursor: pointer;
            transition: all var(--ula-duration-fast) var(--ula-ease-out);
        }

        .ula-nav-lang-btn:hover {
            background: var(--ula-control-dark-fill-hover);
            color: var(--ula-text-on-dark);
        }

        .ula-nav-login-btn {
            height: 44px;
            padding: 0 18px;
            border-radius: var(--ula-radius-md);
            border: var(--ula-border-width-hairline) solid var(--ula-control-dark-border);
            background: transparent;
            color: var(--ula-text-on-dark);
            font-family: var(--ula-font-ar);
            font-size: 15px;
            font-weight: var(--ula-weight-semibold);
            display: inline-flex;
            align-items: center;
            text-decoration: none;
            cursor: pointer;
            transition: all var(--ula-duration-fast) var(--ula-ease-out);
        }

        .ula-nav-login-btn:hover {
            background: var(--ula-control-dark-fill);
            color: var(--ula-text-on-dark);
            text-decoration: none;
        }

        .ula-nav-cta-btn {
            height: 44px;
            padding: 0 22px;
            border-radius: var(--ula-radius-md);
            border: 0;
            background: var(--ula-surface-raised);
            color: var(--ula-text-primary);
            font-family: var(--ula-font-ar);
            font-size: 15px;
            font-weight: var(--ula-weight-semibold);
            display: inline-flex;
            align-items: center;
            text-decoration: none;
            cursor: pointer;
            transition: all var(--ula-duration-fast) var(--ula-ease-out);
        }

        .ula-nav-cta-btn:hover {
            background: var(--ula-surface-page-alt);
            color: var(--ula-text-primary);
            text-decoration: none;
        }

        /* Mobile Nav Toggle & Drawer */
        .ula-mobile-toggle {
            display: none;
            width: 44px;
            height: 44px;
            align-items: center;
            justify-content: center;
            background: var(--ula-control-dark-fill);
            border: var(--ula-border-width-hairline) solid var(--ula-control-dark-border);
            color: var(--ula-text-on-dark);
            border-radius: var(--ula-radius-sm);
            cursor: pointer;
        }

        .ula-mobile-drawer {
            display: none;
            position: fixed;
            top: 80px;
            inset-inline: var(--ula-space-5);
            background: var(--ula-surface-map-chrome);
            border: var(--ula-border-width-hairline) solid var(--ula-border-on-dark);
            border-radius: var(--ula-radius-lg);
            padding: var(--ula-space-7);
            z-index: 999;
            box-shadow: var(--ula-shadow-xl);
            flex-direction: column;
            gap: var(--ula-space-4);
        }

        .ula-mobile-drawer.open {
            display: flex;
        }

        .ula-mobile-drawer a {
            color: var(--ula-text-on-dark);
            text-decoration: none;
            font-size: var(--ula-size-body);
            font-weight: var(--ula-weight-semibold);
            padding: var(--ula-space-3) var(--ula-space-4);
            border-radius: var(--ula-radius-xs);
        }

        .ula-main-wrap {
            padding-top: 80px;
            width: 100%;
        }

        /* ── Landing Footer (Matches 01-Landing: 32px 64px, top border) ── */
        .ula-landing-footer {
            padding: 32px 64px;
            border-top: var(--ula-border-width-hairline) solid var(--ula-border-subtle);
            background: var(--ula-surface-page);
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 24px;
            flex-wrap: wrap;
        }

        .ula-footer-brand {
            display: flex;
            align-items: center;
            gap: 10px;
            text-decoration: none;
            color: var(--ula-accent-default);
        }

        .ula-footer-brand-name {
            font-family: var(--ula-font-en);
            font-size: 16px;
            font-weight: 500;
            color: var(--ula-text-primary);
        }

        .ula-footer-copy {
            font-family: var(--ula-font-mono);
            font-size: 12px;
            color: var(--ula-text-muted);
            direction: ltr;
            unicode-bidi: isolate;
        }

        .ula-footer-currency {
            display: inline-flex;
            align-items: center;
            gap: var(--ula-space-2);
            background: var(--ula-surface-page-alt);
            border: var(--ula-border-width-hairline) solid var(--ula-border-default);
            border-radius: var(--ula-radius-sm);
            padding: 4px 10px;
        }

        .ula-footer-currency select {
            background: transparent;
            color: var(--ula-text-primary);
            border: none;
            outline: none;
            font-family: var(--ula-font-ar);
            font-size: var(--ula-size-xs);
            font-weight: var(--ula-weight-semibold);
            cursor: pointer;
        }

        @media (max-width: 1024px) {
            .ula-landing-nav { padding: 0 var(--ula-space-6); }
            .ula-nav-links { display: none; }
            .ula-mobile-toggle { display: inline-flex; }
            .ula-landing-footer { padding: 24px var(--ula-space-6); }
        }

        @media (max-width: 640px) {
            .ula-landing-nav { padding: 0 var(--ula-space-4); }
            .ula-nav-login-btn { display: none; }
            .ula-landing-footer { padding: 20px var(--ula-space-4); flex-direction: column; align-items: center; text-align: center; }
        }
    </style>
    @yield('styles')
</head>
<body>

    <!-- ── Top Fixed Navigation Bar (#navbar) ── -->
    <header class="ula-landing-nav" id="navbar">
        <a href="{{ route('landing.home') }}" class="ula-brand-lockup">
            <svg role="img" aria-label="UlaSpace" width="43" height="29" viewBox="-1.2 -1.3 60 40" fill="var(--ula-brand-mark-ivory)"><path d="M0 38.734L1.493 30.973L4.179 20.824L6.865 11.869C8.259 7.491 11.94 4.207 17.91 2.018C26.268 -0.569 34.427 -0.669 42.387 1.719C49.153 3.311 54.128 7.292 57.312 13.66L57.312 38.734L26.268 38.734L25.074 27.988C23.482 20.824 21.591 17.242 19.403 17.242C17.214 18.038 15.721 21.819 14.925 28.585L14.328 38.734L0 38.734Z"></path></svg>
            <span class="ula-brand-name">UlaSpace</span>
        </a>

        @php
            $siteNavItems = \App\Domains\CMS\Models\CmsThemeSetting::getByKey('main_navigation', [
                ['label_en' => 'Features', 'label_ar' => 'المزايا', 'url' => '#benefits'],
                ['label_en' => 'Spaces', 'label_ar' => 'المساحات', 'url' => '#spaces'],
                ['label_en' => 'Meetings', 'label_ar' => 'الاجتماعات', 'url' => '#meetings'],
                ['label_en' => 'Pricing', 'label_ar' => 'الأسعار', 'url' => '#pricing'],
            ]);
        @endphp

        <!-- Navigation Links -->
        <ul class="ula-nav-links">
            @foreach($siteNavItems as $item)
                @php
                    $rawUrl = $item['url'] ?? '#';
                    $targetUrl = str_starts_with($rawUrl, '#')
                        ? (request()->routeIs('landing.home') ? $rawUrl : route('landing.home') . $rawUrl)
                        : $rawUrl;
                    $label = (app()->getLocale() === 'ar' ? ($item['label_ar'] ?? $item['label_en'] ?? '') : ($item['label_en'] ?? $item['label_ar'] ?? ''));
                @endphp
                <li><a href="{{ $targetUrl }}">{{ $label }}</a></li>
            @endforeach
        </ul>

        <div class="ula-nav-spacer"></div>

        <!-- Header Actions -->
        <div class="ula-nav-actions">
            <!-- Language Switcher -->
            <a href="{{ route('lang.switch', app()->getLocale() === 'ar' ? 'en' : 'ar') }}" class="ula-nav-lang-btn" title="Toggle Language" aria-label="Toggle Language">
                <span class="ms" style="font-size: 20px;">language</span>
            </a>

            @auth
                <a href="{{ route('dashboard') }}" class="ula-nav-login-btn">{{ __('لوحة التحكم') }}</a>
                <a href="{{ route('office') }}" class="ula-nav-cta-btn">{{ __('ادخل إلى المكتب') }}</a>
            @else
                <a href="{{ route('login') }}" class="ula-nav-login-btn">{{ __('تسجيل الدخول') }}</a>
                <a href="{{ route('register') }}" class="ula-nav-cta-btn">{{ __('احجز عرضاً توضيحياً') }}</a>
            @endauth

            <button type="button" class="ula-mobile-toggle" onclick="toggleNav()" aria-label="Menu">
                <span class="ms" style="font-size: 24px;">menu</span>
            </button>
        </div>

        <!-- Mobile Drawer Menu -->
        <div class="ula-mobile-drawer" id="mobileDrawer">
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
            <hr style="border-color: var(--ula-border-on-dark); margin-block: var(--ula-space-2);">
            @auth
                <a href="{{ route('dashboard') }}">{{ __('لوحة التحكم') }}</a>
                <a href="{{ route('office') }}" style="color: var(--ula-accent-default); font-weight: 700;">{{ __('ادخل إلى المكتب') }}</a>
            @else
                <a href="{{ route('login') }}">{{ __('تسجيل الدخول') }}</a>
                <a href="{{ route('register') }}" style="color: var(--ula-tone-gold-fg); font-weight: 700;">{{ __('احجز عرضاً توضيحياً') }}</a>
            @endauth
        </div>
    </header>

    <!-- ── Main Page Content ── -->
    <main class="ula-main-wrap">
        @yield('content')
    </main>

    <!-- ── Footer (Matching 01-Landing) ── -->
    <footer class="ula-landing-footer">
        <a href="{{ route('landing.home') }}" class="ula-footer-brand">
            <svg role="img" aria-label="UlaSpace" width="38" height="25" viewBox="-1.2 -1.3 60 40" fill="var(--ula-brand-mark-green)" style="flex-shrink: 0; display: block"><path d="M0 38.734L1.493 30.973L4.179 20.824L6.865 11.869C8.259 7.491 11.94 4.207 17.91 2.018C26.268 -0.569 34.427 -0.669 42.387 1.719C49.153 3.311 54.128 7.292 57.312 13.66L57.312 38.734L26.268 38.734L25.074 27.988C23.482 20.824 21.591 17.242 19.403 17.242C17.214 18.038 15.721 21.819 14.925 28.585L14.328 38.734L0 38.734Z"></path></svg>
            <span class="ula-footer-brand-name">UlaSpace</span>
        </a>

        <!-- Currency Selector Widget -->
        <div class="ula-footer-currency">
            <span class="ms" style="font-size: 16px; color: var(--ula-icon-highlight);">payments</span>
            <select id="footerCurrencySelect" onchange="setSiteCurrency(this.value)" aria-label="Select Currency">
                <option value="SAR" selected>🇸🇦 ريال سعودي (ر.س)</option>
                <option value="USD">🇺🇸 دولار أمريكي ($ USD)</option>
                <option value="AED">🇦🇪 درهم إماراتي (د.إ)</option>
                <option value="EGP">🇪🇬 جنيه مصري (ج.م)</option>
            </select>
        </div>

        <span class="ula-footer-copy">© {{ date('Y') }} UlaSpace</span>
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

            window.dispatchEvent(new CustomEvent('currencyChanged', { detail: { currency: curr, info } }));
        }

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
