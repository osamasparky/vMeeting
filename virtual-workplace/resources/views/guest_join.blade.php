<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('Guest Invitation') }} — {{ __('Virtual Workplace') }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;500;600;700;800;900&family=IBM+Plex+Sans+Arabic:wght@300;400;500;600;700;800&family=IBM+Plex+Sans:wght@300;400;500;600;700&family=IBM+Plex+Mono:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Rounded:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" />
    <link rel="stylesheet" href="{{ asset('css/ulaspace-tokens.css') }}">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            position: relative;
            background-color: var(--ula-palm-950);
            color: var(--ula-text-on-dark);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: {{ app()->getLocale() === 'ar' ? "var(--ula-font-ar)" : "var(--ula-font-en)" }};
            padding: var(--ula-space-6);
            overflow: hidden;
        }

        .lobby-backdrop-stripes {
            position: absolute;
            inset: 0;
            background-image: repeating-linear-gradient(135deg, var(--ula-palm-900) 0 14px, var(--ula-palm-950) 14px 28px);
        }

        .lobby-backdrop-scrim {
            position: absolute;
            inset: 0;
            background: var(--ula-surface-overlay);
        }

        .lobby-card {
            position: relative;
            z-index: 1;
            background: var(--ula-surface-card);
            border: 1px solid var(--ula-border-subtle);
            border-radius: var(--ula-radius-xl);
            padding: var(--ula-space-9);
            width: 100%;
            max-width: 480px;
            box-shadow: var(--ula-shadow-xl);
            text-align: start;
            display: flex;
            flex-direction: column;
            gap: var(--ula-space-7);
        }

        .lobby-brand {
            display: flex;
            align-items: center;
            gap: var(--ula-space-4);
        }

        /* Card sits on surface/card, which flips dark in dark mode — mark must
           follow the surface, not stay permanently green (see FIX_BRIEF.md
           "pick by surface, not by page"; same pattern as landing/auth). */
        .lobby-brand-mark { fill: var(--ula-brand-mark-green); }
        @media (prefers-color-scheme: dark) {
            :root:not([data-theme="light"]) .lobby-brand-mark { fill: var(--ula-brand-mark-ivory); }
        }
        [data-theme="dark"] .lobby-brand-mark, .dark .lobby-brand-mark {
            fill: var(--ula-brand-mark-ivory);
        }

        .lobby-brand-name {
            font-family: var(--ula-font-en);
            font-size: var(--ula-size-h4);
            font-weight: var(--ula-weight-medium);
            color: var(--ula-text-primary);
        }

        .inviter-card {
            display: flex;
            align-items: center;
            gap: var(--ula-space-4);
            padding: var(--ula-space-5);
            border-radius: var(--ula-radius-md);
            background: var(--ula-surface-page);
            border: 1px solid var(--ula-border-subtle);
        }

        .inviter-avatar {
            width: 44px;
            height: 44px;
            min-width: 44px;
            border-radius: var(--ula-radius-pill);
            background: var(--ula-tone-palm-bg);
            color: var(--ula-tone-palm-fg);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: var(--ula-size-body-lg);
            font-weight: var(--ula-weight-semibold);
        }

        .room-badge {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: var(--ula-space-4) var(--ula-space-5);
            border-radius: var(--ula-radius-md);
            background: var(--ula-tone-palm-bg);
            color: var(--ula-tone-palm-fg);
        }

        .form-label {
            display: block;
            font-size: var(--ula-size-body);
            font-weight: var(--ula-weight-medium);
            color: var(--ula-text-primary);
            margin-bottom: 7px;
        }

        .form-input {
            width: 100%;
            height: 46px;
            background: var(--ula-surface-page);
            border: 1px solid var(--ula-border-default);
            border-radius: var(--ula-radius-md);
            padding-inline: var(--ula-space-5);
            color: var(--ula-text-primary);
            font-size: var(--ula-size-body);
            font-family: inherit;
            outline: none;
            transition: border-color var(--ula-duration-fast) var(--ula-ease-out), box-shadow var(--ula-duration-fast) var(--ula-ease-out);
        }

        .form-input::placeholder {
            color: var(--ula-text-muted);
        }

        .form-input:focus {
            border-color: var(--ula-border-focus);
            box-shadow: var(--ula-focus-ring);
        }

        .join-btn {
            width: 100%;
            background: var(--ula-accent-default);
            color: var(--ula-accent-fg);
            border: none;
            border-radius: var(--ula-radius-md);
            height: 52px;
            font-size: var(--ula-size-body-lg);
            font-weight: var(--ula-weight-semibold);
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: var(--ula-space-3);
            transition: background-color var(--ula-duration-fast) var(--ula-ease-out), transform var(--ula-duration-fast) var(--ula-ease-out);
            box-shadow: var(--ula-shadow-xs);
            text-decoration: none;
        }

        .join-btn:hover {
            background: var(--ula-accent-hover);
            transform: translateY(-1px);
            text-decoration: none;
        }

        .issue-icon {
            width: 64px;
            height: 64px;
            border-radius: var(--ula-radius-lg);
            background: var(--ula-surface-danger-soft);
            color: var(--ula-status-danger);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            align-self: center;
        }

        .error-card {
            font-size: var(--ula-size-body);
            color: var(--ula-text-body);
            line-height: var(--ula-lh-body);
            text-align: center;
        }
    </style>
