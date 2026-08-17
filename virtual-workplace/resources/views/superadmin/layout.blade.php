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
            --bg-base: #f8fafc;
            --bg-surface: #ffffff;
            --bg-card: #ffffff;
            --bg-input: #f1f5f9;
            --border-color: #e2e8f0;
            --border-color-glow: rgba(0, 180, 179, 0.4);

            --text-primary: #012c41;
            --text-secondary: #004862;
            --text-muted: #64748b;

            /* Brand Colors from color.webp */
            --brand-teal: #00b4b3;
            --brand-pine: #00726c;
            --brand-ocean: #004862;
            --brand-navy: #012c41;
            --brand-green: #006847;
            --brand-lime: #a7c545;
            --brand-gold: #ffd136;
            --brand-orange: #f57b36;
            --brand-coral: #ff3600;
            --brand-crimson: #d20005;

            --shadow-card: 0 4px 20px -2px rgba(1, 44, 65, 0.06), 0 2px 6px -1px rgba(1, 44, 65, 0.04);
            --shadow-hover: 0 12px 28px -4px rgba(1, 44, 65, 0.12), 0 4px 10px -2px rgba(1, 44, 65, 0.06);

            --font-family: {{ app()->getLocale() === 'ar' ? "'Cairo', 'Inter', sans-serif" : "'Inter', 'Cairo', sans-serif" }};
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
            box-shadow: 2px 0 12px rgba(0, 0, 0, 0.02);
            transition: transform 0.3s ease;
        }
        .admin-brand {
            padding: 24px;
            display: flex;
            align-items: center;
            gap: 12px;
            border-bottom: 1px solid var(--border-color);
            background: #ffffff;
        }
        .admin-brand-icon {
            width: 44px;
            height: 44px;
            background: linear-gradient(135deg, var(--brand-teal), var(--brand-pine));
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 22px;
            color: white;
            box-shadow: 0 4px 14px rgba(0, 180, 179, 0.35);
        }
        .admin-brand-text h2 {
            font-size: 16px;
            font-weight: 800;
            color: var(--brand-navy);
            line-height: 1.2;
        }
        .admin-brand-badge {
            display: inline-block;
            font-size: 10px;
            font-weight: 800;
            padding: 2px 8px;
            border-radius: 20px;
            background: rgba(0, 104, 71, 0.1);
            color: var(--brand-green);
            border: 1px solid rgba(0, 104, 71, 0.25);
            text-transform: uppercase;
            margin-top: 2px;
        }

        .admin-nav {
            flex: 1;
            padding: 20px 16px;
            display: flex;
            flex-direction: column;
            gap: 6px;
            overflow-y: auto;
        }
        .nav-category-title {
            font-size: 11px;
            font-weight: 800;
            text-transform: uppercase;
            color: var(--text-muted);
            letter-spacing: 0.5px;
            margin: 14px 10px 6px;
        }
        .nav-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 14px;
            border-radius: 10px;
            color: var(--text-secondary);
            text-decoration: none;
            font-size: 13px;
            font-weight: 700;
            transition: all 0.2s;
        }
        .nav-item:hover {
            color: var(--brand-navy);
            background: #f1f5f9;
        }
        .nav-item.active {
            color: white;
            background: linear-gradient(135deg, var(--brand-teal), var(--brand-pine));
            box-shadow: 0 4px 14px rgba(0, 180, 179, 0.3);
        }
        .nav-item-icon {
            font-size: 18px;
            width: 24px;
            text-align: center;
        }

        .admin-sidebar-footer {
            padding: 16px 20px;
            border-top: 1px solid var(--border-color);
            font-size: 11px;
            color: var(--text-muted);
            font-weight: 600;
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
            height: 72px;
            background: #ffffff;
            border-bottom: 1px solid var(--border-color);
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 32px;
            position: sticky;
            top: 0;
            z-index: 40;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.02);
        }
        .admin-header-left {
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .admin-header-left h1 {
            font-size: 20px;
            font-weight: 800;
            color: var(--brand-navy);
        }
        .admin-header-right {
            display: flex;
            align-items: center;
            gap: 14px;
        }

        .menu-toggle-btn {
            display: none;
            background: #f1f5f9;
            border: 1px solid var(--border-color);
            font-size: 20px;
            padding: 8px 12px;
            border-radius: 8px;
            cursor: pointer;
            color: var(--brand-navy);
        }

        .lang-switch-btn {
            background: #f8fafc;
            border: 1px solid var(--border-color);
            color: var(--brand-navy);
            padding: 8px 14px;
            border-radius: 8px;
            font-size: 12px;
            font-weight: 800;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 6px;
            transition: all 0.2s;
        }
        .lang-switch-btn:hover {
            background: #ffffff;
            border-color: var(--brand-teal);
            box-shadow: 0 2px 8px rgba(0, 180, 179, 0.15);
        }

        .btn-return-app {
            background: linear-gradient(135deg, var(--brand-green), #004d34);
            color: white;
            padding: 9px 18px;
            border-radius: 8px;
            font-size: 12px;
            font-weight: 800;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 6px;
            box-shadow: 0 4px 12px rgba(0, 104, 71, 0.25);
            transition: all 0.2s;
        }
        .btn-return-app:hover {
            transform: translateY(-1px);
            box-shadow: 0 6px 16px rgba(0, 104, 71, 0.35);
        }

        .admin-body {
            flex: 1;
            padding: 32px;
            max-width: 1400px;
            width: 100%;
            margin: 0 auto;
        }

        /* ── Cards & Grid ── */
        .kpi-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 20px;
            margin-bottom: 32px;
        }
        .kpi-card {
            background: var(--bg-card);
            border: 1px solid var(--border-color);
            border-radius: 16px;
            padding: 22px;
            display: flex;
            align-items: center;
            gap: 18px;
            box-shadow: var(--shadow-card);
            transition: transform 0.2s, box-shadow 0.2s;
        }
        .kpi-card:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-hover);
        }
        .kpi-icon {
            width: 54px;
            height: 54px;
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 26px;
            flex-shrink: 0;
        }
        .kpi-info h3 {
            font-size: 12px;
            font-weight: 700;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 4px;
        }
        .kpi-info .kpi-value {
            font-size: 26px;
            font-weight: 900;
            color: var(--brand-navy);
        }

        .panel-card {
            background: var(--bg-card);
            border: 1px solid var(--border-color);
            border-radius: 16px;
            padding: 24px;
            margin-bottom: 32px;
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
            color: var(--brand-navy);
            display: flex;
            align-items: center;
            gap: 10px;
        }

        /* ── Tables ── */
        .data-table-container {
            width: 100%;
            overflow-x: auto;
            border-radius: 10px;
        }
        table.data-table {
            width: 100%;
            border-collapse: collapse;
            text-align: {{ app()->getLocale() === 'ar' ? 'right' : 'left' }};
            font-size: 13px;
        }
        table.data-table th {
            background: #f8fafc;
            color: var(--text-secondary);
            font-weight: 800;
            padding: 14px 16px;
            border-bottom: 1px solid var(--border-color);
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        table.data-table td {
            padding: 14px 16px;
            border-bottom: 1px solid var(--border-color);
            color: var(--text-primary);
            background: #ffffff;
        }
        table.data-table tr:hover td {
            background: #f8fafc;
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
            background: rgba(0, 104, 71, 0.1);
            color: var(--brand-green);
            border: 1px solid rgba(0, 104, 71, 0.25);
        }
        .badge-suspended {
            background: rgba(210, 0, 5, 0.1);
            color: var(--brand-crimson);
            border: 1px solid rgba(210, 0, 5, 0.25);
        }
        .badge-plan {
            background: rgba(0, 180, 179, 0.12);
            color: #00726c;
            border: 1px solid rgba(0, 180, 179, 0.3);
        }

        .btn-action {
            background: #f8fafc;
            border: 1px solid var(--border-color);
            color: var(--brand-navy);
            padding: 7px 14px;
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
            background: #ffffff;
            border-color: var(--brand-teal);
            box-shadow: 0 2px 8px rgba(0, 180, 179, 0.15);
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
            background: rgba(0, 104, 71, 0.1);
            border: 1px solid rgba(0, 104, 71, 0.3);
            color: var(--brand-green);
        }
        .alert-error {
            background: rgba(210, 0, 5, 0.1);
            border: 1px solid rgba(210, 0, 5, 0.3);
            color: var(--brand-crimson);
        }

        /* ── Modals ── */
        .modal-overlay {
            position: fixed;
            inset: 0;
            background: rgba(1, 44, 65, 0.5);
            backdrop-filter: blur(8px);
            display: none;
            align-items: center;
            justify-content: center;
            z-index: 100;
            padding: 20px;
        }
        .modal-card {
            background: #ffffff;
            border: 1px solid var(--border-color);
            border-radius: 18px;
            width: 100%;
            max-width: 540px;
            padding: 28px;
            box-shadow: 0 20px 50px rgba(1, 44, 65, 0.25);
            animation: modalFadeIn 0.2s ease-out;
        }
        @keyframes modalFadeIn {
            from { opacity: 0; transform: scale(0.96); }
            to { opacity: 1; transform: scale(1); }
        }

        /* ── Responsive Mobile ── */
        @media (max-width: 900px) {
            .admin-sidebar {
                position: fixed;
                left: 0;
                top: 0;
                transform: translateX({{ app()->getLocale() === 'ar' ? '100%' : '-100%' }});
            }
            .admin-sidebar.open {
                transform: translateX(0);
            }
            .menu-toggle-btn {
                display: block;
            }
            .admin-header {
                padding: 0 16px;
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
            <div style="font-size: 11px; color: var(--text-muted);">
                vMeeting SaaS Engine 2.0
            </div>
        </div>
    </aside>

    <!-- Main Content -->
    <div class="admin-main">
        <header class="admin-header">
            <div class="admin-header-left">
                <button class="menu-toggle-btn" onclick="toggleSidebar()">☰</button>
                <h1>@yield('page_title', __('Dashboard'))</h1>
            </div>
            <div class="admin-header-right">
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
    </script>
    @yield('scripts')
</body>
</html>
