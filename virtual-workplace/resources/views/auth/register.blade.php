@extends('layouts.auth')

@section('title', __('إنشاء حساب') . ' — UlaSpace')

@section('styles')
<style>
    .ula-plan-radio-box {
        padding: 14px;
        border-radius: 14px;
        border: var(--ula-border-width-hairline) solid var(--ula-border-default);
        background: var(--ula-surface-page);
        display: flex;
        flex-direction: column;
        gap: 8px;
        cursor: pointer;
        transition: all var(--ula-duration-fast) var(--ula-ease-out);
        position: relative;
    }

    .ula-plan-radio-box.selected {
        border: 2px solid var(--ula-accent-default);
        background: var(--ula-palm-50);
    }

    .ula-plan-radio-circle {
        width: 18px;
        height: 18px;
        border-radius: var(--ula-radius-pill);
        border: 1.5px solid var(--ula-border-strong);
        box-sizing: border-box;
        transition: all var(--ula-duration-fast) var(--ula-ease-out);
    }

    .ula-plan-radio-box.selected .ula-plan-radio-circle {
        border: 6px solid var(--ula-accent-default);
    }
</style>
@endsection

@section('content')
<div class="ula-auth-container register-layout">
    <!-- Left: Register Form -->
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

        <div style="display: flex; flex-direction: column; gap: 24px;">
            <!-- Heading -->
            <div style="display: flex; flex-direction: column; gap: 4px;">
                <h1 style="font-size: 34px; font-weight: 600; line-height: 1.25; color: var(--ula-text-primary);">أنشئ حسابك</h1>
                <span style="font-family: var(--ula-font-en); font-size: 18px; font-weight: 300; color: var(--ula-text-secondary);">Create your account</span>
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

            <form method="POST" action="{{ route('register.submit') }}" id="registerForm" style="display: flex; flex-direction: column; gap: 20px;">
                @csrf

                <!-- Form Fields Grid (2 columns) -->
                <div style="display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 16px;">
                    <!-- Full Name -->
                    <div class="ula-field-group">
                        <label class="ula-field-label" for="name">{{ __('الاسم الكامل') }}</label>
                        <div class="ula-input-box">
                            <span class="ms" style="font-size: 20px;">person</span>
                            <input
                                type="text"
                                id="name"
                                name="name"
                                class="ula-input-control"
                                placeholder="{{ __('أدخل اسمك الكامل') }}"
                                value="{{ old('name') }}"
                                required
                                autocomplete="name"
                            >
                        </div>
                    </div>

                    <!-- Email Address -->
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

                    <!-- Password -->
                    <div class="ula-field-group">
                        <label class="ula-field-label" for="password">{{ __('كلمة المرور') }}</label>
                        <div class="ula-input-box">
                            <span class="ms" style="font-size: 20px;">lock</span>
                            <input
                                type="password"
                                id="password"
                                name="password"
                                class="ula-input-control"
                                placeholder="{{ __('أنشئ كلمة مرور') }}"
                                required
                                minlength="8"
                                autocomplete="new-password"
                            >
                            <button type="button" class="ula-password-toggle-btn" onclick="togglePassword('password', this)" aria-label="Toggle password visibility">
                                <span class="ms" style="font-size: 20px;">visibility</span>
                            </button>
                        </div>
                    </div>

                    <!-- Confirm Password -->
                    <div class="ula-field-group">
                        <label class="ula-field-label" for="password_confirmation">{{ __('تأكيد كلمة المرور') }}</label>
                        <div class="ula-input-box">
                            <span class="ms" style="font-size: 20px;">lock</span>
                            <input
                                type="password"
                                id="password_confirmation"
                                name="password_confirmation"
                                class="ula-input-control"
                                placeholder="{{ __('أكّد كلمة المرور') }}"
                                required
                                minlength="8"
                                autocomplete="new-password"
                            >
                        </div>
                    </div>

                    <!-- Company Name (Full width in grid) -->
                    <div class="ula-field-group" style="grid-column: span 2;">
                        <label class="ula-field-label" for="organization_name">{{ __('اسم الشركة أو الفريق') }}</label>
                        <div class="ula-input-box">
                            <span class="ms" style="font-size: 20px;">apartment</span>
                            <input
                                type="text"
                                id="organization_name"
                                name="organization_name"
                                class="ula-input-control"
                                placeholder="{{ __('اسم شركتك أو فريقك') }}"
                                value="{{ old('organization_name') }}"
                                required
                            >
                        </div>
                    </div>
                </div>

                <!-- Choose Subscription Plan -->
                <div style="display: flex; flex-direction: column; gap: 10px;">
                    <span class="ula-field-label">{{ __('اختر باقة الاشتراك') }}</span>
                    <input type="hidden" name="plan_id" id="selectedPlanId" value="{{ $plans->first()?->id }}">

                    @php
                        $planNameAr = ['Free' => 'مجاني', 'Starter' => 'مبتدئ', 'Business' => 'أعمال', 'Enterprise' => 'مؤسسات'];
                        $defaultPlanSlug = request('plan', 'business');
                    @endphp

                    <div style="display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 12px;">
                        @foreach($plans as $index => $plan)
                            @php
                                $isSelected = (request('plan') && $plan->slug === request('plan')) || (!request('plan') && ($plan->slug === 'business' || $index === 0));
                            @endphp
                            <div
                                class="ula-plan-radio-box {{ $isSelected ? 'selected' : '' }}"
                                onclick="selectPlan('{{ $plan->id }}', this)"
                            >
                                <div style="display: flex; align-items: center; justify-content: space-between;">
                                    <span style="font-size: 15px; font-weight: 600;">{{ $planNameAr[$plan->name] ?? $plan->name }}</span>
                                    <span class="ula-plan-radio-circle"></span>
                                </div>
                                <span style="font-family: var(--ula-font-mono); font-size: 20px; font-weight: 500; direction: ltr; unicode-bidi: isolate; text-align: end; color: var(--ula-text-primary);">${{ $plan->price }}</span>
                                <span style="font-size: 12px; color: var(--ula-text-secondary);">
                                    @if($plan->seat_limit === 0)
                                        غير محدود
                                    @else
                                        {{ $plan->seat_limit }} مقعداً
                                    @endif
                                </span>
                            </div>
                        @endforeach
                    </div>
                </div>

                <!-- Submit Button -->
                <button type="submit" class="ula-btn-auth-submit" id="registerBtn">
                    {{ __('إنشاء الحساب') }}
                </button>
            </form>

            <div class="ula-auth-switch-text">
                {{ __('لديك حساب بالفعل؟') }} <a href="{{ route('login') }}">{{ __('سجّل الدخول') }}</a>
            </div>
        </div>
    </div>

    <!-- Right: Branding Hero Panel with Quote -->
    <div class="ula-auth-hero-side">
        <div class="ula-auth-hero-stripes"></div>
        <div class="ula-auth-hero-scrim"></div>
        <div style="position: relative; z-index: 2; display: flex; flex-direction: column; gap: 6px; padding-inline-start: 16px; border-inline-start: 2px solid var(--ula-highlight-default);">
            <blockquote style="font-size: 26px; font-weight: 600; line-height: 1.4; color: var(--ula-text-on-dark); margin: 0;">
                "المكان ليس مجرد جدران، بل مساحة تلتقي فيها العقول."
            </blockquote>
            <span style="font-family: var(--ula-font-en); font-size: 14px; color: var(--ula-sand-400);">UlaSpace</span>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script nonce="{{ $cspNonce ?? '' }}">
    function selectPlan(planId, element) {
        document.getElementById('selectedPlanId').value = planId;
        document.querySelectorAll('.ula-plan-radio-box').forEach(el => el.classList.remove('selected'));
        element.classList.add('selected');
    }
</script>
@endsection
