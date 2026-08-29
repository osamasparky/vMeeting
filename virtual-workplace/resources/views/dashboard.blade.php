<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $organization->name }} — Workspace Admin Dashboard</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;800;900&family=Inter:wght@300;400;500;600;700;800;900&family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/modern-design-system.css') }}">
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.2/Sortable.min.js" nonce="{{ $cspNonce ?? '' }}"></script>
    <style>
        :root {
            /* 🌿 Virtual Workplace — 3D Spatial + Soft Neumorphic Green Palette */
            --bg-primary: #F5F3E8;          /* Warm Ivory Background Canvas */
            --bg-secondary: #FFFDF6;        /* Creamy Elevated Sidebar / Navigation */
            --bg-surface: #FFFDF6;          /* Primary Surface */
            --bg-surface-subtle: #E8EFE2;   /* Soft Sage Secondary Inset Surface */
            --bg-card: #FFFDF6;             /* Card Surface */
            --bg-elevated: #E8EFE2;         /* Raised & Tag Containers */
            --bg-glass: rgba(255, 253, 246, 0.92);

            /* Core Green Identity */
            --brand-forest: #245C3A;        /* Primary Forest Green */
            --brand-workspace: #3F7D4F;     /* Mid Workspace Green */
            --brand-sage: #719B73;          /* Sage Accent */
            --brand-soft-sage: #BFD4B8;     /* Soft Sage Highlight */
            --brand-primary: #245C3A;
            --brand-secondary: #3F7D4F;
            --brand-teal: #245C3A;
            --brand-pine: #3F7D4F;
            --brand-ocean: #245C3A;
            --brand-navy: #26352A;
            --brand-green: #3F7D4F;
            --brand-lime: #719B73;
            --brand-gold: #D6A23A;
            --brand-orange: #D6A23A;
            --brand-coral: #D96B5F;
            --brand-crimson: #D96B5F;

            /* Gradients & Accents */
            --accent-primary: #245C3A;
            --accent-gradient: linear-gradient(180deg, #2D6C45 0%, #245C3A 100%);
            --accent-green: #3F7D4F;
            --accent-amber: #D6A23A;

            /* Typography Colors */
            --text-primary: #26352A;        /* Deep Forest Charcoal */
            --text-secondary: #66756A;      /* Calm Sage Slate */
            --text-muted: #8B9B8F;          /* Subtle Gray-Green */
            --text-dim: #9FAEA3;

            /* Borders */
            --border-color: #D5DED0;        /* Soft Organic Border */
            --border-panel: #D5DED0;
            --border-focus: #245C3A;

            /* Status */
            --status-success: #4F9B5F;
            --status-warning: #D6A23A;
            --status-danger: #D96B5F;
            --status-info: #6E9E9A;

            /* 3D Soft Neumorphic Shadows & Elevation (Deepened & Tactile) */
            --radius-sm: 10px;
            --radius-md: 14px;
            --radius-lg: 20px;
            --radius-xl: 24px;
            --radius-full: 9999px;

            --shadow-card: 0 14px 34px rgba(32, 64, 42, 0.08), 0 3px 8px rgba(32, 64, 42, 0.04), inset 0 1px 0 rgba(255, 255, 255, 0.95);
            --shadow-hover: 0 20px 44px rgba(32, 64, 42, 0.14), 0 6px 14px rgba(32, 64, 42, 0.06), inset 0 1px 0 rgba(255, 255, 255, 1);
            --shadow-soft-3d: 5px 5px 12px rgba(32, 64, 42, 0.07), -4px -4px 10px rgba(255, 255, 255, 0.95);
            --shadow-inset-3d: inset 2px 2px 6px rgba(32, 64, 42, 0.07), inset -2px -2px 6px rgba(255, 255, 255, 0.95);
            --shadow-tactile-btn: 0 6px 18px rgba(36, 92, 58, 0.32), inset 0 1.5px 1.5px rgba(255, 255, 255, 0.45);
            --shadow-kpi-icon: 0 8px 18px rgba(36, 92, 58, 0.32), inset 0 1.5px 1.5px rgba(255, 255, 255, 0.55), inset 0 -2px 4px rgba(0, 0, 0, 0.2);

            --font-family: 'Cairo', 'Inter', sans-serif;
        }

        [data-theme="light"] {
            --bg-primary: #F5F3E8;
            --bg-secondary: #FFFDF6;
            --bg-surface: #FFFDF6;
            --bg-surface-subtle: #E8EFE2;
            --bg-card: #FFFDF6;
            --bg-elevated: #E8EFE2;
            --border-color: #D5DED0;
            --text-primary: #26352A;
            --text-secondary: #66756A;
            --text-muted: #8B9B8F;
            --brand-forest: #245C3A;
            --brand-workspace: #3F7D4F;
            --brand-sage: #719B73;
        }

        /* 🌌 Dark Spatial Workspace Theme Tokens */
        [data-theme="dark"], html.dark, body.dark-mode {
            --bg-primary: #07100C;          /* Deep Green-Black Canvas */
            --bg-secondary: #0B1510;        /* Secondary Dark Surface / Sidebar */
            --bg-surface: #101C15;          /* Primary Surface Cards */
            --bg-surface-subtle: #15251B;   /* Dark Inset Surface & Badges */
            --bg-card: #101C15;             /* Card Surface */
            --bg-elevated: #15251B;         /* Raised Containers & Widgets */
            --bg-glass: rgba(16, 28, 21, 0.94);

            /* Core Green Accents */
            --brand-forest: #4F9B5F;        /* Primary Green */
            --brand-workspace: #3F7D4F;     /* Mid Workspace Green */
            --brand-sage: #7BC47F;          /* Bright Green Highlight */
            --brand-soft-sage: #719B73;     /* Soft Green */
            --brand-primary: #4F9B5F;
            --brand-secondary: #3F7D4F;
            --brand-teal: #4F9B5F;
            --brand-pine: #3F7D4F;
            --brand-ocean: #4F9B5F;
            --brand-navy: #F1F5EF;
            --brand-green: #4F9B5F;
            --brand-lime: #7BC47F;
            --brand-gold: #D6A23A;
            --brand-orange: #D6A23A;
            --brand-coral: #D96B5F;
            --brand-crimson: #D96B5F;

            /* Gradients & Accents */
            --accent-primary: #3F7D4F;
            --accent-gradient: linear-gradient(180deg, #4F9B5F 0%, #3F7D4F 100%);
            --accent-green: #4F9B5F;
            --accent-amber: #D6A23A;

            /* Typography Colors */
            --text-primary: #F1F5EF;        /* High-Contrast Off-White */
            --text-secondary: #9AA99D;      /* Calm Sage Slate */
            --text-muted: #718077;          /* Muted Gray-Green */
            --text-dim: #5C6A61;

            /* Borders */
            --border-color: #26382B;        /* Controlled Dark Border */
            --border-panel: #26382B;
            --border-focus: #4F9B5F;

            /* Status */
            --status-success: #5FAE68;
            --status-warning: #D6A23A;
            --status-danger: #D96B5F;
            --status-info: #6E9E9A;

            /* 3D Soft Neumorphic Shadows for Dark Mode */
            --shadow-card: 0 10px 30px rgba(0, 0, 0, 0.28), 0 2px 8px rgba(0, 0, 0, 0.2);
            --shadow-hover: 0 16px 36px rgba(0, 0, 0, 0.4), 0 4px 12px rgba(79, 155, 95, 0.08);
            --shadow-soft-3d: 3px 3px 8px rgba(0, 0, 0, 0.35), -2px -2px 6px rgba(255, 255, 255, 0.02);
            --shadow-inset-3d: inset 2px 2px 6px rgba(0, 0, 0, 0.35), inset -1px -1px 3px rgba(255, 255, 255, 0.02);
            --shadow-tactile-btn: 0 4px 14px rgba(0, 0, 0, 0.3), inset 0 1px 1px rgba(255, 255, 255, 0.15);
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

        * { margin: 0; padding: 0; box-sizing: border-box; font-family: var(--font-family); -webkit-font-smoothing: antialiased; }
        body {
            background: var(--bg-primary);
            color: var(--text-primary);
            min-height: 100vh;
            display: flex;
            overflow-x: hidden;
            transition: background-color 0.3s ease, color 0.3s ease;
        }

        /* ── Soft Raised Sidebar ── */
        .sidebar {
            width: 270px;
            background: var(--bg-surface);
            border-inline-end: 1px solid var(--border-color);
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
            background: var(--accent-gradient);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            color: #FFFDF6;
            box-shadow: var(--shadow-tactile-btn);
            flex-shrink: 0;
        }

        .sidebar-logo-text {
            font-size: 16px;
            font-weight: 900;
            color: var(--text-primary);
            letter-spacing: -0.4px;
            line-height: 1.2;
        }

        /* Sidebar Profile Card */
        .sidebar-profile-card {
            background: var(--bg-surface-subtle);
            border: 1px solid var(--border-color);
            border-radius: var(--radius-lg);
            padding: 14px;
            margin-bottom: 20px;
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
            position: relative;
            box-shadow: var(--shadow-soft-3d);
            cursor: pointer;
            transition: all 0.2s ease;
        }
        .sidebar-profile-card:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-hover);
            border-color: var(--brand-forest);
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

        /* ── Sidebar Accordions (3D Soft Neumorphic Pill Design) ── */
        .sidebar-accordion {
            margin-bottom: 10px;
        }

        .sidebar-accordion-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            font-size: 11px;
            font-weight: 800;
            color: var(--brand-forest);
            text-transform: uppercase;
            letter-spacing: 0.6px;
            padding: 10px 12px;
            border-radius: 12px;
            cursor: pointer;
            user-select: none;
            transition: all 0.22s cubic-bezier(0.4, 0, 0.2, 1);
            background: #EBF2E5;
            border: 1px solid #D5E1CE;
            box-shadow: 2px 2px 6px rgba(36, 92, 58, 0.04), -1px -1px 4px #FFFFFF;
            margin-bottom: 3px;
        }

        .sidebar-accordion-header:hover {
            color: var(--brand-forest);
            background: #E1ECDA;
            border-color: var(--brand-forest);
            transform: translateY(-1px);
            box-shadow: 3px 3px 8px rgba(36, 92, 58, 0.08), -2px -2px 6px #FFFFFF;
        }

        .sidebar-accordion-chevron {
            font-size: 9px;
            transition: transform 0.25s cubic-bezier(0.4, 0, 0.2, 1);
            color: var(--brand-forest);
            display: inline-block;
        }

        .sidebar-accordion.collapsed .sidebar-accordion-chevron {
            transform: rotate({{ app()->getLocale() === 'ar' ? '90deg' : '-90deg' }});
        }

        .sidebar-accordion-content {
            display: flex;
            flex-direction: column;
            gap: 3px;
            overflow: hidden;
            transition: max-height 0.3s cubic-bezier(0.4, 0, 0.2, 1), opacity 0.25s ease;
            max-height: 2000px;
            opacity: 1;
            padding: 4px 2px;
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
            gap: 10px;
            padding: 9px 12px;
            border-radius: 12px;
            color: #3A4E3E;
            background: transparent;
            border: 1px solid transparent;
            font-family: inherit;
            font-size: 13px;
            font-weight: 700;
            cursor: pointer;
            text-align: {{ app()->getLocale() === 'ar' ? 'right' : 'left' }};
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
            text-decoration: none;
            margin-bottom: 2px;
        }

        .nav-tab-btn:hover {
            background: #F1F6EC;
            color: var(--brand-forest);
            transform: translateX({{ app()->getLocale() === 'ar' ? '-3px' : '3px' }});
            border-color: #DDE8D6;
            box-shadow: 2px 2px 8px rgba(36, 92, 58, 0.04);
        }

        .nav-tab-btn.active {
            background: linear-gradient(135deg, #356F46 0%, #204E32 100%) !important;
            color: #FFFFFF !important;
            border: 1px solid #184128 !important;
            box-shadow: 0 8px 20px rgba(32, 78, 50, 0.32), inset 0 1px 1px rgba(255, 255, 255, 0.4) !important;
            font-weight: 800;
            transform: translateY(-1px);
        }
        .nav-tab-btn.active span {
            color: #FFFFFF !important;
        }

        .org-settings-tabs-nav .org-subtab-btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 9px 18px;
            border-radius: var(--radius-lg);
            font-size: 13px;
            font-weight: 800;
            color: var(--text-secondary);
            background: transparent;
            border: none;
            cursor: pointer;
            white-space: nowrap;
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .org-settings-tabs-nav .org-subtab-btn:hover {
            color: var(--text-primary);
            background: var(--bg-surface-subtle);
        }
        .org-settings-tabs-nav .org-subtab-btn.active {
            color: #ffffff;
            background: var(--brand-forest);
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
            border-radius: var(--radius-lg);
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
            color: var(--text-primary);
            letter-spacing: -0.5px;
        }
        .header-title-area p {
            font-size: 13px;
            color: var(--text-secondary);
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
            background: var(--bg-surface);
            border: 1px solid var(--border-color);
            border-radius: var(--radius-full);
            padding: 10px 18px;
            padding-inline-start: 40px;
            font-size: 13px;
            font-weight: 600;
            color: var(--text-primary);
            box-shadow: var(--shadow-inset-3d);
            outline: none;
            transition: all 0.2s ease;
        }
        .header-search-input:focus {
            border-color: var(--brand-forest);
            box-shadow: 0 0 0 3px rgba(36, 92, 58, 0.12), var(--shadow-inset-3d);
        }
        .header-search-icon {
            position: absolute;
            inset-inline-start: 14px;
            font-size: 15px;
            color: var(--text-muted);
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
            background: var(--bg-surface);
            border: 1px solid var(--border-color);
            box-shadow: var(--shadow-soft-3d);
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--text-primary);
            font-size: 16px;
            cursor: pointer;
            transition: all 0.2s ease;
            text-decoration: none;
        }
        .header-icon-btn:hover {
            transform: translateY(-2px);
            border-color: var(--brand-forest);
            box-shadow: var(--shadow-hover);
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
            border: 2px solid var(--bg-primary);
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
            background: var(--accent-gradient);
            color: #FFFDF6;
            border: 1px solid #1E4E31;
            box-shadow: var(--shadow-tactile-btn);
        }
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(36, 92, 58, 0.32);
            color: #FFFDF6;
        }
        .btn-primary:active {
            transform: translateY(1px);
            box-shadow: 0 2px 6px rgba(36, 92, 58, 0.2);
        }

        .btn-secondary {
            background: var(--bg-surface-subtle);
            color: var(--brand-forest);
            border: 1px solid var(--border-color);
            box-shadow: var(--shadow-soft-3d);
        }
        .btn-secondary:hover {
            transform: translateY(-2px);
            border-color: var(--brand-forest);
            box-shadow: var(--shadow-hover);
        }

        .btn-success {
            background: linear-gradient(180deg, #5CA96C 0%, #3F7D4F 100%);
            color: #FFFDF6;
            border: 1px solid #2B5737;
            box-shadow: 0 4px 14px rgba(63, 125, 79, 0.25);
        }

        .btn-outline {
            background: var(--bg-surface);
            border: 1px solid var(--border-color);
            color: var(--text-primary);
            box-shadow: var(--shadow-soft-3d);
        }

        /* ── Cards & Surfaces ── */
        .card {
            background: var(--bg-surface);
            border: 1px solid var(--border-color);
            border-radius: var(--radius-xl);
            padding: 22px 24px;
            box-shadow: var(--shadow-card);
            margin-bottom: 24px;
            color: var(--text-primary);
            position: relative;
            transition: all 0.2s ease;
        }
        .card:hover {
            box-shadow: var(--shadow-hover);
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
            color: var(--text-primary);
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
            background: var(--bg-surface);
            border: 1px solid var(--border-color);
            border-radius: var(--radius-xl);
            padding: 18px 20px;
            box-shadow: var(--shadow-card);
            display: flex;
            align-items: center;
            gap: 14px;
            transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: hidden;
        }
        .kpi-card:hover {
            transform: translateY(-3px);
            box-shadow: var(--shadow-hover);
            border-color: var(--brand-forest);
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
            color: var(--text-secondary);
            letter-spacing: -0.1px;
        }
        .kpi-value {
            font-size: 26px;
            font-weight: 900;
            color: var(--text-primary);
            line-height: 1.1;
            margin: 2px 0 4px 0;
        }
        .kpi-sub {
            font-size: 11px;
            font-weight: 700;
            color: var(--brand-forest);
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
            background: var(--bg-surface-subtle);
            color: var(--text-secondary);
            font-weight: 800;
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 0.6px;
            border-bottom: 1px solid var(--border-color);
        }
        .data-table th:first-child { border-start-start-radius: 12px; }
        .data-table th:last-child { border-start-end-radius: 12px; }
        .data-table td {
            padding: 14px 16px;
            border-bottom: 1px solid var(--border-color);
            color: var(--text-primary);
            background: var(--bg-surface);
            transition: background 0.15s ease;
        }
        .data-table tr:hover td {
            background: var(--bg-surface-subtle);
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
            background: var(--bg-surface-subtle);
            border: 1px solid var(--border-color);
            border-radius: var(--radius-xl);
            padding: 16px;
            box-shadow: var(--shadow-inset-3d);
            min-height: 520px;
            display: flex;
            flex-direction: column;
            gap: 12px;
            transition: all 0.22s ease;
        }
        .kanban-column.drag-over {
            background: rgba(66, 119, 76, 0.12) !important;
            border: 2px dashed var(--brand-forest) !important;
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
            border-bottom: 2px solid var(--border-color);
        }
        .kanban-cards-container {
            display: flex;
            flex-direction: column;
            gap: 12px;
            min-height: 120px;
        }
        .kanban-card {
            background: var(--bg-surface);
            border: 1px solid var(--border-color);
            border-radius: var(--radius-lg);
            padding: 14px;
            box-shadow: var(--shadow-card);
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
            box-shadow: var(--shadow-hover);
            border-color: var(--brand-forest);
        }
        .kanban-card.is-dragging {
            opacity: 0.35;
            transform: scale(0.96) rotate(1.5deg);
            border: 2px dashed var(--brand-forest);
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
            background: var(--bg-surface);
            border: 1px solid var(--border-color);
            border-radius: var(--radius-xl);
            padding: 30px;
            width: 100%;
            max-width: 520px;
            box-shadow: 0 20px 50px rgba(36, 92, 58, 0.2);
            color: var(--text-primary);
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
            color: var(--text-primary);
        }
        .modal-close {
            background: var(--bg-surface-subtle);
            border: 1px solid var(--border-color);
            color: var(--text-secondary);
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
            background: var(--bg-surface);
            color: var(--text-primary);
            border: 1px solid var(--brand-forest);
            box-shadow: var(--shadow-hover);
            padding: 14px 20px;
            border-radius: var(--radius-md);
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
            border: 2px solid var(--bg-surface);
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
            background: var(--bg-surface);
            border: 1px solid var(--border-color);
            border-radius: var(--radius-xl);
            box-shadow: var(--shadow-card), 0 20px 40px rgba(0,0,0,0.25);
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
            color: var(--text-secondary);
            cursor: pointer;
            transition: all 0.2s;
        }
        .notif-tab-btn.active {
            background: var(--bg-surface-subtle);
            color: var(--brand-forest);
            border-color: var(--border-color);
        }
        .notif-item {
            padding: 12px 16px;
            border-bottom: 1px solid var(--border-color);
            display: flex;
            gap: 12px;
            align-items: flex-start;
            cursor: pointer;
            transition: background 0.15s ease;
            text-decoration: none;
            color: inherit;
        }
        .notif-item:hover {
            background: var(--bg-surface-subtle);
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
            background: var(--bg-surface-subtle);
            border: 1px solid var(--border-color);
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
            background: var(--brand-forest);
            box-shadow: 0 0 6px var(--brand-forest);
            flex-shrink: 0;
            margin-top: 5px;
        }

        /* ── Live Timer Strip ── */
        .live-timer-strip {
            background: linear-gradient(135deg, #E8EFE2, #FFFDF6);
            border: 1px solid var(--brand-forest);
            border-radius: var(--radius-lg);
            padding: 12px 20px;
            margin-bottom: 24px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 12px;
            box-shadow: var(--shadow-card);
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
            background: var(--bg-surface);
            border: 1px solid var(--border-color);
            border-radius: var(--radius-xl);
            padding: 14px 22px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 14px;
            margin-top: 24px;
            box-shadow: var(--shadow-card);
        }

        /* ── Responsive adjustments ── */
        .mobile-menu-btn {
            display: none;
            background: var(--bg-surface-subtle);
            border: 1px solid var(--border-color);
            font-size: 18px;
            padding: 6px 12px;
            border-radius: 8px;
            cursor: pointer;
            color: var(--text-primary);
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
                    <img id="sidebar-tenant-logo" src="{{ $organization->logo_url }}" alt="{{ $organization->name }}" style="width: 38px; height: 38px; border-radius: 12px; object-fit: cover; box-shadow: var(--shadow-soft-3d); flex-shrink: 0;">
                @else
                    <div id="sidebar-tenant-logo-icon" class="sidebar-logo-icon">🏢</div>
                @endif
                <div>
                    <div class="sidebar-logo-text" style="overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">{{ $organization->name }}</div>
                    <div style="font-size: 10px; color: var(--brand-sage); font-weight: 700; text-transform: uppercase; letter-spacing: 0.6px;">{{ __('Virtual Workplace') }}</div>
                </div>
            </a>
            <button onclick="toggleSidebarCollapse()" class="sidebar-toggle-btn" style="width: 28px; height: 28px; font-size: 11px; padding: 0; flex-shrink: 0; background: var(--bg-surface-subtle); border: 1px solid var(--border-color); border-radius: 8px; cursor: pointer; color: var(--text-secondary);" title="{{ __('Toggle Sidebar (Mini / Full)') }}">
                {{ app()->getLocale() === 'ar' ? '◀' : '▶' }}
            </button>
        </div>

        <!-- 1. Workspace Section (Accordion) -->
        <div class="sidebar-accordion" id="sec-workspace">
            <div class="sidebar-accordion-header" onclick="toggleSidebarSection('sec-workspace')" data-tooltip="{{ __('Workspace') }}">
                <span style="display: flex; align-items: center; gap: 8px;">
                    <span class="nav-icon-tile">🏢</span>
                    <span>{{ __('Workspace') }}</span>
                </span>
                <span class="sidebar-accordion-chevron">▼</span>
            </div>
            <div class="sidebar-accordion-content">
                <button class="nav-tab-btn active" id="nav-btn-overview" onclick="switchAdminTab('overview')" data-tooltip="{{ __('Overview') }}">
                    <span style="display: flex; align-items: center; gap: 8px;">
                        <span class="nav-icon-tile">📊</span>
                        <span>{{ __('Overview') }}</span>
                    </span>
                </button>
                <a href="{{ route('office') }}" class="nav-tab-btn" style="text-decoration: none;" data-tooltip="{{ __('Virtual Office') }}">
                    <span style="display: flex; align-items: center; gap: 8px;">
                        <span class="nav-icon-tile">🚀</span>
                        <span>{{ __('Virtual Office') }}</span>
                    </span>
                    <span class="nav-badge-pill">3D</span>
                </a>
                <button class="nav-tab-btn" id="nav-btn-chat" onclick="switchAdminTab('chat')" data-tooltip="{{ __('Team Chat & DMs') }}">
                    <span style="display: flex; align-items: center; gap: 8px;">
                        <span class="nav-icon-tile">💬</span>
                        <span>{{ __('Team Chat & DMs') }}</span>
                    </span>
                    <span class="nav-badge-pill" style="background: rgba(79, 155, 95, 0.2); color: #4F9B5F;">Live</span>
                </button>
                @if($membership->hasPermission('maps.manage'))
                <a href="{{ route('editor') }}" class="nav-tab-btn" style="text-decoration: none;" data-tooltip="{{ __('Floor Map Editor') }}">
                    <span style="display: flex; align-items: center; gap: 8px;">
                        <span class="nav-icon-tile">🎨</span>
                        <span>{{ __('Floor Map Editor') }}</span>
                    </span>
                </a>
                @endif
            </div>
        </div>

        <!-- 2. Project Management Section (Accordion) -->
        <div class="sidebar-accordion" id="sec-projects">
            <div class="sidebar-accordion-header" onclick="toggleSidebarSection('sec-projects')" data-tooltip="{{ __('Project Management') }}">
                <span style="display: flex; align-items: center; gap: 8px;">
                    <span class="nav-icon-tile">📋</span>
                    <span>{{ __('Project Management') }}</span>
                </span>
                <span class="sidebar-accordion-chevron">▼</span>
            </div>
            <div class="sidebar-accordion-content">
                <button class="nav-tab-btn" id="nav-btn-projects" onclick="switchAdminTab('projects')" data-tooltip="{{ __('Projects Portfolio') }}">
                    <span style="display: flex; align-items: center; gap: 8px;">
                        <span class="nav-icon-tile">📁</span>
                        <span>{{ __('Projects Portfolio') }}</span>
                    </span>
                    <span class="nav-badge-pill">{{ $projects->count() }}</span>
                </button>
                @if($membership->hasPermission('tasks.assign') || $membership->hasPermission('tasks.delete') || $membership->role?->slug === 'company_admin')
                <button class="nav-tab-btn" id="nav-btn-all-tasks" onclick="switchAdminTab('all-tasks')" data-tooltip="{{ __('All Tasks Manager') }}">
                    <span style="display: flex; align-items: center; gap: 8px;">
                        <span class="nav-icon-tile">📑</span>
                        <span>{{ __('All Tasks Manager') }}</span>
                    </span>
                    <span class="nav-badge-pill">{{ $tasks->count() }}</span>
                </button>
                @endif
                <button class="nav-tab-btn" id="nav-btn-my-tasks" onclick="switchAdminTab('my-tasks')" data-tooltip="{{ __('My Tasks') }}">
                    <span style="display: flex; align-items: center; gap: 8px;">
                        <span class="nav-icon-tile">✅</span>
                        <span>{{ __('My Tasks') }}</span>
                    </span>
                    <span class="nav-badge-pill">{{ $myTasks->where('status', '!=', 'done')->count() }}</span>
                </button>
                <button class="nav-tab-btn" id="nav-btn-timesheets" onclick="switchAdminTab('timesheets')" data-tooltip="{{ __('Timesheets & Time') }}">
                    <span style="display: flex; align-items: center; gap: 8px;">
                        <span class="nav-icon-tile">⏱️</span>
                        <span>{{ __('Timesheets & Time') }}</span>
                    </span>
                </button>
                @if($membership->hasPermission('reports.view') || $membership->role?->slug === 'company_admin')
                <button class="nav-tab-btn" id="nav-btn-workload" onclick="switchAdminTab('workload')" data-tooltip="{{ __('Team Workload') }}">
                    <span style="display: flex; align-items: center; gap: 8px;">
                        <span class="nav-icon-tile">👥</span>
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
        <div class="sidebar-accordion" id="sec-admin">
            <div class="sidebar-accordion-header" onclick="toggleSidebarSection('sec-admin')" data-tooltip="{{ __('Administration') }}">
                <span style="display: flex; align-items: center; gap: 8px;">
                    <span class="nav-icon-tile">🛡️</span>
                    <span>{{ __('Administration') }}</span>
                </span>
                <span class="sidebar-accordion-chevron">▼</span>
            </div>
            <div class="sidebar-accordion-content">
                @if($membership->hasPermission('members.view') || $membership->hasPermission('members.manage'))
                <button class="nav-tab-btn" id="nav-btn-members" onclick="switchAdminTab('members')" data-tooltip="{{ __('Team Members') }}">
                    <span style="display: flex; align-items: center; gap: 8px;">
                        <span class="nav-icon-tile">👥</span>
                        <span>{{ __('Team Members') }}</span>
                    </span>
                    <span class="nav-badge-pill">{{ $members->count() }}</span>
                </button>
                @endif
                @if($membership->hasPermission('maps.manage') || $membership->role?->slug === 'company_admin')
                <button class="nav-tab-btn" id="nav-btn-offices" onclick="switchAdminTab('offices')" data-tooltip="{{ __('Offices & Branches (الفروع والمكاتب)') }}">
                    <span style="display: flex; align-items: center; gap: 8px;">
                        <span class="nav-icon-tile">🏢</span>
                        <span>{{ __('Offices & Branches') }}</span>
                    </span>
                    <span class="nav-badge-pill" style="background: rgba(36, 92, 58, 0.2); color: var(--brand-forest);">{{ $offices->count() }}</span>
                </button>
                @endif
                @if($membership->hasPermission('rooms.manage'))
                <button class="nav-tab-btn" id="nav-btn-rooms" onclick="switchAdminTab('rooms')" data-tooltip="{{ __('Rooms & Doors') }}">
                    <span style="display: flex; align-items: center; gap: 8px;">
                        <span class="nav-icon-tile">🚪</span>
                        <span>{{ __('Rooms & Doors') }}</span>
                    </span>
                    <span class="nav-badge-pill">{{ $rooms->count() }}</span>
                </button>
                @endif
                <button class="nav-tab-btn" id="nav-btn-meetings" onclick="switchAdminTab('meetings')" data-tooltip="{{ __('Meetings & Schedule') }}">
                    <span style="display: flex; align-items: center; gap: 8px;">
                        <span class="nav-icon-tile">📅</span>
                        <span>{{ __('Meetings & Schedule') }}</span>
                    </span>
                    <span class="nav-badge-pill">{{ $upcomingMeetings->count() }}</span>
                </button>
                @if($membership->hasPermission('guests.invite'))
                <button class="nav-tab-btn" id="nav-btn-guests" onclick="switchAdminTab('guests')" data-tooltip="{{ __('Guest Links') }}">
                    <span style="display: flex; align-items: center; gap: 8px;">
                        <span class="nav-icon-tile">🔗</span>
                        <span>{{ __('Guest Links') }}</span>
                    </span>
                    <span class="nav-badge-pill">{{ $guestInvitations->count() }}</span>
                </button>
                @endif
                @if($membership->hasPermission('departments.manage') || $membership->hasPermission('teams.manage'))
                <button class="nav-tab-btn" id="nav-btn-departments" onclick="switchAdminTab('departments')" data-tooltip="{{ __('Departments & Teams') }}">
                    <span style="display: flex; align-items: center; gap: 8px;">
                        <span class="nav-icon-tile">🏛️</span>
                        <span>{{ __('Departments & Teams') }}</span>
                    </span>
                </button>
                @endif
                @if($membership->hasPermission('audit.view'))
                <button class="nav-tab-btn" id="nav-btn-audit" onclick="switchAdminTab('audit')" data-tooltip="{{ __('Audit Logs') }}">
                    <span style="display: flex; align-items: center; gap: 8px;">
                        <span class="nav-icon-tile">📋</span>
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
                    <span class="nav-icon-tile">📅</span>
                    <span>{{ __('Meetings & Schedule') }}</span>
                </span>
                <span class="nav-badge-pill">{{ $upcomingMeetings->count() }}</span>
            </button>
        </div>
        @endif

        <!-- 4. Settings & Profile Section (Accordion) -->
        <div class="sidebar-accordion" id="sec-settings">
            <div class="sidebar-accordion-header" onclick="toggleSidebarSection('sec-settings')" data-tooltip="{{ __('Settings & Profile') }}">
                <span style="display: flex; align-items: center; gap: 8px;">
                    <span class="nav-icon-tile">⚙️</span>
                    <span>{{ __('Settings & Profile') }}</span>
                </span>
                <span class="sidebar-accordion-chevron">▼</span>
            </div>
            <div class="sidebar-accordion-content">
                <button class="nav-tab-btn" id="nav-btn-profile" onclick="switchAdminTab('profile')" data-tooltip="{{ __('My User Profile') }}">
                    <span style="display: flex; align-items: center; gap: 8px;">
                        <span class="nav-icon-tile">👤</span>
                        <span>{{ __('My User Profile') }}</span>
                    </span>
                </button>
                @if($membership->hasPermission('billing.manage'))
                <button class="nav-tab-btn" id="nav-btn-billing" onclick="switchAdminTab('billing')" data-tooltip="{{ __('Billing & Subscription') }}">
                    <span style="display: flex; align-items: center; gap: 8px;">
                        <span class="nav-icon-tile">💎</span>
                        <span>{{ __('Billing & Subscription') }}</span>
                    </span>
                </button>
                @endif
                @if($membership->hasPermission('organizations.manage'))
                <button class="nav-tab-btn" id="nav-btn-settings" onclick="switchAdminTab('settings')" data-tooltip="{{ __('Workspace Settings') }}">
                    <span style="display: flex; align-items: center; gap: 8px;">
                        <span class="nav-icon-tile">⚙️</span>
                        <span>{{ __('Workspace Settings') }}</span>
                    </span>
                </button>
                @endif
            </div>
        </div>

        @if($user->isSuperAdmin())
        <div style="margin-top: 8px;">
            <a href="{{ route('superadmin.dashboard') }}" class="nav-tab-btn" data-tooltip="{{ __('Super Admin Portal') }}" style="background: rgba(36, 92, 58, 0.1); color: var(--brand-forest); border: 1px solid rgba(36, 92, 58, 0.25); text-decoration: none;">
                <span class="nav-icon-tile" style="background: transparent; border: none; box-shadow: none;">⚡</span>
                <strong>{{ __('Super Admin Portal') }}</strong>
            </a>
        </div>
        @endif

        <!-- Go Premium Card (Only for Free Plan) -->
        @php
            $isFreePlan = !$organization->plan || (float)$organization->plan->price == 0 || strtolower($organization->plan->slug ?? '') === 'free';
        @endphp
        @if($isFreePlan)
        <div class="go-premium-card" style="margin-top: 14px;">
            <div class="go-premium-crown">👑</div>
            <div style="font-size: 13px; font-weight: 900; color: #8A6414; margin-bottom: 2px;">{{ __('Go Premium') }}</div>
            <div style="font-size: 11px; color: #9A7B32; margin-bottom: 10px; line-height: 1.3;">{{ __('Unlock more features and awesome perks!') }}</div>
            <button onclick="switchAdminTab('billing')" class="tactile-btn" style="width: 100%; justify-content: center; background: linear-gradient(180deg, #D6A23A 0%, #B4831B 100%); color: #FFFDF6; border: 1px solid #996D12; font-size: 12px; padding: 8px 12px; box-shadow: 0 4px 10px rgba(180, 131, 27, 0.25);">
                {{ __('Upgrade Now') }}
            </button>
        </div>
        @endif

        <!-- Language & Utility Strip -->
        <div style="margin-top: 14px; padding-top: 10px; border-top: 1px solid var(--border-color); display: flex; gap: 8px; align-items: center;">
            @if(app()->getLocale() === 'ar')
                <a href="{{ route('lang.switch', 'en') }}" style="flex: 1; display: flex; align-items: center; justify-content: center; gap: 6px; padding: 7px; background: var(--bg-surface-subtle); border: 1px solid var(--border-color); border-radius: var(--radius-sm); color: var(--text-primary); text-decoration: none; font-size: 11px; font-weight: 800;">🌐 English</a>
            @else
                <a href="{{ route('lang.switch', 'ar') }}" style="flex: 1; display: flex; align-items: center; justify-content: center; gap: 6px; padding: 7px; background: var(--bg-surface-subtle); border: 1px solid var(--border-color); border-radius: var(--radius-sm); color: var(--text-primary); text-decoration: none; font-size: 11px; font-weight: 800;">🌐 العربية</a>
            @endif
            <button onclick="toggleThemeMode()" style="padding: 7px 10px; background: var(--bg-surface-subtle); border: 1px solid var(--border-color); border-radius: var(--radius-sm); color: var(--text-primary); cursor: pointer; font-size: 12px; font-weight: 800; display: flex; align-items: center; justify-content: center;" title="{{ __('Toggle Dark / Light Mode') }}">
                <span class="theme-toggle-icon-label">🌙</span>
            </button>
        </div>

        <!-- User Profile Card (Footer) -->
        <div class="sidebar-user" style="margin-top: 10px; cursor: pointer; transition: all 0.2s; display: flex; align-items: center; gap: 10px; padding: 10px; border-radius: var(--radius-md); background: var(--bg-surface-subtle); border: 1px solid var(--border-color);" onclick="switchAdminTab('profile')" title="{{ __('View and Edit Profile') }}">
            @if($user->avatar_url)
                <img id="sidebar-user-avatar" src="{{ $user->avatar_url }}" alt="{{ $user->name }}" style="width: 36px; height: 36px; border-radius: 50%; object-fit: cover; flex-shrink: 0; border: 1px solid var(--border-color);">
            @else
                <div class="sidebar-avatar" style="width: 36px; height: 36px; border-radius: 50%; background: var(--accent-gradient); color: #FFFDF6; display: flex; align-items: center; justify-content: center; font-size: 13px; font-weight: 900;">{{ strtoupper(substr($user->name, 0, 2)) }}</div>
            @endif
            <div style="flex: 1; min-width: 0;">
                <div style="font-size: 12px; font-weight: 800; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; color: var(--text-primary);">
                    {{ $user->name }}
                    @if($user->nickname)
                        <span style="font-size: 10px; color: var(--brand-forest); font-weight: 600;">({{ '@' . $user->nickname }})</span>
                    @endif
                </div>
                <div style="font-size: 10px; color: var(--text-muted);">{{ $membership->role->name ?? 'Company Admin' }}</div>
            </div>
            <form method="POST" action="{{ route('logout') }}" style="display:inline;" onclick="event.stopPropagation();">
                @csrf
                <button type="submit" style="background: none; border: none; color: #D96B5F; cursor: pointer; font-size: 15px;" title="{{ __('Logout') }}">🚪</button>
            </form>
        </div>
    </aside>

    <!-- Main Content Area -->
    <main class="main-content">

        @if(session('superadmin_impersonator_id'))
        <div style="background: linear-gradient(135deg, #1E3A8A 0%, #2563EB 100%); color: #ffffff; padding: 12px 22px; border-radius: var(--radius-xl); display: flex; align-items: center; justify-content: space-between; margin-bottom: 22px; box-shadow: 0 8px 24px rgba(37,99,235,0.28); border: 1px solid rgba(255,255,255,0.25); font-weight: 800; font-size: 13px; flex-wrap: wrap; gap: 12px;">
            <div style="display: flex; align-items: center; gap: 10px;">
                <span style="font-size: 22px;">⚡</span>
                <span>{{ __('You are currently logged in as company:') }} <strong style="text-decoration: underline;">{{ session('superadmin_impersonated_org_name') }}</strong> ({{ Auth::user()->name }})</span>
            </div>
            <form method="POST" action="{{ route('impersonate.leave') }}" style="margin: 0; display: inline-flex;">
                @csrf
                <button type="submit" class="tactile-btn" style="background: #ffffff; color: #1E3A8A; border: none; padding: 8px 20px; border-radius: 9999px; font-weight: 900; font-size: 12px; cursor: pointer; display: inline-flex; align-items: center; gap: 6px; box-shadow: 0 4px 12px rgba(0,0,0,0.18);">
                    <span>🛡️</span>
                    <span>{{ __('Return to Super Admin (الرجوع للوحة التحكم)') }}</span>
                </button>
            </form>
        </div>
        @endif

        @if(session('org_impersonator_id'))
        <div style="background: linear-gradient(135deg, #065F46 0%, #059669 100%); color: #ffffff; padding: 12px 22px; border-radius: var(--radius-xl); display: flex; align-items: center; justify-content: space-between; margin-bottom: 22px; box-shadow: 0 8px 24px rgba(5,150,105,0.28); border: 1px solid rgba(255,255,255,0.25); font-weight: 800; font-size: 13px; flex-wrap: wrap; gap: 12px;">
            <div style="display: flex; align-items: center; gap: 10px;">
                <span style="font-size: 22px;">👤</span>
                <span>{{ __('You are currently logged in as team member:') }} <strong style="text-decoration: underline;">{{ Auth::user()->name }}</strong> ({{ Auth::user()->email }})</span>
            </div>
            <form method="POST" action="{{ route('organization.members.impersonate.leave') }}" style="margin: 0; display: inline-flex;">
                @csrf
                <button type="submit" class="tactile-btn" style="background: #ffffff; color: #065F46; border: none; padding: 8px 20px; border-radius: 9999px; font-weight: 900; font-size: 12px; cursor: pointer; display: inline-flex; align-items: center; gap: 6px; box-shadow: 0 4px 12px rgba(0,0,0,0.18);">
                    <span>↩️</span>
                    <span>{{ __('Leave Impersonation (العودة لحساب المسؤول)') }}</span>
                </button>
            </form>
        </div>
        @endif

        <!-- Top App Bar Navigation Header -->
        <div class="top-app-header">
            <div style="display: flex; align-items: center; gap: 14px;">
                <button class="mobile-menu-btn" onclick="toggleDashboardSidebar()">☰</button>
                <div class="header-title-area">
                    <h1 id="page-primary-title">{{ __('Dashboard') }}</h1>
                    <p id="page-primary-subtitle">{{ __('Welcome to your virtual workspace') }}</p>
                </div>
            </div>

            <!-- Soft Elevated Search Bar -->
            <div class="header-search-bar">
                <span class="header-search-icon">🔍</span>
                <input type="text" class="header-search-input" placeholder="{{ __('Search people, rooms, files...') }}" id="globalSearchInput" onkeyup="handleGlobalSearch(this.value)">
            </div>

            <!-- Header Action Controls -->
            <div class="header-actions-group">
                <a href="javascript:void(0)" onclick="openInviteModal()" class="header-icon-btn" title="{{ __('Invite People') }}">
                    <span>👥</span>
                </a>
                <!-- Notification Center Bell & Live Dropdown -->
                <div class="notification-dropdown-wrapper" id="notifWrapper">
                    <a href="javascript:void(0)" onclick="toggleNotificationDropdown()" class="header-icon-btn notification-bell-btn" id="notifBellBtn" title="{{ __('Notifications') }}">
                        <span>🔔</span>
                        <span class="notification-badge-pulse" id="notifBadge" style="display: none;">0</span>
                    </a>

                    <!-- Dropdown Panel -->
                    <div class="notification-dropdown-panel" id="notifDropdown">
                        <!-- Dropdown Header -->
                        <div style="padding: 14px 18px; background: var(--bg-surface-subtle); border-bottom: 1px solid var(--border-color); display: flex; justify-content: space-between; align-items: center;">
                            <div style="display: flex; align-items: center; gap: 8px;">
                                <span style="font-size: 16px;">🔔</span>
                                <strong style="font-size: 13px; color: var(--text-primary);">{{ __('Notifications') }}</strong>
                                <span id="notifHeaderCount" class="badge-status badge-active" style="font-size: 10px; padding: 2px 8px; display: none;">0 new</span>
                            </div>
                            <div style="display: flex; gap: 8px; align-items: center;">
                                <button type="button" onclick="markAllNotificationsAsRead()" style="background: none; border: none; font-size: 11px; font-weight: 800; color: var(--brand-forest); cursor: pointer;" title="{{ __('Mark all as read') }}">
                                    ✓ {{ __('Mark read') }}
                                </button>
                                <button type="button" onclick="clearAllNotificationsFromServer()" style="background: none; border: none; font-size: 11px; font-weight: 800; color: var(--text-muted); cursor: pointer;" title="{{ __('Clear all') }}">
                                    🗑️
                                </button>
                            </div>
                        </div>

                        <!-- Filter Tabs -->
                        <div style="padding: 8px 12px; border-bottom: 1px solid var(--border-color); display: flex; gap: 6px; background: var(--bg-surface);">
                            <button type="button" class="notif-tab-btn active" onclick="filterNotifTab('all', this)">{{ __('All') }}</button>
                            <button type="button" class="notif-tab-btn" onclick="filterNotifTab('task', this)">📋 {{ __('Tasks') }}</button>
                            <button type="button" class="notif-tab-btn" onclick="filterNotifTab('meeting', this)">📅 {{ __('Meetings') }}</button>
                            <button type="button" class="notif-tab-btn" onclick="filterNotifTab('spatial', this)">🚪 {{ __('Office') }}</button>
                        </div>

                        <!-- Notifications Scrollable Feed -->
                        <div id="notifListContainer" style="max-height: 380px; overflow-y: auto; display: flex; flex-direction: column;">
                            <div id="notifEmptyState" style="padding: 36px 18px; text-align: center; color: var(--text-muted);">
                                <div style="font-size: 32px; margin-bottom: 8px;">🎉</div>
                                <strong style="display: block; font-size: 13px; color: var(--text-primary); margin-bottom: 4px;">{{ __('All caught up!') }}</strong>
                                <span style="font-size: 12px;">{{ __('No new notifications right now.') }}</span>
                            </div>
                        </div>
                    </div>
                </div>
                <button onclick="toggleThemeMode()" class="header-icon-btn" title="{{ __('Toggle Dark / Light Mode') }}">
                    <span class="theme-toggle-icon-label">🌙</span>
                </button>

                <!-- Language Switcher (Directly next to user profile) -->
                @if(app()->getLocale() === 'ar')
                    <a href="{{ route('lang.switch', 'en') }}" class="header-icon-btn" style="width: auto; padding: 0 12px; gap: 6px; text-decoration: none; font-size: 12px; font-weight: 800;" title="{{ __('Switch to English') }}">
                        <span>🌐</span>
                        <span>English</span>
                    </a>
                @else
                    <a href="{{ route('lang.switch', 'ar') }}" class="header-icon-btn" style="width: auto; padding: 0 12px; gap: 6px; text-decoration: none; font-size: 12px; font-weight: 800;" title="{{ __('التبديل إلى العربية') }}">
                        <span>🌐</span>
                        <span>العربية</span>
                    </a>
                @endif

                <!-- User Profile Capsule / Avatar -->
                <div onclick="switchAdminTab('profile')" style="cursor: pointer; display: flex; align-items: center; gap: 8px; padding: 4px 10px; background: var(--bg-surface); border: 1px solid var(--border-color); border-radius: var(--radius-full); box-shadow: var(--shadow-soft-3d); transition: all 0.2s ease;" title="{{ __('View and Edit Profile') }}" onmouseover="this.style.borderColor='var(--brand-forest)'" onmouseout="this.style.borderColor='var(--border-color)'">
                    @if($user->avatar_url)
                        <img src="{{ $user->avatar_url }}" alt="{{ $user->name }}" style="width: 30px; height: 30px; border-radius: 50%; object-fit: cover;">
                    @else
                        <div style="width: 30px; height: 30px; border-radius: 50%; background: var(--accent-gradient); color: white; display: flex; align-items: center; justify-content: center; font-size: 11px; font-weight: 900;">
                            {{ strtoupper(substr($user->name, 0, 2)) }}
                        </div>
                    @endif
                    <span style="font-size: 12px; font-weight: 800; color: var(--text-primary); padding-inline-end: 4px;">{{ explode(' ', $user->name)[0] }}</span>
                </div>
            </div>
        </div>

        @if(session('success'))
        <div style="background: rgba(79, 155, 95, 0.15); border: 1px solid rgba(79, 155, 95, 0.35); color: #2E6B40; border-radius: 12px; padding: 12px 18px; margin-bottom: 20px; font-weight: 800; font-size: 13px; display: flex; align-items: center; justify-content: space-between; box-shadow: var(--shadow-soft-3d);">
            <div style="display: flex; align-items: center; gap: 8px;">
                <span>✅</span>
                <span>{{ session('success') }}</span>
            </div>
            <button onclick="this.parentElement.remove()" style="background: none; border: none; font-size: 16px; cursor: pointer; color: #2E6B40;">✕</button>
        </div>
        @endif

        @if(session('error'))
        <div style="background: rgba(217, 107, 95, 0.15); border: 1px solid rgba(217, 107, 95, 0.35); color: #D96B5F; border-radius: 12px; padding: 12px 18px; margin-bottom: 20px; font-weight: 800; font-size: 13px; display: flex; align-items: center; justify-content: space-between; box-shadow: var(--shadow-soft-3d);">
            <div style="display: flex; align-items: center; gap: 8px;">
                <span>⚠️</span>
                <span>{{ session('error') }}</span>
            </div>
            <button onclick="this.parentElement.remove()" style="background: none; border: none; font-size: 16px; cursor: pointer; color: #D96B5F;">✕</button>
        </div>
        @endif

        @if($errors->any())
        <div style="background: rgba(217, 107, 95, 0.15); border: 1px solid rgba(217, 107, 95, 0.35); color: #D96B5F; border-radius: 12px; padding: 12px 18px; margin-bottom: 20px; font-weight: 800; font-size: 13px; box-shadow: var(--shadow-soft-3d);">
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
                        <span style="font-size: 11px; font-weight: 800; text-transform: uppercase; color: var(--brand-forest); letter-spacing: 0.5px;">{{ __('Active Timer Running') }}</span>
                        <span id="timer-project-tag" class="badge badge-green" style="font-size: 10px;">{{ $activeTimer->project->name ?? 'Project' }}</span>
                    </div>
                    <div id="timer-task-title" style="font-size: 14px; font-weight: 800; color: var(--text-primary);">
                        {{ $activeTimer->task->title ?? ($activeTimer->description ?? 'General Work Session') }}
                    </div>
                </div>
            </div>
            <div style="display: flex; align-items: center; gap: 16px;">
                <div id="live-timer-clock" style="font-size: 22px; font-weight: 900; font-family: monospace; color: var(--brand-forest); letter-spacing: 1px;">
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
