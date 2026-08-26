<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $organization->name }} — Workspace Admin Dashboard</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;800;900&family=Inter:wght@300;400;500;600;700;800;900&family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
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
        <div id="tab-overview" class="tab-view active">

            <!-- Hero Section: Welcome & 3D Isometric Workspace Preview -->
            <div style="display: grid; grid-template-columns: 1.35fr 1fr; gap: 20px; margin-bottom: 24px;">
                <!-- Left: Welcome Banner Card -->
                <div class="card hero-welcome-card" style="background: linear-gradient(135deg, #DCEAD8 0%, #EDF5EA 40%, #FFFDF6 100%); border: 1px solid #C8D8BE; display: flex; align-items: center; justify-content: space-between; overflow: hidden; padding: 28px; box-shadow: 0 16px 36px rgba(32, 64, 42, 0.09), inset 0 1px 0 rgba(255, 255, 255, 0.95);">
                    <div style="max-width: 60%; z-index: 2;">
                        <div style="display: inline-flex; align-items: center; gap: 6px; background: rgba(36, 92, 58, 0.12); border: 1px solid rgba(36, 92, 58, 0.25); padding: 4px 12px; border-radius: var(--radius-full); font-size: 11px; font-weight: 800; color: var(--brand-forest); margin-bottom: 12px; box-shadow: 0 2px 4px rgba(36, 92, 58, 0.06);">
                            <span>🌿</span> {{ __('Ready to Collaborate') }}
                        </div>
                        <h2 style="font-size: 24px; font-weight: 900; color: var(--text-primary); line-height: 1.25; margin-bottom: 8px;">
                            {{ __('Good morning, :name!', ['name' => explode(' ', $user->name)[0]]) }}
                        </h2>
                        <p style="font-size: 13px; color: var(--text-secondary); line-height: 1.5; margin-bottom: 20px; font-weight: 600;">
                            {{ __('Your workspace is ready. Let\'s make today productive!') }}
                        </p>
                        <div style="display: flex; gap: 12px; align-items: center;">
                            <a href="{{ route('office') }}" class="tactile-btn btn-primary" style="font-size: 13px; padding: 11px 22px;">
                                <span>{{ __('Enter Workspace') }}</span>
                                <span>{{ app()->getLocale() === 'ar' ? '←' : '→' }}</span>
                            </a>
                        </div>
                    </div>
                    <div style="width: 160px; height: 160px; flex-shrink: 0; position: relative; display: flex; align-items: center; justify-content: center; cursor: pointer;" onclick="switchAdminTab('profile')" title="{{ __('View Profile') }}">
                        @if($user->avatar_url)
                            <img src="{{ $user->avatar_url }}" alt="{{ $user->name }}" style="width: 140px; height: 140px; object-fit: cover; border-radius: 50%; box-shadow: 0 12px 30px rgba(36, 92, 58, 0.22), inset 0 2px 4px rgba(255,255,255,0.8); border: 4px solid #FFFDF6; transition: transform 0.3s ease;" onmouseover="this.style.transform='scale(1.05)'" onmouseout="this.style.transform='scale(1)'">
                        @else
                            <div style="width: 140px; height: 140px; border-radius: 50%; background: var(--accent-gradient); color: #FFFDF6; display: flex; align-items: center; justify-content: center; font-size: 44px; font-weight: 900; box-shadow: 0 12px 30px rgba(36, 92, 58, 0.25); border: 4px solid #FFFDF6; transition: transform 0.3s ease;" onmouseover="this.style.transform='scale(1.05)'" onmouseout="this.style.transform='scale(1)'">
                                {{ strtoupper(substr($user->name, 0, 2)) }}
                            </div>
                        @endif
                        <div style="position: absolute; bottom: 14px; inset-inline-end: 18px; width: 22px; height: 22px; border-radius: 50%; background: #4F9B5F; border: 3px solid #FFFDF6; box-shadow: 0 0 10px rgba(79, 155, 95, 0.8);" title="{{ __('Online') }}"></div>
                    </div>
                </div>

                <!-- Right: "Your Workspace" 3D Isometric Preview Card -->
                <div class="card" style="padding: 20px; display: flex; flex-direction: column; justify-content: space-between; background: linear-gradient(145deg, #FFFDF6 0%, #F5F9F1 100%); border: 1px solid #D2E0CC; box-shadow: 0 16px 36px rgba(32, 64, 42, 0.08), inset 0 1px 0 rgba(255, 255, 255, 0.95); overflow: hidden;">
                    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 12px;">
                        <div>
                            <div style="font-size: 15px; font-weight: 900; color: var(--text-primary);">{{ __('Your Workspace') }}</div>
                            <div style="font-size: 11px; color: var(--brand-sage); font-weight: 700;">🟢 {{ $stats['members'] }} {{ __('Members Online') }} • {{ $rooms->count() }} {{ __('Rooms') }}</div>
                        </div>
                        @if($membership->hasPermission('maps.manage'))
                        <a href="{{ route('editor') }}" class="tactile-btn btn-secondary" style="padding: 6px 12px; font-size: 11px;">
                            <span>🛠️</span> {{ __('Customize Space') }}
                        </a>
                        @endif
                    </div>
                    <div style="width: 100%; height: 130px; border-radius: 14px; overflow: hidden; position: relative; box-shadow: var(--shadow-inset-3d); border: 1px solid var(--border-color);">
                        <img src="/images/isometric_office_preview.jpg" alt="3D Office Preview" style="width: 100%; height: 100%; object-fit: cover; transition: transform 0.4s ease;" onmouseover="this.style.transform='scale(1.05)'" onmouseout="this.style.transform='scale(1)'">
                        <div style="position: absolute; bottom: 8px; inset-inline-start: 8px; background: rgba(255, 253, 246, 0.92); backdrop-filter: blur(4px); padding: 4px 10px; border-radius: 8px; font-size: 10px; font-weight: 800; color: var(--brand-forest); border: 1px solid var(--border-color);">
                            📍 {{ $organization->name }} {{ __('Headquarters') }}
                        </div>
                    </div>
                </div>
            </div>

            <!-- 5 Top KPI Cards -->
            <div class="kpi-grid">
                <!-- 1. Active Now -->
                <div class="kpi-card">
                    <div class="icon-box-3d green">
                        👥
                    </div>
                    <div class="kpi-info">
                        <div class="kpi-title">{{ __('Active Now') }}</div>
                        <div class="kpi-value">{{ $stats['members'] }}</div>
                        <div class="kpi-sub">↑ {{ $stats['members'] }} {{ __('this week') }}</div>
                    </div>
                </div>

                <!-- 2. Rooms -->
                <div class="kpi-card">
                    <div class="icon-box-3d">
                        🏢
                    </div>
                    <div class="kpi-info">
                        <div class="kpi-title">{{ __('Rooms') }}</div>
                        <div class="kpi-value">{{ $rooms->count() }}</div>
                        <div class="kpi-sub">{{ $rooms->where('door_status', 'open')->count() }} {{ __('Open') }}</div>
                    </div>
                </div>

                <!-- 3. Meetings Today -->
                @php
                    $todayMeetingsCount = $upcomingMeetings->filter(fn($m) => $m->scheduled_at && $m->scheduled_at->isToday())->count();
                    $nextMeeting = $upcomingMeetings->filter(fn($m) => $m->scheduled_at && $m->scheduled_at->isAfter(now()))->first();
                @endphp
                <div class="kpi-card">
                    <div class="icon-box-3d">
                        📅
                    </div>
                    <div class="kpi-info">
                        <div class="kpi-title">{{ __('Meetings Today') }}</div>
                        <div class="kpi-value">{{ $todayMeetingsCount }}</div>
                        <div class="kpi-sub" style="color: var(--text-secondary);">
                            {{ $nextMeeting ? __('Next: :time', ['time' => $nextMeeting->scheduled_at->format('h:i A')]) : __('No more meetings today') }}
                        </div>
                    </div>
                </div>

                <!-- 4. Tasks -->
                @php
                    $isTaskManager = ($membership->role?->slug === 'company_admin' || $membership->hasPermission('tasks.assign') || $membership->hasPermission('tasks.delete'));
                    $relevantPendingTasks = $isTaskManager ? $tasks->where('status', '!=', 'done')->count() : $myTasks->where('status', '!=', 'done')->count();
                    $relevantDueToday = $isTaskManager ? $tasks->filter(fn($t) => $t->due_date && $t->due_date->isToday() && $t->status !== 'done')->count() : $myTasks->filter(fn($t) => $t->due_date && $t->due_date->isToday() && $t->status !== 'done')->count();
                @endphp
                <div class="kpi-card" onclick="switchAdminTab('{{ $isTaskManager ? 'all-tasks' : 'my-tasks' }}')" style="cursor: pointer;">
                    <div class="icon-box-3d">
                        📋
                    </div>
                    <div class="kpi-info">
                        <div class="kpi-title">{{ $isTaskManager ? __('Workspace Tasks') : __('My Tasks') }}</div>
                        <div class="kpi-value">{{ $relevantPendingTasks }}</div>
                        <div class="kpi-sub" style="color: {{ $relevantDueToday > 0 ? 'var(--status-warning)' : 'var(--text-muted)' }};">
                            {{ $relevantDueToday }} {{ __('due today') }}
                        </div>
                    </div>
                </div>

                <!-- 5. Unread Messages -->
                <div class="kpi-card">
                    <div class="icon-box-3d">
                        💬
                    </div>
                    <div class="kpi-info">
                        <div class="kpi-title">{{ __('Unread Messages') }}</div>
                        <div class="kpi-value">0</div>
                        <div class="kpi-sub" style="color: var(--text-secondary);">{{ __('All caught up') }}</div>
                    </div>
                </div>
            </div>

            <!-- Analytics Grid: Workspace Activity + Upcoming Meetings + Workspace Overview -->
            <div style="display: grid; grid-template-columns: 1.4fr 1.1fr 1fr; gap: 20px; margin-bottom: 24px;">
                <!-- 1. Workspace Activity Curve Chart -->
                <div class="card" style="margin-bottom: 0;">
                    <div class="card-header">
                        <div>
                            <h3 class="card-title">{{ __('Workspace Activity') }}</h3>
                            <div style="font-size: 11px; color: var(--text-muted);">{{ __('Weekly team engagement & presence') }}</div>
                        </div>
                        <select style="background: var(--bg-surface-subtle); border: 1px solid var(--border-color); border-radius: 8px; font-size: 11px; font-weight: 700; padding: 4px 10px; color: var(--text-secondary); outline: none;">
                            <option>{{ __('This Week') }}</option>
                            <option>{{ __('Last Week') }}</option>
                            <option>{{ __('This Month') }}</option>
                        </select>
                    </div>

                    <!-- Clean Responsive SVG Area Chart -->
                    <div style="position: relative; width: 100%; height: 170px; margin-top: 10px;">
                        <svg viewBox="0 0 400 150" style="width: 100%; height: 100%; overflow: visible;">
                            <defs>
                                <linearGradient id="activityGrad" x1="0%" y1="0%" x2="0%" y2="100%">
                                    <stop offset="0%" stop-color="#3F7D4F" stop-opacity="0.35"/>
                                    <stop offset="100%" stop-color="#3F7D4F" stop-opacity="0.0"/>
                                </linearGradient>
                            </defs>
                            <!-- Grid Lines -->
                            <line x1="0" y1="30" x2="400" y2="30" stroke="rgba(213, 222, 208, 0.4)" stroke-dasharray="4"/>
                            <line x1="0" y1="75" x2="400" y2="75" stroke="rgba(213, 222, 208, 0.4)" stroke-dasharray="4"/>
                            <line x1="0" y1="120" x2="400" y2="120" stroke="rgba(213, 222, 208, 0.4)" stroke-dasharray="4"/>

                            <!-- Area Curve -->
                            <path d="M 0,110 Q 60,40 120,60 T 240,40 T 320,80 T 400,25 L 400,140 L 0,140 Z" fill="url(#activityGrad)"/>
                            <!-- Stroke Curve -->
                            <path d="M 0,110 Q 60,40 120,60 T 240,40 T 320,80 T 400,25" fill="none" stroke="#245C3A" stroke-width="3.5" stroke-linecap="round"/>

                            <!-- Data Points -->
                            <circle cx="0" cy="110" r="4" fill="#245C3A" stroke="#FFFDF6" stroke-width="2"/>
                            <circle cx="66" cy="48" r="4" fill="#245C3A" stroke="#FFFDF6" stroke-width="2"/>
                            <circle cx="133" cy="62" r="4" fill="#245C3A" stroke="#FFFDF6" stroke-width="2"/>
                            <circle cx="200" cy="50" r="4" fill="#245C3A" stroke="#FFFDF6" stroke-width="2"/>
                            <circle cx="266" cy="45" r="4" fill="#245C3A" stroke="#FFFDF6" stroke-width="2"/>
                            <circle cx="333" cy="78" r="4" fill="#245C3A" stroke="#FFFDF6" stroke-width="2"/>
                            <circle cx="400" cy="25" r="5" fill="#4F9B5F" stroke="#FFFDF6" stroke-width="2"/>
                        </svg>
                        <div style="display: flex; justify-content: space-between; font-size: 10px; font-weight: 700; color: var(--text-muted); margin-top: 6px;">
                            <span>{{ __('Mon') }}</span><span>{{ __('Tue') }}</span><span>{{ __('Wed') }}</span><span>{{ __('Thu') }}</span><span>{{ __('Fri') }}</span><span>{{ __('Sat') }}</span><span>{{ __('Sun') }}</span>
                        </div>
                    </div>
                </div>

                <!-- 2. Upcoming Meetings (Dynamic Live Sessions & Schedule) -->
                <div class="card" style="margin-bottom: 0; display: flex; flex-direction: column; justify-content: space-between;">
                    <div>
                        <div class="card-header" style="margin-bottom: 14px;">
                            <div style="display: flex; align-items: center; gap: 8px;">
                                <h3 class="card-title">{{ __('Upcoming Meetings') }}</h3>
                                <span class="nav-badge-pill" style="background: rgba(79, 155, 95, 0.15); color: #4F9B5F;">{{ $upcomingMeetings->count() }}</span>
                            </div>
                            <button onclick="openScheduleMeetingModal()" class="tactile-btn btn-primary" style="padding: 4px 10px; font-size: 11px; text-decoration: none;">
                                + {{ __('Schedule') }}
                            </button>
                        </div>

                        <div style="display: flex; flex-direction: column; gap: 10px;">
                            @forelse($upcomingMeetings->take(4) as $m)
                                @php
                                    $isLive = $m->status === 'active';
                                    $schedTime = $m->scheduled_at ? $m->scheduled_at->format('h:i A') : __('Instant');
                                    $schedDate = $m->scheduled_at ? ($m->scheduled_at->isToday() ? __('Today') : ($m->scheduled_at->isTomorrow() ? __('Tomorrow') : $m->scheduled_at->format('M d'))) : '';
                                    $mParts = $m->participants->take(3);
                                    $moreParts = max(0, $m->participants->count() - 3);
                                @endphp
                                <div class="meeting-list-card" style="display: flex; align-items: center; justify-content: space-between; background: var(--bg-surface-subtle); padding: 10px 12px; border-radius: 14px; border: 1px solid {{ $isLive ? 'var(--brand-forest)' : 'var(--border-color)' }}; box-shadow: var(--shadow-soft-3d); transition: all 0.2s;" onmouseover="this.style.borderColor='var(--brand-forest)'" onmouseout="this.style.borderColor='{{ $isLive ? 'var(--brand-forest)' : 'var(--border-color)' }}'">
                                    <div style="display: flex; align-items: center; gap: 10px; min-width: 0;">
                                        <div style="width: 40px; height: 40px; border-radius: 12px; background: linear-gradient(145deg, #437E51 0%, #245A36 100%); border: 1px solid #1C482B; display: flex; align-items: center; justify-content: center; font-size: 16px; color: #FFFFFF !important; flex-shrink: 0; box-shadow: 0 4px 12px rgba(36, 92, 58, 0.32), inset 0 1px 1px rgba(255, 255, 255, 0.45); text-shadow: 0 1px 2px rgba(0,0,0,0.2);">
                                            {{ $m->project ? '📁' : '📅' }}
                                        </div>
                                        <div style="min-width: 0;">
                                            <div style="display: flex; align-items: center; gap: 6px; flex-wrap: wrap;">
                                                <div style="font-size: 13px; font-weight: 800; color: var(--text-primary); white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">{{ $m->title }}</div>
                                                @if($isLive)
                                                    <span class="nav-badge-pill" style="background: rgba(217, 107, 95, 0.2); color: #D96B5F; font-size: 9px; animation: pulse 1.5s infinite;">🔴 LIVE</span>
                                                @endif
                                            </div>
                                            <div style="font-size: 11px; color: var(--text-muted); display: flex; align-items: center; gap: 6px; margin-top: 2px;">
                                                <span>⏰ {{ $schedDate }} {{ $schedTime }}</span>
                                                <span>•</span>
                                                <span>🚪 {{ $m->room->name ?? __('Meeting Room') }}</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div style="display: flex; align-items: center; gap: 8px; flex-shrink: 0;">
                                        <div style="display: flex; align-items: center; margin-inline-end: 4px;">
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
                                        <a href="{{ route('office') }}" class="tactile-btn btn-secondary" style="padding: 5px 10px; font-size: 11px; text-decoration: none;" title="{{ __('Enter Meeting Room') }}">
                                            🚀
                                        </a>
                                    </div>
                                </div>
                            @empty
                                <div style="text-align: center; padding: 24px; color: var(--text-muted); font-size: 12px; background: var(--bg-surface-subtle); border-radius: 12px; border: 1px dashed var(--border-color);">
                                    <div style="font-size: 24px; margin-bottom: 6px;">📅</div>
                                    {{ __('No scheduled meetings upcoming.') }}
                                    <div style="margin-top: 8px;">
                                        <button onclick="openScheduleMeetingModal()" class="tactile-btn btn-primary" style="padding: 6px 14px; font-size: 11px;">
                                            + {{ __('Schedule First Meeting') }}
                                        </button>
                                    </div>
                                </div>
                            @endforelse
                        </div>
                    </div>
                    @if($upcomingMeetings->count() > 4)
                        <div style="text-align: center; margin-top: 10px;">
                            <a href="javascript:void(0)" onclick="switchAdminTab('meetings')" style="font-size: 11px; font-weight: 800; color: var(--brand-forest); text-decoration: none;">
                                {{ __('View all :count scheduled meetings →', ['count' => $upcomingMeetings->count()]) }}
                            </a>
                        </div>
                    @endif
                </div>

                <!-- 3. Workspace Overview Donut Chart -->
                <div class="card" style="margin-bottom: 0;">
                    <div class="card-header">
                        <h3 class="card-title">{{ __('Workspace Overview') }}</h3>
                    </div>
                    <div style="display: flex; flex-direction: column; align-items: center;">
                        <!-- SVG Donut -->
                        <div style="position: relative; width: 120px; height: 120px; margin-bottom: 12px;">
                            <svg viewBox="0 0 36 36" style="width: 100%; height: 100%; transform: rotate(-90deg);">
                                <circle cx="18" cy="18" r="14" fill="none" stroke="#E8EFE2" stroke-width="4.5"/>
                                <!-- Meetings 45% -->
                                <circle cx="18" cy="18" r="14" fill="none" stroke="#245C3A" stroke-width="4.5" stroke-dasharray="39.6 88" stroke-dashoffset="0"/>
                                <!-- Focus Time 25% -->
                                <circle cx="18" cy="18" r="14" fill="none" stroke="#3F7D4F" stroke-width="4.5" stroke-dasharray="22 88" stroke-dashoffset="-39.6"/>
                                <!-- Collaboration 15% -->
                                <circle cx="18" cy="18" r="14" fill="none" stroke="#719B73" stroke-width="4.5" stroke-dasharray="13.2 88" stroke-dashoffset="-61.6"/>
                                <!-- Learning 10% -->
                                <circle cx="18" cy="18" r="14" fill="none" stroke="#D6A23A" stroke-width="4.5" stroke-dasharray="8.8 88" stroke-dashoffset="-74.8"/>
                                <!-- Other 5% -->
                                <circle cx="18" cy="18" r="14" fill="none" stroke="#D96B5F" stroke-width="4.5" stroke-dasharray="4.4 88" stroke-dashoffset="-83.6"/>
                            </svg>
                            <div style="position: absolute; inset: 0; display: flex; flex-direction: column; align-items: center; justify-content: center;">
                                <span style="font-size: 16px; font-weight: 900; color: var(--text-primary);">100%</span>
                                <span style="font-size: 9px; font-weight: 700; color: var(--text-muted);">{{ __('Activity') }}</span>
                            </div>
                        </div>

                        <!-- Legend -->
                        <div style="width: 100%; display: grid; grid-template-columns: 1fr 1fr; gap: 6px; font-size: 11px; font-weight: 700;">
                            <div style="display: flex; align-items: center; gap: 6px;"><span style="width: 8px; height: 8px; border-radius: 50%; background: #245C3A;"></span> {{ __('Meetings') }} (45%)</div>
                            <div style="display: flex; align-items: center; gap: 6px;"><span style="width: 8px; height: 8px; border-radius: 50%; background: #3F7D4F;"></span> {{ __('Focus') }} (25%)</div>
                            <div style="display: flex; align-items: center; gap: 6px;"><span style="width: 8px; height: 8px; border-radius: 50%; background: #719B73;"></span> {{ __('Collaboration') }} (15%)</div>
                            <div style="display: flex; align-items: center; gap: 6px;"><span style="width: 8px; height: 8px; border-radius: 50%; background: #D6A23A;"></span> {{ __('Learning') }} (10%)</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Bottom Grid: Recently Accessed + My Tasks + Quick Actions -->
            <div style="display: grid; grid-template-columns: 1.1fr 1.3fr 1.2fr; gap: 20px;">
                <!-- 1. Recently Accessed Projects & Workspaces -->
                <div class="card" style="margin-bottom: 0;">
                    <div class="card-header">
                        <h3 class="card-title">{{ __('Recently Accessed') }}</h3>
                        <a href="javascript:void(0)" onclick="switchAdminTab('projects')" style="font-size: 11px; font-weight: 800; color: var(--brand-forest); text-decoration: none;">{{ __('View All') }}</a>
                    </div>
                    <div style="display: flex; flex-direction: column; gap: 10px;">
                        @forelse($projects->take(3) as $p)
                            @php
                                $canOpenHub = ($user->isSuperAdmin() || $membership->role?->slug === 'company_admin' || $membership->hasPermission('projects.manage') || $p->manager_id === $user->id || $p->owner_id === $user->id);
                            @endphp
                            @if($canOpenHub)
                                <a href="{{ route('projects.hub', $p->id) }}" style="display: flex; align-items: center; justify-content: space-between; background: var(--bg-surface-subtle); padding: 10px 12px; border-radius: 12px; border: 1px solid var(--border-color); text-decoration: none; transition: transform 0.2s;" onmouseover="this.style.transform='translateY(-2px)'" onmouseout="this.style.transform='translateY(0)'" title="{{ __('Click to open project dashboard & tasks') }}">
                                    <div style="display: flex; align-items: center; gap: 10px; min-width: 0;">
                                        <div style="width: 32px; height: 32px; border-radius: 8px; background: rgba(79, 155, 95, 0.15); color: var(--brand-forest); display: flex; align-items: center; justify-content: center; font-size: 14px; font-weight: 900; flex-shrink: 0;">📁</div>
                                        <div style="min-width: 0;">
                                            <div style="font-size: 12px; font-weight: 800; color: var(--text-primary); white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">{{ $p->name }}</div>
                                            <div style="font-size: 10px; color: var(--text-muted);">{{ $p->code ?? 'PRJ' }} • {{ $p->tasks_count ?? $p->tasks->count() }} {{ __('tasks') }}</div>
                                        </div>
                                    </div>
                                    <span class="nav-badge-pill" style="font-size: 10px; flex-shrink: 0;">{{ __($p->status) }}</span>
                                </a>
                            @else
                                <div style="display: flex; align-items: center; justify-content: space-between; background: var(--bg-surface-subtle); padding: 10px 12px; border-radius: 12px; border: 1px solid var(--border-color);">
                                    <div style="display: flex; align-items: center; gap: 10px; min-width: 0;">
                                        <div style="width: 32px; height: 32px; border-radius: 8px; background: rgba(79, 155, 95, 0.15); color: var(--brand-forest); display: flex; align-items: center; justify-content: center; font-size: 14px; font-weight: 900; flex-shrink: 0;">📁</div>
                                        <div style="min-width: 0;">
                                            <div style="font-size: 12px; font-weight: 800; color: var(--text-primary); white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">{{ $p->name }}</div>
                                            <div style="font-size: 10px; color: var(--text-muted);">{{ $p->code ?? 'PRJ' }} • {{ $p->tasks_count ?? $p->tasks->count() }} {{ __('tasks') }}</div>
                                        </div>
                                    </div>
                                    <span class="nav-badge-pill" style="font-size: 10px; flex-shrink: 0;">{{ __($p->status) }}</span>
                                </div>
                            @endif
                        @empty
                            <div style="text-align: center; padding: 24px; color: var(--text-muted); font-size: 12px; background: var(--bg-surface-subtle); border-radius: 12px; border: 1px dashed var(--border-color);">
                                <div style="font-size: 22px; margin-bottom: 4px;">📁</div>
                                {{ __('No projects created yet.') }}
                            </div>
                        @endforelse
                    </div>
                </div>

                <!-- 2. My Tasks Checklist (Real DB Data) -->
                <div class="card" style="margin-bottom: 0;">
                    <div class="card-header">
                        <h3 class="card-title">{{ __('My Tasks') }}</h3>
                        <a href="javascript:void(0)" onclick="switchAdminTab('my-tasks')" style="font-size: 11px; font-weight: 800; color: var(--brand-forest); text-decoration: none;">+ {{ __('New Task') }}</a>
                    </div>
                    <div style="display: flex; flex-direction: column; gap: 10px;">
                        @forelse($myTasks->take(4) as $t)
                            <div style="display: flex; align-items: center; justify-content: space-between; background: var(--bg-surface-subtle); padding: 10px 12px; border-radius: 12px; border: 1px solid var(--border-color);">
                                <div style="display: flex; align-items: center; gap: 10px; min-width: 0;">
                                    <input type="checkbox" {{ $t->status === 'done' ? 'checked' : '' }} onchange="toggleTaskDone('{{ $t->id }}', this.checked)" style="width: 16px; height: 16px; accent-color: var(--brand-forest); cursor: pointer;">
                                    <div style="min-width: 0;">
                                        <div style="font-size: 12px; font-weight: 800; color: var(--text-primary); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; {{ $t->status === 'done' ? 'text-decoration: line-through; opacity: 0.6;' : '' }}">
                                            {{ $t->title }}
                                        </div>
                                        <div style="font-size: 10px; color: var(--text-muted);">{{ $t->due_date ? $t->due_date->format('M d') : __('Today') }}</div>
                                    </div>
                                </div>
                                <span class="badge badge-green" style="font-size: 10px; flex-shrink: 0;">{{ $t->project->name ?? __('General') }}</span>
                            </div>
                        @empty
                            <div style="text-align: center; padding: 24px; color: var(--text-muted); font-size: 12px; background: var(--bg-surface-subtle); border-radius: 12px; border: 1px dashed var(--border-color);">
                                <div style="font-size: 22px; margin-bottom: 4px;">✅</div>
                                {{ __('No tasks assigned to you yet.') }}
                                <div style="margin-top: 8px;">
                                    <button onclick="switchAdminTab('my-tasks')" class="tactile-btn btn-primary" style="padding: 5px 12px; font-size: 11px;">
                                        + {{ __('Create Your First Task') }}
                                    </button>
                                </div>
                            </div>
                        @endforelse
                    </div>
                </div>

                <!-- 3. Quick Actions Grid -->
                <div class="card" style="margin-bottom: 0;">
                    <div class="card-header">
                        <h3 class="card-title">{{ __('Quick Actions') }}</h3>
                    </div>
                    <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 8px;">
                        @if($membership->hasPermission('rooms.manage'))
                        <div onclick="switchAdminTab('rooms')" style="background: var(--bg-surface-subtle); border: 1px solid var(--border-color); border-radius: 14px; padding: 12px 6px; text-align: center; cursor: pointer; transition: all 0.2s; box-shadow: var(--shadow-soft-3d);" onmouseover="this.style.transform='translateY(-2px)'" onmouseout="this.style.transform='translateY(0)'">
                            <div style="font-size: 20px; margin-bottom: 3px;">🏢</div>
                            <div style="font-size: 11px; font-weight: 800; color: var(--text-primary); line-height: 1.2;">{{ __('Create Room') }}</div>
                        </div>
                        @else
                        <div onclick="window.location.href='{{ route('office') }}'" style="background: var(--bg-surface-subtle); border: 1px solid var(--border-color); border-radius: 14px; padding: 12px 6px; text-align: center; cursor: pointer; transition: all 0.2s; box-shadow: var(--shadow-soft-3d);" onmouseover="this.style.transform='translateY(-2px)'" onmouseout="this.style.transform='translateY(0)'">
                            <div style="font-size: 20px; margin-bottom: 3px;">🚀</div>
                            <div style="font-size: 11px; font-weight: 800; color: var(--text-primary); line-height: 1.2;">{{ __('Enter Office') }}</div>
                        </div>
                        @endif

                        <div onclick="openScheduleMeetingModal()" style="background: var(--bg-surface-subtle); border: 1px solid var(--border-color); border-radius: 14px; padding: 12px 6px; text-align: center; cursor: pointer; transition: all 0.2s; box-shadow: var(--shadow-soft-3d);" onmouseover="this.style.transform='translateY(-2px)'" onmouseout="this.style.transform='translateY(0)'">
                            <div style="font-size: 20px; margin-bottom: 3px;">📅</div>
                            <div style="font-size: 11px; font-weight: 800; color: var(--text-primary); line-height: 1.2;">{{ __('Schedule') }}</div>
                        </div>

                        @if($membership->hasPermission('members.manage') || $membership->role?->slug === 'company_admin')
                        <div onclick="openInviteModal()" style="background: var(--bg-surface-subtle); border: 1px solid var(--border-color); border-radius: 14px; padding: 12px 6px; text-align: center; cursor: pointer; transition: all 0.2s; box-shadow: var(--shadow-soft-3d);" onmouseover="this.style.transform='translateY(-2px)'" onmouseout="this.style.transform='translateY(0)'">
                            <div style="font-size: 20px; margin-bottom: 3px;">👥</div>
                            <div style="font-size: 11px; font-weight: 800; color: var(--text-primary); line-height: 1.2;">{{ __('Invite People') }}</div>
                        </div>
                        @else
                        <div onclick="switchAdminTab('profile')" style="background: var(--bg-surface-subtle); border: 1px solid var(--border-color); border-radius: 14px; padding: 12px 6px; text-align: center; cursor: pointer; transition: all 0.2s; box-shadow: var(--shadow-soft-3d);" onmouseover="this.style.transform='translateY(-2px)'" onmouseout="this.style.transform='translateY(0)'">
                            <div style="font-size: 20px; margin-bottom: 3px;">👤</div>
                            <div style="font-size: 11px; font-weight: 800; color: var(--text-primary); line-height: 1.2;">{{ __('My Profile') }}</div>
                        </div>
                        @endif

                        <div onclick="switchAdminTab('projects')" style="background: var(--bg-surface-subtle); border: 1px solid var(--border-color); border-radius: 14px; padding: 12px 6px; text-align: center; cursor: pointer; transition: all 0.2s; box-shadow: var(--shadow-soft-3d);" onmouseover="this.style.transform='translateY(-2px)'" onmouseout="this.style.transform='translateY(0)'">
                            <div style="font-size: 20px; margin-bottom: 3px;">📎</div>
                            <div style="font-size: 11px; font-weight: 800; color: var(--text-primary); line-height: 1.2;">{{ __('Share File') }}</div>
                        </div>

                        <div onclick="switchAdminTab('my-tasks')" style="background: var(--bg-surface-subtle); border: 1px solid var(--border-color); border-radius: 14px; padding: 12px 6px; text-align: center; cursor: pointer; transition: all 0.2s; box-shadow: var(--shadow-soft-3d);" onmouseover="this.style.transform='translateY(-2px)'" onmouseout="this.style.transform='translateY(0)'">
                            <div style="font-size: 20px; margin-bottom: 3px;">⚡</div>
                            <div style="font-size: 11px; font-weight: 800; color: var(--text-primary); line-height: 1.2;">{{ __('My Tasks') }}</div>
                        </div>

                        <div onclick="toggleFocusMode()" id="quick-action-focus" style="background: linear-gradient(145deg, #42774C 0%, #2A5D37 100%); color: #FFFDF6; border: 1px solid #1E4E31; border-radius: 14px; padding: 12px 6px; text-align: center; cursor: pointer; transition: all 0.2s; box-shadow: var(--shadow-tactile-btn);" onmouseover="this.style.transform='translateY(-2px)'" onmouseout="this.style.transform='translateY(0)'">
                            <div style="font-size: 20px; margin-bottom: 3px;">🌿</div>
                            <div style="font-size: 11px; font-weight: 800; color: #FFFDF6; line-height: 1.2;">{{ __('Focus Mode') }}</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Bottom Floating Tip Banner -->
            <div class="focus-mode-banner">
                <div style="display: flex; align-items: center; gap: 12px;">
                    <div style="width: 36px; height: 36px; border-radius: 10px; background: var(--bg-surface-subtle); border: 1px solid var(--border-color); display: flex; align-items: center; justify-content: center; font-size: 18px;">
                        🌿
                    </div>
                    <div>
                        <div style="font-size: 13px; font-weight: 800; color: var(--text-primary);">
                            {{ __('Tip: Focus time helps you get more done.') }}
                        </div>
                        <div style="font-size: 11px; color: var(--text-secondary);">
                            {{ __('Block notifications, customize ambient workplace sounds, and boost daily productivity.') }}
                        </div>
                    </div>
                </div>
                <button onclick="toggleFocusMode()" class="tactile-btn btn-primary" style="font-size: 12px; padding: 8px 18px;">
                    {{ __('Enable Focus Mode →') }}
                </button>
            </div>
        </div>

        <!-- 1.5 TEAM CHAT & DMS TAB -->
        <div id="tab-chat" class="tab-view">
            <div class="page-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; flex-wrap: wrap; gap: 14px;">
                <div>
                    <h1 class="page-title" style="font-size: 22px; font-weight: 900; color: var(--text-primary); margin-bottom: 4px;">💬 {{ __('Team Chat & Direct Messages') }}</h1>
                    <p class="page-subtitle" style="font-size: 13px; color: var(--text-secondary);">{{ __('Realtime company communication, direct colleague messaging, and team collaboration channels.') }}</p>
                </div>
                <div style="display: flex; gap: 10px; align-items: center;">
                    <button onclick="loadChatConversations(true)" class="tactile-btn btn-secondary" style="padding: 8px 16px; font-size: 12px;" title="{{ __('Refresh Messages') }}">
                        🔄 {{ __('Refresh') }}
                    </button>
                </div>
            </div>

            <!-- Chat Split Container (3D Tactile Glass Layout) -->
            <div class="card" style="padding: 0; border-radius: var(--radius-xl); overflow: hidden; display: flex; height: 720px; border: 1px solid var(--border-color); box-shadow: var(--shadow-card); background: var(--bg-surface);">
                
                <!-- Left Pane: Channels & Colleagues Roster -->
                <div style="width: 320px; flex-shrink: 0; border-inline-end: 1px solid var(--border-color); background: var(--bg-surface-subtle); display: flex; flex-direction: column;">
                    
                    <!-- Search Bar -->
                    <div style="padding: 16px; border-bottom: 1px solid var(--border-color);">
                        <div style="position: relative;">
                            <input type="text" id="chat-search-input" onkeyup="filterChatRoster()" placeholder="{{ __('Search colleagues & channels...') }}" style="width: 100%; background: var(--bg-surface); border: 1px solid var(--border-color); border-radius: 10px; padding: 9px 12px 9px 34px; font-size: 12px; color: var(--text-primary); outline: none; box-shadow: var(--shadow-inset-3d);">
                            <span style="position: absolute; inset-inline-start: 10px; top: 50%; transform: translateY(-50%); font-size: 14px; color: var(--text-muted);">🔍</span>
                        </div>
                    </div>

                    <!-- Scrollable Roster Lists -->
                    <div style="flex: 1; overflow-y: auto; padding: 12px 8px; display: flex; flex-direction: column; gap: 16px;">
                        
                        <!-- Channels Section -->
                        <div>
                            <div style="font-size: 11px; font-weight: 800; color: var(--text-secondary); text-transform: uppercase; padding: 0 8px 6px 8px; display: flex; justify-content: space-between; align-items: center;">
                                <span>📢 {{ __('Company Channels') }}</span>
                            </div>
                            <div id="chat-channels-list" style="display: flex; flex-direction: column; gap: 4px;">
                                <div style="padding: 10px 12px; font-size: 11px; color: var(--text-muted); text-align: center;">
                                    {{ __('Loading channels...') }}
                                </div>
                            </div>
                        </div>

                        <!-- Direct Messages Section -->
                        <div>
                            <div style="font-size: 11px; font-weight: 800; color: var(--text-secondary); text-transform: uppercase; padding: 0 8px 6px 8px; display: flex; justify-content: space-between; align-items: center;">
                                <span>👥 {{ __('Direct Messages') }}</span>
                                <span class="nav-badge-pill" id="chat-roster-count" style="font-size: 9px;">0</span>
                            </div>
                            <div id="chat-members-list" style="display: flex; flex-direction: column; gap: 4px;">
                                <div style="padding: 10px 12px; font-size: 11px; color: var(--text-muted); text-align: center;">
                                    {{ __('Loading colleagues...') }}
                                </div>
                            </div>
                        </div>

                    </div>
                </div>

                <!-- Right Pane: Active Chat Conversation -->
                <div style="flex: 1; display: flex; flex-direction: column; background: var(--bg-surface);">
                    
                    <!-- Empty State (No Chat Selected) -->
                    <div id="chat-empty-state" style="flex: 1; display: flex; flex-direction: column; align-items: center; justify-content: center; padding: 40px; text-align: center;">
                        <div style="width: 80px; height: 80px; border-radius: 24px; background: rgba(79, 155, 95, 0.12); display: flex; align-items: center; justify-content: center; font-size: 36px; margin-bottom: 16px; border: 1px solid rgba(79, 155, 95, 0.3); box-shadow: var(--shadow-soft-3d);">
                            💬
                        </div>
                        <h3 style="font-size: 18px; font-weight: 900; color: var(--text-primary); margin-bottom: 6px;">{{ __('Welcome to Company Workplace Chat') }}</h3>
                        <p style="font-size: 13px; color: var(--text-secondary); max-width: 380px; margin-bottom: 20px;">
                            {{ __('Select a colleague from the list on the left to start a direct 1-on-1 conversation or join a company collaboration channel.') }}
                        </p>
                        <button onclick="selectFirstColleagueChat()" class="tactile-btn btn-primary" style="padding: 10px 20px; font-size: 13px;">
                            🚀 {{ __('Start First Conversation') }}
                        </button>
                    </div>

                    <!-- Active Conversation Container (Hidden by default until selected) -->
                    <div id="chat-active-state" style="display: none; flex: 1; flex-direction: column; height: 100%;">
                        
                        <!-- Chat Conversation Top Header -->
                        <div style="padding: 14px 20px; border-bottom: 1px solid var(--border-color); background: var(--bg-surface); display: flex; justify-content: space-between; align-items: center;">
                            <div style="display: flex; align-items: center; gap: 12px; min-width: 0;">
                                <div id="chat-active-avatar-box" style="position: relative; width: 42px; height: 42px; border-radius: 12px; background: var(--accent-gradient); display: flex; align-items: center; justify-content: center; font-weight: 900; font-size: 16px; color: white; flex-shrink: 0; box-shadow: var(--shadow-soft-3d);">
                                    <span id="chat-active-avatar-initials">AB</span>
                                    <div style="position: absolute; bottom: -2px; inset-inline-end: -2px; width: 12px; height: 12px; border-radius: 50%; background: #4F9B5F; border: 2px solid var(--bg-surface);" title="Online"></div>
                                </div>
                                <div style="min-width: 0;">
                                    <div style="display: flex; align-items: center; gap: 8px;">
                                        <h3 id="chat-active-title" style="font-size: 15px; font-weight: 900; color: var(--text-primary); margin: 0; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">Colleague Name</h3>
                                        <span id="chat-active-badge" class="nav-badge-pill" style="font-size: 10px;">Member</span>
                                    </div>
                                    <div id="chat-active-subtitle" style="font-size: 11px; color: var(--text-secondary); margin-top: 2px;">Senior Engineer • Active Now</div>
                                </div>
                            </div>
                            <div style="display: flex; gap: 8px; align-items: center;">
                                <button id="chat-view-profile-btn" onclick="viewActiveChatUserProfile()" class="tactile-btn btn-secondary" style="padding: 6px 14px; font-size: 11px; font-weight: 800;" title="{{ __('View Member Profile') }}">
                                    <span>👤</span> {{ __('Profile') }}
                                </button>
                                <a href="{{ route('office') }}" class="tactile-btn btn-primary" style="padding: 6px 14px; font-size: 11px; text-decoration: none;" title="{{ __('Meet in Virtual Office') }}">
                                    <span>🚀</span> {{ __('Meet in Office') }}
                                </a>
                            </div>
                        </div>

                        <!-- Chat Messages History Feed -->
                        <div id="chat-messages-container" style="flex: 1; overflow-y: auto; padding: 20px; display: flex; flex-direction: column; gap: 14px; background: var(--bg-surface-subtle);">
                            <div style="text-align: center; color: var(--text-muted); font-size: 12px; padding: 20px;">
                                {{ __('Loading message history...') }}
                            </div>
                        </div>

                        <!-- Chat Composer Bar -->
                        <div style="padding: 14px 20px; border-top: 1px solid var(--border-color); background: var(--bg-surface);">
                            <form onsubmit="handleSendChatMessage(event)" style="display: flex; gap: 10px; align-items: flex-end;">
                                <div style="flex: 1; position: relative; background: var(--bg-surface-subtle); border: 1px solid var(--border-color); border-radius: 14px; padding: 8px 12px; box-shadow: var(--shadow-inset-3d);">
                                    <textarea id="chat-message-input" rows="1" onkeydown="handleChatInputKeydown(event)" placeholder="{{ __('Type a message... (Press Enter to send, Shift+Enter for new line)') }}" style="width: 100%; background: transparent; border: none; outline: none; color: var(--text-primary); font-size: 13px; font-weight: 500; resize: none; max-height: 120px; font-family: inherit;"></textarea>
                                </div>
                                <button type="submit" id="chat-send-btn" class="tactile-btn btn-primary" style="padding: 11px 20px; font-size: 13px; flex-shrink: 0; border-radius: 12px;">
                                    <span>{{ __('Send') }}</span>
                                    <span>🚀</span>
                                </button>
                            </form>
                        </div>

                    </div>
                </div>

            </div>
        </div>

        <!-- 2. TEAM MEMBERS TAB -->
        @if($membership->hasPermission('members.view') || $membership->hasPermission('members.manage') || $membership->role?->slug === 'company_admin')
        <div id="tab-members" class="tab-view">
            <div class="page-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; flex-wrap: gap; gap: 14px;">
                <div>
                    <h1 class="page-title" style="font-size: 22px; font-weight: 900; color: var(--text-primary); margin-bottom: 4px;">👥 {{ __('Team Members & Roles') }}</h1>
                    <p class="page-subtitle" style="font-size: 13px; color: var(--text-secondary);">{{ __('Manage organization membership, departments, teams, and security roles.') }}</p>
                </div>
                @if($membership->hasPermission('members.manage') || $membership->role?->slug === 'company_admin')
                <button onclick="openInviteModal()" class="tactile-btn btn-primary" style="padding: 10px 18px; font-size: 13px;">
                    <span>+</span> {{ __('Invite Member') }}
                </button>
                @endif
            </div>

            <div class="card" style="border-radius: var(--radius-lg); overflow: hidden; padding: 0;">
                <div style="padding: 20px 24px; border-bottom: 1px solid var(--border-color); display: flex; justify-content: space-between; align-items: center; background: var(--bg-surface);">
                    <h3 style="font-size: 16px; font-weight: 900; color: var(--text-primary);">👥 {{ __('Workspace Roster') }} ({{ $members->count() }})</h3>
                </div>
                <div style="overflow-x: auto;">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>{{ __('Member') }}</th>
                                <th>{{ __('Email') }}</th>
                                <th>{{ __('Department & Team') }}</th>
                                <th>{{ __('Job Title') }}</th>
                                <th>{{ __('Role') }}</th>
                                <th>{{ __('Status') }}</th>
                                @if($membership->hasPermission('members.manage') || $membership->role?->slug === 'company_admin')
                                <th>{{ __('Actions') }}</th>
                                @endif
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($members as $m)
                                @php
                                    $profile = $m->user->profiles->where('organization_id', $organization->id)->first();
                                    $memberDept = $departments->where('id', $profile?->department_id)->first();
                                    $memberTeam = $teams->where('id', $profile?->team_id)->first();
                                @endphp
                                <tr>
                                    <td>
                                        <div onclick="openMemberProfileModal('{{ $m->id }}')" style="display: flex; align-items: center; gap: 10px; cursor: pointer; transition: all 0.2s;" onmouseover="this.style.opacity='0.8'" onmouseout="this.style.opacity='1'" title="{{ __('Click to view member profile, tasks & work time') }}">
                                            <div style="width: 34px; height: 34px; border-radius: 10px; background: var(--accent-gradient); display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 12px; color: white; box-shadow: var(--shadow-soft-3d); flex-shrink: 0;">
                                                {{ strtoupper(substr($m->user->name, 0, 2)) }}
                                            </div>
                                            <div>
                                                <div style="color: var(--brand-forest); font-size: 13px; font-weight: 800; display: flex; align-items: center; gap: 4px;">
                                                    <span>{{ $m->user->name }}</span>
                                                    <span style="font-size: 10px; opacity: 0.7;">👁️</span>
                                                </div>
                                                @if($m->user->nickname)
                                                    <div style="font-size: 10px; color: var(--text-muted); font-family: monospace;">{{ $m->user->nickname }}</div>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                    <td style="font-size: 12px; color: var(--text-muted);">{{ $m->user->email }}</td>
                                    <td>
                                        @if($memberDept)
                                            <div style="display: flex; flex-direction: column; gap: 2px;">
                                                <span class="nav-badge-pill" style="font-size: 11px;">🏛️ {{ $memberDept->name }}</span>
                                                @if($memberTeam)
                                                    <span style="font-size: 10px; color: var(--text-muted); font-weight: 700;">↳ 👥 {{ $memberTeam->name }}</span>
                                                @endif
                                            </div>
                                        @else
                                            <span style="color: var(--text-muted); font-size: 11px; font-style: italic;">— {{ __('Not Assigned') }} —</span>
                                        @endif
                                    </td>
                                    <td style="font-size: 12px; font-weight: 600; color: var(--text-secondary);">
                                        {{ $profile?->job_title ?? '—' }}
                                    </td>
                                    <td>
                                        <span class="nav-badge-pill" style="background: rgba(79, 155, 95, 0.15); color: #4F9B5F;">{{ $m->role->name ?? __('Company Admin') }}</span>
                                    </td>
                                    <td>
                                        @if($m->status === 'active')
                                            <span class="nav-badge-pill" style="background: rgba(79, 155, 95, 0.2); color: #4F9B5F; font-weight: 800;">🟢 {{ __('Active') }}</span>
                                        @elseif($m->status === 'invited')
                                            <span class="nav-badge-pill" style="background: rgba(214, 162, 58, 0.2); color: #D6A23A; font-weight: 800;">✉️ {{ __('Invited / Pending') }}</span>
                                        @elseif($m->status === 'suspended')
                                            <span class="nav-badge-pill" style="background: rgba(217, 107, 95, 0.2); color: #D96B5F; font-weight: 800;">🔴 {{ __('Suspended') }}</span>
                                        @else
                                            <span class="nav-badge-pill">{{ ucfirst($m->status) }}</span>
                                        @endif
                                    </td>
                                    @if($membership->hasPermission('members.manage') || $membership->role?->slug === 'company_admin')
                                    <td>
                                        <div style="display: flex; gap: 6px; align-items: center; flex-wrap: wrap;">
                                            @if($m->user_id !== $user->id)
                                                <form method="POST" action="{{ route('organization.members.impersonate', $m->id) }}" style="display: inline;" onsubmit="return confirm('{{ __('Are you sure you want to log in as :name?', ['name' => addslashes($m->user->name)]) }}');">
                                                    @csrf
                                                    <button type="submit" class="tactile-btn" style="background: rgba(59, 130, 246, 0.15); color: #60A5FA; border: 1px solid rgba(59, 130, 246, 0.3); padding: 5px 10px; font-size: 11px; font-weight: 800;" title="{{ __('Log in as this member (تسجيل الدخول كعضو)') }}">
                                                        <span>👤</span> {{ __('Login As') }}
                                                    </button>
                                                </form>
                                            @endif
                                            <button onclick="openEditMemberModal('{{ $m->id }}', '{{ addslashes($m->user->name) }}', '{{ addslashes($m->user->email) }}', '{{ $profile?->department_id }}', '{{ $profile?->team_id }}', '{{ $m->role_id }}', '{{ addslashes($profile?->job_title ?? '') }}', '{{ $m->status }}')" class="tactile-btn btn-secondary" style="padding: 5px 10px; font-size: 11px; font-weight: 800;" title="{{ __('Edit Member Data (تعديل البيانات)') }}">
                                                <span>✏️</span> {{ __('Edit') }}
                                            </button>
                                            <button onclick="openChangeMemberPasswordModal('{{ $m->id }}', '{{ addslashes($m->user->name) }}')" class="tactile-btn" style="background: rgba(214, 162, 58, 0.15); color: #D6A23A; border: 1px solid rgba(214, 162, 58, 0.3); padding: 5px 10px; font-size: 11px; font-weight: 800;" title="{{ __('Change Password (تغيير كلمة المرور)') }}">
                                                <span>🔑</span> {{ __('Password') }}
                                            </button>
                                            @if($m->user_id !== $user->id)
                                                <form method="POST" action="{{ route('organization.members.delete', $m->id) }}" onsubmit="return confirm('{{ __('Are you sure you want to remove this member from your company?') }}');" style="display: inline;">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="tactile-btn" style="background: rgba(217, 107, 95, 0.15); color: #D96B5F; border: 1px solid rgba(217, 107, 95, 0.3); padding: 5px 8px; font-size: 11px;" title="{{ __('Remove Member (حذف)') }}">
                                                        🗑️
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                    </td>
                                    @endif
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        @endif

        <!-- 2.5 BILLING & SUBSCRIPTION TAB -->
        @if($membership->hasPermission('billing.manage'))
        <div id="tab-billing" class="tab-view">
            <div class="page-header" style="margin-bottom: 24px;">
                <h1 class="page-title" style="font-size: 22px; font-weight: 900; color: var(--text-primary); margin-bottom: 4px;">💎 {{ __('Billing & Subscription') }}</h1>
                <p class="page-subtitle" style="font-size: 13px; color: var(--text-secondary);">{{ __('Manage your plan tier, seat capacity, renewal period, and workspace upgrade.') }}</p>
            </div>

            <!-- Pending Subscription Request Banner -->
            @if(isset($pendingSubscriptionRequest) && $pendingSubscriptionRequest)
            <div class="card" style="margin-bottom: 24px; border-radius: var(--radius-xl); padding: 22px; border: 2px solid #D6A23A; background: linear-gradient(135deg, rgba(214, 162, 58, 0.08) 0%, rgba(214, 162, 58, 0.02) 100%); box-shadow: var(--shadow-card);">
                <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px;">
                    <div style="display: flex; align-items: center; gap: 14px;">
                        <div style="width: 48px; height: 48px; border-radius: 12px; background: rgba(214, 162, 58, 0.2); color: #D6A23A; display: flex; align-items: center; justify-content: center; font-size: 24px;">
                            ⏳
                        </div>
                        <div>
                            <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 4px;">
                                <span style="font-size: 11px; font-weight: 900; color: #996D12; text-transform: uppercase;">{{ __('Pending Wire Transfer Approval') }}</span>
                                <span class="nav-badge-pill" style="background: rgba(214, 162, 58, 0.25); color: #996D12; font-size: 10px; font-weight: 900;">{{ __('Under SuperAdmin Review') }}</span>
                            </div>
                            <h3 style="font-size: 18px; font-weight: 900; color: var(--text-primary); margin: 0;">
                                💎 {{ __('Upgrade to') }} {{ $pendingSubscriptionRequest->plan?->name ?? __('Plan') }} — {{ number_format($pendingSubscriptionRequest->amount, 2) }} {{ $pendingSubscriptionRequest->currency }}
                            </h3>
                            <div style="font-size: 12px; color: var(--text-secondary); margin-top: 4px; display: flex; gap: 14px; flex-wrap: wrap;">
                                <span>🏦 <strong>{{ $pendingSubscriptionRequest->bank_name }}</strong></span>
                                <span>📋 {{ __('Ref') }}: <strong style="font-family: monospace;">{{ $pendingSubscriptionRequest->transfer_reference }}</strong></span>
                                <span>📅 {{ $pendingSubscriptionRequest->created_at->diffForHumans() }}</span>
                            </div>
                        </div>
                    </div>

                    <div style="display: flex; gap: 10px; align-items: center;">
                        <a href="{{ route('subscription.payment', $pendingSubscriptionRequest->plan_id) }}" class="tactile-btn" style="padding: 9px 16px; font-size: 12px; text-decoration: none;">
                            📄 {{ __('View Transfer Details') }}
                        </a>
                        <form method="POST" action="{{ route('subscription.payment.cancel', $pendingSubscriptionRequest->id) }}" onsubmit="return confirm('{{ __('Are you sure you want to cancel this pending subscription request?') }}');" style="margin: 0;">
                            @csrf
                            <button type="submit" class="tactile-btn" style="padding: 9px 14px; font-size: 12px; color: #D96B5F; border-color: rgba(217, 107, 95, 0.3);">
                                ✕ {{ __('Cancel') }}
                            </button>
                        </form>
                    </div>
                </div>
            </div>
            @endif

            @php
                $currentPlan = $organization->plan ?? \App\Domains\Tenancy\Models\Plan::where('slug', 'free')->first();
                $seatLimit = $currentPlan?->seat_limit ?? 5;
                $roomLimit = $currentPlan?->room_limit ?? 3;
                $maxOffices = $currentPlan?->max_offices ?? 1;
                $maxGuests = $currentPlan?->max_guest_invitations ?? 5;
                $storageLimit = $currentPlan?->storage_limit_gb ?? 1;

                $usedSeats = $members->count();
                $usedRooms = $rooms->count();
                $usedOffices = $offices->count();
                $usedGuests = $guestInvitations->count();

                $isSeatsExceeded = ($seatLimit > 0 && $usedSeats > $seatLimit);
                $isRoomsExceeded = ($roomLimit > 0 && $usedRooms > $roomLimit);
                $isOfficesExceeded = ($maxOffices > 0 && $usedOffices > $maxOffices);
                $isGuestsExceeded = ($maxGuests > 0 && $usedGuests > $maxGuests);
                $isAnyExceeded = ($isSeatsExceeded || $isRoomsExceeded || $isOfficesExceeded || $isGuestsExceeded);

                $isUnlimitedSeats = ($seatLimit === 0);
                $seatPercent = $isUnlimitedSeats ? 20 : min(100, round(($usedSeats / max(1, $seatLimit)) * 100));

                $subscription = $organization->subscription;
                $startDate = $subscription?->created_at ?? $organization->created_at;
                $endDate = $subscription?->current_period_end ?? ($startDate ? (clone $startDate)->addMonth() : now()->addMonth());
                $status = $subscription?->status ?? 'active';

                $priceUSD = (float)($currentPlan->price ?? 0);
                $priceSAR = round($priceUSD * 3.75, 2);
            @endphp

            @if($isAnyExceeded)
            <!-- Exceeded Plan Quota Warning Banner -->
            <div class="card" style="margin-bottom: 24px; border-radius: var(--radius-xl); padding: 18px 24px; border: 2px solid #D96B5F; background: linear-gradient(135deg, rgba(217, 107, 95, 0.12) 0%, rgba(217, 107, 95, 0.03) 100%); box-shadow: var(--shadow-card);">
                <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 14px;">
                    <div style="display: flex; align-items: center; gap: 14px;">
                        <div style="width: 44px; height: 44px; border-radius: 12px; background: rgba(217, 107, 95, 0.2); color: #D96B5F; display: flex; align-items: center; justify-content: center; font-size: 22px;">
                            ⚠️
                        </div>
                        <div>
                            <h3 style="font-size: 16px; font-weight: 900; color: #D96B5F; margin: 0 0 4px 0;">
                                {{ __('Plan Limit Exceeded') }} ({{ __('تجاوزت الحد المسموح للباقة') }})
                            </h3>
                            <p style="font-size: 13px; color: var(--text-secondary); margin: 0;">
                                @if($isRoomsExceeded)
                                    {{ __('Your workspace currently has :used rooms, which exceeds your :plan plan quota (:limit rooms). Please upgrade your subscription plan below.', ['used' => $usedRooms, 'plan' => $currentPlan->name ?? 'Free', 'limit' => $roomLimit]) }}
                                @elseif($isSeatsExceeded)
                                    {{ __('Your workspace currently has :used members, which exceeds your :plan seat quota (:limit seats). Please upgrade your plan below.', ['used' => $usedSeats, 'plan' => $currentPlan->name ?? 'Free', 'limit' => $seatLimit]) }}
                                @else
                                    {{ __('Some workplace resources exceed your current plan limits. Please upgrade below.') }}
                                @endif
                            </p>
                        </div>
                    </div>
                    <a href="#available-plans-section" class="tactile-btn" style="background: #D96B5F; color: white; border: none; padding: 10px 20px; font-size: 13px; font-weight: 800; text-decoration: none; display: flex; align-items: center; gap: 6px;">
                        <span>🚀</span> {{ __('Upgrade Plan Now (ترقية الباقة)') }}
                    </a>
                </div>
            </div>
            @endif

            <!-- Current Plan Card (3D Soft Neumorphic) -->
            <div class="card" style="margin-bottom: 28px; border-radius: var(--radius-xl); padding: 24px; border: 1px solid var(--border-color); box-shadow: var(--shadow-card); background: var(--bg-surface);">
                <!-- Top Row: Plan info, SAR/USD Price, and Status -->
                <div style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 16px; margin-bottom: 20px; padding-bottom: 18px; border-bottom: 1px solid var(--border-color);">
                    <div>
                        <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 4px;">
                            <span style="font-size: 11px; font-weight: 800; color: var(--brand-forest); text-transform: uppercase; letter-spacing: 0.5px;">{{ __('Current Plan') }}</span>
                            <span class="nav-badge-pill" style="background: rgba(79, 155, 95, 0.2); color: #4F9B5F; font-size: 10px; text-transform: uppercase;">{{ ucfirst($status) }}</span>
                            @if($isAnyExceeded)
                                <span class="nav-badge-pill" style="background: rgba(217, 107, 95, 0.2); color: #D96B5F; font-size: 10px; font-weight: 900;">⚠️ {{ __('Limit Exceeded') }}</span>
                            @endif
                        </div>
                        <h2 style="font-size: 26px; font-weight: 900; color: var(--text-primary); margin: 4px 0;">💎 {{ $currentPlan->name ?? __('Free Tier') }}</h2>
                        <div style="display: flex; align-items: baseline; gap: 10px; margin-top: 6px;">
                            <span style="font-size: 24px; font-weight: 900; color: var(--brand-forest);">
                                {{ number_format($priceSAR, 2) }} <span style="font-size: 14px; font-weight: 700; color: var(--text-secondary);">{{ __('SAR (ر.س)') }}</span>
                            </span>
                            <span style="font-size: 13px; font-weight: 600; color: var(--text-muted);">
                                (${{ number_format($priceUSD, 2) }} USD / {{ __('month') }})
                            </span>
                        </div>
                    </div>

                    <!-- Dates & Period Box -->
                    <div style="display: flex; gap: 20px; background: var(--bg-surface-subtle); padding: 14px 20px; border-radius: var(--radius-md); border: 1px solid var(--border-color); box-shadow: var(--shadow-inset-3d);">
                        <div>
                            <div style="font-size: 10px; font-weight: 800; color: var(--text-muted); text-transform: uppercase; margin-bottom: 2px;">📅 {{ __('Start Date') }}</div>
                            <div style="font-size: 13px; font-weight: 800; color: var(--text-primary); font-family: monospace;">{{ $startDate ? $startDate->format('Y-m-d') : '—' }}</div>
                        </div>
                        <div style="width: 1px; background: var(--border-color);"></div>
                        <div>
                            <div style="font-size: 10px; font-weight: 800; color: var(--text-muted); text-transform: uppercase; margin-bottom: 2px;">🔄 {{ __('Renewal / End Date') }}</div>
                            <div style="font-size: 13px; font-weight: 800; color: var(--brand-forest); font-family: monospace;">{{ $endDate ? $endDate->format('Y-m-d') : '—' }}</div>
                        </div>
                    </div>
                </div>

                <!-- Plan Details & Limits Breakdown -->
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(170px, 1fr)); gap: 14px; margin-bottom: 20px;">
                    <!-- User Capacity -->
                    <div class="kpi-card" style="padding: 14px; margin-bottom: 0; border-color: {{ $isSeatsExceeded ? '#D96B5F' : 'var(--border-color)' }};">
                        <div class="kpi-header">
                            <span class="kpi-title">{{ __('User Capacity') }}</span>
                            <div class="kpi-icon-box">👥</div>
                        </div>
                        <div class="kpi-value" style="font-size: 18px; color: {{ $isSeatsExceeded ? '#D96B5F' : 'inherit' }};">
                            {{ $usedSeats }} / {{ $isUnlimitedSeats ? __('Unlimited') : $seatLimit . ' ' . __('Seats') }}
                            @if($isSeatsExceeded)
                                <div style="font-size: 10px; font-weight: 800; color: #D96B5F; margin-top: 4px;">⚠️ {{ __('Exceeded') }}</div>
                            @endif
                        </div>
                    </div>

                    <!-- Meeting Rooms -->
                    <div class="kpi-card" style="padding: 14px; margin-bottom: 0; border-color: {{ $isRoomsExceeded ? '#D96B5F' : 'var(--border-color)' }};">
                        <div class="kpi-header">
                            <span class="kpi-title">{{ __('Meeting Rooms') }}</span>
                            <div class="kpi-icon-box">🏢</div>
                        </div>
                        <div class="kpi-value" style="font-size: 18px; color: {{ $isRoomsExceeded ? '#D96B5F' : 'inherit' }};">
                            {{ $usedRooms }} / {{ $roomLimit === 0 ? __('Unlimited') : $roomLimit . ' ' . __('Rooms') }}
                            @if($isRoomsExceeded)
                                <div style="font-size: 10px; font-weight: 800; color: #D96B5F; margin-top: 4px;">⚠️ {{ __('Exceeded Limit') }}</div>
                            @endif
                        </div>
                    </div>

                    <!-- Office Branches -->
                    <div class="kpi-card" style="padding: 14px; margin-bottom: 0; border-color: {{ $isOfficesExceeded ? '#D96B5F' : 'var(--border-color)' }};">
                        <div class="kpi-header">
                            <span class="kpi-title">{{ __('Office Branches') }}</span>
                            <div class="kpi-icon-box">📍</div>
                        </div>
                        <div class="kpi-value" style="font-size: 18px; color: {{ $isOfficesExceeded ? '#D96B5F' : 'inherit' }};">
                            {{ $usedOffices }} / {{ $maxOffices === 0 ? __('Unlimited') : $maxOffices . ' ' . __('Branch') }}
                        </div>
                    </div>

                    <!-- Guest Links -->
                    <div class="kpi-card" style="padding: 14px; margin-bottom: 0;">
                        <div class="kpi-header">
                            <span class="kpi-title">{{ __('Guest Links') }}</span>
                            <div class="kpi-icon-box">🔗</div>
                        </div>
                        <div class="kpi-value" style="font-size: 18px;">
                            {{ $usedGuests }} / {{ $maxGuests === 0 ? __('Unlimited') : $maxGuests . ' ' . __('Links') }}
                        </div>
                    </div>

                    <!-- Cloud Storage -->
                    <div class="kpi-card" style="padding: 14px; margin-bottom: 0;">
                        <div class="kpi-header">
                            <span class="kpi-title">{{ __('Cloud Storage') }}</span>
                            <div class="kpi-icon-box">💾</div>
                        </div>
                        <div class="kpi-value" style="font-size: 18px;">
                            {{ $storageLimit === 0 ? __('Unlimited') : $storageLimit . ' GB' }}
                        </div>
                    </div>
                </div>

                <!-- Seat Progress Bar -->
                <div>
                    <div style="display: flex; justify-content: space-between; font-size: 11px; font-weight: 800; margin-bottom: 6px;">
                        <span style="color: var(--text-secondary);">{{ __('Seat Utilization') }}</span>
                        <span style="color: {{ $seatPercent > 90 ? '#D96B5F' : 'var(--brand-forest)' }};">{{ $seatPercent }}% {{ __('Consumed') }}</span>
                    </div>
                    <div class="progress-bar-bg" style="background: var(--bg-surface-subtle); height: 8px; border-radius: 9999px; overflow: hidden; border: 1px solid var(--border-color);">
                        <div class="progress-bar-fill" style="width: {{ $seatPercent }}%; background: {{ $seatPercent > 90 ? '#D96B5F' : 'var(--accent-gradient)' }}; height: 100%; border-radius: 9999px; transition: width 0.4s ease;"></div>
                    </div>
                </div>
            </div>

            <!-- Available Upgrade Plans Grid -->
            <div id="available-plans-section">
                <h3 style="font-size: 18px; font-weight: 900; margin-bottom: 16px; color: var(--text-primary);">{{ __('Available Subscription Plans') }}</h3>
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 20px;">
                    @foreach($allPlans as $p)
                    @php
                        $isCurrent = ($organization->plan_id == $p->id);
                        $pPriceUSD = (float)$p->price;
                        $pPriceSAR = round($pPriceUSD * 3.75, 2);
                        $isPaid = $pPriceUSD > 0;
                    @endphp
                    <div class="card plan-selection-card" style="padding: 24px; border-radius: var(--radius-xl); border: 2px solid {{ $isCurrent ? 'var(--brand-forest)' : 'var(--border-color)' }}; position: relative; display: flex; flex-direction: column; justify-content: space-between; box-shadow: {{ $isCurrent ? 'var(--shadow-hover)' : 'var(--shadow-card)' }}; background: var(--bg-surface);">
                        @if($isCurrent)
                            <div style="position: absolute; top: -12px; right: 20px; background: var(--brand-forest); color: white; padding: 4px 12px; border-radius: 9999px; font-size: 11px; font-weight: 800; text-transform: uppercase; box-shadow: 0 4px 10px rgba(36,92,58,0.3);">
                                ⭐ {{ __('Current Active') }}
                            </div>
                        @endif
                        <div>
                            <div style="font-size: 12px; font-weight: 800; color: var(--brand-forest); text-transform: uppercase; margin-bottom: 6px;">{{ $p->slug }}</div>
                            <h4 style="font-size: 20px; font-weight: 900; color: var(--text-primary); margin-bottom: 10px;">{{ $p->name }}</h4>
                            <div style="margin-bottom: 16px;">
                                <span style="font-size: 28px; font-weight: 900; color: var(--text-primary);">
                                    {{ number_format($pPriceSAR, 2) }} <span style="font-size: 13px; font-weight: 700; color: var(--text-secondary);">SAR</span>
                                </span>
                                <span style="font-size: 12px; color: var(--text-muted);">
                                    (${{ number_format($pPriceUSD, 2) }} / {{ __('mo') }})
                                </span>
                            </div>
                            <ul style="list-style: none; padding: 0; margin: 0 0 20px 0; font-size: 13px; color: var(--text-secondary); display: flex; flex-direction: column; gap: 8px;">
                                <li style="display: flex; align-items: center; gap: 8px;">
                                    <span>👥</span> <strong>{{ $p->seat_limit === 0 ? __('Unlimited') : $p->seat_limit }}</strong> {{ __('Team Members') }}
                                </li>
                                <li style="display: flex; align-items: center; gap: 8px;">
                                    <span>🏢</span> <strong>{{ $p->room_limit === 0 ? __('Unlimited') : $p->room_limit }}</strong> {{ __('Meeting Rooms') }}
                                </li>
                                <li style="display: flex; align-items: center; gap: 8px;">
                                    <span>📍</span> <strong>{{ ($p->max_offices ?? 1) === 0 ? __('Unlimited') : ($p->max_offices ?? 1) }}</strong> {{ __('Office Branches') }}
                                </li>
                                <li style="display: flex; align-items: center; gap: 8px;">
                                    <span>🔗</span> <strong>{{ ($p->max_guest_invitations ?? 5) === 0 ? __('Unlimited') : ($p->max_guest_invitations ?? 5) }}</strong> {{ __('Guest Meeting Links') }}
                                </li>
                                <li style="display: flex; align-items: center; gap: 8px;">
                                    <span>💾</span> <strong>{{ $p->storage_limit_gb === 0 ? __('Unlimited') : $p->storage_limit_gb . ' GB' }}</strong> {{ __('Storage') }}
                                </li>
                            </ul>
                        </div>

                        @if($isCurrent)
                            <button disabled class="tactile-btn btn-secondary" style="width: 100%; padding: 12px; opacity: 0.6; cursor: not-allowed; justify-content: center;">
                                ✓ {{ __('Current Plan') }}
                            </button>
                        @elseif($isPaid)
                            <a href="{{ route('subscription.payment', $p->id) }}" class="tactile-btn btn-primary" style="width: 100%; padding: 12px; font-weight: 800; text-align: center; text-decoration: none; justify-content: center;">
                                💳 {{ __('Subscribe & Bank Transfer') }}
                            </a>
                        @else
                            <form method="POST" action="{{ route('organization.upgrade_plan') }}">
                                @csrf
                                <input type="hidden" name="plan_id" value="{{ $p->id }}">
                                <button type="submit" class="tactile-btn btn-primary" style="width: 100%; padding: 12px; font-weight: 800; justify-content: center;">
                                    🚀 {{ __('Switch to') }} {{ $p->name }}
                                </button>
                            </form>
                        @endif
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
        @endif

        <!-- 2.5 OFFICES & BRANCHES TAB -->
        @if($membership->hasPermission('maps.manage') || $membership->role?->slug === 'company_admin')
        <div id="tab-offices" class="tab-view">
            <div class="page-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; flex-wrap: wrap; gap: 14px;">
                <div>
                    <h1 class="page-title" style="font-size: 22px; font-weight: 900; color: var(--text-primary); margin-bottom: 4px;">🏢 {{ __('Offices & Virtual Branches (الفروع ومكاتب العمل)') }}</h1>
                    <p class="page-subtitle" style="font-size: 13px; color: var(--text-secondary);">{{ __('Manage multiple branches (e.g. Cairo Branch, Riyadh HQ, Dubai Hub), their blueprints, and member access permissions.') }}</p>
                </div>
                <div style="display: flex; gap: 10px; align-items: center;">
                    @if(!$organization->hasReachedOfficeLimit())
                    <button onclick="openNewOfficeModal()" class="tactile-btn btn-primary" style="padding: 10px 18px; font-size: 13px;">
                        <span>➕</span> {{ __('Add Office Branch (إضافة فرع جديد)') }}
                    </button>
                    @else
                    <button onclick="switchAdminTab('billing')" class="tactile-btn" style="padding: 10px 18px; font-size: 13px; background: linear-gradient(180deg, #D6A23A 0%, #B4831B 100%); color: white; border: 1px solid #996D12;">
                        <span>👑</span> {{ __('Upgrade Plan for More Offices') }}
                    </button>
                    @endif
                </div>
            </div>

            <!-- Quota Indicator Banner -->
            <div style="background: var(--bg-surface-subtle); border: 1px solid var(--border-color); border-radius: var(--radius-lg); padding: 14px 20px; display: flex; align-items: center; justify-content: space-between; margin-bottom: 24px; flex-wrap: wrap; gap: 12px;">
                <div style="display: flex; align-items: center; gap: 10px;">
                    <span style="font-size: 20px;">💎</span>
                    <div>
                        <strong style="color: var(--text-primary); font-size: 13px;">{{ __('Offices Quota:') }} {{ $offices->count() }} / {{ $organization->plan?->isUnlimitedOffices() ? __('Unlimited (غير محدود)') : ($organization->plan?->max_offices ?? 1) }}</strong>
                        <div style="font-size: 11px; color: var(--text-secondary);">{{ __('Your organization is subscribed to :plan plan.', ['plan' => $organization->plan?->name ?? 'Default']) }}</div>
                    </div>
                </div>
                <span class="badge-status" style="background: rgba(36, 92, 58, 0.12); color: var(--brand-forest); font-weight: 800; font-size: 12px;">
                    {{ $offices->count() }} {{ __('Active Branches') }}
                </span>
            </div>

            <!-- Offices Grid -->
            <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 20px;">
                @forelse($offices as $off)
                <div class="card" style="border-radius: var(--radius-xl); padding: 22px; display: flex; flex-direction: column; justify-content: space-between; border: 1px solid var(--border-color); box-shadow: var(--shadow-card); transition: all 0.25s ease;">
                    <div>
                        <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 14px;">
                            <div style="display: flex; align-items: center; gap: 12px;">
                                <div style="width: 44px; height: 44px; border-radius: 14px; background: var(--accent-gradient); color: white; display: flex; align-items: center; justify-content: center; font-size: 22px; box-shadow: var(--shadow-soft-3d);">
                                    🏢
                                </div>
                                <div>
                                    <h3 style="font-size: 16px; font-weight: 900; color: var(--text-primary); margin: 0 0 2px 0;">{{ $off->name }}</h3>
                                    <span style="font-size: 12px; color: var(--text-muted); font-weight: 600;">
                                        📍 {{ $off->city_location ?: __('Primary Location') }}
                                    </span>
                                </div>
                            </div>
                            @if($off->is_default)
                                <span class="badge-status" style="background: rgba(79, 155, 95, 0.15); color: #2E6B40; font-size: 11px; font-weight: 900;">
                                    ⭐ {{ __('Main HQ (الرئيسي)') }}
                                </span>
                            @endif
                        </div>

                        @if($off->description)
                            <p style="font-size: 12px; color: var(--text-secondary); margin: 0 0 16px 0; line-height: 1.5;">
                                {{ $off->description }}
                            </p>
                        @endif

                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-bottom: 18px; padding: 12px; background: var(--bg-surface-subtle); border-radius: 12px; border: 1px solid var(--border-color);">
                            <div>
                                <span style="font-size: 11px; color: var(--text-muted);">{{ __('Configured Rooms') }}</span>
                                <div style="font-size: 15px; font-weight: 900; color: var(--text-primary); margin-top: 2px;">
                                    🚪 {{ $off->rooms->count() }}
                                </div>
                            </div>
                            <div>
                                <span style="font-size: 11px; color: var(--text-muted);">{{ __('Assigned Staff') }}</span>
                                <div style="font-size: 15px; font-weight: 900; color: var(--brand-forest); margin-top: 2px;">
                                    👥 {{ $off->members->count() > 0 ? $off->members->count() : __('All (الكل)') }}
                                </div>
                            </div>
                        </div>
                    </div>

                    <div style="display: flex; gap: 8px; flex-wrap: wrap; pt-2; border-top: 1px solid var(--border-color); padding-top: 14px;">
                        <a href="{{ route('office', ['office' => $off->id]) }}" class="tactile-btn btn-primary" style="flex: 1; justify-content: center; padding: 8px 12px; font-size: 12px; text-decoration: none;">
                            <span>🚀</span> {{ __('Enter Office') }}
                        </a>
                        <button onclick="openEditOfficeModal('{{ $off->id }}', '{{ addslashes($off->name) }}', '{{ addslashes($off->city_location ?? '') }}', '{{ addslashes($off->description ?? '') }}', {{ $off->is_default ? 'true' : 'false' }})" class="tactile-btn" style="padding: 8px 12px; font-size: 12px; background: var(--bg-surface-subtle);" title="{{ __('Edit Branch Details') }}">
                            <span>✏️</span>
                        </button>
                        @if($offices->count() > 1)
                        <form method="POST" action="{{ route('offices.delete', $off->id) }}" onsubmit="return confirm('{{ __('Are you sure you want to permanently delete this office branch and its blueprint?') }}');" style="margin: 0;">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="tactile-btn" style="padding: 8px 12px; font-size: 12px; background: rgba(217, 107, 95, 0.12); color: #D96B5F; border-color: rgba(217, 107, 95, 0.3);" title="{{ __('Delete Branch') }}">
                                <span>🗑️</span>
                            </button>
                        </form>
                        @endif
                    </div>
                </div>
                @empty
                <div style="grid-column: 1 / -1; text-align: center; padding: 40px; color: var(--text-muted);">
                    <p>{{ __('No office branches configured yet.') }}</p>
                </div>
                @endforelse
            </div>
        </div>
        @endif

        <!-- 3. ROOMS TAB -->
        @if($membership->hasPermission('rooms.manage'))
        <div id="tab-rooms" class="tab-view">
            <div class="page-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; flex-wrap: wrap; gap: 14px;">
                <div>
                    <h1 class="page-title" style="font-size: 22px; font-weight: 900; color: var(--text-primary); margin-bottom: 4px;">🚪 {{ __('Meeting Rooms & Doors') }}</h1>
                    <p class="page-subtitle" style="font-size: 13px; color: var(--text-secondary);">{{ __('Configure private offices, conference rooms, and door lock states.') }}</p>
                </div>
                <div style="display: flex; gap: 10px; flex-wrap: wrap;">
                    <a href="{{ route('editor') }}" class="tactile-btn" style="background: linear-gradient(135deg, #10B981, #059669); color: white; padding: 10px 18px; font-size: 13px; text-decoration: none; font-weight: 800; box-shadow: 0 4px 14px rgba(16, 185, 129, 0.35);">
                        <span>✨</span> {{ __('AI Office Generator (توليد ذكي)') }}
                    </a>
                    <a href="{{ route('editor') }}" class="tactile-btn btn-primary" style="padding: 10px 18px; font-size: 13px; text-decoration: none;">
                        <span>🎨</span> {{ __('Launch Floor Editor') }}
                    </a>
                </div>
            </div>

            <div class="card" style="border-radius: var(--radius-lg); overflow: hidden; padding: 0;">
                <div style="padding: 20px 24px; border-bottom: 1px solid var(--border-color); display: flex; justify-content: space-between; align-items: center; background: var(--bg-surface);">
                    <h3 style="font-size: 16px; font-weight: 900; color: var(--text-primary);">🏢 {{ __('Configured Workplace Rooms') }} ({{ $rooms->count() }})</h3>
                </div>
                <div style="overflow-x: auto;">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>{{ __('Room Name') }}</th>
                                <th>{{ __('Type') }}</th>
                                <th>{{ __('Capacity') }}</th>
                                <th>{{ __('Door Status') }}</th>
                                <th>{{ __('Actions') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($rooms as $r)
                                <tr>
                                    <td>
                                        <strong style="color: var(--text-primary);">🏢 {{ $r->name }}</strong>
                                    </td>
                                    <td><span class="nav-badge-pill">{{ ucfirst($r->type) }}</span></td>
                                    <td style="font-weight: 700; font-family: monospace;">{{ $r->capacity }} {{ __('Seats') }}</td>
                                    <td>
                                        <span class="nav-badge-pill" style="{{ $r->access_mode === 'private' ? 'background: rgba(214, 162, 58, 0.15); color: #D6A23A;' : 'background: rgba(79, 155, 95, 0.15); color: #4F9B5F;' }}">
                                            {{ $r->access_mode === 'private' ? '🔒 ' . __('Locked') : '🔓 ' . __('Open') }}
                                        </span>
                                    </td>
                                    <td>
                                        <a href="{{ route('office') }}" class="tactile-btn btn-secondary" style="padding: 6px 12px; font-size: 11px; text-decoration: none;">{{ __('Enter Office') }}</a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        @endif

        <!-- 3.5 SCHEDULED MEETINGS TAB (Administration -> Meetings & Schedule) -->
        <div id="tab-meetings" class="tab-view">
            <div class="page-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; flex-wrap: wrap; gap: 14px;">
                <div>
                    <h1 class="page-title" style="font-size: 22px; font-weight: 900; color: var(--text-primary); margin-bottom: 4px;">📅 {{ __('Scheduled Meetings & Sessions') }}</h1>
                    <p class="page-subtitle" style="font-size: 13px; color: var(--text-secondary);">{{ __('Schedule general or project meetings, manage attendee invitations, and broadcast sound alerts.') }}</p>
                </div>
                <div style="display: flex; gap: 10px; align-items: center;">
                    <button onclick="openScheduleMeetingModal('general')" class="tactile-btn btn-primary" style="padding: 10px 18px; font-size: 13px;">
                        <span>+</span> {{ __('Schedule General Meeting') }}
                    </button>
                </div>
            </div>

            <!-- KPI Metric Cards for Meetings -->
            <div class="kpi-grid" style="margin-bottom: 24px;">
                <div class="kpi-card">
                    <div class="kpi-header">
                        <span class="kpi-title">{{ __('Upcoming Meetings') }}</span>
                        <div class="kpi-icon-box">📅</div>
                    </div>
                    <div class="kpi-value">{{ $upcomingMeetings->count() }}</div>
                    <div class="kpi-trend" style="color: var(--brand-forest);">
                        <span>🟢</span> {{ __('Ready to join') }}
                    </div>
                </div>
                <div class="kpi-card">
                    <div class="kpi-header">
                        <span class="kpi-title">{{ __('Project Meetings') }}</span>
                        <div class="kpi-icon-box">📁</div>
                    </div>
                    <div class="kpi-value">{{ $allMeetings->whereNotNull('project_id')->count() }}</div>
                    <div class="kpi-trend">
                        <span>👥</span> {{ __('Team synced') }}
                    </div>
                </div>
                <div class="kpi-card">
                    <div class="kpi-header">
                        <span class="kpi-title">{{ __('General Meetings') }}</span>
                        <div class="kpi-icon-box">🌐</div>
                    </div>
                    <div class="kpi-value">{{ $allMeetings->whereNull('project_id')->count() }}</div>
                    <div class="kpi-trend">
                        <span>🛡️</span> {{ __('Ad-hoc roster') }}
                    </div>
                </div>
                <div class="kpi-card">
                    <div class="kpi-header">
                        <span class="kpi-title">{{ __('Total Hosted') }}</span>
                        <div class="kpi-icon-box">⚡</div>
                    </div>
                    <div class="kpi-value">{{ $allMeetings->count() }}</div>
                    <div class="kpi-trend" style="color: var(--brand-forest);">
                        <span>🎉</span> {{ $allMeetings->where('status', 'ended')->count() }} {{ __('Completed') }}
                    </div>
                </div>
            </div>

            <!-- Meetings Table Card -->
            <div class="card" style="border-radius: var(--radius-lg); overflow: hidden; padding: 0;">
                <div style="padding: 20px 24px; border-bottom: 1px solid var(--border-color); display: flex; justify-content: space-between; align-items: center; background: var(--bg-surface);">
                    <h3 style="font-size: 16px; font-weight: 900; color: var(--text-primary);">📅 {{ __('All Organization Meetings & Sessions') }} ({{ $allMeetings->count() }})</h3>
                </div>
                <div style="overflow-x: auto;">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>{{ __('Meeting Title') }}</th>
                                <th>{{ __('Scope / Project') }}</th>
                                <th>{{ __('Date & Time') }}</th>
                                <th>{{ __('Duration') }}</th>
                                <th>{{ __('Room') }}</th>
                                <th>{{ __('Host') }}</th>
                                <th>{{ __('Attendees') }}</th>
                                <th>{{ __('Status') }}</th>
                                <th>{{ __('Actions') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($allMeetings as $m)
                                @php
                                    $isLive = $m->status === 'active';
                                    $isCancelled = $m->status === 'ended' && $m->scheduled_at && $m->scheduled_at->isFuture();
                                    $mParts = $m->participants->take(3);
                                    $moreParts = max(0, $m->participants->count() - 3);
                                @endphp
                                <tr>
                                    <td>
                                        <div style="font-weight: 800; color: var(--text-primary); font-size: 13px;">{{ $m->title }}</div>
                                        @if($m->description)
                                            <div style="font-size: 11px; color: var(--text-muted);">{{ Str::limit($m->description, 40) }}</div>
                                        @endif
                                    </td>
                                    <td>
                                        @if($m->project)
                                            <span class="nav-badge-pill" style="background: rgba(79, 155, 95, 0.15); color: #4F9B5F;">
                                                📁 {{ $m->project->name }}
                                            </span>
                                        @else
                                            <span class="nav-badge-pill" style="background: rgba(214, 162, 58, 0.15); color: #D6A23A;">
                                                🌐 {{ __('General') }}
                                            </span>
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
                                        {{ $m->duration_minutes ?? 30 }} {{ __('min') }}
                                    </td>
                                    <td>
                                        <strong style="color: var(--brand-forest); font-size: 12px;">🚪 {{ $m->room->name ?? 'Meeting Room' }}</strong>
                                    </td>
                                    <td>
                                        <div style="font-size: 12px; font-weight: 700; color: var(--text-primary);">
                                            {{ $m->creator->name ?? 'Admin' }}
                                        </div>
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
                                            <span class="nav-badge-pill" style="background: rgba(217, 107, 95, 0.2); color: #D96B5F; font-weight: 800;">🔴 LIVE</span>
                                        @elseif($m->status === 'scheduled')
                                            <span class="nav-badge-pill" style="background: rgba(79, 155, 95, 0.15); color: #4F9B5F; font-weight: 800;">📅 {{ __('Scheduled') }}</span>
                                        @elseif($m->status === 'ended')
                                            <span class="nav-badge-pill" style="background: var(--bg-surface-subtle); color: var(--text-muted);">{{ __('Completed') }}</span>
                                        @else
                                            <span class="nav-badge-pill">{{ ucfirst($m->status) }}</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div style="display: flex; align-items: center; gap: 6px;">
                                            <a href="{{ route('office') }}" class="tactile-btn btn-primary" style="padding: 6px 12px; font-size: 11px; text-decoration: none;">
                                                🚀 {{ __('Join') }}
                                            </a>
                                            @if($m->status === 'scheduled')
                                                <form method="POST" action="{{ route('meetings.cancel', $m->id) }}" onsubmit="return confirm('{{ __('Are you sure you want to cancel this meeting?') }}');" style="display: inline;">
                                                    @csrf
                                                    <button type="submit" class="tactile-btn" style="background: rgba(217, 107, 95, 0.15); color: #D96B5F; border: 1px solid rgba(217, 107, 95, 0.3); padding: 6px 10px; font-size: 11px;" title="{{ __('Cancel Meeting') }}">
                                                        ✕
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" style="text-align: center; color: var(--text-muted); padding: 40px;">
                                        <div style="font-size: 32px; margin-bottom: 8px;">📅</div>
                                        {{ __('No meetings scheduled yet.') }}
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- 4. GUEST INVITATIONS TAB -->
        @if($membership->hasPermission('guests.invite'))
        <div id="tab-guests" class="tab-view">
            <div class="page-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; flex-wrap: wrap; gap: 14px;">
                <div>
                    <h1 class="page-title" style="font-size: 22px; font-weight: 900; color: var(--text-primary); margin-bottom: 4px;">🔗 {{ __('Guest Meeting Links') }}</h1>
                    <p class="page-subtitle" style="font-size: 13px; color: var(--text-secondary);">{{ __('Generate instant join links for clients, interviewees, and external partners.') }}</p>
                </div>
                <div style="display: flex; gap: 10px; align-items: center;">
                    @if($guestInvitations->count() > 0)
                        <form method="POST" action="{{ route('guest_invitations.clear') }}" onsubmit="return confirm('{{ __('Are you sure you want to delete all guest meeting links?') }}');" style="display: inline;">
                            @csrf
                            <button type="submit" class="tactile-btn" style="background: #D96B5F; color: white; padding: 10px 16px; font-size: 13px;">
                                <span>🗑️</span> {{ __('Clear All Links') }}
                            </button>
                        </form>
                    @endif
                    <button onclick="openInviteModal()" class="tactile-btn btn-primary" style="padding: 10px 18px; font-size: 13px;">
                        <span>⚡</span> {{ __('Create Guest Link') }}
                    </button>
                </div>
            </div>

            <div class="card" style="border-radius: var(--radius-lg); overflow: hidden; padding: 0;">
                <div style="padding: 20px 24px; border-bottom: 1px solid var(--border-color); display: flex; justify-content: space-between; align-items: center; background: var(--bg-surface);">
                    <h3 style="font-size: 16px; font-weight: 900; color: var(--text-primary);">🔗 {{ __('Active & Recent Guest Invitations') }} ({{ $guestInvitations->count() }})</h3>
                </div>
                <div style="overflow-x: auto;">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>{{ __('Guest Name / Label') }}</th>
                                <th>{{ __('Target Room') }}</th>
                                <th>{{ __('Expires At') }}</th>
                                <th>{{ __('Join URL') }}</th>
                                <th>{{ __('Actions') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($guestInvitations as $inv)
                                <tr>
                                    <td>
                                        <strong style="color: var(--brand-forest); font-size: 13px;">👤 {{ $inv->guest_name }}</strong>
                                    </td>
                                    <td>🏢 {{ $inv->room->name ?? 'Main Conference' }}</td>
                                    <td style="font-size: 12px; color: var(--text-muted);">{{ $inv->expires_at ? $inv->expires_at->diffForHumans() : __('Never') }}</td>
                                    <td>
                                        <code style="background: var(--bg-surface-subtle); border: 1px solid var(--border-color); padding: 4px 10px; border-radius: 6px; font-size: 11px; color: var(--brand-forest); box-shadow: var(--shadow-inset-3d);">
                                            /guest/join/{{ substr($inv->token, 0, 16) }}...
                                        </code>
                                    </td>
                                    <td>
                                        <div style="display: flex; gap: 8px; align-items: center;">
                                            <button type="button" onclick="copyTableGuestLink('{{ url('/guest/join/' . $inv->token) }}', this)" class="tactile-btn btn-primary" style="padding: 6px 12px; font-size: 11px; cursor: pointer;">
                                                📋 {{ __('Copy Link') }}
                                            </button>
                                            <a href="{{ url('/guest/join/' . $inv->token) }}" target="_blank" class="tactile-btn btn-secondary" style="padding: 6px 12px; font-size: 11px; text-decoration: none;">
                                                👁️ {{ __('Open') }}
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                            <tr>
                                <td colspan="5" style="text-align: center; color: var(--text-muted); padding: 40px;">
                                    {{ __('No guest invitations generated yet.') }}
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        @endif

        <!-- 5. DEPARTMENTS & TEAMS TAB -->
        @if($membership->hasPermission('departments.manage') || $membership->hasPermission('teams.manage'))
        <div id="tab-departments" class="tab-view">
            <div class="page-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; flex-wrap: wrap; gap: 14px;">
                <div>
                    <h1 class="page-title" style="font-size: 22px; font-weight: 900; color: var(--text-primary); margin-bottom: 4px;">🏛️ {{ __('Departments & Teams') }}</h1>
                    <p class="page-subtitle" style="font-size: 13px; color: var(--text-secondary);">{{ __('Organize your organization staff, distribute members across departments, and manage sub-teams.') }}</p>
                </div>
                <button onclick="openDepartmentModal()" class="tactile-btn btn-primary" style="padding: 10px 18px; font-size: 13px;">
                    <span>+</span> {{ __('New Department') }}
                </button>
            </div>

            <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(340px, 1fr)); gap: 20px;">
                @forelse($departments as $dept)
                    @php
                        $deptMembers = $members->filter(function($mem) use ($dept, $organization) {
                            $prof = $mem->user->profiles->where('organization_id', $organization->id)->first();
                            return $prof && $prof->department_id == $dept->id;
                        });
                    @endphp
                    <div class="card" style="margin-bottom: 0; display: flex; flex-direction: column; justify-content: space-between; border: 1px solid var(--border-color); box-shadow: var(--shadow-card); border-radius: var(--radius-xl); padding: 22px;">
                        <div>
                            <!-- Department Header -->
                            <div style="display: flex; align-items: flex-start; justify-content: space-between; margin-bottom: 14px;">
                                <div style="display: flex; align-items: center; gap: 12px;">
                                    <div class="kpi-icon-box" style="font-size: 22px;">
                                        🏛️
                                    </div>
                                    <div>
                                        <h3 style="font-size: 17px; font-weight: 900; color: var(--text-primary); margin-bottom: 2px;">{{ $dept->name }}</h3>
                                        <span style="font-size: 11px; color: var(--text-muted); font-weight: 700;">{{ $dept->teams->count() }} {{ __('Teams') }} • {{ $deptMembers->count() }} {{ __('Members') }}</span>
                                    </div>
                                </div>
                                <div style="display: flex; align-items: center; gap: 6px;">
                                    <button onclick="editDepartment('{{ $dept->id }}', '{{ addslashes($dept->name) }}')" class="tactile-btn btn-secondary" style="padding: 6px 10px; font-size: 12px;" title="{{ __('Edit Department') }}">✏️</button>
                                    <form action="{{ route('departments.delete', $dept->id) }}" method="POST" onsubmit="return confirm('{{ __('Are you sure you want to delete this department?') }}');" style="display: inline;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="tactile-btn" style="background: rgba(217, 107, 95, 0.15); color: #D96B5F; border: 1px solid rgba(217, 107, 95, 0.3); padding: 6px 10px; font-size: 12px;" title="{{ __('Delete Department') }}">🗑️</button>
                                    </form>
                                </div>
                            </div>

                            <!-- Sub-Teams Section -->
                            <div style="background: var(--bg-surface-subtle); border: 1px solid var(--border-color); border-radius: var(--radius-md); padding: 14px; margin-bottom: 14px; box-shadow: var(--shadow-inset-3d);">
                                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 10px;">
                                    <span style="font-size: 11px; font-weight: 800; color: var(--text-secondary); text-transform: uppercase;">{{ __('Sub-Teams') }}</span>
                                    <button onclick="openTeamModal('{{ $dept->id }}', '{{ addslashes($dept->name) }}')" style="background: none; border: none; font-size: 11px; font-weight: 800; color: var(--brand-forest); cursor: pointer;">+ {{ __('Add Team') }}</button>
                                </div>
                                <div style="display: flex; flex-wrap: wrap; gap: 6px;">
                                    @forelse($dept->teams as $t)
                                        <div style="background: var(--bg-surface); border: 1px solid var(--border-color); border-radius: 8px; padding: 5px 10px; font-size: 11px; font-weight: 800; color: var(--text-primary); display: flex; align-items: center; gap: 8px; box-shadow: var(--shadow-soft-3d);">
                                            <span>👥 {{ $t->name }}</span>
                                            <form action="{{ route('teams.delete', $t->id) }}" method="POST" onsubmit="return confirm('{{ __('Are you sure you want to delete this team?') }}');" style="display: inline; margin: 0;">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" style="background: none; border: none; color: var(--text-muted); cursor: pointer; font-size: 11px; padding: 0; line-height: 1;" title="{{ __('Delete Team') }}">✕</button>
                                            </form>
                                        </div>
                                    @empty
                                        <span style="font-size: 11px; color: var(--text-muted); font-style: italic;">{{ __('No sub-teams created yet.') }}</span>
                                    @endforelse
                                </div>
                            </div>

                            <!-- Assigned Department Members -->
                            <div>
                                <span style="font-size: 11px; font-weight: 800; color: var(--text-secondary); text-transform: uppercase; display: block; margin-bottom: 8px;">{{ __('Assigned Staff') }} ({{ $deptMembers->count() }})</span>
                                <div style="display: flex; flex-direction: column; gap: 6px;">
                                    @forelse($deptMembers->take(4) as $dm)
                                        @php
                                            $prof = $dm->user->profiles->where('organization_id', $organization->id)->first();
                                            $tObj = $teams->where('id', $prof?->team_id)->first();
                                        @endphp
                                        <div style="display: flex; align-items: center; justify-content: space-between; background: var(--bg-surface-subtle); border: 1px solid var(--border-color); padding: 8px 12px; border-radius: 10px;">
                                            <div style="display: flex; align-items: center; gap: 8px;">
                                                <div style="width: 26px; height: 26px; border-radius: 8px; background: var(--accent-gradient); color: white; display: flex; align-items: center; justify-content: center; font-size: 10px; font-weight: 800; box-shadow: var(--shadow-soft-3d);">
                                                    {{ strtoupper(substr($dm->user->name, 0, 2)) }}
                                                </div>
                                                <div>
                                                    <span style="font-size: 12px; font-weight: 800; color: var(--text-primary);">{{ $dm->user->name }}</span>
                                                    @if($prof?->job_title)
                                                        <span style="font-size: 10px; color: var(--text-muted);"> • {{ $prof->job_title }}</span>
                                                    @endif
                                                </div>
                                            </div>
                                            @if($tObj)
                                                <span class="nav-badge-pill" style="font-size: 10px;">{{ $tObj->name }}</span>
                                            @endif
                                        </div>
                                    @empty
                                        <div style="text-align: center; padding: 12px; font-size: 11px; color: var(--text-muted); background: var(--bg-surface-subtle); border: 1px dashed var(--border-color); border-radius: 10px;">
                                            {{ __('No members assigned to this department yet.') }}
                                        </div>
                                    @endforelse
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="card" style="grid-column: 1 / -1; text-align: center; color: var(--text-muted); padding: 40px; border-radius: var(--radius-xl);">
                        <div style="font-size: 36px; margin-bottom: 10px;">🏛️</div>
                        <h3 style="font-size: 18px; font-weight: 900; color: var(--text-primary); margin-bottom: 6px;">{{ __('No departments found') }}</h3>
                        <p style="font-size: 13px; color: var(--text-muted); margin-bottom: 18px;">{{ __('Create departments and divide your organization into structured functional teams.') }}</p>
                        <button onclick="openDepartmentModal()" class="tactile-btn btn-primary" style="padding: 10px 20px; font-size: 13px;">
                            <span>+</span> {{ __('New Department') }}
                        </button>
                    </div>
                @endforelse
            </div>
        </div>
        @endif

        <!-- 6. AUDIT LOGS TAB -->
        @if($membership->hasPermission('audit.view'))
        <div id="tab-audit" class="tab-view">
            <div class="page-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; flex-wrap: wrap; gap: 14px;">
                <div>
                    <h1 class="page-title" style="font-size: 22px; font-weight: 900; color: var(--text-primary); margin-bottom: 4px;">📋 {{ __('Audit Logs') }}</h1>
                    <p class="page-subtitle" style="font-size: 13px; color: var(--text-secondary);">{{ __('Track administrative actions and security events across the workplace.') }}</p>
                </div>
                <div>
                    @if($auditLogs->count() > 0)
                        <form method="POST" action="{{ route('audit_logs.clear') }}" onsubmit="return confirm('{{ __('Are you sure you want to purge all audit logs?') }}');" style="display: inline;">
                            @csrf
                            <button type="submit" class="tactile-btn" style="background: #D96B5F; color: white; padding: 10px 16px; font-size: 13px;">
                                <span>🗑️</span> {{ __('Clear All Logs') }}
                            </button>
                        </form>
                    @endif
                </div>
            </div>

            <div class="card" style="border-radius: var(--radius-lg); overflow: hidden; padding: 0;">
                <div style="padding: 20px 24px; border-bottom: 1px solid var(--border-color); display: flex; justify-content: space-between; align-items: center; background: var(--bg-surface);">
                    <h3 style="font-size: 16px; font-weight: 900; color: var(--text-primary);">🛡️ {{ __('Security Activity Trail') }} ({{ $auditLogs->count() }})</h3>
                </div>
                <div style="overflow-x: auto;">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>{{ __('Action') }}</th>
                                <th>{{ __('Target') }}</th>
                                <th>{{ __('User') }}</th>
                                <th>{{ __('IP Address') }}</th>
                                <th>{{ __('Timestamp') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($auditLogs as $log)
                                <tr>
                                    <td><span class="nav-badge-pill" style="background: rgba(79, 155, 95, 0.15); color: #4F9B5F;">{{ $log->action }}</span></td>
                                    <td style="font-weight: 700; color: var(--text-primary);">{{ class_basename($log->auditable_type) }}</td>
                                    <td style="font-family: monospace; font-size: 12px;">{{ substr($log->user_id ?? 'System', 0, 8) }}</td>
                                    <td style="font-family: monospace; font-size: 12px; color: var(--text-muted);">{{ $log->ip_address ?? '127.0.0.1' }}</td>
                                    <td style="font-size: 12px; color: var(--text-muted); font-family: monospace;">{{ $log->created_at->format('Y-m-d H:i:s') }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" style="text-align: center; color: var(--text-muted); padding: 40px;">
                                        {{ __('Audit trail is clean and recorded.') }}
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        @endif

        <!-- 7. SETTINGS TAB -->
        @if($membership->hasPermission('organizations.manage'))
        <div id="tab-settings" class="tab-view">
            <div class="page-header" style="margin-bottom: 24px;">
                <h1 class="page-title" style="font-size: 22px; font-weight: 900; color: var(--text-primary); margin-bottom: 4px;">⚙️ {{ __('Workspace Settings') }}</h1>
                <p class="page-subtitle" style="font-size: 13px; color: var(--text-secondary);">{{ __('Configure organization branding, company logo, and workspace localization.') }}</p>
            </div>

            @if(session('success'))
                <div style="background: rgba(79, 155, 95, 0.15); border: 1px solid rgba(79, 155, 95, 0.35); color: #4F9B5F; padding: 14px 18px; border-radius: var(--radius-md); margin-bottom: 20px; font-size: 13px; font-weight: 800; display: flex; align-items: center; gap: 10px;">
                    <span>✅</span> {{ session('success') }}
                </div>
            @endif

            @if($errors->any())
                <div style="background: rgba(217, 107, 95, 0.15); border: 1px solid rgba(217, 107, 95, 0.35); color: #D96B5F; padding: 14px 18px; border-radius: var(--radius-md); margin-bottom: 20px; font-size: 13px; font-weight: 800;">
                    <ul style="margin: 0; padding-inline-start: 20px;">
                        @foreach($errors->all() as $err)
                            <li>{{ $err }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="card" style="max-width: 680px; border-radius: var(--radius-xl); padding: 24px;">
                <form method="POST" action="{{ route('organization.settings.update') }}" enctype="multipart/form-data" style="display: flex; flex-direction: column; gap: 20px;">
                    @csrf

                    <!-- 1. Company Logo Upload -->
                    <div>
                        <label style="display: block; font-size: 11px; font-weight: 800; color: var(--text-secondary); margin-bottom: 8px; text-transform: uppercase;">
                            🖼️ {{ __('Company Logo / Workspace Icon') }}
                        </label>
                        <div style="display: flex; align-items: center; gap: 18px; background: var(--bg-surface-subtle); padding: 16px; border-radius: var(--radius-md); border: 1px solid var(--border-color); box-shadow: var(--shadow-inset-3d);">
                            <div style="width: 64px; height: 64px; border-radius: 16px; background: var(--bg-surface); border: 2px dashed var(--border-color); display: flex; align-items: center; justify-content: center; overflow: hidden; flex-shrink: 0; position: relative; box-shadow: var(--shadow-soft-3d);">
                                <img id="logo-preview-img" src="{{ $organization->logo_url ? $organization->logo_url : '' }}" alt="Logo" style="width: 100%; height: 100%; object-fit: cover; {{ $organization->logo_url ? '' : 'display: none;' }}">
                                <div id="logo-preview-placeholder" style="font-size: 28px; {{ $organization->logo_url ? 'display: none;' : '' }}">🏢</div>
                            </div>
                            <div style="flex: 1;">
                                <div style="font-size: 13px; font-weight: 800; color: var(--text-primary); margin-bottom: 4px;">{{ __('Upload Logo Image') }}</div>
                                <div style="font-size: 11px; color: var(--text-muted); margin-bottom: 10px;">{{ __('Appears in the top sidebar beside the company name. Recommended: PNG, JPG, SVG or WebP up to 4MB.') }}</div>
                                <input type="file" name="logo" id="org-logo-input" accept="image/*" onchange="previewCompanyLogo(this)" style="font-size: 12px; color: var(--text-secondary);">
                            </div>
                        </div>
                    </div>

                    <!-- 2. Workspace Name -->
                    <div>
                        <label style="display: block; font-size: 11px; font-weight: 800; color: var(--text-secondary); margin-bottom: 6px; text-transform: uppercase;">
                            🏢 {{ __('Workspace / Company Name') }}
                        </label>
                        <input type="text" name="name" required value="{{ old('name', $organization->name) }}" placeholder="e.g. Acme Corp" style="width: 100%; background: var(--bg-surface-subtle); border: 1px solid var(--border-color); border-radius: 10px; padding: 10px 14px; color: var(--text-primary); font-size: 13px; font-weight: 600; box-shadow: var(--shadow-inset-3d);">
                    </div>

                    <!-- 3. Workspace Slug (Read-only) -->
                    <div>
                        <label style="display: block; font-size: 11px; font-weight: 800; color: var(--text-secondary); margin-bottom: 6px; text-transform: uppercase;">
                            🔗 {{ __('Workspace URL Slug') }}
                        </label>
                        <input type="text" value="{{ $organization->slug }}" readonly style="width: 100%; background: var(--bg-surface-subtle); border: 1px solid var(--border-color); border-radius: 10px; padding: 10px 14px; color: var(--brand-forest); font-size: 13px; font-family: monospace; font-weight: 700; box-shadow: var(--shadow-inset-3d);">
                        <span style="display: block; font-size: 10px; color: var(--text-muted); margin-top: 4px;">{{ __('Used for organization identification across the workspace.') }}</span>
                    </div>

                    <!-- 4. Timezone -->
                    <div>
                        <label style="display: block; font-size: 11px; font-weight: 800; color: var(--text-secondary); margin-bottom: 6px; text-transform: uppercase;">
                            🌍 {{ __('Timezone') }}
                        </label>
                        <select name="timezone" style="width: 100%; background: var(--bg-surface-subtle); border: 1px solid var(--border-color); border-radius: 10px; padding: 10px 14px; color: var(--text-primary); font-size: 13px; font-weight: 600; box-shadow: var(--shadow-inset-3d);">
                            @php
                                $commonTimezones = [
                                    'UTC' => 'UTC (Coordinated Universal Time)',
                                    'Africa/Cairo' => 'Africa/Cairo (EET / EEST)',
                                    'Asia/Riyadh' => 'Asia/Riyadh (AST)',
                                    'Asia/Dubai' => 'Asia/Dubai (GST)',
                                    'Europe/London' => 'Europe/London (GMT / BST)',
                                    'Europe/Paris' => 'Europe/Paris (CET / CEST)',
                                    'America/New_York' => 'America/New_York (EST / EDT)',
                                    'America/Chicago' => 'America/Chicago (CST / CDT)',
                                    'America/Los_Angeles' => 'America/Los_Angeles (PST / PDT)',
                                    'Asia/Singapore' => 'Asia/Singapore (SGT)',
                                    'Asia/Tokyo' => 'Asia/Tokyo (JST)',
                                ];
                            @endphp
                            @foreach($commonTimezones as $tzKey => $tzLabel)
                                <option value="{{ $tzKey }}" {{ $organization->timezone === $tzKey ? 'selected' : '' }}>{{ $tzLabel }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- 5. SMTP Mail Server Configuration -->
                    <div style="border-top: 1px solid var(--border-color); padding-top: 20px; margin-top: 10px;">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 14px;">
                            <div>
                                <h3 style="font-size: 15px; font-weight: 900; color: var(--text-primary); margin: 0 0 2px 0;">
                                    ✉️ {{ __('Outgoing SMTP Email Settings') }}
                                </h3>
                                <p style="font-size: 11px; color: var(--text-muted); margin: 0;">
                                    {{ __('Configure the custom mail server used to send meeting invitations and workplace alerts to your team.') }}
                                </p>
                            </div>
                        </div>

                        <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 12px; margin-bottom: 12px;">
                            <div>
                                <label style="display: block; font-size: 11px; font-weight: 800; color: var(--text-secondary); margin-bottom: 4px; text-transform: uppercase;">
                                    {{ __('SMTP Host / Server') }}
                                </label>
                                <input type="text" name="mail_host" id="smtp-host-input" value="{{ old('mail_host', $smtpSettings['mail_host'] ?? '') }}" placeholder="e.g. smtp.gmail.com or smtp.mailgun.org" style="width: 100%; background: var(--bg-surface-subtle); border: 1px solid var(--border-color); border-radius: 10px; padding: 10px 14px; color: var(--text-primary); font-size: 13px; font-weight: 600; box-shadow: var(--shadow-inset-3d);">
                            </div>
                            <div>
                                <label style="display: block; font-size: 11px; font-weight: 800; color: var(--text-secondary); margin-bottom: 4px; text-transform: uppercase;">
                                    {{ __('Port') }}
                                </label>
                                <input type="number" name="mail_port" id="smtp-port-input" value="{{ old('mail_port', $smtpSettings['mail_port'] ?? '587') }}" placeholder="587" style="width: 100%; background: var(--bg-surface-subtle); border: 1px solid var(--border-color); border-radius: 10px; padding: 10px 14px; color: var(--text-primary); font-size: 13px; font-weight: 600; box-shadow: var(--shadow-inset-3d);">
                            </div>
                        </div>

                        <div style="display: grid; grid-template-columns: 1.2fr 1.2fr 0.8fr; gap: 12px; margin-bottom: 12px;">
                            <div>
                                <label style="display: block; font-size: 11px; font-weight: 800; color: var(--text-secondary); margin-bottom: 4px; text-transform: uppercase;">
                                    {{ __('SMTP Username') }}
                                </label>
                                <input type="text" name="mail_username" id="smtp-username-input" value="{{ old('mail_username', $smtpSettings['mail_username'] ?? '') }}" placeholder="api / user@domain.com" style="width: 100%; background: var(--bg-surface-subtle); border: 1px solid var(--border-color); border-radius: 10px; padding: 10px 14px; color: var(--text-primary); font-size: 13px; font-weight: 600; box-shadow: var(--shadow-inset-3d);">
                            </div>
                            <div>
                                <label style="display: block; font-size: 11px; font-weight: 800; color: var(--text-secondary); margin-bottom: 4px; text-transform: uppercase;">
                                    {{ __('SMTP Password') }}
                                </label>
                                <input type="password" name="mail_password" id="smtp-password-input" placeholder="{{ !empty($smtpSettings['mail_password']) ? '••••••••••••' : 'App Password / Secret' }}" style="width: 100%; background: var(--bg-surface-subtle); border: 1px solid var(--border-color); border-radius: 10px; padding: 10px 14px; color: var(--text-primary); font-size: 13px; font-weight: 600; box-shadow: var(--shadow-inset-3d);">
                            </div>
                            <div>
                                <label style="display: block; font-size: 11px; font-weight: 800; color: var(--text-secondary); margin-bottom: 4px; text-transform: uppercase;">
                                    {{ __('Encryption') }}
                                </label>
                                <select name="mail_encryption" id="smtp-encryption-input" style="width: 100%; background: var(--bg-surface-subtle); border: 1px solid var(--border-color); border-radius: 10px; padding: 10px 14px; color: var(--text-primary); font-size: 13px; font-weight: 600; box-shadow: var(--shadow-inset-3d);">
                                    <option value="tls" {{ ($smtpSettings['mail_encryption'] ?? 'tls') === 'tls' ? 'selected' : '' }}>TLS</option>
                                    <option value="ssl" {{ ($smtpSettings['mail_encryption'] ?? '') === 'ssl' ? 'selected' : '' }}>SSL</option>
                                    <option value="none" {{ ($smtpSettings['mail_encryption'] ?? '') === 'none' ? 'selected' : '' }}>None</option>
                                </select>
                            </div>
                        </div>

                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 16px;">
                            <div>
                                <label style="display: block; font-size: 11px; font-weight: 800; color: var(--text-secondary); margin-bottom: 4px; text-transform: uppercase;">
                                    {{ __('Sender Email (From)') }}
                                </label>
                                <input type="email" name="mail_from_address" id="smtp-from-email-input" value="{{ old('mail_from_address', $smtpSettings['mail_from_address'] ?? 'noreply@' . $organization->slug . '.com') }}" placeholder="noreply@domain.com" style="width: 100%; background: var(--bg-surface-subtle); border: 1px solid var(--border-color); border-radius: 10px; padding: 10px 14px; color: var(--text-primary); font-size: 13px; font-weight: 600; box-shadow: var(--shadow-inset-3d);">
                            </div>
                            <div>
                                <label style="display: block; font-size: 11px; font-weight: 800; color: var(--text-secondary); margin-bottom: 4px; text-transform: uppercase;">
                                    {{ __('Sender Display Name') }}
                                </label>
                                <input type="text" name="mail_from_name" id="smtp-from-name-input" value="{{ old('mail_from_name', $smtpSettings['mail_from_name'] ?? $organization->name) }}" placeholder="{{ $organization->name }}" style="width: 100%; background: var(--bg-surface-subtle); border: 1px solid var(--border-color); border-radius: 10px; padding: 10px 14px; color: var(--text-primary); font-size: 13px; font-weight: 600; box-shadow: var(--shadow-inset-3d);">
                            </div>
                        </div>

                        <!-- Test SMTP Button & Live Result Box -->
                        <div style="display: flex; align-items: center; justify-content: space-between; gap: 12px; background: var(--bg-surface-subtle); border: 1px solid var(--border-color); border-radius: 12px; padding: 12px 16px; flex-wrap: wrap;">
                            <div style="font-size: 12px; color: var(--text-secondary);">
                                <span>📧 {{ __('Send a test email to') }} <strong>{{ $user->email }}</strong></span>
                            </div>
                            <button type="button" onclick="testSmtpConnectionAction()" id="btn-test-smtp" class="tactile-btn btn-secondary" style="padding: 8px 16px; font-size: 12px;">
                                🧪 {{ __('Test SMTP Connection') }}
                            </button>
                        </div>
                        <div id="smtp-test-result-box" style="display: none; margin-top: 10px; padding: 10px 14px; border-radius: 10px; font-size: 12px; font-weight: 800;"></div>
                    </div>

                    <!-- 6. OpenAI & AI Virtual Office Generator Settings -->
                    <div style="border-top: 1px solid var(--border-color); padding-top: 20px; margin-top: 10px;">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 14px;">
                            <div>
                                <h3 style="font-size: 15px; font-weight: 900; color: var(--text-primary); margin: 0 0 2px 0; display: flex; align-items: center; gap: 8px;">
                                    <span>🤖</span>
                                    <span>{{ __('OpenAI & AI Floorplan Generator Settings') }}</span>
                                </h3>
                                <p style="font-size: 11px; color: var(--text-muted); margin: 0;">
                                    {{ __('Add your company OpenAI API key to generate bespoke 2D architectural office blueprints directly from the editor without platform rate limits.') }}
                                </p>
                            </div>
                        </div>

                        <!-- Cost Optimization Notice -->
                        <div style="background: rgba(16, 185, 129, 0.08); border: 1px solid rgba(16, 185, 129, 0.25); border-radius: 12px; padding: 12px 14px; margin-bottom: 16px; font-size: 12px; line-height: 1.5; color: var(--text-primary);">
                            <div style="font-weight: 800; color: #10B981; margin-bottom: 4px; display: flex; align-items: center; gap: 6px;">
                                <span>💡</span>
                                <span>{{ __('Token & Cost Optimization Enabled') }}</span>
                            </div>
                            <span style="color: var(--text-secondary); font-size: 11px;">
                                {{ __('Prompts are ultra-compressed to ~60 tokens. Choosing GPT Image 1 Mini or DALL-E 2 with 1024x1024 reduces your cost to approx $0.015 - $0.02 per generated floorplan.') }}
                            </span>
                        </div>

                        <!-- API Key Input -->
                        <div style="margin-bottom: 14px;">
                            <label style="display: block; font-size: 11px; font-weight: 800; color: var(--text-secondary); margin-bottom: 4px; text-transform: uppercase;">
                                🔑 {{ __('Company OpenAI Secret Key (sk-...)') }}
                            </label>
                            <div style="display: flex; gap: 8px;">
                                <input type="password" name="openai_api_key" id="org-openai-key-input" placeholder="{{ !empty($openAiSettings['api_key']) ? '••••••••••••••••••••••••••••••••' : 'sk-proj-... / sk-svcacct-...' }}" style="flex: 1; background: var(--bg-surface-subtle); border: 1px solid var(--border-color); border-radius: 10px; padding: 10px 14px; color: var(--text-primary); font-size: 13px; font-family: monospace; font-weight: 600; box-shadow: var(--shadow-inset-3d);">
                                <button type="button" onclick="testOrgAiConnectionAction()" id="btn-test-org-ai" class="tactile-btn btn-secondary" style="padding: 0 16px; font-size: 12px; white-space: nowrap;">
                                    ⚡ {{ __('Test Key') }}
                                </button>
                            </div>
                            <div id="org-ai-test-result-box" style="display: none; margin-top: 8px; padding: 8px 12px; border-radius: 8px; font-size: 12px; font-weight: 800;"></div>
                        </div>

                        <!-- Generation Model & Image Dimensions -->
                        <div style="display: grid; grid-template-columns: 1.4fr 1fr; gap: 12px; margin-bottom: 16px;">
                            <div>
                                <label style="display: block; font-size: 11px; font-weight: 800; color: var(--text-secondary); margin-bottom: 4px; text-transform: uppercase;">
                                    🖼️ {{ __('Image Generation Model') }}
                                </label>
                                <select name="openai_model" style="width: 100%; background: var(--bg-surface-subtle); border: 1px solid var(--border-color); border-radius: 10px; padding: 10px 14px; color: var(--text-primary); font-size: 12px; font-weight: 700; box-shadow: var(--shadow-inset-3d);">
                                    <option value="gpt-image-1-mini" {{ ($openAiSettings['model'] ?? 'gpt-image-1-mini') === 'gpt-image-1-mini' ? 'selected' : '' }}>
                                        GPT Image 1 Mini (💰 Ultra Low Cost ~$0.015)
                                    </option>
                                    <option value="gpt-image-1" {{ ($openAiSettings['model'] ?? '') === 'gpt-image-1' ? 'selected' : '' }}>
                                        GPT Image 1 (⚡ High Quality Standard ~$0.040)
                                    </option>
                                    <option value="dall-e-2" {{ ($openAiSettings['model'] ?? '') === 'dall-e-2' ? 'selected' : '' }}>
                                        DALL-E 2 (💵 Economy Legacy ~$0.020)
                                    </option>
                                    <option value="dall-e-3" {{ ($openAiSettings['model'] ?? '') === 'dall-e-3' ? 'selected' : '' }}>
                                        DALL-E 3 (🎨 High Definition Art ~$0.080)
                                    </option>
                                </select>
                            </div>
                            <div>
                                <label style="display: block; font-size: 11px; font-weight: 800; color: var(--text-secondary); margin-bottom: 4px; text-transform: uppercase;">
                                    📐 {{ __('Floorplan Dimensions') }}
                                </label>
                                <select name="openai_image_size" style="width: 100%; background: var(--bg-surface-subtle); border: 1px solid var(--border-color); border-radius: 10px; padding: 10px 14px; color: var(--text-primary); font-size: 12px; font-weight: 700; box-shadow: var(--shadow-inset-3d);">
                                    <option value="1024x1024" {{ ($openAiSettings['image_size'] ?? '1024x1024') === '1024x1024' ? 'selected' : '' }}>
                                        1024 × 1024 (Square 1:1 - Low Cost)
                                    </option>
                                    <option value="1792x1024" {{ ($openAiSettings['image_size'] ?? '') === '1792x1024' ? 'selected' : '' }}>
                                        1792 × 1024 (Widescreen 16:9)
                                    </option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div style="padding-top: 10px; border-top: 1px solid var(--border-color);">
                        <button type="submit" class="tactile-btn btn-primary" style="padding: 12px 24px; font-size: 13px; cursor: pointer;">
                            💾 {{ __('Save Workspace & Settings Changes') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
        @endif

        <!-- 7.5 USER PROFILE TAB -->
        <div id="tab-profile" class="tab-view">
            <div class="page-header" style="margin-bottom: 24px;">
                <h1 class="page-title" style="font-size: 22px; font-weight: 900; color: var(--text-primary); margin-bottom: 4px;">👤 {{ __('User Profile & Account') }}</h1>
                <p class="page-subtitle" style="font-size: 13px; color: var(--text-secondary);">{{ __('Manage your digital identity, avatar, contact details, skills, social links, and security.') }}</p>
            </div>

            <!-- Profile Hero Card (3D Soft Neumorphic) -->
            <div class="card" style="margin-bottom: 24px; border-radius: var(--radius-xl); padding: 24px; border: 1px solid var(--border-color); box-shadow: var(--shadow-card); background: var(--bg-surface); position: relative; overflow: hidden;">
                <div style="display: flex; align-items: center; gap: 24px; flex-wrap: wrap;">
                    <!-- Avatar with Upload Overlay -->
                    <div style="position: relative; width: 88px; height: 88px; border-radius: 22px; overflow: hidden; box-shadow: var(--shadow-card); border: 2px solid var(--brand-forest); background: var(--accent-gradient); flex-shrink: 0;">
                        <img id="user-profile-preview-avatar" src="{{ $user->avatar_url ? $user->avatar_url : '' }}" alt="{{ $user->name }}" style="width: 100%; height: 100%; object-fit: cover; {{ $user->avatar_url ? '' : 'display: none;' }}">
                        <div id="user-profile-avatar-fallback" style="width: 100%; height: 100%; display: {{ $user->avatar_url ? 'none' : 'flex' }}; align-items: center; justify-content: center; font-size: 30px; font-weight: 900; color: white;">
                            {{ strtoupper(substr($user->name, 0, 2)) }}
                        </div>
                    </div>

                    <!-- User Identity Details -->
                    <div style="flex: 1; min-width: 220px;">
                        <div style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap;">
                            <h2 style="font-size: 24px; font-weight: 900; color: var(--text-primary); margin: 0;">{{ $user->name }}</h2>
                            @if($user->nickname)
                                <span class="nav-badge-pill" style="font-size: 12px; font-family: monospace;">{{ '@' . $user->nickname }}</span>
                            @endif
                            <span class="nav-badge-pill" style="background: rgba(79, 155, 95, 0.15); color: #4F9B5F; font-size: 11px;">{{ $membership->role->name ?? __('Member') }}</span>
                        </div>
                        <div style="font-size: 13px; color: var(--text-secondary); margin-top: 6px; display: flex; align-items: center; gap: 12px; flex-wrap: wrap;">
                            <span>💼 {{ $myProfile->job_title ?? __('Workspace Member') }}</span>
                            <span>•</span>
                            <span>🏢 {{ $organization->name }}</span>
                            <span>•</span>
                            <span>✉️ {{ $user->email }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Profile Form (Personal, Professional, Hobbies, Skills, Social Links, Notes) -->
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(360px, 1fr)); gap: 24px;">

                <!-- Left Column: Personal, Contact & Work Details -->
                <div class="card" style="margin-bottom: 0; border-radius: var(--radius-xl); padding: 24px;">
                    <h3 style="font-size: 16px; font-weight: 900; color: var(--text-primary); margin-bottom: 18px; display: flex; align-items: center; gap: 8px;">
                        <span>👤</span> {{ __('Personal & Professional Info') }}
                    </h3>

                    <form method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data" style="display: flex; flex-direction: column; gap: 16px;">
                        @csrf

                        <!-- Profile Photo Upload -->
                        <div>
                            <label style="display: block; font-size: 11px; font-weight: 800; color: var(--text-secondary); margin-bottom: 6px; text-transform: uppercase;">
                                📷 {{ __('Change Profile Photo') }}
                            </label>
                            <input type="file" name="avatar" accept="image/*" onchange="previewUserAvatar(this)" style="font-size: 12px; color: var(--text-secondary); width: 100%; background: var(--bg-surface-subtle); padding: 10px; border-radius: 10px; border: 1px solid var(--border-color); box-shadow: var(--shadow-inset-3d);">
                            <span style="font-size: 10px; color: var(--text-muted); display: block; margin-top: 4px;">{{ __('JPEG, PNG, WebP up to 4MB.') }}</span>
                        </div>

                        <!-- Full Name -->
                        <div>
                            <label style="display: block; font-size: 11px; font-weight: 800; color: var(--text-secondary); margin-bottom: 6px; text-transform: uppercase;">
                                {{ __('Full Name') }} <span style="color: #D96B5F;">*</span>
                            </label>
                            <input type="text" name="name" required value="{{ old('name', $user->name) }}" style="width: 100%; background: var(--bg-surface-subtle); border: 1px solid var(--border-color); border-radius: 10px; padding: 10px 14px; color: var(--text-primary); font-size: 13px; font-weight: 600; box-shadow: var(--shadow-inset-3d);">
                        </div>

                        <!-- Nickname -->
                        <div>
                            <label style="display: block; font-size: 11px; font-weight: 800; color: var(--text-secondary); margin-bottom: 6px; text-transform: uppercase;">
                                🏷️ {{ __('Nickname / Display Handle') }}
                            </label>
                            <input type="text" name="nickname" value="{{ old('nickname', $user->nickname) }}" placeholder="e.g. sparky, alex_dev" style="width: 100%; background: var(--bg-surface-subtle); border: 1px solid var(--border-color); border-radius: 10px; padding: 10px 14px; color: var(--brand-forest); font-size: 13px; font-weight: 700; font-family: monospace; box-shadow: var(--shadow-inset-3d);">
                        </div>

                        <!-- Email -->
                        <div>
                            <label style="display: block; font-size: 11px; font-weight: 800; color: var(--text-secondary); margin-bottom: 6px; text-transform: uppercase;">
                                ✉️ {{ __('Email Address') }} <span style="color: #D96B5F;">*</span>
                            </label>
                            <input type="email" name="email" required value="{{ old('email', $user->email) }}" style="width: 100%; background: var(--bg-surface-subtle); border: 1px solid var(--border-color); border-radius: 10px; padding: 10px 14px; color: var(--text-primary); font-size: 13px; font-weight: 600; box-shadow: var(--shadow-inset-3d);">
                        </div>

                        <!-- Date of Birth & Phone in 2 Columns -->
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                            <div>
                                <label style="display: block; font-size: 11px; font-weight: 800; color: var(--text-secondary); margin-bottom: 6px; text-transform: uppercase;">
                                    🎂 {{ __('Date of Birth') }}
                                </label>
                                <input type="date" name="date_of_birth" value="{{ old('date_of_birth', $myProfile->date_of_birth ? $myProfile->date_of_birth->format('Y-m-d') : '') }}" style="width: 100%; background: var(--bg-surface-subtle); border: 1px solid var(--border-color); border-radius: 10px; padding: 10px 14px; color: var(--text-primary); font-size: 13px; font-weight: 600; box-shadow: var(--shadow-inset-3d);">
                            </div>
                            <div>
                                <label style="display: block; font-size: 11px; font-weight: 800; color: var(--text-secondary); margin-bottom: 6px; text-transform: uppercase;">
                                    📱 {{ __('Phone Number') }}
                                </label>
                                <input type="text" name="phone" value="{{ old('phone', $myProfile->phone) }}" placeholder="+966 50 123 4567" style="width: 100%; background: var(--bg-surface-subtle); border: 1px solid var(--border-color); border-radius: 10px; padding: 10px 14px; color: var(--text-primary); font-size: 13px; font-weight: 600; box-shadow: var(--shadow-inset-3d);">
                            </div>
                        </div>

                        <!-- Job Title & Work Mode -->
                        <div style="display: grid; grid-template-columns: 1.4fr 1fr; gap: 12px;">
                            <div>
                                <label style="display: block; font-size: 11px; font-weight: 800; color: var(--text-secondary); margin-bottom: 6px; text-transform: uppercase;">
                                    💼 {{ __('Job Title') }}
                                </label>
                                <input type="text" name="job_title" value="{{ old('job_title', $myProfile->job_title) }}" placeholder="e.g. Senior Fullstack Engineer" style="width: 100%; background: var(--bg-surface-subtle); border: 1px solid var(--border-color); border-radius: 10px; padding: 10px 14px; color: var(--text-primary); font-size: 13px; font-weight: 600; box-shadow: var(--shadow-inset-3d);">
                            </div>
                            <div>
                                <label style="display: block; font-size: 11px; font-weight: 800; color: var(--text-secondary); margin-bottom: 6px; text-transform: uppercase;">
                                    🏢 {{ __('Work Mode') }}
                                </label>
                                <select name="work_mode" style="width: 100%; background: var(--bg-surface-subtle); border: 1px solid var(--border-color); border-radius: 10px; padding: 10px 14px; color: var(--text-primary); font-size: 13px; font-weight: 600; box-shadow: var(--shadow-inset-3d);">
                                    <option value="remote" {{ ($myProfile->work_mode ?? 'remote') === 'remote' ? 'selected' : '' }}>🏠 Remote</option>
                                    <option value="hybrid" {{ ($myProfile->work_mode ?? '') === 'hybrid' ? 'selected' : '' }}>🔄 Hybrid</option>
                                    <option value="onsite" {{ ($myProfile->work_mode ?? '') === 'onsite' ? 'selected' : '' }}>🏢 On-site</option>
                                </select>
                            </div>
                        </div>

                        <!-- Bio -->
                        <div>
                            <label style="display: block; font-size: 11px; font-weight: 800; color: var(--text-secondary); margin-bottom: 6px; text-transform: uppercase;">
                                📝 {{ __('Bio / About Me') }}
                            </label>
                            <textarea name="bio" rows="3" placeholder="{{ __('Tell the team about yourself, your background, and what you love working on...') }}" style="width: 100%; background: var(--bg-surface-subtle); border: 1px solid var(--border-color); border-radius: 10px; padding: 10px 14px; color: var(--text-primary); font-size: 13px; font-weight: 500; resize: vertical; box-shadow: var(--shadow-inset-3d);">{{ old('bio', $myProfile->bio) }}</textarea>
                        </div>

                        <div style="padding-top: 10px; border-top: 1px solid var(--border-color);">
                            <button type="submit" class="tactile-btn btn-primary" style="padding: 12px 24px; font-size: 13px; cursor: pointer;">
                                💾 {{ __('Save Profile Details') }}
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Right Column: Hobbies, Skills, Social Links & Security -->
                <div style="display: flex; flex-direction: column; gap: 24px;">

                    <!-- Hobbies, Skills & Social Links Card -->
                    <div class="card" style="margin-bottom: 0; border-radius: var(--radius-xl); padding: 24px;">
                        <h3 style="font-size: 16px; font-weight: 900; color: var(--text-primary); margin-bottom: 18px; display: flex; align-items: center; gap: 8px;">
                            <span>🌟</span> {{ __('Hobbies, Skills & Social Links') }}
                        </h3>

                        <form method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data" style="display: flex; flex-direction: column; gap: 16px;">
                            @csrf
                            <input type="hidden" name="name" value="{{ $user->name }}">
                            <input type="hidden" name="email" value="{{ $user->email }}">
                            <input type="hidden" name="nickname" value="{{ $user->nickname }}">
                            <input type="hidden" name="phone" value="{{ $myProfile->phone }}">
                            <input type="hidden" name="job_title" value="{{ $myProfile->job_title }}">
                            <input type="hidden" name="work_mode" value="{{ $myProfile->work_mode }}">
                            <input type="hidden" name="bio" value="{{ $myProfile->bio }}">
                            <input type="hidden" name="date_of_birth" value="{{ $myProfile->date_of_birth ? $myProfile->date_of_birth->format('Y-m-d') : '' }}">

                            <!-- Hobbies -->
                            <div>
                                <label style="display: block; font-size: 11px; font-weight: 800; color: var(--text-secondary); margin-bottom: 6px; text-transform: uppercase;">
                                    🎯 {{ __('Hobbies & Interests') }}
                                </label>
                                <input type="text" name="hobbies" value="{{ old('hobbies', $myProfile->hobbies) }}" placeholder="e.g. Chess, Reading, Video Games, Football, Travel" style="width: 100%; background: var(--bg-surface-subtle); border: 1px solid var(--border-color); border-radius: 10px; padding: 10px 14px; color: var(--text-primary); font-size: 13px; font-weight: 600; box-shadow: var(--shadow-inset-3d);">
                            </div>

                            <!-- Skills -->
                            <div>
                                <label style="display: block; font-size: 11px; font-weight: 800; color: var(--text-secondary); margin-bottom: 6px; text-transform: uppercase;">
                                    ⚡ {{ __('Skills & Expertise') }}
                                </label>
                                <input type="text" name="skills" value="{{ old('skills', $myProfile->skills) }}" placeholder="e.g. Laravel, PHP, Vue.js, Architecture, UI/UX, Docker" style="width: 100%; background: var(--bg-surface-subtle); border: 1px solid var(--border-color); border-radius: 10px; padding: 10px 14px; color: var(--text-primary); font-size: 13px; font-weight: 600; box-shadow: var(--shadow-inset-3d);">
                            </div>

                            <!-- Social Media Links -->
                            @php
                                $social = (array)($myProfile->social_links ?? []);
                            @endphp
                            <div>
                                <label style="display: block; font-size: 11px; font-weight: 800; color: var(--text-secondary); margin-bottom: 6px; text-transform: uppercase;">
                                    🌐 {{ __('Social Media & Portfolio Links') }}
                                </label>
                                <div style="display: flex; flex-direction: column; gap: 10px;">
                                    <div style="display: flex; align-items: center; gap: 8px;">
                                        <span style="width: 80px; font-size: 12px; font-weight: 800; color: var(--text-secondary);">LinkedIn</span>
                                        <input type="url" name="linkedin" value="{{ old('linkedin', $social['linkedin'] ?? '') }}" placeholder="https://linkedin.com/in/username" style="flex: 1; background: var(--bg-surface-subtle); border: 1px solid var(--border-color); border-radius: 10px; padding: 8px 12px; color: var(--text-primary); font-size: 12px; box-shadow: var(--shadow-inset-3d);">
                                    </div>
                                    <div style="display: flex; align-items: center; gap: 8px;">
                                        <span style="width: 80px; font-size: 12px; font-weight: 800; color: var(--text-secondary);">GitHub</span>
                                        <input type="url" name="github" value="{{ old('github', $social['github'] ?? '') }}" placeholder="https://github.com/username" style="flex: 1; background: var(--bg-surface-subtle); border: 1px solid var(--border-color); border-radius: 10px; padding: 8px 12px; color: var(--text-primary); font-size: 12px; box-shadow: var(--shadow-inset-3d);">
                                    </div>
                                    <div style="display: flex; align-items: center; gap: 8px;">
                                        <span style="width: 80px; font-size: 12px; font-weight: 800; color: var(--text-secondary);">X (Twitter)</span>
                                        <input type="url" name="twitter" value="{{ old('twitter', $social['twitter'] ?? '') }}" placeholder="https://x.com/username" style="flex: 1; background: var(--bg-surface-subtle); border: 1px solid var(--border-color); border-radius: 10px; padding: 8px 12px; color: var(--text-primary); font-size: 12px; box-shadow: var(--shadow-inset-3d);">
                                    </div>
                                    <div style="display: flex; align-items: center; gap: 8px;">
                                        <span style="width: 80px; font-size: 12px; font-weight: 800; color: var(--text-secondary);">Website</span>
                                        <input type="url" name="website" value="{{ old('website', $social['website'] ?? '') }}" placeholder="https://mywebsite.com" style="flex: 1; background: var(--bg-surface-subtle); border: 1px solid var(--border-color); border-radius: 10px; padding: 8px 12px; color: var(--text-primary); font-size: 12px; box-shadow: var(--shadow-inset-3d);">
                                    </div>
                                </div>
                            </div>

                            <!-- Personal Work Notes -->
                            <div>
                                <label style="display: block; font-size: 11px; font-weight: 800; color: var(--text-secondary); margin-bottom: 6px; text-transform: uppercase;">
                                    📌 {{ __('Work Notes & Preferences') }}
                                </label>
                                <textarea name="notes" rows="3" placeholder="{{ __('Any personal work notes, focus time rules, or reminders...') }}" style="width: 100%; background: var(--bg-surface-subtle); border: 1px solid var(--border-color); border-radius: 10px; padding: 10px 14px; color: var(--text-primary); font-size: 13px; font-weight: 500; resize: vertical; box-shadow: var(--shadow-inset-3d);">{{ old('notes', $myProfile->notes) }}</textarea>
                            </div>

                            <div style="padding-top: 10px; border-top: 1px solid var(--border-color);">
                                <button type="submit" class="tactile-btn btn-primary" style="padding: 12px 24px; font-size: 13px; cursor: pointer;">
                                    💾 {{ __('Save Hobbies, Skills & Social') }}
                                </button>
                            </div>
                        </form>
                    </div>

                    <!-- Security & Password Change Card -->
                    <div class="card" style="margin-bottom: 0; border-radius: var(--radius-xl); padding: 24px;">
                        <h3 style="font-size: 16px; font-weight: 900; color: var(--text-primary); margin-bottom: 18px; display: flex; align-items: center; gap: 8px;">
                            <span>🔒</span> {{ __('Account Security & Password') }}
                        </h3>

                        <form method="POST" action="{{ route('profile.password.update') }}" style="display: flex; flex-direction: column; gap: 16px;">
                            @csrf

                            <div>
                                <label style="display: block; font-size: 11px; font-weight: 800; color: var(--text-secondary); margin-bottom: 6px; text-transform: uppercase;">
                                    🔑 {{ __('Current Password') }}
                                </label>
                                <input type="password" name="current_password" required placeholder="••••••••" style="width: 100%; background: var(--bg-surface-subtle); border: 1px solid var(--border-color); border-radius: 10px; padding: 10px 14px; color: var(--text-primary); font-size: 13px; box-shadow: var(--shadow-inset-3d);">
                            </div>

                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                                <div>
                                    <label style="display: block; font-size: 11px; font-weight: 800; color: var(--text-secondary); margin-bottom: 6px; text-transform: uppercase;">
                                        🆕 {{ __('New Password') }}
                                    </label>
                                    <input type="password" name="password" required placeholder="Min 8 chars" style="width: 100%; background: var(--bg-surface-subtle); border: 1px solid var(--border-color); border-radius: 10px; padding: 10px 14px; color: var(--text-primary); font-size: 13px; box-shadow: var(--shadow-inset-3d);">
                                </div>
                                <div>
                                    <label style="display: block; font-size: 11px; font-weight: 800; color: var(--text-secondary); margin-bottom: 6px; text-transform: uppercase;">
                                        🔄 {{ __('Confirm Password') }}
                                    </label>
                                    <input type="password" name="password_confirmation" required placeholder="Repeat new password" style="width: 100%; background: var(--bg-surface-subtle); border: 1px solid var(--border-color); border-radius: 10px; padding: 10px 14px; color: var(--text-primary); font-size: 13px; box-shadow: var(--shadow-inset-3d);">
                                </div>
                            </div>

                            <div style="padding-top: 10px; border-top: 1px solid var(--border-color);">
                                <button type="submit" class="tactile-btn btn-primary" style="padding: 12px 24px; font-size: 13px; cursor: pointer;">
                                    🔒 {{ __('Update Password') }}
                                </button>
                            </div>
                        </form>
                    </div>

                </div>
            </div>
        </div>

        <!-- 8. PROJECTS PORTFOLIO TAB -->
        <div id="tab-projects" class="tab-view">
            <div class="page-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px;">
                <div>
                    <h1 class="page-title" style="font-size: 22px; font-weight: 900; color: var(--text-primary); margin-bottom: 4px;">📁 {{ __('Projects Portfolio') }}</h1>
                    <p class="page-subtitle" style="font-size: 13px; color: var(--text-secondary);">{{ __('Manage company initiatives, milestones, tasks, and budgets.') }}</p>
                </div>
                @if($membership->hasPermission('projects.manage') || $membership->role?->slug === 'company_admin')
                <div style="display: flex; gap: 10px;">
                    <button onclick="openNewProjectModal()" class="tactile-btn btn-primary" style="padding: 10px 18px; font-size: 13px;">
                        <span>+</span> {{ __('New Project') }}
                    </button>
                </div>
                @endif
            </div>

            <!-- Project KPI Metrics (3D Soft Neumorphic) -->
            <div class="kpi-grid" style="margin-bottom: 24px;">
                <div class="kpi-card">
                    <div class="kpi-header">
                        <span class="kpi-title">{{ __('Total Projects') }}</span>
                        <div class="kpi-icon-box">📁</div>
                    </div>
                    <div class="kpi-value">{{ $projects->count() }}</div>
                    <div class="kpi-trend" style="color: var(--brand-forest);">
                        <span>🟢</span> {{ $projects->where('status', 'active')->count() }} {{ __('Active initiatives') }}
                    </div>
                </div>
                <div class="kpi-card">
                    <div class="kpi-header">
                        <span class="kpi-title">{{ __('Total Tasks') }}</span>
                        <div class="kpi-icon-box">✅</div>
                    </div>
                    <div class="kpi-value">{{ $tasks->count() }}</div>
                    <div class="kpi-trend" style="color: var(--brand-forest);">
                        <span>⚡</span> {{ $tasks->where('status', '!=', 'done')->count() }} {{ __('In progress / Backlog') }}
                    </div>
                </div>
                <div class="kpi-card">
                    <div class="kpi-header">
                        <span class="kpi-title">{{ __('Logged Hours') }}</span>
                        <div class="kpi-icon-box">⏱️</div>
                    </div>
                    <div class="kpi-value">{{ round($projects->sum(fn($p) => $p->actualHours()), 1) }}h</div>
                    <div class="kpi-trend" style="color: var(--brand-forest);">
                        <span>📈</span> {{ __('Tracked across all tasks') }}
                    </div>
                </div>
                <div class="kpi-card">
                    <div class="kpi-header">
                        <span class="kpi-title">{{ __('Total Budget') }}</span>
                        <div class="kpi-icon-box">💰</div>
                    </div>
                    <div class="kpi-value">${{ number_format($projects->sum('budget_amount'), 0) }}</div>
                    <div class="kpi-trend" style="color: var(--brand-forest);">
                        <span>💎</span> {{ __('Allocated capital') }}
                    </div>
                </div>
            </div>

            <!-- Projects Table -->
            <div class="card" style="border-radius: var(--radius-lg); overflow: hidden; padding: 0;">
                <div style="padding: 20px 24px; border-bottom: 1px solid var(--border-color); display: flex; justify-content: space-between; align-items: center; background: var(--bg-surface);">
                    <h3 style="font-size: 16px; font-weight: 900; color: var(--text-primary);">📋 {{ __('Active Initiatives') }} ({{ $projects->count() }})</h3>
                </div>
                <div style="overflow-x: auto;">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>{{ __('Code') }}</th>
                                <th>{{ __('Project Name') }}</th>
                                <th>{{ __('Manager') }}</th>
                                <th>{{ __('Status') }}</th>
                                <th>{{ __('Priority') }}</th>
                                <th>{{ __('Progress') }}</th>
                                <th>{{ __('Due Date') }}</th>
                                <th>{{ __('Budget') }}</th>
                                <th>{{ __('Actions') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($projects as $p)
                                @php
                                    $canOpenHub = ($user->isSuperAdmin() || $membership->role?->slug === 'company_admin' || $membership->hasPermission('projects.manage') || $p->manager_id === $user->id || $p->owner_id === $user->id);
                                @endphp
                                <tr @if($canOpenHub) onclick="window.location.href='{{ route('projects.hub', $p->id) }}'" style="cursor: pointer;" title="{{ __('Click to open project dashboard & tasks') }}" @endif>
                                    <td><span class="nav-badge-pill" style="font-family: monospace;">{{ $p->code ?? 'PRJ' }}</span></td>
                                    <td>
                                        <div style="font-weight: 800; color: var(--text-primary);">{{ $p->name }}</div>
                                        <div style="font-size: 11px; color: var(--text-muted);">{{ Str::limit($p->description, 50) }}</div>
                                    </td>
                                    <td>
                                        <div style="display: flex; align-items: center; gap: 8px;">
                                            <div style="width: 26px; height: 26px; border-radius: 8px; background: var(--accent-gradient); color: white; display: flex; align-items: center; justify-content: center; font-size: 10px; font-weight: 800; box-shadow: var(--shadow-soft-3d);">
                                                {{ strtoupper(substr($p->manager->name ?? 'NA', 0, 2)) }}
                                            </div>
                                            <span style="font-weight: 600; font-size: 13px;">{{ $p->manager->name ?? 'Unassigned' }}</span>
                                        </div>
                                    </td>
                                    <td>
                                        @if($p->status === 'active')
                                            <span class="nav-badge-pill" style="background: rgba(79, 155, 95, 0.15); color: #4F9B5F; border-color: rgba(79, 155, 95, 0.3);">{{ __('Active') }}</span>
                                        @elseif($p->status === 'completed')
                                            <span class="nav-badge-pill" style="background: rgba(113, 155, 115, 0.15); color: #719B73; border-color: rgba(113, 155, 115, 0.3);">{{ __('Completed') }}</span>
                                        @elseif($p->status === 'on_hold')
                                            <span class="nav-badge-pill" style="background: rgba(214, 162, 58, 0.15); color: #D6A23A; border-color: rgba(214, 162, 58, 0.3);">{{ __('On Hold') }}</span>
                                        @else
                                            <span class="nav-badge-pill">{{ ucfirst($p->status) }}</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($p->priority === 'urgent')
                                            <span class="nav-badge-pill" style="background: rgba(217, 107, 95, 0.15); color: #D96B5F; border-color: rgba(217, 107, 95, 0.3);">🔥 {{ __('Urgent') }}</span>
                                        @elseif($p->priority === 'high')
                                            <span class="nav-badge-pill" style="background: rgba(214, 162, 58, 0.15); color: #D6A23A; border-color: rgba(214, 162, 58, 0.3);">⚡ {{ __('High') }}</span>
                                        @else
                                            <span class="nav-badge-pill">{{ ucfirst($p->priority) }}</span>
                                        @endif
                                    </td>
                                    <td style="min-width: 140px;">
                                        @php $pct = $p->progressPercentage(); @endphp
                                        <div style="display: flex; justify-content: space-between; font-size: 11px; margin-bottom: 4px; font-weight: 700;">
                                            <span>{{ $pct }}%</span>
                                            <span style="color: var(--text-muted);">{{ $p->tasks_count }} {{ __('tasks') }}</span>
                                        </div>
                                        <div class="progress-bar-bg" style="background: var(--bg-surface-subtle); height: 7px; border-radius: 9999px; overflow: hidden;">
                                            <div class="progress-bar-fill" style="width: {{ $pct }}%; height: 100%; background: {{ $pct === 100 ? '#4F9B5F' : 'var(--accent-gradient)' }}; border-radius: 9999px;"></div>
                                        </div>
                                    </td>
                                    <td style="font-size: 12px; font-weight: 600;">{{ $p->due_date ? $p->due_date->format('M d, Y') : '—' }}</td>
                                    <td style="font-weight: 800; color: var(--brand-forest);">${{ number_format($p->budget_amount ?? 0, 0) }}</td>
                                    <td>
                                        @if($canOpenHub)
                                            <a href="{{ route('projects.hub', $p->id) }}" onclick="event.stopPropagation();" class="tactile-btn btn-primary" style="padding: 6px 12px; font-size: 11px; text-decoration: none;">
                                                📊 {{ __('Open Hub') }}
                                            </a>
                                        @else
                                            <span class="nav-badge-pill" style="font-size: 10px; color: var(--text-muted);">
                                                👁️ {{ __('View Details') }}
                                            </span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" style="text-align: center; padding: 36px; color: var(--text-muted);">
                                        📁 {{ __('No projects created yet.') }}
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- 9. ALL TASKS MANAGER TAB (Project Manager View) -->
        @if($membership->hasPermission('tasks.assign') || $membership->hasPermission('tasks.delete') || $membership->role?->slug === 'company_admin')
        <div id="tab-all-tasks" class="tab-view">
            <div class="page-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; flex-wrap: wrap; gap: 14px;">
                <div>
                    <h1 class="page-title" style="font-size: 22px; font-weight: 900; color: var(--text-primary); margin-bottom: 4px;">📑 {{ __('All Tasks & Work Orders') }}</h1>
                    <p class="page-subtitle" style="font-size: 13px; color: var(--text-secondary);">{{ __('Workspace-wide task tracking, workload distribution, and Kanban workflow control.') }}</p>
                </div>
                <div style="display: flex; gap: 10px; flex-wrap: wrap; align-items: center;">
                    <div style="display: flex; gap: 4px; background: var(--bg-surface-subtle); padding: 4px; border-radius: var(--radius-md); border: 1px solid var(--border-color); box-shadow: var(--shadow-inset-3d);">
                        <button onclick="switchAllTasksView('table')" id="alltasks-btn-table" class="tactile-btn btn-primary" style="padding: 7px 14px; font-size: 12px;">
                            📋 {{ __('Table View') }}
                        </button>
                        <button onclick="switchAllTasksView('kanban')" id="alltasks-btn-kanban" class="tactile-btn btn-secondary" style="padding: 7px 14px; font-size: 12px; background: transparent; border: none; box-shadow: none; color: var(--text-secondary);">
                            📌 {{ __('Kanban Board') }}
                        </button>
                    </div>
                    <button onclick="openNewTaskModal()" class="tactile-btn btn-primary" style="padding: 10px 18px; font-size: 13px;">
                        <span>+</span> {{ __('New Task') }}
                    </button>
                </div>
            </div>

            <!-- Task KPIs Summary (3D Soft Neumorphic) -->
            <div class="kpi-grid" style="margin-bottom: 24px;">
                <div class="kpi-card">
                    <div class="kpi-header">
                        <span class="kpi-title">{{ __('Total Tasks') }}</span>
                        <div class="kpi-icon-box">📑</div>
                    </div>
                    <div class="kpi-value">{{ $tasks->count() }}</div>
                    <div class="kpi-trend" style="color: var(--brand-forest);">
                        <span>📁</span> {{ __('Across active projects') }}
                    </div>
                </div>
                <div class="kpi-card">
                    <div class="kpi-header">
                        <span class="kpi-title">{{ __('In Progress') }}</span>
                        <div class="kpi-icon-box">⚡</div>
                    </div>
                    <div class="kpi-value">{{ $tasks->where('status', 'in_progress')->count() }}</div>
                    <div class="kpi-trend" style="color: var(--brand-forest);">
                        <span>🏃</span> {{ __('Active work execution') }}
                    </div>
                </div>
                <div class="kpi-card">
                    <div class="kpi-header">
                        <span class="kpi-title">{{ __('Under Review') }}</span>
                        <div class="kpi-icon-box">🔍</div>
                    </div>
                    <div class="kpi-value">{{ $tasks->whereIn('status', ['review', 'qa'])->count() }}</div>
                    <div class="kpi-trend" style="color: var(--status-warning);">
                        <span>⏳</span> {{ __('Pending QA / signoff') }}
                    </div>
                </div>
                <div class="kpi-card">
                    <div class="kpi-header">
                        <span class="kpi-title">{{ __('Completed') }}</span>
                        <div class="kpi-icon-box">🎉</div>
                    </div>
                    <div class="kpi-value">{{ $tasks->where('status', 'done')->count() }}</div>
                    <div class="kpi-trend" style="color: var(--brand-forest);">
                        <span>✅</span> {{ __('Delivered features') }}
                    </div>
                </div>
                <div class="kpi-card">
                    <div class="kpi-header">
                        <span class="kpi-title">{{ __('Estimated Effort') }}</span>
                        <div class="kpi-icon-box">⏱️</div>
                    </div>
                    <div class="kpi-value" style="font-size: 20px;">{{ $tasks->sum('estimated_hours') }}h / {{ round($projects->sum(fn($p) => $p->actualHours()), 1) }}h</div>
                    <div class="kpi-trend" style="color: var(--brand-forest);">
                        <span>📊</span> {{ __('Planned vs Tracked') }}
                    </div>
                </div>
            </div>

            <!-- Filter Toolbar -->
            <div class="card" style="padding: 16px 20px; margin-bottom: 20px; border-radius: var(--radius-lg);">
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 12px; align-items: center;">
                    <div>
                        <label style="display: block; font-size: 11px; font-weight: 800; color: var(--text-secondary); margin-bottom: 4px; text-transform: uppercase;">🔍 {{ __('Search Tasks') }}</label>
                        <input type="text" id="alltasks-filter-search" oninput="filterAllTasksTable()" placeholder="Task title or #..." style="width: 100%; background: var(--bg-surface-subtle); border: 1px solid var(--border-color); border-radius: 10px; padding: 9px 12px; color: var(--text-primary); outline: none; font-size: 12px; font-weight: 600; box-shadow: var(--shadow-inset-3d);">
                    </div>
                    <div>
                        <label style="display: block; font-size: 11px; font-weight: 800; color: var(--text-secondary); margin-bottom: 4px; text-transform: uppercase;">📁 {{ __('Project') }}</label>
                        <select id="alltasks-filter-project" onchange="filterAllTasksTable()" style="width: 100%; background: var(--bg-surface-subtle); border: 1px solid var(--border-color); border-radius: 10px; padding: 9px 12px; color: var(--text-primary); outline: none; font-size: 12px; font-weight: 600;">
                            <option value="">— {{ __('All Projects') }} —</option>
                            @foreach($projects as $p)
                                <option value="{{ $p->id }}">{{ $p->name }} ({{ $p->code }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label style="display: block; font-size: 11px; font-weight: 800; color: var(--text-secondary); margin-bottom: 4px; text-transform: uppercase;">⚡ {{ __('Status') }}</label>
                        <select id="alltasks-filter-status" onchange="filterAllTasksTable()" style="width: 100%; background: var(--bg-surface-subtle); border: 1px solid var(--border-color); border-radius: 10px; padding: 9px 12px; color: var(--text-primary); outline: none; font-size: 12px; font-weight: 600;">
                            <option value="">— {{ __('All Statuses') }} —</option>
                            <option value="backlog">📌 Backlog</option>
                            <option value="ready">🎯 Ready</option>
                            <option value="in_progress">⚡ In Progress</option>
                            <option value="review">🔍 Review / QA</option>
                            <option value="done">🎉 Done</option>
                        </select>
                    </div>
                    <div>
                        <label style="display: block; font-size: 11px; font-weight: 800; color: var(--text-secondary); margin-bottom: 4px; text-transform: uppercase;">⚡ {{ __('Priority') }}</label>
                        <select id="alltasks-filter-priority" onchange="filterAllTasksTable()" style="width: 100%; background: var(--bg-surface-subtle); border: 1px solid var(--border-color); border-radius: 10px; padding: 9px 12px; color: var(--text-primary); outline: none; font-size: 12px; font-weight: 600;">
                            <option value="">— {{ __('All Priorities') }} —</option>
                            <option value="urgent">🔥 Urgent</option>
                            <option value="high">⚡ High</option>
                            <option value="medium">Medium</option>
                            <option value="low">Low</option>
                        </select>
                    </div>
                    <div>
                        <label style="display: block; font-size: 11px; font-weight: 800; color: var(--text-secondary); margin-bottom: 4px; text-transform: uppercase;">👤 {{ __('Assignee') }}</label>
                        <select id="alltasks-filter-assignee" onchange="filterAllTasksTable()" style="width: 100%; background: var(--bg-surface-subtle); border: 1px solid var(--border-color); border-radius: 10px; padding: 9px 12px; color: var(--text-primary); outline: none; font-size: 12px; font-weight: 600;">
                            <option value="">— {{ __('All Members') }} —</option>
                            @foreach($members as $m)
                                <option value="{{ $m->user_id }}">{{ $m->user->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            <!-- View 1: Tasks Table / List -->
            <div id="alltasks-view-table" class="card" style="display: block; border-radius: var(--radius-lg); overflow: hidden; padding: 0;">
                <div style="padding: 20px 24px; border-bottom: 1px solid var(--border-color); display: flex; justify-content: space-between; align-items: center; background: var(--bg-surface);">
                    <h3 style="font-size: 16px; font-weight: 900; color: var(--text-primary);">📋 {{ __('All Organization Tasks') }} (<span id="alltasks-filtered-count">{{ $tasks->count() }}</span>)</h3>
                </div>
                <div style="overflow-x: auto;">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>{{ __('Task Title') }}</th>
                                <th>{{ __('Project') }}</th>
                                <th>{{ __('Assignee') }}</th>
                                <th>{{ __('Status') }}</th>
                                <th>{{ __('Priority') }}</th>
                                <th>{{ __('Estimated / Actual') }}</th>
                                <th>{{ __('Due Date') }}</th>
                                <th>{{ __('Actions') }}</th>
                            </tr>
                        </thead>
                        <tbody id="alltasks-table-body">
                            @forelse($tasks as $t)
                                <tr class="alltask-row" 
                                    data-id="{{ $t->id }}"
                                    data-title="{{ strtolower($t->title) }}"
                                    data-project-id="{{ $t->project_id }}"
                                    data-status="{{ $t->status }}"
                                    data-priority="{{ $t->priority }}"
                                    data-assignee-id="{{ $t->assignee_id }}"
                                    onclick="openTaskDetails('{{ $t->id }}')"
                                    oncontextmenu="event.preventDefault(); event.stopPropagation(); openTaskContextMenu(event, '{{ $t->id }}', '{{ $t->project_id }}', '{{ addslashes($t->title) }}')"
                                    <td><span class="nav-badge-pill" style="font-family: monospace;">#{{ $t->task_number ?? 1 }}</span></td>
                                    <td>
                                        <div style="font-weight: 800; color: var(--text-primary); display: flex; align-items: center; gap: 6px;">
                                            <span>{{ $t->title }}</span>
                                            @if($t->checklistItems && $t->checklistItems->count() > 0)
                                                <span class="nav-badge-pill" style="font-size: 9px; background: rgba(79, 155, 95, 0.15); color: #4F9B5F;">⊞ {{ $t->checklistItems->where('is_completed', true)->count() }}/{{ $t->checklistItems->count() }}</span>
                                            @endif
                                        </div>
                                        @if($t->description)
                                            <div style="font-size: 11px; color: var(--text-muted);">{{ Str::limit($t->description, 45) }}</div>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="nav-badge-pill" style="font-weight: 700;">📁 {{ $t->project->name ?? 'General' }}</span>
                                    </td>
                                    <td>
                                        @if($t->assignee)
                                            <div style="display: flex; align-items: center; gap: 8px;">
                                                <div style="width: 24px; height: 24px; border-radius: 8px; background: var(--accent-gradient); color: white; display: flex; align-items: center; justify-content: center; font-size: 9px; font-weight: 800;">
                                                    {{ strtoupper(substr($t->assignee->name, 0, 2)) }}
                                                </div>
                                                <span style="font-weight: 600; font-size: 13px;">{{ $t->assignee->name }}</span>
                                            </div>
                                        @else
                                            <span style="color: var(--text-muted); font-size: 11px;">— {{ __('Unassigned') }} —</span>
                                        @endif
                                    </td>
                                    <td>
                                        <select onchange="event.stopPropagation(); updateTaskStatusDirect('{{ $t->id }}', this.value)" style="background: var(--bg-surface-subtle); border: 1px solid var(--border-color); color: var(--text-primary); font-size: 11px; font-weight: 700; border-radius: 8px; padding: 4px 8px; outline: none; cursor: pointer;">
                                            <option value="backlog" {{ $t->status === 'backlog' ? 'selected' : '' }}>📌 {{ __('Backlog') }}</option>
                                            <option value="ready" {{ $t->status === 'ready' ? 'selected' : '' }}>🎯 {{ __('Ready') }}</option>
                                            <option value="in_progress" {{ $t->status === 'in_progress' ? 'selected' : '' }}>⚡ {{ __('In Progress') }}</option>
                                            <option value="review" {{ $t->status === 'review' || $t->status === 'qa' ? 'selected' : '' }}>🔍 {{ __('Review') }}</option>
                                            <option value="done" {{ $t->status === 'done' ? 'selected' : '' }}>🎉 {{ __('Done') }}</option>
                                        </select>
                                    </td>
                                    <td>
                                        @if($t->priority === 'urgent')
                                            <span class="nav-badge-pill" style="background: rgba(217, 107, 95, 0.15); color: #D96B5F; border-color: rgba(217, 107, 95, 0.3);">🔥 {{ __('Urgent') }}</span>
                                        @elseif($t->priority === 'high')
                                            <span class="nav-badge-pill" style="background: rgba(214, 162, 58, 0.15); color: #D6A23A; border-color: rgba(214, 162, 58, 0.3);">⚡ {{ __('High') }}</span>
                                        @else
                                            <span class="nav-badge-pill">{{ ucfirst($t->priority) }}</span>
                                        @endif
                                    </td>
                                    <td style="font-family: monospace; font-weight: 700;">
                                        {{ $t->estimated_hours ?? 0 }}h / {{ $t->actualHours() }}h
                                    </td>
                                    <td>
                                        @php
                                            $isOverdue = $t->due_date && $t->due_date->isPast() && $t->status !== 'done';
                                        @endphp
                                        <span style="font-size: 12px; font-weight: 700; color: {{ $isOverdue ? '#D96B5F' : 'var(--text-secondary)' }};">
                                            {{ $t->due_date ? $t->due_date->format('M d, Y') : '—' }}
                                            @if($isOverdue) <span class="nav-badge-pill" style="background: rgba(217, 107, 95, 0.15); color: #D96B5F; font-size: 9px;">{{ __('Overdue') }}</span> @endif
                                        </span>
                                    </td>
                                    <td>
                                        <div style="display: flex; gap: 6px;" onclick="event.stopPropagation();">
                                            <button onclick="startTaskTimer('{{ $t->project_id }}', '{{ $t->id }}', '{{ addslashes($t->title) }}', '{{ addslashes($t->project->name ?? 'Project') }}')" class="tactile-btn" style="background: rgba(79, 155, 95, 0.15); color: var(--brand-forest); border: 1px solid rgba(79, 155, 95, 0.3); padding: 4px 10px; font-size: 11px;">
                                                ▶ {{ __('Timer') }}
                                            </button>
                                            <button onclick="openTaskDetails('{{ $t->id }}')" class="tactile-btn btn-secondary" style="padding: 4px 10px; font-size: 11px;">
                                                🔍 {{ __('Inspect') }}
                                            </button>
                                            <button onclick="openTaskContextMenu(event, '{{ $t->id }}', '{{ $t->project_id }}', '{{ addslashes($t->title) }}')" class="tactile-btn btn-secondary" style="padding: 4px 8px; font-size: 11px;" title="{{ __('More Actions') }}">
                                                •••
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" style="text-align: center; padding: 36px; color: var(--text-muted);">
                                        📑 {{ __('No tasks created yet. Click "+ New Task" to create one.') }}
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- View 2: Global Drag & Drop 3D Kanban Board -->
            <div id="alltasks-view-kanban" style="display: none; margin-top: 14px;">
                <div class="kanban-grid">
                    @php
                        $kanbanColumns = [
                            'backlog' => ['title' => '📌 ' . __('Backlog'), 'color' => 'var(--text-secondary)', 'bg' => 'var(--bg-surface-subtle)'],
                            'ready' => ['title' => '🎯 ' . __('Ready'), 'color' => 'var(--brand-sage)', 'bg' => 'var(--bg-surface-subtle)'],
                            'in_progress' => ['title' => '⚡ ' . __('In Progress'), 'color' => 'var(--brand-forest)', 'bg' => 'rgba(79, 155, 95, 0.08)'],
                            'review' => ['title' => '🔍 ' . __('Review / QA'), 'color' => 'var(--status-warning)', 'bg' => 'rgba(214, 162, 58, 0.08)'],
                            'done' => ['title' => '🎉 ' . __('Done'), 'color' => 'var(--status-success)', 'bg' => 'rgba(79, 155, 95, 0.12)'],
                        ];
                    @endphp

                    @foreach($kanbanColumns as $statusKey => $colMeta)
                    <div class="kanban-column" 
                         id="global-kanban-zone-{{ $statusKey }}"
                         ondragover="handleGlobalDragOver(event)" 
                         ondragleave="handleGlobalDragLeave(event)" 
                         ondrop="handleGlobalDrop(event, '{{ $statusKey }}')">
                        
                        <div class="kanban-col-header" style="color: {{ $colMeta['color'] }};">
                            <span style="display: flex; align-items: center; gap: 6px;">
                                <span>{{ $colMeta['title'] }}</span>
                            </span>
                            <span class="nav-badge-pill" id="global-kanban-cnt-{{ $statusKey }}">
                                {{ $statusKey === 'review' ? $tasks->whereIn('status', ['review', 'qa'])->count() : $tasks->where('status', $statusKey)->count() }}
                            </span>
                        </div>

                        <div class="kanban-cards-container" id="global-kanban-col-{{ $statusKey }}">
                            @php
                                $colTasks = ($statusKey === 'review') ? $tasks->whereIn('status', ['review', 'qa']) : $tasks->where('status', $statusKey);
                            @endphp

                            @forelse($colTasks as $t)
                                <div class="global-kanban-card kanban-card" 
                                     id="global-kanban-card-{{ $t->id }}"
                                     draggable="true" 
                                     ondragstart="handleGlobalDragStart(event, '{{ $t->id }}')" 
                                     ondragend="handleGlobalDragEnd(event)"
                                     oncontextmenu="event.preventDefault(); event.stopPropagation(); openTaskContextMenu(event, '{{ $t->id }}', '{{ $t->project_id }}', '{{ addslashes($t->title) }}')"
                                     data-id="{{ $t->id }}"
                                     data-title="{{ strtolower($t->title) }}"
                                     data-project-id="{{ $t->project_id }}"
                                     data-status="{{ $t->status }}"
                                     data-priority="{{ $t->priority }}"
                                     data-assignee-id="{{ $t->assignee_id }}"
                                     onclick="openTaskDetails('{{ $t->id }}')">
                                    
                                    <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 8px; gap: 6px;">
                                        <div style="display: flex; gap: 4px; align-items: center;">
                                            <span class="nav-badge-pill" style="font-family: monospace; font-size: 10px; font-weight: 800;">
                                                #{{ $t->task_number ?? 1 }}
                                            </span>
                                            <span class="nav-badge-pill" style="font-size: 9px; font-weight: 700; color: var(--brand-forest); background: rgba(79, 155, 95, 0.12);">
                                                {{ $t->project->code ?? 'PRJ' }}
                                            </span>
                                            @if($t->checklistItems && $t->checklistItems->count() > 0)
                                                <span class="nav-badge-pill" style="font-size: 9px; background: rgba(79, 155, 95, 0.15); color: #4F9B5F;" title="{{ __('Checklist Progress') }}">
                                                    ⊞ {{ $t->checklistItems->where('is_completed', true)->count() }}/{{ $t->checklistItems->count() }}
                                                </span>
                                            @endif
                                        </div>

                                        <div style="display: flex; align-items: center; gap: 4px;">
                                            @if($t->priority === 'urgent')
                                                <span class="nav-badge-pill" style="background: rgba(217, 107, 95, 0.15); color: #D96B5F; font-size: 9px; font-weight: 800;">🚩 {{ __('Urgent') }}</span>
                                            @elseif($t->priority === 'high')
                                                <span class="nav-badge-pill" style="background: rgba(214, 162, 58, 0.15); color: #D6A23A; font-size: 9px; font-weight: 800;">⚡ {{ __('High') }}</span>
                                            @endif

                                            <select onclick="event.stopPropagation()" onchange="updateTaskStatusDirect('{{ $t->id }}', this.value)" style="background: var(--bg-surface-subtle); border: 1px solid var(--border-color); color: var(--text-primary); font-size: 10px; font-weight: 700; border-radius: 6px; padding: 2px 4px; outline: none; cursor: pointer;">
                                                <option value="backlog" {{ $t->status === 'backlog' ? 'selected' : '' }}>📌 Backlog</option>
                                                <option value="ready" {{ $t->status === 'ready' ? 'selected' : '' }}>🎯 Ready</option>
                                                <option value="in_progress" {{ $t->status === 'in_progress' ? 'selected' : '' }}>⚡ In Progress</option>
                                                <option value="review" {{ $t->status === 'review' || $t->status === 'qa' ? 'selected' : '' }}>🔍 Review</option>
                                                <option value="done" {{ $t->status === 'done' ? 'selected' : '' }}>🎉 Done</option>
                                            </select>

                                            <button onclick="event.stopPropagation(); openTaskContextMenu(event, '{{ $t->id }}', '{{ $t->project_id }}', '{{ addslashes($t->title) }}')" class="tactile-btn btn-secondary" style="padding: 2px 6px; font-size: 10px; line-height: 1;" title="{{ __('Task Actions') }}">
                                                •••
                                            </button>
                                        </div>
                                    </div>

                                    <div style="font-weight: 800; font-size: 13px; color: var(--text-primary); margin-bottom: 6px; line-height: 1.4;">
                                        {{ $t->title }}
                                    </div>

                                    <div style="font-size: 11px; color: var(--text-muted); margin-bottom: 10px; display: flex; align-items: center; gap: 6px;">
                                        <span>📁 {{ $t->project->name ?? 'General' }}</span>
                                        @if($t->due_date)
                                            <span>•</span>
                                            <span style="color: {{ $t->due_date->isPast() && $t->status !== 'done' ? '#D96B5F' : 'inherit' }}; font-weight: 600;">
                                                📅 {{ $t->due_date->format('M d') }}
                                            </span>
                                        @endif
                                    </div>

                                    <div style="display: flex; justify-content: space-between; align-items: center; border-top: 1px solid var(--border-color); padding-top: 8px; font-size: 11px;">
                                        <div style="display: flex; align-items: center; gap: 6px;">
                                            @if($t->assignee)
                                                <div style="width: 22px; height: 22px; border-radius: 50%; background: var(--accent-gradient); color: white; display: flex; align-items: center; justify-content: center; font-size: 9px; font-weight: 800;">
                                                    {{ strtoupper(substr($t->assignee->name, 0, 2)) }}
                                                </div>
                                                <span style="font-weight: 700; color: var(--text-secondary);">{{ explode(' ', $t->assignee->name)[0] }}</span>
                                            @else
                                                <span style="color: var(--text-muted); font-size: 10px;">— {{ __('Unassigned') }} —</span>
                                            @endif
                                        </div>

                                        <div style="display: flex; align-items: center; gap: 6px;">
                                            <span style="font-family: monospace; font-size: 10px; font-weight: 700; color: var(--text-muted);">
                                                {{ $t->estimated_hours ? $t->estimated_hours . 'h' : '' }}
                                            </span>
                                            <button onclick="event.stopPropagation(); startTaskTimer('{{ $t->project_id }}', '{{ $t->id }}', '{{ addslashes($t->title) }}', '{{ addslashes($t->project->name ?? 'Project') }}')" class="tactile-btn" style="background: rgba(79, 155, 95, 0.15); color: var(--brand-forest); border: 1px solid rgba(79, 155, 95, 0.3); padding: 3px 8px; font-size: 10px;" title="{{ __('Start Timer') }}">
                                                ▶
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <div class="kanban-empty-hint" style="text-align: center; padding: 26px 12px; color: var(--text-muted); font-size: 11px; border: 1px dashed var(--border-color); border-radius: var(--radius-md); background: rgba(255, 255, 255, 0.4);">
                                    {{ __('No tasks in this stage.') }}
                                </div>
                            @endforelse
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
        @endif

        <!-- 10. MY TASKS TAB -->
        <div id="tab-my-tasks" class="tab-view">
            <div class="page-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px;">
                <div>
                    <h1 class="page-title" style="font-size: 22px; font-weight: 900; color: var(--text-primary); margin-bottom: 4px;">✅ {{ __('My Tasks & Action Items') }}</h1>
                    <p class="page-subtitle" style="font-size: 13px; color: var(--text-secondary);">{{ __('Track and log time against your personal assigned tasks.') }}</p>
                </div>
                <div style="display: flex; gap: 10px;">
                    <button onclick="openNewTaskModal()" class="tactile-btn btn-primary" style="padding: 10px 18px; font-size: 13px;">
                        <span>+</span> {{ __('New Task') }}
                    </button>
                </div>
            </div>

            <!-- Task Status Columns Grid (5-Column Kanban matching All Tasks) -->
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 16px;">
                @php
                    $myKanbanCols = [
                        'backlog' => ['title' => '📌 ' . __('Backlog'), 'color' => 'var(--text-secondary)', 'border' => 'var(--border-color)'],
                        'ready' => ['title' => '🎯 ' . __('Ready'), 'color' => 'var(--brand-sage)', 'border' => 'var(--brand-sage)'],
                        'in_progress' => ['title' => '⚡ ' . __('In Progress'), 'color' => 'var(--brand-forest)', 'border' => 'var(--brand-forest)'],
                        'review' => ['title' => '🔍 ' . __('Review / QA'), 'color' => 'var(--status-warning)', 'border' => '#D6A23A'],
                        'done' => ['title' => '🎉 ' . __('Done'), 'color' => 'var(--brand-forest)', 'border' => '#4F9B5F'],
                    ];
                @endphp

                @foreach($myKanbanCols as $colKey => $colMeta)
                    @php
                        $colTasks = ($colKey === 'review') ? $myTasks->whereIn('status', ['review', 'qa']) : $myTasks->where('status', $colKey);
                    @endphp
                    <div class="card" style="border-radius: var(--radius-lg); padding: 14px; background: var(--bg-surface-subtle); display: flex; flex-direction: column;">
                        <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 2px solid {{ $colMeta['border'] }}; padding-bottom: 10px; margin-bottom: 12px;">
                            <h3 style="font-size: 14px; font-weight: 900; color: var(--text-primary); display: flex; align-items: center; gap: 6px;">
                                <span>{{ $colMeta['title'] }}</span>
                            </h3>
                            <span class="nav-badge-pill" style="font-weight: 800;">{{ $colTasks->count() }}</span>
                        </div>

                        <div style="display: flex; flex-direction: column; gap: 10px; flex: 1;">
                            @forelse($colTasks as $t)
                                @php
                                    $canEditThisTask = ($user->isSuperAdmin() || $membership->role?->slug === 'company_admin' || $membership->hasPermission('tasks.assign') || $membership->hasPermission('tasks.delete') || ($t->project && $t->project->manager_id === $user->id) || $t->assignee_id === $user->id || $t->creator_id === $user->id);
                                    $isManager = ($user->isSuperAdmin() || $membership->role?->slug === 'company_admin' || ($t->project && $t->project->manager_id === $user->id));
                                @endphp
                                <div class="kanban-task-card" style="background: var(--bg-surface); border: 1px solid var(--border-color); border-radius: var(--radius-lg); padding: 12px; box-shadow: var(--shadow-card); cursor: pointer;" onclick="openTaskDetails('{{ $t->id }}')">
                                    <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 6px; gap: 6px;">
                                        <div style="display: flex; gap: 4px; align-items: center; flex-wrap: wrap;">
                                            <span class="nav-badge-pill" style="font-family: monospace; font-size: 9px; font-weight: 800;">#{{ $t->task_number ?? 1 }}</span>
                                            <span class="nav-badge-pill" style="font-size: 9px; font-weight: 700; color: var(--brand-forest); background: rgba(79, 155, 95, 0.12);">
                                                {{ $t->project->code ?? 'PRJ' }}
                                            </span>
                                            @if($t->checklistItems && $t->checklistItems->count() > 0)
                                                <span class="nav-badge-pill" style="font-size: 9px; background: rgba(79, 155, 95, 0.15); color: #4F9B5F;">
                                                    ⊞ {{ $t->checklistItems->where('is_completed', true)->count() }}/{{ $t->checklistItems->count() }}
                                                </span>
                                            @endif
                                        </div>
                                        <div style="display: flex; align-items: center; gap: 4px;">
                                            @if($t->priority === 'urgent')
                                                <span class="nav-badge-pill" style="background: rgba(217, 107, 95, 0.15); color: #D96B5F; font-size: 9px; font-weight: 800;">🔥</span>
                                            @elseif($t->priority === 'high')
                                                <span class="nav-badge-pill" style="background: rgba(214, 162, 58, 0.15); color: #D6A23A; font-size: 9px; font-weight: 800;">⚡</span>
                                            @endif
                                            <select onclick="event.stopPropagation()" onchange="updateTaskStatusDirect('{{ $t->id }}', this.value)" {{ $canEditThisTask ? '' : 'disabled' }} style="background: var(--bg-surface-subtle); border: 1px solid var(--border-color); color: var(--text-primary); font-size: 9px; font-weight: 700; border-radius: 6px; padding: 2px 4px; outline: none; cursor: {{ $canEditThisTask ? 'pointer' : 'not-allowed' }};">
                                                <option value="backlog" {{ $t->status === 'backlog' ? 'selected' : '' }}>📌 {{ __('Backlog') }}</option>
                                                <option value="ready" {{ $t->status === 'ready' ? 'selected' : '' }}>🎯 {{ __('Ready') }}</option>
                                                <option value="in_progress" {{ $t->status === 'in_progress' ? 'selected' : '' }}>⚡ {{ __('In Progress') }}</option>
                                                <option value="review" {{ $t->status === 'review' || $t->status === 'qa' ? 'selected' : '' }}>🔍 {{ __('Review') }}</option>
                                                <option value="done" {{ $t->status === 'done' ? 'selected' : '' }}>🎉 {{ __('Done') }}</option>
                                            </select>
                                        </div>
                                    </div>

                                    <div style="font-weight: 800; font-size: 12px; margin-bottom: 4px; color: var(--text-primary); line-height: 1.3;">{{ $t->title }}</div>

                                    @if($t->approval_status === 'pending_approval')
                                        <div style="background: rgba(214, 162, 58, 0.15); border: 1px solid rgba(214, 162, 58, 0.3); color: #D6A23A; font-size: 10px; font-weight: 800; padding: 4px 8px; border-radius: 6px; margin-bottom: 6px; display: flex; align-items: center; justify-content: space-between;">
                                            <span>⏳ {{ __('Pending PM Approval') }}</span>
                                            @if($isManager)
                                                <button onclick="event.stopPropagation(); quickApproveTask('{{ $t->id }}')" class="tactile-btn" style="background: #4F9B5F; color: white; padding: 2px 6px; font-size: 9px; border: none;">✓ {{ __('Approve') }}</button>
                                            @endif
                                        </div>
                                    @elseif($t->approval_status === 'rejected')
                                        <div style="background: rgba(217, 107, 95, 0.15); border: 1px solid rgba(217, 107, 95, 0.3); color: #D96B5F; font-size: 10px; font-weight: 800; padding: 4px 8px; border-radius: 6px; margin-bottom: 6px;">
                                            <span>⚠️ {{ __('Changes Requested') }}</span>
                                        </div>
                                    @endif

                                    <div style="font-size: 10px; color: var(--text-muted); margin-bottom: 8px; display: flex; align-items: center; gap: 4px;">
                                        <span>📁 {{ $t->project->name ?? 'General' }}</span>
                                        @if($t->due_date)
                                            <span>•</span>
                                            <span style="{{ $t->due_date->isPast() && $t->status !== 'done' ? 'color: #D96B5F; font-weight: 800;' : '' }}">
                                                📅 {{ $t->due_date->format('M d') }}
                                            </span>
                                        @endif
                                    </div>

                                    <div style="display: flex; justify-content: space-between; align-items: center; border-top: 1px solid var(--border-color); padding-top: 6px; font-size: 10px;">
                                        <span style="font-family: monospace; font-size: 9px; font-weight: 700; color: var(--brand-forest);">
                                            ⏱️ {{ round($t->logged_hours ?? $t->actual_hours ?? 0, 1) }}h{{ $t->estimated_hours ? ' / ' . $t->estimated_hours . 'h' : '' }}
                                        </span>
                                        <button onclick="event.stopPropagation(); startTaskTimer('{{ $t->project_id }}', '{{ $t->id }}', '{{ addslashes($t->title) }}', '{{ addslashes($t->project->name ?? 'Project') }}')" class="tactile-btn" style="background: rgba(79, 155, 95, 0.15); color: var(--brand-forest); border: 1px solid rgba(79, 155, 95, 0.3); padding: 3px 8px; font-size: 10px; font-weight: 800;">
                                            ▶ {{ __('Timer') }}
                                        </button>
                                    </div>
                                </div>
                            @empty
                                <div style="text-align: center; padding: 18px 8px; color: var(--text-muted); font-size: 11px; border: 1px dashed var(--border-color); border-radius: var(--radius-md);">
                                    {{ __('No tasks in this stage.') }}
                                </div>
                            @endforelse
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- 10. TIMESHEETS & TIME TRACKING TAB -->
        <div id="tab-timesheets" class="tab-view">
            <div class="page-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; flex-wrap: wrap; gap: 14px;">
                <div>
                    <h1 class="page-title" style="font-size: 22px; font-weight: 900; color: var(--text-primary); margin-bottom: 4px;">⏱️ {{ __('Timesheets & Time Tracking') }}</h1>
                    <p class="page-subtitle" style="font-size: 13px; color: var(--text-secondary);">{{ __('Log working hours, view weekly timesheets, and review employee submissions.') }}</p>
                </div>
                <div style="display: flex; gap: 10px; flex-wrap: wrap;">
                    <button onclick="openManualTimeModal()" class="tactile-btn btn-secondary" style="padding: 10px 16px; font-size: 13px;">
                        <span>✍️</span> {{ __('Manual Time Entry') }}
                    </button>
                    <button onclick="submitMyCurrentTimesheet()" class="tactile-btn btn-primary" style="padding: 10px 18px; font-size: 13px;">
                        <span>📤</span> {{ __('Submit Weekly Timesheet') }}
                    </button>
                </div>
            </div>

            <!-- Recent Time Entries -->
            <div class="card" style="border-radius: var(--radius-lg); overflow: hidden; padding: 0; margin-bottom: 24px;">
                <div style="padding: 20px 24px; border-bottom: 1px solid var(--border-color); display: flex; justify-content: space-between; align-items: center; background: var(--bg-surface);">
                    <h3 style="font-size: 16px; font-weight: 900; color: var(--text-primary);">🕒 {{ __('My Recent Time Log') }}</h3>
                    <span class="nav-badge-pill" style="background: rgba(79, 155, 95, 0.15); color: #4F9B5F; font-size: 12px; font-weight: 800;">
                        {{ round($recentTimeEntries->sum('duration_seconds') / 3600, 1) }} {{ __('Hours logged recently') }}
                    </span>
                </div>
                <div style="overflow-x: auto;">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>{{ __('Date') }}</th>
                                <th>{{ __('Project') }}</th>
                                <th>{{ __('Task') }}</th>
                                <th>{{ __('Description') }}</th>
                                <th>{{ __('Duration') }}</th>
                                <th>{{ __('Type') }}</th>
                                <th>{{ __('Status') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentTimeEntries as $te)
                                <tr>
                                    <td style="font-weight: 600;">{{ $te->started_at->format('M d, Y') }}</td>
                                    <td><span class="nav-badge-pill" style="font-weight: 700;">{{ $te->project->name ?? 'General' }}</span></td>
                                    <td style="font-weight: 800; color: var(--text-primary);">{{ $te->task->title ?? '—' }}</td>
                                    <td style="color: var(--text-secondary); font-size: 12px;">{{ $te->description ?? 'Work session' }}</td>
                                    <td style="font-weight: 900; color: var(--brand-forest); font-family: monospace; font-size: 14px;">{{ $te->hours() }}h</td>
                                    <td><span class="nav-badge-pill">{{ ucfirst($te->entry_type) }}</span></td>
                                    <td>
                                        @if($te->status === 'approved')
                                            <span class="nav-badge-pill" style="background: rgba(79, 155, 95, 0.15); color: #4F9B5F;">🔒 {{ __('Approved') }}</span>
                                        @elseif($te->status === 'submitted')
                                            <span class="nav-badge-pill" style="background: rgba(214, 162, 58, 0.15); color: #D6A23A;">⏳ {{ __('Submitted') }}</span>
                                        @elseif($te->status === 'rejected')
                                            <span class="nav-badge-pill" style="background: rgba(217, 107, 95, 0.15); color: #D96B5F;">❌ {{ __('Rejected') }}</span>
                                        @else
                                            <span class="nav-badge-pill">{{ __('Draft') }}</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" style="text-align: center; padding: 32px; color: var(--text-muted);">
                                        ⏱️ {{ __('No time entries logged yet.') }}
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Manager Timesheet Review Queue -->
            <div class="card" style="border-radius: var(--radius-lg); overflow: hidden; padding: 0;">
                <div style="padding: 20px 24px; border-bottom: 1px solid var(--border-color); background: var(--bg-surface);">
                    <h3 style="font-size: 16px; font-weight: 900; color: var(--text-primary);">📋 {{ __('Timesheet Submissions Review Queue') }}</h3>
                </div>
                <div style="overflow-x: auto;">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>{{ __('Employee') }}</th>
                                <th>{{ __('Period') }}</th>
                                <th>{{ __('Total Hours') }}</th>
                                <th>{{ __('Billable') }}</th>
                                <th>{{ __('Status') }}</th>
                                <th>{{ __('Action') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($allTimesheets as $ts)
                                <tr>
                                    <td style="font-weight: 800; color: var(--text-primary);">{{ $ts->user->name ?? 'Member' }}</td>
                                    <td>{{ $ts->period_start->format('M d') }} — {{ $ts->period_end->format('M d, Y') }}</td>
                                    <td style="font-weight: 900; color: var(--text-primary); font-family: monospace;">{{ $ts->total_hours }}h</td>
                                    <td style="color: var(--brand-forest); font-weight: 800; font-family: monospace;">{{ $ts->billable_hours }}h</td>
                                    <td>
                                        @if($ts->status === 'approved')
                                            <span class="nav-badge-pill" style="background: rgba(79, 155, 95, 0.15); color: #4F9B5F;">✅ {{ __('Approved') }}</span>
                                        @elseif($ts->status === 'submitted')
                                            <span class="nav-badge-pill" style="background: rgba(214, 162, 58, 0.15); color: #D6A23A;">⏳ {{ __('Pending Review') }}</span>
                                        @elseif($ts->status === 'rejected')
                                            <span class="nav-badge-pill" style="background: rgba(217, 107, 95, 0.15); color: #D96B5F;">❌ {{ __('Rejected') }}</span>
                                        @else
                                            <span class="nav-badge-pill">{{ ucfirst($ts->status) }}</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($ts->status === 'submitted' && ($membership->hasPermission('timesheets.approve') || $user->isSuperAdmin()))
                                            <div style="display: flex; gap: 6px;">
                                                <button onclick="approveTimesheet('{{ $ts->id }}')" class="tactile-btn" style="background: rgba(79, 155, 95, 0.15); color: var(--brand-forest); border: 1px solid rgba(79, 155, 95, 0.3); padding: 4px 10px; font-size: 11px;">
                                                    ✓ {{ __('Approve') }}
                                                </button>
                                                <button onclick="openRejectModal('{{ $ts->id }}')" class="tactile-btn" style="background: rgba(217, 107, 95, 0.15); color: #D96B5F; border: 1px solid rgba(217, 107, 95, 0.3); padding: 4px 10px; font-size: 11px;">
                                                    ✕ {{ __('Reject') }}
                                                </button>
                                            </div>
                                        @else
                                            <span style="font-size: 11px; color: var(--text-muted);">—</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" style="text-align: center; padding: 32px; color: var(--text-muted);">
                                        {{ __('No timesheets submitted for review.') }}
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- 11. TEAM WORKLOAD TAB -->
        <div id="tab-workload" class="tab-view">
            <div class="page-header" style="margin-bottom: 24px;">
                <h1 class="page-title" style="font-size: 22px; font-weight: 900; color: var(--text-primary); margin-bottom: 4px;">👥 {{ __('Team Capacity & Workload Matrix') }}</h1>
                <p class="page-subtitle" style="font-size: 13px; color: var(--text-secondary);">{{ __('Monitor weekly employee availability, assigned hours, and capacity utilization.') }}</p>
            </div>

            <!-- Team Capacity Table -->
            <div class="card" style="border-radius: var(--radius-lg); overflow: hidden; padding: 0;">
                <div style="padding: 20px 24px; border-bottom: 1px solid var(--border-color); background: var(--bg-surface);">
                    <h3 style="font-size: 16px; font-weight: 900; color: var(--text-primary);">📊 {{ __('Employee Workload Distribution') }}</h3>
                </div>
                <div style="overflow-x: auto;">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>{{ __('Team Member') }}</th>
                                <th>{{ __('Role') }}</th>
                                <th>{{ __('Weekly Capacity') }}</th>
                                <th>{{ __('Assigned Tasks') }}</th>
                                <th>{{ __('Estimated Hours') }}</th>
                                <th>{{ __('Capacity Utilization') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($members as $m)
                                @php
                                    $capacity = $m->weekly_capacity_hours ?? 40.00;
                                    $memberTasks = $tasks->where('assignee_id', $m->user_id)->where('status', '!=', 'done');
                                    $assignedHours = $memberTasks->sum('estimated_hours');
                                    $utilization = ($capacity > 0) ? round(($assignedHours / $capacity) * 100) : 0;
                                @endphp
                                <tr>
                                    <td>
                                        <div style="display: flex; align-items: center; gap: 10px;">
                                            <div style="width: 32px; height: 32px; border-radius: 10px; background: var(--accent-gradient); color: white; display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 12px; box-shadow: var(--shadow-soft-3d);">
                                                {{ strtoupper(substr($m->user->name ?? 'M', 0, 2)) }}
                                            </div>
                                            <div>
                                                <div style="font-weight: 800; color: var(--text-primary);">{{ $m->user->name ?? 'Member' }}</div>
                                                <div style="font-size: 11px; color: var(--text-muted);">{{ $m->user->email }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td><span class="nav-badge-pill" style="font-weight: 700;">{{ $m->role->name ?? 'Member' }}</span></td>
                                    <td style="font-weight: 800; font-family: monospace;">{{ $capacity }}h / wk</td>
                                    <td style="font-weight: 700;">{{ $memberTasks->count() }} {{ __('active') }}</td>
                                    <td style="font-weight: 800; color: var(--brand-forest); font-family: monospace;">{{ $assignedHours }}h</td>
                                    <td style="min-width: 180px;">
                                        <div style="display: flex; justify-content: space-between; font-size: 11px; margin-bottom: 5px; font-weight: 800;">
                                            <span style="color: {{ $utilization > 100 ? '#D96B5F' : ($utilization > 80 ? '#D6A23A' : '#4F9B5F') }};">{{ $utilization }}%</span>
                                            <span style="color: var(--text-muted);">{{ $assignedHours }} / {{ $capacity }}h</span>
                                        </div>
                                        <div class="progress-bar-bg" style="background: var(--bg-surface-subtle); height: 7px; border-radius: 9999px; overflow: hidden;">
                                            <div class="progress-bar-fill" style="width: {{ min(100, $utilization) }}%; height: 100%; background: {{ $utilization > 100 ? '#D96B5F' : ($utilization > 80 ? '#D6A23A' : '#4F9B5F') }}; border-radius: 9999px;"></div>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </main>

    <!-- Modal: New Project -->
    <div id="new-project-modal" class="modal-overlay">
        <div class="modal-card" style="max-width: 540px; border-radius: 20px; padding: 24px;">
            <div class="modal-header" style="margin-bottom: 20px; display: flex; justify-content: space-between; align-items: center;">
                <h3 class="modal-title" style="font-size: 18px; font-weight: 900; color: var(--text-primary);">📁 {{ __('Create New Project') }}</h3>
                <button onclick="closeNewProjectModal()" class="modal-close" style="background: var(--bg-surface-subtle); border: 1px solid var(--border-color); width: 32px; height: 32px; border-radius: 50%; display: flex; align-items: center; justify-content: center; cursor: pointer; color: var(--text-primary); font-weight: 800;">✕</button>
            </div>
            <form id="new-project-form" onsubmit="createProjectSubmit(event)" style="display: flex; flex-direction: column; gap: 14px;">
                <div>
                    <label style="display: block; font-size: 12px; font-weight: 700; color: var(--text-secondary); margin-bottom: 6px;">{{ __('Project Name') }} *</label>
                    <input type="text" name="name" required placeholder="e.g. Mobile App Redesign, Cloud Migration" style="width: 100%; background: var(--bg-surface-subtle); border: 1px solid var(--border-color); border-radius: 10px; padding: 10px 14px; color: var(--text-primary); outline: none; font-size: 13px; font-weight: 600; box-shadow: var(--shadow-inset-3d);">
                </div>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                    <div>
                        <label style="display: block; font-size: 12px; font-weight: 700; color: var(--text-secondary); margin-bottom: 6px;">{{ __('Project Code') }}</label>
                        <input type="text" name="code" placeholder="e.g. MOB-01" style="width: 100%; background: var(--bg-surface-subtle); border: 1px solid var(--border-color); border-radius: 10px; padding: 10px 14px; color: var(--text-primary); outline: none; font-size: 13px; font-weight: 600; box-shadow: var(--shadow-inset-3d);">
                    </div>
                    <div>
                        <label style="display: block; font-size: 12px; font-weight: 700; color: var(--text-secondary); margin-bottom: 6px;">{{ __('Priority') }}</label>
                        <select name="priority" style="width: 100%; background: var(--bg-surface-subtle); border: 1px solid var(--border-color); border-radius: 10px; padding: 10px 14px; color: var(--text-primary); outline: none; font-size: 13px; font-weight: 600;">
                            <option value="medium">{{ __('Medium') }}</option>
                            <option value="low">{{ __('Low') }}</option>
                            <option value="high">{{ __('High') }}</option>
                            <option value="urgent">{{ __('Urgent') }}</option>
                        </select>
                    </div>
                </div>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                    <div>
                        <label style="display: block; font-size: 12px; font-weight: 700; color: var(--text-secondary); margin-bottom: 6px;">{{ __('Project Manager') }}</label>
                        <select name="manager_id" style="width: 100%; background: var(--bg-surface-subtle); border: 1px solid var(--border-color); border-radius: 10px; padding: 10px 14px; color: var(--text-primary); outline: none; font-size: 13px; font-weight: 600;">
                            <option value="">— {{ __('Select Manager') }} —</option>
                            @foreach($members as $m)
                                <option value="{{ $m->user_id }}">{{ $m->user->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label style="display: block; font-size: 12px; font-weight: 700; color: var(--text-secondary); margin-bottom: 6px;">{{ __('Department') }}</label>
                        <select name="department_id" style="width: 100%; background: var(--bg-surface-subtle); border: 1px solid var(--border-color); border-radius: 10px; padding: 10px 14px; color: var(--text-primary); outline: none; font-size: 13px; font-weight: 600;">
                            <option value="">— {{ __('No Department') }} —</option>
                            @foreach($departments as $d)
                                <option value="{{ $d->id }}">{{ $d->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                    <div>
                        <label style="display: block; font-size: 12px; font-weight: 700; color: var(--text-secondary); margin-bottom: 6px;">{{ __('Budget ($)') }}</label>
                        <input type="number" step="0.01" name="budget_amount" placeholder="10000" style="width: 100%; background: var(--bg-surface-subtle); border: 1px solid var(--border-color); border-radius: 10px; padding: 10px 14px; color: var(--text-primary); outline: none; font-size: 13px; font-weight: 600; box-shadow: var(--shadow-inset-3d);">
                    </div>
                    <div>
                        <label style="display: block; font-size: 12px; font-weight: 700; color: var(--text-secondary); margin-bottom: 6px;">{{ __('Planned Hours') }}</label>
                        <input type="number" step="0.5" name="planned_hours" placeholder="160" style="width: 100%; background: var(--bg-surface-subtle); border: 1px solid var(--border-color); border-radius: 10px; padding: 10px 14px; color: var(--text-primary); outline: none; font-size: 13px; font-weight: 600; box-shadow: var(--shadow-inset-3d);">
                    </div>
                </div>
                <div>
                    <label style="display: block; font-size: 12px; font-weight: 700; color: var(--text-secondary); margin-bottom: 6px;">{{ __('Due Date') }}</label>
                    <input type="date" name="due_date" style="width: 100%; background: var(--bg-surface-subtle); border: 1px solid var(--border-color); border-radius: 10px; padding: 10px 14px; color: var(--text-primary); outline: none; font-size: 13px; font-weight: 600; box-shadow: var(--shadow-inset-3d);">
                </div>
                <div>
                    <label style="display: block; font-size: 12px; font-weight: 700; color: var(--text-secondary); margin-bottom: 6px;">{{ __('Description') }}</label>
                    <textarea name="description" rows="2" placeholder="Brief project summary..." style="width: 100%; background: var(--bg-surface-subtle); border: 1px solid var(--border-color); border-radius: 10px; padding: 10px 14px; color: var(--text-primary); outline: none; font-size: 13px; font-weight: 600; box-shadow: var(--shadow-inset-3d);"></textarea>
                </div>
                <button type="submit" class="tactile-btn btn-primary" style="margin-top: 8px; padding: 12px; font-size: 14px; justify-content: center; width: 100%;">
                    🚀 {{ __('Create Project') }}
                </button>
            </form>
        </div>
    </div>

    <!-- Modal: New Task -->
    <div id="new-task-modal" class="modal-overlay">
        <div class="modal-card" style="max-width: 540px; border-radius: 20px; padding: 24px;">
            <div class="modal-header" style="margin-bottom: 20px; display: flex; justify-content: space-between; align-items: center;">
                <h3 class="modal-title" style="font-size: 18px; font-weight: 900; color: var(--text-primary);">✅ {{ __('Create New Task') }}</h3>
                <button onclick="closeNewTaskModal()" class="modal-close" style="background: var(--bg-surface-subtle); border: 1px solid var(--border-color); width: 32px; height: 32px; border-radius: 50%; display: flex; align-items: center; justify-content: center; cursor: pointer; color: var(--text-primary); font-weight: 800;">✕</button>
            </div>
            <form id="new-task-form" onsubmit="createTaskSubmit(event)" style="display: flex; flex-direction: column; gap: 14px;">
                <div>
                    <label style="display: block; font-size: 12px; font-weight: 700; color: var(--text-secondary); margin-bottom: 6px;">{{ __('Project') }} *</label>
                    <select name="project_id" required style="width: 100%; background: var(--bg-surface-subtle); border: 1px solid var(--border-color); border-radius: 10px; padding: 10px 14px; color: var(--text-primary); outline: none; font-size: 13px; font-weight: 600;">
                        @foreach($projects as $p)
                            <option value="{{ $p->id }}">📁 {{ $p->name }} ({{ $p->code }})</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label style="display: block; font-size: 12px; font-weight: 700; color: var(--text-secondary); margin-bottom: 6px;">{{ __('Task Title') }} *</label>
                    <input type="text" name="title" required placeholder="e.g. Implement authentication middleware" style="width: 100%; background: var(--bg-surface-subtle); border: 1px solid var(--border-color); border-radius: 10px; padding: 10px 14px; color: var(--text-primary); outline: none; font-size: 13px; font-weight: 600; box-shadow: var(--shadow-inset-3d);">
                </div>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                    <div>
                        <label style="display: block; font-size: 12px; font-weight: 700; color: var(--text-secondary); margin-bottom: 6px;">{{ __('Assignee') }}</label>
                        <select name="assignee_id" style="width: 100%; background: var(--bg-surface-subtle); border: 1px solid var(--border-color); border-radius: 10px; padding: 10px 14px; color: var(--text-primary); outline: none; font-size: 13px; font-weight: 600;">
                            <option value="">— {{ __('Unassigned') }} —</option>
                            @foreach($members as $m)
                                <option value="{{ $m->user_id }}">{{ $m->user->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label style="display: block; font-size: 12px; font-weight: 700; color: var(--text-secondary); margin-bottom: 6px;">{{ __('Priority') }}</label>
                        <select name="priority" style="width: 100%; background: var(--bg-surface-subtle); border: 1px solid var(--border-color); border-radius: 10px; padding: 10px 14px; color: var(--text-primary); outline: none; font-size: 13px; font-weight: 600;">
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
                        <input type="number" step="0.5" name="estimated_hours" placeholder="4.0" style="width: 100%; background: var(--bg-surface-subtle); border: 1px solid var(--border-color); border-radius: 10px; padding: 10px 14px; color: var(--text-primary); outline: none; font-size: 13px; font-weight: 600; box-shadow: var(--shadow-inset-3d);">
                    </div>
                    <div>
                        <label style="display: block; font-size: 12px; font-weight: 700; color: var(--text-secondary); margin-bottom: 6px;">{{ __('Due Date') }}</label>
                        <input type="date" name="due_date" style="width: 100%; background: var(--bg-surface-subtle); border: 1px solid var(--border-color); border-radius: 10px; padding: 10px 14px; color: var(--text-primary); outline: none; font-size: 13px; font-weight: 600; box-shadow: var(--shadow-inset-3d);">
                    </div>
                </div>
                <button type="submit" class="tactile-btn btn-primary" style="margin-top: 8px; padding: 12px; font-size: 14px; justify-content: center; width: 100%;">
                    💾 {{ __('Create Task') }}
                </button>
            </form>
        </div>
    </div>

    <!-- Modal: Schedule Meeting (General or Project) -->
    <div id="schedule-meeting-modal" class="modal-overlay">
        <div class="modal-card" style="max-width: 600px; border-radius: 20px; padding: 24px; max-height: 90vh; overflow-y: auto;">
            <div class="modal-header" style="margin-bottom: 20px; display: flex; justify-content: space-between; align-items: center;">
                <h3 class="modal-title" style="font-size: 18px; font-weight: 900; color: var(--text-primary);">📅 {{ __('Schedule Meeting & Sync Attendees') }}</h3>
                <button onclick="closeScheduleMeetingModal()" class="modal-close" style="background: var(--bg-surface-subtle); border: 1px solid var(--border-color); width: 32px; height: 32px; border-radius: 50%; display: flex; align-items: center; justify-content: center; cursor: pointer; color: var(--text-primary); font-weight: 800;">✕</button>
            </div>

            <form id="schedule-meeting-form" onsubmit="scheduleMeetingSubmit(event)" method="POST" action="{{ route('meetings.schedule') }}" style="display: flex; flex-direction: column; gap: 14px;">
                @csrf

                <!-- Meeting Scope Switcher -->
                <div>
                    <label style="display: block; font-size: 12px; font-weight: 700; color: var(--text-secondary); margin-bottom: 6px;">{{ __('Meeting Scope') }} *</label>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px; background: var(--bg-surface-subtle); padding: 4px; border-radius: 12px; border: 1px solid var(--border-color);">
                        <label id="lbl-scope-general" style="display: flex; align-items: center; justify-content: center; gap: 6px; padding: 8px; border-radius: 8px; font-size: 12px; font-weight: 800; cursor: pointer; background: var(--bg-surface); color: var(--brand-forest); box-shadow: var(--shadow-soft-3d);">
                            <input type="radio" name="scope" value="general" checked onchange="toggleMeetingScope('general')" style="display: none;">
                            <span>🌐 {{ __('General Meeting') }}</span>
                        </label>
                        <label id="lbl-scope-project" style="display: flex; align-items: center; justify-content: center; gap: 6px; padding: 8px; border-radius: 8px; font-size: 12px; font-weight: 800; cursor: pointer; color: var(--text-secondary);">
                            <input type="radio" name="scope" value="project" onchange="toggleMeetingScope('project')" style="display: none;">
                            <span>📁 {{ __('Project Team Meeting') }}</span>
                        </label>
                    </div>
                </div>

                <!-- Project Selector (Shown when scope is project) -->
                <div id="meeting-project-field" style="display: none;">
                    <label style="display: block; font-size: 12px; font-weight: 700; color: var(--text-secondary); margin-bottom: 6px;">{{ __('Target Project') }} *</label>
                    <select name="project_id" id="meeting-project-select" onchange="renderProjectAttendeesList(this.value)" style="width: 100%; background: var(--bg-surface-subtle); border: 1px solid var(--border-color); border-radius: 10px; padding: 10px 14px; color: var(--text-primary); outline: none; font-size: 13px; font-weight: 600;">
                        <option value="">— {{ __('Select Project') }} —</option>
                        @foreach($projects as $p)
                            <option value="{{ $p->id }}">📁 {{ $p->name }} ({{ $p->code }})</option>
                        @endforeach
                    </select>
                    
                    <!-- Project Team Members Checklist (Only Project-related Members) -->
                    <div id="project-attendees-selection-box" style="margin-top: 10px; display: none;">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 6px;">
                            <label style="font-size: 12px; font-weight: 700; color: var(--text-secondary);">
                                👥 {{ __('Select Project Members to Attend') }}
                            </label>
                            <button type="button" onclick="toggleAllProjectAttendees()" style="background: none; border: none; font-size: 11px; font-weight: 800; color: var(--brand-forest); cursor: pointer;">
                                ✓ {{ __('Select / Unselect All') }}
                            </button>
                        </div>
                        <div id="project-attendees-list" style="max-height: 140px; overflow-y: auto; background: var(--bg-surface-subtle); border: 1px solid var(--border-color); border-radius: 10px; padding: 8px 12px; display: flex; flex-direction: column; gap: 6px;"></div>
                    </div>
                </div>

                <!-- Meeting Title -->
                <div>
                    <label style="display: block; font-size: 12px; font-weight: 700; color: var(--text-secondary); margin-bottom: 6px;">{{ __('Meeting Title') }} *</label>
                    <input type="text" name="title" required placeholder="e.g. Weekly Strategy Sync, Milestone Review" style="width: 100%; background: var(--bg-surface-subtle); border: 1px solid var(--border-color); border-radius: 10px; padding: 10px 14px; color: var(--text-primary); outline: none; font-size: 13px; font-weight: 600; box-shadow: var(--shadow-inset-3d);">
                </div>

                <!-- Meeting Description -->
                <div>
                    <label style="display: block; font-size: 12px; font-weight: 700; color: var(--text-secondary); margin-bottom: 6px;">{{ __('Description / Agenda') }}</label>
                    <textarea name="description" rows="2" placeholder="Brief outline of topics to discuss..." style="width: 100%; background: var(--bg-surface-subtle); border: 1px solid var(--border-color); border-radius: 10px; padding: 10px 14px; color: var(--text-primary); outline: none; font-size: 13px; font-weight: 500; resize: vertical; box-shadow: var(--shadow-inset-3d);"></textarea>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                    <!-- Room Selection -->
                    <div>
                        <label style="display: block; font-size: 12px; font-weight: 700; color: var(--text-secondary); margin-bottom: 6px;">{{ __('Meeting Room') }}</label>
                        <select name="room_id" style="width: 100%; background: var(--bg-surface-subtle); border: 1px solid var(--border-color); border-radius: 10px; padding: 10px 14px; color: var(--text-primary); outline: none; font-size: 13px; font-weight: 600;">
                            @foreach($rooms as $r)
                                <option value="{{ $r->id }}">🚪 {{ $r->name }} ({{ ucfirst($r->type) }})</option>
                            @endforeach
                        </select>
                    </div>
                    <!-- Duration -->
                    <div>
                        <label style="display: block; font-size: 12px; font-weight: 700; color: var(--text-secondary); margin-bottom: 6px;">{{ __('Duration') }}</label>
                        <select name="duration_minutes" style="width: 100%; background: var(--bg-surface-subtle); border: 1px solid var(--border-color); border-radius: 10px; padding: 10px 14px; color: var(--text-primary); outline: none; font-size: 13px; font-weight: 600;">
                            <option value="15">15 {{ __('Minutes') }}</option>
                            <option value="30" selected>30 {{ __('Minutes') }}</option>
                            <option value="45">45 {{ __('Minutes') }}</option>
                            <option value="60">1 {{ __('Hour') }}</option>
                            <option value="90">1.5 {{ __('Hours') }}</option>
                            <option value="120">2 {{ __('Hours') }}</option>
                        </select>
                    </div>
                </div>

                <!-- Date & Time -->
                <div>
                    <label style="display: block; font-size: 12px; font-weight: 700; color: var(--text-secondary); margin-bottom: 6px;">{{ __('Scheduled Date & Time') }} *</label>
                    <input type="datetime-local" name="scheduled_at" id="meeting-scheduled-at-input" required style="width: 100%; background: var(--bg-surface-subtle); border: 1px solid var(--border-color); border-radius: 10px; padding: 10px 14px; color: var(--text-primary); outline: none; font-size: 13px; font-weight: 600; box-shadow: var(--shadow-inset-3d);">
                </div>

                <!-- General Attendees Selection (Shown when scope is general) -->
                <div id="meeting-general-attendees-field">
                    <label style="display: block; font-size: 12px; font-weight: 700; color: var(--text-secondary); margin-bottom: 6px;">
                        👥 {{ __('Select Attendees to Invite') }}
                    </label>
                    <div style="max-height: 140px; overflow-y: auto; background: var(--bg-surface-subtle); border: 1px solid var(--border-color); border-radius: 10px; padding: 8px 12px; display: flex; flex-direction: column; gap: 6px;">
                        @foreach($members as $m)
                            @if($m->user_id !== $user->id)
                                <label style="display: flex; align-items: center; justify-content: space-between; font-size: 12px; color: var(--text-primary); cursor: pointer; padding: 4px 6px; border-radius: 6px; transition: background 0.2s;" onmouseover="this.style.background='var(--bg-surface)'" onmouseout="this.style.background='transparent'">
                                    <span style="display: flex; align-items: center; gap: 8px;">
                                        <input type="checkbox" name="attendee_ids[]" value="{{ $m->user_id }}" style="accent-color: var(--brand-forest);">
                                        <strong>{{ $m->user->name }}</strong>
                                        <span style="font-size: 11px; color: var(--text-muted);">({{ $m->user->email }})</span>
                                    </span>
                                    <span class="nav-badge-pill" style="font-size: 10px;">{{ $m->role->name ?? 'Member' }}</span>
                                </label>
                            @endif
                        @endforeach
                    </div>
                </div>

                <div style="background: var(--bg-surface-subtle); border: 1px solid var(--border-color); border-radius: 12px; padding: 12px; font-size: 11px; color: var(--text-secondary); display: flex; align-items: center; gap: 8px;">
                    <span style="font-size: 18px;">🔔</span>
                    <span>{{ __('Email invitations with direct Join links will be dispatched automatically, and all attendees will receive sound chime alerts before the session starts.') }}</span>
                </div>

                <button type="submit" class="tactile-btn btn-primary" style="margin-top: 6px; padding: 12px; font-size: 14px; justify-content: center; width: 100%;">
                    🚀 {{ __('Schedule Meeting & Dispatch Invitations') }}
                </button>
            </form>
        </div>
    </div>

    <!-- Modal: Manual Time Entry -->
    <div id="manual-time-modal" class="modal-overlay">
        <div class="modal-card" style="max-width: 500px; border-radius: 20px; padding: 24px;">
            <div class="modal-header" style="margin-bottom: 20px; display: flex; justify-content: space-between; align-items: center;">
                <h3 class="modal-title" style="font-size: 18px; font-weight: 900; color: var(--text-primary);">✍️ {{ __('Log Manual Time Entry') }}</h3>
                <button onclick="closeManualTimeModal()" class="modal-close" style="background: var(--bg-surface-subtle); border: 1px solid var(--border-color); width: 32px; height: 32px; border-radius: 50%; display: flex; align-items: center; justify-content: center; cursor: pointer; color: var(--text-primary); font-weight: 800;">✕</button>
            </div>
            <form id="manual-time-form" onsubmit="logManualTimeSubmit(event)" style="display: flex; flex-direction: column; gap: 14px;">
                <div>
                    <label style="display: block; font-size: 12px; font-weight: 700; color: var(--text-secondary); margin-bottom: 6px;">{{ __('Project') }} *</label>
                    <select name="project_id" required style="width: 100%; background: var(--bg-surface-subtle); border: 1px solid var(--border-color); border-radius: 10px; padding: 10px 14px; color: var(--text-primary); outline: none; font-size: 13px; font-weight: 600;">
                        @foreach($projects as $p)
                            <option value="{{ $p->id }}">📁 {{ $p->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                    <div>
                        <label style="display: block; font-size: 12px; font-weight: 700; color: var(--text-secondary); margin-bottom: 6px;">{{ __('Start Time') }} *</label>
                        <input type="datetime-local" name="started_at" required style="width: 100%; background: var(--bg-surface-subtle); border: 1px solid var(--border-color); border-radius: 10px; padding: 10px 14px; color: var(--text-primary); outline: none; font-size: 12px; font-weight: 600; box-shadow: var(--shadow-inset-3d);">
                    </div>
                    <div>
                        <label style="display: block; font-size: 12px; font-weight: 700; color: var(--text-secondary); margin-bottom: 6px;">{{ __('End Time') }} *</label>
                        <input type="datetime-local" name="ended_at" required style="width: 100%; background: var(--bg-surface-subtle); border: 1px solid var(--border-color); border-radius: 10px; padding: 10px 14px; color: var(--text-primary); outline: none; font-size: 12px; font-weight: 600; box-shadow: var(--shadow-inset-3d);">
                    </div>
                </div>
                <div>
                    <label style="display: block; font-size: 12px; font-weight: 700; color: var(--text-secondary); margin-bottom: 6px;">{{ __('Description') }}</label>
                    <input type="text" name="description" placeholder="What did you work on?" style="width: 100%; background: var(--bg-surface-subtle); border: 1px solid var(--border-color); border-radius: 10px; padding: 10px 14px; color: var(--text-primary); outline: none; font-size: 13px; font-weight: 600; box-shadow: var(--shadow-inset-3d);">
                </div>
                <button type="submit" class="tactile-btn btn-primary" style="margin-top: 8px; padding: 12px; font-size: 14px; justify-content: center; width: 100%;">
                    ⏱️ {{ __('Log Time') }}
                </button>
            </form>
        </div>
    </div>

    <!-- Modal: Reject Timesheet -->
    <div id="reject-timesheet-modal" class="modal-overlay">
        <div class="modal-card" style="max-width: 480px; border-radius: 20px; padding: 24px;">
            <div class="modal-header" style="margin-bottom: 20px; display: flex; justify-content: space-between; align-items: center;">
                <h3 class="modal-title" style="font-size: 18px; font-weight: 900; color: var(--text-primary);">❌ {{ __('Reject Timesheet') }}</h3>
                <button onclick="closeRejectModal()" class="modal-close" style="background: var(--bg-surface-subtle); border: 1px solid var(--border-color); width: 32px; height: 32px; border-radius: 50%; display: flex; align-items: center; justify-content: center; cursor: pointer; color: var(--text-primary); font-weight: 800;">✕</button>
            </div>
            <form onsubmit="rejectTimesheetSubmit(event)" style="display: flex; flex-direction: column; gap: 14px;">
                <div>
                    <label style="display: block; font-size: 12px; font-weight: 700; color: var(--text-secondary); margin-bottom: 6px;">{{ __('Feedback Reason for Employee') }} *</label>
                    <textarea id="reject-reason-input" required rows="3" placeholder="Please clarify the 6 hours logged on Friday..." style="width: 100%; background: var(--bg-surface-subtle); border: 1px solid var(--border-color); border-radius: 10px; padding: 10px 14px; color: var(--text-primary); outline: none; font-size: 13px; font-weight: 600; box-shadow: var(--shadow-inset-3d);"></textarea>
                </div>
                <button type="submit" class="tactile-btn" style="margin-top: 8px; padding: 12px; font-size: 14px; justify-content: center; background: #D96B5F; color: white; width: 100%;">
                    ❌ {{ __('Confirm Rejection & Send Feedback') }}
                </button>
            </form>
        </div>
    </div>

    <!-- Modal: Project Hub & KPI Dashboard Drawer -->
    <div id="project-hub-modal" class="modal-overlay">
        <div class="modal-card" style="max-width: 1100px; width: 95vw; max-height: 90vh; display: flex; flex-direction: column; padding: 24px; overflow: hidden; border-radius: 24px;">
            <!-- Hub Header -->
            <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 16px; border-bottom: 1px solid var(--border-color); padding-bottom: 14px;">
                <div>
                    <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 6px; flex-wrap: wrap;">
                        <span id="hub-proj-code" class="nav-badge-pill" style="font-family: monospace; font-size: 12px;">PRJ-01</span>
                        <h2 id="hub-proj-name" style="font-size: 20px; font-weight: 900; margin: 0; color: var(--text-primary);">Project Name</h2>
                        <span id="hub-proj-status" class="nav-badge-pill" style="background: rgba(79, 155, 95, 0.15); color: #4F9B5F;">Active</span>
                        <span id="hub-proj-priority" class="nav-badge-pill" style="background: rgba(214, 162, 58, 0.15); color: #D6A23A;">High</span>
                    </div>
                    <div style="display: flex; align-items: center; gap: 16px; font-size: 12px; color: var(--text-muted); flex-wrap: wrap;">
                        <span>👤 {{ __('Manager') }}: <strong id="hub-proj-manager" style="color: var(--text-primary);">Name</strong></span>
                        <span>🏛️ {{ __('Department') }}: <strong id="hub-proj-dept" style="color: var(--text-primary);">Dept</strong></span>
                        <span>📅 {{ __('Due Date') }}: <strong id="hub-proj-due" style="color: var(--text-primary);">Date</strong></span>
                    </div>
                </div>
                <div style="display: flex; align-items: center; gap: 8px;">
                    <button onclick="scheduleMeetingForCurrentProject()" class="tactile-btn btn-secondary" style="padding: 8px 14px; font-size: 12px;">
                        <span>📅</span> {{ __('Schedule Meeting') }}
                    </button>
                    <button onclick="openNewTaskForCurrentProject()" class="tactile-btn btn-primary" style="padding: 8px 16px; font-size: 12px;">
                        <span>+</span> {{ __('Add Task') }}
                    </button>
                    <button onclick="closeProjectHub()" class="modal-close" style="background: var(--bg-surface-subtle); border: 1px solid var(--border-color); width: 32px; height: 32px; border-radius: 50%; display: flex; align-items: center; justify-content: center; cursor: pointer; color: var(--text-primary); font-weight: 800;">✕</button>
                </div>
            </div>

            <!-- Hub KPI Stats Bar (3D Soft Neumorphic) -->
            <div class="kpi-grid" style="grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 12px; margin-bottom: 16px;">
                <!-- Progress KPI -->
                <div class="kpi-card" style="padding: 14px;">
                    <div class="kpi-header">
                        <span class="kpi-title">{{ __('Progress') }}</span>
                        <div class="kpi-icon-box">📊</div>
                    </div>
                    <div id="hub-kpi-progress-pct" class="kpi-value" style="font-size: 20px; color: var(--brand-forest);">0%</div>
                    <div id="hub-kpi-tasks-ratio" style="font-size: 11px; color: var(--text-muted);">0 / 0 tasks done</div>
                </div>
                <!-- Hours & Effort KPI -->
                <div class="kpi-card" style="padding: 14px;">
                    <div class="kpi-header">
                        <span class="kpi-title">{{ __('Actual vs Planned') }}</span>
                        <div class="kpi-icon-box">⏱️</div>
                    </div>
                    <div id="hub-kpi-hours" class="kpi-value" style="font-size: 20px; color: var(--brand-forest);">0 / 0 h</div>
                    <div id="hub-kpi-hours-var" style="font-size: 11px; color: var(--text-muted);">Variance: 0h</div>
                </div>
                <!-- Financials & Margin KPI -->
                <div class="kpi-card" style="padding: 14px;">
                    <div class="kpi-header">
                        <span class="kpi-title">{{ __('Budget & Cost') }}</span>
                        <div class="kpi-icon-box">💰</div>
                    </div>
                    <div id="hub-kpi-budget" class="kpi-value" style="font-size: 20px; color: var(--brand-forest);">$0 / $0</div>
                    <div id="hub-kpi-margin" style="font-size: 11px; color: #4F9B5F;">Margin: $0 (0%)</div>
                </div>
                <!-- Health & Overdue KPI -->
                <div class="kpi-card" style="padding: 14px;">
                    <div class="kpi-header">
                        <span class="kpi-title">{{ __('Active & Overdue') }}</span>
                        <div class="kpi-icon-box">⚡</div>
                    </div>
                    <div id="hub-kpi-active-tasks" class="kpi-value" style="font-size: 20px; color: #D96B5F;">0 Active</div>
                    <div id="hub-kpi-overdue-tasks" style="font-size: 11px; color: #D96B5F;">0 Overdue</div>
                </div>
            </div>

            <!-- Hub Inner Navigation Tabs -->
            <div style="display: flex; gap: 8px; margin-bottom: 14px; background: var(--bg-surface-subtle); padding: 4px; border-radius: var(--radius-md); border: 1px solid var(--border-color); box-shadow: var(--shadow-inset-3d);">
                <button onclick="switchHubTab('kanban')" id="hub-tab-btn-kanban" class="tactile-btn btn-primary" style="flex: 1; padding: 8px; font-size: 12px; justify-content: center;">
                    📌 {{ __('Kanban Board') }}
                </button>
                <button onclick="switchHubTab('tasks')" id="hub-tab-btn-tasks" class="tactile-btn btn-secondary" style="flex: 1; padding: 8px; font-size: 12px; justify-content: center; background: transparent; border: none; box-shadow: none; color: var(--text-secondary);">
                    📋 {{ __('Task Table') }}
                </button>
                <button onclick="switchHubTab('timelog')" id="hub-tab-btn-timelog" class="tactile-btn btn-secondary" style="flex: 1; padding: 8px; font-size: 12px; justify-content: center; background: transparent; border: none; box-shadow: none; color: var(--text-secondary);">
                    ⏱️ {{ __('Time Entries Log') }}
                </button>
            </div>

            <!-- Hub Content Area (Scrollable) -->
            <div style="flex: 1; overflow-y: auto; padding-right: 4px;">
                <!-- 1. Kanban View -->
                <div id="hub-view-kanban" style="display: block;">
                    <div style="display: grid; grid-template-columns: repeat(5, minmax(200px, 1fr)); gap: 12px; align-items: start;">
                        <!-- Backlog -->
                        <div class="kanban-column" style="background: var(--bg-surface-subtle); border: 1px solid var(--border-color); border-radius: var(--radius-lg); padding: 12px;">
                            <div class="kanban-col-header" style="color: var(--text-secondary); display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px; font-weight: 800; font-size: 12px;">
                                <span>📌 Backlog</span>
                                <span id="col-count-backlog" class="nav-badge-pill">0</span>
                            </div>
                            <div id="kanban-col-backlog" style="display: flex; flex-direction: column; gap: 8px;"></div>
                        </div>
                        <!-- Ready -->
                        <div class="kanban-column" style="background: var(--bg-surface-subtle); border: 1px solid var(--border-color); border-radius: var(--radius-lg); padding: 12px;">
                            <div class="kanban-col-header" style="color: var(--brand-sage); display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px; font-weight: 800; font-size: 12px;">
                                <span>🎯 Ready</span>
                                <span id="col-count-ready" class="nav-badge-pill">0</span>
                            </div>
                            <div id="kanban-col-ready" style="display: flex; flex-direction: column; gap: 8px;"></div>
                        </div>
                        <!-- In Progress -->
                        <div class="kanban-column" style="background: var(--bg-surface-subtle); border: 1px solid var(--border-color); border-radius: var(--radius-lg); padding: 12px;">
                            <div class="kanban-col-header" style="color: var(--brand-forest); display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px; font-weight: 800; font-size: 12px;">
                                <span>⚡ In Progress</span>
                                <span id="col-count-in_progress" class="nav-badge-pill" style="background: rgba(79, 155, 95, 0.2); color: #4F9B5F;">0</span>
                            </div>
                            <div id="kanban-col-in_progress" style="display: flex; flex-direction: column; gap: 8px;"></div>
                        </div>
                        <!-- Review / QA -->
                        <div class="kanban-column" style="background: var(--bg-surface-subtle); border: 1px solid var(--border-color); border-radius: var(--radius-lg); padding: 12px;">
                            <div class="kanban-col-header" style="color: var(--status-warning); display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px; font-weight: 800; font-size: 12px;">
                                <span>🔍 Review / QA</span>
                                <span id="col-count-review" class="nav-badge-pill" style="background: rgba(214, 162, 58, 0.2); color: #D6A23A;">0</span>
                            </div>
                            <div id="kanban-col-review" style="display: flex; flex-direction: column; gap: 8px;"></div>
                        </div>
                        <!-- Done -->
                        <div class="kanban-column" style="background: var(--bg-surface-subtle); border: 1px solid var(--border-color); border-radius: var(--radius-lg); padding: 12px;">
                            <div class="kanban-col-header" style="color: var(--brand-forest); display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px; font-weight: 800; font-size: 12px;">
                                <span>🎉 Done</span>
                                <span id="col-count-done" class="nav-badge-pill" style="background: rgba(79, 155, 95, 0.2); color: #4F9B5F;">0</span>
                            </div>
                            <div id="kanban-col-done" style="display: flex; flex-direction: column; gap: 8px;"></div>
                        </div>
                    </div>
                </div>

                <!-- 2. Task Table View -->
                <div id="hub-view-tasks" style="display: none;">
                    <div style="overflow-x: auto;">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>{{ __('Task Title') }}</th>
                                    <th>{{ __('Assignee') }}</th>
                                    <th>{{ __('Status') }}</th>
                                    <th>{{ __('Priority') }}</th>
                                    <th>{{ __('Hours (Est/Act)') }}</th>
                                    <th>{{ __('Due Date') }}</th>
                                    <th>{{ __('Action') }}</th>
                                </tr>
                            </thead>
                            <tbody id="hub-task-table-body"></tbody>
                        </table>
                    </div>
                </div>

                <!-- 3. Time Log View -->
                <div id="hub-view-timelog" style="display: none;">
                    <div style="overflow-x: auto;">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>{{ __('Date') }}</th>
                                    <th>{{ __('Employee') }}</th>
                                    <th>{{ __('Task') }}</th>
                                    <th>{{ __('Duration') }}</th>
                                    <th>{{ __('Description') }}</th>
                                    <th>{{ __('Type') }}</th>
                                    <th>{{ __('Status') }}</th>
                                </tr>
                            </thead>
                            <tbody id="hub-timelog-table-body"></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal: Task Inspector & Activity Drawer -->
    <div id="task-details-modal" class="modal-overlay">
        <div class="modal-card" style="max-width: 850px; width: 95vw; max-height: 90vh; display: flex; flex-direction: column; padding: 24px; overflow: hidden; border-radius: 24px;">
            <!-- Header -->
            <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 14px; border-bottom: 1px solid var(--border-color); padding-bottom: 12px; flex-wrap: wrap; gap: 10px;">
                <div>
                    <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 4px; flex-wrap: wrap;">
                        <span id="task-modal-code" class="nav-badge-pill" style="font-family: monospace;">#1</span>
                        <h2 id="task-modal-title" style="font-size: 18px; font-weight: 900; margin: 0; color: var(--text-primary);">Task Title</h2>
                        <span id="task-modal-status-badge" class="nav-badge-pill" style="background: rgba(79, 155, 95, 0.2); color: #4F9B5F;">In Progress</span>
                        <span id="task-modal-priority-badge" class="nav-badge-pill" style="background: rgba(217, 107, 95, 0.2); color: #D96B5F;">Urgent</span>
                    </div>
                    <div style="display: flex; align-items: center; gap: 14px; font-size: 12px; color: var(--text-muted); flex-wrap: wrap;">
                        <span>📁 {{ __('Project') }}: <strong id="task-modal-project" style="color: var(--text-primary);">Project Name</strong></span>
                        <span onclick="if(window.currentModalTaskAssigneeMemberId) { closeTaskDetailsModal(); openMemberProfileModal(window.currentModalTaskAssigneeMemberId); }" style="cursor: pointer;" title="{{ __('Click to view member profile, tasks & hours') }}">👤 {{ __('Assignee') }}: <strong id="task-modal-assignee" style="color: var(--brand-forest); text-decoration: underline;">Assignee</strong></span>
                        <span>📅 {{ __('Due Date') }}: <strong id="task-modal-due" style="color: var(--text-primary);">Date</strong></span>
                    </div>
                </div>
                <div style="display: flex; align-items: center; gap: 10px;">
                    <button id="task-modal-timer-btn" class="tactile-btn" style="background: rgba(79, 155, 95, 0.15); color: var(--brand-forest); border: 1px solid rgba(79, 155, 95, 0.3); padding: 6px 14px; font-size: 12px;">
                        ▶ {{ __('Start Timer') }}
                    </button>
                    <button onclick="closeTaskDetailsModal()" class="modal-close" style="background: var(--bg-surface-subtle); border: 1px solid var(--border-color); width: 32px; height: 32px; border-radius: 50%; display: flex; align-items: center; justify-content: center; cursor: pointer; color: var(--text-primary); font-weight: 800;">✕</button>
                </div>
            </div>

            <!-- Task Quick Status Changer Bar & PM Approval Actions -->
            <div style="display: flex; flex-direction: column; gap: 8px; margin-bottom: 14px;">
                <div style="display: flex; justify-content: space-between; align-items: center; background: var(--bg-surface-subtle); padding: 10px 16px; border-radius: var(--radius-md); border: 1px solid var(--border-color); box-shadow: var(--shadow-inset-3d); flex-wrap: wrap; gap: 10px;">
                    <div style="display: flex; align-items: center; gap: 10px;">
                        <span style="font-size: 12px; font-weight: 800; color: var(--text-secondary);">⚡ {{ __('Status') }}:</span>
                        <select id="task-modal-status-select" onchange="updateCurrentTaskStatus(this.value)" style="background: var(--bg-surface); border: 1px solid var(--border-color); color: var(--text-primary); font-size: 12px; font-weight: 700; border-radius: 8px; padding: 5px 12px; outline: none;">
                            <option value="backlog">📌 {{ __('Backlog') }}</option>
                            <option value="ready">🎯 {{ __('Ready') }}</option>
                            <option value="in_progress">⚡ {{ __('In Progress') }}</option>
                            <option value="review">🔍 {{ __('In Review / QA') }}</option>
                            <option value="done">🎉 {{ __('Done / Completed') }}</option>
                        </select>
                    </div>
                    <div style="font-size: 12px; font-family: monospace; font-weight: 800; color: var(--brand-forest);">
                        ⏱️ <span id="task-modal-hours">0h / 0h</span>
                    </div>
                </div>

                <!-- Approval Status Alert & Action Box -->
                <div id="task-modal-approval-banner" style="display: none; padding: 12px 16px; border-radius: var(--radius-md); font-size: 12px; font-weight: 700; justify-content: space-between; align-items: center; gap: 12px; flex-wrap: wrap;">
                    <div id="task-modal-approval-text" style="display: flex; align-items: center; gap: 8px;"></div>
                    <div id="task-modal-approval-actions" style="display: flex; gap: 8px;"></div>
                </div>
            </div>

            <!-- Sub-Tabs Segmented Control -->
            <div class="task-modal-segmented-bar" style="display: flex; gap: 4px; margin-bottom: 16px; background: var(--bg-surface-subtle); padding: 4px; border-radius: 12px; border: 1px solid var(--border-color); box-shadow: var(--shadow-inset-3d); overflow-x: auto;">
                <button type="button" onclick="switchTaskInspectorTab('details')" id="task-tab-btn-details" class="tactile-btn btn-primary" style="flex: 1; min-width: 80px; padding: 8px 10px; font-size: 12px; justify-content: center; gap: 6px;">
                    <span>📝</span>
                    <span>{{ __('Details') }}</span>
                </button>
                <button type="button" onclick="switchTaskInspectorTab('checklist')" id="task-tab-btn-checklist" class="tactile-btn btn-secondary" style="flex: 1; min-width: 80px; padding: 8px 10px; font-size: 12px; justify-content: center; gap: 6px; background: transparent; border: none; box-shadow: none; color: var(--text-secondary);">
                    <span>☑️</span>
                    <span>{{ __('Checklist') }}</span>
                    <span id="task-checklist-count" style="font-size: 10px; background: rgba(36, 92, 58, 0.15); color: var(--brand-forest); padding: 1px 7px; border-radius: 9999px; font-weight: 800; font-family: monospace;">0</span>
                </button>
                <button type="button" onclick="switchTaskInspectorTab('attachments')" id="task-tab-btn-attachments" class="tactile-btn btn-secondary" style="flex: 1; min-width: 80px; padding: 8px 10px; font-size: 12px; justify-content: center; gap: 6px; background: transparent; border: none; box-shadow: none; color: var(--text-secondary);">
                    <span>📎</span>
                    <span>{{ __('Files') }}</span>
                    <span id="task-attachments-count" style="font-size: 10px; background: rgba(36, 92, 58, 0.15); color: var(--brand-forest); padding: 1px 7px; border-radius: 9999px; font-weight: 800; font-family: monospace;">0</span>
                </button>
                <button type="button" onclick="switchTaskInspectorTab('comments')" id="task-tab-btn-comments" class="tactile-btn btn-secondary" style="flex: 1; min-width: 80px; padding: 8px 10px; font-size: 12px; justify-content: center; gap: 6px; background: transparent; border: none; box-shadow: none; color: var(--text-secondary);">
                    <span>💬</span>
                    <span>{{ __('Discussions') }}</span>
                    <span id="task-comments-count" style="font-size: 10px; background: rgba(36, 92, 58, 0.15); color: var(--brand-forest); padding: 1px 7px; border-radius: 9999px; font-weight: 800; font-family: monospace;">0</span>
                </button>
                <button type="button" onclick="switchTaskInspectorTab('dependencies')" id="task-tab-btn-dependencies" class="tactile-btn btn-secondary" style="flex: 1; min-width: 80px; padding: 8px 10px; font-size: 12px; justify-content: center; gap: 6px; background: transparent; border: none; box-shadow: none; color: var(--text-secondary);">
                    <span>🔗</span>
                    <span>{{ __('Dependencies') }}</span>
                </button>
                <button type="button" onclick="switchTaskInspectorTab('timelog')" id="task-tab-btn-timelog" class="tactile-btn btn-secondary" style="flex: 1; min-width: 80px; padding: 8px 10px; font-size: 12px; justify-content: center; gap: 6px; background: transparent; border: none; box-shadow: none; color: var(--text-secondary);">
                    <span>⏱️</span>
                    <span>{{ __('Time Log') }}</span>
                </button>
            </div>

            <!-- Tab Contents -->
            <div style="flex: 1; overflow-y: auto; padding-right: 4px;">
                <!-- 1. Details -->
                <div id="task-inspector-details" style="display: block;">
                    <div style="margin-bottom: 14px;">
                        <label style="display: block; font-size: 11px; font-weight: 800; color: var(--text-secondary); text-transform: uppercase; margin-bottom: 6px;">{{ __('Description') }}</label>
                        <div id="task-modal-description" style="background: var(--bg-surface-subtle); padding: 14px; border-radius: 12px; font-size: 13px; color: var(--text-primary); line-height: 1.5; border: 1px solid var(--border-color); box-shadow: var(--shadow-inset-3d);">
                            —
                        </div>
                    </div>
                </div>

                <!-- 2. Checklist -->
                <div id="task-inspector-checklist" style="display: none;">
                    <form onsubmit="addTaskChecklistItem(event)" style="display: flex; gap: 8px; margin-bottom: 14px;">
                        <input type="text" id="new-checklist-title-input" required placeholder="{{ __('Add checklist sub-item (e.g. Write unit tests, create migration)...') }}" style="flex: 1; background: var(--bg-surface-subtle); border: 1px solid var(--border-color); border-radius: 10px; padding: 9px 12px; color: var(--text-primary); outline: none; font-size: 12px; font-weight: 600; box-shadow: var(--shadow-inset-3d);">
                        <button type="submit" class="tactile-btn btn-primary" style="padding: 8px 16px; font-size: 12px;">
                            <span>+</span> {{ __('Add Item') }}
                        </button>
                    </form>
                    <div id="task-checklist-items-container" style="display: flex; flex-direction: column; gap: 6px;"></div>
                </div>

                <!-- 3. Attachments & Files -->
                <div id="task-inspector-attachments" style="display: none;">
                    <form onsubmit="uploadTaskAttachmentSubmit(event)" style="background: var(--bg-surface-subtle); border: 1px dashed var(--border-color); border-radius: 12px; padding: 16px; text-align: center; margin-bottom: 14px;">
                        <div style="font-size: 24px; margin-bottom: 6px;">📎</div>
                        <div style="font-size: 12px; font-weight: 800; color: var(--text-primary); margin-bottom: 8px;">{{ __('Upload Document or Attachment to Task') }}</div>
                        <div style="display: flex; justify-content: center; gap: 8px; align-items: center; max-width: 420px; margin: 0 auto;">
                            <input type="file" id="task-file-input" required style="font-size: 12px; color: var(--text-primary);">
                            <button type="submit" class="tactile-btn btn-primary" style="padding: 6px 14px; font-size: 12px;">
                                📤 {{ __('Upload') }}
                            </button>
                        </div>
                    </form>
                    <div id="task-attachments-list-container" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(220px, 1fr)); gap: 10px;"></div>
                </div>

                <!-- 4. Comments & Mentions -->
                <div id="task-inspector-comments" style="display: none;">
                    <div id="task-comments-feed" style="display: flex; flex-direction: column; gap: 10px; margin-bottom: 14px; max-height: 280px; overflow-y: auto;"></div>
                    
                    <!-- Quick Mention Suggestion Chips -->
                    <div style="display: flex; align-items: center; gap: 6px; margin-bottom: 8px; flex-wrap: wrap;">
                        <span style="font-size: 11px; font-weight: 800; color: var(--text-secondary);">@ {{ __('Mention') }}:</span>
                        @foreach($members->take(6) as $chipMember)
                            @if($chipMember->user_id !== $user->id)
                                <button type="button" onclick="insertMentionHandle('{{ $chipMember->user->name }}')" class="nav-badge-pill" style="cursor: pointer; font-size: 10px; border: 1px solid var(--border-color); background: var(--bg-surface-subtle); color: var(--brand-forest); font-weight: 700;" title="{{ __('Click to mention :name', ['name' => $chipMember->user->name]) }}">
                                    @<span>{{ $chipMember->user->name }}</span>
                                </button>
                            @endif
                        @endforeach
                    </div>

                    <form onsubmit="addTaskCommentSubmit(event)" style="display: flex; gap: 8px;">
                        <input type="text" id="new-comment-body-input" required placeholder="{{ __('Write a comment or status update... Type @name to mention') }}" style="flex: 1; background: var(--bg-surface-subtle); border: 1px solid var(--border-color); border-radius: 10px; padding: 9px 12px; color: var(--text-primary); outline: none; font-size: 12px; font-weight: 600; box-shadow: var(--shadow-inset-3d);">
                        <button type="submit" class="tactile-btn btn-primary" style="padding: 8px 16px; font-size: 12px;">
                            💬 {{ __('Post') }}
                        </button>
                    </form>
                </div>

                <!-- 5. Dependencies -->
                <div id="task-inspector-dependencies" style="display: none;">
                    <div style="background: var(--bg-surface-subtle); padding: 14px; border-radius: 12px; margin-bottom: 14px; border: 1px solid var(--border-color); box-shadow: var(--shadow-inset-3d);">
                        <label style="display: block; font-size: 11px; font-weight: 800; color: var(--text-secondary); text-transform: uppercase; margin-bottom: 6px;">🔗 {{ __('Add Predecessor / Blocker Task') }}</label>
                        <form onsubmit="addTaskDependencySubmit(event)" style="display: flex; gap: 8px;">
                            <select id="dependency-blocker-select" required style="flex: 1; background: var(--bg-surface); border: 1px solid var(--border-color); border-radius: 8px; padding: 8px 12px; color: var(--text-primary); font-size: 12px; font-weight: 600;">
                                <option value="">— {{ __('Select Blocker Task') }} —</option>
                                @foreach($tasks as $oth)
                                    <option value="{{ $oth->id }}">#{{ $oth->task_number }} {{ $oth->title }} ({{ $oth->project->code ?? 'PRJ' }})</option>
                                @endforeach
                            </select>
                            <button type="submit" class="tactile-btn btn-primary" style="padding: 8px 16px; font-size: 12px;">
                                <span>+</span> {{ __('Add Blocker') }}
                            </button>
                        </form>
                    </div>
                    <div id="task-dependencies-container" style="display: flex; flex-direction: column; gap: 6px;"></div>
                </div>

                <!-- 6. Time Log -->
                <div id="task-inspector-timelog" style="display: none;">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>{{ __('Date') }}</th>
                                <th>{{ __('Member') }}</th>
                                <th>{{ __('Duration') }}</th>
                                <th>{{ __('Description') }}</th>
                                <th>{{ __('Status') }}</th>
                            </tr>
                        </thead>
                        <tbody id="task-modal-timelog-body"></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Comprehensive Team Member Profile Modal -->
    <div id="member-details-modal" class="modal" style="display: none; align-items: center; justify-content: center; z-index: 9999;">
        <div class="modal-box" style="max-width: 860px; width: 95%; max-height: 90vh; display: flex; flex-direction: column; padding: 0; overflow: hidden; border-radius: var(--radius-xl); box-shadow: var(--shadow-modal-3d); border: 1px solid var(--border-color); background: var(--bg-surface);">
            
            <!-- Modal Hero Header -->
            <div style="background: linear-gradient(135deg, rgba(79, 155, 95, 0.12) 0%, rgba(36, 92, 58, 0.22) 100%); padding: 24px; border-bottom: 1px solid var(--border-color); position: relative;">
                <button onclick="closeMemberProfileModal()" style="position: absolute; top: 16px; inset-inline-end: 16px; width: 32px; height: 32px; border-radius: 50%; background: var(--bg-surface); border: 1px solid var(--border-color); color: var(--text-secondary); font-size: 16px; font-weight: 800; cursor: pointer; display: flex; align-items: center; justify-content: center; box-shadow: var(--shadow-soft-3d); transition: all 0.2s;">✕</button>

                <div style="display: flex; align-items: center; gap: 20px; flex-wrap: wrap;">
                    <!-- Avatar -->
                    <div id="mp-avatar-container" style="position: relative; width: 76px; height: 76px; border-radius: 20px; background: var(--accent-gradient); border: 3px solid #FFFDF6; box-shadow: 0 10px 25px rgba(36, 92, 58, 0.25); display: flex; align-items: center; justify-content: center; font-size: 26px; font-weight: 900; color: white; flex-shrink: 0; overflow: hidden;">
                        <img id="mp-avatar-img" src="" alt="Avatar" style="width: 100%; height: 100%; object-fit: cover; display: none;">
                        <span id="mp-avatar-fallback">AB</span>
                        <div style="position: absolute; bottom: -2px; inset-inline-end: -2px; width: 16px; height: 16px; border-radius: 50%; background: #4F9B5F; border: 3px solid #FFFDF6;" title="Online"></div>
                    </div>

                    <!-- Details -->
                    <div style="flex: 1; min-width: 200px;">
                        <div style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
                            <h2 id="mp-user-name" style="font-size: 22px; font-weight: 900; color: var(--text-primary); margin: 0;">Member Name</h2>
                            <span id="mp-user-nickname" class="nav-badge-pill" style="font-family: monospace; font-size: 11px;">@nickname</span>
                            <span id="mp-user-role" class="nav-badge-pill" style="background: rgba(79, 155, 95, 0.15); color: #4F9B5F; font-size: 11px;">Employee</span>
                        </div>
                        <div style="display: flex; align-items: center; gap: 8px; margin-top: 6px; flex-wrap: wrap; font-size: 12px; color: var(--text-secondary);">
                            <span id="mp-job-title" style="font-weight: 700; color: var(--text-primary);">Senior Engineer</span>
                            <span>•</span>
                            <span id="mp-dept-team">Engineering Team</span>
                            <span>•</span>
                            <span id="mp-work-mode" class="nav-badge-pill" style="font-size: 10px;">🏠 Remote</span>
                        </div>
                    </div>

                    <!-- Direct Chat Action -->
                    <div style="flex-shrink: 0;">
                        <button id="mp-chat-btn" onclick="openChatFromProfileModal()" class="tactile-btn btn-primary" style="padding: 10px 20px; font-size: 13px;">
                            <span>💬</span> {{ __('Send Message') }}
                        </button>
                    </div>
                </div>
            </div>

            <!-- Profile Nav Tabs -->
            <div style="display: flex; border-bottom: 1px solid var(--border-color); background: var(--bg-surface-subtle); padding: 0 16px;">
                <button onclick="switchMemberProfileTab('about')" id="mp-tab-btn-about" class="member-profile-tab-btn active" style="padding: 14px 18px; font-size: 13px; font-weight: 800; border: none; background: transparent; cursor: pointer; color: var(--brand-forest); border-bottom: 3px solid var(--brand-forest); transition: all 0.2s;">
                    👤 {{ __('Profile & About') }}
                </button>
                <button onclick="switchMemberProfileTab('tasks')" id="mp-tab-btn-tasks" class="member-profile-tab-btn" style="padding: 14px 18px; font-size: 13px; font-weight: 700; border: none; background: transparent; cursor: pointer; color: var(--text-secondary); border-bottom: 3px solid transparent; transition: all 0.2s;">
                    📋 {{ __('Assigned Tasks') }} <span id="mp-tasks-count-pill" class="nav-badge-pill" style="font-size: 10px; margin-inline-start: 4px;">0</span>
                </button>
                <button onclick="switchMemberProfileTab('time')" id="mp-tab-btn-time" class="member-profile-tab-btn" style="padding: 14px 18px; font-size: 13px; font-weight: 700; border: none; background: transparent; cursor: pointer; color: var(--text-secondary); border-bottom: 3px solid transparent; transition: all 0.2s;">
                    ⏱️ {{ __('Work Time & Logs') }} <span id="mp-hours-count-pill" class="nav-badge-pill" style="font-size: 10px; margin-inline-start: 4px;">0h</span>
                </button>
            </div>

            <!-- Profile Content Body -->
            <div style="flex: 1; overflow-y: auto; padding: 24px; max-height: calc(90vh - 200px);">
                
                <!-- TAB 1: ABOUT & INFO -->
                <div id="mp-tab-content-about" style="display: flex; flex-direction: column; gap: 20px;">
                    
                    <!-- Contact Cards Grid -->
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 12px;">
                        <div style="background: var(--bg-surface-subtle); padding: 12px 14px; border-radius: 12px; border: 1px solid var(--border-color); box-shadow: var(--shadow-inset-3d);">
                            <div style="font-size: 10px; font-weight: 800; color: var(--text-muted); text-transform: uppercase;">✉️ {{ __('Email Address') }}</div>
                            <div id="mp-info-email" style="font-size: 12px; font-weight: 700; color: var(--text-primary); margin-top: 4px; word-break: break-all;">user@company.com</div>
                        </div>
                        <div style="background: var(--bg-surface-subtle); padding: 12px 14px; border-radius: 12px; border: 1px solid var(--border-color); box-shadow: var(--shadow-inset-3d);">
                            <div style="font-size: 10px; font-weight: 800; color: var(--text-muted); text-transform: uppercase;">📱 {{ __('Phone') }}</div>
                            <div id="mp-info-phone" style="font-size: 12px; font-weight: 700; color: var(--text-primary); margin-top: 4px;">—</div>
                        </div>
                        <div style="background: var(--bg-surface-subtle); padding: 12px 14px; border-radius: 12px; border: 1px solid var(--border-color); box-shadow: var(--shadow-inset-3d);">
                            <div style="font-size: 10px; font-weight: 800; color: var(--text-muted); text-transform: uppercase;">🎂 {{ __('Birthday') }}</div>
                            <div id="mp-info-dob" style="font-size: 12px; font-weight: 700; color: var(--text-primary); margin-top: 4px;">—</div>
                        </div>
                        <div style="background: var(--bg-surface-subtle); padding: 12px 14px; border-radius: 12px; border: 1px solid var(--border-color); box-shadow: var(--shadow-inset-3d);">
                            <div style="font-size: 10px; font-weight: 800; color: var(--text-muted); text-transform: uppercase;">📅 {{ __('Joined Workspace') }}</div>
                            <div id="mp-info-joined" style="font-size: 12px; font-weight: 700; color: var(--text-primary); margin-top: 4px;">Jan 01, 2026</div>
                        </div>
                    </div>

                    <!-- Bio Section -->
                    <div style="background: var(--bg-surface-subtle); padding: 16px; border-radius: 14px; border: 1px solid var(--border-color); box-shadow: var(--shadow-inset-3d);">
                        <div style="font-size: 11px; font-weight: 800; color: var(--text-secondary); text-transform: uppercase; margin-bottom: 6px;">📝 {{ __('About / Biography') }}</div>
                        <div id="mp-info-bio" style="font-size: 13px; line-height: 1.6; color: var(--text-primary); font-weight: 500;">No bio provided.</div>
                    </div>

                    <!-- Skills & Hobbies in 2 Columns -->
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                        <div style="background: var(--bg-surface-subtle); padding: 16px; border-radius: 14px; border: 1px solid var(--border-color); box-shadow: var(--shadow-inset-3d);">
                            <div style="font-size: 11px; font-weight: 800; color: var(--text-secondary); text-transform: uppercase; margin-bottom: 8px;">⚡ {{ __('Skills & Expertise') }}</div>
                            <div id="mp-info-skills" style="display: flex; flex-wrap: wrap; gap: 6px;">
                                <span style="font-size: 11px; color: var(--text-muted);">—</span>
                            </div>
                        </div>
                        <div style="background: var(--bg-surface-subtle); padding: 16px; border-radius: 14px; border: 1px solid var(--border-color); box-shadow: var(--shadow-inset-3d);">
                            <div style="font-size: 11px; font-weight: 800; color: var(--text-secondary); text-transform: uppercase; margin-bottom: 8px;">🎯 {{ __('Hobbies & Interests') }}</div>
                            <div id="mp-info-hobbies" style="display: flex; flex-wrap: wrap; gap: 6px;">
                                <span style="font-size: 11px; color: var(--text-muted);">—</span>
                            </div>
                        </div>
                    </div>

                    <!-- Social Media Links -->
                    <div style="background: var(--bg-surface-subtle); padding: 16px; border-radius: 14px; border: 1px solid var(--border-color); box-shadow: var(--shadow-inset-3d);">
                        <div style="font-size: 11px; font-weight: 800; color: var(--text-secondary); text-transform: uppercase; margin-bottom: 10px;">🌐 {{ __('Social Profiles & Portfolio') }}</div>
                        <div id="mp-info-socials" style="display: flex; gap: 10px; flex-wrap: wrap;">
                            <span style="font-size: 11px; color: var(--text-muted);">—</span>
                        </div>
                    </div>

                    <!-- Notes -->
                    <div id="mp-notes-container" style="background: var(--bg-surface-subtle); padding: 16px; border-radius: 14px; border: 1px solid var(--border-color); box-shadow: var(--shadow-inset-3d); display: none;">
                        <div style="font-size: 11px; font-weight: 800; color: var(--text-secondary); text-transform: uppercase; margin-bottom: 6px;">📌 {{ __('Work Preferences & Notes') }}</div>
                        <div id="mp-info-notes" style="font-size: 12px; color: var(--text-primary); line-height: 1.5;"></div>
                    </div>

                </div>

                <!-- TAB 2: ASSIGNED TASKS -->
                <div id="mp-tab-content-tasks" style="display: none; flex-direction: column; gap: 16px;">
                    
                    <!-- Tasks KPIs Summary -->
                    <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 12px;">
                        <div class="kpi-card" style="margin-bottom: 0; padding: 14px;">
                            <div class="kpi-title" style="font-size: 11px;">{{ __('Total Tasks') }}</div>
                            <div id="mp-task-stat-total" class="kpi-value" style="font-size: 20px;">0</div>
                        </div>
                        <div class="kpi-card" style="margin-bottom: 0; padding: 14px;">
                            <div class="kpi-title" style="font-size: 11px;">{{ __('In Progress') }}</div>
                            <div id="mp-task-stat-progress" class="kpi-value" style="font-size: 20px; color: var(--brand-forest);">0</div>
                        </div>
                        <div class="kpi-card" style="margin-bottom: 0; padding: 14px;">
                            <div class="kpi-title" style="font-size: 11px;">{{ __('Pending / Ready') }}</div>
                            <div id="mp-task-stat-pending" class="kpi-value" style="font-size: 20px; color: var(--status-warning);">0</div>
                        </div>
                        <div class="kpi-card" style="margin-bottom: 0; padding: 14px;">
                            <div class="kpi-title" style="font-size: 11px;">{{ __('Completed') }}</div>
                            <div id="mp-task-stat-done" class="kpi-value" style="font-size: 20px; color: #4F9B5F;">0</div>
                        </div>
                    </div>

                    <!-- Tasks Feed Container -->
                    <div id="mp-tasks-list-container" style="display: flex; flex-direction: column; gap: 8px;">
                        <div style="text-align: center; color: var(--text-muted); font-size: 12px; padding: 24px;">
                            {{ __('No tasks assigned to this member.') }}
                        </div>
                    </div>

                </div>

                <!-- TAB 3: WORK TIME & LOGS -->
                <div id="mp-tab-content-time" style="display: none; flex-direction: column; gap: 16px;">
                    
                    <!-- Time KPIs -->
                    <div style="display: grid; grid-template-columns: 1.2fr 1fr; gap: 14px;">
                        <div class="kpi-card" style="margin-bottom: 0; padding: 16px; display: flex; align-items: center; justify-content: space-between;">
                            <div>
                                <div class="kpi-title" style="font-size: 11px;">{{ __('Total Logged Effort') }}</div>
                                <div id="mp-time-total-hours" class="kpi-value" style="font-size: 24px; color: var(--brand-forest);">0.0h</div>
                                <div style="font-size: 10px; color: var(--text-muted); margin-top: 2px;">{{ __('Tracked across all initiatives') }}</div>
                            </div>
                            <div style="font-size: 32px;">⏱️</div>
                        </div>

                        <div id="mp-active-timer-box" class="kpi-card" style="margin-bottom: 0; padding: 16px; background: rgba(79, 155, 95, 0.1); border: 1px solid rgba(79, 155, 95, 0.3);">
                            <div style="display: flex; align-items: center; gap: 6px; font-size: 11px; font-weight: 800; color: #4F9B5F;">
                                <span style="animation: pulse 1.5s infinite;">🟢</span> {{ __('Live Stopwatch Status') }}
                            </div>
                            <div id="mp-active-timer-text" style="font-size: 13px; font-weight: 800; color: var(--text-primary); margin-top: 6px;">
                                {{ __('No active timer running') }}
                            </div>
                        </div>
                    </div>

                    <!-- Time Entries History Table -->
                    <div class="card" style="margin-bottom: 0; padding: 0; overflow: hidden; border-radius: var(--radius-lg);">
                        <div style="padding: 12px 16px; background: var(--bg-surface-subtle); border-bottom: 1px solid var(--border-color); font-size: 12px; font-weight: 800; color: var(--text-primary);">
                            ⏱️ {{ __('Recent Work Logs') }}
                        </div>
                        <div style="overflow-x: auto;">
                            <table class="data-table">
                                <thead>
                                    <tr>
                                        <th>{{ __('Date') }}</th>
                                        <th>{{ __('Project / Initiative') }}</th>
                                        <th>{{ __('Task') }}</th>
                                        <th>{{ __('Duration') }}</th>
                                        <th>{{ __('Description') }}</th>
                                    </tr>
                                </thead>
                                <tbody id="mp-time-entries-tbody">
                                    <tr>
                                        <td colspan="5" style="text-align: center; color: var(--text-muted); padding: 20px;">{{ __('No work logs recorded yet.') }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                </div>

            </div>

        </div>
    </div>

    <!-- Invite Modal -->
    <div id="invite-modal" class="modal">
        <div class="modal-box">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                <h3 style="font-size: 18px; font-weight: 800; color: var(--brand-navy);">📨 {{ __('Invite & Guest Access') }}</h3>
                <button onclick="closeInviteModal()" style="background: none; border: none; color: #94a3b8; font-size: 20px; cursor: pointer;">✕</button>
            </div>

            <!-- Tabs -->
            <div style="display: flex; gap: 8px; margin-bottom: 20px; background: #f1f5f9; padding: 4px; border-radius: 10px;">
                <button onclick="switchInviteTab('guest')" id="tab-guest-btn" style="flex: 1; padding: 8px; border-radius: 8px; border: none; font-size: 13px; font-weight: 700; cursor: pointer; background: var(--brand-teal); color: white;">
                    🔗 {{ __('Guest Meeting Link') }}
                </button>
                <button onclick="switchInviteTab('member')" id="tab-member-btn" style="flex: 1; padding: 8px; border-radius: 8px; border: none; font-size: 13px; font-weight: 700; cursor: pointer; background: none; color: var(--text-muted);">
                    👤 {{ __('Team Member') }}
                </button>
            </div>

            <!-- Guest Form -->
            <div id="guest-tab-content">
                <div style="display: flex; flex-direction: column; gap: 14px;">
                    <div>
                        <label style="display: block; font-size: 12px; font-weight: 700; color: var(--text-secondary); margin-bottom: 6px;">{{ __('Destination Room') }}</label>
                        @php
                            $defaultOffice = $offices->firstWhere('is_default', true) ?: $offices->first();
                            $defaultOfficeId = $defaultOffice?->id;
                            $sortedRooms = $rooms->sortBy(function($r) use ($defaultOfficeId) {
                                $rFloorId = $r->floor_id ?? $r->map?->floor_id;
                                return ($rFloorId == $defaultOfficeId) ? 0 : 1;
                            });
                        @endphp
                        <select id="invite-room-select" onchange="onInviteRoomSelected(this)" style="width: 100%; background: var(--bg-elevated); border: 1px solid var(--border-color); border-radius: 8px; padding: 10px; color: var(--text-primary); outline: none; font-size: 13px; font-weight: 600;">
                            @foreach($sortedRooms as $r)
                                @php
                                    $rFloor = $r->floor ?? $r->map?->floor;
                                    $rFloorId = $r->floor_id ?? $rFloor?->id;
                                    $isDefaultBranch = ($rFloorId == $defaultOfficeId || (!$rFloorId && $loop->first));
                                    $floorName = $rFloor?->name ?? ($isDefaultBranch ? ($defaultOffice?->name ?? __('Main Office')) : __('Branch'));
                                @endphp
                                <option value="{{ $r->id }}" data-floor-id="{{ $rFloorId }}" data-floor-name="{{ $floorName }}" data-is-default="{{ $isDefaultBranch ? '1' : '0' }}">
                                    🏢 {{ $r->name }} ({{ ucfirst($r->type) }}) — [{{ $floorName }}{{ $isDefaultBranch ? ' ⭐ ' . __('Current Branch') : '' }}]
                                </option>
                            @endforeach
                        </select>
                        <div id="invite-room-branch-warning" style="display: none; background: rgba(214, 162, 58, 0.15); border: 1px solid rgba(214, 162, 58, 0.4); border-radius: 8px; padding: 10px 12px; margin-top: 8px; font-size: 12px; color: #D6A23A; line-height: 1.4;">
                            <span>⚠️ <strong>{{ __('Notice') }}:</strong></span>
                            <span id="invite-room-warning-text">{{ __('This room belongs to a different office branch. Make sure you switch to this branch to meet your guest.') }}</span>
                        </div>
                    </div>

                    <div>
                        <label style="display: block; font-size: 12px; font-weight: 700; color: var(--text-secondary); margin-bottom: 6px;">{{ __('Guest Name / Label') }}</label>
                        <input type="text" id="invite-guest-name" value="Investor / Partner" placeholder="e.g. Sarah Miller" style="width: 100%; background: var(--bg-elevated); border: 1px solid var(--border-color); border-radius: 8px; padding: 10px; color: var(--text-primary); outline: none; font-size: 13px; font-weight: 600;">
                    </div>

                    <div>
                        <label style="display: block; font-size: 12px; font-weight: 700; color: var(--text-secondary); margin-bottom: 6px;">{{ __('Link Expiration') }}</label>
                        <select id="invite-guest-hours" style="width: 100%; background: var(--bg-elevated); border: 1px solid var(--border-color); border-radius: 8px; padding: 10px; color: var(--text-primary); outline: none; font-size: 13px; font-weight: 600;">
                            <option value="1">1 Hour</option>
                            <option value="12">12 Hours</option>
                            <option value="24" selected>24 Hours (1 Day)</option>
                            <option value="72">72 Hours (3 Days)</option>
                        </select>
                    </div>

                    <button onclick="generateGuestLink()" id="btn-generate-guest" style="margin-top: 6px; background: linear-gradient(135deg, #10b981, #059669); color: white; font-weight: 800; border: none; border-radius: 10px; padding: 12px; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 8px; font-size: 14px; box-shadow: 0 4px 14px rgba(16, 185, 129, 0.25);">
                        <span>⚡</span> {{ __('Generate Instant Guest Link') }}
                    </button>

                    <div id="guest-result-box" style="display: none; background: rgba(16, 185, 129, 0.1); border: 1px solid rgba(16, 185, 129, 0.3); border-radius: 10px; padding: 12px; margin-top: 10px;">
                        <div style="font-size: 11px; font-weight: 800; color: #34d399; text-transform: uppercase; margin-bottom: 6px;">✅ Invitation Link Ready!</div>
                        <input type="text" id="guest-link-output" readonly style="width: 100%; background: var(--bg-elevated); border: 1px solid var(--border-color); border-radius: 6px; padding: 8px; color: var(--brand-teal); font-size: 12px; font-family: monospace; margin-bottom: 8px;">
                        <div style="display: flex; gap: 8px;">
                            <button type="button" onclick="copyModalGuestLink(this)" id="btn-copy-link" style="flex: 1; background: var(--brand-primary); color: white; font-weight: 700; border: none; border-radius: 6px; padding: 8px; cursor: pointer; font-size: 12px;">
                                📋 {{ __('Copy Link') }}
                            </button>
                            <a id="guest-open-link" href="#" target="_blank" style="background: var(--bg-elevated); border: 1px solid var(--border-color); color: var(--text-primary); font-weight: 700; text-decoration: none; border-radius: 6px; padding: 8px 12px; font-size: 12px; display: flex; align-items: center;">
                                👁️ {{ __('Open') }}
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Member Form (Direct Member Add & Invite) -->
            <div id="member-tab-content" style="display: none;">
                <form method="POST" action="{{ route('organization.members.store') }}" style="display: flex; flex-direction: column; gap: 14px;">
                    @csrf
                    <div>
                        <label style="display: block; font-size: 12px; font-weight: 700; color: var(--text-secondary); margin-bottom: 6px;">{{ __('Full Name (الاسم بالكامل)') }} *</label>
                        <input type="text" name="name" required placeholder="e.g. Ahmed Ali" style="width: 100%; background: var(--bg-elevated); border: 1px solid var(--border-color); border-radius: 8px; padding: 10px; color: var(--text-primary); outline: none; font-size: 13px; font-weight: 600;">
                    </div>

                    <div>
                        <label style="display: block; font-size: 12px; font-weight: 700; color: var(--text-secondary); margin-bottom: 6px;">{{ __('Email Address (البريد الإلكتروني)') }} *</label>
                        <input type="email" name="email" required placeholder="colleague@company.com" style="width: 100%; background: var(--bg-elevated); border: 1px solid var(--border-color); border-radius: 8px; padding: 10px; color: var(--text-primary); outline: none; font-size: 13px; font-weight: 600;">
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                        <div>
                            <label style="display: block; font-size: 12px; font-weight: 700; color: var(--text-secondary); margin-bottom: 6px;">{{ __('Access Role (الدور والصلاحية)') }} *</label>
                            <select name="role_id" required style="width: 100%; background: var(--bg-elevated); border: 1px solid var(--border-color); border-radius: 8px; padding: 10px; color: var(--text-primary); outline: none; font-size: 13px; font-weight: 600;">
                                @foreach($roles as $role)
                                    <option value="{{ $role->id }}">{{ $role->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label style="display: block; font-size: 12px; font-weight: 700; color: var(--text-secondary); margin-bottom: 6px;">{{ __('Initial Password (كلمة المرور)') }}</label>
                            <input type="password" name="password" minlength="8" placeholder="Default: Password@1234" style="width: 100%; background: var(--bg-elevated); border: 1px solid var(--border-color); border-radius: 8px; padding: 10px; color: var(--text-primary); outline: none; font-size: 13px; font-weight: 600;">
                        </div>
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                        <div>
                            <label style="display: block; font-size: 12px; font-weight: 700; color: var(--text-secondary); margin-bottom: 6px;">{{ __('Department (القسم)') }}</label>
                            <select name="department_id" onchange="filterTeamsForInvite(this.value)" style="width: 100%; background: var(--bg-elevated); border: 1px solid var(--border-color); border-radius: 8px; padding: 10px; color: var(--text-primary); outline: none; font-size: 13px; font-weight: 600;">
                                <option value="">— {{ __('No Department') }} —</option>
                                @foreach($departments as $d)
                                    <option value="{{ $d->id }}">{{ $d->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label style="display: block; font-size: 12px; font-weight: 700; color: var(--text-secondary); margin-bottom: 6px;">{{ __('Sub-Team (الفريق الفرعي)') }}</label>
                            <select name="team_id" id="invite-team-select" style="width: 100%; background: var(--bg-elevated); border: 1px solid var(--border-color); border-radius: 8px; padding: 10px; color: var(--text-primary); outline: none; font-size: 13px; font-weight: 600;">
                                <option value="">— {{ __('No Team') }} —</option>
                            </select>
                        </div>
                    </div>

                    <div style="display: grid; grid-template-columns: 1.2fr 0.8fr; gap: 12px;">
                        <div>
                            <label style="display: block; font-size: 12px; font-weight: 700; color: var(--text-secondary); margin-bottom: 6px;">{{ __('Job Title (المسمى الوظيفي)') }}</label>
                            <input type="text" name="job_title" placeholder="e.g. Senior Software Architect" style="width: 100%; background: var(--bg-elevated); border: 1px solid var(--border-color); border-radius: 8px; padding: 10px; color: var(--text-primary); outline: none; font-size: 13px; font-weight: 600;">
                        </div>
                        <div>
                            <label style="display: block; font-size: 12px; font-weight: 700; color: var(--text-secondary); margin-bottom: 6px;">{{ __('Status (الحالة)') }}</label>
                            <select name="status" style="width: 100%; background: var(--bg-elevated); border: 1px solid var(--border-color); border-radius: 8px; padding: 10px; color: var(--text-primary); outline: none; font-size: 13px; font-weight: 600;">
                                <option value="active">🟢 {{ __('Active (نشط)') }}</option>
                                <option value="invited">✉️ {{ __('Invited (مدعو)') }}</option>
                            </select>
                        </div>
                    </div>

                    <button type="submit" class="header-btn btn-primary" style="margin-top: 6px; padding: 12px; font-size: 14px; justify-content: center;">
                        <span>👤</span> {{ __('Create / Add Team Member (إضافة المستخدم)') }}
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal: Department Create / Edit -->
    <div id="department-modal" class="modal-overlay">
        <div class="modal-card">
            <div class="modal-header">
                <h3 class="modal-title" id="department-modal-title">🏛️ {{ __('New Department') }}</h3>
                <button onclick="closeDepartmentModal()" class="modal-close">✕</button>
            </div>
            <form id="department-form" method="POST" action="{{ route('departments.store') }}" style="display: flex; flex-direction: column; gap: 14px;">
                @csrf
                <div id="department-method-field"></div>
                <div>
                    <label style="display: block; font-size: 12px; font-weight: 700; color: var(--text-secondary); margin-bottom: 6px;">{{ __('Department Name') }}</label>
                    <input type="text" name="name" id="department-name-input" required placeholder="e.g. Engineering & IT, Marketing, Sales" style="width: 100%; background: var(--bg-elevated); border: 1px solid var(--border-color); border-radius: 8px; padding: 10px; color: var(--text-primary); outline: none; font-size: 13px; font-weight: 600;">
                </div>
                <button type="submit" class="header-btn btn-primary" style="margin-top: 6px; padding: 12px; font-size: 14px; justify-content: center;">
                    💾 {{ __('Save Department') }}
                </button>
            </form>
        </div>
    </div>

    <!-- Modal: Team Create -->
    <div id="team-modal" class="modal-overlay">
        <div class="modal-card">
            <div class="modal-header">
                <h3 class="modal-title" id="team-modal-title">👥 {{ __('Add Sub-Team') }}</h3>
                <button onclick="closeTeamModal()" class="modal-close">✕</button>
            </div>
            <form method="POST" action="{{ route('teams.store') }}" style="display: flex; flex-direction: column; gap: 14px;">
                @csrf
                <input type="hidden" name="department_id" id="team-department-id">
                <div>
                    <label style="display: block; font-size: 12px; font-weight: 700; color: var(--text-secondary); margin-bottom: 6px;">{{ __('Team Name') }}</label>
                    <input type="text" name="name" required placeholder="e.g. Frontend Team, Enterprise Sales, UI/UX Design" style="width: 100%; background: var(--bg-elevated); border: 1px solid var(--border-color); border-radius: 8px; padding: 10px; color: var(--text-primary); outline: none; font-size: 13px; font-weight: 600;">
                </div>
                <button type="submit" class="header-btn btn-primary" style="margin-top: 6px; padding: 12px; font-size: 14px; justify-content: center;">
                    💾 {{ __('Add Team') }}
                </button>
            </form>
        </div>
    </div>

    <!-- Modal: Assign Member to Department / Team / Role -->
    <div id="assign-modal" class="modal-overlay">
        <div class="modal-card">
            <div class="modal-header">
                <h3 class="modal-title">⚙️ {{ __('Assign Department & Role') }}</h3>
                <button onclick="closeAssignModal()" class="modal-close">✕</button>
            </div>
            <form id="assign-form" method="POST" action="" style="display: flex; flex-direction: column; gap: 14px;">
                @csrf
                <div>
                    <label style="display: block; font-size: 12px; font-weight: 700; color: var(--text-secondary); margin-bottom: 4px;">{{ __('Employee / Member') }}</label>
                    <div id="assign-member-name" style="font-size: 14px; font-weight: 800; color: var(--text-primary); background: var(--bg-elevated); padding: 8px 12px; border-radius: 8px; border: 1px solid var(--border-color);">
                        Member Name
                    </div>
                </div>

                <div>
                    <label style="display: block; font-size: 12px; font-weight: 700; color: var(--text-secondary); margin-bottom: 4px;">{{ __('Department') }}</label>
                    <select name="department_id" id="assign-dept-select" onchange="filterTeamsForAssign(this.value)" style="width: 100%; background: var(--bg-elevated); border: 1px solid var(--border-color); border-radius: 8px; padding: 10px; color: var(--text-primary); outline: none; font-size: 13px; font-weight: 600;">
                        <option value="">— {{ __('No Department') }} —</option>
                        @foreach($departments as $d)
                            <option value="{{ $d->id }}">{{ $d->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label style="display: block; font-size: 12px; font-weight: 700; color: var(--text-secondary); margin-bottom: 4px;">{{ __('Sub-Team') }}</label>
                    <select name="team_id" id="assign-team-select" style="width: 100%; background: var(--bg-elevated); border: 1px solid var(--border-color); border-radius: 8px; padding: 10px; color: var(--text-primary); outline: none; font-size: 13px; font-weight: 600;">
                        <option value="">— {{ __('No Team') }} —</option>
                    </select>
                </div>

                <div>
                    <label style="display: block; font-size: 12px; font-weight: 700; color: var(--text-secondary); margin-bottom: 4px;">{{ __('Job Title') }}</label>
                    <input type="text" name="job_title" id="assign-job-title" placeholder="e.g. Lead Software Architect, Growth Specialist" style="width: 100%; background: var(--bg-elevated); border: 1px solid var(--border-color); border-radius: 8px; padding: 10px; color: var(--text-primary); outline: none; font-size: 13px; font-weight: 600;">
                </div>

                <div>
                    <label style="display: block; font-size: 12px; font-weight: 700; color: var(--text-secondary); margin-bottom: 4px;">{{ __('Access Role') }}</label>
                    <select name="role_id" id="assign-role-select" style="width: 100%; background: var(--bg-elevated); border: 1px solid var(--border-color); border-radius: 8px; padding: 10px; color: var(--text-primary); outline: none; font-size: 13px; font-weight: 600;">
                        @foreach($roles as $role)
                            <option value="{{ $role->id }}">{{ $role->name }}</option>
                        @endforeach
                    </select>
                </div>

                <button type="submit" class="header-btn btn-primary" style="margin-top: 6px; padding: 12px; font-size: 14px; justify-content: center;">
                    💾 {{ __('Save Assignment') }}
                </button>
            </form>
        </div>
    </div>

    <!-- Modal: Edit Complete Member Information -->
    <div id="edit-member-modal" class="modal-overlay">
        <div class="modal-card" style="max-width: 520px;">
            <div class="modal-header">
                <h3 class="modal-title">✏️ {{ __('Edit Team Member (تعديل بيانات المستخدم)') }}</h3>
                <button onclick="closeEditMemberModal()" class="modal-close">✕</button>
            </div>
            <form id="edit-member-form" method="POST" action="" style="display: flex; flex-direction: column; gap: 14px;">
                @csrf
                @method('PUT')
                
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                    <div>
                        <label style="display: block; font-size: 12px; font-weight: 700; color: var(--text-secondary); margin-bottom: 4px;">{{ __('Full Name (الاسم)') }} *</label>
                        <input type="text" name="name" id="edit-member-name-input" required style="width: 100%; background: var(--bg-elevated); border: 1px solid var(--border-color); border-radius: 8px; padding: 10px; color: var(--text-primary); outline: none; font-size: 13px; font-weight: 600;">
                    </div>
                    <div>
                        <label style="display: block; font-size: 12px; font-weight: 700; color: var(--text-secondary); margin-bottom: 4px;">{{ __('Email Address (البريد الإلكتروني)') }} *</label>
                        <input type="email" name="email" id="edit-member-email-input" required style="width: 100%; background: var(--bg-elevated); border: 1px solid var(--border-color); border-radius: 8px; padding: 10px; color: var(--text-primary); outline: none; font-size: 13px; font-weight: 600;">
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                    <div>
                        <label style="display: block; font-size: 12px; font-weight: 700; color: var(--text-secondary); margin-bottom: 4px;">{{ __('Department (القسم)') }}</label>
                        <select name="department_id" id="edit-member-dept-select" onchange="filterTeamsForEditMember(this.value)" style="width: 100%; background: var(--bg-elevated); border: 1px solid var(--border-color); border-radius: 8px; padding: 10px; color: var(--text-primary); outline: none; font-size: 13px; font-weight: 600;">
                            <option value="">— {{ __('No Department') }} —</option>
                            @foreach($departments as $d)
                                <option value="{{ $d->id }}">{{ $d->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label style="display: block; font-size: 12px; font-weight: 700; color: var(--text-secondary); margin-bottom: 4px;">{{ __('Sub-Team (الفريق الفرعي)') }}</label>
                        <select name="team_id" id="edit-member-team-select" style="width: 100%; background: var(--bg-elevated); border: 1px solid var(--border-color); border-radius: 8px; padding: 10px; color: var(--text-primary); outline: none; font-size: 13px; font-weight: 600;">
                            <option value="">— {{ __('No Team') }} —</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label style="display: block; font-size: 12px; font-weight: 700; color: var(--text-secondary); margin-bottom: 4px;">{{ __('Job Title (المسمى الوظيفي)') }}</label>
                    <input type="text" name="job_title" id="edit-member-job-title" placeholder="e.g. Senior Project Manager, Software Engineer" style="width: 100%; background: var(--bg-elevated); border: 1px solid var(--border-color); border-radius: 8px; padding: 10px; color: var(--text-primary); outline: none; font-size: 13px; font-weight: 600;">
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                    <div>
                        <label style="display: block; font-size: 12px; font-weight: 700; color: var(--text-secondary); margin-bottom: 4px;">{{ __('Access Role (الدور / الصلاحية)') }} *</label>
                        <select name="role_id" id="edit-member-role-select" required style="width: 100%; background: var(--bg-elevated); border: 1px solid var(--border-color); border-radius: 8px; padding: 10px; color: var(--text-primary); outline: none; font-size: 13px; font-weight: 600;">
                            @foreach($roles as $role)
                                <option value="{{ $role->id }}">{{ $role->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label style="display: block; font-size: 12px; font-weight: 700; color: var(--text-secondary); margin-bottom: 4px;">{{ __('Account Status (حالة الحساب)') }} *</label>
                        <select name="status" id="edit-member-status-select" required style="width: 100%; background: var(--bg-elevated); border: 1px solid var(--border-color); border-radius: 8px; padding: 10px; color: var(--text-primary); outline: none; font-size: 13px; font-weight: 600;">
                            <option value="active">🟢 {{ __('Active (نشط)') }}</option>
                            <option value="suspended">🔴 {{ __('Suspended (معلق)') }}</option>
                            <option value="invited">✉️ {{ __('Invited (مدعو)') }}</option>
                        </select>
                    </div>
                </div>

                <!-- Granular Office Access Permissions -->
                <div style="background: var(--bg-surface-subtle); border: 1px solid var(--border-color); border-radius: 10px; padding: 12px;">
                    <label style="display: block; font-size: 12px; font-weight: 800; color: var(--text-primary); margin-bottom: 4px;">
                        🏢 {{ __('Allowed Offices / الفروع المصرح بدخولها') }}
                    </label>
                    <div style="font-size: 11px; color: var(--text-muted); margin-bottom: 8px;">
                        {{ __('Select which branches this member can enter (Leave all unchecked for full company access).') }}
                    </div>
                    <div style="display: flex; flex-direction: column; gap: 6px; max-height: 110px; overflow-y: auto;">
                        @foreach($offices as $off)
                        <label style="display: flex; align-items: center; gap: 8px; font-size: 12px; color: var(--text-primary); cursor: pointer;">
                            <input type="checkbox" name="allowed_offices[]" value="{{ $off->id }}" class="edit-member-office-cb" id="edit-office-{{ $off->id }}">
                            <span>🏢 <strong>{{ $off->name }}</strong> ({{ $off->city_location ?: __('Primary') }})</span>
                        </label>
                        @endforeach
                    </div>
                </div>

                <!-- Granular Room Access Permissions -->
                <div style="background: var(--bg-surface-subtle); border: 1px solid var(--border-color); border-radius: 10px; padding: 12px;">
                    <label style="display: block; font-size: 12px; font-weight: 800; color: var(--text-primary); margin-bottom: 4px;">
                        🚪 {{ __('Allowed Rooms / الغرف المصرح بدخولها') }}
                    </label>
                    <div style="font-size: 11px; color: var(--text-muted); margin-bottom: 8px;">
                        {{ __('Select specific private/conference rooms this user is allowed to access.') }}
                    </div>
                    <div style="display: flex; flex-direction: column; gap: 8px; max-height: 130px; overflow-y: auto;">
                        @foreach($offices as $off)
                            @if($off->rooms->count() > 0)
                            <div style="border-bottom: 1px dashed var(--border-color); padding-bottom: 4px; margin-bottom: 4px;">
                                <div style="font-size: 11px; font-weight: 800; color: var(--brand-forest); margin-bottom: 4px;">
                                    🏢 {{ $off->name }}:
                                </div>
                                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 6px;">
                                    @foreach($off->rooms as $rm)
                                    <label style="display: flex; align-items: center; gap: 6px; font-size: 11px; color: var(--text-primary); cursor: pointer;">
                                        <input type="checkbox" name="allowed_rooms[]" value="{{ $rm->id }}" class="edit-member-room-cb" id="edit-room-{{ $rm->id }}">
                                        <span>🚪 {{ $rm->name }}</span>
                                    </label>
                                    @endforeach
                                </div>
                            </div>
                            @endif
                        @endforeach
                    </div>
                </div>

                <button type="submit" class="header-btn btn-primary" style="margin-top: 8px; padding: 12px; font-size: 14px; justify-content: center;">
                    💾 {{ __('Save Member Changes (حفظ التعديلات والصلاحيات)') }}
                </button>
            </form>
        </div>
    </div>

    <!-- Modal: New Office Branch -->
    <div id="new-office-modal" class="modal-overlay">
        <div class="modal-card" style="max-width: 480px;">
            <div class="modal-header">
                <h3 class="modal-title">🏢 {{ __('Add New Office Branch (إضافة فرع جديد)') }}</h3>
                <button onclick="closeNewOfficeModal()" class="modal-close">✕</button>
            </div>
            <form method="POST" action="{{ route('offices.store') }}" style="display: flex; flex-direction: column; gap: 14px;">
                @csrf
                <div>
                    <label style="display: block; font-size: 12px; font-weight: 700; color: var(--text-secondary); margin-bottom: 4px;">{{ __('Office Branch Name (اسم الفرع / المكتب)') }} *</label>
                    <input type="text" name="name" required placeholder="e.g. Cairo Branch, Riyadh HQ, Dubai Innovation Hub" style="width: 100%; background: var(--bg-elevated); border: 1px solid var(--border-color); border-radius: 8px; padding: 10px; color: var(--text-primary); outline: none; font-size: 13px; font-weight: 600;">
                </div>

                <div>
                    <label style="display: block; font-size: 12px; font-weight: 700; color: var(--text-secondary); margin-bottom: 4px;">{{ __('City / Location (المدينة / الدولة)') }}</label>
                    <input type="text" name="city_location" placeholder="e.g. Cairo, Egypt or Riyadh, KSA" style="width: 100%; background: var(--bg-elevated); border: 1px solid var(--border-color); border-radius: 8px; padding: 10px; color: var(--text-primary); outline: none; font-size: 13px; font-weight: 600;">
                </div>

                <div>
                    <label style="display: block; font-size: 12px; font-weight: 700; color: var(--text-secondary); margin-bottom: 4px;">{{ __('Description (الوصف)') }}</label>
                    <textarea name="description" rows="3" placeholder="Brief description of this branch and its teams..." style="width: 100%; background: var(--bg-elevated); border: 1px solid var(--border-color); border-radius: 8px; padding: 10px; color: var(--text-primary); outline: none; font-size: 13px; font-weight: 600;"></textarea>
                </div>

                <div>
                    <label style="display: flex; align-items: center; gap: 8px; font-size: 13px; font-weight: 700; color: var(--text-primary); cursor: pointer;">
                        <input type="checkbox" name="is_default" value="1">
                        <span>⭐ {{ __('Set as Primary / Default Office (تعيين كمقر رئيسي)') }}</span>
                    </label>
                </div>

                <button type="submit" class="header-btn btn-primary" style="margin-top: 6px; padding: 12px; font-size: 14px; justify-content: center;">
                    🏢 {{ __('Create Office Branch (إنشاء الفرع)') }}
                </button>
            </form>
        </div>
    </div>

    <!-- Modal: Edit Office Branch -->
    <div id="edit-office-modal" class="modal-overlay">
        <div class="modal-card" style="max-width: 480px;">
            <div class="modal-header">
                <h3 class="modal-title">✏️ {{ __('Edit Office Branch (تعديل بيانات الفرع)') }}</h3>
                <button onclick="closeEditOfficeModal()" class="modal-close">✕</button>
            </div>
            <form id="edit-office-form" method="POST" action="" style="display: flex; flex-direction: column; gap: 14px;">
                @csrf
                @method('PUT')
                <div>
                    <label style="display: block; font-size: 12px; font-weight: 700; color: var(--text-secondary); margin-bottom: 4px;">{{ __('Office Branch Name') }} *</label>
                    <input type="text" name="name" id="edit-office-name-input" required style="width: 100%; background: var(--bg-elevated); border: 1px solid var(--border-color); border-radius: 8px; padding: 10px; color: var(--text-primary); outline: none; font-size: 13px; font-weight: 600;">
                </div>

                <div>
                    <label style="display: block; font-size: 12px; font-weight: 700; color: var(--text-secondary); margin-bottom: 4px;">{{ __('City / Location') }}</label>
                    <input type="text" name="city_location" id="edit-office-city-input" style="width: 100%; background: var(--bg-elevated); border: 1px solid var(--border-color); border-radius: 8px; padding: 10px; color: var(--text-primary); outline: none; font-size: 13px; font-weight: 600;">
                </div>

                <div>
                    <label style="display: block; font-size: 12px; font-weight: 700; color: var(--text-secondary); margin-bottom: 4px;">{{ __('Description') }}</label>
                    <textarea name="description" id="edit-office-desc-input" rows="3" style="width: 100%; background: var(--bg-elevated); border: 1px solid var(--border-color); border-radius: 8px; padding: 10px; color: var(--text-primary); outline: none; font-size: 13px; font-weight: 600;"></textarea>
                </div>

                <div>
                    <label style="display: flex; align-items: center; gap: 8px; font-size: 13px; font-weight: 700; color: var(--text-primary); cursor: pointer;">
                        <input type="checkbox" name="is_default" id="edit-office-default-input" value="1">
                        <span>⭐ {{ __('Set as Primary / Default Office') }}</span>
                    </label>
                </div>

                <button type="submit" class="header-btn btn-primary" style="margin-top: 6px; padding: 12px; font-size: 14px; justify-content: center;">
                    💾 {{ __('Save Branch Details') }}
                </button>
            </form>
        </div>
    </div>

    <!-- Modal: Change Member Password -->
    <div id="change-member-password-modal" class="modal-overlay">
        <div class="modal-card" style="max-width: 440px;">
            <div class="modal-header">
                <h3 class="modal-title">🔑 {{ __('Reset Member Password (تغيير كلمة المرور)') }}</h3>
                <button onclick="closeChangeMemberPasswordModal()" class="modal-close">✕</button>
            </div>
            <form id="change-member-password-form" method="POST" action="" style="display: flex; flex-direction: column; gap: 14px;">
                @csrf
                <div>
                    <label style="display: block; font-size: 12px; font-weight: 700; color: var(--text-secondary); margin-bottom: 4px;">{{ __('User Name (المستخدم)') }}</label>
                    <div id="change-password-user-name" style="font-size: 14px; font-weight: 800; color: var(--brand-forest); background: var(--bg-elevated); padding: 8px 12px; border-radius: 8px; border: 1px solid var(--border-color);">
                        User Name
                    </div>
                </div>

                <div>
                    <label style="display: block; font-size: 12px; font-weight: 700; color: var(--text-secondary); margin-bottom: 4px;">{{ __('New Password (كلمة المرور الجديدة)') }} *</label>
                    <input type="password" name="password" required minlength="8" placeholder="••••••••" style="width: 100%; background: var(--bg-elevated); border: 1px solid var(--border-color); border-radius: 8px; padding: 10px; color: var(--text-primary); outline: none; font-size: 13px; font-weight: 600;">
                    <span style="font-size: 11px; color: var(--text-muted); margin-top: 2px; display: block;">{{ __('Minimum 8 characters') }}</span>
                </div>

                <div>
                    <label style="display: block; font-size: 12px; font-weight: 700; color: var(--text-secondary); margin-bottom: 4px;">{{ __('Confirm New Password (تأكيد كلمة المرور)') }} *</label>
                    <input type="password" name="password_confirmation" required minlength="8" placeholder="••••••••" style="width: 100%; background: var(--bg-elevated); border: 1px solid var(--border-color); border-radius: 8px; padding: 10px; color: var(--text-primary); outline: none; font-size: 13px; font-weight: 600;">
                </div>

                <button type="submit" class="header-btn btn-primary" style="margin-top: 8px; padding: 12px; font-size: 14px; justify-content: center; background: linear-gradient(135deg, #D6A23A 0%, #B88628 100%);">
                    🔑 {{ __('Update Password (تعيين كلمة المرور)') }}
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
            <span class="nav-badge-pill" style="font-size: 9px; background: rgba(79,155,95,0.15); color: #4F9B5F;">Live</span>
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
    <div id="move-task-modal" class="modal">
        <div class="modal-box" style="max-width: 420px;">
            <div class="modal-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
                <h3 style="font-size: 16px; font-weight: 900; color: var(--text-primary);">➡️ {{ __('Move Task to Project') }}</h3>
                <button type="button" onclick="closeMoveTaskModal()" style="background: none; border: none; font-size: 18px; color: var(--text-muted); cursor: pointer;">✕</button>
            </div>
            <form onsubmit="submitMoveTask(event)" style="display: flex; flex-direction: column; gap: 14px; margin-top: 14px;">
                <input type="hidden" id="move-task-id-input">
                <div>
                    <label style="display: block; font-size: 11px; font-weight: 800; color: var(--text-secondary); margin-bottom: 6px; text-transform: uppercase;">
                        📁 {{ __('Target Project') }}
                    </label>
                    <select id="move-target-project-select" required style="width: 100%; background: var(--bg-surface-subtle); border: 1px solid var(--border-color); border-radius: 10px; padding: 10px 14px; color: var(--text-primary); font-size: 13px; font-weight: 600;">
                        @foreach($projects as $p)
                            <option value="{{ $p->id }}">{{ $p->name }} ({{ $p->code }})</option>
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

    <script>
        const ORG_ID = "{{ $organization->id }}";
        const CSRF_TOKEN = "{{ csrf_token() }}";
        const ALL_TEAMS = @json($teams);

        // ── Theme Manager (Light / Dark / System) ──
        function applyTheme(theme) {
            let activeTheme = theme;
            if (theme === 'system') {
                const prefersDark = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
                activeTheme = prefersDark ? 'dark' : 'light';
            }
            
            document.documentElement.setAttribute('data-theme', activeTheme);
            if (activeTheme === 'dark') {
                document.documentElement.classList.add('dark');
                document.body.classList.add('dark-mode');
            } else {
                document.documentElement.classList.remove('dark');
                document.body.classList.remove('dark-mode');
            }
            
            const isDark = activeTheme === 'dark';
            document.querySelectorAll('.theme-toggle-icon-label').forEach(el => {
                el.textContent = isDark ? '☀️' : '🌙';
            });
            localStorage.setItem('vw_theme', theme);
        }

        function toggleThemeMode() {
            const current = document.documentElement.getAttribute('data-theme') || 'light';
            const next = current === 'dark' ? 'light' : 'dark';
            applyTheme(next);
            showToastNotification(next === 'dark' ? '🌙 <strong>{{ __('Dark Spatial Workspace') }}</strong><br>{{ __('Deep calm green mode activated.') }}' : '☀️ <strong>{{ __('Light Natural Mode') }}</strong><br>{{ __('Warm ivory workspace activated.') }}');
        }

        // Initialize saved theme on load
        (function() {
            const savedTheme = localStorage.getItem('vw_theme') || 'light';
            applyTheme(savedTheme);
        })();

        function toggleSidebarCollapse() {
            const sidebar = document.getElementById('dashboardSidebar');
            const mainContent = document.querySelector('.main-content');
            const toggleBtn = document.querySelector('.sidebar-toggle-btn');
            const isRtl = document.documentElement.dir === 'rtl' || '{{ app()->getLocale() }}' === 'ar';

            if (sidebar) sidebar.classList.toggle('sidebar-collapsed');
            if (mainContent) mainContent.classList.toggle('sidebar-collapsed');
            const isCollapsed = sidebar && sidebar.classList.contains('sidebar-collapsed');
            localStorage.setItem('vw_sidebar_collapsed', isCollapsed ? '1' : '0');

            if (toggleBtn) {
                if (isRtl) {
                    toggleBtn.textContent = isCollapsed ? '▶' : '◀';
                } else {
                    toggleBtn.textContent = isCollapsed ? '◀' : '▶';
                }
            }
        }

        // Mobile drawer toggle
        function toggleDashboardSidebar() {
            const sidebar = document.getElementById('dashboardSidebar');
            if (sidebar) {
                sidebar.classList.toggle('open');
            }
        }

        // Restore sidebar state on load
        if (localStorage.getItem('vw_sidebar_collapsed') === '1') {
            document.addEventListener('DOMContentLoaded', () => {
                const sidebar = document.getElementById('dashboardSidebar');
                const mainContent = document.querySelector('.main-content');
                const toggleBtn = document.querySelector('.sidebar-toggle-btn');
                const isRtl = document.documentElement.dir === 'rtl' || '{{ app()->getLocale() }}' === 'ar';

                if (sidebar) sidebar.classList.add('sidebar-collapsed');
                if (mainContent) mainContent.classList.add('sidebar-collapsed');
                if (toggleBtn) {
                    if (isRtl) {
                        toggleBtn.textContent = '▶';
                    } else {
                        toggleBtn.textContent = '◀';
                    }
                }
            });
        }

        function toggleSidebarSection(sectionId) {
            const sec = document.getElementById(sectionId);
            if (sec) {
                sec.classList.toggle('collapsed');
            }
        }

        function previewCompanyLogo(input) {
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const previewImg = document.getElementById('logo-preview-img');
                    const placeholder = document.getElementById('logo-preview-placeholder');
                    if (previewImg) {
                        previewImg.src = e.target.result;
                        previewImg.style.display = 'block';
                    }
                    if (placeholder) {
                        placeholder.style.display = 'none';
                    }
                };
                reader.readAsDataURL(input.files[0]);
            }
        }

        function previewUserAvatar(input) {
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const previewImg = document.getElementById('user-profile-preview-avatar');
                    const fallback = document.getElementById('user-profile-avatar-fallback');
                    const sidebarAvatar = document.getElementById('sidebar-user-avatar');
                    if (previewImg) {
                        previewImg.src = e.target.result;
                        previewImg.style.display = 'block';
                    }
                    if (fallback) {
                        fallback.style.display = 'none';
                    }
                    if (sidebarAvatar) {
                        sidebarAvatar.src = e.target.result;
                    }
                };
                reader.readAsDataURL(input.files[0]);
            }
        }

        function switchAdminTab(tabName) {
            document.querySelectorAll('.tab-view').forEach(el => el.classList.remove('active'));
            document.querySelectorAll('.nav-tab-btn').forEach(el => el.classList.remove('active'));

            const targetTab = document.getElementById(`tab-${tabName}`);
            if (targetTab) {
                targetTab.classList.add('active');
                window.scrollTo({ top: 0, behavior: 'smooth' });
                if (window.history && window.history.pushState) {
                    window.history.pushState(null, null, '#' + tabName);
                }
            }

            // Close mobile sidebar if open
            const sidebar = document.getElementById('dashboardSidebar');
            if (sidebar && window.innerWidth <= 900) {
                sidebar.classList.remove('open');
            }

            const breadcrumb = document.getElementById('current-tab-breadcrumb');
            if (breadcrumb) {
                breadcrumb.textContent = tabName.replace('-', ' ');
            }

            // Highlight corresponding sidebar button by ID or onclick match & expand parent accordion
            const directNavBtn = document.getElementById(`nav-btn-${tabName}`);
            if (directNavBtn) {
                directNavBtn.classList.add('active');
                const parentAccordion = directNavBtn.closest('.sidebar-accordion');
                if (parentAccordion && parentAccordion.classList.contains('collapsed')) {
                    parentAccordion.classList.remove('collapsed');
                }
            } else {
                document.querySelectorAll('.nav-tab-btn').forEach(btn => {
                    const onclickAttr = btn.getAttribute('onclick') || '';
                    if (onclickAttr.includes(`'${tabName}'`) || onclickAttr.includes(`"${tabName}"`)) {
                        btn.classList.add('active');
                        const parentAccordion = btn.closest('.sidebar-accordion');
                        if (parentAccordion && parentAccordion.classList.contains('collapsed')) {
                            parentAccordion.classList.remove('collapsed');
                        }
                    }
                });
            }
            const titles = {
                'overview': '{{ __('Dashboard') }}',
                'chat': '{{ __('Team Chat & Direct Messages') }}',
                'rooms': '{{ __('Rooms & Doors') }}',
                'members': '{{ __('People & Roles') }}',
                'meetings': '{{ __('Scheduled Meetings & Live Sessions') }}',
                'guests': '{{ __('Meetings & Guest Links') }}',
                'all-tasks': '{{ __('Tasks Manager') }}',
                'my-tasks': '{{ __('My Tasks') }}',
                'projects': '{{ __('Files & Projects') }}',
                'timesheets': '{{ __('Analytics & Timesheets') }}',
                'workload': '{{ __('Team Workload') }}',
                'departments': '{{ __('Departments & Teams') }}',
                'audit': '{{ __('Audit Logs') }}',
                'billing': '{{ __('Billing & Subscription') }}',
                'settings': '{{ __('Workspace Settings') }}',
                'profile': '{{ __('My User Profile') }}'
            };
            const subtitles = {
                'overview': '{{ __('Welcome to your virtual workspace') }}',
                'chat': '{{ __('Realtime company communication, direct colleague messaging, and team channels') }}',
                'rooms': '{{ __('Collaborative 2D & 3D space management') }}',
                'members': '{{ __('Team roster, departments, and permissions') }}',
                'meetings': '{{ __('Scheduled video rooms, attendee sync, and sound alerts') }}',
                'guests': '{{ __('Instant access links without authentication') }}',
                'all-tasks': '{{ __('Track sprints, milestones, and deliverables') }}',
                'my-tasks': '{{ __('Personal checklist and scheduled duties') }}',
                'projects': '{{ __('Shared assets and file repositories') }}',
                'timesheets': '{{ __('Presence trends and productivity tracking') }}',
                'workload': '{{ __('Capacity planning and resource distribution') }}',
                'departments': '{{ __('Organizational structure and hierarchy') }}',
                'audit': '{{ __('Realtime activity logs and security history') }}',
                'billing': '{{ __('Manage subscription tier and payment plans') }}',
                'settings': '{{ __('Workspace configuration and branding') }}',
                'profile': '{{ __('Personal details, hobbies, and security') }}'
            };

            const headerTitle = document.getElementById('page-primary-title');
            const headerSub = document.getElementById('page-primary-subtitle');
            if (headerTitle && titles[tabName]) headerTitle.textContent = titles[tabName];
            if (headerSub && subtitles[tabName]) headerSub.textContent = subtitles[tabName];

            if (tabName === 'chat') {
                loadChatConversations();
            }
        }

        // Global Live Search Filter
        function handleGlobalSearch(query) {
            const q = query.toLowerCase().trim();
            if (!q) {
                document.querySelectorAll('.data-table tbody tr, .card, .kpi-card').forEach(el => el.style.display = '');
                return;
            }
            document.querySelectorAll('.data-table tbody tr').forEach(row => {
                const text = row.innerText.toLowerCase();
                row.style.display = text.includes(q) ? '' : 'none';
            });
        }

        // Focus Mode Interactive Toggle
        let isFocusModeActive = false;
        function toggleFocusMode() {
            isFocusModeActive = !isFocusModeActive;
            const bannerBtn = document.querySelector('.focus-mode-banner button');
            const quickBtn = document.getElementById('quick-action-focus');
            
            if (isFocusModeActive) {
                showToastNotification('🌿 <strong>{{ __('Focus Mode Activated') }}</strong><br>{{ __('Notifications muted. Ambient productivity session in progress.') }}');
                if (bannerBtn) bannerBtn.textContent = '{{ __('Disable Focus Mode ✕') }}';
                if (quickBtn) quickBtn.style.background = 'linear-gradient(180deg, #1E4E31 0%, #163823 100%)';
            } else {
                showToastNotification('🌿 {{ __('Focus Mode Disabled. Welcome back!') }}');
                if (bannerBtn) bannerBtn.textContent = '{{ __('Enable Focus Mode →') }}';
                if (quickBtn) quickBtn.style.background = 'var(--accent-gradient)';
            }
        }

        // Fast Task Toggle from Overview
        async function toggleTaskDone(taskId, isDone) {
            try {
                const res = await fetch(`/api/v1/organizations/${ORG_ID}/tasks/${taskId}/status`, {
                    method: 'PATCH',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': CSRF_TOKEN },
                    credentials: 'same-origin',
                    body: JSON.stringify({ status: isDone ? 'done' : 'in_progress' })
                });
                if (res.ok) {
                    showToastNotification(isDone ? '✅ {{ __('Task completed!') }}' : '🔄 {{ __('Task reopened.') }}');
                }
            } catch(e) {
                console.error(e);
            }
        }

        // Auto-open tab from URL hash on load (e.g. /dashboard#projects or #all-tasks or #kanban)
        window.addEventListener('DOMContentLoaded', () => {
            let hash = window.location.hash.replace('#', '');
            if (hash === 'kanban') {
                hash = 'all-tasks';
                setTimeout(() => switchAllTasksView('kanban'), 120);
            }
            if (hash && document.getElementById(`tab-${hash}`)) {
                switchAdminTab(hash);
            }
        });

        // ── Department Modals ──
        function openDepartmentModal() {
            document.getElementById('department-modal-title').textContent = '🏛️ {{ __('New Department') }}';
            document.getElementById('department-form').action = "{{ route('departments.store') }}";
            document.getElementById('department-method-field').innerHTML = '';
            document.getElementById('department-name-input').value = '';
            document.getElementById('department-modal').style.display = 'flex';
        }

        function editDepartment(id, name) {
            document.getElementById('department-modal-title').textContent = '✏️ {{ __('Edit Department') }}';
            document.getElementById('department-form').action = `/departments/${id}`;
            document.getElementById('department-method-field').innerHTML = '@method("PUT")';
            document.getElementById('department-name-input').value = name;
            document.getElementById('department-modal').style.display = 'flex';
        }

        function closeDepartmentModal() {
            document.getElementById('department-modal').style.display = 'none';
        }

        // ── Team Modals ──
        function openTeamModal(deptId, deptName) {
            document.getElementById('team-modal-title').textContent = `👥 {{ __('Add Sub-Team to') }} ${deptName}`;
            document.getElementById('team-department-id').value = deptId;
            document.getElementById('team-modal').style.display = 'flex';
        }

        function closeTeamModal() {
            document.getElementById('team-modal').style.display = 'none';
        }

        // ── Assign Member Modal ──
        function openAssignModal(memberId, memberName, deptId, teamId, roleId, jobTitle) {
            document.getElementById('assign-form').action = `/members/${memberId}/assign`;
            document.getElementById('assign-member-name').textContent = memberName;
            document.getElementById('assign-dept-select').value = deptId || '';
            filterTeamsForAssign(deptId, teamId);
            document.getElementById('assign-job-title').value = jobTitle || '';
            if (roleId) {
                document.getElementById('assign-role-select').value = roleId;
            }
            document.getElementById('assign-modal').style.display = 'flex';
        }

        function filterTeamsForAssign(deptId, selectedTeamId = '') {
            const teamSelect = document.getElementById('assign-team-select');
            teamSelect.innerHTML = '<option value="">— {{ __('No Team') }} —</option>';
            if (!deptId) return;

            const filtered = ALL_TEAMS.filter(t => t.department_id == deptId);
            filtered.forEach(t => {
                const opt = document.createElement('option');
                opt.value = t.id;
                opt.textContent = t.name;
                if (selectedTeamId && t.id == selectedTeamId) {
                    opt.selected = true;
                }
                teamSelect.appendChild(opt);
            });
        }

        function closeAssignModal() {
            document.getElementById('assign-modal').style.display = 'none';
        }

        // ── Edit Member Modal ──
        function openEditMemberModal(memberId, name, email, deptId, teamId, roleId, jobTitle, status) {
            document.getElementById('edit-member-form').action = `/organization/members/${memberId}`;
            document.getElementById('edit-member-name-input').value = name || '';
            document.getElementById('edit-member-email-input').value = email || '';
            document.getElementById('edit-member-dept-select').value = deptId || '';
            filterTeamsForEditMember(deptId, teamId);
            document.getElementById('edit-member-job-title').value = jobTitle || '';
            if (roleId) {
                document.getElementById('edit-member-role-select').value = roleId;
            }
            if (status) {
                document.getElementById('edit-member-status-select').value = status;
            }

            // Reset checkboxes
            document.querySelectorAll('.edit-member-office-cb').forEach(cb => cb.checked = false);
            document.querySelectorAll('.edit-member-room-cb').forEach(cb => cb.checked = false);

            // Fetch dynamic member profile & allowed offices/rooms
            fetch(`/organization/members/${memberId}/details`)
                .then(r => r.json())
                .then(data => {
                    if (data.member) {
                        if (data.member.allowed_office_ids && Array.isArray(data.member.allowed_office_ids)) {
                            data.member.allowed_office_ids.forEach(id => {
                                const el = document.getElementById(`edit-office-${id}`);
                                if (el) el.checked = true;
                            });
                        }
                        if (data.member.allowed_room_ids && Array.isArray(data.member.allowed_room_ids)) {
                            data.member.allowed_room_ids.forEach(id => {
                                const el = document.getElementById(`edit-room-${id}`);
                                if (el) el.checked = true;
                            });
                        }
                    }
                })
                .catch(err => console.error('Error fetching member details:', err));

            document.getElementById('edit-member-modal').style.display = 'flex';
        }

        // ── Offices Modals ──
        function openNewOfficeModal() {
            document.getElementById('new-office-modal').style.display = 'flex';
        }
        function closeNewOfficeModal() {
            document.getElementById('new-office-modal').style.display = 'none';
        }
        function openEditOfficeModal(officeId, name, city, desc, isDefault) {
            document.getElementById('edit-office-form').action = `/offices/${officeId}`;
            document.getElementById('edit-office-name-input').value = name || '';
            document.getElementById('edit-office-city-input').value = city || '';
            document.getElementById('edit-office-desc-input').value = desc || '';
            document.getElementById('edit-office-default-input').checked = !!isDefault;
            document.getElementById('edit-office-modal').style.display = 'flex';
        }
        function closeEditOfficeModal() {
            document.getElementById('edit-office-modal').style.display = 'none';
        }

        function filterTeamsForEditMember(deptId, selectedTeamId = '') {
            const teamSelect = document.getElementById('edit-member-team-select');
            teamSelect.innerHTML = '<option value="">— {{ __('No Team') }} —</option>';
            if (!deptId) return;

            const filtered = ALL_TEAMS.filter(t => t.department_id == deptId);
            filtered.forEach(t => {
                const opt = document.createElement('option');
                opt.value = t.id;
                opt.textContent = t.name;
                if (selectedTeamId && t.id == selectedTeamId) {
                    opt.selected = true;
                }
                teamSelect.appendChild(opt);
            });
        }

        function filterTeamsForInvite(deptId) {
            const teamSelect = document.getElementById('invite-team-select');
            if (!teamSelect) return;
            teamSelect.innerHTML = '<option value="">— {{ __('No Team') }} —</option>';
            if (!deptId) return;

            const filtered = ALL_TEAMS.filter(t => t.department_id == deptId);
            filtered.forEach(t => {
                const opt = document.createElement('option');
                opt.value = t.id;
                opt.textContent = t.name;
                teamSelect.appendChild(opt);
            });
        }

        function closeEditMemberModal() {
            document.getElementById('edit-member-modal').style.display = 'none';
        }

        // ── Change Password Modal ──
        function openChangeMemberPasswordModal(memberId, userName) {
            document.getElementById('change-member-password-form').action = `/organization/members/${memberId}/password`;
            document.getElementById('change-password-user-name').textContent = userName || 'User';
            document.getElementById('change-member-password-modal').style.display = 'flex';
        }

        function closeChangeMemberPasswordModal() {
            document.getElementById('change-member-password-modal').style.display = 'none';
        }

        function openInviteModal() {
            document.getElementById('invite-modal').style.display = 'flex';
        }

        function closeInviteModal() {
            document.getElementById('invite-modal').style.display = 'none';
        }

        function switchInviteTab(tab) {
            const guestTab = document.getElementById('guest-tab-content');
            const memberTab = document.getElementById('member-tab-content');
            const guestBtn = document.getElementById('tab-guest-btn');
            const memberBtn = document.getElementById('tab-member-btn');

            if (tab === 'guest') {
                guestTab.style.display = 'block';
                memberTab.style.display = 'none';
                guestBtn.style.background = 'var(--accent-primary)';
                guestBtn.style.color = 'white';
                memberBtn.style.background = 'none';
                memberBtn.style.color = '#94a3b8';
            } else {
                guestTab.style.display = 'none';
                memberTab.style.display = 'block';
                memberBtn.style.background = 'var(--accent-primary)';
                memberBtn.style.color = 'white';
                guestBtn.style.background = 'none';
                guestBtn.style.color = '#94a3b8';
            }
        }

        function showToastNotification(message, type = 'success') {
            let container = document.getElementById('toast-container');
            if (!container) {
                container = document.createElement('div');
                container.id = 'toast-container';
                document.body.appendChild(container);
            }

            const toast = document.createElement('div');
            toast.className = 'toast-popup';
            toast.innerHTML = message;
            container.appendChild(toast);

            setTimeout(() => {
                toast.classList.add('toast-fadeout');
                setTimeout(() => {
                    if (toast.parentNode) toast.parentNode.removeChild(toast);
                }, 300);
            }, 3500);
        }

        /* ── Live Workplace Notification Center Client ── */
        let currentNotifications = [];
        let activeNotifFilter = 'all';
        let previousUnreadCount = 0;
        let isInitialNotifLoad = true;

        function playNotificationChime() {
            try {
                const audioCtx = new (window.AudioContext || window.webkitAudioContext)();
                const now = audioCtx.currentTime;
                
                const osc1 = audioCtx.createOscillator();
                const gain1 = audioCtx.createGain();
                osc1.type = 'sine';
                osc1.frequency.setValueAtTime(587.33, now); // D5
                osc1.frequency.exponentialRampToValueAtTime(880, now + 0.12); // A5
                gain1.gain.setValueAtTime(0.08, now);
                gain1.gain.exponentialRampToValueAtTime(0.001, now + 0.35);
                osc1.connect(gain1);
                gain1.connect(audioCtx.destination);
                osc1.start(now);
                osc1.stop(now + 0.35);
            } catch (e) {
                // AudioContext not allowed before user gesture
            }
        }

        function triggerDesktopNotification(notif) {
            if ('Notification' in window && Notification.permission === 'granted') {
                try {
                    const n = new Notification(notif.title || 'Workplace Notification', {
                        body: notif.body || '',
                        icon: '/favicon.ico',
                        badge: '/favicon.ico'
                    });
                    n.onclick = () => {
                        window.focus();
                        if (notif.action_url) window.location.href = notif.action_url;
                    };
                } catch (e) {
                    console.log('Desktop notification error:', e);
                }
            }
        }

        async function fetchUserNotifications() {
            try {
                const res = await fetch('/api/notifications', {
                    headers: { 'Accept': 'application/json' }
                });
                if (!res.ok) return;
                const data = await res.json();
                const unreadCount = data.unread_count || 0;
                currentNotifications = data.notifications || [];

                // Update UI badge
                const badge = document.getElementById('notifBadge');
                const headerCount = document.getElementById('notifHeaderCount');

                if (badge) {
                    if (unreadCount > 0) {
                        badge.textContent = unreadCount > 99 ? '99+' : unreadCount;
                        badge.style.display = 'flex';
                    } else {
                        badge.style.display = 'none';
                    }
                }

                if (headerCount) {
                    if (unreadCount > 0) {
                        headerCount.textContent = `${unreadCount} {{ __('new') }}`;
                        headerCount.style.display = 'inline-flex';
                    } else {
                        headerCount.style.display = 'none';
                    }
                }

                // If new notifications arrived after initial load
                if (!isInitialNotifLoad && unreadCount > previousUnreadCount) {
                    playNotificationChime();
                    const newest = currentNotifications.find(n => !n.is_read) || currentNotifications[0];
                    if (newest) {
                        showToastNotification(`${newest.icon || '🔔'} <strong>${newest.title}</strong><br><small style="color: var(--text-muted);">${newest.body || ''}</small>`);
                        triggerDesktopNotification(newest);
                    }
                }

                previousUnreadCount = unreadCount;
                isInitialNotifLoad = false;
                renderNotificationsList();
            } catch (err) {
                // Silently handle polling errors
            }
        }

        function toggleNotificationDropdown() {
            const dropdown = document.getElementById('notifDropdown');
            if (!dropdown) return;
            const isOpen = dropdown.style.display === 'flex';
            dropdown.style.display = isOpen ? 'none' : 'flex';

            if (!isOpen) {
                // Request desktop notification permission on user interaction
                if ('Notification' in window && Notification.permission === 'default') {
                    Notification.requestPermission();
                }
                renderNotificationsList();
            }
        }

        function filterNotifTab(tab, btn) {
            activeNotifFilter = tab;
            document.querySelectorAll('.notif-tab-btn').forEach(b => b.classList.remove('active'));
            if (btn) btn.classList.add('active');
            renderNotificationsList();
        }

        function renderNotificationsList() {
            const container = document.getElementById('notifListContainer');
            if (!container) return;

            let filtered = currentNotifications;
            if (activeNotifFilter === 'task') {
                filtered = currentNotifications.filter(n => n.type.startsWith('task'));
            } else if (activeNotifFilter === 'meeting') {
                filtered = currentNotifications.filter(n => n.type.startsWith('meeting'));
            } else if (activeNotifFilter === 'spatial') {
                filtered = currentNotifications.filter(n => n.type === 'door_knock' || n.type === 'wave' || n.type.startsWith('room'));
            }

            if (filtered.length === 0) {
                container.innerHTML = `
                    <div style="padding: 36px 18px; text-align: center; color: var(--text-muted);">
                        <div style="font-size: 32px; margin-bottom: 8px;">🎉</div>
                        <strong style="display: block; font-size: 13px; color: var(--text-primary); margin-bottom: 4px;">{{ __('All caught up!') }}</strong>
                        <span style="font-size: 12px;">{{ __('No notifications in this category.') }}</span>
                    </div>
                `;
                return;
            }

            container.innerHTML = '';
            filtered.forEach(n => {
                const item = document.createElement('div');
                item.className = `notif-item ${n.is_read ? '' : 'unread'}`;
                item.onclick = () => handleNotificationClick(n);

                item.innerHTML = `
                    <div class="notif-icon-box">${n.icon || '🔔'}</div>
                    <div style="flex: 1; min-width: 0;">
                        <div style="display: flex; justify-content: space-between; align-items: baseline; gap: 8px; margin-bottom: 2px;">
                            <strong style="font-size: 12px; color: var(--text-primary); white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">${n.title}</strong>
                            <span style="font-size: 10px; color: var(--text-muted); flex-shrink: 0;">${n.created_at_human || ''}</span>
                        </div>
                        <p style="font-size: 11px; color: var(--text-secondary); margin: 0; line-height: 1.4; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;">${n.body || ''}</p>
                    </div>
                    ${!n.is_read ? '<div class="notif-unread-dot"></div>' : ''}
                `;
                container.appendChild(item);
            });
        }

        async function handleNotificationClick(notif) {
            if (!notif.is_read) {
                try {
                    await fetch(`/api/notifications/${notif.id}/read`, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json'
                        }
                    });
                    notif.is_read = true;
                    fetchUserNotifications();
                } catch (e) {}
            }

            if (notif.action_url) {
                window.location.href = notif.action_url;
            }
        }

        async function markAllNotificationsAsRead() {
            try {
                await fetch('/api/notifications/read-all', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    }
                });
                currentNotifications.forEach(n => n.is_read = true);
                fetchUserNotifications();
            } catch (e) {}
        }

        async function clearAllNotificationsFromServer() {
            if (!confirm('{{ __("Clear all notifications?") }}')) return;
            try {
                await fetch('/api/notifications/clear', {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    }
                });
                currentNotifications = [];
                fetchUserNotifications();
            } catch (e) {}
        }

        // Close dropdown when clicking outside
        document.addEventListener('click', (e) => {
            const wrapper = document.getElementById('notifWrapper');
            const dropdown = document.getElementById('notifDropdown');
            if (wrapper && dropdown && !wrapper.contains(e.target)) {
                dropdown.style.display = 'none';
            }
        });

        // Initialize notification polling
        document.addEventListener('DOMContentLoaded', () => {
            fetchUserNotifications();
            setInterval(fetchUserNotifications, 15000);
        });

        function triggerCopySuccess(btn) {
            if (btn) {
                const originalText = btn.innerHTML;
                btn.innerHTML = '✅ {{ __('Copied!') }}';
                btn.style.background = '#10b981';
                btn.style.borderColor = '#10b981';
                btn.style.color = '#ffffff';
                btn.classList.remove('btn-copied-pulse');
                void btn.offsetWidth; // Force CSS reflow to re-trigger pulse animation
                btn.classList.add('btn-copied-pulse');

                setTimeout(() => {
                    btn.innerHTML = originalText;
                    btn.style.background = '';
                    btn.style.borderColor = '';
                    btn.style.color = '';
                    btn.classList.remove('btn-copied-pulse');
                }, 2200);
            }
            showToastNotification('📋 <strong>' + "{{ __('Link Copied!') }}" + '</strong> — ' + "{{ __('Guest meeting link copied to clipboard.') }}", 'success');
        }

        function executeClipboardCopy(text) {
            if (!text) return false;
            let copied = false;

            // Strategy 1: Firefox Native Copy Event Interceptor
            try {
                const onCopy = function(e) {
                    if (e.clipboardData) {
                        e.clipboardData.setData('text/plain', text);
                        e.preventDefault();
                        copied = true;
                    }
                };
                document.addEventListener('copy', onCopy, { once: true });
                document.execCommand('copy');
                document.removeEventListener('copy', onCopy);
            } catch (err) {}

            // Strategy 2: DOM Textarea selection fallback
            if (!copied) {
                try {
                    const temp = document.createElement('textarea');
                    temp.value = text;
                    temp.style.position = 'fixed';
                    temp.style.top = '10px';
                    temp.style.left = '10px';
                    temp.style.width = '100px';
                    temp.style.height = '40px';
                    temp.style.padding = '0';
                    temp.style.border = 'none';
                    temp.style.outline = 'none';
                    temp.style.boxShadow = 'none';
                    temp.style.background = 'transparent';
                    temp.style.opacity = '0.01';
                    temp.style.zIndex = '-9999';
                    document.body.appendChild(temp);
                    temp.focus();
                    temp.select();
                    temp.setSelectionRange(0, text.length);
                    copied = document.execCommand('copy');
                    document.body.removeChild(temp);
                } catch (e) {}
            }

            // Strategy 3: Async Clipboard API
            if (!copied && navigator.clipboard && navigator.clipboard.writeText) {
                navigator.clipboard.writeText(text).catch(() => {});
                copied = true;
            }

            return copied;
        }

        function copyTableGuestLink(url, btn) {
            if (!url) return;
            // Ensure origin is current
            if (url.startsWith('http://') || url.startsWith('https://')) {
                const path = url.replace(/^https?:\/\/[^\/]+/, '');
                url = window.location.origin + path;
            }
            executeClipboardCopy(url);
            triggerCopySuccess(btn);
        }

        function copyModalGuestLink(btn) {
            const input = document.getElementById('guest-link-output');
            const text = input ? input.value : '';
            if (!text) return;
            executeClipboardCopy(text);
            triggerCopySuccess(btn);
        }

        function onInviteRoomSelected(sel) {
            if (!sel) return;
            const opt = sel.options[sel.selectedIndex];
            if (!opt) return;
            const isDefault = opt.getAttribute('data-is-default') === '1';
            const floorName = opt.getAttribute('data-floor-name') || '';
            const warningBox = document.getElementById('invite-room-branch-warning');
            const warningText = document.getElementById('invite-room-warning-text');
            if (warningBox && warningText) {
                if (!isDefault) {
                    warningBox.style.display = 'block';
                    warningText.textContent = `{{ __('This room belongs to branch') }} "${floorName}" {{ __('which is different from your current default team branch. To meet your guest, please switch to this branch.') }}`;
                } else {
                    warningBox.style.display = 'none';
                }
            }
        }

        async function generateGuestLink() {
            const roomId = document.getElementById('invite-room-select').value;
            const guestName = document.getElementById('invite-guest-name').value.trim() || 'Guest';
            const hours = parseInt(document.getElementById('invite-guest-hours').value) || 24;

            if (!roomId) {
                alert('Please select or create a destination room first.');
                return;
            }

            const btn = document.getElementById('btn-generate-guest');
            btn.innerHTML = '<span>⏳</span> Generating...';

            try {
                const res = await fetch(`/api/v1/organizations/${ORG_ID}/rooms/${roomId}/guest-invitations`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': CSRF_TOKEN
                    },
                    credentials: 'same-origin',
                    body: JSON.stringify({
                        guest_name: guestName,
                        expires_in_hours: hours
                    })
                });

                if (!res.ok) {
                    const errData = await res.json();
                    alert(errData.message || 'Failed to generate guest link.');
                    return;
                }

                const data = await res.json();
                if (data.join_url) {
                    document.getElementById('guest-link-output').value = data.join_url;
                    document.getElementById('guest-open-link').href = data.join_url;
                    document.getElementById('guest-result-box').style.display = 'block';
                }
            } catch (e) {
                console.error(e);
                alert('Error generating guest link: ' + (e.message || 'Network error'));
            } finally {
                btn.innerHTML = '<span>⚡</span> Generate Instant Guest Link';
            }
        }

        async function sendMemberInvite() {
            const email = document.getElementById('invite-member-email').value.trim();
            const roleId = document.getElementById('invite-member-role').value;
            const statusBox = document.getElementById('member-invite-status');

            if (!email) {
                alert('Please enter an email address.');
                return;
            }

            try {
                const res = await fetch(`/api/v1/organizations/${ORG_ID}/members/invite`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': CSRF_TOKEN
                    },
                    credentials: 'same-origin',
                    body: JSON.stringify({ email, role_id: roleId })
                });

                const data = await res.json();
                statusBox.style.display = 'block';
                statusBox.style.color = '#10b981';
                statusBox.textContent = `✅ ${data.message || 'Invitation sent successfully!'}`;
            } catch (e) {
                statusBox.style.display = 'block';
                statusBox.style.color = '#ef4444';
                statusBox.textContent = '❌ Failed to send invitation.';
            }
        }

        function toggleGlobalTheme() {
            const current = document.documentElement.getAttribute('data-theme') || 'dark';
            const next = current === 'dark' ? 'light' : 'dark';
            document.documentElement.setAttribute('data-theme', next);
            localStorage.setItem('vw_theme', next);
            document.getElementById('theme-icon').textContent = next === 'dark' ? '🌙' : '☀️';
        }

        // ── PROJECT MANAGEMENT CLIENT CONTROLLERS ──
        let activeTimerSeconds = {{ $activeTimer ? $activeTimer->elapsedSeconds() : 0 }};
        let activeTimerInterval = null;

        function formatTimerClock(totalSeconds) {
            const h = String(Math.floor(totalSeconds / 3600)).padStart(2, '0');
            const m = String(Math.floor((totalSeconds % 3600) / 60)).padStart(2, '0');
            const s = String(totalSeconds % 60).padStart(2, '0');
            return `${h}:${m}:${s}`;
        }

        function initLiveTimerTicker() {
            if (activeTimerInterval) clearInterval(activeTimerInterval);
            const clockEl = document.getElementById('live-timer-clock');
            if (clockEl) clockEl.textContent = formatTimerClock(activeTimerSeconds);

            @if($activeTimer)
                activeTimerInterval = setInterval(() => {
                    activeTimerSeconds++;
                    if (clockEl) clockEl.textContent = formatTimerClock(activeTimerSeconds);
                }, 1000);
            @endif
        }
        initLiveTimerTicker();

        async function startTaskTimer(projectId, taskId, taskTitle, projectName) {
            try {
                const res = await fetch(`/api/v1/organizations/${ORG_ID}/time/timer/start`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': CSRF_TOKEN },
                    credentials: 'same-origin',
                    body: JSON.stringify({ project_id: projectId, task_id: taskId })
                });
                const data = await res.json();
                if (!res.ok) {
                    alert(data.message || 'Failed to start timer.');
                    return;
                }

                // Update UI timer strip
                document.getElementById('universal-timer-strip').style.display = 'flex';
                document.getElementById('timer-project-tag').textContent = projectName;
                document.getElementById('timer-task-title').textContent = taskTitle;
                activeTimerSeconds = 0;
                if (activeTimerInterval) clearInterval(activeTimerInterval);
                activeTimerInterval = setInterval(() => {
                    activeTimerSeconds++;
                    const clock = document.getElementById('live-timer-clock');
                    if (clock) clock.textContent = formatTimerClock(activeTimerSeconds);
                }, 1000);
            } catch (e) {
                console.error(e);
                alert('Network error starting timer.');
            }
        }

        async function stopGlobalTimer() {
            try {
                const res = await fetch(`/api/v1/organizations/${ORG_ID}/time/timer/stop`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': CSRF_TOKEN },
                    credentials: 'same-origin',
                    body: JSON.stringify({})
                });
                const data = await res.json();
                if (!res.ok) {
                    alert(data.message || 'Failed to stop timer.');
                    return;
                }

                if (activeTimerInterval) clearInterval(activeTimerInterval);
                document.getElementById('universal-timer-strip').style.display = 'none';
                alert('✅ Timer stopped and work session logged successfully!');
                window.location.reload();
            } catch (e) {
                console.error(e);
                alert('Network error stopping timer.');
            }
        }

        // New Project Modal
        function openNewProjectModal() { document.getElementById('new-project-modal').style.display = 'flex'; }
        function closeNewProjectModal() { document.getElementById('new-project-modal').style.display = 'none'; }

        async function createProjectSubmit(e) {
            e.preventDefault();
            const form = document.getElementById('new-project-form');
            const formData = new FormData(form);
            const payload = Object.fromEntries(formData.entries());

            try {
                const res = await fetch(`/api/v1/organizations/${ORG_ID}/projects`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': CSRF_TOKEN },
                    credentials: 'same-origin',
                    body: JSON.stringify(payload)
                });
                const data = await res.json();
                if (!res.ok) {
                    alert(data.message || 'Error creating project.');
                    return;
                }
                closeNewProjectModal();
                alert('✅ Project created successfully!');
                window.location.reload();
            } catch (err) {
                alert('Network error creating project.');
            }
        }

        // New Task Modal
        function openNewTaskModal() { document.getElementById('new-task-modal').style.display = 'flex'; }
        function closeNewTaskModal() { document.getElementById('new-task-modal').style.display = 'none'; }

        async function createTaskSubmit(e) {
            e.preventDefault();
            const form = document.getElementById('new-task-form');
            const formData = new FormData(form);
            const payload = Object.fromEntries(formData.entries());

            try {
                const res = await fetch(`/api/v1/organizations/${ORG_ID}/tasks`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': CSRF_TOKEN },
                    credentials: 'same-origin',
                    body: JSON.stringify(payload)
                });
                const data = await res.json();
                if (!res.ok) {
                    alert(data.message || 'Error creating task.');
                    return;
                }
                closeNewTaskModal();
                alert('✅ Task created successfully!');
                window.location.reload();
            } catch (err) {
                alert('Network error creating task.');
            }
        }

        // Manual Time Entry
        function openManualTimeModal() { document.getElementById('manual-time-modal').style.display = 'flex'; }
        function closeManualTimeModal() { document.getElementById('manual-time-modal').style.display = 'none'; }

        async function logManualTimeSubmit(e) {
            e.preventDefault();
            const form = document.getElementById('manual-time-form');
            const formData = new FormData(form);
            const payload = Object.fromEntries(formData.entries());

            try {
                const res = await fetch(`/api/v1/organizations/${ORG_ID}/time/entries/manual`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': CSRF_TOKEN },
                    credentials: 'same-origin',
                    body: JSON.stringify(payload)
                });
                const data = await res.json();
                if (!res.ok) {
                    alert(data.message || 'Error logging time.');
                    return;
                }
                closeManualTimeModal();
                alert('✅ Time entry logged successfully!');
                window.location.reload();
            } catch (err) {
                alert('Network error logging time.');
            }
        }

        // Timesheets Actions
        async function submitMyCurrentTimesheet() {
            if (!confirm('Submit your weekly timesheet for manager review? Logged entries will be locked.')) return;
            const now = new Date();
            const first = now.getDate() - now.getDay() + 1;
            const monday = new Date(now.setDate(first)).toISOString().split('T')[0];
            const sunday = new Date(now.setDate(first + 6)).toISOString().split('T')[0];

            try {
                const res = await fetch(`/api/v1/organizations/${ORG_ID}/timesheets/submit`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': CSRF_TOKEN },
                    credentials: 'same-origin',
                    body: JSON.stringify({ period_start: monday, period_end: sunday })
                });
                const data = await res.json();
                if (!res.ok) {
                    alert(data.message || 'Error submitting timesheet.');
                    return;
                }
                alert('✅ Timesheet submitted successfully!');
                window.location.reload();
            } catch (e) {
                alert('Network error submitting timesheet.');
            }
        }

        async function approveTimesheet(timesheetId) {
            if (!confirm('Approve and permanently lock this timesheet?')) return;
            try {
                const res = await fetch(`/api/v1/organizations/${ORG_ID}/timesheets/${timesheetId}/approve`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': CSRF_TOKEN },
                    credentials: 'same-origin'
                });
                const data = await res.json();
                if (!res.ok) {
                    alert(data.message || 'Error approving timesheet.');
                    return;
                }
                alert('✅ Timesheet approved!');
                window.location.reload();
            } catch (e) {
                alert('Network error approving timesheet.');
            }
        }

        let currentRejectTimesheetId = null;
        function openRejectModal(id) {
            currentRejectTimesheetId = id;
            document.getElementById('reject-timesheet-modal').style.display = 'flex';
        }
        function closeRejectModal() { document.getElementById('reject-timesheet-modal').style.display = 'none'; }

        async function rejectTimesheetSubmit(e) {
            e.preventDefault();
            const reason = document.getElementById('reject-reason-input').value;
            if (!reason) return alert('Please enter a feedback reason.');

            try {
                const res = await fetch(`/api/v1/organizations/${ORG_ID}/timesheets/${currentRejectTimesheetId}/reject`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': CSRF_TOKEN },
                    credentials: 'same-origin',
                    body: JSON.stringify({ rejection_reason: reason })
                });
                const data = await res.json();
                if (!res.ok) {
                    alert(data.message || 'Error rejecting timesheet.');
                    return;
                }
                closeRejectModal();
                alert('✅ Timesheet rejected and feedback returned to employee.');
                window.location.reload();
            } catch (e) {
                alert('Network error rejecting timesheet.');
            }
        }

        // ── PROJECT HUB & KPI DASHBOARD CONTROLLER ──
        let activeHubProjectId = null;

        async function openProjectHub(projectId) {
            activeHubProjectId = projectId;
            const modal = document.getElementById('project-hub-modal');
            modal.style.display = 'flex';

            try {
                const res = await fetch(`/api/v1/organizations/${ORG_ID}/projects/${projectId}`, {
                    headers: { 'Accept': 'application/json' },
                    credentials: 'same-origin'
                });
                if (!res.ok) {
                    alert('Error loading project details.');
                    return;
                }
                const data = await res.json();
                renderProjectHub(data);
            } catch (e) {
                console.error(e);
                alert('Network error loading project hub.');
            }
        }

        function closeProjectHub() {
            document.getElementById('project-hub-modal').style.display = 'none';
            activeHubProjectId = null;
        }

        function switchHubTab(tab) {
            ['kanban', 'tasks', 'timelog'].forEach(t => {
                const view = document.getElementById(`hub-view-${t}`);
                const btn = document.getElementById(`hub-tab-btn-${t}`);
                if (view) view.style.display = (t === tab) ? 'block' : 'none';
                if (btn) {
                    if (t === tab) {
                        btn.className = 'header-btn btn-primary';
                    } else {
                        btn.className = 'header-btn btn-outline';
                    }
                }
            });
        }

        function openNewTaskForCurrentProject() {
            if (!activeHubProjectId) return;
            const select = document.querySelector('#new-task-form select[name="project_id"]');
            if (select) select.value = activeHubProjectId;
            openNewTaskModal();
        }

        async function updateHubTaskStatus(taskId, newStatus) {
            try {
                const res = await fetch(`/api/v1/organizations/${ORG_ID}/tasks/${taskId}/status`, {
                    method: 'PATCH',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': CSRF_TOKEN },
                    credentials: 'same-origin',
                    body: JSON.stringify({ status: newStatus })
                });
                if (!res.ok) {
                    alert('Error updating task status.');
                    return;
                }
                // Refresh project hub
                if (activeHubProjectId) openProjectHub(activeHubProjectId);
            } catch (e) {
                alert('Network error updating task.');
            }
        }

        function renderProjectHub(data) {
            const p = data.project;
            const k = data.kpis || {};

            // Header info
            document.getElementById('hub-proj-code').textContent = p.code || 'PRJ';
            document.getElementById('hub-proj-name').textContent = p.name;
            document.getElementById('hub-proj-status').textContent = (p.status || 'active').toUpperCase();
            document.getElementById('hub-proj-priority').textContent = (p.priority || 'medium').toUpperCase();
            document.getElementById('hub-proj-manager').textContent = p.manager ? p.manager.name : 'Unassigned';
            document.getElementById('hub-proj-dept').textContent = p.department ? p.department.name : 'General';
            document.getElementById('hub-proj-due').textContent = p.due_date ? new Date(p.due_date).toLocaleDateString() : '—';

            // KPI Cards
            document.getElementById('hub-kpi-progress-pct').textContent = `${k.progress_pct || 0}%`;
            document.getElementById('hub-kpi-tasks-ratio').textContent = `${k.completed_tasks || 0} / ${k.total_tasks || 0} tasks done`;
            
            document.getElementById('hub-kpi-hours').textContent = `${k.actual_hours || 0} / ${k.planned_hours || 0} h`;
            document.getElementById('hub-kpi-hours-var').textContent = `Variance: ${k.hours_variance || 0}h`;

            document.getElementById('hub-kpi-budget').textContent = `$${Number(k.budget_amount || 0).toLocaleString()} / $${Number(k.labor_cost || 0).toLocaleString()}`;
            document.getElementById('hub-kpi-margin').textContent = `Margin: $${Number(k.gross_margin || 0).toLocaleString()} (${k.gross_margin_pct || 0}%)`;

            document.getElementById('hub-kpi-active-tasks').textContent = `${k.in_progress_tasks || 0} Active`;
            document.getElementById('hub-kpi-overdue-tasks').textContent = `${k.overdue_tasks || 0} Overdue`;

            // Clear Kanban columns
            const cols = ['backlog', 'ready', 'in_progress', 'review', 'done'];
            cols.forEach(c => {
                const el = document.getElementById(`kanban-col-${c}`);
                if (el) el.innerHTML = '';
                const cnt = document.getElementById(`col-count-${c}`);
                if (cnt) cnt.textContent = '0';
            });

            // Populate Kanban Cards & Task Table
            const tasks = p.tasks || [];
            const taskCounts = { backlog: 0, ready: 0, in_progress: 0, review: 0, done: 0 };
            const taskTableBody = document.getElementById('hub-task-table-body');
            if (taskTableBody) taskTableBody.innerHTML = '';

            tasks.forEach(t => {
                const status = (t.status === 'qa') ? 'review' : t.status;
                if (taskCounts[status] !== undefined) taskCounts[status]++;

                // Kanban Card HTML
                const colEl = document.getElementById(`kanban-col-${status}`);
                if (colEl) {
                    const card = document.createElement('div');
                    card.className = 'kanban-card';
                    card.innerHTML = `
                        <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 6px;">
                            <span class="badge badge-blue" style="font-size: 10px;">#${t.task_number || 1}</span>
                            <select onchange="updateHubTaskStatus('${t.id}', this.value)" style="background: var(--bg-card); border: 1px solid var(--border-color); color: var(--text-primary); font-size: 10px; font-weight: 700; border-radius: 4px; padding: 2px;">
                                <option value="backlog" ${t.status === 'backlog' ? 'selected' : ''}>Backlog</option>
                                <option value="ready" ${t.status === 'ready' ? 'selected' : ''}>Ready</option>
                                <option value="in_progress" ${t.status === 'in_progress' ? 'selected' : ''}>In Progress</option>
                                <option value="review" ${t.status === 'review' || t.status === 'qa' ? 'selected' : ''}>Review</option>
                                <option value="done" ${t.status === 'done' ? 'selected' : ''}>Done</option>
                            </select>
                        </div>
                        <div style="font-weight: 800; font-size: 13px; margin-bottom: 4px; color: var(--text-primary);">${t.title}</div>
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 8px; border-top: 1px solid var(--border-color); padding-top: 6px; font-size: 11px;">
                            <span style="color: var(--text-muted);">👤 ${t.assignee ? t.assignee.name.split(' ')[0] : 'Unassigned'}</span>
                            <button onclick="startTaskTimer('${p.id}', '${t.id}', '${t.title.replace(/'/g, "\\'")}', '${p.name.replace(/'/g, "\\'")}')" class="header-btn" style="background: rgba(16, 185, 129, 0.2); color: #34d399; padding: 2px 6px; font-size: 10px;">
                                ▶ Timer
                            </button>
                        </div>
                    `;
                    colEl.appendChild(card);
                }

                // Task Table Row
                if (taskTableBody) {
                    const row = document.createElement('tr');
                    row.innerHTML = `
                        <td style="font-family: monospace; font-weight: 700;">#${t.task_number || 1}</td>
                        <td style="font-weight: 800; color: var(--text-primary);">${t.title}</td>
                        <td>${t.assignee ? t.assignee.name : '<span style="color: var(--text-muted);">Unassigned</span>'}</td>
                        <td><span class="badge ${t.status === 'done' ? 'badge-green' : (t.status === 'in_progress' ? 'badge-teal' : 'badge-gray')}">${t.status}</span></td>
                        <td><span class="badge ${t.priority === 'urgent' ? 'badge-crimson' : (t.priority === 'high' ? 'badge-amber' : 'badge-gray')}">${t.priority}</span></td>
                        <td style="font-family: monospace;">${t.estimated_hours || 0}h / ${t.actual_hours || 0}h</td>
                        <td>${t.due_date ? new Date(t.due_date).toLocaleDateString() : '—'}</td>
                        <td>
                            <button onclick="startTaskTimer('${p.id}', '${t.id}', '${t.title.replace(/'/g, "\\'")}', '${p.name.replace(/'/g, "\\'")}')" class="header-btn btn-outline" style="padding: 3px 8px; font-size: 10px;">
                                ▶ Timer
                            </button>
                        </td>
                    `;
                    taskTableBody.appendChild(row);
                }
            });

            // Update column count badges
            cols.forEach(c => {
                const cnt = document.getElementById(`col-count-${c}`);
                if (cnt) cnt.textContent = taskCounts[c] || 0;
            });

            // Populate Time Log Table
            const timelogBody = document.getElementById('hub-timelog-table-body');
            if (timelogBody) {
                timelogBody.innerHTML = '';
                const entries = p.time_entries || [];
                if (entries.length === 0) {
                    timelogBody.innerHTML = '<tr><td colspan="7" style="text-align: center; padding: 20px; color: var(--text-muted);">No time tracked on this project yet.</td></tr>';
                } else {
                    entries.forEach(e => {
                        const tr = document.createElement('tr');
                        const hrs = (e.duration_seconds / 3600).toFixed(2);
                        tr.innerHTML = `
                            <td>${new Date(e.started_at).toLocaleDateString()}</td>
                            <td style="font-weight: 700;">${e.user ? e.user.name : 'Member'}</td>
                            <td>${e.task ? e.task.title : '—'}</td>
                            <td style="font-weight: 800; color: #34d399; font-family: monospace;">${hrs}h</td>
                            <td style="font-size: 11px; color: var(--text-secondary);">${e.description || 'Work session'}</td>
                            <td><span class="badge badge-gray">${e.entry_type}</span></td>
                            <td><span class="badge ${e.status === 'approved' ? 'badge-green' : (e.status === 'submitted' ? 'badge-amber' : 'badge-gray')}">${e.status}</span></td>
                        `;
                        timelogBody.appendChild(tr);
                    });
                }
            }
        }

        // ── ALL TASKS & KANBAN BOARD CONTROLLER ──
        function switchAllTasksView(view) {
            const tblView = document.getElementById('alltasks-view-table');
            const knbView = document.getElementById('alltasks-view-kanban');
            const tblBtn = document.getElementById('alltasks-btn-table');
            const knbBtn = document.getElementById('alltasks-btn-kanban');

            if (view === 'table') {
                if (tblView) tblView.style.display = 'block';
                if (knbView) knbView.style.display = 'none';
                if (tblBtn) {
                    tblBtn.className = 'tactile-btn btn-primary';
                    tblBtn.style = 'padding: 7px 14px; font-size: 12px;';
                }
                if (knbBtn) {
                    knbBtn.className = 'tactile-btn btn-secondary';
                    knbBtn.style = 'padding: 7px 14px; font-size: 12px; background: transparent; border: none; box-shadow: none; color: var(--text-secondary);';
                }
                localStorage.setItem('alltasks_view', 'table');
            } else {
                if (tblView) tblView.style.display = 'none';
                if (knbView) knbView.style.display = 'block';
                if (knbBtn) {
                    knbBtn.className = 'tactile-btn btn-primary';
                    knbBtn.style = 'padding: 7px 14px; font-size: 12px;';
                }
                if (tblBtn) {
                    tblBtn.className = 'tactile-btn btn-secondary';
                    tblBtn.style = 'padding: 7px 14px; font-size: 12px; background: transparent; border: none; box-shadow: none; color: var(--text-secondary);';
                }
                localStorage.setItem('alltasks_view', 'kanban');
            }
            filterAllTasksTable();
        }

        // Restore view preference from localStorage
        (function() {
            const savedView = localStorage.getItem('alltasks_view');
            if (savedView === 'kanban') {
                setTimeout(() => switchAllTasksView('kanban'), 100);
            }
        })();

        function filterAllTasksTable() {
            const query = (document.getElementById('alltasks-filter-search')?.value || '').toLowerCase().trim();
            const proj = document.getElementById('alltasks-filter-project')?.value || '';
            const status = document.getElementById('alltasks-filter-status')?.value || '';
            const priority = document.getElementById('alltasks-filter-priority')?.value || '';
            const assignee = document.getElementById('alltasks-filter-assignee')?.value || '';

            // 1. Filter Table Rows
            const rows = document.querySelectorAll('.alltask-row');
            let visibleCount = 0;

            rows.forEach(r => {
                const title = (r.dataset.title || '').toLowerCase();
                const rProj = r.dataset.projectId || '';
                const rStatus = r.dataset.status || '';
                const rPriority = r.dataset.priority || '';
                const rAssignee = r.dataset.assigneeId || '';

                const matchesQuery = !query || title.includes(query);
                const matchesProj = !proj || rProj === proj;
                const matchesStatus = !status || rStatus === status;
                const matchesPriority = !priority || rPriority === priority;
                const matchesAssignee = !assignee || rAssignee === assignee;

                if (matchesQuery && matchesProj && matchesStatus && matchesPriority && matchesAssignee) {
                    r.style.display = '';
                    visibleCount++;
                } else {
                    r.style.display = 'none';
                }
            });

            const cntEl = document.getElementById('alltasks-filtered-count');
            if (cntEl) cntEl.textContent = visibleCount;

            // 2. Filter Global Kanban Cards & Update Column Counters
            const colCounts = { backlog: 0, ready: 0, in_progress: 0, review: 0, done: 0 };
            const cards = document.querySelectorAll('.global-kanban-card');

            cards.forEach(card => {
                const title = (card.dataset.title || '').toLowerCase();
                const cProj = card.dataset.projectId || '';
                let cStatus = card.dataset.status || '';
                if (cStatus === 'qa') cStatus = 'review';
                const cPriority = card.dataset.priority || '';
                const cAssignee = card.dataset.assigneeId || '';

                const matchesQuery = !query || title.includes(query);
                const matchesProj = !proj || cProj === proj;
                const matchesStatus = !status || cStatus === status;
                const matchesPriority = !priority || cPriority === priority;
                const matchesAssignee = !assignee || cAssignee === assignee;

                if (matchesQuery && matchesProj && matchesStatus && matchesPriority && matchesAssignee) {
                    card.style.display = 'block';
                    if (colCounts[cStatus] !== undefined) {
                        colCounts[cStatus]++;
                    }
                } else {
                    card.style.display = 'none';
                }
            });

            // Update column count badges
            Object.keys(colCounts).forEach(st => {
                const badge = document.getElementById(`global-kanban-cnt-${st}`);
                if (badge) badge.textContent = colCounts[st];
            });
        }

        // Global Drag & Drop Engine
        let globalDraggedTaskId = null;

        function handleGlobalDragStart(e, taskId) {
            globalDraggedTaskId = taskId;
            e.dataTransfer.setData('text/plain', taskId);
            e.dataTransfer.effectAllowed = 'move';
            const card = document.getElementById(`global-kanban-card-${taskId}`);
            if (card) card.classList.add('is-dragging');
        }

        function handleGlobalDragEnd(e) {
            document.querySelectorAll('.global-kanban-card').forEach(c => c.classList.remove('is-dragging'));
            document.querySelectorAll('.kanban-column').forEach(col => col.classList.remove('drag-over'));
        }

        function handleGlobalDragOver(e) {
            e.preventDefault();
            e.dataTransfer.dropEffect = 'move';
            const col = e.currentTarget;
            if (col && !col.classList.contains('drag-over')) {
                col.classList.add('drag-over');
            }
        }

        function handleGlobalDragLeave(e) {
            const col = e.currentTarget;
            if (col) col.classList.remove('drag-over');
        }

        async function handleGlobalDrop(e, targetStatus) {
            e.preventDefault();
            const col = e.currentTarget;
            if (col) col.classList.remove('drag-over');

            const taskId = e.dataTransfer.getData('text/plain') || globalDraggedTaskId;
            if (!taskId) return;

            const card = document.getElementById(`global-kanban-card-${taskId}`);
            if (!card) return;

            const oldStatus = card.dataset.status;
            if (oldStatus === targetStatus) return;

            // Optimistic DOM relocation
            const targetContainer = document.getElementById(`global-kanban-col-${targetStatus}`);
            if (targetContainer) {
                const emptyHint = targetContainer.querySelector('.kanban-empty-hint');
                if (emptyHint) emptyHint.remove();
                targetContainer.appendChild(card);
            }

            card.dataset.status = targetStatus;
            const cardSelect = card.querySelector('select');
            if (cardSelect) cardSelect.value = targetStatus;

            // Update matching row in Table view
            const matchingRow = document.querySelector(`.alltask-row[data-id="${taskId}"]`);
            if (matchingRow) {
                matchingRow.dataset.status = targetStatus;
                const rowSelect = matchingRow.querySelector('select');
                if (rowSelect) rowSelect.value = targetStatus;
            }

            filterAllTasksTable();

            try {
                const res = await fetch(`/api/v1/organizations/${ORG_ID}/tasks/${taskId}/status`, {
                    method: 'PATCH',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': CSRF_TOKEN },
                    credentials: 'same-origin',
                    body: JSON.stringify({ status: targetStatus })
                });
                if (!res.ok) {
                    throw new Error('Failed to update task');
                }
                showToastNotification('✅ ' + "{{ __('Task status updated successfully!') }}");
            } catch (err) {
                console.error(err);
                alert('Failed to save task status on server.');
                window.location.reload();
            }
        }

        async function updateTaskStatusDirect(taskId, newStatus) {
            // Optimistically update Kanban card
            const card = document.getElementById(`global-kanban-card-${taskId}`);
            if (card) {
                const targetContainer = document.getElementById(`global-kanban-col-${newStatus}`);
                if (targetContainer) {
                    const emptyHint = targetContainer.querySelector('.kanban-empty-hint');
                    if (emptyHint) emptyHint.remove();
                    targetContainer.appendChild(card);
                }
                card.dataset.status = newStatus;
                const cardSelect = card.querySelector('select');
                if (cardSelect) cardSelect.value = newStatus;
            }

            const matchingRow = document.querySelector(`.alltask-row[data-id="${taskId}"]`);
            if (matchingRow) {
                matchingRow.dataset.status = newStatus;
                const rowSelect = matchingRow.querySelector('select');
                if (rowSelect) rowSelect.value = newStatus;
            }

            filterAllTasksTable();

            try {
                const res = await fetch(`/api/v1/organizations/${ORG_ID}/tasks/${taskId}/status`, {
                    method: 'PATCH',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': CSRF_TOKEN },
                    credentials: 'same-origin',
                    body: JSON.stringify({ status: newStatus })
                });
                if (!res.ok) {
                    alert('Error updating task status.');
                    return;
                }
                showToastNotification('✅ ' + "{{ __('Task status updated successfully!') }}");
            } catch (e) {
                alert('Network error updating task.');
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

            // Calculate coordinate positioning
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

        function ctxActionCopyLink() {
            closeTaskContextMenu();
            const link = `${window.location.origin}/projects/hub/${activeCtxProjectId}?task=${activeCtxTaskId}`;
            executeClipboardCopy(link);
            showToastNotification('📋 ' + "{{ __('Task link copied to clipboard!') }}");
        }

        function ctxActionCopyId() {
            closeTaskContextMenu();
            executeClipboardCopy('#' + activeCtxTaskId);
            showToastNotification('📋 ' + "{{ __('Task ID copied to clipboard!') }}");
        }

        function ctxActionOpenNewTab() {
            closeTaskContextMenu();
            window.open(`/projects/hub/${activeCtxProjectId}?task=${activeCtxTaskId}`, '_blank');
        }

        function ctxActionInspect() {
            closeTaskContextMenu();
            openTaskDetails(activeCtxTaskId);
        }

        function ctxActionStartTimer() {
            closeTaskContextMenu();
            startTaskTimer(activeCtxProjectId, activeCtxTaskId, activeCtxTaskTitle, 'Project');
        }

        async function ctxActionDuplicate() {
            closeTaskContextMenu();
            try {
                const res = await fetch(`/api/v1/organizations/${ORG_ID}/tasks/${activeCtxTaskId}/duplicate`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': CSRF_TOKEN },
                    credentials: 'same-origin'
                });
                if (!res.ok) {
                    alert('Error duplicating task.');
                    return;
                }
                showToastNotification('📋 ' + "{{ __('Task duplicated successfully!') }}");
                setTimeout(() => window.location.reload(), 600);
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
                    credentials: 'same-origin',
                    body: JSON.stringify({ project_id: targetProjId })
                });
                if (!res.ok) {
                    alert('Error moving task.');
                    return;
                }
                closeMoveTaskModal();
                showToastNotification('➡️ ' + "{{ __('Task moved successfully!') }}");
                setTimeout(() => window.location.reload(), 600);
            } catch (err) {
                alert('Network error moving task.');
            }
        }

        function ctxActionInspectCustomFields() {
            closeTaskContextMenu();
            openTaskDetails(activeCtxTaskId);
        }

        function ctxActionInspectDependencies() {
            closeTaskContextMenu();
            openTaskDetails(activeCtxTaskId);
        }

        async function ctxActionDelete() {
            closeTaskContextMenu();
            if (!confirm('{{ __('Are you sure you want to delete this task?') }}')) return;

            try {
                const res = await fetch(`/api/v1/organizations/${ORG_ID}/tasks/${activeCtxTaskId}`, {
                    method: 'DELETE',
                    headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': CSRF_TOKEN },
                    credentials: 'same-origin'
                });
                if (!res.ok) {
                    alert('Error deleting task.');
                    return;
                }
                const card = document.getElementById(`global-kanban-card-${activeCtxTaskId}`);
                if (card) card.remove();
                const row = document.querySelector(`.alltask-row[data-id="${activeCtxTaskId}"]`);
                if (row) row.remove();
                filterAllTasksTable();
                showToastNotification('🗑️ ' + "{{ __('Task deleted.') }}");
            } catch (err) {
                alert('Network error deleting task.');
            }
        }

        function ctxActionPermissions() {
            closeTaskContextMenu();
            showToastNotification('🔒 <strong>' + "{{ __('Sharing & Permissions') }}" + '</strong>: ' + "{{ __('Inherited from Project Role Settings') }}");
        }

        // ── TASK INSPECTOR / DETAILS DRAWER ──
        let activeInspectorTaskId = null;
        let currentInspectorTask = null;

        async function openTaskDetails(taskId) {
            activeInspectorTaskId = taskId;
            const modal = document.getElementById('task-details-modal');
            modal.style.display = 'flex';

            try {
                const res = await fetch(`/api/v1/organizations/${ORG_ID}/tasks/${taskId}`, {
                    headers: { 'Accept': 'application/json' },
                    credentials: 'same-origin'
                });
                if (!res.ok) {
                    alert('Error loading task details.');
                    return;
                }
                const data = await res.json();
                currentInspectorTask = data.task || data;
                renderTaskDetails(currentInspectorTask);
            } catch (e) {
                console.error(e);
                alert('Network error loading task details.');
            }
        }

        function closeTaskDetailsModal() {
            document.getElementById('task-details-modal').style.display = 'none';
            activeInspectorTaskId = null;
            currentInspectorTask = null;
        }

        function switchTaskInspectorTab(tab) {
            ['details', 'checklist', 'attachments', 'comments', 'dependencies', 'timelog'].forEach(t => {
                const view = document.getElementById(`task-inspector-${t}`);
                const btn = document.getElementById(`task-tab-btn-${t}`);
                if (view) view.style.display = (t === tab) ? 'block' : 'none';
                if (btn) {
                    btn.className = (t === tab) ? 'tactile-btn btn-primary' : 'tactile-btn btn-secondary';
                    btn.style.background = (t === tab) ? '' : 'transparent';
                    btn.style.border = (t === tab) ? '' : 'none';
                    btn.style.boxShadow = (t === tab) ? '' : 'none';
                    btn.style.color = (t === tab) ? '' : 'var(--text-secondary)';
                }
            });
        }

        async function updateCurrentTaskStatus(newStatus) {
            if (!activeInspectorTaskId) return;
            try {
                const res = await fetch(`/api/v1/organizations/${ORG_ID}/tasks/${activeInspectorTaskId}/status`, {
                    method: 'PATCH',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': CSRF_TOKEN },
                    credentials: 'same-origin',
                    body: JSON.stringify({ status: newStatus })
                });
                if (!res.ok) {
                    alert('Error updating status.');
                    return;
                }
                openTaskDetails(activeInspectorTaskId);
            } catch (e) {
                alert('Network error updating status.');
            }
        }

        async function addTaskChecklistItem(e) {
            e.preventDefault();
            const input = document.getElementById('new-checklist-title-input');
            const title = input.value.trim();
            if (!title || !activeInspectorTaskId) return;

            try {
                const res = await fetch(`/api/v1/organizations/${ORG_ID}/tasks/${activeInspectorTaskId}/checklist`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': CSRF_TOKEN },
                    credentials: 'same-origin',
                    body: JSON.stringify({ title: title })
                });
                if (!res.ok) {
                    alert('Error adding checklist item.');
                    return;
                }
                input.value = '';
                openTaskDetails(activeInspectorTaskId);
            } catch (err) {
                alert('Network error adding checklist item.');
            }
        }

        async function toggleTaskChecklistItem(itemId) {
            if (!activeInspectorTaskId) return;
            try {
                const res = await fetch(`/api/v1/organizations/${ORG_ID}/tasks/${activeInspectorTaskId}/checklist/${itemId}/toggle`, {
                    method: 'PATCH',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': CSRF_TOKEN },
                    credentials: 'same-origin'
                });
                if (!res.ok) {
                    alert('Error toggling checklist item.');
                    return;
                }
                openTaskDetails(activeInspectorTaskId);
            } catch (err) {
                alert('Network error toggling checklist item.');
            }
        }

        async function addTaskCommentSubmit(e) {
            e.preventDefault();
            const input = document.getElementById('new-comment-body-input');
            const body = input.value.trim();
            if (!body || !activeInspectorTaskId) return;

            try {
                const res = await fetch(`/tasks/${activeInspectorTaskId}/comments`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': CSRF_TOKEN },
                    credentials: 'same-origin',
                    body: JSON.stringify({ body: body })
                });
                if (!res.ok) {
                    alert('Error posting comment.');
                    return;
                }
                input.value = '';
                openTaskDetails(activeInspectorTaskId);
            } catch (err) {
                alert('Network error posting comment.');
            }
        }

        function insertMentionHandle(name) {
            const input = document.getElementById('new-comment-body-input');
            if (!input) return;
            input.value += (input.value ? ' ' : '') + '@' + name + ' ';
            input.focus();
        }

        async function uploadTaskAttachmentSubmit(e) {
            e.preventDefault();
            const fileInput = document.getElementById('task-file-input');
            if (!fileInput || !fileInput.files.length || !activeInspectorTaskId) return;

            const formData = new FormData();
            formData.append('file', fileInput.files[0]);

            try {
                const res = await fetch(`/tasks/${activeInspectorTaskId}/attachments`, {
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
                showToastNotification('📎 ' + "{{ __('Attachment uploaded successfully!') }}");
                openTaskDetails(activeInspectorTaskId);
            } catch (err) {
                alert('Network error uploading attachment.');
            }
        }

        async function deleteTaskAttachmentAction(attachmentId) {
            if (!confirm('{{ __("Are you sure you want to delete this attachment?") }}')) return;
            try {
                const res = await fetch(`/tasks/${activeInspectorTaskId}/attachments/${attachmentId}`, {
                    method: 'DELETE',
                    headers: { 'X-CSRF-TOKEN': CSRF_TOKEN, 'Accept': 'application/json' },
                    credentials: 'same-origin'
                });
                if (!res.ok) {
                    alert('Error deleting attachment.');
                    return;
                }
                showToastNotification('🗑️ ' + "{{ __('Attachment removed.') }}");
                openTaskDetails(activeInspectorTaskId);
            } catch (err) {
                alert('Network error deleting attachment.');
            }
        }

        async function quickApproveTask(taskId) {
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
                showToastNotification('🎉 ' + "{{ __('Task approved and marked as Completed!') }}");
                if (activeInspectorTaskId === taskId) {
                    openTaskDetails(taskId);
                } else {
                    window.location.reload();
                }
            } catch (err) {
                alert('Network error approving task.');
            }
        }

        async function quickRejectTask(taskId) {
            const reason = prompt('{{ __("Please enter a note / feedback on required changes:") }}');
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
                    alert(data.message || 'Error rejecting task.');
                    return;
                }
                showToastNotification('⚠️ ' + "{{ __('Task returned to in-progress with feedback.') }}");
                if (activeInspectorTaskId === taskId) {
                    openTaskDetails(taskId);
                } else {
                    window.location.reload();
                }
            } catch (err) {
                alert('Network error requesting changes.');
            }
        }

        async function addTaskDependencySubmit(e) {
            e.preventDefault();
            const select = document.getElementById('dependency-blocker-select');
            const blockerId = select.value;
            if (!blockerId || !activeInspectorTaskId) return;

            try {
                const res = await fetch(`/api/v1/organizations/${ORG_ID}/tasks/${activeInspectorTaskId}/dependencies`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': CSRF_TOKEN },
                    credentials: 'same-origin',
                    body: JSON.stringify({ depends_on_task_id: blockerId })
                });
                const data = await res.json();
                if (!res.ok) {
                    alert(data.message || 'Error adding dependency.');
                    return;
                }
                select.value = '';
                alert('✅ Dependency linked successfully!');
                openTaskDetails(activeInspectorTaskId);
            } catch (err) {
                alert('Network error linking dependency.');
            }
        }

        function renderTaskDetails(t) {
            const statusLabels = {
                'backlog': '{{ __("Backlog") }}',
                'ready': '{{ __("Ready") }}',
                'in_progress': '{{ __("In Progress") }}',
                'review': '{{ __("In Review / QA") }}',
                'done': '{{ __("Done") }}'
            };
            const priorityLabels = {
                'low': '{{ __("Low") }}',
                'medium': '{{ __("Medium") }}',
                'high': '{{ __("High") }}',
                'urgent': '{{ __("Urgent") }}'
            };

            // Header
            document.getElementById('task-modal-code').textContent = `#${t.task_number || 1}`;
            document.getElementById('task-modal-title').textContent = t.title;
            document.getElementById('task-modal-status-badge').textContent = statusLabels[t.status] || (t.status || 'backlog');
            document.getElementById('task-modal-priority-badge').textContent = priorityLabels[t.priority] || (t.priority || 'medium');
            document.getElementById('task-modal-project').textContent = t.project ? t.project.name : '{{ __("General") }}';
            document.getElementById('task-modal-assignee').textContent = t.assignee ? t.assignee.name : '{{ __("Unassigned") }}';
            
            window.currentModalTaskAssigneeMemberId = null;
            if (t.assignee) {
                if (t.assignee.member_id) {
                    window.currentModalTaskAssigneeMemberId = t.assignee.member_id;
                } else if (typeof cachedChatMembers !== 'undefined' && cachedChatMembers.length) {
                    const matched = cachedChatMembers.find(m => m.user_id == t.assignee.id);
                    if (matched) window.currentModalTaskAssigneeMemberId = matched.id;
                }
            }

            document.getElementById('task-modal-due').textContent = t.due_date ? new Date(t.due_date).toLocaleDateString() : '—';
            document.getElementById('task-modal-status-select').value = t.status || 'backlog';
            document.getElementById('task-modal-description').textContent = t.description || '{{ __("No description provided.") }}';
            document.getElementById('task-modal-hours').textContent = `${t.estimated_hours || 0} {{ __("Estimated Hours") }} / ${t.actual_hours || 0} {{ __("Logged Hours") }}`;

            // Approval Banner logic
            const appBanner = document.getElementById('task-modal-approval-banner');
            const appText = document.getElementById('task-modal-approval-text');
            const appActions = document.getElementById('task-modal-approval-actions');
            if (appBanner && appText && appActions) {
                if (t.approval_status === 'pending_approval') {
                    appBanner.style.display = 'flex';
                    appBanner.style.background = 'rgba(214, 162, 58, 0.15)';
                    appBanner.style.border = '1px solid rgba(214, 162, 58, 0.35)';
                    appBanner.style.color = '#D6A23A';
                    appText.innerHTML = '<span>⏳</span> <span>{{ __("This task is submitted for completion and awaiting PM approval.") }}</span>';
                    appActions.innerHTML = `
                        <button type="button" onclick="quickApproveTask('${t.id}')" class="tactile-btn btn-primary" style="padding: 6px 14px; font-size: 11px;">✓ {{ __("Approve") }}</button>
                        <button type="button" onclick="quickRejectTask('${t.id}')" class="tactile-btn" style="background: rgba(217, 107, 95, 0.2); color: #D96B5F; border: 1px solid rgba(217, 107, 95, 0.3); padding: 6px 12px; font-size: 11px;">✕ {{ __("Request Changes") }}</button>
                    `;
                } else if (t.approval_status === 'rejected') {
                    appBanner.style.display = 'flex';
                    appBanner.style.background = 'rgba(217, 107, 95, 0.15)';
                    appBanner.style.border = '1px solid rgba(217, 107, 95, 0.35)';
                    appBanner.style.color = '#D96B5F';
                    appText.innerHTML = `<span>⚠️</span> <span><strong>{{ __("Changes Requested:") }}</strong> ${t.rejection_reason || '{{ __("Please review feedback.") }}'}</span>`;
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

            // Timer Button
            const timerBtn = document.getElementById('task-modal-timer-btn');
            if (timerBtn) {
                const pId = t.project_id || '';
                const pName = t.project ? t.project.name : '{{ __("Project") }}';
                timerBtn.onclick = () => startTaskTimer(pId, t.id, t.title, pName);
            }

            // Checklist
            const items = t.checklist_items || [];
            document.getElementById('task-checklist-count').textContent = items.length;
            const checkContainer = document.getElementById('task-checklist-items-container');
            if (checkContainer) {
                checkContainer.innerHTML = '';
                if (items.length === 0) {
                    checkContainer.innerHTML = '<div style="font-size: 12px; color: var(--text-muted); padding: 8px;">{{ __("No checklist items yet. Add sub-items above.") }}</div>';
                } else {
                    items.forEach(item => {
                        const div = document.createElement('div');
                        div.style = 'display: flex; align-items: center; justify-content: space-between; background: var(--bg-surface-subtle); padding: 8px 12px; border-radius: 8px; border: 1px solid var(--border-color);';
                        div.innerHTML = `
                            <label style="display: flex; align-items: center; gap: 8px; cursor: pointer; font-size: 13px; color: var(--text-primary); text-decoration: ${item.is_completed ? 'line-through' : 'none'}; opacity: ${item.is_completed ? 0.6 : 1};">
                                <input type="checkbox" onchange="toggleTaskChecklistItem('${item.id}')" ${item.is_completed ? 'checked' : ''}>
                                <span>${item.title}</span>
                            </label>
                            <span class="badge ${item.is_completed ? 'badge-green' : 'badge-gray'}" style="font-size: 10px;">${item.is_completed ? '{{ __("Done") }}' : '{{ __("Pending") }}'}</span>
                        `;
                        checkContainer.appendChild(div);
                    });
                }
            }

            // Attachments
            const attachments = t.attachments || [];
            const attCountEl = document.getElementById('task-attachments-count');
            if (attCountEl) attCountEl.textContent = attachments.length;
            const attContainer = document.getElementById('task-attachments-list-container');
            if (attContainer) {
                attContainer.innerHTML = '';
                if (attachments.length === 0) {
                    attContainer.innerHTML = '<div style="font-size: 12px; color: var(--text-muted); padding: 8px; grid-column: 1 / -1;">{{ __("No files attached to this task.") }}</div>';
                } else {
                    attachments.forEach(att => {
                        const card = document.createElement('div');
                        card.style = 'background: var(--bg-surface); border: 1px solid var(--border-color); border-radius: 10px; padding: 10px; display: flex; flex-direction: column; justify-content: space-between; gap: 6px;';
                        const uploader = att.user ? att.user.name : '{{ __("Member") }}';
                        card.innerHTML = `
                            <div style="font-weight: 800; font-size: 12px; color: var(--text-primary); overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">📄 ${att.file_name}</div>
                            <div style="font-size: 10px; color: var(--text-muted);">👤 ${uploader} • ${(att.file_size / 1024).toFixed(1)} KB</div>
                            <div style="display: flex; gap: 6px; margin-top: 4px;">
                                <a href="${att.file_url || ('/uploads/tasks/' + t.id + '/' + att.file_name)}" target="_blank" download class="tactile-btn btn-secondary" style="flex: 1; padding: 4px 8px; font-size: 10px; text-align: center; text-decoration: none;">⬇ {{ __("Download") }}</a>
                                <button type="button" onclick="deleteTaskAttachmentAction('${att.id}')" class="tactile-btn" style="background: rgba(217, 107, 95, 0.15); color: #D96B5F; border: 1px solid rgba(217, 107, 95, 0.3); padding: 4px 8px; font-size: 10px;">🗑️</button>
                            </div>
                        `;
                        attContainer.appendChild(card);
                    });
                }
            }

            // Comments
            const comments = t.comments || [];
            document.getElementById('task-comments-count').textContent = comments.length;
            const commContainer = document.getElementById('task-comments-feed');
            if (commContainer) {
                commContainer.innerHTML = '';
                if (comments.length === 0) {
                    commContainer.innerHTML = '<div style="font-size: 12px; color: var(--text-muted); padding: 8px;">{{ __("No discussions or comments yet.") }}</div>';
                } else {
                    comments.forEach(c => {
                        const box = document.createElement('div');
                        box.style = 'background: var(--bg-surface-subtle); padding: 10px 12px; border-radius: 8px; border: 1px solid var(--border-color); font-size: 12px;';
                        const author = c.user ? c.user.name : '{{ __("Member") }}';
                        const time = new Date(c.created_at).toLocaleString();
                        box.innerHTML = `
                            <div style="display: flex; justify-content: space-between; margin-bottom: 4px; font-size: 11px;">
                                <strong style="color: var(--brand-forest);">👤 ${author}</strong>
                                <span style="color: var(--text-muted);">${time}</span>
                            </div>
                            <div style="color: var(--text-primary); line-height: 1.4;">${c.body || ''}</div>
                        `;
                        commContainer.appendChild(box);
                    });
                }
            }

            // Dependencies
            const deps = t.dependencies || [];
            const depContainer = document.getElementById('task-dependencies-container');
            if (depContainer) {
                depContainer.innerHTML = '';
                if (deps.length === 0) {
                    depContainer.innerHTML = '<div style="font-size: 12px; color: var(--text-muted); padding: 8px;">{{ __("No blocker dependencies. This task can be started immediately.") }}</div>';
                } else {
                    deps.forEach(d => {
                        const item = document.createElement('div');
                        item.style = 'background: var(--bg-surface-subtle); padding: 8px 12px; border-radius: 8px; border: 1px solid var(--border-color); font-size: 12px; display: flex; justify-content: space-between; align-items: center;';
                        const depTask = d.depends_on_task || {};
                        item.innerHTML = `
                            <span>🔒 <strong>{{ __("Depends On:") }}</strong> #${depTask.task_number || ''} ${depTask.title || '{{ __("Predecessor Task") }}'}</span>
                            <span class="badge ${depTask.status === 'done' ? 'badge-green' : 'badge-crimson'}">${statusLabels[depTask.status] || (depTask.status || 'pending')}</span>
                        `;
                        depContainer.appendChild(item);
                    });
                }
            }

            // Time Log
            const timeBody = document.getElementById('task-modal-timelog-body');
            if (timeBody) {
                timeBody.innerHTML = '';
                const entries = t.time_entries || [];
                if (entries.length === 0) {
                    timeBody.innerHTML = '<tr><td colspan="5" style="text-align: center; padding: 14px; color: var(--text-muted);">{{ __("No time tracked on this task yet.") }}</td></tr>';
                } else {
                    entries.forEach(e => {
                        const tr = document.createElement('tr');
                        const hrs = (e.duration_seconds / 3600).toFixed(2);
                        tr.innerHTML = `
                            <td>${new Date(e.started_at).toLocaleDateString()}</td>
                            <td style="font-weight: 700;">${e.user ? e.user.name : '{{ __("Member") }}'}</td>
                            <td style="font-weight: 800; color: var(--brand-forest); font-family: monospace;">${hrs} {{ __("h") }}</td>
                            <td style="font-size: 11px;">${e.description || '{{ __("Work session") }}'}</td>
                            <td><span class="badge ${e.status === 'approved' ? 'badge-green' : 'badge-gray'}">${e.status === 'approved' ? '{{ __("Approved") }}' : '{{ __("Pending") }}'}</span></td>
                        `;
                        timeBody.appendChild(tr);
                    });
                }
            }
        }

        // ==========================================
        // SCHEDULED MEETINGS & SOUND ALERT ENGINE
        // ==========================================
        const upcomingMeetingsList = {!! json_encode($upcomingMeetingsJson ?? []) !!};

        const projectMembersMap = {
            @foreach($projects as $p)
                '{{ $p->id }}': [
                    @php
                        $pMembers = collect();
                        if ($p->owner) $pMembers->push($p->owner);
                        if ($p->manager) $pMembers->push($p->manager);
                        $pTaskUserIds = $p->tasks()->whereNotNull('assignee_id')->pluck('assignee_id')->unique();
                        $pTaskUsers = \App\Domains\Identity\Models\User::whereIn('id', $pTaskUserIds)->get();
                        $pMembers = $pMembers->concat($pTaskUsers)->unique('id');
                    @endphp
                    @foreach($pMembers as $pm)
                        { id: '{{ $pm->id }}', name: '{{ addslashes($pm->name) }}', email: '{{ addslashes($pm->email) }}' },
                    @endforeach
                ],
            @endforeach
        };

        function openScheduleMeetingModal(scope = 'general', projectId = null) {
            const modal = document.getElementById('schedule-meeting-modal');
            if (!modal) return;

            // Set default date-time to now + 30 mins
            const now = new Date();
            now.setMinutes(now.getMinutes() + 30);
            const isoLocal = new Date(now.getTime() - now.getTimezoneOffset() * 60000).toISOString().slice(0, 16);
            const dtInput = document.getElementById('meeting-scheduled-at-input');
            if (dtInput) dtInput.value = isoLocal;

            toggleMeetingScope(scope);

            if (projectId) {
                const projSelect = document.getElementById('meeting-project-select');
                if (projSelect) {
                    projSelect.value = projectId;
                    renderProjectAttendeesList(projectId);
                }
            }

            modal.style.display = 'flex';
        }

        function closeScheduleMeetingModal() {
            const modal = document.getElementById('schedule-meeting-modal');
            if (modal) modal.style.display = 'none';
        }

        function toggleMeetingScope(scope) {
            const isProject = scope === 'project';
            const projField = document.getElementById('meeting-project-field');
            const genField = document.getElementById('meeting-general-attendees-field');
            const lblGeneral = document.getElementById('lbl-scope-general');
            const lblProject = document.getElementById('lbl-scope-project');
            const radioGen = document.querySelector('input[name="scope"][value="general"]');
            const radioProj = document.querySelector('input[name="scope"][value="project"]');

            if (radioGen) radioGen.checked = !isProject;
            if (radioProj) radioProj.checked = isProject;

            if (projField) projField.style.display = isProject ? 'block' : 'none';
            if (genField) genField.style.display = isProject ? 'none' : 'block';

            if (isProject) {
                const projSelect = document.getElementById('meeting-project-select');
                if (projSelect && projSelect.value) {
                    renderProjectAttendeesList(projSelect.value);
                }
            }

            if (lblGeneral && lblProject) {
                if (isProject) {
                    lblProject.style.background = 'var(--bg-surface)';
                    lblProject.style.color = 'var(--brand-forest)';
                    lblProject.style.boxShadow = 'var(--shadow-soft-3d)';
                    lblGeneral.style.background = 'transparent';
                    lblGeneral.style.color = 'var(--text-secondary)';
                    lblGeneral.style.boxShadow = 'none';
                } else {
                    lblGeneral.style.background = 'var(--bg-surface)';
                    lblGeneral.style.color = 'var(--brand-forest)';
                    lblGeneral.style.boxShadow = 'var(--shadow-soft-3d)';
                    lblProject.style.background = 'transparent';
                    lblProject.style.color = 'var(--text-secondary)';
                    lblProject.style.boxShadow = 'none';
                }
            }
        }

        function renderProjectAttendeesList(projectId) {
            const container = document.getElementById('project-attendees-list');
            const box = document.getElementById('project-attendees-selection-box');
            if (!container || !box) return;

            if (!projectId || !projectMembersMap[projectId] || projectMembersMap[projectId].length === 0) {
                box.style.display = 'block';
                container.innerHTML = '<div style="font-size: 11px; color: var(--text-muted); padding: 8px;">{{ __("No assigned members in this project yet. All project roles will be notified automatically.") }}</div>';
                return;
            }

            box.style.display = 'block';
            container.innerHTML = '';

            projectMembersMap[projectId].forEach(m => {
                if (m.id === '{{ $user->id }}') return;
                const label = document.createElement('label');
                label.style = "display: flex; align-items: center; justify-content: space-between; font-size: 12px; color: var(--text-primary); cursor: pointer; padding: 4px 6px; border-radius: 6px; transition: background 0.2s;";
                label.innerHTML = `
                    <span style="display: flex; align-items: center; gap: 8px;">
                        <input type="checkbox" name="attendee_ids[]" value="${m.id}" checked class="proj-attendee-chk" style="accent-color: var(--brand-forest);">
                        <strong>${m.name}</strong>
                        <span style="font-size: 11px; color: var(--text-muted);">(${m.email})</span>
                    </span>
                    <span class="nav-badge-pill" style="font-size: 10px;">{{ __("Project Team") }}</span>
                `;
                container.appendChild(label);
            });
        }

        function toggleAllProjectAttendees() {
            const chks = document.querySelectorAll('.proj-attendee-chk');
            if (!chks.length) return;
            const anyUnchecked = Array.from(chks).some(c => !c.checked);
            chks.forEach(c => c.checked = anyUnchecked);
        }

        async function scheduleMeetingSubmit(e) {
            e.preventDefault();
            const form = e.target;
            const submitBtn = form.querySelector('button[type="submit"]');
            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.innerHTML = '⏳ {{ __("Scheduling Meeting...") }}';
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
                    showToastNotification('📅 ' + data.message);
                    closeScheduleMeetingModal();
                    setTimeout(() => {
                        if (data.redirect_url) {
                            window.location.href = data.redirect_url;
                            window.location.reload();
                        } else {
                            window.location.reload();
                        }
                    }, 500);
                } else {
                    alert(data.message || 'Error scheduling meeting.');
                    if (submitBtn) {
                        submitBtn.disabled = false;
                        submitBtn.innerHTML = '🚀 {{ __("Schedule Meeting & Dispatch Invitations") }}';
                    }
                }
            } catch (err) {
                form.submit();
            }
        }

        function scheduleMeetingForCurrentProject() {
            if (typeof currentHubProjectId !== 'undefined' && currentHubProjectId) {
                openScheduleMeetingModal('project', currentHubProjectId);
            } else {
                openScheduleMeetingModal('project');
            }
        }

        // SMTP Connection Test AJAX
        function testSmtpConnectionAction() {
            const btn = document.getElementById('btn-test-smtp');
            const resultBox = document.getElementById('smtp-test-result-box');
            if (!btn || !resultBox) return;

            const host = document.getElementById('smtp-host-input')?.value;
            const port = document.getElementById('smtp-port-input')?.value;
            const username = document.getElementById('smtp-username-input')?.value;
            const password = document.getElementById('smtp-password-input')?.value;
            const encryption = document.getElementById('smtp-encryption-input')?.value;
            const fromAddr = document.getElementById('smtp-from-email-input')?.value;
            const fromName = document.getElementById('smtp-from-name-input')?.value;

            if (!host || !fromAddr) {
                resultBox.style.display = 'block';
                resultBox.style.background = 'rgba(217, 107, 95, 0.15)';
                resultBox.style.color = '#D96B5F';
                resultBox.style.border = '1px solid rgba(217, 107, 95, 0.3)';
                resultBox.innerHTML = '⚠️ {{ __('Please enter SMTP Host and Sender From Email address.') }}';
                return;
            }

            btn.disabled = true;
            btn.innerHTML = '⏳ {{ __('Testing Connection...') }}';
            resultBox.style.display = 'block';
            resultBox.style.background = 'var(--bg-surface-subtle)';
            resultBox.style.color = 'var(--text-secondary)';
            resultBox.style.border = '1px solid var(--border-color)';
            resultBox.innerHTML = '🔄 {{ __('Connecting to mail server and sending test packet...') }}';

            fetch("{{ route('organization.smtp.test') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json',
                },
                body: JSON.stringify({
                    mail_host: host,
                    mail_port: port,
                    mail_username: username,
                    mail_password: password,
                    mail_encryption: encryption,
                    mail_from_address: fromAddr,
                    mail_from_name: fromName,
                }),
            })
            .then(res => res.json().then(data => ({ status: res.status, body: data })))
            .then(({ status, body }) => {
                btn.disabled = false;
                btn.innerHTML = '🧪 {{ __('Test SMTP Connection') }}';
                if (status === 200 && body.success) {
                    resultBox.style.background = 'rgba(79, 155, 95, 0.15)';
                    resultBox.style.color = '#4F9B5F';
                    resultBox.style.border = '1px solid rgba(79, 155, 95, 0.35)';
                    resultBox.innerHTML = `✅ <strong>${body.message}</strong>`;
                } else {
                    resultBox.style.background = 'rgba(217, 107, 95, 0.15)';
                    resultBox.style.color = '#D96B5F';
                    resultBox.style.border = '1px solid rgba(217, 107, 95, 0.35)';
                    resultBox.innerHTML = `❌ <strong>${body.message || 'SMTP Connection Error'}</strong>`;
                }
            })
            .catch(err => {
                btn.disabled = false;
                btn.innerHTML = '🧪 {{ __('Test SMTP Connection') }}';
                resultBox.style.background = 'rgba(217, 107, 95, 0.15)';
                resultBox.style.color = '#D96B5F';
                resultBox.style.border = '1px solid rgba(217, 107, 95, 0.35)';
                resultBox.innerHTML = `❌ <strong>{{ __('Network error during SMTP test:') }} ${err.message}</strong>`;
            });
        }

        // Harmonic Sound Synthesizer via Web Audio API
        function playMeetingChime() {
            try {
                const AudioCtx = window.AudioContext || window.webkitAudioContext;
                if (!AudioCtx) return;
                const ctx = new AudioCtx();
                if (ctx.state === 'suspended') {
                    ctx.resume();
                }
                const notes = [523.25, 659.25, 783.99, 1046.50]; // C5, E5, G5, C6 bell chord
                notes.forEach((freq, idx) => {
                    const osc = ctx.createOscillator();
                    const gain = ctx.createGain();
                    osc.type = 'sine';
                    osc.frequency.setValueAtTime(freq, ctx.currentTime + idx * 0.09);
                    gain.gain.setValueAtTime(0.0001, ctx.currentTime + idx * 0.09);
                    gain.gain.exponentialRampToValueAtTime(0.25, ctx.currentTime + idx * 0.09 + 0.03);
                    gain.gain.exponentialRampToValueAtTime(0.0001, ctx.currentTime + idx * 0.09 + 1.2);
                    osc.connect(gain);
                    gain.connect(ctx.destination);
                    osc.start(ctx.currentTime + idx * 0.09);
                    osc.stop(ctx.currentTime + idx * 0.09 + 1.3);
                });
            } catch (e) {
                console.log('Audio chime auto-play notification', e);
            }
        }

        // Meeting Alarm Checker (Every 20s)
        const alertedMeetings = new Set();

        function checkMeetingAlarms() {
            if (!upcomingMeetingsList || !upcomingMeetingsList.length) return;
            const now = new Date();

            upcomingMeetingsList.forEach(m => {
                if (!m.scheduled_at) return;
                const sched = new Date(m.scheduled_at);
                const diffMins = (sched - now) / 60000;

                // Trigger chime if within 5 minutes of start time or up to 2 mins after start time
                if (diffMins <= 5 && diffMins >= -2 && !alertedMeetings.has(m.id)) {
                    alertedMeetings.add(m.id);
                    playMeetingChime();

                    const timeLabel = diffMins > 0 ? (Math.ceil(diffMins) + 'm') : '{{ __('is starting now!') }}';
                    const msg = `
                        <div style="display: flex; align-items: center; gap: 12px;">
                            <span style="font-size: 24px;">🔔</span>
                            <div style="flex: 1;">
                                <div style="font-size: 13px; font-weight: 900; color: var(--brand-forest);">${m.title}</div>
                                <div style="font-size: 11px; color: var(--text-secondary);">${m.project_name ? '📁 ' + m.project_name + ' • ' : ''}🚪 ${m.room_name} (${timeLabel})</div>
                            </div>
                            <a href="{{ route('office') }}" class="tactile-btn btn-primary" style="padding: 5px 12px; font-size: 11px; text-decoration: none;">🚀 {{ __('Join') }}</a>
                        </div>
                    `;
                    showToastNotification(msg, 12000);
                }
            });
        }

        setInterval(checkMeetingAlarms, 20000);
        setTimeout(checkMeetingAlarms, 2500);

        // ═════════════════════════════════════════════════════════════════════
        // 💬 REALTIME TEAM CHAT & DIRECT MESSAGES SYSTEM
        // ═════════════════════════════════════════════════════════════════════
        let activeChatChannelId = null;
        let activeChatTargetUserId = null;
        let activeChatMemberId = null;
        let chatPollingInterval = null;
        let cachedChatMembers = [];
        let cachedChatChannels = [];
        let currentUserId = "{{ Auth::id() }}";

        async function loadChatConversations(isManual = false) {
            try {
                const res = await fetch("{{ route('chat.conversations') }}", {
                    headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
                });
                if (!res.ok) return;
                const data = await res.json();
                cachedChatChannels = data.channels || [];
                cachedChatMembers = data.members || [];
                currentUserId = data.current_user_id || currentUserId;

                renderChatRoster(cachedChatChannels, cachedChatMembers);

                if (isManual) {
                    showToastNotification('💬 {{ __('Messages and channels updated.') }}');
                }

                // If currently viewing a chat, refresh its messages
                if (activeChatChannelId) {
                    fetchChatMessages(activeChatChannelId, false);
                }
            } catch (err) {
                console.error('Failed to load chat conversations:', err);
            }
        }

        function renderChatRoster(channels, members) {
            const channelsContainer = document.getElementById('chat-channels-list');
            const membersContainer = document.getElementById('chat-members-list');
            const rosterCount = document.getElementById('chat-roster-count');

            if (rosterCount) rosterCount.textContent = members.length;

            // Render Channels
            if (channelsContainer) {
                if (!channels.length) {
                    channelsContainer.innerHTML = `<div style="padding: 10px 12px; font-size: 11px; color: var(--text-muted); text-align: center;">{{ __('No channels found') }}</div>`;
                } else {
                    channelsContainer.innerHTML = channels.map(c => {
                        const isActive = activeChatChannelId === c.id;
                        const icon = c.type === 'announcement' ? '📢' : (c.type === 'room' ? '🚪' : '#');
                        return `
                            <div onclick="selectChatChannel('${c.id}', '${escapeHtml(c.name)}', '${c.type}', null, null)"
                                 class="chat-roster-item"
                                 data-name="${escapeHtml(c.name).toLowerCase()}"
                                 style="display: flex; align-items: center; justify-content: space-between; padding: 8px 12px; border-radius: 10px; cursor: pointer; transition: all 0.2s; background: ${isActive ? 'rgba(79, 155, 95, 0.15)' : 'transparent'}; border: 1px solid ${isActive ? 'rgba(79, 155, 95, 0.35)' : 'transparent'};"
                                 onmouseover="if(!${isActive}) this.style.background='var(--bg-surface)'"
                                 onmouseout="if(!${isActive}) this.style.background='transparent'">
                                <div style="display: flex; align-items: center; gap: 8px; min-width: 0;">
                                    <span style="font-weight: 900; color: var(--brand-forest); font-size: 13px;">${icon}</span>
                                    <span style="font-size: 12px; font-weight: 700; color: var(--text-primary); white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">${c.name}</span>
                                </div>
                                ${c.last_message ? `<span style="font-size: 10px; color: var(--text-muted);">${c.last_message.created_at}</span>` : ''}
                            </div>
                        `;
                    }).join('');
                }
            }

            // Render Direct Messages Roster
            if (membersContainer) {
                if (!members.length) {
                    membersContainer.innerHTML = `<div style="padding: 10px 12px; font-size: 11px; color: var(--text-muted); text-align: center;">{{ __('No colleagues found') }}</div>`;
                } else {
                    membersContainer.innerHTML = members.map(m => {
                        const isSelected = activeChatTargetUserId === m.user_id;
                        const initials = (m.name || 'User').substring(0, 2).toUpperCase();
                        const lastMsgPreview = m.last_message ? (m.last_message.is_mine ? `{{ __('You') }}: ` : '') + m.last_message.body : m.job_title;

                        return `
                            <div onclick="openChatWithUser('${m.user_id}')"
                                 class="chat-roster-item"
                                 data-name="${escapeHtml(m.name).toLowerCase()} ${escapeHtml(m.nickname || '').toLowerCase()} ${escapeHtml(m.job_title || '').toLowerCase()}"
                                 style="display: flex; align-items: center; gap: 10px; padding: 8px 10px; border-radius: 10px; cursor: pointer; transition: all 0.2s; background: ${isSelected ? 'rgba(79, 155, 95, 0.15)' : 'transparent'}; border: 1px solid ${isSelected ? 'rgba(79, 155, 95, 0.35)' : 'transparent'};"
                                 onmouseover="if(!${isSelected}) this.style.background='var(--bg-surface)'"
                                 onmouseout="if(!${isSelected}) this.style.background='transparent'">
                                <div style="position: relative; width: 34px; height: 34px; border-radius: 10px; background: var(--accent-gradient); display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 12px; color: white; flex-shrink: 0; box-shadow: var(--shadow-soft-3d); overflow: hidden;">
                                    ${m.avatar_url ? `<img src="${m.avatar_url}" style="width:100%;height:100%;object-fit:cover;">` : initials}
                                    <div style="position: absolute; bottom: -1px; inset-inline-end: -1px; width: 10px; height: 10px; border-radius: 50%; background: #4F9B5F; border: 2px solid var(--bg-surface-subtle);" title="Online"></div>
                                </div>
                                <div style="flex: 1; min-width: 0;">
                                    <div style="display: flex; justify-content: space-between; align-items: center;">
                                        <span style="font-size: 12px; font-weight: 800; color: var(--text-primary); white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                            ${escapeHtml(m.name)} ${m.is_self ? '<span style="font-size: 10px; color: var(--text-muted);">({{ __('You') }})</span>' : ''}
                                        </span>
                                        ${m.last_message ? `<span style="font-size: 9px; color: var(--text-muted); margin-inline-start: 4px;">${m.last_message.created_at}</span>` : ''}
                                    </div>
                                    <div style="font-size: 11px; color: var(--text-secondary); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; margin-top: 1px;">
                                        ${escapeHtml(lastMsgPreview)}
                                    </div>
                                </div>
                            </div>
                        `;
                    }).join('');
                }
            }
        }

        function filterChatRoster() {
            const q = (document.getElementById('chat-search-input').value || '').toLowerCase().trim();
            document.querySelectorAll('.chat-roster-item').forEach(item => {
                const name = item.getAttribute('data-name') || '';
                item.style.display = (!q || name.includes(q)) ? 'flex' : 'none';
            });
        }

        async function openChatWithUser(targetUserId) {
            switchAdminTab('chat');
            try {
                const res = await fetch(`{{ url('/chat/dm') }}/${targetUserId}`, {
                    headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
                });
                if (!res.ok) throw new Error('Failed to initiate direct message.');
                const data = await res.json();
                const channel = data.channel;
                const targetUser = data.target_user;

                // Find corresponding memberId
                const memberObj = cachedChatMembers.find(m => m.user_id == targetUserId);
                activeChatMemberId = memberObj ? memberObj.id : null;

                selectChatChannel(channel.id, targetUser.name, 'dm', targetUserId, activeChatMemberId);
            } catch (err) {
                console.error(err);
                showToastNotification('❌ ' + err.message);
            }
        }

        function selectFirstColleagueChat() {
            if (cachedChatMembers && cachedChatMembers.length) {
                const firstOther = cachedChatMembers.find(m => !m.is_self) || cachedChatMembers[0];
                openChatWithUser(firstOther.user_id);
            } else if (cachedChatChannels && cachedChatChannels.length) {
                const firstCh = cachedChatChannels[0];
                selectChatChannel(firstCh.id, firstCh.name, firstCh.type, null, null);
            }
        }

        function selectChatChannel(channelId, channelName, channelType, targetUserId = null, memberId = null) {
            activeChatChannelId = channelId;
            activeChatTargetUserId = targetUserId;
            activeChatMemberId = memberId;

            // Hide empty state, show active state
            const emptyState = document.getElementById('chat-empty-state');
            const activeState = document.getElementById('chat-active-state');
            if (emptyState) emptyState.style.display = 'none';
            if (activeState) activeState.style.display = 'flex';

            // Update Header Info
            const titleEl = document.getElementById('chat-active-title');
            const subtitleEl = document.getElementById('chat-active-subtitle');
            const badgeEl = document.getElementById('chat-active-badge');
            const avatarBox = document.getElementById('chat-active-avatar-box');
            const avatarInitials = document.getElementById('chat-active-avatar-initials');
            const profileBtn = document.getElementById('chat-view-profile-btn');

            if (titleEl) titleEl.textContent = channelType === 'dm' ? channelName : '#' + channelName;
            if (badgeEl) badgeEl.textContent = channelType === 'dm' ? 'Direct Message' : 'Channel';

            if (channelType === 'dm') {
                const memberObj = cachedChatMembers.find(m => m.user_id == targetUserId);
                if (memberObj) {
                    activeChatMemberId = memberObj.id;
                    if (subtitleEl) subtitleEl.textContent = `${memberObj.job_title} • ${memberObj.role}`;
                }
                if (avatarInitials) avatarInitials.textContent = (channelName || 'U').substring(0, 2).toUpperCase();
                if (profileBtn) profileBtn.style.display = 'inline-flex';
            } else {
                if (subtitleEl) subtitleEl.textContent = `Company Channel • All Members`;
                if (avatarInitials) avatarInitials.textContent = '#';
                if (profileBtn) profileBtn.style.display = 'none';
            }

            renderChatRoster(cachedChatChannels, cachedChatMembers);
            fetchChatMessages(channelId, true);

            // Focus input
            setTimeout(() => {
                const input = document.getElementById('chat-message-input');
                if (input) input.focus();
            }, 100);

            // Start auto polling
            if (chatPollingInterval) clearInterval(chatPollingInterval);
            chatPollingInterval = setInterval(() => {
                if (activeChatChannelId && document.getElementById('tab-chat')?.classList.contains('active')) {
                    fetchChatMessages(activeChatChannelId, false);
                }
            }, 3500);
        }

        async function fetchChatMessages(channelId, scrollToBottom = true) {
            try {
                const res = await fetch(`{{ url('/chat/channels') }}/${channelId}/messages`, {
                    headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
                });
                if (!res.ok) return;
                const data = await res.json();
                const container = document.getElementById('chat-messages-container');
                if (!container) return;

                if (!data.messages || !data.messages.length) {
                    container.innerHTML = `
                        <div style="text-align: center; color: var(--text-muted); font-size: 13px; padding: 40px;">
                            <div style="font-size: 28px; margin-bottom: 8px;">👋</div>
                            <div style="font-weight: 700;">{{ __('No messages in this conversation yet.') }}</div>
                            <div style="font-size: 11px; margin-top: 4px;">{{ __('Send a message below to start the discussion!') }}</div>
                        </div>
                    `;
                    return;
                }

                container.innerHTML = data.messages.map(msg => {
                    const isMine = msg.is_mine;
                    const initials = (msg.sender.name || 'U').substring(0, 2).toUpperCase();

                    return `
                        <div style="display: flex; gap: 10px; align-items: flex-end; justify-content: ${isMine ? 'flex-end' : 'flex-start'};">
                            ${!isMine ? `
                                <div style="width: 30px; height: 30px; border-radius: 8px; background: var(--accent-gradient); display: flex; align-items: center; justify-content: center; font-size: 11px; font-weight: 800; color: white; flex-shrink: 0; box-shadow: var(--shadow-soft-3d); overflow: hidden;">
                                    ${msg.sender.avatar_url ? `<img src="${msg.sender.avatar_url}" style="width:100%;height:100%;object-fit:cover;">` : initials}
                                </div>
                            ` : ''}

                            <div style="max-width: 70%; display: flex; flex-direction: column; align-items: ${isMine ? 'flex-end' : 'flex-start'};">
                                ${!isMine ? `<span style="font-size: 10px; font-weight: 800; color: var(--text-secondary); margin-bottom: 2px; margin-inline-start: 4px;">${escapeHtml(msg.sender.name)}</span>` : ''}
                                
                                <div style="padding: 10px 14px; border-radius: ${isMine ? '14px 14px 2px 14px' : '14px 14px 14px 2px'}; background: ${isMine ? 'var(--accent-gradient)' : 'var(--bg-surface)'}; color: ${isMine ? '#FFFDF6' : 'var(--text-primary)'}; border: 1px solid ${isMine ? 'transparent' : 'var(--border-color)'}; box-shadow: var(--shadow-soft-3d); font-size: 13px; line-height: 1.5; word-break: break-word;">
                                    ${escapeHtml(msg.body).replace(/\\n/g, '<br>')}
                                </div>
                                
                                <span style="font-size: 9px; color: var(--text-muted); margin-top: 3px; margin-inline-start: 4px; margin-inline-end: 4px;">
                                    ${msg.created_at}
                                </span>
                            </div>
                        </div>
                    `;
                }).join('');

                if (scrollToBottom) {
                    container.scrollTop = container.scrollHeight;
                }
            } catch (err) {
                console.error('Failed to fetch messages:', err);
            }
        }

        async function handleSendChatMessage(event) {
            if (event) event.preventDefault();
            if (!activeChatChannelId) return;

            const input = document.getElementById('chat-message-input');
            const body = (input?.value || '').trim();
            if (!body) return;

            input.value = '';

            try {
                const res = await fetch(`{{ url('/chat/channels') }}/${activeChatChannelId}/messages`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': CSRF_TOKEN,
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify({ body })
                });

                if (!res.ok) throw new Error('Failed to send message.');

                fetchChatMessages(activeChatChannelId, true);
                loadChatConversations(false);
            } catch (err) {
                showToastNotification('❌ ' + err.message);
            }
        }

        function handleChatInputKeydown(event) {
            if (event.key === 'Enter' && !event.shiftKey) {
                event.preventDefault();
                handleSendChatMessage();
            }
        }

        function viewActiveChatUserProfile() {
            if (activeChatMemberId) {
                openMemberProfileModal(activeChatMemberId);
            } else if (activeChatTargetUserId) {
                const memberObj = cachedChatMembers.find(m => m.user_id == activeChatTargetUserId);
                if (memberObj) openMemberProfileModal(memberObj.id);
            }
        }

        // ═════════════════════════════════════════════════════════════════════
        // 👤 COMPREHENSIVE TEAM MEMBER PROFILE MODAL SYSTEM
        // ═════════════════════════════════════════════════════════════════════
        let currentModalMemberData = null;

        async function openMemberProfileModal(memberId) {
            const modal = document.getElementById('member-details-modal');
            if (!modal) return;

            modal.style.display = 'flex';
            switchMemberProfileTab('about');

            // Reset placeholders
            document.getElementById('mp-user-name').textContent = '{{ __('Loading...') }}';
            document.getElementById('mp-info-email').textContent = '—';
            document.getElementById('mp-info-bio').textContent = '{{ __('Fetching profile details from workspace database...') }}';

            try {
                const res = await fetch(`{{ url('/organization/members') }}/${memberId}/details`, {
                    headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
                });
                if (!res.ok) throw new Error('Failed to load member profile.');
                const data = await res.json();
                currentModalMemberData = data;

                const m = data.member;
                const p = data.profile;
                const s = data.stats;

                // Hero Details
                document.getElementById('mp-user-name').textContent = m.name;
                document.getElementById('mp-user-nickname').textContent = m.nickname ? `@${m.nickname}` : `@${m.name.toLowerCase().replace(/\\s+/g, '')}`;
                document.getElementById('mp-user-role').textContent = m.role_name;
                document.getElementById('mp-job-title').textContent = p.job_title || m.role_name;
                document.getElementById('mp-dept-team').textContent = `${p.department_name || '{{ __('General') }}'} • ${p.team_name || '{{ __('Core Team') }}'}`;
                
                const workModePill = document.getElementById('mp-work-mode');
                if (workModePill) {
                    const modeLabels = { 'remote': '🏠 {{ __('Remote') }}', 'hybrid': '🔄 {{ __('Hybrid') }}', 'onsite': '🏢 {{ __('On-site') }}' };
                    workModePill.textContent = modeLabels[p.work_mode] || '🏠 {{ __('Remote') }}';
                }

                // Avatar
                const imgEl = document.getElementById('mp-avatar-img');
                const fallbackEl = document.getElementById('mp-avatar-fallback');
                if (m.avatar_url) {
                    imgEl.src = m.avatar_url;
                    imgEl.style.display = 'block';
                    fallbackEl.style.display = 'none';
                } else {
                    imgEl.style.display = 'none';
                    fallbackEl.style.display = 'block';
                    fallbackEl.textContent = (m.name || 'U').substring(0, 2).toUpperCase();
                }

                // Tab 1: About & Info
                document.getElementById('mp-info-email').textContent = m.email;
                document.getElementById('mp-info-phone').textContent = p.phone || '—';
                document.getElementById('mp-info-dob').textContent = p.date_of_birth || '—';
                document.getElementById('mp-info-joined').textContent = m.joined_at;
                document.getElementById('mp-info-bio').textContent = p.bio || '{{ __('No bio or summary added yet.') }}';

                // Skills
                const skillsContainer = document.getElementById('mp-info-skills');
                if (skillsContainer) {
                    if (p.skills && p.skills.length) {
                        skillsContainer.innerHTML = p.skills.map(sk => `<span class="nav-badge-pill" style="background: rgba(79, 155, 95, 0.15); color: #4F9B5F; font-size: 11px; font-weight: 700;">⚡ ${escapeHtml(sk)}</span>`).join('');
                    } else {
                        skillsContainer.innerHTML = `<span style="font-size: 11px; color: var(--text-muted); font-style: italic;">— {{ __('No skills listed') }} —</span>`;
                    }
                }

                // Hobbies
                const hobbiesContainer = document.getElementById('mp-info-hobbies');
                if (hobbiesContainer) {
                    if (p.hobbies && p.hobbies.length) {
                        hobbiesContainer.innerHTML = p.hobbies.map(hb => `<span class="nav-badge-pill" style="background: rgba(214, 162, 58, 0.15); color: #D6A23A; font-size: 11px; font-weight: 700;">🎯 ${escapeHtml(hb)}</span>`).join('');
                    } else {
                        hobbiesContainer.innerHTML = `<span style="font-size: 11px; color: var(--text-muted); font-style: italic;">— {{ __('No hobbies listed') }} —</span>`;
                    }
                }

                // Social Links
                const socialsContainer = document.getElementById('mp-info-socials');
                if (socialsContainer) {
                    const links = p.social_links || {};
                    const socialHtml = [];
                    if (links.linkedin) socialHtml.push(`<a href="${links.linkedin}" target="_blank" class="tactile-btn btn-secondary" style="font-size: 11px; padding: 4px 10px; text-decoration: none;">💼 LinkedIn</a>`);
                    if (links.github) socialHtml.push(`<a href="${links.github}" target="_blank" class="tactile-btn btn-secondary" style="font-size: 11px; padding: 4px 10px; text-decoration: none;">🐙 GitHub</a>`);
                    if (links.twitter) socialHtml.push(`<a href="${links.twitter}" target="_blank" class="tactile-btn btn-secondary" style="font-size: 11px; padding: 4px 10px; text-decoration: none;">🐦 X (Twitter)</a>`);
                    if (links.website) socialHtml.push(`<a href="${links.website}" target="_blank" class="tactile-btn btn-secondary" style="font-size: 11px; padding: 4px 10px; text-decoration: none;">🌐 Website</a>`);

                    socialsContainer.innerHTML = socialHtml.length ? socialHtml.join('') : `<span style="font-size: 11px; color: var(--text-muted); font-style: italic;">— {{ __('No social links attached') }} —</span>`;
                }

                // Notes
                const notesBox = document.getElementById('mp-notes-container');
                const notesText = document.getElementById('mp-info-notes');
                if (p.notes) {
                    notesBox.style.display = 'block';
                    notesText.textContent = p.notes;
                } else {
                    notesBox.style.display = 'none';
                }

                // Tab 2: Assigned Tasks
                document.getElementById('mp-tasks-count-pill').textContent = s.total_tasks;
                document.getElementById('mp-task-stat-total').textContent = s.total_tasks;
                document.getElementById('mp-task-stat-progress').textContent = s.in_progress_tasks;
                document.getElementById('mp-task-stat-pending').textContent = s.pending_tasks;
                document.getElementById('mp-task-stat-done').textContent = s.completed_tasks;

                const tasksContainer = document.getElementById('mp-tasks-list-container');
                if (tasksContainer) {
                    if (!data.tasks || !data.tasks.length) {
                        tasksContainer.innerHTML = `<div style="text-align: center; color: var(--text-muted); font-size: 12px; padding: 30px;">{{ __('No tasks assigned to this member.') }}</div>`;
                    } else {
                        tasksContainer.innerHTML = data.tasks.map(t => {
                            const priorityColors = {
                                'urgent': 'background: rgba(217, 107, 95, 0.15); color: #D96B5F;',
                                'high': 'background: rgba(214, 162, 58, 0.15); color: #D6A23A;',
                                'normal': 'background: rgba(79, 155, 95, 0.15); color: #4F9B5F;',
                                'low': 'background: rgba(148, 163, 184, 0.15); color: #64748B;'
                            };
                            return `
                                <div style="display: flex; justify-content: space-between; align-items: center; padding: 12px 14px; border-radius: 12px; background: var(--bg-surface-subtle); border: 1px solid var(--border-color); box-shadow: var(--shadow-soft-3d); gap: 12px; flex-wrap: wrap;">
                                    <div style="display: flex; align-items: center; gap: 10px; min-width: 0;">
                                        <span class="nav-badge-pill" style="font-family: monospace; font-size: 10px; font-weight: 800;">#${t.task_number}</span>
                                        <div>
                                            <div style="font-weight: 800; font-size: 13px; color: var(--text-primary);">${escapeHtml(t.title)}</div>
                                            <div style="display: flex; align-items: center; gap: 8px; margin-top: 2px; font-size: 11px; color: var(--text-secondary);">
                                                <span>📁 ${escapeHtml(t.project ? t.project.name : 'General')}</span>
                                                ${t.due_date ? `<span>• 📅 ${t.due_date} ${t.is_overdue ? '<span style="color:#D96B5F;font-weight:800;">({{ __('Overdue') }})</span>' : ''}</span>` : ''}
                                                ${t.checklist_count ? `<span>• ☑️ ${t.checklist_done}/${t.checklist_count}</span>` : ''}
                                            </div>
                                        </div>
                                    </div>
                                    <div style="display: flex; align-items: center; gap: 8px;">
                                        <span class="nav-badge-pill" style="${priorityColors[t.priority] || ''}; font-size: 10px; text-transform: uppercase;">${t.priority}</span>
                                        <span class="nav-badge-pill" style="font-size: 10px;">${t.status.replace('_', ' ')}</span>
                                    </div>
                                </div>
                            `;
                        }).join('');
                    }
                }

                // Tab 3: Work Time & Logs
                document.getElementById('mp-hours-count-pill').textContent = `${s.total_hours_logged}h`;
                document.getElementById('mp-time-total-hours').textContent = `${s.total_hours_logged}h`;

                const timerText = document.getElementById('mp-active-timer-text');
                if (s.active_timer) {
                    timerText.innerHTML = `<strong>⏱️ ${s.active_timer.project_name || 'Project'}</strong>: ${s.active_timer.task_title || 'Work Session'}`;
                } else {
                    timerText.textContent = '{{ __('No active timer running') }}';
                }

                const tbody = document.getElementById('mp-time-entries-tbody');
                if (tbody) {
                    if (!data.time_entries || !data.time_entries.length) {
                        tbody.innerHTML = `<tr><td colspan="5" style="text-align: center; color: var(--text-muted); padding: 20px;">{{ __('No work logs recorded yet.') }}</td></tr>`;
                    } else {
                        tbody.innerHTML = data.time_entries.map(te => `
                            <tr>
                                <td style="font-size: 12px; font-weight: 700; color: var(--text-primary);">${te.date}</td>
                                <td style="font-size: 12px; font-weight: 700; color: var(--brand-forest);">📁 ${escapeHtml(te.project_name)}</td>
                                <td style="font-size: 12px; color: var(--text-secondary);">${escapeHtml(te.task_title)}</td>
                                <td><span class="nav-badge-pill" style="background: rgba(79, 155, 95, 0.15); color: #4F9B5F; font-weight: 800;">${te.duration_hours}h</span></td>
                                <td style="font-size: 11px; color: var(--text-muted);">${escapeHtml(te.description)}</td>
                            </tr>
                        `).join('');
                    }
                }

            } catch (err) {
                console.error(err);
                showToastNotification('❌ ' + err.message);
            }
        }

        function switchMemberProfileTab(tabName) {
            document.querySelectorAll('.member-profile-tab-btn').forEach(btn => {
                btn.style.color = 'var(--text-secondary)';
                btn.style.borderBottomColor = 'transparent';
                btn.classList.remove('active');
            });
            const activeBtn = document.getElementById(`mp-tab-btn-${tabName}`);
            if (activeBtn) {
                activeBtn.style.color = 'var(--brand-forest)';
                activeBtn.style.borderBottomColor = 'var(--brand-forest)';
                activeBtn.classList.add('active');
            }

            document.getElementById('mp-tab-content-about').style.display = tabName === 'about' ? 'flex' : 'none';
            document.getElementById('mp-tab-content-tasks').style.display = tabName === 'tasks' ? 'flex' : 'none';
            document.getElementById('mp-tab-content-time').style.display = tabName === 'time' ? 'flex' : 'none';
        }

        function closeMemberProfileModal() {
            const modal = document.getElementById('member-details-modal');
            if (modal) modal.style.display = 'none';
        }

        function openChatFromProfileModal() {
            if (currentModalMemberData && currentModalMemberData.member) {
                const targetUserId = currentModalMemberData.member.user_id;
                closeMemberProfileModal();
                openChatWithUser(targetUserId);
            }
        }

        async function testOrgAiConnectionAction() {
            const keyInput = document.getElementById('org-openai-key-input');
            const resultBox = document.getElementById('org-ai-test-result-box');
            const btn = document.getElementById('btn-test-org-ai');
            const apiKey = keyInput ? keyInput.value.trim() : '';

            if (!apiKey && (!keyInput.placeholder || keyInput.placeholder.includes('sk-'))) {
                resultBox.style.display = 'block';
                resultBox.style.background = 'rgba(239, 68, 68, 0.15)';
                resultBox.style.border = '1px solid rgba(239, 68, 68, 0.3)';
                resultBox.style.color = '#EF4444';
                resultBox.innerText = '{{ __("Please enter an OpenAI API key first.") }}';
                return;
            }

            resultBox.style.display = 'block';
            resultBox.style.background = 'rgba(59, 130, 246, 0.15)';
            resultBox.style.border = '1px solid rgba(59, 130, 246, 0.3)';
            resultBox.style.color = '#3B82F6';
            resultBox.innerText = '⚡ {{ __("Testing OpenAI API key connectivity...") }}';
            if (btn) btn.disabled = true;

            try {
                const res = await fetch('{{ route("organization.ai.test") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ api_key: apiKey })
                });
                const data = await res.json();
                if (res.ok && data.success) {
                    resultBox.style.background = 'rgba(16, 185, 129, 0.15)';
                    resultBox.style.border = '1px solid rgba(16, 185, 129, 0.3)';
                    resultBox.style.color = '#10B981';
                    resultBox.innerText = data.message || '{{ __("✅ Key is valid and active!") }}';
                } else {
                    resultBox.style.background = 'rgba(239, 68, 68, 0.15)';
                    resultBox.style.border = '1px solid rgba(239, 68, 68, 0.3)';
                    resultBox.style.color = '#EF4444';
                    resultBox.innerText = '❌ ' + (data.message || '{{ __("Connection failed.") }}');
                }
            } catch (e) {
                resultBox.style.background = 'rgba(239, 68, 68, 0.15)';
                resultBox.style.border = '1px solid rgba(239, 68, 68, 0.3)';
                resultBox.style.color = '#EF4444';
                resultBox.innerText = '❌ Network error: ' + e.message;
            } finally {
                if (btn) btn.disabled = false;
            }
        }

        function escapeHtml(str) {
            if (!str) return '';
            return String(str).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;').replace(/'/g, '&#039;');
        }
    </script>
</body>
</html>
