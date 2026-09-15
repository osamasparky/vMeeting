<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('Virtual Workplace') }} — UlaSpace</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Mono:wght@400;500;600;700&family=IBM+Plex+Sans+Arabic:wght@300;400;500;600;700;800&family=IBM+Plex+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;1,400&family=Material+Symbols+Rounded:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/ulaspace-tokens.css') }}">
    <link rel="stylesheet" href="{{ asset('css/modern-design-system.css') }}">

    <style>
        :root {
            --bg-page: var(--nx-bg-page, #0B1410);
            --bg-surface: var(--nx-bg-surface, #142B24);
            --bg-card: #142B24;
            --text-primary: var(--nx-sand-100, #F9F4EE);
            --text-secondary: var(--nx-sand-400, #E3D2BB);
            --text-muted: var(--nx-text-muted, #A4B5AD);
            --border-panel: rgba(237, 230, 217, 0.12);
            --brand-primary: var(--nx-palm-300, #4EA66F);
            --brand-accent: var(--nx-sand-300, #EADCC9);
            --font-ar: 'IBM Plex Sans Arabic', -apple-system, BlinkMacSystemFont, sans-serif;
            --font-en: 'IBM Plex Sans', -apple-system, BlinkMacSystemFont, sans-serif;
            --font-mono: 'IBM Plex Mono', monospace;
            --font-family: var(--font-en);
        }

        [dir="rtl"], [lang="ar"] {
            --font-family: var(--font-ar);
        }

        * { margin: 0; padding: 0; box-sizing: border-box; font-family: var(--font-family); }
        body {
            background: var(--bg-page);
            color: var(--text-primary);
            min-height: 100vh;
            overflow-x: hidden;
            display: flex;
            flex-direction: column;
            -webkit-font-smoothing: antialiased;
        }

        .navbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 20px 32px;
            max-width: 1400px;
            margin: 0 auto;
            width: 100%;
        }

        .brand {
            display: flex;
            align-items: center;
            gap: 12px;
            text-decoration: none;
            color: inherit;
        }

        .brand-logo {
            width: 42px;
            height: 42px;
            background: var(--bg-surface);
            border: 1px solid var(--border-panel);
            border-radius: var(--nx-radius-md, 12px);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 6px;
            box-shadow: var(--nx-shadow-sm);
        }

        .brand-name {
            font-size: 20px;
            font-weight: 800;
            color: var(--text-primary);
            letter-spacing: -0.2px;
        }

        .nav-links {
            display: flex;
            align-items: center;
            gap: 16px;
        }

        .btn-link {
            color: var(--text-secondary);
            text-decoration: none;
            font-size: 14px;
            font-weight: 600;
            padding: 8px 16px;
            border-radius: var(--nx-radius-md, 12px);
            transition: all 0.2s ease;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }
        .btn-link:hover {
            color: var(--text-primary);
            background: rgba(255, 255, 255, 0.06);
        }

        .hero {
            max-width: 1000px;
            margin: 60px auto 40px;
            padding: 0 24px;
            text-align: center;
        }

        .badge-pill {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 6px 16px;
            background: rgba(78, 166, 111, 0.12);
            border: 1px solid rgba(78, 166, 111, 0.25);
            border-radius: 9999px;
            font-size: 13px;
            font-weight: 700;
            color: var(--brand-primary);
            margin-bottom: 24px;
        }

        .hero-title {
            font-size: clamp(32px, 5vw, 54px);
            font-weight: 900;
            line-height: 1.15;
            margin-bottom: 20px;
            letter-spacing: -0.5px;
        }

        .gradient-text {
            background: linear-gradient(135deg, #4EA66F 0%, #D3A553 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .hero-desc {
            font-size: 18px;
            line-height: 1.7;
            color: var(--text-secondary);
            max-width: 720px;
            margin: 0 auto 36px;
            font-weight: 400;
        }

        .hero-actions {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 16px;
            flex-wrap: wrap;
        }

        .features-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 20px;
            max-width: 1200px;
            margin: 40px auto 80px;
            padding: 0 24px;
            width: 100%;
        }

        .feature-card {
            background: var(--bg-surface);
            border: 1px solid var(--border-panel);
            border-radius: var(--nx-radius-xl, 16px);
            padding: 28px;
            transition: transform 0.2s, border-color 0.2s;
        }
        .feature-card:hover {
            transform: translateY(-2px);
            border-color: rgba(78, 166, 111, 0.4);
        }

        .feature-icon {
            width: 48px;
            height: 48px;
            background: rgba(255, 255, 255, 0.06);
            border-radius: var(--nx-radius-md, 12px);
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--brand-primary);
            margin-bottom: 16px;
        }

        .feature-title {
            font-size: 17px;
            font-weight: 700;
            margin-bottom: 8px;
            color: var(--text-primary);
        }

        .feature-desc {
            font-size: 14px;
            line-height: 1.6;
            color: var(--text-secondary);
        }

        footer {
            margin-top: auto;
            border-top: 1px solid var(--border-panel);
            padding: 24px;
            text-align: center;
            font-size: 13px;
            color: var(--text-muted);
            font-family: var(--font-mono);
        }
    </style>
</head>
<body>

    <!-- Navigation Header -->
    <header class="navbar">
        <a href="/" class="brand">
            <div class="brand-logo">
                <img src="{{ asset('images/ulaspace-icon.png') }}" alt="UlaSpace" style="width: 26px; height: auto; object-fit: contain;">
            </div>
            <div class="brand-name">UlaSpace</div>
        </a>

        <nav class="nav-links">
            @auth
                <a href="{{ route('office') }}" class="btn-link">
                    <span class="material-symbols-rounded" style="font-size: 18px;">map</span>
                    <span>{{ __('Office Floor') }}</span>
                </a>
                <a href="{{ route('editor') }}" class="btn-link">
                    <span class="material-symbols-rounded" style="font-size: 18px;">design_services</span>
                    <span>{{ __('Designer') }}</span>
                </a>
                <a href="{{ route('dashboard') }}" class="nx-btn nx-btn--primary">
                    <span class="material-symbols-rounded" style="font-size: 18px;">dashboard</span>
                    <span>{{ __('Open Dashboard') }}</span>
                </a>
            @else
                <a href="{{ route('login') }}" class="btn-link">
                    <span class="material-symbols-rounded" style="font-size: 18px;">login</span>
                    <span>{{ __('Sign In') }}</span>
                </a>
                <a href="{{ route('register') }}" class="nx-btn nx-btn--primary">
                    <span class="material-symbols-rounded" style="font-size: 18px;">rocket_launch</span>
                    <span>{{ __('Create Workspace') }}</span>
                </a>
            @endauth
        </nav>
    </header>

    <!-- Main Hero -->
    <section class="hero">
        <div class="badge-pill">
            <span class="material-symbols-rounded" style="font-size: 16px;">auto_awesome</span>
            <span>UlaSpace Spatial Office Platform</span>
        </div>

        <h1 class="hero-title">
            {{ __('Bring your remote team together in a') }} <span class="gradient-text">{{ __('Virtual Office') }}</span>.
        </h1>

        <p class="hero-desc">
            {{ __('Step into a persistent, spatial workspace where your team connects naturally — just like a real office, but without walls.') }}
        </p>

        <div class="hero-actions">
            @auth
                <a href="{{ route('office') }}" class="nx-btn nx-btn--primary" style="padding: 14px 28px; font-size: 15px;">
                    <span class="material-symbols-rounded" style="font-size: 20px;">meeting_room</span>
                    <span>{{ __('Enter Workplace Floor') }}</span>
                </a>
                <a href="{{ route('dashboard') }}" class="btn-link" style="border: 1px solid var(--border-panel); padding: 12px 24px;">
                    <span class="material-symbols-rounded" style="font-size: 18px;">dashboard</span>
                    <span>{{ __('Workspace Dashboard') }}</span>
                </a>
            @else
                <a href="{{ route('register') }}" class="nx-btn nx-btn--primary" style="padding: 14px 28px; font-size: 15px;">
                    <span class="material-symbols-rounded" style="font-size: 20px;">add_circle</span>
                    <span>{{ __('Get Started Free') }}</span>
                </a>
                <a href="{{ route('login') }}" class="btn-link" style="border: 1px solid var(--border-panel); padding: 12px 24px;">
                    <span class="material-symbols-rounded" style="font-size: 18px;">login</span>
                    <span>{{ __('Sign In to Workplace') }}</span>
                </a>
            @endauth
        </div>
    </section>

    <!-- Features -->
    <section class="features-grid">
        <div class="feature-card">
            <div class="feature-icon">
                <span class="material-symbols-rounded" style="font-size: 24px;">spatial_audio</span>
            </div>
            <h3 class="feature-title">{{ __('Spatial Proximity Audio & Video') }}</h3>
            <p class="feature-desc">{{ __('Natural communication that mimics real life with instant WebRTC presence and spatial audio zones.') }}</p>
        </div>

        <div class="feature-card">
            <div class="feature-icon">
                <span class="material-symbols-rounded" style="font-size: 24px;">door_front</span>
            </div>
            <h3 class="feature-title">{{ __('Interactive Room Doors & Ringing') }}</h3>
            <p class="feature-desc">{{ __('Lock your private office or boardroom. Outside visitors can ring the doorbell with 1-click access.') }}</p>
        </div>

        <div class="feature-card">
            <div class="feature-icon">
                <span class="material-symbols-rounded" style="font-size: 24px;">link</span>
            </div>
            <h3 class="feature-title">{{ __('Instant 1-Click Guest Invites') }}</h3>
            <p class="feature-desc">{{ __('Invite candidates, partners, and clients with single-click links without requiring accounts.') }}</p>
        </div>

        <div class="feature-card">
            <div class="feature-icon">
                <span class="material-symbols-rounded" style="font-size: 24px;">architecture</span>
            </div>
            <h3 class="feature-title">{{ __('Visual Map & Furniture Designer') }}</h3>
            <p class="feature-desc">{{ __('Easily customize your office layout with desks, conference rooms, stages, and whiteboard partitions.') }}</p>
        </div>
    </section>

    <footer>
        © {{ date('Y') }} UlaSpace — Virtual Workplace Platform. All rights reserved.
    </footer>

</body>
</html>
