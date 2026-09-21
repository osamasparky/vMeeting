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

        :root {
            --ula-canvas-bg: #F4EDE1;
            --ula-nav-dark: #142B24;
            --ula-nav-text: #C1B6A6;
            --ula-nav-cta: #EDE6D9;
            --ula-accent-green: #1E412F;
            --ula-text-dark: #142B24;
            --ula-text-body: #4A443C;
            --ula-text-muted: #665D52;
            --ula-sand-light: #F9F6EF;
            --ula-border-sand: #E8E4DC;
            --ula-layout-max: 1280px;
        }

        html {
            scroll-behavior: smooth;
        }

        /* ── Full Bleed Page with Flush Top ── */
        body {
            background-color: var(--ula-canvas-bg);
            color: var(--ula-text-dark);
            font-family: 'Cairo', 'IBM Plex Sans Arabic', -apple-system, sans-serif;
            min-height: 100vh;
            margin: 0;
            padding: 0;
            line-height: 1.6;
            -webkit-font-smoothing: antialiased;
            overflow-x: hidden;
        }

        [dir="ltr"] body {
            font-family: 'IBM Plex Sans', -apple-system, sans-serif;
        }

        /* ── Top Fixed Navigation Bar (#56:22 / #53:69) ── */
        .nx-header {
            position: fixed;
            top: 0;
            inset-inline: 0;
            z-index: 1000;
            background: rgba(20, 43, 36, 0.96);
            backdrop-filter: blur(14px);
            -webkit-backdrop-filter: blur(14px);
            border-bottom: 1px solid rgba(237, 230, 217, 0.12);
            padding: 14px 32px;
            transition: all 0.2s ease;
        }

        .nx-header.scrolled {
            padding: 10px 32px;
            background: rgba(14, 30, 25, 0.98);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.25);
        }

        .nx-header-container {
            max-width: var(--ula-layout-max);
            margin: 0 auto;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 24px;
        }

        /* Brand Lockup (#27:48) */
        .nx-brand-lockup {
            display: inline-flex;
            align-items: center;
            gap: 12px;
            text-decoration: none;
            color: #F9F6EF;
        }

        .nx-brand-icon {
            width: 32px;
            height: 22px;
            fill: #EDE6D9;
            flex-shrink: 0;
        }

        .nx-brand-text-block {
            display: flex;
            flex-direction: column;
        }

        .nx-brand-name {
            font-family: 'IBM Plex Sans', sans-serif;
            font-size: 18px;
            font-weight: 600;
            color: #F9F6EF;
            line-height: 1.1;
        }

        .nx-brand-sub {
            font-family: 'IBM Plex Sans', sans-serif;
            font-size: 9px;
            font-weight: 600;
            color: #C1B6A6;
            letter-spacing: 0.12em;
            text-transform: uppercase;
            margin-top: 1px;
        }

        /* Navigation Links */
        .nx-nav-menu {
            display: flex;
            align-items: center;
            gap: 24px;
            list-style: none;
        }

        .nx-nav-link {
            color: var(--ula-nav-text);
            text-decoration: none;
            font-size: 14px;
            font-weight: 600;
            transition: color 0.15s ease;
        }

        .nx-nav-link:hover {
            color: #F9F6EF;
        }

        /* Header Actions */
        .nx-header-actions {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .nx-lang-pill {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 6px 14px;
            border-radius: 999px;
            background: rgba(237, 230, 217, 0.08);
            border: 1px solid rgba(237, 230, 217, 0.16);
            color: #C1B6A6;
            text-decoration: none;
            font-size: 12px;
            font-weight: 600;
            font-family: 'IBM Plex Sans', sans-serif;
            transition: all 0.15s ease;
        }

        .nx-lang-pill:hover {
            background: rgba(237, 230, 217, 0.16);
            color: #F9F6EF;
        }

        .nx-nav-cta {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 9px 22px;
            background: var(--ula-nav-cta);
            color: var(--ula-nav-dark);
            font-size: 13px;
            font-weight: 700;
            text-decoration: none;
            border-radius: 999px;
            transition: all 0.15s ease;
            white-space: nowrap;
        }

        .nx-nav-cta:hover {
            background: #F9F6EF;
            transform: translateY(-1px);
            box-shadow: 0 4px 14px rgba(0, 0, 0, 0.15);
        }

        /* Mobile Toggle & Drawer */
        .nx-mobile-toggle {
            display: none;
            background: rgba(237, 230, 217, 0.08);
            border: 1px solid rgba(237, 230, 217, 0.15);
            color: #F9F6EF;
            font-size: 20px;
            padding: 6px 10px;
            border-radius: 8px;
            cursor: pointer;
        }

        .nx-mobile-drawer {
            display: none;
            position: fixed;
            top: 66px;
            inset-inline: 16px;
            background: rgba(16, 36, 30, 0.98);
            border: 1px solid rgba(237, 230, 217, 0.15);
            border-radius: 18px;
            padding: 24px;
            z-index: 999;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.4);
            backdrop-filter: blur(20px);
        }

        .nx-mobile-drawer.open {
            display: flex;
            flex-direction: column;
            gap: 14px;
        }

        .nx-mobile-drawer a {
            color: #F9F6EF;
            text-decoration: none;
            font-size: 15px;
            font-weight: 600;
            padding: 8px 12px;
            border-radius: 8px;
        }

        /* ── Master Page Container ── */
        .nx-main-wrap {
            padding-top: 64px;
            width: 100%;
        }

        /* ── Full-Width Master Footer (#56:130 / #53:124) ── */
        .nx-footer {
            background: #142B24;
            color: #C1B6A6;
            padding: 64px 32px 32px;
            border-top: 1px solid rgba(237, 230, 217, 0.12);
        }

        .nx-footer-container {
            max-width: var(--ula-layout-max);
            margin: 0 auto;
            display: grid;
            grid-template-columns: 2fr 1fr 1fr 1.2fr;
            gap: 48px;
            margin-bottom: 48px;
        }

        .nx-footer-col h4 {
            font-family: 'IBM Plex Sans', sans-serif;
            font-size: 13px;
            font-weight: 700;
            color: #F9F6EF;
            margin-bottom: 18px;
            text-transform: uppercase;
            letter-spacing: 0.08em;
        }

        .nx-footer-links {
            list-style: none;
            display: flex;
            flex-direction: column;
            gap: 12px;
        }

        .nx-footer-links a {
            color: #C1B6A6;
            text-decoration: none;
            font-size: 13.5px;
            transition: color 0.15s ease;
        }

        .nx-footer-links a:hover {
            color: #F9F6EF;
        }

        .nx-footer-bottom {
            max-width: var(--ula-layout-max);
            margin: 0 auto;
            padding-top: 24px;
            border-top: 1px solid rgba(237, 230, 217, 0.08);
            display: flex;
            align-items: center;
            justify-content: space-between;
            font-size: 12px;
            color: #857A6C;
            flex-wrap: wrap;
            gap: 16px;
        }

        .nx-footer-tagline {
            font-family: 'IBM Plex Sans', sans-serif;
            font-size: 11px;
            letter-spacing: 0.14em;
            text-transform: uppercase;
            color: #EDE6D9;
        }

        /* ── Currency Picker Widget in Footer ── */
        .nx-currency-box {
            margin-top: 20px;
            background: rgba(237, 230, 217, 0.06);
            border: 1px solid rgba(237, 230, 217, 0.14);
            border-radius: 12px;
            padding: 10px 14px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
        }

        .nx-currency-select {
            background: transparent;
            color: #F9F6EF;
            border: none;
            outline: none;
            font-family: 'Cairo', 'IBM Plex Sans', sans-serif;
            font-size: 13px;
            font-weight: 700;
            cursor: pointer;
            width: 100%;
        }

        .nx-currency-select option {
            background: #142B24;
            color: #F9F6EF;
        }

        /* Responsive */
        @media (max-width: 1024px) {
            .nx-nav-menu { display: none; }
            .nx-mobile-toggle { display: block; }
            .nx-footer-container { grid-template-columns: 1fr 1fr; gap: 36px; }
        }

        @media (max-width: 640px) {
            .nx-header { padding: 12px 18px; }
            .nx-footer { padding: 48px 20px 24px; }
            .nx-footer-container { grid-template-columns: 1fr; gap: 28px; }
            .nx-footer-bottom { flex-direction: column; align-items: flex-start; }
        }
    </style>
    @yield('styles')
