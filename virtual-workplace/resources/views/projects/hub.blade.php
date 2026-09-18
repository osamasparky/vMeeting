<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}" data-theme="light">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $project->name }} ({{ $project->code }}) — {{ __('Project Hub') }} | {{ $organization->name }}</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- Typography: IBM Plex Sans Arabic & IBM Plex Sans -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Mono:wght@400;500;600;700&family=IBM+Plex+Sans+Arabic:wght@300;400;500;600;700;800&family=IBM+Plex+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;1,400&family=Material+Symbols+Rounded:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200&display=swap" rel="stylesheet">
    <!-- Centralized UlaSpace Design System Tokens -->
    <link rel="stylesheet" href="{{ asset('css/ulaspace-tokens.css') }}">
    <link rel="stylesheet" href="{{ asset('css/modern-design-system.css') }}">
    <link rel="stylesheet" href="{{ asset('css/ulaspace-dashboard.css') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        /* UlaSpace Design Tokens Mapping.
           Only --ula-gradient-accent and --ula-transition-smooth are
           actually referenced below; everything else this block used to
           carry (--bg-canvas, --bg-secondary, --border-focus, a
           retired-prefix workspace/leaf/sage set, --accent-gold, --status-info,
           --ula-gradient-accent-gold) had zero usages in this file --
           removed rather than kept unused. --font-family here was also
           unused (nothing in this file references var(--font-family));
           the page already correctly inherits the direction-aware font
           from <html> via the shared ulaspace-tokens.css [dir] rules. */
        :root {
            --ula-gradient-accent: linear-gradient(135deg, var(--ula-palm-900) 0%, var(--ula-palm-800) 100%);
            --ula-transition-smooth: all var(--ula-duration-base) var(--ula-ease-in-out);
        }

        /* Dark Spatial Workspace Mode */
        [data-theme="dark"], body.dark-mode {
            --ula-gradient-accent: linear-gradient(135deg, var(--ula-palm-900) 0%, var(--ula-palm-800) 100%);
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            background-color: var(--ula-surface-page);
            color: var(--ula-text-primary);
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
            background: var(--ula-surface-dark);
            color: var(--ula-text-on-dark);
            height: 100vh;
            position: sticky;
            top: 0;
            display: flex;
            flex-direction: column;
            border-inline-end: 1px solid var(--ula-border-on-dark);
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
            border-bottom: 1px solid var(--ula-border-on-dark);
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
            border-radius: var(--ula-radius-sm);
            background: var(--ula-gradient-accent);
            color: var(--ula-white);
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
            color: var(--ula-white);
        }

        .brand-sub {
            font-size: 11px;
            color: var(--ula-text-on-dark-muted);
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
            color: var(--ula-text-on-dark-muted);
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
            border-radius: var(--ula-radius-sm);
            color: var(--ula-text-on-dark);
            font-size: 13px;
            font-weight: 700;
            transition: var(--ula-transition-smooth);
            cursor: pointer;
            position: relative;
            white-space: nowrap;
        }

        .sidebar-link-btn:hover {
            background: var(--ula-control-dark-fill-hover);
            color: var(--ula-white);
            transform: translateX(2px);
        }

        html[dir="rtl"] .sidebar-link-btn:hover {
            transform: translateX(-2px);
        }

        .sidebar-link-btn.active {
            background: var(--ula-control-dark-fill-strong);
            color: var(--ula-white);
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
            color: var(--ula-white);
            font-size: 11px;
            font-weight: 800;
            padding: 2px 8px;
            border-radius: var(--ula-radius-pill);
        }

        .sidebar-footer {
            padding: 16px;
            border-top: 1px solid var(--ula-border-on-dark);
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
            background: var(--ula-surface-card);
            border-bottom: 1px solid var(--ula-border-subtle);
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 28px;
            position: sticky;
            top: 0;
            z-index: 100;
            backdrop-filter: blur(12px);
            box-shadow: var(--ula-shadow-xs);
        }

        .breadcrumb-trail {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 13px;
            font-weight: 700;
            color: var(--ula-text-muted);
            flex-wrap: wrap;
        }

        .breadcrumb-trail a:hover {
            color: var(--ula-text-primary);
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
            border-radius: var(--ula-radius-sm);
            font-size: 13px;
            font-weight: 800;
            cursor: pointer;
            border: 1px solid transparent;
            transition: var(--ula-transition-smooth);
            font-family: inherit;
        }

        .btn-primary {
            background: var(--ula-accent-default);
            color: var(--ula-accent-fg);
            box-shadow: 0 3px 0 var(--ula-palm-800), var(--ula-shadow-sm);
        }

        .btn-primary:hover {
            background: var(--ula-accent-hover);
            transform: translateY(-1.5px);
            box-shadow: 0 4px 0 var(--ula-palm-800), var(--ula-shadow-md);
        }

        .btn-primary:active {
            transform: translateY(2px);
            box-shadow: 0 1px 0 var(--ula-palm-800);
        }

        .btn-secondary {
            background: var(--ula-surface-card);
            color: var(--ula-text-primary);
            border-color: var(--ula-border-subtle);
            box-shadow: 0 3px 0 var(--ula-border-subtle), var(--ula-shadow-xs);
        }

        .btn-secondary:hover {
            background: var(--ula-surface-page-alt);
            border-color: var(--ula-palm-900);
            transform: translateY(-1.5px);
            box-shadow: 0 4px 0 var(--ula-border-subtle), var(--ula-shadow-xs);
        }

        .btn-secondary:active {
            transform: translateY(2px);
            box-shadow: 0 1px 0 var(--ula-border-subtle);
        }

        /* Hero Project Banner */
        .hero-banner-card {
            background: var(--ula-surface-card);
            border: 1px solid var(--ula-border-subtle);
            border-radius: var(--ula-radius-xl);
            padding: 26px 28px;
            margin-bottom: 24px;
            box-shadow: var(--ula-shadow-xs);
            position: relative;
            overflow: hidden;
        }

        .hero-banner-card::before {
            content: '';
            position: absolute;
            top: 0;
            inset-inline-start: 0;
            inset-inline-end: 0;
            height: 3px;
            background: linear-gradient(90deg, var(--ula-status-success) 0%, var(--ula-status-success) 100%);
        }

        /* Badges & Pills */
        .badge-pill {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 4px 10px;
            border-radius: var(--ula-radius-pill);
            font-size: 11px;
            font-weight: 800;
        }

        .badge-green { background: rgba(79, 155, 95, 0.15); color: var(--ula-status-success); border: 1px solid rgba(79, 155, 95, 0.25); }
        .badge-gold { background: rgba(214, 162, 58, 0.15); color: var(--ula-gold-400); border: 1px solid rgba(214, 162, 58, 0.25); }
        .badge-danger { background: rgba(217, 107, 95, 0.15); color: var(--ula-status-danger); border: 1px solid rgba(217, 107, 95, 0.25); }
        .badge-neutral { background: var(--ula-surface-page-alt); color: var(--ula-text-secondary); border: 1px solid var(--ula-border-subtle); }

        /* KPI Grid (Enhanced Spatial Depth) */
        .kpi-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(210px, 1fr));
            gap: 16px;
            margin-bottom: 24px;
        }

        /* .kpi-card/.kpi-header/.kpi-title/.kpi-icon-box/.kpi-value
           intentionally NOT defined here -- ulaspace-dashboard.css (which
           this page loads) owns the single shared definition. This is the
           third time this exact local duplicate has reappeared in this
           file after a token/CSS regeneration; see the comment at the top
           of that shared block for why it keeps getting re-added instead
           of just restored from git history. */

        /* Navigation Sub Tabs */
        .hub-tabs-nav {
            display: flex;
            align-items: center;
            gap: 8px;
            background: var(--ula-surface-page-alt);
            padding: 6px;
            border-radius: var(--ula-radius-lg);
            border: 1px solid var(--ula-border-subtle);
            box-shadow: var(--ula-shadow-xs);
            margin-bottom: 24px;
            overflow-x: auto;
        }

        .hub-tab-btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 18px;
            border-radius: var(--ula-radius-sm);
            font-size: 13px;
            font-weight: 800;
            cursor: pointer;
            border: none;
            background: transparent;
            color: var(--ula-text-secondary);
            transition: var(--ula-transition-smooth);
            white-space: nowrap;
        }

        .hub-tab-btn:hover {
            color: var(--ula-text-primary);
            background: rgba(255, 255, 255, 0.5);
        }

        .hub-tab-btn.active {
            background: var(--ula-surface-card);
            color: var(--ula-text-primary);
            box-shadow: var(--ula-shadow-xs);
        }

        /* Card Container */
        .hub-card {
            background: var(--ula-surface-card);
            border: 1px solid var(--ula-border-subtle);
            border-radius: var(--ula-radius-xl);
            padding: 24px;
            box-shadow: var(--ula-shadow-xs);
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
            background: var(--ula-surface-page-alt);
            color: var(--ula-text-muted);
            font-weight: 800;
            text-transform: uppercase;
            font-size: 11px;
            letter-spacing: 0.5px;
            padding: 14px 18px;
            border-bottom: 1px solid var(--ula-border-subtle);
            text-align: start;
        }

        .data-table td {
            padding: 14px 18px;
            border-bottom: 1px solid var(--ula-border-subtle);
            color: var(--ula-text-primary);
            vertical-align: middle;
        }

        .data-table tr:last-child td {
            border-bottom: none;
        }

        .data-table tr:hover td {
            background: var(--ula-surface-page-alt);
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
            background: var(--ula-surface-page-alt);
            border: 1px solid var(--ula-border-subtle);
            border-radius: var(--ula-radius-xl);
            padding: 16px;
            box-shadow: var(--ula-shadow-xs);
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
            border-bottom: 1px solid var(--ula-border-subtle);
            font-size: 13px;
            font-weight: 900;
        }

        .kanban-card {
            background: var(--ula-surface-card);
            border: 1px solid var(--ula-border-subtle);
            border-radius: var(--ula-radius-sm);
            padding: 14px;
            box-shadow: var(--ula-shadow-xs);
            margin-bottom: 12px;
            cursor: pointer;
            transition: var(--ula-transition-smooth);
            position: relative;
        }

        .kanban-card:hover {
            transform: translateY(-2px);
            border-color: var(--ula-palm-900);
            box-shadow: var(--ula-shadow-xs);
        }

        /* ── ClickUp 3D Tactile Task Context Menu ── */
        .task-context-menu {
            position: fixed;
            z-index: 100000;
            width: 250px;
            background: var(--ula-surface-card);
            border: 1px solid var(--ula-border-subtle);
            border-radius: var(--ula-radius-lg);
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
            border-bottom: 1px solid var(--ula-border-subtle);
            margin-bottom: 4px;
        }
        .ctx-quick-btn {
            flex: 1;
            padding: 6px 8px;
            background: var(--ula-surface-page-alt);
            border: 1px solid var(--ula-border-subtle);
            border-radius: 8px;
            font-size: 11px;
            font-weight: 700;
            color: var(--ula-text-primary);
            cursor: pointer;
            text-align: center;
            transition: all 0.15s ease;
            white-space: nowrap;
        }
        .ctx-quick-btn:hover {
            background: rgba(36, 92, 58, 0.12);
            color: var(--ula-text-primary);
            border-color: var(--ula-palm-900);
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
            color: var(--ula-text-primary);
            cursor: pointer;
            transition: all 0.15s ease;
            text-decoration: none;
            border: 1px solid transparent;
        }
        .ctx-item:hover {
            background: var(--ula-surface-page-alt);
            color: var(--ula-text-primary);
            border-color: var(--ula-border-subtle);
            transform: translateX({{ app()->getLocale() === 'ar' ? '-2px' : '2px' }});
        }
        .ctx-item.danger:hover {
            background: rgba(217, 107, 95, 0.15);
            color: var(--ula-status-danger);
            border-color: rgba(217, 107, 95, 0.3);
        }
        .ctx-divider {
            height: 1px;
            background: var(--ula-border-subtle);
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
            background: var(--ula-surface-card);
            border: 1px solid var(--ula-border-subtle);
            border-radius: var(--ula-radius-xl);
            box-shadow: var(--ula-shadow-lg);
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
            border-bottom: 1px solid var(--ula-border-subtle);
        }

        .modal-close {
            background: var(--ula-surface-page-alt);
            border: 1px solid var(--ula-border-subtle);
            width: 32px;
            height: 32px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            font-weight: 800;
            color: var(--ula-text-primary);
        }

        .form-input {
            width: 100%;
            background: var(--ula-surface-page-alt);
            border: 1px solid var(--ula-border-subtle);
            border-radius: var(--ula-radius-sm);
            padding: 10px 14px;
            color: var(--ula-text-primary);
            font-size: 13px;
            font-weight: 600;
            font-family: inherit;
            outline: none;
            box-shadow: var(--ula-shadow-xs);
        }

        .form-input:focus {
            border-color: var(--ula-palm-900);
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
            background: var(--ula-surface-card);
            border: 1px solid var(--ula-border-subtle);
            border-radius: var(--ula-radius-lg);
            padding: 14px 20px;
            box-shadow: var(--ula-shadow-lg);
            color: var(--ula-text-primary);
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
                inset-inline-start: 0;
                top: 0;
                bottom: 0;
                transform: translateX(-100%);
            }
            html[dir="rtl"] .app-sidebar {
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

        /* ── SortableJS Kanban 3D Drag & Drop ── */
        .kanban-cards-container {
            min-height: 120px;
            padding: 4px;
            border-radius: var(--ula-radius-sm);
            transition: background 0.2s ease, border-color 0.2s ease;
        }
        .kanban-card {
            cursor: grab !important;
            user-select: none;
            transition: transform 0.15s ease, box-shadow 0.15s ease, opacity 0.15s ease;
        }
        .kanban-card:active {
            cursor: grabbing !important;
        }
        .kanban-card-ghost {
            opacity: 0.35 !important;
            background: rgba(79, 155, 95, 0.12) !important;
            border: 2px dashed var(--ula-palm-900) !important;
            box-shadow: none !important;
            transform: scale(0.98);
        }
        .kanban-card-chosen {
            cursor: grabbing !important;
            background: var(--ula-surface-raised, var(--ula-white)) !important;
            box-shadow: 0 12px 30px rgba(0, 0, 0, 0.18) !important;
            transform: scale(1.02);
            z-index: 100;
        }
        .kanban-card-drag {
            opacity: 0.95 !important;
            transform: rotate(2deg) scale(1.02);
        }

        /* ── Task Card Premium Component Styles ── */
        .task-card-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 6px;
            margin-bottom: 8px;
        }
        .task-card-tags {
            display: flex;
            align-items: center;
            gap: 5px;
            flex-wrap: wrap;
        }
        .task-code-badge {
            font-family: monospace;
            font-size: 11px;
            font-weight: 900;
            color: var(--ula-text-muted);
            background: var(--ula-surface-page-alt);
            padding: 2px 6px;
            border-radius: var(--ula-radius-xs, 6px);
            border: 1px solid var(--ula-border-subtle);
        }
        .task-card-actions {
            display: flex;
            align-items: center;
            gap: 5px;
        }
        .task-dots-btn {
            background: transparent;
            border: 1px solid transparent;
            color: var(--ula-text-muted);
            cursor: pointer;
            padding: 2px 6px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 800;
            transition: all 0.15s ease;
        }
        .task-dots-btn:hover {
            background: var(--ula-surface-page-alt);
            border-color: var(--ula-border-subtle);
            color: var(--ula-text-primary);
        }
        .task-card-title {
            font-size: 13.5px;
            font-weight: 800;
            color: var(--ula-text-primary);
            margin: 0 0 8px 0;
            line-height: 1.45;
            word-break: break-word;
        }
        .task-card-milestone {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            font-size: 10.5px;
            font-weight: 800;
            padding: 2px 8px;
            border-radius: 99px;
            background: rgba(66, 119, 76, 0.15);
            color: var(--ula-status-success);
            border: 1px solid rgba(66, 119, 76, 0.3);
            margin-bottom: 8px;
            max-width: 100%;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        .task-card-meta {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 6px;
            font-size: 11px;
            color: var(--ula-text-muted);
            margin-bottom: 8px;
        }
        .task-due-date {
            display: inline-flex;
            align-items: center;
            gap: 3px;
            font-size: 10.5px;
            font-weight: 700;
            padding: 2px 6px;
            border-radius: 6px;
            background: var(--ula-surface-page-alt);
            border: 1px solid var(--ula-border-subtle);
            color: var(--ula-text-secondary);
        }
        .task-due-date.is-overdue {
            background: rgba(239, 68, 68, 0.12);
            color: var(--ula-status-danger);
            border-color: rgba(239, 68, 68, 0.3);
        }
        .task-card-footer {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 8px;
            padding-top: 8px;
            border-top: 1px solid var(--ula-border-subtle);
            margin-top: 4px;
        }
        .task-assignee-chip {
            display: flex;
            align-items: center;
            gap: 6px;
            font-size: 11px;
            font-weight: 700;
            color: var(--ula-text-secondary);
            min-width: 0;
        }
        .task-avatar-circle {
            width: 22px;
            height: 22px;
            border-radius: 50%;
            background: var(--ula-gradient-accent);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 9px;
            font-weight: 900;
            flex-shrink: 0;
        }

        /* ── Frappe Gantt Dark Mode & High Contrast Styles ── */
        .gantt-container {
            background: var(--ula-surface-card) !important;
            font-family: inherit !important;
        }
        .gantt .grid-background {
            fill: var(--ula-surface-card) !important;
        }
        .gantt .grid-header {
            fill: var(--ula-surface-page-alt) !important;
            stroke: var(--ula-border-subtle) !important;
        }
        .gantt .grid-row {
            fill: var(--ula-surface-card) !important;
            stroke: var(--ula-border-subtle) !important;
        }
        .gantt .grid-row:nth-child(even) {
            fill: var(--ula-surface-page-alt) !important;
        }
        .gantt .row-line {
            stroke: var(--ula-border-subtle) !important;
        }
        .gantt .tick {
            stroke: var(--ula-border-subtle) !important;
        }
        .gantt .lower-text, .gantt .upper-text {
            fill: var(--ula-text-primary) !important;
            font-size: 11px !important;
            font-weight: 700 !important;
        }
        .gantt .today-highlight {
            fill: rgba(66, 119, 76, 0.15) !important;
        }
        .gantt .arrow {
            stroke: var(--ula-text-muted) !important;
            stroke-width: 1.5 !important;
        }
        .gantt .bar {
            stroke-width: 0 !important;
        }
        .gantt .bar-progress {
            fill: rgba(255, 255, 255, 0.25) !important;
        }
        .gantt .bar-label {
            fill: var(--ula-white) !important;
            font-size: 11px !important;
            font-weight: 800 !important;
            text-shadow: 0 1px 2px rgba(0,0,0,0.5);
        }

        /* ── Filter Toolbar ── */
        .hub-filter-bar {
            display: flex;
            gap: 10px;
            margin-bottom: 16px;
            background: var(--ula-surface-card);
            padding: 10px 14px;
            border-radius: var(--ula-radius-lg);
            border: 1px solid var(--ula-border-subtle);
            box-shadow: var(--ula-shadow-xs);
            align-items: center;
            flex-wrap: wrap;
        }

        /* ── Task Inspector Tabs & Activity Timeline ── */
        .task-inspector-tab-btn {
            padding: 8px 16px;
            font-size: 12px;
            font-weight: 800;
            color: var(--ula-text-secondary);
            background: transparent;
            border: none;
            border-bottom: 2px solid transparent;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 6px;
            transition: all 0.2s ease;
        }
        .task-inspector-tab-btn:hover {
            color: var(--ula-text-primary);
        }
        .task-inspector-tab-btn.active {
            color: var(--ula-text-primary);
            border-bottom-color: var(--ula-palm-900);
            background: rgba(79, 155, 95, 0.08);
            border-radius: 6px 6px 0 0;
        }

        .activity-timeline-item {
            display: flex;
            gap: 12px;
            position: relative;
            padding-bottom: 16px;
        }
        .activity-timeline-item:not(:last-child)::before {
            content: '';
            position: absolute;
            inset-inline-start: 15px;
            top: 30px;
            bottom: 0;
            width: 2px;
            background: var(--ula-border-subtle);
        }
        .activity-avatar {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            background: var(--ula-gradient-accent);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 11px;
            font-weight: 900;
            flex-shrink: 0;
            box-shadow: 0 2px 6px rgba(0,0,0,0.1);
        }
        .activity-content-box {
            flex: 1;
            background: var(--ula-surface-page-alt);
            border: 1px solid var(--ula-border-subtle);
            border-radius: 8px;
            padding: 10px 12px;
            font-size: 12px;
        }
    </style>
    <!-- SortableJS Library for True Fluid Drag & Drop Kanban -->
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.2/Sortable.min.js" nonce="{{ $cspNonce ?? '' }}"></script>
    <!-- Frappe Gantt — Real Date-Based Gantt Chart Library -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/frappe-gantt@0.6.1/dist/frappe-gantt.min.css">
    <script src="https://cdn.jsdelivr.net/npm/frappe-gantt@0.6.1/dist/frappe-gantt.min.js" nonce="{{ $cspNonce ?? '' }}"></script>
</head>
<body>

    <!-- ── 1. COLLAPSIBLE WORKSPACE SIDEBAR NAVIGATION ── -->
    <aside id="sidebar-main" class="app-sidebar">
        <div class="sidebar-brand-box">
            <div class="brand-logo-area">
                <div class="brand-emblem"><span class="material-symbols-rounded" style="font-size: 1em; vertical-align: text-bottom;">eco</span></div>
                <div class="brand-title-wrap">
                    <div class="brand-title">{{ $organization->name }}</div>
                    <div class="brand-sub">{{ __('Virtual Workplace') }}</div>
                </div>
            </div>
            <button onclick="toggleSidebarCollapse()" class="tactile-btn btn-secondary" style="padding: 4px 8px; font-size: 11px; background: transparent; border: 1px solid var(--ula-border-on-dark); color: var(--ula-text-on-dark-muted);" title="{{ __('Toggle Slim Sidebar') }}">
                ↔
            </button>
        </div>

        <ul class="sidebar-nav-list">
            <!-- Overview & Office -->
            <li class="nav-category-title">{{ __('Workspace') }}</li>
            <li>
                <a href="{{ route('dashboard') }}#overview" class="sidebar-link-btn">
                    <span class="nav-icon"><span class="material-symbols-rounded" style="font-size: 1em; vertical-align: text-bottom;">bar_chart</span></span>
                    <span class="nav-label-text">{{ __('Dashboard') }}</span>
                </a>
            </li>
            <li>
                <a href="{{ route('office') }}" class="sidebar-link-btn" style="background: rgba(79, 155, 95, 0.18); color: var(--ula-status-success);">
                    <span class="nav-icon"><span class="material-symbols-rounded" style="font-size: 1em; vertical-align: text-bottom;">rocket_launch</span></span>
                    <span class="nav-label-text">{{ __('Enter Office') }}</span>
                    <span class="sidebar-badge-pill">مباشر</span>
                </a>
            </li>

            <!-- Projects & Execution -->
            <li class="nav-category-title">{{ __('Projects & Tasks') }}</li>
            <li>
                <a href="{{ route('dashboard') }}#projects" class="sidebar-link-btn active">
                    <span class="nav-icon"><span class="material-symbols-rounded" style="font-size: 1em; vertical-align: text-bottom;">folder</span></span>
                    <span class="nav-label-text">{{ __('Projects Portfolio') }}</span>
                    <span class="sidebar-badge-pill">{{ $stats['total_projects'] ?? 0 }}</span>
                </a>
            </li>
            @if($membership->hasPermission('tasks.assign') || $membership->hasPermission('tasks.delete') || $membership->role?->slug === 'company_admin')
            <li>
                <a href="{{ route('dashboard') }}#all-tasks" class="sidebar-link-btn">
                    <span class="nav-icon"><span class="material-symbols-rounded" style="font-size: 1em; vertical-align: text-bottom;">bookmark</span></span>
                    <span class="nav-label-text">{{ __('All Tasks') }}</span>
                    <span class="sidebar-badge-pill">{{ $stats['total_tasks'] ?? 0 }}</span>
                </a>
            </li>
            @endif
            <li>
                <a href="{{ route('dashboard') }}#my-tasks" class="sidebar-link-btn">
                    <span class="nav-icon"><span class="material-symbols-rounded" style="font-size: 1em; vertical-align: text-bottom;">bolt</span></span>
                    <span class="nav-label-text">{{ __('My Tasks') }}</span>
                    <span class="sidebar-badge-pill">{{ $myTasks->count() }}</span>
                </a>
            </li>
            <li>
                <a href="{{ route('dashboard') }}#chat" class="sidebar-link-btn">
                    <span class="nav-icon"><span class="material-symbols-rounded" style="font-size: 1em; vertical-align: text-bottom;">chat</span></span>
                    <span class="nav-label-text">{{ __('Team Chat & DMs') }}</span>
                    <span class="sidebar-badge-pill">مباشر</span>
                </a>
            </li>
            <li>
                <a href="{{ route('dashboard') }}#timesheets" class="sidebar-link-btn">
                    <span class="nav-icon"><span class="material-symbols-rounded" style="font-size: 1em; vertical-align: text-bottom;">timer</span></span>
                    <span class="nav-label-text">{{ __('Timesheets & Time') }}</span>
                </a>
            </li>
            <li>
                <a href="{{ route('dashboard') }}#meetings" class="sidebar-link-btn">
                    <span class="nav-icon"><span class="material-symbols-rounded" style="font-size: 1em; vertical-align: text-bottom;">calendar_month</span></span>
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
                    <span class="nav-icon"><span class="material-symbols-rounded" style="font-size: 1em; vertical-align: text-bottom;">group</span></span>
                    <span class="nav-label-text">{{ __('Team Members') }}</span>
                    <span class="sidebar-badge-pill">{{ $stats['active_members'] ?? 0 }}</span>
                </a>
            </li>
            @endif
            @if($membership->hasPermission('rooms.manage'))
            <li>
                <a href="{{ route('dashboard') }}#rooms" class="sidebar-link-btn">
                    <span class="nav-icon"><span class="material-symbols-rounded" style="font-size: 1em; vertical-align: text-bottom;">door_front</span></span>
                    <span class="nav-label-text">{{ __('Meeting Rooms') }}</span>
                    <span class="sidebar-badge-pill">{{ $stats['total_rooms'] ?? 0 }}</span>
                </a>
            </li>
            @endif
            @if($membership->hasPermission('departments.manage') || $membership->hasPermission('teams.manage'))
            <li>
                <a href="{{ route('dashboard') }}#departments" class="sidebar-link-btn">
                    <span class="nav-icon"><span class="material-symbols-rounded" style="font-size: 1em; vertical-align: text-bottom;">account_balance</span></span>
                    <span class="nav-label-text">{{ __('Departments') }}</span>
                    <span class="sidebar-badge-pill">{{ $stats['total_departments'] ?? 0 }}</span>
                </a>
            </li>
            @endif
            @if($membership->hasPermission('organizations.manage'))
            <li>
                <a href="{{ route('dashboard') }}#settings" class="sidebar-link-btn">
                    <span class="nav-icon"><span class="material-symbols-rounded" style="font-size: 1em; vertical-align: text-bottom;">settings</span></span>
                    <span class="nav-label-text">{{ __('Workspace Settings') }}</span>
                </a>
            </li>
            @endif
            @endif

            @if($user->is_superadmin ?? false)
                <li class="nav-category-title" style="color: var(--ula-gold-400);">{{ __('Super Admin') }}</li>
                <li>
                    <a href="{{ route('superadmin.dashboard') }}" class="sidebar-link-btn" style="color: var(--ula-gold-400);">
                        <span class="nav-icon"><span class="material-symbols-rounded" style="font-size: 1em; vertical-align: text-bottom;">workspace_premium</span></span>
                        <span class="nav-label-text">{{ __('Super Admin Portal') }}</span>
                    </a>
                </li>
            @endif
        </ul>

        <div class="sidebar-footer">
            <div style="display: flex; align-items: center; gap: 10px;">
                <div style="width: 32px; height: 32px; border-radius: 50%; background: var(--ula-gradient-accent); color: white; display: flex; align-items: center; justify-content: center; font-size: 11px; font-weight: 900;">
                    {{ strtoupper(substr($user->name, 0, 2)) }}
                </div>
                <div class="user-info-text">
                    <div style="font-size: 12px; font-weight: 800; color: var(--ula-white);">{{ $user->name }}</div>
                    <div style="font-size: 10px; color: var(--ula-text-on-dark-muted);">{{ $membership->role->name ?? 'Member' }}</div>
                </div>
            </div>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" style="background: transparent; border: none; color: var(--ula-text-on-dark-muted); cursor: pointer; font-size: 14px;" title="{{ __('Logout') }}">
                    <span class="material-symbols-rounded" style="font-size: 1em; vertical-align: text-bottom;">door_front</span>
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
                    <span class="material-symbols-rounded" style="font-size: 1em; vertical-align: text-bottom;">menu</span>
                </button>

                <div class="breadcrumb-trail">
                    <a href="{{ route('dashboard') }}#overview"><span class="material-symbols-rounded" style="font-size: 1em; vertical-align: text-bottom;">eco</span> {{ __('Workspace') }}</a>
                    <span class="breadcrumb-separator">/</span>
                    <a href="{{ route('dashboard') }}#projects"><span class="material-symbols-rounded" style="font-size: 1em; vertical-align: text-bottom;">folder</span> {{ __('Projects') }}</a>
                    <span class="breadcrumb-separator">/</span>
                    <span style="color: var(--ula-text-primary); font-weight: 900;">{{ $project->name }} ({{ $project->code }})</span>
                </div>
            </div>

            <div style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap;">
                <!-- Language Switcher -->
                <a href="{{ route('lang.switch', app()->getLocale() === 'ar' ? 'en' : 'ar') }}" class="tactile-btn btn-secondary" style="padding: 7px 12px; font-size: 12px;">
                    <span class="material-symbols-rounded" style="font-size: 1em; vertical-align: text-bottom;">public</span> {{ app()->getLocale() === 'ar' ? 'English' : 'العربية' }}
                </a>

                <!-- Theme Toggle -->
                <button onclick="toggleThemeMode()" class="tactile-btn btn-secondary" style="padding: 7px 12px; font-size: 12px;" title="{{ __('Toggle Dark / Light Mode') }}">
                    <span class="theme-toggle-icon-label"><span class="material-symbols-rounded" style="font-size: 1em; vertical-align: text-bottom;">dark_mode</span></span>
                </button>

                <!-- Office Shortcut -->
                <a href="{{ route('office') }}" class="tactile-btn btn-primary" style="padding: 7px 14px; font-size: 12px;">
                    <span class="material-symbols-rounded" style="font-size: 1em; vertical-align: text-bottom;">rocket_launch</span> {{ __('Enter Office') }}
                </a>
            </div>
        </header>

        <main class="hub-main-container">

            <!-- Active Timer Bar if running -->
            @if($activeTimer)
                <div style="background: linear-gradient(135deg, rgba(79, 155, 95, 0.15) 0%, rgba(255, 253, 246, 0.95) 100%); border: 1px solid var(--ula-palm-900); border-radius: var(--ula-radius-lg); padding: 14px 20px; display: flex; align-items: center; justify-content: space-between; margin-bottom: 20px; box-shadow: var(--ula-shadow-xs);">
                    <div style="display: flex; align-items: center; gap: 14px;">
                        <div style="width: 36px; height: 36px; border-radius: 50%; background: var(--ula-palm-900); color: white; display: flex; align-items: center; justify-content: center; font-size: 16px; box-shadow: var(--ula-shadow-xs);">
                            <span class="material-symbols-rounded" style="font-size: 1em; vertical-align: text-bottom;">timer</span>
                        </div>
                        <div>
                            <div style="font-size: 11px; font-weight: 800; color: var(--ula-text-primary); text-transform: uppercase;">
                                {{ __('Active Timer Running') }} • {{ $activeTimer->project->name ?? 'Project' }}
                            </div>
                            <div style="font-size: 14px; font-weight: 800; color: var(--ula-text-primary);">
                                {{ $activeTimer->task->title ?? ($activeTimer->description ?? 'Work Session') }}
                            </div>
                        </div>
                    </div>
                    <div style="display: flex; align-items: center; gap: 14px;">
                        <span id="hub-live-timer-clock" style="font-size: 20px; font-weight: 900; font-family: monospace; color: var(--ula-text-primary);">00:00:00</span>
                        <button onclick="stopHubGlobalTimer()" class="tactile-btn" style="background: var(--ula-terracotta-200); color: var(--ula-terracotta-600); border: 1px solid var(--ula-terracotta-300); padding: 6px 14px; font-size: 12px;">
                            <span class="material-symbols-rounded" style="font-size: 1em; vertical-align: text-bottom;">stop_circle</span> {{ __('Stop Timer') }}
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
                            <h1 style="font-size: 26px; font-weight: 900; color: var(--ula-text-primary); margin: 0; line-height: 1.2;">
                                {{ $project->name }}
                            </h1>
                            <span class="badge-pill {{ $project->status === 'completed' ? 'badge-green' : ($project->status === 'active' ? 'badge-green' : 'badge-gold') }}">
                                {{ ucfirst($project->status) }}
                            </span>
                            <span class="badge-pill {{ $project->priority === 'urgent' ? 'badge-danger' : ($project->priority === 'high' ? 'badge-gold' : 'badge-neutral') }}">
                                <span class="material-symbols-rounded" style="font-size: 1em; vertical-align: text-bottom;">bolt</span> {{ ucfirst($project->priority) }}
                            </span>
                        </div>

                        <p style="font-size: 13px; color: var(--ula-text-secondary); margin-bottom: 16px; line-height: 1.6;">
                            {{ $project->description ?? __('Collaborative workspace project for cross-functional execution, task delivery, timesheets, and team meetings.') }}
                        </p>

                        <div style="display: flex; align-items: center; gap: 20px; font-size: 12px; color: var(--ula-text-muted); flex-wrap: wrap;">
                            <span><span class="material-symbols-rounded" style="font-size: 1em; vertical-align: text-bottom;">person</span> {{ __('Manager') }}: <strong style="color: var(--ula-text-primary);">{{ $project->manager->name ?? __('Unassigned') }}</strong></span>
                            <span><span class="material-symbols-rounded" style="font-size: 1em; vertical-align: text-bottom;">account_balance</span> {{ __('Department') }}: <strong style="color: var(--ula-text-primary);">{{ $project->department->name ?? __('General') }}</strong></span>
                            <span><span class="material-symbols-rounded" style="font-size: 1em; vertical-align: text-bottom;">calendar_month</span> {{ __('Due Date') }}: <strong style="color: var(--ula-text-primary);">{{ $project->end_date ? $project->end_date->format('M d, Y') : __('No deadline') }}</strong></span>
                            <span><span class="material-symbols-rounded" style="font-size: 1em; vertical-align: text-bottom;">group</span> {{ __('Project Team') }}: <strong style="color: var(--ula-text-primary);">{{ $project->members->count() }} {{ __('Members') }}</strong></span>
                        </div>
                    </div>

                    <!-- Action Toolbar -->
                    <div style="display: flex; gap: 10px; align-items: center; flex-wrap: wrap;">
                        <button onclick="openNewTaskModal()" class="tactile-btn btn-primary">
                            <span>+</span> {{ __('Create Task') }}
                        </button>
                        <button onclick="openManualTimeModal()" class="tactile-btn btn-secondary">
                            <span><span class="material-symbols-rounded" style="font-size: 1em; vertical-align: text-bottom;">timer</span></span> {{ __('Log Manual Time Entry') }}
                        </button>
                        <button onclick="openScheduleProjectMeetingModal()" class="tactile-btn btn-secondary">
                            <span><span class="material-symbols-rounded" style="font-size: 1em; vertical-align: text-bottom;">calendar_month</span></span> {{ __('Schedule Project Meeting') }}
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
                        <div class="kpi-icon-box"><span class="material-symbols-rounded" style="font-size: 1em; vertical-align: text-bottom;">bar_chart</span></div>
                    </div>
                    <div class="kpi-value" style="color: var(--ula-text-primary);">{{ $kpis['progress_pct'] ?? 0 }}%</div>
                    <div style="width: 100%; background: var(--ula-surface-page-alt); height: 7px; border-radius: 9999px; overflow: hidden; margin-bottom: 6px;">
                        <div style="width: {{ $kpis['progress_pct'] ?? 0 }}%; height: 100%; background: var(--ula-palm-900); border-radius: 9999px;"></div>
                    </div>
                    <div style="font-size: 11px; color: var(--ula-text-muted); display: flex; justify-content: space-between;">
                        <span>{{ $kpis['completed_tasks'] ?? 0 }} / {{ $kpis['total_tasks'] ?? 0 }} {{ __('done') }}</span>
                        @if(($kpis['overdue_tasks'] ?? 0) > 0)
                            <span style="color: var(--ula-status-danger); font-weight: 800;"><span class="material-symbols-rounded" style="font-size: 1em; vertical-align: text-bottom;">warning</span> {{ $kpis['overdue_tasks'] }} {{ __('overdue') }}</span>
                        @endif
                    </div>
                </div>

                <!-- 2. Hours & Effort -->
                <div class="kpi-card">
                    <div class="kpi-header">
                        <span class="kpi-title">{{ __('Actual vs Planned') }}</span>
                        <div class="kpi-icon-box"><span class="material-symbols-rounded" style="font-size: 1em; vertical-align: text-bottom;">timer</span></div>
                    </div>
                    <div class="kpi-value">{{ $kpis['actual_hours'] ?? 0 }}h</div>
                    <div style="font-size: 11px; color: var(--ula-text-secondary); margin-bottom: 4px;">
                        {{ __('Planned') }}: <strong>{{ $kpis['planned_hours'] ?? 0 }}h</strong>
                    </div>
                    <div style="font-size: 11px; color: {{ ($kpis['hours_variance'] ?? 0) < 0 ? 'var(--ula-status-danger)' : 'var(--ula-palm-900)' }}; font-weight: 800;">
                        {{ ($kpis['hours_variance'] ?? 0) >= 0 ? '+' : '' }}{{ $kpis['hours_variance'] ?? 0 }}h {{ __('variance') }}
                    </div>
                </div>

                <!-- 3. Financials & Budget -->
                <div class="kpi-card">
                    <div class="kpi-header">
                        <span class="kpi-title">{{ __('Budget & Cost') }}</span>
                        <div class="kpi-icon-box"><span class="material-symbols-rounded" style="font-size: 1em; vertical-align: text-bottom;">payments</span></div>
                    </div>
                    <div class="kpi-value">${{ number_format($kpis['labor_cost'] ?? 0, 2) }}</div>
                    <div style="font-size: 11px; color: var(--ula-text-secondary); margin-bottom: 4px;">
                        {{ __('Budget') }}: <strong>${{ number_format($kpis['budget'] ?? $kpis['budget_amount'] ?? 0, 2) }}</strong>
                    </div>
                    <div style="font-size: 11px; color: {{ ($kpis['budget_variance'] ?? 0) < 0 ? 'var(--ula-status-danger)' : 'var(--ula-palm-900)' }}; font-weight: 800;">
                        {{ ($kpis['budget_variance'] ?? 0) >= 0 ? __('Remaining') : __('Over') }}: ${{ number_format(abs($kpis['budget_variance'] ?? 0), 2) }}
                    </div>
                </div>

                <!-- 4. Revenue & Margin -->
                <div class="kpi-card">
                    <div class="kpi-header">
                        <span class="kpi-title">{{ __('Revenue & Margin') }}</span>
                        <div class="kpi-icon-box"><span class="material-symbols-rounded" style="font-size: 1em; vertical-align: text-bottom;">trending_up</span></div>
                    </div>
                    <div class="kpi-value" style="color: var(--ula-text-primary);">${{ number_format($kpis['billable_revenue'] ?? 0, 2) }}</div>
                    <div style="font-size: 11px; color: var(--ula-text-secondary); margin-bottom: 4px;">
                        {{ __('Gross Margin') }}: <strong>${{ number_format($kpis['gross_margin'] ?? 0, 2) }}</strong>
                    </div>
                    <div style="font-size: 11px; color: var(--ula-status-success); font-weight: 800;">
                        {{ $kpis['gross_margin_pct'] ?? 0 }}% {{ __('Margin Rate') }}
                    </div>
                </div>

                <!-- 5. Sprint Workload -->
                <div class="kpi-card">
                    <div class="kpi-header">
                        <span class="kpi-title">{{ __('Sprint Workload') }}</span>
                        <div class="kpi-icon-box"><span class="material-symbols-rounded" style="font-size: 1em; vertical-align: text-bottom;">bolt</span></div>
                    </div>
                    <div class="kpi-value">{{ ($kpis['in_progress_tasks'] ?? 0) + ($kpis['review_tasks'] ?? 0) }}</div>
                    <div style="font-size: 11px; color: var(--ula-text-secondary); margin-bottom: 4px;">
                        <span class="material-symbols-rounded" style="font-size: 1em; vertical-align: text-bottom;">bolt</span> {{ $kpis['in_progress_tasks'] ?? 0 }} {{ __('in progress') }} • <span class="material-symbols-rounded" style="font-size: 1em; vertical-align: text-bottom;">search</span> {{ $kpis['review_tasks'] ?? 0 }} {{ __('in review') }}
                    </div>
                    <div style="font-size: 11px; color: var(--ula-text-muted); font-weight: 700;">
                        <span class="material-symbols-rounded" style="font-size: 1em; vertical-align: text-bottom;">push_pin</span> {{ $kpis['backlog_tasks'] ?? 0 }} {{ __('in backlog/ready') }}
                    </div>
                </div>

                <!-- 6. Team Meetings -->
                <div class="kpi-card">
                    <div class="kpi-header">
                        <span class="kpi-title">{{ __('Team Meetings') }}</span>
                        <div class="kpi-icon-box"><span class="material-symbols-rounded" style="font-size: 1em; vertical-align: text-bottom;">calendar_month</span></div>
                    </div>
                    <div class="kpi-value">{{ $upcomingProjectMeetings->count() }}</div>
                    <div style="font-size: 11px; color: var(--ula-text-primary); margin-bottom: 4px; font-weight: 800;">
                        <span class="material-symbols-rounded" style="font-size: 1em; vertical-align: text-bottom;">circle</span> {{ __('Ready for collaboration') }}
                    </div>
                    <div style="font-size: 11px; color: var(--ula-text-muted);">
                        {{ $projectMeetings->count() }} {{ __('total sessions held') }}
                    </div>
                </div>
            </div>

            <!-- ── 5. CLICKUP-GRADE MULTI-VIEWS NAVIGATION BAR ── -->
            <div class="hub-tabs-nav">
                <button onclick="switchHubSection('kanban')" id="hub-nav-btn-kanban" class="hub-tab-btn active">
                    <span class="material-symbols-rounded" style="font-size: 1em; vertical-align: text-bottom;">push_pin</span> {{ __('Kanban Board') }} (<span id="count-total-kanban">{{ $tasks->count() }}</span>)
                </button>
                <button onclick="switchHubSection('tasks')" id="hub-nav-btn-tasks" class="hub-tab-btn">
                    <span class="material-symbols-rounded" style="font-size: 1em; vertical-align: text-bottom;">assignment</span> {{ __('List View & Custom Fields') }}
                </button>
                <button onclick="switchHubSection('gantt')" id="hub-nav-btn-gantt" class="hub-tab-btn">
                    <span class="material-symbols-rounded" style="font-size: 1em; vertical-align: text-bottom;">bar_chart</span> {{ __('Gantt / Timeline') }}
                </button>
                <button onclick="switchHubSection('workload')" id="hub-nav-btn-workload" class="hub-tab-btn">
                    <span class="material-symbols-rounded" style="font-size: 1em; vertical-align: text-bottom;">trending_up</span> {{ __('Workload Matrix') }}
                </button>
                <button onclick="switchHubSection('docs')" id="hub-nav-btn-docs" class="hub-tab-btn">
                    <span class="material-symbols-rounded" style="font-size: 1em; vertical-align: text-bottom;">menu_book</span> {{ __('Docs & Wiki') }} ({{ $project->documents->count() }})
                </button>
                <button onclick="switchHubSection('goals')" id="hub-nav-btn-goals" class="hub-tab-btn">
                    <span class="material-symbols-rounded" style="font-size: 1em; vertical-align: text-bottom;">track_changes</span> {{ __('Goals & Targets') }} ({{ $project->goals->count() }})
                </button>
                <button onclick="switchHubSection('timelog')" id="hub-nav-btn-timelog" class="hub-tab-btn">
                    <span class="material-symbols-rounded" style="font-size: 1em; vertical-align: text-bottom;">timer</span> {{ __('Time & Margin') }} ({{ $project->timeEntries->count() }})
                </button>
                <button onclick="switchHubSection('meetings')" id="hub-nav-btn-meetings" class="hub-tab-btn">
                    <span class="material-symbols-rounded" style="font-size: 1em; vertical-align: text-bottom;">calendar_month</span> {{ __('Meetings') }} ({{ $upcomingProjectMeetings->count() }})
                </button>
                <button onclick="switchHubSection('team')" id="hub-nav-btn-team" class="hub-tab-btn">
                    <span class="material-symbols-rounded" style="font-size: 1em; vertical-align: text-bottom;">group</span> {{ __('Team Roster') }} ({{ $project->members->count() }})
                </button>
                <button onclick="switchHubSection('files')" id="hub-nav-btn-files" class="hub-tab-btn">
                    <span class="material-symbols-rounded" style="font-size: 1em; vertical-align: text-bottom;">folder</span> {{ __('Files & Docs') }} (<span id="count-total-files">{{ $project->files->count() }}</span>)
                </button>
                <button onclick="switchHubSection('milestones')" id="hub-nav-btn-milestones" class="hub-tab-btn">
                    <span class="material-symbols-rounded" style="font-size: 1em; vertical-align: text-bottom;">flag</span> {{ __('Roadmap') }} ({{ $project->milestones->count() }})
                </button>
            </div>

            <!-- ── 6. SUB-TAB VIEWS ── -->

            <!-- ── MULTI-CRITERIA TASK SEARCH & FILTER TOOLBAR ── -->
            <div class="hub-filter-bar">
                <!-- Search Input -->
                <div style="position: relative; flex: 1; min-width: 200px;">
                    <span style="position: absolute; inset-inline-start: 10px; top: 50%; transform: translateY(-50%); font-size: 13px; color: var(--ula-text-muted); pointer-events: none;"><span class="material-symbols-rounded" style="font-size: 1em; vertical-align: text-bottom;">search</span></span>
                    <input type="text" id="hub-task-search-input" oninput="filterHubTasks()" placeholder="{{ __('Search tasks by title, #number or tags...') }}" style="padding-inline-start: 32px; font-size: 12px; height: 38px; border-radius: var(--ula-radius-sm); width: 100%; background: var(--ula-surface-page-alt); border: 1px solid var(--ula-border-subtle); color: var(--ula-text-primary); outline: none;">
                </div>

                <!-- Assignee Filter -->
                <div style="min-width: 140px;">
                    <select id="hub-filter-assignee" onchange="filterHubTasks()" class="custom-select-control" style="width: 100%;">
                        <option value="">{{ __('All Assignees') }}</option>
                        <option value="unassigned">— {{ __('Unassigned') }} —</option>
                        @foreach($allMembers as $am)
                            <option value="{{ $am->user_id }}">{{ $am->user->name }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Priority Filter -->
                <div style="min-width: 130px;">
                    <select id="hub-filter-priority" onchange="filterHubTasks()" class="custom-select-control" style="width: 100%;">
                        <option value="">{{ __('All Priorities') }}</option>
                        <option value="urgent">{{ __('Urgent') }}</option>
                        <option value="high">{{ __('High') }}</option>
                        <option value="medium">{{ __('Medium') }}</option>
                        <option value="low">{{ __('Low') }}</option>
                    </select>
                </div>

                <!-- Due Date Filter -->
                <div style="min-width: 130px;">
                    <select id="hub-filter-due" onchange="filterHubTasks()" class="custom-select-control" style="width: 100%;">
                        <option value="">{{ __('All Due Dates') }}</option>
                        <option value="overdue">{{ __('Overdue') }}</option>
                        <option value="today">{{ __('Due Today') }}</option>
                        <option value="this_week">{{ __('Due This Week') }}</option>
                        <option value="has_due">{{ __('Has Due Date') }}</option>
                        <option value="no_due">{{ __('No Due Date') }}</option>
                    </select>
                </div>

                <!-- Milestone Filter -->
                <div style="min-width: 140px;">
                    <select id="hub-filter-milestone" onchange="filterHubTasks()" class="custom-select-control" style="width: 100%;">
                        <option value="">{{ __('All Milestones') }}</option>
                        <option value="none">— {{ __('No Milestone') }} —</option>
                        @foreach($project->milestones as $pms)
                            <option value="{{ $pms->id }}">{{ $pms->name }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Clear Filters Button -->
                <button type="button" onclick="clearHubTaskFilters()" id="hub-clear-filters-btn" class="tactile-btn btn-secondary" style="padding: 7px 12px; font-size: 11px; height: 38px; display: none; align-items: center; gap: 4px;">
                    <span class="material-symbols-rounded" style="font-size: 1em; vertical-align: text-bottom;">close</span> {{ __('Reset') }}
                </button>
            </div>

            <!-- TAB 1: KANBAN BOARD (DRAG & DROP SORTABLEJS) -->
            <div id="hub-section-kanban" class="hub-section-content" style="display: block;">
                <div class="kanban-grid">
                    @php
                        $columns = [
                            'backlog' => ['title' => '<span class="material-symbols-rounded" style="font-size: 1em; vertical-align: text-bottom;">push_pin</span> ' . __('Backlog'), 'color' => 'var(--ula-text-secondary)'],
                            'ready' => ['title' => '<span class="material-symbols-rounded" style="font-size: 1em; vertical-align: text-bottom;">track_changes</span> ' . __('Ready'), 'color' => 'var(--ula-palm-500)'],
                            'in_progress' => ['title' => '<span class="material-symbols-rounded" style="font-size: 1em; vertical-align: text-bottom;">bolt</span> ' . __('In Progress'), 'color' => 'var(--ula-palm-900)'],
                            'review' => ['title' => '<span class="material-symbols-rounded" style="font-size: 1em; vertical-align: text-bottom;">search</span> ' . __('Review / QA'), 'color' => 'var(--ula-status-warning)'],
                            'done' => ['title' => '<span class="material-symbols-rounded" style="font-size: 1em; vertical-align: text-bottom;">celebration</span> ' . __('Done'), 'color' => 'var(--ula-palm-900)'],
                        ];
                        $isProjectManager = ($user->isSuperAdmin() || $membership->role?->slug === 'company_admin' || ($project && $project->manager_id === $user->id));
                    @endphp

                    @foreach($columns as $colKey => $colMeta)
                        @php
                            $colTasks = $tasks->where('status', $colKey);
                        @endphp
                        <div class="kanban-column" id="kanban-column-{{ $colKey }}">
                            <div class="kanban-col-header" style="color: {{ $colMeta['color'] }};">
                                <span>{{ $colMeta['title'] }}</span>
                                <span class="badge-pill badge-neutral" id="kanban-count-{{ $colKey }}">{{ $colTasks->count() }}</span>
                            </div>

                            <div class="kanban-cards-container" id="kanban-cards-{{ $colKey }}" data-status="{{ $colKey }}" style="display: flex; flex-direction: column; gap: 10px; flex: 1; min-height: 140px;">
                                @foreach($colTasks as $t)
                                    @php
                                        $canEditThisTask = $user->can('update', $t);
                                    @endphp
                                    <div class="kanban-task-card kanban-card" 
                                         id="task-card-{{ $t->id }}"
                                         data-task-id="{{ $t->id }}"
                                         data-status="{{ $t->status }}"
                                         data-assignee="{{ $t->assignee_id ?? 'unassigned' }}"
                                         data-priority="{{ $t->priority ?? 'medium' }}"
                                         data-milestone="{{ $t->milestone_id ?? 'none' }}"
                                         data-due="{{ $t->due_date ? $t->due_date->format('Y-m-d') : '' }}"
                                         data-title="{{ strtolower($t->title) }} #{{ $t->task_number }}"
                                         onclick="openTaskInspector('{{ $t->id }}')"
                                         oncontextmenu="event.preventDefault(); event.stopPropagation(); openTaskContextMenu(event, '{{ $t->id }}', '{{ $project->id }}', '{{ addslashes($t->title) }}')">
                                        
                                        <!-- Header: Code & Action Buttons -->
                                        <div class="task-card-header">
                                            <div class="task-card-tags">
                                                <span class="task-code-badge">
                                                    #{{ $t->task_number }}
                                                </span>
                                                @if($t->checklistItems && $t->checklistItems->count() > 0)
                                                    <span class="badge-pill badge-green" style="font-size: 9.5px;" title="{{ __('Checklist Progress') }}">
                                                        ⊞ {{ $t->checklistItems->where('is_completed', true)->count() }}/{{ $t->checklistItems->count() }}
                                                    </span>
                                                @endif
                                                @if($t->isRecurring())
                                                    <span class="badge-pill" style="font-size: 9px; background: rgba(214, 162, 58, 0.15); color: var(--ula-gold-400); border: 1px solid rgba(214, 162, 58, 0.3);" title="{{ __('Recurring :rule', ['rule' => $t->recurrence_rule]) }}">
                                                        <span class="material-symbols-rounded" style="font-size: 1em; vertical-align: text-bottom;">refresh</span> {{ ucfirst($t->recurrence_rule) }}
                                                    </span>
                                                @endif
                                            </div>

                                            <div class="task-card-actions">
                                                @if($t->priority === 'urgent')
                                                    <span class="badge-pill badge-danger"><span class="material-symbols-rounded" style="font-size: 1em; vertical-align: text-bottom;">local_fire_department</span> {{ __('Urgent') }}</span>
                                                @elseif($t->priority === 'high')
                                                    <span class="badge-pill badge-gold"><span class="material-symbols-rounded" style="font-size: 1em; vertical-align: text-bottom;">bolt</span> {{ __('High') }}</span>
                                                @endif

                                                <button type="button" onclick="event.stopPropagation(); openTaskContextMenu(event, '{{ $t->id }}', '{{ $project->id }}', '{{ addslashes($t->title) }}')" class="task-dots-btn" title="{{ __('More actions') }}">
                                                    •••
                                                </button>
                                            </div>
                                        </div>

                                        <!-- Body: Title -->
                                        <h4 class="task-card-title" style="{{ $t->status === 'done' ? 'text-decoration: line-through; opacity: 0.6;' : '' }}">
                                            {{ $t->title }}
                                        </h4>

                                        <!-- Milestone Badge (if task belongs to a milestone) -->
                                        @if($t->milestone)
                                            <div class="task-card-milestone" title="{{ __('Milestone: :name', ['name' => $t->milestone->name]) }}">
                                                <span class="material-symbols-rounded" style="font-size: 1em; vertical-align: text-bottom;">flag</span> {{ $t->milestone->name }}
                                            </div>
                                        @endif

                                        @if($t->approval_status === 'pending_approval')
                                            <div style="background: rgba(214, 162, 58, 0.15); border: 1px solid rgba(214, 162, 58, 0.35); color: var(--ula-gold-400); font-size: 10px; font-weight: 800; padding: 4px 8px; border-radius: 8px; display: flex; align-items: center; justify-content: space-between; margin-bottom: 8px;">
                                                <span><span class="material-symbols-rounded" style="font-size: 1em; vertical-align: text-bottom;">hourglass_empty</span> {{ __('Pending PM Approval') }}</span>
                                                @if($isProjectManager)
                                                    <button type="button" onclick="event.stopPropagation(); quickApproveHubTask('{{ $t->id }}')" class="tactile-btn" style="background: var(--ula-status-success); color: white; border: none; padding: 2px 6px; font-size: 9px; border-radius: 4px;"><span class="material-symbols-rounded" style="font-size: 1em; vertical-align: text-bottom;">check</span> {{ __('Approve') }}</button>
                                                @endif
                                            </div>
                                        @elseif($t->approval_status === 'rejected')
                                            <div style="background: rgba(217, 107, 95, 0.15); border: 1px solid rgba(217, 107, 95, 0.35); color: var(--ula-status-danger); font-size: 10px; font-weight: 800; padding: 4px 8px; border-radius: 8px; margin-bottom: 8px;">
                                                <span><span class="material-symbols-rounded" style="font-size: 1em; vertical-align: text-bottom;">warning</span> {{ __('Changes Requested') }}</span>
                                            </div>
                                        @endif

                                        <!-- Metadata: Assignee & Due Date -->
                                        <div class="task-card-meta">
                                            <span class="task-project-name"><span class="material-symbols-rounded" style="font-size: 1em; vertical-align: text-bottom;">folder</span> {{ $project->name }}</span>
                                            <div style="display: flex; align-items: center; gap: 6px;">
                                                @if($t->assignee)
                                                    <div class="task-assignee-chip" title="{{ $t->assignee->name }}">
                                                        <div class="task-avatar-circle">
                                                            {{ strtoupper(substr($t->assignee->name, 0, 2)) }}
                                                        </div>
                                                        <span style="max-width: 65px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">{{ explode(' ', $t->assignee->name)[0] }}</span>
                                                    </div>
                                                @endif
                                                @if($t->due_date)
                                                    <span class="task-due-date {{ $t->due_date->isPast() && $t->status !== 'done' ? 'is-overdue' : '' }}">
                                                        <span class="material-symbols-rounded" style="font-size: 1em; vertical-align: text-bottom;">calendar_month</span> {{ $t->due_date->format('M d') }}
                                                    </span>
                                                @endif
                                            </div>
                                        </div>

                                        <!-- Footer: Direct Status Dropdown & Timer -->
                                        <div class="task-card-footer">
                                            <div style="display: flex; align-items: center; gap: 6px; flex: 1; min-width: 0;">
                                                <select onclick="event.stopPropagation()" onchange="updateHubTaskStatusDirect('{{ $t->id }}', this.value)" class="card-status-select" style="max-width: 100%;">
                                                    <option value="backlog" {{ $t->status === 'backlog' ? 'selected' : '' }}>{{ __('Backlog') }}</option>
                                                    <option value="ready" {{ $t->status === 'ready' ? 'selected' : '' }}>{{ __('Ready') }}</option>
                                                    <option value="in_progress" {{ $t->status === 'in_progress' ? 'selected' : '' }}>{{ __('In Progress') }}</option>
                                                    <option value="review" {{ $t->status === 'review' || $t->status === 'qa' ? 'selected' : '' }}>{{ __('Review') }}</option>
                                                    <option value="done" {{ $t->status === 'done' ? 'selected' : '' }}>{{ __('Done') }}</option>
                                                </select>
                                            </div>

                                            <button type="button" onclick="event.stopPropagation(); startHubTaskTimerDirect('{{ $project->id }}', '{{ $t->id }}', '{{ addslashes($t->title) }}', '{{ addslashes($project->name) }}')" class="tactile-btn" style="background: rgba(79, 155, 95, 0.15); color: var(--ula-text-primary); border: 1px solid rgba(79, 155, 95, 0.3); padding: 3px 8px; font-size: 10.5px; border-radius: var(--ula-radius-pill); font-weight: 800; white-space: nowrap; flex-shrink: 0;" title="{{ __('Start Timer') }}">
                                                <span class="material-symbols-rounded" style="font-size: 1em; vertical-align: text-bottom;">play_arrow</span> {{ round($t->logged_hours ?? $t->actual_hours ?? 0, 1) }}h
                                            </button>
                                        </div>
                                    </div>
                                @endforeach

                                <div class="kanban-empty-drop-hint" style="display: {{ $colTasks->count() === 0 ? 'block' : 'none' }}; text-align: center; padding: 24px 10px; color: var(--ula-text-muted); font-size: 11px; border: 1px dashed var(--ula-border-subtle); border-radius: var(--ula-radius-sm);">
                                    {{ __('Drag tasks here...') }}
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- TAB 2: TASKS MATRIX & TABLE -->
            <div id="hub-section-tasks" class="hub-section-content" style="display: none;">
                <div class="hub-card" style="padding: 0; overflow: hidden;">
                    <div style="padding: 18px 24px; border-bottom: 1px solid var(--ula-border-subtle); display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px;">
                        <div>
                            <h3 style="font-size: 16px; font-weight: 900; color: var(--ula-text-primary);"><span class="material-symbols-rounded" style="font-size: 1em; vertical-align: text-bottom;">assignment</span> {{ __('Project Task Inventory & Sprints') }} ({{ $tasks->count() }})</h3>
                            <p style="font-size: 12px; color: var(--ula-text-muted); margin-top: 2px;">{{ __('Filter and inspect all deliverable tasks, checklist completion, and predecessor dependencies.') }}</p>
                        </div>
                        <button onclick="openNewTaskModal()" class="tactile-btn btn-primary" style="padding: 8px 16px; font-size: 12px;">
                            + {{ __('Create Task') }}
                        </button>
                    </div>

                    <!-- ── BULK ACTION TOOLBAR (appears when tasks are selected) ── -->
                    <div id="bulk-action-toolbar" style="display: none; padding: 10px 24px; background: var(--ula-gradient-accent); border-bottom: 1px solid var(--ula-palm-700); align-items: center; gap: 12px; flex-wrap: wrap;">
                        <span id="bulk-selected-count" style="font-size: 13px; font-weight: 900; color: #fff;">0 {{ __('selected') }}</span>
                        <div style="display: flex; gap: 8px; flex-wrap: wrap;">
                            <!-- Bulk Status -->
                            <select id="bulk-status-select" onchange="bulkChangeStatus()" style="padding: 5px 10px; border-radius: 6px; border: 1px solid rgba(255,255,255,0.3); background: rgba(255,255,255,0.1); color: #fff; font-size: 11px; font-weight: 700; cursor: pointer;">
                                <option value="" disabled selected style="color:#000;">{{ __('Set Status…') }}</option>
                                <option value="backlog" style="color:#000;">{{ __('Backlog') }}</option>
                                <option value="ready" style="color:#000;">{{ __('Ready') }}</option>
                                <option value="in_progress" style="color:#000;">{{ __('In Progress') }}</option>
                                <option value="review" style="color:#000;">{{ __('Review') }}</option>
                                <option value="qa" style="color:#000;">{{ __('QA') }}</option>
                                <option value="done" style="color:#000;">{{ __('Done') }}</option>
                            </select>
                            <!-- Bulk Assign -->
                            <select id="bulk-assign-select" onchange="bulkAssign()" style="padding: 5px 10px; border-radius: 6px; border: 1px solid rgba(255,255,255,0.3); background: rgba(255,255,255,0.1); color: #fff; font-size: 11px; font-weight: 700; cursor: pointer;">
                                <option value="" disabled selected style="color:#000;">{{ __('Assign to…') }}</option>
                                @foreach($projectMembers ?? [] as $pm)
                                    <option value="{{ $pm->user->id }}" style="color:#000;">{{ $pm->user->name }}</option>
                                @endforeach
                                <option value="null" style="color:#000;">{{ __('Unassign') }}</option>
                            </select>
                            <!-- Bulk Delete -->
                            <button onclick="bulkDeleteTasks()" style="padding: 5px 12px; border-radius: 6px; background: rgba(239,68,68,0.25); border: 1px solid rgba(239,68,68,0.5); color: var(--ula-status-danger); font-size: 11px; font-weight: 800; cursor: pointer;">
                                <span class="material-symbols-rounded" style="font-size: 1em; vertical-align: text-bottom;">delete</span> {{ __('Delete Selected') }}
                            </button>
                        </div>
                        <button onclick="clearBulkSelection()" style="margin-inline-start: auto; padding: 5px 10px; border-radius: 6px; background: rgba(255,255,255,0.1); border: 1px solid rgba(255,255,255,0.2); color: rgba(255,255,255,0.7); font-size: 11px; cursor: pointer;">
                            <span class="material-symbols-rounded" style="font-size: 1em; vertical-align: text-bottom;">close</span> {{ __('Clear') }}
                        </button>
                    </div>

                    <div style="overflow-x: auto;">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th style="width: 36px;">
                                        <input type="checkbox" id="bulk-select-all" onchange="toggleSelectAllTasks(this)" title="{{ __('Select All') }}" style="width: 15px; height: 15px; cursor: pointer; accent-color: var(--ula-palm-900);">
                                    </th>
                                    <th>#</th>
                                    <th>{{ __('Task Title') }}</th>
                                    <th><span class="material-symbols-rounded" style="font-size: 1em; vertical-align: text-bottom;">flag</span> {{ __('Milestone') }}</th>
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
                                    <tr onclick="handleTaskRowClick(event, '{{ $t->id }}')" 
                                        oncontextmenu="event.preventDefault(); event.stopPropagation(); openTaskContextMenu(event, '{{ $t->id }}', '{{ $project->id }}', '{{ addslashes($t->title) }}')"
                                        data-task-id="{{ $t->id }}"
                                        data-milestone="{{ $t->milestone_id ?? 'none' }}"
                                        style="cursor: pointer; transition: background 0.15s;">
                                        <td onclick="event.stopPropagation();" style="width: 36px;">
                                            <input type="checkbox" class="task-bulk-checkbox" value="{{ $t->id }}"
                                                onchange="onBulkCheckboxChange()"
                                                style="width: 15px; height: 15px; cursor: pointer; accent-color: var(--ula-palm-900);">
                                        </td>
                                        <td style="font-family: monospace; font-weight: 900; color: var(--ula-text-muted);">
                                            #{{ $t->task_number }}
                                        </td>
                                        <td>
                                            <div style="font-weight: 800; color: var(--ula-text-primary); font-size: 13px; display: flex; align-items: center; gap: 6px;">
                                                <span>{{ $t->title }}</span>
                                                @if($t->isRecurring())
                                                    <span class="badge-pill" style="font-size: 9px; background: rgba(214, 162, 58, 0.15); color: var(--ula-gold-400); border: 1px solid rgba(214, 162, 58, 0.3);" title="{{ __('Recurring :rule', ['rule' => $t->recurrence_rule]) }}">
                                                        <span class="material-symbols-rounded" style="font-size: 1em; vertical-align: text-bottom;">refresh</span> {{ ucfirst($t->recurrence_rule) }}
                                                    </span>
                                                @endif
                                                @if($t->checklistItems && $t->checklistItems->count() > 0)
                                                    <span class="badge-pill" style="font-size: 9px; background: rgba(79, 155, 95, 0.15); color: var(--ula-status-success);">⊞ {{ $t->checklistItems->where('is_completed', true)->count() }}/{{ $t->checklistItems->count() }}</span>
                                                @endif
                                            </div>
                                            @if($t->description)
                                                <div style="font-size: 11px; color: var(--ula-text-muted);">{{ Str::limit($t->description, 50) }}</div>
                                            @endif
                                        </td>
                                        <td>
                                            @if($t->milestone)
                                                <span class="badge-pill" style="font-size: 10px; font-weight: 700; background: rgba(79, 155, 95, 0.12); color: var(--ula-text-primary);">
                                                    <span class="material-symbols-rounded" style="font-size: 1em; vertical-align: text-bottom;">flag</span> {{ $t->milestone->name }}
                                                </span>
                                            @else
                                                <span style="font-size: 11px; color: var(--ula-text-muted);">—</span>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="badge-pill {{ $t->status === 'done' ? 'badge-green' : ($t->status === 'in_progress' ? 'badge-green' : 'badge-gold') }}">
                                                {{ ucfirst(str_replace('_', ' ', $t->status)) }}
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge-pill {{ $t->priority === 'urgent' ? 'badge-danger' : ($t->priority === 'high' ? 'badge-gold' : 'badge-neutral') }}">
                                                {!! $t->priority === 'urgent' ? '<span class="material-symbols-rounded" style="font-size: 1em; vertical-align: text-bottom;">flag</span> ' . __('Urgent') : ($t->priority === 'high' ? '<span class="material-symbols-rounded" style="font-size: 1em; vertical-align: text-bottom;">bolt</span> ' . __('High') : ucfirst($t->priority)) !!}
                                            </span>
                                        </td>
                                        <td>
                                            <div style="display: flex; align-items: center; gap: 6px;">
                                                <div style="width: 22px; height: 22px; border-radius: 50%; background: var(--ula-gradient-accent); color: white; display: flex; align-items: center; justify-content: center; font-size: 9px; font-weight: 800;">
                                                    {{ strtoupper(substr($t->assignee->name ?? 'U', 0, 1)) }}
                                                </div>
                                                <span style="font-weight: 700;">{{ $t->assignee->name ?? __('Unassigned') }}</span>
                                            </div>
                                        </td>
                                        <td style="font-family: monospace; font-weight: 800;">
                                            <span style="color: var(--ula-text-primary);">{{ $t->actual_hours ?? 0 }}h</span>
                                            <span style="color: var(--ula-text-muted);">/ {{ $t->estimated_hours ?? 0 }}h</span>
                                        </td>
                                        <td>
                                            @php
                                                $checks = $t->checklistItems;
                                                $checkDone = $checks->where('is_completed', true)->count();
                                                $checkTotal = $checks->count();
                                            @endphp
                                            @if($checkTotal > 0)
                                                <span class="badge-pill badge-neutral" style="font-size: 10px;"><span class="material-symbols-rounded" style="font-size: 1em; vertical-align: text-bottom;">check_box</span> {{ $checkDone }}/{{ $checkTotal }}</span>
                                            @else
                                                <span style="font-size: 11px; color: var(--ula-text-muted);">—</span>
                                            @endif
                                        </td>
                                        <td>
                                            <span style="font-size: 12px; font-weight: 700; color: {{ $t->due_date && $t->due_date->isPast() && $t->status !== 'done' ? 'var(--ula-status-danger)' : 'var(--ula-text-secondary)' }};">
                                                {{ $t->due_date ? $t->due_date->format('M d, Y') : '—' }}
                                            </span>
                                        </td>
                                        <td onclick="event.stopPropagation();">
                                            <div style="display: flex; gap: 6px;">
                                                <button onclick="openTaskInspector('{{ $t->id }}')" class="tactile-btn btn-secondary" style="padding: 4px 10px; font-size: 11px;">
                                                    <span class="material-symbols-rounded" style="font-size: 1em; vertical-align: text-bottom;">search</span> {{ __('Inspect') }}
                                                </button>
                                                <button onclick="openTaskContextMenu(event, '{{ $t->id }}', '{{ $project->id }}', '{{ addslashes($t->title) }}')" class="tactile-btn btn-secondary" style="padding: 4px 8px; font-size: 11px;" title="{{ __('More Actions') }}">
                                                    •••
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="11" style="text-align: center; color: var(--ula-text-muted); padding: 40px;">
                                            <div style="font-size: 32px; margin-bottom: 8px;"><span class="material-symbols-rounded" style="font-size: 1em; vertical-align: text-bottom;">assignment</span></div>
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
                    <div style="padding: 18px 24px; border-bottom: 1px solid var(--ula-border-subtle); display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px;">
                        <div>
                            <h3 style="font-size: 16px; font-weight: 900; color: var(--ula-text-primary);"><span class="material-symbols-rounded" style="font-size: 1em; vertical-align: text-bottom;">timer</span> {{ __('Work Sessions & Time Tracking Log') }} ({{ $project->timeEntries->count() }})</h3>
                            <p style="font-size: 12px; color: var(--ula-text-muted); margin-top: 2px;">{{ __('Chronological presence log of all billable and non-billable labor recorded on this project.') }}</p>
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
                                    <td style="font-size: 12px; font-weight: 700; color: var(--ula-text-secondary);">
                                        {{ $te->started_at ? $te->started_at->format('M d, Y H:i') : '—' }}
                                    </td>
                                    <td>
                                        <div style="display: flex; align-items: center; gap: 6px;">
                                            <div style="width: 22px; height: 22px; border-radius: 50%; background: var(--ula-gradient-accent); color: white; display: flex; align-items: center; justify-content: center; font-size: 9px; font-weight: 800;">
                                                {{ strtoupper(substr($te->user->name ?? 'U', 0, 1)) }}
                                            </div>
                                            <strong style="color: var(--ula-text-primary);">{{ $te->user->name ?? 'Member' }}</strong>
                                        </div>
                                    </td>
                                    <td>
                                        <span style="font-weight: 700; color: var(--ula-text-primary);">
                                            {{ $te->task ? '#' . $te->task->task_number . ' ' . $te->task->title : __('General Project Work') }}
                                        </span>
                                    </td>
                                    <td style="font-family: monospace; font-weight: 900; color: var(--ula-text-primary); font-size: 14px;">
                                        {{ number_format($te->duration_seconds / 3600, 2) }}h
                                    </td>
                                    <td style="font-size: 12px; color: var(--ula-text-secondary);">
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
                                    <td colspan="7" style="text-align: center; color: var(--ula-text-muted); padding: 40px;">
                                        <div style="font-size: 32px; margin-bottom: 8px;"><span class="material-symbols-rounded" style="font-size: 1em; vertical-align: text-bottom;">timer</span></div>
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
                    <div style="padding: 18px 24px; border-bottom: 1px solid var(--ula-border-subtle); display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px;">
                        <div>
                            <h3 style="font-size: 16px; font-weight: 900; color: var(--ula-text-primary);"><span class="material-symbols-rounded" style="font-size: 1em; vertical-align: text-bottom;">calendar_month</span> {{ __('Project Video Meetings & Standups') }} ({{ $projectMeetings->count() }})</h3>
                            <p style="font-size: 12px; color: var(--ula-text-muted); margin-top: 2px;">{{ __('Scheduled collaboration rooms with instant automated team invitations and chime audio alerts.') }}</p>
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
                                        <div style="font-weight: 800; color: var(--ula-text-primary); font-size: 13px;">{{ $m->title }}</div>
                                        @if($m->description)
                                            <div style="font-size: 11px; color: var(--ula-text-muted);">{{ Str::limit($m->description, 40) }}</div>
                                        @endif
                                    </td>
                                    <td>
                                        <div style="font-weight: 700; color: var(--ula-text-primary); font-size: 12px;">
                                            {{ $m->scheduled_at ? $m->scheduled_at->format('M d, Y') : __('Instant') }}
                                        </div>
                                        <div style="font-size: 11px; color: var(--ula-text-muted);">
                                            {{ $m->scheduled_at ? $m->scheduled_at->format('h:i A') : $m->created_at->format('h:i A') }}
                                        </div>
                                    </td>
                                    <td style="font-size: 12px; font-weight: 700; color: var(--ula-text-secondary);">
                                        {{ $m->duration_minutes ?? 30 }} {{ __('Minutes') }}
                                    </td>
                                    <td>
                                        <strong style="color: var(--ula-text-primary); font-size: 12px;"><span class="material-symbols-rounded" style="font-size: 1em; vertical-align: text-bottom;">door_front</span> {{ $m->room->name ?? 'Meeting Room' }}</strong>
                                    </td>
                                    <td>
                                        <span style="font-weight: 700; font-size: 12px;">{{ $m->creator->name ?? 'Admin' }}</span>
                                    </td>
                                    <td>
                                        <div style="display: flex; align-items: center;">
                                            @foreach($mParts as $p)
                                                <div style="width: 22px; height: 22px; border-radius: 50%; background: var(--ula-gradient-accent); color: white; font-size: 9px; font-weight: 800; display: flex; align-items: center; justify-content: center; border: 2px solid var(--ula-surface-card); margin-inline-start: -6px;" title="{{ $p->user->name ?? 'Attendee' }}">
                                                    {{ strtoupper(substr($p->user->name ?? 'A', 0, 1)) }}
                                                </div>
                                            @endforeach
                                            @if($moreParts > 0)
                                                <div style="width: 22px; height: 22px; border-radius: 50%; background: var(--ula-palm-900); color: white; font-size: 9px; font-weight: 800; display: flex; align-items: center; justify-content: center; border: 2px solid var(--ula-surface-card); margin-inline-start: -6px;">
                                                    +{{ $moreParts }}
                                                </div>
                                            @endif
                                        </div>
                                    </td>
                                    <td>
                                        @if($isLive)
                                            <span class="badge-pill badge-danger" style="animation: pulse 1.5s infinite;"><span class="material-symbols-rounded" style="font-size: 1em; vertical-align: text-bottom;">circle</span> LIVE</span>
                                        @elseif($m->status === 'scheduled')
                                            <span class="badge-pill badge-green"><span class="material-symbols-rounded" style="font-size: 1em; vertical-align: text-bottom;">calendar_month</span> {{ __('Scheduled') }}</span>
                                        @elseif($m->status === 'ended')
                                            <span class="badge-pill badge-neutral">{{ __('Completed') }}</span>
                                        @else
                                            <span class="badge-pill badge-neutral">{{ ucfirst($m->status) }}</span>
                                        @endif
                                    </td>
                                    <td>
                                        <a href="{{ route('office') }}" class="tactile-btn btn-primary" style="padding: 5px 12px; font-size: 11px; text-decoration: none;">
                                            <span class="material-symbols-rounded" style="font-size: 1em; vertical-align: text-bottom;">rocket_launch</span> {{ __('Join') }}
                                        </a>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="8" style="text-align: center; color: var(--ula-text-muted); padding: 40px;">
                                        <div style="font-size: 32px; margin-bottom: 8px;"><span class="material-symbols-rounded" style="font-size: 1em; vertical-align: text-bottom;">calendar_month</span></div>
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
                    <div style="padding: 18px 24px; border-bottom: 1px solid var(--ula-border-subtle);">
                        <h3 style="font-size: 16px; font-weight: 900; color: var(--ula-text-primary);"><span class="material-symbols-rounded" style="font-size: 1em; vertical-align: text-bottom;">group</span> {{ __('Project Team Roster & Allocation') }} ({{ $project->members->count() }})</h3>
                        <p style="font-size: 12px; color: var(--ula-text-muted); margin-top: 2px;">{{ __('Assigned collaborators and their contributed hours and assigned task loads.') }}</p>
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
                                            <div style="width: 32px; height: 32px; border-radius: 50%; background: var(--ula-gradient-accent); color: white; display: flex; align-items: center; justify-content: center; font-size: 12px; font-weight: 900;">
                                                {{ strtoupper(substr($pm->user->name ?? 'U', 0, 2)) }}
                                            </div>
                                            <div>
                                                <div style="font-weight: 800; color: var(--ula-text-primary);">{{ $pm->user->name ?? 'Member' }}</div>
                                                <div style="font-size: 11px; color: var(--ula-text-muted);">{{ $pm->user->email ?? '' }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge-pill badge-neutral">{{ ucfirst($pm->role ?? 'Member') }}</span>
                                    </td>
                                    <td style="font-weight: 800;">
                                        {{ $memberTasksCount }} {{ __('Tasks') }}
                                    </td>
                                    <td style="font-family: monospace; font-weight: 900; color: var(--ula-text-primary); font-size: 14px;">
                                        {{ number_format($memberHours, 1) }}h
                                    </td>
                                    <td style="font-family: monospace; font-weight: 700; color: var(--ula-text-secondary);">
                                        ${{ number_format($pm->hourly_rate ?? 50, 2) }}/h
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" style="text-align: center; color: var(--ula-text-muted); padding: 40px;">
                                        <div style="font-size: 32px; margin-bottom: 8px;"><span class="material-symbols-rounded" style="font-size: 1em; vertical-align: text-bottom;">group</span></div>
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
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; flex-wrap: wrap; gap: 12px;">
                        <div>
                            <h3 style="font-size: 16px; font-weight: 900; color: var(--ula-text-primary);"><span class="material-symbols-rounded" style="font-size: 1em; vertical-align: text-bottom;">flag</span> {{ __('Phases & Delivery Milestones Roadmap') }} ({{ $project->milestones->count() }})</h3>
                            <p style="font-size: 12px; color: var(--ula-text-muted); margin-top: 2px;">{{ __('Track milestone deliverables, progress percentage, task completions, and delivery countdowns.') }}</p>
                        </div>
                        <button onclick="openNewMilestoneModal()" class="tactile-btn btn-primary" style="padding: 8px 16px; font-size: 12px;">
                            + {{ __('New Milestone') }}
                        </button>
                    </div>

                    <div style="display: flex; flex-direction: column; gap: 16px;">
                        @forelse($project->milestones as $ms)
                        <div style="background: var(--ula-surface-page-alt); border: 1px solid var(--ula-border-subtle); border-radius: var(--ula-radius-lg); padding: 20px; box-shadow: var(--ula-shadow-xs);">
                            <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 12px; flex-wrap: wrap; gap: 10px;">
                                <div>
                                    <div style="display: flex; align-items: center; gap: 8px;">
                                        <span style="font-size: 20px;"><span class="material-symbols-rounded" style="font-size: 1em; vertical-align: text-bottom;">flag</span></span>
                                        <h4 style="font-size: 16px; font-weight: 900; color: var(--ula-text-primary); margin: 0;">{{ $ms->name }}</h4>
                                        <span class="badge-pill {{ $ms->status === 'completed' ? 'badge-green' : ($ms->due_date && $ms->due_date->isPast() ? 'badge-danger' : 'badge-gold') }}">
                                            {!! $ms->status === 'completed' ? '<span class="material-symbols-rounded" style="font-size: 1em; vertical-align: text-bottom;">check_circle</span> ' . __('Completed') : ($ms->due_date && $ms->due_date->isPast() ? '<span class="material-symbols-rounded" style="font-size: 1em; vertical-align: text-bottom;">emergency</span> ' . __('Overdue') : '<span class="material-symbols-rounded" style="font-size: 1em; vertical-align: text-bottom;">hourglass_empty</span> ' . __('In Progress')) !!}
                                        </span>
                                    </div>
                                    <div style="font-size: 12px; color: var(--ula-text-muted); margin-top: 4px;">
                                        <span class="material-symbols-rounded" style="font-size: 1em; vertical-align: text-bottom;">calendar_month</span> {{ $ms->due_date ? $ms->due_date->format('M d, Y') . ' (' . $ms->due_date->diffForHumans() . ')' : __('No target date set') }}
                                    </div>
                                </div>
                                
                                <div style="display: flex; align-items: center; gap: 8px;">
                                    <span style="font-size: 22px; font-weight: 900; font-family: monospace; color: var(--ula-text-primary);">{{ $ms->progress_percentage }}%</span>
                                    <button type="button" onclick="quickToggleMilestoneStatus('{{ $ms->id }}', '{{ $ms->status === 'completed' ? 'pending' : 'completed' }}')" class="tactile-btn {{ $ms->status === 'completed' ? 'btn-secondary' : 'btn-primary' }}" style="padding: 5px 10px; font-size: 11px;">
                                        {!! $ms->status === 'completed' ? '<span class="material-symbols-rounded" style="font-size: 1em; vertical-align: text-bottom;">undo</span> ' . __('Reopen') : '<span class="material-symbols-rounded" style="font-size: 1em; vertical-align: text-bottom;">check</span> ' . __('Mark Completed') !!}
                                    </button>
                                    <button type="button" onclick="deleteProjectMilestone('{{ $ms->id }}')" class="tactile-btn" style="background: rgba(217, 107, 95, 0.15); color: var(--ula-status-danger); border: 1px solid rgba(217, 107, 95, 0.3); padding: 5px 8px; font-size: 11px;" title="{{ __('Delete Milestone') }}">
                                        <span class="material-symbols-rounded" style="font-size: 1em; vertical-align: text-bottom;">delete</span>
                                    </button>
                                </div>
                            </div>

                            <!-- Progress Bar -->
                            <div style="width: 100%; height: 8px; background: var(--ula-surface-card); border-radius: 9999px; overflow: hidden; margin-bottom: 12px; border: 1px solid var(--ula-border-subtle);">
                                <div style="width: {{ $ms->progress_percentage }}%; height: 100%; background: linear-gradient(90deg, var(--ula-palm-500) 0%, var(--ula-palm-800) 100%); transition: width 0.3s ease;"></div>
                            </div>

                            <!-- Metric Badges -->
                            <div style="display: flex; gap: 10px; margin-bottom: 12px; flex-wrap: wrap;">
                                <span class="badge-pill badge-neutral" style="font-size: 11px; font-weight: 700;">
                                    ⊞ {{ $ms->completed_tasks_count }} / {{ $ms->tasks_count }} {{ __('Tasks Done') }}
                                </span>
                                <span class="badge-pill badge-neutral" style="font-size: 11px; font-weight: 700; color: var(--ula-text-primary);">
                                    <span class="material-symbols-rounded" style="font-size: 1em; vertical-align: text-bottom;">timer</span> {{ $ms->actualHours() }}h {{ __('Hours Logged') }}
                                </span>
                            </div>

                            <!-- Tasks list in this milestone -->
                            @if($ms->tasks && $ms->tasks->count() > 0)
                            <div style="background: var(--ula-surface-card); border: 1px solid var(--ula-border-subtle); border-radius: 8px; padding: 8px 12px; display: flex; flex-direction: column; gap: 6px;">
                                <div style="font-size: 11px; font-weight: 800; color: var(--ula-text-muted); text-transform: uppercase; margin-bottom: 2px;">
                                    <span class="material-symbols-rounded" style="font-size: 1em; vertical-align: text-bottom;">assignment</span> {{ __('Assigned Tasks') }} ({{ $ms->tasks->count() }})
                                </div>
                                @foreach($ms->tasks as $mt)
                                <div onclick="openTaskInspector('{{ $mt->id }}')" style="display: flex; justify-content: space-between; align-items: center; font-size: 12px; padding: 4px 6px; border-radius: 4px; cursor: pointer; background: var(--ula-surface-page-alt);">
                                    <div style="display: flex; align-items: center; gap: 6px;">
                                        <span style="font-family: monospace; font-weight: 800; color: var(--ula-text-muted); font-size: 10px;">#{{ $mt->task_number }}</span>
                                        <span style="font-weight: 700; color: var(--ula-text-primary); {{ $mt->status === 'done' ? 'text-decoration: line-through; opacity: 0.6;' : '' }}">{{ $mt->title }}</span>
                                    </div>
                                    <div style="display: flex; align-items: center; gap: 6px;">
                                        <span class="badge-pill {{ $mt->status === 'done' ? 'badge-green' : 'badge-gold' }}" style="font-size: 9px;">{{ ucfirst(str_replace('_', ' ', $mt->status)) }}</span>
                                        <span style="font-size: 10px; color: var(--ula-text-muted);">{{ $mt->assignee ? explode(' ', $mt->assignee->name)[0] : __('Unassigned') }}</span>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                            @else
                            <div style="font-size: 11px; color: var(--ula-text-muted); background: var(--ula-surface-card); padding: 8px 12px; border-radius: 8px; border: 1px dashed var(--ula-border-subtle);">
                                ℹ️ {{ __('No tasks assigned to this milestone yet. You can assign tasks from the Task Inspector.') }}
                            </div>
                            @endif
                        </div>
                        @empty
                        <div style="text-align: center; color: var(--ula-text-muted); padding: 40px;">
                            <div style="font-size: 32px; margin-bottom: 8px;"><span class="material-symbols-rounded" style="font-size: 1em; vertical-align: text-bottom;">flag</span></div>
                            <p style="font-size: 14px; font-weight: 700; color: var(--ula-text-primary); margin-bottom: 4px;">{{ __('No milestones defined for this project yet.') }}</p>
                            <p style="font-size: 12px; margin-bottom: 12px;">{{ __('Create project phases and delivery milestones to track high-level progress and release dates.') }}</p>
                            <button onclick="openNewMilestoneModal()" class="tactile-btn btn-primary" style="padding: 7px 16px; font-size: 12px;">+ {{ __('Create First Milestone') }}</button>
                        </div>
                        @endforelse
                    </div>
                </div>
            </div>

            <!-- TAB: GANTT & TIMELINE MATRIX (FRAPPE GANTT — REAL DATE-BASED) -->
            <div id="hub-section-gantt" class="hub-section-content" style="display: none;">
                <div class="hub-card" style="padding: 0; overflow: hidden;">
                    <!-- Gantt Header -->
                    <div style="padding: 16px 24px; border-bottom: 1px solid var(--ula-border-subtle); display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px;">
                        <div>
                            <h3 style="font-size: 16px; font-weight: 900; color: var(--ula-text-primary);"><span class="material-symbols-rounded" style="font-size: 1em; vertical-align: text-bottom;">bar_chart</span> {{ __('Interactive Gantt & Schedule Timeline') }}</h3>
                            <p style="font-size: 12px; color: var(--ula-text-muted); margin-top: 2px;">{{ __('Real date-based timeline with dependency arrows. Drag bars to reschedule.') }}</p>
                        </div>
                        <div style="display: flex; gap: 6px; align-items: center; flex-wrap: wrap;">
                            <span style="font-size: 11px; color: var(--ula-text-muted); margin-inline-end: 4px;">{{ __('View:') }}</span>
                            <button onclick="setGanttView('Quarter Day')" id="gantt-btn-Quarter_Day" class="tactile-btn btn-secondary gantt-view-btn" style="padding: 5px 10px; font-size: 11px;">6H</button>
                            <button onclick="setGanttView('Half Day')"    id="gantt-btn-Half_Day"    class="tactile-btn btn-secondary gantt-view-btn" style="padding: 5px 10px; font-size: 11px;">12H</button>
                            <button onclick="setGanttView('Day')"         id="gantt-btn-Day"          class="tactile-btn btn-secondary gantt-view-btn" style="padding: 5px 10px; font-size: 11px;">{{ __('Day') }}</button>
                            <button onclick="setGanttView('Week')"        id="gantt-btn-Week"         class="tactile-btn btn-secondary gantt-view-btn active" style="padding: 5px 10px; font-size: 11px;">{{ __('Week') }}</button>
                            <button onclick="setGanttView('Month')"       id="gantt-btn-Month"        class="tactile-btn btn-secondary gantt-view-btn" style="padding: 5px 10px; font-size: 11px;">{{ __('Month') }}</button>
                        </div>
                    </div>

                    <!-- Gantt Legend -->
                    <div style="padding: 8px 24px; display: flex; gap: 16px; flex-wrap: wrap; border-bottom: 1px solid var(--ula-border-subtle); background: var(--ula-surface-page-alt);">
                        <span style="font-size: 11px; display: flex; align-items: center; gap: 5px;"><span style="width: 10px; height: 10px; border-radius: 2px; background: var(--ula-palm-500); display: inline-block;"></span> {{ __('Done') }}</span>
                        <span style="font-size: 11px; display: flex; align-items: center; gap: 5px;"><span style="width: 10px; height: 10px; border-radius: 2px; background: var(--ula-gold-500); display: inline-block;"></span> {{ __('In Progress') }}</span>
                        <span style="font-size: 11px; display: flex; align-items: center; gap: 5px;"><span style="width: 10px; height: 10px; border-radius: 2px; background: var(--ula-terracotta-400); display: inline-block;"></span> {{ __('Review/QA') }}</span>
                        <span style="font-size: 11px; display: flex; align-items: center; gap: 5px;"><span style="width: 10px; height: 10px; border-radius: 2px; background: var(--ula-stone-400); display: inline-block;"></span> {{ __('Backlog/Ready') }}</span>
                        <span style="font-size: 11px; margin-inline-start: auto; color: var(--ula-text-muted);">↔ {{ __('Drag bars to reschedule') }}</span>
                    </div>

                    <!-- Frappe Gantt Mount Point -->
                    <div id="frappe-gantt-wrapper" style="overflow-x: auto; min-height: 300px; padding: 12px 0;">
                        <div id="frappe-gantt-container"></div>
                        <div id="gantt-empty-state" style="display: none; text-align: center; padding: 60px 20px; color: var(--ula-text-muted);">
                            <span class="material-symbols-rounded" style="font-size: 1em; vertical-align: text-bottom;">bar_chart</span> {{ __('No tasks with dates found. Add start & due dates to tasks to see the Gantt chart.') }}
                        </div>
                    </div>
                </div>

                <!-- Gantt Task Popover -->
                <div id="gantt-task-popover" style="display: none; position: fixed; z-index: 9999; background: var(--ula-surface-card); border: 1px solid var(--ula-border-subtle); border-radius: var(--ula-radius-lg); padding: 14px 18px; box-shadow: var(--ula-shadow-xl); min-width: 260px; max-width: 340px; pointer-events: auto;">
                    <div id="gantt-popover-content" style="font-size: 13px;"></div>
                    <div style="margin-top: 10px; display: flex; gap: 8px;">
                        <button id="gantt-popover-open-btn" class="tactile-btn btn-primary" style="padding: 5px 12px; font-size: 11px; flex: 1;">{{ __('Open Task') }}</button>
                        <button onclick="document.getElementById('gantt-task-popover').style.display='none'" class="tactile-btn btn-secondary" style="padding: 5px 10px; font-size: 11px;"><span class="material-symbols-rounded" style="font-size: 1em; vertical-align: text-bottom;">close</span></button>
                    </div>
                </div>
            </div>

            <!-- TAB: WORKLOAD MATRIX (CLICKUP WORKLOAD) -->
            <div id="hub-section-workload" class="hub-section-content" style="display: none;">
                <div class="hub-card" style="padding: 0; overflow: hidden;">
                    <div style="padding: 18px 24px; border-bottom: 1px solid var(--ula-border-subtle); display: flex; justify-content: space-between; align-items: center;">
                        <div>
                            <h3 style="font-size: 16px; font-weight: 900; color: var(--ula-text-primary);"><span class="material-symbols-rounded" style="font-size: 1em; vertical-align: text-bottom;">trending_up</span> {{ __('Team Workload & Capacity Matrix') }}</h3>
                            <p style="font-size: 12px; color: var(--ula-text-muted); margin-top: 2px;">{{ __('Prevent employee burnout by balancing weekly hours against allocated tasks.') }}</p>
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
                                            <div style="width: 32px; height: 32px; border-radius: 50%; background: var(--ula-gradient-accent); color: white; display: flex; align-items: center; justify-content: center; font-size: 12px; font-weight: 900;">
                                                {{ strtoupper(substr($wm['member']->user->name ?? 'U', 0, 2)) }}
                                            </div>
                                            <div>
                                                <div style="font-weight: 800; color: var(--ula-text-primary);">{{ $wm['member']->user->name }}</div>
                                                <div style="font-size: 11px; color: var(--ula-text-muted);">{{ $wm['member']->role->name ?? 'Staff' }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td style="font-weight: 800;">{{ $wm['tasks_count'] }} {{ __('Tasks') }}</td>
                                    <td style="font-weight: 800; color: var(--ula-text-primary);">{{ number_format($wm['assigned_hours'], 1) }}h</td>
                                    <td style="color: var(--ula-text-muted);">{{ $wm['capacity'] }}h / {{ __('week') }}</td>
                                    <td>
                                        <div style="display: flex; align-items: center; gap: 8px;">
                                            <div style="width: 80px; height: 8px; background: var(--ula-surface-page-alt); border-radius: 9999px; overflow: hidden; border: 1px solid var(--ula-border-subtle);">
                                                <div style="width: {{ min(100, $wm['utilization']) }}%; height: 100%; background: {{ $wm['status'] === 'overloaded' ? 'var(--ula-status-danger)' : ($wm['status'] === 'optimal' ? 'var(--ula-palm-900)' : 'var(--ula-highlight-default)') }};"></div>
                                            </div>
                                            <span style="font-size: 11px; font-weight: 800;">{{ $wm['utilization'] }}%</span>
                                        </div>
                                    </td>
                                    <td>
                                        @if($wm['status'] === 'overloaded')
                                            <span class="badge-pill" style="background: var(--ula-terracotta-200); color: var(--ula-terracotta-600); border: 1px solid var(--ula-terracotta-300);"><span class="material-symbols-rounded" style="font-size: 1em; vertical-align: text-bottom;">warning</span> {{ __('Overloaded') }}</span>
                                        @elseif($wm['status'] === 'optimal')
                                            <span class="badge-pill badge-green"><span class="material-symbols-rounded" style="font-size: 1em; vertical-align: text-bottom;">bolt</span> {{ __('Optimal') }}</span>
                                        @else
                                            <span class="badge-pill badge-neutral"><span class="material-symbols-rounded" style="font-size: 1em; vertical-align: text-bottom;">eco</span> {{ __('Available') }}</span>
                                        @endif
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="6" style="text-align: center; color: var(--ula-text-muted); padding: 30px;">
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
                            <h3 style="font-size: 16px; font-weight: 900; color: var(--ula-text-primary);"><span class="material-symbols-rounded" style="font-size: 1em; vertical-align: text-bottom;">menu_book</span> {{ __('Project Docs, Specs & Knowledge Wiki') }}</h3>
                            <p style="font-size: 12px; color: var(--ula-text-muted); margin-top: 2px;">{{ __('Centralized living documents, specifications, and team meeting minutes.') }}</p>
                        </div>
                        <button onclick="openNewDocModal()" class="tactile-btn btn-primary" style="padding: 8px 16px; font-size: 12px;">
                            + {{ __('Create Document') }}
                        </button>
                    </div>

                    <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 16px;">
                        @forelse($project->documents as $doc)
                        <div style="background: var(--ula-surface-page-alt); border: 1px solid var(--ula-border-subtle); border-radius: var(--ula-radius-lg); padding: 18px; box-shadow: var(--ula-shadow-xs); transition: all 0.2s;" onmouseover="this.style.transform='translateY(-2px)'" onmouseout="this.style.transform='translateY(0)'">
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                                <span style="font-size: 22px;">@if($doc->icon){{ $doc->icon }}@else<span class="material-symbols-rounded" style="font-size: 1em; vertical-align: text-bottom;">description</span>@endif</span>
                                <span class="badge-pill badge-neutral">v{{ $doc->version }}</span>
                            </div>
                            <h4 style="font-size: 15px; font-weight: 900; color: var(--ula-text-primary); margin-bottom: 6px;">{{ $doc->title }}</h4>
                            <p style="font-size: 12px; color: var(--ula-text-muted); margin-bottom: 12px; max-height: 48px; overflow: hidden; text-overflow: ellipsis;">
                                {{ Str::limit(strip_tags($doc->content), 90) ?: __('No preview content available.') }}
                            </p>
                            <div style="font-size: 11px; color: var(--ula-text-muted); display: flex; justify-content: space-between; align-items: center; border-top: 1px solid var(--ula-border-subtle); padding-top: 8px;">
                                <span><span class="material-symbols-rounded" style="font-size: 1em; vertical-align: text-bottom;">edit</span> {{ $doc->author->name ?? 'Team' }}</span>
                                <span>{{ $doc->updated_at->diffForHumans() }}</span>
                            </div>
                        </div>
                        @empty
                        <div style="grid-column: 1 / -1; text-align: center; color: var(--ula-text-muted); padding: 40px;">
                            <div style="font-size: 32px; margin-bottom: 8px;"><span class="material-symbols-rounded" style="font-size: 1em; vertical-align: text-bottom;">menu_book</span></div>
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
                            <h3 style="font-size: 16px; font-weight: 900; color: var(--ula-text-primary);"><span class="material-symbols-rounded" style="font-size: 1em; vertical-align: text-bottom;">track_changes</span> {{ __('Strategic Goals & Measurable Targets') }} ({{ $project->goals->count() }})</h3>
                            <p style="font-size: 12px; color: var(--ula-text-muted); margin-top: 2px;">{{ __('Real-time OKRs automatically tracked against completed tasks, milestone phases, and logged work hours.') }}</p>
                        </div>
                        <button onclick="openNewGoalModal()" class="tactile-btn btn-primary" style="padding: 8px 16px; font-size: 12px;">
                            + {{ __('New Goal') }}
                        </button>
                    </div>

                    <div style="display: flex; flex-direction: column; gap: 16px;">
                        @forelse($project->goals as $goal)
                        <div style="background: var(--ula-surface-page-alt); border: 1px solid var(--ula-border-subtle); border-radius: var(--ula-radius-lg); padding: 20px; box-shadow: var(--ula-shadow-xs);">
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px; flex-wrap: wrap; gap: 10px;">
                                <div>
                                    <div style="display: flex; align-items: center; gap: 8px;">
                                        <span style="font-size: 20px;"><span class="material-symbols-rounded" style="font-size: 1em; vertical-align: text-bottom;">track_changes</span></span>
                                        <h4 style="font-size: 16px; font-weight: 900; color: var(--ula-text-primary); margin: 0;">{{ $goal->name }}</h4>
                                        <span class="badge-pill {{ $goal->status === 'completed' ? 'badge-green' : ($goal->progress_percentage > 0 ? 'badge-gold' : 'badge-neutral') }}">
                                            {!! $goal->status === 'completed' ? '<span class="material-symbols-rounded" style="font-size: 1em; vertical-align: text-bottom;">check_circle</span> ' . __('Completed') : ($goal->progress_percentage > 0 ? '<span class="material-symbols-rounded" style="font-size: 1em; vertical-align: text-bottom;">bolt</span> ' . __('In Progress') : '<span class="material-symbols-rounded" style="font-size: 1em; vertical-align: text-bottom;">hourglass_empty</span> ' . __('Planned')) !!}
                                        </span>
                                    </div>
                                    @if($goal->description)
                                        <div style="font-size: 12px; color: var(--ula-text-muted); margin-top: 4px;">{{ $goal->description }}</div>
                                    @endif
                                </div>
                                <div style="display: flex; align-items: center; gap: 10px;">
                                    <span style="font-size: 22px; font-weight: 900; font-family: monospace; color: var(--ula-text-primary);">{{ $goal->progress_percentage }}%</span>
                                </div>
                            </div>

                            <!-- Progress Bar -->
                            <div style="width: 100%; height: 8px; background: var(--ula-surface-card); border-radius: 9999px; overflow: hidden; margin-bottom: 14px; border: 1px solid var(--ula-border-subtle);">
                                <div style="width: {{ $goal->progress_percentage }}%; height: 100%; background: linear-gradient(90deg, var(--ula-palm-500) 0%, var(--ula-palm-800) 100%); transition: width 0.3s ease;"></div>
                            </div>

                            <!-- Auto-Calculated Targets Breakdown -->
                            <div style="display: flex; flex-direction: column; gap: 8px;">
                                @forelse($goal->targets as $target)
                                @php
                                    $targetIcon = match($target->target_type) {
                                        'tasks' => '<span class="material-symbols-rounded" style="font-size: 1em; vertical-align: text-bottom;">bolt</span>',
                                        'milestones' => '<span class="material-symbols-rounded" style="font-size: 1em; vertical-align: text-bottom;">flag</span>',
                                        'hours' => '<span class="material-symbols-rounded" style="font-size: 1em; vertical-align: text-bottom;">timer</span>',
                                        default => '<span class="material-symbols-rounded" style="font-size: 1em; vertical-align: text-bottom;">track_changes</span>'
                                    };
                                @endphp
                                <div style="display: flex; justify-content: space-between; align-items: center; font-size: 12px; background: var(--ula-surface-card); padding: 8px 12px; border-radius: var(--ula-radius-sm); border: 1px solid var(--ula-border-subtle); flex-wrap: wrap; gap: 6px;">
                                    <span style="font-weight: 700; color: var(--ula-text-primary); display: flex; align-items: center; gap: 6px;">
                                        <span>{{ $targetIcon }}</span>
                                        <span>{{ $target->title }}</span>
                                        @if(in_array($target->target_type, ['tasks', 'milestones', 'hours']))
                                            <span class="badge-pill" style="font-size: 9px; background: rgba(79, 155, 95, 0.12); color: var(--ula-text-primary);"><span class="material-symbols-rounded" style="font-size: 1em; vertical-align: text-bottom;">bolt</span> {{ __('Auto-Tracked') }}</span>
                                        @endif
                                    </span>
                                    <span style="font-weight: 800; color: var(--ula-text-primary); font-family: monospace; font-size: 12px;">
                                        {{ $target->current_value }} / {{ $target->target_value }} {{ $target->unit }}
                                        @if($target->is_completed)
                                            <span style="color: var(--ula-status-success); margin-inline-start: 4px;"><span class="material-symbols-rounded" style="font-size: 1em; vertical-align: text-bottom;">check</span></span>
                                        @endif
                                    </span>
                                </div>
                                @empty
                                <div style="display: flex; justify-content: space-between; align-items: center; font-size: 12px; background: var(--ula-surface-card); padding: 8px 12px; border-radius: var(--ula-radius-sm); border: 1px solid var(--ula-border-subtle);">
                                    <span style="font-weight: 700; color: var(--ula-text-primary);"><span class="material-symbols-rounded" style="font-size: 1em; vertical-align: text-bottom;">bolt</span> {{ __('Tasks Completed in Project') }}</span>
                                    <span style="font-weight: 800; color: var(--ula-text-primary); font-family: monospace;">
                                        {{ $project->tasks()->where('status', 'done')->count() }} / {{ $project->tasks()->count() }} {{ __('Tasks') }}
                                    </span>
                                </div>
                                @endforelse
                            </div>
                        </div>
                        @empty
                        <div style="text-align: center; color: var(--ula-text-muted); padding: 40px;">
                            <div style="font-size: 32px; margin-bottom: 8px;"><span class="material-symbols-rounded" style="font-size: 1em; vertical-align: text-bottom;">track_changes</span></div>
                            <p style="font-size: 14px; font-weight: 700; color: var(--ula-text-primary); margin-bottom: 4px;">{{ __('No strategic goals set for this project yet.') }}</p>
                            <p style="font-size: 12px; margin-bottom: 12px;">{{ __('Define key deliverables, target KPIs, and measurable milestones to automatically track progress.') }}</p>
                            <button onclick="openNewGoalModal()" class="tactile-btn btn-primary" style="padding: 7px 16px; font-size: 12px;">+ {{ __('Create First Goal') }}</button>
                        </div>
                        @endforelse
                    </div>
                </div>
            </div>
            <!-- TAB: FILES & ATTACHMENTS -->
            <div id="hub-section-files" class="hub-section-content" style="display: none;">
                <div class="hub-card">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; flex-wrap: wrap; gap: 10px;">
                        <div>
                            <h3 style="font-size: 16px; font-weight: 900; color: var(--ula-text-primary);"><span class="material-symbols-rounded" style="font-size: 1em; vertical-align: text-bottom;">folder</span> {{ __('Project Assets & File Repository') }}</h3>
                            <p style="font-size: 12px; color: var(--ula-text-muted); margin-top: 2px;">{{ __('Centralized repository for design files, contracts, deliverables, and specifications.') }}</p>
                        </div>
                    </div>

                    <!-- File Upload Dropzone Form -->
                    <form id="hub-project-file-form" onsubmit="uploadProjectFileSubmit(event)" action="{{ route('projects.files.store', $project) }}" method="POST" enctype="multipart/form-data" style="background: var(--ula-surface-page-alt); border: 2px dashed var(--ula-border-subtle); border-radius: var(--ula-radius-lg); padding: 24px; text-align: center; margin-bottom: 24px; transition: border-color 0.2s;">
                        @csrf
                        <div style="font-size: 32px; margin-bottom: 8px;"><span class="material-symbols-rounded" style="font-size: 1em; vertical-align: text-bottom;">upload</span></div>
                        <h4 style="font-size: 14px; font-weight: 800; color: var(--ula-text-primary); margin-bottom: 4px;">{{ __('Upload Files to Project') }}</h4>
                        <p style="font-size: 12px; color: var(--ula-text-muted); margin-bottom: 14px;">{{ __('Choose documents, images, archives or zip files (Max 50MB)') }}</p>
                        <div style="display: flex; justify-content: center; align-items: center; gap: 10px; max-width: 480px; margin: 0 auto; flex-wrap: wrap;">
                            <input type="file" name="file" id="hub-project-file-input" required class="form-input" style="font-size: 12px; flex: 1;">
                            <button type="submit" id="hub-project-file-btn" class="tactile-btn btn-primary" style="padding: 9px 18px; font-size: 12px;">
                                <span class="material-symbols-rounded" style="font-size: 1em; vertical-align: text-bottom;">rocket_launch</span> {{ __('Upload File') }}
                            </button>
                        </div>
                    </form>

                    <!-- Files Grid -->
                    <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 16px;">
                        @forelse($project->files as $file)
                            @php
                                $ext = strtolower(pathinfo($file->file_name, PATHINFO_EXTENSION));
                                $icon = match($ext) {
                                    'pdf' => '<span class="material-symbols-rounded" style="font-size: 1em; vertical-align: text-bottom;">picture_as_pdf</span>',
                                    'png', 'jpg', 'jpeg', 'svg', 'gif', 'webp' => '<span class="material-symbols-rounded" style="font-size: 1em; vertical-align: text-bottom;">image</span>',
                                    'zip', 'rar', 'tar', 'gz', '7z' => '<span class="material-symbols-rounded" style="font-size: 1em; vertical-align: text-bottom;">inventory_2</span>',
                                    'doc', 'docx' => '<span class="material-symbols-rounded" style="font-size: 1em; vertical-align: text-bottom;">description</span>',
                                    'xls', 'xlsx', 'csv' => '<span class="material-symbols-rounded" style="font-size: 1em; vertical-align: text-bottom;">bar_chart</span>',
                                    'mp4', 'mov', 'avi' => '<span class="material-symbols-rounded" style="font-size: 1em; vertical-align: text-bottom;">videocam</span>',
                                    'mp3', 'wav', 'ogg' => '<span class="material-symbols-rounded" style="font-size: 1em; vertical-align: text-bottom;">music_note</span>',
                                    'php', 'js', 'json', 'html', 'css', 'py', 'ts' => '<span class="material-symbols-rounded" style="font-size: 1em; vertical-align: text-bottom;">computer</span>',
                                    default => '<span class="material-symbols-rounded" style="font-size: 1em; vertical-align: text-bottom;">description</span>'
                                };
                                $canDelete = ($user->isSuperAdmin() || $membership->role?->slug === 'company_admin' || ($project && $project->manager_id === $user->id) || $file->user_id === $user->id);
                            @endphp
                            <div style="background: var(--ula-surface-page-alt); border: 1px solid var(--ula-border-subtle); border-radius: var(--ula-radius-lg); padding: 16px; display: flex; flex-direction: column; justify-content: space-between; gap: 12px; box-shadow: var(--ula-shadow-xs);">
                                <div style="display: flex; gap: 12px; align-items: flex-start;">
                                    <div style="font-size: 28px; width: 44px; height: 44px; background: var(--ula-surface-card); border: 1px solid var(--ula-border-subtle); border-radius: 10px; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                                        {{ $icon }}
                                    </div>
                                    <div style="flex: 1; min-width: 0;">
                                        <div style="font-weight: 800; font-size: 13px; color: var(--ula-text-primary); overflow: hidden; text-overflow: ellipsis; white-space: nowrap;" title="{{ $file->file_name }}">
                                            {{ $file->file_name }}
                                        </div>
                                        <div style="font-size: 11px; color: var(--ula-text-muted); margin-top: 2px;">
                                            {{ $file->formatted_size }} • {{ $file->created_at->format('M d, Y') }}
                                        </div>
                                        <div style="font-size: 11px; color: var(--ula-text-secondary); margin-top: 4px; display: flex; align-items: center; gap: 4px;">
                                            <span><span class="material-symbols-rounded" style="font-size: 1em; vertical-align: text-bottom;">person</span></span>
                                            <strong>{{ $file->user ? $file->user->name : __('Team Member') }}</strong>
                                        </div>
                                    </div>
                                </div>

                                <div style="display: flex; gap: 8px; border-top: 1px solid var(--ula-border-subtle); padding-top: 10px;">
                                    <a href="{{ $file->file_url }}" target="_blank" download class="tactile-btn btn-secondary" style="flex: 1; padding: 6px 12px; font-size: 11px; text-align: center; text-decoration: none; justify-content: center;">
                                        <span class="material-symbols-rounded" style="font-size: 1em; vertical-align: text-bottom;">file_download</span> {{ __('Download') }}
                                    </a>
                                    @if($canDelete)
                                        <form action="{{ route('projects.files.destroy', [$project, $file]) }}" method="POST" onsubmit="return confirm('{{ __('Are you sure you want to delete this file?') }}')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="tactile-btn" style="background: rgba(217, 107, 95, 0.15); color: var(--ula-status-danger); border: 1px solid rgba(217, 107, 95, 0.3); padding: 6px 10px; font-size: 11px;" title="{{ __('Delete File') }}">
                                                <span class="material-symbols-rounded" style="font-size: 1em; vertical-align: text-bottom;">delete</span>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </div>
                        @empty
                            <div style="grid-column: 1 / -1; text-align: center; color: var(--ula-text-muted); padding: 40px;">
                                <div style="font-size: 32px; margin-bottom: 8px;"><span class="material-symbols-rounded" style="font-size: 1em; vertical-align: text-bottom;">folder_open</span></div>
                                {{ __('No files or assets uploaded to this project yet.') }}
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>

        </main>
    </div>

    <!-- ── 7. MODALS ── -->

@include('projects.partials.modals')

        <script nonce="{{ $cspNonce ?? '' }}">
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
                el.innerHTML = (activeTheme === 'dark') ? '<span class="material-symbols-rounded" style="font-size: 1em; vertical-align: text-bottom;">light_mode</span>' : '<span class="material-symbols-rounded" style="font-size: 1em; vertical-align: text-bottom;">dark_mode</span>';
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
                icon: form.icon.value || '<span class="material-symbols-rounded" style="font-size: 1em; vertical-align: text-bottom;">description</span>',
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
                    showHubToast('<span class="material-symbols-rounded" style="font-size: 1em; vertical-align: text-bottom;">menu_book</span> {{ __('Document created successfully!') }}');
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

        // ── FRAPPE GANTT ENGINE ──────────────────────────────────────────────
        let frappeGantt = null;
        let frappeGanttCurrentView = 'Week';
        let ganttCurrentPopoverTaskId = null;

        // UlaSpace palette only (palm/gold/terracotta/stone) -- no blue/purple/
        // slate exist in the design system, so 6 statuses are spread across
        // shades within those families rather than borrowing generic hues.
        const GANTT_STATUS_COLORS = {
            done:        '#3c6b4c', // --ula-palm-500
            in_progress: '#b98a37', // --ula-gold-500
            review:      '#b46c34', // --ula-terracotta-400
            qa:          '#7d451d', // --ula-terracotta-600
            ready:       '#a49889', // --ula-stone-400
            backlog:     '#c1b6a6', // --ula-stone-300
        };

        // Build Frappe Gantt task objects from PHP $ganttTasks
        function buildFrappeGanttTasks() {
            const raw = @json($ganttTasks);
            if (!raw || raw.length === 0) return [];
            const allIds = new Set(raw.map(t => String(t.id)));

            return raw.map(t => {
                let s = t.start_date;
                let e = t.due_date;
                if (!s) s = new Date().toISOString().slice(0, 10);
                if (!e || e <= s) {
                    const d = new Date(s);
                    d.setDate(d.getDate() + 2);
                    e = d.toISOString().slice(0, 10);
                }

                const validDeps = (t.dependencies || []).filter(id => allIds.has(String(id))).join(',');

                return {
                    id:           String(t.id),
                    name:         t.title,
                    start:        s,
                    end:          e,
                    progress:     t.progress || 0,
                    dependencies: validDeps,
                    custom_class: 'gantt-bar-' + (t.status || 'backlog'),
                    _meta:        t,
                };
            });
        }

        function initFrappeGantt() {
            const container = document.getElementById('frappe-gantt-container');
            const emptyState = document.getElementById('gantt-empty-state');
            if (!container) return;

            const tasks = buildFrappeGanttTasks();

            if (tasks.length === 0) {
                container.style.display = 'none';
                emptyState.style.display = 'block';
                return;
            }

            container.style.display = 'block';
            emptyState.style.display = 'none';
            container.innerHTML = '';

            if (typeof Gantt === 'undefined') {
                console.warn('Frappe Gantt not loaded');
                return;
            }

            try {
                frappeGantt = new Gantt('#frappe-gantt-container', tasks, {
                    view_mode:      frappeGanttCurrentView,
                    date_format:    'YYYY-MM-DD',
                    language:       '{{ app()->getLocale() }}',
                    popup_trigger:  'click',
                    custom_popup_html: null, // we handle our own popover

                    on_click: function (task) {
                        showGanttTaskPopover(task);
                    },

                    on_date_change: function (task, start, end) {
                        const orgId = '{{ $project->organization_id }}';
                        const taskId = task.id;
                        const startStr = formatGanttDate(start);
                        const endStr   = formatGanttDate(end);

                        fetch(`/api/v1/organizations/${orgId}/tasks/${taskId}`, {
                            method: 'PATCH',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': CSRF_TOKEN,
                                'Accept': 'application/json',
                            },
                            body: JSON.stringify({ start_date: startStr, due_date: endStr }),
                        })
                        .then(r => r.json())
                        .then(() => showHubToast('📅 {{ __('Task rescheduled') }}: ' + task.name))
                        .catch(() => showHubToast('⚠️ {{ __('Could not reschedule task') }}', 'error'));
                    },
                });

                injectGanttStatusStyles();
            } catch (err) {
                console.error('Error initializing Frappe Gantt:', err);
            }
        }

        function injectGanttStatusStyles() {
            if (document.getElementById('gantt-status-styles')) return;
            const style = document.createElement('style');
            style.id = 'gantt-status-styles';
            style.textContent = `
                .gantt .bar-wrapper.gantt-bar-done         .bar { fill: #3c6b4c !important; }
                .gantt .bar-wrapper.gantt-bar-in_progress  .bar { fill: #b98a37 !important; }
                .gantt .bar-wrapper.gantt-bar-review       .bar { fill: #b46c34 !important; }
                .gantt .bar-wrapper.gantt-bar-qa           .bar { fill: #7d451d !important; }
                .gantt .bar-wrapper.gantt-bar-ready        .bar { fill: #a49889 !important; }
                .gantt .bar-wrapper.gantt-bar-backlog      .bar { fill: #c1b6a6 !important; }
                .gantt .bar-label { font-size: 11px !important; font-weight: 800 !important; fill: #ffffff !important; }
            `;
            document.head.appendChild(style);
        }

        function setGanttView(mode) {
            frappeGanttCurrentView = mode;

            // Update active button
            document.querySelectorAll('.gantt-view-btn').forEach(b => b.classList.remove('active'));
            const btnId = 'gantt-btn-' + mode.replace(' ', '_');
            const btn = document.getElementById(btnId);
            if (btn) btn.classList.add('active');

            if (frappeGantt) {
                frappeGantt.change_view_mode(mode);
                showHubToast('📊 {{ __('Gantt view') }}: ' + mode);
            } else {
                initFrappeGantt();
            }
        }

        function showGanttTaskPopover(task) {
            const meta = task._meta || {};
            ganttCurrentPopoverTaskId = task.id;

            const color = GANTT_STATUS_COLORS[meta.status] || GANTT_STATUS_COLORS.backlog;

            document.getElementById('gantt-popover-content').innerHTML = `
                <div style="font-weight: 900; font-size: 13px; color: var(--ula-text-primary); margin-bottom: 8px; line-height: 1.4;">${task.name}</div>
                <div style="display: flex; flex-wrap: wrap; gap: 6px; margin-bottom: 8px;">
                    <span style="background: ${color}22; color: ${color}; padding: 2px 8px; border-radius: 99px; font-size: 10px; font-weight: 800; border: 1px solid ${color}44;">${(meta.status || 'backlog').replace('_', ' ').toUpperCase()}</span>
                    <span style="background: var(--ula-surface-page-alt); padding: 2px 8px; border-radius: 99px; font-size: 10px; font-weight: 700; border: 1px solid var(--ula-border-subtle);">${meta.priority || 'medium'}</span>
                </div>
                <div style="font-size: 11px; color: var(--ula-text-muted); display: flex; flex-direction: column; gap: 4px;">
                    <span>👤 ${meta.assignee || '{{ __('Unassigned') }}'}</span>
                    <span>📅 ${meta.start_date || '?'} → ${meta.due_date || '?'}</span>
                    <span>📊 {{ __('Progress') }}: ${task.progress || 0}%</span>
                </div>
            `;

            const btn = document.getElementById('gantt-popover-open-btn');
            if (btn) btn.onclick = () => { document.getElementById('gantt-task-popover').style.display = 'none'; openTaskInspector(task.id); };

            // Position near mouse (use viewport center as fallback)
            const popover = document.getElementById('gantt-task-popover');
            popover.style.display = 'block';
            popover.style.top  = (window.innerHeight / 2 - 80) + 'px';
            popover.style.left = (window.innerWidth / 2 - 140) + 'px';
        }

        function formatGanttDate(d) {
            if (!d) return '';
            if (typeof d === 'string') return d.substring(0, 10);
            const y = d.getFullYear();
            const m = String(d.getMonth() + 1).padStart(2, '0');
            const day = String(d.getDate()).padStart(2, '0');
            return `${y}-${m}-${day}`;
        }

        // Legacy alias kept for backward compat
        function zoomGantt(mode) { setGanttView(mode === 'days' ? 'Day' : 'Week'); }

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

            // ── Re-render Frappe Gantt when Gantt tab is opened ──
            if (tab === 'gantt') {
                setTimeout(() => initFrappeGantt(), 80);
            }
        }

        // Restore active tab from hash
        window.addEventListener('DOMContentLoaded', () => {
            const hash = window.location.hash.replace('#', '');
            if (hash && document.getElementById(`hub-section-${hash}`)) {
                switchHubSection(hash);
            } else {
                // If default is kanban or gantt
                const defaultActive = document.querySelector('.hub-tab-btn.active');
                if (defaultActive && defaultActive.id === 'hub-nav-btn-gantt') {
                    setTimeout(() => initFrappeGantt(), 100);
                }
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

        function toggleAllHubProjectAttendees() {
            const chks = document.querySelectorAll('.hub-proj-attendee-chk');
            if (!chks.length) return;
            const anyUnchecked = Array.from(chks).some(c => !c.checked);
            chks.forEach(c => c.checked = anyUnchecked);
        }

        async function scheduleProjectMeetingSubmit(e) {
            e.preventDefault();
            const form = e.target;
            const btn = document.getElementById('hub-schedule-meeting-btn');
            if (btn) {
                btn.disabled = true;
                btn.innerHTML = '⏳ {{ __("Scheduling Meeting...") }}';
            }

            const formData = new FormData(form);

            try {
                const res = await fetch('{{ route("meetings.schedule") }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': CSRF_TOKEN,
                        'Accept': 'application/json'
                    },
                    credentials: 'same-origin',
                    body: formData
                });

                if (res.status === 419) {
                    alert('{{ __("Session expired. The page will reload now.") }}');
                    window.location.reload();
                    return;
                }

                const data = await res.json();
                if (res.ok && data.success) {
                    showHubToast('📅 ' + data.message);
                    closeScheduleProjectMeetingModal();
                    setTimeout(() => window.location.reload(), 600);
                } else {
                    alert(data.message || 'Error scheduling meeting.');
                    if (btn) {
                        btn.disabled = false;
                        btn.innerHTML = '🚀 {{ __("Schedule Meeting & Email Team") }}';
                    }
                }
            } catch (err) {
                form.submit();
            }
        }

        async function uploadProjectFileSubmit(e) {
            e.preventDefault();
            const form = e.target;
            const fileInput = document.getElementById('hub-project-file-input');
            const btn = document.getElementById('hub-project-file-btn');
            if (!fileInput || !fileInput.files.length) return;

            if (btn) {
                btn.disabled = true;
                btn.innerHTML = '⏳ {{ __("Uploading...") }}';
            }

            const formData = new FormData(form);

            try {
                const res = await fetch('{{ route("projects.files.store", $project) }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': CSRF_TOKEN,
                        'Accept': 'application/json'
                    },
                    credentials: 'same-origin',
                    body: formData
                });

                if (res.status === 419) {
                    alert('{{ __("Session expired. The page will reload now.") }}');
                    window.location.reload();
                    return;
                }

                const data = await res.json();
                if (res.ok && data.success) {
                    showHubToast('📁 ' + data.message);
                    setTimeout(() => window.location.reload(), 400);
                } else {
                    alert(data.message || 'Error uploading file.');
                    if (btn) {
                        btn.disabled = false;
                        btn.innerHTML = '🚀 {{ __("Upload File") }}';
                    }
                }
            } catch (err) {
                form.submit();
            }
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

        // ── TASK INSPECTOR TAB SWITCHER ──
        function switchInspectorTab(tab) {
            document.querySelectorAll('.task-inspector-tab-btn').forEach(btn => btn.classList.remove('active'));
            document.querySelectorAll('.task-inspector-tab-pane').forEach(pane => pane.style.display = 'none');

            const targetBtn = document.getElementById(`task-tab-btn-${tab}`);
            const targetPane = document.getElementById(`task-tab-content-${tab}`);
            if (targetBtn) targetBtn.classList.add('active');
            if (targetPane) targetPane.style.display = 'block';
        }

        // ── SORTABLEJS 3D FLUID KANBAN DRAG & DROP ENGINE ──
        let sortableKanbanInstances = [];

        function initSortableKanban() {
            if (typeof Sortable === 'undefined') return;

            // Clean up existing instances if re-initializing
            sortableKanbanInstances.forEach(inst => {
                if (inst && typeof inst.destroy === 'function') inst.destroy();
            });
            sortableKanbanInstances = [];

            const containers = document.querySelectorAll('.kanban-cards-container');
            containers.forEach(container => {
                const inst = new Sortable(container, {
                    group: 'project-kanban-board',
                    animation: 220,
                    ghostClass: 'kanban-card-ghost',
                    chosenClass: 'kanban-card-chosen',
                    dragClass: 'kanban-card-drag',
                    fallbackOnBody: true,
                    swapThreshold: 0.65,
                    filter: 'select, button, a, [data-no-drag]',
                    preventOnFilter: false,
                    onEnd: async function (evt) {
                        const itemEl = evt.item;
                        const taskId = itemEl.getAttribute('data-task-id');
                        const targetCol = evt.to;
                        const fromCol = evt.from;
                        const targetStatus = targetCol.getAttribute('data-status');
                        const fromStatus = fromCol.getAttribute('data-status');

                        if (!taskId || !targetStatus) return;

                        // Same column and position - no-op
                        if (targetStatus === fromStatus && evt.oldIndex === evt.newIndex) {
                            return;
                        }

                        // Optimistically update card attributes and column badge counts
                        itemEl.setAttribute('data-status', targetStatus);
                        const statusSelect = itemEl.querySelector('select');
                        if (statusSelect) statusSelect.value = targetStatus;

                        updateKanbanBadgeCounts();

                        // Async PATCH request
                        try {
                            const res = await fetch(`/api/v1/organizations/${ORG_ID}/tasks/${taskId}/status`, {
                                method: 'PATCH',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': CSRF_TOKEN,
                                    'Accept': 'application/json'
                                },
                                body: JSON.stringify({ status: targetStatus })
                            });

                            const data = await res.json();
                            if (res.ok) {
                                showHubToast(`⚡ {{ __('Task status updated to') }}: <strong>${targetStatus.replace('_', ' ')}</strong>`);
                            } else {
                                showHubToast(`❌ ${data.message || '{{ __('Failed to update status') }}'}`);
                                // Revert position on error
                                if (evt.from && evt.item) {
                                    evt.from.insertBefore(evt.item, evt.from.children[evt.oldIndex]);
                                    updateKanbanBadgeCounts();
                                }
                            }
                        } catch (err) {
                            console.error('Error updating task status via drag & drop:', err);
                            showHubToast('❌ {{ __('Network error updating task status') }}');
                        }
                    }
                });
                sortableKanbanInstances.push(inst);
            });
        }

        function updateKanbanBadgeCounts() {
            const columns = ['backlog', 'ready', 'in_progress', 'review', 'done'];
            columns.forEach(col => {
                const colContainer = document.getElementById(`kanban-cards-${col}`);
                const badge = document.getElementById(`kanban-count-${col}`);
                if (colContainer && badge) {
                    const cards = colContainer.querySelectorAll('.kanban-card:not([style*="display: none"])');
                    badge.textContent = cards.length;

                    // Toggle empty drop hint
                    const emptyHint = colContainer.querySelector('.kanban-empty-drop-hint');
                    if (emptyHint) {
                        const totalCards = colContainer.querySelectorAll('.kanban-card').length;
                        emptyHint.style.display = (totalCards === 0) ? 'block' : 'none';
                    }
                }
            });
        }

        // ── LIVE MULTI-CRITERIA SEARCH & FILTER ENGINE ──
        function filterHubTasks() {
            const searchInput = document.getElementById('hub-task-search-input');
            const assigneeSelect = document.getElementById('hub-filter-assignee');
            const prioritySelect = document.getElementById('hub-filter-priority');
            const dueSelect = document.getElementById('hub-filter-due');
            const milestoneSelect = document.getElementById('hub-filter-milestone');
            const clearBtn = document.getElementById('hub-clear-filters-btn');

            const query = (searchInput ? searchInput.value : '').toLowerCase().trim();
            const assignee = assigneeSelect ? assigneeSelect.value : '';
            const priority = prioritySelect ? prioritySelect.value : '';
            const dueFilter = dueSelect ? dueSelect.value : '';
            const milestoneFilter = milestoneSelect ? milestoneSelect.value : '';

            const hasActiveFilters = query || assignee || priority || dueFilter || milestoneFilter;
            if (clearBtn) clearBtn.style.display = hasActiveFilters ? 'flex' : 'none';

            const todayStr = new Date().toISOString().slice(0, 10);

            // Filter Kanban Cards
            document.querySelectorAll('.kanban-card').forEach(card => {
                const cardTitle = card.getAttribute('data-title') || '';
                const cardAssignee = card.getAttribute('data-assignee') || '';
                const cardPriority = card.getAttribute('data-priority') || '';
                const cardMilestone = card.getAttribute('data-milestone') || 'none';
                const cardDue = card.getAttribute('data-due') || '';

                let matches = true;

                if (query && !cardTitle.includes(query)) {
                    matches = false;
                }
                if (assignee && cardAssignee !== assignee) {
                    matches = false;
                }
                if (priority && cardPriority !== priority) {
                    matches = false;
                }
                if (milestoneFilter && cardMilestone !== milestoneFilter) {
                    matches = false;
                }
                if (dueFilter) {
                    if (dueFilter === 'overdue' && (!cardDue || cardDue >= todayStr)) matches = false;
                    else if (dueFilter === 'today' && cardDue !== todayStr) matches = false;
                    else if (dueFilter === 'has_due' && !cardDue) matches = false;
                    else if (dueFilter === 'no_due' && cardDue) matches = false;
                }

                card.style.display = matches ? 'block' : 'none';
            });

            // Filter Task Table Rows (List View)
            document.querySelectorAll('#hub-section-tasks tbody tr').forEach(row => {
                const rowText = (row.textContent || '').toLowerCase();
                const rowMilestone = row.getAttribute('data-milestone') || 'none';
                let matches = (!query || rowText.includes(query));
                if (milestoneFilter && rowMilestone !== milestoneFilter) {
                    matches = false;
                }
                row.style.display = matches ? '' : 'none';
            });

            updateKanbanBadgeCounts();
        }

        function clearHubTaskFilters() {
            const searchInput = document.getElementById('hub-task-search-input');
            const assigneeSelect = document.getElementById('hub-filter-assignee');
            const prioritySelect = document.getElementById('hub-filter-priority');
            const dueSelect = document.getElementById('hub-filter-due');
            const milestoneSelect = document.getElementById('hub-filter-milestone');
            const clearBtn = document.getElementById('hub-clear-filters-btn');

            if (searchInput) searchInput.value = '';
            if (assigneeSelect) assigneeSelect.value = '';
            if (prioritySelect) prioritySelect.value = '';
            if (dueSelect) dueSelect.value = '';
            if (milestoneSelect) milestoneSelect.value = '';
            if (clearBtn) clearBtn.style.display = 'none';

            filterHubTasks();
        }

        // ── BULK ACTIONS ENGINE ──────────────────────────────────────────────
        function handleTaskRowClick(event, taskId) {
            const target = event.target;
            if (target.closest('input') || target.closest('button') || target.closest('select') || target.closest('a')) {
                return;
            }
            openTaskInspector(taskId);
        }

        function toggleSelectAllTasks(master) {
            const isChecked = master.checked;
            document.querySelectorAll('#hub-section-tasks tbody tr').forEach(row => {
                if (row.style.display !== 'none') {
                    const cb = row.querySelector('.task-bulk-checkbox');
                    if (cb) cb.checked = isChecked;
                }
            });
            onBulkCheckboxChange();
        }

        function onBulkCheckboxChange() {
            const checkedBoxes = document.querySelectorAll('.task-bulk-checkbox:checked');
            const totalVisible = document.querySelectorAll('#hub-section-tasks tbody tr:not([style*="display: none"]) .task-bulk-checkbox');
            const master = document.getElementById('bulk-select-all');
            const toolbar = document.getElementById('bulk-action-toolbar');
            const countLabel = document.getElementById('bulk-selected-count');

            if (master && totalVisible.length > 0) {
                master.checked = checkedBoxes.length === totalVisible.length;
                master.indeterminate = checkedBoxes.length > 0 && checkedBoxes.length < totalVisible.length;
            }

            if (checkedBoxes.length > 0) {
                if (toolbar) toolbar.style.display = 'flex';
                if (countLabel) countLabel.textContent = `${checkedBoxes.length} {{ __('selected') }}`;
            } else {
                if (toolbar) toolbar.style.display = 'none';
            }

            // Highlight selected rows
            document.querySelectorAll('#hub-section-tasks tbody tr').forEach(row => {
                const cb = row.querySelector('.task-bulk-checkbox');
                if (cb && cb.checked) {
                    row.style.background = 'rgba(66, 119, 76, 0.08)';
                } else {
                    row.style.background = '';
                }
            });
        }

        function getSelectedTaskIds() {
            return Array.from(document.querySelectorAll('.task-bulk-checkbox:checked')).map(cb => cb.value);
        }

        function clearBulkSelection() {
            document.querySelectorAll('.task-bulk-checkbox').forEach(cb => cb.checked = false);
            const master = document.getElementById('bulk-select-all');
            if (master) {
                master.checked = false;
                master.indeterminate = false;
            }
            onBulkCheckboxChange();
            const statusSel = document.getElementById('bulk-status-select');
            const assignSel = document.getElementById('bulk-assign-select');
            if (statusSel) statusSel.value = '';
            if (assignSel) assignSel.value = '';
        }

        async function executeBulkAction(payload, successMsg) {
            const orgId = '{{ $project->organization_id }}';
            try {
                const response = await fetch(`/api/organizations/${orgId}/tasks/bulk`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify(payload),
                });

                const data = await response.json();
                if (!response.ok) throw new Error(data.message || 'Failed bulk action');

                showHubToast('⚡ ' + (successMsg || data.message));
                setTimeout(() => window.location.reload(), 700);
            } catch (err) {
                showHubToast('⚠️ ' + err.message, 'error');
            }
        }

        function bulkChangeStatus() {
            const select = document.getElementById('bulk-status-select');
            const status = select ? select.value : '';
            if (!status) return;
            const taskIds = getSelectedTaskIds();
            if (taskIds.length === 0) return;

            executeBulkAction({
                task_ids: taskIds,
                action: 'update_status',
                status: status
            }, `{{ __('Updated status for') }} ${taskIds.length} {{ __('task(s)') }}`);
        }

        function bulkAssign() {
            const select = document.getElementById('bulk-assign-select');
            const assigneeId = select ? select.value : '';
            if (!assigneeId) return;
            const taskIds = getSelectedTaskIds();
            if (taskIds.length === 0) return;

            executeBulkAction({
                task_ids: taskIds,
                action: 'assign',
                assignee_id: assigneeId === 'null' ? null : assigneeId
            }, `{{ __('Reassigned') }} ${taskIds.length} {{ __('task(s)') }}`);
        }

        function bulkDeleteTasks() {
            const taskIds = getSelectedTaskIds();
            if (taskIds.length === 0) return;

            if (!confirm(`{{ __('Are you sure you want to permanently delete') }} ${taskIds.length} {{ __('selected tasks?') }}`)) {
                return;
            }

            executeBulkAction({
                task_ids: taskIds,
                action: 'delete'
            }, `{{ __('Deleted') }} ${taskIds.length} {{ __('task(s)') }}`);
        }

        // ── MILESTONE & ROADMAP CONTROLS ──
        function openNewMilestoneModal() {
            document.getElementById('new-milestone-modal').style.display = 'flex';
        }
        function closeNewMilestoneModal() {
            document.getElementById('new-milestone-modal').style.display = 'none';
        }

        async function createProjectMilestoneSubmit(e) {
            e.preventDefault();
            const form = e.target;
            const formData = new FormData(form);
            const payload = Object.fromEntries(formData.entries());

            try {
                const res = await fetch(`/api/v1/organizations/${ORG_ID}/projects/{{ $project->id }}/milestones`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': CSRF_TOKEN,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(payload)
                });
                if (res.ok) {
                    showHubToast('🚩 {{ __("Milestone created successfully!") }}');
                    closeNewMilestoneModal();
                    setTimeout(() => window.location.reload(), 500);
                } else {
                    const err = await res.json();
                    alert(err.message || 'Error creating milestone.');
                }
            } catch (err) {
                alert('Network error creating milestone.');
            }
        }

        async function quickToggleMilestoneStatus(milestoneId, newStatus) {
            try {
                const res = await fetch(`/api/v1/organizations/${ORG_ID}/projects/{{ $project->id }}/milestones/${milestoneId}`, {
                    method: 'PATCH',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': CSRF_TOKEN,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ status: newStatus })
                });
                if (res.ok) {
                    showHubToast(newStatus === 'completed' ? '✅ {{ __("Milestone completed!") }}' : '↺ {{ __("Milestone reopened.") }}');
                    setTimeout(() => window.location.reload(), 400);
                } else {
                    const err = await res.json();
                    alert(err.message || 'Error updating milestone.');
                }
            } catch (e) {
                console.error(e);
            }
        }

        async function deleteProjectMilestone(milestoneId) {
            if (!confirm('{{ __("Are you sure you want to delete this milestone? Linked tasks will not be deleted.") }}')) return;
            try {
                const res = await fetch(`/api/v1/organizations/${ORG_ID}/projects/{{ $project->id }}/milestones/${milestoneId}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': CSRF_TOKEN,
                        'Accept': 'application/json'
                    }
                });
                if (res.ok) {
                    showHubToast('🗑️ {{ __("Milestone deleted.") }}');
                    setTimeout(() => window.location.reload(), 400);
                }
            } catch (e) {
                console.error(e);
            }
        }

        // ── STRATEGIC GOALS & OKRs CONTROLS ──
        function openNewGoalModal() {
            document.getElementById('new-goal-modal').style.display = 'flex';
        }
        function closeNewGoalModal() {
            document.getElementById('new-goal-modal').style.display = 'none';
        }

        function toggleGoalMetricFields(type) {
            const row = document.getElementById('goal-custom-target-row');
            if (row) {
                row.style.display = (type === 'number' || type === 'currency') ? 'grid' : 'none';
            }
        }

        async function createProjectGoalSubmit(e) {
            e.preventDefault();
            const form = e.target;
            const formData = new FormData(form);
            const payload = Object.fromEntries(formData.entries());

            try {
                const res = await fetch(`/api/v1/organizations/${ORG_ID}/projects/{{ $project->id }}/goals`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': CSRF_TOKEN,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(payload)
                });
                if (res.ok) {
                    const data = await res.json();
                    const targetType = payload.target_type || 'tasks';
                    let targetTitle = 'Project Delivery';
                    let targetVal = parseFloat(payload.target_value) || 100;
                    let targetUnit = payload.unit || '%';

                    if (targetType === 'tasks') {
                        targetTitle = '{{ __("Tasks Completed") }}';
                        targetUnit = 'Tasks';
                    } else if (targetType === 'milestones') {
                        targetTitle = '{{ __("Milestones Delivered") }}';
                        targetUnit = 'Milestones';
                    } else if (targetType === 'hours') {
                        targetTitle = '{{ __("Hours Budget Logged") }}';
                        targetUnit = 'Hours';
                    }

                    if (data.goal && data.goal.id) {
                        await fetch(`/api/v1/organizations/${ORG_ID}/projects/{{ $project->id }}/goals/${data.goal.id}/targets`, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': CSRF_TOKEN,
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify({
                                title: targetTitle,
                                target_type: targetType,
                                target_value: targetVal,
                                unit: targetUnit
                            })
                        });
                    }

                    showHubToast('🎯 {{ __("Strategic goal created successfully!") }}');
                    closeNewGoalModal();
                    setTimeout(() => window.location.reload(), 500);
                } else {
                    const err = await res.json();
                    alert(err.message || 'Error creating goal.');
                }
            } catch (err) {
                alert('Network error creating goal.');
            }
        }

        async function updateCurrentTaskMilestone(milestoneId) {
            if (!activeInspectedTaskId) return;
            try {
                const res = await fetch(`/api/v1/organizations/${ORG_ID}/tasks/${activeInspectedTaskId}/milestone`, {
                    method: 'PATCH',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': CSRF_TOKEN,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ milestone_id: milestoneId || null })
                });
                if (res.ok) {
                    showHubToast('🚩 {{ __("Task milestone updated!") }}');
                    setTimeout(() => window.location.reload(), 400);
                } else {
                    const err = await res.json();
                    showHubToast(`❌ ${err.message || 'Error updating milestone'}`);
                }
            } catch (e) {
                console.error(e);
            }
        }

        function toggleRecurrenceDetails(val) {
            const extra = document.getElementById('new-task-recurrence-extra');
            if (extra) {
                extra.style.display = val ? 'grid' : 'none';
            }
        }

        async function updateCurrentTaskRecurrence(rule) {
            if (!activeInspectedTaskId) return;
            try {
                const res = await fetch(`/api/v1/organizations/${ORG_ID}/tasks/${activeInspectedTaskId}`, {
                    method: 'PATCH',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': CSRF_TOKEN,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ recurrence_rule: rule || null })
                });
                if (res.ok) {
                    showHubToast('🔄 {{ __("Task repeat schedule updated!") }}');
                    setTimeout(() => window.location.reload(), 400);
                } else {
                    const err = await res.json();
                    showHubToast(`❌ ${err.message || 'Error updating recurrence'}`);
                }
            } catch (e) {
                console.error(e);
            }
        }

        // Initialize Sortable on DOM ready
        window.addEventListener('DOMContentLoaded', () => {
            initSortableKanban();
        });

        // Task Inspector
        async function openTaskInspector(taskId) {
            activeInspectedTaskId = taskId;
            switchInspectorTab('overview');
            const modal = document.getElementById('task-details-modal');
            modal.style.display = 'flex';

            try {
                const res = await fetch(`/api/v1/organizations/${ORG_ID}/tasks/${taskId}`, {
                    headers: { 'Accept': 'application/json' }
                });
                if (!res.ok) return;
                const data = await res.json();
                const t = data.task || data;
                const activities = data.activity || [];

                document.getElementById('task-modal-code').textContent = '#' + (t.task_number || '');
                document.getElementById('task-modal-title').textContent = t.title || '';
                document.getElementById('task-modal-description').textContent = t.description || '—';
                
                const priorityBadge = document.getElementById('task-modal-priority-badge');
                if (priorityBadge) {
                    priorityBadge.textContent = '⚡ ' + (t.priority || 'Normal').toUpperCase();
                }

                const hoursPill = document.getElementById('task-modal-hours-pill');
                if (hoursPill) {
                    hoursPill.textContent = `${t.actual_hours || 0}h / ${t.estimated_hours || 0}h`;
                }

                const statusSelect = document.getElementById('task-modal-status-select');
                if (statusSelect) {
                    statusSelect.value = t.status || 'backlog';
                    const canEdit = {{ ($user->isSuperAdmin() || $membership->role?->slug === 'company_admin' || $membership->hasPermission('tasks.assign') || $membership->hasPermission('tasks.delete') || ($project && $project->manager_id === $user->id)) ? 'true' : 'false' }} || (t.assignee_id == '{{ $user->id }}') || (t.creator_id == '{{ $user->id }}');
                    statusSelect.disabled = !canEdit;
                    statusSelect.title = canEdit ? '' : '{{ __('Only assigned member or manager can edit') }}';
                }

                const msSelect = document.getElementById('task-modal-milestone-select');
                if (msSelect) {
                    msSelect.value = t.milestone_id || '';
                }

                const recSelect = document.getElementById('task-modal-recurrence-select');
                if (recSelect) {
                    recSelect.value = t.recurrence_rule || '';
                }

                // Approval Banner handling
                const appBanner = document.getElementById('task-modal-hub-approval-banner');
                const appText = document.getElementById('task-modal-hub-approval-text');
                const appActions = document.getElementById('task-modal-hub-approval-actions');
                const isProjectManager = {{ ($user->isSuperAdmin() || $membership->role?->slug === 'company_admin' || ($project && $project->manager_id === $user->id)) ? 'true' : 'false' }};

                if (appBanner && appText && appActions) {
                    if (t.approval_status === 'pending_approval') {
                        appBanner.style.display = 'flex';
                        appBanner.style.background = 'rgba(214, 162, 58, 0.15)';
                        appBanner.style.border = '1px solid rgba(214, 162, 58, 0.35)';
                        appBanner.style.color = '#D6A23A';
                        appText.innerHTML = '<span>⏳</span> <span>{{ __("This task is submitted for completion and awaiting PM approval.") }}</span>';
                        if (isProjectManager) {
                            appActions.innerHTML = `
                                <button type="button" onclick="quickApproveHubTask('${t.id}')" class="tactile-btn btn-primary" style="padding: 4px 10px; font-size: 11px;">✓ {{ __("Approve") }}</button>
                                <button type="button" onclick="quickRejectHubTask('${t.id}')" class="tactile-btn" style="background: rgba(217, 107, 95, 0.2); color: #D96B5F; border: 1px solid rgba(217, 107, 95, 0.3); padding: 4px 10px; font-size: 11px;">✕ {{ __("Request Changes") }}</button>
                            `;
                        } else {
                            appActions.innerHTML = '';
                        }
                    } else if (t.approval_status === 'rejected') {
                        appBanner.style.display = 'flex';
                        appBanner.style.background = 'rgba(217, 107, 95, 0.15)';
                        appBanner.style.border = '1px solid rgba(217, 107, 95, 0.35)';
                        appBanner.style.color = '#D96B5F';
                        appText.innerHTML = `<span>⚠️</span> <span><strong>{{ __("Changes Requested:") }}</strong> ${t.rejection_reason || 'Please review feedback.'}</span>`;
                        appActions.innerHTML = '';
                    } else if (t.approval_status === 'approved') {
                        appBanner.style.display = 'flex';
                        appBanner.style.background = 'rgba(79, 155, 95, 0.15)';
                        appBanner.style.border = '1px solid rgba(79, 155, 95, 0.35)';
                        appBanner.style.color = '#4F9B5F';
                        appText.innerHTML = '<span>✅</span> <span>{{ __("Task approved and marked Done by Project Manager.") }}</span>';
                        appActions.innerHTML = '';
                    } else {
                        appBanner.style.display = 'none';
                    }
                }

                // Render Attachments
                const attCount = document.getElementById('task-hub-attachments-count');
                const filesBadge = document.getElementById('task-modal-files-badge');
                const attCont = document.getElementById('task-hub-attachments-container');
                const attachments = t.attachments || [];
                if (attCount) attCount.textContent = attachments.length;
                if (filesBadge) filesBadge.textContent = attachments.length;
                if (attCont) {
                    attCont.innerHTML = '';
                    if (attachments.length === 0) {
                        attCont.innerHTML = '<div style="font-size: 11px; color: var(--ula-text-muted); padding: 6px; grid-column: 1/-1;">{{ __("No files attached to this task.") }}</div>';
                    } else {
                        attachments.forEach(att => {
                            const card = document.createElement('div');
                            card.style = 'background: var(--ula-surface-card); border: 1px solid var(--ula-border-subtle); border-radius: 8px; padding: 8px; display: flex; flex-direction: column; justify-content: space-between; gap: 4px;';
                            card.innerHTML = `
                                <div style="font-weight: 700; font-size: 11px; color: var(--ula-text-primary); overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">📄 ${att.file_name}</div>
                                <div style="font-size: 10px; color: var(--ula-text-muted);">${(att.file_size / 1024).toFixed(1)} KB</div>
                                <div style="display: flex; gap: 4px; margin-top: 4px;">
                                    <a href="${att.file_url || ('/uploads/tasks/' + t.id + '/' + att.file_name)}" target="_blank" download class="tactile-btn btn-secondary" style="flex: 1; padding: 3px 6px; font-size: 10px; text-align: center; text-decoration: none;">⬇</a>
                                    <button type="button" onclick="deleteHubTaskAttachmentAction('${att.id}')" class="tactile-btn" style="background: rgba(217, 107, 95, 0.15); color: #D96B5F; border: 1px solid rgba(217, 107, 95, 0.3); padding: 3px 6px; font-size: 10px;">🗑️</button>
                                </div>
                            `;
                            attCont.appendChild(card);
                        });
                    }
                }

                // Render checklist
                const checkCont = document.getElementById('task-checklist-container');
                checkCont.innerHTML = '';
                (t.checklist_items || []).forEach(ci => {
                    const item = document.createElement('label');
                    item.style = 'display: flex; align-items: center; gap: 8px; font-size: 12px; cursor: pointer; background: var(--ula-surface-page-alt); padding: 6px 10px; border-radius: 6px;';
                    item.innerHTML = `
                        <input type="checkbox" ${ci.is_completed ? 'checked' : ''} onchange="toggleChecklistItem('${t.id}', '${ci.id}', this.checked)" style="accent-color: var(--ula-palm-900);">
                        <span style="${ci.is_completed ? 'text-decoration: line-through; opacity: 0.6;' : ''}">${ci.title}</span>
                    `;
                    checkCont.appendChild(item);
                });

                // Render comments
                const commCountBadge = document.getElementById('task-modal-comments-badge');
                const commCont = document.getElementById('task-comments-feed');
                const comments = t.comments || [];
                if (commCountBadge) commCountBadge.textContent = comments.length;
                commCont.innerHTML = '';
                if (comments.length === 0) {
                    commCont.innerHTML = '<div style="text-align: center; color: var(--ula-text-muted); font-size: 11px; padding: 12px;">{{ __("No discussion comments yet. Be the first to post!") }}</div>';
                } else {
                    comments.forEach(c => {
                        const box = document.createElement('div');
                        box.style = 'background: var(--ula-surface-page-alt); padding: 8px 12px; border-radius: 8px; font-size: 12px; border: 1px solid var(--ula-border-subtle);';
                        box.innerHTML = `
                            <div style="display: flex; justify-content: space-between; margin-bottom: 2px; font-weight: 800; font-size: 11px;">
                                <span style="color: var(--ula-text-primary);">👤 ${c.user ? c.user.name : 'Member'}</span>
                                <span style="color: var(--ula-text-muted);">${new Date(c.created_at).toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'})}</span>
                            </div>
                            <div style="color: var(--ula-text-primary); line-height: 1.4;">${c.body}</div>
                        `;
                        commCont.appendChild(box);
                    });
                }

                // Render Activity & Audit Timeline Feed
                const activityBadge = document.getElementById('task-modal-activity-badge');
                const activityFeed = document.getElementById('task-activity-timeline-feed');
                if (activityBadge) activityBadge.textContent = activities.length;
                if (activityFeed) {
                    activityFeed.innerHTML = '';
                    if (activities.length === 0) {
                        activityFeed.innerHTML = '<div style="text-align: center; color: var(--ula-text-muted); font-size: 11px; padding: 20px;">📜 {{ __("No audit entries recorded yet.") }}</div>';
                    } else {
                        activities.forEach(act => {
                            const item = document.createElement('div');
                            item.className = 'activity-timeline-item';
                            
                            const actorName = act.actor ? act.actor.name : 'System';
                            const initials = actorName.slice(0, 2).toUpperCase();
                            const meta = act.metadata || {};
                            
                            let changeSummary = `<strong>${actorName}</strong> ${act.action} this task.`;
                            if (act.action === 'created') {
                                changeSummary = `✨ <strong>${actorName}</strong> created this task.`;
                            } else if (act.action === 'updated') {
                                const changes = [];
                                if (meta.status) changes.push(`status to <span class="badge-pill badge-neutral" style="font-size: 9px;">${meta.status}</span>`);
                                if (meta.priority) changes.push(`priority to <strong>${meta.priority}</strong>`);
                                if (meta.due_date) changes.push(`due date to <strong>${meta.due_date}</strong>`);
                                if (meta.assignee_id) changes.push(`reassigned task`);
                                if (meta.title) changes.push(`updated title`);
                                if (meta.approval_status) changes.push(`approval status to <strong>${meta.approval_status}</strong>`);
                                
                                changeSummary = `✏️ <strong>${actorName}</strong> updated ` + (changes.length ? changes.join(', ') : 'task details.');
                            } else if (act.action === 'deleted') {
                                changeSummary = `🗑️ <strong>${actorName}</strong> deleted task item.`;
                            }

                            item.innerHTML = `
                                <div class="activity-avatar">${initials}</div>
                                <div class="activity-content-box">
                                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2px;">
                                        <div style="font-size: 12px; color: var(--ula-text-primary);">${changeSummary}</div>
                                        <span style="font-size: 10px; color: var(--ula-text-muted); white-space: nowrap; margin-inline-start: 8px;">${act.relative_time || ''}</span>
                                    </div>
                                </div>
                            `;
                            activityFeed.appendChild(item);
                        });
                    }
                }

            } catch (e) {
                console.error(e);
            }
        }

        function insertHubMentionHandle(name) {
            const input = document.getElementById('new-comment-input');
            if (!input) return;
            input.value += (input.value ? ' ' : '') + '@' + name + ' ';
            input.focus();
        }

        async function uploadHubTaskAttachmentSubmit(e) {
            e.preventDefault();
            const fileInput = document.getElementById('hub-task-file-input');
            if (!fileInput || !fileInput.files.length || !activeInspectedTaskId) return;

            const formData = new FormData();
            formData.append('file', fileInput.files[0]);

            try {
                const res = await fetch(`/tasks/${activeInspectedTaskId}/attachments`, {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': CSRF_TOKEN, 'Accept': 'application/json' },
                    credentials: 'same-origin',
                    body: formData
                });
                const data = await res.json();
                if (!res.ok) {
                    alert(data.message || 'Error uploading file.');
                    return;
                }
                fileInput.value = '';
                showHubToast('📎 ' + "{{ __('Attachment uploaded successfully!') }}");
                openTaskInspector(activeInspectedTaskId);
            } catch (err) {
                alert('Network error uploading attachment.');
            }
        }

        async function deleteHubTaskAttachmentAction(attachmentId) {
            if (!confirm('{{ __("Are you sure you want to delete this attachment?") }}')) return;
            try {
                const res = await fetch(`/tasks/${activeInspectedTaskId}/attachments/${attachmentId}`, {
                    method: 'DELETE',
                    headers: { 'X-CSRF-TOKEN': CSRF_TOKEN, 'Accept': 'application/json' },
                    credentials: 'same-origin'
                });
                if (!res.ok) {
                    alert('Error deleting attachment.');
                    return;
                }
                showHubToast('🗑️ ' + "{{ __('Attachment removed.') }}");
                openTaskInspector(activeInspectedTaskId);
            } catch (err) {
                alert('Network error deleting attachment.');
            }
        }

        async function quickApproveHubTask(taskId) {
            try {
                const res = await fetch(`/tasks/${taskId}/approve`, {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': CSRF_TOKEN, 'Accept': 'application/json' },
                    credentials: 'same-origin'
                });
                const data = await res.json();
                if (!res.ok) {
                    alert(data.message || 'Error approving task.');
                    return;
                }
                showHubToast('🎉 ' + "{{ __('Task approved and marked Done!') }}");
                setTimeout(() => window.location.reload(), 400);
            } catch (err) {
                alert('Network error approving task.');
            }
        }

        async function quickRejectHubTask(taskId) {
            const reason = prompt('{{ __("Please enter reason or required changes:") }}');
            if (!reason) return;

            try {
                const res = await fetch(`/tasks/${taskId}/reject`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF_TOKEN, 'Accept': 'application/json' },
                    credentials: 'same-origin',
                    body: JSON.stringify({ rejection_reason: reason })
                });
                const data = await res.json();
                if (!res.ok) {
                    alert(data.message || 'Error requesting changes.');
                    return;
                }
                showHubToast('⚠️ ' + "{{ __('Task sent back for changes.') }}");
                setTimeout(() => window.location.reload(), 400);
            } catch (err) {
                alert('Network error requesting changes.');
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
                } else {
                    const err = await res.json();
                    showHubToast(err.message || 'Error stopping timer.');
                }
            } catch (e) {
                showHubToast('Network error stopping timer.');
            }
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
