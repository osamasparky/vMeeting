<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', __('Super Admin Portal')) — Virtual Workplace</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;800;900&family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        /* ═══════════════════════════════════════════════════════════════
           SAUDI IDENTITY & PREMIUM WHITE DESIGN SYSTEM
           Palette: #00b4b3, #00726c, #004862, #012c41, #006847, #a7c545, #ffd136, #f57b36, #ff3600
           ═══════════════════════════════════════════════════════════════ */
        :root {
            --bg-base: #0b0f19;
            --bg-surface: #111827;
            --bg-card: #111827;
            --bg-input: #1e293b;
            --border-color: rgba(255, 255, 255, 0.10);
            --border-color-glow: rgba(59, 130, 246, 0.4);

            --text-primary: #f8fafc;
            --text-secondary: #cbd5e1;
            --text-muted: #94a3b8;

            --brand-primary: #3b82f6;
            --brand-secondary: #8b5cf6;
            --brand-teal: #06b6d4;
            --brand-pine: #0d9488;
            --brand-ocean: #0284c7;
            --brand-navy: #f8fafc;
            --brand-green: #10b981;
            --brand-lime: #84cc16;
            --brand-gold: #f59e0b;
            --brand-orange: #f97316;
            --brand-coral: #fb7185;
            --brand-crimson: #ef4444;

            --shadow-card: 0 4px 20px -2px rgba(0, 0, 0, 0.35);
            --shadow-hover: 0 12px 28px -4px rgba(59, 130, 246, 0.25);

            --font-family: {{ app()->getLocale() === 'ar' ? "'IBM Plex Sans Arabic', 'Cairo', 'Plus Jakarta Sans', sans-serif" : "'Plus Jakarta Sans', 'Inter', 'IBM Plex Sans Arabic', sans-serif" }};
        }

        [data-theme="light"] {
            --bg-base: #f8fafc;
            --bg-surface: #ffffff;
            --bg-card: #ffffff;
            --bg-input: #f1f5f9;
            --border-color: #e2e8f0;
            --border-color-glow: rgba(59, 130, 246, 0.3);
            --brand-navy: #0f172a;
            --text-primary: #0f172a;
            --text-secondary: #334155;
            --text-muted: #64748b;
            --shadow-card: 0 4px 20px -2px rgba(15, 23, 42, 0.06);
            --shadow-hover: 0 12px 28px -4px rgba(59, 130, 246, 0.15);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: var(--font-family);
        }

        body {
            background-color: var(--bg-base);
            color: var(--text-primary);
            min-height: 100vh;
            display: flex;
            overflow-x: hidden;
        }

        /* ── Sidebar ── */
        .admin-sidebar {
            width: 280px;
            background: var(--bg-surface);
            border-inline-end: 1px solid var(--border-color);
            display: flex;
            flex-direction: column;
            flex-shrink: 0;
            height: 100vh;
            position: sticky;
            top: 0;
            z-index: 50;
            box-shadow: 2px 0 12px rgba(0, 0, 0, 0.2);
            transition: transform 0.3s ease;
        }
        .admin-brand {
            padding: 20px 24px;
            display: flex;
            align-items: center;
            gap: 12px;
            border-bottom: 1px solid var(--border-color);
            background: var(--bg-surface);
        }
        .admin-brand-icon {
            width: 42px;
            height: 42px;
            background: linear-gradient(135deg, #3b82f6, #8b5cf6);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            color: white;
            box-shadow: 0 4px 14px rgba(59, 130, 246, 0.35);
        }
        .admin-brand-text h2 {
            font-size: 15px;
            font-weight: 800;
            color: var(--text-primary);
            line-height: 1.2;
        }
        .admin-brand-badge {
            display: inline-block;
            font-size: 10px;
            font-weight: 800;
            padding: 2px 8px;
            border-radius: 20px;
            background: rgba(16, 185, 129, 0.15);
            color: #34d399;
            border: 1px solid rgba(16, 185, 129, 0.3);
            text-transform: uppercase;
            margin-top: 2px;
        }

        .admin-nav {
            flex: 1;
            padding: 16px 14px;
            display: flex;
            flex-direction: column;
            gap: 4px;
            overflow-y: auto;
        }
        .nav-category-title {
            font-size: 10px;
            font-weight: 800;
            text-transform: uppercase;
            color: var(--text-muted);
            letter-spacing: 0.8px;
            margin: 14px 10px 6px;
        }
        .nav-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 10px 12px;
            border-radius: 10px;
            color: var(--text-secondary);
            text-decoration: none;
            font-size: 13px;
            font-weight: 700;
            transition: all 0.2s;
        }
        .nav-item:hover {
            color: var(--text-primary);
            background: var(--bg-input);
        }
        .nav-item.active {
            color: white;
            background: linear-gradient(135deg, #3b82f6, #8b5cf6);
            box-shadow: 0 4px 14px rgba(59, 130, 246, 0.35);
        }
        .nav-item-icon {
            font-size: 17px;
            width: 24px;
            text-align: center;
        }

        .admin-sidebar-footer {
            padding: 14px 20px;
            border-top: 1px solid var(--border-color);
            font-size: 11px;
            color: var(--text-muted);
            font-weight: 600;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        /* ── Main Content Area ── */
        .admin-main {
            flex: 1;
            display: flex;
            flex-direction: column;
            min-width: 0;
            background-color: var(--bg-base);
        }
        .admin-header {
            min-height: 70px;
            background: var(--bg-surface);
            border-bottom: 1px solid var(--border-color);
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 12px 28px;
            position: sticky;
            top: 0;
            z-index: 40;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.15);
            flex-wrap: wrap;
            gap: 12px;
        }
        .admin-header-left {
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .admin-header-left h1 {
            font-size: 19px;
            font-weight: 800;
            color: var(--text-primary);
        }
        .admin-header-right {
            display: flex;
            align-items: center;
            gap: 10px;
            flex-wrap: wrap;
        }

        .menu-toggle-btn {
            display: none;
            background: var(--bg-input);
            border: 1px solid var(--border-color);
            font-size: 18px;
            padding: 8px 12px;
            border-radius: 8px;
            cursor: pointer;
            color: var(--text-primary);
        }

        .lang-switch-btn {
            background: var(--bg-input);
            border: 1px solid var(--border-color);
            color: var(--text-primary);
            padding: 8px 14px;
            border-radius: 8px;
            font-size: 12px;
            font-weight: 700;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 6px;
            transition: all 0.2s;
        }
        .lang-switch-btn:hover {
            background: var(--bg-card);
            border-color: var(--brand-primary);
        }

        .theme-toggle-btn {
            background: var(--bg-input);
            border: 1px solid var(--border-color);
            color: var(--text-primary);
            padding: 8px 12px;
            border-radius: 8px;
            font-size: 13px;
            cursor: pointer;
            transition: all 0.2s;
        }

        .btn-return-app {
            background: linear-gradient(135deg, #10b981, #059669);
            color: white;
            padding: 9px 16px;
            border-radius: 8px;
            font-size: 12px;
            font-weight: 800;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 6px;
            box-shadow: 0 4px 12px rgba(16, 185, 129, 0.25);
            transition: all 0.2s;
        }
        .btn-return-app:hover {
            transform: translateY(-1px);
            box-shadow: 0 6px 16px rgba(16, 185, 129, 0.35);
        }

        .admin-body {
            flex: 1;
            padding: 28px;
            max-width: 1400px;
            width: 100%;
            margin: 0 auto;
        }

        /* ── Cards & Grid ── */
        .kpi-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 16px;
            margin-bottom: 28px;
        }
        .kpi-card {
            background: var(--bg-card);
            border: 1px solid var(--border-color);
            border-radius: 16px;
            padding: 20px;
            display: flex;
            align-items: center;
            gap: 16px;
            box-shadow: var(--shadow-card);
            transition: transform 0.2s, box-shadow 0.2s;
        }
        .kpi-card:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-hover);
        }
        .kpi-icon {
            width: 50px;
            height: 50px;
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            flex-shrink: 0;
        }
        .kpi-info h3 {
            font-size: 11px;
            font-weight: 700;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 4px;
        }
        .kpi-info .kpi-value {
            font-size: 24px;
            font-weight: 900;
            color: var(--text-primary);
        }

        .panel-card {
            background: var(--bg-card);
            border: 1px solid var(--border-color);
            border-radius: 16px;
            padding: 24px;
            margin-bottom: 28px;
            box-shadow: var(--shadow-card);
        }
        .panel-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 20px;
            padding-bottom: 14px;
            border-bottom: 1px solid var(--border-color);
            flex-wrap: wrap;
            gap: 12px;
        }
        .panel-title {
            font-size: 16px;
            font-weight: 800;
            color: var(--text-primary);
            display: flex;
            align-items: center;
            gap: 10px;
        }

        /* ── Tables ── */
        .data-table-container {
            width: 100%;
            overflow-x: auto;
            border-radius: 10px;
            border: 1px solid var(--border-color);
        }
        table.data-table {
            width: 100%;
            border-collapse: collapse;
            text-align: {{ app()->getLocale() === 'ar' ? 'right' : 'left' }};
            font-size: 13px;
        }
        table.data-table th {
            background: var(--bg-input);
            color: var(--text-secondary);
            font-weight: 800;
            padding: 12px 16px;
            border-bottom: 1px solid var(--border-color);
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        table.data-table td {
            padding: 14px 16px;
            border-bottom: 1px solid var(--border-color);
            color: var(--text-primary);
            background: var(--bg-card);
        }
        table.data-table tr:hover td {
            background: var(--bg-input);
        }

        .badge-status {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 800;
        }
        .badge-active {
            background: rgba(16, 185, 129, 0.15);
            color: #34d399;
            border: 1px solid rgba(16, 185, 129, 0.3);
        }
        .badge-suspended {
            background: rgba(239, 68, 68, 0.15);
            color: #f87171;
            border: 1px solid rgba(239, 68, 68, 0.3);
        }
        .badge-plan {
            background: rgba(59, 130, 246, 0.15);
            color: #60a5fa;
            border: 1px solid rgba(59, 130, 246, 0.3);
        }

        .btn-action {
            background: var(--bg-input);
            border: 1px solid var(--border-color);
            color: var(--text-primary);
            padding: 6px 12px;
            border-radius: 8px;
            font-size: 12px;
            font-weight: 700;
            cursor: pointer;
            text-decoration: none;
            transition: all 0.2s;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }
        .btn-action:hover {
            background: var(--bg-card);
            border-color: var(--brand-primary);
        }

        .form-input {
            background: var(--bg-input);
            border: 1px solid var(--border-color);
            color: var(--text-primary);
            outline: none;
            transition: border-color 0.2s, box-shadow 0.2s;
        }
        .form-input:focus {
            border-color: var(--brand-primary);
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.2);
        }

        /* ── Alerts ── */
        .alert-box {
            padding: 14px 20px;
            border-radius: 12px;
            margin-bottom: 24px;
            font-size: 13px;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .alert-success {
            background: rgba(16, 185, 129, 0.15);
            border: 1px solid rgba(16, 185, 129, 0.3);
            color: #34d399;
        }
        .alert-error {
            background: rgba(239, 68, 68, 0.15);
            border: 1px solid rgba(239, 68, 68, 0.3);
            color: #f87171;
        }

        /* ── Modals ── */
        .modal-overlay {
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, 0.7);
            backdrop-filter: blur(8px);
            display: none;
            align-items: center;
            justify-content: center;
            z-index: 100;
            padding: 20px;
        }
        .modal-card {
            background: var(--bg-surface);
            border: 1px solid var(--border-color);
            border-radius: 18px;
            width: 100%;
            max-width: 540px;
            padding: 28px;
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.5);
            color: var(--text-primary);
            animation: modalFadeIn 0.2s ease-out;
        }
        @keyframes modalFadeIn {
            from { opacity: 0; transform: scale(0.96); }
            to { opacity: 1; transform: scale(1); }
        }

        .sidebar-backdrop {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, 0.6);
            z-index: 45;
        }

        /* ── Responsive Mobile ── */
        @media (max-width: 900px) {
            .admin-sidebar {
                position: fixed;
                inset-inline-start: 0;
                top: 0;
                transform: translateX({{ app()->getLocale() === 'ar' ? '100%' : '-100%' }});
                z-index: 50;
            }
            .admin-sidebar.open {
                transform: translateX(0);
            }
            .admin-sidebar.open + .sidebar-backdrop {
                display: block;
            }
            .menu-toggle-btn {
                display: block;
            }
            .admin-header {
                padding: 12px 16px;
            }
            .admin-body {
                padding: 16px;
            }
        }
    </style>
    @yield('styles')
