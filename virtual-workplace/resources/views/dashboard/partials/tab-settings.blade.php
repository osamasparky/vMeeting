{{--
    Workspace Settings — design-reference "23 - Workspace Settings".
    Section nav (260px) beside a stack of cards; the nav scrolls to a card and tracks the one in view.
    One form: every "save" button posts all four sections to organization.settings.update.
--}}
@php
    $zones = collect(DateTimeZone::listIdentifiers())->mapWithKeys(function ($tz) {
        $offset = (new DateTimeZone($tz))->getOffset(new DateTime('now', new DateTimeZone('UTC')));
        $h = intdiv(abs($offset), 3600);
        $m = intdiv(abs($offset) % 3600, 60);
        $gmt = 'GMT' . ($offset === 0 ? '' : ($offset > 0 ? '+' : '-') . $h . ($m ? ':' . str_pad($m, 2, '0', STR_PAD_LEFT) : ''));
        return [$tz => "{$tz} ({$gmt})"];
    });
    $currentTz = old('timezone', $organization->timezone ?: 'UTC');
    $autoAttendance = (bool) old('attendance_auto_enabled', $attendancePolicy['auto_attendance_enabled'] ?? true);
    $settingsSections = [
        ['general', 'corporate_fare', 'settings.nav_general'],
        ['smtp', 'mail', 'settings.nav_smtp'],
        ['ai', 'auto_awesome', 'settings.nav_ai'],
        ['attendance', 'timer', 'settings.nav_attendance'],
    ];
    $selectClass = 'h-[46px] w-full appearance-none rounded-[var(--ula-radius-md)] border border-[var(--ula-border-default)] bg-[var(--ula-surface-page)] ps-10 pe-10 text-[15px] text-[var(--ula-text-primary)] transition-[border-color,box-shadow] duration-[var(--ula-duration-fast)] ease-[var(--ula-ease-out)] focus:border-[var(--ula-border-focus)] focus:outline-none focus-visible:shadow-[var(--ula-focus-ring)]';
@endphp

