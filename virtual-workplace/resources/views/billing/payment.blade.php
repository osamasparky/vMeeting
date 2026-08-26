@extends('layouts.auth')

@section('title', __('Bank Transfer Payment') . ' — ' . $plan->name . ' — Virtual Workplace')

@section('content')
<div style="position: absolute; top: 20px; inset-inline-end: 24px; z-index: 10; display: flex; gap: 10px; align-items: center;">
    @if(app()->getLocale() === 'ar')
        <a href="{{ route('lang.switch', 'en') }}" class="lang-switch-btn" style="background: #ffffff; border: 1px solid var(--border-color); color: var(--brand-navy); padding: 7px 14px; border-radius: 8px; font-size: 13px; text-decoration: none; font-weight: 800; box-shadow: var(--shadow-input);">🌐 English</a>
    @else
        <a href="{{ route('lang.switch', 'ar') }}" class="lang-switch-btn" style="background: #ffffff; border: 1px solid var(--border-color); color: var(--brand-navy); padding: 7px 14px; border-radius: 8px; font-size: 13px; text-decoration: none; font-weight: 800; box-shadow: var(--shadow-input);">🌐 العربية</a>
    @endif
    <a href="{{ route('dashboard') }}" style="background: rgba(36, 92, 58, 0.1); border: 1px solid var(--border-color); color: var(--brand-forest); padding: 7px 14px; border-radius: 8px; font-size: 13px; text-decoration: none; font-weight: 800;">
        🏠 {{ __('Return to Dashboard') }}
    </a>
</div>

