<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex">
    <title>{{ __('pm.intake_page_title', ['project' => $project->name]) }}</title>
    <script nonce="{{ $cspNonce ?? '' }}">
        (function () {
            let saved = 'light';
            try { saved = localStorage.getItem('vw_theme') || (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light'); } catch (e) {}
            document.documentElement.setAttribute('data-theme', saved);
        })();
    </script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;600;700;800&family=IBM+Plex+Mono:wght@400;600&family=IBM+Plex+Sans+Arabic:wght@300;400;600;700&family=IBM+Plex+Sans:wght@300;400;600;700&family=Material+Symbols+Rounded:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css'])
</head>
<body class="pm-intake-page">
    <main class="pm-intake-card">
        <div class="pm-row" style="justify-content: space-between;">
            <span class="pm-muted">{{ $project->organization?->name }}</span>
            <a class="pm-intake-lang" href="?lang={{ app()->getLocale() === 'ar' ? 'en' : 'ar' }}">{{ app()->getLocale() === 'ar' ? 'English' : 'العربية' }}</a>
        </div>

        <div class="ula-headline-group" style="margin-block: var(--ula-space-5) var(--ula-space-6);">
            <h1 class="ula-headline-ar" style="font-size: var(--ula-size-h2); margin: 0;">{{ __('pm.intake_heading', ['project' => $project->name]) }}</h1>
            @if(app()->getLocale() === 'ar')<span class="ula-headline-en" dir="ltr" style="font-size: var(--ula-size-body);">Send a request to the {{ $project->name }} team</span>@endif
            <span class="pm-muted">{{ __('pm.intake_intro') }}</span>
        </div>

        @if($sent)
            <div class="pm-intake-done" role="status">
                <span class="material-symbols-rounded" aria-hidden="true">check_circle</span>
                <div>
                    <strong>{{ __('pm.intake_thanks') }}</strong>
                    <div class="pm-muted">{{ __('pm.intake_thanks_sub') }}</div>
                </div>
            </div>
            <a class="pm-intake-lang" href="{{ route('intake.show', $project->intake_token) }}">{{ __('pm.intake_another') }}</a>
        @else
            <form method="POST" action="{{ route('intake.store', $project->intake_token) }}" class="pm-intake-form" novalidate>
                @csrf
                {{-- Honeypot: hidden from people, filled by bots --}}
                <div aria-hidden="true" class="pm-intake-hp">
                    <label>Website <input type="text" name="website" tabindex="-1" autocomplete="off"></label>
                </div>

                <x-input name="requester_name" :label="__('pm.intake_your_name')" :value="old('requester_name')" required :error="$errors->first('requester_name')" />
                <x-input name="requester_email" type="email" :label="__('pm.intake_your_email')" :value="old('requester_email')" :helper="__('pm.intake_email_help')" :error="$errors->first('requester_email')" />
                <x-input name="title" :label="__('pm.intake_title')" :value="old('title')" required :error="$errors->first('title')" />

                <label class="pm-intake-label" for="pm-intake-description">{{ __('pm.intake_details') }}</label>
                <textarea id="pm-intake-description" name="description" rows="6" maxlength="5000" class="pm-field" dir="auto">{{ old('description') }}</textarea>
                @error('description')<span class="pm-muted" style="color: var(--ula-text-danger);">{{ $message }}</span>@enderror

                <div><x-btn type="submit" variant="primary" size="lg" icon="send">{{ __('pm.intake_submit') }}</x-btn></div>
            </form>
        @endif
    </main>
</body>
</html>