</head>
<body>
    <!-- Sidebar -->
    <aside class="admin-sidebar" id="adminSidebar">
        <div class="admin-brand">
            <div class="admin-brand-icon">⚡</div>
            <div class="admin-brand-text">
                <h2>{{ __('Super Admin Portal') }}</h2>
                <span class="admin-brand-badge">ROOT ACCESS</span>
            </div>
        </div>

        <nav class="admin-nav">
            <div class="nav-category-title">{{ __('Overview') }}</div>
            <a href="{{ route('superadmin.dashboard') }}" class="nav-item {{ request()->routeIs('superadmin.dashboard') ? 'active' : '' }}">
                <span class="nav-item-icon">📊</span>
                <span>{{ __('Dashboard') }}</span>
            </a>

            <div class="nav-category-title">{{ __('SaaS Management') }}</div>
            <a href="{{ route('superadmin.companies') }}" class="nav-item {{ request()->routeIs('superadmin.companies') ? 'active' : '' }}">
                <span class="nav-item-icon">🏢</span>
                <span>{{ __('Companies') }}</span>
            </a>
            <a href="{{ route('superadmin.plans') }}" class="nav-item {{ request()->routeIs('superadmin.plans') ? 'active' : '' }}">
                <span class="nav-item-icon">💎</span>
                <span>{{ __('Subscription Plans') }}</span>
            </a>
            <a href="{{ route('superadmin.furniture') }}" class="nav-item {{ request()->routeIs('superadmin.furniture*') ? 'active' : '' }}">
                <span class="nav-item-icon">🛋️</span>
                <span>{{ __('Furniture & Assets') }}</span>
            </a>

            <div class="nav-category-title">{{ __('Access & Security') }}</div>
            <a href="{{ route('superadmin.matrix') }}" class="nav-item {{ request()->routeIs('superadmin.matrix') ? 'active' : '' }}">
                <span class="nav-item-icon">🔐</span>
                <span>{{ __('Permission Matrix') }}</span>
            </a>
            <a href="{{ route('superadmin.settings') }}" class="nav-item {{ request()->routeIs('superadmin.settings') ? 'active' : '' }}">
                <span class="nav-item-icon">⚙️</span>
                <span>{{ __('System Settings') }}</span>
            </a>
        </nav>

        <div class="admin-sidebar-footer">
            <span>vMeeting SaaS Engine 2.0</span>
            <button onclick="toggleSuperAdminTheme()" class="theme-toggle-btn" style="padding: 4px 8px; font-size: 11px;">
                <span id="superadmin-theme-icon">🌙</span>
            </button>
        </div>
    </aside>
    <div class="sidebar-backdrop" id="sidebarBackdrop" onclick="toggleSidebar()"></div>

    <!-- Main Content -->
    <div class="admin-main">
        <header class="admin-header">
            <div class="admin-header-left">
                <button class="menu-toggle-btn" onclick="toggleSidebar()">☰</button>
                <h1>@yield('page_title', __('Dashboard'))</h1>
            </div>
            <div class="admin-header-right">
                <!-- Theme Toggle Button in Header -->
                <button onclick="toggleSuperAdminTheme()" class="theme-toggle-btn">
                    <span id="header-theme-icon">🌙</span>
                </button>

                <!-- Language Switcher -->
                @if(app()->getLocale() === 'ar')
                    <a href="{{ route('lang.switch', 'en') }}" class="lang-switch-btn">🌐 English</a>
                @else
                    <a href="{{ route('lang.switch', 'ar') }}" class="lang-switch-btn">🌐 العربية</a>
                @endif

                <a href="{{ route('office') }}" class="btn-return-app">
                    <span>🏢</span>
                    <span>{{ __('Return to Workplace') }}</span>
                </a>
            </div>
        </header>

        <main class="admin-body">
            @if(session('success'))
                <div class="alert-box alert-success">
                    <span>✅</span>
                    <span>{{ session('success') }}</span>
                </div>
            @endif

            @if(session('error'))
                <div class="alert-box alert-error">
                    <span>⚠️</span>
                    <span>{{ session('error') }}</span>
                </div>
            @endif

            @yield('content')
        </main>
    </div>

    <script>
        function toggleSidebar() {
            document.getElementById('adminSidebar').classList.toggle('open');
        }

        function toggleSuperAdminTheme() {
            const current = document.documentElement.getAttribute('data-theme') || 'dark';
            const next = current === 'dark' ? 'light' : 'dark';
            document.documentElement.setAttribute('data-theme', next);
            localStorage.setItem('vw_theme', next);
            updateThemeIcons(next);
        }

        function updateThemeIcons(theme) {
            const icon = theme === 'dark' ? '🌙' : '☀️';
            const el1 = document.getElementById('superadmin-theme-icon');
            const el2 = document.getElementById('header-theme-icon');
            if (el1) el1.textContent = icon;
            if (el2) el2.textContent = icon;
        }

        // Initialize theme on page load
        (function() {
            const saved = localStorage.getItem('vw_theme') || 'dark';
            document.documentElement.setAttribute('data-theme', saved);
            updateThemeIcons(saved);
        })();
    </script>
    @yield('scripts')
</body>
</html>
