<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Virtual Workplace — UlaSpace Digital Workspace">
    <title>@yield('title', __('Virtual Workplace'))</title>
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
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;500;600;700&family=IBM+Plex+Mono:wght@400;500&family=IBM+Plex+Sans:wght@300;400;500;600&family=Material+Symbols+Rounded:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/ulaspace-tokens.css') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        *, *::before, *::after {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            background-color: var(--ula-surface-page);
            color: var(--ula-text-primary);
            font-family: var(--ula-font-ar);
            min-height: 100vh;
            -webkit-font-smoothing: antialiased;
        }

        [dir="ltr"] body {
            font-family: var(--ula-font-en);
        }

        .ms {
            font-family: 'Material Symbols Rounded';
            font-weight: 400;
            font-style: normal;
            line-height: 1;
            letter-spacing: normal;
            text-transform: none;
            display: inline-block;
            white-space: nowrap;
            direction: ltr;
            font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24;
        }

        /* ── Auth Master Container ──
           The reference screens (02/03) show this filling the entire browser
           viewport edge-to-edge -- the rounded-corner/shadow/centered "card"
           look in the design-reference mockup is that tool's own canvas frame
           for displaying numbered screens side by side, not part of the
           actual page (see CLAUDE_CODE_PROMPT.md: "visual targets, not code
           to copy"). No outer padding, radius, or shadow here. */
        .ula-auth-container {
            width: 100%;
            min-height: 100vh;
            background: var(--ula-surface-page);
            display: grid;
        }

        .ula-auth-container.login-layout {
            grid-template-columns: 640px minmax(0, 1fr);
        }

        .ula-auth-container.register-layout {
            grid-template-columns: 760px minmax(0, 1fr);
        }

        .ula-auth-form-side {
            padding: 40px 80px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            gap: 28px;
            background: var(--ula-surface-page);
        }

        .ula-auth-top-bar {
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .ula-auth-brand {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            text-decoration: none;
            color: var(--ula-accent-default);
        }

        .ula-auth-brand-name {
            font-family: var(--ula-font-en);
            font-size: 18px;
            font-weight: 500;
            color: var(--ula-text-primary);
        }

        .ula-auth-lang-btn {
            height: 44px;
            padding: 0 14px;
            border-radius: var(--ula-radius-sm);
            border: var(--ula-border-width-hairline) solid var(--ula-border-default);
            background: var(--ula-surface-card);
            color: var(--ula-text-secondary);
            font-family: var(--ula-font-en);
            font-size: 14px;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            text-decoration: none;
            cursor: pointer;
            transition: all var(--ula-duration-fast) var(--ula-ease-out);
        }

        .ula-auth-lang-btn:hover {
            background: var(--ula-surface-page-alt);
            color: var(--ula-text-primary);
        }

        /* ── Input Styling ── */
        .ula-field-group {
            display: flex;
            flex-direction: column;
            gap: 7px;
        }

        .ula-field-label {
            font-size: 15px;
            font-weight: 500;
            color: var(--ula-text-primary);
        }

        .ula-input-box {
            position: relative;
            height: 46px;
            padding: 0 16px;
            border-radius: 14px;
            border: var(--ula-border-width-hairline) solid var(--ula-border-default);
            background: var(--ula-surface-page);
            display: flex;
            align-items: center;
            gap: 10px;
            color: var(--ula-text-muted);
            transition: border-color var(--ula-duration-fast) var(--ula-ease-out), box-shadow var(--ula-duration-fast) var(--ula-ease-out);
        }

        .ula-input-box:focus-within {
            border-color: var(--ula-accent-default);
            box-shadow: 0 0 0 2px var(--ula-surface-page), 0 0 0 4px var(--ula-highlight-default);
        }

        .ula-input-control {
            flex: 1;
            height: 100%;
            border: none;
            background: transparent;
            font-size: 15px;
            color: var(--ula-text-primary);
            outline: none;
            font-family: inherit;
        }

        .ula-input-control::placeholder {
            color: var(--ula-text-muted);
        }

        .ula-password-toggle-btn {
            background: transparent;
            border: none;
            cursor: pointer;
            color: var(--ula-text-muted);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0;
            outline: none;
        }

        .ula-password-toggle-btn:hover {
            color: var(--ula-text-primary);
        }

        /* ── Action Buttons & Links ── */
        .ula-btn-auth-submit {
            height: 52px;
            width: 100%;
            border-radius: 18px;
            border: 0;
            background: var(--ula-accent-default);
            color: var(--ula-accent-fg);
            font-family: var(--ula-font-ar);
            font-size: 17px;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            box-shadow: var(--ula-shadow-xs);
            transition: background var(--ula-duration-fast) var(--ula-ease-out);
        }

        .ula-btn-auth-submit:hover {
            background: var(--ula-accent-hover);
        }

        .ula-auth-switch-text {
            font-size: 15px;
            color: var(--ula-text-secondary);
            text-align: center;
        }

        .ula-auth-switch-text a {
            color: var(--ula-accent-default);
            font-weight: 600;
            text-decoration: none;
        }

        .ula-auth-switch-text a:hover {
            text-decoration: underline;
        }

        /* ── Alerts ── */
        .ula-auth-alert-error {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 14px;
            border-radius: 14px;
            background: var(--ula-tone-terracotta-bg);
            color: var(--ula-tone-terracotta-fg);
            font-size: 14px;
        }

        .ula-auth-alert-success {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 14px;
            border-radius: 14px;
            background: var(--ula-tone-palm-bg);
            color: var(--ula-tone-palm-fg);
            font-size: 14px;
        }

        /* ── Right Hero Panel (Dark Canvas with Stripes) ── */
        .ula-auth-hero-side {
            position: relative;
            background: var(--ula-surface-dark);
            color: var(--ula-text-on-dark);
            display: flex;
            flex-direction: column;
            justify-content: flex-end;
            padding: 64px;
            overflow: hidden;
        }

        .ula-auth-hero-stripes {
            position: absolute;
            inset: 0;
            background: repeating-linear-gradient(135deg, var(--ula-palm-800) 0 14px, var(--ula-palm-chrome) 14px 28px);
        }

        .ula-auth-hero-scrim {
            position: absolute;
            inset: 0;
            background: linear-gradient(to top, rgba(14,28,23,0.72) 0%, rgba(14,28,23,0.18) 48%, rgba(14,28,23,0) 100%);
            pointer-events: none;
        }

        .ula-auth-hero-content {
            position: relative;
            z-index: 2;
            display: flex;
            flex-direction: column;
            gap: 10px;
            max-width: 560px;
        }

        @media (max-width: 1024px) {
            .ula-auth-container.login-layout,
            .ula-auth-container.register-layout {
                grid-template-columns: 1fr;
            }
            .ula-auth-hero-side { display: none; }
            .ula-auth-form-side { padding: 32px 24px; }
        }
    </style>
    @yield('styles')
</head>
<body>

    @yield('content')

    <script nonce="{{ $cspNonce ?? '' }}">
        function togglePassword(inputId, btn) {
            const input = document.getElementById(inputId);
            const icon = btn.querySelector('.ms');
            if (input.type === 'password') {
                input.type = 'text';
                if (icon) icon.textContent = 'visibility_off';
            } else {
                input.type = 'password';
                if (icon) icon.textContent = 'visibility';
            }
        }
    </script>
    @yield('scripts')
</body>
</html>
