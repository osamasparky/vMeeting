<div id="tab-settings" class="tab-view">
    <div class="page-header" style="margin-bottom: 20px;">
        <h1 class="page-title" style="font-size: 22px; font-weight: 800; color: var(--ula-text-primary); margin-bottom: 4px; display: flex; align-items: center; gap: 8px;">
            <span class="material-symbols-rounded" style="font-size: 24px; color: var(--ula-highlight-default);">settings</span>
            <span>{{ __('Workspace Settings') }}</span>
        </h1>
        <p class="page-subtitle" style="font-size: 13px; color: var(--ula-text-secondary);">{{ __('Configure organization branding, custom SMTP mail servers, and AI office blueprint engine.') }}</p>
    </div>

    @if(session('success'))
        <div style="background: rgba(60, 107, 76, 0.12); border: 1px solid var(--ula-palm-500); color: var(--ula-palm-700); padding: 14px 18px; border-radius: var(--ula-radius-md); margin-bottom: 20px; font-size: 13px; font-weight: 700; display: flex; align-items: center; gap: 10px;">
            <span class="material-symbols-rounded" style="font-size: 18px;">check_circle</span>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    @if($errors->any())
        <div style="background: rgba(201, 116, 58, 0.12); border: 1px solid var(--ula-status-danger); color: var(--ula-status-danger); padding: 14px 18px; border-radius: var(--ula-radius-md); margin-bottom: 20px; font-size: 13px; font-weight: 700;">
            <ul style="margin: 0; padding-inline-start: 20px;">
                @foreach($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- Organization Settings Sub-Tabs Header Navigation -->
    <div class="org-settings-tabs-nav" style="display: flex; gap: 8px; margin-bottom: 20px; background: var(--ula-surface-card); padding: 6px; border-radius: var(--ula-radius-xl); border: 1px solid var(--ula-border-subtle); box-shadow: var(--ula-shadow-sm); width: fit-content; max-width: 100%; overflow-x: auto; scrollbar-width: none;">
        <button type="button" class="org-subtab-btn active" onclick="switchOrgSettingsTab('general', this)" id="org-subtab-btn-general">
            <span class="material-symbols-rounded" style="font-size: 16px;">corporate_fare</span>
            <span>{{ __('General & Branding') }}</span>
        </button>
        <button type="button" class="org-subtab-btn" onclick="switchOrgSettingsTab('smtp', this)" id="org-subtab-btn-smtp">
            <span class="material-symbols-rounded" style="font-size: 16px;">mail</span>
            <span>{{ __('SMTP Mail Server') }}</span>
        </button>
        <button type="button" class="org-subtab-btn" onclick="switchOrgSettingsTab('ai', this)" id="org-subtab-btn-ai">
            <span class="material-symbols-rounded" style="font-size: 16px;">auto_awesome</span>
            <span>{{ __('AI Blueprint Engine') }}</span>
        </button>
        <button type="button" class="org-subtab-btn" onclick="switchOrgSettingsTab('attendance', this)" id="org-subtab-btn-attendance">
            <span class="material-symbols-rounded" style="font-size: 16px;">timer</span>
            <span>{{ __('Attendance & Inactivity Policy') }}</span>
        </button>
    </div>

    <form method="POST" action="{{ route('organization.settings.update') }}" enctype="multipart/form-data">
        @csrf

        <!-- 1. SUB-TAB: General & Branding -->
        <div id="org-subtab-content-general" class="org-subtab-pane active" style="display: block;">
            <div class="card" style="max-width: 720px; border-radius: var(--ula-radius-xl); padding: 26px;">
                <div style="margin-bottom: 20px;">
                    <h3 style="font-size: 16px; font-weight: 800; color: var(--ula-text-primary); margin: 0 0 4px 0; display: flex; align-items: center; gap: 8px;">
                        <span class="material-symbols-rounded" style="color: var(--ula-highlight-default);">corporate_fare</span>
                        <span>{{ __('Workspace Identity & General Info') }}</span>
                    </h3>
                    <p style="font-size: 12px; color: var(--ula-text-muted); margin: 0;">
                        {{ __('Manage your company name, logo icon, URL slug, and default timezone.') }}
                    </p>
                </div>

                <div style="display: flex; flex-direction: column; gap: 18px;">
                    <!-- Company Logo Upload -->
                    <div>
                        <label style="display: block; font-size: 11px; font-weight: 700; color: var(--ula-text-secondary); margin-bottom: 8px; text-transform: uppercase;">
                            {{ __('Company Logo / Workspace Icon') }}
                        </label>
                        <div style="display: flex; align-items: center; gap: 18px; background: var(--ula-sand-100); padding: 16px; border-radius: var(--ula-radius-md); border: 1px solid var(--ula-border-subtle);">
                            <div style="width: 64px; height: 64px; border-radius: 16px; background: var(--ula-surface-card); border: 2px dashed var(--ula-border-subtle); display: flex; align-items: center; justify-content: center; overflow: hidden; flex-shrink: 0; position: relative; box-shadow: var(--ula-shadow-sm);">
                                <img id="logo-preview-img" src="{{ $organization->logo_url ? $organization->logo_url : '' }}" alt="Logo" style="width: 100%; height: 100%; object-fit: cover; {{ $organization->logo_url ? '' : 'display: none;' }}">
                                <div id="logo-preview-placeholder" style="font-size: 28px; {{ $organization->logo_url ? 'display: none;' : '' }}">
                                    <span class="material-symbols-rounded" style="font-size: 32px; color: var(--ula-highlight-default);">corporate_fare</span>
                                </div>
                            </div>
                            <div style="flex: 1;">
                                <div style="font-size: 13px; font-weight: 700; color: var(--ula-text-primary); margin-bottom: 4px;">{{ __('Upload Logo Image') }}</div>
                                <div style="font-size: 11px; color: var(--ula-text-muted); margin-bottom: 10px;">{{ __('Appears in the top sidebar beside the company name. Recommended: PNG, JPG, SVG or WebP up to 4MB.') }}</div>
                                <input type="file" name="logo" id="org-logo-input" accept="image/*" onchange="previewCompanyLogo(this)" style="font-size: 12px; color: var(--ula-text-secondary);">
                            </div>
                        </div>
                    </div>

                    <!-- Workspace Name -->
                    <div>
                        <label style="display: block; font-size: 11px; font-weight: 700; color: var(--ula-text-secondary); margin-bottom: 6px; text-transform: uppercase;">
                            {{ __('Workspace / Company Name') }}
                        </label>
                        <input type="text" name="name" required value="{{ old('name', $organization->name) }}" placeholder="e.g. Acme Corp" style="width: 100%; background: var(--ula-surface-card); border: 1px solid var(--ula-border-subtle); border-radius: 10px; padding: 11px 14px; color: var(--ula-text-primary); font-size: 13px; font-weight: 500;">
                    </div>

                    <!-- Workspace Slug (Read-only) -->
                    <div>
                        <label style="display: block; font-size: 11px; font-weight: 700; color: var(--ula-text-secondary); margin-bottom: 6px; text-transform: uppercase;">
                            {{ __('Workspace URL Slug') }}
                        </label>
                        <input type="text" value="{{ $organization->slug }}" readonly style="width: 100%; background: var(--ula-sand-200); border: 1px solid var(--ula-border-subtle); border-radius: 10px; padding: 11px 14px; color: var(--ula-palm-900); font-size: 13px; font-family: 'IBM Plex Mono', monospace; font-weight: 600;">
                        <span style="display: block; font-size: 10px; color: var(--ula-text-muted); margin-top: 4px;">{{ __('Used for organization identification across the workspace.') }}</span>
                    </div>

                    <!-- Timezone -->
                    <div>
                        <label style="display: block; font-size: 11px; font-weight: 700; color: var(--ula-text-secondary); margin-bottom: 6px; text-transform: uppercase;">
                            {{ __('Timezone') }}
                        </label>
                        <select name="timezone" style="width: 100%; background: var(--ula-surface-card); border: 1px solid var(--ula-border-subtle); border-radius: 10px; padding: 11px 14px; color: var(--ula-text-primary); font-size: 13px; font-weight: 500;">
                            @php
                                $commonTimezones = [
                                    'UTC' => 'UTC (Coordinated Universal Time)',
                                    'Africa/Cairo' => 'Africa/Cairo (EET / EEST)',
                                    'Asia/Riyadh' => 'Asia/Riyadh (AST)',
                                    'Asia/Dubai' => 'Asia/Dubai (GST)',
                                    'Europe/London' => 'Europe/London (GMT / BST)',
                                    'Europe/Paris' => 'Europe/Paris (CET / CEST)',
                                    'America/New_York' => 'America/New_York (EST / EDT)',
                                    'America/Chicago' => 'America/Chicago (CST / CDT)',
                                    'America/Los_Angeles' => 'America/Los_Angeles (PST / PDT)',
                                    'Asia/Singapore' => 'Asia/Singapore (SGT)',
                                    'Asia/Tokyo' => 'Asia/Tokyo (JST)',
                                ];
                            @endphp
                            @foreach($commonTimezones as $tzKey => $tzLabel)
                                <option value="{{ $tzKey }}" {{ $organization->timezone === $tzKey ? 'selected' : '' }}>{{ $tzLabel }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div style="margin-top: 24px; padding-top: 16px; border-top: 1px solid var(--ula-border-subtle); display: flex; justify-content: flex-end;">
                    <x-btn variant="primary" size="md" type="submit" icon="save">
                        {{ __('Save Changes') }}
                    </x-btn>
                </div>
            </div>
        </div>

        <!-- 2. SUB-TAB: SMTP Mail Server -->
        <div id="org-subtab-content-smtp" class="org-subtab-pane" style="display: none;">
            <div class="card" style="max-width: 720px; border-radius: var(--ula-radius-xl); padding: 26px;">
                <div style="margin-bottom: 20px;">
                    <h3 style="font-size: 16px; font-weight: 800; color: var(--ula-text-primary); margin: 0 0 4px 0; display: flex; align-items: center; gap: 8px;">
                        <span class="material-symbols-rounded" style="color: var(--ula-highlight-default);">mail</span>
                        <span>{{ __('Outgoing SMTP Email Server') }}</span>
                    </h3>
                    <p style="font-size: 12px; color: var(--ula-text-muted); margin: 0;">
                        {{ __('Configure your dedicated SMTP mail provider to send meeting invites, reminders, and alerts under your company name.') }}
                    </p>
                </div>

                <div style="display: flex; flex-direction: column; gap: 16px;">
                    <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 12px;">
                        <div>
                            <label style="display: block; font-size: 11px; font-weight: 700; color: var(--ula-text-secondary); margin-bottom: 4px; text-transform: uppercase;">
                                {{ __('SMTP Host / Server') }}
                            </label>
                            <input type="text" name="mail_host" id="smtp-host-input" value="{{ old('mail_host', $smtpSettings['mail_host'] ?? '') }}" placeholder="e.g. smtp.gmail.com or smtp.mailgun.org" style="width: 100%; background: var(--ula-surface-card); border: 1px solid var(--ula-border-subtle); border-radius: 10px; padding: 10px 14px; color: var(--ula-text-primary); font-size: 13px; font-weight: 500;">
                        </div>
                        <div>
                            <label style="display: block; font-size: 11px; font-weight: 700; color: var(--ula-text-secondary); margin-bottom: 4px; text-transform: uppercase;">
                                {{ __('Port') }}
                            </label>
                            <input type="number" name="mail_port" id="smtp-port-input" value="{{ old('mail_port', $smtpSettings['mail_port'] ?? '587') }}" placeholder="587" style="width: 100%; background: var(--ula-surface-card); border: 1px solid var(--ula-border-subtle); border-radius: 10px; padding: 10px 14px; color: var(--ula-text-primary); font-size: 13px; font-weight: 500; font-family: 'IBM Plex Mono', monospace;">
                        </div>
                    </div>

                    <div style="display: grid; grid-template-columns: 1.2fr 1.2fr 0.8fr; gap: 12px;">
                        <div>
                            <label style="display: block; font-size: 11px; font-weight: 700; color: var(--ula-text-secondary); margin-bottom: 4px; text-transform: uppercase;">
                                {{ __('SMTP Username') }}
                            </label>
                            <input type="text" name="mail_username" id="smtp-username-input" value="{{ old('mail_username', $smtpSettings['mail_username'] ?? '') }}" placeholder="api / user@domain.com" style="width: 100%; background: var(--ula-surface-card); border: 1px solid var(--ula-border-subtle); border-radius: 10px; padding: 10px 14px; color: var(--ula-text-primary); font-size: 13px; font-weight: 500;">
                        </div>
                        <div>
                            <label style="display: block; font-size: 11px; font-weight: 700; color: var(--ula-text-secondary); margin-bottom: 4px; text-transform: uppercase;">
                                {{ __('SMTP Password') }}
                            </label>
                            <input type="password" name="mail_password" id="smtp-password-input" placeholder="{{ !empty($smtpSettings['mail_password']) ? '••••••••••••' : 'App Password / Secret' }}" style="width: 100%; background: var(--ula-surface-card); border: 1px solid var(--ula-border-subtle); border-radius: 10px; padding: 10px 14px; color: var(--ula-text-primary); font-size: 13px; font-weight: 500;">
                        </div>
                        <div>
                            <label style="display: block; font-size: 11px; font-weight: 700; color: var(--ula-text-secondary); margin-bottom: 4px; text-transform: uppercase;">
                                {{ __('Encryption') }}
                            </label>
                            <select name="mail_encryption" id="smtp-encryption-input" style="width: 100%; background: var(--ula-surface-card); border: 1px solid var(--ula-border-subtle); border-radius: 10px; padding: 10px 14px; color: var(--ula-text-primary); font-size: 13px; font-weight: 500;">
                                <option value="tls" {{ ($smtpSettings['mail_encryption'] ?? 'tls') === 'tls' ? 'selected' : '' }}>TLS</option>
                                <option value="ssl" {{ ($smtpSettings['mail_encryption'] ?? '') === 'ssl' ? 'selected' : '' }}>SSL</option>
                                <option value="none" {{ ($smtpSettings['mail_encryption'] ?? '') === 'none' ? 'selected' : '' }}>None</option>
                            </select>
                        </div>
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                        <div>
                            <label style="display: block; font-size: 11px; font-weight: 700; color: var(--ula-text-secondary); margin-bottom: 4px; text-transform: uppercase;">
                                {{ __('Sender Email (From)') }}
                            </label>
                            <input type="email" name="mail_from_address" id="smtp-from-email-input" value="{{ old('mail_from_address', $smtpSettings['mail_from_address'] ?? 'noreply@' . $organization->slug . '.com') }}" placeholder="noreply@domain.com" style="width: 100%; background: var(--ula-surface-card); border: 1px solid var(--ula-border-subtle); border-radius: 10px; padding: 10px 14px; color: var(--ula-text-primary); font-size: 13px; font-weight: 500;">
                        </div>
                        <div>
                            <label style="display: block; font-size: 11px; font-weight: 700; color: var(--ula-text-secondary); margin-bottom: 4px; text-transform: uppercase;">
                                {{ __('Sender Display Name') }}
                            </label>
                            <input type="text" name="mail_from_name" id="smtp-from-name-input" value="{{ old('mail_from_name', $smtpSettings['mail_from_name'] ?? $organization->name) }}" placeholder="{{ $organization->name }}" style="width: 100%; background: var(--ula-surface-card); border: 1px solid var(--ula-border-subtle); border-radius: 10px; padding: 10px 14px; color: var(--ula-text-primary); font-size: 13px; font-weight: 500;">
                        </div>
                    </div>

                    <!-- Test SMTP Box -->
                    <div style="display: flex; align-items: center; justify-content: space-between; gap: 12px; background: var(--ula-sand-100); border: 1px solid var(--ula-border-subtle); border-radius: 12px; padding: 12px 16px; flex-wrap: wrap;">
                        <div style="font-size: 12px; color: var(--ula-text-secondary); display: flex; align-items: center; gap: 6px;">
                            <span class="material-symbols-rounded" style="font-size: 16px;">mark_email_read</span>
                            <span>{{ __('Send a test email to') }} <strong>{{ $user->email }}</strong></span>
                        </div>
                        <x-btn variant="secondary" size="sm" type="button" onclick="testSmtpConnectionAction()" id="btn-test-smtp" icon="science">
                            {{ __('Test SMTP Connection') }}
                        </x-btn>
                    </div>
                    <div id="smtp-test-result-box" style="display: none; padding: 10px 14px; border-radius: 10px; font-size: 12px; font-weight: 700;"></div>
                </div>

                <div style="margin-top: 24px; padding-top: 16px; border-top: 1px solid var(--ula-border-subtle); display: flex; justify-content: flex-end;">
                    <x-btn variant="primary" size="md" type="submit" icon="save">
                        {{ __('Save SMTP Changes') }}
                    </x-btn>
                </div>
            </div>
        </div>

        <!-- 3. SUB-TAB: AI Floorplan Engine (OpenAI) -->
        <div id="org-subtab-content-ai" class="org-subtab-pane" style="display: none;">
            <div class="card" style="max-width: 720px; border-radius: var(--ula-radius-xl); padding: 26px;">
                <div style="margin-bottom: 20px;">
                    <h3 style="font-size: 16px; font-weight: 800; color: var(--ula-text-primary); margin: 0 0 4px 0; display: flex; align-items: center; gap: 8px;">
                        <span class="material-symbols-rounded" style="color: var(--ula-highlight-default);">auto_awesome</span>
                        <span>{{ __('OpenAI & AI Floorplan Generator') }}</span>
                    </h3>
                    <p style="font-size: 12px; color: var(--ula-text-muted); margin: 0;">
                        {{ __('Add your company OpenAI API key to generate bespoke 2D architectural office blueprints directly from the editor without platform rate limits.') }}
                    </p>
                </div>

                <!-- Cost Optimization Notice -->
                <div style="background: rgba(60, 107, 76, 0.08); border: 1px solid var(--ula-palm-500); border-radius: 12px; padding: 14px 16px; margin-bottom: 18px; font-size: 12px; line-height: 1.5; color: var(--ula-text-primary);">
                    <div style="font-weight: 700; color: var(--ula-palm-700); margin-bottom: 4px; display: flex; align-items: center; gap: 6px;">
                        <span class="material-symbols-rounded" style="font-size: 16px;">lightbulb</span>
                        <span>{{ __('Token & Cost Optimization Enabled') }}</span>
                    </div>
                    <span style="color: var(--ula-text-secondary); font-size: 11px;">
                        {{ __('Prompts are ultra-compressed to ~60 tokens. Choosing GPT Image 1 Mini or DALL-E 2 with 1024x1024 reduces your cost to approx $0.015 - $0.02 per generated floorplan.') }}
                    </span>
                </div>

                <div style="display: flex; flex-direction: column; gap: 16px;">
                    <!-- API Key Input -->
                    <div>
                        <label style="display: block; font-size: 11px; font-weight: 700; color: var(--ula-text-secondary); margin-bottom: 4px; text-transform: uppercase;">
                            {{ __('Company OpenAI Secret Key (sk-...)') }}
                        </label>
                        <div style="display: flex; gap: 8px;">
                            <input type="password" name="openai_api_key" id="org-openai-key-input" placeholder="{{ !empty($openAiSettings['api_key']) ? '••••••••••••••••••••••••••••••••' : 'sk-proj-... / sk-svcacct-...' }}" style="flex: 1; background: var(--ula-surface-card); border: 1px solid var(--ula-border-subtle); border-radius: 10px; padding: 10px 14px; color: var(--ula-text-primary); font-size: 13px; font-family: 'IBM Plex Mono', monospace; font-weight: 500;">
                            <x-btn variant="secondary" size="sm" type="button" onclick="testOrgAiConnectionAction()" id="btn-test-org-ai" icon="bolt" style="white-space: nowrap;">
                                {{ __('Test Key') }}
                            </x-btn>
                        </div>
                        <div id="org-ai-test-result-box" style="display: none; margin-top: 8px; padding: 8px 12px; border-radius: 8px; font-size: 12px; font-weight: 700;"></div>
                    </div>

                    <!-- Generation Model & Image Dimensions -->
                    <div style="display: grid; grid-template-columns: 1.4fr 1fr; gap: 12px;">
                        <div>
                            <label style="display: block; font-size: 11px; font-weight: 700; color: var(--ula-text-secondary); margin-bottom: 4px; text-transform: uppercase;">
                                {{ __('Image Generation Model') }}
                            </label>
                            <select name="openai_model" style="width: 100%; background: var(--ula-surface-card); border: 1px solid var(--ula-border-subtle); border-radius: 10px; padding: 10px 14px; color: var(--ula-text-primary); font-size: 12px; font-weight: 600;">
                                <option value="gpt-image-1-mini" {{ ($openAiSettings['model'] ?? 'gpt-image-1-mini') === 'gpt-image-1-mini' ? 'selected' : '' }}>
                                    GPT Image 1 Mini (Low Cost ~$0.015)
                                </option>
                                <option value="gpt-image-1" {{ ($openAiSettings['model'] ?? '') === 'gpt-image-1' ? 'selected' : '' }}>
                                    GPT Image 1 (High Quality Standard ~$0.040)
                                </option>
                                <option value="dall-e-2" {{ ($openAiSettings['model'] ?? '') === 'dall-e-2' ? 'selected' : '' }}>
                                    DALL-E 2 (Economy Legacy ~$0.020)
                                </option>
                                <option value="dall-e-3" {{ ($openAiSettings['model'] ?? '') === 'dall-e-3' ? 'selected' : '' }}>
                                    DALL-E 3 (High Definition Art ~$0.080)
                                </option>
                            </select>
                        </div>
                        <div>
                            <label style="display: block; font-size: 11px; font-weight: 700; color: var(--ula-text-secondary); margin-bottom: 4px; text-transform: uppercase;">
                                {{ __('Floorplan Dimensions') }}
                            </label>
                            <select name="openai_image_size" style="width: 100%; background: var(--ula-surface-card); border: 1px solid var(--ula-border-subtle); border-radius: 10px; padding: 10px 14px; color: var(--ula-text-primary); font-size: 12px; font-weight: 600;">
                                <option value="1024x1024" {{ ($openAiSettings['image_size'] ?? '1024x1024') === '1024x1024' ? 'selected' : '' }}>
                                    1024 × 1024 (Square 1:1 - Low Cost)
                                </option>
                                <option value="1792x1024" {{ ($openAiSettings['image_size'] ?? '') === '1792x1024' ? 'selected' : '' }}>
                                    1792 × 1024 (Widescreen 16:9)
                                </option>
                            </select>
                        </div>
                    </div>
                </div>

                <div style="margin-top: 24px; padding-top: 16px; border-top: 1px solid var(--ula-border-subtle); display: flex; justify-content: flex-end;">
                    <x-btn variant="primary" size="md" type="submit" icon="save">
                        {{ __('Save AI Settings') }}
                    </x-btn>
                </div>
            </div>
        </div>

        <!-- 4. SUB-TAB: Attendance & Time Tracking Policy -->
        <div id="org-subtab-content-attendance" class="org-subtab-pane" style="display: none;">
            <div class="card" style="max-width: 720px; border-radius: var(--ula-radius-xl); padding: 26px;">
                <div style="margin-bottom: 20px;">
                    <h3 style="font-size: 16px; font-weight: 800; color: var(--ula-text-primary); margin: 0 0 4px 0; display: flex; align-items: center; gap: 8px;">
                        <span class="material-symbols-rounded" style="color: var(--ula-highlight-default);">timer</span>
                        <span>{{ __('Attendance & Smart Inactivity Policy') }}</span>
                    </h3>
                    <p style="font-size: 12px; color: var(--ula-text-muted); margin: 0;">
                        {{ __('Configure automated virtual office presence recording, task execution rules, and smart idle prompts.') }}
                    </p>
                </div>

                <div style="display: flex; flex-direction: column; gap: 18px;">
                    <!-- Auto Attendance Toggle -->
                    <div style="display: flex; align-items: center; justify-content: space-between; background: var(--ula-sand-100); padding: 16px; border-radius: var(--ula-radius-md); border: 1px solid var(--ula-border-subtle);">
                        <div>
                            <div style="font-size: 13px; font-weight: 700; color: var(--ula-text-primary); margin-bottom: 2px;">
                                {{ __('Automatic Office Attendance Recording') }}
                            </div>
                            <div style="font-size: 11px; color: var(--ula-text-muted);">
                                {{ __('Automatically start tracking user attendance time when they enter the 3D virtual office.') }}
                            </div>
                        </div>
                        <label style="display: flex; align-items: center; gap: 8px; cursor: pointer;">
                            <input type="checkbox" name="attendance_auto_enabled" value="1" {{ ($attendancePolicy['auto_attendance_enabled'] ?? true) ? 'checked' : '' }} style="width: 18px; height: 18px; accent-color: var(--ula-palm-900);">
                            <span style="font-size: 12px; font-weight: 700; color: var(--ula-palm-900);">{{ __('Enabled') }}</span>
                        </label>
                    </div>

                    <!-- Inactivity Check Interval & Grace Period -->
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px;">
                        <div>
                            <label style="display: block; font-size: 11px; font-weight: 700; color: var(--ula-text-secondary); margin-bottom: 6px; text-transform: uppercase;">
                                {{ __('Idle Check Interval (Minutes)') }}
                            </label>
                            <input type="number" name="attendance_idle_prompt_minutes" min="1" max="120" value="{{ $attendancePolicy['idle_prompt_minutes'] ?? 15 }}" style="width: 100%; background: var(--ula-surface-card); border: 1px solid var(--ula-border-subtle); border-radius: 10px; padding: 10px 14px; color: var(--ula-text-primary); font-size: 13px; font-weight: 600; font-family: 'IBM Plex Mono', monospace;">
                            <span style="font-size: 10px; color: var(--ula-text-muted); margin-top: 4px; display: block;">
                                {{ __('If user is idle without a running task, system asks "Are you still online?" after this time.') }}
                            </span>
                        </div>

                        <div>
                            <label style="display: block; font-size: 11px; font-weight: 700; color: var(--ula-text-secondary); margin-bottom: 6px; text-transform: uppercase;">
                                {{ __('Confirmation Grace Period (Seconds)') }}
                            </label>
                            <input type="number" name="attendance_idle_grace_seconds" min="30" max="600" value="{{ $attendancePolicy['idle_response_grace_seconds'] ?? 180 }}" style="width: 100%; background: var(--ula-surface-card); border: 1px solid var(--ula-border-subtle); border-radius: 10px; padding: 10px 14px; color: var(--ula-text-primary); font-size: 13px; font-weight: 600; font-family: 'IBM Plex Mono', monospace;">
                            <span style="font-size: 10px; color: var(--ula-text-muted); margin-top: 4px; display: block;">
                                {{ __('Countdown window to answer before attendance time is automatically paused.') }}
                            </span>
                        </div>
                    </div>

                    <!-- Active Task Protection Note -->
                    <div style="background: rgba(60, 107, 76, 0.08); border: 1px solid var(--ula-palm-500); border-radius: 12px; padding: 14px 16px; font-size: 12px; line-height: 1.5; color: var(--ula-text-primary);">
                        <div style="font-weight: 700; color: var(--ula-palm-700); margin-bottom: 4px; display: flex; align-items: center; gap: 6px;">
                            <span class="material-symbols-rounded" style="font-size: 16px;">shield</span>
                            <span>{{ __('Smart Active Task Protection') }}</span>
                        </div>
                        <span style="color: var(--ula-text-secondary); font-size: 11px;">
                            {{ __('When a member has an active running task in the office, idle prompts are automatically bypassed so deep work is never interrupted.') }}
                        </span>
                    </div>
                </div>

                <div style="margin-top: 24px; padding-top: 16px; border-top: 1px solid var(--ula-border-subtle); display: flex; justify-content: flex-end;">
                    <x-btn variant="primary" size="md" type="submit" icon="save">
                        {{ __('Save Attendance Policy') }}
                    </x-btn>
                </div>
            </div>
        </div>
    </form>
</div>
        