<div id="tab-settings" class="tab-view">
    <div class="ula-set-head">
        <div class="ula-headline-group">
            <h2 class="ula-headline-ar" style="font-size: var(--ula-size-h1); margin: 0;">{{ __('page.settings') }}</h2>
            @if(app()->getLocale() === 'ar')<span class="ula-headline-en" style="font-size: var(--ula-size-h4);">Workspace Settings</span>@endif
        </div>
    </div>

    {{-- The success flash is shown by the dashboard shell; only validation errors render here. --}}
    @if($errors->any())
        <div class="ula-set-alert ula-set-alert--error" role="alert">
            <span class="material-symbols-rounded" aria-hidden="true">error</span>
            <ul>
                @foreach($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="ula-set-layout">
        <nav class="ula-set-nav" aria-label="{{ __('page.settings') }}">
            @foreach($settingsSections as [$key, $icon, $label])
                <button type="button" id="org-subtab-btn-{{ $key }}" class="ula-set-nav-item org-subtab-btn {{ $loop->first ? 'active' : '' }}" onclick="switchOrgSettingsTab('{{ $key }}', this)" @if($loop->first) aria-current="true" @endif>
                    <span class="material-symbols-rounded" aria-hidden="true">{{ $icon }}</span>
                    <span>{{ __($label) }}</span>
                </button>
            @endforeach
        </nav>

        <form method="POST" action="{{ route('organization.settings.update') }}" enctype="multipart/form-data" class="ula-set-stack">
            @csrf

            {{-- 1. General & identity --}}
            <section id="org-subtab-content-general" class="ula-set-card org-subtab-pane" aria-labelledby="set-h-general">
                <div class="ula-set-card-head">
                    <span class="material-symbols-rounded" aria-hidden="true">corporate_fare</span>
                    <h3 id="set-h-general">{{ __('settings.general_title') }}</h3>
                </div>

                <div class="ula-set-logo-row">
                    <div class="ula-set-logo-tile">
                        <img id="logo-preview-img" src="{{ $organization->logo_url ?: '' }}" alt="{{ $organization->name }}" @unless($organization->logo_url) hidden @endunless>
                        <svg id="logo-preview-placeholder" role="img" aria-label="UlaSpace" viewBox="-1.2 -1.3 60 40" fill="currentColor" @if($organization->logo_url) hidden @endif><path d="M0 38.734L1.493 30.973L4.179 20.824L6.865 11.869C8.259 7.491 11.94 4.207 17.91 2.018C26.268 -0.569 34.427 -0.669 42.387 1.719C49.153 3.311 54.128 7.292 57.312 13.66L57.312 38.734L26.268 38.734L25.074 27.988C23.482 20.824 21.591 17.242 19.403 17.242C17.214 18.038 15.721 21.819 14.925 28.585L14.328 38.734L0 38.734Z"/></svg>
                    </div>
                    <div class="ula-set-logo-text">
                        <span class="ula-set-label">{{ __('settings.logo_label') }}</span>
                        <div class="ula-set-logo-actions">
                            <x-btn variant="secondary" size="sm" icon="upload" onclick="document.getElementById('org-logo-input').click()">{{ __('settings.logo_upload') }}</x-btn>
                            <x-btn variant="ghost" size="sm" icon="delete" id="btn-remove-logo" onclick="removeCompanyLogo()" :style="$organization->logo_url ? '' : 'display: none;'">{{ __('settings.logo_remove') }}</x-btn>
                        </div>
                        <span class="ula-set-help">{{ __('settings.logo_help') }}</span>
                        <input type="file" name="logo" id="org-logo-input" accept="image/png,image/jpeg,image/gif,image/svg+xml,image/webp" onchange="previewCompanyLogo(this)" data-too-large="{{ __('settings.logo_too_large') }}" class="sr-only" tabindex="-1">
                        <input type="hidden" name="remove_logo" id="org-remove-logo" value="0">
                    </div>
                </div>

                <div class="ula-set-grid">
                    <x-input name="name" :label="__('settings.name_label')" icon="apartment" :value="old('name', $organization->name)" required />
                    <x-input id="org-slug" :label="__('settings.slug_label')" icon="link" :value="$organization->slug" :helper="__('settings.slug_help')" readonly style="font-family: var(--ula-font-mono); unicode-bidi: plaintext;" />
                    <div class="flex flex-col gap-[7px]">
                        <label for="org-timezone" class="text-[15px] font-medium text-[var(--ula-text-primary)]">{{ __('settings.timezone_label') }}</label>
                        <div class="ula-set-select">
                            <span class="ula-set-select-icon" aria-hidden="true"><span class="material-symbols-rounded">schedule</span></span>
                            <select name="timezone" id="org-timezone" class="{{ $selectClass }}" style="unicode-bidi: plaintext;">
                                @foreach($zones as $tzKey => $tzLabel)
                                    <option value="{{ $tzKey }}" @selected($currentTz === $tzKey)>{{ $tzLabel }}</option>
                                @endforeach
                            </select>
                            <span class="ula-set-select-caret" aria-hidden="true"><span class="material-symbols-rounded">expand_more</span></span>
                        </div>
                    </div>
                </div>

                <div class="ula-set-actions">
                    <x-btn variant="primary" size="md" type="submit" icon="save">{{ __('settings.save') }}</x-btn>
                </div>
            </section>

            {{-- 2. SMTP --}}
            <section id="org-subtab-content-smtp" class="ula-set-card org-subtab-pane" aria-labelledby="set-h-smtp">
                <div class="ula-set-card-head">
                    <span class="material-symbols-rounded" aria-hidden="true">mail</span>
                    <div>
                        <h3 id="set-h-smtp">{{ __('settings.smtp_title') }}</h3>
                        <p>{{ __('settings.smtp_desc') }}</p>
                    </div>
                </div>

                <div class="ula-set-grid">
                    <x-input name="mail_host" id="smtp-host-input" :label="__('settings.smtp_host')" icon="dns" :value="old('mail_host', $smtpSettings['mail_host'] ?? '')" placeholder="smtp.gmail.com" style="unicode-bidi: plaintext;" />
                    <x-input name="mail_port" id="smtp-port-input" type="number" :label="__('settings.smtp_port')" icon="settings_ethernet" :value="old('mail_port', $smtpSettings['mail_port'] ?? '587')" placeholder="587" min="1" max="65535" style="font-family: var(--ula-font-mono);" />
                    <x-input name="mail_username" id="smtp-username-input" :label="__('settings.smtp_username')" icon="person" :value="old('mail_username', $smtpSettings['mail_username'] ?? '')" autocomplete="off" style="unicode-bidi: plaintext;" />
                    <x-input name="mail_password" id="smtp-password-input" type="password" :label="__('settings.smtp_password')" icon="key" :placeholder="!empty($smtpSettings['mail_password']) ? '••••••••••••' : ''" :helper="!empty($smtpSettings['mail_password']) ? __('settings.secret_kept') : null" autocomplete="new-password" />
                    <div class="flex flex-col gap-[7px]">
                        <label for="smtp-encryption-input" class="text-[15px] font-medium text-[var(--ula-text-primary)]">{{ __('settings.smtp_encryption') }}</label>
                        <div class="ula-set-select">
                            <span class="ula-set-select-icon" aria-hidden="true"><span class="material-symbols-rounded">lock</span></span>
                            <select name="mail_encryption" id="smtp-encryption-input" class="{{ $selectClass }}">
                                @foreach(['tls' => 'TLS', 'ssl' => 'SSL', 'none' => __('settings.none')] as $encKey => $encLabel)
                                    <option value="{{ $encKey }}" @selected(old('mail_encryption', $smtpSettings['mail_encryption'] ?? 'tls') === $encKey)>{{ $encLabel }}</option>
                                @endforeach
                            </select>
                            <span class="ula-set-select-caret" aria-hidden="true"><span class="material-symbols-rounded">expand_more</span></span>
                        </div>
                    </div>
                    <x-input name="mail_from_address" id="smtp-from-email-input" type="email" :label="__('settings.smtp_from_address')" icon="alternate_email" :value="old('mail_from_address', $smtpSettings['mail_from_address'] ?? '')" placeholder="noreply@{{ $organization->slug }}.com" style="unicode-bidi: plaintext;" />
                    <x-input name="mail_from_name" id="smtp-from-name-input" :label="__('settings.smtp_from_name')" icon="badge" :value="old('mail_from_name', $smtpSettings['mail_from_name'] ?? $organization->name)" />
                </div>

                <div class="ula-set-inset">
                    <div class="ula-set-inset-text">
                        <span class="material-symbols-rounded" aria-hidden="true">mark_email_read</span>
                        <span>{{ __('settings.smtp_test_to') }} <strong class="ula-set-ltr">{{ $user->email }}</strong></span>
                    </div>
                    <x-btn variant="secondary" size="sm" onclick="testSmtpConnectionAction()" id="btn-test-smtp" icon="science">{{ __('settings.smtp_test') }}</x-btn>
                </div>
                <div id="smtp-test-result-box" class="ula-set-result" role="status" aria-live="polite" hidden></div>

                <div class="ula-set-actions">
                    <x-btn variant="primary" size="md" type="submit" icon="save">{{ __('settings.save') }}</x-btn>
                </div>
            </section>

            {{-- 3. AI floor-plan engine --}}
            <section id="org-subtab-content-ai" class="ula-set-card org-subtab-pane" aria-labelledby="set-h-ai">
                <div class="ula-set-card-head">
                    <span class="material-symbols-rounded" aria-hidden="true">auto_awesome</span>
                    <div>
                        <h3 id="set-h-ai">{{ __('settings.ai_title') }}</h3>
                        <p>{{ __('settings.ai_desc') }}</p>
                    </div>
                </div>

                <div class="ula-set-note">
                    <span class="material-symbols-rounded" aria-hidden="true">lightbulb</span>
                    <div>
                        <strong>{{ __('settings.ai_note_title') }}</strong>
                        <span>{{ __('settings.ai_note_body') }}</span>
                    </div>
                </div>

                <div class="flex flex-col gap-[7px]">
                    <div class="ula-set-key-row">
                        <x-input name="openai_api_key" id="org-openai-key-input" type="password" :label="__('settings.ai_key')" icon="key" :placeholder="!empty($openAiSettings['api_key']) ? '••••••••••••••••••••••••' : 'sk-proj-…'" :helper="!empty($openAiSettings['api_key']) ? __('settings.secret_kept') : null" autocomplete="new-password" style="font-family: var(--ula-font-mono);" data-has-saved="{{ !empty($openAiSettings['api_key']) ? '1' : '0' }}" />
                        <x-btn variant="secondary" size="md" onclick="testOrgAiConnectionAction()" id="btn-test-org-ai" icon="bolt">{{ __('settings.ai_test') }}</x-btn>
                    </div>
                    <div id="org-ai-test-result-box" class="ula-set-result" role="status" aria-live="polite" hidden></div>
                </div>

                <div class="ula-set-grid">
                    <div class="flex flex-col gap-[7px]">
                        <label for="org-openai-model" class="text-[15px] font-medium text-[var(--ula-text-primary)]">{{ __('settings.ai_model') }}</label>
                        <div class="ula-set-select">
                            <span class="ula-set-select-icon" aria-hidden="true"><span class="material-symbols-rounded">image</span></span>
                            <select name="openai_model" id="org-openai-model" class="{{ $selectClass }}">
                                @foreach(['gpt-image-1-mini' => 'GPT Image 1 Mini (~$0.015)', 'gpt-image-1' => 'GPT Image 1 (~$0.040)', 'dall-e-2' => 'DALL-E 2 (~$0.020)', 'dall-e-3' => 'DALL-E 3 (~$0.080)'] as $mKey => $mLabel)
                                    <option value="{{ $mKey }}" @selected(($openAiSettings['model'] ?? 'gpt-image-1-mini') === $mKey)>{{ $mLabel }}</option>
                                @endforeach
                            </select>
                            <span class="ula-set-select-caret" aria-hidden="true"><span class="material-symbols-rounded">expand_more</span></span>
                        </div>
                    </div>
                    <div class="flex flex-col gap-[7px]">
                        <label for="org-openai-size" class="text-[15px] font-medium text-[var(--ula-text-primary)]">{{ __('settings.ai_size') }}</label>
                        <div class="ula-set-select">
                            <span class="ula-set-select-icon" aria-hidden="true"><span class="material-symbols-rounded">aspect_ratio</span></span>
                            <select name="openai_image_size" id="org-openai-size" class="{{ $selectClass }}">
                                <option value="1024x1024" @selected(($openAiSettings['image_size'] ?? '1024x1024') === '1024x1024')>1024 × 1024 · 1:1</option>
                                <option value="1792x1024" @selected(($openAiSettings['image_size'] ?? '') === '1792x1024')>1792 × 1024 · 16:9</option>
                            </select>
                            <span class="ula-set-select-caret" aria-hidden="true"><span class="material-symbols-rounded">expand_more</span></span>
                        </div>
                    </div>
                </div>

                <div class="ula-set-actions">
                    <x-btn variant="primary" size="md" type="submit" icon="save">{{ __('settings.save') }}</x-btn>
                </div>
            </section>

            {{-- 4. Attendance & idle policy --}}
            <section id="org-subtab-content-attendance" class="ula-set-card org-subtab-pane" aria-labelledby="set-h-attendance">
                <div class="ula-set-card-head">
                    <span class="material-symbols-rounded" aria-hidden="true">timer</span>
                    <h3 id="set-h-attendance">{{ __('settings.attendance_title') }}</h3>
                </div>

                <div class="ula-set-toggle-row">
                    <div class="ula-set-toggle-text">
                        <span id="set-auto-attendance-label" class="ula-set-label">{{ __('settings.auto_attendance') }}</span>
                        <span class="ula-set-help">{{ __('settings.auto_attendance_help') }}</span>
                    </div>
                    {{-- Hidden 0 first so an unchecked switch still posts a value and can turn the policy off. --}}
                    <input type="hidden" name="attendance_auto_enabled" value="0">
                    <x-switch name="attendance_auto_enabled" id="set-auto-attendance" :checked="$autoAttendance" aria-labelledby="set-auto-attendance-label" />
                </div>

                <div class="ula-set-grid">
                    <x-input name="attendance_idle_prompt_minutes" type="number" :label="__('settings.idle_minutes')" icon="hourglass_empty" :value="old('attendance_idle_prompt_minutes', $attendancePolicy['idle_prompt_minutes'] ?? 15)" min="1" max="120" required :helper="__('settings.idle_minutes_help')" style="font-family: var(--ula-font-mono);" />
                    <x-input name="attendance_idle_grace_seconds" type="number" :label="__('settings.grace_seconds')" icon="timer" :value="old('attendance_idle_grace_seconds', $attendancePolicy['idle_response_grace_seconds'] ?? 180)" min="30" max="600" required :helper="__('settings.grace_seconds_help')" style="font-family: var(--ula-font-mono);" />
                </div>

                <div class="ula-set-note">
                    <span class="material-symbols-rounded" aria-hidden="true">shield</span>
                    <div>
                        <strong>{{ __('settings.task_protection_title') }}</strong>
                        <span>{{ __('settings.task_protection_body') }}</span>
                    </div>
                </div>

                <div class="ula-set-actions">
                    <x-btn variant="primary" size="md" type="submit" icon="save">{{ __('settings.save') }}</x-btn>
                </div>
            </section>
        </form>
    </div>
</div>
