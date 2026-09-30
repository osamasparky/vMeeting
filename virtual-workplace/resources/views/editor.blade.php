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
    <title>{{ __('Map Editor & Floor Designer') }} — {{ $map->name }}</title>

    <!-- Google Fonts & Material Symbols -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;500;600;700;800;900&family=IBM+Plex+Mono:wght@400;500;600;700&family=IBM+Plex+Sans+Arabic:wght@300;400;500;600;700;800&family=IBM+Plex+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;1,400&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Rounded:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" />
    <link rel="stylesheet" href="https://fonts.googleapis.com/icon?family=Material+Icons|Material+Icons+Round|Material+Icons+Outlined" />

    <!-- UlaSpace Design Tokens & Office Stylesheet -->
    <link rel="stylesheet" href="{{ asset('css/ulaspace-tokens.css') }}">
    <link rel="stylesheet" href="{{ asset('css/ulaspace-office.css') }}">
    <link rel="stylesheet" href="{{ asset('css/modern-design-system.css') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        /* ══ Map editor — light chrome (design-reference 33, re-toned light) ══════════════
           Semantic tokens only, so the dark theme still follows data-theme / prefers-color-scheme.
           The .nx-* toolbar classes come from ulaspace-office.css (the live office keeps its dark
           capsule chrome); they are re-skinned here only inside .nx-editor-screen. */

        @layer base { * { margin: 0; padding: 0; box-sizing: border-box; } }
        body * { user-select: none; }
        body input, body textarea, body select { user-select: text; }

        body {
            font-family: var(--ula-font-ar), var(--ula-font-en), sans-serif;
            background: var(--ula-surface-page);
            color: var(--ula-text-primary);
            height: 100vh;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            -webkit-font-smoothing: antialiased;
        }

        .nx-office-viewport-container { background: var(--ula-surface-page); padding: 0 !important; }

        .nx-editor-screen {
            width: 100%;
            height: 100vh;
            background: var(--ula-surface-page);
            display: flex;
            flex-direction: column;
            overflow: hidden;
            position: relative;
        }

        /* ── Top toolbar ── */
        .nx-editor-screen .nx-map-toolbar {
            height: 64px;
            min-height: 64px;
            padding: 0 var(--ula-space-5);
            gap: var(--ula-space-4);
            background: var(--ula-surface-card);
            backdrop-filter: none;
            border-bottom: var(--ula-border-width-hairline) solid var(--ula-border-subtle);
            box-shadow: var(--ula-shadow-xs);
            z-index: 100;
        }
        .nx-editor-screen .nx-toolbar-group { gap: var(--ula-space-2); min-width: 0; }
        .ed-toolbar-sep { width: 1px; height: 28px; background: var(--ula-border-subtle); flex-shrink: 0; }

        .nx-editor-screen .nx-toolbar-btn {
            height: 40px;
            padding: 0 var(--ula-space-4);
            gap: var(--ula-space-2);
            border-radius: var(--ula-radius-sm);
            background: var(--ula-surface-card);
            border: var(--ula-border-width-hairline) solid var(--ula-border-default);
            color: var(--ula-text-primary);
            font-family: inherit;
            font-size: var(--ula-size-sm);
            font-weight: var(--ula-weight-semibold);
            transition: background-color var(--ula-duration-fast) var(--ula-ease-out), border-color var(--ula-duration-fast) var(--ula-ease-out), color var(--ula-duration-fast) var(--ula-ease-out);
        }
        .nx-editor-screen .nx-toolbar-btn:hover {
            background: var(--ula-surface-hover);
            border-color: var(--ula-border-strong);
            color: var(--ula-text-primary);
            transform: none;
        }
        .nx-editor-screen .nx-toolbar-btn:focus-visible,
        .tool-btn:focus-visible, .view-btn:focus-visible, .drawer-tab:focus-visible,
        .cat-pill:focus-visible, .furn-card:focus-visible, .rot-btn:focus-visible { outline: none; box-shadow: var(--ula-focus-ring); }
        .nx-editor-screen .nx-toolbar-btn .material-symbols-rounded { font-size: 20px; }
        .nx-editor-screen .nx-toolbar-btn.ed-icon-only { width: 40px; padding: 0; justify-content: center; }
        .nx-editor-screen .nx-toolbar-btn.ed-danger { color: var(--ula-text-danger); }
        .nx-editor-screen .nx-toolbar-btn.ed-danger:hover { background: var(--ula-tone-terracotta-bg); border-color: var(--ula-border-danger); color: var(--ula-text-danger); }
        /* Green acts: publish is the one filled action. */
        .nx-editor-screen .nx-toolbar-btn.ed-primary { background: var(--ula-accent-default); border-color: var(--ula-accent-default); color: var(--ula-accent-fg); }
        .nx-editor-screen .nx-toolbar-btn.ed-primary:hover { background: var(--ula-accent-hover); border-color: var(--ula-accent-hover); color: var(--ula-accent-fg); }
        /* Gold notices: the AI entry point keeps a gold icon, never a gold fill. */
        .nx-editor-screen .nx-toolbar-btn.btn-accent { background: var(--ula-surface-card); border-color: var(--ula-border-default); color: var(--ula-text-primary); }
        .nx-editor-screen .nx-toolbar-btn.btn-accent .material-symbols-rounded { color: var(--ula-highlight-default); }
        .nx-editor-screen .nx-toolbar-btn.btn-accent:hover { background: var(--ula-surface-gold-soft); border-color: var(--ula-highlight-default); }
        .nx-editor-screen .nx-toolbar-btn.ed-branch .material-symbols-rounded:first-child { color: var(--ula-icon-accent); }

        .nx-editor-screen .nx-brand-capsule {
            height: 40px;
            padding: 0 var(--ula-space-3);
            gap: var(--ula-space-3);
            border-radius: var(--ula-radius-sm);
            background: transparent;
            border: 0;
            color: var(--ula-text-primary);
            font-size: var(--ula-size-body);
            font-weight: var(--ula-weight-semibold);
        }
        .nx-editor-screen .nx-brand-capsule:hover { background: var(--ula-surface-hover); }
        .ed-brand-logo { width: 32px; height: 32px; border-radius: var(--ula-radius-xs); background: var(--ula-brand-mark-ivory); border: var(--ula-border-width-hairline) solid var(--ula-border-subtle); display: inline-flex; align-items: center; justify-content: center; overflow: hidden; flex-shrink: 0; }
        .ed-brand-logo img { width: 100%; height: 100%; object-fit: contain; padding: 2px; }
        .ed-brand-logo .material-symbols-rounded { font-size: 20px; color: var(--ula-brand-mark-green); }
        .ed-brand-text { display: flex; flex-direction: column; line-height: 1.2; min-width: 0; }
        .ed-brand-text strong { font-size: var(--ula-size-body-en); font-weight: var(--ula-weight-semibold); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 180px; }
        .ed-brand-text span { font-size: var(--ula-size-xs); font-weight: var(--ula-weight-medium); color: var(--ula-text-secondary); }
        .nx-editor-screen .nx-presence-dot { box-shadow: none; }

        .nx-editor-screen .nx-presence-capsule {
            height: 28px;
            padding: 0 var(--ula-space-3);
            border-radius: var(--ula-radius-pill);
            background: var(--ula-tone-palm-bg);
            border: 0;
            color: var(--ula-tone-palm-fg);
            font-family: var(--ula-font-mono);
            font-size: var(--ula-size-xs);
            direction: ltr;
            unicode-bidi: isolate;
        }

        /* Pop-over menus (burger + branch switcher) */
        .ed-menu {
            display: none;
            position: absolute;
            top: calc(100% + 8px);
            inset-inline-start: 0;
            min-width: 260px;
            padding: var(--ula-space-2);
            background: var(--ula-surface-raised);
            border: var(--ula-border-width-hairline) solid var(--ula-border-subtle);
            border-radius: var(--ula-radius-md);
            box-shadow: var(--ula-shadow-lg);
            z-index: 100000;
        }
        .ed-menu-head { display: flex; align-items: center; gap: var(--ula-space-3); padding: var(--ula-space-2) var(--ula-space-3) var(--ula-space-3); border-bottom: var(--ula-border-width-hairline) solid var(--ula-border-subtle); margin-bottom: var(--ula-space-2); }
        .ed-menu-head strong { display: block; font-size: var(--ula-size-sm); color: var(--ula-text-primary); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .ed-menu-head span { font-size: var(--ula-size-xs); color: var(--ula-text-secondary); }
        .ed-menu-label { font-size: var(--ula-size-xs); font-weight: var(--ula-weight-semibold); color: var(--ula-text-muted); padding: var(--ula-space-2) var(--ula-space-3); display: flex; align-items: center; gap: var(--ula-space-2); }
        .ed-menu-divider { height: 1px; background: var(--ula-border-subtle); margin: var(--ula-space-2) 0; }
        .more-menu-item, .ed-menu-item {
            width: 100%;
            min-height: 40px;
            padding: 0 var(--ula-space-3);
            display: flex;
            align-items: center;
            gap: var(--ula-space-3);
            border: 0;
            border-radius: var(--ula-radius-sm);
            background: transparent;
            color: var(--ula-text-primary);
            font-family: inherit;
            font-size: var(--ula-size-sm);
            font-weight: var(--ula-weight-medium);
            text-align: start;
            text-decoration: none;
            cursor: pointer;
            transition: background-color var(--ula-duration-fast) var(--ula-ease-out);
        }
        .more-menu-item .material-symbols-rounded, .ed-menu-item .material-symbols-rounded { font-size: 20px; color: var(--ula-text-secondary); }
        .more-menu-item:hover, .ed-menu-item:hover { background: var(--ula-surface-hover); color: var(--ula-text-primary); }
        .more-menu-item.ed-accent, .more-menu-item.ed-accent .material-symbols-rounded { color: var(--ula-accent-default); }
        .ed-menu-item { justify-content: space-between; }
        .ed-menu-item.active { background: var(--ula-surface-accent-soft); color: var(--ula-accent-default); font-weight: var(--ula-weight-semibold); }
        .ed-menu-item.active .material-symbols-rounded { color: var(--ula-accent-default); }
        .ed-menu-item-meta { font-size: var(--ula-size-xs); color: var(--ula-accent-default); font-weight: var(--ula-weight-semibold); }

        /* Tool selector */
        .segmented-tool-pill {
            display: flex;
            align-items: center;
            gap: 2px;
            padding: 3px;
            background: var(--ula-surface-page-alt);
            border: var(--ula-border-width-hairline) solid var(--ula-border-subtle);
            border-radius: var(--ula-radius-md);
        }
        .tool-btn {
            display: flex;
            align-items: center;
            gap: var(--ula-space-2);
            height: 34px;
            padding: 0 var(--ula-space-4);
            background: transparent;
            border: var(--ula-border-width-hairline) solid transparent;
            border-radius: var(--ula-radius-sm);
            color: var(--ula-text-secondary);
            font-family: inherit;
            font-size: var(--ula-size-sm);
            font-weight: var(--ula-weight-semibold);
            cursor: pointer;
            transition: background-color var(--ula-duration-fast) var(--ula-ease-out), color var(--ula-duration-fast) var(--ula-ease-out);
        }
        .tool-btn .material-symbols-rounded { font-size: 20px; }
        /* Icon-only like the reference; the label stays in the title tooltip. */
        .segmented-tool-pill .tool-btn { width: 40px; padding: 0; justify-content: center; }
        .segmented-tool-pill .tool-btn > span:not(.material-symbols-rounded) { display: none; }
        .tool-btn:hover { background: var(--ula-surface-hover); color: var(--ula-text-primary); }
        .tool-btn.active {
            background: var(--ula-surface-raised);
            border-color: var(--ula-border-subtle);
            color: var(--ula-accent-default);
            box-shadow: var(--ula-shadow-xs);
        }

        .tool-icon-btn {
            display: flex; align-items: center; justify-content: center;
            width: 36px; height: 36px;
            background: var(--ula-surface-card);
            border: var(--ula-border-width-hairline) solid var(--ula-border-default);
            border-radius: var(--ula-radius-sm);
            color: var(--ula-text-secondary);
            cursor: pointer;
        }
        .tool-icon-btn:hover { background: var(--ula-surface-hover); color: var(--ula-text-primary); }
        .tool-icon-btn.danger:hover { background: var(--ula-tone-terracotta-bg); border-color: var(--ula-border-danger); color: var(--ula-text-danger); }

        /* Legacy dropdown hooks still used by scripts */
        .editor-dropdown-menu {
            position: absolute; top: calc(100% + 8px); inset-inline-start: 0;
            background: var(--ula-surface-raised);
            border: var(--ula-border-width-hairline) solid var(--ula-border-subtle);
            border-radius: var(--ula-radius-md);
            box-shadow: var(--ula-shadow-lg);
            padding: var(--ula-space-2);
            z-index: 100000;
            display: flex; flex-direction: column; gap: 2px;
        }
        .editor-dropdown-item {
            display: flex; align-items: center; justify-content: space-between; gap: var(--ula-space-3);
            padding: var(--ula-space-2) var(--ula-space-3);
            border-radius: var(--ula-radius-sm);
            text-decoration: none; background: transparent; border: 0;
            color: var(--ula-text-primary); font-family: inherit; font-size: var(--ula-size-sm); font-weight: var(--ula-weight-medium);
            cursor: pointer; width: 100%; text-align: start;
        }
        .editor-dropdown-item:hover { background: var(--ula-surface-hover); }
        .editor-dropdown-item.active { background: var(--ula-surface-accent-soft); color: var(--ula-accent-default); }

        .act-btn {
            display: flex; align-items: center; gap: var(--ula-space-2);
            height: 36px; padding: 0 var(--ula-space-4);
            border-radius: var(--ula-radius-sm);
            font-family: inherit; font-size: var(--ula-size-sm); font-weight: var(--ula-weight-semibold);
            cursor: pointer; border: var(--ula-border-width-hairline) solid transparent; text-decoration: none;
        }
        .act-btn-emerald { background: var(--ula-accent-default); color: var(--ula-accent-fg); }
        .act-btn-emerald:hover { background: var(--ula-accent-hover); }
        .act-btn-secondary { background: var(--ula-surface-card); border-color: var(--ula-border-default); color: var(--ula-text-primary); }
        .act-btn-secondary:hover { background: var(--ula-surface-hover); border-color: var(--ula-border-strong); }

        /* ── Workspace: canvas + catalog drawer ── */
        .editor-workspace { flex: 1; min-height: 0; display: flex; position: relative; overflow: hidden; }
        .canvas-viewport {
            flex: 1; height: 100%; position: relative; overflow: hidden; cursor: default;
            background-color: var(--ula-surface-page-alt);
        }
        #editor-canvas { display: block; width: 100%; height: 100%; }

        /* Floating zoom / grid controls */
        .viewport-controls {
            position: absolute;
            bottom: var(--ula-space-5);
            inset-inline-start: var(--ula-space-5);
            display: flex; align-items: center; gap: var(--ula-space-1);
            padding: var(--ula-space-1);
            background: var(--ula-surface-raised);
            border: var(--ula-border-width-hairline) solid var(--ula-border-subtle);
            border-radius: var(--ula-radius-md);
            box-shadow: var(--ula-shadow-md);
            z-index: 10;
        }
        .view-btn {
            min-width: 36px; height: 36px; padding: 0 var(--ula-space-2);
            display: flex; align-items: center; justify-content: center; gap: var(--ula-space-1);
            background: transparent; border: 0; border-radius: var(--ula-radius-sm);
            color: var(--ula-text-secondary);
            font-family: inherit; font-size: var(--ula-size-xs); font-weight: var(--ula-weight-semibold);
            cursor: pointer;
            transition: background-color var(--ula-duration-fast) var(--ula-ease-out), color var(--ula-duration-fast) var(--ula-ease-out);
        }
        .view-btn .material-symbols-rounded { font-size: 20px; }
        .view-btn:hover { background: var(--ula-surface-hover); color: var(--ula-text-primary); }
        .view-btn.ed-mono { font-family: var(--ula-font-mono); direction: ltr; unicode-bidi: isolate; color: var(--ula-text-primary); }
        .ed-zoom-sep { width: 1px; height: 22px; background: var(--ula-border-subtle); margin: 0 var(--ula-space-1); }

        /* Selected-object quick actions */
        .floating-item-actions {
            position: absolute;
            transform: translate(-50%, -100%);
            margin-top: -12px;
            background: var(--ula-surface-raised);
            border: var(--ula-border-width-hairline) solid var(--ula-border-subtle);
            border-radius: var(--ula-radius-sm);
            padding: var(--ula-space-1);
            display: none; align-items: center; gap: var(--ula-space-1);
            z-index: 50;
            box-shadow: var(--ula-shadow-md);
        }
        .float-act-btn {
            height: 30px; padding: 0 var(--ula-space-3);
            display: inline-flex; align-items: center; gap: var(--ula-space-1);
            background: transparent; border: 0; border-radius: var(--ula-radius-xs);
            color: var(--ula-text-primary); font-family: inherit; font-size: var(--ula-size-xs); font-weight: var(--ula-weight-semibold);
            cursor: pointer;
        }
        .float-act-btn:hover { background: var(--ula-surface-hover); }
        .float-act-btn.ed-danger { color: var(--ula-text-danger); }
        .float-act-btn.ed-danger:hover { background: var(--ula-tone-terracotta-bg); }

        /* ── Catalog / inspector drawer ── */
        .customizer-drawer {
            width: 380px;
            height: 100%;
            background: var(--ula-surface-card);
            border-inline-start: var(--ula-border-width-hairline) solid var(--ula-border-subtle);
            display: flex; flex-direction: column;
            z-index: 20;
            transition: transform var(--ula-duration-base) var(--ula-ease-out), margin var(--ula-duration-base) var(--ula-ease-out);
        }
        .customizer-drawer.collapsed { transform: translateX(100%); margin-inline-end: -380px; }
        [dir="rtl"] .customizer-drawer.collapsed { transform: translateX(-100%); margin-inline-end: -380px; }

        .drawer-header {
            padding: var(--ula-space-4) var(--ula-space-5);
            display: flex; align-items: center; justify-content: space-between;
        }
        .drawer-title { font-size: var(--ula-size-body); font-weight: var(--ula-weight-semibold); color: var(--ula-text-primary); display: flex; align-items: center; gap: var(--ula-space-2); }
        .drawer-title .material-symbols-rounded { font-size: 20px; color: var(--ula-highlight-default); }
        .ed-drawer-close {
            width: 36px; height: 36px; display: inline-flex; align-items: center; justify-content: center;
            background: transparent; border: 0; border-radius: var(--ula-radius-sm);
            color: var(--ula-text-muted); cursor: pointer;
        }
        .ed-drawer-close:hover { background: var(--ula-surface-hover); color: var(--ula-text-primary); }

        .drawer-tabs {
            display: flex; gap: 2px;
            margin: 0 var(--ula-space-5) var(--ula-space-4);
            padding: 3px;
            background: var(--ula-surface-page-alt);
            border-radius: var(--ula-radius-md);
        }
        .drawer-tab {
            flex: 1;
            min-height: 36px;
            padding: var(--ula-space-1) var(--ula-space-2);
            display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 1px;
            border-radius: var(--ula-radius-sm);
            font-size: var(--ula-size-xs); font-weight: var(--ula-weight-semibold);
            color: var(--ula-text-secondary);
            text-align: center; cursor: pointer;
            transition: background-color var(--ula-duration-fast) var(--ula-ease-out), color var(--ula-duration-fast) var(--ula-ease-out);
        }
        .drawer-tab:hover { color: var(--ula-text-primary); }
        .drawer-tab.active { background: var(--ula-accent-default); color: var(--ula-accent-fg); box-shadow: var(--ula-shadow-xs); }
        .drawer-tab .ed-tab-sub { font-size: var(--ula-size-label); font-weight: var(--ula-weight-medium); opacity: 0.8; }

        .drawer-body {
            flex: 1; overflow-y: auto;
            padding: 0 var(--ula-space-5) var(--ula-space-6);
            display: flex; flex-direction: column; gap: var(--ula-space-3);
            scrollbar-width: thin;
            scrollbar-color: var(--ula-border-strong) transparent;
        }

        .search-box-wrapper { position: relative; display: flex; align-items: center; }
        .search-box-wrapper::before {
            content: 'search';
            font-family: 'Material Symbols Rounded';
            position: absolute; inset-inline-start: 12px;
            font-size: 20px; color: var(--ula-text-muted); pointer-events: none;
        }
        .search-box {
            width: 100%; height: 44px;
            padding-inline: 40px 36px;
            background: var(--ula-surface-page);
            border: var(--ula-border-width-hairline) solid var(--ula-border-default);
            border-radius: var(--ula-radius-md);
            color: var(--ula-text-primary);
            font-family: inherit; font-size: var(--ula-size-sm);
            outline: none;
            transition: border-color var(--ula-duration-fast) var(--ula-ease-out), box-shadow var(--ula-duration-fast) var(--ula-ease-out);
        }
        .search-box::placeholder { color: var(--ula-text-muted); }
        .search-box:focus { border-color: var(--ula-border-focus); box-shadow: var(--ula-focus-ring); }
        .search-clear-btn {
            position: absolute; inset-inline-end: 8px;
            background: none; border: 0; color: var(--ula-text-muted); cursor: pointer;
            display: none; padding: var(--ula-space-1);
        }
        .search-clear-btn:hover { color: var(--ula-text-primary); }

        .ed-catalog-meta { display: flex; justify-content: space-between; align-items: center; font-size: var(--ula-size-xs); color: var(--ula-text-secondary); }
        .ed-catalog-meta strong { font-family: var(--ula-font-mono); font-weight: var(--ula-weight-medium); color: var(--ula-text-primary); direction: ltr; unicode-bidi: isolate; }
        .ed-link-btn { background: none; border: 0; color: var(--ula-accent-default); font-family: inherit; font-size: var(--ula-size-xs); font-weight: var(--ula-weight-semibold); cursor: pointer; }
        .ed-link-btn:hover { text-decoration: underline; }

        .category-filter-bar {
            display: flex; gap: var(--ula-space-2);
            overflow-x: auto;
            padding: 2px 0 var(--ula-space-2);
            scrollbar-width: thin;
            scrollbar-color: var(--ula-border-strong) transparent;
        }
        .cat-pill {
            display: inline-flex; align-items: center; gap: var(--ula-space-1);
            height: 32px; padding: 0 var(--ula-space-3);
            background: var(--ula-surface-card);
            border: var(--ula-border-width-hairline) solid var(--ula-border-default);
            border-radius: var(--ula-radius-pill);
            color: var(--ula-text-secondary);
            font-family: inherit; font-size: var(--ula-size-xs); font-weight: var(--ula-weight-semibold);
            white-space: nowrap; cursor: pointer; flex-shrink: 0;
            transition: background-color var(--ula-duration-fast) var(--ula-ease-out), border-color var(--ula-duration-fast) var(--ula-ease-out), color var(--ula-duration-fast) var(--ula-ease-out);
        }
        .cat-pill .material-symbols-rounded { font-size: 16px; }
        .cat-pill:hover { border-color: var(--ula-border-strong); color: var(--ula-text-primary); background: var(--ula-surface-hover); }
        .cat-pill.active { background: var(--ula-accent-default); border-color: var(--ula-accent-default); color: var(--ula-accent-fg); }
        .cat-pill-count {
            font-family: var(--ula-font-mono); font-size: var(--ula-size-label);
            padding: 0 var(--ula-space-1); border-radius: var(--ula-radius-pill);
            background: var(--ula-surface-page-alt); color: var(--ula-text-secondary);
            direction: ltr; unicode-bidi: isolate;
        }
        .cat-pill.active .cat-pill-count { background: var(--ula-surface-accent-soft); color: var(--ula-accent-default); }

        .category-group {
            background: var(--ula-surface-page);
            border: var(--ula-border-width-hairline) solid var(--ula-border-subtle);
            border-radius: var(--ula-radius-md);
            overflow: hidden;
        }
        .category-title-bar {
            min-height: 44px; padding: 0 var(--ula-space-4);
            display: flex; align-items: center; justify-content: space-between; gap: var(--ula-space-2);
            font-size: var(--ula-size-sm); font-weight: var(--ula-weight-semibold);
            color: var(--ula-text-primary);
            cursor: pointer; background: transparent;
        }
        .category-title-bar:hover { background: var(--ula-surface-hover); }
        .cat-chevron { font-size: 18px; color: var(--ula-text-muted); transition: transform var(--ula-duration-fast) var(--ula-ease-out); }

        .furniture-grid {
            padding: 0 var(--ula-space-3) var(--ula-space-3);
            display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: var(--ula-space-3);
            max-height: 520px; overflow-y: auto;
        }

        .furn-card {
            background: var(--ula-surface-card);
            border: var(--ula-border-width-hairline) solid var(--ula-border-subtle);
            border-radius: var(--ula-radius-md);
            padding: var(--ula-space-2);
            display: flex; flex-direction: column; align-items: stretch;
            text-align: start; gap: var(--ula-space-2);
            cursor: pointer; position: relative; overflow: hidden;
            transition: border-color var(--ula-duration-fast) var(--ula-ease-out), box-shadow var(--ula-duration-fast) var(--ula-ease-out);
        }
        .furn-card:hover { border-color: var(--ula-border-strong); box-shadow: var(--ula-shadow-sm); }
        .furn-card.active, .furn-card.selected { border: 1.5px solid var(--ula-accent-default); box-shadow: var(--ula-shadow-sm); }

        .furn-card-top-badges {
            display: flex; justify-content: space-between; align-items: center;
            font-size: var(--ula-size-label); font-weight: var(--ula-weight-semibold);
        }
        .furn-dim-badge {
            background: var(--ula-surface-page-alt); color: var(--ula-text-secondary);
            padding: 0 var(--ula-space-1); border-radius: var(--ula-radius-xs);
            font-family: var(--ula-font-mono); direction: ltr; unicode-bidi: isolate;
        }
        .furn-type-badge {
            display: inline-flex; align-items: center; gap: 2px;
            background: var(--ula-tone-palm-bg); color: var(--ula-tone-palm-fg);
            padding: 0 var(--ula-space-1); border-radius: var(--ula-radius-xs);
        }

        .furn-icon {
            width: 100%; aspect-ratio: 16 / 10; height: auto;
            display: flex; align-items: center; justify-content: center;
            border-radius: var(--ula-radius-sm);
            background: var(--ula-surface-page-alt);
            overflow: hidden; position: relative; padding: var(--ula-space-2);
            color: var(--ula-icon-accent);
        }
        .furn-icon .material-symbols-rounded { font-size: 28px; }
        .furn-icon img { max-width: 100%; max-height: 100%; object-fit: contain; transition: transform var(--ula-duration-fast) var(--ula-ease-out); }
        .furn-card:hover .furn-icon img { transform: scale(1.06); }
        .furn-label {
            font-size: var(--ula-size-sm); font-weight: var(--ula-weight-semibold);
            color: var(--ula-text-primary); line-height: 1.3;
            overflow: hidden; text-overflow: ellipsis; white-space: nowrap;
        }

        /* ── Inspector ── */
        .prop-section {
            background: var(--ula-surface-page);
            border: var(--ula-border-width-hairline) solid var(--ula-border-subtle);
            border-radius: var(--ula-radius-md);
            padding: var(--ula-space-4);
            display: flex; flex-direction: column; gap: var(--ula-space-3);
        }
        .prop-label { font-size: var(--ula-size-xs); font-weight: var(--ula-weight-semibold); color: var(--ula-text-secondary); }
        .prop-input {
            width: 100%; height: 40px; padding: 0 var(--ula-space-3);
            background: var(--ula-surface-card);
            border: var(--ula-border-width-hairline) solid var(--ula-border-default);
            border-radius: var(--ula-radius-sm);
            color: var(--ula-text-primary); font-family: inherit; font-size: var(--ula-size-sm);
            outline: none;
        }
        textarea.prop-input { height: auto; padding: var(--ula-space-2) var(--ula-space-3); }
        .prop-input:focus { border-color: var(--ula-border-focus); box-shadow: var(--ula-focus-ring); }
        .ed-box {
            display: flex; flex-direction: column; gap: var(--ula-space-2);
            padding: var(--ula-space-3); border-radius: var(--ula-radius-sm);
            background: var(--ula-surface-accent-soft);
        }
        .ed-box--gold { background: var(--ula-tone-gold-bg); }
        .ed-box--stone { background: var(--ula-tone-stone-bg); }
        .ed-box--terracotta { background: var(--ula-tone-terracotta-bg); }
        .ed-box-title { font-size: var(--ula-size-sm); font-weight: var(--ula-weight-semibold); color: var(--ula-text-primary); display: inline-flex; align-items: center; gap: var(--ula-space-1); }

        .rotation-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: var(--ula-space-2); }
        .rot-btn {
            height: 34px; text-align: center;
            background: var(--ula-surface-card);
            border: var(--ula-border-width-hairline) solid var(--ula-border-default);
            border-radius: var(--ula-radius-sm);
            color: var(--ula-text-primary); font-family: var(--ula-font-mono); font-size: var(--ula-size-xs); font-weight: var(--ula-weight-medium);
            cursor: pointer;
        }
        .rot-btn:hover { background: var(--ula-surface-hover); }
        .rot-btn.active { background: var(--ula-accent-default); border-color: var(--ula-accent-default); color: var(--ula-accent-fg); }

        #drawer-view-floors .tool-btn { height: auto; min-height: 60px; padding: var(--ula-space-2) var(--ula-space-1); line-height: 1.3; }
        .ed-floor-actions { display: grid; grid-template-columns: repeat(3, 1fr); gap: var(--ula-space-2); padding: var(--ula-space-2); background: var(--ula-surface-page-alt); border-radius: var(--ula-radius-md); }


        @media (max-width: 1180px) {
            .nx-editor-screen .nx-toolbar-btn.ed-collapsible span:not(.material-symbols-rounded) { display: none; }
            .ed-brand-text { display: none; }
        }

        /* ── Door picker (room inspector) ── */
        .ed-door-picker {
            direction: ltr;
            display: grid;
            grid-template-columns: 36px 1fr 36px;
            grid-template-rows: 36px 88px 36px;
            grid-template-areas: ". top ." "left room right" ". bottom .";
            gap: var(--ula-space-1);
            margin-top: var(--ula-space-2);
            padding: var(--ula-space-2);
            background: var(--ula-surface-page-alt);
            border-radius: var(--ula-radius-md);
        }
        .ed-door-btn {
            display: inline-flex; align-items: center; justify-content: center;
            border: var(--ula-border-width-hairline) solid var(--ula-border-default);
            border-radius: var(--ula-radius-sm);
            background: var(--ula-surface-card);
            color: var(--ula-text-secondary);
            cursor: pointer;
            transition: background-color var(--ula-duration-fast) var(--ula-ease-out), color var(--ula-duration-fast) var(--ula-ease-out), border-color var(--ula-duration-fast) var(--ula-ease-out);
        }
        .ed-door-btn .material-symbols-rounded { font-size: 20px; }
        .ed-door-btn:hover { background: var(--ula-surface-hover); color: var(--ula-text-primary); border-color: var(--ula-border-strong); }
        .ed-door-btn:focus-visible { outline: none; box-shadow: var(--ula-focus-ring); }
        .ed-door-btn.active { background: var(--ula-accent-default); border-color: var(--ula-accent-default); color: var(--ula-accent-fg); }
        .ed-door-btn.blocked { opacity: 0.35; cursor: not-allowed; background: var(--ula-surface-page-alt); }
        .ed-door-btn.blocked:hover { background: var(--ula-surface-page-alt); color: var(--ula-text-secondary); border-color: var(--ula-border-default); }
        .ed-door-caption.is-warning { color: var(--ula-text-danger); }
        .ed-door-btn--top { grid-area: top; justify-self: center; width: 56px; }
        .ed-door-btn--bottom { grid-area: bottom; justify-self: center; width: 56px; }
        .ed-door-btn--left { grid-area: left; align-self: center; height: 56px; }
        .ed-door-btn--right { grid-area: right; align-self: center; height: 56px; }
        .ed-door-room {
            grid-area: room;
            position: relative;
            display: flex; align-items: center; justify-content: center;
            border: 2px solid var(--ula-border-strong);
            border-radius: var(--ula-radius-xs);
            background: var(--ula-surface-card);
        }
        .ed-door-btn--auto { width: 40px; height: 40px; border-style: dashed; }
        /* The door itself, placed on the chosen wall at the offset from the slider below. */
        .ed-door-mark {
            position: absolute;
            display: none;
            background: var(--ula-accent-default);
            border-radius: var(--ula-radius-pill);
            box-shadow: 0 0 0 2px var(--ula-surface-card);
        }
        .ed-door-mark[data-side="top"], .ed-door-mark[data-side="bottom"] { display: block; width: 24px; height: 5px; transform: translateX(-50%); }
        .ed-door-mark[data-side="left"], .ed-door-mark[data-side="right"] { display: block; width: 5px; height: 24px; transform: translateY(-50%); }
        .ed-door-mark[data-side="top"] { top: -4px; }
        .ed-door-mark[data-side="bottom"] { bottom: -4px; }
        .ed-door-mark[data-side="left"] { left: -4px; }
        .ed-door-mark[data-side="right"] { right: -4px; }
        .ed-door-caption { margin-top: var(--ula-space-2); font-size: var(--ula-size-xs); font-weight: var(--ula-weight-semibold); color: var(--ula-text-secondary); text-align: center; }

        /* ── Notification toast ── */
        .toast-bubble {
            position: fixed;
            inset-inline: 0;
            bottom: var(--ula-space-6);
            margin-inline: auto;
            width: max-content;
            min-width: 260px;
            max-width: min(440px, calc(100vw - 32px));
            display: none;
            align-items: center;
            gap: var(--ula-space-3);
            padding: var(--ula-space-3) var(--ula-space-4);
            padding-inline-end: var(--ula-space-5);
            background: var(--ula-surface-raised);
            border: var(--ula-border-width-hairline) solid var(--ula-border-subtle);
            border-radius: var(--ula-radius-md);
            box-shadow: var(--ula-shadow-lg);
            color: var(--ula-text-primary);
            font-size: var(--ula-size-sm);
            font-weight: var(--ula-weight-medium);
            line-height: 1.5;
            z-index: 100000;
            transform: none;
        }
        .toast-bubble.show { display: flex; animation: edToastIn var(--ula-duration-base) var(--ula-ease-out); }
        .toast-icon {
            width: 32px; height: 32px; flex-shrink: 0;
            display: inline-flex; align-items: center; justify-content: center;
            border-radius: var(--ula-radius-sm);
        }
        .toast-icon .material-symbols-rounded { font-size: 20px; }
        .toast-bubble[data-tone="ok"] .toast-icon { background: var(--ula-tone-palm-bg); color: var(--ula-tone-palm-fg); }
        .toast-bubble[data-tone="error"] .toast-icon { background: var(--ula-tone-terracotta-bg); color: var(--ula-tone-terracotta-fg); }
        .toast-bubble[data-tone="busy"] .toast-icon { background: var(--ula-tone-stone-bg); color: var(--ula-tone-stone-fg); }
        .toast-bubble[data-tone="busy"] .toast-icon .material-symbols-rounded { animation: edSpin 1s linear infinite; }
        .toast-bubble[data-tone="info"] .toast-icon { background: var(--ula-tone-gold-bg); color: var(--ula-tone-gold-fg); }
        .toast-text { min-width: 0; overflow-wrap: anywhere; unicode-bidi: plaintext; }
        @keyframes edToastIn { from { opacity: 0; transform: translateY(8px); } to { opacity: 1; transform: none; } }
        @keyframes edSpin { to { transform: rotate(360deg); } }
        @media (prefers-reduced-motion: reduce) {
            .toast-bubble.show, .toast-bubble[data-tone="busy"] .toast-icon .material-symbols-rounded { animation: none; }
        }
    </style>
