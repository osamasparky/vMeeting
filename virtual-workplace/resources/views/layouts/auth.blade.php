<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Virtual Workplace — UlaSpace Digital Workspace">
    <title>@yield('title', __('Virtual Workplace'))</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Mono:wght@400;500;600;700&family=IBM+Plex+Sans+Arabic:wght@300;400;500;600;700;800&family=IBM+Plex+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;1,400&family=Material+Symbols+Rounded:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/ulaspace-tokens.css') }}">
    <link rel="stylesheet" href="{{ asset('css/modern-design-system.css') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        :root {
            --bg-primary: var(--nx-bg-page, #F9F4EE);
            --bg-card: var(--nx-bg-surface, #FFFFFF);
            --bg-input: var(--nx-sand-100, #F9F4EE);
            --border-color: var(--nx-border-subtle, rgba(27, 50, 35, 0.08));
            --text-primary: var(--nx-text-primary, #142B24);
            --text-secondary: var(--nx-text-secondary, #5A6B63);
            --text-muted: var(--nx-text-muted, #8E9D95);
            --brand-primary: var(--nx-palm-900, #142B24);
            --brand-accent: var(--nx-palm-700, #1B3223);
            --brand-emerald: var(--nx-palm-500, #1E412F);
            --status-success: var(--nx-status-live, #3C6B4C);
            --status-error: var(--nx-status-attention, #9A5827);
            --font-ar: 'IBM Plex Sans Arabic', -apple-system, BlinkMacSystemFont, sans-serif;
            --font-en: 'IBM Plex Sans', -apple-system, BlinkMacSystemFont, sans-serif;
            --font-mono: 'IBM Plex Mono', monospace;
            --font-family: var(--font-en);
        }

        [dir="rtl"], [lang="ar"] {
            --font-family: var(--font-ar);
        }

        [data-theme="dark"], html.dark, body.dark-mode {
            --bg-primary: var(--nx-palm-950, #0B1410);
            --bg-card: var(--nx-palm-900, #142B24);
            --bg-input: var(--nx-palm-950, #0B1410);
            --border-color: var(--nx-border-subtle, rgba(237, 230, 217, 0.12));
            --text-primary: var(--nx-sand-100, #F9F4EE);
            --text-secondary: var(--nx-sand-400, #E3D2BB);
            --text-muted: var(--nx-text-muted, #A4B5AD);
            --brand-primary: var(--nx-palm-300, #4EA66F);
            --brand-accent: var(--nx-sand-300, #EADCC9);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: var(--font-family);
        }

        body {
            background: var(--bg-primary);
            color: var(--text-primary);
            min-height: 100vh;
            overflow-x: hidden;
            -webkit-font-smoothing: antialiased;
        }

        .bg-pattern {
            position: fixed;
            top: 0;
            inset-inline-start: 0;
            width: 100%;
            height: 100%;
            z-index: 0;
            overflow: hidden;
            background: radial-gradient(circle at 10% 20%, rgba(27, 50, 35, 0.04) 0%, transparent 45%),
                        radial-gradient(circle at 90% 80%, rgba(211, 165, 83, 0.05) 0%, transparent 40%);
            pointer-events: none;
        }

        .auth-wrapper {
            position: relative;
            z-index: 1;
            display: flex;
            min-height: 100vh;
        }

        .auth-left {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 40px 24px;
        }

        .auth-right {
            flex: 1;
            display: none;
            align-items: center;
            justify-content: center;
            padding: 60px;
            background: linear-gradient(145deg, #142B24 0%, #1B3223 50%, #0B1410 100%);
            color: var(--nx-sand-100, #F9F4EE);
            position: relative;
            overflow: hidden;
        }

        .auth-right::before {
            content: '';
            position: absolute;
            top: -100px;
            inset-inline-end: -100px;
            width: 400px;
            height: 400px;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(211, 165, 83, 0.15) 0%, transparent 70%);
            pointer-events: none;
        }

        @media (min-width: 1024px) {
            .auth-right {
                display: flex;
            }
        }

        .auth-card {
            width: 100%;
            max-width: 460px;
            background: var(--bg-card);
            border: 1px solid var(--border-color);
            border-radius: var(--nx-radius-xl, 20px);
            padding: 40px 32px;
            box-shadow: var(--nx-shadow-md, 0 8px 24px -8px rgba(27, 50, 35, 0.12));
            animation: slideUp 0.4s cubic-bezier(0.16, 1, 0.3, 1);
        }

        @keyframes slideUp {
            from { opacity: 0; transform: translateY(16px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .auth-logo {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 24px;
        }

        .logo-icon {
            width: 42px;
            height: 42px;
            background: linear-gradient(135deg, #142B24 0%, #1E412F 100%);
            border-radius: var(--nx-radius-md, 12px);
            display: flex;
            align-items: center;
            justify-content: center;
            color: #FFFFFF;
            box-shadow: var(--nx-shadow-sm);
        }

        .logo-icon .material-symbols-rounded {
            font-size: 24px;
        }

        .logo-text {
            font-size: 19px;
            font-weight: 800;
            color: var(--text-primary);
            letter-spacing: -0.2px;
        }

        .auth-title {
            font-size: 24px;
            font-weight: 800;
            color: var(--text-primary);
            margin-bottom: 6px;
            line-height: 1.25;
        }

        .auth-subtitle {
            color: var(--text-secondary);
            font-size: 14px;
            margin-bottom: 24px;
            line-height: 1.5;
            font-weight: 500;
        }

        .form-group {
            margin-bottom: 18px;
        }

        .form-label {
            display: block;
            font-size: 12px;
            font-weight: 700;
            color: var(--text-secondary);
            margin-bottom: 6px;
            letter-spacing: 0.2px;
        }

        .form-input-wrapper {
            position: relative;
        }

        .form-input-icon {
            position: absolute;
            inset-inline-start: 14px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-muted);
            font-size: 20px;
            pointer-events: none;
            display: flex;
            align-items: center;
        }

        .form-input {
            width: 100%;
            padding: 12px 16px;
            padding-inline-start: 42px;
            background: var(--bg-input);
            border: 1px solid var(--border-color);
            border-radius: var(--nx-radius-md, 12px);
            color: var(--text-primary);
            font-size: 14px;
            font-weight: 500;
            font-family: inherit;
            transition: all 0.2s ease;
            outline: none;
        }

        .form-input:focus {
            background: var(--bg-card);
            border-color: var(--nx-palm-500, #1E412F);
            box-shadow: 0 0 0 3px rgba(30, 65, 47, 0.12);
        }

        .form-input::placeholder {
            color: var(--text-muted);
            font-weight: 400;
        }

        .password-toggle {
            position: absolute;
            inset-inline-end: 12px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: var(--text-muted);
            cursor: pointer;
            padding: 4px;
            display: flex;
            align-items: center;
            border-radius: 6px;
        }

        .password-toggle:hover {
            color: var(--text-primary);
        }

        .password-toggle .material-symbols-rounded {
            font-size: 20px;
        }

        .form-check {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 20px;
        }

        .form-check-label {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 13px;
            font-weight: 600;
            color: var(--text-secondary);
            cursor: pointer;
        }

        .form-check-input {
            width: 16px;
            height: 16px;
            accent-color: var(--nx-palm-700, #1B3223);
            cursor: pointer;
            border-radius: 4px;
        }

        .form-link {
            color: var(--nx-palm-700, #1B3223);
            font-size: 13px;
            text-decoration: none;
            font-weight: 700;
        }
        .form-link:hover {
            text-decoration: underline;
        }

        .auth-footer {
            text-align: center;
            margin-top: 24px;
            font-size: 13px;
            color: var(--text-secondary);
            font-weight: 500;
        }

        .auth-footer a {
            color: var(--nx-palm-700, #1B3223);
            text-decoration: none;
            font-weight: 700;
        }
        .auth-footer a:hover {
            text-decoration: underline;
        }

        .alert {
            padding: 12px 16px;
            border-radius: var(--nx-radius-md, 12px);
            font-size: 13px;
            font-weight: 600;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .alert-error {
            background: rgba(154, 88, 39, 0.08);
            border: 1px solid rgba(154, 88, 39, 0.25);
            color: var(--status-error);
        }

        .alert-success {
            background: rgba(60, 107, 76, 0.08);
            border: 1px solid rgba(60, 107, 76, 0.25);
            color: var(--status-success);
        }

        .alert .material-symbols-rounded {
            font-size: 20px;
            flex-shrink: 0;
        }

        .brand-panel {
            text-align: center;
            max-width: 440px;
        }
        .brand-panel-icon {
            width: 72px;
            height: 72px;
            margin: 0 auto 24px;
            background: rgba(255, 255, 255, 0.08);
            border: 1px solid rgba(255, 255, 255, 0.12);
            border-radius: var(--nx-radius-xl, 20px);
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--nx-sand-300, #EADCC9);
        }
        .brand-panel-icon .material-symbols-rounded {
            font-size: 38px;
        }
        .brand-title {
            font-size: 28px;
            font-weight: 800;
            margin-bottom: 14px;
            color: var(--nx-sand-100, #F9F4EE);
            letter-spacing: -0.3px;
        }
        .brand-description {
            color: var(--nx-sand-400, #E3D2BB);
            font-size: 15px;
            line-height: 1.7;
            font-weight: 400;
        }

        .lang-switch-btn {
            background: var(--bg-card);
            border: 1px solid var(--border-color);
            color: var(--text-primary);
            padding: 8px 14px;
            border-radius: var(--nx-radius-md, 12px);
            font-size: 13px;
            text-decoration: none;
            font-weight: 700;
            box-shadow: var(--nx-shadow-sm);
            display: inline-flex;
            align-items: center;
            gap: 6px;
            transition: all 0.15s ease;
        }
        .lang-switch-btn:hover {
            border-color: var(--nx-palm-500, #1E412F);
            color: var(--nx-palm-700, #1B3223);
        }

        .spinner {
            width: 18px;
            height: 18px;
            border: 2px solid rgba(255,255,255,0.3);
            border-top-color: white;
            border-radius: 50%;
            animation: spinnerRotate 0.6s linear infinite;
            display: none;
        }
        .btn-loading .spinner { display: block; }
        .btn-loading .btn-text { display: none; }

        @keyframes spinnerRotate {
            to { transform: rotate(360deg); }
        }
    </style>
    @yield('styles')
</head>
<body>
    <div class="bg-pattern"></div>

    @yield('content')

    @yield('scripts')
</body>
</html>
