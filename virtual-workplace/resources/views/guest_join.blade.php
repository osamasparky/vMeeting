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
            background: var(--ula-surface-page);
            color: var(--ula-text-primary);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: {{ app()->getLocale() === 'ar' ? "var(--ula-font-ar)" : "var(--ula-font-en)" }};
            background-image: 
                radial-gradient(circle at 20% 20%, rgba(79, 155, 95, 0.15) 0%, transparent 45%),
                radial-gradient(circle at 80% 80%, rgba(36, 92, 58, 0.25) 0%, transparent 45%);
            padding: var(--ula-space-6);
        }

        .lobby-card {
            background: var(--ula-surface-card);
            border: 1px solid var(--ula-border-subtle);
            border-radius: var(--ula-radius-xl);
            padding: var(--ula-space-9);
            width: 100%;
            max-width: 480px;
            box-shadow: var(--ula-shadow-xl);
            backdrop-filter: blur(20px);
            text-align: center;
        }

        .brand-icon {
            width: 64px;
            height: 64px;
            background: var(--ula-gradient-accent);
            border-radius: var(--ula-radius-xl);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            color: var(--ula-white);
            margin-bottom: var(--ula-space-6);
            box-shadow: var(--ula-shadow-xs);
        }

        .title {
            font-size: var(--ula-size-h3);
            font-weight: var(--ula-weight-bold);
            margin-bottom: var(--ula-space-3);
            color: var(--ula-text-primary);
        }

        .subtitle {
            font-size: var(--ula-size-sm);
            color: var(--ula-text-secondary);
            margin-bottom: var(--ula-space-7);
            line-height: 1.6;
        }

        .room-badge {
            display: inline-flex;
            align-items: center;
            gap: var(--ula-space-3);
            background: var(--ula-surface-accent-soft);
            border: 1px solid var(--ula-border-subtle);
            color: var(--ula-accent-default);
            padding: 8px 16px;
            border-radius: var(--ula-radius-pill);
            font-size: var(--ula-size-xs);
            font-weight: var(--ula-weight-bold);
            margin-bottom: var(--ula-space-7);
        }

        .form-group {
            text-align: start;
            margin-bottom: var(--ula-space-6);
        }

        .form-label {
            display: flex;
            align-items: center;
            gap: 4px;
            font-size: var(--ula-size-xs);
            font-weight: var(--ula-weight-bold);
            color: var(--ula-text-secondary);
            margin-bottom: 8px;
            text-transform: uppercase;
            letter-spacing: 0.04em;
        }

        .form-input {
            width: 100%;
            background: var(--ula-surface-page-alt);
            border: 1px solid var(--ula-border-subtle);
            border-radius: var(--ula-radius-lg);
            padding: 14px 16px;
            color: var(--ula-text-primary);
            font-size: var(--ula-size-sm);
            outline: none;
            transition: all 0.2s;
            box-shadow: var(--ula-shadow-xs);
        }

        .form-input:focus {
            border-color: var(--ula-accent-default);
            background: rgba(255, 255, 255, 0.08);
        }

        .join-btn {
            width: 100%;
            background: var(--ula-gradient-accent);
            color: var(--ula-white);
            border: none;
            border-radius: var(--ula-radius-lg);
            padding: 14px;
            font-size: var(--ula-size-sm);
            font-weight: var(--ula-weight-bold);
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: var(--ula-space-3);
            transition: all 0.2s;
            box-shadow: var(--ula-shadow-xs);
            text-decoration: none;
        }

        .join-btn:hover {
            transform: translateY(-2px);
            box-shadow: var(--ula-shadow-md);
        }

        .error-card {
            background: rgba(217, 107, 95, 0.15);
            border: 1px solid rgba(217, 107, 95, 0.35);
            color: var(--ula-status-danger);
            padding: 16px;
            border-radius: var(--ula-radius-lg);
            font-size: var(--ula-size-xs);
            margin-bottom: 20px;
            line-height: 1.5;
        }
    </style>
</head>
<body>

    <div class="lobby-card">
        <div class="brand-icon" style="background: var(--ula-palm-900); width: 64px; height: 64px; border-radius: var(--ula-radius-xl); display: inline-flex; align-items: center; justify-content: center; padding: 12px; margin-bottom: 20px; box-shadow: var(--ula-shadow-md);">
            <img src="{{ asset('images/ulaspace-icon.png') }}" alt="UlaSpace" style="width: 36px; height: auto; object-fit: contain;">
        </div>

        @if(!empty($error))
            <h1 class="title">{{ __('Invitation Issue') }}</h1>
            <div class="error-card">{{ $error }}</div>
            <a href="{{ route('login') }}" class="join-btn">
                <span class="material-symbols-rounded" style="font-size: 18px;">home</span>
                <span>{{ __('Go to Homepage') }}</span>
            </a>
        @else
            <h1 class="title">{{ $invitation->organization->name }}</h1>
            <p class="subtitle">
                {{ __('You have been invited by') }} <strong>{{ $invitation->host->name }}</strong> {{ __('to join their virtual office space.') }}
            </p>

            <div class="room-badge">
                <span class="material-symbols-rounded" style="font-size: 16px;">apartment</span>
                <span>{{ __('Destination Room:') }} <strong>{{ $invitation->room->name }}</strong></span>
            </div>

            <form action="{{ route('guest.enter', $invitation->token) }}" method="POST">
                @csrf
                <div class="form-group">
                    <label class="form-label">
                        <span class="material-symbols-rounded" style="font-size: 15px;">person</span>
                        <span>{{ __('Your Full Name (Display Name)') }}</span>
                    </label>
                    <input type="text" name="guest_name" class="form-input" value="{{ old('guest_name', $invitation->guest_name) }}" required placeholder="e.g. John Smith / Partner">
                </div>

                <button type="submit" class="join-btn">
                    <span class="material-symbols-rounded" style="font-size: 18px;">login</span>
                    <span>{{ __('Enter Workplace as Guest') }}</span>
                </button>
            </form>
        @endif
    </div>

</body>
</html>