</head>
<body>

    <div class="nx-office-viewport-container" style="display: flex; align-items: center; justify-content: center; height: 100vh; overflow: hidden; position: relative;">
        
        <div class="nx-editor-screen">
            
            <!-- ── Top toolbar (design-reference 33, light) ── -->
            <header class="nx-map-toolbar">

                <!-- 1. Start: menu, brand, branch switcher, version -->
                <div class="nx-toolbar-group">
                    <div style="position: relative;">
                        <button type="button" onclick="toggleEditorMainMenu(event)" class="nx-toolbar-btn ed-icon-only" title="{{ __('Menu') }}" aria-label="{{ __('Menu') }}">
                            <span class="material-symbols-rounded">menu</span>
                        </button>

                        <div id="editor-main-menu-dropdown" class="ed-menu">
                            <div class="ed-menu-head">
                                <span class="ed-brand-logo">
                                    @if(!empty($organization->logo_url))
                                        <img src="{{ $organization->logo_url }}" alt="{{ $organization->name }}">
                                    @else
                                        <span class="material-symbols-rounded">apartment</span>
                                    @endif
                                </span>
                                <div style="min-width: 0;">
                                    <strong>{{ $organization->name }}</strong>
                                    <span>{{ __('Floor Map Designer') }}</span>
                                </div>
                            </div>

                            <a href="{{ route('dashboard') }}" class="more-menu-item">
                                <span class="material-symbols-rounded">dashboard</span>
                                <span>{{ __('Dashboard') }}</span>
                            </a>
                            <a href="{{ route('office', ['office' => $floor->id]) }}" class="more-menu-item ed-accent">
                                <span class="material-symbols-rounded">meeting_room</span>
                                <span>{{ __('Enter Live Office') }}</span>
                            </a>

                            @if(session('superadmin_impersonator_id'))
                            <form method="POST" action="{{ route('impersonate.leave') }}" style="margin: 0;">
                                @csrf
                                <button type="submit" class="more-menu-item ed-accent">
                                    <span class="material-symbols-rounded">shield</span>
                                    <span>{{ __('Return to Super Admin') }}</span>
                                </button>
                            </form>
                            @endif

                            <div class="ed-menu-divider"></div>

                            <button type="button" onclick="toggleAppTheme(); closeEditorMainMenu();" class="more-menu-item">
                                <span class="material-symbols-rounded">contrast</span>
                                <span>{{ __('Toggle Theme') }}</span>
                            </button>
                            @if(app()->getLocale() === 'ar')
                                <a href="{{ route('lang.switch', 'en') }}" class="more-menu-item">
                                    <span class="material-symbols-rounded">language</span>
                                    <span>English (EN)</span>
                                </a>
                            @else
                                <a href="{{ route('lang.switch', 'ar') }}" class="more-menu-item">
                                    <span class="material-symbols-rounded">language</span>
                                    <span>العربية (AR)</span>
                                </a>
                            @endif
                        </div>
                    </div>

                    <div class="nx-brand-capsule" onclick="toggleEditorMainMenu(event)" style="cursor: pointer;" title="{{ __('Click to open menu') }}">
                        <span class="ed-brand-logo">
                            @if(!empty($organization->logo_url))
                                <img src="{{ $organization->logo_url }}" alt="{{ $organization->name }}">
                            @else
                                <span class="material-symbols-rounded">apartment</span>
                            @endif
                        </span>
                        <span class="ed-brand-text">
                            <strong>{{ __('Floor Map Designer') }}</strong>
                            <span>{{ $organization->name }}</span>
                        </span>
                    </div>

                    <div style="position: relative;">
                        <button type="button" onclick="toggleBranchDropdown(event)" class="nx-toolbar-btn ed-branch" title="{{ __('Switch Office Branch') }}">
                            <span class="material-symbols-rounded">domain</span>
                            <span style="max-width: 160px; overflow: hidden; text-overflow: ellipsis;">{{ $floor->name }}</span>
                            <span class="material-symbols-rounded" style="font-size: 18px; color: var(--ula-text-muted);">expand_more</span>
                        </button>
                        <div id="branch-select-dropdown" class="ed-menu" style="min-width: 250px;">
                            <div class="ed-menu-label">
                                <span class="material-symbols-rounded" style="font-size: 16px;">apartment</span> {{ __('Select Office Branch') }}
                            </div>
                            @foreach($floors as $f)
                            <a href="{{ route('editor', ['office' => $f->id]) }}" class="ed-menu-item {{ $f->id === $floor->id ? 'active' : '' }}">
                                <span style="display: flex; align-items: center; gap: 8px; min-width: 0;">
                                    <span class="material-symbols-rounded" style="font-size: 18px;">apartment</span>
                                    <span>{{ $f->name }}</span>
                                </span>
                                @if($f->id === $floor->id)
                                    <span class="ed-menu-item-meta">{{ __('Editing') }}</span>
                                @endif
                            </a>
                            @endforeach
                        </div>
                    </div>

                    <span class="nx-presence-capsule" id="header-version-badge" title="{{ __('Map version and status') }}">
                        v{{ $map->version }} · {{ ucfirst($map->status) }}
                    </span>
                </div>

                <!-- 2. Center: tools + selection actions -->
                <div class="nx-toolbar-group">
                    <div class="segmented-tool-pill">
                        <button class="tool-btn active" id="tool-select" onclick="setTool('select')" title="{{ __('Select & Move Objects (V)') }}">
                            <span class="material-symbols-rounded">near_me</span>
                            <span>{{ __('Select') }}</span>
                        </button>
                        <button class="tool-btn" id="tool-room" onclick="setTool('room')" title="{{ __('Draw Meeting / Private Rooms (R)') }}">
                            <span class="material-symbols-rounded">crop_square</span>
                            <span>{{ __('Room') }}</span>
                        </button>
                        <button class="tool-btn" id="tool-object" onclick="setTool('object')" title="{{ __('Place Furniture & Decor (F)') }}">
                            <span class="material-symbols-rounded">chair</span>
                            <span>{{ __('Furniture') }}</span>
                        </button>
                    </div>

                    <span class="ed-toolbar-sep" aria-hidden="true"></span>

                    <button class="nx-toolbar-btn ed-icon-only" onclick="rotateSelectedItem(90)" title="{{ __('Rotate 90° (R)') }}" aria-label="{{ __('Rotate 90° (R)') }}">
                        <span class="material-symbols-rounded">rotate_right</span>
                    </button>
                    <button class="nx-toolbar-btn ed-icon-only" onclick="duplicateSelectedItem()" title="{{ __('Clone / Duplicate') }}" aria-label="{{ __('Clone / Duplicate') }}">
                        <span class="material-symbols-rounded">content_copy</span>
                    </button>
                    <button class="nx-toolbar-btn ed-icon-only ed-danger" onclick="deleteSelectedItem()" title="{{ __('Delete Selected (Del)') }}" aria-label="{{ __('Delete Selected (Del)') }}">
                        <span class="material-symbols-rounded">delete</span>
                    </button>
                </div>

                <!-- 3. End: AI, save, publish, catalog -->
                <div class="nx-toolbar-group">
                    <input type="file" id="floorplan-file-input" accept="image/jpeg,image/png,image/webp,image/jpg" style="display:none;" onchange="handleFloorplanUpload(this)">

                    <button type="button" onclick="openAiGeneratorModal()" class="nx-toolbar-btn btn-accent ed-collapsible" title="{{ __('Generate 3D Isometric Office Floorplan & Rooms with AI') }}">
                        <span class="material-symbols-rounded">auto_awesome</span>
                        <span>{{ __('AI Generator') }}</span>
                    </button>

                    <span class="ed-toolbar-sep" aria-hidden="true"></span>

                    <button class="nx-toolbar-btn ed-collapsible" onclick="saveMapDraft()" title="{{ __('Save Map Draft') }}">
                        <span class="material-symbols-rounded">save</span>
                        <span>{{ __('Save') }}</span>
                    </button>
                    <button class="nx-toolbar-btn ed-primary ed-collapsible" onclick="publishMap()" title="{{ __('Publish Map to Live Office') }}">
                        <span class="material-symbols-rounded">publish</span>
                        <span>{{ __('Publish') }}</span>
                    </button>
                    <button class="nx-toolbar-btn ed-icon-only" onclick="toggleCustomizerDrawer()" title="{{ __('Toggle 3D Catalog & Inspector') }}" aria-label="{{ __('Toggle 3D Catalog & Inspector') }}">
                        <span class="material-symbols-rounded">dock_to_left</span>
                    </button>
                </div>
            </header>

            <!-- ── Main Workspace ── -->
            <div class="editor-workspace">

        <!-- Canvas Viewport -->
        <div class="canvas-viewport" id="canvas-container">
            <canvas id="editor-canvas"></canvas>

            <!-- Floating Selected Object Actions -->
            <div class="floating-item-actions" id="floating-actions">
                <button class="float-act-btn" onclick="rotateSelectedItem(90)"><span class="material-symbols-rounded" style="font-size: 16px;">rotate_right</span><span class="ed-mono-num">+90°</span></button>
                <button class="float-act-btn" onclick="duplicateSelectedItem()"><span class="material-symbols-rounded" style="font-size: 16px;">content_copy</span> {{ __('Clone') }}</button>
                <button class="float-act-btn ed-danger" onclick="deleteSelectedItem()" aria-label="{{ __('Delete Selected (Del)') }}"><span class="material-symbols-rounded" style="font-size: 16px;">delete</span></button>
            </div>

            <!-- View Navigation Controls -->
            <div class="viewport-controls">
                <button class="view-btn" onclick="zoomOut()" title="{{ __('Zoom Out') }}" aria-label="{{ __('Zoom Out') }}"><span class="material-symbols-rounded">remove</span></button>
                <button class="view-btn" onclick="zoomIn()" title="{{ __('Zoom In') }}" aria-label="{{ __('Zoom In') }}"><span class="material-symbols-rounded">add</span></button>
                <button class="view-btn" onclick="resetView()" title="{{ __('Reset View (100%)') }}" aria-label="{{ __('Reset View (100%)') }}"><span class="material-symbols-rounded">fit_screen</span></button>
                <span class="ed-zoom-sep" aria-hidden="true"></span>
                <button class="view-btn" onclick="toggleGrid()" title="{{ __('Toggle Grid') }}" aria-label="{{ __('Toggle Grid') }}"><span class="material-symbols-rounded">grid_4x4</span></button>
                <button class="view-btn ed-mono" id="btn-grid-snap" onclick="cycleGridSnap()" title="{{ __('Grid Snap Precision') }}"><span class="material-symbols-rounded" style="font-size: 18px;">target</span> 4px</button>
                <span class="ed-zoom-sep" aria-hidden="true"></span>
                <button class="view-btn" onclick="toggleCustomizerDrawer()" title="{{ __('Toggle Catalog Drawer') }}" aria-label="{{ __('Toggle Catalog Drawer') }}"><span class="material-symbols-rounded">chair</span></button>
            </div>
        </div>

        <!-- Right Customizer Drawer -->
        <aside class="customizer-drawer" id="customizer-drawer">
            <div class="drawer-header">
                <div class="drawer-title">
                    <span class="material-symbols-rounded" aria-hidden="true">auto_awesome</span>
                    <span>{{ __('Customize Floor & Furniture') }}</span>
                </div>
                <button type="button" class="ed-drawer-close" onclick="toggleCustomizerDrawer()" aria-label="{{ __('Close') }}"><span class="material-symbols-rounded">close</span></button>
            </div>

            <div class="drawer-tabs" role="tablist">
                <div class="drawer-tab active" id="tab-btn-furniture" onclick="switchDrawerTab('furniture')" role="tab" tabindex="0">
                    {{ __('3D Furniture') }}
                </div>
                <div class="drawer-tab" id="tab-btn-floors" onclick="switchDrawerTab('floors')" role="tab" tabindex="0">
                    {{ __('Floor Styles') }}
                </div>
                <div class="drawer-tab" id="tab-btn-inspector" onclick="switchDrawerTab('inspector')" role="tab" tabindex="0">
                    {{ __('Selected Item') }}
                </div>
                <div class="drawer-tab" id="tab-btn-rooms" onclick="switchDrawerTab('rooms')" role="tab" tabindex="0">
                    {{ __('Rooms') }}
                </div>
            </div>

            <div class="drawer-body">
                
                <!-- 1. FURNITURE CATALOG TAB -->
                <div id="drawer-view-furniture" style="display: flex; flex-direction: column; gap: 10px;">
                    
                    <!-- Search Box with Clear Button -->
                    <div class="search-box-wrapper">
                        <input type="text" id="furniture-search-input" class="search-box" placeholder="{{ __('Search 3D furniture, desks, rugs, plants...') }}" oninput="filterFurniture(this.value)">
                        <button type="button" id="search-clear-btn" class="search-clear-btn" onclick="clearFurnitureSearch()"><span class="material-symbols-rounded" style="font-size: 1em; vertical-align: text-bottom;">close</span></button>
                    </div>

                    <!-- Catalog Quick Stats & Expand/Collapse Toggle -->
                    <div style="display: flex; justify-content: space-between; align-items: center; font-size: 11px; color: var(--ula-text-muted); padding: 0 4px;">
                        @php
                            $totalCatalogCount = $furnitureCategories->sum(function($c) { return $c->items->count(); }) + 12;
                        @endphp
                        <span id="catalog-count-label" style="font-weight: 700; color: var(--ula-status-success);"><span class="material-symbols-rounded" style="font-size: 1em; vertical-align: text-bottom;">auto_awesome</span> {{ $totalCatalogCount }} {{ __('Items Available') }}</span>
                        <button type="button" onclick="expandAllCategories()" style="background:none; border:none; color: var(--ula-text-primary); font-size:11px; font-weight:800; cursor:pointer; text-decoration: underline;">
                            {{ __('Toggle All') }}
                        </button>
                    </div>

                    <!-- Modern Category Filter Horizontal Bar -->
                    <div class="category-filter-bar">
                        <button type="button" class="cat-pill active" onclick="filterByCategory('all')">
                            <span><span class="material-symbols-rounded" style="font-size: 1em; vertical-align: text-bottom;">star</span></span>
                            <span>{{ __('All') }}</span>
                            <span class="cat-pill-count">{{ $totalCatalogCount }}</span>
                        </button>
                        <button type="button" class="cat-pill" onclick="filterByCategory('blueprint')">
                            <span><span class="material-symbols-rounded" style="font-size: 1em; vertical-align: text-bottom;">architecture</span></span>
                            <span>{{ __('Blueprint') }}</span>
                            <span class="cat-pill-count">12</span>
                        </button>
                        @foreach($furnitureCategories as $cat)
                            @php
                                $shortName = trim(preg_replace('/\s*\(.*?\)\s*/', '', $cat->name));
                            @endphp
                            <button type="button" class="cat-pill" onclick="filterByCategory('{{ $cat->slug }}')" title="{{ $cat->name }}">
                                <span>{{ $cat->icon }}</span>
                                <span>{{ $shortName }}</span>
                                <span class="cat-pill-count">{{ $cat->items->count() }}</span>
                            </button>
                        @endforeach
                    </div>

                    <!-- 1. Blueprint Suite Assets -->
                    <div class="category-group" id="cat-blueprint">
                        <div class="category-title-bar" onclick="toggleCategoryGroup('cat-blueprint')">
                            <div style="display: flex; align-items: center; gap: 8px;">
                                <span style="font-size: 15px;"><span class="material-symbols-rounded" style="font-size: 1em; vertical-align: text-bottom;">architecture</span></span>
                                <span>{{ __('Isometric Blueprint Objects') }}</span>
                                <span class="cat-pill-count">12</span>
                            </div>
                            <span class="cat-chevron" id="chevron-cat-blueprint">▾</span>
                        </div>
                        <div class="furniture-grid">
                            <div class="furn-card" data-name="living plant wall botanical" onclick="selectFurnitureItem('living_wall', '#2D6A4F', null, 5, 2, true, 'none', null, 3, '{{ __('Living Plant Wall') }}')">
                                <div class="furn-card-top-badges">
                                    <span class="furn-dim-badge">5×2</span>
                                    <span class="furn-type-badge"><span class="material-symbols-rounded" style="font-size: 1em; vertical-align: text-bottom;">potted_plant</span> {{ __('Plant') }}</span>
                                </div>
                                <div class="furn-icon"><span style="font-size: 30px;"><span class="material-symbols-rounded" style="font-size: 1em; vertical-align: text-bottom;">potted_plant</span></span></div>
                                <div class="furn-label" title="{{ __('Living Plant Wall') }}">{{ __('Living Plant Wall') }}</div>
                            </div>

                            <div class="furn-card" data-name="oak boardroom table conference" onclick="selectFurnitureItem('conference_table', '#D8B589', null, 8, 3, true, 'sit', null, 2, '{{ __('Oak Boardroom Table') }}')">
                                <div class="furn-card-top-badges">
                                    <span class="furn-dim-badge">8×3</span>
                                    <span class="furn-type-badge"><span class="material-symbols-rounded" style="font-size: 1em; vertical-align: text-bottom;">handshake</span> {{ __('Table') }}</span>
                                </div>
                                <div class="furn-icon"><span style="font-size: 30px;"><span class="material-symbols-rounded" style="font-size: 1em; vertical-align: text-bottom;">handshake</span></span></div>
                                <div class="furn-label" title="{{ __('Oak Boardroom Table') }}">{{ __('Oak Boardroom Table') }}</div>
                            </div>

                            <div class="furn-card" data-name="white executive chair seating" onclick="selectFurnitureItem('chair_white', '#FFFFFF', null, 1, 1, false, 'sit', null, 1, '{{ __('White Executive Chair') }}')">
                                <div class="furn-card-top-badges">
                                    <span class="furn-dim-badge">1×1</span>
                                    <span class="furn-type-badge"><span class="material-symbols-rounded" style="font-size: 1em; vertical-align: text-bottom;">chair</span> {{ __('Sit') }}</span>
                                </div>
                                <div class="furn-icon"><span style="font-size: 30px;"><span class="material-symbols-rounded" style="font-size: 1em; vertical-align: text-bottom;">chair</span></span></div>
                                <div class="furn-label" title="{{ __('White Executive Chair') }}">{{ __('White Executive Chair') }}</div>
                            </div>

                            <div class="furn-card" data-name="focus pod desk workstation" onclick="selectFurnitureItem('pod_workstation', '#D8B589', null, 3, 2, true, 'sit', null, 2, '{{ __('Focus Pod Desk') }}')">
                                <div class="furn-card-top-badges">
                                    <span class="furn-dim-badge">3×2</span>
                                    <span class="furn-type-badge"><span class="material-symbols-rounded" style="font-size: 1em; vertical-align: text-bottom;">headphones</span> {{ __('Desk') }}</span>
                                </div>
                                <div class="furn-icon"><span style="font-size: 30px;"><span class="material-symbols-rounded" style="font-size: 1em; vertical-align: text-bottom;">headphones</span></span></div>
                                <div class="furn-label" title="{{ __('Focus Pod Desk') }}">{{ __('Focus Pod Desk') }}</div>
                            </div>

                            <div class="furn-card" data-name="wood feature wall partition" onclick="selectFurnitureItem('wood_panel_wall', '#C49A6C', null, 7, 1, true, 'none', null, 3, '{{ __('Wood Feature Wall') }}')">
                                <div class="furn-card-top-badges">
                                    <span class="furn-dim-badge">7×1</span>
                                    <span class="furn-type-badge"><span class="material-symbols-rounded" style="font-size: 1em; vertical-align: text-bottom;">forest</span> {{ __('Wall') }}</span>
                                </div>
                                <div class="furn-icon"><span style="font-size: 30px;"><span class="material-symbols-rounded" style="font-size: 1em; vertical-align: text-bottom;">forest</span></span></div>
                                <div class="furn-label" title="{{ __('Wood Feature Wall') }}">{{ __('Wood Feature Wall') }}</div>
                            </div>

                            <div class="furn-card" data-name="wooden staircase stairs" onclick="selectFurnitureItem('stairs_wood', '#C49A6C', null, 3, 4, true, 'none', null, 2, '{{ __('Wooden Staircase') }}')">
                                <div class="furn-card-top-badges">
                                    <span class="furn-dim-badge">3×4</span>
                                    <span class="furn-type-badge"><span class="material-symbols-rounded" style="font-size: 1em; vertical-align: text-bottom;">stairs</span> {{ __('Stairs') }}</span>
                                </div>
                                <div class="furn-icon"><span style="font-size: 30px;"><span class="material-symbols-rounded" style="font-size: 1em; vertical-align: text-bottom;">stairs</span></span></div>
                                <div class="furn-label" title="{{ __('Wooden Staircase') }}">{{ __('Wooden Staircase') }}</div>
                            </div>

                            <div class="furn-card" data-name="tech 3d workbench desk" onclick="selectFurnitureItem('tech_workbench', '#D8B589', null, 4, 2, true, 'none', null, 2, '{{ __('Tech 3D Workbench') }}')">
                                <div class="furn-card-top-badges">
                                    <span class="furn-dim-badge">4×2</span>
                                    <span class="furn-type-badge"><span class="material-symbols-rounded" style="font-size: 1em; vertical-align: text-bottom;">build</span> {{ __('Bench') }}</span>
                                </div>
                                <div class="furn-icon"><span style="font-size: 30px;"><span class="material-symbols-rounded" style="font-size: 1em; vertical-align: text-bottom;">build</span></span></div>
                                <div class="furn-label" title="{{ __('Tech 3D Workbench') }}">{{ __('Tech 3D Workbench') }}</div>
                            </div>

                            <div class="furn-card" data-name="reception counter desk" onclick="selectFurnitureItem('reception_counter', '#F4EFE6', null, 4, 2, true, 'drink', null, 2, '{{ __('Reception Desk') }}')">
                                <div class="furn-card-top-badges">
                                    <span class="furn-dim-badge">4×2</span>
                                    <span class="furn-type-badge"><span class="material-symbols-rounded" style="font-size: 1em; vertical-align: text-bottom;">room_service</span> {{ __('Lobby') }}</span>
                                </div>
                                <div class="furn-icon"><span style="font-size: 30px;"><span class="material-symbols-rounded" style="font-size: 1em; vertical-align: text-bottom;">room_service</span></span></div>
                                <div class="furn-label" title="{{ __('Reception Desk') }}">{{ __('Reception Desk') }}</div>
                            </div>

                            <div class="furn-card" data-name="cream 3 seater sofa lounge" onclick="selectFurnitureItem('sofa_cream', '#F4EFE6', null, 3, 2, true, 'sit', null, 1, '{{ __('Cream 3-Seater Sofa') }}')">
                                <div class="furn-card-top-badges">
                                    <span class="furn-dim-badge">3×2</span>
                                    <span class="furn-type-badge"><span class="material-symbols-rounded" style="font-size: 1em; vertical-align: text-bottom;">weekend</span> {{ __('Sofa') }}</span>
                                </div>
                                <div class="furn-icon"><span style="font-size: 30px;"><span class="material-symbols-rounded" style="font-size: 1em; vertical-align: text-bottom;">weekend</span></span></div>
                                <div class="furn-label" title="{{ __('Cream 3-Seater Sofa') }}">{{ __('Cream 3-Seater Sofa') }}</div>
                            </div>

                            <div class="furn-card" data-name="sage armchair single lounge" onclick="selectFurnitureItem('armchair_sage', '#8BA888', null, 2, 2, true, 'sit', null, 1, '{{ __('Sage Armchair') }}')">
                                <div class="furn-card-top-badges">
                                    <span class="furn-dim-badge">2×2</span>
                                    <span class="furn-type-badge"><span class="material-symbols-rounded" style="font-size: 1em; vertical-align: text-bottom;">weekend</span> {{ __('Chair') }}</span>
                                </div>
                                <div class="furn-icon"><span style="font-size: 30px;"><span class="material-symbols-rounded" style="font-size: 1em; vertical-align: text-bottom;">weekend</span></span></div>
                                <div class="furn-label" title="{{ __('Sage Armchair') }}">{{ __('Sage Armchair') }}</div>
                            </div>

                            <div class="furn-card" data-name="oak coffee table lounge" onclick="selectFurnitureItem('coffee_table_oak', '#D8B589', null, 2, 1, true, 'drink', null, 2, '{{ __('Oak Coffee Table') }}')">
                                <div class="furn-card-top-badges">
                                    <span class="furn-dim-badge">2×1</span>
                                    <span class="furn-type-badge"><span class="material-symbols-rounded" style="font-size: 1em; vertical-align: text-bottom;">coffee</span> {{ __('Table') }}</span>
                                </div>
                                <div class="furn-icon"><span style="font-size: 30px;"><span class="material-symbols-rounded" style="font-size: 1em; vertical-align: text-bottom;">coffee</span></span></div>
                                <div class="furn-label" title="{{ __('Oak Coffee Table') }}">{{ __('Oak Coffee Table') }}</div>
                            </div>

                            <div class="furn-card" data-name="strategy whiteboard presentation" onclick="selectFurnitureItem('whiteboard_strategy', '#FFFFFF', null, 4, 1, true, 'whiteboard', null, 3, '{{ __('Strategy Board') }}')">
                                <div class="furn-card-top-badges">
                                    <span class="furn-dim-badge">4×1</span>
                                    <span class="furn-type-badge"><span class="material-symbols-rounded" style="font-size: 1em; vertical-align: text-bottom;">content_copy</span> {{ __('Board') }}</span>
                                </div>
                                <div class="furn-icon"><span style="font-size: 30px;"><span class="material-symbols-rounded" style="font-size: 1em; vertical-align: text-bottom;">content_copy</span></span></div>
                                <div class="furn-label" title="{{ __('Strategy Board') }}">{{ __('Strategy Board') }}</div>
                            </div>
                        </div>
                    </div>

                    <!-- 2. Dynamic Catalog Categories (936+ Items) -->
                    @foreach($furnitureCategories as $cat)
                    @php
                        $cleanCatName = trim(preg_replace('/\s*\(.*?\)\s*/', '', $cat->name));
                        $itemsList = $cat->items;
                        if ($cat->slug === 'custom') {
                            $allowedCustomSlugs = ['branding', 'sticky_note', 'custom_link', 'custom_image'];
                            $itemsList = $itemsList->whereIn('slug', $allowedCustomSlugs);
                        }
                    @endphp
                    @if($itemsList->count() > 0)
                    <div class="category-group" id="cat-{{ $cat->slug }}">
                        <div class="category-title-bar" onclick="toggleCategoryGroup('cat-{{ $cat->slug }}')">
                            <div style="display: flex; align-items: center; gap: 8px;">
                                <span style="font-size: 15px;">{{ $cat->icon }}</span>
                                <span>{{ $cleanCatName }}</span>
                                <span class="cat-pill-count">{{ $itemsList->count() }}</span>
                            </div>
                            <span class="cat-chevron" id="chevron-cat-{{ $cat->slug }}">▾</span>
                        </div>
                        <div class="furniture-grid" style="display: none;">
                            @foreach($itemsList as $item)
                                @php
                                    $itemWidth = $item->width ?? 1;
                                    $itemHeight = $item->height ?? 1;
                                    $itemElev = $item->elevation ?? ($cat->slug === 'rugs' ? 0 : 1);
                                    $itemImg = $item->image_url;
                                    if ($item->slug === 'branding' && !empty($organization->logo_url)) {
                                        $itemImg = asset($organization->logo_url);
                                    }
                                    $itemTypeTag = ($cat->slug === 'rugs' || $itemElev === 0) ? '<span class="material-symbols-rounded" style="font-size: 1em; vertical-align: text-bottom;">texture</span> ' . __('Rug') : ($item->interaction_type !== 'none' ? '<span class="material-symbols-rounded" style="font-size: 1em; vertical-align: text-bottom;">bolt</span> ' . ucfirst($item->interaction_type) : "{$itemWidth}×{$itemHeight}");
                                    if ($item->slug === 'branding') $itemTypeTag = '<span class="material-symbols-rounded" style="font-size: 1em; vertical-align: text-bottom;">apartment</span> ' . __('Logo');
                                    if ($item->slug === 'sticky_note') $itemTypeTag = '<span class="material-symbols-rounded" style="font-size: 1em; vertical-align: text-bottom;">edit_note</span> ' . __('Note');
                                    if ($item->slug === 'custom_link') $itemTypeTag = '<span class="material-symbols-rounded" style="font-size: 1em; vertical-align: text-bottom;">link</span> ' . __('URL');
                                    if ($item->slug === 'custom_image') $itemTypeTag = '<span class="material-symbols-rounded" style="font-size: 1em; vertical-align: text-bottom;">image</span> ' . __('Image');
                                @endphp
                                <div class="furn-card" 
                                     data-name="{{ strtolower($item->name . ' ' . $cleanCatName . ' ' . $cat->slug) }}"
                                     onclick="selectFurnitureItem('{{ $item->slug }}', '{{ $item->colors[0] ?? '#3b82f6' }}', '{{ $itemImg }}', {{ $itemWidth }}, {{ $itemHeight }}, {{ $item->collision ? 'true' : 'false' }}, '{{ $item->interaction_type }}', {{ json_encode($item->interaction_config) }}, {{ $itemElev }}, '{{ addslashes($item->name) }}')">
                                    <div class="furn-card-top-badges">
                                        <span class="furn-dim-badge">{{ $itemWidth }}×{{ $itemHeight }}</span>
                                        <span class="furn-type-badge">{!! $itemTypeTag !!}</span>
                                    </div>
                                    <div class="furn-icon">
                                        @if($itemImg)
                                            <img src="{{ $itemImg }}" alt="{{ $item->name }}" loading="lazy" style="max-height: 48px; max-width: 48px; object-fit: contain;">
                                        @else
                                            <span style="font-size: 26px;">{{ $item->icon }}</span>
                                        @endif
                                    </div>
                                    <div class="furn-label" title="{{ $item->name }}">{{ $item->name }}</div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                    @endif
                    @endforeach
                </div>

                <!-- 2. SELECTED ITEM INSPECTOR TAB -->
                <div id="drawer-view-inspector" style="display: none; flex-direction: column; gap: 12px;">
                    <div class="prop-section" id="inspector-empty-msg">
                        <div style="font-size: 12px; color: var(--ula-text-muted); text-align: center; padding: 24px 0;">
                            <span class="material-symbols-rounded" style="font-size: 1em; vertical-align: text-bottom;">touch_app</span> {{ __('Click any object or room on the map to edit its properties, rotation, boundaries, and acoustic settings.') }}
                        </div>
                    </div>

                    <div id="inspector-content" style="display: none; flex-direction: column; gap: 12px;">
                        
                        <!-- Object Fields -->
                        <div id="inspector-object-fields" class="prop-section" style="display: none;">
                            <strong style="font-size: 13px; color: var(--ula-text-primary);"><span class="material-symbols-rounded" style="font-size: 1em; vertical-align: text-bottom;">chair</span> {{ __('Object Properties') }}</strong>
                            <div>
                                <label class="prop-label">{{ __('Name') }}</label>
                                <input type="text" class="prop-input" id="prop-name" oninput="updateSelectedProp('name', this.value)">
                            </div>
                            <div>
                                <label class="prop-label">{{ __('Rotation') }}</label>
                                <div class="rotation-grid">
                                    <div class="rot-btn" onclick="setRotation(0)">0°</div>
                                    <div class="rot-btn" onclick="setRotation(90)">90°</div>
                                    <div class="rot-btn" onclick="setRotation(180)">180°</div>
                                    <div class="rot-btn" onclick="setRotation(270)">270°</div>
                                </div>
                            </div>
                            <div>
                                <label class="prop-label">{{ __('Dimensions (Width × Height Tiles)') }}</label>
                                <div style="display: flex; gap: 8px;">
                                    <input type="number" class="prop-input" id="prop-width" placeholder="W" min="1" max="20" oninput="updateSelectedProp('width', parseInt(this.value) || 1)">
                                    <input type="number" class="prop-input" id="prop-height" placeholder="H" min="1" max="20" oninput="updateSelectedProp('height', parseInt(this.value) || 1)">
                                </div>
                            </div>
                            <div>
                                <label class="prop-label">{{ __('Layer & Elevation') }}</label>
                                <select class="prop-input" id="prop-elevation" onchange="updateSelectedProp('elevation', parseInt(this.value))">
                                    <option value="0">{{ __('Ground / Rug') }}</option>
                                    <option value="1">{{ __('Default Furniture') }}</option>
                                    <option value="2">{{ __('Desk / Table Surface') }}</option>
                                    <option value="3">{{ __('Tall Plant / Partition') }}</option>
                                    <option value="5">{{ __('Ceiling / Overhead') }}</option>
                                </select>
                            </div>
                            <div>
                                <label class="prop-label">{{ __('Interaction') }}</label>
                                <div id="prop-interaction-badge" style="font-size: 11px; font-weight: 700; color: var(--ula-text-primary); padding: 4px 8px; background: var(--ula-surface-accent-soft); border-radius: 6px; display: inline-block;">NONE</div>
                            </div>

                            <!-- <span class="material-symbols-rounded" style="font-size: 1em; vertical-align: text-bottom;">apartment</span> 1. Company Logo / Branding Inspector Box -->
                            <div id="inspector-branding-box" style="display: none; background: var(--ula-surface-accent-soft); border: 1px solid var(--ula-border-subtle); border-radius: 10px; padding: 12px; flex-direction: column; gap: 8px; margin-top: 4px;">
                                <div style="display: flex; align-items: center; justify-content: space-between;">
                                    <span style="font-size: 12px; font-weight: 800; color: var(--ula-status-success);"><span class="material-symbols-rounded" style="font-size: 1em; vertical-align: text-bottom;">apartment</span> {{ __('Company Logo') }}</span>
                                    <span class="furn-type-badge" style="background: var(--ula-tone-palm-bg); color: var(--ula-status-success);">Logo</span>
                                </div>
                                <div style="font-size: 11px; color: var(--ula-text-muted); line-height: 1.4;">
                                    {{ __('Displays your company logo on the workplace floor or reception.') }}
                                </div>
                                @if($organization->logo_url)
                                <button type="button" class="act-btn act-btn-emerald" onclick="applyOrgLogoToSelected()" style="justify-content: center; padding: 8px; font-size: 11px; width: 100%;">
                                    <span><span class="material-symbols-rounded" style="font-size: 1em; vertical-align: text-bottom;">apartment</span></span> <span>{{ __('Use Official Logo from Settings') }}</span>
                                </button>
                                @endif
                                <div>
                                    <label class="prop-label">{{ __('Custom Logo URL') }}</label>
                                    <input type="text" class="prop-input" id="prop-branding-url" placeholder="https://.../logo.png" oninput="updateSelectedLogoUrl(this.value)">
                                </div>
                                <div>
                                    <input type="file" id="branding-upload-input" accept="image/*" style="display: none;" onchange="uploadObjectImageDirectly(this, 'branding')">
                                    <button type="button" class="tool-btn" onclick="document.getElementById('branding-upload-input').click()" style="width: 100%; justify-content: center; padding: 7px; font-size: 11px;">
                                        <span class="material-symbols-rounded" style="font-size: 1em; vertical-align: text-bottom;">upload</span> {{ __('Upload Custom Logo File') }}
                                    </button>
                                </div>
                            </div>

                            <!-- <span class="material-symbols-rounded" style="font-size: 1em; vertical-align: text-bottom;">edit_note</span> 2. Sticky Note Inspector Box -->
                            <div id="inspector-stickynote-box" style="display: none; background: var(--ula-tone-gold-bg); border: 1px solid var(--ula-border-subtle); border-radius: 10px; padding: 12px; flex-direction: column; gap: 8px; margin-top: 4px;">
                                <div style="display: flex; align-items: center; justify-content: space-between;">
                                    <span style="font-size: 12px; font-weight: 800; color: var(--ula-tone-gold-fg);"><span class="material-symbols-rounded" style="font-size: 1em; vertical-align: text-bottom;">edit_note</span> {{ __('Sticky Note') }}</span>
                                    <span class="furn-type-badge" style="background: var(--ula-tone-gold-bg); color: var(--ula-tone-gold-fg);">Note</span>
                                </div>
                                <div>
                                    <label class="prop-label">{{ __('Note Text') }}</label>
                                    <textarea class="prop-input" id="prop-stickynote-text" rows="3" placeholder="{{ __('Write your note or announcement here...') }}" oninput="updateSelectedStickyText(this.value)" style="resize: vertical; min-height: 65px;"></textarea>
                                </div>
                                <div>
                                    <label class="prop-label">{{ __('Color Theme') }}</label>
                                    <div style="display: flex; gap: 6px;">
                                        <button type="button" class="rot-btn" style="flex: 1; background: rgba(245, 158, 11, 0.2); border-color: #F59E0B; color: #FCD34D;" onclick="setStickyColor('yellow')" title="Yellow">🟡</button>
                                        <button type="button" class="rot-btn" style="flex: 1; background: rgba(234, 88, 12, 0.2); border-color: #EA580C; color: #FDBA74;" onclick="setStickyColor('orange')" title="Orange">🟠</button>
                                        <button type="button" class="rot-btn" style="flex: 1; background: rgba(168, 85, 247, 0.2); border-color: #A855F7; color: #D8B4FE;" onclick="setStickyColor('purple')" title="Purple">🌸</button>
                                        <button type="button" class="rot-btn" style="flex: 1; background: rgba(16, 185, 129, 0.2); border-color: #10B981; color: #6EE7B7;" onclick="setStickyColor('green')" title="Green">🟢</button>
                                        <button type="button" class="rot-btn" style="flex: 1; background: rgba(59, 130, 246, 0.2); border-color: #3B82F6; color: #93C5FD;" onclick="setStickyColor('blue')" title="Blue">🔵</button>
                                    </div>
                                </div>
                            </div>

                            <!-- <span class="material-symbols-rounded" style="font-size: 1em; vertical-align: text-bottom;">link</span> 3. Custom Link Inspector Box -->
                            <div id="inspector-link-box" style="display: none; background: var(--ula-tone-stone-bg); border: 1px solid var(--ula-border-subtle); border-radius: 10px; padding: 12px; flex-direction: column; gap: 8px; margin-top: 4px;">
                                <div style="display: flex; align-items: center; justify-content: space-between;">
                                    <span style="font-size: 12px; font-weight: 800; color: var(--ula-accent-default);"><span class="material-symbols-rounded" style="font-size: 1em; vertical-align: text-bottom;">link</span> {{ __('Interactive Web Link') }}</span>
                                    <span class="furn-type-badge" style="background: var(--ula-tone-stone-bg); color: var(--ula-accent-default);">URL</span>
                                </div>
                                <div>
                                    <label class="prop-label">{{ __('Target URL') }}</label>
                                    <input type="url" class="prop-input" id="prop-link-url" placeholder="https://example.com/doc" oninput="updateSelectedLinkProp('url', this.value)">
                                </div>
                                <div>
                                    <label class="prop-label">{{ __('Link Title / Label') }}</label>
                                    <input type="text" class="prop-input" id="prop-link-title" placeholder="{{ __('e.g. Project Notion Board') }}" oninput="updateSelectedLinkProp('title', this.value)">
                                </div>
                                <div style="display: flex; align-items: center; justify-content: space-between; margin-top: 4px;">
                                    <span style="font-size: 11px; color: var(--ula-text-secondary);">{{ __('Open in New Browser Tab') }}</span>
                                    <input type="checkbox" id="prop-link-newtab" checked onchange="updateSelectedLinkProp('openInNewTab', this.checked)" style="accent-color: var(--ula-accent-default); cursor: pointer; width: 16px; height: 16px;">
                                </div>
                            </div>

                            <!-- <span class="material-symbols-rounded" style="font-size: 1em; vertical-align: text-bottom;">image</span> 4. Custom Image Inspector Box -->
                            <div id="inspector-customimage-box" style="display: none; background: var(--ula-tone-terracotta-bg); border: 1px solid var(--ula-border-subtle); border-radius: 10px; padding: 12px; flex-direction: column; gap: 8px; margin-top: 4px;">
                                <div style="display: flex; align-items: center; justify-content: space-between;">
                                    <span style="font-size: 12px; font-weight: 800; color: var(--ula-tone-terracotta-fg);"><span class="material-symbols-rounded" style="font-size: 1em; vertical-align: text-bottom;">image</span> {{ __('Custom Image / Banner') }}</span>
                                    <span class="furn-type-badge" style="background: var(--ula-tone-terracotta-bg); color: var(--ula-tone-terracotta-fg);">Image</span>
                                </div>
                                <div>
                                    <label class="prop-label">{{ __('Image URL') }}</label>
                                    <input type="url" class="prop-input" id="prop-customimage-url" placeholder="https://.../banner.png" oninput="updateSelectedCustomImageUrl(this.value)">
                                </div>
                                <div>
                                    <input type="file" id="customimage-upload-input" accept="image/*" style="display: none;" onchange="uploadObjectImageDirectly(this, 'custom_image')">
                                    <button type="button" class="tool-btn" onclick="document.getElementById('customimage-upload-input').click()" style="width: 100%; justify-content: center; padding: 7px; font-size: 11px;">
                                        <span class="material-symbols-rounded" style="font-size: 1em; vertical-align: text-bottom;">upload</span> {{ __('Upload Image File') }}
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Room Fields -->
                        <div id="inspector-room-fields" class="prop-section" style="display: none;">
                            <strong style="font-size: 13px; color: var(--ula-text-primary);"><span class="material-symbols-rounded" style="font-size: 1em; vertical-align: text-bottom;">apartment</span> {{ __('Room Properties & Audio') }}</strong>
                            <div>
                                <label class="prop-label">{{ __('Room Name') }}</label>
                                <input type="text" class="prop-input" id="prop-room-name" placeholder="{{ __('e.g. Conference Room A') }}" oninput="updateRoomProp('name', this.value)">
                            </div>
                            <div>
                                <label class="prop-label">{{ __('Room Type') }}</label>
                                <select class="prop-input" id="prop-room-type" onchange="updateRoomProp('type', this.value)">
                                    <option value="meeting">{{ __('Meeting Room') }}</option>
                                    <option value="private">{{ __('Private Office') }}</option>
                                    <option value="focus">{{ __('Focus Pod') }}</option>
                                    <option value="breakout">{{ __('Breakout Lounge') }}</option>
                                    <option value="reception">{{ __('Reception Lobby') }}</option>
                                </select>
                            </div>
                            
                            <!-- Acoustic Isolation Box -->
                            <div style="background: var(--ula-surface-accent-soft); border: 1px solid var(--ula-border-subtle); border-radius: 10px; padding: 12px; display: flex; flex-direction: column; gap: 8px;">
                                <div style="display: flex; align-items: center; justify-content: space-between;">
                                    <span style="font-size: 12px; font-weight: 800; color: var(--ula-status-success);"><span class="material-symbols-rounded" style="font-size: 1em; vertical-align: text-bottom;">mic</span> {{ __('Acoustic Isolation') }}</span>
                                    <input type="checkbox" id="prop-room-isolation" onchange="updateRoomProp('audio_isolation', this.checked)" style="width: 18px; height: 18px; accent-color: var(--ula-accent-default); cursor: pointer;">
                                </div>
                                <span style="font-size: 11px; color: var(--ula-text-muted);" id="prop-room-bounds-label"></span>
                            </div>

                            <div>
                                <label class="prop-label" id="door-picker-label">{{ __('Door Placement') }}</label>
                                {{-- Visual door picker: an arrow on each wall of a mini room. Map directions are physical
                                     (the canvas is never mirrored), so the diagram is laid out LTR in both languages. --}}
                                <input type="hidden" id="prop-room-door-side" value="auto">
                                <div class="ed-door-picker" role="radiogroup" aria-labelledby="door-picker-label">
                                    <button type="button" class="ed-door-btn ed-door-btn--top" data-side="top" role="radio" onclick="setDoorSide('top')" title="{{ __('Top Wall') }}" aria-label="{{ __('Top Wall') }}"><span class="material-symbols-rounded">arrow_upward</span></button>
                                    <button type="button" class="ed-door-btn ed-door-btn--left" data-side="left" role="radio" onclick="setDoorSide('left')" title="{{ __('Left Wall') }}" aria-label="{{ __('Left Wall') }}"><span class="material-symbols-rounded">arrow_back</span></button>
                                    <div class="ed-door-room">
                                        <span class="ed-door-mark" id="door-picker-mark" aria-hidden="true"></span>
                                        <button type="button" class="ed-door-btn ed-door-btn--auto" data-side="auto" role="radio" onclick="setDoorSide('auto')" title="{{ __('Auto Corridor') }}" aria-label="{{ __('Auto Corridor') }}"><span class="material-symbols-rounded">auto_mode</span></button>
                                    </div>
                                    <button type="button" class="ed-door-btn ed-door-btn--right" data-side="right" role="radio" onclick="setDoorSide('right')" title="{{ __('Right Wall') }}" aria-label="{{ __('Right Wall') }}"><span class="material-symbols-rounded">arrow_forward</span></button>
                                    <button type="button" class="ed-door-btn ed-door-btn--bottom" data-side="bottom" role="radio" onclick="setDoorSide('bottom')" title="{{ __('Bottom Wall') }}" aria-label="{{ __('Bottom Wall') }}"><span class="material-symbols-rounded">arrow_downward</span></button>
                                </div>
                                <div class="ed-door-caption" id="door-picker-caption">{{ __('Auto Corridor') }}</div>
                            </div>

                            <div>
                                <label class="prop-label">{{ __('Door Position on Wall') }}</label>
                                <div style="display: flex; align-items: center; gap: 8px;">
                                    <input type="range" class="prop-input" id="prop-room-door-offset" min="15" max="85" value="50" step="5" oninput="updateRoomProp('doorOffset', this.value / 100); document.getElementById('door-offset-val').textContent = this.value + '%'; syncDoorPicker();">
                                    <span id="door-offset-val" style="font-family: var(--ula-font-mono); font-size: var(--ula-size-xs); color: var(--ula-text-primary); min-width: 36px; direction: ltr; unicode-bidi: isolate;">50%</span>
                                </div>
                            </div>

                            <div>
                                <label class="prop-label">{{ __('Capacity') }}</label>
                                <input type="number" class="prop-input" id="prop-room-capacity" min="1" max="200" oninput="updateRoomProp('capacity', this.value)">
                            </div>

                            <button class="act-btn act-btn-emerald" onclick="saveSelectedRoom()" style="margin-top: 6px; justify-content: center;">
                                <span class="material-symbols-rounded" style="font-size: 1em; vertical-align: text-bottom;">save</span> {{ __('Save Room Settings') }}
                            </button>
                        </div>

                    </div>
                </div>

                <!-- 3. ROOMS DIRECTORY TAB -->
                <div id="drawer-view-rooms" style="display: none; flex-direction: column; gap: 10px;">
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <span style="font-size: 12px; font-weight: 800; color: var(--ula-text-muted);">{{ __('All Configured Rooms') }}</span>
                        <button class="tool-btn" onclick="setTool('room')"><span class="material-symbols-rounded" style="font-size: 1em; vertical-align: text-bottom;">add</span> {{ __('New Room') }}</button>
                    </div>
                    <div id="rooms-list-container" style="display: flex; flex-direction: column; gap: 8px;"></div>
                </div>

                <!-- 4. FLOORS & BACKGROUNDS TAB -->
                <div id="drawer-view-floors" style="display: none; flex-direction: column; gap: 12px;">
                    <!-- Quick Action Tools Bar (Moved from Burger Menu) -->
                    <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 6px; padding: 6px; background: var(--ula-surface-page-alt); border: 1px solid var(--ula-border-default); border-radius: 12px;">
                        <label class="tool-btn" style="cursor: pointer; display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 3px; padding: 6px 2px; font-size: 10px; text-align: center; margin: 0;" title="{{ __('Upload Custom Floorplan') }}">
                            <span class="material-symbols-rounded" style="font-size: 20px; color: var(--ula-highlight-default);">upload_file</span>
                            <span style="font-weight: 700;">{{ __('Upload') }}</span>
                            <span style="font-size: 9px; opacity: 0.8; font-family: 'IBM Plex Sans Arabic', sans-serif;">رفع مخصص</span>
                            <input type="file" accept="image/*" style="display:none;" onchange="handleCustomFloorUpload(this)">
                        </label>

                        <button type="button" class="tool-btn" onclick="deleteFloorplan()" style="display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 3px; padding: 6px 2px; font-size: 10px; text-align: center; color: var(--ula-status-danger); background: var(--ula-tone-terracotta-bg); border-color: var(--ula-border-danger);" title="{{ __('Reset to Default Floorplan') }}">
                            <span class="material-symbols-rounded" style="font-size: 20px; color: var(--ula-status-danger);">restart_alt</span>
                            <span style="font-weight: 700;">{{ __('Reset') }}</span>
                            <span style="font-size: 9px; opacity: 0.8; font-family: 'IBM Plex Sans Arabic', sans-serif;">استعادة</span>
                        </button>

                        <button type="button" class="tool-btn" onclick="clearWorkspace()" style="display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 3px; padding: 6px 2px; font-size: 10px; text-align: center; color: var(--ula-highlight-default); background: var(--ula-tone-gold-bg); border-color: var(--ula-border-subtle);" title="{{ __('Clear All Placed Furniture') }}">
                            <span class="material-symbols-rounded" style="font-size: 20px; color: var(--ula-highlight-default);">cleaning_services</span>
                            <span style="font-weight: 700;">{{ __('Clear') }}</span>
                            <span style="font-size: 9px; opacity: 0.8; font-family: 'IBM Plex Sans Arabic', sans-serif;">تفريغ الأثاث</span>
                        </button>
                    </div>

                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <span style="font-size: 11px; font-weight: 800; color: var(--ula-status-success);"><span class="material-symbols-rounded" style="font-size: 1em; vertical-align: text-bottom;">palette</span> {{ __('Floor Styles Library (1200×708)') }}</span>
                        <span style="font-size: 10px; color: var(--ula-text-muted); font-family: monospace;">18 Styles</span>
                    </div>

                    <div style="font-size: 11px; color: var(--ula-text-muted); line-height: 1.4;">
                        {{ __('اختر نمط الأرضية لتطبيقه فوراً كخلفية للمكتب بمقاس 1200×708 بكسل:') }}
                    </div>

                    <div id="floors-catalog-grid" style="display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); grid-auto-rows: max-content; gap: var(--ula-space-3); flex-shrink: 0;">
                        <!-- Injected via JavaScript -->
                    </div>

                    <div style="padding-top: 8px; border-top: 1px solid var(--ula-border-default); display: flex; justify-content: space-between; align-items: center;">
                        <button type="button" class="tool-btn" onclick="clearCurrentFloorBackground()" style="color: var(--ula-status-danger); border-color: var(--ula-border-danger); font-size: 11px; width: 100%; justify-content: center;">
                            <span class="material-symbols-rounded" style="font-size: 1em; vertical-align: text-bottom;">delete</span> {{ __('Remove Floor Background') }}
                        </button>
                    </div>
                </div>

            </div>
        </aside>
    </div> <!-- .editor-workspace -->
    </div> <!-- .nx-editor-screen -->
    </div> <!-- .nx-office-viewport-container -->

    <!-- Toast Notification -->
    <div id="toast-bubble" class="toast-bubble"></div>

    <!-- ── JavaScript Realtime Engine & Editor Pipeline ── -->
    <script nonce="{{ $cspNonce ?? '' }}">
        const MAP_DATA = @json($map);
        const MAP_ID = "{{ $map->id }}";
        const ORG_ID = "{{ $organization->id }}";
        const PLAN_MAX_ROOMS = {{ ($organization->plan && $organization->plan->room_limit > 0) ? $organization->plan->room_limit : 0 }};
        const CSRF_TOKEN = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        const COMPANY_LOGO_URL = @json($organization->logo_url ? asset($organization->logo_url) : '');
        const COMPANY_NAME = @json($organization->name);

        const canvas = document.getElementById('editor-canvas');
        const ctx = canvas.getContext('2d');
        const container = document.getElementById('canvas-container');

        let width = canvas.width = container.clientWidth;
        let height = canvas.height = container.clientHeight;

        const TILE_SIZE = 16;
        let MAP_WIDTH_PX = (MAP_DATA.layout_data && MAP_DATA.layout_data.background_width && Number(MAP_DATA.layout_data.background_width) >= 500)
            ? Number(MAP_DATA.layout_data.background_width)
            : 1200;
        let MAP_HEIGHT_PX = (MAP_DATA.layout_data && MAP_DATA.layout_data.background_height && Number(MAP_DATA.layout_data.background_height) >= 500)
            ? Number(MAP_DATA.layout_data.background_height)
            : 708;

        let zoomLevel = 1.0;
        let panOffset = { x: 0, y: 0 };
        let showGrid = true;

        let currentTool = 'select'; // select | room | object
        let currentRoomType = 'meeting';
        let currentRoomColor = '#4F9B5F';
        let currentObjectType = 'living_wall';
        let currentObjectColor = '#2D6A4F';
        let currentObjectCustom = null;

        let rooms = (MAP_DATA.rooms || []).map(r => ({
            id: r.id,
            name: r.name || 'Room',
            type: r.type || 'meeting',
            access_mode: r.access_mode || 'public',
            capacity: r.capacity || 10,
            color: r.color || '#4F9B5F',
            bounds: r.bounds || { x: 1, y: 1, width: 10, height: 8 },
            metadata: r.metadata || { audio_isolation: true }
        }));
        let objects = (MAP_DATA.objects || []).map(o => ({
            ...o,
            image_url: o.image_url || (o.interaction_config && o.interaction_config.image_url) || null,
            is_custom: typeof o.is_custom !== 'undefined' ? o.is_custom : (o.interaction_config && typeof o.interaction_config.is_custom !== 'undefined' ? o.interaction_config.is_custom : true),
            width: o.width || (o.size ? o.size.width : (o.interaction_config && o.interaction_config.width ? o.interaction_config.width : 1)),
            height: o.height || (o.size ? o.size.height : (o.interaction_config && o.interaction_config.height ? o.interaction_config.height : 1)),
            collision: typeof o.collision === 'boolean' ? o.collision : true
        }));

        let selectedItem = null;
        let isDragging = false;
        let isDrawing = false;
        let isPanning = false;
        let panStartX = 0;
        let panStartY = 0;
        let dragStartTileX = 0;
        let dragStartTileY = 0;
        let dragOrigX = 0;
        let dragOrigY = 0;
        let startX = 0;
        let startY = 0;
        let currentRect = null;
        let roomContainedObjects = [];

        function fitAndCenterView() {
            if (!canvas || !container) return;
            width = canvas.width = container.clientWidth;
            height = canvas.height = container.clientHeight;
            const scaleX = (width - 48) / MAP_WIDTH_PX;
            const scaleY = (height - 48) / MAP_HEIGHT_PX;
            // Scale dynamically to fill viewport comfortably
            zoomLevel = Math.max(0.15, Math.min(3.0, Math.min(scaleX, scaleY)));
            panOffset.x = Math.round((width - MAP_WIDTH_PX * zoomLevel) / 2);
            panOffset.y = Math.round((height - MAP_HEIGHT_PX * zoomLevel) / 2);
            if (typeof draw === 'function') draw();
        }

        // ── Resize Engine ──
        function resizeCanvas() {
            width = canvas.width = container.clientWidth;
            height = canvas.height = container.clientHeight;
            draw();
        }
        window.addEventListener('resize', resizeCanvas);

        // ── Background Blueprint Artwork ──
        const BLUEPRINT_IMAGE = new Image();
        let initialBgUrl = (MAP_DATA.layout_data && MAP_DATA.layout_data.background_image_url)
            ? MAP_DATA.layout_data.background_image_url
            : null;
        let blueprintLoaded = false;
        if (initialBgUrl) {
            BLUEPRINT_IMAGE.src = initialBgUrl + (initialBgUrl.includes('?') ? '&' : '?') + 'v=' + Date.now();
            BLUEPRINT_IMAGE.onload = () => {
                blueprintLoaded = true;
                if (BLUEPRINT_IMAGE.naturalWidth > 0 && BLUEPRINT_IMAGE.naturalHeight > 0) {
                    MAP_WIDTH_PX = BLUEPRINT_IMAGE.naturalWidth;
                    MAP_HEIGHT_PX = BLUEPRINT_IMAGE.naturalHeight;
                } else {
                    MAP_WIDTH_PX = 1200;
                    MAP_HEIGHT_PX = 708;
                }
                fitAndCenterView();
                draw();
            };
            BLUEPRINT_IMAGE.onerror = () => {
                blueprintLoaded = false;
                MAP_WIDTH_PX = 1200;
                MAP_HEIGHT_PX = 708;
                fitAndCenterView();
                draw();
            };
        } else {
            blueprintLoaded = false;
            MAP_WIDTH_PX = 1200;
            MAP_HEIGHT_PX = 708;
            setTimeout(fitAndCenterView, 50);
        }

        // ── Navigation & Tools ──
        function setTool(tool) {
            currentTool = tool;
            document.querySelectorAll('.tool-btn').forEach(btn => btn.classList.remove('active'));
            document.getElementById(`tool-${tool}`)?.classList.add('active');
            canvas.style.cursor = tool === 'select' ? 'default' : 'crosshair';
            if (tool !== 'select') hideFloatingActions();
        }

        function toggleCustomizerDrawer() {
            const drawer = document.getElementById('customizer-drawer');
            drawer.classList.toggle('collapsed');
            setTimeout(resizeCanvas, 320);
        }

        const FLOOR_CATALOG = [
            { id: 'floor_natural_oak', name_en: 'Natural Oak Parquet', name_ar: 'باركيه بلوط طبيعي دافئ', url: '/images/floors/floor_natural_oak.jpg', thumb: '/images/floors/thumb_floor_natural_oak.jpg' },
            { id: 'floor_dark_walnut', name_en: 'Dark Walnut Herringbone', name_ar: 'خشب جوز داكن متعرج', url: '/images/floors/floor_dark_walnut.jpg', thumb: '/images/floors/thumb_floor_dark_walnut.jpg' },
            { id: 'floor_light_birch', name_en: 'Scandinavian Light Birch', name_ar: 'خشب زان إسكندنافي فاتح', url: '/images/floors/floor_light_birch.jpg', thumb: '/images/floors/thumb_floor_light_birch.jpg' },
            { id: 'floor_carrara_marble', name_en: 'Carrara White Marble', name_ar: 'رخام كرارا أبيض فاخر', url: '/images/floors/floor_carrara_marble.jpg', thumb: '/images/floors/thumb_floor_carrara_marble.jpg' },
            { id: 'floor_nero_marquina', name_en: 'Nero Marquina Black Marble', name_ar: 'رخام أسود مذهب ملكي', url: '/images/floors/floor_nero_marquina.jpg', thumb: '/images/floors/thumb_floor_nero_marquina.jpg' },
            { id: 'floor_industrial_concrete', name_en: 'Polished Industrial Concrete', name_ar: 'خرسانة صناعية مصقولة', url: '/images/floors/floor_industrial_concrete.jpg', thumb: '/images/floors/thumb_floor_industrial_concrete.jpg' },
            { id: 'floor_slate_tile', name_en: 'Urban Slate Grey Stone', name_ar: 'بلاط حجري رمادي حضري', url: '/images/floors/floor_slate_tile.jpg', thumb: '/images/floors/thumb_floor_slate_tile.jpg' },
            { id: 'floor_terrazzo', name_en: 'Italian Venetian Terrazzo', name_ar: 'تيرازو إيطالي حديث مذهب', url: '/images/floors/floor_terrazzo.jpg', thumb: '/images/floors/thumb_floor_terrazzo.jpg' },
            { id: 'floor_chevron_timber', name_en: 'Modern Chevron Walnut', name_ar: 'أرضية خشب شيفرون مودرن', url: '/images/floors/floor_chevron_timber.jpg', thumb: '/images/floors/thumb_floor_chevron_timber.jpg' },
            { id: 'floor_executive_carpet', name_en: 'Executive Midnight Carpet', name_ar: 'سجاد مكتبي تنفيذي كحلي', url: '/images/floors/floor_executive_carpet.jpg', thumb: '/images/floors/thumb_floor_executive_carpet.jpg' },
            { id: 'floor_charcoal_carpet', name_en: 'Charcoal Commercial Weave', name_ar: 'موكيت رمادي فحمي مكتبي', url: '/images/floors/floor_charcoal_carpet.jpg', thumb: '/images/floors/thumb_floor_charcoal_carpet.jpg' },
            { id: 'floor_ceramic_beige', name_en: 'Warm Ceramic Beige Tiles', name_ar: 'بلاط سيراميك بيج دافئ', url: '/images/floors/floor_ceramic_beige.jpg', thumb: '/images/floors/thumb_floor_ceramic_beige.jpg' },
            { id: 'floor_geometric_porcelain', name_en: 'Geometric Porcelain Grid', name_ar: 'بورسلين هندسي حديث', url: '/images/floors/floor_geometric_porcelain.jpg', thumb: '/images/floors/thumb_floor_geometric_porcelain.jpg' },
            { id: 'floor_biophilic_garden', name_en: 'Biophilic Moss & Stone Garden', name_ar: 'أرضية عشبية وحجرية بيوفيلك', url: '/images/floors/floor_biophilic_garden.jpg', thumb: '/images/floors/thumb_floor_biophilic_garden.jpg' },
            { id: 'floor_japanese_tatami', name_en: 'Japanese Tatami & Bamboo', name_ar: 'خيزران وتاتامي ياباني', url: '/images/floors/floor_japanese_tatami.jpg', thumb: '/images/floors/thumb_floor_japanese_tatami.jpg' },
            { id: 'floor_emerald_epoxy', name_en: 'Emerald Gloss Epoxy', name_ar: 'إيبوكسي أخضر زمردي لامع', url: '/images/floors/floor_emerald_epoxy.jpg', thumb: '/images/floors/thumb_floor_emerald_epoxy.jpg' },
            { id: 'floor_open_office_blueprint', name_en: 'Open Plan Architectural Plan', name_ar: 'مخطط معماري مكتبي مفتوح', url: '/images/floors/floor_open_office_blueprint.jpg', thumb: '/images/floors/thumb_floor_open_office_blueprint.jpg' },
            { id: 'floor_executive_suite_blueprint', name_en: 'Executive Suite Architectural Plan', name_ar: 'مخطط جناح تنفيذي متكامل', url: '/images/floors/floor_executive_suite_blueprint.jpg', thumb: '/images/floors/thumb_floor_executive_suite_blueprint.jpg' },
        ];

        function renderFloorsCatalog() {
            const grid = document.getElementById('floors-catalog-grid');
            if (!grid) return;
            const currentBg = MAP_DATA.layout_data?.background_image_url || '';
            const isAr = document.documentElement.lang === 'ar' || document.documentElement.dir === 'rtl';

            grid.innerHTML = FLOOR_CATALOG.map(f => {
                const isActive = currentBg.includes(f.id);
                return `
                    <div class="furn-card ${isActive ? 'selected' : ''}" style="display:flex; flex-direction:column; gap:4px; padding:6px; cursor:pointer; position:relative; border-radius:12px; border:1px solid ${isActive ? 'var(--ula-accent-default)' : 'var(--ula-border-subtle)'}; background:var(--ula-surface-page);" onclick="applyFloorBackground('${f.url}', 1200, 708)">
                        <div style="position:relative; width:100%; height:75px; border-radius:8px; overflow:hidden; background: var(--ula-surface-page-alt);">
                            <img src="${f.thumb}" alt="${f.name_en}" style="width:100%; height:100%; object-fit:cover;">
                            <span style="position:absolute; bottom:3px; inset-inline-end:3px; background: var(--ula-surface-raised); font-size:9px; font-family:var(--ula-font-mono); padding:1px 4px; border-radius:4px; color:var(--ula-text-secondary); direction:ltr; unicode-bidi:isolate;">1200×708</span>
                            ${isActive ? '<span style="position:absolute; top:3px; inset-inline-start:3px; background:var(--ula-accent-default); font-size:9px; font-weight:800; padding:1px 6px; border-radius:4px; color:var(--ula-accent-fg);"><span class="material-symbols-rounded" style="font-size: 1em; vertical-align: text-bottom;">check</span> نشط</span>' : ''}
                        </div>
                        <div style="font-size:11px; font-weight:700; color:var(--ula-text-primary); white-space:nowrap; overflow:hidden; text-overflow:ellipsis; text-align:start;">
                            ${isAr ? f.name_ar : f.name_en}
                        </div>
                    </div>
                `;
            }).join('');
        }

        async function applyFloorBackground(floorUrl, width = 1200, height = 708) {
            showToast('⏳ {{ __("Applying Floor Style...") }}');
            try {
                const res = await fetch(`/editor/maps/${MAP_ID}/background`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': CSRF_TOKEN,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        floor_url: floorUrl,
                        width: width,
                        height: height
                    })
                });
                const data = await res.json();
                if (res.ok) {
                    MAP_DATA.layout_data = MAP_DATA.layout_data || {};
                    MAP_DATA.layout_data.background_image_url = floorUrl;
                    MAP_DATA.layout_data.background_width = width;
                    MAP_DATA.layout_data.background_height = height;
                    
                    MAP_WIDTH_PX = width;
                    MAP_HEIGHT_PX = height;
                    
                    BLUEPRINT_IMAGE.src = floorUrl + (floorUrl.includes('?') ? '&' : '?') + 'v=' + Date.now();
                    blueprintLoaded = true;
                    
                    fitAndCenterView();
                    renderFloorsCatalog();
                    showToast('✅ {{ __("Floor Style Applied Successfully") }}');
                } else {
                    showToast('❌ ' + (data.message || 'Failed to apply floor style'));
                }
            } catch (err) {
                console.error(err);
                showToast('❌ Failed to apply floor style');
            }
        }

        async function handleCustomFloorUpload(input) {
            if (!input.files || !input.files[0]) return;
            const file = input.files[0];
            const formData = new FormData();
            formData.append('image', file);

            showToast('⏳ {{ __("Uploading Custom Floor Image...") }}');
            try {
                const res = await fetch(`/editor/maps/${MAP_ID}/background`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': CSRF_TOKEN,
                        'Accept': 'application/json'
                    },
                    body: formData
                });
                const data = await res.json();
                if (res.ok && data.image_url) {
                    const uploadedUrl = data.image_url;
                    MAP_DATA.layout_data = MAP_DATA.layout_data || {};
                    MAP_DATA.layout_data.background_image_url = uploadedUrl;
                    
                    BLUEPRINT_IMAGE.src = uploadedUrl + '?v=' + Date.now();
                    BLUEPRINT_IMAGE.onload = () => {
                        blueprintLoaded = true;
                        if (BLUEPRINT_IMAGE.naturalWidth > 0 && BLUEPRINT_IMAGE.naturalHeight > 0) {
                            MAP_WIDTH_PX = BLUEPRINT_IMAGE.naturalWidth;
                            MAP_HEIGHT_PX = BLUEPRINT_IMAGE.naturalHeight;
                        }
                        fitAndCenterView();
                        renderFloorsCatalog();
                        showToast('✅ {{ __("Floor Background Uploaded & Applied") }}');
                    };
                } else {
                    showToast('❌ ' + (data.message || 'Upload failed'));
                }
            } catch (err) {
                console.error(err);
                showToast('❌ Upload failed');
            }
        }

        async function clearCurrentFloorBackground() {
            if (!confirm('{{ __("Are you sure you want to remove the floor background?") }}')) return;
            try {
                const res = await fetch(`/editor/maps/${MAP_ID}/background`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': CSRF_TOKEN,
                        'Accept': 'application/json'
                    }
                });
                if (res.ok) {
                    if (MAP_DATA.layout_data) {
                        delete MAP_DATA.layout_data.background_image_url;
                        MAP_DATA.layout_data.background_width = 1200;
                        MAP_DATA.layout_data.background_height = 708;
                    }
                    blueprintLoaded = false;
                    BLUEPRINT_IMAGE.removeAttribute('src');
                    BLUEPRINT_IMAGE.src = '';
                    MAP_WIDTH_PX = 1200;
                    MAP_HEIGHT_PX = 708;
                    fitAndCenterView();
                    renderFloorsCatalog();
                    draw();
                    showToast('✅ {{ __("Background removed") }}');
                }
            } catch (err) {
                console.error(err);
            }
        }

        function switchDrawerTab(tab) {
            document.querySelectorAll('.drawer-tab').forEach(el => el.classList.remove('active'));
            document.getElementById(`tab-btn-${tab}`)?.classList.add('active');
            document.getElementById('drawer-view-furniture').style.display = tab === 'furniture' ? 'flex' : 'none';
            document.getElementById('drawer-view-floors').style.display = tab === 'floors' ? 'flex' : 'none';
            document.getElementById('drawer-view-inspector').style.display = tab === 'inspector' ? 'flex' : 'none';
            document.getElementById('drawer-view-rooms').style.display = tab === 'rooms' ? 'flex' : 'none';
            if (tab === 'floors') renderFloorsCatalog();
            if (tab === 'rooms') renderRoomsDirectory();
        }

        function toggleCategoryGroup(id) {
            const grid = document.querySelector(`#${id} .furniture-grid`);
            const chevron = document.querySelector(`#chevron-${id}`);
            if (grid) {
                const isHidden = (grid.style.display === 'none' || getComputedStyle(grid).display === 'none');
                grid.style.display = isHidden ? 'grid' : 'none';
                if (chevron) chevron.textContent = isHidden ? '▴' : '▾';
            }
        }

        function filterByCategory(slug) {
            document.querySelectorAll('.cat-pill').forEach(c => c.classList.remove('active'));
            if (window.event && window.event.currentTarget) window.event.currentTarget.classList.add('active');
            
            let visibleCount = 0;
            document.querySelectorAll('.category-group').forEach(acc => {
                const match = (slug === 'all' || acc.id === `cat-${slug}`);
                acc.style.display = match ? 'block' : 'none';
                const grid = acc.querySelector('.furniture-grid');
                const chevron = acc.querySelector('.cat-chevron');
                if (grid) {
                    if (match) {
                        grid.style.display = 'grid';
                        if (chevron) chevron.textContent = '▴';
                        visibleCount += grid.querySelectorAll('.furn-card').length;
                    } else {
                        grid.style.display = 'none';
                        if (chevron) chevron.textContent = '▾';
                    }
                }
            });
            const countLabel = document.getElementById('catalog-count-label');
            if (countLabel) countLabel.textContent = `✨ ${visibleCount} {{ __('Items shown') }}`;
        }

        function filterFurniture(q) {
            const term = (q || '').trim().toLowerCase();
            const clearBtn = document.getElementById('search-clear-btn');
            if (clearBtn) clearBtn.style.display = term ? 'block' : 'none';

            let visibleTotal = 0;
            document.querySelectorAll('.category-group').forEach(group => {
                let groupHasMatch = false;
                group.querySelectorAll('.furn-card').forEach(card => {
                    const name = card.getAttribute('data-name') || card.querySelector('.furn-label')?.textContent.toLowerCase() || '';
                    const match = !term || name.includes(term);
                    card.style.display = match ? 'flex' : 'none';
                    if (match) {
                        groupHasMatch = true;
                        visibleTotal++;
                    }
                });
                group.style.display = (groupHasMatch || !term) ? 'block' : 'none';
                const grid = group.querySelector('.furniture-grid');
                const chevron = group.querySelector('.cat-chevron');
                if (term) {
                    if (grid) grid.style.display = groupHasMatch ? 'grid' : 'none';
                    if (chevron) chevron.textContent = groupHasMatch ? '▴' : '▾';
                }
            });

            const countLabel = document.getElementById('catalog-count-label');
            if (countLabel) countLabel.textContent = term ? `🔍 ${visibleTotal} {{ __('Matches found') }}` : `✨ ${visibleTotal} {{ __('Items available') }}`;
        }

        function clearFurnitureSearch() {
            const inp = document.getElementById('furniture-search-input');
            if (inp) { inp.value = ''; filterFurniture(''); }
        }

        function expandAllCategories() {
            const grids = document.querySelectorAll('.furniture-grid');
            const anyClosed = Array.from(grids).some(g => g.style.display === 'none' || getComputedStyle(g).display === 'none');
            grids.forEach(g => g.style.display = anyClosed ? 'grid' : 'none');
            document.querySelectorAll('.cat-chevron').forEach(ch => ch.textContent = anyClosed ? '▴' : '▾');
        }

        function selectFurnitureItem(slug, color, imgUrl = null, w = 1, h = 1, col = true, interactionType = 'none', interactionConfig = null, elevation = 1, itemName = null) {
            setTool('object');
            currentObjectType = slug;
            currentObjectColor = color || '#3B82F6';
            currentObjectCustom = {
                name: itemName,
                imageUrl: imgUrl,
                width: parseInt(w) || 1,
                height: parseInt(h) || 1,
                collision: Boolean(col),
                interactionType: interactionType || 'none',
                interactionConfig: interactionConfig || null,
                elevation: (typeof elevation !== 'undefined' && elevation !== null) ? parseInt(elevation) : 1
            };
            document.querySelectorAll('.furn-card').forEach(el => el.classList.remove('active'));
            if (window.event && window.event.currentTarget) window.event.currentTarget.classList.add('active');
        }

        let gridSnapStep = 0.25; // 4px micro precision
        let gridSnapLabel = '4px';

        function cycleGridSnap() {
            if (gridSnapStep === 0.25) {
                gridSnapStep = 0.5;
                gridSnapLabel = '8px';
            } else if (gridSnapStep === 0.5) {
                gridSnapStep = 1.0;
                gridSnapLabel = '16px';
            } else if (gridSnapStep === 1.0) {
                gridSnapStep = 0.0625;
                gridSnapLabel = 'Free';
            } else {
                gridSnapStep = 0.25;
                gridSnapLabel = '4px';
            }
            const btn = document.getElementById('btn-grid-snap');
            if (btn) btn.textContent = `🎯 ${gridSnapLabel}`;
            showToast(`🎯 {{ __("Grid Snap Precision:") }} ${gridSnapLabel}`);
            draw();
        }

        function snapCoordinate(val, step = gridSnapStep) {
            if (step <= 0) return val;
            return Math.round(val / step) * step;
        }

        function zoomIn() { zoomLevel = Math.min(3.5, zoomLevel + 0.15); draw(); }
        function zoomOut() { zoomLevel = Math.max(0.25, zoomLevel - 0.15); draw(); }
        function resetView() { fitAndCenterView(); draw(); }
        function toggleGrid() { showGrid = !showGrid; draw(); }

        // ── Canvas Interaction Handlers ──
        canvas.addEventListener('contextmenu', (e) => e.preventDefault());

        canvas.addEventListener('wheel', (e) => {
            e.preventDefault();
            const zoomDelta = e.deltaY < 0 ? 0.12 : -0.12;
            const newZoom = Math.max(0.25, Math.min(3.5, zoomLevel + zoomDelta));
            if (newZoom !== zoomLevel) {
                const rect = canvas.getBoundingClientRect();
                const mouseX = e.clientX - rect.left;
                const mouseY = e.clientY - rect.top;
                panOffset.x -= (mouseX - panOffset.x) * (newZoom / zoomLevel - 1);
                panOffset.y -= (mouseY - panOffset.y) * (newZoom / zoomLevel - 1);
                zoomLevel = newZoom;
                draw();
            }
        }, { passive: false });

        canvas.addEventListener('mousedown', (e) => {
            if (e.button === 1 || e.button === 2 || (e.button === 0 && e.altKey)) {
                isPanning = true;
                panStartX = e.clientX - panOffset.x;
                panStartY = e.clientY - panOffset.y;
                canvas.style.cursor = 'grab';
                return;
            }

            const rect = canvas.getBoundingClientRect();
            const mouseX = (e.clientX - rect.left - panOffset.x) / zoomLevel;
            const mouseY = (e.clientY - rect.top - panOffset.y) / zoomLevel;

            const rawTileX = mouseX / TILE_SIZE;
            const rawTileY = mouseY / TILE_SIZE;
            const tileX = Math.floor(rawTileX);
            const tileY = Math.floor(rawTileY);

            if (currentTool === 'select') {
                let clicked = null;
                // Objects first (support sub-tile hit testing)
                for (let i = objects.length - 1; i >= 0; i--) {
                    const obj = objects[i];
                    const ox = (obj.position ? obj.position.x : 0);
                    const oy = (obj.position ? obj.position.y : 0);
                    const ow = obj.width || (obj.size ? obj.size.width : 1);
                    const oh = obj.height || (obj.size ? obj.size.height : 1);
                    if (rawTileX >= ox && rawTileX < ox + ow &&
                        rawTileY >= oy && rawTileY < oy + oh) {
                        clicked = { type: 'object', item: obj };
                        break;
                    }
                }
                // Rooms second
                if (!clicked) {
                    for (let i = rooms.length - 1; i >= 0; i--) {
                        const r = rooms[i];
                        if (!r.bounds) continue;
                        if (rawTileX >= r.bounds.x && rawTileX < r.bounds.x + r.bounds.width &&
                            rawTileY >= r.bounds.y && rawTileY < r.bounds.y + r.bounds.height) {
                            clicked = { type: 'room', item: r };
                            break;
                        }
                    }
                }

                selectedItem = clicked;
                if (selectedItem) {
                    isDragging = true;
                    dragStartTileX = rawTileX;
                    dragStartTileY = rawTileY;

                    if (selectedItem.type === 'object') {
                        dragOrigX = (selectedItem.item.position ? selectedItem.item.position.x : 0);
                        dragOrigY = (selectedItem.item.position ? selectedItem.item.position.y : 0);
                        roomContainedObjects = [];
                    } else if (selectedItem.type === 'room') {
                        dragOrigX = selectedItem.item.bounds.x;
                        dragOrigY = selectedItem.item.bounds.y;
                        const rb = selectedItem.item.bounds;
                        roomContainedObjects = objects.filter(obj => {
                            const ox = (obj.position ? obj.position.x : 0);
                            const oy = (obj.position ? obj.position.y : 0);
                            return (ox >= rb.x && ox < rb.x + rb.width && oy >= rb.y && oy < rb.y + rb.height);
                        }).map(obj => ({
                            obj: obj,
                            relX: (obj.position ? obj.position.x : 0) - rb.x,
                            relY: (obj.position ? obj.position.y : 0) - rb.y
                        }));
                    }
                    canvas.style.cursor = 'grabbing';
                }

                updateInspector();
                updateFloatingActions();
                draw();
            } else if (currentTool === 'room') {
                isDrawing = true;
                startX = tileX;
                startY = tileY;
                currentRect = { x: tileX, y: tileY, width: 1, height: 1 };
            } else if (currentTool === 'object') {
                let objImgUrl = currentObjectCustom?.imageUrl || null;
                let initConfig = currentObjectCustom?.interactionConfig ? JSON.parse(JSON.stringify(currentObjectCustom.interactionConfig)) : {};
                let iType = currentObjectCustom?.interactionType || 'none';

                if (currentObjectType === 'branding') {
                    iType = 'branding';
                    if (COMPANY_LOGO_URL) {
                        objImgUrl = COMPANY_LOGO_URL;
                    }
                    initConfig = {
                        behavior: { type: 'branding', data: { use_company_logo: true } },
                        use_company_logo: true,
                        image_url: objImgUrl
                    };
                } else if (currentObjectType === 'sticky_note') {
                    iType = 'stickyNote';
                    initConfig = {
                        behavior: { type: 'stickyNote', data: { text: '' } },
                        noteText: '',
                        color: 'yellow'
                    };
                } else if (currentObjectType === 'custom_link') {
                    iType = 'link';
                    initConfig = {
                        behavior: { type: 'link', data: { url: '', title: '', openInNewTab: true } },
                        url: '',
                        title: '',
                        openInNewTab: true
                    };
                } else if (currentObjectType === 'custom_image') {
                    iType = 'customImage';
                    initConfig = {
                        behavior: { type: 'customImage', data: { imageUrl: objImgUrl, openInNewTab: false } },
                        image_url: objImgUrl
                    };
                }

                const maxTilesX = MAP_WIDTH_PX / TILE_SIZE;
                const maxTilesY = MAP_HEIGHT_PX / TILE_SIZE;
                const placeW = currentObjectCustom?.width || 1;
                const placeH = currentObjectCustom?.height || 1;
                const placeX = Math.max(0, Math.min(maxTilesX - placeW, snapCoordinate(rawTileX, gridSnapStep)));
                const placeY = Math.max(0, Math.min(maxTilesY - placeH, snapCoordinate(rawTileY, gridSnapStep)));

                const newObj = {
                    type: currentObjectType,
                    name: currentObjectCustom?.name || `${currentObjectType.replace(/_/g, ' ')} #${objects.length + 1}`,
                    position: { x: placeX, y: placeY, rotation: 0 },
                    color: currentObjectColor,
                    image_url: objImgUrl,
                    width: placeW,
                    height: placeH,
                    collision: currentObjectCustom ? currentObjectCustom.collision : true,
                    elevation: currentObjectCustom?.elevation || 1,
                    interaction_type: iType,
                    interaction_config: initConfig,
                    is_custom: true
                };
                objects.push(newObj);
                selectedItem = { type: 'object', item: newObj };
                setTool('select');
                updateInspector();
                updateFloatingActions();
                draw();
            }
        });

        canvas.addEventListener('mousemove', (e) => {
            if (isPanning) {
                panOffset.x = e.clientX - panStartX;
                panOffset.y = e.clientY - panStartY;
                draw();
                return;
            }

            const rect = canvas.getBoundingClientRect();
            const mouseX = (e.clientX - rect.left - panOffset.x) / zoomLevel;
            const mouseY = (e.clientY - rect.top - panOffset.y) / zoomLevel;

            const rawTileX = mouseX / TILE_SIZE;
            const rawTileY = mouseY / TILE_SIZE;
            const tileX = Math.floor(rawTileX);
            const tileY = Math.floor(rawTileY);

            if (isDragging && selectedItem) {
                const maxTilesX = MAP_WIDTH_PX / TILE_SIZE;
                const maxTilesY = MAP_HEIGHT_PX / TILE_SIZE;
                const dx = rawTileX - dragStartTileX;
                const dy = rawTileY - dragStartTileY;

                if (selectedItem.type === 'object') {
                    const objW = selectedItem.item.width || (selectedItem.item.size ? selectedItem.item.size.width : 1);
                    const objH = selectedItem.item.height || (selectedItem.item.size ? selectedItem.item.size.height : 1);
                    selectedItem.item.position.x = Math.max(0, Math.min(maxTilesX - objW, snapCoordinate(dragOrigX + dx, gridSnapStep)));
                    selectedItem.item.position.y = Math.max(0, Math.min(maxTilesY - objH, snapCoordinate(dragOrigY + dy, gridSnapStep)));
                } else if (selectedItem.type === 'room') {
                    const rw = selectedItem.item.bounds.width || 1;
                    const rh = selectedItem.item.bounds.height || 1;
                    const newRoomX = Math.max(0, Math.min(maxTilesX - rw, Math.round(dragOrigX + dx)));
                    const newRoomY = Math.max(0, Math.min(maxTilesY - rh, Math.round(dragOrigY + dy)));
                    selectedItem.item.bounds.x = newRoomX;
                    selectedItem.item.bounds.y = newRoomY;

                    if (roomContainedObjects && roomContainedObjects.length > 0) {
                        roomContainedObjects.forEach(entry => {
                            if (entry.obj && entry.obj.position) {
                                entry.obj.position.x = Math.max(0, Math.min(maxTilesX - 1, newRoomX + entry.relX));
                                entry.obj.position.y = Math.max(0, Math.min(maxTilesY - 1, newRoomY + entry.relY));
                            }
                        });
                    }
                }
                updateFloatingActions();
                draw();
                return;
            }

            if (isDrawing) {
                const x = Math.min(startX, tileX);
                const y = Math.min(startY, tileY);
                const w = Math.max(1, Math.abs(tileX - startX) + 1);
                const h = Math.max(1, Math.abs(tileY - startY) + 1);
                currentRect = { x, y, width: w, height: h };
                draw();
            }
        });

        window.addEventListener('mouseup', () => {
            if (isPanning) {
                isPanning = false;
                canvas.style.cursor = currentTool === 'select' ? 'default' : 'crosshair';
            }

            if (isDragging) {
                isDragging = false;
                if (selectedItem && selectedItem.type === 'room' && selectedItem.item.bounds) {
                    const rb = selectedItem.item.bounds;
                    const before = { x: rb.x, y: rb.y };
                    const snapped = snapRoomBounds(rb, rooms, 'move');
                    const inMap = snapped.x >= 0 && snapped.y >= 0 && (snapped.x + snapped.width) * TILE_SIZE <= MAP_WIDTH_PX && (snapped.y + snapped.height) * TILE_SIZE <= MAP_HEIGHT_PX;
                    if (inMap) { rb.x = snapped.x; rb.y = snapped.y; }
                    let conflict = roomSpacingConflict(selectedItem.item);
                    const doorVictim = conflict ? null : blockedNeighbourDoor(selectedItem.item);
                    if (conflict || doorVictim) { rb.x = dragOrigX; rb.y = dragOrigY; }
                    const shiftX = rb.x - before.x, shiftY = rb.y - before.y;
                    (roomContainedObjects || []).forEach(entry => {
                        if (entry.obj && entry.obj.position) { entry.obj.position.x += shiftX; entry.obj.position.y += shiftY; }
                    });
                    if (doorVictim) {
                        showToast('❌ ' + @json(__('This room would block the door of ":name". Move that room\'s door to another wall first.')).replace(':name', doorVictim === selectedItem.item ? @json(__('this room')) : (doorVictim.name || '')));
                    } else if (conflict) {
                        showToast('❌ ' + @json(__('This room must either share a wall with ":name" or be at least :gap tiles away from it, so people can walk between them.')).replace(':name', conflict.name || '').replace(':gap', ROOM_MIN_GAP_TILES));
                    } else if (shiftX || shiftY) {
                        showToast('✅ ' + @json(__('Snapped to the neighbouring room wall')));
                    }
                    updateInspector();
                }
                roomContainedObjects = [];
                canvas.style.cursor = currentTool === 'select' ? 'default' : 'crosshair';
                updateFloatingActions();
                draw();
            }

            if (isDrawing && currentRect) {
                if (currentTool === 'room') {
                    if (PLAN_MAX_ROOMS > 0 && rooms.length >= PLAN_MAX_ROOMS) {
                        isDrawing = false;
                        currentRect = null;
                        draw();
                        alert(`{{ __('Room Limit Exceeded!') }}\n{{ __('Your subscription plan allows a maximum of :limit rooms.', ['limit' => '']) }}${PLAN_MAX_ROOMS}\n{{ __('Please upgrade your plan to add more rooms.') }}`);
                        setTool('select');
                        return;
                    }

                    const snappedRect = snapRoomBounds(currentRect, rooms, 'resize');
                    const didSnap = ['x', 'y', 'width', 'height'].some(k => snappedRect[k] !== currentRect[k]);
                    const newRoom = {
                        name: `${currentRoomType.charAt(0).toUpperCase() + currentRoomType.slice(1)} Room`,
                        type: currentRoomType,
                        access_mode: currentRoomType === 'private' ? 'private' : 'public',
                        capacity: 10,
                        color: currentRoomColor,
                        bounds: { ...snappedRect },
                        metadata: { audio_isolation: true }
                    };
                    const conflict = roomSpacingConflict(newRoom);
                    if (conflict) {
                        isDrawing = false;
                        currentRect = null;
                        draw();
                        showToast('❌ ' + @json(__('This room must either share a wall with ":name" or be at least :gap tiles away from it, so people can walk between them.')).replace(':name', conflict.name || '').replace(':gap', ROOM_MIN_GAP_TILES));
                        return;
                    }
                    rooms.push(newRoom);
                    const doorVictim = blockedNeighbourDoor(newRoom);
                    if (doorVictim) {
                        rooms.splice(rooms.indexOf(newRoom), 1);
                        isDrawing = false;
                        currentRect = null;
                        draw();
                        showToast('❌ ' + @json(__('This room would block the door of ":name". Move that room\'s door to another wall first.')).replace(':name', doorVictim === newRoom ? @json(__('this room')) : (doorVictim.name || '')));
                        return;
                    }
                    selectedItem = { type: 'room', item: newRoom };

                    // Save room to backend
                    fetch('/editor/rooms', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': CSRF_TOKEN,
                            'Accept': 'application/json'
                        },
                        credentials: 'same-origin',
                        body: JSON.stringify({
                            organization_id: ORG_ID,
                            map_id: MAP_ID,
                            name: newRoom.name,
                            type: newRoom.type,
                            access_mode: newRoom.access_mode,
                            capacity: newRoom.capacity,
                            color: newRoom.color,
                            bounds: newRoom.bounds,
                            metadata: newRoom.metadata
                        })
                    }).then(async res => {
                        const data = await res.json().catch(() => ({}));
                        if (res.ok && data.room && data.room.id) { newRoom.id = data.room.id; return; }
                        // Rejected by the server: take it back off the canvas and say why.
                        const idx = rooms.indexOf(newRoom);
                        if (idx > -1) rooms.splice(idx, 1);
                        if (selectedItem && selectedItem.item === newRoom) { selectedItem = null; updateInspector(); hideFloatingActions(); }
                        draw();
                        showToast('❌ ' + (data.message || @json(__('Failed to save room'))));
                    }).catch(console.error);

                    switchDrawerTab('inspector');
                    showToast(didSnap ? '✅ ' + @json(__('Snapped to the neighbouring room wall')) : '🏢 {{ __("Room created!") }}');
                }
                isDrawing = false;
                currentRect = null;
                setTool('select');
                updateInspector();
                updateFloatingActions();
                draw();
            }
        });

        // Keyboard Shortcuts
        window.addEventListener('keydown', (e) => {
            if (['input', 'textarea', 'select'].includes(document.activeElement.tagName.toLowerCase())) return;
            const k = e.key.toLowerCase();
            if (k === 'r' && selectedItem && selectedItem.type === 'object') {
                rotateSelectedItem(90);
            } else if ((k === 'delete' || k === 'backspace') && selectedItem) {
                deleteSelectedItem();
            } else if (k === 'd' && selectedItem && selectedItem.type === 'object') {
                duplicateSelectedItem();
            } else if (k === 'escape') {
                selectedItem = null;
                updateInspector();
                hideFloatingActions();
                draw();
            }
        });

        // ── Room adjacency ──
        // Rooms either share a wall or keep a full walking corridor (RoomBoundsGap on the server).
        // A room drawn or dropped closer than that snaps flush to its neighbour's wall.
        const ROOM_MIN_GAP_TILES = {{ \App\Domains\Workspace\Support\RoomBoundsGap::minGapTiles($map->tile_size ?: \App\Domains\Workspace\Support\RoomBoundsGap::CANONICAL_TILE_PX) }};

        function roomSeparation(a, b) {
            const dx = Math.max(b.x - (a.x + a.width), a.x - (b.x + b.width));
            const dy = Math.max(b.y - (a.y + a.height), a.y - (b.y + b.height));
            return { dx, dy, gap: Math.max(dx, dy) };
        }

        function roomGapAllowed(gap) {
            return Math.abs(gap) < 0.01 || gap >= ROOM_MIN_GAP_TILES;
        }

        // mode 'resize': move the near edge (a freshly drawn rectangle keeps its far corner).
        // mode 'move':   shift the whole room (a dragged room keeps its size).
        function snapRoomBounds(b, others, mode) {
            const out = { ...b };
            for (let pass = 0; pass < 4; pass++) {
                let changed = false;
                for (const o of others) {
                    if (!o.bounds || o.bounds === b) continue;
                    const n = o.bounds;
                    const { dx, dy } = roomSeparation(out, n);
                    const near = d => d > 0 && d < ROOM_MIN_GAP_TILES;
                    // Horizontal neighbour: close on x while rows overlap or are also close.
                    if (near(dx) && (dy < 0 || near(dy) || Math.abs(dy) < 0.01)) {
                        if (out.x + out.width <= n.x) {            // neighbour on the right
                            if (mode === 'move') out.x = n.x - out.width; else out.width = n.x - out.x;
                        } else {                                     // neighbour on the left
                            const edge = n.x + n.width;
                            if (mode === 'move') out.x = edge; else { out.width += out.x - edge; out.x = edge; }
                        }
                        changed = true;
                    }
                    const again = roomSeparation(out, n);
                    if (near(again.dy) && (again.dx < 0 || near(again.dx) || Math.abs(again.dx) < 0.01)) {
                        if (out.y + out.height <= n.y) {           // neighbour below
                            if (mode === 'move') out.y = n.y - out.height; else out.height = n.y - out.y;
                        } else {                                     // neighbour above
                            const edge = n.y + n.height;
                            if (mode === 'move') out.y = edge; else { out.height += out.y - edge; out.y = edge; }
                        }
                        changed = true;
                    }
                    // Small overlap: trim (resize) or push out (move) along the shallower axis.
                    const ov = roomSeparation(out, n);
                    if (ov.dx < 0 && ov.dy < 0) {
                        const penX = Math.min(out.x + out.width - n.x, n.x + n.width - out.x);
                        const penY = Math.min(out.y + out.height - n.y, n.y + n.height - out.y);
                        if (Math.min(penX, penY) <= ROOM_MIN_GAP_TILES) {
                            if (penX <= penY) {
                                const fromLeft = out.x < n.x;
                                if (mode === 'move') out.x = fromLeft ? n.x - out.width : n.x + n.width;
                                else if (fromLeft) out.width = n.x - out.x;
                                else { out.width -= (n.x + n.width) - out.x; out.x = n.x + n.width; }
                            } else {
                                const fromTop = out.y < n.y;
                                if (mode === 'move') out.y = fromTop ? n.y - out.height : n.y + n.height;
                                else if (fromTop) out.height = n.y - out.y;
                                else { out.height -= (n.y + n.height) - out.y; out.y = n.y + n.height; }
                            }
                            changed = true;
                        }
                    }
                }
                if (!changed) break;
            }
            return (out.width >= 1 && out.height >= 1) ? out : { ...b };
        }

        // First sibling this room is still too close to (or overlapping) after snapping, if any.
        function roomSpacingConflict(room) {
            for (const o of rooms) {
                if (o === room || !o.bounds) continue;
                if (!roomGapAllowed(roomSeparation(room.bounds, o.bounds).gap)) return o;
            }
            return null;
        }

        // ── Door geometry, mirrored from office.blade.php getRoomDoorPortal() ──
        // A door is only usable when its outside step lands on free floor, not inside another room.
        function doorSideBlocked(room, side, offset) {
            if (!room || !room.bounds || side === 'auto') return false;
            const rx = room.bounds.x * TILE_SIZE, ry = room.bounds.y * TILE_SIZE;
            const rw = room.bounds.width * TILE_SIZE, rh = room.bounds.height * TILE_SIZE;
            let cx, cy, outX, outY;
            if (side === 'bottom') { cx = rx + rw * offset; cy = ry + rh; outX = cx; outY = cy + 32; }
            else if (side === 'top') { cx = rx + rw * offset; cy = ry; outX = cx; outY = cy - 32; }
            else if (side === 'right') { cx = rx + rw; cy = ry + rh * offset; outX = cx + 32; outY = cy; }
            else { cx = rx; cy = ry + rh * offset; outX = cx - 32; outY = cy; }
            if (outX < 0 || outY < 0 || outX > MAP_WIDTH_PX || outY > MAP_HEIGHT_PX) return true;
            for (const o of rooms) {
                if (o === room || !o.bounds) continue;
                const ox = o.bounds.x * TILE_SIZE, oy = o.bounds.y * TILE_SIZE;
                const ow = o.bounds.width * TILE_SIZE, oh = o.bounds.height * TILE_SIZE;
                if (outX >= ox - 6 && outX <= ox + ow + 6 && outY >= oy - 6 && outY <= oy + oh + 6) return true;
            }
            return false;
        }

        // Where an 'auto' door will actually go: first wall whose centre opens onto free floor.
        function autoDoorSide(room) {
            for (const side of ['bottom', 'top', 'right', 'left']) {
                if (!doorSideBlocked(room, side, 0.5)) return side;
            }
            return 'bottom';
        }

        // Can this room still be entered? Its chosen door (or, for 'auto', any wall) must open onto free floor.
        function roomDoorUsable(room) {
            const side = room.bounds && room.bounds.doorSide;
            if (side && side !== 'auto') {
                const off = typeof room.bounds.doorOffset === 'number' ? room.bounds.doorOffset : 0.5;
                return !doorSideBlocked(room, side, off);
            }
            return ['bottom', 'top', 'right', 'left'].some(s => !doorSideBlocked(room, s, 0.5));
        }

        // A neighbour whose only way in would be covered by this room, if any. Call with the room already in `rooms`.
        function blockedNeighbourDoor(room) {
            for (const o of rooms) {
                if (o === room || !o.bounds) continue;
                if (!roomGapAllowed(roomSeparation(room.bounds, o.bounds).gap) || roomSeparation(room.bounds, o.bounds).gap > 0.01) continue;
                if (!roomDoorUsable(o)) return o;
            }
            return roomDoorUsable(room) ? null : room;
        }
        // ── Canvas palette from the design tokens ──
        // A canvas can't resolve var(--…), so the token values are read once here and again on theme change.
        const ED = {};
        const edProbe = document.createElement('canvas').getContext('2d');
        function edColor(name) {
            const v = getComputedStyle(document.documentElement).getPropertyValue(name).trim();
            edProbe.fillStyle = '#000';
            if (v) edProbe.fillStyle = v;
            return edProbe.fillStyle;
        }
        function edAlpha(col, a) {
            if (col.startsWith('#')) {
                const n = parseInt(col.slice(1), 16);
                return `rgba(${(n >> 16) & 255}, ${(n >> 8) & 255}, ${n & 255}, ${a})`;
            }
            const m = col.match(/[\d.]+/g);
            return m ? `rgba(${m[0]}, ${m[1]}, ${m[2]}, ${a})` : col;
        }
        function refreshEditorPalette() {
            const accent = edColor('--ula-accent-default');
            const gold = edColor('--ula-highlight-default');
            const raised = edColor('--ula-surface-raised');
            Object.assign(ED, {
                mapBg: edColor('--ula-surface-card'),
                blueprintBg: edColor('--ula-surface-page-alt'),
                gridMinor: edAlpha(accent, 0.06),
                gridMajor: edAlpha(accent, 0.16),
                mapBorder: edColor('--ula-border-strong'),
                roomWash: edAlpha(accent, 0.06),
                roomLine: edAlpha(accent, 0.5),
                label: raised,
                labelLine: edColor('--ula-border-default'),
                labelText: edColor('--ula-text-primary'),
                accent,
                accentWash: edAlpha(accent, 0.14),
                accentFill: edAlpha(accent, 0.18),
                open: gold,
                openWash: edAlpha(gold, 0.14),
                knob: raised,
                objDefault: edAlpha(accent, 0.35),
                objLine: edAlpha(raised, 0.5),
            });
        }
        refreshEditorPalette();
        window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', () => { refreshEditorPalette(); if (typeof draw === 'function') draw(); });

        // ── Main Live Canvas Draw Loop (60 FPS Butter Smooth) ──
        function draw() {
            // Guarantee complete clean buffer wipe without transform accumulation
            ctx.setTransform(1, 0, 0, 1, 0, 0);
            ctx.clearRect(0, 0, canvas.width, canvas.height);

            ctx.save();
            ctx.translate(panOffset.x, panOffset.y);
            ctx.scale(zoomLevel, zoomLevel);

            const hasBlueprint = blueprintLoaded && BLUEPRINT_IMAGE && BLUEPRINT_IMAGE.complete && BLUEPRINT_IMAGE.naturalWidth > 0 && BLUEPRINT_IMAGE.src && !BLUEPRINT_IMAGE.src.endsWith('/');

            // 1. Draw Background Blueprint Layer
            if (hasBlueprint) {
                ctx.fillStyle = ED.blueprintBg;
                ctx.fillRect(0, 0, MAP_WIDTH_PX, MAP_HEIGHT_PX);
                ctx.drawImage(BLUEPRINT_IMAGE, 0, 0, MAP_WIDTH_PX, MAP_HEIGHT_PX);
            } else {
                ctx.fillStyle = ED.mapBg;
                ctx.fillRect(0, 0, MAP_WIDTH_PX, MAP_HEIGHT_PX);
            }

            // Grid Overlay (Fine micro-grid for precision object control)
            if (showGrid) {
                // Micro 4px sub-grid lines
                ctx.strokeStyle = ED.gridMinor;
                ctx.lineWidth = 0.5;
                const microStep = (gridSnapStep === 0.125 || gridSnapStep === 0.25) ? 4 : 8;
                for (let x = 0; x <= MAP_WIDTH_PX; x += microStep) {
                    if (x % TILE_SIZE === 0) continue;
                    ctx.beginPath(); ctx.moveTo(x, 0); ctx.lineTo(x, MAP_HEIGHT_PX); ctx.stroke();
                }
                for (let y = 0; y <= MAP_HEIGHT_PX; y += microStep) {
                    if (y % TILE_SIZE === 0) continue;
                    ctx.beginPath(); ctx.moveTo(0, y); ctx.lineTo(MAP_WIDTH_PX, y); ctx.stroke();
                }

                // Major 16px tile grid lines
                ctx.strokeStyle = ED.gridMajor;
                ctx.lineWidth = 1;
                for (let x = 0; x <= MAP_WIDTH_PX; x += TILE_SIZE) {
                    ctx.beginPath(); ctx.moveTo(x, 0); ctx.lineTo(x, MAP_HEIGHT_PX); ctx.stroke();
                }
                for (let y = 0; y <= MAP_HEIGHT_PX; y += TILE_SIZE) {
                    ctx.beginPath(); ctx.moveTo(0, y); ctx.lineTo(MAP_WIDTH_PX, y); ctx.stroke();
                }
            }

            ctx.strokeStyle = ED.mapBorder;
            ctx.lineWidth = 2.5;
            ctx.strokeRect(0, 0, MAP_WIDTH_PX, MAP_HEIGHT_PX);

            // 2. Draw Unselected Rooms Dynamically (Sleek Glass Pills)
            rooms.forEach((r) => {
                const isSelected = selectedItem && selectedItem.type === 'room' && selectedItem.item === r;
                if (isSelected || !r.bounds) return;

                const rx = r.bounds.x * TILE_SIZE;
                const ry = r.bounds.y * TILE_SIZE;
                const rw = r.bounds.width * TILE_SIZE;
                const rh = r.bounds.height * TILE_SIZE;

                // Subtle transparent wash & dashed boundary
                ctx.fillStyle = ED.roomWash;
                ctx.fillRect(rx, ry, rw, rh);

                ctx.strokeStyle = ED.roomLine;
                ctx.lineWidth = 1.2;
                ctx.setLineDash([4, 4]);
                ctx.strokeRect(rx, ry, rw, rh);
                ctx.setLineDash([]);

                // Sleek Dark Glass Floating Room Pill Tag
                const labelText = r.name.split(' - ')[0];
                ctx.font = 'bold 9px Cairo, Inter, sans-serif';
                const textWidth = ctx.measureText(labelText).width;
                const badgeW = Math.min(rw - 8, textWidth + 14);

                if (badgeW > 16 && rw > 20 && rh > 18) {
                    ctx.fillStyle = ED.label;
                    if (ctx.roundRect) ctx.roundRect(rx + 4, ry + 4, badgeW, 18, 6);
                    else ctx.rect(rx + 4, ry + 4, badgeW, 18);
                    ctx.fill();

                    ctx.strokeStyle = ED.labelLine;
                    ctx.lineWidth = 1;
                    if (ctx.roundRect) ctx.roundRect(rx + 4, ry + 4, badgeW, 18, 6);
                    else ctx.rect(rx + 4, ry + 4, badgeW, 18);
                    ctx.stroke();

                    ctx.fillStyle = ED.labelText;
                    ctx.textAlign = 'left';
                    ctx.textBaseline = 'middle';
                    ctx.fillText(labelText, rx + 10, ry + 13);
                }

                // Visual Door Indicator on Wall
                const doorSide = (r.bounds && r.bounds.doorSide && r.bounds.doorSide !== 'auto') ? r.bounds.doorSide : autoDoorSide(r);
                const doorOffset = (r.bounds && typeof r.bounds.doorOffset === 'number') ? r.bounds.doorOffset : 0.5;
                const dW = Math.min(42, (doorSide === 'top' || doorSide === 'bottom') ? rw * 0.45 : rh * 0.45);
                let dX = rx + rw * doorOffset, dY = ry + rh;
                if (doorSide === 'top') { dX = rx + rw * doorOffset; dY = ry; }
                else if (doorSide === 'left') { dX = rx; dY = ry + rh * doorOffset; }
                else if (doorSide === 'right') { dX = rx + rw; dY = ry + rh * doorOffset; }

                ctx.save();
                ctx.fillStyle = ED.accent;
                ctx.strokeStyle = ED.knob;
                ctx.lineWidth = 1.5;
                if (doorSide === 'top' || doorSide === 'bottom') {
                    ctx.fillRect(dX - dW/2, dY - 3, dW, 6);
                    ctx.strokeRect(dX - dW/2, dY - 3, dW, 6);
                } else {
                    ctx.fillRect(dX - 3, dY - dW/2, 6, dW);
                    ctx.strokeRect(dX - 3, dY - dW/2, 6, dW);
                }
                ctx.restore();
            });

            // 3. Selected Room Rectangular Acoustic Sound Isolation Aura & Handles
            if (selectedItem && selectedItem.type === 'room' && selectedItem.item.bounds) {
                const r = selectedItem.item;
                const rx = r.bounds.x * TILE_SIZE;
                const ry = r.bounds.y * TILE_SIZE;
                const rw = r.bounds.width * TILE_SIZE;
                const rh = r.bounds.height * TILE_SIZE;

                r.metadata = r.metadata || {};
                const isIsolated = r.metadata.audio_isolation !== false;

                // Acoustic Aura Backdrop
                ctx.fillStyle = isIsolated ? ED.accentWash : ED.openWash;
                if (ctx.roundRect) ctx.roundRect(rx - 6, ry - 6, rw + 12, rh + 12, 10);
                else ctx.rect(rx - 6, ry - 6, rw + 12, rh + 12);
                ctx.fill();

                // Acoustic Sound Boundary Border
                ctx.strokeStyle = isIsolated ? ED.accent : ED.open;
                ctx.lineWidth = 2.5;
                ctx.setLineDash([8, 6]);
                if (ctx.roundRect) ctx.roundRect(rx - 2, ry - 2, rw + 4, rh + 4, 8);
                else ctx.rect(rx - 2, ry - 2, rw + 4, rh + 4);
                ctx.stroke();
                ctx.setLineDash([]);

                // Corner Grab Accent Nodes
                const corners = [
                    { x: rx - 2, y: ry - 2 },
                    { x: rx + rw + 2, y: ry - 2 },
                    { x: rx + rw + 2, y: ry + rh + 2 },
                    { x: rx - 2, y: ry + rh + 2 }
                ];
                corners.forEach(c => {
                    ctx.fillStyle = ED.accent;
                    ctx.beginPath();
                    ctx.arc(c.x, c.y, 4, 0, Math.PI * 2);
                    ctx.fill();
                    ctx.strokeStyle = ED.knob;
                    ctx.lineWidth = 1.5;
                    ctx.stroke();
                });

                // Acoustic Badge Indicator
                const badgeText = isIsolated ? `${r.name || 'Room'} · ${@json(__('Acoustic Boundary'))}` : `${r.name || 'Room'} · ${@json(__('Open Area'))}`;
                ctx.font = 'bold 11px Cairo, Inter, sans-serif';
                const bMetrics = ctx.measureText(badgeText);
                const bW = bMetrics.width + 22;
                const badgeX = rx + rw / 2 - bW / 2;
                const badgeY = ry - 30;

                ctx.fillStyle = ED.label;
                if (ctx.roundRect) ctx.roundRect(badgeX, badgeY, bW, 24, 6);
                else ctx.rect(badgeX, badgeY, bW, 24);
                ctx.fill();

                ctx.strokeStyle = isIsolated ? ED.accent : ED.open;
                ctx.lineWidth = 1.5;
                if (ctx.roundRect) ctx.roundRect(badgeX, badgeY, bW, 24, 6);
                else ctx.rect(badgeX, badgeY, bW, 24);
                ctx.stroke();

                ctx.fillStyle = ED.labelText;
                ctx.textAlign = 'center';
                ctx.textBaseline = 'middle';
                ctx.fillText(badgeText, rx + rw / 2, ry - 18);
            }

            // 4. Draw Furniture Objects with Elevation Depth Sorting (Floor Rugs -> Furniture -> Surfaces -> Ceiling)
            const sortedObjects = [...objects].sort((a, b) => {
                const isRugA = (a.type && (a.type.includes('rug') || a.type.includes('carpet'))) || (a.name && (a.name.toLowerCase().includes('rug') || a.name.toLowerCase().includes('carpet') || a.name.includes('سجاد')));
                const isRugB = (b.type && (b.type.includes('rug') || b.type.includes('carpet'))) || (b.name && (b.name.toLowerCase().includes('rug') || b.name.toLowerCase().includes('carpet') || b.name.includes('سجاد')));
                const defaultElevA = isRugA ? 0 : 1;
                const defaultElevB = isRugB ? 0 : 1;
                const elevA = typeof a.elevation === 'number' ? a.elevation : (a.interaction_config?.elevation ?? defaultElevA);
                const elevB = typeof b.elevation === 'number' ? b.elevation : (b.interaction_config?.elevation ?? defaultElevB);
                if (elevA !== elevB) return elevA - elevB;
                const yA = (a.position ? a.position.y : 0);
                const yB = (b.position ? b.position.y : 0);
                return yA - yB;
            });

            sortedObjects.forEach(obj => {
                const isSelected = selectedItem && selectedItem.type === 'object' && selectedItem.item === obj;
                const ox = (obj.position ? obj.position.x : 0) * TILE_SIZE;
                const oy = (obj.position ? obj.position.y : 0) * TILE_SIZE;
                const objW = (obj.width || (obj.size ? obj.size.width : 1)) * TILE_SIZE;
                const objH = (obj.height || (obj.size ? obj.size.height : 1)) * TILE_SIZE;
                const imgUrl = obj.image_url || (obj.interaction_config && obj.interaction_config.image_url);

                // If map has blueprint artwork, untextured collision items should not be painted as blue blocks
                if (hasBlueprint && !imgUrl) {
                    if (isSelected) {
                        ctx.save();
                        ctx.strokeStyle = ED.accent;
                        ctx.lineWidth = 1.5;
                        ctx.setLineDash([4, 4]);
                        ctx.strokeRect(ox, oy, objW, objH);
                        ctx.restore();
                    }
                    return;
                }

                ctx.save();
                ctx.translate(ox + objW / 2, oy + objH / 2);
                const rot = (obj.position && typeof obj.position.rotation === 'number') ? obj.position.rotation : (obj.rotation || 0);
                if (rot) ctx.rotate((rot * Math.PI) / 180);

                if (imgUrl) {
                    if (!window._objImgCache) window._objImgCache = new Map();
                    let sprImg = window._objImgCache.get(imgUrl);
                    if (!sprImg) {
                        sprImg = new Image();
                        sprImg.src = imgUrl;
                        sprImg.onload = () => { if (typeof draw === 'function') draw(); };
                        sprImg.onerror = () => { sprImg._error = true; };
                        window._objImgCache.set(imgUrl, sprImg);
                    }
                    if (sprImg && sprImg.complete && sprImg.naturalWidth > 0) {
                        ctx.drawImage(sprImg, -objW / 2, -objH / 2, objW, objH);
                    } else if (isSelected) {
                        // Subtle emerald placeholder box only when actively selected
                        ctx.fillStyle = ED.accentWash;
                        if (ctx.roundRect) ctx.roundRect(-objW / 2, -objH / 2, objW, objH, 4);
                        else ctx.rect(-objW / 2, -objH / 2, objW, objH);
                        ctx.fill();
                    }
                } else if (obj.is_custom || obj.color) {
                    ctx.fillStyle = obj.color ? (obj.color.length === 7 ? obj.color + '99' : obj.color) : ED.objDefault;
                    if (ctx.roundRect) ctx.roundRect(-objW / 2, -objH / 2, objW, objH, 4);
                    else ctx.rect(-objW / 2, -objH / 2, objW, objH);
                    ctx.fill();
                    ctx.strokeStyle = ED.objLine;
                    ctx.lineWidth = 1;
                    if (ctx.roundRect) ctx.roundRect(-objW / 2, -objH / 2, objW, objH, 4);
                    else ctx.rect(-objW / 2, -objH / 2, objW, objH);
                    ctx.stroke();
                }

                if (isSelected) {
                    ctx.strokeStyle = ED.accent;
                    ctx.lineWidth = 2;
                    ctx.setLineDash([4, 4]);
                    if (ctx.roundRect) ctx.roundRect(-objW / 2 - 2, -objH / 2 - 2, objW + 4, objH + 4, 4);
                    else ctx.rect(-objW / 2 - 2, -objH / 2 - 2, objW + 4, objH + 4);
                    ctx.stroke();
                    ctx.setLineDash([]);

                    // Corner Accent Grab Nodes
                    const hw = objW / 2 + 2;
                    const hh = objH / 2 + 2;
                    const grabPoints = [
                        { x: -hw, y: -hh }, { x: hw, y: -hh },
                        { x: hw, y: hh }, { x: -hw, y: hh }
                    ];
                    grabPoints.forEach(p => {
                        ctx.fillStyle = ED.accent;
                        ctx.beginPath();
                        ctx.arc(p.x, p.y, 3.5, 0, Math.PI * 2);
                        ctx.fill();
                        ctx.strokeStyle = ED.knob;
                        ctx.lineWidth = 1.2;
                        ctx.stroke();
                    });
                }

                ctx.restore();
            });

            // 5. Drawing Box
            if (isDrawing && currentRect) {
                const dx = currentRect.x * TILE_SIZE;
                const dy = currentRect.y * TILE_SIZE;
                const dw = currentRect.width * TILE_SIZE;
                const dh = currentRect.height * TILE_SIZE;

                ctx.fillStyle = ED.accentFill;
                ctx.fillRect(dx, dy, dw, dh);
                ctx.strokeStyle = ED.accent;
                ctx.lineWidth = 2;
                ctx.setLineDash([4, 4]);
                ctx.strokeRect(dx, dy, dw, dh);
                ctx.setLineDash([]);
            }

            ctx.restore();
        }

        // ── Inspector & Property Management ──
        function updateInspector() {
            const emptyBox = document.getElementById('inspector-empty-msg');
            const contentBox = document.getElementById('inspector-content');
            const objFields = document.getElementById('inspector-object-fields');
            const roomFields = document.getElementById('inspector-room-fields');

            if (!selectedItem) {
                emptyBox.style.display = 'block';
                contentBox.style.display = 'none';
                return;
            }

            emptyBox.style.display = 'none';
            contentBox.style.display = 'flex';
            const item = selectedItem.item;

            if (selectedItem.type === 'object') {
                objFields.style.display = 'flex';
                roomFields.style.display = 'none';
                document.getElementById('prop-name').value = item.name || '';
                document.getElementById('prop-width').value = item.width || (item.size ? item.size.width : 1);
                document.getElementById('prop-height').value = item.height || (item.size ? item.size.height : 1);
                const rot = (item.position && typeof item.position.rotation === 'number') ? item.position.rotation : (item.rotation || 0);
                document.querySelectorAll('.rot-btn').forEach(btn => {
                    btn.classList.toggle('active', btn.textContent.includes(`${rot}°`));
                });
                const elevEl = document.getElementById('prop-elevation');
                if (elevEl) elevEl.value = item.elevation || 1;
                const badgeEl = document.getElementById('prop-interaction-badge');
                const iType = item.interaction_type || (item.interaction_config && item.interaction_config.behavior?.type) || (item.type === 'branding' ? 'branding' : (item.type === 'sticky_note' ? 'stickyNote' : (item.type === 'custom_link' ? 'link' : (item.type === 'custom_image' ? 'customImage' : 'none'))));
                if (badgeEl) {
                    badgeEl.textContent = iType.toUpperCase();
                }

                // ── Interactive Media Panels Toggle ──
                const brandingBox = document.getElementById('inspector-branding-box');
                const stickyBox = document.getElementById('inspector-stickynote-box');
                const linkBox = document.getElementById('inspector-link-box');
                const imgBox = document.getElementById('inspector-customimage-box');

                if (brandingBox) brandingBox.style.display = (item.type === 'branding' || iType === 'branding') ? 'flex' : 'none';
                if (stickyBox) stickyBox.style.display = (item.type === 'sticky_note' || iType === 'stickyNote' || iType === 'stickynote') ? 'flex' : 'none';
                if (linkBox) linkBox.style.display = (item.type === 'custom_link' || iType === 'link') ? 'flex' : 'none';
                if (imgBox) imgBox.style.display = (item.type === 'custom_image' || (iType === 'customImage' && item.type !== 'branding')) ? 'flex' : 'none';

                // Populate values
                if (item.type === 'branding' || iType === 'branding') {
                    const bUrlInp = document.getElementById('prop-branding-url');
                    if (bUrlInp) bUrlInp.value = item.image_url || (item.interaction_config?.image_url) || '';
                }
                if (item.type === 'sticky_note' || iType === 'stickyNote' || iType === 'stickynote') {
                    const sTxtInp = document.getElementById('prop-stickynote-text');
                    const noteContent = item.interaction_config?.noteText || item.interaction_config?.behavior?.data?.text || '';
                    if (sTxtInp) sTxtInp.value = noteContent;
                }
                if (item.type === 'custom_link' || iType === 'link') {
                    const lUrlInp = document.getElementById('prop-link-url');
                    const lTitleInp = document.getElementById('prop-link-title');
                    const lNewTabInp = document.getElementById('prop-link-newtab');
                    if (lUrlInp) lUrlInp.value = item.interaction_config?.url || item.interaction_config?.behavior?.data?.url || '';
                    if (lTitleInp) lTitleInp.value = item.interaction_config?.title || item.interaction_config?.behavior?.data?.title || item.name || '';
                    if (lNewTabInp) lNewTabInp.checked = item.interaction_config?.openInNewTab !== false;
                }
                if (item.type === 'custom_image' || (iType === 'customImage' && item.type !== 'branding')) {
                    const imgUrlInp = document.getElementById('prop-customimage-url');
                    if (imgUrlInp) imgUrlInp.value = item.image_url || item.interaction_config?.behavior?.data?.imageUrl || '';
                }
            } else if (selectedItem.type === 'room') {
                objFields.style.display = 'none';
                roomFields.style.display = 'flex';
                item.metadata = item.metadata || {};

                document.getElementById('prop-room-name').value = item.name || '';
                document.getElementById('prop-room-type').value = item.type || 'meeting';
                document.getElementById('prop-room-capacity').value = item.capacity || 10;
                document.getElementById('prop-room-isolation').checked = item.metadata.audio_isolation !== false;

                const bounds = item.bounds || { width: 1, height: 1 };
                document.getElementById('prop-room-bounds-label').textContent = `${bounds.width}×${bounds.height} Tiles (${bounds.width * TILE_SIZE}×${bounds.height * TILE_SIZE}px)`;

                const currentDoorSide = bounds.doorSide || 'auto';
                document.getElementById('prop-room-door-side').value = currentDoorSide;
                const currentDoorOffset = Math.round((typeof bounds.doorOffset === 'number' ? bounds.doorOffset : 0.5) * 100);
                document.getElementById('prop-room-door-offset').value = currentDoorOffset;
                document.getElementById('door-offset-val').textContent = currentDoorOffset + '%';
                syncDoorPicker();
            }
        }

        function updateRoomProp(prop, val) {
            if (!selectedItem || selectedItem.type !== 'room') return;
            const r = selectedItem.item;
            r.metadata = r.metadata || {};
            r.bounds = r.bounds || {};

            if (prop === 'name') r.name = val;
            else if (prop === 'type') r.type = val;
            else if (prop === 'capacity') r.capacity = parseInt(val) || 10;
            else if (prop === 'color') r.color = val;
            else if (prop === 'audio_isolation') r.metadata.audio_isolation = !!val;
            else if (prop === 'doorSide') r.bounds.doorSide = val;
            else if (prop === 'doorOffset') r.bounds.doorOffset = parseFloat(val);

            draw();
        }

        function updateSelectedProp(prop, val) {
            if (!selectedItem) return;
            if (prop === 'name') selectedItem.item.name = val;
            if (prop === 'color') selectedItem.item.color = val;
            if (prop === 'width') selectedItem.item.width = val;
            if (prop === 'height') selectedItem.item.height = val;
            if (prop === 'elevation') selectedItem.item.elevation = parseInt(val) || 1;
            draw();
        }

        // ── Custom Media & Interactive Helpers ──
        function applyOrgLogoToSelected() {
            if (!selectedItem || selectedItem.type !== 'object') return;
            const obj = selectedItem.item;
            if (COMPANY_LOGO_URL) {
                obj.image_url = COMPANY_LOGO_URL;
                if (!obj.interaction_config) obj.interaction_config = {};
                obj.interaction_config.image_url = COMPANY_LOGO_URL;
                obj.interaction_config.use_company_logo = true;
                const input = document.getElementById('prop-branding-url');
                if (input) input.value = COMPANY_LOGO_URL;
                if (window._objImgCache) window._objImgCache.delete(obj.image_url);
                draw();
                showToast('🏢 {{ __("Company logo applied!") }}');
            }
        }

        function updateSelectedLogoUrl(val) {
            if (!selectedItem || selectedItem.type !== 'object') return;
            const obj = selectedItem.item;
            obj.image_url = val;
            if (!obj.interaction_config) obj.interaction_config = {};
            obj.interaction_config.image_url = val;
            if (window._objImgCache) window._objImgCache.delete(val);
            draw();
        }

        function updateSelectedStickyText(val) {
            if (!selectedItem || selectedItem.type !== 'object') return;
            const obj = selectedItem.item;
            if (!obj.interaction_config) obj.interaction_config = {};
            obj.interaction_config.noteText = val;
            if (!obj.interaction_config.behavior) obj.interaction_config.behavior = { type: 'stickyNote' };
            if (!obj.interaction_config.behavior.data) obj.interaction_config.behavior.data = {};
            obj.interaction_config.behavior.data.text = val;
            obj.name = val ? `Sticky: ${val.substring(0, 14)}...` : 'Sticky Note';
            const nameInput = document.getElementById('prop-name');
            if (nameInput) nameInput.value = obj.name;
        }

        const STICKY_COLOR_MAP = {
            yellow: 'https://assets.kumospace.com/furniture/decor/sticky-yellow-01/yella-large_240.png',
            orange: 'https://assets.kumospace.com/furniture/decor/sticky-orange-01/orange-large_240.png',
            purple: 'https://assets.kumospace.com/furniture/decor/sticky-purple-01/purple-large_240.png',
            green: 'https://assets.kumospace.com/furniture/decor/sticky-green-01/green-large_240.png',
            blue: 'https://assets.kumospace.com/furniture/decor/sticky-blue-01/blue-large_240.png'
        };

        function setStickyColor(colorKey) {
            if (!selectedItem || selectedItem.type !== 'object') return;
            const obj = selectedItem.item;
            const imgUrl = STICKY_COLOR_MAP[colorKey] || STICKY_COLOR_MAP.yellow;
            obj.image_url = imgUrl;
            if (!obj.interaction_config) obj.interaction_config = {};
            obj.interaction_config.image_url = imgUrl;
            obj.interaction_config.color = colorKey;
            if (window._objImgCache) window._objImgCache.delete(imgUrl);
            draw();
            showToast('🎨 {{ __("Sticky note color changed!") }}');
        }

        function updateSelectedLinkProp(prop, val) {
            if (!selectedItem || selectedItem.type !== 'object') return;
            const obj = selectedItem.item;
            if (!obj.interaction_config) obj.interaction_config = {};
            if (!obj.interaction_config.behavior) obj.interaction_config.behavior = { type: 'link' };
            if (!obj.interaction_config.behavior.data) obj.interaction_config.behavior.data = {};
            
            if (prop === 'url') {
                obj.interaction_config.url = val;
                obj.interaction_config.behavior.data.url = val;
            } else if (prop === 'title') {
                obj.interaction_config.title = val;
                obj.interaction_config.behavior.data.title = val;
                obj.name = val || 'Custom Link';
                const nameInp = document.getElementById('prop-name');
                if (nameInp) nameInp.value = obj.name;
            } else if (prop === 'openInNewTab') {
                obj.interaction_config.openInNewTab = !!val;
                obj.interaction_config.behavior.data.openInNewTab = !!val;
            }
        }

        function updateSelectedCustomImageUrl(val) {
            if (!selectedItem || selectedItem.type !== 'object') return;
            const obj = selectedItem.item;
            obj.image_url = val;
            if (!obj.interaction_config) obj.interaction_config = {};
            obj.interaction_config.image_url = val;
            if (!obj.interaction_config.behavior) obj.interaction_config.behavior = { type: 'customImage' };
            if (!obj.interaction_config.behavior.data) obj.interaction_config.behavior.data = {};
            obj.interaction_config.behavior.data.imageUrl = val;
            if (window._objImgCache) window._objImgCache.delete(val);
            draw();
        }

        async function uploadObjectImageDirectly(fileInput, targetType) {
            const file = fileInput.files[0];
            if (!file) return;
            showToast('⏳ {{ __("Uploading image...") }}');

            const fd = new FormData();
            fd.append('image', file);
            fd.append('_token', CSRF_TOKEN);

            try {
                const res = await fetch('/editor/upload-object-image', {
                    method: 'POST',
                    body: fd,
                    credentials: 'same-origin'
                });
                const data = await res.json();
                if (data.success && data.url) {
                    if (targetType === 'branding') {
                        updateSelectedLogoUrl(data.url);
                        const inp = document.getElementById('prop-branding-url');
                        if (inp) inp.value = data.url;
                    } else {
                        updateSelectedCustomImageUrl(data.url);
                        const inp = document.getElementById('prop-customimage-url');
                        if (inp) inp.value = data.url;
                    }
                    showToast('✅ {{ __("Image uploaded successfully!") }}');
                } else {
                    showToast('❌ ' + (data.message || '{{ __("Upload failed") }}'));
                }
            } catch (err) {
                console.error(err);
                showToast('❌ {{ __("Failed to upload image.") }}');
            }
        }

        function setRotation(deg) {
            if (!selectedItem || selectedItem.type !== 'object') return;
            const obj = selectedItem.item;
            if (!obj.position) obj.position = { x: 0, y: 0, rotation: 0 };
            obj.position.rotation = deg;
            obj.rotation = deg;
            updateInspector();
            draw();
        }

        function rotateSelectedItem(deg = 90) {
            if (!selectedItem || selectedItem.type !== 'object') return;
            const obj = selectedItem.item;
            if (!obj.position) obj.position = { x: 0, y: 0, rotation: 0 };
            const curRot = typeof obj.position.rotation === 'number' ? obj.position.rotation : (obj.rotation || 0);
            setRotation((curRot + deg) % 360);
        }

        function duplicateSelectedItem() {
            if (!selectedItem || selectedItem.type !== 'object') return;
            const orig = selectedItem.item;
            const cloned = JSON.parse(JSON.stringify(orig));
            delete cloned.id;
            cloned.is_custom = true;
            cloned.name = `${orig.name || 'Object'} (Copy)`;
            const maxTx = Math.floor(MAP_WIDTH_PX / TILE_SIZE);
            const maxTy = Math.floor(MAP_HEIGHT_PX / TILE_SIZE);
            const w = cloned.width || 1;
            const h = cloned.height || 1;
            cloned.position = {
                x: Math.min(maxTx - w, (orig.position ? orig.position.x : 0) + 1),
                y: Math.min(maxTy - h, (orig.position ? orig.position.y : 0) + 1),
                rotation: orig.position?.rotation || orig.rotation || 0
            };
            objects.push(cloned);
            selectedItem = { type: 'object', item: cloned };
            updateInspector();
            updateFloatingActions();
            draw();
            showToast('📋 {{ __("Object cloned!") }}');
        }

        function deleteSelectedItem() {
            if (!selectedItem) return;
            if (selectedItem.type === 'object') {
                const idx = objects.indexOf(selectedItem.item);
                if (idx > -1) objects.splice(idx, 1);
            } else if (selectedItem.type === 'room') {
                const r = selectedItem.item;
                const idx = rooms.indexOf(r);
                if (idx > -1) rooms.splice(idx, 1);
                if (r.id) {
                    fetch(`/editor/rooms/${r.id}`, {
                        method: 'DELETE',
                        headers: { 'X-CSRF-TOKEN': CSRF_TOKEN, 'Accept': 'application/json' },
                        credentials: 'same-origin'
                    }).catch(console.error);
                }
            }
            selectedItem = null;
            updateInspector();
            hideFloatingActions();
            draw();
            showToast('🗑️ {{ __("Item deleted") }}');
        }

        function updateFloatingActions() {
            const floatBox = document.getElementById('floating-actions');
            if (!selectedItem || selectedItem.type !== 'object') {
                floatBox.style.display = 'none';
                return;
            }
            const obj = selectedItem.item;
            const ox = (obj.position ? obj.position.x : 0) * TILE_SIZE;
            const oy = (obj.position ? obj.position.y : 0) * TILE_SIZE;
            const ow = (obj.width || (obj.size ? obj.size.width : 1)) * TILE_SIZE;

            const screenX = ox * zoomLevel + panOffset.x + (ow * zoomLevel) / 2;
            const screenY = oy * zoomLevel + panOffset.y;

            floatBox.style.left = `${screenX}px`;
            floatBox.style.top = `${screenY}px`;
            floatBox.style.display = 'flex';
        }

        function hideFloatingActions() {
            document.getElementById('floating-actions').style.display = 'none';
        }

        function renderRoomsDirectory() {
            const container = document.getElementById('rooms-list-container');
            if (!container) return;
            if (rooms.length === 0) {
                container.innerHTML = `<div style="font-size: 11px; color: var(--ula-text-muted); text-align: center; padding: 16px;">{{ __("No rooms configured yet. Click Add Room to create one.") }}</div>`;
                return;
            }
            let html = '';
            rooms.forEach((r, idx) => {
                html += `
                    <div style="background: var(--ula-surface-card); border: 1px solid var(--ula-border-subtle); border-radius: 8px; padding: 10px; display: flex; align-items: center; justify-content: space-between;">
                        <div style="display: flex; flex-direction: column; gap: 2px;">
                            <strong style="font-size: 12px; color: var(--ula-text-primary);"><span class="material-symbols-rounded" style="font-size: 1em; vertical-align: text-bottom;">apartment</span> ${r.name}</strong>
                            <span style="font-size: 10px; color: var(--ula-text-muted);">${r.type || 'meeting'} · ${r.capacity || 10} seats</span>
                        </div>
                        <button onclick="selectRoomByIndex(${idx})" class="tool-btn" style="padding: 4px 8px; font-size: 11px;"><span class="material-symbols-rounded" style="font-size: 1em; vertical-align: text-bottom;">search</span></button>
                    </div>
                `;
            });
            container.innerHTML = html;
        }

        function selectRoomByIndex(idx) {
            if (rooms[idx]) {
                selectedItem = { type: 'room', item: rooms[idx] };
                switchDrawerTab('inspector');
                updateInspector();
                draw();
            }
        }

        // ── Backend Sync: Save & Publish ──
        async function saveSelectedRoom() {
            if (!selectedItem || selectedItem.type !== 'room') return;
            const r = selectedItem.item;
            showToast('💾 {{ __("Saving room settings...") }}');

            try {
                let isUuid = r.id && typeof r.id === 'string' && r.id.length === 36 && r.id.includes('-');
                let url = isUuid ? `/editor/rooms/${r.id}` : `/editor/rooms`;
                let method = isUuid ? 'PATCH' : 'POST';
                let body = {
                    organization_id: ORG_ID,
                    map_id: MAP_ID,
                    name: r.name || 'Meeting Room',
                    type: r.type || 'meeting',
                    access_mode: r.access_mode || 'public',
                    capacity: parseInt(r.capacity) || 10,
                    color: r.color || '#4F9B5F',
                    bounds: r.bounds || { x: 1, y: 1, width: 8, height: 6 },
                    metadata: r.metadata || {}
                };

                const res = await fetch(url, {
                    method: method,
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF_TOKEN, 'Accept': 'application/json' },
                    credentials: 'same-origin',
                    body: JSON.stringify(body)
                });
                const data = await res.json();
                if (res.ok) {
                    if (data.room && data.room.id) r.id = data.room.id;
                    draw();
                    showToast('✅ {{ __("Room saved successfully!") }}');
                } else {
                    showToast('❌ ' + (data.message || 'Save failed'));
                }
            } catch(e) {
                console.error(e);
                showToast('❌ {{ __("Failed to save room") }}');
            }
        }

        async function saveMapDraft() {
            showToast('💾 {{ __("Saving map draft...") }}');
            try {
                const payload = {
                    name: MAP_DATA.name,
                    layout_data: MAP_DATA.layout_data || {},
                    rooms: rooms.map(r => ({
                        id: r.id,
                        name: r.name,
                        type: r.type || 'meeting',
                        access_mode: r.access_mode || 'public',
                        capacity: parseInt(r.capacity) || 10,
                        color: r.color || '#4F9B5F',
                        bounds: r.bounds,
                        metadata: r.metadata || {}
                    })),
                    objects: objects.map(o => ({
                        type: o.type,
                        name: o.name,
                        position: o.position || { x: 0, y: 0, rotation: 0 },
                        size: { width: o.width || (o.size ? o.size.width : 1), height: o.height || (o.size ? o.size.height : 1) },
                        width: o.width || (o.size ? o.size.width : 1),
                        height: o.height || (o.size ? o.size.height : 1),
                        rotation: o.position?.rotation || o.rotation || 0,
                        color: o.color,
                        image_url: o.image_url || null,
                        is_custom: typeof o.is_custom !== 'undefined' ? o.is_custom : true,
                        collision: typeof o.collision === 'boolean' ? o.collision : true,
                        interaction_config: o.interaction_config || { image_url: o.image_url, is_custom: true }
                    }))
                };

                const res = await fetch(`/editor/maps/${MAP_ID}/save`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF_TOKEN, 'Accept': 'application/json' },
                    credentials: 'same-origin',
                    body: JSON.stringify(payload)
                });
                const data = await res.json();
                if (res.ok) {
                    showToast('✅ {{ __("Draft saved successfully!") }}');
                } else {
                    showToast('❌ ' + (data.message || 'Save failed'));
                }
            } catch(e) {
                console.error(e);
                showToast('❌ {{ __("Failed to save draft") }}');
            }
        }

        async function publishMap() {
            if (!confirm('{{ __("Are you sure you want to publish this map layout to all live office users?") }}')) return;
            showToast('🚀 {{ __("Publishing map...") }}');
            try {
                await saveMapDraft();

                const res = await fetch(`/editor/maps/${MAP_ID}/publish`, {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': CSRF_TOKEN, 'Accept': 'application/json' },
                    credentials: 'same-origin'
                });
                const data = await res.json();
                if (res.ok) {
                    showToast('🎉 {{ __("Map published successfully!") }}');
                    const badge = document.getElementById('header-version-badge');
                    if (badge && data.map) {
                        badge.textContent = `v${data.map.version} (${data.map.status})`;
                    }
                } else {
                    showToast('❌ ' + (data.message || 'Publish failed'));
                }
            } catch(e) {
                console.error(e);
                showToast('❌ {{ __("Publish error") }}');
            }
        }

        // ── Custom Floorplan Background Upload & Clear ──
        function triggerFloorplanUpload() {
            document.getElementById('floorplan-file-input').click();
        }

        async function handleFloorplanUpload(input) {
            if (!input.files || !input.files[0]) return;
            const file = input.files[0];
            const formData = new FormData();
            formData.append('image', file);

            showToast('⏳ {{ __("Uploading floorplan image...") }}');

            try {
                const res = await fetch(`/editor/maps/${MAP_ID}/background`, {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': CSRF_TOKEN, 'Accept': 'application/json' },
                    body: formData,
                    credentials: 'same-origin'
                });
                const data = await res.json();
                if (res.ok && data.image_url) {
                    const newUrl = data.image_url + (data.image_url.includes('?') ? '&' : '?') + 'v=' + Date.now();
                    MAP_DATA.layout_data = MAP_DATA.layout_data || {};
                    MAP_DATA.layout_data.background_image_url = data.image_url;
                    
                    BLUEPRINT_IMAGE.src = newUrl;
                    BLUEPRINT_IMAGE.onload = () => {
                        blueprintLoaded = true;
                        if (BLUEPRINT_IMAGE.naturalWidth > 0 && BLUEPRINT_IMAGE.naturalHeight > 0) {
                            MAP_WIDTH_PX = BLUEPRINT_IMAGE.naturalWidth;
                            MAP_HEIGHT_PX = BLUEPRINT_IMAGE.naturalHeight;
                            MAP_DATA.layout_data.background_width = BLUEPRINT_IMAGE.naturalWidth;
                            MAP_DATA.layout_data.background_height = BLUEPRINT_IMAGE.naturalHeight;
                        }
                        fitAndCenterView();
                        draw();
                    };
                    showToast('✅ {{ __("Floorplan uploaded and active!") }}');
                } else {
                    showToast('❌ ' + (data.message || 'Upload failed'));
                }
            } catch(e) {
                console.error(e);
                showToast('❌ {{ __("Upload error") }}');
            }
        }

        async function deleteFloorplan() {
            if (!confirm('{{ __("Are you sure you want to reset the floorplan to default 1200×708?") }}')) return;
            showToast('🗑️ {{ __("Resetting floorplan...") }}');
            try {
                const res = await fetch(`/editor/maps/${MAP_ID}/background`, {
                    method: 'DELETE',
                    headers: { 'X-CSRF-TOKEN': CSRF_TOKEN, 'Accept': 'application/json' },
                    credentials: 'same-origin'
                });
                const data = await res.json();
                if (res.ok) {
                    MAP_DATA.layout_data = MAP_DATA.layout_data || {};
                    delete MAP_DATA.layout_data.background_image_url;
                    MAP_DATA.layout_data.background_width = 1200;
                    MAP_DATA.layout_data.background_height = 708;
                    blueprintLoaded = false;
                    BLUEPRINT_IMAGE.removeAttribute('src');
                    BLUEPRINT_IMAGE.src = '';
                    MAP_WIDTH_PX = 1200;
                    MAP_HEIGHT_PX = 708;
                    fitAndCenterView();
                    renderFloorsCatalog();
                    draw();
                    showToast('✅ {{ __("Floorplan reset to default (1200×708)!") }}');
                } else {
                    showToast('❌ ' + (data.message || 'Reset failed'));
                }
            } catch(e) {
                console.error(e);
                showToast('❌ {{ __("Failed to delete floorplan") }}');
            }
        }

        async function clearWorkspace() {
            if (!confirm('{{ __("Are you sure you want to clear all furniture and reset the canvas for a fresh layout?") }}')) return;
            showToast('🧹 {{ __("Clearing canvas...") }}');
            try {
                const res = await fetch(`/editor/maps/${MAP_ID}/clear`, {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': CSRF_TOKEN, 'Accept': 'application/json' },
                    credentials: 'same-origin'
                });
                const data = await res.json();
                if (res.ok) {
                    objects.length = 0;
                    selectedItem = null;
                    MAP_DATA.layout_data = MAP_DATA.layout_data || {};
                    delete MAP_DATA.layout_data.background_image_url;
                    MAP_DATA.layout_data.background_width = 1200;
                    MAP_DATA.layout_data.background_height = 708;
                    BLUEPRINT_IMAGE.removeAttribute('src');
                    BLUEPRINT_IMAGE.src = '';
                    blueprintLoaded = false;
                    MAP_WIDTH_PX = 1200;
                    MAP_HEIGHT_PX = 708;
                    fitAndCenterView();
                    updateInspector();
                    hideFloatingActions();
                    renderFloorsCatalog();
                    draw();
                    showToast('✨ {{ __("Canvas cleared! You can now upload a new floorplan and place rooms/furniture.") }}');
                } else {
                    showToast('❌ ' + (data.message || 'Clear failed'));
                }
            } catch(e) {
                console.error(e);
                showToast('❌ {{ __("Failed to clear canvas") }}');
            }
        }

        // ── Dropdown & Menu Handlers ──
        function toggleEditorMainMenu(e) {
            if (e) e.stopPropagation();
            const menu = document.getElementById('editor-main-menu-dropdown');
            const branchDD = document.getElementById('branch-select-dropdown');
            if (branchDD) branchDD.style.display = 'none';
            if (menu) {
                menu.style.display = (menu.style.display === 'none' || menu.style.display === '') ? 'block' : 'none';
            }
        }

        function closeEditorMainMenu() {
            const menu = document.getElementById('editor-main-menu-dropdown');
            if (menu) menu.style.display = 'none';
        }

        function toggleBranchDropdown(e) {
            if (e) e.stopPropagation();
            const menu = document.getElementById('editor-main-menu-dropdown');
            if (menu) menu.style.display = 'none';
            const dd = document.getElementById('branch-select-dropdown');
            if (dd) {
                dd.style.display = (dd.style.display === 'none' || dd.style.display === '') ? 'block' : 'none';
            }
        }

        function closeDropdowns() {
            const d1 = document.getElementById('branch-select-dropdown');
            const d2 = document.getElementById('editor-main-menu-dropdown');
            if (d1) d1.style.display = 'none';
            if (d2) d2.style.display = 'none';
        }

        function toggleAppTheme() {
            const cur = document.documentElement.getAttribute('data-theme') || (document.documentElement.classList.contains('dark') ? 'dark' : 'light');
            const next = cur === 'dark' ? 'light' : 'dark';
            document.documentElement.setAttribute('data-theme', next);
            if (next === 'dark') {
                document.documentElement.classList.add('dark');
                if (document.body) document.body.classList.add('dark-mode');
            } else {
                document.documentElement.classList.remove('dark');
                if (document.body) document.body.classList.remove('dark-mode');
            }
            localStorage.setItem('vw_theme', next);
            refreshEditorPalette();
            if (typeof draw === 'function') draw();
            showToast(next === 'dark' ? '{{ __("Dark mode active") }}' : '{{ __("Light mode active") }}');
        }

        document.addEventListener('click', (e) => {
            const menu = document.getElementById('editor-main-menu-dropdown');
            if (menu && menu.style.display === 'block') {
                if (!e.target.closest('#editor-main-menu-dropdown') && !e.target.closest('button[onclick*="toggleEditorMainMenu"]') && !e.target.closest('.nx-brand-capsule')) {
                    menu.style.display = 'none';
                }
            }
            const branchDD = document.getElementById('branch-select-dropdown');
            if (branchDD && branchDD.style.display === 'block') {
                if (!e.target.closest('#branch-select-dropdown') && !e.target.closest('button[onclick*="toggleBranchDropdown"]')) {
                    branchDD.style.display = 'none';
                }
            }
        });

        // Notification toast. Callers still prefix messages with an emoji; it only picks the tone and is stripped.
        let toastTimer = null;
        function showToast(msg) {
            const t = document.getElementById('toast-bubble');
            if (!t) return;
            const raw = String(msg || '').trim();
            let tone = 'info', icon = 'info';
            if (/^(✅|🎉)/u.test(raw)) { tone = 'ok'; icon = 'check_circle'; }
            else if (/^❌/u.test(raw)) { tone = 'error'; icon = 'error'; }
            else if (/^(⏳|💾|🚀|🧹)/u.test(raw)) { tone = 'busy'; icon = 'progress_activity'; }
            else if (/^🗑/u.test(raw)) { tone = 'info'; icon = 'delete'; }
            const text = raw.replace(/[\p{Extended_Pictographic}‍️]+\s*/gu, '').trim();

            const iconBox = document.createElement('span');
            iconBox.className = 'toast-icon';
            const glyph = document.createElement('span');
            glyph.className = 'material-symbols-rounded';
            glyph.setAttribute('aria-hidden', 'true');
            glyph.textContent = icon;
            iconBox.append(glyph);
            const label = document.createElement('span');
            label.className = 'toast-text';
            label.textContent = text;

            t.dataset.tone = tone;
            t.setAttribute('role', tone === 'error' ? 'alert' : 'status');
            t.replaceChildren(iconBox, label);
            t.classList.remove('show');
            void t.offsetWidth; // restart the entry animation
            t.classList.add('show');
            // One timer: a newer toast must not be hidden by an older one's timeout.
            clearTimeout(toastTimer);
            toastTimer = setTimeout(() => t.classList.remove('show'), tone === 'error' ? 5000 : 3200);
        }

        // Door picker in the room inspector
        const DOOR_SIDE_LABELS = {
            auto: @json(__('Auto Corridor')),
            top: @json(__('Top Wall')),
            bottom: @json(__('Bottom Wall')),
            left: @json(__('Left Wall')),
            right: @json(__('Right Wall')),
        };
        function syncDoorPicker() {
            const side = document.getElementById('prop-room-door-side')?.value || 'auto';
            const offset = Number(document.getElementById('prop-room-door-offset')?.value || 50);
            const room = (selectedItem && selectedItem.type === 'room') ? selectedItem.item : null;
            document.querySelectorAll('.ed-door-btn').forEach(b => {
                const on = b.dataset.side === side;
                const blocked = doorSideBlocked(room, b.dataset.side, offset / 100);
                b.classList.toggle('active', on);
                b.classList.toggle('blocked', blocked);
                b.setAttribute('aria-checked', on ? 'true' : 'false');
                b.setAttribute('aria-disabled', blocked ? 'true' : 'false');
            });
            const mark = document.getElementById('door-picker-mark');
            if (mark) {
                // 'auto' draws on the bottom wall (see draw()), so preview it there.
                const drawn = side === 'auto' ? autoDoorSide(room) : side;
                mark.dataset.side = drawn;
                mark.style.left = (drawn === 'top' || drawn === 'bottom') ? offset + '%' : '';
                mark.style.top = (drawn === 'left' || drawn === 'right') ? offset + '%' : '';
            }
            const caption = document.getElementById('door-picker-caption');
            if (caption) {
                const bad = doorSideBlocked(room, side, offset / 100);
                caption.textContent = bad ? @json(__('This wall is shared with another room — choose a wall that opens onto free floor.')) : (DOOR_SIDE_LABELS[side] || side);
                caption.classList.toggle('is-warning', bad);
            }
        }
        function setDoorSide(side) {
            const room = (selectedItem && selectedItem.type === 'room') ? selectedItem.item : null;
            const offset = Number(document.getElementById('prop-room-door-offset')?.value || 50) / 100;
            if (doorSideBlocked(room, side, offset)) {
                showToast('❌ ' + @json(__('This wall is shared with another room — choose a wall that opens onto free floor.')));
                return;
            }
            const input = document.getElementById('prop-room-door-side');
            if (input) input.value = side;
            updateRoomProp('doorSide', side);
            syncDoorPicker();
        }


        // ── AI Workplace Generator Logic ──
        const PLAN_ROOM_LIMIT = {{ $plan && $plan->room_limit > 0 ? $plan->room_limit : 9999 }};
        const PLAN_SEAT_LIMIT = {{ $plan && $plan->seat_limit > 0 ? $plan->seat_limit : 9999 }};

        function openAiGeneratorModal() {
            calculateAiQuotas();
            document.getElementById('ai-generator-modal').style.display = 'flex';
        }

        function closeAiGeneratorModal() {
            document.getElementById('ai-generator-modal').style.display = 'none';
        }

        function selectAiStyle(styleKey) {
            document.querySelectorAll('.ai-style-card').forEach(el => {
                el.classList.remove('active');
                el.style.borderColor = 'var(--ula-border-subtle)';
                el.style.background = 'var(--ula-surface-card)';
            });
            const sel = document.getElementById('ai-style-' + styleKey);
            if (sel) {
                sel.classList.add('active');
                sel.style.borderColor = 'var(--ula-accent-default)';
                sel.style.background = 'var(--ula-surface-accent-soft)';
            }
            const radio = document.querySelector(`input[name="ai_style"][value="${styleKey}"]`);
            if (radio) radio.checked = true;
        }

        function changeAiCounter(fieldId, delta, minVal, maxVal) {
            const inp = document.getElementById(fieldId);
            if (!inp) return;
            let val = parseInt(inp.value) || 0;
            val = Math.max(minVal, Math.min(maxVal, val + delta));
            inp.value = val;
            calculateAiQuotas();
        }

        function getAiFieldValue(id, fallback = 0) {
            const el = document.getElementById(id);
            if (!el) return fallback;
            const parsed = parseInt(el.value, 10);
            return isNaN(parsed) ? fallback : parsed;
        }

        function calculateAiQuotas() {
            const meeting = getAiFieldValue('ai-inp-meeting', 0);
            const office = getAiFieldValue('ai-inp-office', 1);
            const desks = getAiFieldValue('ai-inp-desks', 1);
            const thinking = getAiFieldValue('ai-inp-thinking', 0);
            const rest = getAiFieldValue('ai-inp-rest', 0);
            const theater = getAiFieldValue('ai-inp-theater', 0);

            // Only count actual custom rooms selected by the user
            const totalRooms = meeting + office + thinking + rest + theater;
            // Only count team office workstations/desks
            const totalDesks = (office * desks);

            const roomBadge = document.getElementById('ai-quota-rooms-val');
            const seatBadge = document.getElementById('ai-quota-seats-val');
            const quotaWarning = document.getElementById('ai-quota-warning-box');
            const generateBtn = document.getElementById('btn-ai-submit-generate');

            if (roomBadge) {
                roomBadge.textContent = `${totalRooms} / ${PLAN_ROOM_LIMIT < 9999 ? PLAN_ROOM_LIMIT : '∞'}`;
                roomBadge.style.color = (PLAN_ROOM_LIMIT < 9999 && totalRooms > PLAN_ROOM_LIMIT) ? 'var(--ula-status-danger)' : 'var(--ula-status-success)';
            }

            if (seatBadge) {
                seatBadge.textContent = `${totalDesks} / ${PLAN_SEAT_LIMIT < 9999 ? PLAN_SEAT_LIMIT : '∞'}`;
                seatBadge.style.color = (PLAN_SEAT_LIMIT < 9999 && totalDesks > PLAN_SEAT_LIMIT) ? 'var(--ula-status-danger)' : 'var(--ula-accent-default)';
            }

            let hasError = false;
            let errorMsg = '';

            if (PLAN_ROOM_LIMIT < 9999 && totalRooms > PLAN_ROOM_LIMIT) {
                hasError = true;
                errorMsg = `{{ __('Total rooms (:total) exceed your plan limit (:limit). Please reduce room count or upgrade plan.', ['total' => '__TOTAL__', 'limit' => '__LIMIT__']) }}`
                    .replace('__TOTAL__', totalRooms)
                    .replace('__LIMIT__', PLAN_ROOM_LIMIT);
            } else if (PLAN_SEAT_LIMIT < 9999 && totalDesks > PLAN_SEAT_LIMIT) {
                hasError = true;
                errorMsg = `{{ __('Total office desks (:total) exceed your subscription capacity (:limit seats). Please reduce desk count or offices.', ['total' => '__TOTAL__', 'limit' => '__LIMIT__']) }}`
                    .replace('__TOTAL__', totalDesks)
                    .replace('__LIMIT__', PLAN_SEAT_LIMIT);
            }

            if (quotaWarning) {
                if (hasError) {
                    quotaWarning.style.display = 'block';
                    quotaWarning.innerHTML = `<span class="material-symbols-rounded" style="font-size: 1em; vertical-align: text-bottom;">warning</span> ${errorMsg}`;
                    generateBtn.disabled = true;
                    generateBtn.style.opacity = '0.5';
                    generateBtn.style.cursor = 'not-allowed';
                } else {
                    quotaWarning.style.display = 'none';
                    generateBtn.disabled = false;
                    generateBtn.style.opacity = '1';
                    generateBtn.style.cursor = 'pointer';
                }
            }
        }

        async function generateAiOfficeOnCanvas() {
            const styleRadio = document.querySelector('input[name="ai_style"]:checked');
            const styleKey = styleRadio ? styleRadio.value : 'modern_glass_luxury';
            const meeting = getAiFieldValue('ai-inp-meeting', 0);
            const office = getAiFieldValue('ai-inp-office', 1);
            const desks = getAiFieldValue('ai-inp-desks', 1);
            const thinking = getAiFieldValue('ai-inp-thinking', 0);
            const rest = getAiFieldValue('ai-inp-rest', 0);
            const theater = getAiFieldValue('ai-inp-theater', 0);

            const modalContent = document.getElementById('ai-modal-form-content');
            const loadingBox = document.getElementById('ai-modal-loading-box');
            const statusStep = document.getElementById('ai-loading-step-text');

            modalContent.style.display = 'none';
            loadingBox.style.display = 'flex';

            const steps = [
                '🧠 {{ __("Analyzing room requirements & architectural parameters...") }}',
                '🎨 {{ __("Calling OpenAI DALL-E 3 to generate 3D isometric blueprint...") }}',
                '📐 {{ __("Computing geometric spatial partitions & room isolation boundaries...") }}',
                '🚀 {{ __("Mapping isolated rooms, collision zones, and canvas layout...") }}'
            ];

            let stepIdx = 0;
            statusStep.textContent = steps[stepIdx];
            const interval = setInterval(() => {
                stepIdx = (stepIdx + 1) % steps.length;
                statusStep.textContent = steps[stepIdx];
            }, 3500);

            try {
                const res = await fetch('/organization/ai-map/generate', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': CSRF_TOKEN
                    },
                    credentials: 'same-origin',
                    body: JSON.stringify({
                        target_floor_id: '{{ $floor->id }}',
                        style: styleKey,
                        meeting_rooms: meeting,
                        office_rooms: office,
                        desks_per_office: desks,
                        thinking_rooms: thinking,
                        rest_areas: rest,
                        theaters: theater
                    })
                });

                clearInterval(interval);

                const data = await res.json();
                if (!res.ok || !data.success) {
                    throw new Error(data.message || 'AI Generation failed.');
                }

                // Apply new background image
                if (data.background_image_url) {
                    MAP_DATA.layout_data = MAP_DATA.layout_data || {};
                    MAP_DATA.layout_data.background_image_url = data.background_image_url;
                    BLUEPRINT_IMAGE.src = data.background_image_url + (data.background_image_url.includes('?') ? '&' : '?') + 'v=' + Date.now();
                }

                selectedItem = null;
                updateInspector();
                hideFloatingActions();
                draw();

                closeAiGeneratorModal();
                modalContent.style.display = 'block';
                loadingBox.style.display = 'none';

                showToast('✨ ' + (data.message || '{{ __("AI Virtual Office floorplan generated successfully!") }}'));
            } catch (err) {
                clearInterval(interval);
                modalContent.style.display = 'block';
                loadingBox.style.display = 'none';
                alert('⚠️ ' + (err.message || 'An error occurred during AI generation.'));
            }
        }

        // Initial draw
        draw();
    </script>

    <!-- ── AI Office & Floorplan Generator Modal ── -->
    <div id="ai-generator-modal" style="display: none; position: fixed; inset: 0; background: var(--ula-surface-overlay); backdrop-filter: blur(14px); z-index: 99999; align-items: center; justify-content: center; padding: 20px;">
        <div style="background: var(--ula-surface-page); border: 1px solid var(--ula-border-subtle); border-radius: var(--ula-radius-xl); width: 100%; max-width: 820px; max-height: 90vh; overflow-y: auto; box-shadow: var(--ula-shadow-xl); display: flex; flex-direction: column;">
            
            <!-- Modal Header -->
            <div style="padding: 22px 26px; border-bottom: 1px solid var(--ula-border-subtle); display: flex; justify-content: space-between; align-items: center; background: var(--ula-surface-card);">
                <div style="display: flex; align-items: center; gap: 12px;">
                    <div style="width: 42px; height: 42px; border-radius: 12px; background: var(--ula-accent-default); color: var(--ula-accent-fg); display: flex; align-items: center; justify-content: center; font-size: 20px; box-shadow: 0 4px 12px var(--ula-border-subtle);">
                        <span class="material-symbols-rounded" style="font-size: 1em; vertical-align: text-bottom;">auto_awesome</span>
                    </div>
                    <div>
                        <h2 style="font-size: 17px; font-weight: 900; color: var(--ula-text-primary); margin-bottom: 2px;">
                            {{ __('AI Virtual Office & Blueprint Generator') }}
                        </h2>
                        <p style="font-size: 12px; color: var(--ula-text-muted);">
                            {{ __('Generate bespoke 3D isometric floorplans using OpenAI DALL-E 3 with automatic room isolation.') }}
                        </p>
                    </div>
                </div>
                <button type="button" onclick="closeAiGeneratorModal()" style="background: none; border: none; color: var(--ula-text-muted); font-size: 22px; cursor: pointer; padding: 4px;"><span class="material-symbols-rounded" style="font-size: 1em; vertical-align: text-bottom;">close</span></button>
            </div>

            <!-- Loading State Overlay -->
            <div id="ai-modal-loading-box" style="display: none; flex-direction: column; align-items: center; justify-content: center; padding: 60px 30px; text-align: center; gap: 18px;">
                <div style="width: 64px; height: 64px; border: 4px solid var(--ula-border-subtle); border-top-color: var(--ula-status-success); border-radius: 50%; animation: spin 1s linear infinite;"></div>
                <h3 style="font-size: 18px; font-weight: 900; color: var(--ula-text-primary);">
                    <span class="material-symbols-rounded" style="font-size: 1em; vertical-align: text-bottom;">auto_awesome</span> {{ __('Generating 3D Isometric Office Blueprint...') }}
                </h3>
                <div id="ai-loading-step-text" style="font-size: 13px; color: var(--ula-status-success); font-weight: 700; max-width: 480px;">
                    <span class="material-symbols-rounded" style="font-size: 1em; vertical-align: text-bottom;">psychology</span> {{ __('Analyzing room requirements & architectural parameters...') }}
                </div>
                <p style="font-size: 11px; color: var(--ula-text-muted); max-width: 420px;">
                    {{ __('DALL-E 3 creates high-definition architectural renders. This process usually takes between 15 to 30 seconds.') }}
                </p>
            </div>

            <!-- Form Content -->
            <div id="ai-modal-form-content" style="padding: 24px 26px; display: flex; flex-direction: column; gap: 20px;">
                
                <!-- Plan Quota Header Pill Card -->
                <div style="background: var(--ula-surface-card); border: 1px solid var(--ula-border-subtle); border-radius: var(--ula-radius-lg); padding: 14px 18px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px;">
                    <div>
                        <span style="font-size: 10px; font-weight: 800; text-transform: uppercase; color: var(--ula-text-muted); display: block;">{{ __('Active Subscription Tier') }}</span>
                        <strong style="font-size: 14px; color: var(--ula-text-primary);"><span class="material-symbols-rounded" style="font-size: 1em; vertical-align: text-bottom;">star</span> {{ $plan->name ?? 'Standard Plan' }}</strong>
                    </div>
                    <div style="display: flex; gap: 16px; align-items: center;">
                        <div style="text-align: center;">
                            <span style="font-size: 10px; color: var(--ula-text-muted); display: block;"><span class="material-symbols-rounded" style="font-size: 1em; vertical-align: text-bottom;">apartment</span> {{ __('Total Rooms') }}</span>
                            <span id="ai-quota-rooms-val" style="font-size: 14px; font-weight: 900; color: var(--ula-status-success);">0 / ∞</span>
                        </div>
                        <div style="width: 1px; height: 26px; background: var(--ula-border-subtle);"></div>
                        <div style="text-align: center;">
                            <span style="font-size: 10px; color: var(--ula-text-muted); display: block;"><span class="material-symbols-rounded" style="font-size: 1em; vertical-align: text-bottom;">desktop_windows</span> {{ __('Total Workstations / Desks') }}</span>
                            <span id="ai-quota-seats-val" style="font-size: 14px; font-weight: 900; color: var(--ula-accent-default);">0 / ∞</span>
                        </div>
                    </div>
                </div>

                <!-- 1. Architectural Style Selection -->
                <div>
                    <label style="display: block; font-size: 12px; font-weight: 800; color: var(--ula-text-primary); margin-bottom: 10px;">
                        <span class="material-symbols-rounded" style="font-size: 1em; vertical-align: text-bottom;">palette</span> {{ __('1. Choose Office Architectural Style') }}
                    </label>
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 10px;">
                        @foreach($aiStyles as $key => $style)
                        <div class="ai-style-card {{ $loop->first ? 'active' : '' }}" id="ai-style-{{ $key }}" onclick="selectAiStyle('{{ $key }}')" style="background: {{ $loop->first ? 'var(--ula-surface-accent-soft)' : 'var(--ula-surface-card)' }}; border: 1px solid {{ $loop->first ? 'var(--ula-accent-default)' : 'var(--ula-border-subtle)' }}; border-radius: var(--ula-radius-sm); padding: 12px; cursor: pointer; transition: all 0.2s ease;">
                            <label style="display: flex; align-items: flex-start; gap: 8px; cursor: pointer;">
                                <input type="radio" name="ai_style" value="{{ $key }}" {{ $loop->first ? 'checked' : '' }} style="margin-top: 3px; accent-color: var(--ula-accent-default);">
                                <div>
                                    <strong style="font-size: 12px; color: var(--ula-text-primary); display: block;">{{ $style['name'] }}</strong>
                                    <span style="font-size: 10px; color: var(--ula-text-muted); line-height: 1.3; display: block; margin-top: 2px;">{{ $style['name_ar'] }}</span>
                                </div>
                            </label>
                        </div>
                        @endforeach
                    </div>
                </div>

                <!-- 2. Room Breakdown & Desks Steppers -->
                <div>
                    <label style="display: block; font-size: 12px; font-weight: 800; color: var(--ula-text-primary); margin-bottom: 10px;">
                        <span class="material-symbols-rounded" style="font-size: 1em; vertical-align: text-bottom;">apartment</span> {{ __('2. Customize Room Quantities & Desk Counts') }}
                    </label>

                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(230px, 1fr)); gap: 12px;">
                        
                        <!-- Meeting Rooms -->
                        <div style="background: var(--ula-surface-card); border: 1px solid var(--ula-border-subtle); border-radius: var(--ula-radius-sm); padding: 12px;">
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                                <div>
                                    <strong style="font-size: 12px; color: var(--ula-tone-terracotta-fg); display: block;"><span class="material-symbols-rounded" style="font-size: 1em; vertical-align: text-bottom;">apartment</span> {{ __('Meeting Boardrooms') }}</strong>
                                    <span style="font-size: 10px; color: var(--ula-text-muted);">{{ __('غرف اجتماعات زجاجية') }}</span>
                                </div>
                                <div style="display: flex; align-items: center; gap: 6px;">
                                    <button type="button" onclick="changeAiCounter('ai-inp-meeting', -1, 0, 6)" class="tactile-btn" style="width: 26px; height: 26px; padding: 0; display: flex; align-items: center; justify-content: center; font-weight: 900;">-</button>
                                    <input type="text" id="ai-inp-meeting" value="1" readonly style="width: 32px; text-align: center; background: none; border: none; font-weight: 800; color: var(--ula-text-primary); font-size: 13px;">
                                    <button type="button" onclick="changeAiCounter('ai-inp-meeting', 1, 0, 6)" class="tactile-btn" style="width: 26px; height: 26px; padding: 0; display: flex; align-items: center; justify-content: center; font-weight: 900;">+</button>
                                </div>
                            </div>
                        </div>

                        <!-- Team Offices & Desks -->
                        <div style="background: var(--ula-surface-card); border: 1px solid var(--ula-border-subtle); border-radius: var(--ula-radius-sm); padding: 12px;">
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 6px;">
                                <div>
                                    <strong style="font-size: 12px; color: var(--ula-accent-default); display: block;"><span class="material-symbols-rounded" style="font-size: 1em; vertical-align: text-bottom;">business_center</span> {{ __('Team Offices') }}</strong>
                                    <span style="font-size: 10px; color: var(--ula-text-muted);">{{ __('مكاتب عمل جماعية/فردية') }}</span>
                                </div>
                                <div style="display: flex; align-items: center; gap: 6px;">
                                    <button type="button" onclick="changeAiCounter('ai-inp-office', -1, 1, 8)" class="tactile-btn" style="width: 26px; height: 26px; padding: 0; display: flex; align-items: center; justify-content: center; font-weight: 900;">-</button>
                                    <input type="text" id="ai-inp-office" value="1" readonly style="width: 32px; text-align: center; background: none; border: none; font-weight: 800; color: var(--ula-text-primary); font-size: 13px;">
                                    <button type="button" onclick="changeAiCounter('ai-inp-office', 1, 1, 8)" class="tactile-btn" style="width: 26px; height: 26px; padding: 0; display: flex; align-items: center; justify-content: center; font-weight: 900;">+</button>
                                </div>
                            </div>
                            <div style="display: flex; justify-content: space-between; align-items: center; border-top: 1px dashed var(--ula-border-subtle); padding-top: 6px; margin-top: 4px;">
                                <span style="font-size: 10px; color: var(--ula-text-muted);"><span class="material-symbols-rounded" style="font-size: 1em; vertical-align: text-bottom;">desktop_windows</span> {{ __('Desks per office') }}:</span>
                                <div style="display: flex; align-items: center; gap: 4px;">
                                    <button type="button" onclick="changeAiCounter('ai-inp-desks', -1, 1, 12)" class="tactile-btn" style="width: 22px; height: 22px; padding: 0; display: flex; align-items: center; justify-content: center; font-size: 10px;">-</button>
                                    <input type="text" id="ai-inp-desks" value="2" readonly style="width: 24px; text-align: center; background: none; border: none; font-weight: 800; color: var(--ula-text-primary); font-size: 11px;">
                                    <button type="button" onclick="changeAiCounter('ai-inp-desks', 1, 1, 12)" class="tactile-btn" style="width: 22px; height: 22px; padding: 0; display: flex; align-items: center; justify-content: center; font-size: 10px;">+</button>
                                </div>
                            </div>
                        </div>

                        <!-- Thinking & Focus Pods -->
                        <div style="background: var(--ula-surface-card); border: 1px solid var(--ula-border-subtle); border-radius: var(--ula-radius-sm); padding: 12px;">
                            <div style="display: flex; justify-content: space-between; align-items: center;">
                                <div>
                                    <strong style="font-size: 12px; color: var(--ula-accent-default); display: block;"><span class="material-symbols-rounded" style="font-size: 1em; vertical-align: text-bottom;">lightbulb</span> {{ __('Thinking / Focus Pods') }}</strong>
                                    <span style="font-size: 10px; color: var(--ula-text-muted);">{{ __('غرف التركيز والعصف الذهني') }}</span>
                                </div>
                                <div style="display: flex; align-items: center; gap: 6px;">
                                    <button type="button" onclick="changeAiCounter('ai-inp-thinking', -1, 0, 4)" class="tactile-btn" style="width: 26px; height: 26px; padding: 0; display: flex; align-items: center; justify-content: center; font-weight: 900;">-</button>
                                    <input type="text" id="ai-inp-thinking" value="0" readonly style="width: 32px; text-align: center; background: none; border: none; font-weight: 800; color: var(--ula-text-primary); font-size: 13px;">
                                    <button type="button" onclick="changeAiCounter('ai-inp-thinking', 1, 0, 4)" class="tactile-btn" style="width: 26px; height: 26px; padding: 0; display: flex; align-items: center; justify-content: center; font-weight: 900;">+</button>
                                </div>
                            </div>
                        </div>

                        <!-- Rest & Gaming Lounge -->
                        <div style="background: var(--ula-surface-card); border: 1px solid var(--ula-border-subtle); border-radius: var(--ula-radius-sm); padding: 12px;">
                            <div style="display: flex; justify-content: space-between; align-items: center;">
                                <div>
                                    <strong style="font-size: 12px; color: var(--ula-tone-terracotta-fg); display: block;"><span class="material-symbols-rounded" style="font-size: 1em; vertical-align: text-bottom;">weekend</span> {{ __('Rest & Gaming Lounge') }}</strong>
                                    <span style="font-size: 10px; color: var(--ula-text-muted);">{{ __('صالة الاستراحة والترفيه') }}</span>
                                </div>
                                <div style="display: flex; align-items: center; gap: 6px;">
                                    <button type="button" onclick="changeAiCounter('ai-inp-rest', -1, 0, 3)" class="tactile-btn" style="width: 26px; height: 26px; padding: 0; display: flex; align-items: center; justify-content: center; font-weight: 900;">-</button>
                                    <input type="text" id="ai-inp-rest" value="0" readonly style="width: 32px; text-align: center; background: none; border: none; font-weight: 800; color: var(--ula-text-primary); font-size: 13px;">
                                    <button type="button" onclick="changeAiCounter('ai-inp-rest', 1, 0, 3)" class="tactile-btn" style="width: 26px; height: 26px; padding: 0; display: flex; align-items: center; justify-content: center; font-weight: 900;">+</button>
                                </div>
                            </div>
                        </div>

                        <!-- Presentation Theater / Auditorium -->
                        <div style="background: var(--ula-surface-card); border: 1px solid var(--ula-border-subtle); border-radius: var(--ula-radius-sm); padding: 12px;">
                            <div style="display: flex; justify-content: space-between; align-items: center;">
                                <div>
                                    <strong style="font-size: 12px; color: var(--ula-status-danger); display: block;"><span class="material-symbols-rounded" style="font-size: 1em; vertical-align: text-bottom;">theater_comedy</span> {{ __('Presentation Theater') }}</strong>
                                    <span style="font-size: 10px; color: var(--ula-text-muted);">{{ __('مسرح وقاعة عروض ومؤتمرات') }}</span>
                                </div>
                                <div style="display: flex; align-items: center; gap: 6px;">
                                    <button type="button" onclick="changeAiCounter('ai-inp-theater', -1, 0, 2)" class="tactile-btn" style="width: 26px; height: 26px; padding: 0; display: flex; align-items: center; justify-content: center; font-weight: 900;">-</button>
                                    <input type="text" id="ai-inp-theater" value="0" readonly style="width: 32px; text-align: center; background: none; border: none; font-weight: 800; color: var(--ula-text-primary); font-size: 13px;">
                                    <button type="button" onclick="changeAiCounter('ai-inp-theater', 1, 0, 2)" class="tactile-btn" style="width: 26px; height: 26px; padding: 0; display: flex; align-items: center; justify-content: center; font-weight: 900;">+</button>
                                </div>
                            </div>
                        </div>

                        <!-- Default Amenities Card -->
                        <div style="background: var(--ula-surface-accent-soft); border: 1px dashed var(--ula-border-subtle); border-radius: var(--ula-radius-sm); padding: 12px; display: flex; flex-direction: column; justify-content: center;">
                            <strong style="font-size: 11px; color: var(--ula-status-success); display: flex; align-items: center; gap: 6px;">
                                <span><span class="material-symbols-rounded" style="font-size: 1em; vertical-align: text-bottom;">coffee</span></span> {{ __('Coffee Corner & Reception') }}
                            </strong>
                            <span style="font-size: 10px; color: var(--ula-text-muted); margin-top: 2px;">
                                <span class="material-symbols-rounded" style="font-size: 1em; vertical-align: text-bottom;">check</span> {{ __('Always included automatically in every floorplan') }}
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Live Quota Warning Box -->
                <div id="ai-quota-warning-box" style="display: none; background: var(--ula-tone-terracotta-bg); border: 1px solid var(--ula-border-danger); border-radius: 10px; padding: 12px 16px; font-size: 12px; color: var(--ula-status-danger); font-weight: 700;"></div>

                <!-- Action Buttons -->
                <div style="display: flex; justify-content: space-between; align-items: center; padding-top: 8px; border-top: 1px solid var(--ula-border-subtle);">
                    <x-btn variant="secondary" size="md" onclick="closeAiGeneratorModal()">{{ __('Cancel') }}</x-btn>
                    <x-btn variant="primary" size="md" icon="auto_awesome" id="btn-ai-submit-generate" onclick="generateAiOfficeOnCanvas()">{{ __('Generate Office with AI') }}</x-btn>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
