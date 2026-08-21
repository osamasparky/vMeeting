<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Virtual Workplace - Your Digital Office Space">
    <title>@yield('title', __('Virtual Workplace'))</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;800;900&family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            /* 🌿 Virtual Workplace 3D Spatial Palette */
            --bg-primary: #F5F3E8;
            --bg-secondary: #FFFDF6;
            --bg-card: #FFFDF6;
            --bg-card-hover: #FFFDF6;
            --bg-input: #E8EFE2;
            --bg-input-focus: #FFFDF6;

            /* Brand Colors */
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

            --accent-primary: #245C3A;
            --accent-primary-hover: #1E4E31;
            --accent-gradient: linear-gradient(180deg, #2D6C45 0%, #245C3A 100%);

            --text-primary: #26352A;
            --text-secondary: #66756A;
            --text-muted: #8B9B8F;
            --text-accent: #245C3A;

            --border-color: #D5DED0;
            --border-focus: #245C3A;

            --success: #4F9B5F;
            --error: #D96B5F;
            --warning: #D6A23A;

            --radius-sm: 10px;
            --radius-md: 14px;
            --radius-lg: 20px;
            --radius-xl: 24px;

            --shadow-card: 0 10px 28px rgba(36, 92, 58, 0.06), 0 2px 6px rgba(36, 92, 58, 0.03);
            --shadow-glow: 0 0 30px rgba(36, 92, 58, 0.12);
            --shadow-input: inset 2px 2px 5px rgba(36, 92, 58, 0.05);

            --font-family: 'Cairo', 'Inter', sans-serif;
        }

        [data-theme="dark"], html.dark, body.dark-mode {
            --bg-primary: #07100C;
            --bg-secondary: #0B1510;
            --bg-card: #101C15;
            --bg-card-hover: #101C15;
            --bg-input: #15251B;
            --bg-input-focus: #101C15;
            --text-primary: #F1F5EF;
            --text-secondary: #9AA99D;
            --text-muted: #718077;
            --border-color: #26382B;
            --brand-navy: #F1F5EF;
            --brand-ocean: #9AA99D;
            --shadow-card: 0 10px 30px rgba(0, 0, 0, 0.28);
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

        /* ── Background Subtle Aesthetics ── */
        .bg-pattern {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: 0;
            overflow: hidden;
            background: radial-gradient(circle at 10% 20%, rgba(0, 180, 179, 0.05) 0%, transparent 40%),
                        radial-gradient(circle at 90% 80%, rgba(0, 104, 71, 0.04) 0%, transparent 40%);
        }

        .floating-orb {
            position: absolute;
            border-radius: 50%;
            filter: blur(80px);
            opacity: 0.3;
            animation: float 20s ease-in-out infinite;
        }

        .floating-orb:nth-child(1) {
            width: 350px;
            height: 350px;
            background: rgba(0, 180, 179, 0.15);
            top: 5%;
            right: 10%;
        }

        .floating-orb:nth-child(2) {
            width: 280px;
            height: 280px;
            background: rgba(0, 104, 71, 0.12);
            bottom: 10%;
            left: 5%;
        }

        @keyframes float {
            0%, 100% { transform: translate(0, 0) scale(1); }
            50% { transform: translate(20px, -20px) scale(1.05); }
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
            padding: 40px 20px;
        }

        .auth-right {
            flex: 1;
            display: none;
            align-items: center;
            justify-content: center;
            padding: 60px;
            background: linear-gradient(135deg, #012c41, #004862, #00726c);
            color: white;
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
            max-width: 460px;
            background: var(--bg-card);
            border: 1px solid var(--border-color);
            border-radius: var(--radius-xl);
            padding: 44px 36px;
            box-shadow: var(--shadow-card);
            animation: slideUp 0.5s cubic-bezier(0.16, 1, 0.3, 1);
        }

        @keyframes slideUp {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* ── Logo ── */
        .auth-logo {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 28px;
        }

        .logo-icon {
            width: 44px;
            height: 44px;
            background: var(--accent-gradient);
            border-radius: var(--radius-md);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 22px;
            color: white;
            box-shadow: 0 4px 14px rgba(0, 180, 179, 0.3);
        }

        .logo-text {
            font-size: 20px;
            font-weight: 800;
            color: var(--brand-navy);
        }

        /* ── Headings ── */
        .auth-title {
            font-size: 26px;
            font-weight: 900;
            color: var(--brand-navy);
            margin-bottom: 8px;
            line-height: 1.2;
        }

        .auth-subtitle {
            color: var(--text-secondary);
            font-size: 14px;
            margin-bottom: 28px;
            line-height: 1.5;
            font-weight: 500;
        }

        /* ── Form ── */
        .form-group {
            margin-bottom: 18px;
        }

        .form-label {
            display: block;
            font-size: 12px;
            font-weight: 800;
            color: var(--brand-ocean);
            margin-bottom: 8px;
            letter-spacing: 0.3px;
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
            font-size: 18px;
            pointer-events: none;
            transition: color 0.2s;
        }

        .form-input {
            width: 100%;
            padding: 13px 16px;
            padding-inline-start: 44px;
            background: var(--bg-input);
            border: 1px solid var(--border-color);
            border-radius: var(--radius-md);
            color: var(--brand-navy);
            font-size: 14px;
            font-weight: 600;
            font-family: inherit;
            transition: all 0.2s ease;
            outline: none;
            box-shadow: var(--shadow-input);
        }

        .form-input:focus {
            background: #ffffff;
            border-color: var(--brand-teal);
            box-shadow: 0 0 0 3px rgba(0, 180, 179, 0.15);
        }

        .form-input::placeholder {
            color: #94a3b8;
            font-weight: 400;
        }

        /* ── Password Toggle ── */
        .password-toggle {
            position: absolute;
            inset-inline-end: 14px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: var(--text-muted);
            cursor: pointer;
            font-size: 18px;
            padding: 4px;
        }

        /* ── Checkbox ── */
        .form-check {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 22px;
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
            width: 17px;
            height: 17px;
            accent-color: var(--brand-teal);
            cursor: pointer;
        }

        .form-link {
            color: var(--brand-teal);
            font-size: 13px;
            text-decoration: none;
            font-weight: 700;
        }
        .form-link:hover {
            color: var(--brand-pine);
            text-decoration: underline;
        }

        /* ── Button ── */
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 13px 24px;
            border: none;
            border-radius: var(--radius-md);
            font-family: inherit;
            font-size: 14px;
            font-weight: 800;
            cursor: pointer;
            transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
            text-decoration: none;
        }

        .btn-primary {
            width: 100%;
            background: var(--accent-gradient);
            color: white;
            box-shadow: 0 4px 14px rgba(0, 180, 179, 0.35);
        }

        .btn-primary:hover {
            transform: translateY(-1px);
            box-shadow: 0 8px 22px rgba(0, 180, 179, 0.45);
        }

        /* ── Auth Footer ── */
        .auth-footer {
            text-align: center;
            margin-top: 24px;
            font-size: 13px;
            color: var(--text-secondary);
            font-weight: 600;
        }

        .auth-footer a {
            color: var(--brand-teal);
            text-decoration: none;
            font-weight: 800;
        }
        .auth-footer a:hover {
            color: var(--brand-pine);
            text-decoration: underline;
        }

        /* ── Alerts ── */
        .alert {
            padding: 12px 16px;
            border-radius: var(--radius-md);
            font-size: 13px;
            font-weight: 700;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .alert-error {
            background: rgba(210, 0, 5, 0.08);
            border: 1px solid rgba(210, 0, 5, 0.25);
            color: var(--brand-crimson);
        }

        .alert-success {
            background: rgba(0, 104, 71, 0.08);
            border: 1px solid rgba(0, 104, 71, 0.25);
            color: var(--brand-green);
        }

        /* ── Right Panel (Branding) ── */
        .brand-panel {
            text-align: center;
            max-width: 440px;
        }
        .brand-title {
            font-size: 28px;
            font-weight: 900;
            margin-bottom: 14px;
            color: white;
        }
        .brand-description {
            color: #c7d2fe;
            font-size: 15px;
            line-height: 1.7;
            font-weight: 500;
        }

        /* ── Spinner ── */
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
    <div class="bg-pattern">
        <div class="floating-orb"></div>
        <div class="floating-orb"></div>
    </div>

    @yield('content')

    @yield('scripts')
</body>
</html>
