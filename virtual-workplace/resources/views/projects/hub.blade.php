<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}" data-theme="light">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $project->name }} ({{ $project->code }}) — {{ __('Project Hub') }} | {{ $organization->name }}</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- Google Fonts: Cairo (Arabic) & Inter (English) -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;500;600;700;800;900&family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">

    <style>
        :root {
            /* 🌿 Natural Spatial Palette (Forest Green & Warm Ivory) */
            --bg-body: #F5F3E8;
            --bg-surface: #FFFDF6;
            --bg-surface-subtle: #F7F5EC;
            --bg-elevated: #FFFFFF;
            --sidebar-bg: #1B3524;
            --sidebar-text: #E8EFE9;
            --sidebar-text-muted: #A3BDA8;
            --sidebar-hover: rgba(255, 255, 255, 0.08);
            --sidebar-active: #245C3A;
            --sidebar-border: rgba(255, 255, 255, 0.12);

            --border-color: #D5DED0;
            --border-subtle: #E2E8DC;
            --border-focus: #245C3A;

            --text-primary: #192D21;
            --text-secondary: #4A5B4E;
            --text-muted: #637567;

            --brand-forest: #245C3A;
            --brand-sage: #3F7D4F;
            --brand-leaf: #4F9B5F;
            --brand-gold: #D6A23A;
            --status-danger: #D96B5F;
            --status-warning: #D6A23A;
            --status-success: #4F9B5F;
            --accent-gradient: linear-gradient(135deg, #1C4D30 0%, #245C3A 50%, #3F7D4F 100%);
            
            --shadow-soft-3d: 5px 5px 12px rgba(36, 92, 58, 0.08), -3px -3px 8px rgba(255, 255, 255, 0.95);
            --shadow-card: 0 14px 34px rgba(32, 64, 42, 0.08), 0 3px 8px rgba(32, 64, 42, 0.04), inset 0 1px 0 rgba(255, 255, 255, 0.95);
            --shadow-elevated: 0 20px 44px rgba(32, 64, 42, 0.14), 0 6px 14px rgba(32, 64, 42, 0.06), inset 0 1px 0 rgba(255, 255, 255, 1);
            --shadow-inset-3d: inset 2px 2px 6px rgba(36, 92, 58, 0.07), inset -2px -2px 6px rgba(255, 255, 255, 0.95);

            --radius-sm: 8px;
            --radius-md: 12px;
            --radius-lg: 16px;
            --radius-xl: 22px;
            --radius-full: 9999px;
            
            --transition-smooth: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
        }

        /* 🌙 Dark Spatial Workspace Mode */
        [data-theme="dark"], body.dark-mode {
            --bg-body: #0C1711;
            --bg-surface: #13241B;
            --bg-surface-subtle: #0F1E16;
            --bg-elevated: #192D21;
            --sidebar-bg: #09130E;
            --sidebar-text: #E8EFE9;
            --sidebar-text-muted: #7E9C84;
            --sidebar-hover: rgba(255, 255, 255, 0.06);
            --sidebar-active: #224E33;
            --sidebar-border: rgba(255, 255, 255, 0.08);

            --border-color: #213B2C;
            --border-subtle: #1A3124;
            --border-focus: #5CB87A;

            --text-primary: #F5FBF6;
            --text-secondary: #C0D6C5;
            --text-muted: #88A690;

            --brand-forest: #4F9B5F;
            --brand-sage: #5CB87A;
            --brand-leaf: #6CC989;
            --brand-gold: #E5B54F;
            --status-danger: #E27B70;
            --status-warning: #E5B54F;
            --status-success: #5CB87A;
            --accent-gradient: linear-gradient(135deg, #183824 0%, #224E33 50%, #2E6B46 100%);

            --shadow-soft-3d: 0 4px 16px rgba(0, 0, 0, 0.45);
            --shadow-card: 0 8px 26px rgba(0, 0, 0, 0.55), 0 2px 6px rgba(0, 0, 0, 0.35);
            --shadow-elevated: 0 18px 42px rgba(0, 0, 0, 0.65), 0 4px 16px rgba(0, 0, 0, 0.45);
            --shadow-inset-3d: inset 0 2px 6px rgba(0, 0, 0, 0.55);
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Cairo', 'Inter', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            background-color: var(--bg-body);
            color: var(--text-primary);
            min-height: 100vh;
            line-height: 1.5;
            transition: background-color 0.3s ease, color 0.3s ease;
            display: flex;
            overflow-x: hidden;
        }

        a {
            color: inherit;
            text-decoration: none;
        }

        /* ── WORKSPACE APP SHELL & SIDEBAR ── */
        .app-sidebar {
            width: 270px;
            background: var(--sidebar-bg);
            color: var(--sidebar-text);
            height: 100vh;
            position: sticky;
            top: 0;
            display: flex;
            flex-direction: column;
            border-inline-end: 1px solid var(--sidebar-border);
            z-index: 200;
            transition: width 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            flex-shrink: 0;
            overflow-y: auto;
            overflow-x: hidden;
            box-shadow: 4px 0 24px rgba(0, 0, 0, 0.15);
        }

        .app-sidebar.collapsed {
            width: 76px;
        }

        .sidebar-brand-box {
            padding: 20px 22px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            border-bottom: 1px solid var(--sidebar-border);
        }

        .brand-logo-area {
            display: flex;
            align-items: center;
            gap: 12px;
            overflow: hidden;
            white-space: nowrap;
        }

        .brand-emblem {
            width: 38px;
            height: 38px;
            border-radius: var(--radius-md);
            background: var(--accent-gradient);
            color: #FFFDF6;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            font-weight: 900;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.25);
            flex-shrink: 0;
        }

        .brand-title {
            font-size: 15px;
            font-weight: 900;
            letter-spacing: -0.2px;
            color: #FFFDF6;
        }

        .brand-sub {
            font-size: 11px;
            color: var(--sidebar-text-muted);
        }

        .sidebar-nav-list {
            list-style: none;
            padding: 16px 12px;
            display: flex;
            flex-direction: column;
            gap: 4px;
            flex: 1;
        }

        .nav-category-title {
            font-size: 10px;
            font-weight: 900;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            color: var(--sidebar-text-muted);
            padding: 12px 14px 6px 14px;
            white-space: nowrap;
        }

        .app-sidebar.collapsed .nav-category-title {
            display: none;
        }

        .sidebar-link-btn {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 10px 14px;
            border-radius: var(--radius-md);
            color: var(--sidebar-text);
            font-size: 13px;
            font-weight: 700;
            transition: var(--transition-smooth);
            cursor: pointer;
            position: relative;
            white-space: nowrap;
        }

        .sidebar-link-btn:hover {
            background: var(--sidebar-hover);
            color: #FFFDF6;
            transform: translateX(2px);
        }

        html[dir="rtl"] .sidebar-link-btn:hover {
            transform: translateX(-2px);
        }

        .sidebar-link-btn.active {
            background: var(--sidebar-active);
            color: #FFFDF6;
            box-shadow: 0 4px 14px rgba(0, 0, 0, 0.25);
        }

        .sidebar-link-btn .nav-icon {
            font-size: 16px;
            width: 22px;
            text-align: center;
            flex-shrink: 0;
        }

        .app-sidebar.collapsed .nav-label-text,
        .app-sidebar.collapsed .sidebar-badge-pill,
        .app-sidebar.collapsed .brand-title-wrap,
        .app-sidebar.collapsed .user-info-text {
            display: none;
        }

        .sidebar-badge-pill {
            margin-inline-start: auto;
            background: rgba(255, 255, 255, 0.15);
            color: #FFFDF6;
            font-size: 11px;
            font-weight: 800;
            padding: 2px 8px;
            border-radius: var(--radius-full);
        }

        .sidebar-footer {
            padding: 16px;
            border-top: 1px solid var(--sidebar-border);
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        /* ── MAIN CONTENT AREA ── */
        .app-main-layout {
            flex: 1;
            display: flex;
            flex-direction: column;
            min-width: 0;
            min-height: 100vh;
        }

        /* Sticky Top Header */
        .hub-header-bar {
            height: 70px;
            background: var(--bg-surface);
            border-bottom: 1px solid var(--border-color);
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 28px;
            position: sticky;
            top: 0;
            z-index: 100;
            backdrop-filter: blur(12px);
            box-shadow: var(--shadow-soft-3d);
        }

        .breadcrumb-trail {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 13px;
            font-weight: 700;
            color: var(--text-muted);
            flex-wrap: wrap;
        }

        .breadcrumb-trail a:hover {
            color: var(--brand-forest);
        }

        .breadcrumb-separator {
            font-size: 11px;
            opacity: 0.5;
        }

        .hub-main-container {
            max-width: 1440px;
            width: 100%;
            margin: 0 auto;
            padding: 24px 28px 60px 28px;
        }

        /* ── 3D TACTILE BUTTONS & CARDS ── */
        .tactile-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 10px 18px;
            border-radius: var(--radius-md);
            font-size: 13px;
            font-weight: 800;
            cursor: pointer;
            border: 1px solid transparent;
            transition: var(--transition-smooth);
            font-family: inherit;
        }

        .btn-primary {
            background: #245C3A;
            color: #FFFDF6;
            box-shadow: 0 3px 0 #183F27, 0 6px 14px rgba(36, 92, 58, 0.25);
        }

        .btn-primary:hover {
            background: #1C4D30;
            transform: translateY(-1.5px);
            box-shadow: 0 4px 0 #183F27, 0 8px 18px rgba(36, 92, 58, 0.32);
        }

        .btn-primary:active {
            transform: translateY(2px);
            box-shadow: 0 1px 0 #183F27;
        }

        .btn-secondary {
            background: var(--bg-surface);
            color: var(--text-primary);
            border-color: var(--border-color);
            box-shadow: 0 3px 0 var(--border-color), var(--shadow-soft-3d);
        }

        .btn-secondary:hover {
            background: var(--bg-surface-subtle);
            border-color: var(--brand-forest);
            transform: translateY(-1.5px);
            box-shadow: 0 4px 0 var(--border-color), var(--shadow-card);
        }

        .btn-secondary:active {
            transform: translateY(2px);
            box-shadow: 0 1px 0 var(--border-color);
        }

        /* Hero Project Banner */
        .hero-banner-card {
            background: linear-gradient(135deg, #DFEBDB 0%, #F5F9F1 45%, #FFFDF6 100%);
            border: 1px solid #CADAC3;
            border-radius: var(--radius-xl);
            padding: 28px;
            margin-bottom: 24px;
            box-shadow: var(--shadow-card);
            position: relative;
            overflow: hidden;
        }

        .hero-banner-card::before {
            content: '';
            position: absolute;
            top: 0;
            inset-inline-start: 0;
            inset-inline-end: 0;
            height: 4px;
            background: var(--accent-gradient);
        }

        /* Badges & Pills */
        .badge-pill {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 4px 10px;
            border-radius: var(--radius-full);
            font-size: 11px;
            font-weight: 800;
        }

        .badge-green { background: rgba(79, 155, 95, 0.15); color: #4F9B5F; border: 1px solid rgba(79, 155, 95, 0.25); }
        .badge-gold { background: rgba(214, 162, 58, 0.15); color: #D6A23A; border: 1px solid rgba(214, 162, 58, 0.25); }
        .badge-danger { background: rgba(217, 107, 95, 0.15); color: #D96B5F; border: 1px solid rgba(217, 107, 95, 0.25); }
        .badge-neutral { background: var(--bg-surface-subtle); color: var(--text-secondary); border: 1px solid var(--border-color); }

        /* KPI Grid (Enhanced Spatial Depth) */
        .kpi-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(210px, 1fr));
            gap: 16px;
            margin-bottom: 24px;
        }

        .kpi-card {
            background: var(--bg-surface);
            border: 1px solid var(--border-color);
            border-radius: var(--radius-lg);
            padding: 18px 20px;
            box-shadow: var(--shadow-card);
            position: relative;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            transition: var(--transition-smooth);
        }

        .kpi-card:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-elevated);
            border-color: var(--brand-forest);
        }

        .kpi-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 8px;
        }

        .kpi-title {
            font-size: 11px;
            font-weight: 800;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .kpi-icon-box {
            width: 38px;
            height: 38px;
            border-radius: 12px;
            background: linear-gradient(145deg, #437E51 0%, #225433 100%);
            border: 1px solid #1B4529;
            color: #FFFFFF !important;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 16px;
            box-shadow: 0 4px 12px rgba(34, 84, 51, 0.32), inset 0 1px 1px rgba(255, 255, 255, 0.45);
            text-shadow: 0 1px 2px rgba(0,0,0,0.25);
        }

        .kpi-value {
            font-size: 24px;
            font-weight: 900;
            color: var(--text-primary);
            font-family: 'Inter', sans-serif;
            margin-bottom: 4px;
        }

        /* Navigation Sub Tabs */
        .hub-tabs-nav {
            display: flex;
            align-items: center;
            gap: 8px;
            background: var(--bg-surface-subtle);
            padding: 6px;
            border-radius: var(--radius-lg);
            border: 1px solid var(--border-color);
            box-shadow: var(--shadow-inset-3d);
            margin-bottom: 24px;
            overflow-x: auto;
        }

        .hub-tab-btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 18px;
            border-radius: var(--radius-md);
            font-size: 13px;
            font-weight: 800;
            cursor: pointer;
            border: none;
            background: transparent;
            color: var(--text-secondary);
            transition: var(--transition-smooth);
            white-space: nowrap;
        }

        .hub-tab-btn:hover {
            color: var(--text-primary);
            background: rgba(255, 255, 255, 0.5);
        }

        .hub-tab-btn.active {
            background: var(--bg-surface);
            color: var(--brand-forest);
            box-shadow: var(--shadow-soft-3d);
        }

        /* Card Container */
        .hub-card {
            background: var(--bg-surface);
            border: 1px solid var(--border-color);
            border-radius: var(--radius-xl);
            padding: 24px;
            box-shadow: var(--shadow-card);
            margin-bottom: 24px;
        }

        /* Tables */
        .data-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            font-size: 13px;
        }

        .data-table th {
            background: var(--bg-surface-subtle);
            color: var(--text-muted);
            font-weight: 800;
            text-transform: uppercase;
            font-size: 11px;
            letter-spacing: 0.5px;
            padding: 14px 18px;
            border-bottom: 1px solid var(--border-color);
            text-align: start;
        }

        .data-table td {
            padding: 14px 18px;
            border-bottom: 1px solid var(--border-color);
            color: var(--text-primary);
            vertical-align: middle;
        }

        .data-table tr:last-child td {
            border-bottom: none;
        }

        .data-table tr:hover td {
            background: var(--bg-surface-subtle);
        }

        /* Kanban Column */
        .kanban-grid {
            display: grid;
            grid-template-columns: repeat(5, minmax(260px, 1fr));
            gap: 16px;
            align-items: start;
            overflow-x: auto;
            padding-bottom: 16px;
        }

        .kanban-column {
            background: var(--bg-surface-subtle);
            border: 1px solid var(--border-color);
            border-radius: var(--radius-xl);
            padding: 16px;
            box-shadow: var(--shadow-inset-3d);
            min-height: 480px;
            display: flex;
            flex-direction: column;
        }

        .kanban-col-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 14px;
            padding-bottom: 8px;
            border-bottom: 1px solid var(--border-color);
            font-size: 13px;
            font-weight: 900;
        }

        .kanban-card {
            background: var(--bg-surface);
            border: 1px solid var(--border-color);
            border-radius: var(--radius-md);
            padding: 14px;
            box-shadow: var(--shadow-soft-3d);
            margin-bottom: 12px;
            cursor: pointer;
            transition: var(--transition-smooth);
            position: relative;
        }

        .kanban-card:hover {
            transform: translateY(-2px);
            border-color: var(--brand-forest);
            box-shadow: var(--shadow-card);
        }

        /* ── ClickUp 3D Tactile Task Context Menu ── */
        .task-context-menu {
            position: fixed;
            z-index: 100000;
            width: 250px;
            background: var(--bg-surface);
            border: 1px solid var(--border-color);
            border-radius: var(--radius-lg);
            box-shadow: 0 18px 45px rgba(0, 0, 0, 0.24), 0 4px 14px rgba(36, 92, 58, 0.14), inset 0 1px 0 rgba(255, 255, 255, 0.95);
            padding: 6px;
            display: none;
            flex-direction: column;
            gap: 2px;
            backdrop-filter: blur(16px);
            animation: ctxMenuScale 0.16s cubic-bezier(0.16, 1, 0.3, 1);
        }
        @keyframes ctxMenuScale {
            from { transform: scale(0.94) translateY(-6px); opacity: 0; }
            to { transform: scale(1) translateY(0); opacity: 1; }
        }
        .ctx-quick-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 4px;
            padding: 4px 6px 8px 6px;
            border-bottom: 1px solid var(--border-color);
            margin-bottom: 4px;
        }
        .ctx-quick-btn {
            flex: 1;
            padding: 6px 8px;
            background: var(--bg-surface-subtle);
            border: 1px solid var(--border-color);
            border-radius: 8px;
            font-size: 11px;
            font-weight: 700;
            color: var(--text-primary);
            cursor: pointer;
            text-align: center;
            transition: all 0.15s ease;
            white-space: nowrap;
        }
        .ctx-quick-btn:hover {
            background: rgba(36, 92, 58, 0.12);
            color: var(--brand-forest);
            border-color: var(--brand-forest);
            transform: translateY(-1px);
        }
        .ctx-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 8px 10px;
            border-radius: 8px;
            font-size: 12px;
            font-weight: 700;
            color: var(--text-primary);
            cursor: pointer;
            transition: all 0.15s ease;
            text-decoration: none;
            border: 1px solid transparent;
        }
        .ctx-item:hover {
            background: var(--bg-surface-subtle);
            color: var(--brand-forest);
            border-color: var(--border-color);
            transform: translateX({{ app()->getLocale() === 'ar' ? '-2px' : '2px' }});
        }
        .ctx-item.danger:hover {
            background: rgba(217, 107, 95, 0.15);
            color: #D96B5F;
            border-color: rgba(217, 107, 95, 0.3);
        }
        .ctx-divider {
            height: 1px;
            background: var(--border-color);
            margin: 4px 0;
        }
        .ctx-icon {
            width: 20px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 14px;
            margin-inline-end: 6px;
        }

        /* Modals */
        .modal-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(19, 45, 33, 0.65);
            backdrop-filter: blur(8px);
            z-index: 1000;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .modal-card {
            background: var(--bg-surface);
            border: 1px solid var(--border-color);
            border-radius: var(--radius-xl);
            box-shadow: var(--shadow-elevated);
            width: 100%;
            max-width: 600px;
            padding: 24px;
            max-height: 90vh;
            overflow-y: auto;
            position: relative;
        }

        .modal-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 18px;
            padding-bottom: 12px;
            border-bottom: 1px solid var(--border-color);
        }

        .modal-close {
            background: var(--bg-surface-subtle);
            border: 1px solid var(--border-color);
            width: 32px;
            height: 32px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            font-weight: 800;
            color: var(--text-primary);
        }

        .form-input {
            width: 100%;
            background: var(--bg-surface-subtle);
            border: 1px solid var(--border-color);
            border-radius: var(--radius-md);
            padding: 10px 14px;
            color: var(--text-primary);
            font-size: 13px;
            font-weight: 600;
            font-family: inherit;
            outline: none;
            box-shadow: var(--shadow-inset-3d);
        }

        .form-input:focus {
            border-color: var(--brand-forest);
        }

        /* Toast Container */
        #hub-toast-container {
            position: fixed;
            bottom: 24px;
            inset-inline-end: 24px;
            z-index: 9999;
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .hub-toast {
            background: var(--bg-surface);
            border: 1px solid var(--border-color);
            border-radius: var(--radius-lg);
            padding: 14px 20px;
            box-shadow: var(--shadow-elevated);
            color: var(--text-primary);
            font-size: 13px;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 10px;
            animation: slideIn 0.3s ease;
        }

        @keyframes slideIn {
            from { transform: translateY(20px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }

        /* Responsive Breakpoints */
        @media (max-width: 992px) {
            .app-sidebar {
                position: fixed;
                left: 0;
                top: 0;
                bottom: 0;
                transform: translateX(-100%);
            }
            html[dir="rtl"] .app-sidebar {
                left: auto;
                right: 0;
                transform: translateX(100%);
            }
            .app-sidebar.mobile-open {
                transform: translateX(0) !important;
            }
            .hub-header-bar {
                padding: 0 16px;
            }
            .hub-main-container {
                padding: 16px;
            }
        }
    </style>
</head>
<body>

    <!-- ── 1. COLLAPSIBLE WORKSPACE SIDEBAR NAVIGATION ── -->
    <aside id="sidebar-main" class="app-sidebar">
        <div class="sidebar-brand-box">
            <div class="brand-logo-area">
                <div class="brand-emblem">🌿</div>
                <div class="brand-title-wrap">
                    <div class="brand-title">{{ $organization->name }}</div>
                    <div class="brand-sub">{{ __('Virtual Workplace') }}</div>
                </div>
            </div>
            <button onclick="toggleSidebarCollapse()" class="tactile-btn btn-secondary" style="padding: 4px 8px; font-size: 11px; background: transparent; border: 1px solid var(--sidebar-border); color: var(--sidebar-text-muted);" title="{{ __('Toggle Slim Sidebar') }}">
                ↔
            </button>
        </div>

        <ul class="sidebar-nav-list">
            <!-- Overview & Office -->
            <li class="nav-category-title">{{ __('Workspace') }}</li>
            <li>
                <a href="{{ route('dashboard') }}#overview" class="sidebar-link-btn">
                    <span class="nav-icon">📊</span>
                    <span class="nav-label-text">{{ __('Dashboard') }}</span>
                </a>
            </li>
            <li>
                <a href="{{ route('office') }}" class="sidebar-link-btn" style="background: rgba(79, 155, 95, 0.18); color: #7EE092;">
                    <span class="nav-icon">🚀</span>
                    <span class="nav-label-text">{{ __('Enter Office') }}</span>
                    <span class="sidebar-badge-pill" style="background: #4F9B5F; color: white;">LIVE</span>
                </a>
            </li>

            <!-- Projects & Execution -->
            <li class="nav-category-title">{{ __('Projects & Tasks') }}</li>
            <li>
                <a href="{{ route('dashboard') }}#projects" class="sidebar-link-btn active">
                    <span class="nav-icon">📁</span>
                    <span class="nav-label-text">{{ __('Projects Portfolio') }}</span>
                    <span class="sidebar-badge-pill">{{ $stats['total_projects'] ?? 0 }}</span>
                </a>
            </li>
            @if($membership->hasPermission('tasks.assign') || $membership->hasPermission('tasks.delete') || $membership->role?->slug === 'company_admin')
            <li>
                <a href="{{ route('dashboard') }}#all-tasks" class="sidebar-link-btn">
                    <span class="nav-icon">📑</span>
                    <span class="nav-label-text">{{ __('All Tasks') }}</span>
                    <span class="sidebar-badge-pill">{{ $stats['total_tasks'] ?? 0 }}</span>
                </a>
            </li>
            @endif
            <li>
                <a href="{{ route('dashboard') }}#my-tasks" class="sidebar-link-btn">
                    <span class="nav-icon">⚡</span>
                    <span class="nav-label-text">{{ __('My Tasks') }}</span>
                    <span class="sidebar-badge-pill">{{ $myTasks->count() }}</span>
                </a>
            </li>
            <li>
                <a href="{{ route('dashboard') }}#chat" class="sidebar-link-btn">
                    <span class="nav-icon">💬</span>
                    <span class="nav-label-text">{{ __('Team Chat & DMs') }}</span>
                    <span class="sidebar-badge-pill" style="background: rgba(79, 155, 95, 0.2); color: #7EE092;">LIVE</span>
                </a>
            </li>
            <li>
                <a href="{{ route('dashboard') }}#timesheets" class="sidebar-link-btn">
                    <span class="nav-icon">⏱️</span>
                    <span class="nav-label-text">{{ __('Timesheets & Time') }}</span>
                </a>
            </li>
            <li>
                <a href="{{ route('dashboard') }}#meetings" class="sidebar-link-btn">
                    <span class="nav-icon">📅</span>
                    <span class="nav-label-text">{{ __('Meetings & Schedule') }}</span>
                    <span class="sidebar-badge-pill">{{ $upcomingProjectMeetings->count() }}</span>
                </a>
            </li>

            <!-- Administration -->
            @php
                $canSeeAdmin = $membership->hasPermission('members.view') || $membership->hasPermission('rooms.manage') || $membership->hasPermission('departments.manage') || $membership->hasPermission('organizations.manage') || $membership->role?->slug === 'company_admin';
            @endphp
            @if($canSeeAdmin)
            <li class="nav-category-title">{{ __('Administration') }}</li>
            @if($membership->hasPermission('members.view') || $membership->hasPermission('members.manage'))
            <li>
                <a href="{{ route('dashboard') }}#members" class="sidebar-link-btn">
                    <span class="nav-icon">👥</span>
                    <span class="nav-label-text">{{ __('Team Members') }}</span>
                    <span class="sidebar-badge-pill">{{ $stats['active_members'] ?? 0 }}</span>
                </a>
            </li>
            @endif
            @if($membership->hasPermission('rooms.manage'))
            <li>
                <a href="{{ route('dashboard') }}#rooms" class="sidebar-link-btn">
                    <span class="nav-icon">🚪</span>
                    <span class="nav-label-text">{{ __('Meeting Rooms') }}</span>
                    <span class="sidebar-badge-pill">{{ $stats['total_rooms'] ?? 0 }}</span>
                </a>
            </li>
            @endif
            @if($membership->hasPermission('departments.manage') || $membership->hasPermission('teams.manage'))
            <li>
                <a href="{{ route('dashboard') }}#departments" class="sidebar-link-btn">
                    <span class="nav-icon">🏛️</span>
                    <span class="nav-label-text">{{ __('Departments') }}</span>
                    <span class="sidebar-badge-pill">{{ $stats['total_departments'] ?? 0 }}</span>
                </a>
            </li>
            @endif
            @if($membership->hasPermission('organizations.manage'))
            <li>
                <a href="{{ route('dashboard') }}#settings" class="sidebar-link-btn">
                    <span class="nav-icon">⚙️</span>
                    <span class="nav-label-text">{{ __('Workspace Settings') }}</span>
                </a>
            </li>
            @endif
            @endif

            @if($user->is_superadmin ?? false)
                <li class="nav-category-title" style="color: #E5B54F;">{{ __('Super Admin') }}</li>
                <li>
                    <a href="{{ route('superadmin.dashboard') }}" class="sidebar-link-btn" style="color: #E5B54F;">
                        <span class="nav-icon">👑</span>
                        <span class="nav-label-text">{{ __('Super Admin Portal') }}</span>
                    </a>
                </li>
            @endif
        </ul>

        <div class="sidebar-footer">
            <div style="display: flex; align-items: center; gap: 10px;">
                <div style="width: 32px; height: 32px; border-radius: 50%; background: var(--accent-gradient); color: white; display: flex; align-items: center; justify-content: center; font-size: 11px; font-weight: 900;">
                    {{ strtoupper(substr($user->name, 0, 2)) }}
                </div>
                <div class="user-info-text">
                    <div style="font-size: 12px; font-weight: 800; color: #FFFDF6;">{{ $user->name }}</div>
                    <div style="font-size: 10px; color: var(--sidebar-text-muted);">{{ $membership->role->name ?? 'Member' }}</div>
                </div>
            </div>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" style="background: transparent; border: none; color: var(--sidebar-text-muted); cursor: pointer; font-size: 14px;" title="{{ __('Logout') }}">
                    🚪
                </button>
            </form>
        </div>
    </aside>

    <!-- ── 2. MAIN APP CONTENT CONTAINER ── -->
    <div class="app-main-layout">

        <!-- Top Sticky Header -->
        <header class="hub-header-bar">
            <div style="display: flex; align-items: center; gap: 14px;">
                <button onclick="toggleMobileSidebar()" class="tactile-btn btn-secondary" style="display: none; padding: 6px 10px; font-size: 14px;" id="btn-mobile-sidebar-toggle">
                    ☰
                </button>

                <div class="breadcrumb-trail">
                    <a href="{{ route('dashboard') }}#overview">🌿 {{ __('Workspace') }}</a>
                    <span class="breadcrumb-separator">/</span>
                    <a href="{{ route('dashboard') }}#projects">📁 {{ __('Projects') }}</a>
                    <span class="breadcrumb-separator">/</span>
                    <span style="color: var(--brand-forest); font-weight: 900;">{{ $project->name }} ({{ $project->code }})</span>
                </div>
            </div>

            <div style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap;">
                <!-- Language Switcher -->
                <a href="{{ route('lang.switch', app()->getLocale() === 'ar' ? 'en' : 'ar') }}" class="tactile-btn btn-secondary" style="padding: 7px 12px; font-size: 12px;">
                    🌐 {{ app()->getLocale() === 'ar' ? 'English' : 'العربية' }}
                </a>

                <!-- Theme Toggle -->
                <button onclick="toggleThemeMode()" class="tactile-btn btn-secondary" style="padding: 7px 12px; font-size: 12px;" title="{{ __('Toggle Dark / Light Mode') }}">
                    <span class="theme-toggle-icon-label">🌙</span>
                </button>

                <!-- Office Shortcut -->
                <a href="{{ route('office') }}" class="tactile-btn btn-primary" style="padding: 7px 14px; font-size: 12px;">
                    🚀 {{ __('Enter Office') }}
                </a>
            </div>
        </header>

        <main class="hub-main-container">

            <!-- Active Timer Bar if running -->
            @if($activeTimer)
                <div style="background: linear-gradient(135deg, rgba(79, 155, 95, 0.15) 0%, rgba(255, 253, 246, 0.95) 100%); border: 1px solid var(--brand-forest); border-radius: var(--radius-lg); padding: 14px 20px; display: flex; align-items: center; justify-content: space-between; margin-bottom: 20px; box-shadow: var(--shadow-card);">
                    <div style="display: flex; align-items: center; gap: 14px;">
                        <div style="width: 36px; height: 36px; border-radius: 50%; background: var(--brand-forest); color: white; display: flex; align-items: center; justify-content: center; font-size: 16px; box-shadow: var(--shadow-soft-3d);">
                            ⏱️
                        </div>
                        <div>
                            <div style="font-size: 11px; font-weight: 800; color: var(--brand-forest); text-transform: uppercase;">
                                {{ __('Active Timer Running') }} • {{ $activeTimer->project->name ?? 'Project' }}
                            </div>
                            <div style="font-size: 14px; font-weight: 800; color: var(--text-primary);">
                                {{ $activeTimer->task->title ?? ($activeTimer->description ?? 'Work Session') }}
                            </div>
                        </div>
                    </div>
                    <div style="display: flex; align-items: center; gap: 14px;">
                        <span id="hub-live-timer-clock" style="font-size: 20px; font-weight: 900; font-family: monospace; color: var(--brand-forest);">00:00:00</span>
                        <button onclick="stopHubGlobalTimer()" class="tactile-btn" style="background: #FEE2E2; color: #B91C1C; border: 1px solid #FECACA; padding: 6px 14px; font-size: 12px;">
                            ⏹ {{ __('Stop Timer') }}
                        </button>
                    </div>
                </div>
            @endif

            <!-- ── 3. PROJECT HERO BANNER CARD ── -->
            <div class="hero-banner-card">
                <div style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 20px;">
                    <div style="max-width: 720px;">
                        <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 12px; flex-wrap: wrap;">
                            <span class="badge-pill badge-neutral" style="font-family: monospace; font-size: 12px; font-weight: 900;">
                                {{ $project->code }}
                            </span>
                            <h1 style="font-size: 26px; font-weight: 900; color: var(--text-primary); margin: 0; line-height: 1.2;">
                                {{ $project->name }}
                            </h1>
                            <span class="badge-pill {{ $project->status === 'completed' ? 'badge-green' : ($project->status === 'active' ? 'badge-green' : 'badge-gold') }}">
                                {{ ucfirst($project->status) }}
                            </span>
                            <span class="badge-pill {{ $project->priority === 'urgent' ? 'badge-danger' : ($project->priority === 'high' ? 'badge-gold' : 'badge-neutral') }}">
                                ⚡ {{ ucfirst($project->priority) }}
                            </span>
                        </div>

                        <p style="font-size: 13px; color: var(--text-secondary); margin-bottom: 16px; line-height: 1.6;">
                            {{ $project->description ?? __('Collaborative workspace project for cross-functional execution, task delivery, timesheets, and team meetings.') }}
                        </p>

                        <div style="display: flex; align-items: center; gap: 20px; font-size: 12px; color: var(--text-muted); flex-wrap: wrap;">
                            <span>👤 {{ __('Manager') }}: <strong style="color: var(--text-primary);">{{ $project->manager->name ?? __('Unassigned') }}</strong></span>
                            <span>🏛️ {{ __('Department') }}: <strong style="color: var(--text-primary);">{{ $project->department->name ?? __('General') }}</strong></span>
                            <span>📅 {{ __('Due Date') }}: <strong style="color: var(--text-primary);">{{ $project->end_date ? $project->end_date->format('M d, Y') : __('No deadline') }}</strong></span>
                            <span>👥 {{ __('Project Team') }}: <strong style="color: var(--text-primary);">{{ $project->members->count() }} {{ __('Members') }}</strong></span>
                        </div>
                    </div>

                    <!-- Action Toolbar -->
                    <div style="display: flex; gap: 10px; align-items: center; flex-wrap: wrap;">
                        <button onclick="openNewTaskModal()" class="tactile-btn btn-primary">
                            <span>+</span> {{ __('Create Task') }}
                        </button>
                        <button onclick="openManualTimeModal()" class="tactile-btn btn-secondary">
                            <span>⏱️</span> {{ __('Log Manual Time Entry') }}
                        </button>
                        <button onclick="openScheduleProjectMeetingModal()" class="tactile-btn btn-secondary">
                            <span>📅</span> {{ __('Schedule Project Meeting') }}
                        </button>
                    </div>
                </div>
            </div>

            <!-- ── 4. 6 HIGH-DEPTH 3D KPI METRICS ── -->
            <div class="kpi-grid">
                <!-- 1. Progress -->
                <div class="kpi-card">
                    <div class="kpi-header">
                        <span class="kpi-title">{{ __('Task Progress') }}</span>
                        <div class="kpi-icon-box">📊</div>
                    </div>
                    <div class="kpi-value" style="color: var(--brand-forest);">{{ $kpis['progress_pct'] }}%</div>
                    <div style="width: 100%; background: var(--bg-surface-subtle); height: 7px; border-radius: 9999px; overflow: hidden; margin-bottom: 6px;">
                        <div style="width: {{ $kpis['progress_pct'] }}%; height: 100%; background: var(--brand-forest); border-radius: 9999px;"></div>
                    </div>
                    <div style="font-size: 11px; color: var(--text-muted); display: flex; justify-content: space-between;">
                        <span>{{ $kpis['completed_tasks'] }} / {{ $kpis['total_tasks'] }} {{ __('done') }}</span>
                        @if($kpis['overdue_tasks'] > 0)
                            <span style="color: var(--status-danger); font-weight: 800;">⚠️ {{ $kpis['overdue_tasks'] }} {{ __('overdue') }}</span>
                        @endif
                    </div>
                </div>

                <!-- 2. Hours & Effort -->
                <div class="kpi-card">
                    <div class="kpi-header">
                        <span class="kpi-title">{{ __('Actual vs Planned') }}</span>
                        <div class="kpi-icon-box">⏱️</div>
                    </div>
                    <div class="kpi-value">{{ $kpis['actual_hours'] }}h</div>
                    <div style="font-size: 11px; color: var(--text-secondary); margin-bottom: 4px;">
                        {{ __('Planned') }}: <strong>{{ $kpis['planned_hours'] }}h</strong>
                    </div>
                    <div style="font-size: 11px; color: {{ $kpis['hours_variance'] < 0 ? 'var(--status-danger)' : 'var(--brand-forest)' }}; font-weight: 800;">
                        {{ $kpis['hours_variance'] >= 0 ? '+' : '' }}{{ $kpis['hours_variance'] }}h {{ __('variance') }}
                    </div>
                </div>

                <!-- 3. Financials & Budget -->
                <div class="kpi-card">
                    <div class="kpi-header">
                        <span class="kpi-title">{{ __('Budget & Cost') }}</span>
                        <div class="kpi-icon-box">💰</div>
                    </div>
                    <div class="kpi-value">${{ number_format($kpis['labor_cost'], 2) }}</div>
                    <div style="font-size: 11px; color: var(--text-secondary); margin-bottom: 4px;">
                        {{ __('Budget') }}: <strong>${{ number_format($kpis['budget'], 2) }}</strong>
                    </div>
                    <div style="font-size: 11px; color: {{ $kpis['budget_variance'] < 0 ? 'var(--status-danger)' : 'var(--brand-forest)' }}; font-weight: 800;">
                        {{ $kpis['budget_variance'] >= 0 ? __('Remaining') : __('Over') }}: ${{ number_format(abs($kpis['budget_variance']), 2) }}
                    </div>
                </div>

                <!-- 4. Revenue & Margin -->
                <div class="kpi-card">
                    <div class="kpi-header">
                        <span class="kpi-title">{{ __('Revenue & Margin') }}</span>
                        <div class="kpi-icon-box">📈</div>
                    </div>
                    <div class="kpi-value" style="color: var(--brand-forest);">${{ number_format($kpis['billable_revenue'], 2) }}</div>
                    <div style="font-size: 11px; color: var(--text-secondary); margin-bottom: 4px;">
                        {{ __('Gross Margin') }}: <strong>${{ number_format($kpis['gross_margin'], 2) }}</strong>
                    </div>
                    <div style="font-size: 11px; color: #4F9B5F; font-weight: 800;">
                        {{ $kpis['gross_margin_pct'] }}% {{ __('Margin Rate') }}
                    </div>
                </div>

                <!-- 5. Sprint Workload -->
                <div class="kpi-card">
                    <div class="kpi-header">
                        <span class="kpi-title">{{ __('Sprint Workload') }}</span>
                        <div class="kpi-icon-box">⚡</div>
                    </div>
                    <div class="kpi-value">{{ $kpis['in_progress_tasks'] + $kpis['review_tasks'] }}</div>
                    <div style="font-size: 11px; color: var(--text-secondary); margin-bottom: 4px;">
                        ⚡ {{ $kpis['in_progress_tasks'] }} {{ __('in progress') }} • 🔍 {{ $kpis['review_tasks'] }} {{ __('in review') }}
                    </div>
                    <div style="font-size: 11px; color: var(--text-muted); font-weight: 700;">
                        📌 {{ $kpis['backlog_tasks'] }} {{ __('in backlog/ready') }}
                    </div>
                </div>

                <!-- 6. Team Meetings -->
                <div class="kpi-card">
                    <div class="kpi-header">
                        <span class="kpi-title">{{ __('Team Meetings') }}</span>
                        <div class="kpi-icon-box">📅</div>
                    </div>
                    <div class="kpi-value">{{ $upcomingProjectMeetings->count() }}</div>
                    <div style="font-size: 11px; color: var(--brand-forest); margin-bottom: 4px; font-weight: 800;">
                        🟢 {{ __('Ready for collaboration') }}
                    </div>
                    <div style="font-size: 11px; color: var(--text-muted);">
                        {{ $projectMeetings->count() }} {{ __('total sessions held') }}
                    </div>
                </div>
            </div>

            <!-- ── 5. CLICKUP-GRADE MULTI-VIEWS NAVIGATION BAR ── -->
            <div class="hub-tabs-nav">
                <button onclick="switchHubSection('kanban')" id="hub-nav-btn-kanban" class="hub-tab-btn active">
                    📌 {{ __('Kanban Board') }} (<span id="count-total-kanban">{{ $tasks->count() }}</span>)
                </button>
                <button onclick="switchHubSection('tasks')" id="hub-nav-btn-tasks" class="hub-tab-btn">
                    📋 {{ __('List View & Custom Fields') }}
                </button>
                <button onclick="switchHubSection('gantt')" id="hub-nav-btn-gantt" class="hub-tab-btn">
                    📊 {{ __('Gantt / Timeline') }}
                </button>
                <button onclick="switchHubSection('workload')" id="hub-nav-btn-workload" class="hub-tab-btn">
                    📈 {{ __('Workload Matrix') }}
                </button>
                <button onclick="switchHubSection('docs')" id="hub-nav-btn-docs" class="hub-tab-btn">
                    📚 {{ __('Docs & Wiki') }} ({{ $project->documents->count() }})
                </button>
                <button onclick="switchHubSection('goals')" id="hub-nav-btn-goals" class="hub-tab-btn">
                    🎯 {{ __('Goals & Targets') }} ({{ $project->goals->count() }})
                </button>
                <button onclick="switchHubSection('timelog')" id="hub-nav-btn-timelog" class="hub-tab-btn">
                    ⏱️ {{ __('Time & Margin') }} ({{ $project->timeEntries->count() }})
                </button>
                <button onclick="switchHubSection('meetings')" id="hub-nav-btn-meetings" class="hub-tab-btn">
                    📅 {{ __('Meetings') }} ({{ $upcomingProjectMeetings->count() }})
                </button>
                <button onclick="switchHubSection('team')" id="hub-nav-btn-team" class="hub-tab-btn">
                    👥 {{ __('Team Roster') }} ({{ $project->members->count() }})
                </button>
                <button onclick="switchHubSection('milestones')" id="hub-nav-btn-milestones" class="hub-tab-btn">
                    🚩 {{ __('Roadmap') }} ({{ $project->milestones->count() }})
                </button>
            </div>

            <!-- ── 6. SUB-TAB VIEWS ── -->

            <!-- TAB 1: KANBAN BOARD -->
            <div id="hub-section-kanban" class="hub-section-content" style="display: block;">
                <div class="kanban-grid">
                    @php
                        $columns = [
                            'backlog' => ['title' => '📌 ' . __('Backlog'), 'color' => 'var(--text-secondary)'],
                            'ready' => ['title' => '🎯 ' . __('Ready'), 'color' => 'var(--brand-sage)'],
                            'in_progress' => ['title' => '⚡ ' . __('In Progress'), 'color' => 'var(--brand-forest)'],
                            'review' => ['title' => '🔍 ' . __('Review / QA'), 'color' => 'var(--status-warning)'],
                            'done' => ['title' => '🎉 ' . __('Done'), 'color' => 'var(--brand-forest)'],
                        ];
                    @endphp

                    @foreach($columns as $colKey => $colMeta)
                        @php
                            $colTasks = $tasks->where('status', $colKey);
                        @endphp
                        <div class="kanban-column" id="kanban-column-{{ $colKey }}">
                            <div class="kanban-col-header" style="color: {{ $colMeta['color'] }};">
                                <span>{{ $colMeta['title'] }}</span>
                                <span class="badge-pill badge-neutral">{{ $colTasks->count() }}</span>
                            </div>

                            <div style="display: flex; flex-direction: column; gap: 10px; flex: 1;">
                                @forelse($colTasks as $t)
                                    @php
                                        $canEditThisTask = ($user->isSuperAdmin() || $membership->role?->slug === 'company_admin' || $membership->hasPermission('tasks.assign') || $membership->hasPermission('tasks.delete') || ($project && $project->manager_id === $user->id) || $t->assignee_id === $user->id || $t->creator_id === $user->id);
                                    @endphp
                                    <div class="kanban-card" 
                                         onclick="openTaskInspector('{{ $t->id }}')"
                                         oncontextmenu="event.preventDefault(); event.stopPropagation(); openTaskContextMenu(event, '{{ $t->id }}', '{{ $project->id }}', '{{ addslashes($t->title) }}')">
                                        
                                        <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 6px;">
                                            <div style="display: flex; gap: 4px; align-items: center; flex-wrap: wrap;">
                                                <span class="badge-pill badge-neutral" style="font-family: monospace; font-size: 10px; font-weight: 800;">
                                                    #{{ $t->task_number }}
                                                </span>
                                                <span class="badge-pill" style="font-size: 9px; font-weight: 700; color: var(--brand-forest); background: rgba(79, 155, 95, 0.12);">
                                                    📁 {{ $project->code }}
                                                </span>
                                                @if($t->checklistItems && $t->checklistItems->count() > 0)
                                                    <span class="badge-pill" style="font-size: 9px; background: rgba(79, 155, 95, 0.15); color: #4F9B5F;" title="{{ __('Checklist Progress') }}">
                                                        ⊞ {{ $t->checklistItems->where('is_completed', true)->count() }}/{{ $t->checklistItems->count() }}
                                                    </span>
                                                @endif
                                            </div>

                                            <div style="display: flex; align-items: center; gap: 4px;">
                                                <span class="badge-pill {{ $t->priority === 'urgent' ? 'badge-danger' : ($t->priority === 'high' ? 'badge-gold' : 'badge-neutral') }}" style="font-size: 9px; font-weight: 800;">
                                                    {{ $t->priority === 'urgent' ? '🔥 ' . __('Urgent') : ($t->priority === 'high' ? '⚡ ' . __('High') : ($t->priority === 'medium' ? '⚖️ ' . __('Med') : '🌱 ' . ucfirst($t->priority))) }}
                                                </span>
                                                <button onclick="event.stopPropagation(); openTaskContextMenu(event, '{{ $t->id }}', '{{ $project->id }}', '{{ addslashes($t->title) }}')" class="tactile-btn btn-secondary" style="padding: 2px 6px; font-size: 10px; line-height: 1;" title="{{ __('Task Actions') }}">
                                                    •••
                                                </button>
                                            </div>
                                        </div>

                                        <div style="font-size: 13px; font-weight: 800; color: var(--text-primary); margin-bottom: 8px; line-height: 1.4; {{ $t->status === 'done' ? 'text-decoration: line-through; opacity: 0.6;' : '' }}">
                                            {{ $t->title }}
                                        </div>

                                        <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 10px; font-size: 11px; color: var(--text-muted);">
                                            <div style="display: flex; align-items: center; gap: 6px;">
                                                <div style="width: 22px; height: 22px; border-radius: 50%; background: var(--accent-gradient); color: white; display: flex; align-items: center; justify-content: center; font-size: 9px; font-weight: 800;">
                                                    {{ strtoupper(substr($t->assignee->name ?? 'U', 0, 2)) }}
                                                </div>
                                                <span style="font-weight: 700; color: var(--text-secondary);">{{ $t->assignee ? explode(' ', $t->assignee->name)[0] : __('Unassigned') }}</span>
                                            </div>

                                            <div style="display: flex; align-items: center; gap: 6px;">
                                                <span style="font-family: monospace; font-weight: 800; font-size: 10px; color: var(--brand-forest);">
                                                    ⏱️ {{ round($t->logged_hours ?? $t->actual_hours ?? 0, 1) }}h{{ $t->estimated_hours ? ' / ' . $t->estimated_hours . 'h' : '' }}
                                                </span>
                                                <button onclick="event.stopPropagation(); startHubTaskTimerDirect('{{ $project->id }}', '{{ $t->id }}', '{{ addslashes($t->title) }}', '{{ addslashes($project->name) }}')" class="tactile-btn" style="background: rgba(79, 155, 95, 0.15); color: var(--brand-forest); border: 1px solid rgba(79, 155, 95, 0.3); padding: 3px 8px; font-size: 10px;" title="{{ __('Start Timer') }}">
                                                    ▶
                                                </button>
                                            </div>
                                        </div>

                                        <!-- Quick status shift selector -->
                                        <div style="margin-top: 10px; padding-top: 8px; border-top: 1px dashed var(--border-color); display: flex; justify-content: space-between; align-items: center;" onclick="event.stopPropagation();">
                                            <select onchange="updateTaskStatusFast('{{ $t->id }}', this.value)" {{ $canEditThisTask ? '' : 'disabled' }} title="{{ $canEditThisTask ? __('Change Task Status') : __('Only assigned member or manager can edit') }}" style="background: var(--bg-surface-subtle); border: 1px solid var(--border-color); color: var(--text-secondary); font-size: 10px; font-weight: 700; border-radius: 6px; padding: 2px 6px; outline: none; cursor: {{ $canEditThisTask ? 'pointer' : 'not-allowed' }}; {{ $canEditThisTask ? '' : 'opacity: 0.7;' }}">
                                                @foreach($columns as $optKey => $optMeta)
                                                    <option value="{{ $optKey }}" {{ $t->status === $optKey ? 'selected' : '' }}>{{ $optMeta['title'] }}</option>
                                                @endforeach
                                            </select>
                                            <span style="font-size: 10px; color: var(--text-muted); {{ $t->due_date && $t->due_date->isPast() && $t->status !== 'done' ? 'color: #D96B5F; font-weight: 800;' : '' }}">
                                                📅 {{ $t->due_date ? $t->due_date->format('M d') : '—' }}
                                            </span>
                                        </div>
                                    </div>
                                @empty
                                    <div style="text-align: center; padding: 24px 10px; color: var(--text-muted); font-size: 11px; border: 1px dashed var(--border-color); border-radius: var(--radius-md);">
                                        {{ __('No tasks in this lane.') }}
                                    </div>
                                @endforelse
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- TAB 2: TASKS MATRIX & TABLE -->
            <div id="hub-section-tasks" class="hub-section-content" style="display: none;">
                <div class="hub-card" style="padding: 0; overflow: hidden;">
                    <div style="padding: 18px 24px; border-bottom: 1px solid var(--border-color); display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px;">
                        <div>
                            <h3 style="font-size: 16px; font-weight: 900; color: var(--text-primary);">📋 {{ __('Project Task Inventory & Sprints') }} ({{ $tasks->count() }})</h3>
                            <p style="font-size: 12px; color: var(--text-muted); margin-top: 2px;">{{ __('Filter and inspect all deliverable tasks, checklist completion, and predecessor dependencies.') }}</p>
                        </div>
                        <button onclick="openNewTaskModal()" class="tactile-btn btn-primary" style="padding: 8px 16px; font-size: 12px;">
                            + {{ __('Create Task') }}
                        </button>
                    </div>

                    <div style="overflow-x: auto;">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>{{ __('Task Title') }}</th>
                                    <th>{{ __('Status') }}</th>
                                    <th>{{ __('Priority') }}</th>
                                    <th>{{ __('Assignee') }}</th>
                                    <th>{{ __('Estimated / Actual') }}</th>
                                    <th>{{ __('Checklist') }}</th>
                                    <th>{{ __('Due Date') }}</th>
                                    <th>{{ __('Actions') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($tasks as $t)
                                    <tr onclick="openTaskInspector('{{ $t->id }}')" 
                                        oncontextmenu="event.preventDefault(); event.stopPropagation(); openTaskContextMenu(event, '{{ $t->id }}', '{{ $project->id }}', '{{ addslashes($t->title) }}')"
                                        style="cursor: pointer;">
                                        <td style="font-family: monospace; font-weight: 900; color: var(--text-muted);">
                                            #{{ $t->task_number }}
                                        </td>
                                        <td>
                                            <div style="font-weight: 800; color: var(--text-primary); font-size: 13px; display: flex; align-items: center; gap: 6px;">
                                                <span>{{ $t->title }}</span>
                                                @if($t->checklistItems && $t->checklistItems->count() > 0)
                                                    <span class="badge-pill" style="font-size: 9px; background: rgba(79, 155, 95, 0.15); color: #4F9B5F;">⊞ {{ $t->checklistItems->where('is_completed', true)->count() }}/{{ $t->checklistItems->count() }}</span>
                                                @endif
                                            </div>
                                            @if($t->description)
                                                <div style="font-size: 11px; color: var(--text-muted);">{{ Str::limit($t->description, 50) }}</div>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="badge-pill {{ $t->status === 'done' ? 'badge-green' : ($t->status === 'in_progress' ? 'badge-green' : 'badge-gold') }}">
                                                {{ ucfirst(str_replace('_', ' ', $t->status)) }}
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge-pill {{ $t->priority === 'urgent' ? 'badge-danger' : ($t->priority === 'high' ? 'badge-gold' : 'badge-neutral') }}">
                                                {{ $t->priority === 'urgent' ? '🚩 ' . __('Urgent') : ($t->priority === 'high' ? '⚡ ' . __('High') : ucfirst($t->priority)) }}
                                            </span>
                                        </td>
                                        <td>
                                            <div style="display: flex; align-items: center; gap: 6px;">
                                                <div style="width: 22px; height: 22px; border-radius: 50%; background: var(--accent-gradient); color: white; display: flex; align-items: center; justify-content: center; font-size: 9px; font-weight: 800;">
                                                    {{ strtoupper(substr($t->assignee->name ?? 'U', 0, 1)) }}
                                                </div>
                                                <span style="font-weight: 700;">{{ $t->assignee->name ?? __('Unassigned') }}</span>
                                            </div>
                                        </td>
                                        <td style="font-family: monospace; font-weight: 800;">
                                            <span style="color: var(--brand-forest);">{{ $t->actual_hours ?? 0 }}h</span>
                                            <span style="color: var(--text-muted);">/ {{ $t->estimated_hours ?? 0 }}h</span>
                                        </td>
                                        <td>
                                            @php
                                                $checks = $t->checklistItems;
                                                $checkDone = $checks->where('is_completed', true)->count();
                                                $checkTotal = $checks->count();
                                            @endphp
                                            @if($checkTotal > 0)
                                                <span class="badge-pill badge-neutral" style="font-size: 10px;">☑️ {{ $checkDone }}/{{ $checkTotal }}</span>
                                            @else
                                                <span style="font-size: 11px; color: var(--text-muted);">—</span>
                                            @endif
                                        </td>
                                        <td>
                                            <span style="font-size: 12px; font-weight: 700; color: {{ $t->due_date && $t->due_date->isPast() && $t->status !== 'done' ? 'var(--status-danger)' : 'var(--text-secondary)' }};">
                                                {{ $t->due_date ? $t->due_date->format('M d, Y') : '—' }}
                                            </span>
                                        </td>
                                        <td onclick="event.stopPropagation();">
                                            <div style="display: flex; gap: 6px;">
                                                <button onclick="openTaskInspector('{{ $t->id }}')" class="tactile-btn btn-secondary" style="padding: 4px 10px; font-size: 11px;">
                                                    🔍 {{ __('Inspect') }}
                                                </button>
                                                <button onclick="openTaskContextMenu(event, '{{ $t->id }}', '{{ $project->id }}', '{{ addslashes($t->title) }}')" class="tactile-btn btn-secondary" style="padding: 4px 8px; font-size: 11px;" title="{{ __('More Actions') }}">
                                                    •••
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="9" style="text-align: center; color: var(--text-muted); padding: 40px;">
                                            <div style="font-size: 32px; margin-bottom: 8px;">📋</div>
                                            {{ __('No tasks created in this project yet.') }}
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- TAB 3: TIME TRACKING LOG -->
            <div id="hub-section-timelog" class="hub-section-content" style="display: none;">
                <div class="hub-card" style="padding: 0; overflow: hidden;">
                    <div style="padding: 18px 24px; border-bottom: 1px solid var(--border-color); display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px;">
                        <div>
                            <h3 style="font-size: 16px; font-weight: 900; color: var(--text-primary);">⏱️ {{ __('Work Sessions & Time Tracking Log') }} ({{ $project->timeEntries->count() }})</h3>
                            <p style="font-size: 12px; color: var(--text-muted); margin-top: 2px;">{{ __('Chronological presence log of all billable and non-billable labor recorded on this project.') }}</p>
                        </div>
                        <button onclick="openManualTimeModal()" class="tactile-btn btn-primary" style="padding: 8px 16px; font-size: 12px;">
                            + {{ __('Log Manual Time Entry') }}
                        </button>
                    </div>

                    <div style="overflow-x: auto;">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>{{ __('Date') }}</th>
                                    <th>{{ __('Employee') }}</th>
                                    <th>{{ __('Task') }}</th>
                                    <th>{{ __('Duration') }}</th>
                                    <th>{{ __('Description') }}</th>
                                    <th>{{ __('Billable') }}</th>
                                    <th>{{ __('Status') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($project->timeEntries as $te)
                                <tr>
                                    <td style="font-size: 12px; font-weight: 700; color: var(--text-secondary);">
                                        {{ $te->started_at ? $te->started_at->format('M d, Y H:i') : '—' }}
                                    </td>
                                    <td>
                                        <div style="display: flex; align-items: center; gap: 6px;">
                                            <div style="width: 22px; height: 22px; border-radius: 50%; background: var(--accent-gradient); color: white; display: flex; align-items: center; justify-content: center; font-size: 9px; font-weight: 800;">
                                                {{ strtoupper(substr($te->user->name ?? 'U', 0, 1)) }}
                                            </div>
                                            <strong style="color: var(--text-primary);">{{ $te->user->name ?? 'Member' }}</strong>
                                        </div>
                                    </td>
                                    <td>
                                        <span style="font-weight: 700; color: var(--text-primary);">
                                            {{ $te->task ? '#' . $te->task->task_number . ' ' . $te->task->title : __('General Project Work') }}
                                        </span>
                                    </td>
                                    <td style="font-family: monospace; font-weight: 900; color: var(--brand-forest); font-size: 14px;">
                                        {{ number_format($te->duration_seconds / 3600, 2) }}h
                                    </td>
                                    <td style="font-size: 12px; color: var(--text-secondary);">
                                        {{ $te->description ?? __('Work session') }}
                                    </td>
                                    <td>
                                        <span class="badge-pill {{ $te->is_billable ? 'badge-green' : 'badge-neutral' }}">
                                            {{ $te->is_billable ? __('Billable') : __('Non-Billable') }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge-pill {{ $te->status === 'approved' ? 'badge-green' : ($te->status === 'submitted' ? 'badge-gold' : 'badge-neutral') }}">
                                            {{ ucfirst($te->status) }}
                                        </span>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="7" style="text-align: center; color: var(--text-muted); padding: 40px;">
                                        <div style="font-size: 32px; margin-bottom: 8px;">⏱️</div>
                                        {{ __('No time entries logged on this project yet.') }}
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- TAB 4: SCHEDULED MEETINGS -->
            <div id="hub-section-meetings" class="hub-section-content" style="display: none;">
                <div class="hub-card" style="padding: 0; overflow: hidden;">
                    <div style="padding: 18px 24px; border-bottom: 1px solid var(--border-color); display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px;">
                        <div>
                            <h3 style="font-size: 16px; font-weight: 900; color: var(--text-primary);">📅 {{ __('Project Video Meetings & Standups') }} ({{ $projectMeetings->count() }})</h3>
                            <p style="font-size: 12px; color: var(--text-muted); margin-top: 2px;">{{ __('Scheduled collaboration rooms with instant automated team invitations and chime audio alerts.') }}</p>
                        </div>
                        <button onclick="openScheduleProjectMeetingModal()" class="tactile-btn btn-primary" style="padding: 8px 16px; font-size: 12px;">
                            + {{ __('Schedule Project Meeting') }}
                        </button>
                    </div>

                    <div style="overflow-x: auto;">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>{{ __('Meeting Title') }}</th>
                                    <th>{{ __('Date & Time') }}</th>
                                    <th>{{ __('Duration') }}</th>
                                    <th>{{ __('Room') }}</th>
                                    <th>{{ __('Host') }}</th>
                                    <th>{{ __('Attendees') }}</th>
                                    <th>{{ __('Status') }}</th>
                                    <th>{{ __('Action') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($projectMeetings as $m)
                                @php
                                    $isLive = $m->status === 'active';
                                    $mParts = $m->participants->take(4);
                                    $moreParts = max(0, $m->participants->count() - 4);
                                @endphp
                                <tr>
                                    <td>
                                        <div style="font-weight: 800; color: var(--text-primary); font-size: 13px;">{{ $m->title }}</div>
                                        @if($m->description)
                                            <div style="font-size: 11px; color: var(--text-muted);">{{ Str::limit($m->description, 40) }}</div>
                                        @endif
                                    </td>
                                    <td>
                                        <div style="font-weight: 700; color: var(--text-primary); font-size: 12px;">
                                            {{ $m->scheduled_at ? $m->scheduled_at->format('M d, Y') : __('Instant') }}
                                        </div>
                                        <div style="font-size: 11px; color: var(--text-muted);">
                                            {{ $m->scheduled_at ? $m->scheduled_at->format('h:i A') : $m->created_at->format('h:i A') }}
                                        </div>
                                    </td>
                                    <td style="font-size: 12px; font-weight: 700; color: var(--text-secondary);">
                                        {{ $m->duration_minutes ?? 30 }} {{ __('Minutes') }}
                                    </td>
                                    <td>
                                        <strong style="color: var(--brand-forest); font-size: 12px;">🚪 {{ $m->room->name ?? 'Meeting Room' }}</strong>
                                    </td>
                                    <td>
                                        <span style="font-weight: 700; font-size: 12px;">{{ $m->creator->name ?? 'Admin' }}</span>
                                    </td>
                                    <td>
                                        <div style="display: flex; align-items: center;">
                                            @foreach($mParts as $p)
                                                <div style="width: 22px; height: 22px; border-radius: 50%; background: var(--accent-gradient); color: white; font-size: 9px; font-weight: 800; display: flex; align-items: center; justify-content: center; border: 2px solid var(--bg-surface); margin-inline-start: -6px;" title="{{ $p->user->name ?? 'Attendee' }}">
                                                    {{ strtoupper(substr($p->user->name ?? 'A', 0, 1)) }}
                                                </div>
                                            @endforeach
                                            @if($moreParts > 0)
                                                <div style="width: 22px; height: 22px; border-radius: 50%; background: var(--brand-forest); color: white; font-size: 9px; font-weight: 800; display: flex; align-items: center; justify-content: center; border: 2px solid var(--bg-surface); margin-inline-start: -6px;">
                                                    +{{ $moreParts }}
                                                </div>
                                            @endif
                                        </div>
                                    </td>
                                    <td>
                                        @if($isLive)
                                            <span class="badge-pill badge-danger" style="animation: pulse 1.5s infinite;">🔴 LIVE</span>
                                        @elseif($m->status === 'scheduled')
                                            <span class="badge-pill badge-green">📅 {{ __('Scheduled') }}</span>
                                        @elseif($m->status === 'ended')
                                            <span class="badge-pill badge-neutral">{{ __('Completed') }}</span>
                                        @else
                                            <span class="badge-pill badge-neutral">{{ ucfirst($m->status) }}</span>
                                        @endif
                                    </td>
                                    <td>
                                        <a href="{{ route('office') }}" class="tactile-btn btn-primary" style="padding: 5px 12px; font-size: 11px; text-decoration: none;">
                                            🚀 {{ __('Join') }}
                                        </a>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="8" style="text-align: center; color: var(--text-muted); padding: 40px;">
                                        <div style="font-size: 32px; margin-bottom: 8px;">📅</div>
                                        {{ __('No meetings scheduled for this project yet.') }}
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- TAB 5: PROJECT TEAM -->
            <div id="hub-section-team" class="hub-section-content" style="display: none;">
                <div class="hub-card" style="padding: 0; overflow: hidden;">
                    <div style="padding: 18px 24px; border-bottom: 1px solid var(--border-color);">
                        <h3 style="font-size: 16px; font-weight: 900; color: var(--text-primary);">👥 {{ __('Project Team Roster & Allocation') }} ({{ $project->members->count() }})</h3>
                        <p style="font-size: 12px; color: var(--text-muted); margin-top: 2px;">{{ __('Assigned collaborators and their contributed hours and assigned task loads.') }}</p>
                    </div>

                    <div style="overflow-x: auto;">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>{{ __('Team Member') }}</th>
                                    <th>{{ __('Project Role') }}</th>
                                    <th>{{ __('Assigned Tasks') }}</th>
                                    <th>{{ __('Hours Contributed') }}</th>
                                    <th>{{ __('Billing Rate') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($project->members as $pm)
                                @php
                                    $memberTasksCount = $tasks->where('assignee_id', $pm->user_id)->count();
                                    $memberHours = $project->timeEntries->where('user_id', $pm->user_id)->sum('duration_seconds') / 3600;
                                @endphp
                                <tr>
                                    <td>
                                        <div style="display: flex; align-items: center; gap: 10px;">
                                            <div style="width: 32px; height: 32px; border-radius: 50%; background: var(--accent-gradient); color: white; display: flex; align-items: center; justify-content: center; font-size: 12px; font-weight: 900;">
                                                {{ strtoupper(substr($pm->user->name ?? 'U', 0, 2)) }}
                                            </div>
                                            <div>
                                                <div style="font-weight: 800; color: var(--text-primary);">{{ $pm->user->name ?? 'Member' }}</div>
                                                <div style="font-size: 11px; color: var(--text-muted);">{{ $pm->user->email ?? '' }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge-pill badge-neutral">{{ ucfirst($pm->role ?? 'Member') }}</span>
                                    </td>
                                    <td style="font-weight: 800;">
                                        {{ $memberTasksCount }} {{ __('Tasks') }}
                                    </td>
                                    <td style="font-family: monospace; font-weight: 900; color: var(--brand-forest); font-size: 14px;">
                                        {{ number_format($memberHours, 1) }}h
                                    </td>
                                    <td style="font-family: monospace; font-weight: 700; color: var(--text-secondary);">
                                        ${{ number_format($pm->hourly_rate ?? 50, 2) }}/h
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" style="text-align: center; color: var(--text-muted); padding: 40px;">
                                        <div style="font-size: 32px; margin-bottom: 8px;">👥</div>
                                        {{ __('No members assigned to this project yet.') }}
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- TAB: ROADMAP & MILESTONES -->
            <div id="hub-section-milestones" class="hub-section-content" style="display: none;">
                <div class="hub-card">
                    <h3 style="font-size: 16px; font-weight: 900; color: var(--text-primary); margin-bottom: 16px;">🚩 {{ __('Phases & Delivery Milestones') }}</h3>
                    <div style="display: flex; flex-direction: column; gap: 14px;">
                        @forelse($project->milestones as $ms)
                        <div style="background: var(--bg-surface-subtle); border: 1px solid var(--border-color); border-radius: var(--radius-lg); padding: 18px; display: flex; justify-content: space-between; align-items: center;">
                            <div>
                                <div style="font-size: 15px; font-weight: 900; color: var(--text-primary); margin-bottom: 4px;">
                                    🚩 {{ $ms->title }}
                                </div>
                                <div style="font-size: 12px; color: var(--text-muted);">
                                    {{ $ms->description ?? __('Key milestone deliverable.') }}
                                </div>
                            </div>
                            <div style="text-align: end;">
                                <span class="badge-pill {{ $ms->status === 'completed' ? 'badge-green' : 'badge-gold' }}">
                                    {{ ucfirst($ms->status ?? 'pending') }}
                                </span>
                                <div style="font-size: 11px; color: var(--text-muted); margin-top: 4px;">
                                    📅 {{ $ms->due_date ? $ms->due_date->format('M d, Y') : __('No target date') }}
                                </div>
                            </div>
                        </div>
                        @empty
                        <div style="text-align: center; color: var(--text-muted); padding: 40px;">
                            <div style="font-size: 32px; margin-bottom: 8px;">🎯</div>
                            {{ __('No milestones defined for this project yet.') }}
                        </div>
                        @endforelse
                    </div>
                </div>
            </div>

            <!-- TAB: GANTT & TIMELINE MATRIX (CLICKUP GANTT) -->
            <div id="hub-section-gantt" class="hub-section-content" style="display: none;">
                <div class="hub-card">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 18px; flex-wrap: wrap; gap: 10px;">
                        <div>
                            <h3 style="font-size: 16px; font-weight: 900; color: var(--text-primary);">📊 {{ __('Interactive Gantt & Schedule Timeline') }}</h3>
                            <p style="font-size: 12px; color: var(--text-muted); margin-top: 2px;">{{ __('Visual task schedules, critical paths, and predecessor dependencies.') }}</p>
                        </div>
                        <div style="display: flex; gap: 8px;">
                            <button onclick="zoomGantt('days')" class="tactile-btn btn-secondary" style="padding: 6px 12px; font-size: 11px;">{{ __('Days') }}</button>
                            <button onclick="zoomGantt('weeks')" class="tactile-btn btn-secondary active" style="padding: 6px 12px; font-size: 11px;">{{ __('Weeks') }}</button>
                        </div>
                    </div>

                    <div style="overflow-x: auto; background: var(--bg-surface-subtle); border-radius: var(--radius-lg); border: 1px solid var(--border-color); padding: 20px;">
                        <div style="display: flex; flex-direction: column; gap: 12px; min-width: 750px;">
                            @forelse($ganttTasks as $gt)
                            <div style="display: grid; grid-template-columns: 240px 1fr; align-items: center; gap: 16px; background: var(--bg-surface); padding: 10px 14px; border-radius: var(--radius-md); border: 1px solid var(--border-color);">
                                <div>
                                    <div style="font-size: 13px; font-weight: 800; color: var(--text-primary); white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                        {{ $gt['title'] }}
                                    </div>
                                    <div style="font-size: 10px; color: var(--text-muted); display: flex; gap: 6px;">
                                        <span>👤 {{ $gt['assignee'] }}</span>
                                        <span>•</span>
                                        <span>📅 {{ $gt['due_date'] }}</span>
                                    </div>
                                </div>
                                <div style="position: relative; background: var(--bg-surface-subtle); height: 26px; border-radius: 8px; overflow: hidden; border: 1px solid var(--border-color);">
                                    <div style="position: absolute; inset-inline-start: 10%; width: {{ max(15, min(80, $gt['progress'])) }}%; height: 100%; background: linear-gradient(90deg, #42774C 0%, #2A5D37 100%); border-radius: 6px; display: flex; align-items: center; justify-content: center; color: white; font-size: 10px; font-weight: 900;">
                                        {{ $gt['status'] }}
                                    </div>
                                </div>
                            </div>
                            @empty
                            <div style="text-align: center; color: var(--text-muted); padding: 30px;">
                                {{ __('No tasks available for timeline visualization.') }}
                            </div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>

            <!-- TAB: WORKLOAD MATRIX (CLICKUP WORKLOAD) -->
            <div id="hub-section-workload" class="hub-section-content" style="display: none;">
                <div class="hub-card" style="padding: 0; overflow: hidden;">
                    <div style="padding: 18px 24px; border-bottom: 1px solid var(--border-color); display: flex; justify-content: space-between; align-items: center;">
                        <div>
                            <h3 style="font-size: 16px; font-weight: 900; color: var(--text-primary);">📈 {{ __('Team Workload & Capacity Matrix') }}</h3>
                            <p style="font-size: 12px; color: var(--text-muted); margin-top: 2px;">{{ __('Prevent employee burnout by balancing weekly hours against allocated tasks.') }}</p>
                        </div>
                    </div>

                    <div style="overflow-x: auto;">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>{{ __('Member') }}</th>
                                    <th>{{ __('Assigned Tasks') }}</th>
                                    <th>{{ __('Allocated Hours') }}</th>
                                    <th>{{ __('Weekly Capacity') }}</th>
                                    <th>{{ __('Utilization') }}</th>
                                    <th>{{ __('Workload Status') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($workloadMatrix as $wm)
                                <tr>
                                    <td>
                                        <div style="display: flex; align-items: center; gap: 10px;">
                                            <div style="width: 32px; height: 32px; border-radius: 50%; background: var(--accent-gradient); color: white; display: flex; align-items: center; justify-content: center; font-size: 12px; font-weight: 900;">
                                                {{ strtoupper(substr($wm['member']->user->name ?? 'U', 0, 2)) }}
                                            </div>
                                            <div>
                                                <div style="font-weight: 800; color: var(--text-primary);">{{ $wm['member']->user->name }}</div>
                                                <div style="font-size: 11px; color: var(--text-muted);">{{ $wm['member']->role->name ?? 'Staff' }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td style="font-weight: 800;">{{ $wm['tasks_count'] }} {{ __('Tasks') }}</td>
                                    <td style="font-weight: 800; color: var(--brand-forest);">{{ number_format($wm['assigned_hours'], 1) }}h</td>
                                    <td style="color: var(--text-muted);">{{ $wm['capacity'] }}h / {{ __('week') }}</td>
                                    <td>
                                        <div style="display: flex; align-items: center; gap: 8px;">
                                            <div style="width: 80px; height: 8px; background: var(--bg-surface-subtle); border-radius: 9999px; overflow: hidden; border: 1px solid var(--border-color);">
                                                <div style="width: {{ min(100, $wm['utilization']) }}%; height: 100%; background: {{ $wm['status'] === 'overloaded' ? 'var(--status-danger)' : ($wm['status'] === 'optimal' ? 'var(--brand-forest)' : 'var(--brand-gold)') }};"></div>
                                            </div>
                                            <span style="font-size: 11px; font-weight: 800;">{{ $wm['utilization'] }}%</span>
                                        </div>
                                    </td>
                                    <td>
                                        @if($wm['status'] === 'overloaded')
                                            <span class="badge-pill" style="background: #FEE2E2; color: #B91C1C; border: 1px solid #FECACA;">⚠️ {{ __('Overloaded') }}</span>
                                        @elseif($wm['status'] === 'optimal')
                                            <span class="badge-pill badge-green">⚡ {{ __('Optimal') }}</span>
                                        @else
                                            <span class="badge-pill badge-neutral">🍃 {{ __('Available') }}</span>
                                        @endif
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="6" style="text-align: center; color: var(--text-muted); padding: 30px;">
                                        {{ __('No workload data available.') }}
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- TAB: DOCS & WIKI (CLICKUP DOCS) -->
            <div id="hub-section-docs" class="hub-section-content" style="display: none;">
                <div class="hub-card">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; flex-wrap: wrap; gap: 10px;">
                        <div>
                            <h3 style="font-size: 16px; font-weight: 900; color: var(--text-primary);">📚 {{ __('Project Docs, Specs & Knowledge Wiki') }}</h3>
                            <p style="font-size: 12px; color: var(--text-muted); margin-top: 2px;">{{ __('Centralized living documents, specifications, and team meeting minutes.') }}</p>
                        </div>
                        <button onclick="openNewDocModal()" class="tactile-btn btn-primary" style="padding: 8px 16px; font-size: 12px;">
                            + {{ __('Create Document') }}
                        </button>
                    </div>

                    <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 16px;">
                        @forelse($project->documents as $doc)
                        <div style="background: var(--bg-surface-subtle); border: 1px solid var(--border-color); border-radius: var(--radius-lg); padding: 18px; box-shadow: var(--shadow-soft-3d); transition: all 0.2s;" onmouseover="this.style.transform='translateY(-2px)'" onmouseout="this.style.transform='translateY(0)'">
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                                <span style="font-size: 22px;">{{ $doc->icon ?? '📄' }}</span>
                                <span class="badge-pill badge-neutral">v{{ $doc->version }}</span>
                            </div>
                            <h4 style="font-size: 15px; font-weight: 900; color: var(--text-primary); margin-bottom: 6px;">{{ $doc->title }}</h4>
                            <p style="font-size: 12px; color: var(--text-muted); margin-bottom: 12px; max-height: 48px; overflow: hidden; text-overflow: ellipsis;">
                                {{ Str::limit(strip_tags($doc->content), 90) ?: __('No preview content available.') }}
                            </p>
                            <div style="font-size: 11px; color: var(--text-muted); display: flex; justify-content: space-between; align-items: center; border-top: 1px solid var(--border-color); padding-top: 8px;">
                                <span>✍️ {{ $doc->author->name ?? 'Team' }}</span>
                                <span>{{ $doc->updated_at->diffForHumans() }}</span>
                            </div>
                        </div>
                        @empty
                        <div style="grid-column: 1 / -1; text-align: center; color: var(--text-muted); padding: 40px;">
                            <div style="font-size: 32px; margin-bottom: 8px;">📚</div>
                            {{ __('No documents created yet in this project.') }}
                            <div style="margin-top: 10px;">
                                <button onclick="openNewDocModal()" class="tactile-btn btn-primary" style="padding: 6px 14px; font-size: 12px;">+ {{ __('Create First Doc') }}</button>
                            </div>
                        </div>
                        @endforelse
                    </div>
                </div>
            </div>

            <!-- TAB: GOALS & TARGETS (CLICKUP GOALS) -->
            <div id="hub-section-goals" class="hub-section-content" style="display: none;">
                <div class="hub-card">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; flex-wrap: wrap; gap: 10px;">
                        <div>
                            <h3 style="font-size: 16px; font-weight: 900; color: var(--text-primary);">🎯 {{ __('Strategic Goals & Measurable Targets') }}</h3>
                            <p style="font-size: 12px; color: var(--text-muted); margin-top: 2px;">{{ __('Track milestone deliverables, revenue targets, and completion indicators.') }}</p>
                        </div>
                        <button onclick="openNewGoalModal()" class="tactile-btn btn-primary" style="padding: 8px 16px; font-size: 12px;">
                            + {{ __('New Goal') }}
                        </button>
                    </div>

                    <div style="display: flex; flex-direction: column; gap: 16px;">
                        @forelse($project->goals as $goal)
                        <div style="background: var(--bg-surface-subtle); border: 1px solid var(--border-color); border-radius: var(--radius-lg); padding: 20px; box-shadow: var(--shadow-soft-3d);">
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px; flex-wrap: wrap; gap: 10px;">
                                <div>
                                    <div style="display: flex; align-items: center; gap: 8px;">
                                        <span style="font-size: 18px;">🎯</span>
                                        <h4 style="font-size: 16px; font-weight: 900; color: var(--text-primary);">{{ $goal->name }}</h4>
                                    </div>
                                    <div style="font-size: 12px; color: var(--text-muted); margin-top: 2px;">{{ $goal->description }}</div>
                                </div>
                                <div style="display: flex; align-items: center; gap: 10px;">
                                    <span class="badge-pill {{ $goal->status === 'completed' ? 'badge-green' : 'badge-gold' }}">{{ ucfirst($goal->status) }}</span>
                                    <span style="font-size: 20px; font-weight: 900; color: var(--brand-forest);">{{ $goal->progress_percentage }}%</span>
                                </div>
                            </div>

                            <div style="width: 100%; height: 8px; background: var(--bg-surface); border-radius: 9999px; overflow: hidden; margin-bottom: 14px; border: 1px solid var(--border-color);">
                                <div style="width: {{ $goal->progress_percentage }}%; height: 100%; background: linear-gradient(90deg, #42774C 0%, #2A5D37 100%);"></div>
                            </div>

                            <div style="display: flex; flex-direction: column; gap: 8px;">
                                @foreach($goal->targets as $target)
                                <div style="display: flex; justify-content: space-between; align-items: center; font-size: 12px; background: var(--bg-surface); padding: 8px 12px; border-radius: var(--radius-md); border: 1px solid var(--border-color);">
                                    <span style="font-weight: 700; color: var(--text-primary);">{{ $target->title }}</span>
                                    <span style="font-weight: 800; color: var(--brand-forest); font-family: monospace;">
                                        {{ $target->current_value }} / {{ $target->target_value }} {{ $target->unit }}
                                    </span>
                                </div>
                                @endforeach
                            </div>
                        </div>
                        @empty
                        <div style="text-align: center; color: var(--text-muted); padding: 40px;">
                            <div style="font-size: 32px; margin-bottom: 8px;">🎯</div>
                            {{ __('No strategic goals set for this project yet.') }}
                            <div style="margin-top: 10px;">
                                <button onclick="openNewGoalModal()" class="tactile-btn btn-primary" style="padding: 6px 14px; font-size: 12px;">+ {{ __('Create First Goal') }}</button>
                            </div>
                        </div>
                        @endforelse
                    </div>
                </div>
            </div>

        </main>
    </div>

    <!-- ── 7. MODALS ── -->

    <!-- Modal: New Task -->
    <div id="new-task-modal" class="modal-overlay">
        <div class="modal-card">
            <div class="modal-header">
                <h3 style="font-size: 18px; font-weight: 900; color: var(--text-primary);">📝 {{ __('Create Task in') }} {{ $project->name }}</h3>
                <button onclick="closeNewTaskModal()" class="modal-close">✕</button>
            </div>
            <form id="new-task-form" onsubmit="createProjectTaskSubmit(event)" style="display: flex; flex-direction: column; gap: 14px;">
                <input type="hidden" name="project_id" value="{{ $project->id }}">
                <div>
                    <label style="display: block; font-size: 12px; font-weight: 700; color: var(--text-secondary); margin-bottom: 6px;">{{ __('Task Title') }} *</label>
                    <input type="text" name="title" required placeholder="e.g. Implement payment gateway webhook" class="form-input">
                </div>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                    <div>
                        <label style="display: block; font-size: 12px; font-weight: 700; color: var(--text-secondary); margin-bottom: 6px;">{{ __('Assignee') }}</label>
                        <select name="assignee_id" class="form-input">
                            <option value="">— {{ __('Unassigned') }} —</option>
                            @foreach($allMembers as $m)
                                <option value="{{ $m->user_id }}">{{ $m->user->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label style="display: block; font-size: 12px; font-weight: 700; color: var(--text-secondary); margin-bottom: 6px;">{{ __('Priority') }}</label>
                        <select name="priority" class="form-input">
                            <option value="medium">{{ __('Medium') }}</option>
                            <option value="low">{{ __('Low') }}</option>
                            <option value="high">{{ __('High') }}</option>
                            <option value="urgent">{{ __('Urgent') }}</option>
                        </select>
                    </div>
                </div>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                    <div>
                        <label style="display: block; font-size: 12px; font-weight: 700; color: var(--text-secondary); margin-bottom: 6px;">{{ __('Estimated Hours') }}</label>
                        <input type="number" step="0.5" name="estimated_hours" placeholder="4.0" class="form-input">
                    </div>
                    <div>
                        <label style="display: block; font-size: 12px; font-weight: 700; color: var(--text-secondary); margin-bottom: 6px;">{{ __('Due Date') }}</label>
                        <input type="date" name="due_date" class="form-input">
                    </div>
                </div>
                <div>
                    <label style="display: block; font-size: 12px; font-weight: 700; color: var(--text-secondary); margin-bottom: 6px;">{{ __('Description / Specifications') }}</label>
                    <textarea name="description" rows="3" placeholder="Task requirements..." class="form-input" style="resize: vertical;"></textarea>
                </div>
                <button type="submit" class="tactile-btn btn-primary" style="margin-top: 8px; padding: 12px; font-size: 14px;">
                    💾 {{ __('Create Task') }}
                </button>
            </form>
        </div>
    </div>

    <!-- Modal: Log Time -->
    <div id="manual-time-modal" class="modal-overlay">
        <div class="modal-card">
            <div class="modal-header">
                <h3 style="font-size: 18px; font-weight: 900; color: var(--text-primary);">✍️ {{ __('Log Manual Time Entry') }}</h3>
                <button onclick="closeManualTimeModal()" class="modal-close">✕</button>
            </div>
            <form id="manual-time-form" onsubmit="logProjectTimeSubmit(event)" style="display: flex; flex-direction: column; gap: 14px;">
                <input type="hidden" name="project_id" value="{{ $project->id }}">
                <div>
                    <label style="display: block; font-size: 12px; font-weight: 700; color: var(--text-secondary); margin-bottom: 6px;">{{ __('Associated Task') }}</label>
                    <select name="task_id" class="form-input">
                        <option value="">— {{ __('General Project Work') }} —</option>
                        @foreach($tasks as $t)
                            <option value="{{ $t->id }}">#{{ $t->task_number }} {{ $t->title }}</option>
                        @endforeach
                    </select>
                </div>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                    <div>
                        <label style="display: block; font-size: 12px; font-weight: 700; color: var(--text-secondary); margin-bottom: 6px;">{{ __('Start Time') }} *</label>
                        <input type="datetime-local" name="started_at" required class="form-input">
                    </div>
                    <div>
                        <label style="display: block; font-size: 12px; font-weight: 700; color: var(--text-secondary); margin-bottom: 6px;">{{ __('End Time') }} *</label>
                        <input type="datetime-local" name="ended_at" required class="form-input">
                    </div>
                </div>
                <div>
                    <label style="display: block; font-size: 12px; font-weight: 700; color: var(--text-secondary); margin-bottom: 6px;">{{ __('Work Description') }}</label>
                    <input type="text" name="description" placeholder="Details of work executed..." class="form-input">
                </div>
                <button type="submit" class="tactile-btn btn-primary" style="margin-top: 8px; padding: 12px; font-size: 14px;">
                    ⏱️ {{ __('Save Time Log') }}
                </button>
            </form>
        </div>
    </div>

    <!-- Modal: Schedule Project Meeting -->
    <div id="schedule-meeting-modal" class="modal-overlay">
        <div class="modal-card">
            <div class="modal-header">
                <h3 style="font-size: 18px; font-weight: 900; color: var(--text-primary);">📅 {{ __('Schedule Project Meeting') }}</h3>
                <button onclick="closeScheduleProjectMeetingModal()" class="modal-close">✕</button>
            </div>
            <form method="POST" action="{{ route('meetings.schedule') }}" style="display: flex; flex-direction: column; gap: 14px;">
                @csrf
                <input type="hidden" name="scope" value="project">
                <input type="hidden" name="project_id" value="{{ $project->id }}">

                <div style="background: rgba(79, 155, 95, 0.12); border: 1px solid rgba(79, 155, 95, 0.25); color: var(--brand-forest); padding: 10px 14px; border-radius: var(--radius-md); font-size: 12px; font-weight: 700;">
                    📢 {{ __('All project managers, owners, and task assignees will automatically receive email invitations and chime audio alerts before the meeting.') }}
                </div>

                <div>
                    <label style="display: block; font-size: 12px; font-weight: 700; color: var(--text-secondary); margin-bottom: 6px;">{{ __('Meeting Title') }} *</label>
                    <input type="text" name="title" required value="{{ $project->name }} Sync" class="form-input">
                </div>

                <div>
                    <label style="display: block; font-size: 12px; font-weight: 700; color: var(--text-secondary); margin-bottom: 6px;">{{ __('Agenda / Notes') }}</label>
                    <textarea name="description" rows="2" placeholder="Topics to cover..." class="form-input" style="resize: vertical;"></textarea>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                    <div>
                        <label style="display: block; font-size: 12px; font-weight: 700; color: var(--text-secondary); margin-bottom: 6px;">{{ __('Meeting Room') }}</label>
                        <select name="room_id" class="form-input">
                            @foreach($rooms as $r)
                                <option value="{{ $r->id }}">🚪 {{ $r->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label style="display: block; font-size: 12px; font-weight: 700; color: var(--text-secondary); margin-bottom: 6px;">{{ __('Duration') }}</label>
                        <select name="duration_minutes" class="form-input">
                            <option value="15">15 {{ __('Minutes') }}</option>
                            <option value="30" selected>30 {{ __('Minutes') }}</option>
                            <option value="45">45 {{ __('Minutes') }}</option>
                            <option value="60">1 {{ __('Hour') }}</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label style="display: block; font-size: 12px; font-weight: 700; color: var(--text-secondary); margin-bottom: 6px;">{{ __('Scheduled Date & Time') }} *</label>
                    <input type="datetime-local" name="scheduled_at" id="hub-meeting-time-input" required class="form-input">
                </div>

                <button type="submit" class="tactile-btn btn-primary" style="margin-top: 6px; padding: 12px; font-size: 14px;">
                    🚀 {{ __('Schedule Meeting & Email Team') }}
                </button>
            </form>
        </div>
    </div>

    <!-- Modal: Task Inspector & Activity Drawer -->
    <div id="task-details-modal" class="modal-overlay">
        <div class="modal-card" style="max-width: 800px; width: 95vw;">
            <div class="modal-header">
                <div>
                    <span id="task-modal-code" class="badge-pill badge-neutral" style="font-family: monospace;">#1</span>
                    <h2 id="task-modal-title" style="font-size: 18px; font-weight: 900; color: var(--text-primary); margin-top: 4px;">Task Title</h2>
                </div>
                <button onclick="closeTaskInspector()" class="modal-close">✕</button>
            </div>

            <!-- Quick Status Change & Timer Action -->
            <div style="display: flex; justify-content: space-between; align-items: center; background: var(--bg-surface-subtle); padding: 10px 14px; border-radius: var(--radius-md); border: 1px solid var(--border-color); margin-bottom: 16px;">
                <div style="display: flex; align-items: center; gap: 8px;">
                    <span style="font-size: 12px; font-weight: 800; color: var(--text-secondary);">⚡ {{ __('Status') }}:</span>
                    <select id="task-modal-status-select" onchange="updateCurrentTaskStatus(this.value)" class="form-input" style="padding: 4px 8px; font-size: 12px; width: auto;">
                        <option value="backlog">📌 {{ __('Backlog') }}</option>
                        <option value="ready">🎯 {{ __('Ready') }}</option>
                        <option value="in_progress">⚡ {{ __('In Progress') }}</option>
                        <option value="review">🔍 {{ __('Review / QA') }}</option>
                        <option value="done">🎉 {{ __('Done') }}</option>
                    </select>
                </div>
                <button id="task-modal-timer-btn" onclick="toggleTaskTimerAction()" class="tactile-btn btn-secondary" style="padding: 6px 12px; font-size: 11px;">
                    ⏱️ {{ __('Start Timer') }}
                </button>
            </div>

            <!-- Description -->
            <div style="margin-bottom: 16px;">
                <label style="display: block; font-size: 11px; font-weight: 800; color: var(--text-muted); text-transform: uppercase; margin-bottom: 6px;">{{ __('Description') }}</label>
                <div id="task-modal-description" style="background: var(--bg-surface-subtle); padding: 12px; border-radius: var(--radius-md); font-size: 13px; color: var(--text-primary); border: 1px solid var(--border-color);">
                    —
                </div>
            </div>

            <!-- Checklist -->
            <div style="margin-bottom: 16px;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                    <label style="font-size: 11px; font-weight: 800; color: var(--text-muted); text-transform: uppercase;">{{ __('Checklist Sub-items') }}</label>
                </div>
                <div id="task-checklist-container" style="display: flex; flex-direction: column; gap: 6px; margin-bottom: 10px;"></div>
                <form onsubmit="addTaskChecklistItem(event)" style="display: flex; gap: 8px;">
                    <input type="text" id="new-checklist-item-input" required placeholder="{{ __('Add sub-item...') }}" class="form-input" style="font-size: 12px; padding: 7px 10px;">
                    <button type="submit" class="tactile-btn btn-secondary" style="padding: 7px 12px; font-size: 11px;">+ {{ __('Add') }}</button>
                </form>
            </div>

            <!-- Comments & Discussion -->
            <div>
                <label style="display: block; font-size: 11px; font-weight: 800; color: var(--text-muted); text-transform: uppercase; margin-bottom: 8px;">💬 {{ __('Discussions & Updates') }}</label>
                <div id="task-comments-feed" style="max-height: 180px; overflow-y: auto; display: flex; flex-direction: column; gap: 8px; margin-bottom: 10px;"></div>
                <form onsubmit="addTaskComment(event)" style="display: flex; gap: 8px;">
                    <input type="text" id="new-comment-input" required placeholder="{{ __('Write a comment...') }}" class="form-input" style="font-size: 12px; padding: 7px 10px;">
                    <button type="submit" class="tactile-btn btn-primary" style="padding: 7px 14px; font-size: 11px;">{{ __('Post') }}</button>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal: New Project Document (ClickUp Docs) -->
    <div id="new-doc-modal" class="modal-overlay">
        <div class="modal-card">
            <div class="modal-header">
                <h3 style="font-size: 18px; font-weight: 900; color: var(--text-primary);">📚 {{ __('Create Project Document / Wiki') }}</h3>
                <button onclick="closeNewDocModal()" class="modal-close">✕</button>
            </div>
            <form id="new-doc-form" onsubmit="createProjectDocSubmit(event)" style="display: flex; flex-direction: column; gap: 14px;">
                <div style="display: grid; grid-template-columns: 60px 1fr; gap: 10px;">
                    <div>
                        <label style="display: block; font-size: 12px; font-weight: 700; color: var(--text-secondary); margin-bottom: 6px;">{{ __('Icon') }}</label>
                        <input type="text" name="icon" value="📄" class="form-input" style="text-align: center; font-size: 16px;">
                    </div>
                    <div>
                        <label style="display: block; font-size: 12px; font-weight: 700; color: var(--text-secondary); margin-bottom: 6px;">{{ __('Document Title') }} *</label>
                        <input type="text" name="title" required placeholder="e.g. Technical Specification & API Contracts" class="form-input">
                    </div>
                </div>
                <div>
                    <label style="display: block; font-size: 12px; font-weight: 700; color: var(--text-secondary); margin-bottom: 6px;">{{ __('Markdown Content / Specification') }}</label>
                    <textarea name="content" rows="6" placeholder="# Overview&#10;&#10;Write project documentation, meeting minutes, and architectural decisions here..." class="form-input" style="resize: vertical; font-family: monospace;"></textarea>
                </div>
                <div style="display: flex; align-items: center; gap: 8px;">
                    <input type="checkbox" name="is_pinned" id="doc_pinned" style="accent-color: var(--brand-forest);">
                    <label for="doc_pinned" style="font-size: 12px; font-weight: 700; color: var(--text-primary); cursor: pointer;">📌 {{ __('Pin to top of knowledge wiki') }}</label>
                </div>
                <button type="submit" class="tactile-btn btn-primary" style="margin-top: 8px; padding: 12px; font-size: 14px;">
                    💾 {{ __('Publish Document') }}
                </button>
            </form>
        </div>
    </div>

    <!-- Modal: New Strategic Goal (ClickUp Goals) -->
    <div id="new-goal-modal" class="modal-overlay">
        <div class="modal-card">
            <div class="modal-header">
                <h3 style="font-size: 18px; font-weight: 900; color: var(--text-primary);">🎯 {{ __('Create Strategic Project Goal') }}</h3>
                <button onclick="closeNewGoalModal()" class="modal-close">✕</button>
            </div>
            <form id="new-goal-form" onsubmit="createProjectGoalSubmit(event)" style="display: flex; flex-direction: column; gap: 14px;">
                <div>
                    <label style="display: block; font-size: 12px; font-weight: 700; color: var(--text-secondary); margin-bottom: 6px;">{{ __('Goal Name') }} *</label>
                    <input type="text" name="name" required placeholder="e.g. Beta Launch & 100 User Onboarding" class="form-input">
                </div>
                <div>
                    <label style="display: block; font-size: 12px; font-weight: 700; color: var(--text-secondary); margin-bottom: 6px;">{{ __('Description') }}</label>
                    <textarea name="description" rows="2" placeholder="Key outcomes and deliverable expectations..." class="form-input" style="resize: vertical;"></textarea>
                </div>
                <div>
                    <label style="display: block; font-size: 12px; font-weight: 700; color: var(--text-secondary); margin-bottom: 6px;">{{ __('Target Date') }}</label>
                    <input type="date" name="due_date" class="form-input">
                </div>
                <button type="submit" class="tactile-btn btn-primary" style="margin-top: 8px; padding: 12px; font-size: 14px;">
                    🚀 {{ __('Set Strategic Goal') }}
                </button>
            </form>
        </div>
    </div>

    <!-- 🌟 CLICKUP-PARITY 3D TASK CONTEXT MENU 🌟 -->
    <div id="task-context-menu" class="task-context-menu" onclick="event.stopPropagation();">
        <div class="ctx-quick-header">
            <button type="button" class="ctx-quick-btn" onclick="ctxActionCopyLink()">
                🔗 {{ __('Copy link') }}
            </button>
            <button type="button" class="ctx-quick-btn" onclick="ctxActionCopyId()">
                # {{ __('Copy ID') }}
            </button>
            <button type="button" class="ctx-quick-btn" onclick="ctxActionOpenNewTab()">
                ↗ {{ __('New tab') }}
            </button>
        </div>

        <a href="javascript:void(0)" class="ctx-item" onclick="ctxActionInspect()">
            <span><span class="ctx-icon">🔍</span>{{ __('Inspect & Edit') }}</span>
            <span style="font-size: 10px; color: var(--text-muted);">Enter</span>
        </a>

        <a href="javascript:void(0)" class="ctx-item" onclick="ctxActionStartTimer()">
            <span><span class="ctx-icon">⏱️</span>{{ __('Start timer') }}</span>
            <span class="badge-pill" style="font-size: 9px; background: rgba(79,155,95,0.15); color: #4F9B5F;">Live</span>
        </a>

        <a href="javascript:void(0)" class="ctx-item" onclick="ctxActionDuplicate()">
            <span><span class="ctx-icon">📋</span>{{ __('Duplicate') }}</span>
        </a>

        <a href="javascript:void(0)" class="ctx-item" onclick="ctxActionOpenMoveModal()">
            <span><span class="ctx-icon">➡️</span>{{ __('Move to...') }}</span>
            <span style="font-size: 10px; color: var(--text-muted);">›</span>
        </a>

        <div class="ctx-divider"></div>

        <a href="javascript:void(0)" class="ctx-item" onclick="ctxActionInspectCustomFields()">
            <span><span class="ctx-icon">🏷️</span>{{ __('Custom Fields') }}</span>
        </a>

        <a href="javascript:void(0)" class="ctx-item" onclick="ctxActionInspectDependencies()">
            <span><span class="ctx-icon">🔗</span>{{ __('Relationships') }}</span>
        </a>

        <div class="ctx-divider"></div>

        <a href="javascript:void(0)" class="ctx-item danger" onclick="ctxActionDelete()">
            <span><span class="ctx-icon">🗑️</span>{{ __('Delete') }}</span>
            <span style="font-size: 10px; color: #D96B5F;">Del</span>
        </a>

        <div style="margin-top: 4px; padding-top: 4px; border-top: 1px solid var(--border-color);">
            <button type="button" onclick="ctxActionPermissions()" style="width: 100%; border: none; background: linear-gradient(135deg, #4F9B5F 0%, #245C3A 100%); color: white; padding: 7px; border-radius: 8px; font-size: 11px; font-weight: 800; cursor: pointer; box-shadow: 0 2px 6px rgba(36,92,58,0.25);">
                🔒 {{ __('Sharing & Permissions') }}
            </button>
        </div>
    </div>

    <!-- Move Task Modal -->
    <div id="move-task-modal" class="modal-overlay">
        <div class="modal-card" style="max-width: 420px;">
            <div class="modal-header">
                <h3 style="font-size: 16px; font-weight: 900; color: var(--text-primary);">➡️ {{ __('Move Task to Project') }}</h3>
                <button type="button" onclick="closeMoveTaskModal()" class="modal-close">✕</button>
            </div>
            <form onsubmit="submitMoveTask(event)" style="display: flex; flex-direction: column; gap: 14px; margin-top: 8px;">
                <input type="hidden" id="move-task-id-input">
                <div>
                    <label style="display: block; font-size: 11px; font-weight: 800; color: var(--text-secondary); margin-bottom: 6px; text-transform: uppercase;">
                        📁 {{ __('Target Project') }}
                    </label>
                    <select id="move-target-project-select" required class="form-input">
                        @foreach($allProjects as $p)
                            <option value="{{ $p->id }}" {{ $p->id === $project->id ? 'selected' : '' }}>{{ $p->name }} ({{ $p->code }})</option>
                        @endforeach
                    </select>
                </div>
                <div style="display: flex; justify-content: flex-end; gap: 10px; margin-top: 8px;">
                    <button type="button" onclick="closeMoveTaskModal()" class="tactile-btn btn-secondary" style="padding: 8px 16px; font-size: 12px;">{{ __('Cancel') }}</button>
                    <button type="submit" class="tactile-btn btn-primary" style="padding: 8px 18px; font-size: 12px;">➡️ {{ __('Move Task') }}</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Toast Notifications Container -->
    <div id="hub-toast-container"></div>

    <script>
        const ORG_ID = "{{ $organization->id }}";
        const PROJECT_ID = "{{ $project->id }}";
        const CSRF_TOKEN = "{{ csrf_token() }}";
        let activeInspectedTaskId = null;

        // Theme management
        function applyTheme(theme) {
            const activeTheme = (theme === 'dark') ? 'dark' : 'light';
            document.documentElement.setAttribute('data-theme', activeTheme);
            if (activeTheme === 'dark') {
                document.body.classList.add('dark-mode');
            } else {
                document.body.classList.remove('dark-mode');
            }
            document.querySelectorAll('.theme-toggle-icon-label').forEach(el => {
                el.textContent = (activeTheme === 'dark') ? '☀️' : '🌙';
            });
            localStorage.setItem('vw_theme', activeTheme);
        }

        function toggleThemeMode() {
            const current = document.documentElement.getAttribute('data-theme') || 'light';
            const next = current === 'dark' ? 'light' : 'dark';
            applyTheme(next);
        }

        (function() {
            const saved = localStorage.getItem('vw_theme') || 'light';
            applyTheme(saved);
        })();

        // Sidebar Collapse Engine
        function toggleSidebarCollapse() {
            const sb = document.getElementById('sidebar-main');
            if (!sb) return;
            sb.classList.toggle('collapsed');
            localStorage.setItem('sidebar_collapsed', sb.classList.contains('collapsed'));
        }

        function toggleMobileSidebar() {
            const sb = document.getElementById('sidebar-main');
            if (sb) sb.classList.toggle('mobile-open');
        }

        (function() {
            if (localStorage.getItem('sidebar_collapsed') === 'true') {
                document.getElementById('sidebar-main')?.classList.add('collapsed');
            }
        })();

        // ClickUp Docs Modal Handlers
        function openNewDocModal() {
            document.getElementById('new-doc-modal').style.display = 'flex';
        }
        function closeNewDocModal() {
            document.getElementById('new-doc-modal').style.display = 'none';
        }
        async function createProjectDocSubmit(e) {
            e.preventDefault();
            const form = e.target;
            const payload = {
                title: form.title.value,
                icon: form.icon.value || '📄',
                content: form.content.value,
                is_pinned: form.is_pinned ? form.is_pinned.checked : false,
            };
            try {
                const res = await fetch(`/api/v1/organizations/${ORG_ID}/projects/${PROJECT_ID}/docs`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF_TOKEN, 'Accept': 'application/json' },
                    body: JSON.stringify(payload)
                });
                if (res.ok) {
                    showHubToast('📚 {{ __('Document created successfully!') }}');
                    closeNewDocModal();
                    setTimeout(() => window.location.reload(), 500);
                } else {
                    const err = await res.json();
                    alert(err.message || 'Error creating document.');
                }
            } catch (err) {
                alert('Network error.');
            }
        }

        // ClickUp Goals Modal Handlers
        function openNewGoalModal() {
            document.getElementById('new-goal-modal').style.display = 'flex';
        }
        function closeNewGoalModal() {
            document.getElementById('new-goal-modal').style.display = 'none';
        }
        async function createProjectGoalSubmit(e) {
            e.preventDefault();
            const form = e.target;
            const payload = {
                name: form.name.value,
                description: form.description.value,
                due_date: form.due_date.value || null,
            };
            try {
                const res = await fetch(`/api/v1/organizations/${ORG_ID}/projects/${PROJECT_ID}/goals`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF_TOKEN, 'Accept': 'application/json' },
                    body: JSON.stringify(payload)
                });
                if (res.ok) {
                    showHubToast('🎯 {{ __('Goal created successfully!') }}');
                    closeNewGoalModal();
                    setTimeout(() => window.location.reload(), 500);
                } else {
                    const err = await res.json();
                    alert(err.message || 'Error creating goal.');
                }
            } catch (err) {
                alert('Network error.');
            }
        }

        function zoomGantt(mode) {
            showHubToast('🔍 {{ __('Gantt timeline view adjusted') }}: ' + mode);
        }

        // Sub-Tab Switcher
        function switchHubSection(tab) {
            document.querySelectorAll('.hub-section-content').forEach(el => el.style.display = 'none');
            document.querySelectorAll('.hub-tab-btn').forEach(el => el.classList.remove('active'));

            const target = document.getElementById(`hub-section-${tab}`);
            const btn = document.getElementById(`hub-nav-btn-${tab}`);

            if (target) target.style.display = 'block';
            if (btn) btn.classList.add('active');

            if (window.history && window.history.pushState) {
                window.history.pushState(null, null, '#' + tab);
            }
        }

        // Restore active tab from hash
        window.addEventListener('DOMContentLoaded', () => {
            const hash = window.location.hash.replace('#', '');
            if (hash && document.getElementById(`hub-section-${hash}`)) {
                switchHubSection(hash);
            }
        });

        // Toast display
        function showHubToast(message, duration = 4000) {
            const c = document.getElementById('hub-toast-container');
            if (!c) return;
            const t = document.createElement('div');
            t.className = 'hub-toast';
            t.innerHTML = message;
            c.appendChild(t);
            setTimeout(() => {
                t.style.opacity = '0';
                setTimeout(() => t.remove(), 300);
            }, duration);
        }

        // Modal Controls
        function openNewTaskModal() {
            document.getElementById('new-task-modal').style.display = 'flex';
        }
        function closeNewTaskModal() {
            document.getElementById('new-task-modal').style.display = 'none';
        }

        function openManualTimeModal() {
            document.getElementById('manual-time-modal').style.display = 'flex';
        }
        function closeManualTimeModal() {
            document.getElementById('manual-time-modal').style.display = 'none';
        }

        function openScheduleProjectMeetingModal() {
            const now = new Date();
            now.setMinutes(now.getMinutes() + 30);
            const iso = new Date(now.getTime() - now.getTimezoneOffset() * 60000).toISOString().slice(0, 16);
            const input = document.getElementById('hub-meeting-time-input');
            if (input) input.value = iso;
            document.getElementById('schedule-meeting-modal').style.display = 'flex';
        }
        function closeScheduleProjectMeetingModal() {
            document.getElementById('schedule-meeting-modal').style.display = 'none';
        }

        // AJAX: Create Task
        async function createProjectTaskSubmit(e) {
            e.preventDefault();
            const form = e.target;
            const formData = new FormData(form);
            const payload = Object.fromEntries(formData.entries());

            try {
                const res = await fetch(`/api/v1/organizations/${ORG_ID}/tasks`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': CSRF_TOKEN,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(payload)
                });
                if (res.ok) {
                    showHubToast('✅ {{ __('Task created successfully!') }}');
                    closeNewTaskModal();
                    setTimeout(() => window.location.reload(), 600);
                } else {
                    const err = await res.json();
                    alert(err.message || 'Error creating task.');
                }
            } catch (err) {
                alert('Network error creating task.');
            }
        }

        // AJAX: Log Time
        async function logProjectTimeSubmit(e) {
            e.preventDefault();
            const form = e.target;
            const formData = new FormData(form);
            const payload = Object.fromEntries(formData.entries());

            try {
                const res = await fetch(`/api/v1/organizations/${ORG_ID}/time-entries/manual`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': CSRF_TOKEN,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(payload)
                });
                if (res.ok) {
                    showHubToast('⏱️ {{ __('Time entry logged successfully!') }}');
                    closeManualTimeModal();
                    setTimeout(() => window.location.reload(), 600);
                } else {
                    const err = await res.json();
                    alert(err.message || 'Error logging time.');
                }
            } catch (err) {
                alert('Network error logging time.');
            }
        }

        // Quick status update on Kanban
        async function updateTaskStatusFast(taskId, newStatus) {
            try {
                const res = await fetch(`/api/v1/organizations/${ORG_ID}/tasks/${taskId}/status`, {
                    method: 'PATCH',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': CSRF_TOKEN,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ status: newStatus })
                });
                if (res.ok) {
                    showHubToast('⚡ {{ __('Task status updated!') }}');
                    setTimeout(() => window.location.reload(), 400);
                }
            } catch (e) {
                console.error(e);
            }
        }

        // ── TASK CONTEXT MENU ENGINE (CLICKUP-PARITY) ──
        let activeCtxTaskId = null;
        let activeCtxProjectId = null;
        let activeCtxTaskTitle = '';

        function openTaskContextMenu(e, taskId, projectId, taskTitle) {
            activeCtxTaskId = taskId;
            activeCtxProjectId = projectId;
            activeCtxTaskTitle = taskTitle;

            const menu = document.getElementById('task-context-menu');
            if (!menu) return;

            menu.style.display = 'flex';

            let x = e.clientX || (e.target ? e.target.getBoundingClientRect().left : 200);
            let y = e.clientY || (e.target ? e.target.getBoundingClientRect().bottom : 200);

            const menuWidth = 250;
            const menuHeight = 330;

            if (x + menuWidth > window.innerWidth - 10) {
                x = window.innerWidth - menuWidth - 14;
            }
            if (y + menuHeight > window.innerHeight - 10) {
                y = window.innerHeight - menuHeight - 14;
            }
            if (x < 10) x = 10;
            if (y < 10) y = 10;

            menu.style.left = x + 'px';
            menu.style.top = y + 'px';
        }

        function closeTaskContextMenu() {
            const menu = document.getElementById('task-context-menu');
            if (menu) menu.style.display = 'none';
        }

        document.addEventListener('click', (e) => {
            if (!e.target.closest('#task-context-menu')) {
                closeTaskContextMenu();
            }
        });

        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') closeTaskContextMenu();
        });

        function executeClipboardCopy(text) {
            if (!text) return;
            navigator.clipboard ? navigator.clipboard.writeText(text) : (function() {
                const ta = document.createElement('textarea');
                ta.value = text;
                document.body.appendChild(ta);
                ta.select();
                document.execCommand('copy');
                ta.remove();
            })();
        }

        function ctxActionCopyLink() {
            closeTaskContextMenu();
            const link = `${window.location.origin}/projects/hub/${activeCtxProjectId}?task=${activeCtxTaskId}`;
            executeClipboardCopy(link);
            showHubToast('📋 ' + "{{ __('Task link copied to clipboard!') }}");
        }

        function ctxActionCopyId() {
            closeTaskContextMenu();
            executeClipboardCopy('#' + activeCtxTaskId);
            showHubToast('📋 ' + "{{ __('Task ID copied to clipboard!') }}");
        }

        function ctxActionOpenNewTab() {
            closeTaskContextMenu();
            window.open(`/projects/hub/${activeCtxProjectId}?task=${activeCtxTaskId}`, '_blank');
        }

        function ctxActionInspect() {
            closeTaskContextMenu();
            openTaskInspector(activeCtxTaskId);
        }

        async function startTaskDirectTimer(projId, taskId) {
            try {
                const res = await fetch(`/api/v1/organizations/${ORG_ID}/time-entries/timer/start`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF_TOKEN, 'Accept': 'application/json' },
                    body: JSON.stringify({ project_id: projId, task_id: taskId, description: 'Task work session' })
                });
                if (res.ok) {
                    showHubToast('⏱️ ' + "{{ __('Timer started!') }}");
                    setTimeout(() => window.location.reload(), 400);
                } else {
                    const data = await res.json();
                    alert(data.message || 'Error starting timer.');
                }
            } catch (err) {
                alert('Network error starting timer.');
            }
        }

        function ctxActionStartTimer() {
            closeTaskContextMenu();
            startTaskDirectTimer(activeCtxProjectId, activeCtxTaskId);
        }

        async function ctxActionDuplicate() {
            closeTaskContextMenu();
            try {
                const res = await fetch(`/api/v1/organizations/${ORG_ID}/tasks/${activeCtxTaskId}/duplicate`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': CSRF_TOKEN }
                });
                if (!res.ok) {
                    alert('Error duplicating task.');
                    return;
                }
                showHubToast('📋 ' + "{{ __('Task duplicated successfully!') }}");
                setTimeout(() => window.location.reload(), 500);
            } catch (err) {
                alert('Network error duplicating task.');
            }
        }

        function ctxActionOpenMoveModal() {
            closeTaskContextMenu();
            document.getElementById('move-task-id-input').value = activeCtxTaskId;
            document.getElementById('move-target-project-select').value = activeCtxProjectId;
            document.getElementById('move-task-modal').style.display = 'flex';
        }

        function closeMoveTaskModal() {
            document.getElementById('move-task-modal').style.display = 'none';
        }

        async function submitMoveTask(e) {
            e.preventDefault();
            const taskId = document.getElementById('move-task-id-input').value;
            const targetProjId = document.getElementById('move-target-project-select').value;

            try {
                const res = await fetch(`/api/v1/organizations/${ORG_ID}/tasks/${taskId}/move`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': CSRF_TOKEN },
                    body: JSON.stringify({ project_id: targetProjId })
                });
                if (!res.ok) {
                    alert('Error moving task.');
                    return;
                }
                closeMoveTaskModal();
                showHubToast('➡️ ' + "{{ __('Task moved successfully!') }}");
                setTimeout(() => window.location.reload(), 500);
            } catch (err) {
                alert('Network error moving task.');
            }
        }

        function ctxActionInspectCustomFields() {
            closeTaskContextMenu();
            openTaskInspector(activeCtxTaskId);
        }

        function ctxActionInspectDependencies() {
            closeTaskContextMenu();
            openTaskInspector(activeCtxTaskId);
        }

        async function ctxActionDelete() {
            closeTaskContextMenu();
            if (!confirm('{{ __('Are you sure you want to delete this task?') }}')) return;

            try {
                const res = await fetch(`/api/v1/organizations/${ORG_ID}/tasks/${activeCtxTaskId}`, {
                    method: 'DELETE',
                    headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': CSRF_TOKEN }
                });
                if (!res.ok) {
                    alert('Error deleting task.');
                    return;
                }
                showHubToast('🗑️ ' + "{{ __('Task deleted.') }}");
                setTimeout(() => window.location.reload(), 400);
            } catch (err) {
                alert('Network error deleting task.');
            }
        }

        function ctxActionPermissions() {
            closeTaskContextMenu();
            showHubToast('🔒 <strong>' + "{{ __('Sharing & Permissions') }}" + '</strong>: ' + "{{ __('Inherited from Project Role Settings') }}");
        }

        // Task Inspector
        async function openTaskInspector(taskId) {
            activeInspectedTaskId = taskId;
            const modal = document.getElementById('task-details-modal');
            modal.style.display = 'flex';

            try {
                const res = await fetch(`/api/v1/organizations/${ORG_ID}/tasks/${taskId}`, {
                    headers: { 'Accept': 'application/json' }
                });
                if (!res.ok) return;
                const data = await res.json();
                const t = data.task || data;

                document.getElementById('task-modal-code').textContent = '#' + (t.task_number || '');
                document.getElementById('task-modal-title').textContent = t.title || '';
                document.getElementById('task-modal-description').textContent = t.description || '—';
                const statusSelect = document.getElementById('task-modal-status-select');
                if (statusSelect) {
                    statusSelect.value = t.status || 'backlog';
                    const canEdit = {{ ($user->isSuperAdmin() || $membership->role?->slug === 'company_admin' || $membership->hasPermission('tasks.assign') || $membership->hasPermission('tasks.delete') || ($project && $project->manager_id === $user->id)) ? 'true' : 'false' }} || (t.assignee_id == '{{ $user->id }}') || (t.creator_id == '{{ $user->id }}');
                    statusSelect.disabled = !canEdit;
                    statusSelect.title = canEdit ? '' : '{{ __('Only assigned member or manager can edit') }}';
                }

                // Render checklist
                const checkCont = document.getElementById('task-checklist-container');
                checkCont.innerHTML = '';
                (t.checklist_items || []).forEach(ci => {
                    const item = document.createElement('label');
                    item.style = 'display: flex; align-items: center; gap: 8px; font-size: 12px; cursor: pointer; background: var(--bg-surface-subtle); padding: 6px 10px; border-radius: 6px;';
                    item.innerHTML = `
                        <input type="checkbox" ${ci.is_completed ? 'checked' : ''} onchange="toggleChecklistItem('${t.id}', '${ci.id}', this.checked)" style="accent-color: var(--brand-forest);">
                        <span style="${ci.is_completed ? 'text-decoration: line-through; opacity: 0.6;' : ''}">${ci.title}</span>
                    `;
                    checkCont.appendChild(item);
                });

                // Render comments
                const commCont = document.getElementById('task-comments-feed');
                commCont.innerHTML = '';
                (t.comments || []).forEach(c => {
                    const box = document.createElement('div');
                    box.style = 'background: var(--bg-surface-subtle); padding: 8px 12px; border-radius: 8px; font-size: 12px; border: 1px solid var(--border-color);';
                    box.innerHTML = `
                        <div style="display: flex; justify-content: space-between; margin-bottom: 2px; font-weight: 800; font-size: 11px;">
                            <span>${c.user ? c.user.name : 'Member'}</span>
                            <span style="color: var(--text-muted);">${new Date(c.created_at).toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'})}</span>
                        </div>
                        <div>${c.body}</div>
                    `;
                    commCont.appendChild(box);
                });
            } catch (e) {
                console.error(e);
            }
        }

        async function startHubTaskTimerDirect(projectId, taskId, taskTitle, projectName) {
            try {
                const res = await fetch(`/api/v1/organizations/${ORG_ID}/time-entries/timer/start`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF_TOKEN, 'Accept': 'application/json' },
                    body: JSON.stringify({
                        project_id: projectId,
                        task_id: taskId,
                        description: `${taskTitle} (${projectName})`
                    })
                });
                if (res.ok) {
                    showHubToast('⏱️ {{ __('Timer started successfully!') }}');
                    setTimeout(() => window.location.reload(), 400);
                } else {
                    const err = await res.json();
                    showHubToast(err.message || 'Error starting timer.');
                }
            } catch (e) {
                showHubToast('Network error starting timer.');
            }
        }

        function closeTaskInspector() {
            document.getElementById('task-details-modal').style.display = 'none';
            activeInspectedTaskId = null;
        }

        async function updateCurrentTaskStatus(newStatus) {
            if (!activeInspectedTaskId) return;
            await updateTaskStatusFast(activeInspectedTaskId, newStatus);
        }

        async function toggleChecklistItem(taskId, itemId, checked) {
            try {
                await fetch(`/api/v1/organizations/${ORG_ID}/tasks/${taskId}/checklist/${itemId}`, {
                    method: 'PATCH',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF_TOKEN, 'Accept': 'application/json' },
                    body: JSON.stringify({ is_completed: checked })
                });
            } catch (e) {}
        }

        async function addTaskChecklistItem(e) {
            e.preventDefault();
            if (!activeInspectedTaskId) return;
            const input = document.getElementById('new-checklist-item-input');
            const title = input.value.trim();
            if (!title) return;

            try {
                const res = await fetch(`/api/v1/organizations/${ORG_ID}/tasks/${activeInspectedTaskId}/checklist`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF_TOKEN, 'Accept': 'application/json' },
                    body: JSON.stringify({ title })
                });
                if (res.ok) {
                    input.value = '';
                    openTaskInspector(activeInspectedTaskId);
                }
            } catch (e) {}
        }

        async function addTaskComment(e) {
            e.preventDefault();
            if (!activeInspectedTaskId) return;
            const input = document.getElementById('new-comment-input');
            const body = input.value.trim();
            if (!body) return;

            try {
                const res = await fetch(`/api/v1/organizations/${ORG_ID}/tasks/${activeInspectedTaskId}/comments`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF_TOKEN, 'Accept': 'application/json' },
                    body: JSON.stringify({ body })
                });
                if (res.ok) {
                    input.value = '';
                    openTaskInspector(activeInspectedTaskId);
                }
            } catch (e) {}
        }

        async function stopHubGlobalTimer() {
            try {
                const res = await fetch(`/api/v1/organizations/${ORG_ID}/time-entries/timer/stop`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF_TOKEN, 'Accept': 'application/json' }
                });
                if (res.ok) {
                    showHubToast('⏹ {{ __('Timer stopped and time entry saved.') }}');
                    setTimeout(() => window.location.reload(), 400);
                }
            } catch (e) {}
        }

        // Live timer clock
        @if($activeTimer)
            const timerStart = new Date("{{ $activeTimer->started_at->toIso8601String() }}").getTime();
            function updateHubClock() {
                const now = new Date().getTime();
                const diffSec = Math.floor((now - timerStart) / 1000);
                const hrs = String(Math.floor(diffSec / 3600)).padStart(2, '0');
                const mins = String(Math.floor((diffSec % 3600) / 60)).padStart(2, '0');
                const secs = String(diffSec % 60).padStart(2, '0');
                const el = document.getElementById('hub-live-timer-clock');
                if (el) el.textContent = `${hrs}:${mins}:${secs}`;
            }
            setInterval(updateHubClock, 1000);
            updateHubClock();
        @endif
    </script>
</body>
</html>
