<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Virtual Workplace — Next-Gen Spatial Office & Collaboration</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;800;900&family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg-dark: #070913;
            --bg-secondary: #0f172a;
            --accent-gradient: linear-gradient(135deg, #6366f1, #8b5cf6, #ec4899);
            --accent-green: #10b981;
            --text-main: #f8fafc;
            --text-muted: #94a3b8;
            --border-panel: rgba(255, 255, 255, 0.08);
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Cairo', 'Inter', sans-serif;
            background: var(--bg-dark);
            color: var(--text-main);
            min-height: 100vh;
            overflow-x: hidden;
            display: flex;
            flex-direction: column;
        }

        /* ── Header Navigation ── */
        .navbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 20px 48px;
            max-width: 1400px;
            margin: 0 auto;
            width: 100%;
            border-bottom: 1px solid var(--border-panel);
        }

        .brand {
            display: flex;
            align-items: center;
            gap: 12px;
            text-decoration: none;
            color: white;
        }

        .brand-logo {
            width: 40px;
            height: 40px;
            border-radius: 10px;
            background: var(--accent-gradient);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            font-weight: 800;
            box-shadow: 0 4px 15px rgba(99, 102, 241, 0.4);
        }

        .brand-name {
            font-size: 18px;
            font-weight: 800;
            letter-spacing: -0.5px;
        }

        .nav-links {
            display: flex;
            align-items: center;
            gap: 16px;
        }

        .btn-link {
            color: var(--text-muted);
            text-decoration: none;
            font-weight: 600;
            font-size: 14px;
            padding: 8px 16px;
            border-radius: 8px;
            transition: all 0.2s;
        }

        .btn-link:hover {
            color: white;
            background: rgba(255, 255, 255, 0.05);
        }

        .btn-primary {
            background: var(--accent-gradient);
            color: white;
            text-decoration: none;
            font-weight: 700;
            font-size: 14px;
            padding: 10px 22px;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(99, 102, 241, 0.35);
            transition: transform 0.2s, box-shadow 0.2s;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(99, 102, 241, 0.5);
        }

        .btn-green {
            background: var(--accent-green);
            color: black;
            text-decoration: none;
            font-weight: 800;
            font-size: 14px;
            padding: 10px 22px;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(16, 185, 129, 0.35);
            transition: transform 0.2s;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .btn-green:hover {
            transform: translateY(-2px);
        }

        /* ── Hero Section ── */
        .hero {
            flex: 1;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-align: center;
            padding: 80px 24px 60px;
            max-width: 1000px;
            margin: 0 auto;
            position: relative;
        }

        .badge-pill {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 6px 16px;
            background: rgba(99, 102, 241, 0.12);
            border: 1px solid rgba(99, 102, 241, 0.3);
            border-radius: 30px;
            font-size: 12px;
            font-weight: 700;
            color: #a5b4fc;
            margin-bottom: 24px;
            box-shadow: 0 0 20px rgba(99, 102, 241, 0.2);
        }

        .hero-title {
            font-size: 54px;
            font-weight: 900;
            letter-spacing: -1.5px;
            line-height: 1.15;
            margin-bottom: 20px;
        }

        .gradient-text {
            background: var(--accent-gradient);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .hero-desc {
            font-size: 18px;
            color: var(--text-muted);
            line-height: 1.6;
            max-width: 680px;
            margin-bottom: 36px;
        }

        .hero-actions {
            display: flex;
            align-items: center;
            gap: 16px;
            margin-bottom: 50px;
        }

        /* ── Feature Cards ── */
        .features-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 20px;
            width: 100%;
            max-width: 1200px;
            margin: 0 auto 80px;
            padding: 0 24px;
        }

        .feature-card {
            background: rgba(15, 23, 42, 0.85);
            border: 1px solid var(--border-panel);
            border-radius: 16px;
            padding: 28px;
            text-align: left;
            backdrop-filter: blur(12px);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.4);
            transition: transform 0.2s, border-color 0.2s;
        }

        .feature-card:hover {
            transform: translateY(-4px);
            border-color: rgba(99, 102, 241, 0.3);
        }

        .feature-icon {
            font-size: 32px;
            margin-bottom: 14px;
        }

        .feature-title {
            font-size: 17px;
            font-weight: 700;
            margin-bottom: 8px;
        }

        .feature-desc {
            font-size: 13px;
            color: var(--text-muted);
            line-height: 1.5;
        }

        footer {
            border-top: 1px solid var(--border-panel);
            padding: 24px;
            text-align: center;
            font-size: 12px;
            color: var(--text-muted);
        }
    </style>