</head>
<body>

    <!-- ── Top Fixed Navigation Bar (#56:22 / #53:69) ── -->
    <header class="nx-header" id="navbar">
        <div class="nx-header-container">
            <!-- Brand Lockup (#27:48) -->
            <a href="{{ route('landing.home') }}" class="nx-brand-lockup">
                <!-- Precision Jabal AlFil Mountain Arch Shape -->
                <svg class="nx-brand-icon" viewBox="0 0 100 67" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path fill-rule="evenodd" clip-rule="evenodd" d="M0 67C0 24 16 0 52 0C84 0 100 24 100 67H46C46 38 38 28 28 28C18 28 14 38 14 67H0Z" fill="#EDE6D9"/>
                </svg>
                <div class="nx-brand-text-block">
                    <span class="nx-brand-name">UlaSpace</span>
                    <span class="nx-brand-sub">{{ __('المكتب الافتراضي') }}</span>
                </div>
            </a>

            <!-- Navigation Links (#53:75) -->
            <ul class="nx-nav-menu">
                <li><a href="#spaces" class="nx-nav-link">{{ app()->getLocale() === 'ar' ? 'المساحات الذكية' : 'Spaces' }}</a></li>
                <li><a href="#benefits" class="nx-nav-link">{{ app()->getLocale() === 'ar' ? 'مميزات النظام' : 'Benefits' }}</a></li>
                <li><a href="#meetings" class="nx-nav-link">{{ app()->getLocale() === 'ar' ? 'الاجتماعات' : 'Meetings' }}</a></li>
                <li><a href="#identity" class="nx-nav-link">{{ app()->getLocale() === 'ar' ? 'عن المنصة' : 'Heritage' }}</a></li>
                <li><a href="#pricing" class="nx-nav-link">{{ app()->getLocale() === 'ar' ? 'الباقات والاشتراكات' : 'Pricing' }}</a></li>
            </ul>

            <!-- Header Actions (#53:81) -->
            <div class="nx-header-actions">
                <!-- Language Switcher (#44:134) -->
                <a href="{{ route('lang.switch', app()->getLocale() === 'ar' ? 'en' : 'ar') }}" class="nx-lang-pill">
                    <span class="material-symbols-rounded text-[14px]">language</span>
                    <span>{{ app()->getLocale() === 'ar' ? 'English' : 'العربية' }}</span>
                </a>

                @auth
                    <a href="{{ route('office') }}" class="nx-nav-cta">
                        <span class="material-symbols-rounded text-[16px] mr-1">apartment</span>
                        <span>{{ __('ادخل المقر') }}</span>
                    </a>
                @else
                    <!-- Nav CTA Ivory Pill (#53:87) -->
                    <a href="{{ route('register') }}" class="nx-nav-cta">
                        <span>{{ app()->getLocale() === 'ar' ? 'ابدأ مجاناً' : 'Try Free' }}</span>
                    </a>
                @endauth

                <button type="button" class="nx-mobile-toggle" onclick="toggleNav()" aria-label="Menu">
                    <span class="material-symbols-rounded">menu</span>
                </button>
            </div>
        </div>

        <!-- Mobile Drawer Menu -->
        <div class="nx-mobile-drawer" id="mobileDrawer">
            <a href="#spaces" onclick="toggleNav()">{{ app()->getLocale() === 'ar' ? 'المساحات الذكية' : 'Spaces' }}</a>
            <a href="#benefits" onclick="toggleNav()">{{ app()->getLocale() === 'ar' ? 'مميزات النظام' : 'Benefits' }}</a>
            <a href="#meetings" onclick="toggleNav()">{{ app()->getLocale() === 'ar' ? 'الاجتماعات' : 'Meetings' }}</a>
            <a href="#identity" onclick="toggleNav()">{{ app()->getLocale() === 'ar' ? 'عن المنصة' : 'Heritage' }}</a>
            <a href="#pricing" onclick="toggleNav()">{{ app()->getLocale() === 'ar' ? 'الباقات والاشتراكات' : 'Pricing' }}</a>
            <hr style="border-color: rgba(237, 230, 217, 0.1);">
            @auth
                <a href="{{ route('office') }}" class="nx-nav-cta" style="text-align: center;">{{ __('ادخل المقر') }}</a>
            @else
                <a href="{{ route('login') }}" style="color: #EDE6D9;">{{ __('تسجيل الدخول') }}</a>
                <a href="{{ route('register') }}" class="nx-nav-cta" style="text-align: center;">{{ app()->getLocale() === 'ar' ? 'ابدأ مجاناً' : 'Try Free' }}</a>
            @endauth
        </div>
    </header>

    <!-- ── Main Page Content ── -->
    <div class="nx-main-wrap">
        @yield('content')
    </div>

    <!-- ── Full-Width Master Footer (#56:130 / #53:124) ── -->
    <footer class="nx-footer">
        <div class="nx-footer-container">
            <div>
                <div class="nx-brand-lockup" style="margin-bottom: 14px;">
                    <svg class="nx-brand-icon" viewBox="0 0 100 67" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path fill-rule="evenodd" clip-rule="evenodd" d="M0 67C0 24 16 0 52 0C84 0 100 24 100 67H46C46 38 38 28 28 28C18 28 14 38 14 67H0Z" fill="#EDE6D9"/>
                    </svg>
                    <div class="nx-brand-text-block">
                        <span class="nx-brand-name" style="font-size: 20px;">UlaSpace</span>
                        <span class="nx-brand-sub">{{ __('المكتب الافتراضي') }}</span>
                    </div>
                </div>
                <div class="nx-footer-tagline">
                    SPACES CARVED, NOT BUILT.
                </div>
                <p style="font-size: 13.5px; color: #857A6C; max-width: 320px; line-height: 1.7; margin-top: 14px;">
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
                <div style="font-family: 'IBM Plex Sans', sans-serif; font-size: 11px; font-weight: 600; letter-spacing: 0.12em; color: #C1B6A6; line-height: 1.8; text-transform: uppercase;">
                    ALULA · SAUDI ARABIA<br>THE WORLD
                </div>

                <!-- Currency Changer Widget in Footer (Default: SAR / ر.س) -->
                <div class="nx-currency-box">
                    <span class="material-symbols-rounded text-[18px] text-[#D3A553]">payments</span>
                    <select id="footerCurrencySelect" onchange="setSiteCurrency(this.value)" class="nx-currency-select" aria-label="Select Currency">
                        <option value="SAR" selected>🇸🇦 ريال سعودي (ر.س)</option>
                        <option value="USD">🇺🇸 دولار أمريكي ($ USD)</option>
                        <option value="AED">🇦🇪 درهم إماراتي (د.إ)</option>
                        <option value="EGP">🇪🇬 جنيه مصري (ج.م)</option>
                    </select>
                </div>

                <div style="margin-top: 16px; display: inline-flex; align-items: center; gap: 6px; font-size: 12px; color: #DBE6DD;">
                    <span style="width: 7px; height: 7px; border-radius: 50%; background: #3C6B4C; box-shadow: 0 0 8px #3C6B4C;"></span>
                    <span>{{ __('All systems operational') }}</span>
                </div>
            </div>
        </div>

        <div class="nx-footer-bottom">
            <div>
                © {{ date('Y') }} UlaSpace Inc. {{ __('جميع الحقوق محفوظة.') }}
            </div>
            <div style="font-family: 'IBM Plex Sans', sans-serif; font-size: 11px; color: #857A6C;">
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
