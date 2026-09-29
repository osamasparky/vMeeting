<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('دعوة ضيف') }} — UlaSpace</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;500;600;700&family=IBM+Plex+Mono:wght@400;500&family=IBM+Plex+Sans:wght@300;400;500;600&family=Material+Symbols+Rounded:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/ulaspace-tokens.css') }}">
    <style>
        *, *::before, *::after {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            position: relative;
            background-color: var(--ula-surface-map-canvas);
            color: var(--ula-text-primary);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: var(--ula-font-ar);
            padding: 24px;
            overflow: hidden;
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

        .ula-guest-backdrop-stripes {
            position: absolute;
            inset: 0;
            background: repeating-linear-gradient(135deg, var(--ula-surface-dark) 0 14px, var(--ula-surface-map-canvas) 14px 28px);
        }

        .ula-guest-backdrop-scrim {
            position: absolute;
            inset: 0;
            background: rgba(14,28,23,0.48);
            pointer-events: none;
        }

        /* ── Screen 04 Guest Card ── */
        .ula-guest-card {
            position: relative;
            z-index: 2;
            width: 100%;
            max-width: 520px;
            padding: 40px;
            border-radius: 28px;
            background: var(--ula-surface-card);
            display: flex;
            flex-direction: column;
            gap: 28px;
            box-shadow: var(--ula-shadow-xl);
        }

        .ula-guest-brand {
            display: flex;
            align-items: center;
            gap: 10px;
            text-decoration: none;
            color: var(--ula-accent-default);
        }

        .ula-guest-brand-name {
            font-family: var(--ula-font-en);
            font-size: 18px;
            font-weight: 500;
            color: var(--ula-text-primary);
        }

        .ula-guest-inviter-box {
            display: flex;
            align-items: center;
            gap: 14px;
            padding: 16px;
            border-radius: 16px;
            background: var(--ula-surface-page);
            border: var(--ula-border-width-hairline) solid var(--ula-border-subtle);
        }

        .ula-guest-inviter-avatar {
            width: 44px;
            height: 44px;
            border-radius: var(--ula-radius-pill);
            background: var(--ula-tone-palm-bg);
            color: var(--ula-tone-palm-fg);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 17px;
            font-weight: 600;
            flex-shrink: 0;
        }

        .ula-guest-room-badge {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 14px 16px;
            border-radius: 14px;
            background: var(--ula-tone-palm-bg);
            color: var(--ula-tone-palm-fg);
        }

        .ula-guest-field-group {
            display: flex;
            flex-direction: column;
            gap: 7px;
        }

        .ula-guest-input-box {
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

        .ula-guest-input-box:focus-within {
            border-color: var(--ula-accent-default);
            box-shadow: 0 0 0 2px var(--ula-surface-page), 0 0 0 4px var(--ula-highlight-default);
        }

        .ula-guest-input-control {
            flex: 1;
            height: 100%;
            border: none;
            background: transparent;
            font-size: 15px;
            color: var(--ula-text-primary);
            outline: none;
            font-family: inherit;
        }

        .ula-guest-btn-submit {
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
            gap: 8px;
            cursor: pointer;
            box-shadow: var(--ula-shadow-xs);
            transition: background var(--ula-duration-fast) var(--ula-ease-out);
        }

        .ula-guest-btn-submit:hover {
            background: var(--ula-accent-hover);
        }

        /* ── Screen 05 Guest Issue Card ── */
        .ula-issue-card {
            position: relative;
            z-index: 2;
            width: 100%;
            max-width: 480px;
            padding: 40px;
            border-radius: 28px;
            background: var(--ula-surface-card);
            border: var(--ula-border-width-hairline) solid var(--ula-border-subtle);
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 20px;
            text-align: center;
            box-shadow: var(--ula-shadow-md);
        }

        .ula-issue-icon-box {
            width: 64px;
            height: 64px;
            border-radius: 20px;
            background: var(--ula-tone-terracotta-bg);
            color: var(--ula-tone-terracotta-fg);
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }

        .ula-issue-btn-home {
            height: 44px;
            padding: 0 22px;
            border-radius: 14px;
            border: var(--ula-border-width-hairline) solid var(--ula-border-strong);
            background: var(--ula-surface-card);
            color: var(--ula-text-primary);
            font-family: var(--ula-font-ar);
            font-size: 15px;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            text-decoration: none;
            cursor: pointer;
            transition: all var(--ula-duration-fast) var(--ula-ease-out);
        }

        .ula-issue-btn-home:hover {
            background: var(--ula-surface-page-alt);
        }
    </style>
</head>
<body>

    <div class="ula-guest-backdrop-stripes"></div>
    <div class="ula-guest-backdrop-scrim"></div>

    @if(!empty($error))
        <!-- ── Screen 05: Guest Invite Issue ── -->
        <div class="ula-issue-card">
            <span class="ula-issue-icon-box">
                <span class="ms" style="font-size: 32px;">link_off</span>
            </span>

            <div style="display: flex; flex-direction: column; gap: 4px;">
                <h1 style="font-size: 26px; font-weight: 600; color: var(--ula-text-primary); margin: 0;">مشكلة في الدعوة</h1>
                <span style="font-family: var(--ula-font-en); font-size: 15px; font-weight: 300; color: var(--ula-text-secondary);">Invitation Issue</span>
            </div>

            <p style="font-size: 15px; line-height: 1.6; color: var(--ula-text-body); margin: 0;">
                {{ $error }}
            </p>

            <a href="{{ route('login') }}" class="ula-issue-btn-home">
                <span class="ms" style="font-size: 20px;">home</span>
                <span>{{ __('الذهاب للصفحة الرئيسية') }}</span>
            </a>
        </div>
    @else
        <!-- ── Screen 04: Guest Join Form ── -->
        <div class="ula-guest-card">
            <div class="ula-guest-brand">
                <svg role="img" aria-label="UlaSpace" width="43" height="29" viewBox="-1.2 -1.3 60 40" fill="var(--ula-brand-mark-green)" style="flex-shrink: 0; display: block"><path d="M0 38.734L1.493 30.973L4.179 20.824L6.865 11.869C8.259 7.491 11.94 4.207 17.91 2.018C26.268 -0.569 34.427 -0.669 42.387 1.719C49.153 3.311 54.128 7.292 57.312 13.66L57.312 38.734L26.268 38.734L25.074 27.988C23.482 20.824 21.591 17.242 19.403 17.242C17.214 18.038 15.721 21.819 14.925 28.585L14.328 38.734L0 38.734Z"></path></svg>
                <span class="ula-guest-brand-name">UlaSpace</span>
            </div>

            <div style="display: flex; flex-direction: column; gap: 4px;">
                <h1 style="font-size: 26px; font-weight: 600; line-height: 1.4; color: var(--ula-text-primary); margin: 0;">دعوة ضيف</h1>
                <span style="font-family: var(--ula-font-en); font-size: 15px; font-weight: 300; color: var(--ula-text-secondary);">Guest Invitation</span>
            </div>

            <div class="ula-guest-inviter-box">
                <span class="ula-guest-inviter-avatar">{{ mb_substr($invitation->host->name, 0, 1) }}</span>
                <div style="display: flex; flex-direction: column; gap: 2px;">
                    <span style="font-size: 13px; color: var(--ula-text-secondary);">{{ __('دعاك') }}</span>
                    <span style="font-size: 15px; font-weight: 600; color: var(--ula-text-primary);">
                        {{ $invitation->host->name }} · {{ $invitation->organization->name }}
                    </span>
                    <span style="font-size: 13px; color: var(--ula-text-secondary);">{{ __('للانضمام إلى مكتبهم الافتراضي') }}</span>
                </div>
            </div>

            <div class="ula-guest-room-badge">
                <span style="display: inline-flex; align-items: center; gap: 8px; font-size: 14px; color: var(--ula-tone-palm-fg);">
                    <span class="ms" style="font-size: 20px;">meeting_room</span>
                    <span>{{ __('الغرفة المقصودة:') }}</span>
                </span>
                <span style="font-size: 15px; font-weight: 600; color: var(--ula-tone-palm-fg);">{{ $invitation->room->name }}</span>
            </div>

            <form action="{{ route('guest.enter', $invitation->token) }}" method="POST" style="display: flex; flex-direction: column; gap: 20px;">
                @csrf
                <div class="ula-guest-field-group">
                    <label class="form-label" for="guest_name" style="font-size: 15px; font-weight: 500; color: var(--ula-text-primary);">{{ __('اسمك الكامل (اسم العرض)') }}</label>
                    <div class="ula-guest-input-box">
                        <span class="ms" style="font-size: 20px;">badge</span>
                        <input
                            type="text"
                            id="guest_name"
                            name="guest_name"
                            class="ula-guest-input-control"
                            value="{{ old('guest_name', $invitation->guest_name) }}"
                            required
                            placeholder="مثال: محمد أحمد"
                        >
                    </div>
                </div>

                <button type="submit" class="ula-guest-btn-submit">
                    <span class="ms" style="font-size: 22px;">login</span>
                    <span>{{ __('ادخل كضيف') }}</span>
                </button>
            </form>
        </div>
    @endif

</body>
</html>