</head>
<body>

    <div class="lobby-backdrop-stripes"></div>
    <div class="lobby-backdrop-scrim"></div>

    @if(!empty($error))
        <div class="lobby-card" style="align-items: center; text-align: center;">
            <span class="issue-icon">
                <span class="material-symbols-rounded" style="font-size: 32px;">link_off</span>
            </span>
            <div class="ula-headline-group" style="align-items: center;">
                <span class="ula-headline-ar" style="font-size: var(--ula-size-h3);">مشكلة في الدعوة</span>
                <span class="ula-headline-en" style="font-size: var(--ula-size-h2-en);">Invitation Issue</span>
            </div>
            <div class="error-card">{{ $error }}</div>
            <a href="{{ route('login') }}" class="join-btn">
                <span class="material-symbols-rounded" style="font-size: 18px;">home</span>
                <span>{{ __('Go to Homepage') }}</span>
            </a>
        </div>
    @else
        <div class="lobby-card">
            <div class="lobby-brand">
                <svg class="lobby-brand-mark" viewBox="0 0 100 67" width="34" height="23" xmlns="http://www.w3.org/2000/svg">
                    <path fill-rule="evenodd" clip-rule="evenodd" d="M0 67C0 24 16 0 52 0C84 0 100 24 100 67H46C46 38 38 28 28 28C18 28 14 38 14 67H0Z"/>
                </svg>
                <span class="lobby-brand-name">UlaSpace</span>
            </div>

            <div class="ula-headline-group">
                <span class="ula-headline-ar" style="font-size: var(--ula-size-h3);">دعوة ضيف</span>
                <span class="ula-headline-en" style="font-size: var(--ula-size-h2-en);">Guest Invitation</span>
            </div>

            <div class="inviter-card">
                <span class="inviter-avatar">{{ mb_substr($invitation->host->name, 0, 1) }}</span>
                <div style="display: flex; flex-direction: column; gap: 2px;">
                    <span style="font-size: var(--ula-size-xs); color: var(--ula-text-secondary);">{{ __('Invited by') }}</span>
                    <span style="font-size: var(--ula-size-body); font-weight: var(--ula-weight-semibold); color: var(--ula-text-primary);">
                        {{ $invitation->host->name }} · {{ $invitation->organization->name }}
                    </span>
                    <span style="font-size: var(--ula-size-xs); color: var(--ula-text-secondary);">{{ __('to join their virtual office space.') }}</span>
                </div>
            </div>

            <div class="room-badge">
                <span style="display: inline-flex; align-items: center; gap: var(--ula-space-3); font-size: var(--ula-size-sm);">
                    <span class="material-symbols-rounded" style="font-size: 20px;">meeting_room</span>
                    {{ __('Destination Room:') }}
                </span>
                <span style="font-size: var(--ula-size-body); font-weight: var(--ula-weight-semibold);">{{ $invitation->room->name }}</span>
            </div>

            <form action="{{ route('guest.enter', $invitation->token) }}" method="POST">
                @csrf
                <div style="margin-bottom: var(--ula-space-6);">
                    <label class="form-label" for="guest_name">{{ __('Your Full Name (Display Name)') }}</label>
                    <input type="text" id="guest_name" name="guest_name" class="form-input" value="{{ old('guest_name', $invitation->guest_name) }}" required placeholder="e.g. John Smith / Partner">
                </div>

                <button type="submit" class="join-btn">
                    <span class="material-symbols-rounded" style="font-size: 22px;">login</span>
                    <span>{{ __('Enter Workplace as Guest') }}</span>
                </button>
            </form>
        </div>
    @endif

</body>
</html>
