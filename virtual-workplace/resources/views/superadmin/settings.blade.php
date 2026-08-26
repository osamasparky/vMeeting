@extends('superadmin.layout')

@section('title', __('System Settings & Payment Configuration'))
@section('page_title', __('System Settings (إعدادات النظام وطرق الدفع)'))

@section('content')
<div style="display: flex; flex-direction: column; gap: 24px;">

    <!-- 1. Global SaaS Configuration -->
    <form method="POST" action="{{ route('superadmin.settings.update') }}">
        @csrf

        <div class="panel-card" style="border-radius: var(--radius-xl); padding: 28px;">
            <div class="panel-header" style="margin-bottom: 24px;">
                <div class="panel-title">
                    <span>🌐</span>
                    <span>{{ __('Global SaaS Configuration') }}</span>
                </div>
                <p class="panel-subtitle">{{ __('Configure core platform parameters, default registration tier, and real-time connectivity.') }}</p>
            </div>

            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 20px;">
                <div>
                    <label style="display: block; font-size: 11px; font-weight: 800; color: var(--text-secondary); margin-bottom: 8px; text-transform: uppercase;">
                        🏢 {{ __('Platform Name') }}
                    </label>
                    <input
                        type="text"
                        name="platform_name"
                        value="{{ $globalSettings['platform_name'] ?? 'Virtual Workplace SaaS' }}"
                        style="width: 100%; background: var(--bg-surface-subtle); border: 1px solid var(--border-color); border-radius: 10px; padding: 12px 14px; color: var(--text-primary); outline: none; font-size: 13px; font-weight: 600; box-shadow: var(--shadow-inset-3d);"
                    >
                </div>

                <div>
                    <label style="display: block; font-size: 11px; font-weight: 800; color: var(--text-secondary); margin-bottom: 8px; text-transform: uppercase;">
                        💎 {{ __('Default Registration Plan') }}
                    </label>
                    <select name="default_plan_id" style="width: 100%; background: var(--bg-surface-subtle); border: 1px solid var(--border-color); border-radius: 10px; padding: 12px 14px; color: var(--text-primary); outline: none; font-size: 13px; font-weight: 600; box-shadow: var(--shadow-inset-3d);">
                        @foreach($plans as $p)
                            <option value="{{ $p->id }}" {{ ($globalSettings['default_plan_id'] ?? '') == $p->id ? 'selected' : '' }}>
                                💎 {{ $p->name }} ({{ $p->seat_limit === 0 ? __('Unlimited') : $p->seat_limit }} {{ __('Users') }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label style="display: block; font-size: 11px; font-weight: 800; color: var(--text-secondary); margin-bottom: 8px; text-transform: uppercase;">
                        ⚡ {{ __('Realtime WebSocket URL') }}
                    </label>
                    <input
                        type="text"
                        name="ws_url"
                        value="{{ $globalSettings['ws_url'] ?? 'ws://127.0.0.1:8080' }}"
                        style="width: 100%; background: var(--bg-surface-subtle); border: 1px solid var(--border-color); border-radius: 10px; padding: 12px 14px; color: var(--brand-forest); outline: none; font-size: 13px; font-family: monospace; font-weight: 700; box-shadow: var(--shadow-inset-3d);"
                    >
                </div>

                <div>
                    <label style="display: block; font-size: 11px; font-weight: 800; color: var(--text-secondary); margin-bottom: 8px; text-transform: uppercase;">
                        📡 {{ __('STUN / TURN Server') }}
                    </label>
                    <input
                        type="text"
                        name="stun_server"
                        value="{{ $globalSettings['stun_server'] ?? 'stun:173.212.248.192:3478' }}"
                        style="width: 100%; background: var(--bg-surface-subtle); border: 1px solid var(--border-color); border-radius: 10px; padding: 12px 14px; color: var(--text-primary); outline: none; font-size: 13px; font-family: monospace; box-shadow: var(--shadow-inset-3d);"
                    >
                </div>
            </div>

            <div style="margin-top: 24px; display: flex; justify-content: flex-end;">
                <button type="submit" class="tactile-btn btn-primary" style="padding: 11px 24px; font-size: 13px;">
                    💾 {{ __('Save Global Settings') }}
                </button>
            </div>
        </div>
    </form>

    <!-- 2. Payment & Checkout Information Settings -->
    <form method="POST" action="{{ route('superadmin.settings.payment') }}" id="payment-settings-form">
        @csrf

        <div class="panel-card" style="border-radius: var(--radius-xl); padding: 28px;">
            <div class="panel-header" style="margin-bottom: 24px;">
                <div class="panel-title">
                    <span>💳</span>
                    <span>{{ __('Checkout & Payment Gateways Settings (بيانات وإعدادات الدفع)') }}</span>
                </div>
                <p class="panel-subtitle">{{ __('Configure payment methods, official bank accounts, Instapay / Wallets, currency rates, and terms displayed to users on the checkout page.') }}</p>
            </div>

            <!-- Currency & Rates -->
            <div style="margin-bottom: 24px;">
                <h4 style="font-size: 14px; font-weight: 800; color: var(--text-primary); margin-bottom: 12px;">
                    💱 {{ __('Currency Rates & Taxes (أسعار الصرف والضرائب)') }}
                </h4>
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 16px;">
                    <div>
                        <label style="display: block; font-size: 11px; font-weight: 700; color: var(--text-secondary); margin-bottom: 6px;">1 USD ➔ SAR (ر.س)</label>
                        <input type="number" step="0.01" name="usd_to_sar_rate" value="{{ $paymentSettings['usd_to_sar_rate'] ?? 3.75 }}" required class="form-input" style="width: 100%; font-weight: 700;">
                    </div>
                    <div>
                        <label style="display: block; font-size: 11px; font-weight: 700; color: var(--text-secondary); margin-bottom: 6px;">1 USD ➔ EGP (ج.م)</label>
                        <input type="number" step="0.01" name="usd_to_egp_rate" value="{{ $paymentSettings['usd_to_egp_rate'] ?? 48.5 }}" required class="form-input" style="width: 100%; font-weight: 700;">
                    </div>
                    <div>
                        <label style="display: block; font-size: 11px; font-weight: 700; color: var(--text-secondary); margin-bottom: 6px;">1 USD ➔ AED (د.إ)</label>
                        <input type="number" step="0.01" name="usd_to_aed_rate" value="{{ $paymentSettings['usd_to_aed_rate'] ?? 3.67 }}" required class="form-input" style="width: 100%; font-weight: 700;">
                    </div>
                    <div>
                        <label style="display: block; font-size: 11px; font-weight: 700; color: var(--text-secondary); margin-bottom: 6px;">{{ __('VAT / Tax Percentage (%)') }}</label>
                        <input type="number" step="0.1" name="tax_percentage" value="{{ $paymentSettings['tax_percentage'] ?? 15 }}" class="form-input" style="width: 100%; font-weight: 700;">
                    </div>
                    <div>
                        <label style="display: block; font-size: 11px; font-weight: 700; color: var(--text-secondary); margin-bottom: 6px;">{{ __('Tax Number / الرقم الضريبي') }}</label>
                        <input type="text" name="tax_number" value="{{ $paymentSettings['tax_number'] ?? '' }}" placeholder="300012345600003" class="form-input" style="width: 100%; font-family: monospace;">
                    </div>
                </div>
            </div>

            <!-- Instant Wallets & Digital Payment -->
            <div style="margin-bottom: 24px; padding-top: 16px; border-top: 1px solid var(--border-color);">
                <h4 style="font-size: 14px; font-weight: 800; color: var(--text-primary); margin-bottom: 12px;">
                    📱 {{ __('Instant Payment & Digital Wallets (إنستاباي والمحافظ الرقمية)') }}
                </h4>
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 16px;">
                    <div>
                        <label style="display: block; font-size: 11px; font-weight: 700; color: var(--text-secondary); margin-bottom: 6px;">⚡ Instapay Username / IPA Handle</label>
                        <input type="text" name="instapay_handle" value="{{ $paymentSettings['instapay_handle'] ?? '' }}" placeholder="nextspace@instapay" class="form-input" style="width: 100%; font-weight: 700;">
                    </div>
                    <div>
                        <label style="display: block; font-size: 11px; font-weight: 700; color: var(--text-secondary); margin-bottom: 6px;">📞 Instapay Mobile Number</label>
                        <input type="text" name="instapay_phone" value="{{ $paymentSettings['instapay_phone'] ?? '' }}" placeholder="+201000000000" class="form-input" style="width: 100%;">
                    </div>
                    <div>
                        <label style="display: block; font-size: 11px; font-weight: 700; color: var(--text-secondary); margin-bottom: 6px;">💚 STC Pay (Saudi Arabia)</label>
                        <input type="text" name="stc_pay_phone" value="{{ $paymentSettings['stc_pay_phone'] ?? '' }}" placeholder="+966500000000" class="form-input" style="width: 100%;">
                    </div>
                    <div>
                        <label style="display: block; font-size: 11px; font-weight: 700; color: var(--text-secondary); margin-bottom: 6px;">🔴 Vodafone Cash / Wallets (Egypt)</label>
                        <input type="text" name="vodafone_cash_phone" value="{{ $paymentSettings['vodafone_cash_phone'] ?? '' }}" placeholder="+201000000000" class="form-input" style="width: 100%;">
                    </div>
                </div>
            </div>

            <!-- Bank Accounts Repeater -->
            <div style="margin-bottom: 24px; padding-top: 16px; border-top: 1px solid var(--border-color);">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px; flex-wrap: wrap; gap: 8px;">
                    <h4 style="font-size: 14px; font-weight: 800; color: var(--text-primary);">
                        🏦 {{ __('Official Bank Accounts for Wire Transfer (الحسابات البنكية الرسمية)') }}
                    </h4>
                    <button type="button" onclick="addBankAccountRow()" class="tactile-btn btn-secondary" style="padding: 6px 14px; font-size: 12px;">
                        <span>+</span> {{ __('Add Bank Account (إضافة حساب بنكي)') }}
                    </button>
                </div>

                <div id="bank-accounts-container" style="display: flex; flex-direction: column; gap: 14px;">
                    @php
                        $banks = $paymentSettings['bank_accounts'] ?? [];
                    @endphp
                    @foreach($banks as $index => $b)
                        <div class="bank-account-card" style="background: var(--bg-surface-subtle); border: 1px solid var(--border-color); border-radius: var(--radius-lg); padding: 18px; position: relative;">
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
                                <strong style="font-size: 13px; color: var(--brand-forest);">🏛️ {{ __('Bank Account #:num', ['num' => $index + 1]) }}</strong>
                                <button type="button" onclick="this.closest('.bank-account-card').remove()" class="tactile-btn" style="padding: 4px 10px; font-size: 11px; background: rgba(217, 107, 95, 0.15); color: #D96B5F; border: 1px solid rgba(217, 107, 95, 0.3);">
                                    🗑️ {{ __('Remove') }}
                                </button>
                            </div>
                            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 12px;">
                                <div>
                                    <label style="display: block; font-size: 11px; font-weight: 700; color: var(--text-secondary); margin-bottom: 4px;">{{ __('Bank Name') }} *</label>
                                    <input type="text" name="bank_name[]" value="{{ $b['bank_name'] ?? '' }}" required placeholder="e.g. Al Rajhi Bank (مصرف الراجحي)" class="form-input" style="width: 100%;">
                                </div>
                                <div>
                                    <label style="display: block; font-size: 11px; font-weight: 700; color: var(--text-secondary); margin-bottom: 4px;">{{ __('Beneficiary Name (Arabic)') }}</label>
                                    <input type="text" name="account_name[]" value="{{ $b['account_name'] ?? '' }}" dir="rtl" placeholder="اسم المستفيد بالعربية" class="form-input" style="width: 100%;">
                                </div>
                                <div>
                                    <label style="display: block; font-size: 11px; font-weight: 700; color: var(--text-secondary); margin-bottom: 4px;">{{ __('Beneficiary Name (English)') }}</label>
                                    <input type="text" name="account_name_en[]" value="{{ $b['account_name_en'] ?? '' }}" dir="ltr" placeholder="Beneficiary name in English" class="form-input" style="width: 100%;">
                                </div>
                                <div>
                                    <label style="display: block; font-size: 11px; font-weight: 700; color: var(--text-secondary); margin-bottom: 4px;">IBAN *</label>
                                    <input type="text" name="iban[]" value="{{ $b['iban'] ?? '' }}" required placeholder="SA..." class="form-input" style="width: 100%; font-family: monospace; font-weight: 700;">
                                </div>
                                <div>
                                    <label style="display: block; font-size: 11px; font-weight: 700; color: var(--text-secondary); margin-bottom: 4px;">{{ __('Account Number') }}</label>
                                    <input type="text" name="account_number[]" value="{{ $b['account_number'] ?? '' }}" class="form-input" style="width: 100%; font-family: monospace;">
                                </div>
                                <div>
                                    <label style="display: block; font-size: 11px; font-weight: 700; color: var(--text-secondary); margin-bottom: 4px;">SWIFT / BIC Code</label>
                                    <input type="text" name="swift_code[]" value="{{ $b['swift_code'] ?? '' }}" placeholder="RJHISARI" class="form-input" style="width: 100%; font-family: monospace;">
                                </div>
                                <div>
                                    <label style="display: block; font-size: 11px; font-weight: 700; color: var(--text-secondary); margin-bottom: 4px;">{{ __('Currency') }}</label>
                                    <input type="text" name="currency[]" value="{{ $b['currency'] ?? 'SAR' }}" placeholder="SAR" class="form-input" style="width: 100%;">
                                </div>
                                <div>
                                    <label style="display: block; font-size: 11px; font-weight: 700; color: var(--text-secondary); margin-bottom: 4px;">{{ __('Badge / Label') }}</label>
                                    <input type="text" name="badge[]" value="{{ $b['badge'] ?? 'Direct Instant Local Transfer' }}" class="form-input" style="width: 100%;">
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Checkout Instructions & Terms -->
            <div style="padding-top: 16px; border-top: 1px solid var(--border-color);">
                <h4 style="font-size: 14px; font-weight: 800; color: var(--text-primary); margin-bottom: 12px;">
                    📜 {{ __('Checkout Instructions & Policy (تعليمات وشروط صفحة الدفع)') }}
                </h4>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                    <div>
                        <label style="display: block; font-size: 11px; font-weight: 700; color: var(--text-secondary); margin-bottom: 6px;">{{ __('Instructions in Arabic (التعليمات بالعربية)') }}</label>
                        <textarea name="checkout_terms_ar" rows="3" dir="rtl" class="form-input" style="width: 100%; font-family: 'Cairo', sans-serif;">{{ $paymentSettings['checkout_terms_ar'] ?? '' }}</textarea>
                    </div>
                    <div>
                        <label style="display: block; font-size: 11px; font-weight: 700; color: var(--text-secondary); margin-bottom: 6px;">{{ __('Instructions in English (التعليمات بالإنجليزية)') }}</label>
                        <textarea name="checkout_terms_en" rows="3" dir="ltr" class="form-input" style="width: 100%; font-family: 'Inter', sans-serif;">{{ $paymentSettings['checkout_terms_en'] ?? '' }}</textarea>
                    </div>
                </div>
            </div>

            <div style="margin-top: 24px; display: flex; justify-content: flex-end;">
                <button type="submit" class="tactile-btn btn-primary" style="padding: 12px 32px; font-size: 13px;">
                    💾 {{ __('Save Payment & Checkout Settings (حفظ إعدادات الدفع)') }}
                </button>
            </div>
        </div>
    </form>

    <!-- 3. Global System Default Office Blueprint -->
    <form method="POST" action="{{ route('superadmin.settings.blueprint') }}" enctype="multipart/form-data">
        @csrf
        <div class="panel-card" style="border-radius: var(--radius-xl); padding: 28px;">
            <div class="panel-header" style="margin-bottom: 20px;">
                <div class="panel-title">
                    <span>📐</span>
                    <span>{{ __('Global System Default Office Blueprint (المخطط الافتراضي للنظام)') }}</span>
                </div>
                <p class="panel-subtitle">
                    {{ __('Upload the platform-wide default 3D isometric architectural floorplan. All newly registered organizations and default workspaces will automatically inherit this blueprint design. Company admins can then customize and edit their specific rooms.') }}
                </p>
            </div>

            <div style="display: flex; flex-direction: column; gap: 16px; background: var(--bg-surface-subtle); border: 1px solid var(--border-color); border-radius: 14px; padding: 20px;">
                <div style="display: flex; align-items: center; gap: 20px; flex-wrap: wrap;">
                    <div style="width: 140px; height: 100px; border-radius: 10px; overflow: hidden; border: 2px solid var(--border-color); background: #0C1711; display: flex; align-items: center; justify-content: center;">
                        <img src="/images/office_floorplan.jpg" alt="Default Blueprint" style="width: 100%; height: 100%; object-fit: cover;">
                    </div>
                    <div style="flex: 1; min-width: 250px;">
                        <strong style="font-size: 14px; color: var(--text-primary); display: block; margin-bottom: 4px;">
                            {{ __('Active Default Floorplan Blueprint') }}
                        </strong>
                        <div style="font-size: 12px; color: var(--text-secondary); margin-bottom: 12px;">
                            {{ __('Supported formats: JPG, PNG, WebP (High Resolution Recommended)') }}
                        </div>
                        <input type="file" name="image" accept="image/jpeg,image/png,image/webp,image/jpg" required style="font-size: 12px; color: var(--text-primary);">
                    </div>
                </div>

                <div style="display: flex; justify-content: flex-end; margin-top: 8px;">
                    <button type="submit" class="tactile-btn btn-primary" style="padding: 10px 22px; font-size: 12px;">
                        🚀 {{ __('Update Global Default Blueprint') }}
                    </button>
                </div>
            </div>
        </div>
    </form>

    <!-- 4. OpenAI & AI Office Generator Settings -->
    <form method="POST" action="{{ route('superadmin.settings.ai') }}">
        @csrf
        <div class="panel-card" style="border-radius: var(--radius-xl); padding: 28px;">
            <div class="panel-header" style="margin-bottom: 20px;">
                <div class="panel-title">
                    <span>🤖</span>
                    <span>{{ __('OpenAI & AI Office Generator Settings (إعدادات الذكاء الاصطناعي)') }}</span>
                </div>
                <p class="panel-subtitle">
                    {{ __('Configure ChatGPT & OpenAI (DALL-E 3) API credentials to empower company admins to generate bespoke, 3D isometric architectural floorplans and isolated room maps directly from the Edit Office page.') }}
                </p>
            </div>

            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 20px;">
                <div style="grid-column: 1 / -1;">
                    <label style="display: flex; align-items: center; gap: 10px; cursor: pointer; font-size: 13px; font-weight: 800; color: var(--text-primary);">
                        <input type="checkbox" name="is_enabled" value="1" {{ !empty($aiSettings['is_enabled']) ? 'checked' : '' }} style="width: 18px; height: 18px; accent-color: var(--brand-forest);">
                        <span>✨ {{ __('Enable AI Office & Floorplan Generator Platform-wide (تفعيل ميزة توليد المكاتب بالذكاء الاصطناعي)') }}</span>
                    </label>
                </div>

                <div style="grid-column: 1 / -1;">
                    <label style="display: block; font-size: 12px; font-weight: 700; color: var(--text-secondary); margin-bottom: 6px;">
                        🔑 {{ __('OpenAI API Secret Key (مفتاح API الخاص بـ OpenAI)') }} *
                    </label>
                    <div style="display: flex; gap: 10px;">
                        <input type="password" id="openai-api-key-input" name="api_key" value="{{ $aiSettings['api_key'] ?? '' }}" placeholder="sk-proj-..." class="form-input" style="flex: 1; font-family: monospace; font-size: 13px;">
                        <button type="button" onclick="toggleApiKeyVisibility()" class="tactile-btn" style="padding: 0 14px; font-size: 13px;" title="{{ __('Toggle Visibility') }}">
                            <span id="api-eye-icon">👁️</span>
                        </button>
                        <button type="button" onclick="testOpenAiConnection()" id="btn-test-ai" class="tactile-btn" style="background: rgba(16, 185, 129, 0.15); color: #10B981; border: 1px solid rgba(16, 185, 129, 0.3); padding: 0 16px; font-size: 12px; white-space: nowrap;">
                            ⚡ {{ __('Test Connection (اختبار الاتصال)') }}
                        </button>
                    </div>
                    <div id="ai-test-feedback" style="display: none; margin-top: 8px; font-size: 12px; font-weight: 700; border-radius: 8px; padding: 8px 12px;"></div>
                </div>

                <div>
                    <label style="display: block; font-size: 12px; font-weight: 700; color: var(--text-secondary); margin-bottom: 6px;">
                        🖼️ {{ __('Image Generation Model (نموذج توليد الصور)') }}
                    </label>
                    <select name="model" class="form-input" style="width: 100%;">
                        <option value="dall-e-3" {{ ($aiSettings['model'] ?? 'dall-e-3') === 'dall-e-3' ? 'selected' : '' }}>
                            DALL-E 3 (Recommended: Ultra-detailed 3D Isometric Art)
                        </option>
                        <option value="dall-e-2" {{ ($aiSettings['model'] ?? '') === 'dall-e-2' ? 'selected' : '' }}>
                            DALL-E 2 (Legacy Fast)
                        </option>
                    </select>
                </div>

                <div>
                    <label style="display: block; font-size: 12px; font-weight: 700; color: var(--text-secondary); margin-bottom: 6px;">
                        📐 {{ __('Floorplan Image Aspect Ratio & Size (أبعاد المخطط)') }}
                    </label>
                    <select name="image_size" class="form-input" style="width: 100%;">
                        <option value="1792x1024" {{ ($aiSettings['image_size'] ?? '1792x1024') === '1792x1024' ? 'selected' : '' }}>
                            1792 × 1024 (Widescreen 16:9 - Perfect for Virtual Offices)
                        </option>
                        <option value="1024x1024" {{ ($aiSettings['image_size'] ?? '') === '1024x1024' ? 'selected' : '' }}>
                            1024 × 1024 (Square 1:1)
                        </option>
                    </select>
                </div>

                <div>
                    <label style="display: block; font-size: 12px; font-weight: 700; color: var(--text-secondary); margin-bottom: 6px;">
                        💎 {{ __('Image Render Quality (جودة المعالجة)') }}
                    </label>
                    <select name="quality" class="form-input" style="width: 100%;">
                        <option value="standard" {{ ($aiSettings['quality'] ?? 'standard') === 'standard' ? 'selected' : '' }}>
                            Standard (Fast & Cost Effective)
                        </option>
                        <option value="hd" {{ ($aiSettings['quality'] ?? '') === 'hd' ? 'selected' : '' }}>
                            HD (High Definition Hyper-Realistic Textures)
                        </option>
                    </select>
                </div>

                <div style="grid-column: 1 / -1;">
                    <label style="display: block; font-size: 12px; font-weight: 700; color: var(--text-secondary); margin-bottom: 6px;">
                        📝 {{ __('System Prompt Prefix & Directives (تعليمات التوليد المعماري)') }}
                    </label>
                    <textarea name="prompt_prefix" rows="3" class="form-input" style="width: 100%; font-size: 12px;">{{ $aiSettings['prompt_prefix'] ?? 'A clean, high-angle photorealistic 3D isometric architectural floorplan of a modern virtual workplace office.' }}</textarea>
                </div>
            </div>

            <div style="margin-top: 24px; display: flex; justify-content: flex-end;">
                <button type="submit" class="tactile-btn btn-primary" style="padding: 12px 32px; font-size: 13px;">
                    💾 {{ __('Save AI Generator Settings (حفظ إعدادات الذكاء الاصطناعي)') }}
                </button>
            </div>
        </div>
    </form>
</div>

<script>
    function toggleApiKeyVisibility() {
        const inp = document.getElementById('openai-api-key-input');
        const icon = document.getElementById('api-eye-icon');
        if (inp.type === 'password') {
            inp.type = 'text';
            icon.textContent = '🙈';
        } else {
            inp.type = 'password';
            icon.textContent = '👁️';
        }
    }

    async function testOpenAiConnection() {
        const apiKey = document.getElementById('openai-api-key-input').value.trim();
        const feedback = document.getElementById('ai-test-feedback');
        const btn = document.getElementById('btn-test-ai');
        
        btn.innerHTML = '<span>⏳</span> Testing...';
        feedback.style.display = 'none';

        try {
            const res = await fetch("{{ route('superadmin.settings.ai.test') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ api_key: apiKey })
            });
            const data = await res.json();
            
            feedback.style.display = 'block';
            if (data.success) {
                feedback.style.background = 'rgba(16, 185, 129, 0.15)';
                feedback.style.color = '#10B981';
                feedback.style.border = '1px solid rgba(16, 185, 129, 0.3)';
                feedback.innerHTML = data.message;
            } else {
                feedback.style.background = 'rgba(217, 107, 95, 0.15)';
                feedback.style.color = '#D96B5F';
                feedback.style.border = '1px solid rgba(217, 107, 95, 0.3)';
                feedback.innerHTML = '⚠️ ' + data.message;
            }
        } catch (e) {
            feedback.style.display = 'block';
            feedback.style.background = 'rgba(217, 107, 95, 0.15)';
            feedback.style.color = '#D96B5F';
            feedback.style.border = '1px solid rgba(217, 107, 95, 0.3)';
            feedback.innerHTML = '⚠️ Network error testing connection: ' + e.message;
        } finally {
            btn.innerHTML = '⚡ {{ __("Test Connection (اختبار الاتصال)") }}';
        }
    }
    function addBankAccountRow() {
        const container = document.getElementById('bank-accounts-container');
        const count = container.children.length + 1;
        const card = document.createElement('div');
        card.className = 'bank-account-card';
        card.style = 'background: var(--bg-surface-subtle); border: 1px solid var(--border-color); border-radius: var(--radius-lg); padding: 18px; position: relative;';
        card.innerHTML = `
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
                <strong style="font-size: 13px; color: var(--brand-forest);">🏛️ {{ __('Bank Account') }} #${count}</strong>
                <button type="button" onclick="this.closest('.bank-account-card').remove()" class="tactile-btn" style="padding: 4px 10px; font-size: 11px; background: rgba(217, 107, 95, 0.15); color: #D96B5F; border: 1px solid rgba(217, 107, 95, 0.3);">
                    🗑️ {{ __('Remove') }}
                </button>
            </div>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 12px;">
                <div>
                    <label style="display: block; font-size: 11px; font-weight: 700; color: var(--text-secondary); margin-bottom: 4px;">{{ __('Bank Name') }} *</label>
                    <input type="text" name="bank_name[]" required placeholder="e.g. Al Rajhi Bank" class="form-input" style="width: 100%;">
                </div>
                <div>
                    <label style="display: block; font-size: 11px; font-weight: 700; color: var(--text-secondary); margin-bottom: 4px;">{{ __('Beneficiary Name (Arabic)') }}</label>
                    <input type="text" name="account_name[]" dir="rtl" placeholder="اسم المستفيد بالعربية" class="form-input" style="width: 100%;">
                </div>
                <div>
                    <label style="display: block; font-size: 11px; font-weight: 700; color: var(--text-secondary); margin-bottom: 4px;">{{ __('Beneficiary Name (English)') }}</label>
                    <input type="text" name="account_name_en[]" dir="ltr" placeholder="Beneficiary name in English" class="form-input" style="width: 100%;">
                </div>
                <div>
                    <label style="display: block; font-size: 11px; font-weight: 700; color: var(--text-secondary); margin-bottom: 4px;">IBAN *</label>
                    <input type="text" name="iban[]" required placeholder="SA..." class="form-input" style="width: 100%; font-family: monospace; font-weight: 700;">
                </div>
                <div>
                    <label style="display: block; font-size: 11px; font-weight: 700; color: var(--text-secondary); margin-bottom: 4px;">{{ __('Account Number') }}</label>
                    <input type="text" name="account_number[]" class="form-input" style="width: 100%; font-family: monospace;">
                </div>
                <div>
                    <label style="display: block; font-size: 11px; font-weight: 700; color: var(--text-secondary); margin-bottom: 4px;">SWIFT / BIC Code</label>
                    <input type="text" name="swift_code[]" placeholder="RJHISARI" class="form-input" style="width: 100%; font-family: monospace;">
                </div>
                <div>
                    <label style="display: block; font-size: 11px; font-weight: 700; color: var(--text-secondary); margin-bottom: 4px;">{{ __('Currency') }}</label>
                    <input type="text" name="currency[]" value="SAR" class="form-input" style="width: 100%;">
                </div>
                <div>
                    <label style="display: block; font-size: 11px; font-weight: 700; color: var(--text-secondary); margin-bottom: 4px;">{{ __('Badge / Label') }}</label>
                    <input type="text" name="badge[]" value="Official Bank Account" class="form-input" style="width: 100%;">
                </div>
            </div>
        `;
        container.appendChild(card);
    }
</script>
@endsection