</head>
<body>

    <!-- Navigation Header -->
    <header class="navbar">
        <a href="/" class="brand">
            <div class="brand-logo">🏢</div>
            <div class="brand-name">Virtual Workplace</div>
        </a>

        <nav class="nav-links">
            @auth
                <a href="{{ route('office') }}" class="btn-link">🗺️ Office Floor</a>
                <a href="{{ route('editor') }}" class="btn-link">🎨 Designer</a>
                <a href="{{ route('dashboard') }}" class="btn-primary">📊 Open Dashboard</a>
            @else
                <a href="{{ route('login') }}" class="btn-link" style="color: white; font-size: 15px;">🔑 Sign In</a>
                <a href="{{ route('register') }}" class="btn-green">✨ Create Free Workspace</a>
            @endauth
        </nav>
    </header>

    <!-- Main Hero -->
    <section class="hero">
        <div class="badge-pill">
            <span>✨</span> Next-Gen Spatial Virtual Office Engine
        </div>

        <h1 class="hero-title">
            Bring your remote team together in a <span class="gradient-text">Virtual Office</span>.
        </h1>

        <p class="hero-desc">
            Walk up and talk with spatial voice & video, close private meeting room doors, invite external guests with 1-click links, and design your custom office floor plan in real time.
        </p>

        <div class="hero-actions">
            @auth
                <a href="{{ route('office') }}" class="btn-primary" style="font-size: 16px; padding: 14px 28px;">
                    <span>🚀</span> Enter Workplace Floor
                </a>
                <a href="{{ route('dashboard') }}" class="btn-link" style="border: 1px solid var(--border-panel); padding: 14px 24px; font-size: 15px;">
                    📊 Workspace Dashboard
                </a>
            @else
                <a href="{{ route('register') }}" class="btn-green" style="font-size: 16px; padding: 14px 28px;">
                    <span>⚡</span> Get Started Free
                </a>
                <a href="{{ route('login') }}" class="btn-primary" style="font-size: 16px; padding: 14px 28px;">
                    <span>🔑</span> Sign In to Workplace
                </a>
            @endauth
        </div>
    </section>

    <!-- Features -->
    <section class="features-grid">
        <div class="feature-card">
            <div class="feature-icon">🎙️</div>
            <h3 class="feature-title">Spatial Proximity Audio & Video</h3>
            <p class="feature-desc">Natural communication that mimics real life. Hear colleagues get louder as you walk closer, with instant P2P WebRTC camera video.</p>
        </div>

        <div class="feature-card">
            <div class="feature-icon">🚪</div>
            <h3 class="feature-title">Interactive Room Doors & Ringing</h3>
            <p class="feature-desc">Lock your private office or boardroom. Outside visitors can ring the doorbell, and occupants can allow entry with one click.</p>
        </div>

        <div class="feature-card">
            <div class="feature-icon">🔗</div>
            <h3 class="feature-title">Instant 1-Click Guest Invites</h3>
            <p class="feature-desc">Invite candidates, partners, and clients with single-click links. No registration or software installation required.</p>
        </div>

        <div class="feature-card">
            <div class="feature-icon">🎨</div>
            <h3 class="feature-title">Visual Map & Furniture Designer</h3>
            <p class="feature-desc">Easily customize your office layout with executive desks, conference rooms, stages, sofas, whiteboard partitions, and amenities.</p>
        </div>
    </section>

    <footer>
        © {{ date('Y') }} Virtual Workplace Platform. All rights reserved. Built with Realtime WebSockets & WebRTC P2P mesh technology.
    </footer>

</body>
</html>