<div class="auth-wrapper" style="min-height: 100vh; padding: 40px 20px; max-width: 1200px; margin: 0 auto; display: flex; flex-direction: column; justify-content: center;">
    <!-- Step Progress Flow -->
    <div style="display: flex; justify-content: center; align-items: center; gap: 16px; margin-bottom: 28px; flex-wrap: wrap;">
        <div style="display: flex; align-items: center; gap: 8px; opacity: 0.6;">
            <span style="width: 28px; height: 28px; border-radius: 50%; background: var(--brand-forest); color: white; display: flex; align-items: center; justify-content: center; font-size: 12px; font-weight: 800;">✓</span>
            <span style="font-size: 13px; font-weight: 800; color: var(--text-secondary);">1. {{ __('Choose Plan') }}</span>
        </div>
        <span style="color: var(--border-color); font-weight: 800;">➔</span>
        <div style="display: flex; align-items: center; gap: 8px;">
            <span style="width: 28px; height: 28px; border-radius: 50%; background: var(--accent-gradient); color: white; display: flex; align-items: center; justify-content: center; font-size: 12px; font-weight: 800; box-shadow: 0 4px 10px rgba(36,92,58,0.3);">2</span>
            <span style="font-size: 14px; font-weight: 900; color: var(--brand-forest);">2. {{ __('Bank Transfer Payment') }}</span>
        </div>
        <span style="color: var(--border-color); font-weight: 800;">➔</span>
        <div style="display: flex; align-items: center; gap: 8px; opacity: 0.5;">
            <span style="width: 28px; height: 28px; border-radius: 50%; background: var(--bg-surface-subtle); border: 1px solid var(--border-color); color: var(--text-muted); display: flex; align-items: center; justify-content: center; font-size: 12px; font-weight: 800;">3</span>
            <span style="font-size: 13px; font-weight: 700; color: var(--text-muted);">3. {{ __('SuperAdmin Review & Activation') }}</span>
        </div>
    </div>

    @if(session('info'))
        <div style="background: rgba(36, 92, 58, 0.12); border: 1px solid rgba(36, 92, 58, 0.3); border-radius: 12px; padding: 14px 20px; margin-bottom: 20px; font-size: 14px; font-weight: 800; color: var(--brand-forest); display: flex; align-items: center; gap: 10px;">
            <span>ℹ️</span> {{ session('info') }}
        </div>
    @endif

    @if($pendingRequest)
        <div style="background: rgba(214, 162, 58, 0.15); border: 1px solid rgba(214, 162, 58, 0.4); border-radius: 14px; padding: 18px 24px; margin-bottom: 24px; box-shadow: var(--shadow-card);">
            <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px;">
                <div style="display: flex; align-items: center; gap: 12px;">
                    <div style="font-size: 28px;">⏳</div>
                    <div>
                        <div style="font-size: 15px; font-weight: 900; color: #996D12;">{{ __('You have a pending subscription request for this plan') }}</div>
                        <div style="font-size: 12px; color: var(--text-secondary); margin-top: 2px;">
                            {{ __('Reference') }}: <strong style="font-family: monospace;">{{ $pendingRequest->transfer_reference }}</strong> — {{ __('Amount') }}: <strong>{{ number_format($pendingRequest->amount, 2) }} {{ $pendingRequest->currency }}</strong> ({{ $pendingRequest->created_at->diffForHumans() }})
                        </div>
                    </div>
                </div>
                <div style="display: flex; gap: 10px;">
                    <form method="POST" action="{{ route('subscription.payment.cancel', $pendingRequest->id) }}" onsubmit="return confirm('{{ __('Are you sure you want to cancel this pending request?') }}');">
                        @csrf
                        <button type="submit" style="background: white; border: 1px solid #D96B5F; color: #D96B5F; padding: 8px 16px; border-radius: 8px; font-size: 12px; font-weight: 800; cursor: pointer;">
                            ✕ {{ __('Cancel Request') }}
                        </button>
                    </form>
                    <a href="{{ route('dashboard') }}" style="background: #D6A23A; color: white; padding: 8px 16px; border-radius: 8px; font-size: 12px; font-weight: 800; text-decoration: none;">
                        ✓ {{ __('Go to Dashboard') }}
                    </a>
                </div>
            </div>
        </div>
    @endif

    <div style="display: grid; grid-template-columns: 1fr 1.2fr; gap: 24px; align-items: start;">
        <!-- Left: Plan Details & Bank Transfer Credentials -->
        <div style="display: flex; flex-direction: column; gap: 20px;">
            <!-- Plan Summary Card -->
            <div style="background: var(--bg-surface); border: 2px solid var(--brand-forest); border-radius: var(--radius-xl); padding: 24px; box-shadow: var(--shadow-card); position: relative;">
                <div style="position: absolute; top: -12px; inset-inline-end: 20px; background: var(--brand-forest); color: white; padding: 4px 14px; border-radius: 9999px; font-size: 11px; font-weight: 900; text-transform: uppercase;">
                    ⭐ {{ __('Target Subscription') }}
                </div>
                <div style="font-size: 11px; font-weight: 900; color: var(--brand-forest); text-transform: uppercase; margin-bottom: 4px;">{{ $plan->slug }}</div>
                <h2 style="font-size: 24px; font-weight: 900; color: var(--text-primary); margin-bottom: 8px;">💎 {{ $plan->name }} {{ __('Plan') }}</h2>
                <div style="display: flex; align-items: baseline; gap: 10px; margin-bottom: 16px; padding-bottom: 16px; border-bottom: 1px solid var(--border-color);">
                    <span style="font-size: 32px; font-weight: 900; color: var(--brand-forest);">
                        {{ number_format($priceSAR, 2) }} <span style="font-size: 15px; font-weight: 800; color: var(--text-secondary);">SAR (ر.س)</span>
                    </span>
                    <span style="font-size: 13px; font-weight: 700; color: var(--text-muted);">
                        (${{ number_format($priceUSD, 2) }} USD / {{ __('month') }})
                    </span>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-bottom: 16px;">
                    <div style="background: var(--bg-surface-subtle); padding: 10px 14px; border-radius: 10px; border: 1px solid var(--border-color);">
                        <div style="font-size: 10px; font-weight: 800; color: var(--text-muted); text-transform: uppercase;">👥 {{ __('Seats Capacity') }}</div>
                        <div style="font-size: 14px; font-weight: 900; color: var(--text-primary);">{{ $plan->seat_limit === 0 ? __('Unlimited Seats') : $plan->seat_limit . ' ' . __('Seats') }}</div>
                    </div>
                    <div style="background: var(--bg-surface-subtle); padding: 10px 14px; border-radius: 10px; border: 1px solid var(--border-color);">
                        <div style="font-size: 10px; font-weight: 800; color: var(--text-muted); text-transform: uppercase;">🏢 {{ __('Meeting Rooms') }}</div>
                        <div style="font-size: 14px; font-weight: 900; color: var(--text-primary);">{{ $plan->room_limit === 0 ? __('Unlimited') : $plan->room_limit . ' ' . __('Rooms') }}</div>
                    </div>
                    <div style="background: var(--bg-surface-subtle); padding: 10px 14px; border-radius: 10px; border: 1px solid var(--border-color);">
                        <div style="font-size: 10px; font-weight: 800; color: var(--text-muted); text-transform: uppercase;">💾 {{ __('Cloud Storage') }}</div>
                        <div style="font-size: 14px; font-weight: 900; color: var(--text-primary);">{{ $plan->storage_limit_gb === 0 ? __('Unlimited') : $plan->storage_limit_gb . ' GB' }}</div>
                    </div>
                    <div style="background: var(--bg-surface-subtle); padding: 10px 14px; border-radius: 10px; border: 1px solid var(--border-color);">
                        <div style="font-size: 10px; font-weight: 800; color: var(--text-muted); text-transform: uppercase;">⚡ {{ __('Activation') }}</div>
                        <div style="font-size: 14px; font-weight: 900; color: var(--brand-forest);">{{ __('Instant SuperAdmin Review') }}</div>
                    </div>
                </div>

                <div style="font-size: 12px; font-weight: 800; color: var(--text-secondary); margin-bottom: 8px;">✨ {{ __('Included Features') }}:</div>
                <div style="display: flex; flex-wrap: wrap; gap: 6px;">
                    @if(is_array($plan->features))
                        @foreach($plan->features as $feature)
                            <span style="background: rgba(36, 92, 58, 0.1); border: 1px solid rgba(36, 92, 58, 0.2); color: var(--brand-forest); padding: 3px 10px; border-radius: 6px; font-size: 11px; font-weight: 800;">
                                ✓ {{ str_replace('_', ' ', $feature) }}
                            </span>
                        @endforeach
                    @endif
                </div>
            </div>

            <!-- Official Bank Account Cards -->
            <div style="background: var(--bg-surface); border: 1px solid var(--border-color); border-radius: var(--radius-xl); padding: 22px; box-shadow: var(--shadow-card);">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
                    <h3 style="font-size: 16px; font-weight: 900; color: var(--text-primary); display: flex; align-items: center; gap: 8px;">
                        <span>🏦</span> {{ __('Official Bank Accounts for Wire Transfer') }}
                    </h3>
                    <span style="background: rgba(79, 155, 95, 0.15); color: #4F9B5F; font-size: 11px; font-weight: 900; padding: 4px 10px; border-radius: 9999px;">
                        ✓ {{ __('Verified Corporate') }}
                    </span>
                </div>

                <!-- Unique Payment Reference Notice -->
                <div style="background: #FFF9E6; border: 1px solid #E6D28C; border-radius: 12px; padding: 14px; margin-bottom: 16px;">
                    <div style="font-size: 11px; font-weight: 900; color: #8A6D1D; text-transform: uppercase; margin-bottom: 4px;">
                        ⚠️ {{ __('Important: Include Transfer Reference in Memo') }}
                    </div>
                    <div style="display: flex; justify-content: space-between; align-items: center; background: white; border: 1px dashed #C8B263; padding: 8px 12px; border-radius: 8px;">
                        <span style="font-family: monospace; font-size: 14px; font-weight: 900; color: #192D21;" id="refCodeText">{{ $referenceCode }}</span>
                        <button type="button" onclick="copyToClipboard('{{ $referenceCode }}', this)" style="background: #245C3A; color: white; border: none; padding: 5px 12px; border-radius: 6px; font-size: 11px; font-weight: 800; cursor: pointer;">
                            📋 {{ __('Copy Code') }}
                        </button>
                    </div>
                    <div style="font-size: 11px; color: #6B5416; margin-top: 6px;">
                        {{ __('Please write this reference code in the bank transfer remarks / description to accelerate your activation.') }}
                    </div>
                </div>

                <!-- Bank Accounts List -->
                <div style="display: flex; flex-direction: column; gap: 14px;">
                    @foreach($bankAccounts as $bank)
                    <div style="background: var(--bg-surface-subtle); border: 1px solid var(--border-color); border-radius: 12px; padding: 16px;">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                            <strong style="font-size: 14px; color: var(--text-primary);">🏛️ {{ $bank['bank_name'] }}</strong>
                            <span style="font-size: 10px; font-weight: 800; background: rgba(36,92,58,0.1); color: var(--brand-forest); padding: 2px 8px; border-radius: 6px;">{{ $bank['badge'] }}</span>
                        </div>
                        <div style="font-size: 12px; color: var(--text-secondary); margin-bottom: 6px;">
                            <strong>{{ __('Beneficiary') }}:</strong> {{ app()->getLocale() === 'ar' ? $bank['account_name'] : $bank['account_name_en'] }}
                        </div>
                        <div style="margin-bottom: 6px;">
                            <div style="font-size: 11px; color: var(--text-muted); font-weight: 700;">IBAN:</div>
                            <div style="display: flex; justify-content: space-between; align-items: center; background: var(--bg-surface); border: 1px solid var(--border-color); padding: 6px 10px; border-radius: 6px; margin-top: 2px;">
                                <span style="font-family: monospace; font-size: 12px; font-weight: 800; color: var(--brand-forest); letter-spacing: 0.5px;">{{ $bank['iban'] }}</span>
                                <button type="button" onclick="copyToClipboard('{{ $bank['iban'] }}', this)" style="background: none; border: 1px solid var(--border-color); padding: 3px 8px; border-radius: 4px; font-size: 10px; font-weight: 800; cursor: pointer; color: var(--text-primary);">
                                    📋 {{ __('Copy') }}
                                </button>
                            </div>
                        </div>
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 8px; font-size: 11px; color: var(--text-secondary);">
                            <div><strong>{{ __('Account #') }}:</strong> <span style="font-family: monospace;">{{ $bank['account_number'] ?? '—' }}</span></div>
                            <div><strong>SWIFT / BIC:</strong> <span style="font-family: monospace;">{{ $bank['swift_code'] ?? $bank['swift'] ?? '—' }}</span></div>
                        </div>
                    </div>
                    @endforeach
                </div>

                @if(!empty($paymentSettings['instapay_handle']) || !empty($paymentSettings['stc_pay_phone']) || !empty($paymentSettings['vodafone_cash_phone']))
                    <div style="margin-top: 18px; padding-top: 16px; border-top: 1px dashed var(--border-color);">
                        <div style="font-size: 13px; font-weight: 800; color: var(--text-primary); margin-bottom: 10px; display: flex; align-items: center; gap: 6px;">
                            <span>📱</span> {{ __('Instant Payment & Digital Wallets (الدفع الفوري والمحافظ)') }}
                        </div>
                        <div style="display: flex; flex-direction: column; gap: 8px; font-size: 12px;">
                            @if(!empty($paymentSettings['instapay_handle']))
                                <div style="display: flex; justify-content: space-between; align-items: center; background: var(--bg-surface-subtle); padding: 8px 12px; border-radius: 8px; border: 1px solid var(--border-color);">
                                    <span>⚡ <strong>Instapay IPA:</strong> <code style="color: var(--brand-forest); font-weight: 800;">{{ $paymentSettings['instapay_handle'] }}</code></span>
                                    <button type="button" onclick="copyToClipboard('{{ $paymentSettings['instapay_handle'] }}', this)" style="background: none; border: 1px solid var(--border-color); padding: 2px 6px; border-radius: 4px; font-size: 10px; cursor: pointer;">📋 {{ __('Copy') }}</button>
                                </div>
                            @endif
                            @if(!empty($paymentSettings['stc_pay_phone']))
                                <div style="display: flex; justify-content: space-between; align-items: center; background: var(--bg-surface-subtle); padding: 8px 12px; border-radius: 8px; border: 1px solid var(--border-color);">
                                    <span>💚 <strong>STC Pay:</strong> <code style="color: var(--brand-forest); font-weight: 800;">{{ $paymentSettings['stc_pay_phone'] }}</code></span>
                                    <button type="button" onclick="copyToClipboard('{{ $paymentSettings['stc_pay_phone'] }}', this)" style="background: none; border: 1px solid var(--border-color); padding: 2px 6px; border-radius: 4px; font-size: 10px; cursor: pointer;">📋 {{ __('Copy') }}</button>
                                </div>
                            @endif
                            @if(!empty($paymentSettings['vodafone_cash_phone']))
                                <div style="display: flex; justify-content: space-between; align-items: center; background: var(--bg-surface-subtle); padding: 8px 12px; border-radius: 8px; border: 1px solid var(--border-color);">
                                    <span>🔴 <strong>Vodafone Cash:</strong> <code style="color: var(--brand-forest); font-weight: 800;">{{ $paymentSettings['vodafone_cash_phone'] }}</code></span>
                                    <button type="button" onclick="copyToClipboard('{{ $paymentSettings['vodafone_cash_phone'] }}', this)" style="background: none; border: 1px solid var(--border-color); padding: 2px 6px; border-radius: 4px; font-size: 10px; cursor: pointer;">📋 {{ __('Copy') }}</button>
                                </div>
                            @endif
                        </div>
                    </div>
                @endif
            </div>

            @if(!empty($paymentSettings['checkout_terms_ar']) || !empty($paymentSettings['checkout_terms_en']))
                <div style="background: var(--bg-surface-subtle); border: 1px solid var(--border-color); border-radius: var(--radius-lg); padding: 14px 18px; font-size: 12px; color: var(--text-secondary); line-height: 1.5;">
                    📌 <strong>{{ __('Terms & Activation Policy') }}:</strong>
                    {{ app()->getLocale() === 'ar' ? ($paymentSettings['checkout_terms_ar'] ?? $paymentSettings['checkout_terms_en']) : ($paymentSettings['checkout_terms_en'] ?? $paymentSettings['checkout_terms_ar']) }}
                </div>
            @endif
        </div>

        <!-- Right: Submit Bank Transfer Form -->
        <div style="background: var(--bg-surface); border: 1px solid var(--border-color); border-radius: var(--radius-xl); padding: 28px; box-shadow: var(--shadow-card);">
            <div style="margin-bottom: 20px; border-bottom: 1px solid var(--border-color); padding-bottom: 16px;">
                <h2 style="font-size: 20px; font-weight: 900; color: var(--text-primary); margin-bottom: 4px;">
                    📝 {{ __('Submit Bank Transfer Details & Receipt') }}
                </h2>
                <p style="font-size: 13px; color: var(--text-secondary);">
                    {{ __('After completing your transfer, fill out the form below with your payment reference and attach the receipt slip.') }}
                </p>
            </div>

            @if($errors->any())
                <div class="alert alert-error" style="margin-bottom: 20px; background: rgba(217, 107, 95, 0.15); border: 1px solid rgba(217, 107, 95, 0.35); color: #D96B5F; padding: 12px 16px; border-radius: 10px; font-size: 13px; font-weight: 700;">
                    <div style="font-weight: 900; margin-bottom: 4px;">⚠️ {{ __('Please correct the errors below') }}:</div>
                    <ul style="padding-inline-start: 20px;">
                        @foreach($errors->all() as $err)
                            <li>{{ $err }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('subscription.payment.submit', $plan->id) }}" enctype="multipart/form-data" id="bankPaymentForm">
                @csrf

                <!-- Company & Plan Summary Header in form -->
                <div style="background: var(--bg-surface-subtle); border: 1px solid var(--border-color); border-radius: 12px; padding: 14px; margin-bottom: 20px; display: flex; justify-content: space-between; align-items: center;">
                    <div>
                        <div style="font-size: 10px; font-weight: 800; color: var(--text-muted); text-transform: uppercase;">{{ __('Subscribing Company') }}</div>
                        <div style="font-size: 14px; font-weight: 900; color: var(--text-primary);">🏛️ {{ $organization->name }}</div>
                    </div>
                    <div style="text-align: end;">
                        <div style="font-size: 10px; font-weight: 800; color: var(--text-muted); text-transform: uppercase;">{{ __('Target Plan') }}</div>
                        <div style="font-size: 14px; font-weight: 900; color: var(--brand-forest);">💎 {{ $plan->name }}</div>
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 16px;">
                    <div class="form-group">
                        <label class="form-label" style="font-size: 12px; font-weight: 800; margin-bottom: 6px; display: block;" for="sender_name">
                            {{ __('Sender Full Name / Company Name') }} <span style="color: #D96B5F;">*</span>
                        </label>
                        <input
                            type="text"
                            id="sender_name"
                            name="sender_name"
                            class="form-input"
                            style="width: 100%;"
                            placeholder="{{ __('e.g. John Doe / Acme Corp') }}"
                            value="{{ old('sender_name', $user->name) }}"
                            required
                        >
                    </div>

                    <div class="form-group">
                        <label class="form-label" style="font-size: 12px; font-weight: 800; margin-bottom: 6px; display: block;" for="bank_name">
                            {{ __('Transferred From (Your Bank)') }} <span style="color: #D96B5F;">*</span>
                        </label>
                        <input
                            type="text"
                            id="bank_name"
                            name="bank_name"
                            class="form-input"
                            style="width: 100%;"
                            placeholder="{{ __('e.g. Al Rajhi / SNB / Riyad Bank') }}"
                            value="{{ old('bank_name') }}"
                            required
                        >
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 16px;">
                    <div class="form-group">
                        <label class="form-label" style="font-size: 12px; font-weight: 800; margin-bottom: 6px; display: block;" for="transfer_reference">
                            {{ __('Bank Transaction / Transfer Ref #') }} <span style="color: #D96B5F;">*</span>
                        </label>
                        <input
                            type="text"
                            id="transfer_reference"
                            name="transfer_reference"
                            class="form-input"
                            style="width: 100%; font-family: monospace;"
                            placeholder="{{ __('e.g. TRF-10928374 or') }} {{ $referenceCode }}"
                            value="{{ old('transfer_reference', $referenceCode) }}"
                            required
                        >
                    </div>

                    <div class="form-group">
                        <label class="form-label" style="font-size: 12px; font-weight: 800; margin-bottom: 6px; display: block;" for="transfer_date">
                            {{ __('Transfer Date') }} <span style="color: #D96B5F;">*</span>
                        </label>
                        <input
                            type="date"
                            id="transfer_date"
                            name="transfer_date"
                            class="form-input"
                            style="width: 100%;"
                            value="{{ old('transfer_date', date('Y-m-d')) }}"
                            required
                        >
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1.2fr 1fr; gap: 16px; margin-bottom: 16px;">
                    <div class="form-group">
                        <label class="form-label" style="font-size: 12px; font-weight: 800; margin-bottom: 6px; display: block;" for="amount">
                            {{ __('Transferred Amount') }} <span style="color: #D96B5F;">*</span>
                        </label>
                        <div style="display: flex; gap: 8px;">
                            <input
                                type="number"
                                step="0.01"
                                id="amount"
                                name="amount"
                                class="form-input"
                                style="flex: 1; font-weight: 900;"
                                value="{{ old('amount', $priceSAR) }}"
                                required
                            >
                            <select name="currency" class="form-input" style="width: 90px; font-weight: 800;">
                                <option value="SAR" selected>SAR (ر.س)</option>
                                <option value="USD">USD ($)</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label" style="font-size: 12px; font-weight: 800; margin-bottom: 6px; display: block;" for="billing_cycle">
                            {{ __('Billing Cycle') }} <span style="color: #D96B5F;">*</span>
                        </label>
                        <select name="billing_cycle" id="billing_cycle" class="form-input" style="width: 100%; font-weight: 800;">
                            <option value="monthly" selected>{{ __('Monthly (1 Month)') }}</option>
                            <option value="yearly">{{ __('Yearly (12 Months)') }}</option>
                        </select>
                    </div>
                </div>

                <div class="form-group" style="margin-bottom: 16px;">
                    <label class="form-label" style="font-size: 12px; font-weight: 800; margin-bottom: 6px; display: block;" for="sender_account">
                        {{ __('Sender IBAN or Account Number (Optional)') }}
                    </label>
                    <input
                        type="text"
                        id="sender_account"
                        name="sender_account"
                        class="form-input"
                        style="width: 100%; font-family: monospace;"
                        placeholder="{{ __('e.g. SA0000000000000000000000') }}"
                        value="{{ old('sender_account') }}"
                    >
                </div>

                <!-- Receipt Upload Box (Drag & Drop) -->
                <div class="form-group" style="margin-bottom: 20px;">
                    <label class="form-label" style="font-size: 12px; font-weight: 800; margin-bottom: 6px; display: block;" for="receipt">
                        📎 {{ __('Bank Transfer Slip / Deposit Receipt') }} <span style="color: #D96B5F;">*</span>
                    </label>
                    <div
                        id="dropZone"
                        onclick="document.getElementById('receipt').click()"
                        style="border: 2px dashed var(--brand-forest); background: var(--bg-surface-subtle); border-radius: 14px; padding: 24px; text-align: center; cursor: pointer; transition: all 0.2s;"
                    >
                        <div style="font-size: 32px; margin-bottom: 8px;">📄</div>
                        <div style="font-size: 13px; font-weight: 800; color: var(--text-primary);" id="dropZoneText">
                            {{ __('Click to select receipt or drag & drop file here') }}
                        </div>
                        <div style="font-size: 11px; color: var(--text-muted); margin-top: 4px;">
                            {{ __('Supported formats: JPG, PNG, WEBP, PDF (Max 15MB)') }}
                        </div>
                        <input
                            type="file"
                            id="receipt"
                            name="receipt"
                            accept="image/jpeg,image/png,image/webp,application/pdf"
                            style="display: none;"
                            onchange="handleReceiptFileSelected(this)"
                            required
                        >
                    </div>
                </div>

                <div class="form-group" style="margin-bottom: 24px;">
                    <label class="form-label" style="font-size: 12px; font-weight: 800; margin-bottom: 6px; display: block;" for="notes">
                        💬 {{ __('Additional Remarks / Notes (Optional)') }}
                    </label>
                    <textarea
                        id="notes"
                        name="notes"
                        rows="2"
                        class="form-input"
                        style="width: 100%; resize: vertical;"
                        placeholder="{{ __('Any notes for the SuperAdmin team regarding this transfer...') }}"
                    >{{ old('notes') }}</textarea>
                </div>

                <button
                    type="submit"
                    id="submitBtn"
                    class="btn btn-primary"
                    style="width: 100%; padding: 14px; font-size: 15px; font-weight: 900; display: flex; align-items: center; justify-content: center; gap: 10px; border-radius: 12px;"
                >
                    <span>🚀</span> {{ __('Submit Bank Transfer for SuperAdmin Approval') }}
                </button>
            </form>
        </div>
    </div>
