@extends('layouts.auth')

@section('title', 'Register — Virtual Workplace')

@section('content')
<div class="auth-wrapper">
    <!-- Left: Register Form -->
    <div class="auth-left">
        <div class="auth-card">
            <div class="auth-logo">
                <div class="logo-icon">🏢</div>
                <span class="logo-text">Virtual Workplace</span>
            </div>

            <h1 class="auth-title">Create your account</h1>
            <p class="auth-subtitle">Join the future of remote work — set up your virtual office in minutes</p>

            @if($errors->any())
                <div class="alert alert-error">
                    <span>⚠️</span>
                    <ul class="error-list">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('register.submit') }}" id="registerForm">
                @csrf

                <div class="form-group">
                    <label class="form-label" for="name">Full Name</label>
                    <div class="form-input-wrapper">
                        <input
                            type="text"
                            id="name"
                            name="name"
                            class="form-input"
                            placeholder="Enter your full name"
                            value="{{ old('name') }}"
                            required
                            autocomplete="name"
                        >
                        <span class="form-input-icon">👤</span>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label" for="email">Email Address</label>
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
                    <label class="form-label" for="password">Password</label>
                    <div class="form-input-wrapper">
                        <input
                            type="password"
                            id="password"
                            name="password"
                            class="form-input"
                            placeholder="Create a strong password"
                            required
                            minlength="8"
                            autocomplete="new-password"
                        >
                        <span class="form-input-icon">🔒</span>
                        <button type="button" class="password-toggle" onclick="togglePassword('password', this)">👁️</button>
                    </div>
                    <div class="password-strength" id="passwordStrength"></div>
                </div>

                <div class="form-group">
                    <label class="form-label" for="password_confirmation">Confirm Password</label>
                    <div class="form-input-wrapper">
                        <input
                            type="password"
                            id="password_confirmation"
                            name="password_confirmation"
                            class="form-input"
                            placeholder="Confirm your password"
                            required
                            autocomplete="new-password"
                        >
                        <span class="form-input-icon">🔒</span>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label" for="organization_name">Organization Name</label>
                    <div class="form-input-wrapper">
                        <input
                            type="text"
                            id="organization_name"
                            name="organization_name"
                            class="form-input"
                            placeholder="Your company or team name"
                            value="{{ old('organization_name') }}"
                            required
                        >
                        <span class="form-input-icon">🏛️</span>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary" id="registerBtn" style="margin-top: 8px;">
                    <span class="btn-text">Create Account & Organization</span>
                    <div class="spinner"></div>
                </button>
            </form>

            <div class="auth-footer">
                Already have an account? <a href="{{ route('login') }}">Sign in</a>
            </div>
        </div>
    </div>

    <!-- Right: Branding Panel -->
    <div class="auth-right">
        <div class="brand-panel">
            <div class="brand-illustration">
                <div class="orbit">
                    <div class="orbit-dot" style="top: 0; left: 50%; transform: translate(-50%, -50%);"></div>
                </div>
                <div class="orbit">
                    <div class="orbit-dot" style="bottom: 0; left: 50%; transform: translate(-50%, 50%);"></div>
                </div>
                <div class="orbit">
                    <div class="orbit-dot" style="top: 50%; right: 0; transform: translate(50%, -50%);"></div>
                </div>
                <div class="center-icon">🚀</div>
            </div>

            <h2 class="brand-title">Start Your Virtual Office</h2>
            <p class="brand-description">
                Create your workspace, invite your team, and transform the way you
                collaborate — all from your browser.
            </p>

            <div class="brand-features">
                <div class="brand-feature">
                    <div class="brand-feature-icon">⚡</div>
                    <span class="brand-feature-text">Instant Setup</span>
                </div>
                <div class="brand-feature">
                    <div class="brand-feature-icon">👥</div>
                    <span class="brand-feature-text">Team Ready</span>
                </div>
                <div class="brand-feature">
                    <div class="brand-feature-icon">🔒</div>
                    <span class="brand-feature-text">Secure</span>
                </div>
                <div class="brand-feature">
                    <div class="brand-feature-icon">🆓</div>
                    <span class="brand-feature-text">Free Plan</span>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('styles')
<style>
    .password-strength {
        height: 3px;
        border-radius: 2px;
        margin-top: 8px;
        transition: all 0.3s;
        background: var(--border-color);
    }
    .password-strength.weak { background: linear-gradient(90deg, var(--error) 33%, transparent 33%); }
    .password-strength.medium { background: linear-gradient(90deg, var(--warning) 66%, transparent 66%); }
    .password-strength.strong { background: var(--success); }
</style>
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

    // Password strength indicator
    document.getElementById('password').addEventListener('input', function() {
        const bar = document.getElementById('passwordStrength');
        const val = this.value;
        bar.className = 'password-strength';

        if (val.length === 0) {
            bar.className = 'password-strength';
        } else if (val.length < 6) {
            bar.classList.add('weak');
        } else if (val.length < 10 || !/[A-Z]/.test(val) || !/[0-9]/.test(val)) {
            bar.classList.add('medium');
        } else {
            bar.classList.add('strong');
        }
    });

    document.getElementById('registerForm').addEventListener('submit', function() {
        const btn = document.getElementById('registerBtn');
        btn.classList.add('btn-loading');
        btn.disabled = true;
    });
</script>
@endsection
