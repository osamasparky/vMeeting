<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', __('Super Admin Portal')) — Virtual Workplace</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Mono:wght@400;500;600;700&family=IBM+Plex+Sans+Arabic:wght@300;400;500;600;700;800&family=IBM+Plex+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;1,400&family=Material+Symbols+Rounded:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/ulaspace-tokens.css') }}">
    <link rel="stylesheet" href="{{ asset('css/modern-design-system.css') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        /* ═══════════════════════════════════════════════════════════════
           ULASPACE DESIGN SYSTEM — SUPER ADMIN PORTAL
           ═══════════════════════════════════════════════════════════════ */
        :root {
            /* Light Theme (Warm Ivory & Forest Palm Baseline) */
            /* The old blocks here also declared --bg-surface-hover,
               --border-color-glow, three retired-prefix pine/green/orange
               variables, --status-info and --radius-xs -- all unused
               (zero references in this file). Removed; only
               --ula-gradient-accent is actually used (6x), and its
               dark-mode value below deliberately differs from the
               shared ulaspace-tokens.css default for contrast, so
               it's kept as an intentional per-page override. */
            --ula-gradient-accent: linear-gradient(135deg, var(--ula-palm-900) 0%, var(--ula-palm-700) 100%);
        }

        [data-theme="dark"], html.dark, body.dark-mode {
            --ula-gradient-accent: linear-gradient(135deg, var(--ula-palm-700) 0%, var(--ula-palm-500) 100%);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: inherit;
        }

        body {
            background-color: var(--ula-surface-page);
            color: var(--ula-text-primary);
            min-height: 100vh;
            display: flex;
            overflow-x: hidden;
            letter-spacing: -0.01em;
        }

        /* ── Sidebar ── */
        .admin-sidebar {
            width: 280px;
            background: var(--ula-surface-card);
            border-inline-end: 1px solid var(--ula-border-subtle);
            display: flex;
            flex-direction: column;
            flex-shrink: 0;
            height: 100vh;
            position: sticky;
            top: 0;
            z-index: 50;
            box-shadow: var(--ula-shadow-xs);
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
            background: var(--ula-palm-900);
            color: var(--ula-accent-fg);
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
            border-bottom: 1px solid var(--ula-border-subtle);
            background: var(--ula-surface-card);
        }
        .admin-brand-icon {
            width: 44px;
            height: 44px;
            background: var(--ula-gradient-accent);
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 22px;
            color: var(--ula-white);
            box-shadow: var(--ula-shadow-sm);
            flex-shrink: 0;
        }
        .admin-brand-text h2 {
            font-size: 15px;
            font-weight: 900;
            color: var(--ula-text-primary);
            line-height: 1.2;
        }
        .admin-brand-badge {
            display: inline-block;
            font-size: 10px;
            font-weight: 800;
            padding: 3px 8px;
            border-radius: 20px;
            background: rgba(79, 155, 95, 0.15);
            color: var(--ula-status-success);
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
            color: var(--ula-text-muted);
            letter-spacing: 0.8px;
            margin: 16px 10px 6px;
        }
        .nav-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 11px 14px;
            border-radius: var(--ula-radius-sm);
            color: var(--ula-text-secondary);
            text-decoration: none;
            font-size: 13px;
            font-weight: 800;
            transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
            border: 1px solid transparent;
        }
        .nav-item:hover {
            color: var(--ula-text-primary);
            background: var(--ula-surface-page-alt);
            border-color: var(--ula-border-subtle);
            transform: translateX({{ app()->getLocale() === 'ar' ? '-4px' : '4px' }});
        }
        .nav-item.active {
            color: var(--ula-accent-fg);
            background: var(--ula-gradient-accent);
            border-color: var(--ula-palm-800);
            box-shadow: var(--ula-shadow-sm);
        }
        .nav-item-icon {
            font-size: 17px;
            width: 26px;
            height: 26px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 8px;
            background: var(--ula-surface-page-alt);
            box-shadow: var(--ula-shadow-xs);
            flex-shrink: 0;
        }
        .nav-item.active .nav-item-icon {
            background: rgba(255, 255, 255, 0.2);
            color: var(--ula-white);
        }

        .admin-sidebar-footer {
            padding: 16px 20px;
            border-top: 1px solid var(--ula-border-subtle);
            font-size: 11px;
            color: var(--ula-text-muted);
            font-weight: 700;
            display: flex;
            align-items: center;
            justify-content: space-between;
            background: var(--ula-surface-card);
        }

        /* ── Main Content Area ── */
        .admin-main {
            flex: 1;
            display: flex;
            flex-direction: column;
            min-width: 0;
            background-color: var(--ula-surface-page);
        }
        .admin-header {
            min-height: 70px;
            background: var(--ula-surface-card);
            border-bottom: 1px solid var(--ula-border-subtle);
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 14px 32px;
            position: sticky;
            top: 0;
            z-index: 40;
            box-shadow: var(--ula-shadow-xs);
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
            color: var(--ula-text-primary);
        }
        .admin-header-right {
            display: flex;
            align-items: center;
            gap: 10px;
            flex-wrap: wrap;
        }

        .menu-toggle-btn {
            display: none;
            background: var(--ula-surface-page-alt);
            border: 1px solid var(--ula-border-subtle);
            font-size: 18px;
            padding: 8px 12px;
            border-radius: 10px;
            cursor: pointer;
            color: var(--ula-text-primary);
        }

        .lang-switch-btn {
            background: var(--ula-surface-page-alt);
            border: 1px solid var(--ula-border-subtle);
            color: var(--ula-text-primary);
            padding: 8px 14px;
            border-radius: 10px;
            font-size: 12px;
            font-weight: 800;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 6px;
            box-shadow: var(--ula-shadow-xs);
            transition: all 0.2s;
        }
        .lang-switch-btn:hover {
            transform: translateY(-1px);
            border-color: var(--ula-palm-900);
        }

        .theme-toggle-btn {
            background: var(--ula-surface-page-alt);
            border: 1px solid var(--ula-border-subtle);
            color: var(--ula-text-primary);
            padding: 8px 12px;
            border-radius: 10px;
            font-size: 13px;
            cursor: pointer;
            box-shadow: var(--ula-shadow-xs);
            transition: all 0.2s;
        }
        .theme-toggle-btn:hover {
            transform: translateY(-1px);
        }

        .btn-return-app {
            background: var(--ula-gradient-accent);
            color: var(--ula-accent-fg);
            padding: 9px 18px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 800;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 8px;
            border: 1px solid var(--ula-palm-800);
            box-shadow: var(--ula-shadow-sm);
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
            background: var(--ula-surface-page-alt);
            color: var(--ula-text-primary);
            border: 1px solid var(--ula-border-subtle);
            box-shadow: var(--ula-shadow-xs);
        }
        .tactile-btn:hover, .btn-action:hover {
            transform: translateY(-2px);
            box-shadow: var(--ula-shadow-md);
        }
        .tactile-btn.btn-primary, .btn-action.btn-primary {
            background: var(--ula-gradient-accent);
            color: var(--ula-accent-fg);
            border: 1px solid var(--ula-palm-800);
            box-shadow: var(--ula-shadow-sm);
        }
        .tactile-btn.btn-outline, .btn-action.btn-outline {
            background: var(--ula-surface-card);
            color: var(--ula-text-primary);
            border: 1px solid var(--ula-border-subtle);
        }

        /* ── Cards & Grid ── */
        .kpi-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 16px;
            margin-bottom: 28px;
        }
        .kpi-card {
            background: var(--ula-surface-card);
            border: 1px solid var(--ula-border-subtle);
            border-radius: var(--ula-radius-xl);
            padding: 20px 22px;
            display: flex;
            align-items: center;
            gap: 16px;
            box-shadow: var(--ula-shadow-xs);
            transition: transform 0.2s, box-shadow 0.2s;
        }
        .kpi-card:hover {
            transform: translateY(-2px);
            box-shadow: var(--ula-shadow-md);
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
            background: var(--ula-surface-page-alt);
            border: 1px solid var(--ula-border-subtle);
            box-shadow: var(--ula-shadow-xs);
        }
        .kpi-info h3 {
            font-size: 11px;
            font-weight: 800;
            color: var(--ula-text-muted);
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 4px;
        }
        .kpi-info .kpi-value {
            font-size: 24px;
            font-weight: 900;
            color: var(--ula-text-primary);
        }

        .panel-card {
            background: var(--ula-surface-card);
            border: 1px solid var(--ula-border-subtle);
            border-radius: var(--ula-radius-xl);
            padding: 24px;
            margin-bottom: 28px;
            box-shadow: var(--ula-shadow-xs);
        }
        .panel-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 20px;
            padding-bottom: 14px;
            border-bottom: 1px solid var(--ula-border-subtle);
            flex-wrap: wrap;
            gap: 12px;
        }
        .panel-title {
            font-size: 17px;
            font-weight: 900;
            color: var(--ula-text-primary);
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .panel-subtitle {
            font-size: 12px;
            color: var(--ula-text-muted);
            margin-top: 2px;
        }

        /* ── Tables ── */
        .data-table-container {
            width: 100%;
            overflow-x: auto;
            border-radius: var(--ula-radius-lg);
            border: 1px solid var(--ula-border-subtle);
            box-shadow: var(--ula-shadow-xs);
        }
        table.data-table {
            width: 100%;
            border-collapse: collapse;
            text-align: {{ app()->getLocale() === 'ar' ? 'right' : 'left' }};
            font-size: 13px;
        }
        table.data-table th {
            background: var(--ula-surface-page-alt);
            color: var(--ula-text-secondary);
            font-weight: 900;
            padding: 14px 18px;
            border-bottom: 1px solid var(--ula-border-subtle);
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 0.6px;
        }
        table.data-table td {
            padding: 14px 18px;
            border-bottom: 1px solid var(--ula-border-subtle);
            color: var(--ula-text-primary);
            background: var(--ula-surface-card);
        }
        table.data-table tr:hover td {
            background: var(--ula-surface-page-alt);
        }

        /* ── Badges & Status Pills ── */
        .badge, .badge-status, .nav-badge-pill {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 4px 12px;
            border-radius: 9999px;
            font-size: 11px;
            font-weight: 800;
            background: var(--ula-surface-page-alt);
            color: var(--ula-text-secondary);
            border: 1px solid var(--ula-border-subtle);
        }
        .badge-active, .badge-green, .badge-live {
            background: rgba(79, 155, 95, 0.18) !important;
            color: var(--ula-palm-500) !important;
            border-color: rgba(79, 155, 95, 0.35) !important;
        }
        [data-theme="dark"] .badge-active, [data-theme="dark"] .badge-green, [data-theme="dark"] .badge-live {
            color: var(--ula-palm-300) !important;
        }
        .badge-suspended, .badge-danger, .badge-red {
            background: rgba(217, 107, 95, 0.18) !important;
            color: var(--ula-terracotta-500) !important;
            border-color: rgba(217, 107, 95, 0.35) !important;
        }
        [data-theme="dark"] .badge-suspended, [data-theme="dark"] .badge-danger, [data-theme="dark"] .badge-red {
            color: var(--ula-terracotta-300) !important;
        }
        .badge-amber, .badge-warning {
            background: rgba(211, 165, 83, 0.18) !important;
            color: var(--ula-terracotta-400) !important;
            border-color: rgba(211, 165, 83, 0.35) !important;
        }
        [data-theme="dark"] .badge-amber, [data-theme="dark"] .badge-warning {
            color: var(--ula-gold-300) !important;
        }
        .badge-plan, .badge-teal, .badge-blue {
            background: rgba(20, 43, 36, 0.12) !important;
            color: var(--ula-palm-900) !important;
            border-color: rgba(20, 43, 36, 0.25) !important;
        }
        [data-theme="dark"] .badge-plan, [data-theme="dark"] .badge-teal, [data-theme="dark"] .badge-blue {
            background: rgba(78, 166, 111, 0.2) !important;
            color: var(--ula-palm-300) !important;
            border-color: rgba(78, 166, 111, 0.35) !important;
        }

        /* ── Metric Cards & KPI Variations ── */
        .metrics-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 16px;
            margin-bottom: 24px;
        }
        .metric-card {
            background: var(--ula-surface-card);
            border: 1px solid var(--ula-border-subtle);
            border-radius: var(--ula-radius-xl);
            padding: 20px 22px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            box-shadow: var(--ula-shadow-xs);
            transition: transform 0.2s, box-shadow 0.2s;
        }
        .metric-card:hover {
            transform: translateY(-2px);
            box-shadow: var(--ula-shadow-md);
        }
        .metric-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 10px;
        }
        .metric-title, .kpi-title, .kpi-label {
            font-size: 11px;
            font-weight: 800;
            color: var(--ula-text-muted);
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .metric-icon-badge, .kpi-icon-box, .kpi-icon-wrapper {
            width: 38px;
            height: 38px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            background: var(--ula-surface-page-alt);
            border: 1px solid var(--ula-border-subtle);
        }
        .metric-value, .kpi-value {
            font-size: 24px;
            font-weight: 900;
            color: var(--ula-text-primary);
            line-height: 1.2;
            margin: 4px 0;
            font-family: var(--ula-font-mono), var(--ula-font-family);
        }
        .metric-trend, .kpi-subtext {
            font-size: 11px;
            color: var(--ula-text-muted);
            font-weight: 600;
            margin-top: 4px;
        }

        /* ── Modern Tabs Navigation ── */
        .settings-tabs-nav, .sa-tabs-nav {
            display: flex;
            gap: 8px;
            background: var(--ula-surface-card);
            padding: 8px;
            border-radius: var(--ula-radius-xl);
            border: 1px solid var(--ula-border-subtle);
            box-shadow: var(--ula-shadow-xs);
            overflow-x: auto;
            scrollbar-width: none;
            margin-bottom: 20px;
        }
        .sa-tab-btn, .tab-nav-btn {
            background: transparent;
            color: var(--ula-text-secondary);
            border: 1px solid transparent;
            border-radius: var(--ula-radius-sm);
            padding: 10px 18px;
            font-size: 13px;
            font-weight: 800;
            cursor: pointer;
            transition: all 0.2s ease;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            white-space: nowrap;
            text-decoration: none;
        }
        .sa-tab-btn:hover, .tab-nav-btn:hover {
            background: var(--ula-surface-page-alt);
            color: var(--ula-text-primary);
            border-color: var(--ula-border-subtle);
        }
        .sa-tab-btn.active, .sa-tab-btn.active-tab, .tab-nav-btn.active, .tab-nav-btn.active-tab {
            background: var(--ula-gradient-accent) !important;
            color: var(--ula-accent-fg) !important;
            border-color: var(--ula-palm-800) !important;
            box-shadow: var(--ula-shadow-sm);
        }

        .form-input, select.form-input, textarea.form-input {
            background: var(--ula-surface-page-alt);
            border: 1px solid var(--ula-border-subtle);
            border-radius: 10px;
            padding: 10px 14px;
            color: var(--ula-text-primary);
            outline: none;
            box-shadow: var(--ula-shadow-xs);
            font-size: 13px;
            font-weight: 600;
            transition: border-color 0.2s, box-shadow 0.2s;
            font-family: inherit;
        }
        .form-input:focus, select.form-input:focus, textarea.form-input:focus {
            border-color: var(--ula-palm-900);
            box-shadow: 0 0 0 3px rgba(79, 155, 95, 0.2);
        }

        /* ── Alerts ── */
        .alert-box {
            padding: 14px 20px;
            border-radius: var(--ula-radius-sm);
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
            color: var(--ula-status-success);
        }
        .alert-error {
            background: rgba(217, 107, 95, 0.15);
            border: 1px solid rgba(217, 107, 95, 0.35);
            color: var(--ula-status-danger);
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
            background: var(--ula-surface-card);
            border: 1px solid var(--ula-border-subtle);
            border-radius: 24px;
            width: 100%;
            max-width: 560px;
            padding: 28px;
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.4);
            color: var(--ula-text-primary);
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
                <div class="admin-brand-icon" style="background: var(--ula-palm-900); border-radius: 10px; width: 36px; height: 36px; display: flex; align-items: center; justify-content: center; padding: 4px; box-shadow: var(--ula-shadow-sm);">
                    <img src="{{ asset('images/ulaspace-icon.png') }}" alt="UlaSpace" style="width: 22px; height: auto; object-fit: contain;">
                </div>
                <div class="admin-brand-text">
                    <h2>UlaSpace</h2>
                    <span class="admin-brand-badge">ROOT ACCESS</span>
                </div>
            </div>
            <button onclick="toggleSuperAdminSidebarCollapse()" class="sidebar-collapse-btn" style="background: var(--ula-surface-page-alt); border: 1px solid var(--ula-border-subtle); width: 28px; height: 28px; border-radius: 8px; font-size: 16px; cursor: pointer; color: var(--ula-text-secondary); display: flex; align-items: center; justify-content: center; flex-shrink: 0;" title="{{ __('Toggle Sidebar') }}">
                <span class="material-symbols-rounded" id="superadmin-sidebar-arrow" style="font-size: 18px;">chevron_left</span>
            </button>
        </div>

        <nav class="admin-nav">
            <div class="nav-category-title">{{ __('Overview') }}</div>
            <a href="{{ route('superadmin.dashboard') }}" class="nav-item {{ request()->routeIs('superadmin.dashboard') ? 'active' : '' }}" data-tooltip="{{ __('Dashboard') }}">
                <span class="nav-item-icon"><span class="material-symbols-rounded">dashboard</span></span>
                <span>{{ __('Dashboard') }}</span>
            </a>

            <div class="nav-category-title">{{ __('SaaS Management') }}</div>
            <a href="{{ route('superadmin.companies') }}" class="nav-item {{ request()->routeIs('superadmin.companies') ? 'active' : '' }}" data-tooltip="{{ __('Companies') }}">
                <span class="nav-item-icon"><span class="material-symbols-rounded">domain</span></span>
                <span>{{ __('Companies') }}</span>
            </a>
            <a href="{{ route('superadmin.plans') }}" class="nav-item {{ request()->routeIs('superadmin.plans') ? 'active' : '' }}" data-tooltip="{{ __('Subscription Plans') }}">
                <span class="nav-item-icon"><span class="material-symbols-rounded">workspace_premium</span></span>
                <span>{{ __('Subscription Plans') }}</span>
            </a>
            <a href="{{ route('superadmin.template') }}" class="nav-item {{ request()->routeIs('superadmin.template*') ? 'active' : '' }}" data-tooltip="{{ __('Default Office Template') }}">
                <span class="nav-item-icon"><span class="material-symbols-rounded">architecture</span></span>
                <span>{{ __('Default Office Blueprint') }}</span>
            </a>
            @php
                $sidebarPendingSubs = \App\Domains\Tenancy\Models\SubscriptionRequest::where('status', 'pending')->count();
            @endphp
            <a href="{{ route('superadmin.subscriptions') }}" class="nav-item {{ request()->routeIs('superadmin.subscriptions*') ? 'active' : '' }}" data-tooltip="{{ __('Subscription Requests') }}" style="display: flex; justify-content: space-between; align-items: center;">
                <div style="display: flex; align-items: center; gap: 12px; min-width: 0;">
                    <span class="nav-item-icon"><span class="material-symbols-rounded">credit_card</span></span>
                    <span>{{ __('Subscription Requests') }}</span>
                </div>
                @if($sidebarPendingSubs > 0)
                    <span style="background: var(--ula-status-warning); color: var(--ula-white); font-size: 10px; font-weight: 900; padding: 2px 7px; border-radius: 9999px;">{{ $sidebarPendingSubs }}</span>
                @endif
            </a>
            <a href="{{ route('superadmin.furniture') }}" class="nav-item {{ request()->routeIs('superadmin.furniture*') ? 'active' : '' }}" data-tooltip="{{ __('Furniture & Assets') }}">
                <span class="nav-item-icon"><span class="material-symbols-rounded">chair</span></span>
                <span>{{ __('Furniture & Assets') }}</span>
            </a>

            <div class="nav-category-title">{{ __('Website & CMS') }}</div>
            <a href="{{ route('superadmin.cms.pages') }}" class="nav-item {{ request()->routeIs('superadmin.cms.pages*') ? 'active' : '' }}" data-tooltip="{{ __('CMS Pages & Sections') }}">
                <span class="nav-item-icon"><span class="material-symbols-rounded">web</span></span>
                <span>{{ __('Website CMS Pages') }}</span>
            </a>
            <a href="{{ route('superadmin.cms.assets') }}" class="nav-item {{ request()->routeIs('superadmin.cms.assets*') ? 'active' : '' }}" data-tooltip="{{ __('3D & Media Assets') }}">
                <span class="nav-item-icon"><span class="material-symbols-rounded">folder_open</span></span>
                <span>{{ __('3D & Media Assets') }}</span>
            </a>
            <a href="{{ route('superadmin.cms.theme') }}" class="nav-item {{ request()->routeIs('superadmin.cms.theme*') ? 'active' : '' }}" data-tooltip="{{ __('Theme & Branding Studio') }}">
                <span class="nav-item-icon"><span class="material-symbols-rounded">palette</span></span>
                <span>{{ __('Theme & Branding') }}</span>
            </a>
            <a href="{{ route('superadmin.features') }}" class="nav-item {{ request()->routeIs('superadmin.features*') ? 'active' : '' }}" data-tooltip="{{ __('Feature Flags') }}">
                <span class="nav-item-icon"><span class="material-symbols-rounded">flag</span></span>
                <span>{{ __('Feature Flags') }}</span>
            </a>

            <div class="nav-category-title">{{ __('Access & Security') }}</div>
            <a href="{{ route('superadmin.matrix') }}" class="nav-item {{ request()->routeIs('superadmin.matrix') ? 'active' : '' }}" data-tooltip="{{ __('Permission Matrix') }}">
                <span class="nav-item-icon"><span class="material-symbols-rounded">lock_person</span></span>
                <span>{{ __('Permission Matrix') }}</span>
            </a>
            <a href="{{ route('superadmin.settings') }}" class="nav-item {{ request()->routeIs('superadmin.settings') ? 'active' : '' }}" data-tooltip="{{ __('System Settings') }}">
                <span class="nav-item-icon"><span class="material-symbols-rounded">settings</span></span>
                <span>{{ __('System Settings') }}</span>
            </a>
            <a href="{{ route('superadmin.health') }}" class="nav-item {{ request()->routeIs('superadmin.health*') ? 'active' : '' }}" data-tooltip="{{ __('System Health') }}">
                <span class="nav-item-icon"><span class="material-symbols-rounded">monitor_heart</span></span>
                <span>{{ __('System Health') }}</span>
            </a>
            <a href="{{ route('superadmin.translations') }}" class="nav-item {{ request()->routeIs('superadmin.translations*') ? 'active' : '' }}" data-tooltip="{{ __('Translations') }}">
                <span class="nav-item-icon"><span class="material-symbols-rounded">translate</span></span>
                <span>{{ __('Translations') }}</span>
            </a>

            <div class="nav-category-title">{{ __('Session') }}</div>
            <form method="POST" action="{{ route('logout') }}" style="margin: 0; padding: 0;">
                @csrf
                <button type="submit" class="nav-item" style="width: 100%; border: none; background: none; text-align: start; cursor: pointer; color: var(--ula-status-danger);" data-tooltip="{{ __('Logout') }}">
                    <span class="nav-item-icon"><span class="material-symbols-rounded">logout</span></span>
                    <span>{{ __('Logout') }}</span>
                </button>
            </form>
        </nav>

        <div class="admin-sidebar-footer">
            <span style="font-family: var(--ula-font-mono); font-size: 11px;">UlaSpace 2.0</span>
            <button onclick="toggleSuperAdminTheme()" class="theme-toggle-btn" style="padding: 4px 8px; font-size: 16px; display: inline-flex; align-items: center; justify-content: center;">
                <span class="material-symbols-rounded" id="superadmin-theme-icon" style="font-size: 16px;">dark_mode</span>
            </button>
        </div>
    </aside>
    <div class="sidebar-backdrop" id="sidebarBackdrop" onclick="toggleSidebar()"></div>

    <!-- Main Content -->
    <div class="admin-main">
        <header class="admin-header">
            <div class="admin-header-left">
                <button class="menu-toggle-btn" onclick="toggleSidebar()" aria-label="Toggle Navigation">
                    <span class="material-symbols-rounded">menu</span>
                </button>
                <button onclick="toggleSuperAdminSidebarCollapse()" class="theme-toggle-btn" style="display: inline-flex; align-items: center; justify-content: center;" title="{{ __('Toggle Sidebar') }}">
                    <span class="material-symbols-rounded" id="header-collapse-icon" style="font-size: 18px;">chevron_left</span>
                </button>
                <h1>@yield('page_title', __('Dashboard'))</h1>
            </div>
            <div class="admin-header-right">
                <!-- Theme Toggle Button in Header -->
                <button onclick="toggleSuperAdminTheme()" class="theme-toggle-btn" aria-label="Toggle theme">
                    <span class="material-symbols-rounded" id="header-theme-icon" style="font-size: 18px;">dark_mode</span>
                </button>

                <!-- Language Switcher -->
                @if(app()->getLocale() === 'ar')
                    <a href="{{ route('lang.switch', 'en') }}" class="lang-switch-btn" style="display: inline-flex; align-items: center; gap: 6px;">
                        <span class="material-symbols-rounded" style="font-size: 16px;">language</span>
                        <span>English</span>
                    </a>
                @else
                    <a href="{{ route('lang.switch', 'ar') }}" class="lang-switch-btn" style="display: inline-flex; align-items: center; gap: 6px;">
                        <span class="material-symbols-rounded" style="font-size: 16px;">language</span>
                        <span>العربية</span>
                    </a>
                @endif

                <!-- User Profile Capsule -->
                <div style="display: flex; align-items: center; gap: 8px; padding: 4px 10px; background: var(--ula-surface-page-alt); border: 1px solid var(--ula-border-subtle); border-radius: 9999px; box-shadow: var(--ula-shadow-xs);">
                    @if(auth()->user()?->avatar_url)
                        <img src="{{ auth()->user()->avatar_url }}" alt="{{ auth()->user()->name }}" style="width: 28px; height: 28px; border-radius: 50%; object-fit: cover;">
                    @else
                        <div style="width: 28px; height: 28px; border-radius: 50%; background: var(--ula-gradient-accent); color: var(--ula-white); display: flex; align-items: center; justify-content: center; font-size: 11px; font-weight: 900;">
                            {{ strtoupper(substr(auth()->user()?->name ?? 'SA', 0, 2)) }}
                        </div>
                    @endif
                    <span style="font-size: 12px; font-weight: 800; color: var(--ula-text-primary);">{{ explode(' ', auth()->user()?->name ?? 'Admin')[0] }}</span>
                </div>

                <!-- Super Admin Logout Header Button -->
                <form method="POST" action="{{ route('logout') }}" style="display: inline; margin: 0;">
                    @csrf
                    <button type="submit" class="tactile-btn" style="background: rgba(217, 107, 95, 0.15); color: var(--ula-status-danger); border: 1px solid rgba(217, 107, 95, 0.35); padding: 7px 14px; font-size: 12px; font-weight: 800; cursor: pointer; border-radius: 12px; display: inline-flex; align-items: center; gap: 6px;" title="{{ __('Logout') }}">
                        <span class="material-symbols-rounded" style="font-size: 16px;">logout</span>
                        <span>{{ __('Logout') }}</span>
                    </button>
                </form>
            </div>
        </header>

        <main class="admin-body">
            @if(session('success'))
                <div class="alert-box alert-success" style="display: flex; align-items: center; gap: 8px;">
                    <span class="material-symbols-rounded">check_circle</span>
                    <span>{{ session('success') }}</span>
                </div>
            @endif

            @if(session('error'))
                <div class="alert-box alert-error" style="display: flex; align-items: center; gap: 8px;">
                    <span class="material-symbols-rounded">warning</span>
                    <span>{{ session('error') }}</span>
                </div>
            @endif

            @yield('content')
        </main>
    </div>

    <script nonce="{{ $cspNonce ?? '' }}">
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
            let iconName = 'chevron_left';
            if (isCollapsed) {
                iconName = isRtl ? 'chevron_left' : 'chevron_right';
            } else {
                iconName = isRtl ? 'chevron_right' : 'chevron_left';
            }
            if (arrowEl) arrowEl.textContent = iconName;
            if (headerIcon) headerIcon.textContent = iconName;
        }

        function toggleSuperAdminTheme() {
            const current = document.documentElement.getAttribute('data-theme') || 'dark';
            const next = current === 'dark' ? 'light' : 'dark';
            document.documentElement.setAttribute('data-theme', next);
            localStorage.setItem('vw_theme', next);
            updateThemeIcons(next);
        }

        function updateThemeIcons(theme) {
            const icon = theme === 'dark' ? 'dark_mode' : 'light_mode';
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