</div>

<script>
function copyToClipboard(text, btn) {
    if (navigator.clipboard && window.isSecureContext) {
        navigator.clipboard.writeText(text).then(() => showCopiedFeedback(btn));
    } else {
        const textarea = document.createElement('textarea');
        textarea.value = text;
        textarea.style.position = 'fixed';
        textarea.style.left = '-9999px';
        document.body.appendChild(textarea);
        textarea.focus();
        textarea.select();
        try {
            document.execCommand('copy');
            showCopiedFeedback(btn);
        } catch (err) {
            console.error('Copy failed', err);
        }
        document.body.removeChild(textarea);
    }
}

function showCopiedFeedback(btn) {
    const originalText = btn.innerHTML;
    btn.innerHTML = '✓ {{ __("Copied!") }}';
    btn.style.background = '#4F9B5F';
    btn.style.color = 'white';
    setTimeout(() => {
        btn.innerHTML = originalText;
        btn.style.background = '';
        btn.style.color = '';
    }, 2000);
}

function handleReceiptFileSelected(input) {
    if (input.files && input.files[0]) {
        const file = input.files[0];
        const dropZoneText = document.getElementById('dropZoneText');
        dropZoneText.innerHTML = '✅ <strong>' + file.name + '</strong> (' + (file.size / 1024 / 1024).toFixed(2) + ' MB)';
        document.getElementById('dropZone').style.borderColor = '#4F9B5F';
        document.getElementById('dropZone').style.background = 'rgba(79, 155, 95, 0.1)';
    }
}

const dropZone = document.getElementById('dropZone');
if (dropZone) {
    ['dragenter', 'dragover'].forEach(eventName => {
        dropZone.addEventListener(eventName, (e) => {
            e.preventDefault();
            e.stopPropagation();
            dropZone.style.borderColor = '#4F9B5F';
            dropZone.style.background = 'rgba(79, 155, 95, 0.15)';
        }, false);
    });

    ['dragleave', 'drop'].forEach(eventName => {
        dropZone.addEventListener(eventName, (e) => {
            e.preventDefault();
            e.stopPropagation();
            dropZone.style.borderColor = '';
            dropZone.style.background = '';
        }, false);
    });

    dropZone.addEventListener('drop', (e) => {
        const dt = e.dataTransfer;
        const files = dt.files;
        if (files && files.length) {
            document.getElementById('receipt').files = files;
            handleReceiptFileSelected(document.getElementById('receipt'));
        }
    });
}
</script>
@endsection
