<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $organization->name }} — Workspace Admin Dashboard</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Mono:wght@400;500;600;700&family=IBM+Plex+Sans+Arabic:wght@300;400;500;600;700;800&family=IBM+Plex+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;1,400&family=Material+Symbols+Rounded:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/ulaspace-tokens.css') }}">
    <link rel="stylesheet" href="{{ asset('css/ulaspace-dashboard.css') }}">
    <link rel="stylesheet" href="{{ asset('css/modern-design-system.css') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.2/Sortable.min.js" nonce="{{ $cspNonce ?? '' }}"></script>
    <style>
        /* ═══════════════════════════════════════════════════════════════
           ULASPACE DESIGN SYSTEM — WORKSPACE DASHBOARD
           ═══════════════════════════════════════════════════════════════ */
        :root {
            /* Light Theme (Warm Ivory & Forest Green Baseline) */
                        --bg-secondary: var(--ula-surface-card, #FFFFFF);
                                                            
            /* Core Green Identity */
                        --brand-workspace: var(--ula-palm-800, #1B3223);
                        --brand-soft-sage: #BFD4B8;
                        --brand-secondary: var(--ula-palm-800, #1B3223);
                        --brand-pine: var(--ula-palm-800, #1B3223);
            --brand-ocean: var(--ula-palm-900, #142B24);
                        --brand-green: var(--ula-palm-500, #3C6B4C);
            --brand-lime: #719B73;
                        --brand-orange: #b46c34;
            --brand-coral: #D96B5F;
            
            /* Gradients & Accents */
                        --ula-gradient-accent: linear-gradient(135deg, #142B24 0%, #1E412F 100%);
            --accent-green: var(--ula-palm-500, #3C6B4C);
            --accent-amber: var(--ula-gold-400, #D3A553);

            /* Typography Colors */
                                                
            /* Borders */
                                    --border-focus: var(--ula-palm-900, #142B24);

            /* Status */
                                                --status-info: var(--ula-palm-900, #142B24);

            /* Shadows & Elevation */
            --radius-xs: 6px;
                                                            
                                                                        --shadow-kpi-icon: var(--ula-shadow-sm);

                    }

        [dir="rtl"], [lang="ar"] {
            --font-family: var(--ula-font-ar);
        }

        [dir="ltr"], [lang="en"] {
            --font-family: var(--ula-font-en);
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

        /* 🌌 Dark Theme Tokens */
        [data-theme="dark"], html.dark, body.dark-mode {
                        --bg-secondary: var(--ula-palm-900, #142B24);
                                                            
            /* Core Green Accents */
                        --brand-workspace: var(--ula-palm-700, #1E412F);
                        --brand-soft-sage: #719B73;
                        --brand-secondary: var(--ula-palm-700, #1E412F);
                        --brand-pine: var(--ula-palm-700, #1E412F);
            --brand-ocean: var(--ula-palm-500, #8baa94);
                        --brand-green: var(--ula-palm-500, #8baa94);
            --brand-lime: #7BC47F;
                        --brand-orange: #e6c88b;
            --brand-coral: #D96B5F;
            
            /* Gradients & Accents */
                        --ula-gradient-accent: linear-gradient(135deg, #1E412F 0%, #3C6B4C 100%);
            --accent-green: var(--ula-palm-500, #8baa94);
            --accent-amber: #e6c88b;

            /* Typography Colors */
                                                
            /* Borders */
                                    --border-focus: var(--ula-palm-500, #8baa94);

            /* Status */
                                                --status-info: var(--ula-palm-500, #8baa94);

            /* Shadows */
                                                                    }

        /* Dark Mode specific component refinements */
        [data-theme="dark"] .sidebar-accordion-header {
            background: #15251B;
            border-color: #26382B;
            color: #F1F5EF;
            box-shadow: 2px 2px 6px rgba(0, 0, 0, 0.35);
        }
        [data-theme="dark"] .sidebar-accordion-header:hover {
            background: #1D3325;
            border-color: #4F9B5F;
            color: #7BC47F;
        }
        [data-theme="dark"] .nav-tab-btn {
            color: #9AA99D;
        }
        [data-theme="dark"] .nav-tab-btn:hover {
            background: #15251B;
            color: #F1F5EF;
            border-color: #26382B;
        }
        [data-theme="dark"] .nav-icon-tile {
            background: #101C15;
            border-color: #26382B;
            color: #7BC47F;
        }
        [data-theme="dark"] .nav-badge-pill {
            background: #15251B;
            color: #7BC47F;
            border-color: #26382B;
        }
        [data-theme="dark"] .go-premium-card {
            background: linear-gradient(135deg, #1C180E 0%, #14120B 100%);
            border-color: #3E3215;
        }
        [data-theme="dark"] .go-premium-card div {
            color: #E5C365 !important;
        }
        [data-theme="dark"] .hero-welcome-card {
            background: linear-gradient(135deg, #15251B 0%, #101C15 100%) !important;
            border-color: #26382B !important;
        }
        [data-theme="dark"] .card {
            background: #101C15;
            border-color: #26382B;
        }
        [data-theme="dark"] .kpi-card {
            background: #101C15;
            border-color: #26382B;
        }
        [data-theme="dark"] .data-table thead th {
            background: #15251B;
            border-color: #26382B;
            color: #9AA99D;
        }
        [data-theme="dark"] .data-table tbody tr {
            border-color: #26382B;
        }
        [data-theme="dark"] .data-table tbody tr:hover {
            background: #15251B;
        }
        [data-theme="dark"] .modal-card {
            background: #101C15;
            border-color: #26382B;
        }
        [data-theme="dark"] .modal-header {
            border-color: #26382B;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; -webkit-font-smoothing: antialiased; }
        body {
            background: var(--ula-accent-default);
            color: var(--ula-text-primary);
            min-height: 100vh;
            display: flex;
            overflow-x: hidden;
            transition: background-color 0.3s ease, color 0.3s ease;
        }

        /* ── Soft Raised Sidebar ── */
        .sidebar {
            width: 270px;
            background: var(--ula-surface-card);
            border-inline-end: 1px solid var(--ula-border-subtle);
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
            color: #FFFDF6;
            box-shadow: var(--ula-shadow-sm);
            flex-shrink: 0;
        }

        .sidebar-logo-text {
            font-size: 16px;
            font-weight: 900;
            color: var(--ula-text-primary);
            letter-spacing: -0.4px;
            line-height: 1.2;
        }

        /* Sidebar Profile Card */
        .sidebar-profile-card {
            background: var(--ula-surface-page-alt);
            border: 1px solid var(--ula-border-subtle);
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
            border-color: var(--ula-palm-900);
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
            border: 2px solid #FFFDF6;
            box-shadow: 0 4px 12px rgba(36, 92, 58, 0.15);
        }
        .sidebar-profile-status {
            position: absolute;
            bottom: 2px;
            inset-inline-end: 2px;
            width: 13px;
            height: 13px;
            border-radius: 50%;
            background: #4F9B5F;
            border: 2px solid #FFFDF6;
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
            color: var(--ula-text-muted);
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
            color: var(--ula-text-primary);
            background: var(--ula-surface-page-alt);
        }

        .sidebar-accordion-chevron {
            font-size: 9px;
            transition: transform 0.25s cubic-bezier(0.4, 0, 0.2, 1);
            color: var(--ula-text-muted);
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
            color: var(--ula-text-secondary);
            background: transparent;
            border: 1px solid transparent;
            font-family: inherit;
            font-size: 13px;
            font-weight: 500;
            cursor: pointer;
            text-align: {{ app()->getLocale() === 'ar' ? 'right' : 'left' }};
            transition: all 0.15s ease;
            text-decoration: none;
            margin-bottom: 2px;
        }

        .nav-tab-btn:hover {
            background: var(--ula-surface-page-alt);
            color: var(--ula-text-primary);
        }

        .nav-tab-btn.active {
            background: var(--ula-palm-900) !important;
            color: var(--ula-accent-default) !important;
            border-color: var(--ula-palm-900) !important;
            font-weight: 600;
            box-shadow: var(--ula-shadow-xs) !important;
        }
        .nav-tab-btn.active span {
            color: var(--ula-accent-default) !important;
        }

        .org-settings-tabs-nav .org-subtab-btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 9px 18px;
            border-radius: var(--ula-radius-lg);
            font-size: 13px;
            font-weight: 800;
            color: var(--ula-text-secondary);
            background: transparent;
            border: none;
            cursor: pointer;
            white-space: nowrap;
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .org-settings-tabs-nav .org-subtab-btn:hover {
            color: var(--ula-text-primary);
            background: var(--ula-surface-page-alt);
        }
        .org-settings-tabs-nav .org-subtab-btn.active {
            color: #ffffff;
            background: var(--ula-palm-900);
            box-shadow: 0 4px 14px rgba(36, 92, 58, 0.32);
        }
        .org-subtab-pane {
            animation: orgSubTabFade 0.2s ease-out;
        }
        @keyframes orgSubTabFade {
            from { opacity: 0; transform: translateY(4px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .nav-icon-tile {
            width: 28px;
            height: 28px;
            border-radius: 9px;
            background: #FFFDF6;
            border: 1px solid #D5DED0;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 14px;
            color: #245C3A;
            box-shadow: 1px 1px 4px rgba(36, 92, 58, 0.08);
            flex-shrink: 0;
            transition: all 0.2s ease;
        }
        .nav-tab-btn.active .nav-icon-tile {
            background: rgba(255, 255, 255, 0.22);
            border-color: rgba(255, 255, 255, 0.45);
            color: #FFFFFF !important;
            box-shadow: inset 0 1px 1px rgba(255, 255, 255, 0.4);
        }

        .nav-badge-pill {
            background: #DCE7D4;
            color: #245C3A;
            font-size: 10px;
            font-weight: 800;
            padding: 2px 7px;
            border-radius: 8px;
            border: 1px solid #C8D8BE;
            box-shadow: inset 1px 1px 2px rgba(36, 92, 58, 0.08);
            transition: all 0.2s ease;
        }
        .nav-tab-btn.active .nav-badge-pill {
            background: rgba(255, 255, 255, 0.25);
            color: #FFFFFF !important;
            border-color: rgba(255, 255, 255, 0.45);
        }

        /* Go Premium Gradient Card */
        .go-premium-card {
            margin-top: auto;
            background: linear-gradient(145deg, #FFF6D8 0%, #FEF8E8 45%, #FFFDF6 100%);
            border: 1px solid #EADCB2;
            border-radius: var(--ula-radius-lg);
            padding: 16px 14px;
            text-align: center;
            box-shadow: 0 12px 28px rgba(180, 131, 27, 0.12), inset 0 1px 0 rgba(255, 255, 255, 0.95);
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
            background: #D96B5F;
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
            color: var(--ula-palm-900);
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
            background: linear-gradient(145deg, #437E51 0%, #225433 100%);
            border: 1px solid #1B4529;
            box-shadow: 0 8px 20px rgba(34, 84, 51, 0.35), inset 0 1.5px 1.5px rgba(255, 255, 255, 0.55), inset 0 -2px 4px rgba(0, 0, 0, 0.22);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 22px;
            flex-shrink: 0;
            color: #FFFFFF !important;
            text-shadow: 0 1px 2px rgba(0, 0, 0, 0.3);
            transition: transform 0.22s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .icon-box-3d.green {
            background: linear-gradient(145deg, #437E51 0%, #225433 100%);
            color: #FFFFFF !important;
            border-color: #1B4529;
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

        .kpi-card {
            background: var(--ula-surface-card);
            border: 1px solid var(--ula-border-subtle);
            border-radius: var(--ula-radius-xl);
            padding: 18px 20px;
            box-shadow: var(--ula-shadow-xs);
            display: flex;
            align-items: center;
            gap: 14px;
            transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: hidden;
        }
        .kpi-card:hover {
            transform: translateY(-3px);
            box-shadow: var(--ula-shadow-md);
            border-color: var(--ula-palm-900);
        }
        .kpi-card:hover .icon-box-3d {
            transform: scale(1.05);
        }
        .kpi-info {
            flex: 1;
            min-width: 0;
        }
        .kpi-title {
            font-size: 12px;
            font-weight: 700;
            color: var(--ula-text-secondary);
            letter-spacing: -0.1px;
        }
        .kpi-value {
            font-size: 26px;
            font-weight: 900;
            color: var(--ula-text-primary);
            line-height: 1.1;
            margin: 2px 0 4px 0;
        }
        .kpi-sub {
            font-size: 11px;
            font-weight: 700;
            color: var(--ula-palm-900);
            display: flex;
            align-items: center;
            gap: 4px;
        }

        /* ── Badges ── */
        .badge {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 8px;
            font-size: 11px;
            font-weight: 800;
            letter-spacing: 0.2px;
        }
        .badge-green, .badge-teal { background: #E8EFE2; color: #245C3A; border: 1px solid #D5DED0; }
        .badge-purple { background: #F3E8FF; color: #7E22CE; border: 1px solid #E9D5FF; }
        .badge-blue { background: #E8EFE2; color: #245C3A; border: 1px solid #D5DED0; }
        .badge-amber { background: #FEF3C7; color: #B45309; border: 1px solid #FDE68A; }
        .badge-crimson { background: #FEE2E2; color: #B91C1C; border: 1px solid #FECACA; }
        .badge-gray { background: #F1F5F9; color: #475569; border: 1px solid #E2E8F0; }

        /* ── Data Tables ── */
        .data-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            font-size: 13px;
            text-align: {{ app()->getLocale() === 'ar' ? 'right' : 'left' }};
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
            color: var(--ula-palm-900);
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
            color: var(--ula-palm-900);
            border-color: var(--ula-border-subtle);
            transform: translateX({{ app()->getLocale() === 'ar' ? '-2px' : '2px' }});
        }
        .ctx-item.danger:hover {
            background: rgba(217, 107, 95, 0.15);
            color: #D96B5F;
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
            background: #FEE2E2;
            color: #B91C1C;
            border-color: #FECACA;
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
            background: #EF4444;
            color: #FFFFFF;
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
            color: var(--ula-palm-900);
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
            background: linear-gradient(135deg, #E8EFE2, #FFFDF6);
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
            background: #4F9B5F;
            box-shadow: 0 0 10px #4F9B5F;
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
    <aside class="sidebar" id="dashboardSidebar" style="overflow-y: auto;">
        <!-- Brand Header -->
        <div class="sidebar-brand-wrapper" style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 20px; padding: 0 4px;">
            <a href="javascript:void(0)" onclick="switchAdminTab('overview')" class="sidebar-logo" style="margin-bottom: 0; flex: 1; min-width: 0;">
                @if($organization->logo_url)
                    <img id="sidebar-tenant-logo" src="{{ $organization->logo_url }}" alt="{{ $organization->name }}" style="width: 38px; height: 38px; border-radius: 12px; object-fit: cover; box-shadow: var(--ula-shadow-xs); flex-shrink: 0;">
                @else
                    <div id="sidebar-tenant-logo-icon" class="sidebar-logo-icon" style="background: var(--ula-palm-900, #142B24); width: 38px; height: 38px; border-radius: 12px; display: flex; align-items: center; justify-content: center; padding: 5px; flex-shrink: 0; box-shadow: var(--ula-shadow-sm);">
                        <img src="{{ asset('images/ulaspace-icon.png') }}" alt="UlaSpace" style="width: 24px; height: auto; object-fit: contain;">
                    </div>
                @endif
                <div>
                    <div class="sidebar-logo-text" style="overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">{{ $organization->name }}</div>
                    <div style="font-size: 10px; color: var(--ula-palm-500); font-weight: 700; text-transform: uppercase; letter-spacing: 0.6px;">{{ __('Virtual Workplace') }}</div>
                </div>
            </a>
            <button onclick="toggleSidebarCollapse()" class="sidebar-toggle-btn" style="width: 28px; height: 28px; font-size: 11px; padding: 0; flex-shrink: 0; background: var(--ula-surface-page-alt); border: 1px solid var(--ula-border-subtle); border-radius: 8px; cursor: pointer; color: var(--ula-text-secondary);" title="{{ __('Toggle Sidebar (Mini / Full)') }}">
                {{ app()->getLocale() === 'ar' ? '◀' : '▶' }}
            </button>
        </div>

        <!-- 1. Workspace Section (Accordion) -->
        <div class="sidebar-accordion" id="sec-workspace">
            <div class="sidebar-accordion-header" onclick="toggleSidebarSection('sec-workspace')" data-tooltip="{{ __('Workspace') }}">
                <span style="display: flex; align-items: center; gap: 8px;">
                    <span class="material-symbols-rounded text-[18px] text-[var(--ula-highlight-default)]">apartment</span>
                    <span>{{ __('Workspace') }}</span>
                </span>
                <span class="sidebar-accordion-chevron">▼</span>
            </div>
            <div class="sidebar-accordion-content">
                <button class="nav-tab-btn active" id="nav-btn-overview" onclick="switchAdminTab('overview')" data-tooltip="{{ __('Overview') }}">
                    <span style="display: flex; align-items: center; gap: 8px;">
                        <span class="material-symbols-rounded text-[18px]">dashboard</span>
                        <span>{{ __('Overview') }}</span>
                    </span>
                </button>
                <a href="{{ route('office') }}" class="nav-tab-btn" style="text-decoration: none;" data-tooltip="{{ __('Virtual Office') }}">
                    <span style="display: flex; align-items: center; gap: 8px;">
                        <span class="material-symbols-rounded text-[18px]">view_in_ar</span>
                        <span>{{ __('Virtual Office') }}</span>
                    </span>
                    <span class="nav-badge-pill">3D</span>
                </a>
                <button class="nav-tab-btn" id="nav-btn-chat" onclick="switchAdminTab('chat')" data-tooltip="{{ __('Team Chat & DMs') }}">
                    <span style="display: flex; align-items: center; gap: 8px;">
                        <span class="material-symbols-rounded text-[18px]">chat</span>
                        <span>{{ __('Team Chat & DMs') }}</span>
                    </span>
                    <span class="nav-badge-pill" style="background: rgba(79, 155, 95, 0.2); color: #4F9B5F;">Live</span>
                </button>
                @if($membership->hasPermission('maps.manage'))
                <a href="{{ route('editor') }}" class="nav-tab-btn" style="text-decoration: none;" data-tooltip="{{ __('Floor Map Editor') }}">
                    <span style="display: flex; align-items: center; gap: 8px;">
                        <span class="material-symbols-rounded text-[18px]">draw</span>
                        <span>{{ __('Floor Map Editor') }}</span>
                    </span>
                </a>
                @endif
            </div>
        </div>

        <!-- 2. Project Management Section (Accordion) -->
        <div class="sidebar-accordion collapsed" id="sec-projects">
            <div class="sidebar-accordion-header" onclick="toggleSidebarSection('sec-projects')" data-tooltip="{{ __('Project Management') }}">
                <span style="display: flex; align-items: center; gap: 8px;">
                    <span class="material-symbols-rounded text-[18px] text-[var(--ula-highlight-default)]">assignment</span>
                    <span>{{ __('Project Management') }}</span>
                </span>
                <span class="sidebar-accordion-chevron">▼</span>
            </div>
            <div class="sidebar-accordion-content">
                <button class="nav-tab-btn" id="nav-btn-projects" onclick="switchAdminTab('projects')" data-tooltip="{{ __('Projects Portfolio') }}">
                    <span style="display: flex; align-items: center; gap: 8px;">
                        <span class="material-symbols-rounded text-[18px]">folder</span>
                        <span>{{ __('Projects Portfolio') }}</span>
                    </span>
                    <span class="nav-badge-pill">{{ $projects->count() }}</span>
                </button>
                @if($membership->hasPermission('tasks.assign') || $membership->hasPermission('tasks.delete') || $membership->role?->slug === 'company_admin')
                <button class="nav-tab-btn" id="nav-btn-all-tasks" onclick="switchAdminTab('all-tasks')" data-tooltip="{{ __('All Tasks Manager') }}">
                    <span style="display: flex; align-items: center; gap: 8px;">
                        <span class="material-symbols-rounded text-[18px]">list_alt</span>
                        <span>{{ __('All Tasks Manager') }}</span>
                    </span>
                    <span class="nav-badge-pill">{{ $tasks->count() }}</span>
                </button>
                @endif
                <button class="nav-tab-btn" id="nav-btn-my-tasks" onclick="switchAdminTab('my-tasks')" data-tooltip="{{ __('My Tasks') }}">
                    <span style="display: flex; align-items: center; gap: 8px;">
                        <span class="material-symbols-rounded text-[18px]">task_alt</span>
                        <span>{{ __('My Tasks') }}</span>
                    </span>
                    <span class="nav-badge-pill">{{ $myTasks->where('status', '!=', 'done')->count() }}</span>
                </button>
                <button class="nav-tab-btn" id="nav-btn-timesheets" onclick="switchAdminTab('timesheets')" data-tooltip="{{ __('Timesheets & Time') }}">
                    <span style="display: flex; align-items: center; gap: 8px;">
                        <span class="material-symbols-rounded text-[18px]">timer</span>
                        <span>{{ __('Timesheets & Time') }}</span>
                    </span>
                </button>
                @if($membership->hasPermission('reports.view') || $membership->role?->slug === 'company_admin')
                <button class="nav-tab-btn" id="nav-btn-workload" onclick="switchAdminTab('workload')" data-tooltip="{{ __('Team Workload') }}">
                    <span style="display: flex; align-items: center; gap: 8px;">
                        <span class="material-symbols-rounded text-[18px]">badge</span>
                        <span>{{ __('Team Workload') }}</span>
                    </span>
                </button>
                @endif
            </div>
        </div>

        <!-- 3. Administration Section (Accordion) -->
        @php
            $canSeeAdminSec = $membership->hasPermission('members.view') || $membership->hasPermission('rooms.manage') || $membership->hasPermission('guests.invite') || $membership->hasPermission('departments.manage') || $membership->hasPermission('audit.view') || $membership->role?->slug === 'company_admin';
        @endphp
        @if($canSeeAdminSec)
        <div class="sidebar-accordion collapsed" id="sec-admin">
            <div class="sidebar-accordion-header" onclick="toggleSidebarSection('sec-admin')" data-tooltip="{{ __('Administration') }}">
                <span style="display: flex; align-items: center; gap: 8px;">
                    <span class="material-symbols-rounded text-[18px] text-[var(--ula-highlight-default)]">shield</span>
                    <span>{{ __('Administration') }}</span>
                </span>
                <span class="sidebar-accordion-chevron">▼</span>
            </div>
            <div class="sidebar-accordion-content">
                @if($membership->hasPermission('members.view') || $membership->hasPermission('members.manage'))
                <button class="nav-tab-btn" id="nav-btn-members" onclick="switchAdminTab('members')" data-tooltip="{{ __('Team Members') }}">
                    <span style="display: flex; align-items: center; gap: 8px;">
                        <span class="material-symbols-rounded text-[18px]">group</span>
                        <span>{{ __('Team Members') }}</span>
                    </span>
                    <span class="nav-badge-pill">{{ $members->count() }}</span>
                </button>
                @endif
                @if($membership->hasPermission('maps.manage') || $membership->role?->slug === 'company_admin')
                <button class="nav-tab-btn" id="nav-btn-offices" onclick="switchAdminTab('offices')" data-tooltip="{{ __('Offices & Branches') }}">
                    <span style="display: flex; align-items: center; gap: 8px;">
                        <span class="material-symbols-rounded text-[18px]">domain</span>
                        <span>{{ __('Offices & Branches') }}</span>
                    </span>
                    <span class="nav-badge-pill" style="background: rgba(36, 92, 58, 0.2); color: var(--ula-palm-900);">{{ $offices->count() }}</span>
                </button>
                @endif
                @if($membership->hasPermission('rooms.manage'))
                <button class="nav-tab-btn" id="nav-btn-rooms" onclick="switchAdminTab('rooms')" data-tooltip="{{ __('Rooms & Doors') }}">
                    <span style="display: flex; align-items: center; gap: 8px;">
                        <span class="material-symbols-rounded text-[18px]">meeting_room</span>
                        <span>{{ __('Rooms & Doors') }}</span>
                    </span>
                    <span class="nav-badge-pill">{{ $rooms->count() }}</span>
                </button>
                @endif
                <button class="nav-tab-btn" id="nav-btn-meetings" onclick="switchAdminTab('meetings')" data-tooltip="{{ __('Meetings & Schedule') }}">
                    <span style="display: flex; align-items: center; gap: 8px;">
                        <span class="material-symbols-rounded text-[18px]">calendar_month</span>
                        <span>{{ __('Meetings & Schedule') }}</span>
                    </span>
                    <span class="nav-badge-pill">{{ $upcomingMeetings->count() }}</span>
                </button>
                @if($membership->hasPermission('guests.invite'))
                <button class="nav-tab-btn" id="nav-btn-guests" onclick="switchAdminTab('guests')" data-tooltip="{{ __('Guest Links') }}">
                    <span style="display: flex; align-items: center; gap: 8px;">
                        <span class="material-symbols-rounded text-[18px]">link</span>
                        <span>{{ __('Guest Links') }}</span>
                    </span>
                    <span class="nav-badge-pill">{{ $guestInvitations->count() }}</span>
                </button>
                @endif
                @if($membership->hasPermission('departments.manage') || $membership->hasPermission('teams.manage'))
                <button class="nav-tab-btn" id="nav-btn-departments" onclick="switchAdminTab('departments')" data-tooltip="{{ __('Departments & Teams') }}">
                    <span style="display: flex; align-items: center; gap: 8px;">
                        <span class="material-symbols-rounded text-[18px]">corporate_fare</span>
                        <span>{{ __('Departments & Teams') }}</span>
                    </span>
                </button>
                @endif
                @if($membership->hasPermission('audit.view'))
                <button class="nav-tab-btn" id="nav-btn-audit" onclick="switchAdminTab('audit')" data-tooltip="{{ __('Audit Logs') }}">
                    <span style="display: flex; align-items: center; gap: 8px;">
                        <span class="material-symbols-rounded text-[18px]">history</span>
                        <span>{{ __('Audit Logs') }}</span>
                    </span>
                </button>
                @endif
            </div>
        </div>
        @else
        <!-- Standalone Meetings Button for Non-Admins -->
        <div style="padding: 0 10px; margin-bottom: 8px;">
            <button class="nav-tab-btn" id="nav-btn-meetings" onclick="switchAdminTab('meetings')" data-tooltip="{{ __('Meetings & Schedule') }}">
                <span style="display: flex; align-items: center; gap: 8px;">
                    <span class="material-symbols-rounded text-[18px]">calendar_month</span>
                    <span>{{ __('Meetings & Schedule') }}</span>
                </span>
                <span class="nav-badge-pill">{{ $upcomingMeetings->count() }}</span>
            </button>
        </div>
        @endif

        <!-- 4. Settings & Profile Section (Accordion) -->
        <div class="sidebar-accordion collapsed" id="sec-settings">
            <div class="sidebar-accordion-header" onclick="toggleSidebarSection('sec-settings')" data-tooltip="{{ __('Settings & Profile') }}">
                <span style="display: flex; align-items: center; gap: 8px;">
                    <span class="material-symbols-rounded text-[18px] text-[var(--ula-highlight-default)]">settings</span>
                    <span>{{ __('Settings & Profile') }}</span>
                </span>
                <span class="sidebar-accordion-chevron">▼</span>
            </div>
            <div class="sidebar-accordion-content">
                <button class="nav-tab-btn" id="nav-btn-profile" onclick="switchAdminTab('profile')" data-tooltip="{{ __('My User Profile') }}">
                    <span style="display: flex; align-items: center; gap: 8px;">
                        <span class="material-symbols-rounded text-[18px]">person</span>
                        <span>{{ __('My User Profile') }}</span>
                    </span>
                </button>
                @if($membership->hasPermission('billing.manage'))
                <button class="nav-tab-btn" id="nav-btn-billing" onclick="switchAdminTab('billing')" data-tooltip="{{ __('Billing & Subscription') }}">
                    <span style="display: flex; align-items: center; gap: 8px;">
                        <span class="material-symbols-rounded text-[18px]">credit_card</span>
                        <span>{{ __('Billing & Subscription') }}</span>
                    </span>
                </button>
                @endif
                @if($membership->hasPermission('organizations.manage'))
                <button class="nav-tab-btn" id="nav-btn-settings" onclick="switchAdminTab('settings')" data-tooltip="{{ __('Workspace Settings') }}">
                    <span style="display: flex; align-items: center; gap: 8px;">
                        <span class="material-symbols-rounded text-[18px]">tune</span>
                        <span>{{ __('Workspace Settings') }}</span>
                    </span>
                </button>
                @endif
            </div>
        </div>

        @if($user->isSuperAdmin())
        <div style="margin-top: 8px;">
            <a href="{{ route('superadmin.dashboard') }}" class="nav-tab-btn" data-tooltip="{{ __('Super Admin Portal') }}" style="background: rgba(36, 92, 58, 0.1); color: var(--ula-palm-900); border: 1px solid rgba(36, 92, 58, 0.25); text-decoration: none;">
                <span class="material-symbols-rounded text-[18px]">bolt</span>
                <strong>{{ __('Super Admin Portal') }}</strong>
            </a>
        </div>
        @endif

        <!-- User Profile Card (Footer) -->
        <div class="sidebar-user" style="margin-top: auto; cursor: pointer; transition: all 0.2s; display: flex; align-items: center; gap: 10px; padding: 10px; border-radius: var(--ula-radius-sm); background: var(--ula-surface-page-alt); border: 1px solid var(--ula-border-subtle);" onclick="switchAdminTab('profile')" title="{{ __('View and Edit Profile') }}">
            @if($user->avatar_url)
                <img id="sidebar-user-avatar" src="{{ $user->avatar_url }}" alt="{{ $user->name }}" style="width: 36px; height: 36px; border-radius: 50%; object-fit: cover; flex-shrink: 0; border: 1px solid var(--ula-border-subtle);">
            @else
                <div class="sidebar-avatar" style="width: 36px; height: 36px; border-radius: 50%; background: var(--ula-gradient-accent); color: #FFFDF6; display: flex; align-items: center; justify-content: center; font-size: 13px; font-weight: 900;">{{ strtoupper(substr($user->name, 0, 2)) }}</div>
            @endif
            <div style="flex: 1; min-width: 0;">
                <div style="font-size: 12px; font-weight: 700; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; color: var(--ula-text-primary);">
                    {{ $user->name }}
                    @if($user->nickname)
                        <span style="font-size: 10px; color: var(--ula-palm-900); font-weight: 600;">({{ '@' . $user->nickname }})</span>
                    @endif
                </div>
                <div style="font-size: 10px; color: var(--ula-text-muted);">{{ $membership->role->name ?? 'Company Admin' }}</div>
            </div>
            <form method="POST" action="{{ route('logout') }}" style="display:inline;" onclick="event.stopPropagation();">
                @csrf
                <button type="submit" style="background: none; border: none; color: var(--ula-text-muted); cursor: pointer; display: flex; align-items: center;" title="{{ __('Logout') }}">
                    <span class="material-symbols-rounded text-[18px]">logout</span>
                </button>
            </form>
        </div>
    </aside>

    <!-- Main Content Area -->
    <main class="main-content">

        @if(session('superadmin_impersonator_id'))
        <div style="background: var(--ula-palm-950, #0e1c17); border: 1px solid rgba(211, 165, 83, 0.5); border-radius: var(--ula-radius-lg, 14px); color: var(--ula-sand-100, #f9f6ef); padding: 12px 18px; margin-bottom: 20px; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 12px; box-shadow: var(--ula-shadow-sm);">
            <div style="display: flex; align-items: center; gap: 10px; font-size: 13px; font-weight: 600;">
                <span class="material-symbols-rounded" style="font-size: 20px; color: var(--ula-gold-400, #D3A553);">admin_panel_settings</span>
                <span>{{ __('You are currently logged in as company:') }} <strong style="color: var(--ula-gold-400, #D3A553); text-decoration: underline;">{{ session('superadmin_impersonated_org_name') }}</strong> ({{ Auth::user()->name }})</span>
            </div>
            <form method="POST" action="{{ route('impersonate.leave') }}" style="margin: 0; display: inline-flex;">
                @csrf
                <button type="submit" style="background: rgba(211, 165, 83, 0.15); color: var(--ula-sand-100, #f9f6ef); border: 1px solid var(--ula-gold-400, #D3A553); padding: 6px 14px; border-radius: var(--ula-radius-pill, 9999px); font-size: 12px; font-weight: 700; cursor: pointer; display: inline-flex; align-items: center; gap: 6px; transition: all 0.15s ease;">
                    <span class="material-symbols-rounded" style="font-size: 16px;">logout</span>
                    <span>{{ __('Return to Super Admin') }}</span>
                </button>
            </form>
        </div>
        @endif

        @if(session('org_impersonator_id'))
        <div style="background: var(--ula-palm-900, #142B24); border: 1px solid rgba(78, 166, 111, 0.4); border-radius: var(--ula-radius-lg, 14px); color: var(--ula-sand-100, #f9f6ef); padding: 12px 18px; margin-bottom: 20px; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 12px; box-shadow: var(--ula-shadow-sm);">
            <div style="display: flex; align-items: center; gap: 10px; font-size: 13px; font-weight: 600;">
                <span class="material-symbols-rounded" style="font-size: 20px; color: var(--ula-palm-500, #8baa94);">switch_account</span>
                <span>{{ __('You are currently logged in as team member:') }} <strong style="color: var(--ula-sand-200, #F4EDE1); text-decoration: underline;">{{ Auth::user()->name }}</strong> ({{ Auth::user()->email }})</span>
            </div>
            <form method="POST" action="{{ route('organization.members.impersonate.leave') }}" style="margin: 0; display: inline-flex;">
                @csrf
                <button type="submit" style="background: rgba(78, 166, 111, 0.15); color: var(--ula-sand-100, #f9f6ef); border: 1px solid var(--ula-palm-500, #8baa94); padding: 6px 14px; border-radius: var(--ula-radius-pill, 9999px); font-size: 12px; font-weight: 700; cursor: pointer; display: inline-flex; align-items: center; gap: 6px; transition: all 0.15s ease;">
                    <span class="material-symbols-rounded" style="font-size: 16px;">logout</span>
                    <span>{{ __('Leave Impersonation') }}</span>
                </button>
            </form>
        </div>
        @endif

        <!-- Top App Bar Navigation Header (Figma App Bar Component) -->
        <div class="flex items-center justify-between mb-6 gap-4 flex-wrap">
            <div class="flex items-center gap-3.5">
                <button class="mobile-menu-btn" onclick="toggleDashboardSidebar()">
                    <span class="material-symbols-rounded text-[22px]">menu</span>
                </button>
                <div class="flex flex-col">
                    <h1 id="page-primary-title" class="text-[22px] font-semibold text-[var(--ula-text-primary)] leading-tight font-['IBM_Plex_Sans_Arabic',sans-serif]">
                        {{ __('Dashboard') }}
                    </h1>
                    <p id="page-primary-subtitle" class="text-[12px] text-[var(--ula-text-muted)] font-normal mt-0.5">
                        {{ __('Welcome to your virtual workspace') }}
                    </p>
                </div>
            </div>

            <!-- Soft Search Bar -->
            <div class="flex-1 max-w-[380px] relative flex items-center">
                <span class="material-symbols-rounded absolute inset-inline-start-3.5 text-[18px] text-[var(--ula-text-muted)] pointer-events-none">search</span>
                <input type="text" class="w-full bg-[var(--ula-surface-card)] border border-[var(--ula-border-subtle)] rounded-full py-2.5 px-4 ps-10 text-[13px] font-normal text-[var(--ula-text-primary)] placeholder-[var(--ula-text-muted)] focus:border-[var(--ula-palm-900)] focus:outline-none transition-colors" placeholder="{{ __('Search people, rooms, files...') }}" id="globalSearchInput" onkeyup="handleGlobalSearch(this.value)">
            </div>

            <!-- Header Actions Group -->
            <div class="flex items-center gap-2.5">
                <x-icon-btn icon="group_add" onclick="openInviteModal()" title="{{ __('Invite People') }}" size="md" variant="subtle" />

                <!-- Notification Center Bell & Dropdown -->
                <div class="relative inline-block" id="notifWrapper">
                    <x-icon-btn icon="notifications" onclick="toggleNotificationDropdown()" id="notifBellBtn" title="{{ __('Notifications') }}" size="md" variant="subtle" />
                    <span class="notification-badge-pulse" id="notifBadge" style="display: none;">0</span>

                    <!-- Dropdown Panel -->
                    <div class="notification-dropdown-panel" id="notifDropdown">
                        <!-- Dropdown Header -->
                        <div style="padding: 14px 18px; background: var(--ula-surface-page-alt); border-bottom: 1px solid var(--ula-border-subtle); display: flex; justify-content: space-between; align-items: center;">
                            <div style="display: flex; align-items: center; gap: 8px;">
                                <span class="material-symbols-rounded text-[18px] text-[var(--ula-highlight-default)]">notifications</span>
                                <strong style="font-size: 13px; color: var(--ula-text-primary);">{{ __('Notifications') }}</strong>
                                <span id="notifHeaderCount" class="badge-status badge-active" style="font-size: 10px; padding: 2px 8px; display: none;">0 new</span>
                            </div>
                            <div style="display: flex; gap: 8px; align-items: center;">
                                <button type="button" onclick="markAllNotificationsAsRead()" style="background: none; border: none; font-size: 11px; font-weight: 700; color: var(--ula-palm-900); cursor: pointer;" title="{{ __('Mark all as read') }}">
                                    {{ __('Mark read') }}
                                </button>
                                <button type="button" onclick="clearAllNotificationsFromServer()" style="background: none; border: none; font-size: 11px; font-weight: 700; color: var(--ula-text-muted); cursor: pointer;" title="{{ __('Clear all') }}">
                                    <span class="material-symbols-rounded text-[16px]">delete_sweep</span>
                                </button>
                            </div>
                        </div>

                        <!-- Filter Tabs -->
                        <div style="padding: 8px 12px; border-bottom: 1px solid var(--ula-border-subtle); display: flex; gap: 6px; background: var(--ula-surface-card);">
                            <button type="button" class="notif-tab-btn active" onclick="filterNotifTab('all', this)">{{ __('All') }}</button>
                            <button type="button" class="notif-tab-btn" onclick="filterNotifTab('task', this)">{{ __('Tasks') }}</button>
                            <button type="button" class="notif-tab-btn" onclick="filterNotifTab('meeting', this)">{{ __('Meetings') }}</button>
                            <button type="button" class="notif-tab-btn" onclick="filterNotifTab('spatial', this)">{{ __('Office') }}</button>
                        </div>

                        <!-- Notifications Scrollable Feed -->
                        <div id="notifListContainer" style="max-height: 380px; overflow-y: auto; display: flex; flex-direction: column;">
                            <div id="notifEmptyState" style="padding: 36px 18px; text-align: center; color: var(--ula-text-muted);">
                                <span class="material-symbols-rounded text-[32px] text-[var(--ula-text-muted)] block mb-2">celebration</span>
                                <strong style="display: block; font-size: 13px; color: var(--ula-text-primary); margin-bottom: 4px;">{{ __('All caught up!') }}</strong>
                                <span style="font-size: 12px;">{{ __('No new notifications right now.') }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <x-icon-btn icon="dark_mode" onclick="toggleThemeMode()" title="{{ __('Toggle Dark / Light Mode') }}" size="md" variant="subtle" />

                <!-- Language Switcher -->
                @if(app()->getLocale() === 'ar')
                    <a href="{{ route('lang.switch', 'en') }}" class="inline-flex items-center gap-1 px-3 py-2 rounded-[var(--ula-radius-md)] bg-[var(--ula-surface-card)] border border-[var(--ula-border-subtle)] text-[12px] font-semibold text-[var(--ula-text-primary)] hover:border-[var(--ula-palm-900)] transition-colors" title="{{ __('Switch to English') }}">
                        <span class="material-symbols-rounded text-[16px] text-[var(--ula-highlight-default)]">language</span>
                        <span>EN</span>
                    </a>
                @else
                    <a href="{{ route('lang.switch', 'ar') }}" class="inline-flex items-center gap-1 px-3 py-2 rounded-[var(--ula-radius-md)] bg-[var(--ula-surface-card)] border border-[var(--ula-border-subtle)] text-[12px] font-semibold text-[var(--ula-text-primary)] hover:border-[var(--ula-palm-900)] transition-colors" title="{{ __('التبديل إلى العربية') }}">
                        <span class="material-symbols-rounded text-[16px] text-[var(--ula-highlight-default)]">language</span>
                        <span>عربي</span>
                    </a>
                @endif

                <!-- User Profile Capsule (App Bar spec) -->
                <div onclick="switchAdminTab('profile')" class="cursor-pointer flex items-center gap-2 py-1 px-2.5 rounded-full bg-[var(--ula-surface-card)] border border-[var(--ula-border-subtle)] shadow-[var(--ula-shadow-sm)] hover:border-[var(--ula-palm-900)] transition-all" title="{{ __('View Profile') }}">
                    <div class="w-7 h-7 rounded-full overflow-hidden bg-[var(--ula-sand-200)] flex items-center justify-center text-[var(--ula-palm-900)] font-bold text-[11px] shrink-0">
                        @if($user->avatar_url)
                            <img src="{{ $user->avatar_url }}" alt="{{ $user->name }}" class="w-full h-full object-cover">
                        @else
                            <span>{{ strtoupper(substr($user->name, 0, 2)) }}</span>
                        @endif
                    </div>
                    <span class="text-[12px] font-medium text-[var(--ula-text-primary)] pe-1.5 hidden sm:inline">
                        {{ explode(' ', $user->name)[0] }}
                    </span>
                </div>
            </div>
        </div>

        @if(session('success'))
        <div style="background: rgba(60, 107, 76, 0.12); border: 1px solid rgba(60, 107, 76, 0.3); color: var(--ula-status-success, #3C6B4C); border-radius: var(--ula-radius-md, 12px); padding: 12px 18px; margin-bottom: 20px; font-weight: 700; font-size: 13px; display: flex; align-items: center; justify-content: space-between; box-shadow: var(--ula-shadow-sm);">
            <div style="display: flex; align-items: center; gap: 8px;">
                <span class="material-symbols-rounded" style="font-size: 18px;">check_circle</span>
                <span>{{ session('success') }}</span>
            </div>
            <button onclick="this.parentElement.remove()" style="background: none; border: none; font-size: 16px; cursor: pointer; color: var(--ula-status-success, #3C6B4C); display: flex; align-items: center;">
                <span class="material-symbols-rounded" style="font-size: 18px;">close</span>
            </button>
        </div>
        @endif

        @if(session('error'))
        <div style="background: rgba(154, 88, 39, 0.12); border: 1px solid rgba(154, 88, 39, 0.3); color: var(--ula-status-danger, #9A5827); border-radius: var(--ula-radius-md, 12px); padding: 12px 18px; margin-bottom: 20px; font-weight: 700; font-size: 13px; display: flex; align-items: center; justify-content: space-between; box-shadow: var(--ula-shadow-sm);">
            <div style="display: flex; align-items: center; gap: 8px;">
                <span class="material-symbols-rounded" style="font-size: 18px;">warning</span>
                <span>{{ session('error') }}</span>
            </div>
            <button onclick="this.parentElement.remove()" style="background: none; border: none; font-size: 16px; cursor: pointer; color: var(--ula-status-danger, #9A5827); display: flex; align-items: center;">
                <span class="material-symbols-rounded" style="font-size: 18px;">close</span>
            </button>
        </div>
        @endif

        @if($errors->any())
        <div style="background: rgba(217, 107, 95, 0.15); border: 1px solid rgba(217, 107, 95, 0.35); color: #D96B5F; border-radius: 12px; padding: 12px 18px; margin-bottom: 20px; font-weight: 800; font-size: 13px; box-shadow: var(--ula-shadow-xs);">
            <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 6px;">
                <span>⚠️</span>
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
                        <span style="font-size: 11px; font-weight: 800; text-transform: uppercase; color: var(--ula-palm-900); letter-spacing: 0.5px;">{{ __('Active Timer Running') }}</span>
                        <span id="timer-project-tag" class="badge badge-green" style="font-size: 10px;">{{ $activeTimer->project->name ?? 'Project' }}</span>
                    </div>
                    <div id="timer-task-title" style="font-size: 14px; font-weight: 800; color: var(--ula-text-primary);">
                        {{ $activeTimer->task->title ?? ($activeTimer->description ?? 'General Work Session') }}
                    </div>
                </div>
            </div>
            <div style="display: flex; align-items: center; gap: 16px;">
                <div id="live-timer-clock" style="font-size: 22px; font-weight: 900; font-family: monospace; color: var(--ula-palm-900); letter-spacing: 1px;">
                    00:00:00
                </div>
                <button onclick="stopGlobalTimer()" class="tactile-btn" style="background: #FEE2E2; color: #B91C1C; border: 1px solid #FECACA; padding: 7px 14px; font-size: 12px;">
                    ⏹ {{ __('Stop Timer') }}
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
