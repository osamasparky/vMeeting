{{-- One CMS input. Expects: $name (input name), $value, $def (CmsSchema field), $plans. --}}
@php
    $type = $def['type'];
    $isAr = str_ends_with($name, '_ar]');
    $inputId = 'f_' . md5($name);
@endphp
<div class="cms-field {{ $type === 'textarea' ? 'cms-field--wide' : '' }}">
    <label class="cms-label" for="{{ $inputId }}">
        <span>{{ __($def['label']) }}</span>
        @if($isAr)<span class="cms-lang">AR</span>@elseif(str_ends_with($name, '_en]'))<span class="cms-lang">EN</span>@endif
    </label>

    @if($type === 'textarea')
        <textarea id="{{ $inputId }}" name="{{ $name }}" rows="3" class="form-input cms-input" dir="{{ $isAr ? 'rtl' : 'ltr' }}">{{ $value }}</textarea>
    @elseif($type === 'select')
        <select id="{{ $inputId }}" name="{{ $name }}" class="form-input cms-input">
            @foreach($def['options'] as $optValue => $optLabel)
                <option value="{{ $optValue }}" @selected((string) $value === (string) $optValue)>{{ __($optLabel) }}</option>
            @endforeach
        </select>
    @elseif($type === 'plan')
        <select id="{{ $inputId }}" name="{{ $name }}" class="form-input cms-input">
            <option value="">— {{ __('None') }} —</option>
            @foreach($plans as $plan)
                <option value="{{ $plan->slug }}" @selected($value === $plan->slug)>{{ $plan->name }}</option>
            @endforeach
        </select>
    @elseif($type === 'icon')
        <div class="cms-icon-input">
            <span class="material-symbols-rounded cms-icon-preview" aria-hidden="true">{{ $value }}</span>
            <input id="{{ $inputId }}" type="text" name="{{ $name }}" value="{{ $value }}" class="form-input cms-input" dir="ltr" placeholder="e.g. apartment" data-icon-input>
        </div>
    @else
        <input id="{{ $inputId }}" type="text" name="{{ $name }}" value="{{ $value }}" class="form-input cms-input" dir="{{ $isAr ? 'rtl' : 'ltr' }}" @if($type === 'url') placeholder="/register" @endif>
    @endif

    @if($type === 'icon')
        <span class="cms-hint">{{ __('Material Symbols name') }} — <a href="https://fonts.google.com/icons" target="_blank" rel="noopener">fonts.google.com/icons</a></span>
    @elseif(!empty($def['hint']))
        <span class="cms-hint">{{ __($def['hint']) }}</span>
    @endif
</div>
