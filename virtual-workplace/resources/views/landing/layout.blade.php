<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'UlaSpace — منصة مساحات العمل والمكاتب الافتراضية الذكية')</title>
    <meta name="description" content="@yield('meta_description', 'مساحات عمل افتراضية غامرة تجمع فرق العمل عن بعد مع صوت وفيديو مكاني، وتخطيط خرائط المكاتب، والاجتماعات التفاعلية.')">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- Open Graph & Social Cards -->
    <meta property="og:title" content="@yield('title', 'UlaSpace — Virtual Workplace')">
    <meta property="og:description" content="@yield('meta_description', 'Your team has a place to meet, work, collaborate, and connect — even when remote.')">
    <meta property="og:type" content="website">

    <!-- Design System CSS -->
    <link rel="stylesheet" href="{{ asset('css/ulaspace-tokens.css') }}">
    <link rel="stylesheet" href="{{ asset('css/modern-design-system.css') }}">

    <!-- Three.js for 3D Spatial Canvas -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/three.js/r128/three.min.js" nonce="{{ $cspNonce ?? '' }}"></script>

    <style>
        *, *::before, *::after {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        html {
            scroll-behavior: smooth;
        }

        body {
            font-family: var(--nx-font-family);
            background-color: var(--nx-bg-page);
            color: var(--nx-text-primary);
            min-height: 100vh;
            overflow-x: hidden;
            line-height: 1.6;
            -webkit-font-smoothing: antialiased;
        }

        /* ── Dark Marketing Navigation Header ── */
        .nx-header {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 1000;
            padding: 14px 32px;
            transition: all var(--nx-duration-base) var(--nx-ease-standard);
            backdrop-filter: var(--nx-backdrop-blur);
            -webkit-backdrop-filter: var(--nx-backdrop-blur);
            background: rgba(20, 43, 36, 0.92);
            border-bottom: 1px solid rgba(237, 230, 217, 0.12);
        }

        .nx-header-container {
            max-width: var(--nx-container-max);
            margin: 0 auto;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 24px;
        }

        .nx-brand-lockup {
            display: flex;
            align-items: center;
            gap: 12px;
            text-decoration: none;
            color: #ffffff;
        }

        .nx-brand-arch {
            width: 36px;
            height: 40px;
            border-radius: 18px 18px 4px 4px;
            background: var(--nx-accent);
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 4px 16px rgba(211, 165, 83, 0.35);
        }

        .nx-brand-text h1 {
            font-family: var(--nx-font-en);
            font-size: 20px;
            font-weight: 600;
            letter-spacing: -0.3px;
            color: #ffffff;
            line-height: 1.1;
        }

        .nx-brand-text span {
            display: block;
            font-size: 11px;
            font-weight: 500;
            color: var(--nx-sand-400);
            letter-spacing: 0.08em;
            text-transform: uppercase;
        }

        .nx-nav-menu {
            display: flex;
            align-items: center;
            gap: 6px;
            list-style: none;
        }

        .nx-nav-link {
            padding: 8px 16px;
            color: var(--nx-sand-400);
            text-decoration: none;
            font-size: 14px;
            font-weight: 500;
            border-radius: var(--nx-radius-pill);
            transition: all var(--nx-duration-fast) var(--nx-ease-standard);
        }

        .nx-nav-link:hover {
            color: #ffffff;
            background: rgba(237, 230, 217, 0.08);
        }

        .nx-header-actions {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .nx-lang-pill {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 7px 14px;
            border-radius: var(--nx-radius-pill);
            background: rgba(237, 230, 217, 0.08);
            border: 1px solid rgba(237, 230, 217, 0.15);
            color: var(--nx-sand-300);
            text-decoration: none;
            font-size: 12px;
            font-weight: 600;
            transition: all var(--nx-duration-fast) ease;
        }

        .nx-lang-pill:hover {
            background: rgba(237, 230, 217, 0.16);
            color: #ffffff;
        }

        /* ── Mobile Navigation Toggle ── */
        .nx-mobile-toggle {
            display: none;
            background: rgba(237, 230, 217, 0.08);
            border: 1px solid rgba(237, 230, 217, 0.15);
            color: #ffffff;
            font-size: 20px;
            padding: 6px 12px;
            border-radius: var(--nx-radius-md);
            cursor: pointer;
        }

        /* ── Master Footer ── */
        .nx-footer {
            background: var(--nx-palm-950);
            color: var(--nx-sand-400);
            border-top: 1px solid rgba(237, 230, 217, 0.12);
            padding: 80px 32px 36px;
            position: relative;
        }

        .nx-footer-grid {
            max-width: var(--nx-container-max);
            margin: 0 auto;
            display: grid;
            grid-template-columns: 2fr 1fr 1fr 1fr;
            gap: 48px;
            margin-bottom: 60px;
        }

        .nx-footer-col h4 {
            font-size: 14px;
            font-weight: 600;
            color: #ffffff;
            margin-bottom: 20px;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .nx-footer-links {
            list-style: none;
            display: flex;
            flex-direction: column;
            gap: 12px;
        }

        .nx-footer-links a {
            color: var(--nx-sand-400);
            text-decoration: none;
            font-size: 13px;
            transition: color var(--nx-duration-fast);
        }

        .nx-footer-links a:hover {
            color: #ffffff;
        }

        .nx-footer-bottom {
            max-width: var(--nx-container-max);
            margin: 0 auto;
            padding-top: 24px;
            border-top: 1px solid rgba(237, 230, 217, 0.08);
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 16px;
            font-size: 12px;
        }

        /* ── Responsive ── */
        @media (max-width: 1024px) {
            .nx-nav-menu { display: none; }
            .nx-mobile-toggle { display: block; }
            .nx-footer-grid { grid-template-columns: 1fr 1fr; }
        }

        @media (max-width: 640px) {
            .nx-header { padding: 12px 18px; }
            .nx-footer-grid { grid-template-columns: 1fr; }
        }
    </style>
    @yield('styles')
</head>
<body>

    <!-- Dark Marketing Header -->
    <header class="nx-header">
        <div class="nx-header-container">
            <a href="{{ route('landing.home') }}" class="nx-brand-lockup">
                <div class="nx-brand-arch">
                    <span class="material-symbols-rounded text-white text-[20px]">apartment</span>
                </div>
                <div class="nx-brand-text">
                    <h1>UlaSpace</h1>
                    <span>{{ __('Virtual Workplace') }}</span>
                </div>
            </a>

            <!-- Navigation Links -->
            @php
                $navItems = \App\Domains\CMS\Models\CmsThemeSetting::getByKey('main_navigation', [
                    ['label_en' => 'Spaces', 'label_ar' => 'المساحات الذكية', 'url' => '#spaces'],
                    ['label_en' => 'Features', 'label_ar' => 'المميزات', 'url' => '#features'],
                    ['label_en' => 'Meetings', 'label_ar' => 'الاجتماعات', 'url' => '#meetings'],
                    ['label_en' => 'Pricing', 'label_ar' => 'الباقات والأسعار', 'url' => '#pricing'],
                ]);
            @endphp
            <ul class="nx-nav-menu">
                @foreach($navItems as $item)
                    <li>
                        <a href="{{ $item['url'] }}" class="nx-nav-link">
                            {{ app()->getLocale() === 'ar' ? ($item['label_ar'] ?? $item['label_en']) : ($item['label_en'] ?? $item['label_ar']) }}
                        </a>
                    </li>
                @endforeach
            </ul>

            <!-- Header Actions -->
            <div class="nx-header-actions">
                <a href="{{ route('lang.switch', app()->getLocale() === 'ar' ? 'en' : 'ar') }}" class="nx-lang-pill" title="{{ __('Switch Language') }}">
                    <span class="material-symbols-rounded text-[16px]">language</span>
                    <span>{{ app()->getLocale() === 'ar' ? 'English' : 'العربية' }}</span>
                </a>

                @auth
                    <x-btn href="{{ route('dashboard') }}" variant="nav-cta" size="sm">
                        <span>{{ __('Go to Workspace') }}</span>
                    </x-btn>
                @else
                    <a href="{{ route('login') }}" class="nx-nav-link text-white">
                        <span>{{ __('Log In') }}</span>
                    </a>
                    <x-btn href="{{ route('register') }}" variant="nav-cta" size="sm">
                        <span>{{ __('Book a Demo (احجز عرضاً)') }}</span>
                    </x-btn>
                @endauth

                <button type="button" class="nx-mobile-toggle" onclick="toggleMobileMenu()" aria-label="Toggle Menu">
                    <span class="material-symbols-rounded">menu</span>
                </button>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main style="padding-top: 68px;">
        @yield('content')
    </main>

    <!-- Master Footer -->
    <footer class="nx-footer">
        <div class="nx-footer-grid">
            <div class="nx-footer-col">
                <div class="nx-brand-lockup" style="margin-bottom: 16px;">
                    <div class="nx-brand-arch">
                        <span class="material-symbols-rounded text-white text-[20px]">apartment</span>
                    </div>
                    <div class="nx-brand-text">
                        <h2 style="font-size: 20px; font-weight: 600; color: white;">UlaSpace</h2>
                        <span>Virtual Workplace</span>
                    </div>
                </div>
                <p style="font-size: 13px; color: var(--nx-sand-400); max-width: 320px; line-height: 1.7; margin-bottom: 20px;">
                    {{ __("Your team's space, anywhere. The next-generation spatial virtual office where distributed teams meet, collaborate, and connect naturally.") }}
                </p>
            </div>

            <div class="nx-footer-col">
                <h4>{{ __('Product (المنتج)') }}</h4>
                <ul class="nx-footer-links">
                    <li><a href="#spaces">{{ __('Spatial 2D/3D Office') }}</a></li>
                    <li><a href="#meetings">{{ __('Proximity Audio & Video') }}</a></li>
                    <li><a href="#features">{{ __('Productivity & Analytics') }}</a></li>
                    <li><a href="#pricing">{{ __('Pricing & Plans') }}</a></li>
                </ul>
            </div>

            <div class="nx-footer-col">
                <h4>{{ __('Company (الشركة)') }}</h4>
                <ul class="nx-footer-links">
                    <li><a href="{{ route('register') }}">{{ __('Get Started Free') }}</a></li>
                    <li><a href="{{ route('login') }}">{{ __('Sign In to Workplace') }}</a></li>
                    <li><a href="mailto:contact@ulaspace.com">{{ __('Contact Sales') }}</a></li>
                </ul>
            </div>

            <div class="nx-footer-col">
                <h4>{{ __('Legal & Privacy') }}</h4>
                <ul class="nx-footer-links">
                    <li><a href="#">{{ __('Privacy Policy') }}</a></li>
                    <li><a href="#">{{ __('Terms of Service') }}</a></li>
                    <li><a href="#">{{ __('Security & Data Protection') }}</a></li>
                </ul>
            </div>
        </div>

        <div class="nx-footer-bottom">
            <div>
                © {{ date('Y') }} UlaSpace Inc. {{ __('All rights reserved.') }}
            </div>
            <div>
                <span>🌿 {{ __('Crafted for Next-Gen Remote Teams') }}</span>
            </div>
        </div>
    </footer>

    <script nonce="{{ $cspNonce ?? '' }}">
        function toggleMobileMenu() {
            const menu = document.querySelector('.nx-nav-menu');
            if (menu) {
                menu.style.display = menu.style.display === 'flex' ? 'none' : 'flex';
                menu.style.flexDirection = 'column';
                menu.style.position = 'absolute';
                menu.style.top = '64px';
                menu.style.left = '16px';
                menu.style.right = '16px';
                menu.style.background = 'rgba(20, 43, 36, 0.98)';
                menu.style.padding = '20px';
                menu.style.borderRadius = '16px';
                menu.style.border = '1px solid rgba(237, 230, 217, 0.15)';
            }
        }
    </script>
    @yield('scripts')
</body>
</html>
