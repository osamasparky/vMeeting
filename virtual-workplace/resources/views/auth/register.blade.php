@extends('layouts.auth')

@section('title', __('Register') . ' — Virtual Workplace')

@section('content')
<div style="position: absolute; top: 20px; inset-inline-end: 24px; z-index: 10;">
    @if(app()->getLocale() === 'ar')
        <a href="{{ route('lang.switch', 'en') }}" class="lang-switch-btn" style="background: #ffffff; border: 1px solid var(--border-color); color: var(--brand-navy); padding: 7px 14px; border-radius: 8px; font-size: 13px; text-decoration: none; font-weight: 800; box-shadow: var(--shadow-input);">🌐 English</a>
    @else
        <a href="{{ route('lang.switch', 'ar') }}" class="lang-switch-btn" style="background: #ffffff; border: 1px solid var(--border-color); color: var(--brand-navy); padding: 7px 14px; border-radius: 8px; font-size: 13px; text-decoration: none; font-weight: 800; box-shadow: var(--shadow-input);">🌐 العربية</a>
    @endif
</div>

<div class="auth-wrapper">
    <!-- Left: Register Form -->
    <div class="auth-left" style="max-width: 620px; margin: 0 auto; width: 100%;">
        <div class="auth-card" style="max-width: 580px;">
            <div class="auth-logo">
                <div class="logo-icon">🏢</div>
                <span class="logo-text">{{ __('Virtual Workplace') }}</span>
            </div>

            <h1 class="auth-title">{{ __('Create your account') }}</h1>
            <p class="auth-subtitle">{{ __('Join the future of remote work — set up your virtual office in minutes') }}</p>

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

            <form method="POST" action="{{ route('register.submit') }}" id="registerForm">
                @csrf

                <div class="form-group">
                    <label class="form-label" for="name">{{ __('Full Name') }}</label>
                    <div class="form-input-wrapper">
                        <input
                            type="text"
                            id="name"
                            name="name"
                            class="form-input"
                            placeholder="{{ __('Enter your full name') }}"
                            value="{{ old('name') }}"
                            required
                            autocomplete="name"
                        >
                        <span class="form-input-icon">👤</span>
                    </div>
                </div>

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

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px;">
                    <div class="form-group">
                        <label class="form-label" for="password">{{ __('Password') }}</label>
                        <div class="form-input-wrapper">
                            <input
                                type="password"
                                id="password"
                                name="password"
                                class="form-input"
                                placeholder="{{ __('Create password') }}"
                                required
                                minlength="8"
                                autocomplete="new-password"
                            >
                            <span class="form-input-icon">🔒</span>
                            <button type="button" class="password-toggle" onclick="togglePassword('password', this)">👁️</button>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="password_confirmation">{{ __('Confirm Password') }}</label>
                        <div class="form-input-wrapper">
                            <input
                                type="password"
                                id="password_confirmation"
                                name="password_confirmation"
                                class="form-input"
                                placeholder="{{ __('Confirm password') }}"
                                required
                                autocomplete="new-password"
                            >
                            <span class="form-input-icon">🔒</span>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label" for="organization_name">{{ __('Company Name') }}</label>
                    <div class="form-input-wrapper">
                        <input
                            type="text"
                            id="organization_name"
                            name="organization_name"
                            class="form-input"
                            placeholder="{{ __('Your company or team name') }}"
                            value="{{ old('organization_name') }}"
                            required
                        >
                        <span class="form-input-icon">🏛️</span>
                    </div>
                </div>

                <!-- Choose Subscription Plan (Seats) -->
                <div class="form-group" style="margin-top: 20px; margin-bottom: 24px;">
                    <label class="form-label">{{ __('Choose Subscription Plan') }}</label>
                    <input type="hidden" name="plan_id" id="selectedPlanId" value="{{ $plans->first()?->id }}">

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-top: 8px;">
                        @foreach($plans as $index => $plan)
                        <div
                            class="plan-card-opt {{ $index === 0 ? 'selected' : '' }}"
                            onclick="selectPlan('{{ $plan->id }}', this)"
                            style="border: 2px solid var(--border-color); background: #f8fafc; border-radius: 12px; padding: 12px; cursor: pointer; transition: all 0.2s;"
                        >
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 4px;">
                                <strong style="font-size: 13px; color: var(--brand-navy);">💎 {{ $plan->name }}</strong>
                                <span style="font-size: 11px; font-weight: 800; color: var(--brand-green);">${{ number_format($plan->price, 0) }}/mo</span>
                            </div>
                            <div style="font-size: 11px; color: var(--text-secondary); font-weight: 600;">
                                👥 <strong>{{ $plan->seat_limit === 0 ? __('Unlimited') : $plan->seat_limit }}</strong> {{ __('Seats') }}
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>

                <button type="submit" class="btn btn-primary" id="registerBtn" style="margin-top: 8px;">
                    <span class="btn-text">{{ __('Create Account') }}</span>
                    <div class="spinner"></div>
                </button>
            </form>

            <div class="auth-footer">
                {{ __('Already have an account?') }} <a href="{{ route('login') }}">{{ __('Sign in') }}</a>
            </div>
        </div>
    </div>
</div>
@endsection

@section('styles')
<style>
    .plan-card-opt.selected {
        border-color: var(--brand-teal) !important;
        background: rgba(0, 180, 179, 0.08) !important;
        box-shadow: 0 4px 12px rgba(0, 180, 179, 0.2);
    }
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

    function selectPlan(planId, element) {
        document.getElementById('selectedPlanId').value = planId;
        document.querySelectorAll('.plan-card-opt').forEach(el => el.classList.remove('selected'));
        element.classList.add('selected');
    }
</script>
@endsection
