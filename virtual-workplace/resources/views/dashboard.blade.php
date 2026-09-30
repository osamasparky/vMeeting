<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <script>
        (function() {
            const saved = localStorage.getItem('vw_theme') || 'light';
            document.documentElement.setAttribute('data-theme', saved);
            if (saved === 'dark') {
                document.documentElement.classList.add('dark');
            } else {
                document.documentElement.classList.remove('dark');
            }
        })();
    </script>
    <title>{{ $organization->name }} — Workspace Admin Dashboard</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;500;600;700;800;900&family=IBM+Plex+Mono:wght@400;500;600;700&family=IBM+Plex+Sans+Arabic:wght@300;400;500;600;700;800&family=IBM+Plex+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;1,400&family=Material+Symbols+Rounded:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/ulaspace-tokens.css') }}">
    <link rel="stylesheet" href="{{ asset('css/modern-design-system.css') }}">
    <link rel="stylesheet" href="{{ asset('css/ulaspace-dashboard.css') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.2/Sortable.min.js" nonce="{{ $cspNonce ?? '' }}"></script>
    <style>
        /* ═══════════════════════════════════════════════════════════════
           ULASPACE DESIGN SYSTEM — WORKSPACE DASHBOARD
           ═══════════════════════════════════════════════════════════════ */
        :root {
            --ula-gradient-accent: linear-gradient(135deg, var(--ula-palm-900) 0%, var(--ula-palm-700) 100%);
        }

        html, body, button, input, select, textarea, optgroup, .sidebar, .main-content, .nav-tab-btn {
            font-family: 'Cairo', 'IBM Plex Sans Arabic', -apple-system, BlinkMacSystemFont, sans-serif !important;
        }

        [dir="rtl"], [lang="ar"] {
            --font-family: 'Cairo', 'IBM Plex Sans Arabic', sans-serif;
        }

        [dir="ltr"], [lang="en"] {
            --font-family: 'IBM Plex Sans', 'Cairo', sans-serif;
        }

        /* ── Tabs Visibility Enforcement ── */
        .tab-view {
            display: none !important;
        }
        .tab-view.active {
            display: block !important;
            animation: tabViewFadeIn 0.22s cubic-bezier(0.16, 1, 0.3, 1);
        }
        @keyframes tabViewFadeIn {
            from { opacity: 0; transform: translateY(6px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* Dark Theme Tokens */
        [data-theme="dark"], html.dark, body.dark-mode {
            --ula-gradient-accent: linear-gradient(135deg, var(--ula-palm-700) 0%, var(--ula-palm-500) 100%);
        }

        /* Dark Mode specific component refinements */
        [data-theme="dark"] .sidebar-accordion-header {
            background: var(--ula-palm-900);
            border-color: var(--ula-border-subtle);
            color: var(--ula-text-primary);
            box-shadow: 2px 2px 6px rgba(0, 0, 0, 0.35);
        }
        [data-theme="dark"] .sidebar-accordion-header:hover {
            background: var(--ula-palm-800);
            border-color: var(--ula-palm-400);
            color: var(--ula-palm-300);
        }
        [data-theme="dark"] .nav-tab-btn {
            color: var(--ula-sand-400);
        }
        [data-theme="dark"] .nav-tab-btn:hover {
            background: var(--ula-palm-900);
            color: var(--ula-sand-100);
            border-color: var(--ula-border-subtle);
        }
        [data-theme="dark"] .nav-icon-tile {
            background: var(--ula-palm-950);
            border-color: var(--ula-border-subtle);
            color: var(--ula-palm-300);
        }
        [data-theme="dark"] .nav-badge-pill {
            background: var(--ula-palm-900);
            color: var(--ula-palm-300);
            border-color: var(--ula-border-subtle);
        }
        [data-theme="dark"] .go-premium-card {
            background: linear-gradient(135deg, var(--ula-palm-950) 0%, var(--ula-black) 100%);
            border-color: var(--ula-gold-600);
        }
        [data-theme="dark"] .go-premium-card div {
            color: var(--ula-gold-300) !important;
        }
        [data-theme="dark"] .hero-welcome-card {
            background: linear-gradient(135deg, var(--ula-palm-900) 0%, var(--ula-palm-950) 100%) !important;
            border-color: var(--ula-border-subtle) !important;
        }
        /* .card already uses var(--ula-surface-card)/var(--ula-border-subtle),
           both theme-aware, so no separate dark-mode override is needed. */
        [data-theme="dark"] .data-table thead th {
            background: var(--ula-palm-900);
            border-color: var(--ula-border-subtle);
            color: var(--ula-text-muted);
        }
        [data-theme="dark"] .data-table tbody tr {
            border-color: var(--ula-border-subtle);
        }
        [data-theme="dark"] .data-table tbody tr:hover {
            background: var(--ula-palm-900);
        }
        [data-theme="dark"] .modal-card {
            background: var(--ula-palm-950);
            border-color: var(--ula-border-subtle);
        }
        [data-theme="dark"] .modal-header {
            border-color: var(--ula-border-subtle);
        }

        /* Layered like Tailwind's own preflight so component utilities (px-4, ps-10…) can win over it. */
        @layer base { * { margin: 0; padding: 0; box-sizing: border-box; -webkit-font-smoothing: antialiased; } }
        body {
            background: var(--ula-surface-page);
            color: var(--ula-text-primary);
            min-height: 100vh;
            display: flex;
            overflow-x: hidden;
            transition: background-color 0.3s ease, color 0.3s ease;
        }

        /* ── Dark Chrome Sidebar (matches the landing nav / auth side panel) ── */
        .sidebar {
            width: 270px;
            background: var(--ula-surface-dark);
            border-inline-end: 1px solid var(--ula-border-on-dark);
            padding: 24px 14px;
            display: flex;
            flex-direction: column;
            position: fixed;
            inset-inline-start: 0;
            top: 0;
            height: 100vh;
            z-index: 50;
            box-shadow: 4px 0 24px rgba(36, 92, 58, 0.04);
            transition: width 0.28s cubic-bezier(0.16, 1, 0.3, 1), padding 0.28s cubic-bezier(0.16, 1, 0.3, 1);
            overflow-x: hidden;
        }

        /* ── Mini / Icon-Only Collapsed Sidebar ── */
        .sidebar.sidebar-collapsed {
            width: 76px !important;
            padding: 20px 8px !important;
            transform: none !important;
            align-items: center;
        }

        .sidebar.sidebar-collapsed .sidebar-logo-text,
        .sidebar.sidebar-collapsed .sidebar-logo > div > div:last-child {
            display: none !important;
        }

        .sidebar.sidebar-collapsed .sidebar-brand-wrapper {
            flex-direction: column !important;
            gap: 10px !important;
            align-items: center !important;
            margin-bottom: 16px !important;
            padding: 0 !important;
        }

        .sidebar.sidebar-collapsed .sidebar-logo {
            justify-content: center !important;
            margin: 0 !important;
            padding: 0 !important;
        }

        .sidebar.sidebar-collapsed .sidebar-profile-card {
            padding: 8px 4px !important;
            margin-bottom: 12px !important;
            border-radius: 14px !important;
            width: 100% !important;
        }

        .sidebar.sidebar-collapsed .sidebar-profile-name,
        .sidebar.sidebar-collapsed .sidebar-profile-email,
        .sidebar.sidebar-collapsed .sidebar-profile-badge {
            display: none !important;
        }

        .sidebar.sidebar-collapsed .sidebar-profile-avatar-wrap {
            width: 44px !important;
            height: 44px !important;
            margin: 0 !important;
        }

        .sidebar.sidebar-collapsed .sidebar-accordion {
            width: 100% !important;
            margin-bottom: 6px !important;
        }

        .sidebar.sidebar-collapsed .sidebar-accordion-header {
            padding: 8px 4px !important;
            justify-content: center !important;
            border-radius: 10px !important;
            position: relative;
        }

        .sidebar.sidebar-collapsed .sidebar-accordion-header > span > span:not(.nav-icon-tile),
        .sidebar.sidebar-collapsed .sidebar-accordion-chevron {
            display: none !important;
        }

        .sidebar.sidebar-collapsed .sidebar-accordion.collapsed .sidebar-accordion-content {
            max-height: 500px !important;
            opacity: 1 !important;
            display: flex !important;
            pointer-events: auto !important;
        }

        .sidebar.sidebar-collapsed .nav-tab-btn {
            padding: 7px 0 !important;
            justify-content: center !important;
            width: 100% !important;
            border-radius: 10px !important;
            position: relative;
        }

        .sidebar.sidebar-collapsed .nav-tab-btn > span > span:not(.nav-icon-tile),
        .sidebar.sidebar-collapsed .nav-tab-btn .nav-badge-pill,
        .sidebar.sidebar-collapsed .nav-tab-btn > strong {
            display: none !important;
        }

        .sidebar.sidebar-collapsed .nav-tab-btn .nav-icon-tile {
            margin: 0 !important;
            width: 36px !important;
            height: 36px !important;
            font-size: 16px !important;
        }

        .sidebar.sidebar-collapsed .nav-tab-btn:hover::after,
        .sidebar.sidebar-collapsed .sidebar-accordion-header:hover::after {
            content: attr(data-tooltip);
            position: absolute;
            inset-inline-start: calc(100% + 12px);
            top: 50%;
            transform: translateY(-50%);
            background: var(--ula-palm-900);
            color: var(--ula-sand-100);
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

        .sidebar.sidebar-collapsed .go-premium-card {
            display: none !important;
        }

        .sidebar-logo {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 0 4px;
            margin-bottom: 20px;
            cursor: pointer;
            text-decoration: none;
        }

        .sidebar-logo-icon {
            width: 40px;
            height: 40px;
            background: var(--ula-gradient-accent);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            color: var(--ula-white);
            box-shadow: var(--ula-shadow-sm);
            flex-shrink: 0;
        }

        .sidebar-logo-text {
            font-size: 16px;
            font-weight: 900;
            color: var(--ula-text-on-dark);
            letter-spacing: -0.4px;
            line-height: 1.2;
        }

        /* Sidebar Profile Card */
        .sidebar-profile-card {
            background: var(--ula-control-dark-fill);
            border: 1px solid var(--ula-control-dark-border-subtle);
            border-radius: var(--ula-radius-lg);
            padding: 14px;
            margin-bottom: 20px;
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
            position: relative;
            box-shadow: var(--ula-shadow-xs);
            cursor: pointer;
            transition: all 0.2s ease;
        }
        .sidebar-profile-card:hover {
            transform: translateY(-2px);
            box-shadow: var(--ula-shadow-md);
            border-color: var(--ula-highlight-default);
        }

        .sidebar-profile-avatar-wrap {
            position: relative;
            width: 58px;
            height: 58px;
            margin-bottom: 8px;
        }
        .sidebar-profile-avatar {
            width: 100%;
            height: 100%;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid var(--ula-white);
            box-shadow: 0 4px 12px rgba(36, 92, 58, 0.15);
        }
        .sidebar-profile-status {
            position: absolute;
            bottom: 2px;
            inset-inline-end: 2px;
            width: 13px;
            height: 13px;
            border-radius: 50%;
            background: var(--ula-status-success);
            border: 2px solid var(--ula-white);
            box-shadow: 0 0 6px rgba(79, 155, 95, 0.6);
        }

        /* ── Sidebar Accordions (UlaSpace Clean Design) ── */
        .sidebar-accordion {
            margin-bottom: 6px;
        }

        .sidebar-accordion-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            font-size: 11px;
            font-weight: 600;
            color: var(--ula-text-on-dark-subtle);
            text-transform: uppercase;
            letter-spacing: 0.6px;
            padding: 8px 10px;
            border-radius: var(--ula-radius-xs);
            cursor: pointer;
            user-select: none;
            transition: all 0.15s ease;
            background: transparent;
            border: 1px solid transparent;
            margin-bottom: 2px;
        }

        .sidebar-accordion-header:hover {
            color: var(--ula-text-on-dark);
            background: var(--ula-control-dark-fill);
        }

        .sidebar-accordion-chevron {
            font-size: 9px;
            transition: transform 0.25s cubic-bezier(0.4, 0, 0.2, 1);
            color: var(--ula-text-on-dark-subtle);
            display: inline-block;
        }

        .sidebar-accordion.collapsed .sidebar-accordion-chevron {
            transform: rotate({{ app()->getLocale() === 'ar' ? '90deg' : '-90deg' }});
        }

        .sidebar-accordion-content {
            display: flex;
            flex-direction: column;
            gap: 2px;
            overflow: hidden;
            transition: max-height 0.3s cubic-bezier(0.4, 0, 0.2, 1), opacity 0.25s ease;
            max-height: 2000px;
            opacity: 1;
            padding: 2px 0;
        }

        .sidebar-accordion.collapsed .sidebar-accordion-content {
            max-height: 0;
            opacity: 0;
            padding-top: 0;
            pointer-events: none;
        }

        .nav-tab-btn {
            width: 100%;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 8px;
            padding: 8px 12px;
            border-radius: var(--ula-radius-sm);
            color: var(--ula-text-on-dark-muted);
            background: transparent;
            border: 1px solid transparent;
            font-family: inherit;
            font-size: 13px;
            font-weight: 500;
            cursor: pointer;
            text-align: start;
            transition: all 0.15s ease;
            text-decoration: none;
            margin-bottom: 2px;
        }

        .nav-tab-btn:hover {
            background: var(--ula-control-dark-fill);
            color: var(--ula-text-on-dark);
        }

        .nav-tab-btn.active {
            background: var(--ula-control-dark-fill-strong) !important;
            color: var(--ula-text-on-dark) !important;
            border-color: transparent !important;
            font-weight: 700 !important;
            box-shadow: inset {{ app()->getLocale() === 'ar' ? '-3px' : '3px' }} 0 0 var(--ula-highlight-default) !important;
        }
        .nav-tab-btn.active span,
        .nav-tab-btn.active strong {
            color: var(--ula-text-on-dark) !important;
        }
        .nav-tab-btn.active .nav-icon-tile {
            background: rgba(211, 165, 83, 0.22) !important;
            border-color: var(--ula-gold-400) !important;
            color: var(--ula-gold-300) !important;
        }

)->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <script>
        (function() {
            const saved = localStorage.getItem('vw_theme') || 'light';
            document.documentElement.setAttribute('data-theme', saved);
            if (saved === 'dark') {
                document.documentElement.classList.add('dark');
            } else {
                document.documentElement.classList.remove('dark');
            }
        })();
    </script>
    <title>{{ $organization->name }} — Workspace Admin Dashboard</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;500;600;700;800;900&family=IBM+Plex+Mono:wght@400;500;600;700&family=IBM+Plex+Sans+Arabic:wght@300;400;500;600;700;800&family=IBM+Plex+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;1,400&family=Material+Symbols+Rounded:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/ulaspace-tokens.css') }}">
    <link rel="stylesheet" href="{{ asset('css/modern-design-system.css') }}">
    <link rel="stylesheet" href="{{ asset('css/ulaspace-dashboard.css') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.2/Sortable.min.js" nonce="{{ $cspNonce ?? '' }}"></script>
    <style>
        /* ═══════════════════════════════════════════════════════════════
           ULASPACE DESIGN SYSTEM — WORKSPACE DASHBOARD
           ═══════════════════════════════════════════════════════════════ */
        :root {
            --ula-gradient-accent: linear-gradient(135deg, var(--ula-palm-900) 0%, var(--ula-palm-700) 100%);
        }

        html, body, button, input, select, textarea, optgroup, .sidebar, .main-content, .nav-tab-btn {
            font-family: 'Cairo', 'IBM Plex Sans Arabic', -apple-system, BlinkMacSystemFont, sans-serif !important;
        }

        [dir="rtl"], [lang="ar"] {
            --font-family: 'Cairo', 'IBM Plex Sans Arabic', sans-serif;
        }

        [dir="ltr"], [lang="en"] {
            --font-family: 'IBM Plex Sans', 'Cairo', sans-serif;
        }

        /* ── Tabs Visibility Enforcement ── */
        .tab-view {
            display: none !important;
        }
        .tab-view.active {
            display: block !important;
            animation: tabViewFadeIn 0.22s cubic-bezier(0.16, 1, 0.3, 1);
        }
        @keyframes tabViewFadeIn {
            from { opacity: 0; transform: translateY(6px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* Dark Theme Tokens */
        [data-theme="dark"], html.dark, body.dark-mode {
            --ula-gradient-accent: linear-gradient(135deg, var(--ula-palm-700) 0%, var(--ula-palm-500) 100%);
        }

        /* Dark Mode specific component refinements */
        [data-theme="dark"] .sidebar-accordion-header {
            background: var(--ula-palm-900);
            border-color: var(--ula-border-subtle);
            color: var(--ula-text-primary);
            box-shadow: 2px 2px 6px rgba(0, 0, 0, 0.35);
        }
        [data-theme="dark"] .sidebar-accordion-header:hover {
            background: var(--ula-palm-800);
            border-color: var(--ula-palm-400);
            color: var(--ula-palm-300);
        }
        [data-theme="dark"] .nav-tab-btn {
            color: var(--ula-sand-400);
        }
        [data-theme="dark"] .nav-tab-btn:hover {
            background: var(--ula-palm-900);
            color: var(--ula-sand-100);
            border-color: var(--ula-border-subtle);
        }
        [data-theme="dark"] .nav-icon-tile {
            background: var(--ula-palm-950);
            border-color: var(--ula-border-subtle);
            color: var(--ula-palm-300);
        }
        [data-theme="dark"] .nav-badge-pill {
            background: var(--ula-palm-900);
            color: var(--ula-palm-300);
            border-color: var(--ula-border-subtle);
        }
        [data-theme="dark"] .go-premium-card {
            background: linear-gradient(135deg, var(--ula-palm-950) 0%, var(--ula-black) 100%);
            border-color: var(--ula-gold-600);
        }
        [data-theme="dark"] .go-premium-card div {
            color: var(--ula-gold-300) !important;
        }
        [data-theme="dark"] .hero-welcome-card {
            background: linear-gradient(135deg, var(--ula-palm-900) 0%, var(--ula-palm-950) 100%) !important;
            border-color: var(--ula-border-subtle) !important;
        }
        /* .card already uses var(--ula-surface-card)/var(--ula-border-subtle),
           both theme-aware, so no separate dark-mode override is needed. */
        [data-theme="dark"] .data-table thead th {
            background: var(--ula-palm-900);
            border-color: var(--ula-border-subtle);
            color: var(--ula-text-muted);
        }
        [data-theme="dark"] .data-table tbody tr {
            border-color: var(--ula-border-subtle);
        }
        [data-theme="dark"] .data-table tbody tr:hover {
            background: var(--ula-palm-900);
        }
        [data-theme="dark"] .modal-card {
            background: var(--ula-palm-950);
            border-color: var(--ula-border-subtle);
        }
        [data-theme="dark"] .modal-header {
            border-color: var(--ula-border-subtle);
        }

        /* Layered like Tailwind's own preflight so component utilities (px-4, ps-10…) can win over it. */
        @layer base { * { margin: 0; padding: 0; box-sizing: border-box; -webkit-font-smoothing: antialiased; } }
        body {
            background: var(--ula-surface-page);
            color: var(--ula-text-primary);
            min-height: 100vh;
            display: flex;
            overflow-x: hidden;
            transition: background-color 0.3s ease, color 0.3s ease;
        }

        /* ── Dark Chrome Sidebar (matches the landing nav / auth side panel) ── */
        .sidebar {
            width: 270px;
            background: var(--ula-surface-dark);
            border-inline-end: 1px solid var(--ula-border-on-dark);
            padding: 24px 14px;
            display: flex;
            flex-direction: column;
            position: fixed;
            inset-inline-start: 0;
            top: 0;
            height: 100vh;
            z-index: 50;
            box-shadow: 4px 0 24px rgba(36, 92, 58, 0.04);
            transition: width 0.28s cubic-bezier(0.16, 1, 0.3, 1), padding 0.28s cubic-bezier(0.16, 1, 0.3, 1);
            overflow-x: hidden;
        }

        /* ── Mini / Icon-Only Collapsed Sidebar ── */
        .sidebar.sidebar-collapsed {
            width: 76px !important;
            padding: 20px 8px !important;
            transform: none !important;
            align-items: center;
        }

        .sidebar.sidebar-collapsed .sidebar-logo-text,
        .sidebar.sidebar-collapsed .sidebar-logo > div > div:last-child {
            display: none !important;
        }

        .sidebar.sidebar-collapsed .sidebar-brand-wrapper {
            flex-direction: column !important;
            gap: 10px !important;
            align-items: center !important;
            margin-bottom: 16px !important;
            padding: 0 !important;
        }

        .sidebar.sidebar-collapsed .sidebar-logo {
            justify-content: center !important;
            margin: 0 !important;
            padding: 0 !important;
        }

        .sidebar.sidebar-collapsed .sidebar-profile-card {
            padding: 8px 4px !important;
            margin-bottom: 12px !important;
            border-radius: 14px !important;
            width: 100% !important;
        }

        .sidebar.sidebar-collapsed .sidebar-profile-name,
        .sidebar.sidebar-collapsed .sidebar-profile-email,
        .sidebar.sidebar-collapsed .sidebar-profile-badge {
            display: none !important;
        }

        .sidebar.sidebar-collapsed .sidebar-profile-avatar-wrap {
            width: 44px !important;
            height: 44px !important;
            margin: 0 !important;
        }

        .sidebar.sidebar-collapsed .sidebar-accordion {
            width: 100% !important;
            margin-bottom: 6px !important;
        }

        .sidebar.sidebar-collapsed .sidebar-accordion-header {
            padding: 8px 4px !important;
            justify-content: center !important;
            border-radius: 10px !important;
            position: relative;
        }

        .sidebar.sidebar-collapsed .sidebar-accordion-header > span > span:not(.nav-icon-tile),
        .sidebar.sidebar-collapsed .sidebar-accordion-chevron {
            display: none !important;
        }

        .sidebar.sidebar-collapsed .sidebar-accordion.collapsed .sidebar-accordion-content {
            max-height: 500px !important;
            opacity: 1 !important;
            display: flex !important;
            pointer-events: auto !important;
        }

        .sidebar.sidebar-collapsed .nav-tab-btn {
            padding: 7px 0 !important;
            justify-content: center !important;
            width: 100% !important;
            border-radius: 10px !important;
            position: relative;
        }

        .sidebar.sidebar-collapsed .nav-tab-btn > span > span:not(.nav-icon-tile),
        .sidebar.sidebar-collapsed .nav-tab-btn .nav-badge-pill,
        .sidebar.sidebar-collapsed .nav-tab-btn > strong {
            display: none !important;
        }

        .sidebar.sidebar-collapsed .nav-tab-btn .nav-icon-tile {
            margin: 0 !important;
            width: 36px !important;
            height: 36px !important;
            font-size: 16px !important;
        }

        .sidebar.sidebar-collapsed .nav-tab-btn:hover::after,
        .sidebar.sidebar-collapsed .sidebar-accordion-header:hover::after {
            content: attr(data-tooltip);
            position: absolute;
            inset-inline-start: calc(100% + 12px);
            top: 50%;
            transform: translateY(-50%);
            background: var(--ula-palm-900);
            color: var(--ula-sand-100);
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

        .sidebar.sidebar-collapsed .go-premium-card {
            display: none !important;
        }

        .sidebar-logo {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 0 4px;
            margin-bottom: 20px;
            cursor: pointer;
            text-decoration: none;
        }

        .sidebar-logo-icon {
            width: 40px;
            height: 40px;
            background: var(--ula-gradient-accent);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            color: var(--ula-white);
            box-shadow: var(--ula-shadow-sm);
            flex-shrink: 0;
        }

        .sidebar-logo-text {
            font-size: 16px;
            font-weight: 900;
            color: var(--ula-text-on-dark);
            letter-spacing: -0.4px;
            line-height: 1.2;
        }

        /* Sidebar Profile Card */
        .sidebar-profile-card {
            background: var(--ula-control-dark-fill);
            border: 1px solid var(--ula-control-dark-border-subtle);
            border-radius: var(--ula-radius-lg);
            padding: 14px;
            margin-bottom: 20px;
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
            position: relative;
            box-shadow: var(--ula-shadow-xs);
            cursor: pointer;
            transition: all 0.2s ease;
        }
        .sidebar-profile-card:hover {
            transform: translateY(-2px);
            box-shadow: var(--ula-shadow-md);
            border-color: var(--ula-highlight-default);
        }

        .sidebar-profile-avatar-wrap {
            position: relative;
            width: 58px;
            height: 58px;
            margin-bottom: 8px;
        }
        .sidebar-profile-avatar {
            width: 100%;
            height: 100%;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid var(--ula-white);
            box-shadow: 0 4px 12px rgba(36, 92, 58, 0.15);
        }
        .sidebar-profile-status {
            position: absolute;
            bottom: 2px;
            inset-inline-end: 2px;
            width: 13px;
            height: 13px;
            border-radius: 50%;
            background: var(--ula-status-success);
            border: 2px solid var(--ula-white);
            box-shadow: 0 0 6px rgba(79, 155, 95, 0.6);
        }

        /* ── Sidebar Accordions (UlaSpace Clean Design) ── */
        .sidebar-accordion {
            margin-bottom: 6px;
        }

        .sidebar-accordion-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            font-size: 11px;
            font-weight: 600;
            color: var(--ula-text-on-dark-subtle);
            text-transform: uppercase;
            letter-spacing: 0.6px;
            padding: 8px 10px;
            border-radius: var(--ula-radius-xs);
            cursor: pointer;
            user-select: none;
            transition: all 0.15s ease;
            background: transparent;
            border: 1px solid transparent;
            margin-bottom: 2px;
        }

        .sidebar-accordion-header:hover {
            color: var(--ula-text-on-dark);
            background: var(--ula-control-dark-fill);
        }

        .sidebar-accordion-chevron {
            font-size: 9px;
            transition: transform 0.25s cubic-bezier(0.4, 0, 0.2, 1);
            color: var(--ula-text-on-dark-subtle);
            display: inline-block;
        }

        .sidebar-accordion.collapsed .sidebar-accordion-chevron {
            transform: rotate({{ app()->getLocale() === 'ar' ? '90deg' : '-90deg' }});
        }

        .sidebar-accordion-content {
            display: flex;
            flex-direction: column;
            gap: 2px;
            overflow: hidden;
            transition: max-height 0.3s cubic-bezier(0.4, 0, 0.2, 1), opacity 0.25s ease;
            max-height: 2000px;
            opacity: 1;
            padding: 2px 0;
        }

        .sidebar-accordion.collapsed .sidebar-accordion-content {
            max-height: 0;
            opacity: 0;
            padding-top: 0;
            pointer-events: none;
        }

        .nav-tab-btn {
            width: 100%;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 8px;
            padding: 8px 12px;
            border-radius: var(--ula-radius-sm);
            color: var(--ula-text-on-dark-muted);
            background: transparent;
            border: 1px solid transparent;
            font-family: inherit;
            font-size: 13px;
            font-weight: 500;
            cursor: pointer;
            text-align: start;
            transition: all 0.15s ease;
            text-decoration: none;
            margin-bottom: 2px;
        }

        .nav-tab-btn:hover {
            background: var(--ula-control-dark-fill);
            color: var(--ula-text-on-dark);
        }

        .nav-tab-btn.active {
            background: var(--ula-control-dark-fill-strong) !important;
            color: var(--ula-text-on-dark) !important;
            border-color: transparent !important;
            font-weight: 700 !important;
            box-shadow: inset {{ app()->getLocale() === 'ar' ? '-3px' : '3px' }} 0 0 var(--ula-highlight-default) !important;
        }
        .nav-tab-btn.active span,
        .nav-tab-btn.active strong {
            color: var(--ula-text-on-dark) !important;
        }
        .nav-tab-btn.active .nav-icon-tile {
            background: rgba(211, 165, 83, 0.22) !important;
            border-color: var(--ula-gold-400) !important;
            color: var(--ula-gold-300) !important;
        }

        .nav-icon-tile {
            width: 28px;
            height: 28px;
            border-radius: 9px;
            background: var(--ula-control-dark-fill);
            border: 1px solid var(--ula-control-dark-border-subtle);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 14px;
            color: var(--ula-text-on-dark-muted);
            flex-shrink: 0;
            transition: all 0.2s ease;
        }
        .nav-tab-btn.active .nav-icon-tile {
            background: rgba(255, 255, 255, 0.22);
            border-color: rgba(255, 255, 255, 0.45);
            color: var(--ula-white) !important;
            box-shadow: inset 0 1px 1px rgba(255, 255, 255, 0.4);
        }

        .nav-badge-pill {
            background: var(--ula-control-dark-fill-strong);
            color: var(--ula-text-on-dark);
            font-size: 10px;
            font-weight: 800;
            padding: 2px 7px;
            border-radius: 8px;
            border: 1px solid var(--ula-control-dark-border);
            transition: all 0.2s ease;
        }
        .nav-tab-btn.active .nav-badge-pill {
            background: rgba(255, 255, 255, 0.25);
            color: var(--ula-white) !important;
            border-color: rgba(255, 255, 255, 0.45);
        }

        /* Go Premium Gradient Card */
        .go-premium-card {
            margin-top: auto;
            background: linear-gradient(135deg, var(--ula-palm-950) 0%, var(--ula-black) 100%);
            border: 1px solid var(--ula-gold-600);
            border-radius: var(--ula-radius-lg);
            padding: 16px 14px;
            text-align: center;
            box-shadow: 0 12px 28px rgba(0, 0, 0, 0.3);
        }
        .go-premium-card div {
            color: var(--ula-gold-300);
        }
        .go-premium-crown {
            font-size: 28px;
            margin-bottom: 4px;
            display: inline-block;
            filter: drop-shadow(0 4px 8px rgba(214, 162, 58, 0.35));
        }

        /* ── Main Content Container ── */
        .main-content {
            flex: 1;
            margin-inline-start: 270px;
            padding: 28px 36px;
            max-width: 1440px;
            width: calc(100% - 270px);
            transition: margin-inline-start 0.3s cubic-bezier(0.4, 0, 0.2, 1), width 0.3s ease;
        }

        .main-content.sidebar-collapsed {
            margin-inline-start: 76px !important;
            width: calc(100% - 76px) !important;
            max-width: calc(100% - 76px) !important;
        }

        /* ── Top Header Navigation Bar ── */
        .top-app-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 24px;
            gap: 16px;
            flex-wrap: wrap;
        }

        .header-title-area h1 {
            font-size: 24px;
            font-weight: 900;
            color: var(--ula-text-primary);
            letter-spacing: -0.5px;
        }
        .header-title-area p {
            font-size: 13px;
            color: var(--ula-text-secondary);
            font-weight: 500;
            margin-top: 2px;
        }

        .header-search-bar {
            flex: 1;
            max-width: 440px;
            position: relative;
            display: flex;
            align-items: center;
        }
        .header-search-input {
            width: 100%;
            background: var(--ula-surface-card);
            border: 1px solid var(--ula-border-subtle);
            border-radius: var(--ula-radius-pill);
            padding: 10px 18px;
            padding-inline-start: 40px;
            font-size: 13px;
            font-weight: 600;
            color: var(--ula-text-primary);
            box-shadow: var(--ula-shadow-xs);
            outline: none;
            transition: all 0.2s ease;
        }
        .header-search-input:focus {
            border-color: var(--ula-palm-900);
            box-shadow: 0 0 0 3px rgba(36, 92, 58, 0.12), var(--ula-shadow-xs);
        }
        .header-search-icon {
            position: absolute;
            inset-inline-start: 14px;
            font-size: 15px;
            color: var(--ula-text-muted);
            pointer-events: none;
        }

        .header-actions-group {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .header-icon-btn {
            position: relative;
            width: 40px;
            height: 40px;
            border-radius: 12px;
            background: var(--ula-surface-card);
            border: 1px solid var(--ula-border-subtle);
            box-shadow: var(--ula-shadow-xs);
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--ula-text-primary);
            font-size: 16px;
            cursor: pointer;
            transition: all 0.2s ease;
            text-decoration: none;
        }
        .header-icon-btn:hover {
            transform: translateY(-2px);
            border-color: var(--ula-palm-900);
            box-shadow: var(--ula-shadow-md);
        }
        .header-icon-badge {
            position: absolute;
            top: -3px;
            inset-inline-end: -3px;
            width: 16px;
            height: 16px;
            background: var(--ula-status-danger);
            color: white;
            font-size: 9px;
            font-weight: 900;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 2px solid var(--ula-accent-default);
        }

        /* ── Tactile Buttons ── */
        .header-btn, .tactile-btn {
            padding: 10px 18px;
            border-radius: 12px;
            font-size: 13px;
            font-weight: 800;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            cursor: pointer;
            border: none;
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .btn-primary {
            background: var(--ula-gradient-accent);
            color: var(--ula-accent-fg);
            border: 1px solid var(--ula-palm-800);
            box-shadow: var(--ula-shadow-sm);
        }
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: var(--ula-shadow-md);
            color: var(--ula-accent-fg);
        }
        .btn-primary:active {
            transform: translateY(1px);
            box-shadow: var(--ula-shadow-xs);
        }

        .btn-secondary {
            background: var(--ula-surface-page-alt);
            color: var(--ula-text-primary);
            border: 1px solid var(--ula-border-subtle);
            box-shadow: var(--ula-shadow-xs);
        }
        .btn-secondary:hover {
            transform: translateY(-2px);
            border-color: var(--ula-palm-900);
            box-shadow: var(--ula-shadow-md);
        }

        .btn-success {
            background: var(--ula-status-success);
            color: var(--ula-accent-fg);
            border: 1px solid var(--ula-palm-800);
            box-shadow: var(--ula-shadow-sm);
        }

        .btn-outline {
            background: var(--ula-surface-card);
            border: 1px solid var(--ula-border-subtle);
            color: var(--ula-text-primary);
            box-shadow: var(--ula-shadow-xs);
        }

        /* ── Cards & Surfaces ── */
        .card {
            background: var(--ula-surface-card);
            border: 1px solid var(--ula-border-subtle);
            border-radius: var(--ula-radius-xl);
            padding: 22px 24px;
            box-shadow: var(--ula-shadow-xs);
            margin-bottom: 24px;
            color: var(--ula-text-primary);
            position: relative;
            transition: all 0.2s ease;
        }
        .card:hover {
            box-shadow: var(--ula-shadow-md);
        }

        .card-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 18px;
            flex-wrap: wrap;
            gap: 10px;
        }

        .card-title {
            font-size: 16px;
            font-weight: 800;
            color: var(--ula-text-primary);
            letter-spacing: -0.3px;
        }

        /* ── 3D Glossy Icon Containers (White Icons on Rich Green Gradient) ── */
        .icon-box-3d {
            width: 50px;
            height: 50px;
            border-radius: 16px;
            background: linear-gradient(145deg, var(--ula-accent-default) 0%, var(--ula-palm-800) 100%);
            border: 1px solid var(--ula-palm-800);
            box-shadow: var(--ula-shadow-md), inset 0 1.5px 1.5px rgba(255, 255, 255, 0.55), inset 0 -2px 4px rgba(0, 0, 0, 0.22);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 22px;
            flex-shrink: 0;
            color: var(--ula-white) !important;
            text-shadow: 0 1px 2px rgba(0, 0, 0, 0.3);
            transition: transform 0.22s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .icon-box-3d.green {
            background: linear-gradient(145deg, var(--ula-accent-default) 0%, var(--ula-palm-800) 100%);
            color: var(--ula-white) !important;
            border-color: var(--ula-palm-800);
        }

        /* ── KPI Stat Cards ── */
        .kpi-grid {
            display: grid;
            grid-template-columns: repeat(5, 1fr);
            gap: 16px;
            margin-bottom: 24px;
        }
        @media (max-width: 1200px) {
            .kpi-grid { grid-template-columns: repeat(3, 1fr); }
        }
        @media (max-width: 768px) {
            .kpi-grid { grid-template-columns: 1fr 1fr; }
        }
        @media (max-width: 480px) {
            .kpi-grid { grid-template-columns: 1fr; }
        }

        /* .kpi-card/.kpi-info/.kpi-title/.kpi-value/.kpi-sub/.kpi-header/
           .kpi-icon-box/.kpi-trend intentionally NOT defined here — this
           page loads ulaspace-dashboard.css, which owns the single shared
           definition every page rendering .kpi-card markup uses. */

        /* ── Badges ── */
        .badge {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 8px;
            font-size: 11px;
            font-weight: 800;
            letter-spacing: 0.2px;
        }
        .badge-green, .badge-teal, .badge-blue { background: var(--ula-tone-palm-bg); color: var(--ula-tone-palm-fg); border: 1px solid var(--ula-border-subtle); }
        .badge-amber { background: var(--ula-tone-gold-bg); color: var(--ula-tone-gold-fg); border: 1px solid var(--ula-border-subtle); }
        .badge-crimson { background: var(--ula-tone-terracotta-bg); color: var(--ula-tone-terracotta-fg); border: 1px solid var(--ula-border-subtle); }
        .badge-gray { background: var(--ula-tone-stone-bg); color: var(--ula-tone-stone-fg); border: 1px solid var(--ula-border-subtle); }

        /* ── Data Tables ── */
        .data-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            font-size: 13px;
            text-align: start;
        }
        .data-table th {
            padding: 14px 16px;
            background: var(--ula-surface-page-alt);
            color: var(--ula-text-secondary);
            font-weight: 800;
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 0.6px;
            border-bottom: 1px solid var(--ula-border-subtle);
        }
        .data-table th:first-child { border-start-start-radius: 12px; }
        .data-table th:last-child { border-start-end-radius: 12px; }
        .data-table td {
            padding: 14px 16px;
            border-bottom: 1px solid var(--ula-border-subtle);
            color: var(--ula-text-primary);
            background: var(--ula-surface-card);
            transition: background 0.15s ease;
        }
        .data-table tr:hover td {
            background: var(--ula-surface-page-alt);
        }

        /* ── Tab Views ── */
        .tab-view { display: none; }
        .tab-view.active { display: block; animation: tabFadeIn 0.25s ease-out; }
        @keyframes tabFadeIn {
            from { opacity: 0; transform: translateY(6px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* ── 3D Soft Neumorphic Kanban Board Engine ── */
        .kanban-grid {
            display: grid;
            grid-template-columns: repeat(5, minmax(260px, 1fr));
            gap: 18px;
            align-items: start;
            overflow-x: auto;
            padding-bottom: 20px;
        }
        .kanban-column {
            background: var(--ula-surface-page-alt);
            border: 1px solid var(--ula-border-subtle);
            border-radius: var(--ula-radius-xl);
            padding: 16px;
            box-shadow: var(--ula-shadow-xs);
            min-height: 520px;
            display: flex;
            flex-direction: column;
            gap: 12px;
            transition: all 0.22s ease;
        }
        .kanban-column.drag-over {
            background: rgba(66, 119, 76, 0.12) !important;
            border: 2px dashed var(--ula-palm-900) !important;
            box-shadow: 0 0 18px rgba(66, 119, 76, 0.25);
            transform: scale(1.01);
        }
        .kanban-col-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 13px;
            font-weight: 900;
            padding-bottom: 10px;
            border-bottom: 2px solid var(--ula-border-subtle);
        }
        .kanban-cards-container {
            display: flex;
            flex-direction: column;
            gap: 12px;
            min-height: 120px;
        }
        .kanban-card {
            background: var(--ula-surface-card);
            border: 1px solid var(--ula-border-subtle);
            border-radius: var(--ula-radius-lg);
            padding: 14px;
            box-shadow: var(--ula-shadow-xs);
            cursor: grab;
            user-select: none;
            transition: all 0.22s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
        }
        .kanban-card:active {
            cursor: grabbing;
        }
        .kanban-card:hover {
            transform: translateY(-3px);
            box-shadow: var(--ula-shadow-md);
            border-color: var(--ula-palm-900);
        }
        .kanban-card.is-dragging {
            opacity: 0.35;
            transform: scale(0.96) rotate(1.5deg);
            border: 2px dashed var(--ula-palm-900);
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

        /* ── Modal & Popups ── */
        .modal, .modal-overlay {
            position: fixed;
            inset: 0;
            background: rgba(38, 53, 42, 0.45);
            backdrop-filter: blur(8px);
            display: none;
            align-items: center;
            justify-content: center;
            z-index: 9999;
            padding: 20px;
        }
        .modal-box, .modal-card {
            background: var(--ula-surface-card);
            border: 1px solid var(--ula-border-subtle);
            border-radius: var(--ula-radius-xl);
            padding: 30px;
            width: 100%;
            max-width: 520px;
            box-shadow: 0 20px 50px rgba(36, 92, 58, 0.2);
            color: var(--ula-text-primary);
            position: relative;
            animation: modalFadeIn 0.25s cubic-bezier(0.16, 1, 0.3, 1);
        }
        @keyframes modalFadeIn {
            from { transform: translateY(16px) scale(0.96); opacity: 0; }
            to { transform: translateY(0) scale(1); opacity: 1; }
        }
        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        .modal-title {
            font-size: 18px;
            font-weight: 900;
            color: var(--ula-text-primary);
        }
        .modal-close {
            background: var(--ula-surface-page-alt);
            border: 1px solid var(--ula-border-subtle);
            color: var(--ula-text-secondary);
            font-size: 16px;
            width: 32px;
            height: 32px;
            border-radius: 8px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.2s;
        }
        .modal-close:hover {
            background: var(--ula-terracotta-200);
            color: var(--ula-terracotta-600);
            border-color: var(--ula-terracotta-300);
        }

        /* ── Toast Notification System ── */
        #toast-container {
            position: fixed;
            bottom: 24px;
            inset-inline-end: 24px;
            z-index: 999999;
            display: flex;
            flex-direction: column;
            gap: 10px;
            pointer-events: none;
        }
        .toast-popup {
            pointer-events: auto;
            background: var(--ula-surface-card);
            color: var(--ula-text-primary);
            border: 1px solid var(--ula-palm-900);
            box-shadow: var(--ula-shadow-md);
            padding: 14px 20px;
            border-radius: var(--ula-radius-sm);
            font-size: 13px;
            font-weight: 800;
            display: flex;
            align-items: center;
            gap: 10px;
            animation: toastSlideUp 0.35s cubic-bezier(0.16, 1, 0.3, 1) forwards;
            transition: all 0.3s ease;
        }
        @keyframes toastSlideUp {
            from { opacity: 0; transform: translateY(24px) scale(0.94); }
            to { opacity: 1; transform: translateY(0) scale(1); }
        }
        .toast-popup.toast-fadeout { opacity: 0; transform: translateY(16px); }

        .btn-copied-pulse { animation: copyPulseAnim 0.6s ease; }
        @keyframes copyPulseAnim {
            0% { transform: scale(1); }
            50% { transform: scale(1.08); box-shadow: 0 0 16px rgba(79, 155, 95, 0.4); }
            100% { transform: scale(1); }
        }

        /* ── Notification Center Dropdown ── */
        .notification-dropdown-wrapper {
            position: relative;
            display: inline-block;
        }
        .notification-bell-btn {
            position: relative;
            cursor: pointer;
        }
        .notification-badge-pulse {
            position: absolute;
            top: -3px;
            inset-inline-end: -3px;
            background: var(--ula-status-danger);
            color: var(--ula-white);
            font-size: 10px;
            font-weight: 900;
            min-width: 18px;
            height: 18px;
            border-radius: 9999px;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 0 4px;
            border: 2px solid var(--ula-surface-card);
            box-shadow: 0 2px 6px rgba(239, 68, 68, 0.4);
            animation: bellBadgePulse 2s infinite;
        }
        @keyframes bellBadgePulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.12); }
        }
        .notification-dropdown-panel {
            position: absolute;
            top: calc(100% + 12px);
            inset-inline-end: 0;
            width: 380px;
            max-width: 90vw;
            background: var(--ula-surface-card);
            border: 1px solid var(--ula-border-subtle);
            border-radius: var(--ula-radius-xl);
            box-shadow: var(--ula-shadow-xs), 0 20px 40px rgba(0,0,0,0.25);
            z-index: 100000;
            display: none;
            flex-direction: column;
            overflow: hidden;
            animation: notifSlideDown 0.25s cubic-bezier(0.16, 1, 0.3, 1) forwards;
        }
        @keyframes notifSlideDown {
            from { opacity: 0; transform: translateY(-8px) scale(0.97); }
            to { opacity: 1; transform: translateY(0) scale(1); }
        }
        .notif-tab-btn {
            padding: 6px 12px;
            border-radius: 8px;
            font-size: 11px;
            font-weight: 800;
            border: 1px solid transparent;
            background: transparent;
            color: var(--ula-text-secondary);
            cursor: pointer;
            transition: all 0.2s;
        }
        .notif-tab-btn.active {
            background: var(--ula-surface-page-alt);
            color: var(--ula-text-primary);
            border-color: var(--ula-border-subtle);
        }
        .notif-item {
            padding: 12px 16px;
            border-bottom: 1px solid var(--ula-border-subtle);
            display: flex;
            gap: 12px;
            align-items: flex-start;
            cursor: pointer;
            transition: background 0.15s ease;
            text-decoration: none;
            color: inherit;
        }
        .notif-item:hover {
            background: var(--ula-surface-page-alt);
        }
        .notif-item.unread {
            background: rgba(79, 155, 95, 0.06);
        }
        .notif-item.unread:hover {
            background: rgba(79, 155, 95, 0.12);
        }
        .notif-icon-box {
            width: 36px;
            height: 36px;
            border-radius: 10px;
            background: var(--ula-surface-page-alt);
            border: 1px solid var(--ula-border-subtle);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 16px;
            flex-shrink: 0;
        }
        .notif-unread-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: var(--ula-palm-900);
            box-shadow: 0 0 6px var(--ula-palm-900);
            flex-shrink: 0;
            margin-top: 5px;
        }

        /* ── Live Timer Strip ── */
        .live-timer-strip {
            background: linear-gradient(135deg, var(--ula-tone-palm-bg), var(--ula-sand-100));
            border: 1px solid var(--ula-palm-900);
            border-radius: var(--ula-radius-lg);
            padding: 12px 20px;
            margin-bottom: 24px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 12px;
            box-shadow: var(--ula-shadow-xs);
        }
        .timer-pulse-dot {
            width: 10px;
            height: 10px;
            border-radius: 50%;
            background: var(--ula-status-success);
            box-shadow: 0 0 10px var(--ula-status-success);
            animation: pulseDot 1.5s infinite;
        }
        @keyframes pulseDot {
            0%, 100% { transform: scale(1); opacity: 1; }
            50% { transform: scale(1.4); opacity: 0.6; }
        }

        /* ── Focus Mode Bottom Banner ── */
        .focus-mode-banner {
            background: var(--ula-surface-card);
            border: 1px solid var(--ula-border-subtle);
            border-radius: var(--ula-radius-xl);
            padding: 14px 22px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 14px;
            margin-top: 24px;
            box-shadow: var(--ula-shadow-xs);
        }

        /* ── Responsive adjustments ── */
        .mobile-menu-btn {
            display: none;
            background: var(--ula-surface-page-alt);
            border: 1px solid var(--ula-border-subtle);
            font-size: 18px;
            padding: 6px 12px;
            border-radius: 8px;
            cursor: pointer;
            color: var(--ula-text-primary);
        }
        @media (max-width: 900px) {
            .sidebar { transform: translateX({{ app()->getLocale() === 'ar' ? '100%' : '-100%' }}); }
            .sidebar.open { transform: translateX(0) !important; }
            .main-content { margin-inline-start: 0; padding: 20px 16px; width: 100%; }
            .mobile-menu-btn { display: block; }
        }
    </style>
</head>
<body>

    <!-- Left Admin Sidebar -->
    @include('partials.app-sidebar', ['shellActive' => 'overview', 'shellMode' => 'tabs'])

    <!-- Main Content Area -->
    <main class="main-content">

        @if(session('superadmin_impersonator_id'))
        <div style="background: var(--ula-palm-950); border: 1px solid rgba(211, 165, 83, 0.5); border-radius: var(--ula-radius-lg); color: var(--ula-sand-100); padding: 12px 18px; margin-bottom: 20px; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 12px; box-shadow: var(--ula-shadow-sm);">
            <div style="display: flex; align-items: center; gap: 10px; font-size: 13px; font-weight: 600;">
                <span class="material-symbols-rounded" style="font-size: 20px; color: var(--ula-gold-400);">admin_panel_settings</span>
                <span>{{ __('You are currently logged in as company:') }} <strong style="color: var(--ula-gold-400); text-decoration: underline;">{{ session('superadmin_impersonated_org_name') }}</strong> ({{ Auth::user()->name }})</span>
            </div>
            <form method="POST" action="{{ route('impersonate.leave') }}" style="margin: 0; display: inline-flex;">
                @csrf
                <x-btn variant="nav-cta" size="sm" type="submit" icon="logout" pill="true">
                    {{ __('Return to Super Admin') }}
                </x-btn>
            </form>
        </div>
        @endif

        @if(session('org_impersonator_id'))
        <div style="background: var(--ula-palm-900); border: 1px solid rgba(78, 166, 111, 0.4); border-radius: var(--ula-radius-lg); color: var(--ula-sand-100); padding: 12px 18px; margin-bottom: 20px; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 12px; box-shadow: var(--ula-shadow-sm);">
            <div style="display: flex; align-items: center; gap: 10px; font-size: 13px; font-weight: 600;">
                <span class="material-symbols-rounded" style="font-size: 20px; color: var(--ula-palm-300);">switch_account</span>
                <span>{{ __('You are currently logged in as team member:') }} <strong style="color: var(--ula-sand-200); text-decoration: underline;">{{ Auth::user()->name }}</strong> ({{ Auth::user()->email }})</span>
            </div>
            <form method="POST" action="{{ route('organization.members.impersonate.leave') }}" style="margin: 0; display: inline-flex;">
                @csrf
                <x-btn variant="secondary" size="sm" type="submit" icon="logout" pill="true">
                    {{ __('Leave Impersonation') }}
                </x-btn>
            </form>
        </div>
        @endif

        @include('partials.app-topbar', ['shellMode' => 'tabs'])

        @if(session('success'))
        <div style="background: rgba(60, 107, 76, 0.12); border: 1px solid rgba(60, 107, 76, 0.3); color: var(--ula-status-success, var(--ula-palm-500)); border-radius: var(--ula-radius-md, 12px); padding: 12px 18px; margin-bottom: 20px; font-weight: 700; font-size: 13px; display: flex; align-items: center; justify-content: space-between; box-shadow: var(--ula-shadow-sm);">
            <div style="display: flex; align-items: center; gap: 8px;">
                <span class="material-symbols-rounded" style="font-size: 18px;">check_circle</span>
                <span>{{ session('success') }}</span>
            </div>
            <button onclick="this.parentElement.remove()" style="background: none; border: none; font-size: 16px; cursor: pointer; color: var(--ula-status-success, var(--ula-palm-500)); display: flex; align-items: center;">
                <span class="material-symbols-rounded" style="font-size: 18px;">close</span>
            </button>
        </div>
        @endif

        @if(session('error'))
        <div style="background: rgba(154, 88, 39, 0.12); border: 1px solid rgba(154, 88, 39, 0.3); color: var(--ula-status-danger, var(--ula-terracotta-500)); border-radius: var(--ula-radius-md, 12px); padding: 12px 18px; margin-bottom: 20px; font-weight: 700; font-size: 13px; display: flex; align-items: center; justify-content: space-between; box-shadow: var(--ula-shadow-sm);">
            <div style="display: flex; align-items: center; gap: 8px;">
                <span class="material-symbols-rounded" style="font-size: 18px;">warning</span>
                <span>{{ session('error') }}</span>
            </div>
            <button onclick="this.parentElement.remove()" style="background: none; border: none; font-size: 16px; cursor: pointer; color: var(--ula-status-danger, var(--ula-terracotta-500)); display: flex; align-items: center;">
                <span class="material-symbols-rounded" style="font-size: 18px;">close</span>
            </button>
        </div>
        @endif

        @if($errors->any())
        <div style="background: rgba(217, 107, 95, 0.15); border: 1px solid rgba(217, 107, 95, 0.35); color: var(--ula-status-danger); border-radius: 12px; padding: 12px 18px; margin-bottom: 20px; font-weight: 800; font-size: 13px; box-shadow: var(--ula-shadow-xs);">
            <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 6px;">
                <span class="material-symbols-rounded" style="font-size: 18px;">warning</span>
                <strong>{{ __('Please correct the following errors:') }}</strong>
            </div>
            <ul style="margin: 0; padding-inline-start: 20px; font-size: 12px; font-weight: 600;">
                @foreach($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        <!-- Universal Live Timer Banner Strip -->
        <div id="universal-timer-strip" class="live-timer-strip" style="{{ $activeTimer ? '' : 'display: none;' }}">
            <div style="display: flex; align-items: center; gap: 14px;">
                <div class="timer-pulse-dot"></div>
                <div>
                    <div style="display: flex; align-items: center; gap: 8px;">
                        <span style="font-size: 11px; font-weight: 800; text-transform: uppercase; color: var(--ula-text-primary); letter-spacing: 0.5px;">{{ __('Active Timer Running') }}</span>
                        <span id="timer-project-tag" class="badge badge-green" style="font-size: 10px;">{{ $activeTimer->project->name ?? 'Project' }}</span>
                    </div>
                    <div id="timer-task-title" style="font-size: 14px; font-weight: 800; color: var(--ula-text-primary);">
                        {{ $activeTimer->task->title ?? ($activeTimer->description ?? 'General Work Session') }}
                    </div>
                </div>
            </div>
            <div style="display: flex; align-items: center; gap: 16px;">
                <div id="live-timer-clock" style="font-size: 22px; font-weight: 900; font-family: monospace; color: var(--ula-text-primary); letter-spacing: 1px;">
                    00:00:00
                </div>
                <button onclick="stopGlobalTimer()" class="tactile-btn" style="background: var(--ula-terracotta-200); color: var(--ula-terracotta-600); border: 1px solid var(--ula-terracotta-300); padding: 7px 14px; font-size: 12px;">
                    <span class="material-symbols-rounded" style="font-size: 13px; vertical-align: text-bottom;">stop_circle</span> {{ __('Stop Timer') }}
                </button>
            </div>
        </div>

        <!-- 1. OVERVIEW TAB (3D Spatial + Soft Neumorphic) -->
                <!-- 1. OVERVIEW TAB -->
        @include('dashboard.partials.tab-overview')

        <!-- 2. CHAT TAB -->
        @include('dashboard.partials.tab-chat')

        <!-- 3. MEMBERS TAB -->
        @if($membership->hasPermission('members.view') || $membership->hasPermission('members.manage') || $membership->role?->slug === 'company_admin')
            @include('dashboard.partials.tab-members')
        @endif

        <!-- 4. BILLING TAB -->
        @include('dashboard.partials.tab-billing')

        <!-- 5. OFFICES & ROOMS TABS -->
        @if($membership->hasPermission('maps.manage') || $membership->role?->slug === 'company_admin')
            @include('dashboard.partials.tab-offices')
        @endif
        @include('dashboard.partials.tab-rooms')

        <!-- 6. SCHEDULED MEETINGS TAB -->
        @include('dashboard.partials.tab-meetings')

        <!-- 7. GUESTS & DEPARTMENTS TABS -->
        @include('dashboard.partials.tab-guests')
        @if($membership->hasPermission('departments.manage') || $membership->hasPermission('teams.manage'))
            @include('dashboard.partials.tab-departments')
        @endif

        <!-- 8. AUDIT & SETTINGS TABS -->
        @include('dashboard.partials.tab-audit')
        @if($membership->hasPermission('organizations.manage'))
            @include('dashboard.partials.tab-settings')
        @endif

        <!-- 9. PROFILE & PROJECTS TABS -->
        @include('dashboard.partials.tab-profile')
        @include('dashboard.partials.tab-projects')

        <!-- 10. TASKS & TIMESHEETS TABS -->
        @if($membership->hasPermission('tasks.assign') || $membership->hasPermission('tasks.delete') || $membership->role?->slug === 'company_admin')
            @include('dashboard.partials.tab-all-tasks')
        @endif
        @include('dashboard.partials.tab-my-tasks')
        @include('dashboard.partials.tab-timesheets')

        <!-- 11. WORKLOAD TAB -->
        @include('dashboard.partials.tab-workload')

    </main>

    <!-- MODALS & SCRIPTS -->
    @include('dashboard.partials.modals')
    @include('dashboard.partials.scripts')
</body>
</html>
