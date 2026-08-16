<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Virtual Workplace - Your Digital Office Space">
    <title>@yield('title', 'Virtual Workplace')</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        /* ═══════════════════════════════════════════════════════════════
           DESIGN SYSTEM — Virtual Workplace
           ═══════════════════════════════════════════════════════════════ */

        :root {
            /* Colors */
            --bg-primary: #0a0e1a;
            --bg-secondary: #111827;
            --bg-card: rgba(17, 24, 39, 0.7);
            --bg-card-hover: rgba(17, 24, 39, 0.9);
            --bg-input: rgba(30, 41, 59, 0.5);
            --bg-input-focus: rgba(30, 41, 59, 0.8);

            --accent-primary: #6366f1;
            --accent-primary-hover: #818cf8;
            --accent-secondary: #8b5cf6;
            --accent-gradient: linear-gradient(135deg, #6366f1, #8b5cf6, #a78bfa);
            --accent-glow: rgba(99, 102, 241, 0.3);

            --text-primary: #f1f5f9;
            --text-secondary: #94a3b8;
            --text-muted: #64748b;
            --text-accent: #a78bfa;

            --border-color: rgba(148, 163, 184, 0.1);
            --border-focus: rgba(99, 102, 241, 0.5);

            --success: #10b981;
            --error: #ef4444;
            --warning: #f59e0b;

            /* Spacing */
            --radius-sm: 8px;
            --radius-md: 12px;
            --radius-lg: 16px;
            --radius-xl: 24px;

            /* Shadows */
            --shadow-card: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
            --shadow-glow: 0 0 40px rgba(99, 102, 241, 0.15);
            --shadow-input: 0 2px 8px rgba(0, 0, 0, 0.2);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', 'Cairo', sans-serif;
            background: var(--bg-primary);
            color: var(--text-primary);
            min-height: 100vh;
            overflow-x: hidden;
            -webkit-font-smoothing: antialiased;
        }

        /* ── Background Animation ── */
        .bg-pattern {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: 0;
            overflow: hidden;
        }

        .bg-pattern::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background:
                radial-gradient(ellipse at 20% 50%, rgba(99, 102, 241, 0.08) 0%, transparent 50%),
                radial-gradient(ellipse at 80% 20%, rgba(139, 92, 246, 0.06) 0%, transparent 50%),
                radial-gradient(ellipse at 50% 80%, rgba(167, 139, 250, 0.04) 0%, transparent 50%);
            animation: bgShift 20s ease-in-out infinite alternate;
        }

        @keyframes bgShift {
            0% { transform: translate(0, 0) rotate(0deg); }
            100% { transform: translate(-2%, -2%) rotate(3deg); }
        }

        .floating-orb {
            position: absolute;
            border-radius: 50%;
            filter: blur(60px);
            opacity: 0.4;
            animation: float 15s ease-in-out infinite;
        }

        .floating-orb:nth-child(1) {
            width: 300px;
            height: 300px;
            background: rgba(99, 102, 241, 0.15);
            top: 10%;
            right: 15%;
            animation-delay: 0s;
            animation-duration: 18s;
        }

        .floating-orb:nth-child(2) {
            width: 200px;
            height: 200px;
            background: rgba(139, 92, 246, 0.12);
            bottom: 20%;
            left: 10%;
            animation-delay: -5s;
            animation-duration: 22s;
        }

        .floating-orb:nth-child(3) {
            width: 150px;
            height: 150px;
            background: rgba(167, 139, 250, 0.1);
            top: 60%;
            right: 40%;
            animation-delay: -10s;
            animation-duration: 16s;
        }

        @keyframes float {
            0%, 100% { transform: translate(0, 0) scale(1); }
            25% { transform: translate(30px, -30px) scale(1.05); }
            50% { transform: translate(-20px, 20px) scale(0.95); }
            75% { transform: translate(15px, 15px) scale(1.02); }
        }

        /* Grid dots */
        .grid-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-image: radial-gradient(rgba(148, 163, 184, 0.04) 1px, transparent 1px);
            background-size: 40px 40px;
            z-index: 0;
        }

        /* ── Auth Layout ── */
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
            padding: 40px;
        }

        .auth-right {
            flex: 1;
            display: none;
            align-items: center;
            justify-content: center;
            padding: 60px;
            position: relative;
        }

        @media (min-width: 1024px) {
            .auth-right {
                display: flex;
            }
        }

        /* ── Card ── */
        .auth-card {
            width: 100%;
            max-width: 440px;
            background: var(--bg-card);
            backdrop-filter: blur(24px);
            -webkit-backdrop-filter: blur(24px);
            border: 1px solid var(--border-color);
            border-radius: var(--radius-xl);
            padding: 48px 40px;
            box-shadow: var(--shadow-card), var(--shadow-glow);
            animation: slideUp 0.6s cubic-bezier(0.16, 1, 0.3, 1);
        }

        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(30px) scale(0.98);
            }
            to {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }

        /* ── Logo ── */
        .auth-logo {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 36px;
        }

        .logo-icon {
            width: 44px;
            height: 44px;
            background: var(--accent-gradient);
            border-radius: var(--radius-md);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            box-shadow: 0 4px 16px rgba(99, 102, 241, 0.3);
        }

        .logo-text {
            font-size: 20px;
            font-weight: 700;
            background: var(--accent-gradient);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        /* ── Headings ── */
        .auth-title {
            font-size: 28px;
            font-weight: 800;
            margin-bottom: 8px;
            letter-spacing: -0.5px;
            line-height: 1.2;
        }

        .auth-subtitle {
            color: var(--text-secondary);
            font-size: 15px;
            margin-bottom: 32px;
            line-height: 1.5;
        }

        /* ── Form ── */
        .form-group {
            margin-bottom: 20px;
        }

        .form-label {
            display: block;
            font-size: 13px;
            font-weight: 600;
            color: var(--text-secondary);
            margin-bottom: 8px;
            letter-spacing: 0.3px;
            text-transform: uppercase;
        }

        .form-input-wrapper {
            position: relative;
        }

        .form-input-icon {
            position: absolute;
            left: 14px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-muted);
            font-size: 18px;
            pointer-events: none;
            transition: color 0.3s;
        }

        .form-input {
            width: 100%;
            padding: 14px 16px 14px 44px;
            background: var(--bg-input);
            border: 1px solid var(--border-color);
            border-radius: var(--radius-md);
            color: var(--text-primary);
            font-size: 15px;
            font-family: inherit;
            transition: all 0.3s ease;
            outline: none;
            box-shadow: var(--shadow-input);
        }

        .form-input:focus {
            background: var(--bg-input-focus);
            border-color: var(--border-focus);
            box-shadow: 0 0 0 3px var(--accent-glow), var(--shadow-input);
        }

        .form-input:focus + .form-input-icon,
        .form-input:focus ~ .form-input-icon {
            color: var(--accent-primary);
        }

        .form-input::placeholder {
            color: var(--text-muted);
        }

        .form-input.no-icon {
            padding-left: 16px;
        }

        /* ── Password Toggle ── */
        .password-toggle {
            position: absolute;
            right: 14px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: var(--text-muted);
            cursor: pointer;
            font-size: 18px;
            padding: 4px;
            transition: color 0.3s;
        }

        .password-toggle:hover {
            color: var(--text-primary);
        }

        /* ── Checkbox ── */
        .form-check {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 24px;
        }

        .form-check-label {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 14px;
            color: var(--text-secondary);
            cursor: pointer;
        }

        .form-check-input {
            width: 18px;
            height: 18px;
            accent-color: var(--accent-primary);
            cursor: pointer;
        }

        .form-link {
            color: var(--text-accent);
            font-size: 14px;
            text-decoration: none;
            font-weight: 500;
            transition: color 0.3s;
        }

        .form-link:hover {
            color: var(--accent-primary-hover);
            text-decoration: underline;
        }

        /* ── Button ── */
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 14px 24px;
            border: none;
            border-radius: var(--radius-md);
            font-family: inherit;
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
            text-decoration: none;
        }

        .btn-primary {
            width: 100%;
            background: var(--accent-gradient);
            color: white;
            box-shadow: 0 4px 16px rgba(99, 102, 241, 0.3);
            position: relative;
            overflow: hidden;
        }

        .btn-primary::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.15), transparent);
            transition: left 0.5s;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 24px rgba(99, 102, 241, 0.4);
        }

        .btn-primary:hover::before {
            left: 100%;
        }

        .btn-primary:active {
            transform: translateY(0);
        }

        .btn-primary:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none;
        }

        /* ── Divider ── */
        .divider {
            display: flex;
            align-items: center;
            gap: 16px;
            margin: 24px 0;
        }

        .divider::before, .divider::after {
            content: '';
            flex: 1;
            height: 1px;
            background: var(--border-color);
        }

        .divider span {
            color: var(--text-muted);
            font-size: 13px;
            font-weight: 500;
        }

        /* ── Auth Footer ── */
        .auth-footer {
            text-align: center;
            margin-top: 28px;
            font-size: 14px;
            color: var(--text-secondary);
        }

        .auth-footer a {
            color: var(--text-accent);
            text-decoration: none;
            font-weight: 600;
        }

        .auth-footer a:hover {
            text-decoration: underline;
        }

        /* ── Errors ── */
        .alert {
            padding: 14px 16px;
            border-radius: var(--radius-md);
            font-size: 14px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
            animation: shake 0.4s ease-in-out;
        }

        .alert-error {
            background: rgba(239, 68, 68, 0.1);
            border: 1px solid rgba(239, 68, 68, 0.2);
            color: #fca5a5;
        }

        .alert-success {
            background: rgba(16, 185, 129, 0.1);
            border: 1px solid rgba(16, 185, 129, 0.2);
            color: #6ee7b7;
        }

        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-4px); }
            75% { transform: translateX(4px); }
        }

        .error-list {
            list-style: none;
            padding: 0;
        }

        .error-list li {
            margin-bottom: 4px;
        }

        /* ── Right Panel (Branding) ── */
        .brand-panel {
            text-align: center;
            max-width: 480px;
        }

        .brand-illustration {
            width: 320px;
            height: 320px;
            margin: 0 auto 40px;
            position: relative;
        }

        .brand-illustration .orbit {
            position: absolute;
            border: 1px solid rgba(99, 102, 241, 0.15);
            border-radius: 50%;
            animation: spin 30s linear infinite;
        }

        .brand-illustration .orbit:nth-child(1) {
            width: 100%;
            height: 100%;
            top: 0;
            left: 0;
        }

        .brand-illustration .orbit:nth-child(2) {
            width: 75%;
            height: 75%;
            top: 12.5%;
            left: 12.5%;
            animation-duration: 25s;
            animation-direction: reverse;
        }

        .brand-illustration .orbit:nth-child(3) {
            width: 50%;
            height: 50%;
            top: 25%;
            left: 25%;
            animation-duration: 20s;
        }

        .brand-illustration .center-icon {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 80px;
            height: 80px;
            background: var(--accent-gradient);
            border-radius: var(--radius-lg);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 36px;
            box-shadow: 0 8px 32px rgba(99, 102, 241, 0.4);
        }

        .brand-illustration .orbit-dot {
            position: absolute;
            width: 12px;
            height: 12px;
            border-radius: 50%;
            background: var(--accent-primary);
            box-shadow: 0 0 12px rgba(99, 102, 241, 0.6);
        }

        @keyframes spin {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }

        .brand-title {
            font-size: 28px;
            font-weight: 800;
            margin-bottom: 16px;
            background: var(--accent-gradient);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .brand-description {
            color: var(--text-secondary);
            font-size: 16px;
            line-height: 1.7;
            max-width: 380px;
            margin: 0 auto;
        }

        .brand-features {
            display: flex;
            gap: 24px;
            justify-content: center;
            margin-top: 40px;
        }

        .brand-feature {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 8px;
        }

        .brand-feature-icon {
            width: 48px;
            height: 48px;
            border-radius: var(--radius-md);
            background: rgba(99, 102, 241, 0.1);
            border: 1px solid rgba(99, 102, 241, 0.15);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 22px;
        }

        .brand-feature-text {
            font-size: 12px;
            color: var(--text-muted);
            font-weight: 500;
        }

        /* ── Spinner ── */
        .spinner {
            width: 20px;
            height: 20px;
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

        /* ── Responsive ── */
        @media (max-width: 640px) {
            .auth-card {
                padding: 32px 24px;
                border-radius: var(--radius-lg);
            }

            .auth-left {
                padding: 20px;
            }

            .auth-title {
                font-size: 24px;
            }
        }
    </style>
    @yield('styles')
</head>
<body>
    <!-- Background -->
    <div class="bg-pattern">
        <div class="floating-orb"></div>
        <div class="floating-orb"></div>
        <div class="floating-orb"></div>
    </div>
    <div class="grid-overlay"></div>

    @yield('content')

    @yield('scripts')
</body>
</html>
