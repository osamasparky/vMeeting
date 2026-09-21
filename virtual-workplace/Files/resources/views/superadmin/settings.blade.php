@extends('superadmin.layout')

@section('title', __('System Settings & Payment Configuration'))
@section('page_title', __('System Settings'))

@section('content')
<div style="display: flex; flex-direction: column; gap: 20px;">

    <!-- Modern Sub-Tabs Navigation Bar -->
    <div class="settings-tabs-nav" style="display: flex; gap: 8px; background: var(--ula-surface-card); padding: 8px; border-radius: var(--ula-radius-xl, 20px); border: 1px solid var(--ula-border-subtle); box-shadow: var(--ula-shadow-sm); overflow-x: auto;">
        <button type="button" class="sa-tab-btn active" onclick="switchSuperTab('general', this)" id="sa-tab-btn-general">
            <span class="material-symbols-rounded" style="font-size: 18px;">public</span>
            <span>{{ __('Global Platform & SaaS') }}</span>
        </button>
        <button type="button" class="sa-tab-btn" onclick="switchSuperTab('payment', this)" id="sa-tab-btn-payment">
            <span class="material-symbols-rounded" style="font-size: 18px;">payments</span>
            <span>{{ __('Payment & Bank Accounts') }}</span>
        </button>
        <button type="button" class="sa-tab-btn" onclick="switchSuperTab('blueprint', this)" id="sa-tab-btn-blueprint">
            <span class="material-symbols-rounded" style="font-size: 18px;">architecture</span>
            <span>{{ __('Default Global Blueprint') }}</span>
        </button>
        <button type="button" class="sa-tab-btn" onclick="switchSuperTab('ai', this)" id="sa-tab-btn-ai">
            <span class="material-symbols-rounded" style="font-size: 18px;">smart_toy</span>
            <span>{{ __('AI Engine & OpenAI') }}</span>
        </button>
    </div>

    <!-- 1. TAB: Global SaaS Configuration -->
    <div id="sa-tab-content-general" class="sa-tab-pane" style="display: block;">
        <form method="POST" action="{{ route('superadmin.settings.update') }}">
            @csrf

            <div class="panel-card" style="background: var(--ula-surface-card); border: 1px solid var(--ula-border-subtle); border-radius: var(--ula-radius-xl, 20px); padding: 28px; box-shadow: var(--ula-shadow-sm);">
                <div class="panel-header" style="margin-bottom: 24px; border-bottom: 1px solid var(--ula-border-subtle); padding-bottom: 16px;">
                    <div class="panel-title" style="font-size: 16px; font-weight: 800; color: var(--ula-text-primary); display: flex; align-items: center; gap: 8px;">
                        <span class="material-symbols-rounded" style="color: var(--ula-highlight-default); font-size: 20px;">public</span>
                        <span>{{ __('Global SaaS Configuration') }}</span>
                    </div>
                    <p class="panel-subtitle" style="font-size: 12px; color: var(--ula-text-secondary); margin: 4px 0 0 0;">{{ __('Configure core platform parameters, default registration tier, and real-time connectivity.') }}</p>
                </div>

                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 20px;">
                    <div>
                        <label style="display: block; font-size: 11px; font-weight: 700; color: var(--ula-text-secondary); margin-bottom: 8px; text-transform: uppercase;">
                            {{ __('Platform Name') }}
                        </label>
                        <input
                            type="text"
                            name="platform_name"
                            value="{{ $globalSettings['platform_name'] ?? 'Virtual Workplace SaaS' }}"
                            style="width: 100%; background: var(--ula-surface-page-alt); border: 1px solid var(--ula-border-subtle); border-radius: 10px; padding: 12px 14px; color: var(--ula-text-primary); outline: none; font-size: 13px; font-weight: 600;"
                        >
                    </div>

                    <div>
                        <label style="display: block; font-size: 11px; font-weight: 700; color: var(--ula-text-secondary); margin-bottom: 8px; text-transform: uppercase;">
                            {{ __('Default Registration Plan') }}
                        </label>
                        <select name="default_plan_id" style="width: 100%; background: var(--ula-surface-page-alt); border: 1px solid var(--ula-border-subtle); border-radius: 10px; padding: 12px 14px; color: var(--ula-text-primary); outline: none; font-size: 13px; font-weight: 600;">
                            @foreach($plans as $p)
                                <option value="{{ $p->id }}" {{ ($globalSettings['default_plan_id'] ?? '') == $p->id ? 'selected' : '' }}>
                                    {{ $p->name }} ({{ $p->seat_limit === 0 ? __('Unlimited') : $p->seat_limit }} {{ __('Users') }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label style="display: block; font-size: 11px; font-weight: 700; color: var(--ula-text-secondary); margin-bottom: 8px; text-transform: uppercase;">
                            {{ __('Realtime WebSocket URL') }}
                        </label>
                        <input
                            type="text"
                            name="ws_url"
                            value="{{ $globalSettings['ws_url'] ?? 'ws://127.0.0.1:8080' }}"
                            style="width: 100%; background: var(--ula-surface-page-alt); border: 1px solid var(--ula-border-subtle); border-radius: 10px; padding: 12px 14px; color: var(--ula-palm-900); outline: none; font-size: 13px; font-family: 'IBM Plex Mono', monospace; font-weight: 700;"
                        >
                    </div>

                    <div>
                        <label style="display: block; font-size: 11px; font-weight: 700; color: var(--ula-text-secondary); margin-bottom: 8px; text-transform: uppercase;">
                            {{ __('STUN / TURN Server') }}
                        </label>
                        <input
                            type="text"
                            name="stun_server"
                            value="{{ $globalSettings['stun_server'] ?? 'stun:173.212.248.192:3478' }}"
                            style="width: 100%; background: var(--ula-surface-page-alt); border: 1px solid var(--ula-border-subtle); border-radius: 10px; padding: 12px 14px; color: var(--ula-text-primary); outline: none; font-size: 13px; font-family: 'IBM Plex Mono', monospace;"
                        >
                    </div>
                </div>

                <div style="margin-top: 24px; display: flex; justify-content: flex-end;">
                    <button type="submit" class="tactile-btn btn-primary" style="padding: 12px 28px; font-size: 13px; border-radius: var(--ula-radius-pill, 9999px); display: inline-flex; align-items: center; gap: 6px;">
                        <span class="material-symbols-rounded" style="font-size: 16px;">save</span>
                        <span>{{ __('Save Global Settings') }}</span>
                    </button>
                </div>
            </div>
        </form>
    </div>

    <!-- 2. TAB: Payment & Checkout Information Settings -->
    <div id="sa-tab-content-payment" class="sa-tab-pane" style="display: none;">
        <form method="POST" action="{{ route('superadmin.settings.payment') }}" id="payment-settings-form">
            @csrf

            <div class="panel-card" style="background: var(--ula-surface-card); border: 1px solid var(--ula-border-subtle); border-radius: var(--ula-radius-xl, 20px); padding: 28px; box-shadow: var(--ula-shadow-sm);">
                <div class="panel-header" style="margin-bottom: 24px; border-bottom: 1px solid var(--ula-border-subtle); padding-bottom: 16px;">
                    <div class="panel-title" style="font-size: 16px; font-weight: 800; color: var(--ula-text-primary); display: flex; align-items: center; gap: 8px;">
                        <span class="material-symbols-rounded" style="color: var(--ula-highlight-default); font-size: 20px;">payments</span>
                        <span>{{ __('Checkout & Payment Gateways Settings') }}</span>
                    </div>
                    <p class="panel-subtitle" style="font-size: 12px; color: var(--ula-text-secondary); margin: 4px 0 0 0;">{{ __('Configure payment methods, official bank accounts, Instapay / Wallets, currency rates, and terms displayed to users on the checkout page.') }}</p>
                </div>

                <!-- Currency & Rates -->
                <div style="margin-bottom: 24px;">
                    <h4 style="font-size: 14px; font-weight: 800; color: var(--ula-text-primary); margin-bottom: 12px; display: flex; align-items: center; gap: 6px;">
                        <span class="material-symbols-rounded" style="font-size: 18px; color: var(--ula-highlight-default);">currency_exchange</span>
                        <span>{{ __('Currency Rates & Taxes') }}</span>
                    </h4>
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 16px;">
                        <div>
                            <label style="display: block; font-size: 11px; font-weight: 700; color: var(--ula-text-secondary); margin-bottom: 6px;">1 USD ➔ {{ __('SAR') }}</label>
                            <input type="number" step="0.01" name="usd_to_sar_rate" value="{{ $paymentSettings['usd_to_sar_rate'] ?? 3.75 }}" required class="form-input" style="width: 100%; background: var(--ula-surface-page-alt); border: 1px solid var(--ula-border-subtle); border-radius: 10px; padding: 10px 14px; font-size: 13px; font-family: 'IBM Plex Mono', monospace;">
                        </div>
                        <div>
                            <label style="display: block; font-size: 11px; font-weight: 700; color: var(--ula-text-secondary); margin-bottom: 6px;">1 USD ➔ {{ __('EGP') }}</label>
                            <input type="number" step="0.01" name="usd_to_egp_rate" value="{{ $paymentSettings['usd_to_egp_rate'] ?? 48.5 }}" required class="form-input" style="width: 100%; background: var(--ula-surface-page-alt); border: 1px solid var(--ula-border-subtle); border-radius: 10px; padding: 10px 14px; font-size: 13px; font-family: 'IBM Plex Mono', monospace;">
                        </div>
                        <div>
                            <label style="display: block; font-size: 11px; font-weight: 700; color: var(--ula-text-secondary); margin-bottom: 6px;">1 USD ➔ {{ __('AED') }}</label>
                            <input type="number" step="0.01" name="usd_to_aed_rate" value="{{ $paymentSettings['usd_to_aed_rate'] ?? 3.67 }}" required class="form-input" style="width: 100%; background: var(--ula-surface-page-alt); border: 1px solid var(--ula-border-subtle); border-radius: 10px; padding: 10px 14px; font-size: 13px; font-family: 'IBM Plex Mono', monospace;">
                        </div>
                        <div>
                            <label style="display: block; font-size: 11px; font-weight: 700; color: var(--ula-text-secondary); margin-bottom: 6px;">{{ __('VAT / Tax Percentage (%)') }}</label>
                            <input type="number" step="0.1" name="tax_percentage" value="{{ $paymentSettings['tax_percentage'] ?? 15 }}" class="form-input" style="width: 100%; background: var(--ula-surface-page-alt); border: 1px solid var(--ula-border-subtle); border-radius: 10px; padding: 10px 14px; font-size: 13px; font-family: 'IBM Plex Mono', monospace;">
                        </div>
                    </div>
                </div>

                <!-- Instant Wallets & Digital Payment -->
                <div style="margin-bottom: 24px; padding-top: 16px; border-top: 1px solid var(--ula-border-subtle);">
                    <h4 style="font-size: 14px; font-weight: 800; color: var(--ula-text-primary); margin-bottom: 12px; display: flex; align-items: center; gap: 6px;">
                        <span class="material-symbols-rounded" style="font-size: 18px; color: var(--ula-highlight-default);">account_balance_wallet</span>
                        <span>{{ __('Instant Payment & Digital Wallets') }}</span>
                    </h4>
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 16px;">
                        <div>
                            <label style="display: block; font-size: 11px; font-weight: 700; color: var(--ula-text-secondary); margin-bottom: 6px;">Instapay Username / IPA Handle</label>
                            <input type="text" name="instapay_handle" value="{{ $paymentSettings['instapay_handle'] ?? '' }}" placeholder="nextspace@instapay" class="form-input" style="width: 100%; background: var(--ula-surface-page-alt); border: 1px solid var(--ula-border-subtle); border-radius: 10px; padding: 10px 14px; font-size: 13px;">
                        </div>
                        <div>
                            <label style="display: block; font-size: 11px; font-weight: 700; color: var(--ula-text-secondary); margin-bottom: 6px;">Instapay Mobile Number</label>
                            <input type="text" name="instapay_phone" value="{{ $paymentSettings['instapay_phone'] ?? '' }}" placeholder="+201000000000" class="form-input" style="width: 100%; background: var(--ula-surface-page-alt); border: 1px solid var(--ula-border-subtle); border-radius: 10px; padding: 10px 14px; font-size: 13px;">
                        </div>
                        <div>
                            <label style="display: block; font-size: 11px; font-weight: 700; color: var(--ula-text-secondary); margin-bottom: 6px;">STC Pay (Saudi Arabia)</label>
                            <input type="text" name="stc_pay_phone" value="{{ $paymentSettings['stc_pay_phone'] ?? '' }}" placeholder="+966500000000" class="form-input" style="width: 100%; background: var(--ula-surface-page-alt); border: 1px solid var(--ula-border-subtle); border-radius: 10px; padding: 10px 14px; font-size: 13px;">
                        </div>
                        <div>
                            <label style="display: block; font-size: 11px; font-weight: 700; color: var(--ula-text-secondary); margin-bottom: 6px;">Vodafone Cash / Wallets (Egypt)</label>
                            <input type="text" name="vodafone_cash_phone" value="{{ $paymentSettings['vodafone_cash_phone'] ?? '' }}" placeholder="+201000000000" class="form-input" style="width: 100%; background: var(--ula-surface-page-alt); border: 1px solid var(--ula-border-subtle); border-radius: 10px; padding: 10px 14px; font-size: 13px;">
                        </div>
                    </div>
                </div>

                <!-- Bank Accounts Repeater -->
                <div style="margin-bottom: 24px; padding-top: 16px; border-top: 1px solid var(--ula-border-subtle);">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px; flex-wrap: wrap; gap: 8px;">
                        <h4 style="font-size: 14px; font-weight: 800; color: var(--ula-text-primary); display: flex; align-items: center; gap: 6px;">
                            <span class="material-symbols-rounded" style="font-size: 18px; color: var(--ula-highlight-default);">account_balance</span>
                            <span>{{ __('Official Bank Accounts for Wire Transfer') }}</span>
                        </h4>
                        <button type="button" onclick="addBankAccountRow()" class="tactile-btn btn-secondary" style="padding: 6px 14px; font-size: 12px; border-radius: var(--ula-radius-pill, 9999px); display: inline-flex; align-items: center; gap: 4px;">
                            <span class="material-symbols-rounded" style="font-size: 14px;">add</span>
                            <span>{{ __('Add Bank Account') }}</span>
                        </button>
                    </div>

                    <div id="bank-accounts-container" style="display: flex; flex-direction: column; gap: 14px;">
                        @php
                            $banks = $paymentSettings['bank_accounts'] ?? [];
                        @endphp
                        @foreach($banks as $index => $b)
                            <div class="bank-account-card" style="background: var(--ula-surface-page-alt); border: 1px solid var(--ula-border-subtle); border-radius: var(--ula-radius-lg, 16px); padding: 18px; position: relative;">
                                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
                                    <strong style="font-size: 13px; color: var(--ula-palm-900); display: flex; align-items: center; gap: 6px;">
                                        <span class="material-symbols-rounded" style="font-size: 16px;">account_balance</span>
                                        <span>{{ __('Bank Account #:num', ['num' => $index + 1]) }}</span>
                                    </strong>
                                    <button type="button" onclick="this.closest('.bank-account-card').remove()" class="tactile-btn" style="padding: 4px 10px; font-size: 11px; color: var(--ula-status-danger); display: inline-flex; align-items: center; gap: 4px;">
                                        <span class="material-symbols-rounded" style="font-size: 14px;">delete</span>
                                        <span>{{ __('Remove') }}</span>
                                    </button>
                                </div>
                                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 12px;">
                                    <div>
                                        <label style="display: block; font-size: 11px; font-weight: 700; color: var(--ula-text-secondary); margin-bottom: 4px;">{{ __('Bank Name') }} *</label>
                                        <input type="text" name="bank_name[]" value="{{ $b['bank_name'] ?? '' }}" required placeholder="e.g. Al Rajhi Bank" class="form-input" style="width: 100%; background: var(--ula-surface-card); border: 1px solid var(--ula-border-subtle); border-radius: 8px; padding: 8px 12px; font-size: 13px;">
                                    </div>
                                    <div>
                                        <label style="display: block; font-size: 11px; font-weight: 700; color: var(--ula-text-secondary); margin-bottom: 4px;">{{ __('Beneficiary Name (Arabic)') }}</label>
                                        <input type="text" name="account_name[]" value="{{ $b['account_name'] ?? '' }}" dir="rtl" placeholder="اسم المستفيد بالعربية" class="form-input" style="width: 100%; background: var(--ula-surface-card); border: 1px solid var(--ula-border-subtle); border-radius: 8px; padding: 8px 12px; font-size: 13px;">
                                    </div>
                                    <div>
                                        <label style="display: block; font-size: 11px; font-weight: 700; color: var(--ula-text-secondary); margin-bottom: 4px;">{{ __('Beneficiary Name (English)') }}</label>
                                        <input type="text" name="account_name_en[]" value="{{ $b['account_name_en'] ?? '' }}" dir="ltr" placeholder="Beneficiary name in English" class="form-input" style="width: 100%; background: var(--ula-surface-card); border: 1px solid var(--ula-border-subtle); border-radius: 8px; padding: 8px 12px; font-size: 13px;">
                                    </div>
                                    <div>
                                        <label style="display: block; font-size: 11px; font-weight: 700; color: var(--ula-text-secondary); margin-bottom: 4px;">IBAN *</label>
                                        <input type="text" name="iban[]" value="{{ $b['iban'] ?? '' }}" required placeholder="SA..." class="form-input" style="width: 100%; background: var(--ula-surface-card); border: 1px solid var(--ula-border-subtle); border-radius: 8px; padding: 8px 12px; font-size: 13px; font-family: 'IBM Plex Mono', monospace; font-weight: 700;">
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div style="margin-top: 24px; display: flex; justify-content: flex-end;">
                    <button type="submit" class="tactile-btn btn-primary" style="padding: 12px 32px; font-size: 13px; border-radius: var(--ula-radius-pill, 9999px); display: inline-flex; align-items: center; gap: 6px;">
                        <span class="material-symbols-rounded" style="font-size: 16px;">save</span>
                        <span>{{ __('Save Payment & Checkout Settings') }}</span>
                    </button>
                </div>
            </div>
        </form>
    </div>

    <!-- 3. TAB: Global System Default Office Blueprint -->
    <div id="sa-tab-content-blueprint" class="sa-tab-pane" style="display: none;">
        <form method="POST" action="{{ route('superadmin.settings.blueprint') }}" enctype="multipart/form-data">
            @csrf
            <div class="panel-card" style="background: var(--ula-surface-card); border: 1px solid var(--ula-border-subtle); border-radius: var(--ula-radius-xl, 20px); padding: 28px; box-shadow: var(--ula-shadow-sm);">
                <div class="panel-header" style="margin-bottom: 20px; border-bottom: 1px solid var(--ula-border-subtle); padding-bottom: 14px;">
                    <div class="panel-title" style="font-size: 16px; font-weight: 800; color: var(--ula-text-primary); display: flex; align-items: center; gap: 8px;">
                        <span class="material-symbols-rounded" style="color: var(--ula-highlight-default); font-size: 20px;">architecture</span>
                        <span>{{ __('Global System Default Office Blueprint') }}</span>
                    </div>
                    <p class="panel-subtitle" style="font-size: 12px; color: var(--ula-text-secondary); margin: 4px 0 0 0;">
                        {{ __('Upload the platform-wide default 3D isometric architectural floorplan. All newly registered organizations and default workspaces will automatically inherit this blueprint design.') }}
                    </p>
                </div>

                <div style="display: flex; flex-direction: column; gap: 16px; background: var(--ula-surface-page-alt); border: 1px solid var(--ula-border-subtle); border-radius: 14px; padding: 20px;">
                    <div style="display: flex; align-items: center; gap: 20px; flex-wrap: wrap;">
                        <div style="width: 140px; height: 100px; border-radius: 10px; overflow: hidden; border: 2px solid var(--ula-border-subtle); background: var(--ula-palm-950); display: flex; align-items: center; justify-content: center;">
                            <img src="/images/office_floorplan.jpg" alt="Default Blueprint" style="width: 100%; height: 100%; object-fit: cover;">
                        </div>
                        <div style="flex: 1; min-width: 250px;">
                            <strong style="font-size: 14px; color: var(--ula-text-primary); display: block; margin-bottom: 4px;">
                                {{ __('Active Default Floorplan Blueprint') }}
                            </strong>
                            <div style="font-size: 12px; color: var(--ula-text-secondary); margin-bottom: 12px;">
                                {{ __('Supported formats: JPG, PNG, WebP (High Resolution Recommended)') }}
                            </div>
                            <input type="file" name="image" accept="image/jpeg,image/png,image/webp,image/jpg" required style="font-size: 12px; color: var(--ula-text-primary);">
                        </div>
                    </div>

                    <div style="display: flex; justify-content: flex-end; margin-top: 8px;">
                        <button type="submit" class="tactile-btn btn-primary" style="padding: 10px 22px; font-size: 12px; border-radius: var(--ula-radius-pill, 9999px); display: inline-flex; align-items: center; gap: 6px;">
                            <span class="material-symbols-rounded" style="font-size: 16px;">upload</span>
                            <span>{{ __('Update Global Default Blueprint') }}</span>
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <!-- 4. TAB: OpenAI & AI Office Generator Settings -->
    <div id="sa-tab-content-ai" class="sa-tab-pane" style="display: none;">
        <form method="POST" action="{{ route('superadmin.settings.ai') }}">
            @csrf
            <div class="panel-card" style="background: var(--ula-surface-card); border: 1px solid var(--ula-border-subtle); border-radius: var(--ula-radius-xl, 20px); padding: 28px; box-shadow: var(--ula-shadow-sm);">
                <div class="panel-header" style="margin-bottom: 20px; border-bottom: 1px solid var(--ula-border-subtle); padding-bottom: 14px;">
                    <div class="panel-title" style="font-size: 16px; font-weight: 800; color: var(--ula-text-primary); display: flex; align-items: center; gap: 8px;">
                        <span class="material-symbols-rounded" style="color: var(--ula-highlight-default); font-size: 20px;">smart_toy</span>
                        <span>{{ __('OpenAI & AI Office Generator Settings') }}</span>
                    </div>
                    <p class="panel-subtitle" style="font-size: 12px; color: var(--ula-text-secondary); margin: 4px 0 0 0;">
                        {{ __('Configure ChatGPT & OpenAI (DALL-E 3) API credentials to empower company admins to generate bespoke, 3D isometric architectural floorplans directly from the Edit Office page.') }}
                    </p>
                </div>

                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 20px;">
                    <div style="grid-column: 1 / -1;">
                        <label style="display: flex; align-items: center; gap: 10px; cursor: pointer; font-size: 13px; font-weight: 700; color: var(--ula-text-primary);">
                            <input type="checkbox" name="is_enabled" value="1" {{ !empty($aiSettings['is_enabled']) || !empty($aiSettings['api_key']) ? 'checked' : '' }} style="width: 18px; height: 18px; accent-color: var(--ula-palm-900);">
                            <span>{{ __('Enable AI Office & Floorplan Generator Platform-wide') }}</span>
                        </label>
                    </div>

                    <div style="grid-column: 1 / -1;">
                        <label style="display: block; font-size: 12px; font-weight: 700; color: var(--ula-text-secondary); margin-bottom: 6px;">
                            {{ __('OpenAI API Secret Key') }} *
                        </label>
                        <div style="display: flex; gap: 10px;">
                            <input type="password" id="openai-api-key-input" name="api_key" value="{{ $aiSettings['api_key'] ?? '' }}" placeholder="sk-proj-..." class="form-input" style="flex: 1; font-family: 'IBM Plex Mono', monospace; font-size: 13px; background: var(--ula-surface-page-alt); border: 1px solid var(--ula-border-subtle); border-radius: 10px; padding: 10px 14px;">
                            <button type="button" onclick="toggleApiKeyVisibility()" class="tactile-btn btn-secondary" style="padding: 0 14px; font-size: 13px; display: inline-flex; align-items: center;" title="{{ __('Toggle Visibility') }}">
                                <span class="material-symbols-rounded" id="api-eye-icon" style="font-size: 18px;">visibility</span>
                            </button>
                            <button type="button" onclick="testOpenAiConnection()" id="btn-test-ai" class="tactile-btn btn-secondary" style="padding: 0 16px; font-size: 12px; white-space: nowrap; display: inline-flex; align-items: center; gap: 4px;">
                                <span class="material-symbols-rounded" style="font-size: 15px;">bolt</span>
                                <span>{{ __('Test Connection') }}</span>
                            </button>
                        </div>
                        <div id="ai-test-feedback" style="display: none; margin-top: 8px; font-size: 12px; font-weight: 700; border-radius: 8px; padding: 8px 12px;"></div>
                    </div>
                </div>

                <div style="margin-top: 24px; display: flex; justify-content: flex-end;">
                    <button type="submit" class="tactile-btn btn-primary" style="padding: 12px 32px; font-size: 13px; border-radius: var(--ula-radius-pill, 9999px); display: inline-flex; align-items: center; gap: 6px;">
                        <span class="material-symbols-rounded" style="font-size: 16px;">save</span>
                        <span>{{ __('Save AI Generator Settings') }}</span>
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<style>
    .settings-tabs-nav .sa-tab-btn {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 10px 18px;
        border-radius: var(--ula-radius-lg, 14px);
        font-size: 13px;
        font-weight: 700;
        color: var(--ula-text-secondary);
        background: transparent;
        border: none;
        cursor: pointer;
        white-space: nowrap;
        transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
    }
    .settings-tabs-nav .sa-tab-btn:hover {
        color: var(--ula-text-primary);
        background: var(--ula-surface-page-alt);
    }
    .settings-tabs-nav .sa-tab-btn.active {
        color: #ffffff;
        background: var(--ula-palm-900);
        box-shadow: var(--ula-shadow-sm);
    }
    .sa-tab-pane {
        animation: saFadeIn 0.25s ease-out;
    }
    @keyframes saFadeIn {
        from { opacity: 0; transform: translateY(6px); }
        to { opacity: 1; transform: translateY(0); }
    }
</style>

<script nonce="{{ $cspNonce ?? '' }}">
    function switchSuperTab(tabKey, btnElement) {
        document.querySelectorAll('.sa-tab-pane').forEach(p => p.style.display = 'none');
        document.querySelectorAll('.sa-tab-btn').forEach(b => b.classList.remove('active'));

        const targetPane = document.getElementById('sa-tab-content-' + tabKey);
        if (targetPane) targetPane.style.display = 'block';

        if (btnElement) {
            btnElement.classList.add('active');
        } else {
            const defaultBtn = document.getElementById('sa-tab-btn-' + tabKey);
            if (defaultBtn) defaultBtn.classList.add('active');
        }

        if (history.replaceState) {
            history.replaceState(null, null, '#' + tabKey);
        }
    }

    document.addEventListener('DOMContentLoaded', () => {
        const hash = window.location.hash.replace('#', '');
        if (hash && ['general', 'payment', 'blueprint', 'ai'].includes(hash)) {
            switchSuperTab(hash);
        }
    });

    function toggleApiKeyVisibility() {
        const inp = document.getElementById('openai-api-key-input');
        const icon = document.getElementById('api-eye-icon');
        if (inp.type === 'password') {
            inp.type = 'text';
            icon.textContent = 'visibility_off';
        } else {
            inp.type = 'password';
            icon.textContent = 'visibility';
        }
    }

    async function testOpenAiConnection() {
        const apiKey = document.getElementById('openai-api-key-input').value.trim();
        const feedback = document.getElementById('ai-test-feedback');
        const btn = document.getElementById('btn-test-ai');
        
        btn.innerHTML = '<span class="material-symbols-rounded" style="font-size: 15px;">hourglass_top</span> Testing...';
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
                feedback.style.background = 'rgba(60, 107, 76, 0.12)';
                feedback.style.color = 'var(--ula-status-success)';
                feedback.style.border = '1px solid rgba(60, 107, 76, 0.3)';
                feedback.innerHTML = data.message;
            } else {
                feedback.style.background = 'rgba(217, 107, 95, 0.12)';
                feedback.style.color = 'var(--ula-status-danger)';
                feedback.style.border = '1px solid rgba(217, 107, 95, 0.3)';
                feedback.innerHTML = data.message;
            }
        } catch (e) {
            feedback.style.display = 'block';
            feedback.style.background = 'rgba(217, 107, 95, 0.12)';
            feedback.style.color = 'var(--ula-status-danger)';
            feedback.style.border = '1px solid rgba(217, 107, 95, 0.3)';
            feedback.innerHTML = 'Network error testing connection: ' + e.message;
        } finally {
            btn.innerHTML = '<span class="material-symbols-rounded" style="font-size: 15px;">bolt</span> {{ __("Test Connection") }}';
        }
    }

    function addBankAccountRow() {
        const container = document.getElementById('bank-accounts-container');
        const count = container.children.length + 1;
        const card = document.createElement('div');
        card.className = 'bank-account-card';
        card.style = 'background: var(--ula-surface-page-alt); border: 1px solid var(--ula-border-subtle); border-radius: var(--ula-radius-lg, 16px); padding: 18px; position: relative;';
        card.innerHTML = `
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
                <strong style="font-size: 13px; color: var(--ula-palm-900); display: flex; align-items: center; gap: 6px;">
                    <span class="material-symbols-rounded" style="font-size: 16px;">account_balance</span>
                    <span>{{ __('Bank Account') }} #${count}</span>
                </strong>
                <button type="button" onclick="this.closest('.bank-account-card').remove()" class="tactile-btn" style="padding: 4px 10px; font-size: 11px; color: var(--ula-status-danger); display: inline-flex; align-items: center; gap: 4px;">
                    <span class="material-symbols-rounded" style="font-size: 14px;">delete</span>
                    <span>{{ __('Remove') }}</span>
                </button>
            </div>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 12px;">
                <div>
                    <label style="display: block; font-size: 11px; font-weight: 700; color: var(--ula-text-secondary); margin-bottom: 4px;">{{ __('Bank Name') }} *</label>
                    <input type="text" name="bank_name[]" required placeholder="e.g. Al Rajhi Bank" class="form-input" style="width: 100%; background: var(--ula-surface-card); border: 1px solid var(--ula-border-subtle); border-radius: 8px; padding: 8px 12px; font-size: 13px;">
                </div>
                <div>
                    <label style="display: block; font-size: 11px; font-weight: 700; color: var(--ula-text-secondary); margin-bottom: 4px;">{{ __('Beneficiary Name (Arabic)') }}</label>
                    <input type="text" name="account_name[]" dir="rtl" placeholder="اسم المستفيد بالعربية" class="form-input" style="width: 100%; background: var(--ula-surface-card); border: 1px solid var(--ula-border-subtle); border-radius: 8px; padding: 8px 12px; font-size: 13px;">
                </div>
                <div>
                    <label style="display: block; font-size: 11px; font-weight: 700; color: var(--ula-text-secondary); margin-bottom: 4px;">{{ __('Beneficiary Name (English)') }}</label>
                    <input type="text" name="account_name_en[]" dir="ltr" placeholder="Beneficiary name in English" class="form-input" style="width: 100%; background: var(--ula-surface-card); border: 1px solid var(--ula-border-subtle); border-radius: 8px; padding: 8px 12px; font-size: 13px;">
                </div>
                <div>
                    <label style="display: block; font-size: 11px; font-weight: 700; color: var(--ula-text-secondary); margin-bottom: 4px;">IBAN *</label>
                    <input type="text" name="iban[]" required placeholder="SA..." class="form-input" style="width: 100%; background: var(--ula-surface-card); border: 1px solid var(--ula-border-subtle); border-radius: 8px; padding: 8px 12px; font-size: 13px; font-family: 'IBM Plex Mono', monospace; font-weight: 700;">
                </div>
            </div>
        `;
        container.appendChild(card);
    }
</script>
@endsection
