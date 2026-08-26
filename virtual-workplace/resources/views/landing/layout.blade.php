<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'NextSpace — Spatial Virtual Workplace & Meetings')</title>
    <meta name="description" content="@yield('meta_description', 'Experience the future of remote work. An immersive 3D spatial virtual office platform with proximity audio/video, AI blueprint generation, meetings, and project tools.')">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- Open Graph & Social Cards -->
    <meta property="og:title" content="@yield('title', 'NextSpace — Spatial Virtual Workplace')">
    <meta property="og:description" content="@yield('meta_description', 'Your team has a place to meet, work, collaborate, and connect — even when remote.')">
    <meta property="og:type" content="website">
    <meta property="og:image" content="{{ asset('images/office_floorplan.jpg') }}">

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;800;900&family=Inter:wght@300;400;500;600;700;800;900&family=Outfit:wght@400;600;700;800;900&display=swap" rel="stylesheet">

    <!-- Three.js for 3D Spatial Canvas -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/three.js/r128/three.min.js"></script>

    <!-- Dynamic Theme Variables Injected from Super Admin -->
    <style>
        {!! $dynamicCssVariables ?? '' !!}

        *, *::before, *::after {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        html {
            scroll-behavior: smooth;
        }

        body {
            font-family: {{ app()->getLocale() === 'ar' ? 'var(--ns-font-arabic)' : 'var(--ns-font-latin)' }};
            background-color: var(--ns-deep-space, #071A16);
            color: var(--ns-text-light, #F4FBF7);
            min-height: 100vh;
            overflow-x: hidden;
            line-height: 1.6;
            -webkit-font-smoothing: antialiased;
        }

        /* ── Glassmorphic Navigation Header ── */
        .ns-header {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 1000;
            padding: 16px 32px;
            transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            background: rgba(7, 26, 22, 0.75);
            border-bottom: 1px solid rgba(111, 231, 194, 0.12);
        }

        .ns-header-container {
            max-width: 1400px;
            margin: 0 auto;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 20px;
        }

        .ns-brand {
            display: flex;
            align-items: center;
            gap: 12px;
            text-decoration: none;
            color: var(--ns-white, #ffffff);
        }

        .ns-brand-icon {
            width: 42px;
            height: 42px;
            border-radius: 12px;
            background: var(--ns-gradient-emerald);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 22px;
            box-shadow: 0 4px 20px rgba(19, 168, 121, 0.4);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .ns-brand-text h1 {
            font-size: 20px;
            font-weight: 900;
            letter-spacing: -0.5px;
            background: linear-gradient(135deg, #FFFFFF 0%, #6FE7C2 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .ns-brand-text span {
            display: block;
            font-size: 10px;
            font-weight: 800;
            color: var(--ns-mint, #6FE7C2);
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-top: -3px;
        }

        .ns-nav-menu {
            display: flex;
            align-items: center;
            gap: 8px;
            list-style: none;
        }

        .ns-nav-link {
            padding: 8px 16px;
            color: var(--ns-text-muted, #8BA69C);
            text-decoration: none;
            font-size: 14px;
            font-weight: 700;
            border-radius: 10px;
            transition: all 0.2s ease;
        }

        .ns-nav-link:hover {
            color: var(--ns-white, #ffffff);
            background: rgba(111, 231, 194, 0.08);
        }

        .ns-header-actions {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .ns-lang-btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 8px 14px;
            border-radius: 10px;
            background: rgba(11, 41, 34, 0.6);
            border: 1px solid rgba(111, 231, 194, 0.2);
            color: var(--ns-mint, #6FE7C2);
            text-decoration: none;
            font-size: 12px;
            font-weight: 800;
            transition: all 0.2s ease;
        }

        .ns-lang-btn:hover {
            background: rgba(19, 168, 121, 0.15);
            border-color: var(--ns-mint);
            transform: translateY(-1px);
        }

        .ns-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 11px 22px;
            border-radius: var(--ns-radius-btn, 12px);
            font-size: 13px;
            font-weight: 800;
            text-decoration: none;
            cursor: pointer;
            border: none;
            transition: all 0.25s cubic-bezier(0.16, 1, 0.3, 1);
            white-space: nowrap;
        }

        .ns-btn-primary {
            background: var(--ns-gradient-emerald);
            color: #071A16;
            box-shadow: 0 4px 18px rgba(19, 168, 121, 0.35);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .ns-btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 26px rgba(19, 168, 121, 0.5);
        }

        .ns-btn-secondary {
            background: rgba(11, 41, 34, 0.8);
            color: var(--ns-white, #ffffff);
            border: 1px solid rgba(111, 231, 194, 0.25);
            box-shadow: 0 4px 14px rgba(0, 0, 0, 0.2);
        }

        .ns-btn-secondary:hover {
            background: rgba(19, 168, 121, 0.15);
            border-color: var(--ns-mint);
            transform: translateY(-2px);
        }

        .ns-btn-outline {
            background: transparent;
            color: var(--ns-white, #ffffff);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .ns-btn-outline:hover {
            background: rgba(255, 255, 255, 0.08);
            border-color: rgba(255, 255, 255, 0.4);
        }

        /* ── Mobile Navigation Toggle ── */
        .ns-mobile-toggle {
            display: none;
            background: rgba(11, 41, 34, 0.8);
            border: 1px solid rgba(111, 231, 194, 0.2);
            color: var(--ns-white);
            font-size: 20px;
            padding: 8px 12px;
            border-radius: 10px;
            cursor: pointer;
        }

        /* ── Common Section Styles ── */
        .ns-section {
            padding: 100px 32px;
            position: relative;
        }

        .ns-container {
            max-width: 1300px;
            margin: 0 auto;
            position: relative;
            z-index: 2;
        }

        .ns-section-header {
            text-align: center;
            max-width: 780px;
            margin: 0 auto 60px;
        }

        .ns-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 6px 14px;
            border-radius: 9999px;
            background: rgba(19, 168, 121, 0.12);
            border: 1px solid rgba(111, 231, 194, 0.3);
            color: var(--ns-mint, #6FE7C2);
            font-size: 12px;
            font-weight: 800;
            margin-bottom: 16px;
            box-shadow: 0 0 16px rgba(19, 168, 121, 0.15);
        }

        .ns-section-title {
            font-size: clamp(28px, 4vw, 44px);
            font-weight: 900;
            line-height: 1.25;
            margin-bottom: 16px;
            letter-spacing: -0.5px;
            background: linear-gradient(135deg, #FFFFFF 30%, #DDF8EF 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .ns-section-subtitle {
            font-size: clamp(15px, 1.8vw, 17px);
            color: var(--ns-text-muted, #8BA69C);
            line-height: 1.7;
        }

        /* ── Glass Cards ── */
        .ns-card {
            background: var(--ns-glass-bg, rgba(11, 41, 34, 0.72));
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid var(--ns-glass-border, rgba(111, 231, 194, 0.15));
            border-radius: var(--ns-radius-card, 20px);
            padding: 32px;
            box-shadow: var(--ns-shadow-card);
            transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
            position: relative;
            overflow: hidden;
        }

        .ns-card:hover {
            transform: translateY(-4px);
            border-color: rgba(111, 231, 194, 0.35);
            box-shadow: 0 20px 48px rgba(0, 0, 0, 0.55), 0 0 30px rgba(19, 168, 121, 0.2);
        }

        /* ── Footer ── */
        .ns-footer {
            background: #040D0B;
            border-top: 1px solid rgba(111, 231, 194, 0.12);
            padding: 80px 32px 36px;
            position: relative;
        }

        .ns-footer-grid {
            max-width: 1300px;
            margin: 0 auto;
            display: grid;
            grid-template-columns: 2fr 1fr 1fr 1fr;
            gap: 48px;
            margin-bottom: 60px;
        }

        .ns-footer-col h4 {
            font-size: 14px;
            font-weight: 800;
            color: var(--ns-white);
            margin-bottom: 20px;
            text-transform: uppercase;
            letter-spacing: 0.8px;
        }

        .ns-footer-links {
            list-style: none;
            display: flex;
            flex-direction: column;
            gap: 12px;
        }

        .ns-footer-links a {
            color: var(--ns-text-muted);
            text-decoration: none;
            font-size: 13px;
            font-weight: 600;
            transition: color 0.2s;
        }

        .ns-footer-links a:hover {
            color: var(--ns-mint);
        }

        .ns-footer-bottom {
            max-width: 1300px;
            margin: 0 auto;
            padding-top: 24px;
            border-top: 1px solid rgba(255, 255, 255, 0.08);
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 16px;
            font-size: 12px;
            color: var(--ns-text-muted);
        }

        /* ── Responsive ── */
        @media (max-width: 1024px) {
            .ns-nav-menu { display: none; }
            .ns-mobile-toggle { display: block; }
            .ns-footer-grid { grid-template-columns: 1fr 1fr; }
        }

        @media (max-width: 640px) {
            .ns-header { padding: 12px 18px; }
            .ns-section { padding: 60px 18px; }
            .ns-footer-grid { grid-template-columns: 1fr; }
        }

        @media (prefers-reduced-motion: reduce) {
            *, *::before, *::after {
                animation-duration: 0.01ms !important;
                animation-iteration-count: 1 !important;
                transition-duration: 0.01ms !important;
                scroll-behavior: auto !important;
            }
        }
    </style>
    @yield('styles')
</head>
<body>

    <!-- Glassmorphic Header Navigation -->
    <header class="ns-header">
        <div class="ns-header-container">
            <a href="{{ route('landing.home') }}" class="ns-brand">
                <div class="ns-brand-icon">⚡</div>
                <div class="ns-brand-text">
                    <h1>NEXT SPACE</h1>
                    <span>Virtual Workplace</span>
                </div>
            </a>

            <!-- Desktop Navigation Links -->
            <ul class="ns-nav-menu">
                <li><a href="#hero-spatial" class="ns-nav-link">{{ __('Platform (المنصة)') }}</a></li>
                <li><a href="#spatial-presence" class="ns-nav-link">{{ __('Spatial Presence (التواجد المكاني)') }}</a></li>
                <li><a href="#ai-generator" class="ns-nav-link">{{ __('AI Office (الذكاء الاصطناعي)') }}</a></li>
                <li><a href="#collaboration" class="ns-nav-link">{{ __('Collaboration (التعاون)') }}</a></li>
                <li><a href="#pricing" class="ns-nav-link">{{ __('Pricing (الباقات)') }}</a></li>
            </ul>

            <!-- Header Actions & Auth -->
            <div class="ns-header-actions">
                <a href="{{ route('lang.switch', app()->getLocale() === 'ar' ? 'en' : 'ar') }}" class="ns-lang-btn" title="{{ __('Switch Language') }}">
                    <span>🌐</span>
                    <span>{{ app()->getLocale() === 'ar' ? 'English' : 'العربية' }}</span>
                </a>

                @auth
                    <a href="{{ route('dashboard') }}" class="ns-btn ns-btn-primary">
                        <span>🏢</span>
                        <span>{{ __('Go to Workspace') }}</span>
                    </a>
                @else
                    <a href="{{ route('login') }}" class="ns-btn ns-btn-secondary">
                        <span>{{ __('Log In') }}</span>
                    </a>
                    <a href="{{ route('register') }}" class="ns-btn ns-btn-primary">
                        <span>{{ __('Start Free') }}</span>
                    </a>
                @endauth

                <button type="button" class="ns-mobile-toggle" onclick="toggleMobileMenu()" aria-label="Toggle Menu">
                    ☰
                </button>
            </div>
        </div>
    </header>

    <!-- Main Content Yield -->
    <main style="padding-top: 76px;">
        @yield('content')
    </main>

    <!-- Master Footer -->
    <footer class="ns-footer">
        <div class="ns-footer-grid">
            <div class="ns-footer-col">
                <div class="ns-brand" style="margin-bottom: 16px;">
                    <div class="ns-brand-icon">⚡</div>
                    <div class="ns-brand-text">
                        <h2 style="font-size: 20px; font-weight: 900; color: white;">NEXT SPACE</h2>
                        <span>Virtual Workplace</span>
                    </div>
                </div>
                <p style="font-size: 13px; color: var(--ns-text-muted); max-width: 320px; line-height: 1.7; margin-bottom: 20px;">
                    {{ __("Your team's space, anywhere. The next-generation spatial virtual office where distributed teams meet, collaborate, and connect naturally.") }}
                </p>
                <div style="display: flex; gap: 10px;">
                    <a href="https://twitter.com" target="_blank" class="ns-lang-btn" style="padding: 8px 12px;">𝕏</a>
                    <a href="https://linkedin.com" target="_blank" class="ns-lang-btn" style="padding: 8px 12px;">in</a>
                    <a href="https://github.com" target="_blank" class="ns-lang-btn" style="padding: 8px 12px;">GitHub</a>
                </div>
            </div>

            <div class="ns-footer-col">
                <h4>{{ __('Product (المنتج)') }}</h4>
                <ul class="ns-footer-links">
                    <li><a href="#hero-spatial">{{ __('Spatial 2D Office') }}</a></li>
                    <li><a href="#spatial-presence">{{ __('Proximity Audio & Video') }}</a></li>
                    <li><a href="#ai-generator">{{ __('AI Blueprint Studio') }}</a></li>
                    <li><a href="#collaboration">{{ __('Productivity Suite') }}</a></li>
                    <li><a href="#pricing">{{ __('Pricing & Plans') }}</a></li>
                </ul>
            </div>

            <div class="ns-footer-col">
                <h4>{{ __('Company (الشركة)') }}</h4>
                <ul class="ns-footer-links">
                    <li><a href="/register">{{ __('Get Started Free') }}</a></li>
                    <li><a href="/login">{{ __('Sign In to Workplace') }}</a></li>
                    <li><a href="#faq">{{ __('Frequently Asked Questions') }}</a></li>
                    <li><a href="mailto:support@nextspace.com">{{ __('Contact Support') }}</a></li>
                </ul>
            </div>

            <div class="ns-footer-col">
                <h4>{{ __('Legal & Security (الأمان)') }}</h4>
                <ul class="ns-footer-links">
                    <li><a href="#">{{ __('Privacy Policy') }}</a></li>
                    <li><a href="#">{{ __('Terms of Service') }}</a></li>
                    <li><a href="#">{{ __('Security & Data Protection') }}</a></li>
                    <li><a href="#">{{ __('WebRTC & Encryption') }}</a></li>
                </ul>
            </div>
        </div>

        <div class="ns-footer-bottom">
            <div>
                © {{ date('Y') }} NextSpace Inc. {{ __('All rights reserved.') }}
            </div>
            <div style="display: flex; gap: 16px;">
                <span>⚡ {{ __('Crafted for Next-Gen Remote Teams') }}</span>
            </div>
        </div>
    </footer>

    <script>
        function toggleMobileMenu() {
            const menu = document.querySelector('.ns-nav-menu');
            if (menu) {
                menu.style.display = menu.style.display === 'flex' ? 'none' : 'flex';
                menu.style.flexDirection = 'column';
                menu.style.position = 'absolute';
                menu.style.top = '70px';
                menu.style.left = '16px';
                menu.style.right = '16px';
                menu.style.background = 'rgba(7, 26, 22, 0.95)';
                menu.style.padding = '20px';
                menu.style.borderRadius = '16px';
                menu.style.border = '1px solid rgba(111, 231, 194, 0.2)';
            }
        }
    </script>
    @yield('scripts')
</body>
</html>
