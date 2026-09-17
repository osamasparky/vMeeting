@extends('layouts.auth')

@section('title', __('Register') . ' — UlaSpace')

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
    <!-- Left: Register Form -->
    <div class="auth-left" style="max-width: 640px; margin: 0 auto; width: 100%;">
        <div class="auth-card" style="max-width: 580px;">
            <div class="auth-logo">
                <div class="logo-icon" style="background: var(--ula-palm-900, #142B24); border-radius: 12px; width: 44px; height: 44px; display: flex; align-items: center; justify-content: center; box-shadow: var(--ula-shadow-sm); padding: 6px;">
                    <img src="{{ asset('images/ulaspace-icon.png') }}" alt="UlaSpace" style="width: 26px; height: auto; object-fit: contain;">
                </div>
                <div>
                    <span class="logo-text" style="display: block; line-height: 1.1; font-weight: 800;">UlaSpace</span>
                    <span style="font-size: 10px; font-weight: 700; color: var(--ula-text-secondary); letter-spacing: 0.5px; text-transform: uppercase;">{{ __('Virtual Workplace') }}</span>
                </div>
            </div>

            <h1 class="auth-title">{{ __('Create your account') }}</h1>
            <p class="auth-subtitle">{{ __('Join the future of remote work — set up your virtual office in minutes') }}</p>

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
                        <span class="form-input-icon">
                            <span class="material-symbols-rounded">person</span>
                        </span>
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
                        <span class="form-input-icon">
                            <span class="material-symbols-rounded">mail</span>
                        </span>
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
                            <span class="form-input-icon">
                                <span class="material-symbols-rounded">lock</span>
                            </span>
                            <button type="button" class="password-toggle" onclick="togglePassword('password', this)" aria-label="Toggle password visibility">
                                <span class="material-symbols-rounded">visibility</span>
                            </button>
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
                            <span class="form-input-icon">
                                <span class="material-symbols-rounded">lock</span>
                            </span>
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
                        <span class="form-input-icon">
                            <span class="material-symbols-rounded">corporate_fare</span>
                        </span>
                    </div>
                </div>

                <!-- Choose Subscription Plan (Seats) -->
                <div class="form-group" style="margin-top: 20px; margin-bottom: 24px;">
                    <label class="form-label">{{ __('Choose Subscription Plan') }}</label>
                    <input type="hidden" name="plan_id" id="selectedPlanId" value="{{ $plans->first()?->id }}">

                    @php
                        $planNameAr = ['Free' => 'مجاني', 'Starter' => 'مبتدئ', 'Business' => 'أعمال', 'Enterprise' => 'مؤسسات'];
                    @endphp
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-top: 8px;">
                        @foreach($plans as $index => $plan)
                        <div
                            class="plan-card-opt {{ $index === 0 ? 'selected' : '' }}"
                            onclick="selectPlan('{{ $plan->id }}', this)"
                            style="border: 1.5px solid var(--ula-border-subtle); background: var(--ula-surface-page); border-radius: var(--ula-radius-md, 12px); padding: 12px; cursor: pointer; transition: all 0.2s;"
                        >
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 4px;">
                                <div style="display: flex; align-items: center; gap: 6px;">
                                    <span class="material-symbols-rounded" style="font-size: 16px; color: var(--ula-palm-700, #1E412F);">verified</span>
                                    <span class="ula-headline-group">
                                        <span class="ula-headline-ar" style="font-size: 13px; font-weight: 700;">{{ $planNameAr[$plan->name] ?? $plan->name }}</span>
                                        <span class="ula-headline-en" style="font-size: 10px;">{{ $plan->name }}</span>
                                    </span>
                                </div>
                                <span style="font-size: 12px; font-weight: 700; font-family: var(--ula-font-mono); color: var(--ula-status-success); direction: ltr; unicode-bidi: isolate;">${{ number_format($plan->price, 0) }}/mo</span>
                            </div>
                            <div style="display: flex; align-items: center; gap: 4px; font-size: 11px; color: var(--ula-text-secondary); font-weight: 500;">
                                <span class="material-symbols-rounded" style="font-size: 14px;">group</span>
                                <span>
                                    @if($plan->seat_limit === 0)
                                        <strong>{{ __('غير محدود') }}</strong>
                                    @else
                                        <strong style="font-family: var(--ula-font-mono); direction: ltr; unicode-bidi: isolate; display: inline-block;">{{ $plan->seat_limit }}</strong> {{ __('مقعداً') }}
                                    @endif
                                </span>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>

                <button type="submit" class="nx-btn nx-btn--primary" id="registerBtn" style="width: 100%; justify-content: center; padding: 12px 20px; font-weight: 700; margin-top: 8px;">
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
        border-color: var(--ula-palm-700, #1E412F) !important;
        background: rgba(30, 65, 47, 0.06) !important;
        box-shadow: 0 0 0 1px var(--ula-palm-700, #1E412F);
    }
</style>
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

    function selectPlan(planId, element) {
        document.getElementById('selectedPlanId').value = planId;
        document.querySelectorAll('.plan-card-opt').forEach(el => el.classList.remove('selected'));
        element.classList.add('selected');
    }

    document.getElementById('registerForm').addEventListener('submit', function() {
        const btn = document.getElementById('registerBtn');
        btn.classList.add('btn-loading');
        btn.disabled = true;
    });
</script>
@endsection
