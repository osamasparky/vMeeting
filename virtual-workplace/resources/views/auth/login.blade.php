@extends('layouts.auth')

@section('title', __('Login') . ' — UlaSpace')

@section('content')
<div style="position: absolute; top: 20px; inset-inline-end: 24px; z-index: 10;">
    @if(app()->getLocale() === 'ar')
        <a href="{{ route('lang.switch', 'en') }}" class="lang-switch-btn">
            <span class="material-symbols-rounded" style="font-size: 18px;">language</span>
            <span>English</span>
        </a>
    @else
        <a href="{{ route('lang.switch', 'ar') }}" class="lang-switch-btn">
            <span class="material-symbols-rounded" style="font-size: 18px;">language</span>
            <span>العربية</span>
        </a>
    @endif
</div>

<div class="auth-wrapper">
    <!-- Left: Login Form -->
    <div class="auth-left">
        <div class="auth-card">
            <div class="auth-logo">
                <svg class="auth-brand-mark" viewBox="0 0 100 67" width="34" height="23" xmlns="http://www.w3.org/2000/svg">
                    <path fill-rule="evenodd" clip-rule="evenodd" d="M0 67C0 24 16 0 52 0C84 0 100 24 100 67H46C46 38 38 28 28 28C18 28 14 38 14 67H0Z"/>
                </svg>
                <div>
                    <span class="logo-text" style="display: block; line-height: 1.1; font-weight: 800;">UlaSpace</span>
                    <span style="font-size: 10px; font-weight: 700; color: var(--ula-text-secondary); letter-spacing: 0.5px; text-transform: uppercase;">{{ __('Virtual Workplace') }}</span>
                </div>
            </div>

            <div class="ula-headline-group" style="margin-bottom: 4px;">
                <span class="ula-headline-ar" style="font-size: 24px;">أهلاً بعودتك</span>
                <span class="ula-headline-en" style="font-size: 15px;">Welcome back</span>
            </div>
            <p class="auth-subtitle">{{ __('Sign in to your account to access your virtual office') }}</p>

            @if($errors->any())
                <div class="alert alert-error">
                    <span class="material-symbols-rounded">warning</span>
                    <ul style="list-style: none; padding: 0; margin: 0;">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @if(session('success'))
                <div class="alert alert-success">
                    <span class="material-symbols-rounded">check_circle</span>
                    <span>{{ session('success') }}</span>
                </div>
            @endif

            <form method="POST" action="{{ route('login.submit') }}" id="loginForm">
                @csrf

                <div class="form-group">
                    <x-input
                        id="email"
                        name="email"
                        type="email"
                        :label="__('Email Address')"
                        placeholder="name@company.com"
                        value="{{ old('email') }}"
                        icon="mail"
                        required
                        autocomplete="email"
                    />
                </div>

                <div class="form-group">
                    <label class="form-label" for="password">{{ __('Password') }}</label>
                    <div class="form-input-wrapper">
                        <input
                            type="password"
                            id="password"
                            name="password"
                            class="form-input"
                            placeholder="{{ __('Enter your password') }}"
                            required
                            autocomplete="current-password"
                        >
                        <span class="form-input-icon">
                            <span class="material-symbols-rounded">lock</span>
                        </span>
                        <button type="button" class="password-toggle" onclick="togglePassword('password', this)" aria-label="Toggle password visibility">
                            <span class="material-symbols-rounded">visibility</span>
                        </button>
                    </div>
                </div>

                <div class="form-check">
                    <x-checkbox name="remember">{{ __('Remember me') }}</x-checkbox>
                    <span style="color: var(--ula-text-muted); font-size: 13px; font-weight: 700;">{{ __('Forgot password?') }}</span>
                </div>

                <button type="submit" class="nx-btn nx-btn--primary" id="loginBtn" style="width: 100%; justify-content: center; padding: 12px 20px; font-weight: 700;">
                    <span class="btn-text">{{ __('Sign In') }}</span>
                    <div class="spinner"></div>
                </button>
            </form>

            <div class="auth-footer">
                {{ __("Don't have an account?") }} <a href="{{ route('register') }}">{{ __('Create one') }}</a>
            </div>
        </div>
    </div>

    <!-- Right: Branding Panel -->
    <div class="auth-right">
        <div class="auth-right-stripes"></div>
        <div class="auth-right-scrim"></div>
        <div class="brand-panel">
            <div style="margin-bottom: 28px;">
                <img src="{{ asset('images/ulaspace-logo.png') }}" alt="UlaSpace" style="max-width: 260px; width: 100%; height: auto; margin: 0 auto; display: block;">
            </div>
            <div class="ula-headline-group" style="margin-bottom: 14px;">
                <span class="ula-headline-ar" style="font-size: 30px;">مكتبك الافتراضي بانتظارك</span>
                <span class="ula-headline-en" style="font-size: 17px;">Your Virtual Office Awaits</span>
            </div>
            <p class="brand-description">
                {{ __('Step into a persistent, spatial workspace where your team connects naturally — just like a real office, but without walls.') }}
            </p>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script nonce="{{ $cspNonce ?? '' }}">
    function togglePassword(inputId, btn) {
        const input = document.getElementById(inputId);
        const icon = btn.querySelector('.material-symbols-rounded');
        if (input.type === 'password') {
            input.type = 'text';
            if (icon) icon.textContent = 'visibility_off';
        } else {
            input.type = 'password';
            if (icon) icon.textContent = 'visibility';
        }
    }

    document.getElementById('loginForm').addEventListener('submit', function() {
        const btn = document.getElementById('loginBtn');
        btn.classList.add('btn-loading');
        btn.disabled = true;
    });
</script>
@endsection
