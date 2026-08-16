@extends('layouts.auth')

@section('title', 'Login — Virtual Workplace')

@section('content')
<div class="auth-wrapper">
    <!-- Left: Login Form -->
    <div class="auth-left">
        <div class="auth-card">
            <div class="auth-logo">
                <div class="logo-icon">🏢</div>
                <span class="logo-text">Virtual Workplace</span>
            </div>

            <h1 class="auth-title">Welcome back</h1>
            <p class="auth-subtitle">Sign in to your account to access your virtual office</p>

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

            @if(session('success'))
                <div class="alert alert-success">
                    <span>✅</span>
                    <span>{{ session('success') }}</span>
                </div>
            @endif

            <form method="POST" action="{{ route('login.submit') }}" id="loginForm">
                @csrf

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
                            placeholder="Enter your password"
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
                        Remember me
                    </label>
                    <a href="#" class="form-link">Forgot password?</a>
                </div>

                <button type="submit" class="btn btn-primary" id="loginBtn">
                    <span class="btn-text">Sign In</span>
                    <div class="spinner"></div>
                </button>
            </form>

            <div class="auth-footer">
                Don't have an account? <a href="{{ route('register') }}">Create one</a>
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
                <div class="center-icon">🏢</div>
            </div>

            <h2 class="brand-title">Your Virtual Office Awaits</h2>
            <p class="brand-description">
                Step into a persistent, spatial workspace where your team connects naturally
                — just like a real office, but without walls.
            </p>

            <div class="brand-features">
                <div class="brand-feature">
                    <div class="brand-feature-icon">🎤</div>
                    <span class="brand-feature-text">Spatial Audio</span>
                </div>
                <div class="brand-feature">
                    <div class="brand-feature-icon">🗺️</div>
                    <span class="brand-feature-text">Virtual Maps</span>
                </div>
                <div class="brand-feature">
                    <div class="brand-feature-icon">💬</div>
                    <span class="brand-feature-text">Live Chat</span>
                </div>
                <div class="brand-feature">
                    <div class="brand-feature-icon">📊</div>
                    <span class="brand-feature-text">Analytics</span>
                </div>
            </div>
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
