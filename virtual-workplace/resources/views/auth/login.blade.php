@extends('layouts.auth')

@section('title', __('تسجيل الدخول') . ' — UlaSpace')

@section('content')
<div class="ula-auth-container login-layout">
    <!-- Left: Login Form -->
    <div class="ula-auth-form-side">
        <!-- Top bar: Logo & Language Switcher -->
        <div class="ula-auth-top-bar">
            <a href="{{ route('landing.home') }}" class="ula-auth-brand">
                <svg role="img" aria-label="UlaSpace" width="43" height="29" viewBox="-1.2 -1.3 60 40" fill="var(--ula-brand-mark-green)" style="flex-shrink: 0; display: block"><path d="M0 38.734L1.493 30.973L4.179 20.824L6.865 11.869C8.259 7.491 11.94 4.207 17.91 2.018C26.268 -0.569 34.427 -0.669 42.387 1.719C49.153 3.311 54.128 7.292 57.312 13.66L57.312 38.734L26.268 38.734L25.074 27.988C23.482 20.824 21.591 17.242 19.403 17.242C17.214 18.038 15.721 21.819 14.925 28.585L14.328 38.734L0 38.734Z"></path></svg>
                <span class="ula-auth-brand-name">UlaSpace</span>
            </a>

            @if(app()->getLocale() === 'ar')
                <a href="{{ route('lang.switch', 'en') }}" class="ula-auth-lang-btn">
                    <span class="ms" style="font-size: 20px;">language</span>
                    <span>English</span>
                </a>
            @else
                <a href="{{ route('lang.switch', 'ar') }}" class="ula-auth-lang-btn">
                    <span class="ms" style="font-size: 20px;">language</span>
                    <span>العربية</span>
                </a>
            @endif
        </div>

        <div style="flex: 1; display: flex; flex-direction: column; justify-content: center; gap: 32px; max-width: 480px; width: 100%; margin-inline: auto;">
            <!-- Heading Group -->
            <div style="display: flex; flex-direction: column; gap: 4px;">
                <h1 style="font-size: 34px; font-weight: 600; line-height: 1.25; color: var(--ula-text-primary);">أهلاً بعودتك</h1>
                <span style="font-family: var(--ula-font-en); font-size: 18px; font-weight: 300; color: var(--ula-text-secondary);">Welcome back</span>
                <p style="font-size: 15px; color: var(--ula-text-secondary); margin-top: 8px;">سجّل الدخول للوصول إلى مكتبك الافتراضي</p>
            </div>

            @if($errors->any())
                <div class="ula-auth-alert-error">
                    <span class="ms" style="font-size: 20px;">error</span>
                    <ul style="list-style: none; padding: 0; margin: 0;">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @if(session('success'))
                <div class="ula-auth-alert-success">
                    <span class="ms" style="font-size: 20px;">check_circle</span>
                    <span>{{ session('success') }}</span>
                </div>
            @endif

            <form method="POST" action="{{ route('login.submit') }}" id="loginForm" style="display: flex; flex-direction: column; gap: 20px;">
                @csrf

                <!-- Email Input -->
                <div class="ula-field-group">
                    <label class="ula-field-label" for="email">{{ __('البريد الإلكتروني') }}</label>
                    <div class="ula-input-box">
                        <span class="ms" style="font-size: 20px;">mail</span>
                        <input
                            type="email"
                            id="email"
                            name="email"
                            class="ula-input-control"
                            placeholder="name@company.com"
                            value="{{ old('email') }}"
                            required
                            autocomplete="email"
                            style="direction: ltr; unicode-bidi: isolate;"
                        >
                    </div>
                </div>

                <!-- Password Input -->
                <div class="ula-field-group">
                    <label class="ula-field-label" for="password">{{ __('كلمة المرور') }}</label>
                    <div class="ula-input-box">
                        <span class="ms" style="font-size: 20px;">lock</span>
                        <input
                            type="password"
                            id="password"
                            name="password"
                            class="ula-input-control"
                            placeholder="{{ __('أدخل كلمة المرور') }}"
                            required
                            autocomplete="current-password"
                        >
                        <button type="button" class="ula-password-toggle-btn" onclick="togglePassword('password', this)" aria-label="Toggle password visibility">
                            <span class="ms" style="font-size: 20px;">visibility</span>
                        </button>
                    </div>
                </div>

                <!-- Remember Me & Forgot Password -->
                <div style="display: flex; align-items: center; justify-content: space-between; font-size: 15px;">
                    <label style="display: inline-flex; align-items: center; gap: 8px; cursor: pointer;">
                        <input type="checkbox" name="remember" style="accent-color: var(--ula-accent-default); width: 18px; height: 18px; border-radius: 4px;">
                        <span>{{ __('تذكرني') }}</span>
                    </label>
                    <a href="javascript:void(0)" style="font-size: 15px; font-weight: 500; color: var(--ula-text-secondary); text-decoration: none;">
                        {{ __('نسيت كلمة المرور؟') }}
                    </a>
                </div>

                <!-- Submit Button -->
                <button type="submit" class="ula-btn-auth-submit" id="loginBtn">
                    {{ __('تسجيل الدخول') }}
                </button>
            </form>

            <div class="ula-auth-switch-text">
                {{ __('ليس لديك حساب؟') }} <a href="{{ route('register') }}">{{ __('أنشئ حساباً') }}</a>
            </div>
        </div>
    </div>

    <!-- Right: Branding Hero Panel -->
    <div class="ula-auth-hero-side">
        <div class="ula-auth-hero-stripes"></div>
        <div class="ula-auth-hero-scrim"></div>
        <div class="ula-auth-hero-content">
            <h2 style="font-size: 44px; font-weight: 600; line-height: 1.25; color: var(--ula-text-on-dark);">مكتبك الافتراضي بانتظارك</h2>
            <span style="font-family: var(--ula-font-en); font-size: 20px; font-weight: 300; color: var(--ula-sand-400);">Your Virtual Office Awaits</span>
        </div>
    </div>
</div>
@endsection
