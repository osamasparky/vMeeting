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
           3D SPATIAL WORKSPACE + SOFT NEUMORPHIC DESIGN SYSTEM (SUPER ADMIN)
           ═══════════════════════════════════════════════════════════════ */
        :root {
            /* Light Theme (Warm Ivory & Forest Green Baseline) */
            --bg-base: #F5F3E8;
            --bg-surface: #FFFDF6;
            --bg-surface-subtle: #E8EFE2;
            --bg-card: #FFFDF6;
            --bg-input: #E8EFE2;
            --bg-elevated: #FFFDF6;
            --border-color: #D5DED0;
            --border-color-glow: rgba(36, 92, 58, 0.25);

            --text-primary: #192D21;
            --text-secondary: #405546;
            --text-muted: #718778;
            --text-dim: #98ADA0;

            --brand-primary: #245C3A;
            --brand-forest: #245C3A;
            --brand-sage: #3F7D4F;
            --brand-emerald: #4F9B5F;
            --brand-teal: #245C3A;
            --brand-pine: #1E4E31;
            --brand-navy: #192D21;
            --brand-green: #4F9B5F;

            --status-success: #4F9B5F;
            --status-warning: #D6A23A;
            --status-danger: #D96B5F;
            --status-info: #245C3A;

            --shadow-card: 0 10px 28px rgba(36, 92, 58, 0.06), 0 2px 6px rgba(36, 92, 58, 0.04);
            --shadow-hover: 0 16px 36px rgba(36, 92, 58, 0.12), 0 4px 10px rgba(36, 92, 58, 0.06);
            --shadow-tactile-btn: 0 4px 0 #1B452B, 0 8px 18px rgba(36, 92, 58, 0.25);
            --shadow-tactile-secondary: 0 3px 0 #CBD6C4, 0 6px 14px rgba(36, 92, 58, 0.06);
            --shadow-soft-3d: 0 4px 10px rgba(36, 92, 58, 0.07);
            --shadow-inset-3d: inset 0 2px 4px rgba(36, 92, 58, 0.06);

            --accent-gradient: linear-gradient(135deg, #245C3A 0%, #3F7D4F 100%);
            --radius-sm: 8px;
            --radius-md: 12px;
            --radius-lg: 16px;
            --radius-xl: 20px;

            --font-family: 'Cairo', 'Inter', sans-serif;
        }

        [data-theme="dark"] {
            --bg-base: #07100C;
            --bg-surface: #0B1510;
            --bg-surface-subtle: #101C15;
            --bg-card: #0B1510;
            --bg-input: #101C15;
            --bg-elevated: #15251B;
            --border-color: #26382B;
            --border-color-glow: rgba(79, 155, 95, 0.35);

            --text-primary: #F1F5EF;
            --text-secondary: #C2D1C5;
            --text-muted: #9AA99D;
            --text-dim: #647568;

            --brand-primary: #4F9B5F;
            --brand-forest: #7BC47F;
            --brand-sage: #4F9B5F;
            --brand-emerald: #7BC47F;
            --brand-teal: #4F9B5F;
            --brand-pine: #3F7D4F;
            --brand-navy: #F1F5EF;
            --brand-green: #7BC47F;

            --status-success: #7BC47F;
            --status-warning: #E2B348;
            --status-danger: #E47A6E;
            --status-info: #7BC47F;

            --shadow-card: 0 10px 30px rgba(0, 0, 0, 0.45), 0 2px 6px rgba(0, 0, 0, 0.3);
            --shadow-hover: 0 16px 40px rgba(0, 0, 0, 0.6), 0 4px 12px rgba(79, 155, 95, 0.15);
            --shadow-tactile-btn: 0 4px 0 #183821, 0 8px 20px rgba(0, 0, 0, 0.5);
            --shadow-tactile-secondary: 0 3px 0 #07100C, 0 6px 14px rgba(0, 0, 0, 0.4);
            --shadow-soft-3d: 0 4px 12px rgba(0, 0, 0, 0.4);
            --shadow-inset-3d: inset 0 2px 5px rgba(0, 0, 0, 0.5);

            --accent-gradient: linear-gradient(135deg, #2D6A42 0%, #4F9B5F 100%);
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
            box-shadow: var(--shadow-card);
            transition: width 0.28s cubic-bezier(0.16, 1, 0.3, 1), transform 0.28s cubic-bezier(0.16, 1, 0.3, 1);
            overflow-x: hidden;
        }

        /* ── Mini / Icon-Only Collapsed Super Admin Sidebar ── */
        .admin-sidebar.sidebar-collapsed {
            width: 76px !important;
            align-items: center;
        }

        .admin-sidebar.sidebar-collapsed .admin-brand-text,
        .admin-sidebar.sidebar-collapsed .nav-category-title,
        .admin-sidebar.sidebar-collapsed .nav-item span:last-child,
        .admin-sidebar.sidebar-collapsed .admin-sidebar-footer > span:first-child {
            display: none !important;
        }

        .admin-sidebar.sidebar-collapsed .admin-brand {
            padding: 16px 8px !important;
            justify-content: center !important;
            gap: 0 !important;
            width: 100%;
        }

        .admin-sidebar.sidebar-collapsed .admin-nav {
            padding: 14px 6px !important;
            width: 100%;
        }

        .admin-sidebar.sidebar-collapsed .nav-item {
            padding: 10px 0 !important;
            justify-content: center !important;
            width: 100% !important;
            border-radius: 12px;
            position: relative;
        }

        .admin-sidebar.sidebar-collapsed .nav-item:hover::after {
            content: attr(data-tooltip);
            position: absolute;
            inset-inline-start: calc(100% + 12px);
            top: 50%;
            transform: translateY(-50%);
            background: #192D21;
            color: #FFFDF6;
            padding: 6px 12px;
            border-radius: 8px;
            font-size: 11px;
            font-weight: 800;
            white-space: nowrap;
            z-index: 100;
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.25);
            pointer-events: none;
            opacity: 1;
        }

        .admin-sidebar.sidebar-collapsed .admin-sidebar-footer {
            justify-content: center !important;
            padding: 14px 0 !important;
            width: 100%;
        }

        .admin-brand {
            padding: 22px 20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            border-bottom: 1px solid var(--border-color);
            background: var(--bg-surface);
        }
        .admin-brand-icon {
            width: 44px;
            height: 44px;
            background: var(--accent-gradient);
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 22px;
            color: white;
            box-shadow: var(--shadow-tactile-btn);
            flex-shrink: 0;
        }
        .admin-brand-text h2 {
            font-size: 15px;
            font-weight: 900;
            color: var(--text-primary);
            line-height: 1.2;
        }
        .admin-brand-badge {
            display: inline-block;
            font-size: 10px;
            font-weight: 800;
            padding: 3px 8px;
            border-radius: 20px;
            background: rgba(79, 155, 95, 0.15);
            color: #4F9B5F;
            border: 1px solid rgba(79, 155, 95, 0.3);
            text-transform: uppercase;
            margin-top: 3px;
        }

        .admin-nav {
            flex: 1;
            padding: 18px 14px;
            display: flex;
            flex-direction: column;
            gap: 6px;
            overflow-y: auto;
        }
        .nav-category-title {
            font-size: 10px;
            font-weight: 900;
            text-transform: uppercase;
            color: var(--text-muted);
            letter-spacing: 0.8px;
            margin: 16px 10px 6px;
        }
        .nav-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 11px 14px;
            border-radius: var(--radius-md);
            color: var(--text-secondary);
            text-decoration: none;
            font-size: 13px;
            font-weight: 800;
            transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
            border: 1px solid transparent;
        }
        .nav-item:hover {
            color: var(--text-primary);
            background: var(--bg-surface-subtle);
            border-color: var(--border-color);
            transform: translateX({{ app()->getLocale() === 'ar' ? '-4px' : '4px' }});
        }
        .nav-item.active {
            color: #FFFDF6;
            background: var(--accent-gradient);
            border-color: #1E4E31;
            box-shadow: var(--shadow-tactile-btn);
        }
        .nav-item-icon {
            font-size: 17px;
            width: 26px;
            height: 26px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 8px;
            background: var(--bg-surface-subtle);
            box-shadow: var(--shadow-soft-3d);
            flex-shrink: 0;
        }
        .nav-item.active .nav-item-icon {
            background: rgba(255, 255, 255, 0.2);
            color: white;
        }

        .admin-sidebar-footer {
            padding: 16px 20px;
            border-top: 1px solid var(--border-color);
            font-size: 11px;
            color: var(--text-muted);
            font-weight: 700;
            display: flex;
            align-items: center;
            justify-content: space-between;
            background: var(--bg-surface);
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
            padding: 14px 32px;
            position: sticky;
            top: 0;
            z-index: 40;
            box-shadow: var(--shadow-card);
            flex-wrap: wrap;
            gap: 12px;
        }
        .admin-header-left {
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .admin-header-left h1 {
            font-size: 20px;
            font-weight: 900;
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
            background: var(--bg-surface-subtle);
            border: 1px solid var(--border-color);
            font-size: 18px;
            padding: 8px 12px;
            border-radius: 10px;
            cursor: pointer;
            color: var(--text-primary);
        }

        .lang-switch-btn {
            background: var(--bg-surface-subtle);
            border: 1px solid var(--border-color);
            color: var(--text-primary);
            padding: 8px 14px;
            border-radius: 10px;
            font-size: 12px;
            font-weight: 800;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 6px;
            box-shadow: var(--shadow-soft-3d);
            transition: all 0.2s;
        }
        .lang-switch-btn:hover {
            transform: translateY(-1px);
            border-color: var(--brand-forest);
        }

        .theme-toggle-btn {
            background: var(--bg-surface-subtle);
            border: 1px solid var(--border-color);
            color: var(--text-primary);
            padding: 8px 12px;
            border-radius: 10px;
            font-size: 13px;
            cursor: pointer;
            box-shadow: var(--shadow-soft-3d);
            transition: all 0.2s;
        }
        .theme-toggle-btn:hover {
            transform: translateY(-1px);
        }

        .btn-return-app {
            background: var(--accent-gradient);
            color: #FFFDF6;
            padding: 9px 18px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 800;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 8px;
            border: 1px solid #1E4E31;
            box-shadow: var(--shadow-tactile-btn);
            transition: all 0.2s;
        }
        .btn-return-app:hover {
            transform: translateY(-2px);
        }

        .admin-body {
            flex: 1;
            padding: 32px;
            max-width: 1400px;
            width: 100%;
            margin: 0 auto;
        }

        /* ── Tactile Buttons & Pills ── */
        .tactile-btn, .btn-action {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-weight: 800;
            font-size: 13px;
            padding: 9px 18px;
            border-radius: 12px;
            cursor: pointer;
            text-decoration: none;
            transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
            background: var(--bg-surface-subtle);
            color: var(--text-primary);
            border: 1px solid var(--border-color);
            box-shadow: var(--shadow-tactile-secondary);
        }
        .tactile-btn:hover, .btn-action:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-hover);
        }
        .tactile-btn.btn-primary, .btn-action.btn-primary {
            background: var(--accent-gradient);
            color: #FFFDF6;
            border: 1px solid #1E4E31;
            box-shadow: var(--shadow-tactile-btn);
        }
        .tactile-btn.btn-outline, .btn-action.btn-outline {
            background: var(--bg-surface);
            color: var(--text-primary);
            border: 1px solid var(--border-color);
        }

        /* ── Cards & Grid ── */
        .kpi-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 16px;
            margin-bottom: 28px;
        }
        .kpi-card {
            background: var(--bg-surface);
            border: 1px solid var(--border-color);
            border-radius: var(--radius-xl);
            padding: 20px 22px;
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
            width: 48px;
            height: 48px;
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 22px;
            flex-shrink: 0;
            background: var(--bg-surface-subtle);
            border: 1px solid var(--border-color);
            box-shadow: var(--shadow-soft-3d);
        }
        .kpi-info h3 {
            font-size: 11px;
            font-weight: 800;
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
            background: var(--bg-surface);
            border: 1px solid var(--border-color);
            border-radius: var(--radius-xl);
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
            font-size: 17px;
            font-weight: 900;
            color: var(--text-primary);
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .panel-subtitle {
            font-size: 12px;
            color: var(--text-muted);
            margin-top: 2px;
        }

        /* ── Tables ── */
        .data-table-container {
            width: 100%;
            overflow-x: auto;
            border-radius: var(--radius-lg);
            border: 1px solid var(--border-color);
            box-shadow: var(--shadow-card);
        }
        table.data-table {
            width: 100%;
            border-collapse: collapse;
            text-align: {{ app()->getLocale() === 'ar' ? 'right' : 'left' }};
            font-size: 13px;
        }
        table.data-table th {
            background: var(--bg-surface-subtle);
            color: var(--text-secondary);
            font-weight: 900;
            padding: 14px 18px;
            border-bottom: 1px solid var(--border-color);
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 0.6px;
        }
        table.data-table td {
            padding: 14px 18px;
            border-bottom: 1px solid var(--border-color);
            color: var(--text-primary);
            background: var(--bg-surface);
        }
        table.data-table tr:hover td {
            background: var(--bg-surface-subtle);
        }

        .badge-status, .nav-badge-pill {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 4px 12px;
            border-radius: 9999px;
            font-size: 11px;
            font-weight: 800;
            background: var(--bg-surface-subtle);
            color: var(--text-secondary);
            border: 1px solid var(--border-color);
        }
        .badge-active {
            background: rgba(79, 155, 95, 0.2);
            color: #4F9B5F;
            border-color: rgba(79, 155, 95, 0.35);
        }
        .badge-suspended {
            background: rgba(217, 107, 95, 0.2);
            color: #D96B5F;
            border-color: rgba(217, 107, 95, 0.35);
        }
        .badge-plan {
            background: rgba(79, 155, 95, 0.15);
            color: var(--brand-forest);
            border-color: rgba(79, 155, 95, 0.3);
        }

        .form-input {
            background: var(--bg-surface-subtle);
            border: 1px solid var(--border-color);
            border-radius: 10px;
            padding: 10px 14px;
            color: var(--text-primary);
            outline: none;
            box-shadow: var(--shadow-inset-3d);
            font-size: 13px;
            font-weight: 600;
            transition: border-color 0.2s, box-shadow 0.2s;
        }
        .form-input:focus {
            border-color: var(--brand-forest);
            box-shadow: 0 0 0 3px rgba(79, 155, 95, 0.2);
        }

        /* ── Alerts ── */
        .alert-box {
            padding: 14px 20px;
            border-radius: var(--radius-md);
            margin-bottom: 24px;
            font-size: 13px;
            font-weight: 800;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .alert-success {
            background: rgba(79, 155, 95, 0.15);
            border: 1px solid rgba(79, 155, 95, 0.35);
            color: #4F9B5F;
        }
        .alert-error {
            background: rgba(217, 107, 95, 0.15);
            border: 1px solid rgba(217, 107, 95, 0.35);
            color: #D96B5F;
        }

        /* ── Modals ── */
        .modal-overlay {
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, 0.65);
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
            border-radius: 24px;
            width: 100%;
            max-width: 560px;
            padding: 28px;
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.4);
            color: var(--text-primary);
            animation: modalFadeIn 0.25s cubic-bezier(0.16, 1, 0.3, 1);
        }
        @keyframes modalFadeIn {
            from { opacity: 0; transform: scale(0.95); }
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
            <div style="display: flex; align-items: center; gap: 12px; min-width: 0;">
                <div class="admin-brand-icon">⚡</div>
                <div class="admin-brand-text">
                    <h2>{{ __('Super Admin Portal') }}</h2>
                    <span class="admin-brand-badge">ROOT ACCESS</span>
                </div>
            </div>
            <button onclick="toggleSuperAdminSidebarCollapse()" class="sidebar-collapse-btn" style="background: var(--bg-surface-subtle); border: 1px solid var(--border-color); width: 28px; height: 28px; border-radius: 8px; font-size: 11px; cursor: pointer; color: var(--text-secondary); display: flex; align-items: center; justify-content: center; flex-shrink: 0;" title="{{ __('Toggle Sidebar') }}">
                <span id="superadmin-sidebar-arrow">{{ app()->getLocale() === 'ar' ? '◀' : '▶' }}</span>
            </button>
        </div>

        <nav class="admin-nav">
            <div class="nav-category-title">{{ __('Overview') }}</div>
            <a href="{{ route('superadmin.dashboard') }}" class="nav-item {{ request()->routeIs('superadmin.dashboard') ? 'active' : '' }}" data-tooltip="{{ __('Dashboard') }}">
                <span class="nav-item-icon">📊</span>
                <span>{{ __('Dashboard') }}</span>
            </a>

            <div class="nav-category-title">{{ __('SaaS Management') }}</div>
            <a href="{{ route('superadmin.companies') }}" class="nav-item {{ request()->routeIs('superadmin.companies') ? 'active' : '' }}" data-tooltip="{{ __('Companies') }}">
                <span class="nav-item-icon">🏢</span>
                <span>{{ __('Companies') }}</span>
            </a>
            <a href="{{ route('superadmin.plans') }}" class="nav-item {{ request()->routeIs('superadmin.plans') ? 'active' : '' }}" data-tooltip="{{ __('Subscription Plans') }}">
                <span class="nav-item-icon">💎</span>
                <span>{{ __('Subscription Plans') }}</span>
            </a>
            @php
                $sidebarPendingSubs = \App\Domains\Tenancy\Models\SubscriptionRequest::where('status', 'pending')->count();
            @endphp
            <a href="{{ route('superadmin.subscriptions') }}" class="nav-item {{ request()->routeIs('superadmin.subscriptions*') ? 'active' : '' }}" data-tooltip="{{ __('Subscription Requests') }}" style="display: flex; justify-content: space-between; align-items: center;">
                <div style="display: flex; align-items: center; gap: 12px; min-width: 0;">
                    <span class="nav-item-icon">💳</span>
                    <span>{{ __('Subscription Requests') }}</span>
                </div>
                @if($sidebarPendingSubs > 0)
                    <span style="background: #D6A23A; color: white; font-size: 10px; font-weight: 900; padding: 2px 7px; border-radius: 9999px;">{{ $sidebarPendingSubs }}</span>
                @endif
            </a>
            <a href="{{ route('superadmin.furniture') }}" class="nav-item {{ request()->routeIs('superadmin.furniture*') ? 'active' : '' }}" data-tooltip="{{ __('Furniture & Assets') }}">
                <span class="nav-item-icon">🛋️</span>
                <span>{{ __('Furniture & Assets') }}</span>
            </a>

            <div class="nav-category-title">{{ __('Access & Security') }}</div>
            <a href="{{ route('superadmin.matrix') }}" class="nav-item {{ request()->routeIs('superadmin.matrix') ? 'active' : '' }}" data-tooltip="{{ __('Permission Matrix') }}">
                <span class="nav-item-icon">🔐</span>
                <span>{{ __('Permission Matrix') }}</span>
            </a>
            <a href="{{ route('superadmin.settings') }}" class="nav-item {{ request()->routeIs('superadmin.settings') ? 'active' : '' }}" data-tooltip="{{ __('System Settings') }}">
                <span class="nav-item-icon">⚙️</span>
                <span>{{ __('System Settings') }}</span>
            </a>

            <div class="nav-category-title">{{ __('Session') }}</div>
            <form method="POST" action="{{ route('logout') }}" style="margin: 0; padding: 0;">
                @csrf
                <button type="submit" class="nav-item" style="width: 100%; border: none; background: none; text-align: start; cursor: pointer; color: #D96B5F;" data-tooltip="{{ __('Logout') }}">
                    <span class="nav-item-icon">🚪</span>
                    <span>{{ __('Logout') }}</span>
                </button>
            </form>
        </nav>

        <div class="admin-sidebar-footer">
            <span>vMeeting SaaS 2.0</span>
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
                <button onclick="toggleSuperAdminSidebarCollapse()" class="theme-toggle-btn" style="display: inline-flex; align-items: center; justify-content: center;" title="{{ __('Toggle Sidebar') }}">
                    <span id="header-collapse-icon">◀</span>
                </button>
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

                <!-- User Profile Capsule -->
                <div style="display: flex; align-items: center; gap: 8px; padding: 4px 10px; background: var(--bg-surface-subtle); border: 1px solid var(--border-color); border-radius: 9999px; box-shadow: var(--shadow-soft-3d);">
                    @if(auth()->user()?->avatar_url)
                        <img src="{{ auth()->user()->avatar_url }}" alt="{{ auth()->user()->name }}" style="width: 28px; height: 28px; border-radius: 50%; object-fit: cover;">
                    @else
                        <div style="width: 28px; height: 28px; border-radius: 50%; background: var(--accent-gradient); color: white; display: flex; align-items: center; justify-content: center; font-size: 11px; font-weight: 900;">
                            {{ strtoupper(substr(auth()->user()?->name ?? 'SA', 0, 2)) }}
                        </div>
                    @endif
                    <span style="font-size: 12px; font-weight: 800; color: var(--text-primary);">{{ explode(' ', auth()->user()?->name ?? 'Admin')[0] }}</span>
                </div>

                <!-- Super Admin Logout Header Button -->
                <form method="POST" action="{{ route('logout') }}" style="display: inline; margin: 0;">
                    @csrf
                    <button type="submit" class="tactile-btn" style="background: rgba(217, 107, 95, 0.15); color: #D96B5F; border: 1px solid rgba(217, 107, 95, 0.35); padding: 7px 14px; font-size: 12px; font-weight: 800; cursor: pointer; border-radius: 12px; display: inline-flex; align-items: center; gap: 6px;" title="{{ __('Logout') }}">
                        <span>🚪</span>
                        <span>{{ __('Logout') }}</span>
                    </button>
                </form>
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

        function toggleSuperAdminSidebarCollapse() {
            const sidebar = document.getElementById('adminSidebar');
            const isRtl = document.documentElement.dir === 'rtl' || '{{ app()->getLocale() }}' === 'ar';
            if (sidebar) sidebar.classList.toggle('sidebar-collapsed');
            const isCollapsed = sidebar && sidebar.classList.contains('sidebar-collapsed');
            localStorage.setItem('vw_superadmin_sidebar_collapsed', isCollapsed ? '1' : '0');
            updateSidebarArrow(isCollapsed, isRtl);
        }

        function updateSidebarArrow(isCollapsed, isRtl) {
            const arrowEl = document.getElementById('superadmin-sidebar-arrow');
            const headerIcon = document.getElementById('header-collapse-icon');
            const arrow = isCollapsed ? (isRtl ? '◀' : '▶') : (isRtl ? '▶' : '◀');
            if (arrowEl) arrowEl.textContent = arrow;
            if (headerIcon) headerIcon.textContent = arrow;
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

        // Initialize theme & sidebar state on page load
        (function() {
            const saved = localStorage.getItem('vw_theme') || 'dark';
            document.documentElement.setAttribute('data-theme', saved);
            updateThemeIcons(saved);

            const isCollapsed = localStorage.getItem('vw_superadmin_sidebar_collapsed') === '1';
            const isRtl = document.documentElement.dir === 'rtl' || '{{ app()->getLocale() }}' === 'ar';
            if (isCollapsed) {
                const sidebar = document.getElementById('adminSidebar');
                if (sidebar) sidebar.classList.add('sidebar-collapsed');
                updateSidebarArrow(true, isRtl);
            }
        })();
    </script>
    @yield('scripts')
</body>
</html>
