@extends('layouts.auth')

@section('title', __('Login') . ' — Virtual Workplace')

@section('content')
<div style="position: absolute; top: 20px; inset-inline-end: 24px; z-index: 10;">
    @if(app()->getLocale() === 'ar')
        <a href="{{ route('lang.switch', 'en') }}" class="lang-switch-btn" style="background: #ffffff; border: 1px solid var(--border-color); color: var(--brand-navy); padding: 7px 14px; border-radius: 8px; font-size: 13px; text-decoration: none; font-weight: 800; box-shadow: var(--shadow-input);">🌐 English</a>
    @else
        <a href="{{ route('lang.switch', 'ar') }}" class="lang-switch-btn" style="background: #ffffff; border: 1px solid var(--border-color); color: var(--brand-navy); padding: 7px 14px; border-radius: 8px; font-size: 13px; text-decoration: none; font-weight: 800; box-shadow: var(--shadow-input);">🌐 العربية</a>
    @endif
</div>

<div class="auth-wrapper">
    <!-- Left: Login Form -->
    <div class="auth-left">
        <div class="auth-card">
            <div class="auth-logo">
                <div class="logo-icon">🏢</div>
                <span class="logo-text">{{ __('Virtual Workplace') }}</span>
            </div>

            <h1 class="auth-title">{{ __('Welcome back') }}</h1>
            <p class="auth-subtitle">{{ __('Sign in to your account to access your virtual office') }}</p>

            @if($errors->any())
                <div class="alert alert-error">
                    <span>⚠️</span>
                    <ul style="list-style: none; padding: 0;">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @if(session('success'))
                <div class="alert alert-success">
                    <span>✅</span>
                    <span>{{ session('success') }}</span>
                </div>
            @endif

            <form method="POST" action="{{ route('login.submit') }}" id="loginForm">
                @csrf

                <div class="form-group">
                    <label class="form-label" for="email">{{ __('Email Address') }}</label>
                    <div class="form-input-wrapper">
                        <input
                            type="email"
                            id="email"
                            name="email"
                            class="form-input"
                            placeholder="name@company.com"
                            value="{{ old('email') }}"
                            required
                            autocomplete="email"
                        >
                        <span class="form-input-icon">📧</span>
                    </div>
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
                        <span class="form-input-icon">🔒</span>
                        <button type="button" class="password-toggle" onclick="togglePassword('password', this)">👁️</button>
                    </div>
                </div>

                <div class="form-check">
                    <label class="form-check-label">
                        <input type="checkbox" name="remember" class="form-check-input">
                        {{ __('Remember me') }}
                    </label>
                    <a href="#" class="form-link">{{ __('Forgot password?') }}</a>
                </div>

                <button type="submit" class="btn btn-primary" id="loginBtn">
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
        <div class="brand-panel">
            <div style="font-size: 64px; margin-bottom: 20px;">🏢</div>
            <h2 class="brand-title">{{ __('Your Virtual Office Awaits') }}</h2>
            <p class="brand-description">
                {{ __('Step into a persistent, spatial workspace where your team connects naturally — just like a real office, but without walls.') }}
            </p>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    function togglePassword(inputId, btn) {
        const input = document.getElementById(inputId);
        if (input.type === 'password') {
            input.type = 'text';
            btn.textContent = '🙈';
        } else {
            input.type = 'password';
            btn.textContent = '👁️';
        }
    }

    document.getElementById('loginForm').addEventListener('submit', function() {
        const btn = document.getElementById('loginBtn');
        btn.classList.add('btn-loading');
        btn.disabled = true;
    });
</script>
@endsection
